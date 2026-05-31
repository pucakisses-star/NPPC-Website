@extends('app')

@section('title', "Campus Abductions & Visa Revocations — Student Visa Revocations & ICE Arrests | NPPC")

@section('head')
<meta name="description" content="A living resource on the 2025 U.S. crackdown on international students and scholars — the ICE arrests of student activists, the mass visa revocations and SEVIS terminations, the courts' response, and profiles of the people detained — compiled from reporting by the Associated Press, CNN, NPR, and Inside Higher Ed, the ACLU and the Knight First Amendment Institute, and court filings.">
<style>
    /* ============================================================
       Campus Abductions & Visa Revocations — dark resource page in
       the NPPC briefing series. Black background, white text, the
       site indigo (#5660fe) accent. Layout mirrors the We Are
       Higher Ed "Campus Abductions" page: action alert, latest-
       update box, report banner, locations map, trackers + news
       lists, and individual case profiles. Scoped with svr-.
       ============================================================ */

    /* Full-bleed: drop the centered .container's max-width/padding so
       the black background spans the viewport, and darken the body so
       there are no white gutters around the page. */
    body.page-student-visa-revocations-and-ice-arrests main.container,
    body.page-student-visa-revocations-and-ice-arrests .container { max-width: none !important; padding-left: 0 !important; padding-right: 0 !important; overflow: visible !important; }
    body.page-student-visa-revocations-and-ice-arrests { background: #000; }

    .svr { background: #000; color: rgba(255,255,255,.82); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
    .svr *, .svr *::before, .svr *::after { box-sizing: border-box; }
    .svr a { color: #8b92ff; }
    .svr a:hover { color: #fff; }

    /* ---- layout primitives ---- */
    .svr-wrap { max-width: 880px; margin: 0 auto; padding: 0 24px; }
    .svr-wide { max-width: 1120px; margin: 0 auto; padding: 0 24px; }
    .svr-section { padding: 52px 0; }
    .svr-section--tight { padding: 30px 0; }
    .svr-divider { border: 0; border-top: 1px solid rgba(255,255,255,.10); margin: 0; }

    /* ---- top-region background image (hero + latest-update + report banner) ---- */
    /* Break out of the centered .container to the full viewport width so the
       background photo spans edge to edge; the inner .svr-wrap blocks keep the
       content itself centered and capped. */
    .svr-topbg { position: relative; width: 100vw; margin-left: calc(50% - 50vw); margin-right: calc(50% - 50vw); overflow: hidden; }
    .svr-topbg::before { content: ""; position: absolute; inset: 0; z-index: 0;
        background-image:
            linear-gradient(180deg, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0.78) 55%, #000 100%),
            url('{{ asset('images/original.avif') }}');
        background-size: cover; background-position: center; background-repeat: no-repeat; }
    .svr-topbg > * { position: relative; z-index: 1; }

    /* ---- hero ---- */
    .svr-hero { padding: 64px 0 38px; }
    .svr-alert { display: inline-flex; align-items: center; gap: 9px; background: rgba(86,96,254,.16); color: #aab0ff; font-weight: 800; font-size: 12px; letter-spacing: .14em; text-transform: uppercase; padding: 8px 15px; border-radius: 999px; border: 1px solid rgba(86,96,254,.45); margin-bottom: 22px; }
    .svr-alert::before { content: ""; width: 8px; height: 8px; border-radius: 50%; background: #5660fe; animation: svrpulse 2s infinite; }
    @@keyframes svrpulse { 0% { box-shadow: 0 0 0 0 rgba(86,96,254,.5); } 70% { box-shadow: 0 0 0 8px rgba(86,96,254,0); } 100% { box-shadow: 0 0 0 0 rgba(86,96,254,0); } }
    .svr-h1 { font-size: 3.6rem; line-height: 1.02; font-weight: 800; letter-spacing: -.025em; margin: 0 0 20px; color: #fff; }
    .svr-hero-sub { font-size: 1.25rem; line-height: 1.6; color: rgba(255,255,255,.72); max-width: 720px; margin: 0; }
    .svr-hero-meta { margin-top: 24px; font-size: 13px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; color: rgba(255,255,255,.5); }
    .svr-hero-meta span + span::before { content: "•"; margin: 0 10px; color: #5660fe; }

    /* ---- latest-update box ---- */
    .svr-update { display: flex; gap: 18px; align-items: flex-start; background: #141418; border: 1px solid rgba(255,255,255,.10); border-left: 4px solid #5660fe; border-radius: 8px; padding: 22px 24px; }
    .svr-update-ico { flex: 0 0 auto; color: #8b92ff; margin-top: 2px; }
    .svr-update-tag { font-size: 11px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: #aab0ff; }
    .svr-update h3 { font-size: 1.15rem; font-weight: 800; color: #fff; margin: 5px 0 6px; }
    .svr-update p { margin: 0; color: rgba(255,255,255,.7); font-size: 15px; line-height: 1.6; }

    /* ---- eyebrow + headings ---- */
    .svr-eyebrow { display: inline-flex; align-items: center; gap: 10px; font-size: 12px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; color: #aab0ff; margin-bottom: 14px; }
    .svr-eyebrow::before { content: ""; width: 26px; height: 2px; background: #5660fe; }
    .svr-h2 { font-size: 2.1rem; line-height: 1.12; font-weight: 800; color: #fff; margin: 0 0 18px; letter-spacing: -.015em; }
    .svr-h3 { font-size: 1.3rem; font-weight: 800; color: #fff; margin: 30px 0 12px; }
    .svr-p { font-size: 17px; line-height: 1.75; color: rgba(255,255,255,.76); margin: 0 0 1.2em; }
    .svr-p:last-child { margin-bottom: 0; }
    .svr-p strong { color: #fff; font-weight: 700; }
    .svr-cite { font-size: 13px; color: rgba(255,255,255,.45); }

    /* ---- stats ---- */
    .svr-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1px; background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.10); border-radius: 10px; overflow: hidden; margin-top: 34px; }
    .svr-stat { background: #0e0e12; padding: 26px 22px; }
    .svr-stat-num { font-size: 2.5rem; font-weight: 800; color: #8b92ff; line-height: 1; letter-spacing: -.02em; }
    .svr-stat-num small { font-size: .45em; }
    .svr-stat-label { margin-top: 10px; font-size: 14px; line-height: 1.5; color: rgba(255,255,255,.72); }
    .svr-stat-src { margin-top: 8px; font-size: 12px; color: rgba(255,255,255,.4); }

    /* ---- pull quote ---- */
    .svr-pull { border-left: 3px solid #5660fe; padding: 4px 0 4px 26px; margin: 0; }
    .svr-pull p { font-size: 1.9rem; line-height: 1.3; font-weight: 700; color: #fff; margin: 0 0 12px; }
    .svr-pull cite { font-style: normal; font-size: 14px; letter-spacing: .03em; color: rgba(255,255,255,.5); text-transform: uppercase; }

    /* ---- report banner ---- */
    .svr-banner { background: #16161c; color: #fff; border: 1px solid rgba(255,255,255,.10); border-radius: 12px; padding: 32px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 20px; }
    .svr-banner h3 { font-size: 1.4rem; font-weight: 800; margin: 0 0 6px; color: #fff; }
    .svr-banner p { margin: 0; color: rgba(255,255,255,.7); font-size: 15px; line-height: 1.55; max-width: 560px; }

    /* ---- buttons ---- */
    .svr-btn { display: inline-flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 700; text-decoration: none; padding: 13px 26px; border-radius: 999px; transition: transform .15s, background .15s, color .15s, border-color .15s; white-space: nowrap; }
    .svr-btn svg { width: 17px; height: 17px; }
    .svr-btn-red { background: #5660fe; color: #fff; }
    .svr-btn-red:hover { background: #4850e6; color: #fff; transform: translateY(-1px); }
    .svr-btn-light { background: #fff; color: #111; }
    .svr-btn-light:hover { background: #e9e9ef; color: #000; transform: translateY(-1px); }
    .svr-btn-ghost { background: transparent; color: #fff; border: 1px solid rgba(255,255,255,.32); }
    .svr-btn-ghost:hover { border-color: #fff; background: rgba(255,255,255,.08); color: #fff; transform: translateY(-1px); }

    /* ---- locations map ---- */
    .svr-map { position: relative; border: 1px solid rgba(255,255,255,.10); border-radius: 12px; overflow: hidden;
        background: #000; }
    .svr-map-inner { padding: 30px 28px; }
    .svr-map h3 { font-size: 1.5rem; font-weight: 800; color: #fff; margin: 0 0 6px; }
    .svr-map-sub { font-size: 14px; color: rgba(255,255,255,.55); margin: 0 0 22px; }
    .svr-map-foot { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; border-top: 1px dashed rgba(255,255,255,.18); padding-top: 20px; }
    .svr-map-note { font-size: 13px; color: rgba(255,255,255,.55); max-width: 540px; margin: 0; }
    .svr-map-controls { display: flex; align-items: center; gap: 10px; margin: 4px 0 14px; flex-wrap: wrap; }
    .svr-map-label { font-size: 12px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: rgba(255,255,255,.6); }
    .svr-map-select { background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.18); color: #fff; padding: 9px 12px; font-size: 14px; border-radius: 8px; outline: none; min-width: 200px; }
    .svr-map-select:focus { border-color: #5660fe; }
    .svr-map-select option { color: #111; }
    .svr-map-canvas { position: relative; width: 100%; height: clamp(420px, 62vh, 640px); margin: 0 0 22px; background: #000; border: 1px solid rgba(255,255,255,.12); border-radius: 10px; overflow: hidden; }
    .svr-pop-name { font-weight: 800; font-size: 14px; color: #fff; margin: 0 0 2px; }
    .svr-pop-meta { font-size: 12px; color: rgba(255,255,255,.6); }
    .svr-pop-meta b { color: #aab0ff; }
    /* ---- native institutions table ---- */
    .svr-tbl-total { font-size: 1.35rem; font-weight: 800; color: #fff; margin: 6px 0 10px; }
    .svr-tbl-total span { font-weight: 600; font-size: 1rem; color: rgba(255,255,255,.55); }
    .svr-tbl-context { font-size: 13.5px; line-height: 1.65; color: rgba(255,255,255,.62); max-width: 760px; margin: 0 0 18px; }
    .svr-tbl-context strong { color: rgba(255,255,255,.9); font-weight: 700; }
    .svr-tbl-foot { font-size: 12.5px; line-height: 1.6; color: rgba(255,255,255,.5); max-width: 760px; margin: 14px 0 0; padding-top: 12px; border-top: 1px solid rgba(255,255,255,.08); }
    .svr-credit { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; margin: 16px 0 0; }
    .svr-credit-logo { display: inline-flex; line-height: 0; }
    .svr-credit-logo img { height: 30px; width: auto; display: block; }
    .svr-credit-cap { font-size: 13px; color: rgba(255,255,255,.6); margin: 0; }
    .svr-tbl-search { width: 100%; max-width: 420px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.18); color: #fff; padding: 10px 14px; font-size: 14px; border-radius: 8px; outline: none; margin-bottom: 16px; }
    .svr-tbl-search::placeholder { color: rgba(255,255,255,.4); }
    .svr-tbl-search:focus { border-color: #5660fe; }
    .svr-tbl-wrap { max-height: clamp(420px, 56vh, 560px); overflow-y: auto; border: 1px solid rgba(255,255,255,.12); border-radius: 10px; }
    .svr-tbl { width: 100%; border-collapse: collapse; font-size: 14.5px; }
    .svr-tbl thead th { position: sticky; top: 0; background: #16161c; color: #fff; text-align: left; font-weight: 800; font-size: 12px; letter-spacing: .04em; text-transform: uppercase; padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,.14); z-index: 1; }
    .svr-tbl tbody td { padding: 11px 16px; border-bottom: 1px solid rgba(255,255,255,.07); color: rgba(255,255,255,.82); vertical-align: top; }
    .svr-tbl tbody tr:last-child td { border-bottom: 0; }
    .svr-tbl tbody tr:hover td { background: rgba(255,255,255,.03); }
    .svr-tbl a { color: #8b92ff; text-decoration: none; }
    .svr-tbl a:hover { color: #fff; text-decoration: underline; }
    .svr-tbl-num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .svr-tbl-unknown { color: rgba(255,255,255,.4); font-style: italic; }
    .svr-tbl-empty { padding: 22px 16px; margin: 0; color: rgba(255,255,255,.5); font-style: italic; }
    .svr-tbl-yes { color: #5cd98a; font-weight: 700; }
    .svr-tbl-part { color: #f0b860; font-weight: 700; }

    /* ---- trackers + news lists ---- */
    .svr-list { list-style: none; margin: 0; padding: 0; }
    .svr-list li { border-top: 1px solid rgba(255,255,255,.10); padding: 16px 0; }
    .svr-list li:last-child { border-bottom: 1px solid rgba(255,255,255,.10); }
    .svr-list a { font-weight: 700; text-decoration: none; }
    .svr-list a:hover { text-decoration: underline; }
    .svr-list .svr-src { display: block; font-size: 13px; color: rgba(255,255,255,.5); margin-top: 3px; font-weight: 400; }

    /* ---- cases ---- */
    .svr-cases { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .svr-case { border: 1px solid rgba(255,255,255,.10); border-radius: 12px; padding: 22px; display: flex; gap: 16px; align-items: flex-start; background: #121216; transition: border-color .2s, box-shadow .2s; }
    .svr-case:hover { border-color: rgba(255,255,255,.22); box-shadow: 0 8px 24px rgba(0,0,0,.4); }
    .svr-avatar { width: 58px; height: 58px; border-radius: 50%; flex: 0 0 auto; background: rgba(86,96,254,.16); color: #aab0ff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.05rem; border: 1px solid rgba(86,96,254,.4); letter-spacing: .02em; object-fit: cover; overflow: hidden; }
    .svr-case-body { min-width: 0; }
    .svr-case h3 { font-size: 1.15rem; font-weight: 800; color: #fff; margin: 0 0 2px; }
    .svr-case-role { font-size: 11.5px; letter-spacing: .03em; text-transform: uppercase; color: rgba(255,255,255,.5); margin: 0 0 10px; font-weight: 700; }
    .svr-case p { font-size: 14.5px; line-height: 1.6; color: rgba(255,255,255,.72); margin: 0 0 12px; }
    .svr-tag { display: inline-block; font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; padding: 4px 10px; border-radius: 999px; margin-bottom: 12px; }
    .svr-tag-detained { background: rgba(255,255,255,.08); color: rgba(255,255,255,.7); border: 1px solid rgba(255,255,255,.2); }
    .svr-tag-released { background: rgba(34,197,94,.15); color: #5cd98a; border: 1px solid rgba(34,197,94,.4); }
    .svr-tag-removed { background: rgba(245,158,11,.15); color: #f0b860; border: 1px solid rgba(245,158,11,.4); }
    .svr-tag-court { background: rgba(86,96,254,.18); color: #aab0ff; border: 1px solid rgba(86,96,254,.45); }
    .svr-coverage { font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }

    /* ---- methodology / sources ---- */
    .svr-note { background: #121216; border: 1px solid rgba(255,255,255,.10); border-radius: 10px; padding: 24px; margin-bottom: 28px; }
    .svr-note p { font-size: 15px; line-height: 1.7; color: rgba(255,255,255,.72); margin: 0 0 12px; }
    .svr-note p:last-child { margin: 0; }
    .svr-note strong { color: #fff; }

    /* ---- footer CTA ---- */
    .svr-foot { background: #5660fe; color: #fff; text-align: center; padding: 64px 24px; }
    .svr-foot h2 { font-size: 2.2rem; font-weight: 800; margin: 0 0 14px; color: #fff; letter-spacing: -.01em; }
    .svr-foot p { font-size: 17px; line-height: 1.6; color: rgba(255,255,255,.92); max-width: 620px; margin: 0 auto 26px; }
    .svr-foot .svr-btns { display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; }
    .svr-foot .svr-btn-ghost { background: transparent; color: #fff; border-color: rgba(255,255,255,.6); }
    .svr-foot .svr-btn-ghost:hover { background: rgba(255,255,255,.12); color: #fff; border-color: #fff; }

    /* ---- responsive ---- */
    @@media (max-width: 820px) {
        .svr-h1 { font-size: 2.5rem; }
        .svr-h2 { font-size: 1.7rem; }
        .svr-pull p { font-size: 1.5rem; }
        .svr-stats { grid-template-columns: 1fr 1fr; }
        .svr-cases { grid-template-columns: 1fr; }
        .svr-banner { flex-direction: column; align-items: flex-start; }
    }
    @@media (max-width: 520px) {
        .svr-h1 { font-size: 2.05rem; }
        .svr-hero-sub { font-size: 1.1rem; }
        .svr-stats { grid-template-columns: 1fr; }
        .svr-foot h2 { font-size: 1.8rem; }
    }
</style>
@endsection

@section('body')
<div class="svr">

    {{-- Top region with the full-width background image behind it --}}
    <div class="svr-topbg">

    {{-- ==================== HERO ==================== --}}
    <div class="svr-wrap svr-hero">
        <span class="svr-alert">Immediate action required</span>
        <h1 class="svr-h1">Campus Abductions &amp; Visa Revocations</h1>
        <p class="svr-hero-sub">In 2025 the federal government began arresting international students and scholars over their speech and stripping the legal status of thousands more. This page tracks what is happening, names the people detained, and points to where you can report a case and follow the litigation.</p>
        <div class="svr-hero-meta"><span>Living resource</span><span>Updated 2025</span><span>NPPC</span></div>
    </div>

    {{-- ==================== LATEST UPDATE ==================== --}}
    <div class="svr-wrap svr-section--tight">
        <div class="svr-update">
            <div class="svr-update-ico"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg></div>
            <div>
                <div class="svr-update-tag">Latest development</div>
                <h3>April 25 — ICE reverses course on SEVIS terminations</h3>
                <p>After more than 100 lawsuits and a cascade of court orders, the government told judges it would restore terminated student records nationwide while it wrote a formal policy — then circulated a draft framework that advocates warned could trigger a second round. <a href="https://www.insidehighered.com/news/global/international-students-us/2025/04/25/ice-reverses-course-sevis-terminations" target="_blank" rel="noopener">Read more →</a></p>
            </div>
        </div>
    </div>

    {{-- ==================== REPORT BANNER ==================== --}}
    <div class="svr-wrap svr-section--tight">
        <div class="svr-banner">
            <div>
                <h3>Know a student who's been detained or had a visa revoked?</h3>
                <p>If you or someone at your campus has been arrested by ICE, had a visa revoked, or found a SEVIS record terminated, tell us. We add documented cases to this page and connect people with legal and advocacy support.</p>
            </div>
            <a class="svr-btn svr-btn-red" href="/contact">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v12H5.2L4 17.2z"/></svg>
                Report a case
            </a>
        </div>
    </div>

    </div>{{-- /svr-topbg --}}

    {{-- ==================== LOCATIONS MAP ==================== --}}
    <div class="svr-wide svr-section--tight">
        <div class="svr-map">
            <div class="svr-map-inner">
                @php
                    $instPath = base_path('resources/data/affected-institutions.json');
                    $instData = is_file($instPath) ? json_decode(file_get_contents($instPath), true) : null;
                    $institutions = $instData['institutions'] ?? [];
                    $totalAffected = $instData['total_affected'] ?? null;
                    $instCount = $instData['count'] ?? count($institutions);
                    $syncedLabel = ! empty($instData['synced_at'])
                        ? \Illuminate\Support\Carbon::parse($instData['synced_at'])->format('M j, Y')
                        : null;
                    $mirrorNote = 'the map and table are mirrored and rendered by NPPC'
                        .($syncedLabel ? ', last synced '.$syncedLabel : '').'.';

                    // Geocoded points for the native map: only institutions with
                    // numeric coordinates. Built server-side and drawn by D3.
                    $mapPoints = [];
                    foreach ($institutions as $i) {
                        if (is_numeric($i['latitude'] ?? null) && is_numeric($i['longitude'] ?? null)) {
                            $mapPoints[] = [
                                'name' => $i['name'],
                                'state' => $i['state'],
                                'affected' => $i['affected_people'],
                                'lat' => (float) $i['latitude'],
                                'lng' => (float) $i['longitude'],
                            ];
                        }
                    }
                    $mapStates = collect($mapPoints)->pluck('state')->filter()->unique()->sort()->values();
                @endphp

                <h3>Map of visa revocation locations</h3>
                @if($mapPoints)
                    <p class="svr-map-sub">Each dot is a campus where a student visa was revoked or a SEVIS record terminated — {{ number_format(count($mapPoints)) }} geocoded institutions across {{ $mapStates->count() }} states. Filter by state, or click a dot for detail.</p>
                    <div class="svr-map-controls">
                        <label for="svr-state-select" class="svr-map-label">Select state:</label>
                        <select id="svr-state-select" class="svr-map-select" onchange="svrFilterMap(this.value)">
                            <option value="">United States (all)</option>
                            @foreach($mapStates as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="svr-map-canvas" class="svr-map-canvas" data-points="{{ count($mapPoints) }}"></div>
                    <p class="svr-map-note" id="svr-map-fallback" hidden>The interactive map needs JavaScript. See the full list of institutions below.</p>
                @else
                    <p class="svr-map-note">Map data is being synced. Run <code>php artisan visa:sync-institutions</code> to populate it.</p>
                @endif

                <h3 style="margin-top: 28px;">Affected institutions</h3>
                <p class="svr-map-sub">The running total of known affected people, and the full list of colleges and universities. Search by name or state.</p>
                @if($institutions)
                    <div class="svr-tbl-total">TOTAL: {{ number_format((int) $totalAffected) }} known affected people <span>· {{ number_format((int) $instCount) }} institutions</span></div>
                    <p class="svr-tbl-context">This total counts only students who could be tied to a <strong>specific, named campus</strong> — through a university statement, a registrar, or a lawsuit. The government's own figures are far larger: roughly <strong>3,000 student visas revoked</strong> and <strong>4,700 SEVIS records terminated</strong> in the spring 2025 wave, and the State Department later said it had revoked about <strong>8,000 student visas</strong> over all of 2025. But those were released as national totals with <strong>no list of schools</strong>, so the students behind them can't be placed on a map. The gap between this figure and the headlines is mostly students whose institution was never publicly disclosed — not a gap in this list.</p>
                    <input type="text" class="svr-tbl-search" id="svr-inst-search" placeholder="Search institutions or states…" onkeyup="svrFilterInstitutions(this.value)" aria-label="Search affected institutions">
                    <div class="svr-tbl-wrap">
                        <table class="svr-tbl" id="svr-inst-table">
                            <thead>
                                <tr><th>Institution</th><th>State</th><th class="svr-tbl-num">Affected people</th></tr>
                            </thead>
                            <tbody>
                                @foreach($institutions as $inst)
                                    @php($instLink = ! empty($inst['website']) ? $inst['website'] : ($inst['wikipedia'] ?? ''))
                                    <tr>
                                        <td>
                                            @if(! empty($instLink))
                                                <a href="{{ $instLink }}" target="_blank" rel="noopener">{{ $inst['name'] }}</a>
                                            @else
                                                {{ $inst['name'] }}
                                            @endif
                                        </td>
                                        <td>{{ $inst['state'] }}</td>
                                        <td class="svr-tbl-num">
                                            @if(is_numeric($inst['affected_people']))
                                                {{ number_format((int) $inst['affected_people']) }}
                                            @else
                                                <span class="svr-tbl-unknown">unknown</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p class="svr-tbl-empty" id="svr-inst-empty" hidden>No institutions match your search.</p>
                    </div>
                    <p class="svr-tbl-foot"><strong>Harvard University</strong> is a special case kept off this list. In May 2025 the Department of Homeland Security moved to revoke Harvard's certification to enroll any international students at all — a threat to its entire body of roughly 6,800, rather than a count of individual revocations. A federal court blocked the move, and DHS later stipulated it would not act on the May 22 letter. Counting it here would conflate a single contested institutional action with the per-student totals above.</p>
                @else
                    <p class="svr-map-note">Institution data is being synced. Run <code>php artisan visa:sync-institutions</code> to populate it.</p>
                @endif
                <div class="svr-map-foot">
                    <p class="svr-map-note">Map and institution data compiled by the Nimble Tent Data Viewer; {{ $mirrorNote }} Reporting a case helps document where students are being detained and visas pulled.</p>
                    <a class="svr-btn svr-btn-ghost" href="/contact">Report a visa revocation</a>
                </div>
            </div>
        </div>
    </div>

    <hr class="svr-divider">

    {{-- ==================== CONTEXT + STATS ==================== --}}
    <div class="svr-wrap svr-section">
        <div class="svr-eyebrow">What's happening</div>
        <h2 class="svr-h2">A campaign on two fronts</h2>
        <p class="svr-p">Within days of the January 2025 inauguration, the administration signaled it would use immigration law against campus protest. By early March, officials described a State Department effort — reported as <strong>"Catch and Revoke"</strong> — that would use AI to scan visa holders' social-media accounts for apparent support of Hamas. <span class="svr-cite">(Axios; Inside Higher Ed)</span></p>
        <p class="svr-p">What followed ran on two tracks. The first was loud and individual: high-profile arrests of student organizers, justified by a rarely used clause of immigration law that lets the Secretary of State declare a noncitizen's very presence a foreign-policy problem. The second was quiet and vast: the mass termination of student records in <strong>SEVIS</strong>, the federal database that tracks international students — most of it driven not by protest, but by hits against a criminal-records database for minor or long-closed matters.</p>

        <div class="svr-stats">
            <div class="svr-stat">
                <div class="svr-stat-num">300<small>+</small></div>
                <div class="svr-stat-label">student visas the Secretary of State said had already been revoked by late March.</div>
                <div class="svr-stat-src">Marco Rubio, March 2025</div>
            </div>
            <div class="svr-stat">
                <div class="svr-stat-num">~4,700</div>
                <div class="svr-stat-label">student records terminated in SEVIS by early May, across 40+ states.</div>
                <div class="svr-stat-src">Presidents' Alliance · NAFSA</div>
            </div>
            <div class="svr-stat">
                <div class="svr-stat-num">280<small>+</small></div>
                <div class="svr-stat-label">colleges and universities where students abruptly lost status.</div>
                <div class="svr-stat-src">Inside Higher Ed, April 2025</div>
            </div>
            <div class="svr-stat">
                <div class="svr-stat-num">100<small>+</small></div>
                <div class="svr-stat-label">lawsuits filed; judges ordered records restored in dozens.</div>
                <div class="svr-stat-src">Inside Higher Ed · NPR</div>
            </div>
        </div>
    </div>

    {{-- ==================== PULL QUOTE ==================== --}}
    <div class="svr-wrap svr-section--tight">
        <blockquote class="svr-pull">
            <p>"We do it every day. Every time I find one of these lunatics, I take away their visa."</p>
            <cite>— Secretary of State Marco Rubio, March 27, 2025</cite>
        </blockquote>
    </div>

    <hr class="svr-divider">

    {{-- ==================== TRACKERS ==================== --}}
    <div class="svr-wrap svr-section">
        <div class="svr-eyebrow">Follow the data</div>
        <h2 class="svr-h2">Trackers</h2>
        <p class="svr-p">Independent trackers following detentions, deportations and the student-status crackdown in real time:</p>
        <ul class="svr-list">
            <li>
                <a href="https://forward.com" target="_blank" rel="noopener">ICE detention &amp; deportation tracker — The Forward →</a>
                <span class="svr-src">Running list of people detained and deported by ICE.</span>
            </li>
            <li>
                <a href="https://www.insidehighered.com/news/global/international-students-us" target="_blank" rel="noopener">International students in the U.S. — Inside Higher Ed →</a>
                <span class="svr-src">Ongoing coverage and counts of visa revocations and SEVIS terminations.</span>
            </li>
            <li>
                <a href="https://knightcolumbia.org/cases/aaup-v-rubio" target="_blank" rel="noopener">AAUP v. Rubio — Knight First Amendment Institute →</a>
                <span class="svr-src">Docket and filings in the challenge to the "ideological deportation" policy.</span>
            </li>
        </ul>
    </div>

    <hr class="svr-divider">

    {{-- ==================== IN THE NEWS ==================== --}}
    <div class="svr-wrap svr-section">
        <div class="svr-eyebrow">In the news</div>
        <h2 class="svr-h2">Selected coverage</h2>
        <ul class="svr-list">
            <li>
                <a href="https://www.cnn.com/2025/04/17/us/university-international-student-visas-revoked" target="_blank" rel="noopener">More than 1,000 international students have had visas or status revoked — CNN</a>
                <span class="svr-src">CNN · April 17, 2025</span>
            </li>
            <li>
                <a href="https://www.insidehighered.com/news/global/international-students-us/2025/04/25/ice-reverses-course-sevis-terminations" target="_blank" rel="noopener">ICE reverses course on SEVIS terminations — Inside Higher Ed</a>
                <span class="svr-src">Inside Higher Ed · April 25, 2025</span>
            </li>
            <li>
                <a href="https://apnews.com/hub/immigration" target="_blank" rel="noopener">DHS ran ~1.3 million student names through an FBI criminal database — Associated Press</a>
                <span class="svr-src">Associated Press · Spring 2025</span>
            </li>
            <li>
                <a href="https://www.npr.org" target="_blank" rel="noopener">Judges order release of detained students Öztürk, Mahdawi and Khan Suri — NPR</a>
                <span class="svr-src">NPR · May 2025</span>
            </li>
            <li>
                <a href="https://knightcolumbia.org/cases/aaup-v-rubio" target="_blank" rel="noopener">Faculty groups sue over the ideological-deportation policy — Knight First Amendment Institute</a>
                <span class="svr-src">Filed March 25, 2025</span>
            </li>
        </ul>
    </div>

    <hr class="svr-divider">

    {{-- ==================== CASE PROFILES ==================== --}}
    <div class="svr-wide svr-section">
        <div class="svr-wrap" style="padding-left:0;padding-right:0;">
            <div class="svr-eyebrow">The faces behind the numbers</div>
            <h2 class="svr-h2">Specific cases</h2>
            <p class="svr-p">Documented arrests, deportations and visa actions from 2025. Details are drawn from news reporting and court filings; outcomes in individual cases continue to change.</p>
        </div>

        <div class="svr-cases">

            <div class="svr-case">
                <img class="svr-avatar" src="{{ asset('images/campus-cases/mahmoud-khalil.webp') }}" alt="Mahmoud Khalil" loading="lazy" width="58" height="58">
                <div class="svr-case-body">
                    <h3>Mahmoud Khalil</h3>
                    <p class="svr-case-role">Columbia University · Lawful permanent resident</p>
                    <span class="svr-tag svr-tag-released">Detained · released on bail</span>
                    <p>A recent graduate and lead protest negotiator, arrested at his university apartment on March 8 and not charged with any crime. The case rested on a two-page memo from Secretary Rubio invoking the foreign-policy deportation ground. He was held for months in Jena, Louisiana, before a federal court freed him on bail in June.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Mahmoud%20Khalil" target="_blank" rel="noopener">Coverage of Mahmoud Khalil →</a>
                </div>
            </div>

            <div class="svr-case">
                <img class="svr-avatar" src="{{ asset('images/campus-cases/rumeysa-ozturk.webp') }}" alt="Rümeysa Öztürk" loading="lazy" width="58" height="58">
                <div class="svr-case-body">
                    <h3>Rümeysa Öztürk</h3>
                    <p class="svr-case-role">Tufts University · F-1 PhD student</p>
                    <span class="svr-tag svr-tag-released">Detained · released</span>
                    <p>Seized off a Somerville, Massachusetts street by plainclothes agents on March 25 as she walked to an iftar dinner; her visa had been quietly revoked days earlier. The only basis cited was a co-authored student-newspaper op-ed. A Vermont judge ordered her released on May 9, finding her arrest likely retaliation for protected speech.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Rumeysa%20Ozturk" target="_blank" rel="noopener">Coverage of Rümeysa Öztürk →</a>
                </div>
            </div>

            <div class="svr-case">
                <img class="svr-avatar" src="{{ asset('images/campus-cases/badar-khan-suri.webp') }}" alt="Badar Khan Suri" loading="lazy" width="58" height="58">
                <div class="svr-case-body">
                    <h3>Badar Khan Suri</h3>
                    <p class="svr-case-role">Georgetown University · J-1 scholar</p>
                    <span class="svr-tag svr-tag-released">Detained · released</span>
                    <p>A postdoctoral fellow arrested outside his Virginia home on March 17 by masked agents and moved to a Texas detention center. DHS alleged he "spread Hamas propaganda"; his lawyers said he was punished for his Gaza advocacy and his wife's family ties. He was released on May 14 after a judge found no evidence to justify his detention.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Badar%20Khan%20Suri" target="_blank" rel="noopener">Coverage of Badar Khan Suri →</a>
                </div>
            </div>

            <div class="svr-case">
                <img class="svr-avatar" src="{{ asset('images/campus-cases/mohsen-mahdawi.webp') }}" alt="Mohsen Mahdawi" loading="lazy" width="58" height="58">
                <div class="svr-case-body">
                    <h3>Mohsen Mahdawi</h3>
                    <p class="svr-case-role">Columbia University · Lawful permanent resident</p>
                    <span class="svr-tag svr-tag-released">Detained · released</span>
                    <p>A Palestinian organizer who grew up in a West Bank refugee camp, arrested on April 14 when he arrived for his U.S. citizenship interview in Vermont. A federal judge ordered him released on April 30, finding a "substantial claim" the arrest was meant to stifle disagreeable speech.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Mohsen%20Mahdawi" target="_blank" rel="noopener">Coverage of Mohsen Mahdawi →</a>
                </div>
            </div>

            <div class="svr-case">
                <img class="svr-avatar" src="{{ asset('images/campus-cases/yunseo-chung.webp') }}" alt="Yunseo Chung" loading="lazy" width="58" height="58">
                <div class="svr-case-body">
                    <h3>Yunseo Chung</h3>
                    <p class="svr-case-role">Columbia University · Lawful permanent resident</p>
                    <span class="svr-tag svr-tag-court">Protected by court order</span>
                    <p>A 21-year-old who came to the United States as a child. After a March campus protest, ICE sought to arrest her under the same foreign-policy provision — but she sued first, and a federal judge in New York barred the government from detaining her.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Yunseo%20Chung" target="_blank" rel="noopener">Coverage of Yunseo Chung →</a>
                </div>
            </div>

            <div class="svr-case">
                <img class="svr-avatar" src="{{ asset('images/campus-cases/rasha-alawieh.webp') }}" alt="Rasha Alawieh" loading="lazy" width="58" height="58">
                <div class="svr-case-body">
                    <h3>Rasha Alawieh</h3>
                    <p class="svr-case-role">Brown University · H-1B physician</p>
                    <span class="svr-tag svr-tag-removed">Deported</span>
                    <p>A kidney-transplant specialist and assistant professor detained at Boston Logan in March while returning from Lebanon, and deported despite a court order temporarily blocking her removal. The government cited photos and attendance at a funeral; her colleagues said she was a vital clinician.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Rasha%20Alawieh" target="_blank" rel="noopener">Coverage of Rasha Alawieh →</a>
                </div>
            </div>

            <div class="svr-case">
                <img class="svr-avatar" src="{{ asset('images/campus-cases/kseniia-petrova.webp') }}" alt="Kseniia Petrova" loading="lazy" width="58" height="58">
                <div class="svr-case-body">
                    <h3>Kseniia Petrova</h3>
                    <p class="svr-case-role">Harvard Medical School · J-1 researcher</p>
                    <span class="svr-tag svr-tag-detained">Detained</span>
                    <p>A scientist detained on February 16 returning from France over undeclared research samples. She faced deportation to Russia — which she had fled after protesting the war in Ukraine — and was held for months in an immigration facility while her case proceeded.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Kseniia%20Petrova" target="_blank" rel="noopener">Coverage of Kseniia Petrova →</a>
                </div>
            </div>

            <div class="svr-case">
                <img class="svr-avatar" src="{{ asset('images/campus-cases/dogukan-gunaydin.webp') }}" alt="Doğukan Günaydın" loading="lazy" width="58" height="58">
                <div class="svr-case-body">
                    <h3>Doğukan Günaydın</h3>
                    <p class="svr-case-role">University of Minnesota · F-1 graduate student</p>
                    <span class="svr-tag svr-tag-detained">Detained</span>
                    <p>A graduate business student detained on March 27; the government tied his status to a prior DUI. The university said it received no advance notice of the arrest of one of its international students.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Dogukan%20Gunaydin" target="_blank" rel="noopener">Coverage of Doğukan Günaydın →</a>
                </div>
            </div>

            <div class="svr-case">
                <img class="svr-avatar" src="{{ asset('images/campus-cases/alireza-doroudi.webp') }}" alt="Alireza Doroudi" loading="lazy" width="58" height="58">
                <div class="svr-case-body">
                    <h3>Alireza Doroudi</h3>
                    <p class="svr-case-role">University of Alabama · F-1 PhD student</p>
                    <span class="svr-tag svr-tag-detained">Detained</span>
                    <p>A mechanical-engineering doctoral student detained on March 26. The university said it had no information about the basis for his detention; he later chose to return to Iran rather than remain in custody.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Alireza%20Doroudi" target="_blank" rel="noopener">Coverage of Alireza Doroudi →</a>
                </div>
            </div>

            <div class="svr-case">
                <img class="svr-avatar" src="{{ asset('images/campus-cases/momodou-taal.webp') }}" alt="Momodou Taal" loading="lazy" width="58" height="58">
                <div class="svr-case-body">
                    <h3>Momodou Taal</h3>
                    <p class="svr-case-role">Cornell University · PhD student</p>
                    <span class="svr-tag svr-tag-removed">Left the U.S.</span>
                    <p>A dual British-Gambian doctoral student and pro-Palestine protester whose visa was revoked amid a lawsuit he had joined challenging the executive orders. Facing detention, he left the country rather than be held.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Momodou%20Taal" target="_blank" rel="noopener">Coverage of Momodou Taal →</a>
                </div>
            </div>

            <div class="svr-case">
                <img class="svr-avatar" src="{{ asset('images/campus-cases/ranjani-srinivasan.webp') }}" alt="Ranjani Srinivasan" loading="lazy" width="58" height="58">
                <div class="svr-case-body">
                    <h3>Ranjani Srinivasan</h3>
                    <p class="svr-case-role">Columbia University · F-1 PhD student</p>
                    <span class="svr-tag svr-tag-removed">Left the U.S.</span>
                    <p>An urban-planning doctoral student whose visa was revoked after she was swept up near campus protest arrests. DHS publicly labeled her a "terrorist sympathizer"; she left for Canada rather than face detention.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Ranjani%20Srinivasan" target="_blank" rel="noopener">Coverage of Ranjani Srinivasan →</a>
                </div>
            </div>

            <div class="svr-case">
                <img class="svr-avatar" src="{{ asset('images/campus-cases/xiaofeng-wang.webp') }}" alt="Xiaofeng Wang" loading="lazy" width="58" height="58">
                <div class="svr-case-body">
                    <h3>Xiaofeng Wang</h3>
                    <p class="svr-case-role">Indiana University · Professor</p>
                    <span class="svr-tag svr-tag-detained">Under investigation</span>
                    <p>A tenured cryptography professor and associate dean who, with his wife, dropped from public view after an FBI raid on their homes on March 27. The university removed their faculty profiles and gave no explanation; their whereabouts drew national concern.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Xiaofeng%20Wang%20Indiana" target="_blank" rel="noopener">Coverage of Xiaofeng Wang →</a>
                </div>
            </div>

            <div class="svr-case">
                <img class="svr-avatar" src="{{ asset('images/campus-cases/leqaa-kordia.webp') }}" alt="Leqaa Kordia" loading="lazy" width="58" height="58">
                <div class="svr-case-body">
                    <h3>Leqaa Kordia</h3>
                    <p class="svr-case-role">Columbia protests · Palestinian</p>
                    <span class="svr-tag svr-tag-detained">Detained</span>
                    <p>A Palestinian woman detained on March 14 and taken to a North Texas facility. DHS cited a visa overstay; immigration experts noted that detention over such a status issue is unusual and pointed to her presence at Columbia protests.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Leqaa%20Kordia" target="_blank" rel="noopener">Coverage of Leqaa Kordia →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">FZ</span>
                <div class="svr-case-body">
                    <h3>Felipe Zapata Velásquez</h3>
                    <p class="svr-case-role">University of Florida · F-1 student</p>
                    <span class="svr-tag svr-tag-removed">Returned to Colombia</span>
                    <p>A junior studying food and resource economics, arrested on March 28 after a Gainesville traffic stop over an expired registration and a suspended license. ICE took custody and held him at the Krome facility in Miami; he returned to Colombia in early April. DHS described it as a deportation, while his family did not confirm whether he signed self-deportation paperwork.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Felipe%20Zapata%20Velazquez" target="_blank" rel="noopener">Coverage of Felipe Zapata Velásquez →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">MH</span>
                <div class="svr-case-body">
                    <h3>Mohammed Hoque</h3>
                    <p class="svr-case-role">Minnesota State University, Mankato · F-1 student</p>
                    <span class="svr-tag svr-tag-released">Detained · released</span>
                    <p>An information-systems undergraduate arrested at his home by plainclothes agents on March 28, in front of his visiting parents, after his student record was revoked. The government cited a roughly two-year-old misdemeanor; he said he was targeted for pro-Palestinian posts. A federal judge ordered his release in early May, after about 40 days in jail, finding his speech — not the old charge — had made him a target.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Mohammed%20Hoque%20Mankato" target="_blank" rel="noopener">Coverage of Mohammed Hoque →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">AH</span>
                <div class="svr-case-body">
                    <h3>Aditya Wahyu Harsono</h3>
                    <p class="svr-case-role">Southwest Minnesota State University · Recent graduate on a student visa</p>
                    <span class="svr-tag svr-tag-released">Detained · released</span>
                    <p>A recent SMSU graduate working as a hospital supply-chain manager in Marshall, Minnesota, whose student visa was quietly revoked on March 23 and who was arrested at his workplace on March 27. The cited basis was a 2022 misdemeanor for spray-painted graffiti; his lawyers pointed to his pro-Palestinian activism. A federal judge ordered his release, finding the detention violated the First Amendment.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Aditya%20Harsono" target="_blank" rel="noopener">Coverage of Aditya Wahyu Harsono →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">AS</span>
                <div class="svr-case-body">
                    <h3>Ahwar Sultan</h3>
                    <p class="svr-case-role">Ohio State University · F-1 graduate student</p>
                    <span class="svr-tag svr-tag-court">Protected by court order</span>
                    <p>A master's graduate pursuing a PhD in art history, told to stop teaching and attending in early April after his SEVIS record was terminated and his visa revoked. The stated basis was a 2024 campus-protest trespassing arrest whose charges were later dismissed and expunged. A federal judge ordered his status restored, ruling it could not be pulled over the dismissed arrest; he was not detained.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Ahwar%20Sultan%20Ohio%20State" target="_blank" rel="noopener">Coverage of Ahwar Sultan →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">XL</span>
                <div class="svr-case-body">
                    <h3>Xiaotian Liu</h3>
                    <p class="svr-case-role">Dartmouth College · F-1 student</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>A computer-science PhD candidate with no criminal record whose SEVIS record was terminated on April 4 with no individualized explanation. He sued in New Hampshire, and Judge Samantha Elliott granted a temporary restraining order preserving his status on April 9 — among the first such orders in the country. The government restored his status that summer, and he dismissed the suit.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Xiaotian%20Liu%20Dartmouth" target="_blank" rel="noopener">Coverage of Xiaotian Liu →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">SO</span>
                <div class="svr-case-body">
                    <h3>Suguru Onda</h3>
                    <p class="svr-case-role">Brigham Young University · F-1 student</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>A computer-science PhD candidate from Japan, about a year from finishing, whose status was terminated on April 8 citing a criminal-records "match." His only record was a since-dismissed 2019 fishing-limit citation and a couple of speeding tickets. He sued; his record was restored "as if it was never revoked" just minutes after the filing — his attorney suspected an automated flag.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Suguru%20Onda%20BYU%20visa" target="_blank" rel="noopener">Coverage of Suguru Onda →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">ZC</span>
                <div class="svr-case-body">
                    <h3>Zhuoer Chen</h3>
                    <p class="svr-case-role">UC Berkeley · F-1 student</p>
                    <span class="svr-tag svr-tag-court">Protected by court order</span>
                    <p>A master's student in architecture, in the U.S. since 2017, told on April 8 that her SEVIS record was terminated with no reason or chance to respond; her only prior police contact was a 2022 incident that brought no charges. As lead plaintiff in <em>Chen v. Noem</em>, she won a San Francisco court order barring the government from detaining or removing her and her co-plaintiffs — Mengcheng Yu at Carnegie Mellon, Jiarong Ouyang at Cincinnati and Gexi Guo, a Columbia graduate.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Zhuoer%20Chen%20Berkeley%20visa" target="_blank" rel="noopener">Coverage of Zhuoer Chen →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">CD</span>
                <div class="svr-case-body">
                    <h3>Chinmay Deore</h3>
                    <p class="svr-case-role">Wayne State University · F-1 student</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>A 21-year-old computer-science undergraduate from India whose SEVIS record was terminated on April 4 over a "criminal records check" — his record was a paid speeding ticket and a parking ticket. He is the lead plaintiff in the ACLU of Michigan's suit, alongside Yogesh Joshi at Wayne State and Xiangyun Bu and Qiuyi Yang at the University of Michigan. Their records were restored and the case settled in May, barring re-termination on the same basis.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Chinmay%20Deore%20Wayne%20State" target="_blank" rel="noopener">Coverage of Chinmay Deore →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">KI</span>
                <div class="svr-case-body">
                    <h3>Krish Lal Isserdasani</h3>
                    <p class="svr-case-role">University of Wisconsin–Madison · F-1 student</p>
                    <span class="svr-tag svr-tag-court">Protected by court order</span>
                    <p>A computer-engineering senior from India whose SEVIS record was terminated weeks before his May graduation, apparently over a late-2024 disorderly-conduct arrest that prosecutors never charged. His family had spent roughly $240,000 on his education. On April 15, Judge William Conley issued a restraining order, finding an "overwhelming" likelihood his status was ended without cause; it was reinstated days later.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Krish%20Isserdasani%20Wisconsin" target="_blank" rel="noopener">Coverage of Krish Lal Isserdasani →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">YY</span>
                <div class="svr-case-body">
                    <h3>Yue “Alison” Yang</h3>
                    <p class="svr-case-role">University of Wisconsin–Madison · F-1 student</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>A 21-year-old business student from China, set to graduate in May, whose F-1 status was terminated apparently over a February speeding ticket she had already resolved. Judge William Conley called the termination "arbitrary" and "an abuse of discretion," noting it would leave her having paid some $130,000 in tuition for no degree. Her status was reinstated.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Yue%20Yang%20Wisconsin%20visa" target="_blank" rel="noopener">Coverage of Yue Yang →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">JO</span>
                <div class="svr-case-body">
                    <h3>Jiarong Ouyang</h3>
                    <p class="svr-case-role">University of Cincinnati · F-1 student</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>A statistics doctoral candidate from China, in the U.S. since 2012 and working as a graduate assistant at Cincinnati Children's Hospital, whose status was terminated in April over a 2019 domestic-dispute arrest that was dismissed without conviction. The first UC student to sue Secretary Noem, he won a restraining order, and his status was restored in the nationwide reversal.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Jiarong%20Ouyang%20Cincinnati%20visa" target="_blank" rel="noopener">Coverage of Jiarong Ouyang →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">JL</span>
                <div class="svr-case-body">
                    <h3>Jelena Liu</h3>
                    <p class="svr-case-role">Indiana University Indianapolis · F-1 student</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>A second-year informatics graduate student from China with no criminal record, told on April 3 that her status and SEVIS record were terminated — flagged, per an ACLU complaint, as a "match" to a 2018 border encounter. One of seven ACLU of Indiana plaintiffs, she had her status restored on April 29; she still does not know the underlying reason.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Jelena%20Liu%20IU%20Indianapolis%20visa" target="_blank" rel="noopener">Coverage of Jelena Liu →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">AR</span>
                <div class="svr-case-body">
                    <h3>Anjan Roy</h3>
                    <p class="svr-case-role">Missouri State University · F-1 student</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>A 23-year-old computer-science master's student from Bangladesh, emailed on April 10 that a database check showed his status terminated for reasons unknown; the U.S. embassy separately warned his visa was revoked and he could be detained at any time. His only prior contact with police was a 2021 campus inquiry that produced no charges. His status was restored after students prevailed in court.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Anjan%20Roy%20Missouri%20State%20visa" target="_blank" rel="noopener">Coverage of Anjan Roy →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">P5</span>
                <div class="svr-case-body">
                    <h3>The “Purdue Five”</h3>
                    <p class="svr-case-role">Purdue University · International students</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>Five Chinese students — Nina Hu, Junde Zhu, Xiaotian Yu, Xilai Dai and Zhaorui Ni — were emailed on April 9 that their status had been revoked, most citing vague "criminal records" the ACLU argued met no legal threshold for termination. A judge initially declined to block the terminations, leaving them at risk while they sued; their statuses were restored within weeks as the administration reversed course.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Purdue%20students%20visa%20revoked%20ACLU" target="_blank" rel="noopener">Coverage of the Purdue students →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">NE</span>
                <div class="svr-case-body">
                    <h3>The Pasula plaintiffs</h3>
                    <p class="svr-case-role">Rivier University &amp; WPI · F-1 students</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>Five named plaintiffs in a New England class action — Manikanta Pasula, Likhith Babu Gorrela and Thanuj Kumar Gummadavelli, computer-science students at Rivier University, and Hangrui Zhang and Haoyang An at Worcester Polytechnic — had their SEVIS records terminated in early April, several just weeks from graduating. After a wave of court orders nationwide, DHS reversed the terminations on April 25 and the case later settled.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Pasula%20DHS%20New%20England%20students%20visa" target="_blank" rel="noopener">Coverage of the Pasula plaintiffs →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">SA</span>
                <div class="svr-case-body">
                    <h3>Saleh Al Gurad</h3>
                    <p class="svr-case-role">North Carolina State University · Student visa</p>
                    <span class="svr-tag svr-tag-removed">Returned to Saudi Arabia</span>
                    <p>A master's student in engineering management from Saudi Arabia, emailed on March 27 that his visa had been canceled with no explanation given. His roommate said he was apolitical and involved in no activism. Rather than risk detention, he packed up and flew home to Saudi Arabia on March 31, with the option of appealing from abroad.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Saleh%20Al%20Gurad%20NC%20State" target="_blank" rel="noopener">Coverage of Saleh Al Gurad →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">HJ</span>
                <div class="svr-case-body">
                    <h3>Hyeongseon Jeon</h3>
                    <p class="svr-case-role">University of Houston · Assistant professor</p>
                    <span class="svr-tag svr-tag-removed">Returned to South Korea</span>
                    <p>An assistant professor of mathematics, hired in 2024, who told his students on April 13 that he could no longer teach because of the "unexpected termination" of his visa and had to return to South Korea immediately. The university said his authorization was pulled over his prior status as a doctoral student elsewhere. His case came amid a wave that hit more than 250 students and scholars across Texas campuses.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Hyeongseon%20Jeon%20University%20of%20Houston%20visa" target="_blank" rel="noopener">Coverage of Hyeongseon Jeon →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">P&amp;F</span>
                <div class="svr-case-body">
                    <h3>Pouria Pourhosseinhendabad &amp; Parisa Firouzabadi</h3>
                    <p class="svr-case-role">Louisiana State University · PhD students</p>
                    <span class="svr-tag svr-tag-released">Detained · released</span>
                    <p>An Iranian married couple in LSU's mechanical-engineering PhD program, arrested by ICE in Baton Rouge in late June — hours after U.S. airstrikes on Iran. Their lawyers said agents used a "ruse," luring them out under the pretext of investigating a hit-and-run the couple had themselves reported. After nearly a month in detention, a federal magistrate found a "grave risk" of irreparable harm and both were released.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=LSU%20Iranian%20students%20ICE%20released" target="_blank" rel="noopener">Coverage of the LSU students →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">SS</span>
                <div class="svr-case-body">
                    <h3>Sajawal Ali Sohail</h3>
                    <p class="svr-case-role">West Virginia University · F-1 student</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>A 25-year-old computer-science student from Pakistan whose SEVIS record was terminated on April 10 over a "criminal records" flag. The record stemmed from a scam in which his father was defrauded while paying his tuition — a case in which a judge had ruled Sohail and his family were the victims, not the perpetrators. The ACLU of West Virginia sued, and his status was restored on April 25.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Sajawal%20Ali%20Sohail%20WVU" target="_blank" rel="noopener">Coverage of Sajawal Ali Sohail →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">MA</span>
                <div class="svr-case-body">
                    <h3>Matthew Ariwoola</h3>
                    <p class="svr-case-role">University of South Carolina · F-1 PhD student</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>A 32-year-old chemistry PhD candidate from Nigeria who taught undergraduates and researched making medications more effective, told on April 8 that his status was terminated and he could neither study nor teach. The cited basis was a 2023 Georgia arrest warrant — though he had never been to Georgia and the charges were dismissed. The ACLU of South Carolina sued; a judge granted a restraining order and his status was restored.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Matthew%20Ariwoola%20South%20Carolina%20visa" target="_blank" rel="noopener">Coverage of Matthew Ariwoola →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">HK</span>
                <div class="svr-case-body">
                    <h3>Hamidreza Khademi</h3>
                    <p class="svr-case-role">Iowa State University · F-1, OPT</p>
                    <span class="svr-tag svr-tag-court">Protected by court order</span>
                    <p>A 34-year-old from Iran who earned a master's in architecture and was working on OPT as a project manager at the Dallas-Fort Worth airport. Iowa State told him on April 10 that his SEVIS record was terminated over a February 2024 traffic stop — one the Texas state police themselves determined was not a violation, with no charges filed. He sued and won early injunctive relief, and the case continued in Washington.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Hamidreza%20Khademi%20Iowa%20State%20visa" target="_blank" rel="noopener">Coverage of Hamidreza Khademi →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">PS</span>
                <div class="svr-case-body">
                    <h3>Priya Saxena</h3>
                    <p class="svr-case-role">South Dakota Mines · F-1 PhD student</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>A 28-year-old chemical-and-biological-engineering PhD candidate from India, weeks from her May graduation, whose visa was revoked in early April citing "additional information." The trigger was a single minor 2020 misdemeanor — failing to stop for an emergency vehicle. Judge Karen Schreier found a due-process violation and ordered her status restored, clearing her to attend commencement.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Priya%20Saxena%20South%20Dakota%20Mines%20visa" target="_blank" rel="noopener">Coverage of Priya Saxena →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">KM</span>
                <div class="svr-case-body">
                    <h3>Kiran Manjunatha</h3>
                    <p class="svr-case-role">University at Buffalo (SUNY) · F-1, OPT</p>
                    <span class="svr-tag svr-tag-removed">Left the U.S.</span>
                    <p>A finance graduate from India working at Citibank on OPT, whose SEVIS record was terminated in early April over a since-dismissed misdemeanor for driving without a valid license. One of 13 current and former Buffalo students flagged, he sued — but, fearing detention and deportation to El Salvador, he left the United States, and would need a new visa to return.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Kiran%20Manjunatha%20Buffalo%20visa" target="_blank" rel="noopener">Coverage of Kiran Manjunatha →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">JO</span>
                <div class="svr-case-body">
                    <h3>Jideofor Odoeze</h3>
                    <p class="svr-case-role">University of Notre Dame · F-1 PhD student</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>A Nigerian electrical-engineering doctoral candidate set to graduate in May, in South Bend since 2018 with his wife and two U.S.-citizen children, whose status was terminated with no notice; Notre Dame advised him to prepare to leave the country. A plaintiff in the ACLU of Indiana's suit, he had his SEVIS record reactivated by late April, with ICE agreeing not to re-terminate on the same basis.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Jideofor%20Odoeze%20Notre%20Dame%20visa" target="_blank" rel="noopener">Coverage of Jideofor Odoeze →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">PC</span>
                <div class="svr-case-body">
                    <h3>Parth Atul Chatwani</h3>
                    <p class="svr-case-role">Northwestern University · F-1, OPT</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>A recent graduate from India working under OPT, whose F-1 record was terminated in the April wave with no individualized reason — he had no criminal record and no political activity. He sued, and on May 12 Judge Sara Ellis ordered DHS to reinstate his lawful status.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Parth%20Chatwani%20Northwestern%20visa" target="_blank" rel="noopener">Coverage of Parth Chatwani →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">AG</span>
                <div class="svr-case-body">
                    <h3>Aaron Ortega Gonzalez</h3>
                    <p class="svr-case-role">Oregon State University · F-1 PhD student</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>A doctoral student from Mexico researching how wildfires affect ranchlands, whose F-1 record was covertly terminated on April 4 and who was told to leave immediately; his attorneys said he had never even received a traffic ticket. With the ACLU of Oregon he sued, and Judge Michael McShane ordered his status restored, calling the termination "so arbitrary and capricious." He was never detained.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Aaron%20Ortega%20Gonzalez%20Oregon%20State%20visa" target="_blank" rel="noopener">Coverage of Aaron Ortega Gonzalez →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">JK</span>
                <div class="svr-case-body">
                    <h3>Jean Kashikov</h3>
                    <p class="svr-case-role">University of Alaska Anchorage · F-1, OPT</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>A 2024 graduate from Kazakhstan — a mathematics and professional-piloting major working as a flight instructor on OPT — who learned in a restaurant on April 10 that his SEVIS record was terminated. His only record was a dropped 2022 Arizona arrest and a dismissed speeding ticket. The ACLU of Alaska sued, and his status was restored days later.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Jean%20Kashikov%20Alaska%20visa" target="_blank" rel="noopener">Coverage of Jean Kashikov →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">EZ</span>
                <div class="svr-case-body">
                    <h3>Edward Zhou</h3>
                    <p class="svr-case-role">UCLA · F-1 student</p>
                    <span class="svr-tag svr-tag-court">Protected by court order</span>
                    <p>A fourth-year undergraduate from China told on April 3 that his F-1 record was terminated over an unspecified "criminal records" notation, with his visa revoked two days later. He sued ICE, and on April 15 Judge Cynthia Valenzuela set aside the termination and reinstated his status, finding the government had exceeded its authority and that he faced irreparable harm. He was not detained.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Edward%20Zhou%20UCLA%20visa" target="_blank" rel="noopener">Coverage of Edward Zhou →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">UI</span>
                <div class="svr-case-body">
                    <h3>The University of Iowa four</h3>
                    <p class="svr-case-role">University of Iowa · F-1 students</p>
                    <span class="svr-tag svr-tag-court">Protected by court order</span>
                    <p>Four University of Iowa students and recent graduates — Sri Chaitanya Krishna Akondy, an epidemiologist working on OPT; chemical-engineering student Prasoon Kumar; and undergraduates Songli Cai and Haoran Yang — had their SEVIS records terminated on April 10 with no explanation, then received embassy warnings of possible detention. They sued, and on May 15 Judge Rebecca Goodgame Ebinger barred the government from detaining or removing them, citing a "strong likelihood" they would prevail.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=University%20of%20Iowa%20students%20visa%20lawsuit%20Ebinger" target="_blank" rel="noopener">Coverage of the Iowa students →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">CT</span>
                <div class="svr-case-body">
                    <h3>The Connecticut plaintiffs</h3>
                    <p class="svr-case-role">Yale &amp; UConn · F-1 students</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>Four students in a single ACLU of Connecticut suit — Yale PhD candidates Yan Du and Mengni He, with UConn's Elika Shams, a biomedical-engineering PhD from Iran, and Stephen Azu, a Ghanaian researcher on OPT — had their SEVIS records terminated in early April over dismissed or trivial records. Their status was restored within weeks, and a federal judge barred the government from removing them from Connecticut while the case proceeded.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Connecticut%20students%20visa%20Du%20DHS%20ACLU" target="_blank" rel="noopener">Coverage of the Connecticut students →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">ZV</span>
                <div class="svr-case-body">
                    <h3>Zeel &amp; Vraj Patel</h3>
                    <p class="svr-case-role">Gannon University · F-1 students</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>Zeel Patel, a graduate management student, and Vraj V. Patel, an information-systems undergraduate near graduation — both from India — had their visas revoked in early April over minor matters such as a roommate dispute and traffic tickets. On April 17, Judge W. Scott Hardy granted restraining orders restoring their status while their suits proceeded.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Gannon%20University%20students%20visa%20Patel" target="_blank" rel="noopener">Coverage of the Gannon students →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">TX</span>
                <div class="svr-case-body">
                    <h3>The North Texas plaintiffs</h3>
                    <p class="svr-case-role">UT Dallas &amp; UT Arlington · F-1 / OPT</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>Three North Texas students and graduates — data engineer Manoj Mashatti of UT Dallas, and thermal engineer Chandraprakash Hinge and information-systems student Akshar Patel of UT Arlington — sued after their SEVIS records were terminated over old or dismissed traffic and misdemeanor records. In Patel's case, Judge Ana Reyes publicly called the terminations "arbitrary and capricious"; all three had their status restored.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Patel%20v%20Lyons%20Texas%20students%20visa%20Reyes" target="_blank" rel="noopener">Coverage of the North Texas students →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">RGV</span>
                <div class="svr-case-body">
                    <h3>The UTRGV plaintiffs</h3>
                    <p class="svr-case-role">UT Rio Grande Valley · F-1 students</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>Four students at UT Rio Grande Valley — physics PhD candidates Hugo Adrián Villar Castellanos of Mexico and Shishir Timilsena of Nepal, finance PhD student Amir Gholami of Iran, and computer-science undergraduate Julio Dylan Sanchez Wong of Mexico — learned on April 8 that their SEVIS records were terminated over minor, mostly dismissed citations, and lost their campus jobs. They sued Secretary Noem, and their records were restored in the late-April reversal.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=UTRGV%20students%20visa%20lawsuit%20Noem" target="_blank" rel="noopener">Coverage of the UTRGV students →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">DP</span>
                <div class="svr-case-body">
                    <h3>The DePaul plaintiffs</h3>
                    <p class="svr-case-role">DePaul University · F-1 students</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>Two DePaul students from India — business-analytics student Vishnu Vardhan Nali and recent graduate Satyanarayana Mekarthi, on OPT — had their SEVIS records terminated on April 8 over a dropped shoplifting arrest and a minor traffic charge. Both sued; a judge ordered Nali's record reinstated, and their status was restored in the nationwide reversal.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=DePaul%20students%20visa%20lawsuit%20Nali" target="_blank" rel="noopener">Coverage of the DePaul students →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">ZJ</span>
                <div class="svr-case-body">
                    <h3>Ziliang Jin</h3>
                    <p class="svr-case-role">University of Minnesota · F-1 student</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>A graduate student from China in geographic-information science, told on April 8 that ICE had "unilaterally terminated" his SEVIS record — the only basis being four petty-misdemeanor traffic violations, among them a parking ticket and a speeding citation. He sued in Minnesota; a federal judge ordered his status reinstated retroactively and barred any adverse action, and the case was dismissed after ICE restored it.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Ziliang%20Jin%20Minnesota%20visa" target="_blank" rel="noopener">Coverage of Ziliang Jin →</a>
                </div>
            </div>

            <div class="svr-case">
                <span class="svr-avatar" aria-hidden="true">RR</span>
                <div class="svr-case-body">
                    <h3>Rattanand Ratsantiboon</h3>
                    <p class="svr-case-role">Metropolitan State University · F-1 student</p>
                    <span class="svr-tag svr-tag-court">Status restored</span>
                    <p>A 31-year-old nursing student from Thailand, in the U.S. since 2014, whose SEVIS record was terminated on March 28 over 2018 driving offenses flagged in a "criminal records check" — a careless-driving citation and a DWI for which he had completed probation in 2021. ICE notified neither him nor the university; he sued, and a federal judge ordered his status reinstated retroactive to March 28.</p>
                    <a class="svr-coverage" href="https://news.google.com/search?q=Rattanand%20Ratsantiboon%20Metro%20State%20ICE" target="_blank" rel="noopener">Coverage of Rattanand Ratsantiboon →</a>
                </div>
            </div>

        </div>
    </div>

    <hr class="svr-divider">

    {{-- ==================== SEVIS LAWSUITS ==================== --}}
    <div class="svr-wrap svr-section">
        <div class="svr-eyebrow">The legal fight</div>
        <h2 class="svr-h2">SEVIS lawsuits</h2>
        <p class="svr-p">When the government abruptly terminated thousands of SEVIS records in spring 2025, students fought back in federal court — and largely won. By late April, at least 290 of them were litigating across roughly 65 lawsuits, and about 35 had secured emergency court orders pausing their removal before the government reversed course. The docket below reproduces Inside Higher Ed's lawsuit tracker as of April 23, 2025, with a few later-filed suits added:</p>
        <input type="text" class="svr-tbl-search" id="svr-lawsuits-search" placeholder="Search by case, court, or school…" onkeyup="svrFilterLawsuits(this.value)" aria-label="Search SEVIS lawsuits">
        <div class="svr-tbl-wrap">
            <table class="svr-tbl" id="svr-lawsuits-table">
                <thead>
                    <tr><th>Lawsuit</th><th class="svr-tbl-num">Plaintiffs</th><th>District</th><th>Filed</th><th>Outcome</th><th>School(s)</th></tr>
                </thead>
                <tbody>
                    <tr><td>Student Doe 1 v. Noem</td><td class="svr-tbl-num">1</td><td>C.D. Cal.</td><td>Apr 5, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>A college in the Inland Empire, CA</td></tr>
                    <tr><td>Student Doe 2 v. Noem</td><td class="svr-tbl-num">1</td><td>C.D. Cal.</td><td>Apr 6, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>A college in Orange County, CA</td></tr>
                    <tr><td>Chengkai Zou v. Lyons</td><td class="svr-tbl-num">1</td><td>C.D. Cal.</td><td>Apr 7, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>UCLA</td></tr>
                    <tr><td>Liu v. Noem</td><td class="svr-tbl-num">1</td><td>D.N.H.</td><td>Apr 7, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>Dartmouth</td></tr>
                    <tr><td>Shandilya v. Noem</td><td class="svr-tbl-num">2</td><td>W.D. Pa.</td><td>Apr 7, 2025</td><td><span class="svr-tbl-part">TRO granted in part</span></td><td>An institution in W.D. Pennsylvania</td></tr>
                    <tr><td>Ratsantiboon v. Noem</td><td class="svr-tbl-num">1</td><td>D. Minn.</td><td>Apr 8, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>Metropolitan State University</td></tr>
                    <tr><td>Student Doe 3 v. Noem</td><td class="svr-tbl-num">1</td><td>C.D. Cal.</td><td>Apr 8, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>A university in Los Angeles</td></tr>
                    <tr><td>Doe v. Noem</td><td class="svr-tbl-num">1</td><td>W.D. Wash.</td><td>Apr 9, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>University of Washington</td></tr>
                    <tr><td>Wu v. Lyons</td><td class="svr-tbl-num">2</td><td>E.D.N.Y.</td><td>Apr 9, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td><span class="svr-tbl-unknown">Not disclosed</span></td></tr>
                    <tr><td>Deore v. DHS</td><td class="svr-tbl-num">4</td><td>E.D. Mich.</td><td>Apr 10, 2025</td><td><span class="svr-tbl-part">TRO denied in part</span></td><td>U. of Michigan, Wayne State</td></tr>
                    <tr><td>Zheng v. Lyons</td><td class="svr-tbl-num">1</td><td>D. Mass.</td><td>Apr 11, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td><span class="svr-tbl-unknown">Not disclosed</span></td></tr>
                    <tr><td>Jane Doe 1 v. Bondi</td><td class="svr-tbl-num">133</td><td>N.D. Ga.</td><td>Apr 11, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>Kennesaw State, Georgia Tech, Emory, UGA and many others</td></tr>
                    <tr><td>Mashatti v. Lyons</td><td class="svr-tbl-num">1</td><td>D.D.C.</td><td>Apr 11, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>UT Dallas</td></tr>
                    <tr><td>Nali v. Noem</td><td class="svr-tbl-num">1</td><td>N.D. Ill.</td><td>Apr 11, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>DePaul</td></tr>
                    <tr><td>Patel v. Lyons</td><td class="svr-tbl-num">1</td><td>D.D.C.</td><td>Apr 11, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td><span class="svr-tbl-unknown">Not disclosed</span></td></tr>
                    <tr><td>S.Y. v. Noem</td><td class="svr-tbl-num">5</td><td>N.D. Cal.</td><td>Apr 11, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td><span class="svr-tbl-unknown">Not disclosed</span></td></tr>
                    <tr><td>Baddam v. Lyons</td><td class="svr-tbl-num">1</td><td>D.D.C.</td><td>Apr 11, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td><span class="svr-tbl-unknown">Not disclosed</span></td></tr>
                    <tr><td>Bushireddy v. Lyons</td><td class="svr-tbl-num">1</td><td>D.D.C.</td><td>Apr 11, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td><span class="svr-tbl-unknown">Not disclosed</span></td></tr>
                    <tr><td>Chen v. Noem</td><td class="svr-tbl-num">4</td><td>N.D. Cal.</td><td>Apr 11, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>UC Berkeley, Carnegie Mellon, Cincinnati, Columbia</td></tr>
                    <tr><td>Doe v. Noem</td><td class="svr-tbl-num">1</td><td>D. Mass.</td><td>Apr 11, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>MIT</td></tr>
                    <tr><td>Hinge v. Lyons</td><td class="svr-tbl-num">1</td><td>D.D.C.</td><td>Apr 11, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td><span class="svr-tbl-unknown">Not disclosed</span></td></tr>
                    <tr><td>Yuan v. Lyons</td><td class="svr-tbl-num">1</td><td>D. Mass.</td><td>Apr 14, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td><span class="svr-tbl-unknown">Not disclosed</span></td></tr>
                    <tr><td>Isserdasani v. Noem</td><td class="svr-tbl-num">2</td><td>D.D.C.</td><td>Apr 14, 2025</td><td><span class="svr-tbl-part">TRO granted in part</span></td><td>UW–Madison, U. of Iowa</td></tr>
                    <tr><td>Isserdasani v. Noem</td><td class="svr-tbl-num">2</td><td>W.D. Wis.</td><td>Apr 14, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td><span class="svr-tbl-unknown">Not disclosed</span></td></tr>
                    <tr><td>Jin v. Noem</td><td class="svr-tbl-num">1</td><td>D. Minn.</td><td>Apr 14, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>U. of Minnesota</td></tr>
                    <tr><td>Kshatri v. Lyons</td><td class="svr-tbl-num">1</td><td>N.D. Ala.</td><td>Apr 14, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>U. of Alabama at Birmingham</td></tr>
                    <tr><td>Roe v. Noem</td><td class="svr-tbl-num">2</td><td>D. Mont.</td><td>Apr 14, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>Montana State University</td></tr>
                    <tr><td>Arizona Student Doe 1 v. Trump</td><td class="svr-tbl-num">1</td><td>D. Ariz.</td><td>Apr 14, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>An Arizona college (undisclosed)</td></tr>
                    <tr><td>Arizona Student Doe 2 v. Trump</td><td class="svr-tbl-num">1</td><td>D. Ariz.</td><td>Apr 14, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>An Arizona college (undisclosed)</td></tr>
                    <tr><td>Doe v. Bondi</td><td class="svr-tbl-num">1</td><td>D. Mass.</td><td>Apr 14, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>Boston University</td></tr>
                    <tr><td>Doe v. Noem</td><td class="svr-tbl-num">1</td><td>E.D. Cal.</td><td>Apr 14, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td><span class="svr-tbl-unknown">Not disclosed</span></td></tr>
                    <tr><td>Chatwani v. Noem</td><td class="svr-tbl-num">1</td><td>N.D. Ill.</td><td>Apr 14, 2025</td><td><span class="svr-tbl-yes">Injunction</span></td><td>Northwestern</td></tr>
                    <tr><td>Liu v. Noem</td><td class="svr-tbl-num">7</td><td>S.D. Ind.</td><td>Apr 15, 2025</td><td><span class="svr-tbl-part">TRO denied</span></td><td>IU Indianapolis, Purdue, Notre Dame</td></tr>
                    <tr><td>Manjunatha v. Noem</td><td class="svr-tbl-num">1</td><td>W.D.N.Y.</td><td>Apr 15, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>University at Buffalo (SUNY)</td></tr>
                    <tr><td>Sultan v. Trump</td><td class="svr-tbl-num">1</td><td>D.D.C.</td><td>Apr 15, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>Ohio State University</td></tr>
                    <tr><td>Villar Castellanos v. Noem</td><td class="svr-tbl-num">4</td><td>S.D. Tex.</td><td>Apr 15, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>UT Rio Grande Valley</td></tr>
                    <tr><td>Ortega Gonzalez v. Noem</td><td class="svr-tbl-num">1</td><td>D. Or.</td><td>Apr 16, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td><span class="svr-tbl-unknown">Not disclosed</span></td></tr>
                    <tr><td>Oruganti v. Noem</td><td class="svr-tbl-num">1</td><td>S.D. Ohio</td><td>Apr 16, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>Ohio State University</td></tr>
                    <tr><td>Patel v. Bondi</td><td class="svr-tbl-num">1</td><td>W.D. Pa.</td><td>Apr 16, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>Gannon University</td></tr>
                    <tr><td>Onda v. Noem</td><td class="svr-tbl-num">1</td><td>D. Utah</td><td>Apr 16, 2025</td><td><span class="svr-tbl-yes">Status restored</span></td><td>BYU</td></tr>
                    <tr><td>Chen v. Noem</td><td class="svr-tbl-num">1</td><td>S.D. Ind.</td><td>Apr 17, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td><span class="svr-tbl-unknown">Not disclosed</span></td></tr>
                    <tr><td>Daou v. Noem</td><td class="svr-tbl-num">1</td><td>M.D. Fla.</td><td>Apr 17, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>UT Austin</td></tr>
                    <tr><td>Doe 1 v. Noem</td><td class="svr-tbl-num">2</td><td>W.D.N.Y.</td><td>Apr 17, 2025</td><td><span class="svr-tbl-part">TRO granted in part</span></td><td>Rochester Institute of Technology</td></tr>
                    <tr><td>Doe v. Noem</td><td class="svr-tbl-num">1</td><td>N.D. Ill.</td><td>Apr 17, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>University of Delaware</td></tr>
                    <tr><td>Student Doe 1 v. Trump</td><td class="svr-tbl-num">8</td><td>N.D. Ill.</td><td>Apr 17, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td><span class="svr-tbl-unknown">Not disclosed</span></td></tr>
                    <tr><td>T.S. v. Noem</td><td class="svr-tbl-num">1</td><td>W.D.N.Y.</td><td>Apr 17, 2025</td><td><span class="svr-tbl-part">TRO granted in part</span></td><td>Rochester Institute of Technology</td></tr>
                    <tr><td>D.B. v. Trump</td><td class="svr-tbl-num">1</td><td>S.D. Ohio</td><td>Apr 18, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>An institution in Texas (undisclosed)</td></tr>
                    <tr><td>Doe v. Noem</td><td class="svr-tbl-num">1</td><td>N.D. Ill.</td><td>Apr 18, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>A university in Chicago (undisclosed)</td></tr>
                    <tr><td>P.V. v. Noem</td><td class="svr-tbl-num">1</td><td>W.D.N.Y.</td><td>Apr 18, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>University at Buffalo (SUNY)</td></tr>
                    <tr><td>Pasula v. DHS</td><td class="svr-tbl-num">5</td><td>D.N.H.</td><td>Apr 18, 2025</td><td><span class="svr-tbl-unknown">No ruling (PI)</span></td><td>Rivier, WPI</td></tr>
                    <tr><td>Saxena v. Noem</td><td class="svr-tbl-num">1</td><td>D. Conn.</td><td>Apr 18, 2025</td><td><span class="svr-tbl-part">TRO granted in part</span></td><td>South Dakota Mines</td></tr>
                    <tr><td>Vyas v. Noem</td><td class="svr-tbl-num">1</td><td>S.D. W.Va.</td><td>Apr 18, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>Marshall University</td></tr>
                    <tr><td>Yang v. Noem</td><td class="svr-tbl-num">1</td><td>S.D.N.Y.</td><td>Apr 18, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td><span class="svr-tbl-unknown">Not disclosed</span></td></tr>
                    <tr><td>Ariwoola v. Noem</td><td class="svr-tbl-num">1</td><td>D.S.C.</td><td>Apr 18, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>U. of South Carolina</td></tr>
                    <tr><td>R.I. v. Bondi</td><td class="svr-tbl-num">1</td><td>E.D. Pa.</td><td>Apr 21, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>Thomas Jefferson University</td></tr>
                    <tr><td>Rameez Shaik v. Noem</td><td class="svr-tbl-num">5</td><td>D. Minn.</td><td>Apr 21, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>Concordia University</td></tr>
                    <tr><td>Doe 4 v. Noem</td><td class="svr-tbl-num">16</td><td>C.D. Cal.</td><td>Apr 21, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>Various (California)</td></tr>
                    <tr><td>Hu v. Secretary of DHS</td><td class="svr-tbl-num">8</td><td>N.D. Ind.</td><td>Apr 21, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>Purdue, Notre Dame, Indiana Tech</td></tr>
                    <tr><td>Jin v. Noem</td><td class="svr-tbl-num">1</td><td>C.D. Ill.</td><td>Apr 21, 2025</td><td><span class="svr-tbl-yes">TRO granted</span></td><td>U. of Illinois Urbana-Champaign</td></tr>
                    <tr><td>J.M. v. Noem</td><td class="svr-tbl-num">1</td><td>D.N.J.</td><td>Apr 21, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>Boston University</td></tr>
                    <tr><td>Qui v. Lyons</td><td class="svr-tbl-num">1</td><td>N.D. Cal.</td><td>Apr 21, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td><span class="svr-tbl-unknown">Not disclosed</span></td></tr>
                    <tr><td>Alduaij v. Noem</td><td class="svr-tbl-num">1</td><td>W.D. Pa.</td><td>Apr 21, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td><span class="svr-tbl-unknown">Not disclosed</span></td></tr>
                    <tr><td>Arizona Student Doe 3 v. Trump</td><td class="svr-tbl-num">13</td><td>D. Ariz.</td><td>Apr 21, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>Arizona (U. of Arizona / ASU area)</td></tr>
                    <tr><td>Doe 1 v. Noem</td><td class="svr-tbl-num">4</td><td>S.D. Iowa</td><td>Apr 21, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>University of Iowa</td></tr>
                    <tr><td>Doe 1 v. Noem</td><td class="svr-tbl-num">6</td><td>D.N.J.</td><td>Apr 22, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>Rutgers University</td></tr>
                    <tr><td>Doe v. Noem</td><td class="svr-tbl-num">1</td><td>N.D. Ill.</td><td>Apr 22, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>Trine University</td></tr>
                    <tr><td>J.C. v. Noem</td><td class="svr-tbl-num">12</td><td>N.D. Cal.</td><td>Apr 22, 2025</td><td><span class="svr-tbl-unknown">No ruling</span></td><td>Various (N.D. Cal.)</td></tr>
                    <tr><td>Kashikov v. Noem</td><td class="svr-tbl-num">1</td><td>D. Alaska</td><td>Apr 23, 2025</td><td><span class="svr-tbl-yes">Status restored</span></td><td>U. of Alaska Anchorage</td></tr>
                    <tr><td>Du v. DHS</td><td class="svr-tbl-num">4</td><td>D. Conn.</td><td>Apr 24, 2025</td><td><span class="svr-tbl-yes">TRO → injunction</span></td><td>Yale, UConn</td></tr>
                </tbody>
            </table>
            <p class="svr-tbl-empty" id="svr-lawsuits-empty" hidden>No lawsuits match your search.</p>
        </div>
        <p class="svr-tbl-foot">Reproduces <a href="https://www.insidehighered.com/news/global/international-students-us/2025/04/07/where-students-have-had-their-visas-revoked" target="_blank" rel="noopener">Inside Higher Ed's SEVIS-lawsuits database</a> (Johanna Alonso), based on court filings as of midday April 23, 2025; the final rows are suits filed on or just after that date. "Outcome" reflects each case's status at that snapshot — so suits later resolved in the students' favor may still read "no ruling" — and "Not disclosed" marks plaintiffs or schools that filed anonymously. Case captions are abbreviated.</p>
        <figure class="svr-credit">
            <a class="svr-credit-logo" href="https://www.insidehighered.com/news/global/international-students-us/2025/04/07/where-students-have-had-their-visas-revoked" target="_blank" rel="noopener">
                <img src="{{ asset('images/inside-higher-ed-logo.png') }}" alt="Inside Higher Ed" loading="lazy" width="150" height="74">
            </a>
            <figcaption class="svr-credit-cap">Source: Johanna Alonso / Inside Higher Ed</figcaption>
        </figure>
        <figure class="svr-credit">
            <a class="svr-credit-logo" href="https://images.squarespace-cdn.com/content/v1/67e1a4df85201d775809fd02/680f2e04-8ac2-4985-9efa-d8ff5d2654c1/We+are+higher+ed+red+Logo.png?format=1500w" target="_blank" rel="noopener">
                <img src="{{ asset('images/we-are-higher-ed-logo.webp') }}" alt="We Are Higher Ed" loading="lazy" width="420" height="281">
            </a>
            <figcaption class="svr-credit-cap">Source: We Are Higher Ed</figcaption>
        </figure>
    </div>

    <hr class="svr-divider">

    {{-- ==================== METHODOLOGY & SOURCES ==================== --}}
    <div class="svr-wrap svr-section">
        <div class="svr-eyebrow">Methodology &amp; sources</div>
        <h2 class="svr-h2">How this page was assembled</h2>
        <div class="svr-note">
            <p><strong>About this page.</strong> NPPC did not conduct the reporting described here. This page <strong>compiles and cites</strong> the published work of news organizations, advocacy and legal groups, and court filings, focused on 2025. Figures for visa revocations and SEVIS terminations were a moving target; each number is paired with its source and date and counts a slightly different thing.</p>
            <p>The foreign-policy deportation power and the database-driven terminations were both still being litigated as this page was written, and outcomes in individual cases have continued to change. Where a detail could not be independently confirmed, it is attributed to the outlet that reported it. To correct or add a case, <a href="/contact">get in touch</a>.</p>
        </div>
        <ul class="svr-list">
            <li><a href="https://www.cnn.com/2025/04/17/us/university-international-student-visas-revoked" target="_blank" rel="noopener">CNN — More than 1,000 international students have had visas or legal status revoked</a><span class="svr-src">April 2025</span></li>
            <li><a href="https://www.insidehighered.com/news/global/international-students-us/2025/04/25/ice-reverses-course-sevis-terminations" target="_blank" rel="noopener">Inside Higher Ed — ICE reverses course on SEVIS terminations</a><span class="svr-src">April 2025</span></li>
            <li><a href="https://knightcolumbia.org/cases/aaup-v-rubio" target="_blank" rel="noopener">Knight First Amendment Institute — AAUP v. Rubio (ideological-deportation challenge)</a><span class="svr-src">Filed March 2025</span></li>
            <li><a href="https://apnews.com/hub/immigration" target="_blank" rel="noopener">Associated Press — Reporting on visa revocations and the NCIC student name-check</a><span class="svr-src">Spring 2025</span></li>
            <li><a href="https://www.aclu.org" target="_blank" rel="noopener">ACLU — Litigation over the detention of student activists</a><span class="svr-src">2025</span></li>
            <li><a href="https://www.presidentsalliance.org" target="_blank" rel="noopener">Presidents' Alliance on Higher Education and Immigration — SEVIS termination tracking</a><span class="svr-src">May 2025</span></li>
        </ul>
    </div>

    {{-- ==================== FOOTER CTA ==================== --}}
    <div class="svr-foot">
        <h2>People are easiest to remove when no one is watching.</h2>
        <p>Documentation, naming, and sustained pressure protect those the government would rather move in the dark. Report a case, or get involved.</p>
        <div class="svr-btns">
            <a class="svr-btn svr-btn-light" href="/contact">Report a case</a>
            <a class="svr-btn svr-btn-ghost" href="/volunteer">Get involved</a>
        </div>
    </div>

</div>

<script>
// Client-side filter for the affected-institutions table: match the typed
// query against the institution name and state, hide non-matching rows, and
// show an empty-state message when nothing matches.
function svrFilterInstitutions(q) {
    q = (q || '').trim().toLowerCase();
    var table = document.getElementById('svr-inst-table');
    if (!table) return;
    var rows = table.tBodies[0] ? table.tBodies[0].rows : [];
    var shown = 0;
    for (var i = 0; i < rows.length; i++) {
        var cells = rows[i].cells;
        var hay = ((cells[0] ? cells[0].textContent : '') + ' ' + (cells[1] ? cells[1].textContent : '')).toLowerCase();
        var match = q === '' || hay.indexOf(q) !== -1;
        rows[i].style.display = match ? '' : 'none';
        if (match) shown++;
    }
    var empty = document.getElementById('svr-inst-empty');
    if (empty) empty.hidden = shown !== 0;
}
</script>

<script>
// Client-side filter for the SEVIS-lawsuits table: match the typed query
// against every cell (case name, court, school…), hide non-matching rows,
// and show an empty-state message when nothing matches.
function svrFilterLawsuits(q) {
    q = (q || '').trim().toLowerCase();
    var table = document.getElementById('svr-lawsuits-table');
    if (!table) return;
    var rows = table.tBodies[0] ? table.tBodies[0].rows : [];
    var shown = 0;
    for (var i = 0; i < rows.length; i++) {
        var match = q === '' || rows[i].textContent.toLowerCase().indexOf(q) !== -1;
        rows[i].style.display = match ? '' : 'none';
        if (match) shown++;
    }
    var empty = document.getElementById('svr-lawsuits-empty');
    if (empty) empty.hidden = shown !== 0;
}
</script>

{{-- Native interactive map — self-hosted D3 (geoAlbersUsa). No third-party
     map service or token: the US basemap is a vendored TopoJSON and the
     points come from the synced institutions snapshot, rendered server-side
     into the script below. --}}
<script src="{{ asset('js/d3.min.js') }}"></script>
<script src="{{ asset('js/topojson-client.min.js') }}"></script>
<script>
(function () {
    var POINTS = @json($mapPoints ?? []);
    var canvas = document.getElementById('svr-map-canvas');
    if (!canvas) return;

    function showFallback() {
        var fb = document.getElementById('svr-map-fallback');
        if (fb) fb.hidden = false;
        canvas.style.display = 'none';
    }
    // Need D3 + TopoJSON + at least one point, else fall back to the list.
    if (typeof d3 === 'undefined' || typeof topojson === 'undefined' || !POINTS.length) {
        showFallback();
        return;
    }

    var tip = document.createElement('div');
    tip.className = 'svr-map-tip';
    // Inline styles so the page's global CSS can't override the tooltip
    // (the same override that turned the dots black hides this box's styling).
    tip.style.cssText = 'position:absolute;pointer-events:none;z-index:5;'
        + 'background:#16161c;color:#fff;border:1px solid rgba(255,255,255,.14);'
        + 'border-radius:8px;padding:9px 12px;font-size:12px;line-height:1.4;'
        + 'max-width:240px;opacity:0;transition:opacity .12s;'
        + 'transform:translate(-50%, calc(-100% - 12px));box-shadow:0 6px 20px rgba(0,0,0,.5);';
    canvas.appendChild(tip);

    d3.json('{{ asset('images/us-states-10m.json') }}').then(function (us) {
        var statesFc = topojson.feature(us, us.objects.states);
        var W = canvas.clientWidth || 900;
        var H = canvas.clientHeight || 560;

        var projection = d3.geoAlbersUsa().fitSize([W, H], statesFc);
        var path = d3.geoPath(projection);

        var svg = d3.select(canvas).append('svg')
            .attr('viewBox', '0 0 ' + W + ' ' + H)
            .attr('preserveAspectRatio', 'xMidYMid meet');
        var g = svg.append('g');

        // State outlines. Paint via presentation attributes (not just the
        // CSS class) so a global SVG rule can't override the fill and hide
        // the map or paint over the dots.
        g.append('g').selectAll('path')
            .data(statesFc.features)
            .join('path')
            .attr('class', 'svr-state')
            .attr('fill', '#15151b')
            .attr('stroke', 'rgba(255,255,255,.28)')
            .attr('stroke-width', 0.6)
            // Keep the border a constant on-screen width when zoomed in, so it
            // doesn't fatten to a thick grey glow at high zoom (the CSS
            // vector-effect rule is overridden, so set it as an attribute).
            .attr('vector-effect', 'non-scaling-stroke')
            .attr('d', path);

        // Project points once; keep only those the projection can place
        // (geoAlbersUsa returns null for coords outside the US frame).
        var placed = POINTS.map(function (p) {
            var xy = projection([p.lng, p.lat]);
            return xy ? { p: p, x: xy[0], y: xy[1] } : null;
        }).filter(Boolean);

        var dots = g.append('g').selectAll('circle')
            .data(placed)
            .join('circle')
            .attr('class', 'svr-dot')
            .attr('cx', function (d) { return d.x; })
            .attr('cy', function (d) { return d.y; })
            .attr('r', 3.5)
            // Set paint as presentation attributes so the dots render even if
            // the page's CSS for .svr-dot is overridden by a global SVG rule.
            .attr('fill', '#5660fe')
            .attr('stroke', '#ffffff')
            .attr('stroke-width', 1.2)
            // Constant on-screen stroke width at any zoom (matches the borders).
            .attr('vector-effect', 'non-scaling-stroke')
            .style('cursor', 'pointer')
            .attr('data-state', function (d) { return d.p.state || ''; });

        dots.on('mousemove', function (event, d) {
            var aff = (d.p.affected === null || d.p.affected === undefined || d.p.affected === 'unknown')
                ? 'unknown' : Number(d.p.affected).toLocaleString();
            tip.innerHTML = '<div style="font-weight:800;font-size:13px;color:#fff;margin:0 0 2px;">' + d.p.name + '</div>'
                + '<div style="font-size:12px;color:rgba(255,255,255,.65);">' + (d.p.state || '') + ' · affected: <b style="color:#aab0ff;">' + aff + '</b></div>';
            var r = canvas.getBoundingClientRect();
            tip.style.left = (event.clientX - r.left) + 'px';
            tip.style.top = (event.clientY - r.top) + 'px';
            tip.style.opacity = '1';
        }).on('mouseleave', function () { tip.style.opacity = '0'; });

        // Zoom / pan, with a programmatic zoom-to-bounding-box for the filter.
        var zoom = d3.zoom().scaleExtent([1, 32]).on('zoom', function (event) {
            g.attr('transform', event.transform);
            g.selectAll('.svr-dot').attr('r', 3.5 / event.transform.k);
        });
        svg.call(zoom);

        function resetZoom() {
            svg.transition().duration(700).call(zoom.transform, d3.zoomIdentity);
        }
        function zoomToState(state) {
            var feat = statesFc.features.find(function (f) { return f.properties.name === state; });
            if (!feat) { resetZoom(); return; }
            var b = path.bounds(feat);
            var dx = b[1][0] - b[0][0], dy = b[1][1] - b[0][1];
            var x = (b[0][0] + b[1][0]) / 2, y = (b[0][1] + b[1][1]) / 2;
            // Fill ~115% of the viewport (state slightly overflows the frame)
            // so the selected state zooms in close, capped at the zoom max.
            var scale = Math.min(32, 1.15 / Math.max(dx / W, dy / H));
            var translate = [W / 2 - scale * x, H / 2 - scale * y];
            svg.transition().duration(800).call(
                zoom.transform,
                d3.zoomIdentity.translate(translate[0], translate[1]).scale(scale)
            );
        }

        // Wire up the "Select state" dropdown: dim non-matching dots + zoom.
        window.svrFilterMap = function (state) {
            g.selectAll('.svr-dot')
                .style('opacity', function (d) {
                    return (!state || d.p.state === state) ? 0.9 : 0.08;
                });
            if (state) { zoomToState(state); } else { resetZoom(); }
        };
    }).catch(function () { showFallback(); });
})();
</script>
@endsection
