@extends('app')

@section('title', 'Active Petitions | NPPC')

@section('head')
<style>
.pix-wrap { max-width: 1200px; margin: 0 auto; padding: 0 24px 96px; }
.pix-hero { padding: 56px 0 24px; }
.pix-hero h1 { font-size: 2.5rem; font-weight: 900; color: var(--fg); line-height: 1.05; margin: 0 0 12px; }
.pix-hero p { font-size: 1.05rem; color: rgba(var(--fg-rgb),0.7); max-width: 720px; line-height: 1.7; margin: 0 0 8px; }
.pix-hero .pix-count { font-size: 14px; color: rgba(var(--fg-rgb),0.45); text-transform: uppercase; letter-spacing: 0.08em; margin-top: 16px; }

.pix-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 32px; }
.pix-card { display: flex; flex-direction: column; background: rgba(var(--fg-rgb),0.02); border: 1px solid rgba(var(--fg-rgb),0.08); border-radius: 12px; overflow: hidden; text-decoration: none; transition: border-color 0.15s, background 0.15s, transform 0.15s; }
.pix-card:hover { border-color: var(--accent); background: rgba(86,96,254,0.04); transform: translateY(-2px); }
.pix-img-box { position: relative; aspect-ratio: 16 / 9; background: var(--surface-2) center/cover no-repeat; }
.pix-img-empty { display: flex; align-items: center; justify-content: center; color: rgba(var(--fg-rgb),0.2); font-size: 12px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; }
.pix-card-body { padding: 20px; display: flex; flex-direction: column; gap: 12px; flex: 1; }
.pix-card-title { font-size: 1.1rem; font-weight: 800; color: var(--fg); line-height: 1.3; }
.pix-card-recipients { font-size: 12px; color: rgba(var(--fg-rgb),0.5); text-transform: uppercase; letter-spacing: 0.04em; }
.pix-card-progress { margin-top: auto; }
.pix-card-bar { height: 6px; background: rgba(var(--fg-rgb),0.08); border-radius: 3px; overflow: hidden; margin-bottom: 6px; }
.pix-card-fill { height: 100%; background: var(--accent); border-radius: 3px; }
.pix-card-progress-text { font-size: 12px; color: rgba(var(--fg-rgb),0.5); display: flex; justify-content: space-between; }
.pix-card-progress-text strong { color: var(--fg); }
.pix-card-cta { display: inline-flex; align-items: center; justify-content: center; gap: 6px; background: var(--accent); color: var(--on-accent); padding: 10px 14px; border-radius: 6px; font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; margin-top: 8px; }
.pix-card:hover .pix-card-cta { background: var(--accent-hover); }
.pix-empty { text-align: center; padding: 96px 24px; color: rgba(var(--fg-rgb),0.5); }

/* Featured petition hero band */
.pfx { display: grid; grid-template-columns: 1.05fr 1fr; border: 1px solid rgba(var(--fg-rgb),0.1); background: rgba(var(--fg-rgb),0.03); border-radius: 12px; overflow: hidden; margin-top: 32px; }
.pfx-img { min-height: 380px; background: var(--surface-2) center/cover no-repeat; }
.pfx-img-empty { display: flex; align-items: center; justify-content: center; color: rgba(var(--fg-rgb),0.2); font-size: 13px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; }
.pfx-body { padding: 36px 40px 32px; display: flex; flex-direction: column; align-items: flex-start; }
.pfx-kicker { display: inline-flex; align-items: flex-start; gap: 7px; font-size: 13px; font-weight: 800; color: var(--fg); }
.pfx-kicker::before { content: ''; width: 7px; height: 7px; margin-top: 2px; background: var(--accent); }
.pfx-title { font-size: clamp(1.6rem, 3vw, 2.5rem); font-weight: 900; color: var(--fg); line-height: 1.12; margin: 14px 0 14px; }
.pfx-sub { font-size: 1.05rem; font-weight: 700; color: rgba(var(--fg-rgb),0.75); line-height: 1.55; max-width: 620px; }
.pfx-meta { margin-top: 14px; font-size: 13px; color: rgba(var(--fg-rgb),0.5); }
.pfx-meta strong { color: var(--fg); }
.pfx-cta { display: inline-flex; align-items: center; gap: 8px; margin-top: auto; padding: 10px 18px; border: 1px solid rgba(var(--fg-rgb),0.35); border-radius: 4px; font-size: 13px; font-weight: 800; color: var(--fg); text-decoration: none; transition: background 0.15s, border-color 0.15s; }
.pfx-cta:hover { background: var(--accent); border-color: var(--accent); color: var(--on-accent); }

@@media (max-width: 900px) { .pix-grid { grid-template-columns: repeat(2, 1fr); } }
@@media (max-width: 900px) {
    .pfx { grid-template-columns: 1fr; }
    .pfx-img { min-height: 220px; aspect-ratio: 16 / 9; }
    .pfx-body { padding: 24px; }
    .pfx-cta { margin-top: 20px; }
}
@@media (max-width: 640px) {
    .pix-wrap { padding: 0 16px 64px; }
    .pix-hero { padding: 32px 0 16px; }
    .pix-hero h1 { font-size: 1.8rem; }
    .pix-grid { grid-template-columns: 1fr; gap: 16px; }
}
</style>
@endsection

@section('body')
<main class="pix-wrap">
    @php $activeTotal = $petitions->total() + (!empty($featured) ? 1 : 0); @endphp
    <div class="pix-hero">
        <h1>Active Petitions</h1>
        <p>Add your name to the campaigns demanding clemency, dropping charges, and accountability for U.S. political prisoners. Every signature is delivered to the named recipients.</p>
        <div class="pix-count">{{ $activeTotal }} active {{ \Illuminate\Support\Str::plural('petition', $activeTotal) }}</div>
    </div>

    @if (!empty($featured))
        <div class="pfx">
            <div class="pfx-img {{ $featured->image ? '' : 'pfx-img-empty' }}"
                 @if($featured->image) style="background-image: url('{{ $featured->image_url }}');" @endif>
                @unless($featured->image) Petition @endunless
            </div>
            <div class="pfx-body">
                <span class="pfx-kicker">Featured Petition</span>
                <h2 class="pfx-title">{{ $featured->title }}</h2>
                @if ($featured->body)
                    <p class="pfx-sub">{{ \Illuminate\Support\Str::limit(trim(strip_tags($featured->body)), 170) }}</p>
                @endif
                <div class="pfx-meta"><strong>{{ number_format($featured->signatures_count) }}</strong> signed &middot; Goal: {{ number_format($featured->signature_goal) }}</div>
                <a class="pfx-cta" href="/petition/{{ $featured->slug }}">Act Now &rarr;</a>
            </div>
        </div>
    @endif

    @if ($petitions->isEmpty() && empty($featured))
        <div class="pix-empty">No petitions are currently active. Check back soon.</div>
    @elseif ($petitions->isNotEmpty())
        <div class="pix-grid">
            @foreach ($petitions as $petition)
                @php
                    $pct = $petition->signature_goal > 0
                        ? min(100, round(($petition->signatures_count / max(1, $petition->signature_goal)) * 100, 1))
                        : 0;
                @endphp
                <a class="pix-card" href="/petition/{{ $petition->slug }}">
                    <div class="pix-img-box {{ $petition->image ? '' : 'pix-img-empty' }}"
                         @if($petition->image) style="background-image: url('{{ $petition->image_url }}');" @endif>
                        @unless($petition->image) Petition @endunless
                    </div>
                    <div class="pix-card-body">
                        <div class="pix-card-title">{{ $petition->title }}</div>
                        @if ($petition->recipients)
                            <div class="pix-card-recipients">To: {{ \Illuminate\Support\Str::limit($petition->recipients, 80) }}</div>
                        @endif
                        <div class="pix-card-progress">
                            <div class="pix-card-bar">
                                <div class="pix-card-fill" style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="pix-card-progress-text">
                                <span><strong>{{ number_format($petition->signatures_count) }}</strong> signed</span>
                                <span>Goal: {{ number_format($petition->signature_goal) }}</span>
                            </div>
                        </div>
                        <span class="pix-card-cta">Sign &rsaquo;</span>
                    </div>
                </a>
            @endforeach
        </div>
        {{ $petitions->links('vendor.pagination.nppc') }}
    @endif
</main>
@endsection
