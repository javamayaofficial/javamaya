@extends('layouts.public')
@section('title', 'Aktifkan 2FA')
@section('content')
<div class="max-w-md mx-auto px-4 py-10">
    <div class="jm-card p-6">
        <h1 class="text-xl font-extrabold">Amankan akun admin Anda</h1>
        <p class="mt-1 text-sm text-slate-500">Wajib untuk admin: scan QR di aplikasi Google Authenticator / Authy.</p>

        <div class="mt-5 flex justify-center">
            <img class="rounded-xl border border-slate-200 p-2 bg-white"
                 src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($otpauth_url) }}" alt="QR 2FA" width="200" height="200">
        </div>
        <p class="mt-2 text-center text-xs text-slate-400">Tidak bisa scan? Masukkan manual: <span class="font-mono font-semibold">{{ $secret }}</span></p>

        <div class="mt-5 rounded-xl bg-amber-50 border border-amber-200 p-4">
            <div class="font-bold text-amber-900 text-sm">⚠️ Simpan 8 backup codes ini SEKARANG</div>
            <p class="text-xs text-amber-800 mt-1">Hanya tampil sekali. Dipakai bila HP hilang.</p>
            <div class="mt-2 grid grid-cols-2 gap-1 font-mono text-sm">
                @foreach ($backup_codes as $code)<div class="bg-white rounded px-2 py-1 border border-amber-200">{{ $code }}</div>@endforeach
            </div>
        </div>

        @if ($errors->any())<div class="mt-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('twofactor.enroll') }}" class="mt-5 space-y-3">@csrf
            <input name="code" required inputmode="numeric" maxlength="6" placeholder="6 digit dari aplikasi"
                   class="jm-input">
            <button class="w-full btn-accent text-white font-bold rounded-xl py-3.5 min-h-[48px] shadow-cta hover:opacity-90 transition">Aktifkan 2FA</button>
        </form>
    </div>
</div>
@endsection
