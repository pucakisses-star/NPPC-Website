#!/usr/bin/env bash
#
# BATCH 195 -- a BBC report added to the dashboard.
#
#   THE DASHBOARD, NOT THE ARTICLE SECTION. These are DashboardLink
#   records feeding the ticker, the newswire and the event map -- a
#   separate model from Article. Batch 178 got this wrong once and 179
#   corrected it; this follows 179.
#
#   WHY IT BELONGS HERE. The headline leads on munitions, but the half
#   that concerns this archive is the second clause: Trump posted on
#   Truth Social that the leakers of these treasonous statements are
#   being hunted down and that long term jail sentences will be sought. A
#   sitting president announcing that people who passed information to
#   the press are being pursued, and that he wants them imprisoned, is
#   the front edge of exactly the kind of case this archive documents.
#   The Espionage Act leak prosecutions already in the database began the
#   same way.
#
#   THE HEADLINE IS VERBATIM, which is the convention the existing links
#   follow. It reads oddly on a political-prisoner dashboard because it
#   leads with the weapons story. Rewriting it would be worse -- the
#   reader clicks through and has to find the words they were shown.
#
#   NO COORDINATES, DELIBERATELY. It appears in the ticker and the
#   newswire but not on the event map, because there is no place to pin.
#   This is a social-media post about a nationwide pursuit, not an arrest
#   or a protest that happened somewhere, and dropping a marker on
#   Washington to make the map look busier would invent a location the
#   story does not have. The onMap scope requires both lat and lng, so
#   leaving them null is the whole mechanism.
#
#   FILED UNDER PROSECUTION, and worth being clear: nobody has been
#   charged. What is reported is a threat, not a case. But of the four
#   categories -- protest, arrest, prosecution, other -- a public vow to
#   hunt people down and seek long jail sentences sits better with
#   prosecution than in the catch-all. Move it to other if the archive
#   would rather keep prosecution for filed charges.
#
#   Idempotent: matched on the URL, so a second run updates in place
#   rather than adding a duplicate.
#
# Run from the repo root, after git pull (after batch 194):
#   bash database/data/run-batch-195.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

# tinker exits 0 even when the code inside throws; success is a sentinel
# the step prints as its last act.
run_tinker() {
    local label="$1" sentinel="$2" code="$3" out
    echo; echo "--- ${label}"
    out=$(php artisan tinker --execute="$code" 2>&1) || true
    printf '%s\n' "$out"
    if ! grep -q "$sentinel" <<<"$out"; then
        echo "  !! FAILED: ${label} — sentinel ${sentinel} missing (exception above?)"
        FAILED+=("${label}")
    fi
}

echo "==================================================================="
echo "  Batch 195 — BBC: Trump on hunting down leakers"
echo "==================================================================="

ADD_CODE='
use App\Models\DashboardLink;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch195.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$l = $payload["link"];

if (! array_key_exists($l["category"], DashboardLink::CATEGORIES)) {
    echo "  category ", $l["category"], " is not one of: ",
        implode(", ", array_keys(DashboardLink::CATEGORIES)), " — stopping.\n";

    return;
}

$before = DashboardLink::count();

// Matched on the URL: the same story added twice should update, not duplicate.
$link = DashboardLink::where("url", $l["url"])->first();

$fields = [
    "title"        => $l["title"],
    "source"       => $l["source"],
    "category"     => $l["category"],
    "published_at" => $l["published_at"],
];

if ($link) {
    $changed = [];

    foreach ($fields as $k => $v) {
        $current = $k === "published_at" ? optional($link->{$k})->toDateString() : $link->{$k};

        if ((string) $current !== (string) $v) { $link->{$k} = $v; $changed[] = $k; }
    }

    if ($changed) { $link->save(); echo "  already present — updated: ", implode(", ", $changed), "\n"; }
    else { echo "  already present and unchanged.\n"; }
} else {
    $link = DashboardLink::create($fields + ["url" => $l["url"]]);
    echo "  created.\n";
}

$link->refresh();

echo "\n  ", $link->title, "\n";
echo "    url        ", $link->url, "\n";
echo "    source     ", $link->source, "\n";
echo "    category   ", $link->category, "  (", DashboardLink::CATEGORIES[$link->category], ")\n";
echo "    published  ", optional($link->published_at)->toDateString(), "\n";
echo "    on the map ", ($link->lat !== null && $link->lng !== null ? "yes" : "no — no coordinates, by design"), "\n";

$after = DashboardLink::count();
$live = DashboardLink::published()->count();
$mapped = DashboardLink::onMap()->count();

echo "\n  dashboard links: ", $before, " -> ", $after, "   published: ", $live, "   on the map: ", $mapped, "\n";

$isLive = DashboardLink::published()->where("url", $l["url"])->exists();
$offMap = ! DashboardLink::onMap()->where("url", $l["url"])->exists();

echo "  this link is live in the ticker: ", ($isLive ? "yes" : "NO — published_at is missing or in the future"), "\n";
echo "  this link is kept off the map:   ", ($offMap ? "yes" : "NO — it picked up coordinates from somewhere"), "\n";

echo "\n  ", wordwrap($payload["why_it_belongs"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["no_coordinates_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["category_note"], 72, "\n  "), "\n";

if ($isLive && $offMap) { echo "\nB195-OK\n"; }
'

run_tinker "add-dashboard-link" "B195-OK" "$ADD_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 195 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
