#!/usr/bin/env bash
#
# Fixes for the Priscilla Grim author page + article that were already deployed:
#
#   1. Author bio was stored as raw HTML but the author template escapes it, so
#      the tags showed literally. Replace it with a clean single-paragraph
#      plain-text bio (also drops the redundant second paragraph).
#   2. Article hero image: use the Scalawag "31 Days in DeKalb County Hell"
#      collage artwork instead of the reused portrait.
#   3. Publication date: mid-2024 (2024-07-01) instead of 2023-05-01. (The
#      body still credits the original Scalawag publication as May 2023.)
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-priscilla-grim-article.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
// --- 1. Plain-text bio ---
$author = \App\Models\Author::where("slug", "priscilla-grim")->orWhere("name", "Priscilla Grim")->first();
if ($author) {
    $author->about = trim(file_get_contents(base_path("database/data/articles/priscilla-grim.about.txt")));
    $author->save();
    echo "Author bio updated (plain text).\n";
} else {
    echo "Author not found.\n";
}

// --- 2. Hero image ---
@mkdir(storage_path("app/public/articles"), 0775, true);
$heroSrc = base_path("database/data/articles/priscilla-grim-31-days.hero.jpg");
$irel = "articles/priscilla-grim-31-days.jpg";
if (is_file($heroSrc)) { copy($heroSrc, storage_path("app/public/" . $irel)); }

$article = \App\Models\Article::where("slug", "31-days-in-dekalb-county-hell")->first();
if ($article) {
    $article->image = $irel;
    $article->image_caption = "Artwork by Scalawag Magazine (Zaire Love), for the Stop Cop City Week of Writing.";
    // --- 3. Mid-2024 publication date ---
    $article->published_at = "2024-07-01";
    $article->save();
    echo "Article hero image + caption + date updated.\n";
} else {
    echo "Article not found.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Priscilla Grim bio, article image, and date fixed."
