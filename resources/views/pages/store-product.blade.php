@extends('app')

@section('head')
<style>
    /* The page content sits in the site's 1080px .container. The category
       sidebar alone is nudged left (see .pd-sidebar transform below) so its
       left edge lines up under the logo, which sits on the wider 1280px header
       grid. For that nudge to show, the .container must stop clipping horizontal
       overflow on this page — the sidebar never crosses the viewport edge, so
       this adds no horizontal scroll. */
    #main-content.container { overflow-x: visible; }
    .pd-page { max-width: 1280px; margin: 0 auto; padding: 0 24px 80px; }

    .pd-crumb { display: flex; justify-content: space-between; align-items: center; gap: 16px; font-size: 13px; color: rgba(255,255,255,0.45); margin: 32px 0 28px; letter-spacing: 0.02em; }
    .pd-crumb a { color: rgba(255,255,255,0.6); text-decoration: none; }
    .pd-crumb a:hover { color: #fff; }
    .pd-crumb .pd-trail span { color: #fff; }
    .pd-cart-link { color: #8b93ff !important; font-weight: 700; white-space: nowrap; }

    .pd-main { display: grid; grid-template-columns: 1fr 1fr; gap: 56px; align-items: start; }

    .pd-media { border: 1px solid rgba(255,255,255,0.12); border-radius: 6px; overflow: hidden; background: #11131a; }
    .pd-media img { width: 100%; height: 100%; object-fit: cover; display: block; aspect-ratio: 4 / 5; }
    .pd-media-placeholder { aspect-ratio: 4 / 5; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #111 0%, #1a1a2e 100%); }

    .pd-gallery { display: flex; flex-direction: column; gap: 12px; }
    .pd-thumbs { display: flex; gap: 10px; flex-wrap: wrap; }
    .pd-thumb { width: 64px; height: 80px; padding: 0; border: 1px solid rgba(255,255,255,0.15); border-radius: 4px; overflow: hidden; background: #11131a; cursor: pointer; transition: border-color 0.15s; }
    .pd-thumb:hover { border-color: rgba(255,255,255,0.4); }
    .pd-thumb.active { border-color: #5660fe; }
    .pd-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .pd-eyebrow { font-size: 13px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #8b93ff; margin-bottom: 14px; }
    .pd-eyebrow a { color: inherit; text-decoration: none; }
    .pd-eyebrow a:hover { text-decoration: underline; }
    .pd-title { font-size: 2.6rem; font-weight: 900; color: #fff; line-height: 1.1; margin-bottom: 16px; }
    .pd-price { font-size: 1.6rem; font-weight: 700; color: #fff; margin-bottom: 28px; }
    .pd-desc { font-size: 16px; line-height: 1.8; color: rgba(255,255,255,0.7); margin-bottom: 32px; }

    .pd-field { margin-bottom: 22px; }
    .pd-field label { display: block; font-size: 13px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255,255,255,0.55); margin-bottom: 8px; }
    .pd-field select { width: 100%; max-width: 220px; padding: 12px 14px; background: #16181f; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 4px; font-size: 15px; }

    .pd-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 12px; }
    .pd-btn { display: inline-block; background: #5660fe; color: #fff; padding: 16px 40px; font-size: 15px; font-weight: 700; letter-spacing: 0.04em; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; transition: background 0.2s; }
    .pd-btn:hover { background: #4049d6; }
    .pd-btn.secondary { background: transparent; border: 1px solid rgba(255,255,255,0.25); color: #fff; }
    .pd-btn.secondary:hover { border-color: #fff; background: rgba(255,255,255,0.05); }

    .pd-note { font-size: 13px; color: rgba(255,255,255,0.45); line-height: 1.6; margin-top: 22px; max-width: 460px; }
    .pd-delivery { display: flex; align-items: center; gap: 9px; font-size: 14px; color: rgba(255,255,255,0.7); margin-top: 20px; }
    .pd-delivery svg { width: 18px; height: 18px; fill: rgba(255,255,255,0.55); flex-shrink: 0; }
    .pd-delivery strong { color: #fff; font-weight: 700; }

    .pd-related { margin-top: 96px; }
    .pd-related h2 { font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 28px; }
    .pd-related-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
    .pd-rel { text-decoration: none; display: block; }
    .pd-rel-img { aspect-ratio: 1; background: #1a1a2e; border-radius: 4px; overflow: hidden; margin-bottom: 12px; }
    .pd-rel-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
    .pd-rel:hover .pd-rel-img img { transform: scale(1.05); }
    .pd-rel-name { font-size: 14px; font-weight: 600; color: #fff; text-align: center; }
    .pd-rel-price { font-size: 13px; color: rgba(255,255,255,0.5); text-align: center; margin-top: 2px; }

    /* Left category sidebar */
    .pd-layout { display: flex; gap: 48px; align-items: flex-start; }
    .pd-content { flex: 1; min-width: 0; }
    /* Shift the sidebar left so its left edge aligns with the logo. The logo
       sits at max((100vw - 1280px)/2, 0) + 24px (header grid); the sidebar
       naturally sits at max((100vw - 1080px)/2, 0) + 40px (container + paddings).
       The difference is the (negative) nudge. The content beside it stays put. */
    .pd-sidebar { flex: 0 0 200px; position: sticky; top: 24px; transform: translateX(calc(max((100vw - 1280px) / 2, 0px) - max((100vw - 1080px) / 2, 0px) - 16px)); }
    .pd-sidebar-title { font-size: 22px; font-weight: 900; color: #fff; margin: 32px 0 18px; }
    .pd-cats { display: flex; flex-direction: column; border-top: 1px solid rgba(255,255,255,0.12); }
    .pd-cat { display: block; padding: 13px 2px; font-size: 15px; font-weight: 600; color: rgba(255,255,255,0.72); text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.12); transition: color 0.15s; }
    .pd-cat:hover { color: #fff; }
    .pd-cat.active { color: #8b93ff; }
    .pd-social { display: flex; gap: 18px; margin-top: 22px; }
    .pd-social a { color: rgba(255,255,255,0.5); transition: color 0.15s; }
    .pd-social a:hover { color: #fff; }
    .pd-social svg { width: 19px; height: 19px; fill: currentColor; display: block; }

    @media (max-width: 860px) {
        .pd-main { grid-template-columns: 1fr; gap: 32px; }
        .pd-title { font-size: 2rem; }
        .pd-related-grid { grid-template-columns: repeat(2, 1fr); }
        .pd-layout { flex-direction: column; gap: 0; }
        .pd-sidebar { position: static; flex: none; width: 100%; transform: none; }
        .pd-sidebar-title { font-size: 18px; margin: 24px 0 12px; }
        .pd-cats { flex-direction: row; flex-wrap: wrap; gap: 6px 18px; border-top: none; }
        .pd-cat { border-bottom: none; padding: 6px 0; font-size: 14px; }
    }
    @media (max-width: 480px) {
        .pd-related-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('body')
@php
    $isGarment = $product->category === 'Apparel'
        && preg_match('/t-?shirt|tee|hoodie|sweatshirt|crewneck/i', $product->name);
    $cartCount = app(\App\Services\CartService::class)->count();
@endphp

<div class="pd-page">
    <div class="pd-layout">
    <aside class="pd-sidebar">
        <div class="pd-sidebar-title">Shop</div>
        <nav class="pd-cats">
            <a href="/store" class="pd-cat {{ request('category') ? '' : 'active' }}">All Products</a>
            @foreach($categories as $cat)
                <a href="/store?category={{ urlencode($cat) }}" class="pd-cat {{ $cat === $product->category ? 'active' : '' }}">{{ $cat }}</a>
            @endforeach
        </nav>
        @php
            $sf = \App\Models\SiteSetting::get('facebook_url');
            $sx = \App\Models\SiteSetting::get('twitter_url');
            $si = \App\Models\SiteSetting::get('instagram_url');
            $sy = \App\Models\SiteSetting::get('youtube_url');
        @endphp
        @if($sf || $sx || $si || $sy)
        <div class="pd-social">
            @if($sf)<a href="{{ $sf }}" target="_blank" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>@endif
            @if($sx)<a href="{{ $sx }}" target="_blank" rel="noopener" aria-label="X"><svg viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>@endif
            @if($si)<a href="{{ $si }}" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0 5.675a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg></a>@endif
            @if($sy)<a href="{{ $sy }}" target="_blank" rel="noopener" aria-label="YouTube"><svg viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></a>@endif
        </div>
        @endif
    </aside>
    <div class="pd-content">
    <nav class="pd-crumb">
        <span class="pd-trail">
            <a href="/store">Store</a>
            @if($product->category) / <a href="/store?category={{ urlencode($product->category) }}">{{ $product->category }}</a> @endif
            / <span>{{ $product->name }}</span>
        </span>
        <a href="/cart" class="pd-cart-link">Cart ({{ $cartCount }})</a>
    </nav>

    <div class="pd-main">
        @php
            $pdImages = collect([$product->image])->merge($product->gallery ?? [])->filter()->unique()->map(fn ($p) => Storage::url($p))->values();
        @endphp
        <div class="pd-gallery">
            <div class="pd-media">
                @if($pdImages->isNotEmpty())
                    <img id="pd-main-img" src="{{ $pdImages->first() }}" alt="{{ $product->name }}">
                @else
                    <div class="pd-media-placeholder">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="rgba(255,255,255,0.12)" viewBox="0 0 24 24"><path d="M18 6h-2c0-2.21-1.79-4-4-4S8 3.79 8 6H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2z"/></svg>
                    </div>
                @endif
            </div>
            @if($pdImages->count() > 1)
                <div class="pd-thumbs">
                    @foreach($pdImages as $i => $u)
                        <button type="button" class="pd-thumb {{ $i === 0 ? 'active' : '' }}" onclick="pdSetMainImage(this, '{{ $u }}')" aria-label="View image {{ $i + 1 }}">
                            <img src="{{ $u }}" alt="{{ $product->name }} — image {{ $i + 1 }}" loading="lazy">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="pd-info">
            @if($product->category)
                <div class="pd-eyebrow"><a href="/store?category={{ urlencode($product->category) }}">{{ $product->category }}</a></div>
            @endif
            <h1 class="pd-title">{{ $product->name }}</h1>
            <div class="pd-price">${{ number_format($product->price, 2) }}</div>

            @if($product->description)
                <div class="pd-desc">{!! nl2br(e($product->description)) !!}</div>
            @endif

            @if($product->purchase_url)
                <a class="pd-btn" href="{{ $product->purchase_url }}" target="_blank" rel="noopener">Buy Now</a>
            @else
                <form action="/cart/add" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    @if($isGarment)
                        <div class="pd-field">
                            <label for="pd-size">Size</label>
                            <select id="pd-size" name="size">
                                <option>S</option><option>M</option><option selected>L</option>
                                <option>XL</option><option>2XL</option><option>3XL</option>
                            </select>
                        </div>
                    @endif

                    <div class="pd-field">
                        <label for="pd-qty">Quantity</label>
                        <select id="pd-qty" name="quantity">
                            @for($i = 1; $i <= 10; $i++)<option>{{ $i }}</option>@endfor
                        </select>
                    </div>

                    <div class="pd-actions">
                        <button type="submit" class="pd-btn">Add To Cart</button>
                        <a class="pd-btn secondary" href="/cart">View Cart</a>
                    </div>
                </form>
            @endif

            <p class="pd-delivery">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
                Estimated delivery: <strong>{{ now()->addDays(5)->format('M j') }} &ndash; {{ now()->addDays(10)->format('M j') }}</strong>
            </p>

            <p class="pd-note">Secure checkout powered by Stripe. All proceeds directly support the National Political Prisoner Coalition's advocacy, legal aid, and family-support work.</p>
        </div>
    </div>

    @if($related->isNotEmpty())
        <div class="pd-related">
            <h2>You may also like</h2>
            <div class="pd-related-grid">
                @foreach($related as $rel)
                    <a href="/store/{{ $rel->slug }}" class="pd-rel">
                        <div class="pd-rel-img">
                            @if($rel->image)
                                <img src="{{ Storage::url($rel->image) }}" alt="{{ $rel->name }}">
                            @endif
                        </div>
                        <div class="pd-rel-name">{{ $rel->name }}</div>
                        <div class="pd-rel-price">${{ number_format($rel->price, 2) }}</div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
    </div>{{-- .pd-content --}}
    </div>{{-- .pd-layout --}}
</div>

<script>
    function pdSetMainImage(btn, url) {
        var main = document.getElementById('pd-main-img');
        if (main) { main.src = url; }
        document.querySelectorAll('.pd-thumb').forEach(function (t) { t.classList.remove('active'); });
        btn.classList.add('active');
    }
</script>
@endsection
