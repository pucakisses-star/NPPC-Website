#!/usr/bin/env bash
#
# BATCH 84 -- Melvin McNair: birth date, the III, corrected bio.
#
#   Per the curator-s correction: MELVIN McNAIR III was born on
#   OCTOBER 30, 1948. The birthdate is set; the full form "Melvin
#   McNair III" is recorded as AKA (display name stays "Melvin
#   McNair", matching the batch 83 convention). The bio is replaced
#   with the corrected text, which adds that his Black-liberation and
#   antiwar politics grew out of the racism he experienced during his
#   military service.
#
# The text lives in database/data/fixes/melvin-mcnair-final.json.
# Idempotent throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-84.sh

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
echo "  Batch 84 — Melvin McNair: birth date, III, corrected bio"
echo "==================================================================="

fix_melvin() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/melvin-mcnair-final.json")), true);

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

if ($p->aka !== $payload["aka"]) {
    $p->aka = $payload["aka"];
    $notes[] = "aka=".$payload["aka"];
}

[$y, $m, $d] = $payload["birthdate"];
$was = $p->birthdate ? $p->birthdate->format("Y-m-d") : null;
$p->setPartialDate("birthdate", $y, $m, $d);

if ($was !== $p->birthdate->format("Y-m-d")) {
    $notes[] = "birthdate=".$p->birthdate->format("Y-m-d").($was ? " (was ".$was.")" : "");
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

run "fix-melvin-mcnair-final" fix_melvin

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 84 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
