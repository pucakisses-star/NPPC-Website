#!/usr/bin/env bash
#
# BATCH 152 -- three more newswire links, found by searching the same
# beats as the batch 150 set and restricted to roughly the past month.
#
#     2026-08-04  Democracy Now!    Religious leaders and activists
#                                   arrested protesting attacks on
#                                   voting rights
#     2026-07-26  Willamette Week   Many arrested by Portland police
#                                   during ICE protests saw cases
#                                   fizzle in court
#     2026-07-10  KALW              Texas anti-ICE protesters get 30 to
#                                   100 years in prison
#
#   Each was fetched and returned HTTP 200 and each headline is the
#   page own. Two carry no published date in their metadata, so the
#   date comes from the URL path; that is recorded per item rather
#   than left looking like a read timestamp.
#
#   AS MANY CANDIDATES WERE REJECTED AS ACCEPTED, and the rejections
#   are the more useful half of this batch:
#
#     - one was already in the feed. Every candidate was checked
#       against all 1,055 link URLs currently on the page.
#     - one was a fourth outlet on the Ismael Vega sentencing that
#       batch 150 already carries three times over.
#     - two refused the fetch, and one of those offered only an SEO
#       title from the search index, which is not a headline.
#
#   THE FEED IS CLOSER TO SATURATED ON THESE BEATS THAN IT LOOKS.
#   Searching turns up mostly syndication — the NPR conspiracy piece
#   alone appears on five member-station domains. Volume from here
#   wants an importer with a de-duplicating rule, not more searching.
#
#   NOT PURSUED: the UK Palestine Action prosecutions, the biggest
#   mass political prosecution in the search window by a wide margin.
#   This newswire is domestic — two of its 1,055 links are British —
#   so they are named in the payload and left out.
#
#   Idempotent: matched on URL, so a re-run refreshes rather than
#   duplicating.
#
# Run from the repo root, after git pull (after batch 151):
#   bash database/data/run-batch-152.sh

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
echo "  Batch 152 — three more newswire links from the past month"
echo "==================================================================="

add_links() {
    php artisan tinker --execute='
use App\Models\DashboardLink;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch152.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$cut = \Illuminate\Support\Carbon::create(2025, 5, 7)->startOfDay();

echo "before: ", DashboardLink::count(), " links (",
    DashboardLink::published()->count(), " published)\n";

$made = 0;
$had = 0;

foreach ($payload["links"] as $row) {
    $fields = [
        "title" => $row["title"], "url" => $row["url"], "source" => $row["source"],
        "category" => $row["category"], "published_at" => $row["published_at"],
    ];

    $link = DashboardLink::where("url", $row["url"])->first();

    if ($link) { $link->fill($fields); $link->save(); $had++; $verb = "updated"; }
    else { $link = DashboardLink::create($fields); $made++; $verb = "added  "; }

    echo "\n  ", $verb, "  ", substr($row["published_at"], 0, 10), "  ", $row["source"], "\n";
    echo "            ", $row["title"], "\n";
    echo "            ", $row["url"], "\n";
    echo "    provenance: ", wordwrap($row["provenance"], 76, "\n                ");

    echo "\n";

    if ($link->published_at && $link->published_at->lt($cut)) {
        echo "    NOT VISIBLE — dated before the tracker start of ",
            $cut->format("M j, Y"), "\n";
    }
}

echo "\nafter: ", DashboardLink::count(), " links (",
    DashboardLink::published()->count(), " published)\n";
echo "  ", $made, " added, ", $had, " already present and refreshed.\n";

echo "\nCHECKED AND NOT ADDED\n";

foreach ($payload["rejected"] as $r) {
    echo "\n  ", $r["url"], "\n";
    echo "  ", wordwrap($r["reason"], 84, "\n  "), "\n";
}

echo "\nNOT PURSUED — ", $payload["not_pursued"]["topic"], "\n";
echo "  ", wordwrap($payload["not_pursued"]["reason"], 84, "\n  "), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "dashboard-links-recent" add_links

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 152 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
