#!/usr/bin/env bash
#
# BATCH 113 -- Flores Forbes: verified custody record + portrait.
#
#   Per the curator, from the appellate record (People v. Forbes,
#   175 Cal. App. 3d 807, A024602), the Colorado College 2016
#   lecture biography, and his own account:
#
#     - The 1977-attack case row gains its dates: sentenced
#       September 7, 1983 (six years second-degree murder + two
#       consecutive for firearm use, eight total), incarcerated
#       October 1980 (month precision — his surrender), released
#       1985 (year precision — the exact date awaits his CDCR
#       External Movement History), 1713 days served (four years,
#       eight months, nine days reckoned from October 1).
#     - The conviction is corrected to what it was: second-degree
#       murder of Louis Talbert Johnson by transferred intent, not
#       attempted murder.
#     - Institution: Soledad State Prison (San Quentin noted in the
#       sentence text).
#     - The biography's "about four and a half years" becomes the
#       documented figure by surgical replace; the sourcing and the
#       derived June 10 - July 9, 1985 release window are appended.
#       Nothing is deleted from the description.
#     - His portrait (the documentary-interview headshot the curator
#       supplied) attaches into the empty photo slot.
#
# Run from the repo root, after git pull (after batch 112):
#   bash database/data/run-batch-113.sh

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
echo "  Batch 113 — Flores Forbes custody record + portrait"
echo "==================================================================="

attach_photo() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    if [ -f "database/data/photos/flores-forbes.jpg" ]; then
        cp -f "database/data/photos/flores-forbes.jpg" "${DST_DIR}/flores-forbes.jpg"
        echo "copied flores-forbes.jpg"
    fi

    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch113.json")), true);

foreach ($payload["photos"] as $slug) {
    $p = Prisoner::withUnderReview()->where("slug", $slug)->first();

    if (! $p) { echo str_pad($slug, 20), "NOT FOUND\n"; continue; }

    $rel = "prisoners/".$slug.".jpg";

    if (! is_file(storage_path("app/public/".$rel))) {
        echo str_pad($slug, 20), "file missing on disk — skipped\n";
        continue;
    }

    if ($p->photo === $rel) { echo str_pad($slug, 20), "already attached\n"; continue; }
    if ($p->photo) { echo str_pad($slug, 20), "has a DIFFERENT photo — left alone\n"; continue; }

    $p->photo = $rel;
    $p->save();
    echo str_pad($slug, 20), "photo attached\n";
}

echo "Done.\n";
'
}

fix_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\Institution;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch113.json")), true);

if (! $payload || empty($payload["corrections"])) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

foreach ($payload["corrections"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

    echo "\nFIX ", $row["slug"], "\n";

    if (! $p) { echo "  NOT FOUND — skipped\n"; continue; }

    $notes = [];

    if (! empty($row["desc_replace"])) {
        [$from, $to] = $row["desc_replace"];
        if (str_contains((string) $p->description, $from)) {
            $p->description = str_replace($from, $to, (string) $p->description);
            $notes[] = "duration corrected in description";
        } elseif (! str_contains((string) $p->description, $to)) {
            $notes[] = "desc_replace target not found — left alone";
        }
    }

    if (! empty($row["append"]) && ! str_contains((string) $p->description, mb_substr($row["append"], 0, 60))) {
        $p->description = trim((string) $p->description)."\n\n".$row["append"];
        $notes[] = "sourcing appended";
    }

    if ($notes) { $p->save(); }

    $case = null;
    if (! empty($row["case_match_charges"])) {
        $case = $p->cases->first(fn ($c) => $c->charges && str_contains($c->charges, $row["case_match_charges"]));
    }

    if ($case) {
        $case->setRelation("prisoner", $p);
        $caseDirty = false;

        foreach (["charges" => "case_set_charges", "convicted" => "case_set_convicted", "sentence" => "case_set_sentence"] as $field => $key) {
            if (! empty($row[$key]) && $case->{$field} !== $row[$key]) {
                $case->{$field} = $row[$key];
                $caseDirty = true;
                $notes[] = $field." set";
            }
        }

        $dates = [
            "arrest_date"        => $row["case_set_arrest"] ?? null,
            "incarceration_date" => $row["case_set_incarceration"] ?? null,
            "sentenced_date"     => $row["case_set_sentenced"] ?? null,
            "release_date"       => $row["case_set_release"] ?? null,
        ];

        foreach ($dates as $field => $spec) {
            if ($spec && $case->{$field} === null) {
                [$y, $mo, $d] = array_pad($spec, 3, null);
                $case->setPartialDate($field, $y, $mo, $d);
                $caseDirty = true;
                $notes[] = $field." set";
            }
        }

        if (isset($row["case_set_days"]) && $case->imprisoned_for_days === null) {
            $case->imprisoned_for_days = $row["case_set_days"];
            $caseDirty = true;
            $notes[] = "days set";
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

    echo "  ", ($notes ? implode("; ", $notes) : "already correct"), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "forbes-custody-record" fix_batch
run "forbes-portrait" attach_photo

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 113 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
