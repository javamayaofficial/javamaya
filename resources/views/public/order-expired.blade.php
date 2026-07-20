@extends('layouts.public')
@section('title', 'Order Kedaluwarsa')
@section('content')
<div class="max-w-md mx-auto px-4 py-16 text-center">
    <div class="text-5xl">⌛</div>
    <h1 class="mt-4 text-2xl font-extrabold">Order kedaluwarsa</h1>
    <p class="mt-2 text-slate-500">Order {{ $order->order_ref }} sudah melewati batas waktu pembayaran. Silakan buat order baru.</p>
    @if ($order->items->first())
        <a href="{{ route('checkout.show', $order->items->first()->product?->slug ?? '') }}"
           class="mt-6 inline-block btn-accent text-white font-bold rounded-xl px-6 py-3">Buat order baru</a>
    @endif
</div>
@endsection
