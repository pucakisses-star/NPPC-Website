#!/usr/bin/env bash
#
# Remove the duplicate copies of the three force_new Zimmer deportees.
#
# The Zimmer importer marks three records force_new because their names
# collide with unrelated existing records (Johan Johanson, Carl Larson,
# Jose Angel Hernandez -- the collisions are a different 1930 Buffalo
# Carl Larson, the 2000s Vieques protester, and Grandmaster Jay). Until
# the guard added alongside this script, force_new also bypassed the
# check against the importer's OWN previous output, so every server run
# of prisoners:add-zimmer-deportees --apply created the three again:
# the console history shows "Created 3 record(s)" on run after run, with
# sort placements climbing 7689 -> 7712.
#
# This script deletes the extra copies. A copy is identified by BOTH the
# exact name AND the Zimmer attribution in the description, so the
# unrelated same-name records are untouchable by construction. For each
# name the EARLIEST-CREATED copy is kept (the one the fixed importer will
# find and refresh); every later copy loses its cases, its photo file
# (only when no surviving record points at that file), and the record
# itself. Records with calendar entries or podcast episodes are never
# deleted -- they get a warning instead, since linked content means a
# human has touched them.
#
# The script also repairs duplicate sort_order values (the recurring
# "Records: N  distinct sort_order values: N-1 ... Positions collided"
# line in place-zero-sort-by-year output). For each duplicated value the
# earliest-created record keeps it; everything above the value is shifted
# up by one and the later record slots in just after -- relative order is
# preserved exactly, nothing jumps to the end of the database.
#
# Idempotent: a second run finds nothing to delete and no collisions.
# Run from the repo root:
#   bash database/data/remove-duplicate-zimmer-force-new.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

$names = ["Johan Johanson", "Carl Larson", "José Ángel Hernández"];

$deleted = 0;
foreach ($names as $name) {
    $copies = Prisoner::withoutGlobalScopes()
        ->where("name", $name)
        ->where(function ($q) {
            $q->where("description", "like", "%Adapted from Kenyon Zimmer%")
                ->orWhere("description", "like", "%index of deportation case files%");
        })
        ->orderBy("created_at")
        ->get();

    if ($copies->isEmpty()) {
        echo "  {$name}: no Zimmer-owned record found (nothing to do)\n";
        continue;
    }

    $keep = $copies->first();
    echo "  {$name}: ".$copies->count()." Zimmer-owned cop".($copies->count() === 1 ? "y" : "ies")."; keeping {$keep->slug} (created {$keep->created_at})\n";

    foreach ($copies->slice(1) as $extra) {
        $calendar = DB::table("calendar_entries")->where("prisoner_id", $extra->id)->count();
        $podcasts = DB::table("podcast_episodes")->where("prisoner_id", $extra->id)->count();
        if ($calendar || $podcasts) {
            echo "    WARNING: {$extra->slug} has {$calendar} calendar entr(ies) and {$podcasts} podcast episode(s) -- NOT deleted, resolve by hand\n";
            continue;
        }

        $cases = $extra->cases()->count();
        $extra->cases()->delete();

        if ($extra->photo && $extra->photo !== $keep->photo) {
            $stillUsed = Prisoner::withoutGlobalScopes()
                ->where("photo", $extra->photo)
                ->where("id", "!=", $extra->id)
                ->exists();
            if (! $stillUsed && Storage::disk("public")->exists($extra->photo)) {
                Storage::disk("public")->delete($extra->photo);
            }
        }

        echo "    deleted {$extra->slug} (sort {$extra->sort_order}, {$cases} case(s))\n";
        $extra->delete();
        $deleted++;
    }
}
echo "\nDeleted {$deleted} duplicate record(s).\n";

// -- sort_order collisions ------------------------------------------------
// For each duplicated value: earliest-created keeps it, everything above
// shifts up one, the later record takes the freed slot right after.
$fixed = 0;
for ($round = 0; $round < 20; $round++) {
    $dup = DB::table("prisoners")
        ->select("sort_order")
        ->whereNotNull("sort_order")
        ->groupBy("sort_order")
        ->havingRaw("COUNT(*) > 1")
        ->orderBy("sort_order")
        ->value("sort_order");
    if ($dup === null) {
        break;
    }

    $rows = Prisoner::withoutGlobalScopes()
        ->where("sort_order", $dup)
        ->orderBy("created_at")
        ->get(["id", "slug", "name", "created_at"]);
    $mover = $rows->last();
    echo "  sort collision at {$dup}: ".$rows->pluck("slug")->implode(", ")."; moving {$mover->slug} to ".($dup + 1)."\n";

    DB::table("prisoners")->where("sort_order", ">", $dup)->increment("sort_order");
    DB::table("prisoners")->where("id", $mover->id)->update(["sort_order" => $dup + 1]);
    $fixed++;
}
echo "Fixed {$fixed} sort_order collision(s).\n";

$total = Prisoner::withoutGlobalScopes()->count();
$distinct = DB::table("prisoners")->whereNotNull("sort_order")->distinct()->count("sort_order");
$nulls = Prisoner::withoutGlobalScopes()->whereNull("sort_order")->count();
echo "\nRecords: {$total}  distinct sort_order values: {$distinct}  null sorts: {$nulls}\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
