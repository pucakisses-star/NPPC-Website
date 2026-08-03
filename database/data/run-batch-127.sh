#!/usr/bin/env bash
#
# BATCH 127 -- Víctor Manuel Gerena listed as currently in exile,
# per the curator.
#
#   His record carried in_exile (the historical fact) but not
#   currently_in_exile (the present state), so imprisoned_or_exiled
#   derived to 0 and he was absent from the "currently imprisoned or
#   exiled" lists the site builds from that column — while his
#   profile showed the In Exile badge and a running exile counter.
#   The counter was running on its own: the nightly
#   cases:update-imprisoned-days command writes in_exile_for_days for
#   any open-ended exile row regardless of the flag (see the note at
#   the end of this file).
#
#   Setting currently_in_exile makes the three agree: the badge, the
#   counter, and the currently-active lists.
#
#   in_exile_since stays September 12, 1983 — the day of the West
#   Hartford robbery, after which he disappeared and has never been
#   apprehended.
#
#   The biography closes on "his current status is unknown", which
#   would sit oddly against a flag asserting he is in exile now, so a
#   line is APPENDED recording the basis for the listing. Nothing in
#   the biography is replaced or removed.
#
#   Idempotent.
#
# Run from the repo root, after git pull (after batch 126):
#   bash database/data/run-batch-127.sh

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
echo "  Batch 127 — Víctor Manuel Gerena: currently in exile"
echo "==================================================================="

set_currently_in_exile() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Support\ExileDuration;

$p = Prisoner::withUnderReview()->where("slug", "victor-manuel-gerena")->with("cases")->first();

if (! $p) { echo "victor-manuel-gerena NOT FOUND — nothing changed.\n"; return; }

echo "record: ", $p->name, "  [", $p->slug, "]\n";
echo "  before: in_exile=", var_export((bool) $p->in_exile, true),
    "  currently_in_exile=", var_export((bool) $p->currently_in_exile, true),
    "  imprisoned_or_exiled=", var_export((bool) $p->imprisoned_or_exiled, true), "\n";

$notes = [];

if (! $p->currently_in_exile) { $p->currently_in_exile = true; $notes[] = "currently_in_exile=true"; }
if (! $p->in_exile) { $p->in_exile = true; $notes[] = "in_exile=true"; }

$addition = "The record carries him as currently in exile: he has never been apprehended, and no return to United States jurisdiction has been recorded.";

if (strpos((string) $p->description, "carries him as currently in exile") === false) {
    $p->description = trim((string) $p->description)." ".$addition;
    $notes[] = "biography line appended";
}

// imprisoned_or_exiled is derived in Prisoner::saving from in_custody and
// currently_in_exile, so saving is what puts him back into the
// currently-active lists.
if ($notes) { $p->save(); }

$p->refresh()->load("cases");

echo "  ", implode("; ", $notes ?: ["already correct"]), "\n";
echo "  after:  in_exile=", var_export((bool) $p->in_exile, true),
    "  currently_in_exile=", var_export((bool) $p->currently_in_exile, true),
    "  imprisoned_or_exiled=", var_export((bool) $p->imprisoned_or_exiled, true), "\n";

// Re-save the exile case rows so in_exile_for_days is written by the model
// hook, which only counts an open-ended exile when the flag is set. Until
// now the stored figure came from the nightly command, which does not check.
foreach ($p->cases as $c) {
    if ($c->in_exile_since) { $c->save(); }
}

$p->refresh()->load("cases");

$days = ExileDuration::totalDays($p->cases);
echo "  exile:  since ", (ExileDuration::startFor($p->cases)?->format("Y-m-d") ?? "-"),
    "  ", $days, " day(s) — ", round($days / 365.25, 1), " years\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "gerena-currently-in-exile" set_currently_in_exile

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 127 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
echo
echo "NOTE, not acted on here — the nightly cases:update-imprisoned-days"
echo "command recomputes imprisoned_for_days / in_exile_for_days for every"
echo "open-ended case row without checking in_custody, awaiting_trial or"
echo "currently_in_exile, and writes with saveQuietly() so the model hook"
echo "cannot correct it. That is why Gerena's counter ran with the flag"
echo "off. Across the database it is currently publishing 596 open-ended"
echo "imprisonment counters for people nobody is holding and 757 exile"
echo "counters with the flag off — including 'imprisoned for 324 years'"
echo "and 'in exile for 193 years'. Fixing it is a separate change: it"
echo "would drop those counters from ~1,350 public profiles."
