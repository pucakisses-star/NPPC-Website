@php
    /**
     * Reusable inline newsletter signup with animated L-shape frame.
     *
     *   @include('sections.newsletter-signup')
     *   @include('sections.newsletter-signup', ['variant' => 'compact'])
     */
    $variant = $variant ?? 'default';
    $isCompact = $variant === 'compact';
@endphp
<section class="nls-wrap nls-{{ $variant }}">
    <div class="nls-frame">
        <span class="nls-frame-line nls-line-top"></span>
        <span class="nls-frame-line nls-line-left"></span>
        <span class="nls-frame-line nls-line-bottom"></span>
        <span class="nls-frame-line nls-line-right"></span>
        <div class="nls-inner">
            <div class="nls-brand">Dispatch &middot; NPPC Bulletin</div>
            <h2 class="nls-title">
                @if ($isCompact)
                    Stay close to the fight for political prisoners.
                @else
                    Stay informed about the fight for political prisoners.
                @endif
            </h2>
            @unless($isCompact)
                <p class="nls-lede">Once a month: new prisoner profiles, active clemency campaigns, birthday letter-writing reminders, and updates from the political-prisoner support movement. No spam.</p>
            @endunless
            @if (session('subscribed'))
                <div class="nls-thanks">Thanks — you're subscribed. We'll be in touch.</div>
            @else
                <form class="nls-form" action="/sign-up" method="POST">
                    @csrf
                    <input class="nls-input" type="email" name="email" placeholder="Email Address" required aria-label="Email address">
                    <button class="nls-btn" type="submit">Submit</button>
                </form>
            @endif
        </div>
    </div>
</section>
<style>
    .nls-wrap { padding: 56px 0; margin: 48px 0; }
    .nls-wrap.nls-compact { padding: 24px 0; margin: 32px 0 16px; }
    .nls-frame { position: relative; max-width: 760px; margin: 0 auto; padding: 56px 56px 48px; }
    .nls-compact .nls-frame { padding: 36px 32px 28px; max-width: 680px; }
    .nls-inner { position: relative; z-index: 1; }

    /* Animated L-shape frame: navy lines that draw in from corners on scroll. */
    .nls-frame-line { position: absolute; background: #5660fe; display: block; transform-origin: top left; transition: transform 0.9s cubic-bezier(.59,.08,.39,.95); }
    .nls-line-top    { top: 0;    left: 0;  height: 3px; width: 40%; transform: scaleX(0); }
    .nls-line-left   { top: 0;    left: 0;  width: 3px;  height: 40%; transform: scaleY(0); transform-origin: top left; }
    .nls-line-bottom { bottom: 0; right: 0; height: 3px; width: 40%; transform: scaleX(0); transform-origin: top right; }
    .nls-line-right  { bottom: 0; right: 0; width: 3px;  height: 40%; transform: scaleY(0); transform-origin: bottom right; }
    .nls-wrap.in-view .nls-line-top,
    .nls-wrap.in-view .nls-line-bottom { transform: scaleX(1); }
    .nls-wrap.in-view .nls-line-left,
    .nls-wrap.in-view .nls-line-right  { transform: scaleY(1); }
    .nls-wrap.in-view .nls-line-bottom { transition-delay: 0.25s; }
    .nls-wrap.in-view .nls-line-right  { transition-delay: 0.45s; }
    .nls-wrap.in-view .nls-line-left   { transition-delay: 0.65s; }

    .nls-brand { font-size: 12px; font-weight: 800; color: #5660fe; text-transform: uppercase; letter-spacing: 0.16em; margin-bottom: 14px; }
    .nls-compact .nls-brand { font-size: 11px; margin-bottom: 10px; }
    .nls-title { font-size: 2rem; font-weight: 900; color: #fff; line-height: 1.1; margin: 0 0 14px; }
    .nls-compact .nls-title { font-size: 1.35rem; margin-bottom: 16px; }
    .nls-lede { font-size: 15px; color: rgba(255,255,255,0.65); line-height: 1.6; margin: 0 0 24px; max-width: 560px; }

    .nls-form { display: flex; gap: 0; max-width: 560px; }
    .nls-input { flex: 1; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.18); border-right: 0; border-radius: 4px 0 0 4px; padding: 14px 16px; color: #fff; font-size: 15px; outline: none; }
    .nls-input:focus { border-color: #5660fe; }
    .nls-input::placeholder { color: rgba(255,255,255,0.4); }
    .nls-btn { background: #ff5851; color: #fff; border: 1px solid #ff5851; border-radius: 0 4px 4px 0; padding: 14px 28px; font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; cursor: pointer; transition: background 0.15s, border-color 0.15s; }
    .nls-btn:hover { background: #e04640; border-color: #e04640; }
    .nls-thanks { padding: 14px; border-radius: 6px; background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); color: #22c55e; font-weight: 700; max-width: 560px; }

    @media (max-width: 640px) {
        .nls-frame { padding: 36px 24px; }
        .nls-compact .nls-frame { padding: 28px 20px; }
        .nls-title { font-size: 1.4rem; }
        .nls-compact .nls-title { font-size: 1.15rem; }
        .nls-form { flex-direction: column; gap: 10px; }
        .nls-input { border-right: 1px solid rgba(255,255,255,0.18); border-radius: 4px; }
        .nls-btn { border-radius: 4px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .nls-frame-line { transition: none !important; }
        .nls-line-top, .nls-line-bottom { transform: scaleX(1); }
        .nls-line-left, .nls-line-right { transform: scaleY(1); }
    }
</style>
<script>
(function () {
    if (window.__nlsInit) return; window.__nlsInit = true;
    if (!('IntersectionObserver' in window)) {
        document.querySelectorAll('.nls-wrap').forEach(function (el) { el.classList.add('in-view'); });
        return;
    }
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) {
                e.target.classList.add('in-view');
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.25 });
    document.querySelectorAll('.nls-wrap').forEach(function (el) { io.observe(el); });
})();
</script>
