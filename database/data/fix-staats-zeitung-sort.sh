#!/usr/bin/env bash
#
# Put Jacob Frohwerk and Carl Gleeser next to each other in the sort order.
#
# The two Missouri Staats-Zeitung co-defendants -- arrested together on
# January 26, 1918 -- sat some thousand positions apart (Frohwerk ~5602,
# Gleeser ~6621 at the time of writing). This moves Gleeser to the slot
# directly after Frohwerk, so the pair reads together in the archive:
# first the writer who fought to the Supreme Court, then the publisher
# who pleaded and testified.
#
# The move is a block shift, not a swap, so every sort value stays unique:
# the records between the two positions each move one step toward
# Gleeser's old slot, and Gleeser takes the freed position beside
# Frohwerk. Current positions are read at runtime, neighbours are printed
# before and after as a receipt, and the script exits without writing if
# the two are already adjacent.
#
# Run from the repo root:
#   bash database/data/fix-staats-zeitung-sort.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$frohwerk = Prisoner::withoutGlobalScopes()->where("slug", "jacob-frohwerk")->first();
$gleeser  = Prisoner::withoutGlobalScopes()->where("slug", "carl-gleeser")->first();
if (! $frohwerk || ! $gleeser) {
    echo "NOT FOUND: ".(! $frohwerk ? "jacob-frohwerk " : "").(! $gleeser ? "carl-gleeser" : "")."\n";
    exit(1);
}

$f = (int) $frohwerk->sort_order;
$g = (int) $gleeser->sort_order;
echo "before:  frohwerk sort={$f}   gleeser sort={$g}\n\n";

$neighbours = function (int $centre, string $label) {
    echo "{$label}:\n";
    Prisoner::withoutGlobalScopes()
        ->whereBetween("sort_order", [$centre - 2, $centre + 2])
        ->orderBy("sort_order")
        ->get(["sort_order", "name", "slug"])
        ->each(fn ($p) => print("  ".str_pad((string) $p->sort_order, 6)."{$p->name}  [{$p->slug}]\n"));
    echo "\n";
};

$neighbours($f, "around Frohwerk (before)");

if ($g === $f + 1) {
    echo "Already adjacent -- nothing to do.\n";
    exit(0);
}

$target = $f + 1;
if ($g > $target) {
    // Everyone from the slot after Frohwerk up to just below Gleeser
    // steps one place down the list; Gleeser takes the freed slot.
    Prisoner::withoutGlobalScopes()
        ->whereBetween("sort_order", [$target, $g - 1])
        ->increment("sort_order");
} else {
    // Gleeser currently sits above Frohwerk; the block between them steps
    // one place up, which also pulls Frohwerk to {$f - 1}, and Gleeser
    // lands directly after him.
    Prisoner::withoutGlobalScopes()
        ->whereBetween("sort_order", [$g + 1, $f])
        ->decrement("sort_order");
    $target = $f; // Frohwerk is now at $f - 1
}
$gleeser->sort_order = $target;
$gleeser->save();

$frohwerk->refresh();
echo "after:   frohwerk sort={$frohwerk->sort_order}   gleeser sort={$gleeser->sort_order}\n\n";
$neighbours((int) $frohwerk->sort_order, "around the pair (after)");

$dupes = Prisoner::withoutGlobalScopes()
    ->selectRaw("sort_order, COUNT(*) c")
    ->groupBy("sort_order")->havingRaw("COUNT(*) > 1")->count();
echo $dupes ? "WARNING: {$dupes} duplicated sort value(s) exist -- inspect!\n" : "Sort values verified unique.\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
