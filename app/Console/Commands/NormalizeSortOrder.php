<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Renumbers every prisoner's sort_order into a dense, unique 0..N-1 sequence.
 * Previously ~2,900 rows all shared sort_order 0 (the model default) while the
 * rest had sparse values up to ~32,000.
 *
 * Ordering: reverse-chronological by era (newest first — the most recent era
 * gets sort_order 0, the oldest era the highest number), then related cases
 * grouped together, then name. The sort is:
 *   1. era, NEWEST first ("2020s" ... "1700s"; blank eras still sort last)
 *   2. broad affiliation (first affiliation) — groups a movement together
 *   3. full affiliation set — clusters a specific case within that movement
 *   4. name
 * Only the era axis is reversed; affiliation and name stay ascending within an
 * era. Prisoners with no affiliation sort after the grouped ones within era.
 *
 * Uses a direct bulk update (no model events) and then busts the /api/prisoners
 * cache. Idempotent — same data yields the same numbering.
 */
class NormalizeSortOrder extends Command
{
    protected $signature = 'prisoners:normalize-sort-order {--dry-run : Show the first/last rows without writing}';

    protected $description = 'Renumber every prisoner sort_order to a unique 0..N, ordered by era with related cases grouped';

    public function handle(): int
    {
        $prisoners = Prisoner::withoutGlobalScopes()->get(['id', 'name', 'era', 'affiliation']);

        $sorted = $prisoners
            ->sort(fn ($a, $b) => $this->compare($a, $b))
            ->values();

        $total = $sorted->count();

        if ($this->option('dry-run')) {
            $row = function ($i, $p) {
                $aff = ($p->affiliation[0] ?? '-');
                $this->line(sprintf('  %5d  %-8s  %-30s  %s', $i, $p->era ?: '(none)', mb_substr($aff, 0, 30), $p->name));
            };
            $this->info("Dry run — {$total} prisoners would be numbered 0..".($total - 1).'.');
            $this->info('TOP (newest era):');
            foreach ($sorted->take(15) as $i => $p) {
                $row($i, $p);
            }
            $this->info('BOTTOM (oldest era / blank):');
            $start = max(0, $total - 15);
            foreach ($sorted->slice($start) as $i => $p) {
                $row($i, $p);
            }

            return self::SUCCESS;
        }

        DB::transaction(function () use ($sorted) {
            foreach ($sorted as $i => $p) {
                DB::table('prisoners')->where('id', $p->id)->update(['sort_order' => $i]);
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        $this->info("Renumbered {$total} prisoners: sort_order 0..".($total - 1).', by era with related cases grouped. API cache cleared.');

        return self::SUCCESS;
    }

    private function compare(Prisoner $a, Prisoner $b): int
    {
        // Era: NEWEST first, ranked by decade NUMBER (not string). This keeps
        // any non-decade era value ("Modern", "Early 1900s", a stray text era)
        // from sorting above the numeric decades — such values, and blanks,
        // rank lowest and land at the bottom rather than the top.
        $ra = $this->eraRank($a->era);
        $rb = $this->eraRank($b->era);
        if ($ra !== $rb) {
            return $rb <=> $ra; // higher rank (newer) first
        }

        // Within an era: affiliation grouping, then name — both ascending.
        return $this->withinEraKey($a) <=> $this->withinEraKey($b);
    }

    /**
     * A sortable decade rank; higher = newer. "1910s" -> 1910, "2020s" -> 2020,
     * "20th century" -> 1950. Blank sorts last (-2); a named era with no year
     * sorts just above blank (-1) — both stay at the bottom, never the top.
     */
    private function eraRank(?string $era): int
    {
        $era = trim((string) $era);
        if ($era === '') {
            return -2;
        }
        if (preg_match('/(1[6-9]\d{2}|20\d{2})/', $era, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/(\d{1,2})\s*(?:st|nd|rd|th)\s*century/i', $era, $m)) {
            return ((int) $m[1] - 1) * 100 + 50;
        }

        return -1;
    }

    /** @return array{0:string,1:string,2:string} */
    private function withinEraKey(Prisoner $p): array
    {
        $aff = $p->affiliation;
        $aff = is_array($aff) ? array_values(array_filter($aff)) : ($aff ? [$aff] : []);
        $firstAff = $aff ? mb_strtolower($aff[0]) : "\xff\xff"; // no affiliation sorts last within era
        $fullAff = mb_strtolower(implode('|', $aff));

        return [$firstAff, $fullAff, mb_strtolower((string) $p->name)];
    }
}
