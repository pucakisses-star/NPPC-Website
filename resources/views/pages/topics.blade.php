@extends('app')

@section('head')
<style>
    .topic-explorer { display: flex; min-height: calc(100vh - 108px); }

    /* Left sidebar nav */
    .topic-sidebar { flex: 0 0 220px; padding: 32px 24px; position: relative; z-index: 2; }
    .topic-nav-item { display: block; font-size: 15px; font-weight: 600; color: rgba(255,255,255,0.6); padding: 8px 0; text-decoration: none; transition: color 0.15s; }
    .topic-nav-item:hover { color: #fff; }
    .topic-nav-item.active { color: #5660fe; }
    .topic-search { background: transparent; border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 8px 12px; font-size: 13px; width: 100%; margin-top: 24px; outline: none; }
    .topic-search::placeholder { color: rgba(255,255,255,0.3); }

    /* Center: sub-topics + background image */
    .topic-center { flex: 1; position: relative; min-height: 500px; }
    .topic-center-bg { position: absolute; inset: 0; background-size: cover; background-position: center; opacity: 0.3; }
    .topic-center-overlay { position: absolute; inset: 0; background: linear-gradient(90deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0.6) 100%); }
    .topic-center-content { position: relative; z-index: 2; padding: 32px 24px; }
    .topic-subtopic-heading { font-size: 14px; color: #5660fe; font-weight: 700; margin-bottom: 16px; }
    .topic-subtopic-group-title { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(255,255,255,0.5); margin: 20px 0 8px; }
    .topic-subtopic-link { display: block; font-size: 14px; color: rgba(255,255,255,0.75); padding: 5px 0; text-decoration: none; transition: color 0.15s; }
    .topic-subtopic-link:hover { color: #fff; }
    .topic-subtopic-link.active { color: #5660fe; }

    /* Right content panel */
    .topic-content { flex: 0 0 480px; background: #0a0a14; padding: 32px; overflow-y: auto; max-height: calc(100vh - 108px); border-left: 1px solid rgba(255,255,255,0.08); }
    .topic-content-title { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.5); margin-bottom: 24px; }
    .topic-content-body { font-size: 15px; color: rgba(255,255,255,0.75); line-height: 1.8; }
    .topic-content-body p { margin-bottom: 1.25em; }
    .topic-content-body a { color: #5660fe; }

    /* Related prisoners */
    .topic-prisoners-title { font-size: 16px; font-weight: 800; color: #fff; margin: 32px 0 16px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 24px; }
    .topic-prisoner-card { display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.06); }
    .topic-prisoner-photo { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
    .topic-prisoner-name { font-size: 14px; font-weight: 700; color: #fff; }
    .topic-prisoner-name a { color: #fff; text-decoration: none; }
    .topic-prisoner-name a:hover { color: #5660fe; }
    .topic-prisoner-meta { font-size: 12px; color: rgba(255,255,255,0.4); }

    @media (max-width: 1024px) {
        .topic-explorer { flex-direction: column; }
        .topic-sidebar { flex: auto; }
        .topic-center { min-height: 300px; }
        .topic-content { flex: auto; max-height: none; }
    }
</style>
@endsection

@section('body')
<div class="topic-explorer">
    {{-- Left sidebar --}}
    <div class="topic-sidebar">
        @foreach($rootTopics as $topic)
            <a href="/topics/{{ $topic->slug }}"
               class="topic-nav-item {{ $activeTopic && $activeTopic->id === $topic->id ? 'active' : '' }}">
                {{ $topic->title }}
            </a>
        @endforeach
        <input type="text" class="topic-search" placeholder="Search..." id="topic-search" onkeyup="filterTopics(this.value)">
    </div>

    {{-- Center panel --}}
    <div class="topic-center">
        @php
            $bgImage = null;
            if ($activeTopic && $activeTopic->image) {
                $bgImage = Storage::url($activeTopic->image);
            }
        @endphp
        @if($bgImage)
            <div class="topic-center-bg" style="background-image: url('{{ $bgImage }}');"></div>
        @else
            <div class="topic-center-bg" style="background: linear-gradient(135deg, #0a0a1a 0%, #1a1040 50%, #2a1860 100%);"></div>
        @endif
        <div class="topic-center-overlay"></div>

        <div class="topic-center-content">
            @if($activeTopic)
                <div class="topic-subtopic-heading">About {{ $activeTopic->title }}</div>

                @if($activeTopic->children->isNotEmpty())
                    @foreach($activeTopic->children as $child)
                        <a href="/topics/{{ $child->slug }}"
                           class="topic-subtopic-link {{ $activeChild && $activeChild->id === $child->id ? 'active' : '' }}">
                            {{ $child->title }}
                        </a>
                    @endforeach
                @endif
            @endif
        </div>
    </div>

    {{-- Right content panel --}}
    <div class="topic-content">
        @php $displayTopic = $activeChild ?: $activeTopic; @endphp

        @if($displayTopic)
            <div class="topic-content-title">{{ strtoupper($displayTopic->title) }}</div>

            @if($displayTopic->body)
                <div class="topic-content-body">
                    {!! $displayTopic->body !!}
                </div>
            @else
                <div class="topic-content-body" style="color: rgba(255,255,255,0.4); font-style: italic;">
                    Content for this topic is coming soon.
                </div>
            @endif

            {{-- Related prisoners --}}
            @if($relatedPrisoners->isNotEmpty())
                <div class="topic-prisoners-title">Related Cases ({{ $relatedPrisoners->count() }})</div>
                @foreach($relatedPrisoners as $prisoner)
                    <div class="topic-prisoner-card">
                        @if($prisoner->photo)
                            <img src="{{ asset('storage/' . $prisoner->photo) }}" class="topic-prisoner-photo" alt="">
                        @else
                            <div style="width:48px;height:48px;border-radius:50%;background:#1a1a2e;flex-shrink:0;"></div>
                        @endif
                        <div>
                            <div class="topic-prisoner-name"><a href="{{ $prisoner->url }}">{{ $prisoner->name }}</a></div>
                            <div class="topic-prisoner-meta">
                                {{ $prisoner->era }}{{ $prisoner->era && $prisoner->state ? ' · ' : '' }}{{ $prisoner->state }}
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        @else
            <div class="topic-content-body" style="color: rgba(255,255,255,0.4);">
                Select a topic from the left to explore.
            </div>
        @endif
    </div>
</div>

<script>
function filterTopics(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.topic-nav-item').forEach(function(el) {
        el.style.display = el.textContent.toLowerCase().includes(q) ? 'block' : 'none';
    });
    document.querySelectorAll('.topic-subtopic-link').forEach(function(el) {
        el.style.display = el.textContent.toLowerCase().includes(q) ? 'block' : 'none';
    });
}
</script>
@endsection
