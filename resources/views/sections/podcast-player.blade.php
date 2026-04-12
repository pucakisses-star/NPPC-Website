@php
/** @var \App\Models\PodcastEpisode[] $episodes */
@endphp

@if($episodes->isNotEmpty())
<div class="podcast-section" style="margin: 32px 0;">
    @php $featured = $episodes->first(); @endphp

    {{-- Featured Episode Player --}}
    <div style="border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 24px; margin-bottom: 16px;">
        <div style="display: flex; gap: 20px; align-items: flex-start;">
            @if($featured->cover_image)
                <img src="{{ Storage::url($featured->cover_image) }}" alt="{{ $featured->title }}" style="width: 120px; height: 120px; border-radius: 8px; object-fit: cover; flex-shrink: 0;">
            @else
                <div style="width: 120px; height: 120px; border-radius: 8px; background: linear-gradient(135deg, #5660fe 0%, #1a1040 100%); flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="rgba(255,255,255,0.3)" viewBox="0 0 24 24"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55C7.79 13 6 14.79 6 17s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>
                </div>
            @endif
            <div style="flex: 1;">
                <div style="font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 4px;">
                    {{ $featured->episode_number ? 'Episode '.$featured->episode_number.': ' : '' }}{{ $featured->title }}
                </div>
                @if($featured->show_name)
                    <div style="font-size: 14px; color: rgba(255,255,255,0.5); margin-bottom: 12px;">{{ $featured->show_name }}</div>
                @endif

                @if($featured->embed_code)
                    <div style="margin-top: 8px;">{!! $featured->embed_code !!}</div>
                @elseif($featured->audio_url)
                    <audio controls style="width: 100%; margin-top: 8px;">
                        <source src="{{ $featured->audio_url }}" type="audio/mpeg">
                    </audio>
                @endif

                @if($featured->duration)
                    <div style="font-size: 13px; color: rgba(255,255,255,0.4); margin-top: 8px; text-align: right;">{{ $featured->duration }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Episode List --}}
    @if($episodes->count() > 1)
        @foreach($episodes->skip(1) as $episode)
            <div style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,0.06); cursor: pointer; transition: background 0.15s;" onmouseenter="this.style.background='rgba(255,255,255,0.03)'" onmouseleave="this.style.background='transparent'">
                @if($episode->cover_image)
                    <img src="{{ Storage::url($episode->cover_image) }}" alt="{{ $episode->title }}" style="width: 36px; height: 36px; border-radius: 4px; object-fit: cover; flex-shrink: 0;">
                @else
                    <div style="width: 36px; height: 36px; border-radius: 4px; background: #1a1a2e; flex-shrink: 0;"></div>
                @endif
                <div style="flex: 1; font-size: 14px; color: rgba(255,255,255,0.7);">
                    {{ $episode->episode_number ? 'Episode '.$episode->episode_number.': ' : '' }}{{ $episode->title }}
                </div>
                <div style="font-size: 13px; color: rgba(255,255,255,0.35); flex-shrink: 0;">{{ $episode->duration }}</div>
            </div>
        @endforeach
    @endif
</div>
@endif
