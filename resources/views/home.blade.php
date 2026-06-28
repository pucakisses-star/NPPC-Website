@php use App\Models\SiteSetting; @endphp
@extends('app')

@section('body')
    @php
        $heroHeadline = SiteSetting::get('hero_headline', 'Justice');
        $heroSubheadline = SiteSetting::get('hero_subheadline', 'No matter what');
        $heroHeadlineSize = SiteSetting::get('hero_headline_size', '8');
        $heroSubheadlineSize = SiteSetting::get('hero_subheadline_size', '5');
        $heroHeight = SiteSetting::get('hero_height', '100');
        $heroOverlay = SiteSetting::get('hero_overlay_opacity', '30');
        $heroVideoMp4 = SiteSetting::get('hero_video_mp4');
        $heroVideoWebm = SiteSetting::get('hero_video_webm');
        $articlesLimit = (int) SiteSetting::get('articles_limit', '5');
    @endphp

    {{-- Hero Section --}}
    <div class="relative hero-wrap" style="height: {{ $heroHeight }}vh;">
        <video autoplay loop muted playsinline class="hero-video absolute inset-0 w-full h-full object-cover">
            <source src="{{ $heroVideoMp4 ? asset('storage/' . $heroVideoMp4) : '/videos/home/video.mp4' }}" type="video/mp4">
            <source src="{{ $heroVideoWebm ? asset('storage/' . $heroVideoWebm) : '/videos/home/video.webm' }}" type="video/webm">
        </video>
        <div class="video-bg-fade"></div>
        <div class="absolute inset-0 bg-black" style="opacity: {{ (int)$heroOverlay / 100 }};"></div>
        <div class="absolute inset-0 flex items-end hero-text-wrap" style="z-index: 2; padding: 0 40px 40px;">
            <div class="text-white font-bold">
                <span class="hero-headline hero-reveal" style="--hero-headline-size: {{ $heroHeadlineSize }}rem; font-size: var(--hero-headline-size); line-height: 1.1;">{{ $heroHeadline }}</span>
                <span class="flood-std block hero-subheadline hero-reveal hero-reveal--sub" style="--hero-sub-size: {{ $heroSubheadlineSize }}rem; font-size: var(--hero-sub-size); line-height: 1.2;">{{ $heroSubheadline }}</span>
            </div>
        </div>
    </div>
    <style>
        @media (max-width: 768px) {
            .hero-text-wrap { padding: 0 20px 24px !important; }
            .hero-headline { font-size: clamp(2.5rem, 14vw, calc(var(--hero-headline-size) * 0.55)) !important; }
            .hero-subheadline { font-size: clamp(1.5rem, 9vw, calc(var(--hero-sub-size) * 0.55)) !important; }
        }

        /* Both lines fade in, rise up, and sharpen from a blur (staggered);
           then an accent bar sweeps in beneath "Justice", left to right, once
           the headline has settled. (Inspired by the civicprofile.us headline.) */
        .hero-headline { position: relative; display: inline-block; font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; }
        .hero-headline::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0.04em;
            width: 100%;
            height: 0.08em;
            background: #ff5851;
            transform: scaleX(0);
            transform-origin: left center;
            animation: heroBarSweep 0.7s cubic-bezier(0.22, 1, 0.36, 1) 1s forwards;
            will-change: transform;
        }
        @keyframes heroBarSweep { to { transform: scaleX(1); } }

        .hero-reveal {
            opacity: 0;
            animation: heroReveal 0.9s cubic-bezier(0.22, 1, 0.36, 1) both;
            animation-delay: 0.15s;
            will-change: transform, filter, opacity;
        }
        .hero-reveal--sub { animation-delay: 0.45s; }
        @keyframes heroReveal {
            0%   { opacity: 0; transform: translateY(22px); filter: blur(8px); }
            55%  { filter: blur(0); }
            100% { opacity: 1; transform: translateY(0);    filter: blur(0); }
        }
        @media (prefers-reduced-motion: reduce) {
            .hero-reveal { animation: none; opacity: 1; transform: none; filter: none; }
            .hero-headline::after { animation: none; transform: scaleX(1); }
        }

        /* Hero background video fades in once it actually has a frame to show
           (triggered from JS on ready), over a black backdrop so there is no
           flash before it appears. */
        .hero-wrap { background: #000; }
        .hero-video { opacity: 0; transition: opacity 1.2s ease; will-change: opacity; }
        .hero-video.is-loaded { opacity: 1; }
        @media (prefers-reduced-motion: reduce) {
            .hero-video { opacity: 1; transition: none; }
        }
    </style>

    <script>
        // Fade the hero video in only once it can render a frame, with a safety
        // timeout so it always becomes visible (cached video, blocked autoplay,
        // or events that never fire).
        (function () {
            var v = document.querySelector('.hero-video');
            if (! v) return;
            var show = function () { v.classList.add('is-loaded'); };
            if (v.readyState >= 2) {
                show();
            } else {
                ['loadeddata', 'canplay', 'playing'].forEach(function (e) {
                    v.addEventListener(e, show, { once: true });
                });
            }
            setTimeout(show, 2500);
        })();
    </script>

    <div class="container">
        {{-- Articles --}}
        @if(SiteSetting::get('articles_enabled', '1') === '1')
            <livewire:articles-grid :limit="$articlesLimit" />
        @endif

        {{-- Featured upcoming event (renders only when one exists) --}}
        @include('sections.featured-event')

        {{-- Featured Authors --}}
        @include('sections.featured-authors')

        {{-- Newsletter signup (mid-page CTA) --}}
        @include('sections.newsletter-signup')

        {{-- Stats Visualisation (also renders the Prosecutions-by-State map) --}}
        @if(SiteSetting::get('visualisation_enabled', '1') === '1')
            <div id="app-stats"></div>
        @endif

        {{-- Individual Profiles callout --}}
        @include('sections.individual-profiles')

        {{-- Callout (donation CTA) --}}
        @if(SiteSetting::get('callout_enabled', '1') === '1')
            @include('sections.callout')
        @endif

        {{-- Quotes --}}
        @if(SiteSetting::get('quotes_enabled', '1') === '1')
            @include('sections.quotes')
        @endif
    </div>

    {{-- Gallery --}}
    @if(SiteSetting::get('gallery_enabled', '1') === '1')
        <div id="app-gallery"></div>
    @endif

@endsection
