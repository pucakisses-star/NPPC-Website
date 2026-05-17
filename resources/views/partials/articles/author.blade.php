@php use App\Models\Article; @endphp
@php
    /**
     * @var Article $article
     */
@endphp


@if(!empty($article->author))
    {{-- Inline styles instead of Tailwind utilities — the compiled
         CSS bundle on prod is missing gap-*, w-8, h-8, rounded-full,
         object-cover, so the avatar collapsed and "By" jammed into
         the author name. --}}
    <div style="display: flex; align-items: center; gap: 16px; font-size: 14px; color: #fff;">
        <img
            src="{{ $article->author['avatar_url'] }}"
            alt="{{ $article->author['name'] }}"
            style="width: 32px; height: 32px; border-radius: 9999px; object-fit: cover; flex-shrink: 0;"
        />

        <div style="display: flex; flex-direction: column;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="color: rgba(255,255,255,0.7);">By</span>
                <span style="font-weight: 600; color: #fff;">{{ $article->author['name'] }}</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; color: rgba(255,255,255,0.5); margin-top: 2px;">
                <svg xmlns="http://www.w3.org/2000/svg" style="height: 16px; width: 16px; color: rgba(255,255,255,0.45);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Published {{ $article->published_at->format('M j, Y') }}</span>
            </div>
        </div>

    </div>
@endif
