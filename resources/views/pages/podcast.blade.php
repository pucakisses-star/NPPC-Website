@extends('app')

@section('head')
<style>
    @media (max-width: 768px) {
        .podcast-wrap { padding: 24px 16px 48px !important; }
        .podcast-wrap h1 { font-size: 1.8rem !important; margin-bottom: 12px !important; }
        .podcast-wrap > p { font-size: 16px !important; margin-bottom: 24px !important; }
    }
</style>
@endsection

@section('body')
    <div class="podcast-wrap" style="max-width: 900px; margin: 0 auto; padding: 48px 24px 80px;">
        <h1 style="font-size: 3rem; font-weight: 900; color: #fff; margin-bottom: 16px;">Podcast</h1>
        <p style="font-size: 18px; color: rgba(255,255,255,0.6); line-height: 1.7; margin-bottom: 40px; max-width: 600px;">
            Listen to conversations about political prisoners, civil liberties, and the fight for justice.
        </p>
        <div class="line" style="margin-bottom: 40px;"></div>

        @include('sections.podcast-player', ['episodes' => $episodes])

        @if($episodes->isEmpty())
            <div style="text-align: center; padding: 60px 0; color: rgba(255,255,255,0.4);">
                No episodes yet. Check back soon!
            </div>
        @endif
    </div>
@endsection
