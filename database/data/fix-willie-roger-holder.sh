#!/usr/bin/env bash
#
# WILLIE ROGER HOLDER -- new record: Catherine Marie Kerkow-s
# co-hijacker, from the curator-s dossier.
#
#   DOB June 14, 1949; died February 6, 2012; buried at Fort Rosecrans
#   National Cemetery, San Diego. Three case rows:
#
#   1. AIR PIRACY (US) — the June 2, 1972 hijacking of Western
#      Airlines Flight 701. In exile June 3, 1972 - July 26, 1986
#      (Algeria, then France). US custody from his July 26, 1986
#      return — pretrial custody, then federal imprisonment — until
#      AUGUST 1989 (month precision; the day is not established).
#
#   2. FRENCH CUSTODY — arrested with Kerkow in Paris January 25,
#      1975; Fleury-Mérogis Prison; France refused the US extradition
#      April 7, 1975 as politically motivated; convicted June 2, 1975
#      of presenting falsified passports, THREE MONTHS AND FIFTEEN
#      DAYS (his sentence ran two weeks past hers), released for time
#      served (~128 days; June 2, 1975 at approximate precision). No
#      escape.
#
#   3. FRENCH HIJACKING CONVICTION, 1980 (year precision) — five
#      years, SUSPENDED; no custody under it, so the row carries no
#      custody dates.
#
# PHOTOGRAPH: curator-supplied — the Vietnam-era service photograph of
# Holder in a tank turret (live.staticflickr.com/1815/
# 29110147798_331f680f7b_b.jpg), cropped to 525x700. Fills an empty
# slot only.
#
# EXILE COUNTER GUARD: exile days are summed across case rows, and
# PrisonerCase::saving() auto-derives in_exile_since from release_date
# for anyone flagged in_exile. The real exile span (June 3, 1972 -
# July 26, 1986) lives on the air-piracy row explicitly; the French
# row pins a zero-length pair (in_exile_since = end_of_exile =
# 1975-06-02) so the hook cannot invent a second span there. The 1980
# row has no release date, so the hook does not touch it.
#
# The prose carries apostrophes, so the payload lives in
# database/data/fixes/willie-roger-holder.json.
#
# Idempotent: the person is matched by slug or name and created only
# if absent; case rows are matched by arrest date (or the 1980
# sentencing year) and created only if absent; every field is
# compared before writing.
#
# Run from the repo root:
#   bash database/data/fix-willie-roger-holder.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DST_DIR="storage/app/public/prisoners"
mkdir -p "$DST_DIR"

if [ -f "database/data/photos/willie-roger-holder.jpg" ]; then
    cp -f "database/data/photos/willie-roger-holder.jpg" "${DST_DIR}/willie-roger-holder.jpg"
    echo "copied willie-roger-holder.jpg"
fi

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/willie-roger-holder.json")), true);

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

if ($p->slug !== $payload["slug"]) {
    echo "  NOTE: generated slug is ", $p->slug, " (payload expected ", $payload["slug"], ")\n";
}

$applyDate($p, "birthdate", $spec["birthdate"] ?? null);
$applyDate($p, "death_date", $spec["death_date"] ?? null);

$rel = "prisoners/".$payload["slug"].".jpg";
if (is_file(storage_path("app/public/".$rel)) && ! $p->photo) {
    $p->photo = $rel;
    echo "  photo attached (Vietnam service photograph, curator-supplied)\n";
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
        if (isset($m["arrest"])) {
            return $c->arrest_date && $c->arrest_date->format("Y-m-d") === $m["arrest"];
        }
        if (isset($m["sentenced_year"])) {
            return $c->sentenced_date && $c->sentenced_date->format("Y") === (string) $m["sentenced_year"];
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

    foreach (["arrest_date", "incarceration_date", "sentenced_date", "release_date", "in_exile_since", "end_of_exile"] as $f) {
        if (array_key_exists($f, $cs) && $applyDate($case, $f, $cs[$f])) {
            $notes[] = $f;
        }
    }

    foreach (["charges", "convicted", "sentence"] as $f) {
        if (array_key_exists($f, $cs) && $case->{$f} != $cs[$f]) {
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
        "  days=", ($case->imprisoned_for_days ?? "null"),
        "  exile_days=", ($case->in_exile_for_days ?? "null"), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
