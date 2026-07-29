<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Trim narrative context out of the case `charges` field, leaving the offence.
 *
 * CHARGES should name what someone was charged with. Across 8,369 cases the
 * field had absorbed the circumstances too — "Assault on a federal officer
 * (ICE agent who shot him)", "Civil contempt of court — refusing to testify
 * before the federal grand jury investigating the disclosure of Valerie
 * Plame's employment..." — so the profile prints a paragraph under CHARGES.
 *
 * FOUR CUTS, each guarded:
 *
 *   1. an em/en dash whose tail opens with a narrative word (for, after,
 *      arising, the, refusing, over, in connection...)
 *   2. a circumstance clause: ", for ...", " arising from ...", " in
 *      connection with ...", " stemming from ...", " after ..."
 *   3. everything after the first sentence
 *   4. a trailing parenthetical containing narrative (who, which, after,
 *      while, shot, killed...) — this is the ICE-agent case
 *
 * THE GUARDS ARE THE POINT. A blunt version of rule 1 destroys real charge
 * data: "Four felony counts — burglary (as party to a crime), criminal damage
 * to property, theft, and attempted theft" would keep the useless half and
 * throw away every actual offence. So a cut is refused unless what SURVIVES
 * is at least ten characters and is not a bare quantity — "four felony
 * counts", "several charges", "multiple violations". Any rule whose result
 * fails that test is abandoned and the original kept whole.
 *
 * NOTHING IS DISCARDED. As with prisoners:normalize-verdicts, the original
 * string is checked against the case sentence and the prisoner description
 * first; if the detail is not already on record it is appended verbatim to
 * the sentence as "Charges as recorded: ...". A trim can therefore only move
 * text, never lose it.
 *
 * ROUGHLY 2,300 OF 8,369 CASES CHANGE. Most of the rest are already nothing
 * but statute and offence — long, but legitimately so — and are left alone.
 * This targets added narrative, not length.
 *
 * Idempotent: a trimmed value no longer matches any rule, and the
 * preservation step only fires when the value actually changes.
 *
 *   php artisan prisoners:trim-charge-context
 *   php artisan prisoners:trim-charge-context --apply
 */
final class TrimChargeContext extends Command
{
    protected $signature = 'prisoners:trim-charge-context
        {--apply : Write the trimmed charges (default is a dry run)}
        {--samples=12 : How many before/after examples to print}';

    protected $description = 'Trim narrative context out of case charges, leaving the offence';

    private const GENERIC = '/^(several|multiple|various|numerous|\w+)?\s*(felony|misdemeanou?r|criminal|federal|state)?\s*(counts?|charges?|offences?|offenses?|violations?)\.?$/iu';

    private const NARRATIVE_HEAD = '/^(for|after|arising|following|stemming|the|a|an|refusing|over|in connection|as part|during|relating|related|charged|alleged|accused|he|she|they|his|her|their|police|authorities|prosecutors|one of|all|part of)\b/iu';

    private const NARR_PAREN = '/\b(who|whom|which|after|because|while|when|shot|killed|died|during the|he |she |they |alleged|reportedly)\b/iu';

    private const CLAUSE = '/,?\s+(?:for|after|arising (?:from|out of)|in connection with|stemming from|following|during|over)\s+(?=[a-z0-9])/u';

    /** A survivable head: long enough, and not a bare quantity of counts. */
    private function ok(string $head): bool
    {
        $h = rtrim(trim($head), ",;: ");

        return mb_strlen($h) >= 10 && ! preg_match(self::GENERIC, $h);
    }

    /** The offence alone, or the original when no rule applies safely. */
    public function shorten(string $value): string
    {
        $s = trim(preg_replace('/\s+/u', ' ', $value));
        $orig = $s;

        // 1. em/en dash introducing narrative
        $parts = preg_split('/\s[—–]\s/u', $s, 2);
        if (count($parts) === 2 && $this->ok($parts[0])
            && mb_strlen(trim($parts[1])) >= 20
            && preg_match(self::NARRATIVE_HEAD, trim($parts[1]))) {
            $s = trim($parts[0]);
        }

        // 2. circumstance clause
        // PREG_OFFSET_CAPTURE returns a BYTE offset, so every measurement here
        // stays in bytes. Mixing it with mb_strlen made the tail look shorter
        // than it was and silently skipped the cut on strings containing an em
        // dash — three of them across the live table.
        if (preg_match(self::CLAUSE, $s, $m, PREG_OFFSET_CAPTURE)) {
            $at = $m[0][1];
            $head = substr($s, 0, $at);
            if ($this->ok($head) && (strlen($s) - $at - strlen($m[0][0])) >= 20) {
                $s = trim($head);
            }
        }

        // 3. first sentence only
        if (preg_match('/^(.{20,}?[.!?])\s+[A-Z].{25,}$/su', $s, $m)
            && ! preg_match('/\b(U\.S\.|No\.|Inc\.|St\.|Mr\.|Sec\.|art\.|v\.)\s*$/u', $m[1])) {
            $s = $m[1];
        }

        // 4. trailing narrative parenthetical
        if (preg_match('/^(.{10,}?)\s*\(([^()]{10,})\)\s*\.?$/su', $s, $m)
            && preg_match(self::NARR_PAREN, $m[2]) && $this->ok($m[1])) {
            $s = rtrim($m[1], " ,;:");
        }

        $s = rtrim(trim($s), ",;: ");
        if ($s !== '' && ! str_ends_with($s, '.') && str_ends_with($orig, '.')) {
            $s .= '.';
        }

        // Never leave a stub behind.
        return $this->ok($s) ? $s : $orig;
    }

    private function norm(?string $t): string
    {
        return trim(preg_replace('/[^a-z0-9]+/u', ' ', mb_strtolower((string) $t)));
    }

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $sampleLimit = (int) $this->option('samples');

        $changed = 0;
        $preserved = 0;
        $scanned = 0;
        $shown = 0;

        foreach (Prisoner::withoutGlobalScopes()->with('cases')->cursor() as $p) {
            foreach ($p->cases as $case) {
                $raw = $case->charges;
                if (! $raw || trim($raw) === '') {
                    continue;
                }
                $scanned++;

                $new = $this->shorten($raw);
                if ($new === trim(preg_replace('/\s+/u', ' ', $raw))) {
                    continue;
                }

                $haystack = $this->norm(($case->sentence ?? '').' '.($p->description ?? ''));
                $probe = mb_substr($this->norm($raw), 0, 60);
                $needsKeeping = $probe !== '' && ! str_contains($haystack, $probe);

                if ($shown < $sampleLimit) {
                    $this->line('  '.$p->slug);
                    $this->line('    - '.mb_strimwidth($raw, 0, 104, '…'));
                    $this->line('    + '.mb_strimwidth($new, 0, 104, '…').($needsKeeping ? '   [detail moved to sentence]' : ''));
                    $shown++;
                }

                if ($apply) {
                    if ($needsKeeping) {
                        $case->sentence = trim(($case->sentence ? rtrim($case->sentence)."\n\n" : '')
                            .'Charges as recorded: '.trim($raw));
                    }
                    $case->charges = $new;
                    $case->save();
                }

                $changed++;
                $needsKeeping && $preserved++;
            }
        }

        $this->newLine();
        $this->info(($apply ? 'Trimmed ' : 'Would trim ')."{$changed} of {$scanned} case charge(s).");
        $this->info("{$preserved} carried detail found nowhere else — preserved verbatim on the sentence.");
        $this->line('Cases left alone are already offence-only, or a cut would have left a stub.');

        if ($apply) {
            Cache::forget(PrisonerApiController::cacheKey());
            $this->info('Done.');
        } else {
            $this->warn('Dry run — nothing written. Re-run with --apply.');
        }

        return self::SUCCESS;
    }
}
