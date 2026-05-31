@extends('app')

@section('title', 'Voronoi Stippling — Experiment | NPPC')

@section('head')
<meta name="description" content="An interactive weighted Voronoi stippling experiment: turn any image into a field of dots whose density follows the image's tones.">
<script src="https://cdn.jsdelivr.net/npm/d3-delaunay@6/dist/d3-delaunay.min.js" defer></script>
<style>
    /* ============================================================
       Voronoi stippling experiment. Weighted Lloyd's relaxation on a
       canvas, using d3-delaunay. All classes scoped with the stp- prefix.
       ============================================================ */
    body.page-stippling { background: #0a0a0b; }
    body.page-stippling main.container,
    body.page-stippling .container { max-width: none !important; width: 100% !important; padding: 0 !important; }

    .stp { color: #ece9e2; background: #0a0a0b; font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; padding: 40px 24px 64px; }
    .stp-wrap { max-width: 1040px; margin: 0 auto; }
    .stp-kicker { font-size: 11px; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase; color: #e0a82e; }
    .stp-h1 { font-size: clamp(2rem, 4vw, 2.9rem); font-weight: 800; letter-spacing: -0.02em; color: #fff; margin: 10px 0 12px; }
    .stp-lede { font-size: 1.02rem; line-height: 1.6; color: rgba(236,233,226,0.62); max-width: 680px; margin: 0 0 28px; }

    .stp-stage { display: grid; grid-template-columns: 1fr 260px; gap: 22px; align-items: start; }
    .stp-canvas-wrap { position: relative; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; overflow: hidden; background: #0e0e10; min-height: 320px; display: flex; align-items: center; justify-content: center; }
    #stp-canvas { display: block; width: 100%; height: auto; }
    .stp-drop { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; pointer-events: none; opacity: 0; transition: opacity 0.15s ease; background: rgba(224,168,46,0.14); border: 2px dashed rgba(224,168,46,0.7); border-radius: 12px; font-weight: 700; color: #fff; }
    .stp-canvas-wrap.is-drag .stp-drop { opacity: 1; }

    .stp-panel { background: #0e0e10; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 18px 18px 20px; }
    .stp-field { margin-bottom: 18px; }
    .stp-field:last-child { margin-bottom: 0; }
    .stp-field-lab { display: flex; align-items: baseline; justify-content: space-between; font-size: 11px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; color: rgba(236,233,226,0.55); margin-bottom: 9px; }
    .stp-field-lab b { color: #fff; font-weight: 800; font-variant-numeric: tabular-nums; }
    .stp-field input[type="range"] { width: 100%; accent-color: #e0a82e; }
    .stp-row { display: flex; align-items: center; gap: 9px; }
    .stp-row input[type="checkbox"] { accent-color: #e0a82e; width: 16px; height: 16px; }
    .stp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; font: inherit; font-size: 13px; font-weight: 700; letter-spacing: 0.02em; padding: 10px 14px; border-radius: 8px; cursor: pointer; border: 1px solid; transition: all 0.14s ease; }
    .stp-btn-primary { background: #e0a82e; border-color: #e0a82e; color: #19140a; }
    .stp-btn-primary:hover { background: #f0b945; }
    .stp-btn-ghost { background: transparent; border-color: rgba(255,255,255,0.18); color: #ece9e2; margin-top: 10px; }
    .stp-btn-ghost:hover { border-color: #fff; }
    .stp-file { display: none; }
    .stp-status { margin-top: 16px; font-size: 12px; color: rgba(236,233,226,0.5); font-variant-numeric: tabular-nums; min-height: 16px; }
    .stp-hint { margin-top: 22px; font-size: 12.5px; line-height: 1.6; color: rgba(236,233,226,0.45); }
    .stp-hint a { color: #e0a82e; }

    @@media (max-width: 760px) {
        .stp-stage { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('body')
<div class="stp">
    <div class="stp-wrap">
        <div class="stp-kicker">Experiment</div>
        <h1 class="stp-h1">Voronoi Stippling</h1>
        <p class="stp-lede">Turn an image into a field of dots whose density follows its tones — denser where the image is dark, sparse where it's light. The dots start scattered and settle over a few seconds via weighted Lloyd's relaxation (a centroidal Voronoi tessellation). Drop in your own image to try it.</p>

        <div class="stp-stage">
            <div class="stp-canvas-wrap" id="stp-drop">
                <canvas id="stp-canvas" width="600" height="400"></canvas>
                <div class="stp-drop">Drop an image to stipple it</div>
            </div>

            <div class="stp-panel">
                <div class="stp-field">
                    <div class="stp-field-lab">Dots <b id="stp-dots-v">2,600</b></div>
                    <input type="range" id="stp-dots" min="500" max="8000" step="100" value="2600">
                </div>
                <div class="stp-field">
                    <div class="stp-field-lab">Dot size <b id="stp-size-v">1.4</b></div>
                    <input type="range" id="stp-size" min="0.5" max="3" step="0.1" value="1.4">
                </div>
                <div class="stp-field">
                    <div class="stp-field-lab">Contrast <b id="stp-contrast-v">1.0</b></div>
                    <input type="range" id="stp-contrast" min="0.4" max="2.6" step="0.1" value="1.0">
                </div>
                <div class="stp-field">
                    <label class="stp-row"><input type="checkbox" id="stp-light"> <span>Light background</span></label>
                </div>
                <button type="button" class="stp-btn stp-btn-primary" id="stp-upload">Upload an image</button>
                <button type="button" class="stp-btn stp-btn-ghost" id="stp-restart">Restart</button>
                <input type="file" id="stp-file" class="stp-file" accept="image/*">
                <div class="stp-status" id="stp-status">Loading…</div>
            </div>
        </div>

        <p class="stp-hint">Inspired by Mike Bostock's <a href="https://observablehq.com/@@mbostock/voronoi-stippling" target="_blank" rel="noopener">Voronoi Stippling</a> notebook, reimplemented here in vanilla JS with <code>d3-delaunay</code>. Everything runs in your browser — uploaded images never leave your device.</p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var cv = document.getElementById('stp-canvas');
        var statusEl = document.getElementById('stp-status');
        function setStatus(t) { if (statusEl) statusEl.textContent = t; }

        if (!window.d3 || !d3.Delaunay) { setStatus('Could not load the geometry library.'); return; }

        var ctx = cv.getContext('2d');
        var off = document.createElement('canvas');
        var octx = off.getContext('2d', { willReadFrequently: true });

        var MAXW = 600, MAX_ITERS = 90;
        var W = 0, H = 0, density = null, points = null, n = 0, raf = 0, iter = 0, lastImage = null;
        var opts = { n: 2600, r: 1.4, gamma: 1.0, dark: true };

        function computeDensity(image) {
            var s = Math.min(1, MAXW / image.width);
            W = Math.max(1, Math.round(image.width * s));
            H = Math.max(1, Math.round(image.height * s));
            off.width = W; off.height = H;
            octx.clearRect(0, 0, W, H);
            octx.drawImage(image, 0, 0, W, H);
            var data;
            try { data = octx.getImageData(0, 0, W, H).data; }
            catch (e) { setStatus('Could not read that image (blocked by cross-origin rules).'); return false; }

            density = new Float64Array(W * H);
            var max = 0;
            for (var i = 0, p = 0; i < density.length; i++, p += 4) {
                var lum = (0.2126 * data[p] + 0.7152 * data[p + 1] + 0.0722 * data[p + 2]) / 255;
                var a = data[p + 3] / 255;
                var d = Math.pow((1 - lum) * a, opts.gamma);
                density[i] = d;
                if (d > max) max = d;
            }
            if (max > 0) { for (var j = 0; j < density.length; j++) density[j] /= max; }

            cv.width = W; cv.height = H;
            return true;
        }

        function seed() {
            n = opts.n;
            points = new Float64Array(n * 2);
            var i = 0, attempts = 0, cap = n * 80;
            while (i < n && attempts < cap) {
                attempts++;
                var x = (Math.random() * W) | 0;
                var y = (Math.random() * H) | 0;
                if (Math.random() < density[y * W + x]) {
                    points[i * 2] = x + 0.5;
                    points[i * 2 + 1] = y + 0.5;
                    i++;
                }
            }
            for (; i < n; i++) { points[i * 2] = Math.random() * W; points[i * 2 + 1] = Math.random() * H; }
        }

        function step() {
            var del = new d3.Delaunay(points);
            var cx = new Float64Array(n), cy = new Float64Array(n), cw = new Float64Array(n);
            var hint = 0;
            for (var y = 0, i = 0; y < H; y++) {
                for (var x = 0; x < W; x++, i++) {
                    var w = density[i];
                    if (w <= 0) continue;
                    hint = del.find(x, y, hint);
                    cx[hint] += x * w; cy[hint] += y * w; cw[hint] += w;
                }
            }
            for (var k = 0; k < n; k++) {
                if (cw[k] > 0) { points[k * 2] = cx[k] / cw[k]; points[k * 2 + 1] = cy[k] / cw[k]; }
            }
        }

        function draw() {
            ctx.fillStyle = opts.dark ? '#0a0a0b' : '#f4f1ea';
            ctx.fillRect(0, 0, W, H);
            ctx.fillStyle = opts.dark ? '#ece9e2' : '#14110e';
            var r = opts.r;
            for (var i = 0; i < n; i++) {
                ctx.beginPath();
                ctx.arc(points[i * 2], points[i * 2 + 1], r, 0, 6.283185307179586);
                ctx.fill();
            }
        }

        function tick() {
            step(); draw(); iter++;
            if (iter < MAX_ITERS) { setStatus('Relaxing… ' + iter + ' / ' + MAX_ITERS); raf = requestAnimationFrame(tick); }
            else { setStatus('Done · ' + n.toLocaleString() + ' dots'); }
        }

        function start() {
            cancelAnimationFrame(raf);
            iter = 0;
            if (!density) return;
            seed(); draw();
            raf = requestAnimationFrame(tick);
        }

        function loadImage(src) {
            var img = new Image();
            img.onload = function () { lastImage = img; if (computeDensity(img)) start(); };
            img.onerror = function () { setStatus('Failed to load that image.'); };
            img.src = src;
        }

        function readFile(file) {
            if (!file || !/^image\//.test(file.type)) return;
            var fr = new FileReader();
            fr.onload = function () { loadImage(fr.result); };
            fr.readAsDataURL(file);
        }

        // ---- controls ----
        var dots = document.getElementById('stp-dots');
        var size = document.getElementById('stp-size');
        var contrast = document.getElementById('stp-contrast');
        var light = document.getElementById('stp-light');
        var file = document.getElementById('stp-file');

        dots.addEventListener('input', function () { opts.n = +this.value; document.getElementById('stp-dots-v').textContent = opts.n.toLocaleString(); });
        dots.addEventListener('change', start);
        size.addEventListener('input', function () { opts.r = +this.value; document.getElementById('stp-size-v').textContent = (+this.value).toFixed(1); if (density) draw(); });
        contrast.addEventListener('input', function () { opts.gamma = +this.value; document.getElementById('stp-contrast-v').textContent = (+this.value).toFixed(1); });
        contrast.addEventListener('change', function () { if (lastImage && computeDensity(lastImage)) start(); });
        light.addEventListener('change', function () { opts.dark = !this.checked; if (density) draw(); });
        document.getElementById('stp-restart').addEventListener('click', start);
        document.getElementById('stp-upload').addEventListener('click', function () { file.click(); });
        file.addEventListener('change', function (e) { readFile(e.target.files && e.target.files[0]); });

        // ---- drag & drop ----
        var dropZone = document.getElementById('stp-drop');
        ['dragenter', 'dragover'].forEach(function (ev) {
            dropZone.addEventListener(ev, function (e) { e.preventDefault(); dropZone.classList.add('is-drag'); });
        });
        ['dragleave', 'drop'].forEach(function (ev) {
            dropZone.addEventListener(ev, function (e) { e.preventDefault(); dropZone.classList.remove('is-drag'); });
        });
        dropZone.addEventListener('drop', function (e) { readFile(e.dataTransfer.files && e.dataTransfer.files[0]); });

        // ---- go ----
        setStatus('Loading…');
        loadImage('/images/prison-hell.jpg');
    });
</script>
@endsection
