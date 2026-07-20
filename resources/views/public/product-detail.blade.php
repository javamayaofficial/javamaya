@extends('layouts.public')
@section('title', $product->name)
@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 pb-28">
    {{-- Hero fold: cover + judul + harga + CTA dalam satu layar HP --}}
    @if ($product->cover_path)
        <img src="{{ asset('storage/'.$product->cover_path) }}" alt="{{ $product->name }}"
             class="w-full rounded-2xl aspect-video object-cover shadow-sm" fetchpriority="high">
    @endif
    <h1 class="mt-4 text-2xl sm:text-3xl font-extrabold leading-tight">{{ $product->name }}</h1>
    <div class="mt-2 flex items-baseline gap-3">
        <span class="text-2xl font-extrabold money text-accent-c">{{ jm_rupiah($product->price) }}</span>
        @if ($product->compare_price)
            <span class="text-slate-400 line-through">{{ jm_rupiah($product->compare_price) }}</span>
        @endif
        @if ($product->access_expiry_type === 'n_days')
            <span class="text-xs bg-amber-100 text-amber-800 rounded-full px-2 py-1">Akses {{ $product->access_expiry_days }} hari</span>
        @elseif ($product->access_expiry_type === 'lifetime')
            <span class="text-xs bg-emerald-100 text-emerald-800 rounded-full px-2 py-1">Akses selamanya</span>
        @endif
    </div>

    @if ($product->type === 'bundle' && $product->bundle)
        <div class="mt-5 jm-card p-5">
            <div class="font-semibold mb-2">Isi bundle:</div>
            <ul class="space-y-1 text-sm">
                @foreach ($product->bundle->items as $item)
                    <li class="flex justify-between"><span>✔ {{ $item->itemProduct->name }}</span>
                        <span class="text-slate-400 line-through">{{ jm_rupiah($item->itemProduct->price) }}</span></li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="prose prose-slate mt-6 max-w-none">{!! nl2br(e($product->description)) !!}</div>

    @auth
        @if (\App\Services\LMS\MemberAccessGrantor::hasActiveAccess(auth()->user(), $product->id))
            <div class="mt-8 jm-card p-5" x-data="{ rating: 5 }">
                <h2 class="font-bold">Beri ulasan Anda</h2>
                <form method="POST" action="{{ route('product.review', $product->slug) }}" class="mt-3 space-y-3">
                    @csrf
                    <input type="hidden" name="rating" :value="rating">
                    <div class="flex gap-1 text-2xl">
                        <template x-for="i in 5">
                            <button type="button" @click="rating = i" x-text="i <= rating ? '★' : '☆'"
                                    class="text-amber-500 min-w-[36px] min-h-[36px]"></button>
                        </template>
                    </div>
                    <textarea name="body" rows="3" maxlength="1000" placeholder="Ceritakan pengalaman Anda (opsional)"
                              class="jm-input"></textarea>
                    <button class="btn-accent text-white font-bold rounded-xl px-5 py-2.5">Kirim ulasan</button>
                </form>
            </div>
        @endif
    @endauth

    @if ($product->reviews->isNotEmpty())
        <div class="mt-8">
            <h2 class="font-bold text-lg mb-3">Ulasan pembeli</h2>
            <div class="space-y-3">
                @foreach ($product->reviews as $review)
                    <div class="jm-card p-4">
                        <div class="text-amber-500 text-sm">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
                        <p class="mt-1 text-sm">{{ $review->body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

{{-- CTA lengket bawah viewport (tap target >=44px) --}}
<div class="fixed bottom-0 inset-x-0 bg-white/85 backdrop-blur-xl border-t border-ink/10 p-3">
    <div class="max-w-3xl mx-auto flex items-center gap-3">
        <div class="hidden sm:block font-extrabold money">{{ jm_rupiah($product->price) }}</div>
        @if ($product->isSoldOut())
            <button onclick="document.getElementById('waitlist-modal').showModal()"
                class="flex-1 bg-slate-800 text-white text-center font-bold rounded-xl py-3.5 min-h-[48px] shadow">
                Stok habis — Masuk daftar tunggu 🔔
            </button>
        @else
            <a href="{{ route('checkout.show', $product->slug) }}"
               class="flex-1 btn-accent text-white text-center font-bold rounded-xl py-3.5 min-h-[48px] shadow-cta hover:opacity-90 transition">
                Beli Sekarang
            </a>
        @endif
    </div>
</div>

@if ($product->isSoldOut())
<dialog id="waitlist-modal" class="rounded-2xl p-0 w-full max-w-sm backdrop:bg-slate-900/50">
    <form method="POST" action="{{ route('product.waitlist', $product->slug) }}" class="p-6 space-y-3">
        @csrf
        <h2 class="font-extrabold text-lg">Daftar tunggu — {{ $product->name }}</h2>
        <p class="text-sm text-slate-500">Kami kabari via WhatsApp begitu stok tersedia lagi.</p>
        <input name="name" required placeholder="Nama" class="jm-input">
        <input name="whatsapp" required inputmode="numeric" placeholder="Nomor WhatsApp" class="jm-input">
        <input name="email" type="email" placeholder="Email (opsional)" class="jm-input">
        <div class="flex gap-2">
            <button type="button" onclick="document.getElementById('waitlist-modal').close()"
                    class="flex-1 rounded-xl border border-slate-300 py-3 font-semibold">Batal</button>
            <button class="flex-1 btn-accent text-white font-bold rounded-xl py-3">Daftarkan saya</button>
        </div>
    </form>
</dialog>
@endif
@endsection
