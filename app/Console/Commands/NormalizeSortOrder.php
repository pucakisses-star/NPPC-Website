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
            $this->info("Dry run — {$total} prisoners would be numbered 0..".($total - 1).':');
            foreach ($sorted->take(15) as $i => $p) {
                $aff = ($p->affiliation[0] ?? '-');
                $this->line(sprintf('  %5d  %-8s  %-30s  %s', $i, $p->era ?: '(none)', mb_substr($aff, 0, 30), $p->name));
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
        // Blank eras always sort last, regardless of the reversed era axis.
        $aBlank = empty($a->era);
        $bBlank = empty($b->era);
        if ($aBlank !== $bBlank) {
            return $aBlank ? 1 : -1;
        }

        // Era: NEWEST first. Era strings ("1700s".."2020s") compare
        // chronologically, so reverse the comparison to put 2020s at the top.
        if (! $aBlank) {
            $eraCmp = strcmp((string) $b->era, (string) $a->era);
            if ($eraCmp !== 0) {
                return $eraCmp;
            }
        }

        // Within an era: affiliation grouping, then name — both ascending.
        return $this->withinEraKey($a) <=> $this->withinEraKey($b);
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
