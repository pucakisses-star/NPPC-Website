#!/usr/bin/env bash
#
# BATCH 116 -- Mary Donovan Hapgood, per the curator.
#
#   The recorded Pittston arrest was WRONG: her record dated it
#   March 1929, but the jailing was March 4-8, 1928 — four days in
#   the Luzerne County jail at Wilkes-Barre on charges of disorderly
#   conduct, inciting to riot, and a peace-bond proceeding, released
#   on bond of $2,500 individually ($5,000 combined with Powers
#   Hapgood). Forced date corrections, old values echoed.
#
#   Also entering: her life dates (born 1886, died June 24, 1973 —
#   empty fields only), the Luzerne County Jail institution, a
#   surgical 1929->1928 fix in the biography plus an appended fuller
#   account (Socialist candidate for governor of Massachusetts,
#   Sacco-Vanzetti Defense Committee, the Judge Thayer placard), and
#   the 1927 funeral-parlor arrest as a second case row (arrest at
#   August 1927 month precision; disposition unresolved). Nothing is
#   deleted from the description.
#
#   Flagged, not changed: powers-hapgood shares the same wrong 1929
#   dating and the $5,000 combined-bond figure.
#
# Run from the repo root, after git pull (after the CRDL fourth wave):
#   bash database/data/run-batch-116.sh

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
echo "  Batch 116 — Mary Donovan Hapgood corrections"
echo "==================================================================="

fix_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch116.json")), true);

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

        foreach (["charges" => "case_set_charges", "sentence" => "case_set_sentence"] as $field => $key) {
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

    if (! empty($row["add_case"])) {
        $spec = $row["add_case"];
        $already = $p->cases->first(
            fn ($c) => $c->charges && str_contains($c->charges, $spec["match_missing_charges"])
        );
        if ($already) {
            $notes[] = "added case already present";
        } else {
            $c = new PrisonerCase;
            $c->prisoner_id = $p->id;
            $c->setRelation("prisoner", $p);
            [$y, $mo, $d] = array_pad($spec["arrest"], 3, null);
            $c->setPartialDate("arrest_date", $y, $mo, $d);
            $c->charges = $spec["charges"];
            $c->convicted = $spec["convicted"];
            $c->sentence = $spec["sentence"];
            $c->save();
            $notes[] = "1927 case row added";
        }
    }

    echo "  ", ($notes ? implode("; ", $notes) : "already correct"), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "hapgood-corrections" fix_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 116 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
