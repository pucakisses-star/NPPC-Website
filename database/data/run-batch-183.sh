#!/usr/bin/env bash
#
# BATCH 183 -- prisoners with sort_order 0, inserted where they belong.
#
#   EVERY LISTING THAT ORDERS BY sort_order PUTS THEM FIRST. The column
#   defaults to 0, and a record created without an explicit value — which
#   is every record prisoner:add has created — sorts in front of all
#   8,600 others on /database and the API feed, in arbitrary order.
#
#   THE SEQUENCE HAS NO FORMULA. It trends newest-era-first but is
#   interleaved — 463 era runs — and within it records cluster by cohort:
#   the six people arrested together on January 11, 2023 sit on
#   consecutive numbers. It is a curated order. So a zero record is not
#   renumbered by a rule; it is INSERTED next to its peers, and everything
#   after the insertion point shifts up by one, preserving the curated
#   order exactly.
#
#   WHERE "NEXT TO ITS PEERS" COMES FROM, tiered, first match wins:
#
#     cohort-date  same era, same exact arrest date. The six missing
#                  Tougaloo Nine land directly after Meredith Coleman
#                  Anding Jr. — an actual Tougaloo Nine member. Richard
#                  Lee Haley lands after Helen Jean O'Neal McCray, who
#                  was arrested at the same July 19, 1961 picket.
#     cohort-year  same era, arrested the same year — for co-defendants
#                  whose rows carry different dates of one episode.
#                  Helen Gershonowitz is recorded under her February
#                  1931 arrest while her co-defendants Lieb and Harris
#                  are recorded under the March rearrest; a year match
#                  still puts her among the 1931 Paterson cases.
#     era-end      after the era's last record — where that era's most
#                  recent additions already live.
#     global-end   only for a record with no era and no dates.
#
#   HELEN GERSHONOWITZ ALSO GAINS HER ERA. Her record has none, which is
#   half of why she floated: era 1930s is derived from her own case dates
#   (all in 1931) and written, reported. Derivation happens only when
#   every dated case falls in a single decade — nothing is guessed.
#
#   THE LOGIC LIVES IN CODE, NOT IN THIS SCRIPT. App\Support\
#   PrisonerSortOrder does the placement, and prisoner:add now calls it
#   whenever no sort_order is supplied — so this batch fixes the backlog
#   and the command fix stops it recurring. Run this AFTER git pull so
#   the class exists.
#
#   SHIFTS BYPASS MODEL EVENTS on purpose: a sort-order insertion moves
#   thousands of rows, and each moved row must not fire the saving hook
#   that recomputes imprisonment days.
#
#   Idempotent: a second run finds no zeros and changes nothing.
#
# Run from the repo root, after git pull (after batch 182):
#   bash database/data/run-batch-183.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"; shift
    echo; echo "--- ${label}"
    if "$@"; then return 0; fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}"); return 0
}

echo "==================================================================="
echo "  Batch 183 — sort_order for the records stuck at 0"
echo "==================================================================="

place_zeros() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Support\PrisonerSortOrder;
use Illuminate\Support\Facades\DB;

$zeros = Prisoner::withUnderReview()
    ->where(function ($q) { $q->where("sort_order", "<=", 0)->orWhereNull("sort_order"); })
    ->with("cases")
    ->orderBy("name")
    ->get();

if ($zeros->isEmpty()) {
    echo "  No prisoners with sort_order 0 — nothing to place. (Expected on a second run.)\n";

    return;
}

echo "  ", $zeros->count(), " record(s) at sort_order 0, processed alphabetically so\n";
echo "  cohort-mates chain in a stable order:\n\n";

foreach ($zeros as $p) {
    $era = $p->era;
    $derived = false;

    if (! $era) {
        $era = PrisonerSortOrder::deriveEra($p);

        if ($era) {
            DB::table("prisoners")->where("id", $p->id)->update(["era" => $era]);
            $derived = true;
        }
    }

    $placed = PrisonerSortOrder::place($p, $era);

    $before = DB::table("prisoners")->where("sort_order", $placed["sort_order"] - 1)->value("name");
    $after = DB::table("prisoners")->where("sort_order", $placed["sort_order"] + 1)->value("name");

    echo "  ", str_pad($p->name, 30), " -> #", str_pad((string) $placed["sort_order"], 5),
        " [", $placed["method"], "]", ($derived ? "  era derived: ".$era : ""), "\n";
    echo "      between: ", ($before ?: "(start)"), "  |  ", ($after ?: "(end)"), "\n";
}
'
}

verify() {
    php artisan tinker --execute='
use Illuminate\Support\Facades\DB;

$zeros = DB::table("prisoners")->where("sort_order", "<=", 0)->orWhereNull("sort_order")->count();

$dupes = DB::table("prisoners")
    ->select("sort_order", DB::raw("count(*) as n"))
    ->groupBy("sort_order")->having("n", ">", 1)
    ->count();

$total = DB::table("prisoners")->count();
$min = DB::table("prisoners")->min("sort_order");
$max = DB::table("prisoners")->max("sort_order");

echo "  prisoners:              ", $total, "\n";
echo "  still at sort_order 0:  ", $zeros, ($zeros === 0 ? "" : "   !! SHOULD BE ZERO"), "\n";
echo "  duplicate sort_orders:  ", $dupes, ($dupes === 0 ? "" : "   !! SHOULD BE ZERO"), "\n";
echo "  range:                  ", $min, " .. ", $max, "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "place-zeros" place_zeros
run "verify"      verify

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 183 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "Nobody sorts first for being unsorted anymore, and prisoner:add now"
echo "places every new record beside its cohort at creation."
