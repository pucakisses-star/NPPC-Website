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
    /* Header: avatar left, name + bio right (Intercept-style staff page).
       Explicit CSS throughout — the compiled Tailwind bundle is missing the
       gap-* and sizing utilities these layouts would otherwise use. */
    .au-head { display: flex; gap: 36px; align-items: flex-start; padding: 56px 0 44px; border-bottom: 1px solid rgba(var(--fg-rgb),0.1); }
    .au-avatar { flex: 0 0 128px; width: 128px; height: 128px; border-radius: 9999px; object-fit: cover; }
    .au-avatar-ph { flex: 0 0 128px; width: 128px; height: 128px; border-radius: 9999px; background: linear-gradient(135deg, #1a1a2e, #2a2a4e); display: flex; align-items: center; justify-content: center; font-size: 44px; font-weight: 800; color: rgba(255,255,255,0.35); }
    .au-kicker { font-size: 12px; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; color: var(--accent); margin-bottom: 10px; }
    .au-name { font-size: clamp(2.2rem, 4.5vw, 3.2rem); line-height: 1.1; font-weight: 800; color: var(--fg); margin-bottom: 14px; }
    .au-about { max-width: 720px; font-size: 16px; line-height: 1.7; color: rgba(var(--fg-rgb),0.72); }
    .au-count { margin-top: 14px; font-size: 12.5px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(var(--fg-rgb),0.45); }

    .au-grid-head { font-size: 13px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(var(--fg-rgb),0.5); margin: 44px 0 28px; }
    .au-grid { display: grid; grid-template-columns: 1fr; gap: 48px; padding-bottom: 64px; }
    @media (min-width: 700px) { .au-grid { grid-template-columns: 1fr 1fr; } }
    @media (min-width: 1024px) { .au-grid { grid-template-columns: 1fr 1fr 1fr; } }

    /* Cards match the news grid, including its hover behaviour: gentle photo
       zoom and the per-line underline sweep on the title. */
    .article-item .article-img { display: block; height: 224px; overflow: hidden; background: var(--surface-2); }
    .article-item .article-img img { width: 100%; height: 100%; object-fit: cover; object-position: center; display: block;
        transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1); will-change: transform; }
    .article-item:hover .article-img img,
    .article-item:focus-within .article-img img { transform: scale(1.05); }
    .article-item .article-meta { margin-top: 16px; font-size: 13px; color: rgba(var(--fg-rgb),0.5); letter-spacing: 0.02em; }
    .article-item .article-meta span { text-transform: uppercase; }
    .article-item .article-title {
        font-size: 18px; color: var(--fg); line-height: 1.4; text-decoration: none; display: inline;
        background-image: linear-gradient(currentColor, currentColor);
        background-position: 0 100%; background-repeat: no-repeat; background-size: 0% 1.5px;
        padding-bottom: 2px;
        -webkit-box-decoration-break: clone; box-decoration-break: clone;
        transition: background-size 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .article-item:hover .article-title,
    .article-item:focus-within .article-title { background-size: 100% 1.5px; }
    @media (prefers-reduced-motion: reduce) {
        .article-item .article-title, .article-item .article-img img { transition: none; }
        .article-item:hover .article-img img, .article-item:focus-within .article-img img { transform: none; }
    }

    .au-empty { padding: 48px 0 80px; color: rgba(var(--fg-rgb),0.55); font-size: 15px; }

    @media (max-width: 640px) {
        .au-head { flex-direction: column; gap: 20px; }
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
        <div>
            <div class="au-kicker">Author</div>
            <h1 class="au-name">{{ $author->name }}</h1>
            @if($author->about)
                <p class="au-about">{{ $author->about }}</p>
            @endif
            <div class="au-count">{{ $articles->total() }} {{ Str::plural('article', $articles->total()) }}</div>
        </div>
    </div>

    @if($articles->isNotEmpty())
        <h2 class="au-grid-head">Latest from {{ $author->name }}</h2>
        <div class="au-grid">
            @foreach($articles as $article)
                <div class="article-item">
                    <a class="article-img" href="{{ $article->url }}" tabindex="-1" aria-hidden="true">
                        @if($article->image)
                            <img src="{{ $article->image_url }}" alt="" loading="lazy" decoding="async">
                        @endif
                    </a>
                    <div class="line"></div>
                    <h5 class="article-meta">
                        @if($article->category)<span>{{ $article->category->title }}</span> &nbsp;|&nbsp; @endif{{ $article->published_at?->format('F j, Y') }}
                    </h5>
                    <div style="margin-top: 4px;"><a class="article-title" href="{{ $article->url }}">{{ $article->title }}</a></div>
                </div>
            @endforeach
        </div>
        {{ $articles->links('vendor.pagination.nppc') }}
    @else
        <p class="au-empty">No published articles yet.</p>
    @endif
</main>
@endsection
