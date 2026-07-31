<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * READ-ONLY. Finds prisoner records that look like the same person entered
 * twice, by NAME SHAPE rather than by birthdate. Nothing is ever written.
 *
 * This is the companion to prisoners:audit-duplicates, which groups on a
 * shared birthdate. That is a good signal when a birthdate exists, but most
 * of this table has none — Charles Moyer and George Pettibone were each in
 * the database twice and neither pair shared a birthdate, so nothing caught
 * them until somebody looked by hand.
 *
 * Two passes:
 *
 *   1. SAME FIRST AND LAST NAME. Catches "Charles Moyer" against
 *      "Charles H. Moyer", "Dora Lewis" against "Dora Kelly Lewis".
 *
 *   2. SAME NAME TOKENS IN A DIFFERENT ORDER. Catches "Kim Irene" against
 *      "Irene Kim" and "Harrison George" against "George Harrison", which
 *      the first pass cannot see because the first and last names swap.
 *
 * What it refuses to flag, because these are how a duplicate finder produces
 * confident nonsense in a historical database:
 *
 *   - DIFFERENT GENERATIONAL SUFFIXES. Abraham Isaak and Abraham Isaak Jr.
 *     are a father and a son, both anarchists caught up in 1901. So are
 *     Billy Frank Sr. and Billy Frank Jr. Stripping "Jr." merges them.
 *
 *   - CONFLICTING MIDDLE NAMES OR INITIALS. Michael Hill Africa and Michael
 *     Davis Africa are two MOVE defendants. Robert Hillary King and Robert
 *     Edwin King are two men. An initial ABSENT on one record is a name
 *     variant; an initial that DISAGREES is a second person.
 *
 * Corroboration raises the score: a shared arrest, incarceration, release,
 * birth or death year; the same era; the same state; similar biographies; a
 * shared affiliation. Contradiction lowers it: two records that both carry
 * years and share none are pushed down rather than up.
 *
 * Score is a sorting aid, not a verdict. Everything it reports still needs a
 * human to read both records — templated biographies in particular will
 * score high across a whole cohort of people who are not the same person.
 *
 * Usage:
 *   php artisan prisoners:audit-duplicate-names
 *   php artisan prisoners:audit-duplicate-names --min-score=8
 *   php artisan prisoners:audit-duplicate-names --show-ruled-out
 */
final class AuditDuplicateNames extends Command
{
    protected $signature = 'prisoners:audit-duplicate-names
                            {--min-score=5 : Only report pairs scoring at least this}
                            {--show-ruled-out : Also list the pairs the vetoes rejected, with the reason}';

    protected $description = 'Find prisoner records that look like the same person entered twice, by name (read-only)';

    /** Droppable. Generational suffixes are deliberately NOT in here. */
    private const HONORIFICS = [
        'dr', 'rev', 'reverend', 'mr', 'mrs', 'miss', 'ms', 'fr', 'father',
        'sister', 'brother', 'imam', 'sheikh', 'st', 'saint', 'prof',
        'professor', 'gen', 'col', 'capt', 'sgt', 'lt',
    ];

    private const SUFFIXES = ['jr', 'sr', 'ii', 'iii', 'iv', 'v'];

    public function handle(): int
    {
        $people = Prisoner::withoutGlobalScopes()->with('cases')->get();
        $this->info("Scanning {$people->count()} records by name shape…");

        $parsed = [];
        foreach ($people as $p) {
            if ($q = $this->parseName($p->name)) {
                $parsed[$p->id] = $q;
            }
        }

        $byName = [];
        $byTokens = [];

        foreach ($people as $p) {
            if (! isset($parsed[$p->id])) {
                continue;
            }
            $q = $parsed[$p->id];
            $byName[$q['first'].'|'.$q['last']][] = $p;

            $t = array_unique($q['all']);
            sort($t);
            $byTokens[implode('|', $t)][] = $p;
        }

        $seen = [];
        $hits = [];
        $vetoed = [];

        foreach ([$byName, $byTokens] as $index) {
            foreach ($index as $group) {
                if (count($group) < 2) {
                    continue;
                }
                $group = array_values($group);
                foreach ($group as $i => $a) {
                    foreach (array_slice($group, $i + 1) as $b) {
                        $key = $a->id < $b->id ? $a->id.$b->id : $b->id.$a->id;
                        if (isset($seen[$key])) {
                            continue;
                        }
                        $seen[$key] = true;

                        $r = $this->compare($a, $b, $parsed[$a->id], $parsed[$b->id]);

                        if ($r['veto']) {
                            $vetoed[] = [$a, $b, $r];
                        } elseif ($r['score'] >= (int) $this->option('min-score')) {
                            $hits[] = [$a, $b, $r];
                        }
                    }
                }
            }
        }

        usort($hits, fn ($x, $y) => $y[2]['score'] <=> $x[2]['score']);

        $this->newLine();
        $this->line(str_repeat('=', 100));
        $this->info('  LIKELY DUPLICATES: '.count($hits).'   (read both records before merging anything)');
        $this->line(str_repeat('=', 100));

        foreach ($hits as [$a, $b, $r]) {
            $this->newLine();
            $this->line("[{$r['score']}] {$a->name}   ||   {$b->name}");
            foreach ([$a, $b] as $p) {
                $this->line(sprintf(
                    '      %-40s %-9s %d case(s) %s',
                    $p->slug,
                    $p->photo ? 'photo' : 'no photo',
                    $p->cases->count(),
                    $p->description ? '' : ' NO BIO'
                ));
            }
            $this->line('      '.implode('; ', $r['why']));
        }

        $this->newLine();
        $this->line(str_repeat('=', 100));
        $this->info('  RULED OUT BY A VETO: '.count($vetoed));
        $this->line('  Pairs a looser matcher would have merged wrongly — fathers and sons,');
        $this->line('  and people whose middle initials disagree.');
        $this->line(str_repeat('=', 100));

        if ($this->option('show-ruled-out')) {
            foreach ($vetoed as [$a, $b, $r]) {
                $this->line("  {$a->name} / {$b->name}");
                $this->line('      '.implode('; ', $r['veto']));
            }
        } else {
            $this->line('  Re-run with --show-ruled-out to see them and check the reasoning.');
        }

        $this->newLine();
        $this->line('Nothing was written. This command never modifies a record.');

        return self::SUCCESS;
    }

    /** @return array{first:string,last:string,middles:array,suffix:array,all:array}|null */
    private function parseName(?string $name): ?array
    {
        $s = Str::ascii((string) $name);
        $s = (string) preg_replace('/"[^"]*"/', ' ', $s);
        $s = strtolower((string) preg_replace('/[^A-Za-z0-9\s]/', ' ', $s));

        $t = array_values(array_filter(explode(' ', $s)));
        $t = array_values(array_diff($t, self::HONORIFICS));

        $suffix = array_values(array_intersect($t, self::SUFFIXES));
        $t = array_values(array_diff($t, self::SUFFIXES));

        if (! $t) {
            return null;
        }

        return [
            'first' => $t[0],
            'last' => $t[count($t) - 1],
            'middles' => array_slice($t, 1, -1),
            'suffix' => $suffix,
            'all' => $t,
        ];
    }

    /**
     * Compatible as one person? A subset is, and an initial against its own
     * expansion is. Two different letters are not.
     */
    private function middlesRelation(array $m1, array $m2): string
    {
        if (! $m1 || ! $m2) {
            return 'subset';
        }

        [$long, $short] = count($m1) >= count($m2) ? [$m1, $m2] : [$m2, $m1];

        foreach ($short as $s) {
            $hit = null;
            foreach ($long as $i => $l) {
                if ($s === $l
                    || (strlen($s) === 1 && str_starts_with($l, $s))
                    || (strlen($l) === 1 && str_starts_with($s, $l))) {
                    $hit = $i;
                    break;
                }
            }
            if ($hit === null) {
                return 'conflict';
            }
            unset($long[$hit]);
        }

        return 'match';
    }

    private function years(Prisoner $p): array
    {
        $ys = [];
        foreach (['birthdate', 'death_date'] as $f) {
            if ($p->{$f}) {
                $ys[] = $p->{$f}->format('Y');
            }
        }
        foreach ($p->cases as $c) {
            foreach (['arrest_date', 'incarceration_date', 'release_date', 'sentenced_date'] as $f) {
                if ($c->{$f}) {
                    $ys[] = $c->{$f}->format('Y');
                }
            }
        }

        return array_values(array_unique($ys));
    }

    private function compare(Prisoner $a, Prisoner $b, array $qa, array $qb): array
    {
        $score = 0;
        $why = [];
        $veto = [];

        if ($qa['suffix'] && $qb['suffix'] && $qa['suffix'] !== $qb['suffix']) {
            $veto[] = 'different generational suffix — a father and a son, not one person';
        } elseif ($qa['suffix'] !== $qb['suffix']) {
            $score--;
            $why[] = 'one carries Jr./Sr. and the other does not — one person loosely recorded, or two generations';
        }

        $rel = $this->middlesRelation($qa['middles'], $qb['middles']);

        if ($rel === 'conflict') {
            $veto[] = 'middle names or initials disagree ('
                .implode('/', $qa['middles']).' vs '.implode('/', $qb['middles']).')';
        } elseif (! $qa['middles'] && ! $qb['middles']) {
            $score += 4;
            $why[] = 'identical name';
        } elseif ($rel === 'match') {
            $score += 4;
            $why[] = 'same middle name, one abbreviated';
        } else {
            $score += 4;
            $why[] = 'one has a middle name or initial the other lacks';
        }

        if ($qa['all'] !== $qb['all'] && count($qa['all']) === count($qb['all'])) {
            $why[] = 'NAME TOKENS IN A DIFFERENT ORDER';
        }

        $ya = $this->years($a);
        $yb = $this->years($b);
        $shared = array_intersect($ya, $yb);

        if ($shared) {
            $score += 3;
            $why[] = 'shares year(s) '.implode(',', $shared);
        } elseif ($ya && $yb) {
            $score -= 3;
            $why[] = 'NO shared year ('.implode(',', $ya).' vs '.implode(',', $yb).')';
        }

        if ($a->era && $a->era === $b->era) {
            $score++;
            $why[] = 'same era '.$a->era;
        } elseif ($a->era && $b->era) {
            $score -= 2;
            $why[] = "different era ({$a->era} vs {$b->era})";
        }

        if ($a->state && $a->state === $b->state) {
            $score++;
            $why[] = 'same state';
        }

        $ba = strtolower(strip_tags((string) $a->description));
        $bb = strtolower(strip_tags((string) $b->description));

        if ($ba && $bb) {
            similar_text(substr($ba, 0, 1200), substr($bb, 0, 1200), $pct);
            if ($pct > 55) {
                $score += 3;
                $why[] = 'biographies '.round($pct).'% similar';
            } elseif ($pct > 30) {
                $score++;
                $why[] = 'biographies '.round($pct).'% similar';
            }
        }

        if (array_intersect((array) $a->affiliation, (array) $b->affiliation)) {
            $score++;
            $why[] = 'shared affiliation';
        }

        return ['score' => $score, 'why' => $why, 'veto' => $veto];
    }
}
