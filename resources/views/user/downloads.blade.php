@extends('layouts.member')
@section('title', 'Downloads')
@section('member_eyebrow', 'Downloads')
@section('member_title', 'File Downloads')
@section('member_subtitle', 'Semua aset digital yang Anda miliki ditampilkan seperti pusat unduhan member, lengkap dengan produk asal dan ukuran file.')
@section('member_content')
<section class="jm-member-panel">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="jm-member-panel-title">Pusat unduhan</h2>
            <p class="jm-member-panel-copy">Setiap link download aman dan berlaku {{ config('javamaya.download_token_ttl_minutes') }} menit sejak dibuat.</p>
        </div>
    </div>

    @if ($files->isEmpty())
        <div class="jm-member-empty">
            <div>
                <div class="jm-member-empty-title">Belum ada file download</div>
                <div class="jm-member-empty-copy">Begitu Anda membeli produk dengan file digital, semua unduhan akan muncul di sini.</div>
            </div>
        </div>
    @else
        <div class="mt-6 overflow-x-auto">
            <table class="jm-member-table min-w-[720px]">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Produk</th>
                        <th>Ukuran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($files as $file)
                        <tr>
                            <td>
                                <div class="font-bold text-[#072347]">{{ $file->label }}</div>
                                <div class="mt-1 text-xs text-muted">File siap diunduh kapan saja.</div>
                            </td>
                            <td class="text-sm text-[#072347]">{{ $file->product?->name }}</td>
                            <td class="text-sm text-muted">{{ number_format($file->file_size / 1024 / 1024, 1) }} MB</td>
                            <td>
                                <a href="{{ route('user.download.request', $file->id) }}" class="jm-member-btn jm-member-btn--primary">Download</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
