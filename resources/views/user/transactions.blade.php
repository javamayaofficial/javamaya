@extends('layouts.member')
@section('title', 'Transaksi Saya')
@section('member_eyebrow', 'Transaksi')
@section('member_title', 'Transaksi Saya')
@section('member_subtitle', 'Riwayat order Anda ditampilkan lebih rapi seperti dashboard member modern: mudah discan, mudah lanjut bayar, dan mudah unduh invoice.')
@section('member_content')
<section class="jm-member-panel">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="jm-member-panel-title">Riwayat transaksi</h2>
            <p class="jm-member-panel-copy">Semua pembelian, status pembayaran, dan akses invoice tersusun rapi dalam satu tabel.</p>
        </div>
        <a href="{{ route('home') }}" class="jm-member-btn jm-member-btn--ghost">Lihat produk</a>
    </div>

    @if ($orders->isEmpty())
        <div class="jm-member-empty">
            <div>
                <div class="jm-member-empty-title">Belum ada transaksi</div>
                <div class="jm-member-empty-copy">Riwayat pembelian Anda akan otomatis muncul di halaman ini setelah order pertama berhasil.</div>
                <a href="{{ route('home') }}" class="mt-6 inline-flex jm-member-btn jm-member-btn--primary">Mulai belanja</a>
            </div>
        </div>
    @else
        <div class="mt-6 overflow-x-auto">
            <table class="jm-member-table min-w-[760px]">
                <thead>
                    <tr>
                        <th>No. order</th>
                        <th>Produk</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td>
                                <div class="font-bold text-[#072347]">{{ $order->order_ref }}</div>
                                <div class="mt-1 text-xs text-muted">{{ $order->gateway_code }}</div>
                            </td>
                            <td>
                                <div class="space-y-1.5">
                                    @foreach ($order->items as $item)
                                        <div class="text-sm text-[#072347]">
                                            {{ $item->product_name }}
                                            @if ($item->is_bump)
                                                <span class="text-xs text-muted">(bump)</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="font-bold text-[#072347]">{{ jm_rupiah($order->total_payable) }}</td>
                            <td>
                                <span class="jm-member-badge {{ $order->status === 'paid' ? 'jm-member-badge--ok' : ($order->status === 'pending' ? 'jm-member-badge--warn' : 'jm-member-badge--danger') }}">
                                    {{ $order->status === 'paid' ? 'Lunas' : $order->status }}
                                </span>
                            </td>
                            <td class="text-sm text-muted">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    @if ($order->isPending())
                                        <a href="{{ route('checkout.pay', $order->order_ref) }}" class="jm-member-btn jm-member-btn--primary">Bayar</a>
                                    @endif
                                    @if ($order->isPaid() && $order->invoice_token)
                                        <a href="{{ route('invoice.show', [$order->order_ref, $order->invoice_token]) }}" class="jm-member-btn jm-member-btn--ghost">Invoice</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $orders->links() }}</div>
    @endif
</section>
@endsection
