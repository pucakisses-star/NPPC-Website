<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds dashboard newswire/map entries for events from late June through
 * mid-July 2026 — the three weeks after the feed's previous entries end.
 * Same beat as the existing links: arrests, prosecutions, and protests over
 * dissent (anti-ICE cases, data-center meetings, flag burning, Palestine
 * solidarity). Sourced from public reporting; matched on URL so the command
 * is idempotent and re-runnable.
 */
class AddLateJune2026DashboardCases extends Command {
    protected $signature = 'dashboard:add-late-june-2026-cases';
    protected $description = 'Add late-June/July 2026 protest & prosecution events to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'St. Joseph residents pack a City Council meeting to protest a proposed data center',
                'url'            => 'https://www.newspressnow.com/news/local_news/government/citizens-protest-proposed-data-center-at-city-council-meeting/article_26af3a40-60ab-4f73-b4c8-d88c5619f3bb.html',
                'source'         => 'News-Press NOW',
                'category'       => 'protest',
                'published_at'   => '2026-06-22',
                'location_label' => 'St. Joseph, MO',
                'lat'            => 39.7675,
                'lng'            => -94.8467,
            ],
            [
                'title'          => 'Inver Grove Heights council meeting erupts after a data-center moratorium vote is delayed; mayor walks out',
                'url'            => 'https://www.mprnews.org/story/2026/06/23/inver-grove-heights-meeting-erupts-into-shouts-after-data-center-moratorium-delayed',
                'source'         => 'MPR News',
                'category'       => 'protest',
                'published_at'   => '2026-06-23',
                'location_label' => 'Inver Grove Heights, MN',
                'lat'            => 44.8480,
                'lng'            => -93.0428,
            ],
            [
                'title'          => 'The 15 Direct Action Minnesota defendants plead not guilty to federal conspiracy charges over anti-ICE protests',
                'url'            => 'https://www.cbsnews.com/minnesota/news/anti-ice-protesters-federal-court-july-1-2026/',
                'source'         => 'CBS Minnesota',
                'category'       => 'prosecution',
                'published_at'   => '2026-07-01',
                'location_label' => 'St. Paul, MN',
                'lat'            => 44.9497,
                'lng'            => -93.0931,
            ],
            [
                'title'          => 'Of 30 resolved Portland ICE-protest prosecutions, 28 ended in probation, supervised release, or dismissal — after felony threats of up to 20 years',
                'url'            => 'https://www.wweek.com/news/courts/2026/07/01/protesters-arrested-outside-ice-faced-daunting-prison-sentencesand-mostly-ended-up-with-short-probations/',
                'source'         => 'Willamette Week',
                'category'       => 'prosecution',
                'published_at'   => '2026-07-01',
                'location_label' => 'Portland, OR',
                'lat'            => 45.4885,
                'lng'            => -122.6707,
            ],
            [
                'title'          => 'Golden Gate Bridge Palestine protesters convicted of misdemeanors; jury hangs on the felony conspiracy count',
                'url'            => 'https://missionlocal.org/2026/07/golden-gate-bridge-palestine-protest-san-francisco-verdict/',
                'source'         => 'Mission Local',
                'category'       => 'prosecution',
                'published_at'   => '2026-07-02',
                'location_label' => 'San Francisco, CA',
                'lat'            => 37.8199,
                'lng'            => -122.4783,
            ],
            [
                'title'          => 'Five protesters arrested for failure to disperse after an attempted flag burning at Philadelphia\'s July Fourth celebrations',
                'url'            => 'https://www.inquirer.com/news/philadelphia/philadelphia-arrests-flag-burning-july-4-20260706.html',
                'source'         => 'Philadelphia Inquirer',
                'category'       => 'arrest',
                'published_at'   => '2026-07-04',
                'location_label' => 'Philadelphia, PA',
                'lat'            => 39.9469,
                'lng'            => -75.1526,
            ],
            [
                'title'          => 'Charges against Arkansas governor\'s-mansion protester Olivia Thompson to be dropped after six months of good behavior',
                'url'            => 'https://www.nwaonline.com/news/2026/jul/09/governors-mansion-protestor-charges-to-be-dropped/',
                'source'         => 'Arkansas Democrat-Gazette',
                'category'       => 'prosecution',
                'published_at'   => '2026-07-09',
                'location_label' => 'Little Rock, AR',
                'lat'            => 34.7304,
                'lng'            => -92.2809,
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
