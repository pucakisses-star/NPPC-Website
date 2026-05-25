@php use App\Models\Article; @endphp
@php
    /**
     * @var Article $article
     */
@endphp
@extends('app')

@section('body')
    <div class="line mt-8"></div>

    {{-- Hero image first --}}
    @if($article->image_url)
        <figure style="margin: 48px 0 {{ $article->image_caption ? '8px' : '32px' }} 0; border-radius:8px; overflow:hidden; background:#0a0a0a;">
            <img src="{{ $article->image_url }}" alt="{{ $article->title }}" style="display:block; width:100%; max-height:560px; object-fit:cover; object-position:center top;">
            @if($article->image_caption)
                <figcaption style="font-size:13px; color:rgba(255,255,255,0.4); font-style:italic; padding:8px 0 0 0;">{{ $article->image_caption }}</figcaption>
            @endif
        </figure>
    @endif

    {{-- Category label --}}
    @if($article->category)
        <div style="margin-top:{{ $article->image_url ? '24px' : '48px' }}; margin-bottom:16px;">
            <span style="display:inline-block; padding:4px 12px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color:rgba(255,255,255,0.8);">{{ $article->category->title }}</span>
        </div>
    @endif

    <h1 style="font-size: clamp(2rem, 4.5vw, 3.5rem); line-height: 1.15; font-weight: 800; color: #fff; margin: {{ $article->category || $article->image_url ? '0' : '48px 0 0 0' }} 0 24px 0;">{{$article->title}}</h1>

    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; padding:16px 0; border-top:1px solid rgba(255,255,255,0.08); border-bottom:1px solid rgba(255,255,255,0.08);">
        @include('partials.articles.author')
        <div style="display:flex; align-items:center; gap:8px;">
            @include('partials.articles.share')
            <button onclick="window.print()" style="display:inline-flex; align-items:center; gap:8px; padding:8px 16px; font-size:13px; font-weight:600; color:#fff; background:transparent; border:1px solid rgba(255,255,255,0.2); cursor:pointer; transition:background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='transparent'" class="print-hide" title="Print article">
                <svg xmlns="http://www.w3.org/2000/svg" style="height:16px; width:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print
            </button>
        </div>
    </div>
    <article class="mt-12 page-content">
        {!! $article->body !!}

        <div class="flex justify-between mt-12">
            @include('partials.articles.tags', ['size'=>'normal'])
            @include('partials.articles.citations')
        </div>

        @include('partials.articles.cite')
    </article>

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
        .page-content iframe, .page-content embed, .page-content object, .page-content video { max-width: 100%; width: 100%; border-radius: 8px; margin: 1.5em 0; }
        .page-content table { width: 100%; border-collapse: collapse; margin: 1.5em 0; display: block; overflow-x: auto; }
        .page-content th, .page-content td { border: 1px solid rgba(255,255,255,0.15); padding: 8px 12px; text-align: left; }

        @media (max-width: 768px) {
            article.page-content { font-size: 17px; line-height: 1.7; }
            .page-content h1 { font-size: 1.8rem; }
            .page-content h2 { font-size: 1.5rem; }
            .page-content h3 { font-size: 1.25rem; }
            .page-content blockquote { padding-left: 0.75em; }
        }

        @@media print {
            body { background: #fff !important; color: #000 !important; }
            nav, footer, .print-hide, .scroll-top { display: none !important; }
            h1, h2, h3, h4, .page-content, .page-content p, .page-content li, .page-content blockquote { color: #000 !important; }
            .page-content a { color: #333 !important; }
            article.page-content { margin-top: 1rem !important; }
        }
    </style>
@endsection
