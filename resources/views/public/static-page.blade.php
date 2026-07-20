@extends('layouts.public')
@section('title', $page->title)
@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-extrabold">{{ $page->title }}</h1>
    <div class="prose prose-slate mt-6 max-w-none">{!! $html !!}</div>
</div>
@endsection
