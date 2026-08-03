#!/usr/bin/env bash
#
# BATCH 114 -- the Fort Hood Three: exact custody dates, per the
# curator, from The Militant (report datelined October 16, 1968) and
# the NYU Daily Worker photographic archive.
#
#   The recorded dates were wrong on all three records: confinement
#   began July 7, 1966, when military authorities arrested them in
#   New York (not September 27, 1966, and not July 6), and the
#   releases were staggered in October 1968, not September:
#
#     dennis-mora       released 1968-10-16  (832 days) — confirmed
#                       by the LaGuardia return photo, Oct 16, 1968
#     james-johnson-jr  released 1968-10-23  (839 days)
#     david-samas       released 1968-10-27  (843 days)
#
#   These are FORCED date corrections (the existing values are
#   wrong, so the fill-only-empty guard does not apply); the old
#   values are echoed as each is replaced. Birthdates fill in only
#   where empty: Mora January 7, 1941; Johnson and Samas 1946 (year
#   precision). The sourcing is appended to each biography; nothing
#   is deleted from any description.
#
# Run from the repo root, after git pull (after batch 113):
#   bash database/data/run-batch-114.sh

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
echo "  Batch 114 — Fort Hood Three custody dates"
echo "==================================================================="

fix_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch114.json")), true);

if (! $payload || empty($payload["corrections"])) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

foreach ($payload["corrections"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

    echo "\nFIX ", $row["slug"], "\n";

    if (! $p) { echo "  NOT FOUND — skipped\n"; continue; }

    $notes = [];

    if (! empty($row["birthdate"]) && $p->birthdate === null) {
        [$y, $mo, $d] = array_pad($row["birthdate"], 3, null);
        $p->setPartialDate("birthdate", $y, $mo, $d);
        $notes[] = "birthdate set";
    }

    if (! empty($row["append"]) && ! str_contains((string) $p->description, mb_substr($row["append"], 0, 60))) {
        $p->description = trim((string) $p->description)."\n\n".$row["append"];
        $notes[] = "sourcing appended";
    }

    if ($notes) { $p->save(); }

    $case = $p->cases->first();

    if ($case) {
        $case->setRelation("prisoner", $p);
        $caseDirty = false;

        $forced = [
            "arrest_date"        => $row["case_force_arrest"] ?? null,
            "incarceration_date" => $row["case_force_incarceration"] ?? null,
            "release_date"       => $row["case_force_release"] ?? null,
        ];

        foreach ($forced as $field => $spec) {
            if (! $spec) { continue; }
            [$y, $mo, $d] = array_pad($spec, 3, null);
            $old = $case->{$field} ? $case->{$field}->format("Y-m-d") : "empty";
            $new = sprintf("%04d-%02d-%02d", $y, $mo, $d);
            if ($old === $new) { continue; }
            $case->setPartialDate($field, $y, $mo, $d);
            $caseDirty = true;
            $notes[] = $field.": ".$old." -> ".$new;
        }

        if (isset($row["case_force_days"]) && (int) $case->imprisoned_for_days !== $row["case_force_days"]) {
            $old = $case->imprisoned_for_days ?? "empty";
            $case->imprisoned_for_days = $row["case_force_days"];
            $caseDirty = true;
            $notes[] = "days: ".$old." -> ".$row["case_force_days"];
        }

        if ($caseDirty) { $case->save(); }
    } else {
        $notes[] = "no case row found";
    }

    echo "  ", ($notes ? implode("; ", $notes) : "already correct"), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "fort-hood-three-dates" fix_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 114 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
