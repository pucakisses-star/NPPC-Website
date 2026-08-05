#!/usr/bin/env bash
#
# BATCH 173 -- the thirteen photographs the framing audit left, plus a
# replacement for the one broken photo link.
#
#   THE THIRTEEN come from database/data/PHOTO-FRAMING-AUDIT.md, which
#   fetched and measured every photograph in the archive and found
#   seventeen with Geoffrey Parsons framing. Four were cropped in batch
#   172. These are the rest, in three shapes.
#
#   BLURRED PILLARBOX, the exact Parsons pattern — the bars are a
#   defocused copy of the picture itself:
#     robert-majure           680x383   -> 281x383
#     nicholas-lucia          1024x576  -> 462x576
#     damion-zachary-feller   680x382   -> 316x382
#
#   SOLID BARS, same waste in flat colour:
#     chase-vladamir-spencer  910x512   -> 342x512   black
#     christopher-tindal      648x364   -> 264x364   white
#     dylan-robinson          910x512   -> 491x512   white
#     deyanna-davis           1920x1080 -> 860x1075  black
#
#   GRAPHIC CARDS, a small mugshot on a designed background with the
#   name burnt in. These are the ones a naive crop gets wrong, because
#   the caption is part of the image:
#     jackson-patton     1200x630 -> 279x384
#     semaj-pigram        264x355 -> 223x280
#     walter-stewart      291x348 -> 222x279
#     gilberto-castillo   219x345 -> 217x271
#     vida-jones          198x227 -> 120x173
#     jabari-davis        640x360 -> 258x282
#
#   Vida Jones is the smallest thing in this batch at 120x173, and that
#   is the whole photograph — the card was mostly white space and a
#   caption printing a date of birth her record does not otherwise
#   carry. A small honest picture beats a large one that is four fifths
#   somebody elses graphic design.
#
#   THREE NEEDED A SECOND PASS, and it is worth saying which.
#   Deyanna Davis first came out with the black bar still attached,
#   because the wgrz.com watermark inside it read as picture. She is
#   cropped on column BRIGHTNESS instead, taking a high percentile of
#   each column so that a few bright watermark pixels cannot rescue an
#   otherwise black column. Jackson Patton and Jabari Davis each kept a
#   sliver of card border and were tightened by hand.
#
#   WILLIAM TANNER IS NOT A CROP. His photo path returned an HTML error
#   page, so his profile has been showing a broken image and no
#   dimension check would ever have caught it. The replacement is the
#   1927 portrait from Ita ja Lansi via the National Library of Finland,
#   captioned by the publisher as "Finnish politician and wobbly William
#   Tanner (1884-1940)" — which matches this record exactly, a
#   Kuopio-born 1884 IWW man convicted at the Chicago mass trial and
#   deported to Finland. Public domain in Finland under section 49a as a
#   non-artistic photograph, and in the United States as a work
#   published abroad before 1929. Unlike the last two portraits added to
#   this archive, nothing about this one needs a fair-use argument.
#
#   His birth and death years go in at the same time, at year
#   precision: 1884 was already stated in his own description, and 1940
#   comes from the same publisher caption as the photograph.
#
#   NO PHOTO COLUMNS ARE REWRITTEN. Every file is replaced in place at
#   the path the record already points to, and the three cards stored as
#   PNG stay PNG. Prisoner::photoUrl() stamps ?v= from the file
#   modification time, so a replacement busts the cache by itself.
#
#   Idempotent: files are copied only when they differ; the dates are
#   fixed values.
#
# Run from the repo root, after git pull (after batch 172):
#   bash database/data/run-batch-173.sh

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
echo "  Batch 173 — 13 crops, and a photograph for William Tanner"
echo "==================================================================="

SRC_DIR="database/data/files/framing"
DEST_DIR="storage/app/public/prisoners"

install_photos() {
    mkdir -p "$DEST_DIR"
    local n=0 replaced=0

    for src in "$SRC_DIR"/*; do
        [ -e "$src" ] || { echo "  no source files in $SRC_DIR"; return 1; }
        local base dest
        base="$(basename "$src")"
        dest="$DEST_DIR/$base"

        if [ -f "$dest" ] && cmp -s "$src" "$dest"; then
            echo "  $base — already installed, identical"
        else
            if [ -f "$dest" ]; then
                echo "  $base — $(stat -c%s "$dest") bytes -> $(stat -c%s "$src")"
                replaced=$((replaced + 1))
            else
                echo "  $base — new file (the record pointed at a missing image)"
            fi
            cp "$src" "$dest"
        fi

        [ -e "public/storage/prisoners/$base" ] \
            || { echo "  !! not reachable through the public symlink — run php artisan storage:link"; return 1; }
        n=$((n + 1))
    done

    echo "  $n file(s) in place, $replaced replaced"
}

verify_and_date() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch173.json")), true);

if (! $payload) { echo "Could not read the payload — nothing verified.\n"; return; }

echo "  ", str_pad("record", 26), str_pad("photo column", 40), "on disk\n";
echo "  ", str_repeat("-", 80), "\n";

$bad = 0;

foreach ($payload["rows"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->first();

    if (! $p) { echo "  ", $row["slug"], " — record not found\n"; $bad++; continue; }

    // Nothing is written here. A crop that lands on a path the record does not
    // read is a no-op that looks like a fix, so the column is checked instead.
    $ok = $p->photo === $row["photo_path"];
    $path = $p->photo ? Storage::disk("public")->path($p->photo) : null;
    $size = ($path && is_file($path)) ? filesize($path) : null;

    echo "  ", str_pad($row["slug"], 26), str_pad((string) $p->photo, 40),
        ($size !== null ? $size." bytes" : "MISSING"),
        ($ok ? "" : "   !! column does not match the payload path"), "\n";

    if (! $ok || $size === null) { $bad++; }
}

echo "\n  ", ($bad === 0 ? "every record points at the file this batch installed" : $bad." problem(s) above"), "\n";

// William Tanner: the years that came out of tracing his photograph.
$t = $payload["tanner"];
$p = Prisoner::withUnderReview()->where("slug", $t["slug"])->first();

if ($p) {
    echo "\n  ", $p->name, "\n";
    echo "    source:  ", $t["published"], "\n";
    echo "    caption: ", $t["caption"], "\n";
    echo "    rights:  ", $t["rights"], "\n";

    foreach (["birthdate", "death_date"] as $f) {
        if (! empty($t[$f]) && ! $p->{$f}) {
            $p->setPartialDate($f, $t[$f][0], $t[$f][1] ?? null, $t[$f][2] ?? null);
        }
    }

    $p->save();
    $p->refresh();

    echo "    born ", ($p->birthdate ? $p->formatPartialDate("birthdate") : "-"),
        " [", $p->datePrecisionFor("birthdate"), "]",
        "   died ", ($p->death_date ? $p->formatPartialDate("death_date") : "-"),
        " [", $p->datePrecisionFor("death_date"), "]",
        "   age ", ($p->age ?? "-"), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "install-photos" install_photos
run "verify-and-date" verify_and_date

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 173 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "That closes the croppable half of the framing audit: 17 found, 17"
echo "cropped across batches 172 and 173."
echo
echo "Still open there, and not fixable by cropping: emily-murphy,"
echo "henri-feola and henry-parker, whose broadcast frames are zoomed"
echo "INTO the mugshot so the missing part of the face is not in the file."
echo "Those need replacement sources, the way Tanner just got one."
