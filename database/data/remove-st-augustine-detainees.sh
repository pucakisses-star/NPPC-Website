#!/usr/bin/env bash
#
# Remove the four St. Augustine detainees -- South Carolina revolutionaries
# seized by BRITISH forces after the fall of Charleston in 1780 and held at
# the Castillo de San Marcos. They were arrested by British authorities, not
# American ones, so they fall outside the scope of the database:
#
#   Christopher Gadsden   [christopher-gadsden]
#   Arthur Middleton      [arthur-middleton]
#   Thomas Heyward Jr.    [thomas-heyward-jr]
#   Edward Rutledge       [edward-rutledge]
#
# Scoped to exactly these four slugs -- it will not touch anything else the
# classifier may have grouped with them. Each record is printed before it is
# removed, and a slug that is already gone is reported rather than failing.
#
# Deletes by default. REVIEW=1 hides them instead (under_review = true), which
# keeps the data and is reversible from the admin.
#
#   bash database/data/remove-st-augustine-detainees.sh
#   REVIEW=1 bash database/data/remove-st-augustine-detainees.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

REVIEW="${REVIEW:-0}" php artisan tinker --execute='
use App\Models\Prisoner;

$review = getenv("REVIEW") === "1";
$slugs = ["christopher-gadsden", "arthur-middleton", "thomas-heyward-jr", "edward-rutledge"];

$done = 0;
foreach ($slugs as $slug) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->with("cases")->first();
    if (! $p) { echo "{$slug}: not found (already removed?)\n"; continue; }

    echo "{$p->name}  [{$p->slug}]  sort={$p->sort_order}  cases=".$p->cases->count()."\n";
    $inst = $p->cases->map(fn ($c) => optional($c->institution)->name)->filter()->unique()->implode("; ");
    if ($inst) { echo "    institution: {$inst}\n"; }

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
