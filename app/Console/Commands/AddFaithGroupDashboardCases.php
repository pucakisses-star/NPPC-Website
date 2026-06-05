<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds faith-leader / clergy protest arrest cases to the dashboard as
 * DashboardLink markers (map pins + newswire). Each is a nonviolent civil-
 * disobedience arrest by clergy or religious groups, dated within the dashboard
 * timeline window (May 7, 2025 onward). Sourced from public reporting; matched
 * on URL so the command is idempotent and safe to re-run.
 */
class AddFaithGroupDashboardCases extends Command {
    protected $signature = 'dashboard:add-faith-cases';
    protected $description = 'Add faith-leader / clergy protest arrest cases to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Bishop William Barber and faith leaders arrested in the Capitol Rotunda during a "Moral Monday" protest against Medicaid cuts',
                'url'            => 'https://episcopalnewsservice.org/2025/06/03/faith-leaders-health-care-advocates-arrested-while-protesting-gop-budget-bill-in-capitol/',
                'source'         => 'Episcopal News Service',
                'category'       => 'protest',
                'published_at'   => '2025-06-02',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8899,
                'lng'            => -77.0091,
            ],
            [
                'title'          => 'Eight rabbis arrested outside the Israeli Consulate in Manhattan demanding Gaza aid and a ceasefire',
                'url'            => 'https://www.timesofisrael.com/35-us-rabbis-arrested-in-separate-nyc-and-dc-demonstrations-for-gaza-food-aid/',
                'source'         => 'Times of Israel',
                'category'       => 'protest',
                'published_at'   => '2025-07-28',
                'location_label' => 'Manhattan, NY',
                'lat'            => 40.7510,
                'lng'            => -73.9690,
            ],
            [
                'title'          => 'Twenty-seven rabbis arrested in a sit-in at the office of Sen. John Thune demanding food aid for Gaza',
                'url'            => 'https://www.jta.org/2025/07/29/united-states/35-rabbis-arrested-in-nyc-and-dc-protests-for-gaza-food-aid',
                'source'         => 'JTA',
                'category'       => 'protest',
                'published_at'   => '2025-07-29',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8927,
                'lng'            => -77.0046,
            ],
            [
                'title'          => 'Nearly 60 arrested as Rabbis for Ceasefire blocked the Brooklyn Bridge in a Yom Kippur Gaza protest',
                'url'            => 'https://www.commondreams.org/news/rabbi-protest-new-york',
                'source'         => 'Common Dreams',
                'category'       => 'protest',
                'published_at'   => '2025-10-02',
                'location_label' => 'Brooklyn, NY',
                'lat'            => 40.7061,
                'lng'            => -73.9969,
            ],
            [
                'title'          => 'About 100 clergy arrested blocking ICE deportation flights at the Minneapolis-St. Paul airport',
                'url'            => 'https://www.cbsnews.com/minnesota/news/clergy-members-arrested-minneapolis-st-paul-international-airport/',
                'source'         => 'CBS News',
                'category'       => 'protest',
                'published_at'   => '2026-01-23',
                'location_label' => 'Minneapolis, MN',
                'lat'            => 44.8848,
                'lng'            => -93.2223,
            ],
            [
                'title'          => 'More than 50 faith leaders arrested in a "Pray with Your Feet" sit-in against ICE funding at the Hart Senate building',
                'url'            => 'https://www.ucc.org/ucc-clergy-members-among-those-arrested-at-pray-with-your-feet-day-of-action-ice-out-of-our-communities/',
                'source'         => 'UCC',
                'category'       => 'protest',
                'published_at'   => '2026-01-29',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8920,
                'lng'            => -77.0052,
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
