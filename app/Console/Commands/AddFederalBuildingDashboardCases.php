<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds protest actions at U.S. FEDERAL buildings/courthouses (immigration courts,
 * federal office buildings) to the dashboard as DashboardLink markers -- arrests
 * (category "arrest") and notable no-arrest actions (category "protest"). The
 * U.S. Capitol complex and ICE detention facilities are tracked separately. The
 * in-window clean-arrest pool here is thin: most immigration-court protests in
 * this window turned violent and are excluded on the nonviolence bar. In-window
 * (on/after May 7, 2025), sourced from public reporting; matched on URL so the
 * command is idempotent.
 */
class AddFederalBuildingDashboardCases extends Command {
    protected $signature = 'dashboard:add-federal-building-cases';
    protected $description = 'Add federal-building / courthouse protest actions to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Forty-two faith leaders arrested after chaining themselves to the doors of the San Francisco immigration court to shut it down; their banner read "People of faith choose love over cruelty"',
                'url'            => 'https://missionlocal.org/2025/12/faith-leaders-chain-immigration-court-san-francisco/',
                'source'         => 'Mission Local',
                'category'       => 'arrest',
                'published_at'   => '2025-12-16',
                'location_label' => 'San Francisco, CA',
                'lat'            => 37.7956,
                'lng'            => -122.4015,
            ],
            [
                'title'          => 'Dozens protested at the John E. Moss Federal Building immigration court in Sacramento after suspected ICE agents detained immigrants at their hearings; demonstrators tried to block a detainee van (no arrests)',
                'url'            => 'https://www.capradio.org/articles/2025/06/12/sacramento-immigration-court-on-lockdown-draws-protest-after-suspected-ice-operation/',
                'source'         => 'CapRadio',
                'category'       => 'protest',
                'published_at'   => '2025-06-12',
                'location_label' => 'Sacramento, CA',
                'lat'            => 38.5800,
                'lng'            => -121.5040,
            ],
            [
                'title'          => 'More than a dozen demonstrators rallied outside the downtown Phoenix federal courthouse against ICE arresting immigrants as they left their court hearings (no arrests)',
                'url'            => 'https://www.azfamily.com/2025/05/21/immigration-protests-held-outside-federal-courthouse-phoenix/',
                'source'         => 'Arizona\'s Family',
                'category'       => 'protest',
                'published_at'   => '2025-05-21',
                'location_label' => 'Phoenix, AZ',
                'lat'            => 33.4490,
                'lng'            => -112.0780,
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
