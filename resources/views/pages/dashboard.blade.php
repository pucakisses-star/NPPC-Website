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
    .ppd-ticker-track { position: absolute; top: 0; left: 0; height: 100%; display: inline-flex; align-items: center; white-space: nowrap; animation: ppdmarquee 180s linear infinite; }
    .ppd-ticker-view:hover .ppd-ticker-track { animation-play-state: paused; }
    @@keyframes ppdmarquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }
    .ppd-tk { display: inline-flex; align-items: center; gap: 9px; padding: 0 26px; font-size: 12.5px; color: rgba(236,233,226,0.82); text-decoration: none; transition: color 0.12s ease; }
    .ppd-tk::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: var(--amber); flex: 0 0 auto; }
    .ppd-tk b { color: #fff; font-weight: 700; transition: color 0.12s ease; }
    a.ppd-tk:hover b { color: var(--amber); }
    .ppd-tk-cat { font-size: 9.5px; font-weight: 900; letter-spacing: 0.08em; text-transform: uppercase; color: var(--amber); opacity: 0.85; }

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
    /* "+ Add" article button + suggest-an-article form in the feed head */
    .ppd-feed-add { font: inherit; font-size: 11px; font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase; color: var(--amber); background: rgba(224,168,46,0.10); border: 1px solid rgba(224,168,46,0.40); border-radius: 5px; padding: 4px 10px; cursor: pointer; line-height: 1.2; transition: background 0.12s ease; }
    .ppd-feed-add:hover { background: rgba(224,168,46,0.20); }
    .ppd-feed-add[aria-expanded="true"] { font-size: 15px; padding: 1px 9px; }
    .ppd-addform { display: none; padding: 16px 18px; border-bottom: 1px solid var(--line); background: #0d0d10; }
    .ppd-addform.is-open { display: block; }
    .ppd-addform-thanks { font-size: 12.5px; line-height: 1.5; color: #bfe9cf; background: rgba(63,208,127,0.10); border: 1px solid rgba(63,208,127,0.35); border-radius: 6px; padding: 9px 11px; margin-bottom: 12px; }
    .ppd-addfield { display: block; font-size: 10.5px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--mut); margin-bottom: 11px; }
    .ppd-addfield-opt { font-weight: 600; text-transform: none; letter-spacing: 0; opacity: 0.7; }
    .ppd-addfield input, .ppd-addfield textarea { display: block; width: 100%; margin-top: 5px; font: inherit; font-size: 13px; letter-spacing: normal; text-transform: none; font-weight: 400; color: var(--ink); background: #141418; border: 1px solid var(--line); border-radius: 6px; padding: 8px 10px; }
    .ppd-addfield textarea { resize: vertical; min-height: 76px; }
    .ppd-addfield input:focus, .ppd-addfield textarea:focus { outline: none; border-color: rgba(224,168,46,0.6); }
    .ppd-hp { position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; }
    .ppd-addform-actions { display: flex; gap: 9px; align-items: center; }
    .ppd-addform-submit { font: inherit; font-size: 12px; font-weight: 800; color: #19140a; background: var(--amber); border: 0; border-radius: 6px; padding: 8px 14px; cursor: pointer; }
    .ppd-addform-submit:hover { background: #f0bb47; }
    .ppd-addform-cancel { font: inherit; font-size: 12px; font-weight: 700; color: var(--mut); background: none; border: 0; cursor: pointer; }
    .ppd-addform-cancel:hover { color: var(--ink); }

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
    .leaflet-popup-content .ppd-pop-link { display: inline-block; margin-top: 6px; color: #19c37d; font-weight: 700; text-decoration: none; }
    .leaflet-popup-content .ppd-pop-link:hover { text-decoration: underline; }
    .leaflet-bar a { background: #15151a; color: var(--ink); border-color: var(--line); }
    .leaflet-bar a:hover { background: #20202a; }
    /* ---- map markers: glowing dot + radar "ping" pulse (--c = status colour) ---- */
    .ppd-mk-wrap { background: transparent; border: 0; }
    .ppd-mk { position: relative; display: block; }
    .ppd-mk-dot { position: absolute; inset: 0; border-radius: 50%; background: var(--c); box-shadow: 0 0 0 1px rgba(0,0,0,0.45), 0 0 8px var(--c); pointer-events: none; }
    .ppd-mk-ping { position: absolute; inset: 0; border-radius: 50%; background: var(--c); opacity: 0.5; pointer-events: none; animation: ppd-ping 2.6s cubic-bezier(0,0,0.2,1) infinite; }
    @keyframes ppd-ping { 0% { transform: scale(1); opacity: 0.5; } 70% { opacity: 0; } 100% { transform: scale(3.4); opacity: 0; } }
    @media (prefers-reduced-motion: reduce) { .ppd-mk-ping { animation: none; opacity: 0; } }

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

    /* ---- timeline scrubber (below the map) ---- */
    .ppd-timeline { display: flex; align-items: center; gap: 18px; padding: 18px 22px 22px; border-bottom: 1px solid var(--line); background: #0b0b0d; }
    .ppd-tl-play { flex: 0 0 auto; width: 40px; height: 40px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.18); background: #141418; color: var(--ink); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: background .12s ease, border-color .12s ease, color .12s ease; }
    .ppd-tl-play:hover { background: #20202a; border-color: rgba(224,168,46,0.5); color: var(--amber); }
    .ppd-tl-play .ppd-tl-ico-pause { display: none; }
    .ppd-tl-play.is-playing .ppd-tl-ico-play { display: none; }
    .ppd-tl-play.is-playing .ppd-tl-ico-pause { display: inline; color: var(--amber); }
    .ppd-tl-main { flex: 1 1 auto; min-width: 0; overflow-x: auto; overflow-y: hidden; padding-bottom: 2px; }
    .ppd-tl-main::-webkit-scrollbar { height: 8px; }
    .ppd-tl-main::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 8px; }
    .ppd-tl-scroll { min-width: 640px; }
    .ppd-tl-bar { position: relative; height: 16px; }
    .ppd-tl-rail { position: absolute; left: 0; right: 0; top: 50%; transform: translateY(-50%); height: 3px; border-radius: 999px; background: rgba(255,255,255,0.12); }
    .ppd-tl-fill { position: absolute; left: 0; top: 50%; transform: translateY(-50%); height: 3px; width: 0; border-radius: 999px; background: linear-gradient(90deg, rgba(224,168,46,0.3), var(--amber)); }
    .ppd-tl-handle { position: absolute; top: 50%; left: 0; width: 16px; height: 16px; margin-left: -8px; transform: translateY(-50%); border-radius: 50%; background: var(--amber); border: 2px solid #0b0b0d; box-shadow: 0 0 0 1px var(--amber), 0 2px 6px rgba(0,0,0,0.5); cursor: grab; touch-action: none; }
    .ppd-tl-handle:active { cursor: grabbing; }
    .ppd-tl-handle:focus-visible { outline: 2px solid var(--amber); outline-offset: 3px; }
    .ppd-tl-ticks { display: flex; position: relative; margin-top: 9px; }
    .ppd-tl-tick { flex: 1 1 0; min-width: 0; display: flex; flex-direction: column; align-items: center; gap: 3px; }
    .ppd-tl-tick::before { content: ""; width: 1px; height: 4px; background: rgba(255,255,255,0.14); }
    .ppd-tl-num { font-size: 11px; font-weight: 700; color: rgba(236,233,226,0.34); line-height: 1; min-height: 11px; font-variant-numeric: tabular-nums; }
    .ppd-tl-date { font-size: 9px; color: rgba(236,233,226,0.28); line-height: 1; white-space: nowrap; letter-spacing: 0.02em; }
    .ppd-tl-tick.is-passed::before { background: rgba(224,168,46,0.5); }
    .ppd-tl-tick.is-passed .ppd-tl-num { color: rgba(236,233,226,0.62); }
    .ppd-tl-tick.is-current::before { height: 7px; background: var(--amber); }
    .ppd-tl-tick.is-current .ppd-tl-num { color: #fff; }
    .ppd-tl-tick.is-current .ppd-tl-date { color: var(--amber); }
    @@media (max-width: 540px) { .ppd-timeline { padding: 14px 14px 18px; gap: 12px; } .ppd-tl-play { width: 34px; height: 34px; } }

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
    // status key (most salient first) + label + colour, reused everywhere
    $statusKey = function ($p) {
        if ($p->death_date)         return 'deceased';
        if ($p->in_custody)         return 'custody';
        if ($p->currently_in_exile) return 'exile';
        if ($p->awaiting_trial)     return 'awaiting';
        if ($p->released)           return 'released';
        return 'other';
    };

    // The dashboard is a live tracker of active cases — released and deceased
    // people are dropped from the map, stats, legend and timeline.
    $prisoners = \App\Models\Prisoner::query()->orderByDesc('created_at')->get()
        ->reject(fn ($p) => in_array($statusKey($p), ['released', 'deceased'], true))
        ->values();

    $total     = $prisoners->count();
    $inCustody = $prisoners->where('in_custody', true)->count();
    $inExile   = $prisoners->where('currently_in_exile', true)->count();
    $awaiting  = $prisoners->where('awaiting_trial', true)->count();
    $totalFacilities = \App\Models\Institution::count();

    $statusMeta = [
        'custody'  => ['In custody',     '#e5484d'],
        'awaiting' => ['Awaiting trial', '#4c8dff'],
        'exile'    => ['In exile',       '#e0a82e'],
        'other'    => ['Documented',      '#9aa0a6'],
        'event'    => ['Event',          '#19c37d'],  // curated news/event markers
    ];

    // strip breakdown counters
    $breaks = [
        ['custody',  $inCustody],
        ['awaiting', $awaiting],
        ['exile',    $inExile],
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
    ];

    // Newswire + ticker: published articles plus curated DashboardLinks, merged
    // newest-first into a uniform list of items ({title,url,label,date,external}).
    $articleItems = \App\Models\Article::query()
        ->whereNotNull('published_at')->where('published_at', '<=', now())
        ->with('category')->orderByDesc('published_at')->take(40)->get()
        ->map(fn ($a) => (object) [
            'title' => $a->title, 'url' => $a->url,
            'label' => $a->category?->title, 'date' => $a->published_at, 'external' => false,
        ]);
    $linkItems = \App\Models\DashboardLink::published()
        ->orderByDesc('published_at')->take(40)->get()
        ->map(fn ($l) => (object) [
            'title' => $l->title, 'url' => $l->url,
            'label' => $l->source, 'date' => $l->published_at, 'external' => true,
        ]);
    $newsItems = $articleItems->concat($linkItems)->sortByDesc('date')->values();
    $feedItems = $newsItems->take(40);   // side newswire
    $ticker    = $newsItems->take(16);   // top scroller

    // ---- timeline scrubber: a fixed window from May 7, 2025 to today, one tick
    // per day (it never reaches into the future). Date labels are shown about
    // weekly; only ~30 days are visible at a time and the bar scrolls to slide
    // into the next 30 as you move back. The handle selects how far back to look —
    // the map + newswire show items dated from the handle up to today — and it
    // defaults to the last 30 days (see the JS). Dates before the start clamp to it. ----
    $tlEnd   = now()->startOfDay();
    $tlStart = \Illuminate\Support\Carbon::create(2025, 5, 7)->startOfDay();
    $tlCount = $tlStart->diffInDays($tlEnd) + 1;   // May 7, 2025 → today, inclusive
    $curYear = (int) $tlEnd->year;
    // Format a date showing the year only when it falls in a previous year.
    $smartDate = function ($date) use ($curYear) {
        if (! $date) return '';
        $d = \Illuminate\Support\Carbon::parse($date);
        return $d->format($d->year < $curYear ? 'M j, Y' : 'M j');
    };
    $tlDays  = [];
    $prevMonth = null;
    for ($i = 0; $i < $tlCount; $i++) {
        $d = $tlStart->copy()->addDays($i);
        $month = $d->format('M');
        // older months also carry a short year ("Jun '25") so the lookback isn't ambiguous
        $monthLabel = $month . ($d->year < $curYear ? " '" . $d->format('y') : '');
        $tlDays[] = [
            'dom'   => $d->format('j'),                            // day of month — shown on every tick
            'month' => ($month !== $prevMonth) ? $monthLabel : '',  // month label only where it changes
            'label' => $smartDate($d),                             // full date (tooltip), year if older
        ];
        $prevMonth = $month;
    }
    // 0-based day index for any date, clamped into the tick range
    $dayIndex = function ($date) use ($tlStart, $tlCount) {
        if (! $date) return 0;
        $d = \Illuminate\Support\Carbon::parse($date)->startOfDay();
        $i = $tlStart->diffInDays($d, false);
        return (int) max(0, min($tlCount - 1, $i));
    };

    // map points: prisoners with coordinates, coloured by status
    $mapPoints = $prisoners
        ->filter(fn ($p) => $p->lat !== null && $p->lng !== null)
        ->map(function ($p) use ($statusKey, $statusMeta, $dayIndex) {
            $sk = $statusKey($p);
            return [
                'lat'    => (float) $p->lat,
                'lng'    => (float) $p->lng,
                'name'   => $p->name,
                'status' => $sk,
                'day'    => $dayIndex($p->created_at),
                'meta'   => collect([$statusMeta[$sk][0] ?? null, $p->state])->filter()->join(' · '),
                'url'    => $p->url,
            ];
        })->values();

    // fallback for the map if no prisoner has coordinates: facilities. These
    // carry no documented-date, so they sit at day 0 (always visible).
    $mapFacilities = \App\Models\Institution::query()
        ->whereNotNull('lat')->whereNotNull('lng')->withCount('cases')->get()
        ->map(fn ($i) => [
            'lat'   => (float) $i->lat,
            'lng'   => (float) $i->lng,
            'name'  => $i->name,
            'day'   => 0,
            'meta'  => collect([$i->city, $i->state])->filter()->join(', '),
            'count' => (int) $i->cases_count,
        ])->values();

    // event markers: curated dashboard links that carry coordinates. They sit on
    // the map alongside prisoners, coloured as "event", and scrub with the timeline.
    // Guarded so the page still renders if the lat/lng migration hasn't run yet.
    $eventPoints = \Illuminate\Support\Facades\Schema::hasColumn('dashboard_links', 'lat')
        ? \App\Models\DashboardLink::onMap()
            ->orderByDesc('published_at')->get()
            ->map(fn ($l) => [
                'lat'    => (float) $l->lat,
                'lng'    => (float) $l->lng,
                'name'   => $l->title,
                'status' => 'event',
                'day'    => $dayIndex($l->published_at),
                'meta'   => collect([$l->location_label, $l->source])->filter()->join(' · '),
                'url'    => $l->url,
            ])->values()
        : collect();

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
        <span class="ppd-ticker-tag"><span class="ppd-dot" style="background:#19140a"></span> Latest news</span>
        <div class="ppd-ticker-view">
            {{-- Duplicated twice so the marquee loops seamlessly (see ppdmarquee: -50%). --}}
            <div class="ppd-ticker-track">
                @foreach ($ticker as $a)
                    <a class="ppd-tk" href="{{ $a->url }}"@if ($a->external) target="_blank" rel="noopener"@endif><b>{{ $a->title }}</b>@if ($a->label) <span class="ppd-tk-cat">{{ $a->label }}</span>@endif</a>
                @endforeach
                @foreach ($ticker as $a)
                    <a class="ppd-tk" href="{{ $a->url }}"@if ($a->external) target="_blank" rel="noopener"@endif aria-hidden="true" tabindex="-1"><b>{{ $a->title }}</b>@if ($a->label) <span class="ppd-tk-cat">{{ $a->label }}</span>@endif</a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ==================== BODY: FEED + MAP ==================== --}}
    <div class="ppd-body">
        <aside class="ppd-feed">
            <div class="ppd-feed-head">
                <span class="ppd-label">Newswire · Recent articles</span>
                <button type="button" class="ppd-feed-add" id="ppd-feed-add" aria-expanded="false">+ Add</button>
            </div>

            {{-- Suggest-an-article form. Public page, so a submission is stored
                 for admin review (as a FormSubmission) rather than published
                 directly. Hidden until the "+ Add" button is clicked. --}}
            <form class="ppd-addform" id="ppd-addform" method="POST" action="/form/article-submission">
                @csrf
                @if (request('form_submitted') && request('form') === 'article')
                    <div class="ppd-addform-thanks">Thanks — your article was submitted for review. An editor will publish it if it's a fit.</div>
                @endif
                <label class="ppd-addfield">Title
                    <input type="text" name="title" maxlength="200" required placeholder="Article headline">
                </label>
                <label class="ppd-addfield">Article
                    <textarea name="body" rows="5" required placeholder="Write the article, or paste it with a source link…"></textarea>
                </label>
                <label class="ppd-addfield">Your email <span class="ppd-addfield-opt">(optional)</span>
                    <input type="email" name="submitter_email" placeholder="So an editor can follow up">
                </label>
                <input type="text" name="website" tabindex="-1" autocomplete="off" class="ppd-hp" aria-hidden="true">
                <div class="ppd-addform-actions">
                    <button type="submit" class="ppd-addform-submit">Submit for review</button>
                    <button type="button" class="ppd-addform-cancel" id="ppd-addform-cancel">Cancel</button>
                </div>
            </form>

            @forelse ($feedItems as $a)
                <a class="ppd-feed-item" href="{{ $a->url }}" data-day="{{ $dayIndex($a->date) }}"@if ($a->external) target="_blank" rel="noopener"@endif>
                    <span class="ppd-feed-name">{{ $a->title }}</span>
                    <span class="ppd-feed-sub">
                        @if ($a->label)
                            <span class="ppd-tagchip" style="background: #e0a82e">{{ $a->label }}</span>
                        @endif
                        <span class="ppd-feed-date">{{ optional($a->date)->format('M j, Y') }}</span>
                    </span>
                </a>
            @empty
                <div class="ppd-feed-empty">No news yet.</div>
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
                @if ($eventPoints->isNotEmpty())
                    <button type="button" class="ppd-leg" data-filter="event">
                        <span class="ppd-leg-dot" style="background: {{ $statusMeta['event'][1] }}"></span>
                        <span class="ppd-leg-lab">{{ $statusMeta['event'][0] }}</span>
                        <span class="ppd-leg-n">{{ number_format($eventPoints->count()) }}</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- ==================== TIMELINE SCRUBBER (below the map) ==================== --}}
    <div class="ppd-timeline">
        <button type="button" class="ppd-tl-play" id="ppd-tl-play" aria-label="Play timeline">
            <svg class="ppd-tl-ico-play" width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
            <svg class="ppd-tl-ico-pause" width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 5h4v14H6zM14 5h4v14h-4z"/></svg>
        </button>
        <div class="ppd-tl-main">
            <div class="ppd-tl-scroll">
                <div class="ppd-tl-bar" id="ppd-tl-bar">
                    <div class="ppd-tl-rail"></div>
                    <div class="ppd-tl-fill" id="ppd-tl-fill"></div>
                    <div class="ppd-tl-handle" id="ppd-tl-handle-lo" role="slider" tabindex="0"
                         aria-label="Range start day"
                         aria-valuemin="1" aria-valuemax="{{ max(1, $tlCount) }}" aria-valuenow="{{ max(1, $tlCount - 30) }}"></div>
                    <div class="ppd-tl-handle" id="ppd-tl-handle-hi" role="slider" tabindex="0"
                         aria-label="Range end day"
                         aria-valuemin="1" aria-valuemax="{{ max(1, $tlCount) }}" aria-valuenow="{{ max(1, $tlCount) }}"></div>
                </div>
                <div class="ppd-tl-ticks" id="ppd-tl-ticks">
                    @foreach ($tlDays as $i => $day)
                        <div class="ppd-tl-tick" data-i="{{ $i }}" title="{{ $day['label'] }}">
                            <span class="ppd-tl-num">{{ $day['dom'] }}</span>
                            <span class="ppd-tl-date">{{ $day['month'] }}</span>
                        </div>
                    @endforeach
                </div>
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
        var eventPts    = @json($eventPoints);
        var mapEl = document.getElementById('ppd-map');

        var useFacilities = prisonerPts.length === 0;
        var points = useFacilities ? facilityPts : prisonerPts;

        // The map is optional: if Leaflet fails to load we still wire up the
        // feed + timeline filtering below, so the page degrades gracefully.
        var map = null;
        var markers = [];
        if (!mapEl || !window.L) {
            if (mapEl) mapEl.innerHTML = '<div class="ppd-map-empty">Map library unavailable.</div>';
        } else {
            if (!points.length && !eventPts.length) { mapEl.innerHTML = '<div class="ppd-map-empty">No mapped coordinates recorded yet.</div>'; }

            map = L.map('ppd-map', { zoomControl: true, scrollWheelZoom: false, attributionControl: false }).setView([39, -97], 4);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                subdomains: 'abcd', maxZoom: 19,
                attribution: '&copy; OpenStreetMap &copy; CARTO'
            }).addTo(map);
            setTimeout(function () { map.invalidateSize(); }, 200);

            var latlngs = [];
            // Build a pulsing "ping" marker, register it for legend/timeline filtering,
            // and track its coordinate for fitBounds. Shared by all three layers.
            function addPing(p, color, sz, popupHtml, status) {
                // negative random delay so the rings pulse out of phase, not in lockstep
                var delay = (-Math.random() * 2.6).toFixed(2);
                var icon = L.divIcon({
                    className: 'ppd-mk-wrap',
                    iconSize: [sz, sz],
                    iconAnchor: [sz / 2, sz / 2],
                    popupAnchor: [0, -sz / 2],
                    html: '<span class="ppd-mk" style="--c:' + color + ';width:' + sz + 'px;height:' + sz + 'px">'
                        + '<span class="ppd-mk-ping" style="animation-delay:' + delay + 's"></span>'
                        + '<span class="ppd-mk-dot"></span></span>'
                });
                var m = L.marker([p.lat, p.lng], { icon: icon, keyboard: false });
                if (popupHtml) m.bindPopup(popupHtml);
                m.addTo(map);
                markers.push({ marker: m, status: status, day: p.day || 0 });
                latlngs.push([p.lat, p.lng]);
            }

            points.forEach(function (p) {
                var color = useFacilities ? '#e0a82e' : (statusColors[p.status] || '#9aa0a6');
                // dot diameter: fixed for prisoners, scaled by case count for facilities
                var radius = useFacilities ? Math.max(5, Math.min(20, 4 + Math.sqrt(p.count || 1) * 3)) : 6;
                var extra = useFacilities ? ((p.count || 0) + ' case' + (p.count === 1 ? '' : 's')) : (p.meta || '');
                var popup = '<b>' + esc(p.name) + '</b>' + (extra ? '<br><span class="ppd-pop-meta">' + esc(extra) + '</span>' : '');
                addPing(p, color, Math.round(radius * 2), popup, useFacilities ? 'other' : p.status);
            });

            // curated event markers sit on top, independent of the prisoner/facility base layer
            eventPts.forEach(function (p) {
                var meta = p.meta ? '<br><span class="ppd-pop-meta">' + esc(p.meta) + '</span>' : '';
                var link = p.url ? '<br><a class="ppd-pop-link" href="' + esc(p.url) + '" target="_blank" rel="noopener">Read &rsaquo;</a>' : '';
                addPing(p, statusColors['event'] || '#19c37d', 16, '<b>' + esc(p.name) + '</b>' + meta + link, 'event');
            });

            if (latlngs.length) { map.fitBounds(latlngs, { padding: [42, 42], maxZoom: 7 }); }
        }

        // ---- shared filtering: the legend status filters the map markers; the
        // timeline day filters BOTH the map markers and the newswire items. ----
        var legend = document.getElementById('ppd-legend');
        var legRows = legend ? legend.querySelectorAll('.ppd-leg') : [];
        var feedItems = document.querySelectorAll('.ppd-feed-item');
        var statusFilter = null;     // status key, or null for "all statuses"
        var loFilter = 0, hiFilter = 1e9;   // date-range window: show items dated within [lo, hi]

        function visible(status, day) {
            return (statusFilter === null || status === statusFilter) && (day >= loFilter && day <= hiFilter);
        }
        function applyFilters() {
            if (map) {
                markers.forEach(function (o) {
                    var on = visible(o.status, o.day);
                    if (on) { if (!map.hasLayer(o.marker)) o.marker.addTo(map); }
                    else { if (map.hasLayer(o.marker)) map.removeLayer(o.marker); }
                });
            }
            // Newswire items show only within the selected window (lo <= date <= hi).
            feedItems.forEach(function (el) {
                var dy = parseInt(el.getAttribute('data-day'), 10) || 0;
                el.style.display = (dy >= loFilter && dy <= hiFilter) ? '' : 'none';
            });
            legRows.forEach(function (r) { r.classList.toggle('is-active', r.getAttribute('data-filter') === statusFilter); });
            if (legend) legend.classList.toggle('is-filtered', statusFilter !== null);
        }

        legRows.forEach(function (row) {
            row.addEventListener('click', function () {
                var f = row.getAttribute('data-filter');
                statusFilter = (statusFilter === f) ? null : f;   // click active row again to reset
                applyFilters();
            });
        });

        // ---- timeline scrubber under the map ----
        // Two handles bound a date range: the map markers + newswire show items
        // dated within [lo, hi]. It defaults to the last ~30 days; drag either
        // handle to widen or narrow the window, click the rail to move the nearer
        // handle, or press play to replay history sweeping the end to today.
        (function () {
            var bar = document.getElementById('ppd-tl-bar');
            var loH = document.getElementById('ppd-tl-handle-lo');
            var hiH = document.getElementById('ppd-tl-handle-hi');
            var fill = document.getElementById('ppd-tl-fill');
            var ticksWrap = document.getElementById('ppd-tl-ticks');
            var playBtn = document.getElementById('ppd-tl-play');
            if (!bar || !loH || !hiH || !fill || !ticksWrap || !playBtn) return;

            var ticks = Array.prototype.slice.call(ticksWrap.querySelectorAll('.ppd-tl-tick'));
            var count = ticks.length;
            if (!count) return;

            var main = document.querySelector('.ppd-tl-main');
            var scrollEl = document.querySelector('.ppd-tl-scroll');
            var lo = Math.max(0, count - 1 - 30);   // default window start: ~30 days back
            var hi = count - 1;                      // default window end: today
            var playing = false, rafId = null;

            function centerOf(i) { return ticks[i].offsetLeft + ticks[i].offsetWidth / 2; }

            // Size the bar so ~30 days fill the viewport; the rest scrolls
            // horizontally. But never let a day get so narrow that its number
            // collides with its neighbour on smaller screens — clamp to a
            // minimum tick width so every date stays legible (fewer days show
            // at once and you scroll to see the rest).
            var MIN_TICK = 26;
            function sizeBar() {
                if (!main || !scrollEl) return;
                var perTick = Math.max(MIN_TICK, main.clientWidth / 30);
                scrollEl.style.width = Math.round(perTick * count) + 'px';
            }
            // Keep a given index in view, sliding the window as it nears an edge.
            function ensureVisible(i) {
                if (!main) return;
                var x = centerOf(i);
                var pad = main.clientWidth * 0.12;
                var left = main.scrollLeft, right = left + main.clientWidth;
                if (x < left + pad) main.scrollLeft = Math.max(0, x - pad);
                else if (x > right - pad) main.scrollLeft = Math.min(main.scrollWidth - main.clientWidth, x - main.clientWidth + pad);
            }

            function render() {
                var xl = centerOf(lo), xh = centerOf(hi);
                loH.style.left = xl + 'px';
                hiH.style.left = xh + 'px';
                // Fill the selected window: the span between the two handles.
                fill.style.left = xl + 'px';
                fill.style.width = Math.max(0, xh - xl) + 'px';
                loH.setAttribute('aria-valuenow', lo + 1);
                hiH.setAttribute('aria-valuenow', hi + 1);
                for (var i = 0; i < count; i++) {
                    ticks[i].classList.toggle('is-passed', i >= lo && i <= hi);    // inside the window
                    ticks[i].classList.toggle('is-current', i === lo || i === hi); // the two endpoints
                }
                // Re-filter the map + newswire only when the window actually changes.
                if (loFilter !== lo || hiFilter !== hi) { loFilter = lo; hiFilter = hi; applyFilters(); }
            }
            function clamp(i) { i = i | 0; return i < 0 ? 0 : (i > count - 1 ? count - 1 : i); }
            function setLo(i) { lo = Math.min(clamp(i), hi); render(); ensureVisible(lo); }   // can't pass hi
            function setHi(i) { hi = Math.max(clamp(i), lo); render(); ensureVisible(hi); }   // can't pass lo
            function nearestIndex(clientX) {
                var x = clientX - ticksWrap.getBoundingClientRect().left;
                var best = 0, bestD = Infinity;
                for (var i = 0; i < count; i++) {
                    var d = Math.abs(centerOf(i) - x);
                    if (d < bestD) { bestD = d; best = i; }
                }
                return best;
            }

            // ---- play / pause: replay history, sweeping the end handle to today ----
            function stop() {
                if (!playing) return;
                playing = false;
                if (rafId) cancelAnimationFrame(rafId);
                rafId = null;
                playBtn.classList.remove('is-playing');
                playBtn.setAttribute('aria-label', 'Play timeline');
            }
            function start() {
                if (playing) return;
                lo = 0; setHi(0);   // collapse the window onto the oldest day, then grow it
                playing = true;
                playBtn.classList.add('is-playing');
                playBtn.setAttribute('aria-label', 'Pause timeline');
                var startIdx = 0, endIdx = count - 1;   // sweep the end handle: oldest -> today
                var dur = Math.min(9000, Math.max(2500, count * 130));
                var t0 = null;
                function frame(ts) {
                    if (!playing) return;
                    if (t0 === null) t0 = ts;
                    var p = Math.min(1, (ts - t0) / dur);
                    setHi(Math.round(startIdx + (endIdx - startIdx) * p));
                    if (p < 1) { rafId = requestAnimationFrame(frame); } else { stop(); }
                }
                rafId = requestAnimationFrame(frame);
            }
            playBtn.addEventListener('click', function () { playing ? stop() : start(); });

            // ---- drag a handle, or click the rail/ticks to move the nearer one ----
            function beginDrag(which, e) {
                e.preventDefault();
                stop();
                var move = (which === 'lo')
                    ? function (ev) { setLo(nearestIndex(ev.clientX)); }
                    : function (ev) { setHi(nearestIndex(ev.clientX)); };
                function up() {
                    document.removeEventListener('pointermove', move);
                    document.removeEventListener('pointerup', up);
                }
                document.addEventListener('pointermove', move);
                document.addEventListener('pointerup', up);
            }
            function seek(clientX) {
                stop();
                var i = nearestIndex(clientX);
                // move whichever handle sits closer to the click (ties favour the start)
                if (Math.abs(i - lo) <= Math.abs(i - hi)) { setLo(i); return 'lo'; }
                setHi(i); return 'hi';
            }
            loH.addEventListener('pointerdown', function (e) { beginDrag('lo', e); });
            hiH.addEventListener('pointerdown', function (e) { beginDrag('hi', e); });
            bar.addEventListener('pointerdown', function (e) {
                if (e.target === loH || e.target === hiH) return;
                beginDrag(seek(e.clientX), e);
            });
            ticksWrap.addEventListener('click', function (e) { seek(e.clientX); });
            loH.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowLeft') { stop(); setLo(lo - 1); e.preventDefault(); }
                else if (e.key === 'ArrowRight') { stop(); setLo(lo + 1); e.preventDefault(); }
            });
            hiH.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowLeft') { stop(); setHi(hi - 1); e.preventDefault(); }
                else if (e.key === 'ArrowRight') { stop(); setHi(hi + 1); e.preventDefault(); }
            });

            var rt;
            window.addEventListener('resize', function () { clearTimeout(rt); rt = setTimeout(function () { sizeBar(); render(); ensureVisible(hi); }, 120); });

            loFilter = lo; hiFilter = hi; applyFilters();   // apply the default window immediately
            requestAnimationFrame(function () {
                sizeBar();
                render();
                // default view: most recent ~30 days, with the end handle (today) at the right edge
                if (main) main.scrollLeft = main.scrollWidth;
            });
        })();

        // ---- "+ Add" article form: toggle open/closed ----
        (function () {
            var addBtn = document.getElementById('ppd-feed-add');
            var form = document.getElementById('ppd-addform');
            var cancel = document.getElementById('ppd-addform-cancel');
            if (!addBtn || !form) return;

            function open(on) {
                form.classList.toggle('is-open', on);
                addBtn.setAttribute('aria-expanded', on ? 'true' : 'false');
                addBtn.textContent = on ? '×' : '+ Add';
                if (on) { var t = form.querySelector('input[name="title"]'); if (t) t.focus(); }
            }
            addBtn.addEventListener('click', function () {
                open(!form.classList.contains('is-open'));
            });
            if (cancel) cancel.addEventListener('click', function () { open(false); });

            // Keep it open if the page came back showing the thank-you message.
            if (form.querySelector('.ppd-addform-thanks')) { open(true); }
        })();
    });
</script>
@endsection
