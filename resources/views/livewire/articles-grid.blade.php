<?php

use App\Models\Article;

function renderArticle(Article $article, bool $large = false, bool $eager = false): string {
    if(!$article) return '';

    // Hero keeps its tall fixed height; the smaller cards use a landscape
    // 3:2 aspect-ratio so they never collapse to a square (which cropped the
    // photos) as the grid columns narrow.
    $imgBox = $large ? 'aspect-ratio: 4 / 3' : 'aspect-ratio: 3 / 2';
    $imgUrl = $article->image ? $article->image_url : '';
    $loadingAttr = $eager ? 'eager' : 'lazy';
    $fetchPriority = $eager ? 'high' : 'auto';

    $imageMarkup = $imgUrl
        ? "<a href=\"{$article->url}\" style=\"display: block; {$imgBox}; overflow: hidden; background:var(--surface-2);\"><img src=\"{$imgUrl}\" alt=\"\" loading=\"{$loadingAttr}\" decoding=\"async\" fetchpriority=\"{$fetchPriority}\" style=\"width:100%; height:100%; object-fit:cover; object-position:center; display:block;\"></a>"
        : "<a href=\"{$article->url}\" class=\"article-img-empty\" style=\"display: block; {$imgBox}; background:var(--surface-2);\"></a>";

    $category = $article->category?->title;
    $date = $article->published_at?->format('F j, Y');

    $meta = '';
    if ($category && $date) {
        $meta = "<span style='text-transform:uppercase;'>{$category}</span> &nbsp;|&nbsp; {$date}";
    } elseif ($category) {
        $meta = "<span style='text-transform:uppercase;'>{$category}</span>";
    } elseif ($date) {
        $meta = $date;
    }

    return <<<EOB
<div class="article-item" style="margin-bottom: 24px;">
    {$imageMarkup}
    <div class="line"></div>
    <h5 style="margin-top: 16px; font-size: 13px; color: rgba(var(--fg-rgb),0.5); letter-spacing: 0.02em;">{$meta}</h5>
    <div style="margin-top: 4px;"><a class="article-title" style="font-size: 18px; color: var(--fg); line-height: 1.4;" href="{$article->url}">{$article->title}</a></div>
</div>
EOB;

}
?>
<section >
    <h1 style="font-size: 3.75rem; margin-top: 48px; margin-bottom: 48px; font-weight: 300; color: var(--fg);">News</h1>
    <div class = "line mt-8" ></div >

    <div class = "py-12" >
        <div style="display: flex; justify-content: center; gap: 24px; flex-wrap: wrap; margin-bottom: 48px;">
            <button
                    wire:click = "selectCategory('Latest')"
                    style="text-transform: uppercase; font-size: 14px; font-weight: 600; letter-spacing: 0.08em; padding-bottom: 8px; border-bottom: 2px solid {{ $selectedCategory === 'Latest' ? 'var(--accent)' : 'transparent' }}; background: none; color: var(--fg); cursor: pointer;">
                Latest
            </button >
            @foreach ($categories as $category)
                <button
                        wire:click="selectCategory(@js($category->title))"
                        style="text-transform: uppercase; font-size: 14px; font-weight: 600; letter-spacing: 0.08em; padding-bottom: 8px; border-bottom: 2px solid {{ $selectedCategory === $category->title ? 'var(--accent)' : 'transparent' }}; background: none; color: var(--fg); cursor: pointer;">
                    {{ $category->title }}
                </button>
            @endforeach
        </div>

        <?php
            // The hero layout (1 large + 4 smaller) is page-1 only.
            // Pages 2+ render every article in a uniform 3-col grid.
            $isPaginator = method_exists($articles, 'currentPage');
            $showHero    = !$isPaginator || $articles->currentPage() === 1;
        ?>

        @if ($showHero)
            {{-- Hero row: 1 large article on the left, up to 4 smaller on the right.
                 Inline grid styles so we don't depend on Tailwind gap-* classes
                 being in the compiled CSS bundle. --}}
            <div class="news-hero-row">
                <div>
                    <?php $x = 0; foreach ($articles as $article) {
                        $x++; if ($x === 2) break; ?>
                    {!! renderArticle($article, true, true) !!}
                    <?php } ?>
                </div>

                <div class="news-hero-sub">
                    <?php $x = 0; foreach ($articles as $article) {
                        $x++; if ($x === 1) continue;  if ($x === 6) break; ?>
                    {!! renderArticle($article) !!}
                    <?php } ?>
                </div>
            </div>


            {{-- Rest of the grid: 3 cols on desktop, 2 on tablet, 1 on mobile.
                 Explicit gap so spacing is guaranteed. --}}
            <div class="news-rest-grid">
                <?php $x = 0; foreach ($articles as $article) {
                    $x++; if ($x < 6) continue; ?>
                {!! renderArticle($article) !!}
                <?php } ?>
            </div>
        @else
            {{-- Pages 2+: uniform 3-col grid, no hero. --}}
            <div class="news-rest-grid">
                @foreach ($articles as $article)
                    {!! renderArticle($article) !!}
                @endforeach
            </div>
        @endif

        <style>
            /* Animated underline on article titles: grows in from the left
               when the card is highlighted (hovered or keyboard-focused).
               The anchor is inline with box-decoration-break: clone so every
               wrapped line paints its own underline, not just the last. */
            .article-item .article-title {
                text-decoration: none;
                display: inline;
                background-image: linear-gradient(currentColor, currentColor);
                background-position: 0 100%;
                background-repeat: no-repeat;
                background-size: 0% 1.5px;
                padding-bottom: 2px;
                -webkit-box-decoration-break: clone;
                box-decoration-break: clone;
                transition: background-size 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .article-item:hover .article-title,
            .article-item:focus-within .article-title {
                background-size: 100% 1.5px;
            }

            /* Imageless articles keep their grey placeholder box on desktop,
               where it holds the grid rhythm. In the single-column phone
               layout the same box is a full-width empty slab that reads as a
               broken image, so collapse it there — the card is then just
               meta + title, which is what an imageless story is. */
            @media (max-width: 767px) {
                .article-item .article-img-empty { display: none !important; }
            }

            /* Gentle zoom on the card's photo while it's highlighted. The
               image link already clips with overflow: hidden. */
            .article-item a img {
                transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
                will-change: transform;
            }
            .article-item:hover a img,
            .article-item:focus-within a img {
                transform: scale(1.05);
            }

            @media (prefers-reduced-motion: reduce) {
                .article-item .article-title { transition: none; }
                .article-item a img { transition: none; }
                .article-item:hover a img,
                .article-item:focus-within a img { transform: none; }
            }

            .news-hero-row {
                display: grid;
                grid-template-columns: 1fr;
                gap: 32px;
                margin-bottom: 48px;
            }
            .news-hero-sub {
                display: grid;
                grid-template-columns: 1fr;
                gap: 24px;
            }
            .news-rest-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 48px;
            }
            @media (min-width: 768px) {
                .news-hero-row { grid-template-columns: 1fr 1fr; }
                .news-hero-sub { grid-template-columns: 1fr 1fr; }
                .news-rest-grid { grid-template-columns: 1fr 1fr; }
            }
            @media (min-width: 1024px) {
                .news-rest-grid { grid-template-columns: 1fr 1fr 1fr; }
            }
        </style>

        @if(method_exists($articles, 'links'))
            {{ $articles->links('vendor.pagination.nppc') }}
        @endif

    </div>

</section>
