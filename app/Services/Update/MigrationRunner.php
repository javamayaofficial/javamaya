<?php

namespace App\Services\Update;

use Illuminate\Support\Facades\Artisan;

/**
 * Migration runner idempotent + sequential.
 * TAHAP 1: dipakai UpdateManager::finalizeManualUpdate() dan installer.
 * Semua migration Javamaya diguard hasTable/hasColumn sehingga aman dijalankan ulang.
 */
class MigrationRunner
{
    /** @return array{ok: bool, output: string} */
    public function run(): array
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            return ['ok' => true, 'output' => Artisan::output()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'output' => $e->getMessage()];
        }
    }
}
