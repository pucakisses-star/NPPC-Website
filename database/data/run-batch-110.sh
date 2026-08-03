#!/usr/bin/env bash
#
# BATCH 110 -- the follow-ups the batch 109 photo audit flagged:
#
#   FIFTEEN REPLACEMENT PORTRAITS — records left photo-less by the
#   batch 109 detachments get verified portraits of the RIGHT
#   person, each identity-confirmed against a captioned source (see
#   CREDITS-batch-110.md): four NARA Leavenworth intake mugshots
#   with matching number boards (Larson, Turner, Hoover, Anderson),
#   the NARA Julius Rosenberg arrest photo, the 1969 Fred Hampton
#   Grant Park photo, the vi3.org Abdul Azeez portrait, and more —
#   plus the Daily Worker group-photo crop of Helen Gershonowitz.
#   The attach step only fills EMPTY photo slots and reports if a
#   photo is already present. Eleven records stay photo-less with
#   the reasons flagged in fixes/batch110.json.
#
#   STEVE KELLY CASE DEDUP — steve-kelly-sj carried two
#   near-identical rows for the Prince of Peace Plowshares action
#   (arrests 1997-02-11 and 1997-02-12, both ~28 months). The action
#   was Ash Wednesday, February 12, 1997, aboard the USS The
#   Sullivans at Bath Iron Works, so the Feb 12 row is kept; any
#   fields it lacked copy over from the Feb 11 row before that row
#   deletes. No description text is touched.
#
#   HELEN GERSHONOWITZ — the 1931 Passaic defendant whose biography
#   was found contaminating the Kasinowitz record gets her own
#   record via prisoner:add (which refuses duplicates by name). Her
#   preserved text inside samuel-h-kashinowitz stays put, per the
#   no-deletion rule; a one-line pointer to her new record is
#   appended there.
#
# Run from the repo root, after git pull (after batch 109):
#   bash database/data/run-batch-110.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

bio_appends() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch110.json")), true);

foreach ($payload["appends"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->first();

    echo "\nAPPEND ", $row["slug"], "\n";

    if (! $p) { echo "  NOT FOUND — skipped\n"; continue; }

    if (str_contains((string) $p->description, mb_substr($row["append"], 0, 60))) {
        echo "  already appended\n";
        continue;
    }

    $p->description = trim((string) $p->description)."\n\n".$row["append"];
    $p->save();
    echo "  appended\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

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
echo "  Batch 110 — replacement portraits + Kelly dedup + Gershonowitz"
echo "==================================================================="

attach_photos() {
    local SRC_DIR="database/data/photos/batch110"
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    for f in "$SRC_DIR"/*.jpg; do
        [ -e "$f" ] || continue
        cp -f "$f" "${DST_DIR}/$(basename "$f")"
        echo "copied $(basename "$f")"
    done

    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch110.json")), true);

foreach ($payload["photos"] as $slug) {
    $p = Prisoner::withUnderReview()->where("slug", $slug)->first();

    if (! $p) {
        echo str_pad($slug, 26), "NOT FOUND\n";
        continue;
    }

    $rel = "prisoners/".$slug.".jpg";

    if (! is_file(storage_path("app/public/".$rel))) {
        echo str_pad($slug, 26), "file missing on disk — skipped\n";
        continue;
    }

    if ($p->photo === $rel) {
        echo str_pad($slug, 26), "already attached\n";
        continue;
    }

    if ($p->photo) {
        echo str_pad($slug, 26), "has a DIFFERENT photo (", $p->photo, ") — left alone\n";
        continue;
    }

    $p->photo = $rel;
    $p->save();
    echo str_pad($slug, 26), "photo attached\n";
}

echo "Done.\n";
'
}

kelly_dedup() {
    php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withUnderReview()->where("slug", "steve-kelly-sj")->with("cases")->first();

if (! $p) { echo "steve-kelly-sj NOT FOUND\n"; return; }

$feb = $p->cases->filter(
    fn ($c) => $c->arrest_date && $c->arrest_date->format("Y-m") === "1997-02"
)->sortBy(fn ($c) => $c->arrest_date->format("Y-m-d"))->values();

if ($feb->count() < 2) {
    echo "only ", $feb->count(), " February 1997 row(s) — nothing to dedup (already done?)\n";
    return;
}

$dup  = $feb->first(fn ($c) => $c->arrest_date->format("Y-m-d") === "1997-02-11");
$keep = $feb->first(fn ($c) => $c->arrest_date->format("Y-m-d") === "1997-02-12");

if (! $dup || ! $keep) {
    echo "expected Feb 11 + Feb 12 rows, found: ",
        $feb->map(fn ($c) => $c->arrest_date->format("Y-m-d"))->implode(", "),
        " — left alone for the curator\n";
    return;
}

$copied = [];
foreach (["institution_id", "charges", "sentence", "convicted", "prosecutor", "judge",
          "incarceration_date", "sentenced_date", "release_date", "imprisoned_for_days"] as $f) {
    $kv = $keep->getAttributes()[$f] ?? null;
    $dv = $dup->getAttributes()[$f] ?? null;
    if (($kv === null || $kv === "") && $dv !== null && $dv !== "") {
        $keep->setAttribute($f, $dv);
        $copied[] = $f;
    }
}

if ($copied) { $keep->save(); }
$dup->delete();

echo "duplicate Feb 11 row deleted",
    ($copied ? "; copied into the kept row: ".implode(", ", $copied) : "; kept row already complete"),
    "\n";
'
}

create_gershonowitz() {
    php artisan prisoner:add "$(cat database/data/fixes/helen-gershonowitz.json)"

    php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withUnderReview()->where("slug", "samuel-h-kashinowitz")->first();

if (! $p) { echo "samuel-h-kashinowitz NOT FOUND\n"; return; }

$note = "Helen Gershonowitz, whose story is preserved in the merged passage above, now has a record of her own in this archive.";

if (str_contains((string) $p->description, "now has a record of her own")) {
    echo "pointer already present\n";
} else {
    $p->description = trim((string) $p->description)."\n\n".$note;
    $p->save();
    echo "pointer appended\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "create-helen-gershonowitz" create_gershonowitz
run "attach-replacement-portraits" attach_photos
run "kelly-1997-case-dedup" kelly_dedup
run "bio-appends" bio_appends

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 110 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
