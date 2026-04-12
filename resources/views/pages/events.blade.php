@extends('app')

@section('head')
<style>
    .events-page { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
    .events-hero { display: flex; gap: 48px; align-items: flex-start; padding: 48px 0 64px; }
    .events-hero-text { flex: 1; }
    .events-hero-image { flex: 0 0 400px; border-radius: 12px; overflow: hidden; }
    .events-title { font-size: 4rem; font-weight: 900; color: #fff; margin-bottom: 24px; line-height: 1.05; font-style: italic; }
    .events-intro { font-size: 18px; color: rgba(255,255,255,0.7); line-height: 1.7; }
    .events-content { display: flex; gap: 48px; padding-bottom: 80px; }
    .events-main { flex: 1; }
    .events-sidebar { flex: 0 0 300px; }
    .events-tabs { display: flex; gap: 24px; margin-bottom: 8px; position: relative; }
    .events-tab { font-size: 16px; font-weight: 700; color: rgba(255,255,255,0.5); cursor: pointer; padding-bottom: 8px; text-decoration: none; transition: all 0.2s; }
    .events-tab:hover { color: #fff; }
    .events-tab.active { color: #fff; }
    .events-tab-indicator { position: absolute; top: -12px; width: 40px; height: 3px; background: #5660fe; }
    .events-tab-line { height: 1px; background: rgba(255,255,255,0.15); margin-bottom: 32px; }
    .event-card { display: flex; align-items: flex-start; gap: 20px; padding: 24px 0; border-bottom: 1px solid rgba(255,255,255,0.08); }
    .event-date-col { text-align: center; flex: 0 0 60px; }
    .event-date-month { font-size: 15px; font-weight: 700; color: rgba(255,255,255,0.5); text-transform: uppercase; }
    .event-date-day { font-size: 36px; font-weight: 900; color: #fff; line-height: 1.1; }
    .event-divider { width: 2px; background: #5660fe; align-self: stretch; flex-shrink: 0; border-radius: 1px; }
    .event-image { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
    .event-info { flex: 1; }
    .event-info-title { font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 4px; }
    .event-info-meta { font-size: 13px; color: rgba(255,255,255,0.45); margin-bottom: 8px; }
    .event-info-details { font-size: 14px; font-weight: 700; color: #5660fe; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
    .event-info-details:hover { color: #7880ff; }
    .events-empty { text-align: center; padding: 60px 0; color: rgba(255,255,255,0.4); font-size: 16px; }
    .sidebar-title { font-size: 16px; font-weight: 800; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.15); padding-bottom: 12px; margin-bottom: 20px; }
    .sidebar-series { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.06); text-decoration: none; color: rgba(255,255,255,0.75); font-size: 15px; font-weight: 600; transition: color 0.15s; }
    .sidebar-series:hover { color: #fff; }
    .sidebar-series-dot { width: 8px; height: 8px; border-radius: 50%; background: #5660fe; flex-shrink: 0; }
    @media (max-width: 768px) {
        .events-hero { flex-direction: column; }
        .events-hero-image { flex: auto; width: 100%; }
        .events-content { flex-direction: column; }
        .events-sidebar { flex: auto; }
        .events-title { font-size: 2.5rem; }
    }
</style>
@endsection

@section('body')
<div class="events-page">
    <div class="events-hero">
        <div class="events-hero-text">
            <h1 class="events-title">Events</h1>
            <p class="events-intro">Join NPPC's events for conversations about political imprisonment, civil liberties, and social justice with advocates, researchers, and community members.</p>
        </div>
        <div class="events-hero-image">
            <div style="width:100%; height:300px; background:linear-gradient(135deg, #1a1040 0%, #2a1860 50%, #5660fe 100%); border-radius:12px; display:flex; align-items:center; justify-content:center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="rgba(255,255,255,0.15)" viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zM9 14H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2zm-8 4H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2z"/></svg>
            </div>
        </div>
    </div>

    <div class="events-content">
        <div class="events-main">
            <div class="events-tabs">
                <div class="events-tab-indicator" id="tab-indicator"></div>
                <a href="#" class="events-tab active" data-no-fade onclick="switchTab('upcoming', this); return false;">Upcoming</a>
                <a href="#" class="events-tab" data-no-fade onclick="switchTab('past', this); return false;">Past</a>
            </div>
            <div class="events-tab-line"></div>

            {{-- Upcoming --}}
            <div id="events-upcoming">
                @if($upcoming->isEmpty())
                    <div class="events-empty">No upcoming events at this time. Check back soon!</div>
                @else
                    @foreach($upcoming as $event)
                        <div class="event-card">
                            <div class="event-date-col">
                                <div class="event-date-month">{{ $event->event_date->format('M') }}</div>
                                <div class="event-date-day">{{ $event->event_date->format('j') }}</div>
                            </div>
                            <div class="event-divider"></div>
                            @if($event->image)
                                <img src="{{ Storage::url($event->image) }}" class="event-image" alt="{{ $event->title }}">
                            @endif
                            <div class="event-info">
                                <div class="event-info-title">{{ $event->title }}</div>
                                @if($event->time || $event->location)
                                    <div class="event-info-meta">
                                        {{ $event->time }}{{ $event->time && $event->location ? ' · ' : '' }}{{ $event->location }}
                                    </div>
                                @endif
                                @if($event->event_url)
                                    <a href="{{ $event->event_url }}" target="_blank" class="event-info-details">DETAILS &rarr;</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Past --}}
            <div id="events-past" style="display:none;">
                @if($past->isEmpty())
                    <div class="events-empty">No past events found.</div>
                @else
                    @foreach($past as $event)
                        <div class="event-card">
                            <div class="event-date-col">
                                <div class="event-date-month">{{ $event->event_date->format('M') }}</div>
                                <div class="event-date-day">{{ $event->event_date->format('j') }}</div>
                            </div>
                            <div class="event-divider"></div>
                            @if($event->image)
                                <img src="{{ Storage::url($event->image) }}" class="event-image" alt="{{ $event->title }}">
                            @endif
                            <div class="event-info">
                                <div class="event-info-title">{{ $event->title }}</div>
                                @if($event->time || $event->location)
                                    <div class="event-info-meta">
                                        {{ $event->time }}{{ $event->time && $event->location ? ' · ' : '' }}{{ $event->location }}
                                    </div>
                                @endif
                                @if($event->event_url)
                                    <a href="{{ $event->event_url }}" target="_blank" class="event-info-details">DETAILS &rarr;</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        @if($series->isNotEmpty())
            <div class="events-sidebar">
                <div class="sidebar-title">Event Series</div>
                @foreach($series as $s)
                    <a href="/events?series={{ urlencode($s) }}" class="sidebar-series">
                        <span class="sidebar-series-dot"></span>
                        {{ $s }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
function switchTab(tab, btn) {
    document.getElementById('events-upcoming').style.display = tab === 'upcoming' ? 'block' : 'none';
    document.getElementById('events-past').style.display = tab === 'past' ? 'block' : 'none';

    document.querySelectorAll('.events-tab').forEach(function(t) { t.classList.remove('active'); });
    btn.classList.add('active');

    var indicator = document.getElementById('tab-indicator');
    indicator.style.left = tab === 'past' ? '108px' : '0';
}
</script>
@endsection
