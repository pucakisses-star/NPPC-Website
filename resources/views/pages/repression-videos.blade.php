@extends('app')

@section('title', 'Videos — Repression in America — National Political Prisoner Coalition')

@section('head')
    <meta name="description" content="Films and footage from the NPPC: the launch film, the museum theater, and the podcast — the moving-image companion to Repression in America.">
    @verbatim
    <style>
        /* Repression in America — Videos. Black documentary grid: serif
           intro line, two-column stills with circular play chips, bold
           title—description rows, underlined all-caps play links. Fixed
           palette in both themes; all assets are our own. */
        /* Verlag, the site's licensed face, in place of an Avenir stack that
           was never licensed here and fell back to Helvetica off Apple. */
        .rv { background: #000; color: #fff; font-family: Verlag A, Verlag B, Verlag, 'Helvetica Neue', Helvetica, Arial, sans-serif; min-height: 100vh; }
        .rv * { box-sizing: border-box; }
        .rv-wrap { max-width: 1200px; margin: 0 auto; padding: 0 32px; }
        .rv-caps { font-weight: 800; text-transform: uppercase; letter-spacing: .2em; }

        /* breadcrumb label row */
        .rv-crumb { padding: 26px 0 0; display: flex; align-items: center; gap: 14px; font-size: 11px; }
        .rv-crumb a { color: rgba(255,255,255,.85); text-decoration: none; }
        .rv-crumb a:hover { color: #fff; }
        .rv-crumb .sep { width: 1px; height: 12px; background: rgba(255,255,255,.35); }
        .rv-crumb .here { color: rgba(255,255,255,.5); }

        /* serif intro */
        .rv-intro { padding: 90px 0 70px; text-align: center; }
        .rv-intro p {
            margin: 0 auto; max-width: 780px; font-family: Georgia, 'Times New Roman', serif;
            font-size: clamp(21px, 2.8vw, 30px); line-height: 1.5;
        }
        .rv-intro p .dim { color: rgba(255,255,255,.45); }

        /* video grid */
        .rv-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 56px 40px; padding-bottom: 130px; }
        .rv-card { text-decoration: none; color: #fff; display: block; }
        .rv-still { display: block; position: relative; aspect-ratio: 16/9; overflow: hidden; background: #101014; }
        .rv-still img, .rv-still video { width: 100%; height: 100%; object-fit: cover; display: block; filter: grayscale(100%) contrast(1.05); transition: filter .25s; }
        .rv-card:hover .rv-still img { filter: grayscale(60%) contrast(1.05); }
        .rv-play {
            position: absolute; right: 16px; bottom: 16px; width: 42px; height: 42px; border-radius: 50%;
            background: #fff; color: #000; display: flex; align-items: center; justify-content: center;
            font-size: 13px; transition: transform .18s;
        }
        .rv-card:hover .rv-play { transform: scale(1.12); }
        .rv-card .desc { display: block; margin: 18px 0 14px; font-size: 14px; line-height: 1.7; color: rgba(255,255,255,.72); max-width: 520px; }
        .rv-card .desc strong { color: #fff; font-weight: 800; }
        .rv-card .go { display: inline-block; font-size: 11.5px; font-weight: 800; letter-spacing: .2em; text-transform: uppercase; color: #fff; padding-bottom: 6px; border-bottom: 1px solid rgba(255,255,255,.5); }
        .rv-card:hover .go { border-color: #fff; }
        @media (max-width: 820px) { .rv-grid { grid-template-columns: 1fr; } }

        /* inline player state */
        .rv-modal { position: fixed; inset: 0; z-index: 500001; background: rgba(0,0,0,.94); display: flex; align-items: center; justify-content: center; padding: 4vmin; }
        .rv-modal.hide { display: none; }
        .rv-modal video { max-width: 100%; max-height: 88vh; box-shadow: 0 30px 90px rgba(0,0,0,.7); }
        .rv-close { position: absolute; top: 22px; right: 26px; background: none; border: 1px solid rgba(255,255,255,.5); color: #fff; border-radius: 999px; padding: 9px 20px; font-family: inherit; font-size: 12px; letter-spacing: .16em; text-transform: uppercase; cursor: pointer; }
        .rv-close:hover { background: #fff; color: #000; }
    </style>
    @endverbatim
@endsection

@section('body')
<div class="rv">

    <div class="rv-wrap">
        <div class="rv-crumb rv-caps">
            <a href="/">NPPC</a>
            <span class="sep"></span>
            <a href="/repression-in-america">Repression in America</a>
            <span class="sep"></span>
            <span class="here">Videos</span>
        </div>
    </div>

    <section class="rv-intro">
        <div class="rv-wrap">
            <p><span class="dim">Moving images from the movement —</span> the coalition's launch film, the museum's theater, and the voices of the people this site documents.</p>
        </div>
    </section>

    <section class="rv-wrap rv-grid">

        <a class="rv-card" href="#" id="rv-launch">
            <span class="rv-still">
                <img src="/videos/nppc-launch-film-poster.jpg" alt="Still from the NPPC launch film">
                <span class="rv-play">▶</span>
            </span>
            <span class="desc"><strong>The Launch Film</strong> — The National Political Prisoner Coalition, in its own words: why we document seven thousand political prisoners, and what we owe the ones still inside. Plays right here.</span>
            <span class="go">Play the video</span>
        </a>

        <a class="rv-card" href="/museum">
            <span class="rv-still">
                <img src="/images/topics-eras.jpg" alt="">
                <span class="rv-play">▶</span>
            </span>
            <span class="desc"><strong>The Museum Theater</strong> — A screening room inside our walkable 3D museum, running an era-by-era program built live from the database. Take a seat in the back row.</span>
            <span class="go">Enter the theater</span>
        </a>

        <a class="rv-card" href="/podcast">
            <span class="rv-still">
                <img src="/images/imam-jamil-al-amin/portrait-mics.jpg" alt="" style="object-position: center 30%;">
                <span class="rv-play">▶</span>
            </span>
            <span class="desc"><strong>The Podcast</strong> — Case histories told episode by episode: the trials, the campaigns, the people. The audio companion to everything on this site.</span>
            <span class="go">Listen now</span>
        </a>

        <a class="rv-card" href="/launch-film">
            <span class="rv-still">
                <img src="/images/articles/iww-deportation-1917.jpg" alt="">
                <span class="rv-play">▶</span>
            </span>
            <span class="desc"><strong>About the Film</strong> — Notes on the launch film: the archives it draws from, the cases it follows, and the people who made it.</span>
            <span class="go">Read the notes</span>
        </a>

    </section>

    <div class="rv-modal hide" id="rv-modal" role="dialog" aria-label="Video player">
        <button class="rv-close" id="rv-close">Close</button>
        <video id="rv-video" controls preload="none" poster="/videos/nppc-launch-film-poster.jpg">
            <source src="/videos/nppc-launch-film.mp4" type="video/mp4">
        </video>
    </div>

</div>

@verbatim
<script>
(function () {
    var modal = document.getElementById('rv-modal');
    var video = document.getElementById('rv-video');
    var open = document.getElementById('rv-launch');
    if (open) open.addEventListener('click', function (e) {
        e.preventDefault();
        modal.classList.remove('hide');
        video.play().catch(function () {});
    });
    function close() { modal.classList.add('hide'); video.pause(); }
    document.getElementById('rv-close').addEventListener('click', close);
    modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();
</script>
@endverbatim
@endsection
