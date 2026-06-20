<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds United States v. Hemani to the dashboard. Ali Danial Hemani — a
 * Texas-born U.S./Pakistani dual citizen investigated over alleged family
 * terrorism ties — cooperated with a 2022 search, surrendering a gun and
 * admitting roughly every-other-day marijuana use; months later the government
 * prosecuted him under 18 U.S.C. § 922(g)(3) (drug user in possession of a
 * firearm), a charge carrying up to 15 years. On June 18, 2026 the Supreme
 * Court held 9-0 that the prosecution violated the Second Amendment. Filed as a
 * prosecution marker (Eastern District of Texas). Idempotent (matched on URL).
 */
final class AddHemaniDashboardCase extends Command
{
    protected $signature = 'dashboard:add-hemani';

    protected $description = 'Add United States v. Hemani to the dashboard';

    public function handle(): int
    {
        $case = [
            'title' => 'Supreme Court rules 9-0 (US v. Hemani) that prosecuting Ali Danial Hemani under the federal drug-user gun ban — a charge that followed a terrorism investigation of his family and rested solely on his admitted marijuana use — violated the Second Amendment',
            'url' => 'https://www.thetrace.org/2026/06/hemani-supreme-court-gun-ban-drug-users/',
            'source' => 'The Trace',
            'category' => 'prosecution',
            'published_at' => '2026-06-18',
            'location_label' => 'Eastern District of Texas',
            'lat' => 33.0198,
            'lng' => -96.6989,
        ];

        $link = DashboardLink::firstOrCreate(
            ['url' => $case['url']],
            array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
        );

        if ($link->wasRecentlyCreated) {
            $this->info("Added: {$case['title']}");
        } else {
            $this->line("Skipped (already present): {$case['title']}");
        }

        return self::SUCCESS;
    }
}
