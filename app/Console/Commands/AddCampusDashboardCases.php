<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds protest ARREST cases at U.S. universities/colleges (campus walkouts,
 * sit-ins, anti-ICE actions) to the dashboard as DashboardLink markers. The big
 * Gaza-encampment arrest wave was spring 2024 (pre-window); the in-window pool
 * (May 7, 2025 onward) is thin and skews toward anti-ICE campus actions in late
 * 2025/early 2026 plus a commencement-protest case. Sourced from public
 * reporting; matched on URL so the command is idempotent and safe to re-run.
 */
class AddCampusDashboardCases extends Command {
    protected $signature = 'dashboard:add-campus-cases';
    protected $description = 'Add university/college protest arrest cases to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Two students arrested after a pro-Palestinian walkout interrupted the University of Texas at Dallas commencement; one was charged with disrupting a meeting and another with criminal trespass',
                'url'            => 'https://www.texastribune.org/2026/05/21/pro-palestinian-protestors-sue-ut-dallas-leaders-police-officers-over-alleged-punishment/',
                'source'         => 'The Texas Tribune',
                'category'       => 'arrest',
                'published_at'   => '2025-05-16',
                'location_label' => 'Richardson, TX',
                'lat'            => 32.9857,
                'lng'            => -96.7502,
            ],
            [
                'title'          => 'Twelve people, including students and faculty, arrested and given summonses after an "ICE Off Campus" protest blocked the entrance to Columbia University demanding a sanctuary campus',
                'url'            => 'https://www.columbiaspectator.com/news/2026/02/05/nypd-arrests-12-including-students-and-faculty-at-anti-ice-demonstration-outside-campus-gates/',
                'source'         => 'Columbia Daily Spectator',
                'category'       => 'arrest',
                'published_at'   => '2026-02-05',
                'location_label' => 'New York, NY',
                'lat'            => 40.8075,
                'lng'            => -73.9626,
            ],
            [
                'title'          => 'Sixty-seven arrested for unlawful assembly at a University of Minnesota anti-ICE noise demonstration outside a Graduate hotel believed to be housing ICE agents',
                'url'            => 'https://www.kare11.com/article/news/local/u-of-m-police-arrests-67-protesters-at-graduate-hotel/89-b0cbd79e-afa3-47f0-b1dc-429f7f26ad6f',
                'source'         => 'KARE 11',
                'category'       => 'arrest',
                'published_at'   => '2026-01-28',
                'location_label' => 'Minneapolis, MN',
                'lat'            => 44.9735,
                'lng'            => -93.2270,
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
