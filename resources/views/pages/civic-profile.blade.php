@extends('app')

@section('title', 'Civic Profile — National Political Prisoner Coalition')

@section('head')
    <meta name="description" content="Discover your unique Civic Profile. An interactive quiz from the National Political Prisoner Coalition on political repression in America: where you stand on dissent and state power, what you do for the imprisoned, and how well you know the history.">
    @verbatim
    <style>
        /* ============================================================
           Civic Profile — layout & presentation modeled on
           civicprofile.org: off-white "grid paper" background, gold /
           crimson / teal accents, an animated photo-wall intro, a
           three-part overview band, and a sticky photo mosaic whose
           crimson "Take the Quiz" box expands to fill the screen.
           ============================================================ */
        /* Dark is the site default; html[data-theme="light"] flips to light. */
        .cph-hero, .cph-overview, .cph-mosaic-area, .cph-quiz {
            --cph-bg: #000000;
            --cph-ink: #ffffff;
            --cph-grid: #15171f;
            --cph-gold: #f25c54;
            --cph-crimson: #5660fe;
            --cph-cta: #4049d6;
            --cph-teal: #8b93ff;
            --cph-gray: #a3a9b6;
        }
        html[data-theme="light"] .cph-hero, html[data-theme="light"] .cph-overview,
        html[data-theme="light"] .cph-mosaic-area, html[data-theme="light"] .cph-quiz {
            --cph-bg: #fbfbfe;
            --cph-ink: #16181f;
            --cph-grid: #ececf6;
            --cph-teal: #1a1a2e;
            --cph-gray: #686868;
        }
        .cph-hero *, .cph-overview *, .cph-mosaic-area *, .cph-quiz * { box-sizing: border-box; }

        /* Hairline grid-lines backdrop (the site-wide texture on the original). */
        .cph-gridbg { position: relative; background: var(--cph-bg); }
        .cph-gridbg::before {
            content: ""; position: absolute; inset: 0; pointer-events: none; z-index: 0;
            --cw: calc(100vw / 10); --ch: calc(100vh / 3);
            background-image:
                repeating-linear-gradient(to right, var(--cph-grid) 0, var(--cph-grid) 1px, transparent 1px, transparent var(--cw)),
                repeating-linear-gradient(to bottom, var(--cph-grid) 0, var(--cph-grid) 1px, transparent 1px, transparent var(--ch));
            border-bottom: 1px solid var(--cph-grid);
        }
        @media (max-width: 1024px) { .cph-gridbg::before { --cw: calc(100vw / 6); } }
        @media (max-width: 640px)  { .cph-gridbg::before { --cw: calc(100vw / 4); } }

        /* ---------- Hero: photo-wall intro that resolves to the headline ---------- */
        .cph-hero {
            position: relative; height: 100vh; min-height: 560px; overflow: hidden;
            background: var(--cph-bg); color: var(--cph-ink);
        }
        .cph-rows {
            position: absolute; inset: 0; display: flex; flex-direction: column;
            justify-content: center; pointer-events: none;
        }
        .cph-row { height: 50%; overflow: hidden; display: flex; align-items: stretch; will-change: transform; }
        .cph-track { display: flex; width: max-content; will-change: transform; }
        .cph-card { flex: 0 0 auto; height: 100%; aspect-ratio: 5 / 7; overflow: hidden; background: #e9e9ee; }
        .cph-card img { width: 100%; height: 100%; object-fit: cover; display: block; }

        .cph-logo {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            z-index: 5; display: flex; align-items: center; gap: 22px; padding: 0 2rem;
            max-width: 580px; width: 100%; justify-content: center; pointer-events: none;
        }
        .cph-logo-mark { width: clamp(72px, 12vw, 118px); height: auto; flex: 0 0 auto; }
        .cph-logo-text { line-height: 1.02; }
        .cph-logo-text strong {
            display: block; font-size: clamp(34px, 6vw, 58px); font-weight: 900;
            letter-spacing: -0.02em; color: var(--cph-ink);
        }
        .cph-logo-text span {
            display: block; margin-top: 10px; font-size: clamp(11px, 1.6vw, 14px);
            font-weight: 800; text-transform: uppercase; letter-spacing: 0.16em; color: var(--cph-crimson);
        }

        .cph-content {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            z-index: 6; width: 100%; max-width: 960px; padding: 3rem 1.5rem;
            display: flex; flex-direction: column; align-items: center; text-align: center;
        }
        .cph-content h1 {
            margin: 0; font-size: clamp(2.5rem, 5vw, 4rem); line-height: 1.1;
            letter-spacing: -0.02em; font-weight: 900; color: var(--cph-ink); will-change: transform;
        }
        .cph-lede {
            margin: 1.5rem auto 0; font-size: clamp(1.05rem, 2vw, 1.25rem);
            max-width: 640px; color: var(--cph-ink); opacity: 0.85; line-height: 1.55;
        }
        .cph-cta-row { margin-top: 2rem; }
        .cph-btn {
            display: inline-block; background: var(--cph-crimson); color: #fff;
            border: 2px solid transparent; border-radius: 3.125rem; padding: 1rem 2.25rem;
            font-size: 1.25rem; font-weight: 600; text-decoration: none;
            transition: background .2s, color .2s, border-color .2s;
        }
        .cph-btn:hover { background: transparent; border-color: var(--cph-crimson); color: var(--cph-crimson); }
        .cph-arrow {
            margin-top: 2rem; display: flex; align-items: center; justify-content: center;
            color: var(--cph-ink); animation: cphBounce 2s ease-in-out infinite;
        }
        .cph-arrow svg { width: 15px; height: 18px; }
        @keyframes cphBounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(8px); }
        }
        .cph-progress {
            position: absolute; bottom: 0; left: 0; height: 10px; width: 0;
            background: var(--cph-crimson); z-index: 999; transition: width .2s ease-out;
        }
        /* Static / finished state (no JS, reduced motion, or replay skipped). */
        .cph-hero.cph-done .cph-rows, .cph-hero.cph-done .cph-logo { display: none; }
        /* Hide the site chrome while the intro is playing, like the original. */
        body.cph-intro-active #desktop-nav,
        body.cph-intro-active #nav-spacing,
        body.cph-intro-active nav.fixed { opacity: 0; pointer-events: none; transition: opacity .4s; }
        #desktop-nav, nav.fixed { transition: opacity .4s; }

        /* ---------- Overview: Part One / Two / Three ---------- */
        .cph-overview { min-height: 100vh; display: grid; grid-template-columns: repeat(3, 1fr); color: var(--cph-ink); }
        .cph-ov-col {
            position: relative; z-index: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center; text-align: center;
            padding: 4rem 1.5rem; min-height: 100%;
        }
        .cph-ov-inner { max-width: 400px; display: flex; flex-direction: column; align-items: center; }
        /* Equalize the three inner blocks so eyebrows, icons, and headings
           align across columns despite different text lengths. */
        @media (min-width: 901px) { .cph-ov-inner { min-height: 400px; } }
        .cph-ov-eyebrow {
            margin: 0 0 1.75rem; font-size: 1rem; font-weight: 500; color: var(--cph-ink); opacity: .75;
        }
        .cph-ov-icon { height: 64px; display: flex; align-items: center; margin-bottom: 1.5rem; }
        .cph-ov-icon svg { width: 56px; height: 56px; }
        .cph-ov-col h2 { margin: 0 0 1rem; font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 800; letter-spacing: -0.01em; }
        .cph-ov-col p { margin: 0 0 1.5rem; font-size: 1rem; line-height: 1.6; }
        .cph-ov-link {
            color: var(--acc, var(--cph-crimson)); font-weight: 600; text-decoration: underline;
            text-underline-offset: 3px; padding: .5rem 0;
        }
        .cph-ov-link:hover { opacity: .7; }
        @media (max-width: 900px) { .cph-overview { grid-template-columns: 1fr; min-height: 0; } .cph-ov-col { padding: 3rem 1.5rem; } }

        /* ---------- Mosaic: sticky photo grid + expanding CTA ---------- */
        .cph-mosaic-area { position: relative; }
        .cph-mosaic-sticky { height: 100vh; overflow: hidden; position: relative; width: 100%; }
        .cph-mosaic {
            position: absolute; inset: 0; z-index: 2; display: grid;
            grid-template-columns: repeat(10, 1fr); grid-template-rows: repeat(3, 1fr);
        }
        .cph-slot { overflow: hidden; position: relative; will-change: transform; }
        .cph-slot img { display: block; width: 100%; height: 100%; object-fit: cover; }
        .cph-ctabox {
            position: absolute; left: 40%; top: 33.3333%; width: 20%; height: 33.3333%;
            z-index: 10; background: var(--cph-cta); color: #fff; overflow: hidden;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-align: center; padding: 2rem;
        }
        .cph-cta-heading {
            margin: 0; color: #fff; font-weight: 700; line-height: 1.2;
            font-size: clamp(1.5rem, 3.5vw, 3.125rem); white-space: nowrap;
        }
        .cph-cta-btn {
            /* Sits on the constant-indigo CTA box — colors stay fixed in both themes. */
            display: inline-block; margin-top: 2rem; background: #fff; color: #16181f;
            border: 2px solid transparent; border-radius: 3.125rem; padding: 1rem 2.25rem;
            font-size: 1.25rem; font-weight: 600; text-decoration: none;
            transition: background .2s, color .2s, border-color .2s;
        }
        .cph-cta-btn:hover { background: transparent; border-color: #fff; color: #fff; }
        @media (max-width: 1024px) {
            .cph-mosaic { grid-template-columns: repeat(3, 1fr) !important; grid-template-rows: repeat(3, 1fr) !important; }
            .cph-slot[data-slot="4"], .cph-slot[data-slot="6"], .cph-slot[data-slot="7"],
            .cph-slot[data-slot="9"], .cph-slot[data-slot="11"] { display: none !important; }
            .cph-slot[data-slot="0"]  { grid-column: 1 / 2 !important; grid-row: 1 / 2 !important; }
            .cph-slot[data-slot="1"]  { grid-column: 2 / 3 !important; grid-row: 1 / 2 !important; }
            .cph-slot[data-slot="2"]  { grid-column: 3 / 4 !important; grid-row: 1 / 2 !important; }
            .cph-slot[data-slot="3"]  { grid-column: 1 / 2 !important; grid-row: 2 / 3 !important; }
            .cph-slot[data-slot="5"]  { grid-column: 3 / 4 !important; grid-row: 2 / 3 !important; }
            .cph-slot[data-slot="8"]  { grid-column: 1 / 2 !important; grid-row: 3 / 4 !important; }
            .cph-slot[data-slot="10"] { grid-column: 2 / 3 !important; grid-row: 3 / 4 !important; }
            .cph-slot[data-slot="12"] { grid-column: 3 / 4 !important; grid-row: 3 / 4 !important; }
            .cph-ctabox { left: 33.3333%; top: 33.3333%; width: 33.3333%; height: 33.3333%; }
        }
        @media (max-width: 768px) {
            .cph-slot[data-slot="1"], .cph-slot[data-slot="3"], .cph-slot[data-slot="5"],
            .cph-slot[data-slot="8"] { display: none !important; }
            .cph-slot[data-slot="0"]  { grid-column: 1 / 3 !important; grid-row: 1 / 2 !important; }
            .cph-slot[data-slot="2"]  { grid-column: 3 / 4 !important; grid-row: 1 / 2 !important; }
            .cph-slot[data-slot="10"] { grid-column: 1 / 2 !important; grid-row: 3 / 4 !important; }
            .cph-slot[data-slot="12"] { grid-column: 2 / 4 !important; grid-row: 3 / 4 !important; }
            .cph-ctabox { left: 0; top: 33.3333%; width: 100%; height: 33.3333%; }
        }

        /* ---------- Quiz ---------- */
        .cph-quiz { padding: 1px 0; }
        .cp {
            --cp-accent: #5660fe;
            --cp-accent-dark: #4049d6;
            --cp-gold: #f25c54;
            --cp-teal: #8b93ff;
            --cp-ink: #ffffff;
            --cp-muted: #a3a9b6;
            --cp-line: #262b38;
            --cp-bg: #000000;
            --cp-card: #16181f;
            --cp-good: #8b93ff;
            --cp-bad: #f25c54;
            --cp-coral-deep: #ff8a80;
            --cp-perc-fg: #b9bdd8;
            --cp-perc-bg: rgba(185,189,216,.12);
            --cp-know-bg: rgba(139,147,255,.14);
            --cp-eng-fg: #8b93ff;
            --cp-axis-line: #333947;
            --cp-axis-fg: #8f96a3;
            --cp-track: #4a5060;
            --cp-opt-hover: #1b1e29;
            --cp-sel-bg: rgba(86,96,254,.16);
            position: relative; z-index: 1;
            max-width: 760px;
            margin: 0 auto;
            padding: 48px 20px 110px;
            color: var(--cp-ink);
            line-height: 1.5;
            scroll-margin-top: 84px;
        }
        html[data-theme="light"] .cp {
            --cp-teal: #1a1a2e;
            --cp-ink: #16181f;
            --cp-muted: #686868;
            --cp-line: #e5ebee;
            --cp-bg: #fbfbfe;
            --cp-card: #ffffff;
            --cp-good: #1a1a2e;
            --cp-coral-deep: #c03e37;
            --cp-perc-fg: #2a2a4a;
            --cp-perc-bg: rgba(42,42,74,.1);
            --cp-know-bg: rgba(26,26,46,.1);
            --cp-eng-fg: #5660fe;
            --cp-axis-line: #cfd6da;
            --cp-axis-fg: #9aa1a7;
            --cp-track: #7d8288;
            --cp-opt-hover: #fafaff;
            --cp-sel-bg: rgba(86,96,254,.06);
        }
        .cp *, .cp *::before, .cp *::after { box-sizing: border-box; }
        .cp button { font-family: inherit; cursor: pointer; }

        /* Intro / hero card */
        .cp-hero { text-align: center; padding: 24px 0 8px; }
        .cp-eyebrow {
            text-transform: uppercase; letter-spacing: .14em; font-size: 13px;
            font-weight: 700; color: var(--cp-eng-fg); margin: 0 0 14px;
        }
        .cp-hero h1 {
            font-size: clamp(32px, 6vw, 52px); line-height: 1.04; margin: 0 0 16px;
            font-weight: 900; letter-spacing: -0.02em;
        }
        .cp-lede { font-size: clamp(17px, 2.4vw, 20px); color: var(--cp-muted); max-width: 560px; margin: 0 auto 28px; }
        .cp-parts { display: grid; gap: 14px; grid-template-columns: repeat(3, 1fr); margin: 8px 0 32px; text-align: left; }
        .cp-part-card { background: var(--cp-card); border: 1px solid var(--cp-line); border-radius: 14px; padding: 18px; }
        .cp-part-card .n { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 50%; background: var(--pc, var(--cp-accent)); color: #fff; font-weight: 800; font-size: 14px; margin-bottom: 12px; }
        .cp-part-card h3 { margin: 0 0 6px; font-size: 18px; font-weight: 800; }
        .cp-part-card p { margin: 0; font-size: 14px; color: var(--cp-muted); }

        .cp-btn {
            display: inline-block; border: 2px solid transparent; background: var(--cp-accent); color: #fff;
            font-weight: 600; font-size: 1.15rem; padding: 0.9rem 2.1rem; border-radius: 3.125rem;
            transition: background .2s, color .2s, border-color .2s; text-decoration: none;
        }
        .cp-btn:hover { background: transparent; border-color: var(--cp-accent); color: var(--cp-accent); }
        .cp-note { font-size: 13px; color: var(--cp-muted); margin-top: 16px; }

        /* Progress */
        .cp-progress { margin: 8px 0 28px; }
        .cp-progress-meta { display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; color: var(--cp-muted); margin-bottom: 8px; }
        .cp-progress-meta .part { text-transform: uppercase; letter-spacing: .1em; }
        .cp-progress-meta .part--values { color: var(--cp-gold); }
        .cp-progress-meta .part--engagement { color: var(--cp-eng-fg); }
        .cp-progress-meta .part--knowledge { color: var(--cp-teal); }
        .cp-progress-track { height: 8px; background: var(--cp-line); border-radius: 999px; overflow: hidden; }
        .cp-progress-fill { height: 100%; background: var(--cp-accent); border-radius: 999px; transition: width .35s cubic-bezier(.4,0,.2,1); }

        /* Question stage */
        .cp-stage { background: var(--cp-card); border: 1px solid var(--cp-line); border-radius: 18px; padding: 32px 28px; box-shadow: 0 12px 40px -28px rgba(22,24,31,.5); }
        .cp-part-badge { display: inline-block; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .12em; padding: 5px 12px; border-radius: 999px; margin-bottom: 18px; }
        .cp-part-badge--values { color: var(--cp-coral-deep); background: rgba(242,92,84,.14); }
        .cp-part-badge--engagement { color: var(--cp-eng-fg); background: rgba(86,96,254,.12); }
        .cp-part-badge--knowledge { color: var(--cp-teal); background: var(--cp-know-bg); }
        .cp-q { font-size: clamp(20px, 3.2vw, 26px); font-weight: 800; line-height: 1.25; margin: 0 0 24px; letter-spacing: -0.01em; }
        .cp-scale-hint { font-size: 13px; color: var(--cp-muted); margin: -14px 0 18px; }

        .cp-opts { display: flex; flex-direction: column; gap: 12px; }
        .cp-opt {
            display: flex; align-items: center; gap: 14px; width: 100%; text-align: left;
            background: var(--cp-card); border: 2px solid var(--cp-line); border-radius: 12px;
            padding: 16px 18px; font-size: 16px; font-weight: 600; color: var(--cp-ink);
            transition: border-color .12s ease, background .12s ease, transform .05s ease;
        }
        .cp-opt:hover { border-color: var(--cp-accent); background: var(--cp-opt-hover); }
        .cp-opt:active { transform: scale(.995); }
        .cp-opt.is-selected { border-color: var(--cp-accent); background: var(--cp-sel-bg); }
        .cp-opt-key {
            flex: 0 0 auto; width: 30px; height: 30px; border-radius: 8px; background: var(--cp-bg);
            border: 1px solid var(--cp-line); display: inline-flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 14px; color: var(--cp-muted);
        }
        .cp-opt.is-selected .cp-opt-key { background: var(--cp-accent); border-color: var(--cp-accent); color: #fff; }

        /* Perception-check slider question (guess the percentage) */
        .cp-part-badge--perceptions { color: var(--cp-perc-fg); background: var(--cp-perc-bg); }
        .cp-progress-meta .part--perceptions { color: var(--cp-perc-fg); }
        .cp-guess-q { text-align: center; }
        .cp-guess-ctarow { display: flex; align-items: center; justify-content: center; gap: 18px; flex-wrap: wrap; margin: 2px 0 40px; }
        .cp-guess-ctarow[hidden] { display: none; }
        .cp-guess-hint { margin: 0; color: var(--cp-bad); font-size: 17px; font-weight: 600; }
        .cp-guess-submit {
            background: var(--cp-accent); color: #fff; font-weight: 700; font-size: 16px;
            border: none; border-radius: 10px; padding: 10px 24px;
            box-shadow: 0 4px 12px -5px rgba(86,96,254,.75); transition: background .15s;
        }
        .cp-guess-submit:hover { background: var(--cp-accent-dark); }
        .cp-slider { position: relative; padding-top: 46px; margin: 0 6px; }
        .cp-slider-val {
            position: absolute; top: 0; transform: translateX(-50%);
            font-size: 28px; font-weight: 900; color: var(--cp-ink); white-space: nowrap;
        }
        .cp-range { -webkit-appearance: none; appearance: none; display: block; width: 100%; height: 24px; margin: 0; background: transparent; }
        .cp-range:focus { outline: none; }
        .cp-range:focus-visible { outline: 2px solid var(--cp-teal); outline-offset: 4px; border-radius: 6px; }
        .cp-range::-webkit-slider-runnable-track { height: 2px; background: var(--cp-track); border-radius: 2px; }
        .cp-range::-webkit-slider-thumb {
            -webkit-appearance: none; width: 22px; height: 22px; border-radius: 50%; margin-top: -10px;
            background: var(--cp-accent); border: 3px solid var(--cp-card); box-shadow: 0 0 0 2px var(--cp-ink); cursor: grab;
        }
        .cp-range::-moz-range-track { height: 2px; background: var(--cp-track); border-radius: 2px; }
        .cp-range::-moz-range-thumb {
            width: 16px; height: 16px; border-radius: 50%;
            background: var(--cp-accent); border: 3px solid var(--cp-card); box-shadow: 0 0 0 2px var(--cp-ink); cursor: grab;
        }
        .cp-range[disabled]::-webkit-slider-thumb { cursor: default; }
        .cp-range[disabled]::-moz-range-thumb { cursor: default; }
        .cp-guess-actual {
            position: absolute; top: 51px; width: 14px; height: 14px; border-radius: 50%;
            background: var(--cp-gold); box-shadow: 0 0 0 2px var(--cp-card); transform: translateX(-50%); pointer-events: none;
        }
        .cp-axis { margin-top: 26px; }
        .cp-axis-line { position: relative; height: 36px; }
        .cp-axis-line::before { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 1px; background: var(--cp-axis-line); }
        .cp-tick { position: absolute; top: 0; transform: translateX(-50%); text-align: center; color: var(--cp-axis-fg); font-weight: 700; font-size: 15px; }
        .cp-tick::before { content: ""; display: block; width: 1px; height: 8px; background: var(--cp-axis-line); margin: 0 auto 6px; }
        .cp-axis-caption { text-align: center; color: var(--cp-axis-fg); font-size: 13px; margin-top: 10px; }
        .cp-guess-feedback { margin-top: 30px; text-align: center; }
        .cp-guess-feedback p { font-size: 17px; line-height: 1.55; margin: 0 0 20px; }
        .cp-guess-feedback .you { color: var(--cp-eng-fg); font-weight: 800; }
        .cp-guess-feedback .act { color: var(--cp-coral-deep); font-weight: 800; }
        .cp-guess-rows { display: flex; flex-direction: column; gap: 14px; }
        .cp-guess-row .top { display: flex; justify-content: space-between; font-size: 14px; font-weight: 700; margin-bottom: 6px; }
        .cp-guess-row .top .v { color: var(--cp-muted); font-weight: 600; }
        .cp-guess-track { position: relative; height: 12px; background: var(--cp-line); border-radius: 999px; }
        .cp-gdot { position: absolute; top: 50%; width: 14px; height: 14px; border-radius: 50%; transform: translate(-50%, -50%); box-shadow: 0 0 0 2px var(--cp-card); }
        .cp-gdot--you { background: var(--cp-accent); }
        .cp-gdot--act { background: var(--cp-gold); }
        .cp-guess-legend .sw { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 4px; vertical-align: baseline; }

        .cp-stage-foot { display: flex; align-items: center; justify-content: space-between; margin-top: 26px; }
        .cp-back { background: none; border: none; color: var(--cp-muted); font-weight: 700; font-size: 15px; padding: 8px 4px; }
        .cp-back:hover { color: var(--cp-ink); }
        .cp-back[disabled] { opacity: 0; pointer-events: none; }
        .cp-tap-hint { font-size: 13px; color: var(--cp-muted); }

        /* Part intro card */
        .cp-partintro { text-align: center; padding: 18px 4px 6px; }
        .cp-partintro .step { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: .14em; color: var(--cp-eng-fg); margin-bottom: 14px; }
        .cp-partintro h2 { font-size: clamp(26px, 4.5vw, 38px); font-weight: 900; margin: 0 0 16px; letter-spacing: -0.02em; }
        .cp-partintro p { font-size: 17px; color: var(--cp-muted); max-width: 520px; margin: 0 auto 28px; }

        /* Results */
        .cp-results-head { text-align: center; margin-bottom: 8px; }
        .cp-results-head .eyebrow { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: .14em; color: var(--cp-eng-fg); }
        .cp-results-head h1 { font-size: clamp(28px, 5vw, 44px); font-weight: 900; margin: 10px 0 6px; letter-spacing: -0.02em; }
        .cp-summary { text-align: center; font-size: 17px; color: var(--cp-muted); max-width: 560px; margin: 0 auto 32px; }

        .cp-res-card { background: var(--cp-card); border: 1px solid var(--cp-line); border-radius: 18px; padding: 28px 26px; margin-bottom: 20px; }
        .cp-res-card .label { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .12em; color: var(--cp-muted); margin-bottom: 8px; }
        .cp-res-card h2 { font-size: clamp(22px, 3.6vw, 30px); font-weight: 900; margin: 0 0 12px; color: var(--cp-eng-fg); letter-spacing: -0.01em; }
        .cp-res-card p { margin: 0 0 18px; font-size: 16px; color: var(--cp-ink); }

        .cp-bars { display: flex; flex-direction: column; gap: 14px; }
        .cp-bar .bar-top { display: flex; justify-content: space-between; font-size: 14px; font-weight: 700; margin-bottom: 6px; }
        .cp-bar .bar-top .v { color: var(--cp-muted); }
        .cp-bar.is-top .bar-top { color: var(--cp-eng-fg); }
        .cp-bar-track { height: 12px; background: var(--cp-line); border-radius: 999px; overflow: hidden; }
        .cp-bar-fill { height: 100%; border-radius: 999px; background: var(--cp-eng-fg); opacity: .45; transition: width .8s cubic-bezier(.4,0,.2,1); }
        .cp-bar.is-top .cp-bar-fill { background: var(--cp-accent); opacity: 1; }

        .cp-score-row { display: flex; align-items: center; gap: 22px; }
        .cp-ring { flex: 0 0 auto; position: relative; width: 104px; height: 104px; }
        .cp-ring svg { transform: rotate(-90deg); }
        .cp-ring .pct { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; font-weight: 900; }
        .cp-ring .pct .num { font-size: 26px; line-height: 1; }
        .cp-ring .pct .den { font-size: 12px; color: var(--cp-muted); font-weight: 700; margin-top: 2px; }
        .cp-score-tier { font-size: 20px; font-weight: 900; margin-bottom: 4px; }

        .cp-missed { margin-top: 18px; border-top: 1px solid var(--cp-line); padding-top: 16px; }
        .cp-missed summary { font-weight: 800; cursor: pointer; font-size: 15px; }
        .cp-missed ul { margin: 14px 0 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 14px; }
        .cp-missed li { font-size: 15px; }
        .cp-missed .qx { font-weight: 700; display: block; margin-bottom: 4px; }
        .cp-missed .ax { color: var(--cp-good); font-weight: 700; }

        .cp-actions { margin: 28px 0 8px; }
        .cp-actions h3 { text-align: center; font-size: 20px; font-weight: 900; margin: 0 0 18px; }
        .cp-action-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .cp-action { display: block; background: var(--cp-card); border: 1px solid var(--cp-line); border-radius: 14px; padding: 20px 18px; text-decoration: none; color: var(--cp-ink); transition: border-color .12s ease, transform .08s ease; }
        .cp-action:hover { border-color: var(--cp-accent); transform: translateY(-2px); }
        .cp-action .ic { font-size: 22px; margin-bottom: 10px; color: var(--cp-eng-fg); }
        .cp-action strong { display: block; font-size: 16px; margin-bottom: 4px; }
        .cp-action span { font-size: 13px; color: var(--cp-muted); }

        .cp-foot { text-align: center; margin-top: 34px; }
        .cp-share { display: flex; gap: 12px; justify-content: center; margin-bottom: 22px; }
        .cp-share a { width: 44px; height: 44px; border-radius: 50%; border: 1px solid var(--cp-line); display: inline-flex; align-items: center; justify-content: center; color: var(--cp-ink); text-decoration: none; transition: border-color .12s, color .12s; }
        .cp-share a:hover { border-color: var(--cp-accent); color: var(--cp-accent); }
        .cp-share svg { width: 18px; height: 18px; fill: currentColor; }
        .cp-retake { background: none; border: 2px solid var(--cp-line); color: var(--cp-ink); font-weight: 800; font-size: 15px; padding: 12px 28px; border-radius: 999px; }
        .cp-retake:hover { border-color: var(--cp-accent); color: var(--cp-accent); }

        @media (max-width: 640px) {
            .cp-parts { grid-template-columns: 1fr; }
            .cp-action-grid { grid-template-columns: 1fr; }
            .cp-stage { padding: 26px 20px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .cph-arrow { animation: none; }
        }
    </style>
    @endverbatim
@endsection

@section('body')
    @php
        $cphRow1 = ['pp-01', 'pp-05', 'pp-07', 'pp-10', 'pp-08', 'pp-11'];
        $cphRow2 = ['pp-03', 'pp-09', 'pp-02', 'pp-12', 'pp-06', 'pp-04'];
        // Mosaic geometry mirrors the original's 10x3 grid (slot 0 is a 3x2 feature).
        $cphMosaic = [
            0  => ['1 / 4',  '1 / 3', 'pp-09'],
            1  => ['4 / 5',  '1 / 2', 'pp-05'],
            2  => ['5 / 7',  '1 / 2', 'pp-01'],
            3  => ['7 / 9',  '1 / 2', 'pp-10'],
            4  => ['9 / 11', '1 / 2', 'pp-03'],
            5  => ['4 / 5',  '2 / 3', 'pp-07'],
            6  => ['7 / 9',  '2 / 3', 'pp-12'],
            7  => ['9 / 11', '2 / 3', 'pp-02'],
            8  => ['1 / 2',  '3 / 4', 'pp-06'],
            9  => ['2 / 3',  '3 / 4', 'pp-11'],
            10 => ['3 / 5',  '3 / 4', 'pp-04'],
            11 => ['5 / 7',  '3 / 4', 'pp-08'],
            12 => ['7 / 11', '3 / 4', 'pp-01'],
        ];
    @endphp

    {{-- ============ Hero: photo-wall intro ============ --}}
    <section class="cph-hero" id="cph-hero">
        <div class="cph-rows" aria-hidden="true">
            <div class="cph-row cph-row--1">
                <div class="cph-track">
                    @foreach (array_merge($cphRow1, $cphRow1) as $img)
                        <div class="cph-card"><img src="/images/civic-profile/{{ $img }}.jpg" alt="" loading="eager"></div>
                    @endforeach
                </div>
            </div>
            <div class="cph-row cph-row--2">
                <div class="cph-track">
                    @foreach (array_merge($cphRow2, $cphRow2) as $img)
                        <div class="cph-card"><img src="/images/civic-profile/{{ $img }}.jpg" alt="" loading="eager"></div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="cph-logo" aria-hidden="true">
            <svg class="cph-logo-mark" viewBox="0 0 96 96" xmlns="http://www.w3.org/2000/svg">
                <rect width="96" height="96" rx="16" fill="#f25c54"/>
                <path d="M48 18A30 30 0 0 1 78 48L48 48Z" fill="#5660fe"/>
                <path d="M78 48A30 30 0 0 1 48 78L48 48Z" fill="#1a1a2e"/>
                <path d="M48 78A30 30 0 0 1 18 48L48 48Z" fill="#fbfbfe"/>
                <path d="M18 48A30 30 0 0 1 48 18L48 48Z" fill="#16181f"/>
            </svg>
            <div class="cph-logo-text">
                <strong>Civic<br>Profile</strong>
                <span>National Political<br>Prisoner Coalition</span>
            </div>
        </div>

        <div class="cph-content">
            <h1>Discover your <br>unique Civic Profile</h1>
            <p class="cph-lede">The Civic Profile is an interactive quiz from the National Political Prisoner Coalition about political repression in America — where you stand, how you show up, and how much of the history you know.</p>
            <div class="cph-cta-row">
                <a href="#cp-app" class="cph-btn">Take the Quiz</a>
            </div>
            <div class="cph-arrow" aria-hidden="true">
                <svg viewBox="0 0 11 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5.5 1v11m0 0L1 7.6M5.5 12 10 7.6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
        </div>
    </section>

    {{-- ============ Overview: Part One / Two / Three ============ --}}
    <section class="cph-overview cph-gridbg">
        <div class="cph-ov-col">
            <div class="cph-ov-inner">
                <p class="cph-ov-eyebrow">Part One</p>
                <div class="cph-ov-icon">
                    <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M24 42C10 32 4 24.5 4 16.5 4 10 9 5 15.2 5 19 5 22.2 6.9 24 9.8 25.8 6.9 29 5 32.8 5 39 5 44 10 44 16.5 44 24.5 38 32 24 42Z" fill="#f25c54"/></svg>
                </div>
                <h2>Values</h2>
                <p>Where do you stand when the state turns on dissent? This section maps your beliefs about free expression, due process, solidarity with the imprisoned, and the risks worth taking to resist repression.</p>
                <a class="cph-ov-link" style="--acc:#f25c54" href="#cp-app">More About Values</a>
            </div>
        </div>
        <div class="cph-ov-col">
            <div class="cph-ov-inner">
                <p class="cph-ov-eyebrow">Part Two</p>
                <div class="cph-ov-icon">
                    <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M6 20v8c0 1.7 1.3 3 3 3h3v8c0 1.7 1.3 3 3 3h4c1.1 0 2-.9 2-2v-9l16.2 7.6c1.3.6 2.8-.3 2.8-1.8V11.2c0-1.5-1.5-2.4-2.8-1.8L21 17H9c-1.7 0-3 1.3-3 3Z" fill="#5660fe"/></svg>
                </div>
                <h2>Engagement</h2>
                <p>Political prisoners survive on outside support. This section measures what you actually do — from letters and commissary to court support, defense funds, and campaigns for release.</p>
                <a class="cph-ov-link" style="--acc:#5660fe" href="#cp-app">More about Engagement</a>
            </div>
        </div>
        <div class="cph-ov-col">
            <div class="cph-ov-inner">
                <p class="cph-ov-eyebrow">Part Three</p>
                <div class="cph-ov-icon">
                    <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M24 12C20 8.7 14.6 7 8 7c-1.1 0-2 .9-2 2v28c0 1.1.9 2 2 2 6.6 0 12 1.7 16 5 4-3.3 9.4-5 16-5 1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2-6.6 0-12 1.7-16 5Zm0 0v32" fill="none" stroke="#1a1a2e" style="stroke:var(--cph-teal)" stroke-width="3" stroke-linejoin="round"/></svg>
                </div>
                <h2>Knowledge</h2>
                <p>From the Espionage Act to COINTELPRO to today, the United States has a long record of jailing dissenters. This section tests how well you know that history.</p>
                <a class="cph-ov-link" style="--acc:var(--cph-teal)" href="#cp-app">More about Knowledge</a>
            </div>
        </div>
    </section>

    {{-- ============ Mosaic: sticky photo grid, CTA expands on scroll ============ --}}
    <div class="cph-mosaic-area" id="cph-mosaic-area">
        <div class="cph-mosaic-sticky cph-gridbg">
            <div class="cph-mosaic" aria-hidden="true">
                @foreach ($cphMosaic as $slot => [$cols, $rows, $img])
                    <div class="cph-slot" data-slot="{{ $slot }}" style="grid-column: {{ $cols }}; grid-row: {{ $rows }};">
                        <img src="/images/civic-profile/{{ $img }}.jpg" alt="">
                    </div>
                @endforeach
            </div>
            <div class="cph-ctabox" id="cph-ctabox">
                <h2 class="cph-cta-heading">Take the<br>Quiz</h2>
                <a class="cph-cta-btn" href="#cp-app">Let&rsquo;s Get Started</a>
            </div>
        </div>
    </div>

    {{-- ============ Quiz ============ --}}
    <section class="cph-quiz cph-gridbg">
        <div id="cp-app" class="cp" aria-live="polite"></div>
    </section>

    <script src="/js/gsap.min.js"></script>
    <script src="/js/ScrollTrigger.min.js"></script>

    @verbatim
    <script>
    /* ---------- Hero intro (photo wall slides in, pans, slides out) ---------- */
    (function () {
        var hero = document.getElementById('cph-hero');
        if (!hero) return;

        // Smooth-scroll every "Take the Quiz" link down to the quiz.
        document.querySelectorAll('a[href="#cp-app"]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                var el = document.getElementById('cp-app');
                if (!el) return;
                e.preventDefault();
                el.scrollIntoView({ behavior: 'smooth' });
            });
        });

        var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var force = new URLSearchParams(window.location.search).get('animate') === 'true';
        var played = sessionStorage.getItem('cphHeroPlayed');

        if (!window.gsap || reduced || (played && !force)) {
            hero.classList.add('cph-done');
            try { sessionStorage.setItem('cphHeroPlayed', '1'); } catch (e) {}
            return;
        }

        var row1 = hero.querySelector('.cph-row--1'), row2 = hero.querySelector('.cph-row--2');
        var tr1 = row1.querySelector('.cph-track'), tr2 = row2.querySelector('.cph-track');
        var logo = hero.querySelector('.cph-logo');
        var content = hero.querySelector('.cph-content');
        var h1 = content.querySelector('h1');
        var lede = content.querySelector('.cph-lede');
        var btns = content.querySelector('.cph-cta-row');
        var arrow = content.querySelector('.cph-arrow');

        // Initial state (inline styles only, so no-JS still shows the finished hero).
        gsap.set(row1, { yPercent: -100 });
        gsap.set(row2, { yPercent: 100 });
        gsap.set(content, { opacity: 0 });
        gsap.set(h1, { scale: 2, transformOrigin: 'center center' });
        gsap.set(lede, { opacity: 0, y: 10 });
        gsap.set(btns, { opacity: 0 });
        gsap.set(arrow, { opacity: 0 });

        // Lock scrolling and hide the site chrome while the intro plays.
        document.body.classList.add('cph-intro-active');
        var prevent = function (e) { e.preventDefault(); };
        window.addEventListener('wheel', prevent, { passive: false });
        window.addEventListener('touchmove', prevent, { passive: false });
        document.documentElement.style.overflow = 'hidden';

        var finished = false;
        function finish() {
            if (finished) return;
            finished = true;
            window.removeEventListener('wheel', prevent);
            window.removeEventListener('touchmove', prevent);
            document.documentElement.style.overflow = '';
            document.body.classList.remove('cph-intro-active');
            try { sessionStorage.setItem('cphHeroPlayed', '1'); } catch (e) {}
        }
        function abort() {   // failsafe: jump straight to the finished state
            finish();
            hero.classList.add('cph-done');
            gsap.set(content, { opacity: 1 });
            gsap.set(h1, { scale: 1 });
            gsap.set(lede, { opacity: 1, y: 0 });
            gsap.set(btns, { opacity: 1 });
            gsap.set(arrow, { opacity: 1 });
        }
        var failSafe = setTimeout(abort, 12000);

        // Preload the wall images behind a progress bar, then play.
        var bar = document.createElement('div');
        bar.className = 'cph-progress';
        hero.appendChild(bar);
        var srcs = [];
        hero.querySelectorAll('.cph-track img').forEach(function (im) {
            var s = im.currentSrc || im.src;
            if (srcs.indexOf(s) < 0) srcs.push(s);
        });
        var loadedCount = 0, started = false;
        function step() {
            loadedCount++;
            bar.style.width = Math.round(loadedCount / srcs.length * 100) + '%';
            if (loadedCount >= srcs.length) start();
        }
        srcs.forEach(function (s) {
            var im = new Image();
            im.onload = step; im.onerror = step;
            im.src = s;
        });
        if (!srcs.length) start();

        function start() {
            if (started) return;
            started = true;
            bar.style.width = '100%';
            setTimeout(function () { if (bar.parentNode) bar.parentNode.removeChild(bar); }, 200);

            // Center the tracks, then pan them a whole number of cards.
            var vw = hero.offsetWidth;
            var cardW = row1.offsetHeight * (5 / 7);
            var trackW = tr1.scrollWidth;
            var q = (vw - trackW) / 2;
            var O = Math.max(cardW, Math.floor(((trackW - vw) / 2) / cardW) * cardW);
            gsap.set([tr1, tr2], { x: q });

            var tl = gsap.timeline({
                onComplete: function () {
                    clearTimeout(failSafe);
                    finish();
                    hero.classList.add('cph-done');
                }
            });
            tl.to(row1, { yPercent: 0, duration: 1, ease: 'power2.out' }, 0);
            tl.to(row2, { yPercent: 0, duration: 1, ease: 'power2.out' }, 0);
            tl.to(logo, { opacity: 0, duration: 0.6, ease: 'power2.in' }, 1);
            tl.to(tr1, { x: q + O, duration: 2.5, ease: 'power1.inOut' }, 1);
            tl.to(tr2, { x: q - O, duration: 2.5, ease: 'power1.inOut' }, 1);
            tl.to(content, { opacity: 1, duration: 0.3, ease: 'power2.out' }, 3.5);
            tl.to(row1, { yPercent: -100, duration: 1.5, ease: 'power2.inOut' }, 3.5);
            tl.to(row2, { yPercent: 100, duration: 1.5, ease: 'power2.inOut' }, 3.5);
            tl.to(h1, { scale: 1, duration: 1, ease: 'power2.out' }, 4);
            tl.to(lede, { opacity: 1, y: 0, duration: 0.8, ease: 'power2.out' }, 4.7);
            tl.to(btns, { opacity: 1, duration: 0.8, ease: 'power2.out' }, 5);
            tl.to(arrow, { opacity: 1, duration: 0.8, ease: 'power2.out' }, 5.3);
        }
    })();

    /* ---------- Mosaic: pinned grid, CTA box expands and pushes tiles out ---------- */
    (function () {
        var area = document.getElementById('cph-mosaic-area');
        if (!area || !window.gsap || !window.ScrollTrigger) return;
        gsap.registerPlugin(ScrollTrigger);

        var sticky = area.querySelector('.cph-mosaic-sticky');
        var mosaic = area.querySelector('.cph-mosaic');
        var cta = area.querySelector('.cph-ctabox');
        var heading = cta.querySelector('.cph-cta-heading');
        var btn = cta.querySelector('.cph-cta-btn');
        var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // Push direction for each slot at each breakpoint (matches the original).
        var DIR_D = ['left', 'left', 'up', 'right', 'right', 'left', 'right', 'right', 'left', 'left', 'left', 'down', 'right'];
        var DIR_T = ['left', 'up', 'right', 'left', null, 'right', null, null, 'left', null, 'down', null, 'right'];
        var DIR_M = ['left', null, 'right', null, null, null, null, null, null, null, 'left', null, 'right'];

        var live = { triggers: [], tl: null, raf: null };

        function teardown() {
            live.triggers.forEach(function (t) { t.kill(); });
            live.triggers = [];
            if (live.tl) { live.tl.kill(); live.tl = null; }
            gsap.set(cta, { clearProps: 'position,top,left,width,height,xPercent,yPercent' });
            gsap.set(heading, { clearProps: 'fontSize' });
            gsap.set(btn, { clearProps: 'opacity,height,overflow,marginTop' });
            mosaic.querySelectorAll('.cph-slot').forEach(function (el) { el.style.transform = ''; });
        }

        function init() {
            var dirs = window.innerWidth <= 768 ? DIR_M : window.innerWidth <= 1024 ? DIR_T : DIR_D;
            var slots = [].slice.call(mosaic.querySelectorAll('.cph-slot'));

            // Home geometry, captured before any pinning or resizing of the CTA.
            var sr = sticky.getBoundingClientRect();
            var cr = cta.getBoundingClientRect();
            var home = { left: cr.left - sr.left, right: cr.right - sr.left, top: cr.top - sr.top, bottom: cr.bottom - sr.top };
            var W = sticky.offsetWidth, H = sticky.offsetHeight;

            gsap.set(cta, {
                position: 'absolute', top: '50%', left: '50%', xPercent: -50, yPercent: -50,
                width: cr.width, height: cr.height
            });
            gsap.set(btn, { opacity: 0, height: 0, overflow: 'hidden', marginTop: 0 });

            function push() {
                var e = sticky.getBoundingClientRect();
                var t = cta.getBoundingClientRect();
                var cur = { left: t.left - e.left, right: t.right - e.left, top: t.top - e.top, bottom: t.bottom - e.top };
                slots.forEach(function (el, i) {
                    var d = dirs[i];
                    if (!d) return;
                    var dx = 0, dy = 0;
                    if (d === 'left') dx = Math.min(0, cur.left - home.left);
                    else if (d === 'right') dx = Math.max(0, cur.right - home.right);
                    else if (d === 'up') dy = Math.min(0, cur.top - home.top);
                    else dy = Math.max(0, cur.bottom - home.bottom);
                    el.style.transform = 'translate(' + dx + 'px,' + dy + 'px)';
                });
            }

            var headingTo = Math.min(Math.max(56, window.innerWidth * 0.08), 100); // clamp(3.5rem, 8vw, 6.25rem)
            var tl = gsap.timeline({ paused: true });
            tl.to(cta, { height: H, duration: 0.4, ease: 'power2.inOut' }, 0);
            tl.to(cta, { width: W, duration: 0.4, ease: 'power2.inOut' }, 0.4);
            tl.to(heading, { fontSize: headingTo + 'px', duration: 0.4, ease: 'power2.inOut' }, 0.4);
            tl.to(btn, { height: 'auto', marginTop: '2rem', overflow: 'visible', duration: 0.1, ease: 'power2.out' }, 0.8);
            tl.to(btn, { opacity: 1, duration: 0.1, ease: 'power2.out' }, 0.9);
            live.tl = tl;

            if (reduced) {
                live.triggers.push(ScrollTrigger.create({
                    trigger: area, start: 'top 60%', once: true,
                    onEnter: function () { tl.progress(1); push(); }
                }));
            } else if (window.innerWidth <= 768) {
                live.triggers.push(ScrollTrigger.create({
                    trigger: area, start: 'top top', once: true,
                    onEnter: function () { tl.eventCallback('onUpdate', push); tl.play(); }
                }));
            } else {
                live.triggers.push(ScrollTrigger.create({
                    trigger: area, start: 'top top', end: '+=' + (2 * window.innerHeight),
                    pin: sticky, pinSpacing: true, scrub: 0.5,
                    onUpdate: function (st) { tl.progress(st.progress); push(); }
                }));
            }
            push();
        }

        var lastW = window.innerWidth, resizeT;
        window.addEventListener('resize', function () {
            if (window.innerWidth === lastW) return;
            lastW = window.innerWidth;
            clearTimeout(resizeT);
            resizeT = setTimeout(function () {
                teardown();
                init();
                ScrollTrigger.refresh();
            }, 250);
        });

        init();
    })();
    </script>

    <script>
    (function () {
        const QUIZ = {
            values: {
                title: 'Values',
                definition: 'These statements probe your beliefs about dissent, state power, and the people the state locks up. There are no right or wrong answers — this section maps where you stand.',
                scale: ['Strongly disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly agree'],
                // dim: which civic value the item measures; reverse: high agreement counts against the dimension
                questions: [
                    { text: 'No one should ever be imprisoned for their political beliefs, speech, or associations.', dim: 'liberty', reverse: false },
                    { text: 'The government should be able to jail members of political movements it labels dangerous, even without proving any crime.', dim: 'liberty', reverse: true },
                    { text: 'Radical ideas — even calls to fundamentally change our system of government — deserve full legal protection.', dim: 'liberty', reverse: false },
                    { text: 'People imprisoned for their politics deserve material support — letters, funds, and advocacy — from those of us outside.', dim: 'solidarity', reverse: false },
                    { text: 'Once someone is convicted, whatever happens to them in prison is no longer the public’s concern.', dim: 'solidarity', reverse: true },
                    { text: 'Standing with prisoners and their families is part of standing against repression.', dim: 'solidarity', reverse: false },
                    { text: 'Everyone deserves a fair trial and a real defense, even when the charge is terrorism or violence against the state.', dim: 'justice', reverse: false },
                    { text: 'It is acceptable to hold people in jail for years awaiting trial if the charges against them are serious enough.', dim: 'justice', reverse: true },
                    { text: 'Protesters and dissidents must face the same laws as everyone else — never harsher ones.', dim: 'justice', reverse: false },
                    { text: 'Civil disobedience is justified when the laws themselves are unjust.', dim: 'participation', reverse: false },
                    { text: 'Defending political prisoners is too risky for ordinary people to get involved in.', dim: 'participation', reverse: true },
                    { text: 'Speaking out against political imprisonment is worth the personal risk it can carry.', dim: 'participation', reverse: false }
                ]
            },
            engagement: {
                title: 'Engagement',
                definition: 'Political prisoners survive on outside support. Think back over the past year as you answer — there is no shame in “never”; every organizer started there.',
                scale: ['Never', 'Rarely', 'Sometimes', 'Often'],
                prompt: 'In the past year, how often have you…',
                questions: [
                    { text: 'Written a letter or card to an incarcerated person?' },
                    { text: 'Sent money for commissary, books, or phone calls to someone inside?' },
                    { text: 'Attended a protest, rally, or vigil for someone imprisoned or facing charges?' },
                    { text: 'Shown up for court support at a hearing or trial of an activist?' },
                    { text: 'Signed a petition for someone’s clemency, parole, or release?' },
                    { text: 'Donated to a bail fund or a legal defense fund?' },
                    { text: 'Contacted an official about a prisoner’s case or about prison conditions?' },
                    { text: 'Shared a political prisoner’s story or case with people around you?' },
                    { text: 'Joined a call-in, phone zap, or email campaign for someone inside?' },
                    { text: 'Volunteered with a prisoner-support or legal-support organization?' }
                ],
                levels: [
                    { min: 0, max: 7, name: 'Witness', desc: 'You are paying attention, and that matters. The first concrete step is smaller than you think — one letter, one signature, one story shared.' },
                    { min: 8, max: 15, name: 'Supporter', desc: 'You show up when it counts — signing, sharing, giving. People inside feel exactly this kind of steady support.' },
                    { min: 16, max: 23, name: 'Advocate', desc: 'Prisoner support is a regular part of your life, from the mailbox to the courtroom to the streets.' },
                    { min: 24, max: 30, name: 'Organizer', desc: 'You are the infrastructure. You write, give, show up, and bring others with you — movements run on people like you.' }
                ]
            },
            perceptions: {
                title: 'The Numbers',
                definition: 'Before Part 3, test your sense of the scale of repression and incarceration in the United States. Drag the dot to your guess, then submit — the real numbers surprise almost everyone.',
                questions: [
                    { label: 'World’s prisoners held in the U.S.', text: 'What percent of the <strong>world’s prisoners</strong> do you think are held in the United States?', actual: 20, caption: '% of the world’s incarcerated', note: 'With roughly 4% of the world’s population, the U.S. holds about 1 in 5 of its prisoners — the largest incarceration system on earth.', source: 'Prison Policy Initiative / World Prison Brief' },
                    { label: 'Jailed without a conviction', text: 'What percent of people held in <strong>local jails</strong> do you think have not been convicted of any crime?', actual: 70, caption: '% held pretrial', note: 'About 7 in 10 people in local jails are legally innocent — held pretrial, most because they cannot afford bail.', source: 'Bureau of Justice Statistics' },
                    { label: 'Convictions by plea deal', text: 'What percent of <strong>federal convictions</strong> do you think come from guilty pleas rather than trials?', actual: 98, caption: '% resolved by plea', note: 'Nearly all federal convictions are plea bargains — the government’s evidence is almost never tested before a jury, and the “trial penalty” keeps it that way.', source: 'U.S. Sentencing Commission' },
                    { label: 'Guantánamo detainees charged', text: 'What percent of the roughly 780 men held at <strong>Guantánamo Bay</strong> do you think were ever charged with a crime?', actual: 2, caption: '% ever charged', note: 'Of some 780 men held at Guantánamo, only about 16 were ever charged with anything — indefinite detention without charge, in plain sight.', source: 'ACLU' }
                ],
                tiers: [
                    { max: 7, name: 'Finger on the Pulse', desc: 'Your sense of where your fellow Americans stand is remarkably accurate.' },
                    { max: 15, name: 'Well Calibrated', desc: 'Your guesses tracked closely with what Americans actually said.' },
                    { max: 25, name: 'Broadly Aware', desc: 'You have the general shape right, though a few answers likely surprised you.' },
                    { max: 101, name: 'Ready to Recalibrate', desc: 'The real numbers differ a good deal from your guesses — which is exactly why the NPPC keeps counting.' }
                ]
            },
            knowledge: {
                title: 'Knowledge',
                definition: 'From the Espionage Act to COINTELPRO to today, the United States has a long record of jailing dissenters. Each question has one correct answer.',
                questions: [
                    { text: 'Under which law was Eugene V. Debs imprisoned for a 1918 anti-war speech — a law still used against whistleblowers today?', options: ['The Espionage Act', 'The Alien Enemies Act', 'The Patriot Act', 'The Volstead Act'], answer: 0 },
                    { text: 'While serving that sentence in Atlanta Federal Penitentiary, Debs did what in 1920?', options: ['Escaped to Mexico', 'Ran for President and received nearly a million votes', 'Renounced socialism', 'Went on the first recorded prison hunger strike'], answer: 1 },
                    { text: 'The Palmer Raids of 1919–1920 rounded up thousands of people for deportation because of:', options: ['Tax evasion', 'Their anarchist or communist politics, targeting immigrants especially', 'Bootlegging', 'Bank robbery'], answer: 1 },
                    { text: 'The Smith Act of 1940 was used in the late 1940s and 1950s to imprison:', options: ['Nazi saboteurs only', 'Leaders of the Communist Party USA for their political advocacy', 'War profiteers', 'Union-busting employers'], answer: 1 },
                    { text: 'The Hollywood Ten went to federal prison in 1950 for:', options: ['Espionage for the Soviet Union', 'Refusing to answer HUAC’s questions about their political beliefs and associations', 'Tax fraud', 'Violating the Production Code'], answer: 1 },
                    { text: 'COINTELPRO — exposed when activists burgled an FBI office in Media, Pennsylvania in 1971 — was:', options: ['A prison education initiative', 'A covert FBI program to surveil, infiltrate, and disrupt political movements', 'A Cold War radio station', 'A federal witness-protection program'], answer: 1 },
                    { text: 'Executive Order 9066, signed in 1942, led to:', options: ['The incarceration of some 120,000 people of Japanese ancestry, most of them U.S. citizens', 'The desegregation of the armed forces', 'The internment of German POWs', 'The creation of the CIA'], answer: 0 },
                    { text: 'The 1971 Attica prison uprising — whose retaking left 39 people dead — began as a demand for:', options: ['Shorter sentences for all', 'Humane conditions, political rights, and an end to brutality', 'Televisions in every cell', 'The warden’s resignation'], answer: 1 },
                    { text: 'Leonard Peltier, imprisoned for nearly half a century until his sentence was commuted in 2025, was a leader in:', options: ['The American Indian Movement', 'The Weather Underground', 'The Young Lords', 'The IWW'], answer: 0 },
                    { text: 'Which civil rights leader wrote "Letter from Birmingham Jail" while jailed for nonviolent protest?', options: ['Malcolm X', 'Martin Luther King Jr.', 'Thurgood Marshall', 'Medgar Evers'], answer: 1 },
                    { text: 'The legal principle of "habeas corpus" protects a person from:', options: ['Being forced to testify against themselves', 'Unlawful or indefinite detention without being brought before a court', 'Cruel and unusual punishment', 'Being tried twice for the same crime'], answer: 1 },
                    { text: 'A "political prisoner" is generally understood to be someone imprisoned primarily because of:', options: ['Violent crimes against individuals', 'Their political beliefs, activism, or association', 'Financial fraud', 'Repeated traffic violations'], answer: 1 }
                ]
            },
            profiles: {
                liberty: { name: 'The Dissent Defender', desc: 'You put the right to dissent first. To you, no one should ever sit in a cell for their beliefs, speech, or associations — and the surest measure of a free society is how it treats its radicals.' },
                solidarity: { name: 'The Prisoner’s Ally', desc: 'You believe no one inside should be forgotten. Letters, commissary, family support, and advocacy are, to you, the backbone of resisting repression — your solidarity does not stop at the wall.' },
                justice: { name: 'The Due Process Guardian', desc: 'Fair trials anchor your worldview. The state must prove its case against everyone — dissident or not — and pretrial detention, secret evidence, and show trials corrode everything they touch.' },
                participation: { name: 'The Resister', desc: 'To you, repression is answered by showing up. Court support, protest, civil disobedience — you accept that resisting injustice carries risk, and you take it anyway.' }
            },
            dimLabels: { liberty: 'Right to Dissent', solidarity: 'Prisoner Solidarity', justice: 'Due Process', participation: 'Resistance' },
            knowledgeTiers: [
                { min: 90, name: 'Movement Historian' },
                { min: 70, name: 'Well Versed' },
                { min: 50, name: 'Learning the History' },
                { min: 0, name: 'Just Getting Started' }
            ]
        };

        // Build the linear step list.
        const PARTS = ['values', 'engagement', 'knowledge'];
        const steps = [{ type: 'intro' }];
        PARTS.forEach(function (p) {
            if (p === 'knowledge') {
                // Perception-check interlude sits between Part 2 and Part 3.
                steps.push({ type: 'guessintro' });
                QUIZ.perceptions.questions.forEach(function (_, i) { steps.push({ type: 'guess', i: i }); });
            }
            steps.push({ type: 'part', part: p });
            QUIZ[p].questions.forEach(function (_, i) { steps.push({ type: 'q', part: p, i: i }); });
        });
        steps.push({ type: 'results' });

        const answers = { values: [], engagement: [], knowledge: [], perceptions: [] };
        const guessDraft = {};
        let cur = 0;
        let locked = false;
        const root = document.getElementById('cp-app');

        const totalQ = QUIZ.values.questions.length + QUIZ.engagement.questions.length + QUIZ.knowledge.questions.length + QUIZ.perceptions.questions.length;
        function answeredCount() {
            return PARTS.concat(['perceptions']).reduce(function (n, p) {
                return n + answers[p].filter(function (x) { return x != null; }).length;
            }, 0);
        }

        function esc(s) {
            return String(s).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        function go(n) {
            cur = Math.max(0, Math.min(steps.length - 1, n));
            locked = false;
            render();
            var el = document.getElementById('cp-app');
            if (el && cur > 0) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function select(part, i, val) {
            if (locked) return;
            answers[part][i] = val;
            locked = true;
            render();
            setTimeout(function () { go(cur + 1); }, 260);
        }

        function partProgress(part, i) {
            const labels = { values: 'Part 1 · Values', engagement: 'Part 2 · Engagement', perceptions: 'Interlude · The Numbers', knowledge: 'Part 3 · Knowledge' };
            return '<div class="cp-progress">'
                + '<div class="cp-progress-meta"><span class="part part--' + part + '">' + labels[part] + '</span>'
                + '<span>Question ' + (i + 1) + ' of ' + QUIZ[part].questions.length + '</span></div>'
                + '<div class="cp-progress-track"><div class="cp-progress-fill" style="width:' + Math.round(answeredCount() / totalQ * 100) + '%"></div></div>'
                + '</div>';
        }

        function render() {
            const s = steps[cur];
            if (s.type === 'intro') return renderIntro();
            if (s.type === 'part') return renderPartIntro(s.part);
            if (s.type === 'q') return renderQuestion(s.part, s.i);
            if (s.type === 'guessintro') return renderGuessIntro();
            if (s.type === 'guess') return renderGuess(s.i);
            if (s.type === 'results') return renderResults();
        }

        function renderIntro() {
            root.innerHTML =
                '<div class="cp-hero">'
                + '<p class="cp-eyebrow">National Political Prisoner Coalition</p>'
                + '<h1>Discover your Civic Profile</h1>'
                + '<p class="cp-lede">An interactive quiz that measures your <strong>values</strong> on dissent and state power, your <strong>engagement</strong> with the imprisoned, and your <strong>knowledge</strong> of America&rsquo;s history of political repression.</p>'
                + '</div>'
                + '<div class="cp-parts">'
                + partCard('1', 'Values', 'Where you stand on dissent, due process, and the power of the state.', 'var(--cp-gold)')
                + partCard('2', 'Engagement', 'What you do to support political prisoners and resist repression.', 'var(--cp-accent)')
                + partCard('3', 'Knowledge', 'How well you know America’s history of political imprisonment.', 'var(--cp-teal)')
                + '</div>'
                + '<div style="text-align:center">'
                + '<button class="cp-btn" id="cp-start">Let’s Get Started</button>'
                + '<p class="cp-note">Takes about 5 minutes · ' + totalQ + ' questions · No sign-up required</p>'
                + '</div>';
            document.getElementById('cp-start').addEventListener('click', function () { go(cur + 1); });
        }

        function partCard(n, title, desc, color) {
            return '<div class="cp-part-card" style="--pc:' + color + '"><span class="n">' + n + '</span><h3>' + title + '</h3><p>' + desc + '</p></div>';
        }

        function renderPartIntro(part) {
            const idx = PARTS.indexOf(part) + 1;
            const q = QUIZ[part];
            root.innerHTML =
                '<div class="cp-partintro">'
                + '<div class="step">Part ' + idx + ' of 3</div>'
                + '<h2>' + esc(q.title) + '</h2>'
                + '<p>' + esc(q.definition) + '</p>'
                + '<button class="cp-btn" id="cp-continue">Begin Part ' + idx + '</button>'
                + '</div>'
                + '<div style="text-align:center;margin-top:18px"><button class="cp-back" id="cp-back">← Back</button></div>';
            document.getElementById('cp-continue').addEventListener('click', function () { go(cur + 1); });
            document.getElementById('cp-back').addEventListener('click', function () { go(cur - 1); });
        }

        function renderGuessIntro() {
            const q = QUIZ.perceptions;
            root.innerHTML =
                '<div class="cp-partintro">'
                + '<div class="step" style="color:var(--cp-perc-fg)">Interlude</div>'
                + '<h2>' + esc(q.title) + '</h2>'
                + '<p>' + esc(q.definition) + '</p>'
                + '<button class="cp-btn" id="cp-continue">Start Guessing</button>'
                + '</div>'
                + '<div style="text-align:center;margin-top:18px"><button class="cp-back" id="cp-back">← Back</button></div>';
            document.getElementById('cp-continue').addEventListener('click', function () { go(cur + 1); });
            document.getElementById('cp-back').addEventListener('click', function () { go(cur - 1); });
        }

        function renderGuess(i) {
            const q = QUIZ.perceptions.questions[i];
            const submitted = answers.perceptions[i] != null;
            const val = submitted ? answers.perceptions[i] : (guessDraft[i] != null ? guessDraft[i] : 50);

            const ticks = [0, 25, 50, 75, 100].map(function (t) {
                return '<span class="cp-tick" style="left:' + t + '%">' + t + '%</span>';
            }).join('');

            let ctaOrFeedback;
            if (submitted) {
                const diff = Math.abs(val - q.actual);
                const lead = diff <= 5 ? 'Impressively close.' : diff <= 15 ? 'Not far off.' : 'Most people are surprised.';
                ctaOrFeedback =
                    '<div class="cp-guess-feedback">'
                    + '<p>You said <span class="you">' + val + '%</span>. The actual answer is <span class="act">' + q.actual + '%</span>. '
                    + esc(lead) + ' ' + esc(q.note) + '</p>'
                    + '<button class="cp-btn" id="cp-guess-continue">Continue</button>'
                    + '<p class="cp-note">Source: ' + esc(q.source) + '.</p>'
                    + '</div>';
            } else {
                ctaOrFeedback = '';
            }

            root.innerHTML =
                partProgress('perceptions', i)
                + '<div class="cp-stage">'
                + '<span class="cp-part-badge cp-part-badge--perceptions">The Numbers</span>'
                + '<p class="cp-q cp-guess-q">' + q.text + '</p>'
                + '<div class="cp-guess-ctarow"' + (submitted ? ' hidden' : '') + '>'
                + '<p class="cp-guess-hint">Drag the dot to your guess.</p>'
                + '<button class="cp-guess-submit" id="cp-guess-submit">Submit</button>'
                + '</div>'
                + '<div class="cp-slider">'
                + '<div class="cp-slider-val" id="cp-slider-val"></div>'
                + '<input class="cp-range" id="cp-range" type="range" min="0" max="100" step="1" value="' + val + '"'
                + (submitted ? ' disabled' : '') + ' aria-label="Your guess, as a percent of Americans">'
                + (submitted ? '<span class="cp-guess-actual" style="left:calc(' + q.actual + '% + ' + ((0.5 - q.actual / 100) * 22).toFixed(1) + 'px)" aria-hidden="true"></span>' : '')
                + '</div>'
                + '<div class="cp-axis" aria-hidden="true">'
                + '<div class="cp-axis-line">' + ticks + '</div>'
                + '<div class="cp-axis-caption">' + esc(q.caption) + '</div>'
                + '</div>'
                + ctaOrFeedback
                + '<div class="cp-stage-foot">'
                + '<button class="cp-back" id="cp-back">← Back</button>'
                + '<span class="cp-tap-hint">' + (submitted ? 'Tap Continue to move on' : 'Drag the dot, then tap Submit') + '</span>'
                + '</div>'
                + '</div>';

            const range = document.getElementById('cp-range');
            const label = document.getElementById('cp-slider-val');
            function place() {
                const v = parseInt(range.value, 10);
                label.textContent = v + '%';
                // Track the thumb center: the thumb (22px) travels the track minus its own width.
                label.style.left = 'calc(' + v + '% + ' + ((0.5 - v / 100) * 22).toFixed(1) + 'px)';
            }
            place();
            if (!submitted) {
                range.addEventListener('input', function () {
                    guessDraft[i] = parseInt(range.value, 10);
                    place();
                });
                document.getElementById('cp-guess-submit').addEventListener('click', function () {
                    answers.perceptions[i] = parseInt(range.value, 10);
                    render();
                });
            } else {
                document.getElementById('cp-guess-continue').addEventListener('click', function () { go(cur + 1); });
            }
            document.getElementById('cp-back').addEventListener('click', function () { go(cur - 1); });
        }

        function renderQuestion(part, i) {
            const q = QUIZ[part].questions[i];
            const selected = answers[part][i];
            let opts, hint = '';

            if (part === 'values') {
                hint = '<p class="cp-scale-hint">How much do you agree?</p>';
                opts = QUIZ.values.scale.map(function (label, idx) {
                    return optButton(part, i, idx, label, selected === idx, null);
                }).join('');
            } else if (part === 'engagement') {
                opts = QUIZ.engagement.scale.map(function (label, idx) {
                    return optButton(part, i, idx, label, selected === idx, null);
                }).join('');
            } else {
                opts = q.options.map(function (label, idx) {
                    return optButton(part, i, idx, label, selected === idx, 'ABCD'[idx]);
                }).join('');
            }

            const stem = part === 'engagement'
                ? '<span style="color:var(--cp-muted);font-weight:700">' + esc(QUIZ.engagement.prompt) + '</span><br>' + esc(q.text)
                : esc(q.text);

            root.innerHTML =
                partProgress(part, i)
                + '<div class="cp-stage">'
                + '<span class="cp-part-badge cp-part-badge--' + part + '">' + esc(QUIZ[part].title) + '</span>'
                + '<p class="cp-q">' + stem + '</p>'
                + hint
                + '<div class="cp-opts">' + opts + '</div>'
                + '<div class="cp-stage-foot">'
                + '<button class="cp-back" id="cp-back"' + (cur <= 1 ? ' disabled' : '') + '>← Back</button>'
                + '<span class="cp-tap-hint">Tap an answer to continue</span>'
                + '</div>'
                + '</div>';

            Array.prototype.forEach.call(root.querySelectorAll('.cp-opt'), function (btn) {
                btn.addEventListener('click', function () {
                    select(part, i, parseInt(btn.getAttribute('data-val'), 10));
                });
            });
            document.getElementById('cp-back').addEventListener('click', function () { go(cur - 1); });
        }

        function optButton(part, i, val, label, isSel, key) {
            return '<button class="cp-opt' + (isSel ? ' is-selected' : '') + '" data-val="' + val + '" aria-pressed="' + (isSel ? 'true' : 'false') + '">'
                + (key ? '<span class="cp-opt-key">' + key + '</span>' : '')
                + '<span>' + esc(label) + '</span></button>';
        }

        // ---------- Scoring ----------
        function scoreValues() {
            const totals = { liberty: 0, solidarity: 0, justice: 0, participation: 0 };
            const counts = { liberty: 0, solidarity: 0, justice: 0, participation: 0 };
            QUIZ.values.questions.forEach(function (q, i) {
                let v = answers.values[i];
                if (v == null) v = 2; // neutral fallback
                let score = v + 1;             // 1..5
                if (q.reverse) score = 6 - score;
                totals[q.dim] += score;
                counts[q.dim] += 1;
            });
            const pct = {};
            let top = null, topVal = -1;
            Object.keys(totals).forEach(function (d) {
                const max = counts[d] * 5, min = counts[d] * 1;
                pct[d] = Math.round((totals[d] - min) / (max - min) * 100);
                if (totals[d] > topVal) { topVal = totals[d]; top = d; }
            });
            return { pct: pct, top: top };
        }

        function scoreEngagement() {
            const sum = answers.engagement.reduce(function (a, v) { return a + (v == null ? 0 : v); }, 0);
            const level = QUIZ.engagement.levels.find(function (l) { return sum >= l.min && sum <= l.max; }) || QUIZ.engagement.levels[0];
            return { sum: sum, max: QUIZ.engagement.questions.length * 3, level: level };
        }

        function scorePerceptions() {
            const rows = QUIZ.perceptions.questions.map(function (q, i) {
                return { q: q, guess: answers.perceptions[i] };
            }).filter(function (r) { return r.guess != null; });
            if (!rows.length) return null;
            const avg = Math.round(rows.reduce(function (a, r) { return a + Math.abs(r.guess - r.q.actual); }, 0) / rows.length);
            const tier = QUIZ.perceptions.tiers.find(function (t) { return avg <= t.max; });
            return { rows: rows, avg: avg, tier: tier };
        }

        function scoreKnowledge() {
            let correct = 0;
            const missed = [];
            QUIZ.knowledge.questions.forEach(function (q, i) {
                if (answers.knowledge[i] === q.answer) correct++;
                else missed.push({ q: q.text, a: q.options[q.answer] });
            });
            const total = QUIZ.knowledge.questions.length;
            const pct = Math.round(correct / total * 100);
            const tier = QUIZ.knowledgeTiers.find(function (t) { return pct >= t.min; });
            return { correct: correct, total: total, pct: pct, tier: tier.name, missed: missed };
        }

        function renderResults() {
            const v = scoreValues();
            const e = scoreEngagement();
            const k = scoreKnowledge();
            const profile = QUIZ.profiles[v.top];

            const bars = Object.keys(QUIZ.dimLabels).map(function (d) {
                const isTop = d === v.top;
                return '<div class="cp-bar' + (isTop ? ' is-top' : '') + '">'
                    + '<div class="bar-top"><span>' + esc(QUIZ.dimLabels[d]) + '</span><span class="v">' + v.pct[d] + '%</span></div>'
                    + '<div class="cp-bar-track"><div class="cp-bar-fill" style="width:' + v.pct[d] + '%"></div></div>'
                    + '</div>';
            }).join('');

            // knowledge ring
            const R = 46, C = 2 * Math.PI * R, off = C * (1 - k.pct / 100);
            const ring = '<div class="cp-ring"><svg width="104" height="104" viewBox="0 0 104 104">'
                + '<circle cx="52" cy="52" r="' + R + '" fill="none" stroke="#e5ebee" style="stroke:var(--cp-line)" stroke-width="9"></circle>'
                + '<circle cx="52" cy="52" r="' + R + '" fill="none" stroke="#5660fe" stroke-width="9" stroke-linecap="round" stroke-dasharray="' + C.toFixed(1) + '" stroke-dashoffset="' + off.toFixed(1) + '"></circle>'
                + '</svg><div class="pct"><span class="num">' + k.pct + '%</span><span class="den">' + k.correct + ' / ' + k.total + '</span></div></div>';

            let missedHtml = '';
            if (k.missed.length) {
                missedHtml = '<details class="cp-missed"><summary>Review the ' + k.missed.length + ' you missed</summary><ul>'
                    + k.missed.map(function (m) {
                        return '<li><span class="qx">' + esc(m.q) + '</span><span class="ax">Correct answer: ' + esc(m.a) + '</span></li>';
                    }).join('') + '</ul></details>';
            } else {
                missedHtml = '<p style="margin-top:16px;color:var(--cp-good);font-weight:700">Perfect score — every answer correct.</p>';
            }

            const summary = 'Your profile: <strong>' + esc(profile.name) + '</strong> · ' + esc(e.level.name) + ' · ' + k.pct + '% on civic knowledge.';

            root.innerHTML =
                '<div class="cp-results-head"><span class="eyebrow">Your results</span><h1>Your Civic Profile</h1></div>'
                + '<p class="cp-summary">' + summary + '</p>'

                + '<div class="cp-res-card">'
                + '<div class="label">Part 1 · Civic Values</div>'
                + '<h2>' + esc(profile.name) + '</h2>'
                + '<p>' + esc(profile.desc) + '</p>'
                + '<div class="cp-bars">' + bars + '</div>'
                + '</div>'

                + '<div class="cp-res-card">'
                + '<div class="label">Part 2 · Civic Engagement</div>'
                + '<div class="cp-score-row">'
                + '<div class="cp-ring"><svg width="104" height="104" viewBox="0 0 104 104">'
                + '<circle cx="52" cy="52" r="' + R + '" fill="none" stroke="#e5ebee" style="stroke:var(--cp-line)" stroke-width="9"></circle>'
                + '<circle cx="52" cy="52" r="' + R + '" fill="none" stroke="#5660fe" stroke-width="9" stroke-linecap="round" stroke-dasharray="' + C.toFixed(1) + '" stroke-dashoffset="' + (C * (1 - e.sum / e.max)).toFixed(1) + '"></circle>'
                + '</svg><div class="pct"><span class="num">' + e.sum + '</span><span class="den">of ' + e.max + '</span></div></div>'
                + '<div><div class="cp-score-tier">' + esc(e.level.name) + '</div><p style="margin:0;color:var(--cp-muted)">' + esc(e.level.desc) + '</p></div>'
                + '</div></div>'

                + perceptionCard()

                + '<div class="cp-res-card">'
                + '<div class="label">Part 3 · Civic Knowledge</div>'
                + '<div class="cp-score-row">' + ring
                + '<div><div class="cp-score-tier">' + esc(k.tier) + '</div><p style="margin:0;color:var(--cp-muted)">You answered ' + k.correct + ' of ' + k.total + ' questions correctly.</p></div>'
                + '</div>' + missedHtml + '</div>'

                + '<div class="cp-actions"><h3>Turn your profile into action</h3><div class="cp-action-grid">'
                + actionCard('/volunteer', '✋', 'Volunteer', 'Give your time to the movement.')
                + actionCard('/petitions', '✍', 'Sign a petition', 'Add your name to active campaigns.')
                + actionCard('/prisoner-outreach', '✉', 'Support a prisoner', 'Write to someone inside.')
                + '</div></div>'

                + '<div class="cp-foot">'
                + '<div class="cp-share" id="cp-share"></div>'
                + '<button class="cp-retake" id="cp-retake">Retake the quiz</button>'
                + '</div>';

            // share buttons (built in JS so they reflect the live URL)
            const url = window.location.origin + window.location.pathname;
            const text = 'I just discovered my Civic Profile: ' + profile.name + '. Take the National Political Prisoner Coalition’s quiz on political repression in America.';
            const share = document.getElementById('cp-share');
            share.innerHTML =
                '<a href="https://twitter.com/intent/tweet?text=' + encodeURIComponent(text) + '&url=' + encodeURIComponent(url) + '" target="_blank" rel="noopener" aria-label="Share on X"><svg viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>'
                + '<a href="https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url) + '" target="_blank" rel="noopener" aria-label="Share on Facebook"><svg viewBox="0 0 24 24"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.412c0-3.027 1.792-4.7 4.533-4.7 1.312 0 2.686.235 2.686.235v2.97h-1.513c-1.491 0-1.956.93-1.956 1.886v2.27h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073"/></svg></a>';

            document.getElementById('cp-retake').addEventListener('click', function () {
                answers.values = []; answers.engagement = []; answers.knowledge = []; answers.perceptions = [];
                Object.keys(guessDraft).forEach(function (k) { delete guessDraft[k]; });
                go(0);
            });
        }

        function perceptionCard() {
            const p = scorePerceptions();
            if (!p) return '';
            const rows = p.rows.map(function (r) {
                return '<div class="cp-guess-row">'
                    + '<div class="top"><span>' + esc(r.q.label) + '</span>'
                    + '<span class="v">you ' + r.guess + '% · actual ' + r.q.actual + '%</span></div>'
                    + '<div class="cp-guess-track">'
                    + '<span class="cp-gdot cp-gdot--you" style="left:' + r.guess + '%"></span>'
                    + '<span class="cp-gdot cp-gdot--act" style="left:' + r.q.actual + '%"></span>'
                    + '</div></div>';
            }).join('');
            return '<div class="cp-res-card">'
                + '<div class="label">Interlude · The Numbers</div>'
                + '<h2>' + esc(p.tier.name) + '</h2>'
                + '<p>' + esc(p.tier.desc) + ' On average, your guesses were within <strong>' + p.avg + ' points</strong> of the real numbers.</p>'
                + '<div class="cp-guess-rows">' + rows + '</div>'
                + '<p class="cp-note cp-guess-legend" style="text-align:left"><span class="sw" style="background:var(--cp-accent)"></span>your guess &nbsp; <span class="sw" style="background:var(--cp-gold)"></span>actual</p>'
                + '</div>';
        }

        function actionCard(href, ic, title, desc) {
            return '<a class="cp-action" href="' + href + '"><div class="ic">' + ic + '</div><strong>' + title + '</strong><span>' + desc + '</span></a>';
        }

        render();
    })();
    </script>
    @endverbatim
@endsection
