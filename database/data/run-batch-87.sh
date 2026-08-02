#!/usr/bin/env bash
#
# BATCH 87 -- Hollis Watkins: new record with three documented
# jailings, from the curator-s dossier.
#
#   PER THE CURATOR-S EXPLICIT INSTRUCTION the name is HOLLIS
#   WATKINS — no Muhammad in the name field (and no AKA). Born July
#   29, 1941, near Summit in Lincoln County, Mississippi; died
#   September 20, 2023, at home in Clinton, Mississippi; buried at
#   Chisholm Mission AME Church Cemetery near Summit.
#
#   THREE CASE ROWS:
#
#   1. McCOMB WOOLWORTH SIT-IN — arrested August 26, 1961 with
#      Curtis Hayes after McComb-s first sit-in; 34 days in jail
#      (reportedly losing 18 pounds); charges later dropped; release
#      entered circa September 29, 1961.
#
#   2. BURGLUND HIGH SCHOOL WALKOUT — arrested October 4, 1961 at
#      McComb City Hall; 39 days in jail per the family biography;
#      release entered circa November 12, 1961.
#
#   3. GREENWOOD VOTER-REGISTRATION IMPRISONMENT — June 1963 (month
#      precision; the AP obituary says 1962 but his first-person
#      account and the Greenwood chronology indicate 1963, as the
#      sentence text records): a four-month sentence after a
#      five-minute trial; city jail, ~1 week at the county penal
#      farm, then 55 days in Parchman-s maximum-security death-row
#      unit; release entered circa August 1963. Institution:
#      Mississippi State Penitentiary (Parchman) — the roster-s
#      most-used variant.
#
#   TAXONOMY — ideology "Civil Rights" (the consolidated taxonomy
#   has no Voting Rights / Anti-Segregation entries; that work is in
#   the bio); affiliations use the existing "Student Nonviolent
#   Coordinating Committee" form plus Mississippi Freedom Democratic
#   Party, Southern Echo and Nation of Islam per the dossier.
#
#   No photograph is attached: the dossier cites the AP portrait but
#   no image was supplied. Drop database/data/photos/
#   hollis-watkins.jpg into a later batch to add one.
#
#   The final step re-runs prisoners:sort-new so the record takes a
#   dated place in the roster.
#
# The prose lives in database/data/fixes/hollis-watkins.json.
# Idempotent throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-87.sh

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
echo "  Batch 87 — Hollis Watkins: new record, three jailings"
echo "==================================================================="

fix_watkins() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/hollis-watkins.json")), true);

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
    $p = Prisoner::create(collect($spec)->except(["birthdate", "death_date"])->all());
    $p->load("cases");
    echo "CREATED ", $p->slug, "\n";
} else {
    echo $p->slug, " (existing)\n";
    foreach (collect($spec)->except(["birthdate", "death_date"])->all() as $f => $v) {
        if ($p->{$f} != $v) {
            $p->{$f} = $v;
        }
    }
}

$applyDate($p, "birthdate", $spec["birthdate"]);
$applyDate($p, "death_date", $spec["death_date"]);

if (trim((string) $p->description) !== $payload["bio"]) {
    $p->description = $payload["bio"];
}

if ($p->isDirty()) {
    $p->save();
    echo "  person saved\n";
} else {
    echo "  person already correct\n";
}

foreach ($payload["cases"] as $cs) {
    $m = $cs["match"];
    $case = $p->cases->first(function ($c) use ($m) {
        if (! $c->arrest_date) {
            return false;
        }
        if (isset($m["arrest"])) {
            return $c->arrest_date->format("Y-m-d") === $m["arrest"];
        }
        if (isset($m["arrest_month"])) {
            return $c->arrest_date->format("Y-m") === $m["arrest_month"];
        }

        return false;
    });

    $isNew = ! $case;

    if ($isNew) {
        $case = new PrisonerCase;
        $case->prisoner_id = $p->id;
    }

    $case->setRelation("prisoner", $p);
    $notes = [];

    foreach (["arrest_date", "incarceration_date", "sentenced_date", "release_date"] as $f) {
        if (array_key_exists($f, $cs) && $applyDate($case, $f, $cs[$f])) {
            $notes[] = $f;
        }
    }

    foreach (["charges", "convicted", "sentence"] as $f) {
        if ($case->{$f} != $cs[$f]) {
            $case->{$f} = $cs[$f];
            $notes[] = $f;
        }
    }

    if (isset($cs["institution"])) {
        $inst = Institution::firstOrCreate(
            ["name" => $cs["institution"]],
            ["city" => $cs["institution_city"] ?? null, "state" => $cs["institution_state"] ?? null]
        );

        if ($case->institution_id !== $inst->id) {
            $case->institution_id = $inst->id;
            $notes[] = "institution=".$inst->name;
        }
    }

    if ($isNew || $notes) {
        $case->save();

        if ($isNew) {
            $p->cases->push($case);
        }
    }

    echo "  case [", $cs["label"], "]", ($isNew ? " NEW" : ""), ": ",
        ($notes ? implode("; ", $notes) : "already correct"),
        "  days=", ($case->imprisoned_for_days ?? "null"), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-hollis-watkins" fix_watkins
run "sort-new-placement" php artisan prisoners:sort-new

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 87 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
