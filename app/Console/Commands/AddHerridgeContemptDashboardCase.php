<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the U.S. Press Freedom Tracker incident in which former Fox News
 * correspondent Catherine Herridge was held in civil contempt (Feb 29, 2024,
 * Washington, D.C.) for refusing to identify a confidential source under
 * subpoena. Categorized "other" (a press-freedom / legal item), matching the
 * dashboard's other Press Freedom Tracker entries. Stored under the resolved
 * canonical URL (the source was shared as a t.co short link). Matched on URL
 * with updateOrCreate, so the command is idempotent.
 */
final class AddHerridgeContemptDashboardCase extends Command
{
    protected $signature = 'dashboard:add-herridge-contempt-case';

    protected $description = 'Add the Catherine Herridge contempt-of-court press-freedom incident to the dashboard';

    public function handle(): int
    {
        $case = [
            'title' => 'Former Fox News reporter held in contempt for refusing to comply with subpoena',
            'url' => 'https://pressfreedomtracker.us/all-incidents/former-fox-news-reporter-held-in-contempt-for-refusing-to-comply-with-subpoena/',
            'source' => 'U.S. Press Freedom Tracker',
            'category' => 'other',
            'published_at' => '2024-02-29',
            'location_label' => 'Washington, D.C.',
            'lat' => 38.8951,
            'lng' => -77.0364,
        ];

        $link = DashboardLink::updateOrCreate(
            ['url' => $case['url']],
            array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
        );

        if ($link->wasRecentlyCreated) {
            $this->info("Added: {$case['title']}");
        } else {
            $this->line("Already present, refreshed: {$case['title']}");
        }

        return self::SUCCESS;
    }
}
