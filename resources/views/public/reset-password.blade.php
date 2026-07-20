@extends('layouts.public')
@section('title', 'Atur Ulang Password')
@section('content')
<div class="max-w-sm mx-auto px-4 py-10">
    <div class="jm-card p-6">
        <h1 class="text-xl font-extrabold text-center">Atur Ulang Password</h1>
        @if ($errors->any())<div class="mt-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('password.reset.submit') }}" class="mt-5 space-y-4">@csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">
            <input type="password" name="password" required minlength="8" placeholder="Password baru (min 8)" class="jm-input">
            <input type="password" name="password_confirmation" required minlength="8" placeholder="Ulangi password baru" class="jm-input">
            <button class="w-full btn-accent text-white font-bold rounded-xl py-3.5 min-h-[48px] shadow-cta hover:opacity-90 transition">Simpan password baru</button>
        </form>
    </div>
</div>
@endsection
