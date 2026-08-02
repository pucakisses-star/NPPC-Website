#!/usr/bin/env bash
#
# BATCH 83 -- the Flight 841 five: final biographies, AKAs removed,
# Jean McNair-s name shortened.
#
#   GEORGE WRIGHT — AKA (José Luís Jorge dos Santos) removed so the
#   record shows just "George Wright"; final bio set. The middle-name
#   field (Edward) stays, matching the Jean treatment where the
#   middle name lives in its field but not the display name.
#
#   JOYCE TILLERSON — both AKA names removed; final bio set (death
#   from cancer in 2000).
#
#   GEORGE BROWN — final bio set.
#
#   JEAN McNAIR — AKA removed; name shortened from "Jean Carol Allen
#   McNair" back to "JEAN McNAIR" with CAROL kept in middle_name.
#   The rename regenerates the slug (jean-carol-allen-mcnair ->
#   jean-mcnair, which she vacated in batch 74, so it is free); she
#   is matched by either slug so the batch runs correctly whether or
#   not 74 has been applied. Final bio set.
#
#   MELVIN McNAIR — final bio set.
#
# The bios live in database/data/fixes/flight841-final-bios.json.
# Idempotent throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-83.sh

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
echo "  Batch 83 — Flight 841 five: final bios, AKAs removed"
echo "==================================================================="

fix_bios() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/flight841-final-bios.json")), true);

if (! $payload) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

foreach ($payload["people"] as $entry) {
    $p = Prisoner::withUnderReview()->whereIn("slug", $entry["slugs"])->first();

    if (! $p) {
        echo str_pad($entry["slugs"][0], 26), "NOT FOUND\n";
        continue;
    }

    $notes = [];

    foreach ($entry["set"] as $f => $v) {
        if ($p->{$f} !== $v) {
            $p->{$f} = $v;
            $notes[] = $f.($v === null ? " cleared" : "=".$v);
        }
    }

    if (trim((string) $p->description) !== $entry["bio"]) {
        $p->description = $entry["bio"];
        $notes[] = "final bio set";
    }

    if ($notes) {
        $p->save();
    }

    echo str_pad($p->fresh()->slug, 26), ($notes ? implode("; ", $notes) : "already correct"), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-flight841-final-bios" fix_bios

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 83 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
