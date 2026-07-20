@extends('app')

@section('title', 'Youth — National Political Prisoner Coalition')

@section('head')
    <meta name="description" content="Too young to vote, old enough to be a target. The NPPC's youth page: the young people America has jailed for their politics — from Scottsboro to the Freedom Rides to today — and what you can do about it.">
    @verbatim
    <style>
        /* NPPC Youth page — structure inspired by movement landing pages,
           all content original, built from the NPPC database.
           Dark is the site default; html[data-theme="light"] flips. */
        .yp {
            --yp-bg: #000000;
            --yp-surface: #16181f;
            --yp-surface-2: #1a1a2e;
            --yp-ink: #ffffff;
            --yp-ink-rgb: 255,255,255;
            --yp-muted: #a3a9b6;
            --yp-line: rgba(255,255,255,0.12);
            --yp-accent: #5660fe;
            --yp-accent-2: #8b93ff;
            --yp-coral: #f25c54;
        }
        html[data-theme="light"] .yp {
            --yp-bg: #ffffff;
            --yp-surface: #ffffff;
            --yp-surface-2: #eef0f7;
            --yp-ink: #15171c;
            --yp-ink-rgb: 21,23,28;
            --yp-muted: #686868;
            --yp-line: rgba(21,23,28,0.12);
            --yp-accent-2: #4049d6;
        }
        .yp { background: var(--yp-bg); color: var(--yp-ink); }
        .yp * { box-sizing: border-box; }
        .yp-wrap { max-width: 1160px; margin: 0 auto; padding: 0 24px; }
        .yp-eyebrow { font-size: 13px; font-weight: 800; letter-spacing: .22em; text-transform: uppercase; color: var(--yp-coral); margin: 0 0 18px; }
        .yp a.yp-btn {
            display: inline-block; background: var(--yp-accent); color: #fff; text-decoration: none;
            font-weight: 700; font-size: 15px; letter-spacing: .04em; text-transform: uppercase;
            padding: 15px 34px; border-radius: 4px; transition: background .15s;
        }
        .yp a.yp-btn:hover { background: #4049d6; }
        .yp a.yp-btn--ghost { background: transparent; border: 2px solid var(--yp-accent); color: var(--yp-accent-2); }
        .yp a.yp-btn--ghost:hover { background: rgba(86,96,254,.12); }

        /* ---- hero: giant stacked headline ---- */
        .yp-hero { padding: 90px 0 60px; border-bottom: 1px solid var(--yp-line); }
        .yp-hero h1 {
            margin: 0; font-weight: 900; text-transform: uppercase; letter-spacing: -0.015em;
            font-size: clamp(46px, 9vw, 124px); line-height: .96;
        }
        .yp-hero h1 .a { color: var(--yp-accent); }
        .yp-hero h1 .c { color: var(--yp-coral); }
        .yp-hero-sub { max-width: 640px; font-size: clamp(17px, 2.2vw, 21px); line-height: 1.6; color: var(--yp-muted); margin: 34px 0 30px; }
        .yp-hero-ctas { display: flex; gap: 14px; flex-wrap: wrap; }

        /* ---- movement statement ---- */
        .yp-statement { padding: 76px 0; border-bottom: 1px solid var(--yp-line); }
        .yp-statement p.big { font-size: clamp(22px, 3.2vw, 32px); font-weight: 700; line-height: 1.35; max-width: 900px; margin: 0 0 18px; }
        .yp-statement p.small { color: var(--yp-muted); font-size: 17px; line-height: 1.65; max-width: 760px; margin: 0; }

        /* ---- arrested-young story rail ---- */
        .yp-stories { padding: 76px 0; border-bottom: 1px solid var(--yp-line); }
        .yp-stories h2 { font-size: clamp(28px, 4.2vw, 44px); font-weight: 900; margin: 0 0 10px; }
        .yp-stories .lede { color: var(--yp-muted); font-size: 17px; margin: 0 0 34px; max-width: 720px; }
        .yp-rail { display: grid; grid-auto-flow: column; grid-auto-columns: 260px; gap: 18px; overflow-x: auto; padding-bottom: 14px; scroll-snap-type: x mandatory; }
        .yp-card {
            scroll-snap-align: start; background: var(--yp-surface); border: 1px solid var(--yp-line);
            border-radius: 12px; overflow: hidden; text-decoration: none; color: var(--yp-ink);
            display: flex; flex-direction: column; transition: transform .12s, border-color .12s;
        }
        .yp-card:hover { transform: translateY(-3px); border-color: var(--yp-accent); }
        .yp-card img { width: 100%; height: 280px; object-fit: cover; object-position: top; display: block; filter: grayscale(100%); }
        .yp-card:hover img { filter: none; }
        .yp-card .body { padding: 16px 16px 18px; }
        .yp-age { display: inline-block; background: var(--yp-coral); color: #16181f; font-weight: 900; font-size: 12px; letter-spacing: .08em; text-transform: uppercase; border-radius: 999px; padding: 4px 12px; margin-bottom: 10px; }
        .yp-card h3 { margin: 0 0 6px; font-size: 19px; font-weight: 800; }
        .yp-card p { margin: 0; font-size: 13.5px; line-height: 1.5; color: var(--yp-muted); }

        /* ---- three explainers ---- */
        .yp-qa { padding: 76px 0; border-bottom: 1px solid var(--yp-line); }
        .yp-qa h2 { font-size: clamp(28px, 4.2vw, 44px); font-weight: 900; margin: 0 0 34px; }
        .yp-qa-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 22px; }
        .yp-qa-col { background: var(--yp-surface); border: 1px solid var(--yp-line); border-radius: 12px; padding: 26px 24px; }
        .yp-qa-col .n { color: var(--yp-accent-2); font-weight: 900; font-size: 14px; letter-spacing: .12em; margin-bottom: 12px; }
        .yp-qa-col h3 { margin: 0 0 12px; font-size: 20px; font-weight: 800; line-height: 1.3; }
        .yp-qa-col p { margin: 0; font-size: 15px; line-height: 1.65; color: var(--yp-muted); }
        @media (max-width: 860px) { .yp-qa-grid { grid-template-columns: 1fr; } }

        /* ---- join band ---- */
        .yp-join { padding: 76px 0; border-bottom: 1px solid var(--yp-line); background: var(--yp-surface-2); }
        .yp-join-grid { display: grid; grid-template-columns: 1.1fr 1fr; gap: 44px; align-items: center; }
        .yp-join h2 { font-size: clamp(28px, 4.4vw, 48px); font-weight: 900; line-height: 1.05; margin: 0 0 14px; }
        .yp-join p { color: var(--yp-muted); font-size: 16px; line-height: 1.6; margin: 0 0 22px; max-width: 480px; }
        .yp-signup { display: flex; gap: 10px; max-width: 480px; }
        .yp-signup input {
            flex: 1; min-width: 0; background: var(--yp-bg); color: var(--yp-ink);
            border: 1px solid var(--yp-line); border-radius: 4px; padding: 14px 16px; font-size: 15px;
        }
        .yp-signup button {
            background: var(--yp-coral); color: #16181f; border: none; border-radius: 4px;
            font-weight: 900; font-size: 14px; letter-spacing: .08em; text-transform: uppercase;
            padding: 0 26px; cursor: pointer;
        }
        .yp-signup button:hover { filter: brightness(1.08); }
        .yp-actions { display: grid; gap: 12px; }
        .yp-action { display: flex; align-items: baseline; gap: 12px; background: var(--yp-surface); border: 1px solid var(--yp-line); border-radius: 10px; padding: 16px 18px; text-decoration: none; color: var(--yp-ink); transition: border-color .12s; }
        .yp-action:hover { border-color: var(--yp-accent); }
        .yp-action strong { font-size: 16px; }
        .yp-action span { font-size: 13.5px; color: var(--yp-muted); }
        @media (max-width: 860px) { .yp-join-grid { grid-template-columns: 1fr; } }

        /* ---- FAQ accordion ---- */
        .yp-faq { padding: 76px 0; border-bottom: 1px solid var(--yp-line); }
        .yp-faq h2 { font-size: clamp(28px, 4.2vw, 44px); font-weight: 900; margin: 0 0 26px; }
        .yp-faq details { border-bottom: 1px solid var(--yp-line); }
        .yp-faq summary {
            cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center;
            gap: 18px; padding: 22px 0; font-size: 19px; font-weight: 800;
        }
        .yp-faq summary::-webkit-details-marker { display: none; }
        .yp-faq summary::after { content: '+'; color: var(--yp-accent-2); font-size: 26px; font-weight: 400; flex: 0 0 auto; }
        .yp-faq details[open] summary::after { content: '–'; }
        .yp-faq .a { padding: 0 0 24px; max-width: 820px; color: var(--yp-muted); font-size: 16px; line-height: 1.7; }
        .yp-faq .a a { color: var(--yp-accent-2); }

        /* ---- history band (always dark, like the museum) ---- */
        .yp-history { padding: 84px 0; background: #0a0a12; color: #fff; border-bottom: 1px solid rgba(255,255,255,.08); }
        .yp-history-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 44px; align-items: center; }
        .yp-history h2 { font-size: clamp(28px, 4.4vw, 48px); font-weight: 900; line-height: 1.05; margin: 0 0 16px; }
        .yp-history p { color: rgba(255,255,255,.65); font-size: 16px; line-height: 1.65; margin: 0 0 24px; max-width: 480px; }
        .yp-history .faces { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .yp-history .faces img { width: 100%; aspect-ratio: 5/7; object-fit: cover; border-radius: 8px; filter: grayscale(100%); display: block; }
        @media (max-width: 860px) { .yp-history-grid { grid-template-columns: 1fr; } }

        /* ---- donate banner ---- */
        .yp-donate { padding: 64px 0; background: var(--yp-accent); color: #fff; }
        .yp-donate .yp-wrap { display: flex; align-items: center; justify-content: space-between; gap: 28px; flex-wrap: wrap; }
        .yp-donate h2 { font-size: clamp(22px, 3.2vw, 34px); font-weight: 900; margin: 0; max-width: 720px; line-height: 1.25; }
        .yp-donate a.yp-btn { background: #fff; color: #15171c; }
        .yp-donate a.yp-btn:hover { background: #e8e9ff; }
    </style>
    @endverbatim
@endsection

@section('body')
@php
    // Featured people arrested young — ages computed from our database
    // (birthdate → first arrest). Photos come from each live record; anyone
    // missing a photo simply drops out of the rail.
    $featured = [
        ['slug' => 'billy-frank-jr', 'age' => 14, 'line' => 'First arrested for fishing in his own tribe\'s waters. He would be arrested more than 50 times defending Nisqually treaty rights.'],
        ['slug' => 'gary-tyler', 'age' => 16, 'line' => 'Convicted by an all-white jury after a school-desegregation mob attack in Louisiana. He served 41 years.'],
        ['slug' => 'charles-greenlee', 'age' => 16, 'line' => 'The youngest of the Groveland Four, framed in Jim Crow Florida. Pardoned decades after his death.'],
        ['slug' => 'joffre-stewart', 'age' => 17, 'line' => 'Jailed as a teenager for refusing to register for the WWII draft — the start of a lifetime of pacifist resistance.'],
        ['slug' => 'haywood-patterson', 'age' => 18, 'line' => 'One of the nine Scottsboro defendants. The case became a generation\'s lesson in what Southern courts did to Black youth.'],
        ['slug' => 'george-jackson', 'age' => 19, 'line' => 'Sentenced one year to life over a $70 robbery. He came of age, wrote, and died inside.'],
        ['slug' => 'stokely-carmichael', 'age' => 19, 'line' => 'Arrested on the Freedom Rides before he could legally buy a drink. He kept riding.'],
        ['slug' => 'mollie-steimer', 'age' => 20, 'line' => 'A garment worker handed 15 years under the Sedition Act for anti-war leaflets thrown from a rooftop.'],
        ['slug' => 'bettina-aptheker', 'age' => 20, 'line' => 'Arrested in the Sproul Hall sit-in that made the Free Speech Movement a national story.'],
    ];
    $people = \App\Models\Prisoner::whereIn('slug', collect($featured)->pluck('slug'))->get()->keyBy('slug');
@endphp

<div class="yp">

    {{-- ============ hero ============ --}}
    <section class="yp-hero">
        <div class="yp-wrap">
            <p class="yp-eyebrow">NPPC Youth</p>
            <h1>Too young<br>to vote.<br><span class="a">Old enough</span><br><span class="c">to be</span><br><span class="c">a target.</span></h1>
            <p class="yp-hero-sub">Some of the people in our database were arrested before they could drive. Many more before they could vote. Young people have always been at the front of American movements — and the state has always met them there.</p>
            <div class="yp-hero-ctas">
                <a class="yp-btn" href="#yp-join">Join us</a>
                <a class="yp-btn yp-btn--ghost" href="/nppc-quiz">Take the quiz</a>
            </div>
        </div>
    </section>

    {{-- ============ movement statement ============ --}}
    <section class="yp-statement">
        <div class="yp-wrap">
            <p class="yp-eyebrow">Why this page exists</p>
            <p class="big">The Scottsboro Nine were teenagers. The Freedom Riders were college students. The people filling protest dockets today are the same age.</p>
            <p class="small">The National Political Prisoner Coalition documents more than 7,000 Americans imprisoned across our history for what they believed, said, or organized. A striking number of them were young — students, apprentices, teenagers pulled off buses and out of sit-ins. This page is for the generation being arrested right now, and everyone who stands with them.</p>
        </div>
    </section>

    {{-- ============ arrested-young rail ============ --}}
    <section class="yp-stories">
        <div class="yp-wrap">
            <h2>Arrested young</h2>
            <p class="lede">Real people from our database, with the age at which each was first arrested. Every card opens a full record you can read and act on.</p>
            <div class="yp-rail">
                @foreach ($featured as $f)
                    @php $p = $people[$f['slug']] ?? null; @endphp
                    @if ($p && $p->photo_url)
                        <a class="yp-card" href="/prisoner/{{ $p->slug }}">
                            <img src="{{ $p->photo_url }}" alt="{{ $p->name }}" loading="lazy">
                            <div class="body">
                                <span class="yp-age">Arrested at {{ $f['age'] }}</span>
                                <h3>{{ $p->name }}</h3>
                                <p>{{ $f['line'] }}</p>
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ three explainers ============ --}}
    <section class="yp-qa">
        <div class="yp-wrap">
            <h2>Three things to understand</h2>
            <div class="yp-qa-grid">
                <div class="yp-qa-col">
                    <div class="n">01</div>
                    <h3>Why are young people targeted?</h3>
                    <p>Because movements run on them. Students and young workers have the least to lose, the most energy to give, and the longest futures to threaten. Prosecutors know a felony charge at 19 can shadow a whole life — and that the fear of one can empty a picket line before it forms.</p>
                </div>
                <div class="yp-qa-col">
                    <div class="n">02</div>
                    <h3>What does it look like today?</h3>
                    <p>Conspiracy and terrorism enhancements for property damage, federal charges for protest conduct, gang statutes stretched over student groups, visa revocations for campus speech. The tools change; the pattern — reach for the heaviest charge available against the youngest defendants — is the oldest one in our database.</p>
                </div>
                <div class="yp-qa-col">
                    <div class="n">03</div>
                    <h3>What actually helps?</h3>
                    <p>Court support fills the benches. Letters keep people connected inside. Defense funds keep families afloat. Attention keeps cases from disappearing. Every acquittal and clemency in our records has the same footnote: someone outside refused to move on.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ join band ============ --}}
    <section class="yp-join" id="yp-join">
        <div class="yp-wrap yp-join-grid">
            <div>
                <h2>Sign up &amp; stand with them</h2>
                <p>Get case updates, letter-writing calls, and court dates. No spam — just the moments when showing up matters.</p>
                <form class="yp-signup" action="/sign-up" method="POST">
                    @csrf
                    <input type="email" name="email" placeholder="Your email" required aria-label="Email address">
                    <button type="submit">Sign up</button>
                </form>
            </div>
            <div class="yp-actions">
                <a class="yp-action" href="/prisoner-outreach"><strong>Write a letter</strong><span>Someone inside reads it this week.</span></a>
                <a class="yp-action" href="/petitions"><strong>Sign a petition</strong><span>Active campaigns for release and clemency.</span></a>
                <a class="yp-action" href="/volunteer"><strong>Volunteer</strong><span>Research, court support, events, web — every role has a coordinator.</span></a>
                <a class="yp-action" href="/nppc-quiz"><strong>Take the NPPC Quiz</strong><span>Where do you stand? Five minutes, no sign-up.</span></a>
            </div>
        </div>
    </section>

    {{-- ============ FAQ ============ --}}
    <section class="yp-faq">
        <div class="yp-wrap">
            <h2>Frequently asked questions</h2>
            <details>
                <summary>What counts as a political prisoner?</summary>
                <div class="a">Someone imprisoned primarily because of their political beliefs, activism, or associations — from sedition and conspiracy prosecutions to protest cases where the charge is ordinary but the targeting is not. Our <a href="/database">database</a> documents more than 7,000 such cases across American history, with sources on every record.</div>
            </details>
            <details>
                <summary>I'm a student. Is it safe to get involved?</summary>
                <div class="a">Know your rights before you need them: you have the right to remain silent, the right to a lawyer, and the right not to consent to searches. Organizations like the National Lawyers Guild train legal observers and run jail-support hotlines for exactly this reason. Most support work — letters, petitions, court attendance, fundraising — carries no legal risk at all.</div>
            </details>
            <details>
                <summary>Does writing to a prisoner actually matter?</summary>
                <div class="a">Ask anyone who has been inside. Mail is proof that the outside remembers you — and prisons treat well-supported prisoners differently, because someone is watching. Our <a href="/prisoner-outreach">outreach page</a> walks you through your first letter, including what you can and can't send.</div>
            </details>
            <details>
                <summary>Where should I start learning?</summary>
                <div class="a">Start with the <a href="/history">history</a> and <a href="/timeline">timeline</a>, walk the <a href="/museum">3D museum</a>, read original documents in the <a href="/archive">archive</a>, or test yourself with the <a href="/nppc-quiz">NPPC Quiz</a>. Every path leads back to real people and real cases.</div>
            </details>
        </div>
    </section>

    {{-- ============ history band ============ --}}
    <section class="yp-history">
        <div class="yp-wrap yp-history-grid">
            <div>
                <p class="yp-eyebrow">The long view</p>
                <h2>This has happened before. That's the point.</h2>
                <p>Every generation's dissenters were called dangerous, and every generation's charges looked permanent — until they didn't. Walk the museum, read the history, and see where today's cases fit.</p>
                <div class="yp-hero-ctas">
                    <a class="yp-btn" href="/museum">Enter the museum</a>
                    <a class="yp-btn yp-btn--ghost" href="/history">Read the history</a>
                </div>
            </div>
            <div class="faces" aria-hidden="true">
                <img src="/images/civic-profile/pp-08.jpg" alt="">
                <img src="/images/civic-profile/pp-05.jpg" alt="">
                <img src="/images/civic-profile/pp-10.jpg" alt="">
            </div>
        </div>
    </section>

    {{-- ============ donate ============ --}}
    <section class="yp-donate">
        <div class="yp-wrap">
            <h2>We document more than 7,000 political prisoners across American history — and support the ones inside right now.</h2>
            <a class="yp-btn" href="/donate">Donate</a>
        </div>
    </section>

</div>
@endsection
