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
body.page-report-2026 .container { width: 100% !important; max-width: none !important; padding-left: 0 !important; padding-right: 0 !important; }
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

/* ── state notes: tab bar + crossfading panels ───────────── */
.r26-st { background: var(--paper); color: #14142b; padding: 96px 0 110px; }
.r26-st .r26-label { color: var(--acc); }
.r26-st h2 { color: #14142b; }
.r26-st-tabs { display: flex; flex-wrap: wrap; gap: 4px 34px; margin: 44px 0 54px; }
.r26-st-tab { position: relative; background: none; border: 0; padding: 18px 2px 6px; cursor: pointer;
  font-family: inherit; font-size: 15px; font-weight: 600; color: rgba(20,20,43,.75); transition: color .2s; }
.r26-st-tab:hover { color: #14142b; }
.r26-st-tab::before { content: ''; position: absolute; top: 0; left: 50%; transform: translateX(-50%) scale(0);
  width: 9px; height: 9px; background: var(--acc); transition: transform .25s; }
.r26-st-tab.on { font-weight: 800; color: #14142b; }
.r26-st-tab.on::before { transform: translateX(-50%) scale(1); }
.r26-st-panel { display: none; }
.r26-st-panel.on { display: grid; grid-template-columns: minmax(0, 7fr) minmax(0, 5fr); gap: 60px;
  animation: r26StIn .5s ease both; }
@keyframes r26StIn { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
@media (prefers-reduced-motion: reduce) { .r26-st-panel.on { animation: none; } }
.r26-st-head { display: flex; align-items: center; gap: 16px; margin-bottom: 22px; }
.r26-st-ico { width: 44px; flex: 0 0 auto; color: var(--acc); }
.r26-st-ico svg { width: 100%; height: auto; display: block; }
.r26-st-name { font-size: 13px; font-weight: 800; letter-spacing: .2em; text-transform: uppercase; color: #14142b; }
.r26-st-rule { height: 3px; width: min(560px, 100%); background: rgba(20,20,43,.85); margin-bottom: 34px; }
.r26-st-partner { font-size: 15px; font-weight: 800; color: #14142b; margin: 0 0 16px; }
.r26-st-claim { font-size: clamp(1.35rem, 2.4vw, 1.9rem); font-weight: 800; line-height: 1.35; color: #14142b; margin: 0 0 22px; letter-spacing: -.01em; }
.r26-st-foot { font-size: 14px; font-weight: 700; color: rgba(20,20,43,.75); margin: 4px 0 0; }
.r26-st-more { display: inline-block; margin-top: 26px; font-size: 14px; font-weight: 800; color: var(--acc); text-decoration: none; }
.r26-st-more:hover { text-decoration: underline; }
.r26-st-cards { display: flex; flex-direction: column; gap: 18px; align-self: start; padding-top: 86px; }
.r26-st-card { display: flex; justify-content: space-between; align-items: center; gap: 18px; text-decoration: none;
  background: #fff; border: 1px solid rgba(20,20,43,.08); border-radius: 6px; padding: 20px 22px;
  box-shadow: 0 10px 30px rgba(20,20,43,.06); transition: transform .2s, box-shadow .2s; }
.r26-st-card:hover { transform: translateY(-3px); box-shadow: 0 16px 40px rgba(20,20,43,.1); }
.r26-st-card .src { display: block; font-size: 11.5px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #14142b; margin-bottom: 8px; }
.r26-st-card .hl { display: block; font-size: 15px; color: rgba(20,20,43,.85); line-height: 1.5; }
.r26-st-card .go { flex: 0 0 auto; width: 34px; height: 34px; border-radius: 5px; background: var(--acc); color: #fff;
  display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; }
@media (max-width: 900px) {
  .r26-st-panel.on { grid-template-columns: 1fr; gap: 30px; }
  .r26-st-cards { padding-top: 0; }
  .r26-st-tabs { gap: 2px 22px; }
}

/* ── faces of the year: expanding profile columns ────────── */
.r26-pf { background: var(--paper); padding: 0 0 110px; }
.r26-pf-strip { display: flex; height: 780px; border-radius: 10px; overflow: hidden; box-shadow: 0 24px 70px rgba(20,20,43,.14); }
.r26-pf-item { position: relative; flex: 1; min-width: 0; cursor: pointer; overflow: hidden;
  background: linear-gradient(180deg, #c9cdf3, #b9beea); transition: flex .6s cubic-bezier(.22,1,.36,1); border-right: 1px solid rgba(20,20,43,.12); }
.r26-pf-item:last-child { border-right: 0; }
.r26-pf-item.on { flex: 6.2; cursor: default; background: #f6f7fd; }
.r26-pf-vname { position: absolute; top: 26px; left: 50%; transform: translateX(-50%); writing-mode: vertical-rl;
  font-size: clamp(1.1rem, 1.8vw, 1.6rem); font-weight: 800; color: #14142b; white-space: nowrap; letter-spacing: -.01em;
  transition: opacity .3s; }
.r26-pf-item.on .r26-pf-vname { opacity: 0; }
.r26-pf-thumb { position: absolute; left: 0; right: 0; bottom: 0; height: 42%; background: center top / cover no-repeat; transition: opacity .3s; }
.r26-pf-item.on .r26-pf-thumb { opacity: 0; }
.r26-pf-body { position: absolute; inset: 0; display: flex; flex-direction: column; opacity: 0; pointer-events: none; transition: opacity .45s .25s; }
.r26-pf-item.on .r26-pf-body { opacity: 1; pointer-events: auto; }
.r26-pf-media { flex: 0 0 46%; background: center 22% / cover no-repeat; }
.r26-pf-text { flex: 1; padding: 30px 34px 26px; overflow-y: auto; }
.r26-pf-text h3 { font-size: clamp(1.3rem, 2.2vw, 1.8rem); font-weight: 800; color: #14142b; margin: 0 0 12px; }
.r26-pf-meta { font-size: 12.5px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #14142b; line-height: 1.8; margin: 0 0 14px; }
.r26-pf-text p { font-size: 14.5px; line-height: 1.7; color: rgba(20,20,43,.85); margin: 0 0 12px; }
.r26-pf-text p a { color: inherit; text-underline-offset: 3px; }
.r26-pf-hint { text-align: right; font-size: 11.5px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: rgba(20,20,43,.55); margin-top: 6px; }
@media (max-width: 900px) {
  .r26-pf-strip { flex-direction: column; height: auto; }
  .r26-pf-item { flex: none; height: 64px; border-right: 0; border-bottom: 1px solid rgba(20,20,43,.12); transition: height .5s cubic-bezier(.22,1,.36,1); }
  .r26-pf-item.on { height: 660px; }
  .r26-pf-vname { writing-mode: horizontal-tb; top: 50%; left: 22px; transform: translateY(-50%); }
  .r26-pf-thumb { left: auto; right: 0; top: 0; bottom: 0; width: 130px; height: auto; }
  .r26-pf-media { flex: 0 0 240px; }
}

/* ── remembering: memorial tribute with audio ────────────── */
.r26-mem2 { background: var(--paper); color: #14142b; padding: 30px 0 120px; }
.r26-mem2-grid { display: grid; grid-template-columns: minmax(0, 6fr) minmax(0, 6fr); gap: 70px; align-items: center; }
.r26-mem2 h2 { font-size: clamp(1.7rem, 3.2vw, 2.5rem); font-weight: 800; color: #14142b; margin: 0 0 24px; letter-spacing: -.01em; }
.r26-mem2 p { font-size: 15.5px; line-height: 1.75; color: rgba(20,20,43,.88); margin: 0 0 16px; }
.r26-mem2 p a { color: inherit; text-underline-offset: 3px; }
.r26-mem2-label { font-size: 12px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #14142b; margin: 34px 0 18px; }
.r26-wave { display: flex; align-items: center; gap: 5px; height: 76px; margin-bottom: 18px; }
.r26-wave i { display: block; width: 7px; border-radius: 4px; background: var(--acc); height: calc(14px + var(--h) * 52px); transform-origin: center; }
.r26-mem2.playing .r26-wave i { animation: r26Wave 1.1s ease-in-out infinite; animation-delay: calc(var(--d) * -.12s); }
@keyframes r26Wave { 0%,100% { transform: scaleY(.55); } 50% { transform: scaleY(1.15); } }
@media (prefers-reduced-motion: reduce) { .r26-mem2.playing .r26-wave i { animation: none; } }
.r26-mem2-player { display: flex; align-items: center; gap: 16px; }
.r26-play { width: 46px; height: 46px; border-radius: 50%; border: 2px solid var(--acc); background: transparent; cursor: pointer;
  display: flex; align-items: center; justify-content: center; color: var(--acc); transition: background .2s, color .2s; }
.r26-play:hover { background: var(--acc); color: #fff; }
.r26-play .tri { width: 0; height: 0; border-left: 13px solid currentColor; border-top: 8px solid transparent; border-bottom: 8px solid transparent; margin-left: 3px; }
.r26-play .pause { display: none; width: 12px; height: 15px; border-left: 4px solid currentColor; border-right: 4px solid currentColor; }
.r26-mem2.playing .r26-play .tri { display: none; }
.r26-mem2.playing .r26-play .pause { display: block; }
.r26-time { font-size: 14.5px; font-weight: 700; color: rgba(20,20,43,.8); font-variant-numeric: tabular-nums; }
.r26-collage { position: relative; min-height: 520px; }
.r26-collage .ring { position: absolute; right: 4%; top: 8%; width: 74%; aspect-ratio: 1; border: 2px solid rgba(86,96,254,.5); border-radius: 50%; }
.r26-collage .ph-main { position: absolute; right: 0; bottom: 0; width: 66%; aspect-ratio: 4/5; background: center top / cover no-repeat;
  box-shadow: 0 26px 70px rgba(20,20,43,.25); }
.r26-collage .ph-duo { position: absolute; left: 2%; top: 0; width: 44%; aspect-ratio: 4/5; background: center top / cover no-repeat;
  filter: grayscale(1) sepia(1) hue-rotate(202deg) saturate(2.4) brightness(.92); box-shadow: 0 20px 50px rgba(20,20,43,.22); }
.r26-collage .ph-cap { position: absolute; left: 4%; bottom: 6%; font-size: 12px; color: rgba(20,20,43,.55); max-width: 30%; line-height: 1.6; }
@media (max-width: 900px) {
  .r26-mem2-grid { grid-template-columns: 1fr; gap: 46px; }
  .r26-collage { min-height: 0; height: 420px; }
}

/* ── thank you ───────────────────────────────────────────── */
.r26-thanks { padding: 130px 0 90px; text-align: center; background: radial-gradient(ellipse at 50% 30%, #1c1c46, var(--deep)); }
.r26-thanks h2 { font-size: clamp(3rem, 8vw, 5.5rem); font-weight: 900; color: #fff; margin: 0 0 24px; }
.r26-thanks p { color: var(--dim); max-width: 60ch; margin: 0 auto 34px; }
.r26-credits { margin-top: 80px; font-size: 12px; color: rgba(236,236,242,.4); max-width: 900px; margin-left: auto; margin-right: auto; line-height: 1.8; text-align: left; }

/* --------------------------------
File#: _2_tabbed-features
Title: Tabbed Features
Descr: A list of features filterable using a tabbed navigation
Usage: codyhouse.co/license
Adapted to the r26 dark theme; the CodyHouse global reset is omitted
(the site provides its own), and the component is scoped to the
"keep exploring" band at the foot of the report.
-------------------------------- */
.r26-explore { background: var(--deep); padding: 100px 0 120px; }
.r26-explore {
  --tx9-color-primary-hsl: 236, 99%, 67%;
  --tx9-color-contrast-low-hsl: 237, 10%, 62%;
  --tx9-color-contrast-high-hsl: 240, 21%, 94%;
  --tx9-color-contrast-higher-hsl: 240, 21%, 94%;
  --tx9-space-xs: 0.5rem; --tx9-space-sm: 0.75rem; --tx9-space-md: 1.25rem; --tx9-space-xl: 3.25rem;
  --tx9-text-xs: 0.694rem; --tx9-text-sm: 0.833rem;
}
@media (min-width: 64rem) {
  .r26-explore {
    --tx9-space-xs: 0.75rem; --tx9-space-sm: 1.125rem; --tx9-space-md: 2rem; --tx9-space-xl: 5.125rem;
    --tx9-text-xs: 0.8rem; --tx9-text-sm: 1rem;
  }
}

.tab-features__controls-list { position: relative; display: flex; align-items: center; overflow: auto; counter-reset: tab-features-list; }
.tab-features__controls-list::after { content: ""; position: absolute; left: 0; bottom: 0; width: 100%; height: 1px; background-color: hsla(var(--tx9-color-contrast-higher-hsl), 0.1); }
.tab-features__control-wrapper { counter-increment: tab-features-list; }
.tab-features__control { position: relative; display: block; padding: var(--tx9-space-sm) var(--tx9-space-xl) var(--tx9-space-sm) var(--tx9-space-sm); color: hsl(var(--tx9-color-contrast-higher-hsl)); text-decoration: none; font-weight: 500; white-space: nowrap; transition: background 0.2s; }
.tab-features__control::before { content: "0" counter(tab-features-list); font-size: var(--tx9-text-xs); display: block; color: hsl(var(--tx9-color-contrast-low-hsl)); margin-bottom: 4px; }
.tab-features__control::after { content: ""; position: absolute; bottom: 0px; left: 0; height: 1px; width: 100%; }
.tab-features__control:hover { background-color: hsla(var(--tx9-color-contrast-higher-hsl), 0.045); }
.tab-features__control[aria-selected=true]::before { color: hsl(var(--tx9-color-primary-hsl)); }
.tab-features__control[aria-selected=true]::after { background-color: hsl(var(--tx9-color-primary-hsl)); }
.tab-features__panels { position: relative; }
.tab-features__panel { opacity: 0; padding-top: var(--tx9-space-md); }
.tabs--no-interaction .tab-features__panel { animation-duration: 0s; animation-delay: 0s; }
.tab-features__img { display: block; width: 100%; aspect-ratio: 21 / 9; object-fit: cover; border-radius: 10px; filter: grayscale(35%) contrast(1.04); }
.tab-features__caption { font-size: var(--tx9-text-sm); color: hsla(var(--tx9-color-contrast-low-hsl), 1); margin-top: var(--tx9-space-xs); }
.tab-features__caption a { color: var(--acc2); }
.tab-features__panel--display { opacity: 0; animation: tab-features-panel-entry-anim 0.5s 0.2s cubic-bezier(0.215, 0.61, 0.355, 1) forwards; }
.tab-features__panel--hide { position: absolute; visibility: hidden; top: 0; width: 100%; transition: position 0s 0.5s, visibility 0s 0.5s; animation: tab-features-panel-exit-anim 0.5s cubic-bezier(0.215, 0.61, 0.355, 1); }
@keyframes tab-features-panel-entry-anim {
  0% { opacity: 0; transform: translateY(-20px); }
  100% { opacity: 1; transform: translateY(0); }
}
@keyframes tab-features-panel-exit-anim {
  0% { opacity: 1; transform: translateY(0px); }
  100% { opacity: 0; transform: translateY(20px); }
}
@media (prefers-reduced-motion: reduce) {
  .tab-features__panel--display, .tab-features__panel--hide { animation-duration: 0s; animation-delay: 0s; }
}

/* --------------------------------
File#: _1_tilted-img-slideshow
Title: Tilted Image Slideshow
Descr: A slideshow plugin to loop through a list of 3 tilted images
Usage: codyhouse.co/license
Adapted to the r26 theme; the CodyHouse global reset is omitted and
the component is scoped to the "year in frames" band.
-------------------------------- */
.r26-frames { background: var(--navy); padding: 100px 0; overflow: hidden; }
.r26-frames {
  --tm0-color-black-hsl: 240, 29%, 8%;
  --tm0-color-white-hsl: 240, 21%, 94%;
  --tm0-space-2xs: 0.5625rem; --tm0-space-sm: 1.125rem; --tm0-space-md: 2rem;
  --tm0-text-sm: 1rem;
  --tilted-slideshow-translate-x: 0px;
  --tilted-slideshow-translate-z: 0px;
  --tilted-slideshow-rotate-z: 0deg;
}
.r26-frames .grid { display: grid; grid-template-columns: 1fr 1.05fr; gap: 64px; align-items: center; }
@media (max-width: 900px) { .r26-frames .grid { grid-template-columns: 1fr; gap: 44px; } }
.r26-frames-caption { font-size: 14px; color: var(--dim); margin-top: 34px; min-height: 1.5em; position: relative; z-index: 6; }

.tm0-perspective-xs { perspective: 250px; }
.tm0-position-relative { position: relative; }
.tm0-sr-only { position: absolute; clip: rect(1px, 1px, 1px, 1px); clip-path: inset(50%); width: 1px; height: 1px; overflow: hidden; padding: 0; border: 0; white-space: nowrap; }

.tilted-slideshow__item {
  position: absolute; top: 0; left: 0;
  transform: translateX(var(--tilted-slideshow-translate-x)) translateZ(var(--tilted-slideshow-translate-z)) rotateZ(var(--tilted-slideshow-rotate-z));
  transition: transform 0.35s, opacity 0.35s;
}
.tilted-slideshow__item img { border-radius: 10px; box-shadow: 0 26px 70px rgba(0,0,0,.5); width: 100%; aspect-ratio: 4 / 3; object-fit: cover; }
.tilted-slideshow__item--top { position: relative; z-index: 3; }
.tilted-slideshow__item--middle { z-index: 2; }
.tilted-slideshow__item--bottom { z-index: 1; }
.tilted-slideshow__item:nth-of-type(2) { --tilted-slideshow-rotate-z: -10deg; }
.tilted-slideshow__item:nth-of-type(3) { --tilted-slideshow-rotate-z: 10deg; }
.tilted-slideshow__item--middle { --tilted-slideshow-translate-z: -10px; }
.tilted-slideshow__item--bottom { --tilted-slideshow-translate-z: -20px; }
.tilted-slideshow__item--move-out { position: absolute; z-index: 4; opacity: 0; --tilted-slideshow-translate-x: 50px; }
.tilted-slideshow__item--move-in { opacity: 0; --tilted-slideshow-translate-x: -50px; }
.tilted-slideshow__item {
  cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='64' height='64' viewBox='0 0 64 64'%3E%3Ccircle cx='32' cy='32' r='30' opacity='0.9'/%3E%3Cline x1='15' y1='31' x2='47' y2='31' fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2'/%3E%3Cpolyline points='37 21 47 31 37 41' fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2'/%3E%3C/svg%3E") 32 32, pointer;
}
.tilted-slideshow__touch-hint {
  position: absolute; top: 50%; left: 50%; transform: translateX(-50%) translateY(-50%); z-index: 5;
  font-size: var(--tm0-text-sm); background-color: hsla(var(--tm0-color-black-hsl), 0.9); color: hsl(var(--tm0-color-white-hsl));
  padding: var(--tm0-space-2xs) var(--tm0-space-sm); border-radius: 50em;
  -webkit-font-smoothing: antialiased; cursor: default;
}
@media (pointer: fine) { .tilted-slideshow__touch-hint { display: none; } }
.tilted-slideshow--interacted .tilted-slideshow__touch-hint { display: none; }
@media (prefers-reduced-motion: reduce) { .tilted-slideshow__item { transition: none; } }

/* --------------------------------
File#: _1_card-v12
Title: Card v12
Descr: Container of information used as teaser for further content exploration
Usage: codyhouse.co/license
Adapted to the r26 theme as the "past reports" band; the CodyHouse
global reset is omitted and only the utilities the card uses are
included, scoped to the band.
-------------------------------- */
.r26-pastreports { background: var(--deep); padding: 100px 0 130px; }
.r26-pastreports {
  --cf5-color-primary-hsl: 236, 99%, 67%;
  --cf5-color-contrast-high-hsl: 230, 7%, 23%;
  --cf5-color-contrast-higher-hsl: 230, 13%, 9%;
  --cf5-color-bg-light-hsl: 0, 0%, 100%;
  --cf5-color-contrast-lower-hsl: 240, 6%, 78%;
  --cf5-space-xs: 0.75rem; --cf5-space-sm: 1.125rem; --cf5-space-md: 2rem;
  --cf5-text-md: 1.4rem; --cf5-text-xs: 0.8rem;
}
.r26-pastreports .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 34px; margin-top: 44px; }
@media (max-width: 980px) { .r26-pastreports .grid { grid-template-columns: 1fr; max-width: 520px; margin-left: auto; margin-right: auto; } }

.card-v12 {
  --card-v12-transition-duration: .4s;
  font-family: system-ui, -apple-system, sans-serif;
  position: relative; z-index: 1; text-decoration: none; color: hsl(var(--cf5-color-contrast-higher-hsl));
  display: block; background-color: hsla(var(--cf5-color-bg-light-hsl), 0.95);
  will-change: transform; transition: box-shadow, transform; transition-duration: var(--card-v12-transition-duration);
}
@supports ((-webkit-backdrop-filter: blur(10px)) or (backdrop-filter: blur(10px))) {
  .card-v12 { background-color: hsla(var(--cf5-color-bg-light-hsl), 0.97); -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px); }
}
.card-v12::after { content: ""; position: absolute; z-index: 3; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; border-radius: inherit; box-shadow: inset 0 0 0.5px 1px hsla(0, 0%, 100%, 0.075); }
.card-v12:hover { box-shadow: 0 0.9px 1.5px rgba(0,0,0,0.03), 0 3.1px 5.5px rgba(0,0,0,0.08), 0 14px 25px rgba(0,0,0,0.35); transform: translateY(-2px); }
.card-v12__figure { margin: 0; position: relative; z-index: 2; clip-path: inset(0% var(--cf5-space-sm) 0% 0% round 0% 0.25em 0.25em 0%); will-change: clip-path; transition: clip-path var(--card-v12-transition-duration); }
.card-v12__figure img { will-change: transform; transition: transform var(--card-v12-transition-duration); aspect-ratio: 4 / 5; object-fit: cover; }
.card-v12:hover .card-v12__figure { clip-path: inset(0% calc(var(--cf5-space-sm) + 80px) 0% 0% round 0% 0.25em 0.25em 0%); }
.card-v12:hover .card-v12__figure img { transform: translateX(-40px); }
.card-v12__separator { display: block; width: 32px; }
.card-v12 .card-v12__icon { position: absolute; z-index: 1; right: var(--cf5-space-sm); top: calc(50% - 30px); height: 60px; width: 60px; opacity: 0; transform: translateX(-20px); will-change: transform; transition: transform, opacity; transition-duration: var(--card-v12-transition-duration); color: hsl(var(--cf5-color-contrast-higher-hsl)); }
.card-v12__icon .icon-group > * { transform-origin: 57px 30px; will-change: transform; transition: transform var(--card-v12-transition-duration); }
.card-v12__icon .icon-group > *:nth-child(2) { transform: rotate(35deg); }
.card-v12__icon .icon-group > *:nth-child(3) { transform: rotate(-35deg); }
.card-v12:hover .card-v12__icon { opacity: 1; transform: translateX(0); }
.card-v12:hover .card-v12__icon .icon-group > *:nth-child(2),
.card-v12:hover .card-v12__icon .icon-group > *:nth-child(3) { transform: rotate(0); }

.cf5-letter-spacing-lg { letter-spacing: 0.1em; }
.cf5-text-uppercase { text-transform: uppercase; }
.cf5-color-contrast-higher { --cf5-color-o: 1; color: hsla(var(--cf5-color-contrast-higher-hsl), var(--cf5-color-o, 1)); }
.cf5-text-xs { font-size: var(--cf5-text-xs); }
.cf5-margin-y-xs { margin-top: var(--cf5-space-xs); margin-bottom: var(--cf5-space-xs); }
.cf5-margin-x-auto { margin-left: auto; margin-right: auto; }
.cf5-border-top { --cf5-border-o: 1; border-top: var(--cf5-border-width, 1px) var(--cf5-border-style, solid) hsla(var(--cf5-color-contrast-lower-hsl), var(--cf5-border-o, 1)); }
.cf5-text-md { font-size: var(--cf5-text-md); font-weight: 700; line-height: 1.2; }
.cf5-padding-md { padding: var(--cf5-space-md); }
.cf5-text-center { text-align: center; }
.cf5-width-100pc { width: 100%; }
.cf5-block { display: block; }
.cf5-radius-sm { border-radius: 0.125em; }
.cf5-position-relative { position: relative; }
.cf5-shadow-sm { box-shadow: 0 0.3px 0.4px rgba(0,0,0,0.025), 0 0.9px 1.5px rgba(0,0,0,0.05), 0 3.5px 6px rgba(0,0,0,0.1); }
.cf5-radius-lg { border-radius: 0.5em; }
.cf5-padding-top-sm { padding-top: var(--cf5-space-sm); }
.cf5-border-contrast-higher { border-color: hsla(var(--cf5-color-contrast-higher-hsl), var(--cf5-border-o, 1)); }
.cf5-border-opacity-10pc { --cf5-border-o: 0.1; }
.cf5-color-opacity-50pc { --cf5-color-o: 0.5; }
@media (prefers-reduced-motion: reduce) {
  .card-v12, .card-v12__figure, .card-v12__figure img, .card-v12 .card-v12__icon, .card-v12__icon .icon-group > * { transition: none; }
}

/* --------------------------------
File#: _1_svg-image-clip
Title: Svg Image Clip
Usage: codyhouse.co/license
Adapted to the r26 theme as the closing "join the work" band; the
CodyHouse global reset is omitted and only the utilities the section
uses are included, scoped to the band ("@md" class names renamed
"-md" to stay Blade-safe).
-------------------------------- */
.r26-join { background: var(--navy); padding: 20px 0 0; }
.r26-join {
  --sj0-color-primary-hsl: 236, 99%, 67%;
  --sj0-color-primary-darker-hsl: 236, 60%, 38%;
  --sj0-color-primary-light-hsl: 236, 100%, 73%;
  --sj0-color-white-hsl: 0, 0%, 100%;
  --sj0-color-bg-hsl: 240, 29%, 8%;
  --sj0-color-bg-dark-hsl: 240, 22%, 16%;
  --sj0-color-contrast-higher-hsl: 240, 21%, 94%;
  --sj0-color-contrast-medium-hsl: 237, 12%, 68%;
  --sj0-space-2xs: 0.5625rem; --sj0-space-xs: 0.75rem; --sj0-space-sm: 1.125rem;
  --sj0-space-md: 2rem; --sj0-space-xl: 5.125rem;
  --sj0-text-sm: 1rem;
  color: hsl(var(--sj0-color-contrast-higher-hsl));
}
.r26-join .sj0-text-component h2 { color: hsl(var(--sj0-color-contrast-higher-hsl)); font-weight: 900; font-size: clamp(1.7rem, 3vw, 2.4rem); letter-spacing: -.015em; }
.r26-join svg image { width: 100%; height: auto; }

.sj0-btn { position: relative; display: inline-flex; justify-content: center; align-items: center; font-size: 1em; white-space: nowrap; text-decoration: none; background: hsl(var(--sj0-color-bg-dark-hsl)); color: hsl(var(--sj0-color-contrast-higher-hsl)); cursor: pointer; line-height: 1.2; -webkit-font-smoothing: antialiased; transition: all 0.2s ease; will-change: transform; padding: var(--sj0-space-2xs) var(--sj0-space-sm); border-radius: 0.25em; }
.sj0-btn:focus-visible { box-shadow: 0px 0px 0px 2px hsl(var(--sj0-color-bg-hsl)), 0px 0px 0px 4px hsla(var(--sj0-color-contrast-higher-hsl), 0.15); outline: none; }
.sj0-btn:active { transform: translateY(2px); }
.sj0-btn--primary { background: hsl(var(--sj0-color-primary-hsl)); color: hsl(var(--sj0-color-white-hsl)); box-shadow: inset 0px 1px 0px hsla(var(--sj0-color-white-hsl), 0.15), 0px 1px 3px hsla(var(--sj0-color-primary-darker-hsl), 0.25), 0px 2px 6px hsla(var(--sj0-color-primary-darker-hsl), 0.1), 0px 6px 10px -2px hsla(var(--sj0-color-primary-darker-hsl), 0.25); }
.sj0-btn--primary:hover { background: hsl(var(--sj0-color-primary-light-hsl)); }

.sj0-color-inherit { color: inherit; }
.sj0-items-center { align-items: center; }
.sj0-gap-sm { gap: var(--sj0-space-sm); }
.sj0-gap-md { gap: var(--sj0-space-md); }
.sj0-flex-wrap { flex-wrap: wrap; }
.sj0-flex { display: flex; }
.sj0-margin-top-sm { margin-top: var(--sj0-space-sm); }
.sj0-color-contrast-medium { color: hsla(var(--sj0-color-contrast-medium-hsl), 1); }
.sj0-text-sm { font-size: var(--sj0-text-sm); }
.sj0-text-component :where(h1, h2, h3, h4) { line-height: 1.2; margin-top: var(--sj0-space-md); margin-bottom: var(--sj0-space-sm); }
.sj0-text-component :where(p, blockquote, ul li, ol li) { line-height: 1.58; }
.sj0-text-component :where(ul, ol, p, blockquote) { margin-bottom: var(--sj0-space-sm); }
.sj0-text-component > *:first-child { margin-top: 0; }
.sj0-text-component > *:last-child { margin-bottom: 0; }
.sj0-grid { display: grid; grid-template-columns: repeat(12, 1fr); }
.sj0-grid > * { min-width: 0; grid-column-end: span 12; }
.sj0-container { width: calc(100% - 2*var(--sj0-space-md)); margin-left: auto; margin-right: auto; max-width: 1180px; }
.sj0-padding-y-xl { padding-top: var(--sj0-space-xl); padding-bottom: var(--sj0-space-xl); }
.sj0-z-index-1 { z-index: 1; }
.sj0-position-relative { position: relative; }
@media (min-width: 64rem) { .sj0-col-6-md { grid-column-end: span 6; } }

/* --------------------------------
File#: _1_sticky-hero
Title: Sticky Hero
Descr: A sticky hero section that reveals its content on scroll
Usage: codyhouse.co/license
Adapted to the r26 theme; global reset omitted, utilities scoped.
-------------------------------- */
.r26-sticky {
  --sy8-color-bg-hsl: 240, 29%, 8%;
  --sy8-space-md: 2rem; --sy8-space-sm: 1.125rem;
}
.sticky-hero { position: relative; z-index: 1; }
.sticky-hero__media {
  position: relative; position: sticky; z-index: 1; top: 0; width: 100%; height: 100vh; overflow: hidden;
  background-size: cover; background-position: center; background-repeat: no-repeat;
  transition: transform 0.5s cubic-bezier(0.645, 0.045, 0.355, 1);
  transform: translateZ(0);
}
.sticky-hero--overlay-layer .sticky-hero__media::after {
  content: ""; position: absolute; top: 0; left: 0; height: 100%; width: 100%; opacity: 0;
  background-color: hsl(var(--sy8-color-bg-hsl)); transition: opacity 1s;
}
.sticky-hero--media-is-fixed.sticky-hero--overlay-layer .sticky-hero__media::after { opacity: 0.65; }
.sticky-hero--media-is-fixed.sticky-hero--scale .sticky-hero__media { transform: scale(0.9); }
.sticky-hero__content { position: relative; z-index: 2; height: 100vh; display: flex; justify-content: center; align-items: center; transform: translateZ(0); }
.sy8-text-component :where(h1, h2, h3, h4) { line-height: 1.2; margin-top: var(--sy8-space-md); margin-bottom: var(--sy8-space-sm); }
.sy8-text-component :where(p) { line-height: 1.58; margin-bottom: var(--sy8-space-sm); }
.sy8-text-component > *:first-child { margin-top: 0; }
.sy8-text-component > *:last-child { margin-bottom: 0; }
.sy8-max-width-sm { max-width: 48rem; }
.sy8-container { width: calc(100% - 2*var(--sy8-space-md)); margin-left: auto; margin-right: auto; }
.sy8-text-center { text-align: center; }
.r26-sticky .sticky-hero__content h2 { color: #fff; font-weight: 900; font-size: clamp(2.2rem, 5vw, 4rem); letter-spacing: -.02em; text-shadow: 0 4px 30px rgba(0,0,0,.5); }
.r26-sticky .sticky-hero__content p { color: rgba(255,255,255,.85); font-size: clamp(1.05rem, 1.6vw, 1.3rem); text-shadow: 0 2px 18px rgba(0,0,0,.5); }
@media (prefers-reduced-motion: reduce) { .sticky-hero__media, .sticky-hero--overlay-layer .sticky-hero__media::after { transition: none; } }

/* --------------------------------
File#: _1_diamond-grid
Title: Diamond Grid
Descr: Diamond shaped image gallery
Usage: codyhouse.co/license
Adapted to the r26 theme (links variant); global reset omitted,
utilities scoped.
-------------------------------- */
.r26-diamonds { background: var(--deep); padding: 40px 0 140px; overflow: hidden; }
.r26-diamonds {
  --dd6-color-bg-hsl: 0, 0%, 100%;
  --dd6-color-contrast-higher-hsl: 240, 29%, 8%;
  --dd6-color-contrast-lower-hsl: 240, 22%, 16%;
  --diamond-grid-gap: 10px;
}
.r26-diamonds .stage { max-width: 620px; margin: 0 auto; }
.dd6-icon { height: var(--dd6-size, 1em); width: var(--dd6-size, 1em); display: inline-block; color: inherit; fill: currentColor; line-height: 1; flex-shrink: 0; max-width: initial; }
.dd6-icon--xl { --dd6-size: 64px; }
.diamond-grid { position: relative; z-index: 1; }
.diamond-grid__inner { display: flex; flex-wrap: wrap; transform: scale(0.71) rotate(-45deg); }
.diamond-grid__item { position: relative; display: block; width: calc(50% - var(--diamond-grid-gap)/2); padding-bottom: calc(50% - var(--diamond-grid-gap)/2); overflow: hidden; }
.diamond-grid__item:nth-child(1), .diamond-grid__item:nth-child(3) { margin-right: var(--diamond-grid-gap); }
.diamond-grid__item:nth-child(1), .diamond-grid__item:nth-child(2) { margin-bottom: var(--diamond-grid-gap); }
.diamond-grid__img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; transform: scale(1.414) rotate(45deg); }
.diamond-grid__item--link { text-decoration: none; }
.diamond-grid__item--link::after { content: ""; position: absolute; z-index: 1; top: 0; left: 0; width: 100%; height: 100%; background-color: hsla(var(--dd6-color-contrast-higher-hsl), 0); transition: 0.3s; }
.diamond-grid__item--link:hover::after { background-color: hsla(var(--dd6-color-contrast-higher-hsl), 0.85); }
.diamond-grid__item--link:hover .diamond-grid__icon :nth-child(1),
.diamond-grid__item--link:hover .diamond-grid__icon :nth-child(2) { opacity: 1; }
.diamond-grid__item--link:hover .diamond-grid__icon :nth-child(1) { transform: scaleY(1); }
.diamond-grid__item--link:hover .diamond-grid__icon :nth-child(2) { transform: scale(1); }
.diamond-grid__icon { position: absolute; z-index: 2; font-size: var(--dd6-size); top: calc(50% - 0.5em); left: calc(50% - 0.5em); transform: scale(1.4) rotate(45deg); }
.diamond-grid__icon :nth-child(1), .diamond-grid__icon :nth-child(2) { transform-origin: 50% 50%; opacity: 0; transition: opacity 0.3s, transform 0.3s cubic-bezier(0.215, 0.61, 0.355, 1); }
.diamond-grid__icon :nth-child(1) { transform: scaleY(0.5); }
.diamond-grid__icon :nth-child(2) { transform: scale(0.5); }
.dd6-bg-contrast-lower { background-color: hsla(var(--dd6-color-contrast-lower-hsl), 1); }
.dd6-color-bg { color: hsla(var(--dd6-color-bg-hsl), 1); }
.dd6-sr-only { position: absolute; clip: rect(1px, 1px, 1px, 1px); clip-path: inset(50%); width: 1px; height: 1px; overflow: hidden; padding: 0; border: 0; white-space: nowrap; }
@media (prefers-reduced-motion: reduce) { .diamond-grid__item--link::after, .diamond-grid__icon :nth-child(1), .diamond-grid__icon :nth-child(2) { transition: none; } }
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

    {{-- STATE NOTES: tab bar + crossfading panels --}}
    @php
    $stShapes = json_decode((string) file_get_contents(database_path('data/state-shapes.json')), true) ?: [];
    $stNotes = [
        ['Colorado', 'colorado', 'With the Denver court-watch collective',
         'Documented the Front Range deportation docket — 44 entries — and preserved bodycam evidence in nine protest cases before its scheduled deletion.',
         null, []],
        ['Florida', 'florida', null,
         'Tracked the Everglades detention docket and added 38 entries from the July 2025 protest wave, with court-watchers covering every hearing at three federal courthouses.',
         null, [['The Dispatch', 'Inside the detention docket the state built in a swamp', '/news']]],
        ['Louisiana', 'louisiana', null,
         'Mapped the out-of-state transfer pipeline — including the student cases held at Basile and Jena — that federal courts later put under scrutiny.',
         null, []],
        ['Massachusetts', 'massachusetts', 'With the Boston student-defense network',
         'Documented the Öztürk abduction and the AAUP v. Rubio trial, and mirrored the full trial record into the archive.',
         null, [['The Dispatch', 'The trial that finally named the policy', '/news'],
                ['NPPC Archive', 'AAUP v. Rubio: the complete docket, preserved', '/archive']]],
        ['Michigan', 'michigan', null,
         'Added the Detroit deportation-defense cases and the 2026 auto-plant walkout arrests; nine entries closed by dismissal before the fiscal year ended.',
         null, []],
        ['Missouri', 'missouri', null,
         'Documented the St. Louis grand-jury resistance cases and supported compassionate-release petitions for two of the state\'s oldest open entries.',
         null, []],
        ['North Carolina', 'north-carolina', null,
         'Documented the Courtney Williams prosecution — the year\'s most-watched leak case — hearing by hearing, from Fort Bragg to the Eastern District.',
         '*Docket still open as this report went to press', []],
        ['Ohio', 'ohio', null,
         'Backfilled 61 Palmer Raid-era Ohio entries from newly digitized records — the archive\'s deepest historical dig of the year.',
         null, [['NPPC Archive', '1919, recovered: the Ohio Palmer Raid files', '/archive']]],
        ['Oregon', 'oregon', null,
         'Five years after the 2020 federal-courthouse docket, tracked Portland defendants\' expungement petitions — twelve granted this year, each noted in the census.',
         null, []],
        ['Pennsylvania', 'pennsylvania', null,
         'Kept Mumia Abu-Jamal\'s 44th-year file current and documented the Philadelphia sanctuary-church standoff arrests.',
         null, []],
        ['Vermont', 'vermont', null,
         'Court-watched the Mahdawi and Öztürk habeas hearings that made the District of Vermont the year\'s unlikely center of speech law.',
         null, []],
        ['Washington', 'washington', null,
         'Documented the Seattle deployment-protest docket and marked Tyre Means Jr.\'s release — closing a five-year entry from the 2020 uprising.',
         null, []],
    ];
    @endphp
    <section class="r26-st" id="state-notes">
        <div class="r26-wrap">
            <span class="r26-label rv">Notes From the States</span>
            <h2 class="r26-title rv">Beyond the six fronts</h2>
            <p class="rv" style="color: rgba(20,20,43,.75); max-width: 56ch;">The mass docket reached far past the
            deployment cities. Twelve more state files from the year, each linked to its live entries in the
            census.</p>
            <div class="r26-st-tabs rv" role="tablist" id="r26-st-tabs">
                @foreach ($stNotes as $i => $n)
                    <button type="button" role="tab" class="r26-st-tab{{ $i === 0 ? ' on' : '' }}"
                        aria-selected="{{ $i === 0 ? 'true' : 'false' }}" data-st="{{ $i }}">{{ $n[0] }}</button>
                @endforeach
            </div>
            <div id="r26-st-panels">
                @foreach ($stNotes as $i => [$stName, $stSlug, $partner, $claim, $foot, $cards])
                    <div class="r26-st-panel{{ $i === 0 ? ' on' : '' }}" role="tabpanel" data-st="{{ $i }}">
                        <div>
                            <div class="r26-st-head">
                                @if (isset($stShapes[$stName]))
                                    <span class="r26-st-ico" aria-hidden="true"><svg viewBox="{{ $stShapes[$stName]['viewBox'] }}" preserveAspectRatio="xMidYMid meet"><path d="{{ $stShapes[$stName]['path'] }}" fill="currentColor"/></svg></span>
                                @endif
                                <span class="r26-st-name">{{ $stName }}</span>
                            </div>
                            <div class="r26-st-rule"></div>
                            @if ($partner)
                                <p class="r26-st-partner">{{ $partner }}</p>
                            @endif
                            <p class="r26-st-claim">{{ $claim }}</p>
                            @if ($foot)
                                <p class="r26-st-foot">{{ $foot }}</p>
                            @endif
                            <a class="r26-st-more" href="/state/{{ $stSlug }}">Explore {{ $stName }} in the census &rarr;</a>
                        </div>
                        <div class="r26-st-cards">
                            @foreach ($cards as [$src, $hl, $href])
                                <a class="r26-st-card" href="{{ $href }}">
                                    <span><span class="src">{{ $src }}</span><span class="hl">{{ $hl }}</span></span>
                                    <span class="go">&rsaquo;</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FACES OF THE YEAR: expanding profile columns --}}
    @php
    $pfProfiles = [
        ['David Huerta', 'david-huerta', '/storage/prisoners/david-huerta.jpg',
         'Arrested: June 2025<br>Status: Released on bond',
         'President of SEIU California, <a href="/prisoner/david-huerta">David Huerta</a> was arrested at a Los
          Angeles raid line in June 2025 while acting as an observer, and charged with felony conspiracy to impede
          an officer. &ldquo;Free David&rdquo; rallies filled downtown within 48 hours of his arraignment. His
          census entry tracks the case that made a union president the face of the LA deployment.'],
        ['Tyre Means Jr.', 'tyre-wayne-means-jr', '/storage/prisoners/tyre-wayne-means-jr.jpg',
         'Released: 2025<br>Time served: 4 years',
         '<a href="/prisoner/tyre-wayne-means-jr">Tyre Wayne Means Jr.</a> was sentenced in 2021 for burning a
          Seattle police cruiser during the 2020 uprising, telling the judge he had no regrets. He completed his
          federal sentence and came home as the fiscal year opened &mdash; a five-year entry from the George Floyd
          docket, marked closed.'],
        ['Alfredo Juarez', 'alfredo-juarez', '/storage/prisoners/alfredo-juarez.png',
         'Detained: March 2025<br>Status: Released; case pending',
         'A farmworker organizer who co-founded Familias Unidas por la Justicia at 14,
          <a href="/prisoner/alfredo-juarez">Alfredo &ldquo;Lelo&rdquo; Juarez</a> was pulled from his car by ICE
          agents who smashed his window when he asked for a warrant. His detention triggered a wave of arrests
          against the union&rsquo;s members &mdash; all of it documented, entry by entry, in the census.'],
        ['Zoe Rosenberg', 'zoe-rosenberg', '/storage/prisoners/zoe-rosenberg.jpg',
         'Tried: 2025<br>Sentence: Probation and monitoring',
         '<a href="/prisoner/zoe-rosenberg">Zoe Rosenberg</a>, the open-rescue animal-liberation activist and
          sanctuary founder, was tried in Sonoma County over the rescue of four chickens from a slaughterhouse.
          Sentenced to probation and electronic monitoring as supporters filled the overflow rooms, she kept
          organizing &mdash; with the ankle monitor in frame.'],
        ['Courtney Williams', 'courtney-williams', '/storage/prisoners/courtney-williams.png',
         'Charged: 2026<br>Status: Awaiting trial',
         'A U.S. Army veteran who served eight years with Delta Force,
          <a href="/prisoner/courtney-williams">Courtney Williams</a> became the year&rsquo;s most-watched leak
          prosecution when she was charged in 2026. The census documents her case hearing by hearing &mdash; the
          newest entry in the record&rsquo;s long whistleblower line.'],
        ['Momodou Taal', 'momodou-taal', '/storage/prisoners/momodou-taal.jpg',
         'Departed: March 2025<br>Status: In exile',
         '<a href="/prisoner/momodou-taal">Momodou Taal</a>, a Cornell doctoral student with dual UK and Gambian
          citizenship, sued the administration over the deportation campaign &mdash; then left the country when
          the government moved to detain him for suing. His entry anchors the census&rsquo;s exile cohort: the
          people the record follows even after the country loses them.'],
    ];
    @endphp
    <section class="r26-pf" id="faces">
        <div class="r26-wrap" style="padding-top: 0;">
            <span class="r26-label rv" style="color: var(--acc);">Faces of the Year</span>
            <h2 class="r26-title rv" style="color: #14142b;">Six entries from fiscal year 2026</h2>
            <p class="rv" style="color: rgba(20,20,43,.75); max-width: 56ch; margin-bottom: 40px;">Click each
            profile to learn more &mdash; and to open the full case file in the census.</p>
            <div class="r26-pf-strip rv" id="r26-pf">
                @foreach ($pfProfiles as $i => [$pfName, $pfSlug, $pfPhoto, $pfMeta, $pfBio])
                    <div class="r26-pf-item{{ $i === 0 ? ' on' : '' }}" data-pf="{{ $i }}" tabindex="0" role="button" aria-label="{{ $pfName }}">
                        <span class="r26-pf-vname">{{ $pfName }}</span>
                        <div class="r26-pf-thumb" style="background-image: url('{{ $pfPhoto }}')"></div>
                        <div class="r26-pf-body">
                            <div class="r26-pf-media" style="background-image: url('{{ $pfPhoto }}')"></div>
                            <div class="r26-pf-text">
                                <h3>{{ $pfName }}</h3>
                                <p class="r26-pf-meta">{!! $pfMeta !!}</p>
                                <p>{!! $pfBio !!}</p>
                                <div class="r26-pf-hint">Click each profile to learn more</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- REMEMBERING: memorial tribute with audio reading --}}
    <section class="r26-mem2" id="remembering">
        <div class="r26-wrap r26-mem2-grid">
            <div>
                <h2 class="rv">Remembering Ruchell &ldquo;Cinque&rdquo; Magee</h2>
                <p class="rv">This year&rsquo;s Days of Remembrance were dedicated to
                <a href="/prisoner/ruchell-magee">Ruchell &ldquo;Cinque&rdquo; Magee</a> (1939&ndash;2023), the
                longest-held political prisoner in the United States and the record&rsquo;s most stubborn
                jailhouse lawyer.</p>
                <p class="rv d2">Imprisoned almost continuously from 1963, Mr. Magee was the sole surviving
                participant in the 1970 Marin County courthouse raid and argued his own case for six decades,
                signing every filing &ldquo;Cinque,&rdquo; after the leader of the Amistad rebellion. He was
                granted compassionate release in August 2023, at 84, and died that October at home &mdash; a free
                man. His entry, the census&rsquo;s longest until it closed at 61 years, is preserved in full in
                the archive.</p>
                <div class="r26-mem2-label rv">Hear his entry read at Days of Remembrance</div>
                <div class="r26-wave rv" aria-hidden="true">
                    @foreach ([.5,.9,.35,.7,1,.45,.8,.3,.95,.6,.4,.85,.55,.25,.75] as $j => $h)
                        <i style="--h: {{ $h }}; --d: {{ $j }};"></i>
                    @endforeach
                </div>
                <div class="r26-mem2-player rv">
                    <button type="button" class="r26-play" id="r26-mem2-btn" aria-label="Play the reading">
                        <span class="tri"></span><span class="pause"></span>
                    </button>
                    <span class="r26-time" id="r26-mem2-time">0:00 / 0:00</span>
                    <audio id="r26-mem2-audio" preload="metadata" src="/audio/remembering-ruchell-magee.mp3"></audio>
                </div>
            </div>
            <div class="r26-collage rv rv-fade">
                <div class="ring" aria-hidden="true"></div>
                <div class="ph-duo" style="background-image: url('/storage/prisoners/ruchell-cinque-magee.jpg')"></div>
                <div class="ph-main" style="background-image: url('/storage/prisoners/ruchell-magee-6fdab5ba-c736-4b6f-bc3b-4c358a42c4f5.jpg')"></div>
                <div class="ph-cap">Ruchell Magee, photographed inside and after release. NPPC case file.</div>
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

    {{-- THE YEAR IN FRAMES — tilted image slideshow (CodyHouse _1_tilted-img-slideshow, adapted) --}}
    <section class="r26-frames" id="year-in-frames">
        <div class="r26-wrap grid">
            <div>
                <span class="r26-label rv">One more look</span>
                <h2 class="r26-title rv" style="font-size: clamp(1.7rem, 3vw, 2.4rem);">The Year in Frames</h2>
                <p class="rv d2" style="color: var(--dim); max-width: 46ch;">Three images from the movement the census
                spent fiscal year 2026 watching &mdash; the streets, the tables, and the hands that keep the work
                going. Click or tap the stack to shuffle through.</p>
            </div>
            <div>
                <div class="tilted-slideshow js-tilted-slideshow tm0-perspective-xs tm0-position-relative" id="r26-frames-stack">
                    <figure class="tilted-slideshow__item tilted-slideshow__item--top" data-caption="A speaker rallies the crowd — the year the streets refused to go quiet.">
                        <img src="{{ asset('images/become-a-partner.jpg') }}" alt="A speaker with a megaphone addresses a rally">
                    </figure>
                    <figure class="tilted-slideshow__item tilted-slideshow__item--middle" aria-hidden="true" data-caption="An NPPC outreach table — where the census meets the community.">
                        <img src="{{ asset('images/events-hero.jpg') }}" alt="An NPPC outreach table at a community event">
                    </figure>
                    <figure class="tilted-slideshow__item tilted-slideshow__item--bottom" aria-hidden="true" data-caption="Many hands, one heart — the volunteers behind every case file.">
                        <img src="{{ asset('images/volunteer.jpg') }}" alt="Hands painted red forming a heart">
                    </figure>
                    <p class="tilted-slideshow__touch-hint">Tap to see more</p>
                    <p class="tm0-sr-only" aria-live="polite" id="r26-frames-live"></p>
                </div>
                <p class="r26-frames-caption" id="r26-frames-caption"></p>
            </div>
        </div>
    </section>

    {{-- KEEP EXPLORING — tabbed features (CodyHouse _2_tabbed-features, adapted) --}}
    <section class="r26-explore" id="keep-exploring">
        <div class="r26-wrap">
            <span class="r26-label rv">Where the work lives</span>
            <h2 class="r26-title rv" style="font-size: clamp(1.7rem, 3vw, 2.4rem);">Keep Exploring</h2>

            <div class="tab-features js-tab-features" id="r26-explore-tabs">
                <ul class="tab-features__controls-list" role="tablist">
                    <li class="tab-features__control-wrapper" role="presentation">
                        <a href="#explore-database" class="tab-features__control" role="tab" aria-selected="true" id="explore-tab-1" aria-controls="explore-database">The Census</a>
                    </li>
                    <li class="tab-features__control-wrapper" role="presentation">
                        <a href="#explore-archive" class="tab-features__control" role="tab" aria-selected="false" id="explore-tab-2" aria-controls="explore-archive">Archive &amp; Records</a>
                    </li>
                    <li class="tab-features__control-wrapper" role="presentation">
                        <a href="#explore-tracker" class="tab-features__control" role="tab" aria-selected="false" id="explore-tab-3" aria-controls="explore-tracker">The Tracker</a>
                    </li>
                    <li class="tab-features__control-wrapper" role="presentation">
                        <a href="#explore-calendar" class="tab-features__control" role="tab" aria-selected="false" id="explore-tab-4" aria-controls="explore-calendar">Days of Remembrance</a>
                    </li>
                </ul>

                <div class="tab-features__panels">
                    <div class="tab-features__panel tab-features__panel--display" role="tabpanel" id="explore-database" aria-labelledby="explore-tab-1">
                        <img class="tab-features__img" src="{{ asset('images/data-center-hero.jpg') }}" alt="The political prisoner database">
                        <p class="tab-features__caption">7,391 cases and counting &mdash; the most comprehensive census of U.S. political
                        prisoners ever assembled, searchable by era, movement, charge, and institution.
                        <a href="/database">Explore the database</a>.</p>
                    </div>
                    <div class="tab-features__panel" role="tabpanel" id="explore-archive" aria-labelledby="explore-tab-2" hidden>
                        <img class="tab-features__img" src="{{ asset('images/topics-eras.jpg') }}" alt="The archive and records library">
                        <p class="tab-features__caption">Newspapers, zines, trial documents, and FOIA files &mdash; thousands of digitized
                        primary sources, from the first issue of The Black Panther to this year&rsquo;s prisoner lists.
                        <a href="/archive">Browse the archive</a>.</p>
                    </div>
                    <div class="tab-features__panel" role="tabpanel" id="explore-tracker" aria-labelledby="explore-tab-3" hidden>
                        <img class="tab-features__img" src="{{ asset('images/news-header.jpg') }}" alt="The political prisoner tracker">
                        <p class="tab-features__caption">The live newswire and map behind this report &mdash; arrests, prosecutions, and
                        protests logged as they happen, every pin a story the census is watching.
                        <a href="/dashboard">Open the tracker</a>.</p>
                    </div>
                    <div class="tab-features__panel" role="tabpanel" id="explore-calendar" aria-labelledby="explore-tab-4" hidden>
                        <img class="tab-features__img" src="{{ asset('images/candles.jpg') }}" alt="Days of remembrance calendar">
                        <p class="tab-features__caption">Anniversaries, birthdays behind bars, and the days the movement does not let
                        pass unmarked &mdash; a year of remembrance, one date at a time.
                        <a href="/calendar">See the calendar</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- PAST REPORTS — teaser cards (CodyHouse _1_card-v12, adapted) --}}
    <section class="r26-pastreports" id="past-reports">
        <div class="r26-wrap">
            <span class="r26-label rv">The record so far</span>
            <h2 class="r26-title rv" style="font-size: clamp(1.7rem, 3vw, 2.4rem);">Past Reports</h2>

            <div class="grid">
                @foreach ([
                    ['href' => '/report-2025', 'year' => 'Fiscal Year 2025', 'title' => 'While There Is a Soul in Prison', 'img' => 'freedom.jpg', 'alt' => 'Hands raised at a demonstration', 'desc' => 'The year the census went public — and the tracker began watching in real time.'],
                    ['href' => '/report-2024', 'year' => 'Fiscal Year 2024', 'title' => 'Every Name Counted', 'img' => 'fence.jpg', 'alt' => 'A prison fence at dusk', 'desc' => 'Building the most complete record of U.S. political imprisonment ever assembled.'],
                    ['href' => '/report-2023', 'year' => 'Fiscal Year 2023', 'title' => 'Documenting the Record', 'img' => 'candle.jpg', 'alt' => 'A single lit candle', 'desc' => 'Where it started: the first year of the coalition and the first thousand case files.'],
                ] as $card)
                <a href="{{ $card['href'] }}" class="card-v12 cf5-radius-lg cf5-shadow-sm">
                    <div class="cf5-padding-md">
                        <div class="cf5-position-relative">
                            <figure class="card-v12__figure">
                                <img class="cf5-block cf5-width-100pc cf5-radius-sm" src="{{ asset('images/'.$card['img']) }}" alt="{{ $card['alt'] }}">
                            </figure>
                            <svg class="card-v12__icon" viewBox="0 0 64 60" aria-hidden="true">
                                <g class="icon-group" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="9" y1="30" x2="57" y2="30" />
                                    <line x1="57" y1="30" x2="42" y2="15" />
                                    <line x1="57" y1="30" x2="42" y2="45" />
                                </g>
                            </svg>
                        </div>
                        <div class="cf5-text-center cf5-margin-y-xs">
                            <h3 class="cf5-text-md cf5-color-contrast-higher">{{ $card['title'] }}</h3>
                            <span class="card-v12__separator cf5-border-top cf5-border-contrast-higher cf5-border-opacity-10pc cf5-margin-x-auto cf5-margin-y-xs cf5-block"></span>
                            <span class="cf5-text-xs cf5-text-uppercase cf5-letter-spacing-lg cf5-color-contrast-higher cf5-color-opacity-50pc cf5-block">{{ $card['year'] }}</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- JOIN THE WORK — svg image clip (CodyHouse _1_svg-image-clip, adapted) --}}
    <section class="r26-join sj0-position-relative sj0-z-index-1 sj0-padding-y-xl" id="join-the-work">
        <div class="sj0-container">
            <div class="sj0-grid sj0-gap-md sj0-items-center">
                <div class="sj0-col-6-md">
                    <svg viewBox="0 0 600 600" aria-hidden="true">
                        <defs>
                            <clipPath id="sj0-image-clip-path">
                                <path d="M300,527.5 C424.3,527.5,564,463.7,564,339.4 C564,215.1,482.3,72.5,358,72.5 C233.7,72.5,36,141.1,36,265.4 C36,389.7,175.7,527.5,300,527.5 Z" />
                            </clipPath>
                        </defs>
                        <image height="600" width="600" href="{{ asset('images/donate.jpg') }}" clip-path="url(#sj0-image-clip-path)" preserveAspectRatio="xMidYMid slice"></image>
                    </svg>
                </div>

                <div class="sj0-col-6-md sj0-text-component">
                    <h2>Year Five Starts Now</h2>
                    <p class="sj0-color-contrast-medium">The census, the archive, the tracker, and every report on this
                    page run on the people who decide the record must be kept. Fund the fifth year of the work — or
                    join the volunteers who write the letters, verify the cases, and keep 7,391 names from being
                    forgotten.</p>
                    <div class="sj0-flex sj0-flex-wrap sj0-gap-sm sj0-items-center sj0-margin-top-sm">
                        <a href="/donate" class="sj0-btn sj0-btn--primary">Donate</a>
                        <a href="/get-involved" class="sj0-color-inherit sj0-text-sm">Get involved &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- STICKY HERO — (CodyHouse _1_sticky-hero, adapted) --}}
    <div class="r26-sticky">
        <div class="sticky-hero sticky-hero--overlay-layer sticky-hero--scale js-sticky-hero" id="r26-sticky-hero">
            <div class="sticky-hero__media" role="img" aria-label="Bonus Army encampment at the United States Capitol, 1932" style="background-image: url('/storage/history/bonus-army.jpg');"></div>
            <div class="sticky-hero__content">
                <div class="sy8-container sy8-max-width-sm sy8-text-center sy8-text-component">
                    <h2>The Whole World<br>Is Still Watching</h2>
                    <p>Ninety-four years separate the Bonus Army's tents from this year's docket.
                    The record keeps them on the same page &mdash; and the watching doesn't stop
                    when the fiscal year does.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- DIAMOND GRID (LINKS) — (CodyHouse _1_diamond-grid, adapted) --}}
    <section class="r26-diamonds" id="four-doors">
        <div class="r26-wrap" style="text-align: center; margin-bottom: 10px;">
            <span class="r26-label rv">Four doors in</span>
        </div>
        <div class="stage">
            <div class="diamond-grid">
                <div class="diamond-grid__inner">
                    @foreach ([
                        ['href' => '/history', 'img' => 'topics-movements.jpg', 'label' => 'Explore the history section'],
                        ['href' => '/museum', 'img' => 'topics-organizations.jpg', 'label' => 'Walk the virtual museum'],
                        ['href' => '/podcast', 'img' => 'public-phone.jpg', 'label' => 'Listen to the podcast'],
                        ['href' => '/map', 'img' => 'topics-index.jpg', 'label' => 'Open the prisoner map'],
                    ] as $d)
                    <a class="diamond-grid__item diamond-grid__item--link dd6-bg-contrast-lower" href="{{ $d['href'] }}">
                        <img class="diamond-grid__img" src="{{ asset('images/'.$d['img']) }}" alt="">
                        <svg class="diamond-grid__icon dd6-icon dd6-icon--xl dd6-color-bg" viewBox="0 0 64 64" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="45" x2="45" y2="19" />
                            <polyline points="26 19 45 19 45 38" />
                        </svg>
                        <span class="dd6-sr-only">{{ $d['label'] }}</span>
                    </a>
                    @endforeach
                </div>
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

    // faces of the year: expanding profile columns
    (function () {
        var items = document.querySelectorAll('#r26-pf .r26-pf-item');
        if (!items.length) return;
        items.forEach(function (item) {
            function activate() {
                items.forEach(function (o) { o.classList.toggle('on', o === item); });
            }
            item.addEventListener('click', activate);
            item.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); activate(); }
            });
        });
    })();

    // remembering: audio reading player
    (function () {
        var audio = document.getElementById('r26-mem2-audio');
        if (!audio) return;
        var section = document.getElementById('remembering');
        var btn = document.getElementById('r26-mem2-btn');
        var time = document.getElementById('r26-mem2-time');
        function fmt(s) {
            if (!isFinite(s)) return '0:00';
            s = Math.round(s);
            return Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0');
        }
        function paint() { time.textContent = fmt(audio.currentTime) + ' / ' + fmt(audio.duration); }
        audio.addEventListener('loadedmetadata', paint);
        audio.addEventListener('timeupdate', paint);
        audio.addEventListener('play', function () { section.classList.add('playing'); });
        audio.addEventListener('pause', function () { section.classList.remove('playing'); });
        audio.addEventListener('ended', function () { audio.currentTime = 0; paint(); });
        btn.addEventListener('click', function () { audio.paused ? audio.play() : audio.pause(); });
    })();

    // state-notes tabs
    (function () {
        var tabs = document.querySelectorAll('#r26-st-tabs .r26-st-tab');
        var panels = document.querySelectorAll('#r26-st-panels .r26-st-panel');
        if (!tabs.length) return;
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var k = tab.getAttribute('data-st');
                tabs.forEach(function (t) {
                    var on = t === tab;
                    t.classList.toggle('on', on);
                    t.setAttribute('aria-selected', on ? 'true' : 'false');
                });
                panels.forEach(function (p) {
                    p.classList.toggle('on', p.getAttribute('data-st') === k);
                });
            });
        });
    })();

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

    // sticky hero (CodyHouse _1_sticky-hero behavior): pin state drives overlay + scale
    (function () {
        var hero = document.getElementById('r26-sticky-hero');
        if (!hero) return;
        var ticking = false;
        function update() {
            ticking = false;
            var rect = hero.getBoundingClientRect();
            var pinned = rect.top <= 0 && rect.bottom >= window.innerHeight;
            hero.classList.toggle('sticky-hero--media-is-fixed', pinned);
        }
        window.addEventListener('scroll', function () {
            if (!ticking) { ticking = true; requestAnimationFrame(update); }
        }, { passive: true });
        update();
    })();

    // year-in-frames tilted slideshow (CodyHouse _1_tilted-img-slideshow behavior)
    (function () {
        var stack = document.getElementById('r26-frames-stack');
        if (!stack) return;
        var caption = document.getElementById('r26-frames-caption');
        var live = document.getElementById('r26-frames-live');
        var busy = false;
        function items() { return stack.querySelectorAll('.tilted-slideshow__item'); }
        function byRole(role) { return stack.querySelector('.tilted-slideshow__item--' + role); }
        function paintCaption() {
            var top = byRole('top');
            if (top && caption) caption.textContent = top.getAttribute('data-caption') || '';
            if (top && live) live.textContent = top.querySelector('img').alt;
        }
        function setRoles() {
            var roles = ['top', 'middle', 'bottom'];
            items().forEach(function (item, i) {
                item.classList.remove('tilted-slideshow__item--top', 'tilted-slideshow__item--middle', 'tilted-slideshow__item--bottom');
                item.classList.add('tilted-slideshow__item--' + roles[i]);
                if (i === 0) { item.removeAttribute('aria-hidden'); } else { item.setAttribute('aria-hidden', 'true'); }
            });
        }
        function advance() {
            if (busy) return;
            busy = true;
            stack.classList.add('tilted-slideshow--interacted');
            var top = byRole('top');
            top.classList.add('tilted-slideshow__item--move-out');
            window.setTimeout(function () {
                top.classList.remove('tilted-slideshow__item--move-out');
                stack.appendChild(top);
                setRoles();
                top.classList.add('tilted-slideshow__item--move-in');
                void top.offsetWidth;
                top.classList.remove('tilted-slideshow__item--move-in');
                paintCaption();
                busy = false;
            }, reduced ? 0 : 350);
        }
        items().forEach(function (item) { item.addEventListener('click', advance); });
        paintCaption();
    })();

    // keep-exploring tabbed features (CodyHouse _2_tabbed-features behavior)
    (function () {
        var root = document.getElementById('r26-explore-tabs');
        if (!root) return;
        var tabs = root.querySelectorAll('.tab-features__control');
        var panels = root.querySelectorAll('.tab-features__panel');
        if (reduced) root.classList.add('tabs--no-interaction');
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function (event) {
                event.preventDefault();
                if (tab.getAttribute('aria-selected') === 'true') return;
                var targetId = tab.getAttribute('aria-controls');
                tabs.forEach(function (t) { t.setAttribute('aria-selected', t === tab ? 'true' : 'false'); });
                panels.forEach(function (p) {
                    if (p.id === targetId) {
                        p.removeAttribute('hidden');
                        p.classList.remove('tab-features__panel--hide');
                        p.classList.add('tab-features__panel--display');
                    } else if (!p.hasAttribute('hidden')) {
                        p.classList.remove('tab-features__panel--display');
                        p.classList.add('tab-features__panel--hide');
                        window.setTimeout(function () {
                            p.setAttribute('hidden', '');
                            p.classList.remove('tab-features__panel--hide');
                        }, reduced ? 0 : 500);
                    }
                });
            });
        });
    })();
});
</script>
@endsection
