<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds notable protest actions at U.S. STATE capitol buildings to the dashboard
 * as DashboardLink markers -- both arrests (category "arrest") and large
 * no-arrest demonstrations (category "protest"). In-window (on/after May 7,
 * 2025), sourced from public reporting; matched on URL so the command is
 * idempotent.
 */
class AddStateCapitolDashboardCases extends Command {
    protected $signature = 'dashboard:add-state-capitol-cases';
    protected $description = 'Add state-capitol protest actions (arrests and large no-arrest rallies) to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Three arrested in gallery protests at the Tennessee Capitol over a redistricting map splitting majority-Black Memphis; Rep. Justin Jones burned a paper Confederate flag',
                'url'            => 'https://nashvillebanner.com/2026/05/07/tennessee-congressional-redistricting-confederate-flag/',
                'source'         => 'Nashville Banner',
                'category'       => 'arrest',
                'published_at'   => '2026-05-07',
                'location_label' => 'Nashville, TN',
                'lat'            => 36.1659,
                'lng'            => -86.7844,
            ],
            [
                'title'          => 'About 25,000 rallied at the Minnesota Capitol for the "No Kings" day of protest (no arrests; held amid the Hortman shooting)',
                'url'            => 'https://sahanjournal.com/democracy-politics/minnesota-capitol-no-kings-rally-draws-thousands/',
                'source'         => 'Sahan Journal',
                'category'       => 'protest',
                'published_at'   => '2025-06-14',
                'location_label' => 'St. Paul, MN',
                'lat'            => 44.9553,
                'lng'            => -93.1022,
            ],
            [
                'title'          => 'About 5,000 rallied at the Arizona Capitol for the "No Kings" protest against mass deportations (no arrests)',
                'url'            => 'https://azmirror.com/2025/06/14/anti-trump-no-kings-rallies-draw-massive-crowds-despite-arizonas-sweltering-summer-heat/',
                'source'         => 'Arizona Mirror',
                'category'       => 'protest',
                'published_at'   => '2025-06-14',
                'location_label' => 'Phoenix, AZ',
                'lat'            => 33.4480,
                'lng'            => -112.0968,
            ],
            [
                'title'          => 'Thousands rallied at the Washington Capitol in Olympia for the "No Kings" day of protest (no arrests)',
                'url'            => 'https://washingtonstatestandard.com/2025/06/14/no-kings-rally-in-olympia-draws-thousands-to-state-capitol/',
                'source'         => 'Washington State Standard',
                'category'       => 'protest',
                'published_at'   => '2025-06-14',
                'location_label' => 'Olympia, WA',
                'lat'            => 47.0357,
                'lng'            => -122.9050,
            ],
            [
                'title'          => 'About 200 rallied at the Florida Old Capitol in Tallahassee against ICE immigration raids (no arrests)',
                'url'            => 'https://www.wctv.tv/2025/05/31/its-unfair-more-than-150-rally-capital-city-protest-unjust-immigration-raids/',
                'source'         => 'WCTV',
                'category'       => 'protest',
                'published_at'   => '2025-05-30',
                'location_label' => 'Tallahassee, FL',
                'lat'            => 30.4383,
                'lng'            => -84.2807,
            ],
            [
                'title'          => 'Hundreds rallied at the Missouri Capitol against the legislature rolling back voter-approved abortion rights and paid sick leave (no arrests)',
                'url'            => 'https://www.stlpr.org/government-politics-issues/2025-05-15/missouri-capitol-rally-abortion-paid-sick-leave',
                'source'         => 'St. Louis Public Radio',
                'category'       => 'protest',
                'published_at'   => '2025-05-15',
                'location_label' => 'Jefferson City, MO',
                'lat'            => 38.5790,
                'lng'            => -92.1730,
            ],
            [
                'title'          => 'About 15,000-20,000 rallied at the Wisconsin Capitol for the "No Kings" protest over ICE raids and healthcare (no arrests)',
                'url'            => 'https://isthmus.com/news/news/ice-raids-healthcare-concerns-top-protesters-concerns-in-madison-No-Kings-rally/',
                'source'         => 'Isthmus',
                'category'       => 'protest',
                'published_at'   => '2025-10-18',
                'location_label' => 'Madison, WI',
                'lat'            => 43.0747,
                'lng'            => -89.3844,
            ],
            [
                'title'          => 'More than 10,000 rallied at the Ohio Statehouse for the "No Kings" protest on immigration and democracy (no arrests)',
                'url'            => 'https://www.statenews.org/government-politics/2025-10-19/thousands-rallied-in-no-kings-protests-in-ohio-including-huge-turnout-at-statehouse',
                'source'         => 'Ohio Statehouse News Bureau',
                'category'       => 'protest',
                'published_at'   => '2025-10-18',
                'location_label' => 'Columbus, OH',
                'lat'            => 39.9612,
                'lng'            => -82.9988,
            ],
            [
                'title'          => 'About 6,000 rallied at the Oregon Capitol in Salem for the "No Kings" day of protest (no arrests)',
                'url'            => 'https://www.salemreporter.com/2025/10/18/salem-no-kings-rally-draws-thousands-to-oppose-trump/',
                'source'         => 'Salem Reporter',
                'category'       => 'protest',
                'published_at'   => '2025-10-18',
                'location_label' => 'Salem, OR',
                'lat'            => 44.9382,
                'lng'            => -123.0301,
            ],
            [
                'title'          => 'About 13 arrested at an anti-ICE protest at the Texas Capitol in Austin after troopers used tear gas; charges ranged from obstruction and graffiti to assault',
                'url'            => 'https://www.kut.org/crime-justice/2025-06-10/austin-tx-immigration-ice-trump-mass-deportation-protest-texas-capitol',
                'source'         => 'KUT',
                'category'       => 'arrest',
                'published_at'   => '2025-06-09',
                'location_label' => 'Austin, TX',
                'lat'            => 30.2747,
                'lng'            => -97.7404,
            ],
            [
                'title'          => 'Seventeen arrested as a 1,000-strong "ICE Out" rally at the Colorado Capitol in Denver was dispersed with smoke; charges ranged from obstruction and graffiti to assault',
                'url'            => 'https://www.coloradopolitics.com/colorado-in-dc/denver-protest-arrests-state-capitol/article_727b84fd-a17f-5c68-b716-ea952541141e.html',
                'source'         => 'Colorado Politics',
                'category'       => 'arrest',
                'published_at'   => '2025-06-10',
                'location_label' => 'Denver, CO',
                'lat'            => 39.7397,
                'lng'            => -104.9847,
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
