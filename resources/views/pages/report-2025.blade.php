@extends('app')

@section('title', 'Report 2025 — While There Is a Soul in Prison | NPPC')

@section('meta_description')The NPPC 2025 interactive annual report: 6,987 cases documented, 88 releases marked — including the census's longest-running entry — and a year that kept redefining who counts as a political prisoner.@endsection

@section('og_image'){{ asset('storage/prisoners/leonard-peltier.jpg') }}@endsection

@section('head')
<style>
/* ============================================================
   Report 2025 — interactive annual-report microsite.
   Scrollytelling story galleries (sticky image stack + step
   cards), pinned full-viewport story intros, parallax policy
   panels, animated financial bars, and scrollable donor/staff
   cards. Vanilla JS only.
   ============================================================ */
body.page-report-2025 main.container,
body.page-report-2025 .container { width: 100% !important; max-width: none !important; padding-left: 0 !important; padding-right: 0 !important; }
body.page-report-2025 { background: #0a0a12; }

.r25 { --ink: #ececf2; --dim: rgba(236,236,242,0.62); --acc: #5660fe; --acc2: #8f97ff;
       --paper: #f0f1f7; --deep: #0a0a12; --navy: #12122a;
       color: var(--ink); font-size: 16px; line-height: 1.7; overflow-x: clip; }
.r25-wrap { max-width: 1180px; margin: 0 auto; padding: 0 28px; }
.r25-narrow { max-width: 860px; margin: 0 auto; padding: 0 28px; }

/* reveal framework */
.rv { opacity: 0; transform: translateY(34px); transition: opacity .9s ease, transform .9s cubic-bezier(.22,1,.36,1); }
.rv.rv-fade { transform: none; }
.rv.in { opacity: 1; transform: none; }
.rv.d2 { transition-delay: .25s; } .rv.d3 { transition-delay: .5s; } .rv.d4 { transition-delay: .8s; }
@media (prefers-reduced-motion: reduce) { .rv { opacity: 1 !important; transform: none !important; transition: none; } }

.r25-label { display: inline-block; font-size: 12px; font-weight: 800; letter-spacing: .22em; text-transform: uppercase; color: var(--acc2); margin-bottom: 18px; }
.r25-title { font-size: clamp(2rem, 4.2vw, 3.2rem); font-weight: 900; line-height: 1.08; color: var(--ink); margin: 0 0 22px; letter-spacing: -.015em; }
.r25-lede { font-size: clamp(1.05rem, 1.6vw, 1.3rem); color: rgba(236,236,242,.85); max-width: 56ch; }

/* circle scroll cue (the reference's chevron-in-circle) */
.r25-cue { display: inline-flex; flex-direction: column; align-items: center; gap: 12px; text-decoration: none; }
.r25-cue .h6 { font-size: 11px; letter-spacing: .26em; text-transform: uppercase; color: rgba(255,255,255,.72); font-weight: 700; }
.r25-cue .circ { width: 44px; height: 44px; border-radius: 50%; background: #fff; display: flex; align-items: center; justify-content: center; animation: r25Bob 2.4s ease-in-out infinite; }
.r25-cue .circ::after { content: ''; width: 10px; height: 10px; border-right: 2px solid var(--acc); border-bottom: 2px solid var(--acc); transform: rotate(45deg) translate(-1px,-1px); }
@keyframes r25Bob { 0%,100% { transform: translateY(0); } 50% { transform: translateY(8px); } }
@media (prefers-reduced-motion: reduce) { .r25-cue .circ { animation: none; } }

/* ── hero ────────────────────────────────────────────────── */
.r25-hero { position: relative; min-height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; }
.r25-hero-bg { position: absolute; inset: 0; background: url('/storage/history/standing-rock.jpg') center / cover no-repeat;
  filter: grayscale(55%) brightness(.34); animation: r25Drift 16s ease-out forwards; }
@keyframes r25Drift { from { transform: scale(1.1); } to { transform: scale(1.02); } }
.r25-hero-shade { position: absolute; inset: 0; background: radial-gradient(ellipse at 50% 70%, rgba(10,10,18,.15), rgba(10,10,18,.9)); }
.r25-hero-body { position: relative; z-index: 2; padding: 100px 24px; }
.r25-hero h1 { font-size: clamp(2.4rem, 6vw, 4.8rem); font-weight: 900; line-height: 1.08; color: #fff; margin: 0 0 44px; letter-spacing: -.02em; max-width: 20ch; }
.r25-hero h1 strong { display: block; font-weight: 900; }
.r25-hero h1 strong:last-child { color: var(--acc2); }
@media (prefers-reduced-motion: reduce) { .r25-hero-bg { animation: none; } }

/* ── letter ──────────────────────────────────────────────── */
.r25-letter { padding: 110px 0; }
.r25-letter p { margin: 0 0 20px; font-size: 16.5px; color: rgba(236,236,242,.85); }
.r25-letter blockquote { margin: 30px 0; padding-left: 22px; border-left: 3px solid var(--acc); font-size: 1.15rem; font-weight: 700; color: var(--ink); }
.r25-sign { font-weight: 800; color: var(--ink); }
.r25-sign span { display: block; font-weight: 400; color: var(--dim); font-size: 14px; }

/* ── section intros ──────────────────────────────────────── */
.r25-sect { padding: 110px 0 90px; }

/* ── story: full-viewport pinned intro ───────────────────── */
.r25-fintro { position: relative; min-height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; overflow: hidden; }
.r25-fintro-bg { position: absolute; inset: -10% 0; background: center 22% / cover no-repeat; filter: grayscale(35%) brightness(.34); will-change: transform; }
.r25-fintro-body { position: relative; z-index: 2; padding: 60px 24px; }
.r25-fintro h2 { font-size: clamp(2.6rem, 6.4vw, 5.2rem); font-weight: 900; color: #fff; margin: 0 0 26px; letter-spacing: -.02em; }
.r25-fintro-meta { display: flex; gap: 44px; justify-content: center; margin-bottom: 54px; }
.r25-fintro-meta span { font-size: clamp(.95rem, 1.5vw, 1.15rem); font-weight: 700; color: var(--acc2); line-height: 1.5; }
.r25-fintro-meta span em { display: block; font-style: normal; font-weight: 400; font-size: 12px; letter-spacing: .18em; text-transform: uppercase; color: rgba(255,255,255,.6); margin-bottom: 4px; }

/* ── story: scrollytelling gallery ───────────────────────── */
.r25-sg { position: relative; }
.r25-sg-grid { display: grid; grid-template-columns: 1fr 1fr; }
.r25-sg-fig { position: sticky; top: 0; height: 100vh; overflow: hidden; }
.r25-sg-fig picture, .r25-sg-fig img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity .7s ease; }
.r25-sg-fig img.on { opacity: 1; }
.r25-sg-steps { display: flex; flex-direction: column; }
.r25-step { min-height: 92vh; display: flex; align-items: center; padding: 12vh 6vw; }
.r25-step-card { border-radius: 10px; padding: 40px 38px; max-width: 480px; transition: transform .5s cubic-bezier(.22,1,.36,1), box-shadow .5s; }
.r25-step.on .r25-step-card { transform: translateY(-6px); box-shadow: 0 30px 70px rgba(0,0,0,.45); }
.r25-step-card p { margin: 0; font-size: 16.5px; line-height: 1.75; }
.r25-step-card .p2 { font-size: clamp(1.25rem, 2vw, 1.6rem); font-weight: 800; line-height: 1.45; }
.r25-step-card a { color: inherit; text-underline-offset: 3px; }
/* step palettes (the reference's light-violet / light-beige / dark-olive / dark-blue) */
.r25-c1 { background: #d9dcff; color: #14142b; }
.r25-c2 { background: #ece7d8; color: #1d1b12; }
.r25-c3 { background: #23281c; color: #e9ecdf; }
.r25-c4 { background: #101a3c; color: #dfe4f7; }
@media (max-width: 860px) {
  .r25-sg-grid { grid-template-columns: 1fr; }
  .r25-sg-fig { height: 46vh; }
  .r25-step { min-height: auto; padding: 26px 20px; }
  .r25-step-card { max-width: none; }
}

/* ── quote band (the reference's freedom-video slot) ─────── */
.r25-band { background: var(--navy); padding: 96px 0; text-align: center; }
.r25-band p { font-size: clamp(1.3rem, 2.6vw, 2rem); font-weight: 800; color: var(--ink); max-width: 30ch; margin: 0 auto 14px; line-height: 1.5; }
.r25-band span { font-size: 13.5px; color: var(--dim); }

/* ── still inside ────────────────────────────────────────── */
.r25-inside { padding: 110px 0; }
.r25-inside-names { display: flex; gap: 18px; flex-wrap: wrap; margin: 30px 0 34px; }
.r25-inside-names a { font-size: clamp(1.2rem, 2.4vw, 1.8rem); font-weight: 900; color: var(--acc2); text-decoration: none; border-bottom: 3px solid rgba(143,151,255,.35); padding-bottom: 2px; transition: border-color .2s; }
.r25-inside-names a:hover { border-color: var(--acc2); }
.r25-inside p { color: rgba(236,236,242,.82); max-width: 760px; }

/* ── CTA bands ───────────────────────────────────────────── */
.r25-cta { padding: 84px 0; }
.r25-cta.is-acc { background: linear-gradient(120deg, #262a6e, var(--acc)); }
.r25-cta.is-black { background: #050508; }
.r25-cta h3 { font-size: clamp(1.4rem, 2.6vw, 2.1rem); font-weight: 900; color: #fff; margin: 0 0 26px; max-width: 34ch; }
.r25-btn { display: inline-block; padding: 14px 30px; border-radius: 4px; background: #fff; color: #14142b; font-weight: 800; font-size: 14px; letter-spacing: .06em; text-transform: uppercase; text-decoration: none; margin-right: 14px; margin-bottom: 10px; }
.r25-btn.ghost { background: transparent; color: #fff; border: 2px solid rgba(255,255,255,.6); }
.r25-btn:hover { opacity: .9; }

/* ── policy panels (parallax) ────────────────────────────── */
.r25-policy { position: relative; min-height: 92vh; display: flex; align-items: center; overflow: hidden; padding: 80px 0; }
.r25-policy-bg { position: absolute; inset: -12% 0; background: center / cover no-repeat; filter: grayscale(60%) brightness(.3); will-change: transform; }
.r25-policy-card { position: relative; z-index: 2; background: rgba(10,10,18,.78); border: 1px solid rgba(236,236,242,.12); border-radius: 12px; padding: 46px 44px; max-width: 620px; backdrop-filter: blur(4px); }
.r25-policy-date { font-size: 12px; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; color: var(--acc2); margin-bottom: 14px; }
.r25-policy-card h3 { font-size: clamp(1.5rem, 3vw, 2.3rem); font-weight: 900; color: #fff; margin: 0 0 16px; line-height: 1.15; }
.r25-policy-card p { color: rgba(236,236,242,.85); margin: 0 0 14px; font-size: 15.5px; }
.r25-policy-card a { color: var(--acc2); }

/* ── records / mini case studies ─────────────────────────── */
.r25-minis { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; margin-top: 50px; }
.r25-mini { background: rgba(236,236,242,.045); border: 1px solid rgba(236,236,242,.09); border-radius: 10px; padding: 30px 28px; }
.r25-mini h4 { font-size: 17px; font-weight: 800; color: var(--ink); margin: 0 0 10px; }
.r25-mini p { font-size: 14px; color: var(--dim); margin: 0; line-height: 1.7; }
.r25-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-top: 54px; }
.r25-stat-n { font-size: clamp(2rem, 4vw, 3.2rem); font-weight: 900; color: var(--acc2); line-height: 1; }
.r25-stat-l { margin-top: 8px; font-size: 13px; color: var(--dim); line-height: 1.5; }
@media (max-width: 860px) { .r25-minis, .r25-stats { grid-template-columns: 1fr; } }

/* ── movement panels ─────────────────────────────────────── */
.r25-panel { padding: 96px 0; }
.r25-panel.alt { background: var(--navy); }
.r25-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
.r25-2col img { width: 100%; border-radius: 8px; box-shadow: 0 24px 70px rgba(0,0,0,.5); }
.r25-panel p { color: rgba(236,236,242,.82); }
@media (max-width: 860px) { .r25-2col { grid-template-columns: 1fr; gap: 34px; } }

/* ── ways to give ────────────────────────────────────────── */
.r25-ways { display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px; margin-top: 46px; }
.r25-way { background: rgba(236,236,242,.045); border: 1px solid rgba(236,236,242,.09); border-radius: 10px; padding: 26px 22px; text-decoration: none; transition: border-color .2s, transform .2s; }
.r25-way:hover { border-color: rgba(86,96,254,.5); transform: translateY(-3px); }
.r25-way h4 { font-size: 15.5px; font-weight: 800; color: var(--ink); margin: 0 0 8px; }
.r25-way p { font-size: 13px; color: var(--dim); margin: 0; line-height: 1.6; }
@media (max-width: 860px) { .r25-ways { grid-template-columns: 1fr 1fr; } }

/* ── financials ──────────────────────────────────────────── */
.r25-fin { background: var(--paper); color: #14142b; padding: 110px 0; }
.r25-fin .r25-label { color: var(--acc); }
.r25-fin h2 { color: #14142b; }
.r25-fin-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 44px; margin-top: 54px; }
.r25-fin-col h3 { font-size: 1.15rem; font-weight: 900; color: #14142b; margin: 0 0 20px; }
.r25-fin-row { padding: 11px 0; border-bottom: 1px solid rgba(20,20,43,.12); }
.r25-fin-row .top { display: flex; justify-content: space-between; gap: 12px; font-size: 14px; }
.r25-fin-row .top b { font-weight: 700; color: #14142b; }
.r25-fin-row .top span { color: rgba(20,20,43,.7); white-space: nowrap; }
.r25-fin-row .pct { font-size: 11.5px; font-weight: 800; color: var(--acc); margin-top: 5px; }
.r25-bar { height: 4px; background: rgba(20,20,43,.1); border-radius: 2px; margin-top: 6px; overflow: hidden; }
.r25-bar i { display: block; height: 100%; width: 0; background: var(--acc); border-radius: 2px; transition: width 1.1s cubic-bezier(.22,1,.36,1); }
.r25-fin-row.tot { border-bottom: 0; padding-top: 16px; }
.r25-fin-row.tot .top b, .r25-fin-row.tot .top span { font-weight: 900; font-size: 15px; color: #14142b; }
@media (max-width: 900px) { .r25-fin-grid { grid-template-columns: 1fr; } }

/* ── donors / staff scrollable cards ─────────────────────── */
.r25-lists { padding: 110px 0; }
.r25-hint { font-size: 12px; letter-spacing: .18em; text-transform: uppercase; color: var(--dim); margin-bottom: 34px; }
.r25-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px; }
.r25-cards.three { grid-template-columns: repeat(3, 1fr); }
.r25-card { background: rgba(236,236,242,.04); border: 1px solid rgba(236,236,242,.1); border-radius: 10px; padding: 26px 24px; height: 460px; overflow-y: auto; }
.r25-card::-webkit-scrollbar { width: 6px; } .r25-card::-webkit-scrollbar-thumb { background: rgba(236,236,242,.2); border-radius: 3px; }
.r25-card h4 { font-size: 15px; font-weight: 900; color: var(--acc2); margin: 0 0 6px; }
.r25-card h4 + h4 { margin-top: 26px; }
.r25-card .who { font-size: 14px; color: rgba(236,236,242,.85); padding: 6px 0; border-bottom: 1px solid rgba(236,236,242,.07); }
.r25-card .who small { display: block; font-size: 12px; color: var(--dim); }
@media (max-width: 1000px) { .r25-cards, .r25-cards.three { grid-template-columns: 1fr 1fr; } }
@media (max-width: 640px) { .r25-cards, .r25-cards.three { grid-template-columns: 1fr; } .r25-card { height: 380px; } }

/* ── from our donors ─────────────────────────────────────── */
.r25-donorstory { background: var(--navy); padding: 100px 0; }
.r25-donorstory p { color: rgba(236,236,242,.85); max-width: 720px; margin: 0 0 18px; font-size: 16.5px; }
.r25-donorstory .pull { font-size: clamp(1.3rem, 2.4vw, 1.9rem); font-weight: 900; color: var(--ink); line-height: 1.4; margin-bottom: 26px; max-width: 26ch; }

/* ── thank you ───────────────────────────────────────────── */
.r25-thanks { padding: 130px 0 90px; text-align: center; background: radial-gradient(ellipse at 50% 30%, #1c1c46, var(--deep)); }
.r25-thanks h2 { font-size: clamp(3rem, 8vw, 5.5rem); font-weight: 900; color: #fff; margin: 0 0 24px; }
.r25-thanks p { color: var(--dim); max-width: 60ch; margin: 0 auto 34px; }
.r25-credits { margin-top: 80px; font-size: 12px; color: rgba(236,236,242,.4); max-width: 900px; margin-left: auto; margin-right: auto; line-height: 1.8; text-align: left; }
</style>
@endsection

@section('body')
<div class="r25">

    {{-- HERO --}}
    <section class="r25-hero" id="hero">
        <div class="r25-hero-bg"></div>
        <div class="r25-hero-shade"></div>
        <div class="r25-hero-body">
            <span class="r25-label rv in">National Political Prisoner Coalition &middot; 2025 Annual Report</span>
            <h1>
                <strong class="rv">While There Is a Soul in Prison,</strong>
                <strong class="rv d3">We Are Not Free.</strong>
            </h1>
            <a href="#statement" class="r25-cue rv d4">
                <span class="h6">Scroll to Begin</span>
                <span class="circ"></span>
            </a>
        </div>
    </section>

    {{-- LETTER --}}
    <section class="r25-letter" id="statement">
        <div class="r25-narrow">
            <span class="r25-label rv">A letter from the Coordinating Committee</span>
            <p class="rv">This has been an extraordinary year for the record. It began with the longest-running
            entry in the census finally closing: after forty-nine years, Leonard Peltier came home. It continued
            with people taken off sidewalks for op-eds, a blanket pardon that reopened every argument about what
            the words &ldquo;political prisoner&rdquo; mean, and a movement of readers, archivists, and defense
            committees who refused to let a single name disappear.</p>
            <p class="rv">By December the census held 6,987 documented cases &mdash; 457 added this year, more
            than any year in our history &mdash; and 88 entries were marked closed by release, pardon, or
            commutation. Each closure is a person. Several of their stories follow.</p>
            <blockquote class="rv">&ldquo;While there is a lower class, I am in it; while there is a criminal
            element, I am of it; and while there is a soul in prison, I am not free.&rdquo;
            <span style="display:block; font-size:13px; font-weight:400; color:var(--dim); margin-top:10px;">
            &mdash; Eugene V. Debs, Canton, Ohio, 1918. Convicted under the Espionage Act for the speech that
            ends this way; census entry no. 0041.</span></blockquote>
            <p class="rv">None of this work belongs to us. It belongs to the people in the record, and to you.</p>
            <p class="r25-sign rv">The Coordinating Committee <span>National Political Prisoner Coalition</span></p>
        </div>
    </section>

    {{-- RESTORING FREEDOM INTRO --}}
    <section class="r25-sect" id="restoring-freedom">
        <div class="r25-narrow">
            <span class="r25-label rv">Restoring Freedom</span>
            <h2 class="r25-title rv">Eighty-eight entries, marked closed</h2>
            <p class="r25-lede rv">Behind every entry in the census is a person, a family, and a fight. In 2025
            the coalition documented 88 releases &mdash; commutations, pardons, court-ordered releases, and
            sentences completed. Our readers verified each one against the record before it was marked closed,
            because freedom is part of the record too. Scroll on for three of those stories.</p>
        </div>
    </section>

    @php
    $stories = [
        [
            'id' => 'leonard-peltier', 'name' => 'Leonard Peltier',
            'meta1' => ['Released', 'February 2025'], 'meta2' => ['Time served', '49 years'],
            'bg' => '/storage/prisoners/leonard-peltier.jpg',
            'images' => ['/storage/prisoners/leonard-peltier.jpg', '/storage/history/wounded-knee.jpg',
                         '/storage/history/standing-rock.jpg', '/storage/history/dakota-war-trials.jpg'],
            'steps' => [
                ['c1', 'p2', '&ldquo;Today I am finally free! They may have imprisoned me, but they never took
                    my spirit.&rdquo; &mdash; his statement as he walked out of USP Coleman on February 18, 2025.'],
                ['c2', 'p3', '<a href="/prisoner/leonard-peltier">Leonard Peltier</a>, an organizer with the
                    American Indian Movement, was convicted in 1977 for the deaths of two FBI agents in the 1975
                    firefight on the Pine Ridge Reservation &mdash; a conviction built on coerced affidavits,
                    recanted testimony, and ballistics evidence withheld from the defense.'],
                ['c3', 'p3', 'For decades his case was the census&rsquo;s longest-running open entry. Amnesty
                    International, tribal nations, members of Congress, and the U.S. Attorney whose office
                    prosecuted him all called for clemency. On January 19, 2025, his sentence was commuted to
                    home confinement.'],
                ['c4', 'p3', 'He returned to the Turtle Mountain Band of Chippewa reservation in North Dakota,
                    where he is painting, receiving visitors, and meeting a generation of organizers raised on
                    his letters. Entry no. 2214, opened 1977, was marked closed after 49 years &mdash; the
                    longest span in the record.'],
            ],
            'band' => ['Forty-nine years, and the record held every one of them.',
                       'Entry no. 2214 is the longest-running entry in the census ever marked closed.'],
        ],
        [
            'id' => 'rumeysa-ozturk', 'name' => 'Rumeysa &Ouml;zt&uuml;rk',
            'meta1' => ['Released', 'May 2025'], 'meta2' => ['Time held', '45 days'],
            'bg' => '/images/campus-cases/rumeysa-ozturk.webp',
            'images' => ['/images/campus-cases/rumeysa-ozturk.webp',
                         '/storage/petitions/end-espionage-act-prosecutions-of-journalists.jpg',
                         '/storage/petitions/drop-charges-gaza-encampment-defendants.jpg',
                         '/storage/prisoners/rumeysa-ozturk.png'],
            'steps' => [
                ['c1', 'p2', 'She co-wrote an op-ed in a student newspaper. Six masked plainclothes agents took
                    her off a sidewalk in Somerville, Massachusetts for it.'],
                ['c2', 'p3', '<a href="/prisoner/rumeysa-ozturk">Rumeysa &Ouml;zt&uuml;rk</a>, a Turkish PhD
                    student at Tufts University, had her visa secretly revoked in March 2025 amid a campaign
                    targeting thousands of international students. On March 25 she was seized on video, moved
                    across three states overnight, and flown to an ICE facility in rural Louisiana.'],
                ['c3', 'p3', 'She spent 45 days in a crowded cell, suffering repeated asthma attacks, while her
                    lawyers fought to find out which government office had ordered her arrest &mdash; and why. The
                    video of her abduction became one of the year&rsquo;s defining images of speech policing.'],
                ['c4', 'p3', 'On May 9, 2025, a federal judge ordered her released on the spot, finding her
                    detention raised grave First Amendment and due-process concerns. She returned to Tufts to
                    finish her doctorate. Her entry cross-references a dozen other students in the census seized
                    over what they wrote.'],
            ],
            'band' => ['&ldquo;Writing an op-ed&rdquo; now appears 13 times in the census as an alleged ground for detention.',
                       'The 2025 student cohort, documented in full at /database.'],
        ],
        [
            'id' => 'mohsen-mahdawi', 'name' => 'Mohsen Mahdawi',
            'meta1' => ['Released', 'April 2025'], 'meta2' => ['Time held', '16 days'],
            'bg' => '/images/campus-cases/mohsen-mahdawi.webp',
            'images' => ['/images/campus-cases/mohsen-mahdawi.webp',
                         '/storage/petitions/drop-charges-gaza-encampment-defendants.jpg',
                         '/storage/petitions/end-espionage-act-prosecutions-of-journalists.jpg',
                         '/storage/prisoners/mohsen-mahdawi.jpg'],
            'steps' => [
                ['c1', 'p2', '&ldquo;I am saying it clear and loud: I am not afraid of you.&rdquo; &mdash; on the
                    courthouse steps in Vermont, the day a judge ordered him freed.'],
                ['c2', 'p3', '<a href="/prisoner/mohsen-mahdawi">Mohsen Mahdawi</a>, a Palestinian graduate
                    student at Columbia University and a green-card holder of ten years, was arrested on April 14,
                    2025 &mdash; at the federal office where he had been summoned for his citizenship interview.'],
                ['c3', 'p3', 'The government sought to deport him not for any crime, but under a Cold War-era
                    provision letting the Secretary of State declare a person&rsquo;s presence a foreign-policy
                    problem. His organizing on campus was the entire case against him.'],
                ['c4', 'p3', 'Sixteen days later a federal judge ordered his release, likening the moment to the
                    Red Scare. In May he walked at Columbia&rsquo;s graduation to a standing ovation. His case
                    &mdash; and the doctrine used against him &mdash; remains open in the courts and in the census.'],
            ],
            'band' => ['Detained at his own citizenship interview.',
                       'Entry no. 6743 &mdash; one of 40+ student cases added to the census in 2025.'],
        ],
    ];
    @endphp

    @foreach ($stories as $s)
        {{-- STORY INTRO (pinned full-viewport) --}}
        <section class="r25-fintro" id="{{ $s['id'] }}-intro">
            <div class="r25-fintro-bg" data-parallax style="background-image: url('{{ $s['bg'] }}')"></div>
            <div class="r25-hero-shade"></div>
            <div class="r25-fintro-body">
                <h2 class="rv">{!! $s['name'] !!}</h2>
                <div class="r25-fintro-meta rv d2">
                    <span><em>{{ $s['meta1'][0] }}</em>{{ $s['meta1'][1] }}</span>
                    <span><em>{{ $s['meta2'][0] }}</em>{{ $s['meta2'][1] }}</span>
                </div>
                <span class="r25-cue rv d3"><span class="h6">Scroll to Read the Story</span><span class="circ"></span></span>
            </div>
        </section>

        {{-- STORY SCROLL-GALLERY --}}
        <section class="r25-sg" data-gallery>
            <div class="r25-sg-grid">
                <div class="r25-sg-fig">
                    @foreach ($s['images'] as $i => $img)
                        <img src="{{ $img }}" alt="" loading="lazy" data-image="{{ $i + 1 }}" class="{{ $i === 0 ? 'on' : '' }}">
                    @endforeach
                </div>
                <div class="r25-sg-steps">
                    @foreach ($s['steps'] as $i => [$color, $size, $text])
                        <div class="r25-step" data-step="{{ $i + 1 }}">
                            <div class="r25-step-card r25-{{ $color }}">
                                <p class="{{ $size }}">{!! $text !!}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- BAND (the reference's per-story media slot) --}}
        <section class="r25-band">
            <div class="r25-wrap">
                <p class="rv">{!! $s['band'][0] !!}</p>
                <span class="rv d2">{!! $s['band'][1] !!}</span>
            </div>
        </section>
    @endforeach

    {{-- STILL INSIDE --}}
    <section class="r25-inside" id="fighting">
        <div class="r25-narrow">
            <span class="r25-label rv">The Fight Continues</span>
            <h2 class="r25-title rv">Fighting for the elders still inside</h2>
            <div class="r25-inside-names rv">
                <a href="/prisoner/mumia-abu-jamal">Mumia Abu-Jamal</a>
                <a href="/prisoner/imam-jamil-al-amin">Imam Jamil Al-Amin</a>
                <a href="/prisoner/yorie-von-kahl">Yorie von Kahl</a>
            </div>
            <p class="rv">While the record closed its longest entry, hundreds of its oldest remain open. This year
            we worked with defense committees on medical-release and compassionate-release petitions for aging
            prisoners &mdash; men and women in their seventies and eighties, many decades past parole eligibility,
            several in medical crisis. Mumia Abu-Jamal entered his 44th year inside. Imam Jamil Al-Amin&rsquo;s
            family renewed their fight for adequate cancer care. Yorie von Kahl was denied parole again, four
            decades after Medina.</p>
            <p class="rv">The Peltier commutation proved something the census has always insisted: no entry is
            permanent. Age is not a sentence, and neither is being forgotten.</p>
        </div>
    </section>

    {{-- CTA 1 --}}
    <section class="r25-cta is-acc" id="contributions">
        <div class="r25-wrap">
            <h3 class="rv">Every case in the census costs about $11 to document. Help us keep every name counted.</h3>
            <div class="rv d2">
                <a class="r25-btn" href="/donate">Donate</a>
                <a class="r25-btn ghost" href="/donate">Give Monthly</a>
            </div>
        </div>
    </section>

    {{-- TRANSFORMING SYSTEMS INTRO --}}
    <section class="r25-sect" id="transforming-systems">
        <div class="r25-narrow">
            <span class="r25-label rv">Transforming Systems</span>
            <h2 class="r25-title rv">The year the law kept redefining &ldquo;political prisoner&rdquo;</h2>
            <p class="r25-lede rv">Four times in 2025, the machinery itself moved &mdash; clemency at midnight,
            pardons at noon, new designations, and courts drawing lines around speech. The census documented every
            turn, because the definition of a political prisoner should belong to the record, not to whoever holds
            the pen that year.</p>
        </div>
    </section>

    @php
    $policies = [
        ['date' => 'January 19, 2025', 'title' => 'The Last Clemencies',
         'bg' => '/storage/history/wounded-knee.jpg',
         'text' => 'In his final hours in office, the outgoing president commuted Leonard Peltier&rsquo;s sentence
            to home confinement &mdash; the oldest open entry in the census, closed by a signature. The same pen
            had already moved dozens of death-row sentences to life the month before. Executive mercy remains the
            only door out for many of the record&rsquo;s longest entries; this year showed both how wide it can
            open and how rarely it does.'],
        ['date' => 'January 20, 2025', 'title' => 'Pardons as Politics',
         'bg' => '/storage/history/bonus-army.jpg',
         'text' => 'One day later, a blanket pardon covered roughly 1,500 January 6 defendants &mdash; the largest
            single act of clemency for politically motivated offenses in American history, and the most contested.
            The census&rsquo;s answer to the argument that followed is the same answer it gives every year: document
            everyone, describe honestly, and let the record hold what partisans cannot. A pardon does not erase an
            entry. It closes one.'],
        ['date' => 'September 2025', 'title' => 'Designation as Punishment',
         'bg' => '/storage/history/smith-act-trials.jpg',
         'text' => 'A presidential memorandum directed the machinery of domestic-terrorism enforcement at
            &ldquo;antifa&rdquo; and, in practice, at the loose networks of protest support around it &mdash; bail
            funds, legal collectives, medics. The census has seen this instrument before: the Smith Act built a
            generation of entries out of membership alone. We opened a new tracking category and began documenting
            the first designation-adjacent prosecutions.'],
        ['date' => 'Spring 2025', 'title' => 'Speech as Deportable Conduct',
         'bg' => '/storage/petitions/end-espionage-act-prosecutions-of-journalists.jpg',
         'text' => 'A Cold War-era foreign-policy provision became the year&rsquo;s workhorse: op-eds, campus
            organizing, and vigil attendance offered as grounds for arrest and deportation. By autumn a federal
            court had ruled the campaign unconstitutional as applied to its student targets. The census added more
            than 40 student entries in 2025 &mdash; and cross-referenced every one to the doctrine used against
            them, so the pattern can never be called anecdote.'],
    ];
    @endphp

    @foreach ($policies as $p)
        <section class="r25-policy" data-ppanel>
            <div class="r25-policy-bg" data-parallax style="background-image: url('{{ $p['bg'] }}')"></div>
            <div class="r25-wrap">
                <div class="r25-policy-card rv">
                    <div class="r25-policy-date">{{ $p['date'] }}</div>
                    <h3>{{ $p['title'] }}</h3>
                    <p>{!! $p['text'] !!}</p>
                </div>
            </div>
        </section>
    @endforeach

    {{-- RECORDS DEFENSE --}}
    <section class="r25-sect" id="records">
        <div class="r25-narrow">
            <span class="r25-label rv">Defending the Paper Trail</span>
            <h2 class="r25-title rv">When the record fights back</h2>
            <p class="r25-lede rv">Wrongful history begins with missing paper. In 2025, dockets sealed faster,
            agency pages went dark more often, and local coverage kept vanishing behind dead links. The archive
            team&rsquo;s answer was volume and redundancy.</p>
            <div class="r25-stats">
                <div class="rv"><div class="r25-stat-n">63</div><div class="r25-stat-l">public-records requests filed across 19 states and 4 federal agencies</div></div>
                <div class="rv d2"><div class="r25-stat-n">41,000</div><div class="r25-stat-l">documents mirrored into the archive before their sources went offline</div></div>
                <div class="rv d3"><div class="r25-stat-n">9</div><div class="r25-stat-l">defense committees trained in records preservation and FOIA practice</div></div>
            </div>
            <div class="r25-minis">
                <div class="r25-mini rv"><h4>The vanishing docket</h4>
                    <p>When a student deportation case was moved between three district courts in a week, the
                    filings scattered. Volunteers reassembled the full docket from PACER fragments and reporters&rsquo;
                    copies within four days &mdash; the version now cited by two law-review articles.</p></div>
                <div class="r25-mini rv d2"><h4>Bodycam as history</h4>
                    <p>Footage of a 2020 protest arrest, due for routine deletion under a five-year retention
                    policy, was preserved after our request &mdash; and became the deciding exhibit in a 2025
                    expungement petition. Retention schedules are now a standing item in our request calendar.</p></div>
            </div>
        </div>
    </section>

    {{-- CTA 2 --}}
    <section class="r25-cta is-black">
        <div class="r25-wrap">
            <h3 class="rv">6,987 cases and counting. The census is free to read, forever.</h3>
            <div class="rv d2">
                <a class="r25-btn" href="/database">Open the Database</a>
                <a class="r25-btn ghost" href="/prisoner-outreach">Write to Someone Inside</a>
            </div>
        </div>
    </section>

    {{-- ADVANCING THE MOVEMENT --}}
    <section class="r25-sect" id="advancing">
        <div class="r25-narrow">
            <span class="r25-label rv">Advancing the Movement</span>
            <h2 class="r25-title rv">The people who keep the record</h2>
            <p class="r25-lede rv">Time and again this year, the coalition&rsquo;s community was the engine:
            readers verifying entries, families correcting them, letter-writers keeping the oldest cases warm,
            and donors keeping the lights on. In 2025 that community got a room of its own.</p>
        </div>
    </section>

    <section class="r25-panel alt" id="highlights">
        <div class="r25-wrap r25-2col">
            <div class="rv"><img src="/storage/history/attica.jpg" alt="Attica prison yard, September 1971" loading="lazy"></div>
            <div>
                <h2 class="r25-title rv" style="font-size: clamp(1.6rem, 3vw, 2.4rem);">The Gathering of the Record</h2>
                <p class="rv">In October we held the coalition&rsquo;s first national convening: two days, 400
                attendees, 22 defense committees, and a working archive room where families brought documents we
                had been missing for years. Panels paired census readers with historians; the closing session
                introduced every formerly imprisoned person in the room, to a standing ovation that would not end.</p>
                <p class="rv d2">The convening voted to make Days of Remembrance a coalition-wide calendar, and
                seated the first formerly incarcerated chair of the readers&rsquo; panel.</p>
            </div>
        </div>
    </section>

    <section class="r25-panel" id="digital">
        <div class="r25-wrap r25-2col">
            <div>
                <h2 class="r25-title rv" style="font-size: clamp(1.6rem, 3vw, 2.4rem);">Expanding the record&rsquo;s reach</h2>
                <p class="rv">The Dispatch, our weekly newsletter, grew to 58,000 subscribers. The memorial page
                drew 3.1 million views on Days of Remembrance. Census data was cited in three court filings, two
                law-review articles, and a dozen syllabi &mdash; and the archive&rsquo;s public reading room served
                its hundred-thousandth document in November.</p>
                <p class="rv d2">Every number in this paragraph has a source line in the archive. That is the point
                of us.</p>
            </div>
            <div class="rv d2"><img src="/storage/history/whistleblowers.jpg" alt="Archival photograph from the census archive" loading="lazy"></div>
        </div>
    </section>

    <section class="r25-panel alt" id="celebration">
        <div class="r25-wrap r25-2col">
            <div class="rv"><img src="/storage/history/standing-rock.jpg" alt="Movement gathering" loading="lazy"></div>
            <div>
                <h2 class="r25-title rv" style="font-size: clamp(1.6rem, 3vw, 2.4rem);">2025 Days of Remembrance</h2>
                <p class="rv">On December 4 &mdash; the anniversary of the Fred Hampton raid &mdash; 300 guests
                joined the annual Days of Remembrance evening: readings from the record, letters written to 61
                people still inside, and the year&rsquo;s releases welcomed home in person. The evening raised
                $410,000 for the legal support and commissary funds.</p>
            </div>
        </div>
    </section>

    {{-- WAYS TO GIVE --}}
    <section class="r25-sect" id="ways-to-give">
        <div class="r25-wrap">
            <span class="r25-label rv">Ways to Give</span>
            <h2 class="r25-title rv">Together, we keep every name counted</h2>
            <div class="r25-ways">
                <a class="r25-way rv" href="/donate"><h4>Donate</h4><p>One-time or monthly. $11 documents a case, forever.</p></a>
                <a class="r25-way rv d2" href="/prisoner-outreach"><h4>Write to a Prisoner</h4><p>Letters are the oldest program we have &mdash; and the most requested.</p></a>
                <a class="r25-way rv d3" href="/donate"><h4>Commissary &amp; Family Fund</h4><p>Direct support for people inside and the families holding them up.</p></a>
                <a class="r25-way rv d4" href="/volunteer"><h4>Volunteer</h4><p>Readers, archivists, translators, and letter-night hosts.</p></a>
            </div>
        </div>
    </section>

    {{-- FINANCIALS --}}
    @php
    $revenue = [
        ['Individuals', 2304180], ['Foundations', 1102500], ['Events', 171320],
        ['Store & publications', 118404], ['Investments', 214660], ['Other income', 79510],
    ];
    $revTotal = array_sum(array_column($revenue, 1));
    $expend = [ ['Program', 2916404], ['Management & General', 498212], ['Fundraising', 472395] ];
    $expTotal = array_sum(array_column($expend, 1));
    $programs = [
        ['Census & Research', 942175], ['Archive & Digital', 655310], ['Family & Commissary', 588404],
        ['Legal Support Fund', 471209], ['Development', 472395], ['Communications', 443118], ['Operations', 314400],
    ];
    $progTotal = array_sum(array_column($programs, 1));
    @endphp
    <section class="r25-fin" id="activities">
        <div class="r25-wrap">
            <span class="r25-label rv">Financials</span>
            <h2 class="r25-title rv">Statement of Activities &mdash; FY25</h2>
            <div class="r25-fin-grid">
                <div class="r25-fin-col rv">
                    <h3>Revenue</h3>
                    @foreach ($revenue as [$label, $amt])
                        <div class="r25-fin-row">
                            <div class="top"><b>{{ $label }}</b><span>${{ number_format($amt) }}</span></div>
                            <div class="pct">{{ round($amt * 100 / $revTotal) }}%</div>
                            <div class="r25-bar"><i data-pct="{{ round($amt * 100 / $revTotal) }}"></i></div>
                        </div>
                    @endforeach
                    <div class="r25-fin-row tot"><div class="top"><b>Total Revenue</b><span>${{ number_format($revTotal) }}</span></div></div>
                </div>
                <div class="r25-fin-col rv d2">
                    <h3>Expenditures</h3>
                    @foreach ($expend as [$label, $amt])
                        <div class="r25-fin-row">
                            <div class="top"><b>{{ $label }}</b><span>${{ number_format($amt) }}</span></div>
                            <div class="pct">{{ round($amt * 100 / $expTotal) }}%</div>
                            <div class="r25-bar"><i data-pct="{{ round($amt * 100 / $expTotal) }}"></i></div>
                        </div>
                    @endforeach
                    <div class="r25-fin-row tot"><div class="top"><b>Total Expenditures</b><span>${{ number_format($expTotal) }}</span></div></div>
                </div>
                <div class="r25-fin-col rv d3">
                    <h3>Expenses by Program</h3>
                    @foreach ($programs as [$label, $amt])
                        <div class="r25-fin-row">
                            <div class="top"><b>{{ $label }}</b><span>${{ number_format($amt) }}</span></div>
                            <div class="pct">{{ round($amt * 100 / $progTotal) }}%</div>
                            <div class="r25-bar"><i data-pct="{{ round($amt * 100 / $progTotal) }}"></i></div>
                        </div>
                    @endforeach
                    <div class="r25-fin-row tot"><div class="top"><b>Total</b><span>${{ number_format($progTotal) }}</span></div></div>
                </div>
            </div>
        </div>
    </section>

    {{-- DONORS --}}
    @php
    $donorTiers = json_decode(file_get_contents(database_path('data/report-2025-donors.json')), true) ?: [];
    $donorCards = [array_slice($donorTiers, 0, 2), array_slice($donorTiers, 2, 2),
                   array_slice($donorTiers, 4, 2), array_slice($donorTiers, 6, 2)];
    @endphp
    <section class="r25-lists" id="donors">
        <div class="r25-wrap">
            <span class="r25-label rv">Fiscal Year 2025 Donors</span>
            <h2 class="r25-title rv">Every name here kept the record open</h2>
            <div class="r25-hint rv">Scroll within each card to see all names</div>
            <div class="r25-cards">
                @foreach ($donorCards as $card)
                    <div class="r25-card rv">
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

    {{-- FROM OUR DONORS --}}
    <section class="r25-donorstory" id="from-donors">
        <div class="r25-narrow">
            <span class="r25-label rv">From Our Donors</span>
            <div class="pull rv">&ldquo;Eleven dollars a month. That&rsquo;s a name, every month, that doesn&rsquo;t disappear.&rdquo;</div>
            <p class="rv">A retired schoolteacher in Ohio has given $11 a month since the year the census opened
            &mdash; the cost, as she likes to remind us, of documenting exactly one case. She found the coalition
            after searching for a great-uncle deported in the Palmer Raids, and found him in the record within an
            hour.</p>
            <p class="rv d2">This year, while making her estate plans, she added the coalition to her will &mdash;
            a legacy gift dedicated to the archive&rsquo;s reading room, &ldquo;so the record outlives everyone who
            wanted it gone, and me too.&rdquo; Legacy gifts like hers fund the census&rsquo;s permanence: servers,
            preservation, and the readers who check every entry twice.</p>
        </div>
    </section>

    {{-- STAFF --}}
    @php
    try { $staffMembers = \App\Models\Staff::orderBy('name')->get(); } catch (\Throwable $e) { $staffMembers = collect(); }
    $staffGroup = $staffMembers->where('group', '!=', 'board');
    $boardGroup = $staffMembers->where('group', 'board');
    $readers = ['Ashley Santos', 'Bruce Freeman', 'Catherine Morris', 'Donna Okafor', 'Jason Wood',
                'Jeffrey Kaplan', 'Marcus Bailey', 'Margaret Torres', 'Nicole Reed', 'Rachel Campbell',
                'Sandra Woods', 'Vincent Collins', 'Walter Simmons'];
    $archivists = ['Betty Evans', 'Betty Ortiz', 'Daniel Vasquez', 'Emily Bailey', 'Emma Sullivan',
                   'Eric Herrera', 'Ezra Jordan', 'Hiram Richards', 'Jason Thompson', 'Jessica Harris',
                   'Judith Watson', 'Keith Morales', 'Kenneth Ramos', 'Kyle Herrera', 'Laura Brown',
                   'Lawrence Daniels', 'Lori Kaur', 'Marie Thompson', 'Nicholas Ramirez', 'Priya Hunter',
                   'Thomas Knight'];
    @endphp
    <section class="r25-lists" id="staff" style="padding-top: 0;">
        <div class="r25-wrap">
            <span class="r25-label rv">Our People</span>
            <h2 class="r25-title rv">The coalition, by name</h2>
            <div class="r25-hint rv">Scroll within each card to see all names</div>
            <div class="r25-cards three">
                <div class="r25-card rv">
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
                <div class="r25-card rv d2">
                    <h4>Readers&rsquo; Panel 2025</h4>
                    <div class="who" style="border-bottom: 0; color: var(--dim); font-size: 12.5px; padding-bottom: 12px;">
                        Every contested entry is reviewed by an outside panel of historians and movement veterans
                        before publication.</div>
                    @foreach ($readers as $n)
                        <div class="who">{{ $n }}</div>
                    @endforeach
                </div>
                <div class="r25-card rv d3">
                    <h4>Volunteer Archivists</h4>
                    <div class="who" style="border-bottom: 0; color: var(--dim); font-size: 12.5px; padding-bottom: 12px;">
                        The people who scanned, indexed, and mirrored 41,000 documents this year.</div>
                    @foreach ($archivists as $n)
                        <div class="who">{{ $n }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- THANK YOU --}}
    <section class="r25-thanks" id="thank-you">
        <div class="r25-wrap">
            <h2 class="rv">Thank You</h2>
            <p class="rv d2">Our work to document political imprisonment, support the people living it, and close
            the record&rsquo;s oldest entries is only possible because you decided it should exist. 6,987 cases.
            457 added this year. 88 marked closed &mdash; one of them 49 years in the making.</p>
            <div class="rv d3">
                <a class="r25-btn" style="background: var(--acc); color: #fff;" href="/database">Explore the Database</a>
                <a class="r25-btn ghost" href="/annual-report">All Annual Reports</a>
            </div>
            <div class="r25-credits rv">
                Photo credits, in order of appearance: Standing Rock #NoDAPL demonstration (Fibonacci Blue, CC BY 2.0);
                Leonard Peltier, NPPC case file; Wounded Knee occupation, 1973 (public domain); Dakota War trials
                lithograph (public domain); R&uuml;meysa &Ouml;zt&uuml;rk and Mohsen Mahdawi, campus-case files;
                First Amendment inscription, Newseum fa&ccedil;ade (CC BY-SA); Columbia University encampment, April
                2024 (CC BY-SA); Bonus Army encampment, 1932 (public domain); Smith Act defendants, 1949 (public
                domain); Attica prison yard, September 1971 (public domain); Stop Cop City vigil, January 2023
                (Tatsoi, CC BY-SA 4.0). Full per-image licensing lives in the repository&rsquo;s CREDITS files.
            </div>
        </div>
    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // reveal framework
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
    }, { threshold: 0.18 });
    document.querySelectorAll('.rv').forEach(function (el) { io.observe(el); });

    // scrollytelling galleries: steps drive the sticky image stack
    document.querySelectorAll('[data-gallery]').forEach(function (gal) {
        var imgs = gal.querySelectorAll('.r25-sg-fig img');
        var steps = gal.querySelectorAll('.r25-step');
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

    // parallax backgrounds (story intros + policy panels)
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

    // financial bars sweep in
    var bio = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (!e.isIntersecting) return;
            bio.unobserve(e.target);
            e.target.querySelectorAll('.r25-bar i').forEach(function (bar) {
                var pct = bar.getAttribute('data-pct');
                if (reduced) { bar.style.transition = 'none'; }
                requestAnimationFrame(function () { bar.style.width = pct + '%'; });
            });
        });
    }, { threshold: 0.3 });
    document.querySelectorAll('.r25-fin-col').forEach(function (el) { bio.observe(el); });
});
</script>
@endsection
