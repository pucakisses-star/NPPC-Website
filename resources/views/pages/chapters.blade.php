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

    /* ===== Find-a-chapter map band (site dark theme) ===== */
    .ch-mapband { position: relative; width: 100vw; margin-left: calc(50% - 50vw); margin-right: calc(50% - 50vw); margin-top: 72px; background: rgba(var(--fg-rgb),0.025); color: var(--fg); padding: 56px 0 64px; }
    .ch-mapband-inner { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
    .ch-map-prompt { font-size: 1.5rem; font-weight: 700; color: var(--fg); margin-bottom: 26px; }
    .ch-usmap { width: 100%; max-width: 980px; height: auto; display: block; margin: 0 auto; }
    .ch-usmap path { fill: rgba(var(--fg-rgb),0.08); stroke: rgba(var(--fg-rgb),0.22); stroke-width: 1; cursor: pointer; transition: fill 0.12s ease; }
    .ch-usmap path:hover,
    .ch-usmap path.is-selected { fill: var(--accent); }
    /* No focus-ring box around a picked state — the accent fill already marks
       it, for both mouse and keyboard users. */
    .ch-usmap path:focus,
    .ch-usmap path:focus-visible { outline: none; fill: var(--accent); }
    .ch-tip { position: fixed; z-index: 60; background: var(--gi-ev-gold, #f5b400); color: #15171c; font-size: 13px; font-weight: 800; letter-spacing: 0.02em; padding: 5px 9px; border-radius: 3px; pointer-events: none; transform: translate(-50%, -145%); display: none; box-shadow: 0 4px 14px rgba(0,0,0,0.4); }

    .ch-all-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; border-top: 1px solid rgba(var(--fg-rgb),0.14); padding-top: 22px; margin-top: 48px; }
    .ch-all-title { font-size: 1.05rem; font-weight: 800; color: var(--fg); letter-spacing: 0.02em; }
    .ch-reset { display: none; background: none; border: none; cursor: pointer; color: var(--accent-2); font-weight: 700; font-size: 13px; letter-spacing: 0.04em; text-transform: uppercase; }
    .ch-all-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0 44px; }
    .ch-ccard { display: block; text-decoration: none; color: inherit; border-top: 1px solid rgba(var(--fg-rgb),0.12); padding: 22px 2px; transition: background 0.15s; }
    .ch-ccard:first-child, .ch-all-grid .ch-ccard:nth-child(2), .ch-all-grid .ch-ccard:nth-child(3) { border-top: none; }
    .ch-ccard:hover { background: rgba(86,96,254,0.08); }
    .ch-ccard-top { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; }
    .ch-ccard-label { font-size: 12px; font-weight: 600; color: rgba(var(--fg-rgb),0.5); }
    .ch-ccard-loc { font-size: 12px; font-weight: 800; color: var(--accent-2); text-align: right; }
    .ch-ccard-name { font-size: 1.35rem; font-weight: 800; color: var(--fg); margin: 8px 0 4px; line-height: 1.15; }
    .ch-ccard:hover .ch-ccard-name { color: var(--accent); }
    .ch-ccard-desc { font-size: 13px; color: rgba(var(--fg-rgb),0.6); line-height: 1.5; }
    .ch-empty { padding: 26px 2px; font-size: 16px; color: rgba(var(--fg-rgb),0.65); line-height: 1.6; }
    .ch-empty a { color: var(--accent); font-weight: 700; text-decoration: none; }
    .ch-empty a:hover { text-decoration: underline; }

    /* Map + international side panel */
    .ch-mapwrap { display: grid; grid-template-columns: 1fr 260px; gap: 44px; align-items: start; }
    .ch-map-col { min-width: 0; }
    .ch-map-col .ch-usmap { margin: 0; }
    .ch-intl-h { font-size: 1.3rem; font-weight: 800; color: var(--accent-2); line-height: 1.15; padding-bottom: 12px; border-bottom: 2px solid var(--accent); margin: 4px 0 20px; }
    .ch-intl-list { display: grid; grid-template-columns: repeat(2, 1fr); gap: 22px 18px; }
    .ch-intl-box { display: flex; flex-direction: column; align-items: center; gap: 11px; width: 100%; background: none; border: 0; padding: 0; cursor: pointer; color: inherit; }
    /* The square tile that holds the country silhouette. */
    .ch-intl-sq { width: 100%; aspect-ratio: 1 / 1; display: flex; align-items: center; justify-content: center; border: 1px solid rgba(var(--fg-rgb),0.14); border-radius: 8px; transition: border-color 0.15s, background 0.15s; }
    .ch-intl-box:hover .ch-intl-sq,
    .ch-intl-box.is-selected .ch-intl-sq { border-color: var(--accent); background: rgba(86,96,254,0.08); }
    .ch-intl-ico { width: 50%; height: 50%; display: flex; align-items: center; justify-content: center; color: rgba(var(--fg-rgb),0.32); transition: color 0.15s; }
    .ch-intl-ico svg { width: 100%; height: 100%; display: block; }
    .ch-intl-box:hover .ch-intl-ico,
    .ch-intl-box.is-selected .ch-intl-ico { color: var(--accent); }
    .ch-intl-name { font-size: 14px; font-weight: 700; color: var(--fg); text-align: center; }
    @media (max-width: 860px) { .ch-mapwrap { grid-template-columns: 1fr; } }

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
        .ch-all-grid .ch-ccard:nth-child(3) { border-top: 1px solid rgba(var(--fg-rgb),0.12); }
    }
    @media (max-width: 768px) {
        .ch-hero-title { font-size: 2.6rem; }
        .ch-do-grid { grid-template-columns: 1fr; }
        .ch-all-grid { grid-template-columns: 1fr; }
        .ch-all-grid .ch-ccard { border-top: 1px solid rgba(var(--fg-rgb),0.12); }
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

    // State codes that currently have a chapter.
    $chStateNames = [
        'NY' => 'New York', 'MA' => 'Massachusetts', 'PA' => 'Pennsylvania', 'DC' => 'District of Columbia',
        'IL' => 'Illinois', 'MN' => 'Minnesota', 'MI' => 'Michigan', 'GA' => 'Georgia', 'TX' => 'Texas',
        'NC' => 'North Carolina', 'LA' => 'Louisiana', 'CA' => 'California', 'WA' => 'Washington', 'CO' => 'Colorado',
    ];

    // Every state -> full name (any state is clickable on the map).
    $allStateNames = [
        'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California',
        'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'DC' => 'District of Columbia',
        'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois',
        'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana',
        'ME' => 'Maine', 'MD' => 'Maryland', 'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota',
        'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
        'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
        'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma', 'OR' => 'Oregon',
        'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina', 'SD' => 'South Dakota',
        'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont', 'VA' => 'Virginia',
        'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming',
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

    // International solidarity chapters (shown in the side panel).
    $chIntl = [
        ['code' => 'CAN', 'flag' => '🇨🇦', 'name' => 'Canada', 'city' => 'Toronto', 'loc' => 'Toronto, Canada', 'desc' => 'Cross-border solidarity and letter-writing for U.S. and Canadian political prisoners.'],
        ['code' => 'GBR', 'flag' => '🇬🇧', 'name' => 'United Kingdom', 'city' => 'London', 'loc' => 'London, UK', 'desc' => 'International solidarity actions and prisoner support based in London.'],
        ['code' => 'MEX', 'flag' => '🇲🇽', 'name' => 'Mexico', 'city' => 'Mexico City', 'loc' => 'Mexico City, Mexico', 'desc' => 'Support for cross-border and Latin American political-prisoner campaigns.'],
    ];

    // Country silhouette icons (viewBox 0 0 40 40) keyed by ISO code.
    $chIntlIcons = [
        'CAN' => 'M5.6,27.5L5.5,27.5L5,26.5L4.8,26.1L4.2,25.5L4.3,24.8L4.5,24.4L4.2,23.9L4.4,23.4L4.2,22.7L4.4,22.4L4.7,22.2L4.9,21.8L4.6,21L4.7,20.1L4.7,19.6L4.6,19.1L4.6,18.8L4.6,18.4L4.2,18.3L3.7,18.5L3.8,17.9L3.7,17.5L3.6,17.1L3.4,16.9L6,13.8L7.7,11.8L7.9,12.2L8,12.7L8.2,12.9L8.5,12.8L8.9,12.8L9.1,13.1L9.6,13L10,13.1L10,13.4L10.3,13.4L10.5,13.1L10.6,13.3L10.6,14L11.1,13.7L10.9,14.2L11.2,14.2L11.4,14.1L11.7,14.2L11.9,14.6L12.3,15.1L12.6,15.3L12.9,15.3L13.1,15.8L12.6,16L13.1,16.3L13.8,16.4L14,16.3L14.2,16.8L14.6,16.5L14.4,16.2L14.6,16L14.9,16L15.1,15.9L15.3,16.1L15.5,16.6L15.8,16.5L16.2,16.9L16.6,16.8L17,16.8L17,16.4L17.2,16.3L17.6,16.5L17.6,17.2L17.8,16.6L18,16.7L18.1,16L17.8,15.5L17.5,15.2L17.6,14.5L17.8,14L18.1,14.1L18.4,14.4L18.7,15.1L18.5,15.5L19,15.6L19.1,16.3L19.3,15.7L19.7,16.2L19.7,16.7L20,17.1L20.2,16.6L20.3,15.9L20.2,15.2L20.5,15.2L20.9,15.2L21.3,15.5L21.4,15.8L21.3,16.2L21.6,16.5L21.6,16.9L21.2,17.5L20.8,17.6L20.5,17.5L20.5,17.8L20.3,18.5L20.2,18.8L19.9,19.3L19.5,19.4L19.3,19.7L19.3,20.2L19,20.3L18.6,20.9L18.3,21.7L18.1,22.2L18.1,23L18.7,23.1L18.9,23.8L19.1,24.3L19.6,24.1L20.3,24.4L20.7,24.6L21,24.9L21.6,25L22,25.2L22.7,25.1L23.1,25.1L23.2,25.7L23.4,26.4L23.9,27.1L24.7,27.6L24.9,27.3L25,26.5L24.5,25.5L24.1,25.2L24.7,24.8L25,24.2L25.1,23.6L24.9,23.2L24.5,22.7L23.9,22.3L24.1,21.5L23.8,20.9L23.4,19.9L23.6,19.7L24.1,19.7L24.5,19.6L24.7,19.4L25,19.5L25.6,19.7L25.8,19.9L26.3,19.8L26.6,20.3L27,21.1L27.4,21.1L27.8,21.3L28.1,20.7L28,19.9L28.1,19.5L28.6,19.9L29.5,20.5L30.2,21L30.3,21.5L30.9,21.6L31.5,21.7L32.1,21.5L32.4,21.6L32.9,22L33.2,21.9L33.5,22L33.9,22.6L33.8,23L33.7,23.4L33.2,24L33,24.8L32.4,25.3L31.5,25.7L30.9,26L30.5,26.2L30.4,26.8L30,27.4L29.7,28.5L29.4,29.3L29.7,29L30.1,27.9L30.8,26.9L31.4,26.6L31.9,26.7L31.7,27.3L32.1,27.9L32.5,28.3L33.2,28.4L33.9,27.9L33.9,26.9L34.2,27.4L34.6,27.4L34.3,28.1L33.5,29.1L33.2,29.5L32.8,30.3L32.5,30.4L32.2,29.8L32.8,28.9L32.1,29.3L31.6,29.6L31.2,29.4L30.8,28.5L30.5,28.4L30.3,28.7L30.1,28.5L29.9,29.2L30,29.7L29.9,30.1L29.8,30.3L29.6,30.4L29.6,30.6L28.8,30.9L28,31.1L27.9,31.3L27.5,31.9L27.4,32L27.4,32.3L26.9,32.5L26.4,32.6L26.2,32.7L26.3,32.9L26.4,33.1L26.4,33.1L25.8,33.6L25.3,33.9L24.8,34.3L24.7,34.4L24.5,34.3L24.4,34.2L24.4,34.1L24.5,33.9L24.7,33.5L24.7,33L24.5,32.5L24.3,31.9L23.7,31.6L23.8,31.5L23.7,31.4L23.6,31.5L23.4,31.4L23.4,31.2L23.3,31.3L23.2,31.3L23.2,31.2L23.1,31.2L23,31L22.6,30.8L22.2,30.7L21.7,30.5L21.2,30.3L20.8,30.5L20.7,30.5L20.1,30.4L19.7,30.5L19.2,30.3L18.8,30.2L18.4,30.2L18.3,30.1L18.2,29.7L18,29.7L18,30L17.1,30L15.5,29.9L13.9,29.8L12.6,29.6L11.2,29.3L9.9,29L8.5,28.6L8.1,28.5L6.8,28ZM21.5,20.2L21.7,19.9L22.1,19.8L22.1,19.9L21.9,20.4L21.6,20.4ZM21,12.9L20.7,12.6L20.6,12.4L20.7,12.3L21.2,12.3L21.6,12.5L21.7,12.7L21.5,12.8L21.2,12.8L21.1,13ZM22.7,20.2L22.8,20L22.9,20L23.1,20.1L23,20.5L22.9,20.5L22.7,20.3ZM18.1,11.8L18,12.1L17.7,12.1L17.5,11.9L17.6,11.6L17.9,11.4L18,11.6ZM18,10.1L17.9,10.1L17.6,10.1L17.6,9.9L17.9,9.9L18,10ZM17.6,9.2L17.7,9.5L17.7,9.7L17.5,9.8L17.3,9.7L17.3,9.4L17.3,9.1L17.5,9.2ZM19.1,12.2L18.8,12.1L18.3,11.9L18.2,11.5L18.2,11.2L18,10.9L17.7,10.8L17.5,10.6L17.5,10.3L17.9,10.4L18,10.6L18.4,10.6L18.5,10.8L18.5,11L18.7,11.2L18.8,11.3L19.1,11.3L19.3,11.4L19.6,11.2L19.9,11L20.2,11L20.5,11.2L20.6,11.5L20.5,11.7L20.3,11.9L20,11.9L19.5,12.1ZM15.5,9.4L15.6,9.5L15.5,9.7L15.2,9.8L15.1,9.6L15.2,9.4ZM15.6,8.9L15.8,9.1L15.6,9.2L15.3,9.2L15.4,9.1L15.6,8.9ZM34.3,23.1L34.3,23.6L34.4,24.3L34.5,23.9L34.8,23.9L34.8,24.2L35.3,24.1L35.3,23.8L35.8,23.8L36,24.3L36.2,24L36.5,24.2L36.8,24.5L37,25.1L36.8,25.3L36.5,25.4L36.3,24.8L36.1,24.8L36,25.7L35.8,25.8L35.9,25.3L35.4,25.5L35,25.8L34.2,26.3L34.1,26.1L34.2,25.8L33.9,25.7L34,25.1L33.9,23.9L33.9,23.4L34,23L34.2,22.9ZM21.2,18.4L21.6,18.6L21.9,18.7L22.1,19L22.3,18.9L22.5,19.1L22.3,19.4L21.8,19.3L21.6,19L21.3,19.5L21,19.9L20.8,19.5L20.4,19.6L20.6,19.3L20.6,18.7L20.6,18.1L20.8,18.1L20.9,18.4L21,18.2ZM21.3,13.2L21.4,12.9L21.9,13L22.3,13.3L22.5,13.5L22.8,13.2L23.2,13.6L23.8,13.6L24.1,13.8L24.6,14.3L24.3,14.8L25.1,14.9L25.5,14.9L26.2,15.2L26.6,15.1L26.8,15.5L26.7,16.5L26.3,16.4L25.6,16L25.3,16.2L25.4,16.6L25.9,16.8L26.4,17L26.6,17.1L27.1,17.6L27.2,18.1L26.8,18.1L25.8,17.9L26.5,18.3L27,18.5L27.1,18.7L26.2,18.9L25.4,18.7L24.9,18.6L25,18.3L24.4,18.2L23.9,18L23.9,18.2L23.1,18.6L22.8,18.4L22.9,17.8L23.4,17.7L23.9,17.4L23.8,17.2L23.7,16.8L23.8,16.1L23.6,15.8L23.5,15.6L23,15.4L22.4,15.4L22.5,15.1L22.1,14.8L21.9,14.8L21.7,14.7L21.6,14.9L21.2,15.1L20.3,15.1L19.8,15L19.4,14.9L19.2,14.7L19.4,14.4L19,14.4L18.9,13.7L19,13.1L19.2,12.8L19.6,12.5L19.6,13L19.8,13.4L19.9,12.8L20.3,12.4L20.8,13L20.9,13.5ZM17.9,12.4L18.3,12.4L18.7,12.6L18.5,13.2L18.2,13.4L18,13.9L17.8,13.9L17.6,13.3L17.7,12.9L17.8,12.6ZM13.3,10.1L13.7,9.7L14.2,9.4L14.4,9.5L14.7,9.5L14.5,10L14.3,10.2L14.1,10.2L13.7,10.3L13.4,10.3ZM3.3,22.4L3.7,22.5L3.2,23.1L3.2,23.8L3.1,23.7L3,23.3L3.1,23L3,22.7L3.1,22.4L3.2,22.2ZM16.4,8.7L16.7,8.9L17,9.2L17.1,9.5L17.1,9.8L16.9,9.7L16.7,9.4L16.4,9.4L16.5,9.2L16.4,9ZM5.2,27.7L4.9,27.7L4.4,27.1L4.3,26.8L4.1,26.4L4.1,26.2L3.7,25.9L3.7,25.4L3.9,25.3L4.2,25.7L4.4,25.9L4.7,26.1L4.8,26.4L4.9,26.8L5.2,27.3ZM13,11.2L13.2,11.5L13.6,11.7L13.7,11.9L13.8,12.3L13.5,12.4L12.9,12.7L12.4,13L12.3,13.3L11.6,13.4L11.7,13L11.4,12.5L11.6,12.3L12,11.9L12.3,11.5L12.4,11.1ZM15.6,11L15.8,11L16,11L15.9,11.4L15.8,11.7L15.2,11.7L14.6,11.8L14.3,11.8L14.4,11.6L14.8,11.4L14,11.2L13.8,11L14.3,10.4L14.5,10.3L14.9,10.7L15.1,11.1L15.4,11.3L15.3,10.6L15.5,10.3L15.6,10.5L15.6,10.8ZM15.5,13L15.7,13.3L15.7,14L15.7,14.5L16.1,14.9L16.5,15.2L16.4,15.6L16,15.6L16.1,15.9L16,16.1L15.6,15.9L15.2,15.7L14.9,15.7L14.4,15.8L13.7,15.8L13.3,15.7L13.2,15.4L13,15.1L12.7,15.1L12.6,14.5L12.8,14.5L13.2,14.5L13.5,14.6L13.8,14.6L13.4,14.3L12.9,14.2L12.6,14.1L12.6,13.8L13.2,13.7L12.9,13.6L12.6,13.3L12.9,12.9L13.2,12.6L13.8,12.4L14,12.6L13.8,12.9L14.3,12.8L14.5,13.2L14.8,12.9L14.9,13.2L15,13.9L15.1,13.6L15.1,12.9L15.3,12.8ZM16.7,13.4L16.5,12.9L16.8,12.6L17,12.8L17.4,12.7L17.4,12.9L17.2,13.2L17.5,13.5L17.5,14.2L17.1,14.4L16.9,14.3L16.8,14.1L16.3,13.5L16.3,13.3ZM15.6,12.6L15.8,12.6L16,12.8L15.7,13.2L15.5,12.7ZM17.2,10.6L17.4,10.9L17.4,11.3L17.3,11.8L17,11.9L16.8,11.8L16.8,11.3L16.5,11.4L16.6,10.8L16.7,10.9L17,10.7L17.2,10.7ZM17.7,7.9L17.7,7.7L17.8,7.7L17.8,7.5L18,7.5L18.2,7.8L18.4,8L18.6,8.1L18.8,8.5L19,8.7L18.8,8.9L18.7,9.5L18.4,9.6L18.1,9.5L17.9,9.2L17.9,9L18,8.8L17.8,8.8L17.6,8.6L17.6,8.2ZM18.1,7L18.2,6.9L18.3,6.9L18.5,6.7L18.6,6.4L18.7,6.5L18.9,6.6L18.8,6.3L18.9,6.1L19.1,6L19.3,5.9L19.4,6L19.5,5.8L19.7,5.7L19.9,5.7L20.1,5.6L20.4,5.6L20.6,5.7L20.7,5.9L20.7,6.2L20.5,6.5L20.5,6.7L20.7,6.5L20.7,7L20.6,7.3L20.7,7.9L20.5,8.1L20.5,8.2L20.1,8.4L20.3,8.5L20.3,8.6L20.5,8.9L20.4,9.2L20.3,9.4L20.3,9.7L20.2,10L20.2,10.1L20.5,10L20.5,10.2L20.2,10.7L19.8,10.6L19.3,10.8L19,10.7L18.7,10.7L18.7,10.4L18.9,10.2L18.8,9.7L18.9,9.7L19.3,9.9L19,9.5L18.8,9.4L18.9,9.1L19.1,8.9L19.1,8.7L18.8,8.4L18.7,8.1L19.1,8.1L19.2,8.1L19.3,7.8L19,7.8L18.6,7.9L18.4,7.7L18.3,7.5L18.1,7.3ZM23.1,16.2L23,16.4L22.8,16.6L22.6,16.3L22.6,15.9L22.8,15.7L23,15.8L23.1,16.1ZM17.6,15.7L17.7,15.9L17.6,16.2L17.2,16L17,16L16.7,15.7L16.9,15.5L17.1,15.2L17.4,15.4L17.5,15.5ZM31.4,26.1L31.5,25.9L32.1,25.8L32.6,25.8L32.7,25.9L32.5,26.1L31.9,26.2ZM32.4,27.7L32.7,28L33.1,27.9L33.5,27.6L33.4,28L33.2,28.1L32.6,28.2L32.4,28Z',
        'MEX' => 'M3,9.1L4.3,9L5.7,8.8L5.6,9.1L7.3,9.7L9.8,10.6L12.1,10.6L13,10.6L13,10.1L14.9,10.1L15.3,10.6L15.9,11L16.6,11.5L16.9,12.2L17.2,12.9L17.8,13.3L18.7,13.7L19.4,12.7L20.3,12.7L21.1,13.2L21.7,14.1L22.1,14.8L22.7,15.5L23,16.4L23.3,17L24.2,17.4L25,17.7L25.4,17.6L25,18.7L24.8,19.6L24.7,21.2L24.6,21.8L24.8,22.5L25.1,23.1L25.4,24L26.1,24.9L26.4,25.6L26.8,26.2L28,26.5L28.5,27L29.4,26.7L30.3,26.5L31.1,26.3L31.8,26.1L32.6,25.6L32.8,24.9L32.9,23.9L33.1,23.6L33.9,23.3L35.1,23L36.1,23L36.7,22.9L37,23.2L37,23.8L36.4,24.5L36.1,25.2L36.3,25.4L36.1,25.9L35.9,26.8L35.6,26.5L35.3,26.6L35.1,26.6L34.7,27.3L34.5,27.1L34.4,27.2L34.4,27.4L33.4,27.4L32.3,27.4L32.3,28L31.8,28L32.2,28.4L32.6,28.7L32.8,28.9L32.9,29L32.9,29.4L31.5,29.4L30.9,30.4L31.1,30.6L31,30.9L30.9,31.2L29.7,29.9L29.1,29.6L28.2,29.3L27.5,29.3L26.6,29.8L26.1,29.9L25.3,29.6L24.4,29.4L23.4,28.8L22.6,28.7L21.3,28.1L20.3,27.6L20.1,27.2L19.4,27.2L18.3,26.8L17.8,26.3L16.6,25.6L16,24.8L15.8,24.3L16.2,24.1L16,23.8L16.3,23.5L16.3,23.1L15.9,22.5L15.8,22L15.4,21.4L14.5,20.2L13.3,19.3L12.8,18.5L11.8,18L11.6,17.7L11.8,16.9L11.2,16.6L10.6,16L10.3,15.1L9.7,15L9,14.4L8.5,13.7L8.4,13.3L7.8,12.4L7.4,11.4L7.5,10.9L6.7,10.3L6.3,10.4L5.6,10L5.5,10.6L5.6,11.2L5.8,12.2L6.1,12.7L7,13.6L7.2,13.9L7.3,14L7.5,14.4L7.7,14.4L7.9,15.2L8.2,15.5L8.5,16L9.2,16.6L9.6,17.8L9.9,18.3L10.2,18.9L10.3,19.6L10.8,19.6L11.3,20.2L11.7,20.7L11.6,20.9L11.2,21.4L11,21.4L10.7,20.6L9.9,19.9L9.1,19.3L8.5,19L8.6,18.1L8.4,17.4L7.9,17.1L7.1,16.5L7,16.7L6.7,16.3L6,16L5.3,15.3L5.4,15.2L5.9,15.3L6.3,14.8L6.3,14.2L5.5,13.3L4.8,13L4.4,12.1L4,11.3L3.5,10.2Z',
        'GBR' => 'M12.6,22.6L10.9,21.8L9.5,21.8L9.9,19.7L9.5,17.6L11.4,17.4L13.8,19.9ZM19.7,24.3L19.7,24.3L20,22.1L18.5,19.7L18.4,19.7L15.7,19L15.1,17.9L16,16.1L15.2,15L14,16.9L13.8,13L12.7,10.9L13.5,6.5L15.3,3L17.1,3.4L19.9,3L17.4,7.7L19.8,7.1L22.2,7.1L21.7,10.5L19.6,14.2L22,14.5L22.1,14.9L24.2,19.6L25.7,20.2L27.1,24.6L27.8,26.1L30.5,26.9L30.3,29.3L29.1,30.3L30,32.2L28,34.1L24.9,34.1L21,35.1L20,34.4L18.5,36L16.4,35.6L14.8,37L13.6,36.3L16.9,32.5L18.9,31.7L18.9,31.7L15.4,31.1L14.7,29.7L17.1,28.5L15.8,26.5L16.3,24Z',
    ];
    // Fold the international chapters into the "All Chapters" list (tagged by country code).
    foreach ($chIntl as $ic) {
        $chapters[] = ['st' => $ic['code'], 'loc' => $ic['loc'], 'city' => $ic['city'], 'desc' => $ic['desc']];
    }

    // Every selectable place -> display name (US states + countries) and the set that has chapters.
    $chPlaceNames = $allStateNames + collect($chIntl)->pluck('name', 'code')->all();
    $chapterCodes = array_merge(array_keys($chStateNames), array_column($chIntl, 'code'));
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
        <div class="ch-mapwrap">
            <div class="ch-map-col">
                <div class="ch-map-prompt" id="ch-prompt">Select a state on the map</div>
                @include('partials.us-chapters-map')
            </div>
            <aside class="ch-intl">
                <h3 class="ch-intl-h">Our Chapters<br>around the World</h3>
                <div class="ch-intl-list">
                    @foreach($chIntl as $ic)
                        <button type="button" class="ch-intl-box" data-state="{{ $ic['code'] }}">
                            <span class="ch-intl-sq">
                                <span class="ch-intl-ico" aria-hidden="true">
                                    <svg viewBox="0 0 40 40" preserveAspectRatio="xMidYMid meet"><path d="{{ $chIntlIcons[$ic['code']] ?? '' }}" fill="currentColor"/></svg>
                                </span>
                            </span>
                            <span class="ch-intl-name">{{ $ic['name'] }}</span>
                        </button>
                    @endforeach
                </div>
            </aside>
        </div>

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
        <div class="ch-empty" id="ch-empty" hidden>
            No chapters in <strong class="ch-empty-state"></strong> yet.
            <a href="/contact">Start one &rarr;</a>
        </div>
    </div>
    <div class="ch-tip" id="ch-tip"></div>
</section>

<script>
    (function () {
        var svg = document.querySelector('.ch-usmap');
        if (!svg) return;
        var names = @json($chPlaceNames);
        var chapterSet = @json($chapterCodes);
        var tip = document.getElementById('ch-tip');
        var prompt = document.getElementById('ch-prompt');
        var grid = document.getElementById('ch-allgrid');
        var empty = document.getElementById('ch-empty');
        var reset = document.getElementById('ch-reset');
        var selected = null;

        function selectState(code) {
            var name = names[code] || code;
            prompt.textContent = name;
            reset.style.display = 'inline-block';
            var has = chapterSet.indexOf(code) !== -1;
            grid.style.display = has ? '' : 'none';
            empty.hidden = has;
            if (has) {
                grid.querySelectorAll('.ch-ccard').forEach(function (c) {
                    c.style.display = c.getAttribute('data-state') === code ? '' : 'none';
                });
            } else {
                empty.querySelector('.ch-empty-state').textContent = name;
            }
        }

        function clear() {
            prompt.textContent = 'Select a state on the map';
            reset.style.display = 'none';
            empty.hidden = true;
            grid.style.display = '';
            grid.querySelectorAll('.ch-ccard').forEach(function (c) { c.style.display = ''; });
        }

        svg.querySelectorAll('path[data-state]').forEach(function (p) {
            var code = p.getAttribute('data-state');
            var name = names[code] || code;
            p.setAttribute('tabindex', '0');
            p.setAttribute('role', 'button');
            p.setAttribute('aria-label', name);
            p.addEventListener('mouseenter', function () { tip.textContent = code; tip.style.display = 'block'; });
            p.addEventListener('mousemove', function (e) { tip.style.left = e.clientX + 'px'; tip.style.top = e.clientY + 'px'; });
            p.addEventListener('mouseleave', function () { tip.style.display = 'none'; });
            function pick() {
                if (selected) selected.classList.remove('is-selected');
                selected = p; p.classList.add('is-selected');
                selectState(code);
            }
            p.addEventListener('click', pick);
            p.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); pick(); } });
        });

        // International chapter boxes behave like states (no cursor tooltip).
        document.querySelectorAll('.ch-intl-box').forEach(function (box) {
            var code = box.getAttribute('data-state');
            box.addEventListener('click', function () {
                if (selected) selected.classList.remove('is-selected');
                selected = box; box.classList.add('is-selected');
                selectState(code);
            });
        });

        reset.addEventListener('click', function () {
            if (selected) { selected.classList.remove('is-selected'); selected = null; }
            clear();
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
