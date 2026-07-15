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

    /* Directory */
    .ch-dir-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-bottom: 8px; }
    .ch-dir-title { font-size: 2.4rem; font-weight: 900; color: var(--fg); }
    .ch-dir-note { font-size: 15px; color: rgba(var(--fg-rgb),0.55); max-width: 460px; line-height: 1.6; }
    .ch-region { margin-top: 44px; }
    .ch-region-h { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; color: var(--accent-2); padding-bottom: 12px; border-bottom: 2px solid var(--accent); margin-bottom: 24px; }
    .ch-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .ch-card { display: block; text-decoration: none; color: inherit; border: 1px solid rgba(var(--fg-rgb),0.1); border-radius: 8px; padding: 20px 22px; background: rgba(var(--fg-rgb),0.015); transition: border-color 0.18s, background 0.18s, transform 0.18s; }
    .ch-card:hover { border-color: var(--accent); background: rgba(86,96,254,0.06); transform: translateY(-2px); }
    .ch-card-city { font-size: 1.15rem; font-weight: 800; color: var(--fg); margin-bottom: 6px; }
    .ch-card-desc { font-size: 13px; color: rgba(var(--fg-rgb),0.55); line-height: 1.55; margin-bottom: 14px; }
    .ch-card-link { font-size: 12px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--accent); display: inline-flex; align-items: center; gap: 6px; }
    .ch-card.is-start { border-style: dashed; background: transparent; }
    .ch-card.is-start .ch-card-city { color: var(--accent); }

    /* Start a chapter CTA */
    .ch-start { position: relative; width: 100vw; margin-left: calc(50% - 50vw); margin-right: calc(50% - 50vw); background: linear-gradient(120deg, #14141c 0%, #1c1550 55%, #5660fe 140%); color: #fff; margin-top: 80px; }
    .ch-start-inner { max-width: 1200px; margin: 0 auto; padding: 72px 24px; display: flex; gap: 48px; align-items: center; justify-content: space-between; flex-wrap: wrap; }
    .ch-start-h { font-size: 2.6rem; font-weight: 900; line-height: 1.1; margin-bottom: 14px; }
    .ch-start-p { font-size: 16px; color: rgba(255,255,255,0.75); line-height: 1.7; max-width: 560px; }
    .ch-start-btn { display: inline-block; background: #fff; color: #111; padding: 16px 38px; font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; text-decoration: none; white-space: nowrap; transition: background 0.2s; }
    .ch-start-btn:hover { background: rgba(255,255,255,0.88); }
    /* Let the full-bleed CTA escape the centered .container on this page. */
    .page-chapters .container { overflow: visible; }

    @media (max-width: 1024px) {
        .ch-do-grid, .ch-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .ch-hero-title { font-size: 2.6rem; }
        .ch-do-grid, .ch-grid { grid-template-columns: 1fr; }
        .ch-start-inner { padding: 48px 24px; }
        .ch-start-h { font-size: 2rem; }
    }
</style>
@endsection

@section('body')
@php
    // Volunteer-led local chapters. Each links to the contact form so people
    // can connect with (or ask to start) a chapter in their area.
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

    $chRegions = [
        'Northeast' => [
            ['New York City', 'Letter-writing, court support, and street outreach across the five boroughs.'],
            ['Boston', 'Campus organizing and solidarity actions across Greater Boston.'],
            ['Philadelphia', 'Court support and mutual aid for regional political prisoners.'],
            ['Washington, D.C.', 'Advocacy and days of action in the capital.'],
        ],
        'Midwest' => [
            ['Chicago', 'Letter-writing nights and defense-committee support.'],
            ['Twin Cities', 'Community education and prisoner solidarity in Minneapolis–St. Paul.'],
            ['Detroit', 'Local outreach and fundraising drives.'],
        ],
        'South' => [
            ['Atlanta', 'Court support and organizing across the Southeast.'],
            ['Austin', 'Teach-ins and letter-writing in Central Texas.'],
            ['Durham', 'Mutual aid and campus solidarity in the Triangle.'],
            ['New Orleans', 'Community education and Gulf Coast organizing.'],
        ],
        'West' => [
            ['Los Angeles', 'Film screenings, tabling, and prisoner outreach.'],
            ['Bay Area', 'Court support and defense-fund drives across the Bay.'],
            ['Seattle', 'Letter-writing and Pacific Northwest solidarity.'],
            ['Denver', 'Community education and regional actions.'],
        ],
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

    <div class="ch-divider"></div>

    {{-- Directory --}}
    <div class="ch-dir-head">
        <h2 class="ch-dir-title">Find a chapter</h2>
        <p class="ch-dir-note">Our chapter network is growing. Reach out to connect with organizers near you — and if there isn't a chapter in your area yet, we will help you start one.</p>
    </div>

    @foreach($chRegions as $region => $cities)
        <div class="ch-region">
            <div class="ch-region-h">{{ $region }}</div>
            <div class="ch-grid">
                @foreach($cities as [$city, $desc])
                    <a class="ch-card" href="/contact">
                        <div class="ch-card-city">{{ $city }}</div>
                        <div class="ch-card-desc">{{ $desc }}</div>
                        <span class="ch-card-link">Connect &rarr;</span>
                    </a>
                @endforeach
                <a class="ch-card is-start" href="/contact">
                    <div class="ch-card-city">Start one</div>
                    <div class="ch-card-desc">Don&rsquo;t see your city? Launch a chapter in your area.</div>
                    <span class="ch-card-link">Get started &rarr;</span>
                </a>
            </div>
        </div>
    @endforeach
</div>

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
