<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the U.S. Press Freedom Tracker incident in which Jarom Smith of the
 * Lyon County Observer was served a search warrant for his outlet's Facebook
 * data after photographing an old police shooting range in Emporia, Kansas.
 * Authorities withdrew the warrant after Smith retained a First Amendment
 * attorney. Categorized "other" (a press-freedom / search-warrant item).
 * Source label matches the dashboard's existing Press Freedom Tracker entries.
 * Matched on URL with updateOrCreate, so the command is idempotent.
 */
class AddKansasJournalistDashboardCase extends Command {
    protected $signature = 'dashboard:add-kansas-journalist-case';
    protected $description = 'Add the Emporia, KS journalist search-warrant press-freedom incident to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Kansas journalist hit with search warrant after photographing police range',
                'url'            => 'https://pressfreedomtracker.us/all-incidents/kansas-journalist-hit-with-search-warrant-after-photographing-police-range/',
                'source'         => 'U.S. Press Freedom Tracker',
                'category'       => 'other',
                'published_at'   => '2025-11-14',
                'location_label' => 'Emporia, KS',
                'lat'            => 38.4039,
                'lng'            => -96.1817,
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
