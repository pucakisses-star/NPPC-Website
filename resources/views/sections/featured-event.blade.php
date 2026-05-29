@php
    // Featured upcoming event for the home page. Renders nothing when there
    // is no published, upcoming event (so the section simply disappears).
    $featuredEvent = \App\Models\Event::published()->upcoming()->first();
@endphp

@if($featuredEvent)
    <section class="home-event">
        <div class="home-event-head">
            <span class="home-event-eyebrow">Event</span>
            <a href="/events" class="home-event-more">More Events</a>
        </div>
        <div class="home-event-rule"></div>

        <div class="home-event-body">
            {{-- Visual / banner --}}
            <a href="{{ $featuredEvent->event_url ?: '/events' }}"
               @if($featuredEvent->event_url) target="_blank" rel="noopener" @endif
               class="home-event-visual">
                @if($featuredEvent->image)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($featuredEvent->image) }}" alt="{{ $featuredEvent->title }}">
                @else
                    <div class="home-event-visual-fallback">
                        <div class="home-event-fallback-date">{{ $featuredEvent->event_date->format('M j') }}</div>
                        <div class="home-event-fallback-title">{{ $featuredEvent->title }}</div>
                    </div>
                @endif
            </a>

            {{-- Details --}}
            <div class="home-event-detail">
                <span class="home-event-tag">Upcoming Event</span>
                <div class="home-event-date">{{ $featuredEvent->event_date->format('M j') }}</div>
                <h3 class="home-event-title">{{ $featuredEvent->title }}</h3>
                @if($featuredEvent->description)
                    <p class="home-event-desc">{{ \Illuminate\Support\Str::limit(strip_tags($featuredEvent->description), 260) }}</p>
                @endif
                @if($featuredEvent->location || $featuredEvent->time)
                    <div class="home-event-meta">
                        @if($featuredEvent->location)<span>{{ $featuredEvent->location }}</span>@endif
                        @if($featuredEvent->time)<span>{{ $featuredEvent->time }}</span>@endif
                    </div>
                @endif
                <a href="{{ $featuredEvent->event_url ?: '/events' }}"
                   @if($featuredEvent->event_url) target="_blank" rel="noopener" @endif
                   class="home-event-cta">
                    {{ $featuredEvent->event_url ? 'Register' : 'View event' }} &rarr;
                </a>
            </div>
        </div>
    </section>

    <style>
        .home-event { margin: 72px 0; }
        .home-event-head { display: flex; align-items: baseline; justify-content: space-between; }
        .home-event-eyebrow { font-size: 1.05rem; font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase; color: #0a0a0a; }
        .home-event-more { font-size: 13px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #1f2a5e; text-decoration: none; }
        .home-event-more:hover { text-decoration: underline; }
        .home-event-rule { position: relative; height: 1px; background: rgba(0,0,0,0.15); margin: 10px 0 36px; }
        .home-event-rule::before { content: ""; position: absolute; left: 0; top: -1px; height: 3px; width: 110px; background: #1f2a5e; }

        .home-event-body { display: grid; grid-template-columns: 1.15fr 1fr; gap: 48px; align-items: center; }
        .home-event-visual { display: block; border-radius: 2px; overflow: hidden; background: linear-gradient(135deg, #1b2a6b 0%, #2f7fd6 100%); aspect-ratio: 16 / 9; }
        .home-event-visual img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .home-event-visual-fallback { width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; padding: 40px; color: #fff; }
        .home-event-fallback-date { font-size: 1.1rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; opacity: 0.85; }
        .home-event-fallback-title { font-size: 2rem; font-weight: 800; line-height: 1.1; margin-top: 10px; }

        .home-event-tag { display: inline-block; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #3a3f5e; background: #eceeffe6; padding: 4px 10px; border-radius: 3px; margin-bottom: 14px; }
        .home-event-date { font-size: 2.4rem; font-weight: 800; color: #1f2a5e; line-height: 1; }
        .home-event-title { font-size: 2.1rem; font-weight: 800; color: #0a0a0a; line-height: 1.12; margin: 4px 0 16px; }
        .home-event-desc { font-size: 16px; line-height: 1.6; color: #333; margin: 0 0 16px; }
        .home-event-meta { display: flex; flex-direction: column; gap: 2px; font-size: 14px; color: #555; margin-bottom: 20px; }
        .home-event-cta { display: inline-block; font-size: 14px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #1f2a5e; text-decoration: none; }
        .home-event-cta:hover { text-decoration: underline; }

        @@media (max-width: 768px) {
            .home-event { margin: 48px 0; }
            .home-event-body { grid-template-columns: 1fr; gap: 24px; }
            .home-event-title { font-size: 1.7rem; }
            .home-event-date { font-size: 2rem; }
        }
    </style>
@endif
