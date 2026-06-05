<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds Luigi Mangione case court updates to the dashboard as DashboardLink
 * markers — map pins (they carry lat/lng) and newswire items. Sourced from
 * public reporting; matched on URL so the command is idempotent and re-runnable.
 *
 * The dashboard timeline starts May 7, 2025, so cases before that (the Dec 2024
 * Mangione arrest and the related Briana Boston arrest) are intentionally
 * excluded; this command also removes them if an earlier version seeded them.
 */
class AddMangioneDashboardCases extends Command {
    protected $signature = 'dashboard:add-mangione-cases';
    protected $description = 'Add Luigi Mangione case court-update markers to the dashboard';

    public function handle(): int {
        // Pre-window entries seeded by earlier versions — dropped (dashboard starts May 2025).
        $removed = DashboardLink::whereIn('url', [
            'https://www.cbsnews.com/news/luigi-mangione-healthcare-ceo-shooting-what-we-know/',
            'https://www.wfla.com/news/polk-county/lakeland-woman-who-said-delay-deny-depose-in-call-to-insurance-company-has-charge-dropped/',
        ])->delete();
        if ($removed) {
            $this->warn("Removed {$removed} pre-window marker(s).");
        }

        $cases = [
            [
                'title'          => 'NY judge dismisses terrorism-related murder charges against Luigi Mangione; murder count stands',
                'url'            => 'https://www.cnn.com/2025/09/16/us/luigi-mangione-ny-court-hearing',
                'source'         => 'CNN',
                'category'       => 'prosecution',
                'published_at'   => '2025-09-16',
                'location_label' => 'Manhattan, NY',
                'lat'            => 40.7155,
                'lng'            => -74.0021,
            ],
            [
                'title'          => 'Federal judge tosses death-penalty counts against Luigi Mangione; trial set for fall 2026',
                'url'            => 'https://www.cnn.com/2026/01/30/us/luigi-mangione-case-rulings-trial',
                'source'         => 'CNN',
                'category'       => 'prosecution',
                'published_at'   => '2026-01-30',
                'location_label' => 'Manhattan, NY',
                'lat'            => 40.7141,
                'lng'            => -74.0028,
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
