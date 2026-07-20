@extends('layouts.public')
@section('title', 'Instruksi Transfer')
@section('content')
<div class="max-w-md mx-auto px-4 py-8" x-data="statusPoll('{{ route('checkout.status', $order->order_ref) }}', '{{ $order->status }}')" x-init="start()">
    <div class="jm-card p-6">
        <h1 class="text-xl font-extrabold">Transfer Bank</h1>
        <p class="text-sm text-muted mt-1">Order {{ $order->order_ref }}</p>

        <div class="mt-5 rounded-xl bg-slate-50 border border-slate-200 p-4 space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-muted">Bank</span><b>{{ $bank_name }}</b></div>
            <div class="flex justify-between items-center"><span class="text-muted">No. Rekening</span>
                <button type="button" class="font-bold underline decoration-dotted" x-data
                    @click="navigator.clipboard.writeText('{{ $account_number }}')">{{ $account_number }} ⧉</button></div>
            <div class="flex justify-between"><span class="text-muted">Atas Nama</span><b>{{ $account_holder }}</b></div>
        </div>

        <div class="mt-4 rounded-xl bg-amber-50 border border-amber-200 p-4 text-center">
            <div class="text-xs uppercase tracking-wide text-amber-700 font-semibold">Transfer TEPAT sejumlah</div>
            <button type="button" class="mt-1 text-3xl font-extrabold tracking-tight" x-data
                @click="navigator.clipboard.writeText('{{ $order->total_payable }}')">{{ jm_rupiah($order->total_payable) }} ⧉</button>
            <p class="mt-2 text-xs text-amber-700">{{ $note }}</p>
        </div>

        <div class="mt-5 text-center text-sm" x-show="status === 'pending'">
            <span class="inline-flex items-center gap-2 text-muted"><span class="animate-pulse">●</span>
            @if ($auto_confirm) Menunggu pembayaran — terkonfirmasi otomatis @else Menunggu konfirmasi admin @endif</span>
        </div>
        <template x-if="status === 'paid'">
            <a href="{{ route('checkout.thankyou', $order->order_ref) }}" class="mt-5 block btn-accent text-white text-center font-bold rounded-xl py-3">Pembayaran diterima — Lanjut ➜</a>
        </template>
    </div>
</div>
<script>
function statusPoll(url, initial) {
    return { status: initial, timer: null,
        start() { if (this.status !== 'pending') return; this.timer = setInterval(async () => {
            try { const d = await (await fetch(url)).json(); this.status = d.status;
                if (d.status !== 'pending') clearInterval(this.timer); } catch (e) {} }, 5000); } };
}
</script>
@endsection
