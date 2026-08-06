#!/usr/bin/env bash
#
# BATCH 186 -- the NPPC author merged into National Political Prisoner
# Coalition, and deleted. Supersedes batch 185.
#
#   BATCH 185 READ THE REQUEST AS A RENAME. The curator meant a merge:
#   the articles bylined NPPC should be written by the existing author
#   National Political Prisoner Coalition, and the NPPC record deleted.
#   Both records exist on the live site, so renaming would have produced
#   two authors displaying the same name — the exact state batch 159
#   called worse than the one being fixed, when it merged NPPC Editorial
#   into NPPC. This is 159's pattern again, one step further down the
#   same road.
#
#   WHAT HAPPENS: every article on the nppc author — published or not —
#   moves to national-political-prisoner-coalition. The target inherits
#   the avatar and the about text if it lacks them and the source has
#   them. The nppc record is deleted only once it owns nothing; the
#   script re-counts after the move and refuses otherwise.
#
#   /author/nppc WILL 404 AFTERWARDS. Batch 159 recorded the same
#   consequence for nppc-editorial and made the same choice made here:
#   no redirect, because adding routes is a different kind of change
#   from moving data. Anything linking to /author/nppc externally will
#   break; the full-name author page is /author/national-political-
#   prisoner-coalition.
#
#   RUN ORDER WITH 185: run this one. If 185 runs after it, it finds no
#   nppc author and does nothing — superseded, not dangerous. If 185
#   already ran, the nppc record just carries the full name and this
#   batch still converges to the same end state, matching by slug.
#
#   Idempotent: a second run finds no nppc author and says so.
#
# Run from the repo root, after git pull (after batch 184; instead of 185):
#   bash database/data/run-batch-186.sh

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
echo "  Batch 186 — author NPPC merged into the full-name author"
echo "==================================================================="

MERGE_CODE='
use App\Models\Author;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch186.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$source = Author::where("slug", $payload["from"]["slug"])->first();
$target = Author::where("slug", $payload["into"]["slug"])->first();

if (! $source) {
    echo "  no author at slug ", $payload["from"]["slug"], " — already merged. (Expected on a second run.)\n";

    if ($target) {
        echo "  ", $target->name, " [", $target->slug, "] carries ",
            $target->articles()->count(), " article(s).\n";
    }

    echo "B186-OK\n";

    return;
}

// The target is created only if genuinely absent — on the live site it
// already exists. HasSlug derives the slug from the name on create.
if (! $target) {
    $target = Author::create(["name" => $payload["into"]["name"]]);
    echo "  target author created: ", $target->name, " [", $target->slug, "]\n";
}

if ($target->slug !== $payload["into"]["slug"]) {
    echo "  target slug is ", $target->slug, ", expected ", $payload["into"]["slug"], " — stopping rather than merging into the wrong record.\n";

    return;
}

// The organisation record keeps its face and its blurb if only the old
// record had them.
$inherited = [];

foreach (["avatar", "about"] as $f) {
    if (empty($target->{$f}) && ! empty($source->{$f})) {
        $target->{$f} = $source->{$f};
        $inherited[] = $f;
    }
}

if ($inherited) {
    $target->save();
    echo "  inherited from the old record: ", implode(", ", $inherited), "\n";
}

$moving = $source->articles()->count();

DB::table("articles")->where("author_id", $source->id)->update(["author_id" => $target->id]);

$left = $source->articles()->count();

echo "  articles moved: ", $moving, " (published and drafts alike)\n";

if ($left > 0) {
    echo "  ", $left, " article(s) still on the old record — NOT deleting it.\n";

    return;
}

$source->delete();

echo "  deleted author: ", $payload["from"]["name"], " [", $payload["from"]["slug"], "]\n";
echo "  /author/", $payload["from"]["slug"], " now 404s; the byline page is /author/", $target->slug, "\n";
echo "  ", $target->name, " now carries ", $target->articles()->count(), " article(s).\n";

echo "B186-OK\n";
'

run_tinker "merge-author" "B186-OK" "$MERGE_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 186 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "Batch 185 is superseded: run after this, it finds no nppc author"
echo "and does nothing."
