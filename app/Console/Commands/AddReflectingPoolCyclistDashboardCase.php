<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the Reflecting Pool cyclist arrest to the dashboard. David Hearn, 67, a
 * three-time Olympic canoeist, was arrested June 20, 2026 on a misdemeanor
 * destruction-of-government-property charge at the Lincoln Memorial Reflecting
 * Pool in Washington, D.C. — after he reached into the pool to feel a detached
 * piece of its new liner. The arrest followed President Trump's accusation that
 * "vandals" were sabotaging the pool's $14M renovation; Hearn denies wrongdoing
 * and is due in D.C. Superior Court on July 9. Idempotent (matched on URL).
 */
final class AddReflectingPoolCyclistDashboardCase extends Command
{
    protected $signature = 'dashboard:add-reflecting-pool-cyclist';

    protected $description = 'Add the Reflecting Pool cyclist arrest to the dashboard';

    public function handle(): int
    {
        $case = [
            'title' => 'Cyclist arrested at Reflecting Pool denies vandalism claims after Trump renovation',
            'url' => 'https://www.washingtonpost.com/nation/2026/06/20/cyclist-arrested-reflecting-pool-denies-trump-vandalism-claims/',
            'source' => 'The Washington Post',
            'category' => 'arrest',
            'published_at' => '2026-06-20',
            'location_label' => 'Lincoln Memorial Reflecting Pool, Washington, D.C.',
            'lat' => 38.8893,
            'lng' => -77.0456,
        ];

        $link = DashboardLink::updateOrCreate(
            ['url' => $case['url']],
            array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
        );

        $this->info(($link->wasRecentlyCreated ? 'Added: ' : 'Updated: ').$case['title']);

        return self::SUCCESS;
    }
}
