@php
$isHome = request()->segment(1) == ''
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <link rel="stylesheet" href="/vue/app.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="/vue/app.js" defer></script>
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
    </style>
    @yield('head')
</head>


<body class="page-{{request()->segment(1)}} @if ($isHome) home-page @endif">

{{-- Page transition overlay --}}
<div id="page-transition" style="position:fixed; inset:0; background:#000; z-index:999999; opacity:1; pointer-events:none; transition:opacity 0.4s ease;"></div>

@include('layout.nav_desktop')
@include('layout.nav_mobile')

@if($isHome)
    @yield('body')
@else
    <main class="container">
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
