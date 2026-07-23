@extends('layouts.public', [
    'hideChrome' => true,
    'hideFooter' => true,
    'bodyClass' => 'jm-member-body',
])

@push('head')
<style>
    .jm-member-body {
        background:
            radial-gradient(circle at top left, rgba(0, 59, 143, 0.08), transparent 24%),
            radial-gradient(circle at top right, rgba(255, 145, 0, 0.12), transparent 18%),
            linear-gradient(180deg, #f7f4ec 0%, #f3efe5 100%);
    }

    .jm-member-app {
        min-height: 100vh;
        padding: 1rem;
    }

    .jm-member-shell {
        display: grid;
        gap: 1rem;
        max-width: 1440px;
        margin: 0 auto;
    }

    .jm-member-sidebar {
        position: relative;
        overflow: hidden;
        border-radius: 28px;
        border: 1px solid rgba(120, 144, 181, 0.22);
        background:
            linear-gradient(180deg, rgba(4, 16, 43, 0.98), rgba(7, 26, 61, 0.98)),
            linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0));
        color: #f8fbff;
        box-shadow: 0 24px 60px rgba(5, 21, 55, 0.26);
    }

    .jm-member-sidebar::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at top, rgba(59, 176, 255, 0.18), transparent 28%),
            radial-gradient(circle at bottom, rgba(255, 145, 0, 0.14), transparent 24%);
        pointer-events: none;
    }

    .jm-member-sidebar > * {
        position: relative;
        z-index: 1;
    }

    .jm-member-brand {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 1.2rem 1.2rem 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .jm-member-brandmark {
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 1rem;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #ffb347, #ff9100);
        color: #09162d;
        font-weight: 900;
        letter-spacing: 0.08em;
    }

    .jm-member-brandname {
        font-size: 1rem;
        font-weight: 800;
        letter-spacing: 0.02em;
    }

    .jm-member-brandcopy {
        margin-top: 0.15rem;
        font-size: 0.78rem;
        color: rgba(235, 244, 255, 0.64);
    }

    .jm-member-nav {
        display: grid;
        gap: 0.5rem;
        padding: 1rem;
    }

    .jm-member-link {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.9rem 1rem;
        border-radius: 18px;
        color: rgba(241, 246, 255, 0.78);
        border: 1px solid transparent;
        transition: background 0.2s ease, transform 0.2s ease, border-color 0.2s ease, color 0.2s ease;
    }

    .jm-member-link:hover {
        color: #fff;
        transform: translateY(-1px);
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.08);
    }

    .jm-member-link.is-active {
        color: #fff;
        background: linear-gradient(135deg, rgba(27, 58, 122, 0.95), rgba(8, 33, 79, 0.95));
        border-color: rgba(117, 168, 255, 0.28);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08), 0 16px 32px rgba(0, 0, 0, 0.22);
    }

    .jm-member-link-badge {
        width: 2.2rem;
        height: 2.2rem;
        border-radius: 0.9rem;
        display: grid;
        place-items: center;
        background: rgba(255, 255, 255, 0.07);
        color: #9dd7ff;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.06em;
        flex-shrink: 0;
    }

    .jm-member-link.is-active .jm-member-link-badge {
        background: linear-gradient(135deg, #35c0b6, #2ba5a2);
        color: #051728;
    }

    .jm-member-link-label {
        display: block;
        font-weight: 700;
        font-size: 0.95rem;
    }

    .jm-member-link-copy {
        display: block;
        margin-top: 0.2rem;
        font-size: 0.75rem;
        color: rgba(223, 234, 250, 0.56);
    }

    .jm-member-sidebar-foot {
        padding: 0 1rem 1rem;
    }

    .jm-member-usercard {
        border-radius: 22px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(255, 255, 255, 0.04);
        padding: 1rem;
    }

    .jm-member-usermeta {
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }

    .jm-member-avatar {
        width: 2.8rem;
        height: 2.8rem;
        border-radius: 999px;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #eff6ff, #ffd9aa);
        color: #072347;
        font-size: 0.95rem;
        font-weight: 900;
    }

    .jm-member-usercard-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: #fff;
    }

    .jm-member-usercard-copy {
        margin-top: 0.15rem;
        font-size: 0.74rem;
        color: rgba(223, 234, 250, 0.56);
    }

    .jm-member-logout {
        margin-top: 1rem;
        width: 100%;
        border-radius: 16px;
        border: 1px solid rgba(255, 145, 0, 0.18);
        background: rgba(255, 145, 0, 0.08);
        color: #ffcc89;
        font-weight: 700;
        padding: 0.85rem 1rem;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .jm-member-logout:hover {
        background: rgba(255, 145, 0, 0.14);
        transform: translateY(-1px);
    }

    .jm-member-main {
        display: grid;
        gap: 1rem;
    }

    .jm-member-topbar,
    .jm-member-panel,
    .jm-member-card {
        border-radius: 28px;
        border: 1px solid rgba(0, 59, 143, 0.08);
        background: rgba(255, 252, 247, 0.94);
        box-shadow: 0 22px 55px rgba(0, 38, 94, 0.08);
    }

    .jm-member-topbar {
        padding: 1.35rem 1.35rem 1.2rem;
    }

    .jm-member-eyebrow {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.22em;
        color: rgba(0, 59, 143, 0.48);
        text-transform: uppercase;
    }

    .jm-member-heading {
        margin-top: 0.45rem;
        font-size: clamp(1.8rem, 4vw, 2.7rem);
        line-height: 1;
        letter-spacing: -0.04em;
        font-weight: 900;
        color: #072347;
    }

    .jm-member-subtitle {
        margin-top: 0.6rem;
        max-width: 60ch;
        color: rgba(7, 35, 71, 0.68);
        line-height: 1.65;
    }

    .jm-member-topbar-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
        margin-top: 1rem;
    }

    .jm-member-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border-radius: 999px;
        border: 1px solid rgba(0, 59, 143, 0.08);
        background: #fff;
        color: #072347;
        padding: 0.65rem 0.9rem;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .jm-member-chip-key {
        color: rgba(7, 35, 71, 0.48);
        font-weight: 700;
    }

    .jm-member-content {
        display: grid;
        gap: 1rem;
    }

    .jm-member-panel {
        padding: 1.15rem;
    }

    .jm-member-panel-title {
        font-size: 1.18rem;
        font-weight: 800;
        color: #072347;
        letter-spacing: -0.02em;
    }

    .jm-member-panel-copy {
        margin-top: 0.35rem;
        color: rgba(7, 35, 71, 0.6);
        font-size: 0.92rem;
    }

    .jm-member-grid {
        display: grid;
        gap: 1rem;
    }

    .jm-member-stats {
        display: grid;
        gap: 1rem;
    }

    .jm-member-stat {
        border-radius: 24px;
        padding: 1.1rem;
        border: 1px solid rgba(0, 59, 143, 0.08);
        background: #fffdfb;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }

    .jm-member-stat--accent {
        background: linear-gradient(135deg, #2db9aa, #31b8c2);
        color: #f8feff;
        border-color: transparent;
    }

    .jm-member-stat--accent .jm-member-stat-label,
    .jm-member-stat--accent .jm-member-stat-copy {
        color: rgba(245, 253, 255, 0.78);
    }

    .jm-member-stat-label {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: rgba(7, 35, 71, 0.48);
    }

    .jm-member-stat-value {
        margin-top: 0.55rem;
        font-size: clamp(1.8rem, 3vw, 2.35rem);
        line-height: 1;
        font-weight: 900;
        letter-spacing: -0.04em;
    }

    .jm-member-stat-copy {
        margin-top: 0.45rem;
        color: rgba(7, 35, 71, 0.6);
        font-size: 0.84rem;
        line-height: 1.6;
    }

    .jm-member-card {
        padding: 1rem;
    }

    .jm-member-card + .jm-member-card {
        margin-top: 0.9rem;
    }

    .jm-member-list {
        display: grid;
        gap: 0.85rem;
    }

    .jm-member-list-item {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.9rem 0;
        border-top: 1px solid rgba(0, 59, 143, 0.08);
    }

    .jm-member-list-item:first-child {
        border-top: 0;
        padding-top: 0;
    }

    .jm-member-list-item:last-child {
        padding-bottom: 0;
    }

    .jm-member-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 0.45rem 0.78rem;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .jm-member-badge--ok {
        background: rgba(49, 184, 122, 0.12);
        color: #0f7f53;
    }

    .jm-member-badge--warn {
        background: rgba(255, 145, 0, 0.12);
        color: #b66400;
    }

    .jm-member-badge--danger {
        background: rgba(221, 87, 122, 0.12);
        color: #b23d5f;
    }

    .jm-member-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        overflow: hidden;
    }

    .jm-member-table th,
    .jm-member-table td {
        padding: 1rem 1rem;
        text-align: left;
        border-top: 1px solid rgba(0, 59, 143, 0.08);
        vertical-align: top;
    }

    .jm-member-table tr:first-child th,
    .jm-member-table tr:first-child td {
        border-top: 0;
    }

    .jm-member-table th {
        font-size: 0.72rem;
        color: rgba(7, 35, 71, 0.48);
        font-weight: 800;
        letter-spacing: 0.18em;
        text-transform: uppercase;
    }

    .jm-member-progress {
        height: 0.7rem;
        overflow: hidden;
        border-radius: 999px;
        background: rgba(0, 59, 143, 0.08);
    }

    .jm-member-progress > span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #003b8f, #ff9100);
    }

    .jm-member-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        border-radius: 16px;
        padding: 0.85rem 1.1rem;
        font-size: 0.88rem;
        font-weight: 800;
        transition: transform 0.2s ease, filter 0.2s ease, background 0.2s ease;
    }

    .jm-member-btn:hover {
        transform: translateY(-1px);
    }

    .jm-member-btn--primary {
        background: linear-gradient(135deg, #ffad35, #ff9100);
        color: #fff;
        box-shadow: 0 18px 30px rgba(255, 145, 0, 0.2);
    }

    .jm-member-btn--ghost {
        background: #fff;
        color: #072347;
        border: 1px solid rgba(0, 59, 143, 0.1);
    }

    .jm-member-empty {
        display: grid;
        place-items: center;
        min-height: 240px;
        text-align: center;
        color: rgba(7, 35, 71, 0.62);
    }

    .jm-member-empty-title {
        margin-top: 1rem;
        font-size: 1.2rem;
        font-weight: 800;
        color: #072347;
    }

    .jm-member-empty-copy {
        margin-top: 0.45rem;
        max-width: 34ch;
        line-height: 1.7;
    }

    .jm-member-stack {
        display: grid;
        gap: 1rem;
    }

    .jm-member-kv {
        display: grid;
        gap: 0.25rem;
    }

    .jm-member-kv-label {
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: rgba(7, 35, 71, 0.45);
    }

    .jm-member-kv-value {
        font-size: 1rem;
        font-weight: 700;
        color: #072347;
    }

    .jm-member-split {
        display: grid;
        gap: 1rem;
    }

    @media (min-width: 768px) {
        .jm-member-app {
            padding: 1.25rem;
        }

        .jm-member-topbar {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1.25rem;
            padding: 1.5rem;
        }

        .jm-member-stats {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .jm-member-grid--2 {
            grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.8fr);
        }

        .jm-member-split--2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1100px) {
        .jm-member-shell {
            grid-template-columns: 290px minmax(0, 1fr);
        }

        .jm-member-sidebar {
            position: sticky;
            top: 1rem;
            min-height: calc(100vh - 2rem);
            display: flex;
            flex-direction: column;
        }

        .jm-member-nav {
            padding-top: 1.1rem;
            flex: 1;
            align-content: start;
        }

        .jm-member-sidebar-foot {
            margin-top: auto;
        }
    }
</style>
@endpush

@section('content')
@php
    $memberUser = auth()->user();
    $memberAffiliate = $memberUser?->affiliate;
    $memberBankAccount = $memberUser?->bankAccounts()->latest('id')->first();
    $needsPhoneCompletion = blank($memberUser?->phone);
    $needsAffiliatePayoutAccount = $memberAffiliate
        && (! $memberBankAccount
            || blank($memberBankAccount->bank_name)
            || blank($memberBankAccount->account_number)
            || blank($memberBankAccount->account_holder));
    $memberTitle = trim($__env->yieldContent('member_title'));
    $memberEyebrow = trim($__env->yieldContent('member_eyebrow'));
    $memberSubtitle = trim($__env->yieldContent('member_subtitle'));

    $memberTitle = $memberTitle !== '' ? $memberTitle : (trim($__env->yieldContent('title')) ?: 'Dashboard Member');
    $memberEyebrow = $memberEyebrow !== '' ? $memberEyebrow : 'Member Area';
    $memberSubtitle = $memberSubtitle !== '' ? $memberSubtitle : 'Kelola akses, transaksi, dan pengalaman belajar Anda dalam satu dashboard premium.';

    $memberNav = [
        ['route' => 'user.dashboard', 'patterns' => ['user.dashboard'], 'short' => 'DB', 'label' => 'Dashboard', 'copy' => 'Ringkasan akun dan aktivitas'],
        ['route' => 'user.member.area', 'patterns' => ['user.member.area', 'user.class.*'], 'short' => 'AM', 'label' => 'Area Member', 'copy' => 'Konten, kelas, dan akses aktif'],
        ['route' => 'user.transactions', 'patterns' => ['user.transactions'], 'short' => 'TR', 'label' => 'Transaksi', 'copy' => 'Riwayat order dan invoice'],
        ['route' => 'user.downloads', 'patterns' => ['user.downloads', 'user.download.*'], 'short' => 'DL', 'label' => 'File Downloads', 'copy' => 'Unduhan aset dan produk digital'],
        ['route' => 'user.certificates', 'patterns' => ['user.certificates', 'user.certificate.*'], 'short' => 'SR', 'label' => 'Sertifikat Saya', 'copy' => 'Daftar sertifikat pembelajaran'],
        ['route' => 'user.affiliate', 'patterns' => ['user.affiliate'], 'short' => 'AF', 'label' => 'Affiliate', 'copy' => 'Status komisi dan referral'],
        ['route' => 'user.profile', 'patterns' => ['user.profile', 'user.sessions.*', 'user.gdpr.*'], 'short' => 'PR', 'label' => 'Profil', 'copy' => 'Identitas, keamanan, dan privasi'],
    ];
@endphp
<div class="jm-member-app">
    <div class="jm-member-shell">
        <aside class="jm-member-sidebar">
            <div class="jm-member-brand">
                <div class="jm-member-brandmark">JM</div>
                <div>
                    <div class="jm-member-brandname">{{ jm_setting('store_name', config('app.name')) }}</div>
                    <div class="jm-member-brandcopy">Premium member dashboard</div>
                </div>
            </div>

            <nav class="jm-member-nav">
                @foreach ($memberNav as $item)
                    @php $active = request()->routeIs(...$item['patterns']); @endphp
                    <a href="{{ route($item['route']) }}" class="jm-member-link {{ $active ? 'is-active' : '' }}">
                        <span class="jm-member-link-badge">{{ $item['short'] }}</span>
                        <span>
                            <span class="jm-member-link-label">{{ $item['label'] }}</span>
                            <span class="jm-member-link-copy">{{ $item['copy'] }}</span>
                        </span>
                    </a>
                @endforeach
            </nav>

            <div class="jm-member-sidebar-foot">
                <div class="jm-member-usercard">
                    <div class="jm-member-usermeta">
                        <div class="jm-member-avatar">{{ mb_strtoupper(mb_substr($memberUser?->name ?? 'J', 0, 1)) }}</div>
                        <div class="min-w-0">
                            <div class="jm-member-usercard-name truncate">{{ $memberUser?->name }}</div>
                            <div class="jm-member-usercard-copy truncate">{{ $memberUser?->email }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button class="jm-member-logout">Keluar dari akun</button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="jm-member-main">
            <section class="jm-member-topbar">
                <div>
                    <div class="jm-member-eyebrow">{{ $memberEyebrow }}</div>
                    <h1 class="jm-member-heading">{{ $memberTitle }}</h1>
                    <p class="jm-member-subtitle">{{ $memberSubtitle }}</p>
                </div>
                <div class="jm-member-topbar-meta">
                    <div class="jm-member-chip">
                        <span class="jm-member-chip-key">Tanggal</span>
                        <span>{{ now()->translatedFormat('d M Y') }}</span>
                    </div>
                    <div class="jm-member-chip">
                        <span class="jm-member-chip-key">Akun</span>
                        <span>{{ explode(' ', trim($memberUser?->name ?? 'Member'))[0] }}</span>
                    </div>
                </div>
            </section>

            <div class="jm-member-content">
                @if ($needsPhoneCompletion || $needsAffiliatePayoutAccount)
                    <section class="jm-member-panel" style="border-color: rgba(255, 145, 0, 0.18); background: linear-gradient(135deg, rgba(255, 247, 236, 0.98), rgba(255, 252, 247, 0.98));">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <div class="jm-member-panel-title">Lengkapi profil Anda</div>
                                <p class="jm-member-panel-copy">
                                    @if ($needsPhoneCompletion && $needsAffiliatePayoutAccount)
                                        Nomor WhatsApp dan rekening payout affiliate Anda belum lengkap. Lengkapi keduanya agar akun dan pencairan komisi siap dipakai.
                                    @elseif ($needsPhoneCompletion)
                                        Akun Anda sudah masuk, tetapi nomor WhatsApp belum diisi. Lengkapi profil dulu agar member area dan notifikasi bekerja lebih utuh.
                                    @else
                                        Data rekening payout affiliate belum lengkap. Lengkapi bank, nomor rekening, dan nama pemilik rekening agar pencairan komisi siap diproses.
                                    @endif
                                </p>
                            </div>
                            @unless (request()->routeIs('user.profile'))
                                <a href="{{ route('user.profile') }}" class="jm-member-btn jm-member-btn--primary">Lengkapi sekarang</a>
                            @endunless
                        </div>
                    </section>
                @endif
                @yield('member_content')
            </div>
        </main>
    </div>
</div>
@endsection
