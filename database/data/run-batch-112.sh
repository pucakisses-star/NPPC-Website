#!/usr/bin/env bash
#
# BATCH 112 -- Allen Blitz case chronology, per the curator.
#
#   The Greensboro Massacre rioting defendant was arrested May 2,
#   1980 — after the felony-riot indictments, not at the November 3,
#   1979 massacre — and jailed approximately three days: a
#   contemporary Workers Viewpoint report has him still in jail on
#   May 3 (with Dori Blitz and Lacie Russell, after Johnson and
#   Manzella bonded out), and on May 5 a judge cut the remaining
#   defendants bail to 5,000 dollars each, apparently permitting
#   release. His empty case-row dates fill in (arrest and
#   incarceration May 2, release May 5, three days), and the
#   sourcing is appended to his biography. Nothing is deleted from
#   the description, and only EMPTY date fields are filled.
#
# Run from the repo root, after git pull (after batch 111):
#   bash database/data/run-batch-112.sh

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
echo "  Batch 112 — Allen Blitz jailing chronology"
echo "==================================================================="

fix_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch112.json")), true);

if (! $payload || empty($payload["corrections"])) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

foreach ($payload["corrections"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

    echo "\nFIX ", $row["slug"], "\n";

    if (! $p) { echo "  NOT FOUND — skipped\n"; continue; }

    $notes = [];

    if (! empty($row["append"]) && ! str_contains((string) $p->description, mb_substr($row["append"], 0, 60))) {
        $p->description = trim((string) $p->description)."\n\n".$row["append"];
        $p->save();
        $notes[] = "sourcing appended";
    }

    $case = null;
    if (! empty($row["case_match_charges"])) {
        $case = $p->cases->first(fn ($c) => $c->charges && str_contains($c->charges, $row["case_match_charges"]));
    }

    if ($case) {
        $case->setRelation("prisoner", $p);
        $caseDirty = false;

        $dates = [
            "arrest_date"        => $row["case_set_arrest"] ?? null,
            "incarceration_date" => $row["case_set_incarceration"] ?? null,
            "release_date"       => $row["case_set_release"] ?? null,
        ];

        foreach ($dates as $field => $spec) {
            if ($spec && $case->{$field} === null) {
                [$y, $mo, $d] = array_pad($spec, 3, null);
                $case->setPartialDate($field, $y, $mo, $d);
                $caseDirty = true;
                $notes[] = $field." set";
            }
        }

        if (isset($row["case_set_days"]) && $case->imprisoned_for_days === null) {
            $case->imprisoned_for_days = $row["case_set_days"];
            $caseDirty = true;
            $notes[] = "days set";
        }

        if ($caseDirty) { $case->save(); }
    } elseif (! empty($row["case_match_charges"])) {
        $notes[] = "matching case not found (already corrected?)";
    }

    echo "  ", ($notes ? implode("; ", $notes) : "already correct"), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "blitz-chronology" fix_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 112 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
