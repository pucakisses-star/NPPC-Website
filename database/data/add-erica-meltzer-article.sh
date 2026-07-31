#!/usr/bin/env bash
#
# ERICA MELTZER -- author page, and the 1904 "Is Colorado in America?"
# poster article.
#
# THE FULL TEXT IS REPUBLISHED WITH PERMISSION. The piece is Copyright
# Colorado Public Radio, and the curator has confirmed that Denverite
# granted permission to reproduce it exactly. The body is therefore the
# original article verbatim, not a paraphrase.
#
#   ATTRIBUTION IS CARRIED IN THREE PLACES because that is what
#   republication under permission normally requires and because the byline
#   would otherwise be the only signal that this is not our own reporting:
#   a credit line above the text, the author record itself, and a closing
#   note with her bio and the copyright holder.
#
#   TWO ORIGINAL TYPOS ARE PRESERVED, deliberately. The piece reads
#   "Industrial Workers of World" and "Peabody ordered Moyer-s released".
#   Verbatim means verbatim; silently correcting a republished text edits
#   somebody else-s work under their name. Fix them only if Denverite asks.
#
#   The internal links are preserved too -- the Labadie Collection, the
#   Denver Public Library, the three-governors piece and the History.com
#   entry all point where she pointed them.
#
# THE HERO IMAGE IS THE POSTER ITSELF, from Wikimedia Commons at
# 6648x8352 and public domain, resized to 1400px tall and NOT cropped --
# a poster whose whole point is thirteen lines of text should not be
# trimmed to a banner. Using the primary source also avoids taking a
# Colorado Public Radio photograph.
#
# THE AVATAR is the headshot supplied with the request, from Denverite.
#
# Idempotent: firstOrNew on the author name and the article slug, so a
# second run updates rather than duplicating. published_at is only set
# when empty, so a later editorial change to the date is not overwritten.
#
# Run from the repo root:
#   bash database/data/add-erica-meltzer-article.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;

$dir = base_path("database/data/articles");

// --- Author ---
@mkdir(storage_path("app/public/authors"), 0775, true);
$arel = "authors/erica-meltzer.jpg";
copy($dir."/erica-meltzer.avatar.jpg", storage_path("app/public/".$arel));

$author = Author::firstOrNew(["name" => "Erica Meltzer"]);
$author->avatar = $arel;
$author->about = trim(file_get_contents($dir."/erica-meltzer.about.txt"));
$author->save();
echo "Author saved: /author/{$author->slug}\n";

// --- Article ---
@mkdir(storage_path("app/public/articles"), 0775, true);
$irel = "articles/is-colorado-in-america.jpg";
copy($dir."/is-colorado-in-america.hero.jpg", storage_path("app/public/".$irel));

$category = Category::firstOrCreate(["title" => "News"], ["slug" => "news"]);

$article = Article::firstOrNew(["slug" => "is-colorado-in-america"]);
$article->title = "LOOK: Is Colorado in America?";
$article->author_id = $author->id;
$article->category_id = $category->id;
$article->intro = trim(file_get_contents($dir."/is-colorado-in-america.intro.txt"));
$article->body = file_get_contents($dir."/is-colorado-in-america.body.html");
$article->image = $irel;
$article->image_caption = "Western Federation of Miners poster, 1904. University of Michigan Library, Joseph A. Labadie Collection / Denver Public Library, Western History Collection (C331.892822). Public domain.";
$article->citations_json = [
    ["title" => "Original reporting",
     "content" => "Erica Meltzer, \"LOOK: Is Colorado in America?\", Denverite, February 2, 2017. https://denverite.com/2017/02/02/look-colorado-america/"],
    ["title" => "Books cited",
     "content" => "George Suggs, Colorado-s War on Militant Unionism. Bridget Burke, \"Is Colorado in America?\", in Colorado Labor Wars 1903-1904."],
    ["title" => "Poster",
     "content" => "University of Michigan Library, Joseph A. Labadie Collection; Denver Public Library, Western History Collection C331.892822."],
];

if (empty($article->published_at)) {
    $article->published_at = "2017-02-02";
}

$article->save();
echo "Article saved: {$article->url}\n";
echo "  title:     {$article->title}\n";
echo "  author:    {$author->name}\n";
echo "  category:  {$category->title}\n";
echo "  published: {$article->published_at}\n";
echo "  body:      ", strlen($article->body), " chars\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
