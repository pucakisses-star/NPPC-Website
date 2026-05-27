@php use App\Models\Page; @endphp
@php
    /**
     * @var Page $page
     */
@endphp
@extends('app')

@section('body')
    <div class="line mt-8"></div>
    <h1 class="text-6xl mt-12">{{$page->title}}</h1>
    <div class=" h-[420px] rounded-lg mt-12 mb-6 overflow-hidden  justify-center items-center bg-center bg-cover"
         style="background-image: url('{{ $page->image_url }}')">
    </div>
    <article class="mt-12 page-content">
        {!! $page->body !!}
    </article>

    @if ($page->slug === 'get-involved')
        @include('sections.active-petitions')
    @endif

    <style>
        article.page-content p { margin: 0 0 1.25em 0 !important; line-height: 1.75 !important; min-height: 1.2em; }
        article.page-content p:empty { min-height: 1.75em !important; }
        article.page-content br { display: block; margin-bottom: 0.75em; }
        .page-content h1 { font-size: 2.5rem; font-weight: 800; margin: 1.5em 0 0.75em; }
        .page-content h2 { font-size: 2rem; font-weight: 700; margin: 1.5em 0 0.75em; }
        .page-content h3 { font-size: 1.5rem; font-weight: 700; margin: 1.25em 0 0.5em; }
        .page-content h4 { font-size: 1.25rem; font-weight: 600; margin: 1em 0 0.5em; }
        .page-content ul, .page-content ol { margin: 1em 0; padding-left: 1.5em; }
        .page-content li { margin-bottom: 0.5em; line-height: 1.75; }
        .page-content blockquote { border-left: 3px solid rgba(255,255,255,0.3); padding-left: 1em; margin: 1.5em 0; color: rgba(255,255,255,0.7); }
        .page-content a { color: #6366f1; text-decoration: underline; }
        .page-content strong { font-weight: 700; }
        .page-content em { font-style: italic; }
        .page-content .lead { font-size: 1.25em; }
        .page-content small, .page-content .small { font-size: 0.875em; }
        .page-content img { max-width: 100%; height: auto; border-radius: 8px; margin: 1.5em 0; }
        .page-content table { width: 100%; border-collapse: collapse; margin: 1.5em 0; }
        .page-content th, .page-content td { border: 1px solid rgba(255,255,255,0.15); padding: 8px 12px; text-align: left; }
    </style>
@endsection
