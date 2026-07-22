@extends('layouts.member')
@section('title', 'Perangkat & Sesi')
@section('member_eyebrow', 'Profil')
@section('member_title', 'Perangkat & Sesi')
@section('member_subtitle', 'Kelola perangkat yang sedang login, cabut sesi lama, dan amankan akun Anda dengan kontrol yang lebih rapi.')
@section('member_content')
<section class="jm-member-panel">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="jm-member-panel-title">Sesi aktif</h2>
            <p class="jm-member-panel-copy">Lihat semua perangkat yang sedang terhubung ke akun Anda saat ini.</p>
        </div>
        <form method="POST" action="{{ route('user.sessions.revokeOthers') }}">@csrf
            <button class="jm-member-btn jm-member-btn--ghost">Cabut sesi lain</button>
        </form>
    </div>

    <div class="mt-6 jm-member-list">
        @foreach ($sessions as $s)
            <div class="jm-member-card">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="font-bold text-[#072347]">{{ \Illuminate\Support\Str::limit($s->device, 70) }}</div>
                        <div class="mt-1 text-sm text-muted">IP {{ $s->ip }} · aktif {{ $s->last_active_at?->diffForHumans() }}</div>
                    </div>
                    @if ($s->session_id === $currentSessionId)
                        <span class="jm-member-badge jm-member-badge--ok">Perangkat ini</span>
                    @else
                        <form method="POST" action="{{ route('user.sessions.revoke', $s->id) }}">@csrf
                            <button class="jm-member-btn jm-member-btn--ghost">Cabut</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</section>

@if (isset($trustedDevices) && $trustedDevices->isNotEmpty())
    <section class="jm-member-panel">
        <h2 class="jm-member-panel-title">Perangkat tepercaya 2FA</h2>
        <p class="jm-member-panel-copy">Perangkat ini melewati verifikasi 2FA untuk periode terbatas.</p>
        <div class="mt-6 jm-member-list">
            @foreach ($trustedDevices as $device)
                <div class="jm-member-card">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="font-bold text-[#072347]">{{ \Illuminate\Support\Str::limit($device->user_agent, 70) }}</div>
                            <div class="mt-1 text-sm text-muted">IP {{ $device->ip }} · berlaku s/d {{ $device->expires_at->translatedFormat('d M Y') }}</div>
                        </div>
                        <form method="POST" action="{{ route('user.sessions.revokeTrusted', $device->id) }}">@csrf
                            <button class="jm-member-btn jm-member-btn--ghost">Cabut</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
@endsection
