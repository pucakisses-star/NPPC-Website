#!/usr/bin/env bash
#
# BATCH 106 -- the release audit: all 188 in-custody records checked
# against the BOP inmate locator, state DOC locators, court records,
# and support-committee reporting (August 2, 2026).
#
#   28 RELEASED — federal releases carry the BOP actual release date
#   (day precision); deportations, bail, time-served and
#   term-expiration releases carry the precision the record supports,
#   with the evidentiary situation stated in the biography. Carlos
#   Coleman also gets his case corrected: he was ACQUITTED (Rule 29,
#   November 13, 2012), not convicted on a plea as the record said.
#
#   2 DIED IN CUSTODY — Edward Sistrunk (Omar Askia Ali), 2024, and
#   Salih Ali Abdullah, 2020 (fatal stroke at his fourteenth parole
#   appearance). Year precision; both records were years stale.
#
#   2 NOTES, no status change — Larry Hoover (federal commutation May
#   2025, still serving the 200-year Illinois state sentence) and
#   Grailing Brown (transferred to state custody).
#
#   Everything else stays in custody. Unresolved cases, duplicate
#   records, wrong case data, and facility drift are documented in
#   database/data/RELEASE-AUDIT-NOTES.md for the curator — flagged,
#   not changed.
#
# The release_date lands on the case row with the latest arrest date
# that lacks one. Guards: released/in_custody flags compared before
# writing, death dates enter only where the field is empty, appends
# are idempotent by str_contains.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-106.sh

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
echo "  Batch 106 — the release audit"
echo "==================================================================="

fix_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/release-audit.json")), true);

if (! $payload || empty($payload["released"])) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

$applyDate = function ($model, string $field, $spec): bool {
    [$y, $m, $d, $circa] = array_pad($spec, 4, null);
    $wasDate = $model->{$field} ? $model->{$field}->format("Y-m-d") : null;
    $wasPrec = $model->datePrecisionFor($field);
    $model->setPartialDate($field, $y, $m, $d, (bool) $circa);

    return $wasDate !== ($model->{$field} ? $model->{$field}->format("Y-m-d") : null)
        || $wasPrec !== $model->datePrecisionFor($field);
};

foreach ($payload["released"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

    if (! $p) {
        echo str_pad($row["slug"], 34), "NOT FOUND\n";
        continue;
    }

    $notes = [];

    if ($p->in_custody) { $p->in_custody = false; $notes[] = "in_custody=false"; }
    if (! $p->released) { $p->released = true;  $notes[] = "released=true"; }

    if (! empty($row["append"]) && ! str_contains((string) $p->description, mb_substr($row["append"], 0, 60))) {
        $p->description = trim((string) $p->description)."\n\n".$row["append"];
        $notes[] = "bio appended";
    }

    if ($notes) { $p->save(); }

    // release date -> the latest-arrest case row without one
    $case = $p->cases
        ->filter(fn ($c) => ! $c->release_date)
        ->sortByDesc(fn ($c) => $c->arrest_date ? $c->arrest_date->format("Y-m-d") : "0000")
        ->first();

    if ($case && ! empty($row["release"])) {
        $case->setRelation("prisoner", $p);
        if ($applyDate($case, "release_date", $row["release"])) {
            $notes[] = "release_date=".$case->release_date->format("Y-m-d")
                ." (".($case->datePrecisionFor("release_date") ?: "day").")";
        }
        if (! empty($row["fix_case"])) {
            foreach ($row["fix_case"] as $f => $v) {
                if ($case->{$f} != $v) { $case->{$f} = $v; $notes[] = "case ".$f; }
            }
        }
        $case->save();
    } elseif (! empty($row["fix_case"]) && $p->cases->first()) {
        $c2 = $p->cases->first();
        $c2->setRelation("prisoner", $p);
        foreach ($row["fix_case"] as $f => $v) {
            if ($c2->{$f} != $v) { $c2->{$f} = $v; $notes[] = "case ".$f; }
        }
        $c2->save();
    }

    echo str_pad($row["slug"], 34), ($notes ? implode("; ", $notes) : "already correct"), "\n";
}

foreach ($payload["died"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->first();

    if (! $p) {
        echo str_pad($row["slug"], 34), "NOT FOUND\n";
        continue;
    }

    $notes = [];

    if ($p->in_custody) { $p->in_custody = false; $notes[] = "in_custody=false"; }
    if (! $p->released) { $p->released = true;  $notes[] = "released=true"; }

    if ($p->death_date === null && $applyDate($p, "death_date", $row["death"])) {
        $notes[] = "death_date=".$p->death_date->format("Y-m-d")
            ." (".($p->datePrecisionFor("death_date") ?: "day").")";
    }

    if (! empty($row["append"]) && ! str_contains((string) $p->description, mb_substr($row["append"], 0, 60))) {
        $p->description = trim((string) $p->description)."\n\n".$row["append"];
        $notes[] = "bio appended";
    }

    if ($notes) { $p->save(); }
    echo str_pad($row["slug"], 34), ($notes ? implode("; ", $notes) : "already correct"), "\n";
}

foreach ($payload["notes"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->first();

    if (! $p) {
        echo str_pad($row["slug"], 34), "NOT FOUND\n";
        continue;
    }

    if (! str_contains((string) $p->description, mb_substr($row["append"], 0, 60))) {
        $p->description = trim((string) $p->description)."\n\n".$row["append"];
        $p->save();
        echo str_pad($row["slug"], 34), "bio appended (status unchanged)\n";
    } else {
        echo str_pad($row["slug"], 34), "already noted\n";
    }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "release-audit" fix_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 106 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
