<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * A fourth batch of dashboard newswire items (surfaced June 2026):
 *
 *  - The FBI arrest of five men charged in an alleged plot to attack the
 *    UFC "Freedom 250" event at the White House with explosive drones
 *    (New York Post).
 *  - President Trump publicly demanding that U.S. District Judge Christopher
 *    Cooper "face charges" after Cooper ordered Trump's name removed from the
 *    Kennedy Center and blocked its closure — executive pressure on the
 *    judiciary (The Independent).
 *
 * Matched on URL with updateOrCreate, so the command is idempotent.
 */
class AddJune2026Batch4DashboardCases extends Command
{
    protected $signature = 'dashboard:add-june-2026-batch-4';

    protected $description = 'Add the White House UFC drone-plot arrests and the Trump/Kennedy Center judge-charges item to the dashboard';

    public function handle(): int
    {
        $cases = [
            [
                'title' => 'Five men arrested and charged in alleged plot to attack White House UFC event with explosive drones',
                'url' => 'https://nypost.com/2026/06/16/us-news/fbi-arrests-5-people-in-connection-with-drone-attack-plot-against-white-house-ufc-freedom-250-event/',
                'source' => 'New York Post',
                'category' => 'arrest',
                'published_at' => '2026-06-16',
                'location_label' => 'Washington, D.C.',
                'lat' => 38.8977,
                'lng' => -77.0365,
            ],
            [
                'title' => 'Trump demands federal judge face charges after Kennedy Center ruling',
                'url' => 'https://www.independent.co.uk/news/world/americas/us-politics/trump-kennedy-center-judge-charges-b2986522.html',
                'source' => 'The Independent',
                'category' => 'other',
                'published_at' => '2026-06-17',
                'location_label' => 'Washington, D.C.',
                'lat' => 38.8956,
                'lng' => -77.0558,
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
