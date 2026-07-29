<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Strip age mentions out of bios and derive the birth year they imply.
 *
 * "Alexandria Ty Fite, 32, Riverside Historic Courthouse green-handprint
 * defendant, July 30 2022." — the 32 is press-report furniture, and 761
 * records carry something like it. The age comes out of the text, and where
 * the birthdate field is empty it is filled, at YEAR precision, with the year
 * the age implies.
 *
 * THE AGE IS ANCHORED TO THE EVENT, NEVER TO TODAY. "32" in a bio means 32
 * when the events happened, not 32 in whatever year this command runs. The
 * reference year is the first year named in the same sentence as the age
 * (Fite's 32 sits beside "July 30 2022", so 2022), falling back to the
 * earliest case year. A record with no reference year at all is LEFT ALONE
 * and reported — removing the age without capturing it would destroy the one
 * datum, and anchoring it to the present would corrupt it.
 *
 * The derived year is approximate by construction — event year minus age is
 * off by one for anyone whose birthday falls after the event — which is what
 * year precision already expresses.
 *
 * THE AGE MUST BELONG TO THE SUBJECT. Bios mention other people's ages:
 * "Muhammed Sher Ali Khan, 76, and his sons" appears in the bio of one of
 * the sons. Two guards: a mention preceded by a kinship word (his, her, son,
 * daughter, wife, father...) within 40 characters is refused, and the
 * name-comma patterns must sit against the record's own surname. Refused
 * records are reported untouched, ~22 of them.
 *
 * GRAMMAR SURVIVES THE CUT. "a 32-year-old activist" needs "an activist";
 * "Salah Sarsour, age 53, is" needs "Salah Sarsour is" — but the collapse is
 * applied only at the splice point, because "of Brooklyn, New York, is" is a
 * correct appositive that a global pass was found to mangle.
 *
 * A record whose birthdate is already set gets the text cleaned only; when
 * the implied year disagrees with the stored one by more than two years the
 * conflict is reported, not resolved.
 *
 * Validated the same way as the verdict and charges commands: a separately
 * written Python prototype and this implementation were both run over every
 * live description and agree on every output string and every derived year.
 *
 *   php artisan prisoners:strip-bio-ages
 *   php artisan prisoners:strip-bio-ages --apply
 */
final class StripBioAges extends Command
{
    protected $signature = 'prisoners:strip-bio-ages
        {--apply : Write the cleaned bios and derived birth years}
        {--samples=10 : How many before/after examples to print}';

    protected $description = 'Remove age mentions from bios, filling the birthdate the age implies';

    private const REL = '/\b(his|her|their|son|sons|daughter|daughters|wife|husband|father|mother|brother|sister|grandson|granddaughter|nephew|niece|child|children)\b/iu';

    private const YEAR = '/\b(1[789]\d{2}|20[0-2]\d)\b/u';

    /** Pattern order is semantic: the first kind that matches anywhere wins. */
    private const PATS = [
        'name-comma' => '/^([A-Z][^,\d]{2,40}),\s(\d{2}),\s/u',
        'comma-of' => '/,\s(\d{2}),\s(?=of\b)/u',
        'comma-mid' => '/([A-Za-z]{3,}),\s(\d{2}),\s/u',
        'yearold-art' => '/\b(an?|the)\s(\d{2})-year-old\b/u',
        'yearold' => '/\s?\b(\d{2})-year-old\b/u',
        'aged' => '/,?\s\b(?:aged?)\s(\d{2})\b(?!\s*(?:to|-|–|or)\s*\d)/u',
        'then' => '/,\s*then\s(\d{2})\b,?/u',
    ];

    /**
     * Pure text transform: [status, age, birthYear, newDescription].
     * Status: 'ok' | 'skip-subject' | 'skip-noyear' | 'skip-implausible' | null.
     */
    public static function processText(string $desc, string $lastName, ?int $caseYear): array
    {
        foreach (self::PATS as $kind => $rx) {
            if (! preg_match($rx, $desc, $m, PREG_OFFSET_CAPTURE)) {
                continue;
            }
            $groups = array_values(array_filter(array_slice($m, 1), fn ($g) => ($g[0] ?? '') !== ''));
            $age = (int) end($groups)[0];
            if ($age < 14 || $age > 99) {
                continue;
            }
            $pos = $m[0][1];
            $len = strlen($m[0][0]);

            // -- the age must be the subject's --
            $win = substr($desc, max(0, $pos - 40), min(40, $pos) + $len);
            if (preg_match(self::REL, $win)) {
                return ['skip-subject', null, null, null];
            }
            if ($kind === 'name-comma') {
                if ($lastName === '' || ! str_contains(mb_strtolower($m[1][0]), $lastName)) {
                    return ['skip-subject', null, null, null];
                }
            } elseif ($kind === 'comma-of' || $kind === 'comma-mid') {
                $before = substr($desc, max(0, $pos - 60), min(60, $pos) + 12);
                if ($lastName === '' || ! str_contains(mb_strtolower($before), $lastName)) {
                    return ['skip-subject', null, null, null];
                }
            }

            // -- reference year: first year in the sentence, else the case --
            $s = strrpos(substr($desc, 0, $pos), '. ');
            $s = $s === false ? 0 : $s + 1;
            $e = strpos($desc, '. ', $pos);
            $sentence = substr($desc, $s, ($e === false ? strlen($desc) : $e) - $s);
            $ry = preg_match(self::YEAR, $sentence, $ym) ? (int) $ym[1] : $caseYear;
            if ($ry === null || $ry < 1750 || $ry > 2026) {
                return ['skip-noyear', null, null, null];
            }
            $birth = $ry - $age;
            if ($birth < 1700 || $birth > 2012) {
                return ['skip-implausible', null, null, null];
            }

            // -- removal, per kind --
            $head = substr($desc, 0, $pos);
            $tailAfter = substr($desc, $pos + $len);
            $new = match ($kind) {
                'name-comma' => $head.$m[1][0].', '.$tailAfter,
                'comma-of' => $head.', '.$tailAfter,
                'comma-mid' => $head.$m[1][0].', '.$tailAfter,
                'yearold-art' => (function () use ($head, $tailAfter, $m) {
                    $art = $m[1][0];
                    $rest = ltrim($tailAfter);
                    $a = ($rest !== '' && str_contains('aeiou', mb_strtolower(mb_substr($rest, 0, 1)))) ? 'an' : 'a';
                    $rep = mb_strtolower($art) === 'the' ? $art
                        : (ctype_lower($art[0]) ? $a : ucfirst($a));

                    return $head.$rep.' '.$rest;
                })(),
                'yearold' => $head.$tailAfter,
                'aged' => $head.$tailAfter,
                'then' => $head.(str_ends_with($m[0][0], ',') ? ',' : '').$tailAfter,
            };

            // -- splice-local comma collapse: "Name, is" -> "Name is" --
            $tail = substr($new, $pos);
            if (preg_match('/^(\w*)?,\s+(is|was|were|has|had|faces|faced|pleaded|remains)\b/u', $tail)) {
                $new = substr($new, 0, $pos).preg_replace('/,\s+/u', ' ', $tail, 1);
            }

            $new = preg_replace('/\s{2,}/u', ' ', $new);
            $new = preg_replace('/\s+([,.;])/u', '$1', $new);
            $new = preg_replace('/,\s*,/u', ',', $new);
            $new = ltrim($new);

            return ['ok', $age, $birth, $new];
        }

        return [null, null, null, null];
    }

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $sampleLimit = (int) $this->option('samples');

        $cleaned = 0;
        $filled = 0;
        $shown = 0;
        $skipSubject = [];
        $skipNoYear = [];
        $conflicts = [];

        foreach (Prisoner::withoutGlobalScopes()->with('cases')->cursor() as $p) {
            $desc = (string) $p->description;
            if ($desc === '') {
                continue;
            }

            $ln = mb_strtolower((string) (collect(explode(' ', trim((string) $p->name)))->last() ?? ''));

            $caseYear = null;
            foreach ($p->cases as $case) {
                foreach (['arrest_date', 'incarceration_date', 'sentenced_date'] as $f) {
                    if ($case->{$f}) {
                        $y = (int) Carbon::parse($case->{$f})->year;
                        $caseYear = $caseYear ? min($caseYear, $y) : $y;
                    }
                }
            }

            [$status, $age, $birth, $new] = self::processText($desc, $ln, $caseYear);
            if ($status === null) {
                continue;
            }
            if ($status === 'skip-subject') {
                $skipSubject[] = $p->slug;

                continue;
            }
            if ($status !== 'ok') {
                $skipNoYear[] = $p->slug;

                continue;
            }

            $willFill = ! $p->birthdate;
            if (! $willFill) {
                $stored = (int) Carbon::parse($p->birthdate)->year;
                if (abs($stored - $birth) > 2) {
                    $conflicts[] = [$p->slug, $stored, $birth];
                }
            }

            if ($shown < $sampleLimit) {
                $this->line('  '.$p->slug.'  age '.$age.' -> born c. '.$birth.($willFill ? '' : '  (birthdate already set: text only)'));
                $this->line('    - '.mb_strimwidth($desc, 0, 100, '…'));
                $this->line('    + '.mb_strimwidth($new, 0, 100, '…'));
                $shown++;
            }

            if ($apply) {
                $p->description = $new;
                if ($willFill) {
                    $p->setPartialDate('birthdate', $birth);
                }
                $p->save();
            }

            $cleaned++;
            $willFill && $filled++;
        }

        $this->newLine();
        $this->info(($apply ? 'Cleaned ' : 'Would clean ')."{$cleaned} bio(s); {$filled} empty birthdate(s) filled at year precision.");
        if ($skipSubject) {
            $this->warn(count($skipSubject).' left alone — the age appears to belong to someone else in the bio: '.implode(', ', array_slice($skipSubject, 0, 12)).(count($skipSubject) > 12 ? '…' : ''));
        }
        if ($skipNoYear) {
            $this->warn(count($skipNoYear).' left alone — no reference year to anchor the age to: '.implode(', ', array_slice($skipNoYear, 0, 12)).(count($skipNoYear) > 12 ? '…' : ''));
        }
        if ($conflicts) {
            $this->warn(count($conflicts).' conflict(s) — stored birth year vs the year the bio implies (reported, not resolved):');
            foreach (array_slice($conflicts, 0, 15) as [$slug, $stored, $implied]) {
                $this->line("  {$slug}: stored {$stored}, bio implies c. {$implied}");
            }
        }

        if ($apply) {
            Cache::forget(PrisonerApiController::cacheKey());
            $this->info('Done.');
        } else {
            $this->warn('Dry run — nothing written. Re-run with --apply.');
        }

        return self::SUCCESS;
    }
}
