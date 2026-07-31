#!/usr/bin/env bash
#
# BATCH 43 -- collapse the duplicate news categories.
#
# /news showed both PRESS RELEASES and PRESS RELEASE as tabs. They are two
# separate Category rows, and ArticlesGrid builds its tab bar from
# Category::all(), so every category becomes a tab whether or not it
# duplicates another. Thirty articles sat under the plural and three under
# the singular:
#
#     /press-release/nppc-announces-mobile-app
#     /press-release/nppc-launches-online-store
#     /press-release/nppc-launches-the-nppc-quiz
#
# THE MERGE COMMAND ALREADY EXISTED AND HAD NEVER BEEN RUN. So did the
# equivalents for Report and Publication. All three are run here.
#
# THE CAUSE IS FIXED IN THIS BATCH, WHICH MATTERS MORE THAN THE MERGE.
# Five commands were reaching for their category with firstOrCreate keyed
# on the TITLE:
#
#     AddMobileAppPressRelease   ['title' => 'Press Release']
#     AddStorePressRelease       ['title' => 'Press Release']
#     AddNppcQuizPressRelease    ['title' => 'Press Release']
#     AddTnrReportArticle        ['title' => 'Report']
#     AddHaymarketPrairielandArticle  ['title' => 'Publication']
#
# A title that does not match the seeded one does not fail -- it silently
# mints a second category. Those three press-release commands are exactly
# the three articles that ended up in the singular. Running the merge
# without fixing them would have worked until the next time any of them
# ran, which is presumably how this came back after the Report and
# Publication merges were written.
#
# All five now key on the SLUG, matching CategorySeeder, which has always
# keyed on the slug and passed the title as the fallback.
#
# THE THREE ARTICLES CHANGE URL, from /press-release/{slug} to
# /press-releases/{slug}, because Article::getUrlAttribute() builds the
# prefix from the category slug. NOTHING BREAKS: SiteController::article()
# looks an article up by slug alone and 301s a mismatched prefix to the
# canonical URL, so existing links redirect rather than 404.
#
# NO ARTICLE IS DELETED by any of the three commands. Each re-tags first
# and removes only the emptied category.
#
# One failing step does not abort the run.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-43.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

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
echo "  Batch 43 — collapse the duplicate news categories"
echo "==================================================================="

echo
echo "Categories before:"
php artisan tinker --execute='echo \App\Models\Category::orderBy("title")->get()->map(fn ($c) => "  ".str_pad($c->title, 18)." ".str_pad($c->slug, 18)." ".$c->articles()->count()." article(s)")->implode("\n"), "\n";'

run "articles:merge-press-release-category" php artisan articles:merge-press-release-category
run "articles:merge-report-category"       php artisan articles:merge-report-category
run "articles:merge-publication-category"  php artisan articles:merge-publication-category

echo
echo "Categories after (these are exactly the tabs on /news):"
php artisan tinker --execute='echo \App\Models\Category::orderBy("title")->get()->map(fn ($c) => "  ".str_pad($c->title, 18)." ".str_pad($c->slug, 18)." ".$c->articles()->count()." article(s)")->implode("\n"), "\n";'

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 43 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
