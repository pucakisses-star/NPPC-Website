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
    body.page-student-visa-revocations-and-ice-arrests { background: #0b0b0d; }

    .svr { background: #0b0b0d; color: rgba(255,255,255,.82); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
    .svr *, .svr *::before, .svr *::after { box-sizing: border-box; }
    .svr a { color: #8b92ff; }
    .svr a:hover { color: #fff; }

    /* ---- layout primitives ---- */
    .svr-wrap { max-width: 880px; margin: 0 auto; padding: 0 24px; }
    .svr-wide { max-width: 1120px; margin: 0 auto; padding: 0 24px; }
    .svr-section { padding: 52px 0; }
    .svr-section--tight { padding: 30px 0; }
    .svr-divider { border: 0; border-top: 1px solid rgba(255,255,255,.10); margin: 0; }

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
        background: radial-gradient(circle at 1px 1px, rgba(255,255,255,.06) 1px, transparent 0) 0 0 / 22px 22px, #101014; }
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
    .svr-map-canvas { position: relative; width: 100%; height: clamp(420px, 62vh, 640px); margin: 0 0 22px; background: #0b0b0d; border: 1px solid rgba(255,255,255,.12); border-radius: 10px; overflow: hidden; }
    .svr-pop-name { font-weight: 800; font-size: 14px; color: #fff; margin: 0 0 2px; }
    .svr-pop-meta { font-size: 12px; color: rgba(255,255,255,.6); }
    .svr-pop-meta b { color: #aab0ff; }
    /* ---- native institutions table ---- */
    .svr-tbl-total { font-size: 1.35rem; font-weight: 800; color: #fff; margin: 6px 0 14px; }
    .svr-tbl-total span { font-weight: 600; font-size: 1rem; color: rgba(255,255,255,.55); }
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
                    <input type="text" class="svr-tbl-search" id="svr-inst-search" placeholder="Search institutions or states…" onkeyup="svrFilterInstitutions(this.value)" aria-label="Search affected institutions">
                    <div class="svr-tbl-wrap">
                        <table class="svr-tbl" id="svr-inst-table">
                            <thead>
                                <tr><th>Institution</th><th>State</th><th class="svr-tbl-num">Affected people</th></tr>
                            </thead>
                            <tbody>
                                @foreach($institutions as $inst)
                                    <tr>
                                        <td>
                                            @if(! empty($inst['website']))
                                                <a href="{{ $inst['website'] }}" target="_blank" rel="noopener">{{ $inst['name'] }}</a>
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

        </div>
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
        var zoom = d3.zoom().scaleExtent([1, 12]).on('zoom', function (event) {
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
            // so the selected state zooms in close, capped at 16x.
            var scale = Math.min(16, 1.15 / Math.max(dx / W, dy / H));
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
