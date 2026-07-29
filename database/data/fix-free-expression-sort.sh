#!/usr/bin/env bash
#
# Put the free-expression five where their dates belong.
#
# Four of the five landed at the TOP of a newest-first list:
#
#   sort 1  John Peter Zenger  1734    should sit near the bottom
#   sort 2  William F. Davis   1887    should sit around 7,500
#   sort 3  Ralph Ginzburg     1972    should sit around 2,700
#   sort 4  Judith Miller      2005    should sit around 1,900
#
# James Franklin, the fifth, was placed correctly at 8298 -- so the
# import itself is fine and something in the placement pass put these
# four at slot 1 one after another, each pushing the previous one down,
# which is exactly the 1-2-3-4 in oldest-to-newest order the admin list
# shows. THE PRECISE CAUSE IS NOT PROVEN, so this script does not rely
# on re-running the placement command; it moves each record to a slot
# derived from the live data and then checks the result.
#
# HOW THE TARGETS WERE DERIVED, from the live list:
#
#   Miller    2005   60 records share the year, at sorts 1156-1880;
#                    she goes immediately after the last of them
#   Ginzburg  1972   59 records share the year, at sorts 2163-2745;
#                    he goes immediately after the last of them
#   Davis     1887   one record shares the year, Elmina Slenker at
#                    7527; he goes immediately after her
#   Zenger    1734   nothing shares the year. Samuel Seabury (1775)
#                    sits at 8297 and James Franklin (1722) at 8298, so
#                    1734 belongs between them -- Zenger takes
#                    Franklin{39}s slot and Franklin moves down one
#
# ANCHORS ARE SLUGS, NOT NUMBERS. Each target is recomputed from the
# anchor record{39}s CURRENT sort at the moment of the move, so the
# shifting caused by one move cannot corrupt the next, and the script
# stays correct if the list has moved on since these numbers were read.
#
# THE FOUR ARE VACATED FIRST. They are set to sort_order 0 and the rest
# of the list is closed up, so their old positions at the top do not
# leave holes and the anchors settle before anything is inserted.
#
# Idempotent: a re-run finds each record already beside its anchor and
# reports no change.
#
# Run from the repo root:
#   bash database/data/fix-free-expression-sort.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\DB;

// slug => [anchor slug, "after" | "at"]
$moves = [
    "judith-miller"     => ["matthew-crozier", "after"],
    "ralph-ginzburg"    => ["reuben-taylor",   "after"],
    "william-f-davis"   => ["elmina-slenker",  "after"],
    "john-peter-zenger" => ["james-franklin",  "at"],
];

$get = fn ($slug) => Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();

// --- 1. check everything exists before touching the ordering ---------
$missing = [];
foreach ($moves as $slug => [$anchor, $mode]) {
    if (! $get($slug))  { $missing[] = $slug; }
    if (! $get($anchor)) { $missing[] = $anchor." (anchor)"; }
}
if ($missing) {
    echo "ABORTING -- not found: ".implode(", ", $missing)."\n";
    exit(1);
}

// --- 2. vacate the four, then close the gaps -------------------------
foreach (array_keys($moves) as $slug) {
    $p = $get($slug);
    echo "  vacating ".str_pad($slug, 20)." from sort ".$p->sort_order."\n";
    DB::table("prisoners")->where("id", $p->id)->update(["sort_order" => 0]);
}

$rows = DB::table("prisoners")->where("sort_order", ">", 0)
    ->orderBy("sort_order")->orderBy("created_at")->get(["id", "sort_order"]);
$next = 1;
foreach ($rows as $r) {
    if ((int) $r->sort_order !== $next) {
        DB::table("prisoners")->where("id", $r->id)->update(["sort_order" => $next]);
    }
    $next++;
}
echo "\n  list closed up to ".($next - 1)." positions\n\n";

// --- 3. insert each beside its anchor, recomputed live ---------------
foreach ($moves as $slug => [$anchorSlug, $mode]) {
    $p = $get($slug);
    $anchor = $get($anchorSlug);
    $target = $mode === "after" ? ((int) $anchor->sort_order + 1) : (int) $anchor->sort_order;

    DB::table("prisoners")->where("id", "!=", $p->id)
        ->where("sort_order", ">=", $target)->increment("sort_order");
    DB::table("prisoners")->where("id", $p->id)->update(["sort_order" => $target]);

    echo "  ".str_pad($slug, 20)." -> sort ".str_pad((string) $target, 6)
        ." ({$mode} {$anchorSlug}, which is at ".$get($anchorSlug)->sort_order.")\n";
}

// --- 4. verify: each should now sit beside records of its own era ----
echo "\n";
foreach (array_keys($moves) as $slug) {
    $p = $get($slug);
    $above = Prisoner::withoutGlobalScopes()->where("sort_order", $p->sort_order - 1)->first();
    $below = Prisoner::withoutGlobalScopes()->where("sort_order", $p->sort_order + 1)->first();
    echo str_pad($p->name, 20)." sort ".str_pad((string) $p->sort_order, 6)." era ".str_pad((string) $p->era, 7)."\n";
    echo "   above: ".($above ? $above->name." (".$above->era.")" : "-")."\n";
    echo "   below: ".($below ? $below->name." (".$below->era.")" : "-")."\n";
}

$total = Prisoner::withoutGlobalScopes()->count();
$zero = Prisoner::withoutGlobalScopes()->where("sort_order", 0)->orWhereNull("sort_order")->count();
$distinct = DB::table("prisoners")->where("sort_order", ">", 0)->distinct()->count("sort_order");
echo "\nRecords {$total}; at sort 0: {$zero} (expect 0); distinct sorts: {$distinct} (expect {$total})\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
