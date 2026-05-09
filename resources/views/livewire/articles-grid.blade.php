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

        @if($this->page === 1)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-12" style="gap: 32px;">

                <div>
                    <?php $x = 0; foreach ($articles as $article) {
                        $x++; if ($x === 2) break; ?>
                    {!! renderArticle($article, true) !!}
                    <?php } ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" style="gap: 32px;">
                    <?php $x = 0; foreach ($articles as $article) {
                        $x++; if ($x === 1) continue;  if ($x === 6) break; ?>
                    {!! renderArticle($article) !!}
                    <?php } ?>
                </div>
            </div>


            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12" style="gap: 32px;">
                <?php $x = 0; foreach ($articles as $article) {
                    $x++; if ($x < 6) continue; ?>
                {!! renderArticle($article) !!}
                <?php } ?>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12" style="gap: 32px;">
                @foreach($articles as $article)
                    {!! renderArticle($article) !!}
                @endforeach
            </div>
        @endif

        @if($this->totalPages > 1)
            <nav aria-label="News pagination" style="display:flex; justify-content:center; align-items:center; gap:16px; margin-top:48px; flex-wrap:wrap;">
                <button
                    wire:click="prevPage"
                    @disabled($this->page <= 1)
                    style="padding:12px 24px; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.18); color:#fff; border-radius:24px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; cursor:pointer; opacity:{{ $this->page <= 1 ? '0.35' : '1' }};">
                    &larr; Previous
                </button>
                <span style="font-size:14px; color:rgba(255,255,255,0.6); letter-spacing:0.04em;">
                    Page {{ $this->page }} of {{ $this->totalPages }}
                </span>
                <button
                    wire:click="nextPage"
                    @disabled($this->page >= $this->totalPages)
                    style="padding:12px 24px; background:#5660fe; border:1px solid #5660fe; color:#fff; border-radius:24px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; cursor:pointer; opacity:{{ $this->page >= $this->totalPages ? '0.35' : '1' }};">
                    Next &rarr;
                </button>
            </nav>
        @endif

    </div>

</section>
