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
    .tpx-action:hover { color: var(--on-dark); }
    .tpx-action svg { width: 15px; height: 15px; }

    /* Left column — root topics + search, over the photo */
    .tpx-nav { grid-column: 1; padding: 26px clamp(20px, 3vw, 40px); }
    .tpx-nav-item { display: block; font-size: 15px; font-weight: 600; color: rgba(255,255,255,0.78); padding: 9px 0; text-decoration: none; transition: color 0.15s; text-shadow: 0 1px 8px rgba(0,0,0,0.6); }
    .tpx-nav-item:hover { color: var(--on-dark); }
    .tpx-nav-item.active { color: #8b93ff; }
    .tpx-search { background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.3); color: var(--on-dark); padding: 9px 12px; font-size: 13px; width: 100%; margin-top: 24px; outline: none; }
    .tpx-search::placeholder { color: rgba(255,255,255,0.5); }
    .tpx-search:focus { border-color: rgba(255,255,255,0.7); }

    /* Middle column — sub-topics, over the photo. When the active sub-topic has
       nested topics, a second list sits beside the sub-topics inside this same
       grid column, filling space that was already empty — so showing it never
       moves the left nav, the sub-topics list, or the detail panel. */
    .tpx-sub { grid-column: 2; padding: 26px clamp(20px, 3vw, 40px); border-left: 1px solid rgba(255,255,255,0.18); }
    .tpx-sub-inner { display: flex; align-items: stretch; flex-wrap: wrap; transition: opacity 0.5s ease, transform 0.5s ease; }
    /* Enter state for soft-nav: sub-topic columns slide in from the right and
       fade (mirrors the ecfr.eu "Mapping Palestinian Politics" column transition). */
    .tpx-sub-inner.tpx-enter { opacity: 0; transform: translateX(32px); }
    .tpx-sub-col { flex: 0 0 auto; min-width: 150px; }
    /* Nested-topics list — sits to the right of the sub-topics list */
    .tpx-sub2 { margin-left: clamp(20px, 2.2vw, 34px); padding-left: clamp(22px, 2.4vw, 40px); border-left: 1px solid rgba(255,255,255,0.18); }
    .tpx-sub-heading { font-size: 14px; font-weight: 800; letter-spacing: 0.03em; color: var(--on-dark); margin: 0 0 18px; text-shadow: 0 1px 8px rgba(0,0,0,0.6); }
    .tpx-sub-link { display: block; font-size: 14px; line-height: 1.4; color: rgba(255,255,255,0.82); padding: 8px 0; text-decoration: none; transition: color 0.15s; text-shadow: 0 1px 8px rgba(0,0,0,0.6); }
    .tpx-sub-link:hover { color: var(--on-dark); }
    .tpx-sub-link.active { color: #8b93ff; }

    /* Right column — white detail panel with a large image */
    .tpx-detail { grid-column: 3; grid-row: 1 / span 2; position: relative; z-index: 3; background: #fff; color: #1a1a1a; padding: 40px clamp(28px, 3vw, 48px); overflow-y: auto; max-height: calc(100vh - 108px); transition: opacity 0.5s ease; }
    .tpx-detail-eyebrow { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; color: #6b7280; margin-bottom: 18px; }
    .tpx-detail-hero { width: 100%; aspect-ratio: 4 / 3; object-fit: cover; display: block; margin-bottom: 22px; background: #ece9e4; }
    .tpx-detail-body { font-size: 16px; color: #333; line-height: 1.75; }
    .tpx-detail-body p { margin-bottom: 1.2em; }
    .tpx-detail-body a { color: #1f3df0; }
    .tpx-detail-empty { font-size: 16px; color: #9aa0a6; font-style: italic; }
    /* Contribute-to-the-database panel */
    .tpx-contribute-thanks { background: #eafaf1; border: 1px solid #46c08d; color: #176b48; border-radius: 8px; padding: 12px 14px; font-size: 14px; margin-bottom: 18px; }
    .tpx-contribute { display: flex; flex-direction: column; gap: 14px; margin-top: 18px; }
    .tpx-cf { display: flex; flex-direction: column; gap: 6px; font-size: 12px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #6b7280; }
    .tpx-cf input, .tpx-cf select, .tpx-cf textarea { font: inherit; font-weight: 400; letter-spacing: normal; text-transform: none; color: #1a1a1a; background: #fff; border: 1px solid #d6d3cd; border-radius: 7px; padding: 9px 11px; width: 100%; }
    .tpx-cf textarea { resize: vertical; }
    .tpx-cf input:focus, .tpx-cf select:focus, .tpx-cf textarea:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(86,96,254,0.15); }
    .tpx-cf-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .tpx-contribute-btn { align-self: flex-start; font: inherit; font-weight: 700; background: var(--accent); color: var(--on-accent); border: 0; border-radius: 8px; padding: 11px 22px; cursor: pointer; transition: background 0.15s ease; }
    .tpx-contribute-btn:hover { background: var(--accent-hover); }
    @@media (max-width: 560px) { .tpx-cf-row { grid-template-columns: 1fr; } }

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
        .tpx-sub2 { margin-left: 0; padding-left: 0; border-left: 0; }
    }
</style>
@endsection

@section('body')
@php
    $activeGrandchild = $activeGrandchild ?? null;
    $displayTopic = $activeGrandchild ?: ($activeChild ?: $activeTopic);
    // A third nav column appears when the active sub-topic has nested topics.
    $showL3 = $activeChild && $activeChild->children->isNotEmpty();

    // Bundled fallback imagery per topic, used only when a topic has no
    // image of its own. Any image uploaded in admin overrides these.
    $topicDefaults = [
        // Sections (drive the full-bleed background)
        'introduction'          => '/images/freedom.jpg',
        'movements'             => '/images/topics-movements.jpg',
        'eras'                  => '/images/topics-eras.jpg',
        'organizations'         => '/images/topics-organizations.jpg',
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

    // Per-topic full-bleed background overrides, keyed by the active leaf
    // topic's slug. Lets a specific sub-topic carry an explicit backdrop URL.
    // (Topics with their own uploaded image no longer need an override — the
    // image itself now drives the full-bleed background, below.)
    $bgOverrides = [];
    $activeOverride = ($displayTopic && isset($bgOverrides[$displayTopic->slug]))
        ? $bgOverrides[$displayTopic->slug]
        : null;

    // Full-bleed background: prefer the *displayed* topic's own image, so a
    // selected sub-topic's photo becomes the full-width backdrop (not just the
    // detail-panel hero). Falls back to the section's image, then a bundled
    // default.
    $bgImage = $showIndex
        ? '/images/topics-index.jpg'
        : ($showContribute
            ? '/images/topics-contributions.jpg'
            : ($activeOverride
                ?: ($displayTopic && $displayTopic->image
                    ? Storage::url($displayTopic->image)
                    : ($activeTopic && $activeTopic->image
                        ? Storage::url($activeTopic->image)
                        : $defaultFor($displayTopic ?: $activeTopic)))));
    $heroImage = $activeOverride
        ?: ($displayTopic && $displayTopic->image
            ? Storage::url($displayTopic->image)
            : $defaultFor($displayTopic));

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
        {{-- Header (spans the nav columns) --}}
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
            <a href="/topics/contributions" data-no-fade class="tpx-nav-item {{ $showContribute ? 'active' : '' }}">Contributions</a>
            <input type="text" class="tpx-search" placeholder="Search..." id="topic-search" onkeyup="filterTopics(this.value)">
        </div>

        {{-- Column 2: sub-topics, with an optional nested-topics list beside them.
             The nested list lives inside this same grid column, so it fills space
             that was already empty and never shifts the other columns. --}}
        <div class="tpx-sub">
            <div class="tpx-sub-inner">
                <div class="tpx-sub-col">
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
                @if($showL3)
                <div class="tpx-sub-col tpx-sub2">
                    <div class="tpx-sub-heading">About {{ $activeChild->title }}</div>
                    @foreach($activeChild->children as $grandchild)
                        <a href="/topics/{{ $grandchild->slug }}" data-no-fade
                           class="tpx-sub-link {{ $activeGrandchild && $activeGrandchild->id === $grandchild->id ? 'active' : '' }}">
                            {{ $grandchild->title }}
                        </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Detail panel --}}
        <div class="tpx-detail">
            @if($showContribute)
                <div class="tpx-detail-eyebrow">Contribute to the database</div>
                @if(request('form_submitted'))
                    <div class="tpx-contribute-thanks">Thank you — your contribution has been received. Our research team reviews every submission before it goes live.</div>
                @endif
                <div class="tpx-detail-body">
                    <p>This database is built and corrected by people like you. Know of a political prisoner we're missing, a detail that's wrong, or a source we should cite? Send it here — researchers verify every submission before it's published.</p>
                </div>
                <form class="tpx-contribute" method="POST" action="/form/contribution">
                    @csrf
                    <label class="tpx-cf">Type of contribution
                        <select name="contribution_type">
                            <option>New case / prisoner</option>
                            <option>Correction to an existing case</option>
                            <option>Additional source or document</option>
                            <option>Other</option>
                        </select>
                    </label>
                    <label class="tpx-cf">Person or case this concerns
                        <input type="text" name="subject_name" placeholder="Name of the prisoner or case">
                    </label>
                    <label class="tpx-cf">Details
                        <textarea name="details" rows="5" required placeholder="What should we add, change, or correct?"></textarea>
                    </label>
                    <label class="tpx-cf">Sources / links
                        <textarea name="sources" rows="2" placeholder="Court records, news articles, organization reports…"></textarea>
                    </label>
                    <div class="tpx-cf-row">
                        <label class="tpx-cf">Your name
                            <input type="text" name="contributor_name">
                        </label>
                        <label class="tpx-cf">Your email
                            <input type="email" name="contributor_email" placeholder="So we can follow up">
                        </label>
                    </div>
                    <div class="g-recaptcha" data-sitekey="6LdREZkqAAAAADv7Ei5dS_SZ1oVaz6A5FE7nacrw"></div>
                    <button type="submit" class="tpx-contribute-btn">Submit contribution</button>
                </form>
            @elseif($showIndex)
                <div class="tpx-detail-eyebrow">Index — All Topics A–Z</div>
                @forelse($indexGroups as $letter => $topics)
                    <div class="tpx-index-letter">{{ $letter }}</div>
                    @foreach($topics as $t)
                        <a class="tpx-index-link" data-no-fade href="/topics/{{ $t->slug }}">{{ $t->title }}</a>
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

    // A signature of the sub-topic column list that ignores which item is
    // active, so clicking a sibling in the same column doesn't count as a
    // change. Only a different section (or opening/closing a nested column)
    // changes this — and only then should the column re-animate.
    function subSignature(el) {
        return el ? el.innerHTML.replace(/\bactive\b/g, '').replace(/\s+/g, ' ').trim() : '';
    }

    // Crossfade the backdrop: layer the new photo above the current one at
    // opacity 0, fade it in, then drop any older layers.
    function crossfadeBackground(tpx, newBg) {
        var anchor = tpx.querySelector('.tpx-photo-tint') || tpx.querySelector('.tpx-grid');
        var layer = document.createElement('div');
        layer.className = 'tpx-photo';
        layer.style.backgroundImage = newBg;
        layer.style.opacity = '0';
        layer.style.transition = 'opacity 0.5s ease';
        if (anchor) { tpx.insertBefore(layer, anchor); } else { tpx.appendChild(layer); }
        requestAnimationFrame(function () {
            requestAnimationFrame(function () { layer.style.opacity = '1'; });
        });
        window.setTimeout(function () {
            tpx.querySelectorAll('.tpx-photo').forEach(function (p) {
                if (p !== layer && p.parentNode) { p.parentNode.removeChild(p); }
            });
            layer.style.transition = '';
        }, 600);
    }

    // Fade the detail panel out, resolving once the fade has run.
    function fadeOutDetail(el, ms) {
        return new Promise(function (resolve) {
            if (!el) { resolve(); return; }
            el.style.opacity = '0';
            window.setTimeout(resolve, ms);
        });
    }

    var FADE_MS = 500;

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

            // Swap the columns/header in place. The root nav updates instantly;
            // the sub-topic columns slide in from the right and the detail panel
            // fades back in, both just below.
            var freshGrid = fresh.querySelector('.tpx-grid');
            var curGrid = current.querySelector('.tpx-grid');
            // Decide whether the sub-topic column actually changed BEFORE the
            // swap replaces it. Clicking a sibling in the same column leaves the
            // list identical (only the active item differs), so it must not
            // re-animate — matching the ecfr.eu reference.
            var subChanged = subSignature(current.querySelector('.tpx-sub-inner'))
                          !== subSignature(fresh.querySelector('.tpx-sub-inner'));
            if (freshGrid && curGrid) { curGrid.innerHTML = freshGrid.innerHTML; }
            else { current.innerHTML = fresh.innerHTML; }

            // Slide + fade the sub-topic column(s) in from the right (0.5s), the
            // signature ecfr.eu column transition — only when the column's list
            // changed (a new section, or a nested column opening/closing).
            var newSub = current.querySelector('.tpx-sub-inner');
            if (newSub && subChanged) {
                newSub.classList.add('tpx-enter');
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () { newSub.classList.remove('tpx-enter'); });
                });
            }

            // A soft-injected reCAPTCHA (the Contributions form) won't auto-render,
            // so render it explicitly. The api.js library is loaded site-wide. If
            // it isn't available, fall back to a full load so the form never breaks.
            var captcha = current.querySelector('.g-recaptcha');
            if (captcha && !captcha.firstChild) {
                if (window.grecaptcha && grecaptcha.render) {
                    try { grecaptcha.render(captcha, { sitekey: captcha.getAttribute('data-sitekey') }); }
                    catch (err) { window.location.href = href; return; }
                } else {
                    window.location.href = href; return;
                }
            }

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
        var a = e.target.closest('a.tpx-nav-item, a.tpx-sub-link, a.tpx-index-link');
        if (!a) return;
        if (a.hasAttribute('data-fullload')) return;   // let this link do a full page load (form + reCAPTCHA)
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
