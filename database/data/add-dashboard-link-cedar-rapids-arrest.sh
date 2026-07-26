#!/usr/bin/env bash
#
# Dashboard newswire link: KCRG story on Annya Mari, the 58-year-old
# protester arrested July 7, 2026 outside the Cedar Rapids DHS office (viral
# use-of-force video; ~3 hours in jail pending bail; internal review opened).
# Categorized as an arrest and pinned to Cedar Rapids on the dashboard map.
#
# Idempotent (updateOrCreate by URL). Run from the repo root:
#   bash database/data/add-dashboard-link-cedar-rapids-arrest.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\DashboardLink;

$link = DashboardLink::updateOrCreate(
    ["url" => "https://www.kcrg.com/2026/07/14/i-was-wearing-sundress-cedar-rapids-protester-speaks-out-after-viral-arrest-video/"],
    [
        "title" => "‘I was wearing a sundress’: Cedar Rapids protester speaks out after viral arrest video",
        "source" => "KCRG",
        "category" => "arrest",
        "lat" => 41.9779,
        "lng" => -91.6656,
        "location_label" => "Cedar Rapids, Iowa",
        "published_at" => "2026-07-14 17:50:00",
    ],
);

echo ($link->wasRecentlyCreated ? "created" : "updated")." dashboard link: {$link->title}\n";
echo "Done.\n";
'

echo
echo "Done."
