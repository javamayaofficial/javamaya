<?php

namespace App\Services\Update;

use App\Models\ActivityLog;
use App\Models\UpdateHistory;

/**
 * TAHAP 1 — LENGKAP & HIDUP: manual check + changelog + link download ZIP
 * + deteksi versi manual + update history + jalankan migrate/clear cache
 * pasca update manual (dipanggil dari halaman Update Manager Filament).
 *
 * TAHAP 2 (auto-apply penuh) memakai kelas: SignatureVerifierSodium,
 * UpdateBackupService, ApplyEngine, RollbackEngine, MigrationRunner,
 * MaintenanceMode — struktur tersedia di folder ini dengan catatan eksplisit.
 */
class UpdateManager
{
    public function __construct(protected VersionChecker $checker) {}

    public function status(): array
    {
        $check = $this->checker->check();
        return [
            'current' => $this->checker->currentVersion(),
            'check'   => $check,
            'history' => UpdateHistory::latest()->limit(20)->get(),
        ];
    }

    /** Dipanggil tombol "Selesai Update Manual": migrate idempotent + clear cache + catat history. */
    public function finalizeManualUpdate(string $fromVersion): UpdateHistory
    {
        $start = microtime(true);
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');

        $history = UpdateHistory::create([
            'from_version'     => $fromVersion,
            'to_version'       => $this->checker->currentVersion(),
            'status'           => 'manual_detected',
            'duration_seconds' => (int) (microtime(true) - $start),
            'initiated_by'     => auth()->id(),
            'log_excerpt'      => mb_substr(\Illuminate\Support\Facades\Artisan::output(), 0, 2000),
        ]);
        ActivityLog::record('update.manual_finalized', $history);
        return $history;
    }
}
