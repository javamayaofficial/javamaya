@extends('layouts.member')
@section('title', $class->title)
@section('member_eyebrow', 'Member Area')
@section('member_title', $class->title)
@section('member_subtitle', 'Lanjutkan materi, pantau progress, dan unduh sertifikat langsung dari area member yang lebih terstruktur.')
@section('member_content')
<div class="jm-member-stack"
     x-data="classProgress({{ $progress }}, @js($certificate ? route('user.certificate.download', $certificate->id) : null))">
    <section class="jm-member-panel">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="jm-member-panel-title">{{ $class->title }}</h2>
                <p class="jm-member-panel-copy">
                    @if ($expiresAt)
                        Akses aktif hingga {{ $expiresAt->translatedFormat('d F Y') }}.
                    @else
                        Akses belajar aktif dan siap dilanjutkan kapan saja.
                    @endif
                </p>
            </div>
            <div class="jm-member-chip">
                <span class="jm-member-chip-key">Progress</span>
                <span x-text="progress + '%'"></span>
            </div>
        </div>

        <div class="mt-5 jm-member-progress">
            <span :style="'width:' + Math.max(4, progress) + '%'"></span>
        </div>

        <template x-if="certUrl">
            <a :href="certUrl" class="mt-5 inline-flex jm-member-btn jm-member-btn--primary">Download Sertifikat PDF</a>
        </template>
    </section>

    <section class="jm-member-panel">
        <h2 class="jm-member-panel-title">Daftar materi</h2>
        <p class="jm-member-panel-copy">Materi disusun seperti learning path agar lebih nyaman dibuka dari member dashboard.</p>
        <div class="mt-6 space-y-3">
        @foreach ($class->materials as $material)
            <div class="jm-member-card"
                 x-data="{ done: {{ in_array($material->id, $completedIds) ? 'true' : 'false' }}, busy: false }">
                <div class="flex items-start justify-between gap-3">
                    <div class="font-bold text-[#072347]">{{ $loop->iteration }}. {{ $material->title }}</div>
                    <button @click="busy = true; toggleMaterial('{{ route('user.material.toggle', $material->id) }}')
                                .then(d => { if (d) { done = !done; } busy = false; })"
                            :disabled="busy"
                            class="shrink-0 text-sm font-semibold rounded-full px-3 py-1.5 border min-h-[36px] transition disabled:opacity-50"
                            :class="done ? 'bg-emerald-50 border-emerald-300 text-emerald-700' : 'border-slate-300 text-slate-500 bg-white'">
                        <span x-text="busy ? '…' : (done ? '✓ Selesai' : 'Tandai selesai')"></span>
                    </button>
                </div>
                <div class="mt-3">
                    @if ($material->type === 'video_url')
                        <div class="aspect-video rounded-xl overflow-hidden bg-slate-900">
                            <iframe class="w-full h-full" src="{{ $material->content }}" allowfullscreen loading="lazy"></iframe>
                        </div>
                    @elseif ($material->type === 'embed')
                        <div class="rounded-xl overflow-hidden">{!! $material->content !!}</div>
                    @elseif ($material->type === 'text')
                        <div class="prose prose-slate max-w-none text-sm">{!! nl2br(e($material->content)) !!}</div>
                    @elseif ($material->type === 'pdf')
                        <a href="{{ $material->content }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-accent font-semibold text-sm">📄 Buka materi PDF ➜</a>
                    @endif
                </div>
            </div>
        @endforeach
        </div>
    </section>
</div>
<script>
function classProgress(initial, certUrl) {
    return { progress: initial, certUrl: certUrl };
}
async function toggleMaterial(url) {
    try {
        const r = await fetch(url, { method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
        if (!r.ok) return null;
        const d = await r.json();
        // Perbarui progress + link sertifikat di komponen root
        const root = document.querySelector('[x-data^="classProgress"]');
        if (root && root._x_dataStack) {
            root._x_dataStack[0].progress = d.progress;
            if (d.certificate_url) root._x_dataStack[0].certUrl = d.certificate_url;
        }
        return d;
    } catch (e) { return null; }
}
</script>
@endsection
