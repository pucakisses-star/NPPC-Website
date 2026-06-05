<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds nonviolent civil-disobedience protest ARREST cases at U.S. ICE detention
 * facilities, processing centers, and field offices to the dashboard as
 * DashboardLink markers. Each is a blockade / trespass / sit-in arrest dated
 * within the dashboard timeline window (May 7, 2025 onward), sourced from public
 * reporting; matched on URL so the command is idempotent and safe to re-run.
 */
class AddIceFacilityDashboardCases extends Command {
    protected $signature = 'dashboard:add-ice-facility-cases';
    protected $description = 'Add protest arrest cases at ICE detention facilities / field offices to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'At least four arrested after anti-ICE protesters blockaded the road to the Otay Mesa Detention Center with their bodies and a U-lock; signs read "ICE melts under pressure"',
                'url'            => 'https://www.10news.com/news/local-news/anti-ice-protesters-block-roads-leading-to-otay-mesa-detention-center',
                'source'         => 'ABC 10News San Diego',
                'category'       => 'arrest',
                'published_at'   => '2025-06-14',
                'location_label' => 'San Diego, CA',
                'lat'            => 32.5760,
                'lng'            => -116.9690,
            ],
            [
                'title'          => 'More than 30 arrested, including former City Council President Ben Stuckart, after protesters blocked an ICE transport bus and barricaded the Spokane ICE office',
                'url'            => 'https://www.spokesman.com/stories/2025/jun/11/protesters-including-former-city-council-president/',
                'source'         => 'The Spokesman-Review',
                'category'       => 'arrest',
                'published_at'   => '2025-06-11',
                'location_label' => 'Spokane, WA',
                'lat'            => 47.6635,
                'lng'            => -117.4280,
            ],
            [
                'title'          => 'Thirty arrested for trespassing after a Sunrise Movement march blocked the entrance to the Krome Detention Center near Miami demanding the facility be shut down',
                'url'            => 'https://www.wlrn.org/immigration/2025-11-23/protesters-marched-to-demand-shut-down-of-krome-detention-center-and-then-got-arrested-for-trespassing',
                'source'         => 'WLRN',
                'category'       => 'arrest',
                'published_at'   => '2025-11-22',
                'location_label' => 'Miami, FL',
                'lat'            => 25.7530,
                'lng'            => -80.4940,
            ],
            [
                'title'          => 'Fifteen arrested, eleven charged with misdemeanor resisting, after protesters crossed the barricade line at the Broadview ICE Processing Center outside Chicago',
                'url'            => 'https://chicago.suntimes.com/immigration/2025/10/18/15-protesters-arrested-at-broadview-ice-facility',
                'source'         => 'Chicago Sun-Times',
                'category'       => 'arrest',
                'published_at'   => '2025-10-18',
                'location_label' => 'Broadview, IL',
                'lat'            => 41.8540,
                'lng'            => -87.8560,
            ],
            [
                'title'          => 'Ten arrested, including nine clergy, after a "No ICE Philly" Holy Week action entered a restricted area at the ICE Philadelphia field office garage; they sang "We Shall Not Be Moved"',
                'url'            => 'https://www.inquirer.com/news/immigration-protest-ice-garage-center-city-philadelphia-20260330.html',
                'source'         => 'The Philadelphia Inquirer',
                'category'       => 'arrest',
                'published_at'   => '2026-03-30',
                'location_label' => 'Philadelphia, PA',
                'lat'            => 39.9540,
                'lng'            => -75.1530,
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
