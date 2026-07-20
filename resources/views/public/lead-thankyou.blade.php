@extends('layouts.public')
@section('title', 'Berhasil!')
@section('content')
<div class="max-w-md mx-auto px-4 py-14 text-center">
    <div class="text-5xl">✅</div>
    <h1 class="mt-4 text-2xl font-extrabold">Akses terkirim, {{ $lead->name }}!</h1>
    <p class="mt-2 text-slate-500">Link <b>{{ $magnet->title }}</b> sudah dikirim ke WhatsApp Anda@if($lead->email) & email @endif.</p>
    @if ($magnet->deliverable_url)
        <a href="{{ $magnet->deliverable_url }}" class="mt-6 inline-block btn-accent text-white font-bold rounded-xl px-6 py-3.5">Atau buka langsung di sini ➜</a>
    @endif
</div>
@endsection
