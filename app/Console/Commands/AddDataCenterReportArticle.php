<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Creates (or updates) the "pointer" news article for the Data-Center Revolt
 * briefing so it shows up as a card in the news grid's Reports section and links
 * through to /data-center-cases (via SiteController::FEATURE_REDIRECTS).
 *
 * The card mirrors an existing report article so it lands in the same category
 * (and shares an author) regardless of how that category is named. Idempotent --
 * matched on slug, safe to re-run.
 */
class AddDataCenterReportArticle extends Command {
    protected $signature = 'articles:add-data-center-report';
    protected $description = 'Add the Data-Center Revolt pointer article to the news Reports section';

    public function handle(): int {
        // Copy the category/author from an existing report card so the new one
        // appears in the same section as the others.
        $template = Article::where('slug', 'under-cover-of-war')->first()
            ?? Article::where('slug', 'detained-for-dissent')->first();

        $categoryId = $template?->category_id
            ?? Category::firstOrCreate(['title' => 'Reports'], ['slug' => 'reports'])->id;

        if (! $template) {
            $this->warn('No existing report article found to mirror; falling back to a "Reports" category.');
        }

        $article = Article::updateOrCreate(
            ['slug' => 'the-data-center-revolt'],
            [
                'title'        => 'The Data-Center Revolt',
                'intro'        => 'Residents are being dragged from town halls, jailed, and charged with felonies for opposing AI data centers. A living NPPC briefing on the people facing prosecution -- and the surveillance now trained on them.',
                'body'         => '<p>This is a standalone briefing. Read it at <a href="/data-center-cases">The Data-Center Revolt</a>.</p>',
                'image'        => '/images/data-center-cases-cover.svg',
                'category_id'  => $categoryId,
                'author_id'    => $template?->author_id,
                'published_at' => Carbon::parse('2026-06-07'),
            ],
        );

        $this->info(($article->wasRecentlyCreated ? 'Created' : 'Updated')
            .' the pointer article "The Data-Center Revolt" (category_id='.($categoryId ?? 'null').').');
        $this->line('It redirects to /data-center-cases via SiteController::FEATURE_REDIRECTS.');

        return self::SUCCESS;
    }
}
