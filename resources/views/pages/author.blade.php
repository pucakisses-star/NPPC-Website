@extends('app')

@section('title'){{ $author->name }} | NPPC @endsection

@if($author->about)
@section('meta_description'){{ Str::limit(strip_tags($author->about), 155) }}@endsection
@endif

@if($author->avatar_url)
@section('og_image'){{ url($author->avatar_url) }}@endsection
@endif

@section('head')
<style>
    /* Staff-page layout modelled on major-outlet author pages: stacked
       header (avatar above a large name and a wide bio), then full-width
       article rows — big thumbnail, category + headline + dateline, and an
       excerpt column. Explicit CSS throughout — the compiled Tailwind
       bundle is missing the gap-* and sizing utilities. */
    .au-head { padding: 64px 0 8px; }
    .au-avatar { width: 176px; height: 176px; border-radius: 9999px; object-fit: cover; display: block; }
    .au-avatar-ph { width: 176px; height: 176px; border-radius: 9999px; background: linear-gradient(135deg, #1a1a2e, #2a2a4e); display: flex; align-items: center; justify-content: center; font-size: 60px; font-weight: 800; color: rgba(255,255,255,0.35); }
    .au-name { font-size: clamp(2.6rem, 5.5vw, 4rem); line-height: 1.05; font-weight: 800; color: var(--fg); margin: 36px 0 28px; letter-spacing: -0.01em; }
    .au-about { max-width: 780px; font-size: clamp(17px, 1.6vw, 21px); line-height: 1.65; color: rgba(var(--fg-rgb),0.8); }
    .au-contacts { margin-top: 48px; max-width: 520px; }
    .au-contacts-label { font-family: ui-monospace, 'SF Mono', Menlo, monospace; font-size: 14px; letter-spacing: 0.04em; color: rgba(var(--fg-rgb),0.55); padding-bottom: 14px; border-bottom: 1px solid rgba(var(--fg-rgb),0.25); }

    .au-list { margin-top: 72px; padding-bottom: 80px; }
    .au-row { display: grid; grid-template-columns: minmax(0, 46%) minmax(0, 1fr) minmax(0, 26%); gap: 40px; padding: 40px 0; border-top: 1px solid rgba(var(--fg-rgb),0.08); }
    .au-row:first-child { border-top: 0; padding-top: 0; }

    .au-row-img { display: block; aspect-ratio: 16 / 9.5; overflow: hidden; background: var(--surface-2); }
    .au-row-img img { width: 100%; height: 100%; object-fit: cover; object-position: center; display: block;
        transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1); will-change: transform; }
    .au-row:hover .au-row-img img,
    .au-row:focus-within .au-row-img img { transform: scale(1.05); }

    .au-row-cat { display: block; font-size: 15px; font-weight: 800; color: var(--accent); text-decoration: none; margin-bottom: 10px; }
    .au-row-title {
        font-size: clamp(19px, 1.9vw, 24px); font-weight: 800; line-height: 1.3; color: var(--fg);
        text-decoration: none; display: inline;
        background-image: linear-gradient(currentColor, currentColor);
        background-position: 0 100%; background-repeat: no-repeat; background-size: 0% 2px;
        padding-bottom: 2px;
        -webkit-box-decoration-break: clone; box-decoration-break: clone;
        transition: background-size 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .au-row:hover .au-row-title,
    .au-row:focus-within .au-row-title { background-size: 100% 2px; }
    .au-row-byline { margin-top: 14px; font-family: ui-monospace, 'SF Mono', Menlo, monospace; font-size: 13px; color: rgba(var(--fg-rgb),0.5); }
    .au-row-byline b { font-weight: 600; color: rgba(var(--fg-rgb),0.7); }
    .au-row-dek { font-size: 16.5px; line-height: 1.6; color: rgba(var(--fg-rgb),0.55); }

    .au-empty { padding: 48px 0 80px; color: rgba(var(--fg-rgb),0.55); font-size: 15px; }

    @media (prefers-reduced-motion: reduce) {
        .au-row-title, .au-row-img img { transition: none; }
        .au-row:hover .au-row-img img, .au-row:focus-within .au-row-img img { transform: none; }
    }
    @media (max-width: 900px) {
        .au-row { grid-template-columns: 1fr; gap: 18px; padding: 32px 0; }
        .au-row-dek { display: none; }
        .au-avatar, .au-avatar-ph { width: 132px; height: 132px; }
    }
</style>
@endsection

@section('body')
<main class="container">
    <div class="au-head">
        @if($author->avatar_url)
            <img class="au-avatar" src="{{ $author->avatar_url }}" alt="{{ $author->name }}">
        @else
            <div class="au-avatar-ph">{{ Str::upper(Str::substr($author->name, 0, 1)) }}</div>
        @endif
        <h1 class="au-name">{{ $author->name }}</h1>
        @if($author->about)
            <p class="au-about">{{ $author->about }}</p>
        @endif
        <div class="au-contacts">
            <div class="au-contacts-label">{{ $articles->total() }} {{ Str::plural('article', $articles->total()) }}</div>
        </div>
    </div>

    @if($articles->isNotEmpty())
        <div class="au-list">
            @foreach($articles as $article)
                <div class="au-row">
                    <a class="au-row-img" href="{{ $article->url }}" tabindex="-1" aria-hidden="true">
                        @if($article->image)
                            <img src="{{ $article->image_url }}" alt="" loading="lazy" decoding="async">
                        @endif
                    </a>
                    <div>
                        @if($article->category)
                            <span class="au-row-cat">{{ $article->category->title }}</span>
                        @endif
                        <a class="au-row-title" href="{{ $article->url }}">{{ $article->title }}</a>
                        <div class="au-row-byline"><b>{{ $author->name }}</b> &ndash; {{ $article->published_at?->format('F j, Y') }}</div>
                    </div>
                    <div class="au-row-dek">{{ Str::limit(trim(strip_tags($article->intro ?: $article->body)), 180) }}</div>
                </div>
            @endforeach
        </div>
        {{ $articles->links('vendor.pagination.nppc') }}
    @else
        <p class="au-empty">No published articles yet.</p>
    @endif
</main>
@endsection
