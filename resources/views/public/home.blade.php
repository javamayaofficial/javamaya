@extends('layouts.public')
@section('title', jm_setting('store_name', config('app.name')))
@section('content')
<div class="max-w-5xl mx-auto px-4">

    {{-- Hero --}}
    <section class="pt-14 pb-10 text-center max-w-2xl mx-auto">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-accent-soft text-accent-c text-xs font-bold px-3.5 py-1.5">
            ✦ {{ jm_setting('store_badge', 'Produk digital & kelas online') }}
        </span>
        <h1 class="mt-5 text-4xl sm:text-5xl font-extrabold leading-[1.08]">
            {{ jm_setting('store_headline', 'Belajar & berkembang, mulai hari ini') }}
        </h1>
        <p class="mt-4 text-muted text-lg leading-relaxed">
            {{ jm_setting('store_tagline', 'Akses instan setelah pembayaran — langsung ke email & WhatsApp Anda.') }}
        </p>
    </section>

    {{-- Katalog --}}
    @if ($products->isEmpty())
        <div class="jm-card p-12 text-center max-w-lg mx-auto">
            <span class="mx-auto h-14 w-14 rounded-2xl bg-accent-soft grid place-items-center text-2xl">🛍️</span>
            <p class="mt-4 font-bold">Katalog sedang disiapkan</p>
            <p class="mt-1 text-sm text-muted">Produk pertama akan segera hadir. Silakan kembali lagi nanti.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 pb-4">
            @foreach ($products as $product)
                <a href="{{ route('product.show', $product->slug) }}" class="jm-card overflow-hidden group flex flex-col">
                    <div class="relative">
                        @if ($product->cover_path)
                            <img src="{{ asset('storage/'.$product->cover_path) }}" alt="{{ $product->name }}"
                                 class="w-full aspect-video object-cover group-hover:scale-[1.03] transition duration-300" loading="lazy">
                        @else
                            <div class="w-full aspect-video bg-accent-soft grid place-items-center text-4xl">
                                {{ ['digital' => '💾', 'downloadable' => '📁', 'class' => '🎓', 'bundle' => '🎁'][$product->type] ?? '💾' }}
                            </div>
                        @endif
                        <span class="absolute top-3 left-3 rounded-full bg-white/90 backdrop-blur text-[11px] font-extrabold uppercase tracking-wide px-2.5 py-1">
                            {{ ['digital' => 'Digital', 'downloadable' => 'File', 'class' => 'Kelas', 'bundle' => 'Bundle'][$product->type] ?? 'Produk' }}
                        </span>
                        @if ($product->compare_price && $product->compare_price > $product->price)
                            <span class="absolute top-3 right-3 rounded-full bg-rose-500 text-white text-[11px] font-extrabold px-2.5 py-1">
                                -{{ (int) round((1 - $product->price / $product->compare_price) * 100) }}%
                            </span>
                        @endif
                    </div>
                    <div class="p-5 flex flex-col flex-1">
                        <div class="font-bold leading-snug group-hover:text-accent-c transition">{{ $product->name }}</div>
                        <div class="mt-auto pt-3 flex items-baseline gap-2">
                            <span class="text-lg font-extrabold money text-accent-c">{{ jm_rupiah($product->price) }}</span>
                            @if ($product->compare_price)
                                <span class="text-sm text-stone-400 line-through money">{{ jm_rupiah($product->compare_price) }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
