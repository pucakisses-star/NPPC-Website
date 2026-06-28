@extends('app')

@section('title', 'Transnational Repression — How states silence dissent across borders | NPPC')

@section('head')
<meta name="description" content="A briefing on transnational repression — how governments reach across borders to silence exiles, journalists, and diaspora communities through assassination, abduction, unlawful deportation, and digital surveillance. Built on Freedom House's research, with a live watch of the political exiles the NPPC documents.">
<style>
    /* ============================================================
       Transnational Repression — hand-crafted briefing page.
       Long-form report layout in an amber-on-black "watch desk"
       aesthetic. All classes are scoped with the tnr- prefix so
       nothing leaks into the rest of the site. Built on the format
       used by human-rights publications (cf. Freedom House).
       ============================================================ */
    .tnr { background: #0a0a0b; color: #fff; }
    .tnr a { color: #e0a82e; }
    .tnr a:hover { color: #fff; }

    /* ---- Hero ---- */
    .tnr-hero { position: relative; overflow: hidden; background: #000; min-height: 600px; display: flex; align-items: flex-end; }
    .tnr-hero-overlay { position: absolute; inset: 0; z-index: 1;
        background:
            radial-gradient(120% 90% at 78% 8%, rgba(224,168,46,0.16), transparent 58%),
            radial-gradient(90% 80% at 10% 100%, rgba(224,168,46,0.08), transparent 55%),
            linear-gradient(180deg, #050506 0%, #0b0b0d 60%, #0a0a0b 100%); }
    /* faint border-grid / "map" texture behind the hero */
    .tnr-hero-grid { position: absolute; inset: 0; z-index: 0; opacity: 0.5;
        background-image: linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
        background-size: 64px 64px; mask-image: radial-gradient(120% 100% at 70% 0%, #000, transparent 70%); }
    .tnr-hero-content { position: relative; z-index: 2; max-width: 920px; margin: 0 auto; width: 100%; padding: 130px 24px 56px; }
    .tnr-kicker { display: inline-flex; align-items: center; gap: 12px; font-size: 12px; font-weight: 800; letter-spacing: 0.22em; text-transform: uppercase; color: #e0a82e; margin-bottom: 22px; }
    .tnr-kicker::before { content: ""; width: 34px; height: 2px; background: #e0a82e; display: inline-block; }
    .tnr-hero-title { font-size: 4.6rem; line-height: 0.98; font-weight: 800; color: #fff; margin: 0 0 18px; letter-spacing: -0.02em; }
    .tnr-hero-sub { font-size: 1.4rem; line-height: 1.45; color: rgba(255,255,255,0.78); max-width: 740px; margin: 0 0 28px; }
    .tnr-hero-meta { display: flex; flex-wrap: wrap; gap: 10px 18px; align-items: center; font-size: 13px; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: rgba(255,255,255,0.55); }
    .tnr-hero-meta span + span::before { content: ""; width: 4px; height: 4px; border-radius: 50%; background: #e0a82e; margin-right: 18px; display: inline-block; vertical-align: middle; }

    /* ---- Layout primitives ---- */
    .tnr-wrap { max-width: 820px; margin: 0 auto; padding: 0 24px; }
    .tnr-section { padding: 72px 0; border-top: 1px solid rgba(255,255,255,0.08); }
    .tnr-eyebrow { display: flex; align-items: center; gap: 12px; font-size: 12px; font-weight: 800; letter-spacing: 0.2em; text-transform: uppercase; color: #e0a82e; margin-bottom: 18px; }
    .tnr-eyebrow .tnr-num { font-family: Georgia, serif; color: rgba(255,255,255,0.4); }
    .tnr-h2 { font-size: 2.6rem; line-height: 1.1; font-weight: 800; color: #fff; margin: 0 0 24px; letter-spacing: -0.01em; }
    .tnr-h3 { font-size: 1.4rem; font-weight: 800; color: #fff; margin: 32px 0 12px; }
    .tnr-p { font-size: 17px; line-height: 1.85; color: rgba(255,255,255,0.76); margin: 0 0 1.4em; }
    .tnr-p strong { color: #fff; font-weight: 700; }

    /* ---- Lead ---- */
    .tnr-lead .tnr-p { font-family: Georgia, 'Times New Roman', serif; font-size: 1.45rem; line-height: 1.55; color: #fff; }
    .tnr-lead .tnr-p:first-child::first-letter { float: left; font-size: 4.2em; line-height: 0.72; padding: 0.05em 0.12em 0 0; color: #e0a82e; font-weight: 700; }

    /* ---- Stats band ---- */
    .tnr-stats-band { background: #050506; border-top: 1px solid rgba(255,255,255,0.08); border-bottom: 1px solid rgba(255,255,255,0.08); }
    .tnr-stats { max-width: 1100px; margin: 0 auto; padding: 0 24px; display: grid; grid-template-columns: repeat(4, 1fr); }
    .tnr-stat { padding: 44px 22px; border-left: 1px solid rgba(255,255,255,0.08); }
    .tnr-stat:first-child { border-left: 0; }
    .tnr-stat-num { font-family: Georgia, serif; font-size: 3.1rem; line-height: 1; font-weight: 700; color: #e0a82e; letter-spacing: -0.02em; }
    .tnr-stat-num small { font-size: 0.42em; color: rgba(255,255,255,0.45); }
    .tnr-stat-label { margin-top: 12px; font-size: 14px; line-height: 1.5; color: rgba(255,255,255,0.72); }
    .tnr-stats-src { max-width: 1100px; margin: 0 auto; padding: 14px 24px 22px; font-size: 12px; color: rgba(255,255,255,0.4); }

    /* ---- Tactic cards ---- */
    .tnr-cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-top: 36px; }
    .tnr-card { background: #111; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 26px; transition: border-color 0.25s, transform 0.25s; }
    .tnr-card:hover { border-color: rgba(224,168,46,0.45); transform: translateY(-3px); }
    .tnr-card-icon { color: #e0a82e; margin-bottom: 14px; }
    .tnr-card-icon svg { width: 30px; height: 30px; display: block; }
    .tnr-card h3 { font-size: 1.15rem; font-weight: 800; color: #fff; margin: 0 0 8px; }
    .tnr-card p { font-size: 14px; line-height: 1.65; color: rgba(255,255,255,0.62); margin: 0; }

    /* ---- Pull quote ---- */
    .tnr-pull { border-left: 3px solid #e0a82e; padding: 6px 0 6px 28px; margin: 10px 0; }
    .tnr-pull p { font-family: Georgia, 'Times New Roman', serif; font-size: 1.9rem; line-height: 1.32; color: #fff; margin: 0 0 14px; }
    .tnr-pull cite { font-style: normal; font-size: 14px; letter-spacing: 0.04em; color: rgba(255,255,255,0.5); text-transform: uppercase; }

    /* ---- Watch list (NPPC exiles) ---- */
    .tnr-watch-head { display: flex; align-items: center; gap: 12px; margin-bottom: 8px; }
    .tnr-live { display: inline-flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; color: #3fd07f; }
    .tnr-live-dot { width: 8px; height: 8px; border-radius: 50%; background: #3fd07f; box-shadow: 0 0 0 0 rgba(63,208,127,0.6); animation: tnrpulse 2s infinite; }
    @@keyframes tnrpulse { 0% { box-shadow: 0 0 0 0 rgba(63,208,127,0.55); } 70% { box-shadow: 0 0 0 8px rgba(63,208,127,0); } 100% { box-shadow: 0 0 0 0 rgba(63,208,127,0); } }
    .tnr-watch { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; margin-top: 28px; }
    .tnr-exile { display: grid; grid-template-columns: 60px 1fr; gap: 16px; align-items: start; background: #111; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 18px; text-decoration: none; transition: border-color 0.2s, transform 0.2s; }
    .tnr-exile:hover { border-color: rgba(224,168,46,0.45); transform: translateY(-2px); }
    .tnr-exile-photo { width: 60px; height: 60px; border-radius: 6px; object-fit: cover; object-position: center top; background: #1a1a1f; }
    .tnr-exile-ph { width: 60px; height: 60px; border-radius: 6px; background: #1a1a1f; border: 1px solid rgba(255,255,255,0.14); display: flex; align-items: center; justify-content: center; font-family: Georgia, serif; font-weight: 700; font-size: 1.2rem; color: #e0a82e; }
    .tnr-exile-name { font-size: 1.05rem; font-weight: 800; color: #fff; margin: 2px 0 6px; }
    .tnr-exile-desc { font-size: 13px; line-height: 1.55; color: rgba(255,255,255,0.6); margin: 0; }
    .tnr-watch-empty { background: #111; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 28px; color: rgba(255,255,255,0.6); font-size: 15px; }

    /* ---- Recommendations ---- */
    .tnr-rec { border-top: 1px solid rgba(255,255,255,0.08); padding: 24px 0; display: grid; grid-template-columns: 200px 1fr; gap: 24px; }
    .tnr-rec:last-child { border-bottom: 1px solid rgba(255,255,255,0.08); }
    .tnr-rec-who { font-size: 13px; font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase; color: #e0a82e; }
    .tnr-rec p { font-size: 15px; line-height: 1.7; color: rgba(255,255,255,0.72); margin: 0; }

    /* ---- Sources ---- */
    .tnr-sources { list-style: none; margin: 24px 0 0; padding: 0; }
    .tnr-sources li { border-top: 1px solid rgba(255,255,255,0.08); padding: 16px 0; }
    .tnr-sources li:last-child { border-bottom: 1px solid rgba(255,255,255,0.08); }
    .tnr-sources a { font-weight: 700; color: #fff; text-decoration: none; }
    .tnr-sources a:hover { color: #e0a82e; }
    .tnr-sources span { display: block; font-size: 13px; color: rgba(255,255,255,0.4); margin-top: 4px; }

    /* ---- CTA ---- */
    .tnr-cta { background: linear-gradient(135deg, #1c1606, #0a0a0b); border-top: 1px solid rgba(224,168,46,0.25); text-align: center; padding: 80px 24px; }
    .tnr-cta h2 { font-size: 2.4rem; font-weight: 800; color: #fff; margin: 0 0 16px; }
    .tnr-cta p { font-size: 17px; line-height: 1.7; color: rgba(255,255,255,0.82); max-width: 600px; margin: 0 auto 30px; }
    .tnr-btns { display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; }
    .tnr-btn { display: inline-flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 800; text-decoration: none; padding: 15px 30px; border-radius: 999px; transition: transform 0.2s, background 0.2s; }
    .tnr-btn-primary { background: #e0a82e; color: #15140a; }
    .tnr-btn-primary:hover { transform: translateY(-2px); color: #000; background: #f0bb47; }
    .tnr-btn-ghost { background: transparent; color: #fff; border: 1px solid rgba(255,255,255,0.55); }
    .tnr-btn-ghost:hover { background: rgba(255,255,255,0.12); transform: translateY(-2px); color: #fff; }

    /* ---- Responsive ---- */
    @@media (max-width: 900px) {
        .tnr-hero { min-height: 480px; }
        .tnr-hero-title { font-size: 3rem; }
        .tnr-hero-sub { font-size: 1.15rem; }
        .tnr-h2 { font-size: 2rem; }
        .tnr-stats { grid-template-columns: repeat(2, 1fr); }
        .tnr-stat:nth-child(3) { border-left: 0; }
        .tnr-cards, .tnr-watch { grid-template-columns: 1fr; }
        .tnr-rec { grid-template-columns: 1fr; gap: 8px; }
    }
</style>
@endsection

@section('body')
@php
    use App\Models\Prisoner;

    // NPPC's own "watch": the political exiles documented in the database —
    // people forced to flee abroad to escape prosecution or persecution.
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

    {{-- ==================== HERO ==================== --}}
    <section class="tnr-hero">
        <div class="tnr-hero-grid"></div>
        <div class="tnr-hero-overlay"></div>
        <div class="tnr-hero-content">
            <div class="tnr-kicker">NPPC Briefing</div>
            <h1 class="tnr-hero-title">Transnational Repression</h1>
            <p class="tnr-hero-sub">How governments reach across borders to silence dissent — hunting exiles, journalists, and diaspora communities far from home.</p>
            <div class="tnr-hero-meta">
                <span>Briefing</span>
                <span>Borders &amp; Exile</span>
                <span>Sources: Freedom House</span>
            </div>
        </div>
    </section>

    {{-- ==================== LEAD ==================== --}}
    <div class="tnr-wrap tnr-lead" style="padding-top: 64px;">
        <p class="tnr-p">Transnational repression is what happens when a state reaches beyond its own borders to silence the people it cannot control at home. Its targets are exiles, refugees, journalists, and diaspora communities — and its tools range from assassination and abduction to unlawful deportation, digital surveillance, and threats against the family members left behind.</p>
        <p class="tnr-p">It is one of the defining human-rights threats of the era, and — as Freedom House has documented — it does not stop at the borders of any democracy, including the United States.</p>
    </div>

    {{-- ==================== STATS (Freedom House) ==================== --}}
    <div class="tnr-stats-band" style="margin-top: 56px;">
        <div class="tnr-stats">
            <div class="tnr-stat">
                <div class="tnr-stat-num">3.5<small>M+</small></div>
                <div class="tnr-stat-label">People estimated to be at risk worldwide</div>
            </div>
            <div class="tnr-stat">
                <div class="tnr-stat-num">1,375<small>+</small></div>
                <div class="tnr-stat-label">Direct, physical incidents catalogued since 2014</div>
            </div>
            <div class="tnr-stat">
                <div class="tnr-stat-num">54</div>
                <div class="tnr-stat-label">Origin governments reaching across borders</div>
            </div>
            <div class="tnr-stat">
                <div class="tnr-stat-num">107</div>
                <div class="tnr-stat-label">Host countries where incidents have occurred</div>
            </div>
        </div>
        <div class="tnr-stats-src">Figures: Freedom House, <em>Transnational Repression</em> project (incidents catalogued 2014–2025).</div>
    </div>

    {{-- ==================== 01 — WHAT IS IT ==================== --}}
    <section class="tnr-section">
        <div class="tnr-wrap">
            <div class="tnr-eyebrow"><span class="tnr-num">01</span> What it is</div>
            <h2 class="tnr-h2">A spectrum of cross-border coercion</h2>
            <p class="tnr-p">Transnational repression describes the campaign a government wages against its own nationals and former nationals <strong>after they have left the country</strong>. Rather than a single act, it is a spectrum — from the spectacular and violent to the quiet and bureaucratic.</p>
            <p class="tnr-p">At one end are assassinations, abductions, and renditions. At the other are passport cancellations, abusive Interpol notices, spyware on a phone, and the threat that a dissident's elderly parents will lose their jobs — or their freedom — if the dissident keeps speaking out. The aim is always the same: to make distance no protection at all.</p>
            <div class="tnr-pull">
                <p>&ldquo;The message is that no one is ever truly out of reach.&rdquo;</p>
                <cite>— the logic of transnational repression</cite>
            </div>
        </div>
    </section>

    {{-- ==================== 02 — WHO IS AT RISK ==================== --}}
    <section class="tnr-section">
        <div class="tnr-wrap">
            <div class="tnr-eyebrow"><span class="tnr-num">02</span> Who is at risk</div>
            <h2 class="tnr-h2">The people in the crosshairs</h2>
            <p class="tnr-p">The targets are rarely random. They are <strong>journalists and human-rights defenders</strong> whose reporting embarrasses a regime; <strong>political opposition figures</strong> who organize from abroad; <strong>ethnic and religious minorities</strong> — Uyghurs, Tibetans, and others — treated as suspect wherever they live; and <strong>former insiders</strong> who know too much.</p>
            <p class="tnr-p">The damage radiates outward. When one exile is attacked, an entire diaspora learns to self-censor — to stop attending protests, stop calling home, stop speaking to reporters. That chilling effect, multiplied across millions of people, is the point.</p>
        </div>
    </section>

    {{-- ==================== 03 — TACTICS ==================== --}}
    <section class="tnr-section">
        <div class="tnr-wrap">
            <div class="tnr-eyebrow"><span class="tnr-num">03</span> Tactics</div>
            <h2 class="tnr-h2">Four ways states reach across borders</h2>
            <p class="tnr-p">Freedom House groups the tactics of transnational repression into four broad categories.</p>
            <div class="tnr-cards">
                <div class="tnr-card">
                    <div class="tnr-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="9" y1="12" x2="15" y2="12"/></svg></div>
                    <h3>Direct attacks</h3>
                    <p>Assassinations, physical assaults, abductions, and renditions — violence carried out on foreign soil.</p>
                </div>
                <div class="tnr-card">
                    <div class="tnr-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12" y2="18"/></svg></div>
                    <h3>Long-distance threats</h3>
                    <p>Spyware and digital surveillance, online harassment campaigns, and threats — or reprisals — against relatives back home.</p>
                </div>
                <div class="tnr-card">
                    <div class="tnr-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4z"/><path d="M4 9h16"/><circle cx="8" cy="6.5" r="0.5" fill="currentColor"/></svg></div>
                    <h3>Mobility controls</h3>
                    <p>Cancelled passports, abuse of Interpol Red Notices, and interference with visas and asylum claims to trap targets or force their return.</p>
                </div>
                <div class="tnr-card">
                    <div class="tnr-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20a15 15 0 0 1 0-20z"/></svg></div>
                    <h3>Coercion by proxy</h3>
                    <p>Pressuring host states into unlawful detentions and deportations — turning another country's police and courts into instruments of repression.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== 04 — THE U.S. ==================== --}}
    <section class="tnr-section">
        <div class="tnr-wrap">
            <div class="tnr-eyebrow"><span class="tnr-num">04</span> The United States</div>
            <h2 class="tnr-h2">Shelter — and shadow</h2>
            <p class="tnr-p">The United States is one of the most common <strong>host countries</strong> for transnational repression: a place dissidents flee <em>to</em>, only to find that the governments they escaped still reach for them — with spyware, with intimidation of their families, and with attempts to bend U.S. institutions to their ends.</p>
            <p class="tnr-p">But exile cuts both ways. Among the prisoners the NPPC documents are Americans who were themselves forced abroad — who fled prosecution they considered political and built new lives in other countries, sometimes pursued for decades by extradition demands and bounties. Their stories are a reminder that the line between a refuge and a state that reaches across borders is not always where we expect it.</p>
        </div>
    </section>

    {{-- ==================== 05 — NPPC WATCH (dynamic) ==================== --}}
    <section class="tnr-section">
        <div class="tnr-wrap">
            <div class="tnr-eyebrow"><span class="tnr-num">05</span> NPPC Watch</div>
            <div class="tnr-watch-head">
                <h2 class="tnr-h2" style="margin: 0;">Political exiles we document</h2>
            </div>
            <div class="tnr-live" style="margin-bottom: 18px;"><span class="tnr-live-dot"></span> Live from the database{{ $exileLabel }}</div>
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

    {{-- ==================== 06 — RECOMMENDATIONS ==================== --}}
    <section class="tnr-section">
        <div class="tnr-wrap">
            <div class="tnr-eyebrow"><span class="tnr-num">06</span> What can be done</div>
            <h2 class="tnr-h2">Closing the reach</h2>
            <p class="tnr-p">Freedom House's research points to a shared agenda for blunting transnational repression. The responsibility is distributed.</p>
            <div class="tnr-rec">
                <div class="tnr-rec-who">Host governments</div>
                <p>Identify and warn likely targets, refuse to carry out proxy detentions or deportations, train police to recognize the threat, and reform the review of Interpol Red Notices so they cannot be weaponized.</p>
            </div>
            <div class="tnr-rec">
                <div class="tnr-rec-who">Civil society</div>
                <p>Document incidents, support survivors and their families, and keep the cases visible so that distance never becomes silence.</p>
            </div>
            <div class="tnr-rec">
                <div class="tnr-rec-who">Technology companies</div>
                <p>Shut down the commercial spyware market, harden products against state surveillance, and notify users who are targeted.</p>
            </div>
            <div class="tnr-rec">
                <div class="tnr-rec-who">International bodies</div>
                <p>Sanction perpetrators, build accountability mechanisms, and treat cross-border repression as the human-rights violation it is.</p>
            </div>
        </div>
    </section>

    {{-- ==================== SOURCES ==================== --}}
    <section class="tnr-section">
        <div class="tnr-wrap">
            <div class="tnr-eyebrow">Sources &amp; further reading</div>
            <ul class="tnr-sources">
                <li>
                    <a href="https://freedomhouse.org/report/transnational-repression" target="_blank" rel="noopener">Freedom House — Transnational Repression</a>
                    <span>The most comprehensive ongoing catalogue of cross-border repression, including the <a href="https://freedomhouse.org/report/transnational-repression#TNRWatch" target="_blank" rel="noopener">TNR Watch</a> incident tracker. Figures on this page are drawn from this project.</span>
                </li>
                <li>
                    <a href="/map">NPPC — Political Prisoner Database &amp; Map</a>
                    <span>Our own documentation of political prisoners and exiles, including the cases listed above.</span>
                </li>
            </ul>
            <p class="tnr-p" style="margin-top: 28px; font-size: 14px; color: rgba(255,255,255,0.45);">This briefing summarizes Freedom House's published research for an NPPC audience and connects it to the exile cases in our database. It is not produced or endorsed by Freedom House.</p>
        </div>
    </section>

    {{-- ==================== CTA ==================== --}}
    <section class="tnr-cta">
        <h2>Distance should never mean silence.</h2>
        <p>Explore the cases we document, and help keep the people targeted across borders visible.</p>
        <div class="tnr-btns">
            <a class="tnr-btn tnr-btn-primary" href="/map">Explore the database</a>
            <a class="tnr-btn tnr-btn-ghost" href="/get-involved">Get involved</a>
        </div>
    </section>

</div>
@endsection
