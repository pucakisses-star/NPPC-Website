#!/usr/bin/env bash
#
# Place the eight 1901 Chicago "Free Society" anarchists in the sort order to
# match their period: chained immediately after Abraham Isaak Sr. (their
# cluster-mate, arrested with them September 1901), family members first.
# Everything at or after the insertion point shifts down to make room.
#
# Anchor fallback: abraham-isaak, then hippolyte-havel (also a 1901
# co-arrestee). Aborts with a message if neither is positioned.
#
# Only moves records still at sort_order 0, so it is idempotent — re-running
# after placement does nothing. Run from the repo root:
#   bash database/data/place-1901-anarchists.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$anchor = null;
foreach (["abraham-isaak", "hippolyte-havel"] as $slug) {
    $a = Prisoner::withoutGlobalScopes()->where("slug", $slug)->where("sort_order", "!=", 0)->first();
    if ($a) { $anchor = $a; break; }
}
if (! $anchor) { echo "ABORT: neither abraham-isaak nor hippolyte-havel is positioned.\n"; exit(1); }
echo "anchor: {$anchor->slug} at sort {$anchor->sort_order}\n";

// Family first, then the rest of the Free Society co-arrestees.
$slugs = [
    "marie-isaak", "abraham-isaak-jr", "julia-mechanic", "clemens-pfuetzner",
    "alfred-schneider", "enrico-travaglio", "martin-rasnick", "michael-roz",
];

foreach ($slugs as $slug) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "NOT FOUND: {$slug}\n"; continue; }
    if ($p->sort_order != 0) { echo "{$slug}: already placed at {$p->sort_order}, skipped.\n"; continue; }

    $newSort = $anchor->sort_order + 1;
    Prisoner::withoutGlobalScopes()
        ->where("id", "!=", $p->id)
        ->where("sort_order", ">=", $newSort)
        ->increment("sort_order");
    $p->sort_order = $newSort;
    $p->save();
    echo "{$slug}: placed at {$newSort} (after {$anchor->slug}).\n";
    $anchor = $p;  // chain so the group stays contiguous, in this order
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
