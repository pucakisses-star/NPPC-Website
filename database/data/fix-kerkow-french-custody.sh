#!/usr/bin/env bash
#
# CATHERINE MARIE KERKOW -- the French custody of 1975, entered as a
# second case row.
#
#   French police arrested Kerkow and Willie Roger Holder on JANUARY
#   25, 1975; she was held at FLEURY-MEROGIS PRISON near Paris while
#   France considered the American extradition request. France REFUSED
#   the extradition on APRIL 7, 1975, treating the hijacking as
#   politically motivated. On JUNE 2, 1975 a Paris court convicted her
#   of PRESENTING A FALSIFIED PASSPORT and sentenced her to THREE
#   MONTHS AND ONE DAY plus a 1,000-FRANC FINE — and Le Monde reported
#   she was to be released because her detention (about 128 days) had
#   already exceeded the sentence. The release enters June 2, 1975 at
#   APPROXIMATE precision: reported as imminent, not timestamped.
#   France then opened its own hijacking prosecution, but the
#   investigating judge did not keep her incarcerated — judicial
#   supervision after release.
#
#   Jan 25 -> Jun 2, 1975 is exactly 128 days, matching the reported
#   figure; imprisoned_for_days computes to that on save.
#
# EXILE COUNTER GUARD: PrisonerCase::saving() auto-derives
# in_exile_since from release_date whenever the prisoner is flagged in
# exile and the case has none — and exile days are SUMMED across
# cases. Left alone, this new row would sprout a second counter
# running from 1975 to today and double her exile total. So the row
# pins a ZERO-LENGTH exile pair (in_exile_since = end_of_exile =
# 1975-06-02): the hook cannot fire, the row contributes zero exile
# days, and the counter set by the curator on the 1972 air-piracy case
# (in exile since June 2, 1972) remains the only one. That case is not
# touched.
#
# The prose carries apostrophes, so the payload lives in
# database/data/fixes/kerkow-french-custody.json.
#
# Idempotent: the French row is matched by its 1975-01-25 arrest
# (created only if absent) and every field is compared before writing.
#
# Run from the repo root:
#   bash database/data/fix-kerkow-french-custody.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/kerkow-french-custody.json")), true);

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
    echo "NOT FOUND: ", $payload["slug"], " — nothing changed.\n";
    return;
}

echo $p->slug, "\n";

if (! str_contains((string) $p->description, mb_substr($payload["append"], 0, 60))) {
    $p->description = trim((string) $p->description)."\n\n".$payload["append"];
    $p->save();
    echo "  description: 1975 paragraph appended\n";
} else {
    echo "  description: already carries the 1975 paragraph\n";
}

$spec = $payload["case"];

$case = $p->cases->first(fn ($c) => $c->arrest_date && $c->arrest_date->format("Y-m-d") === "1975-01-25");
$isNew = ! $case;

if ($isNew) {
    $case = new PrisonerCase;
    $case->prisoner_id = $p->id;
}

$case->setRelation("prisoner", $p);
$notes = [];

foreach (["arrest_date" => $spec["arrest"], "incarceration_date" => $spec["incarceration"], "sentenced_date" => $spec["sentenced"], "release_date" => $spec["release"]] as $f => $d) {
    if ($applyDate($case, $f, $d)) {
        $notes[] = $f."=".$case->{$f}->format("Y-m-d")." (".($case->datePrecisionFor($f) ?: "day").")";
    }
}

// The zero-length exile pair — see the header: it blocks the
// auto-derive hook so this row adds nothing to the summed exile
// counter, which lives on the 1972 case only.
foreach (["in_exile_since", "end_of_exile"] as $f) {
    if ($applyDate($case, $f, $spec["release"])) {
        $notes[] = $f." pinned (zero-length pair)";
    }
}

foreach (["charges" => $spec["charges"], "convicted" => $spec["convicted"], "sentence" => $spec["sentence"]] as $f => $v) {
    if ($case->{$f} != $v) {
        $case->{$f} = $v;
        $notes[] = $f;
    }
}

$inst = Institution::firstOrCreate(
    ["name" => $spec["institution"]],
    ["city" => $spec["institution_city"], "state" => $spec["institution_state"]]
);

if ($case->institution_id !== $inst->id) {
    $case->institution_id = $inst->id;
    $notes[] = "institution=".$inst->name;
}

if ($isNew || $notes) {
    $case->save();
}

echo "  French case", ($isNew ? " NEW" : ""), ": ", ($notes ? implode("; ", $notes) : "already correct"), "\n";
echo "    days imprisoned=", ($case->imprisoned_for_days ?? "null"),
     "   exile days on this row=", ($case->in_exile_for_days ?? "null"), " (must be 0)\n";

$orig = $p->cases->first(fn ($c) => $c->in_exile_since && $c->in_exile_since->format("Y-m-d") === "1972-06-02");
echo "  1972 exile counter: ", ($orig ? ($orig->in_exile_for_days ?? "?")." day(s) — untouched" : "ROW NOT FOUND (run batch 67 first)"), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
