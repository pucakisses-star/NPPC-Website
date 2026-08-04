#!/usr/bin/env bash
#
# BATCH 147 -- restore Walter Brown, removed in error by batch 146.
#
#   THE ERROR. Batch 146 removed him on the ground that "no membership
#   of the union or other political activity of his own is recorded".
#   His record reads: framed on a rape charge in the 1935 Birmingham,
#   Alabama terror against the Share Croppers Union. The Share
#   Croppers Union was a Communist-led union of Black tenant farmers
#   in the Alabama black belt, and the Birmingham repression was a
#   campaign directed at it. A prosecution mounted as part of a
#   campaign to destroy a union is political whether or not the
#   defendant's own membership is on paper. The removal took the
#   absence of a membership card for the absence of politics and
#   ignored the sentence it sat in.
#
#   HE IS THE ONLY ONE OF THE SIXTEEN with that feature. The other
#   fifteen were re-checked: none of their records names an
#   organisation that the prosecution was directed against. In those
#   the political actor really is only the defence campaign. So this
#   restores one record and does not reopen the batch.
#
#   The record is recreated with its original fields — name, state,
#   race, gender, era, sort order 5068, and its single case row with
#   the twenty-year sentence — and with the union context written into
#   the biography, so a later audit reads it correctly instead of
#   flagging it again. The slug regenerates as walter-brown, the
#   original having been freed by the deletion, so the old URL works.
#
#   Idempotent: if the record already exists it is left alone.
#
# Run from the repo root, after git pull (after batch 146):
#   bash database/data/run-batch-147.sh

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
echo "  Batch 147 — restore Walter Brown (Share Croppers Union)"
echo "==================================================================="

restore_record() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch147.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$row = $payload["restore"];

$existing = Prisoner::withUnderReview()->where("name", $row["name"])->first();

if ($existing) {
    echo "  ", $row["name"], " already present [", $existing->slug, "] — not recreated\n";
    echo "  If this is a different Walter Brown, the restore has not happened. Check it.\n";

    return;
}

$code = Artisan::call("prisoner:add", ["json" => json_encode($row)]);

$p = Prisoner::withUnderReview()->where("name", $row["name"])->with("cases")->first();

if (! $p) { echo "  RESTORE FAILED (exit ", $code, ")\n"; return; }

echo "  restored: ", $p->name, "  [", $p->slug, "]\n";
echo "    state=", ($p->state ?: "-"), "  race=", ($p->race ?: "-"),
    "  gender=", ($p->gender ?: "-"), "  era=", ($p->era ?: "-"),
    "  sort_order=", $p->sort_order, "\n";
echo "    case rows: ", $p->cases->count(), "\n";

foreach ($p->cases as $c) {
    echo "      charges: ", $c->charges, "\n";
    echo "      days:    ", ($c->imprisoned_for_days ?? "null"), "\n";
}

if ($p->slug !== $payload["expected_slug"]) {
    echo "\n  NOTE: slug is ", $p->slug, ", not ", $payload["expected_slug"],
        " — something else holds the original. The old URL will not resolve.\n";
} else {
    echo "\n  slug matches the original, so /prisoner/", $p->slug, " works again.\n";
}

echo "\n  biography:\n  ", wordwrap($p->description, 84, "\n  "), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "restore-walter-brown" restore_record

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 147 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
