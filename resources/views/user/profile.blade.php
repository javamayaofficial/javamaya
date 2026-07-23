@extends('layouts.member')
@section('title', 'Profil Saya')
@section('member_eyebrow', 'Profil')
@section('member_title', 'Profil Saya')
@section('member_subtitle', 'Identitas akun, ringkasan keamanan, dan kontrol privasi dirangkum dalam satu halaman profil member.')
@section('member_content')
<section class="jm-member-split jm-member-split--2">
    <div class="jm-member-panel">
        <h2 class="jm-member-panel-title">Lengkapi profil</h2>
        <p class="jm-member-panel-copy">Halaman ini sekarang bukan hanya ringkasan, tetapi juga tempat untuk melengkapi data profil setelah daftar lewat Google.</p>

        @if ($errors->any())
            <div class="mt-4 rounded-[18px] border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('user.profile.update') }}" class="mt-6 jm-member-stack">
            @csrf
            <div class="jm-member-card">
                <label class="jm-member-kv-label" for="profile-name">Nama lengkap</label>
                <input id="profile-name" name="name" value="{{ old('name', $user->name) }}" class="mt-3 w-full rounded-2xl border border-[rgba(0,59,143,0.12)] bg-white px-4 py-3 font-medium text-[#072347] outline-none focus:border-[rgba(255,145,0,0.45)]" required>
            </div>
            <div class="jm-member-card">
                <label class="jm-member-kv-label" for="profile-email">Email</label>
                <input id="profile-email" name="email" type="email" value="{{ old('email', $user->email) }}" class="mt-3 w-full rounded-2xl border border-[rgba(0,59,143,0.12)] bg-white px-4 py-3 font-medium text-[#072347] outline-none focus:border-[rgba(255,145,0,0.45)]" required>
            </div>
            <div class="jm-member-card">
                <label class="jm-member-kv-label" for="profile-phone">Nomor WhatsApp</label>
                <input id="profile-phone" name="phone" inputmode="numeric" placeholder="08xxxxxxxxxx" value="{{ old('phone', $user->phone) }}" class="mt-3 w-full rounded-2xl border border-[rgba(0,59,143,0.12)] bg-white px-4 py-3 font-medium text-[#072347] outline-none focus:border-[rgba(255,145,0,0.45)]" required>
                <div class="mt-2 text-sm text-muted">Nomor akan dinormalisasi ke format `62xxxxxxxx` untuk kebutuhan notifikasi dan OTP.</div>
            </div>
            <div class="jm-member-card">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <label class="jm-member-kv-label" for="profile-bank-name">Bank payout affiliate</label>
                        <div class="mt-2 text-sm text-muted">Dipakai saat pencairan komisi affiliate. Jika akun affiliate Anda aktif, data ini wajib lengkap.</div>
                    </div>
                    @if ($needsPayoutAccount)
                        <span class="jm-member-badge jm-member-badge--warn">Wajib dilengkapi</span>
                    @endif
                </div>
                <div class="mt-4 grid gap-4">
                    <input id="profile-bank-name" name="bank_name" placeholder="Contoh: BCA" value="{{ old('bank_name', $bankAccount?->bank_name) }}" class="w-full rounded-2xl border border-[rgba(0,59,143,0.12)] bg-white px-4 py-3 font-medium text-[#072347] outline-none focus:border-[rgba(255,145,0,0.45)]">
                    <input name="account_number" inputmode="numeric" placeholder="Nomor rekening" value="{{ old('account_number', $bankAccount?->account_number) }}" class="w-full rounded-2xl border border-[rgba(0,59,143,0.12)] bg-white px-4 py-3 font-medium text-[#072347] outline-none focus:border-[rgba(255,145,0,0.45)]">
                    <input name="account_holder" placeholder="Nama pemilik rekening" value="{{ old('account_holder', $bankAccount?->account_holder ?: $user->name) }}" class="w-full rounded-2xl border border-[rgba(0,59,143,0.12)] bg-white px-4 py-3 font-medium text-[#072347] outline-none focus:border-[rgba(255,145,0,0.45)]">
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm text-muted">Bergabung sejak {{ $user->created_at?->translatedFormat('d F Y') }}</div>
                <button class="jm-member-btn jm-member-btn--primary">Simpan profil</button>
            </div>
        </form>
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
            <div class="jm-member-card">
                <div class="jm-member-kv-label">Status profil</div>
                <div class="jm-member-kv-value">
                    {{ $user->phone && (! $user->affiliate || ! $needsPayoutAccount) ? 'Lengkap' : 'Perlu dilengkapi' }}
                </div>
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
