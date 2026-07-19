<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the Spectrum News report on the arrest of Jonathan Hale of
 * People's Vision Zero, cited for misdemeanor vandalism after painting
 * an unauthorized crosswalk at a Westwood intersection his group
 * considers unsafe (December 2025).
 */
final class AddCrosswalkHaleDashboardLink extends Command
{
    protected $signature = 'dashboard:add-crosswalk-hale';

    protected $description = 'Add the Jonathan Hale crosswalk-painting arrest to the dashboard newswire';

    public function handle(): int
    {
        $url = 'https://spectrumlocalnews.com/ca/california/public-safety/2025/12/09/los-angeles-crosswalk-jonathan-hale';

        $link = DashboardLink::updateOrCreate(
            ['url' => $url],
            [
                'title' => 'Jonathan Hale of People\'s Vision Zero arrested for painting an unauthorized crosswalk in Westwood',
                'source' => 'Spectrum News',
                'category' => 'arrest',
                'published_at' => Carbon::parse('2025-12-09'),
                'location_label' => 'Westwood, Los Angeles, CA',
                'lat' => 34.0561,
                'lng' => -118.4426,
            ],
        );

        $this->info(($link->wasRecentlyCreated ? 'Added' : 'Refreshed')." dashboard link: {$link->title}");

        return self::SUCCESS;
    }
}
