@extends('app')

@section('title', 'Repression in America — National Political Prisoner Coalition')

@section('head')
    <meta name="description" content="An immersive introduction to the history of political imprisonment in the United States — the record, the names, the documents, and the museum — built from the NPPC's database of more than 7,000 political prisoners.">
    @verbatim
    <style>
        /* Repression in America — immersive documentary hub. Deliberately
           dark in both themes, like the report pages: full-bleed historic
           photography reads as a fixed-palette experience. All imagery is
           our own already-shipped library (1917 Bisbee deportation and 1887
           Haymarket clipping are public-domain historic photographs). */
        .ra { background: #0b0b0e; color: #f4f1ea; font-family: Georgia, 'Times New Roman', serif; }
        .ra * { box-sizing: border-box; }
        .ra-wrap { max-width: 1180px; margin: 0 auto; padding: 0 28px; }
        .ra-eyebrow { font-family: Avenir, Helvetica, sans-serif; font-size: 12px; font-weight: 800; letter-spacing: .26em; text-transform: uppercase; color: #f25c54; margin: 0 0 20px; }
        .ra a.ra-btn {
            display: inline-block; font-family: Avenir, Helvetica, sans-serif; font-weight: 700;
            font-size: 14px; letter-spacing: .08em; text-transform: uppercase; text-decoration: none;
            padding: 15px 34px; border: 1px solid rgba(244,241,234,.5); color: #f4f1ea; transition: background .15s, color .15s, border-color .15s;
        }
        .ra a.ra-btn:hover { background: #f4f1ea; color: #0b0b0e; }
        .ra a.ra-btn--solid { background: #5660fe; border-color: transparent; }
        .ra a.ra-btn--solid:hover { background: #4049d6; color: #fff; }

        /* section dot-nav */
        .ra-dots {
            position: fixed; right: 22px; top: 50%; transform: translateY(-50%); z-index: 40;
            display: flex; flex-direction: column; gap: 14px;
        }
        .ra-dots a { width: 10px; height: 10px; border-radius: 50%; background: rgba(244,241,234,.35); transition: background .15s, transform .15s; }
        .ra-dots a.on { background: #f25c54; transform: scale(1.35); }
        @media (max-width: 900px) { .ra-dots { display: none; } }

        /* hero */
        .ra-hero { position: relative; min-height: 100vh; display: flex; align-items: center; overflow: hidden; }
        .ra-hero .bg {
            position: absolute; inset: 0;
            background: url('/images/articles/iww-deportation-1917.jpg') center 35% / cover no-repeat;
            filter: grayscale(100%) contrast(1.05) brightness(.9);
        }
        .ra-hero .shade { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(11,11,14,.55) 0%, rgba(11,11,14,.35) 45%, rgba(11,11,14,.92) 100%); }
        .ra-hero .inner { position: relative; z-index: 2; width: 100%; padding: 120px 0 90px; }
        .ra-hero h1 { margin: 0; font-weight: 400; font-size: clamp(52px, 9.5vw, 130px); line-height: 1.02; letter-spacing: .01em; }
        .ra-hero h1 em { font-style: italic; color: #ffd9d5; }
        .ra-hero .sub { font-family: Avenir, Helvetica, sans-serif; max-width: 620px; font-size: clamp(16px, 2vw, 19px); line-height: 1.65; color: rgba(244,241,234,.82); margin: 28px 0 0; }
        .ra-hero .credit { position: absolute; bottom: 18px; left: 0; right: 0; text-align: center; font-family: Avenir, Helvetica, sans-serif; font-size: 11.5px; letter-spacing: .04em; color: rgba(244,241,234,.45); }
        .ra-scroll { position: absolute; bottom: 54px; left: 50%; transform: translateX(-50%); color: rgba(244,241,234,.7); font-family: Avenir, sans-serif; font-size: 12px; letter-spacing: .2em; text-transform: uppercase; }
        .ra-scroll::after { content: ""; display: block; width: 1px; height: 34px; background: rgba(244,241,234,.5); margin: 10px auto 0; }

        /* statement */
        .ra-statement { padding: 130px 0; border-bottom: 1px solid rgba(244,241,234,.12); }
        .ra-statement .big { font-size: clamp(26px, 4.2vw, 46px); line-height: 1.28; max-width: 980px; margin: 0 0 30px; font-weight: 400; }
        .ra-statement .big em { font-style: italic; color: #ffd9d5; }
        .ra-statement .body { font-family: Avenir, Helvetica, sans-serif; column-gap: 44px; max-width: 900px; color: rgba(244,241,234,.78); font-size: 16.5px; line-height: 1.75; }
        .ra-stats { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 26px; margin-top: 60px; max-width: 900px; }
        .ra-stat .n { font-size: clamp(38px, 5vw, 60px); color: #8b93ff; line-height: 1; }
        .ra-stat .l { font-family: Avenir, Helvetica, sans-serif; font-size: 13.5px; letter-spacing: .1em; text-transform: uppercase; color: rgba(244,241,234,.6); margin-top: 10px; }
        @media (max-width: 720px) { .ra-stats { grid-template-columns: 1fr; } }

        /* half panels */
        .ra-panel { display: grid; grid-template-columns: 1fr 1fr; min-height: 92vh; border-bottom: 1px solid rgba(244,241,234,.12); }
        .ra-panel .media { position: relative; min-height: 46vh; overflow: hidden; }
        .ra-panel .media .img { position: absolute; inset: 0; background-size: cover; background-position: center; filter: grayscale(100%) contrast(1.04); transition: transform .6s ease; }
        .ra-panel:hover .media .img { transform: scale(1.03); }
        .ra-panel .media .grid { position: absolute; inset: 0; display: grid; grid-template-columns: repeat(3, 1fr); }
        .ra-panel .media .grid img { width: 100%; height: 100%; object-fit: cover; filter: grayscale(100%); display: block; }
        .ra-panel .copy { display: flex; flex-direction: column; justify-content: center; padding: 76px 64px; }
        .ra-panel .copy .n { font-family: Avenir, sans-serif; color: #f25c54; font-weight: 800; letter-spacing: .22em; font-size: 12px; margin-bottom: 16px; }
        .ra-panel h2 { margin: 0 0 18px; font-weight: 400; font-size: clamp(34px, 4.6vw, 58px); line-height: 1.05; }
        .ra-panel p { font-family: Avenir, Helvetica, sans-serif; margin: 0 0 30px; color: rgba(244,241,234,.75); font-size: 16.5px; line-height: 1.7; max-width: 480px; }
        .ra-panel.flip .media { order: 2; }
        @media (max-width: 900px) {
            .ra-panel { grid-template-columns: 1fr; min-height: 0; }
            .ra-panel.flip .media { order: 0; }
            .ra-panel .copy { padding: 48px 28px 64px; }
        }

        /* involved */
        .ra-involved { padding: 120px 0 110px; }
        .ra-involved h2 { font-weight: 400; font-size: clamp(34px, 5vw, 62px); margin: 0 0 18px; }
        .ra-involved .lede { font-family: Avenir, sans-serif; color: rgba(244,241,234,.75); font-size: 17px; line-height: 1.65; max-width: 640px; margin: 0 0 44px; }
        .ra-acts { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 16px; margin-bottom: 46px; }
        .ra-act { border: 1px solid rgba(244,241,234,.18); padding: 24px 22px; text-decoration: none; color: #f4f1ea; transition: border-color .15s, background .15s; }
        .ra-act:hover { border-color: #5660fe; background: rgba(86,96,254,.08); }
        .ra-act strong { display: block; font-family: Avenir, sans-serif; font-size: 16px; margin-bottom: 8px; }
        .ra-act span { font-family: Avenir, sans-serif; font-size: 13.5px; color: rgba(244,241,234,.6); line-height: 1.5; }
        @media (max-width: 900px) { .ra-acts { grid-template-columns: repeat(2, minmax(0,1fr)); } }
        @media (max-width: 560px) { .ra-acts { grid-template-columns: 1fr; } }
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

    <nav class="ra-dots" aria-label="Page sections">
        <a href="#ra-top" aria-label="Top"></a>
        <a href="#ra-statement" aria-label="Statement"></a>
        <a href="#ra-record" aria-label="The record"></a>
        <a href="#ra-names" aria-label="The names"></a>
        <a href="#ra-documents" aria-label="The documents"></a>
        <a href="#ra-museum" aria-label="The museum"></a>
        <a href="#ra-involved" aria-label="Get involved"></a>
    </nav>

    {{-- ============ hero ============ --}}
    <section class="ra-hero" id="ra-top">
        <div class="bg" role="img" aria-label="Deported striking miners marched out of Bisbee, Arizona under guard, July 12, 1917"></div>
        <div class="shade"></div>
        <div class="ra-wrap inner">
            <p class="ra-eyebrow">A National Political Prisoner Coalition introduction</p>
            <h1>Repression<br><em>in America</em></h1>
            <p class="sub">On July 12, 1917, more than a thousand striking miners were marched out of Bisbee, Arizona at gunpoint and abandoned in the desert. Their crime was a union card. This site documents them — and seven thousand others.</p>
        </div>
        <div class="ra-scroll">Scroll</div>
        <div class="credit">Deportation of striking IWW miners, Bisbee, Arizona, July 12, 1917 — public domain</div>
    </section>

    {{-- ============ statement ============ --}}
    <section class="ra-statement" id="ra-statement">
        <div class="ra-wrap">
            <p class="ra-eyebrow">The premise</p>
            <p class="big">For as long as Americans have organized, struck, marched, refused, and dissented, other Americans have used the law to <em>lock them up for it.</em></p>
            <div class="body">
                <p>Sedition acts and criminal-syndicalism laws. Conspiracy counts stretched over picket lines and prayer meetings. Draft boards, loyalty boards, grand juries, and gang statutes. The instruments change with the decades, but the work they do is constant: they turn political conviction into criminal conviction. The National Political Prisoner Coalition documents that record — every era, every movement, left and right, famous and forgotten — with sources on every case.</p>
            </div>
            <div class="ra-stats">
                <div class="ra-stat"><div class="n">{{ number_format($stats['total']) }}</div><div class="l">Documented political prisoners</div></div>
                <div class="ra-stat"><div class="n">250+</div><div class="l">Years of the record, colonial era to today</div></div>
                <div class="ra-stat"><div class="n">{{ number_format($stats['inCustody']) }}</div><div class="l">In custody right now</div></div>
            </div>
        </div>
    </section>

    {{-- ============ panels ============ --}}
    <section class="ra-panel" id="ra-record">
        <div class="media"><div class="img" style="background-image:url('/images/topics-eras.jpg')"></div></div>
        <div class="copy">
            <div class="n">01 — The Record</div>
            <h2>Every era. Every movement.</h2>
            <p>From the Alien and Sedition Acts to this year's protest dockets: the eras, the laws, and the campaigns of repression that connect them, told as one continuous history.</p>
            <div><a class="ra-btn" href="/history">Read the history</a></div>
        </div>
    </section>

    <section class="ra-panel flip" id="ra-names">
        <div class="media">
            <div class="grid" aria-hidden="true">
                <img src="/images/civic-profile/pp-01.jpg" alt=""><img src="/images/civic-profile/pp-03.jpg" alt=""><img src="/images/civic-profile/pp-08.jpg" alt="">
                <img src="/images/civic-profile/pp-10.jpg" alt=""><img src="/images/civic-profile/pp-09.jpg" alt=""><img src="/images/civic-profile/pp-04.jpg" alt="">
            </div>
        </div>
        <div class="copy">
            <div class="n">02 — The Names</div>
            <h2>{{ number_format($stats['total']) }} people, each with a record.</h2>
            <p>Not a statistic — a database. Every person has a page: who they were, what they were charged with, what it cost them, and how to reach the ones still inside.</p>
            <div><a class="ra-btn" href="/database">Search the database</a></div>
        </div>
    </section>

    <section class="ra-panel" id="ra-documents">
        <div class="media"><div class="img" style="background-image:url('/images/articles/haymarket-hanging-clipping.jpg'); background-position: center 20%;"></div></div>
        <div class="copy">
            <div class="n">03 — The Documents</div>
            <h2>Read it in their own ink.</h2>
            <p>Prison newspapers, trial records, pamphlets, FBI files, letters smuggled and letters censored — an archive of original documents, digitized and readable.</p>
            <div><a class="ra-btn" href="/archive">Open the archive</a></div>
        </div>
    </section>

    <section class="ra-panel flip" id="ra-museum">
        <div class="media"><div class="img" style="background-image:url('/storage/history/bonus-army.jpg'), url('/images/articles/iww-deportation-1917.jpg')"></div></div>
        <div class="copy">
            <div class="n">04 — The Museum</div>
            <h2>Walk through it.</h2>
            <p>A 3D museum built live from the database: themed galleries, a timeline corridor, an archive reading room, a theater, and a full-scale solitary cell.</p>
            <div><a class="ra-btn" href="/museum">Enter the museum</a></div>
        </div>
    </section>

    {{-- ============ get involved ============ --}}
    <section class="ra-involved" id="ra-involved">
        <div class="ra-wrap">
            <p class="ra-eyebrow">What you can do</p>
            <h2>The record is still being written.</h2>
            <p class="lede">{{ number_format($stats['inCustody']) }} of the people in this database are in custody right now. History looks settled only from a distance — up close, it needs witnesses.</p>
            <div class="ra-acts">
                <a class="ra-act" href="/prisoner-outreach"><strong>Write a letter</strong><span>Reach someone inside this week.</span></a>
                <a class="ra-act" href="/petitions"><strong>Sign a petition</strong><span>Active campaigns for clemency and release.</span></a>
                <a class="ra-act" href="/volunteer"><strong>Volunteer</strong><span>Research, court support, events, and more.</span></a>
                <a class="ra-act" href="/nppc-quiz"><strong>Take the quiz</strong><span>Where do you stand? Five minutes.</span></a>
            </div>
            <a class="ra-btn ra-btn--solid" href="/donate">Support this work</a>
        </div>
    </section>

</div>

@verbatim
<script>
(function () {
    // scrollspy for the dot nav
    var dots = document.querySelectorAll('.ra-dots a');
    var ids = Array.prototype.map.call(dots, function (a) { return a.getAttribute('href').slice(1); });
    var sections = ids.map(function (id) { return document.getElementById(id); });
    function spy() {
        var y = window.scrollY + window.innerHeight * 0.4;
        var on = 0;
        sections.forEach(function (s, i) { if (s && s.offsetTop <= y) on = i; });
        dots.forEach(function (d, i) { d.classList.toggle('on', i === on); });
    }
    document.addEventListener('scroll', spy, { passive: true });
    dots.forEach(function (d) {
        d.addEventListener('click', function (e) {
            var el = document.getElementById(d.getAttribute('href').slice(1));
            if (!el) return;
            e.preventDefault();
            el.scrollIntoView({ behavior: 'smooth' });
        });
    });
    spy();
})();
</script>
@endverbatim
@endsection
