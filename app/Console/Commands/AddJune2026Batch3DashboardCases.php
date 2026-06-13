<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * A third batch of dashboard items (surfaced June 13, 2026; incident dates
 * vary):
 *
 *  - The dismissal of federal charges against Nasra Ahmed, a U.S. citizen
 *    swept up in Operation Metro Surge in St. Paul — the disposition of the
 *    existing "Nasra Ahmed charged" marker; a judge barred re-filing
 *    (Star Tribune).
 *  - A Mississippi court's prior-restraint order forcing the Clarksdale Press
 *    Register to pull an op-ed critical of city officials (U.S. Press Freedom
 *    Tracker).
 *  - FBI agents questioning Eugene, Oregon photographer Robert Scherle about
 *    the identities of anti-deportation protesters he had photographed
 *    (U.S. Press Freedom Tracker).
 *
 * Matched on URL with updateOrCreate, so the command is idempotent.
 */
class AddJune2026Batch3DashboardCases extends Command {
    protected $signature = 'dashboard:add-june-2026-batch-3';
    protected $description = 'Add the Nasra Ahmed dismissal and two press-freedom incidents (Clarksdale, Eugene) to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Federal charges dismissed against Nasra Ahmed, U.S. citizen detained by ICE during surge',
                'url'            => 'https://www.startribune.com/federal-charges-dismissed-against-nasra-ahmed-us-citizen-detained-by-ice-during-surge/601856686',
                'source'         => 'Star Tribune',
                'category'       => 'prosecution',
                'published_at'   => '2026-06-13',
                'location_label' => 'St. Paul, MN',
                'lat'            => 44.9502,
                'lng'            => -93.0940,
            ],
            [
                'title'          => 'Mississippi newspaper ordered to remove op-ed critical of city officials',
                'url'            => 'https://pressfreedomtracker.us/all-incidents/mississippi-newspaper-ordered-to-remove-op-ed-critical-of-city-officials/',
                'source'         => 'U.S. Press Freedom Tracker',
                'category'       => 'other',
                'published_at'   => '2025-02-18',
                'location_label' => 'Clarksdale, MS',
                'lat'            => 34.2001,
                'lng'            => -90.5712,
            ],
            [
                'title'          => "FBI questions Oregon photographer about protesters' identities",
                'url'            => 'https://pressfreedomtracker.us/all-incidents/fbi-questions-oregon-photographer-about-protesters-identities/',
                'source'         => 'U.S. Press Freedom Tracker',
                'category'       => 'other',
                'published_at'   => '2026-02-04',
                'location_label' => 'Eugene, OR',
                'lat'            => 44.0521,
                'lng'            => -123.0868,
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
