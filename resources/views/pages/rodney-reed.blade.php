@extends('app')

@section('title', 'Everything You Should Know About Rodney Reed | NPPC')

@section('head')
<meta name="description" content="Rodney Reed's execution date has been canceled — but he's still on Texas death row. An immersive look at the case, recreated from the Innocence Project's feature.">
<style>
    /* ============================================================
       Rodney Reed — immersive full-screen slide deck, recreated from
       the Innocence Project's "Everything You Should Know About Rodney
       Reed" photo essay. Standalone dark microsite that sits on top of
       the NPPC chrome (fixed, full-viewport). All classes rr- prefixed.
       ============================================================ */

    /* Lock the page behind the deck — this is a full-screen takeover. */
    body.page-rodney-reed { overflow: hidden; }
    body.page-rodney-reed .container { overflow: visible; }
    body.page-rodney-reed .site-theme-toggle,
    body.page-rodney-reed #scroll-top { display: none !important; }

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
    .rr-hero-bg { position: absolute; inset: 0; z-index: 0; background-size: cover; background-position: center; }
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

    /* Photo placeholder (used until a real image is dropped in) */
    .rr-ph {
        width: 100%; background: #0e0e13; border: 1px solid rgba(255,255,255,0.13); border-radius: 6px;
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px;
        color: rgba(255,255,255,0.32);
    }
    .rr-ph--portrait { aspect-ratio: 3 / 4; max-width: 420px; }
    .rr-ph--land { aspect-ratio: 3 / 2; }
    .rr-ph svg { width: 46px; height: 46px; }
    .rr-ph span { font-size: 12px; letter-spacing: 0.14em; text-transform: uppercase; }

    /* ---- Split slide (photo + text) ---- */
    .rr-split { display: grid; grid-template-columns: 1fr 1fr; gap: 56px; align-items: center; width: 100%; max-width: 1200px; }
    .rr-split .rr-figure { max-width: none; align-items: flex-start; }
    .rr-split .rr-cap { text-align: left; }
    .rr-body { font-size: 1.18rem; line-height: 1.72; color: rgba(255,255,255,0.92); margin: 0; }

    /* ---- Quote slide ---- */
    .rr-quotewrap { display: flex; flex-direction: column; align-items: center; gap: 26px; width: 100%; max-width: 1040px; }
    .rr-quotewrap .rr-figure { max-width: 660px; }
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
        .rr-split { grid-template-columns: 1fr; gap: 22px; }
        .rr-split .rr-figure { max-width: 480px; }
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
    // Photo slides auto-upgrade from placeholder to real image the moment a
    // file with the expected name is added to public/images/rodney-reed/.
    $imgBase = 'images/rodney-reed/';
    $img = function ($f) use ($imgBase) {
        return file_exists(public_path($imgBase.$f)) ? asset($imgBase.$f) : null;
    };
    $hero = $img('hero.jpg');
    $reedGlass = $img('reed-glass.jpg');
    $courtroom = $img('courtroom.jpg');
    $rally = $img('rally.jpg');

    $actUrl = 'https://innocenceproject.org/petitions/justice-for-rodney-reed/';
    $slideCount = 14;
@endphp

<div class="rr-deck" id="rr-deck">

    {{-- ===== Top bar ===== --}}
    <div class="rr-topbar">
        <a class="rr-logo" href="/">
            <img src="/logo.svg" alt="National Political Prisoner Coalition">
            <span class="rr-logo-credit">Adapted from the Innocence Project</span>
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
        <div class="rr-hero-bg" style="@if($hero) background-image: linear-gradient(rgba(0,0,0,0.30), rgba(0,0,0,0.60)), url('{{ $hero }}'); @else background: radial-gradient(120% 120% at 62% 32%, #2c313d 0%, #14161d 55%, #08090d 100%); @endif"></div>
        <div class="rr-hero-inner">
            <h1 class="rr-hero-title">Rodney Reed's execution date has been canceled</h1>
            <p class="rr-hero-sub">But he's still on death row. Here's what you need to know.</p>
        </div>
    </section>

    {{-- 2 — Stay of execution --}}
    <section class="rr-slide">
        <p class="rr-statement">The Texas Court of Criminal Appeals granted Rodney Reed an indefinite stay of execution on November 15, just five days before he was scheduled to be executed.</p>
        <div class="rr-cover-cap">Cover Photo: Rodney Reed in Allan B. Polunsky Unit, West Livingston, Texas in 2015. Photo by Massoud Hayoun/Al Jazeera.</div>
    </section>

    {{-- 3 — Portrait photo --}}
    <section class="rr-slide">
        <figure class="rr-figure">
            @if ($reedGlass)
                <img class="rr-photo-img" src="{{ $reedGlass }}" alt="Rodney Reed in the Allan B. Polunsky Unit, 2014">
            @else
                <div class="rr-ph rr-ph--portrait">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="12" cy="12" r="3.2"/><path d="M8 5l1.5-2h5L16 5"/></svg>
                    <span>Photo</span>
                </div>
            @endif
            <figcaption class="rr-cap">Rodney Reed in Allan B. Polunsky Unit, West Livingston, Texas in 2014. Photo by Jana Birchum/Austin Chronicle.</figcaption>
        </figure>
    </section>

    {{-- 4 — Wrongful conviction --}}
    <section class="rr-slide">
        <p class="rr-statement">Rodney was wrongfully convicted in 1998 for the murder of Stacey Stites in Bastrop, Texas. But he now has another chance to prove his innocence.</p>
    </section>

    {{-- 5 — Split: courtroom + conviction text --}}
    <section class="rr-slide">
        <div class="rr-split">
            <figure class="rr-figure">
                @if ($courtroom)
                    <img class="rr-photo-img" src="{{ $courtroom }}" alt="Rodney Reed and his attorney Bryce Benjet at a 2014 hearing">
                @else
                    <div class="rr-ph rr-ph--land">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="12" cy="12" r="3.2"/><path d="M8 5l1.5-2h5L16 5"/></svg>
                        <span>Photo</span>
                    </div>
                @endif
                <figcaption class="rr-cap">Rodney Reed, left, and his attorney Bryce Benjet speaking at a hearing in Bastrop County in 2014. Photo by Jay Janner/Austin American-Statesman via AP.</figcaption>
            </figure>
            <p class="rr-body">But in 1998, Rodney was convicted and sentenced to death. The decision was based almost entirely on expert opinion that falsely claimed that the semen found in Stacey's body had to have been from a sexual encounter that occurred at or around the same time as the murder, thus implicating Rodney in her death. However, this testimony was false and the evidence is actually more consistent with Rodney's account of a consensual encounter the day before Stacey's disappearance.</p>
        </div>
    </section>

    {{-- 6 — The forensic timeline --}}
    <section class="rr-slide">
        <p class="rr-statement">The State told the jury Stacey was killed between 3 and 5 a.m. Leading forensic pathologists now say she was most likely killed hours earlier — before midnight — when she was alone with her fiancé.</p>
    </section>

    {{-- 7 — The first suspect --}}
    <section class="rr-slide">
        <p class="rr-statement">For months, the prime suspect was not Rodney Reed. It was Stacey's fiancé, police officer Jimmy Fennell — the last person to admit seeing her alive.</p>
    </section>

    {{-- 8 — Fennell's record + confession --}}
    <section class="rr-slide">
        <p class="rr-statement">Years later, Fennell was sent to prison for kidnapping and sexually assaulting a woman in his custody as a police officer. A fellow inmate swore under oath that Fennell confessed to Stacey's murder behind bars.</p>
    </section>

    {{-- 9 — Withheld evidence --}}
    <section class="rr-slide">
        <p class="rr-statement">At trial, prosecutors said no one could confirm Rodney and Stacey knew each other. It later emerged that coworkers had told investigators the two were close — statements never turned over to Rodney's defense.</p>
    </section>

    {{-- 10 — The untested belt --}}
    <section class="rr-slide">
        <p class="rr-statement">Stacey was strangled with a belt. That belt — the murder weapon — has never once been tested for DNA. For over a decade, Rodney has asked the courts to test it. The State has fought every request.</p>
    </section>

    {{-- 11 — Supreme Court --}}
    <section class="rr-slide">
        <p class="rr-statement">In 2023, the U.S. Supreme Court ruled 6&ndash;3 that Rodney has the right to pursue DNA testing in federal court. The most basic question — what the evidence would show — is still unanswered.</p>
    </section>

    {{-- 12 — Quote (rally photo + quote) --}}
    <section class="rr-slide">
        <div class="rr-quotewrap">
            <figure class="rr-figure">
                @if ($rally)
                    <img class="rr-photo-img" src="{{ $rally }}" alt="Rodney Reed rally at the Texas Capitol, February 2015">
                @else
                    <div class="rr-ph rr-ph--land">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="12" cy="12" r="3.2"/><path d="M8 5l1.5-2h5L16 5"/></svg>
                        <span>Photo</span>
                    </div>
                @endif
                <figcaption class="rr-cap">Rodney Reed rally at the Texas Capitol in February 2015. Photo courtesy of Jaynna Sims.</figcaption>
            </figure>
            <p class="rr-quote">&ldquo;When you first hear about this case the racial undertones in it sound like <em>To Kill a Mockingbird</em>. That this happened in the late 1990s and is going on now seems backwards.&rdquo;</p>
            <p class="rr-quote-by">&mdash; Griffin Hardy, spokesperson for Sister Helen Prejean</p>
        </div>
    </section>

    {{-- 13 — CTA --}}
    <section class="rr-slide">
        <div class="rr-cta">
            <h2>Join the fight for Rodney Reed</h2>
            <p>Add your name to this petition and stay in the loop about upcoming ways to help.</p>
            <a class="rr-bigbtn" href="{{ $actUrl }}" target="_blank" rel="noopener">Act now</a>
        </div>
    </section>

    {{-- 14 — Share --}}
    <section class="rr-slide">
        <div class="rr-share">
            <h2>Share Rodney's story</h2>
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
        <div class="rr-foot">Adapted by the National Political Prisoner Coalition from the Innocence Project's feature on Rodney Reed.</div>
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
    var text = encodeURIComponent("Rodney Reed is still on Texas death row. Here's what you should know.");
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
