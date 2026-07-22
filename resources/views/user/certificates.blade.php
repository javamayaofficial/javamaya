@extends('layouts.member')
@section('title', 'Sertifikat Saya')
@section('member_eyebrow', 'Sertifikat')
@section('member_title', 'Sertifikat Saya')
@section('member_subtitle', 'Semua sertifikat pembelajaran yang berhasil Anda dapatkan ditampilkan dalam satu halaman khusus.')
@section('member_content')
<section class="jm-member-panel">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="jm-member-panel-title">Daftar sertifikat</h2>
            <p class="jm-member-panel-copy">Sertifikat akan muncul otomatis saat progress kelas mencapai syarat kelulusan.</p>
        </div>
        <span class="jm-member-badge jm-member-badge--ok">{{ $certificates->count() }} sertifikat</span>
    </div>

    @if ($certificates->isEmpty())
        <div class="jm-member-empty">
            <div>
                <div class="jm-member-empty-title">Belum ada sertifikat</div>
                <div class="jm-member-empty-copy">Selesaikan materi kelas Anda untuk membuka sertifikat secara otomatis di halaman ini.</div>
            </div>
        </div>
    @else
        <div class="mt-6 grid gap-4 md:grid-cols-2">
            @foreach ($certificates as $certificate)
                <div class="jm-member-card">
                    <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-[rgba(7,35,71,0.45)]">Certificate</div>
                    <div class="mt-2 font-bold text-[#072347]">{{ $certificate->classRoom?->title ?? 'Kelas premium' }}</div>
                    <div class="mt-2 text-sm text-muted">Kode {{ $certificate->code }}</div>
                    <div class="mt-1 text-sm text-muted">Terbit {{ $certificate->issued_at?->translatedFormat('d M Y') ?? $certificate->created_at?->translatedFormat('d M Y') }}</div>
                    <div class="mt-5">
                        <a href="{{ route('user.certificate.download', $certificate->id) }}" class="jm-member-btn jm-member-btn--primary">Download PDF</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection
