#!/usr/bin/env bash
#
# Update the existing article "How the Espionage Act Became a Tool of
# Repression" with the full text and inline links, reposted with the author's
# permission (Matthew Wills, originally in JSTOR Daily). The body is read from a
# file so its quotes and apostrophes survive intact:
#
#   database/data/articles/espionage-act-repression.body.html
#
# Matches the existing record by slug or title; if none exists it is created.
# The body carries a reposted-with-permission note linking back to the original
# JSTOR Daily piece. Idempotent. Run from the repo root:
#   bash database/data/update-espionage-act-article.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$body = file_get_contents(base_path("database/data/articles/espionage-act-repression.body.html"));

$slugs = ["how-the-espionage-act-became-a-tool-of-repression", "espionage-act-repression", "how-the-espionage-act-became-a-tool-of-repression-2"];
$article = \App\Models\Article::whereIn("slug", $slugs)
    ->orWhere("title", "like", "%Espionage Act%")
    ->first();

if (! $article) {
    $article = \App\Models\Article::firstOrNew(["slug" => "how-the-espionage-act-became-a-tool-of-repression"]);
    $article->title = "How the Espionage Act Became a Tool of Repression";
    echo "No existing record matched; creating one.\n";
}

$article->body = $body;
if (empty($article->title)) { $article->title = "How the Espionage Act Became a Tool of Repression"; }
$article->save();
echo "Article saved: {$article->url}\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Espionage Act article updated with full text and inline links."
