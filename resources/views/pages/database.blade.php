@extends('app')

@php
    // Arrived via /database/{facet}/{value}. The value here is the raw URL
    // segment; the Vue app matches it against the real filter options, so
    // this is only used for the title.
    $facetLabel = isset($facet) ? ucwords(str_replace('-', ' ', $facetValue)) : null;
@endphp

@section('title', $facetLabel ? $facetLabel.' | Political Prisoner Database | NPPC' : 'Political Prisoner Database | NPPC')

@section('head')
<style>
    @media (max-width: 768px) {
        .db-about-section { padding: 48px 16px !important; }
        .db-about-section h2 { font-size: 1.6rem !important; margin-bottom: 20px !important; }
        .db-about-section .db-about-inner { font-size: 16px !important; }
    }

    /* Loading state shown until the Vue database app renders its content */
    #db-loading {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        text-align: center; padding: 120px 24px; min-height: 50vh; color: rgba(var(--fg-rgb),0.6);
    }
    #db-loading .db-spinner {
        width: 52px; height: 52px; border-radius: 50%;
        border: 4px solid rgba(var(--fg-rgb),0.15); border-top-color: var(--accent);
        animation: db-spin 0.9s linear infinite; margin-bottom: 24px;
    }
    @keyframes db-spin { to { transform: rotate(360deg); } }
    #db-loading .db-title { font-size: 2rem; font-weight: 700; color: var(--fg); margin-bottom: 16px; }
    #db-loading .db-help { font-size: 16px; line-height: 1.6; max-width: 500px; margin: 0 auto; }
    @media (prefers-reduced-motion: reduce) { #db-loading .db-spinner { animation-duration: 2.4s; } }
</style>
@endsection

@section('body')
    <section id="prisoners-page">
        <main id="maincontent">
            {{-- Loading indicator: lives OUTSIDE #app so Vue's mount doesn't wipe it.
                 Hidden by the script below once the Vue app renders its content (which
                 covers both the app.js download and the async data fetch). If the app
                 never loads, this stays visible with the refresh/contact guidance. --}}
            <div id="db-loading" role="status" aria-live="polite">
                <div class="db-spinner" aria-hidden="true"></div>
                <div class="db-title">Loading Prisoner Database...</div>
                <p class="db-help">If this page doesn't load, please try refreshing. If the problem persists, <a href="/contact" style="color:var(--accent-2); text-decoration:underline;">contact us</a>.</p>
            </div>
            <div id="app"></div>
        </main>
    </section>

    {{-- The Vue app reads the filter deep link straight off location.pathname,
         so nothing needs handing to it here. It has to work that way round:
         the Back button delivers a path and nothing else, so the path is the
         only thing that can be the source of truth in both directions. $facet
         is still used above, for the page title. --}}

    <script>
        (function () {
            var overlay = document.getElementById('db-loading');
            var app = document.getElementById('app');
            if (!overlay || !app) return;

            // The Vue app renders <section id="vueApp"> with the database UI inside it.
            // While <Suspense> waits on the data fetch, #vueApp has no element children,
            // so we keep the loader up until real content appears.
            function isLoaded() {
                var v = app.querySelector('#vueApp');
                return !!(v && v.childElementCount > 0);
            }
            function hideLoader() {
                if (observer) observer.disconnect();
                overlay.style.display = 'none';
            }

            var observer = new MutationObserver(function () {
                if (isLoaded()) hideLoader();
            });
            observer.observe(app, { childList: true, subtree: true });

            // In case the app rendered before this script ran.
            if (isLoaded()) hideLoader();
        })();
    </script>

    {{-- About this database --}}
    <section class="db-about-section" style="background:var(--bg); color:rgba(var(--fg-rgb),0.85); padding:96px 24px; border-top:1px solid rgba(var(--fg-rgb),0.08);">
        <div style="max-width:780px; margin:0 auto;">
            <h2 style="font-size:2.5rem; font-weight:900; color:var(--fg); line-height:1.1; margin:0 0 32px;">About this database</h2>

            <div class="db-about-inner" style="font-size:17px; line-height:1.7;">
                <p style="margin:0 0 20px;">
                    This database of U.S. political prisoners was assembled from public records, including the historical case archives of prisoner-support committees, court files retrieved through the federal judiciary's PACER case-management system and state court records, the federal Bureau of Prisons inmate locator, state Department of Corrections inmate locators, and numerous various other sources. Each prisoner's case data is categorized by ideology, affiliation, era, and current custody status.
                </p>

                <p style="margin:0 0 20px;">
                    The first iteration of this database was compiled by NPPC researchers from internal support-work archives. Since then, a team of volunteer researchers and formerly incarcerated organizers has verified cases against court records, BOP and state inmate locators, contemporaneous news coverage, and direct correspondence with prisoners, their families, and the support committees that work alongside them.
                </p>

                <p style="margin:0 0 20px;">
                    The database includes political prisoners held in U.S. federal and state custody, U.S. nationals in exile to escape U.S. prosecution, and foreign nationals whose imprisonment is the direct result of U.S. action — including extradition cases, prosecutions for actions at U.S. military installations abroad, and ICE administrative detentions. The collection extends from the COINTELPRO-era prosecutions of the 1960s and 70s through the post-Floyd uprising and Palestine-solidarity defendants of 2020–2025.
                </p>

                <p style="margin:0;">
                    This database is shared under a <a href="https://creativecommons.org/licenses/by-nc/4.0/" style="color:var(--accent-2); text-decoration:underline;">Creative Commons Attribution-NonCommercial 4.0 license</a> and may be reused for noncommercial purposes with appropriate attribution. If you republish this database in whole or in part, we request you credit the <strong>National Political Prisoner Coalition (NPPC)</strong>. To submit a correction, suggest a case, or request the data in CSV form, please <a href="/contact" style="color:var(--accent-2); text-decoration:underline;">contact us</a>.
                </p>
            </div>
        </div>
    </section>
@endsection
