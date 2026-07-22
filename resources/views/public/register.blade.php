@extends('layouts.public')
@section('title', 'Daftar')
@section('content')
<div class="max-w-sm mx-auto px-4 py-14">
    <div class="text-center mb-6">
        <h1 class="text-2xl font-extrabold">Buat akun baru</h1>
        <p class="mt-1 text-sm text-muted">Gratis — akses pembelian Anda tersimpan rapi di satu tempat.</p>
    </div>
    <div class="jm-card p-6">
        @if ($errors->any())<div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm font-medium">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('register') }}" class="space-y-3.5">@csrf
            <div><label class="block text-sm font-semibold mb-1.5">Nama lengkap</label>
                <input name="name" value="{{ old('name') }}" required placeholder="Nama Anda" class="jm-input"></div>
            <div><label class="block text-sm font-semibold mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com" class="jm-input"></div>
            <div><label class="block text-sm font-semibold mb-1.5">Nomor WhatsApp</label>
                <input name="phone" value="{{ old('phone') }}" required inputmode="numeric" placeholder="08xxxxxxxxxx" class="jm-input"></div>
            <div><label class="block text-sm font-semibold mb-1.5">Password</label>
                <input type="password" name="password" required placeholder="Minimal 8 karakter" class="jm-input"></div>
            <div><label class="block text-sm font-semibold mb-1.5">Ulangi password</label>
                <input type="password" name="password_confirmation" required placeholder="Ketik ulang password" class="jm-input"></div>
            <button class="w-full btn-accent text-white font-bold rounded-xl py-3.5 min-h-[48px] shadow-cta hover:opacity-90 transition">Buat akun</button>
        </form>

        @if ($googleReady ?? false)
            <div class="my-5 flex items-center gap-3 text-xs text-stone-400 font-semibold">
                <span class="flex-1 h-px bg-line"></span> atau <span class="flex-1 h-px bg-line"></span>
            </div>
            <a href="{{ route('google.redirect') }}" class="rounded-xl border border-ink/15 py-3 text-center font-semibold min-h-[48px] flex items-center justify-center gap-2 hover:border-ink/30 hover:bg-stone-50 transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.06H2.18A11 11 0 0 0 1 12c0 1.77.43 3.45 1.18 4.94l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg>
                Daftar dengan Google
            </a>
        @endif
    </div>
    <p class="mt-5 text-center text-sm text-muted">Sudah punya akun? <a href="{{ route('login') }}" class="text-accent-c font-bold hover:underline">Masuk</a></p>
</div>
@endsection
