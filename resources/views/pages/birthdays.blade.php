@extends('app')

@section('head')
<style>
.bd-wrap { max-width: 1100px; margin: 0 auto; padding: 0 24px 96px; }
.bd-hero { padding: 56px 0 24px; }
.bd-hero h1 { font-size: 2.5rem; font-weight: 900; color: #fff; line-height: 1.05; margin: 0 0 12px; }
.bd-hero p { font-size: 1.05rem; color: rgba(255,255,255,0.7); max-width: 720px; line-height: 1.7; margin: 0 0 8px; }
.bd-hero .bd-count { font-size: 14px; color: rgba(255,255,255,0.45); text-transform: uppercase; letter-spacing: 0.08em; margin-top: 16px; }

.bd-toolbar { display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: 16px; margin: 24px 0 16px; }
.bd-toolbar-center { display: flex; align-items: center; justify-content: center; gap: 12px; }
.bd-toolbar-right { display: flex; justify-content: flex-end; }
.bd-month-name { font-size: 2rem; font-weight: 900; color: #fff; min-width: 200px; text-align: center; }
.bd-nav-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 44px; height: 44px; padding: 0 14px; border: 1px solid rgba(255,255,255,0.18); border-radius: 6px; background: rgba(255,255,255,0.04); color: #fff; text-decoration: none; font-size: 14px; font-weight: 600; transition: background 0.15s, border-color 0.15s; cursor: pointer; }
.bd-nav-btn:hover { background: rgba(86,96,254,0.18); border-color: #5660fe; color: #fff; }
.bd-today-btn { font-weight: 700; }

.bd-months-bar { display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: 24px; }
.bd-months-bar a { padding: 6px 14px; font-size: 13px; font-weight: 600; border: 1px solid rgba(255,255,255,0.12); color: rgba(255,255,255,0.6); background: transparent; border-radius: 4px; text-decoration: none; transition: all 0.15s; cursor: pointer; }
.bd-months-bar a:hover { border-color: #5660fe; color: #fff; }
.bd-months-bar a.active { background: #5660fe; border-color: #5660fe; color: #fff; }

.bd-month { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; overflow: hidden; display: none; }
.bd-month.is-visible { display: block; }

.bd-weekdays { display: grid; grid-template-columns: repeat(7, 1fr); padding: 12px 12px 0; }
.bd-weekday { text-align: center; font-size: 11px; font-weight: 800; letter-spacing: 0.1em; color: rgba(255,255,255,0.4); padding: 8px 0; text-transform: uppercase; }

.bd-days { display: grid; grid-template-columns: repeat(7, 1fr); grid-auto-rows: 1fr; gap: 6px; padding: 6px 12px 16px; }
.bd-cell { height: 120px; padding: 10px; border: 1px solid rgba(255,255,255,0.06); border-radius: 6px; display: flex; flex-direction: column; gap: 6px; background: rgba(255,255,255,0.015); overflow: hidden; }
.bd-cell.is-blank { background: transparent; border-color: transparent; }
.bd-cell.is-today { border-color: #5660fe; background: rgba(86,96,254,0.12); }
.bd-cell.has-birthdays { background: rgba(255,255,255,0.04); }
.bd-cell-day { font-size: 13px; font-weight: 700; color: rgba(255,255,255,0.6); line-height: 1; text-align: right; padding: 2px 4px 0; font-variant-numeric: tabular-nums; }
.bd-cell.is-today .bd-cell-day { color: #5660fe; }

.bd-cell-list { display: flex; flex-direction: column; gap: 3px; overflow: hidden; }
.bd-pill { display: flex; align-items: center; gap: 8px; padding: 4px 6px; border-radius: 4px; background: rgba(86,96,254,0.2); color: #fff; text-decoration: none; font-size: 12px; line-height: 1.2; transition: background 0.15s; min-height: 22px; }
.bd-pill:hover { background: rgba(86,96,254,0.36); color: #fff; }
.bd-pill-avatar { flex: 0 0 18px; width: 18px; height: 18px; border-radius: 50%; background: #1a1a2e center/cover no-repeat; flex-shrink: 0; }
.bd-pill-name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 600; }
.bd-pill-more { font-size: 11px; color: rgba(255,255,255,0.55); padding: 1px 4px; font-weight: 700; }

@@media (max-width: 768px) {
    .bd-wrap { padding: 0 16px 64px; }
    .bd-hero { padding: 32px 0 16px; }
    .bd-hero h1 { font-size: 1.8rem; }
    .bd-hero p { font-size: 0.95rem; }
    .bd-toolbar { grid-template-columns: auto 1fr auto; }
    .bd-month-name { font-size: 1.4rem; min-width: 0; }
    .bd-nav-btn { min-width: 40px; height: 40px; font-size: 13px; padding: 0 10px; }
    .bd-cell { height: 70px; padding: 6px; }
    .bd-cell-day { font-size: 11px; }
    .bd-pill { padding: 2px 4px; min-height: 16px; }
    .bd-pill-avatar { width: 12px; height: 12px; flex-basis: 12px; }
    .bd-pill-name { font-size: 10px; }
    .bd-weekday { font-size: 10px; padding: 6px 0; }
}
</style>
@endsection

@section('body')
<main class="bd-wrap">
    <div class="bd-hero">
        <h1>Political Prisoner Birthdays</h1>
        <p>Birthdays of U.S. political prisoners currently incarcerated. Send a card on someone's birthday — a small act of solidarity that breaks the isolation of long-term imprisonment.</p>
        <div class="bd-count">{{ number_format($totalCount) }} prisoners listed</div>
    </div>

    <div class="bd-toolbar">
        <div></div>
        <div class="bd-toolbar-center">
            <button class="bd-nav-btn" data-bd-prev type="button" aria-label="Previous month">&lsaquo;</button>
            <span class="bd-month-name" data-bd-name></span>
            <button class="bd-nav-btn" data-bd-next type="button" aria-label="Next month">&rsaquo;</button>
        </div>
        <div class="bd-toolbar-right">
            <button class="bd-nav-btn bd-today-btn" data-bd-today type="button">Today</button>
        </div>
    </div>

    <div class="bd-months-bar">
        @for ($m = 1; $m <= 12; $m++)
            <a data-bd-month="{{ $m }}" data-no-fade href="?month={{ $m }}">{{ date('M', mktime(0, 0, 0, $m, 1)) }}</a>
        @endfor
    </div>

    @for ($m = 1; $m <= 12; $m++)
        @php
            $entries = $byMonth[$m] ?? [];
            $byDay = [];
            foreach ($entries as $e) { $byDay[$e['day']][] = $e['prisoner']; }
            $firstWeekday = (int) date('w', mktime(0, 0, 0, $m, 1, (int) date('Y')));
            $daysInMonth = (int) date('t', mktime(0, 0, 0, $m, 1, (int) date('Y')));
        @endphp
        <section class="bd-month" data-bd-grid="{{ $m }}" data-bd-grid-name="{{ date('F Y', mktime(0, 0, 0, $m, 1, (int) date('Y'))) }}">
            <div class="bd-weekdays">
                <div class="bd-weekday">Sun</div>
                <div class="bd-weekday">Mon</div>
                <div class="bd-weekday">Tue</div>
                <div class="bd-weekday">Wed</div>
                <div class="bd-weekday">Thu</div>
                <div class="bd-weekday">Fri</div>
                <div class="bd-weekday">Sat</div>
            </div>
            <div class="bd-days">
                @for ($i = 0; $i < $firstWeekday; $i++)
                    <div class="bd-cell is-blank"></div>
                @endfor
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $people = $byDay[$day] ?? [];
                        $isToday = ($m === $todayMonth && $day === $todayDay);
                        $shown = array_slice($people, 0, 3);
                        $overflow = count($people) - count($shown);
                    @endphp
                    <div class="bd-cell {{ $isToday ? 'is-today' : '' }} {{ count($people) ? 'has-birthdays' : '' }}">
                        <div class="bd-cell-day">{{ $day }}</div>
                        <div class="bd-cell-list">
                            @foreach ($shown as $p)
                                <a class="bd-pill" href="{{ $p->url }}" title="{{ $p->name }}">
                                    <span class="bd-pill-avatar" @if($p->photo_url) style="background-image:url('{{ $p->photo_url }}')" @endif></span>
                                    <span class="bd-pill-name">{{ $p->name }}</span>
                                </a>
                            @endforeach
                            @if ($overflow > 0)
                                <span class="bd-pill-more">+{{ $overflow }} more</span>
                            @endif
                        </div>
                    </div>
                @endfor
            </div>
        </section>
    @endfor
</main>

<script>
(function () {
    var TODAY_MONTH = {{ $todayMonth }};
    var initial = parseInt(new URLSearchParams(location.search).get('month') || '{{ $month }}', 10);
    if (isNaN(initial) || initial < 1 || initial > 12) initial = TODAY_MONTH;

    var grids = document.querySelectorAll('[data-bd-grid]');
    var monthLinks = document.querySelectorAll('[data-bd-month]');
    var nameEl = document.querySelector('[data-bd-name]');
    var prevBtn = document.querySelector('[data-bd-prev]');
    var nextBtn = document.querySelector('[data-bd-next]');
    var todayBtn = document.querySelector('[data-bd-today]');

    function show(month, pushHistory) {
        if (month < 1) month = 12;
        if (month > 12) month = 1;

        grids.forEach(function (g) {
            var m = parseInt(g.getAttribute('data-bd-grid'), 10);
            g.classList.toggle('is-visible', m === month);
            if (m === month) nameEl.textContent = g.getAttribute('data-bd-grid-name');
        });
        monthLinks.forEach(function (a) {
            var m = parseInt(a.getAttribute('data-bd-month'), 10);
            a.classList.toggle('active', m === month);
        });

        if (pushHistory !== false) {
            var url = new URL(location.href);
            url.searchParams.set('month', month);
            history.replaceState({ month: month }, '', url.toString());
        }
    }

    monthLinks.forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            show(parseInt(a.getAttribute('data-bd-month'), 10));
        });
    });
    prevBtn.addEventListener('click', function () {
        var current = parseInt(nameEl.dataset.current || initial, 10);
        var next = current - 1;
        nameEl.dataset.current = next < 1 ? 12 : next;
        show(parseInt(nameEl.dataset.current, 10));
    });
    nextBtn.addEventListener('click', function () {
        var current = parseInt(nameEl.dataset.current || initial, 10);
        var next = current + 1;
        nameEl.dataset.current = next > 12 ? 1 : next;
        show(parseInt(nameEl.dataset.current, 10));
    });
    todayBtn.addEventListener('click', function () {
        nameEl.dataset.current = TODAY_MONTH;
        show(TODAY_MONTH);
    });

    // Sync the prev/next pointer when month-strip / direct nav is used.
    var observer = new MutationObserver(function () {
        for (var i = 0; i < grids.length; i++) {
            if (grids[i].classList.contains('is-visible')) {
                nameEl.dataset.current = grids[i].getAttribute('data-bd-grid');
                return;
            }
        }
    });
    grids.forEach(function (g) { observer.observe(g, { attributes: true, attributeFilter: ['class'] }); });

    show(initial, false);
})();
</script>
@endsection
