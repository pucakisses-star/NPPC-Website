#!/usr/bin/env bash
#
# BATCH 130 -- Josephine Sunshine Overaker: turn the exile counter on,
# per the curator.
#
#   in_exile_since is set to 2001 at YEAR precision, on the strength
#   of federal law enforcement quoted in April 2022: she is believed
#   to have fled to Europe in late 2001. Batches 128 and 129 recorded
#   that in the biography but left the field empty; the curator has
#   asked for the running counter, so it goes in.
#
#   ONE THING TO KNOW ABOUT THE NUMBER. Year precision stores the
#   date as 2001-01-01, which is the partial-date convention (missing
#   parts default to 01), and the counter measures from whatever is
#   stored. The source says LATE 2001, so the counter runs from
#   January 1 and reads up to about ten months longer than the exile
#   actually is — roughly 25 years 7 months today against a true
#   figure nearer 24 years 8 months. The case card shows "In exile
#   since: 2001", which is the honest statement; the counter is the
#   part that cannot express the imprecision, because there is no
#   in_exile_for_months counterpart to imprisoned_for_months. If a
#   month for her departure ever turns up, set it and both become
#   right.
#
#   currently_in_exile is ensured here too, so the counter cannot
#   silently fail to render if batch 128 has not been applied — the
#   model only counts an open-ended exile for somebody carrying that
#   flag.
#
#   Idempotent.
#
# Run from the repo root, after git pull (after batch 129):
#   bash database/data/run-batch-130.sh

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
echo "  Batch 130 — Overaker: exile counter on, in exile since 2001"
echo "==================================================================="

set_exile_start() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Support\ExileDuration;
use App\Support\ImprisonmentDuration;

$p = Prisoner::withUnderReview()->where("slug", "josephine-sunshine-overaker")->with("cases")->first();

if (! $p) { echo "josephine-sunshine-overaker NOT FOUND — nothing changed.\n"; return; }

echo "record: ", $p->name, "  [", $p->slug, "]\n";

$notes = [];

// The model only counts an open-ended exile for somebody flagged as
// currently in exile, so ensure it rather than depend on batch 128 order.
if (! $p->currently_in_exile) {
    $p->currently_in_exile = true;
    $notes[] = "currently_in_exile=true (batch 128 apparently not applied)";
}
if (! $p->in_exile) { $p->in_exile = true; $notes[] = "in_exile=true"; }

if ($notes) { $p->save(); }

$case = $p->cases->first();

if (! $case) { echo "  case: NONE — no row to carry the exile start; nothing changed.\n"; return; }

$was = $case->in_exile_since ? $case->in_exile_since->format("Y-m-d") : null;
$wasPrec = $case->datePrecisionFor("in_exile_since");

if ($was !== "2001-01-01" || $wasPrec !== "year") {
    $case->setPartialDate("in_exile_since", 2001);
    $case->save();
    $notes[] = "in_exile_since=2001 [year]".($was ? " (was ".$was." [".$wasPrec."])" : "");
} else {
    $case->save();   // recompute in_exile_for_days through the model hook
}

$p->refresh()->load("cases");
$case->refresh();

echo "  ", implode("\n  ", $notes ?: ["already correct"]), "\n";
echo "  flags:  in_exile=", var_export((bool) $p->in_exile, true),
    "  currently_in_exile=", var_export((bool) $p->currently_in_exile, true),
    "  imprisoned_or_exiled=", var_export((bool) $p->imprisoned_or_exiled, true), "\n";
echo "  case:   in exile since ", $case->formatPartialDate("in_exile_since"),
    "  in_exile_for_days=", ($case->in_exile_for_days ?? "null"), "\n";

$days = ExileDuration::totalDays($p->cases);

echo "\n  public counter will read: Time in Exile: ",
    ImprisonmentDuration::phrase(ExileDuration::startFor($p->cases), $days), "\n";
echo "  counted from January 1, 2001 because year precision anchors there;\n";
echo "  the source says LATE 2001, so this reads up to about ten months long.\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "overaker-exile-counter" set_exile_start

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 130 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
