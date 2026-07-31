#!/usr/bin/env bash
#
# TWO MEN RECORDED AS CONVICTED WHO WERE ACQUITTED.
#
# Isidore Begun and Simon W. Gerson were both described as "Convicted in
# the 1952 second-string Foley Square Smith Act trial and sentenced in
# January 1953" -- to two years and three years respectively. Neither the
# conviction nor the sentence happened. On SEPTEMBER 30, 1952 Federal
# Judge Edward J. Dimock DIRECTED VERDICTS OF ACQUITTAL for both men,
# ruling that the government had presented insufficient evidence. The
# thirteen remaining defendants were convicted and sentenced, and the
# federal appellate decision expressly notes that two defendants had been
# acquitted at the close of the government case.
#
# THE RECORDS WERE ALREADY ARGUING WITH THEMSELVES, which is what makes
# this worth stating carefully. An earlier partial correction had set the
# Convicted field to "No — acquitted" and appended an explanation to the
# sentence text -- but left the biography asserting conviction and
# sentence, and left the sentence field OPENING with "2 years —" and
# "3 years —". The first thing a reader saw was still a prison term. Half
# a correction on the fields nobody reads is worse than none, because it
# looks fixed.
#
# The acquittal date was also wrong by six days: recorded as September
# 24, 1952, actually September 30.
#
# THEIR ACTUAL CUSTODY. Both were arrested on June 20, 1951 and held
# while contesting bail of between $10,000 and $20,000. Both were out by
# June 29 -- about ten days. That is now recorded as a real, closed term
# rather than an open-ended one.
#
# Begun was jailed a SECOND time. In July the government invalidated the
# bonds supplied by the Civil Rights Congress Bail Fund, and Judge
# Sylvester Ryan ordered the defendants apprehended unless they furnished
# private replacement bail. Begun was returned to custody, was one of four
# still jailed at the end of July and one of only two still jailed on
# August 8, and was released on $10,000 private bail in mid-August.
# Neither the return date nor the release date is given in the sources, so
# the return is recorded to the MONTH only and no release date is entered.
# Month precision would put the release on August 1, which the August 8
# report contradicts, so a month-precision release would be worse than
# none.
#
# Gerson gets NO second case. He had acceptable replacement bail by the
# weekend of July 28-29, when he was no longer among those reported
# jailed, but whether he was physically returned to custody in the
# interval is not established. Recording a detention that may not have
# happened is the same error as recording a conviction that did not.
#
# BEGUN GAINS HIS DATES: born December 3, 1903, died October 21, 1988.
# Gerson already carried January 23, 1909 and December 26, 2004.
#
# WHY THE RELEASE DATES MATTER MORE THAN THEY LOOK. Both men had an
# incarceration date and no release date. Gerson also has a death date,
# and Prisoner::getIncarcerationYearsArray fell back to it -- so a man
# jailed for ten days and acquitted was listed as having spent every year
# from 1951 to 2004 in prison. The model is fixed in the same batch;
# recording the real June 29 release fixes it here from the data side too.
#
# The payload lives in database/data/fixes/smith-act-acquittals-1.json so
# the prose can carry ordinary apostrophes and dollar signs.
#
# Guarded and idempotent.
#
# Run from the repo root:
#   bash database/data/fix-smith-act-acquittals-1.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/smith-act-acquittals-1.json")), true);

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

foreach ($payload["records"] as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->with("cases")->first();

    if (! $p) {
        echo "  NOT FOUND: ", $row["slug"], "\n";
        continue;
    }

    $notes = [];

    if (array_key_exists("description", $row) && $p->description !== $row["description"]) {
        $p->description = $row["description"];
        $notes[] = "description rewritten (removed the conviction and sentence)";
    }

    foreach (["birthdate", "death_date"] as $field) {
        if (array_key_exists($field, $row) && $applyDate($p, $field, $row[$field])) {
            $notes[] = $field." ".$p->formatPartialDate($field);
        }
    }

    if ($notes) {
        $p->save();
    }

    echo "  ", str_pad($p->slug, 16), " ", ($notes ? implode("; ", $notes) : "already correct"), "\n";

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

        foreach (["arrest" => "arrest_date", "incarceration" => "incarceration_date", "release" => "release_date"] as $key => $field) {
            if (array_key_exists($key, $spec) && $applyDate($case, $field, $spec[$key])) {
                $caseNotes[] = $field."=".($case->{$field} ? $case->{$field}->format("Y-m-d") : "null");
            }
        }

        foreach (["convicted", "judge", "sentence"] as $field) {
            if (array_key_exists($field, $spec) && $case->{$field} !== $spec[$field]) {
                $case->{$field} = $spec[$field];
                $caseNotes[] = $field;
            }
        }

        if ($isNew || $caseNotes) {
            $case->save();
        }

        echo "      case#", $i, ($isNew ? " NEW  " : "      "),
             ($caseNotes ? implode(", ", $caseNotes) : "unchanged"),
             "   days=", ($case->imprisoned_for_days === null ? "null" : $case->imprisoned_for_days), "\n";
    }

    $p->refresh();
    echo "      years listed as imprisoned: ", implode(", ", $p->years_in_prison), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
