#!/usr/bin/env bash
#
# BATCH 193 -- photographs for articles that had none or had a broken
# one. Seven of the thirty-one, and the other twenty-four are listed
# rather than filled.
#
#   MEASURED FIRST. All 116 published articles were pulled from /news and
#   every card image was requested: 85 resolve, 22 return 404, 9 carry no
#   image at all. Thirty-one need a photograph.
#
#   THE 404s ARE TWO DIFFERENT BUGS. Seventeen point at
#   images/site/default-article.jpg -- a placeholder path that does not
#   exist on the server, so those articles were never given a photograph
#   and the fallback was never created either. Five point at real
#   storage/articles ULID uploads whose files are missing from disk.
#
#   MOST OF THIS IS NOT A RESTORATION, and that is the finding that
#   decides the shape of this batch. Seventeen of them are press releases
#   republished from cldc.org; fifteen of those have no photograph on
#   cldc.org either. Their own posts carry no featured image. So for most
#   of these there is nothing to put back -- a photograph has to be found
#   somewhere new, which is a different job from repairing a link.
#
#   THE RULE HERE: every image comes from something this archive already
#   owns or hosts -- its own prisoner portraits, its own product
#   photography, its own quiz artwork. No third-party news photography is
#   copied in. That rule is exactly what limits this batch to seven, and
#   the remaining twenty-four are written into the payload as a worklist
#   with the three options spelled out, because choosing between an
#   organisational wordmark, a public-domain substitute, and licensed
#   news photography is a curators decision with money and copyright
#   attached to it.
#
#   ONE IMAGE WAS FOUND AND DELIBERATELY NOT USED. The CLDC post behind
#   the North Dakota ruling carries court-exhibit photographs of Sophia
#   Wilanskys arm after it was destroyed at Standing Rock -- medical
#   evidence of an open traumatic wound. Dropping one into the /news
#   thumbnail grid unannounced is an editorial call for the curator, not
#   something to do quietly inside a batch.
#
#   COPIES, NOT POINTERS. The prisoner portraits are copied into
#   articles/ rather than referenced in place, so replacing a prisoners
#   photograph later does not silently change an article that happens to
#   be about them.
#
#   Idempotent: files are copied only when absent or different, and the
#   image field is set only when it differs.
#
# Run from the repo root, after git pull (after batch 192):
#   bash database/data/run-batch-193.sh

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
echo "  Batch 193 — article photographs"
echo "==================================================================="

SRC_DIR="database/data/files/articles"
DEST_DIR="storage/app/public/articles"

echo
echo "--- install-repo-images"
install_ok=1
mkdir -p "$DEST_DIR"
for name in nppc-quiz.jpg; do
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
[ "$install_ok" -eq 1 ] || FAILED+=("install-repo-images")

ATTACH_CODE='
use App\Models\Article;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

$payload = json_decode(File::get(base_path("database/data/fixes/batch193.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$disk = Storage::disk("public");
$problems = [];
$set = 0; $already = 0; $copied = 0;

// Portraits and product shots already on this server, copied into articles/.
foreach ($payload["server_copies"] as $c) {
    if (! $disk->exists($c["from"])) {
        $problems[] = $c["slug"]." — source missing on disk: ".$c["from"];
        echo "  !! source missing: ", $c["from"], "\n";

        continue;
    }

    $needsCopy = ! $disk->exists($c["to"])
        || $disk->get($c["to"]) !== $disk->get($c["from"]);

    if ($needsCopy) {
        $disk->put($c["to"], $disk->get($c["from"]));
        $copied++;
    }

    $article = Article::where("slug", $c["slug"])->first();

    if (! $article) { $problems[] = $c["slug"]." — no such article"; echo "  !! no article: ", $c["slug"], "\n"; continue; }

    if ($article->image === $c["to"]) {
        $already++;
    } else {
        echo "  ", mb_strimwidth($c["title"], 0, 56, "..."), "\n";
        echo "      was  ", ($article->image ?: "(none)"), "\n";
        echo "      now  ", $c["to"], "   <- ", $c["from"], ($needsCopy ? "  (copied)" : "  (already on disk)"), "\n";
        $article->image = $c["to"];
        $article->save();
        $set++;
    }
}

// Files shipped in the repo and installed above.
foreach ($payload["repo_files"] as $c) {
    if (! $disk->exists($c["to"])) {
        $problems[] = $c["slug"]." — file not installed: ".$c["to"];
        echo "  !! not installed: ", $c["to"], "\n";

        continue;
    }

    $article = Article::where("slug", $c["slug"])->first();

    if (! $article) { $problems[] = $c["slug"]." — no such article"; echo "  !! no article: ", $c["slug"], "\n"; continue; }

    if ($article->image === $c["to"]) {
        $already++;
    } else {
        echo "  ", mb_strimwidth($c["title"], 0, 56, "..."), "\n";
        echo "      was  ", ($article->image ?: "(none)"), "\n";
        echo "      now  ", $c["to"], "   (shipped in the repo)\n";
        $article->image = $c["to"];
        $article->save();
        $set++;
    }
}

echo "\n  images set: ", $set, "   already correct: ", $already, "   files copied on disk: ", $copied, "\n";

// Re-measure rather than assert: how many published articles still have no
// usable image, counting a path with no file behind it as no image.
$missing = 0; $broken = 0;

foreach (Article::whereNotNull("published_at")->get(["id", "slug", "image"]) as $a) {
    if (! $a->image) { $missing++; continue; }
    if (! $disk->exists($a->image)) { $broken++; }
}

$total = Article::whereNotNull("published_at")->count();

echo "\n  published articles: ", $total, "\n";
echo "    with no image field:            ", $missing, "\n";
echo "    with an image field but no file: ", $broken, "\n";
echo "    still needing a photograph:      ", $missing + $broken,
    "   (expected ", $payload["expected"]["articles_still_without_a_working_image_after_this"], ")\n";

echo "\n  ", wordwrap($payload["sourcing_rule"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["not_a_restoration_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["wilansky_note"], 72, "\n  "), "\n";
echo "\n  STILL TO DO — ", $payload["remaining"]["count"], " articles:\n  ",
    wordwrap($payload["remaining"]["decision_needed"], 72, "\n  "), "\n";

if (! $problems) { echo "\nB193-OK\n"; }
'

run_tinker "attach-images" "B193-OK" "$ATTACH_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 193 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "24 articles still have no photograph. The payload lists every slug"
echo "and the three sourcing options; none of them can be settled without"
echo "a decision about wordmarks, public-domain substitutes, or paying"
echo "for news photography."
