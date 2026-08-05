@extends('app')

@section('title', 'A History of Political Prisoner Activism | NPPC')

@section('meta_description')A century of freedom work, year by year: the defense committees, amnesty campaigns, and mass-defense movements that fought for America's political prisoners — from the General Defense Committee of 1917 to the census era.@endsection

@section('og_image'){{ asset('storage/history/attica.jpg') }}@endsection

@section('head')
<style>
/* ============================================================
   Movement History — a year-by-year timeline of political
   prisoner activism. Masonry photo hero, sticky year sidebar
   with scrollspy, color-banded timeline blocks with accent
   bars, category eyebrows, reveal animations, and circular
   "Movement Victory" seals on the campaigns that won.
   ============================================================ */
body.page-movement-history main.container,
body.page-movement-history .container { width: 100% !important; max-width: none !important; padding-left: 0 !important; padding-right: 0 !important; }
body.page-movement-history { background: #0a0a12; }

.mh { --ink: #ececf2; --acc: #5660fe; --acc2: #8f97ff; --deep: #0a0a12; --navy: #12122a;
      color: var(--ink); font-size: 16px; line-height: 1.7; overflow-x: clip; }
.mh a { color: inherit; }

/* reveal */
.mh .reveal { opacity: 0; transform: translateY(28px); transition: opacity .8s ease, transform .8s cubic-bezier(.22,1,.36,1); }
.mh .reveal.in { opacity: 1; transform: none; }
@media (prefers-reduced-motion: reduce) { .mh .reveal { opacity: 1 !important; transform: none !important; transition: none; } }

/* ── top strip ───────────────────────────────────────────── */
.mh-nav { position: sticky; top: 0; z-index: 40; background: rgba(10,10,18,.9); backdrop-filter: blur(8px);
  border-bottom: 1px solid rgba(236,236,242,.1); }
.mh-nav-in { max-width: 1280px; margin: 0 auto; padding: 14px 28px; display: flex; justify-content: space-between; align-items: center; }
.mh-nav b { font-size: 14px; font-weight: 900; letter-spacing: .12em; text-transform: uppercase; color: var(--ink); }
.mh-nav a { font-size: 13px; font-weight: 700; color: var(--acc2); text-decoration: none; }
.mh-nav a:hover { text-decoration: underline; }

/* ── hero ────────────────────────────────────────────────── */
.mh-hero { display: grid; grid-template-columns: minmax(0, 5fr) minmax(0, 7fr); gap: 40px; align-items: center;
  max-width: 1280px; margin: 0 auto; padding: 70px 28px 60px; }
.mh-hero-sub { font-size: clamp(1.3rem, 2.4vw, 2rem); font-weight: 700; color: rgba(236,236,242,.9); line-height: 1.3; margin: 0 0 14px; }
.mh-hero-title { font-size: clamp(2.8rem, 6vw, 4.8rem); font-weight: 900; line-height: 1.06; margin: 0 0 34px; letter-spacing: -.02em; color: var(--ink); }
.mh-hero-title span { display: inline-block; background: var(--acc); color: #fff; padding: 2px 16px 6px; margin-bottom: 8px; }
/* animated headline (CodyHouse-style slide rotator) */
.mh-rotator { position: relative; display: inline-block; overflow: hidden; vertical-align: bottom;
  text-align: left; white-space: nowrap; transition: width .45s cubic-bezier(.22,1,.36,1); }
.mh-rotator b { display: inline-block; font-weight: inherit; white-space: nowrap;
  position: absolute; left: 0; top: 0; opacity: 0; transform: translateY(105%);
  transition: transform .55s cubic-bezier(.22,1,.36,1), opacity .45s ease; }
.mh-rotator b.is-visible { position: relative; opacity: 1; transform: none; }
.mh-rotator b.is-hidden { transform: translateY(-105%); opacity: 0; }
@media (prefers-reduced-motion: reduce) {
  .mh-rotator, .mh-rotator b { transition: none; }
}
.mh-chev { width: 46px; height: 46px; border-radius: 50%; border: 2px solid var(--acc); background: none; cursor: pointer;
  display: flex; align-items: center; justify-content: center; transition: background .2s; }
.mh-chev::after { content: ''; width: 11px; height: 11px; border-right: 2px solid var(--acc); border-bottom: 2px solid var(--acc);
  transform: rotate(45deg) translate(-1px,-1px); transition: border-color .2s; }
.mh-chev:hover { background: var(--acc); } .mh-chev:hover::after { border-color: #fff; }
.mh-masonry { columns: 3; column-gap: 12px; }
.mh-brick { break-inside: avoid; margin-bottom: 12px; opacity: 0; transform: translateY(20px); animation: mhFade .9s ease forwards; }
.mh-brick img { width: 100%; display: block; border-radius: 4px; }
.mh-brick.f2 { animation-delay: .25s; } .mh-brick.f3 { animation-delay: .5s; } .mh-brick.f4 { animation-delay: .75s; }
@keyframes mhFade { to { opacity: 1; transform: none; } }
@media (prefers-reduced-motion: reduce) { .mh-brick { animation-duration: .01s; animation-delay: 0s !important; } }
@media (max-width: 900px) { .mh-hero { grid-template-columns: 1fr; } .mh-masonry { columns: 3; } }

/* ── intro / seal legend ─────────────────────────────────── */
.mh-intro { max-width: 1280px; margin: 0 auto; padding: 0 28px 70px; display: grid; grid-template-columns: minmax(0, 8fr) minmax(0, 4fr); gap: 50px; align-items: center; }
.mh-intro h2 { font-size: clamp(1.15rem, 1.9vw, 1.5rem); font-weight: 700; line-height: 1.55; margin: 0; color: rgba(236,236,242,.9); }
.mh-legend { display: flex; align-items: center; gap: 18px; }
.mh-legend p { font-size: 14px; font-weight: 700; color: rgba(236,236,242,.85); margin: 0; }
@media (max-width: 900px) { .mh-intro { grid-template-columns: 1fr; gap: 30px; } }

/* victory seal (inline SVG, rotates slowly) */
.mh-seal { width: 110px; height: 110px; flex: 0 0 auto; }
.mh-seal svg { width: 100%; height: 100%; display: block; }
.mh-seal .spin { transform-origin: 60px 60px; animation: mhSpin 24s linear infinite; }
@media (prefers-reduced-motion: reduce) { .mh-seal .spin { animation: none; } }
@keyframes mhSpin { to { transform: rotate(360deg); } }

/* ── timeline layout ─────────────────────────────────────── */
.mh-tl { display: grid; grid-template-columns: 150px minmax(0, 1fr); max-width: 1440px; margin: 0 auto; }
.mh-side { position: relative; }
.mh-side ul { position: sticky; top: 76px; list-style: none; margin: 0; padding: 30px 0 30px 28px;
  max-height: calc(100vh - 76px); overflow-y: auto; scrollbar-width: none; }
.mh-side ul::-webkit-scrollbar { display: none; }
.mh-side li { margin-bottom: 10px; }
.mh-side a { font-size: 14px; font-weight: 700; color: rgba(236,236,242,.35); text-decoration: none; transition: color .2s, font-size .2s; }
.mh-side li.on a { color: var(--acc2); font-weight: 900; font-size: 17px; }
.mh-main { min-width: 0; }
@media (max-width: 900px) { .mh-tl { grid-template-columns: 1fr; } .mh-side { display: none; } }

.mh-year { font-size: clamp(3.4rem, 8vw, 6.5rem); font-weight: 900; letter-spacing: -.03em; color: var(--ink);
  padding: 44px 40px 18px; line-height: 1; scroll-margin-top: 70px; }

/* ── timeline blocks ─────────────────────────────────────── */
.mh-block { padding: 0 40px; }
.mh-block-in { display: flex; border-radius: 8px; overflow: hidden; margin-bottom: 26px; }
.mh-accent { flex: 0 0 10px; }
.mh-block-body { flex: 1; min-width: 0; padding: 44px 46px; display: grid; grid-template-columns: minmax(0, 7fr) minmax(0, 5fr); gap: 44px; align-items: start; }
.mh-block-body.noimg { grid-template-columns: 1fr; }
/* palette bands (the reference's beige/white/blue/black rotation, in our scheme) */
.mh-c-white  { background: #15152e; } .mh-c-white  .mh-accent { background: #8f97ff; }
.mh-c-lav    { background: #1a1a3c; } .mh-c-lav    .mh-accent { background: #9aa1e8; }
.mh-c-blue   { background: #1d2358; } .mh-c-blue   .mh-accent { background: var(--acc); }
.mh-c-sand   { background: #26221a; } .mh-c-sand   .mh-accent { background: #b3a87f; }
.mh-c-navy   { background: #0d0d20; } .mh-c-navy .mh-accent { background: var(--acc); }
.mh-eyebrow { display: flex; align-items: center; gap: 9px; margin-bottom: 16px; }
.mh-eyebrow-dot { width: 9px; height: 9px; border-radius: 50%; background: var(--acc); }
.mh-eyebrow-h { font-size: 12px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; color: var(--ink); }
.mh-h { font-size: clamp(1.4rem, 2.6vw, 2.1rem); font-weight: 900; line-height: 1.15; color: var(--ink); margin: 0 0 16px; letter-spacing: -.01em; }
.mh-sub { font-size: 16px; line-height: 1.75; color: rgba(236,236,242,.85); margin: 0; }
.mh-sub a { text-underline-offset: 3px; }
.mh-img img { width: 100%; display: block; border-radius: 4px; box-shadow: 0 20px 50px rgba(0,0,0,.5); }
.mh-desc { font-size: 12.5px; color: rgba(236,236,242,.5); margin-top: 10px; line-height: 1.6; }
.mh-block .mh-seal { width: 96px; height: 96px; margin-top: 24px; }
@media (max-width: 900px) {
  .mh-year { padding: 46px 20px 14px; }
  .mh-block { padding: 0 16px; }
  .mh-block-body { grid-template-columns: 1fr; gap: 26px; padding: 30px 24px; }
}

/* ── end cap ─────────────────────────────────────────────── */
.mh-end { background: radial-gradient(ellipse at 50% 30%, #1c1c46, var(--deep)); color: #ececf2; text-align: center; padding: 110px 28px; margin-top: 70px; }
.mh-end h2 { font-size: clamp(2rem, 4.6vw, 3.4rem); font-weight: 900; color: #fff; margin: 0 0 18px; }
.mh-end p { color: rgba(236,236,242,.7); max-width: 58ch; margin: 0 auto 34px; }
.mh-btn { display: inline-block; padding: 14px 30px; border-radius: 4px; background: var(--acc); color: #fff; font-weight: 800;
  font-size: 14px; letter-spacing: .06em; text-transform: uppercase; text-decoration: none; margin: 0 8px 12px; }
.mh-btn.ghost { background: transparent; border: 2px solid rgba(255,255,255,.5); }
.mh-btn:hover { opacity: .9; }
</style>
@endsection

@section('body')
@php
// Circular "Movement Victory" seal (inline SVG so it needs no asset).
$seal = <<<'SVG'
<svg viewBox="0 0 120 120" aria-label="Movement Victory — someone came home">
  <defs><path id="mh-circ" d="M 60,60 m -44,0 a 44,44 0 1,1 88,0 a 44,44 0 1,1 -88,0"/></defs>
  <circle cx="60" cy="60" r="58" fill="#5660fe"/>
  <circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,.55)" stroke-width="1.5"/>
  <g class="spin">
    <text font-size="10.5" font-weight="800" letter-spacing="2.2" fill="#fff">
      <textPath href="#mh-circ">MOVEMENT VICTORY &#183; SOMEONE CAME HOME &#183;</textPath>
    </text>
  </g>
  <path d="M60 38 L65.5 51.5 L80 52.5 L69 62 L72.5 76 L60 68.5 L47.5 76 L51 62 L40 52.5 L54.5 51.5 Z" fill="#fff"/>
</svg>
SVG;

// year => blocks: [category, title, text, img, caption, seal, palette]
$P = 'http://104.238.162.40'; // (photos are root-relative on prod; kept relative below)
$timeline = [
    ['1917', [
        ['History', 'The General Defense Committee',
         'Facing hundreds of Espionage Act prosecutions of its members, the Industrial Workers of the World founds
          the General Defense Committee — the first national organization built to defend political cases: bail,
          lawyers, prison relief, and publicity, run by the accused themselves.',
         '/storage/history/the-labor-movement.jpg', 'IWW organizers, from the census archive.', false, 'mh-c-lav'],
    ]],
    ['1920', [
        ['Transforming Systems', 'Out of the Palmer Raids',
         'The National Civil Liberties Bureau — born defending World War I objectors and raid targets — reorganizes
          as the ACLU. Deportation defense becomes a discipline; the case file becomes a weapon. Eugene Debs,
          census entry no. 0041, runs for president from an Atlanta cell and takes nearly a million votes.',
         '/storage/history/world-war-i.jpg', 'The wartime speech prosecutions filled the first ledgers.', false, 'mh-c-white'],
    ]],
    ['1931', [
        ['Restoring Freedom', 'Scottsboro',
         'Nine Black teenagers are sentenced to death in Alabama on a fabricated charge. The International Labor
          Defense — founded in 1925 around the mass-defense model, with chapters in every industrial city and
          monthly stipends to &ldquo;class-war prisoners&rdquo; — meets its test: marches on five continents, the
          mothers touring Europe, two landmark Supreme Court rulings, and the playbook every defense committee
          since has used.',
         '/storage/history/scottsboro-nine.jpg', 'The Scottsboro defendants with their guards, 1931.', false, 'mh-c-blue'],
    ]],
    ['1946', [
        ['History', 'The Civil Rights Congress',
         'The ILD&rsquo;s successor takes the model into the Cold War: Willie McGee, the Martinsville Seven, the
          Trenton Six — and in 1951, the <em>We Charge Genocide</em> petition to the United Nations, the first
          attempt to put America&rsquo;s political and racial prisoners before an international audience. Its
          leaders are themselves jailed under the Smith Act for the trouble.',
         '/storage/history/smith-act-trials.jpg', 'Smith Act defendants outside the federal courthouse, 1949.', false, 'mh-c-white'],
    ]],
    ['1968', [
        ['Advancing the Movement', 'Free Huey',
         'Building on the civil-rights movement&rsquo;s jail-no-bail discipline — fill the jails, publish the
          names, make the state hold what it has taken — the campaign for Huey P. Newton invents the modern
          political-prisoner campaign: buttons, benefit concerts, celebrity delegations, a birthday rally in the
          Oakland Auditorium. His manslaughter conviction is reversed in 1970. Every &ldquo;Free&rdquo; poster
          since is a descendant.',
         '/storage/prisoners/01KVF6QKJVC1CJ75K1TJ81CBK0.jpg', 'Huey P. Newton. NPPC case file.', true, 'mh-c-navy'],
    ]],
    ['1970', [
        ['Restoring Freedom', 'Free Angela',
         'Angela Davis — fired, hunted, jailed, and tried for capital crimes over guns registered in her name —
          becomes the most famous political prisoner on earth. Three hundred defense committees form on five
          continents. In June 1972 an all-white jury acquits her on every count, and the campaign converts itself
          into a permanent organization against racist and political repression.',
         '/storage/prisoners/angela-davis.jpg', 'Angela Davis. NPPC case file.', true, 'mh-c-blue'],
    ]],
    ['1971', [
        ['History', 'Attica changes everything',
         'The rebellion and the massacre put prisoners&rsquo; own voices at the center of the movement for the
          first time. The observers&rsquo; committee, the litigation that runs for three decades, and the annual
          remembrance politics that follow make one thing permanent: the people inside are the movement, not its
          object.',
         '/storage/history/attica.jpg', 'D Yard, Attica Correctional Facility, September 1971.', false, 'mh-c-sand'],
    ]],
    ['1973', [
        ['Transforming Systems', 'The Wounded Knee Legal Defense/Offense Committee',
         'After the 71-day occupation, the government files over 500 indictments against the American Indian
          Movement. WKLDOC — the largest volunteer defense operation since Scottsboro — beats nearly all of them,
          exposing FBI misconduct so thoroughly that a federal judge dismisses the leadership cases outright.',
         '/storage/history/wounded-knee.jpg', 'AIM members during the Wounded Knee occupation, 1973.', false, 'mh-c-white'],
        ['Advancing the Movement', 'The Alliance',
         'The Angela Davis campaign refuses to demobilize. It becomes the National Alliance Against Racist and
          Political Repression — the first standing organization dedicated to <em>all</em> political prisoners at
          once, whatever their movement. The coalition idea this website runs on starts here.',
         null, null, false, 'mh-c-lav'],
    ]],
    ['1976', [
        ['Advancing the Movement', 'The longest campaign begins',
         'The Leonard Peltier Defense Committee forms after the Pine Ridge convictions. It will outlast eight
          presidents, four name changes, and every prediction of its irrelevance — forty-nine years of petitions,
          tribunals, congressional letters, and doorstep vigils, kept alive between headlines by volunteers.',
         '/storage/prisoners/leonard-peltier.jpg', 'Leonard Peltier. NPPC case file.', false, 'mh-c-white'],
    ]],
    ['1979', [
        ['Restoring Freedom', 'The Nationalists come home',
         'After a decade of campaigning across the Americas, President Carter commutes the sentences of Lolita
          Lebr&oacute;n and the Puerto Rican Nationalist prisoners — some held more than 25 years. Proof, filed
          and remembered, that even the longest entries can close.',
         null, null, true, 'mh-c-blue'],
    ]],
    ['1995', [
        ['Advancing the Movement', 'Live from Death Row',
         'Mumia Abu-Jamal&rsquo;s book — written on Pennsylvania&rsquo;s death row — turns his case into the
          era&rsquo;s defining political-prisoner campaign. Millions march; a 1995 death warrant is stayed under
          global pressure; and a generation of organizers gets its education in the mechanics of a defense
          committee.',
         '/storage/prisoners/mumia-abu-jamal.png', 'Mumia Abu-Jamal. NPPC case file.', false, 'mh-c-navy'],
    ]],
    ['1997', [
        ['Restoring Freedom', 'Geronimo ji-Jaga Pratt walks free',
         'Twenty-seven years after being framed for a murder the FBI knew he did not commit, the Black Panther
          leader&rsquo;s conviction is vacated. The campaign that never quit — church basements, prison visits,
          one stubborn lawyer named Johnnie Cochran — becomes the movement&rsquo;s canonical proof of concept.',
         '/storage/prisoners/geronimo-pratt.jpg', 'Geronimo ji-Jaga Pratt after release, 1997. NPPC case file.', true, 'mh-c-blue'],
    ]],
    ['1998', [
        ['Advancing the Movement', 'Jericho &rsquo;98',
         'Thousands march on the White House behind one demand: <em>amnesty for U.S. political prisoners</em> —
          and a definitive list of who they are. The Jericho Movement that forms around the march is the
          census&rsquo;s most direct ancestor: the insistence that the names be gathered in one place, kept
          current, and never allowed to disappear.',
         null, null, false, 'mh-c-sand'],
    ]],
    ['1999', [
        ['Restoring Freedom', 'Clemency for the independentistas',
         'President Clinton grants clemency to eleven Puerto Rican prisoners after a decade-long campaign spanning
          churches, unions, Nobel laureates, and a hundred thousand letters. A second generation watches a
          long-shot campaign end with people walking out of prison.',
         null, null, true, 'mh-c-white'],
    ]],
    ['2011', [
        ['Transforming Systems', 'Pelican Bay',
         'Hunger strikes against indefinite solitary confinement spread from one SHU pod to 30,000 California
          prisoners at their peak — coordinated between rival organizations, sustained by outside families and
          faith networks, and ultimately forcing the state to settle. Prisoner-led, outside-supported: the Attica
          lesson, applied at scale.',
         '/storage/petitions/end-bop-communications-management-units.jpg', 'Control-unit architecture, from the census archive.', false, 'mh-c-lav'],
    ]],
    ['2016', [
        ['Transforming Systems', 'Standing Rock&rsquo;s legal collective',
         'The water protector camps build their defense infrastructure <em>before</em> the mass arrests come:
          on-site legal observers, a jail-support hotline, a defense fund, and a database of all 800+ cases that
          volunteers keep until the last one resolves — six years later.',
         '/storage/history/standing-rock.jpg', '#NoDAPL demonstration. Fibonacci Blue, CC BY 2.0.', false, 'mh-c-white'],
    ]],
    ['2017', [
        ['Restoring Freedom', 'Chelsea Manning&rsquo;s sentence commuted',
         'Seven years of global campaigning — vigils outside Fort Leavenworth, a million-signature petition, and a
          hunger strike that forced concessions from the Army itself — end with a commutation in the final days of
          the Obama administration.',
         '/storage/prisoners/chelsea-manning.jpg', 'Chelsea Manning. NPPC case file.', true, 'mh-c-blue'],
    ]],
    ['2020', [
        ['History', 'The uprising&rsquo;s mass defense',
         'The George Floyd uprising produces more protest prosecutions than any year since 1968 — and the largest
          defense mobilization ever: bail funds absorb over $90 million in small donations, legal hotlines run
          around the clock, and volunteers track thousands of cases in shared spreadsheets. The movement rediscovers,
          at national scale, that the first list is the one that protects everyone on it.',
         null, null, false, 'mh-c-navy'],
    ]],
    ['2023', [
        ['The Record', 'The coalition convenes',
         'Defense committees, families, archivists, and the Jericho generation pool their lists into one: the
          National Political Prisoner Coalition opens the census with 6,104 documented cases, from the Haymarket
          defendants to the Stop Cop City RICO docket unfolding as it launches. A century of freedom work finally
          gets its permanent record.',
         '/storage/history/stop-cop-city.jpg', 'Stop Cop City vigil, January 2023. Tatsoi, CC BY-SA 4.0.', false, 'mh-c-blue'],
    ]],
    ['2024', [
        ['Restoring Freedom', 'Assange is free',
         'A plea deal ends the fourteen-year pursuit of the WikiLeaks founder — and a campaign that ran from
          London balconies to parliamentary delegations to a unanimous Australian caucus. The Espionage Act
          precedent stays unresolved; the man goes home.',
         '/storage/prisoners/julian-assange.jpg', 'Julian Assange. NPPC case file.', true, 'mh-c-white'],
    ]],
    ['2025', [
        ['Restoring Freedom', 'Forty-nine years: Peltier comes home',
         'The campaign founded in 1976 outlives the sentence. Commuted on January 19 and home at Turtle Mountain
          by February, Leonard Peltier closes the longest-running entry in the census — and the longest continuous
          defense campaign in American history closes its office with him.',
         '/storage/history/dakota-war-trials.jpg', 'The oldest entries in the record reach back to 1862.', true, 'mh-c-sand'],
    ]],
    ['2026', [
        ['The Record', 'The mass docket meets the mass defense',
         'Deployments, designations, and the largest protest dockets in a generation — met by rapid-response
          intake, court-watch corps, and a census that now documents arrests in near-real time. The Broadview Six
          walk free with prejudice. The record holds, because a century of this work taught it how.
          <a href="/report-2026">Read the FY26 report &rarr;</a>',
         null, null, false, 'mh-c-navy'],
    ]],
];
$heroBricks = [
    '/storage/history/scottsboro-nine.jpg', '/storage/prisoners/angela-davis.jpg',
    '/storage/history/wounded-knee.jpg', '/storage/prisoners/01KVF6QKJVC1CJ75K1TJ81CBK0.jpg',
    '/storage/history/attica.jpg', '/storage/prisoners/chelsea-manning.jpg',
    '/storage/history/standing-rock.jpg', '/storage/prisoners/leonard-peltier.jpg',
    '/storage/history/stop-cop-city.jpg',
];
@endphp

<div class="mh">

    <div class="mh-nav">
        <div class="mh-nav-in">
            <b>A History of Freedom Work</b>
            <a href="/history">Eras of repression &rarr;</a>
        </div>
    </div>

    {{-- HERO --}}
    <section class="mh-hero">
        <div>
            <h2 class="mh-hero-sub">Chronicling a<br>Century of</h2>
            <h1 class="mh-hero-title">
                <span><span class="mh-rotator" id="mh-rotator"><b class="is-visible">Freedom</b><b>Justice</b><b>Solidarity</b><b>Righteous</b></span>&nbsp;</span><br>
                <span>Work&nbsp;</span>
            </h1>
            <button type="button" class="mh-chev" id="mh-chev" aria-label="Scroll to the timeline"></button>
        </div>
        <div class="mh-masonry" aria-hidden="true">
            @foreach ($heroBricks as $i => $b)
                <div class="mh-brick f{{ ($i % 4) + 1 }}"><img src="{{ $b }}" alt="" loading="{{ $i < 3 ? 'eager' : 'lazy' }}"></div>
            @endforeach
        </div>
    </section>

    {{-- INTRO + SEAL LEGEND --}}
    <section class="mh-intro">
        <h2>Before the census there were the committees: the volunteers who raised the bail, kept the lists, wrote
        the letters, and refused to let a single name disappear. This is their century, year by year — the history
        of political prisoner activism in the United States.</h2>
        <div class="mh-legend">
            <div class="mh-seal">{!! $seal !!}</div>
            <p>This seal marks the campaigns that brought someone home.</p>
        </div>
    </section>

    {{-- TIMELINE --}}
    <section class="mh-tl" id="timeline">
        <nav class="mh-side" aria-label="Timeline years">
            <ul id="mh-side-list">
                @foreach ($timeline as $i => [$year, $blocks])
                    <li class="{{ $i === 0 ? 'on' : '' }}" data-year="{{ $year }}"><a href="#year-{{ $year }}">{{ $year }}</a></li>
                @endforeach
            </ul>
        </nav>
        <div class="mh-main">
            @foreach ($timeline as [$year, $blocks])
                <h2 class="mh-year" id="year-{{ $year }}" data-year="{{ $year }}">{{ $year }}</h2>
                @foreach ($blocks as [$cat, $title, $text, $img, $cap, $sealHere, $pal])
                    <div class="mh-block">
                        <div class="mh-block-in {{ $pal }}">
                            <div class="mh-accent"></div>
                            <div class="mh-block-body{{ $img ? '' : ' noimg' }}">
                                <div>
                                    <div class="mh-eyebrow"><span class="mh-eyebrow-dot"></span><span class="mh-eyebrow-h">{{ $cat }}</span></div>
                                    <h3 class="mh-h">{!! $title !!}</h3>
                                    <p class="mh-sub reveal">{!! $text !!}</p>
                                    @if ($sealHere)
                                        <div class="mh-seal reveal">{!! $seal !!}</div>
                                    @endif
                                </div>
                                @if ($img)
                                    <div class="mh-img reveal">
                                        <img src="{{ $img }}" alt="" loading="lazy">
                                        @if ($cap)<div class="mh-desc">{{ $cap }}</div>@endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </section>

    {{-- END CAP --}}
    <section class="mh-end">
        <h2>The record continues</h2>
        <p>Every campaign on this page left behind lists, files, and lessons. The census is where they live now —
        7,391 documented cases and growing, free to read forever.</p>
        <div>
            <a class="mh-btn" href="/database">Explore the Database</a>
            <a class="mh-btn ghost" href="/report-2026">The FY26 Report</a>
            <a class="mh-btn ghost" href="/history">Eras of Repression</a>
        </div>
    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // reveal
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
    }, { threshold: 0.15 });
    document.querySelectorAll('.mh .reveal').forEach(function (el) { io.observe(el); });

    // animated headline: Freedom -> Justice -> Solidarity -> Righteous -> ...
    (function () {
        var rot = document.getElementById('mh-rotator');
        if (!rot) return;
        var words = rot.querySelectorAll('b');
        var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var i = 0;
        function fit() {
            var w = words[i].getBoundingClientRect().width;
            if (w > 0) rot.style.width = Math.ceil(w) + 6 + 'px';
        }
        function step() {
            var prev = words[i];
            i = (i + 1) % words.length;
            var next = words[i];
            prev.classList.remove('is-visible');
            prev.classList.add('is-hidden');
            next.classList.remove('is-hidden');
            next.classList.add('is-visible');
            fit();
            setTimeout(function () { prev.classList.remove('is-hidden'); }, 600);
        }
        fit();
        window.addEventListener('resize', fit);
        if (document.fonts && document.fonts.ready) document.fonts.ready.then(fit);
        if (!reduced) setInterval(step, 2800);
    })();

    // chevron
    var chev = document.getElementById('mh-chev');
    if (chev) chev.addEventListener('click', function () {
        document.getElementById('timeline').scrollIntoView({ behavior: 'smooth' });
    });

    // sidebar scrollspy
    var items = {};
    document.querySelectorAll('#mh-side-list li').forEach(function (li) { items[li.getAttribute('data-year')] = li; });
    var spy = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (!e.isIntersecting) return;
            var y = e.target.getAttribute('data-year');
            Object.keys(items).forEach(function (k) { items[k].classList.toggle('on', k === y); });
            // Keep the active year visible by scrolling ONLY the sidebar list.
            // (li.scrollIntoView also scrolls the document and fights the
            // user's page scrolling.)
            var li = items[y];
            if (li) {
                var ul = li.parentElement;
                if (ul.scrollHeight > ul.clientHeight) {
                    // li's offsetParent is the (positioned, sticky) UL, so
                    // offsetTop is already list-relative.
                    var top = li.offsetTop;
                    if (top < ul.scrollTop + 24) ul.scrollTop = Math.max(0, top - 24);
                    else if (top > ul.scrollTop + ul.clientHeight - 48) ul.scrollTop = top - ul.clientHeight + 48;
                }
            }
        });
    }, { rootMargin: '-15% 0px -70% 0px' });
    document.querySelectorAll('.mh-year').forEach(function (el) { spy.observe(el); });
});
</script>
@endsection
