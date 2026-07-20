<?php

namespace App\Services\Update;

/**
 * ===== STRUKTUR TAHAP 2 — BELUM DIPAKAI DI JALUR PRODUKSI TAHAP 1 =====
 * KONTRAK SAAT DISELESAIKAN (Tahap 2):
 * - Baca heartbeat file tahap update (storage/app/update-tmp/heartbeat.json)
 * - Bila apply gagal: restore direktori *.old yang tersisa, restore .env dari
 *   pre-update backup, optimize:clear, catat update_history status=rolled_back
 * - Restore DATABASE hanya dengan konfirmasi eksplisit admin (dua langkah)
 * Utility restoreOldDirectories() di bawah nyata dan siap dipakai pipeline.
 */
class RollbackEngine
{
    /** Restore semua {dir}.old yang tersisa di base_path (tanda apply gagal di tengah). */
    public function restoreOldDirectories(array $dirs): array
    {
        $restored = [];
        foreach ($dirs as $dir) {
            $live = base_path($dir);
            $old  = $live . '.old';
            if (is_dir($old)) {
                if (is_dir($live)) continue; // live utuh, .old sisa -> biarkan pipeline hapus
                if (@rename($old, $live)) $restored[] = $dir;
            }
        }
        return $restored;
    }
}
