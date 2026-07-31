#!/usr/bin/env bash
#
# TWENTY-FIVE MORE SILENT SENTINELS -- dossier entries 26 to 50.
#
# Same rules as batches 30 and 32. Nominal completion dates are never
# stored as releases; a fourth element on a birthdate marks the year
# approximate and stores circa precision.
#
# EIGHT RECORDS ARE RENAMED, in two distinct classes.
#
#   FIVE WERE FILED UNDER A HUSBAND’S NAME and did not name the woman at
#   all: Mrs. Charles W. Barnes is Nellie Main Barnes, Mrs. Edmund C.
#   Evans is Rebecca Winsor Evans, Mrs. Frederick W. Kendall is Ada
#   Louise Davenport Kendall, Mrs. Mark Jackson is Bertha M. Jackson,
#   and Mrs. Palys L. Chevrier is Palys L. Chevrier, known as Dollee.
#
#     THIS IS A DIFFERENT TEST FROM THE ONE APPLIED IN BATCHES 30 AND 32.
#     There, married and maiden names went into the aka field and the
#     record kept the name the sources used, because those names were the
#     woman’s own -- just shorter. "Mrs. Mark Jackson" is not a short
#     form of her name; it is her husband’s name. A database of named
#     people should name them. Every Mrs. form is kept in aka so a search
#     for it still finds her.
#
#   THREE ARE ERRORS. Rebecca Harrison is Rebecca M. Garrison -- the
#   Joplin residence, the February 1919 demonstration and the five-day
#   sentence all match Garrison, and Harrison looks like a transcription
#   or printing slip. Nina Samarodin is Nina Samorodin. Anna Gwinter is
#   Anna Gvinter, whom Irwin also prints as Eleanor Gwinter.
#
#   ALL EIGHT SLUGS CHANGE.
#
# THE JULY 1917 THREE HAD THEIR CUSTODY DATES BACKWARDS. Anne Martin,
# Beatrice Kinkead and Betsy Graves Reyneau each carried an incarceration
# of 1917-07-17 and a release of 1917-07-19 -- which starts custody on
# the day they were PARDONED. They were arrested on July 14 and pardoned
# by President Wilson on July 17. Custody now runs July 14 to 17, giving
# the three days the sources describe instead of two beginning at the
# wrong end.
#
# TWO RECORDS HAD THE WRONG MONTH. Ada Kendall and Bertha Jackson were
# both recorded as arrested in August 1917. Both were arrested on
# September 13, 1917.
#
# ANNA KELTON WILEY’S TERM IS RECONSTRUCTED FROM AN ARITHMETIC FIT. She
# was arrested November 10, 1917 on a fifteen-day sentence, released on
# bond November 19 pending appeal, and is recorded by her biographers as
# having served five days. Five days ending November 19 puts the start at
# November 14, which is also when the rest of that cohort was sentenced.
# The incarceration date is set to November 14 on that reasoning, which
# is stated in the case text rather than presented as a documented date.
#
# THREE CONFLICTS ARE RECORDED, NOT RESOLVED.
#
#   anna-gvinter    Stevens attaches her thirty days to the November 10
#                   arrests; the September 13 chronology is better
#                   supported -- she wrote from Occoquan on September 21
#                   and an October 13 affidavit fits release by then --
#                   and Irwin’s November 10 list omits her. September is
#                   followed and the disagreement is stated.
#   anna-kuhn       Stevens records a November 1917 arrest and thirty-day
#                   sentence; Irwin’s November 10 roster omits her and no
#                   facility or discharge date is given anywhere. That
#                   term is described in her biography but NOT recorded
#                   as a case with dates.
#   ruth-crocker,
#   phoebe-munnecke Their contempt sentences are forty-eight hours in
#                   Irwin and three days in Stevens. Both are recorded as
#                   undated case rows carrying the disagreement.
#
# THREE BIRTHDATES ARE DELIBERATELY LEFT EMPTY where the sources conflict
# on the day rather than the year: Rebecca Winsor Evans (1877 or 1879),
# Belle Sheinberg (October 12 or July 18, 1895), and Ruth Scott (nothing
# established). Recording one of two candidate days would invent
# precision the sources do not have.
#
# The payload lives in database/data/fixes/suffragist-identities-3.json.
#
# Guarded and idempotent: fields are compared before writing, an empty
# cases array leaves existing rows alone, and case rows are matched by
# position so a second run updates rather than duplicates.
#
# Run from the repo root:
#   bash database/data/fix-suffragist-identities-3.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/suffragist-identities-3.json")), true);

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

    // A fourth element marks the year as approximate, which setPartialDate
    // stores as circa precision and renders as "c. 1891".
    [$y, $m, $d, $approx] = array_pad($spec, 4, null);
    $wasDate = $model->{$field} ? $model->{$field}->format("Y-m-d") : null;
    $wasPrec = $model->datePrecisionFor($field);
    $model->setPartialDate($field, $y, $m, $d, (bool) $approx);
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
