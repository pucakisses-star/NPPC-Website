#!/usr/bin/env bash
#
# BATCH 211 -- Roberto Rivera's biography restored.
#
#   BATCHES 209 AND 210 REWROTE IT. Nobody asked for that. The
#   instruction in 209 was to update the record and in 210 to correct the
#   custody facts; neither was an instruction to replace the prose, and
#   replacing it was mine to not do. This puts the original text back,
#   word for word.
#
#   WORD FOR WORD MEANS WORD FOR WORD: the original 539 characters,
#   unchanged punctuation, including its own hedges. One sentence is
#   appended recording the death, because the instruction was to restore
#   the bio AND add the date of death, and a biography that ends with a
#   sentence being imposed and says nothing about the man dying in prison
#   is a gap rather than a restoration. It also follows the rule batch
#   108 recorded for this archive: nothing is deleted from descriptions,
#   corrections are appended.
#
#   DELETE THAT LAST SENTENCE and the field is byte-identical to its
#   pre-batch-209 state. The original is stored on its own in the payload
#   so that is a copy-paste, not a reconstruction.
#
#   TWO KNOWN ERRORS ARE LEFT IN, deliberately. The restored text says he
#   was arrested on November 16-17, 2012 -- the New Jersey Attorney
#   General says the 16th -- and that a 25-year sentence was imposed
#   "reportedly in February 2019", when it was February 22, 2019. The
#   instruction was to restore the prose, not to improve it. Both correct
#   facts sit on the case row, which is where the site reads dates from;
#   the prose keeps its hedges.
#
#   NOTHING ELSE IS REVERTED. The structural corrections from batch 210
#   stand, because those came from the curator: the death date, both
#   custody flags off, death_in_custody_date on the case, the deleted
#   false-release row, Bergen County Jail in place of MCC Chicago, the
#   corrected arrest date, and the name. Only the prose was mine to undo.
#
#   Idempotent: written only when it differs.
#
# Run from the repo root, after git pull, after 209 and 210:
#   bash database/data/run-batch-211.sh

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
echo "  Batch 211 — Roberto Rivera: original biography restored"
echo "==================================================================="

RESTORE_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch211.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = $payload["prisoner"];

$prisoner = Prisoner::withoutGlobalScopes()->where("slug", $p["slug"])->first();

if (! $prisoner) { echo "  no prisoner at slug ", $p["slug"], " — nothing changed.\n"; return; }

if (! in_array($prisoner->name, $p["expect_name_one_of"], true)) {
    echo "  slug ", $p["slug"], " holds ", $prisoner->name, ", not the record this batch is for — stopping.\n";

    return;
}

$r = $payload["restore"];
$was = (string) $prisoner->description;

if ($was !== $r["description"]) {
    $prisoner->description = $r["description"];
    $prisoner->save();
    $prisoner->refresh();
    echo "  description restored (", mb_strlen($was), " chars -> ", mb_strlen($prisoner->description), " chars)\n";
} else {
    echo "  description already restored — nothing to do.\n";
}

$now = (string) $prisoner->description;

echo "\n  ", $prisoner->name, "  [/prisoner/", $prisoner->slug, "]\n";
echo "    original text present verbatim: ", (str_contains($now, $r["original"]) ? "yes" : "NO"), "\n";
echo "    appended death sentence:        ", (str_ends_with(trim($now), $r["append"]) ? "yes" : "NO"), "\n";
echo "    length:                         ", mb_strlen($now), "   (expected ", $payload["expected"]["description_chars"], ")\n";

echo "\n  what the field now holds:\n  ", wordwrap($now, 70, "\n  "), "\n";

echo "\n  Untouched by this batch, and still correct:\n";
echo "    death_date       ", ($prisoner->death_date ?: "(none)"), "\n";
echo "    in_custody       ", ($prisoner->in_custody ? "true" : "false"),
    "    released ", ($prisoner->released ? "true" : "false"), "\n";
echo "    state            ", ($prisoner->state ?: "(none)"), "\n";
echo "    cases            ", $prisoner->cases()->count(), "\n";

echo "\n  ", wordwrap($payload["known_errors_left_alone"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["append_note"], 72, "\n  "), "\n";

$ok = str_contains($now, $r["original"])
    && str_ends_with(trim($now), $r["append"])
    && mb_strlen($now) === (int) $payload["expected"]["description_chars"];

if ($ok) { echo "\nB211-OK\n"; }
'

run_tinker "restore-bio" "B211-OK" "$RESTORE_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 211 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "The original 539 characters are in batch211.json under restore.original,"
echo "on their own, if the appended death sentence should also come off."
