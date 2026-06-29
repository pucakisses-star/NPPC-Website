<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;

/**
 * Publishes a press release announcing the launch of the Political Prisoner
 * Database Live Tracker (the /dashboard page), filed under a "Press Release"
 * category and dated to the dashboard's launch (2026-06-26). Idempotent:
 * matches the article by slug/title and updates in place on re-run.
 */
final class AddDashboardAnnouncement extends Command
{
    protected $signature = 'articles:add-dashboard-announcement';

    protected $description = 'Publish the press release announcing the Political Prisoner Database Live Tracker';

    private const SLUG = 'nppc-launches-political-prisoner-database-live-tracker';

    public function handle(): int
    {
        $author = Author::firstOrCreate(
            ['name' => 'National Political Prisoner Coalition'],
            ['about' => 'The National Political Prisoner Coalition is an independent, donor-supported coalition dedicated to documenting, supporting, and advocating for U.S. political prisoners.']
        );

        $category = Category::firstOrCreate(['title' => 'Press Release']);

        $title = 'NPPC Launches the Political Prisoner Database Live Tracker';

        $intro = 'The National Political Prisoner Coalition has launched the Political Prisoner Database Live Tracker — a public, continuously updated dashboard mapping U.S. political prisoners, the facilities holding them, and the events shaping their cases.';

        $body = <<<'HTML'
<p><strong>FOR IMMEDIATE RELEASE</strong> — The National Political Prisoner Coalition (NPPC) today announced the launch of the <a href="/dashboard">Political Prisoner Database Live Tracker</a>, a public dashboard that brings the coalition's data on political imprisonment in the United States together in one place.</p>
<p>The tracker presents a live count of the political prisoners NPPC has documented, the institutions where they are held, and an interactive map of related cases and events across the country. It is built to make the scale and geography of political imprisonment legible at a glance, and it updates as new cases are added to the database.</p>
<p>The dashboard is also participatory: visitors can submit cases and corrections for review through a built-in form, helping the coalition keep the public record accurate and current.</p>
<p>The Political Prisoner Database Live Tracker is available now at <a href="/dashboard">/dashboard</a>. NPPC is an independent, donor-supported coalition dedicated to documenting, supporting, and advocating for U.S. political prisoners — from the late nineteenth century to the present.</p>
HTML;

        $attributes = [
            'title' => $title,
            'slug' => self::SLUG,
            'intro' => $intro,
            'body' => $body,
            'author_id' => $author->id,
            'category_id' => $category->id,
            'published_at' => '2026-06-26 09:00:00',
        ];

        $article = Article::where('slug', self::SLUG)->orWhere('title', $title)->first();
        if ($article) {
            $article->fill($attributes)->save();
            $this->info("Updated article: {$article->title}");
        } else {
            $article = Article::create($attributes);
            $this->info("Created article: {$article->title}");
        }

        $this->info("Filed under {$category->title}, dated 2026-06-26. View: {$article->url}");

        return self::SUCCESS;
    }
}
