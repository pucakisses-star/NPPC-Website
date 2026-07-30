#!/usr/bin/env bash
#
# SIXTEEN SILENT SENTINELS -- corrected identities, lifespans and terms.
#
# Of the 143 records in the Silent Sentinels cohort, six had a birthdate
# and seven a death date. Most were created from Doris Stevens’s appendix
# to Jailed for Freedom, which gives a name and a sentence length and
# nothing else, so the records carried a name, a one-line term, and no
# dates at all. This batch applies a researcher dossier covering sixteen
# of them.
#
# TWO NAMES WERE WRONG IN THE DATABASE.
#
#   anne-herkimer -> Anna Herkner. Stevens’s appendix prints “Anne
#     Herkimer” and calls her a Baltimore child-labor inspector. Irwin’s
#     contemporary history prints “Anne Herkner” among those arrested on
#     February 9, 1919. Maryland government records of the period name
#     Anna Herkner of Baltimore as the state child-labor inspector, and
#     her biography confirms the occupation, the city and the February 9
#     arrest. Occupation, city and arrest all match, so the three names
#     are one woman.
#
#     THE CORRECTION NOTE SAYS “HISTORICAL NAME ERROR”, not transcription
#     error. The distinction matters and was made explicitly by the
#     researcher: the misspelling is in the printed 1920 appendix itself,
#     most likely a typographical slip at the point of publication. It is
#     not a modern mis-keying of a correct source, which is what
#     “transcription error” would imply and would wrongly place the fault
#     on this database rather than on Stevens.
#
#   edna-m-purtelle -> Edna Mary Purtell. Straightforward misspelling.
#
#   BOTH RENAMES REGENERATE THE SLUG, because the Prisoner model rebuilds
#   it whenever the name is dirty. /prisoner/anne-herkimer and
#   /prisoner/edna-m-purtelle stop resolving. Accepted for the same
#   reason as the Cerna-Camacho fix in batch 28: a database of named
#   people should spell the names right. Both old spellings are kept in
#   the aka field so a search for them still finds her.
#
# ONE RECORD CLAIMED A PRISON TERM THAT NEVER HAPPENED.
#
#   dr-sarah-h-lockrey recorded “August 1918: 15 days.” Dr. Sarah Hunt
#   Lockrey, a Philadelphia surgeon, was arrested on August 6, 1918 and
#   released on bail, and then PAID THE FINE rather than serve, so that
#   she could carry out scheduled surgery. The case row now records the
#   arrest with no incarceration date, so no day counter is computed, and
#   the description says plainly that she served no jail term.
#
#   THIS RAISES A SCOPE QUESTION THIS SCRIPT DOES NOT DECIDE: she is now
#   a person in a political-prisoner database who was never imprisoned.
#   She is left in place, correctly described, for a curator to rule on.
#
# TWO IDENTITIES ARE DELIBERATELY LEFT UNRESOLVED, and no dates are
# written for either. Getting this wrong would merge two women into one
# record, which is exactly the failure batch 28 had to undo.
#
#   annie-j-magee   Irwin names a “Katherine Magee” arrested January 27
#                   and sentenced January 28, 1919 to five days. Annie
#                   Josephine Stirlith McGee Jackson (1873-1934) has also
#                   been proposed. Neither link survives without a court
#                   or jail ledger, so the record keeps its name, gains
#                   no dates, and states both candidates as unproven.
#
#   bertha-walmsley Stevens gives three days for applauding in court.
#                   Irwin instead lists an “Elizabeth Walmsley” among
#                   thirteen women sentenced January 25, 1919 to
#                   forty-eight hours. Whether Bertha and Elizabeth are
#                   the same woman, and whether either is Elizabeth K.
#                   Andrews Walmsley, is unsettled. Not merged.
#
# ONE CASE ROW WAS INTERNALLY IMPOSSIBLE. julia-emory recorded an arrest
# on 1917-11-10 against an incarceration on 1917-09-04 -- jailed two
# months before being arrested. Her November term now runs from the
# arrest date. She gains three further terms: August 1918, and two
# separate January 1919 sentences.
#
# UNRESOLVED DAYS ARE LEFT NULL RATHER THAN CALCULATED. Several sentences
# have a known start and no documented discharge. Berthe Arnold is the
# clearest: January 30 appears in accounts but is only the arithmetic end
# of a five-day term imposed on January 25, not a record of her walking
# out. Recording it would manufacture a discharge date. Where the release
# is unresolved the field stays empty, and PrisonerCase returns a null
# day counter for a released prisoner with no release date rather than
# counting to the present.
#
# WHERE CUSTODY IS TAKEN TO BEGIN, stated because it is a judgement and
# it moves the counters by a day. For the February 9, 1919 watchfire
# group the sources give an arrest on the 9th and a sentence on the 10th
# without saying where the night in between was spent, so custody is
# recorded from the sentencing date -- the first day it is documented.
# Catherine Boyle is the exception and starts from her arrest, because
# her source says explicitly that she was held overnight. If the February
# 9 women were held overnight too, which is likely given the arrests were
# made at an evening demonstration, each of those counters is one day
# short. The undercount is deliberate: it is the figure the sources
# support.
#
# THE AUGUST 1918 GROUP. Several of these women were sentenced to ten or
# fifteen days but held only from August 15 to 20, 1918 and released
# early together. Both facts are recorded: the dates give the five days
# actually served, and the sentence text gives the term imposed.
#
# The payload lives in database/data/fixes/suffragist-identities-1.json
# so the prose can carry ordinary apostrophes; this script contains none
# inside its single-quoted block.
#
# Guarded throughout: fields are compared before writing, an empty cases
# array means leave the existing rows alone, and case rows are matched by
# position so a second run updates rather than duplicates. Re-running
# reports “already correct”.
#
# Run from the repo root:
#   bash database/data/fix-suffragist-identities-1.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/suffragist-identities-1.json")), true);

if (! $payload || empty($payload["records"])) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

$defaults = $payload["_defaults"] ?? [];

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
    $nowDate = $model->{$field} ? $model->{$field}->format("Y-m-d") : null;

    return $wasDate !== $nowDate || $wasPrec !== $model->datePrecisionFor($field);
};

$people = 0;
$casesUpdated = 0;
$casesCreated = 0;
$missing = 0;

foreach ($payload["records"] as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->with("cases")->first();

    // After a rename the old slug is gone, so fall back to the new name.
    if (! $p && isset($row["name"])) {
        $p = Prisoner::withoutGlobalScopes()->where("name", $row["name"])->with("cases")->first();
    }

    if (! $p) {
        echo "  NOT FOUND: ", $row["slug"], "\n";
        $missing++;
        continue;
    }

    $notes = [];

    foreach (["name", "first_name", "middle_name", "last_name", "aka", "description"] as $field) {
        if (array_key_exists($field, $row) && $p->{$field} !== $row[$field]) {
            $p->{$field} = $row[$field];
            $notes[] = $field;
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

    echo "  ", str_pad($p->slug, 24), " ", ($notes ? implode("; ", $notes) : "already correct"), "\n";

    // An empty cases array means leave the existing rows alone.
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
            $case->charges = $defaults["charges"] ?? null;
            $case->convicted = $defaults["convicted"] ?? null;
            $isNew = true;
        }

        $case->setRelation("prisoner", $p);

        $caseNotes = [];

        foreach (["arrest" => "arrest_date", "incarceration" => "incarceration_date", "release" => "release_date"] as $key => $field) {
            if (array_key_exists($key, $spec) && $applyDate($case, $field, $spec[$key])) {
                $caseNotes[] = $field."=".($case->{$field} ? $case->{$field}->format("Y-m-d") : "null");
            }
        }

        if (array_key_exists("sentence", $spec) && $case->sentence !== $spec["sentence"]) {
            $case->sentence = $spec["sentence"];
            $caseNotes[] = "sentence";
        }

        if ($isNew || $caseNotes) {
            $case->save();
            $isNew ? $casesCreated++ : $casesUpdated++;
        }

        echo "      case#", $i, ($isNew ? " NEW  " : "      "),
             ($caseNotes ? implode(", ", $caseNotes) : "unchanged"),
             "   days=", ($case->imprisoned_for_days === null ? "null" : $case->imprisoned_for_days), "\n";
    }
}

echo "\nPeople updated:   {$people}\n";
echo "Cases updated:    {$casesUpdated}\n";
echo "Cases created:    {$casesCreated}\n";
echo "Slugs not found:  {$missing}\n";

$cohort = Prisoner::withoutGlobalScopes()->where("description", "like", "%Silent Sentinels%")->get();
$withBirth = $cohort->filter(fn ($x) => $x->birthdate)->count();
$withDeath = $cohort->filter(fn ($x) => $x->death_date)->count();
echo "Silent Sentinels cohort: ", $cohort->count(),
     "  with a birthdate: ", $withBirth,
     "  with a death date: ", $withDeath, "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
