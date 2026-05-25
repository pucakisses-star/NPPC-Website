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
    .cal-grid { display: grid; grid-template-columns: repeat(4, 1fr); grid-auto-rows: 1fr; gap: 24px; padding-bottom: 80px; }
    .cal-card { border: 1px solid rgba(255,255,255,0.12); border-radius: 4px; overflow: hidden; transition: border-color 0.2s; cursor: pointer; text-decoration: none; display: flex; flex-direction: column; height: 100%; }
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

    /* Black left-to-right curtain wipe between calendar views.
       Adapted from the EJI "A History of Racial Injustice" calendar:
       a single full-viewport panel slides in from the left, holds while
       the next page loads, then slides off to the right.

       --cal-wipe-top is set from JS to the bottom of .cal-header so
       the curtain only covers the content area, leaving the site nav
       and the title / Day-Month toggle / month picker visible. */
    .cal-wipe { display: block; position: fixed; inset: 0; pointer-events: none; z-index: 99999; }
    .cal-wipe span { position: fixed; left: -100%; top: var(--cal-wipe-top, 0px); height: calc(100vh - var(--cal-wipe-top, 0px)); width: 100%; background-color: #000; transform: translate3d(0,0,0); }
    .cal-wipe.page-load span { transform: translate3d(100%, 0, 0); }
    .cal-wipe.in span  { animation: cal-wipe-in  1.5s cubic-bezier(.59,.08,.39,.95) forwards; }
    .cal-wipe.out span { animation: cal-wipe-out 1.5s cubic-bezier(.59,.08,.39,.95) forwards; }
    @keyframes cal-wipe-in  { 0% { transform: translate3d(0, 0, 0); }    35%, 65% { transform: translate3d(100%, 0, 0); } 100% { transform: translate3d(100%, 0, 0); } }
    @keyframes cal-wipe-out { 0% { transform: translate3d(100%, 0, 0); } 35%, 65% { transform: translate3d(200%, 0, 0); } 100% { transform: translate3d(200%, 0, 0); } }
    .cal-empty-card { border: 1px dashed rgba(255,255,255,0.08); border-radius: 4px; display: flex; align-items: center; justify-content: center; min-height: 280px; height: 100%; }
    .cal-empty-day { font-size: 4rem; font-weight: 900; color: rgba(255,255,255,0.06); line-height: 1; }

    /* Day view */
    .cal-day-view { display: flex; gap: 0; min-height: 70vh; padding-bottom: 80px; }
    .cal-day-left { flex: 0 0 320px; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; border-right: 1px solid rgba(255,255,255,0.1); padding: 48px; position: sticky; top: 108px; align-self: flex-start; max-height: calc(100vh - 108px); overflow-y: auto; }
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

    @media (max-width: 900px) { .cal-grid { grid-template-columns: repeat(2, 1fr); } .cal-day-view { flex-direction: column; } .cal-day-left { flex: auto; padding: 32px; position: static; max-height: none; overflow: visible; align-self: auto; } .cal-day-right { padding: 32px 24px; } }
    @media (max-width: 500px) {
        .cal-page { padding: 0 16px; }
        .cal-grid { grid-template-columns: 1fr; gap: 16px; }
        .cal-month-name { font-size: 1.8rem; }
        .cal-card-day { font-size: 2.5rem; }
        .cal-empty-day { font-size: 2.5rem; }
        .cal-day-num { font-size: 5rem; }
        .cal-day-month { font-size: 2.5rem; }
        .cal-day-title { font-size: 1.6rem; margin-bottom: 20px; }
        .cal-day-left { padding: 24px 16px; }
        .cal-day-right { padding: 24px 16px; }
    }
</style>
@endsection

@section('body')
<div class="cal-wipe page-load"><span></span></div>
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

    @if($view === 'day' && $dayEntries->isNotEmpty())
        {{-- DAY VIEW --}}
        <div class="cal-day-view">
            <div class="cal-day-left">
                <div class="cal-day-num">{{ str_pad($selectedDay, 2, '0', STR_PAD_LEFT) }}</div>
                <div class="cal-day-month">{{ strtoupper(substr($monthName, 0, 3)) }}</div>

                @php
                    $firstEntry = $dayEntries->first();
                    $shareDate = $monthName.' '.str_pad($selectedDay, 2, '0', STR_PAD_LEFT);
                    if ($dayEntries->count() === 1) {
                        $shareText = 'On this day in '.$firstEntry->year.': '.$firstEntry->title.' — NPPC';
                    } else {
                        $shareText = 'On this day ('.$shareDate.') in the U.S. political-prisoner record — NPPC';
                    }
                    $shareUrl  = request()->fullUrl();
                    $encText   = rawurlencode($shareText);
                    $encUrl    = rawurlencode($shareUrl);
                    $twitterUrl  = 'https://twitter.com/intent/tweet?text='.$encText.'&url='.$encUrl;
                    $facebookUrl = 'https://www.facebook.com/sharer/sharer.php?u='.$encUrl;
                    $blueskyUrl  = 'https://bsky.app/intent/compose?text='.rawurlencode($shareText.' '.$shareUrl);
                    $threadsUrl  = 'https://www.threads.net/intent/post?text='.rawurlencode($shareText.' '.$shareUrl);
                @endphp
                <div class="cal-day-share">
                    <div class="cal-day-share-title">Share this</div>
                    <div class="cal-day-share-text">Help confront our history to overcome injustice.</div>
                    <div style="display:flex; justify-content:center; gap:14px; margin-top:14px; flex-wrap:wrap;">
                        {{-- Twitter / X --}}
                        <a href="{{ $twitterUrl }}" target="_blank" rel="noopener" title="Share on X (Twitter)" aria-label="Share on X"
                           style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; border:1px solid rgba(255,255,255,0.15); border-radius:50%; color:rgba(255,255,255,0.75); transition:all 0.15s;"
                           onmouseover="this.style.color='#fff'; this.style.borderColor='rgba(255,255,255,0.4)';"
                           onmouseout="this.style.color='rgba(255,255,255,0.75)'; this.style.borderColor='rgba(255,255,255,0.15)';">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        {{-- Facebook --}}
                        <a href="{{ $facebookUrl }}" target="_blank" rel="noopener" title="Share on Facebook" aria-label="Share on Facebook"
                           style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; border:1px solid rgba(255,255,255,0.15); border-radius:50%; color:rgba(255,255,255,0.75); transition:all 0.15s;"
                           onmouseover="this.style.color='#fff'; this.style.borderColor='rgba(255,255,255,0.4)';"
                           onmouseout="this.style.color='rgba(255,255,255,0.75)'; this.style.borderColor='rgba(255,255,255,0.15)';">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.099 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.412c0-3.022 1.792-4.69 4.533-4.69 1.312 0 2.686.235 2.686.235v2.964h-1.515c-1.492 0-1.957.93-1.957 1.886v2.266h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.099 24 12.073"/></svg>
                        </a>
                        {{-- Bluesky --}}
                        <a href="{{ $blueskyUrl }}" target="_blank" rel="noopener" title="Share on Bluesky" aria-label="Share on Bluesky"
                           style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; border:1px solid rgba(255,255,255,0.15); border-radius:50%; color:rgba(255,255,255,0.75); transition:all 0.15s;"
                           onmouseover="this.style.color='#fff'; this.style.borderColor='rgba(255,255,255,0.4)';"
                           onmouseout="this.style.color='rgba(255,255,255,0.75)'; this.style.borderColor='rgba(255,255,255,0.15)';">
                            <svg width="16" height="16" viewBox="0 0 600 530" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M135.72 44.03C202.216 93.951 273.74 195.17 300 249.49c26.262-54.316 97.782-155.54 164.28-205.46C512.26 8.022 590-19.503 590 68.997c0 17.668-10.132 148.42-16.073 169.65-20.65 73.792-95.95 92.612-162.97 81.187 117.13 19.93 146.97 86.115 82.62 152.3-122.16 125.62-175.51-31.516-189.18-71.79-2.503-7.382-3.676-10.832-3.687-7.898-0.011-2.934-1.183 0.517-3.687 7.898-13.674 40.274-67.022 197.41-189.18 71.79-64.343-66.185-34.506-132.37 82.626-152.3-67.02 11.425-142.32-7.395-162.97-81.187C20.135 217.43 10 86.673 10 68.997 10-19.503 87.74 8.022 135.72 44.03z"/></svg>
                        </a>
                        {{-- Threads --}}
                        <a href="{{ $threadsUrl }}" target="_blank" rel="noopener" title="Share on Threads" aria-label="Share on Threads"
                           style="width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; border:1px solid rgba(255,255,255,0.15); border-radius:50%; color:rgba(255,255,255,0.75); transition:all 0.15s;"
                           onmouseover="this.style.color='#fff'; this.style.borderColor='rgba(255,255,255,0.4)';"
                           onmouseout="this.style.color='rgba(255,255,255,0.75)'; this.style.borderColor='rgba(255,255,255,0.15)';">
                            <svg width="16" height="16" viewBox="0 0 192 192" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M141.537 88.988a66.667 66.667 0 0 0-2.518-1.143c-1.482-27.307-16.403-42.94-41.457-43.1h-.34c-14.986 0-27.449 6.396-35.12 18.036l13.779 9.452c5.73-8.695 14.724-10.548 21.348-10.548h.229c8.249.053 14.474 2.452 18.503 7.129 2.93 3.404 4.89 8.107 5.864 14.045-7.34-1.248-15.276-1.63-23.764-1.144-23.91 1.378-39.281 15.323-38.249 34.7.526 9.831 5.416 18.287 13.776 23.81 7.07 4.67 16.173 6.95 25.635 6.434 12.5-.683 22.305-5.45 29.144-14.167 5.196-6.617 8.479-15.196 9.92-26.006 5.92 3.572 10.308 8.275 12.733 13.929 4.121 9.609 4.363 25.394-8.51 38.257-11.281 11.272-24.838 16.15-45.32 16.3-22.722-.168-39.9-7.456-51.06-21.66-10.45-13.301-15.852-32.52-16.054-57.117.202-24.597 5.604-43.816 16.055-57.118 11.16-14.205 28.337-21.49 51.06-21.659 22.886.17 40.361 7.494 51.94 21.779 5.683 7.008 9.97 15.821 12.79 26.083l16.456-4.385c-3.42-12.626-8.806-23.503-16.143-32.547C148.058 9.83 126.804.169 99.21 0h-.111C71.561.169 50.546 9.866 36.74 28.825 24.461 45.69 18.127 69.165 17.917 95.74l-.001.13.001.131c.21 26.574 6.544 50.05 18.823 66.915C50.546 181.874 71.561 191.571 99.099 191.74h.111c24.485-.169 41.736-6.578 55.94-20.769 18.586-18.566 18.027-41.83 11.899-56.116-4.393-10.246-12.789-18.555-24.258-24.022-3.149-1.439-6.401-2.717-9.799-3.842zm-26.064 19.51c-3.484 4.408-9.165 6.892-16.21 7.275-6.99.38-13.55-1.286-17.96-4.625-3.32-2.516-5.45-6.122-5.7-9.677-.51-7.196 6.06-13.32 18.91-14.064 1.83-.106 3.66-.158 5.49-.158 4.86 0 9.5.466 13.74 1.388 1.16 12.84-1.5 19.06-2.27 19.86z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="cal-day-right">
                <div class="cal-day-dateline">
                    <span>On this day</span>
                    <span class="cal-day-dateline-bar"></span>
                    <span>{{ $monthName }} {{ str_pad($selectedDay, 2, '0', STR_PAD_LEFT) }}@if($dayEntries->count() === 1), {{ $dayEntries->first()->year }}@endif</span>
                </div>

                @foreach($dayEntries as $idx => $entry)
                    @if($idx > 0)
                        <hr style="border:none; border-top:1px solid rgba(255,255,255,0.1); margin:48px 0;">
                    @endif
                    @if($dayEntries->count() > 1)
                        <div style="font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:rgba(255,255,255,0.4); margin-bottom:12px;">{{ $monthName }} {{ str_pad($selectedDay, 2, '0', STR_PAD_LEFT) }}, {{ $entry->year }}</div>
                    @endif

                    <h1 class="cal-day-title">{{ $entry->title }}</h1>

                    @if($entry->image)
                        <div class="cal-day-image">
                            <img src="{{ Storage::url($entry->image) }}" alt="{{ $entry->title }}">
                        </div>
                    @endif

                    {{-- Prisoner info card --}}
                    @if($entry->prisoner)
                        @php $p = $entry->prisoner; @endphp
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

                    @if($entry->description)
                        <div class="cal-day-desc">
                            @foreach(explode("\n", $entry->description) as $para)
                                @if(trim($para))<p style="margin-bottom:1.25em;">{{ $para }}</p>@endif
                            @endforeach
                        </div>
                    @endif
                @endforeach

                {{-- Prev/Next navigation (by day, not by entry) --}}
                <div class="cal-day-nav">
                    @php
                        $prevDay = $entries->where('day', '<', $selectedDay)->pluck('day')->unique()->last();
                        $nextDay = $entries->where('day', '>', $selectedDay)->pluck('day')->unique()->first();
                    @endphp
                    @if($prevDay)
                        <a href="/calendar?month={{ $month }}&view=day&day={{ $prevDay }}" class="cal-day-nav-btn" data-no-fade>&larr; {{ $monthName }} {{ $prevDay }}</a>
                    @endif
                    @if($nextDay)
                        <a href="/calendar?month={{ $month }}&view=day&day={{ $nextDay }}" class="cal-day-nav-btn" data-no-fade>{{ $monthName }} {{ $nextDay }} &rarr;</a>
                    @endif
                </div>
            </div>
        </div>
    @elseif($view === 'day' && $dayEntries->isEmpty())
        <div style="text-align:center; padding:80px 0; color:rgba(255,255,255,0.4);">No entry for this day. Try the month view to see all entries.</div>
    @else
        {{-- MONTH VIEW --}}
        <div class="cal-grid">
            @php
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, (int) date('Y'));
                $entriesByDay = $entries->groupBy('day');
            @endphp

            @for($d = 1; $d <= $daysInMonth; $d++)
                @if(isset($entriesByDay[$d]))
                    @php
                        $dayList = $entriesByDay[$d];
                        $entry = $dayList->first();
                        $extra = $dayList->count() - 1;
                    @endphp
                    <a href="/calendar?month={{ $month }}&view=day&day={{ $d }}" class="cal-card {{ ($month === $currentMonth && $d === $today) ? 'today' : '' }}" data-no-fade style="position:relative;">
                        @if($extra > 0)
                            <span style="position:absolute; top:8px; right:8px; font-size:11px; font-weight:700; padding:2px 6px; border-radius:10px; background:rgba(86,96,254,0.2); color:#5660fe; border:1px solid rgba(86,96,254,0.4);">+{{ $extra }}</span>
                        @endif
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
    calWipeNavigate(url.toString());
}

// Black left-to-right curtain wipe.
// Same shape as EJI's transition: 1.5s in (curtain covers screen, holds),
// then we navigate while it's covering; the destination page mounts with
// the curtain still covering and immediately plays the "out" half so the
// motion reads as one continuous wipe across the viewport.
(function () {
    var wipe = document.querySelector('.cal-wipe');
    if (!wipe) return;

    // Set --cal-wipe-top to the current bottom of .cal-header so the
    // curtain only covers the content beneath the title/Day-Month/month
    // picker row. Recompute on resize.
    function refreshWipeTop() {
        var header = document.querySelector('.cal-header');
        var top = 0;
        if (header) {
            var b = header.getBoundingClientRect().bottom;
            top = Math.max(0, Math.round(b));
        }
        wipe.style.setProperty('--cal-wipe-top', top + 'px');
    }
    refreshWipeTop();
    window.addEventListener('resize', refreshWipeTop);

    // 1) Page just loaded — let the curtain finish its wipe off to the right.
    requestAnimationFrame(function () {
        wipe.classList.add('out');
        setTimeout(function () { wipe.classList.remove('page-load', 'out'); }, 1600);
    });

    // 2) Intercept calendar navigation clicks so they trigger the wipe.
    var selectors = '.cal-card, .cal-month-btn, .cal-months-btn';
    document.addEventListener('click', function (e) {
        var a = e.target.closest(selectors);
        if (!a) return;
        if (a.target === '_blank' || a.hasAttribute('data-no-wipe')) return;
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
        var href = a.getAttribute('href');
        if (!href || href.charAt(0) === '#') return;
        e.preventDefault();
        calWipeNavigate(href);
    });
})();

function calWipeNavigate(href) {
    var wipe = document.querySelector('.cal-wipe');
    if (!wipe) { window.location.href = href; return; }
    // Recompute the top edge in case the layout shifted since load.
    var header = document.querySelector('.cal-header');
    if (header) {
        wipe.style.setProperty('--cal-wipe-top', Math.max(0, Math.round(header.getBoundingClientRect().bottom)) + 'px');
    }
    wipe.classList.remove('out', 'page-load');
    // Force reflow so the animation restarts cleanly if .in was already set.
    void wipe.offsetWidth;
    wipe.classList.add('in');
    // The curtain finishes its left-to-right entry around the 65% mark of 1.5s
    // (~975ms); navigate just before that so the new page can render under
    // the covering curtain and continue the wipe.
    setTimeout(function () { window.location.href = href; }, 750);
}
</script>
@endsection
