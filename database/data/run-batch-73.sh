#!/usr/bin/env bash
#
# BATCH 73 -- Catherine Kerkow: display name shortened, middle name
# kept as a separate field, final biography set.
#
#   Per the curator: the record becomes CATHERINE KERKOW, with MARIE
#   stored in middle_name but excluded from the full name and the
#   slug. Prisoner::updating() regenerates the slug when the name
#   changes, and "catherine-kerkow" is free, so the profile moves from
#   /prisoner/catherine-marie-kerkow to /prisoner/catherine-kerkow.
#   The photo path does not depend on the slug and is untouched.
#
#   The bio is replaced verbatim with the curator-s final text (the
#   1972 hijacking, asylum in Algeria, the 1975 French arrest and
#   refused extradition, the 1977 flight to Switzerland, and her
#   status as an FBI fugitive).
#
# The text lives in database/data/fixes/kerkow-name-bio.json.
# Idempotent: matched by old or new slug; fields compared before
# writing.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-73.sh

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
echo "  Batch 73 — Catherine Kerkow: name, middle name, final bio"
echo "==================================================================="

fix_kerkow() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/kerkow-name-bio.json")), true);

if (! $payload) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

$p = Prisoner::withUnderReview()
    ->whereIn("slug", [$payload["old_slug"], $payload["new_slug"]])
    ->first();

if (! $p) {
    echo "NOT FOUND: ", $payload["old_slug"], " — nothing changed.\n";
    return;
}

echo $p->slug, "\n";

$notes = [];

foreach (["name", "first_name", "middle_name", "last_name"] as $f) {
    if ($p->{$f} !== $payload[$f]) {
        $p->{$f} = $payload[$f];
        $notes[] = $f."=".$payload[$f];
    }
}

if (trim((string) $p->description) !== $payload["bio"]) {
    $p->description = $payload["bio"];
    $notes[] = "final bio set";
}

if ($notes) {
    $p->save();
}

echo "  ", ($notes ? implode("; ", $notes) : "already correct"), "\n";
echo "  slug now: ", $p->fresh()->slug, "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-kerkow-name-bio" fix_kerkow

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 73 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
