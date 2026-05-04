@extends('app')

@section('head')
<style>
    .store-page { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

    /* Hero */
    .store-hero { display: flex; border: 1px solid rgba(255,255,255,0.15); margin: 48px 0; overflow: hidden; border-radius: 4px; }
    .store-hero-image { flex: 0 0 50%; }
    .store-hero-image img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .store-hero-content { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 40px; text-align: center; }
    .store-hero-title { font-size: 3rem; font-weight: 900; color: #fff; line-height: 1.1; margin-bottom: 24px; }
    .store-hero-btn { background: #fff; color: #000; padding: 14px 32px; font-size: 14px; font-weight: 700; text-decoration: none; display: inline-block; transition: background 0.2s; }
    .store-hero-btn:hover { background: #e0e0e0; }
    .store-hero-placeholder { width: 100%; height: 100%; min-height: 400px; background: linear-gradient(135deg, #0a0a1a 0%, #1a1040 50%, #5660fe 100%); display: flex; align-items: center; justify-content: center; }

    /* Categories */
    .store-categories { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 64px; }
    .store-cat-card { text-align: center; text-decoration: none; }
    .store-cat-image { aspect-ratio: 1; background: #1a1a2e; border-radius: 8px; overflow: hidden; margin-bottom: 12px; display: flex; align-items: center; justify-content: center; }
    .store-cat-image img { width: 100%; height: 100%; object-fit: cover; }
    .store-cat-label { font-size: 16px; font-weight: 600; color: #fff; }
    .store-cat-label span { color: rgba(255,255,255,0.4); }

    /* Feature Section */
    .store-feature { display: flex; border: 1px solid rgba(255,255,255,0.15); margin-bottom: 64px; overflow: hidden; border-radius: 4px; }
    .store-feature-text { flex: 1; padding: 48px 40px; display: flex; flex-direction: column; justify-content: center; }
    .store-feature-title { font-size: 2.5rem; font-weight: 900; color: #fff; line-height: 1.1; margin-bottom: 20px; }
    .store-feature-desc { font-size: 16px; color: rgba(255,255,255,0.6); line-height: 1.7; }
    .store-feature-image { flex: 0 0 50%; }
    .store-feature-image img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .store-feature-placeholder { width: 100%; height: 100%; min-height: 300px; background: linear-gradient(135deg, #1a1040 0%, #5660fe 100%); }

    /* Products */
    .store-products-title { font-size: 24px; font-weight: 800; color: #fff; margin-bottom: 24px; }
    .store-products-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; padding-bottom: 80px; }
    .store-product { text-decoration: none; display: block; }
    .store-product-image { aspect-ratio: 1; background: #1a1a2e; border-radius: 4px; overflow: hidden; margin-bottom: 12px; }
    .store-product-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
    .store-product:hover .store-product-image img { transform: scale(1.05); }
    .store-product-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #111 0%, #1a1a2e 100%); }
    .store-product-name { font-size: 15px; font-weight: 600; color: #fff; text-align: center; margin-bottom: 4px; }
    .store-product-price { font-size: 14px; color: rgba(255,255,255,0.5); text-align: center; }

    /* Filter */
    .store-filter { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 32px; }
    .store-filter-btn { padding: 8px 20px; font-size: 13px; font-weight: 600; border: 1px solid rgba(255,255,255,0.2); color: rgba(255,255,255,0.7); background: transparent; cursor: pointer; border-radius: 4px; text-decoration: none; transition: all 0.15s; }
    .store-filter-btn:hover { border-color: #5660fe; color: #fff; }
    .store-filter-btn.active { background: #5660fe; border-color: #5660fe; color: #fff; }

    @media (max-width: 768px) {
        .store-hero { flex-direction: column; }
        .store-hero-image { flex: auto; }
        .store-hero-title { font-size: 2rem; }
        .store-categories { grid-template-columns: repeat(2, 1fr); }
        .store-feature { flex-direction: column; }
        .store-products-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endsection

@section('body')
<div class="store-page">
    {{-- Hero --}}
    <div class="store-hero">
        <div class="store-hero-image">
            @if($featured && $featured->image)
                <img src="{{ Storage::url($featured->image) }}" alt="{{ $featured->name }}">
            @elseif(file_exists(public_path('images/site/store-hero.jpg')))
                <img src="/images/site/store-hero.jpg" alt="Shop to support political prisoners">
            @else
                <div class="store-hero-placeholder">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="rgba(255,255,255,0.1)" viewBox="0 0 24 24"><path d="M18 6h-2c0-2.21-1.79-4-4-4S8 3.79 8 6H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-6-2c1.1 0 2 .9 2 2h-4c0-1.1.9-2 2-2zm6 16H6V8h2v2c0 .55.45 1 1 1s1-.45 1-1V8h4v2c0 .55.45 1 1 1s1-.45 1-1V8h2v12z"/></svg>
                </div>
            @endif
        </div>
        <div class="store-hero-content">
            <h1 class="store-hero-title">Shop to Support Political Prisoners</h1>
            <a href="#products" class="store-hero-btn">Shop all</a>
        </div>
    </div>

    {{-- Categories --}}
    @if($categories->isNotEmpty())
        <div class="store-categories">
            @foreach($categories as $cat)
                <a href="#products" data-store-category="{{ $cat }}" class="store-cat-card">
                    <div class="store-cat-image">
                        @php $catProduct = \App\Models\Product::published()->where('category', $cat)->whereNotNull('image')->first(); @endphp
                        @if($catProduct && $catProduct->image)
                            <img src="{{ Storage::url($catProduct->image) }}" alt="{{ $cat }}">
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="rgba(255,255,255,0.1)" viewBox="0 0 24 24"><path d="M18 6h-2c0-2.21-1.79-4-4-4S8 3.79 8 6H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2z"/></svg>
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
            <p class="store-feature-desc">Show your support for political prisoners with a product from our store. All purchases directly support our work to advocate for justice, provide legal aid, and assist families in need.</p>
        </div>
        <div class="store-feature-image">
            @if(file_exists(public_path('images/site/store-feature.jpg')))
                <img src="/images/site/store-feature.jpg" alt="Goods that do good">
            @else
                <div class="store-feature-placeholder"></div>
            @endif
        </div>
    </div>

    {{-- Products --}}
    <div id="products">
        <h2 class="store-products-title" data-store-title>{{ $category ? $category : 'All Products' }}</h2>

        @if($categories->isNotEmpty())
            <div class="store-filter">
                <button type="button" data-store-filter="" class="store-filter-btn {{ !$category ? 'active' : '' }}">All</button>
                @foreach($categories as $cat)
                    <button type="button" data-store-filter="{{ $cat }}" class="store-filter-btn {{ $category === $cat ? 'active' : '' }}">{{ $cat }}</button>
                @endforeach
            </div>
        @endif

        @if($products->isEmpty())
            <div style="text-align: center; padding: 60px 0; color: rgba(255,255,255,0.4);">
                No products available yet. Check back soon!
            </div>
        @else
            <div class="store-products-grid" data-store-grid>
                @foreach($products as $product)
                    <a href="{{ $product->purchase_url ?: '#' }}" data-product-category="{{ $product->category }}" class="store-product" {{ $product->purchase_url ? 'target=_blank' : '' }}>
                        <div class="store-product-image">
                            @if($product->image)
                                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}">
                            @else
                                <div class="store-product-placeholder">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="rgba(255,255,255,0.1)" viewBox="0 0 24 24"><path d="M18 6h-2c0-2.21-1.79-4-4-4S8 3.79 8 6H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2z"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="store-product-name">{{ $product->name }}</div>
                        <div class="store-product-price">${{ number_format($product->price, 2) }}</div>
                    </a>
                @endforeach
                <div data-store-empty style="display:none; grid-column: 1 / -1; text-align: center; padding: 60px 0; color: rgba(255,255,255,0.4);">
                    No products in this category.
                </div>
            </div>
        @endif
    </div>
</div>

<script>
(function () {
    var filterBtns = document.querySelectorAll('[data-store-filter]');
    var catCards = document.querySelectorAll('[data-store-category]');
    var products = document.querySelectorAll('[data-product-category]');
    var title = document.querySelector('[data-store-title]');
    var empty = document.querySelector('[data-store-empty]');

    function applyFilter(category, opts) {
        opts = opts || {};
        var visible = 0;
        products.forEach(function (el) {
            var match = !category || el.getAttribute('data-product-category') === category;
            el.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        filterBtns.forEach(function (btn) {
            btn.classList.toggle('active', btn.getAttribute('data-store-filter') === (category || ''));
        });
        if (title) title.textContent = category || 'All Products';
        if (empty) empty.style.display = visible === 0 ? '' : 'none';

        var url = new URL(window.location.href);
        if (category) url.searchParams.set('category', category);
        else url.searchParams.delete('category');
        history.replaceState(null, '', url.toString() + (opts.scroll ? '#products' : window.location.hash));
    }

    filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            applyFilter(btn.getAttribute('data-store-filter'));
        });
    });

    catCards.forEach(function (card) {
        card.addEventListener('click', function (e) {
            e.preventDefault();
            applyFilter(card.getAttribute('data-store-category'), { scroll: true });
            var anchor = document.getElementById('products');
            if (anchor) anchor.scrollIntoView({ behavior: 'smooth' });
        });
    });
})();
</script>
@endsection
