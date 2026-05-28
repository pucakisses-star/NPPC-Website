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

    <title>@yield('title')</title>
    <link href="/app.css?v={{ @filemtime(public_path('app.css')) }}" rel="stylesheet"/>
    <link href="/styles.css?v={{ @filemtime(public_path('styles.css')) }}" rel="stylesheet"/>
    <link href="/fontawesome/css/all.min.css" rel="stylesheet"/>
    <link href="/fontawesome/css/thin.css" rel="stylesheet"/>
    <link href="/fonts/verlag/stylesheet.css" rel="stylesheet"/>
    <link href="/fonts/flood-std.css" rel="stylesheet"/>
    <link href="/style/nav.css" rel="stylesheet"/>
    <link href="/style/basics.css" rel="stylesheet"/>
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
            outline: 2px solid #5660fe;
            outline-offset: 2px;
        }
        /* Skip-to-content link — visible only on keyboard focus */
        .skip-to-content {
            position: absolute; left: -9999px; top: 8px;
            background: #5660fe; color: #fff; padding: 10px 16px;
            border-radius: 4px; font-weight: 700; z-index: 999999;
            text-decoration: none;
        }
        .skip-to-content:focus { left: 8px; }

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
    @yield('head')
</head>


<body class="page-{{request()->segment(1)}} @if ($isHome) home-page @endif">

<a href="#main-content" class="skip-to-content">Skip to content</a>

{{-- Page transition overlay --}}
<div id="page-transition" style="position:fixed; inset:0; background:#000; z-index:999999; opacity:1; pointer-events:none; transition:opacity 0.4s ease;"></div>

@include('layout.nav_desktop')
@include('layout.nav_mobile')

@if($isHome)
    <div id="main-content">
        @yield('body')
    </div>
@else
    <main id="main-content" class="container">
        @yield('body')
    </main>
@endif

@yield('footer')

@include('layout.scrolltop')

@include('layout.footer')
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
