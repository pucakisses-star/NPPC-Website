@extends('app')

@section('head')
<style>
    @media (max-width: 768px) {
        .search-h1 { font-size: 2rem !important; margin-top: 32px !important; }
        .search-form { flex-direction: column !important; gap: 12px !important; }
        .search-form button { padding: 14px !important; }
    }
</style>
@endsection

@section('body')
    <h1 class="search-h1" style="font-size: 3rem; font-weight: 300; margin-top: 48px; margin-bottom: 16px;">Search</h1>
    <div class="line"></div>

    <div style="max-width: 700px; margin: 32px 0;">
        <form class="search-form" action="/search" method="GET" style="display: flex; gap: 8px;">
            <input type="text" name="q" value="{{ $query }}" placeholder="Search..." style="flex: 1; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); border-radius: 6px; padding: 12px 16px; color: #fff; font-size: 16px; outline: none;">
            <button type="submit" style="background: #5660fe; color: #fff; border: none; border-radius: 6px; padding: 12px 24px; font-size: 14px; font-weight: 700; cursor: pointer; text-transform: uppercase;">Search</button>
        </form>
    </div>

    @if($query)
        <div style="color: rgba(255,255,255,0.5); margin-bottom: 32px; font-size: 15px;">
            {{ count($results) }} result{{ count($results) !== 1 ? 's' : '' }} for "<span style="color: #fff; font-weight: 600;">{{ $query }}</span>"
        </div>

        @if(count($results) > 0)
            <div style="margin-bottom: 48px;">
                @foreach($results as $result)
                    <a href="{{ $result['url'] }}" style="display: block; text-decoration: none; padding: 20px 0; border-bottom: 1px solid rgba(255,255,255,0.1);">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 6px;">
                            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #5660fe; background: rgba(86,96,254,0.1); padding: 2px 8px; border-radius: 4px;">{{ $result['type'] }}</span>
                        </div>
                        <div style="font-size: 18px; font-weight: 600; color: #fff; margin-bottom: 4px;">{{ $result['title'] }}</div>
                        @if($result['excerpt'])
                            <div style="font-size: 14px; color: rgba(255,255,255,0.5); line-height: 1.5;">{{ $result['excerpt'] }}...</div>
                        @endif
                    </a>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 60px 0; color: rgba(255,255,255,0.4);">
                <div style="font-size: 48px; margin-bottom: 16px;">&#128269;</div>
                <div style="font-size: 18px;">No results found for "{{ $query }}"</div>
                <div style="font-size: 14px; margin-top: 8px;">Try different keywords or browse using the navigation menu.</div>
            </div>
        @endif
    @endif
@endsection
