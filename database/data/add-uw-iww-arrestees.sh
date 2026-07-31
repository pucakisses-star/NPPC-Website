#!/usr/bin/env bash
#
# THE UNIVERSITY OF WASHINGTON IWW LIST -- twelve new records, two
# existing records corrected, and one name that must NOT become a record.
#
# The curator compared the database against the University of
# Washington's IWW persecution chronology and supplied fourteen names.
# They resolve as follows:
#
#   TWELVE NEW PEOPLE:  Max Dezettel, Jennie La Zar, Walker C. Smith,
#   Peter Lynch, James Cronin, James Hayes, W. E. Spear, Alicia
#   Rosenbaum, James Schmidt, James Johnson (Everett), George Bradley,
#   Louis Lavine.
#
#   "ALICE ROSE" IS NOT A PERSON. Contemporary reporting identifies her
#   as Alicia Rosenbaum, alias Alice Rose, the twenty-one-year-old
#   stenographer of the Seattle Globe Building office; a 1924 IWW notice
#   adds the alias Alice Lloyd. She enters as ALICIA ROSENBAUM with both
#   aliases, and no Alice Rose record is created.
#
#   WARREN BILLINGS ALREADY EXISTS and is corrected, not created.
#   HARVEY O'CONNOR ALREADY EXISTS and gains his 1919 case.
#
# ------------------------------------------------------------------
# THE TWO EXISTING RECORDS
# ------------------------------------------------------------------
#
# warren-billings had THE SAME IMPRISONMENT IMPORTED TWICE — two case
# rows off by one day at every date (arrest July 25/26, incarceration
# September 22/23, release October 15/16, 1939). The same off-by-one
# double import sits on thomas-mooney, which is NOT touched here beyond
# being flagged: Mooney has THREE rows for one imprisonment and deserves
# his own pass.
#
#   The duplicate row is removed and the surviving case corrected from
#   the dossier: arrested July 26, 1916; sentenced October 7, 1916; LIFE
#   FOR SECOND-DEGREE MURDER — Mooney, not Billings, drew the
#   first-degree conviction and the death sentence. Because the two
#   imported rows disagreed on the release day, the October 1939 release
#   goes in at MONTH precision rather than picking a day on no evidence.
#
#   THE DELETED ROW ALSO TOOK AN ERROR WITH IT: its sentence text said
#   he was pardoned by Governor Earl Warren. His 1961 full pardon came
#   from Governor Edmund G. Brown — Earl Warren was Chief Justice of the
#   United States by then.
#
#   His death date moves from August 5 to SEPTEMBER 4, 1972.
#
# harvey-oconnor carried only the October 1953 contempt-of-Congress
# indictment for refusing Senator McCarthy's questions. He gains his
# vital dates (March 29, 1897 to August 29, 1987), a biography covering
# both episodes, and a second case: arrested in the February 1919
# Seattle General Strike roundup of radical publishers, indicted under
# Washington's criminal-anarchy law, NEVER TRIED — prosecutors abandoned
# the case after the state lost its test prosecution.
#
# ------------------------------------------------------------------
# EVIDENTIARY RULES APPLIED THROUGHOUT
# ------------------------------------------------------------------
#
# The UW chronology's own warning is honoured: its dates are sometimes
# newspaper publication dates, and its descriptions are contemporary
# reports never independently verified. So:
#
#   - DEZETTEL'S FIFTY DAYS IS NOT RECORDED AS TIME SERVED. The reports
#     disagree — fifty days imposed, released after about four. The
#     sentence text carries both; the day counter carries neither.
#   - LAVINE'S "DUE FOR RELEASE JUNE 26, 1917" IS NOT A RELEASE. A
#     projected date is not a documented one; it lives in the text only.
#     Same rule as Sarah Lockrey's projected term in batch 30.
#   - SPEAR'S DISMISSAL (January 24, 1921) IS NOT A RELEASE EITHER. The
#     bail record is silent, so his actual custody span is unknown and
#     no release date is entered.
#   - JOHNSON'S "58 DAYS" IS KEPT AS REPORTED, NOT AS ARITHMETIC. The
#     calendar interval of his dates is 55 days; Walker C. Smith's
#     contemporary count says 58. The counter computes from the dates;
#     the text preserves the reported figure and the discrepancy.
#   - NO VITAL DATES for anyone but O'Connor and Billings. Peter Lynch,
#     James Cronin, James Hayes, James Johnson and George Bradley are
#     common names; attaching census or death records to them without a
#     stronger identifier is how the Jacob Riis error happened.
#   - JAMES JOHNSON'S SLUG IS james-johnson-everett, on purpose. The
#     database already holds a 2000s James Johnson, a James Johnson Jr.
#     and the Los Angeles J. J. Johnson; the bare slug is left unclaimed
#     rather than seeding the next duplicate audit.
#
# MINOR-CASE FLAGS go to Dezettel (about four days), La Zar (brief
# questioning) and Lynch (brief hold ended by his own kidnapping) —
# following the batch 42 rule that the flag measures duration, not what
# was done to the person. What was done to Lynch was that ranchers took
# the jail keys at midnight, drove him into the desert, beat him and
# robbed him.
#
# FLAGGED, NOT CHANGED: the existing ed-burns record — the Sacramento
# defendant who DIED IN CUSTODY in November 1918 — carries an arrest
# date of February 1, 1924, more than five years after his own death.
# He is also a different man from the Ed Burns of the 1920 Globe
# Building raid, who has no record and is named only in the Spear and
# Rosenbaum texts here.
#
# NO PHOTOGRAPHS. Warren Billings has a verified Bancroft Library
# portrait available through Calisphere, and already carries an image;
# nobody else in this batch has a securely identified portrait, and
# nothing is attached on a name match alone.
#
# PLACEMENT TAIL: four institutions may be created — Boise City Jail,
# Calipatria Town Jail, Gamboa Stockade, Everett Jail.
#
# Idempotent: people are matched by slug with firstOrNew, cases are
# matched by year so a second run updates rather than duplicates, and
# every field is compared before writing.
#
# The prose carries apostrophes, so it lives in
# database/data/fixes/uw-iww-arrestees.json.
#
# Run from the repo root:
#   bash database/data/add-uw-iww-arrestees.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/uw-iww-arrestees.json")), true);

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

    [$y, $m, $d] = array_pad($spec, 3, null);
    $wasDate = $model->{$field} ? $model->{$field}->format("Y-m-d") : null;
    $wasPrec = $model->datePrecisionFor($field);
    $model->setPartialDate($field, $y, $m, $d);

    return $wasDate !== ($model->{$field} ? $model->{$field}->format("Y-m-d") : null)
        || $wasPrec !== $model->datePrecisionFor($field);
};

$applyCase = function (Prisoner $p, array $spec) use ($applyDate): void {
    $year = null;
    foreach (["arrest", "incarceration", "release"] as $k) {
        if (! empty($spec[$k][0])) {
            $year = $spec[$k][0];
            break;
        }
    }

    $case = $p->cases->first(function ($c) use ($year) {
        foreach (["arrest_date", "incarceration_date", "release_date"] as $f) {
            if ($c->{$f} && (int) $c->{$f}->format("Y") === $year) {
                return true;
            }
        }

        return false;
    });

    $isNew = false;

    if (! $case) {
        $case = new PrisonerCase;
        $case->prisoner_id = $p->id;
        $isNew = true;
    }

    $case->setRelation("prisoner", $p);

    $notes = [];

    foreach (["arrest" => "arrest_date", "incarceration" => "incarceration_date", "release" => "release_date", "sentenced" => "sentenced_date"] as $k => $field) {
        if (array_key_exists($k, $spec) && $applyDate($case, $field, $spec[$k])) {
            $notes[] = $field."=".($case->{$field} ? $case->{$field}->format("Y-m-d") : "null");
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

    echo "      case ", ($isNew ? "NEW  " : "     "),
         ($notes ? implode(", ", $notes) : "unchanged"),
         "   days=", ($case->imprisoned_for_days === null ? "null" : $case->imprisoned_for_days), "\n";
};

$created = 0;
$existed = 0;

foreach ($payload["new"] as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->with("cases")->first();
    $isNew = ! $p;

    if ($isNew) {
        $p = new Prisoner;
        $p->slug = $row["slug"];
        $created++;
    } else {
        $existed++;
    }

    foreach (["name", "first_name", "middle_name", "last_name", "aka", "gender", "era", "state", "description"] as $f) {
        if (array_key_exists($f, $row) && $p->{$f} !== $row[$f]) {
            $p->{$f} = $row[$f];
        }
    }

    foreach (["affiliation", "ideologies"] as $f) {
        if (array_key_exists($f, $row) && $p->{$f} != $row[$f]) {
            $p->{$f} = $row[$f];
        }
    }

    $p->in_custody = false;
    $p->released = true;
    $p->minor_case = ! empty($row["minor_case"]);
    $p->save();
    $p->load("cases");

    echo "\n  ", str_pad($p->slug, 26), ($isNew ? "CREATED" : "already exists — updated in place"), "\n";

    foreach (($row["cases"] ?? []) as $spec) {
        $applyCase($p, $spec);
    }
}

// ---- the two existing records ------------------------------------------

echo "\nCorrections to existing records:\n";

$b = $payload["updates"]["billings"];
$p = Prisoner::withoutGlobalScopes()->where("slug", $b["slug"])->with("cases")->first();

if (! $p) {
    echo "  NOT FOUND: ", $b["slug"], "\n";
} else {
    echo "\n  ", $p->slug, "\n";

    if ($applyDate($p, "death_date", $b["death_date"])) {
        $p->save();
        echo "      death_date corrected to ", $p->death_date->format("Y-m-d"), "\n";
    }

    // Remove the off-by-one duplicate row: same year, later created_at.
    $rows = $p->cases
        ->filter(fn ($c) => $c->incarceration_date && (int) $c->incarceration_date->format("Y") === $b["keep_case_year"])
        ->sortBy("created_at")
        ->values();

    if ($rows->count() > 1) {
        foreach ($rows->slice(1) as $dupe) {
            $dupe->delete();
            echo "      removed the off-by-one duplicate case row\n";
        }
        $p->load("cases");
    }

    $applyCase($p, $b["case"]);
}

$o = $payload["updates"]["oconnor"];
$p = Prisoner::withoutGlobalScopes()->where("slug", $o["slug"])->with("cases")->first();

if (! $p) {
    echo "  NOT FOUND: ", $o["slug"], "\n";
} else {
    echo "\n  ", $p->slug, "\n";

    $notes = [];
    foreach (["birthdate", "death_date"] as $f) {
        if ($applyDate($p, $f, $o[$f])) {
            $notes[] = $f."=".$p->{$f}->format("Y-m-d");
        }
    }
    if ($p->description !== $o["description"]) {
        $p->description = $o["description"];
        $notes[] = "description";
    }
    if ($notes) {
        $p->save();
    }
    echo "      ", ($notes ? implode("; ", $notes) : "already correct"), "\n";

    $p->load("cases");
    $applyCase($p, $o["new_case"]);
}

echo "\nCreated: {$created}   Already existed: {$existed}\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
