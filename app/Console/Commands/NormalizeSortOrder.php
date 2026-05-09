<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Walk every prisoner whose sort_order is 0 (the schema default —
 * accumulates whenever a row is created without an explicit value)
 * and reassign them to the bottom of the list, one after another in
 * created_at order.
 *
 * Use this once after bulk-imports that didn't set sort_order. The
 * Prisoner model now auto-assigns sort_order = MAX(sort_order) + 1
 * on create, so future rows shouldn't accumulate 0s.
 *
 * Pass --by-name to break ties alphabetically instead of by created_at.
 */
class NormalizeSortOrder extends Command
{
    protected $signature   = 'prisoners:normalize-sort-order {--dry-run : Print plan without writing} {--by-name : Order zero-sort_order rows alphabetically rather than by created_at}';
    protected $description = 'Reassign sort_order on every prisoner whose value is 0, appending them to the bottom of the list.';

    public function handle(): int
    {
        $dryRun  = (bool) $this->option('dry-run');
        $byName  = (bool) $this->option('by-name');

        $startAt = (int) Prisoner::query()->where('sort_order', '!=', 0)->max('sort_order') + 1;

        $query = Prisoner::query()->where('sort_order', 0);
        $query = $byName ? $query->orderBy('name') : $query->orderBy('created_at');
        $rows  = $query->get(['id', 'name', 'sort_order', 'created_at']);

        $this->info("Zero-sort_order prisoners: {$rows->count()}");
        $this->info("Highest non-zero sort_order: " . max($startAt - 1, 0) . "; appending starting at {$startAt}.");

        if ($rows->isEmpty()) {
            $this->info("Nothing to do.");
            return self::SUCCESS;
        }

        $written = 0;
        foreach ($rows as $i => $p) {
            $newOrder = $startAt + $i;
            if ($dryRun) {
                $this->line(sprintf("  %5d  %s", $newOrder, $p->name));
                continue;
            }
            DB::table('prisoners')->where('id', $p->id)->update(['sort_order' => $newOrder]);
            $written++;
        }

        $this->info($dryRun ? "Dry run — no changes written." : "Done. Updated {$written} prisoners.");
        return self::SUCCESS;
    }
}
