#!/usr/bin/env bash
#
# CHARLES H. MOYER AND GEORGE ARTHUR PETTIBONE -- two duplicate pairs
# merged, and the Moyer record corrected from a curator dossier.
#
# TWO PEOPLE WERE IN THE DATABASE TWICE. Each pair is one thin record with
# no photo and one fuller record with a photo, and they came in together:
#
#   charles-h-moyer    -> merged into charles-moyer,    then deleted
#   george-a-pettibone -> merged into george-pettibone, then deleted
#
# The record with the PHOTO survives in both cases. That also keeps the
# live /prisoner/charles-moyer and /prisoner/george-pettibone URLs, which
# matters because prisoner slugs have no redirect map -- SiteController
# has one for articles, but nothing equivalent for people, so a renamed
# prisoner slug simply 404s.
#
# THE INITIAL AND THE MIDDLE NAME GO IN AS middle_name, NOT INTO name.
# Prisoner::updating() only regenerates the slug when `name` is dirty, so
# setting middle_name leaves the slug alone. That is the same route taken
# for Dr. Otis W. Smith in batch 38, and it is what makes it possible to
# have the H. and the Arthur without moving either page.
#
#   charles-moyer     middle_name = "H."
#   george-pettibone  middle_name = "Arthur"
#
# NO PHOTOGRAPH IS TOUCHED, on instruction. Both surviving records keep
# the images they already have and neither deleted record had one.
#
#   Recorded because it was checked and should not have to be checked
#   again: a candidate portrait for Moyer, a printed halftone captioned
#   CHARLES H. MOYER, shows a young clean-shaven man. The Library of
#   Congress photograph of the three defendants outside the Boise
#   sheriff-s office (item 2004677537, "Bill Haywood - Charles Moyer -
#   George Pettibone", by Myers of Boise, 1907) shows a moustached man
#   matching the picture already on this record. The candidate was NOT
#   used. If it is ever revisited, that LOC plate is the reference.
#
# ------------------------------------------------------------------
# MOYER -- what the dossier changes
# ------------------------------------------------------------------
#
# BIRTHDATE DROPS TO YEAR PRECISION. The record carried 1866-07-04. The
# dossier is explicit that no exact month and day has been reliably
# established, so a Fourth of July birthday for a labour leader is a
# suspiciously good story that nothing supports. It becomes 1866.
#
# THE 1904 COLORADO CASE IS NEW ON THIS RECORD and is the reason the
# duplicate mattered: charles-moyer had only the Idaho case, so a man
# jailed twice showed one imprisonment.
#
#   MARCH 30 WAS NOT HIS ARREST. The dossier says so in as many words.
#   He was arrested at Ouray on MARCH 26, 1904 on a charge of desecrating
#   the flag, released on a reported $500 bond, and only then taken back
#   into custody by the militia. March 30 is where the military
#   imprisonment later litigated in Moyer v. Peabody begins. The case row
#   now carries arrest 1904-03-26 and incarceration 1904-03-30, which is
#   the distinction the dossier asks for and which the day counter
#   reflects: 77 days from March 30 to June 15.
#
#   The poster he was charged over is the one in the Denverite article
#   added in batch 41 -- "Is Colorado in America?", the flag whose
#   stripes list the rights suspended in the mining districts, with
#   Henry Maki chained to a telegraph pole. The article and this record
#   now describe the same object from the two ends.
#
# THE IDAHO CASE MOVES AT BOTH ENDS.
#
#   Arrest and custody 1906-02-16 -> 1906-02-17. The dossier puts the
#   Denver arrest on the late evening of February 17. He reached Idaho
#   about February 20, but custody was continuous from the arrest, so
#   February 17 is the start; the Ada County arrival is left in the prose
#   rather than splitting the row.
#
#   Release 1908-03-09 -> 1908-01-04. THIS IS A REAL DISAGREEMENT AND IT
#   IS BEING RESOLVED, NOT PAPERED OVER. The stored date and the old
#   biography said charges were dismissed in March 1908. The dossier says
#   Moyer was released on the afternoon of January 4, 1908, immediately
#   after Pettibone-s acquittal, when prosecutors asked the court to
#   dismiss. It gives the mechanism and the sequence, and the deleted
#   duplicate independently carried January 1908. The counter goes from
#   752 days to 686, which is the figure the dossier states.
#
# RACE = WHITE is carried across from the deleted duplicate, which held
# it and the surviving record did not.
#
# AFFILIATION adds "Western Federation of Miners", WHICH IS A NEW TERM.
# There are 171 affiliations in the table and no Federation among them,
# which is an odd gap for a database holding its president, its
# secretary-treasurer and a board member. The other two terms are the
# existing canonical spellings, "Industrial Workers of the World (IWW)"
# and "Socialist Party of America". Haywood and Pettibone are not given
# the new term here; that is a taxonomy pass, not this fix.
#
# ------------------------------------------------------------------
# PETTIBONE
# ------------------------------------------------------------------
#
# middle_name "Arthur", birthdate June 15, 1862, death August 3, 1908 at
# Denver. The death date was already right; the birthdate is new, and it
# makes the age column read 46, which matches the age given at death.
#
#   FLAGGED, NOT CHANGED: the deleted george-a-pettibone put his
#   acquittal on 1908-01-04 and the surviving record puts his release on
#   1908-01-03. Deleting the duplicate destroys that second reading, so
#   it is written down here. The Moyer dossier has Pettibone acquitted
#   and Moyer dismissed on the same afternoon of January 4, which favours
#   the 4th -- but the correction supplied covered his name and vital
#   dates, not his release, so the stored date is left alone rather than
#   changed on inference.
#
# ------------------------------------------------------------------
# UNRELATED, CARRIED IN THE SAME RUN
# ------------------------------------------------------------------
#
# charles-crowley LOSES HIS PHOTOGRAPH. It is a coarse newspaper halftone
# captioned "Charlie Crowley." and credited WIDE WORLD PHOTOS. The screen
# is so open that the face is barely legible at any size, and nothing
# ties a Wide World wire photo of a "Charlie Crowley" to the IWW member
# from Portola who served three years in San Quentin from October 29,
# 1923.
#
#   THIS IS NOT THE OTHER CROWLEY. charles-c-crowley is a different man
#   entirely — the San Francisco private detective hired by the German
#   consulate, tried in the Hindu-German Conspiracy case — and he keeps
#   his own photograph. The two records are easy to confuse and were
#   checked against each other before anything was cleared.
#
#   The image file stays on disk. Only the column is nulled, so putting
#   it back is a one-line change if the identification is ever made.
#
# NOTHING IS DELETED BEFORE IT IS MERGED. Each pair is checked, the
# survivor is written first, and the duplicate is removed only after.
# Case rows cascade on delete, so the duplicates take their own rows with
# them; that is why the 1904 case is written onto the survivor rather
# than repointed.
#
# The prose carries apostrophes and quotation marks, so it lives in
# database/data/fixes/moyer-pettibone-merge.json.
#
# Guarded and idempotent: every field is compared before writing, cases
# are matched by year rather than by position so a second run updates
# instead of duplicating, and a missing slug is reported rather than
# assumed.
#
# Run from the repo root:
#   bash database/data/merge-moyer-pettibone.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/moyer-pettibone-merge.json")), true);

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

// ---- the two survivors -------------------------------------------------

foreach (["keep", "pettibone"] as $key) {
    $row = $payload[$key] ?? null;
    if (! $row) {
        continue;
    }

    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->with("cases")->first();

    if (! $p) {
        echo "  NOT FOUND: ", $row["slug"], " — skipped\n";
        continue;
    }

    echo "\n", $row["slug"], "\n";

    $notes = [];

    foreach (["middle_name", "race", "description"] as $field) {
        if (array_key_exists($field, $row) && $p->{$field} !== $row[$field]) {
            $p->{$field} = $row[$field];
            $notes[] = $field;
        }
    }

    if (array_key_exists("affiliation", $row) && $p->affiliation != $row["affiliation"]) {
        $p->affiliation = $row["affiliation"];
        $notes[] = "affiliation";
    }

    foreach (["birthdate", "death_date"] as $field) {
        if (array_key_exists($field, $row) && $applyDate($p, $field, $row[$field])) {
            $notes[] = $field."=".($p->{$field} ? $p->{$field}->format("Y-m-d") : "null")
                       ." (".($p->datePrecisionFor($field) ?: "day").")";
        }
    }

    if ($notes) {
        $p->save();
        $p->refresh();
    }

    echo "    ", ($notes ? implode("; ", $notes) : "already correct"), "\n";
    echo "    name now: ", trim($p->first_name." ".$p->middle_name." ".$p->last_name),
         "   slug: ", $p->slug, "   age: ", ($p->age === null ? "null" : $p->age), "\n";

    // ---- cases, matched on the year rather than on position -----------

    foreach (($row["cases"] ?? []) as $spec) {
        $year = $spec["match_year"];

        $case = $p->cases->first(function ($c) use ($year) {
            foreach (["incarceration_date", "arrest_date", "release_date"] as $f) {
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

        $caseNotes = [];

        foreach (["arrest" => "arrest_date", "incarceration" => "incarceration_date", "release" => "release_date"] as $k => $field) {
            if (array_key_exists($k, $spec) && $applyDate($case, $field, $spec[$k])) {
                $caseNotes[] = $field."=".($case->{$field} ? $case->{$field}->format("Y-m-d") : "null");
            }
        }

        foreach (["charges", "convicted", "sentence"] as $field) {
            if (array_key_exists($field, $spec) && $case->{$field} != $spec[$field]) {
                $case->{$field} = $spec[$field];
                $caseNotes[] = $field;
            }
        }

        if (! empty($spec["institution_name"]) && ! $case->institution_id) {
            $inst = Institution::firstOrCreate(
                ["name" => $spec["institution_name"]],
                ["city" => $spec["institution_city"] ?? null, "state" => $spec["institution_state"] ?? null]
            );
            $case->institution_id = $inst->id;
            $caseNotes[] = "institution=".$inst->name;
        }

        if ($isNew || $caseNotes) {
            $case->save();
        }

        echo "      case ", $year, ($isNew ? " NEW  " : "      "),
             ($caseNotes ? implode(", ", $caseNotes) : "unchanged"),
             "   days=", ($case->imprisoned_for_days === null ? "null" : $case->imprisoned_for_days), "\n";
    }

    $p->refresh();
    echo "    total imprisonedFor now: ", $p->cases()->sum("imprisoned_for_days"), " day(s) across ",
         $p->cases()->count(), " case(s)\n";
}

// ---- and only now, the duplicates --------------------------------------

echo "\nRemoving the duplicate records:\n";

foreach (array_merge($payload["delete"] ?? [], $payload["pettibone_delete"] ?? []) as $slug) {
    $dupe = Prisoner::withoutGlobalScopes()->where("slug", $slug)->with("cases")->first();

    if (! $dupe) {
        echo "  ", $slug, " — already gone\n";
        continue;
    }

    if ($dupe->photo) {
        echo "  ", $slug, " — HAS A PHOTO, refusing to delete. Merge it by hand.\n";
        continue;
    }

    $cases = $dupe->cases->count();
    $dupe->delete();
    echo "  ", $slug, " — deleted (took ", $cases, " case row(s) with it)\n";
}

// ---- unrelated: photographs pulled off records -------------------------

foreach ($payload["clear_photo"] ?? [] as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->first();

    if (! $p) {
        echo "\n  NOT FOUND: ", $row["slug"], "\n";
        continue;
    }

    echo "\n", $row["slug"], "\n";

    if (! $p->photo) {
        echo "    already has no photo\n";
        continue;
    }

    $was = $p->photo;
    $p->photo = null;
    $p->save();

    echo "    photo cleared (was ", $was, ")\n";
    echo "    the file is left on disk, so this is reversible by setting the column back\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
