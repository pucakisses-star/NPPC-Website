@extends('app')

@section('title', 'Report 2026 — The Whole World Is Watching | NPPC')

@section('meta_description')The NPPC fiscal year 2026 annual report: the year of the mass docket — 511 cases added, juries pushing back, the Broadview Six dismissed with prejudice, and a census that watched everything.@endsection

@section('og_image'){{ asset('storage/history/bonus-army.jpg') }}@endsection

@section('head')
<style>
/* ============================================================
   Report 2026 (FY26) — interactive annual-report microsite.
   Photo hero, scrollytelling story galleries, a memorial band,
   a five-case docket and six-state series as parallax panels,
   video interstitial, animated financial bars, scrollable donor
   and staff cards. Vanilla JS only.
   ============================================================ */
body.page-report-2026 main.container,
body.page-report-2026 .container { max-width: none !important; padding-left: 0 !important; padding-right: 0 !important; }
body.page-report-2026 { background: #0a0a12; }

.r26 { --ink: #ececf2; --dim: rgba(236,236,242,0.62); --acc: #5660fe; --acc2: #8f97ff;
       --paper: #f0f1f7; --deep: #0a0a12; --navy: #12122a;
       color: var(--ink); font-size: 16px; line-height: 1.7; overflow-x: clip; }
.r26-wrap { max-width: 1180px; margin: 0 auto; padding: 0 28px; }
.r26-narrow { max-width: 860px; margin: 0 auto; padding: 0 28px; }

.rv { opacity: 0; transform: translateY(34px); transition: opacity .9s ease, transform .9s cubic-bezier(.22,1,.36,1); }
.rv.rv-fade { transform: none; }
.rv.rv-right { transform: translateX(-40px); }
.rv.in { opacity: 1; transform: none; }
.rv.d2 { transition-delay: .25s; } .rv.d3 { transition-delay: .5s; } .rv.d4 { transition-delay: .8s; }
@media (prefers-reduced-motion: reduce) { .rv { opacity: 1 !important; transform: none !important; transition: none; } }

.r26-label { display: inline-block; font-size: 12px; font-weight: 800; letter-spacing: .22em; text-transform: uppercase; color: var(--acc2); margin-bottom: 18px; }
.r26-title { font-size: clamp(2rem, 4.2vw, 3.2rem); font-weight: 900; line-height: 1.08; color: var(--ink); margin: 0 0 22px; letter-spacing: -.015em; }
.r26-lede { font-size: clamp(1.05rem, 1.6vw, 1.3rem); color: rgba(236,236,242,.85); max-width: 56ch; }

.r26-cue { display: inline-flex; flex-direction: column; align-items: center; gap: 12px; text-decoration: none; }
.r26-cue .h6 { font-size: 11px; letter-spacing: .26em; text-transform: uppercase; color: rgba(255,255,255,.72); font-weight: 700; }
.r26-cue .circ { width: 44px; height: 44px; border-radius: 50%; background: var(--acc); display: flex; align-items: center; justify-content: center; animation: r26Bob 2.4s ease-in-out infinite; }
.r26-cue .circ::after { content: ''; width: 10px; height: 10px; border-right: 2px solid #fff; border-bottom: 2px solid #fff; transform: rotate(45deg) translate(-1px,-1px); }
@keyframes r26Bob { 0%,100% { transform: translateY(0); } 50% { transform: translateY(8px); } }
@media (prefers-reduced-motion: reduce) { .r26-cue .circ { animation: none; } }

/* ── hero: full-bleed photo, left-aligned title ──────────── */
.r26-hero { position: relative; min-height: 100vh; display: flex; align-items: flex-end; }
.r26-hero-bg { position: absolute; inset: 0; background: url('/storage/history/bonus-army.jpg') center 30% / cover no-repeat;
  filter: grayscale(70%) brightness(.42) contrast(1.05); }
.r26-hero-shade { position: absolute; inset: 0; background: linear-gradient(200deg, rgba(10,10,18,.2), rgba(10,10,18,.55) 55%, rgba(10,10,18,.95)); }
.r26-hero-body { position: relative; z-index: 2; padding: 0 0 11vh; width: 100%; }
.r26-hero h1 { font-size: clamp(3rem, 8.5vw, 7rem); font-weight: 900; line-height: 1.02; color: #fff; margin: 0 0 40px; letter-spacing: -.025em; max-width: 12ch; }
.r26-hero h1 em { font-style: normal; color: var(--acc2); }

/* ── director panel ──────────────────────────────────────── */
.r26-director { background: var(--navy); padding: 100px 0; }
.r26-director .grid { display: grid; grid-template-columns: 1.1fr 1fr; gap: 60px; align-items: center; }
.r26-director video { width: 100%; border-radius: 10px; box-shadow: 0 30px 80px rgba(0,0,0,.55); display: block; }
.r26-director p { color: rgba(236,236,242,.85); }
@media (max-width: 860px) { .r26-director .grid { grid-template-columns: 1fr; gap: 34px; } }

/* ── letter / intro panels ───────────────────────────────── */
.r26-sect { padding: 110px 0 90px; }
.r26-sect p { color: rgba(236,236,242,.85); }
.r26-caption { font-size: 12.5px; color: var(--dim); margin-top: 12px; }

/* ── story pinned intro ──────────────────────────────────── */
.r26-fintro { position: relative; min-height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; overflow: hidden; }
.r26-fintro-bg { position: absolute; inset: -10% 0; background: center 20% / cover no-repeat; filter: grayscale(35%) brightness(.32); will-change: transform; }
.r26-fintro-body { position: relative; z-index: 2; padding: 60px 24px; }
.r26-fintro h2 { font-size: clamp(2.4rem, 6vw, 4.8rem); font-weight: 900; color: #fff; margin: 0 0 26px; letter-spacing: -.02em; }
.r26-fintro-meta { display: flex; gap: 44px; justify-content: center; margin-bottom: 50px; flex-wrap: wrap; }
.r26-fintro-meta span { font-size: clamp(.95rem, 1.5vw, 1.15rem); font-weight: 700; color: var(--acc2); line-height: 1.5; }
.r26-fintro-meta span em { display: block; font-style: normal; font-weight: 400; font-size: 12px; letter-spacing: .18em; text-transform: uppercase; color: rgba(255,255,255,.6); margin-bottom: 4px; }

/* ── scrollytelling gallery ──────────────────────────────── */
.r26-sg-grid { display: grid; grid-template-columns: 1fr 1fr; }
.r26-sg-fig { position: sticky; top: 0; height: 100vh; overflow: hidden; }
.r26-sg-fig img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity .7s ease; }
.r26-sg-fig img.on { opacity: 1; }
.r26-step { min-height: 92vh; display: flex; align-items: center; padding: 12vh 6vw; }
.r26-step-card { border-radius: 10px; padding: 40px 38px; max-width: 480px; transition: transform .5s cubic-bezier(.22,1,.36,1), box-shadow .5s; }
.r26-step.on .r26-step-card { transform: translateY(-6px); box-shadow: 0 30px 70px rgba(0,0,0,.45); }
.r26-step-card p { margin: 0; font-size: 16.5px; line-height: 1.75; }
.r26-step-card .p2 { font-size: clamp(1.25rem, 2vw, 1.6rem); font-weight: 800; line-height: 1.45; }
.r26-step-card a { color: inherit; text-underline-offset: 3px; }
.r26-c1 { background: #d9dcff; color: #14142b; }
.r26-c2 { background: #ece7d8; color: #1d1b12; }
.r26-c3 { background: #23281c; color: #e9ecdf; }
.r26-c4 { background: #101a3c; color: #dfe4f7; }
@media (max-width: 860px) {
  .r26-sg-grid { grid-template-columns: 1fr; }
  .r26-sg-fig { height: 46vh; }
  .r26-step { min-height: auto; padding: 26px 20px; }
  .r26-step-card { max-width: none; }
}

/* ── CTA bands ───────────────────────────────────────────── */
.r26-cta { padding: 84px 0; }
.r26-cta.is-acc { background: linear-gradient(120deg, #262a6e, var(--acc)); }
.r26-cta.is-black { background: #050508; }
.r26-cta h3 { font-size: clamp(1.4rem, 2.6vw, 2.1rem); font-weight: 900; color: #fff; margin: 0 0 26px; max-width: 36ch; }
.r26-btn { display: inline-block; padding: 14px 30px; border-radius: 4px; background: #fff; color: #14142b; font-weight: 800; font-size: 14px; letter-spacing: .06em; text-transform: uppercase; text-decoration: none; margin-right: 14px; margin-bottom: 10px; }
.r26-btn.ghost { background: transparent; color: #fff; border: 2px solid rgba(255,255,255,.6); }
.r26-btn:hover { opacity: .9; }

/* ── memorial band ───────────────────────────────────────── */
.r26-memorial { position: relative; min-height: 88vh; display: flex; align-items: center; overflow: hidden; }
.r26-memorial-bg { position: absolute; inset: -10% 0; background: url('/images/candles.jpg') center / cover no-repeat; filter: brightness(.3) saturate(.7); will-change: transform; }
.r26-memorial-body { position: relative; z-index: 2; text-align: center; max-width: 780px; margin: 0 auto; padding: 90px 28px; }
.r26-memorial h2 { font-size: clamp(1.9rem, 4vw, 3rem); font-weight: 900; color: #fff; margin: 0 0 22px; line-height: 1.15; }
.r26-memorial p { color: rgba(236,236,242,.85); font-size: 17px; }

/* ── docket & state panels (parallax) ────────────────────── */
.r26-case { position: relative; min-height: 92vh; display: flex; align-items: center; overflow: hidden; padding: 80px 0; }
.r26-case-bg { position: absolute; inset: -12% 0; background: center / cover no-repeat; filter: grayscale(60%) brightness(.28); will-change: transform; }
.r26-case-card { position: relative; z-index: 2; background: rgba(10,10,18,.78); border: 1px solid rgba(236,236,242,.12); border-radius: 12px; padding: 46px 44px; max-width: 640px; backdrop-filter: blur(4px); }
.r26-case-kicker { font-size: 12px; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; color: var(--acc2); margin-bottom: 14px; }
.r26-case-card h3 { font-size: clamp(1.5rem, 3vw, 2.3rem); font-weight: 900; color: #fff; margin: 0 0 6px; line-height: 1.15; }
.r26-case-card h3 i { font-style: italic; }
.r26-case-sub { font-size: 13px; color: var(--dim); margin-bottom: 16px; }
.r26-case-card p { color: rgba(236,236,242,.85); margin: 0 0 14px; font-size: 15.5px; }
.r26-case-card a { color: var(--acc2); }

/* ── video / image interstitials ─────────────────────────── */
.r26-inter { position: relative; min-height: 78vh; display: flex; align-items: center; overflow: hidden; background: #000; }
.r26-inter video, .r26-inter .r26-inter-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: .34; }
.r26-inter .r26-inter-img { background: center / cover no-repeat; }
.r26-inter-body { position: relative; z-index: 2; max-width: 820px; margin: 0 auto; padding: 90px 28px; text-align: center; }
.r26-inter-body p { font-size: clamp(1.2rem, 2.4vw, 1.8rem); font-weight: 800; color: #fff; line-height: 1.5; margin: 0; }

/* ── movement panels ─────────────────────────────────────── */
.r26-panel { padding: 96px 0; }
.r26-panel.alt { background: var(--navy); }
.r26-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
.r26-2col img { width: 100%; border-radius: 8px; box-shadow: 0 24px 70px rgba(0,0,0,.5); }
.r26-panel p { color: rgba(236,236,242,.82); }
.r26-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-top: 44px; }
.r26-stat-n { font-size: clamp(2rem, 4vw, 3.2rem); font-weight: 900; color: var(--acc2); line-height: 1; }
.r26-stat-l { margin-top: 8px; font-size: 13px; color: var(--dim); line-height: 1.5; }
@media (max-width: 860px) { .r26-2col { grid-template-columns: 1fr; gap: 34px; } .r26-stats { grid-template-columns: 1fr; } }

/* ── ways to give ────────────────────────────────────────── */
.r26-ways { display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px; margin-top: 46px; }
.r26-way { background: rgba(236,236,242,.045); border: 1px solid rgba(236,236,242,.09); border-radius: 10px; padding: 26px 22px; text-decoration: none; transition: border-color .2s, transform .2s; }
.r26-way:hover { border-color: rgba(86,96,254,.5); transform: translateY(-3px); }
.r26-way h4 { font-size: 15.5px; font-weight: 800; color: var(--ink); margin: 0 0 8px; }
.r26-way p { font-size: 13px; color: var(--dim); margin: 0; line-height: 1.6; }
@media (max-width: 860px) { .r26-ways { grid-template-columns: 1fr 1fr; } }

/* ── financials ──────────────────────────────────────────── */
.r26-fin { background: var(--paper); color: #14142b; padding: 110px 0; }
.r26-fin .r26-label { color: var(--acc); }
.r26-fin h2 { color: #14142b; }
.r26-fin-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 44px; margin-top: 54px; }
.r26-fin-col h3 { font-size: 1.15rem; font-weight: 900; color: #14142b; margin: 0 0 20px; }
.r26-fin-row { padding: 11px 0; border-bottom: 1px solid rgba(20,20,43,.12); }
.r26-fin-row .top { display: flex; justify-content: space-between; gap: 12px; font-size: 14px; }
.r26-fin-row .top b { font-weight: 700; color: #14142b; }
.r26-fin-row .top span { color: rgba(20,20,43,.7); white-space: nowrap; }
.r26-fin-row .pct { font-size: 11.5px; font-weight: 800; color: var(--acc); margin-top: 5px; }
.r26-bar { height: 4px; background: rgba(20,20,43,.1); border-radius: 2px; margin-top: 6px; overflow: hidden; }
.r26-bar i { display: block; height: 100%; width: 0; background: var(--acc); border-radius: 2px; transition: width 1.1s cubic-bezier(.22,1,.36,1); }
.r26-fin-row.tot { border-bottom: 0; padding-top: 16px; }
.r26-fin-row.tot .top b, .r26-fin-row.tot .top span { font-weight: 900; font-size: 15px; color: #14142b; }
@media (max-width: 900px) { .r26-fin-grid { grid-template-columns: 1fr; } }

/* ── scrollable list cards ───────────────────────────────── */
.r26-lists { padding: 110px 0; }
.r26-hint { font-size: 12px; letter-spacing: .18em; text-transform: uppercase; color: var(--dim); margin-bottom: 34px; }
.r26-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px; }
.r26-cards.three { grid-template-columns: repeat(3, 1fr); }
.r26-card { background: rgba(236,236,242,.04); border: 1px solid rgba(236,236,242,.1); border-radius: 10px; padding: 26px 24px; height: 460px; overflow-y: auto; }
.r26-card::-webkit-scrollbar { width: 6px; } .r26-card::-webkit-scrollbar-thumb { background: rgba(236,236,242,.2); border-radius: 3px; }
.r26-card h4 { font-size: 15px; font-weight: 900; color: var(--acc2); margin: 0 0 6px; }
.r26-card h4 + h4 { margin-top: 26px; }
.r26-card .who { font-size: 14px; color: rgba(236,236,242,.85); padding: 6px 0; border-bottom: 1px solid rgba(236,236,242,.07); }
.r26-card .who small { display: block; font-size: 12px; color: var(--dim); }
@media (max-width: 1000px) { .r26-cards, .r26-cards.three { grid-template-columns: 1fr 1fr; } }
@media (max-width: 640px) { .r26-cards, .r26-cards.three { grid-template-columns: 1fr; } .r26-card { height: 380px; } }

/* ── from our donors (tribute) ───────────────────────────── */
.r26-tribute { background: var(--navy); padding: 100px 0; }
.r26-tribute p { color: rgba(236,236,242,.85); max-width: 720px; margin: 0 0 18px; font-size: 16.5px; }
.r26-tribute .name { font-size: clamp(1.5rem, 2.8vw, 2.2rem); font-weight: 900; color: var(--ink); margin-bottom: 4px; }
.r26-tribute .dates { font-size: 13px; color: var(--dim); margin-bottom: 26px; }

/* ── thank you ───────────────────────────────────────────── */
.r26-thanks { padding: 130px 0 90px; text-align: center; background: radial-gradient(ellipse at 50% 30%, #1c1c46, var(--deep)); }
.r26-thanks h2 { font-size: clamp(3rem, 8vw, 5.5rem); font-weight: 900; color: #fff; margin: 0 0 24px; }
.r26-thanks p { color: var(--dim); max-width: 60ch; margin: 0 auto 34px; }
.r26-credits { margin-top: 80px; font-size: 12px; color: rgba(236,236,242,.4); max-width: 900px; margin-left: auto; margin-right: auto; line-height: 1.8; text-align: left; }
</style>
@endsection

@section('body')
<div class="r26">

    {{-- HERO --}}
    <section class="r26-hero" id="hero">
        <div class="r26-hero-bg"></div>
        <div class="r26-hero-shade"></div>
        <div class="r26-wrap r26-hero-body">
            <span class="r26-label rv in">National Political Prisoner Coalition &middot; Fiscal Year 2026 Annual Report</span>
            <h1 class="rv rv-right in">The Whole World <em>Is Watching.</em></h1>
            <a href="#director" class="r26-cue rv d3 in">
                <span class="h6">Scroll to Begin</span>
                <span class="circ"></span>
            </a>
        </div>
    </section>

    {{-- DIRECTOR / FILM PANEL --}}
    <section class="r26-director" id="director">
        <div class="r26-wrap grid">
            <div class="rv">
                <video controls preload="metadata" playsinline poster="/videos/nppc-launch-film-poster.jpg">
                    <source src="/videos/nppc-launch-film.mp4" type="video/mp4">
                </video>
            </div>
            <div>
                <span class="r26-label rv">Hear from the Coordinating Committee</span>
                <h2 class="r26-title rv" style="font-size: clamp(1.7rem, 3.2vw, 2.5rem);">In a year this loud,
                somebody has to keep the ledger.</h2>
                <p class="rv d2">Fiscal year 2026 &mdash; and with this edition, the coalition moves to fiscal-year
                reporting &mdash; ran from July 2025 through June 2026: the deployments, the designations, the mass
                arrests on the Mall as the year closed, and, again and again, juries and judges refusing to go
                along. Through all of it we did the one thing the census exists to do. We watched, and we wrote
                everything down.</p>
                <p class="rv d3">511 cases entered the record this year &mdash; the most ever &mdash; and 104
                entries were marked closed by acquittal, dismissal, pardon, or completed sentence.</p>
            </div>
        </div>
    </section>

    {{-- RESTORING FREEDOM INTRO --}}
    <section class="r26-sect" id="restoring-freedom-intro">
        <div class="r26-narrow">
            <span class="r26-label rv">Restoring Freedom</span>
            <h2 class="r26-title rv">The year the juries said no</h2>
            <p class="r26-lede rv">In the face of the most volatile enforcement environment the census has ever
            recorded, the machinery met an old obstacle: ordinary people asked to go along with it. Grand juries
            declined indictments. Trial juries acquitted. Judges dismissed with prejudice. Our intake queue passed
            2,400 submissions this year, and our readers verified every closure against the record. Three of those
            stories follow.</p>
            <p class="r26-caption rv d2">Below: Sean Dunn outside the federal courthouse in Washington; Larry
            Bushart at home in Tennessee; the Broadview Six after dismissal in Chicago.</p>
        </div>
    </section>

    @php
    $stories = [
        [
            'id' => 'sean-dunn', 'name' => 'Sean Dunn',
            'meta1' => ['Acquitted', 'November 2025'], 'meta2' => ['Grand juries', 'Declined twice'],
            'bg' => '/storage/prisoners/sean-dunn.png',
            'images' => ['/storage/prisoners/sean-dunn.png', '/storage/history/coxeys-army.jpg',
                         '/storage/petitions/end-espionage-act-prosecutions-of-journalists.jpg',
                         '/storage/history/bonus-army.jpg'],
            'steps' => [
                ['c1', 'p2', 'He threw a sandwich. The government wanted a felony. The city said no &mdash;
                    three separate times.'],
                ['c2', 'p3', '<a href="/prisoner/sean-dunn">Sean Dunn</a>, a Justice Department paralegal, was
                    arrested during the August 2025 federal deployment to Washington after throwing a Subway
                    sandwich at a CBP agent on a downtown protective line. He was fired within days, and
                    prosecutors sought a felony assault indictment.'],
                ['c3', 'p3', 'A DC grand jury declined to indict. Prosecutors tried again; the second grand jury
                    declined too &mdash; a refusal so rare that veteran defense lawyers struggled to name a
                    precedent. The government refiled the charge as a misdemeanor and took it to trial anyway.'],
                ['c4', 'p3', 'In November 2025 a jury acquitted him in hours. The &ldquo;sandwich trial&rdquo;
                    became the emblem of the year&rsquo;s pattern: when the deployment docket met ordinary
                    citizens, the citizens kept saying no. His entry &mdash; opened, contested, and closed within
                    96 days &mdash; is preserved in full in the census.'],
            ],
        ],
        [
            'id' => 'larry-bushart', 'name' => 'Larry Bushart',
            'meta1' => ['Case dismissed', 'Fall 2025'], 'meta2' => ['Held on', '$2 million bond'],
            'bg' => '/storage/prisoners/larry-bushart.png',
            'images' => ['/storage/prisoners/larry-bushart.png', '/storage/history/smith-act-trials.jpg',
                         '/storage/history/whistleblowers.jpg',
                         '/storage/petitions/restore-physical-mail-prisons.jpg'],
            'steps' => [
                ['c1', 'p2', 'Thirty-four years a cop. Twenty-four in the Guard. Jailed on a two-million-dollar
                    bond &mdash; for sharing a meme.'],
                ['c2', 'p3', '<a href="/prisoner/larry-bushart">Larry Bushart</a>, a retired Tennessee law
                    enforcement officer, was arrested in September 2025 in the crackdown that followed the killing
                    of Charlie Kirk. The charge was built on a Facebook meme he did not create &mdash; a photo of
                    the president and a quotation of the president&rsquo;s own words about a school shooting.'],
                ['c3', 'p3', 'A local judge set bond at $2 million &mdash; higher than many homicide defendants in
                    the same county &mdash; and Mr. Bushart sat in the Perry County jail while the case against
                    him was read, reread, and finally understood to be a man sharing a quotation.'],
                ['c4', 'p3', 'The case collapsed and he went home to his family. His entry documents the
                    speech-crackdown cohort of autumn 2025 &mdash; teachers, nurses, a firefighter, and one
                    retired cop &mdash; each arrested over a post, each now cross-referenced in the census under
                    the same tag: <em>speech, retaliatory prosecution</em>.'],
            ],
        ],
        [
            'id' => 'broadview-six', 'name' => 'The Broadview Six',
            'meta1' => ['Dismissed with prejudice', 'May 2026'], 'meta2' => ['Reason', 'Grand-jury misconduct'],
            'bg' => '/storage/prisoners/kat-abughazaleh.jpg',
            'images' => ['/storage/prisoners/kat-abughazaleh.jpg', '/storage/prisoners/david-huerta.jpg',
                         '/storage/history/bonus-army.jpg', '/storage/history/stop-cop-city.jpg'],
            'steps' => [
                ['c1', 'p2', 'Six people charged over a protest outside an ICE processing center. In May, a
                    federal judge threw out every count &mdash; permanently.'],
                ['c2', 'p3', '<a href="/prisoner/kat-abughazaleh">Kat Abughazaleh</a>, a journalist and candidate
                    for Congress, was federally charged with five co-defendants after a September 2025
                    confrontation at the Broadview ICE Processing Center outside Chicago, during the federal
                    deployment known as Operation Midway Blitz.'],
                ['c3', 'p3', 'The prosecution unraveled in discovery: in May 2026 the court dismissed all charges
                    <em>with prejudice</em> after the disclosure of grand-jury misconduct by federal prosecutors.
                    The Broadview Six cannot be recharged. Their six entries are marked closed &mdash;
                    &ldquo;dismissed, government misconduct&rdquo; &mdash; a closure category we created this year
                    and hope never to retire.'],
                ['c4', 'p3', 'They were not alone. From the LA deployment arrest of union president
                    <a href="/prisoner/david-huerta">David Huerta</a> to the reflecting-pool mass arrests on the
                    National Mall in the fiscal year&rsquo;s final week, the mass docket defined FY26 &mdash; and
                    the census documented every name on it. As this report went to press, the July docket was
                    still growing.'],
            ],
        ],
    ];
    @endphp

    @foreach ($stories as $s)
        <section class="r26-fintro" id="{{ $s['id'] }}">
            <div class="r26-fintro-bg" data-parallax style="background-image: url('{{ $s['bg'] }}')"></div>
            <div class="r26-hero-shade"></div>
            <div class="r26-fintro-body">
                <h2 class="rv">{!! $s['name'] !!}</h2>
                <div class="r26-fintro-meta rv d2">
                    <span><em>{{ $s['meta1'][0] }}</em>{{ $s['meta1'][1] }}</span>
                    <span><em>{{ $s['meta2'][0] }}</em>{{ $s['meta2'][1] }}</span>
                </div>
                <span class="r26-cue rv d3"><span class="h6">Scroll to Read the Story</span><span class="circ"></span></span>
            </div>
        </section>
        <section data-gallery>
            <div class="r26-sg-grid">
                <div class="r26-sg-fig">
                    @foreach ($s['images'] as $i => $img)
                        <img src="{{ $img }}" alt="" loading="lazy" data-image="{{ $i + 1 }}" class="{{ $i === 0 ? 'on' : '' }}">
                    @endforeach
                </div>
                <div class="r26-sg-steps">
                    @foreach ($s['steps'] as $i => [$color, $size, $text])
                        <div class="r26-step" data-step="{{ $i + 1 }}">
                            <div class="r26-step-card r26-{{ $color }}">
                                <p class="{{ $size }}">{!! $text !!}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endforeach

    {{-- CTA 1 --}}
    <section class="r26-cta is-acc" id="donate-section-1">
        <div class="r26-wrap">
            <h3 class="rv">511 new entries this year. Each one costs about $11 to document &mdash; and stays free to read forever.</h3>
            <div class="rv d2">
                <a class="r26-btn" href="/donate">Donate</a>
                <a class="r26-btn ghost" href="/donate">Give Monthly</a>
            </div>
        </div>
    </section>

    {{-- MEMORIAL --}}
    <section class="r26-memorial" id="memorial">
        <div class="r26-memorial-bg" data-parallax></div>
        <div class="r26-memorial-body">
            <span class="r26-label rv">In Memoriam</span>
            <h2 class="rv">Eleven entries closed by death this year &mdash; not by justice</h2>
            <p class="rv d2">Eleven people in the census died in fiscal year 2026 with their cases still open:
            in prison hospitals, in immigration detention, and at home waiting on appeals that outlived them.
            The record does not soften what happened to them, because the record is the only apology the state
            never offers. Their entries stay in the census permanently, marked and remembered on the
            <a href="/memorial" style="color: var(--acc2)">memorial</a> each December.</p>
        </div>
    </section>

    {{-- TRANSFORMING SYSTEMS INTRO --}}
    <section class="r26-sect" id="transforming-systems-intro">
        <div class="r26-narrow">
            <span class="r26-label rv">Transforming Systems</span>
            <h2 class="r26-title rv">The docket that defined the year</h2>
            <p class="r26-lede rv">Alongside the individual fights, the rules themselves were on trial. Five
            cases &mdash; each documented in the census with full filings in the archive &mdash; drew the year&rsquo;s
            legal lines around speech, protest, and status.</p>
        </div>
    </section>

    @php
    $docket = [
        ['case' => 'AAUP v. Rubio', 'sub' => 'U.S. District Court, District of Massachusetts &middot; decided September 30, 2025',
         'bg' => '/storage/petitions/end-espionage-act-prosecutions-of-journalists.jpg',
         'text' => 'The first full trial over the campaign to arrest and deport noncitizen students and faculty for
            pro-Palestinian speech ended with a ruling that the campaign violated the First Amendment. The court
            found the government had intentionally targeted protected expression to chill it. The census&rsquo;s
            student cohort &mdash; more than 40 entries &mdash; is cross-referenced to the opinion, and our archive
            holds the trial record.'],
        ['case' => '&Ouml;zt&uuml;rk v. Trump', 'sub' => 'Habeas litigation, District of Vermont &middot; ongoing',
         'bg' => '/images/campus-cases/rumeysa-ozturk.webp',
         'text' => 'The case that began with six masked agents on a Somerville sidewalk continued to set precedent
            after R&uuml;meysa &Ouml;zt&uuml;rk&rsquo;s release: on venue games, on secret visa revocations, and on
            what a court may demand the government produce about its own decision to detain a writer for writing.
            Her census entry, opened in March 2025, remains open until the last question is answered.'],
        ['case' => 'United States v. Dunn', 'sub' => 'D.C. Superior Court jury &middot; acquitted November 2025',
         'bg' => '/storage/history/coxeys-army.jpg',
         'text' => 'Two grand juries declined. A trial jury acquitted. The sandwich case earned its place on this
            list not for its stakes but for its signal: the deployment docket&rsquo;s weakest cases could not
            survive contact with twelve residents of the city they were policing. Prosecutors quietly dropped a
            half-dozen similar cases in the weeks after the verdict &mdash; each one noted in the census.'],
        ['case' => 'United States v. Abughazaleh', 'sub' => 'N.D. Illinois &middot; dismissed with prejudice, May 2026',
         'bg' => '/storage/prisoners/kat-abughazaleh.jpg',
         'text' => 'The Broadview Six dismissal did more than free six defendants: the court&rsquo;s findings on
            grand-jury misconduct forced disclosure practices onto the rest of the deployment docket in the
            district. It is the year&rsquo;s clearest example of why defense committees fight discovery battles
            nobody tweets about &mdash; and why the archive preserves what discovery surfaces.'],
        ['case' => 'Noem v. Vasquez Perdomo', 'sub' => 'U.S. Supreme Court, emergency docket &middot; September 2025',
         'bg' => '/storage/history/attica.jpg',
         'text' => 'On the shadow docket, the Court stayed an injunction that had barred roving immigration stops
            in Los Angeles based on appearance, language, and workplace. The census felt the ruling immediately:
            arrest entries from the LA deployment tripled in the following quarter. When the law moves this fast,
            a record that timestamps everything is not a luxury. It is the evidence the next case will need.'],
    ];
    @endphp

    @foreach ($docket as $d)
        <section class="r26-case" data-ppanel>
            <div class="r26-case-bg" data-parallax style="background-image: url('{{ $d['bg'] }}')"></div>
            <div class="r26-wrap">
                <div class="r26-case-card rv">
                    <div class="r26-case-kicker">From the Docket</div>
                    <h3><i>{!! $d['case'] !!}</i></h3>
                    <div class="r26-case-sub">{!! $d['sub'] !!}</div>
                    <p>{!! $d['text'] !!}</p>
                </div>
            </div>
        </section>
    @endforeach

    {{-- VIDEO INTERSTITIAL --}}
    <section class="r26-inter" id="before-cases">
        <video autoplay muted loop playsinline poster="/videos/nppc-launch-film-poster.jpg" data-inter-video>
            <source src="/videos/nppc-launch-film.mp4" type="video/mp4">
        </video>
        <div class="r26-inter-body">
            <p class="rv">Alongside the courtroom fights, we worked with defense committees in every region the
            deployments touched &mdash; tracking arrests in real time, connecting families to counsel, and turning
            the year&rsquo;s chaos into a record that cannot be untold.</p>
        </div>
    </section>

    {{-- SIX STATES --}}
    <section class="r26-sect" id="wins" style="padding-bottom: 40px;">
        <div class="r26-narrow">
            <span class="r26-label rv">The Map of the Year</span>
            <h2 class="r26-title rv">Six states, six fronts</h2>
            <p class="r26-lede rv">The mass docket was not one story &mdash; it was six regional ones. Each front
            below links to its live entries in the census.</p>
        </div>
    </section>

    @php
    $states = [
        ['state' => 'District of Columbia', 'title' => 'The deployment docket meets the jury box',
         'bg' => '/storage/history/coxeys-army.jpg',
         'text' => 'Under the August 2025 federal deployment, protest and bystander arrests in the capital ran at
            a pace the census had never recorded there. Then the district&rsquo;s residents started sitting on its
            juries. Acquittals and declined indictments &mdash; Dunn&rsquo;s first among them &mdash; turned the
            deployment docket into the year&rsquo;s best evidence that documentation plus a jury is still a defense.
            In the fiscal year&rsquo;s last week, the reflecting-pool mass arrests opened forty new entries in a
            single night.'],
        ['state' => 'Illinois', 'title' => 'Broadview, Midway Blitz, and the misconduct dismissal',
         'bg' => '/storage/prisoners/kat-abughazaleh.jpg',
         'text' => 'Operation Midway Blitz brought the deployment model to Chicago, and the Broadview ICE
            processing center became its flashpoint. The census opened 61 Illinois entries this year and closed
            the six most prominent with prejudice. Our Chicago defense-committee partners ran the year&rsquo;s
            best rapid-response intake &mdash; names verified within 48 hours of arrest.'],
        ['state' => 'Georgia', 'title' => 'The RICO theory grinds on', 'bg' => '/storage/history/stop-cop-city.jpg',
         'text' => 'The 61-defendant Stop Cop City RICO prosecution entered its fourth year with the conspiracy
            theory shrinking at every hearing &mdash; and the defendants still living under it. The census keeps
            each of the 61 entries current, hearing by hearing, because the broadest conspiracy prosecution of a
            protest movement in a generation should have the most complete record of one.'],
        ['state' => 'Texas', 'title' => 'Prairieland and the July 4 cases', 'bg' => '/storage/petitions/end-bop-communications-management-units.jpg',
         'text' => 'The prosecutions that followed the July 2025 incident outside the Prairieland ICE detention
            center produced one of the year&rsquo;s heaviest dockets &mdash; attempted-murder and terrorism counts,
            capital exposure, and a support network under subpoena. Our Texas partners&rsquo; letter-writing and
            court-watch programs, listed on our events page, kept every hearing observed and every defendant
            written to.'],
        ['state' => 'Minnesota', 'title' => 'The heartland cases', 'bg' => '/storage/history/whistleblowers.jpg',
         'text' => 'Deployment-adjacent prosecutions reached the upper Midwest by summer&rsquo;s end: church
            sanctuary volunteers, a school-board commenter, and the Minneapolis rapid-response networks that
            documented ICE operations found themselves documented back. Thirty-one Minnesota entries opened this
            year; nine closed by dismissal. The census&rsquo;s oldest lesson held: the first list is the one that
            protects everyone on it.'],
        ['state' => 'California', 'title' => 'The Los Angeles deployment, from Huerta to Perdomo', 'bg' => '/storage/prisoners/david-huerta.jpg',
         'text' => 'From union president David Huerta&rsquo;s arrest at a June 2025 raid line to the post-Perdomo
            tripling of roving-stop arrests, California produced more FY26 entries than any other state. It also
            produced the year&rsquo;s densest defense infrastructure: 14 committees, a statewide bail fund, and a
            court-watch corps the census now cites as a source in 200+ entries.'],
    ];
    @endphp

    @foreach ($states as $st)
        <section class="r26-case" data-ppanel>
            <div class="r26-case-bg" data-parallax style="background-image: url('{{ $st['bg'] }}')"></div>
            <div class="r26-wrap">
                <div class="r26-case-card rv">
                    <div class="r26-case-kicker">{{ $st['state'] }}</div>
                    <h3>{{ $st['title'] }}</h3>
                    <p>{!! $st['text'] !!}</p>
                </div>
            </div>
        </section>
    @endforeach

    {{-- CTA 2 --}}
    <section class="r26-cta is-black" id="donate-section-2">
        <div class="r26-wrap">
            <h3 class="rv">The census reached 7,391 documented cases this year. Help us keep watching.</h3>
            <div class="rv d2">
                <a class="r26-btn" href="/database">Open the Database</a>
                <a class="r26-btn ghost" href="/dashboard">See the Live Dashboard</a>
            </div>
        </div>
    </section>

    {{-- IMAGE INTERSTITIAL --}}
    <section class="r26-inter" id="before-advancing-movement">
        <div class="r26-inter-img" style="background-image: url('/storage/history/standing-rock.jpg')"></div>
        <div class="r26-inter-body">
            <p class="rv">A record is only as strong as the movement that keeps it. This year, that movement got
            bigger, faster, and harder to ignore.</p>
        </div>
    </section>

    {{-- ADVANCING: NETWORK --}}
    <section class="r26-panel alt" id="network">
        <div class="r26-wrap r26-2col">
            <div class="rv"><img src="/storage/history/attica.jpg" alt="Attica prison yard, September 1971" loading="lazy"></div>
            <div>
                <span class="r26-label rv">Advancing the Movement</span>
                <h2 class="r26-title rv" style="font-size: clamp(1.6rem, 3vw, 2.4rem);">2026 Gathering of the Record</h2>
                <p class="rv">In April, the coalition&rsquo;s second national convening drew 640 attendees to two
                days of casework, archive training, and testimony &mdash; including 31 defense committees, up from
                22 last year, and delegations from every deployment city. The rapid-response intake standard our
                Chicago partners built was adopted coalition-wide on the closing day.</p>
                <p class="rv d2">As always, the room stood longest for the people whose entries are closed: the
                freed, the acquitted, the pardoned, and the six who cannot be recharged.</p>
            </div>
        </div>
    </section>

    {{-- ADVANCING: DASHBOARD / DATA --}}
    <section class="r26-panel" id="just-data">
        <div class="r26-wrap r26-2col">
            <div>
                <span class="r26-label rv">The Census, Live</span>
                <h2 class="r26-title rv" style="font-size: clamp(1.6rem, 3vw, 2.4rem);">The dashboard era</h2>
                <p class="rv">This was the year the record went live: the coalition&rsquo;s public
                <a href="/dashboard" style="color: var(--acc2)">dashboard</a> now tracks protest arrests and
                prosecutions in near-real time, plotted on the national <a href="/map" style="color: var(--acc2)">map</a>
                and fed into the census by the intake team. During the June mass arrests it became the most-cited
                independent count of who was taken and where.</p>
                <div class="r26-stats">
                    <div class="rv"><div class="r26-stat-n">7,391</div><div class="r26-stat-l">documented cases in the census at fiscal year-end</div></div>
                    <div class="rv d2"><div class="r26-stat-n">4.2M</div><div class="r26-stat-l">content views across the census, dashboard, memorial, and archive</div></div>
                    <div class="rv d3"><div class="r26-stat-n">72,000</div><div class="r26-stat-l">Dispatch subscribers &mdash; up 14,000 this year</div></div>
                </div>
            </div>
            <div class="rv d2"><img src="/storage/history/wounded-knee.jpg" alt="Archival photograph from the census archive" loading="lazy"></div>
        </div>
    </section>

    {{-- WAYS TO GIVE --}}
    <section class="r26-sect" id="ways-to-give">
        <div class="r26-wrap">
            <span class="r26-label rv">Ways to Give</span>
            <h2 class="r26-title rv">Keep the whole world watching</h2>
            <div class="r26-ways">
                <a class="r26-way rv" href="/donate"><h4>Donate</h4><p>One-time or monthly. $11 documents a case, forever.</p></a>
                <a class="r26-way rv d2" href="/prisoner-outreach"><h4>Write to a Prisoner</h4><p>The most requested program we run, forty years running.</p></a>
                <a class="r26-way rv d3" href="/donate"><h4>Commissary &amp; Family Fund</h4><p>Direct support for people inside and their families.</p></a>
                <a class="r26-way rv d4" href="/volunteer"><h4>Volunteer</h4><p>Readers, archivists, court-watchers, and letter-night hosts.</p></a>
            </div>
        </div>
    </section>

    {{-- FINANCIALS FY26 --}}
    @php
    $revenue = [
        ['Individuals', 2711630], ['Foundations', 1284200], ['Events', 209415],
        ['Store & publications', 133880], ['Investments', 241770], ['Other income', 88030],
    ];
    $revTotal = array_sum(array_column($revenue, 1));
    $expend = [ ['Program', 3387905], ['Management & General', 561480], ['Fundraising', 524610] ];
    $expTotal = array_sum(array_column($expend, 1));
    $programs = [
        ['Census & Research', 1084130], ['Archive & Digital', 745215], ['Family & Commissary', 668340],
        ['Legal Support Fund', 549850], ['Development', 524610], ['Communications', 505180], ['Operations', 396670],
    ];
    $progTotal = array_sum(array_column($programs, 1));
    @endphp
    <section class="r26-fin" id="activities">
        <div class="r26-wrap">
            <span class="r26-label rv">Financials</span>
            <h2 class="r26-title rv">Statement of Activities &mdash; FY26</h2>
            <div class="r26-fin-grid">
                <div class="r26-fin-col rv">
                    <h3>Revenue</h3>
                    @foreach ($revenue as [$label, $amt])
                        <div class="r26-fin-row">
                            <div class="top"><b>{{ $label }}</b><span>${{ number_format($amt) }}</span></div>
                            <div class="pct">{{ round($amt * 100 / $revTotal) }}%</div>
                            <div class="r26-bar"><i data-pct="{{ round($amt * 100 / $revTotal) }}"></i></div>
                        </div>
                    @endforeach
                    <div class="r26-fin-row tot"><div class="top"><b>Total Revenue</b><span>${{ number_format($revTotal) }}</span></div></div>
                </div>
                <div class="r26-fin-col rv d2">
                    <h3>Expenditures</h3>
                    @foreach ($expend as [$label, $amt])
                        <div class="r26-fin-row">
                            <div class="top"><b>{{ $label }}</b><span>${{ number_format($amt) }}</span></div>
                            <div class="pct">{{ round($amt * 100 / $expTotal) }}%</div>
                            <div class="r26-bar"><i data-pct="{{ round($amt * 100 / $expTotal) }}"></i></div>
                        </div>
                    @endforeach
                    <div class="r26-fin-row tot"><div class="top"><b>Total Expenditures</b><span>${{ number_format($expTotal) }}</span></div></div>
                </div>
                <div class="r26-fin-col rv d3">
                    <h3>Expenses by Program</h3>
                    @foreach ($programs as [$label, $amt])
                        <div class="r26-fin-row">
                            <div class="top"><b>{{ $label }}</b><span>${{ number_format($amt) }}</span></div>
                            <div class="pct">{{ round($amt * 100 / $progTotal) }}%</div>
                            <div class="r26-bar"><i data-pct="{{ round($amt * 100 / $progTotal) }}"></i></div>
                        </div>
                    @endforeach
                    <div class="r26-fin-row tot"><div class="top"><b>Total</b><span>${{ number_format($progTotal) }}</span></div></div>
                </div>
            </div>
        </div>
    </section>

    {{-- DONORS --}}
    @php
    $donorTiers = json_decode(file_get_contents(database_path('data/report-2026-donors.json')), true) ?: [];
    $donorCards = [array_slice($donorTiers, 0, 2), array_slice($donorTiers, 2, 2),
                   array_slice($donorTiers, 4, 2), array_slice($donorTiers, 6, 2)];
    @endphp
    <section class="r26-lists" id="donors">
        <div class="r26-wrap">
            <span class="r26-label rv">Fiscal Year 2026 Donors</span>
            <h2 class="r26-title rv">The names behind the record</h2>
            <div class="r26-hint rv">Scroll within each card to see all names</div>
            <div class="r26-cards">
                @foreach ($donorCards as $card)
                    <div class="r26-card rv">
                        @foreach ($card as [$tier, $names])
                            <h4>{{ $tier }}</h4>
                            @foreach ($names as $n)
                                <div class="who">{{ $n }}</div>
                            @endforeach
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FROM OUR DONORS — TRIBUTE --}}
    <section class="r26-tribute" id="from-donors">
        <div class="r26-narrow">
            <span class="r26-label rv">From Our Donors</span>
            <div class="name rv">Remembering Diana Sullivan</div>
            <div class="dates rv d2">Supporter since the census opened &middot; died March 2026</div>
            <p class="rv d2">The coalition fondly remembers Diana Sullivan, whose gifts anchored our top donor
            tier for three consecutive years and whose questions anchored something rarer: she read the census.
            She would call the archive desk about a single entry &mdash; a name, a date, a charge that didn&rsquo;t
            add up &mdash; and more than once her questions became corrections.</p>
            <p class="rv d3">Her family has endowed the Sullivan Reading Desk in the archive&rsquo;s public reading
            room, so that the record she funded stays free to everyone who, like her, refuses to skim. We are
            honored to have been part of her legacy.</p>
        </div>
    </section>

    {{-- STAFF --}}
    @php
    try { $staffMembers = \App\Models\Staff::orderBy('name')->get(); } catch (\Throwable $e) { $staffMembers = collect(); }
    $staffGroup = $staffMembers->where('group', '!=', 'board');
    $boardGroup = $staffMembers->where('group', 'board');
    $readers = ['Anthony McDonald', 'Bobby Webb', 'Cynthia Hoffman', 'Cynthia Weinstein', 'Daniel Black',
                'Doris Gutierrez', 'Emily Chavez', 'Gerald Robertson', 'Grace Talltree', 'Hiram Rose',
                'Janice Crawford', 'Jeremy Ruiz', 'Karen Schmidt', 'Kelly Mendez', 'Simone Jackson'];
    $archivists = ['Amanda Washington', 'Arthur Contreras', 'Ashley Simmons', 'Bryan Brown', 'Carol Gonzales',
                   'Charles Hayes', 'Debra Sullivan', 'Eric Hughes', 'Jacqueline Wood', 'Joan Burns',
                   'Jonah Delgado', 'Jonathan Hawkins', 'Julia Marshall', 'Julia Parker', 'Kathleen Williams',
                   'Kenji Daniels', 'Lisa Shaw', 'Marilyn Romero', 'Nancy Duncan', 'Raymond Castillo',
                   'Roger James', 'Ruth Diaz', 'Thomas Edwards', 'Zachary Wilson'];
    @endphp
    <section class="r26-lists" id="staff" style="padding-top: 0;">
        <div class="r26-wrap">
            <span class="r26-label rv">Our People</span>
            <h2 class="r26-title rv">The coalition, by name</h2>
            <div class="r26-hint rv">Scroll within each card to see all names</div>
            <div class="r26-cards three">
                <div class="r26-card rv">
                    <h4>Staff &amp; Coordinating Committee</h4>
                    @if ($staffGroup->isNotEmpty())
                        @foreach ($staffGroup as $m)
                            <div class="who">{{ $m->name }}@if($m->position)<small>{{ $m->position }}</small>@endif</div>
                        @endforeach
                    @else
                        <div class="who">See <a href="/staff" style="color: var(--acc2)">the staff page</a></div>
                    @endif
                    @if ($boardGroup->isNotEmpty())
                        <h4>Board</h4>
                        @foreach ($boardGroup as $m)
                            <div class="who">{{ $m->name }}@if($m->position)<small>{{ $m->position }}</small>@endif</div>
                        @endforeach
                    @endif
                </div>
                <div class="r26-card rv d2">
                    <h4>Readers&rsquo; Panel FY26</h4>
                    <div class="who" style="border-bottom: 0; color: var(--dim); font-size: 12.5px; padding-bottom: 12px;">
                        Every contested entry is reviewed by an outside panel of historians and movement veterans
                        before publication. Fifteen readers served this year.</div>
                    @foreach ($readers as $n)
                        <div class="who">{{ $n }}</div>
                    @endforeach
                </div>
                <div class="r26-card rv d3">
                    <h4>Volunteer Archivists</h4>
                    <div class="who" style="border-bottom: 0; color: var(--dim); font-size: 12.5px; padding-bottom: 12px;">
                        The corps that mirrored, indexed, and timestamped the year of the mass docket.</div>
                    @foreach ($archivists as $n)
                        <div class="who">{{ $n }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- THANK YOU --}}
    <section class="r26-thanks" id="thank-you">
        <div class="r26-wrap">
            <h2 class="rv">Thank You</h2>
            <p class="rv d2">7,391 cases. 511 added in fiscal year 2026 alone. 104 marked closed &mdash; by juries,
            judges, and the stubborn fact of a record nobody could untell. The census exists because you decided
            the whole world should get to keep watching.</p>
            <div class="rv d3">
                <a class="r26-btn" style="background: var(--acc); color: #fff;" href="/database">Explore the Database</a>
                <a class="r26-btn ghost" href="/annual-report">All Annual Reports</a>
            </div>
            <div class="r26-credits rv">
                Photo credits, in order of appearance: Bonus Army encampment at the Capitol, 1932 (public domain); NPPC launch film; Sean Dunn, Larry Bushart, Kat Abughazaleh, and David Huerta, NPPC case
                files; Bonus Army encampment, 1932 (public domain); First Amendment inscription, Newseum
                fa&ccedil;ade (CC BY-SA); Smith Act defendants, 1949 (public domain); memorial candles, NPPC;
                R&uuml;meysa &Ouml;zt&uuml;rk, campus-case file; Attica prison yard, September 1971 (public
                domain); Stop Cop City vigil, January 2023 (Tatsoi, CC BY-SA 4.0); Standing Rock #NoDAPL
                demonstration (Fibonacci Blue, CC BY 2.0); Wounded Knee occupation, 1973 (public domain). Full
                per-image licensing lives in the repository&rsquo;s CREDITS files.
            </div>
        </div>
    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
    }, { threshold: 0.18 });
    document.querySelectorAll('.rv').forEach(function (el) { io.observe(el); });

    // scrollytelling galleries
    document.querySelectorAll('[data-gallery]').forEach(function (gal) {
        var imgs = gal.querySelectorAll('.r26-sg-fig img');
        var steps = gal.querySelectorAll('.r26-step');
        var sio = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (!e.isIntersecting) { e.target.classList.remove('on'); return; }
                e.target.classList.add('on');
                var n = parseInt(e.target.getAttribute('data-step'), 10);
                imgs.forEach(function (im) {
                    im.classList.toggle('on', parseInt(im.getAttribute('data-image'), 10) === Math.min(n, imgs.length));
                });
            });
        }, { rootMargin: '-45% 0px -45% 0px' });
        steps.forEach(function (s) { sio.observe(s); });
    });

    // parallax
    var pxs = document.querySelectorAll('[data-parallax]');
    if (pxs.length && !reduced) {
        var ticking = false;
        addEventListener('scroll', function () {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(function () {
                ticking = false;
                pxs.forEach(function (bg) {
                    var host = bg.parentElement;
                    var r = host.getBoundingClientRect();
                    if (r.bottom < 0 || r.top > innerHeight) return;
                    var p = (r.top + r.height / 2 - innerHeight / 2) / innerHeight;
                    bg.style.transform = 'translateY(' + (p * 52) + 'px)';
                });
            });
        }, { passive: true });
    }

    // pause the interstitial video off-screen; respect reduced motion
    var iv = document.querySelector('[data-inter-video]');
    if (iv) {
        if (reduced) { iv.removeAttribute('autoplay'); iv.pause(); }
        else {
            new IntersectionObserver(function (entries) {
                entries.forEach(function (e) { e.isIntersecting ? iv.play().catch(function(){}) : iv.pause(); });
            }, { threshold: 0.15 }).observe(iv);
        }
    }

    // financial bars
    var bio = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (!e.isIntersecting) return;
            bio.unobserve(e.target);
            e.target.querySelectorAll('.r26-bar i').forEach(function (bar) {
                var pct = bar.getAttribute('data-pct');
                if (reduced) { bar.style.transition = 'none'; }
                requestAnimationFrame(function () { bar.style.width = pct + '%'; });
            });
        });
    }, { threshold: 0.3 });
    document.querySelectorAll('.r26-fin-col').forEach(function (el) { bio.observe(el); });
});
</script>
@endsection
