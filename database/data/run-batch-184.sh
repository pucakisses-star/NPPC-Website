#!/usr/bin/env bash
#
# BATCH 184 -- the sort_order placement, corrected for SQLite.
#
#   BATCH 183 CRASHED ON ITS FIRST RECORD AND WROTE NOTHING. The
#   placement class used MySQL date functions — YEAR(), DATE() — because
#   the project docs say the database is MySQL. The production connection
#   is SQLite, which has no YEAR(), so the first cohort-year query threw
#   and the loop died before a single row moved. The class now uses
#   Laravel whereDate/whereYear, which compile per grammar — strftime on
#   SQLite, DATE/YEAR on MySQL — and both shapes were tested against a
#   real SQLite database before this shipped. CLAUDE.md is corrected in
#   the same commit so no future script repeats the mistake.
#
#   183 ALSO SAID "NO FAILURES" WITH AN EXCEPTION ON SCREEN. tinker
#   exits 0 when the code inside it throws, so the runner's failure
#   check never fired. Every step here ends by printing a sentinel that
#   an exception would prevent, and the runner greps for it — a step
#   without its sentinel is a failed step.
#
#   AND THE VERIFY STEP FOUND A PRE-EXISTING DUPLICATE: two records
#   share one sort_order somewhere in the live data (not from 183 —
#   nothing was written). A dedupe step now resolves any such ties by
#   keeping the first record (alphabetically) at the shared value and
#   re-inserting each other one directly after it, so tied records end
#   up adjacent and deterministic rather than in undefined order.
#
#   THE PLACEMENT ITSELF IS UNCHANGED from batch 183's design: zeros are
#   inserted beside their cohorts (same era + arrest date, then same era
#   + arrest year, then era end, then global end), everything after each
#   insertion shifts up one, and the curated order is preserved exactly.
#   prisoner:add places new records at creation from now on.
#
#   Idempotent: a second run finds no zeros and no duplicates and
#   changes nothing.
#
# Run from the repo root, after git pull (after batch 183):
#   bash database/data/run-batch-184.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

# tinker exits 0 even when the code inside throws, so success is detected
# by a sentinel the step prints as its last act — an exception stops it.
run_tinker() {
    local label="$1" sentinel="$2" code="$3" out
    echo; echo "--- ${label}"
    out=$(php artisan tinker --execute="$code" 2>&1) || true
    printf '%s\n' "$out"
    if ! grep -q "$sentinel" <<<"$out"; then
        echo "  !! FAILED: ${label} — sentinel ${sentinel} missing (exception above?)"
        FAILED+=("${label}")
    fi
}

echo "==================================================================="
echo "  Batch 184 — sort_order placement, corrected for SQLite"
echo "==================================================================="

PLACE_CODE='
use App\Models\Prisoner;
use App\Support\PrisonerSortOrder;
use Illuminate\Support\Facades\DB;

$zeros = Prisoner::withUnderReview()
    ->where(function ($q) { $q->where("sort_order", "<=", 0)->orWhereNull("sort_order"); })
    ->with("cases")
    ->orderBy("name")
    ->get();

if ($zeros->isEmpty()) {
    echo "  No prisoners at sort_order 0 — nothing to place. (Expected on a second run.)\n";
} else {
    echo "  ", $zeros->count(), " record(s) at sort_order 0:\n\n";

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
}

echo "B184-PLACE-OK\n";
'

DEDUPE_CODE='
use Illuminate\Support\Facades\DB;

$fixed = 0;

for ($i = 0; $i < 200; $i++) {
    $v = DB::table("prisoners")
        ->select("sort_order")
        ->where("sort_order", ">", 0)
        ->groupBy("sort_order")
        ->havingRaw("count(*) > 1")
        ->orderBy("sort_order")
        ->value("sort_order");

    if ($v === null) { break; }

    // Keep the alphabetically first record at the shared value; re-insert
    // the next one directly after it. Ties resolve adjacent and stable.
    $extra = DB::table("prisoners")
        ->where("sort_order", $v)
        ->orderBy("name")
        ->skip(1)->take(1)
        ->first(["id", "name"]);

    DB::table("prisoners")->where("sort_order", ">", $v)->increment("sort_order");
    DB::table("prisoners")->where("id", $extra->id)->update(["sort_order" => $v + 1]);

    echo "  duplicate at #", $v, ": ", $extra->name, " moved to #", ($v + 1), "\n";
    $fixed++;
}

if ($fixed === 0) { echo "  No duplicate sort_orders. (Expected on a second run.)\n"; }

echo "B184-DEDUPE-OK\n";
'

VERIFY_CODE='
use Illuminate\Support\Facades\DB;

$zeros = DB::table("prisoners")
    ->where(function ($q) { $q->where("sort_order", "<=", 0)->orWhereNull("sort_order"); })
    ->count();

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

if ($zeros === 0 && $dupes === 0) { echo "B184-VERIFY-OK\n"; }
'

run_tinker "place-zeros" "B184-PLACE-OK" "$PLACE_CODE"
run_tinker "dedupe"      "B184-DEDUPE-OK" "$DEDUPE_CODE"
run_tinker "verify"      "B184-VERIFY-OK" "$VERIFY_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 184 applied. All three sentinels present."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
