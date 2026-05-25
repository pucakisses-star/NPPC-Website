@extends('app')

@section('head')
<style>
.bd-wrap { max-width: 1400px; margin: 0 auto; padding: 0 24px 96px; }
.bd-hero { padding: 64px 0 32px; }
.bd-hero h1 { font-size: 3rem; font-weight: 900; color: #fff; line-height: 1.05; margin: 0 0 12px; }
.bd-hero p { font-size: 1.05rem; color: rgba(255,255,255,0.7); max-width: 720px; line-height: 1.7; margin: 0 0 8px; }
.bd-hero .bd-count { font-size: 14px; color: rgba(255,255,255,0.45); text-transform: uppercase; letter-spacing: 0.08em; margin-top: 16px; }

.bd-print-cta { margin: 0 0 32px; padding: 16px 20px; background: rgba(86,96,254,0.08); border: 1px solid rgba(86,96,254,0.25); border-radius: 8px; color: rgba(255,255,255,0.7); font-size: 14px; }
.bd-print-cta strong { color: #fff; }

.bd-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 32px; }
@@media (max-width: 1100px) { .bd-grid { grid-template-columns: 1fr; } }

.bd-month { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; overflow: hidden; }
.bd-month.is-current { border-color: rgba(86,96,254,0.45); }
.bd-month-head { display: flex; align-items: baseline; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.02); }
.bd-month.is-current .bd-month-head { background: rgba(86,96,254,0.08); }
.bd-month-head h2 { margin: 0; font-size: 1.4rem; font-weight: 800; color: #fff; letter-spacing: 0.02em; }
.bd-month-head .bd-month-count { font-size: 12px; color: rgba(255,255,255,0.45); text-transform: uppercase; letter-spacing: 0.06em; }

.bd-weekdays { display: grid; grid-template-columns: repeat(7, 1fr); padding: 8px 8px 0; }
.bd-weekday { text-align: center; font-size: 10px; font-weight: 800; letter-spacing: 0.1em; color: rgba(255,255,255,0.35); padding: 6px 0; text-transform: uppercase; }

.bd-days { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; padding: 4px 8px 12px; }
.bd-cell { min-height: 78px; padding: 6px; border: 1px solid rgba(255,255,255,0.05); border-radius: 6px; display: flex; flex-direction: column; gap: 4px; background: rgba(255,255,255,0.015); }
.bd-cell.is-blank { background: transparent; border-color: transparent; }
.bd-cell.is-today { border-color: #5660fe; background: rgba(86,96,254,0.12); }
.bd-cell.has-birthdays { background: rgba(255,255,255,0.04); }
.bd-cell-day { font-size: 12px; font-weight: 700; color: rgba(255,255,255,0.55); line-height: 1; text-align: right; padding: 2px 2px 0; font-variant-numeric: tabular-nums; }
.bd-cell.is-today .bd-cell-day { color: #5660fe; }

.bd-cell-list { display: flex; flex-direction: column; gap: 2px; overflow: hidden; }
.bd-pill { display: flex; align-items: center; gap: 6px; padding: 2px 4px; border-radius: 3px; background: rgba(86,96,254,0.18); color: #fff; text-decoration: none; font-size: 11px; line-height: 1.2; transition: background 0.15s; min-height: 18px; }
.bd-pill:hover { background: rgba(86,96,254,0.32); color: #fff; }
.bd-pill-avatar { flex: 0 0 14px; width: 14px; height: 14px; border-radius: 50%; background: #1a1a2e center/cover no-repeat; flex-shrink: 0; }
.bd-pill-name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 600; }
.bd-pill-more { font-size: 10px; color: rgba(255,255,255,0.5); padding: 1px 4px; font-weight: 700; }

@@media (max-width: 640px) {
    .bd-wrap { padding: 0 16px 64px; }
    .bd-hero { padding: 32px 0 16px; }
    .bd-hero h1 { font-size: 2rem; }
    .bd-hero p { font-size: 0.95rem; }
    .bd-grid { gap: 20px; }
    .bd-month-head h2 { font-size: 1.2rem; }
    .bd-cell { min-height: 52px; padding: 4px; }
    .bd-cell-day { font-size: 11px; }
    .bd-pill { padding: 1px 3px; min-height: 14px; }
    .bd-pill-avatar { width: 10px; height: 10px; flex-basis: 10px; }
    .bd-pill-name { font-size: 10px; }
    .bd-weekday { font-size: 9px; }
}

@@media print {
    .bd-print-cta, header, footer, nav { display: none !important; }
    body { background: #fff !important; color: #000 !important; }
    .bd-month, .bd-cell, .bd-month-head { background: #fff !important; border-color: #ddd !important; }
    .bd-pill { background: #eef0ff !important; color: #000 !important; }
    .bd-pill-name, .bd-month-head h2, .bd-cell-day { color: #000 !important; }
    .bd-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 12px !important; }
}
</style>
@endsection

@section('body')
<main class="bd-wrap">
    <div class="bd-hero">
        <h1>Political Prisoner Birthdays</h1>
        <p>A year-at-a-glance calendar of birthdays for U.S. political prisoners currently in custody, in exile, or awaiting trial. Use it to send a card on someone's birthday — a small act of solidarity that breaks the isolation of long-term imprisonment.</p>
        <div class="bd-count">{{ number_format($totalCount) }} prisoners listed</div>
    </div>

    <div class="bd-print-cta">
        Print this page (<strong>⌘P / Ctrl+P</strong>) to keep a year-round letter-writing calendar by your desk.
        Click any name to open their full prisoner profile with mailing address and case background.
    </div>

    <div class="bd-grid">
        @foreach($byMonth as $monthIndex => $entries)
            @php
                $monthName = date('F', mktime(0, 0, 0, $monthIndex, 1));
                // Index birthdays by day for O(1) cell lookup
                $byDay = [];
                foreach ($entries as $e) { $byDay[$e['day']][] = $e['prisoner']; }
                // Calendar layout
                $firstWeekday = (int) date('w', mktime(0, 0, 0, $monthIndex, 1, (int) date('Y')));
                $daysInMonth = (int) date('t', mktime(0, 0, 0, $monthIndex, 1, (int) date('Y')));
            @endphp
            <section class="bd-month {{ $monthIndex === $todayMonth ? 'is-current' : '' }}">
                <header class="bd-month-head">
                    <h2>{{ $monthName }}</h2>
                    <span class="bd-month-count">{{ count($entries) }} {{ \Illuminate\Support\Str::plural('birthday', count($entries)) }}</span>
                </header>
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
                            $isToday = ($monthIndex === $todayMonth && $day === $todayDay);
                            $shown = array_slice($people, 0, 2);
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
        @endforeach
    </div>
</main>
@endsection
