<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Audit the prisoner database for likely duplicate entries by
 * grouping on shared birthdate. Same birthdate alone is a weak
 * signal (collisions are common), so each pair is also scored on
 * name similarity:
 *
 *   - SAME last_name OR Levenshtein(name) ≤ 3   → HIGH    (likely dupe)
 *   - Levenshtein(name) ≤ 6                     → MEDIUM  (review)
 *   - Otherwise                                 → LOW     (probable coincidence)
 *
 * Report-only. No writes.
 */
final class AuditDuplicatePrisoners extends Command {
    protected $signature = 'prisoners:audit-duplicates {--min-severity=medium : low|medium|high}';
    protected $description = 'Flag prisoners sharing a birthdate that may be duplicate records.';

    public function handle(): int {
        $minSeverity = strtolower((string) $this->option('min-severity'));
        $rank = ['low' => 0, 'medium' => 1, 'high' => 2];
        $threshold = $rank[$minSeverity] ?? 1;

        $prisoners = Prisoner::query()
            ->whereNotNull('birthdate')
            ->orderBy('birthdate')
            ->get(['id', 'name', 'first_name', 'last_name', 'aka', 'birthdate', 'slug', 'state']);

        $byDate = $prisoners->groupBy(fn ($p) => $p->birthdate?->format('Y-m-d'));

        $findings = [];
        $totalPairs = 0;

        foreach ($byDate as $date => $group) {
            if ($group->count() < 2) {
                continue;
            }

            $rows = $group->values();
            for ($i = 0; $i < $rows->count(); $i++) {
                for ($j = $i + 1; $j < $rows->count(); $j++) {
                    $a = $rows[$i];
                    $b = $rows[$j];
                    $totalPairs++;

                    $severity = $this->scorePair($a, $b);
                    if ($rank[$severity] < $threshold) {
                        continue;
                    }
                    $findings[] = compact('date', 'a', 'b', 'severity');
                }
            }
        }

        $this->info('Scanned '.$prisoners->count().' prisoners with a birthdate; '
            .$byDate->filter(fn ($g) => $g->count() > 1)->count().' shared-birthdate clusters; '
            .$totalPairs.' candidate pairs.');
        $this->line('');

        if (empty($findings)) {
            $this->info('No suspicious pairs at or above severity: '.$minSeverity);
            return self::SUCCESS;
        }

        usort($findings, fn ($x, $y) => $rank[$y['severity']] <=> $rank[$x['severity']]);

        $bySeverity = ['high' => 0, 'medium' => 0, 'low' => 0];
        foreach ($findings as $f) {
            $bySeverity[$f['severity']]++;
            $tag = strtoupper($f['severity']);
            $this->line("[{$tag}]  {$f['date']}");
            $this->line('  A  #'.$f['a']->id.'  /prisoner/'.$f['a']->slug.'  — '.$f['a']->name
                .($f['a']->state ? '  ('.$f['a']->state.')' : '')
                .($f['a']->aka ? '  aka: '.$f['a']->aka : ''));
            $this->line('  B  #'.$f['b']->id.'  /prisoner/'.$f['b']->slug.'  — '.$f['b']->name
                .($f['b']->state ? '  ('.$f['b']->state.')' : '')
                .($f['b']->aka ? '  aka: '.$f['b']->aka : ''));
            $this->line('');
        }

        $this->line('— SUMMARY —');
        foreach (['high', 'medium', 'low'] as $sev) {
            if ($bySeverity[$sev] > 0) {
                $this->info(str_pad((string) $bySeverity[$sev], 4, ' ', STR_PAD_LEFT).'  '.strtoupper($sev));
            }
        }

        return self::SUCCESS;
    }

    private function scorePair(Prisoner $a, Prisoner $b): string {
        $aName = $this->normalize($a->name);
        $bName = $this->normalize($b->name);
        $aLast = $this->normalize((string) $a->last_name);
        $bLast = $this->normalize((string) $b->last_name);

        if ($aLast !== '' && $aLast === $bLast) {
            return 'high';
        }

        $dist = levenshtein($aName, $bName);
        if ($dist <= 3) {
            return 'high';
        }
        if ($dist <= 6) {
            return 'medium';
        }

        // Also check aka overlap
        $aAka = $this->normalize((string) $a->aka);
        $bAka = $this->normalize((string) $b->aka);
        if ($aAka !== '' && ($aAka === $bName || $aAka === $bLast)) {
            return 'high';
        }
        if ($bAka !== '' && ($bAka === $aName || $bAka === $aLast)) {
            return 'high';
        }

        return 'low';
    }

    private function normalize(string $s): string {
        $s = mb_strtolower($s);
        $s = preg_replace('/[^a-z0-9 ]/', '', $s);
        $s = preg_replace('/\s+/', ' ', $s);

        return trim((string) $s);
    }
}
