@extends('layouts.public')
@section('title', 'Gangguan Pembayaran')
@section('content')
<div class="max-w-md mx-auto px-4 py-16 text-center">
    <div class="text-5xl">⚠️</div>
    <h1 class="mt-4 text-2xl font-extrabold">Metode pembayaran sedang gangguan</h1>
    <p class="mt-2 text-slate-500">{{ $message }}</p>
    <a href="{{ url()->previous() }}" class="mt-6 inline-block rounded-xl border border-slate-300 px-6 py-3 font-semibold hover:bg-slate-50">Pilih metode lain</a>
</div>
@endsection
