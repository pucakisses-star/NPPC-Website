#!/usr/bin/env bash
#
# BATCH 128 -- Josephine Sunshine Overaker, per the curator:
#   list her as currently in exile, add a photo, add her date of birth.
#
#   EXILE. currently_in_exile is set, which also derives
#   imprisoned_or_exiled and puts her back into the site's
#   currently-imprisoned-or-exiled lists. in_exile_since is NOT set:
#   the FBI says she left the United States shortly after the
#   December 2005 Operation Backfire arrests, the biography already on
#   the record says she fled before them, and neither gives a day. The
#   badge and the lists work without it; only the running counter is
#   absent, which is right for an exile whose start is unknown.
#
#   PHOTO. The portrait from her FBI wanted poster (Portland field
#   office) — a US government work, public domain — attaches into her
#   empty photo slot at the standard 525x700 panel. Credit and the
#   note on which of the poster four images was used, and why not the
#   age-progression or the tattoo photograph, are in
#   database/data/CREDITS-batch-128.md.
#
#   DATE OF BIRTH -- NOT SET, DELIBERATELY. The wanted poster is the
#   only source carrying a birth date and it gives THREE, under the
#   heading "Date(s) of Birth Used": November 19, 1974; October 4,
#   1971; November 4, 1971. On an FBI notice that heading means dates
#   the subject has been recorded using, not a verified birth date,
#   and two of her aliases are full alternate identities (Lisa
#   Rachelle Quintana, Maria Rachelle Quintana) which is where at
#   least two of those dates come from. Entering one of the three
#   would assert what no source supports, so all three go into the
#   biography and the field stays empty. See the birthdate block in
#   database/data/fixes/batch128.json. If the curator wants November
#   19, 1974 stored, it is a one-line follow-up.
#
#   Also filled from the same poster, empty fields only: her aliases
#   (which are what make the birth date unresolvable) and the
#   January 19, 2006 indictment.
#
#   Nothing is deleted; the biography is appended to. Idempotent.
#
# Run from the repo root, after git pull (after batch 127):
#   bash database/data/run-batch-128.sh

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
echo "  Batch 128 — Overaker: currently in exile, FBI poster portrait"
echo "==================================================================="

update_overaker() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    if [ -f "database/data/photos/josephine-sunshine-overaker.jpg" ]; then
        cp -f "database/data/photos/josephine-sunshine-overaker.jpg" "${DST_DIR}/josephine-sunshine-overaker.jpg"
        echo "portrait copied to ${DST_DIR}/josephine-sunshine-overaker.jpg"
    else
        echo "!! portrait missing from database/data/photos — the record will keep its empty photo slot"
    fi

    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch128.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->with("cases")->first();

if (! $p) { echo $payload["slug"], " NOT FOUND — nothing changed.\n"; return; }

echo "record: ", $p->name, "  [", $p->slug, "]\n";
echo "  before: in_exile=", var_export((bool) $p->in_exile, true),
    "  currently_in_exile=", var_export((bool) $p->currently_in_exile, true),
    "  imprisoned_or_exiled=", var_export((bool) $p->imprisoned_or_exiled, true),
    "  photo=", ($p->photo ?: "(none)"),
    "  birthdate=", ($p->birthdate ? $p->formatPartialDate("birthdate") : "(none)"), "\n";

$notes = [];

if (! $p->currently_in_exile) { $p->currently_in_exile = true; $notes[] = "currently_in_exile=true"; }
if (! $p->in_exile) { $p->in_exile = true; $notes[] = "in_exile=true"; }

// Aliases, empty field only.
if (! $p->aka && ! empty($payload["aka"])) {
    $p->aka = $payload["aka"];
    $notes[] = "aka set from the FBI notice";
}

// Photo into the empty slot only — an existing portrait is never replaced.
$rel = "prisoners/josephine-sunshine-overaker.jpg";
if (! $p->photo && is_file(storage_path("app/public/".$rel))) {
    $p->photo = $rel;
    $notes[] = "photo attached";
}

$append = $payload["bio_append"] ?? null;
if ($append && strpos((string) $p->description, "Date(s) of Birth Used") === false
    && strpos((string) $p->description, "dates of birth used") === false) {
    $p->description = trim((string) $p->description)." ".$append;
    $notes[] = "biography appended (birth dates, place of birth, indictment, reward)";
}

if ($notes) { $p->save(); }

$p->refresh()->load("cases");

echo "  ", implode("\n  ", $notes ?: ["already correct"]), "\n";
echo "  after:  in_exile=", var_export((bool) $p->in_exile, true),
    "  currently_in_exile=", var_export((bool) $p->currently_in_exile, true),
    "  imprisoned_or_exiled=", var_export((bool) $p->imprisoned_or_exiled, true),
    "  photo=", ($p->photo ?: "(none)"), "\n";

// The indictment date, empty field only, on the Operation Backfire case row.
$case = $p->cases->first();

if (! $case) { echo "  case: NONE — the indictment date was not written.\n"; }
elseif (! $case->indicted && ! empty($payload["indicted"])) {
    $case->indicted = $payload["indicted"];
    $case->save();
    echo "  case: indicted=", $case->indicted, "\n";
} else {
    echo "  case: indicted already set (", ($case->indicted ?: "empty, no value in payload"), ")\n";
}

echo "\n  BIRTHDATE NOT SET — deliberately.\n  ",
    wordwrap($payload["birthdate"]["reason"], 88, "\n  "), "\n";
echo "\n  IN_EXILE_SINCE NOT SET — deliberately.\n  ",
    wordwrap($payload["in_exile_since"]["reason"], 88, "\n  "), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "overaker-update" update_overaker

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 128 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
