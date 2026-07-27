#!/usr/bin/env bash
#
# Reorder every prisoner tagged with an 1700s era so the block reads newest
# first: the most recent cases get the lowest sort_order (seen first) and the
# oldest cases sink to the bottom of the block.
#
# The 1700s records are rearranged WITHIN THE SLOTS THEY ALREADY OCCUPY -- the
# script collects their current sort_order values and reassigns those same
# numbers in the new order, so no other prisoner in the database moves and no
# renumbering ripples outward.
#
# Chronology key: the earliest dated event across a prisoner cases
# (incarceration_date, arrest_date, sentenced_date, in_exile_since), falling
# back to a 4-digit year in the era string. Undated records sort to the bottom
# of the block (oldest end); records sharing a year are shuffled randomly, not
# alphabetised, so each preview reshuffles same-year cohorts.
#
# Records still at sort_order 0 are NOT touched -- run the placement commands
# first (prisoners:place-zero-sort / prisoners:auto-place-zero-sort).
#
# Preview by default; set APPLY=1 to write:
#   bash database/data/reorder-1700s-chronologically.sh
#   APPLY=1 bash database/data/reorder-1700s-chronologically.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

APPLY="${APPLY:-0}" php artisan tinker --execute='
use App\Models\Prisoner;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

$apply = getenv("APPLY") === "1";

$people = Prisoner::withoutGlobalScopes()
    ->where("era", "like", "%1700%")
    ->with("cases")
    ->get();

$unplaced = $people->where("sort_order", 0);
$placed = $people->where("sort_order", "!=", 0);

echo "1700s-era prisoners: ".$people->count()." (".$placed->count()." placed, ".$unplaced->count()." still at sort 0)\n\n";

if ($unplaced->isNotEmpty()) {
    echo "Skipped (sort_order 0 -- run the placement commands first):\n";
    foreach ($unplaced as $u) { echo "  ".$u->slug."\n"; }
    echo "\n";
}
if ($placed->isEmpty()) { echo "Nothing to reorder.\n"; exit(0); }

$yearOf = function (Prisoner $p) {
    $year = null;
    foreach ($p->cases as $c) {
        foreach (["incarceration_date", "arrest_date", "sentenced_date", "in_exile_since"] as $f) {
            if ($c->{$f}) {
                $y = (int) Carbon::parse($c->{$f})->year;
                if ($y > 1000) { $year = $year ? min($year, $y) : $y; }
            }
        }
    }
    if (! $year && $p->era && preg_match("/\\d{4}/", $p->era, $m)) { $year = (int) $m[0]; }

    return $year;
};

// Slots these records already occupy, ascending.
$slots = $placed->pluck("sort_order")->map(fn ($s) => (int) $s)->sort()->values()->all();

// Newest first: descending year, undated last, name as tiebreaker.
// Newest first. Records sharing a year (and the undated group) are shuffled
// randomly rather than alphabetised, so same-year cohorts do not read as an
// A-Z list. Chronology always wins over the shuffle.
$buckets = [];
foreach ($placed as $p) { $buckets[$yearOf($p) ?? 0][] = $p; }
krsort($buckets);            // newest year first; undated (key 0) lands last
$ordered = collect();
foreach ($buckets as $bucket) {
    shuffle($bucket);
    foreach ($bucket as $p) { $ordered->push($p); }
}

echo "Planned order (top of list first):\n";
$moves = [];
foreach ($ordered as $i => $p) {
    $newSort = $slots[$i];
    $y = $yearOf($p);
    $flag = ((int) $p->sort_order === $newSort) ? "  " : "->";
    echo sprintf("  %s %-5s %5d (was %5d)  %s\n", $flag, $y ?: "----", $newSort, $p->sort_order, $p->slug);
    if ((int) $p->sort_order !== $newSort) { $moves[] = [$p->id, $newSort]; }
}

echo "\n".count($moves)." record(s) change position.\n";

if (! $apply) {
    echo "\nPREVIEW ONLY -- nothing written. Re-run with APPLY=1 to apply:\n";
    echo "  APPLY=1 bash database/data/reorder-1700s-chronologically.sh\n";
    exit(0);
}

DB::transaction(function () use ($moves) {
    foreach ($moves as [$id, $newSort]) {
        Prisoner::withoutGlobalScopes()->whereKey($id)->update(["sort_order" => $newSort]);
    }
});

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Applied. Done.\n";
'
