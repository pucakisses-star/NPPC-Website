#!/usr/bin/env bash
#
# BATCH 159 -- byline articles to NPPC rather than NPPC Editorial.
#
#   A MERGE, NOT A RENAME. Both authors already exist:
#
#     nppc-editorial   "NPPC Editorial"   4 articles
#     nppc             "NPPC"             0 articles
#
#   Renaming the first would have left two author records both
#   displaying "NPPC", on the slugs nppc and nppc-editorial, which is
#   worse than the state being fixed. So the four articles are
#   repointed to the existing NPPC author and the NPPC Editorial
#   record is deleted.
#
#   Only articles.author_id refers to authors, so the move is a single
#   column update. The delete happens only if the record has no
#   articles left: the script re-counts after the move and refuses if
#   anything remains.
#
#   WORTH KNOWING: /author/nppc-editorial will 404 afterwards. Nothing
#   on the site links to it except the bylines being moved, but an
#   external link or a search result would break. A redirect to
#   /author/nppc is a one-line addition to routes/web.php and is NOT
#   made here — adding routes is a different kind of change from
#   moving data.
#
#   Idempotent: a second run finds the source author gone and says so.
#
# Run from the repo root, after git pull (after batch 158):
#   bash database/data/run-batch-159.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"
    shift
    echo
    echo "--- ${label}"
    if "$@"; then return 0; fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}")
    return 0
}

echo "==================================================================="
echo "  Batch 159 — byline articles to NPPC, not NPPC Editorial"
echo "==================================================================="

merge_author() {
    php artisan tinker --execute='
use App\Models\Article;
use App\Models\Author;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch159.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$from = Author::where("slug", $payload["from"]["slug"])->first();
$into = Author::where("slug", $payload["into"]["slug"])->first();

if (! $into) {
    echo "  target author ", $payload["into"]["slug"], " NOT FOUND — refusing to move anything.\n";

    return;
}

if (! $from) {
    echo "  ", $payload["from"]["slug"], " already gone. Nothing to merge.\n";
    echo "  ", $into->name, " currently has ", $into->articles()->count(), " article(s).\n";

    return;
}

$moving = Article::where("author_id", $from->id)->count();
$already = Article::where("author_id", $into->id)->count();

echo "  from: ", $from->name, "  [", $from->slug, "]  ", $moving, " article(s)\n";
echo "  into: ", $into->name, "  [", $into->slug, "]  ", $already, " article(s)\n\n";

foreach (Article::where("author_id", $from->id)->get() as $a) {
    echo "    moving: ", $a->title, "\n";
}

$moved = Article::where("author_id", $from->id)->update(["author_id" => $into->id]);

$left = Article::where("author_id", $from->id)->count();
$now = Article::where("author_id", $into->id)->count();

echo "\n  moved ", $moved, " article(s)\n";
echo "  ", $into->name, " now has ", $now, " (", $already, " + ", $moving, ")";
echo $now === $already + $moving ? "  balanced\n" : "  !! DOES NOT BALANCE\n";

if ($left > 0) {
    echo "  ", $from->name, " still owns ", $left, " article(s) — NOT deleting it.\n";

    return;
}

$from->delete();

echo "  ", $payload["from"]["name"], " deleted.\n";
echo "\n  /author/", $payload["from"]["slug"], " will now 404. ",
    "A redirect to /author/", $payload["into"]["slug"], " is a one-line\n",
    "  addition to routes/web.php if any external link points at the old one.\n";

echo "\n  authors named NPPC-something now: \n";

foreach (Author::where("name", "like", "%NPPC%")->get() as $a) {
    echo "    ", str_pad($a->name, 20), " /author/", $a->slug,
        "  ", $a->articles()->count(), " article(s)\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "merge-nppc-editorial-into-nppc" merge_author

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 159 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
