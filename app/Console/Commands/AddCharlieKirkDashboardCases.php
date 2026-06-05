<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds curated Charlie Kirk-related arrest/prosecution cases to the dashboard.
 *
 * Each entry becomes a DashboardLink, which the tracker plots as an event marker
 * on the map (it carries lat/lng) and lists in the newswire. Sourced from public
 * reporting; matched on URL so the command is idempotent and safe to re-run.
 *
 * Note: the dashboard timeline defaults to the last 30 days, so these
 * September 2025 markers only appear once the scrubber is dragged back to then.
 */
class AddCharlieKirkDashboardCases extends Command {
    protected $signature = 'dashboard:add-kirk-cases';
    protected $description = 'Add curated Charlie Kirk-related arrest/prosecution cases as dashboard map markers';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Tyler Robinson charged with aggravated murder in Charlie Kirk killing; prosecutors seek death penalty',
                'url'            => 'https://www.cnn.com/2025/09/16/us/what-charges-charlie-kirk-case',
                'source'         => 'CNN',
                'category'       => 'prosecution',
                'published_at'   => '2025-09-16',
                'location_label' => 'Orem, UT',
                'lat'            => 40.2969,
                'lng'            => -111.6946,
            ],
            [
                'title'          => 'Tennessee man Larry Bushart jailed 37 days over a Charlie Kirk Facebook meme; charge later dropped',
                'url'            => 'https://www.nbcnews.com/news/us-news/man-jailed-charlie-kirk-post-wins-settlement-rcna346140',
                'source'         => 'NBC News',
                'category'       => 'arrest',
                'published_at'   => '2025-09-22',
                'location_label' => 'Lexington, TN',
                'lat'            => 35.6512,
                'lng'            => -88.3936,
            ],
            [
                'title'          => 'Xaelyn Dunbar, 19, charged with a terroristic threat over UTSA Charlie Kirk vigil posts',
                'url'            => 'https://www.ksat.com/news/local/2025/09/24/man-arrested-after-posting-threatening-online-comments-about-charlie-kirk-vigil-at-utsa-affidavit-says/',
                'source'         => 'KSAT',
                'category'       => 'arrest',
                'published_at'   => '2025-09-23',
                'location_label' => 'San Antonio, TX',
                'lat'            => 29.4241,
                'lng'            => -98.4936,
            ],
            [
                'title'          => 'Two arrested at the Charlie Kirk candlelight vigil on Boston Common',
                'url'            => 'https://www.bostonglobe.com/2025/09/19/metro/charlie-kirk-vigil-arrests/',
                'source'         => 'Boston Globe',
                'category'       => 'protest',
                'published_at'   => '2025-09-18',
                'location_label' => 'Boston, MA',
                'lat'            => 42.3551,
                'lng'            => -71.0657,
            ],
            [
                'title'          => 'Two arrested over a pepper-spray disturbance at the Pensacola Charlie Kirk vigil',
                'url'            => 'https://weartv.com/news/local/ppd-arrest-man-for-pepper-spray-attack-at-kirk-vigil-warrant-issued-for-second-suspect',
                'source'         => 'WEAR',
                'category'       => 'protest',
                'published_at'   => '2025-09-15',
                'location_label' => 'Pensacola, FL',
                'lat'            => 30.4213,
                'lng'            => -87.2169,
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
