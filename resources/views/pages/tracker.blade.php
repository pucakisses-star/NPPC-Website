@php
    /**
     * @var int $totalDaysImprisoned
     * @var int $totalDaysInExile
     * @var int $totalDaysLost
     * @var int $inCustody
     * @var int $inExile
     * @var int $released
     * @var int $awaitingTrial
     * @var array<string, int> $daysByIdeology
     * @var \Illuminate\Support\Collection $activeCases
     * @var \Illuminate\Support\Collection $casesByPrisoner
     * @var int $totalPrisoners
     * @var int $firstYear
     */
    $now = \Carbon\Carbon::now();
    $sinceLabel = $firstYear ? ($now->year - $firstYear).' years ago' : 'over the past century';
    $maxIdeologyDays = max(array_values($daysByIdeology) ?: [1]);
@endphp

@extends('app')

@section('body')
    <section class="tk">
        <div class="tk-wrap">

            {{-- HERO COUNTER --}}
            <header class="tk-hero">
                <div class="tk-eyebrow">A real-time tracker</div>
                <h1 class="tk-title">The Cost of Political Imprisonment</h1>
                <div class="tk-counter" data-tk-counter="{{ $totalDaysLost }}">0</div>
                <p class="tk-counter-label">Days of life lost to U.S. political imprisonment and exile across the {{ number_format($totalPrisoners) }} cases documented by the NPPC since {{ $firstYear }} &mdash; {{ $sinceLabel }}.</p>

                <div class="tk-share">
                    <span class="tk-share-label">Share</span>
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode('The Cost of Political Imprisonment — '.number_format($totalDaysLost).' days of life lost across U.S. political prisoner cases since '.$firstYear.'.') }}&url={{ urlencode(url('/tracker')) }}" target="_blank" rel="noopener" aria-label="Share on X / Twitter">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/tracker')) }}" target="_blank" rel="noopener" aria-label="Share on Facebook">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.412c0-3.027 1.792-4.7 4.533-4.7 1.312 0 2.686.235 2.686.235v2.97h-1.513c-1.491 0-1.956.93-1.956 1.886v2.27h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073"/></svg>
                    </a>
                    <a href="mailto:?subject={{ urlencode('The Cost of Political Imprisonment') }}&body={{ urlencode(url('/tracker')) }}" aria-label="Share via email">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2zm-2 0l-8 5-8-5zm0 12H4V8l8 5 8-5z"/></svg>
                    </a>
                </div>
            </header>

            {{-- ANCHOR NAV --}}
            <nav class="tk-nav" aria-label="On this page">
                <a href="#toll">The Toll</a>
                <a href="#by-movement">By Movement</a>
                <a href="#active">Active Cases</a>
                <a href="#methodology">How We Count</a>
            </nav>

            {{-- SECTION 1: The Toll --}}
            <section id="toll" class="tk-section">
                <div class="tk-section-eyebrow">01 &middot; The Toll</div>
                <h2 class="tk-section-title">Counting the days political imprisonment takes from a life.</h2>
                <p class="tk-lede">For every documented U.S. political prisoner the NPPC tracks, we record the time their case has cost: days in custody, days awaiting trial, days under post-release supervision, and days in forced exile. The counter above is the running sum across the entire archive. It updates whenever a new case is added or a case&rsquo;s incarceration record is updated &mdash; nothing is rounded or amortized.</p>

                <div class="tk-stat-grid">
                    <div class="tk-stat">
                        <div class="tk-stat-num">{{ number_format($totalDaysImprisoned) }}</div>
                        <div class="tk-stat-label">days in custody</div>
                    </div>
                    <div class="tk-stat">
                        <div class="tk-stat-num">{{ number_format($totalDaysInExile) }}</div>
                        <div class="tk-stat-label">days in exile</div>
                    </div>
                    <div class="tk-stat">
                        <div class="tk-stat-num">{{ number_format($totalPrisoners) }}</div>
                        <div class="tk-stat-label">documented cases</div>
                    </div>
                    <div class="tk-stat">
                        <div class="tk-stat-num">{{ number_format($inCustody) }}</div>
                        <div class="tk-stat-label">in custody today</div>
                    </div>
                </div>
            </section>

            {{-- SECTION 2: By Movement --}}
            <section id="by-movement" class="tk-section">
                <div class="tk-section-eyebrow">02 &middot; By Movement</div>
                <h2 class="tk-section-title">Where the time went.</h2>
                <p class="tk-lede">Breaking the running total down by ideology shows which movements have absorbed the heaviest share of the cost. A single case can be tagged with more than one movement, so the bars below overlap rather than partition the total &mdash; they answer &ldquo;how much time has the state taken from organizers in this tradition?&rdquo;, not &ldquo;what percentage of the total does this category own?&rdquo;</p>

                <div class="tk-money">
                    @foreach ($daysByIdeology as $ideology => $days)
                        @php $pct = max(2, round(($days / $maxIdeologyDays) * 100)); @endphp
                        <a class="tk-money-row" href="/database?ideologies%5B%5D={{ urlencode($ideology) }}">
                            <div class="tk-money-label">{{ $ideology }}</div>
                            <div class="tk-money-bar"><div class="tk-money-bar-fill" style="width: {{ $pct }}%"></div></div>
                            <div class="tk-money-value">{{ number_format($days) }} <span>days</span></div>
                        </a>
                    @endforeach
                </div>
            </section>

            {{-- MID-PAGE NEWSLETTER CTA --}}
            <aside class="tk-cta">
                <div class="tk-cta-eyebrow">Dispatch</div>
                <div class="tk-cta-headline">New cases are added each week.</div>
                <p class="tk-cta-text">Get the running total in your inbox once a month, along with new prisoner profiles, clemency campaigns, and birthday letter-writing reminders.</p>
                <form class="tk-cta-form" action="/sign-up" method="POST">
                    @csrf
                    <input type="email" name="email" placeholder="Email Address" aria-label="Email address" required>
                    <button type="submit">Subscribe</button>
                </form>
            </aside>

            {{-- SECTION 3: Active Cases --}}
            <section id="active" class="tk-section">
                <div class="tk-section-eyebrow">03 &middot; Active Cases</div>
                <h2 class="tk-section-title">What we know so far &mdash; today.</h2>
                <p class="tk-lede">{{ number_format($inCustody) }} of the {{ number_format($totalPrisoners) }} people in this archive are in custody as of the most recent update. {{ number_format($awaitingTrial) }} are awaiting trial, {{ number_format($inExile) }} are in exile, and {{ number_format($released) }} are released or unincarcerated. The most recently added active cases:</p>

                <div class="tk-active-grid">
                    @foreach ($activeCases as $p)
                        @php
                            $earliest = $casesByPrisoner[$p->id]?->min('arrest_date');
                            $days = (int) $casesByPrisoner[$p->id]?->sum('imprisoned_for_days');
                        @endphp
                        <a class="tk-active-card" href="/prisoner/{{ $p->slug }}">
                            @if ($p->photo_url)
                                <div class="tk-active-photo" style="background-image: url('{{ $p->photo_url }}')"></div>
                            @else
                                <div class="tk-active-photo tk-active-photo-blank"></div>
                            @endif
                            <div class="tk-active-body">
                                <div class="tk-active-name">{{ $p->name }}</div>
                                @if ($earliest)
                                    <div class="tk-active-meta">Arrested {{ \Carbon\Carbon::parse($earliest)->format('M Y') }}</div>
                                @endif
                                @if ($days > 0)
                                    <div class="tk-active-days">{{ number_format($days) }} <span>days</span></div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                <a class="tk-more" href="/database?in_custody=1">See all active cases &rsaquo;</a>
            </section>

            {{-- SECTION 4: Methodology --}}
            <section id="methodology" class="tk-section">
                <div class="tk-section-eyebrow">04 &middot; How We Count</div>
                <h2 class="tk-section-title">Methodology.</h2>
                <div class="tk-method">
                    <p><strong>Scope.</strong> A &ldquo;political prisoner&rdquo; in the NPPC archive is a person held in U.S. custody, or driven into exile from the U.S., for activity reasonably understood as political &mdash; including but not limited to movement organizing, civil resistance, militant action, dissident speech, whistleblowing, and protest. The standard is descriptive (was the prosecution political in character?), not endorsement of the underlying conduct.</p>

                    <p><strong>Days counted.</strong> For each case we compute calendar days between the earliest documented arrest, incarceration, or exile date and the matching release date (or today, if still active). Time on parole, supervised release, and house arrest is included when our source material treats it as continuing custody. Days are computed per-case and summed across cases &mdash; people with multiple cases (re-arrests, parole violations) are counted for each interval.</p>

                    <p><strong>Sourcing.</strong> Cases are built from court records, FBI files released under FOIA, contemporary movement press, oral histories, and the archives of long-running support organizations (Anarchist Black Cross, Freedom Archives, Jericho Movement, etc.). Each prisoner profile cites its sources; the running counter reflects only cases whose incarceration dates are documented.</p>

                    <p><strong>Updates.</strong> Numbers refresh on every page load &mdash; nothing here is cached longer than the underlying database. If you see a case missing or a date that&rsquo;s off, <a href="/form/contact">tell us</a>.</p>
                </div>
            </section>

        </div>
    </section>

    <style>
        .tk { background: #000; color: #fff; padding: 64px 0 96px; }
        .tk-wrap { max-width: 1100px; margin: 0 auto; padding: 0 32px; }

        /* HERO */
        .tk-hero { text-align: center; padding: 48px 0 56px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .tk-eyebrow { font-size: 13px; font-weight: 800; letter-spacing: 0.18em; text-transform: uppercase; color: #5660fe; margin-bottom: 18px; }
        .tk-title { font-size: clamp(2rem, 4.5vw, 3.25rem); font-weight: 900; line-height: 1.05; letter-spacing: -0.02em; margin: 0 0 36px; color: #fff; }
        .tk-counter { font-size: clamp(3.5rem, 10vw, 7rem); font-weight: 900; letter-spacing: -0.04em; line-height: 1; color: #fff; font-variant-numeric: tabular-nums; margin-bottom: 18px; }
        .tk-counter-label { font-size: 16px; line-height: 1.55; color: rgba(255,255,255,0.7); max-width: 720px; margin: 0 auto 28px; }
        .tk-share { display: inline-flex; align-items: center; gap: 14px; }
        .tk-share-label { font-size: 11px; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; color: rgba(255,255,255,0.5); }
        .tk-share a { color: rgba(255,255,255,0.7); transition: color 0.15s; }
        .tk-share a:hover { color: #fff; }
        .tk-share svg { width: 20px; height: 20px; fill: currentColor; display: block; }

        /* ANCHOR NAV */
        .tk-nav { display: flex; flex-wrap: wrap; gap: 8px 24px; justify-content: center; padding: 24px 0; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 48px; position: sticky; top: 0; background: rgba(0,0,0,0.92); backdrop-filter: blur(8px); z-index: 10; }
        .tk-nav a { font-size: 13px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255,255,255,0.6); text-decoration: none; padding: 4px 0; border-bottom: 2px solid transparent; transition: color 0.15s, border-color 0.15s; }
        .tk-nav a:hover { color: #fff; border-color: #5660fe; }

        /* SECTIONS */
        .tk-section { padding: 56px 0; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .tk-section:last-of-type { border-bottom: none; }
        .tk-section-eyebrow { font-size: 12px; font-weight: 800; letter-spacing: 0.18em; text-transform: uppercase; color: #5660fe; margin-bottom: 16px; }
        .tk-section-title { font-size: clamp(1.75rem, 3.5vw, 2.5rem); font-weight: 900; line-height: 1.1; letter-spacing: -0.015em; margin: 0 0 24px; max-width: 800px; color: #fff; }
        .tk-lede { font-size: 17px; line-height: 1.65; color: rgba(255,255,255,0.78); max-width: 760px; margin: 0 0 36px; }

        /* STAT GRID (section 1) */
        .tk-stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
        .tk-stat { padding: 24px; border: 1px solid rgba(255,255,255,0.1); border-radius: 4px; }
        .tk-stat-num { font-size: clamp(1.75rem, 3vw, 2.5rem); font-weight: 900; letter-spacing: -0.02em; line-height: 1; margin-bottom: 8px; font-variant-numeric: tabular-nums; }
        .tk-stat-label { font-size: 12px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(255,255,255,0.55); }

        /* BREAKDOWN BARS (section 2) */
        .tk-money { display: flex; flex-direction: column; gap: 10px; }
        .tk-money-row { display: grid; grid-template-columns: 220px 1fr 140px; gap: 20px; align-items: center; padding: 14px 18px; border: 1px solid rgba(255,255,255,0.08); border-radius: 4px; text-decoration: none; color: inherit; transition: border-color 0.15s, background 0.15s; }
        .tk-money-row:hover { border-color: #5660fe; background: rgba(86,96,254,0.04); }
        .tk-money-label { font-size: 14px; font-weight: 700; color: #fff; }
        .tk-money-bar { height: 8px; background: rgba(255,255,255,0.06); border-radius: 4px; overflow: hidden; }
        .tk-money-bar-fill { height: 100%; background: linear-gradient(90deg, #5660fe, #8b94ff); }
        .tk-money-value { text-align: right; font-size: 18px; font-weight: 900; color: #fff; font-variant-numeric: tabular-nums; }
        .tk-money-value span { font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.55); margin-left: 4px; }

        /* MID-PAGE CTA */
        .tk-cta { position: relative; margin: 56px 0; padding: 40px 36px; border-top: 2px solid rgba(150,165,230,0.5); border-right: 2px solid rgba(150,165,230,0.5); border-bottom: 2px solid rgba(150,165,230,0.5); }
        .tk-cta::before { content: ''; position: absolute; left: -16px; bottom: -16px; width: 56px; height: 56px; border-left: 2px solid rgba(150,165,230,0.5); border-bottom: 2px solid rgba(150,165,230,0.5); pointer-events: none; }
        .tk-cta-eyebrow { font-size: 1.375rem; font-weight: 900; color: rgba(180,190,230,0.55); line-height: 1; margin-bottom: 6px; }
        .tk-cta-headline { font-size: 1.875rem; font-weight: 900; color: #fff; line-height: 1.1; margin-bottom: 12px; letter-spacing: -0.01em; }
        .tk-cta-text { font-size: 15px; line-height: 1.55; color: rgba(255,255,255,0.7); margin: 0 0 24px; max-width: 620px; }
        .tk-cta-form { display: flex; height: 56px; max-width: 540px; }
        .tk-cta-form input { flex: 1; height: 100%; background: transparent; border: 1px solid rgba(150,165,230,0.5); border-right: none; color: #fff; padding: 0 18px; font-size: 16px; outline: none; border-radius: 0; }
        .tk-cta-form input::placeholder { color: rgba(170,180,220,0.7); }
        .tk-cta-form input:focus { border-color: #fff; }
        .tk-cta-form button { height: 100%; background: #f25c54; color: #fff; border: none; padding: 0 36px; font-size: 14px; font-weight: 900; letter-spacing: 0.12em; text-transform: uppercase; cursor: pointer; transition: background 0.15s; }
        .tk-cta-form button:hover { background: #d44a42; }

        /* ACTIVE CASES GRID (section 3) */
        .tk-active-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 28px; }
        .tk-active-card { display: flex; flex-direction: column; border: 1px solid rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden; text-decoration: none; color: inherit; transition: border-color 0.15s, transform 0.15s; }
        .tk-active-card:hover { border-color: #5660fe; transform: translateY(-2px); }
        .tk-active-photo { aspect-ratio: 4/5; background-size: cover; background-position: center top; background-color: #1a1a2e; filter: grayscale(0.4); }
        .tk-active-photo-blank { background: linear-gradient(135deg, #1a1a2e 0%, #2a1860 100%); }
        .tk-active-body { padding: 16px 16px 18px; }
        .tk-active-name { font-size: 15px; font-weight: 800; color: #fff; line-height: 1.25; margin-bottom: 6px; }
        .tk-active-meta { font-size: 12px; color: rgba(255,255,255,0.5); margin-bottom: 10px; }
        .tk-active-days { font-size: 20px; font-weight: 900; color: #5660fe; font-variant-numeric: tabular-nums; line-height: 1; }
        .tk-active-days span { font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.5); margin-left: 4px; }
        .tk-more { display: inline-block; font-size: 13px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; color: #5660fe; text-decoration: none; padding: 8px 0; border-bottom: 2px solid #5660fe; }
        .tk-more:hover { color: #fff; border-bottom-color: #fff; }

        /* METHODOLOGY (section 4) */
        .tk-method p { font-size: 16px; line-height: 1.7; color: rgba(255,255,255,0.78); margin: 0 0 20px; max-width: 760px; }
        .tk-method p strong { color: #fff; font-weight: 800; }
        .tk-method a { color: #5660fe; text-decoration: underline; }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .tk { padding: 32px 0 56px; }
            .tk-wrap { padding: 0 20px; }
            .tk-nav { gap: 8px 16px; }
            .tk-stat-grid { grid-template-columns: repeat(2, 1fr); gap: 14px; }
            .tk-stat { padding: 18px; }
            .tk-money-row { grid-template-columns: 1fr; gap: 8px; padding: 14px; }
            .tk-money-value { text-align: left; }
            .tk-active-grid { grid-template-columns: repeat(2, 1fr); gap: 14px; }
            .tk-cta { padding: 28px 20px; }
            .tk-cta-form { flex-direction: column; height: auto; max-width: none; }
            .tk-cta-form input, .tk-cta-form button { height: 50px; border-right: 1px solid rgba(150,165,230,0.5); }
        }
    </style>

    <script>
        (function () {
            // Animate the hero counter up to its target value once it's visible.
            const el = document.querySelector('[data-tk-counter]');
            if (! el) return;
            const target = parseInt(el.getAttribute('data-tk-counter'), 10) || 0;
            const fmt = new Intl.NumberFormat('en-US');
            let started = false;
            const animate = () => {
                if (started) return; started = true;
                const duration = 1800;
                const start = performance.now();
                const step = (now) => {
                    const t = Math.min(1, (now - start) / duration);
                    // ease-out cubic
                    const eased = 1 - Math.pow(1 - t, 3);
                    el.textContent = fmt.format(Math.floor(target * eased));
                    if (t < 1) requestAnimationFrame(step);
                    else el.textContent = fmt.format(target);
                };
                requestAnimationFrame(step);
            };
            if ('IntersectionObserver' in window) {
                const io = new IntersectionObserver((entries) => {
                    if (entries.some((e) => e.isIntersecting)) { animate(); io.disconnect(); }
                });
                io.observe(el);
            } else {
                animate();
            }
        })();
    </script>
@endsection
