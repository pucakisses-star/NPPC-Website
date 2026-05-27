@php
    /** @var \App\Models\ArchiveRecord $record */
    $isPdf = $record->file && str_ends_with(strtolower($record->file), '.pdf');
@endphp
@extends('app')

@section('head')
<style>
    .av-wrap { max-width: 1400px; margin: 0 auto; padding: 0 24px 64px; }
    .av-bar { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 24px 0 12px; flex-wrap: wrap; }
    .av-back { display: inline-flex; align-items: center; gap: 8px; color: rgba(255,255,255,0.6); text-decoration: none; font-size: 13px; font-weight: 600; }
    .av-back:hover { color: #5660fe; }
    .av-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .av-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border: 1px solid rgba(255,255,255,0.2); border-radius: 6px; color: #fff; text-decoration: none; font-size: 13px; font-weight: 700; transition: background 0.15s, border-color 0.15s; }
    .av-btn:hover { border-color: #5660fe; background: rgba(86,96,254,0.1); color: #fff; }
    .av-btn-primary { background: #5660fe; border-color: #5660fe; }
    .av-btn-primary:hover { background: #4850e6; border-color: #4850e6; }
    .av-title { font-size: 1.5rem; font-weight: 800; color: #fff; line-height: 1.2; margin: 0 0 8px; }
    .av-meta { font-size: 13px; color: rgba(255,255,255,0.55); margin-bottom: 18px; }
    .av-meta strong { color: rgba(255,255,255,0.85); font-weight: 600; }
    .av-viewer { width: 100%; height: 80vh; min-height: 600px; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; overflow: hidden; background: #1a1a1a; }
    .av-viewer iframe { width: 100%; height: 100%; border: 0; }
    .av-fallback { padding: 60px 24px; text-align: center; color: rgba(255,255,255,0.6); }
    .av-fallback p { margin-bottom: 16px; }
    .av-desc { margin-top: 32px; max-width: 800px; font-size: 15px; color: rgba(255,255,255,0.7); line-height: 1.7; }
    .av-desc h2 { font-size: 1.1rem; font-weight: 800; color: #fff; margin: 0 0 12px; }
    @media (max-width: 768px) {
        .av-wrap { padding: 0 16px 48px; }
        .av-viewer { height: 70vh; min-height: 480px; }
        .av-title { font-size: 1.2rem; }
    }
</style>
@endsection

@section('body')
<main class="av-wrap">
    <div class="av-bar">
        <a class="av-back" href="/archive">&lsaquo; Back to Archive</a>
        <div class="av-actions">
            <a class="av-btn" href="{{ $record->file_url }}" download>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download
            </a>
            <a class="av-btn av-btn-primary" href="{{ $record->file_url }}" target="_blank" rel="noopener">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Open in new tab
            </a>
        </div>
    </div>

    <h1 class="av-title">{{ $record->title }}</h1>
    <div class="av-meta">
        @if ($record->authors)<span><strong>{{ $record->authors }}</strong></span>@endif
        @if ($record->year) &middot; <span>{{ $record->year }}</span>@endif
        @if ($record->publisher) &middot; <span>{{ $record->publisher }}</span>@endif
        @if ($record->collection) &middot; <span>{{ $record->collection }}</span>@endif
    </div>

    @if ($isPdf)
        <div class="av-viewer">
            <iframe src="{{ $record->file_url }}#view=FitH" title="{{ $record->title }}" loading="lazy"></iframe>
        </div>
    @else
        <div class="av-viewer">
            <div class="av-fallback">
                <p>This file isn't a PDF.</p>
                <a class="av-btn av-btn-primary" href="{{ $record->file_url }}" target="_blank" rel="noopener">Open file in new tab</a>
            </div>
        </div>
    @endif

    @if ($record->description)
        <div class="av-desc">
            <h2>About this record</h2>
            <p>{{ $record->description }}</p>
        </div>
    @endif
</main>
@endsection
