<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * State-by-state sweep of the dashboard timeline window (May 7, 2025 onward) to
 * fill in geographic gaps -- protest/arrest cases in states that had no (or very
 * few) markers. Covers an 8-region sweep of all 50 states: gap-fill states
 * (ME, VT, WV, NC, SC, IA, MI, IN, OK, NM) plus top-ups for thinly-covered
 * states (FL, GA, NV, OH, CO, LA, MA). All in-window and nonviolent (or
 * no-arrest protests), sourced from public reporting. Matched on URL so the
 * command is idempotent.
 */
class AddStateSweepDashboardCases extends Command {
    protected $signature = 'dashboard:add-state-sweep-cases';
    protected $description = 'Add state-sweep protest/arrest cases (geographic gap-fill) to the dashboard';

    public function handle(): int {
        $cases = [
            // ---- Maine ----
            [
                'title'          => 'Nine faith leaders arrested for trespassing at a "pray-in" in the Portland office of Senator Susan Collins, urging an end to ICE funding in Maine; they sang "We Shall Overcome"',
                'url'            => 'https://www.mainepublic.org/immigration/2026-01-27/nine-faith-leaders-arrested-at-pray-in-at-susan-collins-portland-office',
                'source'         => 'Maine Public',
                'category'       => 'arrest',
                'published_at'   => '2026-01-27',
                'location_label' => 'Portland, ME',
                'lat'            => 43.6580,
                'lng'            => -70.2560,
            ],
            // ---- Vermont ----
            [
                'title'          => 'Thirteen protesters cited for trespassing after occupying the atrium of an ICE digital-surveillance center in Williston, Vermont; the prosecutor later declined to charge them',
                'url'            => 'https://vtdigger.org/2026/02/10/11-arrested-during-ice-protest-at-williston-business-park/',
                'source'         => 'VTDigger',
                'category'       => 'arrest',
                'published_at'   => '2026-02-09',
                'location_label' => 'Williston, VT',
                'lat'            => 44.4760,
                'lng'            => -73.0840,
            ],
            [
                'title'          => 'Four arrested for unlawful trespass blockading the entrances to the ICE digital-surveillance center in Williston, Vermont',
                'url'            => 'https://vtdigger.org/2026/05/14/four-arrested-at-protest-against-ice-at-williston-facility/',
                'source'         => 'VTDigger',
                'category'       => 'arrest',
                'published_at'   => '2026-05-14',
                'location_label' => 'Williston, VT',
                'lat'            => 44.4755,
                'lng'            => -73.0848,
            ],
            // ---- West Virginia ----
            [
                'title'          => 'Six arrested for trespassing in a sit-in at the Charleston office of Senator Shelley Moore Capito against Medicaid and SNAP cuts, including two city council members',
                'url'            => 'https://westvirginiawatch.com/2025/06/25/six-arrested-while-protesting-cuts-to-medicaid-snap-outside-capitos-charleston-office/',
                'source'         => 'West Virginia Watch',
                'category'       => 'arrest',
                'published_at'   => '2025-06-25',
                'location_label' => 'Charleston, WV',
                'lat'            => 38.3506,
                'lng'            => -81.6320,
            ],
            [
                'title'          => 'Six arrested for trespassing, including a congressional candidate and clergy, in a Moral Mondays sit-in at the Morgantown office of Senator Shelley Moore Capito over Medicaid and SNAP cuts',
                'url'            => 'https://wvpublic.org/story/government/west-virginians-protest-across-state-six-arrested-in-morgantown/',
                'source'         => 'West Virginia Public Broadcasting',
                'category'       => 'arrest',
                'published_at'   => '2026-01-20',
                'location_label' => 'Morgantown, WV',
                'lat'            => 39.6360,
                'lng'            => -79.9540,
            ],
            // ---- North Carolina ----
            [
                'title'          => 'Charlotte school-bus driver Heather Morrow had a federal felony assault charge dropped after video disputed it, then faced misdemeanors over a blockade of the Border Patrol office during the Charlotte immigration surge',
                'url'            => 'https://www.wfae.org/crime-justice/2025-11-25/after-dropping-felony-charges-federal-prosecutors-file-new-ones-against-ice-protesters',
                'source'         => 'WFAE',
                'category'       => 'prosecution',
                'published_at'   => '2025-11-16',
                'location_label' => 'Charlotte, NC',
                'lat'            => 35.1530,
                'lng'            => -80.8800,
            ],
            // ---- South Carolina ----
            [
                'title'          => 'Charleston anti-ICE protester Julia Tucker charged under a city mask ordinance for wearing a keffiyeh at an emergency rally over the fatal Minneapolis ICE shooting',
                'url'            => 'https://www.postandcourier.com/news/crime/marion-square-protest-arrest-jan-25/article_e3d40615-2739-4ea2-aca5-ca3cdbc9cf9d.html',
                'source'         => 'The Post and Courier',
                'category'       => 'arrest',
                'published_at'   => '2026-01-25',
                'location_label' => 'Charleston, SC',
                'lat'            => 32.7880,
                'lng'            => -79.9375,
            ],
            // ---- Iowa ----
            [
                'title'          => 'About 15 people charged with unlawful assembly and failure to disperse at Iowa City protests after ICE agents tackled and arrested a local grocery worker, Jorge Gonzalez Ochoa',
                'url'            => 'https://www.thegazette.com/news/crime-and-courts/large-iowa-city-protests-led-to-only-15-people-charged/article_b762e12c-9a76-5dc3-a081-44580d9ae7f9.html',
                'source'         => 'The Gazette',
                'category'       => 'arrest',
                'published_at'   => '2025-09-26',
                'location_label' => 'Iowa City, IA',
                'lat'            => 41.6562,
                'lng'            => -91.5350,
            ],
            // ---- Michigan ----
            [
                'title'          => 'Hundreds marched in Ann Arbor, Michigan, in a nationwide general strike against the ICE crackdown in Minneapolis (no arrests)',
                'url'            => 'https://www.michiganpublic.org/politics-government/2026-01-30/hundreds-march-in-ann-arbor-as-part-of-general-strike-against-ice-crackdown-in-minneapolis',
                'source'         => 'Michigan Public',
                'category'       => 'protest',
                'published_at'   => '2026-01-30',
                'location_label' => 'Ann Arbor, MI',
                'lat'            => 42.2808,
                'lng'            => -83.7430,
            ],
            // ---- Indiana ----
            [
                'title'          => 'Hundreds rallied at Monument Circle in Indianapolis after the fatal Minneapolis ICE shooting of Renee Good (no arrests)',
                'url'            => 'https://www.wfyi.org/news/articles/indianapolis-protest-ice-shooting-renee-nicole-good',
                'source'         => 'WFYI',
                'category'       => 'protest',
                'published_at'   => '2026-01-08',
                'location_label' => 'Indianapolis, IN',
                'lat'            => 39.7684,
                'lng'            => -86.1581,
            ],
            // ---- Oklahoma ----
            [
                'title'          => 'Four Tulsa Food Not Bombs volunteers arrested for obstruction at a weekly free-meal handout after the city demanded a costly events permit',
                'url'            => 'https://nondoc.com/2026/05/13/tulsa-police-arrest-4-food-not-bombs-members-others-allege-mayor-targeting-group/',
                'source'         => 'NonDoc',
                'category'       => 'arrest',
                'published_at'   => '2026-05-06',
                'location_label' => 'Tulsa, OK',
                'lat'            => 36.1600,
                'lng'            => -95.9950,
            ],
            // ---- New Mexico ----
            [
                'title'          => 'Two arrested, on charges including resisting arrest and disorderly conduct, at an Albuquerque protest outside a DHS facility over the fatal Minneapolis ICE shooting; agents pepper-sprayed the crowd',
                'url'            => 'https://www.abqjournal.com/news/two-arrested-at-ice-protest-in-albuquerque/2957357',
                'source'         => 'Albuquerque Journal',
                'category'       => 'arrest',
                'published_at'   => '2026-01-09',
                'location_label' => 'Albuquerque, NM',
                'lat'            => 35.0400,
                'lng'            => -106.6300,
            ],
            // ---- Florida (top-up) ----
            [
                'title'          => 'One protester arrested on misdemeanor charges at a "No Kings" protest at the Florida Capitol in Tallahassee',
                'url'            => 'https://news.wfsu.org/wfsu-local-news/2026-03-28/one-person-arrested-at-tallahassee-no-kings-protest',
                'source'         => 'WFSU',
                'category'       => 'arrest',
                'published_at'   => '2026-03-28',
                'location_label' => 'Tallahassee, FL',
                'lat'            => 30.4385,
                'lng'            => -84.2820,
            ],
            // ---- Georgia (top-up) ----
            [
                'title'          => 'About 29 arrested for obstruction at a "No Kings" anti-ICE march in Doraville, Georgia, after police declared an unlawful assembly and used tear gas to clear a roadway blockade',
                'url'            => 'https://www.axios.com/local/atlanta/2025/06/15/ice-protest-arrests-dekalb-atlanta-georgia',
                'source'         => 'Axios Atlanta',
                'category'       => 'arrest',
                'published_at'   => '2025-06-14',
                'location_label' => 'Doraville, GA',
                'lat'            => 33.8950,
                'lng'            => -84.2800,
            ],
            // ---- Nevada (top-up) ----
            [
                'title'          => 'About 94 arrested for unlawful assembly at a downtown Las Vegas anti-ICE protest after a police dispersal order; the city later declined to prosecute most cases',
                'url'            => 'https://www.reviewjournal.com/crime/nearly-100-arrested-in-downtown-las-vegas-ice-protest-police-say-3384453/',
                'source'         => 'Las Vegas Review-Journal',
                'category'       => 'arrest',
                'published_at'   => '2025-06-11',
                'location_label' => 'Las Vegas, NV',
                'lat'            => 36.1700,
                'lng'            => -115.1390,
            ],
            // ---- Ohio (top-up) ----
            [
                'title'          => 'Three arrested for trespassing after protesters surrounded a Border Patrol recruiting booth at an Ohio State University career fair in Columbus',
                'url'            => 'https://www.wosu.org/politics-government/2026-01-20/three-people-arrested-at-ohio-state-career-fair-during-customs-and-border-protection-protest',
                'source'         => 'WOSU',
                'category'       => 'arrest',
                'published_at'   => '2026-01-20',
                'location_label' => 'Columbus, OH',
                'lat'            => 40.0010,
                'lng'            => -83.0085,
            ],
            // ---- Colorado (top-up) ----
            [
                'title'          => 'Hundreds protested the ICE detention of a Colombian father and his children in Durango, Colorado; a CBP officer was later charged with assault for throwing a protester who was filming down an embankment',
                'url'            => 'https://www.cbsnews.com/colorado/news/customs-border-protection-agent-charged-assault-durango-colorado-protest/',
                'source'         => 'CBS Colorado',
                'category'       => 'protest',
                'published_at'   => '2025-10-28',
                'location_label' => 'Durango, CO',
                'lat'            => 37.2753,
                'lng'            => -107.8801,
            ],
            // ---- Louisiana (top-up) ----
            [
                'title'          => 'Seven LSU students arrested protesting board appointees of Governor Jeff Landry at a university presidential-search meeting in Baton Rouge; they wore "No MAGA President" shirts and sat down rather than resist',
                'url'            => 'https://kpel965.com/lsu-students-arrested/',
                'source'         => 'KPEL',
                'category'       => 'arrest',
                'published_at'   => '2025-10-02',
                'location_label' => 'Baton Rouge, LA',
                'lat'            => 30.4133,
                'lng'            => -91.1800,
            ],
            // ---- Massachusetts (top-up) ----
            [
                'title'          => 'Two arrested plastering anti-ICE posters on a Citizens Bank branch in Boston, part of a campaign over its financing of ICE detention operators',
                'url'            => 'https://www.bostonglobe.com/2026/05/11/metro/ice/',
                'source'         => 'The Boston Globe',
                'category'       => 'arrest',
                'published_at'   => '2026-05-11',
                'location_label' => 'Boston, MA',
                'lat'            => 42.3496,
                'lng'            => -71.0786,
            ],
        ];

        $created = 0;
        foreach ($cases as $case) {
            $link = DashboardLink::firstOrCreate(
                ['url' => $case['url']],
                array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
            );

            if ($link->wasRecentlyCreated) {
                $created++;
                $this->info("Added: {$case['title']}");
            } else {
                $this->line("Skipped (already present): {$case['title']}");
            }
        }

        $this->info("Done. {$created} new case(s) added; ".(count($cases) - $created).' already present.');

        return self::SUCCESS;
    }
}
