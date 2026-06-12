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
                'title'          => '8 arrested in FBI raids related to pro-Palestine advocacy at University of Michigan',
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
        $updated = 0;
        foreach ($cases as $case) {
            $link = DashboardLink::updateOrCreate(
                ['url' => $case['url']],
                array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
            );

            if ($link->wasRecentlyCreated) {
                $created++;
                $this->info("Added: {$case['title']}");
            } else {
                $updated++;
                $this->line("Updated: {$case['title']}");
            }
        }

        $this->info("Done. {$created} added, {$updated} updated.");

        return self::SUCCESS;
    }
}
