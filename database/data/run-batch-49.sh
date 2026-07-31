#!/usr/bin/env bash
#
# BATCH 49 -- date the Colorado poster article into 2024.
#
# The Erica Meltzer republication at /news/is-colorado-in-america was
# carrying a site date of July 31, 2026 — the day the batch that created
# it happened to run, not a date anyone chose. The add script tried to
# set February 2, 2017 (the original Denverite publication date) but
# only when the field was empty, and it was not.
#
# The curator asked for a date somewhere in 2024. It becomes FEBRUARY 2,
# 2024 — the anniversary of the original February 2, 2017 Denverite
# publication — so the day carries meaning rather than being arbitrary.
# The article body's own attribution line still names the true 2017
# origin, so no reader is misled about when the piece was written.
#
# One field on one article. Idempotent: compared before writing.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-49.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

echo "==================================================================="
echo "  Batch 49 — date the Colorado poster article into 2024"
echo "==================================================================="

php artisan tinker --execute='
use App\Models\Article;

$a = Article::where("slug", "is-colorado-in-america")->first();

if (! $a) {
    echo "Article not found — nothing changed.\n";
    return;
}

$want = "2024-02-02";
$was = $a->published_at ? $a->published_at->format("Y-m-d") : "empty";

if ($was === $want) {
    echo "  already dated {$want} — nothing to do.\n";
} else {
    $a->published_at = $want;
    $a->save();
    echo "  published_at: {$was} -> {$want}\n";
}
'

echo
echo "Done."
