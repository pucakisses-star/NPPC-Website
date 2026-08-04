@extends('app')

@section('title', 'Repression in America — National Political Prisoner Coalition')

@section('head')
    <meta name="description" content="An immersive introduction to the history of political imprisonment in the United States — the record, the names, the documents, and the museum — built from the NPPC's database of more than 7,000 political prisoners.">
    @verbatim
    <style>
        /* Repression in America — immersive black documentary hub.
           Condensed all-caps display type, near-black photo treatments,
           sparse panels that fade in on scroll. Fixed palette in both
           themes. All imagery is our own already-shipped public-domain
           library. */
        /* Verlag, the site's licensed face, in place of an Avenir stack that
           was never licensed here and fell back to Helvetica off Apple. */
        .ra { background: #000; color: #fff; font-family: Verlag A, Verlag B, Verlag, 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        .ra * { box-sizing: border-box; }
        .ra-caps { font-family: 'Arial Narrow', 'Helvetica Neue Condensed', 'Roboto Condensed', 'Liberation Sans Narrow', Arial, sans-serif; font-weight: 800; text-transform: uppercase; letter-spacing: .01em; }
        .ra-wrap { max-width: 1200px; margin: 0 auto; padding: 0 32px; }

        /* tiny all-caps link + circular arrow chip */
        .ra-cta { display: inline-flex; align-items: center; gap: 14px; text-decoration: none; color: #fff; }
        .ra-cta .t { font-size: 12px; font-weight: 800; letter-spacing: .22em; text-transform: uppercase; }
        .ra-cta .o {
            width: 36px; height: 36px; border-radius: 50%; background: #fff; color: #000;
            display: inline-flex; align-items: center; justify-content: center; font-size: 15px;
            transition: transform .18s;
        }
        .ra-cta:hover .o { transform: translateX(4px); }

        /* thin-rule caption */
        .ra-cap { position: relative; padding-left: 18px; max-width: 340px; font-size: 13px; line-height: 1.75; color: rgba(255,255,255,.62); }
        .ra-cap::before { content: ""; position: absolute; left: 0; top: 4px; bottom: 4px; width: 1px; background: rgba(255,255,255,.35); }

        /* scroll reveal */
        .ra-rev { opacity: 0; transform: translateY(26px); transition: opacity .9s ease, transform .9s ease; }
        .ra-rev.in { opacity: 1; transform: none; }
        @media (prefers-reduced-motion: reduce) { .ra-rev { opacity: 1; transform: none; transition: none; } }

        /* ---- hero: crossfading near-black photos, centered condensed title ---- */
        .ra-hero { position: relative; height: 100vh; min-height: 560px; overflow: hidden; display: flex; align-items: center; justify-content: center; text-align: center; }
        .ra-hero .ph {
            position: absolute; inset: 0; background-size: cover; background-position: center;
            filter: grayscale(100%) contrast(1.1); opacity: 0; transition: opacity 2.4s ease;
        }
        .ra-hero .ph.on { opacity: .22; }
        .ra-hero .inner { position: relative; z-index: 2; padding: 0 24px; }
        .ra-hero .mark { font-size: 13px; font-weight: 800; letter-spacing: .34em; color: #fff; margin: 0 0 26px; }
        .ra-hero h1 { margin: 0; font-size: clamp(58px, 11vw, 150px); line-height: .92; }
        .ra-hero .down {
            position: absolute; bottom: 34px; left: 50%; transform: translateX(-50%); z-index: 3;
            width: 44px; height: 44px; border-radius: 50%; border: 1px solid rgba(255,255,255,.55);
            display: flex; align-items: center; justify-content: center; color: #fff; text-decoration: none;
            transition: background .15s, color .15s;
        }
        .ra-hero .down:hover { background: #fff; color: #000; }
        .ra-hero .credit { position: absolute; bottom: 12px; right: 16px; z-index: 3; font-size: 10.5px; letter-spacing: .06em; color: rgba(255,255,255,.35); }

        /* ---- statement ---- */
        .ra-statement { padding: 170px 0 150px; text-align: center; }
        .ra-statement .big { margin: 0 auto 40px; max-width: 900px; font-size: clamp(30px, 5vw, 58px); line-height: 1.08; }
        .ra-statement .rule { width: 1px; height: 54px; background: rgba(255,255,255,.4); margin: 0 auto 34px; }
        .ra-statement .body { margin: 0 auto; max-width: 560px; font-size: 14px; line-height: 1.85; color: rgba(255,255,255,.62); }
        .ra-stats { display: flex; justify-content: center; gap: clamp(34px, 7vw, 90px); margin-top: 90px; flex-wrap: wrap; }
        .ra-stat .n { font-size: clamp(40px, 5.4vw, 66px); line-height: 1; }
        .ra-stat .l { font-size: 11px; font-weight: 800; letter-spacing: .2em; text-transform: uppercase; color: rgba(255,255,255,.5); margin-top: 12px; }

        /* ---- chapter panels: sparse black, offset media ---- */
        .ra-panel { position: relative; min-height: 96vh; display: flex; align-items: center; padding: 90px 0; overflow: hidden; }
        .ra-panel .grid { display: grid; grid-template-columns: 480px 1fr; gap: clamp(40px, 6vw, 96px); align-items: center; width: 100%; }
        .ra-panel.flip .grid { grid-template-columns: 1fr 480px; }
        .ra-panel .media { position: relative; }
        .ra-panel .media .img { width: 100%; aspect-ratio: 16/10.5; background-size: cover; background-position: center; filter: grayscale(100%) contrast(1.06) brightness(.9); }
        .ra-panel .media .pgrid { display: grid; grid-template-columns: repeat(3, 1fr); }
        .ra-panel .media .pgrid img { width: 100%; aspect-ratio: 5/6.4; object-fit: cover; filter: grayscale(100%) brightness(.92); display: block; }
        .ra-panel.flip .media { order: 2; }
        .ra-panel h2 { margin: 0 0 26px; font-size: clamp(44px, 6.5vw, 92px); line-height: .95; }
        .ra-panel .ra-cap { margin: 0 0 34px; }
        @media (max-width: 940px) {
            .ra-panel { min-height: 0; }
            .ra-panel .grid, .ra-panel.flip .grid { grid-template-columns: 1fr; }
            .ra-panel.flip .media { order: 0; }
        }

        /* footage panel: framed still + circular play chip */
        #ra-footage .media .img { box-shadow: inset 0 0 0 1px rgba(255,255,255,.18); }
        .ra-panel .media .play {
            position: absolute; right: 16px; bottom: 16px; width: 42px; height: 42px; border-radius: 50%;
            background: #fff; color: #000; display: flex; align-items: center; justify-content: center; font-size: 13px;
        }

        /* faint portrait strip peeking at a panel edge, like the reference */
        .ra-peek { position: absolute; left: 0; right: 0; bottom: -1px; display: grid; grid-template-columns: repeat(6, 1fr); gap: 2px; opacity: .5; pointer-events: none; }
        .ra-peek img { width: 100%; height: 84px; object-fit: cover; object-position: top; filter: grayscale(100%) brightness(.8); display: block; }

        /* ---- get involved ---- */
        .ra-involved { padding: 180px 0 150px; text-align: center; }
        .ra-involved h2 { margin: 0 0 34px; font-size: clamp(56px, 10vw, 140px); line-height: .95; }
        .ra-involved .rule { width: 1px; height: 54px; background: rgba(255,255,255,.4); margin: 0 auto 30px; }
        .ra-involved .lede { margin: 0 auto 70px; max-width: 420px; font-size: 14px; line-height: 1.85; color: rgba(255,255,255,.62); }
        .ra-links { display: flex; justify-content: center; gap: clamp(24px, 4.5vw, 60px); flex-wrap: wrap; margin-bottom: 80px; }
        .ra-links a { color: rgba(255,255,255,.85); text-decoration: none; font-size: 12px; font-weight: 800; letter-spacing: .22em; text-transform: uppercase; padding-bottom: 6px; border-bottom: 1px solid rgba(255,255,255,.25); transition: color .15s, border-color .15s; }
        .ra-links a:hover { color: #fff; border-color: #fff; }
    </style>
    @endverbatim
@endsection

@section('body')
@php
    $stats = \Illuminate\Support\Facades\Cache::remember('repression-page:stats', now()->addHours(6), function () {
        return [
            'total' => \App\Models\Prisoner::count(),
            'inCustody' => \App\Models\Prisoner::where('in_custody', true)->count(),
        ];
    });
@endphp
<div class="ra">

    {{-- ============ hero ============ --}}
    <section class="ra-hero" id="ra-top">
        <div class="ph on" style="background-image:url('/images/articles/iww-deportation-1917.jpg')"></div>
        <div class="ph" style="background-image:url('/storage/history/bonus-army.jpg')"></div>
        <div class="ph" style="background-image:url('/images/topics-eras.jpg')"></div>
        <div class="inner">
            <p class="mark ra-caps">NPPC</p>
            <h1 class="ra-caps">Repression<br>in America</h1>
        </div>
        <a class="down" href="#ra-statement" aria-label="Scroll down">↓</a>
        <div class="credit">Bisbee deportation, 1917 · Bonus Army, 1932 — public domain</div>
    </section>

    {{-- ============ statement ============ --}}
    <section class="ra-statement" id="ra-statement">
        <div class="ra-wrap ra-rev">
            <p class="big ra-caps">Political conviction,<br>made criminal conviction</p>
            <div class="rule"></div>
            <p class="body">For as long as Americans have organized, struck, marched, refused, and dissented, other Americans have used the law to lock them up for it. Sedition acts and criminal-syndicalism laws. Conspiracy counts stretched over picket lines and prayer meetings. The instruments change with the decades; the work they do is constant. The National Political Prisoner Coalition documents that record — every era, every movement, with sources on every case.</p>
            <div class="ra-stats">
                <div class="ra-stat"><div class="n ra-caps">{{ number_format($stats['total']) }}</div><div class="l">Documented political prisoners</div></div>
                <div class="ra-stat"><div class="n ra-caps">250+</div><div class="l">Years of the record</div></div>
                <div class="ra-stat"><div class="n ra-caps">{{ number_format($stats['inCustody']) }}</div><div class="l">In custody right now</div></div>
            </div>
        </div>
    </section>

    {{-- ============ chapters ============ --}}
    <section class="ra-panel" id="ra-record">
        <div class="ra-wrap grid ra-rev">
            <div class="media"><div class="img" style="background-image:url('/images/topics-eras.jpg')"></div></div>
            <div>
                <h2 class="ra-caps">The Record</h2>
                <p class="ra-cap">Every era, every movement — from the Alien and Sedition Acts to this year's protest dockets, told as one continuous history of the laws and campaigns that filled American prisons with dissenters.</p>
                <a class="ra-cta" href="/history"><span class="t">Read the history</span><span class="o">→</span></a>
            </div>
        </div>
    </section>

    <section class="ra-panel flip" id="ra-names">
        <div class="ra-wrap grid ra-rev">
            <div class="media">
                <div class="pgrid" aria-hidden="true">
                    <img src="/images/civic-profile/pp-01.jpg" alt=""><img src="/images/civic-profile/pp-03.jpg" alt=""><img src="/images/civic-profile/pp-08.jpg" alt="">
                    <img src="/images/civic-profile/pp-10.jpg" alt=""><img src="/images/civic-profile/pp-09.jpg" alt=""><img src="/images/civic-profile/pp-04.jpg" alt="">
                </div>
            </div>
            <div>
                <h2 class="ra-caps">The Names</h2>
                <p class="ra-cap">{{ number_format($stats['total']) }} people, each with a page: who they were, what they were charged with, what it cost them, and how to reach the ones still inside.</p>
                <a class="ra-cta" href="/database"><span class="t">Search the database</span><span class="o">→</span></a>
            </div>
        </div>
    </section>

    <section class="ra-panel" id="ra-documents">
        <div class="ra-wrap grid ra-rev">
            <div class="media"><div class="img" style="background-image:url('/images/articles/haymarket-hanging-clipping.jpg'); background-position: center 20%;"></div></div>
            <div>
                <h2 class="ra-caps">The Documents</h2>
                <p class="ra-cap">Prison newspapers, trial records, pamphlets, FBI files — letters smuggled and letters censored. An archive of original documents, digitized and readable in their own ink.</p>
                <a class="ra-cta" href="/archive"><span class="t">Open the archive</span><span class="o">→</span></a>
            </div>
        </div>
    </section>

    <section class="ra-panel flip" id="ra-museum">
        <div class="ra-wrap grid ra-rev">
            <div class="media"><div class="img" style="background-image:url('/storage/history/bonus-army.jpg'), url('/images/articles/iww-deportation-1917.jpg')"></div></div>
            <div>
                <h2 class="ra-caps">The Museum</h2>
                <p class="ra-cap">A walkable 3D museum built live from the database — themed galleries, a timeline corridor, an archive reading room, a theater, and a full-scale solitary cell.</p>
                <a class="ra-cta" href="/museum"><span class="t">Enter the museum</span><span class="o">→</span></a>
            </div>
        </div>
        <div class="ra-peek" aria-hidden="true">
            <img src="/images/civic-profile/pp-02.jpg" alt=""><img src="/images/civic-profile/pp-05.jpg" alt=""><img src="/images/civic-profile/pp-06.jpg" alt="">
            <img src="/images/civic-profile/pp-07.jpg" alt=""><img src="/images/civic-profile/pp-11.jpg" alt=""><img src="/images/civic-profile/pp-12.jpg" alt="">
        </div>
    </section>

    <section class="ra-panel" id="ra-footage">
        <div class="ra-wrap grid ra-rev">
            <div class="media">
                <div class="img" style="background-image:url('/videos/nppc-launch-film-poster.jpg')"></div>
                <span class="play" aria-hidden="true">▶</span>
            </div>
            <div>
                <h2 class="ra-caps">The Footage</h2>
                <p class="ra-cap">The coalition's launch film, the museum's theater program, and the podcast — the moving-image record, collected on one screen.</p>
                <a class="ra-cta" href="/repression-videos"><span class="t">Watch the videos</span><span class="o">→</span></a>
            </div>
        </div>
    </section>

    {{-- ============ get involved ============ --}}
    <section class="ra-involved" id="ra-involved">
        <div class="ra-wrap ra-rev">
            <h2 class="ra-caps">Get Involved</h2>
            <div class="rule"></div>
            <p class="lede">{{ number_format($stats['inCustody']) }} of the people in this database are in custody right now. History looks settled only from a distance — up close, it needs witnesses.</p>
            <div class="ra-links">
                <a href="/prisoner-outreach">Write a letter</a>
                <a href="/petitions">Sign a petition</a>
                <a href="/volunteer">Volunteer</a>
                <a href="/nppc-quiz">Take the quiz</a>
            </div>
            <a class="ra-cta" href="/donate"><span class="t">Support this work</span><span class="o">→</span></a>
        </div>
    </section>

</div>

@verbatim
<script>
(function () {
    // hero photo crossfade
    var phs = document.querySelectorAll('.ra-hero .ph');
    if (phs.length > 1 && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
        var cur = 0;
        setInterval(function () {
            phs[cur].classList.remove('on');
            cur = (cur + 1) % phs.length;
            phs[cur].classList.add('on');
        }, 5200);
    }
    // scroll reveals
    var revs = document.querySelectorAll('.ra-rev');
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (es) {
            es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
        }, { threshold: 0.18 });
        revs.forEach(function (r) { io.observe(r); });
    } else {
        revs.forEach(function (r) { r.classList.add('in'); });
    }
    // smooth-scroll the hero arrow
    var down = document.querySelector('.ra-hero .down');
    if (down) down.addEventListener('click', function (e) {
        var el = document.getElementById('ra-statement');
        if (!el) return;
        e.preventDefault();
        el.scrollIntoView({ behavior: 'smooth' });
    });
})();
</script>
@endverbatim
@endsection
