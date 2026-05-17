<?php

use App\Models\Article;

function renderArticle(Article $article, bool $large = false): string {
    if(!$article) return '';

    $imgHeight = $large ? '600px' : '224px';
    $imgUrl = $article->image ? $article->image_url : '';
    $bgStyle = $imgUrl ? "background-image: url('{$imgUrl}');" : 'background-color: #1a1a1a;';
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
    <a href="{$article->url}" style="display: block; height: {$imgHeight}; overflow: hidden; background-size: cover; background-position: center; {$bgStyle}"></a>
    <div class="line"></div>
    <h5 style="margin-top: 16px; font-size: 13px; color: rgba(255,255,255,0.5); letter-spacing: 0.02em;">{$meta}</h5>
    <a style="font-size: 18px; color: #fff; display: block; margin-top: 4px; line-height: 1.4;" href="{$article->url}">{$article->title}</a>
</div>
EOB;

}
?>
<section >
    <h1 style="font-size: 3.75rem; margin-top: 48px; margin-bottom: 48px; font-weight: 300; color: #fff;">News</h1>
    <div class = "line mt-8" ></div >

    <div class = "py-12" >
        <div style="display: flex; justify-content: center; gap: 24px; flex-wrap: wrap; margin-bottom: 48px;">
            <button
                    wire:click = "selectCategory('Latest')"
                    style="text-transform: uppercase; font-size: 14px; font-weight: 600; letter-spacing: 0.08em; padding-bottom: 8px; border-bottom: 2px solid {{ $selectedCategory === 'Latest' ? '#6366f1' : 'transparent' }}; background: none; color: #fff; cursor: pointer;">
                Latest
            </button >
            @foreach ($categories as $category)
                <button
                        wire:click="selectCategory('{{ $category->title }}')"
                        style="text-transform: uppercase; font-size: 14px; font-weight: 600; letter-spacing: 0.08em; padding-bottom: 8px; border-bottom: 2px solid {{ $selectedCategory === $category->title ? '#6366f1' : 'transparent' }}; background: none; color: #fff; cursor: pointer;">
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
                    {!! renderArticle($article, true) !!}
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
