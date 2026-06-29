@extends('app')

@section('title', "The Data-Center Revolt — Arrests & Protest over AI Data Centers | NPPC")

@section('head')
<meta name="description" content="A living NPPC briefing on the grassroots revolt against AI data centers — the residents arrested and charged at town halls and protests, the police surveillance of opponents, and the nationwide fights over water, power, and land. Compiled from local reporting, court records, and the NPPC live tracker.">
<style>
    /* ============================================================
       The Data-Center Revolt — dark briefing page in the NPPC
       series. Near-black background, white text, an electric-cyan
       (#22d3ee) accent. Self-contained: pure Blade + CSS, no JS or
       external data files. All classes scoped with the dcc- prefix.
       ============================================================ */

    /* Full-bleed: drop the centered .container constraints so the
       black background spans the viewport. */
    body.page-data-center-cases main.container,
    body.page-data-center-cases .container { max-width: none !important; padding-left: 0 !important; padding-right: 0 !important; overflow: visible !important; }
    body.page-data-center-cases { background: #0a0b0d; }

    .dcc { background: #0a0b0d; color: rgba(255,255,255,.82); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
    .dcc *, .dcc *::before, .dcc *::after { box-sizing: border-box; }
    .dcc a { color: #5fd6e8; text-decoration: none; }
    .dcc a:hover { color: var(--on-dark); }

    /* ---- layout primitives ---- */
    .dcc-wrap { width: 100%; max-width: 880px; margin: 0 auto; padding: 0 24px; }
    .dcc-wide { width: 100%; max-width: 1120px; margin: 0 auto; padding: 0 24px; }
    .dcc-section { padding: 56px 0; }
    .dcc-section--tight { padding: 30px 0; }
    .dcc-divider { border: 0; border-top: 1px solid rgba(255,255,255,.10); margin: 0; }

    /* ---- hero (CSS-only: cyan glow + faint server-rack pattern) ---- */
    .dcc-hero { position: relative; overflow: hidden; background: #0a0b0d; padding: 124px 0 64px; }
    .dcc-hero::before { content: ""; position: absolute; inset: 0; z-index: 0;
        background:
            radial-gradient(80% 60% at 78% 8%, rgba(34,211,238,.20), transparent 60%),
            repeating-linear-gradient(90deg, rgba(255,255,255,.045) 0 1px, transparent 1px 26px),
            repeating-linear-gradient(0deg, rgba(255,255,255,.03) 0 1px, transparent 1px 26px),
            linear-gradient(180deg, #0c1116 0%, #0a0b0d 70%); }
    .dcc-hero::after { content: ""; position: absolute; left: 0; right: 0; bottom: 0; height: 120px; z-index: 0; background: linear-gradient(180deg, transparent, #0a0b0d); }
    .dcc-hero > * { position: relative; z-index: 1; }
    .dcc-kicker { display: inline-flex; align-items: center; gap: 10px; font-size: 12px; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; color: #7fe3f1; margin-bottom: 20px; }
    .dcc-kicker::before { content: ""; width: 28px; height: 2px; background: #22d3ee; }
    .dcc-h1 { font-size: 4.2rem; line-height: 1.02; font-weight: 800; letter-spacing: -.025em; margin: 0 0 20px; color: var(--on-dark); }
    .dcc-hero-sub { font-size: 1.4rem; line-height: 1.55; color: rgba(255,255,255,.78); max-width: 760px; margin: 0; }
    .dcc-hero-meta { margin-top: 24px; font-size: 13px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: rgba(255,255,255,.5); }
    .dcc-hero-meta span + span::before { content: "•"; margin: 0 10px; color: #22d3ee; }

    /* ---- latest-update box ---- */
    .dcc-update { display: flex; gap: 16px; align-items: flex-start; background: #11151a; border: 1px solid rgba(255,255,255,.10); border-left: 4px solid #22d3ee; border-radius: 8px; padding: 20px 22px; }
    .dcc-update-ico { flex: 0 0 auto; color: #5fd6e8; margin-top: 2px; }
    .dcc-update-tag { font-size: 11px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: #7fe3f1; }
    .dcc-update h3 { font-size: 1.12rem; font-weight: 800; color: var(--on-dark); margin: 5px 0 6px; }
    .dcc-update p { margin: 0; color: rgba(255,255,255,.7); font-size: 15px; line-height: 1.6; }

    /* ---- eyebrow + headings + prose ---- */
    .dcc-eyebrow { display: inline-flex; align-items: center; gap: 10px; font-size: 12px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; color: #7fe3f1; margin-bottom: 14px; }
    .dcc-eyebrow::before { content: ""; width: 26px; height: 2px; background: #22d3ee; }
    .dcc-h2 { font-size: 2.1rem; line-height: 1.12; font-weight: 800; color: var(--on-dark); margin: 0 0 18px; letter-spacing: -.015em; }
    .dcc-p { font-size: 17px; line-height: 1.75; color: rgba(255,255,255,.76); margin: 0 0 1.2em; }
    .dcc-p:last-child { margin-bottom: 0; }
    .dcc-p strong { color: var(--on-dark); font-weight: 700; }
    .dcc-cite { font-size: 13px; color: rgba(255,255,255,.45); }

    /* ---- stats ---- */
    .dcc-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1px; background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.10); border-radius: 10px; overflow: hidden; margin-top: 32px; }
    .dcc-stat { background: #0e1216; padding: 26px 22px; }
    .dcc-stat-num { font-size: 2.5rem; font-weight: 800; color: #5fd6e8; line-height: 1; letter-spacing: -.02em; }
    .dcc-stat-num small { font-size: .45em; }
    .dcc-stat-label { margin-top: 10px; font-size: 14px; line-height: 1.5; color: rgba(255,255,255,.72); }
    .dcc-stat-src { margin-top: 8px; font-size: 12px; color: rgba(255,255,255,.4); }

    /* ---- pull quote ---- */
    .dcc-pull { border-left: 3px solid #22d3ee; padding: 4px 0 4px 26px; margin: 0; }
    .dcc-pull p { font-size: 1.85rem; line-height: 1.3; font-weight: 700; color: var(--on-dark); margin: 0 0 12px; }
    .dcc-pull cite { font-style: normal; font-size: 14px; letter-spacing: .02em; color: rgba(255,255,255,.5); }

    /* ---- callout (surveillance) ---- */
    .dcc-callout { background: #15110f; border: 1px solid rgba(245,158,11,.30); border-left: 4px solid #f59e0b; border-radius: 10px; padding: 24px 26px; }
    .dcc-callout h3 { font-size: 1.2rem; font-weight: 800; color: var(--on-dark); margin: 0 0 10px; }
    .dcc-callout p { font-size: 15.5px; line-height: 1.7; color: rgba(255,255,255,.74); margin: 0 0 12px; }
    .dcc-callout p:last-child { margin: 0; }
    .dcc-callout strong { color: var(--on-dark); }

    /* ---- cases ---- */
    .dcc-cases { display: flex; flex-direction: column; gap: 40px; }
    .dcc-case { display: grid; grid-template-columns: 96px minmax(0, 1fr); grid-template-areas: "photo name" "photo body"; column-gap: 24px; row-gap: 8px; align-items: start; }
    .dcc-avatar { grid-area: photo; width: 96px; height: 96px; border-radius: 10px; background: rgba(34,211,238,.12); color: #7fe3f1; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.7rem; border: 1px solid rgba(34,211,238,.4); letter-spacing: .02em; }
    .dcc-case > h3 { grid-area: name; font-size: 1.5rem; font-weight: 800; color: var(--on-dark); margin: 0; line-height: 1.1; }
    .dcc-case-body { grid-area: body; min-width: 0; }
    .dcc-case-role { font-size: 12.5px; letter-spacing: .03em; text-transform: uppercase; color: rgba(255,255,255,.5); margin: 4px 0 12px; font-weight: 700; }
    .dcc-case p { font-size: 15px; line-height: 1.65; color: rgba(255,255,255,.72); margin: 0 0 12px; }
    .dcc-tag { display: inline-block; font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; padding: 4px 10px; border-radius: 999px; margin-bottom: 12px; }
    .dcc-tag-charged { background: rgba(34,211,238,.16); color: #7fe3f1; border: 1px solid rgba(34,211,238,.45); }
    .dcc-tag-arrested { background: rgba(245,158,11,.15); color: #f0b860; border: 1px solid rgba(245,158,11,.4); }
    .dcc-coverage { font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; }

    /* ---- flashpoints table ---- */
    .dcc-tbl-wrap { max-height: clamp(420px, 60vh, 620px); overflow-y: auto; border: 1px solid rgba(255,255,255,.12); border-radius: 10px; }
    .dcc-tbl { width: 100%; border-collapse: collapse; font-size: 14.5px; }
    .dcc-tbl thead th { position: sticky; top: 0; background: #15191e; color: var(--on-dark); text-align: left; font-weight: 800; font-size: 12px; letter-spacing: .04em; text-transform: uppercase; padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,.14); z-index: 1; }
    .dcc-tbl tbody td { padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,.07); color: rgba(255,255,255,.82); vertical-align: top; }
    .dcc-tbl tbody tr:last-child td { border-bottom: 0; }
    .dcc-tbl tbody tr:hover td { background: rgba(255,255,255,.03); }
    .dcc-tbl-place { font-weight: 700; color: var(--on-dark); white-space: nowrap; }
    .dcc-tbl-date { white-space: nowrap; color: rgba(255,255,255,.55); font-variant-numeric: tabular-nums; }

    /* ---- lists (trackers + news) ---- */
    .dcc-list { list-style: none; margin: 0; padding: 0; }
    .dcc-list li { border-top: 1px solid rgba(255,255,255,.10); padding: 16px 0; }
    .dcc-list li:last-child { border-bottom: 1px solid rgba(255,255,255,.10); }
    .dcc-list a { font-weight: 700; }
    .dcc-list a:hover { text-decoration: underline; }
    .dcc-list .dcc-src { display: block; font-size: 13px; color: rgba(255,255,255,.5); margin-top: 3px; font-weight: 400; }

    /* ---- methodology ---- */
    .dcc-note { background: #11151a; border: 1px solid rgba(255,255,255,.10); border-radius: 10px; padding: 24px; }
    .dcc-note p { font-size: 14.5px; line-height: 1.7; color: rgba(255,255,255,.7); margin: 0 0 12px; }
    .dcc-note p:last-child { margin: 0; }
    .dcc-note strong { color: var(--on-dark); }

    /* ---- buttons + footer CTA ---- */
    .dcc-btn { display: inline-flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 700; padding: 13px 26px; border-radius: 999px; transition: transform .15s, background .15s, color .15s, border-color .15s; }
    .dcc-btn-light { background: #fff; color: #0a0b0d; }
    .dcc-btn-light:hover { background: #e9f6f9; color: #000; transform: translateY(-1px); }
    .dcc-btn-ghost { background: transparent; color: var(--on-dark); border: 1px solid rgba(255,255,255,.5); }
    .dcc-btn-ghost:hover { background: rgba(255,255,255,.1); color: var(--on-dark); border-color: var(--on-dark); transform: translateY(-1px); }
    .dcc-foot { background: linear-gradient(135deg, #0e7490, #155e75); text-align: center; padding: 60px 24px; }
    .dcc-foot h2 { font-size: 2.1rem; font-weight: 800; margin: 0 0 14px; color: var(--on-dark); letter-spacing: -.01em; }
    .dcc-foot p { font-size: 17px; line-height: 1.6; color: rgba(255,255,255,.92); max-width: 600px; margin: 0 auto 26px; }
    .dcc-foot-btns { display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; }

    /* ---- responsive ---- */
    @@media (max-width: 820px) {
        .dcc-h1 { font-size: 2.9rem; }
        .dcc-hero-sub { font-size: 1.22rem; }
        .dcc-h2 { font-size: 1.7rem; }
        .dcc-pull p { font-size: 1.45rem; }
        .dcc-stats { grid-template-columns: 1fr 1fr; }
    }
    @@media (max-width: 520px) {
        .dcc-h1 { font-size: 2.3rem; }
        .dcc-stats { grid-template-columns: 1fr; }
        .dcc-case { grid-template-columns: 1fr; grid-template-areas: "name" "photo" "body"; }
        .dcc-foot h2 { font-size: 1.7rem; }
    }
</style>
@endsection

@section('body')
<div class="dcc">

    {{-- ==================== HERO ==================== --}}
    <div class="dcc-hero">
        <div class="dcc-wrap">
            <span class="dcc-kicker">NPPC Briefing</span>
            <h1 class="dcc-h1">The Data-Center Revolt</h1>
            <p class="dcc-hero-sub">The AI build-out has reached nearly every state — and so has the backlash. As residents pack town halls to fight the water, power, and land demands of hyperscale data centers, a growing number are being dragged from meetings, jailed, and charged with felonies. This page tracks the people facing arrest for that dissent, and the surveillance now trained on them.</p>
            <div class="dcc-hero-meta"><span>Living resource</span><span>Updated June 2026</span><span>NPPC</span></div>
        </div>
    </div>

    {{-- ==================== LATEST UPDATE ==================== --}}
    <div class="dcc-wrap dcc-section--tight">
        <div class="dcc-update">
            <div class="dcc-update-ico"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg></div>
            <div>
                <div class="dcc-update-tag">Latest development</div>
                <h3>June 5 — New York sends Hochul a statewide data-center freeze</h3>
                <p>The New York Legislature passed the Responsible Data Center Development Act, a one-year moratorium on new permits for large (20+ MW) data centers, and sent it to Governor Hochul. If she signs, New York would be the first state to enact such a freeze — Maine's governor vetoed a similar bill in April. <a href="https://news.bgov.com/bloomberg-government-news/new-york-lawmakers-send-hochul-one-year-ban-on-new-data-centers" target="_blank" rel="noopener">Read more →</a></p>
            </div>
        </div>
    </div>

    {{-- ==================== CONTEXT + STATS ==================== --}}
    <div class="dcc-wrap dcc-section">
        <div class="dcc-eyebrow">What's happening</div>
        <h2 class="dcc-h2">An infrastructure gold rush meets a backlash</h2>
        <p class="dcc-p">The race to build artificial intelligence has touched off the largest construction boom in the history of computing. More than <strong>4,000 data centers</strong> already operate across the United States, with thousands more announced or under construction, and Big Tech is on track to spend roughly <strong>$700 billion</strong> on AI infrastructure in 2025 alone. <span class="dcc-cite">(American Edge Project / Axios; Dec. 2025)</span></p>
        <p class="dcc-p">Those campuses draw staggering amounts of electricity and water, and they are increasingly proposed next to homes, farms, and schools. The result has been a bipartisan, hyper-local revolt: residents flooding county commissions and city councils, winning moratoriums, and stalling tens of billions of dollars in projects. As the meetings have grown more crowded and more heated, officials have answered with speaking-time buzzers, removals, citations — and, in a striking number of cases, felony charges.</p>

        <div class="dcc-stats">
            <div class="dcc-stat">
                <div class="dcc-stat-num">4,000<small>+</small></div>
                <div class="dcc-stat-label">data centers operating in the U.S., with thousands more proposed.</div>
                <div class="dcc-stat-src">American Edge / Axios · 2025</div>
            </div>
            <div class="dcc-stat">
                <div class="dcc-stat-num">$64<small>B</small></div>
                <div class="dcc-stat-label">in projects blocked or delayed by local opposition; 188 groups across ~40 states.</div>
                <div class="dcc-stat-src">Data Center Watch · mid-2026</div>
            </div>
            <div class="dcc-stat">
                <div class="dcc-stat-num">69<small>+</small></div>
                <div class="dcc-stat-label">local jurisdictions with active data-center moratoriums; bills filed in 12+ states.</div>
                <div class="dcc-stat-src">Good Jobs First · Apr. 2026</div>
            </div>
            <div class="dcc-stat">
                <div class="dcc-stat-num">7 in 10</div>
                <div class="dcc-stat-label">Americans say they would oppose a data center built near them.</div>
                <div class="dcc-stat-src">Washington Post · May 2026</div>
            </div>
        </div>
    </div>

    {{-- ==================== PULL QUOTE ==================== --}}
    <div class="dcc-wrap dcc-section--tight">
        <blockquote class="dcc-pull">
            <p>"Your address is public information and I can protest in front of your house all day and night until you gain humanity and ban this data center."</p>
            <cite>— Harley Delander, the Dixon, Illinois resident later charged with three felonies, recounting the message that led to his arrest (Business Insider, May 2026)</cite>
        </blockquote>
    </div>

    <hr class="dcc-divider">

    {{-- ==================== SURVEILLANCE ==================== --}}
    <div class="dcc-wrap dcc-section">
        <div class="dcc-eyebrow">Policing dissent</div>
        <h2 class="dcc-h2">When opposing a data center became an "extremism indicator"</h2>
        <div class="dcc-callout">
            <h3>A fusion center put First Amendment activity on a threat list</h3>
            <p>In June 2026, <strong>The Intercept</strong> revealed a confidential law-enforcement bulletin from the <strong>Delaware Valley Intelligence Center</strong> — a fusion center housed inside the Philadelphia Police Department — that listed "disruptive First Amendment activity" as an <strong>indicator</strong> of risk from "domestic violent extremists" in the context of opposition to AI data centers.</p>
            <p>The December alert leaned on social-media posts, Facebook memes, and even references to the novel <em>Dune</em>, while conceding "a lack of specific information on plans to target AI data centers." A police spokesperson confirmed the monitoring; a civil-rights attorney called it a chilling attempt to recast ordinary protest as a security threat. <span class="dcc-cite">(<a href="https://theintercept.com/2026/06/01/ai-data-center-protest-police-surveillance/" target="_blank" rel="noopener">The Intercept, June 1, 2026</a>)</span></p>
        </div>
    </div>

    <hr class="dcc-divider">

    {{-- ==================== CASE PROFILES ==================== --}}
    <div class="dcc-wrap dcc-section">
        <div class="dcc-eyebrow">Arrested or charged</div>
        <h2 class="dcc-h2">The people facing prosecution</h2>
        <p class="dcc-p">Residents arrested, jailed, or charged in connection with opposition to a data center. Details are drawn from local reporting and official statements; charges and outcomes continue to change. These cases are also plotted on the <a href="/dashboard">NPPC live tracker</a>.</p>

        <div class="dcc-cases">

            <div class="dcc-case">
                <span class="dcc-avatar" aria-hidden="true">HD</span>
                <h3>Harley Delander</h3>
                <div class="dcc-case-body">
                    <p class="dcc-case-role">Dixon, Illinois · Age 28</p>
                    <span class="dcc-tag dcc-tag-charged">Charged · 3 felonies</span>
                    <p>Charged with intimidation, stalking, and cyberstalking in late May 2026 — roughly twelve hours after posting a Facebook event to organize a protest near the home of Tom Demmer, a former state representative leading a group courting a data-center developer near Rock Falls. His attorney said they would weigh "the important First Amendment issues" in the case; Delander says he is counting on free speech as his defense.</p>
                    <a class="dcc-coverage" href="https://www.shawlocal.com/sauk-valley/2026/05/28/dixon-mans-alleged-threat-to-former-state-representative-centered-on-data-center-development-police-chief/" target="_blank" rel="noopener">Coverage of Harley Delander →</a>
                </div>
            </div>

            <div class="dcc-case">
                <span class="dcc-avatar" aria-hidden="true">AH</span>
                <h3>Anthony Hinojosa</h3>
                <div class="dcc-case-body">
                    <p class="dcc-case-role">Trenton, Illinois · Age 33</p>
                    <span class="dcc-tag dcc-tag-charged">Charged · felony hold</span>
                    <p>Charged by the Madison County State's Attorney with intimidation, filing a false report, and electronic harassment — with a felony hold for terroristic threats — over February 2026 Facebook posts that threatened to kill Troy, Illinois officials if they did not halt discussions of a proposed data center. He was released under Illinois' SAFE-T Act.</p>
                    <a class="dcc-coverage" href="https://www.timestribunenews.com/2026/02/17/troy-police-supply-details-on-man-who-threatened-mayor-city-officials-over-data-center/" target="_blank" rel="noopener">Coverage of Anthony Hinojosa →</a>
                </div>
            </div>

            <div class="dcc-case">
                <span class="dcc-avatar" aria-hidden="true">DJ</span>
                <h3>Diego Joe</h3>
                <div class="dcc-case-body">
                    <p class="dcc-case-role">El Centro, California · Age 22</p>
                    <span class="dcc-tag dcc-tag-charged">Charged · felony</span>
                    <p>Arrested near the El Centro Public Library on April 16, 2026 and booked on a felony criminal-threats charge plus a misdemeanor online-harassment count, after allegedly posting threats in a local Facebook group against data-center developer Sebastian Rucci. He was held on $20,000 bail.</p>
                    <a class="dcc-coverage" href="https://www.kpbs.org/news/public-safety/2026/04/20/el-centro-resident-arrested-for-allegedly-making-online-threats-against-data-center-developer" target="_blank" rel="noopener">Coverage of Diego Joe →</a>
                </div>
            </div>

            <div class="dcc-case">
                <span class="dcc-avatar" aria-hidden="true">IA</span>
                <h3>Ismael Arvizu</h3>
                <div class="dcc-case-body">
                    <p class="dcc-case-role">El Centro, California · Age 26</p>
                    <span class="dcc-tag dcc-tag-arrested">Arrested at meeting</span>
                    <p>Arrested at an April 2026 Imperial County Board of Supervisors meeting after speaking against a lot-merger for a 950,000-square-foot data center, which he said would benefit billionaires at residents' expense. He was charged with trespassing, disturbing the peace, resisting arrest, and threatening a public official.</p>
                    <a class="dcc-coverage" href="https://www.latimes.com/california/story/2026-04-11/man-speaking-against-data-center-arrested-at-imperial-county-board-meeting-as-tensions-flare-nationwide" target="_blank" rel="noopener">Coverage of Ismael Arvizu →</a>
                </div>
            </div>

            <div class="dcc-case">
                <span class="dcc-avatar" aria-hidden="true">DB</span>
                <h3>Darren Blanchard</h3>
                <div class="dcc-case-body">
                    <p class="dcc-case-role">Claremore, Oklahoma · Farmer</p>
                    <span class="dcc-tag dcc-tag-arrested">Arrested · jailed</span>
                    <p>A farmer who ran about thirty seconds past his three-minute limit while opposing "Project Mustang," a Beale Infrastructure data center, at a February 17, 2026 Claremore City Council hearing. When he stepped toward the dais to hand over paperwork, the city manager had police remove him; he was booked into the Rogers County Jail on a trespassing charge he calls retaliatory and has vowed to fight.</p>
                    <a class="dcc-coverage" href="https://www.404media.co/farmer-arrested-for-speaking-too-long-at-datacenter-town-hall-vows-to-fight/" target="_blank" rel="noopener">Coverage of Darren Blanchard →</a>
                </div>
            </div>

            <div class="dcc-case">
                <span class="dcc-avatar" aria-hidden="true">CL</span>
                <h3>Christine LeJeune</h3>
                <div class="dcc-case-body">
                    <p class="dcc-case-role">Port Washington, Wisconsin · Resident</p>
                    <span class="dcc-tag dcc-tag-arrested">Arrested · cited</span>
                    <p>One of three residents taken into custody at a December 2, 2025 Common Council meeting over a 902-megawatt data center tied to the OpenAI–Oracle "Stargate" project. Seconds after her buzzer she led a "recall" chant; officers moved to remove her, she went limp and was dragged from the room, and two residents who tried to intervene were also detained. All three were cited for disorderly conduct.</p>
                    <a class="dcc-coverage" href="https://www.commondreams.org/news/ai-data-center-protest" target="_blank" rel="noopener">Coverage of Christine LeJeune →</a>
                </div>
            </div>

            <div class="dcc-case">
                <span class="dcc-avatar" aria-hidden="true">PP</span>
                <h3>Pablo Payan</h3>
                <div class="dcc-case-body">
                    <p class="dcc-case-role">Hobart, Indiana · Age 42</p>
                    <span class="dcc-tag dcc-tag-arrested">Arrested at meeting</span>
                    <p>Arrested for criminal trespass and disorderly conduct at a May 7, 2026 Hobart Plan Commission meeting on an Amazon–NIPSCO data center. After he declined to give his name and address for the record and kept speaking, video shows an officer pushing him over a railing before handcuffing him. Payan disputes the city's account and says he plans to pursue legal action.</p>
                    <a class="dcc-coverage" href="https://www.fox32chicago.com/news/video-shows-man-removed-arrested-indiana-data-center-meeting" target="_blank" rel="noopener">Coverage of Pablo Payan →</a>
                </div>
            </div>

        </div>
    </div>

    <hr class="dcc-divider">

    {{-- ==================== FLASHPOINTS ==================== --}}
    <div class="dcc-wide dcc-section">
        <div class="dcc-wrap" style="padding:0;">
            <div class="dcc-eyebrow">A national fight</div>
            <h2 class="dcc-h2">Flashpoints, coast to coast</h2>
            <p class="dcc-p">Beyond the arrests, the data-center fight has filled hearing rooms and statehouses in dozens of communities — and, in one case, turned violent. A sampling of recent flashpoints:</p>
        </div>
        <div class="dcc-tbl-wrap" style="margin-top:18px;">
            <table class="dcc-tbl">
                <thead>
                    <tr><th>Place</th><th>Date</th><th>What happened</th></tr>
                </thead>
                <tbody>
                    <tr><td class="dcc-tbl-place">Bessemer, AL</td><td class="dcc-tbl-date">Sep 13, 2025</td><td>Residents pack a listening session against a 675-acre hyperscale data center. <a href="https://www.wbrc.com/2025/09/14/bessemer-residents-frustrated-over-proposed-data-center/" target="_blank" rel="noopener">WBRC →</a></td></tr>
                    <tr><td class="dcc-tbl-place">Memphis, TN</td><td class="dcc-tbl-date">Oct 4, 2025</td><td>"Tigers Against Pollution" march downtown against Elon Musk's xAI "Colossus." <a href="https://www.aol.com/articles/memphis-activists-stage-downtown-march-005536611.html" target="_blank" rel="noopener">Commercial Appeal →</a></td></tr>
                    <tr><td class="dcc-tbl-place">Prince William County, VA</td><td class="dcc-tbl-date">Nov 5, 2025</td><td>Residents protest a Dominion data-center transmission line at an open house. <a href="https://www.fox5dc.com/news/prince-william-county-residents-once-again-protest-against-proposed-data-centers" target="_blank" rel="noopener">FOX 5 DC →</a></td></tr>
                    <tr><td class="dcc-tbl-place">Tucker County, WV</td><td class="dcc-tbl-date">Dec 3, 2025</td><td>Residents rally at a state air-quality hearing over a data-center permit. <a href="https://wvmetronews.com/2025/12/03/protesters-oppose-tucker-county-data-center-as-air-quality-board-considers-permit-appeal/" target="_blank" rel="noopener">WV MetroNews →</a></td></tr>
                    <tr><td class="dcc-tbl-place">Knox, IN</td><td class="dcc-tbl-date">Dec 4, 2025</td><td>Hundreds pack a high school; the plan commission backs a moratorium. <a href="https://wkvi.com/2025/12/starke-county-plan-commission-unanimously-recommend-a-moratorium-on-data-centers/" target="_blank" rel="noopener">WKVI →</a></td></tr>
                    <tr><td class="dcc-tbl-place">Lansing, MI</td><td class="dcc-tbl-date">Dec 16, 2025</td><td>Residents rally at the Michigan Capitol against data-center projects. <a href="https://www.wilx.com/2025/12/16/data-center-protest-outside-michigan-capitol/" target="_blank" rel="noopener">WILX →</a></td></tr>
                    <tr><td class="dcc-tbl-place">Harwood, ND</td><td class="dcc-tbl-date">Jan 12, 2026</td><td>A reported JD Vance visit draws an impromptu protest at the Applied Digital site. <a href="https://www.grandforksherald.com/news/north-dakota/reported-jd-vance-visit-draws-impromptu-protest-at-north-dakota-data-center-site" target="_blank" rel="noopener">Grand Forks Herald →</a></td></tr>
                    <tr><td class="dcc-tbl-place">Festus, MO</td><td class="dcc-tbl-date">Mar 30, 2026</td><td>Residents pack a high-school gym to oppose a $6B data center before a council vote. <a href="https://www.stlpr.org/show/st-louis-on-the-air/2026-04-24/festus-residents-opposed-data-center-national-news-lawsuit-election-city-council" target="_blank" rel="noopener">St. Louis Public Radio →</a></td></tr>
                    <tr><td class="dcc-tbl-place">Indianapolis, IN</td><td class="dcc-tbl-date">Apr 6, 2026</td><td>Thirteen shots hit a councilman's home with a "No Data Centers" note left behind — a violent act condemned across the movement. <a href="https://www.cbsnews.com/news/indianapolis-councilor-ron-gibson-home-shooting-data-centers-note/" target="_blank" rel="noopener">CBS News →</a></td></tr>
                    <tr><td class="dcc-tbl-place">Perry Village, OH</td><td class="dcc-tbl-date">Apr 9, 2026</td><td>A crowd packs the village hall to protest a data-center project. <a href="https://www.news5cleveland.com/news/local-news/crowd-packs-perry-village-hall-to-protest-data-center-project-as-tensions-rise-across-ohio" target="_blank" rel="noopener">News 5 Cleveland →</a></td></tr>
                    <tr><td class="dcc-tbl-place">Andover Township, NJ</td><td class="dcc-tbl-date">May 7, 2026</td><td>A resident is forcibly removed from a committee meeting (no charges filed) as the township weighs a data-center ban. <a href="https://www.datacenterdynamics.com/en/news/ban-on-data-centers-being-considered-in-andover-township-new-jersey-after-town-meeting-gets-violent/" target="_blank" rel="noopener">DataCenter Dynamics →</a></td></tr>
                    <tr><td class="dcc-tbl-place">Reno, NV</td><td class="dcc-tbl-date">May 14, 2026</td><td>Reno becomes the first Nevada city to pause new data centers after a packed meeting. <a href="https://www.reviewjournal.com/news/environment/this-nevada-city-is-the-first-to-pause-new-data-centers-3824360/" target="_blank" rel="noopener">Las Vegas Review-Journal →</a></td></tr>
                    <tr><td class="dcc-tbl-place">East Fishkill, NY</td><td class="dcc-tbl-date">May 21, 2026</td><td>Residents rally against a proposed data center as a statewide moratorium advances. <a href="https://www.foodandwaterwatch.org/2026/06/04/ny-gov-hochul-must-sign-one-year-ai-data-center-moratorium-passed-by-legislature/" target="_blank" rel="noopener">Food &amp; Water Watch →</a></td></tr>
                    <tr><td class="dcc-tbl-place">Salt Lake City, UT</td><td class="dcc-tbl-date">May 23, 2026</td><td>More than 600 rally at the Utah Capitol against a Box Elder data center. <a href="https://www.ksl.com/article/51501551/hundreds-of-utahns-rally-against-proposed-box-elder-data-center" target="_blank" rel="noopener">KSL →</a></td></tr>
                    <tr><td class="dcc-tbl-place">Shalersville Township, OH</td><td class="dcc-tbl-date">May 29, 2026</td><td>Hundreds rally against a proposed Bitdeer data center near the Ohio Turnpike. <a href="https://www.cleveland19.com/2026/05/30/shalersville-residents-rally-against-proposed-data-center/" target="_blank" rel="noopener">Cleveland 19 →</a></td></tr>
                    <tr><td class="dcc-tbl-place">Belton, TX</td><td class="dcc-tbl-date">Jun 1, 2026</td><td>Bell County residents pack commissioners court to oppose a Temple data center. <a href="https://www.kwtx.com/2026/06/02/we-dont-want-this-bell-county-residents-show-up-by-masses-protest-data-center-asks-commissioners-court-intervention/" target="_blank" rel="noopener">KWTX →</a></td></tr>
                    <tr><td class="dcc-tbl-place">Morris, IL</td><td class="dcc-tbl-date">Jun 2, 2026</td><td>Hundreds rally against a proposed hyperscale data center. <a href="https://www.wcsjnews.com/news/local/data-center-protest-held-in-morris-on-saturday/article_f3eac834-b42a-4dc9-b9a8-ce7196f30b34.html" target="_blank" rel="noopener">WCSJ News →</a></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <hr class="dcc-divider">

    {{-- ==================== TRACKERS ==================== --}}
    <div class="dcc-wrap dcc-section">
        <div class="dcc-eyebrow">Follow the data</div>
        <h2 class="dcc-h2">Trackers &amp; campaigns</h2>
        <ul class="dcc-list">
            <li>
                <a href="https://www.datacenterwatch.org/" target="_blank" rel="noopener">Data Center Watch →</a>
                <span class="dcc-src">National tracker of opposition and blocked or delayed projects. (Backed by 10a Labs, an AI-security firm.)</span>
            </li>
            <li>
                <a href="https://www.foodandwaterwatch.org/2026/01/22/stop-data-centers-now-campaign-launch/" target="_blank" rel="noopener">Food &amp; Water Watch — "Stop Data Centers Now!" →</a>
                <span class="dcc-src">National campaign supporting local fights, moratorium bills, and a federal moratorium act.</span>
            </li>
            <li>
                <a href="https://goodjobsfirst.org/data-center-moratorium-bills-are-spreading-in-2026/" target="_blank" rel="noopener">U.S. Data Center Moratorium Tracker — Good Jobs First →</a>
                <span class="dcc-src">Tracks local and state moratorium bills and enacted pauses.</span>
            </li>
            <li>
                <a href="https://floridadatacenters.org/" target="_blank" rel="noopener">Data Center Opposition Tracker →</a>
                <span class="dcc-src">Community-facing tracker of data-center fights across all 50 states.</span>
            </li>
        </ul>
    </div>

    <hr class="dcc-divider">

    {{-- ==================== IN THE NEWS ==================== --}}
    <div class="dcc-wrap dcc-section">
        <div class="dcc-eyebrow">In the news</div>
        <h2 class="dcc-h2">Selected coverage</h2>
        <ul class="dcc-list">
            <li>
                <a href="https://theintercept.com/2026/06/01/ai-data-center-protest-police-surveillance/" target="_blank" rel="noopener">Philly cops admit they're tracking "First Amendment activity" critical of AI — The Intercept</a>
                <span class="dcc-src">The Intercept · June 1, 2026</span>
            </li>
            <li>
                <a href="https://www.nbcnews.com/politics/economics/state-local-opposition-new-data-centers-gaining-steam-rcna243838" target="_blank" rel="noopener">State and local opposition to new data centers is gaining steam — NBC News</a>
                <span class="dcc-src">NBC News · June 2026</span>
            </li>
            <li>
                <a href="https://fortune.com/2026/05/18/communities-are-blocking-billions-in-data-centers-big-tech-has-wagered-1-trillion-otherwise/" target="_blank" rel="noopener">Communities are blocking billions in data centers — Fortune</a>
                <span class="dcc-src">Fortune · May 18, 2026</span>
            </li>
            <li>
                <a href="https://www.washingtonpost.com/nation/2026/05/13/7-10-americans-oppose-data-centers-being-built-their-communities/" target="_blank" rel="noopener">7 in 10 Americans oppose data centers being built in their communities — Washington Post</a>
                <span class="dcc-src">Washington Post · May 13, 2026</span>
            </li>
            <li>
                <a href="https://www.pbs.org/newshour/politics/ocasio-cortez-and-sanders-push-bill-to-impose-ai-data-center-moratorium" target="_blank" rel="noopener">Ocasio-Cortez and Sanders push a bill for a federal AI data-center moratorium — PBS NewsHour</a>
                <span class="dcc-src">PBS NewsHour · March 2026</span>
            </li>
            <li>
                <a href="https://fortune.com/2026/04/07/indianapolis-councilmember-ai-data-center-backlash/" target="_blank" rel="noopener">A councilman backed a data center — then 13 bullets and a "No Data Centers" note hit his home — Fortune</a>
                <span class="dcc-src">Fortune · April 7, 2026</span>
            </li>
        </ul>
    </div>

    <hr class="dcc-divider">

    {{-- ==================== METHODOLOGY ==================== --}}
    <div class="dcc-wrap dcc-section">
        <div class="dcc-eyebrow">About this page</div>
        <h2 class="dcc-h2">Methodology &amp; sources</h2>
        <div class="dcc-note">
            <p>This briefing compiles arrests, charges, and protests connected to opposition to AI data centers, drawn from local news reporting, official law-enforcement statements, and court records. Each case and flashpoint links to its source.</p>
            <p>We include cases where a person was <strong>arrested, cited, jailed, or charged</strong> in connection with data-center opposition — whether for conduct at a public meeting or for online speech. We distinguish these from acts of violence: the Indianapolis shooting is listed as a flashpoint, not as a protest case, and was condemned by opposition groups. Several national totals (the number of U.S. data centers, the value of blocked projects) vary by source and methodology; we cite the trackers we rely on and note their backers where relevant.</p>
            <p>Charges and outcomes change as cases move through the courts. Spot an error or a case we've missed? <a href="/contact">Let us know.</a></p>
        </div>
    </div>

    {{-- ==================== FOOTER CTA ==================== --}}
    <div class="dcc-foot">
        <h2>These fights are happening near you.</h2>
        <p>Explore every documented case on the live tracker, or tell us about an arrest or protest in your community.</p>
        <div class="dcc-foot-btns">
            <a class="dcc-btn dcc-btn-light" href="/dashboard">Explore the live tracker</a>
            <a class="dcc-btn dcc-btn-ghost" href="/contact">Report a data-center case</a>
        </div>
    </div>

</div>
@endsection
