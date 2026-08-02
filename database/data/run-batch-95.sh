#!/usr/bin/env bash
#
# BATCH 95 -- Barry Cooper: phantom Tennessee prison detached, exile
# counter started.
#
#   His case row was linked to WEST TENNESSEE STATE PENITENTIARY
#   (Henning) complete with prison mailing and physical addresses —
#   an import artifact: Cooper was never imprisoned; he fled to
#   Brazil in 2011 and lives there in exile. The institution link and
#   both address fields are cleared.
#
#   His exile flags were on but the case had no in_exile_since, so
#   the counter read zero. Set to 2011 (year precision, per his bio:
#   "In 2011 Cooper and his family fled to Brazil"), no end date —
#   about 15 years and running.
#
#   NOTE FOR THE CURATOR: ten OTHER records share the same West
#   Tennessee State Penitentiary institution link, which looks like
#   an old import placeholder — basheer-hameed,
#   gerardo-hernandez-nordelo, herman-wallace, brandon-baxter,
#   dion-ortiz, gary-johnson, darius-fullmer, helen-woodson,
#   joseph-patrick-doherty, christopher-trotter. Several are
#   demonstrably wrong (Wallace was at Angola, Hernandez in federal
#   custody, Trotter in Indiana). Left unchanged pending direction.
#
# Idempotent throughout.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-95.sh

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
echo "  Batch 95 — Barry Cooper: phantom prison removed, exile started"
echo "==================================================================="

fix_cooper() {
    php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withUnderReview()->where("slug", "barry-cooper")->with("cases")->first();

if (! $p) {
    echo "NOT FOUND: barry-cooper\n";
    return;
}

echo $p->slug, "\n";

$case = $p->cases->sortBy("created_at")->first();

if (! $case) {
    echo "  no case row — nothing to do\n";
} else {
    $case->setRelation("prisoner", $p);
    $notes = [];

    if ($case->institution_id !== null) {
        $case->institution_id = null;
        $notes[] = "institution detached (was the phantom West Tennessee State Penitentiary link)";
    }

    foreach (["mailing_address", "physical_address"] as $f) {
        if ($case->{$f}) {
            $case->{$f} = null;
            $notes[] = $f." cleared";
        }
    }

    $was = $case->in_exile_since ? $case->in_exile_since->format("Y-m-d") : null;

    if (! $was || substr($was, 0, 4) !== "2011") {
        $case->setPartialDate("in_exile_since", 2011);
        $notes[] = "in_exile_since=2011 (year precision)".($was ? " (was ".$was.")" : "");
    }

    if ($case->end_of_exile !== null) {
        $case->setPartialDate("end_of_exile", null);
        $notes[] = "end_of_exile cleared";
    }

    $case->save();

    echo "  case: ", ($notes ? implode("; ", $notes) : "already correct"), "\n";
    echo "    exile days=", ($case->in_exile_for_days ?? "null"), " (~15 years, running)\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "fix-barry-cooper" fix_cooper

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 95 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
