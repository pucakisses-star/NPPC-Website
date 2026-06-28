@extends('app')

@section('title', 'Transnational Repression — How states silence dissent across borders | NPPC')

@section('head')
<meta name="description" content="A report on transnational repression — how governments reach across borders to silence exiles, journalists, and diaspora communities. Built on Freedom House's research, with a live watch of the political exiles the NPPC documents.">
<style>
    /* ============================================================
       Transnational Repression — long-form report.
       Light editorial layout in the format of major human-rights
       reports: full-bleed colour banners with a stacked overline +
       title, alternating with clean light content. Per-section
       accent tints. All classes scoped with the tnr- prefix.
       ============================================================ */
    .tnr { background: #ffffff; color: #1b2333; font-feature-settings: "kern" 1; }
    .tnr * { box-sizing: border-box; }
    .tnr a { color: #2f5c8f; }
    .tnr a:hover { color: #16233f; }

    /* ---- Full-bleed banners ---- */
    .tnr-banner { position: relative; min-height: 460px; display: flex; align-items: flex-end; overflow: hidden; color: #fff; }
    .tnr-banner--hero { min-height: 560px; }
    .tnr-banner-bg { position: absolute; inset: 0; z-index: 0; }
    /* faint dotted "global reach" texture over the gradient */
    .tnr-banner-bg::after { content: ""; position: absolute; inset: 0;
        background-image: radial-gradient(rgba(255,255,255,0.10) 1.2px, transparent 1.2px);
        background-size: 26px 26px; opacity: 0.6;
        mask-image: linear-gradient(180deg, transparent, #000 60%); }
    .tnr-banner-inner { position: relative; z-index: 1; width: 100%; max-width: 1080px; margin: 0 auto; padding: 64px 32px 56px; }
    .tnr-overline { display: inline-block; font-size: 13px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: rgba(255,255,255,0.78); margin-bottom: 14px; padding-bottom: 12px; border-bottom: 2px solid rgba(255,255,255,0.5); }
    .tnr-banner-title { font-size: 3.6rem; line-height: 1.02; font-weight: 800; letter-spacing: -0.015em; margin: 0; color: #fff; }
    .tnr-banner--hero .tnr-banner-title { font-size: 5rem; }
    .tnr-banner-sub { font-size: 1.3rem; line-height: 1.45; color: rgba(255,255,255,0.85); max-width: 640px; margin: 22px 0 0; }
    /* accent gradients per section */
    .tnr-bg-navy   { background: radial-gradient(120% 120% at 80% 0%, #244a72 0%, #16233f 60%, #0f1a30 100%); }
    .tnr-bg-teal   { background: radial-gradient(120% 120% at 80% 0%, #2a8c84 0%, #15433f 70%, #0e2d2a 100%); }
    .tnr-bg-maroon { background: radial-gradient(120% 120% at 80% 0%, #9a3320 0%, #5a160b 65%, #380c05 100%); }
    .tnr-bg-blue   { background: radial-gradient(120% 120% at 80% 0%, #3a6aa0 0%, #1d3c63 65%, #122843 100%); }

    /* ---- Content sections ---- */
    .tnr-wrap { max-width: 760px; margin: 0 auto; padding: 0 32px; }
    .tnr-section { padding: 72px 0; }
    .tnr-section--tint { background: #f6f8fa; }
    .tnr-kicker { display: inline-block; font-size: 12px; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase; color: #2f5c8f; margin-bottom: 14px; }
    .tnr-kicker--teal { color: #1f7d76; }
    .tnr-kicker--maroon { color: #8a2a16; }
    .tnr-h2 { font-size: 2.4rem; line-height: 1.12; font-weight: 800; color: #14203a; margin: 0 0 22px; letter-spacing: -0.015em; }
    .tnr-p { font-size: 18px; line-height: 1.78; color: #3a4658; margin: 0 0 1.3em; }
    .tnr-p strong { color: #14203a; font-weight: 700; }
    .tnr-lead .tnr-p { font-size: 1.4rem; line-height: 1.5; color: #1b2333; }
    .tnr-lead .tnr-p:first-child::first-letter { float: left; font-size: 4em; line-height: 0.72; padding: 0.06em 0.1em 0 0; color: #2f5c8f; font-weight: 800; }

    /* ---- Stats band ---- */
    .tnr-stats-band { background: #14203a; color: #fff; }
    .tnr-stats { max-width: 1080px; margin: 0 auto; padding: 0 32px; display: grid; grid-template-columns: repeat(4, 1fr); }
    .tnr-stat { padding: 52px 22px; border-left: 1px solid rgba(255,255,255,0.12); }
    .tnr-stat:first-child { border-left: 0; }
    .tnr-stat-num { font-size: 3.4rem; line-height: 1; font-weight: 800; color: #7fb2e0; letter-spacing: -0.02em; }
    .tnr-stat-num small { font-size: 0.4em; color: rgba(255,255,255,0.55); font-weight: 700; }
    .tnr-stat-label { margin-top: 14px; font-size: 14px; line-height: 1.5; color: rgba(255,255,255,0.78); }
    .tnr-stats-src { max-width: 1080px; margin: 0 auto; padding: 0 32px 26px; font-size: 12px; color: rgba(255,255,255,0.5); }

    /* ---- Tactic cards ---- */
    .tnr-cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px; margin-top: 30px; }
    .tnr-card { background: #fff; border: 1px solid #e3e7ee; border-top: 4px solid #8a2a16; border-radius: 6px; padding: 26px; transition: box-shadow 0.2s, transform 0.2s; }
    .tnr-card:hover { box-shadow: 0 14px 36px rgba(20,32,58,0.10); transform: translateY(-3px); }
    .tnr-card-icon { color: #8a2a16; margin-bottom: 12px; }
    .tnr-card-icon svg { width: 28px; height: 28px; display: block; }
    .tnr-card h3 { font-size: 1.15rem; font-weight: 800; color: #14203a; margin: 0 0 8px; }
    .tnr-card p { font-size: 14px; line-height: 1.62; color: #51607a; margin: 0; }

    /* ---- Pull quote ---- */
    .tnr-pull { border-left: 4px solid #2f5c8f; padding: 4px 0 4px 26px; margin: 8px 0 4px; }
    .tnr-pull p { font-size: 1.75rem; line-height: 1.32; font-weight: 700; color: #14203a; margin: 0; }

    /* ---- Recommendations ---- */
    .tnr-rec { border-top: 1px solid #e3e7ee; padding: 22px 0; display: grid; grid-template-columns: 190px 1fr; gap: 24px; }
    .tnr-rec:last-child { border-bottom: 1px solid #e3e7ee; }
    .tnr-rec-who { font-size: 13px; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; color: #2f5c8f; }
    .tnr-rec p { font-size: 15px; line-height: 1.65; color: #3a4658; margin: 0; }

    /* ---- NPPC Watch ---- */
    .tnr-live { display: inline-flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; color: #1f9d57; margin-bottom: 18px; }
    .tnr-live-dot { width: 8px; height: 8px; border-radius: 50%; background: #1f9d57; box-shadow: 0 0 0 0 rgba(31,157,87,0.5); animation: tnrpulse 2s infinite; }
    @@keyframes tnrpulse { 0% { box-shadow: 0 0 0 0 rgba(31,157,87,0.45); } 70% { box-shadow: 0 0 0 8px rgba(31,157,87,0); } 100% { box-shadow: 0 0 0 0 rgba(31,157,87,0); } }
    .tnr-watch { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; margin-top: 26px; }
    .tnr-exile { display: grid; grid-template-columns: 58px 1fr; gap: 16px; align-items: start; background: #fff; border: 1px solid #e3e7ee; border-radius: 8px; padding: 16px; text-decoration: none; transition: box-shadow 0.2s, transform 0.2s; }
    .tnr-exile:hover { box-shadow: 0 12px 30px rgba(20,32,58,0.10); transform: translateY(-2px); }
    .tnr-exile-photo { width: 58px; height: 58px; border-radius: 6px; object-fit: cover; object-position: center top; background: #e8ecf2; }
    .tnr-exile-ph { width: 58px; height: 58px; border-radius: 6px; background: #eaeff5; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; color: #2f5c8f; }
    .tnr-exile-name { font-size: 1.02rem; font-weight: 800; color: #14203a; margin: 1px 0 6px; }
    .tnr-exile-desc { font-size: 13px; line-height: 1.55; color: #51607a; margin: 0; }
    .tnr-watch-empty { background: #f6f8fa; border: 1px solid #e3e7ee; border-radius: 8px; padding: 26px; color: #51607a; font-size: 15px; }

    /* ---- Sources ---- */
    .tnr-sources { list-style: none; margin: 22px 0 0; padding: 0; }
    .tnr-sources li { border-top: 1px solid #e3e7ee; padding: 16px 0; }
    .tnr-sources li:last-child { border-bottom: 1px solid #e3e7ee; }
    .tnr-sources a { font-weight: 700; color: #14203a; text-decoration: none; }
    .tnr-sources a:hover { color: #2f5c8f; }
    .tnr-sources span { display: block; font-size: 13px; color: #6b7689; margin-top: 4px; }
    .tnr-disclaimer { margin-top: 26px; font-size: 13px; color: #8390a3; }

    /* ---- CTA ---- */
    .tnr-cta { text-align: center; padding: 84px 32px; color: #fff; }
    .tnr-cta h2 { font-size: 2.4rem; font-weight: 800; color: #fff; margin: 0 0 14px; letter-spacing: -0.01em; }
    .tnr-cta p { font-size: 17px; line-height: 1.65; color: rgba(255,255,255,0.85); max-width: 580px; margin: 0 auto 28px; }
    .tnr-btns { display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; }
    .tnr-btn { display: inline-flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 800; text-decoration: none; padding: 15px 30px; border-radius: 4px; transition: transform 0.2s, background 0.2s; }
    .tnr-btn-primary { background: #fff; color: #14203a; }
    .tnr-btn-primary:hover { transform: translateY(-2px); color: #000; }
    .tnr-btn-ghost { background: transparent; color: #fff; border: 1px solid rgba(255,255,255,0.6); }
    .tnr-btn-ghost:hover { background: rgba(255,255,255,0.14); transform: translateY(-2px); color: #fff; }

    /* ---- Responsive ---- */
    @@media (max-width: 900px) {
        .tnr-banner { min-height: 360px; }
        .tnr-banner--hero { min-height: 440px; }
        .tnr-banner-title { font-size: 2.5rem; }
        .tnr-banner--hero .tnr-banner-title { font-size: 3.1rem; }
        .tnr-banner-sub { font-size: 1.1rem; }
        .tnr-h2 { font-size: 1.9rem; }
        .tnr-stats { grid-template-columns: repeat(2, 1fr); }
        .tnr-stat:nth-child(3) { border-left: 0; }
        .tnr-cards, .tnr-watch { grid-template-columns: 1fr; }
        .tnr-rec { grid-template-columns: 1fr; gap: 6px; }
    }
</style>
@endsection

@section('body')
@php
    use App\Models\Prisoner;

    $exiles = Prisoner::query()
        ->where('currently_in_exile', true)
        ->orderBy('name')
        ->get(['id', 'name', 'slug', 'description', 'photo']);
    $exileCount = $exiles->count();
    $exileLabel = $exileCount ? ' · ' . $exileCount . ' ' . \Illuminate\Support\Str::plural('exile', $exileCount) : '';

    $excerpt = function ($text, $len = 150) {
        $clean = trim(preg_replace('/\s+/', ' ', strip_tags((string) $text)));
        return \Illuminate\Support\Str::limit($clean, $len);
    };
    $initials = function ($name) {
        $parts = preg_split('/\s+/', trim((string) $name));
        $a = $parts[0][0] ?? '';
        $b = count($parts) > 1 ? ($parts[count($parts) - 1][0] ?? '') : '';
        return strtoupper($a . $b);
    };
@endphp

<div class="tnr">

    {{-- ==================== HERO BANNER ==================== --}}
    <section class="tnr-banner tnr-banner--hero">
        <div class="tnr-banner-bg tnr-bg-navy"></div>
        <div class="tnr-banner-inner">
            <span class="tnr-overline">A Report on the Global Reach of Repression</span>
            <h1 class="tnr-banner-title">Transnational<br>Repression</h1>
            <p class="tnr-banner-sub">Governments reaching across borders to silence dissent among diasporas and exiles.</p>
        </div>
    </section>

    {{-- ==================== LEAD ==================== --}}
    <section class="tnr-section">
        <div class="tnr-wrap tnr-lead">
            <span class="tnr-kicker">What it is</span>
            <p class="tnr-p">Transnational repression is what happens when a state reaches beyond its own borders to silence the people it cannot control at home — exiles, refugees, journalists, and diaspora communities. Its tools range from assassination and abduction to unlawful deportation, digital surveillance, and threats against the family members left behind.</p>
            <p class="tnr-p">As Freedom House has documented, it is one of the defining human-rights threats of the era — and it does not stop at the borders of any democracy, including the United States.</p>
        </div>
    </section>

    {{-- ==================== STATS BAND ==================== --}}
    <div class="tnr-stats-band">
        <div class="tnr-stats">
            <div class="tnr-stat"><div class="tnr-stat-num">3.5<small>M+</small></div><div class="tnr-stat-label">People estimated to be at risk worldwide</div></div>
            <div class="tnr-stat"><div class="tnr-stat-num">1,375<small>+</small></div><div class="tnr-stat-label">Direct, physical incidents catalogued since 2014</div></div>
            <div class="tnr-stat"><div class="tnr-stat-num">54</div><div class="tnr-stat-label">Origin governments reaching across borders</div></div>
            <div class="tnr-stat"><div class="tnr-stat-num">107</div><div class="tnr-stat-label">Host countries where incidents have occurred</div></div>
        </div>
        <div class="tnr-stats-src">Figures: Freedom House, <em>Transnational Repression</em> project (incidents catalogued 2014–2025).</div>
    </div>

    {{-- ==================== BANNER: WHO IS AT RISK ==================== --}}
    <section class="tnr-banner">
        <div class="tnr-banner-bg tnr-bg-teal"></div>
        <div class="tnr-banner-inner">
            <span class="tnr-overline">Who Is at Risk?</span>
            <h2 class="tnr-banner-title">Everyday People</h2>
        </div>
    </section>
    <section class="tnr-section">
        <div class="tnr-wrap">
            <p class="tnr-p">The targets are rarely random. They are <strong>journalists and human-rights defenders</strong> whose reporting embarrasses a regime; <strong>political opposition figures</strong> who organize from abroad; <strong>ethnic and religious minorities</strong> treated as suspect wherever they live; and <strong>former insiders</strong> who know too much.</p>
            <p class="tnr-p">The damage radiates outward. When one exile is attacked, an entire diaspora learns to self-censor — to stop attending protests, stop calling home, stop speaking to reporters. That chilling effect, multiplied across millions of people, is the point.</p>
            <div class="tnr-pull"><p>&ldquo;The message is that no one is ever truly out of reach.&rdquo;</p></div>
        </div>
    </section>

    {{-- ==================== BANNER: A GRAVE THREAT (TACTICS) ==================== --}}
    <section class="tnr-banner">
        <div class="tnr-banner-bg tnr-bg-maroon"></div>
        <div class="tnr-banner-inner">
            <span class="tnr-overline">A Grave Threat To</span>
            <h2 class="tnr-banner-title">Democracy &amp; Freedom</h2>
        </div>
    </section>
    <section class="tnr-section">
        <div class="tnr-wrap">
            <span class="tnr-kicker tnr-kicker--maroon">The tactics</span>
            <h2 class="tnr-h2">Four ways states reach across borders</h2>
            <p class="tnr-p">Freedom House groups the tactics of transnational repression into four broad categories.</p>
            <div class="tnr-cards">
                <div class="tnr-card">
                    <div class="tnr-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="9" y1="12" x2="15" y2="12"/></svg></div>
                    <h3>Direct attacks</h3>
                    <p>Assassinations, physical assaults, abductions, and renditions — violence carried out on foreign soil.</p>
                </div>
                <div class="tnr-card">
                    <div class="tnr-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg></div>
                    <h3>Long-distance threats</h3>
                    <p>Spyware and digital surveillance, online harassment, and threats — or reprisals — against relatives back home.</p>
                </div>
                <div class="tnr-card">
                    <div class="tnr-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="1"/><path d="M4 9h16"/></svg></div>
                    <h3>Mobility controls</h3>
                    <p>Cancelled passports, abuse of Interpol Red Notices, and interference with visas and asylum claims to trap targets or force their return.</p>
                </div>
                <div class="tnr-card">
                    <div class="tnr-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15 15 0 0 1 0 20a15 15 0 0 1 0-20z"/></svg></div>
                    <h3>Coercion by proxy</h3>
                    <p>Pressuring host states into unlawful detentions and deportations — turning another country's police and courts into instruments of repression.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== BANNER: THE UNITED STATES ==================== --}}
    <section class="tnr-banner">
        <div class="tnr-banner-bg tnr-bg-blue"></div>
        <div class="tnr-banner-inner">
            <span class="tnr-overline">The United States</span>
            <h2 class="tnr-banner-title">Shelter &amp; Shadow</h2>
        </div>
    </section>
    <section class="tnr-section">
        <div class="tnr-wrap">
            <p class="tnr-p">The United States is one of the most common <strong>host countries</strong> for transnational repression: a place dissidents flee <em>to</em>, only to find that the governments they escaped still reach for them — with spyware, with intimidation of their families, and with attempts to bend U.S. institutions to their ends.</p>
            <p class="tnr-p">But exile cuts both ways. Among the prisoners the NPPC documents are Americans who were themselves forced abroad — who fled prosecution they considered political and built new lives in other countries, sometimes pursued for decades by extradition demands and bounties. The line between a refuge and a state that reaches across borders is not always where we expect it.</p>
        </div>
    </section>

    {{-- ==================== NPPC WATCH (dynamic) ==================== --}}
    <section class="tnr-section tnr-section--tint">
        <div class="tnr-wrap">
            <span class="tnr-kicker">NPPC Watch</span>
            <h2 class="tnr-h2">Political exiles we document</h2>
            <div class="tnr-live"><span class="tnr-live-dot"></span> Live from the database{{ $exileLabel }}</div>
            <p class="tnr-p">These are the people in the NPPC database currently living in exile — forced to leave the country to escape imprisonment or persecution. Each profile links to their full case.</p>

            @if($exiles->isNotEmpty())
                <div class="tnr-watch">
                    @foreach($exiles as $exile)
                        <a class="tnr-exile" href="/prisoner/{{ $exile->slug }}">
                            @if($exile->photo)
                                <img class="tnr-exile-photo" src="{{ asset('storage/' . $exile->photo) }}" alt="{{ $exile->name }}" loading="lazy" decoding="async" onerror="this.style.display='none'">
                            @else
                                <div class="tnr-exile-ph">{{ $initials($exile->name) }}</div>
                            @endif
                            <div>
                                <div class="tnr-exile-name">{{ $exile->name }}</div>
                                @if($exile->description)
                                    <p class="tnr-exile-desc">{{ $excerpt($exile->description) }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="tnr-watch-empty">No exile cases are flagged in the database right now. Explore the full database on the <a href="/map">map</a>.</div>
            @endif
        </div>
    </section>

    {{-- ==================== BANNER: ACCOUNTABILITY ==================== --}}
    <section class="tnr-banner">
        <div class="tnr-banner-bg tnr-bg-blue"></div>
        <div class="tnr-banner-inner">
            <span class="tnr-overline">A Call For</span>
            <h2 class="tnr-banner-title">Accountability &amp; Resilience</h2>
        </div>
    </section>
    <section class="tnr-section">
        <div class="tnr-wrap">
            <p class="tnr-p">Freedom House's research points to a shared agenda for blunting transnational repression. The responsibility is distributed.</p>
            <div class="tnr-rec"><div class="tnr-rec-who">Host governments</div><p>Identify and warn likely targets, refuse to carry out proxy detentions or deportations, train police to recognize the threat, and reform the review of Interpol Red Notices so they cannot be weaponized.</p></div>
            <div class="tnr-rec"><div class="tnr-rec-who">Civil society</div><p>Document incidents, support survivors and their families, and keep the cases visible so that distance never becomes silence.</p></div>
            <div class="tnr-rec"><div class="tnr-rec-who">Technology companies</div><p>Shut down the commercial spyware market, harden products against state surveillance, and notify users who are targeted.</p></div>
            <div class="tnr-rec"><div class="tnr-rec-who">International bodies</div><p>Sanction perpetrators, build accountability mechanisms, and treat cross-border repression as the human-rights violation it is.</p></div>
        </div>
    </section>

    {{-- ==================== SOURCES ==================== --}}
    <section class="tnr-section tnr-section--tint">
        <div class="tnr-wrap">
            <span class="tnr-kicker">Sources &amp; further reading</span>
            <ul class="tnr-sources">
                <li>
                    <a href="https://freedomhouse.org/report/transnational-repression" target="_blank" rel="noopener">Freedom House — Transnational Repression</a>
                    <span>The most comprehensive ongoing catalogue of cross-border repression, including the <a href="https://freedomhouse.org/report/transnational-repression#TNRWatch" target="_blank" rel="noopener">TNR Watch</a> incident tracker. The figures on this page are drawn from this project.</span>
                </li>
                <li>
                    <a href="/map">NPPC — Political Prisoner Database &amp; Map</a>
                    <span>Our own documentation of political prisoners and exiles, including the cases listed above.</span>
                </li>
            </ul>
            <p class="tnr-disclaimer">This briefing summarizes Freedom House's published research for an NPPC audience and connects it to the exile cases in our database. It is not produced or endorsed by Freedom House.</p>
        </div>
    </section>

    {{-- ==================== CTA ==================== --}}
    <section class="tnr-banner" style="min-height: 0;">
        <div class="tnr-banner-bg tnr-bg-navy"></div>
        <div class="tnr-cta" style="position: relative; z-index: 1; width: 100%;">
            <h2>Distance should never mean silence.</h2>
            <p>Explore the cases we document, and help keep the people targeted across borders visible.</p>
            <div class="tnr-btns">
                <a class="tnr-btn tnr-btn-primary" href="/map">Explore the database</a>
                <a class="tnr-btn tnr-btn-ghost" href="/get-involved">Get involved</a>
            </div>
        </div>
    </section>

</div>
@endsection
