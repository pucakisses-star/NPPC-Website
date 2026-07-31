#!/usr/bin/env bash
#
# SIX CURATOR CORRECTIONS to current-era records.
#
# Four are dates that were wrong by a day or by months; one is an age;
# one is a record that had a person out of custody when he is inside.
#
# KAT ABUGHAZALEH WAS NEVER ARRESTED AT THE PROTEST. The record dated her
# arrest to September 26, 2025, the day of the anti-ICE confrontation at
# the Broadview ICE Processing Center. She was not taken there. The
# charges came afterwards and she SELF-SURRENDERED on November 12, 2025,
# and was released the same day. The arrest date moves to the surrender;
# the incarceration date stays empty because there was none.
#
#   SCOPE NOTE, flagged and not decided: this makes her the second record
#   in recent batches, after Dr. Sarah Lockrey in batch 30, of somebody
#   charged in a political case who served no jail time at all. Whether
#   such people belong in a political-PRISONER database is a curatorial
#   question. She is left in place and described accurately.
#
# LARRY BUSHART was arrested late on September 21, not September 20. The
# stored release of October 27 is unchanged, so the day counter moves
# from 37 to 36.
#
#   THE COUNTER AND THE PROSE NOW DISAGREE BY ONE, ON PURPOSE. His
#   biography says he spent thirty-seven days in jail, and that figure is
#   correct counted INCLUSIVELY from September 21 to October 27. The
#   counter measures the interval between the two dates, which is 36.
#   That is the same inclusive-versus-interval distinction recorded in
#   the John Letcher script. It is worth noticing that the corrected date
#   is what makes the reported thirty-seven days come out right: from
#   September 20 an inclusive count gives thirty-eight.
#
# LUCAS GRIFFITH was released on July 18, 2025. His case row had an
# arrest date and no custody at all, so the record showed him as never
# jailed. He was held overnight; incarceration and release are both
# recorded and the counter reads one day.
#
# MAHMOUD KHALIL was arrested on March 8, not March 7. The counter moves
# from 104 days to 103.
#
#   HIS DESCRIPTION IS ALSO REWRITTEN, because it was not merely thin but
#   contradicted the record: it said he "is being detained" while the
#   flags said released. It now gives the arrest, the detention in Jena,
#   Louisiana, the absence of any criminal charge, and the June 19, 2025
#   release on federal court order.
#
#   NOT CHANGED, and worth a look: the stored release date of June 19,
#   2025. Widely published accounts put his release on June 20. The
#   correction supplied covered only the arrest date, so the release is
#   left as it stands rather than changed on an unsourced hunch.
#
# MOHAMMED HOQUE was 20 when first detained, not 22. The stored age
# column said 22 while his own biography said 20 in its first sentence,
# so the record disagreed with itself. The age is set to 20 and the
# sentence is rewritten to ANCHOR it -- "was a 20-year-old ... when he
# was first detained" -- so it cannot later be read as his current age.
# He has no birthdate, and 20 on one date gives a two-year window rather
# than a year, so none is derived.
#
#   ALSO NOT CHANGED: his case row dates the arrest to March 27, 2025
#   while his biography says March 28. One of them is wrong. The
#   correction supplied did not cover it, so the prose is softened to
#   "late March 2025" and the case row is left alone rather than guessed
#   at. A source naming the day would settle it.
#
#   Fixed in passing: "Hoques arrest" had lost its apostrophe.
#
# JOHN WADE was the worst of the six. His record carried a single case
# with no arrest date, no incarceration date, and a release on November
# 2, 2025 -- while the flags said he was in custody, which the day
# counter therefore reported as zero. In fact his federal release was in
# JUNE 2024, he was REARRESTED IN OCTOBER 2024, and he was still listed
# in custody in spring 2026. The first case now ends in June 2024 and a
# second case carries the rearrest.
#
#   BOTH NEW DATES ARE MONTH-PRECISION, because only the months are
#   established. A month-precision date resolves to the first of the
#   month, so his current term counts from October 1, 2024 and may
#   overstate the span by up to thirty days. That is stated in the case
#   text. The alternative was to leave the incarceration empty, which
#   would report nothing at all for a man who has been inside for about
#   twenty-one months.
#
# The payload lives in database/data/fixes/modern-case-corrections-1.json
# so the prose can carry ordinary apostrophes.
#
# Guarded and idempotent: every field is compared before writing, case
# rows are matched by position so a second run updates rather than
# duplicates, and keys absent from a record are left untouched.
#
# Run from the repo root:
#   bash database/data/fix-modern-case-corrections-1.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/modern-case-corrections-1.json")), true);

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
    $wasDate = $model->{$field} ? $model->{$field}->format("Y-m-d") : null;
    $wasPrec = $model->datePrecisionFor($field);
    $model->setPartialDate($field, $y, $m, $d);

    return $wasDate !== ($model->{$field} ? $model->{$field}->format("Y-m-d") : null)
        || $wasPrec !== $model->datePrecisionFor($field);
};

$people = 0;
$casesUpdated = 0;
$casesCreated = 0;
$missing = 0;

foreach ($payload["records"] as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->with("cases")->first();

    if (! $p) {
        echo "  NOT FOUND: ", $row["slug"], "\n";
        $missing++;
        continue;
    }

    $notes = [];

    foreach (["description", "age", "in_custody", "released", "awaiting_trial"] as $field) {
        if (array_key_exists($field, $row) && $p->{$field} != $row[$field]) {
            $p->{$field} = $row[$field];
            $notes[] = $field;
        }
    }

    if ($notes) {
        $p->save();
        $people++;
    }

    echo "  ", str_pad($p->slug, 20), " ", ($notes ? implode("; ", $notes) : "already correct"), "\n";

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
            // Carry the charge text forward from the first case so a new row
            // for a later term is not left blank.
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

    $p->refresh();
    echo "      total imprisonedFor now: ", $p->cases()->sum("imprisoned_for_days"), "\n";
}

echo "\nPeople updated:   {$people}\n";
echo "Cases updated:    {$casesUpdated}\n";
echo "Cases created:    {$casesCreated}\n";
echo "Slugs not found:  {$missing}\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
