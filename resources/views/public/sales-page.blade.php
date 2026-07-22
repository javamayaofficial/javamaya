@php
    $fullCanvas = $page->is_homepage || (! $page->show_header && ! $page->show_footer);
    $rendered = $rendered ?? [
        'is_full_document' => false,
        'head_html' => '',
        'body_html' => $page->content_html,
        'body_class' => '',
        'body_style' => '',
        'title' => null,
        'meta_desc' => null,
    ];
    $resolvedTitle = $page->meta_title ?: ($rendered['title'] ?: $page->title);
    $resolvedMetaDesc = $page->meta_desc ?: ($rendered['meta_desc'] ?: null);
    $useCanvasWrapper = $fullCanvas || $rendered['is_full_document'];
@endphp
@extends('layouts.public', [
    'hideChrome' => $fullCanvas || ! $page->show_header,
    'hideFooter' => $fullCanvas || ! $page->show_footer,
    'bodyClass' => $rendered['body_class'] ?? '',
    'bodyStyle' => $rendered['body_style'] ?? null,
])
@section('title', $resolvedTitle)
@push('head')
    @if ($resolvedMetaDesc)<meta name="description" content="{{ $resolvedMetaDesc }}">@endif
    {!! $rendered['head_html'] ?? '' !!}
    <style>
        .jm-salespage-fullcanvas {
            width: 100%;
            min-height: 100vh;
        }
    </style>
@endpush
@section('content')
<article class="{{ $useCanvasWrapper ? 'jm-salespage-fullcanvas' : 'jm-salespage max-w-3xl mx-auto px-4 py-10' }}">
    {!! $rendered['body_html'] ?? $page->content_html !!}
</article>
@endsection
