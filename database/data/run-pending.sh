#!/usr/bin/env bash
#
# Run every outstanding change from this round of work, in the order
# that makes them correct, and finish by placing the new records in the
# sort order.
#
# ORDER MATTERS, which is the whole point of this script:
#
#   * The five free-expression records must be CREATED before anything
#     can attach a photo to Judith Miller or give her a sort position.
#   * The Zimmer duplicate cleanup must run BEFORE the importer, so the
#     importer sees one copy of each force_new record and refreshes it
#     instead of finding several.
#   * prisoners:place-zero-sort-by-year must run AFTER every add and
#     after the Zimmer re-sort, or it places nothing.
#   * compact-sort-order runs AFTER placement, so unplaced records are
#     never laundered into real positions.
#   * prisoners:recompute-imprisonment runs LAST, because the day
#     counters are stored on the case rows and only recomputed when a
#     case is saved.
#
# ONE FAILING STEP DOES NOT ABORT THE RUN. Every step is wrapped: a
# failure is recorded and printed in a summary at the end, and the rest
# of the work still happens. (The first version of this script used
# plain set -e, and a single NOT FOUND from a script that had already
# run once stopped the entire pipeline before most of the corrections
# were reached.)
#
# Everything here is idempotent, so running the script twice is safe.
#
# Run from the repo root, after git pull:
#   bash database/data/run-pending.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

step() {
    echo
    echo "==================================================================="
    echo "  $1"
    echo "==================================================================="
}

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

# ---- 1. removals, before anything counts records ---------------------
step "Removals"
run "remove-ngo-civil-suit-defendants" bash database/data/remove-ngo-civil-suit-defendants.sh
if [ -f database/data/remove-matewan-hatfield-chambers.sh ]; then
    run "remove-matewan-hatfield-chambers" bash database/data/remove-matewan-hatfield-chambers.sh
fi
run "remove-non-portrait-photos" bash database/data/remove-non-portrait-photos.sh

# ---- 2. taxonomy -----------------------------------------------------
step "Taxonomy"
run "ideology-taxonomy-cleanup" bash database/data/ideology-taxonomy-cleanup.sh
run "merge-iww-affiliation" bash database/data/merge-iww-affiliation.sh
if [ -f database/data/merge-pacifism-into-anti-war.sh ]; then
    run "merge-pacifism-into-anti-war" bash database/data/merge-pacifism-into-anti-war.sh
fi

# ---- 3. creations, before placement ----------------------------------
step "Creations"
run "add-free-expression-five" bash database/data/add-free-expression-five.sh
run "remove-duplicate-zimmer-force-new" bash database/data/remove-duplicate-zimmer-force-new.sh
run "zimmer importer (dry run)" php artisan prisoners:add-zimmer-deportees
run "zimmer importer (apply)" php artisan prisoners:add-zimmer-deportees --apply

# ---- 4. per-prisoner corrections -------------------------------------
step "Per-prisoner corrections"
for s in fix-walter-matthey fix-david-elmakayes fix-eric-hafner fix-alissa-azar \
         set-alissa-azar-photo fix-cyril-lartigue fix-samantha-brooks \
         fix-george-meyers fix-jane-speed-de-andreu fix-aurelio-tolentino \
         fix-bradford-lyttle set-kolton-krottinger-photo \
         add-merrimack-four-photos fix-merrimack-four-custody \
         set-judith-miller-photo fix-lucy-fowlkes fix-sami-hamdi \
         fix-sofia-deferrari; do
    if [ -f "database/data/${s}.sh" ]; then
        run "${s}" bash "database/data/${s}.sh"
    else
        echo "  (missing: ${s}.sh)"
    fi
done

# ---- 5. placement and derived values, last ---------------------------
step "Placement and derived values"
run "resort-zimmer-deportees" bash database/data/resort-zimmer-deportees.sh
run "place-zero-sort-by-year" php artisan prisoners:place-zero-sort-by-year --apply
run "group-codefendants" php artisan prisoners:group-codefendants --apply
run "compact-sort-order" bash database/data/compact-sort-order.sh
run "recompute-imprisonment" php artisan prisoners:recompute-imprisonment --apply

step "Final state"
php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\DB;
$total = Prisoner::withoutGlobalScopes()->count();
$zero = Prisoner::withoutGlobalScopes()->where("sort_order", 0)->orWhereNull("sort_order")->count();
$distinct = DB::table("prisoners")->whereNotNull("sort_order")->distinct()->count("sort_order");
$photos = Prisoner::withoutGlobalScopes()->whereNotNull("photo")->count();
echo "Records: {$total}\n";
echo "Still at sort_order 0: {$zero}   (expect 0)\n";
echo "Distinct sort_order values: {$distinct}   (expect to equal the record count)\n";
echo "Records with a photo: {$photos}\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Cache cleared.\n";
'

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  All pending work applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
    echo "  Everything else was applied. Re-run after fixing, or report the output."
fi
echo "==================================================================="
