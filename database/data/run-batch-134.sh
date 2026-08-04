#!/usr/bin/env bash
#
# BATCH 134 -- remove the remaining two Alexandria 1942 soldiers, per
# the curator: all three go.
#
#   The case: three Black soldiers condemned to death by a civil
#   court at Alexandria, Louisiana in 1942 on a rape charge brought
#   by a white complainant, scheduled to die on October 30, 1942, and
#   reported by New Masses as a wartime legal lynching of servicemen.
#
#     john-w-bordenave    removed by batch 133
#     lawrence-mitchell   removed here
#     richard-adams       removed here
#
#   Bordenave is listed in the payload again so that this script is
#   the complete operation on its own. If batch 133 already ran he
#   reports as absent — the same thing this script does on a second
#   run of its own, and the reason it is safe to run in any order or
#   more than once.
#
#   Each removal deletes the record, its case rows and any
#   auto-generated calendar entries. Records are located by slug,
#   falling back to an exact case-insensitive name match; a name
#   matching more than one record aborts that removal rather than
#   guessing, and every record is printed in full before it goes.
#
# Run from the repo root, after git pull (after batch 133):
#   bash database/data/run-batch-134.sh

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
echo "  Batch 134 — remove the Alexandria 1942 three"
echo "==================================================================="

remove_records() {
    php artisan tinker --execute='
use App\Models\CalendarEntry;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch134.json")), true);

if (! $payload || empty($payload["removals"])) {
    echo "Could not read the payload — nothing changed.\n";

    return;
}

$removed = 0;
$absent = 0;

foreach ($payload["removals"] as $row) {
    echo "\nREMOVE ", $row["slug"], "\n";

    $matches = Prisoner::withUnderReview()
        ->where(fn ($q) => $q->where("slug", $row["slug"])
            ->orWhereRaw("LOWER(name) = ?", [mb_strtolower($row["name"])]))
        ->with("cases")
        ->get();

    if ($matches->isEmpty()) {
        echo "  not found (already removed?)\n";
        $absent++;

        continue;
    }

    if ($matches->count() > 1) {
        echo "  ABORT: ", $matches->count(), " records match. Refusing to guess:\n";
        foreach ($matches as $m) { echo "    ", $m->slug, "  ", $m->name, "\n"; }

        continue;
    }

    $p = $matches->first();

    echo "  record:      ", $p->name, "  [", $p->slug, "]\n";
    echo "  era:         ", ($p->era ?: "-"), "\n";
    echo "  affiliation: ", (is_array($p->affiliation) ? implode(", ", $p->affiliation) : "-"), "\n";
    echo "  ideologies:  ", (is_array($p->ideologies) ? implode(", ", $p->ideologies) : "-"), "\n";
    echo "  photo:       ", ($p->photo ?: "(none)"), "\n";

    $cases = $p->cases->count();
    foreach ($p->cases as $c) { $c->delete(); }

    $cal = CalendarEntry::where("prisoner_id", $p->id)->delete();

    $p->delete();
    $removed++;

    echo "  deleted (", $cases, " case rows, ", $cal, " calendar entries) — ", $row["reason"], "\n";
}

echo "\n", $removed, " removed, ", $absent, " already absent, out of ",
    count($payload["removals"]), " in the payload.\n";

// Anything still carrying the case biography would mean a fourth record
// nobody has accounted for.
$left = Prisoner::withUnderReview()
    ->where("description", "like", "%Alexandria, Louisiana in 1942%")
    ->get();

echo "records still carrying the Alexandria 1942 biography: ", $left->count(), "\n";

foreach ($left as $l) { echo "  ", $l->slug, "  ", $l->name, "\n"; }

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "remove-alexandria-1942" remove_records

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 134 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
