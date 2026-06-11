<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the June 2026 federal indictment and FBI raids targeting eight
 * pro-Palestinian activists tied to the University of Michigan divestment
 * campaign to the dashboard as a DashboardLink arrest marker. A grand-jury
 * indictment in the Eastern District of Michigan (announced by FBI Director
 * Kash Patel and unsealed June 10, 2026) charged conspiracy to transmit
 * threats, witness intimidation, and destruction of property; seven of the
 * eight defendants were arrested in raids in Washtenaw County (Ypsilanti).
 * The charges are allegations. Matched on URL so the command is idempotent
 * and safe to re-run.
 */
class AddUmichRaidDashboardCases extends Command {
    protected $signature = 'dashboard:add-umich-raid-cases';
    protected $description = 'Add the June 2026 U-Michigan pro-Palestine FBI raid / indictment case to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Eight pro-Palestinian activists tied to the University of Michigan divestment campaign were indicted in federal court and seven were arrested in FBI raids in Washtenaw County, charged with conspiracy to transmit threats, witness intimidation, and destruction of property; the defendants are Paige Feyock, Amatullah Hakim, Zainab Hakim, Ahmet Korkaya, Miriam Odeh, Alexander Sepulveda, Colin Weger, and Jonathan Zou',
                'url'            => 'https://www.mlive.com/news/ann-arbor/2026/06/8-arrested-in-fbi-raids-related-to-pro-palestine-advocacy-at-university-of-michigan.html',
                'source'         => 'MLive',
                'category'       => 'arrest',
                'published_at'   => '2026-06-10',
                'location_label' => 'Ann Arbor, MI',
                'lat'            => 42.2808,
                'lng'            => -83.7430,
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
