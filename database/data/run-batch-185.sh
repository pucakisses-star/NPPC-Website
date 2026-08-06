#!/usr/bin/env bash
#
# BATCH 185 -- the NPPC author byline, spelled out.
#
#   THE AUTHOR PAGE RENDERS THE RECORD, so the record is renamed rather
#   than the template special-cased: the author whose slug is nppc goes
#   from "NPPC" to "National Political Prisoner Coalition". The heading
#   on /author/nppc, the page title, its byline rows, and the byline on
#   every article credited to this author all follow from the one field.
#
#   THE URL DOES NOT CHANGE. HasSlug generates slugs only on create, so
#   the slug stays nppc and /author/nppc keeps working.
#
#   THE SITE ALREADY LEANED THIS WAY: the citation partial hardcodes
#   "National Political Prisoner Coalition" as its fallback byline. The
#   record was the odd one out.
#
#   Matched by SLUG, not by name, so the rename is idempotent — a second
#   run finds the name already correct and says so.
#
# Run from the repo root, after git pull (after batch 184):
#   bash database/data/run-batch-185.sh

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
echo "  Batch 185 — author NPPC -> National Political Prisoner Coalition"
echo "==================================================================="

RENAME_CODE='
use App\Models\Author;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch185.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$a = $payload["author"];

$author = Author::where("slug", $a["slug"])->first();

if (! $author) { echo "  author slug ", $a["slug"], " not found — nothing changed.\n"; return; }

$was = $author->name;

if ($author->name === $a["to"]) {
    echo "  already named ", $a["to"], " — nothing to do.\n";
} else {
    $author->name = $a["to"];
    $author->save();
    $author->refresh();
}

$bylines = $author->articles()->whereNotNull("published_at")->count();

echo "  name:  ", $was, "  ->  ", $author->name, "\n";
echo "  slug:  ", $author->slug, "  (unchanged — /author/", $author->slug, " still works)\n";
echo "  published articles now carrying the full byline: ", $bylines, "\n";

// Any other author record still holding a bare NPPC name is reported, not
// touched — renaming a record this batch was not aimed at is a new decision.
$others = Author::where("name", "like", "%NPPC%")->where("slug", "!=", $a["slug"])->get(["name", "slug"]);

if ($others->isNotEmpty()) {
    echo "\n  other authors mentioning NPPC, left alone:\n";
    foreach ($others as $o) { echo "    ", $o->name, "  [", $o->slug, "]\n"; }
}

echo "B185-OK\n";
'

run_tinker "rename-author" "B185-OK" "$RENAME_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 185 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
