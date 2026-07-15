<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the Fergie Chambers arrest to the dashboard: James Cox Chambers Jr., a
 * Cox Enterprises heir turned communist financier and major pro-Palestinian
 * donor, arrested in Ibiza's Morna Valley on July 10, 2026 on a U.S.
 * extradition request. A sealed federal indictment charges international money
 * laundering, riot, and conspiracy to riot, alleging he moved ~$7.5 million out
 * of the U.S. in 2023; supporters call it politically motivated repression.
 * Ibiza coordinates sit off the default continental-US frame but stay on the
 * world map and in the newswire feed. Idempotent (matched on URL).
 */
final class AddFergieChambersDashboardCase extends Command
{
    protected $signature = 'dashboard:add-fergie-chambers';

    protected $description = 'Add the Fergie Chambers Spain arrest / US extradition case to the dashboard';

    public function handle(): int
    {
        $case = [
            'title' => 'Pro-Palestine activist and Cox heir Fergie Chambers arrested in Spain on US extradition request',
            'url' => 'https://www.middleeasteye.net/news/fergie-chambers-arrested-spain-after-us-extradition-request',
            'source' => 'Middle East Eye',
            'category' => 'arrest',
            'published_at' => '2026-07-13',
            'location_label' => 'Ibiza, Spain',
            'lat' => 39.0600,
            'lng' => 1.5300,
        ];

        $link = DashboardLink::updateOrCreate(
            ['url' => $case['url']],
            array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
        );

        $this->info(($link->wasRecentlyCreated ? 'Added: ' : 'Updated: ').$case['title']);

        return self::SUCCESS;
    }
}
