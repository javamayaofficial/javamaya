@extends('layouts.member')
@section('title', 'Affiliate')
@section('member_eyebrow', 'Affiliate')
@section('member_title', 'Affiliate')
@section('member_subtitle', 'Ringkasan status affiliate dan komisi referral Anda ditampilkan langsung dari member area.')
@section('member_content')
<section class="jm-member-split jm-member-split--2">
    <div class="jm-member-panel">
        <h2 class="jm-member-panel-title">Status affiliate</h2>
        <p class="jm-member-panel-copy">Jika akun Anda sudah menjadi affiliate, ringkasan slug dan performanya akan muncul di sini.</p>
        <div class="mt-6 jm-member-stack">
            <div class="jm-member-card">
                <div class="jm-member-kv-label">Status</div>
                <div class="jm-member-kv-value">{{ $affiliate ? strtoupper($affiliate->status) : 'BELUM AKTIF' }}</div>
            </div>
            <div class="jm-member-card">
                <div class="jm-member-kv-label">Slug referral</div>
                <div class="jm-member-kv-value">{{ $affiliate?->slug ?: 'Belum tersedia' }}</div>
            </div>
            <div class="jm-member-card">
                <div class="jm-member-kv-label">Saldo approved</div>
                <div class="jm-member-kv-value">{{ $affiliate ? jm_rupiah($affiliate->approvedBalance()) : jm_rupiah(0) }}</div>
            </div>
        </div>
    </div>

    <div class="jm-member-panel">
        <h2 class="jm-member-panel-title">Catatan</h2>
        <p class="jm-member-panel-copy">Halaman ini saya siapkan agar member area terasa lengkap seperti referensi Averion, sekaligus tetap nyambung dengan data affiliate Javamaya.</p>
        <div class="mt-6 jm-member-card">
            @if ($affiliate)
                <div class="font-bold text-[#072347]">Akun affiliate aktif</div>
                <div class="mt-2 text-sm text-muted">Gunakan slug Anda untuk tracking referral. Riwayat komisi terakhir tampil di bawah.</div>
            @else
                <div class="font-bold text-[#072347]">Program affiliate belum aktif di akun ini</div>
                <div class="mt-2 text-sm text-muted">Saat akun Anda diaktifkan sebagai affiliate, halaman ini akan otomatis menampilkan slug dan komisi.</div>
            @endif
        </div>
    </div>
</section>

<section class="jm-member-panel">
    <h2 class="jm-member-panel-title">Komisi terbaru</h2>
    <p class="jm-member-panel-copy">Riwayat komisi referral paling baru dari akun affiliate Anda.</p>

    @if ($commissions->isEmpty())
        <div class="jm-member-empty">
            <div>
                <div class="jm-member-empty-title">Belum ada komisi</div>
                <div class="jm-member-empty-copy">Komisi referral yang masuk akan ditampilkan di halaman ini secara otomatis.</div>
            </div>
        </div>
    @else
        <div class="mt-6 overflow-x-auto">
            <table class="jm-member-table min-w-[680px]">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($commissions as $commission)
                        <tr>
                            <td class="font-bold text-[#072347]">#{{ $commission->order_id }}</td>
                            <td class="font-bold text-[#072347]">{{ jm_rupiah($commission->amount) }}</td>
                            <td>
                                <span class="jm-member-badge {{ $commission->status === 'approved' ? 'jm-member-badge--ok' : 'jm-member-badge--warn' }}">{{ $commission->status }}</span>
                            </td>
                            <td class="text-sm text-muted">{{ $commission->created_at?->translatedFormat('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
