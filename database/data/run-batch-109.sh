#!/usr/bin/env bash
#
# BATCH 109 -- the duplicate-photo audit: every profile photo in the
# database was checked against every other (exact byte hash plus
# perceptual hash), group photos legitimately shared across records
# excluded, and each remaining collision verified visually and
# against the biographies. Per the curator, with the standing rule:
# NOTHING IS DELETED FROM DESCRIPTIONS.
#
#   TWENTY-SIX PHOTO DETACHMENTS — records wearing someone else's
#   portrait (usually a more famous near-namesake: Julius Rosenberg
#   wearing Susan Rosenberg's photo, Fred Hampton wearing his son's,
#   James Larson wearing Big Jim Larkin's...). The photo column
#   clears; the image file stays on disk because in most pairs the
#   OTHER record legitimately owns it. Five initially ambiguous
#   pairs were resolved by reference-image research (Vern/Elmer
#   Smith, Shafer/Samuel, Shabazz Bey/Azeez, Wilson/Lewinson,
#   Gelders/Geier) and are included.
#
#   TEN MERGES — photo collisions that turned out to be one person
#   with two records (frank-cordaro/cordero, steve-kelly-sj/
#   stephen-kelly, ammon-hennacy/hennesey, the Kashinowitz spellings,
#   dawn-jeffrey, bryce-williams, james-e-jackson, judith-clark,
#   jorge-cornell, sophie-melvin/gerson). The kept record gains the
#   dropped record's ENTIRE biography as an appended passage plus any
#   AKA, photo, inmate number, and case dates it lacked; where the
#   dropped record documents SEPARATE custody episodes (Kelly,
#   Melvin) those case rows MOVE to the kept record instead of
#   deleting.
#
# Run from the repo root, after git pull (after batch 108):
#   bash database/data/run-batch-109.sh

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
echo "  Batch 109 — duplicate-photo audit: detachments + merges"
echo "==================================================================="

fix_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch109.json")), true);

if (! $payload || empty($payload["detach_photos"]) || empty($payload["merges"])) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

foreach ($payload["detach_photos"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->first();

    echo "\nDETACH ", $row["slug"], "\n";

    if (! $p) { echo "  NOT FOUND — skipped\n"; continue; }

    if ($p->photo) {
        $p->photo = null;
        $p->save();
        echo "  photo detached (", $row["reason"], ")\n";
    } else {
        echo "  already photo-less\n";
    }
}

foreach ($payload["merges"] as $m) {
    $keep = Prisoner::withUnderReview()->where("slug", $m["keep"])->with("cases")->first();
    $drop = Prisoner::withUnderReview()->where("slug", $m["drop"])->with("cases")->first();

    echo "\nMERGE ", $m["keep"], " <- ", $m["drop"], "\n";

    if (! $keep) { echo "  keep record NOT FOUND — skipped\n"; continue; }
    if (! $drop) { echo "  drop record not found (already merged?)\n"; }

    $notes = [];

    if ($drop) {
        $dropDesc = trim((string) $drop->description);
        if ($dropDesc !== "" && ! str_contains((string) $keep->description, mb_substr($dropDesc, 0, 60))) {
            $keep->description = trim((string) $keep->description)
                ."\n\nFrom a second record of the same person, merged here so nothing is lost:\n\n".$dropDesc;
            $notes[] = "description merged";
        }

        if ($drop->photo && ! $keep->photo) {
            $keep->photo = $drop->photo;
            $notes[] = "photo carried over";
        }

        if ($drop->inmate_number && ! $keep->inmate_number) {
            $keep->inmate_number = $drop->inmate_number;
            $notes[] = "inmate_number carried over";
        }
    }

    if (! empty($m["aka_add"]) && ! str_contains((string) $keep->aka, $m["aka_add"])) {
        $keep->aka = trim(($keep->aka ? $keep->aka."; " : "").$m["aka_add"], "; ");
        $notes[] = "aka added";
    }

    $case = $keep->cases->first();

    if ($case) {
        $case->setRelation("prisoner", $keep);
        $caseDirty = false;

        if (! empty($m["copy_case_arrest"]) && $case->arrest_date === null) {
            [$y, $mo, $d] = array_pad($m["copy_case_arrest"], 3, null);
            $case->setPartialDate("arrest_date", $y, $mo, $d);
            $caseDirty = true;
            $notes[] = "case arrest copied";
        }

        if (! empty($m["copy_case_incarceration"]) && $case->incarceration_date === null) {
            [$y, $mo, $d] = array_pad($m["copy_case_incarceration"], 3, null);
            $case->setPartialDate("incarceration_date", $y, $mo, $d);
            $caseDirty = true;
            $notes[] = "case incarceration copied";
        }

        if (! empty($m["copy_case_release"]) && $case->release_date === null) {
            [$y, $mo, $d] = array_pad($m["copy_case_release"], 3, null);
            $case->setPartialDate("release_date", $y, $mo, $d);
            $caseDirty = true;
            $notes[] = "case release copied";
        }

        if ($caseDirty) { $case->save(); }
    }

    if ($notes) { $keep->save(); }

    if ($drop) {
        if (! empty($m["move_cases"])) {
            $keepMonths = $keep->cases
                ->map(fn ($c) => $c->arrest_date ? $c->arrest_date->format("Y-m") : null)
                ->filter()
                ->all();

            foreach ($drop->cases as $c) {
                $month = $c->arrest_date ? $c->arrest_date->format("Y-m") : null;

                if ($month !== null && in_array($month, $keepMonths, true)) {
                    $c->delete();
                    $notes[] = "duplicate case ".$month." deleted";
                } else {
                    $c->prisoner_id = $keep->id;
                    $c->save();
                    $notes[] = "case ".($month ?? "undated")." moved";
                }
            }
        } else {
            foreach ($drop->cases as $c) { $c->delete(); }
        }

        $drop->delete();
        $notes[] = "duplicate row deleted";
    }

    echo "  ", ($notes ? implode("; ", $notes) : "nothing to do"), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "photo-detachments-and-merges" fix_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 109 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
