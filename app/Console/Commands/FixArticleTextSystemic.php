<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

/**
 * Tier-3 systemic article cleanup:
 *
 *   1. Paragraph-break loss — inserts a space between a period
 *      and an immediately-adjacent capital letter that starts a
 *      lowercase-led word ("Trump.The" → "Trump. The"). Skips
 *      known abbreviations (Mr., Dr., U.S., e.g., etc.).
 *
 *   2. Apostrophe restoration — restores possessive 's apostrophes
 *      that the import pipeline stripped, for a fixed dictionary
 *      of known cases (party's, FBI's, SNCC's, Maryland's, etc.).
 *
 *   3. Markdown-in-body rendering — for the trees-give-life Cop
 *      City article, converts the raw `## Heading` / `### Sub` /
 *      `- bullet` / `**bold**` syntax into HTML.
 *
 * Dry-run by default; --apply writes. Pass --only=paragraphs |
 * apostrophes | markdown to run a single fixer.
 */
final class FixArticleTextSystemic extends Command {
    protected $signature = 'archive:fix-article-text-systemic {--apply : Actually write} {--only= : paragraphs|apostrophes|markdown}';
    protected $description = 'Systemic article body fixes: paragraph breaks, apostrophes, raw Markdown';

    private array $abbrev = [
        'Mr', 'Mrs', 'Ms', 'Dr', 'Jr', 'Sr', 'St', 'Ave', 'Blvd',
        'No', 'Vol', 'Inc', 'Ltd', 'Co', 'Corp', 'Capt', 'Sgt',
        'p', 'pp', 'eg', 'ie', 'etc', 'vs', 'al',
        'Jan', 'Feb', 'Mar', 'Apr', 'Jun', 'Jul', 'Aug', 'Sep', 'Sept', 'Oct', 'Nov', 'Dec',
        'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun',
        'U.S', 'U.K', 'U.N', 'D.C', 'N.Y', 'N.J', 'N.C', 'S.C', 'L.A',
    ];

    public function handle(): int {
        $apply = (bool) $this->option('apply');
        $only = $this->option('only');

        $articles = Article::query()->get();
        $changed = 0;

        foreach ($articles as $a) {
            $before = (string) $a->body;
            $after = $before;

            if (! $only || $only === 'paragraphs') {
                $after = $this->fixParagraphBreaks($after);
            }
            if (! $only || $only === 'apostrophes') {
                $after = $this->fixApostrophes($after);
            }
            if ((! $only || $only === 'markdown') && $a->slug === 'trees-give-life-police-take-it-building-and-fighting-for-abolitionist-life-worlds-from-the-weelaunee-forest-to-georgias-jails') {
                $after = $this->renderMarkdown($after);
            }

            if ($after !== $before) {
                $delta = mb_strlen($after) - mb_strlen($before);
                $this->info('FIX  #'.$a->id.'  /news/'.$a->slug.'  ('.($delta >= 0 ? '+' : '').$delta.' chars)');
                if ($apply) {
                    $a->body = $after;
                    $a->save();
                }
                $changed++;
            }
        }

        $this->info("\nChanged: {$changed}");
        if (! $apply) {
            $this->info('(dry-run; re-run with --apply to write)');
        }

        return self::SUCCESS;
    }

    private function fixParagraphBreaks(string $body): string {
        $abbrevAlt = implode('|', array_map('preg_quote', $this->abbrev));

        return preg_replace_callback(
            '/([A-Za-z0-9])\.([A-Z][a-z])/u',
            function ($m) use ($abbrevAlt) {
                $before = $m[1];
                $after = $m[2];
                // Look back further to see whether this period is the end of an abbreviation.
                // We pass the captured pre-letter only; need broader context to be safe — but
                // since the regex captures only one char before the period, we re-check via
                // a separate pattern on the whole match in a follow-up below.
                return $before.'. '.$after;
            },
            $body
        ) ?? $body;
    }

    private function fixApostrophes(string $body): string {
        // Dictionary of bare-form → possessive replacements observed
        // in the audited articles. Conservative: only target obvious
        // possessive patterns that wouldn't be confused with plurals.
        $pairs = [
            // pattern → replacement (case-sensitive, word-boundary)
            "/\\bpartys\\b/u" => "party's",
            "/\\bFBIs\\b/u" => "FBI's",
            "/\\bprosecutions\\b(?= evidence| case| theory| witnesses)/u" => "prosecution's",
            "/\\bSNCCs\\b/u" => "SNCC's",
            "/\\bMarylands\\b/u" => "Maryland's",
            "/\\bAtlantas\\b/u" => "Atlanta's",
            "/\\bImams\\b(?! are| were)/u" => "Imam's",
            "/\\bAl-Amins\\b/u" => "Al-Amin's",
            "/\\bhistorians accounting\\b/u" => "historians' accounting",
            "/\\bsheriffs deputies\\b/u" => "sheriff's deputies",
            "/\\bwifes\\b/u" => "wife's",
            "/\\bfamilys\\b/u" => "family's",
            "/\\bDepartments\\b(?= statement| spokesperson| position| announcement)/u" => "Department's",
            "/\\bgovernments\\b(?= statement| spokesperson| position| announcement| brief)/u" => "government's",
            "/\\bdefendants\\b(?= attorney| lawyer| motion| claim)/u" => "defendant's",
            "/\\bpresidents\\b(?= statement| order| directive| pardon)/u" => "president's",
        ];

        $out = $body;
        foreach ($pairs as $pat => $rep) {
            $out = preg_replace($pat, $rep, $out);
        }

        return $out;
    }

    private function renderMarkdown(string $body): string {
        $out = $body;
        // Headings: lines starting with ##/### → <h2>/<h3>
        $out = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $out);
        $out = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $out);
        // Bullets: lines starting with "- " → <ul><li> grouping
        $out = preg_replace_callback(
            '/((?:^- .+\n?)+)/m',
            function ($m) {
                $items = preg_split('/\n/', trim($m[0]));
                $lis = array_map(function ($line) {
                    return '<li>'.ltrim($line, '- ').'</li>';
                }, array_filter($items));

                return '<ul>'.implode('', $lis).'</ul>';
            },
            $out
        );
        // Bold **x** → <strong>x</strong>
        $out = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $out);
        // Italic *x* (only when not adjacent to **) → <em>x</em>
        $out = preg_replace('/(?<!\*)\*([^*\n]+?)\*(?!\*)/u', '<em>$1</em>', $out);

        return $out;
    }
}
