<?php

namespace App\Services\Update;

/**
 * ===== STRUKTUR TAHAP 2 =====
 * Maintenance mode khusus update: 503 untuk storefront + user area,
 * admin tetap bisa akses (banner). Implementasi flag file nyata di bawah;
 * middleware MaintenanceMode (Tahap 1) membacanya.
 */
class MaintenanceMode
{
    protected function flagPath(): string
    {
        return storage_path('framework/javamaya-maintenance.flag');
    }

    public function enable(string $reason = 'update'): void
    {
        file_put_contents($this->flagPath(), json_encode(['reason' => $reason, 'since' => now()->toIso8601String()]));
    }

    public function disable(): void
    {
        @unlink($this->flagPath());
    }

    public function isActive(): bool
    {
        return is_file($this->flagPath());
    }
}
