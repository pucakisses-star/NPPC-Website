#!/usr/bin/env bash
#
# The author avatar is rendered as a circle with object-fit: cover, which
# center-cropped Priscilla Grim's tall portrait and cut off the top of her
# head. This installs a square, top-anchored crop (her full head in frame) as
# the avatar, without changing the global avatar CSS (which would re-crop every
# other author's avatar). Her full portrait stays the prisoner photo.
#
#   database/data/articles/priscilla-grim.avatar.jpg
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-priscilla-grim-avatar.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$src = base_path("database/data/articles/priscilla-grim.avatar.jpg");
if (! is_file($src)) { echo "Avatar crop missing.\n"; return; }

@mkdir(storage_path("app/public/authors"), 0775, true);
$rel = "authors/priscilla-grim.jpg";
copy($src, storage_path("app/public/" . $rel));

$author = \App\Models\Author::where("slug", "priscilla-grim")->orWhere("name", "Priscilla Grim")->first();
if ($author) {
    $author->avatar = $rel;
    $author->save();
    echo "Avatar updated (top-anchored crop).\n";
} else {
    echo "Author not found.\n";
}
echo "Done.\n";
'

echo
echo "Done. Priscilla Grim avatar re-cropped."
