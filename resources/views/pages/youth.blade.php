@extends('app')

@section('title', 'Youth — National Political Prisoner Coalition')

@section('head')
    <meta name="description" content="Too young to vote, old enough to be a target. The NPPC's youth page: the young people America has jailed for their politics — from Scottsboro to the Freedom Rides to today — and what you can do about it.">
    @verbatim
    <style>
        /* NPPC Youth — editorial style on a black ground: heavy white
           grotesque type, floating snapshot portraits, cream sign-up band,
           photo-strip merch band, copper donate band. Fixed palette in both
           themes (like the report pages). */
        /* Verlag, the site's licensed face, in place of a Helvetica/Avenir
           stack that read as a different site from the rest of the archive. */
        .yp { background: #000; color: #fff; font-family: Verlag A, Verlag B, Verlag, 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        .yp * { box-sizing: border-box; }
        .yp-wrap { max-width: 1120px; margin: 0 auto; padding: 0 24px; }
        .yp-label { font-size: 14px; font-weight: 700; margin: 0 0 26px; }
        .yp a.yp-btn {
            display: inline-flex; align-items: center; gap: 10px; background: #5660fe; color: #fff;
            text-decoration: none; font-weight: 700; font-size: 15px; padding: 14px 26px; border-radius: 4px;
            transition: background .15s;
        }
        .yp a.yp-btn:hover { background: #4049d6; }
        .yp a.yp-link { color: #fff; font-weight: 700; text-decoration: underline; text-underline-offset: 4px; }
        .yp a.yp-link:hover { opacity: .65; }

        /* ---- statement hero: centered bold text, floating snapshots ---- */
        .yp-intro { position: relative; min-height: 92vh; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .yp-intro .statement {
            position: relative; z-index: 2; max-width: 760px; margin: 0 auto; padding: 0 24px;
            text-align: center; font-size: clamp(22px, 3.4vw, 32px); font-weight: 800; line-height: 1.32; letter-spacing: -0.01em;
        }
        .yp-intro .statement .accent { color: #8b93ff; }
        .yp-snap {
            position: absolute; width: clamp(70px, 9vw, 120px); aspect-ratio: 1/1.15; object-fit: cover; object-position: top;
            box-shadow: 0 8px 26px rgba(0,0,0,.6); will-change: transform;
        }
        .yp-intro .cue { position: absolute; bottom: 26px; left: 50%; transform: translateX(-50%); font-size: 12px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: rgba(255,255,255,.45); }

        /* ---- giant campaign headline ---- */
        .yp-campaign { padding: 110px 0 90px; }
        .yp-campaign h1 { margin: 0 0 28px; font-weight: 800; letter-spacing: -0.02em; font-size: clamp(44px, 7.6vw, 96px); line-height: 1.0; }
        .yp-campaign h1 .c { color: #f25c54; }
        .yp-campaign p { max-width: 680px; font-size: 18px; line-height: 1.65; color: rgba(255,255,255,.65); margin: 0 0 30px; }

        /* ---- arrested-young carousel ---- */
        .yp-stories { padding: 40px 0 90px; }
        .yp-stories .yp-label { margin-bottom: 8px; }
        .yp-stories h2 { font-size: clamp(30px, 4.4vw, 48px); font-weight: 800; letter-spacing: -0.015em; margin: 0 0 30px; }
        .yp-rail { display: grid; grid-auto-flow: column; grid-auto-columns: 250px; gap: 20px; overflow-x: auto; padding: 4px 4px 10px; scroll-snap-type: x mandatory; scrollbar-width: none; }
        .yp-rail::-webkit-scrollbar { display: none; }
        .yp-card { scroll-snap-align: start; text-decoration: none; color: #fff; }
        .yp-card img { width: 100%; height: 270px; object-fit: cover; object-position: top; display: block; border-radius: 6px; }
        .yp-age { display: inline-block; margin: 12px 0 8px; background: #fff; color: #111114; font-weight: 800; font-size: 11.5px; letter-spacing: .08em; text-transform: uppercase; border-radius: 999px; padding: 4px 12px; }
        .yp-card h3 { margin: 0 0 6px; font-size: 18px; font-weight: 800; }
        .yp-card p { margin: 0; font-size: 13.5px; line-height: 1.5; color: rgba(255,255,255,.6); }
        .yp-dots { display: flex; gap: 9px; justify-content: center; margin-top: 22px; }
        .yp-dots button { width: 7px; height: 7px; border-radius: 50%; border: none; padding: 0; background: #3a3a44; cursor: pointer; }
        .yp-dots button.on { background: #8b93ff; }

        /* ---- explainer questions (thin-ruled, light) ---- */
        .yp-qa { padding: 60px 0 90px; }
        .yp-qa h2 { font-size: clamp(30px, 4.4vw, 48px); font-weight: 800; letter-spacing: -0.015em; margin: 0 0 8px; }
        .yp-qa .item { border-bottom: 1px solid rgba(255,255,255,.16); padding: 30px 0; }
        .yp-qa .item h3 { margin: 0 0 12px; font-size: clamp(20px, 2.6vw, 26px); font-weight: 800; }
        .yp-qa .item p { margin: 0; max-width: 780px; font-size: 16.5px; line-height: 1.7; color: rgba(255,255,255,.65); }

        /* ---- sign-up band (cream) ---- */
        .yp-join { background: #efe9e1; color: #111114; padding: 100px 0; text-align: center; }
        .yp-join h2 { font-size: clamp(38px, 6vw, 72px); font-weight: 800; letter-spacing: -0.02em; line-height: 1.05; margin: 0 0 44px; }
        .yp-signup { display: flex; gap: 14px; max-width: 640px; margin: 0 auto 14px; }
        .yp-signup input {
            flex: 1; min-width: 0; background: #fff; color: #111114; border: 1px solid #6b6b72;
            border-radius: 2px; padding: 17px 16px; font-size: 15px; font-family: inherit;
        }
        .yp-signup button {
            background: #5660fe; color: #fff; border: none; border-radius: 4px; font-family: inherit;
            font-weight: 700; font-size: 15px; padding: 0 28px; cursor: pointer;
        }
        .yp-signup button:hover { background: #4049d6; }
        .yp-join .fine { font-size: 12.5px; color: #7a7a80; margin: 0; }
        @media (max-width: 620px) { .yp-signup { flex-direction: column; } .yp-signup button { padding: 15px; } }

        /* ---- actions row ---- */
        .yp-acts { padding: 90px 0 70px; }
        .yp-acts-grid { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 18px; }
        .yp-act { border: 1px solid rgba(255,255,255,.2); border-radius: 8px; padding: 24px 22px; text-decoration: none; color: #fff; transition: border-color .12s, transform .12s; }
        .yp-act:hover { border-color: #8b93ff; transform: translateY(-2px); }
        .yp-act strong { display: block; font-size: 16.5px; font-weight: 800; margin-bottom: 8px; }
        .yp-act span { font-size: 13.5px; color: rgba(255,255,255,.6); line-height: 1.5; }
        @media (max-width: 900px) { .yp-acts-grid { grid-template-columns: repeat(2, minmax(0,1fr)); } }
        @media (max-width: 560px) { .yp-acts-grid { grid-template-columns: 1fr; } }

        /* ---- FAQ (big bold questions, thin rules) ---- */
        .yp-faq { padding: 30px 0 100px; }
        .yp-faq details { border-bottom: 1px solid rgba(255,255,255,.16); }
        .yp-faq summary {
            cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center;
            gap: 18px; padding: 26px 0; font-size: clamp(19px, 2.6vw, 26px); font-weight: 800;
        }
        .yp-faq summary::-webkit-details-marker { display: none; }
        .yp-faq summary::after { content: '+'; color: #8b93ff; font-size: 28px; font-weight: 400; flex: 0 0 auto; }
        .yp-faq details[open] summary::after { content: '–'; }
        .yp-faq .a { padding: 0 0 26px; max-width: 820px; color: rgba(255,255,255,.65); font-size: 16.5px; line-height: 1.7; }
        .yp-faq .a a { color: #fff; font-weight: 700; }

        /* ---- merch/history band with B&W photo strip ---- */
        .yp-history { background: #0c0c10; color: #fff; border-top: 1px solid rgba(255,255,255,.08); }
        .yp-history .strip { display: grid; grid-template-columns: repeat(6, 1fr); }
        .yp-history .strip img { width: 100%; height: clamp(110px, 16vw, 190px); object-fit: cover; object-position: top; filter: grayscale(100%); display: block; }
        .yp-history .inner { text-align: center; padding: 80px 24px 90px; }
        .yp-history h2 { font-size: clamp(30px, 4.8vw, 52px); font-weight: 800; letter-spacing: -0.015em; line-height: 1.12; margin: 0 0 22px; }
        .yp-history a { color: #fff; font-weight: 700; font-size: 16px; text-decoration: underline; text-underline-offset: 5px; }
        .yp-history a:hover { opacity: .7; }
        .yp-history .links { display: flex; gap: 34px; justify-content: center; flex-wrap: wrap; }

        /* ---- copper donate band ---- */
        .yp-donate { background: #dd9d85; color: #111114; padding: 90px 0; }
        .yp-donate h2 { font-size: clamp(28px, 4.4vw, 48px); font-weight: 800; letter-spacing: -0.015em; line-height: 1.15; margin: 0 0 30px; max-width: 900px; }
        .yp-donate a.yp-btn { background: #111114; }
        .yp-donate a.yp-btn:hover { background: #2c2c32; }

        @media (prefers-reduced-motion: reduce) { .yp-snap { transform: none !important; } }
    </style>
    @endverbatim
@endsection

@section('body')
@php
    // Featured people arrested young — ages computed from our database
    // (birthdate → first arrest). Photos come from each live record; anyone
    // missing a photo simply drops out.
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
    $snaps = collect($featured)->map(fn ($f) => $people[$f['slug']] ?? null)
        ->filter(fn ($p) => $p && $p->photo_url)->values()->take(6);
    $snapSpots = [
        ['top' => '9%',  'left' => '38%'], ['top' => '13%', 'left' => '62%'],
        ['top' => '7%',  'left' => '78%'], ['top' => '66%', 'left' => '11%'],
        ['top' => '60%', 'left' => '76%'], ['top' => '78%', 'left' => '46%'],
    ];
@endphp

<div class="yp">

    {{-- ============ statement hero with floating snapshots ============ --}}
    <section class="yp-intro">
        @foreach ($snaps as $i => $p)
            <img class="yp-snap" src="{{ $p->photo_url }}" alt="{{ $p->name }}"
                 style="top: {{ $snapSpots[$i]['top'] }}; left: {{ $snapSpots[$i]['left'] }};" data-depth="{{ 0.04 + 0.03 * ($i % 3) }}">
        @endforeach
        <p class="statement">NPPC Youth is for the generation being arrested right now — and everyone who came before them. Young people have always been at the front of American movements, <span class="accent">and the state has always met them there.</span></p>
        <div class="cue">Scroll</div>
    </section>

    {{-- ============ giant campaign headline ============ --}}
    <section class="yp-campaign">
        <div class="yp-wrap">
            <h1>Too young to vote. <span class="c">Old enough to be a&nbsp;target.</span></h1>
            <p>Some of the people in our database were arrested before they could drive. Many more before they could vote. The Scottsboro Nine were teenagers. The Freedom Riders were college students. The people filling protest dockets today are the same age.</p>
            <a class="yp-link" href="/database">Search the database →</a>
        </div>
    </section>

    {{-- ============ arrested-young carousel ============ --}}
    <section class="yp-stories">
        <div class="yp-wrap">
            <p class="yp-label">From our database</p>
            <h2>Arrested young</h2>
            <div class="yp-rail" id="yp-rail">
                @foreach ($featured as $f)
                    @php $p = $people[$f['slug']] ?? null; @endphp
                    @if ($p && $p->photo_url)
                        <a class="yp-card" href="/prisoner/{{ $p->slug }}">
                            <img src="{{ $p->photo_url }}" alt="{{ $p->name }}" loading="lazy">
                            <span class="yp-age">Arrested at {{ $f['age'] }}</span>
                            <h3>{{ $p->name }}</h3>
                            <p>{{ $f['line'] }}</p>
                        </a>
                    @endif
                @endforeach
            </div>
            <div class="yp-dots" id="yp-dots" aria-hidden="true"></div>
        </div>
    </section>

    {{-- ============ explainers ============ --}}
    <section class="yp-qa">
        <div class="yp-wrap">
            <p class="yp-label">Questions</p>
            <div class="item">
                <h3>Why are young people targeted?</h3>
                <p>Because movements run on them. Students and young workers have the least to lose, the most energy to give, and the longest futures to threaten. Prosecutors know a felony charge at 19 can shadow a whole life — and that the fear of one can empty a picket line before it forms.</p>
            </div>
            <div class="item">
                <h3>What does it look like today?</h3>
                <p>Conspiracy and terrorism enhancements for property damage, federal charges for protest conduct, gang statutes stretched over student groups, visa revocations for campus speech. The tools change; the pattern — reach for the heaviest charge available against the youngest defendants — is the oldest one in our database.</p>
            </div>
            <div class="item">
                <h3>What actually helps?</h3>
                <p>Court support fills the benches. Letters keep people connected inside. Defense funds keep families afloat. Attention keeps cases from disappearing. Every acquittal and clemency in our records has the same footnote: someone outside refused to move on.</p>
            </div>
        </div>
    </section>

    {{-- ============ sign-up band ============ --}}
    <section class="yp-join" id="yp-join">
        <div class="yp-wrap">
            <h2>Sign up &amp;<br>stand with them</h2>
            <form class="yp-signup" action="/sign-up" method="POST">
                @csrf
                <input type="email" name="email" placeholder="Email" required aria-label="Email address">
                <button type="submit">Join now →</button>
            </form>
            <p class="fine">Case updates, letter-writing calls, and court dates — just the moments when showing up matters.</p>
        </div>
    </section>

    {{-- ============ actions ============ --}}
    <section class="yp-acts">
        <div class="yp-wrap">
            <div class="yp-acts-grid">
                <a class="yp-act" href="/prisoner-outreach"><strong>Write a letter</strong><span>Someone inside reads it this week.</span></a>
                <a class="yp-act" href="/petitions"><strong>Sign a petition</strong><span>Active campaigns for release and clemency.</span></a>
                <a class="yp-act" href="/volunteer"><strong>Volunteer</strong><span>Research, court support, events, web — every role has a coordinator.</span></a>
                <a class="yp-act" href="/nppc-quiz"><strong>Take the NPPC Quiz</strong><span>Where do you stand? Five minutes, no sign-up.</span></a>
            </div>
        </div>
    </section>

    {{-- ============ FAQ ============ --}}
    <section class="yp-faq">
        <div class="yp-wrap">
            <p class="yp-label">Frequently asked questions</p>
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
                <div class="a">Start with the <a href="/history">history</a> and <a href="/timeline">timeline</a>, walk the <a href="/museum">3D museum</a>, read original documents in the <a href="/archive">archive</a>, or test yourself with the <a href="/nppc-quiz">NPPC Quiz</a>.</div>
            </details>
        </div>
    </section>

    {{-- ============ black history/merch band ============ --}}
    <section class="yp-history">
        <div class="strip" aria-hidden="true">
            <img src="/images/civic-profile/pp-01.jpg" alt=""><img src="/images/civic-profile/pp-03.jpg" alt="">
            <img src="/images/civic-profile/pp-08.jpg" alt=""><img src="/images/civic-profile/pp-10.jpg" alt="">
            <img src="/images/civic-profile/pp-09.jpg" alt=""><img src="/images/civic-profile/pp-04.jpg" alt="">
        </div>
        <div class="inner">
            <h2>Carry the history<br>and support the work</h2>
            <div class="links">
                <a href="/store">Shop the store</a>
                <a href="/museum">Walk the museum</a>
            </div>
        </div>
    </section>

    {{-- ============ copper donate band ============ --}}
    <section class="yp-donate">
        <div class="yp-wrap">
            <h2>We document more than 7,000 political prisoners across American history — and support the ones inside right now.</h2>
            <a class="yp-btn" href="/donate">Donate →</a>
        </div>
    </section>

</div>

@verbatim
<script>
(function () {
    // gentle parallax on the floating snapshots
    var snaps = document.querySelectorAll('.yp-snap');
    if (snaps.length && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
        var ticking = false;
        function move() {
            ticking = false;
            var y = window.scrollY;
            snaps.forEach(function (s) {
                s.style.transform = 'translateY(' + (-y * parseFloat(s.dataset.depth || 0.05)).toFixed(1) + 'px)';
            });
        }
        document.addEventListener('scroll', function () {
            if (!ticking) { ticking = true; requestAnimationFrame(move); }
        }, { passive: true });
    }

    // carousel pagination dots
    var rail = document.getElementById('yp-rail');
    var dotsEl = document.getElementById('yp-dots');
    if (rail && dotsEl) {
        var pages = Math.max(1, Math.ceil(rail.scrollWidth / rail.clientWidth));
        for (var i = 0; i < pages; i++) {
            var b = document.createElement('button');
            (function (idx) {
                b.addEventListener('click', function () {
                    rail.scrollTo({ left: idx * rail.clientWidth, behavior: 'smooth' });
                });
            })(i);
            dotsEl.appendChild(b);
        }
        function paint() {
            var idx = Math.min(pages - 1, Math.round(rail.scrollLeft / rail.clientWidth));
            Array.prototype.forEach.call(dotsEl.children, function (d, i) { d.classList.toggle('on', i === idx); });
        }
        rail.addEventListener('scroll', paint, { passive: true });
        paint();
    }
})();
</script>
@endverbatim
@endsection
