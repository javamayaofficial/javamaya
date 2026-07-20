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
    </div>
    <p class="mt-5 text-center text-sm text-muted">Sudah punya akun? <a href="{{ route('login') }}" class="text-accent-c font-bold hover:underline">Masuk</a></p>
</div>
@endsection
