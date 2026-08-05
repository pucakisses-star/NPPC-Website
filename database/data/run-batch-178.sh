#!/usr/bin/env bash
#
# BATCH 178 -- an article on the Hood County meme arrest, and the two
# photographs the framing audit said could not be cropped.
#
#   THE ARTICLE. Two links came in on the Kolton Krottinger case: the
#   Dallas Express on the qualified-immunity ruling and Texas Scorecard on
#   the suit. This site has no external-link article type — a column for
#   one was added and dropped again on the same day in June — so a link is
#   carried the way this archive carries everything else, as a written
#   piece with its sources attached. Two more were read to check the dates
#   and are cited alongside them.
#
#   HIS PRISONER RECORD IS NOT TOUCHED. It already carries the arrest and
#   the December declination, and the curator asked for the articles
#   rather than an edit to the record. What the new reporting adds — the
#   December 5 discharge for want of probable cause, the social-media ban
#   written into his bond, the phone seized without a warrant and never
#   returned, and the July 20 immunity ruling — is in the article.
#
#   TWO THINGS IN THE RECORD DISAGREE, and the article says so instead of
#   picking the tidier version. His own defence lawyer described the meme
#   as his in November 2025; the civil suit says another administrator of
#   the page posted it; the prosecutors who refused the case said there
#   was insufficient evidence he created or posted it. And the ruling is
#   dated both July 19 and July 20 depending on who is reporting.
#
#   THE PHOTOGRAPHS. PHOTO-FRAMING-AUDIT.md lists three images that
#   cropping cannot fix, because the broadcast frame is zoomed INTO the
#   mugshot and the missing part of the face is not in the file. Two of
#   them are fixed here, from the Fulton County Sheriff booking
#   photographs of the six arrested in Atlanta on January 21, 2023, which
#   the Atlanta Police Department released as a single six-up composite.
#
#     emily-murphy   932x524, cut past the top of the head and the chin
#                    ->  400x337, the whole booking photograph
#     henri-feola    900x506, ending just below the eyes, station
#                    watermark, and served from a file named for a name
#                    this record does not use
#                    ->  400x337, the whole booking photograph, on a path
#                        that matches the record
#
#   LOWER RESOLUTION, BETTER PICTURE. 400x337 is smaller than what it
#   replaces, and that is the trade being made deliberately: the audit's
#   complaint was never that these images were small, it was that the face
#   was cut off. A complete head at 400 pixels beats a cropped one at 900.
#
#   FEOLA'S FILE IS RENAMED, not replaced in place, which is the one
#   departure from batch 173's rule. The image was being served from
#   madeleine-feola.jpg while the record reads Henri Feola, so the old
#   name was sitting in a public URL. The photo column moves to
#   henri-feola.jpg. The old file is left on disk rather than deleted —
#   removing it is a separate decision from pointing the record somewhere
#   better.
#
#   THE SHERIFF SEAL STAYS. Fulton County's own mark sits in the corner of
#   the Murphy frame. It is the issuing agency's mark on the agency's own
#   record, which is why the framing audit also left the white margins on
#   the 1961 Freedom Rider prints alone. What got removed from other
#   photographs here was a television station's watermark on somebody
#   else's picture.
#
#   WHICH CELL IS WHO WAS CHECKED, NOT ASSUMED. The composite is a
#   three-by-two grid and the caption names the six in order. Rather than
#   trust the order, each candidate cell was put beside the image already
#   on the record and matched by eye first.
#
#   THREE OTHERS FROM THE SAME NIGHT ARE LEFT ALONE, and the reason is
#   worth recording: Francis Carroll, Graham Evatt and Ivan Ferguson were
#   cropped in batch 172 from broadcast frames 524 pixels tall, which is
#   MORE than the official composite gives. Swapping them for the official
#   photograph would read as a provenance upgrade and be a downgrade in
#   the picture. The composite wins only where the frame was zoomed in and
#   there was nothing left to recover.
#
#   HENRY PARKER, the third image on the audit's list, is not fixed and
#   will not be. The 640x360 frame in this archive is the same frame every
#   outlet ran; there is no better version in circulation. The clipping is
#   slight and the face is complete. The audit was right that it is
#   imperfect and wrong that it was fixable.
#
#   Idempotent: the article is created only when the slug is absent, and
#   the files are copied only when they differ.
#
# Run from the repo root, after git pull (after batch 177):
#   bash database/data/run-batch-178.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"; shift
    echo; echo "--- ${label}"
    if "$@"; then return 0; fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}"); return 0
}

echo "==================================================================="
echo "  Batch 178 — the Hood County meme arrest, and two photographs"
echo "==================================================================="

SRC_DIR="database/data/files/cop-city"
DEST_DIR="storage/app/public/prisoners/cop-city"

install_photos() {
    mkdir -p "$DEST_DIR"
    local n=0

    for base in emily-murphy.png henri-feola.jpg; do
        local src="$SRC_DIR/$base" dest="$DEST_DIR/$base"

        [ -f "$src" ] || { echo "  missing source file: $src"; return 1; }

        if [ -f "$dest" ] && cmp -s "$src" "$dest"; then
            echo "  $base — already installed, identical"
        else
            if [ -f "$dest" ]; then
                echo "  $base — $(stat -c%s "$dest") bytes -> $(stat -c%s "$src")"
            else
                echo "  $base — new file"
            fi
            cp "$src" "$dest"
        fi

        [ -e "public/storage/prisoners/cop-city/$base" ] \
            || { echo "  !! not reachable through the public symlink — run php artisan storage:link"; return 1; }
        n=$((n + 1))
    done

    echo "  $n file(s) in place"
}

point_records() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch178.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$src = $payload["source_note"];

echo "  ", wordwrap($src["image"], 72, "\n  "), "\n\n";

foreach ($payload["photos"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->first();

    if (! $p) { echo "  ", $row["slug"], " — record not found\n"; continue; }

    $before = (string) $p->photo;

    // Only Feola moves path. Murphy is replaced where she already points,
    // so the column is left alone and the file swap is the whole change.
    if (! empty($row["old_path"]) && $p->photo !== $row["path"]) {
        $p->photo = $row["path"];
        $p->save();
    }

    $p->refresh();

    $disk = Storage::disk("public");
    $ok = $disk->exists($p->photo);

    echo "  ", $p->name, "  [", $row["slug"], "]\n";
    echo "    was:  ", $row["was"], "\n";
    echo "    now:  ", $row["now"], "\n";
    echo "    photo column: ", $before;

    if ($before !== (string) $p->photo) { echo "  ->  ", $p->photo; }

    echo "\n    file on disk: ", ($ok ? $disk->size($p->photo)." bytes" : "MISSING"), "\n\n";
}

echo "  ", wordwrap($src["verification"], 72, "\n  "), "\n\n";
echo "  ", wordwrap($src["watermark"], 72, "\n  "), "\n";
'
}

add_article() {
    php artisan tinker --execute='
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch178.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$a = $payload["article"];

$existing = Article::where("slug", $a["slug"])->first();

if ($existing) {
    echo "  ", $a["slug"], " already exists — not created again.\n";
    echo "  /news/", $existing->slug, "\n";

    return;
}

$category = Category::firstOrCreate(["title" => $a["category"]], ["slug" => "news"]);
$author = Author::where("slug", $a["author_slug"])->first();

if (! $author) { echo "  author ", $a["author_slug"], " not found — the piece would have no byline.\n"; return; }

// citations_json is a repeater of title/content pairs; the accessor on the
// model reads exactly that shape.
// The slug is passed rather than left to HasSlug, which would derive it from
// the title. The existence check above keys on the payload slug, so a derived
// one would never match it and a second run would create the piece again.
$article = Article::create([
    "slug"           => $a["slug"],
    "title"          => $a["title"],
    "intro"          => $a["intro"],
    "body"           => $a["body"],
    "category_id"    => $category->id,
    "author_id"      => $author->id,
    "published_at"   => $a["published_at"],
    "citations_json" => $a["citations"],
]);

$article->refresh();

echo "  created: ", $article->title, "\n";
echo "    slug       ", $article->slug, "\n";
echo "    byline     ", $author->name, "\n";
echo "    category   ", $category->title, "\n";
echo "    published  ", $article->published_at->toDateString(), "\n";
echo "    body       ", mb_strlen((string) $article->body), " characters, ",
    substr_count((string) $article->body, "<h2"), " sections\n";
echo "    citations  ", count($article->citations ?? []), "\n";

foreach (array_keys($article->citations ?? []) as $t) { echo "      - ", $t, "\n"; }

echo "\n    /news/", $article->slug, "\n";
'
}

report() {
    php artisan tinker --execute='
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch178.json")), true);

if (! $payload) { echo "Could not read the payload.\n"; return; }

echo "  DELIBERATELY NOT CHANGED:\n";

foreach ($payload["not_changed"] as $n) {
    echo "\n    ", $n["what"], "\n";
    echo wordwrap("      ".$n["why"], 74, "\n      "), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "install-photos" install_photos
run "point-records"  point_records
run "add-article"    add_article
run "report"         report

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 178 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "The framing audit listed three photographs cropping could not fix."
echo "Two are fixed. The third has no better source anywhere, and that is"
echo "now written down so nobody goes looking for it again."
