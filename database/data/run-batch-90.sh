#!/usr/bin/env bash
#
# BATCH 90 -- the RNA hijacker trio: exile spans pinned from
# November 27, 1971 to their deaths.
#
#   Per the curator, all three exiles begin NOVEMBER 27, 1971 (the
#   TWA Flight 106 hijacking to Cuba):
#
#   CHARLIE HILL — no end of exile: he remains in Cuba, so
#   currently_in_exile stays on and the counter keeps running.
#
#   MICHAEL FINNEY — end of exile JANUARY 24, 2005, his death
#   (~33 years). currently_in_exile off, historical flag on.
#
#   RALPH GOODWIN — end of exile 1975, his death year (year
#   precision, matching the batch 89 death date). currently_in_exile
#   off, historical flag on.
#
#   Finney-s and Goodwin-s stored exile counters were STALE — still
#   counting to the present even though their current-exile flags
#   were off (the stored day columns only recompute when the case row
#   saves). Pinning the spans re-saves the rows and fixes both
#   counters.
#
# The specs live in database/data/fixes/rna-exile-spans.json.
# Idempotent throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-90.sh

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
echo "  Batch 90 — RNA trio: exile spans from November 27, 1971"
echo "==================================================================="

fix_exiles() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/rna-exile-spans.json")), true);

if (! $payload) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

$applyDate = function ($model, string $field, $spec): bool {
    if ($spec === null) {
        if ($model->{$field} === null) {
            return false;
        }
        $model->setPartialDate($field, null);

        return true;
    }

    [$y, $m, $d, $circa] = array_pad($spec, 4, null);
    $wasDate = $model->{$field} ? $model->{$field}->format("Y-m-d") : null;
    $wasPrec = $model->datePrecisionFor($field);
    $model->setPartialDate($field, $y, $m, $d, (bool) $circa);

    return $wasDate !== ($model->{$field} ? $model->{$field}->format("Y-m-d") : null)
        || $wasPrec !== $model->datePrecisionFor($field);
};

foreach ($payload["people"] as $entry) {
    $p = Prisoner::withUnderReview()->where("slug", $entry["slug"])->with("cases")->first();

    if (! $p) {
        echo str_pad($entry["slug"], 20), "NOT FOUND\n";
        continue;
    }

    $notes = [];

    foreach ($entry["set"] as $f => $v) {
        if ($p->{$f} !== $v) {
            $p->{$f} = $v;
            $notes[] = $f."=".var_export($v, true);
        }
    }

    if ($notes) {
        $p->save();
    }

    $case = $p->cases->sortBy("created_at")->first();

    if (! $case) {
        echo str_pad($entry["slug"], 20), "no case row — skipped\n";
        continue;
    }

    $case->setRelation("prisoner", $p);
    $cnotes = [];

    if ($applyDate($case, "in_exile_since", $entry["in_exile_since"])) {
        $cnotes[] = "in_exile_since";
    }

    if ($applyDate($case, "end_of_exile", $entry["end_of_exile"])) {
        $cnotes[] = "end_of_exile";
    }

    // Always re-save so stale stored counters recompute even when the
    // date fields were already correct.
    $case->save();

    echo str_pad($entry["slug"], 20),
        ($notes || $cnotes ? implode("; ", array_merge($notes, $cnotes)) : "dates already correct"),
        "  exile_days=", ($case->in_exile_for_days ?? "null (counter cleared)"), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-rna-exile-spans" fix_exiles

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 90 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
