#!/usr/bin/env bash
#
# BATCH 74 -- Jean Carol Allen McNair: full dossier — dates, French
# imprisonment, exile ended at her death, expanded biography, photo.
#
#   Previously the record was "Jean McNair" with no dates, no photo,
#   an open-ended exile counter (54 years and counting) and the vague
#   "relatively light French sentence" text. Per the curator-s
#   verified dossier:
#
#   PERSON — full name JEAN CAROL ALLEN McNAIR (maiden name Jean
#   Carol Allen kept as AKA); born October 11, 1946, Winston-Salem,
#   North Carolina; died October 24, 2014, in Caen. The name change
#   regenerates the slug (jean-mcnair -> jean-carol-allen-mcnair).
#   currently_in_exile turns off — her exile ended with her death.
#
#   CASE — arrested in Paris MAY 26, 1976; imprisoned at
#   FLEURY-MEROGIS PRISON through the PARIS ASSIZE COURT conviction of
#   NOVEMBER 24, 1978: five years, two suspended, released immediately
#   for time served (the May 26, 1976 -> November 24, 1978 span
#   computes to exactly the dossier-s 912 days). France refused the
#   American extradition request. The exile span is pinned explicitly:
#   in_exile_since July 31, 1972 (the hijacking) to end_of_exile
#   October 24, 2014 (her death) — about 42 years 3 months, replacing
#   the running counter.
#
#   AFFILIATION — per the dossier-s caution, she is described as a
#   Black-liberation activist associated with the Panther exile
#   community; no formal Black Panther Party membership is asserted
#   (the affiliation list stays empty).
#
#   PHOTO — curator-supplied (ibb.co/60Nzm6qV): Jean with Melvin at
#   home in Caen; her portrait cropped from the full-resolution frame
#   at 525x700. Fills an empty slot only.
#
# The prose lives in database/data/fixes/jean-mcnair-full.json.
# Idempotent: matched by old or new slug; fields compared before
# writing.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-74.sh

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
echo "  Batch 74 — Jean Carol Allen McNair: full dossier"
echo "==================================================================="

fix_mcnair() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    if [ -f "database/data/photos/jean-carol-allen-mcnair.jpg" ]; then
        cp -f "database/data/photos/jean-carol-allen-mcnair.jpg" "${DST_DIR}/jean-carol-allen-mcnair.jpg"
        echo "copied jean-carol-allen-mcnair.jpg"
    fi

    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/jean-mcnair-full.json")), true);

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

$p = Prisoner::withUnderReview()
    ->whereIn("slug", [$payload["old_slug"], $payload["new_slug"]])
    ->with("cases")
    ->first();

if (! $p) {
    echo "NOT FOUND: ", $payload["old_slug"], " — nothing changed.\n";
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

$rel = "prisoners/".$payload["new_slug"].".jpg";
if (is_file(storage_path("app/public/".$rel)) && ! $p->photo) {
    $p->photo = $rel;
    $notes[] = "photo attached";
}

if ($notes) {
    $p->save();
}

echo "  person: ", ($notes ? implode("; ", $notes) : "already correct"), "\n";
echo "  slug now: ", $p->fresh()->slug, "\n";

$case = $p->cases->first(fn ($c) => str_contains((string) $c->charges, "841"))
    ?? $p->cases->sortBy("created_at")->first();

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
    echo "    days imprisoned=", ($case->imprisoned_for_days ?? "null"), " (dossier: ~912)",
         "   exile days=", ($case->in_exile_for_days ?? "null"), " (~42 years 3 months)\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-jean-mcnair" fix_mcnair

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 74 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
