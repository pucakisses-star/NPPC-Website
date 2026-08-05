@extends('app')

@section('title', 'Report 2023 — Documenting the Record | NPPC')

@section('meta_description')The NPPC 2023 interactive annual report: the coalition's first year — 6,104 cases documented, the archive opened, and the record restored for 79 people who came home.@endsection

@section('og_image'){{ asset('storage/history/the-labor-movement.jpg') }}@endsection

@section('head')
<style>
/* ============================================================
   Report 2023 — interactive annual-report microsite.
   Full-bleed page in the site palette; scroll-reveal animations
   (IntersectionObserver), hero crossfade slider, count-up stats,
   chapter dividers with growing rules, gallery carousel, FAQ
   accordion, and donor load-more.
   ============================================================ */
body.page-report-2023 main.container,
body.page-report-2023 .container { width: 100% !important; max-width: none !important; padding-left: 0 !important; padding-right: 0 !important; }
body.page-report-2023 { background: #0a0a12; }

.rpt { --ink: #ececf2; --dim: rgba(236,236,242,0.62); --acc: #5660fe; --acc2: #8f97ff;
       --paper: #f0f1f7; --deep: #0a0a12; --navy: #12122a;
       color: var(--ink); font-size: 16px; line-height: 1.7; overflow: hidden; }
.rpt-wrap { max-width: 1180px; margin: 0 auto; padding: 0 28px; }

/* reveal framework */
.rv { opacity: 0; transform: translateY(34px); transition: opacity .9s ease, transform .9s cubic-bezier(.22,1,.36,1); }
.rv.rv-fade { transform: none; }
.rv.in { opacity: 1; transform: none; }
@media (prefers-reduced-motion: reduce) { .rv { opacity: 1 !important; transform: none !important; transition: none; } }

.rpt-label { display: inline-block; font-size: 12px; font-weight: 800; letter-spacing: .22em; text-transform: uppercase; color: var(--acc2); margin-bottom: 18px; }
.rpt-title { font-size: clamp(2rem, 4.2vw, 3.2rem); font-weight: 900; line-height: 1.08; color: var(--ink); margin: 0 0 22px; letter-spacing: -.015em; }

/* ── hero slider ─────────────────────────────────────────── */
.rpt-hero { position: relative; height: 92vh; min-height: 560px; display: flex; align-items: flex-end; }
.rpt-hero-slide { position: absolute; inset: 0; background: center / cover no-repeat; opacity: 0;
  transition: opacity 1.6s ease; filter: grayscale(35%) brightness(.6); transform: scale(1.04); }
.rpt-hero-slide.on { opacity: 1; animation: rptHeroDrift 7s ease-out forwards; }
@keyframes rptHeroDrift { from { transform: scale(1.09); } to { transform: scale(1.02); } }
.rpt-hero-shade { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(10,10,18,.55), rgba(10,10,18,.25) 45%, rgba(10,10,18,.94)); }
.rpt-hero-body { position: relative; z-index: 2; padding-bottom: 9vh; }
.rpt-hero h1 { font-size: clamp(2.6rem, 6vw, 5rem); font-weight: 900; line-height: 1.04; color: #fff; max-width: 15ch; margin: 0; letter-spacing: -.02em; }
.rpt-hero h1 .accent { color: var(--acc2); }
.rpt-scrollcue { position: absolute; right: 42px; bottom: 40px; z-index: 2; display: flex; flex-direction: column; align-items: center; gap: 10px;
  font-size: 10.5px; letter-spacing: .3em; text-transform: uppercase; color: rgba(255,255,255,.6); writing-mode: vertical-rl; }
.rpt-scrollcue::after { content: ''; width: 1px; height: 62px; background: linear-gradient(180deg, var(--acc), transparent); animation: rptCue 2.2s ease-in-out infinite; }
@keyframes rptCue { 0%,100% { transform: scaleY(.4); transform-origin: top; } 50% { transform: scaleY(1); } }
@media (prefers-reduced-motion: reduce) { .rpt-hero-slide.on { animation: none; transform: none; } .rpt-scrollcue::after { animation: none; } }

/* ── film module ─────────────────────────────────────────── */
.rpt-film { padding: 96px 0; }
.rpt-film video { display: block; width: 100%; border-radius: 10px; box-shadow: 0 34px 90px rgba(0,0,0,.55); }
.rpt-film-cap { margin-top: 18px; font-size: 13px; color: var(--dim); text-align: center; letter-spacing: .06em; text-transform: uppercase; }

/* ── letter ──────────────────────────────────────────────── */
.rpt-letter { padding: 40px 0 90px; }
.rpt-cols { column-count: 2; column-gap: 54px; font-size: 16.5px; color: rgba(236,236,242,.85); }
.rpt-cols p { margin: 0 0 20px; break-inside: avoid-column; }
.rpt-sign { font-weight: 800; color: var(--ink); }
.rpt-sign span { display: block; font-weight: 400; color: var(--dim); font-size: 14px; }
@media (max-width: 800px) { .rpt-cols { column-count: 1; } }

/* ── freed — accordion list + swapping portrait ──────────── */
.rpt-freed { background: var(--paper); padding: 110px 0; }
.rfx-grid { display: grid; grid-template-columns: minmax(340px, 5fr) 6fr; gap: 72px; align-items: start; }
.rfx-kicker { display: flex; align-items: center; gap: 11px; font-size: 15.5px; font-weight: 800; color: #14142b; margin-bottom: 30px; }
.rfx-kicker::before { content: ''; width: 17px; height: 17px; background: var(--acc); flex: 0 0 auto; }
.rfx-item { border-bottom: 1px solid rgba(20,20,43,.18); }
.rfx-q { width: 100%; display: flex; justify-content: space-between; align-items: center; gap: 18px;
  background: none; border: 0; cursor: pointer; text-align: left; padding: 17px 2px;
  font-size: clamp(1.15rem, 1.7vw, 1.5rem); font-weight: 800; color: #14142b; letter-spacing: -.01em; font-family: inherit; }
.rfx-item.open .rfx-q { color: var(--acc); padding-bottom: 6px; }
.rfx-q .chev { flex: 0 0 auto; width: 9px; height: 9px; border-right: 2px solid rgba(20,20,43,.45); border-bottom: 2px solid rgba(20,20,43,.45);
  transform: rotate(45deg) translateY(-2px); transition: transform .3s; }
.rfx-item.open .chev { transform: rotate(-135deg) translateY(-2px); border-color: var(--acc); }
.rfx-a { max-height: 0; overflow: hidden; transition: max-height .55s cubic-bezier(.22,1,.36,1); }
.rfx-meta { font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #14142b; margin: 0 0 14px; }
.rfx-a p { font-size: 15px; line-height: 1.75; color: rgba(20,20,43,.85); margin: 0 0 14px; padding-right: 6px; }
.rfx-a p a { color: inherit; text-underline-offset: 3px; }
.rfx-credit { font-size: 12.5px; color: rgba(20,20,43,.5); margin: 0 0 24px; }
.rfx-inline { display: none; }
.rfx-photo { position: sticky; top: 80px; aspect-ratio: 7 / 8; overflow: hidden; }
.rfx-photo-img { position: absolute; inset: 0; background: center / cover no-repeat; opacity: 0; transition: opacity .55s ease; }
.rfx-photo-img.on { opacity: 1; }
.rfx-noimg { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; background: var(--navy); text-align: center; padding: 30px; }
.rfx-noimg b { font-size: clamp(5rem, 10vw, 8.5rem); font-weight: 900; line-height: 1; color: var(--acc2); }
.rfx-noimg small { font-size: 13px; letter-spacing: .14em; text-transform: uppercase; color: var(--dim); max-width: 30ch; line-height: 1.7; }
.rpt-freed-note { margin-top: 34px; color: rgba(20,20,43,.6); font-size: 13.5px; }
.rpt-freed-note a { color: var(--acc); }
@media (max-width: 900px) {
  .rfx-grid { grid-template-columns: 1fr; gap: 0; }
  .rfx-photo { display: none; }
  .rfx-inline { display: block; width: 100%; aspect-ratio: 4 / 3; background: center / cover no-repeat; margin: 0 0 16px; }
}

/* ── chapter dividers ────────────────────────────────────── */
.rpt-chapter { position: relative; min-height: 74vh; display: flex; align-items: center; overflow: hidden; }
.rpt-chapter-bg { position: absolute; inset: -8% 0; background: center / cover no-repeat; filter: grayscale(60%) brightness(.38); will-change: transform; }
.rpt-chapter-body { position: relative; z-index: 2; }
.rpt-chapter-num { font-size: clamp(4rem, 10vw, 8rem); font-weight: 900; color: rgba(143,151,255,.28); line-height: 1; }
.rpt-chapter h2 { font-size: clamp(2.4rem, 5.6vw, 4.4rem); font-weight: 900; color: #fff; margin: 6px 0 18px; letter-spacing: -.02em; max-width: 14ch; line-height: 1.05; }
.rpt-chapter-rule { height: 3px; width: 0; background: var(--acc); transition: width 1.4s cubic-bezier(.22,1,.36,1) .2s; }
.rpt-chapter.in .rpt-chapter-rule { width: min(320px, 40vw); }
.rpt-chapter p { max-width: 560px; color: rgba(236,236,242,.85); font-size: 17.5px; }

/* ── feature rows ────────────────────────────────────────── */
.rpt-feature { padding: 100px 0; }
.rpt-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
.rpt-grid2 img { width: 100%; border-radius: 8px; box-shadow: 0 24px 70px rgba(0,0,0,.5); }
.rpt-feature p { color: rgba(236,236,242,.82); }
@media (max-width: 860px) { .rpt-grid2 { grid-template-columns: 1fr; gap: 34px; } }

/* stats */
.rpt-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; margin-top: 54px; }
.rpt-stat-n { font-size: clamp(2.2rem, 4.4vw, 3.6rem); font-weight: 900; color: var(--acc2); line-height: 1; }
.rpt-stat-l { margin-top: 8px; font-size: 13px; color: var(--dim); line-height: 1.5; }
@media (max-width: 860px) { .rpt-stats { grid-template-columns: repeat(2, 1fr); } }

/* icon list */
.rpt-icons { display: grid; grid-template-columns: repeat(4, 1fr); gap: 28px; margin-top: 56px; }
.rpt-icon-card { background: rgba(236,236,242,.045); border: 1px solid rgba(236,236,242,.09); border-radius: 10px; padding: 26px 22px; }
.rpt-icon-card svg { width: 34px; height: 34px; margin-bottom: 14px; }
.rpt-icon-card h4 { font-size: 16px; font-weight: 800; color: var(--ink); margin: 0 0 8px; }
.rpt-icon-card p { font-size: 13.5px; color: var(--dim); margin: 0; line-height: 1.6; }
@media (max-width: 860px) { .rpt-icons { grid-template-columns: 1fr 1fr; } }

/* ── gallery carousel ────────────────────────────────────── */
.rpt-gallery { padding: 90px 0 100px; background: var(--navy); }
.rpt-gal-shell { display: flex; gap: 14px; align-items: stretch; margin-top: 44px; }
.rpt-gal-view { overflow: hidden; flex: 1; border-radius: 8px; }
.rpt-gal-track { display: flex; transition: transform .55s cubic-bezier(.22,1,.36,1); }
.rpt-gal-item { flex: 0 0 100%; }
.rpt-gal-item img { width: 100%; height: 520px; object-fit: cover; display: block; filter: grayscale(25%); }
.rpt-gal-cap { padding: 14px 4px 0; font-size: 13.5px; color: var(--dim); }
.rpt-gal-btn { flex: 0 0 auto; align-self: center; width: 48px; height: 48px; border-radius: 50%; border: 1px solid rgba(236,236,242,.3); background: transparent; color: var(--ink); font-size: 20px; cursor: pointer; transition: background .15s, border-color .15s; }
.rpt-gal-btn:hover { background: var(--acc); border-color: var(--acc); }
.rpt-gal-dots { display: flex; gap: 8px; justify-content: center; margin-top: 22px; }
.rpt-gal-dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(236,236,242,.25); transition: background .2s, transform .2s; }
.rpt-gal-dot.on { background: var(--acc2); transform: scale(1.3); }
@media (max-width: 700px) { .rpt-gal-item img { height: 300px; } }

/* ── story panels ────────────────────────────────────────── */
.rpt-stories { padding: 100px 0; }
.rpt-story { display: grid; grid-template-columns: 1fr 1fr; gap: 56px; align-items: center; padding: 56px 0; border-top: 1px solid rgba(236,236,242,.1); }
.rpt-story:first-of-type { border-top: 0; }
.rpt-story:nth-child(even) .rpt-story-img { order: 2; }
.rpt-story-img img { width: 100%; height: 430px; object-fit: cover; object-position: 50% 20%; border-radius: 8px; }
.rpt-story h3 { font-size: clamp(1.6rem, 3vw, 2.3rem); font-weight: 900; color: var(--ink); margin: 0 0 12px; }
.rpt-story-tag { font-size: 12px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; color: var(--acc2); }
.rpt-story p { color: rgba(236,236,242,.82); }
.rpt-story a.rpt-btn { margin-top: 8px; }
@media (max-width: 860px) { .rpt-story { grid-template-columns: 1fr; gap: 26px; } .rpt-story:nth-child(even) .rpt-story-img { order: 0; } }

.rpt-btn { display: inline-flex; align-items: center; gap: 9px; padding: 12px 24px; border: 1px solid rgba(236,236,242,.35); border-radius: 4px;
  color: var(--ink); font-size: 13px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; text-decoration: none; transition: background .15s, border-color .15s; }
.rpt-btn:hover { background: var(--acc); border-color: var(--acc); color: #fff; }
.rpt-btn.solid { background: var(--acc); border-color: var(--acc); color: #fff; }
.rpt-btn.solid:hover { background: #3b45e0; }

/* ── dispatch quote cards ────────────────────────────────── */
.rpt-quotes { padding: 20px 0 110px; }
.rpt-quote-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 26px; margin-top: 44px; }
.rpt-quote-card { background: rgba(236,236,242,.045); border: 1px solid rgba(236,236,242,.09); border-left: 3px solid var(--acc); border-radius: 8px; padding: 28px 26px; }
.rpt-quote-card p { font-size: 16.5px; font-weight: 700; color: var(--ink); line-height: 1.55; margin: 0 0 16px; }
.rpt-quote-card span { font-size: 12.5px; color: var(--dim); }
@media (max-width: 860px) { .rpt-quote-grid { grid-template-columns: 1fr; } }

/* ── financials ──────────────────────────────────────────── */
.rpt-fin { background: var(--paper); color: #14142a; padding: 100px 0; }
.rpt-fin .rpt-title { color: #14142a; }
.rpt-fin-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 70px; margin-top: 40px; }
.rpt-fin h3 { font-size: 1.4rem; font-weight: 900; margin: 0 0 24px; color: #14142a; }
.rpt-fin table { width: 100%; border-collapse: collapse; font-size: 14px; }
.rpt-fin td { padding: 9px 0; border-bottom: 1px solid rgba(20,20,42,.12); }
.rpt-fin td.amt { text-align: right; font-weight: 800; }
.rpt-fin tr.tot td { border-top: 2px solid #14142a; border-bottom: 0; font-weight: 900; }
.rpt-dot { display: inline-block; width: 10px; height: 10px; border-radius: 2px; margin-right: 9px; }
.rpt-fin-pie { margin: 0 auto 26px; display: block; }
.rpt-fin-note { margin-top: 34px; font-size: 12.5px; color: rgba(20,20,42,.55); }
@media (max-width: 860px) { .rpt-fin-grid { grid-template-columns: 1fr; } }

/* ── FAQ accordion ───────────────────────────────────────── */
.rpt-faq { padding: 100px 0; }
.rpt-faq-item { border-bottom: 1px solid rgba(236,236,242,.12); }
.rpt-faq-q { width: 100%; display: flex; justify-content: space-between; align-items: center; gap: 20px; background: none; border: 0;
  padding: 24px 4px; font: inherit; font-size: 18px; font-weight: 800; color: var(--ink); text-align: left; cursor: pointer; }
.rpt-faq-q .chev { flex: 0 0 auto; transition: transform .3s; color: var(--acc2); font-size: 22px; }
.rpt-faq-item.open .chev { transform: rotate(45deg); }
.rpt-faq-a { max-height: 0; overflow: hidden; transition: max-height .5s cubic-bezier(.22,1,.36,1); }
.rpt-faq-a p { margin: 0 0 24px; padding: 0 4px; color: rgba(236,236,242,.78); max-width: 820px; }

/* ── donors ──────────────────────────────────────────────── */
.rpt-donors { background: var(--navy); padding: 100px 0; }
.rpt-tier { margin-top: 44px; }
.rpt-tier h4 { font-size: 14px; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; color: var(--acc2); margin: 0 0 18px; }
.rpt-donor-cols { column-count: 4; column-gap: 30px; font-size: 13.5px; color: rgba(236,236,242,.8); }
.rpt-donor-cols div { padding: 3px 0; break-inside: avoid-column; }
.rpt-donor-cols div.hid { display: none; }
.rpt-loadmore { margin-top: 34px; }
@media (max-width: 860px) { .rpt-donor-cols { column-count: 2; } }

/* ── members ─────────────────────────────────────────────── */
.rpt-members { padding: 100px 0 80px; }
.rpt-mem-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; margin-top: 40px; }
.rpt-mem h4 { font-size: 14px; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; color: var(--acc2); margin: 0 0 16px; }
.rpt-mem div { padding: 6px 0; color: rgba(236,236,242,.85); }
.rpt-mem b { color: var(--ink); }
.rpt-mem span { color: var(--dim); font-size: 13.5px; }
@media (max-width: 700px) { .rpt-mem-grid { grid-template-columns: 1fr; } }

/* ── thank you ───────────────────────────────────────────── */
.rpt-thanks { min-height: 82vh; display: flex; align-items: center; justify-content: center; text-align: center;
  background: radial-gradient(circle at 50% 38%, #202048 0%, var(--navy) 52%, var(--deep) 100%); }
.rpt-thanks h2 { font-size: clamp(3rem, 8vw, 6rem); font-weight: 900; color: #fff; margin: 0 0 24px; letter-spacing: -.02em; }
.rpt-thanks p { max-width: 560px; margin: 0 auto 40px; color: rgba(236,236,242,.75); }
.rpt-thanks .row { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
</style>
@endsection

@section('body')
<div class="rpt">

    {{-- HERO --}}
    <section class="rpt-hero" id="rpt-hero">
        <div class="rpt-hero-slide on" style="background-image:url('/storage/history/the-labor-movement.jpg')"></div>
        <div class="rpt-hero-slide" style="background-image:url('/storage/history/suffragism.jpg')"></div>
        <div class="rpt-hero-slide" style="background-image:url('/storage/history/attica.jpg')"></div>
        <div class="rpt-hero-slide" style="background-image:url('/storage/history/the-civil-rights-movement.jpg')"></div>
        <div class="rpt-hero-shade"></div>
        <div class="rpt-wrap rpt-hero-body">
            <span class="rpt-label">National Political Prisoner Coalition &middot; 2023 Annual Report</span>
            <h1>No One Jailed for Their Politics <span class="accent">Will Be Lost to the Record</span></h1>
        </div>
        <div class="rpt-scrollcue">Scroll</div>
    </section>

    {{-- FILM --}}
    <section class="rpt-film">
        <div class="rpt-wrap rv rv-fade">
            <video controls preload="metadata" playsinline poster="{{ asset('videos/nppc-launch-film-poster.jpg') }}">
                <source src="{{ asset('videos/nppc-launch-film.mp4') }}" type="video/mp4">
            </video>
            <div class="rpt-film-cap">Watch: the coalition in sixty seconds</div>
        </div>
    </section>

    {{-- LETTER --}}
    <section class="rpt-letter">
        <div class="rpt-wrap">
            <span class="rpt-label rv">A Letter From the Coalition</span>
            <h2 class="rpt-title rv">The year we started writing it all down</h2>
            <div class="rpt-cols rv">
                <p>The coalition began in 2023 with a filing cabinet's worth of borrowed files and a conviction: that the
                United States has always had political prisoners, and that the surest way to protect them is to write
                every case down where no one can lose it. Movements had kept these records before us — defense
                committees, prisoner-support networks, families with shoeboxes of court papers — but the records were
                scattered, and scattered records are how people disappear.</p>
                <p>So we spent our first year building the ledger. By December the database held 6,104 documented
                cases, from the Haymarket defendants of 1886 to the protest prosecutions still unfolding as we wrote.
                We opened the archive with the first thousand digitized documents. And we recorded 79 releases — people
                who came home this year, some after decades, whose entries we were finally able to mark closed.</p>
                <p>None of this belongs to us. The record belongs to the people in it, and to everyone who refuses to
                let this country forget what it has done. Thank you for building it with us.</p>
                <p class="rpt-sign">The Coordinating Committee <span>National Political Prisoner Coalition</span></p>
            </div>
        </div>
    </section>

    {{-- FREED — accordion list with swapping portrait --}}
    @php
    $freed = [
        ['name' => 'Ruchell Magee', 'slug' => 'ruchell-magee',
         'meta' => 'Released: August 2023 &middot; 61 years &middot; California',
         'photo' => '/storage/prisoners/ruchell-magee-6fdab5ba-c736-4b6f-bc3b-4c358a42c4f5.jpg',
         'credit' => 'Image: NPPC case file',
         'bio' => 'was the longest-held political prisoner in the United States, imprisoned almost continuously
            from 1963 and the sole surviving participant in the 1970 Marin County courthouse raid. He argued his
            own case for six decades, signing every filing &ldquo;Cinque.&rdquo; Granted compassionate release in
            August 2023 at age 84, he came home to Los Angeles and died that October &mdash; a free man, his entry
            in the census finally marked closed.'],
        ['name' => 'Polly H. Mann', 'slug' => 'polly-h-mann',
         'meta' => 'Record closed: 2023 &middot; 41 years &middot; Minnesota',
         'photo' => null, 'credit' => 'The census holds no photograph for this entry.',
         'bio' => 'co-founded Women Against Military Madness in Minneapolis in 1981 and was arrested again and
            again at Honeywell shareholder actions through that decade, the first time past age sixty. Her file
            &mdash; 41 years of arrests, jailings, and organizing &mdash; is one of the longest continuous records
            in the census, and it was marked closed in 2023.'],
        ['name' => 'Maumin Khabir', 'slug' => 'maumin-khabir',
         'meta' => 'Record closed: 2023 &middot; 38 years &middot; Illinois',
         'photo' => null, 'credit' => 'The census holds no photograph for this entry.',
         'bio' => 'was named by the government as a &ldquo;general&rdquo; of Chicago&rsquo;s El Rukn organization
            and convicted under RICO in the case alleging the group conspired with Libya &mdash; a prosecution
            built on paid informants inside the federal building itself. After decades imprisoned, his release
            entered the census in 2023, closing one of the record&rsquo;s longest-running Illinois entries.'],
        ['name' => 'John LaForge', 'slug' => 'john-laforge',
         'meta' => 'Released: 2023 &middot; 37 years &middot; Wisconsin',
         'photo' => null, 'credit' => 'The census holds no photograph for this entry.',
         'bio' => 'has coordinated Nukewatch from Luck, Wisconsin since the mid-1980s, and became the first
            American imprisoned in Germany for protesting U.S. nuclear weapons after go-in actions at
            B&uuml;chel Air Base. He walked out of Hamburg&rsquo;s Billwerder prison in 2023 and went straight
            back to work. His census entry spans 37 years of anti-nuclear resistance.'],
        ['name' => 'Ana Montes', 'slug' => 'ana-montes',
         'meta' => 'Released: January 2023 &middot; 22 years &middot; Texas',
         'photo' => '/storage/prisoners/ana-montes.jpg', 'credit' => 'Image: NPPC case file',
         'bio' => 'was a senior Defense Intelligence Agency analyst who passed U.S. war planning to Cuba out of
            conviction rather than payment, telling the court she had obeyed her conscience instead of the law.
            She served more than two decades &mdash; much of it in solitary confinement at FMC Carswell in
            Texas &mdash; and was released in January 2023.'],
        ['name' => 'David Williams', 'slug' => 'david-williams',
         'meta' => 'Released: 2023 &middot; 15 years &middot; New York',
         'photo' => null, 'credit' => 'The census holds no photograph for this entry.',
         'bio' => 'was one of the Newburgh Four, recruited by a paid FBI informant into a fictional synagogue
            bomb plot the government itself scripted and financed &mdash; drawn in while caring for his dying
            mother. The sentencing judge wrote that the government came up with the crime, provided the means,
            and removed every obstacle. He was granted compassionate release in 2023.'],
        ['name' => 'Onta Williams', 'slug' => 'onta-williams',
         'meta' => 'Released: 2023 &middot; 15 years &middot; New York',
         'photo' => null, 'credit' => 'The census holds no photograph for this entry.',
         'bio' => 'another of the Newburgh Four, was described by the sentencing judge as hapless and easily
            manipulated &mdash; a man swept into an FBI-scripted plot by the promise of money he never saw.
            He served 15 years of a 25-year mandatory sentence before compassionate release brought him home
            to New York in 2023.'],
        ['name' => 'Jerritt Pace', 'slug' => 'jerritt-pace',
         'meta' => 'Released: 2023 &middot; 4 years &middot; District of Columbia',
         'photo' => '/storage/prisoners/jerritt-pace.jpg', 'credit' => 'Image: NPPC case file',
         'bio' => 'was sentenced to federal prison for attempting to set fire to a Metropolitan Police
            Department precinct during the May 2020 uprising in Washington, DC &mdash; one of dozens of
            protest-arson prosecutions from that summer documented in the census. He completed his sentence
            and came home in 2023.'],
    ];
    @endphp
    <section class="rpt-freed">
        <div class="rpt-wrap rfx-grid">
            <div class="rv">
                <div class="rfx-kicker">They Came Home in 2023</div>
                <div id="rfx-list">
                    @foreach ($freed as $i => $f)
                    <div class="rfx-item{{ $i === 0 ? ' open' : '' }}">
                        <button type="button" class="rfx-q">{{ $f['name'] }} <span class="chev"></span></button>
                        <div class="rfx-a" @if ($i === 0) style="max-height: none" @endif>
                            <div class="rfx-meta">{!! $f['meta'] !!}</div>
                            @if ($f['photo'])
                                <div class="rfx-inline" style="background-image: url('{{ $f['photo'] }}')"></div>
                            @endif
                            <p><a href="/prisoner/{{ $f['slug'] }}">{{ $f['name'] }}</a> {!! $f['bio'] !!}</p>
                            <div class="rfx-credit">{{ $f['credit'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="rpt-freed-note">Seventy-nine releases entered the record in 2023, and every one stays
                    in the census &mdash; freedom is part of the record too.
                    <a href="/database">Explore the database &rarr;</a></div>
            </div>
            <div class="rfx-photo rv rv-fade" id="rfx-photo">
                @foreach ($freed as $i => $f)
                    @if ($f['photo'])
                        <div class="rfx-photo-img{{ $i === 0 ? ' on' : '' }}" style="background-image: url('{{ $f['photo'] }}')"></div>
                    @else
                        <div class="rfx-photo-img rfx-noimg{{ $i === 0 ? ' on' : '' }}">
                            <b>{{ mb_substr($f['name'], 0, 1) }}</b>
                            <small>The census holds no photograph for this entry</small>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    {{-- CHAPTER 1 --}}
    <section class="rpt-chapter" data-chapter>
        <div class="rpt-chapter-bg" data-parallax style="background-image:url('/storage/history/world-war-i.jpg')"></div>
        <div class="rpt-wrap rpt-chapter-body">
            <div class="rpt-chapter-num rv rv-fade">01</div>
            <h2 class="rv">Documenting the Record</h2>
            <div class="rpt-chapter-rule"></div>
            <p class="rv">A census of American political imprisonment, built case by case from dockets, prison
            rosters, and the files of the defense committees that came before us.</p>
        </div>
    </section>

    {{-- DATABASE FEATURE + STATS --}}
    <section class="rpt-feature">
        <div class="rpt-wrap">
            <span class="rpt-label rv">The Database</span>
            <h2 class="rpt-title rv">Six thousand cases, each with a name</h2>
            <div class="rpt-grid2">
                <div>
                    <p class="rv">Every entry in the census is a person: their charges, their court, the institution
                    that held them, and the sources that prove it. In 2023 we built the schema, recruited the first
                    volunteer researchers, and entered the founding cohorts — the Espionage Act defendants of the
                    1910s, the Smith Act and HUAC prosecutions, the COINTELPRO era, and the modern protest cases
                    arriving faster than any decade before them.</p>
                    <p class="rv">The rule that governs all of it: no guessing. Contested cases are flagged and
                    published with the argument, not just the verdict.</p>
                    <a class="rpt-btn rv" href="/database">Explore the database</a>
                </div>
                <div class="rpt-stats">
                    <div class="rv"><div class="rpt-stat-n" data-count="6104">0</div><div class="rpt-stat-l">cases documented by December 31, 2023</div></div>
                    <div class="rv" style="transition-delay:80ms"><div class="rpt-stat-n" data-count="3200">0</div><div class="rpt-stat-l">entries with a verified photograph</div></div>
                    <div class="rv" style="transition-delay:160ms"><div class="rpt-stat-n" data-count="700">0</div><div class="rpt-stat-l">institutions mapped, from county jails to ADX</div></div>
                    <div class="rv" style="transition-delay:240ms"><div class="rpt-stat-n" data-count="79">0</div><div class="rpt-stat-l">releases recorded in the coalition&rsquo;s first year</div></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ARCHIVE FEATURE --}}
    <section class="rpt-feature" style="padding-top: 0">
        <div class="rpt-wrap">
            <div class="rpt-grid2">
                <img class="rv rv-fade" src="{{ asset('images/imam-jamil-al-amin/bel-air-statement.jpg') }}" alt="A 1967 SNCC statement, one of the archive's first digitized documents">
                <div>
                    <span class="rpt-label rv">The Archive</span>
                    <h2 class="rpt-title rv">The paper behind every claim</h2>
                    <p class="rv">Behind the database sits the archive: trial transcripts, defense-committee
                    pamphlets, clemency petitions, and prison letters, each catalogued to the case it documents.
                    The first thousand documents went online this year — including the complete SNCC statement on
                    the 1967 prosecution of H. Rap Brown, the kind of paper that turns an assertion into a
                    citation.</p>
                    <a class="rpt-btn rv" href="/archive">Search the records</a>
                </div>
            </div>
            <div class="rpt-icons">
                @foreach ([
                    ['M6 2h9l5 5v15H6z M15 2v7h5', 'The charges', 'Every count as filed, and what became of each one.'],
                    ['M12 3 2 9h20z M4 9v9 M8 9v9 M12 9v9 M16 9v9 M20 9v9 M2 21h20', 'The court', 'Judge, prosecutor, and the docket that names them.'],
                    ['M3 21V8l4-4h10l4 4v13 M9 21v-6h6v6 M3 12h18', 'The institution', 'Where they were held, mapped and cross-linked.'],
                    ['M4 4h12v16H4z M8 8h4 M8 12h4 M8 16h4 M16 7l4-2v14l-4-2', 'The sources', 'Primary documents cited on every case page.'],
                ] as $i => [$path, $t, $d])
                    <div class="rpt-icon-card rv" style="transition-delay: {{ $i * 90 }}ms">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#8f97ff" stroke-width="1.6" stroke-linejoin="round"><path d="{{ $path }}"/></svg>
                        <h4>{{ $t }}</h4><p>{{ $d }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CHAPTER 2 --}}
    <section class="rpt-chapter" data-chapter>
        <div class="rpt-chapter-bg" data-parallax style="background-image:url('/storage/history/wounded-knee.jpg')"></div>
        <div class="rpt-wrap rpt-chapter-body">
            <div class="rpt-chapter-num rv rv-fade">02</div>
            <h2 class="rv">Counting the Cost</h2>
            <div class="rpt-chapter-rule"></div>
            <p class="rv">A century of political imprisonment, measured in years of human life — and in the
            people still inside.</p>
        </div>
    </section>

    {{-- GALLERY --}}
    <section class="rpt-gallery">
        <div class="rpt-wrap">
            <span class="rpt-label rv">From the Archive</span>
            <h2 class="rpt-title rv">The year in acquisitions</h2>
            <div class="rpt-gal-shell rv rv-fade">
                <button type="button" class="rpt-gal-btn" id="rpt-gal-prev" aria-label="Previous image">&larr;</button>
                <div class="rpt-gal-view">
                    <div class="rpt-gal-track" id="rpt-gal-track">
                        @foreach ([
                            ['/storage/history/coxeys-army.jpg', "Coxey's Army on the march to Washington, 1894 — Ray Stannard Baker, Library of Congress."],
                            ['/storage/history/scottsboro-nine.jpg', 'The Scottsboro defendants with attorney Samuel Leibowitz under National Guard watch, 1932.'],
                            ['/storage/history/bonus-army.jpg', 'Bonus Army veterans encamped on the Capitol lawn, summer 1932.'],
                            ['/storage/history/smith-act-trials.jpg', 'Convicted Communist Party leaders outside the Foley Square courthouse, 1949.'],
                            ['/storage/history/stop-cop-city.jpg', 'Banner and memorial to Tortuguita, Atlanta, January 2023 — the newest cohort in the census.'],
                        ] as [$src, $cap])
                            <figure class="rpt-gal-item" style="margin:0">
                                <img src="{{ $src }}" alt="">
                                <figcaption class="rpt-gal-cap">{{ $cap }}</figcaption>
                            </figure>
                        @endforeach
                    </div>
                </div>
                <button type="button" class="rpt-gal-btn" id="rpt-gal-next" aria-label="Next image">&rarr;</button>
            </div>
            <div class="rpt-gal-dots" id="rpt-gal-dots"></div>
        </div>
    </section>

    {{-- STORY PANELS --}}
    <section class="rpt-stories">
        <div class="rpt-wrap">
            <span class="rpt-label rv">Still Inside</span>
            <h2 class="rpt-title rv">The cases the census exists for</h2>
            <div class="rpt-story">
                <div class="rpt-story-img rv rv-fade"><img src="/storage/prisoners/leonard-peltier.jpg" alt="Leonard Peltier"></div>
                <div>
                    <span class="rpt-story-tag rv">47 years &middot; American Indian Movement</span>
                    <h3 class="rv">Leonard Peltier</h3>
                    <p class="rv">Convicted in 1977 on evidence the government's own prosecutors have disowned,
                    Peltier turned 79 this year at USP Coleman — the longest-held political prisoner in the
                    census, and the first case entered into it.</p>
                    <a class="rpt-btn rv" href="/prisoner/leonard-peltier">Read the case</a>
                </div>
            </div>
            <div class="rpt-story">
                <div class="rpt-story-img rv rv-fade"><img src="/storage/prisoners/mumia-abu-jamal.png" alt="Mumia Abu-Jamal"></div>
                <div>
                    <span class="rpt-story-tag rv">42 years &middot; Journalist, Black Panther Party</span>
                    <h3 class="rv">Mumia Abu-Jamal</h3>
                    <p class="rv">Philadelphia's most famous radio journalist entered his fifth decade in prison in
                    2023, still appealing a conviction built on recanted testimony — with six boxes of withheld
                    files surfaced by prosecutors as recently as 2019.</p>
                    <a class="rpt-btn rv" href="/prisoner/mumia-abu-jamal">Read the case</a>
                </div>
            </div>
            <div class="rpt-story">
                <div class="rpt-story-img rv rv-fade"><img src="{{ asset('images/imam-jamil-al-amin/portrait.jpg') }}" alt="Imam Jamil Al-Amin"></div>
                <div>
                    <span class="rpt-story-tag rv">23 years &middot; SNCC, Imam</span>
                    <h3 class="rv">Imam Jamil Al-Amin</h3>
                    <p class="rv">Once H. Rap Brown of SNCC, imprisoned since 2000 for a shooting another man
                    confessed to — a confession no jury has ever heard. His full case dossier was among the first
                    the coalition assembled.</p>
                    <a class="rpt-btn rv" href="/prisoner/imam-jamil-al-amin">Read the case</a>
                </div>
            </div>
        </div>
    </section>

    {{-- DISPATCH QUOTES --}}
    <section class="rpt-quotes">
        <div class="rpt-wrap">
            <span class="rpt-label rv">From the Dispatch</span>
            <h2 class="rpt-title rv">What readers wrote back</h2>
            <div class="rpt-quote-grid">
                <div class="rpt-quote-card rv"><p>&ldquo;I found my grandfather in the database. Deported in 1920.
                    We never knew the docket number. Now my family does.&rdquo;</p><span>— A reader in Michigan</span></div>
                <div class="rpt-quote-card rv" style="transition-delay:100ms"><p>&ldquo;I assign the census to my
                    students instead of a textbook chapter. It argues less and documents more.&rdquo;</p><span>— A history teacher in Oregon</span></div>
                <div class="rpt-quote-card rv" style="transition-delay:200ms"><p>&ldquo;Eleven dollars documents a
                    case. That's the best price on memory I've ever seen.&rdquo;</p><span>— A monthly donor in Ohio</span></div>
            </div>
        </div>
    </section>

    {{-- CHAPTER 3 --}}
    <section class="rpt-chapter" data-chapter>
        <div class="rpt-chapter-bg" data-parallax style="background-image:url('/storage/history/standing-rock.jpg')"></div>
        <div class="rpt-wrap rpt-chapter-body">
            <div class="rpt-chapter-num rv rv-fade">03</div>
            <h2 class="rv">Building the Coalition</h2>
            <div class="rpt-chapter-rule"></div>
            <p class="rv">A staff you can count on one hand, a volunteer corps in dozens of states, and the
            donors who paid for every server and stamp.</p>
        </div>
    </section>

    {{-- FINANCIALS --}}
    <section class="rpt-fin">
        <div class="rpt-wrap">
            <span class="rpt-label rv" style="color:#3b45e0">Financials</span>
            <h2 class="rpt-title rv">Statement of Activities — FY23</h2>
            <div class="rpt-fin-grid">
                <div class="rv">
                    <h3>Revenue</h3>
                    <svg class="rpt-fin-pie" width="210" height="210" viewBox="0 0 210 210">
                        <path d="M105,105 L105,2 A103,103 0 1 1 15.6,155.6 Z" fill="#5660fe"/>
                        <path d="M105,105 L15.6,155.6 A103,103 0 0 1 8.6,60.5 Z" fill="#23233f"/>
                        <path d="M105,105 L8.6,60.5 A103,103 0 0 1 27.5,35.4 Z" fill="#8f97ff"/>
                        <path d="M105,105 L27.5,35.4 A103,103 0 0 1 68.4,8.7 Z" fill="#c5c9f5"/>
                        <path d="M105,105 L68.4,8.7 A103,103 0 0 1 105,2 Z" fill="#dcd5c0"/>
                    </svg>
                    <table>
                        <tr><td><span class="rpt-dot" style="background:#5660fe"></span>Individuals</td><td class="amt">$1,102,400</td></tr>
                        <tr><td><span class="rpt-dot" style="background:#23233f"></span>Foundations</td><td class="amt">$561,300</td></tr>
                        <tr><td><span class="rpt-dot" style="background:#8f97ff"></span>Events</td><td class="amt">$48,200</td></tr>
                        <tr><td><span class="rpt-dot" style="background:#c5c9f5"></span>Store &amp; publications</td><td class="amt">$41,900</td></tr>
                        <tr><td><span class="rpt-dot" style="background:#dcd5c0"></span>Other income</td><td class="amt">$188,500</td></tr>
                        <tr class="tot"><td>Total Revenue</td><td class="amt">$1,942,300</td></tr>
                    </table>
                </div>
                <div class="rv" style="transition-delay:120ms">
                    <h3>Expenditures</h3>
                    <svg class="rpt-fin-pie" width="210" height="210" viewBox="0 0 210 210">
                        <path d="M105,105 L105,2 A103,103 0 1 1 6.6,133.2 Z" fill="#5660fe"/>
                        <path d="M105,105 L6.6,133.2 A103,103 0 0 1 27.5,35.4 Z" fill="#23233f"/>
                        <path d="M105,105 L27.5,35.4 A103,103 0 0 1 105,2 Z" fill="#c5c9f5"/>
                    </svg>
                    <table>
                        <tr><td><span class="rpt-dot" style="background:#5660fe"></span>Program</td><td class="amt">$1,244,700</td></tr>
                        <tr><td><span class="rpt-dot" style="background:#23233f"></span>Management &amp; General</td><td class="amt">$262,100</td></tr>
                        <tr><td><span class="rpt-dot" style="background:#c5c9f5"></span>Fundraising</td><td class="amt">$198,400</td></tr>
                        <tr class="tot"><td>Total Expenditures</td><td class="amt">$1,705,200</td></tr>
                    </table>
                </div>
            </div>
            <div class="rpt-fin-note rv">Figures exclude donated professional services. The full audited statement is
            available on request.</div>
        </div>
    </section>

    {{-- FAQ --}}
    <section class="rpt-faq">
        <div class="rpt-wrap">
            <span class="rpt-label rv">Questions</span>
            <h2 class="rpt-title rv">How the census works</h2>
            @foreach ([
                ['Who belongs in a census of political prisoners?', 'People imprisoned, exiled, or held awaiting trial in the United States where the politics of the accused — their speech, organizing, affiliation, or movement — is essential to understanding the prosecution. Contested cases are flagged and published with the argument on both sides.'],
                ['Where does the information come from?', 'Primary sources: court dockets, prison rosters, contemporaneous reporting, and the records of the defense committees that came before us. Every case page names its sources, and cases whose files were destroyed or sealed say so on the record.'],
                ['What if something is wrong?', 'Tell us. Every entry is reviewed twice before publication and corrections ship weekly. A census that cannot admit error cannot be trusted with anyone&rsquo;s history.'],
                ['How can I help?', 'Write to a prisoner, join the volunteer research corps, or become a sustaining donor — a case costs about eleven dollars to document. Start at the Get Involved page.'],
            ] as [$q, $a])
                <div class="rpt-faq-item rv">
                    <button type="button" class="rpt-faq-q">{{ $q }} <span class="chev">+</span></button>
                    <div class="rpt-faq-a"><p>{{ $a }}</p></div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- DONORS --}}
    <section class="rpt-donors">
        <div class="rpt-wrap">
            <span class="rpt-label rv">With Thanks</span>
            <h2 class="rpt-title rv">Fiscal Year 2023 Donors</h2>
            @php
                $donorTiers = json_decode(file_get_contents(database_path('data/report-2023-donors.json')), true);
            @endphp
            @foreach ($donorTiers as $ti => [$tier, $names])
                <div class="rpt-tier rv">
                    <h4>{{ $tier }}</h4>
                    <div class="rpt-donor-cols" data-donor-cols>
                        @foreach ($names as $ni => $n)
                            <div @if($ti === 3 && $ni >= 20) class="hid" @endif>{{ $n }}</div>
                        @endforeach
                    </div>
                    @if ($ti === 3)
                        <button type="button" class="rpt-btn rpt-loadmore" id="rpt-loadmore">Load more donors</button>
                    @endif
                </div>
            @endforeach
            <p class="rv" style="margin-top:40px; color:var(--dim); font-size:13.5px">Plus 640 sustaining members
            giving monthly, and everyone who asked to remain anonymous. Every name here built the record.</p>
        </div>
    </section>

    {{-- MEMBERS --}}
    <section class="rpt-members">
        <div class="rpt-wrap">
            <span class="rpt-label rv">The People</span>
            <h2 class="rpt-title rv">Who did the work</h2>
            <div class="rpt-mem-grid">
                <div class="rpt-mem rv">
                    <h4>Staff</h4>
                    <div><b>Mike McCorkle</b> <span>&mdash; Attorney</span></div>
                    <div><b>Brian Mulhearn</b> <span>&mdash; Research &amp; Operations</span></div>
                </div>
                <div class="rpt-mem rv" style="transition-delay:100ms">
                    <h4>Research Volunteers</h4>
                    <div>Sixty-two volunteers in 21 states built the census&rsquo;s first year — archivists, law
                    students, librarians, and families of the imprisoned. Because of the nature of this work, most
                    ask not to be named. They know who they are. So do the 6,104.</div>
                </div>
            </div>
        </div>
    </section>

    {{-- THANK YOU --}}
    <section class="rpt-thanks">
        <div class="rpt-wrap">
            <h2 class="rv">Thank You</h2>
            <p class="rv">The record exists because you decided it should. Here&rsquo;s to the year the writing-down
            began — and to everyone it will bring home.</p>
            <div class="row rv">
                <a class="rpt-btn solid" href="/database">Explore the database</a>
                <a class="rpt-btn" href="/annual-report">All annual reports</a>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Scroll reveals + chapter rules
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
        });
    }, { threshold: 0.18 });
    document.querySelectorAll('.rv, [data-chapter]').forEach(function (el) { io.observe(el); });

    // Hero crossfade
    (function () {
        var slides = document.querySelectorAll('.rpt-hero-slide');
        if (slides.length < 2 || reduced) return;
        var i = 0;
        setInterval(function () {
            slides[i].classList.remove('on');
            i = (i + 1) % slides.length;
            slides[i].classList.add('on');
        }, 6000);
    })();

    // Count-up stats
    var cio = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (!e.isIntersecting) return;
            cio.unobserve(e.target);
            var el = e.target, target = parseInt(el.dataset.count, 10);
            if (reduced) { el.textContent = target.toLocaleString(); return; }
            var t0 = null, dur = 1600;
            function tick(ts) {
                if (t0 === null) t0 = ts;
                var p = Math.min(1, (ts - t0) / dur);
                el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString();
                if (p < 1) requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);
        });
    }, { threshold: 0.6 });
    document.querySelectorAll('[data-count]').forEach(function (el) { cio.observe(el); });

    // Chapter background parallax
    if (!reduced) {
        var chapters = Array.prototype.slice.call(document.querySelectorAll('[data-chapter]'));
        var ticking = false;
        window.addEventListener('scroll', function () {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(function () {
                ticking = false;
                chapters.forEach(function (ch) {
                    var r = ch.getBoundingClientRect();
                    if (r.bottom < 0 || r.top > innerHeight) return;
                    var p = (r.top + r.height / 2 - innerHeight / 2) / innerHeight;
                    ch.querySelector('[data-parallax]').style.transform = 'translateY(' + (p * 46) + 'px)';
                });
            });
        }, { passive: true });
    }

    // Gallery carousel
    (function () {
        var track = document.getElementById('rpt-gal-track');
        if (!track) return;
        var n = track.children.length, i = 0;
        var dots = document.getElementById('rpt-gal-dots');
        for (var d = 0; d < n; d++) { var s = document.createElement('span'); s.className = 'rpt-gal-dot' + (d ? '' : ' on'); dots.appendChild(s); }
        function go(k) {
            i = (k + n) % n;
            track.style.transform = 'translateX(' + (-i * 100) + '%)';
            dots.querySelectorAll('.rpt-gal-dot').forEach(function (el, j) { el.classList.toggle('on', j === i); });
        }
        document.getElementById('rpt-gal-next').addEventListener('click', function () { go(i + 1); });
        document.getElementById('rpt-gal-prev').addEventListener('click', function () { go(i - 1); });
    })();

    // Freed accordion + portrait swap
    (function () {
        var items = document.querySelectorAll('#rfx-list .rfx-item');
        if (!items.length) return;
        var photos = document.querySelectorAll('#rfx-photo .rfx-photo-img');
        items.forEach(function (item, k) {
            item.querySelector('.rfx-q').addEventListener('click', function () {
                var wasOpen = item.classList.contains('open');
                items.forEach(function (o) {
                    o.classList.remove('open');
                    o.querySelector('.rfx-a').style.maxHeight = '0';
                });
                if (wasOpen) return;
                item.classList.add('open');
                var a = item.querySelector('.rfx-a');
                a.style.maxHeight = a.scrollHeight + 'px';
                photos.forEach(function (p, j) { p.classList.toggle('on', j === k); });
            });
        });
    })();

    // FAQ accordion
    document.querySelectorAll('.rpt-faq-q').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var item = btn.parentElement, a = item.querySelector('.rpt-faq-a');
            var open = item.classList.toggle('open');
            a.style.maxHeight = open ? a.scrollHeight + 'px' : '0';
        });
    });

    // Donor load-more
    var lm = document.getElementById('rpt-loadmore');
    if (lm) lm.addEventListener('click', function () {
        lm.closest('.rpt-tier').querySelectorAll('.hid').forEach(function (el) { el.classList.remove('hid'); });
        lm.remove();
    });
});
</script>
@endsection
