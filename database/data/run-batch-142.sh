#!/usr/bin/env bash
#
# BATCH 142 -- merge institution records that are the same place under
# different spellings. Reported with batches 139, 140 and 141; acted on
# here.
#
#   Twenty-eight merges, absorbing forty-three duplicate records and
#   repointing 188 case rows. The largest:
#
#     Leavenworth   7 records, 146 case rows
#     Atlanta       5 records,  32 case rows
#     McNeil Island 4 records
#     Lewisburg     4 records
#     Sing Sing     3 records
#     Clinton       3 records
#     Los Angeles MDC 3 records
#
#   Leavenworth is the one that shows what the fragmentation costs:
#   Richard Brazier, Myron Sprague and George O'Connell were in the same
#   prison in the same years and did not group together, because Brazier
#   pointed at one record and the other two at another.
#
#   MECHANICS. Only prisoner_cases.institution_id refers to
#   institutions, so a merge is a repoint of that column followed by
#   deleting the emptied record. A survivor missing a city or state
#   takes it from the payload. Addresses are never copied: batch 140
#   cleared the wrong ones, and carrying one over here would undo that.
#
#   WHAT IS NOT MERGED, and why it matters: Georgia Penitentiary at
#   Milledgeville and Georgia State Prison at Reidsville are different
#   prisons; so are the three Ohio records. San Quentin State Prison
#   and San Quentin Rehabilitation Center are one site under two
#   official names, which is a curatorial choice rather than a repair.
#   The compound records — "Occoquan Workhouse / DC Jail", "Alcatraz /
#   federal prisons" — name two places at once and merging them would
#   assert a prisoner was at one of them. All listed in the payload
#   with reasons, and printed by this script.
#
#   SAFETY. Nothing is merged by pattern. Every survivor and every
#   absorbed name is written out in full in the payload, and a name
#   that does not resolve to exactly one institution is reported and
#   skipped rather than guessed at. The script prints the case-row count
#   of every record before and after, and the totals must balance: no
#   case row may be lost or gained.
#
#   Idempotent: a second run finds the absorbed records gone and says so.
#
# Run from the repo root, after git pull (after batch 141):
#   bash database/data/run-batch-142.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"
    shift
    echo
    echo "--- ${label}"
    if "$@"; then
        return 0
    fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}")
    return 0
}

echo "==================================================================="
echo "  Batch 142 — merge duplicate institution records"
echo "==================================================================="

apply_batch() {
    php artisan tinker --execute='
use App\Models\Institution;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch142.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$rowsFor = fn (Institution $i) => PrisonerCase::where("institution_id", $i->id)->count();

$totalBefore = PrisonerCase::whereNotNull("institution_id")->count();
$institutionsBefore = Institution::count();

echo "before: ", $institutionsBefore, " institutions, ",
    $totalBefore, " case rows carrying one\n";

$merged = 0;
$absorbed = 0;
$moved = 0;
$problems = 0;

foreach ($payload["merges"] as $m) {
    echo "\n", str_repeat("-", 67), "\n";

    $survivors = Institution::where("name", $m["survivor"])->get();

    if ($survivors->count() !== 1) {
        echo "  SKIP ", $m["survivor"], " — resolves to ", $survivors->count(), " records\n";
        $problems++;

        continue;
    }

    $survivor = $survivors->first();
    $startRows = $rowsFor($survivor);

    echo "  ", $survivor->name, "   [", $startRows, " rows]\n";

    $took = 0;
    $any = false;

    foreach ($m["absorb"] as $name) {
        $losers = Institution::where("name", $name)->get();

        if ($losers->isEmpty()) { echo "      (gone) ", $name, "\n"; continue; }

        if ($losers->count() > 1) {
            echo "      SKIP ", $name, " — resolves to ", $losers->count(), " records\n";
            $problems++;

            continue;
        }

        $loser = $losers->first();

        if ($loser->id === $survivor->id) { continue; }

        $n = PrisonerCase::where("institution_id", $loser->id)
            ->update(["institution_id" => $survivor->id]);

        $loser->delete();

        $took += $n;
        $absorbed++;
        $any = true;

        echo "      absorbed ", str_pad($name, 48), " ", $n, " rows\n";
    }

    if (! $any) { echo "      nothing to absorb — already merged\n"; continue; }

    // City and state only, never an address: batch 140 cleared the wrong
    // addresses and reintroducing one here would undo that work.
    if (! $survivor->city && ! empty($m["city"])) { $survivor->city = $m["city"]; }
    if (! $survivor->state && ! empty($m["state"])) { $survivor->state = $m["state"]; }
    $survivor->save();

    $endRows = $rowsFor($survivor);

    echo "      now ", $endRows, " rows  (", $startRows, " + ", $took, ")";
    echo $endRows === $startRows + $took ? "  balanced\n" : "  !! DOES NOT BALANCE\n";

    if ($endRows !== $startRows + $took) { $problems++; }

    $merged++;
    $moved += $took;

    echo "      ", wordwrap($m["reason"], 78, "\n      "), "\n";
}

// ------------------------------------------------------------- not merged
echo "\n", str_repeat("=", 67), "\nDELIBERATELY NOT MERGED\n";

foreach ($payload["not_merged"] as $n) {
    echo "\n  ", $n["group"], "\n  ", wordwrap($n["reason"], 84, "\n  "), "\n";
}

// ---------------------------------------------------------------- balance
$totalAfter = PrisonerCase::whereNotNull("institution_id")->count();
$institutionsAfter = Institution::count();

echo "\n", str_repeat("=", 67), "\n";
echo "  merges applied:        ", $merged, "\n";
echo "  records absorbed:      ", $absorbed, "\n";
echo "  case rows repointed:   ", $moved, "\n";
echo "  problems:              ", $problems, "\n";
echo "\n  institutions: ", $institutionsBefore, " -> ", $institutionsAfter,
    "  (", ($institutionsBefore - $institutionsAfter), " fewer)\n";
echo "  case rows carrying an institution: ", $totalBefore, " -> ", $totalAfter, "\n";

if ($totalBefore === $totalAfter) {
    echo "  BALANCED — no case row lost or gained.\n";
} else {
    echo "  !! NOT BALANCED — ", abs($totalAfter - $totalBefore), " case rows differ. Investigate.\n";
}

// Anything left that still looks doubled up.
echo "\nRemaining institutions whose names differ only by punctuation or case:\n";

$all = Institution::orderBy("name")->get();
$seen = [];

foreach ($all as $i) {
    $key = preg_replace("/[^a-z0-9]/", "", mb_strtolower((string) $i->name));

    if ($key === "") { continue; }

    $seen[$key][] = $i->name;
}

$dupes = array_filter($seen, fn ($v) => count($v) > 1);

if (! $dupes) { echo "  none.\n"; }

foreach ($dupes as $v) { echo "  ", implode("  |  ", $v), "\n"; }

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "merge-institutions" apply_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 142 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
echo
echo "The case-row total must be identical before and after. If the run"
echo "above says NOT BALANCED, something was deleted that still had rows"
echo "on it, and the batch should be investigated before deploying more."
