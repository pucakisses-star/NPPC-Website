#!/usr/bin/env bash
#
# DR. OTIS WESLEY SMITH -- he did serve the jail time.
#
# The record said he was "sentenced to eight months on a work gang and
# fined $500 -- the sentence suspended only on condition that he write
# the woman a letter begging forgiveness and leave town." Read plainly,
# that says he served nothing. HE SERVED SEVEN DAYS: March 10 to 17,
# 1958, and the contemporary Atlanta Daily World describes the period as
# "the seven days he was held in jail."
#
# FIVE THINGS WERE WRONG.
#
#   THE YEAR. The telephone incident was June 25, 1957, not 1958. The
#   conviction came the following March.
#
#   THE CUSTODY. Convicted March 10, 1958 by Judge Oscar L. Long of
#   Peach County Superior Court on a straight eight-month jail sentence,
#   he remained in jail while residents petitioned for probation. On
#   March 17 Long suspended the term.
#
#   THE FINE. The $500 came with the MODIFICATION on March 17, not with
#   the original sentence, which carried no fine at all.
#
#   THE LETTER. The record said the suspension was conditioned on his
#   writing the woman a letter begging forgiveness. NO RELIABLE SUPPORT
#   FOR THAT WAS FOUND. The contemporary account documents a fine,
#   probation and an order to leave the area; Zinn mentions leaving town
#   and no letter. It is removed.
#
#   THE WORK GANG. The contemporary source says jail. Howard Zinn's
#   later account calls it eight months on the chain gang. Both are
#   stated; neither is presented as the other.
#
# THE STATUTE IS NAMED because it matters: Georgia's former prohibition
# on unprovoked "opprobrious words or abusive language" tending to cause
# a breach of the peace, which the United States Supreme Court held
# FACIALLY UNCONSTITUTIONAL in Gooding v. Wilson in 1972. The law he was
# jailed under was later found to be no law at all.
#
# THE JAIL BUILDING IS NOT IDENTIFIED, so no institution is linked.
# Contemporary reporting says only that he was held in jail.
#
# THE NAME AND SLUG DO NOT CHANGE. An earlier version of this script
# renamed him to "Otis Wesley Smith", which regenerated the slug. That is
# reverted: he stays Otis W. Smith at /prisoner/otis-w-smith, and only the
# middle-name FIELD is expanded from the initial to Wesley, which does not
# touch the display name and so leaves the slug alone.
#
#   IF THE EARLIER VERSION ALREADY RAN, this script repairs it. It looks
#   up both slugs, sets the name back, and the model regenerates
#   otis-w-smith from it. Re-running is the fix; nothing else is needed.
#
#   THE AKA FIELD IS CLEARED. An earlier version filed alternate names
#   there; the payload now carries a null for it, which the field loop
#   writes through, so any leftover string is removed. There is only one
#   form of his name in use and no alternate spelling to search for.
#
# THE PHOTOGRAPH comes from his own funeral program, in the Digital
# Library of Georgia. Its cover reads "Sunrise May 12, 1925 - Sunset
# February 5, 2007", which is where both dates in this record come from
# -- the image and the vital dates authenticate each other.
#
# Run from the repo root:
#   bash database/data/fix-otis-wesley-smith.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DST_DIR="storage/app/public/prisoners"
mkdir -p "$DST_DIR"

if [ -f database/data/photos/otis-w-smith.jpg ]; then
    cp -f database/data/photos/otis-w-smith.jpg "${DST_DIR}/otis-w-smith.jpg"
    echo "copied otis-w-smith.jpg"
else
    echo "MISSING SOURCE: database/data/photos/otis-w-smith.jpg"
fi

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/otis-wesley-smith.json")), true);

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
    // Try the intended slug, then the one an earlier version of this
    // script would have left behind, so a re-run repairs the rename.
    $p = Prisoner::withoutGlobalScopes()
        ->whereIn("slug", [$row["slug"], "otis-wesley-smith"])
        ->with("cases")
        ->first();

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


php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->whereIn("slug", ["otis-w-smith", "otis-wesley-smith"])->first();

if (! $p) {
    echo "  NOT FOUND: neither otis-wesley-smith nor otis-w-smith\n";
    return;
}

$rel = "prisoners/otis-w-smith.jpg";

if (! is_file(storage_path("app/public/".$rel))) {
    echo "  image not on disk — photo left unset\n";
} else {
    $was = $p->photo;
    $p->photo = $rel;
    $p->save();
    echo "  ", $p->name, " photo -> {$rel}", ($was ? "   (replaced {$was})" : "   (was empty)"), "\n";
}

echo "  slug: ", $p->slug, "\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
