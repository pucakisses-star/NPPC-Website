#!/usr/bin/env bash
#
# BATCH 122 -- William Morales (FALN), per the curator: the full
# custody chronology, his birth date, his portrait, and exile status.
#
#   - birthdate February 7, 1950 (empty field only); FALN
#     affiliation ensured; in_exile / currently_in_exile set — he
#     has lived in Cuba since Mexico flew him there on June 24,
#     1988, rather than extraditing him.
#   - The US case row gains its dates: custody from July 12, 1978
#     (the East Elmhurst blast that took his hands), ending May 21,
#     1979 with the Bellevue prison-ward escape — 313 days; the
#     recorded end of custody is the ESCAPE, not a release, and the
#     sentence text says so. Charges and sentences fill in: the
#     February 28, 1979 federal conviction (10 years + 5 probation)
#     and the April 1979 New York State 29-to-89-year sentence;
#     institution MCC New York.
#   - A second case row enters for Mexico: recaptured May 26, 1983
#     near Puebla, convicted over the police officer's death,
#     eight-year sentence, released June 24, 1988 to Cuba — 1,856
#     days.
#   - His portrait (the behind-bars Mexican-custody press photo the
#     curator supplied) attaches into the EMPTY photo slot.
#   - The full chronology is appended to the biography; nothing is
#     deleted.
#
# Run from the repo root, after git pull (after batch 121):
#   bash database/data/run-batch-122.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"
    shift
    echo
    echo "--- ${label}"
    if "$@"; then
        return 0
    fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}")
    return 0
}

echo "==================================================================="
echo "  Batch 122 — William Morales chronology + portrait"
echo "==================================================================="

fix_batch() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    if [ -f "database/data/photos/william-morales.jpg" ]; then
        cp -f "database/data/photos/william-morales.jpg" "${DST_DIR}/william-morales.jpg"
        echo "copied william-morales.jpg"
    fi

    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch122.json")), true);
$row = $payload["morales"] ?? null;

if (! $row) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

if (! $p) { echo $row["slug"], " NOT FOUND\n"; return; }

$notes = [];

if (! empty($row["birthdate"]) && $p->birthdate === null) {
    [$y, $mo, $d] = array_pad($row["birthdate"], 3, null);
    $p->setPartialDate("birthdate", $y, $mo, $d);
    $notes[] = "birthdate set";
}

if (! empty($row["affiliation_add"])) {
    $affs = (array) ($p->affiliation ?? []);
    if (! in_array($row["affiliation_add"], $affs, true)) {
        $affs[] = $row["affiliation_add"];
        $p->affiliation = array_values($affs);
        $notes[] = "affiliation added";
    }
}

if (! empty($row["set_exile"])) {
    if (! $p->in_exile) { $p->in_exile = true; $notes[] = "in_exile"; }
    if (! $p->currently_in_exile) { $p->currently_in_exile = true; $notes[] = "currently_in_exile"; }
}

if (! empty($row["append"]) && ! str_contains((string) $p->description, mb_substr($row["append"], 0, 60))) {
    $p->description = trim((string) $p->description)."\n\n".$row["append"];
    $notes[] = "chronology appended";
}

$rel = "prisoners/".$row["slug"].".jpg";
if (is_file(storage_path("app/public/".$rel)) && ! $p->photo) {
    $p->photo = $rel;
    $notes[] = "photo attached";
}

if ($notes) { $p->save(); }

$us = $row["us_case"];
$case = $p->cases->first(fn ($c) => $c->charges && str_contains($c->charges, $us["match_charges"]));

if ($case) {
    $case->setRelation("prisoner", $p);
    $caseDirty = false;

    $forced = [
        "arrest_date"        => $us["force_arrest"] ?? null,
        "incarceration_date" => $us["force_incarceration"] ?? null,
        "release_date"       => $us["force_release"] ?? null,
    ];

    foreach ($forced as $field => $spec) {
        if (! $spec) { continue; }
        [$y, $mo, $d] = array_pad($spec, 3, null);
        $old = $case->{$field} ? $case->{$field}->format("Y-m-d") : "empty";
        $case->setPartialDate($field, $y, $mo, $d);
        $new = $case->{$field}->format("Y-m-d");
        if ($old !== $new) { $caseDirty = true; $notes[] = $field.": ".$old." -> ".$new; }
    }

    if (isset($us["force_days"]) && (int) $case->imprisoned_for_days !== $us["force_days"]) {
        $case->imprisoned_for_days = $us["force_days"];
        $caseDirty = true;
        $notes[] = "days set";
    }

    foreach (["charges" => "set_charges", "convicted" => "set_convicted", "sentence" => "set_sentence"] as $field => $key) {
        if (! empty($us[$key]) && $case->{$field} !== $us[$key]) {
            $case->{$field} = $us[$key];
            $caseDirty = true;
            $notes[] = $field." set";
        }
    }

    if (! empty($us["institution"]) && ! $case->institution_id) {
        $inst = Institution::firstOrCreate(
            ["name" => $us["institution"]["name"]],
            ["city" => $us["institution"]["city"] ?? null, "state" => $us["institution"]["state"] ?? null]
        );
        $case->institution_id = $inst->id;
        $caseDirty = true;
        $notes[] = "institution set";
    }

    if ($caseDirty) { $case->save(); }
} else {
    $notes[] = "US case not matched";
}

$spec = $row["add_case"];
$already = $p->cases->first(fn ($c) => $c->charges && str_contains($c->charges, $spec["match_missing_charges"]));
if ($already) {
    $notes[] = "Mexico case already present";
} else {
    $c = new PrisonerCase;
    $c->prisoner_id = $p->id;
    $c->setRelation("prisoner", $p);
    foreach (["arrest" => "arrest_date", "incarceration" => "incarceration_date", "release" => "release_date"] as $k => $field) {
        if (! empty($spec[$k])) {
            [$y, $mo, $d] = array_pad($spec[$k], 3, null);
            $c->setPartialDate($field, $y, $mo, $d);
        }
    }
    if (isset($spec["days"])) { $c->imprisoned_for_days = $spec["days"]; }
    $c->charges = $spec["charges"];
    $c->convicted = $spec["convicted"];
    $c->sentence = $spec["sentence"];
    $c->save();
    $notes[] = "Mexico case row added";
}

echo implode("; ", $notes ?: ["already correct"]), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "morales-chronology" fix_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 122 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
