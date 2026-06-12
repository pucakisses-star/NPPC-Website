<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds recent (late May–June 2026) protest-repression and political-arrest
 * cases to the dashboard as DashboardLink markers — the current anti-ICE /
 * immigration-enforcement protest wave plus campus and ICE-protest
 * prosecutions. Titles are the source article headlines. Matched on URL and
 * uses updateOrCreate, so the command is idempotent and re-running updates
 * existing rows.
 */
class AddRecentProtestArrestsDashboardCases extends Command {
    protected $signature = 'dashboard:add-recent-protest-arrests-cases';
    protected $description = 'Add recent (June 2026) protest-arrest and political-arrest cases to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'What to know about the protests and arrests outside a New Jersey detention center',
                'url'            => 'https://www.pbs.org/newshour/nation/what-to-know-about-the-protests-and-arrests-outside-a-new-jersey-detention-center',
                'source'         => 'PBS NewsHour',
                'category'       => 'arrest',
                'published_at'   => '2026-06-06',
                'location_label' => 'Newark, NJ',
                'lat'            => 40.7357,
                'lng'            => -74.1724,
            ],
            [
                'title'          => '3 Spokane ICE protesters found guilty in conspiracy case',
                'url'            => 'https://www.spokesman.com/stories/2026/may/28/3-spokane-ice-protesters-found-guilty-in-conspirac/',
                'source'         => 'The Spokesman-Review',
                'category'       => 'prosecution',
                'published_at'   => '2026-05-28',
                'location_label' => 'Spokane, WA',
                'lat'            => 47.6588,
                'lng'            => -117.4260,
            ],
            [
                'title'          => 'Swarthmore 9 protesters urge Delco DA to drop trespassing case tied to pro-Palestinian encampment',
                'url'            => 'https://www.inquirer.com/crime/swarthmore-college-protesters-trespassing-charges-20260609.html',
                'source'         => 'The Philadelphia Inquirer',
                'category'       => 'prosecution',
                'published_at'   => '2026-06-09',
                'location_label' => 'Swarthmore, PA',
                'lat'            => 39.9020,
                'lng'            => -75.3493,
            ],
            [
                'title'          => 'Case Dismissed Against Santa Barbara Activist Arrested for Slashing ICE Tire',
                'url'            => 'https://www.independent.com/2026/06/04/case-dismissed-against-santa-barbara-activist-arrested-for-slashing-ice-tire/',
                'source'         => 'Santa Barbara Independent',
                'category'       => 'other',
                'published_at'   => '2026-06-04',
                'location_label' => 'Santa Barbara, CA',
                'lat'            => 34.4208,
                'lng'            => -119.6982,
            ],
            [
                'title'          => 'ICE denies having a protester database. But a letter to Congress sheds more light',
                'url'            => 'https://www.npr.org/2026/06/10/nx-s1-5843159/ice-protester-database-dhs',
                'source'         => 'NPR',
                'category'       => 'other',
                'published_at'   => '2026-06-10',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8951,
                'lng'            => -77.0364,
            ],
        ];

        $created = 0;
        $updated = 0;
        foreach ($cases as $case) {
            $link = DashboardLink::updateOrCreate(
                ['url' => $case['url']],
                array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
            );

            if ($link->wasRecentlyCreated) {
                $created++;
                $this->info("Added: {$case['title']}");
            } else {
                $updated++;
                $this->line("Updated: {$case['title']}");
            }
        }

        $this->info("Done. {$created} added, {$updated} updated.");

        return self::SUCCESS;
    }
}
