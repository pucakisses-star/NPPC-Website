<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds smaller protests in mid-size cities and small towns across the country as
 * DashboardLink map markers — filling in geographic breadth the tracker was
 * missing (it skewed toward big-metro events). These are real, locally-reported
 * demonstrations from the spring 2026 protest waves: the March 28, 2026
 * "No Kings" day, May 1 May Day ("Workers Over Billionaires"), and a couple of
 * Deep South voting-rights actions.
 *
 * Every town here is NET-NEW (checked against existing markers — places already
 * on the map such as Providence, New Haven, Charlottesville, Knoxville, Grand
 * Island, Burlington/Williston VT and Charleston WV were deliberately excluded).
 * Each row has a local-news source; coordinates are town-center/venue level.
 * Most were peaceful with no arrests (category 'protest'); Springfield, MO saw
 * citations and one arrest (category 'arrest'). The Mason City, IA headcount was
 * paywalled and is left out of the title. Idempotent via updateOrCreate on URL.
 */
class AddSmallTownProtestsDashboardCases extends Command
{
    protected $signature = 'dashboard:add-small-town-protests';

    protected $description = 'Add smaller mid-size-city and small-town protests (2026) to the dashboard map';

    public function handle(): int
    {
        $cases = [
            // ── May Day, May 1, 2026 ──
            [
                'title' => "Hundreds rally and march in Pack Square Park for May Day 'Workers Over Billionaires'",
                'url' => 'https://wlos.com/news/local/may-day-asheville-protest-downtown-pack-square-park-workers-billionaries-education-labor-nationwide-community-university-north-carolina-photos-live',
                'source' => 'WLOS',
                'category' => 'protest',
                'published_at' => '2026-05-01',
                'location_label' => 'Pack Square Park, Asheville, NC',
                'lat' => 35.5950,
                'lng' => -82.5510,
            ],
            [
                'title' => 'May Day protest fills the South Carolina State House grounds',
                'url' => 'https://www.wistv.com/2026/05/01/watch-may-day-protest-held-sc-state-house/',
                'source' => 'WIS-TV',
                'category' => 'protest',
                'published_at' => '2026-05-01',
                'location_label' => 'South Carolina State House, Columbia, SC',
                'lat' => 34.0007,
                'lng' => -81.0348,
            ],

            // ── Deep South voting-rights actions ──
            [
                'title' => "About 400 march silently to the Edmund Pettus Bridge in an 'All Roads Lead to the South' voting-rights action",
                'url' => 'https://www.fox10tv.com/2026/05/16/voting-rights-advocates-gather-selma-all-roads-lead-south-protest-bridge-walk/',
                'source' => 'FOX10 News',
                'category' => 'protest',
                'published_at' => '2026-05-16',
                'location_label' => 'Edmund Pettus Bridge, Selma, AL',
                'lat' => 32.4070,
                'lng' => -87.0210,
            ],
            [
                'title' => 'Thousands rally at the Old Capitol against redistricting and for voting rights',
                'url' => 'https://mississippitoday.org/2026/05/20/mississippi-redistricting-voting-rights-rally/',
                'source' => 'Mississippi Today',
                'category' => 'protest',
                'published_at' => '2026-05-20',
                'location_label' => 'Old Capitol, Jackson, MS',
                'lat' => 32.3036,
                'lng' => -90.1820,
            ],

            // ── "No Kings" — March 28, 2026 ──
            [
                'title' => "Hundreds line State Road and Losey Boulevard for a 'No Kings' rally",
                'url' => 'https://www.wxow.com/news/la-crosse/hundreds-turn-out-in-la-crosse-no-kings-rally/article_a2fece9e-f676-4b70-abfc-8817aca467de.html',
                'source' => 'WXOW',
                'category' => 'protest',
                'published_at' => '2026-03-28',
                'location_label' => 'State Rd & Losey Blvd, La Crosse, WI',
                'lat' => 43.8014,
                'lng' => -91.2396,
            ],
            [
                'title' => "About 405 march through downtown Honesdale in a 'No Kings' protest",
                'url' => 'https://www.aol.com/articles/no-kings-crowd-honesdale-makes-000804615.html',
                'source' => 'The Wayne Independent',
                'category' => 'protest',
                'published_at' => '2026-03-28',
                'location_label' => 'Honesdale, PA',
                'lat' => 41.5773,
                'lng' => -75.2585,
            ],
            [
                'title' => "About 400 rally at the Madison County courthouse for 'No Kings'",
                'url' => 'https://tennesseelookout.com/2026/03/28/anti-trump-no-kings-rallies-draw-thousands-across-tennessee/',
                'source' => 'Tennessee Lookout',
                'category' => 'protest',
                'published_at' => '2026-03-28',
                'location_label' => 'Madison County Courthouse, Jackson, TN',
                'lat' => 35.6145,
                'lng' => -88.8139,
            ],
            [
                'title' => "About 200 protest at the post office in South Dakota's capital for 'No Kings'",
                'url' => 'https://www.newsfromthestates.com/article/enough-tyranny-no-kings-protesters-take-streets-south-dakota',
                'source' => 'South Dakota Searchlight',
                'category' => 'protest',
                'published_at' => '2026-03-28',
                'location_label' => 'Pierre, SD',
                'lat' => 44.3683,
                'lng' => -100.3510,
            ],
            [
                'title' => "About 400 rally on the Kenai Peninsula in Soldotna's largest 'No Kings' turnout yet",
                'url' => 'https://www.peninsulaclarion.com/2026/04/01/no-consent-third-nationwide-no-kings-protest-draws-thousands-across-alaska/',
                'source' => 'Peninsula Clarion',
                'category' => 'protest',
                'published_at' => '2026-03-28',
                'location_label' => 'Soldotna, AK',
                'lat' => 60.4877,
                'lng' => -151.0583,
            ],
            [
                'title' => "Hundreds rally at the Monroe County Courthouse for 'No Kings'",
                'url' => 'https://www.aol.com/articles/hundreds-gather-courthouse-during-peaceful-231459054.html',
                'source' => 'The Herald-Times',
                'category' => 'protest',
                'published_at' => '2026-03-28',
                'location_label' => 'Monroe County Courthouse, Bloomington, IN',
                'lat' => 39.1653,
                'lng' => -86.5264,
            ],
            [
                'title' => "Hundreds march to Odd Fellows Park in the mountain town of Gunnison for 'No Kings'",
                'url' => 'https://coloradosun.com/2026/03/28/no-kings-colorado-rally-politics/',
                'source' => 'The Colorado Sun',
                'category' => 'protest',
                'published_at' => '2026-03-28',
                'location_label' => 'Gunnison, CO',
                'lat' => 38.5458,
                'lng' => -106.9253,
            ],
            [
                'title' => "Hundreds gather at the federal courthouse and line the Central Avenue bridge for 'No Kings'",
                'url' => 'https://www.krtv.com/news/montana-and-regional-news/no-kings-rallies-held-across-montana',
                'source' => 'KRTV',
                'category' => 'protest',
                'published_at' => '2026-03-28',
                'location_label' => 'Great Falls, MT',
                'lat' => 47.5053,
                'lng' => -111.3008,
            ],
            [
                'title' => "Hundreds line Route 7 in Rutland for a 'No Kings' rally",
                'url' => 'https://www.wcax.com/2026/03/28/no-kings-day-rallies-draw-crowds-across-vermont/',
                'source' => 'WCAX',
                'category' => 'protest',
                'published_at' => '2026-03-28',
                'location_label' => 'Rutland, VT',
                'lat' => 43.6106,
                'lng' => -72.9726,
            ],
            [
                'title' => "About 850 rally on Wooster's public square for 'No Kings'",
                'url' => 'https://www.yourohionews.com/wayne-county/wooster-rally-draws-850-for-no-kings/1026464',
                'source' => 'Your Ohio News',
                'category' => 'protest',
                'published_at' => '2026-03-28',
                'location_label' => 'Public Square, Wooster, OH',
                'lat' => 40.8051,
                'lng' => -81.9351,
            ],
            [
                'title' => "400-500 demonstrate on the Johnson County Courthouse lawn for 'No Kings'",
                'url' => 'https://showmeprogress.com/2026/03/28/no-kings-warrensburg-missouri-march-28-2026/',
                'source' => 'Show Me Progress',
                'category' => 'protest',
                'published_at' => '2026-03-28',
                'location_label' => 'Johnson County Courthouse, Warrensburg, MO',
                'lat' => 38.7628,
                'lng' => -93.7360,
            ],
            [
                'title' => "21 cited and one arrested at a Springfield 'No Kings' rally after marchers entered the roadway",
                'url' => 'https://sgfcitizen.org/government/crime/springfield-police-no-kings-rally/',
                'source' => 'Springfield Daily Citizen',
                'category' => 'arrest',
                'published_at' => '2026-03-28',
                'location_label' => 'E. Battlefield Rd, Springfield, MO',
                'lat' => 37.2090,
                'lng' => -93.2923,
            ],
            [
                'title' => "About 280 occupy the four corners of 9th and Main in Winfield for 'No Kings'",
                'url' => 'https://www.aol.com/news/no-kings-protest-occupies-four-235324021.html',
                'source' => 'The Hutchinson News',
                'category' => 'protest',
                'published_at' => '2026-03-28',
                'location_label' => '9th & Main, Winfield, KS',
                'lat' => 37.2398,
                'lng' => -96.9956,
            ],
            [
                'title' => "About 300 gather in Chardon's town square for 'No Kings'",
                'url' => 'https://www.ideastream.org/government-politics/2026-03-28/northeast-ohio-no-kings-protests-target-immigration-voting-policies',
                'source' => 'Ideastream Public Media',
                'category' => 'protest',
                'published_at' => '2026-03-28',
                'location_label' => 'Chardon Square, Chardon, OH',
                'lat' => 41.5803,
                'lng' => -81.2007,
            ],
            [
                'title' => "Hundreds rally at Central Park in Mason City for 'No Kings'",
                'url' => 'https://globegazette.com/news/local/article_fa847400-63af-4593-9cd1-c149ce317aca.html',
                'source' => 'Globe Gazette',
                'category' => 'protest',
                'published_at' => '2026-03-28',
                'location_label' => 'Central Park, Mason City, IA',
                'lat' => 43.1536,
                'lng' => -93.2010,
            ],

            // ── "No Kings" — March 28, 2026 (Rio Grande Valley & Pensacola) ──
            [
                'title' => "'No Kings' rally at Tim Cole Memorial Park",
                'url' => 'https://www.everythinglubbock.com/news/local-news/no-kings-protest-set-to-happen-saturday-in-lubbock-and-organizers-say-they-are-expecting-big-crowd/',
                'source' => 'EverythingLubbock (KLBK/KAMC)',
                'category' => 'protest',
                'published_at' => '2026-03-28',
                'location_label' => 'Tim Cole Memorial Park, Lubbock, TX',
                'lat' => 33.5850,
                'lng' => -101.8780,
            ],
            [
                'title' => "'No Kings' rally at the federal courthouse against ICE and deportations",
                'url' => 'https://www.valleycentral.com/news/local-news/mcallen-no-kings-protest-draws-hundreds/',
                'source' => 'ValleyCentral (KVEO)',
                'category' => 'protest',
                'published_at' => '2026-03-28',
                'location_label' => 'Federal courthouse, McAllen, TX',
                'lat' => 26.2030,
                'lng' => -98.2300,
            ],
            [
                'title' => "'No Kings' rally on Airport Boulevard as part of a Northwest Florida day of action",
                'url' => 'https://weartv.com/news/local/protesters-rally-in-pensacola-for-no-kings-demonstration-organizers-say-turnout-unclear',
                'source' => 'WEAR-TV',
                'category' => 'protest',
                'published_at' => '2026-03-28',
                'location_label' => 'Pensacola, FL',
                'lat' => 30.4210,
                'lng' => -87.2170,
            ],
        ];

        $created = 0;
        $updated = 0;
        foreach ($cases as $case) {
            $link = DashboardLink::updateOrCreate(
                ['url' => $case['url']],
                array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
            );

            if ($link->wasRecentlyCreated) {
                $created++;
                $this->info("Added: {$case['location_label']}");
            } else {
                $updated++;
                $this->line("Updated: {$case['location_label']}");
            }
        }

        $this->info("Done. {$created} added, {$updated} updated ({$created} new markers across the map).");

        return self::SUCCESS;
    }
}
