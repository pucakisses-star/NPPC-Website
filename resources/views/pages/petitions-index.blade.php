@extends('app')

@section('head')
<style>
.pix-wrap { max-width: 1200px; margin: 0 auto; padding: 0 24px 96px; }
.pix-hero { padding: 56px 0 24px; }
.pix-hero h1 { font-size: 2.5rem; font-weight: 900; color: #fff; line-height: 1.05; margin: 0 0 12px; }
.pix-hero p { font-size: 1.05rem; color: rgba(255,255,255,0.7); max-width: 720px; line-height: 1.7; margin: 0 0 8px; }
.pix-hero .pix-count { font-size: 14px; color: rgba(255,255,255,0.45); text-transform: uppercase; letter-spacing: 0.08em; margin-top: 16px; }

.pix-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 32px; }
.pix-card { display: flex; flex-direction: column; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; overflow: hidden; text-decoration: none; transition: border-color 0.15s, background 0.15s, transform 0.15s; }
.pix-card:hover { border-color: #5660fe; background: rgba(86,96,254,0.04); transform: translateY(-2px); }
.pix-img-box { position: relative; aspect-ratio: 16 / 9; background: #1a1a2e center/cover no-repeat; }
.pix-img-empty { display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.2); font-size: 12px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; }
.pix-card-body { padding: 20px; display: flex; flex-direction: column; gap: 12px; flex: 1; }
.pix-card-title { font-size: 1.1rem; font-weight: 800; color: #fff; line-height: 1.3; }
.pix-card-recipients { font-size: 12px; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 0.04em; }
.pix-card-progress { margin-top: auto; }
.pix-card-bar { height: 6px; background: rgba(255,255,255,0.08); border-radius: 3px; overflow: hidden; margin-bottom: 6px; }
.pix-card-fill { height: 100%; background: #5660fe; border-radius: 3px; }
.pix-card-progress-text { font-size: 12px; color: rgba(255,255,255,0.5); display: flex; justify-content: space-between; }
.pix-card-progress-text strong { color: #fff; }
.pix-card-cta { display: inline-flex; align-items: center; justify-content: center; gap: 6px; background: #5660fe; color: #fff; padding: 10px 14px; border-radius: 6px; font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; margin-top: 8px; }
.pix-card:hover .pix-card-cta { background: #4850e6; }
.pix-empty { text-align: center; padding: 96px 24px; color: rgba(255,255,255,0.5); }

@@media (max-width: 900px) { .pix-grid { grid-template-columns: repeat(2, 1fr); } }
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
    <div class="pix-hero">
        <h1>Active Petitions</h1>
        <p>Add your name to the campaigns demanding clemency, dropping charges, and accountability for U.S. political prisoners. Every signature is delivered to the named recipients.</p>
        <div class="pix-count">{{ $petitions->count() }} active {{ \Illuminate\Support\Str::plural('petition', $petitions->count()) }}</div>
    </div>

    @if ($petitions->isEmpty())
        <div class="pix-empty">No petitions are currently active. Check back soon.</div>
    @else
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
    @endif
</main>
@endsection
