<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

class ResortPrisonersByEra extends Command
{
    protected $signature = 'prisoners:resort-by-era {--dry-run : Show planned ordering without writing} {--limit= : Print only the first N rows in dry-run output}';
    protected $description = 'Reassign sort_order to every prisoner so the database list is bunched chronologically by era (oldest → newest), then by earliest case year within era, then by name.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit  = $this->option('limit') ? (int) $this->option('limit') : null;

        $prisoners = Prisoner::with('cases')->get();

        $ranked = $prisoners->map(function (Prisoner $p) {
            return [
                'prisoner' => $p,
                'key'      => $this->sortKey($p),
            ];
        })->sortBy('key')->values();

        $this->info("Sorted " . $ranked->count() . " prisoners.");

        if ($dryRun) {
            $shown = $limit ? $ranked->take($limit) : $ranked;
            foreach ($shown as $i => $row) {
                $p = $row['prisoner'];
                $era = $p->era ?: '—';
                $this->line(sprintf("  %4d. [%-10s] %s", ($i + 1) * 10, $era, $p->name));
            }
            $this->warn("Dry run — no changes written.");
            return self::SUCCESS;
        }

        $written = 0;
        foreach ($ranked as $i => $row) {
            $p = $row['prisoner'];
            $newOrder = ($i + 1) * 10;
            if ($p->sort_order !== $newOrder) {
                $p->sort_order = $newOrder;
                $p->saveQuietly();
                $written++;
            }
        }

        $this->info("Done. Updated sort_order on {$written} prisoners.");

        return self::SUCCESS;
    }

    /**
     * Compose a sort key as "EEEE-CCCC-name" so sortBy yields:
     *   primary   = era anchor year (1700s → 1700, 2020s → 2020, "Modern" → 2025, unknown → 9999)
     *   secondary = earliest case year on that prisoner (so within a decade, earlier-incarcerated come first)
     *   tertiary  = lowercased name
     */
    private function sortKey(Prisoner $p): string
    {
        $eraYear  = $this->eraToYear($p->era);
        $caseYear = $this->earliestCaseYear($p);
        return sprintf('%04d-%04d-%s', $eraYear, $caseYear, mb_strtolower($p->name ?? ''));
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

    private function earliestCaseYear(Prisoner $p): int
    {
        $year = 0;
        foreach ($p->cases as $case) {
            foreach (['incarceration_date', 'arrest_date', 'sentenced_date', 'release_date'] as $field) {
                $d = $case->$field ?? null;
                if (! $d) continue;
                $y = (int) $d->format('Y');
                if ($y < 1500 || $y > 2100) continue; // sanity
                if ($year === 0 || $y < $year) $year = $y;
            }
        }
        return $year ?: 9999; // no case dates → end of decade group
    }
}
