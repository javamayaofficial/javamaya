<h1 class="text-lg font-extrabold mb-1">Buat akun Super Admin</h1>
<p class="text-sm text-slate-500 mb-4">Akun ini untuk masuk ke panel admin. Setelah login pertama Anda akan diminta mengaktifkan 2FA (wajib).</p>
<form method="post" action="index.php?step=3" class="space-y-3">
    <div><label class="text-sm font-semibold">Nama</label>
        <input name="name" required class="mt-1 w-full rounded-xl border-slate-300 px-4 py-3"></div>
    <div><label class="text-sm font-semibold">Email</label>
        <input name="email" type="email" required class="mt-1 w-full rounded-xl border-slate-300 px-4 py-3"></div>
    <div><label class="text-sm font-semibold">Password (min 8 karakter)</label>
        <input name="password" type="password" required minlength="8" class="mt-1 w-full rounded-xl border-slate-300 px-4 py-3"></div>
    <div><label class="text-sm font-semibold">Ulangi password</label>
        <input name="password2" type="password" required minlength="8" class="mt-1 w-full rounded-xl border-slate-300 px-4 py-3"></div>
    <button class="w-full bg-indigo-600 text-white font-bold rounded-xl py-3.5">Buat akun & selesaikan ➜</button>
</form>
