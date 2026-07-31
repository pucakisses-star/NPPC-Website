<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Adds the "Transnational Repression" report as a clickable article filed under
 * the "Report" category (URL /report/{slug}, dated 2025). Like the other
 * feature pages, the article's URL is redirected to the standalone report
 * (/transnational-repression) by the FEATURE_REDIRECTS map in SiteController
 * (matched by slug, so any category prefix works), so clicking the card opens
 * the report. Copies the card cover to the public disk. Idempotent (matches by slug).
 */
final class AddTnrReportArticle extends Command
{
    protected $signature = 'articles:add-tnr-report';

    protected $description = 'Add the Transnational Repression report as a news article (links to the report page)';

    private const SLUG = 'transnational-repression-report';

    private const SOURCE = 'images/articles/transnational-repression.jpg';

    private const IMAGE = 'articles/transnational-repression.jpg';

    public function handle(): int
    {
        // Copy the committed cover onto the public disk where article images are served.
        $source = public_path(self::SOURCE);
        if (is_file($source)) {
            Storage::disk('public')->put(self::IMAGE, file_get_contents($source));
            $this->info('Cover copied to public disk: '.self::IMAGE);
        } else {
            $this->warn('Source image not found: public/'.self::SOURCE);
        }

        // Filed under "Reports" (not a news article), so its URL is
        // /reports/{slug} and the card is labelled REPORTS. Keyed on the
        // SLUG: keying on the title "Report" minted a singular duplicate
        // alongside the seeded "Reports", which MergeReportCategory then
        // had to clean up.
        $category = Category::firstOrCreate(['slug' => 'reports'], ['title' => 'Reports']);
        $author = Author::where('name', 'National Political Prisoner Coalition')->first();

        $intro = 'A new NPPC report on transnational repression — how governments reach across borders to '
            .'silence exiles, journalists, and diaspora communities — built on Freedom House\'s research, with '
            .'a live watch of the political exiles we document.';

        // Fallback body (the /news URL normally redirects straight to the report).
        $body = '<p>This report lives at <a href="/transnational-repression">/transnational-repression</a>.</p>';

        $attributes = [
            'title' => 'Transnational Repression: How States Silence Dissent Across Borders',
            'slug' => self::SLUG,
            'intro' => $intro,
            'body' => $body,
            'image' => self::IMAGE,
            'published_at' => '2025-06-28 09:00:00',
        ];
        if ($category) {
            $attributes['category_id'] = $category->id;
        }
        if ($author) {
            $attributes['author_id'] = $author->id;
        }

        $article = Article::where('slug', self::SLUG)->first();
        if ($article) {
            $article->fill($attributes)->save();
            $this->info("Updated article: {$article->title}");
        } else {
            $article = Article::create($attributes);
            $this->info("Created article: {$article->title}");
        }

        $this->info('Card opens: /transnational-repression (via /news/'.self::SLUG.' redirect).');

        return self::SUCCESS;
    }
}
