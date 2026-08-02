#!/usr/bin/env bash
#
# BATCH 101 -- Hnaihen photo + BOP number; Mike Sun BOP number;
# Mohammad Yousef Hasna added with a news entry.
#
#   HASHEM YOUNIS HASHEM HNAIHEN — photo REPLACED per the curator
#   with the Orlando Sentinel booking photo (tos-l-Hashem-Hnaihen.jpg;
#   the 1800x1800 frame-s blurred side bars cropped off for an exact
#   3:4 at 525x700), and BOP register number 29753-511 recorded.
#
#   MIKE SUN — BOP register number 15303-506 recorded.
#
#   MOHAMMAD YOUSEF HASNA — new record from the ABC News (AP) wire of
#   the July 31, 2026 arrest: a 45-year-old Turkish man from Istanbul
#   (born circa 1981, approximate year precision) arrested in the
#   United Kingdom on Manhattan federal charges of conspiring to
#   provide material support to Hamas, conspiring to finance
#   terrorism, and financing terrorism; ordered held pending
#   extradition, so in_custody and awaiting_trial are on. The case
#   text carries the allegations as allegations. No ideology or
#   affiliation is asserted. A News article announcing the arrest and
#   pointing at the database entry publishes via
#   articles:add-hasna-arrest, and prisoners:sort-new places the new
#   record.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-101.sh

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
echo "  Batch 101 — Hnaihen photo/BOP, Sun BOP, Hasna record + article"
echo "==================================================================="

fix_batch() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    if [ -f "database/data/photos/hashem-younis-hashem-hnaihen.jpg" ]; then
        cp -f "database/data/photos/hashem-younis-hashem-hnaihen.jpg" "${DST_DIR}/hashem-younis-hashem-hnaihen.jpg"
        echo "copied hashem-younis-hashem-hnaihen.jpg (replacement)"
    fi

    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch101.json")), true);

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

$hn = $payload["hnaihen"];
$p = Prisoner::withUnderReview()->where("slug", $hn["slug"])->first();

if (! $p) {
    echo str_pad($hn["slug"], 32), "NOT FOUND\n";
} else {
    $notes = [];
    $rel = "prisoners/".$hn["slug"].".jpg";

    if ($p->photo !== $rel) {
        $p->photo = $rel;
        $notes[] = "photo field set";
    } else {
        $notes[] = "photo file replaced on disk";
    }

    if ($p->inmate_number !== $hn["inmate_number"]) {
        $p->inmate_number = $hn["inmate_number"];
        $notes[] = "inmate_number=".$hn["inmate_number"];
    }

    $p->save();
    echo str_pad($hn["slug"], 32), implode("; ", $notes), "\n";
}

$sn = $payload["sun"];
$p = Prisoner::withUnderReview()->where("slug", $sn["slug"])->first();

if (! $p) {
    echo str_pad($sn["slug"], 32), "NOT FOUND\n";
} elseif ($p->inmate_number !== $sn["inmate_number"]) {
    $p->inmate_number = $sn["inmate_number"];
    $p->save();
    echo str_pad($sn["slug"], 32), "inmate_number=", $sn["inmate_number"], "\n";
} else {
    echo str_pad($sn["slug"], 32), "already correct\n";
}

$hs = $payload["hasna"];
$spec = $hs["person"];

$p = Prisoner::withUnderReview()
    ->where(function ($q) use ($hs, $spec) {
        $q->where("slug", $hs["slug"])->orWhere("name", $spec["name"]);
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

if (trim((string) $p->description) !== $hs["bio"]) {
    $p->description = $hs["bio"];
}

if ($p->isDirty()) {
    $p->save();
    echo "  person saved\n";
} else {
    echo "  person already correct\n";
}

$cs = $hs["case"];
$case = $p->cases->first(fn ($c) => $c->arrest_date && $c->arrest_date->format("Y-m-d") === $cs["match_arrest"]);
$isNew = ! $case;

if ($isNew) {
    $case = new PrisonerCase;
    $case->prisoner_id = $p->id;
}

$case->setRelation("prisoner", $p);
$notes = [];

foreach (["arrest_date", "incarceration_date"] as $f) {
    if ($applyDate($case, $f, $cs[$f])) {
        $notes[] = $f;
    }
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

echo "  case", ($isNew ? " NEW" : ""), ": ", ($notes ? implode("; ", $notes) : "already correct"),
    "  days=", ($case->imprisoned_for_days ?? "null"), " (running)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-batch-101" fix_batch
run "add-hasna-arrest-article" php artisan articles:add-hasna-arrest
run "sort-new-placement" php artisan prisoners:sort-new

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 101 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
