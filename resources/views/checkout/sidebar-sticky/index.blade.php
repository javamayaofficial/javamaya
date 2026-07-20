@extends('layouts.public')
@section('title', 'Checkout — '.$product->name)
@section('content')
{{-- SIDEBAR STICKY: dua kolom desktop, ringkasan produk sticky; di HP jadi collapsible di atas. --}}
<div class="max-w-5xl mx-auto px-4 py-8 grid lg:grid-cols-5 gap-8">
    <aside class="lg:col-span-2 lg:order-2">
        <div class="lg:sticky lg:top-6" x-data="{ open: window.innerWidth >= 1024 }">
            <button type="button" @click="open = !open" class="lg:hidden w-full flex justify-between items-center bg-white rounded-xl border border-slate-200 px-4 py-3 font-semibold">
                Ringkasan pesanan <span x-text="open ? '▲' : '▼'"></span>
            </button>
            <div x-show="open" class="bg-white rounded-2xl border border-slate-200 p-5 mt-2 lg:mt-0 shadow-sm">
                @if ($product->cover_path)<img src="{{ asset('storage/'.$product->cover_path) }}" class="w-full aspect-video object-cover rounded-xl">@endif
                <div class="mt-3 font-bold">{{ $product->name }}</div>
                <p class="mt-2 text-sm text-slate-500 line-clamp-4">{{ strip_tags((string) $product->description) }}</p>
                <div class="mt-4 border-t border-dashed pt-3 flex justify-between font-extrabold">
                    <span>Total</span><span>{{ jm_rupiah($product->price) }}</span>
                </div>
                @if ($product->access_expiry_type === 'lifetime')
                    <div class="mt-2 text-xs text-emerald-600 font-semibold">✔ Akses selamanya</div>
                @elseif ($product->access_expiry_type === 'n_days')
                    <div class="mt-2 text-xs text-amber-600 font-semibold">⏱ Akses {{ $product->access_expiry_days }} hari</div>
                @endif
            </div>
        </div>
    </aside>
    <div class="lg:col-span-3 lg:order-1">
        <div class="jm-card p-5">
            <h1 class="text-lg font-extrabold mb-4">Lengkapi data Anda</h1>
            @include('partials.checkout-form')
        </div>
    </div>
</div>
@endsection
