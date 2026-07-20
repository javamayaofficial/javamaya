@extends('layouts.public')
@section('title', 'Verifikasi 2FA')
@section('content')
<div class="max-w-sm mx-auto px-4 py-10">
    <div class="jm-card p-6 text-center">
        <div class="text-4xl">🔐</div>
        <h1 class="mt-3 text-xl font-extrabold">Verifikasi 2FA</h1>
        <p class="mt-1 text-sm text-slate-500">Masukkan kode dari aplikasi authenticator, atau salah satu backup code.</p>
        @if ($errors->any())<div class="mt-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('twofactor.challenge') }}" class="mt-5 space-y-3">@csrf
            <input name="code" required placeholder="Kode 6 digit / backup code" autofocus
                   class="jm-input">
            <label class="flex items-center justify-center gap-2 text-sm text-slate-600 cursor-pointer">
                <input type="checkbox" name="trust_device" value="1" class="w-4 h-4 rounded text-accent">
                Ingat perangkat ini selama 30 hari
            </label>
            <button class="w-full btn-accent text-white font-bold rounded-xl py-3.5 min-h-[48px] shadow-cta hover:opacity-90 transition">Verifikasi</button>
        </form>
    </div>
</div>
@endsection
