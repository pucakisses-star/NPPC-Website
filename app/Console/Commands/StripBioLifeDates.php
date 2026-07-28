<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Strip life dates out of prisoner descriptions, moving them into the
 * birthdate / death_date fields where those are empty.
 *
 * Bios accumulated openings like "William Sanger (November 12, 1873 – July
 * 25, 1961) was an American architect" — the dates belong in the dedicated
 * fields, which drive the age display, the deceased dagger and the profile
 * date rows, not in the prose.
 *
 * WHAT IS MATCHED. A parenthetical containing a life-date form:
 *
 *   (1827–1908)  (May 28, 1859 – April 18, 1953)  (born 1959)  (b. 1855)
 *   (died 1908)  (c. 1814 – 1890)
 *
 * ...but only when it reads as a life span of the person themselves:
 *
 *   - a date range must span a plausible lifetime (15–110 years), which
 *     excludes custody spans like "(2007-2009)" and campaign periods like
 *     "(1989-1993)"; and
 *   - the parenthetical must either directly follow the person's name
 *     (accent-insensitive, tolerant of Sr./Jr./III and of "later NAME"
 *     alias forms) or be followed by "was"/"is"/"were" — the biographical
 *     apposition. This excludes career spans such as "delegate to the U.S.
 *     House (1971-1991)" and "professor at Wellesley College (1896-1918)",
 *     all of which were verified by hand against the live corpus.
 *
 * WHAT IS DONE with a match, per date and precision-aware:
 *
 *   - field empty                     -> set it from the bio, then strip
 *   - field agrees                    -> strip (the field already has it)
 *   - field year-only, bio has m/d    -> upgrade the field, then strip
 *   - field disagrees                 -> CONFLICT: print loudly, touch
 *                                        nothing, leave the bio alone
 *
 * A handful of narrative openings ("Name, born June 24, 1976, was...") are
 * fixed surgically, preserving birthplaces: "born October 9, 1949 in
 * Huntingdon" becomes "born in Huntingdon" with the date moved to the
 * field. Mid-bio narrative sentences ("He was born on January 14, 1817 and
 * died on March 7, 1896.") are deliberately NOT touched — several were
 * added on request precisely because the profile needed the prose.
 *
 * Dry-run by default; every change prints as a before/after fragment.
 *
 *   php artisan prisoners:strip-bio-dates
 *   php artisan prisoners:strip-bio-dates --slug=william-sanger
 *   php artisan prisoners:strip-bio-dates --apply
 */
final class StripBioLifeDates extends Command
{
    protected $signature = 'prisoners:strip-bio-dates {--apply : Save the changes} {--slug= : Comma-separated slugs to limit the run}';

    protected $description = 'Move life dates from prisoner descriptions into the birthdate/death_date fields and strip them from the prose';

    private const MONTHS = [
        'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
        'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
        'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12,
    ];

    /**
     * Narrative openings fixed by exact replacement. Each entry may also
     * carry dates to set; null keeps the field as it is (already correct).
     * slug => [search, replace, [birth y,m,d]|null, [death y,m,d]|null]
     */
    private const SURGICAL = [
        'steven-donziger' => ['Donziger, born September 14, 1961, is', 'Donziger is', null, null],
        'joseph-konopka' => ['Konopka, born June 24, 1976, was', 'Konopka was', null, null],
        'carl-hampton' => ['Hampton, born December 17, 1948, was', 'Hampton was', null, null],
        'john-theodore-glick' => ['born October 9, 1949 in Huntingdon', 'born in Huntingdon', null, null],
        'harry-eisman' => ['born November 26, 1913 in Kishinev', 'born in Kishinev', null, null],
        'donato-carrillo' => ['born August 4, 1894 in Sant\'Agata', 'born in Sant\'Agata', null, null],
        'ruby-montoya' => ['Ruby Montoya, born in 1990 and raised in Phoenix', 'Ruby Montoya, raised in Phoenix', [1990, null, null], null],
        'albert-nuh-washington' => ['born 1941 in New York City', 'born in New York City', [1941, null, null], null],
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $query = Prisoner::withoutGlobalScopes()->whereNotNull('description');
        if ($slugs = $this->option('slug')) {
            $query->whereIn('slug', array_map('trim', explode(',', $slugs)));
        }

        $changed = 0;
        $datesSet = 0;
        $conflicts = [];

        foreach ($query->orderBy('slug')->cursor() as $p) {
            $result = $this->process($p);
            if ($result === null) {
                continue;
            }
            [$description, $sets, $recordConflicts, $deltas] = $result;

            foreach ($recordConflicts as $c) {
                $conflicts[] = "{$p->slug}: {$c}";
            }

            if ($description === $p->description && ! $sets) {
                continue;
            }

            $this->line("{$p->name}  [{$p->slug}]");
            foreach ($deltas as $delta) {
                $this->line("    {$delta}");
            }
            foreach ($sets as [$field, $y, $m, $d]) {
                $this->line("    set {$field} <- ".sprintf('%04d%s%s', $y, $m ? sprintf('-%02d', $m) : '', $d ? sprintf('-%02d', $d) : ''));
                $datesSet++;
            }

            if ($apply) {
                foreach ($sets as [$field, $y, $m, $d]) {
                    $p->setPartialDate($field, $y, $m, $d);
                }
                $p->description = $description;
                $p->save();
            }
            $changed++;
        }

        $this->newLine();
        if ($conflicts) {
            $this->warn('CONFLICTS -- bio and field disagree; nothing was touched, review by hand:');
            foreach ($conflicts as $c) {
                $this->warn("  {$c}");
            }
            $this->newLine();
        }

        $this->info(($apply ? 'Applied to' : 'Would change')." {$changed} record(s); {$datesSet} date field(s) ".($apply ? 'set' : 'to set').'; '.count($conflicts).' conflict(s).');
        if (! $apply) {
            $this->info('Dry run. Re-run with --apply to save.');
        } else {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        return self::SUCCESS;
    }

    /** @return array{0: string, 1: array, 2: array, 3: array}|null */
    private function process(Prisoner $p): ?array
    {
        $desc = (string) $p->description;
        $out = $desc;
        $sets = [];
        $conflicts = [];
        $deltas = [];

        // ---- surgical narrative openings ----------------------------------
        if (isset(self::SURGICAL[$p->slug])) {
            [$search, $replace, $birth, $death] = self::SURGICAL[$p->slug];
            if (str_contains($out, $search)) {
                $out = Str::replaceFirst($search, $replace, $out);
                $deltas[] = "\"{$search}\" -> \"{$replace}\"";
                if ($birth && ! $p->birthdate) {
                    $sets[] = ['birthdate', ...$birth];
                }
                if ($death && ! $p->death_date) {
                    $sets[] = ['death_date', ...$death];
                }
            }
        }

        // ---- life-date parentheticals -------------------------------------
        $month = '(?:January|February|March|April|May|June|July|August|September|October|November|December)';
        $dateB = "(?:(?<b_m>{$month})\\s+(?<b_d>\\d{1,2}),?\\s+)?(?<b_y>1[0-9]{3}|20[0-2][0-9])";
        $dateD = "(?:(?<d_m>{$month})\\s+(?<d_d>\\d{1,2}),?\\s+)?(?<d_y>1[0-9]{3}|20[0-2][0-9])";
        $circa = '(?:c\\.\\s*|ca\\.\\s*|circa\\s+)?';
        $range = "/^\\s*{$circa}{$dateB}\\s*[–—-]\\s*{$circa}{$dateD}\\s*$/u";
        $born = "/^\\s*(?:born|b\\.)\\s+{$circa}{$dateB}\\s*$/iu";
        $died = "/^\\s*(?:died|d\\.)\\s+{$circa}{$dateD}\\s*$/iu";

        preg_match_all('/\\s?\\(([^()]*\\b(?:1[0-9]{3}|20[0-2][0-9])\\b[^()]*)\\)/u', $desc, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        $nameTokens = $this->tokens($p->name);

        foreach ($matches as $m) {
            $whole = $m[0][0];
            $inner = $m[1][0];
            $offset = $m[0][1];

            $b = $d = null;
            if (preg_match($range, $inner, $g)) {
                $b = $this->parts($g, 'b');
                $d = $this->parts($g, 'd');
                $span = $d[0] - $b[0];
                if ($span < 15 || $span > 110) {
                    continue; // custody or campaign span, not a lifetime
                }
            } elseif (preg_match($born, $inner, $g)) {
                $b = $this->parts($g, 'b');
            } elseif (preg_match($died, $inner, $g)) {
                $d = $this->parts($g, 'd');
            } else {
                continue;
            }

            // Adjacency: name directly before, or biographical "was/is" after.
            $pre = rtrim(rtrim(substr($desc, 0, $offset)), ',');
            $pre = preg_replace('/\\s*(?:Sr\\.?|Jr\\.?|III|II|IV)\\s*[,.]?\\s*$/', '', $pre);
            $preTokens = $this->tokens($pre);
            $last = $preTokens ? end($preTokens) : null;
            $post = substr($desc, $offset + strlen($whole));
            $bioPost = (bool) preg_match('/^\\s*,?\\s*(?:was|is|were)\\b/', $post);
            if (! ($last !== null && in_array($last, $nameTokens, true)) && ! $bioPost) {
                continue;
            }

            $vb = $this->verdict($p, 'birthdate', $b);
            $vd = $this->verdict($p, 'death_date', $d);
            if ($vb === 'conflict' || $vd === 'conflict') {
                $conflicts[] = "\"({$inner})\" vs birthdate=".($p->birthdate?->toDateString() ?: '-').' death_date='.($p->death_date?->toDateString() ?: '-');
                continue;
            }

            foreach ([['birthdate', $vb, $b], ['death_date', $vd, $d]] as [$field, $v, $parsed]) {
                if (in_array($v, ['set', 'upgrade'], true)) {
                    $sets[] = [$field, ...$parsed];
                }
            }

            $context = trim(substr($desc, max(0, $offset - 25), strlen($whole) + 45));
            $deltas[] = "strip \"({$inner})\"  near \"...{$context}...\"";
            $out = Str::replaceFirst($whole, '', $out);
        }

        if ($out === $desc && ! $sets && ! $conflicts) {
            return null;
        }

        $out = trim(str_replace(' ,', ',', preg_replace('/ {2,}/', ' ', $out)));

        return [$out, $sets, $conflicts, $deltas];
    }

    /** @return array{0: int, 1: int|null, 2: int|null}|null */
    private function parts(array $g, string $prefix): ?array
    {
        if (empty($g[$prefix.'_y'])) {
            return null;
        }

        return [
            (int) $g[$prefix.'_y'],
            ! empty($g[$prefix.'_m']) ? self::MONTHS[$g[$prefix.'_m']] : null,
            ! empty($g[$prefix.'_d']) ? (int) $g[$prefix.'_d'] : null,
        ];
    }

    /**
     * Compare a parsed bio date against the stored field, precision-aware.
     * Legacy records store year-only dates as January 1 with no precision
     * entry; those are treated as year precision rather than as a real
     * January 1.
     */
    private function verdict(Prisoner $p, string $field, ?array $parsed): string
    {
        if (! $parsed) {
            return 'na';
        }
        $stored = $p->{$field};
        if (! $stored) {
            return 'set';
        }
        if ((int) $stored->year !== $parsed[0]) {
            return 'conflict';
        }

        $precision = $p->date_precision[$field] ?? null;
        if ($precision === null && (int) $stored->month === 1 && (int) $stored->day === 1) {
            $precision = 'year'; // legacy year-only convention
        }
        $fm = in_array($precision, [null, 'day', 'month'], true) ? (int) $stored->month : null;
        $fd = in_array($precision, [null, 'day'], true) ? (int) $stored->day : null;

        if ($parsed[1] && $fm && $parsed[1] !== $fm) {
            return 'conflict';
        }
        if ($parsed[2] && $fd && $parsed[2] !== $fd) {
            return 'conflict';
        }
        if ($parsed[1] && ! $fm) {
            return 'upgrade';
        }

        return 'match';
    }

    /** Accent-insensitive lowercase word tokens. */
    private function tokens(?string $s): array
    {
        $s = strtolower(Str::ascii((string) $s));
        $s = preg_replace('/[^a-z ]/', ' ', $s);

        return array_values(array_filter(explode(' ', $s)));
    }
}
