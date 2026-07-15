<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds dashboard newswire/map entries for events from late June through
 * mid-July 2026: the Lincoln Memorial Reflecting Pool arrests and
 * arraignments, the July Fourth "Not Another 250" action in Atlanta, a
 * DOJ material-support arrest in upstate New York, and the widening
 * data-center protest wave. Sourced from public reporting; matched on URL
 * so the command is idempotent and re-runnable.
 */
class AddMidJuly2026DashboardCases extends Command {
    protected $signature = 'dashboard:add-mid-july-2026-cases';
    protected $description = 'Add mid-July 2026 protest & prosecution events to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'At least seven arrested over the Lincoln Memorial Reflecting Pool "vandalism" as Interior confirms seven federal citations',
                'url'            => 'https://www.forbes.com/sites/zacharyfolk/2026/06/25/seventh-person-arrested-for-reflecting-pool-vandalism/',
                'source'         => 'Forbes',
                'category'       => 'arrest',
                'published_at'   => '2026-06-25',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8893,
                'lng'            => -77.0470,
            ],
            [
                'title'          => 'Catherine Washburn of Irondequoit arrested on a federal material-support charge over the Direct Action Movement for Palestinian Liberation; DOJ seeks up to 20 years',
                'url'            => 'https://www.justice.gov/opa/pr/upstate-new-york-woman-arrested-charged-attempting-provide-material-support-palestine',
                'source'         => 'U.S. Department of Justice',
                'category'       => 'arrest',
                'published_at'   => '2026-06-30',
                'location_label' => 'Irondequoit, NY',
                'lat'            => 43.2134,
                'lng'            => -77.5798,
            ],
            [
                'title'          => 'Five Black women arrested at Atlanta\'s Peachtree Road Race after a locked-arms "Not Another 250" action on the July Fourth course',
                'url'            => 'https://www.fox5atlanta.com/news/atlanta-police-arrest-5-protesters-during-peachtree-road-race',
                'source'         => 'FOX 5 Atlanta',
                'category'       => 'arrest',
                'published_at'   => '2026-07-04',
                'location_label' => 'Atlanta, GA',
                'lat'            => 33.7900,
                'lng'            => -84.3780,
            ],
            [
                'title'          => 'Data-center protests spread across the Heartland as statehouses rush to regulate development',
                'url'            => 'https://www.mprnews.org/story/2026/07/05/as-people-protest-data-centers-across-the-heartland-lawmakers-rush-to-regulate-development',
                'source'         => 'MPR News',
                'category'       => 'protest',
                'published_at'   => '2026-07-05',
            ],
            [
                'title'          => 'Olympian David Hearn pleads not guilty to the Reflecting Pool felony; three co-defendants arraigned on misdemeanor destruction charges',
                'url'            => 'https://www.nbcnews.com/news/us-news/us-olympian-david-hearn-arraigned-charges-reflecting-pool-vandalism-ca-rcna353638',
                'source'         => 'NBC News',
                'category'       => 'prosecution',
                'published_at'   => '2026-07-09',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8951,
                'lng'            => -77.0364,
            ],
            [
                'title'          => 'Humans First maps a July 18 national day of protest against data centers in more than 50 cities',
                'url'            => 'https://www.newsweek.com/national-day-of-protest-against-data-centers-on-july-18map-list-of-cities-12150084',
                'source'         => 'Newsweek',
                'category'       => 'protest',
                'published_at'   => '2026-07-09',
            ],
            [
                'title'          => 'Richmond protesters demand a statewide moratorium on new Virginia data-center approvals',
                'url'            => 'https://wset.com/news/local/richmond-protesters-urge-virginia-to-halt-new-data-center-approvals-statewide-botetourt-county-google-july-2026',
                'source'         => 'WSET',
                'category'       => 'protest',
                'published_at'   => '2026-07-12',
                'location_label' => 'Richmond, VA',
                'lat'            => 37.5388,
                'lng'            => -77.4336,
            ],
        ];

        $added = 0;
        foreach ($cases as $case) {
            $link = DashboardLink::firstOrCreate(
                ['url' => $case['url']],
                array_merge($case, ['published_at' => Carbon::parse($case['published_at'])])
            );
            if ($link->wasRecentlyCreated) {
                $added++;
                $this->info('Added: '.$case['title']);
            } else {
                $this->line('Exists: '.$case['title']);
            }
        }

        $this->info("Done. {$added} new dashboard entr".($added === 1 ? 'y' : 'ies').' added.');

        return self::SUCCESS;
    }
}
