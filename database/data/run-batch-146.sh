#!/usr/bin/env bash
#
# BATCH 146 -- remove the sixteen ILD / Civil Rights Congress frame-up
# cases, per the curator.
#
#   THE TEST APPLIED IS THE CURATOR'S: a political prisoner is someone
#   imprisoned for their own political activity. In each of these
#   sixteen the political actor is the defence campaign — the
#   International Labor Defense, the Civil Rights Congress or the
#   NAACP — and not the prisoner. Research found no political activity
#   by any of the sixteen, and for roughly half of them no source
#   exists beyond the movement rosters this archive was built from, so
#   the question cannot be carried further than the payload carries it.
#
#   Two of the sixteen are men whose cases became law without their
#   ever having been activists: Clyde Brown (Brown v. Allen) and
#   Herman Dennis (Burns v. Wilson). A third, Jeremiah Reeves, was a
#   sixteen-year-old grocery delivery boy and jazz drummer whose
#   execution set off the Montgomery protests he was never part of.
#
#   RAISED AND OVERRULED, RECORDED SO IT IS NOT MISTAKEN FOR AN
#   OVERSIGHT: the same test reaches records this batch does not
#   touch. The Scottsboro Boys are in this database, described as nine
#   Black teenagers pulled off a freight train, and were not
#   politically active either; nor were festus-coleman or
#   james-victory. 253 records use frame-up or legal-lynching language
#   and 56 name the ILD or CRC while carrying no ideology and no
#   affiliation. The curator was shown this and asked for these
#   sixteen removed regardless. That is what this does.
#
#   Each removal deletes the record, its case rows and any
#   auto-generated calendar entries. Records are located by slug,
#   falling back to an exact case-insensitive name match; a name
#   matching more than one record aborts that removal rather than
#   guessing, and every record is printed in full before it goes.
#
# Run from the repo root, after git pull (after batch 145):
#   bash database/data/run-batch-146.sh

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
echo "  Batch 146 — remove the sixteen ILD/CRC frame-up cases"
echo "==================================================================="

remove_records() {
    php artisan tinker --execute='
use App\Models\CalendarEntry;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch146.json")), true);

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

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "remove-ild-crc-frameups" remove_records

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 146 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
