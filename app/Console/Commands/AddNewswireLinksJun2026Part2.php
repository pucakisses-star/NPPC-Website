<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Second late-June 2026 batch of dashboard newswire links, resolved from t.co
 * short links to their canonical article URLs. Idempotent (updateOrCreate by
 * URL); located items carry coordinates for the map. The Syracuse date is
 * approximate (syracuse.com blocks fetching; the incident was during the
 * June 2026 NY primary).
 */
final class AddNewswireLinksJun2026Part2 extends Command
{
    protected $signature = 'dashboard:add-newswire-jun2026-part2';

    protected $description = 'Add the second late-June 2026 batch of dashboard newswire links';

    public function handle(): int
    {
        $cases = [
            [
                'title' => 'Ohio voting rights group facing criminal fraud investigation, sources say',
                'url' => 'https://www.cbsnews.com/news/ohio-organizing-collaborative-fraud-investigation-fbi/',
                'source' => 'CBS News',
                'category' => 'other',
                'published_at' => '2026-06-12',
                'location_label' => 'Columbus, OH',
                'lat' => 39.9612, 'lng' => -82.9988,
            ],
            [
                'title' => 'Federal agents track down Syracuse woman, demand she remove Instagram post about ICE',
                'url' => 'https://www.syracuse.com/news/2026/06/federal-agents-track-down-syracuse-woman-demand-she-remove-instagram-post-about-ice.html',
                'source' => 'Syracuse.com',
                'category' => 'other',
                'published_at' => '2026-06-24',
                'location_label' => 'Syracuse, NY',
                'lat' => 43.0481, 'lng' => -76.1474,
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
                $this->line("Refreshed: {$case['title']}");
            }
        }

        $this->info("\nDone. {$created} added, {$updated} refreshed.");

        return self::SUCCESS;
    }
}
