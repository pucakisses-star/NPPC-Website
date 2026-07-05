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
 * Ordering: chronological by era, then related cases grouped together, then
 * name. Concretely the sort key is:
 *   1. era ("1700s" ... "2020s"; blank eras sort last)
 *   2. broad affiliation (first affiliation) — groups a movement together
 *   3. full affiliation set — clusters a specific case within that movement
 *   4. name
 * Prisoners with no affiliation sort after the grouped ones within their era.
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
            ->sort(fn ($a, $b) => $this->sortKey($a) <=> $this->sortKey($b))
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

    /** @return array{0:string,1:string,2:string,3:string} */
    private function sortKey(Prisoner $p): array
    {
        $era = $p->era ?: '9999z';                       // blank era sorts last

        $aff = $p->affiliation;
        $aff = is_array($aff) ? array_values(array_filter($aff)) : ($aff ? [$aff] : []);
        $firstAff = $aff ? mb_strtolower($aff[0]) : "\xff\xff"; // no affiliation sorts last within era
        $fullAff = mb_strtolower(implode('|', $aff));

        return [$era, $firstAff, $fullAff, mb_strtolower((string) $p->name)];
    }
}
