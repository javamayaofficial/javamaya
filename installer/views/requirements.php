<h1 class="text-lg font-extrabold mb-4">Cek kebutuhan server</h1>
<?php $allOk = true; ?>
<div class="space-y-2">
    <?php foreach ($requirements as [$label, $ok, $hint]): if (!$ok) $allOk = false; ?>
        <div class="flex items-start gap-3 rounded-xl border px-4 py-3 <?= $ok ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50' ?>">
            <span class="text-lg"><?= $ok ? '✅' : '❌' ?></span>
            <div class="text-sm">
                <div class="font-semibold"><?= htmlspecialchars($label) ?></div>
                <?php if (!$ok): ?><div class="text-rose-600 text-xs mt-0.5"><?= htmlspecialchars($hint) ?></div><?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php if ($allOk): ?>
    <a href="index.php?step=2" class="mt-5 block text-center bg-indigo-600 text-white font-bold rounded-xl py-3.5">Semua siap — Lanjut ➜</a>
<?php else: ?>
    <a href="index.php?step=1" class="mt-5 block text-center border border-slate-300 font-bold rounded-xl py-3.5">Sudah diperbaiki? Cek ulang ⟳</a>
    <p class="mt-3 text-xs text-slate-500 text-center">Butuh bantuan? Buka <b>docs/PANDUAN-INSTALASI.md</b> — semua langkah pakai bahasa awam.</p>
<?php endif; ?>
