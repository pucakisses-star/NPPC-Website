#!/usr/bin/env bash
#
# BATCH 99 -- Robert Malecki: the custody chronology entered.
#
#   Per the curator-s dossier:
#
#   INCARCERATED NOVEMBER 11, 1969 — the "Silver Spring Revisited"
#   raid; a January 9, 1970 report has all three defendants still
#   jailed, Malecki under $10,000 bond in the Baltimore County Jail.
#   The federal indictment was returned DECEMBER 10, 1969 (recorded
#   in the indicted field).
#
#   RELEASED ON BAIL MARCH 1972 (month precision — an August 18,
#   1972 wire report quotes him saying he was released "last March";
#   no exact day is documented). Total custody ~28 months.
#
#   INSTITUTION: USP LEWISBURG (the roster-s most-used variant) —
#   the federal prison where he was held from approximately August
#   1970, an estimate from his own nine-months-in-Baltimore
#   recollection, flagged as unverified in the sentence text along
#   with the irreconcilable 27-months-at-Lewisburg claim. The
#   Baltimore County Jail phase lives in the sentence text (one
#   institution slot per case).
#
#   The exile span (June 1972 to his death, batch 97) is re-pinned
#   here with identical values, so this batch is safe to run in
#   either order relative to 97 and the release date can never
#   trigger a stray exile auto-derivation.
#
# The prose lives in database/data/fixes/malecki-custody.json.
# Idempotent throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-99.sh

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
echo "  Batch 99 — Robert Malecki: custody chronology"
echo "==================================================================="

fix_malecki() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/malecki-custody.json")), true);

if (! $payload) {
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

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->with("cases")->first();

if (! $p) {
    echo "NOT FOUND: ", $payload["slug"], " — nothing changed.\n";
    return;
}

echo $p->slug, "\n";

$case = $p->cases->sortBy("created_at")->first();

if (! $case) {
    echo "  no case row — nothing to do\n";
} else {
    $cs = $payload["case"];
    $case->setRelation("prisoner", $p);
    $notes = [];

    foreach (["arrest_date", "incarceration_date", "release_date", "in_exile_since", "end_of_exile"] as $f) {
        if ($applyDate($case, $f, $cs[$f])) {
            $notes[] = $f;
        }
    }

    foreach (["indicted", "sentence"] as $f) {
        if ($case->{$f} != $cs[$f]) {
            $case->{$f} = $cs[$f];
            $notes[] = $f;
        }
    }

    $inst = Institution::firstOrCreate(
        ["name" => $cs["institution"]],
        ["city" => $cs["institution_city"], "state" => $cs["institution_state"]]
    );

    if ($case->institution_id !== $inst->id) {
        $case->institution_id = $inst->id;
        $notes[] = "institution=".$inst->name;
    }

    $case->save();

    echo "  case: ", ($notes ? implode("; ", $notes) : "already correct"), "\n";
    echo "    days imprisoned=", ($case->imprisoned_for_days ?? "null"), " (~28 months)",
         "   exile days=", ($case->in_exile_for_days ?? "null"), " (~52 years)\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-robert-malecki-custody" fix_malecki

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 99 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
