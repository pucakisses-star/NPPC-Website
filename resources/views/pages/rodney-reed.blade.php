@extends('app')

@section('title', 'Everything You Should Know About Rodney Reed | NPPC')

@section('head')
<meta name="description" content="Rodney Reed has spent nearly 30 years on Texas death row for a murder he has always said he did not commit. The murder weapon has never been DNA tested. Everything you should know about his case.">
<style>
    /* ============================================================
       Rodney Reed — long-form feature, recreated in the NPPC house
       style from the Innocence Project's "Everything You Should Know
       About Rodney Reed" photo essay. Full-bleed dark banners alternate
       with clean light/dark content. All classes scoped with rr- prefix.
       ============================================================ */
    .rr { background: var(--bg); color: var(--fg); font-feature-settings: "kern" 1; }
    .rr * { box-sizing: border-box; }
    .rr a { color: var(--accent-2); }
    .rr a:hover { color: var(--fg); }

    /* ---- Full-bleed banners ---- */
    .rr-banner { position: relative; min-height: 460px; display: flex; align-items: flex-end; overflow: hidden; color: var(--on-dark); }
    .rr-banner--hero { min-height: 620px; }
    .rr-banner-bg { position: absolute; inset: 0; z-index: 0; }
    /* faint vertical "bars" texture evoking a cell, over the gradient */
    .rr-banner-bg::after { content: ""; position: absolute; inset: 0;
        background-image: repeating-linear-gradient(90deg, rgba(255,255,255,0.05) 0 1px, transparent 1px 64px);
        mask-image: linear-gradient(180deg, transparent, #000 70%); }
    .rr-banner-inner { position: relative; z-index: 1; width: 100%; max-width: 1080px; margin: 0 auto; padding: 64px 32px 56px; }
    .rr-overline { display: inline-block; font-size: 13px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: rgba(255,255,255,0.78); margin-bottom: 16px; padding-bottom: 12px; border-bottom: 2px solid rgba(255,255,255,0.45); }
    .rr-banner-title { font-size: 3.6rem; line-height: 1.03; font-weight: 800; letter-spacing: -0.015em; margin: 0; color: var(--on-dark); }
    .rr-banner--hero .rr-banner-title { font-size: 4.6rem; }
    .rr-banner-sub { font-size: 1.3rem; line-height: 1.5; color: rgba(255,255,255,0.86); max-width: 660px; margin: 24px 0 0; }
    .rr-banner-meta { margin-top: 26px; font-size: 13px; letter-spacing: 0.04em; color: rgba(255,255,255,0.62); }

    /* accent gradients per section */
    .rr-bg-hero    { background: radial-gradient(120% 120% at 70% 0%, #2a2f3a 0%, #14161d 55%, #08090d 100%); }
    .rr-bg-slate   { background: radial-gradient(120% 120% at 80% 0%, #33485f 0%, #1b2738 65%, #111a28 100%); }
    .rr-bg-crimson { background: radial-gradient(120% 120% at 80% 0%, #8f2b2b 0%, #511414 65%, #330b0b 100%); }
    .rr-bg-indigo  { background: radial-gradient(120% 120% at 80% 0%, #4b54d6 0%, #262c7a 65%, #161a47 100%); }
    .rr-bg-ink     { background: radial-gradient(120% 120% at 50% 0%, #232734 0%, #0f1117 70%, #07080c 100%); }

    /* ---- Content sections ---- */
    .rr-wrap { max-width: 760px; margin: 0 auto; padding: 0 32px; }
    .rr-section { padding: 72px 0; }
    .rr-section--tint { background: var(--surface); }
    .rr-kicker { display: inline-block; font-size: 12px; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase; color: var(--accent-2); margin-bottom: 14px; }
    .rr-kicker--crimson { color: #c0413b; }
    .rr-h2 { font-size: 2.4rem; line-height: 1.12; font-weight: 800; color: var(--fg); margin: 0 0 22px; letter-spacing: -0.015em; }
    .rr-p { font-size: 18px; line-height: 1.78; color: rgba(var(--fg-rgb),0.82); margin: 0 0 1.3em; }
    .rr-p strong { color: var(--fg); font-weight: 700; }
    .rr-p:last-child { margin-bottom: 0; }
    .rr-lead .rr-p { font-size: 1.4rem; line-height: 1.5; color: var(--fg); }
    .rr-lead .rr-p:first-child::first-letter { float: left; font-size: 4em; line-height: 0.72; padding: 0.06em 0.1em 0 0; color: var(--accent-2); font-weight: 800; }

    /* ---- Two-up bio (Reed / Stites) ---- */
    .rr-people { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; margin-top: 8px; }
    .rr-person { background: var(--surface); border: 1px solid rgba(var(--fg-rgb),0.12); border-radius: 8px; padding: 26px; }
    .rr-person h3 { font-size: 1.3rem; font-weight: 800; color: var(--fg); margin: 0 0 4px; }
    .rr-person .rr-person-role { font-size: 13px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; color: var(--accent-2); margin: 0 0 14px; }
    .rr-person p { font-size: 15px; line-height: 1.66; color: rgba(var(--fg-rgb),0.78); margin: 0; }

    /* ---- Stats band ---- */
    .rr-stats-band { background: #14161d; color: var(--on-dark); }
    .rr-stats { max-width: 1080px; margin: 0 auto; padding: 0 32px; display: grid; grid-template-columns: repeat(4, 1fr); }
    .rr-stat { padding: 52px 22px; border-left: 1px solid rgba(255,255,255,0.12); }
    .rr-stat:first-child { border-left: 0; }
    .rr-stat-num { font-size: 3.4rem; line-height: 1; font-weight: 800; color: #e0796f; letter-spacing: -0.02em; }
    .rr-stat-num small { font-size: 0.4em; color: rgba(255,255,255,0.55); font-weight: 700; }
    .rr-stat-label { margin-top: 14px; font-size: 14px; line-height: 1.5; color: rgba(255,255,255,0.78); }
    .rr-stats-src { max-width: 1080px; margin: 0 auto; padding: 0 32px 26px; font-size: 12px; color: rgba(255,255,255,0.5); }

    /* ---- Pull quote ---- */
    .rr-pull { border-left: 4px solid var(--accent-2); padding: 6px 0 6px 26px; margin: 4px 0; }
    .rr-pull p { font-size: 1.75rem; line-height: 1.32; font-weight: 700; color: var(--fg); margin: 0; }
    .rr-pull cite { display: block; font-size: 14px; font-weight: 700; font-style: normal; letter-spacing: 0.04em; text-transform: uppercase; color: rgba(var(--fg-rgb),0.6); margin-top: 16px; }

    /* ---- "What you should know" fact cards ---- */
    .rr-facts { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 30px; }
    .rr-fact { background: var(--surface); border: 1px solid rgba(var(--fg-rgb),0.12); border-left: 4px solid var(--accent-2); border-radius: 6px; padding: 22px 24px; }
    .rr-fact-num { font-size: 12px; font-weight: 800; letter-spacing: 0.12em; color: var(--accent-2); margin-bottom: 8px; }
    .rr-fact p { font-size: 15px; line-height: 1.62; color: rgba(var(--fg-rgb),0.82); margin: 0; }
    .rr-fact p strong { color: var(--fg); }

    /* ---- Timeline ---- */
    .rr-timeline { margin-top: 30px; border-left: 2px solid rgba(var(--fg-rgb),0.18); padding-left: 0; }
    .rr-tl { position: relative; padding: 0 0 34px 30px; }
    .rr-tl:last-child { padding-bottom: 0; }
    .rr-tl::before { content: ""; position: absolute; left: -7px; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: var(--accent-2); box-shadow: 0 0 0 4px var(--bg); }
    .rr-tl-year { font-size: 13px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--accent-2); margin-bottom: 6px; }
    .rr-tl-body { font-size: 16px; line-height: 1.66; color: rgba(var(--fg-rgb),0.82); }
    .rr-tl-body strong { color: var(--fg); }

    /* ---- Sources ---- */
    .rr-sources { list-style: none; margin: 22px 0 0; padding: 0; }
    .rr-sources li { border-top: 1px solid rgba(var(--fg-rgb),0.12); padding: 16px 0; }
    .rr-sources li:last-child { border-bottom: 1px solid rgba(var(--fg-rgb),0.12); }
    .rr-sources a { font-weight: 700; color: var(--fg); text-decoration: none; }
    .rr-sources a:hover { color: var(--accent-2); }
    .rr-sources span { display: block; font-size: 13px; color: rgba(var(--fg-rgb),0.6); margin-top: 4px; }
    .rr-disclaimer { margin-top: 26px; font-size: 13px; line-height: 1.6; color: rgba(var(--fg-rgb),0.5); }

    /* ---- CTA ---- */
    .rr-cta { text-align: center; padding: 84px 32px; color: var(--on-dark); }
    .rr-cta h2 { font-size: 2.4rem; font-weight: 800; color: var(--on-dark); margin: 0 0 14px; letter-spacing: -0.01em; }
    .rr-cta p { font-size: 17px; line-height: 1.65; color: rgba(255,255,255,0.85); max-width: 600px; margin: 0 auto 28px; }
    .rr-btns { display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; }
    .rr-btn { display: inline-flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 800; text-decoration: none; padding: 15px 30px; border-radius: 4px; transition: transform 0.2s, background 0.2s; }
    .rr-btn-primary { background: #fff; color: #14161d; }
    .rr-btn-primary:hover { transform: translateY(-2px); color: #000; }
    .rr-btn-ghost { background: transparent; color: var(--on-dark); border: 1px solid rgba(255,255,255,0.6); }
    .rr-btn-ghost:hover { background: rgba(255,255,255,0.14); transform: translateY(-2px); color: var(--on-dark); }

    /* ---- Responsive ---- */
    @@media (max-width: 900px) {
        .rr-banner { min-height: 380px; }
        .rr-banner--hero { min-height: 500px; }
        .rr-banner-title { font-size: 2.5rem; }
        .rr-banner--hero .rr-banner-title { font-size: 3.1rem; }
        .rr-banner-sub { font-size: 1.1rem; }
        .rr-h2 { font-size: 1.9rem; }
        .rr-stats { grid-template-columns: repeat(2, 1fr); }
        .rr-stat:nth-child(3) { border-left: 0; }
        .rr-people, .rr-facts { grid-template-columns: 1fr; }
        .rr-pull p { font-size: 1.4rem; }
    }
</style>
@endsection

@section('body')
@php
    use App\Models\Prisoner;

    // Optional internal tie-in: if Rodney Reed is in the NPPC database,
    // link his profile from the CTA. Safe no-op if absent.
    $reed = null;
    try {
        $reed = Prisoner::where('name', 'like', '%Rodney Reed%')->first(['slug', 'name']);
    } catch (\Throwable $e) {
        $reed = null;
    }
@endphp

<div class="rr">

    {{-- ==================== HERO ==================== --}}
    <section class="rr-banner rr-banner--hero">
        <div class="rr-banner-bg rr-bg-hero"></div>
        <div class="rr-banner-inner">
            <span class="rr-overline">A Case on Texas Death Row</span>
            <h1 class="rr-banner-title">Everything You Should<br>Know About Rodney Reed</h1>
            <p class="rr-banner-sub">For nearly three decades, Rodney Reed has sat on Texas death row for a murder he has always said he did not commit — while the weapon used to kill Stacey Stites has never once been tested for DNA.</p>
            <div class="rr-banner-meta">Adapted from the Innocence Project's reporting on the case of Rodney Reed.</div>
        </div>
    </section>

    {{-- ==================== LEAD ==================== --}}
    <section class="rr-section">
        <div class="rr-wrap rr-lead">
            <span class="rr-kicker">The case</span>
            <p class="rr-p">In 1998, an all-white jury in Bastrop County, Texas, convicted Rodney Reed — a Black man — of the rape and murder of Stacey Stites, a 19-year-old white woman, and sentenced him to death. The conviction rested almost entirely on forensic testimony that has since collapsed.</p>
            <p class="rr-p">In the years since, leading forensic pathologists, new witnesses, and a record of evidence withheld from the defense have all pointed away from Reed. Yet the State of Texas has fought, for more than a decade, to keep the murder weapon and other crime-scene evidence from ever being DNA tested. This is what you should know.</p>
        </div>
    </section>

    {{-- ==================== STATS BAND ==================== --}}
    <div class="rr-stats-band">
        <div class="rr-stats">
            <div class="rr-stat"><div class="rr-stat-num">~30</div><div class="rr-stat-label">Years Rodney Reed has spent on Texas death row</div></div>
            <div class="rr-stat"><div class="rr-stat-num">0</div><div class="rr-stat-label">Times the belt used to strangle Stacey Stites has been DNA tested</div></div>
            <div class="rr-stat"><div class="rr-stat-num">1998</div><div class="rr-stat-label">Year an all-white jury convicted him and sentenced him to death</div></div>
            <div class="rr-stat"><div class="rr-stat-num">6&ndash;3</div><div class="rr-stat-label">Supreme Court vote affirming his right to seek DNA testing (2023)</div></div>
        </div>
        <div class="rr-stats-src">Figures drawn from the Innocence Project case file and public court records.</div>
    </div>

    {{-- ==================== WHO ==================== --}}
    <section class="rr-banner">
        <div class="rr-banner-bg rr-bg-slate"></div>
        <div class="rr-banner-inner">
            <span class="rr-overline">Who They Are</span>
            <h2 class="rr-banner-title">Two People, One Night</h2>
        </div>
    </section>
    <section class="rr-section">
        <div class="rr-wrap">
            <div class="rr-people">
                <div class="rr-person">
                    <h3>Rodney Reed</h3>
                    <p class="rr-person-role">Convicted &amp; sentenced to death</p>
                    <p>Born in 1967 and raised in Bastrop, Texas, Rodney Reed was 28 when he was arrested. He has maintained from the beginning that he and Stacey Stites were involved in a private, consensual relationship — a claim he says explains the DNA evidence used to convict him. He remains on death row at the Allan B. Polunsky Unit.</p>
                </div>
                <div class="rr-person">
                    <h3>Stacey Stites</h3>
                    <p class="rr-person-role">Victim, 19 years old</p>
                    <p>Stacey Stites was a 19-year-old who worked in the produce section of an H-E-B grocery store in Bastrop. She lived with her fiancé, local police officer Jimmy Fennell Jr., and was weeks away from their wedding when she was found dead on April 23, 1996, strangled with her own belt.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== THE CONVICTION ==================== --}}
    <section class="rr-section rr-section--tint">
        <div class="rr-wrap">
            <span class="rr-kicker">The conviction</span>
            <h2 class="rr-h2">A case built on a forensic claim that fell apart</h2>
            <p class="rr-p">The prosecution argued that Stites was abducted and killed between 3 and 5 a.m., and that the only sperm found in her body must have been deposited at the time of the murder. Because that sperm was matched to Rodney Reed, the State told the jury, Reed had to be the killer.</p>
            <p class="rr-p">That single inference carried the case. But the science behind it was wrong. The same kind of forensic testimony has since been repudiated — including by experts whose work the State relied on — and the timeline the prosecution built on top of it no longer holds.</p>
            <div class="rr-pull">
                <p>&ldquo;The expert testimony used to convict Rodney Reed was false, and the evidence is consistent with his account of a consensual relationship.&rdquo;</p>
                <cite>&mdash; The Innocence Project</cite>
            </div>
        </div>
    </section>

    {{-- ==================== THE TIMELINE THAT COLLAPSED ==================== --}}
    <section class="rr-section">
        <div class="rr-wrap">
            <span class="rr-kicker">The forensics</span>
            <h2 class="rr-h2">When she actually died</h2>
            <p class="rr-p">The prosecution's whole theory depended on Stites being alive and in Reed's hands in the early hours of April 23. But renowned forensic pathologists who later reviewed the evidence concluded the opposite: that Stites was most likely killed <strong>hours earlier, before midnight on April 22</strong> — a window during which she was in the sole company of her fiancé.</p>
            <p class="rr-p">Several of the state's own experts signed affidavits acknowledging errors in their original time-of-death testimony. Multiple leading pathologists concluded that the timeline used to convict Reed is medically and scientifically untenable.</p>
        </div>
    </section>

    {{-- ==================== THE OTHER SUSPECT ==================== --}}
    <section class="rr-banner">
        <div class="rr-banner-bg rr-bg-crimson"></div>
        <div class="rr-banner-inner">
            <span class="rr-overline">The First Suspect</span>
            <h2 class="rr-banner-title">Jimmy Fennell</h2>
        </div>
    </section>
    <section class="rr-section">
        <div class="rr-wrap">
            <span class="rr-kicker rr-kicker--crimson">An alternative the jury never fully heard</span>
            <p class="rr-p">For months after the murder, the prime suspect was not Rodney Reed but <strong>Jimmy Fennell Jr.</strong>, Stites's fiancé and a local police officer. Investigators noted inconsistencies in his account of the night she died.</p>
            <p class="rr-p">Years later, Fennell was convicted of kidnapping and sexually assaulting a woman while on duty as a police officer, and served roughly a decade in prison. After Reed's conviction, multiple witnesses came forward: a former insurance colleague who said Fennell threatened to kill Stites; a mourner who described an incriminating remark at her funeral; and a former prison associate of Fennell's who swore in an affidavit that Fennell had confessed to the killing behind bars.</p>
            <div class="rr-pull">
                <p>&ldquo;For months, the prime suspect was not Rodney Reed. It was the last person to admit seeing Stacey Stites alive.&rdquo;</p>
            </div>
        </div>
    </section>

    {{-- ==================== WITHHELD EVIDENCE ==================== --}}
    <section class="rr-section rr-section--tint">
        <div class="rr-wrap">
            <span class="rr-kicker">What the jury didn't know</span>
            <h2 class="rr-h2">Evidence that never reached the defense</h2>
            <p class="rr-p">At trial, prosecutors told the jury that no one could confirm any relationship between Reed and Stites. Years later, it emerged that <strong>coworkers had given statements saying the two knew each other and appeared close</strong> — statements that were never turned over to Reed's defense.</p>
            <p class="rr-p">The Innocence Project has documented a broader pattern of favorable evidence withheld for more than two decades — the kind of suppression that, on its own, can undermine the integrity of a conviction.</p>
        </div>
    </section>

    {{-- ==================== THE FIGHT FOR DNA TESTING ==================== --}}
    <section class="rr-banner">
        <div class="rr-banner-bg rr-bg-indigo"></div>
        <div class="rr-banner-inner">
            <span class="rr-overline">The Central Demand</span>
            <h2 class="rr-banner-title">Test the Belt</h2>
        </div>
    </section>
    <section class="rr-section">
        <div class="rr-wrap">
            <p class="rr-p">Stacey Stites was strangled with a belt. That belt — the murder weapon — <strong>has never been tested for DNA.</strong> For more than a decade, Rodney Reed has asked Texas courts to test it, along with other crime-scene evidence. The State has fought every request.</p>
            <p class="rr-p">In 2023, the U.S. Supreme Court ruled 6&ndash;3 that Reed's federal lawsuit seeking that testing was filed on time, allowing his challenge to proceed. The fundamental question — what modern DNA testing of the weapon would actually show — remains, to this day, unanswered.</p>
        </div>
    </section>

    {{-- ==================== TIMELINE ==================== --}}
    <section class="rr-section rr-section--tint">
        <div class="rr-wrap">
            <span class="rr-kicker">Timeline</span>
            <h2 class="rr-h2">Thirty years, in brief</h2>
            <div class="rr-timeline">
                <div class="rr-tl">
                    <div class="rr-tl-year">April 1996</div>
                    <div class="rr-tl-body">Stacey Stites, 19, is found dead behind a Bastrop high school, strangled with her own belt. Suspicion first falls on her fiancé, police officer <strong>Jimmy Fennell</strong>.</div>
                </div>
                <div class="rr-tl">
                    <div class="rr-tl-year">1997</div>
                    <div class="rr-tl-body"><strong>Rodney Reed</strong> is charged after his DNA is matched to sperm recovered from Stites. Reed says the two were in a secret consensual relationship.</div>
                </div>
                <div class="rr-tl">
                    <div class="rr-tl-year">May 1998</div>
                    <div class="rr-tl-body">An all-white jury convicts Reed and sentences him to death, relying on testimony that the sperm was deposited at the moment of the murder.</div>
                </div>
                <div class="rr-tl">
                    <div class="rr-tl-year">November 2019</div>
                    <div class="rr-tl-body">Days before a scheduled November 20 execution, the Texas Board of Pardons and Paroles unanimously recommends a reprieve and the Court of Criminal Appeals halts the execution to weigh new evidence.</div>
                </div>
                <div class="rr-tl">
                    <div class="rr-tl-year">2021</div>
                    <div class="rr-tl-body">A two-week evidentiary hearing airs testimony that Fennell confessed and that the State's forensic timeline was impossible.</div>
                </div>
                <div class="rr-tl">
                    <div class="rr-tl-year">April 2023</div>
                    <div class="rr-tl-body">The U.S. Supreme Court rules <strong>6&ndash;3</strong> that Reed may pursue his federal suit seeking DNA testing of the never-tested murder weapon.</div>
                </div>
                <div class="rr-tl">
                    <div class="rr-tl-year">2026</div>
                    <div class="rr-tl-body">The Supreme Court declines to take up Reed's latest due-process challenge to the district attorney's refusal of DNA testing. The belt has still never been tested. Reed remains on death row.</div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== WHAT YOU SHOULD KNOW ==================== --}}
    <section class="rr-section">
        <div class="rr-wrap">
            <span class="rr-kicker">What you should know</span>
            <h2 class="rr-h2">The facts at the heart of the case</h2>
            <div class="rr-facts">
                <div class="rr-fact"><div class="rr-fact-num">01</div><p>The belt used as the <strong>murder weapon has never been DNA tested</strong>, despite years of requests.</p></div>
                <div class="rr-fact"><div class="rr-fact-num">02</div><p>State forensic experts have <strong>acknowledged errors</strong> in their time-of-death testimony, making the prosecution's timeline implausible.</p></div>
                <div class="rr-fact"><div class="rr-fact-num">03</div><p>New witnesses, including Stites's cousin and a coworker, corroborate that Reed and Stites were in a <strong>consensual relationship</strong>.</p></div>
                <div class="rr-fact"><div class="rr-fact-num">04</div><p>Renowned forensic pathologists concluded that <strong>Reed's guilt is scientifically untenable</strong> on the available evidence.</p></div>
                <div class="rr-fact"><div class="rr-fact-num">05</div><p><strong>Jimmy Fennell</strong>, Stites's fiancé, was the prime suspect for months and gave an inconsistent account of that night.</p></div>
                <div class="rr-fact"><div class="rr-fact-num">06</div><p>Fennell later served a <strong>10-year prison term</strong> for kidnapping and sexually assaulting a woman while on duty as an officer.</p></div>
                <div class="rr-fact"><div class="rr-fact-num">07</div><p>A former prison associate swore that Fennell <strong>confessed to the murder</strong> behind bars.</p></div>
                <div class="rr-fact"><div class="rr-fact-num">08</div><p>Coworker statements that Reed and Stites knew each other were <strong>withheld from the defense</strong> at trial.</p></div>
                <div class="rr-fact"><div class="rr-fact-num">09</div><p>Reed, a Black man, was convicted of murdering a white woman by an <strong>all-white jury</strong> in 1990s Texas.</p></div>
                <div class="rr-fact"><div class="rr-fact-num">10</div><p>In 2023 the U.S. Supreme Court ruled <strong>6&ndash;3</strong> that Reed has the right to pursue DNA testing in federal court.</p></div>
            </div>
        </div>
    </section>

    {{-- ==================== CTA ==================== --}}
    <section class="rr-banner rr-cta-banner" style="min-height:0;">
        <div class="rr-banner-bg rr-bg-ink"></div>
        <div class="rr-cta" style="position:relative; z-index:1; width:100%; max-width:1080px; margin:0 auto;">
            <h2>The weapon has never been tested. The fight isn't over.</h2>
            <p>Rodney Reed's case is one of many where the simplest path to the truth — testing the evidence — has been blocked for decades. Learn more, and stand with those fighting for it.</p>
            <div class="rr-btns">
                <a class="rr-btn rr-btn-primary" href="https://innocenceproject.org/petitions/justice-for-rodney-reed/" target="_blank" rel="noopener">Sign the Innocence Project petition</a>
                @if($reed)
                    <a class="rr-btn rr-btn-ghost" href="/prisoner/{{ $reed->slug }}">Rodney Reed's NPPC profile</a>
                @else
                    <a class="rr-btn rr-btn-ghost" href="/get-involved">Get involved with NPPC</a>
                @endif
            </div>
        </div>
    </section>

    {{-- ==================== SOURCES ==================== --}}
    <section class="rr-section">
        <div class="rr-wrap">
            <span class="rr-kicker">Sources &amp; further reading</span>
            <h2 class="rr-h2">Where this comes from</h2>
            <ul class="rr-sources">
                <li>
                    <a href="https://interactive.innocenceproject.org/rodney-reed/" target="_blank" rel="noopener">Everything You Should Know About Rodney Reed</a>
                    <span>The Innocence Project — the interactive photo essay this feature is adapted from.</span>
                </li>
                <li>
                    <a href="https://innocenceproject.org/news/10-facts-you-need-to-know-about-rodney-reed-who-is-scheduled-for-execution-on-november-20/" target="_blank" rel="noopener">10 Facts About Rodney Reed's Case You Need to Know</a>
                    <span>The Innocence Project.</span>
                </li>
                <li>
                    <a href="https://innocenceproject.org/the-u-s-supreme-court-rules-6-3-in-favor-of-rodney-reed/" target="_blank" rel="noopener">The U.S. Supreme Court Rules 6&ndash;3 in Favor of Rodney Reed</a>
                    <span>The Innocence Project, on Reed v. Goertz (2023).</span>
                </li>
                <li>
                    <a href="https://en.wikipedia.org/wiki/Rodney_Reed" target="_blank" rel="noopener">Rodney Reed</a>
                    <span>Wikipedia — case background and chronology.</span>
                </li>
            </ul>
            <p class="rr-disclaimer">This page is an independent recreation by the National Political Prisoner Coalition of the Innocence Project's feature on Rodney Reed, built to make the case accessible to NPPC's readers. All credit for the underlying reporting and case work belongs to the Innocence Project and Reed's legal team. For the authoritative and most current account, follow the links above.</p>
        </div>
    </section>

</div>
@endsection
