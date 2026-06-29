@extends('app')

@section('head')
<style>
    .oc-page { max-width: 720px; margin: 0 auto; padding: 60px 24px 100px; text-align: center; }
    .oc-badge { width: 72px; height: 72px; border-radius: 50%; background: rgba(34,197,94,0.15); border: 2px solid #22c55e; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; }
    .oc-title { font-size: 2.2rem; font-weight: 900; color: var(--fg); margin-bottom: 12px; }
    .oc-title.warn { color: #ffb347; }
    .oc-sub { font-size: 16px; color: rgba(var(--fg-rgb),0.65); line-height: 1.7; margin-bottom: 32px; }
    .oc-ref { display: inline-block; background: var(--surface); border: 1px solid rgba(var(--fg-rgb),0.15); border-radius: 6px; padding: 10px 18px; color: var(--fg); font-weight: 700; letter-spacing: 0.05em; margin-bottom: 32px; }
    .oc-items { text-align: left; max-width: 480px; margin: 0 auto 32px; }
    .oc-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid rgba(var(--fg-rgb),0.1); color: rgba(var(--fg-rgb),0.8); font-size: 15px; }
    .oc-total { display: flex; justify-content: space-between; padding: 14px 0; color: var(--fg); font-weight: 800; font-size: 17px; }
    .oc-btn { display: inline-block; background: var(--accent); color: var(--on-accent); padding: 14px 32px; border-radius: 4px; text-decoration: none; font-weight: 700; }
    .oc-btn:hover { background: var(--accent-hover); }
</style>
@endsection

@section('body')
<div class="oc-page">
    @if($order && $order->isPaid())
        <div class="oc-badge">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <h1 class="oc-title">Thank you for your order!</h1>
        <p class="oc-sub">Your payment was received. A confirmation has been sent to your email, and we'll be in touch about shipping. Every purchase directly supports the National Political Prisoner Coalition's advocacy and family-support work.</p>
        <div class="oc-ref">Order {{ $order->reference }}</div>
        <div class="oc-items">
            @foreach($order->items as $it)
                <div class="oc-item">
                    <span>{{ $it->name }}@if($it->size) ({{ $it->size }})@endif &times; {{ $it->quantity }}</span>
                    <span>${{ number_format($it->line_total, 2) }}</span>
                </div>
            @endforeach
            <div class="oc-total"><span>Total</span><span>${{ number_format($order->total, 2) }}</span></div>
        </div>
        <a href="/store" class="oc-btn">Continue shopping</a>
    @else
        <h1 class="oc-title warn">Payment not completed</h1>
        <p class="oc-sub">Your order hasn't been confirmed{{ $paymentStatus ? ' (status: '.$paymentStatus.')' : '' }}. If you believe you were charged, email <a href="mailto:info@nationalpoliticalprisonercoalition.org" style="color:var(--accent-2);">info@nationalpoliticalprisonercoalition.org</a> and we'll sort it out right away.</p>
        <a href="/cart" class="oc-btn">Return to cart</a>
    @endif
</div>
@endsection
