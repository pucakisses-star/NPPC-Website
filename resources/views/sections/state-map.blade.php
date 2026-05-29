{{-- US tile-grid map of prisoners by state, modeled on theintercept.com/trial-and-terror --}}
@php
    $stateNames = [
        'AL' => 'Alabama',        'AK' => 'Alaska',         'AZ' => 'Arizona',
        'AR' => 'Arkansas',       'CA' => 'California',     'CO' => 'Colorado',
        'CT' => 'Connecticut',    'DE' => 'Delaware',       'DC' => 'District of Columbia',
        'FL' => 'Florida',        'GA' => 'Georgia',        'HI' => 'Hawaii',
        'ID' => 'Idaho',          'IL' => 'Illinois',       'IN' => 'Indiana',
        'IA' => 'Iowa',           'KS' => 'Kansas',         'KY' => 'Kentucky',
        'LA' => 'Louisiana',      'ME' => 'Maine',          'MD' => 'Maryland',
        'MA' => 'Massachusetts',  'MI' => 'Michigan',       'MN' => 'Minnesota',
        'MS' => 'Mississippi',    'MO' => 'Missouri',       'MT' => 'Montana',
        'NE' => 'Nebraska',       'NV' => 'Nevada',         'NH' => 'New Hampshire',
        'NJ' => 'New Jersey',     'NM' => 'New Mexico',     'NY' => 'New York',
        'NC' => 'North Carolina', 'ND' => 'North Dakota',   'OH' => 'Ohio',
        'OK' => 'Oklahoma',       'OR' => 'Oregon',         'PA' => 'Pennsylvania',
        'RI' => 'Rhode Island',   'SC' => 'South Carolina', 'SD' => 'South Dakota',
        'TN' => 'Tennessee',      'TX' => 'Texas',          'UT' => 'Utah',
        'VT' => 'Vermont',        'VA' => 'Virginia',       'WA' => 'Washington',
        'WV' => 'West Virginia',  'WI' => 'Wisconsin',      'WY' => 'Wyoming',
        'PR' => 'Puerto Rico',
    ];

    // [abbr, row, col]
    $grid = [
        ['ME', 1, 11],
        ['WA', 2, 1], ['ID', 2, 2], ['MT', 2, 3], ['ND', 2, 4], ['MN', 2, 5], ['WI', 2, 6], ['MI', 2, 7], ['PA', 2, 8], ['VT', 2, 9], ['NH', 2, 10], ['MA', 2, 11],
        ['OR', 3, 1], ['NV', 3, 2], ['WY', 3, 3], ['SD', 3, 4], ['IA', 3, 5], ['IN', 3, 6], ['OH', 3, 7], ['MD', 3, 8], ['NY', 3, 9], ['CT', 3, 10], ['RI', 3, 11],
        ['CA', 4, 1], ['UT', 4, 2], ['CO', 4, 3], ['NE', 4, 4], ['MO', 4, 5], ['IL', 4, 6], ['KY', 4, 7], ['DC', 4, 8], ['DE', 4, 9], ['NJ', 4, 10],
        ['AZ', 5, 2], ['NM', 5, 3], ['KS', 5, 4], ['AR', 5, 5], ['TN', 5, 6], ['WV', 5, 7], ['VA', 5, 8], ['NC', 5, 9],
        ['OK', 6, 4], ['LA', 6, 5], ['MS', 6, 6], ['AL', 6, 7], ['GA', 6, 8], ['SC', 6, 9],
        ['AK', 7, 1], ['HI', 7, 2], ['TX', 7, 4], ['FL', 7, 10], ['PR', 7, 11],
    ];

    $counts = \App\Models\Prisoner::query()
        ->whereNotNull('state')
        ->where('state', '!=', '')
        ->selectRaw('state, count(*) as cnt')
        ->groupBy('state')
        ->pluck('cnt', 'state'); // [full state name => count]

    $total = max($counts->sum(), 1);
    $max   = max($counts->max() ?: 0, 1);

    // Map abbr -> count
    $byAbbr = [];
    foreach ($stateNames as $abbr => $name) {
        $byAbbr[$abbr] = (int) ($counts[$name] ?? 0);
    }

    // Color: dark site bg → site brand blue → magenta accent
    // Stops: 0 -> #14141a (empty), low -> #2c3a8c, mid -> #5660fe, high -> #c5279a
    $colorFor = function (int $c) use ($max): string {
        if ($c <= 0) return '#14141a';
        $t = min(1.0, log($c + 1) / log($max + 1)); // log scale so small counts are visible
        // 3-stop interpolation: dark → blue → magenta
        if ($t < 0.5) {
            $u = $t / 0.5;
            $r = (int) round(0x14 + ($u * (0x56 - 0x14)));
            $g = (int) round(0x14 + ($u * (0x60 - 0x14)));
            $b = (int) round(0x1a + ($u * (0xfe - 0x1a)));
        } else {
            $u = ($t - 0.5) / 0.5;
            $r = (int) round(0x56 + ($u * (0xc5 - 0x56)));
            $g = (int) round(0x60 + ($u * (0x27 - 0x60)));
            $b = (int) round(0xfe + ($u * (0x9a - 0xfe)));
        }
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    };
@endphp

<section style="background:#000; padding:96px 24px 80px;">
    <div style="max-width:1100px; margin:0 auto;">
        <h2 style="text-align:center; font-size:13px; font-weight:800; color:#fff; letter-spacing:0.16em; text-transform:uppercase; margin:0 0 8px;">
            All Prosecutions by State
        </h2>
        <p style="text-align:center; font-size:14px; color:rgba(255,255,255,0.4); margin:0 0 40px;">
            Hover any state for the count and share of total documented cases.
        </p>

        <div class="state-map-grid" id="state-map-grid">
            @foreach($grid as [$abbr, $row, $col])
                @php
                    $count = $byAbbr[$abbr] ?? 0;
                    $color = $colorFor($count);
                    $pct   = $count > 0 ? round(($count / $total) * 100) : 0;
                @endphp
                <a href="/database?state={{ urlencode($stateNames[$abbr]) }}"
                   class="state-cell"
                   style="grid-row: {{ $row }}; grid-column: {{ $col }}; background: {{ $color }};"
                   data-name="{{ $stateNames[$abbr] }}"
                   data-count="{{ $count }}"
                   data-pct="{{ $pct }}">
                    <span class="state-abbr">{{ $abbr }}</span>
                </a>
            @endforeach
        </div>

        <div class="state-map-legend">
            <span class="legend-label">1</span>
            <div class="legend-bar"></div>
            <span class="legend-label">{{ $max }}</span>
        </div>
    </div>

    <div id="state-map-tooltip" class="state-map-tooltip" hidden>
        <div class="tt-name"></div>
        <div class="tt-pct"></div>
        <div class="tt-count"></div>
    </div>
</section>

<style>
    .state-map-grid {
        display: grid;
        grid-template-rows: repeat(7, 64px);
        grid-template-columns: repeat(11, 1fr);
        gap: 4px;
        max-width: 880px;
        margin: 0 auto;
    }
    .state-cell {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        border: 1px solid rgba(255,255,255,0.08);
        transition: transform 0.12s ease, border-color 0.12s ease, box-shadow 0.12s ease;
        cursor: pointer;
    }
    .state-cell:hover {
        transform: scale(1.05);
        border-color: rgba(255,255,255,0.5);
        box-shadow: 0 0 0 2px rgba(255,255,255,0.15);
        z-index: 2;
    }
    .state-abbr {
        font-size: 12px;
        font-weight: 700;
        color: rgba(255,255,255,0.85);
        letter-spacing: 0.04em;
        text-shadow: 0 1px 2px rgba(0,0,0,0.7);
    }

    .state-map-legend {
        display: flex;
        align-items: center;
        gap: 12px;
        max-width: 320px;
        margin: 28px auto 0;
        font-size: 12px;
        color: rgba(255,255,255,0.55);
    }
    .legend-bar {
        flex: 1;
        height: 6px;
        background: linear-gradient(90deg, #14141a 0%, #2c3a8c 25%, #5660fe 50%, #8d44d6 75%, #c5279a 100%);
        border-radius: 2px;
    }

    .state-map-tooltip {
        position: fixed;
        z-index: 1000;
        background: #ffffff;
        color: #0a0a0a;
        padding: 10px 14px;
        border-radius: 4px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.4);
        font-size: 13px;
        line-height: 1.5;
        pointer-events: none;
        max-width: 220px;
    }
    .state-map-tooltip[hidden] { display: none !important; }
    .state-map-tooltip .tt-name { font-weight: 800; margin-bottom: 4px; }
    .state-map-tooltip .tt-pct, .state-map-tooltip .tt-count { color: rgba(0,0,0,0.7); }

    @media (max-width: 760px) {
        .state-map-grid { grid-template-rows: repeat(7, 44px); }
        .state-abbr { font-size: 10px; }
    }
</style>

<script>
(function () {
    var grid = document.getElementById('state-map-grid');
    var tip  = document.getElementById('state-map-tooltip');
    if (!grid || !tip) return;

    var nameEl  = tip.querySelector('.tt-name');
    var pctEl   = tip.querySelector('.tt-pct');
    var countEl = tip.querySelector('.tt-count');

    function show(cell) {
        nameEl.textContent  = cell.dataset.name;
        var pct  = parseInt(cell.dataset.pct, 10);
        var cnt  = parseInt(cell.dataset.count, 10);
        pctEl.textContent   = (pct > 0 ? pct : '<1') + '% of all defendants';
        countEl.textContent = cnt + ' defendant' + (cnt === 1 ? '' : 's');
        tip.hidden = false;
    }
    function move(e) {
        var x = e.clientX + 14, y = e.clientY + 14;
        var rect = tip.getBoundingClientRect();
        if (x + rect.width > window.innerWidth - 8) x = e.clientX - rect.width - 14;
        if (y + rect.height > window.innerHeight - 8) y = e.clientY - rect.height - 14;
        tip.style.left = x + 'px';
        tip.style.top  = y + 'px';
    }
    function hide() { tip.hidden = true; }

    grid.querySelectorAll('.state-cell').forEach(function (cell) {
        cell.addEventListener('mouseenter', function (e) { show(cell); move(e); });
        cell.addEventListener('mousemove', move);
        cell.addEventListener('mouseleave', hide);
    });
    document.addEventListener('scroll', hide, { passive: true });
})();
</script>
