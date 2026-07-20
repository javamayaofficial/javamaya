@extends('layouts.public')
@section('title', 'Downloads')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    <a href="{{ route('user.dashboard') }}" class="text-sm font-semibold text-muted hover:text-ink transition">← Dashboard</a>
    <h1 class="mt-2 text-3xl font-extrabold">Downloads</h1>
    <p class="mt-2 text-sm text-muted">Demi keamanan file, setiap link download berlaku {{ config('javamaya.download_token_ttl_minutes') }} menit sejak dibuat.</p>
    <div class="mt-6 space-y-3">
        @forelse ($files as $file)
            <div class="jm-card p-5 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3.5 min-w-0">
                    <span class="h-11 w-11 rounded-xl bg-accent-soft grid place-items-center text-xl shrink-0">📁</span>
                    <div class="min-w-0">
                        <div class="font-bold truncate">{{ $file->label }}</div>
                        <div class="text-xs text-stone-400 mt-0.5">{{ $file->product?->name }} • {{ number_format($file->file_size / 1024 / 1024, 1) }} MB</div>
                    </div>
                </div>
                <a href="{{ route('user.download.request', $file->id) }}"
                   class="shrink-0 btn-accent text-white font-bold rounded-xl px-5 py-2.5 min-h-[44px] flex items-center shadow-cta hover:opacity-90 transition">Download</a>
            </div>
        @empty
            <div class="jm-card p-10 text-center">
                <span class="mx-auto h-14 w-14 rounded-2xl bg-accent-soft grid place-items-center text-2xl">⬇️</span>
                <p class="mt-4 font-bold">Belum ada file</p>
                <p class="mt-1 text-sm text-muted">File dari produk yang Anda beli akan tersedia di sini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
