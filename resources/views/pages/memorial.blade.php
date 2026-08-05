@extends('app')

@section('title', 'Memorial — One Star for Every Political Prisoner')

@section('head')
<style>
    /* Full-bleed dark memorial, modeled in spirit on gazaschildren.com. */
    body.page-memorial main.container, body.page-memorial .container { width: 100% !important; max-width: none !important; padding-left: 0 !important; padding-right: 0 !important; overflow: visible !important; }
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

    .mem-legend { position: absolute; left: clamp(16px, 3vw, 34px); bottom: calc(clamp(16px, 3vh, 30px) + 74px); display: flex; flex-direction: column; gap: 8px; font-size: 12px; color: #8a8377; z-index: 3; pointer-events: none; }
    .mem-legend span { display: flex; align-items: center; gap: 9px; }
    .mem-dot { width: 9px; height: 9px; border-radius: 50%; flex: 0 0 auto; }
    .mem-dot--custody { background: #f7e4bf; box-shadow: 0 0 10px 2px rgba(247,228,191,0.55); }
    .mem-dot--died { background: #fff8e6; box-shadow: 0 0 12px 3px rgba(255,248,230,0.7); }
    .mem-dot--released { background: #cbbc9e; opacity: 0.7; }

    .mem-tooltip { position: fixed; z-index: 5; pointer-events: none; transform: translate(-50%, -140%); background: rgba(11,10,18,0.92); border: 1px solid rgba(247,228,191,0.25); color: #f7e4bf; font-size: 13px; font-weight: 600; padding: 5px 11px; border-radius: 8px; white-space: nowrap; }
    .mem-tooltip[hidden] { display: none; }

    .mem-focus { position: absolute; left: 50%; bottom: calc(clamp(20px, 5vh, 44px) + 74px); transform: translateX(-50%); z-index: 4; width: min(440px, calc(100vw - 40px)); background: rgba(8,7,14,0.86); backdrop-filter: blur(10px); border: 1px solid rgba(247,228,191,0.22); border-radius: 16px; padding: 22px 24px 20px; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
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

    /* Chronological timeline player (stars ignite in the year of imprisonment). */
    .mem-timeline { position: absolute; left: 0; right: 0; bottom: 0; z-index: 6; display: flex; align-items: center; gap: clamp(10px, 2vw, 20px); padding: 14px clamp(16px, 3vw, 34px) 18px; background: linear-gradient(to top, rgba(5,4,8,0.92) 40%, rgba(5,4,8,0)); }
    .mem-play { flex: 0 0 auto; width: 42px; height: 42px; border-radius: 50%; border: 1px solid rgba(247,228,191,0.35); background: rgba(247,228,191,0.1); color: #f7e4bf; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s, border-color 0.2s; padding: 0; }
    .mem-play:hover { background: rgba(247,228,191,0.2); border-color: rgba(247,228,191,0.6); }
    .mem-play svg { width: 16px; height: 16px; fill: currentColor; }
    .mem-tl-main { flex: 1 1 auto; min-width: 0; }
    .mem-tl-readout { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; margin-bottom: 7px; }
    .mem-tl-year { font-size: clamp(20px, 3.4vw, 30px); font-weight: 700; color: #f7e4bf; letter-spacing: 0.01em; font-variant-numeric: tabular-nums; line-height: 1; }
    .mem-tl-count { font-size: 13px; color: #d6d0c3; text-align: right; }
    .mem-tl-count b { color: #f7e4bf; font-variant-numeric: tabular-nums; }
    .mem-scrub { -webkit-appearance: none; appearance: none; width: 100%; height: 4px; border-radius: 999px; background: rgba(247,228,191,0.18); outline: none; cursor: pointer; }
    .mem-scrub::-webkit-slider-thumb { -webkit-appearance: none; appearance: none; width: 15px; height: 15px; border-radius: 50%; background: #f7e4bf; box-shadow: 0 0 10px 2px rgba(247,228,191,0.55); cursor: pointer; border: 0; }
    .mem-scrub::-moz-range-thumb { width: 15px; height: 15px; border-radius: 50%; background: #f7e4bf; box-shadow: 0 0 10px 2px rgba(247,228,191,0.55); cursor: pointer; border: 0; }
    .mem-speed { flex: 0 0 auto; background: rgba(247,228,191,0.08); border: 1px solid rgba(247,228,191,0.22); color: #f2eee4; font: inherit; font-size: 13px; font-weight: 700; padding: 8px 12px; border-radius: 999px; cursor: pointer; min-width: 50px; text-align: center; transition: background 0.2s; }
    .mem-speed:hover { background: rgba(247,228,191,0.18); }

    @@media (max-width: 640px) {
        .mem { height: calc(100vh - 88px); }
        .mem-legend { flex-direction: row; flex-wrap: wrap; gap: 6px 16px; bottom: calc(clamp(16px, 3vh, 30px) + 92px); }
        .mem-focus { bottom: calc(clamp(20px, 5vh, 44px) + 92px); }
        .mem-tl-year { font-size: 20px; }
        .mem-timeline { gap: 10px; padding: 12px 16px 14px; }
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

    <div class="mem-timeline" id="mem-timeline">
        <button class="mem-play" id="mem-play" aria-label="Play timeline">
            <svg id="mem-play-icon" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 2l11 6-11 6z"/></svg>
        </button>
        <div class="mem-tl-main">
            <div class="mem-tl-readout">
                <span class="mem-tl-year" id="mem-tl-year">{{ $maxYear }}</span>
                <span class="mem-tl-count"><b id="mem-tl-count">{{ number_format($count) }}</b> imprisoned by this year</span>
            </div>
            <input class="mem-scrub" id="mem-scrub" type="range" min="{{ $minYear }}" max="{{ $maxYear }}" value="{{ $maxYear }}" step="1" aria-label="Year">
        </div>
        <button class="mem-speed" id="mem-speed" aria-label="Playback speed">1&times;</button>
    </div>
</div>

<script>window.__MEM = @json($people); window.__MEMCFG = { min: {{ $minYear }}, max: {{ $maxYear }} };</script>
<script>
(function () {
    var DATA = window.__MEM || [];
    var CFG = window.__MEMCFG || { min: 1850, max: new Date().getFullYear() };
    var cv = document.getElementById('mem-canvas');
    var tooltip = document.getElementById('mem-tooltip');
    var focusEl = document.getElementById('mem-focus');
    var nameEl = document.getElementById('mem-focus-name');
    var metaEl = document.getElementById('mem-focus-meta');
    var linkEl = document.getElementById('mem-focus-link');
    var searchInput = document.getElementById('mem-search-input');
    var searchResults = document.getElementById('mem-search-results');
    var yearEl = document.getElementById('mem-tl-year');
    var countEl = document.getElementById('mem-tl-count');
    var scrub = document.getElementById('mem-scrub');
    var playBtn = document.getElementById('mem-play');
    var playIcon = document.getElementById('mem-play-icon');
    var speedBtn = document.getElementById('mem-speed');
    if (!cv) return;

    var N = DATA.length;
    var Wc = 0, Hc = 0, DPR = 1;
    var hover = -1, focus = -1;
    var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 1 : 0;

    var SPEEDS = [1, 2, 4, 0.5];
    var speedIx = 0;
    var curYear = CFG.max;          // fractional "now" on the timeline
    var playing = false;
    var PLAY_SECS = 28;             // wall-clock seconds to sweep min→max at 1×

    // Sorted imprisonment years, for a fast "how many lit by year Y" count.
    var sortedYears = DATA.map(function (p) { return p.y || CFG.min; }).sort(function (a, b) { return a - b; });
    function litCount(y) {
        var lo = 0, hi = sortedYears.length;
        while (lo < hi) { var mid = (lo + hi) >> 1; if (sortedYears[mid] <= y) lo = mid + 1; else hi = mid; }
        return lo;
    }
    // Fractional reveal rank for a (possibly fractional) year — drives the pour
    // exactly like gazaschildren's uRevealCount over each star's appear rank.
    function revealFloat(y) {
        var k = litCount(y);
        if (k >= N) return N;
        var nextY = sortedYears[k], prevY = k > 0 ? sortedYears[k - 1] : CFG.min;
        var span = Math.max(1, nextY - prevY);
        return k + Math.min(1, Math.max(0, (y - prevY) / span));
    }

    // Deterministic per-star RNG (stable positions).
    function mulberry32(a) {
        return function () {
            a |= 0; a = a + 0x6D2B79F5 | 0;
            var t = Math.imul(a ^ a >>> 15, 1 | a);
            t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t;
            return ((t ^ t >>> 14) >>> 0) / 4294967296;
        };
    }

    // Per-star attributes, interleaved for one WebGL point-cloud draw.
    // [clipx, clipy, size, brightness, phase, speed, amplitude, style, order, index, r, g, b]
    var STRIDE = 13;
    var buf = new Float32Array(N * STRIDE);
    var clipx = new Float32Array(N), clipy = new Float32Array(N);
    var sx = new Float32Array(N), sy = new Float32Array(N);   // screen px (css) for hit-testing

    // Chronological appear order (the reveal rank).
    var order = new Float32Array(N);
    var byYear = []; for (var q = 0; q < N; q++) byYear.push(q);
    byYear.sort(function (a, b) { return (DATA[a].y || CFG.min) - (DATA[b].y || CFG.min) || a - b; });
    for (var r = 0; r < N; r++) order[byYear[r]] = r;

    for (var i = 0; i < N; i++) {
        var p = DATA[i];
        var rnd = mulberry32((i * 2654435761 + 12345) >>> 0);
        var cx = -0.985 + rnd() * 1.97;
        var cy = -0.985 + rnd() * 1.97;
        clipx[i] = cx; clipy[i] = cy;
        var depth = rnd(); depth = depth * depth;                 // bias to many small/far, few big/near
        var sSize = p.d ? 2.4 : (p.c ? 2.0 : 1.5);               // deceased largest, imprisoned mid, released small
        var sBright = p.d ? 1.0 : (p.c ? 0.92 : 0.66);
        var col = p.d ? [1.0, 0.97, 0.90] : (p.c ? [0.99, 0.90, 0.74] : [0.82, 0.75, 0.62]);
        var o = i * STRIDE;
        buf[o] = cx; buf[o + 1] = cy;
        buf[o + 2] = sSize * (0.55 + depth * 1.5);
        buf[o + 3] = Math.min(1, sBright * (0.5 + depth * 0.55));
        buf[o + 4] = rnd() * Math.PI * 2;                        // phase
        buf[o + 5] = 0.4 + rnd() * 1.7;                          // speed
        buf[o + 6] = 0.05 + rnd() * 0.22;                        // amplitude
        buf[o + 7] = Math.floor(rnd() * 3);                      // twinkle style 0/1/2
        buf[o + 8] = order[i];
        buf[o + 9] = i;
        buf[o + 10] = col[0]; buf[o + 11] = col[1]; buf[o + 12] = col[2];
    }

    // ---- WebGL point cloud (crisp pinpoints, per-star twinkle, reveal-by-rank) ----
    var gl = cv.getContext('webgl2', { alpha: true, premultipliedAlpha: true, antialias: true })
          || cv.getContext('webgl', { alpha: true, premultipliedAlpha: true, antialias: true });

    var VERT = [
        'precision mediump float;',
        'attribute vec2 aClip; attribute float aSize; attribute float aBrightness;',
        'attribute float aPhase; attribute float aSpeed; attribute float aAmplitude;',
        'attribute float aStyle; attribute float aOrder; attribute float aIndex; attribute vec3 aColor;',
        'uniform float uTime, uPixelRatio, uHoverIndex, uFocusIndex, uReducedMotion, uRevealCount;',
        'varying float vAlpha, vHover, vFocus, vReveal; varying vec3 vColor;',
        'void main(){',
        '  float reveal = clamp(uRevealCount - aOrder, 0.0, 1.0); vReveal = reveal;',
        '  float tw = 0.0;',
        '  if (uReducedMotion < 0.5) {',
        '    float t = uTime * 0.001;',
        '    if (aStyle > 1.5) { tw = (sin(t*aSpeed+aPhase)*0.6 + sin(t*aSpeed*2.3+aPhase+1.1)*0.4) * aAmplitude; }',
        '    else if (aStyle > 0.5) { float s = sin(t*aSpeed+aPhase); tw = (s*s*sign(s)) * aAmplitude; }',
        '    else { tw = sin(t*aSpeed+aPhase) * aAmplitude; }',
        '  }',
        '  float base = clamp(aBrightness + tw, 0.10, 1.0);',
        '  vAlpha = base * reveal;',
        '  float hover = (abs(aIndex - uHoverIndex) < 0.5) ? 1.0 : 0.0;',
        '  float focus = (abs(aIndex - uFocusIndex) < 0.5) ? 1.0 : 0.0;',
        '  vHover = hover; vFocus = focus; vColor = aColor;',
        '  float highlight = max(hover, focus);',
        '  float ps = aSize * uPixelRatio * (1.0 + highlight * 4.0);',
        '  if (highlight > 0.5) ps = max(ps, 22.0 * uPixelRatio);',
        '  gl_PointSize = clamp(ps, 1.0, (highlight > 0.5) ? 64.0 : 20.0);',
        '  gl_Position = vec4(aClip, 0.0, 1.0);',
        '}'
    ].join('\n');

    var FRAG = [
        'precision mediump float;',
        'uniform vec3 uHoverColor, uFocusColor;',
        'varying float vAlpha, vHover, vFocus, vReveal; varying vec3 vColor;',
        'void main(){',
        '  if (vReveal <= 0.0) discard;',
        '  vec2 uv = gl_PointCoord - 0.5; float dist = length(uv);',
        '  if (dist > 0.5) discard;',
        '  float core = smoothstep(0.5, 0.18, dist);',           // crisp pinpoint, no halo
        '  if (vHover > 0.5 || vFocus > 0.5) {',
        '    float pinpoint = smoothstep(0.22, 0.0, dist);',
        '    float glow = smoothstep(0.5, 0.0, dist);',
        '    float intensity = clamp(pinpoint + glow * 0.55, 0.0, 1.0);',
        '    float a = intensity * max(vAlpha, 0.6);',
        '    vec3 c = (vHover > 0.5) ? uHoverColor : uFocusColor;',
        '    gl_FragColor = vec4(c * a, a); return;',
        '  }',
        '  float a = core * vAlpha;',
        '  gl_FragColor = vec4(vColor * a, a);',
        '}'
    ].join('\n');

    var prog, uni = {};
    function compile(type, src) {
        var sh = gl.createShader(type); gl.shaderSource(sh, src); gl.compileShader(sh);
        return sh;
    }
    function initGL() {
        prog = gl.createProgram();
        gl.attachShader(prog, compile(gl.VERTEX_SHADER, VERT));
        gl.attachShader(prog, compile(gl.FRAGMENT_SHADER, FRAG));
        gl.linkProgram(prog);
        if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) { gl = null; return false; }
        gl.useProgram(prog);

        var vbo = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, vbo);
        gl.bufferData(gl.ARRAY_BUFFER, buf, gl.STATIC_DRAW);
        var FS = 4, ST = STRIDE * FS;
        function attr(name, size, off) {
            var loc = gl.getAttribLocation(prog, name);
            gl.enableVertexAttribArray(loc);
            gl.vertexAttribPointer(loc, size, gl.FLOAT, false, ST, off * FS);
        }
        attr('aClip', 2, 0); attr('aSize', 1, 2); attr('aBrightness', 1, 3);
        attr('aPhase', 1, 4); attr('aSpeed', 1, 5); attr('aAmplitude', 1, 6);
        attr('aStyle', 1, 7); attr('aOrder', 1, 8); attr('aIndex', 1, 9); attr('aColor', 3, 10);

        ['uTime', 'uPixelRatio', 'uHoverIndex', 'uFocusIndex', 'uReducedMotion', 'uRevealCount', 'uHoverColor', 'uFocusColor']
            .forEach(function (u) { uni[u] = gl.getUniformLocation(prog, u); });
        gl.uniform3f(uni.uHoverColor, 1.0, 0.96, 0.86);
        gl.uniform3f(uni.uFocusColor, 1.0, 0.90, 0.62);
        gl.uniform1f(uni.uReducedMotion, reduced);

        gl.disable(gl.DEPTH_TEST);
        gl.enable(gl.BLEND);
        gl.blendFunc(gl.ONE, gl.ONE_MINUS_SRC_ALPHA);           // premultiplied output
        gl.clearColor(0, 0, 0, 0);
        return true;
    }

    // 2D fallback (crisp dots) if WebGL is unavailable.
    var ctx2d = null;
    if (!gl || !initGL()) { gl = null; ctx2d = cv.getContext('2d'); }

    function resize() {
        DPR = Math.min(2, window.devicePixelRatio || 1);
        var rect = cv.getBoundingClientRect();
        Wc = rect.width; Hc = rect.height;
        cv.width = Math.round(Wc * DPR); cv.height = Math.round(Hc * DPR);
        for (var i = 0; i < N; i++) {
            sx[i] = (clipx[i] * 0.5 + 0.5) * Wc;
            sy[i] = (1 - (clipy[i] * 0.5 + 0.5)) * Hc;
        }
        if (gl) { gl.viewport(0, 0, cv.width, cv.height); gl.useProgram(prog); gl.uniform1f(uni.uPixelRatio, DPR); }
    }

    function drawGL(now, rc) {
        gl.uniform1f(uni.uTime, now);
        gl.uniform1f(uni.uRevealCount, rc);
        gl.uniform1f(uni.uHoverIndex, hover);
        gl.uniform1f(uni.uFocusIndex, focus);
        gl.clear(gl.COLOR_BUFFER_BIT);
        gl.drawArrays(gl.POINTS, 0, N);
    }

    function drawFallback(now, rc) {
        var t = now * 0.001;
        ctx2d.setTransform(DPR, 0, 0, DPR, 0, 0);
        ctx2d.clearRect(0, 0, Wc, Hc);
        ctx2d.globalCompositeOperation = 'lighter';
        for (var i = 0; i < N; i++) {
            var o = i * STRIDE;
            var reveal = Math.min(1, Math.max(0, rc - buf[o + 8]));
            if (reveal <= 0) continue;
            var tw = reduced ? 0 : Math.sin(t * buf[o + 5] + buf[o + 4]) * buf[o + 6];
            var a = Math.min(1, (buf[o + 3] + tw)) * reveal;
            var hi = (i === hover || i === focus);
            var rad = buf[o + 2] * (hi ? 2.2 : 0.9);
            ctx2d.globalAlpha = hi ? Math.max(0.7, a) : a;
            ctx2d.fillStyle = 'rgb(' + (buf[o + 10] * 255 | 0) + ',' + (buf[o + 11] * 255 | 0) + ',' + (buf[o + 12] * 255 | 0) + ')';
            ctx2d.beginPath(); ctx2d.arc(sx[i], sy[i], rad, 0, 7); ctx2d.fill();
        }
        ctx2d.globalAlpha = 1; ctx2d.globalCompositeOperation = 'source-over';
    }

    var lastFrame = performance.now();
    function frame(now) {
        var dt = Math.min(0.1, (now - lastFrame) / 1000); lastFrame = now;
        if (playing) {
            curYear += dt * ((CFG.max - CFG.min) / PLAY_SECS) * SPEEDS[speedIx];
            if (curYear >= CFG.max) { curYear = CFG.max; setPlaying(false); }
            syncTimeline();
        }
        var rc = revealFloat(curYear);
        if (gl) drawGL(now, rc); else drawFallback(now, rc);
        requestAnimationFrame(frame);
    }

    // ---- Timeline controls ----
    var ICON_PLAY = '<path d="M3 2l11 6-11 6z"/>';
    var ICON_PAUSE = '<path d="M3 2h4v12H3zM9 2h4v12H9z"/>';
    function setPlaying(on) {
        playing = on;
        playIcon.innerHTML = on ? ICON_PAUSE : ICON_PLAY;
        playBtn.setAttribute('aria-label', on ? 'Pause timeline' : 'Play timeline');
    }
    function syncTimeline() {
        var yInt = Math.round(curYear);
        yearEl.textContent = yInt;
        countEl.textContent = litCount(curYear).toLocaleString();
        scrub.value = yInt;
    }
    playBtn.addEventListener('click', function () {
        if (playing) { setPlaying(false); return; }
        if (curYear >= CFG.max) { curYear = CFG.min; syncTimeline(); }
        setPlaying(true);
    });
    scrub.addEventListener('input', function () { setPlaying(false); curYear = parseInt(this.value, 10); syncTimeline(); });
    speedBtn.addEventListener('click', function () {
        speedIx = (speedIx + 1) % SPEEDS.length;
        var s = SPEEDS[speedIx];
        speedBtn.innerHTML = (s === 0.5 ? '½' : s) + '×';
    });

    // ---- Interaction (hover / focus / search) ----
    function nearest(mx, my, maxPx) {
        var best = -1, bd = maxPx * maxPx;
        for (var i = 0; i < N; i++) {
            var dx = sx[i] - mx, dy = sy[i] - my, d = dx * dx + dy * dy;
            if (d < bd) { bd = d; best = i; }
        }
        return best;
    }
    function statusText(p) { return p.c ? 'Currently imprisoned' : (p.d ? 'Deceased' : 'Released'); }
    function setFocus(i) {
        focus = i;
        if (i < 0) { focusEl.hidden = true; return; }
        var p = DATA[i];
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
            tooltip.textContent = DATA[idx].n;
            tooltip.style.left = e.clientX + 'px';
            tooltip.style.top = e.clientY + 'px';
            tooltip.hidden = false; cv.style.cursor = 'pointer';
        } else { tooltip.hidden = true; cv.style.cursor = 'default'; }
    });
    cv.addEventListener('mouseleave', function () { hover = -1; tooltip.hidden = true; });
    cv.addEventListener('click', function (e) {
        var rect = cv.getBoundingClientRect();
        var idx = nearest(e.clientX - rect.left, e.clientY - rect.top, 18);
        setFocus(idx >= 0 ? idx : -1);
    });

    document.getElementById('mem-focus-close').addEventListener('click', function () { setFocus(-1); });
    document.getElementById('mem-focus-prev').addEventListener('click', function () { if (focus >= 0) setFocus((focus - 1 + N) % N); });
    document.getElementById('mem-focus-next').addEventListener('click', function () { if (focus >= 0) setFocus((focus + 1) % N); });
    document.addEventListener('keydown', function (e) {
        if (focus < 0) return;
        if (e.key === 'Escape') setFocus(-1);
        else if (e.key === 'ArrowLeft') setFocus((focus - 1 + N) % N);
        else if (e.key === 'ArrowRight') setFocus((focus + 1) % N);
    });

    searchInput.addEventListener('input', function () {
        var query = this.value.trim().toLowerCase();
        searchResults.innerHTML = '';
        if (query.length < 2) return;
        var n = 0;
        for (var i = 0; i < N && n < 8; i++) {
            if (DATA[i].n.toLowerCase().indexOf(query) !== -1) {
                (function (idx) {
                    var b = document.createElement('button');
                    b.textContent = DATA[idx].n;
                    b.addEventListener('click', function () { setFocus(idx); searchResults.innerHTML = ''; searchInput.value = DATA[idx].n; });
                    searchResults.appendChild(b);
                })(i);
                n++;
            }
        }
    });
    searchInput.addEventListener('blur', function () { setTimeout(function () { searchResults.innerHTML = ''; }, 150); });

    syncTimeline();
    window.addEventListener('resize', resize);
    resize();
    requestAnimationFrame(frame);
})();
</script>
@endsection
