#!/usr/bin/env bash
#
# BATCH 75 -- George Wright: birth date, biography, exile pinned to
# the hijacking, the 2011 Portuguese custody, and the photograph.
#
#   PERSON — middle name EDWARD recorded (display name stays "George
#   Wright", so the slug is untouched); born MARCH 29, 1943, Halifax,
#   Virginia (state set). AKA José Luís Jorge dos Santos was already
#   on the record. Bio replaced with the curator-s text: the 1962
#   robbery-murder of Walter Patterson (no-contest plea, 15-to-30
#   years), the August 22, 1970 Leesburg escape with George Brown, the
#   Flight 841 hijacking as Reverend Larry Darnell Burgess, the
#   Guinea-Bissau and Portugal years, the 2011 arrest and denied
#   extradition, and his standing as a wanted fugitive living in
#   Portugal.
#
#   MAIN CASE — in_exile_since pinned explicitly to JULY 31, 1972
#   (the hijacking) with NO end and currently_in_exile left on, so
#   the counter runs to today, per the curator. Conviction and
#   sentence text carry the full custody story.
#
#   NEW CASE — the Portuguese custody: arrested SEPTEMBER 26, 2011
#   (fingerprint match to the fugitive record), released OCTOBER 14,
#   2011; Portuguese courts denied the American extradition request.
#   The row pins the standard zero-length exile pair so the
#   auto-derive hook cannot start a second running counter (exile
#   days are summed across rows; the real span lives on the main
#   case).
#
#   PHOTOGRAPH — curator-supplied (ibb.co/dsJM6Gt9). PROVENANCE NOTE:
#   the file self-identifies as ChatGPT-processed (its original
#   filename is "Chat-GPT-Image-Aug-1-2026..."), i.e. an AI-rendered
#   version of his 2011 Portugal imagery. It was face-checked against
#   the FBI-s authenticated 2011 photographs (from the official
#   wanted-poster PDF at fbi.gov/wanted/dt/george-edward-wright) and
#   is consistent — same head shape, thin rectangular glasses, brow
#   and jowl structure. Attached per the curator-s direction; the
#   authentic FBI originals remain available if preferred. Fills an
#   empty slot only.
#
# The prose lives in database/data/fixes/george-wright-full.json.
# Idempotent throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-75.sh

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
echo "  Batch 75 — George Wright: dossier, Portuguese custody, photo"
echo "==================================================================="

fix_wright() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    if [ -f "database/data/photos/george-wright.jpg" ]; then
        cp -f "database/data/photos/george-wright.jpg" "${DST_DIR}/george-wright.jpg"
        echo "copied george-wright.jpg"
    fi

    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/george-wright-full.json")), true);

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

$spec = $payload["person"];
$notes = [];

foreach (["middle_name", "state"] as $f) {
    if ($p->{$f} != $spec[$f]) {
        $p->{$f} = $spec[$f];
        $notes[] = $f;
    }
}

if ($applyDate($p, "birthdate", $spec["birthdate"])) {
    $notes[] = "birthdate";
}

if (trim((string) $p->description) !== $payload["bio"]) {
    $p->description = $payload["bio"];
    $notes[] = "bio";
}

$rel = "prisoners/george-wright.jpg";
if (is_file(storage_path("app/public/".$rel)) && ! $p->photo) {
    $p->photo = $rel;
    $notes[] = "photo attached";
}

if ($notes) {
    $p->save();
}

echo "  person: ", ($notes ? implode("; ", $notes) : "already correct"), "\n";

$mc = $payload["main_case"];
$main = $p->cases->first(fn ($c) => str_contains((string) $c->charges, $mc["match_charges"]));

if (! $main) {
    echo "  main case: NOT FOUND — skipped\n";
} else {
    $main->setRelation("prisoner", $p);
    $mnotes = [];

    if ($applyDate($main, "in_exile_since", $mc["in_exile_since"])) {
        $mnotes[] = "in_exile_since";
    }

    foreach (["convicted", "sentence"] as $f) {
        if ($main->{$f} != $mc[$f]) {
            $main->{$f} = $mc[$f];
            $mnotes[] = $f;
        }
    }

    if ($mnotes) {
        $main->save();
    }

    echo "  main case: ", ($mnotes ? implode("; ", $mnotes) : "already correct"),
        "  exile_days=", ($main->in_exile_for_days ?? "null"), " (runs to today)\n";
}

$cs = $payload["portugal_case"];
$case = $p->cases->first(fn ($c) => $c->arrest_date && $c->arrest_date->format("Y-m-d") === $cs["match_arrest"]);
$isNew = ! $case;

if ($isNew) {
    $case = new PrisonerCase;
    $case->prisoner_id = $p->id;
}

$case->setRelation("prisoner", $p);
$cnotes = [];

foreach (["arrest_date", "incarceration_date", "release_date", "in_exile_since", "end_of_exile"] as $f) {
    if (array_key_exists($f, $cs) && $applyDate($case, $f, $cs[$f])) {
        $cnotes[] = $f;
    }
}

foreach (["charges", "convicted", "sentence"] as $f) {
    if ($case->{$f} != $cs[$f]) {
        $case->{$f} = $cs[$f];
        $cnotes[] = $f;
    }
}

if ($isNew || $cnotes) {
    $case->save();
}

echo "  Portugal case", ($isNew ? " NEW" : ""), ": ",
    ($cnotes ? implode("; ", $cnotes) : "already correct"),
    "  days=", ($case->imprisoned_for_days ?? "null"),
    "  exile_days=", ($case->in_exile_for_days ?? "null"), " (must be 0)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-george-wright" fix_wright

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 75 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
