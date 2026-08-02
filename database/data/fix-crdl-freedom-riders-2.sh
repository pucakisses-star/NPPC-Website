#!/usr/bin/env bash
#
# THE CRDL MINING COHORT, SECOND WAVE -- the curator's continued
# systematic mining of the Civil Rights Digital Library: the remaining
# 1961 Jackson Freedom Ride arrestees, the Jackson sit-in campaigns
# (Livingston Park, the Greyhound and Trailways terminals, the
# Illinois Central terminal, the Walgreens lunch counters, the
# Tougaloo Nine library read-in), the September 13, 1961 Episcopal
# clergy ride that became Pierson v. Ray, the December 10, 1961
# Albany train-station arrests, and one 1954 literature arrest.
#
# THE EVIDENTIARY LINE THIS BATCH HOLDS, same as the first wave:
# CRDL's Sovereignty Commission records prove the ARREST and the
# IDENTIFICATION PHOTOGRAPH — they do not prove conviction, a
# Parchman transfer, a standard four-month sentence, or a release
# date. Dispositions read as unresolved, releases are left empty, and
# no institution is entered unless a record documents it. Documented
# exceptions only: Frances Lee Wilson Canty (60 days + $200 per a
# family obituary, Parchman transfer), Terry Perlman Hickerson (Hinds
# County jail then Parchman maximum security, her own account), Byron
# Mark Baer (forty-five days at Parchman per archival and biographical
# sources), and Helen Irene Singleton (city jail then Parchman
# maximum security, her own account). The fifteen Episcopal clergymen
# carry their documented September 15 municipal-court conviction —
# four months and $200 apiece, dismissed May 21, 1962, the episode
# that reached the Supreme Court as Pierson v. Ray — with time
# physically served left unresolved.
#
# EVERY DIRECT CRDL RECORD ID IN THIS BATCH WAS RE-VERIFIED against
# the live catalog before import: the record page was fetched and its
# title checked against the person's name (with the catalog's own
# [sic] spellings preserved as AKAs). The mining dossier's known
# failure mode — sequence-inferred ids that catalog someone else —
# was screened for; the two known bad inferences (Schwarzschild,
# Woollcott Smith) were already corrected in the first wave.
#
# NO RACE IS SET on any new record. Birth years derived from ages at
# arrest enter at CIRCA precision. Catalog misspellings (Frankhauser,
# Gwendalyn, Edmon, Mitaritenna, Huddleson, Trumpower, Earnest,
# Hirshfeld, Maztkin, Svanoe, Leavarn-Dee, Janis, Doland, Eldredge,
# Ester, Rosenbert, Sedgewick, Joesph...) are preserved as AKAs or
# bio notes, never as the primary form.
#
# NAME COLLISIONS with unrelated records already in the database get
# distinguishing slugs: frank-johnson-freedom-rider (the 1917 Houston
# mutiny private keeps frank-johnson), charles-butler-freedom-rider
# (the SOA Watch activist keeps charles-butler), and
# william-baker-freedom-rider (the IWW prisoner keeps william-baker).
#
# PHOTOGRAPHS: provenance in database/data/photos/CREDITS-crdl-2.md.
# Every attached image is the individually captioned Mississippi
# State Sovereignty Commission identification photograph the catalog
# itself ties to the person, fetched from the MDAH large scan series
# (800px, roughly twice the resolution of the first wave's source),
# cropped to the frontal panel with the Jackson PD placard at
# 525x700, autocontrast. The attach loop only fills EMPTY photo
# slots. Slugs with no image yet report missing until a file is
# dropped in.
#
# The payload lives in database/data/fixes/crdl-freedom-riders-2.json.
#
# Idempotent: people matched by slug, case rows matched by arrest
# year+month, every field compared before writing.
#
# Run from the repo root:
#   bash database/data/fix-crdl-freedom-riders-2.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DST_DIR="storage/app/public/prisoners"
mkdir -p "$DST_DIR"

copied=0
for SRC in database/data/photos/crdl2/*.jpg; do
    [ -e "$SRC" ] || continue
    base="$(basename "$SRC")"
    cp -f "$SRC" "${DST_DIR}/${base}"
    copied=$((copied+1))
done
echo "copied ${copied} portrait(s) from database/data/photos/crdl2/"

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/crdl-freedom-riders-2.json")), true);

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

    echo str_pad($row["slug"], 34), ($isNew ? "CREATED" : "updated in place"),
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

$cohort = Prisoner::withoutGlobalScopes()->get()->filter(function ($x) {
    return str_contains((string) $x->description, "Civil Rights Digital Library");
});

echo "\ncreated: ", $created, "   updated in place: ", $updatedInPlace,
     "   CRDL-sourced records now: ", $cohort->count(), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
