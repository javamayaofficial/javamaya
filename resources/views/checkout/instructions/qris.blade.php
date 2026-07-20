@extends('layouts.public')
@section('title', 'Bayar via QRIS')
@section('content')
<div class="max-w-md mx-auto px-4 py-8">
    <div class="jm-card p-6 text-center">
        <h1 class="text-xl font-extrabold">Scan QRIS</h1>
        <p class="text-sm text-slate-500 mt-1">Order {{ $order->order_ref }}</p>
        <img src="{{ $qris_image_url }}" alt="QRIS" class="mx-auto mt-4 w-64 h-64 object-contain rounded-xl border border-slate-200">
        <div class="mt-4 rounded-xl bg-amber-50 border border-amber-200 p-4">
            <div class="text-xs uppercase tracking-wide text-amber-700 font-semibold">Bayar TEPAT sejumlah</div>
            <div class="mt-1 text-3xl font-extrabold">{{ jm_rupiah($order->total_payable) }}</div>
            <p class="mt-2 text-xs text-amber-700">{{ $note }}</p>
        </div>
        <a href="{{ route('checkout.thankyou', $order->order_ref) }}" class="mt-5 block rounded-xl border border-slate-300 py-3 font-semibold hover:bg-slate-50">Saya sudah bayar</a>
    </div>
</div>
@endsection
