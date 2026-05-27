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
     * @var ?\App\Models\PrisonerCase $earliestCase
     * @var ?\App\Models\Prisoner $earliestPrisoner
     * @var ?\App\Models\PrisonerCase $newestActiveCase
     * @var ?\App\Models\Prisoner $newestActivePrisoner
     */
    $now = \Carbon\Carbon::now();
    $sinceLabel = $firstYear ? ($now->year - $firstYear).' years' : 'over a century';
    $maxIdeologyDays = max(array_values($daysByIdeology) ?: [1]);
    $totalYears = (int) round($totalDaysLost / 365);
@endphp

@extends('app')

@section('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
@endsection

@section('body')
    <article class="tkn">
        <div class="tkn-wrap">

            {{-- HERO --}}
            <header class="tkn-hero">
                <div class="tkn-eyebrow">A real-time tracker</div>
                <h1 class="tkn-title">The Cost of Political Imprisonment</h1>
                <div class="tkn-counter" data-tk-counter="{{ $totalDaysLost }}">0</div>
                <p class="tkn-counter-label">Days of life lost to U.S. political imprisonment and exile across the {{ number_format($totalPrisoners) }} cases the NPPC documents &mdash; over the past {{ $sinceLabel }}.</p>
                <div class="tkn-share">
                    <span class="tkn-share-label">Share</span>
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
            <nav class="tkn-nav" aria-label="On this page">
                <a href="#toll">01 The Toll</a>
                <a href="#movement">02 By Movement</a>
                <a href="#thennow">03 Then &amp; Now</a>
                <a href="#active">04 Active Cases</a>
                <a href="#methodology">05 How We Count</a>
            </nav>

            {{-- 01 THE TOLL --}}
            <section id="toll" class="tkn-section">
                <div class="tkn-snum">01</div>
                <h2 class="tkn-shead">Counting the days political imprisonment takes from a life.</h2>
                <p class="tkn-lede">For every documented U.S. political prisoner the NPPC tracks, we record what their case has cost: days in custody, days awaiting trial, days under post-release supervision, days in forced exile. The counter above is the running sum across the entire archive. It updates whenever a new case is added or an incarceration record is amended &mdash; nothing is rounded or amortized.</p>

                <div class="tkn-bignums">
                    <div class="tkn-bignum">
                        <div class="tkn-bignum-value">{{ number_format($totalDaysImprisoned) }}</div>
                        <div class="tkn-bignum-label">Days in custody</div>
                    </div>
                    <div class="tkn-bignum">
                        <div class="tkn-bignum-value">{{ number_format($totalDaysInExile) }}</div>
                        <div class="tkn-bignum-label">Days in exile</div>
                    </div>
                    <div class="tkn-bignum">
                        <div class="tkn-bignum-value">{{ number_format($totalPrisoners) }}</div>
                        <div class="tkn-bignum-label">Documented cases</div>
                    </div>
                    <div class="tkn-bignum">
                        <div class="tkn-bignum-value">{{ number_format($inCustody) }}</div>
                        <div class="tkn-bignum-label">In custody today</div>
                    </div>
                </div>

                {{-- Pull quote: editorial framing --}}
                <figure class="tkn-quote">
                    <blockquote>The greatest threat to the internal security of the country.</blockquote>
                    <figcaption>&mdash; J. Edgar Hoover, FBI Director, on the Black Panther Party (1969)</figcaption>
                </figure>
            </section>

            {{-- 02 BY MOVEMENT --}}
            <section id="movement" class="tkn-section">
                <div class="tkn-snum">02</div>
                <h2 class="tkn-shead">Where the time went.</h2>
                <p class="tkn-lede">Breaking the running total down by ideology shows which movements have absorbed the heaviest share of the cost. A single case can be tagged with more than one movement, so the bars below overlap rather than partition the total &mdash; they answer &ldquo;how much time has the state taken from organizers in this tradition?&rdquo;, not &ldquo;what percentage of the total does this category own?&rdquo;</p>

                <div class="tkn-bars">
                    @foreach ($daysByIdeology as $ideology => $days)
                        @php $pct = max(2, round(($days / $maxIdeologyDays) * 100)); @endphp
                        <a class="tkn-bar" href="/database?ideologies%5B%5D={{ urlencode($ideology) }}">
                            <div class="tkn-bar-label">{{ $ideology }}</div>
                            <div class="tkn-bar-track"><div class="tkn-bar-fill" style="width: {{ $pct }}%"></div></div>
                            <div class="tkn-bar-value">{{ number_format($days) }} <span>days</span></div>
                        </a>
                    @endforeach
                </div>
            </section>

            {{-- 03 THEN & NOW --}}
            <section id="thennow" class="tkn-section">
                <div class="tkn-snum">03</div>
                <h2 class="tkn-shead">Then &amp; Now.</h2>
                <p class="tkn-lede">U.S. political imprisonment is not a relic. The earliest case in this archive sits beside an active prisoner being held today &mdash; a hundred years apart, the same machinery.</p>

                <div class="tkn-thennow">
                    @if ($earliestPrisoner)
                        <a class="tkn-then" href="/prisoner/{{ $earliestPrisoner->slug }}">
                            <div class="tkn-then-eyebrow">Then &middot; {{ $earliestCase->arrest_date ? \Carbon\Carbon::parse($earliestCase->arrest_date)->format('Y') : '' }}</div>
                            @if ($earliestPrisoner->photo_url)
                                <div class="tkn-then-photo" style="background-image: url('{{ $earliestPrisoner->photo_url }}')"></div>
                            @else
                                <div class="tkn-then-photo tkn-photo-blank"></div>
                            @endif
                            <div class="tkn-then-name">{{ $earliestPrisoner->name }}</div>
                            @if ($earliestCase->charges)
                                <div class="tkn-then-charge">{{ \Illuminate\Support\Str::limit($earliestCase->charges, 140) }}</div>
                            @endif
                        </a>
                    @endif

                    @if ($newestActivePrisoner)
                        <a class="tkn-now" href="/prisoner/{{ $newestActivePrisoner->slug }}">
                            <div class="tkn-now-eyebrow">Now &middot; {{ $newestActiveCase->arrest_date ? \Carbon\Carbon::parse($newestActiveCase->arrest_date)->format('M Y') : 'Active' }}</div>
                            @if ($newestActivePrisoner->photo_url)
                                <div class="tkn-now-photo" style="background-image: url('{{ $newestActivePrisoner->photo_url }}')"></div>
                            @else
                                <div class="tkn-now-photo tkn-photo-blank"></div>
                            @endif
                            <div class="tkn-now-name">{{ $newestActivePrisoner->name }}</div>
                            @if ($newestActiveCase->charges)
                                <div class="tkn-now-charge">{{ \Illuminate\Support\Str::limit($newestActiveCase->charges, 140) }}</div>
                            @endif
                        </a>
                    @endif
                </div>
            </section>

            {{-- MID-PAGE CTA --}}
            <aside class="tkn-cta">
                <div class="tkn-cta-eyebrow">Dispatch</div>
                <div class="tkn-cta-headline">New cases are added each week.</div>
                <p class="tkn-cta-text">Get the running total in your inbox once a month, along with new prisoner profiles, clemency campaigns, and birthday letter-writing reminders.</p>
                <form class="tkn-cta-form" action="/sign-up" method="POST">
                    @csrf
                    <input type="email" name="email" placeholder="Email Address" aria-label="Email address" required>
                    <button type="submit">Subscribe</button>
                </form>
            </aside>

            {{-- 04 ACTIVE CASES --}}
            <section id="active" class="tkn-section">
                <div class="tkn-snum">04</div>
                <h2 class="tkn-shead">What we know so far &mdash; today.</h2>
                <p class="tkn-lede">{{ number_format($inCustody) }} of the {{ number_format($totalPrisoners) }} people in this archive are in custody right now. {{ number_format($awaitingTrial) }} are awaiting trial, {{ number_format($inExile) }} are in exile, and {{ number_format($released) }} are released or unincarcerated. The most recently arrested active cases:</p>

                <div class="tkn-active">
                    @foreach ($activeCases as $p)
                        @php
                            $caseSet = $casesByPrisoner->get($p->id);
                            $earliest = $caseSet?->min('arrest_date');
                            $days = (int) ($caseSet?->sum('imprisoned_for_days') ?? 0);
                        @endphp
                        <a class="tkn-acard" href="/prisoner/{{ $p->slug }}">
                            @if ($p->photo_url)
                                <div class="tkn-acard-photo" style="background-image: url('{{ $p->photo_url }}')"></div>
                            @else
                                <div class="tkn-acard-photo tkn-photo-blank"></div>
                            @endif
                            <div class="tkn-acard-name">{{ $p->name }}</div>
                            @if ($earliest)
                                <div class="tkn-acard-meta">Arrested {{ \Carbon\Carbon::parse($earliest)->format('M Y') }}</div>
                            @endif
                            @if ($days > 0)
                                <div class="tkn-acard-days">{{ number_format($days) }} <span>days</span></div>
                            @endif
                        </a>
                    @endforeach
                </div>

                <a class="tkn-more" href="/database?in_custody=1">See all active cases &rsaquo;</a>
            </section>

            {{-- 05 METHODOLOGY --}}
            <section id="methodology" class="tkn-section">
                <div class="tkn-snum">05</div>
                <h2 class="tkn-shead">How we count.</h2>
                <div class="tkn-method">
                    <p><strong>Scope.</strong> A &ldquo;political prisoner&rdquo; in the NPPC archive is a person held in U.S. custody, or driven into exile from the U.S., for activity reasonably understood as political &mdash; movement organizing, civil resistance, militant action, dissident speech, whistleblowing, protest. The standard is descriptive (was the prosecution political in character?), not endorsement of the underlying conduct.</p>

                    <p><strong>Days counted.</strong> For each case we compute calendar days between the earliest documented arrest, incarceration, or exile date and the matching release date (or today, if still active). Time on parole, supervised release, and house arrest is included when our source material treats it as continuing custody. Days are computed per-case and summed across cases &mdash; people with multiple cases (re-arrests, parole violations) are counted for each interval.</p>

                    <p><strong>Sourcing.</strong> Cases are built from court records, FBI files released under FOIA, contemporary movement press, oral histories, and the archives of long-running support organizations (Anarchist Black Cross, Freedom Archives, Jericho Movement, etc.). Each prisoner profile cites its sources; the running counter reflects only cases whose incarceration dates are documented.</p>

                    <p><strong>Updates.</strong> Numbers refresh on every page load &mdash; nothing here is cached longer than the underlying database. If you see a case missing or a date that&rsquo;s off, <a href="/form/contact">tell us</a>.</p>
                </div>
            </section>

        </div>
    </article>

    <style>
        body.page-tracker { background: #f4efe4 !important; color: #1a1a1a; }
        body.page-tracker main.container, body.page-tracker .container { max-width: none !important; padding-left: 0 !important; padding-right: 0 !important; }

        .tkn { background: #f4efe4; color: #1a1a1a; font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; padding: 56px 0 88px; }
        .tkn-wrap { max-width: 980px; margin: 0 auto; padding: 0 32px; }

        /* HERO */
        .tkn-hero { text-align: center; padding: 32px 0 64px; border-bottom: 2px solid #1a1a1a; }
        .tkn-eyebrow { font-size: 12px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: #b03930; margin-bottom: 24px; }
        .tkn-title { font-family: 'Playfair Display', Georgia, serif; font-size: clamp(2.5rem, 5.5vw, 4rem); font-weight: 900; line-height: 1; letter-spacing: -0.02em; margin: 0 0 36px; color: #0d0d0d; }
        .tkn-counter { font-family: 'Playfair Display', Georgia, serif; font-size: clamp(4.5rem, 14vw, 10rem); font-weight: 900; line-height: 0.95; letter-spacing: -0.04em; color: #0d0d0d; font-variant-numeric: tabular-nums; margin-bottom: 24px; }
        .tkn-counter-label { font-size: 17px; line-height: 1.5; color: #4a4a4a; max-width: 680px; margin: 0 auto 32px; }
        .tkn-share { display: inline-flex; align-items: center; gap: 14px; }
        .tkn-share-label { font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: #4a4a4a; }
        .tkn-share a { color: #1a1a1a; transition: color 0.15s; }
        .tkn-share a:hover { color: #b03930; }
        .tkn-share svg { width: 18px; height: 18px; fill: currentColor; display: block; }

        /* ANCHOR NAV */
        .tkn-nav { display: flex; flex-wrap: wrap; gap: 4px 32px; justify-content: center; padding: 20px 0; border-bottom: 1px solid rgba(26,26,26,0.18); margin-bottom: 56px; position: sticky; top: 0; background: rgba(244,239,228,0.95); backdrop-filter: blur(10px); z-index: 10; }
        .tkn-nav a { font-size: 12px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: #1a1a1a; text-decoration: none; padding: 6px 0; border-bottom: 2px solid transparent; transition: border-color 0.15s; }
        .tkn-nav a:hover { border-color: #b03930; }

        /* SECTIONS */
        .tkn-section { padding: 56px 0; border-bottom: 1px solid rgba(26,26,26,0.18); }
        .tkn-section:last-of-type { border-bottom: none; }
        .tkn-snum { font-family: 'Playfair Display', Georgia, serif; font-size: 14rem; font-weight: 900; line-height: 0.8; color: #1a1a1a; opacity: 0.06; letter-spacing: -0.04em; margin-bottom: -120px; pointer-events: none; }
        .tkn-shead { font-family: 'Playfair Display', Georgia, serif; font-size: clamp(2.25rem, 4.5vw, 3.25rem); font-weight: 900; line-height: 1.02; letter-spacing: -0.02em; margin: 0 0 24px; color: #0d0d0d; max-width: 820px; position: relative; }
        .tkn-lede { font-size: 18px; line-height: 1.6; color: #2c2c2c; max-width: 720px; margin: 0 0 40px; }

        /* BIG NUMBER STAT GRID */
        .tkn-bignums { display: grid; grid-template-columns: repeat(4, 1fr); gap: 28px; margin-bottom: 56px; }
        .tkn-bignum { border-top: 2px solid #1a1a1a; padding-top: 16px; }
        .tkn-bignum-value { font-family: 'Playfair Display', Georgia, serif; font-size: clamp(2rem, 3.5vw, 3rem); font-weight: 900; line-height: 1; letter-spacing: -0.025em; color: #0d0d0d; font-variant-numeric: tabular-nums; margin-bottom: 10px; }
        .tkn-bignum-label { font-size: 13px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #4a4a4a; }

        /* PULL QUOTE */
        .tkn-quote { margin: 56px 0 0; padding: 32px 0; border-top: 2px solid #1a1a1a; border-bottom: 2px solid #1a1a1a; text-align: center; }
        .tkn-quote blockquote { font-family: 'Playfair Display', Georgia, serif; font-style: italic; font-size: clamp(1.5rem, 3vw, 2.25rem); font-weight: 700; line-height: 1.2; color: #0d0d0d; margin: 0 0 16px; max-width: 720px; margin-inline: auto; }
        .tkn-quote blockquote::before { content: '\201C'; }
        .tkn-quote blockquote::after { content: '\201D'; }
        .tkn-quote figcaption { font-size: 12px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: #4a4a4a; }

        /* BREAKDOWN BARS */
        .tkn-bars { display: flex; flex-direction: column; gap: 0; }
        .tkn-bar { display: grid; grid-template-columns: 240px 1fr 160px; gap: 24px; align-items: center; padding: 18px 0; border-top: 1px solid rgba(26,26,26,0.18); text-decoration: none; color: inherit; transition: background 0.15s; }
        .tkn-bar:last-child { border-bottom: 1px solid rgba(26,26,26,0.18); }
        .tkn-bar:hover { background: rgba(26,26,26,0.03); }
        .tkn-bar-label { font-size: 15px; font-weight: 700; color: #0d0d0d; }
        .tkn-bar-track { height: 14px; background: rgba(26,26,26,0.08); position: relative; }
        .tkn-bar-fill { height: 100%; background: #0d0d0d; }
        .tkn-bar-value { font-family: 'Playfair Display', Georgia, serif; text-align: right; font-size: 22px; font-weight: 900; color: #0d0d0d; font-variant-numeric: tabular-nums; }
        .tkn-bar-value span { font-family: 'Inter', sans-serif; font-size: 11px; font-weight: 600; color: #4a4a4a; margin-left: 4px; letter-spacing: 0.08em; text-transform: uppercase; }

        /* THEN & NOW */
        .tkn-thennow { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; align-items: start; }
        .tkn-then, .tkn-now { display: block; text-decoration: none; color: inherit; border: 1px solid rgba(26,26,26,0.2); padding: 24px; background: #fff; transition: border-color 0.15s, transform 0.15s; }
        .tkn-then:hover, .tkn-now:hover { border-color: #b03930; transform: translateY(-2px); }
        .tkn-then-eyebrow, .tkn-now-eyebrow { font-size: 12px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: #b03930; margin-bottom: 18px; }
        .tkn-then-photo, .tkn-now-photo { width: 100%; aspect-ratio: 1/1; background-size: cover; background-position: center top; background-color: #1a1a1a; margin-bottom: 18px; filter: grayscale(0.5); }
        .tkn-photo-blank { background: linear-gradient(135deg, #1a1a1a 0%, #2a1860 100%); }
        .tkn-then-name, .tkn-now-name { font-family: 'Playfair Display', Georgia, serif; font-size: 1.75rem; font-weight: 900; line-height: 1.05; color: #0d0d0d; margin-bottom: 12px; }
        .tkn-then-charge, .tkn-now-charge { font-size: 14px; line-height: 1.5; color: #4a4a4a; }

        /* MID-PAGE CTA */
        .tkn-cta { position: relative; margin: 64px 0; padding: 40px 36px; background: #0d0d0d; color: #fff; border: none; }
        .tkn-cta-eyebrow { font-size: 1.5rem; font-weight: 900; font-family: 'Playfair Display', Georgia, serif; font-style: italic; color: rgba(255,255,255,0.55); line-height: 1; margin-bottom: 8px; }
        .tkn-cta-headline { font-family: 'Playfair Display', Georgia, serif; font-size: 2rem; font-weight: 900; color: #fff; line-height: 1.1; margin-bottom: 16px; letter-spacing: -0.01em; max-width: 580px; }
        .tkn-cta-text { font-size: 15px; line-height: 1.55; color: rgba(255,255,255,0.78); margin: 0 0 24px; max-width: 580px; }
        .tkn-cta-form { display: flex; height: 52px; max-width: 540px; }
        .tkn-cta-form input { flex: 1; height: 100%; background: transparent; border: 1px solid rgba(255,255,255,0.4); border-right: none; color: #fff; padding: 0 18px; font-size: 15px; outline: none; border-radius: 0; }
        .tkn-cta-form input::placeholder { color: rgba(255,255,255,0.55); }
        .tkn-cta-form input:focus { border-color: #fff; }
        .tkn-cta-form button { height: 100%; background: #b03930; color: #fff; border: none; padding: 0 30px; font-size: 13px; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; cursor: pointer; transition: background 0.15s; }
        .tkn-cta-form button:hover { background: #8a2a23; }

        /* ACTIVE CASES */
        .tkn-active { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 32px; }
        .tkn-acard { display: block; text-decoration: none; color: inherit; transition: transform 0.15s; }
        .tkn-acard:hover { transform: translateY(-3px); }
        .tkn-acard-photo { aspect-ratio: 3/4; background-size: cover; background-position: center top; background-color: #1a1a1a; margin-bottom: 14px; filter: grayscale(0.4); }
        .tkn-acard-name { font-family: 'Playfair Display', Georgia, serif; font-size: 1.125rem; font-weight: 900; line-height: 1.15; color: #0d0d0d; margin-bottom: 6px; }
        .tkn-acard-meta { font-size: 12px; color: #6a6a6a; margin-bottom: 10px; }
        .tkn-acard-days { font-family: 'Playfair Display', Georgia, serif; font-size: 1.5rem; font-weight: 900; color: #b03930; line-height: 1; font-variant-numeric: tabular-nums; }
        .tkn-acard-days span { font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; color: #6a6a6a; margin-left: 4px; letter-spacing: 0.12em; text-transform: uppercase; }
        .tkn-more { display: inline-block; font-size: 12px; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; color: #b03930; text-decoration: none; padding: 8px 0; border-bottom: 2px solid #b03930; }
        .tkn-more:hover { color: #0d0d0d; border-bottom-color: #0d0d0d; }

        /* METHODOLOGY */
        .tkn-method p { font-size: 16px; line-height: 1.7; color: #2c2c2c; margin: 0 0 22px; max-width: 720px; }
        .tkn-method p strong { color: #0d0d0d; font-weight: 800; }
        .tkn-method a { color: #b03930; text-decoration: underline; }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .tkn { padding: 24px 0 48px; }
            .tkn-wrap { padding: 0 20px; }
            .tkn-nav { gap: 4px 16px; }
            .tkn-snum { font-size: 8rem; margin-bottom: -68px; }
            .tkn-bignums { grid-template-columns: repeat(2, 1fr); gap: 16px; }
            .tkn-bar { grid-template-columns: 1fr; gap: 6px; padding: 14px 0; }
            .tkn-bar-value { text-align: left; }
            .tkn-thennow { grid-template-columns: 1fr; gap: 20px; }
            .tkn-active { grid-template-columns: repeat(2, 1fr); gap: 16px; }
            .tkn-cta { padding: 28px 22px; }
            .tkn-cta-form { flex-direction: column; height: auto; max-width: none; }
            .tkn-cta-form input, .tkn-cta-form button { height: 48px; border-right: 1px solid rgba(255,255,255,0.4); }
        }
    </style>

    <script>
        (function () {
            const el = document.querySelector('[data-tk-counter]');
            if (! el) return;
            const target = parseInt(el.getAttribute('data-tk-counter'), 10) || 0;
            const fmt = new Intl.NumberFormat('en-US');
            let started = false;
            const animate = () => {
                if (started) return; started = true;
                const duration = 2200;
                const start = performance.now();
                const step = (now) => {
                    const t = Math.min(1, (now - start) / duration);
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
