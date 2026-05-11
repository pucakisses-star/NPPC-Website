<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Cleans up OCR artifacts in the description text of WWI Kohn prisoners.
 *
 * The source PDF's OCR splits many words letter-by-letter — e.g.
 *   "O c t o b e r 5, 1918, to D e c e m b e r 21"
 * gets imported verbatim as the description. This command collapses
 * those single-letter-spaced sequences back into proper words.
 *
 * Also normalizes the source-attribution suffix and multi-space runs.
 */
final class NormalizeKohnDescriptions extends Command {
    protected $signature = 'archive:normalize-kohn-descriptions {--dry : preview only, do not write} {--sample=3 : show N before/after samples}';
    protected $description = 'Collapse OCR letter-splits ("O c t o b e r" → "October") in WWI Kohn prisoner descriptions';

    private const ERA = '1910s';

    public function handle(): int {
        $dry = (bool) $this->option('dry');
        $sampleN = max(0, (int) $this->option('sample'));

        $query = Prisoner::query()->where('era', self::ERA);
        $total = (clone $query)->count();
        $this->info("Inspecting {$total} WWI-era prisoners…");

        $changed = 0;
        $unchanged = 0;
        $samples = [];

        $query->chunk(100, function ($batch) use (&$changed, &$unchanged, &$samples, $sampleN, $dry) {
            foreach ($batch as $prisoner) {
                $orig = (string) $prisoner->description;
                $clean = $this->normalize($orig);

                if ($clean === $orig) {
                    $unchanged++;

                    continue;
                }

                if (count($samples) < $sampleN) {
                    $samples[] = [$prisoner->name, $orig, $clean];
                }

                if (! $dry) {
                    $prisoner->description = $clean;
                    $prisoner->save();
                }
                $changed++;
            }
        });

        $this->info(($dry ? 'would update' : 'updated').": {$changed}");
        $this->info("unchanged: {$unchanged}");

        foreach ($samples as [$name, $before, $after]) {
            $this->line("\n--- {$name} ---");
            $this->line('BEFORE: '.mb_substr($before, 0, 200).(mb_strlen($before) > 200 ? '…' : ''));
            $this->line('AFTER:  '.mb_substr($after, 0, 200).(mb_strlen($after) > 200 ? '…' : ''));
        }

        return self::SUCCESS;
    }

    /**
     * Normalize OCR letter-splits in arbitrary text.
     * Public so other commands and tests can use it.
     */
    public function normalize(string $text): string {
        // Pass 1: collapse runs of 3+ single-letter alphabetic tokens. The
        // original OCR pattern emits e.g. "O c t o b e r" as 7 single-letter
        // tokens. 3+ is conservative enough to never catch real prose.
        $out = preg_replace_callback(
            '/\b([A-Za-z])((?:\s[A-Za-z]){2,})\b/u',
            fn ($m) => $m[1].str_replace(' ', '', $m[2]),
            $text
        );

        // Pass 2: handle common 2-letter trailing splits where pass 1 won't
        // trigger ("D e cember" - 1 letter + word) by collapsing capital +
        // space + lowercase-word pattern only for a known word list. We
        // intentionally restrict to a small allow-list so we don't merge
        // legitimate phrases like "I am" or "A new".
        $partials = [
            'A merican', 'C alifornia', 'C onnecticut', 'D ecember', 'D ecade',
            'F ebruary', 'G eorgia', 'I llinois', 'J anuary', 'J une', 'J uly',
            'M arch', 'M assachusetts', 'M innesota', 'M issouri',
            'N ovember', 'N ebraska', 'O ctober', 'O hio', 'O klahoma',
            'P ennsylvania', 'P enitentiary', 'S eptember', 'T exas',
            'V irginia', 'W ashington', 'W est Virginia', 'W isconsin',
        ];
        foreach ($partials as $p) {
            $compact = str_replace(' ', '', $p);
            $out = preg_replace('/\b'.preg_quote($p, '/').'\b/i', $compact, $out);
        }

        // Pass 2b: collapse common short 2-letter splits ("o n" → "on",
        // "i n" → "in", etc.). Restricted to a fixed allow-list so we don't
        // merge legitimate initialisms or single letters as a pair.
        // Preserves the case of the first letter so "H e" → "He", "h e" → "he".
        $shortPairs = ['on', 'in', 'at', 'of', 'is', 'as', 'to', 'be', 'he', 'do', 'an', 'my', 'by', 'so', 'up', 'us', 'we', 'if', 'or', 'am'];
        foreach ($shortPairs as $word) {
            $a = $word[0];
            $b = $word[1];
            // Match both cases of the first letter, preserve it
            $out = preg_replace_callback(
                '/(?<=\s)('.preg_quote($a, '/').'|'.preg_quote(strtoupper($a), '/').')\s'.preg_quote($b, '/').'(?=\s)/u',
                fn ($m) => $m[1].$b,
                $out
            );
        }

        // Pass 3: normalize multi-spaces to single space (but keep newlines).
        $out = preg_replace('/[ \t]{2,}/', ' ', $out);

        // Pass 4: tidy spaces around punctuation introduced by OCR ("word ,").
        $out = preg_replace('/\s+([,;:.])/u', '$1', $out);
        $out = preg_replace('/([,;:])(?=\S)/u', '$1 ', $out);

        return trim($out);
    }
}
