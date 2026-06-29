<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the Salt Lake Tribune report on Julio Cesar Irungaray — a Utah activist
 * detained by ICE — to the /dashboard newswire as a curated link. Idempotent
 * (updateOrCreate by URL); geocoded to Salt Lake City for the dashboard map.
 */
final class AddUtahIceDetentionDashboardLink extends Command
{
    protected $signature = 'dashboard:add-utah-ice-detention';

    protected $description = 'Add the Salt Lake Tribune Utah ICE-detention report to the dashboard newswire';

    public function handle(): int
    {
        $url = 'https://www.sltrib.com/news/2026/06/26/utah-activist-detained-by-ice-has/';

        $link = DashboardLink::updateOrCreate(
            ['url' => $url],
            [
                'title' => 'Utah activist detained by ICE has diabetes, family fears for his safety',
                'source' => 'The Salt Lake Tribune',
                'category' => 'arrest',
                'published_at' => Carbon::parse('2026-06-26'),
                'location_label' => 'Salt Lake City, UT',
                'lat' => 40.7608,
                'lng' => -111.8910,
            ],
        );

        $this->info(($link->wasRecentlyCreated ? 'Added' : 'Refreshed')." dashboard link: {$link->title}");

        return self::SUCCESS;
    }
}
