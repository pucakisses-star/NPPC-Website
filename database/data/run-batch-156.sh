#!/usr/bin/env bash
#
# BATCH 156 -- restore the accent in César Andreu Iglesias's name.
#
#   Fixed on his own record and in the biography of Jane Speed de
#   Andreu, his wife, who is the only other record that names him.
#
#   NO PHOTOGRAPH IS ADDED, and that is deliberate. The portrait on his
#   English Wikipedia article is hosted at
#   upload.wikimedia.org/wikipedia/en/ rather than on Wikimedia
#   Commons, which is the signature of a non-free file. Its file page
#   confirms it: categorised "All non-free media" and "Wikipedia
#   non-free files with NFUR stated", and stating that the use
#   qualifies as fair use on Wikipedia and that "any other uses of this
#   image, on Wikipedia or elsewhere, may be copyright infringement".
#   Wikipedia's fair-use rationale covers that article, not this site.
#   A search of Wikimedia Commons returns no freely licensed image of
#   him, so there is no drop-in substitute either. The payload lists
#   the three routes that would actually work.
#
#   THE SLUG SHOULD NOT MOVE. Laravel's Str::slug transliterates é to
#   e, so cesar-andreu-iglesias survives and nothing linking to him
#   breaks. Prisoner::updating regenerates the slug whenever the name
#   is dirty, so this is worth watching rather than assuming — the
#   script prints the slug before and after.
#
#   Idempotent: replacements are literal and a second run finds them
#   already applied.
#
# Run from the repo root, after git pull (after batch 155):
#   bash database/data/run-batch-156.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"
    shift
    echo
    echo "--- ${label}"
    if "$@"; then return 0; fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}")
    return 0
}

echo "==================================================================="
echo "  Batch 156 — César Andreu Iglesias: the accent"
echo "==================================================================="

fix_name() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch156.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$n = $payload["name"];
$p = Prisoner::withUnderReview()->where("slug", $n["slug"])->first();

if (! $p) { echo "  ", $n["slug"], " NOT FOUND — nothing changed.\n"; return; }

$slugBefore = $p->slug;

echo "  name before: ", $p->name, "\n";
echo "  slug before: ", $slugBefore, "\n";

if ($p->name === $n["from"]) {
    $p->name = $n["to"];
    $p->save();
    $p->refresh();
    echo "  name after:  ", $p->name, "\n";
} elseif ($p->name === $n["to"]) {
    echo "  name already accented\n";
} else {
    echo "  name is neither the expected value nor the target — left alone: ", $p->name, "\n";
}

echo "  slug after:  ", $p->slug,
    ($p->slug === $slugBefore ? "   unchanged, the URL still works" : "   !! MOVED — old links will 404"), "\n";

echo "\n  biographies\n";

foreach ($payload["descriptions"] as $d) {
    $r = Prisoner::withUnderReview()->where("slug", $d["slug"])->first();

    if (! $r) { echo "    ", $d["slug"], " NOT FOUND\n"; continue; }

    $count = mb_substr_count((string) $r->description, $d["from"]);

    if ($count === 0) {
        echo "    ", str_pad($d["slug"], 26), " already accented\n";

        continue;
    }

    $r->description = str_replace($d["from"], $d["to"], $r->description);
    $r->save();

    echo "    ", str_pad($d["slug"], 26), " ", $count, " mention(s) accented\n";
}

// ---- the photograph, and why there is not one
$pn = $payload["photo_not_added"];

echo "\n  PHOTOGRAPH NOT ADDED\n";
echo "  ", $pn["url"], "\n";
echo "  ", wordwrap($pn["reason"], 84, "\n  "), "\n";
echo "\n  What would work instead:\n";

foreach ($pn["routes_that_would_work"] as $i => $r) {
    echo "    ", $i + 1, ". ", wordwrap($r, 80, "\n       "), "\n";
}

echo "\n  photo field on the record: ", ($p->refresh()->photo ?: "(still empty)"), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "cesar-andreu-iglesias-accent" fix_name

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 156 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
