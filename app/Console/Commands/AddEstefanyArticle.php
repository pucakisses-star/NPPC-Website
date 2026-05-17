<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Publish article on the ICE arrest and detention of Nashville
 * journalist Estefany María Rodríguez Flórez — and the press-
 * freedom amicus filed by the Reporters Committee for Freedom of
 * the Press (RCFP) on March 16, 2026.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddEstefanyArticle extends Command {
    protected $signature = 'articles:add-estefany';
    protected $description = 'Publish article on the ICE arrest of journalist Estefany Rodríguez Flórez (2026)';

    private const SLUG     = 'estefany-rodriguez-florez-ice-arrest-first-amendment-2026';
    private const PUB_DATE = '2026-03-30 22:39:51';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'Repression'], ['slug' => 'repression']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        $body = <<<'BODY'
<p><em>ICE arrests a Colombian-born journalist covering immigration enforcement in Nashville, ships her to an Alabama detention center, and faces a First Amendment lawsuit and a 24-organization press-freedom amicus brief before a federal judge releases her on $10,000 bond two weeks later. RCFP calls it "a potent means of suppressing newsgathering and reporting."</em></p>

<p>On <strong>March 4, 2026</strong>, U.S. Immigration and Customs Enforcement officers arrested <strong>Estefany María Rodríguez Flórez</strong>, a 36-year-old Colombian journalist working for <strong>Nashville Noticias</strong>, a Spanish-language news outlet. She was driven out of Tennessee and held at an ICE detention facility in Alabama. The arrest came as Rodríguez was increasingly covering the Trump administration's immigration-enforcement operations and their effects on Nashville's immigrant community — work that, by her family's and her attorney's account, the federal government did not appreciate.</p>

<p>The arrest interrupted a documented immigration process. Rodríguez had entered the United States legally on a tourist visa in March 2021, applied for political asylum before that visa expired, and built her life in Tennessee. She is married to a <strong>U.S. citizen</strong>, has a <strong>seven-year-old U.S.-citizen daughter</strong>, holds a <strong>valid work permit</strong>, and has a <strong>pending green card application</strong>. In Colombia, she had worked as a reporter covering armed and militant groups before death threats forced her to flee — the very pattern of journalism-under-threat that U.S. asylum law was designed to recognize.</p>

<h2>The legal challenge</h2>

<p>Rodríguez's attorneys filed in the <strong>U.S. District Court for the Middle District of Tennessee</strong>, asserting violations of the First, Fourth, and Fifth Amendments. Their central allegation: the ICE action was retaliation for her journalism. The filings noted both the timing — coming during a coverage cycle in which she had been visibly reporting on ICE itself — and the disproportion between her documented status (legally present, work-authorized, married to a citizen, in a pending green card track) and the choice to detain her at all.</p>

<p>On <strong>March 16, 2026</strong>, the <strong>Reporters Committee for Freedom of the Press (RCFP)</strong> filed an amicus brief in support of her habeas petition, joined by the National Association of Hispanic Journalists, the Committee to Protect Journalists, the National Press Club Journalism Institute, the Foreign Press Association USA, the International Women's Media Foundation, and other press-freedom organizations.</p>

<p>The brief made an argument the federal courts have not yet had to address directly at this scale: that <strong>"the arrest and detention of non-citizen reporters can serve as a potent means of suppressing newsgathering and reporting."</strong> The coalition argued that non-citizen journalists — uniquely positioned to cover immigrant communities and immigration enforcement — must be permitted to rapidly challenge the constitutional validity of their detentions in federal district court, rather than being stranded indefinitely in ICE custody on procedural grounds while their First Amendment interests are degraded.</p>

<p>On <strong>March 19, 2026</strong>, three days after the amicus filing, a federal judge ordered Rodríguez <strong>released on $10,000 bond</strong>.</p>

<h2>What the case is part of</h2>

<p>The RCFP brief situated Rodríguez's case alongside two other prosecutions the press-freedom community had already taken on:</p>

<ul>
  <li><strong>Rümeysa Öztürk</strong>, the Tufts Ph.D. student whose F-1 visa was revoked after she co-authored an op-ed critical of Israel's military operations and who was detained in Louisiana for over six weeks before her release.</li>
  <li><strong>Mario Guevara</strong>, an Atlanta-based reporter detained by ICE in connection with his immigration coverage.</li>
</ul>

<p>RCFP filed analogous First Amendment briefs in both. Taken together, the three cases describe a pattern: <strong>federal immigration custody being used as a censorship instrument</strong> against journalists, students, and other non-citizens whose protected speech the executive branch finds inconvenient. The pattern overlaps with the State Department's "foreign policy deportability" theory used against Mahmoud Khalil, Mohsen Mahdawi, and Yunseo Chung in the Palestine-organizing arrests.</p>

<h2>Where things stand</h2>

<p>Rodríguez is out of detention but her case continues. Her removal proceedings remain open; her asylum application and green-card application both predate the arrest and continue to be adjudicated. Her First/Fourth/Fifth Amendment damages claims, depending on how the litigation evolves, may produce a federal-court ruling on whether retaliatory ICE detention of journalists is constitutional — a ruling whose implications would reach far beyond a single Nashville newsroom.</p>

<p>The Reporters Committee's underlying point — that a press apparatus is not free if the government can pick off reporters one at a time for ordinary journalism, simply by relying on the leverage that non-citizen status provides — has now been put before a federal judge. How the court handles it will signal what protections, if any, non-citizen journalists in the United States can rely on going forward.</p>
BODY;

        $data = [
            'title'        => 'ICE Arrests a Nashville Journalist. RCFP and 23 Press-Freedom Groups File for Her Release.',
            'intro'        => "ICE arrests a Colombian-born journalist covering immigration enforcement in Nashville, ships her to an Alabama detention center, and faces a First Amendment lawsuit and a 24-organization press-freedom amicus brief before a federal judge releases her on \$10,000 bond two weeks later. RCFP calls it \"a potent means of suppressing newsgathering and reporting.\"",
            'body'         => $body,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'published_at' => Carbon::parse(self::PUB_DATE),
            'citations_json' => [
                ['title' => 'RCFP — The ICE arrest of Estefany Maria Rodriguez Florez raises serious First Amendment concerns', 'url' => 'https://www.rcfp.org/estefany-rodriguez-ice-detention-tennessee/'],
                ['title' => 'CPJ — Timeline: Estefany Rodríguez\'s arrest and ICE detention', 'url' => 'https://cpj.org/2026/03/timeline-estefany-rodriguezs-arrest-and-ice-detention/'],
                ['title' => '@rcfp on X (origin tweet)', 'url' => 'https://x.com/rcfp/status/2038748067000942834'],
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
