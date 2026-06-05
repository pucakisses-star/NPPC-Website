<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds speech-based ICE / immigration DETENTION cases to the dashboard as
 * DashboardLink markers (map pins + newswire) — activists, scholars, or
 * commentators taken into civil immigration custody in a way widely viewed as
 * retaliation for protected political speech. Limited to detentions that began
 * within the dashboard window (on/after May 7, 2025); the better-known March-
 * April 2025 student cluster (Khalil, Ozturk, etc.) is intentionally excluded.
 * Sourced from public reporting; matched on URL so the command is idempotent.
 */
class AddDetentionDashboardCases extends Command {
    protected $signature = 'dashboard:add-detention-cases';
    protected $description = 'Add speech-based ICE/immigration detention cases to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'British political commentator Sami Hamdi detained by ICE at San Francisco airport after his visa was revoked over his Gaza commentary',
                'url'            => 'https://www.pbs.org/newshour/nation/british-political-commentator-sami-hamdi-detained-by-ice-while-on-u-s-speaking-tour',
                'source'         => 'PBS NewsHour',
                'category'       => 'arrest',
                'published_at'   => '2025-10-26',
                'location_label' => 'San Francisco, CA',
                'lat'            => 37.6213,
                'lng'            => -122.3790,
            ],
            [
                'title'          => 'Palestinian-American community leader Salah Sarsour, a longtime green-card holder, detained by ICE in Milwaukee; rights groups call it retaliation for his pro-Palestinian advocacy',
                'url'            => 'https://www.cnn.com/2026/04/03/us/salah-sarsour-islamic-society-milwaukee-detained',
                'source'         => 'CNN',
                'category'       => 'arrest',
                'published_at'   => '2026-03-30',
                'location_label' => 'Milwaukee, WI',
                'lat'            => 43.0389,
                'lng'            => -87.9065,
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
