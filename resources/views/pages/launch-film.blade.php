@extends('app')

@section('title', 'Always. — The NPPC Launch Film | NPPC')

@section('meta_description')The National Political Prisoner Coalition launch film: sixty seconds on a century of American political imprisonment — and the database, tracker, archive, and memorial built to answer it.@endsection

@section('og_image'){{ asset('videos/nppc-launch-film-poster.jpg') }}@endsection

@section('head')
<meta property="og:video" content="{{ asset('videos/nppc-launch-film.mp4') }}">
<meta property="og:video:type" content="video/mp4">
<meta property="og:video:width" content="1920">
<meta property="og:video:height" content="1080">
<style>
    /* Full-bleed dark screening room. */
    body.page-launch-film main.container,
    body.page-launch-film .container { width: 100% !important; max-width: none !important; padding-left: 0 !important; padding-right: 0 !important; }
    body.page-launch-film { background: #060608; }

    .lf { background: #060608; color: rgba(255,255,255,0.85); padding: 96px 24px 110px; text-align: center; }
    .lf-kicker { display: inline-flex; align-items: center; gap: 10px; font-size: 12px; font-weight: 800; letter-spacing: 0.24em; text-transform: uppercase; color: #8f97ff; }
    .lf-kicker::before, .lf-kicker::after { content: ''; width: 30px; height: 2px; background: #5660fe; }
    .lf-title { font-size: clamp(2.4rem, 5.5vw, 4rem); font-weight: 800; color: var(--on-dark); margin: 18px 0 10px; letter-spacing: -0.02em; }
    .lf-sub { font-size: 1.1rem; color: rgba(255,255,255,0.6); max-width: 620px; margin: 0 auto 44px; line-height: 1.6; }
    .lf-player { max-width: 1180px; margin: 0 auto; border-radius: 12px; overflow: hidden; box-shadow: 0 40px 120px rgba(0,0,0,0.65), 0 0 0 1px rgba(255,255,255,0.08); background: #000; }
    .lf-player video { display: block; width: 100%; height: auto; }
    .lf-meta { margin-top: 26px; font-size: 13px; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255,255,255,0.4); }
    .lf-actions { margin-top: 34px; display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
    .lf-btn { display: inline-block; font-size: 15px; font-weight: 700; padding: 13px 28px; border-radius: 999px; text-decoration: none; transition: transform 0.15s, background 0.15s; }
    .lf-btn-solid { background: #5660fe; color: #fff; }
    .lf-btn-solid:hover { background: #3b45e0; color: #fff; transform: translateY(-1px); }
    .lf-btn-ghost { background: transparent; color: var(--on-dark); border: 1px solid rgba(255,255,255,0.4); }
    .lf-btn-ghost:hover { background: rgba(255,255,255,0.08); color: var(--on-dark); transform: translateY(-1px); }
</style>
@endsection

@section('body')
<div class="lf">
    <span class="lf-kicker">The launch film</span>
    <h1 class="lf-title">Always.</h1>
    <p class="lf-sub">Sixty seconds on a century of American political imprisonment — and the database, tracker, archive, and memorial built to answer it.</p>
    <div class="lf-player">
        <video controls preload="metadata" playsinline poster="{{ asset('videos/nppc-launch-film-poster.jpg') }}">
            <source src="{{ asset('videos/nppc-launch-film.mp4') }}" type="video/mp4">
        </video>
    </div>
    <p class="lf-meta">60 seconds · archival photographs, public domain &amp; CC · original score</p>
    <div class="lf-actions">
        <a class="lf-btn lf-btn-solid" href="/database">Explore the database</a>
        <a class="lf-btn lf-btn-ghost" href="{{ asset('videos/nppc-launch-film.mp4') }}" download>Download the film</a>
    </div>
</div>
@endsection
