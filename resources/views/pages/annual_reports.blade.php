@php
    use App\Models\AnnualReport;
    /** @var AnnualReport[] $reports */
@endphp

@extends('app')

@section('title', 'Annual Report | NPPC')

@section('head')
<style>
    /* Explicit grid + spacing (the compiled Tailwind bundle is missing the
       gap-* utilities, which fused the tiles into one solid band). Tiles are
       deliberately narrower than their columns so space separates each one. */
    /* The compiled bundle is also missing the plain text-6xl utility, so the
       heading needs an explicit size. */
    .ar-heading { font-size: clamp(2.6rem, 5vw, 3.75rem); line-height: 1.05; margin: 48px 0 64px; }
    .ar-grid { display: grid; grid-template-columns: 1fr; gap: 64px 72px; padding-bottom: 96px; }
    @media (min-width: 700px) { .ar-grid { grid-template-columns: 1fr 1fr; } }
    @media (min-width: 1024px) { .ar-grid { grid-template-columns: 1fr 1fr 1fr; } }
    .ar-item { max-width: 320px; margin: 0 auto; width: 100%; }
    .ar-cover { display: block; width: 100%; aspect-ratio: 840 / 1148; border-radius: 4px; overflow: hidden;
                background: #14141c center / cover no-repeat; box-shadow: 0 18px 50px rgba(0,0,0,0.35), 0 0 0 1px rgba(var(--fg-rgb),0.08);
                transition: transform 0.3s cubic-bezier(0.22,1,0.36,1), box-shadow 0.3s; }
    a.ar-cover:hover { transform: translateY(-6px); box-shadow: 0 30px 70px rgba(0,0,0,0.5), 0 0 0 1px rgba(86,96,254,0.5); }
    .ar-cover--empty { display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.35); font-size: 14px; }
    .ar-title { font-size: 1.2rem; color: var(--fg); display: block; text-align: center; text-decoration: none; }
    a.ar-title:hover { color: var(--accent); }
    .ar-note { display: block; text-align: center; font-size: 12px; letter-spacing: 0.1em; text-transform: uppercase; color: rgba(var(--fg-rgb),0.45); margin-top: 6px; }
    @media (prefers-reduced-motion: reduce) { .ar-cover { transition: none; } }
</style>
@endsection

@section('body')
    <main class="container">
        <div class="line mt-8"></div>
        <h1 class="ar-heading">Annual Reports</h1>
        @if (count($reports) > 0)
        <div class="ar-grid">
            @foreach($reports as $report)
                <div class="ar-item">
                    @if($report->file)
                        <a class="ar-cover" href="/storage/{{ $report->file }}" @if($report->image) style="background-image:url('/storage/{{ $report->image }}');" @endif></a>
                    @elseif($report->image)
                        <div class="ar-cover" style="background-image:url('/storage/{{ $report->image }}');"></div>
                    @else
                        <div class="ar-cover ar-cover--empty">Coming soon</div>
                    @endif
                    <div class="line my-4"></div>
                    @if($report->file)
                        <a class="ar-title" href="/storage/{{ $report->file }}">{{ $report->title }}</a>
                    @else
                        <span class="ar-title">{{ $report->title }}</span>
                        <span class="ar-note">Full report coming soon</span>
                    @endif
                </div>
            @endforeach
        </div>
        @else
            @include('sections.coming-soon', ['message' => 'Our annual reports will be posted here soon. Please check back shortly.'])
        @endif
    </main>
@endsection
