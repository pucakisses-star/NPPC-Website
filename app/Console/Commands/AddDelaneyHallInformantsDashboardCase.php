<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds to the dashboard The Intercept's report that the FBI tried to recruit
 * anti-ICE protesters as informants after the Delaney Hall demonstrations in
 * Newark, New Jersey. Agents reportedly contacted about half of the roughly 90
 * people arrested — including cellist John Mark Rozendaal, arrested May 29,
 * 2026 while playing outside the facility — asking them to inform on fellow
 * protesters. A deputy public defender filed complaints that the approaches
 * violated the protesters' right to counsel. Filed as an "other" marker
 * (surveillance / informant recruitment). Idempotent (matched on URL).
 */
final class AddDelaneyHallInformantsDashboardCase extends Command
{
    protected $signature = 'dashboard:add-delaney-hall-informants';

    protected $description = 'Add the FBI Delaney Hall informant-recruitment report to the dashboard';

    public function handle(): int
    {
        $case = [
            'title' => 'FBI Tried to Flip Anti-ICE Protesters Into Informants',
            'url' => 'https://theintercept.com/2026/06/20/fbi-ice-delaney-hall-protest-informants/',
            'source' => 'The Intercept',
            'category' => 'other',
            'published_at' => '2026-06-20',
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
