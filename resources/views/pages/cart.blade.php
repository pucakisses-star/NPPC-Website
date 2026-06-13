@extends('app')

@section('head')
<style>
    .cart-page { max-width: 1000px; margin: 0 auto; padding: 0 24px 80px; }
    .cart-head { font-size: 2.4rem; font-weight: 900; color: #fff; margin: 44px 0 24px; }
    .cart-flash { background: rgba(86,96,254,0.15); border: 1px solid #5660fe; color: #fff; padding: 14px 18px; border-radius: 6px; margin-bottom: 24px; font-size: 14px; }
    .cart-empty { color: rgba(255,255,255,0.6); font-size: 17px; line-height: 1.7; padding: 40px 0; }
    .cart-empty a { color: #8b93ff; text-decoration: none; }

    .cart-row { display: flex; align-items: center; gap: 20px; padding: 20px 0; border-bottom: 1px solid rgba(255,255,255,0.1); }
    .cart-thumb { width: 88px; height: 88px; flex: 0 0 88px; border-radius: 6px; overflow: hidden; background: #1a1a2e; display: block; }
    .cart-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .cart-info { flex: 1; min-width: 0; }
    .cart-name { font-size: 16px; font-weight: 700; color: #fff; text-decoration: none; }
    .cart-name:hover { color: #8b93ff; }
    .cart-meta { font-size: 13px; color: rgba(255,255,255,0.5); margin-top: 4px; }
    .cart-qty { display: flex; align-items: center; gap: 8px; margin: 0; }
    .cart-qty input { width: 62px; padding: 8px; background: #16181f; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 4px; font-size: 14px; text-align: center; }
    .cart-qty button { background: transparent; border: 1px solid rgba(255,255,255,0.2); color: rgba(255,255,255,0.7); border-radius: 4px; padding: 8px 10px; font-size: 12px; cursor: pointer; }
    .cart-line-total { font-weight: 700; color: #fff; min-width: 84px; text-align: right; }
    .cart-remove { background: none; border: none; color: rgba(255,255,255,0.4); cursor: pointer; font-size: 13px; text-decoration: underline; padding: 0; }
    .cart-remove:hover { color: #ff6b6b; }

    .cart-summary { display: flex; justify-content: flex-end; margin-top: 32px; }
    .cart-summary-box { width: 320px; }
    .cart-subtotal { display: flex; justify-content: space-between; font-size: 18px; color: #fff; font-weight: 700; margin-bottom: 6px; }
    .cart-note { font-size: 13px; color: rgba(255,255,255,0.45); margin-bottom: 18px; line-height: 1.5; }
    .cart-checkout { display: block; width: 100%; text-align: center; background: #5660fe; color: #fff; border: none; padding: 16px; font-size: 15px; font-weight: 700; border-radius: 4px; cursor: pointer; }
    .cart-checkout:hover { background: #4049d6; }
    .cart-continue { display: inline-block; margin-top: 16px; color: rgba(255,255,255,0.6); text-decoration: none; font-size: 14px; }
    .cart-continue:hover { color: #fff; }

    @media (max-width: 620px) {
        .cart-row { flex-wrap: wrap; }
        .cart-summary-box { width: 100%; }
    }
</style>
@endsection

@section('body')
<div class="cart-page">
    <h1 class="cart-head">Your Cart</h1>

    @if(session('cart_status'))
        <div class="cart-flash">{{ session('cart_status') }}</div>
    @endif

    @if($items->isEmpty())
        <div class="cart-empty">Your cart is empty. <a href="/store">Browse the store &rarr;</a></div>
    @else
        @foreach($items as $item)
            <div class="cart-row">
                <a href="/store/{{ $item->slug }}" class="cart-thumb">
                    @if($item->image_url)<img src="{{ $item->image_url }}" alt="{{ $item->name }}">@endif
                </a>
                <div class="cart-info">
                    <a href="/store/{{ $item->slug }}" class="cart-name">{{ $item->name }}</a>
                    <div class="cart-meta">@if($item->size)Size: {{ $item->size }} &middot; @endif${{ number_format($item->price, 2) }} each</div>
                </div>
                <form action="/cart/update" method="POST" class="cart-qty">
                    @csrf
                    <input type="hidden" name="key" value="{{ $item->key }}">
                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="0" max="99" onchange="this.form.submit()" aria-label="Quantity">
                    <button type="submit">Update</button>
                </form>
                <div class="cart-line-total">${{ number_format($item->line_total, 2) }}</div>
                <form action="/cart/remove" method="POST">
                    @csrf
                    <input type="hidden" name="key" value="{{ $item->key }}">
                    <button type="submit" class="cart-remove">Remove</button>
                </form>
            </div>
        @endforeach

        <div class="cart-summary">
            <div class="cart-summary-box">
                <div class="cart-subtotal"><span>Subtotal</span><span>${{ number_format($subtotal, 2) }}</span></div>
                <div class="cart-note">Shipping is confirmed by email after checkout. Secure payment via Stripe.</div>
                <form action="/cart/checkout" method="POST">
                    @csrf
                    <button type="submit" class="cart-checkout">Proceed to Checkout</button>
                </form>
                <a href="/store" class="cart-continue">&larr; Continue shopping</a>
            </div>
        </div>
    @endif
</div>
@endsection
