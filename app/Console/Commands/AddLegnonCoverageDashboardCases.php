<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds additional news coverage of the Micah Legnon arrest (the New Iberia,
 * LA Turtle Island Liberation Front member charged with threatening ICE
 * officers) to the dashboard as further "arrest" markers, alongside the DOJ
 * press release already added by prisoners:add-legnon. Curated to mainstream
 * and local Louisiana outlets. Coordinates are nudged slightly off the DOJ
 * marker so the pins don't perfectly overlap on the map. Matched on URL with
 * updateOrCreate, so the command is idempotent.
 */
class AddLegnonCoverageDashboardCases extends Command {
    protected $signature = 'dashboard:add-legnon-coverage';
    protected $description = 'Add additional Micah Legnon arrest coverage to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'New Iberia man charged in alleged plot to attack ICE agents',
                'url'            => 'https://www.wafb.com/2025/12/16/new-iberia-man-charged-alleged-plot-attack-ice-agents/',
                'source'         => 'WAFB',
                'category'       => 'arrest',
                'published_at'   => '2025-12-16',
                'location_label' => 'New Iberia, LA',
                'lat'            => 30.0040,
                'lng'            => -91.8190,
            ],
            [
                'title'          => 'FBI arrests New Iberia man in extremist threat investigation tied to New Orleans',
                'url'            => 'https://www.wwltv.com/article/news/local/fbi-arrests-new-iberia-man-in-extremist-threat-investigation-tied-to-new-orleans-terroism-new-years-eve-california/289-e48d2289-eec1-4988-8a45-b9d809ff4266',
                'source'         => 'WWL-TV',
                'category'       => 'arrest',
                'published_at'   => '2025-12-16',
                'location_label' => 'New Iberia, LA',
                'lat'            => 30.0030,
                'lng'            => -91.8180,
            ],
            [
                'title'          => 'Former U.S. Marine arrested in Louisiana for alleged threats against ICE officers; FBI, AG respond',
                'url'            => 'https://www.fox8live.com/2025/12/22/ag-bondi-fbi-director-patel-comment-arrest-new-iberia-man-booked-with-threatening-ice-officers/',
                'source'         => 'FOX 8',
                'category'       => 'arrest',
                'published_at'   => '2025-12-22',
                'location_label' => 'New Iberia, LA',
                'lat'            => 30.0045,
                'lng'            => -91.8195,
            ],
            [
                'title'          => 'Ex-Marine, Turtle Island Liberation Member Arrested in Connection to NYE Terror Plot',
                'url'            => 'https://www.newsweek.com/nye-terror-plot-turtle-island-liberation-front-arrest-11222340',
                'source'         => 'Newsweek',
                'category'       => 'arrest',
                'published_at'   => '2025-12-16',
                'location_label' => 'New Iberia, LA',
                'lat'            => 30.0025,
                'lng'            => -91.8175,
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
