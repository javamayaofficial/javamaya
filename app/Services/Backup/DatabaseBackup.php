<?php

namespace App\Services\Backup;

use App\Models\Backup;
use Illuminate\Support\Facades\DB;

/**
 * Backup database: mysqldump via shell_exec, fallback PHP-native dumper
 * bila shell_exec disabled (umum di shared hosting). Retention keep last N.
 */
class DatabaseBackup
{
    /** @return array{ok: bool, path?: string, size?: int, error?: string} */
    public function run(?string $subdir = null): array
    {
        $dir = $subdir ?: ('backups/' . now()->format('Y-m-d-Hi'));
        $absDir = storage_path('app/' . $dir);
        if (! is_dir($absDir) && ! @mkdir($absDir, 0755, true)) {
            return ['ok' => false, 'error' => 'Folder backup tidak bisa dibuat. Cek permission storage.'];
        }

        $file = $absDir . '/database.sql';
        $ok = $this->tryMysqldump($file) || $this->phpNativeDump($file);
        if (! $ok || ! is_file($file)) {
            Backup::create(['path' => $dir, 'type' => 'db', 'status' => 'failed']);
            return ['ok' => false, 'error' => 'Backup database gagal (mysqldump & PHP-native).'];
        }

        $size = (int) filesize($file);
        Backup::create(['path' => $dir . '/database.sql', 'size' => $size, 'type' => 'db', 'storage_driver' => 'local', 'status' => 'success']);
        $this->applyRetention();
        return ['ok' => true, 'path' => $dir . '/database.sql', 'size' => $size];
    }

    protected function tryMysqldump(string $file): bool
    {
        if (! function_exists('shell_exec')) return false;
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        if (in_array('shell_exec', $disabled, true)) return false;

        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --skip-lock-tables %s > %s 2>/dev/null',
            escapeshellarg(config('database.connections.mysql.host')),
            escapeshellarg((string) config('database.connections.mysql.port')),
            escapeshellarg(config('database.connections.mysql.username')),
            escapeshellarg(config('database.connections.mysql.password')),
            escapeshellarg(config('database.connections.mysql.database')),
            escapeshellarg($file)
        );
        @shell_exec($cmd);
        return is_file($file) && filesize($file) > 0;
    }

    /** Dumper PHP-native: SHOW TABLES -> SHOW CREATE TABLE + INSERT chunked. */
    protected function phpNativeDump(string $file): bool
    {
        try {
            $handle = fopen($file, 'w');
            if (! $handle) return false;
            fwrite($handle, "-- Javamaya PHP-native dump " . now()->toDateTimeString() . "\nSET FOREIGN_KEY_CHECKS=0;\n\n");

            $tables = array_map(fn ($row) => array_values((array) $row)[0], DB::select('SHOW TABLES'));
            foreach ($tables as $table) {
                $create = DB::select("SHOW CREATE TABLE `$table`")[0];
                $createSql = ((array) $create)['Create Table'] ?? array_values((array) $create)[1];
                fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n$createSql;\n\n");

                DB::table($table)->orderByRaw('1')->chunk(500, function ($rows) use ($handle, $table) {
                    foreach ($rows as $row) {
                        $values = array_map(function ($v) {
                            if ($v === null) return 'NULL';
                            return DB::getPdo()->quote((string) $v);
                        }, (array) $row);
                        fwrite($handle, "INSERT INTO `$table` VALUES (" . implode(',', $values) . ");\n");
                    }
                });
                fwrite($handle, "\n");
            }
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($handle);
            return filesize($file) > 0;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    /** Retention: keep last N (default 7) backup sukses; hapus file + record tertua. */
    protected function applyRetention(): void
    {
        $keep = (int) config('javamaya.backup.retention');
        $old = Backup::where('type', 'db')->where('status', 'success')
            ->orderByDesc('id')->skip($keep)->take(50)->get();
        foreach ($old as $backup) {
            $abs = storage_path('app/' . $backup->path);
            if (is_file($abs)) @unlink($abs);
            $dir = dirname($abs);
            if (is_dir($dir) && count(glob($dir . '/*') ?: []) === 0) @rmdir($dir);
            $backup->delete();
        }
    }
}
