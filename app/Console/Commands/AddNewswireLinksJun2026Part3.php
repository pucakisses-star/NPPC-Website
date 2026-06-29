<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Third late-June 2026 batch of dashboard newswire links. Idempotent
 * (updateOrCreate by URL), so re-running never duplicates; located items
 * carry coordinates for the map.
 *
 * Notes:
 * - The WAVY Flock-cameras date is approximate: wavy.com blocks fetching, so
 *   the date is taken from WAVY's coverage timing (the charges were certified
 *   to Circuit Court in December 2025).
 * - The KATU Hoopes link is the earlier July 2025 *charging* article; it
 *   complements the existing OPB *sentencing* link (dashboard:add-hoopes-link),
 *   which is a different URL covering the same case at a later stage.
 */
final class AddNewswireLinksJun2026Part3 extends Command
{
    protected $signature = 'dashboard:add-newswire-jun2026-part3';

    protected $description = 'Add the third late-June 2026 batch of dashboard newswire links';

    public function handle(): int
    {
        $cases = [
            [
                'title' => "DC reaches settlement with man who protested troops' patrol with Darth Vader song",
                'url' => 'https://abcnews.com/US/wireStory/dc-reaches-court-settlement-man-detained-protesting-troops-134249966',
                'source' => 'ABC News',
                'category' => 'other',
                'published_at' => '2026-06-26',
                'location_label' => 'Washington, D.C.',
                'lat' => 38.9072, 'lng' => -77.0369,
            ],
            [
                'title' => 'Suffolk man charged with destroying 13 Flock cameras',
                'url' => 'https://www.wavy.com/news/local-news/suffolk/suffolk-man-charged-with-destroying-13-flock-cameras/',
                'source' => 'WAVY',
                'category' => 'prosecution',
                'published_at' => '2025-12-10',
                'location_label' => 'Suffolk, VA',
                'lat' => 36.7282, 'lng' => -76.5836,
            ],
            [
                'title' => "Portland man and 'lifelong Quaker' charged with assaulting ICE officer during protest",
                'url' => 'https://katu.com/news/local/fbi-links-man-to-ice-protest-via-gas-mask-photo-faces-federal-charges-portland-man-and-lifelong-quaker-charged-with-assaulting-ice-officer-during-protest',
                'source' => 'KATU',
                'category' => 'prosecution',
                'published_at' => '2025-07-28',
                'location_label' => 'Portland, OR',
                'lat' => 45.5152, 'lng' => -122.6784,
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
