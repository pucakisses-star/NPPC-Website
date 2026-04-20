@php
    $partners = \App\Models\Partner::published()->orderBy('sort_order')->get();
    $total = $partners->count();
    $halfPoint = (int) ceil($total / 2);
    $topRow = $total > 7 ? $partners->slice(0, $halfPoint) : $partners;
    $bottomRow = $total > 7 ? $partners->slice($halfPoint) : collect();
@endphp

@if($total >= 2)
<div class="partners-section">
    <h2 class="partners-title">Organizational Partners</h2>

    {{-- Top row: moves left --}}
    <div class="partners-row" id="partners-row-top">
        <div class="partners-track partners-track-left">
            @foreach($topRow as $partner)
                <div class="partner-box">
                    @if($partner->logo)
                        <img src="{{ Storage::url($partner->logo) }}" alt="{{ $partner->name }}">
                    @else
                        <span class="partner-initials">{{ strtoupper(substr($partner->name, 0, 2)) }}</span>
                    @endif
                    <div class="partner-name">{{ $partner->name }}</div>
                </div>
            @endforeach
            {{-- Duplicate for seamless loop --}}
            @foreach($topRow as $partner)
                <div class="partner-box">
                    @if($partner->logo)
                        <img src="{{ Storage::url($partner->logo) }}" alt="{{ $partner->name }}">
                    @else
                        <span class="partner-initials">{{ strtoupper(substr($partner->name, 0, 2)) }}</span>
                    @endif
                    <div class="partner-name">{{ $partner->name }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Bottom row: moves right (only shown when > 7 partners) --}}
    @if($bottomRow->isNotEmpty())
        <div class="partners-row" id="partners-row-bottom">
            <div class="partners-track partners-track-right">
                @foreach($bottomRow as $partner)
                    <div class="partner-box">
                        @if($partner->logo)
                            <img src="{{ Storage::url($partner->logo) }}" alt="{{ $partner->name }}">
                        @else
                            <span class="partner-initials">{{ strtoupper(substr($partner->name, 0, 2)) }}</span>
                        @endif
                        <div class="partner-name">{{ $partner->name }}</div>
                    </div>
                @endforeach
                {{-- Duplicate for seamless loop --}}
                @foreach($bottomRow as $partner)
                    <div class="partner-box">
                        @if($partner->logo)
                            <img src="{{ Storage::url($partner->logo) }}" alt="{{ $partner->name }}">
                        @else
                            <span class="partner-initials">{{ strtoupper(substr($partner->name, 0, 2)) }}</span>
                        @endif
                        <div class="partner-name">{{ $partner->name }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<style>
    .partners-section { padding: 80px 0; overflow: hidden; }
    .partners-title { font-size: 2.5rem; font-weight: 900; color: #fff; text-align: center; margin-bottom: 48px; }
    .partners-row { overflow: hidden; margin-bottom: 4px; }
    .partners-track { display: flex; gap: 4px; width: max-content; }
    .partners-track-left { animation: scrollLeft 40s linear infinite; }
    .partners-track-right { animation: scrollRight 40s linear infinite; }
    .partners-row:hover .partners-track { animation-play-state: paused; }

    .partner-box {
        flex: 0 0 180px;
        height: 180px;
        border: 1px solid rgba(255,255,255,0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.02);
        transition: background 0.3s, border-color 0.3s, box-shadow 0.3s;
        position: relative;
        overflow: hidden;
    }
    .partner-box img {
        max-width: 70%;
        max-height: 70%;
        object-fit: contain;
        border-radius: 50%;
        opacity: 0.7;
        transition: opacity 0.3s;
    }
    .partner-box .partner-initials {
        font-size: 1.5rem;
        font-weight: 800;
        color: rgba(255,255,255,0.15);
        transition: color 0.3s;
    }
    .partner-name {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 16px;
        font-size: 13px;
        font-weight: 700;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        background: rgba(0,0,0,0.85);
        opacity: 0;
        transition: opacity 0.3s;
    }
    .partner-box:hover .partner-name { opacity: 1; }
    .partner-box:hover {
        background: rgba(255,255,255,0.06);
        border-color: rgba(255,255,255,0.2);
        box-shadow: 0 0 20px rgba(86,96,254,0.15);
    }
    .partner-box:hover img { opacity: 1; }
    .partner-box:hover .partner-initials { color: rgba(255,255,255,0.5); }

    @@keyframes scrollLeft {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    @@keyframes scrollRight {
        0% { transform: translateX(-50%); }
        100% { transform: translateX(0); }
    }

    @@media (max-width: 768px) {
        .partner-box { flex: 0 0 120px; height: 120px; }
        .partners-title { font-size: 1.8rem; }
        .partner-name { font-size: 11px; padding: 8px; }
    }
</style>
@endif
