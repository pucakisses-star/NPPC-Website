@extends('app')

@section('title', 'Political Prisoner Tracker — Live Database Dashboard | NPPC')

@section('head')
<meta name="description" content="A live dashboard of the National Political Prisoner Coalition database — who is imprisoned, where they are held, and how the cases break down by status, movement, era, and state.">
<link href="https://api.mapbox.com/mapbox-gl-js/v3.5.1/mapbox-gl.css" rel="stylesheet">
<script src="https://api.mapbox.com/mapbox-gl-js/v3.5.1/mapbox-gl.js" defer></script>
<style>
    /* ============================================================
       Political Prisoner Tracker — live dashboard over the prisoner
       database. Full-bleed dark layout modeled on a conflict-tracker
       dashboard. All classes are scoped with the ppd- prefix so
       nothing leaks into the rest of the site.
       ============================================================ */

    /* Break the page out of the centered .container and go fully dark. */
    body.page-dashboard { background: #0b0d10; }
    body.page-dashboard main.container,
    body.page-dashboard .container { max-width: none !important; width: 100% !important; padding: 0 !important; }

    .ppd { color: #e7e9ee; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
    .ppd a { color: inherit; text-decoration: none; }
    .ppd-wrap { max-width: 1220px; margin: 0 auto; padding: 0 24px; }

    /* ---- top hero + stat band ---- */
    .ppd-top { background:
        radial-gradient(1100px 380px at 18% -10%, rgba(91,141,239,0.18), transparent 60%),
        linear-gradient(180deg, #111722 0%, #0b0d10 100%);
        border-bottom: 1px solid rgba(255,255,255,0.08); padding: 44px 0 30px; }
    .ppd-top-inner { max-width: 1220px; margin: 0 auto; padding: 0 24px; display: grid; grid-template-columns: minmax(0,1fr) auto; gap: 36px; align-items: end; }
    .ppd-live { display: inline-flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; color: #8fb4ff; }
    .ppd-live-dot { width: 8px; height: 8px; border-radius: 50%; background: #5b8def; box-shadow: 0 0 0 0 rgba(91,141,239,0.6); animation: ppdpulse 2s infinite; }
    @@keyframes ppdpulse { 0% { box-shadow: 0 0 0 0 rgba(91,141,239,0.55); } 70% { box-shadow: 0 0 0 9px rgba(91,141,239,0); } 100% { box-shadow: 0 0 0 0 rgba(91,141,239,0); } }
    .ppd-title { font-size: clamp(2.1rem, 4vw, 3.1rem); line-height: 1.04; font-weight: 800; letter-spacing: -0.02em; margin: 12px 0 12px; color: #fff; }
    .ppd-lede { font-size: 1.06rem; line-height: 1.55; color: rgba(255,255,255,0.66); max-width: 620px; margin: 0; }
    .ppd-updated { margin-top: 16px; font-size: 12px; font-weight: 600; letter-spacing: 0.03em; text-transform: uppercase; color: rgba(255,255,255,0.4); }

    .ppd-stats { display: grid; grid-template-columns: repeat(3, minmax(96px, 1fr)); gap: 14px; }
    .ppd-stat { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 16px 16px 14px; min-width: 120px; }
    .ppd-stat-num { display: block; font-size: 2rem; font-weight: 800; letter-spacing: -0.02em; color: #fff; line-height: 1; font-variant-numeric: tabular-nums; }
    .ppd-stat-label { display: block; margin-top: 7px; font-size: 11px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: rgba(255,255,255,0.5); }
    .ppd-stat--custody  .ppd-stat-num { color: #f3a13a; }
    .ppd-stat--released .ppd-stat-num { color: #46c08d; }
    .ppd-stat--exile    .ppd-stat-num { color: #b58cff; }
    .ppd-stat--deceased .ppd-stat-num { color: #aeb6c4; }

    /* ---- sections ---- */
    .ppd-section { max-width: 1220px; margin: 0 auto; padding: 38px 24px 0; }
    .ppd-section:last-of-type { padding-bottom: 64px; }
    .ppd-section-head { display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; }
    .ppd-h2 { font-size: 1.4rem; font-weight: 800; letter-spacing: -0.01em; color: #fff; margin: 0; }
    .ppd-section-note { font-size: 12px; font-weight: 600; letter-spacing: 0.03em; text-transform: uppercase; color: rgba(255,255,255,0.4); }

    /* ---- map ---- */
    .ppd-map { height: 460px; border-radius: 14px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); background: #0e1116; position: relative; }
    .ppd-map-empty { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.4); font-size: 14px; padding: 24px; text-align: center; }
    .ppd-pop .mapboxgl-popup-content { background: #161a20; color: #e7e9ee; border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; font-size: 13px; line-height: 1.4; padding: 10px 12px; }
    .ppd-pop .mapboxgl-popup-tip { border-top-color: #161a20; border-bottom-color: #161a20; }
    .ppd-pop-n { color: #8fb4ff; font-weight: 700; }

    /* ---- breakdown grid ---- */
    .ppd-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .ppd-card { background: #14171c; border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 20px 22px; }
    .ppd-card-h { font-size: 12px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; color: rgba(255,255,255,0.55); margin: 0 0 16px; }
    .ppd-bar-row { display: grid; grid-template-columns: 150px 1fr 44px; align-items: center; gap: 12px; margin-bottom: 11px; }
    .ppd-bar-row:last-child { margin-bottom: 0; }
    .ppd-bar-label { font-size: 13px; color: rgba(255,255,255,0.78); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ppd-bar-track { height: 8px; border-radius: 999px; background: rgba(255,255,255,0.07); overflow: hidden; }
    .ppd-bar-fill { display: block; height: 100%; border-radius: 999px; background: #5b8def; min-width: 2px; }
    .ppd-fill--custody  { background: #f3a13a; }
    .ppd-fill--awaiting { background: #e8c45c; }
    .ppd-fill--exile    { background: #b58cff; }
    .ppd-fill--released { background: #46c08d; }
    .ppd-fill--deceased { background: #8a93a3; }
    .ppd-bar-val { font-size: 13px; font-weight: 700; color: #fff; text-align: right; font-variant-numeric: tabular-nums; }
    .ppd-empty { font-size: 13px; color: rgba(255,255,255,0.4); font-style: italic; margin: 0; }

    /* ---- recent people ---- */
    .ppd-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .ppd-chip { font: inherit; font-size: 12px; font-weight: 700; letter-spacing: 0.02em; color: rgba(255,255,255,0.6); background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 999px; padding: 6px 13px; cursor: pointer; transition: all 0.15s ease; }
    .ppd-chip:hover { color: #fff; border-color: rgba(255,255,255,0.25); }
    .ppd-chip.is-active { color: #0b0d10; background: #e7e9ee; border-color: #e7e9ee; }
    .ppd-people { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 16px; }
    .ppd-pcard { display: block; background: #14171c; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; overflow: hidden; transition: transform 0.15s ease, border-color 0.15s ease; }
    .ppd-pcard:hover { transform: translateY(-3px); border-color: rgba(91,141,239,0.5); }
    .ppd-pcard-photo { position: relative; display: block; aspect-ratio: 1 / 1; background: #1b1f26; overflow: hidden; }
    .ppd-pcard-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .ppd-pcard-initials { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; color: rgba(255,255,255,0.28); letter-spacing: 0.04em; }
    .ppd-badge { position: absolute; left: 8px; bottom: 8px; font-size: 10px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 3px 8px; border-radius: 999px; color: #0b0d10; background: #aeb6c4; }
    .ppd-badge--custody  { background: #f3a13a; }
    .ppd-badge--awaiting { background: #e8c45c; }
    .ppd-badge--exile    { background: #b58cff; }
    .ppd-badge--released { background: #46c08d; }
    .ppd-badge--deceased { background: #aeb6c4; }
    .ppd-pcard-name { display: block; padding: 12px 14px 2px; font-size: 14px; font-weight: 700; color: #fff; }
    .ppd-pcard-meta { display: block; padding: 0 14px 14px; font-size: 12px; color: rgba(255,255,255,0.5); }

    .ppd-foot { display: flex; flex-wrap: wrap; gap: 22px; margin-top: 30px; padding-top: 22px; border-top: 1px solid rgba(255,255,255,0.08); }
    .ppd-foot-link { font-size: 14px; font-weight: 700; color: #8fb4ff; }
    .ppd-foot-link:hover { color: #fff; }

    /* ---- responsive ---- */
    @@media (max-width: 980px) {
        .ppd-top-inner { grid-template-columns: 1fr; gap: 26px; align-items: start; }
        .ppd-grid { grid-template-columns: 1fr; }
    }
    @@media (max-width: 560px) {
        .ppd-stats { grid-template-columns: 1fr 1fr; }
        .ppd-bar-row { grid-template-columns: 110px 1fr 40px; }
        .ppd-map { height: 360px; }
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

    // Facilities (institutions) that have coordinates, for the map.
    $facilities = \App\Models\Institution::query()
        ->whereNotNull('lat')->whereNotNull('lng')
        ->withCount('cases')
        ->get()
        ->map(fn ($i) => [
            'name'  => $i->name,
            'city'  => $i->city,
            'state' => $i->state,
            'lat'   => (float) $i->lat,
            'lng'   => (float) $i->lng,
            'count' => (int) $i->cases_count,
        ])->values();
    $totalFacilities = \App\Models\Institution::count();

    // Breakdowns
    $byState = $prisoners->filter(fn ($p) => filled($p->state))
        ->groupBy('state')->map->count()->sortDesc()->take(12);

    $byEra = $prisoners->filter(fn ($p) => filled($p->era))
        ->groupBy('era')->map->count()->sortDesc()->take(10);

    $movementCounts = [];
    foreach ($prisoners as $p) {
        $tags = array_merge((array) ($p->ideologies ?? []), (array) ($p->affiliation ?? []));
        foreach (array_unique(array_filter(array_map('trim', $tags))) as $tag) {
            $movementCounts[$tag] = ($movementCounts[$tag] ?? 0) + 1;
        }
    }
    arsort($movementCounts);
    $byMovement = array_slice($movementCounts, 0, 12, true);

    // Status helper (most salient state first) for badges + filtering.
    $statusKey = function ($p) {
        if ($p->death_date)         return 'deceased';
        if ($p->in_custody)         return 'custody';
        if ($p->currently_in_exile) return 'exile';
        if ($p->awaiting_trial)     return 'awaiting';
        if ($p->released)           return 'released';
        return 'other';
    };
    $statusLabels = [
        'custody'  => 'In custody',
        'awaiting' => 'Awaiting trial',
        'exile'    => 'In exile',
        'released' => 'Released',
        'deceased' => 'Deceased',
        'other'    => 'Documented',
    ];

    $recent = $prisoners->take(12);

    $statusBars = [
        ['custody',  'In custody',     $inCustody],
        ['awaiting', 'Awaiting trial', $awaiting],
        ['exile',    'In exile',       $inExile],
        ['released', 'Released',       $released],
        ['deceased', 'Deceased',       $deceased],
    ];
    $statusMax   = collect($statusBars)->max(fn ($r) => $r[2]) ?: 1;
    $maxState    = $byState->max() ?: 1;
    $maxEra      = $byEra->max() ?: 1;
    $maxMovement = $byMovement ? max($byMovement) : 1;

    $mapboxToken = config('services.mapbox.token', '');
@endphp
<div class="ppd">

    {{-- ==================== HERO + STAT BAND ==================== --}}
    <header class="ppd-top">
        <div class="ppd-top-inner">
            <div class="ppd-head">
                <span class="ppd-live"><span class="ppd-live-dot"></span> Live database</span>
                <h1 class="ppd-title">Political Prisoner Tracker</h1>
                <p class="ppd-lede">A real-time snapshot of every person documented in the National Political Prisoner Coalition database — who is imprisoned, where they are held, and how the cases break down.</p>
                <div class="ppd-updated">Reflecting the database as of {{ now()->format('M j, Y · g:i A') }}</div>
            </div>
            <div class="ppd-stats">
                <div class="ppd-stat"><span class="ppd-stat-num" data-count="{{ $total }}">{{ number_format($total) }}</span><span class="ppd-stat-label">Documented</span></div>
                <div class="ppd-stat ppd-stat--custody"><span class="ppd-stat-num" data-count="{{ $inCustody }}">{{ number_format($inCustody) }}</span><span class="ppd-stat-label">In custody</span></div>
                <div class="ppd-stat ppd-stat--released"><span class="ppd-stat-num" data-count="{{ $released }}">{{ number_format($released) }}</span><span class="ppd-stat-label">Released</span></div>
                <div class="ppd-stat ppd-stat--exile"><span class="ppd-stat-num" data-count="{{ $inExile }}">{{ number_format($inExile) }}</span><span class="ppd-stat-label">In exile</span></div>
                <div class="ppd-stat ppd-stat--deceased"><span class="ppd-stat-num" data-count="{{ $deceased }}">{{ number_format($deceased) }}</span><span class="ppd-stat-label">Deceased</span></div>
                <div class="ppd-stat"><span class="ppd-stat-num" data-count="{{ $totalFacilities }}">{{ number_format($totalFacilities) }}</span><span class="ppd-stat-label">Facilities</span></div>
            </div>
        </div>
    </header>

    {{-- ==================== MAP ==================== --}}
    <section class="ppd-section">
        <div class="ppd-section-head">
            <h2 class="ppd-h2">Where they are held</h2>
            <span class="ppd-section-note">{{ number_format($facilities->count()) }} of {{ number_format($totalFacilities) }} facilities mapped · circle size = cases</span>
        </div>
        <div id="ppd-map" class="ppd-map"></div>
    </section>

    {{-- ==================== BREAKDOWNS ==================== --}}
    <section class="ppd-section">
        <div class="ppd-grid">
            <div class="ppd-card">
                <h3 class="ppd-card-h">By status</h3>
                @foreach ($statusBars as [$key, $label, $val])
                    <div class="ppd-bar-row">
                        <span class="ppd-bar-label">{{ $label }}</span>
                        <span class="ppd-bar-track"><span class="ppd-bar-fill ppd-fill--{{ $key }}" style="width: {{ round($val / $statusMax * 100) }}%"></span></span>
                        <span class="ppd-bar-val">{{ number_format($val) }}</span>
                    </div>
                @endforeach
            </div>

            <div class="ppd-card">
                <h3 class="ppd-card-h">Top movements</h3>
                @forelse ($byMovement as $name => $count)
                    <div class="ppd-bar-row">
                        <span class="ppd-bar-label" title="{{ $name }}">{{ $name }}</span>
                        <span class="ppd-bar-track"><span class="ppd-bar-fill" style="width: {{ round($count / $maxMovement * 100) }}%"></span></span>
                        <span class="ppd-bar-val">{{ number_format($count) }}</span>
                    </div>
                @empty
                    <p class="ppd-empty">No movement data yet.</p>
                @endforelse
            </div>

            <div class="ppd-card">
                <h3 class="ppd-card-h">By era</h3>
                @forelse ($byEra as $name => $count)
                    <div class="ppd-bar-row">
                        <span class="ppd-bar-label" title="{{ $name }}">{{ $name }}</span>
                        <span class="ppd-bar-track"><span class="ppd-bar-fill" style="width: {{ round($count / $maxEra * 100) }}%"></span></span>
                        <span class="ppd-bar-val">{{ number_format($count) }}</span>
                    </div>
                @empty
                    <p class="ppd-empty">No era data yet.</p>
                @endforelse
            </div>

            <div class="ppd-card">
                <h3 class="ppd-card-h">Top states</h3>
                @forelse ($byState as $name => $count)
                    <div class="ppd-bar-row">
                        <span class="ppd-bar-label" title="{{ $name }}">{{ $name }}</span>
                        <span class="ppd-bar-track"><span class="ppd-bar-fill" style="width: {{ round($count / $maxState * 100) }}%"></span></span>
                        <span class="ppd-bar-val">{{ number_format($count) }}</span>
                    </div>
                @empty
                    <p class="ppd-empty">No state data yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ==================== RECENTLY DOCUMENTED ==================== --}}
    <section class="ppd-section">
        <div class="ppd-section-head">
            <h2 class="ppd-h2">Recently documented</h2>
            <div class="ppd-chips">
                <button type="button" class="ppd-chip is-active" data-filter="all">All</button>
                <button type="button" class="ppd-chip" data-filter="custody">In custody</button>
                <button type="button" class="ppd-chip" data-filter="released">Released</button>
                <button type="button" class="ppd-chip" data-filter="exile">In exile</button>
                <button type="button" class="ppd-chip" data-filter="deceased">Deceased</button>
            </div>
        </div>
        <div class="ppd-people">
            @foreach ($recent as $p)
                @php $sk = $statusKey($p); @endphp
                <a class="ppd-pcard" href="{{ $p->url }}" data-status="{{ $sk }}">
                    <span class="ppd-pcard-photo">
                        @if ($p->photo_url)
                            <img src="{{ $p->photo_url }}" alt="{{ $p->name }}" loading="lazy">
                        @else
                            <span class="ppd-pcard-initials">{{ strtoupper(mb_substr($p->first_name ?: $p->name, 0, 1)).strtoupper(mb_substr($p->last_name ?: '', 0, 1)) }}</span>
                        @endif
                        <span class="ppd-badge ppd-badge--{{ $sk }}">{{ $statusLabels[$sk] ?? 'Documented' }}</span>
                    </span>
                    <span class="ppd-pcard-name">{{ $p->name }}</span>
                    <span class="ppd-pcard-meta">{{ collect([$p->era, $p->state])->filter()->join(' · ') ?: 'NPPC database' }}</span>
                </a>
            @endforeach
        </div>
        <div class="ppd-foot">
            <a class="ppd-foot-link" href="/map">Open the full interactive map →</a>
            <a class="ppd-foot-link" href="/feature-political-prisoner-cost">See the cost-of-incarceration tracker →</a>
        </div>
    </section>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ---- count-up animation on the stat band ----
        document.querySelectorAll('.ppd-stat-num[data-count]').forEach(function (el) {
            var target = parseInt(el.getAttribute('data-count'), 10) || 0;
            if (target <= 0) { el.textContent = '0'; return; }
            var dur = 900, start = null;
            function step(ts) {
                if (start === null) start = ts;
                var t = Math.min(1, (ts - start) / dur);
                var eased = 0.5 - Math.cos(Math.PI * t) / 2; // ease-in-out
                el.textContent = Math.round(target * eased).toLocaleString();
                if (t < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        });

        // ---- status filter chips ----
        var chips = document.querySelectorAll('.ppd-chip');
        var cards = document.querySelectorAll('.ppd-pcard');
        chips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                chips.forEach(function (c) { c.classList.remove('is-active'); });
                chip.classList.add('is-active');
                var f = chip.getAttribute('data-filter');
                cards.forEach(function (card) {
                    card.style.display = (f === 'all' || card.getAttribute('data-status') === f) ? '' : 'none';
                });
            });
        });

        // ---- facilities map ----
        var facilities = @json($facilities);
        var token = @json($mapboxToken);
        var mapEl = document.getElementById('ppd-map');
        if (!mapEl) return;

        if (!token || !window.mapboxgl) {
            mapEl.innerHTML = '<div class="ppd-map-empty">Map unavailable (no Mapbox token configured).</div>';
            return;
        }
        if (!facilities.length) {
            mapEl.innerHTML = '<div class="ppd-map-empty">No facility coordinates recorded yet.</div>';
            return;
        }

        function esc(s) {
            return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
            });
        }

        mapboxgl.accessToken = token;
        var map = new mapboxgl.Map({
            container: 'ppd-map',
            style: 'mapbox://styles/mapbox/dark-v11',
            center: [-97, 38],
            zoom: 3.1,
            attributionControl: false
        });
        map.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'top-right');

        map.on('load', function () {
            var features = facilities.map(function (f) {
                return {
                    type: 'Feature',
                    properties: { name: f.name, city: f.city, state: f.state, count: f.count },
                    geometry: { type: 'Point', coordinates: [f.lng, f.lat] }
                };
            });
            map.addSource('facilities', { type: 'geojson', data: { type: 'FeatureCollection', features: features } });
            map.addLayer({
                id: 'fac-circles',
                type: 'circle',
                source: 'facilities',
                paint: {
                    'circle-color': '#5b8def',
                    'circle-opacity': 0.72,
                    'circle-stroke-color': '#cfe0ff',
                    'circle-stroke-width': 1,
                    'circle-radius': ['interpolate', ['linear'], ['get', 'count'], 0, 5, 1, 7, 5, 12, 15, 18, 40, 26]
                }
            });

            var bounds = new mapboxgl.LngLatBounds();
            features.forEach(function (ft) { bounds.extend(ft.geometry.coordinates); });
            if (!bounds.isEmpty()) map.fitBounds(bounds, { padding: 60, maxZoom: 6.5, duration: 0 });

            var popup = new mapboxgl.Popup({ closeButton: false, className: 'ppd-pop' });
            map.on('mouseenter', 'fac-circles', function (e) {
                map.getCanvas().style.cursor = 'pointer';
                var p = e.features[0].properties;
                var coords = e.features[0].geometry.coordinates.slice();
                var loc = [p.city, p.state].filter(Boolean).join(', ');
                var n = parseInt(p.count, 10) || 0;
                var html = '<strong>' + esc(p.name) + '</strong>' +
                    (loc ? '<br>' + esc(loc) : '') +
                    '<br><span class="ppd-pop-n">' + n.toLocaleString() + ' case' + (n === 1 ? '' : 's') + '</span>';
                popup.setLngLat(coords).setHTML(html).addTo(map);
            });
            map.on('mouseleave', 'fac-circles', function () {
                map.getCanvas().style.cursor = '';
                popup.remove();
            });
        });
    });
</script>
@endsection
