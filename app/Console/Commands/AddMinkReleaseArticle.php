<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;

/**
 * Adds a news article to the dashboard about the "Northumberland 2" mink
 * fur-farm case (Cara Mitrano and Celeste Legere). Follows the site convention:
 * an original summary (intro + body) with a citations_json list linking the
 * sources, rather than reproducing the periodicals' text. The category ("news")
 * and author ("NPPC Editorial") are looked up by slug/name so it works across
 * environments (UUIDs differ between the snapshot and production).
 *
 * Idempotent — skips if the article slug already exists.
 */
final class AddMinkReleaseArticle extends Command
{
    protected $signature = 'articles:add-mink-release';

    protected $description = 'Add the "Northumberland 2" mink fur-farm news article';

    public function handle(): int
    {
        $slug = 'northumberland-2-mink-fur-farm-case';

        if (Article::where('slug', $slug)->exists()) {
            $this->info('Article already exists, skipping.');

            return self::SUCCESS;
        }

        $category = Category::where('slug', 'news')->first();
        $author = Author::where('name', 'NPPC Editorial')->first();

        $body = '<p>Cara Mitrano, 28, and Celeste Legere, 30, both of Worcester, Massachusetts, are accused of '
            .'breaking into the Stahl family fur farm (Richard H. Stahl &amp; Sons) in Rockefeller Township, '
            .'Northumberland County, Pennsylvania, overnight on October 18&ndash;19, 2024 and releasing more than '
            .'600 mink from their cages. The two were arrested on October 19, 2024, jailed at the Northumberland '
            .'County Prison, and released on bond the following month.</p>'
            .'<p>Prosecutors initially brought a felony corrupt-organizations charge under Pennsylvania&rsquo;s '
            .'racketeering (RICO) statute, but a judge dismissed it on July 21, 2025 after an evidentiary hearing on a '
            .'defense habeas-corpus motion; the felony &ldquo;ecoterrorism&rdquo; count was likewise dropped. The pair '
            .'still face more than a dozen counts, including burglary, theft, agricultural vandalism, criminal mischief, '
            .'loitering and prowling at night, agricultural trespass, and cruelty to animals &mdash; with an added '
            .'aggravated-animal-cruelty count &mdash; plus conspiracy.</p>'
            .'<p>The case has become a cause for animal-liberation supporters, who organize under the banner of the '
            .'&ldquo;Northumberland 2.&rdquo; A state trooper testified that &ldquo;anarchist propaganda&rdquo; was found '
            .'in the defendants&rsquo; vehicle, but on cross-examination acknowledged the materials were largely privacy '
            .'manuals and activist notes.</p>'
            .'<p>Both defendants returned to Massachusetts under conditions requiring regular check-ins and are scheduled '
            .'to stand trial in May 2026.</p>';

        $article = Article::create([
            'title' => 'Trial Set for the "Northumberland 2" After RICO Charge Dropped in Mink Fur-Farm Case',
            'slug' => $slug,
            'intro' => 'Two Massachusetts animal-liberation activists — Cara Mitrano and Celeste Legere, known to '
                .'supporters as the "Northumberland 2" — head to trial in 2026 over the October 2024 release of more '
                .'than 600 mink from a Pennsylvania fur farm. A judge dropped the felony corrupt-organizations (RICO) '
                .'charge against them in July 2025, and the felony "ecoterrorism" count was also dismissed, though more '
                .'than a dozen lesser charges remain.',
            'body' => $body,
            'image' => '',
            'published_at' => now(),
            'category_id' => $category?->id,
            'author_id' => $author?->id,
            'citations_json' => [
                [
                    'title' => 'North Central PA — Trial set for Massachusetts pair accused of releasing hundreds of mink',
                    'url' => 'https://www.northcentralpa.com/news/trial-set-for-massachusetts-pair-accused-of-releasing-hundreds-of-mink/article_6e34580b-d30b-4726-8851-f871f3ce5e9b.html',
                ],
                [
                    'title' => 'Unicorn Riot — RICO Charge Dropped in Northumberland 2 Mink Fur Farm Case',
                    'url' => 'https://unicornriot.ninja/2025/rico-charge-dropped-in-northumberland-2-mink-fur-farm-case/',
                ],
            ],
        ]);

        $this->info("Added article: {$article->title} (/news/{$article->slug})");
        $this->line('  category='.($category?->slug ?? 'null').' author='.($author?->name ?? 'null'));

        return self::SUCCESS;
    }
}
