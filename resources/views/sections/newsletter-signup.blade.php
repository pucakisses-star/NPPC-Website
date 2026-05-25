@php
    /**
     * Reusable inline newsletter signup. Posts to /sign-up which inserts
     * an EmailSubscriber row.
     *
     *   @include('sections.newsletter-signup')
     *   @include('sections.newsletter-signup', ['variant' => 'compact'])
     *
     * variant = 'default' (large) | 'compact' (smaller, e.g. after article)
     */
    $variant = $variant ?? 'default';
    $isCompact = $variant === 'compact';
@endphp
<section class="nls-wrap nls-{{ $variant }}">
    <div class="nls-inner">
        <div class="nls-text">
            <h2 class="nls-title">
                @if ($isCompact)
                    Keep up with the fight.
                @else
                    Stay informed about U.S. political prisoners.
                @endif
            </h2>
            <p class="nls-lede">
                @if ($isCompact)
                    Get NPPC updates on clemency campaigns, new prisoners, and the political-prisoner support movement.
                @else
                    Once a month, NPPC sends a short email with new prisoner profiles, active clemency campaigns, birthday letter-writing reminders, and updates on the political-prisoner support movement. No spam. Unsubscribe anytime.
                @endif
            </p>
        </div>
        @if (session('subscribed'))
            <div class="nls-thanks">Thanks — you're subscribed. We'll be in touch.</div>
        @else
            <form class="nls-form" action="/sign-up" method="POST">
                @csrf
                <input class="nls-input" type="email" name="email" placeholder="you@example.com" required aria-label="Email address">
                <button class="nls-btn" type="submit">Subscribe</button>
            </form>
        @endif
    </div>
</section>
<style>
    .nls-wrap { padding: 56px 0; margin: 48px 0; border-top: 1px solid rgba(255,255,255,0.08); border-bottom: 1px solid rgba(255,255,255,0.08); background: linear-gradient(180deg, rgba(86,96,254,0.05) 0%, rgba(86,96,254,0) 100%); }
    .nls-wrap.nls-compact { padding: 36px 24px; margin: 32px 0 16px; border-radius: 12px; border: 1px solid rgba(86,96,254,0.25); background: rgba(86,96,254,0.08); }
    .nls-inner { max-width: 720px; margin: 0 auto; padding: 0 24px; }
    .nls-compact .nls-inner { padding: 0; }
    .nls-text { margin-bottom: 24px; text-align: center; }
    .nls-title { font-size: 1.75rem; font-weight: 900; color: #fff; line-height: 1.15; margin: 0 0 8px; }
    .nls-compact .nls-title { font-size: 1.25rem; }
    .nls-lede { font-size: 15px; color: rgba(255,255,255,0.7); line-height: 1.6; margin: 0; }
    .nls-compact .nls-lede { font-size: 14px; }
    .nls-form { display: flex; gap: 8px; max-width: 480px; margin: 0 auto; }
    .nls-input { flex: 1; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.2); border-radius: 6px; padding: 12px 14px; color: #fff; font-size: 15px; outline: none; }
    .nls-input:focus { border-color: #5660fe; }
    .nls-input::placeholder { color: rgba(255,255,255,0.35); }
    .nls-btn { background: #5660fe; color: #fff; border: none; border-radius: 6px; padding: 12px 22px; font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; cursor: pointer; transition: background 0.15s; }
    .nls-btn:hover { background: #4850e6; }
    .nls-thanks { text-align: center; padding: 14px; border-radius: 6px; background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); color: #22c55e; font-weight: 700; max-width: 480px; margin: 0 auto; }

    @media (max-width: 640px) {
        .nls-wrap { padding: 40px 0; margin: 32px 0; }
        .nls-title { font-size: 1.4rem; }
        .nls-form { flex-direction: column; gap: 10px; }
    }
</style>
