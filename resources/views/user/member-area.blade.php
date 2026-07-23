@extends('layouts.member')
@section('title', 'Member Area')
@section('member_eyebrow', 'Member Area')
@section('member_title', 'Member Area')
@section('member_subtitle', 'Semua kelas, akses aktif, dan konten premium Anda dikumpulkan dalam satu halaman khusus seperti portal member modern.')
@section('member_content')
<section class="jm-member-panel">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="jm-member-panel-title">Akses konten</h2>
            <p class="jm-member-panel-copy">Fokus ke konten yang sudah Anda miliki dan bisa dibuka sekarang juga.</p>
        </div>
        <span class="jm-member-badge jm-member-badge--ok">{{ $access->filter->isActive()->count() }} aktif</span>
    </div>

    @if ($classes->isEmpty() && $access->isEmpty())
        <div class="jm-member-empty">
            <div>
                <div class="jm-member-empty-title">Belum ada akses aktif</div>
                <div class="jm-member-empty-copy">Semua kelas, lisensi, dan produk digital yang Anda beli akan muncul otomatis di area ini.</div>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                    @if (blank(auth()->user()?->phone))
                        <a href="{{ route('user.profile') }}" class="inline-flex jm-member-btn jm-member-btn--primary">Lengkapi profil</a>
                    @endif
                    <a href="{{ route('home') }}" class="inline-flex jm-member-btn jm-member-btn--ghost">Buka homepage</a>
                </div>
            </div>
        </div>
    @else
        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            @foreach ($classes as $item)
                <a href="{{ route('user.class.show', $item['class']->slug) }}" class="jm-member-card hover:no-underline">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-[rgba(7,35,71,0.45)]">Kelas</div>
                            <div class="mt-2 font-bold text-[#072347]">{{ $item['class']->title }}</div>
                        </div>
                        <span class="jm-member-badge {{ $item['progress'] >= 100 ? 'jm-member-badge--ok' : 'jm-member-badge--warn' }}">{{ $item['progress'] }}%</span>
                    </div>
                    <div class="mt-5 jm-member-progress"><span style="width: {{ max(4, $item['progress']) }}%"></span></div>
                    <div class="mt-3 text-sm text-muted">{{ $item['progress'] >= 100 ? 'Materi selesai dan sertifikat siap.' : 'Lanjutkan materi terakhir Anda.' }}</div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            <h3 class="font-bold text-[#072347]">Akses produk</h3>
            <div class="mt-4 jm-member-list">
                @foreach ($access as $item)
                    <div class="jm-member-card">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="font-bold text-[#072347]">{{ $item->product?->name }}</div>
                                <div class="mt-1 text-sm text-muted">{{ strtoupper($item->product?->type ?? 'digital') }} access</div>
                            </div>
                            @if (! $item->isActive())
                                <span class="jm-member-badge jm-member-badge--danger">Nonaktif</span>
                            @elseif ($item->expires_at)
                                <span class="jm-member-badge jm-member-badge--warn">s/d {{ $item->expires_at->translatedFormat('d M Y') }}</span>
                            @else
                                <span class="jm-member-badge jm-member-badge--ok">Selamanya</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>
@endsection
