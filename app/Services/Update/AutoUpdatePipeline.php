<?php

namespace App\Services\Update;

use App\Models\ActivityLog;
use App\Models\LicenseInfo;
use App\Models\UpdateHistory;
use Illuminate\Support\Facades\Http;

/**
 * Pipeline auto-update 1-klik (Tingkat 1: dipicu admin, prosesnya otomatis).
 *
 * Urutan aman (setiap langkah bisa membatalkan sisanya):
 *   1) Pre-checks: ekstensi, disk, permission, versi minimum
 *   2) Maintenance ON
 *   3) Backup DB + .env
 *   4) Download paket + verifikasi sha256 + signature Ed25519
 *   5) Extract ke tmp
 *   6) Atomic swap per direktori inti (rename .old -> pindah baru)
 *   7) Migrasi idempotent
 *   8) Bila SEMUA sukses -> hapus *.old, clear cache, Maintenance OFF, catat sukses
 *   9) Bila GAGAL di langkah manapun -> RollbackEngine restore *.old + .env,
 *      clear cache, Maintenance OFF, catat rolled_back
 *
 * Direktori yang di-swap; .env, storage/, public/uploads TIDAK disentuh.
 */
class AutoUpdatePipeline
{
    /** Direktori inti yang diganti saat update. */
    protected array $coreDirs = ['app', 'bootstrap/cache-config', 'config', 'database', 'resources', 'routes', 'public/assets', 'vendor'];

    public function __construct(
        protected VersionChecker $checker,
        protected SignatureVerifierSodium $verifier,
        protected UpdateBackupService $backup,
        protected ApplyEngine $apply,
        protected RollbackEngine $rollback,
        protected MigrationRunner $migrator,
        protected MaintenanceMode $maintenance,
    ) {}

    /**
     * Jalankan update penuh ke versi terbaru.
     * @return array{ok: bool, status: string, message: string, history_id?: int}
     */
    public function run(): array
    {
        $from = $this->checker->currentVersion();
        $start = microtime(true);
        $tmpBase = storage_path('app/update-tmp');
        $log = [];

        // 1) PRE-CHECKS
        $pre = $this->preChecks();
        if (! $pre['ok']) {
            return $this->fail($from, $from, 'pre_check_failed', $pre['message'], $start, $log);
        }
        $check = $this->checker->check(true);
        if (empty($check['ok']) || empty($check['update_available'])) {
            return ['ok' => false, 'status' => 'no_update', 'message' => 'Tidak ada update yang tersedia.'];
        }
        $to = $check['latest'] ?? $check['latest_version'] ?? 'unknown';
        $log[] = "Update $from -> $to";

        // Cek versi minimum
        if (! empty($check['min_required']) && version_compare($from, $check['min_required'], '<')) {
            return $this->fail($from, $to, 'min_required',
                "Versi Anda ($from) di bawah minimum ({$check['min_required']}) untuk update ini. Update bertahap diperlukan.", $start, $log);
        }

        // 2) MAINTENANCE ON
        $this->maintenance->enable('update');
        $log[] = 'Maintenance mode aktif';

        try {
            // 3) BACKUP
            $backup = $this->backup->createPreUpdateBackup();
            if (! $backup['ok']) {
                throw new \RuntimeException('Backup gagal: ' . ($backup['error'] ?? 'unknown'));
            }
            $backupDir = $backup['dir'];
            $log[] = "Backup dibuat: $backupDir";

            // 4) DOWNLOAD
            @mkdir($tmpBase, 0755, true);
            $zipPath = $tmpBase . '/package-' . $to . '.zip';
            $dl = $this->download($check['download_url'] ?? '', $zipPath);
            if (! $dl['ok']) throw new \RuntimeException('Download gagal: ' . $dl['error']);
            $log[] = 'Paket terunduh (' . number_format(filesize($zipPath) / 1048576, 1) . ' MB)';

            // 4b) VERIFIKASI sha256 + signature
            if (! empty($check['sha256'])) {
                $actual = hash_file('sha256', $zipPath);
                if (! hash_equals($check['sha256'], $actual)) {
                    throw new \RuntimeException('sha256 paket tidak cocok — paket rusak/dipalsukan.');
                }
                $log[] = 'sha256 terverifikasi';
            }
            if (! empty($check['package_signature'])) {
                $ok = $this->verifier->verifyHashSignature($check['sha256'], $check['package_signature'], $to);
                if (! $ok) throw new \RuntimeException('Tanda tangan paket tidak sah — ditolak demi keamanan.');
                $log[] = 'Tanda tangan Ed25519 sah';
            }

            // 5) EXTRACT
            $extractDir = $tmpBase . '/extract-' . $to;
            $this->apply->deletePath($extractDir);
            if (! $this->apply->extractZip($zipPath, $extractDir)) {
                throw new \RuntimeException('Ekstraksi paket gagal.');
            }
            // Paket bisa punya folder root; deteksi.
            $root = $this->detectRoot($extractDir);
            $log[] = 'Paket diekstrak';

            // 6) ATOMIC SWAP per direktori inti yang ADA di paket
            $swapped = [];
            foreach ($this->coreDirs as $dir) {
                $newDir = $root . '/' . $dir;
                if (! is_dir($newDir)) continue;               // paket tak selalu berisi semua dir
                if (! $this->apply->atomicSwapDirectory(base_path($dir), $newDir)) {
                    throw new \RuntimeException("Gagal mengganti direktori: $dir");
                }
                $swapped[] = $dir;
                $log[] = "Swap: $dir";
            }

            // 7) MIGRASI
            $mig = $this->migrator->run();
            if (! $mig['ok']) throw new \RuntimeException('Migrasi gagal: ' . mb_substr($mig['output'], 0, 300));
            $log[] = 'Migrasi selesai';

            // 8) SUKSES — bersihkan
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            $this->apply->deletePath($tmpBase);
            $this->maintenance->disable();

            $history = $this->record($from, $to, 'success', $start, $log);
            ActivityLog::record('update.auto_applied', $history);
            return ['ok' => true, 'status' => 'success',
                    'message' => "Berhasil update ke versi $to.", 'history_id' => $history->id];

        } catch (\Throwable $e) {
            // 9) ROLLBACK
            $log[] = 'GAGAL: ' . $e->getMessage();
            $restored = $this->rollback->restoreOldDirectories($this->coreDirs);
            if ($restored) $log[] = 'Rollback direktori: ' . implode(', ', $restored);

            // restore .env bila backup ada
            if (! empty($backupDir)) {
                $envBackup = storage_path('app/' . $backupDir . '/env.backup');
                if (is_file($envBackup)) { @copy($envBackup, base_path('.env')); $log[] = '.env dipulihkan'; }
            }
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            $this->apply->deletePath($tmpBase);
            $this->maintenance->disable();

            $history = $this->record($from, $to ?? $from, 'rolled_back', $start, $log);
            ActivityLog::record('update.rolled_back', $history);
            return ['ok' => false, 'status' => 'rolled_back',
                    'message' => 'Update gagal dan sistem dikembalikan ke versi semula. Detail: ' . $e->getMessage(),
                    'history_id' => $history->id];
        }
    }

    protected function preChecks(): array
    {
        if (! extension_loaded('zip'))    return ['ok' => false, 'message' => 'Ekstensi PHP zip tidak aktif.'];
        if (! extension_loaded('sodium')) return ['ok' => false, 'message' => 'Ekstensi PHP sodium tidak aktif.'];
        if (! class_exists(\ZipArchive::class)) return ['ok' => false, 'message' => 'ZipArchive tidak tersedia.'];
        if (! $this->verifier->hasConfiguredPublicKey()) {
            return ['ok' => false, 'message' => 'Public key lisensi belum dikonfigurasi. Isi JAVAMAYA_LICENSE_PUBKEY sebelum memakai auto-update.'];
        }

        $free = @disk_free_space(base_path());
        if ($free !== false && $free < 300 * 1024 * 1024) {
            return ['ok' => false, 'message' => 'Ruang disk kurang dari 300 MB. Kosongkan dulu.'];
        }
        if (! is_writable(base_path('app')) || ! is_writable(base_path('config'))) {
            return ['ok' => false, 'message' => 'Folder aplikasi tidak writable oleh proses web.'];
        }
        return ['ok' => true];
    }

    protected function download(string $url, string $dest): array
    {
        if ($url === '') return ['ok' => false, 'error' => 'URL unduh kosong'];
        try {
            $resp = Http::timeout(300)->withOptions(['sink' => $dest])
                ->withHeaders(['X-License-Key' => LicenseInfo::query()->value('license_key') ?? ''])
                ->get($url);
            if (! $resp->successful()) return ['ok' => false, 'error' => 'HTTP ' . $resp->status()];
            if (! is_file($dest) || filesize($dest) < 1024) return ['ok' => false, 'error' => 'Paket kosong/terlalu kecil'];
            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    protected function detectRoot(string $extractDir): string
    {
        $entries = array_values(array_filter(scandir($extractDir), fn ($e) => $e !== '.' && $e !== '..'));
        // Bila hanya ada satu folder & tidak ada composer.json di root -> itu folder root paket
        if (count($entries) === 1 && is_dir($extractDir . '/' . $entries[0])
            && ! is_file($extractDir . '/composer.json')) {
            return $extractDir . '/' . $entries[0];
        }
        return $extractDir;
    }

    protected function fail(string $from, string $to, string $status, string $msg, float $start, array $log): array
    {
        $this->maintenance->disable();
        $log[] = $msg;
        $h = $this->record($from, $to, $status, $start, $log);
        return ['ok' => false, 'status' => $status, 'message' => $msg, 'history_id' => $h->id];
    }

    protected function record(string $from, string $to, string $status, float $start, array $log): UpdateHistory
    {
        return UpdateHistory::create([
            'from_version'     => $from,
            'to_version'       => $to,
            'status'           => $status,
            'duration_seconds' => (int) (microtime(true) - $start),
            'initiated_by'     => auth()->id(),
            'log_excerpt'      => mb_substr(implode("\n", $log), 0, 4000),
        ]);
    }
}
