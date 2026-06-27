<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;

/**
 * Adds the BBC News report on Muhammad Pahlawan's 40-year sentence (the Iran →
 * Houthi ballistic-missile smuggling case) to the /dashboard newswire as a
 * curated external link (a DashboardLink). Dated to the article's publication
 * (Oct 17, 2025), so it sits at that point on the tracker's timeline.
 *
 * Idempotent: matches the link by URL.
 */
final class AddPahlawanDashboardLink extends Command
{
    protected $signature = 'dashboard:add-pahlawan-link';

    protected $description = 'Add the BBC Muhammad Pahlawan sentencing article to the dashboard newswire';

    private const URL = 'https://www.bbc.com/news/articles/cwy534vw28go';

    public function handle(): int
    {
        $attributes = [
            'title' => 'Weapons smuggler jailed for 40 years after shipping ballistic missiles from Iran',
            'source' => 'BBC News',
            'category' => 'prosecution',
            'published_at' => '2025-10-17 23:10:31',
        ];

        $link = DashboardLink::where('url', self::URL)->first();
        if ($link) {
            $link->fill($attributes)->save();
            $this->info("Updated dashboard link: {$link->title}");
        } else {
            $link = DashboardLink::create($attributes + ['url' => self::URL]);
            $this->info("Created dashboard link: {$link->title}");
        }

        $this->info('Newswire link → '.self::URL);

        return self::SUCCESS;
    }
}
