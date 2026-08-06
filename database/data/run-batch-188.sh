#!/usr/bin/env bash
#
# BATCH 188 -- every article by a featured author filed under
# Publications.
#
#   "TAGGED UNDER PUBLICATIONS" IS THE CATEGORY, not a Spatie tag. The
#   tabs across /news — Latest, Publications, Policy Brief, News, Press
#   Releases, Reports — are Category records, and Publications is one of
#   them. Articles also carry Spatie tags, but nothing on the site
#   filters by them, so the category is what the request means.
#
#   THE FEATURED AUTHORS ARE COMPUTED, NOT A LIST. The homepage section
#   takes every author with an avatar, at least one published article,
#   and a name that is not an organisation placeholder, then shows four
#   of them at random per page load. So "the featured authors" is that
#   whole pool, not whichever four happen to be on screen. This script
#   rebuilds the pool with the same query the section uses — which means
#   re-running it later is the maintenance path when a new author starts
#   appearing there.
#
#   ON THE LIVE SITE TODAY the pool is ten authors with one article
#   each, seven already filed under Publications. Three change:
#
#     Erica Meltzer    LOOK: Is Colorado in America?              News -> Publications
#     James Ray        We're All Going to be Called Terrorists... News -> Publications
#     Priscilla Grim   31 Days in DeKalb County Hell              (none) -> Publications
#
#   TWO OF THOSE LEAVE THE NEWS TAB. Moving an article between
#   categories is a move, not a copy — Meltzer's and Ray's pieces will
#   stop appearing under News on /news and start appearing under
#   Publications. That is what was asked for, and it is worth saying out
#   loud before it happens.
#
#   DRAFTS COUNT TOO. An author qualifies for the pool through a
#   PUBLISHED article, but once they qualify every article of theirs is
#   filed, drafts included, because the request was about the author's
#   work rather than about what is currently visible. The script reports
#   the split.
#
#   THE CATEGORY IS LOOKED UP, NEVER GUESSED. If Publications is missing
#   the script stops rather than creating a second category that only
#   differs by capitalisation from one already there.
#
#   Idempotent: articles already in Publications are counted and skipped.
#
# Run from the repo root, after git pull (after batch 187):
#   bash database/data/run-batch-188.sh

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
echo "  Batch 188 — featured authors' articles filed under Publications"
echo "==================================================================="

FILE_CODE='
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch188.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$want = Category::where("title", $payload["category"]["title"])->first();

if (! $want) {
    echo "  category ", $payload["category"]["title"], " not found — stopping rather than\n";
    echo "  creating a near-duplicate of a category that may already exist.\n";

    return;
}

// The same pool the homepage section builds: avatar, a published article,
// and not an organisation placeholder.
$pool = Author::query()
    ->whereHas("articles", fn ($q) => $q->whereNotNull("published_at"))
    ->whereNotNull("avatar")
    ->where("avatar", "!=", "")
    ->whereNotIn("name", $payload["excluded_names"])
    ->orderBy("name")
    ->get();

echo "  featured-author pool: ", $pool->count(), " author(s)\n";
echo "  target category:      ", $want->title, " [", $want->slug, "]\n\n";

$moved = 0; $already = 0; $drafts = 0;

foreach ($pool as $author) {
    foreach ($author->articles()->orderBy("title")->get() as $article) {
        $isDraft = $article->published_at === null;

        if ((string) $article->category_id === (string) $want->id) {
            $already++;
            continue;
        }

        $from = $article->category?->title ?: "(none)";

        $article->category_id = $want->id;
        $article->save();

        if ($isDraft) { $drafts++; }
        $moved++;

        echo "  ", str_pad($author->name, 20), str_pad(mb_strimwidth($article->title, 0, 46, "..."), 48),
            str_pad($from, 14), " -> ", $want->title, ($isDraft ? "   [draft]" : ""), "\n";
    }
}

echo "\n  changed: ", $moved, " (", $drafts, " of them drafts)\n";
echo "  already under ", $want->title, ", untouched: ", $already, "\n";

$total = Article::where("category_id", $want->id)->count();

echo "  articles now in ", $want->title, " site-wide: ", $total, "\n";

// Any pool author left with an article outside the target would mean the
// loop missed something; check rather than assume.
$stray = 0;

foreach ($pool as $author) {
    $stray += $author->articles()->where(function ($q) use ($want) {
        $q->whereNull("category_id")->orWhere("category_id", "!=", $want->id);
    })->count();
}

echo "  featured-author articles NOT in ", $want->title, ": ", $stray,
    ($stray === 0 ? "" : "   !! SHOULD BE ZERO"), "\n";

if ($stray === 0) { echo "B188-OK\n"; }
'

run_tinker "file-under-publications" "B188-OK" "$FILE_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 188 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "Re-run this after adding an author to the featured pool — it"
echo "recomputes the pool rather than working from a fixed list."
