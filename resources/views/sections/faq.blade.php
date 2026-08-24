@php use App\Models\Faq; @endphp
@php
    $faqs = Faq::getFaqsByType($type);
    // Ids are namespaced by type. Several FAQ blocks can appear on one page
    // (about, map and donate each include this), and the script below scopes
    // every toggle to its own section, so two blocks cannot drive each
    // other's open state.
    $uid = 'faq-'.$type;
@endphp

@if($faqs->isNotEmpty())
<section class="faqx" id="{{ $uid }}">
    <div class="faqx-inner">
        <h2 class="faqx-title">Frequently Asked Questions</h2>

        <div class="faqx-list">
            @foreach($faqs as $faq)
                <div class="faqx-entry">
                    {{-- The accent sweep sits behind the row and bleeds past
                         the text column, as in the reference. --}}
                    <span class="faqx-sweep" aria-hidden="true"></span>

                    <button type="button" class="faqx-q" aria-expanded="false" aria-controls="{{ $uid }}-a{{ $loop->index }}">
                        <span class="faqx-q-text">{{ $faq->question }}</span>
                        <svg class="faqx-arrow" width="30" height="30" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M6 9l6 6 6-6" stroke-linecap="square"/>
                        </svg>
                    </button>

                    <div class="faqx-a" id="{{ $uid }}-a{{ $loop->index }}" role="region" hidden>
                        <div class="faqx-a-inner">{!! nl2br(e($faq->answer)) !!}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@once
<style>
    /* FAQ — accordion in the shape of the reference: hairline-separated
       rows, question left and arrow right, the question and the arrow
       picking up the accent on hover while a bar of accent sweeps in behind
       the row and bleeds past the text column. The answer is set in a serif
       with an indented first line and opens by height.

       Uses the theme variables rather than hard-coded black and white, so
       the block survives the light theme the old markup broke in. */
    .faqx { padding: 96px 0; }
    .faqx-inner { max-width: 940px; margin: 0 auto; padding: 0 24px; }
    .faqx-title { font-size: 2rem; font-weight: 900; letter-spacing: 0.02em; color: var(--fg); margin: 0 0 48px; }

    /* -1px so adjacent rules collapse into a single hairline. */
    .faqx-entry { position: relative; margin-bottom: -1px; border-top: 1px solid rgba(var(--fg-rgb),0.22); border-bottom: 1px solid rgba(var(--fg-rgb),0.22); }

    .faqx-sweep { position: absolute; inset: 0 auto 0 -60px; width: calc(100% + 120px); background: var(--accent); opacity: 0; transition: opacity 0.2s ease; pointer-events: none; z-index: 0; }
    .faqx-entry:hover .faqx-sweep, .faqx-entry:focus-within .faqx-sweep { opacity: 0.12; }
    .faqx-entry.is-open .faqx-sweep { opacity: 0.16; }

    .faqx-q { position: relative; z-index: 1; width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 24px; padding: 26px 0; background: none; border: 0; text-align: left; cursor: pointer; color: rgba(var(--fg-rgb),0.85); font: inherit; transition: color 0.2s ease; }
    .faqx-q-text { font-size: 21px; font-weight: 500; line-height: 1.3; }
    .faqx-q:hover, .faqx-q:focus-visible { color: var(--accent-2); }
    .faqx-entry.is-open .faqx-q { color: var(--accent-2); }
    .faqx-q:focus-visible { outline: 2px solid var(--accent-2); outline-offset: 4px; }

    .faqx-arrow { flex: 0 0 auto; width: 30px; height: 30px; transition: transform 0.25s ease, color 0.2s ease; }
    .faqx-entry.is-open .faqx-arrow { transform: rotate(180deg); }

    /* Height is animated by the script; overflow keeps the text clipped
       while it moves. */
    .faqx-a { position: relative; z-index: 1; height: 0; overflow: hidden; transition: height 0.3s ease; }
    .faqx-a-inner { padding: 0 0 28px; font-family: Georgia, 'Times New Roman', serif; font-size: 18px; line-height: 1.75; color: rgba(var(--fg-rgb),0.62); text-indent: 1.6em; max-width: 68ch; }

    @media (max-width: 900px) {
        .faqx { padding: 64px 0; }
        .faqx-sweep { inset: 0 auto 0 -24px; width: calc(100% + 48px); }
        .faqx-q-text { font-size: 18px; }
        .faqx-arrow { width: 24px; height: 24px; }
    }

    @media (max-width: 600px) {
        .faqx { padding: 48px 0; }
        .faqx-inner { padding: 0 16px; }
        .faqx-title { font-size: 1.5rem; margin-bottom: 28px; }
        .faqx-q { padding: 20px 0; gap: 14px; }
        .faqx-q-text { font-size: 16px; }
        .faqx-arrow { width: 20px; height: 20px; }
        .faqx-a-inner { font-size: 15px; line-height: 1.65; text-indent: 1.2em; }
    }

    @media (prefers-reduced-motion: reduce) {
        .faqx-sweep, .faqx-q, .faqx-arrow, .faqx-a { transition: none; }
    }
</style>

<script>
(function () {
    // Delegated and scoped per section, so two FAQ blocks on one page (the
    // about and map pages each carry one) never toggle each other.
    var calm = window.matchMedia('(prefers-reduced-motion: reduce)');

    function close(entry) {
        var a = entry.querySelector('.faqx-a');
        var q = entry.querySelector('.faqx-q');
        a.style.height = a.scrollHeight + 'px';
        requestAnimationFrame(function () { a.style.height = '0px'; });
        entry.classList.remove('is-open');
        q.setAttribute('aria-expanded', 'false');
        window.setTimeout(function () {
            if (!entry.classList.contains('is-open')) a.hidden = true;
        }, calm.matches ? 0 : 320);
    }

    function open(entry) {
        var a = entry.querySelector('.faqx-a');
        var q = entry.querySelector('.faqx-q');
        a.hidden = false;
        entry.classList.add('is-open');
        q.setAttribute('aria-expanded', 'true');
        a.style.height = calm.matches ? 'auto' : a.scrollHeight + 'px';
    }

    // Bound on the document, not on each section: this block is emitted
    // once per page, and a section further down the markup would not exist
    // yet if the listeners were attached here and now.
    document.addEventListener('click', function (e) {
        var q = e.target.closest && e.target.closest('.faqx-q');
        if (!q) return;

        var section = q.closest('.faqx');
        var entry = q.closest('.faqx-entry');
        var isOpen = entry.classList.contains('is-open');

        // One at a time, within this section only.
        section.querySelectorAll('.faqx-entry.is-open').forEach(close);
        if (!isOpen) open(entry);
    });

    // A row that has finished opening settles to its natural height, so a
    // long answer stays fully visible if the window is later resized.
    document.addEventListener('transitionend', function (e) {
        if (e.propertyName !== 'height') return;
        var a = e.target;
        if (a.classList && a.classList.contains('faqx-a') && a.closest('.faqx-entry').classList.contains('is-open')) {
            a.style.height = 'auto';
        }
    }, true);

    window.addEventListener('resize', function () {
        document.querySelectorAll('.faqx-entry.is-open .faqx-a').forEach(function (a) { a.style.height = 'auto'; });
    });
})();
</script>
@endonce
@endif
