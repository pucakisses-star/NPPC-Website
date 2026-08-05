#!/usr/bin/env bash
#
# BATCH 179 -- the two Hood County links onto the DASHBOARD, and the
# article batch 178 wrote in the wrong place taken back out.
#
#   BATCH 178 PUT THEM IN THE ARTICLE SECTION. That was wrong. The
#   dashboard has its own model for exactly this — DashboardLink, a
#   curated link with a headline, a source tag, a date and optional
#   coordinates, feeding the /dashboard ticker, the newswire and the
#   event map. Articles are this site writing something; dashboard links
#   are this site pointing at somebody else's reporting. These two are
#   the second thing.
#
#   THE TWO LINKS:
#
#     Jul 25, 2026  The Dallas Express
#                   Federal Judge Rejects Hood County Sheriffs Qualified
#                   Immunity Bid In Meme Arrest Lawsuit
#     Jan 13, 2026  Texas Scorecard
#                   Citizen Arrested for a Meme Sues Hood County Officials
#
#   FILED AS PROSECUTION, not arrest. The arrest itself was November
#   2025; both of these report its legal aftermath, the civil suit and
#   the immunity ruling. The category sets the marker colour and the
#   legend grouping on the dashboard map.
#
#   BOTH PLOT ON GRANBURY, the Hood County seat, where the sheriff's
#   office, the jail and the justice court all sit.
#
#   THE HEADLINES ARE THE ONES THE OUTLETS PRINT. Worth noting because
#   the Dallas Express URL slug says denied-qualified-immunity while the
#   page itself is headed Federal Judge Rejects ... Qualified Immunity
#   Bid. The stored title follows the page, not the slug.
#
#   THE ARTICLE IS DELETED. Batch 178 created
#   /news/hood-county-meme-arrest-qualified-immunity-krottinger-2026.
#   It goes. The text is not lost — it is still in
#   database/data/fixes/batch178.json in the repository if it is ever
#   wanted as a piece. The deletion runs only if the article is there, so
#   this batch is safe whether or not 178 has been applied on this server.
#
#   NOTHING ELSE FROM 178 IS TOUCHED. The two photographs it installed
#   for Emily Murphy and Henri Feola were a separate job and were the
#   right one.
#
#   Idempotent: updateOrCreate keyed on the URL, matching the existing
#   dashboard:add-keith-rose-cleared command, so a re-run refreshes the
#   two links rather than duplicating them.
#
# Run from the repo root, after git pull (after batch 178):
#   bash database/data/run-batch-179.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"; shift
    echo; echo "--- ${label}"
    if "$@"; then return 0; fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}"); return 0
}

echo "==================================================================="
echo "  Batch 179 — two Hood County links onto the dashboard newswire"
echo "==================================================================="

add_links() {
    php artisan tinker --execute='
use App\Models\DashboardLink;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch179.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

echo "  ", wordwrap($payload["why"], 72, "\n  "), "\n\n";

$added = 0; $refreshed = 0;

foreach ($payload["links"] as $row) {
    $link = DashboardLink::updateOrCreate(
        ["url" => $row["url"]],
        [
            "title"          => $row["title"],
            "source"         => $row["source"],
            "category"       => $row["category"],
            "published_at"   => Carbon::parse($row["published_at"]),
            "location_label" => $row["location_label"],
            "lat"            => $row["lat"],
            "lng"            => $row["lng"],
        ],
    );

    $link->refresh();

    if ($link->wasRecentlyCreated) { $added++; } else { $refreshed++; }

    echo "  ", ($link->wasRecentlyCreated ? "added    " : "refreshed"), "  ",
        $link->published_at->toDateString(), "  ", $link->source, "\n";
    echo "    ", $link->title, "\n";
    echo "    ", $link->category, " · ", $link->location_label,
        " (", $link->lat, ", ", $link->lng, ")\n";
    echo "    ", $link->url, "\n\n";
}

echo "  ", $added, " added, ", $refreshed, " refreshed\n";

// Only a published link with coordinates reaches the map, so both scopes are
// checked rather than assumed from the columns just written.
$onMap = DashboardLink::onMap()
    ->whereIn("url", array_column($payload["links"], "url"))
    ->count();

$published = DashboardLink::published()
    ->whereIn("url", array_column($payload["links"], "url"))
    ->count();

echo "  visible in the ticker and newswire: ", $published, " of ", count($payload["links"]), "\n";
echo "  plotted on the dashboard map:       ", $onMap, " of ", count($payload["links"]), "\n";

foreach ($payload["notes"] as $k => $v) {
    echo "\n  ", str_pad($k, 13), wordwrap($v, 60, "\n               ");
}

echo "\n";
'
}

remove_article() {
    php artisan tinker --execute='
use App\Models\Article;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch179.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$r = $payload["remove_article"];

$a = Article::where("slug", $r["slug"])->first();

if (! $a) {
    echo "  ", $r["slug"], "\n";
    echo "  not present — nothing to remove. Expected if batch 178 has not been run here.\n";

    return;
}

echo "  removing: ", $a->title, "\n";
echo "    /news/", $a->slug, "\n";

$a->delete();

echo "    deleted. Still present? ",
    (Article::where("slug", $r["slug"])->exists() ? "YES — the delete failed" : "no"), "\n";

echo "\n", wordwrap("  ".$r["why"], 74, "\n  "), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
'
}

run "add-links"      add_links
run "remove-article" remove_article

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 179 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "Both links are on /dashboard — ticker, newswire, and a marker on"
echo "Granbury. The photographs batch 178 installed are untouched."
