@php
$isHome = request()->segment(1) == ''
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#000000">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    @php
        // Page title with a site-wide default so no page ships an empty
        // <title>. Sections may end in " | NPPC" etc.; the raw yield is
        // also reused for the social tags below.
        // Inline @section('title', '...') values arrive HTML-escaped by
        // Blade, and {{ }} escapes again — so "&" would render as a literal
        // "&amp;" in the tab. Decode once here; {{ }} below re-escapes.
        $pageTitle = html_entity_decode(trim($__env->yieldContent('title')), ENT_QUOTES | ENT_HTML5) ?: 'National Political Prisoner Coalition';
        $pageDesc = html_entity_decode(trim($__env->yieldContent('meta_description')), ENT_QUOTES | ENT_HTML5)
            ?: 'The National Political Prisoner Coalition documents, supports, and advocates for U.S. political prisoners — a live database, case files, news, and history from the nineteenth century to the present.';
    @endphp
    <title>{{ $pageTitle }}</title>

    {{-- Social / link-preview tags. Pages can override the description via
         @section('meta_description', '...'); a page-specific
         <meta name="description"> in @section('head') still wins for search
         engines since crawlers use the first occurrence. --}}
    <meta property="og:site_name" content="National Political Prisoner Coalition">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDesc }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @php
        // Pages with a subject image (prisoner photo, article cover) override
        // via @section('og_image', ...); crawlers use the first og:image, so
        // the override must land here rather than in the page's head section.
        $ogImage = html_entity_decode(trim($__env->yieldContent('og_image')), ENT_QUOTES | ENT_HTML5)
            ?: asset('images/og-default.jpg').'?v='.@filemtime(public_path('images/og-default.jpg'));
    @endphp
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    {{-- Favicons (white NPPC monogram on black) --}}
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v={{ @filemtime(public_path('favicon-32x32.png')) }}">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v={{ @filemtime(public_path('favicon-16x16.png')) }}">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v={{ @filemtime(public_path('apple-touch-icon.png')) }}">

    <link href="/app.css?v={{ @filemtime(public_path('app.css')) }}" rel="stylesheet"/>
    <link href="/styles.css?v={{ @filemtime(public_path('styles.css')) }}" rel="stylesheet"/>
    <link href="/fontawesome/css/all.min.css" rel="stylesheet"/>
    <link href="/fontawesome/css/thin.css" rel="stylesheet"/>
    <link href="/fonts/verlag/stylesheet.css" rel="stylesheet"/>
    <link href="/fonts/flood-std.css" rel="stylesheet"/>
    <link href="/style/nav.css" rel="stylesheet"/>
    <link href="/style/basics.css?v={{ @filemtime(public_path('style/basics.css')) }}" rel="stylesheet"/>
    <link href="/style/scss/app.css?v={{ @filemtime(public_path('style/scss/app.css')) }}" rel="stylesheet"/>

    <link rel="stylesheet" href="{{ asset('vendor/laraberg/css/laraberg.css') }}">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    @livewireStyles
    @php
        // Cache-bust the built Vue bundle by its file mtime so a fresh
        // `yarn build` is picked up without a manual hard-refresh.
        $vueCssV = @filemtime(public_path('vue/app.css')) ?: time();
        $vueJsV  = @filemtime(public_path('vue/app.js')) ?: time();
    @endphp
    <link rel="stylesheet" href="/vue/app.css?v={{ $vueCssV }}">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="/vue/app.js?v={{ $vueJsV }}" defer></script>
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script>
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) window.location.reload();
        });
    </script>

    <style>
        .container {
            overflow:hidden;
        }

        .grecaptcha-badge {
            visibility: hidden !important;
        }

        .page-news .container {
            overflow: visible;
        }

        body.home-page .container {
            overflow: visible;
        }

        /* The /history page uses position: sticky for its sidecar
           visual, which silently breaks if any ancestor has
           overflow: hidden / auto / scroll. */
        .page-history .container {
            overflow: visible;
        }

        /* /calendar/<day> pins its left date column with position: sticky,
           which needs overflow: visible up the ancestor chain. */
        .page-calendar .container {
            overflow: visible;
        }

        /* === Site-wide mobile touch polish === */
        html { -webkit-tap-highlight-color: transparent; }
        a, button, [role="button"], input[type="submit"], input[type="button"] {
            touch-action: manipulation;
        }
        /* Visible focus ring for keyboard users (suppress for mouse via :focus-visible) */
        a:focus-visible, button:focus-visible, input:focus-visible, textarea:focus-visible, select:focus-visible {
            outline: 2px solid var(--accent, #5660fe);
            outline-offset: 2px;
        }
        /* Skip-to-content link — visible only on keyboard focus */
        .skip-to-content {
            position: absolute; left: -9999px; top: 8px;
            background: var(--accent, #5660fe); color: var(--on-accent, #fff); padding: 10px 16px;
            border-radius: 4px; font-weight: 700; z-index: 999999;
            text-decoration: none;
        }
        .skip-to-content:focus { left: 8px; }

        /* Assorted widgets (e.g. the Vue gallery carousel) and elements
           styled with utility classes missing from the purged Tailwind
           build can extend past the right edge and widen the mobile
           layout viewport, making every page pannable sideways. Clip the
           viewport at the root: unlike overflow-x hidden, 'clip' creates
           no scroll container, so position: sticky keeps working. */
        html { overflow-x: clip; }
        @supports not (overflow: clip) { html { overflow-x: hidden; } }
        /* The Vue gallery's .carousel wrapper carries an overflow-hidden
           utility class that was purged from vue/app.css, so its multi-
           thousand-pixel slide track lays out unclipped and expands the
           mobile layout viewport before the root clip can apply (fixed
           elements then track the widened viewport — clipped nav logo,
           sideways pan). Clip the track at its wrapper. */
        .carousel { overflow: hidden !important; }
        /* Two more layout-viewport wideners found by live probing:
           - the Vue graph section's tab row lays out wider than the
             screen — let it scroll horizontally instead;
           - the footer's unbreakable email address forces its 1fr grid
             column past the viewport — allow grid items to shrink and
             long strings to wrap. */
        #graph-component nav .container { overflow-x: auto; flex-wrap: nowrap; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
        #graph-component nav .container::-webkit-scrollbar { display: none; }
        #app-footer-v2 .f2-top > * { min-width: 0; }
        #app-footer-v2 a, #app-footer-v2 p { overflow-wrap: anywhere; }

        @media (max-width: 768px) {
            /* iOS Safari zooms inputs whose font-size is below 16px on
               focus. Force a 16px floor on form fields so input focus
               never triggers a viewport zoom. */
            input:not([type="checkbox"]):not([type="radio"]),
            textarea, select {
                font-size: 16px !important;
            }
            /* iOS notch safe-area insets — apply to common edge gutters
               so content doesn't disappear behind the notch / home bar
               on landscape iPhone */
            body { padding-left: env(safe-area-inset-left); padding-right: env(safe-area-inset-right); }
            .pet-banner, .events-page, .prisoner-page, .donate-page { padding-left: max(16px, env(safe-area-inset-left)) !important; padding-right: max(16px, env(safe-area-inset-right)) !important; }
        }

        /* Content hyperlink hover: CodyHouse "underline" text-background effect.
           A thin accent underline draws in from left to right on hover. The
           text-shadow (in the page background colour) lets the line pass
           cleanly behind letter descenders. Scoped to prose links inside
           article/page bodies so navigation, buttons, and cards keep their
           own styles. */
        .page-content a {
            color: var(--accent);
            text-decoration: none;
            background-repeat: no-repeat;
            background-position: left center;
            background-size: 0% 100%;
            background-image: linear-gradient(transparent calc(100% - 3px), currentColor calc(100% - 3px), currentColor calc(100% - 2px), transparent 2px);
            text-shadow: 1.5px 1px var(--bg), -1.5px 1px var(--bg), 0 1px var(--bg);
            will-change: background-size;
            transition: background-size 0.3s cubic-bezier(0.645, 0.045, 0.355, 1);
        }
        .page-content a:hover,
        .page-content a:focus-visible {
            background-size: 100% 100%;
        }

        /* Respect the user's reduced-motion preference (iOS / Android /
           macOS / Windows accessibility setting) — disable the page-
           transition fade and any non-essential CSS transitions. */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
            #page-transition { display: none !important; }
        }
    </style>

    {{-- ===== Global light/dark theme (full-site rollout) =====
         Dark values match the current look exactly; [data-theme="light"] flips
         the palette. Muted whites use rgba(var(--fg-rgb), …) so one triplet
         recolors them all. --on-dark / --feature-* stay constant so intentional
         dark sections (heroes) keep light text in both themes. --}}
    <style>
        :root {
            --bg: #000000;
            --surface: #16181f;
            --surface-2: #1a1a2e;
            --fg: #ffffff;
            --fg-rgb: 255,255,255;
            --accent: #5660fe;
            --accent-2: #8b93ff;
            --accent-hover: #4049d6;
            --on-accent: #ffffff;
            --on-dark: #ffffff;
            /* Store aliases (store CSS still references --store-*). */
            --store-bg: transparent; --store-fg: var(--fg); --store-fg-rgb: var(--fg-rgb);
            --store-surface: var(--surface); --store-surface-2: var(--surface-2);
            --store-accent: var(--accent); --store-accent-2: var(--accent-2);
            --store-accent-hover: var(--accent-hover); --store-on-accent: var(--on-accent);
        }
        html[data-theme="light"] {
            --bg: #ffffff;
            --surface: #ffffff;
            --surface-2: #e6e8ed;
            --fg: #15171c;
            --fg-rgb: 21,23,28;
            --accent: #5660fe;
            --accent-2: #4049d6;
            --accent-hover: #3a42b8;
            --on-accent: #ffffff;
            --on-dark: #ffffff;
            --store-bg: #ffffff;
        }
        /* The compiled CSS sets a dark body background; override it in light mode. */
        html[data-theme="light"] body { background: var(--bg); color: var(--fg); }

        /* Global theme toggle (bottom-left, accent pill). */
        .site-theme-toggle {
            position: fixed; left: 20px; bottom: 20px; z-index: 2147483000;
            display: inline-flex; align-items: center; gap: 8px;
            padding: 11px 16px; border-radius: 999px; border: none;
            background: var(--accent); color: var(--on-accent);
            font-size: 13px; font-weight: 700; cursor: pointer;
            box-shadow: 0 6px 20px rgba(0,0,0,0.35); transition: transform 0.15s, background 0.15s;
        }
        .site-theme-toggle:hover { transform: translateY(-2px); background: var(--accent-hover); }
        .site-theme-toggle svg { width: 16px; height: 16px; display: block; }
        .site-theme-toggle .site-theme-moon { display: none; }
        html[data-theme="light"] .site-theme-toggle .site-theme-sun { display: none; }
        html[data-theme="light"] .site-theme-toggle .site-theme-moon { display: block; }

        /* Light-mode fixes for compiled/JS widgets that don't read the tokens.
           Targeted by stable id hooks so no Vue/asset rebuild is needed. */
        /* Home-page stats chart (Vue / Unovis) */
        html[data-theme="light"] #app-stats,
        html[data-theme="light"] #graph-component,
        html[data-theme="light"] #graph-component nav { background: var(--bg); }
        /* VisualisationApp wraps the whole block in #vueApp with an INLINE
           `style="background:#000"`. An inline style beats any stylesheet
           rule regardless of specificity, so this is the one that has to be
           !important — it is the black band showing above and below the
           filter buttons while everything inside them is already light. */
        html[data-theme="light"] #vueApp { background: var(--bg) !important; }
        html[data-theme="light"] #graph-component .text-white:not(.app-color-bg) { color: var(--fg); }
        html[data-theme="light"] #graph-component .border-white { border-color: rgba(var(--fg-rgb), 0.3); }
        html[data-theme="light"] #graph-component svg text,
        html[data-theme="light"] #graph-component tspan { fill: var(--fg); }
        /* Home-page statistics panel (Vue NumbersComponent). It hardcodes
           `background:#000; color:#FFF` on the section, so in light mode the
           whole block stayed a black slab below the filter bar. Flipping the
           section is enough: every label and paragraph inside inherits its
           colour, and the figures picked out in the donut palette are inline
           data colours that should survive the theme. */
        html[data-theme="light"] #stats-component {
            background: var(--bg) !important;
            color: var(--fg) !important;
        }

        /* Place-of-prosecution map (Vue StateMapComponent), same problem.
           Only the chrome is flipped. The grid cells are painted from a data
           ramp that runs to near-black at the low end, so the abbreviations
           sitting on them, the cell borders and the legend bar keep their
           light-on-dark treatment — recolouring those would make the sparsest
           states unreadable on exactly the cells that are hardest to read
           already. Scoped component CSS needs !important to beat. */
        html[data-theme="light"] #state-map-component { background: var(--bg) !important; }
        html[data-theme="light"] #state-map-component .state-map-eyebrow {
            color: var(--fg) !important;
            border-bottom-color: var(--fg) !important;
        }
        html[data-theme="light"] #state-map-component .state-map-title { color: var(--fg) !important; }
        html[data-theme="light"] #state-map-component .state-map-lead { color: rgba(var(--fg-rgb), 0.85) !important; }
        html[data-theme="light"] #state-map-component .state-map-sub { color: rgba(var(--fg-rgb), 0.55) !important; }
        html[data-theme="light"] #state-map-component .state-map-legend { color: rgba(var(--fg-rgb), 0.6) !important; }
        /* The icon ships as black line-art and the component forces it white
           with `brightness(0) invert(1)`. Dropping the invert rather than
           stacking a second one puts it back to black, which is what a light
           background wants. */
        html[data-theme="light"] #state-map-component .state-map-icon {
            filter: brightness(0) !important;
        }

        /* Mobile slide-down menu */
        html[data-theme="light"] #mobile-menu { background: var(--bg); }
        html[data-theme="light"] #mobile-menu a { color: var(--fg); }

        /* Prisoner database (compiled Vue app): the app hardcodes a black
           canvas and white borders/text/icons. Flip them via its stable
           hooks (#prisoners-page, .prisoner, #filters …); !important where
           we must beat inline styles or compiled scoped-CSS. */
        html[data-theme="light"] #prisoners-page,
        html[data-theme="light"] #prisoners-page .bg-black { background: var(--bg) !important; color: var(--fg); }
        html[data-theme="light"] #prisoners-page .text-white { color: var(--fg); }
        html[data-theme="light"] #prisoners-page .prisoner { border-color: rgba(var(--fg-rgb),0.8); }
        html[data-theme="light"] #prisoners-page .border-white { border-color: rgba(var(--fg-rgb),0.55) !important; }
        html[data-theme="light"] #prisoners-page h2 a {
            color: var(--fg) !important;
            border-bottom-color: rgba(var(--fg-rgb),0.25) !important;
        }
        html[data-theme="light"] #prisoners-page .link-img svg,
        html[data-theme="light"] #prisoners-page .meta svg { fill: var(--fg); stroke: var(--fg); }
        html[data-theme="light"] #prisoners-page .currentImprisonment { border-color: rgba(var(--fg-rgb),0.8); }
        html[data-theme="light"] #prisoners-page .clear-filters-btn {
            color: var(--fg); border-color: rgba(var(--fg-rgb),0.35);
        }
        html[data-theme="light"] #prisoners-page .clear-filters-btn:hover {
            border-color: var(--fg); background: rgba(var(--fg-rgb),0.08);
        }
        html[data-theme="light"] #prisoners-page .results-count { color: rgba(var(--fg-rgb),0.55); }
        html[data-theme="light"] #prisoners-page .load-more-indicator { color: rgba(var(--fg-rgb),0.6); }
        html[data-theme="light"] #prisoners-page .load-more-spinner {
            border-color: rgba(var(--fg-rgb),0.2); border-top-color: var(--fg);
        }
        /* The no-photo placeholder SVG is white line-art — invert it on light. */
        html[data-theme="light"] #prisoners-page img[src*="no-image-available"] { filter: invert(1); }
        /* White filter/search boxes disappear on a white page — give them an edge. */
        html[data-theme="light"] #prisoners-page #filters .ant-select-selector,
        html[data-theme="light"] #prisoners-page input#prisoner-search,
        html[data-theme="light"] #prisoners-page .ant-radio-button-wrapper {
            border: 1px solid rgba(var(--fg-rgb),0.35) !important;
        }
    </style>
    <script>
        /* Set the theme before first paint (no flash). */
        (function () {
            try {
                var t = localStorage.getItem('nppc-theme');
                if (t !== 'light' && t !== 'dark') {
                    t = (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) ? 'light' : 'dark';
                }
                if (t === 'light') { document.documentElement.setAttribute('data-theme', 'light'); }
            } catch (e) {}
        })();
    </script>
    @yield('head')
</head>


<body class="page-{{request()->segment(1)}} @if ($isHome) home-page @endif">

<a href="#main-content" class="skip-to-content">Skip to content</a>

{{-- Page transition overlay --}}
<div id="page-transition" style="position:fixed; inset:0; background:#000; z-index:999999; opacity:1; pointer-events:none; transition:opacity 0.4s ease;"></div>

@include('layout.nav_desktop')
@include('layout.nav_mobile')
@include('layout.theme_toggle')

@if($isHome)
    <div id="main-content">
        @yield('body')
    </div>
@elseif(request()->is('nppc-quiz', 'youth', 'repression-in-america', 'repression-videos'))
    {{-- Full-bleed landing pages: their heroes/bands span the full viewport
         width and each page self-constrains its own text columns, so they
         render outside the width-limiting .container wrapper (like home). --}}
    <main id="main-content">
        @yield('body')
    </main>
@else
    <main id="main-content" class="container">
        @yield('body')
    </main>
@endif

@yield('footer')

@include('layout.scrolltop')

{{-- Footer variant: pages may opt into the legacy "STAY INFORMED" footer with
     @section('footer-style', 'alt'); everything else gets the default footer. --}}
@php $__footerView = trim($__env->yieldContent('footer-style')) === 'alt' ? 'layout.footer_alt' : 'layout.footer'; @endphp
@include($__footerView)
<script src="/js/timeline.js"></script>
<script src="/js/nav.js"></script>
@livewireScripts

<script>
    Livewire.on('open-payment-tab', url => {
        window.open(url, '_blank');
    });

    // Page transition: fade in on load, fade out on navigate
    (function() {
        var overlay = document.getElementById('page-transition');
        if (!overlay) return;

        // Fade in: remove black overlay as soon as the DOM is ready,
        // so the page isn't gated on slow assets like the hero video.
        function revealPage() {
            setTimeout(function() { overlay.style.opacity = '0'; }, 50);
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', revealPage);
        } else {
            revealPage();
        }

        // Fade out: show black overlay before navigating
        document.addEventListener('click', function(e) {
            var link = e.target.closest('a[href]');
            if (!link) return;

            var href = link.getAttribute('href');
            // Skip external links, anchors, javascript, new tabs, admin links, and same-page query changes
            if (!href || href.startsWith('#') || href.startsWith('javascript') ||
                href.startsWith('http') || href.startsWith('/admin') ||
                link.target === '_blank' || e.ctrlKey || e.metaKey ||
                link.hasAttribute('data-no-fade')) return;

            // Skip fade for same-page navigation (query params only)
            var currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
            var linkUrl = new URL(href, window.location.origin);
            var linkPath = linkUrl.pathname.replace(/\/+$/, '') || '/';
            if (linkPath === currentPath) return;

            e.preventDefault();
            overlay.style.opacity = '1';
            setTimeout(function() { window.location.href = href; }, 400);
        });
    })();
</script>
</body>

</html>
