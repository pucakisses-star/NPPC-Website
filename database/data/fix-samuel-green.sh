#!/usr/bin/env bash
#
# REVEREND SAMUEL GREEN -- what he was actually tried for.
#
# THE RECORD HAD THE TWO TRIALS THE WRONG WAY ROUND. It said he was
# "arrested and tried -- and acquitted" for aiding the escape of the Dover
# Eight, and that slaveholders "then charged him again" over the book.
#
# HE WAS NEVER TRIED FOR AIDING THE DOVER EIGHT. State's Attorney Charles
# F. Goldsborough concluded there was insufficient evidence and never
# brought that charge. Both indictments were for possessing prohibited
# abolitionist material, and the acquittal was on the FIRST of those two:
# a letter from his son in Canada, a map of Canada, and railroad
# schedules. The judge ruled that documents useful for escape did not fall
# within that statute. He was then tried on the already-pending Uncle
# Tom-s Cabin indictment and convicted.
#
# The difference matters. As written, the record made him a man acquitted
# of helping people escape and then punished on a pretext. What actually
# happened is that the state could not prove the escape case at all, and
# reached instead for a literature law -- and even the first literature
# charge failed. Only the book stuck.
#
# TWO CASE ROWS, because there were two indictments with two outcomes. The
# acquitted row carries the April 4 arrest and NO custody; the custody
# belongs to the conviction. Nothing is double-counted.
#
# THE COUNTER RUNS FROM THE PENITENTIARY ADMISSION OF MAY 18, 1857, not
# from the April 4 arrest, and this deliberately departs from the field
# list supplied with the correction.
#
#   That list gives "Incarceration 1857-04-04". Pairing April 4 with the
#   April 21, 1862 release makes the day counter publish 1,843 days --
#   about five years and seventeen days -- which is exactly the figure the
#   same correction says should NOT be entered as exact without the
#   Dorchester County jail ledger. Following the field list would have
#   asserted the number its own author declined to assert.
#
#   Running from May 18 gives 1,799 days: four years, eleven months and
#   three days, which is the confirmed penitentiary time the correction
#   states. The April 4 arrest is still recorded, in arrest_date, where it
#   does not feed the counter.
#
# HE IS NOT RENAMED. The correction gives the canonical name as "Reverend
# Samuel Green", but Reverend is an honorific -- and one the correction
# itself qualifies, noting he was a licensed lay exhorter rather than an
# ordained minister. The record keeps Samuel Green and the slug is
# untouched; the honorific and the qualification are in the biography.
#
# ALSO RECORDED: born circa 1802 at East New Market, died February 28,
# 1877 at Baltimore; prisoner number 5146; the Maryland Penitentiary; the
# May 14, 1857 sentencing date; the statutory minimum of ten years under
# the 1841 law; and Governor Bradford-s conditional pardon of March 26,
# 1862 requiring him to leave Maryland within sixty days.
#
# Guarded and idempotent throughout.
#
# Run from the repo root:
#   bash database/data/fix-samuel-green.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/samuel-green.json")), true);

if (! $payload || empty($payload["records"])) {
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
    [$y, $m, $d, $approx] = array_pad($spec, 4, null);
    $was = $model->{$field} ? $model->{$field}->format("Y-m-d") : null;
    $wasPrec = $model->datePrecisionFor($field);
    $model->setPartialDate($field, $y, $m, $d, (bool) $approx);

    return $was !== ($model->{$field} ? $model->{$field}->format("Y-m-d") : null)
        || $wasPrec !== $model->datePrecisionFor($field);
};

$people = 0;
$casesTouched = 0;
$instFixed = 0;
$missing = 0;

foreach ($payload["records"] as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->with("cases")->first();

    if (! $p && isset($row["name"])) {
        $p = Prisoner::withoutGlobalScopes()->where("name", $row["name"])->with("cases")->first();
    }

    if (! $p) {
        echo "  NOT FOUND: ", $row["slug"], "\n";
        $missing++;
        continue;
    }

    $notes = [];

    foreach (["name", "first_name", "middle_name", "last_name", "aka", "description", "state", "inmate_number", "ideologies", "gender"] as $field) {
        if (array_key_exists($field, $row) && $p->{$field} != $row[$field]) {
            $p->{$field} = $row[$field];
            $notes[] = $field;
        }
    }

    foreach (["in_custody", "released"] as $field) {
        if (array_key_exists($field, $row) && (bool) $p->{$field} !== $row[$field]) {
            $p->{$field} = $row[$field];
            $notes[] = $field."=".($row[$field] ? "yes" : "no");
        }
    }

    foreach (["birthdate", "death_date"] as $field) {
        if (array_key_exists($field, $row) && $applyDate($p, $field, $row[$field])) {
            $notes[] = $field." ".$p->formatPartialDate($field);
        }
    }

    if ($notes) {
        $p->save();
        $people++;
    }

    echo "  ", str_pad($p->slug, 22), " ", ($notes ? implode("; ", $notes) : "already correct"), "\n";

    if (empty($row["cases"])) {
        continue;
    }

    $existing = $p->cases->sortBy("created_at")->values();

    foreach ($row["cases"] as $i => $spec) {
        $case = $existing->get($i);
        $isNew = false;

        if (! $case) {
            $case = new PrisonerCase;
            $case->prisoner_id = $p->id;
            $case->charges = $existing->first() ? $existing->first()->charges : null;
            $isNew = true;
        }

        $case->setRelation("prisoner", $p);
        $caseNotes = [];

        foreach (["arrest" => "arrest_date", "incarceration" => "incarceration_date",
                  "release" => "release_date", "death_in_custody" => "death_in_custody_date",
                  "sentenced" => "sentenced_date"] as $key => $field) {
            if (array_key_exists($key, $spec) && $applyDate($case, $field, $spec[$key])) {
                $caseNotes[] = $field."=".($case->{$field} ? $case->{$field}->format("Y-m-d") : "null");
            }
        }

        foreach (["charges", "sentence", "convicted", "judge"] as $field) {
            if (array_key_exists($field, $spec) && $case->{$field} !== $spec[$field]) {
                $case->{$field} = $spec[$field];
                $caseNotes[] = $field;
            }
        }

        if (! empty($spec["institution"])) {
            $want = $spec["institution"];
            // Match the row already in use by name so the table does not
            // fill up with near-duplicates, then correct its city/state in
            // place. That correction reaches every prisoner linked to it.
            $inst = Institution::firstOrNew(["name" => $want["name"]]);
            $before = $inst->exists ? ($inst->city." / ".$inst->state) : "(new)";
            $dirty = ! $inst->exists;

            foreach (["city", "state"] as $f) {
                if (! empty($want[$f]) && $inst->{$f} !== $want[$f]) {
                    $inst->{$f} = $want[$f];
                    $dirty = true;
                }
            }

            if ($dirty) {
                $inst->save();
                $instFixed++;
                echo "        institution ", $inst->name, ": ", $before, " -> ", $inst->city, " / ", $inst->state, "\n";
            }

            if ($case->institution_id !== $inst->id) {
                $case->institution_id = $inst->id;
                $caseNotes[] = "institution";
            }
        }

        if ($isNew || $caseNotes) {
            $case->save();
            $casesTouched++;
        }

        echo "      case#", $i, ($isNew ? " NEW  " : "      "),
             ($caseNotes ? implode(", ", $caseNotes) : "unchanged"),
             "   days=", ($case->imprisoned_for_days === null ? "null" : $case->imprisoned_for_days), "\n";
    }
}

echo "\nPeople updated:        {$people}\n";
echo "Case rows written:     {$casesTouched}\n";
echo "Institutions repaired: {$instFixed}\n";
echo "Slugs not found:       {$missing}\n";

$fed = Prisoner::withoutGlobalScopes()->whereHas("cases", fn ($q) => $q->where("charges", "like", "%Espionage Act of 1917 and/or the Sedition Act%"))->count();
echo "Records still carrying the generic federal Espionage/Sedition charge: {$fed}\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
