@php use App\Models\Article; @endphp
@php
    /**
     * @var Article $article
     */
@endphp
@extends('app')

@section('title'){{ $article->title }} | NPPC @endsection

@section('meta_description'){{ substr(trim(strip_tags($article->intro ?: $article->body)), 0, 170) }}@endsection

@if($article->image_url)
@section('og_image'){{ str_starts_with($article->image_url, 'http') ? $article->image_url : url($article->image_url) }}@endsection
@endif

{{-- Article typography: Raleway for headings, Roboto for running text, the
     pairing used by bcomber.org. Loaded here rather than in the layout so it
     costs nothing on the rest of the site, and scoped to .article-type so the
     site's own Verlag is untouched everywhere else — including the static
     pages, which share .page-content with this template. --}}
@section('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Both stacks fall back to the system UI font rather than to Verlag:
           if Google Fonts is blocked or slow, the page should look like a
           plain article, not like half of one. */
        .article-type,
        .article-type p,
        .article-type li,
        .article-type blockquote,
        .article-type figcaption,
        .article-type td,
        .article-type th {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
        }

        .article-type h1,
        .article-type h2,
        .article-type h3,
        .article-type h4,
        .article-type h5,
        .article-type h6,
        .article-type .article-title {
            font-family: 'Raleway', -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
        }

        /* Roboto sits a little smaller and tighter than Verlag at the same
           size, so the body copy is nudged back to a comfortable measure. */
        .article-type .page-content p {
            font-size: 1.0625rem;
            line-height: 1.75;
        }
    </style>
@endsection

@section('body')
    <div class="article-type">
    <div class="line mt-8"></div>

    {{-- Hero image first. The caption sits as its own line *below* the photo
         (not overlaid on it): only the <img> is clipped/rounded, so the
         figcaption renders outside that box as a plain attribution line. --}}
    @if($article->image_url)
        <figure style="margin: 48px 0 32px 0;">
            <img src="{{ $article->image_url }}" alt="{{ $article->title }}" style="display:block; width:100%; max-height:560px; object-fit:cover; object-position:center top; border-radius:8px; background:var(--surface-2);">
            @if($article->image_caption)
                <figcaption style="font-size:13px; color:rgba(var(--fg-rgb),0.55); font-style:italic; line-height:1.5; margin-top:10px;">{{ $article->image_caption }}</figcaption>
            @endif
        </figure>
    @endif

    {{-- Category label --}}
    @if($article->category)
        <div style="margin-top:{{ $article->image_url ? '24px' : '48px' }}; margin-bottom:16px;">
            <span style="display:inline-block; padding:4px 12px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; background:rgba(var(--fg-rgb),0.1); border:1px solid rgba(var(--fg-rgb),0.2); color:rgba(var(--fg-rgb),0.8);">{{ $article->category->title }}</span>
        </div>
    @endif

    <h1 style="font-size: clamp(2rem, 4.5vw, 3.5rem); line-height: 1.15; font-weight: 800; color: var(--fg); margin: {{ $article->category || $article->image_url ? '0' : '48px 0 0 0' }} 0 24px 0;">{{$article->title}}</h1>

    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; padding:16px 0; border-top:1px solid rgba(var(--fg-rgb),0.08); border-bottom:1px solid rgba(var(--fg-rgb),0.08);">
        @include('partials.articles.author')
        <div style="display:flex; align-items:center; gap:8px;">
            @include('partials.articles.share')
            <button onclick="window.print()" style="display:inline-flex; align-items:center; gap:8px; padding:8px 16px; font-size:13px; font-weight:600; color:var(--fg); background:transparent; border:1px solid rgba(var(--fg-rgb),0.2); cursor:pointer; transition:background 0.15s;" onmouseover="this.style.background='rgba(var(--fg-rgb),0.1)'" onmouseout="this.style.background='transparent'" class="print-hide" title="Print article">
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

    @if(!empty($article->author) && !empty($article->author['about']))
        @php $aboutAuthorUrl = $article->author['url'] ?? null; @endphp
        <div style="display:flex; gap:16px; align-items:flex-start; margin-top:48px; padding:24px; border:1px solid rgba(var(--fg-rgb),0.12); border-radius:8px; background:rgba(var(--fg-rgb),0.02);">
            @if($article->author['avatar_url'])
                @if($aboutAuthorUrl)<a href="{{ $aboutAuthorUrl }}" aria-label="More articles by {{ $article->author['name'] }}" style="flex-shrink:0;">@endif
                <img src="{{ $article->author['avatar_url'] }}" alt="{{ $article->author['name'] }}" style="width:64px; height:64px; border-radius:9999px; object-fit:cover; flex-shrink:0; display:block;">
                @if($aboutAuthorUrl)</a>@endif
            @endif
            <div>
                <div style="font-size:12px; text-transform:uppercase; letter-spacing:0.1em; color:rgba(var(--fg-rgb),0.5); margin-bottom:4px;">About the author</div>
                <div style="font-weight:700; font-size:18px; margin-bottom:6px;">
                    @if($aboutAuthorUrl)
                        <a href="{{ $aboutAuthorUrl }}" style="color:var(--fg); text-decoration:none; border-bottom:1px solid rgba(var(--fg-rgb),0.3); transition:border-color 0.15s;"
                           onmouseover="this.style.borderBottomColor='var(--fg)'" onmouseout="this.style.borderBottomColor='rgba(var(--fg-rgb),0.3)'">{{ $article->author['name'] }}</a>
                    @else
                        <span style="color:var(--fg);">{{ $article->author['name'] }}</span>
                    @endif
                </div>
                <div style="color:rgba(var(--fg-rgb),0.7); line-height:1.6; font-size:15px;">{{ $article->author['about'] }}</div>
                @if($aboutAuthorUrl)
                    <a href="{{ $aboutAuthorUrl }}" style="display:inline-block; margin-top:10px; font-size:13.5px; font-weight:700; color:var(--accent); text-decoration:none;">More from {{ $article->author['name'] }} &rarr;</a>
                @endif
            </div>
        </div>
    @endif

    {{-- Related / similar articles --}}
    @if(!empty($related) && $related->isNotEmpty())
        <section aria-labelledby="related-heading" style="margin-top:64px; padding-top:40px; border-top:1px solid rgba(var(--fg-rgb),0.1);">
            <h2 id="related-heading" style="font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:0.12em; color:rgba(var(--fg-rgb),0.5); margin:0 0 28px 0;">Related articles</h2>
            <div class="related-grid">
                @foreach($related as $rel)
                    <div class="article-item">
                        <a href="{{ $rel->url }}" style="display:block; height:224px; overflow:hidden; background:var(--surface-2);">
                            @if($rel->image_url)
                                <img src="{{ $rel->image_url }}" alt="" loading="lazy" decoding="async" onerror="this.style.display='none'" style="width:100%; height:100%; object-fit:cover; object-position:center; display:block;">
                            @endif
                        </a>
                        @if($rel->category)
                            <h5 style="margin-top:16px; font-size:13px; color:rgba(var(--fg-rgb),0.5); letter-spacing:0.02em; text-transform:uppercase;">{{ $rel->category->title }}</h5>
                        @endif
                        <a class="article-title" style="font-size:18px; color:var(--fg); display:block; margin-top:4px; line-height:1.4;" href="{{ $rel->url }}">{{ $rel->title }}</a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @include('sections.newsletter-signup', ['variant' => 'compact'])
    </div>{{-- /.article-type --}}

    <style>
        /* Related-articles grid: 3 across on desktop, 1 on mobile. */
        .related-grid { display:grid; grid-template-columns:1fr; gap:32px; }
        @@media (min-width:768px) { .related-grid { grid-template-columns:1fr 1fr 1fr; } }

        /* Animated underline on related-article titles, matching the news grid:
           grows in from the left when the card is highlighted. */
        .article-item .article-title {
            text-decoration:none;
            width:fit-content;
            max-width:100%;
            background-image:linear-gradient(currentColor, currentColor);
            background-position:0 100%;
            background-repeat:no-repeat;
            background-size:0% 1.5px;
            padding-bottom:2px;
            transition:background-size 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .article-item:hover .article-title,
        .article-item:focus-within .article-title { background-size:100% 1.5px; }
        @@media (prefers-reduced-motion: reduce) { .article-item .article-title { transition:none; } }
        article.page-content p { margin: 0 0 1.25em 0 !important; line-height: 1.75 !important; min-height: 1.2em; }
        article.page-content p:empty { min-height: 1.75em !important; }
        article.page-content br { display: block; margin-bottom: 0.75em; }
        .page-content h1 { font-size: 2.5rem; font-weight: 800; margin: 1.5em 0 0.75em; }
        .page-content h2 { font-size: 2rem; font-weight: 700; margin: 1.5em 0 0.75em; }
        .page-content h3 { font-size: 1.5rem; font-weight: 700; margin: 1.25em 0 0.5em; }
        .page-content h4 { font-size: 1.25rem; font-weight: 600; margin: 1em 0 0.5em; }
        .page-content ul, .page-content ol { margin: 1em 0; padding-left: 1.5em; }
        .page-content li { margin-bottom: 0.5em; line-height: 1.75; }
        .page-content blockquote { border-left: 3px solid rgba(var(--fg-rgb),0.3); padding-left: 1em; margin: 1.5em 0; color: rgba(var(--fg-rgb),0.7); }
        .page-content a { color: var(--accent-2); text-decoration: underline; }
        .page-content strong { font-weight: 700; }
        .page-content em { font-style: italic; }
        .page-content .lead { font-size: 1.25em; }
        .page-content small, .page-content .small { font-size: 0.875em; }
        .page-content img { max-width: 100%; height: auto; border-radius: 8px; margin: 1.5em 0; }
        /* Images with captions: the figure owns the vertical rhythm; the
           figcaption renders as a small, centered, muted attribution line
           (links inherit that styling instead of the accent link color).
           .img-caption is a fallback class for caption <p>s not wrapped in a figure. */
        .page-content figure { margin: 1.5em 0; }
        .page-content figure img { display: block; margin: 0 auto; }
        .page-content figcaption, .page-content .img-caption {
            text-align: center; font-size: 13px; font-style: italic;
            color: rgba(var(--fg-rgb),0.55); line-height: 1.5; margin-top: 10px;
        }
        .page-content figcaption a, .page-content .img-caption a {
            color: inherit; font-size: inherit;
            text-decoration: underline; text-decoration-color: rgba(var(--fg-rgb),0.35);
        }
        .page-content iframe, .page-content embed, .page-content object, .page-content video { max-width: 100%; width: 100%; border-radius: 8px; margin: 1.5em 0; }
        .page-content table { width: 100%; border-collapse: collapse; margin: 1.5em 0; display: block; overflow-x: auto; }
        .page-content th, .page-content td { border: 1px solid rgba(var(--fg-rgb),0.15); padding: 8px 12px; text-align: left; }

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
