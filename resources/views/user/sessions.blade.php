@extends('layouts.public')
@section('title', 'Perangkat & Sesi')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-10">
    <a href="{{ route('user.dashboard') }}" class="text-sm font-semibold text-muted hover:text-ink transition">← Dashboard</a>
    <h1 class="mt-2 text-3xl font-extrabold">Perangkat & Sesi</h1>
    <p class="mt-2 text-sm text-muted">Kelola di mana saja akun Anda sedang masuk.</p>

    <div class="mt-6 jm-card divide-y divide-ink/5 overflow-hidden">
        @foreach ($sessions as $s)
            <div class="px-5 py-4 flex items-center justify-between gap-3 text-sm">
                <div class="flex items-center gap-3.5 min-w-0">
                    <span class="h-10 w-10 rounded-xl bg-accent-soft grid place-items-center text-lg shrink-0">
                        {{ str_contains(strtolower((string) $s->device), 'mobile') || str_contains(strtolower((string) $s->device), 'android') || str_contains(strtolower((string) $s->device), 'iphone') ? '📱' : '💻' }}
                    </span>
                    <div class="min-w-0">
                        <div class="font-semibold truncate">{{ \Illuminate\Support\Str::limit($s->device, 55) }}</div>
                        <div class="text-xs text-stone-400 mt-0.5">IP {{ $s->ip }} • aktif {{ $s->last_active_at?->diffForHumans() }}</div>
                    </div>
                </div>
                @if ($s->session_id === $currentSessionId)
                    <span class="shrink-0 text-xs font-extrabold text-emerald-700 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1.5">Perangkat ini</span>
                @else
                    <form method="POST" action="{{ route('user.sessions.revoke', $s->id) }}">@csrf
                        <button class="shrink-0 text-rose-600 font-bold text-xs rounded-full border border-rose-200 bg-rose-50 px-3 py-2 min-h-[36px] hover:bg-rose-100 transition">Cabut</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
    <form method="POST" action="{{ route('user.sessions.revokeOthers') }}" class="mt-4">@csrf
        <button class="w-full rounded-xl border border-rose-200 text-rose-600 font-bold py-3 hover:bg-rose-50 transition">Cabut semua sesi lain</button>
    </form>

    @if (isset($trustedDevices) && $trustedDevices->isNotEmpty())
        <h2 class="mt-10 font-extrabold text-xl">Perangkat tepercaya 2FA</h2>
        <p class="mt-1 text-sm text-muted">Perangkat ini melewati verifikasi 2FA selama 30 hari.</p>
        <div class="mt-4 jm-card divide-y divide-ink/5 overflow-hidden">
            @foreach ($trustedDevices as $device)
                <div class="px-5 py-4 flex items-center justify-between gap-3 text-sm">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <span class="h-10 w-10 rounded-xl bg-accent-soft grid place-items-center text-lg shrink-0">🔐</span>
                        <div class="min-w-0">
                            <div class="font-semibold truncate">{{ \Illuminate\Support\Str::limit($device->user_agent, 50) }}</div>
                            <div class="text-xs text-stone-400 mt-0.5">IP {{ $device->ip }} • berlaku s/d {{ $device->expires_at->translatedFormat('d M Y') }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('user.sessions.revokeTrusted', $device->id) }}">@csrf
                        <button class="shrink-0 text-rose-600 font-bold text-xs rounded-full border border-rose-200 bg-rose-50 px-3 py-2 min-h-[36px] hover:bg-rose-100 transition">Cabut</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
