@extends('layouts.public')
@section('title', 'Transaksi Saya')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    <a href="{{ route('user.dashboard') }}" class="text-sm font-semibold text-muted hover:text-ink transition">← Dashboard</a>
    <h1 class="mt-2 text-3xl font-extrabold">Transaksi Saya</h1>
    <div class="mt-6 space-y-4">
        @forelse ($orders as $order)
            <div class="jm-card p-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <div class="font-extrabold">{{ $order->order_ref }}</div>
                        <div class="text-xs text-stone-400 mt-0.5">{{ $order->created_at->translatedFormat('d M Y, H:i') }} • {{ $order->gateway_code }}</div>
                    </div>
                    @php $badge = ['pending' => 'bg-amber-50 text-amber-700 border-amber-200', 'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                   'expired' => 'bg-stone-100 text-stone-500 border-stone-200', 'refunded' => 'bg-rose-50 text-rose-700 border-rose-200'][$order->status]; @endphp
                    <span class="text-xs font-extrabold uppercase tracking-wide rounded-full border px-3 py-1.5 {{ $badge }}">{{ $order->status }}</span>
                </div>
                <ul class="mt-4 text-sm space-y-1.5">
                    @foreach ($order->items as $item)
                        <li class="flex justify-between gap-3">
                            <span class="text-muted">{{ $item->product_name }}@if($item->is_bump) <span class="text-xs">(bump)</span>@endif</span>
                            <span class="money font-semibold">{{ jm_rupiah($item->price) }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-3 pt-3 border-t border-dashed border-line flex justify-between font-extrabold">
                    <span>Total</span><span class="money">{{ jm_rupiah($order->total_payable) }}</span>
                </div>
                @if ($order->isPending() || ($order->isPaid() && $order->invoice_token))
                    <div class="mt-4 flex gap-2.5">
                        @if ($order->isPending())
                            <a href="{{ route('checkout.pay', $order->order_ref) }}" class="flex-1 btn-accent text-white text-center font-bold rounded-xl py-2.5 shadow-cta hover:opacity-90 transition">Lanjutkan pembayaran</a>
                        @elseif ($order->isPaid() && $order->invoice_token)
                            <a href="{{ route('invoice.show', [$order->order_ref, $order->invoice_token]) }}" class="flex-1 text-center font-bold rounded-xl border border-ink/15 py-2.5 hover:bg-stone-50 transition">Invoice PDF ⭳</a>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="jm-card p-10 text-center">
                <span class="mx-auto h-14 w-14 rounded-2xl bg-accent-soft grid place-items-center text-2xl">🧾</span>
                <p class="mt-4 font-bold">Belum ada transaksi</p>
                <p class="mt-1 text-sm text-muted">Riwayat pembelian Anda akan tampil di sini.</p>
                <a href="{{ route('home') }}" class="mt-5 inline-flex btn-accent text-white font-bold rounded-xl px-6 py-3 shadow-cta hover:opacity-90 transition">Mulai belanja</a>
            </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
</div>
@endsection
