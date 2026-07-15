<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Mechanical description cleanup for prisoner records:
 *   1. Re-joins OCR line-break hyphenation ("con- victed" -> "convicted",
 *      "Leav- enworth" -> "Leavenworth"). Only joins when the second fragment
 *      is NOT a real word in the corpus, so legitimate suspended hyphens
 *      ("cigar- and tobacco workers", "Nicaraguan- American") are left intact.
 *   2. Fixes the OCR typo "Penitentiaiy" -> "Penitentiary".
 *   3. Corrects the Idaho city "St. Marie" -> "St. Maries".
 *   4. Collapses runs of spaces (newlines are preserved).
 *
 * The "real word" set is built from the corpus itself (tokens appearing >= 4
 * times outside hyphen-split regions). Idempotent; only writes changed rows.
 * Run with --dry to preview counts.
 */
final class CleanPrisonerDescriptions extends Command
{
    protected $signature = 'prisoners:clean-descriptions {--dry : Preview without writing}';

    protected $description = 'Fix OCR hyphenation, the Penitentiary typo, St. Maries, and double spaces in descriptions';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry');

        // Pass 1: build the corpus real-word set (tokens seen >= 4x outside hyphen breaks).
        $counts = [];
        foreach (Prisoner::withUnderReview()->whereNotNull('description')->pluck('description') as $d) {
            $stripped = preg_replace('/[A-Za-z]+- [A-Za-z]+/', ' ', (string) $d);
            preg_match_all('/[A-Za-z]{2,}/', $stripped, $mm);
            foreach ($mm[0] as $w) {
                $w = strtolower($w);
                $counts[$w] = ($counts[$w] ?? 0) + 1;
            }
        }
        $wordset = [];
        foreach ($counts as $w => $c) {
            if ($c >= 4) {
                $wordset[$w] = true;
            }
        }

        $changed = 0;
        $hyphen = 0;
        $pen = 0;
        $marie = 0;
        $spaces = 0;

        Prisoner::withUnderReview()
            ->whereNotNull('description')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($wordset, $dry, &$changed, &$hyphen, &$pen, &$marie, &$spaces) {
                foreach ($rows as $p) {
                    $orig = (string) $p->description;
                    $d = $orig;

                    // 1. Re-join OCR line-break hyphens (only when 2nd fragment isn't a real word).
                    $d = preg_replace_callback('/([A-Za-z]+)- ([A-Za-z]+)/', function ($m) use ($wordset, &$hyphen) {
                        if (isset($wordset[strtolower($m[2])])) {
                            return $m[0]; // real word after -> suspended hyphen, leave it
                        }
                        $hyphen++;

                        return $m[1].$m[2];
                    }, $d);

                    // 2. OCR typo.
                    $d = str_replace(['Penitentiaiy', 'penitentiaiy'], ['Penitentiary', 'penitentiary'], $d, $pc);
                    $pen += $pc;

                    // 3. Idaho city.
                    $d = preg_replace('~St\. Marie(?![a-z\'])~', 'St. Maries', $d, -1, $mc);
                    $marie += $mc;

                    // 4. Collapse runs of spaces (keep newlines).
                    $d = preg_replace('/ {2,}/', ' ', $d, -1, $sc);
                    $spaces += $sc;

                    if ($d !== $orig) {
                        $changed++;
                        if (! $dry) {
                            Prisoner::withUnderReview()->whereKey($p->getKey())->update(['description' => $d]);
                        }
                    }
                }
            });

        $verb = $dry ? 'Would change' : 'Changed';
        $this->info("{$verb} {$changed} description(s).");
        $this->line("  hyphen re-joins:      {$hyphen}");
        $this->line("  Penitentiary typos:   {$pen}");
        $this->line("  St. Maries fixes:     {$marie}");
        $this->line("  space collapses:      {$spaces}");

        if (! $dry && $changed > 0) {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        return self::SUCCESS;
    }
}
