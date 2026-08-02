#!/usr/bin/env bash
#
# BATCH 77 -- Joyce Tillerson: dates, French imprisonment, exile from
# the hijacking to her death, expanded biography.
#
#   Previously the record had no dates and exile flags switched off
#   with a stale counter. Per the curator-s dossier:
#
#   PERSON — born JUNE 6, 1951, Spartanburg, South Carolina; died in
#   the 12th arrondissement of Paris APRIL 15, 2000 (the French death
#   index confirms both dates). AKA gains "Joyce Tillerson Burgess"
#   alongside the existing "Joyce Brown". in_exile turns ON
#   (historical), currently_in_exile stays off, released on.
#
#   CASE — per the curator-s explicit instruction, incarceration MAY
#   26, 1976 (the Paris arrest) to NOVEMBER 24, 1978 (the Paris
#   Assize Court verdict: five years, two suspended, released within
#   days having served nearly the whole custodial portion) —
#   the same 912-day Fleury-Mérogis span as Jean McNair. Exile
#   pinned JULY 31, 1972 (the hijacking) to APRIL 15, 2000 (her
#   death), about 27.7 years.
#
#   The bio is replaced with the curator-s text, including the La
#   Cimade, African National Congress and South African embassy
#   years, and the note that the French proceedings reportedly did
#   not establish that she or Jean McNair personally carried a
#   weapon. The state field is left empty per the batch 76 precedent
#   (birthplace lives in the bio).
#
# The prose lives in database/data/fixes/joyce-tillerson-full.json.
# Idempotent throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-77.sh

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
echo "  Batch 77 — Joyce Tillerson: imprisonment, exile span, bio"
echo "==================================================================="

fix_tillerson() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/joyce-tillerson-full.json")), true);

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

$spec = $payload["person"];
$notes = [];

foreach (collect($spec)->except(["birthdate", "death_date"])->all() as $f => $v) {
    if ($p->{$f} != $v) {
        $p->{$f} = $v;
        $notes[] = $f;
    }
}

if ($applyDate($p, "birthdate", $spec["birthdate"])) {
    $notes[] = "birthdate";
}

if ($applyDate($p, "death_date", $spec["death_date"])) {
    $notes[] = "death_date";
}

if (trim((string) $p->description) !== $payload["bio"]) {
    $p->description = $payload["bio"];
    $notes[] = "bio";
}

if ($notes) {
    $p->save();
}

echo "  person: ", ($notes ? implode("; ", $notes) : "already correct"), "\n";

$case = $p->cases->sortBy("created_at")->first();

if (! $case) {
    echo "  no case row found — case update skipped\n";
} else {
    $cs = $payload["case"];
    $case->setRelation("prisoner", $p);
    $cnotes = [];

    foreach (["arrest_date", "incarceration_date", "sentenced_date", "release_date", "in_exile_since", "end_of_exile"] as $f) {
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
    echo "    days imprisoned=", ($case->imprisoned_for_days ?? "null"), " (dossier: ~2.5 years)",
         "   exile days=", ($case->in_exile_for_days ?? "null"), " (~27.7 years)\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-joyce-tillerson" fix_tillerson

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 77 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
