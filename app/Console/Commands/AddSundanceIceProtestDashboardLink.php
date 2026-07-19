<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the Salt Lake Tribune report on the January 26, 2026 anti-ICE
 * march down Main Street in Park City during the Sundance Film Festival
 * ("Shoot movies, not people" — roughly 500 marchers, organized
 * overnight) to the /dashboard newswire and map.
 *
 * Idempotent (updateOrCreate by URL); geocoded to Park City. No arrests
 * were reported, so this is a protest-category event link only — no
 * prisoner records accompany it.
 */
final class AddSundanceIceProtestDashboardLink extends Command
{
    protected $signature = 'dashboard:add-sundance-ice-protest';

    protected $description = 'Add the Salt Lake Tribune Sundance anti-ICE march report to the dashboard newswire';

    public function handle(): int
    {
        $url = 'https://www.sltrib.com/news/2026/01/26/sundance-ice-protest-draws/';

        $link = DashboardLink::updateOrCreate(
            ['url' => $url],
            [
                'title' => 'Sundance anti-ICE protest draws hundreds down Park City\'s Main Street',
                'source' => 'The Salt Lake Tribune',
                'category' => 'protest',
                'published_at' => Carbon::parse('2026-01-26'),
                'location_label' => 'Park City, UT',
                'lat' => 40.6461,
                'lng' => -111.4980,
            ],
        );

        $this->info(($link->wasRecentlyCreated ? 'Added' : 'Refreshed')." dashboard link: {$link->title}");

        return self::SUCCESS;
    }
}
