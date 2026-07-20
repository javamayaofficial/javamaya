@extends('layouts.public')
@section('title', 'Checkout — '.$product->name)
@section('content')
{{-- STANDARD: header produk penuh + deskripsi ringkas + testimoni; untuk cold traffic. --}}
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="jm-card overflow-hidden">
        @if ($product->cover_path)<img src="{{ asset('storage/'.$product->cover_path) }}" class="w-full aspect-[3/1] object-cover">@endif
        <div class="p-6">
            <h1 class="text-2xl font-extrabold">{{ $product->name }}</h1>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-extrabold text-accent">{{ jm_rupiah($product->price) }}</span>
                @if ($product->compare_price)<span class="text-slate-400 line-through text-sm">{{ jm_rupiah($product->compare_price) }}</span>@endif
            </div>
            <p class="mt-3 text-sm text-slate-500 line-clamp-3">{{ strip_tags((string) $product->description) }}</p>
        </div>
    </div>

    @php $reviews = $product->reviews()->where('status', 'approved')->latest()->limit(2)->get(); @endphp
    @if ($reviews->isNotEmpty())
        <div class="mt-4 grid sm:grid-cols-2 gap-3">
            @foreach ($reviews as $review)
                <div class="bg-white rounded-xl border border-slate-200 p-4 text-sm">
                    <div class="text-amber-500">{{ str_repeat('★', $review->rating) }}</div>
                    <p class="mt-1 text-slate-600 line-clamp-3">{{ $review->body }}</p>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-6 jm-card p-6">
        <h2 class="font-extrabold mb-4">Checkout</h2>
        @include('partials.checkout-form')
    </div>
</div>
@endsection
