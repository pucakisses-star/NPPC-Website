#!/usr/bin/env bash
#
# Re-place the Zimmer deportees chronologically.
#
# The ~700 records created by the Red Scare Deportees import sit in a
# single block around sort 7000-8000, because the original import placed
# them together. Their median case year is 1919 -- the same years as the
# records sitting near sort 5000-6000 -- so the block leaves the
# archives chronology broken in the middle: a reader scrolling the
# 1918-1919 stretch meets them twice.
#
# The fix reuses the placement machinery instead of inventing a new one:
# every record whose description carries the "Adapted from Kenyon
# Zimmer" attribution -- the signature of a record the import owns, NOT
# carried by the pre-existing records it merely enriched (Goldman,
# Berkman, Steimer...) -- has its sort_order set to 0 here, and
# prisoners:place-zero-sort-by-year --apply then slots each one into the
# cohort its case year belongs to, affiliation cluster first. Records
# already interleaved (if any were hand-moved) are re-placed into the
# same neighbourhood, so the operation is safe to repeat.
#
# RUN THE PLACEMENT COMMAND AFTERWARDS or ~700 records sit at sort 0:
#   php artisan prisoners:place-zero-sort-by-year --apply
# (run-pending.sh runs this script and the placement in the right order.)
#
# Run from the repo root:
#   bash database/data/resort-zimmer-deportees.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\DB;

$owned = Prisoner::withoutGlobalScopes()
    ->where("description", "like", "%Adapted from Kenyon Zimmer%")
    ->get(["id", "slug", "sort_order"]);

$zeroed = 0;
foreach ($owned as $p) {
    if ((int) $p->sort_order === 0) {
        continue;
    }
    DB::table("prisoners")->where("id", $p->id)->update(["sort_order" => 0]);
    $zeroed++;
}

echo "Zimmer-owned records: ".$owned->count()."\n";
echo "sort_order reset to 0 on {$zeroed} of them.\n";
echo "\nNow run:  php artisan prisoners:place-zero-sort-by-year --apply\n";
echo "to interleave them with their contemporaries by case year.\n";
'

echo
echo "Done."
