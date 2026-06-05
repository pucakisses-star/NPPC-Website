<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds Luigi Mangione case events (the arrest and major court updates) to the
 * dashboard as DashboardLink markers — map pins (they carry lat/lng) and
 * newswire items. Sourced from public reporting; matched on URL so the command
 * is idempotent and safe to re-run.
 *
 * Note: the dashboard timeline starts May 7, 2025, so the December 2024 arrests
 * fall before the window — on the map they clamp to the timeline start and are
 * hidden from the newswire. The two 2025/2026 court updates sit inside the window
 * and display on their real dates.
 */
class AddMangioneDashboardCases extends Command {
    protected $signature = 'dashboard:add-mangione-cases';
    protected $description = 'Add Luigi Mangione case and related arrest/court-update markers to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Luigi Mangione arrested at an Altoona, PA McDonald\'s in the UnitedHealthcare CEO killing',
                'url'            => 'https://www.cbsnews.com/news/luigi-mangione-healthcare-ceo-shooting-what-we-know/',
                'source'         => 'CBS News',
                'category'       => 'arrest',
                'published_at'   => '2024-12-09',
                'location_label' => 'Altoona, PA',
                'lat'            => 40.5187,
                'lng'            => -78.3947,
            ],
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
            // Related fallout from the UnitedHealthcare CEO killing (not Mangione's own case).
            [
                'title'          => 'Briana Boston charged with a terrorism threat over a "delay, deny, depose" call to her health insurer; charge later dropped',
                'url'            => 'https://www.wfla.com/news/polk-county/lakeland-woman-who-said-delay-deny-depose-in-call-to-insurance-company-has-charge-dropped/',
                'source'         => 'WFLA',
                'category'       => 'arrest',
                'published_at'   => '2024-12-11',
                'location_label' => 'Lakeland, FL',
                'lat'            => 28.0395,
                'lng'            => -81.9498,
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
