@extends('layouts.public')
@section('title', 'Terima Kasih')
@section('content')
@php $upsellOffer = app(\App\Services\Checkout\UpsellService::class)->offerFor($order); @endphp
<div class="max-w-xl mx-auto px-4 py-10" x-data="statusPoll('{{ route('checkout.status', $order->order_ref) }}', '{{ $order->status }}')" x-init="start()">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center shadow-sm">
        <template x-if="status === 'paid'">
            <div>
                <div class="mx-auto w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl">✓</div>
                <h1 class="mt-4 text-2xl font-extrabold">Pembayaran diterima!</h1>
                <p class="mt-2 text-muted">Order <b>{{ $order->order_ref }}</b> lunas. Detail akses juga dikirim ke email & WhatsApp Anda.</p>
                <div class="mt-6 grid gap-3">
                    <a href="{{ route('user.dashboard') }}" class="btn-accent text-white font-bold rounded-xl py-3">Masuk Member Area</a>
                    <a x-show="invoiceUrl" :href="invoiceUrl" class="rounded-xl border border-slate-300 py-3 font-semibold hover:bg-slate-50">Download Invoice PDF</a>
                </div>
            </div>
        </template>
        <template x-if="status !== 'paid'">
            <div>
                <div class="mx-auto w-14 h-14 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-2xl animate-pulse">⏳</div>
                <h1 class="mt-4 text-2xl font-extrabold">Menunggu pembayaran</h1>
                <p class="mt-2 text-muted">Order <b>{{ $order->order_ref }}</b> — total
                    <b>{{ jm_rupiah($order->total_payable) }}</b>. Status akan berubah otomatis begitu pembayaran masuk.</p>
                <a href="{{ route('checkout.pay', $order->order_ref) }}" class="mt-6 inline-block rounded-xl border border-slate-300 px-5 py-3 font-semibold hover:bg-slate-50">Lihat instruksi bayar</a>
            </div>
        </template>
    </div>

    @if ($upsellOffer)
        <div class="mt-5 rounded-2xl border-2 border-dashed border-amber-300 bg-amber-50 p-5 text-center">
            <div class="text-xs uppercase font-bold tracking-wide text-amber-700">🎁 Penawaran sekali lewat — khusus Anda</div>
            <div class="mt-2 font-extrabold text-lg text-amber-900">{{ $upsellOffer->headline }}</div>
            @if ($upsellOffer->description)<p class="mt-1 text-sm text-amber-800">{{ $upsellOffer->description }}</p>@endif
            <div class="mt-3 flex items-baseline justify-center gap-2">
                <span class="text-2xl font-extrabold text-amber-900">{{ jm_rupiah($upsellOffer->upsell_price) }}</span>
                @if ($upsellOffer->upsell_price < $upsellOffer->upsellProduct->price)
                    <span class="text-amber-600 line-through">{{ jm_rupiah($upsellOffer->upsellProduct->price) }}</span>
                @endif
            </div>
            <form method="POST" action="{{ route('upsell.accept', [$order->order_ref, $upsellOffer->id]) }}" class="mt-4">
                @csrf
                <button class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl py-3.5 min-h-[52px] shadow transition">
                    Ya, tambahkan — bayar {{ jm_rupiah($upsellOffer->upsell_price) }} ➜
                </button>
            </form>
            <p class="mt-2 text-xs text-amber-600">Data & metode pembayaran Anda dipakai ulang otomatis.</p>
        </div>
    @endif
</div>
<script>
function statusPoll(url, initial) {
    return { status: initial, invoiceUrl: null, timer: null,
        start() { if (this.status === 'paid') return this.fetchOnce(); this.timer = setInterval(() => this.fetchOnce(), 5000); },
        async fetchOnce() {
            try {
                const r = await fetch(url); const d = await r.json();
                this.status = d.status; this.invoiceUrl = d.invoice_url;
                if (d.status !== 'pending' && this.timer) clearInterval(this.timer);
            } catch (e) {}
        } };
}
</script>
@endsection
