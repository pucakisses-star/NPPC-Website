{{-- Shared assets for the scribble text decoration (CodyHouse-style
     hand-drawn oval that circles the word on hover — drawing in on
     mouse-enter / keyboard focus and erasing on leave). Include once per
     page inside @section('head'); the @once guard prevents duplication.
     JS builds a pixel-fitted oval per word so it stays undistorted at any
     size; CSS :hover drives the reversible draw. --}}
@once
<style>
    .scr { position: relative; display: inline-block; white-space: nowrap; color: #5660fe; }
    .scr > svg { position: absolute; overflow: visible; pointer-events: none; }
    .scr > svg path { fill: none; stroke: #5660fe; stroke-width: 3; stroke-linecap: round; stroke-linejoin: round;
        stroke-dasharray: var(--len, 900px); stroke-dashoffset: var(--len, 900px);
        transition: stroke-dashoffset 0.85s cubic-bezier(.65, 0, .35, 1); }
    .scr:hover > svg path, .scr:focus-visible > svg path { stroke-dashoffset: 0; }
    @media (prefers-reduced-motion: reduce) { .scr > svg path { transition: none; } }
</style>
<script>
    (function () {
        function build() {
            document.querySelectorAll('.scr').forEach(function (scr) {
                var old = scr.querySelector('svg'); if (old) old.remove();
                var r = scr.getBoundingClientRect(); if (!r.width) return;
                var w = r.width, h = r.height, padX = w * 0.13 + 8, padY = h * 0.26 + 6;
                var W = Math.round(w + padX * 2), H = Math.round(h + padY * 2);
                var cx = W / 2, cy = H / 2, rx = W / 2 - 3, ry = H / 2 - 3;
                var sx = cx + rx * 0.52, sy = cy - ry * 0.82, ex = cx - rx * 0.08, ey = cy - ry * 0.99,
                    tx = cx + rx * 0.72, ty = cy - ry * 0.5;
                // A near-full elliptical arc with a small overshoot tail — a
                // hand-drawn circle around the word.
                var d = 'M' + sx.toFixed(1) + ',' + sy.toFixed(1) +
                    ' A' + rx.toFixed(1) + ',' + ry.toFixed(1) + ' 0 1 1 ' + ex.toFixed(1) + ',' + ey.toFixed(1) +
                    ' C' + (ex + rx * 0.42).toFixed(1) + ',' + (ey - ry * 0.05).toFixed(1) + ' ' +
                           tx.toFixed(1) + ',' + (ty - ry * 0.22).toFixed(1) + ' ' + tx.toFixed(1) + ',' + ty.toFixed(1);
                var ns = 'http://www.w3.org/2000/svg';
                var svg = document.createElementNS(ns, 'svg');
                svg.setAttribute('viewBox', '0 0 ' + W + ' ' + H);
                svg.setAttribute('aria-hidden', 'true');
                svg.style.cssText = 'left:' + (-padX) + 'px;top:' + (-padY) + 'px;width:' + W + 'px;height:' + H + 'px';
                var path = document.createElementNS(ns, 'path');
                path.setAttribute('d', d);
                svg.appendChild(path);
                scr.appendChild(svg);
                path.style.setProperty('--len', Math.ceil(path.getTotalLength()) + 'px');
            });
        }
        function boot() {
            build();
            var t;
            addEventListener('resize', function () { clearTimeout(t); t = setTimeout(build, 200); });
            if (document.fonts && document.fonts.ready) document.fonts.ready.then(build);
        }
        if (document.readyState !== 'loading') boot();
        else document.addEventListener('DOMContentLoaded', boot);
    })();
</script>
@endonce
