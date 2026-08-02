#!/usr/bin/env bash
#
# BATCH 107 -- taxonomy swap: "Black Lives Matter" leaves the
# IDEOLOGY list; the people who carried it gain the existing
# "Black Lives Matter Movement" AFFILIATION instead.
#
#   At audit time (August 2, 2026) 224 records carried the exact
#   ideology string "Black Lives Matter"; 12 records already used the
#   "Black Lives Matter Movement" affiliation, so the swap converges
#   on the established term. The sweep is rule-based and idempotent:
#   for every prisoner whose ideologies contain the exact string, it
#   removes that entry and adds the affiliation only if not already
#   present. Other ideologies (Police Accountability, Anti-Racism,
#   Pro-Palestine...) are left untouched, and nothing else about the
#   record changes.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-107.sh

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
echo "  Batch 107 — Black Lives Matter: ideology -> affiliation"
echo "==================================================================="

fix_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;

$IDEOLOGY    = "Black Lives Matter";
$AFFILIATION = "Black Lives Matter Movement";

$changed = 0;
$affAdded = 0;
$affAlready = 0;

Prisoner::withUnderReview()->chunkById(200, function ($people) use ($IDEOLOGY, $AFFILIATION, &$changed, &$affAdded, &$affAlready) {
    foreach ($people as $p) {
        $ideos = (array) ($p->ideologies ?? []);

        if (! in_array($IDEOLOGY, $ideos, true)) {
            continue;
        }

        $p->ideologies = array_values(array_filter($ideos, fn ($i) => $i !== $IDEOLOGY));

        $affs = (array) ($p->affiliation ?? []);

        if (! in_array($AFFILIATION, $affs, true)) {
            $affs[] = $AFFILIATION;
            $p->affiliation = array_values($affs);
            $affAdded++;
        } else {
            $affAlready++;
        }

        $p->save();
        $changed++;
    }
});

echo "records changed: ", $changed,
     "   affiliation added: ", $affAdded,
     "   affiliation already present: ", $affAlready, "\n";

$left = Prisoner::withUnderReview()->get()->filter(
    fn ($p) => in_array($IDEOLOGY, (array) ($p->ideologies ?? []), true)
)->count();
echo "records still carrying the ideology: ", $left, "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "blm-ideology-to-affiliation" fix_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 107 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
