@extends('layouts.public')
@section('title', $magnet->title)
@section('content')
<div class="max-w-md mx-auto px-4 py-10" x-data="leadCapture()">
    <div class="jm-card p-6">
        <div class="text-center">
            <div class="text-4xl">🎁</div>
            <h1 class="mt-3 text-2xl font-extrabold leading-tight">{{ $magnet->title }}</h1>
            <p class="mt-2 text-sm text-slate-500">Gratis — isi data di bawah, verifikasi WhatsApp, dan langsung terima aksesnya.</p>
        </div>

        <div x-show="!sent" class="mt-6 space-y-3">
            <input x-model="name" required placeholder="Nama Anda" class="jm-input">
            <input x-model="whatsapp" required inputmode="numeric" placeholder="Nomor WhatsApp aktif" class="jm-input">
            <input x-model="email" type="email" placeholder="Email (opsional, untuk bonus)" class="jm-input">
            <button @click="requestOtp('{{ route('lead.otp', $magnet->slug) }}')" :disabled="loading"
                    class="w-full btn-accent text-white font-bold rounded-xl py-3.5 min-h-[52px] disabled:opacity-60">
                <span x-show="!loading">Kirim kode verifikasi WA ➜</span><span x-show="loading">Mengirim…</span>
            </button>
            <p class="text-sm text-center" x-show="msg" :class="ok ? 'text-emerald-600' : 'text-rose-600'" x-text="msg"></p>
        </div>

        <form x-show="sent" x-cloak method="POST" action="{{ route('lead.verify', $magnet->slug) }}" class="mt-6 space-y-3">
            @csrf
            @if ($errors->any())<div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm">{{ $errors->first() }}</div>@endif
            <p class="text-sm text-slate-500 text-center">Kode 6 digit dikirim ke WhatsApp <b x-text="whatsapp"></b></p>
            <input name="code" required inputmode="numeric" maxlength="6" placeholder="______"
                   class="jm-input">
            <button class="w-full btn-accent text-white font-bold rounded-xl py-3.5 min-h-[52px]">Verifikasi & ambil akses 🎉</button>
        </form>
    </div>
</div>
<script>
function leadCapture() {
    return { name: '', whatsapp: '', email: '', sent: false, loading: false, msg: '', ok: false,
        async requestOtp(url) {
            if (!this.name || !this.whatsapp) { this.ok = false; this.msg = 'Nama & nomor WhatsApp wajib diisi.'; return; }
            this.loading = true; this.msg = '';
            const r = await fetch(url, { method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ name: this.name, whatsapp: this.whatsapp, email: this.email || null }) });
            const d = await r.json();
            this.loading = false; this.ok = d.ok; this.msg = d.message;
            if (d.ok) this.sent = true;
        } };
}
</script>
@endsection
