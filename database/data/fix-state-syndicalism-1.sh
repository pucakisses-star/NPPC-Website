#!/usr/bin/env bash
#
# NINETEEN CURATOR CORRECTIONS -- mostly misclassified prosecutions.
#
# THE DOMINANT ERROR IS JURISDICTIONAL. Eleven of these records carried
# the boilerplate charge "Federal prosecution under the Espionage Act of
# 1917 and/or the Sedition Act of 1918" when the prosecution was a STATE
# criminal syndicalism case. That is not a nuance: it attributes the
# prosecution to the wrong sovereign, under the wrong statute, in the
# wrong decade in some cases. Corrected here:
#
#   Washington criminal syndicalism   elias-matson, w-r-holey,
#                                     frank-hestings
#   California criminal syndicalism   john-hiza, john-g-weiler,
#                                     mickey-j-dunn
#   Idaho criminal syndicalism        j-l-brian
#   Pennsylvania, underground
#     Communist organising            israel-blankenstein
#   Federal, but the WRONG federal
#     charge                          frank-geizler was prosecuted at
#                                     Fargo for threatening the
#                                     President, not under the Espionage
#                                     Act
#   Not a prosecution at all          pedro-gonzalez was the subject of
#                                     an attempted EXTRADITION
#
# THREE RENAMES, all slug-changing.
#
#   carol-swenson  -> Carl Swenson
#   pedro-gonzales -> Pedro González
#   tomas-sarabia  -> Tomás Sarabia Labrada
#
# TWO DEATH CLAIMS ARE WITHDRAWN OR CORRECTED.
#
#   dorothy-calhoun  The record said she "died in custody in Georgia" and
#                    her sentence field read "Died in a Georgia jail."
#                    SHE DID NOT DIE IN JAIL. She died in Atlanta and was
#                    dead by October 14, 1936. That police mistreatment
#                    caused her death is ANGELO HERNDON’S ALLEGATION and
#                    is now recorded as his allegation rather than as a
#                    finding. Her death year is stored at year precision
#                    because "by October 14" is a bound, not a date.
#
#                    SCOPE NOTE, flagged not decided: with the custodial
#                    death withdrawn, no imprisonment is documented for
#                    her at all. That is the third such record in recent
#                    batches, after Dr. Sarah Lockrey and Kat
#                    Abughazaleh.
#
#   carl-swenson     He DID die in custody, on September 18, 1919 in the
#                    Spokane County Jail while awaiting trial. The record
#                    gave September 19 and flagged him released. Setting
#                    death_in_custody_date makes the PrisonerCase hook
#                    mirror it onto release_date automatically, and the
#                    flags now read neither in custody nor released,
#                    which is the state batch 27 established for a death
#                    inside.
#
# ONE CLAIM OF TIME SERVED IS WITHDRAWN. lotta-burke was described as
# having "served fifteen months in the Missouri State Prison". Fifteen
# months was the SENTENCE; no admission or release has been found, she
# was on appeal bond throughout, and ON MAY 26, 1924 THE SUPREME COURT
# REVERSED ALL THIRTEEN CONVICTIONS in the case. Her only documented
# custody is three weeks in a Dayton jail in 1917.
#
# ONE DEATH SENTENCE IS EXPLICITLY NOT READ AS AN EXECUTION. o-g-brown,
# a Black youth sentenced to death in Mississippi for the alleged theft
# of $1.85, has no recorded outcome. The sentence year also moves from
# 1935 to 1934: the Labor Defender item is a January 1935 review of the
# preceding year.
#
# INSTITUTIONS ARE MATCHED TO THE ROWS ALREADY IN USE rather than
# created afresh, so this does not litter the table with near-duplicates.
# One institution is CORRECTED IN PLACE: San Quentin State Prison had its
# city recorded as Tamal, which is the site’s postal name. It is set to
# San Quentin, Marin County, California -- AND THAT AFFECTS EVERY
# PRISONER LINKED TO THAT ROW, which is 151 case rows. The correction is
# right for all of them.
#
# WHERE A DURATION IS CALCULATED RATHER THAN DOCUMENTED, THE TEXT SAYS
# SO. Geizler’s 588 days, Weiler’s two years three months and Dunn’s four
# years two months are all arithmetic on reported endpoints, and two of
# the three rest on month-only dates.
#
# THREE RELEASES ARE DELIBERATELY LEFT EMPTY where a source gives only a
# bound. Tomás Sarabia was "freed after five months" per a report of
# January 18, 1910 -- which establishes only that he was free BY then, so
# entering it would manufacture an elapsed 161 days no source documents.
# Guillermo Adán’s seventeen months apparently spans repeated arrests, so
# it is not converted into an admission date. Sam Kurland has no
# discharge on record at all.
#
# The payload is in database/data/fixes/state-syndicalism-corrections-1.json.
#
# Guarded and idempotent throughout.
#
# Run from the repo root:
#   bash database/data/fix-state-syndicalism-1.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/state-syndicalism-corrections-1.json")), true);

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
    [$y, $m, $d] = array_pad($spec, 3, null);
    $was = $model->{$field} ? $model->{$field}->format("Y-m-d") : null;
    $wasPrec = $model->datePrecisionFor($field);
    $model->setPartialDate($field, $y, $m, $d);

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
                  "release" => "release_date", "death_in_custody" => "death_in_custody_date"] as $key => $field) {
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
