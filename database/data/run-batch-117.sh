#!/usr/bin/env bash
#
# BATCH 117 -- Powers Hapgood: the full custody reconstruction, per
# the curator's newspaper research.
#
#   THE PITTSTON YEAR ERROR: his record dated the arrest March 1929;
#   it was March 4, 1928, with Mary Donovan Hapgood — about four
#   days in the Luzerne County jail at Wilkes-Barre under $5,000
#   bail each, release dated March 8 as probable (habeas denied and
#   bail fixed that day; a labor-defense account has release on that
#   bail after several days), acquitted later in 1928. Forced date
#   corrections, old values echoed.
#
#   LIFE DATES (empty fields only): born December 28, 1899, Chicago;
#   died February 4, 1949, Marion County, Indiana — per the
#   Encyclopedia of Indianapolis.
#
#   SEVEN CASE ROWS ADDED from the reconstruction, with unresolved
#   fields stated rather than inferred:
#     1922-04-15  Somerset organizing arrest (free by April 17)
#     1927-08-10  State House death-watch picketing
#     1927-08-14  Boston Common speech (6 months imposed Aug 16,
#                 never served — appealed under $1,000 bail)
#     1927-08-22  execution-night arrest + Boston Psychopathic
#                 Hospital commitment (released by Aug 26)
#     1927-08     the contemporaneously reported fourth Boston
#                 arrest, exact date unresolved
#     1931        Glen Alden / Maxwell Colliery attempted-assault
#                 charge, county jail, $1,000 bail, dates unsurfaced
#     1937-05-06  Lewiston-Auburn shoe-strike contempt — 58 days
#                 served of a six-month term, released ~July 2-3
#                 (month precision), ruling overturned
#
#   The biography gets the surgical 1929->1928 fix plus the full
#   appended account (the dozen-plus Somerset arrests and the IUP
#   archive lead included). Nothing is deleted from the description.
#
# Run from the repo root, after git pull (after batch 116):
#   bash database/data/run-batch-117.sh

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
echo "  Batch 117 — Powers Hapgood reconstruction"
echo "==================================================================="

fix_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch117.json")), true);

if (! $payload || empty($payload["corrections"])) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

foreach ($payload["corrections"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

    echo "\nFIX ", $row["slug"], "\n";

    if (! $p) { echo "  NOT FOUND — skipped\n"; continue; }

    $notes = [];

    foreach (["birthdate", "death_date"] as $field) {
        if (! empty($row[$field]) && $p->{$field} === null) {
            [$y, $mo, $d] = array_pad($row[$field], 3, null);
            $p->setPartialDate($field, $y, $mo, $d);
            $notes[] = $field." set";
        }
    }

    if (! empty($row["desc_replace"])) {
        [$from, $to] = $row["desc_replace"];
        if (str_contains((string) $p->description, $from)) {
            $p->description = str_replace($from, $to, (string) $p->description);
            $notes[] = "description date corrected";
        } elseif (! str_contains((string) $p->description, $to)) {
            $notes[] = "desc_replace target not found — left alone";
        }
    }

    if (! empty($row["append"]) && ! str_contains((string) $p->description, mb_substr($row["append"], 0, 60))) {
        $p->description = trim((string) $p->description)."\n\n".$row["append"];
        $notes[] = "fuller account appended";
    }

    if ($notes) { $p->save(); }

    $case = null;
    if (! empty($row["case_match_charges"])) {
        $case = $p->cases->first(fn ($c) => $c->charges && str_contains($c->charges, $row["case_match_charges"]));
    }

    if ($case) {
        $case->setRelation("prisoner", $p);
        $caseDirty = false;

        $forced = [
            "arrest_date"        => $row["case_force_arrest"] ?? null,
            "incarceration_date" => $row["case_force_incarceration"] ?? null,
            "release_date"       => $row["case_force_release"] ?? null,
        ];

        foreach ($forced as $field => $spec) {
            if (! $spec) { continue; }
            [$y, $mo, $d] = array_pad($spec, 3, null);
            $old = $case->{$field} ? $case->{$field}->format("Y-m-d") : "empty";
            $case->setPartialDate($field, $y, $mo, $d);
            $new = $case->{$field}->format("Y-m-d");
            if ($old !== $new) {
                $caseDirty = true;
                $notes[] = $field.": ".$old." -> ".$new;
            }
        }

        if (isset($row["case_force_days"]) && (int) $case->imprisoned_for_days !== $row["case_force_days"]) {
            $case->imprisoned_for_days = $row["case_force_days"];
            $caseDirty = true;
            $notes[] = "days set";
        }

        foreach (["charges" => "case_set_charges", "convicted" => "case_set_convicted", "sentence" => "case_set_sentence"] as $field => $key) {
            if (! empty($row[$key]) && $case->{$field} !== $row[$key]) {
                $case->{$field} = $row[$key];
                $caseDirty = true;
                $notes[] = $field." set";
            }
        }

        if (! empty($row["case_set_institution"]) && ! $case->institution_id) {
            $inst = Institution::firstOrCreate(
                ["name" => $row["case_set_institution"]["name"]],
                [
                    "city"  => $row["case_set_institution"]["city"] ?? null,
                    "state" => $row["case_set_institution"]["state"] ?? null,
                ]
            );
            $case->institution_id = $inst->id;
            $caseDirty = true;
            $notes[] = "institution set (".($inst->wasRecentlyCreated ? "new" : "existing").")";
        }

        if ($caseDirty) { $case->save(); }
    } elseif (! empty($row["case_match_charges"])) {
        $notes[] = "matching case not found";
    }

    foreach ($row["add_cases"] ?? [] as $spec) {
        $already = $p->cases->first(
            fn ($c) => $c->charges && str_contains($c->charges, $spec["match_missing_charges"])
        );
        if ($already) {
            $notes[] = "case already present: ".$spec["match_missing_charges"];
            continue;
        }
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
        $notes[] = "case row added: ".$spec["match_missing_charges"];
    }

    echo "  ", ($notes ? implode("; ", $notes) : "already correct"), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "powers-hapgood" fix_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 117 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
