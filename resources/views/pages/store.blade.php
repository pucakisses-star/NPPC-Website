@extends('app')

@section('title', 'Store | NPPC')

@section('head')
<style>
    .store-page { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

    /* Hero */
    .store-hero { display: flex; border: 1px solid rgba(var(--store-fg-rgb),0.15); margin: 48px 0; overflow: hidden; border-radius: 4px; }
    .store-hero-image { flex: 0 0 50%; }
    .store-hero-image img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .store-hero-content { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 40px; text-align: center; }
    .store-hero-title { font-size: 3rem; font-weight: 900; color: var(--store-fg); line-height: 1.1; margin-bottom: 24px; }
    .store-hero-btn { background: var(--store-fg); color: var(--store-surface); padding: 14px 32px; font-size: 14px; font-weight: 700; text-decoration: none; display: inline-block; transition: background 0.2s; }
    .store-hero-btn:hover { background: rgba(var(--store-fg-rgb),0.85); }
    .store-hero-placeholder { width: 100%; height: 100%; min-height: 400px; background: linear-gradient(135deg, #0a0a1a 0%, #1a1040 50%, var(--store-accent) 100%); display: flex; align-items: center; justify-content: center; }

    /* Categories */
    .store-categories { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 64px; }
    .store-cat-card { text-align: center; text-decoration: none; }
    .store-cat-image { aspect-ratio: 1; background: var(--store-surface-2); border-radius: 8px; overflow: hidden; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; }
    .store-cat-image img { width: 100%; height: 100%; object-fit: cover; }
    .store-cat-label { font-size: 16px; font-weight: 600; color: var(--store-fg); }
    .store-cat-label span { color: rgba(var(--store-fg-rgb),0.4); }

    /* Feature Section */
    .store-feature { display: flex; border: 1px solid rgba(var(--store-fg-rgb),0.15); margin-bottom: 64px; overflow: hidden; border-radius: 4px; }
    .store-feature-text { flex: 1; padding: 48px 40px; display: flex; flex-direction: column; justify-content: center; }
    .store-feature-title { font-size: 2.5rem; font-weight: 900; color: var(--store-fg); line-height: 1.1; margin-bottom: 20px; }
    .store-feature-desc { font-size: 16px; color: rgba(var(--store-fg-rgb),0.6); line-height: 1.7; }
    .store-feature-image { flex: 0 0 50%; }
    .store-feature-image img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .store-feature-placeholder { width: 100%; height: 100%; min-height: 300px; background: linear-gradient(135deg, #1a1040 0%, var(--store-accent) 100%); }

    /* Products */
    .store-products-title { font-size: 24px; font-weight: 800; color: var(--store-fg); margin-bottom: 24px; }
    .store-products-head { display: grid; grid-template-columns: 1fr auto 1fr; grid-template-areas: "title search cart"; align-items: center; gap: 24px; margin-bottom: 24px; }
    .store-products-head .store-products-title { grid-area: title; justify-self: start; margin-bottom: 0; }
    .store-products-head .store-cart-link { grid-area: cart; justify-self: end; }
    .store-cart-link { color: var(--store-accent-2); text-decoration: none; font-weight: 700; font-size: 14px; border: 1px solid rgba(139,147,255,0.4); padding: 8px 16px; border-radius: 4px; white-space: nowrap; }
    .store-cart-link:hover { background: rgba(139,147,255,0.12); }
    .store-products-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; padding-bottom: 32px; }
    .store-pager { display: flex; justify-content: center; align-items: center; gap: 16px; padding: 8px 0 80px; }
    .store-page-btn { padding: 10px 22px; font-size: 14px; font-weight: 700; border: 1px solid rgba(var(--store-fg-rgb),0.25); color: var(--store-fg); background: transparent; cursor: pointer; border-radius: 4px; transition: all 0.15s; }
    .store-page-btn:hover:not(:disabled) { background: var(--store-accent); border-color: var(--store-accent); color: var(--store-on-accent); }
    .store-page-btn:disabled { opacity: 0.35; cursor: not-allowed; }
    .store-page-info { font-size: 13px; color: rgba(var(--store-fg-rgb),0.55); font-weight: 600; }
    .store-product { text-decoration: none; display: block; }
    .store-product-image { aspect-ratio: 1; background: var(--store-surface-2); border-radius: 4px; overflow: hidden; margin-bottom: 12px; }
    .store-product-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
    .store-product:hover .store-product-image img { transform: scale(1.05); }
    .store-product-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #111 0%, var(--store-surface-2) 100%); }
    /* Books & zines: show the full cover (portrait, never cropped) instead of a square crop. */
    .store-product--cover .store-product-image { aspect-ratio: 2 / 3; background: transparent; }
    .store-product--cover .store-product-image img { object-fit: contain; }
    .store-product-name { font-size: 15px; font-weight: 600; color: var(--store-fg); text-align: center; margin-bottom: 4px; }
    .store-product-price { font-size: 14px; color: rgba(var(--store-fg-rgb),0.5); text-align: center; }

    /* Filter */
    .store-filter { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 32px; }
    .store-filter-btn { padding: 8px 20px; font-size: 13px; font-weight: 600; border: 1px solid rgba(var(--store-fg-rgb),0.2); color: rgba(var(--store-fg-rgb),0.7); background: transparent; cursor: pointer; border-radius: 4px; text-decoration: none; transition: all 0.15s; }
    .store-filter-btn:hover { border-color: var(--store-accent); color: var(--store-fg); }
    .store-filter-btn.active { background: var(--store-accent); border-color: var(--store-accent); color: var(--store-on-accent); }
    .store-search { grid-area: search; justify-self: center; width: 360px; max-width: 100%; }
    .store-search input { width: 100%; padding: 7px 4px 7px 26px; font-size: 14px; background: transparent url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E") no-repeat left center; background-size: 15px 15px; color: var(--store-fg); border: none; border-bottom: 1px solid rgba(var(--store-fg-rgb),0.25); border-radius: 0; outline: none; transition: border-color 0.15s; }
    .store-search input:focus { border-bottom-color: var(--store-accent); }
    .store-search input::placeholder { color: rgba(var(--store-fg-rgb),0.4); }

    @media (max-width: 768px) {
        .store-hero { flex-direction: column; }
        .store-hero-image { flex: auto; }
        .store-hero-title { font-size: 2rem; }
        .store-categories { grid-template-columns: repeat(2, 1fr); }
        .store-feature { flex-direction: column; }
        .store-products-grid { grid-template-columns: repeat(2, 1fr); }
        .store-products-head { grid-template-columns: 1fr auto; grid-template-areas: "title cart" "search search"; gap: 14px 24px; }
        .store-products-head .store-search { width: 100%; justify-self: stretch; }
    }
    @media (max-width: 420px) {
        .store-hero-title { font-size: 1.5rem; }
        .store-feature-title { font-size: 1.5rem; }
        .store-categories { grid-template-columns: 1fr; gap: 12px; }
        .store-products-grid { grid-template-columns: 1fr; gap: 16px; }
    }
</style>
@endsection

@section('body')
<div class="store-page">
    {{-- Hero --}}
    <div class="store-hero">
        <div class="store-hero-image">
            @if(file_exists(public_path('images/site/store-hero.jpg')))
                <img src="/images/site/store-hero.jpg?v={{ @filemtime(public_path('images/site/store-hero.jpg')) }}" alt="Shop to support political prisoners">
            @elseif($featured && $featured->image)
                <img src="{{ Storage::url($featured->image) }}" alt="{{ $featured->name }}">
            @else
                <div class="store-hero-placeholder">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="rgba(140,140,150,0.4)" viewBox="0 0 24 24"><path d="M18 6h-2c0-2.21-1.79-4-4-4S8 3.79 8 6H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-6-2c1.1 0 2 .9 2 2h-4c0-1.1.9-2 2-2zm6 16H6V8h2v2c0 .55.45 1 1 1s1-.45 1-1V8h4v2c0 .55.45 1 1 1s1-.45 1-1V8h2v12z"/></svg>
                </div>
            @endif
        </div>
        <div class="store-hero-content">
            <h1 class="store-hero-title">Shop to Support Political Prisoners</h1>
            <a href="#products" class="store-hero-btn">Shop all</a>
        </div>
    </div>

    @php
        // Fixed display order. CARDS show only departments that have products;
        // PILLS are the full curated set in this exact order (some, like Zines,
        // may have no products yet — their pill just filters to an empty grid).
        $cardCategories = collect(['Apparel', 'Accessories', 'Bundles', 'Books'])
            ->filter(fn ($c) => $categories->contains($c))->values();
        $filterCategories = collect(['Apparel', 'Accessories', 'Pins', 'Stickers', 'Magnets', 'Bundles', 'Books', 'Zines']);
    @endphp

    {{-- Categories (cards: departments only) --}}
    @if($cardCategories->isNotEmpty())
        <div class="store-categories">
            @foreach($cardCategories as $cat)
                <a href="#products" data-store-category="{{ $cat }}" class="store-cat-card">
                    <div class="store-cat-image">
                        @php
                            // Explicit per-category cover image (public/images/site/category-{slug}.jpg)
                            // takes precedence; otherwise fall back to the first product's image.
                            $coverRel = 'images/site/category-'.\Illuminate\Support\Str::slug($cat).'.jpg';
                            $hasCover = file_exists(public_path($coverRel));
                            $catProduct = $hasCover ? null : \App\Models\Product::published()->whereNotNull('image')->where(function ($q) use ($cat) {
                                $q->where('category', $cat)->orWhereJsonContains('categories', $cat);
                            })->first();
                        @endphp
                        @if($hasCover)
                            <img src="/{{ $coverRel }}?v={{ @filemtime(public_path($coverRel)) }}" alt="{{ $cat }}">
                        @elseif($catProduct && $catProduct->image)
                            <img src="{{ Storage::url($catProduct->image) }}" alt="{{ $cat }}">
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="rgba(140,140,150,0.4)" viewBox="0 0 24 24"><path d="M18 6h-2c0-2.21-1.79-4-4-4S8 3.79 8 6H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2z"/></svg>
                        @endif
                    </div>
                    <div class="store-cat-label">{{ $cat }} <span>&rarr;</span></div>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Feature Section --}}
    <div class="store-feature">
        <div class="store-feature-text">
            <h2 class="store-feature-title">Goods That Do Good</h2>
            <p class="store-feature-desc">Show your support for political prisoners with a product from our store. All purchases directly support our work to advocate for justice, provide legal aid, and assist those in need.</p>
        </div>
        <div class="store-feature-image">
            @php
                // Randomly show one of the available feature photos on each load.
                $featureImgs = collect(['images/site/store-feature.jpg', 'images/site/store-feature-2.jpg'])
                    ->filter(fn ($p) => file_exists(public_path($p)))->values();
                $featureImg = $featureImgs->isNotEmpty() ? $featureImgs->random() : null;
            @endphp
            @if($featureImg)
                <img src="/{{ $featureImg }}?v={{ @filemtime(public_path($featureImg)) }}" alt="Goods that do good">
            @else
                <div class="store-feature-placeholder"></div>
            @endif
        </div>
    </div>

    {{-- Products --}}
    <div id="products">
        <div class="store-products-head">
            <h2 class="store-products-title" data-store-title>{{ $category ? $category : 'All Products' }}</h2>
            <div class="store-search">
                <input type="search" data-store-search placeholder="Search products…" aria-label="Search products" autocomplete="off">
            </div>
            <a href="/cart" class="store-cart-link">Cart ({{ app(\App\Services\CartService::class)->count() }})</a>
        </div>

        @if($filterCategories->isNotEmpty())
            <div class="store-filter">
                <button type="button" data-store-filter="" class="store-filter-btn {{ !$category ? 'active' : '' }}">All</button>
                @foreach($filterCategories as $cat)
                    <button type="button" data-store-filter="{{ $cat }}" class="store-filter-btn {{ $category === $cat ? 'active' : '' }}">{{ $cat }}</button>
                @endforeach
            </div>
        @endif

        @if($products->isEmpty())
            <div style="text-align: center; padding: 60px 0; color: rgba(var(--store-fg-rgb),0.4);">
                No products available yet. Check back soon!
            </div>
        @else
            <div class="store-products-grid" data-store-grid>
                @foreach($products as $product)
                    <a href="/store/{{ $product->slug }}" data-product-categories="{{ implode(',', $product->all_categories) }}" data-product-name="{{ \Illuminate\Support\Str::lower($product->name) }}" class="store-product {{ in_array($product->category, ['Books', 'Zines'], true) ? 'store-product--cover' : '' }}">
                        <div class="store-product-image">
                            @if($product->image)
                                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}">
                            @else
                                <div class="store-product-placeholder">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="rgba(140,140,150,0.4)" viewBox="0 0 24 24"><path d="M18 6h-2c0-2.21-1.79-4-4-4S8 3.79 8 6H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2z"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="store-product-name">{{ $product->name }}</div>
                        <div class="store-product-price">${{ number_format($product->price, 2) }}</div>
                    </a>
                @endforeach
                <div data-store-empty style="display:none; grid-column: 1 / -1; text-align: center; padding: 60px 0; color: rgba(var(--store-fg-rgb),0.4);">
                    No products in this category.
                </div>
            </div>
            <div class="store-pager" data-store-pager style="display:none;"></div>
        @endif
    </div>
</div>

<script>
(function () {
    var PAGE_SIZE = 20; // 5 rows x 4 columns on desktop
    var filterBtns = document.querySelectorAll('[data-store-filter]');
    var catCards = document.querySelectorAll('[data-store-category]');
    var products = Array.prototype.slice.call(document.querySelectorAll('[data-product-categories]'));
    var title = document.querySelector('[data-store-title]');
    var empty = document.querySelector('[data-store-empty]');
    var pager = document.querySelector('[data-store-pager]');
    var searchInput = document.querySelector('[data-store-search]');

    var currentCategory = @json($category) || '';
    var currentSearch = '';
    var currentPage = 1;

    function render(opts) {
        opts = opts || {};
        var matched = [];
        products.forEach(function (el) {
            el.style.display = 'none';
            var cats = (el.getAttribute('data-product-categories') || '').split(',');
            var name = el.getAttribute('data-product-name') || '';
            var catOk = !currentCategory || cats.indexOf(currentCategory) !== -1;
            var searchOk = !currentSearch || name.indexOf(currentSearch) !== -1;
            if (catOk && searchOk) {
                matched.push(el);
            }
        });

        var totalPages = Math.max(1, Math.ceil(matched.length / PAGE_SIZE));
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;
        var start = (currentPage - 1) * PAGE_SIZE;
        matched.slice(start, start + PAGE_SIZE).forEach(function (el) { el.style.display = ''; });

        filterBtns.forEach(function (btn) {
            btn.classList.toggle('active', btn.getAttribute('data-store-filter') === currentCategory);
        });
        if (title) title.textContent = currentCategory || 'All Products';
        if (empty) empty.style.display = matched.length === 0 ? '' : 'none';

        renderPager(totalPages);

        var url = new URL(window.location.href);
        if (currentCategory) url.searchParams.set('category', currentCategory);
        else url.searchParams.delete('category');
        history.replaceState(null, '', url.toString());

        if (opts.scroll) {
            var anchor = document.getElementById('products');
            if (anchor) anchor.scrollIntoView({ behavior: 'smooth' });
        }
    }

    function renderPager(totalPages) {
        if (!pager) return;
        pager.innerHTML = '';
        if (totalPages <= 1) { pager.style.display = 'none'; return; }
        pager.style.display = '';

        var prev = document.createElement('button');
        prev.type = 'button';
        prev.className = 'store-page-btn';
        prev.textContent = '← Prev';
        prev.disabled = currentPage <= 1;
        prev.addEventListener('click', function () { if (currentPage > 1) { currentPage--; render({ scroll: true }); } });

        var info = document.createElement('span');
        info.className = 'store-page-info';
        info.textContent = 'Page ' + currentPage + ' of ' + totalPages;

        var next = document.createElement('button');
        next.type = 'button';
        next.className = 'store-page-btn';
        next.textContent = 'Next →';
        next.disabled = currentPage >= totalPages;
        next.addEventListener('click', function () { if (currentPage < totalPages) { currentPage++; render({ scroll: true }); } });

        pager.appendChild(prev);
        pager.appendChild(info);
        pager.appendChild(next);
    }

    function setCategory(category, scroll) {
        currentCategory = category || '';
        currentPage = 1;
        render({ scroll: !!scroll });
    }

    filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            setCategory(btn.getAttribute('data-store-filter'), false);
        });
    });

    catCards.forEach(function (card) {
        card.addEventListener('click', function (e) {
            e.preventDefault();
            setCategory(card.getAttribute('data-store-category'), true);
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            currentSearch = this.value.trim().toLowerCase();
            currentPage = 1;
            render();
        });
    }

    render();
})();
</script>
@endsection
