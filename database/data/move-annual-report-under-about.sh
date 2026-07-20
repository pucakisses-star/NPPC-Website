#!/usr/bin/env bash
#
# Move the "Annual Report" nav page under the About parent page.
#
# Finds the Page whose slug is "annual-report" (the pages table has no
# "url" column — "url" is an appended accessor derived from slug) and the
# top-level About page (parent_id NULL, slug "about"), then sets the
# annual-report page's parent_id to About's id so it appears in the
# About dropdown instead of at the top level.
#
# Idempotent: does nothing if the page is already under About, or if
# either page cannot be found.
#
# Run from the repo root:  bash database/data/move-annual-report-under-about.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

echo "Moving Annual Report page under About..."

php artisan tinker --execute='
$page = \App\Models\Page::where("slug", "annual-report")->first();
$about = \App\Models\Page::whereNull("parent_id")->where("slug", "about")->first();

if (! $page) {
    echo "Page with slug annual-report not found — nothing done.\n";
} elseif (! $about) {
    echo "Top-level About page (slug about, no parent) not found — nothing done.\n";
} elseif ($page->parent_id === $about->id) {
    echo "Annual Report ({$page->title}) is already under About — nothing done.\n";
} else {
    $from = $page->parent_id ? (\App\Models\Page::find($page->parent_id)->title ?? $page->parent_id) : "top level";
    $page->parent_id = $about->id;
    $page->save();
    echo "MOVED {$page->title} ({$page->slug}) from {$from} to under {$about->title}.\n";
}
echo "Done.\n";
'

echo
echo "Done. Annual Report nav placement updated."
