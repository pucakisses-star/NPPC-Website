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
        <video autoplay loop muted playsinline class="absolute inset-0 w-full h-full object-cover">
            <source src="{{ $heroVideoMp4 ? asset('storage/' . $heroVideoMp4) : '/videos/home/video.mp4' }}" type="video/mp4">
            <source src="{{ $heroVideoWebm ? asset('storage/' . $heroVideoWebm) : '/videos/home/video.webm' }}" type="video/webm">
        </video>
        <div class="video-bg-fade"></div>
        <div class="absolute inset-0 bg-black" style="opacity: {{ (int)$heroOverlay / 100 }};"></div>
        <div class="absolute inset-0 flex items-end hero-text-wrap" style="z-index: 2; padding: 0 40px 40px;">
            <div class="text-white font-bold">
                <span class="block hero-headline" style="--hero-headline-size: {{ $heroHeadlineSize }}rem; font-size: var(--hero-headline-size); line-height: 1.1;">{{ $heroHeadline }}</span>
                <span class="flood-std block hero-subheadline" style="--hero-sub-size: {{ $heroSubheadlineSize }}rem; font-size: var(--hero-sub-size); line-height: 1.2;">{{ $heroSubheadline }}</span>
            </div>
        </div>
    </div>
    <style>
        @media (max-width: 768px) {
            .hero-text-wrap { padding: 0 20px 24px !important; }
            .hero-headline { font-size: clamp(2.5rem, 14vw, calc(var(--hero-headline-size) * 0.55)) !important; }
            .hero-subheadline { font-size: clamp(1.5rem, 9vw, calc(var(--hero-sub-size) * 0.55)) !important; }
        }
    </style>

    <div class="container">
        {{-- Articles --}}
        @if(SiteSetting::get('articles_enabled', '1') === '1')
            <livewire:articles-grid :limit="$articlesLimit" />
        @endif

        {{-- Featured Authors --}}
        @include('sections.featured-authors')

        {{-- Callout --}}
        @if(SiteSetting::get('callout_enabled', '1') === '1')
            @include('sections.callout')
        @endif

        {{-- Stats Visualisation (also renders the Prosecutions-by-State map) --}}
        @if(SiteSetting::get('visualisation_enabled', '1') === '1')
            <div id="app-stats"></div>
        @endif

        {{-- Active Petitions --}}
        @include('sections.active-petitions')

        {{-- Newsletter signup (mid-page CTA) --}}
        @include('sections.newsletter-signup')

        {{-- Individual Profiles callout --}}
        @include('sections.individual-profiles')

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
