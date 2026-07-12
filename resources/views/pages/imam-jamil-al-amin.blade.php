@extends('app')

@section('title', 'Everything You Should Know About Imam Jamil Al-Amin | NPPC')

@section('head')
<meta name="description" content="Imam Jamil Al-Amin — the civil-rights leader once known as H. Rap Brown — died in federal custody in November 2025, still maintaining his innocence in the killing of a Fulton County deputy while another man's sworn confession went unheard. An immersive look at the case.">
<style>
    /* ============================================================
       Imam Jamil Al-Amin — immersive full-screen slide deck in the
       NPPC story format (originated with the Rodney Reed feature
       adapted from the Innocence Project). Standalone dark microsite
       that sits on top of the NPPC chrome (fixed, full-viewport).
       All classes rr- prefixed (shared story-deck styles).
       ============================================================ */

    /* Lock the page behind the deck — this is a full-screen takeover. */
    body.page-imam-jamil-al-amin { overflow: hidden; }
    body.page-imam-jamil-al-amin .container { overflow: visible; }
    body.page-imam-jamil-al-amin .site-theme-toggle,
    body.page-imam-jamil-al-amin #scroll-top { display: none !important; }

    .rr-deck {
        position: fixed; inset: 0; z-index: 2147483600;
        background: #060608; color: #fff;
        font-family: 'Verlag', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
        overflow: hidden; -webkit-font-smoothing: antialiased;
    }
    .rr-deck * { box-sizing: border-box; }

    /* ---- Top bar ---- */
    .rr-topbar {
        position: absolute; top: 0; left: 0; right: 0; z-index: 8;
        display: flex; align-items: center; justify-content: space-between;
        padding: 20px 28px;
    }
    .rr-logo { display: inline-flex; align-items: center; gap: 10px; text-decoration: none; }
    .rr-logo img { height: 30px; width: auto; display: block; }
    .rr-logo-credit { font-size: 10px; line-height: 1.2; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255,255,255,0.5); max-width: 130px; }
    .rr-topbar-right { display: flex; align-items: center; gap: 18px; }
    .rr-share-jump {
        background: none; border: none; color: #fff; font: inherit; font-size: 15px; font-weight: 600;
        cursor: pointer; display: inline-flex; align-items: center; gap: 6px; padding: 8px 4px;
    }
    .rr-share-jump svg { width: 11px; height: 11px; }
    .rr-actnow {
        background: #3b5bfd; color: #fff; text-decoration: none; font-size: 15px; font-weight: 700;
        padding: 12px 22px; border-radius: 7px; transition: background 0.15s;
    }
    .rr-actnow:hover { background: #2a47df; color: #fff; }

    /* ---- Progress segments ---- */
    .rr-progress {
        position: absolute; top: 70px; left: 28px; right: 28px; z-index: 7;
        display: flex; gap: 6px;
    }
    .rr-seg { flex: 1 1 0; height: 3px; background: rgba(255,255,255,0.16); cursor: pointer; transition: background 0.25s; }
    .rr-seg.is-done { background: #fff; }

    /* ---- Slides ---- */
    .rr-slide {
        position: absolute; inset: 0; z-index: 1;
        display: flex; align-items: center; justify-content: center;
        padding: 120px 112px 88px;
        opacity: 0; visibility: hidden; pointer-events: none;
        transition: opacity 0.5s ease;
    }
    .rr-slide.is-active { opacity: 1; visibility: visible; pointer-events: auto; z-index: 2; }

    /* ---- Hero slide ---- */
    .rr-hero { padding: 0; }
    .rr-hero-bg { position: absolute; inset: 0; z-index: 0; background-size: cover; background-position: center 22%; }
    .rr-hero-inner { position: absolute; z-index: 1; left: 8%; right: 8%; top: 50%; transform: translateY(-50%); max-width: 70%; }
    .rr-hero-title { font-size: 4.4rem; line-height: 1.04; font-weight: 800; letter-spacing: -0.02em; margin: 0; }
    .rr-hero-sub { font-size: 1.25rem; line-height: 1.5; color: rgba(255,255,255,0.9); margin: 26px 0 0; max-width: 540px; }

    /* ---- Statement (big centered text) ---- */
    .rr-statement {
        font-size: 2.6rem; line-height: 1.22; font-weight: 800; letter-spacing: -0.01em;
        color: #fff; max-width: 1120px; text-align: center; margin: 0;
    }

    /* ---- Centered figure (photo + caption) ---- */
    .rr-figure { display: flex; flex-direction: column; align-items: center; gap: 18px; width: 100%; max-width: 560px; }
    .rr-figure--land { max-width: 880px; }
    .rr-photo-img { width: 100%; height: auto; max-height: 64vh; object-fit: contain; display: block; }
    .rr-cap { font-size: 0.84rem; line-height: 1.5; color: rgba(255,255,255,0.55); text-align: center; max-width: 620px; margin: 0; }

    /* ---- Quote slide ---- */
    .rr-quotewrap { display: flex; flex-direction: column; align-items: center; gap: 26px; width: 100%; max-width: 1040px; }
    .rr-quote { font-size: 2.05rem; line-height: 1.28; font-weight: 800; text-align: center; margin: 0; letter-spacing: -0.005em; }
    .rr-quote-by { font-size: 1.05rem; color: rgba(255,255,255,0.72); text-align: center; margin: 0; }

    /* ---- CTA slide ---- */
    .rr-cta { display: flex; flex-direction: column; align-items: center; gap: 22px; text-align: center; max-width: 720px; }
    .rr-cta h2 { font-size: 2.7rem; font-weight: 800; margin: 0; letter-spacing: -0.01em; }
    .rr-cta p { font-size: 1.2rem; line-height: 1.55; color: rgba(255,255,255,0.85); margin: 0; max-width: 520px; }
    .rr-bigbtn { display: inline-block; background: #3b5bfd; color: #fff; font-size: 1.1rem; font-weight: 800; text-decoration: none; padding: 18px 44px; border-radius: 7px; margin-top: 8px; transition: background 0.15s, transform 0.15s; }
    .rr-bigbtn:hover { background: #2a47df; color: #fff; transform: translateY(-2px); }

    /* ---- Share slide ---- */
    .rr-share { display: flex; flex-direction: column; align-items: center; gap: 34px; text-align: center; }
    .rr-share h2 { font-size: 2.7rem; font-weight: 800; margin: 0; letter-spacing: -0.01em; }
    .rr-socials { display: flex; gap: 22px; }
    .rr-social { width: 64px; height: 64px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.5); display: flex; align-items: center; justify-content: center; color: #fff; text-decoration: none; transition: background 0.15s, color 0.15s; }
    .rr-social:hover { background: #fff; color: #060608; }
    .rr-social svg { width: 22px; height: 22px; fill: currentColor; }
    .rr-foot { position: absolute; bottom: 22px; left: 0; right: 0; text-align: center; font-size: 0.8rem; color: rgba(255,255,255,0.45); z-index: 3; }

    /* Pinned cover-photo caption (slide 2) */
    .rr-cover-cap { position: absolute; bottom: 26px; left: 28px; right: 28px; text-align: center; font-size: 0.8rem; color: rgba(255,255,255,0.5); z-index: 3; }

    /* ---- Nav arrows ---- */
    .rr-nav {
        position: absolute; top: 50%; transform: translateY(-50%); z-index: 9;
        width: 56px; height: 56px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.4); color: #fff;
        cursor: pointer; transition: background 0.15s, border-color 0.15s;
    }
    .rr-nav:hover { background: rgba(255,255,255,0.18); }
    .rr-nav svg { width: 22px; height: 22px; }
    .rr-nav-prev { left: 24px; }
    .rr-nav-next { right: 24px; }
    .rr-nav-accent { background: #3b5bfd; border-color: #3b5bfd; }
    .rr-nav-accent:hover { background: #2a47df; }

    /* ---- Responsive ---- */
    @@media (max-width: 900px) {
        .rr-slide { padding: 104px 64px 80px; }
        .rr-hero-inner { max-width: 86%; left: 7%; right: 7%; }
        .rr-hero-title { font-size: 2.6rem; }
        .rr-hero-sub { font-size: 1.05rem; }
        .rr-statement { font-size: 1.6rem; }
        .rr-quote { font-size: 1.45rem; }
        .rr-cta h2, .rr-share h2 { font-size: 1.9rem; }
        .rr-nav { width: 44px; height: 44px; }
        .rr-nav-prev { left: 10px; }
        .rr-nav-next { right: 10px; }
        .rr-actnow { padding: 10px 16px; font-size: 14px; }
    }
    @@media (max-width: 560px) {
        .rr-slide { padding: 100px 54px 76px; }
        .rr-hero-title { font-size: 2.1rem; }
        .rr-statement { font-size: 1.3rem; }
        .rr-topbar { padding: 16px 16px; }
        .rr-logo-credit { display: none; }
    }
</style>
@endsection

@section('body')
@php
    $imgBase = 'images/imam-jamil-al-amin/';
    $img = function ($f) use ($imgBase) {
        return file_exists(public_path($imgBase.$f)) ? asset($imgBase.$f) : null;
    };
    $hero = $img('hero.jpg');
    $portrait = $img('portrait.jpg');

    $actUrl = 'https://www.imamjamilactionnetwork.org/';
    $slideCount = 14;
@endphp

<div class="rr-deck" id="rr-deck">

    {{-- ===== Top bar ===== --}}
    <div class="rr-topbar">
        <a class="rr-logo" href="/">
            <img src="/logo.svg" alt="National Political Prisoner Coalition">
            <span class="rr-logo-credit">An NPPC story</span>
        </a>
        <div class="rr-topbar-right">
            <button type="button" class="rr-share-jump">Share
                <svg viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M1 1l4 4 4-4"/></svg>
            </button>
            <a class="rr-actnow" href="{{ $actUrl }}" target="_blank" rel="noopener">Act Now</a>
        </div>
    </div>

    {{-- ===== Progress segments ===== --}}
    <div class="rr-progress">
        @for ($s = 0; $s < $slideCount; $s++)
            <span class="rr-seg" data-seg="{{ $s }}"></span>
        @endfor
    </div>

    {{-- ===== Slides ===== --}}

    {{-- 1 — Hero --}}
    <section class="rr-slide rr-hero is-active">
        <div class="rr-hero-bg" style="@if($hero) background-image: linear-gradient(rgba(0,0,0,0.42), rgba(0,0,0,0.72)), url('{{ $hero }}'); @else background: radial-gradient(120% 120% at 62% 32%, #2c313d 0%, #14161d 55%, #08090d 100%); @endif"></div>
        <div class="rr-hero-inner">
            <h1 class="rr-hero-title">Imam Jamil Al-Amin died in federal custody on November 23, 2025</h1>
            <p class="rr-hero-sub">He spent his last 23 years insisting he did not kill Deputy Ricky Kinchen — while another man confessed to the crime. Here's what you need to know.</p>
        </div>
    </section>

    {{-- 2 — Who he was --}}
    <section class="rr-slide">
        <p class="rr-statement">Before he was Imam Jamil Al-Amin, he was H. Rap Brown — the fifth chairman of the Student Nonviolent Coordinating Committee, Minister of Justice of the Black Panther Party, and one of the defining voices of Black Power.</p>
        <div class="rr-cover-cap">Cover photo: H. Rap Brown at a SNCC news conference, 1967. Photo by Marion S. Trikosko, U.S. News &amp; World Report collection, Library of Congress (public domain).</div>
    </section>

    {{-- 3 — Portrait photo --}}
    <section class="rr-slide">
        <figure class="rr-figure">
            @if ($portrait)
                <img class="rr-photo-img" src="{{ $portrait }}" alt="H. Rap Brown speaking at a SNCC news conference in 1967">
            @else
                <div class="rr-ph rr-ph--portrait">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="12" cy="12" r="3.2"/><path d="M8 5l1.5-2h5L16 5"/></svg>
                    <span>Photo</span>
                </div>
            @endif
            <figcaption class="rr-cap">H. Rap Brown at a SNCC news conference in 1967. Photo by Marion S. Trikosko, U.S. News &amp; World Report collection, Library of Congress (public domain).</figcaption>
        </figure>
    </section>

    {{-- 4 — The Rap Brown Act --}}
    <section class="rr-slide">
        <p class="rr-statement">Washington considered him dangerous enough to write a law about him. The 1968 federal Anti-Riot Act is still known as the "Rap Brown Act" — and it has been used against protest movements from the Chicago Eight to defendants charged today.</p>
    </section>

    {{-- 5 — Transformation --}}
    <section class="rr-slide">
        <p class="rr-statement">In prison in the 1970s he converted to Islam and emerged as Imam Jamil Abdullah Al-Amin. In Atlanta's West End, his mosque drew thousands — and neighbors credited him with helping drive drugs out of the neighborhood.</p>
    </section>

    {{-- 6 — March 16, 2000 --}}
    <section class="rr-slide">
        <p class="rr-statement">On March 16, 2000, two Fulton County sheriff's deputies — Ricky Kinchen and Aldranon English — came to his West End store to serve a warrant. Gunfire was exchanged. Kinchen was killed and English wounded. Al-Amin was arrested four days later in White Hall, Alabama.</p>
    </section>

    {{-- 7 — Conviction --}}
    <section class="rr-slide">
        <p class="rr-statement">In March 2002, a Fulton County jury convicted him of murder, and he was sentenced to life without the possibility of parole. He maintained from the first day to the last that he was not the shooter.</p>
    </section>

    {{-- 8 — The disputed identification --}}
    <section class="rr-slide">
        <p class="rr-statement">The case against him was disputed from the start. Deputy English told investigators he had shot and wounded the gunman — but when Al-Amin was captured days later, he had no gunshot wounds. English also described a shooter with gray eyes. Al-Amin's eyes are brown.</p>
    </section>

    {{-- 9 — Otis Jackson --}}
    <section class="rr-slide">
        <p class="rr-statement">Another man — federal inmate Otis Jackson — repeatedly confessed, including under oath, to the shooting. The jury that convicted Al-Amin never heard him. The courts never reopened the case.</p>
    </section>

    {{-- 10 — Federal custody anomaly --}}
    <section class="rr-slide">
        <p class="rr-statement">Though convicted of a state crime, Al-Amin was transferred into the federal Bureau of Prisons — including years at ADX Florence, the most isolating supermax in America, a prison meant for people with federal convictions. He had none.</p>
    </section>

    {{-- 11 — Calls to reopen --}}
    <section class="rr-slide">
        <p class="rr-statement">For two decades the Council on American-Islamic Relations, the Imam Jamil Action Network, and civil-rights advocates across the country called for the case to be reopened. It never was.</p>
    </section>

    {{-- 12 — Quote --}}
    <section class="rr-slide">
        <div class="rr-quotewrap">
            <p class="rr-quote">&ldquo;Justice means &lsquo;just us.&rsquo;&rdquo;</p>
            <p class="rr-quote-by">&mdash; H. Rap Brown, <em>Die Nigger Die!</em> (1969)</p>
        </div>
    </section>

    {{-- 13 — CTA --}}
    <section class="rr-slide">
        <div class="rr-cta">
            <h2>The fight for his name continues</h2>
            <p>Imam Jamil Al-Amin died at the Federal Medical Center in Butner, North Carolina, at age 82 — still serving a sentence his supporters believe belonged to another man. His family and the Imam Jamil Action Network are still fighting to clear his name.</p>
            <a class="rr-bigbtn" href="{{ $actUrl }}" target="_blank" rel="noopener">Act now</a>
        </div>
    </section>

    {{-- 14 — Share --}}
    <section class="rr-slide">
        <div class="rr-share">
            <h2>Share his story</h2>
            <div class="rr-socials">
                <a class="rr-social" data-share="facebook" href="#" target="_blank" rel="noopener" aria-label="Share on Facebook">
                    <svg viewBox="0 0 24 24"><path d="M14 8.5h2.5V5.2C16.1 5.1 14.9 5 13.6 5c-2.7 0-4.6 1.7-4.6 4.7V12H6v3.5h3v9h3.6v-9H15l.5-3.5h-3V10c0-1 .3-1.5 1.5-1.5z"/></svg>
                </a>
                <a class="rr-social" data-share="twitter" href="#" target="_blank" rel="noopener" aria-label="Share on Twitter">
                    <svg viewBox="0 0 24 24"><path d="M22 6.1c-.7.3-1.5.6-2.3.7.8-.5 1.5-1.3 1.8-2.3-.8.5-1.7.8-2.6 1A4 4 0 0 0 12 9.3c0 .3 0 .6.1.9A11.4 11.4 0 0 1 3.8 5a4 4 0 0 0 1.2 5.3c-.6 0-1.2-.2-1.8-.5v.1c0 1.9 1.4 3.5 3.2 3.9-.6.2-1.3.2-1.9.1a4 4 0 0 0 3.7 2.8A8 8 0 0 1 2 18.6 11.3 11.3 0 0 0 8.2 20c7.4 0 11.4-6.1 11.4-11.4v-.5c.8-.6 1.5-1.3 2-2z"/></svg>
                </a>
                <a class="rr-social" data-share="linkedin" href="#" target="_blank" rel="noopener" aria-label="Share on LinkedIn">
                    <svg viewBox="0 0 24 24"><path d="M6.9 8.6H4V20h2.9V8.6zM5.4 4a1.7 1.7 0 1 0 0 3.4 1.7 1.7 0 0 0 0-3.4zM20 13.8c0-2.9-1.6-4.3-3.7-4.3-1.7 0-2.5.9-2.9 1.6V8.6H10.5c0 .8 0 11.4 0 11.4h2.9v-6.4c0-.3 0-.7.1-.9.3-.7.9-1.4 1.9-1.4 1.3 0 1.8 1 1.8 2.5V20H20v-6.2z"/></svg>
                </a>
            </div>
        </div>
        <div class="rr-foot">Sources: NPPC case file for Imam Jamil Al-Amin; Library of Congress (photos). His profile in the NPPC database: <a href="/prisoner/imam-jamil-al-amin" style="color:rgba(255,255,255,0.65);">nppc.org/prisoner/imam-jamil-al-amin</a></div>
    </section>

    {{-- ===== Nav arrows ===== --}}
    <button type="button" class="rr-nav rr-nav-prev" aria-label="Previous">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
    </button>
    <button type="button" class="rr-nav rr-nav-next" aria-label="Next">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
    </button>

</div>

<script>
(function () {
    var deck = document.getElementById('rr-deck');
    if (!deck) return;
    var slides = [].slice.call(deck.querySelectorAll('.rr-slide'));
    var segs = [].slice.call(deck.querySelectorAll('.rr-seg'));
    var prev = deck.querySelector('.rr-nav-prev');
    var next = deck.querySelector('.rr-nav-next');
    var i = 0;

    function render() {
        slides.forEach(function (s, n) { s.classList.toggle('is-active', n === i); });
        segs.forEach(function (s, n) { s.classList.toggle('is-done', n <= i); });
        prev.style.visibility = (i === 0) ? 'hidden' : 'visible';
        next.style.visibility = (i === slides.length - 1) ? 'hidden' : 'visible';
        next.classList.toggle('rr-nav-accent', i === 0);
    }
    function go(n) { i = Math.max(0, Math.min(slides.length - 1, n)); render(); }

    prev.addEventListener('click', function () { go(i - 1); });
    next.addEventListener('click', function () { go(i + 1); });
    segs.forEach(function (s, n) { s.addEventListener('click', function () { go(n); }); });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight' || e.key === 'ArrowDown' || e.key === 'PageDown') go(i + 1);
        else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp' || e.key === 'PageUp') go(i - 1);
    });

    // Touch swipe
    var x0 = null;
    deck.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    deck.addEventListener('touchend', function (e) {
        if (x0 === null) return;
        var dx = e.changedTouches[0].clientX - x0;
        if (Math.abs(dx) > 50) go(dx < 0 ? i + 1 : i - 1);
        x0 = null;
    }, { passive: true });

    // Scroll / trackpad wheel — one slide per gesture (scroll down = next slide,
    // scroll up = previous). The lock stays engaged until the gesture (including
    // trackpad momentum) settles, so a single scroll advances exactly one slide.
    var wheelLock = false, wheelTimer = null;
    deck.addEventListener('wheel', function (e) {
        if (Math.abs(e.deltaY) < Math.abs(e.deltaX)) return; // ignore horizontal scroll
        e.preventDefault();
        clearTimeout(wheelTimer);
        wheelTimer = setTimeout(function () { wheelLock = false; }, 600);
        if (wheelLock || Math.abs(e.deltaY) < 8) return;
        wheelLock = true;
        go(i + (e.deltaY > 0 ? 1 : -1));
    }, { passive: false });

    // "Share" in the top bar jumps to the final slide
    var jump = deck.querySelector('.rr-share-jump');
    if (jump) jump.addEventListener('click', function () { go(slides.length - 1); });

    // Wire share links to the live URL
    var url = encodeURIComponent(window.location.href);
    var text = encodeURIComponent("Imam Jamil Al-Amin (H. Rap Brown) died in federal custody maintaining his innocence — while another man confessed. Here's what you should know.");
    var map = {
        facebook: 'https://www.facebook.com/sharer/sharer.php?u=' + url,
        twitter: 'https://twitter.com/intent/tweet?url=' + url + '&text=' + text,
        linkedin: 'https://www.linkedin.com/sharing/share-offsite/?url=' + url
    };
    Object.keys(map).forEach(function (k) {
        var el = deck.querySelector('[data-share="' + k + '"]');
        if (el) el.href = map[k];
    });

    render();
})();
</script>
@endsection
