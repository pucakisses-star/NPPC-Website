#!/usr/bin/env bash
#
# BATCH 149 -- three curated links added to the dashboard newswire.
#
#   THE FEED WAS NOT EMPTY. /dashboard already carried 1,105 items —
#   1,052 DashboardLinks and 53 Articles — running from the tracker
#   start of May 7, 2025 to August 2, 2026. What was missing was the
#   last few days, not bulk, so this adds three items rather than a
#   sweep.
#
#   EVERY ITEM IS A REAL ARTICLE. Each URL came back from a search,
#   was fetched and returned HTTP 200, and the headline, source and
#   date are as published. No headline is a paraphrase and no URL is
#   constructed. That matters more here than anywhere else in this
#   archive: the newswire is the one place the site publishes claims
#   about the outside world under its own name, and a dead link or an
#   invented headline is not a data error, it is a false citation.
#
#   NO COORDINATES. None of the three gets lat/lng, so none produces a
#   map marker. A pin asserts where an event happened; these are
#   national stories rather than located ones, and the map is for
#   events with places.
#
#   PUBLICATION TIME. The sources give a date but not a time, so each
#   is stored at noon. DashboardLink::published() compares
#   published_at to now(), and a midnight timestamp on the current day
#   would be indistinguishable from a backdated one in the ticker
#   ordering.
#
#   Idempotent: matched on URL, so a re-run updates rather than
#   duplicating.
#
# Run from the repo root, after git pull (after batch 148):
#   bash database/data/run-batch-149.sh

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
echo "  Batch 149 — dashboard newswire: three curated links"
echo "==================================================================="

add_links() {
    php artisan tinker --execute='
use App\Models\DashboardLink;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch149.json")), true);

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
    echo "  Batch 149 applied. No failures."
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
