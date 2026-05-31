@extends('app')

@section('title', 'Political Prisoner Tracker — Live Database Dashboard | NPPC')

@section('head')
<meta name="description" content="A live command-center dashboard of the National Political Prisoner Coalition database — who is imprisoned, where they are held, and how the cases break down by status, movement, era, and state.">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
<style>
    /* ============================================================
       Political Prisoner Tracker — live "command center" dashboard
       over the prisoner database. Amber-on-black intelligence-desk
       aesthetic. All classes are scoped with the ppd- prefix.
       ============================================================ */

    body.page-dashboard { background: #0a0a0b; }
    body.page-dashboard main.container,
    body.page-dashboard .container { max-width: none !important; width: 100% !important; padding: 0 !important; }

    .ppd {
        --amber: #e0a82e;
        --line: rgba(255,255,255,0.09);
        --ink: #ece9e2;
        --mut: rgba(236,233,226,0.46);
        --panel: #0e0e10;
        color: var(--ink);
        background: #0a0a0b;
        font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        -webkit-font-smoothing: antialiased;
        font-variant-numeric: tabular-nums;
    }
    .ppd a { color: inherit; text-decoration: none; }
    .ppd-label { font-size: 10.5px; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase; color: var(--mut); }

    /* ---- top utility bar ---- */
    .ppd-bar { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 11px 20px; border-bottom: 1px solid var(--line); background: #0c0c0e; }
    .ppd-brand { display: inline-flex; align-items: center; gap: 9px; font-size: 11px; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase; color: var(--amber); white-space: nowrap; }
    .ppd-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--amber); flex: 0 0 auto; }
    .ppd-bar-title { font-size: 12px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: #fff; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ppd-bar-live { display: inline-flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700; letter-spacing: 0.06em; color: var(--mut); white-space: nowrap; }
    .ppd-live-dot { width: 8px; height: 8px; border-radius: 50%; background: #3fd07f; box-shadow: 0 0 0 0 rgba(63,208,127,0.6); animation: ppdpulse 2s infinite; }
    @@keyframes ppdpulse { 0% { box-shadow: 0 0 0 0 rgba(63,208,127,0.55); } 70% { box-shadow: 0 0 0 8px rgba(63,208,127,0); } 100% { box-shadow: 0 0 0 0 rgba(63,208,127,0); } }

    /* ---- stat strip ---- */
    .ppd-strip { display: flex; align-items: center; flex-wrap: wrap; gap: 10px 30px; padding: 16px 22px; border-bottom: 1px solid var(--line); background: #0b0b0d; }
    .ppd-total { display: flex; align-items: baseline; gap: 12px; padding-right: 28px; border-right: 1px solid var(--line); }
    .ppd-total-num { font-size: 2.3rem; font-weight: 800; line-height: 1; color: #fff; letter-spacing: -0.02em; }
    .ppd-breaks { display: flex; align-items: center; flex-wrap: wrap; gap: 8px 26px; }
    .ppd-bk { display: inline-flex; align-items: center; gap: 9px; }
    .ppd-bk-dot { width: 9px; height: 9px; border-radius: 50%; flex: 0 0 auto; }
    .ppd-bk-lab { font-size: 10.5px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; color: var(--mut); }
    .ppd-bk-num { font-size: 1.15rem; font-weight: 800; color: #fff; }

    /* ---- breaking / recent ticker ---- */
    .ppd-ticker { display: flex; align-items: stretch; border-bottom: 1px solid var(--line); background: #0c0c0e; height: 38px; overflow: hidden; }
    .ppd-ticker-tag { display: inline-flex; align-items: center; gap: 8px; padding: 0 16px; background: var(--amber); color: #19140a; font-size: 10.5px; font-weight: 900; letter-spacing: 0.12em; text-transform: uppercase; white-space: nowrap; flex: 0 0 auto; }
    .ppd-ticker-view { position: relative; flex: 1 1 auto; overflow: hidden; }
    .ppd-ticker-track { position: absolute; top: 0; left: 0; height: 100%; display: inline-flex; align-items: center; white-space: nowrap; animation: ppdmarquee 48s linear infinite; }
    .ppd-ticker-view:hover .ppd-ticker-track { animation-play-state: paused; }
    @@keyframes ppdmarquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }
    .ppd-tk { display: inline-flex; align-items: center; gap: 9px; padding: 0 26px; font-size: 12.5px; color: rgba(236,233,226,0.82); }
    .ppd-tk::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: var(--amber); }
    .ppd-tk b { color: #fff; font-weight: 700; }

    /* ---- main body: feed + map ---- */
    .ppd-body { display: grid; grid-template-columns: 372px 1fr; height: min(74vh, 760px); min-height: 480px; border-bottom: 1px solid var(--line); }
    .ppd-feed { border-right: 1px solid var(--line); overflow-y: auto; background: #0b0b0d; }
    .ppd-feed::-webkit-scrollbar { width: 9px; }
    .ppd-feed::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 9px; }
    .ppd-feed-head { position: sticky; top: 0; z-index: 2; display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 13px 18px; background: #0c0c0e; border-bottom: 1px solid var(--line); }
    .ppd-feed-item { display: block; padding: 13px 18px; border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.12s ease; }
    .ppd-feed-item:hover { background: rgba(224,168,46,0.06); }
    .ppd-feed-name { font-size: 14px; font-weight: 700; color: #fff; line-height: 1.25; }
    .ppd-feed-sub { display: flex; align-items: center; gap: 10px; margin-top: 7px; }
    .ppd-tagchip { font-size: 9.5px; font-weight: 900; letter-spacing: 0.06em; text-transform: uppercase; padding: 2px 7px; border-radius: 3px; color: #0a0a0b; }
    .ppd-feed-date { font-size: 11px; color: var(--mut); letter-spacing: 0.02em; }
    .ppd-feed-empty { padding: 24px 18px; color: var(--mut); font-style: italic; font-size: 13px; }

    /* ---- map ---- */
    .ppd-mapwrap { position: relative; background: #07090c; }
    #ppd-map { position: absolute; inset: 0; height: 100%; width: 100%; background: #07090c; }
    .ppd-map-empty { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: var(--mut); font-size: 14px; text-align: center; padding: 24px; z-index: 500; }
    .leaflet-container { background: #07090c; font: inherit; }
    .leaflet-popup-content-wrapper, .leaflet-popup-tip { background: #15151a; color: var(--ink); border: 1px solid var(--line); box-shadow: 0 8px 28px rgba(0,0,0,0.5); }
    .leaflet-popup-content-wrapper { border-radius: 8px; }
    .leaflet-popup-content { margin: 10px 13px; font-size: 13px; line-height: 1.45; }
    .leaflet-popup-content b { color: #fff; }
    .leaflet-popup-content .ppd-pop-meta { color: var(--mut); }
    .leaflet-bar a { background: #15151a; color: var(--ink); border-color: var(--line); }
    .leaflet-bar a:hover { background: #20202a; }
    .leaflet-control-attribution { background: rgba(0,0,0,0.55) !important; color: rgba(255,255,255,0.4) !important; }
    .leaflet-control-attribution a { color: rgba(255,255,255,0.55) !important; }

    .ppd-legend { position: absolute; top: 14px; right: 14px; z-index: 600; background: rgba(12,12,14,0.92); border: 1px solid var(--line); border-radius: 8px; padding: 12px 13px 10px; backdrop-filter: blur(4px); min-width: 150px; }
    .ppd-legend-h { font-size: 10px; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; color: var(--mut); margin-bottom: 9px; }
    .ppd-leg { display: flex; align-items: center; gap: 9px; width: 100%; background: none; border: 0; padding: 5px 4px; border-radius: 5px; cursor: pointer; font: inherit; color: var(--ink); text-align: left; transition: background 0.12s ease; }
    .ppd-leg:hover { background: rgba(255,255,255,0.05); }
    .ppd-leg-dot { width: 10px; height: 10px; border-radius: 50%; flex: 0 0 auto; }
    .ppd-leg-lab { font-size: 12px; flex: 1 1 auto; }
    .ppd-leg-n { font-size: 11px; font-weight: 700; color: var(--mut); font-variant-numeric: tabular-nums; }
    .ppd-legend.is-filtered .ppd-leg:not(.is-active) { opacity: 0.42; }

    /* ---- bottom breakdown strip ---- */
    .ppd-breakdowns { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1px; background: var(--line); }
    .ppd-bd { background: #0b0b0d; padding: 18px 20px 20px; }
    .ppd-bd-h { font-size: 10.5px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; color: var(--mut); margin: 0 0 14px; }
    .ppd-row { display: grid; grid-template-columns: 1fr 38px; align-items: center; gap: 10px; margin-bottom: 9px; }
    .ppd-row:last-child { margin-bottom: 0; }
    .ppd-row-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .ppd-row-lab { font-size: 12px; color: rgba(236,233,226,0.8); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ppd-row-val { font-size: 12px; font-weight: 700; color: #fff; text-align: right; }
    .ppd-track { grid-column: 1 / -1; height: 5px; border-radius: 999px; background: rgba(255,255,255,0.07); overflow: hidden; margin-top: -2px; }
    .ppd-fill { display: block; height: 100%; border-radius: 999px; background: var(--amber); min-width: 2px; }
    .ppd-empty { font-size: 12px; color: var(--mut); font-style: italic; margin: 0; }

    .ppd-foot { display: flex; flex-wrap: wrap; gap: 24px; padding: 18px 22px 40px; }
    .ppd-foot-link { font-size: 13px; font-weight: 700; letter-spacing: 0.02em; color: var(--amber); }
    .ppd-foot-link:hover { color: #fff; }

    /* ---- responsive ---- */
    @@media (max-width: 900px) {
        .ppd-bar-title { display: none; }
        .ppd-body { grid-template-columns: 1fr; height: auto; }
        .ppd-feed { max-height: 360px; border-right: 0; border-bottom: 1px solid var(--line); }
        .ppd-mapwrap { height: 420px; }
        #ppd-map { position: absolute; }
        .ppd-breakdowns { grid-template-columns: 1fr 1fr; }
    }
    @@media (max-width: 540px) {
        .ppd-strip { gap: 10px 16px; }
        .ppd-total-num { font-size: 1.9rem; }
        .ppd-breakdowns { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('body')
@php
    $prisoners = \App\Models\Prisoner::query()->orderByDesc('created_at')->get();

    $total     = $prisoners->count();
    $inCustody = $prisoners->where('in_custody', true)->count();
    $released  = $prisoners->where('released', true)->count();
    $inExile   = $prisoners->where('currently_in_exile', true)->count();
    $deceased  = $prisoners->filter(fn ($p) => $p->death_date)->count();
    $awaiting  = $prisoners->where('awaiting_trial', true)->count();
    $totalFacilities = \App\Models\Institution::count();

    // status key (most salient first) + label + colour, reused everywhere
    $statusKey = function ($p) {
        if ($p->death_date)         return 'deceased';
        if ($p->in_custody)         return 'custody';
        if ($p->currently_in_exile) return 'exile';
        if ($p->awaiting_trial)     return 'awaiting';
        if ($p->released)           return 'released';
        return 'other';
    };
    $statusMeta = [
        'custody'  => ['In custody',     '#e5484d'],
        'awaiting' => ['Awaiting trial', '#4c8dff'],
        'exile'    => ['In exile',       '#e0a82e'],
        'released' => ['Released',        '#46c08d'],
        'deceased' => ['Deceased',        '#d7d3ca'],
        'other'    => ['Documented',      '#9aa0a6'],
    ];

    // strip breakdown counters
    $breaks = [
        ['custody',  $inCustody],
        ['awaiting', $awaiting],
        ['exile',    $inExile],
        ['released', $released],
        ['deceased', $deceased],
    ];

    // breakdown panels
    $byState = $prisoners->filter(fn ($p) => filled($p->state))->groupBy('state')->map->count()->sortDesc()->take(8);
    $byEra   = $prisoners->filter(fn ($p) => filled($p->era))->groupBy('era')->map->count()->sortDesc()->take(8);
    $movementCounts = [];
    foreach ($prisoners as $p) {
        $tags = array_merge((array) ($p->ideologies ?? []), (array) ($p->affiliation ?? []));
        foreach (array_unique(array_filter(array_map('trim', $tags))) as $tag) {
            $movementCounts[$tag] = ($movementCounts[$tag] ?? 0) + 1;
        }
    }
    arsort($movementCounts);
    $byMovement = array_slice($movementCounts, 0, 8, true);
    $maxState    = $byState->max() ?: 1;
    $maxEra      = $byEra->max() ?: 1;
    $maxMovement = $byMovement ? max($byMovement) : 1;
    $statusMax   = collect($breaks)->max(fn ($r) => $r[1]) ?: 1;
    $statusBars  = [
        ['custody',  'In custody',     $inCustody],
        ['awaiting', 'Awaiting trial', $awaiting],
        ['exile',    'In exile',       $inExile],
        ['released', 'Released',       $released],
        ['deceased', 'Deceased',       $deceased],
    ];

    // intelligence feed + ticker
    $feed   = $prisoners->take(40);
    $ticker = $prisoners->take(14);

    // map points: prisoners with coordinates, coloured by status
    $mapPoints = $prisoners
        ->filter(fn ($p) => $p->lat !== null && $p->lng !== null)
        ->map(function ($p) use ($statusKey, $statusMeta) {
            $sk = $statusKey($p);
            return [
                'lat'    => (float) $p->lat,
                'lng'    => (float) $p->lng,
                'name'   => $p->name,
                'status' => $sk,
                'meta'   => collect([$statusMeta[$sk][0] ?? null, $p->state])->filter()->join(' · '),
                'url'    => $p->url,
            ];
        })->values();

    // fallback for the map if no prisoner has coordinates: facilities
    $mapFacilities = \App\Models\Institution::query()
        ->whereNotNull('lat')->whereNotNull('lng')->withCount('cases')->get()
        ->map(fn ($i) => [
            'lat'   => (float) $i->lat,
            'lng'   => (float) $i->lng,
            'name'  => $i->name,
            'meta'  => collect([$i->city, $i->state])->filter()->join(', '),
            'count' => (int) $i->cases_count,
        ])->values();

    $statusColors = collect($statusMeta)->map(fn ($m) => $m[1]); // key => colour
@endphp
<div class="ppd">

    {{-- ==================== TOP UTILITY BAR ==================== --}}
    <div class="ppd-bar">
        <span class="ppd-brand"><span class="ppd-dot"></span> National Political Prisoner Coalition</span>
        <span class="ppd-bar-title">Political Prisoner Database · Live Tracker</span>
        <span class="ppd-bar-live"><span class="ppd-live-dot"></span> LIVE · {{ now()->format('M j, Y · g:i A') }}</span>
    </div>

    {{-- ==================== STAT STRIP ==================== --}}
    <div class="ppd-strip">
        <div class="ppd-total">
            <span class="ppd-label">Documented</span>
            <span class="ppd-total-num" data-count="{{ $total }}">{{ number_format($total) }}</span>
        </div>
        <div class="ppd-breaks">
            @foreach ($breaks as [$key, $val])
                <span class="ppd-bk">
                    <span class="ppd-bk-dot" style="background: {{ $statusMeta[$key][1] }}"></span>
                    <span class="ppd-bk-lab">{{ $statusMeta[$key][0] }}</span>
                    <span class="ppd-bk-num" data-count="{{ $val }}">{{ number_format($val) }}</span>
                </span>
            @endforeach
            <span class="ppd-bk">
                <span class="ppd-bk-dot" style="background: #6b7280"></span>
                <span class="ppd-bk-lab">Facilities</span>
                <span class="ppd-bk-num" data-count="{{ $totalFacilities }}">{{ number_format($totalFacilities) }}</span>
            </span>
        </div>
    </div>

    {{-- ==================== RECENT TICKER ==================== --}}
    @if ($ticker->isNotEmpty())
    <div class="ppd-ticker">
        <span class="ppd-ticker-tag"><span class="ppd-dot" style="background:#19140a"></span> Recently documented</span>
        <div class="ppd-ticker-view">
            <div class="ppd-ticker-track">
                @foreach ($ticker as $p)
                    <span class="ppd-tk"><b>{{ $p->name }}</b> — {{ $statusMeta[$statusKey($p)][0] }}</span>
                @endforeach
                @foreach ($ticker as $p)
                    <span class="ppd-tk"><b>{{ $p->name }}</b> — {{ $statusMeta[$statusKey($p)][0] }}</span>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ==================== BODY: FEED + MAP ==================== --}}
    <div class="ppd-body">
        <aside class="ppd-feed">
            <div class="ppd-feed-head">
                <span class="ppd-label">Intelligence feed · Recent cases</span>
                <span class="ppd-label">{{ number_format($total) }}</span>
            </div>
            @forelse ($feed as $p)
                @php $sk = $statusKey($p); @endphp
                <a class="ppd-feed-item" href="{{ $p->url }}" data-status="{{ $sk }}">
                    <span class="ppd-feed-name">{{ $p->name }}</span>
                    <span class="ppd-feed-sub">
                        <span class="ppd-tagchip" style="background: {{ $statusMeta[$sk][1] }}">{{ $statusMeta[$sk][0] }}</span>
                        <span class="ppd-feed-date">{{ collect([$p->state, optional($p->created_at)->format('M j, Y')])->filter()->join(' · ') }}</span>
                    </span>
                </a>
            @empty
                <div class="ppd-feed-empty">No cases documented yet.</div>
            @endforelse
        </aside>

        <div class="ppd-mapwrap">
            <div id="ppd-map"></div>
            <div class="ppd-legend" id="ppd-legend">
                <div class="ppd-legend-h">Status</div>
                @foreach ($statusBars as [$key, $label, $val])
                    <button type="button" class="ppd-leg" data-filter="{{ $key }}">
                        <span class="ppd-leg-dot" style="background: {{ $statusMeta[$key][1] }}"></span>
                        <span class="ppd-leg-lab">{{ $label }}</span>
                        <span class="ppd-leg-n">{{ number_format($val) }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ==================== BOTTOM BREAKDOWNS ==================== --}}
    <div class="ppd-breakdowns">
        <div class="ppd-bd">
            <h3 class="ppd-bd-h">By status</h3>
            @foreach ($statusBars as [$key, $label, $val])
                <div class="ppd-row">
                    <div class="ppd-row-top"><span class="ppd-row-lab">{{ $label }}</span><span class="ppd-row-val">{{ number_format($val) }}</span></div>
                    <span class="ppd-track"><span class="ppd-fill" style="width: {{ round($val / $statusMax * 100) }}%; background: {{ $statusMeta[$key][1] }}"></span></span>
                </div>
            @endforeach
        </div>
        <div class="ppd-bd">
            <h3 class="ppd-bd-h">Top movements</h3>
            @forelse ($byMovement as $name => $count)
                <div class="ppd-row">
                    <div class="ppd-row-top"><span class="ppd-row-lab" title="{{ $name }}">{{ $name }}</span><span class="ppd-row-val">{{ number_format($count) }}</span></div>
                    <span class="ppd-track"><span class="ppd-fill" style="width: {{ round($count / $maxMovement * 100) }}%"></span></span>
                </div>
            @empty
                <p class="ppd-empty">No movement data yet.</p>
            @endforelse
        </div>
        <div class="ppd-bd">
            <h3 class="ppd-bd-h">By era</h3>
            @forelse ($byEra as $name => $count)
                <div class="ppd-row">
                    <div class="ppd-row-top"><span class="ppd-row-lab" title="{{ $name }}">{{ $name }}</span><span class="ppd-row-val">{{ number_format($count) }}</span></div>
                    <span class="ppd-track"><span class="ppd-fill" style="width: {{ round($count / $maxEra * 100) }}%"></span></span>
                </div>
            @empty
                <p class="ppd-empty">No era data yet.</p>
            @endforelse
        </div>
        <div class="ppd-bd">
            <h3 class="ppd-bd-h">Top states</h3>
            @forelse ($byState as $name => $count)
                <div class="ppd-row">
                    <div class="ppd-row-top"><span class="ppd-row-lab" title="{{ $name }}">{{ $name }}</span><span class="ppd-row-val">{{ number_format($count) }}</span></div>
                    <span class="ppd-track"><span class="ppd-fill" style="width: {{ round($count / $maxState * 100) }}%"></span></span>
                </div>
            @empty
                <p class="ppd-empty">No state data yet.</p>
            @endforelse
        </div>
    </div>

    <div class="ppd-foot">
        <a class="ppd-foot-link" href="/map">Open the full interactive map →</a>
        <a class="ppd-foot-link" href="/feature-political-prisoner-cost">See the cost-of-incarceration tracker →</a>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function esc(s) {
            return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
            });
        }

        // ---- count-up on the stat strip ----
        document.querySelectorAll('[data-count]').forEach(function (el) {
            var target = parseInt(el.getAttribute('data-count'), 10) || 0;
            if (target <= 0) { el.textContent = '0'; return; }
            var dur = 850, start = null;
            function step(ts) {
                if (start === null) start = ts;
                var t = Math.min(1, (ts - start) / dur);
                el.textContent = Math.round(target * (0.5 - Math.cos(Math.PI * t) / 2)).toLocaleString();
                if (t < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        });

        // ---- map (Leaflet + Carto dark) ----
        var statusColors = @json($statusColors);
        var prisonerPts = @json($mapPoints);
        var facilityPts = @json($mapFacilities);
        var mapEl = document.getElementById('ppd-map');

        if (!mapEl || !window.L) { if (mapEl) mapEl.innerHTML = '<div class="ppd-map-empty">Map library unavailable.</div>'; return; }

        var useFacilities = prisonerPts.length === 0;
        var points = useFacilities ? facilityPts : prisonerPts;
        if (!points.length) { mapEl.innerHTML = '<div class="ppd-map-empty">No mapped coordinates recorded yet.</div>'; }

        var map = L.map('ppd-map', { zoomControl: true, scrollWheelZoom: false, attributionControl: true }).setView([39, -97], 4);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            subdomains: 'abcd', maxZoom: 19,
            attribution: '&copy; OpenStreetMap &copy; CARTO'
        }).addTo(map);
        setTimeout(function () { map.invalidateSize(); }, 200);

        var markers = [];
        var latlngs = [];
        points.forEach(function (p) {
            var color = useFacilities ? '#e0a82e' : (statusColors[p.status] || '#9aa0a6');
            var radius = useFacilities ? Math.max(5, Math.min(20, 4 + Math.sqrt(p.count || 1) * 3)) : 6;
            var m = L.circleMarker([p.lat, p.lng], {
                radius: radius, color: color, weight: 1.5, opacity: 0.9,
                fillColor: color, fillOpacity: 0.5
            });
            var extra = useFacilities ? ((p.count || 0) + ' case' + (p.count === 1 ? '' : 's')) : (p.meta || '');
            m.bindPopup('<b>' + esc(p.name) + '</b>' + (extra ? '<br><span class="ppd-pop-meta">' + esc(extra) + '</span>' : ''));
            m.addTo(map);
            markers.push({ marker: m, status: useFacilities ? 'other' : p.status });
            latlngs.push([p.lat, p.lng]);
        });
        if (latlngs.length) { map.fitBounds(latlngs, { padding: [42, 42], maxZoom: 7 }); }

        // ---- legend acts as a status filter (map markers + feed) ----
        var legend = document.getElementById('ppd-legend');
        var legRows = legend ? legend.querySelectorAll('.ppd-leg') : [];
        var feedItems = document.querySelectorAll('.ppd-feed-item');
        var active = null;

        function apply(f) {
            markers.forEach(function (o) {
                var on = (f === null) || (o.status === f);
                if (on) { if (!map.hasLayer(o.marker)) o.marker.addTo(map); }
                else { if (map.hasLayer(o.marker)) map.removeLayer(o.marker); }
            });
            feedItems.forEach(function (el) {
                el.style.display = (f === null || el.getAttribute('data-status') === f) ? '' : 'none';
            });
            legRows.forEach(function (r) { r.classList.toggle('is-active', r.getAttribute('data-filter') === f); });
            if (legend) legend.classList.toggle('is-filtered', f !== null);
        }

        legRows.forEach(function (row) {
            row.addEventListener('click', function () {
                var f = row.getAttribute('data-filter');
                active = (active === f) ? null : f;     // click active row again to reset
                apply(active);
            });
        });
    });
</script>
@endsection
