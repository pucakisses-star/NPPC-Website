@extends('app')

@section('title', 'Chapters | NPPC')
@section('meta_description', 'NPPC chapters are volunteer-led local groups organizing letter-writing, court support, community education, and mutual aid for political prisoners. Find a chapter near you or start one.')

@section('head')
<style>
    .ch { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

    /* Hero */
    .ch-hero { padding: 72px 0 40px; max-width: 820px; }
    .ch-hero-label { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.14em; color: var(--accent-2); margin-bottom: 18px; }
    .ch-hero-title { font-size: 4rem; font-weight: 900; color: var(--fg); line-height: 1.04; margin-bottom: 24px; }
    .ch-hero-sub { font-size: 19px; color: rgba(var(--fg-rgb),0.7); line-height: 1.7; }
    .ch-hero-cta { display: flex; gap: 14px; flex-wrap: wrap; margin-top: 34px; }
    .ch-btn { display: inline-block; padding: 15px 32px; font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; text-decoration: none; transition: background 0.2s, color 0.2s, border-color 0.2s; }
    .ch-btn-primary { background: var(--accent); color: var(--on-accent); }
    .ch-btn-primary:hover { background: var(--accent-hover); }
    .ch-btn-ghost { background: transparent; color: var(--fg); border: 1px solid rgba(var(--fg-rgb),0.25); }
    .ch-btn-ghost:hover { border-color: var(--accent); color: var(--accent); }

    .ch-divider { height: 1px; background: rgba(var(--fg-rgb),0.1); margin: 56px 0; }

    /* What chapters do */
    .ch-do-title { font-size: 2.4rem; font-weight: 900; color: var(--fg); margin-bottom: 40px; }
    .ch-do-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 28px; }
    .ch-do-card { border: 1px solid rgba(var(--fg-rgb),0.09); border-radius: 10px; padding: 28px; }
    .ch-do-ico { width: 40px; height: 40px; color: var(--accent); margin-bottom: 16px; }
    .ch-do-h { font-size: 1.15rem; font-weight: 800; color: var(--fg); margin-bottom: 8px; }
    .ch-do-p { font-size: 14px; color: rgba(var(--fg-rgb),0.6); line-height: 1.6; }

    /* ===== Find-a-chapter map band (light, ASS-style) ===== */
    .ch-mapband { position: relative; width: 100vw; margin-left: calc(50% - 50vw); margin-right: calc(50% - 50vw); margin-top: 72px; background: #eef0ff; color: #15171c; padding: 56px 0 64px; }
    .ch-mapband-inner { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
    .ch-map-prompt { font-size: 1.5rem; font-weight: 700; color: #15171c; margin-bottom: 26px; }
    .ch-usmap { width: 100%; max-width: 980px; height: auto; display: block; margin: 0 auto; }
    .ch-usmap path { fill: #ffffff; stroke: #c9cee0; stroke-width: 1; transition: fill 0.12s ease; }
    .ch-usmap path.has-chapter { cursor: pointer; }
    .ch-usmap path.has-chapter:hover,
    .ch-usmap path.is-selected { fill: var(--accent); }
    .ch-usmap path.has-chapter:focus-visible { outline: none; fill: var(--accent); }
    .ch-tip { position: fixed; z-index: 60; background: #f5b400; color: #15171c; font-size: 13px; font-weight: 800; letter-spacing: 0.02em; padding: 5px 9px; border-radius: 3px; pointer-events: none; transform: translate(-50%, -145%); display: none; box-shadow: 0 4px 12px rgba(0,0,0,0.18); }

    .ch-all-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; border-top: 1px solid rgba(21,23,28,0.14); padding-top: 22px; margin-top: 48px; }
    .ch-all-title { font-size: 1.05rem; font-weight: 800; color: #15171c; letter-spacing: 0.02em; }
    .ch-reset { display: none; background: none; border: none; cursor: pointer; color: var(--accent); font-weight: 700; font-size: 13px; letter-spacing: 0.04em; text-transform: uppercase; }
    .ch-all-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0 44px; }
    .ch-ccard { display: block; text-decoration: none; color: inherit; border-top: 1px solid rgba(21,23,28,0.12); padding: 22px 2px; transition: background 0.15s; }
    .ch-ccard:first-child, .ch-all-grid .ch-ccard:nth-child(2), .ch-all-grid .ch-ccard:nth-child(3) { border-top: none; }
    .ch-ccard:hover { background: rgba(86,96,254,0.05); }
    .ch-ccard-top { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; }
    .ch-ccard-label { font-size: 12px; font-weight: 600; color: rgba(21,23,28,0.5); }
    .ch-ccard-loc { font-size: 12px; font-weight: 800; color: #c0870a; text-align: right; }
    .ch-ccard-name { font-size: 1.35rem; font-weight: 800; color: #15171c; margin: 8px 0 4px; line-height: 1.15; }
    .ch-ccard:hover .ch-ccard-name { color: var(--accent); }
    .ch-ccard-desc { font-size: 13px; color: rgba(21,23,28,0.6); line-height: 1.5; }

    /* Start a chapter CTA */
    .ch-start { position: relative; width: 100vw; margin-left: calc(50% - 50vw); margin-right: calc(50% - 50vw); background: linear-gradient(120deg, #14141c 0%, #1c1550 55%, #5660fe 140%); color: #fff; margin-top: 0; }
    .ch-start-inner { max-width: 1200px; margin: 0 auto; padding: 72px 24px; display: flex; gap: 48px; align-items: center; justify-content: space-between; flex-wrap: wrap; }
    .ch-start-h { font-size: 2.6rem; font-weight: 900; line-height: 1.1; margin-bottom: 14px; }
    .ch-start-p { font-size: 16px; color: rgba(255,255,255,0.75); line-height: 1.7; max-width: 560px; }
    .ch-start-btn { display: inline-block; background: #fff; color: #111; padding: 16px 38px; font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; text-decoration: none; white-space: nowrap; transition: background 0.2s; }
    .ch-start-btn:hover { background: rgba(255,255,255,0.88); }
    /* Let the full-bleed bands escape the centered .container on this page. */
    .page-chapters .container { overflow: visible; }

    @media (max-width: 1024px) {
        .ch-do-grid { grid-template-columns: repeat(2, 1fr); }
        .ch-all-grid { grid-template-columns: repeat(2, 1fr); }
        .ch-all-grid .ch-ccard:nth-child(3) { border-top: 1px solid rgba(21,23,28,0.12); }
    }
    @media (max-width: 768px) {
        .ch-hero-title { font-size: 2.6rem; }
        .ch-do-grid { grid-template-columns: 1fr; }
        .ch-all-grid { grid-template-columns: 1fr; }
        .ch-all-grid .ch-ccard { border-top: 1px solid rgba(21,23,28,0.12); }
        .ch-start-inner { padding: 48px 24px; }
        .ch-start-h { font-size: 2rem; }
    }
</style>
@endsection

@section('body')
@php
    $chDo = [
        ['h' => 'Letter Writing', 'p' => 'Regular letter-writing nights that keep imprisoned activists connected to the outside world.',
         'svg' => '<path d="M4 4h16v16H4z"/><path d="m4 6 8 6 8-6"/>'],
        ['h' => 'Court Support', 'p' => 'Packing courtrooms, tracking cases, and showing defendants they are not facing the state alone.',
         'svg' => '<path d="M3 21h18"/><path d="M12 3 4 8h16z"/><path d="M6 8v9M18 8v9M12 8v9"/>'],
        ['h' => 'Public Education', 'p' => 'Teach-ins, film screenings, and tabling that bring the reality of political imprisonment to your community.',
         'svg' => '<path d="M2 7l10-4 10 4-10 4z"/><path d="M6 10v5c0 1 3 3 6 3s6-2 6-3v-5"/>'],
        ['h' => 'Mutual Aid', 'p' => 'Commissary drives, legal-defense funds, and support for prisoners\' families and loved ones.',
         'svg' => '<path d="M20.8 5.6a5.5 5.5 0 0 0-7.8 0L12 6.6l-1-1a5.5 5.5 0 1 0-7.8 7.8l1 1L12 22l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>'],
    ];

    // State code -> full name (for the tooltip + prompt).
    $chStateNames = [
        'NY' => 'New York', 'MA' => 'Massachusetts', 'PA' => 'Pennsylvania', 'DC' => 'District of Columbia',
        'IL' => 'Illinois', 'MN' => 'Minnesota', 'MI' => 'Michigan', 'GA' => 'Georgia', 'TX' => 'Texas',
        'NC' => 'North Carolina', 'LA' => 'Louisiana', 'CA' => 'California', 'WA' => 'Washington', 'CO' => 'Colorado',
    ];

    // Flat list of chapters (each tagged with its state for map filtering).
    $chapters = [
        ['st' => 'NY', 'loc' => 'New York, NY', 'city' => 'New York City', 'desc' => 'Letter-writing, court support, and street outreach across the five boroughs.'],
        ['st' => 'MA', 'loc' => 'Boston, MA', 'city' => 'Boston', 'desc' => 'Campus organizing and solidarity actions across Greater Boston.'],
        ['st' => 'PA', 'loc' => 'Philadelphia, PA', 'city' => 'Philadelphia', 'desc' => 'Court support and mutual aid for regional political prisoners.'],
        ['st' => 'DC', 'loc' => 'Washington, DC', 'city' => 'Washington, D.C.', 'desc' => 'Advocacy and days of action in the capital.'],
        ['st' => 'IL', 'loc' => 'Chicago, IL', 'city' => 'Chicago', 'desc' => 'Letter-writing nights and defense-committee support.'],
        ['st' => 'MN', 'loc' => 'Minneapolis, MN', 'city' => 'Twin Cities', 'desc' => 'Community education and prisoner solidarity in Minneapolis–St. Paul.'],
        ['st' => 'MI', 'loc' => 'Detroit, MI', 'city' => 'Detroit', 'desc' => 'Local outreach and fundraising drives.'],
        ['st' => 'GA', 'loc' => 'Atlanta, GA', 'city' => 'Atlanta', 'desc' => 'Court support and organizing across the Southeast.'],
        ['st' => 'TX', 'loc' => 'Austin, TX', 'city' => 'Austin', 'desc' => 'Teach-ins and letter-writing in Central Texas.'],
        ['st' => 'NC', 'loc' => 'Durham, NC', 'city' => 'Durham', 'desc' => 'Mutual aid and campus solidarity in the Triangle.'],
        ['st' => 'LA', 'loc' => 'New Orleans, LA', 'city' => 'New Orleans', 'desc' => 'Community education and Gulf Coast organizing.'],
        ['st' => 'CA', 'loc' => 'Los Angeles, CA', 'city' => 'Los Angeles', 'desc' => 'Film screenings, tabling, and prisoner outreach.'],
        ['st' => 'CA', 'loc' => 'San Francisco, CA', 'city' => 'Bay Area', 'desc' => 'Court support and defense-fund drives across the Bay.'],
        ['st' => 'WA', 'loc' => 'Seattle, WA', 'city' => 'Seattle', 'desc' => 'Letter-writing and Pacific Northwest solidarity.'],
        ['st' => 'CO', 'loc' => 'Denver, CO', 'city' => 'Denver', 'desc' => 'Community education and regional actions.'],
    ];
@endphp

<div class="ch">
    <div class="ch-hero">
        <div class="ch-hero-label">Chapters</div>
        <h1 class="ch-hero-title">Organize where you are</h1>
        <p class="ch-hero-sub">NPPC chapters are volunteer-led local groups that bring people together to support political prisoners — writing letters, packing courtrooms, educating their communities, and raising funds for defense and commissary. Wherever you are, you can plug into a chapter or start one.</p>
        <div class="ch-hero-cta">
            <a href="/contact" class="ch-btn ch-btn-primary">Start a Chapter</a>
            <a href="/get-involved" class="ch-btn ch-btn-ghost">Other Ways to Help</a>
        </div>
    </div>

    <div class="ch-divider"></div>

    {{-- What chapters do --}}
    <h2 class="ch-do-title">What chapters do</h2>
    <div class="ch-do-grid">
        @foreach($chDo as $d)
            <div class="ch-do-card">
                <svg class="ch-do-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">{!! $d['svg'] !!}</svg>
                <div class="ch-do-h">{{ $d['h'] }}</div>
                <div class="ch-do-p">{{ $d['p'] }}</div>
            </div>
        @endforeach
    </div>
</div>

{{-- ===== Find a chapter: interactive US map + filterable chapter list ===== --}}
<section class="ch-mapband">
    <div class="ch-mapband-inner">
        <div class="ch-map-prompt" id="ch-prompt">Select a state on the map</div>
        @include('partials.us-chapters-map')

        <div class="ch-all-head">
            <div class="ch-all-title">All Chapters</div>
            <button type="button" class="ch-reset" id="ch-reset">&times; Show all</button>
        </div>
        <div class="ch-all-grid" id="ch-allgrid">
            @foreach($chapters as $c)
                <a class="ch-ccard" href="/contact" data-state="{{ $c['st'] }}">
                    <div class="ch-ccard-top">
                        <span class="ch-ccard-label">Local Chapter</span>
                        <span class="ch-ccard-loc">{{ $c['loc'] }}</span>
                    </div>
                    <div class="ch-ccard-name">{{ $c['city'] }}</div>
                    <div class="ch-ccard-desc">{{ $c['desc'] }}</div>
                </a>
            @endforeach
        </div>
    </div>
    <div class="ch-tip" id="ch-tip"></div>
</section>

<script>
    (function () {
        var svg = document.querySelector('.ch-usmap');
        if (!svg) return;
        var names = @json($chStateNames);
        var tip = document.getElementById('ch-tip');
        var prompt = document.getElementById('ch-prompt');
        var grid = document.getElementById('ch-allgrid');
        var reset = document.getElementById('ch-reset');
        var selected = null;

        function filter(code) {
            grid.querySelectorAll('.ch-ccard').forEach(function (c) {
                c.style.display = (!code || c.getAttribute('data-state') === code) ? '' : 'none';
            });
            if (code) { prompt.textContent = names[code]; reset.style.display = 'inline-block'; }
            else { prompt.textContent = 'Select a state on the map'; reset.style.display = 'none'; }
        }

        Object.keys(names).forEach(function (code) {
            var p = svg.querySelector('path[data-state="' + code + '"]');
            if (!p) return;
            p.classList.add('has-chapter');
            p.setAttribute('tabindex', '0');
            p.setAttribute('role', 'button');
            p.setAttribute('aria-label', names[code] + ' — view chapters');
            p.addEventListener('mouseenter', function () { tip.textContent = code; tip.style.display = 'block'; });
            p.addEventListener('mousemove', function (e) { tip.style.left = e.clientX + 'px'; tip.style.top = e.clientY + 'px'; });
            p.addEventListener('mouseleave', function () { tip.style.display = 'none'; });
            function pick() {
                if (selected) selected.classList.remove('is-selected');
                selected = p; p.classList.add('is-selected');
                filter(code);
            }
            p.addEventListener('click', pick);
            p.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); pick(); } });
        });

        reset.addEventListener('click', function () {
            if (selected) { selected.classList.remove('is-selected'); selected = null; }
            filter(null);
        });
    })();
</script>

{{-- Start a chapter CTA --}}
<section class="ch-start">
    <div class="ch-start-inner">
        <div>
            <div class="ch-start-h">No chapter near you?<br>Start one.</div>
            <p class="ch-start-p">Starting a chapter is simple: gather a few people who care, and we will provide the toolkit, materials, and support to get going — from your first letter-writing night to court support and community events.</p>
        </div>
        <a href="/contact" class="ch-start-btn">Start a Chapter &rarr;</a>
    </div>
</section>
@endsection
