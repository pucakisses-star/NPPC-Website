@extends('app')

@section('title'){{ $selected['affiliation'] ?? $selected['ideology'] ?? $selected['era'] ?? 'Icons' }} — Political Prisoner Portraits | NPPC @endsection

@section('meta_description'){{ number_format($total) }} portraits of political prisoners held in the United States, from the 1700s to today — filterable by era, ideology, organization and state.@endsection

@section('head')
<meta name="description" content="{{ number_format($total) }} portraits of political prisoners held in the United States, filterable by era, ideology, organization and state.">
<style>
    .icons-page { max-width: 1600px; margin: 0 auto; padding: 0 24px 96px; font-family: Verlag A, Verlag B, Verlag, Helvetica, Arial, sans-serif; }

    /* Heading — set very large and light, tracked out, as in the reference. */
    .icons-head { padding: 56px 0 8px; }
    .icons-title { font-size: clamp(3.5rem, 11vw, 9rem); font-weight: 300; line-height: 0.95; letter-spacing: 0.02em; text-transform: uppercase; color: var(--fg); margin: 0; }
    .icons-count { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.14em; color: rgba(var(--fg-rgb),0.45); margin-top: 8px; }

    /* Filter bar: each control sits between two hairlines, label left,
       chevron right — a native select made to look like the reference. */
    .icons-filters { display: flex; flex-wrap: wrap; align-items: stretch; gap: 0 48px; margin: 40px 0 48px; }
    .icons-filter { position: relative; flex: 0 1 200px; min-width: 160px; border-top: 1px solid rgba(var(--fg-rgb),0.25); border-bottom: 1px solid rgba(var(--fg-rgb),0.25); }
    .icons-filter select { appearance: none; -webkit-appearance: none; width: 100%; background: transparent; border: 0; color: var(--fg); font: inherit; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.1em; padding: 16px 28px 16px 0; cursor: pointer; }
    .icons-filter select:focus-visible { outline: 2px solid var(--accent-2); outline-offset: 2px; }
    .icons-filter option { background: #111; color: #fff; text-transform: none; letter-spacing: 0; }
    .icons-filter::after { content: ""; position: absolute; right: 4px; top: 50%; width: 8px; height: 8px; margin-top: -6px; border-right: 1.5px solid rgba(var(--fg-rgb),0.6); border-bottom: 1.5px solid rgba(var(--fg-rgb),0.6); transform: rotate(45deg); pointer-events: none; }
    .icons-filter.is-set { border-color: rgba(var(--fg-rgb),0.7); }
    .icons-filter.is-set select { color: var(--fg); font-weight: 700; }

    .icons-actions { flex: 1 1 auto; display: flex; align-items: center; justify-content: flex-end; gap: 28px; }
    .icons-toggle { border-top: 1px solid rgba(var(--fg-rgb),0.25); border-bottom: 1px solid rgba(var(--fg-rgb),0.25); padding: 16px 0; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(var(--fg-rgb),0.75); text-decoration: none; white-space: nowrap; }
    .icons-toggle:hover { color: var(--fg); }
    .icons-toggle.on { color: var(--fg); font-weight: 700; border-color: rgba(var(--fg-rgb),0.7); }
    .icons-clear { font-size: 12px; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(var(--fg-rgb),0.45); text-decoration: none; white-space: nowrap; }
    .icons-clear:hover { color: var(--fg); }
    .icons-nojs { font-size: 12px; color: rgba(var(--fg-rgb),0.45); margin: -32px 0 32px; }

    /* The wall. Same tile treatment as the related-cases grid on a prisoner
       page, so the two read as one system. */
    .icons-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; }
    .icons-tile { position: relative; display: block; aspect-ratio: 1/1; border-radius: 4px; overflow: hidden; background: linear-gradient(135deg, #111 0%, #1a1a2e 100%); }
    /* Full grayscale at rest, colour on hover. The wall mixes 1910s
       mugshots, studio portraits and phone selfies; desaturating them is
       what lets an era be read as one thing instead of a jumble. */
    .icons-tile img { width: 100%; height: 100%; object-fit: cover; display: block; filter: grayscale(100%) contrast(1.05); transition: filter 0.3s; }
    .icons-tile::after { content: ""; position: absolute; inset: 0; background: rgba(0,0,0,0.62); opacity: 0; transition: opacity 0.3s; }
    .icons-tile:hover img, .icons-tile:focus-visible img { filter: grayscale(0%); }
    .icons-tile:hover::after, .icons-tile:focus-visible::after { opacity: 1; }
    .icons-name { position: absolute; inset: 0; z-index: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 12px; font-size: 14px; font-weight: 700; letter-spacing: 0.08em; line-height: 1.25; text-transform: uppercase; color: #fff; overflow-wrap: anywhere; }
    .icons-name .w { display: block; max-width: 100%; opacity: 0; transition: opacity 0.28s ease; transition-delay: var(--do, 0s); }
    .icons-tile:hover .icons-name .w, .icons-tile:focus-visible .icons-name .w { opacity: 1; transition-delay: var(--di, 0s); }
    .icons-badge { position: absolute; top: 0; right: 0; z-index: 2; background: #c8102e; color: #fff; font-size: 10px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; padding: 4px 10px; }

    .icons-empty { padding: 80px 0; color: rgba(var(--fg-rgb),0.5); font-size: 16px; }
    /* The select labels are for screen readers only — the visible label is
       the select's own first option. This class is not defined globally, so
       it is declared here rather than assumed. */
    .icons-sr { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; border: 0; }

    @media (prefers-reduced-motion: reduce) {
        .icons-tile img, .icons-tile::after { transition: none; }
        .icons-name .w, .icons-tile:hover .icons-name .w, .icons-tile:focus-visible .icons-name .w { transition-delay: 0s; }
    }

    @media (max-width: 768px) {
        .icons-page { padding: 0 16px 64px; }
        .icons-head { padding: 32px 0 4px; }
        .icons-filters { gap: 0 24px; margin: 24px 0 32px; }
        .icons-filter { flex: 1 1 140px; }
        .icons-actions { flex: 1 1 100%; justify-content: flex-start; }
        .icons-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; }
        .icons-name { font-size: 12px; }
    }
</style>
@endsection

@section('body')
@php
    // Rebuild the query string for a single facet change, dropping the page
    // number so a filter always lands on the first page.
    $urlFor = function (array $overrides) use ($selected, $showNew) {
        $q = array_filter([
            'era' => $selected['era'],
            'ideology' => $selected['ideology'],
            'affiliation' => $selected['affiliation'],
            'state' => $selected['state'],
            'new' => $showNew ? 1 : null,
        ]);
        foreach ($overrides as $k => $v) {
            if ($v === null || $v === '') { unset($q[$k]); } else { $q[$k] = $v; }
        }

        return '/icons'.(count($q) ? '?'.http_build_query($q) : '');
    };
    $anySelected = $showNew || collect($selected)->filter()->isNotEmpty();
    $labels = ['era' => 'Era', 'ideology' => 'Ideology', 'affiliation' => 'Organization', 'state' => 'State'];
@endphp

<div class="icons-page">
    <div class="icons-head">
        <h1 class="icons-title">Icons</h1>
        <div class="icons-count">
            {{ number_format($total) }} {{ Str::plural('portrait', $total) }}
            @if($anySelected) &middot; filtered @endif
        </div>
    </div>

    {{-- Filters. Plain GET forms so the page works with JavaScript off; the
         inline onchange is a convenience, not the mechanism. --}}
    <form class="icons-filters" method="get" action="/icons">
        @if($showNew)<input type="hidden" name="new" value="1">@endif

        @foreach($labels as $facet => $label)
            <div class="icons-filter {{ $selected[$facet] ? 'is-set' : '' }}">
                <label class="icons-sr" for="f-{{ $facet }}">{{ $label }}</label>
                <select id="f-{{ $facet }}" name="{{ $facet }}" onchange="this.form.submit()">
                    <option value="">{{ $label }}</option>
                    @foreach($options[$facet] as $value => $count)
                        <option value="{{ $value }}" @selected($selected[$facet] === $value)>{{ $value }} ({{ $count }})</option>
                    @endforeach
                </select>
            </div>
        @endforeach

        <div class="icons-actions">
            <noscript><button type="submit" class="icons-toggle" style="background:none;cursor:pointer">Apply</button></noscript>
            <a class="icons-toggle {{ $showNew ? 'on' : '' }}" href="{{ $urlFor(['new' => $showNew ? null : 1]) }}">
                {{ $showNew ? 'Showing new' : 'Show new' }}
            </a>
            @if($anySelected)
                <a class="icons-clear" href="/icons">Clear</a>
            @endif
        </div>
    </form>

    {{-- Everything the filters change lives in here, so the enhancement
         below can swap it in one move. --}}
    <div id="icons-results">
    @if($prisoners->isEmpty())
        <div class="icons-empty">No portraits match that combination.</div>
    @else
        <div class="icons-grid">
            @foreach($prisoners as $p)
                @php
                    $words = preg_split('/\s+/', trim($p->name)) ?: [$p->name];
                    $n = count($words);
                    $isNew = $p->created_at && $p->created_at->greaterThanOrEqualTo($newSince);
                @endphp
                <a href="/prisoner/{{ $p->slug ?: $p->id }}" class="icons-tile" aria-label="{{ $p->name }}">
                    <img src="{{ $p->photoUrl() }}" alt="{{ $p->name }}" loading="lazy" decoding="async">
                    @if($isNew)<span class="icons-badge">New</span>@endif
                    <span class="icons-name">
                        @foreach($words as $i => $w)
                            <span class="w" style="--di:{{ number_format($i * 0.12, 2) }}s;--do:{{ number_format(($n - 1 - $i) * 0.08, 2) }}s">{{ $w }}</span>
                        @endforeach
                    </span>
                </a>
            @endforeach
        </div>

        <div class="icons-pager">{{ $prisoners->links('vendor.pagination.nppc') }}</div>
    @endif
    </div>
</div>

{{-- Progressive enhancement. Without this the form and links above still
     work by full page load — this only replaces the reload with a fade, by
     fetching the same URL and swapping the results region.

     Deliberately no framework and no separate JSON endpoint: the page it
     fetches is the page it would have navigated to, so the two paths can
     never render different results. --}}
<script>
(function () {
    var page = document.querySelector('.icons-page');
    if (!page || !window.fetch || !window.DOMParser || !window.history.pushState) return;

    var results = document.getElementById('icons-results');
    var form = page.querySelector('.icons-filters');
    var count = page.querySelector('.icons-count');
    var calm = window.matchMedia('(prefers-reduced-motion: reduce)');
    var token = 0;

    results.style.transition = 'opacity .22s ease';

    function fade(to) {
        return new Promise(function (done) {
            if (calm.matches) { results.style.opacity = to; return done(); }
            results.style.opacity = to;
            setTimeout(done, 230);
        });
    }

    function go(url, push) {
        var mine = ++token;

        fade(0).then(function () {
            return fetch(url, { headers: { 'X-Requested-With': 'fetch' } });
        }).then(function (r) {
            if (!r.ok) throw new Error(r.status);
            return r.text();
        }).then(function (html) {
            // A newer click landed while this was in flight — drop this one.
            if (mine !== token) return;

            var doc = new DOMParser().parseFromString(html, 'text/html');
            var fresh = doc.getElementById('icons-results');
            if (!fresh) throw new Error('no results region');

            results.innerHTML = fresh.innerHTML;

            var freshCount = doc.querySelector('.icons-count');
            if (freshCount && count) count.innerHTML = freshCount.innerHTML;

            var freshActions = doc.querySelector('.icons-actions');
            var actions = page.querySelector('.icons-actions');
            if (freshActions && actions) actions.innerHTML = freshActions.innerHTML;

            // Keep the selects and their set/unset rules in step with the
            // URL, so a back button or a Clear reflects in the controls.
            doc.querySelectorAll('.icons-filters select').forEach(function (fresh) {
                var here = form.querySelector('select[name="' + fresh.name + '"]');
                if (!here) return;
                here.value = fresh.value;
                here.parentNode.classList.toggle('is-set', !!fresh.value);
            });

            var hidden = form.querySelector('input[name="new"]');
            var isNew = url.indexOf('new=1') !== -1;
            if (isNew && !hidden) {
                hidden = document.createElement('input');
                hidden.type = 'hidden'; hidden.name = 'new'; hidden.value = '1';
                form.insertBefore(hidden, form.firstChild);
            } else if (!isNew && hidden) {
                hidden.parentNode.removeChild(hidden);
            }

            if (push) window.history.pushState({ icons: true }, '', url);
            if (page.getBoundingClientRect().top < 0) page.scrollIntoView({ behavior: calm.matches ? 'auto' : 'smooth' });

            return fade(1);
        }).catch(function () {
            // Anything unexpected: fall back to the navigation this replaced.
            window.location.href = url;
        });
    }

    function urlFromForm() {
        var params = new URLSearchParams(new FormData(form));
        var kept = new URLSearchParams();
        params.forEach(function (v, k) { if (v !== '') kept.append(k, v); });
        var q = kept.toString();

        return '/icons' + (q ? '?' + q : '');
    }

    form.addEventListener('change', function (e) {
        if (e.target.tagName !== 'SELECT') return;
        go(urlFromForm(), true);
    });

    // Show new, Clear, and the pager — all plain links to the same page.
    page.addEventListener('click', function (e) {
        var a = e.target.closest('a');
        if (!a || e.metaKey || e.ctrlKey || e.shiftKey || a.target === '_blank') return;

        // The paginator emits absolute URLs while Show new and Clear emit
        // relative ones, so resolve before comparing. Anything that is not
        // this page — a portrait, the nav — navigates normally.
        var url;
        try { url = new URL(a.href, window.location.origin); } catch (err) { return; }
        if (url.origin !== window.location.origin || url.pathname !== '/icons') return;

        e.preventDefault();
        go(url.pathname + url.search, true);
    });

    window.addEventListener('popstate', function () {
        go(window.location.pathname + window.location.search, false);
    });

    // The selects submit the form without JS; with JS the change handler
    // above owns them, so drop the inline fallback to avoid a double load.
    form.querySelectorAll('select[onchange]').forEach(function (s) { s.onchange = null; });
})();
</script>
@endsection
