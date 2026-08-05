#!/usr/bin/env bash
#
# BATCH 150 -- eighteen curated links supplied by the curator.
#
#   EVERY URL WAS FETCHED AND RETURNED HTTP 200, and the headline,
#   publication date and outlet are taken from each page's own
#   metadata rather than from the URL slug or from a search summary.
#   Where the two disagreed the metadata won: the Los Angeles Times
#   pieces carrying 2026-05-06 and 2026-08-03 in their URLs publish as
#   2026-05-07 and 2026-08-04. AMP suffixes and tracking parameters
#   were stripped before storing.
#
#   ONE REPLACEMENT. Batch 149 added the "She protested ICE raids"
#   story from KPBS, which carries it as a syndicated copy. The
#   curator has now supplied the NPR original. The original is added
#   and the KPBS duplicate removed, rather than leaving the same
#   headline in the newswire twice.
#
#   THREE SKIPPED, ALL BLOCKED RATHER THAN MISSING. VPM, KTLA and KGW
#   returned 403 to the fetch, so their headlines and dates could not
#   be read from the page and are not invented here. The VPM piece
#   would have been dropped anyway: its URL dates it to June 24, 2024,
#   before the tracker start of May 7, 2025, so the newswire would
#   have filtered it out and it would have been created and never
#   seen. All three are listed with their reasons by this script.
#
#   SOME OF THESE COVER ONE ANOTHER. The Mercury News piece of July 23
#   and the Los Angeles Times piece of July 22 look like the same
#   cinder-block sentencing; the LA Magazine, Ventura County Star and
#   Los Angeles Times items about a Westlake man and a CHP car may be
#   one defendant across three outlets. Several outlets on one story
#   is normal in a newswire and none is removed, but the feed should
#   not be read as a count of separate cases.
#
#   Idempotent: matched on URL, so a re-run refreshes rather than
#   duplicating.
#
# Run from the repo root, after git pull (after batch 149):
#   bash database/data/run-batch-150.sh

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
echo "  Batch 150 — dashboard newswire: eighteen curated links"
echo "==================================================================="

add_links() {
    php artisan tinker --execute='
use App\Models\DashboardLink;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch150.json")), true);

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

// ---- one replacement: the syndicated copy added in batch 149
if (! empty($payload["replace"])) {
    $dupe = DashboardLink::where("url", $payload["replace"]["url"])->first();

    echo "\nREPLACED\n";

    if ($dupe) {
        echo "  removing ", $dupe->url, "\n";
        $dupe->delete();
    } else {
        echo "  already gone: ", $payload["replace"]["url"], "\n";
    }

    echo "  ", wordwrap($payload["replace"]["reason"], 84, "\n  "), "\n";
}

// ---- links that could not be verified
echo "\nSKIPPED — NOT ADDED\n";

foreach ($payload["skipped"] as $s) {
    echo "\n  HTTP ", $s["http"], "  ", $s["url"], "\n";
    echo "  ", wordwrap($s["reason"], 84, "\n  "), "\n";
}

echo "\nNOTE\n  ", wordwrap($payload["same_story_note"], 84, "\n  "), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "dashboard-links" add_links

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 150 applied. No failures."
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
