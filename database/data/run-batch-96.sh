#!/usr/bin/env bash
#
# BATCH 96 -- Gavin Seim: exile date set.
#
#   Per the curator: IN EXILE SINCE NOVEMBER 24, 2017. His record was
#   already flagged in exile but the case row had no in_exile_since,
#   so the public counter read zero; setting the date starts it
#   (about 8.7 years and counting — he remains in exile, so no end
#   date is set).
#
# Idempotent throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-96.sh

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
echo "  Batch 96 — Gavin Seim: in exile since November 24, 2017"
echo "==================================================================="

fix_seim() {
    php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withUnderReview()->where("slug", "gavin-seim")->with("cases")->first();

if (! $p) {
    echo "NOT FOUND: gavin-seim\n";
    return;
}

echo $p->slug, "\n";

$case = $p->cases->sortBy("created_at")->first();

if (! $case) {
    echo "  no case row — nothing to do\n";
} else {
    $case->setRelation("prisoner", $p);
    $was = $case->in_exile_since ? $case->in_exile_since->format("Y-m-d") : null;
    $case->setPartialDate("in_exile_since", 2017, 11, 24);

    if ($was !== $case->in_exile_since->format("Y-m-d")) {
        $case->save();
        echo "  in_exile_since=2017-11-24", ($was ? " (was ".$was.")" : ""), "\n";
    } else {
        $case->save();
        echo "  exile date already correct — counter recomputed\n";
    }

    echo "  in exile for ", ($case->in_exile_for_days ?? "?"), " day(s)\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-gavin-seim-exile" fix_seim

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 96 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
