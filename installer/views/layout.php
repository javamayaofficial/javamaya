<?php /** @var int $step */ ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Install Javamaya</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>body{font-family:'Plus Jakarta Sans',system-ui,sans-serif}</style>
</head>
<body class="bg-slate-50 min-h-screen">
<div class="max-w-lg mx-auto px-4 py-10">
    <div class="text-center mb-6">
        <div class="text-3xl font-extrabold text-indigo-600">Javamaya</div>
        <div class="text-sm text-slate-500">Instalasi — selesai dalam ± 5 menit</div>
    </div>

    <?php if ($view !== 'sudah'): ?>
    <div class="flex items-center gap-1 mb-6">
        <?php foreach (['Cek Server', 'Database', 'Akun Admin', 'Selesai'] as $i => $label): $n = $i + 1; ?>
            <div class="flex-1 text-center">
                <div class="mx-auto w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm
                    <?= $n < $step ? 'bg-emerald-500 text-white' : ($n === $step ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-500') ?>">
                    <?= $n < $step ? '✓' : $n ?></div>
                <div class="mt-1 text-[11px] font-semibold <?= $n === $step ? 'text-indigo-600' : 'text-slate-400' ?>"><?= $label ?></div>
            </div>
            <?php if ($n < 4): ?><div class="flex-1 h-px bg-slate-200 -mt-4"></div><?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm">
            <?php foreach ($errors as $e): ?><div><?= $e ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <?php include __DIR__ . '/' . $view . '.php'; ?>
    </div>
</div>
</body>
</html>
