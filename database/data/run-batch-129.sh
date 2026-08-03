#!/usr/bin/env bash
#
# BATCH 129 -- Josephine Sunshine Overaker date of birth resolved,
# per the curator: find her most likely date of birth.
#
#   NOVEMBER 19, 1974, entered at day precision.
#
#   Batch 128 left the field empty because the FBI wanted poster
#   lists three dates under "Date(s) of Birth Used" — November 19,
#   1974; October 4, 1971; November 4, 1971 — without saying which is
#   hers. The FBI's own age-progressed photograph settles which one
#   the Bureau works from:
#
#     The age progression was released in December 2015 and depicts
#     her AT 41 YEARS OF AGE. She is 41 in December 2015 only under
#     the November 19, 1974 date, which she reached a few weeks
#     earlier on November 19, 2015. Under either 1971 date she was
#     44 at that moment, and had turned 41 back in the autumn of
#     2012.
#
#   Supporting: November 19, 1974 is the date the poster lists first,
#   which on an FBI notice is normally the primary identity; and the
#   two 1971 dates differ only by a month, reading as one alias
#   identity recorded two ways rather than two independent
#   attestations, which fits the two Quintana identities in her alias
#   list.
#
#   NOT CERTAIN, and the record says so: federal law enforcement was
#   still describing her in April 2022 as "either 47 or 50 years
#   old", so the 1971 branch has never been formally abandoned, and
#   the poster heading remains "dates used" rather than a verified
#   birth record. The biography carries all three dates and this
#   reasoning; the batch 128 sentence saying no date was entered is
#   surgically replaced rather than left to contradict the field.
#
#   ALSO: federal law enforcement, quoted April 2022, believes she
#   fled to Europe in LATE 2001 — better than the accounts batch 128
#   found in conflict. It goes into the biography. It is still NOT
#   written to in_exile_since; see the in_exile_since block in
#   database/data/fixes/batch129.json for why.
#
#   The prisoner age column is derived from birthdate on save, so it
#   fills itself.
#
#   Idempotent, and safe to run whether or not batch 128 has been
#   applied: the biography edit falls back to a standalone paragraph
#   when the batch 128 sentence is not present.
#
# Run from the repo root, after git pull (after batch 128):
#   bash database/data/run-batch-129.sh

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
echo "  Batch 129 — Overaker date of birth: November 19, 1974"
echo "==================================================================="

set_birthdate() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch129.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->first();

if (! $p) { echo $payload["slug"], " NOT FOUND — nothing changed.\n"; return; }

echo "record: ", $p->name, "  [", $p->slug, "]\n";
echo "  before: birthdate=", ($p->birthdate ? $p->formatPartialDate("birthdate") : "(none)"),
    "  age=", ($p->age ?? "(none)"), "\n";

$notes = [];
$b = $payload["birthdate"];

$was = $p->birthdate ? $p->birthdate->format("Y-m-d") : null;
$target = sprintf("%04d-%02d-%02d", $b["year"], $b["month"], $b["day"]);

if ($was !== $target || $p->datePrecisionFor("birthdate") !== "day") {
    $p->setPartialDate("birthdate", $b["year"], $b["month"], $b["day"]);
    $notes[] = "birthdate=".$target." [day]".($was ? " (was ".$was.")" : "");
}

// The batch 128 biography says no date was entered. Replace that clause
// rather than leave it contradicting the field now that one is. If the
// clause is absent — batch 128 not applied, or the text since edited —
// append the standalone paragraph instead. Nothing is deleted beyond the
// one superseded clause.
$marker = $payload["bio_marker"];
$desc = (string) $p->description;

if (strpos($desc, "the one entered here") !== false) {
    // already carries the resolution
} elseif (strpos($desc, $marker) !== false) {
    $p->description = str_replace($marker, $payload["bio_replacement"], $desc);
    $notes[] = "biography: superseded clause replaced with the birth-date reasoning";
} else {
    $p->description = trim($desc)." ".$payload["bio_fallback"];
    $notes[] = "biography: birth-date reasoning appended (batch 128 text not found)";
}

if (strpos((string) $p->description, "fled to Europe in late 2001") === false) {
    $p->description = trim((string) $p->description)." ".$payload["bio_flight"];
    $notes[] = "biography: late-2001 flight to Europe appended";
}

if ($notes) { $p->save(); }

$p->refresh();

echo "  ", implode("\n  ", $notes ?: ["already correct"]), "\n";
echo "  after:  birthdate=", ($p->birthdate ? $p->formatPartialDate("birthdate") : "(none)"),
    "  age=", ($p->age ?? "(none)"), "  (derived on save)\n";

echo "\n  BASIS\n  ", wordwrap($payload["basis"], 88, "\n  "), "\n";
echo "\n  IN_EXILE_SINCE STILL NOT SET — deliberately.\n  ",
    wordwrap($payload["in_exile_since"]["reason"], 88, "\n  "), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "overaker-birthdate" set_birthdate

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 129 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
