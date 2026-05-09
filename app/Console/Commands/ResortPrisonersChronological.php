<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Reassigns sort_order on every prisoner so the database list is in
 * strict chronological order by their earliest case-related date,
 * newest → oldest by default. (The existing prisoners:resort-by-era
 * command groups by decade-era first; this one ignores eras and uses
 * actual case dates so the ordering is consistent within and across
 * decades.)
 *
 * Order direction defaults to newest-first to match the public-facing
 * display preference. Pass --direction=oldest for oldest-first.
 *
 * Anchor year for each prisoner, in priority order:
 *   1. earliest of (arrest_date, incarceration_date, sentenced_date,
 *      release_date) across all attached cases
 *   2. birthdate + 25 years (rough adult-life estimate)
 *   3. era anchor year (1980s → 1980, "civil war" → 1860, etc.)
 *   4. unknown → sorts last regardless of direction
 */
class ResortPrisonersChronological extends Command
{
    protected $signature   = 'prisoners:resort-chronological {--direction=newest : newest|oldest first} {--dry-run : Print planned order without writing} {--limit= : Limit dry-run output to N rows}';
    protected $description = 'Reassign sort_order to every prisoner in strict chronological order by earliest case date (newest-first by default).';

    public function handle(): int
    {
        $dryRun    = (bool) $this->option('dry-run');
        $limit     = $this->option('limit') ? (int) $this->option('limit') : null;
        $direction = strtolower((string) ($this->option('direction') ?? 'newest'));
        if (! in_array($direction, ['newest', 'oldest'], true)) {
            $this->error("--direction must be 'newest' or 'oldest'.");
            return self::FAILURE;
        }

        $prisoners = Prisoner::with('cases')->get();

        $ranked = $prisoners->map(function (Prisoner $p) use ($direction) {
            return [
                'prisoner' => $p,
                'year'     => $this->anchorYear($p),
                'key'      => $this->sortKey($p, $direction),
            ];
        })->sortBy('key')->values();

        $this->info("Sorted " . $ranked->count() . " prisoners (direction: {$direction}).");

        if ($dryRun) {
            $shown = $limit ? $ranked->take($limit) : $ranked;
            foreach ($shown as $i => $row) {
                $p    = $row['prisoner'];
                $year = $row['year'] === 9999 ? '----' : (string) $row['year'];
                $this->line(sprintf("  %4d. %s  %s", $i, $year, $p->name));
            }
            $this->warn("Dry run — no changes written.");
            return self::SUCCESS;
        }

        // Two-pass write to avoid clashing with any existing unique
        // index on sort_order while we shuffle values around.
        $written = 0;
        \Illuminate\Support\Facades\DB::transaction(function () use ($ranked, &$written) {
            $offset = 1_000_000_000;
            foreach ($ranked as $i => $row) {
                \Illuminate\Support\Facades\DB::table('prisoners')
                    ->where('id', $row['prisoner']->id)
                    ->update(['sort_order' => $offset + $i]);
            }
            foreach ($ranked as $i => $row) {
                \Illuminate\Support\Facades\DB::table('prisoners')
                    ->where('id', $row['prisoner']->id)
                    ->update(['sort_order' => $i]);
                $written++;
            }
        });

        $this->info("Done. Updated sort_order on {$written} prisoners (0.." . ($written - 1) . ").");
        return self::SUCCESS;
    }

    private function sortKey(Prisoner $p, string $direction): string
    {
        $year = $this->anchorYear($p);
        if ($year === 9999) {
            $primary = 9999; // unknown always last
        } elseif ($direction === 'newest') {
            $primary = 9999 - $year;
        } else {
            $primary = $year;
        }
        return sprintf('%04d-%s', $primary, mb_strtolower($p->name ?? ''));
    }

    private function anchorYear(Prisoner $p): int
    {
        // 1. earliest case-related date
        $caseYear = 0;
        foreach ($p->cases as $case) {
            foreach (['incarceration_date', 'arrest_date', 'sentenced_date', 'release_date'] as $field) {
                $d = $case->$field ?? null;
                if (! $d) continue;
                $y = (int) $d->format('Y');
                if ($y < 1500 || $y > 2100) continue;
                if ($caseYear === 0 || $y < $caseYear) $caseYear = $y;
            }
        }
        if ($caseYear) return $caseYear;

        // 2. birthdate + 25 (rough adult-life anchor)
        if ($p->birthdate) {
            $by = (int) $p->birthdate->format('Y');
            if ($by >= 1500 && $by <= 2100) return $by + 25;
        }

        // 3. era anchor
        return $this->eraToYear($p->era);
    }

    private function eraToYear(?string $era): int
    {
        if (! $era) return 9999;
        $era = trim($era);
        if (preg_match('/^(\d{4})s$/', $era, $m)) return (int) $m[1];

        return match (mb_strtolower($era)) {
            'pre-colonial', 'pre-1700s' => 1500,
            'colonial', '1600s'         => 1600,
            'revolutionary', '1700s'    => 1700,
            'antebellum'                => 1830,
            'civil war'                 => 1860,
            'reconstruction'            => 1870,
            'gilded age'                => 1880,
            'progressive era'           => 1900,
            'modern', 'contemporary'    => 2025,
            default                     => 9999,
        };
    }
}
