{{-- Form checkout dipakai keempat template. $product, $gateways tersedia. --}}
<form method="POST" action="{{ route('checkout.store', $product->slug) }}" x-data="checkoutForm()" @submit="submitting = true" class="space-y-4">
    @csrf
    <input type="hidden" name="idem" value="{{ \Illuminate\Support\Str::random(40) }}">

    @if ($errors->any())
        <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div>
        <label class="block text-sm font-semibold mb-1">Nama lengkap</label>
        <input name="buyer_name" value="{{ old('buyer_name') }}" required
               class="jm-input" placeholder="Nama Anda">
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Email</label>
        <input type="email" name="buyer_email" x-model="email" value="{{ old('buyer_email') }}" required
               class="jm-input" placeholder="email@contoh.com">
        <p class="text-xs text-slate-400 mt-1">Akses produk dikirim ke email ini.</p>
    </div>
    <div>
        <label class="block text-sm font-semibold mb-1">Nomor WhatsApp</label>
        <input name="buyer_phone" value="{{ old('buyer_phone') }}" required inputmode="numeric"
               class="jm-input" placeholder="08xxxxxxxxxx">
    </div>

    @php $bump = $product->bumps->firstWhere('active', true); @endphp
    @if ($bump && $bump->bumpProduct)
        <label class="flex gap-3 items-start rounded-xl border-2 border-dashed border-amber-300 bg-amber-50 p-4 cursor-pointer">
            <input type="checkbox" name="bump_id" value="{{ $bump->id }}" class="mt-1 w-5 h-5 rounded text-accent">
            <span>
                <span class="font-bold text-amber-900">{{ $bump->headline }}</span><br>
                <span class="text-sm text-amber-800">{{ $bump->bumpProduct->name }} — hanya <b>{{ jm_rupiah($bump->bump_price) }}</b></span>
            </span>
        </label>
    @endif

    <div x-data="{ open: false }">
        <button type="button" @click="open = !open" class="text-sm font-semibold text-accent underline decoration-dotted">Punya kupon?</button>
        <div x-show="open" x-cloak class="mt-2 flex gap-2">
            <input x-model="coupon" name="coupon_code" class="flex-1 rounded-xl border-slate-300 px-4 py-3 min-h-[48px]" placeholder="KODEKUPON">
            <button type="button" @click="checkCoupon('{{ route('checkout.coupon') }}', '{{ $product->slug }}')"
                    class="rounded-xl border border-slate-300 px-4 font-semibold min-h-[48px]">Cek</button>
        </div>
        <p class="text-sm mt-1" x-show="couponMsg" :class="couponOk ? 'text-emerald-600' : 'text-rose-600'" x-text="couponMsg"></p>
    </div>

    <div>
        <label class="block text-sm font-semibold mb-2">Metode pembayaran</label>
        <div class="grid gap-2">
            @foreach ($gateways as $gw)
                @php $cfg = \App\Models\PaymentGateway::where('code', $gw->code())->first(); @endphp
                <label class="flex items-center gap-3 rounded-xl border border-slate-300 p-3.5 min-h-[52px] cursor-pointer has-[:checked]:border-accent has-[:checked]:ring-2 has-[:checked]:ring-accent/30">
                    <input type="radio" name="gateway" value="{{ $gw->code() }}" required class="w-5 h-5 text-accent">
                    <span class="flex-1">
                        <span class="font-semibold">{{ $cfg?->name ?? $gw->code() }}</span>
                        @if ($cfg?->fee_display)<span class="block text-xs text-slate-400">{{ $cfg->fee_display }}</span>@endif
                    </span>
                    @if ($cfg?->sandbox_mode)<span class="text-[10px] uppercase bg-slate-100 text-slate-500 rounded px-1.5 py-0.5">sandbox</span>@endif
                </label>
            @endforeach
        </div>
    </div>

    <button :disabled="submitting" class="w-full btn-accent text-white font-bold rounded-xl py-4 min-h-[52px] shadow hover:opacity-90 transition disabled:opacity-60">
        <span x-show="!submitting">Bayar {{ jm_rupiah($product->price) }}</span>
        <span x-show="submitting">Memproses…</span>
    </button>
    <p class="text-center text-xs text-slate-400">🔒 Transaksi aman • Akses instan setelah pembayaran</p>
</form>

<script>
function checkoutForm() {
    return { submitting: false, email: '', coupon: '', couponMsg: '', couponOk: false,
        async checkCoupon(url, slug) {
            if (!this.coupon) return;
            const r = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ slug, code: this.coupon, email: this.email || null }) });
            const d = await r.json();
            this.couponOk = !!d.ok;
            this.couponMsg = d.ok ? ('Kupon valid! Hemat ' + d.discount_label + ' (dihitung final saat submit).') : d.message;
        } };
}
</script>
