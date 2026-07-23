#!/usr/bin/env bash
#
# Priscilla Grim: set her prisoner photo, create an author page for her, and
# publish her first-person essay "31 Days in DeKalb County Hell" (originally in
# Scalawag Magazine, May 2023) as an article bylined to her.
#
#   - Prisoner photo: professional portrait (from her own site, priscillagrim.com)
#   - Author page:    /author/priscilla-grim  (avatar + bio)
#   - Article:        /news/31-days-in-dekalb-county-hell  (author = Priscilla Grim)
#
# The article body, intro, and author bio are read from files so their
# apostrophes and quotes survive intact:
#   database/data/articles/priscilla-grim-31-days.body.html
#   database/data/articles/priscilla-grim-31-days.intro.txt
#   database/data/articles/priscilla-grim.about.html
#   database/data/photos/cop-city/priscilla-grim.jpg
#
# Idempotent: the prisoner photo is only set on the matched record; the author
# and article are matched by name/slug and updated in place on re-run. Run from
# the repo root:
#   bash database/data/add-priscilla-grim-article.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$src = base_path("database/data/photos/cop-city/priscilla-grim.jpg");
if (! is_file($src)) { echo "Portrait file missing.\n"; return; }

// --- 1. Prisoner photo ---
@mkdir(storage_path("app/public/prisoners/cop-city"), 0775, true);
$prel = "prisoners/cop-city/priscilla-grim.jpg";
copy($src, storage_path("app/public/" . $prel));
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "priscilla-grim")->first();
if ($p) {
    $p->photo = $prel;
    $p->save();
    echo "Prisoner photo set on {$p->slug}.\n";
} else {
    echo "Prisoner priscilla-grim not found; photo not attached.\n";
}

// --- 2. Author page ---
@mkdir(storage_path("app/public/authors"), 0775, true);
$arel = "authors/priscilla-grim.jpg";
copy($src, storage_path("app/public/" . $arel));
$author = \App\Models\Author::firstOrNew(["name" => "Priscilla Grim"]);
$author->avatar = $arel;
$author->about = trim(file_get_contents(base_path("database/data/articles/priscilla-grim.about.txt")));
$author->save();
echo "Author saved: /author/{$author->slug}\n";

// --- 3. Article ---
@mkdir(storage_path("app/public/articles"), 0775, true);
$heroSrc = base_path("database/data/articles/priscilla-grim-31-days.hero.jpg");
$irel = "articles/priscilla-grim-31-days.jpg";
copy(is_file($heroSrc) ? $heroSrc : $src, storage_path("app/public/" . $irel));
$article = \App\Models\Article::firstOrNew(["slug" => "31-days-in-dekalb-county-hell"]);
$article->title = "31 Days in DeKalb County Hell";
$article->author_id = $author->id;
$article->intro = trim(file_get_contents(base_path("database/data/articles/priscilla-grim-31-days.intro.txt")));
$article->body = file_get_contents(base_path("database/data/articles/priscilla-grim-31-days.body.html"));
$article->image = $irel;
$article->image_caption = "Artwork by Scalawag Magazine (Zaire Love), for the Stop Cop City Week of Writing.";
if (empty($article->published_at)) { $article->published_at = "2024-07-01"; }
$article->save();
echo "Article saved: {$article->url}\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Priscilla Grim photo, author page, and article published."
