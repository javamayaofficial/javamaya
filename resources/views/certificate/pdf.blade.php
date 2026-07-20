<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>
    @page { margin: 0; }
    body { font-family: DejaVu Sans, sans-serif; margin: 0; color: #1e293b;
        @if ($background) background: url('{{ storage_path('app/public/' . $background) }}') no-repeat center; background-size: cover; @endif }
    .wrap { padding: 90px 70px; text-align: center; }
    .title { font-size: 34px; font-weight: bold; letter-spacing: 4px; text-transform: uppercase; }
    .sub { color: #64748b; margin-top: 6px; }
    .name { font-size: 40px; font-weight: bold; margin: 36px 0 6px; border-bottom: 2px solid #1e293b; display: inline-block; padding: 0 30px 8px; }
    .class { font-size: 18px; margin-top: 18px; }
    .meta { margin-top: 46px; color: #64748b; font-size: 12px; }
    .code { font-family: DejaVu Sans Mono, monospace; font-weight: bold; }
</style></head>
<body>
    <div class="wrap">
        <div class="title">Sertifikat Penyelesaian</div>
        <div class="sub">Diberikan kepada</div>
        <div class="name">{{ $certificate->participant_name }}</div>
        <div class="class">atas penyelesaian kelas<br><b>{{ $certificate->classRoom->title }}</b></div>
        <div class="meta">
            Diterbitkan {{ $certificate->issued_at->translatedFormat('d F Y') }} oleh <b>{{ $store_name }}</b><br>
            Kode verifikasi: <span class="code">{{ $certificate->code }}</span> •
            Cek keaslian di {{ route('certificate.verify', $certificate->code) }}
        </div>
    </div>
</body></html>
