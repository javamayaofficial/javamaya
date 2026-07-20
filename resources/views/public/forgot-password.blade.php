@extends('layouts.public')
@section('title', 'Lupa Password')
@section('content')
<div class="max-w-sm mx-auto px-4 py-10">
    <div class="jm-card p-6">
        <h1 class="text-xl font-extrabold text-center">Lupa Password</h1>
        <p class="mt-1 text-sm text-slate-500 text-center">Kami kirimkan link reset ke email (dan WhatsApp bila terdaftar).</p>
        <form method="POST" action="{{ route('password.email') }}" class="mt-5 space-y-4">@csrf
            <input type="email" name="email" required placeholder="Email akun Anda" class="jm-input">
            <button class="w-full btn-accent text-white font-bold rounded-xl py-3.5 min-h-[48px] shadow-cta hover:opacity-90 transition">Kirim link reset</button>
        </form>
        <p class="mt-4 text-center text-sm"><a href="{{ route('login') }}" class="text-accent font-semibold">⟵ Kembali ke login</a></p>
    </div>
</div>
@endsection
