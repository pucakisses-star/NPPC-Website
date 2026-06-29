{{-- Global light/dark toggle. Styles + the anti-FOUC bootstrap live in
     app.blade.php's head; this is just the button and its click handler. --}}
<button type="button" class="site-theme-toggle" data-theme-toggle aria-label="Toggle light or dark theme">
    {{-- Sun shows in dark mode (click → light); moon shows in light mode. --}}
    <svg class="site-theme-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
    <svg class="site-theme-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
    <span>Theme</span>
</button>
<script>
(function () {
    var root = document.documentElement;
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-theme-toggle]');
        if (!btn) { return; }
        var next = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
        if (next === 'light') { root.setAttribute('data-theme', 'light'); }
        else { root.removeAttribute('data-theme'); }
        try { localStorage.setItem('nppc-theme', next); } catch (e) {}
    });
})();
</script>
