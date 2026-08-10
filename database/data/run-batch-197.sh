#!/usr/bin/env bash
#
# BATCH 197 -- Democracy Now on the CIA's new Cuba task force, added to
# the dashboard.
#
#   THIS ONE SITS FURTHER FROM THE ARCHIVES SUBJECT than most dashboard
#   links, so the connection is argued rather than assumed. The New York
#   Times reports the CIA has stood up a new Cuba task force: case
#   officers who recruit and run spies, cyber operators, and specialists
#   in covert influence operations. The last time the agency did this --
#   the 1960 Cuba task force -- it produced the Bay of Pigs and six
#   decades of covert operations, and those operations put people in
#   prison on both sides. This archive already holds two of them, Antonio
#   Guerrero and René González of the Cuban Five, checked against the
#   live database rather than remembered. A new task force of the same
#   kind is the upstream of cases this archive will end up documenting.
#
#   FILED UNDER OTHER, which is the honest answer and not a shrug. Nobody
#   has been arrested, charged or policed here. The categories are
#   protest, arrest, prosecution and other, and an intelligence
#   reorganisation is none of the first three. Stretching it into
#   prosecution to make it look more relevant would be worse than
#   admitting it is context.
#
#   PINNED AT LANGLEY, 38.9517 -77.1467 -- the George Bush Center for
#   Intelligence. Same reading of a pin that batch 196 established for
#   the White House: the marker is where the thing came from, not where
#   anything happened to anybody. Nothing in this story happened at
#   Langley either; a task force was stood up there and its operations
#   point at Cuba, 1,200 miles away. Havana is the other defensible pin.
#   The agency was chosen over the target because the story is about the
#   CIA acting, not about Cuba being acted upon.
#
#   THE HEADLINE IS VERBATIM, NYT: prefix and all, which is the
#   convention the other links follow. The prefix is accurate --
#   Democracy Now is reporting the Times reporting -- so it stays.
#
#   Idempotent: matched on the URL, fields written only when they differ.
#
# Run from the repo root, after git pull. Independent of every other batch:
#   bash database/data/run-batch-197.sh

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
echo "  Batch 197 — Democracy Now: CIA's new Cuba task force"
echo "==================================================================="

ADD_CODE='
use App\Models\DashboardLink;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch197.json")), true);

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
    $link = DashboardLink::create($fields + ["url" => $l["url"]]);
    echo "  created.\n";
} else {
    $changed = [];

    foreach ($fields as $k => $v) {
        if ($k === "published_at") {
            $current = optional($link->{$k})->toDateString();
        } elseif ($k === "lat" || $k === "lng") {
            // decimal(10,7) reads back as 38.9517000; round both sides so a
            // second run does not rewrite the same value every time.
            $current = $link->{$k} === null ? null : round((float) $link->{$k}, 4);
            $v = round((float) $v, 4);
        } else {
            $current = $link->{$k};
        }

        if ((string) $current !== (string) $v) { $link->{$k} = $fields[$k]; $changed[] = $k; }
    }

    if ($changed) { $link->save(); echo "  already present — updated: ", implode(", ", $changed), "\n"; }
    else { echo "  already present and unchanged.\n"; }
}

$link->refresh();

echo "\n  ", $link->title, "\n";
echo "    url        ", $link->url, "\n";
echo "    source     ", $link->source, "\n";
echo "    category   ", $link->category, "  (", DashboardLink::CATEGORIES[$link->category], ")\n";
echo "    published  ", optional($link->published_at)->toDateString(), "\n";
echo "    location   ", ($link->location_label ?: "(none)"), "\n";
echo "    coords     ", $link->lat, ", ", $link->lng, "\n";

$isLive = DashboardLink::published()->where("url", $l["url"])->exists();
$onMap = DashboardLink::onMap()->where("url", $l["url"])->exists();

echo "\n  live in the ticker: ", ($isLive ? "yes" : "NO — published_at missing or in the future"), "\n";
echo "  on the event map:   ", ($onMap ? "yes" : "NO — coordinates did not take"), "\n";
echo "  dashboard links: ", DashboardLink::count(), " total, ",
    DashboardLink::published()->count(), " published, ", DashboardLink::onMap()->count(), " on the map\n";

// The relevance argument in the header rests on two records actually being
// here. Check them rather than repeat the claim.
$five = Prisoner::withoutGlobalScopes()->whereIn("slug", ["antonio-guerrero", "rene-gonzalez"])->pluck("name");

echo "\n  Cuban Five prisoners already in this archive: ", ($five->isEmpty() ? "none found" : $five->implode(", ")), "\n";

echo "\n  ", wordwrap($payload["why_it_belongs"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["category_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["coordinates_note"], 72, "\n  "), "\n";

if ($isLive && $onMap) { echo "\nB197-OK\n"; }
'

run_tinker "add-dashboard-link" "B197-OK" "$ADD_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 197 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
