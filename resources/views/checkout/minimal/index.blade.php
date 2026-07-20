@extends('layouts.public')
@section('title', 'Checkout — '.$product->name)
@section('content')
{{-- MINIMAL: tanpa header produk besar, langsung form. Untuk produk impulse/murah. --}}
<div class="max-w-md mx-auto px-4 py-8">
    <div class="flex items-center gap-3 mb-5">
        @if ($product->cover_path)<img src="{{ asset('storage/'.$product->cover_path) }}" class="w-14 h-14 rounded-xl object-cover">@endif
        <div>
            <div class="font-bold leading-snug">{{ $product->name }}</div>
            <div class="text-accent font-extrabold">{{ jm_rupiah($product->price) }}</div>
        </div>
    </div>
    <div class="jm-card p-5">
        @include('partials.checkout-form')
    </div>
</div>
@endsection
