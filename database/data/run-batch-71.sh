#!/usr/bin/env bash
#
# BATCH 71 -- Willie Roger Holder: AKA removed, final biography set.
#
#   The curator-s final bio replaces the batch 70 text verbatim (it
#   now names Catherine Marie Kerkow in full and smooths the prose),
#   and the "Roger Holder" AKA set in batch 69 is cleared.
#
# The bio text lives in database/data/fixes/holder-final-bio.json.
# Idempotent throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-71.sh

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
echo "  Batch 71 — Holder: AKA removed, final bio"
echo "==================================================================="

fix_holder() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/holder-final-bio.json")), true);

if (! $payload) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->first();

if (! $p) {
    echo "NOT FOUND: ", $payload["slug"], " — run batch 69 first; nothing changed.\n";
    return;
}

echo $p->slug, "\n";

$notes = [];

if ($p->aka !== null) {
    $notes[] = "aka cleared (was ".$p->aka.")";
    $p->aka = null;
}

if (trim((string) $p->description) !== $payload["bio"]) {
    $p->description = $payload["bio"];
    $notes[] = "final bio set";
}

if ($notes) {
    $p->save();
}

echo "  ", ($notes ? implode("; ", $notes) : "already correct"), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-holder-final-bio" fix_holder

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 71 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
