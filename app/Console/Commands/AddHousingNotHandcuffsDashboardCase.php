<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the June 11, 2026 Housing Not Handcuffs direct action in Washington,
 * D.C., where 75+ housing advocates disrupted the Cicero Institute's
 * homelessness conference — blocking the doors and releasing research tying
 * Cicero leaders to private-prison and surveillance industries — in protest of
 * the criminalization of homelessness. Categorized "other" (a protest / direct
 * action, matching the dashboard's other rally/blockade entries). Matched on
 * URL with updateOrCreate, so the command is idempotent.
 */
class AddHousingNotHandcuffsDashboardCase extends Command {
    protected $signature = 'dashboard:add-housing-not-handcuffs-case';
    protected $description = 'Add the Housing Not Handcuffs Cicero Institute conference disruption to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Housing advocates disrupt Cicero Institute homelessness conference in D.C.',
                'url'            => 'https://housingnothandcuffs.org/2026/06/11/6112026/',
                'source'         => 'Housing Not Handcuffs',
                'category'       => 'other',
                'published_at'   => '2026-06-11',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8951,
                'lng'            => -77.0364,
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
