#!/usr/bin/env bash
#
# Remove Sid Hatfield and Ed Chambers.
#
# Both were prosecuted after the May 19, 1920 Battle of Matewan and both were
# assassinated by Baldwin-Felts gunmen on the McDowell County courthouse steps
# at Welch on August 1, 1921 -- but neither record documents any imprisonment.
# They were acquitted in the Matewan murder trial, Hatfield continued serving
# as Matewan police chief throughout it, and both were at liberty and arriving
# to answer charges when they were killed. Political prosecution and
# assassination, not incarceration, so out of scope for the database.
#
# Matched by slug with a name fallback, and each record is printed -- including
# any case dates it holds -- before removal, so the output shows exactly what
# went and would reveal any custody data worth reconsidering first.
#
# Deletes by default. REVIEW=1 hides them instead (under_review = true), which
# keeps the research and is reversible from the admin.
#
#   bash database/data/remove-matewan-hatfield-chambers.sh
#   REVIEW=1 bash database/data/remove-matewan-hatfield-chambers.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

REVIEW="${REVIEW:-0}" php artisan tinker --execute='
use App\Models\Prisoner;

$review = getenv("REVIEW") === "1";

$targets = [
    ["sid-hatfield", "sid hatfield"],
    ["ed-chambers", "ed chambers"],
];

$done = 0;
foreach ($targets as [$slug, $name]) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->with("cases")->first()
        ?? Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", [$name])->with("cases")->first();
    if (! $p) { echo "{$slug}: not found (already removed?)\n"; continue; }

    echo "{$p->name}  [{$p->slug}]  sort={$p->sort_order}  cases=".$p->cases->count()."\n";
    foreach ($p->cases as $c) {
        echo "    case inc=".($c->incarceration_date ?: "-")." rel=".($c->release_date ?: "-")
            ." days=".($c->imprisoned_for_days ?? "null")."  ".substr((string) $c->charges, 0, 60)."\n";
    }

    if ($review) {
        $p->under_review = true;
        $p->save();
        echo "    hidden (under_review = true) -- reversible in the admin\n";
    } else {
        $n = $p->cases()->count();
        $p->delete();
        echo "    deleted, with {$n} case(s)\n";
    }
    $done++;
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone. {$done} record(s) ".($review ? "hidden" : "deleted").".\n";
'

echo
echo "Done."
