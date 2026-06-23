@extends('app')

@section('title', 'Civic Profile — National Political Prisoner Coalition')

@section('head')
    <meta name="description" content="Discover your unique Civic Profile. An interactive quiz from the National Political Prisoner Coalition that measures your civic values, engagement, and knowledge of rights, due process, and the right to dissent.">
    @verbatim
    <style>
        .cp {
            --cp-accent: #5660fe;
            --cp-accent-dark: #3a43d6;
            --cp-ink: #14152a;
            --cp-muted: #5b5e72;
            --cp-line: #e4e6f1;
            --cp-bg: #f6f7fc;
            --cp-card: #ffffff;
            --cp-good: #1f9d57;
            --cp-bad: #d3445b;
            max-width: 760px;
            margin: 0 auto;
            padding: 28px 20px 96px;
            color: var(--cp-ink);
            line-height: 1.5;
        }
        .cp *, .cp *::before, .cp *::after { box-sizing: border-box; }
        .cp button { font-family: inherit; cursor: pointer; }

        /* ---------- Intro / hero ---------- */
        .cp-hero { text-align: center; padding: 24px 0 8px; }
        .cp-eyebrow {
            text-transform: uppercase; letter-spacing: .14em; font-size: 13px;
            font-weight: 700; color: var(--cp-accent); margin: 0 0 14px;
        }
        .cp-hero h1 {
            font-size: clamp(32px, 6vw, 52px); line-height: 1.04; margin: 0 0 16px;
            font-weight: 900; letter-spacing: -0.02em;
        }
        .cp-lede { font-size: clamp(17px, 2.4vw, 20px); color: var(--cp-muted); max-width: 560px; margin: 0 auto 28px; }
        .cp-parts { display: grid; gap: 14px; grid-template-columns: repeat(3, 1fr); margin: 8px 0 32px; text-align: left; }
        .cp-part-card { background: var(--cp-card); border: 1px solid var(--cp-line); border-radius: 14px; padding: 18px; }
        .cp-part-card .n { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 50%; background: var(--cp-accent); color: #fff; font-weight: 800; font-size: 14px; margin-bottom: 12px; }
        .cp-part-card h3 { margin: 0 0 6px; font-size: 18px; font-weight: 800; }
        .cp-part-card p { margin: 0; font-size: 14px; color: var(--cp-muted); }

        .cp-btn {
            display: inline-block; border: none; background: var(--cp-accent); color: #fff;
            font-weight: 800; font-size: 17px; padding: 15px 38px; border-radius: 999px;
            transition: background .15s ease, transform .05s ease; text-decoration: none;
        }
        .cp-btn:hover { background: var(--cp-accent-dark); }
        .cp-btn:active { transform: translateY(1px); }
        .cp-note { font-size: 13px; color: var(--cp-muted); margin-top: 16px; }

        /* ---------- Progress ---------- */
        .cp-progress { margin: 8px 0 28px; }
        .cp-progress-meta { display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; color: var(--cp-muted); margin-bottom: 8px; }
        .cp-progress-meta .part { color: var(--cp-accent); text-transform: uppercase; letter-spacing: .1em; }
        .cp-progress-track { height: 8px; background: var(--cp-line); border-radius: 999px; overflow: hidden; }
        .cp-progress-fill { height: 100%; background: var(--cp-accent); border-radius: 999px; transition: width .35s cubic-bezier(.4,0,.2,1); }

        /* ---------- Question stage ---------- */
        .cp-stage { background: var(--cp-card); border: 1px solid var(--cp-line); border-radius: 18px; padding: 32px 28px; box-shadow: 0 12px 40px -28px rgba(20,21,42,.5); }
        .cp-part-badge { display: inline-block; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .12em; color: var(--cp-accent); background: rgba(86,96,254,.1); padding: 5px 12px; border-radius: 999px; margin-bottom: 18px; }
        .cp-q { font-size: clamp(20px, 3.2vw, 26px); font-weight: 800; line-height: 1.25; margin: 0 0 24px; letter-spacing: -0.01em; }
        .cp-scale-hint { font-size: 13px; color: var(--cp-muted); margin: -14px 0 18px; }

        .cp-opts { display: flex; flex-direction: column; gap: 12px; }
        .cp-opt {
            display: flex; align-items: center; gap: 14px; width: 100%; text-align: left;
            background: #fff; border: 2px solid var(--cp-line); border-radius: 12px;
            padding: 16px 18px; font-size: 16px; font-weight: 600; color: var(--cp-ink);
            transition: border-color .12s ease, background .12s ease, transform .05s ease;
        }
        .cp-opt:hover { border-color: var(--cp-accent); background: #fbfbff; }
        .cp-opt:active { transform: scale(.995); }
        .cp-opt.is-selected { border-color: var(--cp-accent); background: rgba(86,96,254,.08); }
        .cp-opt-key {
            flex: 0 0 auto; width: 30px; height: 30px; border-radius: 8px; background: var(--cp-bg);
            border: 1px solid var(--cp-line); display: inline-flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 14px; color: var(--cp-muted);
        }
        .cp-opt.is-selected .cp-opt-key { background: var(--cp-accent); border-color: var(--cp-accent); color: #fff; }

        .cp-stage-foot { display: flex; align-items: center; justify-content: space-between; margin-top: 26px; }
        .cp-back { background: none; border: none; color: var(--cp-muted); font-weight: 700; font-size: 15px; padding: 8px 4px; }
        .cp-back:hover { color: var(--cp-ink); }
        .cp-back[disabled] { opacity: 0; pointer-events: none; }
        .cp-tap-hint { font-size: 13px; color: var(--cp-muted); }

        /* ---------- Part intro card ---------- */
        .cp-partintro { text-align: center; padding: 18px 4px 6px; }
        .cp-partintro .step { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: .14em; color: var(--cp-accent); margin-bottom: 14px; }
        .cp-partintro h2 { font-size: clamp(26px, 4.5vw, 38px); font-weight: 900; margin: 0 0 16px; letter-spacing: -0.02em; }
        .cp-partintro p { font-size: 17px; color: var(--cp-muted); max-width: 520px; margin: 0 auto 28px; }

        /* ---------- Results ---------- */
        .cp-results-head { text-align: center; margin-bottom: 8px; }
        .cp-results-head .eyebrow { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: .14em; color: var(--cp-accent); }
        .cp-results-head h1 { font-size: clamp(28px, 5vw, 44px); font-weight: 900; margin: 10px 0 6px; letter-spacing: -0.02em; }
        .cp-summary { text-align: center; font-size: 17px; color: var(--cp-muted); max-width: 560px; margin: 0 auto 32px; }

        .cp-res-card { background: var(--cp-card); border: 1px solid var(--cp-line); border-radius: 18px; padding: 28px 26px; margin-bottom: 20px; }
        .cp-res-card .label { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .12em; color: var(--cp-muted); margin-bottom: 8px; }
        .cp-res-card h2 { font-size: clamp(22px, 3.6vw, 30px); font-weight: 900; margin: 0 0 12px; color: var(--cp-accent); letter-spacing: -0.01em; }
        .cp-res-card p { margin: 0 0 18px; font-size: 16px; color: var(--cp-ink); }

        .cp-bars { display: flex; flex-direction: column; gap: 14px; }
        .cp-bar .bar-top { display: flex; justify-content: space-between; font-size: 14px; font-weight: 700; margin-bottom: 6px; }
        .cp-bar .bar-top .v { color: var(--cp-muted); }
        .cp-bar.is-top .bar-top { color: var(--cp-accent); }
        .cp-bar-track { height: 12px; background: var(--cp-line); border-radius: 999px; overflow: hidden; }
        .cp-bar-fill { height: 100%; border-radius: 999px; background: #b9beff; transition: width .8s cubic-bezier(.4,0,.2,1); }
        .cp-bar.is-top .cp-bar-fill { background: var(--cp-accent); }

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
        .cp-action .ic { font-size: 22px; margin-bottom: 10px; color: var(--cp-accent); }
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

        /* ---------- Scrolling gallery hero (matches civicprofile.org front page) ---------- */
        html { scroll-behavior: smooth; }
        .cpg {
            position: relative; height: 100vh; min-height: 560px; overflow: hidden;
            background: #0f1024;
        }
        .cpg-rows {
            position: absolute; inset: 0; display: flex; flex-direction: column;
            gap: 18px; padding: 18px 0; justify-content: center;
        }
        .cpg-row { flex: 1 1 0; min-height: 0; overflow: hidden; display: flex; align-items: center; }
        .cpg-track {
            display: flex; gap: 18px; width: max-content; padding-left: 18px;
            will-change: transform; animation: cpg-marquee 60s linear infinite;
        }
        .cpg-track--rev { animation-direction: reverse; animation-duration: 74s; }
        .cpg-row:hover .cpg-track { animation-play-state: paused; }
        .cpg-card {
            flex: 0 0 auto; height: 100%; aspect-ratio: 3 / 4; border-radius: 16px;
            overflow: hidden; background: #1b1d39; box-shadow: 0 14px 34px rgba(0,0,0,.40);
        }
        .cpg-card img { width: 100%; height: 100%; object-fit: cover; display: block; }
        @keyframes cpg-marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }

        /* vignette so the overlay text stays legible over the photos */
        .cpg::after {
            content: ""; position: absolute; inset: 0; pointer-events: none;
            background: radial-gradient(125% 95% at 50% 50%,
                rgba(15,16,36,.34) 0%, rgba(15,16,36,.78) 68%, rgba(15,16,36,.93) 100%);
        }
        .cpg-overlay {
            position: absolute; inset: 0; z-index: 2; display: flex; flex-direction: column;
            align-items: center; justify-content: center; text-align: center; padding: 24px; color: #fff;
        }
        .cpg-eyebrow {
            text-transform: uppercase; letter-spacing: .18em; font-size: 13px; font-weight: 800;
            color: #aeb4ff; margin: 0 0 14px;
        }
        .cpg-title {
            font-size: clamp(40px, 8vw, 84px); line-height: .98; font-weight: 900;
            letter-spacing: -0.02em; margin: 0 0 16px; text-shadow: 0 4px 30px rgba(0,0,0,.45);
        }
        .cpg-lede {
            font-size: clamp(16px, 2.4vw, 20px); max-width: 540px; margin: 0 auto 30px;
            color: rgba(255,255,255,.86); line-height: 1.5;
        }
        .cpg-cta {
            display: inline-flex; align-items: center; gap: 10px; background: var(--cp-accent);
            color: #fff; font-weight: 800; font-size: 17px; text-decoration: none;
            padding: 15px 34px; border-radius: 999px; box-shadow: 0 12px 30px rgba(86,96,254,.45);
            transition: transform .12s, background .12s;
        }
        .cpg-cta:hover { background: var(--cp-accent-dark); transform: translateY(-1px); }
        .cpg-arrow {
            position: absolute; bottom: 22px; left: 50%; z-index: 2; color: rgba(255,255,255,.82);
            display: inline-flex; align-items: center; justify-content: center; text-decoration: none;
            animation: cpg-bounce .95s ease-in-out infinite;
        }
        .cpg-arrow svg { width: 30px; height: 30px; }
        @keyframes cpg-bounce {
            0%,20%,50%,80%,100% { transform: translate(-50%, 0); }
            40% { transform: translate(-50%, -7px); }
            60% { transform: translate(-50%, -4px); }
        }
        .cp { scroll-margin-top: 84px; }
        @media (max-width: 640px) { .cpg-rows { gap: 12px; } .cpg-track { gap: 12px; } }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .cpg-track, .cpg-arrow { animation: none; }
        }
    </style>
    @endverbatim
@endsection

@section('body')
    @php
        // Two rows of portrait cards scrolling in opposite directions; each set is
        // rendered twice so the CSS marquee (translateX -50%) loops seamlessly.
        $cpgRow1 = ['cp-01', 'cp-02', 'cp-03', 'cp-04', 'cp-05', 'cp-06'];
        $cpgRow2 = ['cp-07', 'cp-08', 'cp-09', 'cp-10', 'cp-11', 'cp-12'];
    @endphp
    <section class="cpg">
        <div class="cpg-rows" aria-hidden="true">
            <div class="cpg-row">
                <div class="cpg-track">
                    @foreach (array_merge($cpgRow1, $cpgRow1) as $img)
                        <div class="cpg-card"><img src="/images/civic-profile/{{ $img }}.jpg" alt=""></div>
                    @endforeach
                </div>
            </div>
            <div class="cpg-row">
                <div class="cpg-track cpg-track--rev">
                    @foreach (array_merge($cpgRow2, $cpgRow2) as $img)
                        <div class="cpg-card"><img src="/images/civic-profile/{{ $img }}.jpg" alt=""></div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="cpg-overlay">
            <p class="cpg-eyebrow">National Political Prisoner Coalition</p>
            <h1 class="cpg-title">Civic Profile</h1>
            <p class="cpg-lede">A short, interactive quiz measuring your civic values, your engagement, and your knowledge of the rights and freedoms the NPPC defends.</p>
            <a href="#cp-app" class="cpg-cta">Take the quiz &rarr;</a>
        </div>
        <a href="#cp-app" class="cpg-arrow" aria-label="Scroll to the quiz">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
        </a>
    </section>

    <div id="cp-app" class="cp" aria-live="polite"></div>

    @verbatim
    <script>
    (function () {
        const QUIZ = {
            values: {
                title: 'Values',
                definition: 'Civic values are the beliefs people hold about what makes a good society, a good government, and how individuals and groups should treat one another. This section has no right or wrong answers — it maps what you care about most.',
                scale: ['Strongly disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly agree'],
                // dim: which civic value the item measures; reverse: high agreement counts against the dimension
                questions: [
                    { text: 'People have the right to express political opinions even when most of society finds them offensive.', dim: 'liberty', reverse: false },
                    { text: 'The government should be able to monitor private communications without a warrant whenever it claims national security.', dim: 'liberty', reverse: true },
                    { text: 'Protecting individual privacy from government surveillance is essential to a free society.', dim: 'liberty', reverse: false },
                    { text: 'We have a responsibility to help members of our community even when it costs us something personally.', dim: 'solidarity', reverse: false },
                    { text: 'Social problems are best solved by individuals acting alone rather than by people organizing together.', dim: 'solidarity', reverse: true },
                    { text: 'Standing in solidarity with people who are being mistreated is a basic civic duty.', dim: 'solidarity', reverse: false },
                    { text: 'Everyone accused of a crime deserves a fair trial and a lawyer, no matter what the charge is.', dim: 'justice', reverse: false },
                    { text: 'It is acceptable to deny some people due process if the accusations against them are serious enough.', dim: 'justice', reverse: true },
                    { text: 'The same laws should apply equally to the powerful and the powerless.', dim: 'justice', reverse: false },
                    { text: 'Peaceful protest is a legitimate and important way to influence the government.', dim: 'participation', reverse: false },
                    { text: 'Ordinary people have little reason to get involved in politics.', dim: 'participation', reverse: true },
                    { text: 'Speaking out against injustice is worth the personal risk it can carry.', dim: 'participation', reverse: false }
                ]
            },
            engagement: {
                title: 'Engagement',
                definition: 'Civic engagement is the set of actions and behaviors people take to make a positive contribution to society. Think back over the past year as you answer.',
                scale: ['Never', 'Rarely', 'Sometimes', 'Often'],
                prompt: 'In the past year, how often have you…',
                questions: [
                    { text: 'Voted in an election you were eligible for (local, state, or national)?' },
                    { text: 'Contacted an elected official or government agency about an issue?' },
                    { text: 'Attended a protest, march, rally, or demonstration?' },
                    { text: 'Signed a petition, online or in person?' },
                    { text: 'Donated to a cause, campaign, or nonprofit?' },
                    { text: 'Volunteered your time for a community organization or cause?' },
                    { text: 'Attended a public meeting such as a city council, school board, or town hall?' },
                    { text: 'Shared news or reliable information about a civic or political issue with others?' },
                    { text: 'Supported someone who is incarcerated — through letters, commissary, or advocacy?' },
                    { text: 'Boycotted or deliberately chose a product for political or ethical reasons?' }
                ],
                levels: [
                    { min: 0, max: 7, name: 'Civic Observer', desc: 'You are paying attention, mostly from the sidelines for now. Small, concrete steps — signing a petition, writing a single letter to a prisoner — are an easy place to begin.' },
                    { min: 8, max: 15, name: 'Civic Participant', desc: 'You take part when it counts. You vote, you speak up, and you lend support to the causes you believe in.' },
                    { min: 16, max: 23, name: 'Civic Advocate', desc: 'You are consistently active across many forms of civic life — from the ballot box to the streets to direct support for people in need.' },
                    { min: 24, max: 30, name: 'Civic Leader', desc: 'You live civic engagement. Few forms of participation are foreign to you: you organize, show up, give, and bring others with you.' }
                ]
            },
            knowledge: {
                title: 'Knowledge',
                definition: 'Civic knowledge is an understanding of how our political system works, the rights and responsibilities of people within it, and the key events, movements, and figures that shaped it. Each question has one correct answer.',
                questions: [
                    { text: 'Which amendment to the U.S. Constitution protects freedom of speech, the press, assembly, and the right to petition the government?', options: ['The First Amendment', 'The Fourth Amendment', 'The Fifth Amendment', 'The Tenth Amendment'], answer: 0 },
                    { text: 'The legal principle of "habeas corpus" protects a person from:', options: ['Being forced to testify against themselves', 'Unlawful or indefinite detention without being brought before a court', 'Cruel and unusual punishment', 'Being tried twice for the same crime'], answer: 1 },
                    { text: 'Which amendment protects against unreasonable searches and seizures?', options: ['The First Amendment', 'The Second Amendment', 'The Fourth Amendment', 'The Sixth Amendment'], answer: 2 },
                    { text: 'The requirement that police inform you of your right to remain silent and to an attorney comes from which landmark Supreme Court decision?', options: ['Brown v. Board of Education', 'Miranda v. Arizona', 'Marbury v. Madison', 'Plessy v. Ferguson'], answer: 1 },
                    { text: 'A "political prisoner" is generally understood to be someone imprisoned primarily because of:', options: ['Violent crimes against individuals', 'Their political beliefs, activism, or association', 'Financial fraud', 'Repeated traffic violations'], answer: 1 },
                    { text: 'Which of these is NOT one of the three branches of the U.S. federal government?', options: ['Legislative', 'Executive', 'Judicial', 'Administrative'], answer: 3 },
                    { text: 'The Sixth Amendment guarantees which right to people accused of crimes?', options: ['A speedy and public trial by an impartial jury', 'The right to bear arms', 'Protection from quartering soldiers', 'The right to vote at age 18'], answer: 0 },
                    { text: 'Which 1948 document sets out fundamental human rights, including freedom from arbitrary arrest and detention?', options: ['The Geneva Conventions', 'The Universal Declaration of Human Rights', 'The Magna Carta', 'The Treaty of Versailles'], answer: 1 },
                    { text: 'At the federal level, the power to grant clemency — including pardons and commutations — belongs to:', options: ['The Supreme Court', 'Congress', 'The President', 'The Attorney General'], answer: 2 },
                    { text: 'Which civil rights leader wrote "Letter from Birmingham Jail" while jailed for nonviolent protest?', options: ['Malcolm X', 'Martin Luther King Jr.', 'Thurgood Marshall', 'Medgar Evers'], answer: 1 },
                    { text: 'The First Amendment’s protection of the right "peaceably to assemble" most directly protects:', options: ['Owning firearms', 'Public protests and demonstrations', 'The right to a jury trial', 'Freedom from taxation'], answer: 1 },
                    { text: 'Being held in jail before trial can often be avoided through:', options: ['Bail or pretrial release', 'An appeal', 'A pardon', 'Jury nullification'], answer: 0 }
                ]
            },
            profiles: {
                liberty: { name: 'The Civil Libertarian', desc: 'You place individual freedom at the center of your civic worldview — free expression, privacy, and firm limits on the power of the state. You are the kind of person who defends the right to dissent even when the speech is unpopular.' },
                solidarity: { name: 'The Community Builder', desc: 'You believe a just society is built together. Mutual aid, collective responsibility, and standing with people who are mistreated are, to you, the heart of civic life.' },
                justice: { name: 'The Justice Seeker', desc: 'Fairness and due process anchor your values. You believe the law must apply equally to the powerful and the powerless, and that everyone — whatever the accusation — deserves a fair hearing.' },
                participation: { name: 'The Engaged Citizen', desc: 'To you, democracy is a verb. Showing up, speaking out, and accepting some personal risk to challenge injustice are basic civic duties.' }
            },
            dimLabels: { liberty: 'Liberty', solidarity: 'Solidarity', justice: 'Justice & Due Process', participation: 'Participation' },
            knowledgeTiers: [
                { min: 90, name: 'Civic Scholar' },
                { min: 70, name: 'Well Informed' },
                { min: 50, name: 'Building Knowledge' },
                { min: 0, name: 'Just Getting Started' }
            ]
        };

        // Build the linear step list.
        const PARTS = ['values', 'engagement', 'knowledge'];
        const steps = [{ type: 'intro' }];
        PARTS.forEach(function (p) {
            steps.push({ type: 'part', part: p });
            QUIZ[p].questions.forEach(function (_, i) { steps.push({ type: 'q', part: p, i: i }); });
        });
        steps.push({ type: 'results' });

        const answers = { values: [], engagement: [], knowledge: [] };
        let cur = 0;
        let locked = false;
        const root = document.getElementById('cp-app');

        const totalQ = QUIZ.values.questions.length + QUIZ.engagement.questions.length + QUIZ.knowledge.questions.length;
        function answeredCount() {
            return PARTS.reduce(function (n, p) {
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
            if (window.scrollTo) window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function select(part, i, val) {
            if (locked) return;
            answers[part][i] = val;
            locked = true;
            render();
            setTimeout(function () { go(cur + 1); }, 260);
        }

        function partProgress(part, i) {
            const labels = { values: 'Part 1 · Values', engagement: 'Part 2 · Engagement', knowledge: 'Part 3 · Knowledge' };
            return '<div class="cp-progress">'
                + '<div class="cp-progress-meta"><span class="part">' + labels[part] + '</span>'
                + '<span>Question ' + (i + 1) + ' of ' + QUIZ[part].questions.length + '</span></div>'
                + '<div class="cp-progress-track"><div class="cp-progress-fill" style="width:' + Math.round(answeredCount() / totalQ * 100) + '%"></div></div>'
                + '</div>';
        }

        function render() {
            const s = steps[cur];
            if (s.type === 'intro') return renderIntro();
            if (s.type === 'part') return renderPartIntro(s.part);
            if (s.type === 'q') return renderQuestion(s.part, s.i);
            if (s.type === 'results') return renderResults();
        }

        function renderIntro() {
            root.innerHTML =
                '<div class="cp-hero">'
                + '<p class="cp-eyebrow">National Political Prisoner Coalition</p>'
                + '<h1>Discover your Civic Profile</h1>'
                + '<p class="cp-lede">An interactive quiz that measures your civic <strong>values</strong>, your <strong>engagement</strong>, and your <strong>knowledge</strong> of the rights, due process, and freedom to dissent that the NPPC defends.</p>'
                + '</div>'
                + '<div class="cp-parts">'
                + partCard('1', 'Values', 'What you believe makes for a good society and a fair government.')
                + partCard('2', 'Engagement', 'The actions you take to contribute to your community and the world.')
                + partCard('3', 'Knowledge', 'How well you know your rights, the system, and the history of dissent.')
                + '</div>'
                + '<div style="text-align:center">'
                + '<button class="cp-btn" id="cp-start">Let’s get started</button>'
                + '<p class="cp-note">Takes about 5 minutes · ' + totalQ + ' questions · No sign-up required</p>'
                + '</div>';
            document.getElementById('cp-start').addEventListener('click', function () { go(cur + 1); });
        }

        function partCard(n, title, desc) {
            return '<div class="cp-part-card"><span class="n">' + n + '</span><h3>' + title + '</h3><p>' + desc + '</p></div>';
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
                + '<span class="cp-part-badge">' + esc(QUIZ[part].title) + '</span>'
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
                + '<circle cx="52" cy="52" r="' + R + '" fill="none" stroke="#e4e6f1" stroke-width="9"></circle>'
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
                + '<circle cx="52" cy="52" r="' + R + '" fill="none" stroke="#e4e6f1" stroke-width="9"></circle>'
                + '<circle cx="52" cy="52" r="' + R + '" fill="none" stroke="#5660fe" stroke-width="9" stroke-linecap="round" stroke-dasharray="' + C.toFixed(1) + '" stroke-dashoffset="' + (C * (1 - e.sum / e.max)).toFixed(1) + '"></circle>'
                + '</svg><div class="pct"><span class="num">' + e.sum + '</span><span class="den">of ' + e.max + '</span></div></div>'
                + '<div><div class="cp-score-tier">' + esc(e.level.name) + '</div><p style="margin:0;color:var(--cp-muted)">' + esc(e.level.desc) + '</p></div>'
                + '</div></div>'

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
            const text = 'I just discovered my Civic Profile: ' + profile.name + '. Find yours from the National Political Prisoner Coalition.';
            const share = document.getElementById('cp-share');
            share.innerHTML =
                '<a href="https://twitter.com/intent/tweet?text=' + encodeURIComponent(text) + '&url=' + encodeURIComponent(url) + '" target="_blank" rel="noopener" aria-label="Share on X"><svg viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>'
                + '<a href="https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url) + '" target="_blank" rel="noopener" aria-label="Share on Facebook"><svg viewBox="0 0 24 24"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.412c0-3.027 1.792-4.7 4.533-4.7 1.312 0 2.686.235 2.686.235v2.97h-1.513c-1.491 0-1.956.93-1.956 1.886v2.27h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073"/></svg></a>';

            document.getElementById('cp-retake').addEventListener('click', function () {
                answers.values = []; answers.engagement = []; answers.knowledge = [];
                go(0);
            });
        }

        function actionCard(href, ic, title, desc) {
            return '<a class="cp-action" href="' + href + '"><div class="ic">' + ic + '</div><strong>' + title + '</strong><span>' + desc + '</span></a>';
        }

        render();
    })();
    </script>
    @endverbatim
@endsection
