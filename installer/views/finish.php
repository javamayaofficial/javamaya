<div class="text-center">
    <div class="text-5xl">🎉</div>
    <h1 class="mt-3 text-xl font-extrabold">Instalasi selesai!</h1>
    <?php if ($selfDeleted): ?>
        <p class="mt-2 text-sm text-emerald-600 font-semibold">Folder installer sudah dihapus otomatis. ✓</p>
    <?php else: ?>
        <p class="mt-2 text-sm text-rose-600 font-semibold">PENTING: hapus folder <b>installer/</b> secara manual via File Manager sekarang. Aplikasi menolak berjalan selama folder itu masih ada.</p>
    <?php endif; ?>
    <div class="mt-6 grid gap-3">
        <a href="../admin" class="bg-indigo-600 text-white font-bold rounded-xl py-3.5">Masuk Panel Admin ➜</a>
        <a href="../" class="border border-slate-300 font-bold rounded-xl py-3.5">Lihat toko saya</a>
    </div>
    <div class="mt-6 text-left rounded-xl bg-slate-50 border border-slate-200 p-4 text-sm">
        <div class="font-bold mb-2">Langkah berikutnya (± 15 menit):</div>
        <ol class="list-decimal ml-4 space-y-1 text-slate-600">
            <li>Login admin → aktifkan <b>2FA</b> & simpan 8 backup codes</li>
            <li>Ikuti <b>Setup Checklist</b> di dashboard: branding → pajak → payment gateway → Fonnte/Mailketing</li>
            <li>Buat produk pertama → uji checkout dengan <b>Sandbox mode</b></li>
        </ol>
    </div>
</div>
