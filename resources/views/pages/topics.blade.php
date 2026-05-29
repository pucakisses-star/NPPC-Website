@extends('app')

@section('head')
<style>
    /* Full-bleed: break out of the global .container max-width (like the tracker). */
    body.page-topics main.container, body.page-topics .container { max-width: none !important; padding-left: 0 !important; padding-right: 0 !important; overflow: visible !important; }
    body.page-topics { background: #0a0a0c; color: #fff; }

    /* ── Miller-columns topic explorer, modeled on ecfr.eu "Mapping Palestinian Politics" ── */
    .tpx { position: relative; min-height: calc(100vh - 108px); overflow: hidden; }
    .tpx-bg { position: absolute; inset: 0; z-index: 0; background-size: cover; background-position: center; opacity: 0.22; }
    .tpx-bg-overlay { position: absolute; inset: 0; z-index: 1; background: linear-gradient(180deg, rgba(10,10,12,0.55) 0%, rgba(10,10,12,0.78) 100%); }
    .tpx-inner { position: relative; z-index: 2; padding: 40px clamp(24px, 4vw, 64px); }

    /* Header row */
    .tpx-head { display: flex; align-items: center; justify-content: space-between; gap: 24px; padding-bottom: 18px; border-bottom: 1px solid rgba(255,255,255,0.14); margin-bottom: 4px; }
    .tpx-title { display: flex; align-items: center; gap: 14px; font-size: 1.5rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; color: #fff; margin: 0; }
    .tpx-title svg { width: 30px; height: 30px; color: #5660fe; flex: 0 0 auto; }
    .tpx-actions { display: flex; gap: 22px; }
    .tpx-action { display: inline-flex; align-items: center; gap: 7px; background: none; border: 0; cursor: pointer; font: inherit; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255,255,255,0.6); text-decoration: none; transition: color 0.15s; }
    .tpx-action:hover { color: #fff; }
    .tpx-action svg { width: 15px; height: 15px; }

    /* Columns */
    .tpx-cols { display: grid; grid-template-columns: minmax(190px, 230px) minmax(220px, 1fr) minmax(340px, 460px); align-items: start; }
    .tpx-col { padding: 28px clamp(18px, 2vw, 32px); min-height: 60vh; }
    .tpx-col + .tpx-col { border-left: 1px solid rgba(86,96,254,0.28); }
    .tpx-col-detail { background: rgba(8,8,16,0.66); }

    /* Column 1 — root topics + search */
    .tpx-nav-item { display: block; font-size: 15px; font-weight: 600; color: rgba(255,255,255,0.62); padding: 9px 0; text-decoration: none; transition: color 0.15s; }
    .tpx-nav-item:hover { color: #fff; }
    .tpx-nav-item.active { color: #5660fe; }
    .tpx-search { background: transparent; border: 1px solid rgba(255,255,255,0.22); color: #fff; padding: 9px 12px; font-size: 13px; width: 100%; margin-top: 26px; outline: none; }
    .tpx-search::placeholder { color: rgba(255,255,255,0.32); }
    .tpx-search:focus { border-color: rgba(86,96,254,0.6); }

    /* Column 2 — sub-topics */
    .tpx-sub-heading { font-size: 13px; font-weight: 800; letter-spacing: 0.04em; color: rgba(255,255,255,0.85); margin: 0 0 18px; }
    .tpx-sub-link { display: block; font-size: 14px; line-height: 1.4; color: rgba(255,255,255,0.7); padding: 8px 0; text-decoration: none; transition: color 0.15s; }
    .tpx-sub-link:hover { color: #fff; }
    .tpx-sub-link.active { color: #5660fe; }
    .tpx-sub-empty { font-size: 13px; color: rgba(255,255,255,0.4); font-style: italic; }

    /* Column 3 — detail panel */
    .tpx-detail-title { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.55); margin-bottom: 22px; }
    .tpx-detail-body { font-size: 15px; color: rgba(255,255,255,0.78); line-height: 1.8; }
    .tpx-detail-body p { margin-bottom: 1.25em; }
    .tpx-detail-body a { color: #5660fe; }
    .tpx-detail-empty { font-size: 15px; color: rgba(255,255,255,0.4); font-style: italic; }

    .tpx-cases-title { font-size: 16px; font-weight: 800; color: #fff; margin: 32px 0 14px; border-top: 1px solid rgba(255,255,255,0.12); padding-top: 24px; }
    .tpx-case { display: flex; gap: 12px; align-items: center; padding: 11px 0; border-bottom: 1px solid rgba(255,255,255,0.07); }
    .tpx-case-photo { width: 46px; height: 46px; border-radius: 50%; object-fit: cover; flex: 0 0 auto; background: #1a1a2e; }
    .tpx-case-name { font-size: 14px; font-weight: 700; }
    .tpx-case-name a { color: #fff; text-decoration: none; }
    .tpx-case-name a:hover { color: #5660fe; }
    .tpx-case-meta { font-size: 12px; color: rgba(255,255,255,0.42); margin-top: 1px; }

    @@media (max-width: 1024px) {
        .tpx-cols { grid-template-columns: 1fr; }
        .tpx-col { min-height: 0; }
        .tpx-col + .tpx-col { border-left: 0; border-top: 1px solid rgba(86,96,254,0.28); }
    }
    @@media (max-width: 600px) {
        .tpx-head { flex-direction: column; align-items: flex-start; gap: 14px; }
    }
</style>
@endsection

@section('body')
@php $displayTopic = $activeChild ?: $activeTopic; @endphp
<div class="tpx">
    @php
        $bgImage = $activeTopic && $activeTopic->image ? Storage::url($activeTopic->image) : null;
    @endphp
    @if($bgImage)
        <div class="tpx-bg" style="background-image: url('{{ $bgImage }}');"></div>
    @else
        <div class="tpx-bg" style="background: linear-gradient(135deg, #0a0a1a 0%, #1a1040 55%, #2a1860 100%); opacity: 0.5;"></div>
    @endif
    <div class="tpx-bg-overlay"></div>

    <div class="tpx-inner">
        {{-- Header --}}
        <div class="tpx-head">
            <h1 class="tpx-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span>{{ $activeTopic ? $activeTopic->title : 'Topics' }}</span>
            </h1>
            <div class="tpx-actions">
                <button type="button" class="tpx-action" onclick="window.print()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                    Print
                </button>
                <button type="button" class="tpx-action" onclick="tpxShare()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/></svg>
                    Share
                </button>
            </div>
        </div>

        {{-- Columns --}}
        <div class="tpx-cols">
            {{-- Column 1: root topics --}}
            <div class="tpx-col tpx-col-nav">
                @foreach($rootTopics as $topic)
                    <a href="/topics/{{ $topic->slug }}"
                       class="tpx-nav-item {{ $activeTopic && $activeTopic->id === $topic->id ? 'active' : '' }}">
                        {{ $topic->title }}
                    </a>
                @endforeach
                <input type="text" class="tpx-search" placeholder="Search..." id="topic-search" onkeyup="filterTopics(this.value)">
            </div>

            {{-- Column 2: sub-topics of the active topic --}}
            <div class="tpx-col tpx-col-sub">
                @if($activeTopic)
                    <div class="tpx-sub-heading">About {{ $activeTopic->title }}</div>
                    @if($activeTopic->children->isNotEmpty())
                        @foreach($activeTopic->children as $child)
                            <a href="/topics/{{ $child->slug }}"
                               class="tpx-sub-link {{ $activeChild && $activeChild->id === $child->id ? 'active' : '' }}">
                                {{ $child->title }}
                            </a>
                        @endforeach
                    @else
                        <div class="tpx-sub-empty">No sub-topics.</div>
                    @endif
                @endif
            </div>

            {{-- Column 3: detail panel --}}
            <div class="tpx-col tpx-col-detail">
                @if($displayTopic)
                    <div class="tpx-detail-title">{{ strtoupper($displayTopic->title) }}</div>

                    @if($displayTopic->body)
                        <div class="tpx-detail-body">{!! $displayTopic->body !!}</div>
                    @else
                        <div class="tpx-detail-empty">Content for this topic is coming soon.</div>
                    @endif

                    @if($relatedPrisoners->isNotEmpty())
                        <div class="tpx-cases-title">Related Cases ({{ $relatedPrisoners->count() }})</div>
                        @foreach($relatedPrisoners as $prisoner)
                            <div class="tpx-case">
                                @if($prisoner->photo)
                                    <img src="{{ asset('storage/' . $prisoner->photo) }}" class="tpx-case-photo" alt="">
                                @else
                                    <div class="tpx-case-photo"></div>
                                @endif
                                <div>
                                    <div class="tpx-case-name"><a href="{{ $prisoner->url }}">{{ $prisoner->name }}</a></div>
                                    <div class="tpx-case-meta">{{ $prisoner->era }}{{ $prisoner->era && $prisoner->state ? ' · ' : '' }}{{ $prisoner->state }}</div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                @else
                    <div class="tpx-detail-empty">Select a topic from the left to explore.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function filterTopics(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.tpx-nav-item').forEach(function (el) {
        el.style.display = el.textContent.toLowerCase().includes(q) ? 'block' : 'none';
    });
}
function tpxShare() {
    var url = window.location.href;
    var title = document.title;
    if (navigator.share) {
        navigator.share({ title: title, url: url }).catch(function () {});
    } else if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(function () { alert('Link copied to clipboard'); });
    } else {
        window.prompt('Copy this link:', url);
    }
}
</script>
@endsection
