@extends('app')

@section('head')
<style>
    @media (max-width: 768px) {
        .db-about-section { padding: 48px 16px !important; }
        .db-about-section h2 { font-size: 1.6rem !important; margin-bottom: 20px !important; }
        .db-about-section .db-about-inner { font-size: 16px !important; }
    }
</style>
@endsection

@section('body')
    <section id="prisoners-page">
        <main id="maincontent">
            <div id="app">
                <div style="text-align:center; padding:120px 24px; color:rgba(255,255,255,0.6);">
                    <div style="font-size:2rem; font-weight:700; color:#fff; margin-bottom:16px;">Loading Prisoner Database...</div>
                    <p style="font-size:16px; line-height:1.6; max-width:500px; margin:0 auto;">If this page doesn't load, please try refreshing. If the problem persists, <a href="/contact" style="color:#5660fe; text-decoration:underline;">contact us</a>.</p>
                </div>
            </div>
        </main>
    </section>

    {{-- About this database --}}
    <section class="db-about-section" style="background:#000; color:rgba(255,255,255,0.85); padding:96px 24px; border-top:1px solid rgba(255,255,255,0.08);">
        <div style="max-width:780px; margin:0 auto;">
            <h2 style="font-size:2.5rem; font-weight:900; color:#fff; line-height:1.1; margin:0 0 32px;">About this database</h2>

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
                    This database is shared under a <a href="https://creativecommons.org/licenses/by-nc/4.0/" style="color:#5660fe; text-decoration:underline;">Creative Commons Attribution-NonCommercial 4.0 license</a> and may be reused for noncommercial purposes with appropriate attribution. If you republish this database in whole or in part, we request you credit the <strong>National Political Prisoner Coalition (NPPC)</strong>. To submit a correction, suggest a case, or request the data in CSV form, please <a href="/contact" style="color:#5660fe; text-decoration:underline;">contact us</a>.
                </p>
            </div>
        </div>
    </section>
@endsection
