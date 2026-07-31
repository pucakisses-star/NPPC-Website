#!/usr/bin/env bash
#
# TWELVE MORE MISCLASSIFIED OR OVER-ASSERTED RECORDS.
#
# The recurring fault in this round is not a wrong date. It is a record
# asserting more than its source supports -- a group sentence written as
# an individual one, a pretrial ruling written as a conviction, a
# sentence length projected forward into a release.
#
# THREE RENAMES, all changing the slug.
#
#   ed-evans          -> Henry C. Evans. "Ed Evans" appears to conflate
#                        him with his fellow Sacramento prisoner Ed
#                        Burns; contemporary sources give H. C. Evans and
#                        Henry Evans. NOT to be confused with the Henry
#                        Evans already in this database, the Oberlin
#                        abolitionist of 1859 -- a different man in a
#                        different century, whose slug is unaffected.
#   charles-jacobsen  -> C. Jacobsen. The expansion to Charles is
#                        unverified; the records give only the initial.
#   prisciliano-silva -> Prisciliano G. Silva.
#
# FOUR OVER-ASSERTIONS ARE WITHDRAWN.
#
#   tomas-de-espinosa   His sentence field carried Turner’s COLLECTIVE
#                       range of about eighteen to thirty months, and
#                       Leavenworth or Florence, as though describing his
#                       own term. Turner does not identify his individual
#                       sentence or institution. Both are now labelled
#                       group-level and no institution is linked.
#   andre-boutin        Described as found guilty of disloyalty on the
#                       strength of United States v. Boutin, 251 F. 313
#                       (N.D.N.Y. 1918). THAT DECISION REFUSES TO DISMISS
#                       THE PROSECUTION -- it is a pretrial motion, not a
#                       judgment. The conviction is no longer asserted.
#   augustus-j-johnson  A contemporary report says FOUR unnamed officials
#                       began serving suspended ten-day sentences after
#                       the December 9, 1957 transit strike began. His
#                       participation is probable, not documented, and
#                       the term is now recorded as group evidence.
#   santiago-oleary     Captured San Patricios received materially
#                       different outcomes according to whether they had
#                       enlisted in and deserted from the United States
#                       Army. The group’s death sentences, brandings and
#                       whippings cannot be assigned to him without his
#                       court-martial record, and are not.
#
# ONE IDEOLOGY IS PLAINLY IMPOSSIBLE AND IS REMOVED. Santiago O’Leary,
# captured at Churubusco in 1847, was tagged CATHOLIC WORKER MOVEMENT --
# a movement founded in 1933, eighty-six years later.
#
# TWO JURISDICTIONAL FIXES. Albert Brooks was convicted under MONTANA’S
# STATE SEDITION LAW by a Beaverhead County jury on May 24, 1918, case
# number 735, for distributing an IWW pamphlet -- not under the federal
# Espionage Act. THE MONTANA SUPREME COURT REVERSED IT on April 8, 1920
# and he was released on May 4. Walter Nichiperuck was an ADMINISTRATIVE
# IMMIGRATION DEPORTEE, not a criminal prisoner, and the Palmer-era group
# detention dates are deliberately not applied to him.
#
# NO RELEASE DATE IS EVER PROJECTED FROM A SENTENCE LENGTH. Otto
# Inglehardt’s one year from October 7, 1918, and the ninety days given
# to Caravas and Conn in a Labor Defender photograph caption, would each
# yield a tidy release date and none is documented. Albert Brooks has a
# release but no admission, so his counter stays empty too.
#
# TWO DATE CONFLICTS ARE RECORDED RATHER THAN RESOLVED. Henry C. Evans
# died in the Sacramento County Jail awaiting trial: the Sacramento Bee
# reported October 31, 1918, a cemetery record gives October 30. C.
# Jacobsen was sentenced either to one year or to one year and one day,
# depending on which of two 1919-20 summaries is followed.
#
# The payload is in database/data/fixes/misclassified-prosecutions-2.json.
#
# Guarded and idempotent throughout.
#
# Run from the repo root:
#   bash database/data/fix-misclassified-prosecutions-2.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/misclassified-prosecutions-2.json")), true);

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
