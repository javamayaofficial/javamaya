@extends('layouts.public', ['hideChrome' => ! $page->show_header])
@section('title', $page->meta_title ?: $page->title)
@push('head')
    @if ($page->meta_desc)<meta name="description" content="{{ $page->meta_desc }}">@endif
@endpush
@section('content')
<article class="jm-salespage max-w-3xl mx-auto px-4 py-10">
    {!! $page->content_html !!}
</article>
@endsection
