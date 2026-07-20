@extends('layouts.public')
@section('title', $class->title)
@section('content')
<div class="max-w-3xl mx-auto px-4 py-8"
     x-data="classProgress({{ $progress }}, @js($certificate ? route('user.certificate.download', $certificate->id) : null))">
    <h1 class="text-2xl font-extrabold">{{ $class->title }}</h1>
    @if ($expiresAt)
        <div class="mt-1 text-sm text-amber-600 font-semibold">Akses hingga {{ $expiresAt->translatedFormat('d F Y') }}</div>
    @endif

    <div class="mt-4 jm-card p-4">
        <div class="flex justify-between text-sm font-semibold"><span>Progress</span><span x-text="progress + '%'"></span></div>
        <div class="mt-2 h-3 rounded-full bg-stone-100 overflow-hidden">
            <div class="h-full btn-accent transition-all duration-500" :style="'width:' + progress + '%'"></div>
        </div>
        <template x-if="certUrl">
            <a :href="certUrl" class="mt-3 inline-flex items-center gap-2 text-emerald-600 font-bold text-sm">🎓 Download Sertifikat PDF ➜</a>
        </template>
    </div>

    <div class="mt-6 space-y-3">
        @foreach ($class->materials as $material)
            <div class="jm-card p-4"
                 x-data="{ done: {{ in_array($material->id, $completedIds) ? 'true' : 'false' }}, busy: false }">
                <div class="flex items-start justify-between gap-3">
                    <div class="font-bold">{{ $loop->iteration }}. {{ $material->title }}</div>
                    <button @click="busy = true; toggleMaterial('{{ route('user.material.toggle', $material->id) }}')
                                .then(d => { if (d) { done = !done; } busy = false; })"
                            :disabled="busy"
                            class="shrink-0 text-sm font-semibold rounded-full px-3 py-1.5 border min-h-[36px] transition disabled:opacity-50"
                            :class="done ? 'bg-emerald-50 border-emerald-300 text-emerald-700' : 'border-slate-300 text-slate-500'">
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
