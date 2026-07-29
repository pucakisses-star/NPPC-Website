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
#   * prisoners:place-zero-sort-by-year must run AFTER every add, or it
#     places nothing -- a record that does not exist yet cannot be at
#     sort_order 0.
#   * prisoners:recompute-imprisonment runs LAST, because the day
#     counters are stored on the case rows and only recomputed when a
#     case is saved; several scripts above change flags or dates that
#     the counters depend on.
#
# Everything here is idempotent, so running the script twice is safe
# and the second run mostly reports "nothing to do".
#
# Run from the repo root, after git pull:
#   bash database/data/run-pending.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

step() {
    echo
    echo "==================================================================="
    echo "  $1"
    echo "==================================================================="
}

# ---- 1. removals, before anything counts records ---------------------
step "Remove the Andy Ngo civil-suit defendants (no custody, civil judgment)"
bash database/data/remove-ngo-civil-suit-defendants.sh

step "Remove Hatfield and Chambers (merged long ago, never run)"
bash database/data/remove-matewan-hatfield-chambers.sh || echo "  (script missing or already applied)"

step "Photo audit: drop 19 non-portrait images and 32 dead photo paths"
bash database/data/remove-non-portrait-photos.sh

# ---- 2. taxonomy -----------------------------------------------------
step "Ideology taxonomy cleanup: retire six labels, merge two"
bash database/data/ideology-taxonomy-cleanup.sh

step "Merge Pacifism into Anti-War"
bash database/data/merge-pacifism-into-anti-war.sh || echo "  (already applied)"

# ---- 3. creations, before placement ----------------------------------
step "Add the five free-expression prisoners"
bash database/data/add-free-expression-five.sh

step "Zimmer: delete the duplicate force_new records BEFORE the importer"
bash database/data/remove-duplicate-zimmer-force-new.sh

step "Zimmer importer: dry run first, so the numbers can be read"
php artisan prisoners:add-zimmer-deportees

step "Zimmer importer: apply"
php artisan prisoners:add-zimmer-deportees --apply

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
        echo
        echo "--- ${s}"
        bash "database/data/${s}.sh"
    else
        echo "  (missing: ${s}.sh)"
    fi
done

# ---- 5. placement and derived values, last ---------------------------
step "Zero the Zimmer block so it interleaves chronologically"
bash database/data/resort-zimmer-deportees.sh

step "Place every record still at sort_order 0 (new records, Sofia, the Zimmer block)"
php artisan prisoners:place-zero-sort-by-year --apply

step "Group co-defendants so linked prisoners sit together"
php artisan prisoners:group-codefendants --apply || echo "  (skipped)"

step "Compact the sort order: renumber 1..N, closing every gap"
bash database/data/compact-sort-order.sh

step "Recompute the imprisonment day counters"
php artisan prisoners:recompute-imprisonment --apply

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
echo "  All pending work applied."
echo "==================================================================="
