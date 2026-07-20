@extends('layouts.public')
@section('title', 'Data Saya')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-10">
    <a href="{{ route('user.dashboard') }}" class="text-sm font-semibold text-muted hover:text-ink transition">← Dashboard</a>
    <h1 class="mt-2 text-3xl font-extrabold">Data Saya</h1>
    <p class="mt-2 text-sm text-muted">Anda memegang kendali penuh atas data pribadi Anda.</p>
    @if ($errors->any())<div class="mt-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm font-medium">{{ $errors->first() }}</div>@endif

    <div class="mt-6 grid sm:grid-cols-2 gap-4">
        <form method="POST" action="{{ route('user.gdpr.store') }}">@csrf<input type="hidden" name="type" value="export">
            <button class="w-full h-full jm-card jm-lift p-6 text-left">
                <span class="h-11 w-11 rounded-xl bg-accent-soft grid place-items-center text-xl">📦</span>
                <div class="mt-3 font-extrabold">Export data saya</div>
                <div class="text-xs text-muted mt-1 leading-relaxed">ZIP berisi profil, transaksi, akses & sertifikat. Link berlaku 24 jam.</div>
            </button></form>
        <form method="POST" action="{{ route('user.gdpr.store') }}" onsubmit="return confirm('Yakin mengajukan penghapusan akun? Proses menunggu 30 hari.')">@csrf<input type="hidden" name="type" value="delete">
            <button class="w-full h-full jm-card jm-lift p-6 text-left border-rose-200">
                <span class="h-11 w-11 rounded-xl bg-rose-50 grid place-items-center text-xl">🗑️</span>
                <div class="mt-3 font-extrabold text-rose-600">Hapus akun saya</div>
                <div class="text-xs text-muted mt-1 leading-relaxed">Masa tunggu 30 hari. Data transaksi dianonimkan (ketentuan pajak).</div>
            </button></form>
    </div>

    <h2 class="mt-10 font-extrabold text-xl">Riwayat permintaan</h2>
    <div class="mt-4 jm-card divide-y divide-ink/5 overflow-hidden text-sm">
        @forelse ($requests as $r)
            <div class="px-5 py-4 flex justify-between items-center gap-3">
                <div>
                    <span class="font-semibold">{{ $r->type === 'export' ? '📦 Export data' : '🗑️ Penghapusan akun' }}</span>
                    <span class="text-xs text-stone-400 ml-1">{{ $r->created_at->translatedFormat('d M Y') }}</span>
                </div>
                @if ($r->type === 'export' && $r->status === 'ready' && ! $r->export_expires_at?->isPast())
                    <a href="{{ route('user.gdpr.download', $r->id) }}" class="text-accent-c font-bold hover:underline shrink-0">Download ⭳</a>
                @else
                    <span class="text-xs uppercase font-extrabold text-stone-500 rounded-full bg-stone-100 px-3 py-1.5 shrink-0">{{ $r->status }}</span>
                @endif
            </div>
        @empty
            <div class="p-8 text-center text-sm text-muted">Belum ada permintaan.</div>
        @endforelse
    </div>
</div>
@endsection
