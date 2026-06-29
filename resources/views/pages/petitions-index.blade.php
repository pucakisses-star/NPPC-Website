@extends('app')

@section('title', 'Sign Petitions | NPPC')

@section('head')
<meta name="description" content="Be a force for change. Add your name to the campaigns demanding clemency, dropped charges, and accountability for political prisoners across the United States.">
<style>
    /* ============================================================
       Petitions index — modeled on the Innocence Project's
       "Sign Petitions" page: a featured-petition hero, a clean
       square-thumbnail card grid, a sort control, and a closing
       donate CTA. Fully themed with the global light/dark tokens.
       ============================================================ */
    .pix-wrap { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

    /* Header */
    .pix-hero { padding: 60px 0 8px; }
    .pix-hero h1 { font-size: 3rem; font-weight: 900; color: var(--fg); line-height: 1.04; margin: 0 0 14px; letter-spacing: -0.02em; }
    .pix-hero p { font-size: 1.2rem; color: rgba(var(--fg-rgb),0.7); max-width: 720px; line-height: 1.65; margin: 0; }

    /* Featured petition */
    .pix-featured { display: grid; grid-template-columns: 1.1fr 1fr; gap: 40px; align-items: center; margin: 40px 0 8px; padding: 0 0 48px; border-bottom: 1px solid rgba(var(--fg-rgb),0.1); }
    .pix-feat-media { aspect-ratio: 16 / 11; border-radius: 14px; overflow: hidden; background: var(--surface-2) center/cover no-repeat; }
    .pix-feat-media.is-empty { display: flex; align-items: center; justify-content: center; color: rgba(var(--fg-rgb),0.25); font-size: 13px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; }
    .pix-overline { display: inline-block; font-size: 12px; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase; color: var(--accent-2); margin-bottom: 14px; }
    .pix-feat-title { font-size: 2rem; font-weight: 800; color: var(--fg); line-height: 1.18; margin: 0 0 16px; letter-spacing: -0.01em; }
    .pix-feat-title a { color: inherit; text-decoration: none; }
    .pix-feat-title a:hover { color: var(--accent-2); }
    .pix-feat-desc { font-size: 1.05rem; color: rgba(var(--fg-rgb),0.72); line-height: 1.7; margin: 0 0 18px; }
    .pix-feat-meta { font-size: 13px; color: rgba(var(--fg-rgb),0.5); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 22px; }
    .pix-feat-meta strong { color: var(--fg); }

    .pix-btn { display: inline-flex; align-items: center; gap: 8px; background: var(--accent); color: var(--on-accent); padding: 15px 30px; border-radius: 8px; font-size: 15px; font-weight: 800; text-decoration: none; transition: background 0.15s, transform 0.15s; }
    .pix-btn:hover { background: var(--accent-hover); color: var(--on-accent); transform: translateY(-2px); }

    /* Sort bar */
    .pix-bar { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin: 40px 0 22px; }
    .pix-bar-title { font-size: 1.5rem; font-weight: 900; color: var(--fg); margin: 0; text-transform: uppercase; letter-spacing: 0.02em; }
    .pix-sort { display: inline-flex; gap: 6px; background: rgba(var(--fg-rgb),0.05); border: 1px solid rgba(var(--fg-rgb),0.1); border-radius: 999px; padding: 4px; }
    .pix-sort-btn { background: none; border: none; color: rgba(var(--fg-rgb),0.6); font: inherit; font-size: 13px; font-weight: 700; padding: 8px 16px; border-radius: 999px; cursor: pointer; transition: background 0.15s, color 0.15s; }
    .pix-sort-btn.is-active { background: var(--accent); color: var(--on-accent); }

    /* Grid */
    .pix-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 26px 24px; padding-bottom: 24px; }
    .pix-card { display: flex; flex-direction: column; text-decoration: none; }
    .pix-img-box { aspect-ratio: 1 / 1; border-radius: 12px; overflow: hidden; background: var(--surface-2) center/cover no-repeat; transition: transform 0.18s, box-shadow 0.18s; }
    .pix-img-box.is-empty { display: flex; align-items: center; justify-content: center; color: rgba(var(--fg-rgb),0.22); font-size: 12px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; }
    .pix-card:hover .pix-img-box { transform: translateY(-3px); box-shadow: 0 16px 36px rgba(0,0,0,0.18); }
    .pix-card-title { font-size: 1.08rem; font-weight: 800; color: var(--fg); line-height: 1.32; margin: 16px 0 6px; }
    .pix-card:hover .pix-card-title { color: var(--accent-2); }
    .pix-card-meta { font-size: 13px; color: rgba(var(--fg-rgb),0.5); margin-top: auto; }
    .pix-card-meta strong { color: var(--fg); }

    .pix-empty { text-align: center; padding: 96px 24px; color: rgba(var(--fg-rgb),0.5); font-size: 1.05rem; }

    /* Closing CTA */
    .pix-cta { margin: 56px 0 88px; padding: 64px 32px; border-radius: 18px; text-align: center; background: radial-gradient(120% 140% at 50% 0%, var(--surface-2) 0%, var(--surface) 100%); border: 1px solid rgba(var(--fg-rgb),0.1); }
    .pix-cta h2 { font-size: 2rem; font-weight: 900; color: var(--fg); margin: 0 0 12px; letter-spacing: -0.01em; }
    .pix-cta p { font-size: 1.05rem; color: rgba(var(--fg-rgb),0.7); max-width: 560px; margin: 0 auto 26px; line-height: 1.6; }
    .pix-cta-btns { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
    .pix-btn-ghost { display: inline-flex; align-items: center; gap: 8px; background: transparent; color: var(--fg); border: 1px solid rgba(var(--fg-rgb),0.3); padding: 15px 30px; border-radius: 8px; font-size: 15px; font-weight: 800; text-decoration: none; transition: border-color 0.15s, transform 0.15s; }
    .pix-btn-ghost:hover { border-color: var(--accent); color: var(--fg); transform: translateY(-2px); }

    @@media (max-width: 820px) {
        .pix-hero { padding: 40px 0 8px; }
        .pix-hero h1 { font-size: 2.1rem; }
        .pix-hero p { font-size: 1.05rem; }
        .pix-featured { grid-template-columns: 1fr; gap: 22px; padding-bottom: 36px; }
        .pix-feat-title { font-size: 1.6rem; }
        .pix-bar-title { font-size: 1.25rem; }
    }
    @@media (max-width: 640px) {
        .pix-wrap { padding: 0 16px; }
        .pix-grid { grid-template-columns: repeat(2, 1fr); gap: 18px 14px; }
        .pix-cta { padding: 44px 20px; }
        .pix-cta h2 { font-size: 1.5rem; }
    }
</style>
@endsection

@section('body')
@php
    use Illuminate\Support\Str;
    $featured = $petitions->first();
    $rest = $petitions->slice(1)->values();
    $excerpt = function ($p) {
        $text = trim(strip_tags((string) ($p->body ?? '')));
        if ($text === '') {
            return $p->recipients
                ? 'Add your name to this campaign — every signature is delivered to '.Str::limit($p->recipients, 80).'.'
                : 'Add your name to this campaign for political prisoners.';
        }
        return Str::limit(preg_replace('/\s+/', ' ', $text), 230);
    };
@endphp

<main class="pix-wrap">

    {{-- Header --}}
    <header class="pix-hero">
        <h1>Sign Petitions</h1>
        <p>Be a force for change. Add your name to the campaigns demanding clemency, dropped charges, and accountability for political prisoners across the United States.</p>
    </header>

    @if ($petitions->isEmpty())
        <div class="pix-empty">No petitions are currently active. Check back soon.</div>
    @else

        {{-- Featured petition --}}
        @if ($featured)
            <section class="pix-featured">
                <a class="pix-feat-media {{ $featured->image ? '' : 'is-empty' }}"
                   href="/petition/{{ $featured->slug }}"
                   @if($featured->image) style="background-image: url('{{ $featured->image_url }}');" @endif>
                    @unless($featured->image) Petition @endunless
                </a>
                <div class="pix-feat-body">
                    <span class="pix-overline">Featured Petition</span>
                    <h2 class="pix-feat-title"><a href="/petition/{{ $featured->slug }}">{{ $featured->title }}</a></h2>
                    <p class="pix-feat-desc">{{ $excerpt($featured) }}</p>
                    <div class="pix-feat-meta"><strong>{{ number_format($featured->signatures_count) }}</strong> {{ Str::plural('signature', $featured->signatures_count) }} so far</div>
                    <a class="pix-btn" href="/petition/{{ $featured->slug }}">Sign the Petition &rarr;</a>
                </div>
            </section>
        @endif

        {{-- Sort bar + grid (the remaining petitions) --}}
        @if ($rest->isNotEmpty())
            <div class="pix-bar">
                <h2 class="pix-bar-title">All Petitions</h2>
                <div class="pix-sort" role="group" aria-label="Sort petitions">
                    <button type="button" class="pix-sort-btn is-active" data-sort="newest">Newest</button>
                    <button type="button" class="pix-sort-btn" data-sort="signed">Most signed</button>
                </div>
            </div>

            <div class="pix-grid" id="pix-grid">
                @foreach ($rest as $petition)
                    <a class="pix-card" href="/petition/{{ $petition->slug }}"
                       data-order="{{ $loop->index }}" data-signed="{{ (int) $petition->signatures_count }}">
                        <div class="pix-img-box {{ $petition->image ? '' : 'is-empty' }}"
                             @if($petition->image) style="background-image: url('{{ $petition->image_url }}');" @endif>
                            @unless($petition->image) Petition @endunless
                        </div>
                        <div class="pix-card-title">{{ $petition->title }}</div>
                        <div class="pix-card-meta"><strong>{{ number_format($petition->signatures_count) }}</strong> signed</div>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Closing CTA --}}
        <section class="pix-cta">
            <h2>Stand with political prisoners</h2>
            <p>Your signature is one way to act. Your support keeps this work going — documenting cases, backing legal defense, and pressing for release.</p>
            <div class="pix-cta-btns">
                <a class="pix-btn" href="/donate">Donate</a>
                <a class="pix-btn-ghost" href="/get-involved">Get Involved</a>
            </div>
        </section>

    @endif
</main>

<script>
    (function () {
        var grid = document.getElementById('pix-grid');
        if (!grid) return;
        var btns = [].slice.call(document.querySelectorAll('.pix-sort-btn'));

        function sort(mode) {
            var cards = [].slice.call(grid.children);
            cards.sort(function (a, b) {
                if (mode === 'signed') {
                    return (parseInt(b.dataset.signed, 10) || 0) - (parseInt(a.dataset.signed, 10) || 0);
                }
                return (parseInt(a.dataset.order, 10) || 0) - (parseInt(b.dataset.order, 10) || 0);
            });
            cards.forEach(function (c) { grid.appendChild(c); });
        }

        btns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                btns.forEach(function (b) { b.classList.remove('is-active'); });
                btn.classList.add('is-active');
                sort(btn.dataset.sort);
            });
        });
    })();
</script>
@endsection
