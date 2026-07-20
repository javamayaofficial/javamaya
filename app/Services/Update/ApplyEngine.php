<?php

namespace App\Services\Update;

/**
 * ===== STRUKTUR TAHAP 2 — BELUM DIPAKAI DI JALUR PRODUKSI TAHAP 1 =====
 * Atomic file replace pattern untuk auto-update penuh.
 *
 * KONTRAK YANG HARUS DIPENUHI SAAT DISELESAIKAN (Tahap 2):
 * 1) extract package ke storage/app/update-tmp/{version}
 * 2) per direktori inti (app, bootstrap, config, database, resources, routes, vendor, public/build):
 *    rename lama -> {dir}.old ; copy baru ; sukses semua -> hapus *.old ; gagal -> restore *.old
 * 3) preserve: .env, storage/, public/uploads, installer terhapus
 * 4) tulis heartbeat file per tahap untuk dideteksi RollbackEngine
 * 5) diuji dengan skenario gagal disengaja: disk penuh, permission dicabut, proses di-kill
 *
 * Method di bawah adalah utilitas nyata yang SUDAH bisa dipakai (extract + atomic swap satu dir),
 * namun pipeline orkestrasinya sengaja belum diaktifkan di Tahap 1 demi keselamatan buyer.
 */
class ApplyEngine
{
    public function extractZip(string $zipPath, string $targetDir): bool
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) return false;
        @mkdir($targetDir, 0755, true);
        $ok = $zip->extractTo($targetDir);
        $zip->close();
        return $ok;
    }

    /** Atomic swap satu direktori: rename lama ke .old, pindahkan baru, gagal -> restore. */
    public function atomicSwapDirectory(string $liveDir, string $newDir): bool
    {
        $oldDir = $liveDir . '.old';
        if (is_dir($oldDir)) $this->deleteDirectory($oldDir);
        if (is_dir($liveDir) && ! @rename($liveDir, $oldDir)) return false;
        if (! @rename($newDir, $liveDir)) {
            if (is_dir($oldDir)) @rename($oldDir, $liveDir); // restore
            return false;
        }
        $this->deleteDirectory($oldDir);
        return true;
    }

    /** Hapus path apapun (file atau direktori). Aman bila tidak ada. */
    public function deletePath(string $path): void
    {
        if (is_file($path)) { @unlink($path); return; }
        if (is_dir($path))  { $this->deleteDirectory($path); }
    }

    protected function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) return;
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($dir);
    }
}
