@extends('app')

@section('title', 'The Museum — National Political Prisoner Coalition')

@section('head')
    <meta name="description" content="A walkable 3D museum of American political imprisonment — galleries, a timeline hall, an archive room, a theater, and a full-scale solitary cell, built from the NPPC database.">
    @verbatim
    <style>
        #museum-wrap { position: fixed; inset: 0; z-index: 500000; background: #0c0d10; }
        #museum-canvas { width: 100%; height: 100%; display: block; }
        #museum-wrap, #museum-wrap * { box-sizing: border-box; font-family: Georgia, 'Times New Roman', serif; }
        .hide { display: none !important; }

        /* splash + pause */
        .mu-cover {
            position: absolute; inset: 0; z-index: 10; display: flex; flex-direction: column;
            align-items: center; justify-content: center; text-align: center; color: #f4f1ea;
            background: radial-gradient(120% 100% at 50% 20%, rgba(18,19,24,.86) 0%, rgba(9,10,13,.96) 100%);
            padding: 24px;
        }
        .mu-eyebrow { font-size: 13px; letter-spacing: .22em; color: #e4a524; margin: 0 0 18px; }
        .mu-title { font-size: clamp(30px, 5vw, 56px); line-height: 1.05; margin: 0 0 14px; font-weight: 700; }
        .mu-sub { max-width: 560px; font-size: 16px; line-height: 1.55; color: rgba(244,241,234,.75); margin: 0 0 30px; }
        .mu-btn {
            background: #98002e; color: #fff; border: 2px solid transparent; cursor: pointer;
            font-size: 18px; font-weight: 600; padding: 15px 42px; border-radius: 999px;
            transition: background .2s, border-color .2s, color .2s; font-family: inherit;
        }
        .mu-btn:hover { background: transparent; border-color: #98002e; color: #ffd7e2; }
        .mu-keys { display: flex; gap: 26px; margin-top: 34px; font-size: 13px; color: rgba(244,241,234,.6); flex-wrap: wrap; justify-content: center; }
        .mu-keys b { display: inline-block; border: 1px solid rgba(244,241,234,.35); border-radius: 6px; padding: 3px 9px; margin-right: 7px; font-weight: 600; color: #f4f1ea; }
        .mu-leave { margin-top: 20px; color: rgba(244,241,234,.55); font-size: 14px; text-decoration: underline; text-underline-offset: 3px; }
        .mu-leave:hover { color: #fff; }

        /* HUD */
        #museum-hud { position: absolute; inset: 0; z-index: 5; pointer-events: none; }
        #museum-reticle {
            position: absolute; top: 50%; left: 50%; width: 7px; height: 7px; border-radius: 50%;
            transform: translate(-50%, -50%); background: rgba(255,255,255,.85);
            box-shadow: 0 0 6px rgba(0,0,0,.6); transition: width .12s, height .12s, background .12s;
        }
        #museum-reticle.on { width: 15px; height: 15px; background: rgba(228,165,36,.95); }
        #museum-hint {
            position: absolute; top: calc(50% + 26px); left: 50%; transform: translateX(-50%);
            color: #f4f1ea; font-size: 14px; text-shadow: 0 1px 4px rgba(0,0,0,.8); letter-spacing: .04em;
        }
        #museum-toast {
            position: absolute; bottom: 42px; left: 50%; transform: translateX(-50%) translateY(14px);
            color: #f4f1ea; font-size: 22px; font-weight: 600; letter-spacing: .06em;
            text-shadow: 0 2px 12px rgba(0,0,0,.9); opacity: 0; transition: opacity .5s, transform .5s;
        }
        #museum-toast.on { opacity: 1; transform: translateX(-50%) translateY(0); }
        #museum-touch-note { position: absolute; bottom: 14px; width: 100%; text-align: center; color: rgba(244,241,234,.55); font-size: 12px; }

        /* inspect overlay */
        #museum-inspect {
            position: absolute; inset: 0; z-index: 20; background: rgba(8,9,12,.88);
            backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center; padding: 4vmin;
        }
        .mi-card { display: flex; gap: 42px; max-width: 1150px; max-height: 88vh; align-items: center; }
        #mi-img {
            max-width: 46vw; max-height: 82vh; object-fit: contain; background: #17181c;
            border: 10px solid #26221c; outline: 1px solid rgba(255,255,255,.14);
            box-shadow: 0 30px 90px rgba(0,0,0,.65);
        }
        .mi-info { color: #f4f1ea; max-width: 460px; overflow-y: auto; max-height: 82vh; padding-right: 6px; }
        #mi-eyebrow { color: #e4a524; font-size: 13px; letter-spacing: .2em; text-transform: uppercase; margin: 0 0 12px; }
        #mi-title { font-size: clamp(26px, 3.4vw, 42px); line-height: 1.08; margin: 0 0 10px; font-weight: 700; }
        #mi-meta { color: rgba(244,241,234,.65); font-style: italic; margin: 0 0 18px; font-size: 16px; }
        #mi-desc { font-size: 16.5px; line-height: 1.62; color: rgba(244,241,234,.88); margin: 0 0 26px; white-space: pre-line; }
        #mi-link {
            display: inline-block; background: #98002e; color: #fff; text-decoration: none;
            padding: 12px 26px; border-radius: 999px; font-weight: 600; font-size: 15px; margin-right: 14px;
        }
        #mi-link:hover { background: #b31840; }
        #mi-close, #mi-read {
            background: none; border: 1px solid rgba(244,241,234,.4); color: #f4f1ea; cursor: pointer;
            padding: 12px 26px; border-radius: 999px; font-size: 15px; font-family: inherit; margin-right: 12px;
        }
        #mi-close:hover, #mi-read:hover { border-color: #fff; }
        #mi-read { border-color: #e4a524; color: #ffe6b0; }
        @media (max-width: 860px) {
            .mi-card { flex-direction: column; gap: 18px; overflow-y: auto; }
            #mi-img { max-width: 86vw; max-height: 44vh; }
            .mi-info { max-width: 86vw; max-height: none; }
        }
        #museum-fallback { color: #f4f1ea; }
        #museum-fallback a { color: #e4a524; }

        /* reading-room PDF reader */
        #museum-reader {
            position: absolute; inset: 0; z-index: 22; background: rgba(8,9,12,.93);
            display: flex; flex-direction: column; padding: 3vmin 4vmin;
        }
        .mr-bar { display: flex; align-items: baseline; gap: 18px; color: #f4f1ea; margin-bottom: 14px; flex-wrap: wrap; }
        #mr-title { font-size: clamp(18px, 2.4vw, 28px); font-weight: 700; margin: 0; }
        #mr-meta { color: rgba(244,241,234,.6); font-style: italic; font-size: 14px; }
        .mr-actions { margin-left: auto; display: flex; gap: 10px; }
        .mr-actions a, .mr-actions button {
            font-family: inherit; font-size: 13.5px; cursor: pointer; text-decoration: none;
            padding: 9px 18px; border-radius: 999px; border: 1px solid rgba(244,241,234,.4);
            background: none; color: #f4f1ea;
        }
        .mr-actions a:hover, .mr-actions button:hover { border-color: #fff; }
        .mr-actions .primary { background: #98002e; border-color: transparent; }
        .mr-actions .primary:hover { background: #b31840; }
        #mr-frame {
            flex: 1; width: 100%; border: 0; border-radius: 6px; background: #26221c;
            box-shadow: 0 30px 90px rgba(0,0,0,.6);
        }
        #mr-note { color: rgba(244,241,234,.55); font-size: 12.5px; margin-top: 10px; text-align: center; }
    </style>
    @endverbatim
@endsection

@section('body')
    <div id="museum-wrap">
        <canvas id="museum-canvas"></canvas>

        <div id="museum-hud" class="hide">
            <div id="museum-reticle"></div>
            <div id="museum-hint"></div>
            <div id="museum-toast"></div>
            <div id="museum-touch-note" class="hide">Left thumb: move &nbsp;·&nbsp; right thumb: look &nbsp;·&nbsp; tap: inspect</div>
        </div>

        <div id="museum-splash" class="mu-cover">
            <p class="mu-eyebrow">NATIONAL POLITICAL PRISONER COALITION</p>
            <h1 class="mu-title">The Museum of<br>Political Imprisonment</h1>
            <p class="mu-sub">A walkable gallery built live from the NPPC database — six themed halls, a timeline corridor, an archive of original documents, a theater, and a full-scale replica of a solitary cell.</p>
            <button class="mu-btn" id="museum-enter">Enter the museum</button>
            <div class="mu-keys">
                <span><b>W A S D</b> walk</span>
                <span><b>Mouse</b> look</span>
                <span><b>Shift</b> run</span>
                <span><b>Click / E</b> inspect</span>
                <span><b>Esc</b> pause</span>
            </div>
            <a class="mu-leave" href="/">← Back to the site</a>
        </div>

        <div id="museum-pause" class="mu-cover hide">
            <p class="mu-eyebrow">PAUSED</p>
            <h1 class="mu-title" style="font-size:clamp(26px,4vw,44px)">The museum will wait.</h1>
            <button class="mu-btn" id="museum-resume">Resume</button>
            <a class="mu-leave" href="/">← Leave the museum</a>
        </div>

        <div id="museum-reader" class="hide">
            <div class="mr-bar">
                <h2 id="mr-title"></h2>
                <span id="mr-meta"></span>
                <span class="mr-actions">
                    <a id="mr-record" href="#">Archive record</a>
                    <a id="mr-open" href="#" target="_blank" rel="noopener" class="primary">Open in new tab</a>
                    <button id="mr-close">Put the book back</button>
                </span>
            </div>
            <iframe id="mr-frame" title="Document reader"></iframe>
            <div id="mr-note">Reading the digitized scan from the NPPC archive. If pages don't render on your device, use “Open in new tab.”</div>
        </div>

        <div id="museum-inspect" class="hide">
            <div class="mi-card">
                <img id="mi-img" alt="">
                <div class="mi-info">
                    <p id="mi-eyebrow"></p>
                    <h2 id="mi-title"></h2>
                    <p id="mi-meta"></p>
                    <p id="mi-desc"></p>
                    <a id="mi-link" href="#">Open the full record →</a>
                    <button id="mi-read" class="hide">Read the scan</button>
                    <button id="mi-close">Back to the gallery</button>
                </div>
            </div>
        </div>
    </div>

    <script>window.MUSEUM = @json($museum);</script>
    <script type="importmap">
    {
        "imports": {
            "three": "/js/three/three.module.min.js",
            "three/addons/": "/js/three/addons/"
        }
    }
    </script>
    <script>
        (function () {
            try {
                var c = document.createElement('canvas');
                if (!(window.WebGL2RenderingContext && c.getContext('webgl2'))) throw 0;
            } catch (e) {
                var s = document.getElementById('museum-splash');
                s.innerHTML = '<div id="museum-fallback"><h1 class="mu-title">This museum needs WebGL2.</h1>'
                    + '<p class="mu-sub">Your browser or device can\'t render the 3D galleries. You can still explore everything the museum contains:</p>'
                    + '<p><a href="/topics">Topics</a> · <a href="/timeline">Timeline</a> · <a href="/archive">Archive</a> · <a href="/memorial">Memorial</a></p></div>';
            }
        })();
    </script>
    <script type="module" src="/js/museum.js"></script>
@endsection
