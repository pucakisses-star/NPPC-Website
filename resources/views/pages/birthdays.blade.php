@extends('app')

@section('head')
<style>
.bd-wrap { max-width: 1200px; margin: 0 auto; padding: 0 16px 96px; }
.bd-hero { padding: 64px 0 32px; }
.bd-hero h1 { font-size: 3rem; font-weight: 900; color: #fff; line-height: 1.05; margin: 0 0 12px; }
.bd-hero p { font-size: 1.05rem; color: rgba(255,255,255,0.7); max-width: 720px; line-height: 1.7; margin: 0 0 8px; }
.bd-hero .bd-count { font-size: 14px; color: rgba(255,255,255,0.45); text-transform: uppercase; letter-spacing: 0.08em; margin-top: 16px; }

.bd-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; margin-top: 32px; }
@@media (max-width: 1000px) { .bd-grid { grid-template-columns: repeat(2, 1fr); } }
@@media (max-width: 640px) { .bd-grid { grid-template-columns: 1fr; } }

.bd-month { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; }
.bd-month-head { display: flex; align-items: baseline; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.02); }
.bd-month-head h2 { margin: 0; font-size: 1.4rem; font-weight: 800; color: #fff; letter-spacing: 0.02em; }
.bd-month-head .bd-month-count { font-size: 12px; color: rgba(255,255,255,0.45); text-transform: uppercase; letter-spacing: 0.06em; }
.bd-month.is-current { border-color: rgba(86,96,254,0.45); }
.bd-month.is-current .bd-month-head { background: rgba(86,96,254,0.08); }

.bd-list { list-style: none; margin: 0; padding: 0; }
.bd-row { display: flex; align-items: center; gap: 12px; padding: 10px 20px; border-top: 1px solid rgba(255,255,255,0.04); transition: background 0.15s; }
.bd-row:first-child { border-top: none; }
.bd-row:hover { background: rgba(255,255,255,0.04); }
.bd-row.is-today { background: rgba(86,96,254,0.12); }

.bd-day { flex: 0 0 32px; font-size: 14px; color: rgba(255,255,255,0.5); font-variant-numeric: tabular-nums; }
.bd-row.is-today .bd-day { color: #5660fe; font-weight: 700; }
.bd-avatar { flex: 0 0 36px; width: 36px; height: 36px; border-radius: 50%; background: #1a1a2e center/cover no-repeat; flex-shrink: 0; }
.bd-link { flex: 1; min-width: 0; color: #fff; text-decoration: none; font-size: 14.5px; line-height: 1.3; }
.bd-link:hover { color: #5660fe; }
.bd-link .bd-aka { display: block; font-size: 11.5px; color: rgba(255,255,255,0.4); margin-top: 2px; }

.bd-empty { padding: 20px; font-size: 13px; color: rgba(255,255,255,0.3); text-align: center; }

.bd-print-cta { margin-top: 24px; padding: 16px 20px; background: rgba(86,96,254,0.08); border: 1px solid rgba(86,96,254,0.25); border-radius: 8px; color: rgba(255,255,255,0.7); font-size: 14px; }
.bd-print-cta strong { color: #fff; }

@@media print {
    .bd-print-cta, header, footer, nav { display: none !important; }
    body { background: #fff !important; color: #000 !important; }
    .bd-month, .bd-row, .bd-month-head { background: #fff !important; border-color: #ddd !important; }
    .bd-link, .bd-month-head h2 { color: #000 !important; }
    .bd-aka, .bd-day, .bd-month-head .bd-month-count { color: #555 !important; }
    .bd-grid { gap: 16px !important; }
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
            @php $monthName = date('F', mktime(0, 0, 0, $monthIndex, 1)); @endphp
            <section class="bd-month {{ $monthIndex === $todayMonth ? 'is-current' : '' }}">
                <header class="bd-month-head">
                    <h2>{{ $monthName }}</h2>
                    <span class="bd-month-count">{{ count($entries) }} {{ \Illuminate\Support\Str::plural('birthday', count($entries)) }}</span>
                </header>
                @if (count($entries))
                    <ul class="bd-list">
                        @foreach($entries as $entry)
                            @php $p = $entry['prisoner']; @endphp
                            <li class="bd-row {{ ($monthIndex === $todayMonth && $entry['day'] === $todayDay) ? 'is-today' : '' }}">
                                <span class="bd-day">{{ str_pad($entry['day'], 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="bd-avatar" @if($p->photo_url) style="background-image:url('{{ $p->photo_url }}')" @endif></span>
                                <a class="bd-link" href="{{ $p->url }}">
                                    {{ $p->name }}
                                    @if ($p->aka)
                                        <span class="bd-aka">aka {{ $p->aka }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="bd-empty">No birthdays on file</div>
                @endif
            </section>
        @endforeach
    </div>
</main>
@endsection
