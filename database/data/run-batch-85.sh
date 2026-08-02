#!/usr/bin/env bash
#
# BATCH 85 -- Charlotte O'Neal: middle name, AKA removed, birth date,
# corrected bio, exile 1970-1990.
#
#   Per the curator: middle name HILL recorded (display name stays
#   "Charlotte O'Neal", slug untouched); the "Mama C" AKA removed;
#   born MARCH 9, 1951; bio replaced with the curator-s text. The
#   exile is recorded FROM 1970 TO 1990 (year precision on both
#   ends, about twenty years) on her case row, and currently_in_exile
#   turns off — the historical in_exile flag stays on. The case
#   sentence text is updated to match the recorded span.
#
# The text lives in database/data/fixes/charlotte-oneal.json.
# Idempotent throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-85.sh

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
echo "  Batch 85 — Charlotte O'Neal: names, dates, exile 1970-1990"
echo "==================================================================="

fix_oneal() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/charlotte-oneal.json")), true);

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

foreach (collect($spec)->except(["birthdate"])->all() as $f => $v) {
    if ($p->{$f} !== $v) {
        $p->{$f} = $v;
        $notes[] = $f.($v === null ? " cleared" : "");
    }
}

if ($applyDate($p, "birthdate", $spec["birthdate"])) {
    $notes[] = "birthdate";
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

    foreach (["in_exile_since", "end_of_exile"] as $f) {
        if ($applyDate($case, $f, $cs[$f])) {
            $cnotes[] = $f;
        }
    }

    if ($case->sentence != $cs["sentence"]) {
        $case->sentence = $cs["sentence"];
        $cnotes[] = "sentence";
    }

    if ($cnotes) {
        $case->save();
    }

    echo "  case: ", ($cnotes ? implode("; ", $cnotes) : "already correct"),
        "   exile days=", ($case->in_exile_for_days ?? "null"), " (~20 years)\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-charlotte-oneal" fix_oneal

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 85 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
