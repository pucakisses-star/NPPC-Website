<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;

/**
 * Publishes the "NPPC Launches Live Event Dashboard" press release (announcing
 * the /dashboard page), filed under a "Press Release" category and dated
 * 2025-05-25. Idempotent: matches the article by its current or previous slug
 * (or title) and updates in place, so re-running never creates a duplicate.
 */
final class AddDashboardAnnouncement extends Command
{
    protected $signature = 'articles:add-dashboard-announcement';

    protected $description = 'Publish the "NPPC Launches Live Event Dashboard" press release';

    private const SLUG = 'nppc-launches-live-event-dashboard';

    /** Previous slug, so an already-published copy is updated rather than duplicated. */
    private const OLD_SLUG = 'nppc-launches-political-prisoner-database-live-tracker';

    public function handle(): int
    {
        $author = Author::firstOrCreate(
            ['name' => 'National Political Prisoner Coalition'],
            ['about' => 'The National Political Prisoner Coalition is an independent, donor-supported coalition dedicated to documenting, supporting, and advocating for U.S. political prisoners.']
        );

        $category = Category::firstOrCreate(['title' => 'Press Release']);

        $title = 'NPPC Launches Live Event Dashboard';

        $intro = 'The National Political Prisoner Coalition has launched the Live Event Dashboard — a public, continuously updated map and tracker of U.S. political prisoners, the facilities holding them, and the events shaping their cases.';

        $body = <<<'HTML'
<p><strong>FOR IMMEDIATE RELEASE</strong> — The National Political Prisoner Coalition (NPPC) today announced the launch of the <a href="/dashboard">Live Event Dashboard</a>, a public dashboard that brings the coalition's data on political imprisonment in the United States together in one place.</p>
<p>The dashboard presents a live count of the political prisoners NPPC has documented, the institutions where they are held, and an interactive map of related cases and events across the country. It is built to make the scale and geography of political imprisonment legible at a glance, and it updates as new cases are added to the database.</p>
<p>The dashboard is also participatory: visitors can submit cases and corrections for review through a built-in form, helping the coalition keep the public record accurate and current.</p>
<p>The Live Event Dashboard is available now at <a href="/dashboard">/dashboard</a>. NPPC is an independent, donor-supported coalition dedicated to documenting, supporting, and advocating for U.S. political prisoners — from the late nineteenth century to the present.</p>
HTML;

        $attributes = [
            'title' => $title,
            'slug' => self::SLUG,
            'intro' => $intro,
            'body' => $body,
            'author_id' => $author->id,
            'category_id' => $category->id,
            'published_at' => '2025-05-25 09:00:00',
        ];

        $article = Article::where('slug', self::SLUG)
            ->orWhere('slug', self::OLD_SLUG)
            ->orWhere('title', $title)
            ->first();

        if ($article) {
            $article->fill($attributes)->save();
            $this->info("Updated article: {$article->title}");
        } else {
            $article = Article::create($attributes);
            $this->info("Created article: {$article->title}");
        }

        $this->info("Filed under {$category->title}, dated 2025-05-25. View: {$article->url}");

        return self::SUCCESS;
    }
}
