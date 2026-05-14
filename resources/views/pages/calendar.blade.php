@extends('app')

@section('head')
<style>
    .cal-page { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
    .cal-header { display: flex; align-items: center; justify-content: space-between; padding: 48px 0 40px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 48px; }
    .cal-header-left { display: flex; align-items: center; gap: 16px; }
    .cal-header-title { font-size: 18px; font-weight: 800; color: #fff; }
    .cal-toggle { display: flex; align-items: center; gap: 12px; }
    .cal-toggle-label { font-size: 14px; font-weight: 600; color: rgba(255,255,255,0.5); }
    .cal-toggle-label.active { color: #fff; }
    .cal-toggle-switch { width: 48px; height: 26px; background: #333; border-radius: 13px; cursor: pointer; position: relative; transition: background 0.2s; }
    .cal-toggle-switch.on { background: #5660fe; }
    .cal-toggle-switch::after { content: ''; position: absolute; top: 3px; left: 3px; width: 20px; height: 20px; background: #fff; border-radius: 50%; transition: left 0.2s; }
    .cal-toggle-switch.on::after { left: 25px; }
    .cal-month-select { display: flex; align-items: center; gap: 8px; }
    .cal-month-name { font-size: 2.5rem; font-weight: 900; color: #fff; }
    .cal-month-btn { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; border-radius: 4px; text-decoration: none; font-size: 18px; transition: background 0.15s; }
    .cal-month-btn:hover { background: rgba(255,255,255,0.12); }
    .cal-months-bar { display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: 32px; }
    .cal-months-btn { padding: 6px 16px; font-size: 13px; font-weight: 600; border: 1px solid rgba(255,255,255,0.15); color: rgba(255,255,255,0.6); background: transparent; cursor: pointer; border-radius: 4px; text-decoration: none; transition: all 0.15s; }
    .cal-months-btn:hover { border-color: #5660fe; color: #fff; }
    .cal-months-btn.active { background: #5660fe; border-color: #5660fe; color: #fff; }

    /* Month grid */
    .cal-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; padding-bottom: 80px; }
    .cal-card { border: 1px solid rgba(255,255,255,0.12); border-radius: 4px; overflow: hidden; transition: border-color 0.2s; cursor: pointer; text-decoration: none; display: flex; flex-direction: column; }
    .cal-card:hover { border-color: rgba(255,255,255,0.3); }
    .cal-card.today { border-color: #5660fe; border-width: 2px; }
    .cal-card-top { padding: 24px 20px 20px; text-align: center; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .cal-card-day { font-size: 4rem; font-weight: 900; color: #fff; line-height: 1; margin-bottom: 12px; }
    .cal-card.today .cal-card-day { color: #5660fe; }
    .cal-card-title { font-size: 14px; font-weight: 600; color: rgba(255,255,255,0.85); line-height: 1.4; }
    .cal-card-divider { width: 24px; height: 2px; background: rgba(255,255,255,0.3); margin: 12px auto 6px; }
    .cal-card-year { font-size: 13px; color: rgba(255,255,255,0.5); font-weight: 600; }
    .cal-card-image { height: 160px; overflow: hidden; flex-shrink: 0; }
    .cal-card-image img { width: 100%; height: 100%; object-fit: cover; filter: grayscale(40%); transition: filter 0.3s; }
    .cal-card:hover .cal-card-image img { filter: grayscale(0); }
    .cal-empty-card { border: 1px dashed rgba(255,255,255,0.08); border-radius: 4px; display: flex; align-items: center; justify-content: center; min-height: 280px; }
    .cal-empty-day { font-size: 4rem; font-weight: 900; color: rgba(255,255,255,0.06); line-height: 1; }

    /* Day view */
    .cal-day-view { display: flex; gap: 0; min-height: 70vh; padding-bottom: 80px; }
    .cal-day-left { flex: 0 0 320px; display: flex; flex-direction: column; align-items: center; justify-content: center; border-right: 1px solid rgba(255,255,255,0.1); padding: 48px; }
    .cal-day-num { font-size: 8rem; font-weight: 900; color: #fff; line-height: 1; }
    .cal-day-month { font-size: 4rem; font-weight: 900; color: #fff; line-height: 1; text-transform: uppercase; }
    .cal-day-share { margin-top: 48px; text-align: center; }
    .cal-day-share-title { font-size: 16px; font-weight: 800; color: #fff; margin-bottom: 8px; }
    .cal-day-share-text { font-size: 13px; color: rgba(255,255,255,0.5); margin-bottom: 16px; line-height: 1.5; }
    .cal-day-right { flex: 1; padding: 48px 64px; }
    .cal-day-dateline { font-size: 14px; color: rgba(255,255,255,0.5); margin-bottom: 24px; display: flex; align-items: center; gap: 12px; }
    .cal-day-dateline-bar { width: 24px; height: 2px; background: rgba(255,255,255,0.3); }
    .cal-day-title { font-size: 2.5rem; font-weight: 900; color: #fff; line-height: 1.15; margin-bottom: 32px; text-align: center; }
    .cal-day-image { width: 100%; max-width: 500px; margin: 0 auto 32px; border-radius: 4px; overflow: hidden; }
    .cal-day-image img { width: 100%; height: auto; }
    .cal-day-desc { font-size: 16px; color: rgba(255,255,255,0.7); line-height: 1.8; max-width: 600px; margin: 0 auto; }
    .cal-day-nav { display: flex; gap: 12px; justify-content: center; margin-top: 40px; }
    .cal-day-nav-btn { padding: 10px 24px; border: 1px solid rgba(255,255,255,0.2); color: #fff; text-decoration: none; font-size: 14px; font-weight: 600; border-radius: 4px; transition: all 0.15s; }
    .cal-day-nav-btn:hover { border-color: #5660fe; }

    @media (max-width: 900px) { .cal-grid { grid-template-columns: repeat(2, 1fr); } .cal-day-view { flex-direction: column; } .cal-day-left { flex: auto; padding: 32px; } .cal-day-right { padding: 32px 24px; } }
    @media (max-width: 500px) { .cal-grid { grid-template-columns: 1fr; } .cal-month-name { font-size: 1.8rem; } }
</style>
@endsection

@section('body')
<div class="cal-page">
    <div class="cal-header">
        <div class="cal-header-left">
            <div class="cal-header-title">A History of Political Prisoners</div>
        </div>

        {{-- Day/Month toggle --}}
        <div class="cal-toggle">
            <span class="cal-toggle-label {{ $view === 'day' ? 'active' : '' }}">Day</span>
            <div class="cal-toggle-switch {{ $view === 'month' ? 'on' : '' }}" onclick="toggleCalView()" id="cal-toggle"></div>
            <span class="cal-toggle-label {{ $view === 'month' ? 'active' : '' }}">Month</span>
        </div>

        <div class="cal-month-select">
            <a href="/calendar?month={{ $month > 1 ? $month - 1 : 12 }}&view={{ $view }}" class="cal-month-btn" data-no-fade>&larr;</a>
            <div class="cal-month-name">{{ $monthName }}</div>
            <a href="/calendar?month={{ $month < 12 ? $month + 1 : 1 }}&view={{ $view }}" class="cal-month-btn" data-no-fade>&rarr;</a>
        </div>
    </div>

    <div class="cal-months-bar">
        @php $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; @endphp
        @for($m = 1; $m <= 12; $m++)
            <a href="/calendar?month={{ $m }}&view={{ $view }}" class="cal-months-btn {{ $month === $m ? 'active' : '' }}" data-no-fade>{{ $monthNames[$m-1] }}</a>
        @endfor
    </div>

    @if($view === 'day' && $dayEntry)
        {{-- DAY VIEW --}}
        <div class="cal-day-view">
            <div class="cal-day-left">
                <div class="cal-day-num">{{ str_pad($dayEntry->day, 2, '0', STR_PAD_LEFT) }}</div>
                <div class="cal-day-month">{{ strtoupper(substr($monthName, 0, 3)) }}</div>

                <div class="cal-day-share">
                    <div class="cal-day-share-title">Share this</div>
                    <div class="cal-day-share-text">Help confront our history to overcome injustice.</div>
                </div>
            </div>
            <div class="cal-day-right">
                <div class="cal-day-dateline">
                    <span>On this day</span>
                    <span class="cal-day-dateline-bar"></span>
                    <span>{{ $monthName }} {{ str_pad($dayEntry->day, 2, '0', STR_PAD_LEFT) }}, {{ $dayEntry->year }}</span>
                </div>

                <h1 class="cal-day-title">{{ $dayEntry->title }}</h1>

                @if($dayEntry->image)
                    <div class="cal-day-image">
                        <img src="{{ Storage::url($dayEntry->image) }}" alt="{{ $dayEntry->title }}">
                    </div>
                @endif

                {{-- Prisoner info card --}}
                @if($dayEntry->prisoner)
                    @php $p = $dayEntry->prisoner; @endphp
                    <div style="border:1px solid rgba(255,255,255,0.1); border-radius:8px; padding:24px; margin:32px auto; max-width:600px; display:flex; gap:20px; align-items:flex-start;">
                        @if($p->photo)
                            <img src="{{ asset('storage/'.$p->photo) }}" style="width:80px; height:80px; border-radius:50%; object-fit:cover; flex-shrink:0;">
                        @endif
                        <div style="flex:1;">
                            <a href="/prisoner/{{ $p->slug ?: $p->id }}" style="font-size:18px; font-weight:800; color:#fff; text-decoration:none; display:block; margin-bottom:4px;">{{ $p->name }}</a>
                            @if($p->aka)<div style="font-size:13px; color:rgba(255,255,255,0.4); margin-bottom:8px; font-style:italic;">AKA: {{ $p->aka }}</div>@endif
                            <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:8px;">
                                @if($p->in_custody)<span style="font-size:11px; font-weight:700; text-transform:uppercase; padding:2px 8px; border-radius:3px; background:rgba(239,68,68,0.15); color:#ef4444; border:1px solid rgba(239,68,68,0.3);">In Custody</span>@endif
                                @if($p->released)<span style="font-size:11px; font-weight:700; text-transform:uppercase; padding:2px 8px; border-radius:3px; background:rgba(34,197,94,0.15); color:#22c55e; border:1px solid rgba(34,197,94,0.3);">Released</span>@endif
                                @if($p->currently_in_exile || $p->in_exile)<span style="font-size:11px; font-weight:700; text-transform:uppercase; padding:2px 8px; border-radius:3px; background:rgba(234,179,8,0.15); color:#eab308; border:1px solid rgba(234,179,8,0.3);">In Exile</span>@endif
                                @if($p->awaiting_trial)<span style="font-size:11px; font-weight:700; text-transform:uppercase; padding:2px 8px; border-radius:3px; background:rgba(59,130,246,0.15); color:#3b82f6; border:1px solid rgba(59,130,246,0.3);">Awaiting Trial</span>@endif
                            </div>
                            @if($p->description)
                                <div style="font-size:14px; color:rgba(255,255,255,0.6); line-height:1.6;">{{ \Illuminate\Support\Str::limit(strip_tags($p->description), 200) }}</div>
                            @endif
                            <a href="/prisoner/{{ $p->slug ?: $p->id }}" style="display:inline-block; margin-top:12px; font-size:13px; font-weight:700; color:#5660fe; text-decoration:none;">View full profile &rarr;</a>
                        </div>
                    </div>
                @endif

                @if($dayEntry->description)
                    <div class="cal-day-desc">
                        @foreach(explode("\n", $dayEntry->description) as $para)
                            @if(trim($para))<p style="margin-bottom:1.25em;">{{ $para }}</p>@endif
                        @endforeach
                    </div>
                @endif

                {{-- Prev/Next navigation --}}
                <div class="cal-day-nav">
                    @php
                        $prevEntry = $entries->where('day', '<', $dayEntry->day)->last();
                        $nextEntry = $entries->where('day', '>', $dayEntry->day)->first();
                    @endphp
                    @if($prevEntry)
                        <a href="/calendar?month={{ $month }}&view=day&day={{ $prevEntry->day }}" class="cal-day-nav-btn" data-no-fade>&larr; {{ $monthName }} {{ $prevEntry->day }}</a>
                    @endif
                    @if($nextEntry)
                        <a href="/calendar?month={{ $month }}&view=day&day={{ $nextEntry->day }}" class="cal-day-nav-btn" data-no-fade>{{ $monthName }} {{ $nextEntry->day }} &rarr;</a>
                    @endif
                </div>
            </div>
        </div>
    @elseif($view === 'day' && !$dayEntry)
        <div style="text-align:center; padding:80px 0; color:rgba(255,255,255,0.4);">No entry for this day. Try the month view to see all entries.</div>
    @else
        {{-- MONTH VIEW --}}
        <div class="cal-grid">
            @php
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, (int) date('Y'));
                $entriesByDay = $entries->keyBy('day');
            @endphp

            @for($d = 1; $d <= $daysInMonth; $d++)
                @if(isset($entriesByDay[$d]))
                    @php $entry = $entriesByDay[$d]; @endphp
                    <a href="/calendar?month={{ $month }}&view=day&day={{ $d }}" class="cal-card {{ ($month === $currentMonth && $d === $today) ? 'today' : '' }}" data-no-fade>
                        <div class="cal-card-top">
                            @if($entry->prisoner && $entry->prisoner->photo)
                                <img src="{{ asset('storage/'.$entry->prisoner->photo) }}" style="width:40px; height:40px; border-radius:50%; object-fit:cover; margin:0 auto 8px; display:block; border:2px solid rgba(255,255,255,0.15);">
                            @endif
                            <div class="cal-card-day">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</div>
                            <div class="cal-card-title">{{ $entry->title }}</div>
                            <div class="cal-card-divider"></div>
                            <div class="cal-card-year">{{ $entry->year }}</div>
                        </div>
                        @if($entry->image)
                        <div class="cal-card-image">
                            <img src="{{ Storage::url($entry->image) }}" alt="{{ $entry->title }}">
                        </div>
                        @elseif($entry->prisoner && $entry->prisoner->photo)
                        <div class="cal-card-image">
                            <img src="{{ asset('storage/'.$entry->prisoner->photo) }}" alt="{{ $entry->title }}">
                        </div>
                        @endif
                    </a>
                @else
                    <div class="cal-empty-card">
                        <div class="cal-empty-day">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</div>
                    </div>
                @endif
            @endfor
        </div>
    @endif
</div>

<script>
function toggleCalView() {
    var toggle = document.getElementById('cal-toggle');
    var isMonth = toggle.classList.contains('on');
    var newView = isMonth ? 'day' : 'month';
    var url = new URL(window.location);
    url.searchParams.set('view', newView);
    window.location.href = url.toString();
}
</script>
@endsection
