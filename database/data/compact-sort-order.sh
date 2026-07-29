#!/usr/bin/env bash
#
# Compact the sort order: renumber every record 1..N with no gaps and no
# collisions, PRESERVING the existing order exactly.
#
# The sequence had drifted -- 8,319 records spread across a maximum sort
# value of 8,370, leaving ~51 unused values (3 and 51 among them, from
# deletions over time), plus the occasional duplicate value. Gaps are
# harmless for display but make every position report ("she is number
# 466") slightly false and every audit noisier.
#
# The renumbering sorts by (sort_order, created_at) -- created_at only
# breaks ties, so two records sharing a value keep their creation order
# -- and assigns 1, 2, 3... in that sequence. NOTHING MOVES RELATIVE TO
# ANYTHING ELSE; the list reads identically before and after.
#
# RECORDS AT SORT 0 ARE LEFT AT 0. Zero means "not yet placed" to
# prisoners:place-zero-sort-by-year, and compaction must not launder
# unplaced records into real positions. Run this AFTER placement (which
# is where run-pending.sh puts it).
#
# Idempotent: a second run reports zero changes. Run from the repo root:
#   bash database/data/compact-sort-order.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use Illuminate\Support\Facades\DB;

$rows = DB::table("prisoners")
    ->whereNotNull("sort_order")
    ->where("sort_order", ">", 0)
    ->orderBy("sort_order")
    ->orderBy("created_at")
    ->get(["id", "sort_order"]);

$changed = 0;
$next = 1;
foreach ($rows as $r) {
    if ((int) $r->sort_order !== $next) {
        DB::table("prisoners")->where("id", $r->id)->update(["sort_order" => $next]);
        $changed++;
    }
    $next++;
}

$total = $rows->count();
$max = DB::table("prisoners")->where("sort_order", ">", 0)->max("sort_order");
$distinct = DB::table("prisoners")->where("sort_order", ">", 0)->distinct()->count("sort_order");
$zeros = DB::table("prisoners")->where(fn ($q) => $q->where("sort_order", 0)->orWhereNull("sort_order"))->count();

echo "Renumbered {$changed} of {$total} positioned record(s).\n";
echo "Max sort is now {$max}, distinct values {$distinct}  (both should equal {$total}).\n";
echo "Records at sort 0 or null, deliberately untouched: {$zeros}  (place them with prisoners:place-zero-sort-by-year --apply)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
