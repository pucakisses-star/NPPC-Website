#!/usr/bin/env bash
#
# BATCH 157 -- one curated link for the dashboard newswire.
#
#   2026-07-28  KATU  Woman pleads guilty to interference after
#                     allegedly painting Portland ICE driveway
#
#   Fetched and returned HTTP 200; the headline is the page own
#   og:title.
#
#   ON THE DATE. The page carries two timestamps that fall on
#   different days: article:published_time gives 2026-07-29T00:41:55Z
#   and the JSON-LD gives 2026-07-28T17:41:55-07:00. They are the same
#   instant, the first being the second in UTC. KATU is a Portland
#   station reporting a Portland story, so the day it published is
#   July 28 Pacific, and that is what is stored. Taking
#   article:published_time at face value would have filed it a day
#   late and sorted it above stories that actually came after it.
#
#   Checked against the 1,055 link URLs on the live page before
#   adding. Not a duplicate: the feed already holds one KATU story,
#   about a man charged with assaulting an ICE officer, which is a
#   different case.
#
#   No coordinates, so no map marker.
#
#   Idempotent: matched on URL, so a re-run refreshes rather than
#   duplicating.
#
# Run from the repo root, after git pull (after batch 156):
#   bash database/data/run-batch-157.sh

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
echo "  Batch 157 — dashboard newswire: one curated link"
echo "==================================================================="

add_links() {
    php artisan tinker --execute='
use App\Models\DashboardLink;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch157.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$before = DashboardLink::count();
$published = DashboardLink::published()->count();
$newest = DashboardLink::published()->orderByDesc("published_at")->first();

echo "before: ", $before, " links (", $published, " published)";
echo "  newest: ", ($newest && $newest->published_at ? $newest->published_at->format("Y-m-d") : "-"), "\n\n";

$made = 0;
$had = 0;

foreach ($payload["links"] as $row) {
    $link = DashboardLink::where("url", $row["url"])->first();

    if ($link) {
        $link->fill($row);
        $link->save();
        $had++;
        echo "  updated  ", $row["published_at"], "  ", $row["source"], "\n";
    } else {
        DashboardLink::create($row);
        $made++;
        echo "  added    ", $row["published_at"], "  ", $row["source"], "\n";
    }

    echo "           ", $row["title"], "\n";
    echo "           ", $row["url"], "\n";
}

$after = DashboardLink::count();
$newest = DashboardLink::published()->orderByDesc("published_at")->first();

echo "\nafter: ", $after, " links (", DashboardLink::published()->count(), " published)";
echo "  newest: ", ($newest && $newest->published_at ? $newest->published_at->format("Y-m-d") : "-"), "\n";
echo "  ", $made, " added, ", $had, " already present and refreshed.\n";

// The newswire hides anything before the tracker start, so an item dated
// earlier would be created and never appear.
$cut = \Illuminate\Support\Carbon::create(2025, 5, 7)->startOfDay();
$hidden = DashboardLink::published()->where("published_at", "<", $cut)->count();

echo "  links dated before the tracker start and therefore invisible: ", $hidden, "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "dashboard-links" add_links

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 157 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
echo
echo "Check the three links resolve on the live page before relying on"
echo "them. A newswire item is a citation, and a dead one is worse than"
echo "an absent one."
