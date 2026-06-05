<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds protest actions at TOWN HALL meetings and CITY HALL buildings to the
 * dashboard as DashboardLink markers -- arrests/removals (category "arrest") and
 * notable no-arrest actions (category "protest"). In-window (on/after May 7,
 * 2025), sourced from public reporting; matched on URL so the command is
 * idempotent.
 */
class AddCityHallDashboardCases extends Command {
    protected $signature = 'dashboard:add-cityhall-cases';
    protected $description = 'Add town-hall-meeting and city-hall protest actions to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Three protesters arrested after taking the stage at a town hall held by Rep. Mike Flood in Lincoln, Nebraska over the budget bill and Medicaid cuts',
                'url'            => 'https://www.cnn.com/2025/08/04/politics/mike-flood-elissa-slotkin-town-halls',
                'source'         => 'CNN',
                'category'       => 'arrest',
                'published_at'   => '2025-08-04',
                'location_label' => 'Lincoln, NE',
                'lat'            => 40.8136,
                'lng'            => -96.7026,
            ],
            [
                'title'          => 'Three protesters arrested for trespassing after disrupting a town hall held by Rep. Adam Smith in Renton, Washington over Gaza',
                'url'            => 'https://www.fox13seattle.com/news/3-demonstrators-arrested-wa-rep-adam-smith-town-hall',
                'source'         => 'FOX 13 Seattle',
                'category'       => 'arrest',
                'published_at'   => '2025-08-04',
                'location_label' => 'Renton, WA',
                'lat'            => 47.4829,
                'lng'            => -122.2171,
            ],
            [
                'title'          => 'Pro-Palestinian protesters disrupted a town hall held by Rep. Wesley Bell in St. Louis; security clashed with demonstrators but no arrests were made',
                'url'            => 'https://www.stlpr.org/government-politics-issues/2025-08-20/wesley-bell-st-louis-town-hall-israel-protest',
                'source'         => 'St. Louis Public Radio',
                'category'       => 'protest',
                'published_at'   => '2025-08-19',
                'location_label' => 'St. Louis, MO',
                'lat'            => 38.6270,
                'lng'            => -90.1994,
            ],
            [
                'title'          => 'One protester arrested as anti-ICE demonstrators forced the Worcester, Massachusetts City Council meeting to adjourn',
                'url'            => 'https://www.bostonglobe.com/2025/06/12/metro/worcester-city-council-meeting-ice-protests/',
                'source'         => 'Boston Globe',
                'category'       => 'arrest',
                'published_at'   => '2025-06-10',
                'location_label' => 'Worcester, MA',
                'lat'            => 42.2626,
                'lng'            => -71.8023,
            ],
            [
                'title'          => 'More than 200 rallied at San Antonio City Hall against ICE immigration raids (no arrests)',
                'url'            => 'https://www.tpr.org/border-immigration/2025-06-08/protestors-gather-at-san-antonio-city-hall-to-protest-ice-arrests',
                'source'         => 'Texas Public Radio',
                'category'       => 'protest',
                'published_at'   => '2025-06-08',
                'location_label' => 'San Antonio, TX',
                'lat'            => 29.4246,
                'lng'            => -98.4951,
            ],
            [
                'title'          => 'Anti-ICE protesters forcibly removed from a New Orleans City Council meeting while demanding "ICE-free zones" (no arrests)',
                'url'            => 'https://www.nola.com/news/politics/new-orleans-border-patrol-protest/article_e9a5057c-86b8-4549-906f-3d36f725abec.html',
                'source'         => 'NOLA.com',
                'category'       => 'protest',
                'published_at'   => '2025-12-04',
                'location_label' => 'New Orleans, LA',
                'lat'            => 29.9533,
                'lng'            => -90.0780,
            ],
            [
                'title'          => 'Immigrant-rights advocates removed from a Houston City Council meeting over police cooperation with ICE; chants of "Let us speak!" (no arrests)',
                'url'            => 'https://abc13.com/post/houston-city-council-meeting-erupts-calls-end-ice-cooperation/18488930/',
                'source'         => 'ABC13',
                'category'       => 'protest',
                'published_at'   => '2026-01-27',
                'location_label' => 'Houston, TX',
                'lat'            => 29.7606,
                'lng'            => -95.3698,
            ],
            [
                'title'          => 'Four anti-ICE protesters arrested occupying the Portland, Oregon City Hall council chambers, postponing a housing-funds vote',
                'url'            => 'https://www.kptv.com/2026/02/19/portland-council-postpones-decision-21-million-unspent-housing-funds/',
                'source'         => 'KPTV',
                'category'       => 'arrest',
                'published_at'   => '2026-02-18',
                'location_label' => 'Portland, OR',
                'lat'            => 45.5150,
                'lng'            => -122.6790,
            ],
            [
                'title'          => 'Housing advocates slept outside New York City Hall to protest homeless-encampment sweeps (no arrests)',
                'url'            => 'https://www.thecity.nyc/2025/12/04/mamdani-says-homeless-camps-crackdowns-will-end/',
                'source'         => 'THE CITY',
                'category'       => 'protest',
                'published_at'   => '2025-12-04',
                'location_label' => 'New York, NY',
                'lat'            => 40.7127,
                'lng'            => -74.0059,
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
