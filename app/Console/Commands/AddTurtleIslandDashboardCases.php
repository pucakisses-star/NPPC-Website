<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the "Turtle Island Liberation Front" New Year's Eve bombing-plot case
 * to the dashboard as a DashboardLink arrest marker. Four people — Audrey
 * Carroll, Zachary Page, Dante Gaffield, and Tina Lai — were arrested in the
 * Mojave Desert on Dec. 12, 2025 and charged in the Central District of
 * California. The Intercept later reported that a longtime paid FBI informant
 * was instrumental in the case. Categorized "arrest" and located in Los
 * Angeles. Matched on URL with updateOrCreate, so it is idempotent.
 */
class AddTurtleIslandDashboardCases extends Command {
    protected $signature = 'dashboard:add-turtle-island-cases';
    protected $description = 'Add the Turtle Island Liberation Front bombing-plot arrests to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Four Defendants Arrested for Alleged Anti-Capitalist and Anti-Government Plot to Bomb U.S. Companies on New Year\'s Eve',
                'url'            => 'https://www.justice.gov/opa/pr/four-defendants-arrested-alleged-anti-capitalist-and-anti-government-plot-bomb-us-companies',
                'source'         => 'U.S. Department of Justice',
                'category'       => 'arrest',
                'published_at'   => '2025-12-15',
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
