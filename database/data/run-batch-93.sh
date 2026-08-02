#!/usr/bin/env bash
#
# BATCH 93 -- Edward Snowden: bogus 1995 release date cleared, exile
# pinned to his 2013 flight.
#
#   His case row carried RELEASE DATE NOVEMBER 15, 1995 — an import
#   artifact (he was twelve years old in 1995, and the row has no
#   arrest or incarceration; he has never been in U.S. custody). The
#   auto-derive hook had seeded in_exile_since from that bogus
#   release, so his public exile counter read 30+ years instead of
#   ~13.
#
#   FIX: release_date cleared; in_exile_since set explicitly to MAY
#   20, 2013 — the day he left the United States for Hong Kong — with
#   no end (asylum in Russia continues), so the counter runs
#   correctly from 2013. The person-level released flag turns OFF
#   (never in custody, never released); in_exile / currently_in_exile
#   stay on. A short sentence text records the facts so the empty
#   custody fields read as deliberate.
#
# Idempotent throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-93.sh

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
echo "  Batch 93 — Snowden: 1995 release artifact cleared, exile fixed"
echo "==================================================================="

fix_snowden() {
    php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withUnderReview()->where("slug", "edward-snowden")->with("cases")->first();

if (! $p) {
    echo "NOT FOUND: edward-snowden\n";
    return;
}

echo $p->slug, "\n";

$notes = [];

if ($p->released) {
    $p->released = false;
    $notes[] = "released=false (never in U.S. custody)";
}

if (! $p->in_exile) {
    $p->in_exile = true;
    $notes[] = "in_exile=true";
}

if (! $p->currently_in_exile) {
    $p->currently_in_exile = true;
    $notes[] = "currently_in_exile=true";
}

if ($notes) {
    $p->save();
}

echo "  person: ", ($notes ? implode("; ", $notes) : "already correct"), "\n";

$case = $p->cases->sortBy("created_at")->first();

if (! $case) {
    echo "  no case row — nothing more to do\n";
} else {
    $case->setRelation("prisoner", $p);
    $cnotes = [];

    if ($case->release_date !== null) {
        $was = $case->release_date->format("Y-m-d");
        $case->setPartialDate("release_date", null);
        $cnotes[] = "release_date cleared (was ".$was." — an import artifact; he has never been in U.S. custody)";
    }

    $wasEx = $case->in_exile_since ? $case->in_exile_since->format("Y-m-d") : null;

    if ($wasEx !== "2013-05-20") {
        $case->setPartialDate("in_exile_since", 2013, 5, 20);
        $cnotes[] = "in_exile_since=2013-05-20".($wasEx ? " (was ".$wasEx.")" : "");
    }

    if ($case->end_of_exile !== null) {
        $case->setPartialDate("end_of_exile", null);
        $cnotes[] = "end_of_exile cleared";
    }

    $sentence = "Never in U.S. custody — indicted June 13, 2013 under the Espionage Act after fleeing the United States on May 20, 2013; granted political asylum in Russia, where he remains.";

    if ($case->sentence != $sentence) {
        $case->sentence = $sentence;
        $cnotes[] = "sentence text";
    }

    // Save regardless so the stored exile counter recomputes.
    $case->save();

    echo "  case: ", ($cnotes ? implode("; ", $cnotes) : "already correct"), "\n";
    echo "    exile days=", ($case->in_exile_for_days ?? "null"), " (~13 years, running)\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-edward-snowden" fix_snowden

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 93 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
