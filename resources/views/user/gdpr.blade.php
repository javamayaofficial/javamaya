@extends('layouts.member')
@section('title', 'Data Saya')
@section('member_eyebrow', 'Profil')
@section('member_title', 'Data Saya')
@section('member_subtitle', 'Pengaturan privasi, export data, dan permintaan penghapusan akun Anda dipusatkan di halaman ini.')
@section('member_content')
@if ($errors->any())
    <div class="jm-member-panel">
        <div class="rounded-[20px] border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ $errors->first() }}</div>
    </div>
@endif

<section class="jm-member-split jm-member-split--2">
    <form method="POST" action="{{ route('user.gdpr.store') }}">@csrf<input type="hidden" name="type" value="export">
        <button class="jm-member-panel w-full text-left">
            <h2 class="jm-member-panel-title">Export data saya</h2>
            <p class="jm-member-panel-copy">ZIP berisi profil, transaksi, akses, dan sertifikat. Link berlaku 24 jam setelah siap.</p>
            <span class="mt-6 inline-flex jm-member-btn jm-member-btn--primary">Ajukan export</span>
        </button>
    </form>

    <form method="POST" action="{{ route('user.gdpr.store') }}" onsubmit="return confirm('Yakin mengajukan penghapusan akun? Proses menunggu 30 hari.')">@csrf<input type="hidden" name="type" value="delete">
        <button class="jm-member-panel w-full text-left">
            <h2 class="jm-member-panel-title">Hapus akun saya</h2>
            <p class="jm-member-panel-copy">Permintaan penghapusan memiliki masa tunggu 30 hari sebelum diproses admin.</p>
            <span class="mt-6 inline-flex jm-member-btn jm-member-btn--ghost">Ajukan penghapusan</span>
        </button>
    </form>
</section>

<section class="jm-member-panel">
    <h2 class="jm-member-panel-title">Riwayat permintaan</h2>
    <p class="jm-member-panel-copy">Pantau status export data atau penghapusan akun yang pernah Anda ajukan.</p>

    <div class="mt-6 jm-member-list">
        @forelse ($requests as $requestItem)
            <div class="jm-member-card">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <div class="font-bold text-[#072347]">{{ $requestItem->type === 'export' ? 'Export data' : 'Penghapusan akun' }}</div>
                        <div class="mt-1 text-sm text-muted">{{ $requestItem->created_at->translatedFormat('d M Y') }}</div>
                    </div>
                    @if ($requestItem->type === 'export' && $requestItem->status === 'ready' && ! $requestItem->export_expires_at?->isPast())
                        <a href="{{ route('user.gdpr.download', $requestItem->id) }}" class="jm-member-btn jm-member-btn--primary">Download</a>
                    @else
                        <span class="jm-member-badge jm-member-badge--warn">{{ $requestItem->status }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="jm-member-empty">
                <div>
                    <div class="jm-member-empty-title">Belum ada permintaan</div>
                    <div class="jm-member-empty-copy">Semua riwayat export data dan penghapusan akun akan tampil di sini.</div>
                </div>
            </div>
        @endforelse
    </div>
</section>
@endsection
