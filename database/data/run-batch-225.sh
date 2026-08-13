#!/usr/bin/env bash
#
# BATCH 225 -- Dorothy Rose Blumberg was in custody twice.
#
#   TWO SPELLS, NINETEEN MONTHS APART, and her record held one dateless
#   case row. Arrested 8 August 1951 and released on bond that September;
#   incarcerated again on 27 January 1953 and released 14 May 1955.
#
#   TWO ROWS, NOT ONE SPAN. A single row would have to run from August
#   1951 to May 1955 and would claim 1375 days of imprisonment. She was in
#   custody for 861 of them and on bond for the other 514. The prisoner
#   page sums imprisoned_for_days across cases, so two rows give the right
#   total; one row would overstate it by more than a year. This is the
#   distinction batch 210 had to reason about for Roberto Rivera, decided
#   the other way -- he was never at liberty between his two places of
#   confinement, and she was.
#
#   THE 1951 PERIOD GOES ON THE ROW THAT ALREADY EXISTS and the 1953
#   imprisonment becomes the new row, rather than the other way round. The
#   cases relation has no ordering, so the page lists them in insertion
#   order: putting the earlier period on the older row is what makes the
#   page read in chronological order.
#
#   THE SENTENCE TEXT MOVES WITH THE CONVICTION. "Convicted; reversed on
#   later appeal" belongs to the 1953 imprisonment, so it goes to the new
#   row, and the existing row gets a line saying what it now holds.
#   Nothing is lost: both rows are printed in full before and after.
#
#   SEPTEMBER 1951 IS A MONTH, stored as the first with a month precision
#   flag. So the 24 days of the first spell is a floor, the same caveat as
#   Albert Blumberg November release in batch 224.
#
#   Idempotent: the new row is matched on its incarceration date, so a
#   second run finds it and does not create a third case.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-225.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

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
echo "  Batch 225 — Dorothy Rose Blumberg, two periods in custody"
echo "==================================================================="

CASES_CODE='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch225.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withoutGlobalScopes()->where("slug", $payload["prisoner"]["slug"])->first();

if (! $p) { echo "  !! no prisoner at slug ", $payload["prisoner"]["slug"], "\n"; return; }

if ($p->name !== $payload["prisoner"]["expect_name"]) {
    echo "  !! that slug holds ", $p->name, ", not ", $payload["prisoner"]["expect_name"], " — stopping.\n";

    return;
}

$dump = function (Prisoner $p, string $label) {
    echo "  ", $label, "\n";

    foreach ($p->cases()->get() as $i => $c) {
        echo "    [", $i + 1, "] arrest ", str_pad(optional($c->arrest_date)->toDateString() ?: "-", 12),
            " incarc ", str_pad(optional($c->incarceration_date)->toDateString() ?: "-", 12),
            " release ", str_pad(optional($c->release_date)->toDateString() ?: "-", 12),
            " ", str_pad((string) $c->imprisoned_for_days, 5), " days   ",
            ($c->sentence ?: "(no sentence text)"), "\n";
    }
};

$dump($p, "BEFORE");

$e = $payload["existing_case"];
$n = $payload["new_case"];

// The new row is matched on its incarceration date so a second run finds it
// instead of creating a third case.
$new = $p->cases()->get()->first(fn ($c) => optional($c->incarceration_date)->toDateString() === $n["match_on"]);

// The existing row is whichever is not the new one -- on a first run, the
// single dateless row that was already there.
$existing = $p->cases()->get()->first(fn ($c) => ! $new || $c->getKey() !== $new->getKey());

if (! $existing) { echo "\n  !! no case row to work with — stopping.\n"; return; }

echo "\n  writing\n";

$existing->arrest_date = $e["arrest_date"];
$existing->release_date = $e["release_date"];
$existing->sentence = $e["sentence"];
$existing->date_precision = array_merge($existing->date_precision ?? [], [
    "arrest_date" => $e["arrest_precision"],
    "release_date" => $e["release_precision"],
]);
$existing->save();

echo "    existing row -> ", $e["role"], "\n";

if (! $new) {
    $new = new PrisonerCase;
    $new->prisoner_id = $p->getKey();
    echo "    created a second case row\n";
}

$new->incarceration_date = $n["incarceration_date"];
$new->release_date = $n["release_date"];
$new->sentence = $n["sentence"];
$new->date_precision = array_merge($new->date_precision ?? [], [
    "incarceration_date" => $n["incarceration_precision"],
    "release_date" => $n["release_precision"],
]);
$new->save();

echo "    new row      -> ", $n["role"], "\n\n";

$p->refresh();
$p->load("cases");

$dump($p, "AFTER");

$total = $p->cases->sum("imprisoned_for_days");
$count = $p->cases->count();

echo "\n    total imprisonment across both rows: ", $total, " days\n";
echo "    a single row would have claimed 1375 days\n";

echo "\n  ", wordwrap($payload["two_rows_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["sentence_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["sourcing"], 72, "\n  "), "\n";

$ok = $count === (int) $payload["expected"]["cases"]
    && $total === (int) $payload["expected"]["total_days"]
    && (int) $existing->imprisoned_for_days === (int) $e["days"]
    && (int) $new->imprisoned_for_days === (int) $n["days"];

if ($ok) { echo "\nB225-OK\n"; }
'

run_tinker "set-blumberg-two-cases" "B225-OK" "$CASES_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 225 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
