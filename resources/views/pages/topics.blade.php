@extends('app')

@section('head')
<style>
    /* Full-bleed: break out of the global .container max-width (like the tracker). */
    body.page-topics main.container, body.page-topics .container { max-width: none !important; padding-left: 0 !important; padding-right: 0 !important; overflow: visible !important; }
    body.page-topics { background: #14110e; }

    /* ── Topic explorer, modeled on ecfr.eu "Mapping Palestinian Politics":
       a strong photographic backdrop with light nav columns on the left, and
       a white detail panel with a large image on the right. ── */
    /* Break out to the full viewport width so the photo spans the page edge
       to edge, regardless of the centered .container around it. */
    .tpx { position: relative; min-height: calc(100vh - 108px); width: 100vw; margin-left: calc(50% - 50vw); }

    /* Photographic backdrop spanning the navigation area */
    .tpx-photo { position: absolute; inset: 0; z-index: 0; background-size: cover; background-position: center; }
    .tpx-photo-tint { position: absolute; inset: 0; z-index: 1; background: linear-gradient(90deg, rgba(8,7,5,0.82) 0%, rgba(8,7,5,0.45) 40%, rgba(8,7,5,0.55) 100%); }

    .tpx-grid { position: relative; z-index: 2; display: grid; grid-template-columns: minmax(200px, 240px) minmax(220px, 1fr) minmax(380px, 520px); grid-template-rows: auto 1fr; align-items: stretch; min-height: calc(100vh - 108px); }

    /* Header bar sits across the nav area */
    .tpx-head { grid-column: 1 / 3; display: flex; align-items: center; justify-content: flex-end; gap: 24px; padding: 28px clamp(20px, 3vw, 40px) 0; }
    .tpx-actions { display: flex; gap: 20px; }
    .tpx-action { display: inline-flex; align-items: center; gap: 7px; background: none; border: 0; cursor: pointer; font: inherit; font-size: 12px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: rgba(255,255,255,0.8); text-decoration: none; transition: color 0.15s; }
    .tpx-action:hover { color: #fff; }
    .tpx-action svg { width: 15px; height: 15px; }

    /* Left column — root topics + search, over the photo */
    .tpx-nav { grid-column: 1; padding: 26px clamp(20px, 3vw, 40px); }
    .tpx-nav-item { display: block; font-size: 15px; font-weight: 600; color: rgba(255,255,255,0.78); padding: 9px 0; text-decoration: none; transition: color 0.15s; text-shadow: 0 1px 8px rgba(0,0,0,0.6); }
    .tpx-nav-item:hover { color: #fff; }
    .tpx-nav-item.active { color: #5660fe; }
    .tpx-search { background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.3); color: #fff; padding: 9px 12px; font-size: 13px; width: 100%; margin-top: 24px; outline: none; }
    .tpx-search::placeholder { color: rgba(255,255,255,0.5); }
    .tpx-search:focus { border-color: rgba(255,255,255,0.7); }

    /* Middle column — sub-topics, over the photo */
    .tpx-sub { grid-column: 2; padding: 26px clamp(20px, 3vw, 40px); border-left: 1px solid rgba(255,255,255,0.18); }
    .tpx-sub-heading { font-size: 14px; font-weight: 800; letter-spacing: 0.03em; color: #fff; margin: 0 0 18px; text-shadow: 0 1px 8px rgba(0,0,0,0.6); }
    .tpx-sub-link { display: block; font-size: 14px; line-height: 1.4; color: rgba(255,255,255,0.82); padding: 8px 0; text-decoration: none; transition: color 0.15s; text-shadow: 0 1px 8px rgba(0,0,0,0.6); }
    .tpx-sub-link:hover { color: #fff; }
    .tpx-sub-link.active { color: #5660fe; }
    .tpx-sub-empty { font-size: 13px; color: rgba(255,255,255,0.6); font-style: italic; text-shadow: 0 1px 8px rgba(0,0,0,0.6); }

    /* Right column — white detail panel with a large image */
    .tpx-detail { grid-column: 3; grid-row: 1 / span 2; position: relative; z-index: 3; background: #fff; color: #1a1a1a; padding: 40px clamp(28px, 3vw, 48px); overflow-y: auto; max-height: calc(100vh - 108px); transition: opacity 0.3s ease; }
    .tpx-detail-eyebrow { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; color: #6b7280; margin-bottom: 18px; }
    .tpx-detail-hero { width: 100%; aspect-ratio: 4 / 3; object-fit: cover; display: block; margin-bottom: 22px; background: #ece9e4; }
    .tpx-detail-body { font-size: 16px; color: #333; line-height: 1.75; }
    .tpx-detail-body p { margin-bottom: 1.2em; }
    .tpx-detail-body a { color: #1f3df0; }
    .tpx-detail-empty { font-size: 16px; color: #9aa0a6; font-style: italic; }

    /* Index — alphabetical list of all sub-topics */
    .tpx-index-letter { font-size: 13px; font-weight: 800; letter-spacing: 0.08em; color: #6b7280; border-bottom: 1px solid rgba(0,0,0,0.1); padding-bottom: 6px; margin: 26px 0 8px; }
    .tpx-index-letter:first-of-type { margin-top: 0; }
    .tpx-index-link { display: block; font-size: 15px; color: #1a1a1a; text-decoration: none; padding: 6px 0; }
    .tpx-index-link:hover { color: #1f3df0; }

    .tpx-cases-title { font-size: 16px; font-weight: 800; color: #111; margin: 30px 0 12px; border-top: 1px solid rgba(0,0,0,0.1); padding-top: 22px; }
    .tpx-case { display: flex; gap: 12px; align-items: center; padding: 11px 0; border-bottom: 1px solid rgba(0,0,0,0.08); }
    .tpx-case-photo { width: 46px; height: 46px; border-radius: 50%; object-fit: cover; flex: 0 0 auto; background: #e7e3dd; }
    .tpx-case-name { font-size: 14px; font-weight: 700; }
    .tpx-case-name a { color: #111; text-decoration: none; }
    .tpx-case-name a:hover { color: #1f3df0; }
    .tpx-case-meta { font-size: 12px; color: #8a8f98; margin-top: 1px; }

    @@media (max-width: 1024px) {
        .tpx-grid { grid-template-columns: 1fr 1fr; grid-template-rows: auto; }
        .tpx-head { grid-column: 1 / 3; }
        .tpx-detail { grid-column: 1 / 3; grid-row: auto; max-height: none; }
    }
    @@media (max-width: 600px) {
        .tpx-grid { grid-template-columns: 1fr; }
        .tpx-head { grid-column: 1; flex-direction: column; align-items: flex-start; gap: 14px; }
        .tpx-nav, .tpx-sub, .tpx-detail { grid-column: 1; }
        .tpx-sub { border-left: 0; }
    }
</style>
@endsection

@section('body')
@php
    $displayTopic = $activeChild ?: $activeTopic;

    // Bundled fallback imagery per topic, used only when a topic has no
    // image of its own. Any image uploaded in admin overrides these.
    $topicDefaults = [
        // Sections (drive the full-bleed background)
        'introduction'          => '/images/freedom.jpg',
        'movements'             => '/images/volunteer.jpg',
        'eras'                  => '/images/candles.jpg',
        'organizations'         => '/images/stop-jailing-truth-tellers.webp',
        // Movement leaves (drive the detail-panel hero)
        'black-lives-matter'    => '/images/section_1.jpg',
        'environmental-justice' => '/images/volunteer.jpg',
        'anti-war-activism'     => '/images/section_2.jpg',
    ];
    $defaultFor = function ($topic) use ($topicDefaults) {
        if (! $topic) return '/images/prison-hell.jpg';
        if (isset($topicDefaults[$topic->slug])) return $topicDefaults[$topic->slug];
        // A leaf with no image of its own inherits its section's image.
        $parent = $topic->parent_id ? $topic->parent : null;
        if ($parent && isset($topicDefaults[$parent->slug])) return $topicDefaults[$parent->slug];
        return '/images/prison-hell.jpg';
    };

    $bgImage = $showIndex
        ? '/images/fence.jpg'
        : ($activeTopic && $activeTopic->image
            ? Storage::url($activeTopic->image)
            : $defaultFor($activeTopic));
    $heroImage = $displayTopic && $displayTopic->image
        ? Storage::url($displayTopic->image)
        : $defaultFor($displayTopic);

    // Only show the detail-panel hero when it's a different image from the
    // full-bleed backdrop. A topic with no image of its own falls back to its
    // section's image, which is also what drives the background — so without
    // this guard the same photo appears twice (once behind, once in the panel).
    $showHero = $heroImage && $heroImage !== $bgImage;
@endphp
<div class="tpx">
    {{-- Photographic backdrop --}}
    <div class="tpx-photo" style="background-image: url('{{ $bgImage }}');"></div>
    <div class="tpx-photo-tint"></div>

    <div class="tpx-grid">
        {{-- Header (spans the two nav columns) --}}
        <div class="tpx-head">
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

        {{-- Column 1: root topics --}}
        <div class="tpx-nav">
            @foreach($rootTopics as $topic)
                <a href="/topics/{{ $topic->slug }}" data-no-fade
                   class="tpx-nav-item {{ $activeTopic && $activeTopic->id === $topic->id ? 'active' : '' }}">
                    {{ $topic->title }}
                </a>
            @endforeach
            <a href="/topics/index" data-no-fade class="tpx-nav-item {{ $showIndex ? 'active' : '' }}">Index</a>
            <input type="text" class="tpx-search" placeholder="Search..." id="topic-search" onkeyup="filterTopics(this.value)">
        </div>

        {{-- Column 2: sub-topics --}}
        <div class="tpx-sub">
            @if($activeTopic && $activeTopic->children->isNotEmpty())
                <div class="tpx-sub-heading">About {{ $activeTopic->title }}</div>
                @foreach($activeTopic->children as $child)
                    <a href="/topics/{{ $child->slug }}" data-no-fade
                       class="tpx-sub-link {{ $activeChild && $activeChild->id === $child->id ? 'active' : '' }}">
                        {{ $child->title }}
                    </a>
                @endforeach
            @endif
        </div>

        {{-- Column 3: white detail panel --}}
        <div class="tpx-detail">
            @if($showIndex)
                <div class="tpx-detail-eyebrow">Index — All Topics A–Z</div>
                @forelse($indexGroups as $letter => $topics)
                    <div class="tpx-index-letter">{{ $letter }}</div>
                    @foreach($topics as $t)
                        <a class="tpx-index-link" href="/topics/{{ $t->slug }}">{{ $t->title }}</a>
                    @endforeach
                @empty
                    <div class="tpx-detail-empty">No topics to index yet.</div>
                @endforelse
            @elseif($displayTopic)
                <div class="tpx-detail-eyebrow">{{ strtoupper($displayTopic->title) }}</div>

                @if($showHero)
                    <img class="tpx-detail-hero" src="{{ $heroImage }}" alt="{{ $displayTopic->title }}">
                @endif

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

<script>
function filterTopics(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.tpx-nav-item').forEach(function (el) {
        el.style.display = el.textContent.toLowerCase().includes(q) ? 'block' : 'none';
    });
}
function tpxShare() {
    var url = window.location.href, title = document.title;
    if (navigator.share) { navigator.share({ title: title, url: url }).catch(function () {}); }
    else if (navigator.clipboard) { navigator.clipboard.writeText(url).then(function () { alert('Link copied to clipboard'); }); }
    else { window.prompt('Copy this link:', url); }
}

/* Soft navigation between topics: swap the explorer in place instead of a
   full page reload. The far-right detail panel fades out and back in, and the
   backdrop photo crossfades. Degrades to normal navigation. */
(function () {
    function bgImageOf(el) { return el ? el.style.backgroundImage : ''; }

    // Crossfade the backdrop: layer the new photo above the current one at
    // opacity 0, fade it in, then drop any older layers.
    function crossfadeBackground(tpx, newBg) {
        var anchor = tpx.querySelector('.tpx-photo-tint') || tpx.querySelector('.tpx-grid');
        var layer = document.createElement('div');
        layer.className = 'tpx-photo';
        layer.style.backgroundImage = newBg;
        layer.style.opacity = '0';
        layer.style.transition = 'opacity 0.6s ease';
        if (anchor) { tpx.insertBefore(layer, anchor); } else { tpx.appendChild(layer); }
        requestAnimationFrame(function () {
            requestAnimationFrame(function () { layer.style.opacity = '1'; });
        });
        window.setTimeout(function () {
            tpx.querySelectorAll('.tpx-photo').forEach(function (p) {
                if (p !== layer && p.parentNode) { p.parentNode.removeChild(p); }
            });
            layer.style.transition = '';
        }, 700);
    }

    // Fade the detail panel out, resolving once the fade has run.
    function fadeOutDetail(el, ms) {
        return new Promise(function (resolve) {
            if (!el) { resolve(); return; }
            el.style.opacity = '0';
            window.setTimeout(resolve, ms);
        });
    }

    var FADE_MS = 300;

    function swapTopic(href, push) {
        var current = document.querySelector('.tpx');
        var detail = current ? current.querySelector('.tpx-detail') : null;

        var fetchP = fetch(href, { headers: { 'X-Requested-With': 'fetch' } })
            .then(function (r) {
                if (!r.ok) throw new Error('bad response');
                return r.text();
            });

        // Fade the current panel out and fetch in parallel; only swap once both
        // have finished, so the fade is always visible (even when cached).
        Promise.all([fetchP, fadeOutDetail(detail, FADE_MS)]).then(function (results) {
            var doc = new DOMParser().parseFromString(results[0], 'text/html');
            var fresh = doc.querySelector('.tpx');
            current = document.querySelector('.tpx');
            if (!fresh || !current) { window.location.href = href; return; }

            // Crossfade the background photo to the new topic's image.
            var photos = current.querySelectorAll('.tpx-photo');
            var curBg = bgImageOf(photos[photos.length - 1]);
            var newBg = bgImageOf(fresh.querySelector('.tpx-photo'));
            if (newBg && newBg !== curBg) { crossfadeBackground(current, newBg); }

            // Swap the columns/header in place. Nav and sub-topic lists update
            // instantly; the detail panel is faded back in just below.
            var freshGrid = fresh.querySelector('.tpx-grid');
            var curGrid = current.querySelector('.tpx-grid');
            if (freshGrid && curGrid) { curGrid.innerHTML = freshGrid.innerHTML; }
            else { current.innerHTML = fresh.innerHTML; }

            // Fade the freshly-swapped detail panel in from transparent.
            var newDetail = current.querySelector('.tpx-detail');
            if (newDetail) {
                newDetail.scrollTop = 0;
                newDetail.style.opacity = '0';
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () { newDetail.style.opacity = '1'; });
                });
            }

            if (doc.title) document.title = doc.title;
            if (push) window.history.pushState({ tpx: true }, '', href);
        }).catch(function () { window.location.href = href; });
    }

    document.addEventListener('click', function (e) {
        var a = e.target.closest('a.tpx-nav-item, a.tpx-sub-link');
        if (!a) return;
        // Respect new-tab / modified clicks.
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        var href = a.getAttribute('href');
        if (!href) return;
        e.preventDefault();
        if (href === window.location.pathname) return;
        swapTopic(href, true);
    });

    window.addEventListener('popstate', function () {
        if (window.location.pathname.indexOf('/topics') === 0) {
            swapTopic(window.location.href, false);
        } else {
            window.location.reload();
        }
    });
})();
</script>
@endsection
