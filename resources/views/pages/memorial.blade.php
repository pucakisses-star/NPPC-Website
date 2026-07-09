@extends('app')

@section('title', 'Memorial — One Star for Every Political Prisoner')

@section('head')
<style>
    /* Full-bleed dark memorial, modeled in spirit on gazaschildren.com. */
    body.page-memorial main.container, body.page-memorial .container { max-width: none !important; padding-left: 0 !important; padding-right: 0 !important; overflow: visible !important; }
    body.page-memorial { background: #050408; }

    .mem { position: relative; width: 100vw; margin-left: calc(50% - 50vw); height: calc(100vh - 108px); min-height: 560px; background: radial-gradient(120% 90% at 50% 0%, #0b0a12 0%, #050408 60%); overflow: hidden; color: #f2eee4; }
    .mem-canvas { position: absolute; inset: 0; width: 100%; height: 100%; display: block; }
    .mem-vignette { position: absolute; inset: 0; pointer-events: none; background: radial-gradient(120% 100% at 50% 40%, rgba(0,0,0,0) 55%, rgba(0,0,0,0.55) 100%); }

    .mem-head { position: absolute; top: clamp(20px, 5vh, 54px); left: 0; right: 0; text-align: center; padding: 0 20px; pointer-events: none; z-index: 3; }
    .mem-eyebrow { font-size: 12px; font-weight: 700; letter-spacing: 0.22em; text-transform: uppercase; color: #8a8377; margin-bottom: 12px; }
    .mem-title { font-size: clamp(24px, 4vw, 44px); font-weight: 700; letter-spacing: -0.01em; color: #f7e4bf; margin: 0 0 12px; text-shadow: 0 2px 30px rgba(247,228,191,0.15); }
    .mem-sub { font-size: 15px; line-height: 1.6; color: #d6d0c3; max-width: 620px; margin: 0 auto; }
    .mem-sub b { color: #f7e4bf; font-weight: 700; }

    .mem-search { pointer-events: auto; max-width: 340px; margin: 22px auto 0; position: relative; }
    .mem-search input { width: 100%; background: rgba(247,228,191,0.06); border: 1px solid rgba(247,228,191,0.22); border-radius: 999px; color: #f2eee4; font: inherit; font-size: 14px; padding: 11px 18px; outline: none; text-align: center; transition: border-color 0.2s, background 0.2s; }
    .mem-search input::placeholder { color: #8a8377; }
    .mem-search input:focus { border-color: rgba(247,228,191,0.6); background: rgba(247,228,191,0.1); }
    .mem-search-results { position: absolute; left: 0; right: 0; top: calc(100% + 8px); background: #0b0a12; border: 1px solid rgba(247,228,191,0.18); border-radius: 12px; overflow: hidden; text-align: left; }
    .mem-search-results:empty { display: none; }
    .mem-search-results button { display: block; width: 100%; text-align: left; background: none; border: 0; color: #d6d0c3; font: inherit; font-size: 14px; padding: 9px 16px; cursor: pointer; }
    .mem-search-results button:hover { background: rgba(247,228,191,0.1); color: #f7e4bf; }

    .mem-legend { position: absolute; left: clamp(16px, 3vw, 34px); bottom: clamp(16px, 3vh, 30px); display: flex; flex-direction: column; gap: 8px; font-size: 12px; color: #8a8377; z-index: 3; pointer-events: none; }
    .mem-legend span { display: flex; align-items: center; gap: 9px; }
    .mem-dot { width: 9px; height: 9px; border-radius: 50%; flex: 0 0 auto; }
    .mem-dot--custody { background: #f7e4bf; box-shadow: 0 0 10px 2px rgba(247,228,191,0.55); }
    .mem-dot--died { background: #fff8e6; box-shadow: 0 0 12px 3px rgba(255,248,230,0.7); }
    .mem-dot--released { background: #cbbc9e; opacity: 0.7; }

    .mem-tooltip { position: fixed; z-index: 5; pointer-events: none; transform: translate(-50%, -140%); background: rgba(11,10,18,0.92); border: 1px solid rgba(247,228,191,0.25); color: #f7e4bf; font-size: 13px; font-weight: 600; padding: 5px 11px; border-radius: 8px; white-space: nowrap; }
    .mem-tooltip[hidden] { display: none; }

    .mem-focus { position: absolute; left: 50%; bottom: clamp(20px, 5vh, 44px); transform: translateX(-50%); z-index: 4; width: min(440px, calc(100vw - 40px)); background: rgba(8,7,14,0.86); backdrop-filter: blur(10px); border: 1px solid rgba(247,228,191,0.22); border-radius: 16px; padding: 22px 24px 20px; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
    .mem-focus[hidden] { display: none; }
    .mem-focus__close { position: absolute; top: 10px; right: 12px; background: none; border: 0; color: #8a8377; font-size: 22px; line-height: 1; cursor: pointer; }
    .mem-focus__close:hover { color: #f2eee4; }
    .mem-focus__name { font-size: 21px; font-weight: 700; color: #f7e4bf; }
    .mem-focus__meta { font-size: 13px; color: #8a8377; margin-top: 5px; text-transform: uppercase; letter-spacing: 0.06em; }
    .mem-focus__actions { display: flex; align-items: center; justify-content: center; gap: 14px; margin-top: 18px; }
    .mem-focus__nav { background: rgba(247,228,191,0.08); border: 1px solid rgba(247,228,191,0.2); color: #f2eee4; width: 36px; height: 36px; border-radius: 50%; font-size: 18px; line-height: 1; cursor: pointer; transition: background 0.2s; }
    .mem-focus__nav:hover { background: rgba(247,228,191,0.18); }
    .mem-focus__link { flex: 1; background: #f7e4bf; color: #1a1508; font-weight: 700; font-size: 14px; text-decoration: none; padding: 10px 16px; border-radius: 999px; transition: background 0.2s; }
    .mem-focus__link:hover { background: #fff0cf; }

    @@media (max-width: 640px) {
        .mem { height: calc(100vh - 88px); }
        .mem-legend { flex-direction: row; flex-wrap: wrap; gap: 6px 16px; }
    }
</style>
@endsection

@section('body')
<div class="mem" id="mem">
    <canvas class="mem-canvas" id="mem-canvas"></canvas>
    <div class="mem-vignette"></div>

    <header class="mem-head">
        <div class="mem-eyebrow">In memory &amp; in solidarity</div>
        <h1 class="mem-title">One star for every political prisoner</h1>
        <p class="mem-sub"><b>{{ number_format($count) }}</b> names in the National Political Prisoner Coalition database. Move across the sky and touch a star to read a name.</p>
        <div class="mem-search">
            <input id="mem-search-input" type="text" placeholder="Find a name…" autocomplete="off" spellcheck="false">
            <div class="mem-search-results" id="mem-search-results"></div>
        </div>
    </header>

    <div class="mem-legend">
        <span><i class="mem-dot mem-dot--custody"></i> Still imprisoned</span>
        <span><i class="mem-dot mem-dot--died"></i> Deceased</span>
        <span><i class="mem-dot mem-dot--released"></i> Released</span>
    </div>

    <div class="mem-focus" id="mem-focus" hidden>
        <button class="mem-focus__close" id="mem-focus-close" aria-label="Close">&times;</button>
        <div class="mem-focus__name" id="mem-focus-name"></div>
        <div class="mem-focus__meta" id="mem-focus-meta"></div>
        <div class="mem-focus__actions">
            <button class="mem-focus__nav" id="mem-focus-prev" aria-label="Previous star">&lsaquo;</button>
            <a class="mem-focus__link" id="mem-focus-link" href="#">View profile &rarr;</a>
            <button class="mem-focus__nav" id="mem-focus-next" aria-label="Next star">&rsaquo;</button>
        </div>
    </div>

    <div class="mem-tooltip" id="mem-tooltip" hidden></div>
</div>

<script>window.__MEM = @json($people);</script>
<script>
(function () {
    var DATA = window.__MEM || [];
    var cv = document.getElementById('mem-canvas');
    var ctx = cv.getContext('2d');
    var tooltip = document.getElementById('mem-tooltip');
    var focusEl = document.getElementById('mem-focus');
    var nameEl = document.getElementById('mem-focus-name');
    var metaEl = document.getElementById('mem-focus-meta');
    var linkEl = document.getElementById('mem-focus-link');
    var searchInput = document.getElementById('mem-search-input');
    var searchResults = document.getElementById('mem-search-results');
    if (!cv || !ctx) return;

    var W = 0, H = 0, DPR = 1;
    var hover = -1, focus = -1;

    // Deterministic per-star positions (stable across resizes).
    function mulberry32(a) {
        return function () {
            a |= 0; a = a + 0x6D2B79F5 | 0;
            var t = Math.imul(a ^ a >>> 15, 1 | a);
            t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t;
            return ((t ^ t >>> 14) >>> 0) / 4294967296;
        };
    }

    var stars = DATA.map(function (p, i) {
        var rnd = mulberry32((i * 2654435761) >>> 0);
        var size = p.d ? 2.6 : (p.c ? 2.1 : 1.5);   // deceased largest, imprisoned mid, released small
        var base = p.d ? 1.0 : (p.c ? 0.9 : 0.62);
        return { p: p, nx: rnd(), ny: rnd(), size: size, base: base,
                 twp: rnd() * Math.PI * 2, tws: 0.5 + rnd() * 1.1, x: 0, y: 0 };
    });

    // Pre-rendered glow sprites (warm gold; brighter warm-white for the deceased).
    function makeSprite(core, edge) {
        var s = 64, c = document.createElement('canvas'); c.width = c.height = s;
        var g = c.getContext('2d');
        var grd = g.createRadialGradient(s / 2, s / 2, 0, s / 2, s / 2, s / 2);
        grd.addColorStop(0, core); grd.addColorStop(0.22, core); grd.addColorStop(1, edge);
        g.fillStyle = grd; g.beginPath(); g.arc(s / 2, s / 2, s / 2, 0, 7); g.fill();
        return c;
    }
    var spriteGold = makeSprite('rgba(247,228,191,1)', 'rgba(247,228,191,0)');
    var spriteWhite = makeSprite('rgba(255,248,230,1)', 'rgba(255,248,230,0)');

    function resize() {
        DPR = Math.min(2, window.devicePixelRatio || 1);
        var rect = cv.getBoundingClientRect();
        W = rect.width; H = rect.height;
        cv.width = Math.round(W * DPR); cv.height = Math.round(H * DPR);
        ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
        var m = Math.min(W, H) * 0.05;
        for (var i = 0; i < stars.length; i++) {
            stars[i].x = m + stars[i].nx * (W - 2 * m);
            stars[i].y = m + stars[i].ny * (H - 2 * m);
        }
    }

    var t0 = performance.now();
    function draw(now) {
        var t = (now - t0) / 1000;
        ctx.clearRect(0, 0, W, H);
        ctx.globalCompositeOperation = 'lighter';
        for (var i = 0; i < stars.length; i++) {
            var st = stars[i];
            var tw = 0.72 + 0.28 * Math.sin(t * st.tws + st.twp);
            var big = (focus === i) ? 2.4 : (hover === i ? 1.8 : 1);
            var px = st.size * 7 * big;
            var a = Math.min(1, st.base * tw * ((focus === i || hover === i) ? 1.35 : 1));
            ctx.globalAlpha = a;
            ctx.drawImage(st.p.d ? spriteWhite : spriteGold, st.x - px / 2, st.y - px / 2, px, px);
        }
        ctx.globalCompositeOperation = 'source-over';
        ctx.globalAlpha = 1;
        if (focus >= 0 && stars[focus]) {
            var f = stars[focus];
            var r = 13 + 2.5 * Math.sin(t * 2.6);
            ctx.beginPath(); ctx.arc(f.x, f.y, r, 0, 7);
            ctx.strokeStyle = 'rgba(247,228,191,' + (0.45 + 0.25 * Math.sin(t * 2.6)) + ')';
            ctx.lineWidth = 1.2; ctx.stroke();
        }
        requestAnimationFrame(draw);
    }

    function nearest(mx, my, maxPx) {
        var best = -1, bd = maxPx * maxPx;
        for (var i = 0; i < stars.length; i++) {
            var dx = stars[i].x - mx, dy = stars[i].y - my, d = dx * dx + dy * dy;
            if (d < bd) { bd = d; best = i; }
        }
        return best;
    }

    function statusText(p) { return p.c ? 'Currently imprisoned' : (p.d ? 'Deceased' : 'Released'); }

    function setFocus(i) {
        focus = i;
        if (i < 0) { focusEl.hidden = true; return; }
        var p = stars[i].p;
        nameEl.textContent = p.n;
        metaEl.textContent = (p.e ? p.e + ' · ' : '') + statusText(p);
        linkEl.href = p.u || '#';
        focusEl.hidden = false;
    }

    cv.addEventListener('mousemove', function (e) {
        var rect = cv.getBoundingClientRect();
        var idx = nearest(e.clientX - rect.left, e.clientY - rect.top, 16);
        hover = idx;
        if (idx >= 0) {
            tooltip.textContent = stars[idx].p.n;
            tooltip.style.left = e.clientX + 'px';
            tooltip.style.top = e.clientY + 'px';
            tooltip.hidden = false;
            cv.style.cursor = 'pointer';
        } else { tooltip.hidden = true; cv.style.cursor = 'default'; }
    });
    cv.addEventListener('mouseleave', function () { hover = -1; tooltip.hidden = true; });

    cv.addEventListener('click', function (e) {
        var rect = cv.getBoundingClientRect();
        var idx = nearest(e.clientX - rect.left, e.clientY - rect.top, 18);
        if (idx >= 0) setFocus(idx); else setFocus(-1);
    });

    document.getElementById('mem-focus-close').addEventListener('click', function () { setFocus(-1); });
    document.getElementById('mem-focus-prev').addEventListener('click', function () { if (focus >= 0) setFocus((focus - 1 + stars.length) % stars.length); });
    document.getElementById('mem-focus-next').addEventListener('click', function () { if (focus >= 0) setFocus((focus + 1) % stars.length); });
    document.addEventListener('keydown', function (e) {
        if (focus < 0) return;
        if (e.key === 'Escape') setFocus(-1);
        else if (e.key === 'ArrowLeft') setFocus((focus - 1 + stars.length) % stars.length);
        else if (e.key === 'ArrowRight') setFocus((focus + 1) % stars.length);
    });

    // Search: jump to a matching star.
    searchInput.addEventListener('input', function () {
        var q = this.value.trim().toLowerCase();
        searchResults.innerHTML = '';
        if (q.length < 2) return;
        var out = [], n = 0;
        for (var i = 0; i < stars.length && n < 8; i++) {
            if (stars[i].p.n.toLowerCase().indexOf(q) !== -1) { out.push(i); n++; }
        }
        out.forEach(function (i) {
            var b = document.createElement('button');
            b.textContent = stars[i].p.n;
            b.addEventListener('click', function () {
                setFocus(i); searchResults.innerHTML = ''; searchInput.value = stars[i].p.n;
            });
            searchResults.appendChild(b);
        });
    });
    searchInput.addEventListener('blur', function () { setTimeout(function () { searchResults.innerHTML = ''; }, 150); });

    window.addEventListener('resize', resize);
    resize();
    requestAnimationFrame(draw);
})();
</script>
@endsection
