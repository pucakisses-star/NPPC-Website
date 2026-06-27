<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;

/**
 * Creates (or updates) the news article covering Robert Jacob Hoopes, the
 * Portland anti-ICE protester sentenced to 30 months in federal prison. Uses
 * the house byline ("National Political Prisoner Coalition") and the existing
 * "news" category, publishes it (dated to the June 12, 2026 report), and cites
 * the AP/OPB coverage.
 *
 * Idempotent: matches an existing article by slug/title and updates the text
 * in place without touching any cover image that may have been set separately.
 */
final class AddHoopesArticle extends Command
{
    protected $signature = 'articles:add-hoopes';

    protected $description = 'Add the news article for Robert Jacob Hoopes (Portland anti-ICE protester)';

    private const SLUG = 'robert-hoopes-anti-ice-protester-sentenced-30-months';

    public function handle(): int
    {
        $title = 'Portland Anti-ICE Protester Robert Jacob Hoopes Sentenced to 30 Months for Assaulting a Federal Officer';

        $intro = 'Robert Jacob Hoopes, a 25-year-old Portland protester, was sentenced to 30 months in '
            .'federal prison for throwing a rock that struck a U.S. Immigration and Customs Enforcement '
            .'officer in the head during an anti-ICE protest outside the agency\'s South Portland facility '
            .'in June 2025.';

        $body = <<<'HTML'
<p>Robert Jacob Hoopes, 25, of Portland, Oregon, was sentenced on June 11, 2026 to 30 months in federal prison for throwing a large rock that struck a U.S. Immigration and Customs Enforcement (ICE) Enforcement and Removal Operations officer in the head during a protest outside the agency&rsquo;s facility in South Portland on June 14, 2025. The rock opened a gash over the officer&rsquo;s eye.</p>
<p>Hoopes, a Reed College alumnus, pleaded guilty in February 2026 to aggravated assault of a federal employee with a dangerous weapon. Along with the prison term, U.S. District Judge Adrienne Nelson ordered three years of supervised release and more than $8,000 in restitution.</p>
<p>According to court records, the FBI identified Hoopes using facial-recognition technology, matching images of the protester to photographs published by The Oregonian/OregonLive and to a Reed College photo from April 2023 in which a distinctive tattoo was visible. Agents then tracked him to his Northeast Portland home.</p>
<p>Hoopes is among several people federally prosecuted in connection with the 2025 protests against stepped-up immigration enforcement in Portland.</p>
HTML;

        $citations = [
            [
                'title' => 'Associated Press / OPB',
                'content' => 'Claire Rush, "Anti-ICE protester in Portland sentenced to 30 months in prison for '
                    .'assaulting a federal officer," OPB / Associated Press, June 12, 2026. '
                    .'https://www.opb.org/article/2026/06/12/portland-anti-ice-protester-sentenced-in-prison/',
            ],
        ];

        $category = Category::where('slug', 'news')->first();
        $author = Author::where('name', 'National Political Prisoner Coalition')->first();

        $article = Article::where('slug', self::SLUG)
            ->orWhere('title', 'like', '%Hoopes%')
            ->first();

        $attributes = [
            'title' => $title,
            'slug' => self::SLUG,
            'intro' => $intro,
            'body' => $body,
            'citations_json' => $citations,
            'published_at' => '2026-06-12 09:00:00',
        ];
        if ($category) {
            $attributes['category_id'] = $category->id;
        }
        if ($author) {
            $attributes['author_id'] = $author->id;
        }

        if ($article) {
            // Preserve any cover image set separately; update only the text.
            $article->fill($attributes)->save();
            $this->info("Updated existing article: {$article->title}");
        } else {
            $article = Article::create($attributes);
            $this->info("Created article: {$article->title}");
        }

        $this->info("View: {$article->url}");

        return self::SUCCESS;
    }
}
