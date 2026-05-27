@php
    /**
     * @var int $totalDaysImprisoned
     * @var int $totalDaysInExile
     * @var int $inCustody
     * @var int $inExile
     * @var int $released
     * @var int $awaitingTrial
     * @var array<string, int> $costByIdeology
     * @var \Illuminate\Support\Collection $activeCases
     * @var \Illuminate\Support\Collection $casesByPrisoner
     * @var int $totalPrisoners
     * @var int $totalCases
     * @var int $firstYear
     * @var ?\App\Models\PrisonerCase $earliestCase
     * @var ?\App\Models\Prisoner $earliestPrisoner
     * @var ?\App\Models\PrisonerCase $newestActiveCase
     * @var ?\App\Models\Prisoner $newestActivePrisoner
     * @var \Illuminate\Support\Collection $heroPrisoners
     * @var int $costOfIncarceration
     * @var int $costOfProsecution
     * @var int $totalCost
     * @var int $costFederalIncarceration
     * @var int $costStateIncarceration
     * @var int $costLocalIncarceration
     * @var int $costOfAppeals
     * @var \Illuminate\Support\Collection $costBubbles
     * @var int $federalDailyCost
     * @var int $stateDailyCost
     * @var int $localDailyCost
     * @var int $costPerProsecution
     * @var int $costPerAppeal
     */
    $now = \Carbon\Carbon::now();
    $sinceYears = $firstYear ? $now->year - $firstYear : 0;
    $maxIdeologyCost = max(array_values($costByIdeology) ?: [1]);
    $maxBubble = (int) ($costBubbles->max('value') ?: 1);
@endphp

@extends('app')

@section('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
@endsection

@section('body')
    <article class="tk2">

        {{-- TOP NAV STRIP --}}
        <header class="tk2-topnav">
            <div class="tk2-topnav-inner">
                <a class="tk2-brand" href="/" aria-label="NPPC home">
                    <img src="/logo.svg" alt="NPPC" />
                </a>
                <nav class="tk2-anchors" aria-label="On this page">
                    <a href="#toll">The Toll<span aria-hidden="true">&rarr;</span></a>
                    <a href="#breakdown">Cost Breakdown<span aria-hidden="true">&rarr;</span></a>
                    <a href="#movement">By Movement<span aria-hidden="true">&rarr;</span></a>
                    <a href="#thennow">Then &amp; Now<span aria-hidden="true">&rarr;</span></a>
                    <a href="#active">Active Cases</a>
                </nav>
            </div>
        </header>

        {{-- HERO with prisoner photo collage + coral banner --}}
        <section class="tk2-hero">
            <div class="tk2-hero-photos" aria-hidden="true">
                @foreach ($heroPrisoners as $i => $p)
                    <div class="tk2-hero-photo tk2-hero-photo-{{ $i }}" style="background-image: url('{{ $p->photo_url }}')"></div>
                @endforeach
                <div class="tk2-hero-halftone"></div>
            </div>
            <h1 class="tk2-hero-title">The Price of Political Prosecution</h1>
        </section>

        <section class="tk2-banner">
            <div class="tk2-banner-inner">
                <div class="tk2-banner-num"><span class="tk2-banner-sign">$</span><span data-tk-counter="{{ $totalCost }}">0</span></div>
            </div>
        </section>

        <p class="tk2-banner-sub">Public dollars spent prosecuting and incarcerating the {{ number_format($totalPrisoners) }} U.S. political prisoners documented by the NPPC &mdash; over the past {{ $sinceYears }} years.</p>

        <div class="tk2-share-bar">
            <span>Share</span>
            <a href="https://twitter.com/intent/tweet?text={{ urlencode('The Price of Political Prosecution — $'.number_format($totalCost).' spent prosecuting and incarcerating U.S. political prisoners.') }}&url={{ urlencode(url('/tracker')) }}" target="_blank" rel="noopener" aria-label="Share on X / Twitter">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/tracker')) }}" target="_blank" rel="noopener" aria-label="Share on Facebook">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.412c0-3.027 1.792-4.7 4.533-4.7 1.312 0 2.686.235 2.686.235v2.97h-1.513c-1.491 0-1.956.93-1.956 1.886v2.27h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073"/></svg>
            </a>
            <a href="mailto:?subject={{ urlencode('The Cost of Political Imprisonment') }}&body={{ urlencode(url('/tracker')) }}" aria-label="Share via email">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2zm-2 0l-8 5-8-5zm0 12H4V8l8 5 8-5z"/></svg>
            </a>
        </div>

        <div class="tk2-body">

            {{-- 01 THE TOLL --}}
            <section id="toll" class="tk2-section">
                <div class="tk2-snum">01</div>
                <h2 class="tk2-shead">Pricing out a century of political prosecution.</h2>
                <p class="tk2-lede">For every documented U.S. political prisoner the NPPC tracks, we estimate two public-dollar costs: the prosecution itself and the days subsequently spent in custody. Multiply per-day incarceration costs by {{ number_format($totalDaysImprisoned) }} documented days behind bars, add an average prosecution cost per case across {{ number_format($totalCases) }} cases, and the running total above is what U.S. taxpayers have spent confining political speech, protest, and movement organizing since {{ $firstYear }}. See <a href="#methodology">How we count</a> for assumptions.</p>

                <div class="tk2-bignums">
                    <div class="tk2-bignum">
                        <div class="tk2-bignum-value">${{ number_format($costOfIncarceration) }}</div>
                        <div class="tk2-bignum-label">Cost of incarceration</div>
                    </div>
                    <div class="tk2-bignum">
                        <div class="tk2-bignum-value">${{ number_format($costOfProsecution) }}</div>
                        <div class="tk2-bignum-label">Cost of prosecution</div>
                    </div>
                    <div class="tk2-bignum">
                        <div class="tk2-bignum-value">{{ number_format($totalCases) }}</div>
                        <div class="tk2-bignum-label">Documented cases</div>
                    </div>
                    <div class="tk2-bignum">
                        <div class="tk2-bignum-value">{{ number_format($inCustody) }}</div>
                        <div class="tk2-bignum-label">In custody today</div>
                    </div>
                </div>

                <figure class="tk2-quote">
                    <blockquote>The greatest threat to the internal security of the country.</blockquote>
                    <figcaption>&mdash; J. Edgar Hoover, FBI Director, on the Black Panther Party (1969)</figcaption>
                </figure>
            </section>

            {{-- 02 COST BREAKDOWN BUBBLES --}}
            <section id="breakdown" class="tk2-section">
                <div class="tk2-snum">02</div>
                <h2 class="tk2-shead">What goes into the total.</h2>
                <p class="tk2-lede">Public money flows through five distinct buckets: federal-prison custody, state-prison custody, county- and city-jail custody, the prosecution itself, and post-conviction appellate and habeas litigation. Hover any bubble to read the running figure.</p>

                <div class="tk2-bubbles" id="tk2-bubbles">
                    <div class="tk2-bubbles-canvas" id="tk2-bubbles-canvas">
                        @php
                            $bubbleCount = $costBubbles->count();
                            $canvasW = 900; // matches CSS layout target; physics will recompute on load
                            $canvasH = 540;
                            $i = 0;
                        @endphp
                        @foreach ($costBubbles as $b)
                            @php
                                $ratio = sqrt(max(1, $b['value']) / max(1, $maxBubble));
                                $size = max(110, round(360 * $ratio));
                                // Fallback static layout: distribute horizontally + alternate vertical so bubbles
                                // are visible immediately even before Matter.js loads / runs.
                                $slotX = $bubbleCount > 1
                                    ? (int) round(($canvasW / ($bubbleCount + 1)) * ($i + 1) - $size / 2)
                                    : (int) round(($canvasW - $size) / 2);
                                $slotY = (int) round(($canvasH - $size) / 2 + ($i % 2 === 0 ? -60 : 60));
                                $i++;
                            @endphp
                            <div class="tk2-bubble tk2-bubble-{{ $b['shade'] }}"
                                 data-radius="{{ round($size / 2) }}"
                                 style="width: {{ $size }}px; height: {{ $size }}px; left: {{ $slotX }}px; top: {{ $slotY }}px;">
                                <div class="tk2-bubble-label">{{ $b['label'] }}</div>
                                <div class="tk2-bubble-value">${{ number_format($b['value']) }}</div>
                            </div>
                        @endforeach
                    </div>
                    <p class="tk2-bubbles-hint">Drag the bubbles &mdash; they collide, bounce, and settle.</p>
                </div>
            </section>

            {{-- 03 BY MOVEMENT --}}
            <section id="movement" class="tk2-section">
                <div class="tk2-snum">03</div>
                <h2 class="tk2-shead">Where the money went.</h2>
                <p class="tk2-lede">Breaking the dollar total down by ideology shows which movements have been the most expensive for the state to prosecute and confine. A single case can be tagged with more than one movement, so the bars below overlap rather than partition the total.</p>

                <div class="tk2-bars">
                    @foreach ($costByIdeology as $ideology => $cost)
                        @php $pct = max(2, round(($cost / $maxIdeologyCost) * 100)); @endphp
                        <a class="tk2-bar" href="/database?ideologies%5B%5D={{ urlencode($ideology) }}">
                            <div class="tk2-bar-label">{{ $ideology }}</div>
                            <div class="tk2-bar-track"><div class="tk2-bar-fill" style="width: {{ $pct }}%"></div></div>
                            <div class="tk2-bar-value">${{ number_format($cost) }}</div>
                        </a>
                    @endforeach
                </div>
            </section>

            {{-- 04 THEN & NOW --}}
            <section id="thennow" class="tk2-section">
                <div class="tk2-snum">04</div>
                <h2 class="tk2-shead">Then &amp; Now.</h2>
                <p class="tk2-lede">U.S. political imprisonment is not a relic. The earliest case in this archive sits beside an active prisoner being held today &mdash; a hundred years apart, the same machinery.</p>

                <div class="tk2-thennow">
                    @if ($earliestPrisoner)
                        <a class="tk2-tn" href="/prisoner/{{ $earliestPrisoner->slug }}">
                            <div class="tk2-tn-eyebrow">Then &middot; {{ $earliestCase->arrest_date ? \Carbon\Carbon::parse($earliestCase->arrest_date)->format('Y') : '' }}</div>
                            @if ($earliestPrisoner->photo_url)
                                <div class="tk2-tn-photo" style="background-image: url('{{ $earliestPrisoner->photo_url }}')"></div>
                            @else
                                <div class="tk2-tn-photo tk2-photo-blank"></div>
                            @endif
                            <div class="tk2-tn-name">{{ $earliestPrisoner->name }}</div>
                            @if ($earliestCase->charges)
                                <div class="tk2-tn-charge">{{ \Illuminate\Support\Str::limit($earliestCase->charges, 140) }}</div>
                            @endif
                        </a>
                    @endif

                    @if ($newestActivePrisoner)
                        <a class="tk2-tn" href="/prisoner/{{ $newestActivePrisoner->slug }}">
                            <div class="tk2-tn-eyebrow">Now &middot; {{ $newestActiveCase->arrest_date ? \Carbon\Carbon::parse($newestActiveCase->arrest_date)->format('M Y') : 'Active' }}</div>
                            @if ($newestActivePrisoner->photo_url)
                                <div class="tk2-tn-photo" style="background-image: url('{{ $newestActivePrisoner->photo_url }}')"></div>
                            @else
                                <div class="tk2-tn-photo tk2-photo-blank"></div>
                            @endif
                            <div class="tk2-tn-name">{{ $newestActivePrisoner->name }}</div>
                            @if ($newestActiveCase->charges)
                                <div class="tk2-tn-charge">{{ \Illuminate\Support\Str::limit($newestActiveCase->charges, 140) }}</div>
                            @endif
                        </a>
                    @endif
                </div>
            </section>

            {{-- MID-PAGE CTA --}}
            <aside class="tk2-cta">
                <div class="tk2-cta-eyebrow">Dispatch</div>
                <div class="tk2-cta-headline">New cases are added each week.</div>
                <p class="tk2-cta-text">Get the running total in your inbox once a month, along with new prisoner profiles, clemency campaigns, and birthday letter-writing reminders.</p>
                <form class="tk2-cta-form" action="/sign-up" method="POST">
                    @csrf
                    <input type="email" name="email" placeholder="Email Address" aria-label="Email address" required>
                    <button type="submit">Subscribe</button>
                </form>
            </aside>

            {{-- 05 ACTIVE CASES --}}
            <section id="active" class="tk2-section">
                <div class="tk2-snum">05</div>
                <h2 class="tk2-shead">What we know so far &mdash; today.</h2>
                <p class="tk2-lede">{{ number_format($inCustody) }} of the {{ number_format($totalPrisoners) }} people in this archive are in custody right now. {{ number_format($awaitingTrial) }} are awaiting trial, {{ number_format($inExile) }} are in exile, and {{ number_format($released) }} are released or unincarcerated.</p>

                <div class="tk2-active">
                    @foreach ($activeCases as $p)
                        @php
                            $caseSet = $casesByPrisoner->get($p->id);
                            $earliest = $caseSet?->min('arrest_date');
                            $days = (int) ($caseSet?->sum('imprisoned_for_days') ?? 0);
                            $cost = 0;
                            foreach ($caseSet ?? collect() as $c) {
                                $d = (int) ($c->imprisoned_for_days ?? 0);
                                $inst = (string) optional($c->institution)->name;
                                if (preg_match('/\b(federal|FCI|USP|ADX|FMC|FDC|MDC|MCC|FCC|U\.S\.\s*Penit|United States Penit|U\.S\. District|Bureau of Prisons|BOP)\b/i', $inst)) {
                                    $cost += $d * $federalDailyCost;
                                } elseif (preg_match('/\b(county jail|city jail|municipal|holding facility)\b/i', $inst)) {
                                    $cost += $d * $localDailyCost;
                                } else {
                                    $cost += $d * $stateDailyCost;
                                }
                                $cost += $costPerProsecution;
                                if ((string) ($c->convicted ?? '') !== '' || (string) ($c->plead ?? '') !== '' || (string) ($c->sentence ?? '') !== '') {
                                    $cost += $costPerAppeal;
                                }
                            }
                        @endphp
                        <a class="tk2-acard" href="/prisoner/{{ $p->slug }}">
                            @if ($p->photo_url)
                                <div class="tk2-acard-photo" style="background-image: url('{{ $p->photo_url }}')"></div>
                            @else
                                <div class="tk2-acard-photo tk2-photo-blank"></div>
                            @endif
                            <div class="tk2-acard-name">{{ $p->name }}</div>
                            @if ($earliest)
                                <div class="tk2-acard-meta">Arrested {{ \Carbon\Carbon::parse($earliest)->format('M Y') }}</div>
                            @endif
                            @if ($cost > 0)
                                <div class="tk2-acard-days">${{ number_format($cost) }}<span>&nbsp;spent</span></div>
                            @endif
                        </a>
                    @endforeach
                </div>

                <a class="tk2-more" href="/database?in_custody=1">See all active cases &rsaquo;</a>
            </section>

            {{-- 06 METHODOLOGY --}}
            <section id="methodology" class="tk2-section">
                <div class="tk2-snum">06</div>
                <h2 class="tk2-shead">How we count.</h2>
                <div class="tk2-method">
                    <p><strong>Scope.</strong> A &ldquo;political prisoner&rdquo; in the NPPC archive is a person held in U.S. custody, or driven into exile from the U.S., for activity reasonably understood as political &mdash; movement organizing, civil resistance, militant action, dissident speech, whistleblowing, protest. The standard is descriptive (was the prosecution political in character?), not endorsement of the underlying conduct.</p>

                    <p><strong>Per-day incarceration rates.</strong> Federal: ${{ number_format($federalDailyCost) }}/day (~${{ number_format($federalDailyCost * 365) }}/year, drawn from the BOP&rsquo;s <em>Annual Determination of Average Cost of Incarceration Fee</em>). State: ${{ number_format($stateDailyCost) }}/day (~${{ number_format($stateDailyCost * 365) }}/year, blended 50-state median from the Vera Institute&rsquo;s <em>Price of Prisons</em>). Local jails: ${{ number_format($localDailyCost) }}/day (BJS county-jail average). Each case in the archive is assigned to one of these three buckets by its institution name &mdash; federal facilities (FCI, USP, ADX, FMC, MDC, MCC, BOP) get the federal rate; county/city jails get the local rate; the rest default to the state rate.</p>

                    <p><strong>Per-case prosecution cost: ${{ number_format($costPerProsecution) }}.</strong> Drawn from Bureau of Justice Statistics reporting on federal and state felony prosecution costs, blended to a conservative midpoint. Political cases typically run higher because of specialized AUSA time, classified-evidence handling, and multi-jurisdictional grand juries.</p>

                    <p><strong>Per-case appeals & post-conviction cost: ${{ number_format($costPerAppeal) }}.</strong> Applied only to cases that resulted in a conviction or sentence (acquittals/dismissals stop the meter). Covers direct appeals, state and federal habeas petitions, and civil-rights litigation arising from the conviction. Long-running appeals from death-penalty or life-sentence cases routinely exceed this average several times over, so the figure understates rather than overstates.</p>

                    <p><strong>Days counted.</strong> For each case we compute calendar days between the earliest documented arrest, incarceration, or exile date and the matching release date (or today, if still active). Time on parole, supervised release, and house arrest is included when our source material treats it as continuing custody.</p>

                    <p><strong>Sourcing.</strong> Cases are built from court records, FBI files released under FOIA, contemporary movement press, oral histories, and the archives of long-running support organizations.</p>

                    <p><strong>Updates.</strong> Numbers refresh on every page load &mdash; nothing here is cached longer than the underlying database. If you see a case missing or a cost assumption you&rsquo;d challenge, <a href="/form/contact">tell us</a>.</p>
                </div>
            </section>

        </div>
    </article>

    <style>
        body.page-tracker { background: #000 !important; color: #fff; }
        body.page-tracker main.container, body.page-tracker .container { max-width: none !important; padding-left: 0 !important; padding-right: 0 !important; }

        .tk2 { background: #000; color: #fff; font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif; padding: 0 0 88px; }

        /* TOP NAV STRIP */
        .tk2-topnav { padding: 28px 32px 24px; border-bottom: 1px solid rgba(255,255,255,0.15); }
        .tk2-topnav-inner { max-width: 1380px; margin: 0 auto; display: flex; align-items: center; gap: 56px; flex-wrap: wrap; }
        .tk2-brand { display: inline-flex; align-items: center; }
        .tk2-brand img { height: 44px; width: auto; display: block; filter: brightness(0) invert(1); }
        .tk2-anchors { display: flex; flex-wrap: wrap; gap: 12px 32px; align-items: center; flex: 1; }
        .tk2-anchors a { font-size: 13px; font-weight: 900; letter-spacing: 0.08em; text-transform: uppercase; color: #4dd9d2; text-decoration: none; display: inline-flex; align-items: center; gap: 16px; }
        .tk2-anchors a:hover { color: #fff; }
        .tk2-anchors a span { color: #4dd9d2; font-size: 16px; font-weight: 400; }
        .tk2-anchors a:last-child span { display: none; }

        /* HERO */
        .tk2-hero { position: relative; max-width: 1380px; margin: 0 auto; padding: 56px 32px 0; min-height: 540px; display: flex; align-items: flex-end; justify-content: center; overflow: hidden; }
        .tk2-hero::before { content: ''; position: absolute; inset: 0; background-image: radial-gradient(rgba(255,255,255,0.04) 1px, transparent 1px); background-size: 4px 4px; pointer-events: none; }
        .tk2-hero-photos { position: absolute; left: 0; right: 0; bottom: 0; height: 540px; display: flex; align-items: flex-end; justify-content: center; gap: 0; padding: 0 32px; }
        .tk2-hero-photo { width: 26%; height: 100%; background-size: cover; background-position: center top; filter: grayscale(1) contrast(1.15); -webkit-mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 75%, rgba(0,0,0,0) 100%); mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 75%, rgba(0,0,0,0) 100%); }
        .tk2-hero-photo-0 { transform: translate(40%, 14%) scale(0.82); opacity: 0.85; }
        .tk2-hero-photo-1 { transform: translate(15%, 4%) scale(0.92); }
        .tk2-hero-photo-2 { transform: scale(1.05); z-index: 2; filter: grayscale(0.4) contrast(1.1); }
        .tk2-hero-photo-3 { transform: translate(-15%, 6%) scale(0.95); }
        .tk2-hero-halftone { position: absolute; inset: 0; background-image: radial-gradient(rgba(0,0,0,0.18) 1px, transparent 1.4px); background-size: 3px 3px; mix-blend-mode: multiply; pointer-events: none; }
        .tk2-hero-title { position: relative; z-index: 3; font-family: 'Playfair Display', Georgia, serif; font-style: italic; font-weight: 700; font-size: clamp(2.5rem, 5vw, 4rem); color: #fff; text-align: center; margin: 0 0 32px; letter-spacing: -0.01em; text-shadow: 0 2px 14px rgba(0,0,0,0.5); }

        /* CORAL BANNER WITH BIG NUMBER */
        .tk2-banner { background: #f25c54; padding: 36px 32px 28px; }
        .tk2-banner-inner { max-width: 1380px; margin: 0 auto; text-align: center; }
        .tk2-banner-num { font-family: 'Inter', sans-serif; font-weight: 900; font-size: clamp(3.5rem, 10vw, 8rem); line-height: 1; letter-spacing: -0.04em; color: #0a0a0a; font-variant-numeric: tabular-nums; display: inline-flex; align-items: baseline; }
        .tk2-banner-sign { font-size: 0.7em; margin-right: 0.05em; font-weight: 900; }
        .tk2-banner-sub { text-align: center; font-size: 16px; line-height: 1.5; color: #fff; max-width: 720px; margin: 24px auto 16px; padding: 0 32px; font-weight: 700; }

        /* SHARE BAR */
        .tk2-share-bar { display: flex; justify-content: center; align-items: center; gap: 14px; padding: 0 32px 56px; }
        .tk2-share-bar span { font-size: 11px; font-weight: 800; letter-spacing: 0.18em; text-transform: uppercase; color: rgba(255,255,255,0.5); }
        .tk2-share-bar a { color: rgba(255,255,255,0.75); transition: color 0.15s; }
        .tk2-share-bar a:hover { color: #4dd9d2; }
        .tk2-share-bar svg { width: 18px; height: 18px; fill: currentColor; display: block; }

        /* BODY SECTIONS */
        .tk2-body { max-width: 980px; margin: 0 auto; padding: 0 32px; }
        .tk2-section { padding: 56px 0; border-top: 1px solid rgba(255,255,255,0.12); position: relative; }
        .tk2-snum { font-family: 'Playfair Display', Georgia, serif; font-style: italic; font-size: 14rem; font-weight: 900; line-height: 0.8; color: rgba(255,255,255,0.05); letter-spacing: -0.04em; margin-bottom: -120px; pointer-events: none; }
        .tk2-shead { font-family: 'Playfair Display', Georgia, serif; font-size: clamp(2rem, 4vw, 3rem); font-weight: 900; line-height: 1.04; letter-spacing: -0.02em; margin: 0 0 24px; color: #fff; max-width: 820px; position: relative; }
        .tk2-lede { font-size: 17px; line-height: 1.6; color: rgba(255,255,255,0.78); max-width: 720px; margin: 0 0 40px; }

        /* BIG NUMBER STAT GRID */
        .tk2-bignums { display: grid; grid-template-columns: repeat(4, 1fr); gap: 28px; margin-bottom: 56px; }
        .tk2-bignum { border-top: 2px solid #fff; padding-top: 16px; }
        .tk2-bignum-value { font-family: 'Playfair Display', Georgia, serif; font-size: clamp(1.75rem, 3vw, 2.5rem); font-weight: 900; line-height: 1; letter-spacing: -0.02em; color: #fff; font-variant-numeric: tabular-nums; margin-bottom: 10px; }
        .tk2-bignum-label { font-size: 12px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: rgba(255,255,255,0.6); }

        /* PULL QUOTE */
        .tk2-quote { margin: 0; padding: 32px 0; border-top: 2px solid #fff; border-bottom: 2px solid #fff; text-align: center; }
        .tk2-quote blockquote { font-family: 'Playfair Display', Georgia, serif; font-style: italic; font-size: clamp(1.5rem, 3vw, 2.25rem); font-weight: 700; line-height: 1.2; color: #fff; margin: 0 0 16px; max-width: 720px; margin-inline: auto; }
        .tk2-quote blockquote::before { content: '\201C'; }
        .tk2-quote blockquote::after { content: '\201D'; }
        .tk2-quote figcaption { font-size: 12px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: rgba(255,255,255,0.55); }

        /* COST BREAKDOWN BUBBLES — physics-driven, draggable */
        .tk2-bubbles { padding: 24px 0 8px; }
        .tk2-bubbles-canvas { position: relative; width: 100%; height: 540px; overflow: hidden; touch-action: none; user-select: none; -webkit-user-select: none; }
        .tk2-bubble { position: absolute; top: 0; left: 0; border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 12px; color: #06303d; cursor: grab; box-sizing: border-box; will-change: transform; }
        .tk2-bubble:active { cursor: grabbing; }
        .tk2-bubble-a { background: #06766e; color: #fff; }
        .tk2-bubble-b { background: #2aa098; color: #fff; }
        .tk2-bubble-c { background: #54b8b1; color: #06303d; }
        .tk2-bubble-d { background: #8ed1cc; color: #06303d; }
        .tk2-bubble-e { background: #b4dfdb; color: #06303d; }
        .tk2-bubble-label { font-family: 'Inter', sans-serif; font-weight: 800; font-size: clamp(11px, 0.95vw, 15px); line-height: 1.15; padding: 0 8px; margin-bottom: 4px; max-width: 88%; pointer-events: none; }
        .tk2-bubble-value { font-family: 'Playfair Display', Georgia, serif; font-weight: 900; font-size: clamp(13px, 1.4vw, 22px); line-height: 1; font-variant-numeric: tabular-nums; pointer-events: none; }
        .tk2-bubbles-hint { text-align: center; font-size: 12px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255,255,255,0.45); margin: 16px 0 0; font-style: italic; }
        @media (max-width: 700px) {
            .tk2-bubbles-canvas { height: 420px; }
            .tk2-bubble { transform-origin: top left; }
        }

        /* BREAKDOWN BARS */
        .tk2-bars { display: flex; flex-direction: column; gap: 0; }
        .tk2-bar { display: grid; grid-template-columns: 240px 1fr 160px; gap: 24px; align-items: center; padding: 18px 0; border-top: 1px solid rgba(255,255,255,0.12); text-decoration: none; color: inherit; transition: background 0.15s; }
        .tk2-bar:last-child { border-bottom: 1px solid rgba(255,255,255,0.12); }
        .tk2-bar:hover { background: rgba(255,255,255,0.03); }
        .tk2-bar-label { font-size: 15px; font-weight: 700; color: #fff; }
        .tk2-bar-track { height: 14px; background: rgba(255,255,255,0.08); position: relative; }
        .tk2-bar-fill { height: 100%; background: #f25c54; }
        .tk2-bar-value { font-family: 'Playfair Display', Georgia, serif; text-align: right; font-size: 22px; font-weight: 900; color: #fff; font-variant-numeric: tabular-nums; }
        .tk2-bar-value span { font-family: 'Inter', sans-serif; font-size: 11px; font-weight: 600; color: rgba(255,255,255,0.55); margin-left: 4px; letter-spacing: 0.08em; text-transform: uppercase; }

        /* THEN & NOW */
        .tk2-thennow { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; align-items: start; }
        .tk2-tn { display: block; text-decoration: none; color: inherit; border: 1px solid rgba(255,255,255,0.18); padding: 24px; background: rgba(255,255,255,0.02); transition: border-color 0.15s, transform 0.15s; }
        .tk2-tn:hover { border-color: #f25c54; transform: translateY(-2px); }
        .tk2-tn-eyebrow { font-size: 12px; font-weight: 800; letter-spacing: 0.18em; text-transform: uppercase; color: #f25c54; margin-bottom: 18px; }
        .tk2-tn-photo { width: 100%; aspect-ratio: 1/1; background-size: cover; background-position: center top; background-color: #1a1a1a; margin-bottom: 18px; filter: grayscale(0.6); }
        .tk2-photo-blank { background: linear-gradient(135deg, #1a1a1a 0%, #2a1860 100%); }
        .tk2-tn-name { font-family: 'Playfair Display', Georgia, serif; font-size: 1.75rem; font-weight: 900; line-height: 1.05; color: #fff; margin-bottom: 12px; }
        .tk2-tn-charge { font-size: 14px; line-height: 1.5; color: rgba(255,255,255,0.65); }

        /* MID-PAGE CTA */
        .tk2-cta { position: relative; margin: 64px 0; padding: 40px 36px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.15); }
        .tk2-cta-eyebrow { font-family: 'Playfair Display', Georgia, serif; font-style: italic; font-size: 1.5rem; font-weight: 900; color: rgba(255,255,255,0.45); line-height: 1; margin-bottom: 8px; }
        .tk2-cta-headline { font-family: 'Playfair Display', Georgia, serif; font-size: 2rem; font-weight: 900; color: #fff; line-height: 1.1; margin-bottom: 16px; letter-spacing: -0.01em; max-width: 580px; }
        .tk2-cta-text { font-size: 15px; line-height: 1.55; color: rgba(255,255,255,0.7); margin: 0 0 24px; max-width: 580px; }
        .tk2-cta-form { display: flex; height: 52px; max-width: 540px; }
        .tk2-cta-form input { flex: 1; height: 100%; background: transparent; border: 1px solid rgba(255,255,255,0.35); border-right: none; color: #fff; padding: 0 18px; font-size: 15px; outline: none; border-radius: 0; }
        .tk2-cta-form input::placeholder { color: rgba(255,255,255,0.5); }
        .tk2-cta-form input:focus { border-color: #fff; }
        .tk2-cta-form button { height: 100%; background: #f25c54; color: #0a0a0a; border: none; padding: 0 30px; font-size: 13px; font-weight: 900; letter-spacing: 0.14em; text-transform: uppercase; cursor: pointer; transition: background 0.15s; }
        .tk2-cta-form button:hover { background: #fff; }

        /* ACTIVE CASES */
        .tk2-active { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 32px; }
        .tk2-acard { display: block; text-decoration: none; color: inherit; transition: transform 0.15s; }
        .tk2-acard:hover { transform: translateY(-3px); }
        .tk2-acard-photo { aspect-ratio: 3/4; background-size: cover; background-position: center top; background-color: #1a1a1a; margin-bottom: 14px; filter: grayscale(0.5); }
        .tk2-acard-name { font-family: 'Playfair Display', Georgia, serif; font-size: 1.125rem; font-weight: 900; line-height: 1.15; color: #fff; margin-bottom: 6px; }
        .tk2-acard-meta { font-size: 12px; color: rgba(255,255,255,0.5); margin-bottom: 10px; }
        .tk2-acard-days { font-family: 'Playfair Display', Georgia, serif; font-size: 1.5rem; font-weight: 900; color: #f25c54; line-height: 1; font-variant-numeric: tabular-nums; }
        .tk2-acard-days span { font-family: 'Inter', sans-serif; font-size: 10px; font-weight: 700; color: rgba(255,255,255,0.5); margin-left: 4px; letter-spacing: 0.12em; text-transform: uppercase; }
        .tk2-more { display: inline-block; font-size: 12px; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; color: #4dd9d2; text-decoration: none; padding: 8px 0; border-bottom: 2px solid #4dd9d2; }
        .tk2-more:hover { color: #fff; border-bottom-color: #fff; }

        /* METHODOLOGY */
        .tk2-method p { font-size: 16px; line-height: 1.7; color: rgba(255,255,255,0.78); margin: 0 0 22px; max-width: 720px; }
        .tk2-method p strong { color: #fff; font-weight: 800; }
        .tk2-method a { color: #4dd9d2; text-decoration: underline; }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .tk2-topnav { padding: 16px 20px; }
            .tk2-topnav-inner { gap: 18px; }
            .tk2-anchors { gap: 8px 16px; }
            .tk2-anchors a { font-size: 11px; gap: 8px; }
            .tk2-hero { min-height: 360px; padding: 32px 20px 0; }
            .tk2-hero-photos { height: 360px; padding: 0 12px; }
            .tk2-hero-photo { width: 28%; }
            .tk2-hero-title { font-size: 2rem; margin-bottom: 24px; }
            .tk2-banner { padding: 24px 20px 20px; }
            .tk2-banner-sub { padding: 0 20px; font-size: 14px; }
            .tk2-body { padding: 0 20px; }
            .tk2-snum { font-size: 8rem; margin-bottom: -68px; }
            .tk2-bignums { grid-template-columns: repeat(2, 1fr); gap: 16px; }
            .tk2-bar { grid-template-columns: 1fr; gap: 6px; padding: 14px 0; }
            .tk2-bar-value { text-align: left; }
            .tk2-thennow { grid-template-columns: 1fr; gap: 20px; }
            .tk2-active { grid-template-columns: repeat(2, 1fr); gap: 16px; }
            .tk2-cta { padding: 28px 22px; margin: 40px 0; }
            .tk2-cta-form { flex-direction: column; height: auto; max-width: none; }
            .tk2-cta-form input, .tk2-cta-form button { height: 48px; border-right: 1px solid rgba(255,255,255,0.35); }
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

    {{-- Physics-driven, draggable cost-breakdown bubbles --}}
    <script src="https://cdn.jsdelivr.net/npm/matter-js@0.20.0/build/matter.min.js"></script>
    <script>
        (function initBubblePhysics() {
            const start = function () {
                const canvas = document.getElementById('tk2-bubbles-canvas');
                if (! canvas) return;
                const bubbles = Array.from(canvas.querySelectorAll('.tk2-bubble'));
                if (! bubbles.length) return;
                if (typeof Matter === 'undefined') {
                    console.warn('[tracker] Matter.js failed to load; bubbles will use static fallback layout.');
                    return;
                }

            const { Engine, World, Bodies, Body, Events, Mouse, MouseConstraint, Runner } = Matter;
            const engine = Engine.create();
            // No gravity — bubbles float. A per-tick spring force below
            // gently pulls each body back toward the canvas centre.
            engine.gravity.y = 0;
            engine.gravity.x = 0;

            let width = canvas.clientWidth;
            let height = canvas.clientHeight;

            // Walls — full box around the canvas so a flung bubble can't
            // escape. With zero gravity all four sides matter.
            const wallOpts = { isStatic: true, render: { visible: false } };
            let walls = [];
            const buildWalls = () => {
                if (walls.length) World.remove(engine.world, walls);
                walls = [
                    Bodies.rectangle(width / 2, height + 30, width + 100, 60, wallOpts),
                    Bodies.rectangle(width / 2, -30, width + 100, 60, wallOpts),
                    Bodies.rectangle(-30, height / 2, 60, height + 200, wallOpts),
                    Bodies.rectangle(width + 30, height / 2, 60, height + 200, wallOpts),
                ];
                World.add(engine.world, walls);
            };
            buildWalls();

            // Bubble bodies. Lay them out radially around the canvas
            // centre so they start where they belong and drift slightly
            // from there.
            const cx = width / 2;
            const cy = height / 2;
            const bodies = bubbles.map((el, i) => {
                const r = parseInt(el.dataset.radius, 10) || (el.offsetWidth / 2);
                const angle = (i / bubbles.length) * Math.PI * 2 - Math.PI / 2;
                const ringRadius = Math.min(width, height) * 0.22;
                const x = cx + Math.cos(angle) * ringRadius;
                const y = cy + Math.sin(angle) * ringRadius;
                const body = Bodies.circle(x, y, r, {
                    restitution: 0.6,
                    friction: 0,
                    frictionAir: 0.08,
                    density: 0.001,
                });
                // Remember each body's "home" point (centre of canvas) so
                // we can pull it back when it drifts.
                body.elem = el;
                body.home = { x: cx, y: cy };
                return body;
            });
            World.add(engine.world, bodies);

            // Floaty-bubble feel: every engine tick, each body gets a small
            // spring force toward the canvas centre plus a tiny random jitter
            // so the cluster never settles into a perfectly static lattice.
            Events.on(engine, 'beforeUpdate', () => {
                bodies.forEach(body => {
                    if (body.isStatic) return;
                    const dx = body.home.x - body.position.x;
                    const dy = body.home.y - body.position.y;
                    const k = 0.0000022; // spring stiffness
                    Body.applyForce(body, body.position, {
                        x: dx * k * body.mass,
                        y: dy * k * body.mass,
                    });
                    // Subtle Brownian wobble.
                    Body.applyForce(body, body.position, {
                        x: (Math.random() - 0.5) * 0.00002 * body.mass,
                        y: (Math.random() - 0.5) * 0.00002 * body.mass,
                    });
                });
            });

            // Mouse drag — Matter.js MouseConstraint handles touch automatically.
            const mouse = Mouse.create(canvas);
            const mouseConstraint = MouseConstraint.create(engine, {
                mouse,
                constraint: { stiffness: 0.15, render: { visible: false } },
            });
            World.add(engine.world, mouseConstraint);

            // Let page-level scrolling work over the canvas (Matter's mouse
            // wheel handler swallows it by default).
            mouse.element.removeEventListener('wheel', mouse.mousewheel);

            // Now that physics is active, clear the static-fallback left/top so
            // the transform alone positions each bubble.
            bubbles.forEach(el => { el.style.left = '0'; el.style.top = '0'; });

            // Sync DOM transforms to physics each frame.
            (function tick() {
                bodies.forEach(body => {
                    const r = body.circleRadius;
                    body.elem.style.transform = `translate(${body.position.x - r}px, ${body.position.y - r}px) rotate(${body.angle}rad)`;
                });
                requestAnimationFrame(tick);
            })();

            Runner.run(Runner.create(), engine);

            // Resize: re-measure container, move walls, nudge bubbles back into bounds.
            let resizeTO = null;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTO);
                resizeTO = setTimeout(() => {
                    width = canvas.clientWidth;
                    height = canvas.clientHeight;
                    buildWalls();
                    bodies.forEach(body => {
                        const r = body.circleRadius;
                        if (body.position.x < r) Body.setPosition(body, { x: r + 4, y: body.position.y });
                        if (body.position.x > width - r) Body.setPosition(body, { x: width - r - 4, y: body.position.y });
                    });
                }, 150);
            });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', start);
            } else {
                start();
            }
        })();
    </script>
@endsection
