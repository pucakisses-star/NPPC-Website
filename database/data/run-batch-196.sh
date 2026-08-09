#!/usr/bin/env bash
#
# BATCH 196 -- the BBC leakers link pinned to the White House.
# Supersedes the coordinate decision in batch 195.
#
#   BATCH 195 LEFT IT OFF THE MAP on the reasoning that a Truth Social
#   post about a nationwide pursuit has no place to pin. The curator
#   asked for the White House, so it goes on the map there.
#
#   WHAT THE MARKER MEANS, since it is not the same thing the other pins
#   mean. Nothing in this story happened at the White House: the post
#   went up on Truth Social, the pursuit it describes is nationwide, and
#   no arrest or protest in it has a location. Every other pin on this
#   map marks a place something happened to somebody. This one marks
#   where the threat came from. That is a defensible reading of the same
#   fact -- it is simply a different one from the reading in 195.
#
#   38.8977, -77.0365 is the building itself rather than the wider
#   federal district, so the marker lands where a reader expects instead
#   of floating over the Mall.
#
#   DOES NOT DEPEND ON 195 HAVING RUN. Matched on the URL: updates in
#   place if the link is there, creates it with every field including the
#   coordinates if it is not. The two converge in either order.
#
#   Idempotent: fields are written only when they differ.
#
# Run from the repo root, after git pull (195 first if you like, but the
# order does not matter):
#   bash database/data/run-batch-196.sh

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
echo "  Batch 196 — BBC leakers link pinned to the White House"
echo "==================================================================="

PIN_CODE='
use App\Models\DashboardLink;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch196.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$l = $payload["link"];

if (! array_key_exists($l["category"], DashboardLink::CATEGORIES)) {
    echo "  category ", $l["category"], " is not one of: ",
        implode(", ", array_keys(DashboardLink::CATEGORIES)), " — stopping.\n";

    return;
}

$fields = [
    "title"          => $l["title"],
    "source"         => $l["source"],
    "category"       => $l["category"],
    "published_at"   => $l["published_at"],
    "lat"            => $l["lat"],
    "lng"            => $l["lng"],
    "location_label" => $l["location_label"],
];

$link = DashboardLink::where("url", $l["url"])->first();

if (! $link) {
    // Batch 195 has not run here; this batch stands on its own.
    $link = DashboardLink::create($fields + ["url" => $l["url"]]);
    echo "  link was absent — created with coordinates.\n";
} else {
    $changed = [];

    foreach ($fields as $k => $v) {
        if ($k === "published_at") {
            $current = optional($link->{$k})->toDateString();
        } elseif ($k === "lat" || $k === "lng") {
            $current = $link->{$k} === null ? null : round((float) $link->{$k}, 4);
            $v = round((float) $v, 4);
        } else {
            $current = $link->{$k};
        }

        if ((string) $current !== (string) $v) { $link->{$k} = $fields[$k]; $changed[] = $k; }
    }

    if ($changed) { $link->save(); echo "  updated: ", implode(", ", $changed), "\n"; }
    else { echo "  already pinned and unchanged.\n"; }
}

$link->refresh();

echo "\n  ", $link->title, "\n";
echo "    url        ", $link->url, "\n";
echo "    category   ", $link->category, "  (", DashboardLink::CATEGORIES[$link->category], ")\n";
echo "    published  ", optional($link->published_at)->toDateString(), "\n";
echo "    location   ", ($link->location_label ?: "(none)"), "\n";
echo "    coords     ", $link->lat, ", ", $link->lng, "\n";

$isLive = DashboardLink::published()->where("url", $l["url"])->exists();
$onMap = DashboardLink::onMap()->where("url", $l["url"])->exists();

echo "\n  live in the ticker: ", ($isLive ? "yes" : "NO — published_at missing or in the future"), "\n";
echo "  on the event map:   ", ($onMap ? "yes" : "NO — coordinates did not take"), "\n";
echo "  dashboard links on the map in total: ", DashboardLink::onMap()->count(), "\n";

echo "\n  ", wordwrap($payload["what_the_marker_means"], 72, "\n  "), "\n";

if ($isLive && $onMap) { echo "\nB196-OK\n"; }
'

run_tinker "pin-to-white-house" "B196-OK" "$PIN_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 196 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
