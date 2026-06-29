@php
    $activePetitions = \App\Models\Petition::where('published', true)
        ->withCount('signatures')
        ->orderByDesc('created_at')
        ->limit(3)
        ->get();
@endphp
@if ($activePetitions->isNotEmpty())
<section style="padding: 64px 0 32px;">
    <div style="display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 24px; gap: 16px; flex-wrap: wrap;">
        <h2 style="font-size: 2rem; font-weight: 900; color: var(--fg); margin: 0;">Active Petitions</h2>
        <a href="/petitions" style="font-size: 13px; font-weight: 700; color: var(--accent); text-decoration: none; text-transform: uppercase; letter-spacing: 0.08em;">View all &rsaquo;</a>
    </div>

    <style>
        .home-pet-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .home-pet-card { display: flex; flex-direction: column; background: rgba(var(--fg-rgb),0.02); border: 1px solid rgba(var(--fg-rgb),0.08); border-radius: 12px; overflow: hidden; text-decoration: none; transition: border-color 0.15s, transform 0.15s; }
        .home-pet-card:hover { border-color: var(--accent); transform: translateY(-2px); }
        .home-pet-img { aspect-ratio: 16 / 9; background: var(--surface-2) center/cover no-repeat; }
        .home-pet-body { padding: 18px; display: flex; flex-direction: column; gap: 10px; flex: 1; }
        .home-pet-title { font-size: 1.05rem; font-weight: 800; color: var(--fg); line-height: 1.3; }
        .home-pet-bar { height: 5px; background: rgba(var(--fg-rgb),0.08); border-radius: 3px; overflow: hidden; margin-top: auto; }
        .home-pet-fill { height: 100%; background: var(--accent); }
        .home-pet-stats { font-size: 12px; color: rgba(var(--fg-rgb),0.5); display: flex; justify-content: space-between; }
        .home-pet-stats strong { color: var(--fg); }
        @media (max-width: 900px) { .home-pet-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="home-pet-grid">
        @foreach ($activePetitions as $petition)
            @php
                $pct = $petition->signature_goal > 0
                    ? min(100, round(($petition->signatures_count / max(1, $petition->signature_goal)) * 100, 1))
                    : 0;
            @endphp
            <a class="home-pet-card" href="/petition/{{ $petition->slug }}">
                <div class="home-pet-img" @if($petition->image) style="background-image: url('{{ $petition->image_url }}');" @endif></div>
                <div class="home-pet-body">
                    <div class="home-pet-title">{{ $petition->title }}</div>
                    <div class="home-pet-bar"><div class="home-pet-fill" style="width: {{ $pct }}%"></div></div>
                    <div class="home-pet-stats">
                        <span><strong>{{ number_format($petition->signatures_count) }}</strong> signed</span>
                        <span>{{ number_format($petition->signature_goal) }} goal</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</section>
@endif
