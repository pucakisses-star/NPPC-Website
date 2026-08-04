#!/usr/bin/env bash
#
# BATCH 133 -- remove John W. Bordenave, per the curator.
#
#   One of the three Black soldiers condemned to death by a civil
#   court at Alexandria, Louisiana in 1942 on a rape charge brought
#   by a white complainant, scheduled to die on October 30, 1942, and
#   reported by New Masses as a wartime legal lynching.
#
#   ONLY HE IS REMOVED. The other two men of the same case,
#   lawrence-mitchell and richard-adams, are in the database with
#   identical case rows and are NOT touched: the curator named
#   Bordenave alone. Mitchell was pasted alongside him without being
#   named and Adams was not mentioned. The script reports both so the
#   decision is visible rather than assumed — see the flagged block
#   in database/data/fixes/batch133.json.
#
#   The removal deletes the record, its case rows and any
#   auto-generated calendar entries. The record is located by slug,
#   falling back to an exact case-insensitive name match, and a name
#   matching more than one record aborts rather than guessing. The
#   record is printed in full before it goes. Idempotent: an
#   already-absent record just reports as such.
#
# Run from the repo root, after git pull (after batch 132):
#   bash database/data/run-batch-133.sh

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
echo "  Batch 133 — remove John W. Bordenave"
echo "==================================================================="

remove_records() {
    php artisan tinker --execute='
use App\Models\CalendarEntry;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch133.json")), true);

if (! $payload || empty($payload["removals"])) {
    echo "Could not read the payload — nothing changed.\n";

    return;
}

foreach ($payload["removals"] as $row) {
    echo "\nREMOVE ", $row["slug"], "\n";

    $matches = Prisoner::withUnderReview()
        ->where(fn ($q) => $q->where("slug", $row["slug"])
            ->orWhereRaw("LOWER(name) = ?", [mb_strtolower($row["name"])]))
        ->with("cases")
        ->get();

    if ($matches->isEmpty()) { echo "  not found (already removed?)\n"; continue; }

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

    echo "  deleted (", $cases, " case rows, ", $cal, " calendar entries) — ", $row["reason"], "\n";
}

foreach ($payload["flagged"] ?? [] as $f) {
    echo "\nFLAGGED, NOT REMOVED — ", implode(", ", $f["slugs"]), "\n  ",
        wordwrap($f["reason"], 88, "\n  "), "\n";

    foreach ($f["slugs"] as $slug) {
        $q = Prisoner::withUnderReview()->where("slug", $slug)->with("cases")->first();

        echo "  ", str_pad($slug, 22), " ",
            ($q ? "still present — ".$q->cases->count()." case row(s), imprisoned_for_days="
                .($q->cases->sum("imprisoned_for_days") ?: "null")
              : "not found"), "\n";
    }
}

if (! empty($payload["note"])) {
    echo "\nNOTE\n  ", wordwrap($payload["note"], 88, "\n  "), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "remove-bordenave" remove_records

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 133 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
