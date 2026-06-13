<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the Daily Kos analysis "How ICE Is Still Terrorizing Los Angeles" to the
 * dashboard as an "other" Los Angeles marker. This is a commentary/analysis
 * piece on the ongoing ICE enforcement surge in LA rather than a single
 * discrete incident; added at the maintainer's direction. Source label matches
 * the dashboard's existing Daily Kos entries. Matched on URL with
 * updateOrCreate, so the command is idempotent.
 */
class AddDailyKosLaIceDashboardCase extends Command {
    protected $signature = 'dashboard:add-dailykos-la-ice-case';
    protected $description = 'Add the Daily Kos "How ICE Is Still Terrorizing Los Angeles" piece to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'How ICE Is Still Terrorizing Los Angeles',
                'url'            => 'https://www.dailykos.com/stories/2026/6/10/800052773/stateandlocal/how-ice-is-still-terrorizing-los-angeles/',
                'source'         => 'Daily Kos',
                'category'       => 'other',
                'published_at'   => '2026-06-10',
                'location_label' => 'Los Angeles, CA',
                'lat'            => 34.0522,
                'lng'            => -118.2437,
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
