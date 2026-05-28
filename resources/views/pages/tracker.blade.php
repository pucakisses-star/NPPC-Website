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
     * @var array<string,int> $activeCaseCosts
     * @var int $windowYears
     * @var int $federalDays
     * @var int $stateDays
     * @var int $localDays
     * @var array{min:int,max:int,minYear:int,maxYear:int} $methodFedRateRange
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

        {{-- HERO — single composite portrait of political prisoners --}}
        <section class="tk2-hero" id="tk2-hero">
            <img class="tk2-hero-img" src="/images/tracker-hero.png" alt="" aria-hidden="true">
            <div class="tk2-hero-spot-overlay" aria-hidden="true"></div>
            <div class="tk2-hero-spot-highlight" aria-hidden="true"></div>
            <h1 class="tk2-hero-title">The Price of Political Prosecution</h1>
        </section>

        <section class="tk2-banner">
            <div class="tk2-banner-inner">
                <div class="tk2-banner-num"><span class="tk2-banner-sign">$</span><span data-tk-counter="{{ $totalCost }}" data-tk-per-second="{{ $perSecondOngoingCost }}">0</span></div>
            </div>
        </section>

        <p class="tk2-banner-sub">Public dollars spent prosecuting and incarcerating the {{ number_format($totalPrisoners) }} U.S. political prisoners documented by the NPPC &mdash; over the past {{ $sinceYears }} years.</p>

        <div class="tk2-share-bar">
            <a href="https://twitter.com/intent/tweet?text={{ urlencode('The Price of Political Prosecution — $'.number_format($totalCost).' spent prosecuting and incarcerating U.S. political prisoners.') }}&url={{ urlencode(url('/tracker')) }}" target="_blank" rel="noopener" aria-label="Share on X / Twitter">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/tracker')) }}" target="_blank" rel="noopener" aria-label="Share on Facebook">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.412c0-3.027 1.792-4.7 4.533-4.7 1.312 0 2.686.235 2.686.235v2.97h-1.513c-1.491 0-1.956.93-1.956 1.886v2.27h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073"/></svg>
            </a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url('/tracker')) }}" target="_blank" rel="noopener" aria-label="Share on LinkedIn">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
            </a>
            <a href="https://bsky.app/intent/compose?text={{ urlencode('The Price of Political Prosecution — '.url('/tracker')) }}" target="_blank" rel="noopener" aria-label="Share on Bluesky">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5.07 3.04c2.68 2.01 5.56 6.09 6.62 8.28 1.06-2.19 3.94-6.27 6.62-8.28 1.93-1.45 5.07-2.58 5.07.99 0 .71-.41 5.98-.65 6.84-.83 2.97-3.86 3.73-6.55 3.27 4.71.8 5.9 3.45 3.32 6.11-4.91 5.04-7.05-1.27-7.6-2.88-.1-.3-.15-.43-.15-.31 0-.12-.05.01-.15.31-.55 1.61-2.69 7.92-7.6 2.88-2.58-2.66-1.39-5.31 3.32-6.11-2.69.46-5.72-.3-6.55-3.27C.41 9.02 0 3.75 0 3.04 0-.53 3.14.59 5.07 3.04z"/></svg>
            </a>
            <a href="mailto:?subject={{ urlencode('The Price of Political Prosecution') }}&body={{ urlencode(url('/tracker')) }}" aria-label="Share via email">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2zm-2 0l-8 5-8-5zm0 12H4V8l8 5 8-5z"/></svg>
            </a>
        </div>

        {{-- SECTION NAV — sits below the hero, introduces the body content --}}
        <nav class="tk2-anchors" aria-label="On this page">
            <a href="#toll">The Toll<span aria-hidden="true">&rarr;</span></a>
            <a href="#breakdown">Cost Breakdown<span aria-hidden="true">&rarr;</span></a>
            <a href="#movement">By Movement<span aria-hidden="true">&rarr;</span></a>
            <a href="#charges">Charges<span aria-hidden="true">&rarr;</span></a>
            <a href="#affiliation">Affiliation<span aria-hidden="true">&rarr;</span></a>
            <a href="#wheregoes">Where It Goes</a>
        </nav>

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
                <p class="tk2-lede">Public money flows through six distinct buckets: federal-prison custody, state-prison custody, county- and city-jail custody, the investigation that precedes the prosecution (FBI surveillance, JTTF stings, COINTELPRO-style programs), the prosecution itself, and post-conviction appellate and habeas litigation. Drag any bubble to see how the pieces compare.</p>

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
                                 data-label="{{ $b['label'] }}"
                                 data-value="${{ number_format($b['value']) }}"
                                 style="--bs: {{ $size }}px; width: {{ $size }}px; height: {{ $size }}px; left: {{ $slotX }}px; top: {{ $slotY }}px;">
                                <div class="tk2-bubble-label">{{ $b['label'] }}</div>
                                <div class="tk2-bubble-value">${{ number_format($b['value']) }}</div>
                            </div>
                        @endforeach
                    </div>
                    {{-- Tooltip lives OUTSIDE the canvas so overflow:hidden
                         on the canvas can't clip it when a bubble is near
                         the top/edges. --}}
                    <div class="tk2-bubble-tooltip" id="tk2-bubble-tooltip" hidden>
                        <div class="tk2-bubble-tooltip-label"></div>
                        <div class="tk2-bubble-tooltip-value"></div>
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

            {{-- 04 MOST COMMON CHARGES --}}
            <section id="charges" class="tk2-section">
                <div class="tk2-snum">04</div>
                <img class="tk2-section-icon" src="/images/icon-charges.svg" alt="" aria-hidden="true">
                <h2 class="tk2-shead">Most common charges in all cases.</h2>
                <p class="tk2-lede">How often each charge appears across the documented cases, and the average cost to prosecute a case carrying that charge. A single case usually carries several charges, so the counts overlap.</p>

                <div class="tk2-charges">
                    @foreach ($chargeStats as $ch)
                        @php
                            $ratio = sqrt(max(1, $ch['count']) / max(1, $maxChargeCount));
                            $cd = max(18, round(120 * $ratio)); // circle diameter px
                        @endphp
                        <div class="tk2-charge" title="{{ $ch['label'] }}: {{ number_format($ch['count']) }} cases, avg ${{ number_format($ch['avgCost']) }} to prosecute">
                            <div class="tk2-charge-dot-wrap">
                                <span class="tk2-charge-dot" style="width: {{ $cd }}px; height: {{ $cd }}px;"></span>
                            </div>
                            <div class="tk2-charge-count">{{ number_format($ch['count']) }}</div>
                            <div class="tk2-charge-label">{{ $ch['label'] }}</div>
                            <div class="tk2-charge-cost">${{ number_format($ch['avgCost']) }} avg prosecution</div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- 05 AFFILIATION OVER TIME --}}
            <section id="affiliation" class="tk2-section">
                <div class="tk2-snum">05</div>
                <img class="tk2-section-icon" src="/images/icon-affiliation.svg" alt="" aria-hidden="true">
                <h2 class="tk2-shead">Purported affiliation, over time.</h2>
                <p class="tk2-lede">Defendants grouped by the movement or organization the government alleged they belonged to, plotted by year of arrest. Each prisoner is counted once per affiliation they were tagged with.</p>
                <div class="tk2-affil-wrap">
                    <canvas id="tk2-affil-chart" height="300"
                            data-years='@json($affYears)'
                            data-series='@json($affiliationSeries)'></canvas>
                </div>
            </section>

            {{-- 06 WHERE IT GOES — CAP-style alternating cost cards --}}
            @php
                $tkMoney = function ($n) {
                    $n = (int) $n;
                    if ($n >= 1000000000) return '$'.rtrim(rtrim(number_format($n / 1000000000, 3, '.', ''), '0'), '.').'B';
                    if ($n >= 1000000)    return '$'.number_format(round($n / 1000000)).'M';
                    if ($n >= 1000)       return '$'.number_format(round($n / 1000)).'K';
                    return '$'.number_format($n);
                };
            @endphp
            <section id="wheregoes" class="tk2-section tk2-costcap-section">
                <div class="tk2-snum">06</div>
                <div class="tk2-costcap-head">
                    <div class="tk2-costcap-eyebrow">Total cost of repression</div>
                    <div class="tk2-costcap-total">${{ number_format($totalCost) }}</div>
                </div>

                <div class="tk2-costcap-list">
                    @foreach ($costCards as $i => $card)
                        <div class="tk2-costcap-row {{ $i % 2 === 0 ? 'is-imgright' : 'is-imgleft' }}">
                            <div class="tk2-costcap-figure">
                                <div class="tk2-costcap-diamond tk2-costcap-diamond--{{ $card['key'] }}">
                                    <span class="tk2-costcap-emblem">
                                        @switch($card['key'])
                                            @case('federal')
                                                <svg viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="5" stroke-linejoin="round" stroke-linecap="round"><path d="M50 12 L82 34 H18 Z"/><line x1="20" y1="40" x2="80" y2="40"/><line x1="28" y1="40" x2="28" y2="74"/><line x1="42" y1="40" x2="42" y2="74"/><line x1="58" y1="40" x2="58" y2="74"/><line x1="72" y1="40" x2="72" y2="74"/><line x1="16" y1="82" x2="84" y2="82"/></svg>
                                                @break
                                            @case('state')
                                                <svg viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="5" stroke-linejoin="round" stroke-linecap="round"><rect x="24" y="22" width="52" height="60"/><line x1="38" y1="34" x2="38" y2="54"/><line x1="50" y1="34" x2="50" y2="54"/><line x1="62" y1="34" x2="62" y2="54"/><rect x="42" y="64" width="16" height="18"/></svg>
                                                @break
                                            @case('local')
                                                <svg viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round"><line x1="22" y1="20" x2="78" y2="20"/><line x1="22" y1="80" x2="78" y2="80"/><line x1="30" y1="20" x2="30" y2="80"/><line x1="43" y1="20" x2="43" y2="80"/><line x1="57" y1="20" x2="57" y2="80"/><line x1="70" y1="20" x2="70" y2="80"/></svg>
                                                @break
                                            @case('investigation')
                                                <svg viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round"><circle cx="44" cy="44" r="24"/><line x1="62" y1="62" x2="80" y2="80"/></svg>
                                                @break
                                            @case('prosecution')
                                                <svg viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"><line x1="50" y1="18" x2="50" y2="80"/><line x1="30" y1="82" x2="70" y2="82"/><line x1="24" y1="30" x2="76" y2="30"/><path d="M24 30 L14 52 H34 Z"/><path d="M76 30 L66 52 H86 Z"/><circle cx="50" cy="22" r="5"/></svg>
                                                @break
                                            @case('appeals')
                                                <svg viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="5" stroke-linejoin="round" stroke-linecap="round"><path d="M24 34 V84 H58"/><rect x="32" y="22" width="40" height="52" rx="3"/><line x1="42" y1="38" x2="62" y2="38"/><line x1="42" y1="48" x2="62" y2="48"/><line x1="42" y1="58" x2="56" y2="58"/></svg>
                                                @break
                                        @endswitch
                                    </span>
                                </div>
                            </div>
                            <div class="tk2-costcap-body">
                                <div class="tk2-costcap-amount">{{ $tkMoney($card['value']) }}</div>
                                <div class="tk2-costcap-title"><span>{{ $card['label'] }}</span></div>
                                <p class="tk2-costcap-text">{{ $card['blurb'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- 07 METHODOLOGY --}}
            <section id="methodology" class="tk2-section">
                <div class="tk2-snum">07</div>
                <h2 class="tk2-shead">How we count.</h2>
                <div class="tk2-method">
                    <p><strong>Scope.</strong> A &ldquo;political prisoner&rdquo; in the NPPC archive is a person held in U.S. custody, or driven into exile from the U.S., for activity reasonably understood as political &mdash; movement organizing, civil resistance, militant action, dissident speech, whistleblowing, protest. The standard is descriptive (was the prosecution political in character?), not endorsement of the underlying conduct.</p>

                    <p><strong>Federal rates &mdash; year by year.</strong> Each day a prisoner spent in federal custody is priced at the federal per-prisoner rate <em>for that fiscal year</em>, taken directly from the Bureau of Prisons&rsquo; own <em>Annual Determination of Average Cost of Incarceration Fee</em> notices in the Federal Register. The series runs from about ${{ number_format($methodFedRateRange['min']) }}/day in {{ $methodFedRateRange['minYear'] }} ($13,162/year) to roughly ${{ number_format($methodFedRateRange['max']) }}/day in {{ $methodFedRateRange['maxYear'] }} ($47,400/year). A day served in 1985 isn&rsquo;t billed at 2024 rates &mdash; that&rsquo;s a deliberate choice, and it&rsquo;s the biggest difference between this tracker and back-of-the-envelope estimates that multiply total days by a single recent figure.</p>

                    <p><strong>State rates &mdash; state by state, year by year.</strong> Every U.S. state&rsquo;s Department of Corrections publishes a per-prisoner annual cost. Recent figures range from roughly $17,000/year (Alabama, Mississippi) to roughly $133,000/year (California) &mdash; an 8&times; spread. We use the state in each case&rsquo;s <code>Institution.state</code> column to pick the right base rate, then roll it back through history using a 3.36% annual cost-growth factor derived from the BOP federal series. If a case&rsquo;s institution has no state recorded, we fall back to the 50-state mean ($35,810/year) at the right year. Sources: Vera Institute&rsquo;s <em>Price of Prisons</em> series, BJS state-prison expenditure reports, and individual state DOC budgets compiled for FY 2020.</p>

                    <p><strong>Local jail rates.</strong> County and city jails are priced at the BJS Survey of Jails national average ($34,700/year as of 2020), rolled back through history the same way as state rates. Jails are typically cheaper per day than state prisons because much of the population is pre-trial or short-stay.</p>

                    <p><strong>Per-case investigation cost &mdash; tier-graded.</strong> The investigation bucket covers the money spent before a case becomes a prosecution: FBI surveillance and informants, Joint Terrorism Task Force stings, COINTELPRO programs (Black nationalist, New Left, White Hate, SWP, CP), the post-9/11 frame-up cases (Newburgh 4, Liberty City 7, Fort Dix 5, Cromitie sting), state-police intelligence units, and grand-jury empanelment time. Tier figures (2020 USD, then rolled back to arrest year): capital cases ~$1,500,000; complex federal (RICO / terrorism / sedition / espionage / FARA / CCE) ~$500,000; ordinary federal felony ~$150,000; state violent ~$50,000; state non-violent ~$15,000; federal misdemeanor ~$5,000; state misdemeanor ~$1,500. Sources: Church Committee Final Report (1976, books II-VI) on COINTELPRO operational budgets; GAO and DOJ OIG reports on FBI investigative expenditure; Brennan Center for Justice and ACLU analyses of post-9/11 JTTF per-target costs; Center for Investigative Reporting / Mother Jones analyses of FBI informant-network expenditure ($3.3B since 2001 across all national-security investigations).</p>

                    <p><strong>Per-case prosecution cost &mdash; tier-graded.</strong> A capital-murder trial does not cost the same as a trespass prosecution. We classify each case from its <code>charges</code> and <code>sentence</code> text into one of seven tiers and price it accordingly (all figures in 2020 dollars, then rolled back to the arrest year):</p>

                    <ul class="tk2-method-list">
                        <li><strong>Capital cases &mdash; ~$2,000,000.</strong> Death-penalty or aggravated/first-degree murder. Source: Death Penalty Information Center; Loyola Law School / Alarc&oacute;n &amp; Mitchell capital-cost study.</li>
                        <li><strong>Complex federal &mdash; ~$400,000.</strong> RICO, terrorism, seditious conspiracy, espionage, FARA, continuing criminal enterprise. Source: DOJ OIG complex-prosecution reports.</li>
                        <li><strong>Federal felony &mdash; ~$120,000.</strong> Standard federal felony. Source: Federal Judicial Center / Administrative Office of the U.S. Courts.</li>
                        <li><strong>State violent felony &mdash; ~$80,000.</strong> Murder, manslaughter, assault, robbery, rape, kidnapping, arson.</li>
                        <li><strong>State non-violent felony &mdash; ~$30,000.</strong> Drug offenses, fraud, theft, burglary, conspiracy, sabotage, property destruction.</li>
                        <li><strong>Federal misdemeanor &mdash; ~$20,000.</strong></li>
                        <li><strong>State misdemeanor &mdash; ~$5,000.</strong> Trespass, disorderly conduct, loitering, contempt. Source: BJS Census of State Court Prosecutors; Sixth Amendment Center indigent-defense reports.</li>
                    </ul>

                    <p><strong>Per-case appeals & post-conviction cost &mdash; also tier-graded.</strong> Capital appellate litigation (state and federal habeas, often spanning decades) runs ~$1.5M in 2020 dollars; complex federal appeals ~$150,000; ordinary federal felony appeals ~$60,000; state violent felony ~$50,000; state non-violent ~$25,000; misdemeanors $3,000-$8,000. Year-adjusted to arrest year. Applied only to cases with a populated <code>convicted</code>, <code>plead</code>, or <code>sentence</code> field &mdash; acquittals and dismissals stop the meter.</p>

                    <p><strong>Year-by-year priced.</strong> For continuous incarcerations that span multiple calendar years, each year&rsquo;s days are priced at that year&rsquo;s rate &mdash; not the rate at the start or the end. Internally we walk the period day-by-day across year boundaries and apply each year&rsquo;s rate separately.</p>

                    <p><strong>Days counted.</strong> For each case we compute calendar days between the earliest documented arrest, incarceration, or exile date and the matching release date (or today, if still active). Time on parole, supervised release, and house arrest is included when our source material treats it as continuing custody.</p>

                    <p><strong>Sourcing.</strong> Cases are built from court records, FBI files released under FOIA, contemporary movement press, oral histories, and the archives of long-running support organizations.</p>

                    <p><strong>Updates.</strong> Numbers refresh on every page load &mdash; nothing here is cached longer than the underlying database. If you see a case missing or a cost assumption you&rsquo;d challenge, <a href="/form/contact">tell us</a>.</p>
                </div>
            </section>

        </div>

        {{-- Closing block — repeats the live ticker + share over the
             full-bleed map artwork, mirroring the hero. --}}
        <div class="tk2-footer" id="tk2-footer">
            <img class="tk2-footer-bg" src="/images/tracker-footer-map.png" alt="" aria-hidden="true">
            <div class="tk2-footer-spot-overlay" aria-hidden="true"></div>
            <div class="tk2-footer-spot-highlight" aria-hidden="true"></div>
            <div class="tk2-footer-content">
                <section class="tk2-banner tk2-banner--footer">
                    <div class="tk2-banner-inner">
                        <div class="tk2-banner-num"><span class="tk2-banner-sign">$</span><span data-tk-counter="{{ $totalCost }}" data-tk-per-second="{{ $perSecondOngoingCost }}">0</span></div>
                    </div>
                </section>
                <div class="tk2-share-bar tk2-share-bar--footer">
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode('The Price of Political Prosecution — $'.number_format($totalCost).' spent prosecuting and incarcerating U.S. political prisoners.') }}&url={{ urlencode(url('/tracker')) }}" target="_blank" rel="noopener" aria-label="Share on X / Twitter">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/tracker')) }}" target="_blank" rel="noopener" aria-label="Share on Facebook">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.412c0-3.027 1.792-4.7 4.533-4.7 1.312 0 2.686.235 2.686.235v2.97h-1.513c-1.491 0-1.956.93-1.956 1.886v2.27h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073"/></svg>
                    </a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url('/tracker')) }}" target="_blank" rel="noopener" aria-label="Share on LinkedIn">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    </a>
                    <a href="https://bsky.app/intent/compose?text={{ urlencode('The Price of Political Prosecution — '.url('/tracker')) }}" target="_blank" rel="noopener" aria-label="Share on Bluesky">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5.07 3.04c2.68 2.01 5.56 6.09 6.62 8.28 1.06-2.19 3.94-6.27 6.62-8.28 1.93-1.45 5.07-2.58 5.07.99 0 .71-.41 5.98-.65 6.84-.83 2.97-3.86 3.73-6.55 3.27 4.71.8 5.9 3.45 3.32 6.11-4.91 5.04-7.05-1.27-7.6-2.88-.1-.3-.15-.43-.15-.31 0-.12-.05.01-.15.31-.55 1.61-2.69 7.92-7.6 2.88-2.58-2.66-1.39-5.31 3.32-6.11-2.69.46-5.72-.3-6.55-3.27C.41 9.02 0 3.75 0 3.04 0-.53 3.14.59 5.07 3.04z"/></svg>
                    </a>
                    <a href="mailto:?subject={{ urlencode('The Price of Political Prosecution') }}&body={{ urlencode(url('/tracker')) }}" aria-label="Share via email">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2zm-2 0l-8 5-8-5zm0 12H4V8l8 5 8-5z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </article>

    <style>
        body.page-tracker { background: #000 !important; color: #fff; overflow-x: hidden; }
        /* Full-bleed closing block: a large map backdrop with the ticker +
           share repeated over it (mirrors the hero). The map is sized
           independently of the coral ticker, which always spans the page. */
        .tk2-footer { position: relative; left: 50%; margin: 64px 0 0 -50vw; width: 100vw; overflow: hidden; background: #000; display: flex; align-items: center; justify-content: center; cursor: none; --spot-radius: 240px; --spot-brightness: 0.65; }
        .tk2-footer-bg { position: relative; width: clamp(900px, 135vw, 2400px); height: auto; display: block; margin: 0 auto; z-index: 0; opacity: 0.25; }
        /* Dot-matrix overlay + cursor-follow searchlight, mirroring the hero. */
        .tk2-footer::before { content: ''; position: absolute; inset: 0; background-image: radial-gradient(rgba(255,255,255,0.16) 0.5px, transparent 0.9px); background-size: 4px 4px; pointer-events: none; mix-blend-mode: overlay; z-index: 1; }
        .tk2-footer-spot-overlay { position: absolute; inset: 0; background: #000; opacity: 0; transition: opacity 0.3s; pointer-events: none; z-index: 3; }
        .tk2-footer.spot-active .tk2-footer-spot-overlay { opacity: 0.55; -webkit-mask-image: radial-gradient(circle var(--spot-radius) at var(--mx) var(--my), transparent 0%, rgba(0,0,0,0.35) 45%, #000 100%); mask-image: radial-gradient(circle var(--spot-radius) at var(--mx) var(--my), transparent 0%, rgba(0,0,0,0.35) 45%, #000 100%); }
        .tk2-footer-spot-highlight { position: absolute; inset: 0; opacity: 0; mix-blend-mode: overlay; transition: opacity 0.3s; pointer-events: none; z-index: 4; }
        .tk2-footer.spot-active .tk2-footer-spot-highlight { opacity: var(--spot-brightness); background: radial-gradient(circle var(--spot-radius) at var(--mx) var(--my), rgba(255,255,255,0.75) 0%, rgba(255,255,255,0.3) 40%, transparent 70%); }
        .tk2-footer-content { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 5; }
        .tk2-footer .tk2-share-bar--footer a { cursor: pointer; }
        /* Coral ticker bar spans the full page width regardless of map size;
           share row sits directly beneath it. */
        .tk2-footer .tk2-banner--footer { position: relative; left: auto; right: auto; margin: 0; width: 100vw; }
        .tk2-footer .tk2-share-bar--footer { position: relative; margin: 0; padding: 24px 0 0; }
        @media (max-width: 700px) {
            .tk2-footer-bg { width: 165vw; }
            .tk2-footer .tk2-share-bar--footer { padding-top: 14px; }
        }
        body.page-tracker main.container, body.page-tracker .container { max-width: none !important; padding-left: 0 !important; padding-right: 0 !important; overflow: visible !important; }

        .tk2 { background: #000; color: #fff; font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif; padding: 0 0 88px; }

        /* TOP NAV STRIP */
        /* Section nav — centered strip below the hero, sticky to the top
           of the viewport once you scroll past the banner. */
        .tk2-anchors { display: flex; flex-wrap: wrap; gap: 12px 32px; align-items: center; justify-content: center; padding: 20px 32px; border-top: 1px solid rgba(255,255,255,0.12); border-bottom: 1px solid rgba(255,255,255,0.12); position: sticky; top: 0; background: rgba(0,0,0,0.92); backdrop-filter: blur(8px); z-index: 50; }
        .tk2-anchors a { font-size: 13px; font-weight: 900; letter-spacing: 0.08em; text-transform: uppercase; color: #4dd9d2; text-decoration: none; display: inline-flex; align-items: center; gap: 16px; }
        .tk2-anchors a:hover { color: #fff; }
        .tk2-anchors a span { color: #4dd9d2; font-size: 16px; font-weight: 400; }
        .tk2-anchors a:last-child span { display: none; }

        /* HERO */
        /* Viewport-escape so the hero strip and coral banner always span
           100vw regardless of any ancestor container max-width or padding. */
        .tk2-hero, .tk2-banner { position: relative; left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw; width: 100vw; max-width: 100vw; }
        .tk2-hero { padding: 0; min-height: 760px; display: flex; align-items: flex-end; justify-content: center; overflow: hidden; }
        .tk2-hero-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: center top; display: block; z-index: 0; }
        /* Dot-matrix overlay — fine white dots scattered across the photo
           to give it the same printed-newsprint texture the old hero had. */
        .tk2-hero::before { content: ''; position: absolute; inset: 0; background-image: radial-gradient(rgba(255,255,255,0.16) 0.5px, transparent 0.9px); background-size: 4px 4px; pointer-events: none; mix-blend-mode: overlay; z-index: 1; }
        .tk2-hero::after { content: ''; position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,0) 60%, rgba(0,0,0,0.65) 100%); pointer-events: none; z-index: 2; }
        /* Searchlight — same cursor-follow spotlight as the About page.
           A soft white glow brightens the photo under the cursor; an
           optional dark overlay (off by default) can dim the rest. */
        .tk2-hero { cursor: none; --spot-radius: 240px; --spot-brightness: 0.65; }
        .tk2-hero-spot-overlay { position: absolute; inset: 0; background: #000; opacity: 0; transition: opacity 0.3s; pointer-events: none; z-index: 3; }
        .tk2-hero.spot-active .tk2-hero-spot-overlay { opacity: 0.55; -webkit-mask-image: radial-gradient(circle var(--spot-radius) at var(--mx) var(--my), transparent 0%, rgba(0,0,0,0.35) 45%, #000 100%); mask-image: radial-gradient(circle var(--spot-radius) at var(--mx) var(--my), transparent 0%, rgba(0,0,0,0.35) 45%, #000 100%); }
        .tk2-hero-spot-highlight { position: absolute; inset: 0; opacity: 0; mix-blend-mode: overlay; transition: opacity 0.3s; pointer-events: none; z-index: 4; }
        .tk2-hero.spot-active .tk2-hero-spot-highlight { opacity: var(--spot-brightness); background: radial-gradient(circle var(--spot-radius) at var(--mx) var(--my), rgba(255,255,255,0.75) 0%, rgba(255,255,255,0.3) 40%, transparent 70%); }
        .tk2-hero-title { position: relative; z-index: 5; font-family: 'Playfair Display', Georgia, serif; font-style: italic; font-weight: 700; font-size: clamp(2.5rem, 5vw, 4rem); color: #fff; text-align: center; margin: 0 0 32px; letter-spacing: -0.01em; text-shadow: 0 2px 14px rgba(0,0,0,0.5); }

        /* CORAL BANNER WITH BIG NUMBER */
        .tk2-banner { background: #f25c54; padding: 36px 32px 28px; }
        .tk2-banner-inner { max-width: none; margin: 0; text-align: center; }
        .tk2-banner-num { font-family: 'Inter', sans-serif; font-weight: 900; font-size: clamp(3.5rem, 10vw, 8rem); line-height: 1; letter-spacing: -0.04em; color: #0a0a0a; font-variant-numeric: tabular-nums; display: inline-flex; align-items: baseline; }
        .tk2-banner-sign { font-size: 0.7em; margin-right: 0.05em; font-weight: 900; }
        .tk2-banner-sub { text-align: center; font-size: 16px; line-height: 1.5; color: #fff; max-width: 720px; margin: 24px auto 16px; padding: 0 32px; font-weight: 700; }

        /* SHARE BAR */
        .tk2-share-bar { display: flex; justify-content: center; align-items: center; gap: 40px; padding: 32px 32px 56px; }
        .tk2-share-bar a { color: #fff; transition: color 0.15s, transform 0.15s; }
        .tk2-share-bar a:hover { color: #4dd9d2; transform: translateY(-2px); }
        .tk2-share-bar svg { width: 30px; height: 30px; fill: currentColor; display: block; }
        @media (max-width: 600px) { .tk2-share-bar { gap: 28px; } .tk2-share-bar svg { width: 26px; height: 26px; } }

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
        .tk2-bubbles { padding: 24px 0 8px; position: relative; }
        .tk2-bubbles-canvas { position: relative; width: 100%; height: 540px; overflow: hidden; touch-action: none; user-select: none; -webkit-user-select: none; }
        .tk2-bubble { position: absolute; top: 0; left: 0; border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: calc(var(--bs, 200px) * 0.055); color: #fff; cursor: grab; box-sizing: border-box; will-change: transform; }
        .tk2-bubble:active { cursor: grabbing; }
        /* NPPC accent palette — variations of the site's #5660fe indigo,
           all dark enough that pure-white text reads at every size. */
        /* Even dark→light gradient from #15173a (Investigations, darkest)
           to #5660fe (Local jail time, brightest) — six equally-spaced
           steps in their existing dark-to-light order. */
        .tk2-bubble-f { background: #15173a; }   /* Investigations — step 1 (darkest) */
        .tk2-bubble-e { background: #222661; }   /* Appeals & post-conviction — step 2 */
        .tk2-bubble-a { background: #2f3488; }   /* Federal incarceration — step 3 */
        .tk2-bubble-b { background: #3c43b0; }   /* State incarceration — step 4 */
        .tk2-bubble-d { background: #4951d7; }   /* Prosecution — step 5 */
        .tk2-bubble-c { background: #5660fe; }   /* Local jail time — step 6 (brightest) */
        .tk2-bubble-tooltip { position: absolute; left: 0; top: 0; pointer-events: none; background: #fff; color: #0a0a0a; border-radius: 4px; padding: 10px 14px 12px; min-width: 160px; box-shadow: 0 8px 24px rgba(0,0,0,0.5); z-index: 20; }
        .tk2-bubble-tooltip[hidden] { display: none; }
        .tk2-bubble-tooltip::after { content: ''; position: absolute; left: 50%; border: 7px solid transparent; }
        /* Default: tooltip is ABOVE the bubble with the arrow pointing down. */
        .tk2-bubble-tooltip::after { top: 100%; transform: translateX(-50%); border-top-color: #fff; }
        /* When the bubble is near the canvas top, the tooltip flips BELOW
           with the arrow pointing up. */
        .tk2-bubble-tooltip.is-below::after { top: auto; bottom: 100%; transform: translateX(-50%); border-top-color: transparent; border-bottom-color: #fff; }
        .tk2-bubble-tooltip-label { font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 800; color: #0a0a0a; letter-spacing: 0.02em; }
        .tk2-bubble-tooltip-value { font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 600; color: #4a4a4a; margin-top: 2px; }
        /* Font sizes scale with the bubble diameter so the label + value
           always fit, large or small. --bs is set inline in PHP from the
           computed size in px. */
        .tk2-bubble-label { font-family: 'Inter', system-ui, sans-serif; font-weight: 700; letter-spacing: -0.005em; font-size: clamp(9px, calc(var(--bs, 200px) * 0.07), 18px); line-height: 1.1; padding: 0 4px; margin-bottom: calc(var(--bs, 200px) * 0.025); max-width: 94%; color: #fff; pointer-events: none; }
        /* Value sits on a single line — long dollar strings like
           $408,882,383 wouldn't survive wrapping inside a circle, so
           we lock white-space and scale the font down enough to fit. */
        .tk2-bubble-value { font-family: 'Inter', system-ui, sans-serif; font-weight: 900; letter-spacing: -0.02em; font-size: clamp(11px, calc(var(--bs, 200px) * 0.095), 28px); line-height: 1; font-variant-numeric: tabular-nums; white-space: nowrap; max-width: 94%; color: #fff; pointer-events: none; }
        .tk2-bubbles-hint { text-align: center; font-size: 12px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255,255,255,0.45); margin: 16px 0 0; font-style: italic; }
        @media (max-width: 700px) {
            .tk2-bubbles-canvas { height: 420px; }
            .tk2-bubble { transform-origin: top left; }
        }

        /* MOST COMMON CHARGES grid */
        .tk2-charges { display: grid; grid-template-columns: repeat(5, 1fr); gap: 36px 24px; margin-top: 8px; }
        .tk2-charge { text-align: center; }
        .tk2-charge-dot-wrap { height: 124px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px; }
        .tk2-charge-dot { display: block; border-radius: 50%; background: radial-gradient(circle at 35% 30%, #6b73ff, #3a3fa3); }
        .tk2-charge-count { font-family: 'Playfair Display', Georgia, serif; font-size: 1.75rem; font-weight: 900; line-height: 1; color: #fff; margin-bottom: 6px; font-variant-numeric: tabular-nums; }
        .tk2-charge-label { font-size: 13px; font-weight: 700; color: rgba(255,255,255,0.82); line-height: 1.3; max-width: 200px; margin: 0 auto 6px; }
        .tk2-charge-cost { font-size: 11px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #4dd9d2; }
        @media (max-width: 900px) {
            .tk2-charges { grid-template-columns: repeat(3, 1fr); gap: 28px 16px; }
            .tk2-charge-dot-wrap { height: 96px; }
        }
        @media (max-width: 560px) {
            .tk2-charges { grid-template-columns: repeat(2, 1fr); }
        }

        /* Section icon (line-art glyph, forced white on the dark bg) */
        .tk2-section-icon { display: block; width: 88px; height: auto; margin: 0 0 20px; filter: brightness(0) invert(1); opacity: 0.92; }

        /* AFFILIATION-OVER-TIME chart */
        .tk2-affil-wrap { position: relative; width: 100%; }
        .tk2-affil-wrap canvas { width: 100% !important; }

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

        /* WHERE IT GOES — CAP-style alternating cost cards */
        .tk2-costcap-head { text-align: center; margin-bottom: 84px; }
        .tk2-costcap-eyebrow { display: inline-block; background: #4dd9d2; color: #06302e; font-family: 'Inter', sans-serif; font-weight: 800; font-size: 12px; letter-spacing: 0.16em; text-transform: uppercase; padding: 5px 11px; }
        .tk2-costcap-total { margin-top: 18px; font-family: 'Inter', sans-serif; font-weight: 900; font-size: clamp(2.6rem, 7vw, 5rem); color: #f25c54; letter-spacing: -0.03em; line-height: 1; font-variant-numeric: tabular-nums; }
        .tk2-costcap-list { display: flex; flex-direction: column; gap: 110px; }
        .tk2-costcap-row { display: grid; grid-template-columns: 1fr 1fr; gap: 56px; align-items: center; }
        .tk2-costcap-row.is-imgright { direction: rtl; }
        .tk2-costcap-row.is-imgright > * { direction: ltr; }
        .tk2-costcap-figure { display: flex; justify-content: center; }
        .tk2-costcap-diamond { position: relative; width: 300px; height: 300px; max-width: 64vw; max-height: 64vw; transform: rotate(45deg); background: linear-gradient(135deg, #8d939c 0%, #565b63 55%, #3c4047 100%); border-radius: 4px; display: flex; align-items: center; justify-content: center; box-shadow: 0 26px 64px rgba(0,0,0,0.55); }
        .tk2-costcap-diamond::after { content: ''; position: absolute; left: -12px; bottom: -12px; width: 66px; height: 66px; background: #4dd9d2; z-index: -1; }
        .tk2-costcap-emblem { transform: rotate(-45deg); width: 48%; color: #fff; opacity: 0.9; }
        .tk2-costcap-emblem svg { width: 100%; height: auto; display: block; }
        .tk2-costcap-amount { font-family: 'Inter', sans-serif; font-weight: 900; font-size: clamp(2.4rem, 5.5vw, 3.6rem); color: #4dd9d2; line-height: 1; letter-spacing: -0.02em; font-variant-numeric: tabular-nums; }
        .tk2-costcap-title { margin: 16px 0 18px; }
        .tk2-costcap-title span { display: inline; background: #fff; color: #0a0a0a; font-family: 'Inter', sans-serif; font-weight: 800; font-size: clamp(1rem, 2.4vw, 1.35rem); text-transform: uppercase; letter-spacing: 0.005em; padding: 4px 9px; line-height: 1.75; box-decoration-break: clone; -webkit-box-decoration-break: clone; }
        .tk2-costcap-text { color: rgba(255,255,255,0.72); font-size: 15px; line-height: 1.7; max-width: 44ch; margin: 0; }

        /* METHODOLOGY */
        .tk2-method p { font-size: 16px; line-height: 1.7; color: rgba(255,255,255,0.78); margin: 0 0 22px; max-width: 720px; }
        .tk2-method p strong { color: #fff; font-weight: 800; }
        .tk2-method a { color: #4dd9d2; text-decoration: underline; }
        .tk2-method code { font-family: ui-monospace, Menlo, monospace; font-size: 13px; background: rgba(255,255,255,0.08); padding: 1px 6px; border-radius: 3px; color: #fff; }
        .tk2-method-list { list-style: none; padding: 0; margin: 0 0 22px; max-width: 760px; }
        .tk2-method-list li { font-size: 15px; line-height: 1.55; color: rgba(255,255,255,0.78); padding: 10px 0; border-top: 1px solid rgba(255,255,255,0.1); }
        .tk2-method-list li:last-child { border-bottom: 1px solid rgba(255,255,255,0.1); }
        .tk2-method-list li strong { color: #fff; font-weight: 800; }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .tk2-anchors { gap: 8px 16px; padding: 14px 20px; }
            .tk2-anchors a { font-size: 11px; gap: 8px; }
            .tk2-hero { min-height: 420px; padding: 0; cursor: auto; }
            .tk2-hero-title { font-size: 2rem; margin-bottom: 24px; }
            .tk2-banner { padding: 24px 20px 20px; }
            .tk2-banner-sub { padding: 0 20px; font-size: 14px; }
            .tk2-body { padding: 0 20px; }
            .tk2-snum { font-size: 8rem; margin-bottom: -68px; }
            .tk2-bignums { grid-template-columns: repeat(2, 1fr); gap: 16px; }
            .tk2-bar { grid-template-columns: 1fr; gap: 6px; padding: 14px 0; }
            .tk2-bar-value { text-align: left; }
            .tk2-costcap-list { gap: 64px; }
            .tk2-costcap-row, .tk2-costcap-row.is-imgright { grid-template-columns: 1fr; direction: ltr; gap: 28px; }
            .tk2-costcap-figure { order: -1; }
            .tk2-costcap-diamond { width: 220px; height: 220px; }
            .tk2-costcap-head { margin-bottom: 56px; }
        }
    </style>

    <script>
        (function () {
            // Animate every ticker on the page (hero + footer repeat) so
            // both climb in lockstep.
            const els = Array.from(document.querySelectorAll('[data-tk-counter]'));
            if (! els.length) return;
            const baseTarget = parseFloat(els[0].getAttribute('data-tk-counter')) || 0;
            const perSecond  = parseFloat(els[0].getAttribute('data-tk-per-second')) || 0;
            const fmt = new Intl.NumberFormat('en-US');
            let started = false;
            const animate = () => {
                if (started) return; started = true;
                const duration = 2200;
                const start = performance.now();

                // Phase 1: ease 0 -> baseTarget. Phase 2: keep adding
                // perSecond × elapsed at the live incarceration rate.
                const tick = (now) => {
                    const elapsedMs = now - start;
                    let value;
                    if (elapsedMs < duration) {
                        const t = elapsedMs / duration;
                        const eased = 1 - Math.pow(1 - t, 3);
                        value = baseTarget * eased;
                    } else {
                        const extraSec = (elapsedMs - duration) / 1000;
                        value = baseTarget + perSecond * extraSec;
                    }
                    const text = fmt.format(Math.floor(value));
                    els.forEach(el => { el.textContent = text; });
                    requestAnimationFrame(tick);
                };
                requestAnimationFrame(tick);
            };
            if ('IntersectionObserver' in window) {
                const io = new IntersectionObserver((entries) => {
                    if (entries.some((e) => e.isIntersecting)) { animate(); io.disconnect(); }
                });
                io.observe(els[0]);
            } else {
                animate();
            }
        })();

        // Hero searchlight — cursor-follow spotlight, same as the About page.
        (function () {
            const hero = document.getElementById('tk2-hero');
            if (! hero) return;
            // Skip on touch devices (no hover) — leave the photo fully lit.
            if (window.matchMedia('(hover: none)').matches) { hero.style.cursor = 'auto'; return; }
            hero.addEventListener('mouseenter', () => hero.classList.add('spot-active'));
            hero.addEventListener('mouseleave', () => hero.classList.remove('spot-active'));
            hero.addEventListener('mousemove', (e) => {
                const rect = hero.getBoundingClientRect();
                hero.style.setProperty('--mx', (e.clientX - rect.left) + 'px');
                hero.style.setProperty('--my', (e.clientY - rect.top) + 'px');
            });
        })();

        // Footer map searchlight — same cursor-follow spotlight as the hero.
        (function () {
            const footer = document.getElementById('tk2-footer');
            if (! footer) return;
            if (window.matchMedia('(hover: none)').matches) { footer.style.cursor = 'auto'; return; }
            footer.addEventListener('mouseenter', () => footer.classList.add('spot-active'));
            footer.addEventListener('mouseleave', () => footer.classList.remove('spot-active'));
            footer.addEventListener('mousemove', (e) => {
                const rect = footer.getBoundingClientRect();
                footer.style.setProperty('--mx', (e.clientX - rect.left) + 'px');
                footer.style.setProperty('--my', (e.clientY - rect.top) + 'px');
            });
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
                const ringRadius = Math.min(width, height) * 0.30;
                const hx = cx + Math.cos(angle) * ringRadius;
                const hy = cy + Math.sin(angle) * ringRadius;
                const body = Bodies.circle(hx, hy, r, {
                    restitution: 0.4,
                    friction: 0,
                    frictionAir: 0.06,
                    density: 0.001,
                    // Rotation is allowed but a per-tick restoring torque
                    // applied below keeps each bubble naturally upright —
                    // like a weighted bath toy: you can spin it, but it
                    // wobbles back to label-up on its own.
                });
                // Each bubble's "home" is its own spot on the ring (not
                // the shared centre) so they rest spread out and drift
                // independently rather than as one clump.
                body.elem = el;
                body.home = { x: hx, y: hy };
                // Per-body phase offsets + widely-varied frequencies so no
                // two bubbles sway on the same cycle.
                body.swayPhaseX = Math.random() * Math.PI * 2;
                body.swayPhaseY = Math.random() * Math.PI * 2;
                body.swaySpeedX = 0.35 + Math.random() * 0.65; // rad/s
                body.swaySpeedY = 0.30 + Math.random() * 0.60;
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
                    // Soft home spring — loose enough that bubbles can wander
                    // a fair way from their spot before easing back, so the
                    // group breathes apart rather than locking together.
                    const k = 0.0000012;
                    Body.applyForce(body, body.position, {
                        x: dx * k * body.mass,
                        y: dy * k * body.mass,
                    });
                    // Continuous balloon-style sway: two phase-offset
                    // sinusoidal forces (x slower, y slightly faster)
                    // so each bubble bobs gently around its home even
                    // at rest. Phase + speed are per-body so the cluster
                    // doesn't move in lockstep.
                    const t = engine.timing.timestamp * 0.001; // seconds
                    Body.applyForce(body, body.position, {
                        x: Math.sin(t * body.swaySpeedX + body.swayPhaseX) * 0.0000325 * body.mass,
                        y: Math.cos(t * body.swaySpeedY + body.swayPhaseY) * 0.0000375 * body.mass,
                    });
                    // Tiny Brownian wobble on top of the sway for organic feel.
                    Body.applyForce(body, body.position, {
                        x: (Math.random() - 0.5) * 0.000008 * body.mass,
                        y: (Math.random() - 0.5) * 0.000008 * body.mass,
                    });

                    // Buoyancy-toy uprighting: a spring torque toward
                    // angle 0 plus angular damping. Bumping a bubble
                    // can tilt it, but it wobbles back to label-up
                    // within a second or so.
                    let a = body.angle % (Math.PI * 2);
                    if (a >  Math.PI) a -= Math.PI * 2;
                    if (a < -Math.PI) a += Math.PI * 2;
                    const restore = -a * body.mass * 0.0035;
                    const damping = -body.angularVelocity * body.mass * 0.025;
                    body.torque += restore + damping;
                });

                // Pair-wise magnetism: every pair of bubbles attracts each
                // other with a gentle force inversely proportional to
                // distance squared (gravitational-style). With six bubbles
                // that's 15 pairs — negligible compute, and the effect is
                // only meaningful when one drifts far from the cluster:
                // close-together bubbles' attractions roughly cancel.
                const G = 0.00012;
                for (let i = 0; i < bodies.length; i++) {
                    for (let j = i + 1; j < bodies.length; j++) {
                        const a = bodies[i], b = bodies[j];
                        const dx = b.position.x - a.position.x;
                        const dy = b.position.y - a.position.y;
                        const dist2 = dx * dx + dy * dy;
                        const minSep = (a.circleRadius + b.circleRadius) * 1.05;
                        if (dist2 < minSep * minSep) continue; // no pull when touching
                        const dist = Math.sqrt(dist2);
                        const f = (G * a.mass * b.mass) / dist2;
                        const fx = (dx / dist) * f;
                        const fy = (dy / dist) * f;
                        Body.applyForce(a, a.position, { x:  fx, y:  fy });
                        Body.applyForce(b, b.position, { x: -fx, y: -fy });
                    }
                }
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

            // Hover tooltip — single shared element that tracks whichever
            // bubble the cursor is over. Stays pinned to that bubble's
            // current physics position (so it follows when bubbles drift).
            const tip = document.getElementById('tk2-bubble-tooltip');
            const tipLabel = tip?.querySelector('.tk2-bubble-tooltip-label');
            const tipValue = tip?.querySelector('.tk2-bubble-tooltip-value');
            let hoverBody = null;
            bubbles.forEach((el, i) => {
                el.addEventListener('mouseenter', () => {
                    hoverBody = bodies[i];
                    if (tip && tipLabel && tipValue) {
                        tipLabel.textContent = el.dataset.label || '';
                        tipValue.textContent = el.dataset.value || '';
                        tip.hidden = false;
                    }
                });
                el.addEventListener('mouseleave', () => {
                    if (hoverBody === bodies[i]) hoverBody = null;
                    if (tip) tip.hidden = true;
                });
            });

            // Sync DOM transforms to physics each frame.
            (function tick() {
                bodies.forEach(body => {
                    const r = body.circleRadius;
                    body.elem.style.transform = `translate(${body.position.x - r}px, ${body.position.y - r}px) rotate(${body.angle}rad)`;
                });
                if (hoverBody && tip && ! tip.hidden) {
                    // Tooltip lives in .tk2-bubbles (sibling of the canvas),
                    // so add the canvas's offsetTop within that wrapper to
                    // translate from canvas-space to wrapper-space.
                    const canvasOffsetTop = canvas.offsetTop;
                    const r = hoverBody.circleRadius;
                    const aboveY = hoverBody.position.y - r + canvasOffsetTop;
                    const belowY = hoverBody.position.y + r + canvasOffsetTop;
                    // Flip to below the bubble if the tooltip would clip
                    // the top of the page area.
                    const flip = aboveY < tip.offsetHeight + 20;
                    if (flip) {
                        tip.classList.add('is-below');
                        tip.style.transform = `translate(calc(${hoverBody.position.x}px - 50%), calc(${belowY}px + 14px))`;
                    } else {
                        tip.classList.remove('is-below');
                        tip.style.transform = `translate(calc(${hoverBody.position.x}px - 50%), calc(${aboveY}px - 100% - 14px))`;
                    }
                }
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

    {{-- Affiliation-over-time area chart --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        window.addEventListener('load', function () {
            if (typeof Chart === 'undefined') return;
            const canvas = document.getElementById('tk2-affil-chart');
            if (! canvas) return;
            const years = JSON.parse(canvas.dataset.years || '[]');
            const series = JSON.parse(canvas.dataset.series || '[]');
            if (! series.length) return;

            // NPPC indigo gradient + a couple of warm accents so the
            // overlapping movements stay distinguishable.
            const palette = ['#5660fe', '#4dd9d2', '#9b5cff', '#f25c54', '#f5d061', '#3a3fa3'];

            new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: years,
                    datasets: series.map((s, i) => ({
                        label: s.label + ' (' + s.total + ')',
                        data: s.data,
                        borderColor: palette[i % palette.length],
                        backgroundColor: palette[i % palette.length] + '33',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                    })),
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { labels: { color: 'rgba(255,255,255,0.8)', font: { size: 12, weight: '700' }, usePointStyle: true, pointStyle: 'rectRounded' } },
                        tooltip: { backgroundColor: '#fff', titleColor: '#0a0a0a', bodyColor: '#333' },
                    },
                    scales: {
                        x: { grid: { color: 'rgba(255,255,255,0.08)' }, ticks: { color: 'rgba(255,255,255,0.55)', maxRotation: 0, autoSkip: true, maxTicksLimit: 12 } },
                        y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.08)' }, ticks: { color: 'rgba(255,255,255,0.55)', precision: 0 } },
                    },
                },
            });
        });
    </script>
@endsection
