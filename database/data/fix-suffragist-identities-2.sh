#!/usr/bin/env bash
#
# TWELVE MORE SILENT SENTINELS -- researcher dossier entries 17 to 28.
#
# Same shape as batch 30 and the same rules. The one addition is that a
# birthdate may now carry a fourth element marking the year approximate,
# which stores circa precision: Gertrude Murphy was born "about 1891" and
# renders as "c. 1891" rather than claiming a year the sources hedge.
#
# ONE NAME WAS WRONG IN THE DATABASE.
#
#   elizabeth-hoff -> Elizabeth Minnie Huff. Stevens lists "Elizabeth
#   Hoff"; Irwin names Elizabeth Huff among those arrested on January 13,
#   1919, and the biographical record confirms Huff. THE SLUG CHANGES.
#   "Hoff" is kept in the aka field.
#
# NOMINAL COMPLETION DATES ARE NOT STORED AS RELEASES, and this batch is
# mostly made of them. Nine of these terms have a documented sentencing
# date and no documented discharge. Adding the sentence length to the
# start date would produce a release for every one -- January 19 for the
# whole January 13 cohort, February 2 for Gertrude Murphy, October 23 for
# Ernestine Hara -- and every one would be a calculation presented as a
# record. They are left empty. PrisonerCase returns a null day counter
# for a released prisoner with no release date, so these read as absent
# rather than wrong. Only the four genuinely documented discharges are
# entered: Elizabeth McShane on November 27 1917, August 20 1918 and
# February 13 1919, Estella Eylward on February 13 1919, and Elsie
# Unterman on January 27 1919.
#
# THE JANUARY 13 1919 COHORT were arrested in the afternoon, bailed,
# rearrested the same evening and held overnight before being sentenced
# on the 14th. Custody therefore starts on the 13th for Kalb, Huff,
# Winsor and Vervane -- the overnight detention is documented, which is
# the same test applied to Catherine Boyle in batch 30.
#
# TWO CONFLICTS ARE RECORDED RATHER THAN RESOLVED.
#
#   elizabeth-mcshane   Stevens dates her third term to January 1919.
#                       Irwin and the Library of Congress detailed
#                       chronology both place it in February. February is
#                       used and the disagreement is stated in the bio.
#
#   elsie-unterman      Stevens summarises her as serving three days,
#                       which cannot be reconciled with the two
#                       consecutive forty-eight-hour sentences the
#                       narrative describes. Both sentencing dates are
#                       recorded because they are documented; the total is
#                       left contradictory in the text rather than
#                       silently picked.
#
# ONE CLAIM IS RECORDED AS UNVERIFIED. A biographical account gives Ellen
# Winsor an earlier arrest in the summer of 1917 involving a meeting or a
# statue. No contemporary arrest roster supports it, so it is described in
# her bio and NO case row is created for it.
#
# ERNESTINE HARA IS NOT RENAMED. The dossier gives her canonical name as
# Ernestine Hara Kettler, but Kettler is a later married name and Hara is
# what she was arrested and photographed under. As with the married names
# in batch 30, the fuller forms go in aka and the record keeps the name
# the sources use. Only demonstrable ERRORS are renamed.
#
# The payload lives in database/data/fixes/suffragist-identities-2.json.
#
# Guarded throughout, and idempotent: fields are compared before writing,
# an empty cases array leaves existing rows alone, and case rows are
# matched by position so a second run updates rather than duplicates.
#
# Run from the repo root:
#   bash database/data/fix-suffragist-identities-2.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/suffragist-identities-2.json")), true);

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
