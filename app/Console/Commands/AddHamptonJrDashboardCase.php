<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the June 2026 arrest and release of Fred Hampton Jr. — chairman of the
 * Black Panther Party Cubs / Prisoners of Conscience Committee and son of the
 * assassinated Panther leader Fred Hampton — to the dashboard. He was arrested
 * June 2, 2026 in Cook County, Illinois on a "violation of a no-contact order"
 * charge tied to his attendance at his father's December 3, 2025 memorial, and
 * released June 3 after a judge found the state had not shown he posed a
 * threat. Categorized "arrest"; located in Chicago. (Hampton Jr. already has a
 * prisoner profile.) Matched on URL with updateOrCreate, so the command is
 * idempotent.
 */
class AddHamptonJrDashboardCase extends Command {
    protected $signature = 'dashboard:add-hampton-jr-case';
    protected $description = 'Add the June 2026 Fred Hampton Jr. arrest/release to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Fred Hampton Jr. freed from Cook County Jail',
                'url'            => 'https://chicagocrusader.com/hampton-jr-freed-from-county-jail-fred-hampton-jr/',
                'source'         => 'Chicago Crusader',
                'category'       => 'arrest',
                'published_at'   => '2026-06-04',
                'location_label' => 'Chicago, IL',
                'lat'            => 41.8781,
                'lng'            => -87.6298,
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
