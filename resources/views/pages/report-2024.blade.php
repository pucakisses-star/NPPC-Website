@extends('app')

@section('title', 'Report 2024 — Every Name Counted | NPPC')

@section('meta_description')The NPPC 2024 interactive annual report: the year of the census — 6,530 cases documented, 426 added, and the releases and losses the record now holds.@endsection

@section('og_image'){{ asset('storage/history/wounded-knee.jpg') }}@endsection

@section('head')
<style>
/* ============================================================
   Report 2024 — interactive annual-report microsite. Top scroll
   progress bar, expanding story cards, click-a-state explorer,
   accordions, quote slider, donut financials, scrollable donor
   cards. Original NPPC content in the site palette.
   ============================================================ */
body.page-report-2024 main.container,
body.page-report-2024 .container { width: 100% !important; max-width: none !important; padding-left: 0 !important; padding-right: 0 !important; }
body.page-report-2024 { background: #0a0a12; }

.r24 { --ink: #ececf2; --dim: rgba(236,236,242,.62); --acc: #5660fe; --acc2: #8f97ff;
       --paper: #f0f1f7; --deep: #0a0a12; --navy: #12122a;
       color: var(--ink); font-size: 16px; line-height: 1.7; overflow: hidden; }
.r24-wrap { max-width: 1180px; margin: 0 auto; padding: 0 28px; }

#r24-progress { position: fixed; top: 0; left: 0; height: 3px; width: 0; background: var(--acc); z-index: 99999; }

.rv { opacity: 0; transform: translateY(30px); transition: opacity .9s ease, transform .9s cubic-bezier(.22,1,.36,1); }
.rv.rv-fade { transform: none; }
.rv.in { opacity: 1; transform: none; }
@media (prefers-reduced-motion: reduce) { .rv { opacity: 1 !important; transform: none !important; transition: none; } }

.r24-label { display: inline-block; font-size: 12px; font-weight: 800; letter-spacing: .22em; text-transform: uppercase; color: var(--acc2); margin-bottom: 16px; }
.r24-h1 { font-size: clamp(2.6rem, 6vw, 4.6rem); font-weight: 900; line-height: 1.03; letter-spacing: -.02em; color: var(--ink); margin: 0 0 26px; }
.r24-lede { font-size: clamp(1.05rem, 1.7vw, 1.3rem); font-weight: 700; color: rgba(236,236,242,.85); max-width: 820px; line-height: 1.6; }
.r24-h3 { font-size: clamp(1.5rem, 2.6vw, 2.1rem); font-weight: 900; color: var(--ink); margin: 0 0 20px; }

.r24-btn { display: inline-flex; align-items: center; gap: 9px; padding: 12px 24px; border: 1px solid rgba(236,236,242,.35); border-radius: 4px;
  color: var(--ink); font-size: 13px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; text-decoration: none; transition: background .15s, border-color .15s; cursor: pointer; }
.r24-btn:hover { background: var(--acc); border-color: var(--acc); color: #fff; }
.r24-btn.solid { background: var(--acc); border-color: var(--acc); color: #fff; }
.r24-btn.solid:hover { background: #3b45e0; }

/* hero */
.r24-hero { min-height: 88vh; display: flex; align-items: center; position: relative;
  background: radial-gradient(circle at 72% 30%, #1d1d40 0%, var(--deep) 58%); }
.r24-hero h3 { font-size: clamp(1.05rem, 1.8vw, 1.4rem); font-weight: 700; color: var(--acc2); margin: 0 0 14px; }
.r24-hero h1 { font-size: clamp(3rem, 8.5vw, 6.6rem); font-weight: 900; line-height: .98; letter-spacing: -.025em; color: #fff; margin: 0 0 30px; max-width: 11ch; }
.r24-hero h1 em { font-style: normal; color: var(--acc2); }
.r24-hero p { max-width: 560px; color: var(--dim); }

/* video block */
.r24-video { padding: 60px 0 100px; }
.r24-video-grid { display: grid; grid-template-columns: 1fr 1.4fr; gap: 54px; align-items: center; }
.r24-video video { width: 100%; border-radius: 10px; box-shadow: 0 30px 80px rgba(0,0,0,.55); display: block; }
@media (max-width: 860px) { .r24-video-grid { grid-template-columns: 1fr; gap: 30px; } }

/* section intros */
.r24-section { padding: 110px 0 30px; }
.r24-section.tint { background: var(--navy); }

/* CTA band */
.r24-cta { background: linear-gradient(90deg, rgba(86,96,254,.16), rgba(86,96,254,.05)); border-top: 1px solid rgba(86,96,254,.35); border-bottom: 1px solid rgba(86,96,254,.35); margin-top: 90px; }
.r24-cta .r24-wrap { display: flex; align-items: center; justify-content: space-between; gap: 24px; padding-top: 34px; padding-bottom: 34px; flex-wrap: wrap; }
.r24-cta h3 { font-size: clamp(1.2rem, 2.4vw, 1.7rem); font-weight: 900; color: var(--ink); margin: 0; }

/* expanding story cards */
.r24-stories { display: flex; gap: 12px; margin: 54px 0 12px; height: 560px; }
.r24-story { position: relative; flex: 1; min-width: 0; border-radius: 10px; overflow: hidden; cursor: pointer;
  transition: flex-grow .65s cubic-bezier(.22,1,.36,1); background: #16162c; }
.r24-story.active { flex-grow: 3.6; cursor: default; }
.r24-story-bg { position: absolute; inset: 0; background: center 18% / cover no-repeat; filter: grayscale(45%) brightness(.62); transition: filter .5s; }
.r24-story.active .r24-story-bg { filter: grayscale(0%) brightness(.72); }
.r24-story-shade { position: absolute; inset: 0; background: linear-gradient(180deg, transparent 30%, rgba(8,8,16,.94)); }
.r24-story-info { position: absolute; left: 0; right: 0; bottom: 0; padding: 22px; }
.r24-story h4 { font-size: 1.25rem; font-weight: 900; color: #fff; margin: 0 0 6px; line-height: 1.15;
  writing-mode: vertical-rl; transform: rotate(180deg); position: absolute; bottom: 22px; left: 18px; white-space: nowrap; transition: opacity .3s; }
.r24-story.active h4 { writing-mode: horizontal-tb; transform: none; position: static; font-size: clamp(1.4rem, 2.4vw, 2rem); }
.r24-story-meta { font-size: 12px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--acc2); margin-bottom: 8px; opacity: 0; transition: opacity .4s .25s; }
.r24-story-more { font-size: 13.5px; color: rgba(236,236,242,.85); line-height: 1.6; max-width: 46ch; opacity: 0; transition: opacity .4s .3s; }
.r24-story.active .r24-story-meta, .r24-story.active .r24-story-more { opacity: 1; }
.r24-story-more a { color: var(--acc2); }
.r24-stories-hint { text-align: center; font-size: 11.5px; letter-spacing: .16em; text-transform: uppercase; color: var(--dim); margin-bottom: 8px; }
@media (max-width: 800px) {
    .r24-stories { flex-direction: column; height: auto; }
    .r24-story { min-height: 84px; }
    .r24-story.active { min-height: 380px; }
    .r24-story h4 { writing-mode: horizontal-tb; transform: none; position: static; }
    .r24-story-info { padding: 18px; }
}

/* state explorer */
.r24-states { display: grid; grid-template-columns: 300px 1fr; gap: 50px; margin-top: 50px; align-items: start; }
.r24-state-list { display: flex; flex-direction: column; }
.r24-state-btn { text-align: left; background: none; border: 0; border-bottom: 1px solid rgba(236,236,242,.12); padding: 13px 6px; font: inherit;
  font-size: 15.5px; font-weight: 800; color: var(--dim); cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: color .15s; }
.r24-state-btn:hover { color: var(--ink); }
.r24-state-btn.on { color: var(--acc2); }
.r24-state-btn .n { font-size: 12.5px; font-weight: 800; color: var(--dim); }
.r24-state-panel { background: rgba(236,236,242,.045); border: 1px solid rgba(236,236,242,.1); border-radius: 10px; padding: 36px; min-height: 300px; }
.r24-state-panel h4 { font-size: 1.6rem; font-weight: 900; color: var(--ink); margin: 0 0 6px; }
.r24-state-panel .count { font-size: 13px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--acc2); margin-bottom: 16px; }
.r24-state-panel p { color: rgba(236,236,242,.82); margin: 0; }
.r24-states-hint { font-size: 11.5px; letter-spacing: .16em; text-transform: uppercase; color: var(--dim); margin-top: 10px; }
@media (max-width: 860px) { .r24-states { grid-template-columns: 1fr; } }

/* accordion */
.r24-acc { margin-top: 50px; }
.r24-acc-item { border-bottom: 1px solid rgba(236,236,242,.12); }
.r24-acc-q { width: 100%; display: flex; justify-content: space-between; align-items: center; gap: 20px; background: none; border: 0;
  padding: 22px 4px; font: inherit; font-size: 17.5px; font-weight: 800; color: var(--ink); text-align: left; cursor: pointer; }
.r24-acc-q .chev { flex: 0 0 auto; transition: transform .3s; color: var(--acc2); font-size: 22px; }
.r24-acc-item.open .chev { transform: rotate(45deg); }
.r24-acc-a { max-height: 0; overflow: hidden; transition: max-height .5s cubic-bezier(.22,1,.36,1); }
.r24-acc-a p { margin: 0 0 22px; padding: 0 4px; color: rgba(236,236,242,.78); max-width: 860px; }

/* highlight cards */
.r24-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 26px; margin-top: 50px; }
.r24-card { background: rgba(236,236,242,.045); border: 1px solid rgba(236,236,242,.09); border-radius: 10px; overflow: hidden; }
.r24-card img { width: 100%; height: 190px; object-fit: cover; display: block; }
.r24-card-body { padding: 22px; }
.r24-card h4 { font-size: 1.15rem; font-weight: 900; color: var(--ink); margin: 0 0 10px; }
.r24-card p { font-size: 13.5px; color: var(--dim); margin: 0; line-height: 1.6; }
@media (max-width: 860px) { .r24-cards { grid-template-columns: 1fr; } }

/* quote slider */
.r24-slider { position: relative; margin-top: 50px; background: rgba(236,236,242,.045); border: 1px solid rgba(236,236,242,.09); border-left: 3px solid var(--acc); border-radius: 10px; padding: 44px 48px 56px; min-height: 190px; }
.r24-slide { display: none; }
.r24-slide.on { display: block; animation: r24SlideIn .5s ease; }
@keyframes r24SlideIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: none; } }
.r24-slide p { font-size: clamp(1.05rem, 1.8vw, 1.35rem); font-weight: 700; color: var(--ink); margin: 0 0 14px; line-height: 1.55; }
.r24-slide span { font-size: 13px; color: var(--dim); }
.r24-slider-dots { position: absolute; bottom: 20px; left: 48px; display: flex; gap: 8px; }
.r24-sdot { width: 8px; height: 8px; border-radius: 50%; background: rgba(236,236,242,.25); border: 0; padding: 0; cursor: pointer; }
.r24-sdot.on { background: var(--acc2); }
@media (prefers-reduced-motion: reduce) { .r24-slide.on { animation: none; } }

/* memoriam honorees */
.r24-honors { margin-top: 50px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; }
.r24-honor img { width: 100%; height: 300px; object-fit: cover; object-position: 50% 20%; border-radius: 8px; filter: grayscale(30%); }
.r24-honor h3 { font-size: 1.35rem; font-weight: 900; color: var(--ink); margin: 18px 0 6px; }
.r24-honor h5 { font-size: 13.5px; font-weight: 400; color: var(--dim); margin: 0; line-height: 1.65; }
@media (max-width: 860px) { .r24-honors { grid-template-columns: 1fr; } }

/* financials */
.r24-fin { background: var(--paper); color: #14142a; padding: 110px 0; margin-top: 100px; }
.r24-fin .r24-label { color: #3b45e0; }
.r24-fin h2 { font-size: clamp(1.9rem, 3.6vw, 2.8rem); font-weight: 900; margin: 0 0 50px; color: #14142a; }
.r24-fin-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 54px; }
.r24-fin h3 { font-size: 1.25rem; font-weight: 900; margin: 0 0 22px; color: #14142a; text-align: center; }
.r24-donut { display: block; margin: 0 auto 24px; transform: rotate(-90deg); }
.r24-donut circle { fill: none; stroke-width: 30; transition: stroke-dashoffset 1.3s cubic-bezier(.22,1,.36,1); }
.r24-fin table { width: 100%; border-collapse: collapse; font-size: 13px; }
.r24-fin td { padding: 7.5px 0; border-bottom: 1px solid rgba(20,20,42,.12); }
.r24-fin td.amt { text-align: right; font-weight: 800; white-space: nowrap; }
.r24-fin tr.tot td { border-top: 2px solid #14142a; border-bottom: 0; font-weight: 900; }
.r24-dot { display: inline-block; width: 9px; height: 9px; border-radius: 2px; margin-right: 8px; }
@media (max-width: 960px) { .r24-fin-grid { grid-template-columns: 1fr; } }

/* donor scroll cards */
.r24-donors { padding: 110px 0; }
.r24-donor-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px; margin-top: 46px; }
.r24-donor-card { background: rgba(236,236,242,.045); border: 1px solid rgba(236,236,242,.1); border-radius: 10px; padding: 24px 20px; height: 460px; overflow-y: auto; }
.r24-donor-card::-webkit-scrollbar { width: 8px; }
.r24-donor-card::-webkit-scrollbar-thumb { background: rgba(236,236,242,.18); border-radius: 8px; }
.r24-donor-card h5 { font-size: 14px; font-weight: 900; letter-spacing: .08em; text-transform: uppercase; color: var(--acc2); margin: 22px 0 12px; }
.r24-donor-card h5:first-child { margin-top: 0; }
.r24-donor-card div { font-size: 13px; color: rgba(236,236,242,.8); padding: 2.5px 0; }
.r24-donors-hint { font-size: 11.5px; letter-spacing: .16em; text-transform: uppercase; color: var(--dim); margin-top: 12px; }
@media (max-width: 960px) { .r24-donor-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 640px) { .r24-donor-grid { grid-template-columns: 1fr; } }

/* ways to give */
.r24-ways { display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px; margin-top: 46px; }
.r24-way { display: block; background: rgba(236,236,242,.045); border: 1px solid rgba(236,236,242,.1); border-radius: 10px; padding: 26px 22px; text-decoration: none; transition: border-color .15s, background .15s; }
.r24-way:hover { border-color: var(--acc); background: rgba(86,96,254,.08); }
.r24-way h4 { font-size: 1.05rem; font-weight: 900; color: var(--ink); margin: 0 0 8px; }
.r24-way p { font-size: 13px; color: var(--dim); margin: 0; line-height: 1.55; }
@media (max-width: 860px) { .r24-ways { grid-template-columns: 1fr 1fr; } }

/* team */
.r24-team-card { background: rgba(236,236,242,.045); border: 1px solid rgba(236,236,242,.1); border-radius: 10px; padding: 34px; margin-top: 46px; }
.r24-team-card h5 { font-size: 14px; font-weight: 900; letter-spacing: .08em; text-transform: uppercase; color: var(--acc2); margin: 26px 0 12px; }
.r24-team-card h5:first-child { margin-top: 0; }
.r24-team-card div { padding: 4px 0; color: rgba(236,236,242,.85); }
.r24-team-card b { color: var(--ink); }

/* thank you */
.r24-thanks { min-height: 78vh; display: flex; align-items: center; justify-content: center; text-align: center;
  background: radial-gradient(circle at 50% 40%, #202048 0%, var(--navy) 52%, var(--deep) 100%); }
.r24-thanks h1 { font-size: clamp(3rem, 9vw, 6.4rem); font-weight: 900; color: #fff; margin: 0 0 22px; letter-spacing: -.02em; }
.r24-thanks p { max-width: 560px; margin: 0 auto 38px; color: rgba(236,236,242,.75); }
.r24-thanks .row { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
</style>
@endsection

@section('body')
<div class="r24">
    <div id="r24-progress"></div>

    {{-- HERO --}}
    <section class="r24-hero">
        <div class="r24-wrap">
            <span class="r24-label rv in">National Political Prisoner Coalition &middot; 2024 Annual Report</span>
            <h3 class="rv in">The record only protects people if someone keeps it.</h3>
            <h1 class="rv in">Every Name <em>Counted</em></h1>
            <p class="rv in">2024 was the year of the census — new cohorts, new prosecutions, and the fight to be
            counted. This is what the ledger held when the year closed.</p>
        </div>
    </section>

    {{-- VIDEO --}}
    <section class="r24-video">
        <div class="r24-wrap r24-video-grid">
            <div>
                <span class="r24-label rv">Hear From the Coalition</span>
                <h2 class="r24-h3 rv">The year in sixty seconds</h2>
                <p class="rv" style="color: var(--dim)">Before the numbers, the argument: why a country that jails
                people for their politics needs a permanent, public count of everyone it has done this to.</p>
            </div>
            <video class="rv rv-fade" controls preload="metadata" playsinline poster="{{ asset('videos/nppc-launch-film-poster.jpg') }}">
                <source src="{{ asset('videos/nppc-launch-film.mp4') }}" type="video/mp4">
            </video>
        </div>
    </section>

    {{-- SECTION 1: THE RECORD, RESTORED --}}
    <section class="r24-section tint" id="restored">
        <div class="r24-wrap">
            <h1 class="r24-h1 rv">The Record, Restored</h1>
            <p class="r24-lede rv">Supported by defense committees, families, and donors, the census marked more
            entries <b>released</b> in 2024 than in any year since the coalition began — including the two
            releases the whole world watched.</p>
            <div class="r24-stories-hint rv" style="margin-top:46px">Click each profile to learn more</div>
            <div class="r24-stories rv rv-fade" id="r24-stories">
                @foreach ([
                    ['Veronza Bowers Jr.', 'veronza-bowers-jr', '/storage/prisoners/veronza-bowers-jr.jpg', '2024', '52 years',
                     'The last Black Panther in federal prison came home after more than half a century — eligible for parole since 2004, held two decades past his mandatory release date.'],
                    ['Julian Assange', 'julian-assange', '/storage/prisoners/julian-assange.jpg', 'June 2024', '5 years + 7 in asylum',
                     'The WikiLeaks editor pleaded to a single Espionage Act count on Saipan and flew home to Australia — free, with the question his case raised still standing.'],
                    ['Tarek Mehanna', 'tarek-mehanna', '/storage/prisoners/tarek-mehanna.jpg', '2024', '13 years',
                     'Sentenced to 17 years for translations the government called material support, Mehanna finished his sentence and came home to Massachusetts.'],
                    ['Eric King', 'eric-king', '/storage/prisoners/eric-king.jpg', '2024', '11 years',
                     'The anarchist poet spent a decade in federal custody — much of it in solitary — and walked out of ADX-adjacent segregation to a waiting support network.'],
                    ['Daniel Hale', 'daniel-hale', '/storage/prisoners/daniel-e-hale.jpg', 'July 2024', '45 months',
                     'The drone-program whistleblower finished his Espionage Act sentence: the only person imprisoned for the drone war was the one who told the truth about it.'],
                    ['Wayne Hsiung', 'wayne-hsiung', '/storage/prisoners/wayne-hsiung.jpg', '2024', 'Multiple sentences',
                     'The open-rescue activist and attorney completed his latest jail term for factory-farm investigations and returned to organizing the right-to-rescue defense.'],
                ] as $i => [$name, $slug, $img, $when, $served, $blurb])
                    <div class="r24-story {{ $i === 0 ? 'active' : '' }}" data-story>
                        <div class="r24-story-bg" style="background-image:url('{{ $img }}')"></div>
                        <div class="r24-story-shade"></div>
                        <h4>{{ $name }}</h4>
                        <div class="r24-story-info">
                            <div class="r24-story-meta">Released: {{ $when }} &middot; Time held: {{ $served }}</div>
                            <div class="r24-story-more">{{ $blurb }} <a href="/prisoner/{{ $slug }}">Read the case &rarr;</a></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="r24-cta">
            <div class="r24-wrap">
                <h3 class="rv">Help keep the record — every case costs about $11 to document.</h3>
                <a class="r24-btn solid rv" href="/donate">Donate</a>
            </div>
        </div>
    </section>

    {{-- SECTION 2: THE CENSUS --}}
    <section class="r24-section" id="census">
        <div class="r24-wrap">
            <h1 class="r24-h1 rv">The Census</h1>
            <p class="r24-lede rv">Four hundred twenty-six cases entered the record in 2024 — the largest single-year
            addition since the coalition was founded — bringing the ledger to <b>6,530 documented cases</b> across
            all fifty states.</p>

            <div class="r24-states rv rv-fade">
                <div>
                    <div class="r24-state-list" id="r24-state-list">
                        @foreach ([
                            ['Georgia', '61 defendants', 'The forest cases arrived as a cohort: sixty-one people indicted together under the state RICO act for opposing the Atlanta police training center — the broadest conspiracy prosecution of a protest movement in a generation — each now individually documented, alongside Manuel Paez Terán, killed in the forest in January 2023.'],
                            ['New York', 'Encampment docket', 'The Gaza-solidarity campus prosecutions entered the census as they resolved — most collapsing to violations or dismissals, a pattern the record now makes visible from arraignment to outcome.'],
                            ['Texas', 'Carswell files', 'FMC Carswell — the federal medical center that has held Reality Winner, Aafia Siddiqui, and Marius Mason — became the census\'s most-documented women\'s facility, with new medical-neglect records attached to each case.'],
                            ['Pennsylvania', '5th decade', 'Mumia Abu-Jamal entered his forty-third year in custody; three Philadelphia protest defendants came home. Both facts sit in the same table now.'],
                            ['California', 'Legacy files', 'Volunteers finished digitizing the Ruchell Magee legacy files — sixty-one years of court paper from the longest-held political prisoner in U.S. history, released in 2023.'],
                            ['Minnesota', 'Metro cohort', 'The federal protest prosecutions that began with 2024\'s enforcement surges were logged at indictment, so the census will hold their whole arc.'],
                            ['Oregon', '30 resolutions', 'Thirty resolved Portland ICE-protest prosecutions were closed out in the census — twenty-eight ending in probation, supervised release, or dismissal after felony threats of up to twenty years.'],
                            ['Florida', 'USP Coleman', 'Leonard Peltier\'s 49th year was documented from USP Coleman as the clemency campaign built — the census\'s oldest open case, closed at last in early 2025.'],
                            ['Missouri', 'Home', 'Eric King\'s release closed an eleven-year file that documented some of the harshest solitary conditions in the federal system.'],
                            ['Massachusetts', 'Home', 'Tarek Mehanna\'s release closed the census\'s defining material-support-for-translation case, thirteen years after it opened.'],
                            ['North Dakota', 'NoDAPL legacy', 'The Standing Rock prosecutions were completed as a cohort: more than 800 arrests, now traceable from charge to outcome.'],
                            ['Washington, DC', 'Federal docket', 'Daniel Hale\'s release, the grand-jury fights, and the federal protest docket kept the capital the census\'s busiest single jurisdiction.'],
                        ] as $i => [$state, $count, $note])
                            <button type="button" class="r24-state-btn {{ $i === 0 ? 'on' : '' }}" data-state="{{ $i }}"
                                data-name="{{ $state }}" data-count="{{ $count }}" data-note="{{ $note }}">
                                {{ $state }} <span class="n">{{ $count }}</span>
                            </button>
                        @endforeach
                    </div>
                    <div class="r24-states-hint">Click each state to learn more</div>
                </div>
                <div class="r24-state-panel" id="r24-state-panel">
                    <h4>Georgia</h4>
                    <div class="count">61 defendants</div>
                    <p>The forest cases arrived as a cohort: sixty-one people indicted together under the state RICO
                    act for opposing the Atlanta police training center — the broadest conspiracy prosecution of a
                    protest movement in a generation — each now individually documented, alongside Manuel Paez
                    Terán, killed in the forest in January 2023.</p>
                </div>
            </div>

            <h2 class="r24-h3 rv" style="margin-top:90px">The machinery of political prosecution</h2>
            <p class="rv" style="color:var(--dim); max-width:760px">Six legal instruments account for most of the
            cases added in 2024. Open each to see how it works.</p>
            <div class="r24-acc">
                @foreach ([
                    ['The Espionage Act', 'Written for saboteurs in 1917, now the standard charge against sources and publishers. Defendants cannot tell the jury why they acted; more source prosecutions have been brought since 2009 than in the statute\'s entire prior history.'],
                    ['RICO & conspiracy', 'Racketeering statutes built for organized crime, redeployed to treat a protest movement as a criminal enterprise — sweeping in legal observers and bail funds, as in the 61-defendant Atlanta indictment.'],
                    ['Material support', 'A charge that criminalizes association: translations, donations, and advocacy prosecuted as support for terrorism, with sentences that dwarf the conduct.'],
                    ['State domestic-terrorism statutes', 'New state laws that upgrade protest offenses — including simple trespass — into terrorism counts carrying decades, first tested at scale against the Atlanta forest movement.'],
                    ['Felony protest enhancements', 'Riot, mob action, and blocking statutes that convert misdemeanor protest conduct into felonies. Most collapse before trial; the census records the years people spend under their threat.'],
                    ['Clemency as afterthought', 'Parole denied on schedule, pardons reserved for a presidency\'s final hours. The census documents the waiting — 49 years of it, in its oldest case.'],
                ] as [$q, $a])
                    <div class="r24-acc-item rv">
                        <button type="button" class="r24-acc-q">{{ $q }} <span class="chev">+</span></button>
                        <div class="r24-acc-a"><p>{{ $a }}</p></div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="r24-cta">
            <div class="r24-wrap">
                <h3 class="rv">6,530 cases and counting. Explore the census.</h3>
                <a class="r24-btn solid rv" href="/database">Open the database</a>
            </div>
        </div>
    </section>

    {{-- SECTION 3: THE MOVEMENT --}}
    <section class="r24-section tint" id="movement">
        <div class="r24-wrap">
            <h1 class="r24-h1 rv">The Movement</h1>
            <p class="r24-lede rv">The census is kept by hand — by volunteers, families, archivists, and the readers
            who made 2024 the year the record found its public.</p>

            <span class="r24-label rv" style="margin-top:50px">Highlights</span>
            <div class="r24-cards">
                <div class="r24-card rv">
                    <img src="{{ asset('videos/nppc-launch-film-poster.jpg') }}" alt="">
                    <div class="r24-card-body"><h4>The Memorial</h4>
                    <p>One star for every name in the census, rendered live from the database — the year&rsquo;s
                    most-shared page, and its quietest.</p></div>
                </div>
                <div class="r24-card rv" style="transition-delay:90ms">
                    <img src="/storage/history/the-first-red-scare.png" alt="">
                    <div class="r24-card-body"><h4>The Dispatch</h4>
                    <p>The weekly newsletter grew to 31,000 subscribers, and census data was cited in court filings,
                    syllabi, and at least one parole packet.</p></div>
                </div>
                <div class="r24-card rv" style="transition-delay:180ms">
                    <img src="/storage/history/attica.jpg" alt="">
                    <div class="r24-card-body"><h4>Days of Remembrance</h4>
                    <p>Volunteers marked 53 anniversaries with case histories, letter-writing drives, and commissary
                    funds — from Attica in September to Fred Hampton in December.</p></div>
                </div>
            </div>

            <h2 class="r24-h3 rv" style="margin-top:90px">What readers wrote back</h2>
            <div class="r24-slider rv rv-fade" id="r24-slider">
                <div class="r24-slide on"><p>&ldquo;I found my grandfather in the database. Deported in 1920. We never
                    knew the docket number. Now my family does.&rdquo;</p><span>— A reader in Michigan</span></div>
                <div class="r24-slide"><p>&ldquo;I assign the census to my students instead of a textbook chapter. It
                    argues less and documents more.&rdquo;</p><span>— A history teacher in Oregon</span></div>
                <div class="r24-slide"><p>&ldquo;When the sentencing coverage vanished from the local paper&rsquo;s
                    site, the case page was still there. That&rsquo;s the whole point, isn&rsquo;t it?&rdquo;</p><span>— A defense investigator in Texas</span></div>
                <div class="r24-slide"><p>&ldquo;Eleven dollars documents a case. That&rsquo;s the best price on
                    memory I&rsquo;ve ever seen.&rdquo;</p><span>— A monthly donor in Ohio</span></div>
                <div class="r24-slider-dots" id="r24-slider-dots"></div>
            </div>

            <h2 class="r24-h3 rv" style="margin-top:90px">In Memoriam 2024</h2>
            <div class="r24-honors">
                <div class="r24-honor rv">
                    <img src="/storage/prisoners/john-sinclair.jpg" alt="John Sinclair">
                    <h3>John Sinclair</h3>
                    <h5>Poet and White Panther, whose ten-for-two sentence filled an arena. Freed 1971; kept the
                    case&rsquo;s meaning alive for half a century. 1941&ndash;2024.</h5>
                </div>
                <div class="r24-honor rv" style="transition-delay:90ms">
                    <img src="/storage/prisoners/james-lawson.jpg" alt="Rev. James Lawson">
                    <h3>Rev. James Lawson</h3>
                    <h5>Imprisoned as a draft resister in 1951; taught a generation of the movement how to be
                    arrested without surrendering a thing. 1928&ndash;2024.</h5>
                </div>
                <div class="r24-honor rv" style="transition-delay:180ms">
                    <img src="/storage/history/wounded-knee.jpg" alt="">
                    <h3>Bo Brown, Viola Plummer &amp; Randall Kehler</h3>
                    <h5>Three organizers who served their time and then served everyone else&rsquo;s — entered in
                    the memorial this year, where the record keeps them.</h5>
                </div>
            </div>
        </div>
        <div class="r24-cta">
            <div class="r24-wrap">
                <h3 class="rv">Write to someone still inside.</h3>
                <a class="r24-btn solid rv" href="/prisoner-outreach">Prisoner outreach</a>
            </div>
        </div>
    </section>

    {{-- FINANCIALS --}}
    <section class="r24-fin">
        <div class="r24-wrap">
            <span class="r24-label rv">Financials</span>
            <h2 class="rv">Statement of Activities — FY24</h2>
            @php
                $fin = [
                    ['Revenue', 3113740, [
                        ['Individuals', 1842300, '#5660fe'], ['Foundations', 811450, '#23233f'],
                        ['Events', 118240, '#8f97ff'], ['Store & publications', 92616, '#c5c9f5'],
                        ['Investments', 186024, '#dcd5c0'], ['Other income', 63110, '#7a80d0'],
                    ]],
                    ['Expenditures', 2764361, [
                        ['Program', 2041880, '#5660fe'], ['Management & General', 391562, '#23233f'],
                        ['Fundraising', 330919, '#c5c9f5'],
                    ]],
                    ['Expenses by Program', 2764361, [
                        ['Census & Research', 698412, '#5660fe'], ['Archive & Digital', 512306, '#23233f'],
                        ['Family & Commissary', 449875, '#8f97ff'], ['Legal Support Fund', 342118, '#dcd5c0'],
                        ['Communications', 361240, '#7a80d0'], ['Development', 218395, '#c5c9f5'],
                        ['Operations', 182015, '#31316b'],
                    ]],
                ];
            @endphp
            <div class="r24-fin-grid">
                @foreach ($fin as $fi => [$title, $total, $rows])
                    <div class="rv" style="transition-delay: {{ $fi * 110 }}ms">
                        <h3>{{ $title }}</h3>
                        @php $C = 2 * M_PI * 70; $off = 0; @endphp
                        <svg class="r24-donut" width="200" height="200" viewBox="0 0 200 200" data-donut>
                            @foreach ($rows as [$l, $v, $col])
                                @php $frac = $v / $total; $len = $frac * $C; @endphp
                                <circle cx="100" cy="100" r="70" stroke="{{ $col }}"
                                    stroke-dasharray="{{ number_format($len, 2, '.', '') }} {{ number_format($C - $len, 2, '.', '') }}"
                                    stroke-dashoffset="{{ number_format(-$off, 2, '.', '') }}" data-final-offset="{{ number_format(-$off, 2, '.', '') }}"/>
                                @php $off += $len; @endphp
                            @endforeach
                        </svg>
                        <table>
                            @foreach ($rows as [$l, $v, $col])
                                <tr><td><span class="r24-dot" style="background:{{ $col }}"></span>{{ $l }}</td>
                                    <td class="amt">${{ number_format($v) }}</td></tr>
                            @endforeach
                            <tr class="tot"><td>Total</td><td class="amt">${{ number_format($total) }}</td></tr>
                        </table>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- DONORS --}}
    <section class="r24-donors">
        <div class="r24-wrap">
            <span class="r24-label rv">Fiscal Year 2024 Donors</span>
            <h2 class="r24-h3 rv">Every name here built the record</h2>
            <div class="r24-donors-hint rv">Scroll within each card to see all names</div>
            @php
                $tiers = json_decode(file_get_contents(database_path('data/report-2024-donors.json')), true);
                $corp = ['Bright Ledger Analytics', 'Cedar & Vine Print Co-op', 'Copperline Coffee Roasters',
                    'Driftless Software Collective', 'Fourth Estate Legal, LLP', 'Granite Bay Matching Gifts',
                    'Halftone Press', 'Lakeshore Mutual Employee Giving', 'Northbook Publishing Group',
                    'Old Post Records Management', 'Prairie Signal Internet', 'Redline Bicycle Works',
                    'Sawtooth Data Cooperative', 'Standard Union Brewing', 'Tallgrass Foundation Matching Program',
                    'Watchword Media', 'Westgate Community Credit Union', 'Whetstone Research Group'];
                $cards = [
                    array_slice($tiers, 0, 3),
                    array_slice($tiers, 3, 2),
                    array_slice($tiers, 5, 2),
                    array_merge(array_slice($tiers, 7, 1), [['Corporate & Organizational', $corp]]),
                ];
            @endphp
            <div class="r24-donor-grid rv rv-fade">
                @foreach ($cards as $card)
                    <div class="r24-donor-card">
                        @foreach ($card as [$tier, $names])
                            <h5>{{ $tier }}</h5>
                            @foreach ($names as $n)<div>{{ $n }}</div>@endforeach
                        @endforeach
                    </div>
                @endforeach
            </div>

            <h2 class="r24-h3 rv" style="margin-top:90px">From our donors</h2>
            <div class="r24-acc" style="margin-top:26px">
                @foreach ([
                    ['What first brought you to the coalition\'s work?', 'A line in the Dispatch: a case costs about eleven dollars to document. I had never seen memory priced before. I set up a monthly gift for exactly that — one case a month — and I have not missed one since 2023.'],
                    ['What does your support mean to you?', 'It stopped feeling like charity a long time ago. It feels like paying rent on my own memory — making sure the country cannot forget what it did, because somebody wrote it down and kept the server running.'],
                    ['What would you tell someone considering a gift?', 'Look up one case. Any case. Read to the bottom, where the sources are. Then ask yourself what it is worth to you that the page exists. Eleven dollars was my answer.'],
                ] as [$q, $a])
                    <div class="r24-acc-item rv">
                        <button type="button" class="r24-acc-q">{{ $q }} <span class="chev">+</span></button>
                        <div class="r24-acc-a"><p>&ldquo;{{ $a }}&rdquo; <br><em style="color:var(--dim); font-size:13px">— A monthly donor in Ohio, one of 2,100 sustaining members</em></p></div>
                    </div>
                @endforeach
            </div>

            <h2 class="r24-h3 rv" style="margin-top:90px">Ways to give</h2>
            <div class="r24-ways">
                <a class="r24-way rv" href="/donate"><h4>Donate</h4><p>A one-time gift — $11 documents a case, start to finish.</p></a>
                <a class="r24-way rv" style="transition-delay:80ms" href="/donate"><h4>Give monthly</h4><p>Join 2,100 sustaining members, the census&rsquo;s largest source of support.</p></a>
                <a class="r24-way rv" style="transition-delay:160ms" href="/prisoner-outreach"><h4>Support people inside</h4><p>Commissary funds and letters for the 172 people in custody.</p></a>
                <a class="r24-way rv" style="transition-delay:240ms" href="/volunteer"><h4>Volunteer</h4><p>Join the research corps — one case is enough to start.</p></a>
            </div>

            <span class="r24-label rv" style="margin-top:90px">Our Team</span>
            <div class="r24-team-card rv">
                <h5>Staff</h5>
                <div><b>Mike McCorkle</b> — Attorney</div>
                <div><b>Brian Mulhearn</b> — Research &amp; Operations</div>
                <h5>Research Volunteers</h5>
                <div>118 volunteers in 34 states built the census this year — archivists, law students, librarians,
                families of the imprisoned, and two former political prisoners. Because of the nature of this work,
                most ask not to be named. They know who they are. So do the 6,530.</div>
                <h5>Advisory Readers</h5>
                <div>Every contested entry is reviewed by an outside panel of historians and movement veterans before
                publication. Our thanks to the eleven readers who served in 2024.</div>
            </div>
        </div>
    </section>

    {{-- THANK YOU --}}
    <section class="r24-thanks">
        <div class="r24-wrap">
            <h1 class="rv">Thank You</h1>
            <p class="rv">6,530 cases documented. 426 names recovered this year alone. The census exists because you
            decided it should — and it will still be here when everyone who tried to forget is gone.</p>
            <div class="row rv">
                <a class="r24-btn solid" href="/database">Explore the database</a>
                <a class="r24-btn" href="/annual-report">All annual reports</a>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // top progress bar
    var bar = document.getElementById('r24-progress');
    window.addEventListener('scroll', function () {
        var h = document.documentElement.scrollHeight - innerHeight;
        bar.style.width = (h > 0 ? (scrollY / h) * 100 : 0) + '%';
    }, { passive: true });

    // reveals
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
        });
    }, { threshold: 0.16 });
    document.querySelectorAll('.rv').forEach(function (el) { io.observe(el); });

    // expanding story cards
    document.querySelectorAll('[data-story]').forEach(function (card) {
        card.addEventListener('click', function () {
            document.querySelectorAll('[data-story]').forEach(function (c) { c.classList.remove('active'); });
            card.classList.add('active');
        });
    });

    // state explorer
    var panel = document.getElementById('r24-state-panel');
    document.querySelectorAll('.r24-state-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.r24-state-btn').forEach(function (b) { b.classList.remove('on'); });
            btn.classList.add('on');
            panel.innerHTML = '<h4></h4><div class="count"></div><p></p>';
            panel.querySelector('h4').textContent = btn.dataset.name;
            panel.querySelector('.count').textContent = btn.dataset.count;
            panel.querySelector('p').textContent = btn.dataset.note;
        });
    });

    // accordions
    document.querySelectorAll('.r24-acc-q').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var item = btn.parentElement, a = item.querySelector('.r24-acc-a');
            var open = item.classList.toggle('open');
            a.style.maxHeight = open ? a.scrollHeight + 'px' : '0';
        });
    });

    // quote slider
    (function () {
        var slider = document.getElementById('r24-slider');
        if (!slider) return;
        var slides = slider.querySelectorAll('.r24-slide');
        var dots = document.getElementById('r24-slider-dots');
        var i = 0, timer;
        slides.forEach(function (_, k) {
            var d = document.createElement('button');
            d.type = 'button'; d.className = 'r24-sdot' + (k ? '' : ' on');
            d.setAttribute('aria-label', 'Quote ' + (k + 1));
            d.addEventListener('click', function () { go(k); restart(); });
            dots.appendChild(d);
        });
        function go(k) {
            slides[i].classList.remove('on');
            dots.children[i].classList.remove('on');
            i = (k + slides.length) % slides.length;
            slides[i].classList.add('on');
            dots.children[i].classList.add('on');
        }
        function restart() { clearInterval(timer); if (!reduced) timer = setInterval(function () { go(i + 1); }, 6500); }
        restart();
    })();

    // donut sweep-in
    var dio = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (!e.isIntersecting) return;
            dio.unobserve(e.target);
            e.target.querySelectorAll('circle').forEach(function (c) {
                if (reduced) return;
                var final = c.getAttribute('data-final-offset');
                var dash = c.getAttribute('stroke-dasharray').split(' ');
                var C = parseFloat(dash[0]) + parseFloat(dash[1]);
                c.style.transition = 'none';
                c.setAttribute('stroke-dashoffset', parseFloat(final) + C * 0.35);
                void c.getBoundingClientRect();
                c.style.transition = '';
                c.setAttribute('stroke-dashoffset', final);
            });
        });
    }, { threshold: 0.5 });
    document.querySelectorAll('[data-donut]').forEach(function (el) { dio.observe(el); });
});
</script>
@endsection
