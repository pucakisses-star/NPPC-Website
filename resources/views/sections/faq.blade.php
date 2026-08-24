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
    /* FAQ, matching the reference video rather than my first reading of it.

       Rows are hairline-separated, the question set in wide uppercase with a
       chevron opposite. Hovering fills the whole row with a solid bar of
       accent that bleeds past the text column, and the question and chevron
       go to the on-accent colour. An open row is not filled: its question
       and chevron simply take the accent colour and the answer sits on the
       page background, in a serif with an indented first line.

       More than one row can be open at a time — the video shows several open
       together, which is the behaviour, not an accordion.

       Uses the theme variables rather than hard-coded black and white, so
       the block survives the light theme the old markup broke in. */
    .faqx { padding: 96px 0; }
    .faqx-inner { max-width: 1000px; margin: 0 auto; padding: 0 24px; }
    .faqx-title { font-size: clamp(2.4rem, 6vw, 4.5rem); font-weight: 300; line-height: 1; letter-spacing: 0.01em; text-transform: uppercase; color: var(--fg); margin: 0 0 48px; }

    /* -1px so adjacent rules collapse into a single hairline. */
    .faqx-entry { position: relative; margin-bottom: -1px; border-top: 1px solid rgba(var(--fg-rgb),0.18); border-bottom: 1px solid rgba(var(--fg-rgb),0.18); }

    /* The hover bar: solid, and wider than the column it sits in. */
    .faqx-sweep { position: absolute; left: -60px; right: -60px; top: 0; bottom: 0; background: var(--accent); opacity: 0; transition: opacity 0.18s ease; pointer-events: none; z-index: 0; }
    .faqx-entry:hover > .faqx-sweep { opacity: 1; }
    /* Keyboard focus lights the bar too, but a mouse click must not: a
       button keeps focus after it is clicked, and :focus-within would leave
       the row you just opened stuck under a solid bar. Kept in its own rule
       so a browser without :has() still gets the hover bar. */
    .faqx-entry:has(.faqx-q:focus-visible) > .faqx-sweep { opacity: 1; }
    /* Only the question row is covered, never the open answer. */
    .faqx-entry.is-open > .faqx-sweep { bottom: auto; height: var(--faqx-row, 0px); }

    .faqx-q { position: relative; z-index: 1; width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 24px; padding: 24px 0; background: none; border: 0; text-align: left; cursor: pointer; color: rgba(var(--fg-rgb),0.88); font: inherit; transition: color 0.18s ease; }
    .faqx-q-text { font-size: 21px; font-weight: 400; line-height: 1.25; letter-spacing: 0.015em; text-transform: uppercase; }
    /* --accent-2 rather than --accent: this is text on the page background,
       and --accent-2 is the triplet tuned per theme for exactly that. Plain
       --accent clears AA by a hair on both grounds; --accent-2 clears it
       comfortably. The bar below stays --accent, which is a fill. */
    .faqx-entry.is-open .faqx-q { color: var(--accent-2); }
    /* Wherever the bar is lit the question has to be on-accent — including
       on a row that is already open, whose accent text would otherwise be
       accent on accent and invisible. Both selectors are written to outweigh
       the .is-open rule above. */
    .faqx-entry:hover .faqx-q, .faqx-entry .faqx-q:focus-visible { color: var(--on-accent); }
    .faqx-q:focus-visible { outline: 2px solid var(--on-accent); outline-offset: -4px; }

    .faqx-arrow { flex: 0 0 auto; width: 30px; height: 30px; transition: transform 0.25s ease; }
    .faqx-entry.is-open .faqx-arrow { transform: rotate(180deg); }

    /* Height is animated by the script; overflow keeps the text clipped
       while it moves. No background of its own. */
    .faqx-a { position: relative; z-index: 1; height: 0; overflow: hidden; transition: height 0.3s ease; }
    .faqx-a-inner { padding: 0 0 26px; font-family: Georgia, 'Times New Roman', serif; font-size: 17px; line-height: 1.7; color: rgba(var(--fg-rgb),0.6); text-indent: 1.6em; max-width: 62ch; }

    @media (max-width: 900px) {
        .faqx { padding: 64px 0; }
        .faqx-sweep { left: -24px; right: -24px; }
        .faqx-q-text { font-size: 18px; }
        .faqx-arrow { width: 24px; height: 24px; }
    }

    @media (max-width: 600px) {
        .faqx { padding: 48px 0; }
        .faqx-inner { padding: 0 16px; }
        /* The bleed has to track the padding exactly: at 24px against a
           16px gutter the bar hangs 8px off each edge and the page picks up
           a horizontal scrollbar. */
        .faqx-sweep { left: -16px; right: -16px; }
        .faqx-title { margin-bottom: 28px; }
        .faqx-q { padding: 18px 0; gap: 14px; }
        .faqx-q-text { font-size: 15px; }
        .faqx-arrow { width: 20px; height: 20px; }
        .faqx-a-inner { font-size: 15px; line-height: 1.65; text-indent: 1.2em; }
    }

    @media (prefers-reduced-motion: reduce) {
        .faqx-sweep, .faqx-q, .faqx-arrow, .faqx-a { transition: none; }
    }
</style>

<script>
(function () {
    var calm = window.matchMedia('(prefers-reduced-motion: reduce)');

    // The hover bar must cover the question row and stop there, never the
    // open answer beneath it, so the row height is published to the CSS.
    function measure(entry) {
        var q = entry.querySelector('.faqx-q');
        entry.style.setProperty('--faqx-row', q.offsetHeight + 'px');
    }

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
        measure(entry);
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

        var entry = q.closest('.faqx-entry');

        // Any number of rows may be open at once — the reference shows
        // several open together, so opening one does not close the others.
        if (entry.classList.contains('is-open')) {
            close(entry);
        } else {
            open(entry);
        }
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
