@extends('layouts.member')
@section('title', 'Dashboard Member')
@section('member_eyebrow', 'Dashboard')
@section('member_title', 'Halo, ' . (explode(' ', trim($user->name))[0] ?? $user->name))
@section('member_subtitle', 'Ringkasan akun, akses premium, dan aktivitas belajar Anda dirangkum dalam dashboard bergaya app yang lebih rapi dan fokus.')
@section('member_content')
@php
    $activeAccess = $access->filter->isActive();
    $paidOrders = $user->orders()->where('status', 'paid')->count();
    $firstName = explode(' ', trim($user->name))[0] ?? $user->name;
    $latestOrder = $orders->first();
    $completionRate = $classes->count() ? round($classes->avg('progress')) : 0;
    $totalSpent = (int) $user->orders()->where('status', 'paid')->sum('total_payable');
@endphp
<section class="jm-member-stats">
    <div class="jm-member-stat jm-member-stat--accent">
        <div class="jm-member-stat-label">Total order</div>
        <div class="jm-member-stat-value">{{ $paidOrders }}</div>
        <div class="jm-member-stat-copy">Jumlah transaksi sukses yang sudah tercatat di akun Anda.</div>
    </div>
    <div class="jm-member-stat">
        <div class="jm-member-stat-label">Total belanja</div>
        <div class="jm-member-stat-value">{{ jm_rupiah($totalSpent) }}</div>
        <div class="jm-member-stat-copy">Akumulasi pembayaran lunas untuk produk, kelas, dan lisensi.</div>
    </div>
    <div class="jm-member-stat">
        <div class="jm-member-stat-label">Akses aktif</div>
        <div class="jm-member-stat-value">{{ $activeAccess->count() }}</div>
        <div class="jm-member-stat-copy">Semua akses premium yang saat ini bisa langsung Anda gunakan.</div>
    </div>
</section>

<section class="jm-member-grid jm-member-grid--2">
    <div class="jm-member-panel">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="jm-member-panel-title">Transaksi terbaru</h2>
                <p class="jm-member-panel-copy">Ringkas, cepat dibaca, dan langsung fokus ke aktivitas pembelian terakhir Anda.</p>
            </div>
            <a href="{{ route('user.transactions') }}" class="jm-member-btn jm-member-btn--ghost">Semua</a>
        </div>

        <div class="mt-6">
            @forelse ($orders as $order)
                <div class="jm-member-list-item">
                    <div class="min-w-0">
                        <div class="font-bold truncate">{{ $order->product_name ?? $order->order_ref }}</div>
                        <div class="mt-1 text-sm text-muted">{{ $order->created_at?->translatedFormat('d M Y') }}</div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="font-bold">{{ jm_rupiah((int) $order->total_payable) }}</div>
                        <span class="mt-2 jm-member-badge {{ $order->status === 'paid' ? 'jm-member-badge--ok' : ($order->status === 'pending' ? 'jm-member-badge--warn' : 'jm-member-badge--danger') }}">
                            {{ $order->status === 'paid' ? 'Lunas' : strtoupper($order->status) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="jm-member-empty">
                    <div>
                        <div class="jm-member-empty-title">Belum ada transaksi</div>
                        <div class="jm-member-empty-copy">Saat transaksi pertama Anda masuk, daftar order akan muncul di panel ini.</div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <div class="jm-member-stack">
        <div class="jm-member-panel">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="jm-member-panel-title">Snapshot akun</h2>
                    <p class="jm-member-panel-copy">Versi ringkas seperti executive summary untuk akun member Anda.</p>
                </div>
                <span class="jm-member-badge jm-member-badge--ok">Aktif</span>
            </div>

            <div class="mt-6 jm-member-stack">
                <div class="jm-member-card">
                    <div class="jm-member-kv-label">Member</div>
                    <div class="jm-member-kv-value">{{ $user->name }}</div>
                </div>
                <div class="jm-member-card">
                    <div class="jm-member-kv-label">Progress rata-rata</div>
                    <div class="jm-member-kv-value">{{ $completionRate }}%</div>
                    <div class="mt-3 jm-member-progress"><span style="width: {{ max(4, $completionRate) }}%"></span></div>
                </div>
                <div class="jm-member-card">
                    <div class="jm-member-kv-label">Pembelian terakhir</div>
                    <div class="jm-member-kv-value">{{ $latestOrder ? ($latestOrder->product_name ?? $latestOrder->order_ref) : 'Belum ada transaksi' }}</div>
                    <div class="mt-1 text-sm text-muted">
                        {{ $latestOrder ? $latestOrder->created_at?->translatedFormat('d M Y, H:i') : 'Akan tampil otomatis setelah order pertama selesai.' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="jm-member-panel">
            <h2 class="jm-member-panel-title">Quick actions</h2>
            <p class="jm-member-panel-copy">Pintasan cepat untuk area yang paling sering dibuka.</p>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                @foreach ([
                    ['route' => 'user.member.area', 'label' => 'Area Member', 'copy' => 'Buka kelas dan akses konten'],
                    ['route' => 'user.downloads', 'label' => 'File Downloads', 'copy' => 'Ambil file produk digital'],
                    ['route' => 'user.certificates', 'label' => 'Sertifikat', 'copy' => 'Lihat sertifikat yang tersedia'],
                    ['route' => 'user.profile', 'label' => 'Profil', 'copy' => 'Kelola akun dan keamanan'],
                ] as $item)
                    <a href="{{ route($item['route']) }}" class="jm-member-card hover:no-underline">
                        <div class="font-bold text-[#072347]">{{ $item['label'] }}</div>
                        <div class="mt-2 text-sm text-muted leading-relaxed">{{ $item['copy'] }}</div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="jm-member-grid jm-member-grid--2">
    <div class="jm-member-panel">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="jm-member-panel-title">Produk dan kelas saya</h2>
                <p class="jm-member-panel-copy">Tampilan konten aktif dibuat lebih mirip app member: ringkas, fokus, dan gampang dipindai.</p>
            </div>
            <a href="{{ route('user.member.area') }}" class="jm-member-btn jm-member-btn--ghost">Buka area member</a>
        </div>

        @if ($classes->isEmpty())
            <div class="jm-member-empty">
                <div>
                    <div class="jm-member-empty-title">Belum ada kelas aktif</div>
                    <div class="jm-member-empty-copy">Saat Anda membeli kelas, semua akses akan otomatis muncul di dashboard ini.</div>
                    <a href="{{ route('home') }}" class="mt-6 inline-flex jm-member-btn jm-member-btn--primary">Jelajahi katalog</a>
                </div>
            </div>
        @else
            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                @foreach ($classes->take(4) as $item)
                    <a href="{{ route('user.class.show', $item['class']->slug) }}" class="jm-member-card hover:no-underline">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-[rgba(7,35,71,0.45)]">Class access</div>
                                <div class="mt-2 font-bold text-[#072347] leading-snug">{{ $item['class']->title }}</div>
                            </div>
                            <span class="jm-member-badge {{ $item['progress'] >= 100 ? 'jm-member-badge--ok' : 'jm-member-badge--warn' }}">
                                {{ $item['progress'] }}%
                            </span>
                        </div>
                        <div class="mt-5 jm-member-progress"><span style="width: {{ max(4, $item['progress']) }}%"></span></div>
                        <div class="mt-3 text-sm text-muted">{{ $item['progress'] >= 100 ? 'Materi selesai dan sertifikat siap diakses.' : 'Lanjutkan modul terakhir Anda.' }}</div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <div class="jm-member-panel">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="jm-member-panel-title">Akses saya</h2>
                <p class="jm-member-panel-copy">Semua produk aktif, lisensi, dan akses konten premium dalam satu tempat.</p>
            </div>
            <span class="jm-member-badge jm-member-badge--ok">{{ $activeAccess->count() }} aktif</span>
        </div>

        @if ($access->isEmpty())
            <div class="jm-member-empty">
                <div>
                    <div class="jm-member-empty-title">Belum ada akses aktif</div>
                    <div class="jm-member-empty-copy">Akses produk digital akan otomatis masuk ke akun setelah pembelian berhasil.</div>
                </div>
            </div>
        @else
            <div class="mt-6 jm-member-list">
                @foreach ($access->take(6) as $item)
                    <div class="jm-member-card">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="font-bold text-[#072347] truncate">{{ $item->product?->name }}</div>
                                <div class="mt-1 text-xs uppercase tracking-[0.18em] text-[rgba(7,35,71,0.45)]">{{ $item->product?->type ?? 'digital' }}</div>
                            </div>
                            @if (! $item->isActive())
                                <span class="jm-member-badge jm-member-badge--danger">Perlu perpanjang</span>
                            @elseif ($item->expires_at)
                                <span class="jm-member-badge jm-member-badge--warn">s/d {{ $item->expires_at->translatedFormat('d M Y') }}</span>
                            @else
                                <span class="jm-member-badge jm-member-badge--ok">Selamanya</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
