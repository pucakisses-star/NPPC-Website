#!/usr/bin/env bash
#
# BATCH 151 -- the three links batch 150 could not verify.
#
#   All three outlets refuse automated fetches: VPM, KTLA and KGW
#   returned 403 or hung up. So none of these headlines came from the
#   page itself, and each was recovered another way. The route is
#   recorded per item in the payload and printed by this script,
#   because a headline is a quotation and where it came from is part
#   of it.
#
#     KGW    headline from the search index entry for the exact URL;
#            date from KGW own YouTube upload of the same piece,
#            identical title, published 2026-05-12
#     KTLA   headline from the search index and confirmed word for
#            word against AOL syndicated copy; date 2026-06-30 is that
#            copy published timestamp
#     VPM    headline from the search index entry; date from the URL,
#            matching the reported June 2024 sentencing of the nine
#            I-95 blockade defendants
#
#   TWO OF THE THREE DATES ARE DERIVED, NOT READ. Neither the KGW nor
#   the KTLA date is the article page own timestamp. They are the best
#   available and they are marked as such rather than presented as if
#   they had been read off the page.
#
#   ONE OF THE THREE WILL NOT APPEAR. The VPM piece is dated June 24,
#   2024 and the newswire filters to the tracker window, which starts
#   May 7, 2025. The row is created and stored correctly and stays
#   invisible until the window is widened. It is added anyway so the
#   record exists. This script says so plainly at the end rather than
#   reporting three successes and leaving the page to disagree.
#
#   Idempotent: matched on URL, so a re-run refreshes rather than
#   duplicating.
#
# Run from the repo root, after git pull (after batch 150):
#   bash database/data/run-batch-151.sh

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
echo "  Batch 151 — the three links that refused to be fetched"
echo "==================================================================="

add_links() {
    php artisan tinker --execute='
use App\Models\DashboardLink;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch151.json")), true);

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

$hidden = DashboardLink::published()->where("published_at", "<", $cut)->get();

echo "\n", $hidden->count(), " published link(s) dated before the tracker start, and so invisible:\n";

foreach ($hidden as $h) {
    echo "  ", $h->published_at->format("Y-m-d"), "  ", $h->title, "\n";
}

echo "\n", wordwrap($payload["invisible_warning"], 84, "\n"), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "dashboard-links-unverifiable" add_links

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 151 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
