#!/usr/bin/env bash
#
# BATCH 80 -- George Brown: dates, French imprisonment, exile from
# the hijacking to his death, expanded biography.
#
#   The Flight 841 hijacker (slug george-brown — NOT the Civil
#   War-era Baltimore mayor george-william-brown). Per the curator-s
#   dossier:
#
#   PERSON — born MARCH 28, 1944; died in the 20th arrondissement of
#   Paris OCTOBER 9, 2015. released on, in_exile on (historical),
#   currently_in_exile off.
#
#   CASE — arrested in Paris MAY 26, 1976; held at FLEURY-MEROGIS;
#   sentenced by the Paris Assize Court NOVEMBER 24, 1978 to five
#   years (no suspension recorded); released in the SPRING OF 1980
#   after about three years in total custody — entered as circa
#   April 1980 at approximate month precision. Exile pinned JULY 31,
#   1972 (the hijacking) to OCTOBER 9, 2015 (his death), the same
#   convention the curator set for Jean McNair and Joyce Tillerson.
#
#   The bio is the curator-s text (the New Jersey escape with George
#   Wright, the federal unlawful-flight warrant, the hijacking as one
#   of the three armed men, the Four of Fleury support campaign, his
#   Paris years as a painter).
#
# The prose lives in database/data/fixes/george-brown-full.json.
# Idempotent throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-80.sh

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
echo "  Batch 80 — George Brown: imprisonment, exile span, bio"
echo "==================================================================="

fix_brown() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/george-brown-full.json")), true);

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
    echo "    days imprisoned=", ($case->imprisoned_for_days ?? "null"), " (~3.9 years to spring 1980)",
         "   exile days=", ($case->in_exile_for_days ?? "null"), " (~43.2 years)\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-george-brown" fix_brown

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 80 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
