<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

/**
 * Applies the 2026 editorial rewrite of 18 articles flagged in a site-wide
 * prose audit for formulaic machine-written voice, structural template
 * scaffolding ("why it matters" / "what comes next"), citation debris left in
 * visible text, unverifiable quotes, and — in one case — an apparently
 * fabricated source ("Tara Eaglewoman, Native Voices United"), which the
 * rewrite removes. Every fact, date, name, and documented quote in the
 * originals is preserved; only unverifiable material was cut.
 *
 * New bodies live in database/data/article-rewrites/{slug}.html. Two titles
 * are corrected (a missing verb; a stray "by National Political Prisoner
 * Coalition" appended to a headline), and two surgical fixes are applied to
 * articles outside the rewrite set (a missing apostrophe; a dangling
 * "on Tuesday" with no date anywhere in the piece).
 *
 * Only updates an article when its body differs from the rewrite, so the
 * command is idempotent. Third-party republished content (CLDC releases,
 * Mahmoud Khalil's letter, the Ed Mead memorial, historical essays) is
 * deliberately untouched. Supports --only=slug1,slug2 and --dry-run.
 */
final class ApplyArticleCopyRewrites extends Command
{
    protected $signature = 'articles:apply-copy-rewrites {--only= : Comma-separated slugs to limit to} {--dry-run : Report what would change without writing}';

    protected $description = 'Apply the editorial rewrites for the 18 articles flagged in the prose audit';

    /** Slugs receiving a rewritten body (file: database/data/article-rewrites/{slug}.html). */
    private const SLUGS = [
        'leonard-peltier-released-after-nearly-five-decades-of-incarceration',
        'president-biden-commutes-sentence-of-indigenous-activist-leonard-peltier',
        'immigrant-rights-activist-jeanette-vizguerra-released-on-bond-after-nine-months-in-ice-custody',
        'third-circuit-ruling-narrows-habeas-pathway-in-mahmoud-khalil-case-raising-prospect-of-renewed-detention-by-national-political-prisoner-coalition',
        'federal-judge-orders-restoration-of-tufts-student-rumeysa-ozturks-sevis-record-clearing-the-way-for-campus-work',
        'maduros-new-york-federal-case-heads-toward-march-hearing-as-defense-signals-immunity-capture-challenges',
        'trump-threatens-treason-charges-against-journalists-covering-iran-war',
        'trump-administration-creates-chilling-effect-on-free-speech-by-weaponizeing-immigration-enforcement-to-silences-political-opposition',
        'over-1300-student-visas-by-trump-admin-in-chilling-attack-on-civil-liberties',
        'domestic-terrorism-charging-escalation-2024-pattern',
        'campus-encampment-mass-arrests-spring-2024',
        'campus-repression-year-oct-2023-dec-2024-data',
        'steven-donziger-2024-scotus-cert-denial',
        'aafia-siddiqui-2024-clemency-push-fmc-carswell',
        'prairieland-defendants-convicted-most-counts-2026',
        'political-prisoner-2024-year-end-census',
        'guidelines-on-the-definition-of-political-prisoners',
        'us-attorney-general-directs-prosecutors-to-seek-death-penalty-for-luigi-mangione',
    ];

    /** Title corrections applied alongside the body rewrite. */
    private const TITLES = [
        'over-1300-student-visas-by-trump-admin-in-chilling-attack-on-civil-liberties'
            => 'Over 1,300 Student Visas Revoked by Trump Admin in Chilling Attack on Civil Liberties',
        'third-circuit-ruling-narrows-habeas-pathway-in-mahmoud-khalil-case-raising-prospect-of-renewed-detention-by-national-political-prisoner-coalition'
            => 'Third Circuit Ruling Narrows Habeas Pathway in Mahmoud Khalil Case, Raising Prospect of Renewed Detention',
    ];

    /** Small surgical string fixes on articles outside the rewrite set. */
    private const PATCHES = [
        'tampa-five-florida-prosecutors-drop-felony-charges-against-usf-anti-dei-protesters' => [
            ['University of South Floridas Tampa campus', 'University of South Florida\'s Tampa campus'],
        ],
        'doj-working-group-determines-prosecutors' => [
            // "on Tuesday" with no date anywhere in the piece — drop the
            // dangling relative day; the article's dateline carries the date.
            ['The Justice Department on Tuesday released', 'The Justice Department released'],
        ],
    ];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $only = array_filter(array_map('trim', explode(',', (string) $this->option('only'))));
        $set = 0; $skip = 0; $fail = 0;

        foreach (self::SLUGS as $slug) {
            if ($only && ! in_array($slug, $only, true)) {
                continue;
            }
            $file = database_path('data/article-rewrites/'.$slug.'.html');
            if (! is_file($file)) {
                $this->warn("Rewrite file missing, skipping: {$slug}");
                $fail++;
                continue;
            }
            $article = Article::where('slug', $slug)->first();
            if (! $article) {
                $this->warn("Article not found, skipping: {$slug}");
                $fail++;
                continue;
            }

            $body = trim(file_get_contents($file));
            $newTitle = self::TITLES[$slug] ?? null;
            $bodySame = trim((string) $article->body) === $body;
            $titleSame = $newTitle === null || $article->title === $newTitle;
            if ($bodySame && $titleSame) {
                $this->line("Already applied: {$slug}");
                $skip++;
                continue;
            }

            if ($dry) {
                $this->info('Would rewrite: '.$slug.($titleSame ? '' : ' (+title)'));
                $set++;
                continue;
            }

            $article->body = $body;
            if ($newTitle !== null) {
                $article->title = $newTitle;
            }
            $article->save();
            $this->info('Rewrote: '.$slug.($newTitle !== null ? ' (+title)' : ''));
            $set++;
        }

        foreach (self::PATCHES as $slug => $pairs) {
            if ($only && ! in_array($slug, $only, true)) {
                continue;
            }
            $article = Article::where('slug', $slug)->first();
            if (! $article) {
                $this->warn("Patch target not found, skipping: {$slug}");
                $fail++;
                continue;
            }
            $body = (string) $article->body;
            $patched = $body;
            foreach ($pairs as [$from, $to]) {
                $patched = str_replace($from, $to, $patched);
            }
            if ($patched === $body) {
                $this->line("Patch already applied (or text not found): {$slug}");
                $skip++;
                continue;
            }
            if ($dry) {
                $this->info("Would patch: {$slug}");
                $set++;
                continue;
            }
            $article->body = $patched;
            $article->save();
            $this->info("Patched: {$slug}");
            $set++;
        }

        $this->info("\nDone. Applied={$set}  Skipped={$skip}  Missing={$fail}".($dry ? '  (dry run)' : ''));

        return self::SUCCESS;
    }
}
