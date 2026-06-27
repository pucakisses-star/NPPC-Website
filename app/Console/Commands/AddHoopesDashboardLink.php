<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\DashboardLink;
use Illuminate\Console\Command;

/**
 * Adds the OPB / Associated Press report on Robert Jacob Hoopes to the
 * /dashboard newswire as a curated external link (a DashboardLink), the model
 * that drives the dashboard ticker + feed. Also removes the standalone news
 * Article that was created for him earlier (the dashboard link replaces it).
 *
 * Idempotent: matches the link by URL and the stale article by slug/title.
 */
final class AddHoopesDashboardLink extends Command
{
    protected $signature = 'dashboard:add-hoopes-link';

    protected $description = 'Add the OPB Hoopes article to the dashboard newswire as a link (removes the old news article)';

    private const URL = 'https://www.opb.org/article/2026/06/12/portland-anti-ice-protester-sentenced-in-prison/';

    private const ARTICLE_SLUG = 'robert-hoopes-anti-ice-protester-sentenced-30-months';

    public function handle(): int
    {
        // 1) Remove the standalone news Article created earlier — the dashboard
        //    link replaces it.
        $deleted = Article::where('slug', self::ARTICLE_SLUG)
            ->orWhere('title', 'like', '%Hoopes%')
            ->get();
        foreach ($deleted as $article) {
            $article->delete();
        }
        if ($deleted->isNotEmpty()) {
            $this->info("Removed {$deleted->count()} stale Hoopes news article(s).");
        } else {
            $this->line('No stale Hoopes news article to remove.');
        }

        // 2) Add (or update) the dashboard newswire link.
        $attributes = [
            'title' => 'Anti-ICE protester in Portland sentenced to 30 months in prison for assaulting a federal officer',
            'source' => 'OPB',
            'category' => 'prosecution',
            'published_at' => '2026-06-12 09:00:00',
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
