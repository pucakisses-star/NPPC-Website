<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class NormalizePrisonerEras extends Command
{
    protected $signature = 'prisoners:normalize-eras {--dry-run : Show what would change without writing}';
    protected $description = 'Replace non-decade era values (Antebellum, Modern, etc.) and fill in empty eras with the appropriate "{decade}s" string derived from each prisoner\'s case dates.';

    public function handle(): int
    {
        $updated = 0;
        $unchanged = 0;
        $skippedNoData = 0;

        $changes = [];

        foreach (Prisoner::with('cases')->get() as $p) {
            $current = trim((string) $p->era);
            $derived = $this->deriveEra($p);

            // Decide if this needs an update
            $shouldUpdate = false;

            if ($derived === null) {
                // Couldn't derive anything — leave alone
                if ($current === '' || $current === '—') {
                    $skippedNoData++;
                } else {
                    $unchanged++;
                }
                continue;
            }

            if ($current === '' || $current === '—') {
                $shouldUpdate = true; // empty, fill in
            } elseif (! preg_match('/^\d{4}s$/', $current)) {
                $shouldUpdate = true; // non-decade format like "Antebellum", "Modern"
            } elseif ($current !== $derived) {
                // Already a decade — only overwrite if it's clearly wrong
                // (e.g., "2000s" set on a case from 1970s due to a typo).
                // We're conservative: only overwrite if the existing decade
                // doesn't appear in the derived list of plausible decades.
                $plausible = $this->plausibleDecades($p);
                if (! in_array($current, $plausible, true)) {
                    $shouldUpdate = true;
                }
            }

            if ($shouldUpdate) {
                $changes[] = [$p->id, $p->name, $current ?: '(empty)', $derived];
                if (! $this->option('dry-run')) {
                    $p->era = $derived;
                    $p->saveQuietly();
                }
                $updated++;
            } else {
                $unchanged++;
            }
        }

        $this->line('');
        $this->info('=== Era changes ===');
        foreach ($changes as [$id, $name, $from, $to]) {
            $this->line("  [{$id}] {$name}: {$from} -> {$to}");
        }

        $this->line('');
        if ($this->option('dry-run')) {
            $this->warn('Dry run — no changes written.');
        }
        $this->info("Done.");
        $this->line("  updated:                  {$updated}");
        $this->line("  unchanged (already OK):   {$unchanged}");
        $this->line("  skipped (no date data):   {$skippedNoData}");

        return self::SUCCESS;
    }

    /**
     * Derive a "{decade}s" era for the prisoner using, in order:
     *   1. earliest case year (incarceration_date | arrest_date | sentenced_date)
     *   2. birthdate + 30 years (rough adult-activism age)
     * Returns null if no usable date data exists.
     */
    private function deriveEra(Prisoner $p): ?string
    {
        $earliest = null;
        foreach ($p->cases as $case) {
            $dates = [
                $case->incarceration_date,
                $case->arrest_date,
                $case->sentenced_date,
                $case->release_date,
                $case->death_in_custody_date,
            ];
            foreach ($dates as $d) {
                if (! $d) continue;
                $y = (int) $d->format('Y');
                if ($y < 1700 || $y > 2100) continue; // sanity
                if ($earliest === null || $y < $earliest) $earliest = $y;
            }
        }

        if ($earliest === null && $p->birthdate) {
            $earliest = ((int) $p->birthdate->format('Y')) + 30;
        }

        if ($earliest === null) return null;
        return $this->yearToDecade($earliest);
    }

    /**
     * Returns the set of decades that any of the prisoner's known dates
     * fall in. Used to validate an existing decade era.
     */
    private function plausibleDecades(Prisoner $p): array
    {
        $set = [];
        foreach ($p->cases as $case) {
            foreach ([$case->incarceration_date, $case->arrest_date,
                      $case->sentenced_date, $case->release_date,
                      $case->death_in_custody_date] as $d) {
                if (! $d) continue;
                $y = (int) $d->format('Y');
                if ($y >= 1700 && $y <= 2100) $set[$this->yearToDecade($y)] = true;
            }
        }
        if ($p->birthdate) {
            $by = (int) $p->birthdate->format('Y');
            // Span birthdate+18 through birthdate+50 as plausible activism years
            for ($y = $by + 18; $y <= $by + 50; $y += 10) {
                $set[$this->yearToDecade($y)] = true;
            }
        }
        return array_keys($set);
    }

    private function yearToDecade(int $year): string
    {
        return ((int) floor($year / 10) * 10).'s';
    }
}
