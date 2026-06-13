<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the reported State Department investigation into revoking the green card
 * of and deporting Trita Parsi — the Swedish-Iranian permanent resident who
 * co-founded the National Iranian American Council and the Quincy Institute —
 * over his criticism of war with Iran and advocacy for diplomacy. Categorized
 * "other" (a targeting / immigration-enforcement item aimed at political
 * speech, not an arrest or criminal prosecution). Matched on URL with
 * updateOrCreate, so the command is idempotent.
 */
class AddParsiDashboardCases extends Command {
    protected $signature = 'dashboard:add-parsi-case';
    protected $description = 'Add the State Dept. investigation into deporting Iran-war critic Trita Parsi to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Trump Investigates How to Deport Iran War Critic Trita Parsi',
                'url'            => 'https://newrepublic.com/post/211691/trump-investigates-deport-iran-war-critic-trita-parsi',
                'source'         => 'The New Republic',
                'category'       => 'other',
                'published_at'   => '2026-06-11',
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
