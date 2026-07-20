@extends('layouts.public')
@section('title', 'Dashboard')
@section('content')
@php
    $activeAccess = $access->filter->isActive();
    $paidOrders = $user->orders()->where('status', 'paid')->count();
@endphp
<div class="max-w-4xl mx-auto px-4 py-10">

    {{-- Sapaan + ringkasan --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-accent-c">{{ now()->translatedFormat('l, d F Y') }}</p>
            <h1 class="mt-1 text-3xl font-extrabold">Halo, {{ explode(' ', $user->name)[0] }} 👋</h1>
        </div>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="text-sm font-semibold text-muted hover:text-ink transition px-3 py-2">Keluar →</button>
        </form>
    </div>

    {{-- Stat ringkas --}}
    <div class="mt-6 grid grid-cols-3 gap-3">
        <div class="jm-card p-4 text-center">
            <div class="text-2xl font-extrabold money">{{ $classes->count() }}</div>
            <div class="mt-0.5 text-xs font-semibold text-muted">Kelas</div>
        </div>
        <div class="jm-card p-4 text-center">
            <div class="text-2xl font-extrabold money">{{ $activeAccess->count() }}</div>
            <div class="mt-0.5 text-xs font-semibold text-muted">Akses aktif</div>
        </div>
        <div class="jm-card p-4 text-center">
            <div class="text-2xl font-extrabold money">{{ $paidOrders }}</div>
            <div class="mt-0.5 text-xs font-semibold text-muted">Pembelian</div>
        </div>
    </div>

    {{-- Navigasi --}}
    <nav class="mt-6 flex flex-wrap gap-2 text-sm font-semibold">
        @foreach ([
            ['route' => 'user.transactions', 'icon' => '🧾', 'label' => 'Transaksi'],
            ['route' => 'user.downloads', 'icon' => '⬇️', 'label' => 'Downloads'],
            ['route' => 'user.sessions', 'icon' => '📱', 'label' => 'Perangkat'],
            ['route' => 'user.gdpr', 'icon' => '🛡️', 'label' => 'Data Saya'],
        ] as $item)
            <a href="{{ route($item['route']) }}"
               class="inline-flex items-center gap-2 rounded-full bg-white border border-ink/10 px-4 py-2.5 hover:border-ink/30 hover:shadow-card transition">
                <span class="text-base leading-none">{{ $item['icon'] }}</span> {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    {{-- Kelas saya --}}
    <div class="mt-10 flex items-baseline justify-between">
        <h2 class="font-extrabold text-xl">Kelas saya</h2>
        @if ($classes->isNotEmpty())
            <a href="{{ route('home') }}" class="text-sm font-semibold text-accent-c hover:underline">Jelajahi kelas lain →</a>
        @endif
    </div>
    @if ($classes->isEmpty())
        <div class="mt-4 jm-card p-10 text-center">
            <span class="mx-auto h-14 w-14 rounded-2xl bg-accent-soft grid place-items-center text-2xl">🎓</span>
            <p class="mt-4 font-bold">Belum ada kelas</p>
            <p class="mt-1 text-sm text-muted">Mulai belajar hari ini — pilih kelas pertama Anda dari katalog.</p>
            <a href="{{ route('home') }}" class="mt-5 inline-flex btn-accent text-white font-bold rounded-xl px-6 py-3 shadow-cta hover:opacity-90 transition">Lihat katalog</a>
        </div>
    @else
        <div class="mt-4 grid sm:grid-cols-2 gap-4">
            @foreach ($classes as $item)
                <a href="{{ route('user.class.show', $item['class']->slug) }}" class="jm-card p-5 group">
                    <div class="flex items-start justify-between gap-3">
                        <div class="font-bold leading-snug group-hover:text-accent-c transition">{{ $item['class']->title }}</div>
                        <span class="shrink-0 text-xs font-extrabold rounded-full px-2.5 py-1 {{ $item['progress'] >= 100 ? 'bg-emerald-100 text-emerald-700' : 'bg-accent-soft text-accent-c' }}">
                            {{ $item['progress'] >= 100 ? '✓ Selesai' : $item['progress'].'%' }}
                        </span>
                    </div>
                    <div class="mt-4 h-2 rounded-full bg-stone-100 overflow-hidden">
                        <div class="h-full btn-accent rounded-full transition-all duration-500" style="width: {{ max(3, $item['progress']) }}%"></div>
                    </div>
                    <div class="mt-2.5 text-xs font-semibold text-muted">
                        {{ $item['progress'] >= 100 ? 'Sertifikat tersedia 🎓' : 'Lanjutkan belajar →' }}
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Akses saya --}}
    <h2 class="mt-10 font-extrabold text-xl">Akses saya</h2>
    <div class="mt-4 jm-card divide-y divide-ink/5 overflow-hidden">
        @forelse ($access as $a)
            <div class="px-5 py-4 flex items-center justify-between gap-3 text-sm">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="h-9 w-9 rounded-xl bg-accent-soft grid place-items-center text-base shrink-0">
                        {{ ['digital' => '💾', 'downloadable' => '📁', 'class' => '🎓', 'bundle' => '🎁'][$a->product?->type] ?? '💾' }}
                    </span>
                    <span class="font-semibold truncate">{{ $a->product?->name }}</span>
                </div>
                @if (! $a->isActive())
                    <a href="{{ $a->product ? route('product.show', $a->product->slug) : '#' }}"
                       class="shrink-0 text-rose-600 font-bold text-xs rounded-full bg-rose-50 border border-rose-200 px-3 py-1.5 hover:bg-rose-100 transition">Perpanjang →</a>
                @elseif ($a->expires_at)
                    <span class="shrink-0 text-amber-700 text-xs font-bold rounded-full bg-amber-50 border border-amber-200 px-3 py-1.5">s/d {{ $a->expires_at->translatedFormat('d M Y') }}</span>
                @else
                    <span class="shrink-0 text-emerald-700 text-xs font-bold rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1.5">Selamanya</span>
                @endif
            </div>
        @empty
            <div class="p-10 text-center">
                <span class="mx-auto h-14 w-14 rounded-2xl bg-accent-soft grid place-items-center text-2xl">🔑</span>
                <p class="mt-4 font-bold">Belum ada akses aktif</p>
                <p class="mt-1 text-sm text-muted">Akses produk muncul di sini otomatis setelah pembelian pertama Anda.</p>
                <a href="{{ route('home') }}" class="mt-5 inline-flex btn-accent text-white font-bold rounded-xl px-6 py-3 shadow-cta hover:opacity-90 transition">Belanja sekarang</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
