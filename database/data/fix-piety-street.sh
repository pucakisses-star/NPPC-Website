#!/usr/bin/env bash
#
# THE PIETY STREET DEFENDANTS -- verified findings, seven portraits,
# and the two missing members of the roster.
#
# From the curator's verified findings on the New Orleans Panthers
# arrested September 15, 1970 after the police assault on the Piety
# Street headquarters, all acquitted August 6, 1971:
#
# CUSTODY. Every defendant gains the documented span: arrest and
# incarceration September 15, 1970, Orleans Parish Prison, acquitted
# August 6, 1971 — and, ON THE CURATOR-S STANDING INSTRUCTION, the
# acquittal date is ASSUMED AS THE RELEASE DATE unless the record
# implies otherwise. No separate discharge records are documented,
# and every sentence text states that the release is assumed from
# the acquittal. Two defendants ARE implied otherwise, and keep no
# release:
#
#   RONALD AILSWORTH must NOT have August 6 as a release: he faced
#   additional federal and New Haven matters, and his discharge from
#   this custody is unresolved. He also gains a second case row for
#   the much later, unrelated Angola imprisonment the dossier
#   documents: released September 26, 2019 after approximately forty
#   years, no admission date or underlying detail established, so
#   only the release is entered.
#
#   CHARLES RUDOLPH SCOTT was reportedly subject to the same federal
#   and New Haven holds, and a family account only places him back
#   in Queens by about mid-1972 — so his release too stays open.
#
#   MALIK RAHIM keeps the assumed August 6 release: "released during
#   1971" after about eleven months is consistent with the acquittal
#   date, and nothing implies a different one. His rearrest five
#   days after release stays in his texts.
#
# BIRTH YEARS from age at arrest, the Camden 28 method: birth year =
# 1970 minus the reported age, entered at CIRCA precision ("may be off
# by one"), because a person aged N in September 1970 was born in
# either 1970-N or 1969-N. Nobody gets an invented exact date. Exact
# dates only where documented: Rahim already holds 1947-12-17; ALTON
# EDWARDS gains his death, January 26, 2020, at seventy, anchored by
# the obituary that identifies his codefendant the Rev. Dr. Tyronne
# Edwards as his brother; CHARLES RUDOLPH SCOTT died July 21, 1999.
#
# TWO DEFENDANTS WERE MISSING FROM THE DATABASE and are created:
# CHARLES RUDOLPH SCOTT (slug charles-rudolph-scott — the plain
# charles-scott belongs to a different, California man, the
# james-johnson-everett rule) and CATHERINE BOURNES (also rendered
# Bourns and Bourne). Both enter with the cohort custody record and
# dispositions, and with what the dossier supports and no more.
#
# AKAS: Isaac Edwards gains "Isaac Edwards III" (the printed caption
# and dossier styling — the name field is not changed, which would
# break his URL for an enhancement rather than an error); Tyrone
# Edwards gains "Tyronne Edwards", his later styling; Alton Edwards
# gains "Sugar Ed"; Elaine Young gains "E-baby".
#
# LATER LIVES, stated only where verified: Ailsworth interviewed 2021;
# Tyronne Edwards is as of 2026 the Plaquemines Parish District 1
# representative; the 2007 Leah Hodges photograph is PROBABLE, NOT
# PROVED, and her record says so instead of asserting it.
#
# ------------------------------------------------------------------
# THE PORTRAITS -- seven attached, one stored as an alternate
# ------------------------------------------------------------------
#
# Provenance in database/data/photos/CREDITS-piety-street.md. All
# eight come from the curator-supplied composite sheet of period
# newspaper portraits, EACH CARRYING ITS OWN PRINTED CAPTION — the
# identification anchor. Attached: Isaac Edwards, Alton Edwards,
# William Cloud, Leroy Jones, Tyrone Edwards, Milton Martin, Elaine
# Young (the center figure of a captioned group photograph, per the
# caption fragment and the curator's own "(center)" label). NOT
# auto-attached: the 1970 Donald T. Guyton portrait, because the
# malik-rahim record already has a photograph and photos are not
# replaced without instruction — the file is stored as
# malik-rahim-1970.jpg for the curator to swap in if wanted.
#
# The attach loop below only fills EMPTY photo slots; it never
# replaces an existing portrait.
#
# The prose carries apostrophes, so the payload lives in
# database/data/fixes/piety-street-findings.json.
#
# Idempotent: people matched by slug, appends guarded by str_contains,
# every field compared, the Angola case matched by its release year,
# the new records matched by slug on re-runs.
#
# Run from the repo root:
#   bash database/data/fix-piety-street.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DST_DIR="storage/app/public/prisoners"
mkdir -p "$DST_DIR"

for slug in isaac-edwards alton-edwards william-cloud leroy-jones tyrone-edwards milton-martin elaine-young; do
    SRC="database/data/photos/${slug}.jpg"
    if [ -f "$SRC" ]; then
        cp -f "$SRC" "${DST_DIR}/${slug}.jpg"
        echo "copied ${slug}.jpg"
    else
        echo "no source image for ${slug} — skipped (see CREDITS-piety-street.md)"
    fi
done

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/piety-street-findings.json")), true);

if (! $payload || empty($payload["people"])) {
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

foreach ($payload["people"] as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->with("cases.institution")->first();

    if (! $p) {
        echo "\nNOT FOUND: ", $row["slug"], " — skipped\n";
        continue;
    }

    echo "\n", $row["slug"], "\n";

    $notes = [];

    if (! empty($row["aka"]) && $p->aka !== $row["aka"]) {
        $p->aka = $row["aka"];
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

    $rel = "prisoners/".$row["slug"].".jpg";
    if (is_file(storage_path("app/public/".$rel)) && ! $p->photo) {
        $p->photo = $rel;
        $notes[] = "photo attached";
    }

    if ($notes) {
        $p->save();
    }

    echo "  ", ($notes ? implode("; ", $notes) : "person unchanged"), "\n";

    // The 1970 case: the row already tied to Orleans Parish Prison, or
    // the oldest row.
    $case = $p->cases->first(function ($c) {
        return $c->institution && str_contains($c->institution->name, "Orleans Parish");
    }) ?? $p->cases->sortBy("created_at")->first();

    if (! $case) {
        echo "  no case row — skipped\n";
        continue;
    }

    $case->setRelation("prisoner", $p);

    $caseNotes = [];

    foreach (["arrest" => "arrest_date", "incarceration" => "incarceration_date", "release" => "release_date"] as $k => $field) {
        if (array_key_exists($k, $row) && $applyDate($case, $field, $row[$k])) {
            $caseNotes[] = $field."=".$case->{$field}->format("Y-m-d")." (".($case->datePrecisionFor($field) ?: "day").")";
        }
    }

    foreach (["convicted", "sentence"] as $field) {
        if (array_key_exists($field, $row) && $case->{$field} != $row[$field]) {
            $case->{$field} = $row[$field];
            $caseNotes[] = $field;
        }
    }

    if ($caseNotes) {
        $case->save();
    }

    echo "  case: ", ($caseNotes ? implode("; ", $caseNotes) : "unchanged"),
         "   days=", ($case->imprisoned_for_days === null ? "null" : $case->imprisoned_for_days), "\n";

    // Ailsworth: the separate Angola imprisonment, matched by release year.
    if (! empty($row["angola_case"])) {
        $spec = $row["angola_case"];
        $angola = $p->cases->first(function ($c) {
            return $c->release_date && (int) $c->release_date->format("Y") === 2019;
        });
        $isNew = ! $angola;

        if ($isNew) {
            $angola = new PrisonerCase;
            $angola->prisoner_id = $p->id;
        }

        $angola->setRelation("prisoner", $p);

        $aNotes = [];

        if ($applyDate($angola, "release_date", $spec["release"])) {
            $aNotes[] = "release_date=".$angola->release_date->format("Y-m-d");
        }

        foreach (["charges", "convicted", "sentence"] as $field) {
            if ($angola->{$field} != $spec[$field]) {
                $angola->{$field} = $spec[$field];
                $aNotes[] = $field;
            }
        }

        if (! $angola->institution_id) {
            $inst = Institution::firstOrCreate(
                ["name" => $spec["institution"]],
                ["city" => $spec["institution_city"], "state" => $spec["institution_state"]]
            );
            $angola->institution_id = $inst->id;
            $aNotes[] = "institution=".$inst->name;
        }

        if ($isNew || $aNotes) {
            $angola->save();
        }

        echo "  Angola case ", ($isNew ? "NEW  " : "     "), ($aNotes ? implode(", ", $aNotes) : "unchanged"), "\n";
    }
}

// ---- the two missing defendants ---------------------------------------

foreach ($payload["new"] as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->with("cases")->first();
    $isNew = ! $p;

    if ($isNew) {
        $p = new Prisoner;
        $p->slug = $row["slug"];
    }

    foreach (["name", "first_name", "last_name", "gender", "race", "era", "state", "description"] as $f) {
        if (array_key_exists($f, $row) && $p->{$f} !== $row[$f]) {
            $p->{$f} = $row[$f];
        }
    }

    if (! empty($row["middle_name"]) && $p->middle_name !== $row["middle_name"]) {
        $p->middle_name = $row["middle_name"];
    }

    if (! empty($row["aka"]) && $p->aka !== $row["aka"]) {
        $p->aka = $row["aka"];
    }

    foreach (["affiliation", "ideologies"] as $f) {
        if ($p->{$f} != $row[$f]) {
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
    $p->save();
    $p->load("cases");

    echo "\n", str_pad($p->slug, 24), ($isNew ? "CREATED" : "already exists — updated in place"), "\n";

    $spec = $row["case"];
    $case = $p->cases->first();
    $caseIsNew = ! $case;

    if ($caseIsNew) {
        $case = new PrisonerCase;
        $case->prisoner_id = $p->id;
    }

    $case->setRelation("prisoner", $p);

    $caseNotes = [];

    foreach (["arrest" => "arrest_date", "incarceration" => "incarceration_date"] as $k => $field) {
        if ($applyDate($case, $field, $spec[$k])) {
            $caseNotes[] = $field."=".$case->{$field}->format("Y-m-d");
        }
    }

    foreach (["charges", "convicted", "sentence"] as $field) {
        if ($case->{$field} != $spec[$field]) {
            $case->{$field} = $spec[$field];
            $caseNotes[] = $field;
        }
    }

    if (! $case->institution_id) {
        $inst = Institution::firstOrCreate(
            ["name" => $spec["institution"]],
            ["city" => $spec["institution_city"], "state" => $spec["institution_state"]]
        );
        $case->institution_id = $inst->id;
        $caseNotes[] = "institution=".$inst->name;
    }

    if ($caseIsNew || $caseNotes) {
        $case->save();
    }

    echo "  case ", ($caseIsNew ? "NEW  " : "     "), ($caseNotes ? implode(", ", $caseNotes) : "unchanged"), "\n";
}

$cohort = Prisoner::withoutGlobalScopes()->get()->filter(
    fn ($x) => in_array("National Committee to Combat Fascism", (array) $x->affiliation)
);
echo "\nNCCF cohort: ", $cohort->count(),
     "  with a birthdate: ", $cohort->filter(fn ($x) => $x->birthdate)->count(),
     "  with a photo: ", $cohort->filter(fn ($x) => $x->photo)->count(), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
