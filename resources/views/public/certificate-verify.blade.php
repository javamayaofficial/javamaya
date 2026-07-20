@extends('layouts.public')
@section('title', 'Verifikasi Sertifikat')
@section('content')
<div class="max-w-md mx-auto px-4 py-12">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm text-center">
        @if ($certificate)
            <div class="mx-auto w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl">✓</div>
            <h1 class="mt-4 text-xl font-extrabold">Sertifikat VALID</h1>
            <dl class="mt-5 text-left text-sm space-y-2">
                <div class="flex justify-between"><dt class="text-slate-500">Nama</dt><dd class="font-semibold">{{ $certificate->participant_name }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Kelas</dt><dd class="font-semibold">{{ $certificate->classRoom->title }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Terbit</dt><dd class="font-semibold">{{ $certificate->issued_at->translatedFormat('d F Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Kode</dt><dd class="font-mono font-semibold">{{ $certificate->code }}</dd></div>
            </dl>
        @else
            <div class="mx-auto w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-2xl">✕</div>
            <h1 class="mt-4 text-xl font-extrabold">Kode tidak ditemukan</h1>
            <p class="mt-2 text-slate-500 text-sm">Kode <b>{{ $code }}</b> tidak terdaftar di sistem kami.</p>
        @endif
    </div>
</div>
@endsection
