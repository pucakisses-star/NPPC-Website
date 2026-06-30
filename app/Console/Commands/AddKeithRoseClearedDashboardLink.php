<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the FOX 2 (St. Louis) report on St. Louis activist Keith Rose being
 * cleared of a felony property-damage charge from the August 2024 Ferguson
 * protest — prosecutors dropped it after finding the video used to charge him
 * was from the wrong day — to the /dashboard newswire.
 *
 * Idempotent (updateOrCreate by URL); geocoded to Ferguson for the map. The
 * publication date is approximate: fox2now.com blocks fetching and shows no
 * date on the page, so it is set to the share date.
 */
final class AddKeithRoseClearedDashboardLink extends Command
{
    protected $signature = 'dashboard:add-keith-rose-cleared';

    protected $description = 'Add the FOX 2 Keith Rose "cleared of felony charge" report to the dashboard newswire';

    public function handle(): int
    {
        $url = 'https://fox2now.com/news/missouri/video-evidence-clears-activist-of-felony-charge/';

        $link = DashboardLink::updateOrCreate(
            ['url' => $url],
            [
                'title' => 'Video evidence clears activist of felony charge',
                'source' => 'FOX 2 (St. Louis)',
                'category' => 'prosecution',
                'published_at' => Carbon::parse('2026-06-27'),
                'location_label' => 'Ferguson, MO',
                'lat' => 38.7442,
                'lng' => -90.3054,
            ],
        );

        $this->info(($link->wasRecentlyCreated ? 'Added' : 'Refreshed')." dashboard link: {$link->title}");

        return self::SUCCESS;
    }
}
