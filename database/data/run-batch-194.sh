#!/usr/bin/env bash
#
# BATCH 194 -- the other twenty-four. Every published article now has a
# photograph behind a working path.
#
#   THE CURATOR ASKED ME TO CHOOSE, so: the issuing organisations own
#   mark for the organisations own releases, freely licensed photographs
#   for everything else, and no licensed news photography anywhere.
#   Nothing here costs money and nothing needs anyones permission.
#
#   FIFTEEN CLDC PRESS RELEASES GET THE CLDC MARK. None of them carries a
#   photograph on cldc.org either -- that was the finding in batch 193 --
#   so there is nothing to restore and no photograph of the event exists
#   in reach. A logo is not a photograph, and that is exactly why it is
#   the right answer here: nobody can mistake it for documentary
#   evidence, it says at a glance who issued the release, and it groups
#   the CLDC republications as the set they are. Taken from cldc.org and
#   composed onto a plain card rather than hotlinked to their server.
#
#   NINE ARTICLES GET REAL PHOTOGRAPHS, six Creative Commons and two
#   public domain from Wikimedia Commons, one NPPCs own screen. Two of
#   them are not illustrative at all but literal: Debs speaking at Canton
#   in 1918 is a photograph of the speech that produced the most famous
#   Espionage Act conviction in American history, and the San Francisco
#   rally is a U.S. solidarity action for the struggle the Wetsuweten
#   defendants were prosecuted over.
#
#   ATTRIBUTION LIVES ON THE ARTICLE. Six of these images legally require
#   credit at the point of use, and image_caption renders directly under
#   the photograph, which is the point of use. Author and licence are in
#   every caption. Do not blank them.
#
#   THREE CAPTIONS SAY THE PHOTOGRAPH IS NOT THE EVENT -- the Minneapolis
#   sign, the Panther 21 rally standing in for Johanna Fernández, and the
#   schools protest for the visa revocations. An illustrative photograph
#   is fine. An illustrative photograph a reader takes for documentary
#   evidence is not, and this archive is in the business of that
#   difference.
#
#   NO PORTRAIT WAS INVENTED. Johanna Fernández has no freely licensed
#   photograph that could be verified as her, so her obituary carries the
#   movement she spent her career documenting, captioned as exactly that,
#   rather than a face that might be someone else. Mohammad Yousef Hasna
#   is in this archive as a prisoner with no photograph on his record;
#   his article carries the prosecuting institution instead.
#
#   SOPHIA WILANSKY, AGAIN NOT USED. The court-exhibit photographs of her
#   destroyed arm on the CLDC post are medical evidence of an open wound.
#   Putting one into the /news thumbnail grid unannounced is a decision
#   for a person, not a batch. The North Dakota release takes the CLDC
#   mark like the rest.
#
#   Idempotent: files are copied only when absent or different, and image
#   and image_caption are set only when they differ.
#
# Run from the repo root, after git pull (after batch 193):
#   bash database/data/run-batch-194.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

# tinker exits 0 even when the code inside throws; success is a sentinel
# the step prints as its last act.
run_tinker() {
    local label="$1" sentinel="$2" code="$3" out
    echo; echo "--- ${label}"
    out=$(php artisan tinker --execute="$code" 2>&1) || true
    printf '%s\n' "$out"
    if ! grep -q "$sentinel" <<<"$out"; then
        echo "  !! FAILED: ${label} — sentinel ${sentinel} missing (exception above?)"
        FAILED+=("${label}")
    fi
}

echo "==================================================================="
echo "  Batch 194 — photographs for the remaining 24 articles"
echo "==================================================================="

SRC_DIR="database/data/files/articles"
DEST_DIR="storage/app/public/articles"

echo
echo "--- install-images"
install_ok=1
mkdir -p "$DEST_DIR"
for name in cldc-press-release.jpg student-visa-revocations.jpg weelaunee-stop-cop-city.jpg \
            cities-church-minnesota.jpg young-lords-panther-21.jpg doj-headquarters.jpg \
            wetsuweten-solidarity-sf.jpg debs-canton-1918.jpg thurgood-marshall-courthouse.jpg \
            nppc-database-app.jpg; do
    src="$SRC_DIR/$name"
    dest="$DEST_DIR/$name"
    if [ ! -f "$src" ]; then
        echo "  missing source file: $src"; install_ok=0; continue
    fi
    if [ -f "$dest" ] && cmp -s "$src" "$dest"; then
        echo "  $name — already installed, identical"
    else
        cp "$src" "$dest"
        echo "  $name — $(stat -c%s "$dest") bytes installed"
    fi
    if [ ! -e "public/storage/articles/$name" ]; then
        echo "  !! not reachable through the public symlink — run php artisan storage:link"
        install_ok=0
    fi
done
[ "$install_ok" -eq 1 ] || FAILED+=("install-images")

ATTACH_CODE='
use App\Models\Article;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch194.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$disk = Storage::disk("public");
$problems = []; $set = 0; $already = 0;

$apply = function (string $slug, string $path, string $caption) use ($disk, &$problems, &$set, &$already) {
    if (! $disk->exists($path)) {
        $problems[] = $slug." — image not installed: ".$path;
        echo "  !! not installed: ", $path, "\n";

        return;
    }

    $article = Article::where("slug", $slug)->first();

    if (! $article) { $problems[] = $slug." — no such article"; echo "  !! no article: ", $slug, "\n"; return; }

    if ($article->image === $path && $article->image_caption === $caption) {
        $already++;

        return;
    }

    echo "  ", mb_strimwidth($article->title, 0, 62, "..."), "\n";
    echo "      was  ", ($article->image ?: "(none)"), "\n";
    echo "      now  ", $path, "\n";

    $article->image = $path;
    $article->image_caption = $caption;
    $article->save();
    $set++;
};

// The fifteen CLDC republications, all taking the same mark.
$c = $payload["cldc"];

echo "\n  CLDC press releases:\n";

foreach ($c["slugs"] as $slug) { $apply($slug, $c["to"], $c["caption"]); }

echo "\n  Individually sourced photographs:\n";

foreach ($payload["individual"] as $item) {
    $apply($item["slug"], "articles/".$item["file"], $item["caption"]);
}

echo "\n  images set: ", $set, "   already correct: ", $already, "\n";

// Re-measure rather than assert. A path with no file behind it counts as no
// image, which is how the 22 broken ones hid in the first place.
$missing = 0; $broken = 0; $uncaptioned = 0;

foreach (Article::whereNotNull("published_at")->get(["id", "slug", "image", "image_caption"]) as $a) {
    if (! $a->image) { $missing++; continue; }
    if (! $disk->exists($a->image)) { $broken++; echo "    still broken: ", $a->slug, " -> ", $a->image, "\n"; }
}

$total = Article::whereNotNull("published_at")->count();

echo "\n  published articles: ", $total, "\n";
echo "    with no image field:             ", $missing, "\n";
echo "    with an image field but no file: ", $broken, "\n";
echo "    still needing a photograph:      ", $missing + $broken,
    "   (expected ", $payload["expected"]["articles_still_without_a_working_image_after_this"], ")\n";

// Credit is a legal requirement on six of these, so check it survived rather
// than trusting that it did.
foreach ($payload["individual"] as $item) {
    $a = Article::where("slug", $item["slug"])->first();

    if ($a && trim((string) $a->image_caption) === "") { $uncaptioned++; }
}

echo "    individually sourced images missing their credit: ", $uncaptioned,
    ($uncaptioned === 0 ? "" : "   !! MUST BE ZERO"), "\n";

echo "\n  ", wordwrap($payload["decision_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["attribution_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["honesty_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["wilansky_note"], 72, "\n  "), "\n";

if (! $problems && $uncaptioned === 0 && ($missing + $broken) === 0) { echo "\nB194-OK\n"; }
'

run_tinker "attach-images" "B194-OK" "$ATTACH_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 194 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "The sentinel only prints when zero published articles are left"
echo "without a working image and every credited photograph still has its"
echo "credit. If it did not print, read the two counts above."
