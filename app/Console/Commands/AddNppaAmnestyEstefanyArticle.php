<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Restore content at the original placeholder slug
 *   /news/nppa-joins-amnesty-international-in-demanding-release-of-journalist-estefany-rodriguez
 * which previously had no body. Covers NPPC/NPPA's joining of Amnesty
 * International's solidarity call for the release of Nashville Noticias
 * journalist Estefany Rodríguez Flórez from ICE custody in Alabama —
 * dated to the mid-March 2026 solidarity window, before her March 19
 * release. The separate `AddEstefanyArticle` covers the RCFP First
 * Amendment amicus + the actual release; the two articles are paired,
 * not duplicates.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddNppaAmnestyEstefanyArticle extends Command {
    protected $signature = 'articles:add-nppa-amnesty-estefany';
    protected $description = 'Restore NPPA + Amnesty International Estefany Rodríguez release-demand article at its original slug';

    private const SLUG     = 'nppa-joins-amnesty-international-in-demanding-release-of-journalist-estefany-rodriguez';
    private const PUB_DATE = '2026-03-14 09:00:00';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        $body = <<<'BODY'
<p><em>The National Political Prisoner Coalition joins Amnesty International, the National Press Photographers Association, the Reporters Committee for Freedom of the Press, and a broad coalition of press-freedom and human-rights organizations in demanding the immediate release of Nashville Noticias journalist Estefany Rodríguez Flórez from ICE detention. She was arrested while doing her job — covering an ICE enforcement operation outside Nashville — and is now being held in an Alabama immigration prison hundreds of miles from her family and her newsroom.</em></p>

<p>On <strong>March 4, 2026</strong>, <strong>Estefany Rodríguez Flórez</strong> — a Colombian-born Spanish-language journalist for <em>Nashville Noticias</em> — was arrested by U.S. Immigration and Customs Enforcement officers while reporting on an enforcement operation in the Nashville area. She was filming what was already a matter of intense public interest, in a public place, with the protections of the First Amendment, and the federal government took her into custody anyway. She has since been transferred to an immigration detention facility in <strong>Alabama</strong>, hundreds of miles from her home, her newsroom, and her family.</p>

<p>This week, <strong>Amnesty International</strong> joined the call from press-freedom and human-rights groups — the <strong>National Press Photographers Association (NPPA)</strong>, the <strong>Reporters Committee for Freedom of the Press</strong>, the <strong>Committee to Protect Journalists</strong>, the <strong>Tennessee Coalition for Open Government</strong>, and others — demanding her immediate release. The National Political Prisoner Coalition adds its name to that demand today.</p>

<h2>What Amnesty is saying</h2>

<p>Amnesty International has called Rodríguez's detention "a clear act of retaliation against journalists exercising their right to inform the public" and demanded that ICE release her without conditions. The organization's statement places her case in the broader 2025–2026 pattern of the Trump administration's second-term use of immigration enforcement against political speech and adversarial journalism — a pattern that has swept up Mahmoud Khalil, Rumeysa Öztürk, Mohsen Mahdawi, Yunseo Chung, Leqaa Kordia, and now a working Spanish-language journalist documenting that very enforcement apparatus.</p>

<h2>What NPPA is saying</h2>

<p>The National Press Photographers Association — a 75-year-old professional organization of visual journalists — has been unequivocal: "Reporters cannot do their jobs if the act of covering a federal enforcement operation can land them in immigration detention hundreds of miles from their families." NPPA has called on ICE to release Rodríguez immediately and to issue guidance ensuring that visual journalists documenting enforcement operations are not subjected to retaliatory detention.</p>

<h2>What this is, plainly</h2>

<p>The Trump administration's second term has produced a new and ugly category of political-prisoner case in the United States: people detained not under criminal statutes — which carry the protections of indictment, counsel, public trial, and proof beyond a reasonable doubt — but under <strong>civil immigration authority</strong>, which carries almost none of those protections and can be applied to anyone whose immigration status is anything less than U.S. citizenship. The same machinery that picked up Khalil at his Columbia apartment, Öztürk on a Somerville sidewalk, and Mahdawi at his green-card interview has now picked up a journalist whose only "offense" was pointing a camera at the very enforcement operation she has now disappeared into.</p>

<p>Detaining a journalist because she covered a story is the textbook definition of retaliation. Detaining her under immigration law instead of criminal law is the textbook 2025 workaround. Calling that combination something other than political imprisonment requires the kind of euphemism that, in NPPC's view, the moment no longer allows.</p>

<h2>The demand</h2>

<p>NPPC joins Amnesty International, NPPA, RCFP, CPJ, and the broader coalition in demanding:</p>

<ul>
  <li>The <strong>immediate release</strong> of Estefany Rodríguez Flórez from ICE custody.</li>
  <li>An end to the use of immigration detention as retaliation against journalists reporting on federal enforcement.</li>
  <li>Formal ICE guidance affirming that journalists — citizen, resident, or otherwise — covering enforcement operations are protected from retaliatory detention.</li>
</ul>

<p>Until she is home, her case stays open and her name stays on the list of people the United States is currently holding for political reasons. NPPC will continue to track her case and post updates as the campaign for her release progresses.</p>
BODY;

        $data = [
            'title'        => 'NPPC Joins Amnesty International in Demanding the Release of Journalist Estefany Rodríguez',
            'intro'        => "The National Political Prisoner Coalition joins Amnesty International, the National Press Photographers Association, the Reporters Committee for Freedom of the Press, and a broad coalition of press-freedom and human-rights organizations in demanding the immediate release of Nashville Noticias journalist Estefany Rodríguez Flórez from ICE detention.",
            'body'         => $body,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'published_at' => Carbon::parse(self::PUB_DATE),
            'citations_json' => [
                ['title' => 'Amnesty International — Statement on the detention of Estefany Rodríguez Flórez', 'url' => 'https://www.amnesty.org/en/latest/news/2026/03/usa-detained-journalist-estefany-rodriguez/'],
                ['title' => 'NPPA — Press freedom groups demand release of detained Nashville journalist', 'url' => 'https://nppa.org/news/press-freedom-groups-demand-release-detained-nashville-journalist'],
                ['title' => 'Reporters Committee for Freedom of the Press — Coalition letter on Estefany Rodríguez', 'url' => 'https://www.rcfp.org/rodriguez-detention-letter-2026/'],
                ['title' => 'Committee to Protect Journalists — US must release Nashville Spanish-language journalist Estefany Rodríguez Flórez', 'url' => 'https://cpj.org/2026/03/us-must-release-nashville-spanish-language-journalist-estefany-rodriguez-florez/'],
                ['title' => 'Tennessee Lookout — Detained Nashville Spanish-language journalist transferred to Alabama', 'url' => 'https://tennesseelookout.com/2026/03/06/detained-nashville-spanish-language-journalist-transferred-alabama/'],
            ],
        ];

        $existing = Article::where('slug', self::SLUG)->first();
        if ($existing) {
            $existing->update($data);
            $this->info('Updated article: '.$data['title']);
        } else {
            Article::create(['slug' => self::SLUG] + $data);
            $this->info('Created article: '.$data['title']);
        }

        return self::SUCCESS;
    }
}
