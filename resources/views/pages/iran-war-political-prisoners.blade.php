@extends('app')

@section('title', "Under Cover of War — Iran's Political Prisoners | NPPC")

@section('head')
<meta name="description" content="A briefing on how Iran used the June 2025 war with Israel to intensify the arbitrary arrest, torture, and execution of political prisoners — compiled from documentation by Amnesty International, Human Rights Watch, the UN, and Iranian human rights groups.">
<style>
    /* ============================================================
       Iran War Political Prisoners — hand-crafted briefing page.
       Long-form report layout modeled on the publication format of
       human-rights organizations. All classes are scoped with the
       iwp- prefix so nothing leaks into the rest of the site.
       ============================================================ */
    .iwp { background: #0a0a0c; color: #fff; }
    .iwp-serif { font-family: Georgia, 'Times New Roman', Times, serif; }
    .iwp a { color: #e8675b; }
    .iwp a:hover { color: #fff; }

    /* ---- Hero ---- */
    .iwp-hero { position: relative; overflow: hidden; background: #000; min-height: 620px; display: flex; align-items: flex-end; }
    .iwp-hero-bg { position: absolute; inset: 0; z-index: 0; }
    .iwp-hero-bg svg { width: 100%; height: 100%; display: block; }
    .iwp-hero-bg img { width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; }
    .iwp-hero-credit { position: absolute; top: 16px; right: 18px; z-index: 2; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255,255,255,0.5); }
    .iwp-hero-overlay { position: absolute; inset: 0; z-index: 1;
        background: radial-gradient(120% 90% at 72% 0%, rgba(216,57,43,0.18), transparent 60%),
                    linear-gradient(180deg, rgba(10,10,12,0.35) 0%, rgba(10,10,12,0.72) 55%, #0a0a0c 100%); }
    .iwp-hero-content { position: relative; z-index: 2; max-width: 900px; margin: 0 auto; width: 100%; padding: 120px 24px 56px; }
    .iwp-kicker { display: inline-flex; align-items: center; gap: 12px; font-size: 12px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: #e8675b; margin-bottom: 22px; }
    .iwp-kicker::before { content: ""; width: 34px; height: 2px; background: #d8392b; display: inline-block; }
    .iwp-hero-title { font-size: 4.6rem; line-height: 0.98; font-weight: 700; color: #fff; margin: 0 0 18px; letter-spacing: -0.02em; }
    .iwp-hero-sub { font-size: 1.4rem; line-height: 1.4; color: rgba(255,255,255,0.78); max-width: 720px; margin: 0 0 28px; }
    .iwp-hero-meta { display: flex; flex-wrap: wrap; gap: 10px 18px; align-items: center; font-size: 13px; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: rgba(255,255,255,0.55); }
    .iwp-hero-meta span { display: inline-flex; align-items: center; }
    .iwp-hero-meta span + span::before { content: ""; width: 4px; height: 4px; border-radius: 50%; background: #d8392b; margin-right: 18px; }

    /* ---- Layout primitives ---- */
    .iwp-wrap { max-width: 820px; margin: 0 auto; padding: 0 24px; }
    .iwp-section { padding: 72px 0; border-top: 1px solid rgba(255,255,255,0.08); }
    .iwp-section:first-of-type { border-top: 0; }
    .iwp-eyebrow { display: flex; align-items: center; gap: 12px; font-size: 12px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: #e8675b; margin-bottom: 18px; }
    .iwp-eyebrow .iwp-num { font-family: Georgia, serif; color: rgba(255,255,255,0.4); }
    .iwp-h2 { font-size: 2.6rem; line-height: 1.1; font-weight: 700; color: #fff; margin: 0 0 24px; }
    .iwp-h3 { font-size: 1.5rem; font-weight: 700; color: #fff; margin: 36px 0 14px; }
    .iwp-p { font-size: 17px; line-height: 1.85; color: rgba(255,255,255,0.76); margin: 0 0 1.4em; }
    .iwp-p strong { color: #fff; font-weight: 700; }
    .iwp-cite { font-size: 13px; color: rgba(255,255,255,0.45); }
    .iwp-cite a { color: rgba(255,255,255,0.55); text-decoration: underline; }
    .iwp-cite a:hover { color: #e8675b; }

    /* ---- Lead ---- */
    .iwp-lead .iwp-p { font-family: Georgia, 'Times New Roman', serif; font-size: 1.45rem; line-height: 1.5; color: #fff; }
    .iwp-lead .iwp-p:first-child::first-letter { float: left; font-size: 4.2em; line-height: 0.72; padding: 0.05em 0.12em 0 0; color: #d8392b; font-weight: 700; }

    /* ---- Stats ---- */
    .iwp-stats-band { background: #000; border-top: 1px solid rgba(255,255,255,0.08); border-bottom: 1px solid rgba(255,255,255,0.08); }
    .iwp-stats { max-width: 1080px; margin: 0 auto; padding: 8px 24px; display: grid; grid-template-columns: repeat(4, 1fr); }
    .iwp-stat { padding: 48px 24px; border-left: 1px solid rgba(255,255,255,0.08); }
    .iwp-stat:first-child { border-left: 0; }
    .iwp-stat-num { font-family: Georgia, serif; font-size: 3.4rem; line-height: 1; font-weight: 700; color: #e8675b; letter-spacing: -0.02em; }
    .iwp-stat-num small { font-size: 0.45em; color: rgba(255,255,255,0.45); }
    .iwp-stat-label { margin-top: 14px; font-size: 15px; line-height: 1.5; color: rgba(255,255,255,0.72); }
    .iwp-stat-src { margin-top: 10px; font-size: 12px; color: rgba(255,255,255,0.4); }

    /* ---- Pull quote ---- */
    .iwp-pull { border-left: 3px solid #d8392b; padding: 6px 0 6px 28px; margin: 8px 0; }
    .iwp-pull p { font-family: Georgia, 'Times New Roman', serif; font-size: 2rem; line-height: 1.3; color: #fff; margin: 0 0 14px; }
    .iwp-pull cite { font-style: normal; font-size: 14px; letter-spacing: 0.04em; color: rgba(255,255,255,0.5); text-transform: uppercase; }

    /* ---- Testimony ---- */
    .iwp-testimony { position: relative; background: #111; border: 1px solid rgba(255,255,255,0.08); border-left: 3px solid #8c1c12; border-radius: 6px; padding: 32px; margin-top: 32px; }
    .iwp-testimony::before { content: "\201C"; position: absolute; top: 14px; right: 22px; font-family: Georgia, serif; font-size: 4rem; line-height: 0.6; color: #8c1c12; opacity: 0.6; }
    .iwp-testimony p { font-family: Georgia, serif; font-style: italic; font-size: 1.25rem; line-height: 1.5; color: #fff; margin: 0 0 14px; }
    .iwp-testimony footer { font-size: 14px; color: rgba(255,255,255,0.5); }

    /* ---- Abuse cards ---- */
    .iwp-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 36px; }
    .iwp-card { background: #111; border: 1px solid rgba(255,255,255,0.08); border-radius: 6px; padding: 26px; transition: border-color 0.25s, transform 0.25s; }
    .iwp-card:hover { border-color: rgba(216,57,43,0.4); transform: translateY(-3px); }
    .iwp-card-icon { color: #d8392b; margin-bottom: 14px; }
    .iwp-card h3 { font-size: 1.15rem; font-weight: 800; color: #fff; margin: 0 0 8px; }
    .iwp-card p { font-size: 14px; line-height: 1.6; color: rgba(255,255,255,0.62); margin: 0; }

    /* ---- Named cases ---- */
    .iwp-case { display: grid; grid-template-columns: 56px 1fr; gap: 20px; align-items: start; padding: 26px 0; border-top: 1px solid rgba(255,255,255,0.08); }
    .iwp-case:last-child { border-bottom: 1px solid rgba(255,255,255,0.08); }
    .iwp-avatar { width: 56px; height: 56px; border-radius: 50%; background: #1a1a1f; border: 1px solid rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; font-family: Georgia, serif; font-weight: 700; font-size: 1.25rem; color: #e8675b; }
    .iwp-case h3 { font-size: 1.25rem; font-weight: 800; color: #fff; margin: 0 0 2px; }
    .iwp-case-role { font-size: 12px; letter-spacing: 0.05em; text-transform: uppercase; color: rgba(255,255,255,0.45); margin: 0 0 10px; }
    .iwp-case p { font-size: 15px; line-height: 1.65; color: rgba(255,255,255,0.72); margin: 0; }
    .iwp-tag { display: inline-block; font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; padding: 5px 10px; border-radius: 999px; margin-top: 12px; }
    .iwp-tag-risk { background: rgba(216,57,43,0.15); color: #e8675b; border: 1px solid rgba(216,57,43,0.4); }
    .iwp-tag-exec { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.2); }

    /* ---- Sources & note ---- */
    .iwp-note { background: #111; border: 1px solid rgba(255,255,255,0.08); border-radius: 6px; padding: 28px; margin-bottom: 32px; }
    .iwp-note p { font-size: 15px; line-height: 1.7; color: rgba(255,255,255,0.72); margin: 0 0 12px; }
    .iwp-note p:last-child { margin: 0; }
    .iwp-note strong { color: #fff; }
    .iwp-sources { list-style: none; margin: 0; padding: 0; }
    .iwp-sources li { border-top: 1px solid rgba(255,255,255,0.08); padding: 16px 0; }
    .iwp-sources li:last-child { border-bottom: 1px solid rgba(255,255,255,0.08); }
    .iwp-sources a { font-weight: 700; color: #fff; text-decoration: none; }
    .iwp-sources a:hover { color: #e8675b; }
    .iwp-sources span { display: block; font-size: 13px; color: rgba(255,255,255,0.4); margin-top: 4px; }

    /* ---- CTA ---- */
    .iwp-cta { background: linear-gradient(135deg, #8c1c12, #5a120b); text-align: center; padding: 80px 24px; }
    .iwp-cta h2 { font-family: Georgia, serif; font-size: 2.6rem; font-weight: 700; color: #fff; margin: 0 0 16px; }
    .iwp-cta p { font-size: 17px; line-height: 1.7; color: rgba(255,255,255,0.88); max-width: 600px; margin: 0 auto 30px; }
    .iwp-btns { display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; }
    .iwp-btn { display: inline-flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 700; text-decoration: none; padding: 15px 30px; border-radius: 999px; transition: transform 0.2s, background 0.2s; }
    .iwp-btn-primary { background: #fff; color: #1a1a1a; }
    .iwp-btn-primary:hover { transform: translateY(-2px); color: #000; }
    .iwp-btn-ghost { background: transparent; color: #fff; border: 1px solid rgba(255,255,255,0.55); }
    .iwp-btn-ghost:hover { background: rgba(255,255,255,0.12); transform: translateY(-2px); color: #fff; }

    /* ---- Responsive ---- */
    @@media (max-width: 900px) {
        .iwp-hero { min-height: 500px; }
        .iwp-hero-title { font-size: 3rem; }
        .iwp-hero-sub { font-size: 1.15rem; }
        .iwp-h2 { font-size: 2.1rem; }
        .iwp-stats { grid-template-columns: 1fr 1fr; }
        .iwp-stat { border-left: 0; border-top: 1px solid rgba(255,255,255,0.08); padding: 32px 16px; }
        .iwp-stat:first-child, .iwp-stat:nth-child(2) { border-top: 0; }
        .iwp-cards { grid-template-columns: 1fr 1fr; }
        .iwp-pull p { font-size: 1.6rem; }
    }
    @@media (max-width: 600px) {
        .iwp-hero-content { padding: 100px 20px 40px; }
        .iwp-hero-title { font-size: 2.3rem; }
        .iwp-h2 { font-size: 1.8rem; }
        .iwp-lead .iwp-p { font-size: 1.2rem; }
        .iwp-stats { grid-template-columns: 1fr; }
        .iwp-stat, .iwp-stat:nth-child(2) { border-left: 0; border-top: 1px solid rgba(255,255,255,0.08); }
        .iwp-stat:first-child { border-top: 0; }
        .iwp-cards { grid-template-columns: 1fr; }
        .iwp-cta h2 { font-size: 2rem; }
    }
</style>
@endsection

@section('body')
<div class="iwp">

    {{-- ==================== HERO ==================== --}}
    <div class="iwp-hero">
        <div class="iwp-hero-bg" aria-hidden="true">
            <img src="{{ asset('images/iran-war-hero.jpg') }}" alt="">
        </div>
        <div class="iwp-hero-overlay" aria-hidden="true"></div>
        <div class="iwp-hero-credit">Photo: Getty Images</div>
        <div class="iwp-hero-content">
            <span class="iwp-kicker">A briefing on political imprisonment in Iran</span>
            <h1 class="iwp-hero-title iwp-serif">Under Cover of War</h1>
            <p class="iwp-hero-sub">How the Islamic Republic used the 2025 conflict with Israel to intensify the arbitrary arrest, torture, and execution of political prisoners — while the prisoners themselves became casualties of the war.</p>
            <div class="iwp-hero-meta">
                <span>Briefing</span><span>Updated May 2026</span><span>NPPC</span>
            </div>
        </div>
    </div>

    {{-- ==================== LEAD ==================== --}}
    <div class="iwp-section iwp-lead">
        <div class="iwp-wrap">
            <p class="iwp-p">When Israel and Iran went to war for twelve days in June 2025, the fighting did not stay at the front. It reached inside Iran's prisons — and it became a pretext for what followed. As missiles fell, the Islamic Republic launched one of the most sweeping waves of repression in its recent history: thousands of arbitrary arrests, a fast-tracked expansion of the death penalty, and a surge of executions that monitors say has continued, under the cover of national emergency, long after the ceasefire.</p>
            <p class="iwp-p">This briefing draws together the documentation of Amnesty International, Human Rights Watch, the United Nations, and Iranian human rights organizations to describe what was done to political prisoners and detainees in Iran during and after the war — and to put names to some of those at risk.</p>
        </div>
    </div>

    {{-- ==================== STATS ==================== --}}
    <div class="iwp-stats-band">
        <div class="iwp-stats">
            <div class="iwp-stat">
                <div class="iwp-stat-num">700<small>+</small></div>
                <div class="iwp-stat-label">arrested on alleged "collaboration with Israel" charges in the days after fighting began.</div>
                <div class="iwp-stat-src">Amnesty International, June 2025</div>
            </div>
            <div class="iwp-stat">
                <div class="iwp-stat-num">2,159<small>+</small></div>
                <div class="iwp-stat-label">executions recorded across Iran in 2025 — the pace accelerating after the war.</div>
                <div class="iwp-stat-src">Amnesty International</div>
            </div>
            <div class="iwp-stat">
                <div class="iwp-stat-num">~80</div>
                <div class="iwp-stat-label">people killed when Israeli airstrikes hit Tehran's Evin Prison on 23 June 2025.</div>
                <div class="iwp-stat-src">Iranian authorities · HRW</div>
            </div>
            <div class="iwp-stat">
                <div class="iwp-stat-num">78<small>+</small></div>
                <div class="iwp-stat-label">protesters and dissidents under sentence of death and at risk of execution.</div>
                <div class="iwp-stat-src">Amnesty International, 2026</div>
            </div>
        </div>
    </div>

    {{-- ==================== CONTEXT ==================== --}}
    <div class="iwp-section">
        <div class="iwp-wrap">
            <div class="iwp-eyebrow"><span class="iwp-num">01</span> The war</div>
            <h2 class="iwp-h2 iwp-serif">A twelve-day war, and a pretext</h2>
            <p class="iwp-p">On 13 June 2025, Israel launched a wide-ranging attack on Iran. The escalation that followed — the <strong>Twelve-Day War</strong> — killed more than 1,100 people in Iran and wounded over 5,600 before a ceasefire took hold. <span class="iwp-cite">(<a href="https://www.amnesty.org/en/latest/news/2025/06/iran-growing-fears-over-torture-and-executions-of-individuals-accused-of-espionage-for-israel/" target="_blank" rel="noopener">Amnesty International</a>)</span></p>
            <p class="iwp-p">From the first day, the Iranian state turned the emergency inward. Beginning on 14 June, authorities arrested more than <strong>700 people</strong> on accusations of "collaboration" or "espionage" for Israel. Within days, parliament moved to fast-track legislation making such "cooperation with hostile governments" automatically punishable by death as "corruption on earth" (<em>efsad fel-arz</em>).</p>
            <p class="iwp-p">Human rights monitors warned at once that the war had handed the authorities a license: a way to recast long-standing political prisoners, protesters, journalists, lawyers, and ethnic-minority activists as wartime enemies — and to accelerate executions and arrests behind a wall of national-security secrecy.</p>
        </div>
    </div>

    {{-- ==================== PULL QUOTE ==================== --}}
    <div class="iwp-section">
        <div class="iwp-wrap">
            <blockquote class="iwp-pull">
                <p>We found reasonable grounds to believe that, in carrying out the airstrikes on Evin prison, Israel committed the war crime of intentionally directing attacks against a civilian object.</p>
                <cite>— Sara Hossain, Independent International Fact-Finding Mission on Iran, to the UN Human Rights Council, March 2026</cite>
            </blockquote>
        </div>
    </div>

    {{-- ==================== ARRESTS ==================== --}}
    <div class="iwp-section">
        <div class="iwp-wrap">
            <div class="iwp-eyebrow"><span class="iwp-num">02</span> Mass arrests</div>
            <h2 class="iwp-h2 iwp-serif">A "tsunami" of arbitrary arrests</h2>
            <p class="iwp-p">Human Rights Watch described the wave of detentions that followed the war as a <strong>"tsunami of arbitrary arrests and enforced disappearances."</strong> Arrests were carried out without warrants — in home raids and at checkpoints — sweeping up journalists, lawyers, students, teachers, and ordinary social-media users accused of sharing footage or merely calling for peace. <span class="iwp-cite">(<a href="https://www.hrw.org/news/2026/02/24/iran-tsunami-of-arbitrary-arrests-enforced-disappearances" target="_blank" rel="noopener">Human Rights Watch</a>)</span></p>
            <p class="iwp-p">Many of those seized were held incommunicado in undisclosed locations, their families given no information about where they were or whether they were alive. Ethnic minorities — Kurds, Baloch, and Afghan nationals — were targeted with particular intensity, a long-documented pattern that the war only sharpened.</p>
            <p class="iwp-p">By early 2026, monitors counted <strong>thousands</strong> detained on national-security grounds, layered on top of the tens of thousands arrested during the protests that swept the country in late 2025 and January 2026.</p>
        </div>
    </div>

    {{-- ==================== EVIN ==================== --}}
    <div class="iwp-section">
        <div class="iwp-wrap">
            <div class="iwp-eyebrow"><span class="iwp-num">03</span> The Evin strike</div>
            <h2 class="iwp-h2 iwp-serif">The prison itself became a target</h2>
            <p class="iwp-p">At around midday on <strong>23 June 2025</strong>, Israeli airstrikes hit Tehran's Evin Prison — the country's most notorious holding site for political prisoners — striking multiple buildings across the complex during visiting hours. The library, the clinic, the prosecutor's office, the visitation hall, and cell blocks were damaged or destroyed. Iranian authorities reported around <strong>80 people killed</strong>, among them prisoners, guards, medical staff, and visiting family members. <span class="iwp-cite">(<a href="https://www.hrw.org/news/2025/08/14/iran-israeli-attack-on-evin-prison-an-apparent-war-crime" target="_blank" rel="noopener">HRW</a>; <a href="https://www.amnesty.org/en/latest/news/2025/07/iran-deliberate-israeli-attack-on-tehrans-evin-prison-must-be-investigated-as-a-war-crime/" target="_blank" rel="noopener">Amnesty</a>)</span></p>
            <p class="iwp-p">Both Human Rights Watch and Amnesty International concluded the attack was an <strong>apparent war crime</strong>; in March 2026 the UN's Independent International Fact-Finding Mission found "reasonable grounds" that it was a deliberate strike on a civilian object.</p>
            <h3 class="iwp-h3 iwp-serif">Loaded onto buses in the night</h3>
            <p class="iwp-p">The danger did not end with the bombing. On the night of the strike, prisoners from Evin's wards 4, 7, and 8 — home to many political detainees — were forcibly loaded onto buses by armed guards and moved without any legal process. Men were sent to the harsh Greater Tehran Penitentiary (Fashafouyeh); women to the notorious Qarchak prison. Rights groups reported that detainees were handcuffed, shackled, not even allowed to collect their belongings, then held in degraded, overcrowded conditions. <span class="iwp-cite">(<a href="https://www.hrw.org/news/2025/08/14/iran-detainees-ill-treated-and-disappeared-after-israeli-evin-prison-attack" target="_blank" rel="noopener">HRW</a>; <a href="https://iran-hrm.com/2025/06/28/forced-transfer-of-political-prisoners-from-evin/" target="_blank" rel="noopener">Iran HRM</a>)</span></p>

            <figure class="iwp-testimony">
                <p>Prisoners were transferred at gunpoint in the middle of the night, shackled and with nothing but the clothes they wore, to facilities without adequate water, beds, or medical care. For days, families did not know whether their relatives had survived the bombing.</p>
                <footer>Representative account drawn from documentation by Human Rights Watch and Iranian rights groups, June–August 2025.</footer>
            </figure>
        </div>
    </div>

    {{-- ==================== EXECUTIONS ==================== --}}
    <div class="iwp-section">
        <div class="iwp-wrap">
            <div class="iwp-eyebrow"><span class="iwp-num">04</span> Executions</div>
            <h2 class="iwp-h2 iwp-serif">Executions under cover of war</h2>
            <p class="iwp-p">The clearest measure of the crackdown is the gallows. Amnesty International recorded <strong>at least 2,159 executions in Iran in 2025</strong> — and noted that the pace accelerated sharply after the Twelve-Day War. UN experts warned in October 2025 of "escalating repression and record executions" following the June attacks. <span class="iwp-cite">(<a href="https://www.ohchr.org/en/press-releases/2025/10/iran-un-expert-warns-escalating-repression-and-record-executions-after-june" target="_blank" rel="noopener">OHCHR</a>)</span></p>
            <p class="iwp-p">In the war's first days, the judiciary moved fast against people accused of spying for Israel. <strong>Esmaeil Fekri</strong> was hanged on 16 June 2025, two days after the fighting began. Others — including the Kurdish men <strong>Edris Ali</strong>, <strong>Azad Shojaei</strong>, and <strong>Rasoul Ahmad Rasoul</strong> — were executed on espionage allegations that prosecutors backed with no credible public evidence. <span class="iwp-cite">(<a href="https://time.com/7298282/iran-israel-war-executions-arrests/" target="_blank" rel="noopener">TIME</a>)</span></p>
            <p class="iwp-p">The executions then widened to protesters. By the spring of 2026, the Center for Human Rights in Iran documented at least <strong>22 political prisoners hanged in six weeks</strong> — roughly one every two days — after secret proceedings marked by torture and forced confessions. Among the first was <strong>Saleh Mohammadi</strong>, a 19-year-old wrestler sentenced to death less than three weeks after his arrest. <span class="iwp-cite">(<a href="https://iranhumanrights.org/2026/04/irans-execution-machine-political-hangings-surge-as-dozens-face-imminent-death/" target="_blank" rel="noopener">CHRI</a>)</span></p>
            <p class="iwp-p">Amnesty International reports that <strong>at least 78 protesters and dissidents are now under sentence of death</strong> and at risk of execution — including people who were children at the time of the alleged offense. <span class="iwp-cite">(<a href="https://www.amnesty.org/en/documents/mde13/1032/2026/en/" target="_blank" rel="noopener">Amnesty</a>)</span></p>
        </div>
    </div>

    {{-- ==================== TORTURE / ABUSES ==================== --}}
    <div class="iwp-section">
        <div class="iwp-wrap">
            <div class="iwp-eyebrow"><span class="iwp-num">05</span> Torture &amp; due process</div>
            <h2 class="iwp-h2 iwp-serif">Confessions extracted in the dark</h2>
            <p class="iwp-p">Across these cases, rights organizations describe the same machinery: arrest without warrant, disappearance into solitary confinement, torture to extract a "confession," a closed trial without meaningful defense, and a sentence handed down in days. The methods documented since the war include beatings, electric shocks, mock executions, suspension by the hands and feet, prolonged solitary confinement, and the denial of food and medical care. <span class="iwp-cite">(<a href="https://www.amnestyusa.org/press-releases/iran-mass-arbitrary-arrests-and-political-executions-mark-intensifying-repression/" target="_blank" rel="noopener">Amnesty International USA</a>)</span></p>

            <div class="iwp-cards">
                <div class="iwp-card">
                    <div class="iwp-card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2 4 6v6c0 5 3.5 8 8 10 4.5-2 8-5 8-10V6z"/><path d="m9 12 2 2 4-4"/></svg></div>
                    <h3>Arbitrary detention</h3>
                    <p>Arrests without warrants, in home raids and at checkpoints, of journalists, lawyers, students, and social-media users.</p>
                </div>
                <div class="iwp-card">
                    <div class="iwp-card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg></div>
                    <h3>Enforced disappearance</h3>
                    <p>Detainees held incommunicado in undisclosed sites, with families denied any word of their fate.</p>
                </div>
                <div class="iwp-card">
                    <div class="iwp-card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M13 2 3 14h7l-1 8 11-13h-7z"/></svg></div>
                    <h3>Torture</h3>
                    <p>Beatings, electric shocks, mock executions, and suspension used to coerce confessions.</p>
                </div>
                <div class="iwp-card">
                    <div class="iwp-card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M7 6V4h10v2M6 6l1 14h10l1-14"/></svg></div>
                    <h3>Sham trials</h3>
                    <p>Closed, days-long proceedings without counsel, ending in death sentences built on coerced "confessions."</p>
                </div>
                <div class="iwp-card">
                    <div class="iwp-card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 22V4a2 2 0 0 1 2-2h9l1 3h4l-2 5 2 5h-5l-1-3H6v10z"/></svg></div>
                    <h3>Wartime "espionage" charges</h3>
                    <p>A fast-tracked law making alleged cooperation with Israel automatically punishable by death.</p>
                </div>
                <div class="iwp-card">
                    <div class="iwp-card-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v6m0 0 3-3m-3 3L9 6M5 21h14M7 21l1-9h8l1 9"/></svg></div>
                    <h3>Denial of care</h3>
                    <p>Food, hygiene, and medical treatment withheld — conditions worsened by forced transfers after the Evin strike.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== NAMED CASES ==================== --}}
    <div class="iwp-section">
        <div class="iwp-wrap">
            <div class="iwp-eyebrow"><span class="iwp-num">06</span> The faces behind the numbers</div>
            <h2 class="iwp-h2 iwp-serif">Names, not statistics</h2>
            <p class="iwp-p">Behind every figure is a person with a name, a family, and a story the state has tried to erase. A few of the documented cases:</p>

            <div class="iwp-case">
                <div class="iwp-avatar" aria-hidden="true">AD</div>
                <div>
                    <h3>Ahmadreza Djalali</h3>
                    <p class="iwp-case-role">Disaster-medicine academic · Swedish–Iranian</p>
                    <p>Arbitrarily detained since 2016 and held in Evin, his death sentence on "espionage" charges has been repeatedly upheld. He has spent years at imminent risk of execution.</p>
                    <span class="iwp-tag iwp-tag-risk">At risk of execution</span>
                </div>
            </div>
            <div class="iwp-case">
                <div class="iwp-avatar" aria-hidden="true">SM</div>
                <div>
                    <h3>Saleh Mohammadi</h3>
                    <p class="iwp-case-role">Wrestler, age 19</p>
                    <p>Sentenced to death by a Qom court less than three weeks after his arrest, over the alleged killing of a security agent during protests — a charge he denied. He was among the first protesters of the January 2026 uprising to be hanged.</p>
                    <span class="iwp-tag iwp-tag-exec">Executed, 2026</span>
                </div>
            </div>
            <div class="iwp-case">
                <div class="iwp-avatar" aria-hidden="true">EF</div>
                <div>
                    <h3>Esmaeil Fekri</h3>
                    <p class="iwp-case-role">Accused of espionage for Israel</p>
                    <p>Executed on 16 June 2025 — two days after the war began — in one of the first wartime hangings on espionage allegations that rights groups say were never backed by credible evidence.</p>
                    <span class="iwp-tag iwp-tag-exec">Executed, 2025</span>
                </div>
            </div>
            <div class="iwp-case">
                <div class="iwp-avatar" aria-hidden="true">KR</div>
                <div>
                    <h3>Edris Ali, Azad Shojaei &amp; Rasoul Ahmad Rasoul</h3>
                    <p class="iwp-case-role">Kurdish men</p>
                    <p>Executed on espionage allegations tied to the 2020 assassination of nuclear scientist Mohsen Fakhrizadeh, after proceedings condemned by rights groups as grossly unfair.</p>
                    <span class="iwp-tag iwp-tag-exec">Executed, 2025</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== METHODOLOGY & SOURCES ==================== --}}
    <div class="iwp-section">
        <div class="iwp-wrap">
            <div class="iwp-eyebrow"><span class="iwp-num">07</span> Methodology &amp; sources</div>
            <h2 class="iwp-h2 iwp-serif">How this briefing was assembled</h2>
            <div class="iwp-note">
                <p><strong>About this page.</strong> NPPC did not conduct the field investigations described here. This briefing <strong>compiles and cites</strong> the published findings of established human rights organizations, UN bodies, and credible news reporting, current to May 2026. Figures change as monitors update their counts; where accounts are presented as representative, they are drawn from that published documentation and labeled as such.</p>
                <p>Reports of conditions inside Iranian prisons are necessarily incomplete: the state restricts access, holds many detainees incommunicado, and prosecutes those who speak out. The true scale is likely greater than any figure here.</p>
            </div>
            <ul class="iwp-sources">
                <li><a href="https://www.amnesty.org/en/latest/news/2025/06/iran-growing-fears-over-torture-and-executions-of-individuals-accused-of-espionage-for-israel/" target="_blank" rel="noopener">Amnesty International — Growing fears over torture and executions of those accused of "espionage" for Israel</a><span>June 2025</span></li>
                <li><a href="https://www.amnesty.org/en/latest/news/2025/07/iran-deliberate-israeli-attack-on-tehrans-evin-prison-must-be-investigated-as-a-war-crime/" target="_blank" rel="noopener">Amnesty International — Israeli attack on Evin prison must be investigated as a war crime</a><span>July 2025</span></li>
                <li><a href="https://www.amnesty.org/en/documents/mde13/1032/2026/en/" target="_blank" rel="noopener">Amnesty International — Iran: Protesters and dissidents at risk of execution</a><span>2026</span></li>
                <li><a href="https://www.hrw.org/news/2025/08/14/iran-israeli-attack-on-evin-prison-an-apparent-war-crime" target="_blank" rel="noopener">Human Rights Watch — Israeli attack on Evin prison an apparent war crime</a><span>August 2025</span></li>
                <li><a href="https://www.hrw.org/news/2026/02/24/iran-tsunami-of-arbitrary-arrests-enforced-disappearances" target="_blank" rel="noopener">Human Rights Watch — Iran: Tsunami of arbitrary arrests, enforced disappearances</a><span>February 2026</span></li>
                <li><a href="https://www.ohchr.org/en/press-releases/2025/10/iran-un-expert-warns-escalating-repression-and-record-executions-after-june" target="_blank" rel="noopener">OHCHR — UN expert warns of escalating repression and record executions after June attacks</a><span>October 2025</span></li>
                <li><a href="https://iranhumanrights.org/2026/04/irans-execution-machine-political-hangings-surge-as-dozens-face-imminent-death/" target="_blank" rel="noopener">Center for Human Rights in Iran — Iran's execution machine: political hangings surge</a><span>April 2026</span></li>
                <li><a href="https://iran-hrm.com/2025/06/28/forced-transfer-of-political-prisoners-from-evin/" target="_blank" rel="noopener">Iran Human Rights Monitor — Forced transfer of political prisoners from Evin</a><span>June 2025</span></li>
                <li><a href="https://www.npr.org/2026/05/06/nx-s1-5804464/iran-steps-up-executions-of-prisoners-under-cover-of-war" target="_blank" rel="noopener">NPR — Iran steps up executions of prisoners under cover of war</a><span>May 2026</span></li>
            </ul>
        </div>
    </div>

    {{-- ==================== CTA ==================== --}}
    <div class="iwp-cta">
        <h2>Their names should outlast their cells.</h2>
        <p>Political prisoners are most in danger when the world stops watching. Documentation, naming, and sustained pressure save lives.</p>
        <div class="iwp-btns">
            <a class="iwp-btn iwp-btn-primary" href="/get-involved">Get involved</a>
            <a class="iwp-btn iwp-btn-ghost" href="/petitions">Sign a petition</a>
        </div>
    </div>

</div>
@endsection
