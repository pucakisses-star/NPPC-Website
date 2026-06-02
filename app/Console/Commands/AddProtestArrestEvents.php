<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds recent protest-arrest events from a range of issues — climate, housing,
 * anti-war, trans rights and immigration — to the dashboard newswire, each
 * pinned on the map at its exact venue (state capitol, brownstone, Senate
 * office building, ICE facility) rather than a vague town centre.
 *
 * Same shape as dashboard:add-arrest-events: every source URL becomes a
 * newswire item, and one representative source per event carries the
 * coordinates (the marker). Rows are keyed by URL via updateOrCreate, so the
 * command is idempotent and safe to re-run. Manage them in /admin afterwards.
 *
 * Run on the server: php artisan dashboard:add-protest-events
 */
final class AddProtestArrestEvents extends Command {
    protected $signature = 'dashboard:add-protest-events';
    protected $description = 'Add recent protest-arrest events (climate, housing, anti-war, trans rights, immigration) to the dashboard';

    /**
     * [title, url, source, date, lat, lng, location_label]. lat/lng/label are
     * null for additional sources covering an event already pinned above them.
     */
    private array $links = [
        // ── Climate · New York State Capitol, Albany NY · Apr 21, 2026 (+ Mar 25) ──
        ['18 arrested at NY Capitol over climate-law rollback', 'https://capitalregion.iheart.com/content/2026-04-22-protesters-arrested-at-new-york-state-capitol-over-climate-law/', 'iHeart Capital Region', '2026-04-21', 42.6525913, -73.7573712, 'New York State Capitol, Albany, NY'],
        ['Climate advocates arrested at New York State Capitol', 'https://www.wamc.org/news/2026-03-25/climate-advocates-arrested-at-new-york-state-capitol', 'WAMC', '2026-03-25', null, null, null],
        ['21 arrested as hundreds swarm Capitol over climate law', 'https://www.news10.com/capitol/climate-law-protests-arrests/', 'News10', '2026-03-25', null, null, null],

        // ── Housing · 212 Jefferson Ave, Bedford-Stuyvesant, Brooklyn · Apr 22, 2026 ──
        ['Council member Chi Ossé arrested at Brooklyn eviction protest', 'https://ny1.com/nyc/brooklyn/news/2026/04/22/councilmember-chi-oss--arrested-during-eviction-dispute-in-brooklyn', 'NY1', '2026-04-22', 40.6831871, -73.9491010, '212 Jefferson Ave, Bedford-Stuyvesant, Brooklyn'],
        ['NYC councilmember released after violent arrest at anti-eviction protest', 'https://www.democracynow.org/2026/4/23/headlines/nyc_councilmember_chi_osse_released_after_violent_arrest_at_anti_eviction_protest', 'Democracy Now!', '2026-04-22', null, null, null],

        // ── Anti-war · Hart Senate Office Building, Washington DC · Mar 4, 2026 ──
        ['Marine veteran arrested at Senate hearing over Israel policy', 'https://www.military.com/feature/2026/03/05/brian-mcginnis-removed-senate-hearing-after-protest-over-us-policy-toward-israel.html', 'Military.com', '2026-03-04', 38.8928461, -77.0041747, 'Hart Senate Office Building, Washington, DC'],
        ['Ex-Marine arrested, arm broken during Iran-war protest in Senate', 'https://www.democracynow.org/2026/3/11/brian_mcginnis_iran_war_protest_congress', 'Democracy Now!', '2026-03-04', null, null, null],
        ['NC firefighter, Marine veteran charged after Senate hearing protest', 'https://abc11.com/post/marine-veteran-north-carolina-charged-protesting-war-iran-senate-hearing/18679829/', 'ABC11', '2026-03-04', null, null, null],

        // ── Trans rights · Idaho State Capitol, Boise ID · Apr 1, 2026 (+ Apr 3) ──
        ['Nine arrested at Idaho Statehouse over anti-trans bill', 'https://idahocapitalsun.com/2026/04/01/protestors-urging-idaho-governor-to-veto-bill-outing-trans-kids-to-parents-arrested-at-statehouse/', 'Idaho Capital Sun', '2026-04-01', 43.6177696, -116.1996904, 'Idaho State Capitol, Boise, ID'],
        ['Six arrested at Idaho Capitol over trans bathroom-ban sit-in', 'https://www.thepinknews.com/2026/04/06/idaho-trans-bathroom-ban/', 'PinkNews', '2026-04-03', null, null, null],

        // ── Immigration · ICE facility, South Waterfront, Portland OR · Jan 9, 2026 ──
        ['Six arrested at Portland ICE facility protest', 'https://www.newsweek.com/portland-protest-outside-ice-facility-sees-multiple-people-arrested-10868665', 'Newsweek', '2026-01-09', 45.4925394, -122.6725455, 'ICE facility, South Waterfront, Portland, OR'],
        ['PPB monitors protest near ICE facility; six arrests made', 'https://www.portland.gov/police/news/2026/1/9/ppb-monitors-protest-activity-near-ice-facility-six-arrests-made', 'City of Portland', '2026-01-09', null, null, null],
    ];

    public function handle(): int {
        $created = 0;
        $updated = 0;
        $markers = 0;

        foreach ($this->links as [$title, $url, $source, $date, $lat, $lng, $label]) {
            $link = DashboardLink::updateOrCreate(
                ['url' => $url],
                [
                    'title' => $title,
                    'source' => $source,
                    'published_at' => Carbon::parse($date . ' 09:00'),
                    'lat' => $lat,
                    'lng' => $lng,
                    'location_label' => $label,
                ],
            );

            $link->wasRecentlyCreated ? $created++ : $updated++;
            if ($lat !== null) {
                $markers++;
            }
        }

        $this->info("Done. {$created} created, {$updated} updated — " . count($this->links) . " newswire items, {$markers} map markers.");

        return self::SUCCESS;
    }
}
