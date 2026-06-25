<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the Mother Jones report that a Defense Department office posted QAnon-laden
 * memes (tied to a Trump "quantum dominance" announcement) to the dashboard
 * newswire. Categorized "other"; no coordinates (a national/online story).
 * Resolved from a t.co short link. Date is approximate — Mother Jones blocks
 * automated fetching; the URL places it in June 2026. Idempotent (by URL).
 */
final class AddDodQanonMemesDashboardCase extends Command
{
    protected $signature = 'dashboard:add-dod-qanon-memes';

    protected $description = 'Add the Mother Jones "DoD posting QAnon memes" report to the dashboard';

    public function handle(): int
    {
        $case = [
            'title' => 'The Defense Department Is Posting QAnon Memes',
            'url' => 'https://www.motherjones.com/politics/2026/06/qanon-memes-trump-quantum/',
            'source' => 'Mother Jones',
            'category' => 'other',
            'published_at' => '2026-06-24',
        ];

        $link = DashboardLink::updateOrCreate(
            ['url' => $case['url']],
            array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
        );

        $this->info(($link->wasRecentlyCreated ? 'Added: ' : 'Updated: ').$case['title']);

        return self::SUCCESS;
    }
}
