#!/usr/bin/env bash
#
# WILLIE ROGER HOLDER -- the 1991-92 parole-violation jailing, and the
# curator-s replacement biography.
#
#   BIO: replaced verbatim with the curator-s text — the fuller life
#   story: AWOL after a landmine wounding, army jail for marijuana
#   possession, the 1972 hijacking, asylum in Algeria, the 1975 French
#   arrest, the 1980 suspended sentence, the 1986 deportation and
#   FOUR-YEAR US sentence, PAROLE in August 1989, the 1991 parole
#   violation, and his later life as a day laborer.
#
#   NEW CASE ROW: rearrested JULY 2, 1991 on a parole violation after
#   a police informant claimed he was planning another hijacking;
#   held until JUNE 2, 1992 — his final release from prison. Matched
#   by the 1991-07-02 arrest, created only if absent.
#
#   The US air-piracy row-s sentence text gains the four-year sentence
#   and the August 1989 parole (it previously said only that custody
#   ran to August 1989).
#
# EXILE COUNTER GUARD (same as batches 68/69): the auto-derive hook
# sets in_exile_since from release_date for anyone flagged in_exile,
# so the new row pins a zero-length exile pair (in_exile_since =
# end_of_exile = 1992-06-02) and contributes zero exile days; the real
# span stays on the air-piracy row.
#
# The prose carries apostrophes, so the payload lives in
# database/data/fixes/holder-parole-bio.json.
#
# Run from the repo root:
#   bash database/data/fix-holder-parole-case.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/holder-parole-bio.json")), true);

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

    [$y, $m, $d, $circa] = array_pad($spec, 4, null);
    $wasDate = $model->{$field} ? $model->{$field}->format("Y-m-d") : null;
    $wasPrec = $model->datePrecisionFor($field);
    $model->setPartialDate($field, $y, $m, $d, (bool) $circa);

    return $wasDate !== ($model->{$field} ? $model->{$field}->format("Y-m-d") : null)
        || $wasPrec !== $model->datePrecisionFor($field);
};

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->with("cases")->first();

if (! $p) {
    echo "NOT FOUND: ", $payload["slug"], " — run batch 69 first; nothing changed.\n";
    return;
}

echo $p->slug, "\n";

if (trim((string) $p->description) !== $payload["bio"]) {
    $p->description = $payload["bio"];
    $p->save();
    echo "  description replaced with the curator bio\n";
} else {
    echo "  description already the curator bio\n";
}

$us = $p->cases->first(fn ($c) => $c->arrest_date && $c->arrest_date->format("Y-m-d") === "1986-07-26");

if ($us && $us->sentence !== $payload["us_case_sentence"]) {
    $us->setRelation("prisoner", $p);
    $us->sentence = $payload["us_case_sentence"];
    $us->save();
    echo "  US air-piracy row: sentence text now carries the four-year term and the August 1989 parole\n";
} elseif ($us) {
    echo "  US air-piracy row: already correct\n";
} else {
    echo "  US air-piracy row: NOT FOUND (run batch 69 first)\n";
}

$cs = $payload["parole_case"];

$case = $p->cases->first(fn ($c) => $c->arrest_date && $c->arrest_date->format("Y-m-d") === $cs["match_arrest"]);
$isNew = ! $case;

if ($isNew) {
    $case = new PrisonerCase;
    $case->prisoner_id = $p->id;
}

$case->setRelation("prisoner", $p);
$notes = [];

foreach (["arrest_date", "incarceration_date", "release_date", "in_exile_since", "end_of_exile"] as $f) {
    if (array_key_exists($f, $cs) && $applyDate($case, $f, $cs[$f])) {
        $notes[] = $f;
    }
}

foreach (["charges", "sentence"] as $f) {
    if ($case->{$f} != $cs[$f]) {
        $case->{$f} = $cs[$f];
        $notes[] = $f;
    }
}

if ($isNew || $notes) {
    $case->save();
}

echo "  parole-violation row", ($isNew ? " NEW" : ""), ": ",
    ($notes ? implode("; ", $notes) : "already correct"),
    "  days=", ($case->imprisoned_for_days ?? "null"),
    "  exile_days=", ($case->in_exile_for_days ?? "null"), " (must be 0)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
