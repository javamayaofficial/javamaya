@extends('layouts.public')
@section('title', 'Checkout — '.$product->name)
@section('content')
{{-- TWO-STEP: step 1 data kontak (Alpine), step 2 metode bayar. Untuk produk harga tinggi. --}}
<div class="max-w-md mx-auto px-4 py-8" x-data="{ step: 1 }">
    <div class="flex items-center gap-2 mb-5 text-sm font-semibold">
        <span class="flex items-center gap-1.5" :class="step >= 1 ? 'text-accent' : 'text-slate-400'">
            <span class="w-6 h-6 rounded-full flex items-center justify-center text-white" :class="step >= 1 ? 'btn-accent' : 'bg-slate-300'">1</span> Data diri
        </span>
        <span class="flex-1 h-px bg-slate-300"></span>
        <span class="flex items-center gap-1.5" :class="step >= 2 ? 'text-accent' : 'text-slate-400'">
            <span class="w-6 h-6 rounded-full flex items-center justify-center text-white" :class="step >= 2 ? 'btn-accent' : 'bg-slate-300'">2</span> Pembayaran
        </span>
    </div>

    <div class="jm-card p-5">
        <div class="flex items-center gap-3 pb-4 mb-4 border-b border-dashed">
            @if ($product->cover_path)<img src="{{ asset('storage/'.$product->cover_path) }}" class="w-12 h-12 rounded-lg object-cover">@endif
            <div class="text-sm"><div class="font-bold">{{ $product->name }}</div>
                <div class="text-accent font-extrabold">{{ jm_rupiah($product->price) }}</div></div>
        </div>

        <form method="POST" action="{{ route('checkout.store', $product->slug) }}" x-data="{ submitting: false }" @submit="submitting = true" class="space-y-4">
            @csrf
            <input type="hidden" name="idem" value="{{ \Illuminate\Support\Str::random(40) }}">
            @if ($errors->any())
                <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm">{{ $errors->first() }}</div>
            @endif

            {{-- STEP 1 --}}
            <div x-show="step === 1" class="space-y-4">
                <div><label class="block text-sm font-semibold mb-1">Nama lengkap</label>
                    <input name="buyer_name" value="{{ old('buyer_name') }}" required class="jm-input"></div>
                <div><label class="block text-sm font-semibold mb-1">Email</label>
                    <input type="email" name="buyer_email" value="{{ old('buyer_email') }}" required class="jm-input"></div>
                <div><label class="block text-sm font-semibold mb-1">Nomor WhatsApp</label>
                    <input name="buyer_phone" value="{{ old('buyer_phone') }}" required inputmode="numeric" class="jm-input"></div>
                <button type="button" @click="step = 2" class="w-full btn-accent text-white font-bold rounded-xl py-4 min-h-[52px]">Lanjut ke pembayaran ➜</button>
            </div>

            {{-- STEP 2 --}}
            <div x-show="step === 2" x-cloak class="space-y-4">
                @php $bump = $product->bumps->firstWhere('active', true); @endphp
                @if ($bump && $bump->bumpProduct)
                    <label class="flex gap-3 items-start rounded-xl border-2 border-dashed border-amber-300 bg-amber-50 p-4 cursor-pointer">
                        <input type="checkbox" name="bump_id" value="{{ $bump->id }}" class="mt-1 w-5 h-5 rounded">
                        <span><span class="font-bold text-amber-900">{{ $bump->headline }}</span><br>
                        <span class="text-sm text-amber-800">{{ $bump->bumpProduct->name }} — {{ jm_rupiah($bump->bump_price) }}</span></span>
                    </label>
                @endif
                <input type="hidden" name="coupon_code" value="">
                <div>
                    <label class="block text-sm font-semibold mb-2">Metode pembayaran</label>
                    <div class="grid gap-2">
                        @foreach ($gateways as $gw)
                            @php $cfg = \App\Models\PaymentGateway::where('code', $gw->code())->first(); @endphp
                            <label class="flex items-center gap-3 rounded-xl border border-slate-300 p-3.5 min-h-[52px] cursor-pointer has-[:checked]:border-accent has-[:checked]:ring-2 has-[:checked]:ring-accent/30">
                                <input type="radio" name="gateway" value="{{ $gw->code() }}" required class="w-5 h-5">
                                <span class="font-semibold flex-1">{{ $cfg?->name ?? $gw->code() }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" @click="step = 1" class="rounded-xl border border-slate-300 py-3.5 font-semibold min-h-[52px]">⟵</button>
                    <button :disabled="submitting" class="col-span-2 btn-accent text-white font-bold rounded-xl py-3.5 min-h-[52px] disabled:opacity-60">
                        <span x-show="!submitting">Bayar sekarang</span><span x-show="submitting">Memproses…</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
