{{--
    Scoped light/dark theme PROOF OF CONCEPT for the store pages only.

    Defines the store color tokens (dark = current look, unchanged; light =
    override), a floating toggle, persistence (localStorage) and system-pref
    (prefers-color-scheme) support. Included near the top of the store page
    bodies so the inline script sets the theme before the content below paints.

    Scope: only store pages include this partial, and only store CSS reads these
    tokens, so the rest of the site (and its global header/footer) is untouched.
--}}
<style>
    :root {
        --store-bg: transparent;
        --store-fg: #fff;
        --store-fg-rgb: 255,255,255;
        --store-surface: #16181f;
        --store-surface-2: #1a1a2e;
        --store-accent: #5660fe;
        --store-accent-2: #8b93ff;
        --store-accent-hover: #4049d6;
        --store-on-accent: #fff;
    }
    html[data-store-theme="light"] {
        --store-bg: #f4f5f7;
        --store-fg: #17191f;
        --store-fg-rgb: 23,25,31;
        --store-surface: #ffffff;
        --store-surface-2: #e6e8ed;
        --store-accent: #5660fe;
        --store-accent-2: #4049d6;
        --store-accent-hover: #3a42b8;
        --store-on-accent: #fff;
    }
    /* In light mode, light up the whole page behind the store content. The
       global header/footer keep their own (dark) component styles — expected
       for this scoped POC. */
    html[data-store-theme="light"] body { background: var(--store-bg); }
    .store-page, .pd-page { background: var(--store-bg); }

    .store-theme-toggle {
        position: fixed; right: 20px; bottom: 20px; z-index: 60;
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 16px; border-radius: 999px;
        border: 1px solid rgba(var(--store-fg-rgb), 0.25);
        background: var(--store-surface); color: var(--store-fg);
        font-size: 13px; font-weight: 700; cursor: pointer;
        box-shadow: 0 6px 20px rgba(0,0,0,0.28); transition: transform 0.15s, border-color 0.15s;
    }
    .store-theme-toggle:hover { transform: translateY(-1px); border-color: var(--store-accent); }
    .store-theme-toggle svg { width: 16px; height: 16px; display: block; }
    .store-theme-toggle .store-theme-moon { display: none; }
    html[data-store-theme="light"] .store-theme-toggle .store-theme-sun { display: none; }
    html[data-store-theme="light"] .store-theme-toggle .store-theme-moon { display: block; }
</style>

<button type="button" class="store-theme-toggle" data-store-theme-toggle aria-label="Toggle light or dark theme">
    {{-- Sun shows in dark mode (click → light); moon shows in light mode (click → dark). --}}
    <svg class="store-theme-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
    <svg class="store-theme-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
    <span class="store-theme-label">Theme</span>
</button>

<script>
(function () {
    var root = document.documentElement;
    var KEY = 'nppc-store-theme';
    function apply(t) {
        if (t === 'light') { root.setAttribute('data-store-theme', 'light'); }
        else { root.removeAttribute('data-store-theme'); }
    }
    var saved = null;
    try { saved = localStorage.getItem(KEY); } catch (e) {}
    if (saved !== 'light' && saved !== 'dark') {
        saved = (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) ? 'light' : 'dark';
    }
    apply(saved);
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-store-theme-toggle]');
        if (!btn) { return; }
        var next = root.getAttribute('data-store-theme') === 'light' ? 'dark' : 'light';
        apply(next);
        try { localStorage.setItem(KEY, next); } catch (e) {}
    });
})();
</script>
