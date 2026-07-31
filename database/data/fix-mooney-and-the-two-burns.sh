#!/usr/bin/env bash
#
# THOMAS MOONEY'S TRIPLE CASE ROW, AND THE TWO ED BURNSES.
#
# Both were flagged in batch 47 and left for their own pass. This is it.
#
# ------------------------------------------------------------------
# THOMAS MOONEY
# ------------------------------------------------------------------
#
# One imprisonment, THREE case rows: the same off-by-one double import
# found on Warren Billings (arrest July 25/26, incarceration February
# 23/24 1917, release January 6/7 1939), plus a third row with no dates
# at all. The first row is kept, the other two are deleted, and the
# survivor is corrected:
#
#   ARREST AND CUSTODY GO TO MONTH PRECISION, July 1916. The two rows
#   said the 25th and the 26th; the standard account says he was taken
#   on the 26th or 27th, in the days after the July 22 bombing. Rather
#   than pick a day on no evidence, the month is asserted and the
#   uncertainty is stated in the sentence text. This also fixes a
#   subtler error: both dated rows recorded his INCARCERATION as
#   February 1917 — the sentencing — although he was in custody without
#   bail from his arrest. The day counter previously started seven
#   months late.
#
#   SENTENCED February 24, 1917 goes into the sentenced_date field.
#   Release stays January 7, 1939 — the day of Governor Olson's
#   unconditional pardon, which the record's own text already named
#   while one row said January 6.
#
#   THE INSTITUTION MOVES OFF "San Quentin Rehabilitation Center" —
#   the prison's name since 2023 — and onto San Quentin State Prison,
#   which is both the historical name and the name every other record
#   of that era uses. Same correction direction as the Tamal fix on
#   J. J. Johnson.
#
# ------------------------------------------------------------------
# THE TWO ED BURNSES
# ------------------------------------------------------------------
#
# They are different men seventeen months apart, and the database had
# one broken record and one missing one.
#
# ed-burns — the SACRAMENTO defendant — died in the Sacramento County
# Jail in November 1918, one of the five Sacramento IWW defendants who
# died in the county jail before their Espionage Act case came to trial
# in January 1919. His record carried an ARREST DATE OF FEBRUARY 1,
# 1924, five years after his own death. That date is removed as
# impossible and nothing replaces it: his actual arrest date is not
# documented, and the rule since batch 27 is that an unknown is stored
# as an unknown. What he gains instead:
#
#   - death_date November 1918, month precision — the record had NO
#     death date at all for a man its own biography says died in jail
#   - death_in_custody_date on the case, which the model mirrors onto
#     the release date, so the year counter now ends at his death
#     instead of running to the present
#   - in_custody false, released FALSE — the batch 27 convention for a
#     man who died inside
#   - the Sacramento County Jail as his institution
#   - a biography that says which Ed Burns he is
#
# ed-burns-seattle — NEW RECORD — is the Ed Burns of the April 22, 1920
# Globe Building raid: W. E. Spear's assistant, arrested with Spear and
# Alicia Rosenbaum when the Red Squad seized the office and its
# twelve-thousand-name membership directory. Batch 47 created Spear and
# Rosenbaum and could only name Burns in their texts; this completes
# the trio. Same evidentiary limits as his co-defendants: held
# incommunicado on open charges, no release date entered because the
# custody span is undocumented, disposition unresolved — Spear's
# dismissal of January 24, 1921 is his co-defendant's outcome, not
# proof of his.
#
#   THE SLUG IS ed-burns-seattle for the same reason batch 47 used
#   james-johnson-everett: the plain slug belongs to the other man, and
#   prisoner slugs have no redirect map, so renaming the dead man to
#   free it would break his URL.
#
# NOTHING IS DELETED except Mooney's two surplus case rows. No photos
# are touched; none of the three records has one.
#
# The prose carries apostrophes, so the payload lives in
# database/data/fixes/mooney-and-the-two-burns.json.
#
# Idempotent: the Mooney dedupe keeps the oldest row and deletes the
# rest, so a second run finds one row and deletes nothing; every field
# is compared before writing; the new record is matched by slug.
#
# Run from the repo root:
#   bash database/data/fix-mooney-and-the-two-burns.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/mooney-and-the-two-burns.json")), true);

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

    [$y, $m, $d] = array_pad($spec, 3, null);
    $wasDate = $model->{$field} ? $model->{$field}->format("Y-m-d") : null;
    $wasPrec = $model->datePrecisionFor($field);
    $model->setPartialDate($field, $y, $m, $d);

    return $wasDate !== ($model->{$field} ? $model->{$field}->format("Y-m-d") : null)
        || $wasPrec !== $model->datePrecisionFor($field);
};

// ---- Mooney: one imprisonment, one row --------------------------------

$m = $payload["mooney"];
$p = Prisoner::withoutGlobalScopes()->where("slug", $m["slug"])->with("cases")->first();

if (! $p) {
    echo "NOT FOUND: ", $m["slug"], "\n";
} else {
    echo $p->slug, "\n";

    $rows = $p->cases->sortBy("created_at")->values();

    foreach ($rows->slice(1) as $dupe) {
        $dupe->delete();
        echo "  removed a surplus case row (",
             ($dupe->arrest_date ? $dupe->arrest_date->format("Y-m-d") : "no dates"), ")\n";
    }

    $case = $rows->first();

    if (! $case) {
        echo "  no case row at all — creating one\n";
        $case = new PrisonerCase;
        $case->prisoner_id = $p->id;
    }

    $case->setRelation("prisoner", $p);

    $notes = [];
    $spec = $m["case"];

    foreach (["arrest" => "arrest_date", "incarceration" => "incarceration_date", "sentenced" => "sentenced_date", "release" => "release_date"] as $k => $field) {
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

    $inst = Institution::firstOrCreate(["name" => $spec["institution"]], ["state" => "California"]);
    if ($case->institution_id !== $inst->id) {
        $was = $case->institution?->name;
        $case->institution_id = $inst->id;
        $notes[] = "institution=".$inst->name.($was ? " (was ".$was.")" : "");
    }

    if ($notes) {
        $case->save();
    }

    echo "  ", ($notes ? implode("; ", $notes) : "case already correct"), "\n";
    $p->refresh();
    echo "  now ", $p->cases()->count(), " case row, days=",
         ($p->cases()->first()->imprisoned_for_days ?? "null"), "\n";
}

// ---- the Sacramento Ed Burns ------------------------------------------

$b = $payload["burns_fix"];
$p = Prisoner::withoutGlobalScopes()->where("slug", $b["slug"])->with("cases")->first();

if (! $p) {
    echo "\nNOT FOUND: ", $b["slug"], "\n";
} else {
    echo "\n", $p->slug, "\n";

    $notes = [];

    if ($applyDate($p, "death_date", $b["death_date"])) {
        $notes[] = "death_date=".$p->death_date->format("Y-m-d")." (".($p->datePrecisionFor("death_date") ?: "day").")";
    }

    if ($p->description !== $b["description"]) {
        $p->description = $b["description"];
        $notes[] = "description";
    }

    if ($p->in_custody || $p->released) {
        $p->in_custody = false;
        $p->released = false;
        $notes[] = "flags set to died-in-custody (both false)";
    }

    if ($notes) {
        $p->save();
    }

    echo "  ", ($notes ? implode("; ", $notes) : "person already correct"), "\n";

    $case = $p->cases->sortBy("created_at")->first();

    if ($case) {
        $case->setRelation("prisoner", $p);
        $caseNotes = [];
        $spec = $b["case"];

        if ($case->arrest_date) {
            $caseNotes[] = "removed impossible arrest ".$case->arrest_date->format("Y-m-d");
            $case->setPartialDate("arrest_date", null);
        }

        if (array_key_exists("death_in_custody", $spec) && $applyDate($case, "death_in_custody_date", $spec["death_in_custody"])) {
            $caseNotes[] = "death_in_custody_date=".$case->death_in_custody_date->format("Y-m-d");
        }

        foreach (["charges", "convicted", "sentence"] as $field) {
            if (array_key_exists($field, $spec) && $case->{$field} != $spec[$field]) {
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

        if ($caseNotes) {
            $case->save();
        }

        echo "  ", ($caseNotes ? implode("; ", $caseNotes) : "case already correct"), "\n";
        echo "  release now mirrors his death: ",
             ($case->release_date ? $case->release_date->format("Y-m-d") : "null"), "\n";
    }
}

// ---- the Seattle Ed Burns ---------------------------------------------

$n = $payload["burns_new"];
$p = Prisoner::withoutGlobalScopes()->where("slug", $n["slug"])->with("cases")->first();
$isNew = ! $p;

if ($isNew) {
    $p = new Prisoner;
    $p->slug = $n["slug"];
}

foreach (["name", "first_name", "last_name", "gender", "era", "state", "description"] as $f) {
    if ($p->{$f} !== $n[$f]) {
        $p->{$f} = $n[$f];
    }
}

foreach (["affiliation", "ideologies"] as $f) {
    if ($p->{$f} != $n[$f]) {
        $p->{$f} = $n[$f];
    }
}

$p->in_custody = false;
$p->released = true;
$p->save();
$p->load("cases");

echo "\n", $p->slug, "  ", ($isNew ? "CREATED" : "already exists — updated in place"), "\n";

$spec = $n["cases"][0];
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

if ($caseIsNew || $caseNotes) {
    $case->save();
}

echo "  case ", ($caseIsNew ? "NEW  " : "     "), ($caseNotes ? implode(", ", $caseNotes) : "unchanged"), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
