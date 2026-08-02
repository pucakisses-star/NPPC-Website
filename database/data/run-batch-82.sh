#!/usr/bin/env bash
#
# BATCH 82 -- Melvin McNair: the French custody entered.
#
#   Per the curator: FRENCH CUSTODY MAY 26, 1976 (the Paris arrest,
#   with Jean McNair, George Brown and Joyce Tillerson) to SPRING
#   1980 — entered, like George Brown-s in batch 80, as circa April
#   1980 at approximate month precision. Sentenced NOVEMBER 24, 1978
#   (the Paris Assize Court verdict on all four defendants);
#   institution FLEURY-MEROGIS PRISON. The old "relatively light
#   French sentence" text is replaced; no sentence length is asserted
#   for Melvin since the dossiers did not state his. released flag
#   turned on.
#
#   His exile fields are untouched: in_exile_since stays as set (the
#   1972 hijacking) with the counter running — the curator has not
#   ended his exile.
#
# The text lives in database/data/fixes/melvin-mcnair-custody.json.
# Idempotent throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-82.sh

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
echo "  Batch 82 — Melvin McNair: French custody 1976-1980"
echo "==================================================================="

fix_melvin() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/melvin-mcnair-custody.json")), true);

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

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->with("cases")->first();

if (! $p) {
    echo "NOT FOUND: ", $payload["slug"], " — nothing changed.\n";
    return;
}

echo $p->slug, "\n";

if (! $p->released) {
    $p->released = true;
    $p->save();
    echo "  person: released=true\n";
} else {
    echo "  person: already correct\n";
}

$case = $p->cases->sortBy("created_at")->first();

if (! $case) {
    echo "  no case row found — case update skipped\n";
} else {
    $cs = $payload["case"];
    $case->setRelation("prisoner", $p);
    $cnotes = [];

    foreach (["arrest_date", "incarceration_date", "sentenced_date", "release_date"] as $f) {
        if ($applyDate($case, $f, $cs[$f])) {
            $cnotes[] = $f;
        }
    }

    foreach (["convicted", "sentence"] as $f) {
        if ($case->{$f} != $cs[$f]) {
            $case->{$f} = $cs[$f];
            $cnotes[] = $f;
        }
    }

    $inst = Institution::firstOrCreate(
        ["name" => $cs["institution"]],
        ["city" => $cs["institution_city"], "state" => $cs["institution_state"]]
    );

    if ($case->institution_id !== $inst->id) {
        $case->institution_id = $inst->id;
        $cnotes[] = "institution=".$inst->name;
    }

    if ($cnotes) {
        $case->save();
    }

    echo "  case: ", ($cnotes ? implode("; ", $cnotes) : "already correct"), "\n";
    echo "    days imprisoned=", ($case->imprisoned_for_days ?? "null"), " (~3.9 years to spring 1980)",
         "   exile days=", ($case->in_exile_for_days ?? "null"), " (still running)\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-melvin-mcnair-custody" fix_melvin

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 82 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
