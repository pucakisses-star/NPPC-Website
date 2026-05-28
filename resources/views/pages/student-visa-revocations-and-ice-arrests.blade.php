@extends('app')

@section('title', "Detained for Dissent — Student Visa Revocations & ICE Arrests | NPPC")

@section('head')
<meta name="description" content="A briefing on the 2025 U.S. crackdown on international students — the targeted ICE arrests of student activists, the mass visa revocations and SEVIS terminations, and the courts' response — compiled from reporting by the Associated Press, CNN, NPR, and Inside Higher Ed, the work of the ACLU and the Knight First Amendment Institute, and court filings.">
<style>
    /* ============================================================
       Student Visa Revocations & ICE Arrests — hand-crafted briefing.
       Long-form report layout in the NPPC briefing series. All classes
       are scoped with the svr- prefix so nothing leaks into the rest
       of the site. Amber accent distinguishes it from the Iran page.
       ============================================================ */
    .svr { background: #0b0b0d; color: #fff; }
    .svr-serif { font-family: Georgia, 'Times New Roman', Times, serif; }
    .svr a { color: #e0a23c; }
    .svr a:hover { color: #fff; }

    /* ---- Hero ---- */
    .svr-hero { position: relative; overflow: hidden; background: #000; min-height: 620px; display: flex; align-items: flex-end; }
    .svr-hero-bg { position: absolute; inset: 0; z-index: 0; }
    .svr-hero-bg svg { width: 100%; height: 100%; display: block; }
    .svr-hero-overlay { position: absolute; inset: 0; z-index: 1;
        background: radial-gradient(120% 90% at 72% 0%, rgba(200,134,42,0.16), transparent 60%),
                    linear-gradient(180deg, rgba(11,11,13,0.35) 0%, rgba(11,11,13,0.72) 55%, #0b0b0d 100%); }
    .svr-hero-content { position: relative; z-index: 2; max-width: 900px; margin: 0 auto; width: 100%; padding: 120px 24px 56px; }
    .svr-kicker { display: inline-flex; align-items: center; gap: 12px; font-size: 12px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: #e0a23c; margin-bottom: 22px; }
    .svr-kicker::before { content: ""; width: 34px; height: 2px; background: #c8862a; display: inline-block; }
    .svr-hero-title { font-size: 4.6rem; line-height: 0.98; font-weight: 700; color: #fff; margin: 0 0 18px; letter-spacing: -0.02em; }
    .svr-hero-sub { font-size: 1.4rem; line-height: 1.4; color: rgba(255,255,255,0.78); max-width: 720px; margin: 0 0 28px; }
    .svr-hero-meta { display: flex; flex-wrap: wrap; gap: 10px 18px; align-items: center; font-size: 13px; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: rgba(255,255,255,0.55); }
    .svr-hero-meta span { display: inline-flex; align-items: center; }
    .svr-hero-meta span + span::before { content: ""; width: 4px; height: 4px; border-radius: 50%; background: #c8862a; margin-right: 18px; }

    /* ---- Layout primitives ---- */
    .svr-wrap { max-width: 820px; margin: 0 auto; padding: 0 24px; }
    .svr-section { padding: 72px 0; border-top: 1px solid rgba(255,255,255,0.08); }
    .svr-section:first-of-type { border-top: 0; }
    .svr-eyebrow { display: flex; align-items: center; gap: 12px; font-size: 12px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: #e0a23c; margin-bottom: 18px; }
    .svr-eyebrow .svr-num { font-family: Georgia, serif; color: rgba(255,255,255,0.4); }
    .svr-h2 { font-size: 2.6rem; line-height: 1.1; font-weight: 700; color: #fff; margin: 0 0 24px; }
    .svr-h3 { font-size: 1.5rem; font-weight: 700; color: #fff; margin: 36px 0 14px; }
    .svr-p { font-size: 17px; line-height: 1.85; color: rgba(255,255,255,0.76); margin: 0 0 1.4em; }
    .svr-p strong { color: #fff; font-weight: 700; }
    .svr-cite { font-size: 13px; color: rgba(255,255,255,0.45); }
    .svr-cite a { color: rgba(255,255,255,0.55); text-decoration: underline; }
    .svr-cite a:hover { color: #e0a23c; }

    /* ---- Lead ---- */
    .svr-lead .svr-p { font-family: Georgia, 'Times New Roman', serif; font-size: 1.45rem; line-height: 1.5; color: #fff; }
    .svr-lead .svr-p:first-child::first-letter { float: left; font-size: 4.2em; line-height: 0.72; padding: 0.05em 0.12em 0 0; color: #c8862a; font-weight: 700; }

    /* ---- Stats ---- */
    .svr-stats-band { background: #000; border-top: 1px solid rgba(255,255,255,0.08); border-bottom: 1px solid rgba(255,255,255,0.08); }
    .svr-stats { max-width: 1080px; margin: 0 auto; padding: 8px 24px; display: grid; grid-template-columns: repeat(4, 1fr); }
    .svr-stat { padding: 48px 24px; border-left: 1px solid rgba(255,255,255,0.08); }
    .svr-stat:first-child { border-left: 0; }
    .svr-stat-num { font-family: Georgia, serif; font-size: 3.4rem; line-height: 1; font-weight: 700; color: #e0a23c; letter-spacing: -0.02em; }
    .svr-stat-num small { font-size: 0.45em; color: rgba(255,255,255,0.45); }
    .svr-stat-label { margin-top: 14px; font-size: 15px; line-height: 1.5; color: rgba(255,255,255,0.72); }
    .svr-stat-src { margin-top: 10px; font-size: 12px; color: rgba(255,255,255,0.4); }

    /* ---- Pull quote ---- */
    .svr-pull { border-left: 3px solid #c8862a; padding: 6px 0 6px 28px; margin: 8px 0; }
    .svr-pull p { font-family: Georgia, 'Times New Roman', serif; font-size: 2rem; line-height: 1.3; color: #fff; margin: 0 0 14px; }
    .svr-pull cite { font-style: normal; font-size: 14px; letter-spacing: 0.04em; color: rgba(255,255,255,0.5); text-transform: uppercase; }

    /* ---- Pattern cards ---- */
    .svr-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 36px; }
    .svr-card { background: #111; border: 1px solid rgba(255,255,255,0.08); border-radius: 6px; padding: 26px; transition: border-color 0.25s, transform 0.25s; }
    .svr-card:hover { border-color: rgba(200,134,42,0.4); transform: translateY(-3px); }
    .svr-card-icon { color: #c8862a; margin-bottom: 14px; }
    .svr-card h3 { font-size: 1.15rem; font-weight: 800; color: #fff; margin: 0 0 8px; }
    .svr-card p { font-size: 14px; line-height: 1.6; color: rgba(255,255,255,0.62); margin: 0; }

    /* ---- Named cases ---- */
    .svr-case { display: grid; grid-template-columns: 56px 1fr; gap: 20px; align-items: start; padding: 26px 0; border-top: 1px solid rgba(255,255,255,0.08); }
    .svr-case:last-child { border-bottom: 1px solid rgba(255,255,255,0.08); }
    .svr-avatar { width: 56px; height: 56px; border-radius: 50%; background: #1a1a1f; border: 1px solid rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; font-family: Georgia, serif; font-weight: 700; font-size: 1.05rem; color: #e0a23c; }
    .svr-case h3 { font-size: 1.25rem; font-weight: 800; color: #fff; margin: 0 0 2px; }
    .svr-case-role { font-size: 12px; letter-spacing: 0.05em; text-transform: uppercase; color: rgba(255,255,255,0.45); margin: 0 0 10px; }
    .svr-case p { font-size: 15px; line-height: 1.65; color: rgba(255,255,255,0.72); margin: 0; }
    .svr-tag { display: inline-block; font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; padding: 5px 10px; border-radius: 999px; margin-top: 12px; }
    .svr-tag-risk { background: rgba(200,134,42,0.15); color: #e0a23c; border: 1px solid rgba(200,134,42,0.4); }
    .svr-tag-free { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.2); }

    /* ---- Sources & note ---- */
    .svr-note { background: #111; border: 1px solid rgba(255,255,255,0.08); border-radius: 6px; padding: 28px; margin-bottom: 32px; }
    .svr-note p { font-size: 15px; line-height: 1.7; color: rgba(255,255,255,0.72); margin: 0 0 12px; }
    .svr-note p:last-child { margin: 0; }
    .svr-note strong { color: #fff; }
    .svr-sources { list-style: none; margin: 0; padding: 0; }
    .svr-sources li { border-top: 1px solid rgba(255,255,255,0.08); padding: 16px 0; }
    .svr-sources li:last-child { border-bottom: 1px solid rgba(255,255,255,0.08); }
    .svr-sources a { font-weight: 700; color: #fff; text-decoration: none; }
    .svr-sources a:hover { color: #e0a23c; }
    .svr-sources span { display: block; font-size: 13px; color: rgba(255,255,255,0.4); margin-top: 4px; }

    /* ---- CTA ---- */
    .svr-cta { background: linear-gradient(135deg, #6e4f16, #3f2c0c); text-align: center; padding: 80px 24px; }
    .svr-cta h2 { font-family: Georgia, serif; font-size: 2.6rem; font-weight: 700; color: #fff; margin: 0 0 16px; }
    .svr-cta p { font-size: 17px; line-height: 1.7; color: rgba(255,255,255,0.88); max-width: 600px; margin: 0 auto 30px; }
    .svr-btns { display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; }
    .svr-btn { display: inline-flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 700; text-decoration: none; padding: 15px 30px; border-radius: 999px; transition: transform 0.2s, background 0.2s; }
    .svr-btn-primary { background: #fff; color: #1a1a1a; }
    .svr-btn-primary:hover { transform: translateY(-2px); color: #000; }
    .svr-btn-ghost { background: transparent; color: #fff; border: 1px solid rgba(255,255,255,0.55); }
    .svr-btn-ghost:hover { background: rgba(255,255,255,0.12); transform: translateY(-2px); color: #fff; }

    /* ---- Responsive ---- */
    @@media (max-width: 900px) {
        .svr-hero { min-height: 500px; }
        .svr-hero-title { font-size: 3rem; }
        .svr-hero-sub { font-size: 1.15rem; }
        .svr-h2 { font-size: 2.1rem; }
        .svr-stats { grid-template-columns: 1fr 1fr; }
        .svr-stat { border-left: 0; border-top: 1px solid rgba(255,255,255,0.08); padding: 32px 16px; }
        .svr-stat:first-child, .svr-stat:nth-child(2) { border-top: 0; }
        .svr-cards { grid-template-columns: 1fr 1fr; }
        .svr-pull p { font-size: 1.6rem; }
    }
    @@media (max-width: 600px) {
        .svr-hero-content { padding: 100px 20px 40px; }
        .svr-hero-title { font-size: 2.3rem; }
        .svr-h2 { font-size: 1.8rem; }
        .svr-lead .svr-p { font-size: 1.2rem; }
        .svr-stats { grid-template-columns: 1fr; }
        .svr-stat, .svr-stat:nth-child(2) { border-left: 0; border-top: 1px solid rgba(255,255,255,0.08); }
        .svr-stat:first-child { border-top: 0; }
        .svr-cards { grid-template-columns: 1fr; }
        .svr-cta h2 { font-size: 2rem; }
    }
</style>
@endsection

@section('body')
<div class="svr">

    {{-- ==================== HERO ==================== --}}
    <div class="svr-hero">
        <div class="svr-hero-bg" aria-hidden="true">
            <svg viewBox="0 0 1200 640" preserveAspectRatio="xMidYMid slice">
                <defs>
                    <linearGradient id="svrSky" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#1b1b22"/>
                        <stop offset="1" stop-color="#0b0b0d"/>
                    </linearGradient>
                </defs>
                <rect width="1200" height="640" fill="url(#svrSky)"/>
                {{-- faint document text lines --}}
                <g stroke="#23232b" stroke-width="6" stroke-linecap="round">
                    <line x1="80" y1="170" x2="540" y2="170"/>
                    <line x1="80" y1="212" x2="470" y2="212"/>
                    <line x1="80" y1="254" x2="560" y2="254"/>
                    <line x1="80" y1="296" x2="430" y2="296"/>
                    <line x1="80" y1="338" x2="520" y2="338"/>
                    <line x1="80" y1="380" x2="380" y2="380"/>
                    <line x1="80" y1="422" x2="500" y2="422"/>
                    <line x1="80" y1="464" x2="440" y2="464"/>
                </g>
                {{-- revocation stamp --}}
                <g transform="rotate(-13 880 300)" fill="none" stroke="#c8862a" opacity="0.5">
                    <rect x="710" y="208" width="330" height="156" rx="14" stroke-width="8"/>
                    <rect x="732" y="230" width="286" height="112" rx="8" stroke-width="3"/>
                </g>
                {{-- stamp ring --}}
                <g fill="none" stroke="#c8862a" opacity="0.26">
                    <circle cx="1024" cy="150" r="72" stroke-width="6"/>
                    <circle cx="1024" cy="150" r="54" stroke-width="2"/>
                </g>
            </svg>
        </div>
        <div class="svr-hero-overlay" aria-hidden="true"></div>
        <div class="svr-hero-content">
            <span class="svr-kicker">A briefing on student visa revocations &amp; ICE arrests</span>
            <h1 class="svr-hero-title svr-serif">Detained for Dissent</h1>
            <p class="svr-hero-sub">In the spring of 2025, the United States jailed student activists over their speech and stripped the legal status of thousands of others — turning immigration enforcement on its own campuses.</p>
            <div class="svr-hero-meta">
                <span>Briefing</span><span>Spring 2025</span><span>NPPC</span>
            </div>
        </div>
    </div>

    {{-- ==================== LEAD ==================== --}}
    <div class="svr-section svr-lead">
        <div class="svr-wrap">
            <p class="svr-p">In the spring of 2025, the federal government moved against international students on two fronts at once. On one, masked agents arrested and jailed a handful of student activists — green-card holders and visa students alike — over op-eds, protests, and campus organizing. On the other, the government quietly canceled the legal status of thousands more, running student names through criminal databases and switching off their records with little notice and, in most cases, no connection to any protest at all.</p>
            <p class="svr-p">This briefing draws together reporting by the Associated Press, CNN, NPR, and Inside Higher Ed, along with court filings and the work of the ACLU, the Knight First Amendment Institute, and the Presidents' Alliance on Higher Education and Immigration, to set out what happened — and to put names to the people swept up in it.</p>
        </div>
    </div>

    {{-- ==================== STATS ==================== --}}
    <div class="svr-stats-band">
        <div class="svr-stats">
            <div class="svr-stat">
                <div class="svr-stat-num">300<small>+</small></div>
                <div class="svr-stat-label">student visas the Secretary of State said had already been revoked &mdash; by late March.</div>
                <div class="svr-stat-src">Marco Rubio, March 2025</div>
            </div>
            <div class="svr-stat">
                <div class="svr-stat-num">~4,700</div>
                <div class="svr-stat-label">student records terminated in the federal SEVIS system by early May, across 40+ states.</div>
                <div class="svr-stat-src">Presidents' Alliance · NAFSA</div>
            </div>
            <div class="svr-stat">
                <div class="svr-stat-num">280<small>+</small></div>
                <div class="svr-stat-label">colleges and universities where students abruptly lost their status.</div>
                <div class="svr-stat-src">Inside Higher Ed, April 2025</div>
            </div>
            <div class="svr-stat">
                <div class="svr-stat-num">100<small>+</small></div>
                <div class="svr-stat-label">lawsuits filed; judges ordered records restored in dozens of them.</div>
                <div class="svr-stat-src">Inside Higher Ed · NPR</div>
            </div>
        </div>
    </div>

    {{-- ==================== CONTEXT ==================== --}}
    <div class="svr-section">
        <div class="svr-wrap">
            <div class="svr-eyebrow"><span class="svr-num">01</span> Two tracks</div>
            <h2 class="svr-h2 svr-serif">A campaign on two fronts</h2>
            <p class="svr-p">Within days of the January 2025 inauguration, the administration signaled it would use immigration law against campus protest. A January 29 executive order on combating antisemitism (Executive Order 14188) directed agencies to find and remove noncitizens tied to post&ndash;October 7 demonstrations. By early March, officials described a State Department effort &mdash; reported as <strong>"Catch and Revoke"</strong> &mdash; that would use AI to scan tens of thousands of visa holders' social-media accounts for apparent support of Hamas. <span class="svr-cite">(Axios; Inside Higher Ed)</span></p>
            <p class="svr-p">What followed ran on two tracks. The first was loud and individual: a small number of high-profile arrests of student organizers, justified by a rarely used clause of immigration law that lets the Secretary of State declare a noncitizen's very presence a foreign-policy problem. The second was quiet and vast: the mass termination of student records in <strong>SEVIS</strong>, the federal database that tracks international students &mdash; most of it driven not by protest, but by hits against a criminal-records database for minor or long-closed matters.</p>
        </div>
    </div>

    {{-- ==================== PULL QUOTE ==================== --}}
    <div class="svr-section">
        <div class="svr-wrap">
            <blockquote class="svr-pull">
                <p>We do it every day. Every time I find one of these lunatics, I take away their visa.</p>
                <cite>&mdash; Secretary of State Marco Rubio, March 27, 2025</cite>
            </blockquote>
        </div>
    </div>

    {{-- ==================== ARRESTS ==================== --}}
    <div class="svr-section">
        <div class="svr-wrap">
            <div class="svr-eyebrow"><span class="svr-num">02</span> Arrested for what they said</div>
            <h2 class="svr-h2 svr-serif">Jailed over op-eds and protests</h2>
            <p class="svr-p">Beginning with the arrest of Columbia graduate <strong>Mahmoud Khalil</strong> on March 8, federal agents detained a series of students and scholars who had taken part in pro-Palestinian advocacy. None was charged with a crime. Instead, the government leaned on a seldom-used provision of the Immigration and Nationality Act &mdash; <strong>&sect;237(a)(4)(C)</strong> (8 U.S.C. &sect;1227(a)(4)(C)) &mdash; which permits deportation when the Secretary of State has "reasonable ground to believe" a person's presence would carry "potentially serious adverse foreign policy consequences." <span class="svr-cite">(NBC News; The Hill; NPR)</span></p>
            <p class="svr-p">In Khalil's case, the government's evidence was a <strong>two-page memo</strong> from Secretary Rubio asserting that his "participation and roles in antisemitic protests and disruptive activities" undercut U.S. foreign policy. Several of those detained were flown far from their homes, lawyers, and courts &mdash; to immigration facilities in <strong>Louisiana and Texas</strong> &mdash; within hours of arrest.</p>
        </div>
    </div>

    {{-- ==================== THE PURGE ==================== --}}
    <div class="svr-section">
        <div class="svr-wrap">
            <div class="svr-eyebrow"><span class="svr-num">03</span> The quiet purge</div>
            <h2 class="svr-h2 svr-serif">Thousands stripped of status, by database</h2>
            <p class="svr-p">Away from the cameras, a far larger number of students simply found their legal status gone. Estimates climbed through the spring: <strong>more than 1,000</strong> by mid-April, <span class="svr-cite">(<a href="https://www.cnn.com/2025/04/17/us/university-international-student-visas-revoked" target="_blank" rel="noopener">CNN</a>)</span> nearly <strong>2,000 across 280+ institutions</strong> by late April, <span class="svr-cite">(<a href="https://www.insidehighered.com/news/global/international-students-us/2025/04/25/ice-reverses-course-sevis-terminations" target="_blank" rel="noopener">Inside Higher Ed</a>)</span> and roughly <strong>4,700 SEVIS terminations</strong> across more than 40 states by early May. <span class="svr-cite">(Presidents' Alliance; NAFSA)</span> The figures differ because they count different things &mdash; visa revocations, database terminations, and unique students &mdash; and because the number kept growing.</p>
            <p class="svr-p">According to Associated Press reporting tied to the <em>AAUP v. Rubio</em> litigation, the surge was generated by a Department of Homeland Security effort that ran roughly <strong>1.3 million</strong> foreign-student names through the FBI's National Crime Information Center (NCIC) database and flagged about <strong>6,400</strong> with any law-enforcement encounter. <span class="svr-cite">(Associated Press)</span> Reporting and court filings showed the great majority of terminations were tied to <strong>minor or dismissed matters</strong> &mdash; a years-old traffic stop, a dropped charge, an infraction that never led to conviction &mdash; and some students had no record at all. The government later conceded it would not terminate a record "solely based on the NCIC finding."</p>

            <div class="svr-cards">
                <div class="svr-card">
                    <div class="svr-card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2 4 6v6c0 5 3.5 8 8 10 4.5-2 8-5 8-10V6z"/><path d="m9 12 2 2 4-4"/></svg></div>
                    <h3>Foreign-policy deportation</h3>
                    <p>A rarely used clause letting the Secretary of State declare a noncitizen's presence a foreign-policy threat &mdash; the basis for the marquee arrests.</p>
                </div>
                <div class="svr-card">
                    <div class="svr-card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/></svg></div>
                    <h3>"Catch and Revoke"</h3>
                    <p>An AI-assisted State Department review of visa holders' social-media accounts for apparent support of Hamas.</p>
                </div>
                <div class="svr-card">
                    <div class="svr-card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18M8 14h8"/></svg></div>
                    <h3>The NCIC dragnet</h3>
                    <p>~1.3 million student names run through an FBI criminal database; ~6,400 flagged &mdash; mostly for minor or dismissed matters.</p>
                </div>
                <div class="svr-card">
                    <div class="svr-card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 3h14v18l-7-4-7 4z"/><path d="M9 8h6"/></svg></div>
                    <h3>SEVIS termination</h3>
                    <p>Status records switched off without warning, ending work authorization and lawful presence overnight.</p>
                </div>
                <div class="svr-card">
                    <div class="svr-card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-7-5-7-11a7 7 0 0 1 14 0c0 6-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></div>
                    <h3>Detention far from home</h3>
                    <p>Detainees moved within hours to remote facilities in Louisiana and Texas, far from their lawyers and courts.</p>
                </div>
                <div class="svr-card">
                    <div class="svr-card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 19H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h5"/><path d="m14 8 4 4-4 4M18 12H8"/></svg></div>
                    <h3>Self-deportation</h3>
                    <p>Facing arrest, other students abandoned degrees and left the country &mdash; the chilling effect, by design.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== COURTS ==================== --}}
    <div class="svr-section">
        <div class="svr-wrap">
            <div class="svr-eyebrow"><span class="svr-num">04</span> The courts push back</div>
            <h2 class="svr-h2 svr-serif">Judges intervene &mdash; and a U-turn</h2>
            <p class="svr-p">The two tracks met the same obstacle: federal judges. In the detention cases, courts in Vermont, New Jersey, and Virginia found that the arrests likely punished protected speech or rested on no evidence, and ordered the students released one after another &mdash; <strong>Mohsen Mahdawi</strong> on April 30, <strong>Rümeysa Öztürk</strong> on May 9, <strong>Badar Khan Suri</strong> on May 14, and <strong>Mahmoud Khalil</strong> on bail in June. A New York judge barred the government from detaining <strong>Yunseo Chung</strong> at all.</p>
            <p class="svr-p">In the SEVIS cases, more than <strong>100 lawsuits</strong> produced a cascade of restraining orders. On <strong>April 25, 2025</strong>, the government abruptly reversed course, telling courts it would <strong>restore the terminated records</strong> nationwide while it wrote a formal policy. <span class="svr-cite">(<a href="https://www.insidehighered.com/news/global/international-students-us/2025/04/25/ice-reverses-course-sevis-terminations" target="_blank" rel="noopener">Inside Higher Ed</a>)</span> Days later, a draft ICE framework surfaced in a filing that broadened the stated grounds for future terminations &mdash; including treating a visa revocation as itself a basis to end status &mdash; which advocates warned set up a second round. Meanwhile the broader challenge to the "ideological deportation" policy, <em>AAUP v. Rubio</em> &mdash; brought March 25 by the Knight First Amendment Institute with the AAUP and the Middle East Studies Association &mdash; headed toward a federal trial. <span class="svr-cite">(<a href="https://knightcolumbia.org/cases/aaup-v-rubio" target="_blank" rel="noopener">Knight First Amendment Institute</a>)</span></p>
        </div>
    </div>

    {{-- ==================== NAMED CASES ==================== --}}
    <div class="svr-section">
        <div class="svr-wrap">
            <div class="svr-eyebrow"><span class="svr-num">05</span> The faces behind the numbers</div>
            <h2 class="svr-h2 svr-serif">Names, not statistics</h2>
            <p class="svr-p">A few of the documented cases from the spring of 2025:</p>

            <div class="svr-case">
                <div class="svr-avatar" aria-hidden="true">MK</div>
                <div>
                    <h3>Mahmoud Khalil</h3>
                    <p class="svr-case-role">Columbia University &middot; Lawful permanent resident</p>
                    <p>A recent graduate and prominent protest negotiator, arrested at his university apartment on March 8 and not charged with any crime. The case rested on a two-page memo from Secretary Rubio invoking the foreign-policy deportation ground. He was held for months at a remote facility in Jena, Louisiana, before a federal court freed him on bail in June.</p>
                    <span class="svr-tag svr-tag-free">Detained &middot; released on bail</span>
                </div>
            </div>
            <div class="svr-case">
                <div class="svr-avatar" aria-hidden="true">RÖ</div>
                <div>
                    <h3>Rümeysa Öztürk</h3>
                    <p class="svr-case-role">Tufts University &middot; F-1 student</p>
                    <p>A PhD student seized off a Somerville, Massachusetts street by plainclothes agents on March 25 as she walked to an iftar dinner; her visa had been quietly revoked days earlier. The only basis cited was a co-authored op-ed in the student newspaper. A Vermont judge ordered her released on May 9, finding her arrest likely retaliation for protected speech.</p>
                    <span class="svr-tag svr-tag-free">Detained &middot; released</span>
                </div>
            </div>
            <div class="svr-case">
                <div class="svr-avatar" aria-hidden="true">BS</div>
                <div>
                    <h3>Badar Khan Suri</h3>
                    <p class="svr-case-role">Georgetown University &middot; J-1 scholar</p>
                    <p>A postdoctoral fellow arrested outside his Virginia home on March 17 by masked agents and moved to a Texas detention center. DHS alleged he "spread Hamas propaganda"; his lawyers said he was being punished for his Gaza advocacy and his wife's family ties. He was released on May 14 after a judge found no evidence to justify his detention.</p>
                    <span class="svr-tag svr-tag-free">Detained &middot; released</span>
                </div>
            </div>
            <div class="svr-case">
                <div class="svr-avatar" aria-hidden="true">MM</div>
                <div>
                    <h3>Mohsen Mahdawi</h3>
                    <p class="svr-case-role">Columbia University &middot; Lawful permanent resident</p>
                    <p>A Palestinian organizer who grew up in a West Bank refugee camp, arrested on April 14 when he arrived for his U.S. citizenship interview in Vermont. A federal judge ordered him released on April 30, finding a "substantial claim" the arrest was meant to stifle disagreeable speech.</p>
                    <span class="svr-tag svr-tag-free">Detained &middot; released</span>
                </div>
            </div>
            <div class="svr-case">
                <div class="svr-avatar" aria-hidden="true">YC</div>
                <div>
                    <h3>Yunseo Chung</h3>
                    <p class="svr-case-role">Columbia University &middot; Lawful permanent resident</p>
                    <p>A 21-year-old who came to the United States as a child. After a March campus protest, ICE sought to arrest her under the same foreign-policy provision &mdash; but she sued first, and a federal judge in New York barred the government from detaining her.</p>
                    <span class="svr-tag svr-tag-risk">Protected by court order</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== METHODOLOGY & SOURCES ==================== --}}
    <div class="svr-section">
        <div class="svr-wrap">
            <div class="svr-eyebrow"><span class="svr-num">06</span> Methodology &amp; sources</div>
            <h2 class="svr-h2 svr-serif">How this briefing was assembled</h2>
            <div class="svr-note">
                <p><strong>About this page.</strong> NPPC did not conduct the reporting described here. This briefing <strong>compiles and cites</strong> the published work of news organizations, advocacy and legal groups, and court filings, focused on the period from January through June 2025. Figures for visa revocations and SEVIS terminations were a moving target throughout the spring; each number is paired with its source and date and counts a slightly different thing.</p>
                <p>The foreign-policy deportation power and the database-driven terminations were both still being litigated as this page was written; outcomes in individual cases have continued to change. Where a detail could not be independently confirmed, it is attributed to the outlet that reported it.</p>
            </div>
            <ul class="svr-sources">
                <li><a href="https://www.cnn.com/2025/04/17/us/university-international-student-visas-revoked" target="_blank" rel="noopener">CNN — More than 1,000 international students have had visas or legal status revoked</a><span>April 2025</span></li>
                <li><a href="https://www.insidehighered.com/news/global/international-students-us/2025/04/25/ice-reverses-course-sevis-terminations" target="_blank" rel="noopener">Inside Higher Ed — ICE reverses course on SEVIS terminations</a><span>April 2025</span></li>
                <li><a href="https://knightcolumbia.org/cases/aaup-v-rubio" target="_blank" rel="noopener">Knight First Amendment Institute — AAUP v. Rubio (ideological-deportation challenge)</a><span>Filed March 2025</span></li>
                <li><a href="https://apnews.com/hub/immigration" target="_blank" rel="noopener">Associated Press — Reporting on visa revocations and the NCIC student name-check</a><span>Spring 2025</span></li>
                <li><a href="https://www.aclu.org/" target="_blank" rel="noopener">ACLU — Litigation over the detention of student activists</a><span>2025</span></li>
                <li><a href="https://www.presidentsalliance.org/" target="_blank" rel="noopener">Presidents' Alliance on Higher Education and Immigration — SEVIS termination tracking</a><span>May 2025</span></li>
                <li><a href="https://www.npr.org/" target="_blank" rel="noopener">NPR — Coverage of the Khalil, Öztürk, Mahdawi and Khan Suri cases</a><span>2025</span></li>
            </ul>
        </div>
    </div>

    {{-- ==================== CTA ==================== --}}
    <div class="svr-cta">
        <h2>The right to speak shouldn't depend on a passport.</h2>
        <p>People are easiest to remove when no one is watching. Documentation, naming, and sustained pressure protect those the government would rather move in the dark.</p>
        <div class="svr-btns">
            <a class="svr-btn svr-btn-primary" href="/volunteer">Get involved</a>
            <a class="svr-btn svr-btn-ghost" href="/petitions">Sign a petition</a>
        </div>
    </div>

</div>
@endsection
