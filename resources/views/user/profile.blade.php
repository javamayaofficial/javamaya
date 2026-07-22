@extends('layouts.member')
@section('title', 'Profil Saya')
@section('member_eyebrow', 'Profil')
@section('member_title', 'Profil Saya')
@section('member_subtitle', 'Identitas akun, ringkasan keamanan, dan kontrol privasi dirangkum dalam satu halaman profil member.')
@section('member_content')
<section class="jm-member-split jm-member-split--2">
    <div class="jm-member-panel">
        <h2 class="jm-member-panel-title">Identitas akun</h2>
        <p class="jm-member-panel-copy">Informasi utama yang tersimpan pada akun Anda saat ini.</p>
        <div class="mt-6 jm-member-stack">
            <div class="jm-member-card">
                <div class="jm-member-kv">
                    <div class="jm-member-kv-label">Nama lengkap</div>
                    <div class="jm-member-kv-value">{{ $user->name }}</div>
                </div>
            </div>
            <div class="jm-member-card">
                <div class="jm-member-kv">
                    <div class="jm-member-kv-label">Email</div>
                    <div class="jm-member-kv-value">{{ $user->email }}</div>
                </div>
            </div>
            <div class="jm-member-card">
                <div class="jm-member-kv">
                    <div class="jm-member-kv-label">Nomor WhatsApp</div>
                    <div class="jm-member-kv-value">{{ $user->phone ?: 'Belum diisi' }}</div>
                </div>
            </div>
            <div class="jm-member-card">
                <div class="jm-member-kv">
                    <div class="jm-member-kv-label">Bergabung sejak</div>
                    <div class="jm-member-kv-value">{{ $user->created_at?->translatedFormat('d F Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="jm-member-panel">
        <h2 class="jm-member-panel-title">Status akun</h2>
        <p class="jm-member-panel-copy">Ringkasan cepat performa akun member Anda.</p>
        <div class="mt-6 jm-member-stack">
            <div class="jm-member-card">
                <div class="jm-member-kv-label">Akses aktif</div>
                <div class="jm-member-kv-value">{{ $activeAccess }}</div>
            </div>
            <div class="jm-member-card">
                <div class="jm-member-kv-label">Total transaksi</div>
                <div class="jm-member-kv-value">{{ $ordersCount }}</div>
            </div>
            <div class="jm-member-card">
                <div class="jm-member-kv-label">Sesi aktif</div>
                <div class="jm-member-kv-value">{{ $activeSessions }}</div>
            </div>
            <div class="jm-member-card">
                <div class="jm-member-kv-label">Perangkat tepercaya</div>
                <div class="jm-member-kv-value">{{ $trustedDevices }}</div>
            </div>
        </div>
    </div>
</section>

<section class="jm-member-split jm-member-split--2">
    <div class="jm-member-panel">
        <h2 class="jm-member-panel-title">Keamanan akun</h2>
        <p class="jm-member-panel-copy">Cabut sesi lama, lihat perangkat tepercaya, dan pastikan akun tetap aman.</p>
        <div class="mt-6">
            <a href="{{ route('user.sessions') }}" class="jm-member-btn jm-member-btn--primary">Buka pengaturan sesi</a>
        </div>
    </div>

    <div class="jm-member-panel">
        <h2 class="jm-member-panel-title">Privasi dan data</h2>
        <p class="jm-member-panel-copy">Export data atau ajukan penghapusan akun dari pusat kontrol privasi.</p>
        <div class="mt-6">
            <a href="{{ route('user.gdpr') }}" class="jm-member-btn jm-member-btn--ghost">Kelola data saya</a>
        </div>
    </div>
</section>
@endsection
