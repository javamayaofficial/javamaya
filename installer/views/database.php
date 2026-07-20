<h1 class="text-lg font-extrabold mb-1">Sambungkan database</h1>
<p class="text-sm text-slate-500 mb-4">Buat database & user dulu di cPanel > <b>MySQL® Databases</b>, lalu isi di sini.</p>
<form method="post" action="index.php?step=2" class="space-y-3">
    <div><label class="text-sm font-semibold">URL website Anda</label>
        <input name="app_url" required placeholder="https://tokoanda.com" value="<?= htmlspecialchars('https://' . ($_SERVER['HTTP_HOST'] ?? '')) ?>"
               class="mt-1 w-full rounded-xl border-slate-300 px-4 py-3"></div>
    <div class="grid grid-cols-3 gap-3">
        <div class="col-span-2"><label class="text-sm font-semibold">Host</label>
            <input name="db_host" value="127.0.0.1" class="mt-1 w-full rounded-xl border-slate-300 px-4 py-3"></div>
        <div><label class="text-sm font-semibold">Port</label>
            <input name="db_port" value="3306" class="mt-1 w-full rounded-xl border-slate-300 px-4 py-3"></div>
    </div>
    <div><label class="text-sm font-semibold">Nama database</label>
        <input name="db_name" required placeholder="cth: user_javamaya" class="mt-1 w-full rounded-xl border-slate-300 px-4 py-3"></div>
    <div><label class="text-sm font-semibold">User database</label>
        <input name="db_user" required class="mt-1 w-full rounded-xl border-slate-300 px-4 py-3"></div>
    <div><label class="text-sm font-semibold">Password database</label>
        <input name="db_pass" type="password" class="mt-1 w-full rounded-xl border-slate-300 px-4 py-3"></div>
    <button class="w-full bg-indigo-600 text-white font-bold rounded-xl py-3.5">Tes koneksi & pasang database ➜</button>
    <p class="text-xs text-slate-400 text-center">Langkah ini membuat file .env dan memasang seluruh tabel (± 1 menit).</p>
</form>
