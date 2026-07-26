#!/usr/bin/env bash
#
# One-time global resequence: renumber every prisoner 1..N by current list
# order (sort_order asc, name as tiebreaker) so all sort_order values are
# unique and equal to the record's position in the admin list. After this,
# drag-and-drop in the admin panel keeps the numbering exact (the
# ListPrisoners reorder override renumbers globally on every drag).
#
# IMPORTANT: run the zero-sort PLACEMENT scripts FIRST —
#   bash database/data/remove-duplicate-colonial-records.sh
#   php artisan prisoners:place-zero-sort
#   php artisan prisoners:auto-place-zero-sort
#   bash database/data/place-1901-anarchists.sh
# — because those detect unplaced records by sort_order = 0, and this
# resequence assigns every record (zeros included, at the top of the list)
# a real position, after which the placement scripts become no-ops.
#
# Idempotent. Run from the repo root:
#   bash database/data/resequence-sort-order.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\DB;

$zeros = Prisoner::withoutGlobalScopes()->where("sort_order", 0)->count();
if ($zeros > 0) {
    echo "WARNING: {$zeros} record(s) still at sort_order 0 — they will be numbered at the TOP of the list. Run the placement scripts first if that is not intended.\n";
}

$ids = Prisoner::withoutGlobalScopes()
    ->orderBy("sort_order")
    ->orderBy("name")
    ->pluck("sort_order", "id");

$changed = 0;
DB::transaction(function () use ($ids, &$changed) {
    $position = 0;
    foreach ($ids as $id => $sort) {
        $position++;
        if ((int) $sort !== $position) {
            Prisoner::withoutGlobalScopes()->whereKey($id)->update(["sort_order" => $position]);
            $changed++;
        }
    }
});

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Resequenced ".count($ids)." prisoners 1..".count($ids).", {$changed} updated.\n";
echo "Done.\n";
'

echo
echo "Done."
