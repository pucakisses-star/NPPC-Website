<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds Type Investigations' report that FBI files undercut the government's
 * argument in the Texas "antifa" / Prairieland anti-ICE terrorism trial in
 * Fort Worth, as an additional source marker alongside the existing Intercept
 * coverage of that trial. Categorized "prosecution"; placed at the Fort Worth
 * courthouse with a slight offset from the Intercept pin. Matched on URL with
 * updateOrCreate, so the command is idempotent.
 */
class AddTexasAntifaTrialSourceDashboardCase extends Command {
    protected $signature = 'dashboard:add-texas-antifa-trial-source';
    protected $description = 'Add the Type Investigations source on the Texas antifa / Prairieland trial to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'FBI Files Counter Government Argument in Texas Antifa Trial',
                'url'            => 'https://typeinvestigations.org/investigation/2026/03/26/exclusive-fbi-files-counter-government-argument-in-texas-antifa-trial/',
                'source'         => 'Type Investigations',
                'category'       => 'prosecution',
                'published_at'   => '2026-03-26',
                'location_label' => 'Fort Worth, TX',
                'lat'            => 32.7503,
                'lng'            => -97.3335,
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
