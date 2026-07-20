@extends('layouts.public')
@section('title', 'Masuk dengan WhatsApp')
@section('content')
<div class="max-w-sm mx-auto px-4 py-10" x-data="otpLogin()">
    <div class="jm-card p-6">
        <h1 class="text-xl font-extrabold text-center">Masuk via WhatsApp OTP</h1>
        @if ($errors->any())<div class="mt-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm">{{ $errors->first() }}</div>@endif

        <div x-show="!sent" class="mt-5 space-y-4">
            <input x-model="phone" inputmode="numeric" placeholder="Nomor WhatsApp (08xx)" class="jm-input">
            <button @click="requestOtp()" :disabled="loading" class="w-full btn-accent text-white font-bold rounded-xl py-3.5 min-h-[48px] disabled:opacity-60">
                <span x-show="!loading">Kirim kode OTP</span><span x-show="loading">Mengirim…</span></button>
            <p class="text-sm text-center" x-show="msg" :class="ok ? 'text-emerald-600' : 'text-rose-600'" x-text="msg"></p>
        </div>

        <form x-show="sent" x-cloak method="POST" action="{{ route('otp.verify') }}" class="mt-5 space-y-4">@csrf
            <input type="hidden" name="phone" :value="phone">
            <p class="text-sm text-slate-500 text-center">Kode dikirim ke WhatsApp <b x-text="phone"></b> (berlaku 5 menit).</p>
            <input name="code" required inputmode="numeric" maxlength="6" placeholder="6 digit kode"
                   class="jm-input">
            <input name="name" placeholder="Nama (untuk akun baru, opsional)" class="jm-input">
            <button class="w-full btn-accent text-white font-bold rounded-xl py-3.5 min-h-[48px] shadow-cta hover:opacity-90 transition">Verifikasi & Masuk</button>
        </form>
    </div>
</div>
<script>
function otpLogin() {
    return { phone: '', sent: false, loading: false, msg: '', ok: false,
        async requestOtp() {
            if (!this.phone) return;
            this.loading = true; this.msg = '';
            const r = await fetch('{{ route('otp.request') }}', { method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ phone: this.phone }) });
            const d = await r.json();
            this.loading = false; this.ok = d.ok; this.msg = d.message;
            if (d.ok) this.sent = true;
        } };
}
</script>
@endsection
