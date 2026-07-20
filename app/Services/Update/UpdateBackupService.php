<?php

namespace App\Services\Update;

use App\Services\Backup\DatabaseBackup;

/**
 * ===== STRUKTUR TAHAP 2 =====
 * Backup pra-update: memakai DatabaseBackup (Tahap 1, sudah hidup) +
 * salin .env ke folder backup update.
 * SUDAH SELESAI: backup DB + .env ke storage/app/backups/pre-update-*.
 * DISELESAIKAN DI TAHAP 2: backup file inti termodifikasi + integrasi pipeline ApplyEngine.
 */
class UpdateBackupService
{
    public function __construct(protected DatabaseBackup $db) {}

    /** @return array{ok: bool, dir?: string, error?: string} */
    public function createPreUpdateBackup(): array
    {
        try {
            $dir = 'backups/pre-update-' . now()->format('Y-m-d-Hi');
            $dbResult = $this->db->run($dir);
            if (! $dbResult['ok']) return ['ok' => false, 'error' => $dbResult['error'] ?? 'Backup DB gagal'];

            $envSource = base_path('.env');
            if (is_file($envSource)) {
                copy($envSource, storage_path('app/' . $dir . '/env.backup'));
            }
            return ['ok' => true, 'dir' => $dir];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
