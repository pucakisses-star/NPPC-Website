#!/usr/bin/env bash
#
# THE CRDL MINING COHORT -- seventy-two new records and eight enriched
# ones, from the curator's systematic mining of the Civil Rights
# Digital Library: the 1961 Jackson Freedom Ride and sit-in arrestees,
# the Americus Four pair, the Albany train-station eight's leaders,
# and the Greensboro 1963 detainee.
#
# THE EVIDENTIARY LINE THIS BATCH HOLDS: CRDL's Sovereignty Commission
# records prove the ARREST and the IDENTIFICATION PHOTOGRAPH — they do
# not prove conviction, a Parchman transfer, a standard four-month
# sentence, or a release date. Per the curator's own custody caution,
# NOBODY in this batch is assigned the generic Freedom Rider custody
# path: dispositions read as unresolved, releases are left empty, and
# no institution is entered unless a record documents it (Rogers at
# Parchman, where the catalog reports she miscarried; Stoner's 1964
# Forrest County work camp; the Americus pair's August 8 to November 1,
# 1963 no-bail confinement, which IS a documented span and is entered).
#
# NO RACE IS SET on any new record: the dossier does not state it and
# a photograph is not a documentation standard this database infers
# from. Birth years derived from ages at arrest enter at CIRCA
# precision, the Camden 28 method. Catalog misspellings (Barouh,
# Brienes, Joesph, Rosenbert, Glinda, Thimothy, Lorenze, O-Conner,
# Sam-Jo, Loring, Schwarzchild, Woollcodt, Merdith) are preserved as
# AKAs or bio notes, never as the primary form.
#
# EXISTING RECORDS ENRICHED, never clobbered: Carl Braden gains his
# 1954 Kentucky sedition case (fifteen years, about eight months
# served, overturned 1956); Igal Roodenko his 1947 Journey of
# Reconciliation chain-gang case; Wyatt T. Walker a third case row for
# the June 21, 1961 Jackson arrest, his vitals, and his mugshot; Felix
# Singer his arrest date, circa-1929 birth and mugshot; James Forman
# and Bernard Lee the December 10, 1961 Albany train-station arrests;
# John Lewis's 1961 arrest tightens from year precision to May 24 —
# the date on his Jackson police placard. DIANE NASH loses an
# impossible date: her stored case had release 1961-07-01 on an arrest
# of 1962-03-31, a release nine months before the arrest; it is
# cleared, not replaced. NO existing photograph is replaced anywhere
# in this batch, and appends are guarded so a curator-edited biography
# is never overwritten.
#
# THE MUGSHOT DATES OVERRIDE THE DOSSIER twice, on placard evidence:
# John Lewis (placard 20886, 5-24-61) and Dion Diamond (placard 20897,
# 5-24-61) enter May 24, 1961 where the dossier said May 25.
#
# PHOTOGRAPHS: provenance table in CREDITS-crdl-freedom-riders.md.
# Every attached image is the individually captioned Mississippi State
# Sovereignty Commission identification photograph the catalog itself
# ties to the person — the same anchor class as batch 59 — cropped to
# the frontal panel at 525x700. The attach loop only fills EMPTY photo
# slots. Slugs with no image yet are pre-listed and report missing
# until a file is dropped in.
#
# The payload lives in database/data/fixes/crdl-freedom-riders.json.
#
# Idempotent: people matched by slug, case rows matched by arrest date
# (or by a charges fragment for the added cases), appends guarded by
# str_contains, every field compared before writing.
#
# Run from the repo root:
#   bash database/data/fix-crdl-freedom-riders.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DST_DIR="storage/app/public/prisoners"
mkdir -p "$DST_DIR"

copied=0
missing=0
for SRC in database/data/photos/crdl/*.jpg; do
    [ -e "$SRC" ] || continue
    base="$(basename "$SRC")"
    cp -f "$SRC" "${DST_DIR}/${base}"
    copied=$((copied+1))
done
echo "copied ${copied} portrait(s) from database/data/photos/crdl/"

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/crdl-freedom-riders.json")), true);

if (! $payload || empty($payload["new"])) {
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

$applyCase = function (Prisoner $p, array $spec, ?PrisonerCase $case) use ($applyDate): array {
    $isNew = ! $case;

    if ($isNew) {
        $case = new PrisonerCase;
        $case->prisoner_id = $p->id;
    }

    $case->setRelation("prisoner", $p);

    $notes = [];

    foreach (["arrest" => "arrest_date", "incarceration" => "incarceration_date", "release" => "release_date"] as $k => $field) {
        if (array_key_exists($k, $spec) && $applyDate($case, $field, $spec[$k])) {
            $notes[] = $field."=".($case->{$field} ? $case->{$field}->format("Y-m-d") : "null")
                ." (".($case->datePrecisionFor($field) ?: "day").")";
        }
    }

    foreach (["charges", "convicted", "sentence"] as $field) {
        if (array_key_exists($field, $spec) && $case->{$field} != $spec[$field]) {
            $case->{$field} = $spec[$field];
            $notes[] = $field;
        }
    }

    if (! empty($spec["institution"]) && ! $case->institution_id) {
        $inst = Institution::firstOrCreate(
            ["name" => $spec["institution"]],
            ["city" => $spec["institution_city"] ?? null, "state" => $spec["institution_state"] ?? null]
        );
        $case->institution_id = $inst->id;
        $notes[] = "institution=".$inst->name;
    }

    if ($isNew || $notes) {
        $case->save();
    }

    return [$isNew, $notes];
};

// ---- the new records ---------------------------------------------------

$created = 0;
$updatedInPlace = 0;

foreach ($payload["new"] as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->with("cases")->first();
    $isNew = ! $p;

    if ($isNew) {
        $p = new Prisoner;
        $p->slug = $row["slug"];
    }

    foreach (["name", "first_name", "middle_name", "last_name", "gender", "era", "state", "aka", "description"] as $f) {
        if (array_key_exists($f, $row) && $p->{$f} !== $row[$f]) {
            $p->{$f} = $row[$f];
        }
    }

    foreach (["affiliation", "ideologies"] as $f) {
        if (array_key_exists($f, $row) && $p->{$f} != $row[$f]) {
            $p->{$f} = $row[$f];
        }
    }

    foreach (["birth" => "birthdate", "death" => "death_date"] as $k => $field) {
        if (array_key_exists($k, $row)) {
            $applyDate($p, $field, $row[$k]);
        }
    }

    $p->in_custody = false;
    $p->released = true;

    $rel = "prisoners/".$row["slug"].".jpg";
    if (is_file(storage_path("app/public/".$rel)) && ! $p->photo) {
        $p->photo = $rel;
    }

    $p->save();
    $p->load("cases");

    echo str_pad($row["slug"], 30), ($isNew ? "CREATED" : "updated in place"),
         ($p->photo ? "  [photo]" : "  [no photo yet]"), "\n";

    $isNew ? $created++ : $updatedInPlace++;

    // Case rows matched by arrest year+month so re-runs update in place.
    $existing = $p->cases->all();
    foreach ($row["cases"] as $spec) {
        $match = null;
        foreach ($existing as $c) {
            if ($c->arrest_date && ! empty($spec["arrest"][0])
                && (int) $c->arrest_date->format("Y") === $spec["arrest"][0]
                && ((int) $c->arrest_date->format("n") === (int) ($spec["arrest"][1] ?? (int) $c->arrest_date->format("n")))) {
                $match = $c;
                break;
            }
        }
        if (! $match && count($row["cases"]) === 1) {
            $match = $existing[0] ?? null;
        }
        [$cNew, $cNotes] = $applyCase($p, $spec, $match);
        echo "    case ", ($cNew ? "NEW  " : "     "), ($cNotes ? implode(", ", $cNotes) : "unchanged"), "\n";
    }
}

// ---- the existing-record enrichments ----------------------------------

foreach ($payload["updates"] as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->with("cases")->first();

    if (! $p) {
        echo "\nNOT FOUND: ", $row["slug"], " — skipped\n";
        continue;
    }

    echo "\n", $row["slug"], "\n";
    if (! empty($row["note"])) {
        echo "  NOTE: ", str_replace("\n", "\n  ", wordwrap($row["note"], 68)), "\n";
    }

    $notes = [];

    foreach (["first_name", "middle_name", "last_name"] as $f) {
        if (! empty($row[$f]) && $p->{$f} !== $row[$f]) {
            $p->{$f} = $row[$f];
            $notes[] = $f;
        }
    }

    if (! empty($row["aka"]) && $p->aka !== $row["aka"]) {
        $p->aka = trim(($p->aka ? $p->aka."; " : "").$row["aka"], "; ");
        $notes[] = "aka";
    }

    foreach (["birth" => "birthdate", "death" => "death_date"] as $k => $field) {
        if (array_key_exists($k, $row) && $applyDate($p, $field, $row[$k])) {
            $notes[] = $field."=".$p->{$field}->format("Y-m-d")." (".($p->datePrecisionFor($field) ?: "day").")";
        }
    }

    if (! empty($row["append"]) && ! str_contains((string) $p->description, mb_substr($row["append"], 0, 60))) {
        $p->description = trim((string) $p->description)."\n\n".$row["append"];
        $notes[] = "description appended";
    }

    if (! empty($row["photo_slug"])) {
        $rel = "prisoners/".$row["photo_slug"].".jpg";
        if (is_file(storage_path("app/public/".$rel)) && ! $p->photo) {
            $p->photo = $rel;
            $notes[] = "photo attached";
        }
    }

    if ($notes) {
        $p->save();
    }

    echo "  ", ($notes ? implode("; ", $notes) : "person unchanged"), "\n";

    if (! empty($row["case_arrest"])) {
        $case = $p->cases->sortBy("created_at")->first();
        if ($case) {
            $case->setRelation("prisoner", $p);
            if ($applyDate($case, "arrest_date", $row["case_arrest"])) {
                $case->save();
                echo "  case: arrest_date=", $case->arrest_date->format("Y-m-d"),
                     " (", ($case->datePrecisionFor("arrest_date") ?: "day"), ")\n";
            }
        }
    }

    if (! empty($row["fix_impossible_release"])) {
        foreach ($p->cases as $case) {
            if ($case->arrest_date && $case->release_date && $case->release_date->lt($case->arrest_date)) {
                echo "  cleared impossible release ", $case->release_date->format("Y-m-d"),
                     " (before the ", $case->arrest_date->format("Y-m-d"), " arrest)\n";
                $case->setRelation("prisoner", $p);
                $case->setPartialDate("release_date", null);
                $case->save();
            }
        }
    }

    if (! empty($row["add_case"])) {
        $spec = $row["add_case"];
        $already = $p->cases->first(function ($c) use ($spec) {
            return $c->charges && str_contains($c->charges, $spec["match_missing_charges"]);
        });
        if ($already) {
            echo "  added case already present — skipped\n";
        } else {
            [$cNew, $cNotes] = $applyCase($p, $spec, null);
            echo "  case NEW  ", implode(", ", $cNotes), "\n";
        }
    }
}

$cohort = Prisoner::withoutGlobalScopes()->get()->filter(function ($x) {
    foreach ((array) $x->cases as $c) {
        // cheap proxy not needed; count by description marker instead
    }

    return str_contains((string) $x->description, "Civil Rights Digital Library");
});

echo "\ncreated: ", $created, "   updated in place: ", $updatedInPlace,
     "   CRDL-sourced records now: ", $cohort->count(), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
