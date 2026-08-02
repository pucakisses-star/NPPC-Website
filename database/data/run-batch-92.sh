#!/usr/bin/env bash
#
# BATCH 92 -- Johnny Earl Vines: new record, from the curator-s
# dossier, with the AP arrest photograph.
#
#   The Albuquerque airport aircraft fueler who sheltered Charles
#   Hill, Michael Finney and Ralph Goodwin during the November 1971
#   manhunt after the killing of New Mexico State Police officer
#   Robert Rosenbloom, and allegedly advised them on airport access
#   before the TWA Flight 106 hijacking of November 27, 1971.
#
#   ARRESTED NOVEMBER 30, 1971; convicted of HARBORING FEDERAL
#   FUGITIVES; FIVE YEARS; conviction survived appeal, Supreme Court
#   review denied December 4, 1972. His incarceration and release
#   dates are not documented, so the case carries the arrest date
#   only — no dates are invented. Born circa 1942 (the curator-s
#   "age 29 in 1971", entered at approximate year precision). No
#   ideology or affiliation is asserted — the dossier documents the
#   harboring conviction, not membership.
#
#   PHOTOGRAPH — curator-supplied: the AP Wirephoto of December 1,
#   1971 ("CHARGED WITH HARBORING HIJACKERS"), whose caption reads
#   "Johnny Earl Vines, left, of Albuquerque, N.M., is held by an
#   unidentified FBI agent..." — a positional identification. The
#   left figure is cropped at 525x700 (HistoricImages press-scan
#   watermark faintly present, as with prior press-photo scans).
#
#   Ends with prisoners:sort-new for roster placement.
#
# The prose lives in database/data/fixes/johnny-earl-vines.json.
# Idempotent throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-92.sh

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
echo "  Batch 92 — Johnny Earl Vines: new record + AP photo"
echo "==================================================================="

fix_vines() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    if [ -f "database/data/photos/johnny-earl-vines.jpg" ]; then
        cp -f "database/data/photos/johnny-earl-vines.jpg" "${DST_DIR}/johnny-earl-vines.jpg"
        echo "copied johnny-earl-vines.jpg"
    fi

    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/johnny-earl-vines.json")), true);

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

$spec = $payload["person"];

$p = Prisoner::withUnderReview()
    ->where(function ($q) use ($payload, $spec) {
        $q->where("slug", $payload["slug"])->orWhere("name", $spec["name"]);
    })
    ->with("cases")
    ->first();

if (! $p) {
    $p = Prisoner::create(collect($spec)->except(["birthdate"])->all());
    $p->load("cases");
    echo "CREATED ", $p->slug, "\n";
} else {
    echo $p->slug, " (existing)\n";
    foreach (collect($spec)->except(["birthdate"])->all() as $f => $v) {
        if ($p->{$f} != $v) {
            $p->{$f} = $v;
        }
    }
}

$applyDate($p, "birthdate", $spec["birthdate"]);

if (trim((string) $p->description) !== $payload["bio"]) {
    $p->description = $payload["bio"];
}

$rel = "prisoners/".$payload["slug"].".jpg";
if (is_file(storage_path("app/public/".$rel)) && ! $p->photo) {
    $p->photo = $rel;
    echo "  photo attached (AP Wirephoto, caption-anchored)\n";
}

if ($p->isDirty()) {
    $p->save();
    echo "  person saved\n";
} else {
    echo "  person already correct\n";
}

$cs = $payload["case"];
$case = $p->cases->first(fn ($c) => $c->arrest_date && $c->arrest_date->format("Y-m-d") === $cs["match_arrest"]);
$isNew = ! $case;

if ($isNew) {
    $case = new PrisonerCase;
    $case->prisoner_id = $p->id;
}

$case->setRelation("prisoner", $p);
$notes = [];

if ($applyDate($case, "arrest_date", $cs["arrest_date"])) {
    $notes[] = "arrest_date";
}

foreach (["charges", "convicted", "sentence"] as $f) {
    if ($case->{$f} != $cs[$f]) {
        $case->{$f} = $cs[$f];
        $notes[] = $f;
    }
}

if ($isNew || $notes) {
    $case->save();
}

echo "  case", ($isNew ? " NEW" : ""), ": ", ($notes ? implode("; ", $notes) : "already correct"), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-johnny-earl-vines" fix_vines
run "sort-new-placement" php artisan prisoners:sort-new

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 92 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
