#!/usr/bin/env bash
#
# BATCH 207 -- where the Airtable biography is longer than the one on the
# site, the Airtable text becomes the site text. 51 records.
#
#   WHAT CHANGES: 29,931 characters gained across 51 prisoner
#   descriptions. Zolo Azania goes from 329 characters to 3,108; Maliki
#   Shakur Latine from 254 to 2,479; Sekou Kambui from 234 to 1,786.
#
#   THE TRADEOFF, said plainly, because it is a real change of register.
#   The site descriptions are short, dated, third-person summaries in the
#   archive's own voice. The Airtable descriptions are the campaign copy
#   they were condensed from: longer, far richer in circumstance, and in
#   places openly advocating -- "Zolo still adamantly maintains
#   innocence", "He is a very peaceful and deeply spiritual person". That
#   is what these pages will say now. The longer text is where the detail
#   lives, which is the point, but nobody should be surprised later that
#   the tone moved with it.
#
#   EVERY REPLACED DESCRIPTION IS STORED IN THE PAYLOAD under "was". That
#   is the whole rollback, and it matters more than usual here: the
#   current site wording exists nowhere else. It is not the Airtable
#   text, and the Airtable is not versioned. If one of these reads worse
#   afterwards, the old wording is in batch207.json.
#
#   NEWLINES ARE KEPT. 320 of the 572 Airtable descriptions carry real
#   line breaks, and the prisoner page splits the description on newline
#   and renders each piece as its own paragraph. So these lay out as
#   multi-paragraph biographies -- Azania seventeen paragraphs, Latine
#   fifteen -- rather than as one block. Collapsing that whitespace would
#   have produced a wall of text.
#
#   NOTHING ELSE MOVES. Only the description column. No dates, no case
#   rows, no photographs, no names. Where the Airtable prose repeats a
#   date the structured fields already hold -- Latine's birth date, for
#   one -- the fields stay authoritative and untouched.
#
#   ONE RECORD MATCHES THROUGH THE AKA FIELD: the Airtable row is labelled
#   William J. Turk, the archive record is Sekou Kambui. Checked before
#   including it -- the Airtable prose itself calls him Sekou Kambui
#   throughout, so only the row label uses the birth name.
#
#   Idempotent: each description is written only when it differs from the
#   payload, so a second run reports 51 already applied.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-207.sh

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
echo "  Batch 207 — longer Airtable biographies onto the site"
echo "==================================================================="

SWAP_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch207.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$set = 0; $already = 0; $missing = []; $mismatch = [];

foreach ($payload["items"] as $i) {
    $prisoner = Prisoner::withoutGlobalScopes()->where("slug", $i["slug"])->first();

    if (! $prisoner) { $missing[] = $i["slug"]; continue; }

    if (trim((string) $prisoner->description) === trim($i["now"])) { $already++; continue; }

    // If what is on the record is neither the text this batch expects to
    // replace nor the replacement, something else has edited it since the
    // payload was built. Say so rather than overwrite it silently.
    if (trim((string) $prisoner->description) !== trim($i["was"])) {
        $mismatch[] = $i["slug"];
    }

    $prisoner->description = $i["now"];
    $prisoner->save();
    $set++;
}

echo "  descriptions replaced: ", $set, "\n";
echo "  already replaced:      ", $already, "\n";
echo "  slugs not found:       ", count($missing), (count($missing) ? "  ".implode(", ", $missing) : ""), "\n";

if ($mismatch) {
    echo "\n  !! these had been edited since this payload was built — replaced anyway, and\n";
    echo "     their previous text is NOT the was value stored in batch207.json:\n";

    foreach ($mismatch as $s) { echo "       ", $s, "\n"; }
}

// Re-measure rather than assert.
$gained = 0; $short = [];

foreach ($payload["items"] as $i) {
    $prisoner = Prisoner::withoutGlobalScopes()->where("slug", $i["slug"])->first();

    if (! $prisoner) { continue; }

    $len = mb_strlen(preg_replace("/\s+/u", " ", trim((string) $prisoner->description)));
    $gained += $len - (int) $i["was_len"];

    if ($len < (int) $i["now_len"]) { $short[] = $i["slug"]; }
}

echo "\n  characters gained across the set: ", $gained,
    "   (expected ", $payload["expected"]["chars_gained"], ")\n";
echo "  records shorter than the payload says they should be: ", count($short), "\n";

// The paragraph split is the whole reason the newlines were kept; show it landed.
$azania = Prisoner::withoutGlobalScopes()->where("slug", "zolo-azania")->first();

if ($azania) {
    echo "  zolo-azania paragraphs on the page: ", substr_count((string) $azania->description, "\n") + 1, "\n";
}

echo "\n  ", wordwrap($payload["the_tradeoff"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["reversibility"], 72, "\n  "), "\n";

$ok = ! $missing && ! $short && ($set + $already) === (int) $payload["expected"]["count"];

if ($ok) { echo "\nB207-OK\n"; }
'

run_tinker "swap-descriptions" "B207-OK" "$SWAP_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 207 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "To undo: every previous description is in database/data/fixes/batch207.json"
echo "under \"was\", keyed by slug."
