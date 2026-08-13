<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Three protest rows for the data-center fight, which is the movement
 * generating the most protest coverage right now and which the newswire
 * had only covered as national days of action — the July 18 day of 142
 * protests across 42 states, and the state-legislature scramble. What was
 * missing was the local organising underneath that: the zoning meetings
 * and neighbourhood associations where these fights are actually won.
 *
 * Every URL was requested and read before being written, which mattered
 * here: search results attributed a 105-87 NPU-V vote in Atlanta to
 * August, and the article itself puts that vote in April. The August
 * story is a dozen residents speaking at a City Council meeting on the
 * 3rd. The row says the latter.
 */
final class AddDataCenterProtestDashboardLinks extends Command {
    protected $signature = 'dashboard:add-data-center-protests';
    protected $description = 'Add dashboard protest links for the local data-center fights (Atlanta, Prince George\'s County, national)';

    public function handle(): int {
        $links = [
            [
                'url' => 'https://roughdraftatlanta.com/2026/08/05/atlanta-residents-protest-digital-realty/',
                'title' => 'Atlanta residents take the Digital Realty data center to City Council: a dozen speak against the $500m West End proposal',
                'source' => 'Rough Draft Atlanta',
                'category' => 'protest',
                'published_at' => '2026-08-05',
                // The proposed site, 713 Ralph David Abernathy Boulevard, where
                // West End, Adair Park and Mechanicsville meet.
                'location_label' => 'West End, Atlanta, GA',
                'lat' => 33.7350,
                'lng' => -84.4130,
            ],
            [
                'url' => 'https://slate.com/technology/2026/08/ai-data-centers-protests-maryland.html',
                'title' => 'How Prince George\'s County activists paused an AI data center project, for now',
                'source' => 'Slate',
                'category' => 'protest',
                'published_at' => '2026-08-06',
                'location_label' => "Prince George's County, MD",
                'lat' => 38.8157,
                'lng' => -76.7497,
            ],
            [
                'url' => 'https://time.com/article/2026/07/22/community-backlash-ai-data-centers/',
                'title' => 'Community backlash to AI data centers is growing, with billions in projects delayed or cancelled by local opposition',
                'source' => 'TIME',
                'category' => 'protest',
                'published_at' => '2026-07-22',
                'location_label' => 'Nationwide (pinned at Washington, DC)',
                'lat' => 38.9072,
                'lng' => -77.0369,
            ],
        ];

        foreach ($links as $link) {
            $url = $link['url'];
            unset($link['url']);
            $link['published_at'] = Carbon::parse($link['published_at']);
            DashboardLink::updateOrCreate(['url' => $url], $link);
            $this->info("Upserted: {$link['title']}");
        }

        $this->info("\n".count($links).' link(s) upserted.');

        return self::SUCCESS;
    }
}
