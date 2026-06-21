<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the Delaney Hall arrests to the dashboard. Delaney Hall is a GEO Group
 * ICE detention center in Newark, New Jersey that reopened under the second
 * Trump administration; after weeks of clashes between ICE and protesters,
 * Newark Mayor Ras Baraka had city police take over law-enforcement duties at
 * the site, and arrests fell sharply (from about 80 to three). Three protesters
 * faced charges including aggravated assault, rioting and arson. Filed as an
 * arrest marker (Newark, NJ). Idempotent (matched on URL).
 */
final class AddDelaneyHallArrestsDashboardCase extends Command
{
    protected $signature = 'dashboard:add-delaney-hall-arrests';

    protected $description = 'Add the Delaney Hall ICE-detention arrests to the dashboard';

    public function handle(): int
    {
        $case = [
            'title' => 'Arrests Continue at Delaney Hall After ICE Drawdown – But They\'re Slowing Down, NJ Mayor Says',
            'url' => 'https://patch.com/new-jersey/newarknj/arrests-continue-delaney-hall-after-ice-drawdown-they-re-slowing-down-nj-mayor',
            'source' => 'Patch',
            'category' => 'arrest',
            'published_at' => '2026-06-05',
            'location_label' => 'Delaney Hall, Newark, New Jersey',
            'lat' => 40.7180,
            'lng' => -74.1180,
        ];

        $link = DashboardLink::updateOrCreate(
            ['url' => $case['url']],
            array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
        );

        $this->info(($link->wasRecentlyCreated ? 'Added: ' : 'Updated: ').$case['title']);

        return self::SUCCESS;
    }
}
