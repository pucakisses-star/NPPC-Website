#!/usr/bin/env bash
#
# BATCH 97 -- three portraits (Weger, Hakim, Feyock) and Robert
# Malecki-s dates and exile span.
#
#   COLIN WEGER — curator-supplied Detroit News booking photo (the
#   CDN file is named for him: 90511476007-colin-weger.JPG; fetched
#   through a real browser, the CDN refuses plain clients), center
#   crop 525x700.
#
#   ZAINAB HAKIM — curator-supplied portrait (the widely shared
#   photo of her speaking at the encampment, keffiyeh and
#   microphone), cropped above the graphic-s banner at 525x700.
#
#   PAIGE FEYOCK — curator-supplied headshot, center crop 525x700.
#
#   All three attaches fill EMPTY slots only.
#
#   ROBERT MALECKI — born October 27, 1942 (already recorded —
#   confirmed); died SEPTEMBER 24, 2024, in Sweden. Exile per the
#   curator: from JUNE 1972 (month precision) to his death — about
#   52 years. currently_in_exile turns off, the historical flag
#   stays on, and the counter recomputes.
#
# Idempotent throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-97.sh

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
echo "  Batch 97 — Weger/Hakim/Feyock portraits; Malecki dates + exile"
echo "==================================================================="

fix_batch() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    for slug in colin-weger zainab-hakim paige-feyock; do
        SRC="database/data/photos/${slug}.jpg"
        if [ -f "$SRC" ]; then
            cp -f "$SRC" "${DST_DIR}/${slug}.jpg"
            echo "copied ${slug}.jpg"
        fi
    done

    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch97.json")), true);

if (! $payload) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

foreach ($payload["photos"] as $slug) {
    $p = Prisoner::withUnderReview()->where("slug", $slug)->first();

    if (! $p) {
        echo str_pad($slug, 20), "NOT FOUND\n";
        continue;
    }

    $rel = "prisoners/".$slug.".jpg";

    if (! is_file(storage_path("app/public/".$rel))) {
        echo str_pad($slug, 20), "no file in storage — skipped\n";
    } elseif ($p->photo === $rel) {
        echo str_pad($slug, 20), "already attached\n";
    } elseif ($p->photo) {
        echo str_pad($slug, 20), "has another photo — left alone\n";
    } else {
        $p->photo = $rel;
        $p->save();
        echo str_pad($slug, 20), "photo attached\n";
    }
}

$applyDate = function ($model, string $field, $spec): bool {
    [$y, $m, $d, $circa] = array_pad($spec, 4, null);
    $wasDate = $model->{$field} ? $model->{$field}->format("Y-m-d") : null;
    $wasPrec = $model->datePrecisionFor($field);
    $model->setPartialDate($field, $y, $m, $d, (bool) $circa);

    return $wasDate !== ($model->{$field} ? $model->{$field}->format("Y-m-d") : null)
        || $wasPrec !== $model->datePrecisionFor($field);
};

$spec = $payload["malecki"];
$p = Prisoner::withUnderReview()->where("slug", $spec["slug"])->with("cases")->first();

if (! $p) {
    echo str_pad($spec["slug"], 20), "NOT FOUND\n";
} else {
    $notes = [];

    if ($applyDate($p, "birthdate", $spec["birthdate"])) {
        $notes[] = "birthdate";
    }

    if ($applyDate($p, "death_date", $spec["death_date"])) {
        $notes[] = "death_date";
    }

    if ($p->currently_in_exile) {
        $p->currently_in_exile = false;
        $notes[] = "currently_in_exile=false";
    }

    if (! $p->in_exile) {
        $p->in_exile = true;
        $notes[] = "in_exile=true";
    }

    if ($notes) {
        $p->save();
    }

    echo str_pad($spec["slug"], 20), ($notes ? implode("; ", $notes) : "person already correct"), "\n";

    $case = $p->cases->sortBy("created_at")->first();

    if (! $case) {
        echo "  no case row — exile span skipped\n";
    } else {
        $case->setRelation("prisoner", $p);
        $cnotes = [];

        if ($applyDate($case, "in_exile_since", $spec["in_exile_since"])) {
            $cnotes[] = "in_exile_since=June 1972";
        }

        if ($applyDate($case, "end_of_exile", $spec["end_of_exile"])) {
            $cnotes[] = "end_of_exile=2024-09-24";
        }

        $case->save();

        echo "  case: ", ($cnotes ? implode("; ", $cnotes) : "already correct"),
            "  exile_days=", ($case->in_exile_for_days ?? "null"), " (~52 years)\n";
    }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-batch-97" fix_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 97 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
