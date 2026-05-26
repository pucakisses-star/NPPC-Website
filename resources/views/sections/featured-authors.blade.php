@php
    use App\Models\Author;
    use App\Models\Article;

    // Org-attribution placeholders we never want to surface as "authors."
    $excludedNames = [
        'National Political Prisoner Coalition',
        'NPPC',
        'NPPC Editorial',
        'NPPC Staff',
        'Staff',
        'Editorial',
        'Editor',
        'Unknown',
    ];

    // Real authors only: must have an avatar AND at least one published article
    // AND not be one of the org-attribution placeholders.
    $featuredAuthors = Author::query()
        ->whereHas('articles', fn ($q) => $q->whereNotNull('published_at'))
        ->whereNotNull('avatar')
        ->where('avatar', '!=', '')
        ->whereNotIn('name', $excludedNames)
        ->withMax('articles as latest_pub', 'published_at')
        ->orderByDesc('latest_pub')
        ->limit(8)
        ->get();

    // Each author's most recent article for the snippet under the card.
    $latestByAuthor = Article::query()
        ->whereIn('author_id', $featuredAuthors->pluck('id'))
        ->whereNotNull('published_at')
        ->orderByDesc('published_at')
        ->get()
        ->groupBy('author_id')
        ->map(fn ($collection) => $collection->first());

    // Drop any author whose latest article didn't come through (data drift safety).
    $featuredAuthors = $featuredAuthors->filter(fn ($a) => isset($latestByAuthor[$a->id]))->values();
@endphp
@if ($featuredAuthors->isNotEmpty())
<section class="fa-section">
    <div class="fa-head">
        <h2 class="fa-title">Featured Authors</h2>
        <a class="fa-more" href="/news">More articles &rsaquo;</a>
    </div>

    <div class="fa-grid">
        @foreach ($featuredAuthors as $author)
            @php $latest = $latestByAuthor[$author->id]; @endphp
            <div class="fa-card">
                <div class="fa-card-top">
                    <img class="fa-avatar" src="{{ $author->avatar_url }}" alt="{{ $author->name }}" loading="lazy" decoding="async">
                    <div class="fa-meta">
                        <div class="fa-name">{{ $author->name }}</div>
                        @if ($author->about)
                            <div class="fa-role">{{ \Illuminate\Support\Str::limit(strip_tags($author->about), 60) }}</div>
                        @endif
                    </div>
                </div>
                <div class="fa-bottom">
                    <div class="fa-divider"></div>
                    <a class="fa-article" href="{{ $latest->url }}">{{ $latest->title }}</a>
                </div>
            </div>
        @endforeach
    </div>
</section>
<style>
    .fa-section { padding: 64px 0 32px; }
    .fa-head { display: flex; align-items: end; justify-content: space-between; gap: 16px; margin-bottom: 24px; border-bottom: 2px solid #5660fe; padding-bottom: 12px; }
    .fa-title { font-size: 1.6rem; font-weight: 900; color: #fff; margin: 0; text-transform: uppercase; letter-spacing: 0.02em; }
    .fa-more { font-size: 13px; font-weight: 700; color: rgba(255,255,255,0.7); text-decoration: none; text-transform: uppercase; letter-spacing: 0.06em; }
    .fa-more:hover { color: #5660fe; }

    /* Equal-height cards via grid-auto-rows: 1fr + flex column with the
       article block bottom-pinned. */
    .fa-grid { display: grid; grid-template-columns: repeat(4, 1fr); grid-auto-rows: 1fr; gap: 0; }
    .fa-card { display: flex; flex-direction: column; gap: 16px; padding: 16px 18px; border-right: 1px solid rgba(255,255,255,0.08); min-height: 280px; }
    .fa-card:nth-child(4n) { border-right: 0; }

    .fa-card-top { display: flex; gap: 14px; align-items: flex-start; }
    .fa-avatar { width: 84px; height: 84px; border-radius: 4px; object-fit: cover; flex-shrink: 0; background: #1a1a2e; }
    .fa-meta { min-width: 0; padding-top: 4px; }
    .fa-name { font-size: 14px; font-weight: 800; color: #5660fe; line-height: 1.2; }
    .fa-role { font-size: 12px; color: rgba(255,255,255,0.55); line-height: 1.4; margin-top: 6px; }

    .fa-bottom { margin-top: auto; }
    .fa-divider { height: 2px; width: 36px; background: #ff5851; margin-bottom: 10px; }
    .fa-article { font-size: 15px; font-weight: 800; color: #fff; line-height: 1.3; text-decoration: none; display: block; }
    .fa-article:hover { color: #5660fe; }

    @media (max-width: 1024px) {
        .fa-grid { grid-template-columns: repeat(2, 1fr); }
        .fa-card:nth-child(4n) { border-right: 1px solid rgba(255,255,255,0.08); }
        .fa-card:nth-child(2n) { border-right: 0; }
        .fa-card { border-bottom: 1px solid rgba(255,255,255,0.08); }
    }
    @media (max-width: 640px) {
        .fa-section { padding: 40px 0 24px; }
        .fa-grid { grid-template-columns: 1fr; }
        .fa-card { border-right: 0; min-height: 0; padding: 16px 0; }
        .fa-card:last-child { border-bottom: 0; }
        .fa-avatar { width: 64px; height: 64px; }
        .fa-title { font-size: 1.25rem; }
    }
</style>
@endif
