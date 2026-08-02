#!/usr/bin/env bash
#
# BATCH 76 -- George Wright: state removed, corrected biography set.
#
#   The curator-s corrections to batch 75: the STATE field (Virginia)
#   is cleared, and the bio is replaced verbatim with the shorter
#   corrected text (BLA membership, the hijacking, France then
#   Guinea-Bissau then Portugal, the 2011 fingerprint arrest, the
#   refused extradition and release).
#
# The bio lives in database/data/fixes/george-wright-bio2.json.
# Idempotent throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-76.sh

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
echo "  Batch 76 — George Wright: state removed, corrected bio"
echo "==================================================================="

fix_wright() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/george-wright-bio2.json")), true);

if (! $payload) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->first();

if (! $p) {
    echo "NOT FOUND: ", $payload["slug"], " — nothing changed.\n";
    return;
}

echo $p->slug, "\n";

$notes = [];

if ($p->state !== null) {
    $notes[] = "state cleared (was ".$p->state.")";
    $p->state = null;
}

if (trim((string) $p->description) !== $payload["bio"]) {
    $p->description = $payload["bio"];
    $notes[] = "corrected bio set";
}

if ($notes) {
    $p->save();
}

echo "  ", ($notes ? implode("; ", $notes) : "already correct"), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-george-wright-bio2" fix_wright

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 76 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
