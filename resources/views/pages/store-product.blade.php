@extends('app')

@section('head')
<style>
    .pd-page { max-width: 1200px; margin: 0 auto; padding: 0 24px 80px; }

    .pd-crumb { display: flex; justify-content: space-between; align-items: center; gap: 16px; font-size: 13px; color: rgba(255,255,255,0.45); margin: 32px 0 28px; letter-spacing: 0.02em; }
    .pd-crumb a { color: rgba(255,255,255,0.6); text-decoration: none; }
    .pd-crumb a:hover { color: #fff; }
    .pd-crumb .pd-trail span { color: #fff; }
    .pd-cart-link { color: #8b93ff !important; font-weight: 700; white-space: nowrap; }

    .pd-main { display: grid; grid-template-columns: 1fr 1fr; gap: 56px; align-items: start; }

    .pd-media { border: 1px solid rgba(255,255,255,0.12); border-radius: 6px; overflow: hidden; background: #11131a; }
    .pd-media img { width: 100%; height: 100%; object-fit: cover; display: block; aspect-ratio: 4 / 5; }
    .pd-media-placeholder { aspect-ratio: 4 / 5; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #111 0%, #1a1a2e 100%); }

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

    .pd-related { margin-top: 96px; }
    .pd-related h2 { font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 28px; }
    .pd-related-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
    .pd-rel { text-decoration: none; display: block; }
    .pd-rel-img { aspect-ratio: 1; background: #1a1a2e; border-radius: 4px; overflow: hidden; margin-bottom: 12px; }
    .pd-rel-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
    .pd-rel:hover .pd-rel-img img { transform: scale(1.05); }
    .pd-rel-name { font-size: 14px; font-weight: 600; color: #fff; text-align: center; }
    .pd-rel-price { font-size: 13px; color: rgba(255,255,255,0.5); text-align: center; margin-top: 2px; }

    @media (max-width: 860px) {
        .pd-main { grid-template-columns: 1fr; gap: 32px; }
        .pd-title { font-size: 2rem; }
        .pd-related-grid { grid-template-columns: repeat(2, 1fr); }
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
    <nav class="pd-crumb">
        <span class="pd-trail">
            <a href="/store">Store</a>
            @if($product->category) / <a href="/store?category={{ urlencode($product->category) }}">{{ $product->category }}</a> @endif
            / <span>{{ $product->name }}</span>
        </span>
        <a href="/cart" class="pd-cart-link">Cart ({{ $cartCount }})</a>
    </nav>

    <div class="pd-main">
        <div class="pd-media">
            @if($product->image)
                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}">
            @else
                <div class="pd-media-placeholder">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="rgba(255,255,255,0.12)" viewBox="0 0 24 24"><path d="M18 6h-2c0-2.21-1.79-4-4-4S8 3.79 8 6H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2z"/></svg>
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
</div>
@endsection
