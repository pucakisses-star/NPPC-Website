{{-- Shared assets for the scribble text decoration (CodyHouse-style
     hand-drawn underline that draws in when the word scrolls into view).
     Include once per page inside @section('head'); the @once guard keeps
     it from duplicating if a page decorates more than one word. --}}
@once
<style>
    .scr { position: relative; white-space: nowrap; color: #5660fe; }
    .scr > svg { position: absolute; left: -4%; bottom: -0.30em; width: 108%; height: 0.5em;
        overflow: visible; pointer-events: none; }
    .scr > svg path { fill: none; stroke: currentColor; stroke-width: 6; stroke-linecap: round;
        stroke-linejoin: round; stroke-dasharray: var(--len); stroke-dashoffset: var(--len); }
    .scr > svg path.p2 { stroke-width: 4; opacity: 0.8; }
    .scr.drawn > svg path { animation: scrDraw 1.1s cubic-bezier(.65, 0, .35, 1) forwards; }
    .scr.drawn > svg path.p2 { animation-delay: 0.35s; }
    @keyframes scrDraw { to { stroke-dashoffset: 0; } }
    @media (prefers-reduced-motion: reduce) {
        .scr > svg path { stroke-dashoffset: 0; }
        .scr.drawn > svg path { animation: none; }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) { e.target.classList.add('drawn'); io.unobserve(e.target); }
            });
        }, { threshold: 0.6 });
        document.querySelectorAll('.scr').forEach(function (el) { io.observe(el); });
    });
</script>
@endonce
