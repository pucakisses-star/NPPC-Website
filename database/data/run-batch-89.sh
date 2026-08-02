#!/usr/bin/env bash
#
# BATCH 89 -- the Republic of New Afrika hijacker trio: names, dates
# and two portraits.
#
#   CHARLIE HILL — full name Charles Lee Hill recorded in the fields
#   (first name Charles, middle name Lee) while the DISPLAY NAME
#   stays "Charlie Hill" per the curator; the "Charles Hill" AKA is
#   cleared. Born December 15, 1949.
#
#   MICHAEL FINNEY — middle name Robert; born December 9, 1950, San
#   Francisco (state California); died January 24, 2005. PHOTO: the
#   curator-supplied Miami Herald clipping (latinamericanstudies.org
#   Finney.pdf, October 30, 1980) — the color photograph captioned
#   "Michael Finney Outside His Home in Havana", cropped from the
#   full-resolution scan at 525x700.
#
#   RALPH GOODWIN — middle name Lawrence; born April 29, 1947,
#   Berkeley (state California); died 1975 (year precision, per the
#   curator). PHOTO: the curator-supplied KOAT news graphic — the
#   panel LABELED "GOODWIN" beneath the Albuquerque Journal "Men
#   Admitted N.M. Killing" front page; the mug panel is nearly
#   square, so it sits full-width on a 525x700 canvas over a
#   blurred-and-darkened fill rather than clipping his hair to force
#   3:4.
#
#   Photo attaches fill EMPTY slots only.
#
# The specs live in database/data/fixes/rna-three.json. Idempotent
# throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-89.sh

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
echo "  Batch 89 — RNA trio: names, dates, Finney + Goodwin portraits"
echo "==================================================================="

fix_trio() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    for slug in michael-finney ralph-goodwin; do
        SRC="database/data/photos/${slug}.jpg"
        if [ -f "$SRC" ]; then
            cp -f "$SRC" "${DST_DIR}/${slug}.jpg"
            echo "copied ${slug}.jpg"
        fi
    done

    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/rna-three.json")), true);

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

foreach ($payload["people"] as $entry) {
    $p = Prisoner::withUnderReview()->where("slug", $entry["slug"])->first();

    if (! $p) {
        echo str_pad($entry["slug"], 20), "NOT FOUND\n";
        continue;
    }

    $notes = [];

    foreach ($entry["set"] as $f => $v) {
        if ($p->{$f} !== $v) {
            $p->{$f} = $v;
            $notes[] = $f.($v === null ? " cleared" : "=".$v);
        }
    }

    foreach (["birthdate", "death_date"] as $f) {
        if (array_key_exists($f, $entry) && $applyDate($p, $f, $entry[$f])) {
            $notes[] = $f;
        }
    }

    if (! empty($entry["photo"])) {
        $rel = "prisoners/".$entry["slug"].".jpg";

        if (is_file(storage_path("app/public/".$rel)) && ! $p->photo) {
            $p->photo = $rel;
            $notes[] = "photo attached";
        } elseif ($p->photo && $p->photo !== $rel) {
            $notes[] = "photo left alone (has another)";
        }
    }

    if ($p->isDirty()) {
        $p->save();
    }

    echo str_pad($entry["slug"], 20), ($notes ? implode("; ", $notes) : "already correct"), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-rna-trio" fix_trio

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 89 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
