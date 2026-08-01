#!/usr/bin/env bash
#
# BATCH 63 -- remove the Daniel Jongyon Park entry, on the curator's
# instruction.
#
# The record (slug daniel-jongyon-park, 2020s) and its case row are
# deleted, along with the stored photograph file. Nothing else is
# touched; the institution on his case, if any, is left in place for
# other records. Idempotent: a re-run finds nothing and reports so.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-63.sh

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
echo "  Batch 63 — remove the Daniel Jongyon Park entry"
echo "==================================================================="

remove_park() {
php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\Storage;

$p = Prisoner::withUnderReview()->where("slug", "daniel-jongyon-park")->with("cases")->first();

if (! $p) {
    echo "daniel-jongyon-park already gone — nothing to do.\n";
    return;
}

echo "Removing ", $p->name, " (", $p->slug, ") — ", $p->cases->count(), " case row(s)";

if ($p->photo) {
    if (Storage::disk("public")->exists($p->photo)) {
        Storage::disk("public")->delete($p->photo);
        echo ", photo file deleted";
    }
}

foreach ($p->cases as $c) {
    $c->delete();
}
$p->delete();

echo ".\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "remove-daniel-jongyon-park" remove_park

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 63 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
