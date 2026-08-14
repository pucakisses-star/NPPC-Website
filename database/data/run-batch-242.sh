#!/usr/bin/env bash
#
# BATCH 242 -- six descriptions with a book page header scanned into the
# middle of a sentence.
#
#   HARRY WILLIAMS READ: received a one- to four- State Anti-Sedition and
#   Criminal Syndicalism Prisoners 169 teen-year prison sentence. The
#   running header and its page number sit between "four-" and "teen",
#   splitting the word fourteen across a page turn.
#
#   NOTHING IS DELETED FROM DESCRIPTIONS, and nothing is here. What comes
#   out is a printers running header and a page number a scanner dropped
#   in. What else changes is that words the page break split are rejoined:
#   fourteen, Syndicalism, a Communist, Upon coming, and he became. No
#   sentence, clause or fact is removed. Every before and after is printed
#   in full below, so the change is auditable from this log.
#
#   THE HEADERS NAME CHAPTERS, which is the finding worth more than the six
#   repairs. They read: State Anti-Sedition and Criminal Syndicalism
#   Prisoners at pages 167 and 169; Federal Espionage and Sedition Act
#   Prisoners at 125; Political Prisoners Who Died While Incarcerated at
#   189; and American Political Prisoners at 138. Chapter titles and a book
#   title. A substantial part of this archive was taken from one printed
#   book, and that book kept federal Espionage Act prisoners and state
#   criminal syndicalism prisoners in separate chapters.
#
#   WHICH IS VERY LIKELY HOW 608 CASE ROWS came to carry "Federal
#   prosecution under the Espionage Act of 1917 and/or the Sedition Act of
#   1918". One chapters framing applied to a whole book at import -- the
#   error corrected on fifteen records in batch 240 and on James Larkin in
#   batch 234. Carl Sklar is the proof inside a single record: the header
#   naming the STATE chapter is embedded in his description, and his charge
#   field said federal.
#
#   THEY WERE NOT FOUND THE WAY THIS WAS PROPOSED. Sweeping for spliced
#   headers by shape returned thousands of false positives -- ordinary
#   prose with numbers in it, May Day 2012, IRS Form 1096, Akron Police
#   Officer Badge 1518. What found them was the bullet used as a
#   page-number separator: five descriptions in the whole database.
#
#   Idempotent: each edit is applied only if its exact damaged text is
#   still present.
#
# Run from the repo root, after git pull, after batch 241:
#   bash database/data/run-batch-242.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

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
echo "  Batch 242 — page headers scanned into descriptions"
echo "==================================================================="

OCR_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch242.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$written = 0; $already = 0; $bad = [];

foreach ($payload["people"] as $e) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $e["slug"])->first();

    if (! $p) { echo "  !! no record at ", $e["slug"], "\n"; $bad[] = $e["slug"]; continue; }

    if ($p->name !== $e["expect_name"]) {
        echo "  !! ", $e["slug"], " now holds ", $p->name, " — skipped\n";
        $bad[] = $e["slug"];

        continue;
    }

    if ($p->description === $e["after"]) {
        echo "\n  ", $p->name, " — already repaired\n";
        $already++;

        continue;
    }

    // Only touch text that still carries the damage this batch was written
    // against. Anything else here has been edited since and is left alone.
    $stillDamaged = true;

    foreach ($e["removed"] as $frag) {
        if (! str_contains((string) $p->description, $frag)) { $stillDamaged = false; }
    }

    if (! $stillDamaged) {
        echo "\n  !! ", $p->name, " no longer matches the damaged text this batch expects — left alone\n";
        $bad[] = $e["slug"];

        continue;
    }

    echo "\n  ", str_repeat("-", 66), "\n";
    echo "  ", $p->name, "   [/prisoner/", $p->slug, "]\n";
    echo "\n  BEFORE (", mb_strlen((string) $p->description), " chars):\n  ",
        wordwrap((string) $p->description, 70, "\n  "), "\n";

    $p->description = $e["after"];
    $p->save();
    $p->refresh();
    $written++;

    echo "\n  AFTER  (", mb_strlen($p->description), " chars):\n  ",
        wordwrap($p->description, 70, "\n  "), "\n";
    echo "\n  removed: ";

    foreach ($e["removed"] as $frag) { echo "\n    ", mb_substr($frag, 0, 80); }

    echo "\n";

    if (mb_strlen($p->description) !== (int) $e["after_chars"]) {
        $bad[] = $e["slug"]." length is ".mb_strlen($p->description).", expected ".$e["after_chars"];
    }
}

echo "\n  ", str_repeat("=", 66), "\n";
echo "  repaired ", $written, "   already clean ", $already, "\n";

// Any remaining page-header marks anywhere in the archive.
$left = Prisoner::withoutGlobalScopes()
    ->where("description", "like", "%•%")->pluck("name", "slug");

echo "\n  descriptions still containing the page-number bullet: ", count($left), "\n";

foreach ($left as $slug => $name) { echo "    ", $name, "  [", $slug, "]\n"; }

echo "\n  ", wordwrap($payload["what_this_is"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["not_a_deletion_of_content"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["the_headers_name_chapters"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["which_explains_batch_240"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["how_they_were_found"], 72, "\n  "), "\n";

echo "\n  FLAGGED, NOT CHANGED\n";

foreach ($payload["flags"] as $i => $f) {
    echo "\n  ", ($i + 1), ". ", wordwrap($f, 69, "\n     "), "\n";
}

echo "\n  problems: ", count($bad), "\n";

foreach ($bad as $b) { echo "    !! ", $b, "\n"; }

if (count($bad) === 0 && ($written + $already) === (int) $payload["expected"]["count"]) { echo "\nB242-OK\n"; }
'

run_tinker "repair-scanned-headers" "B242-OK" "$OCR_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 242 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
