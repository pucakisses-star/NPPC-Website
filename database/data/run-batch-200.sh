#!/usr/bin/env bash
#
# BATCH 200 -- Bronner: the revised biography, copy-edited; the aka line
# removed; the case row brought back into line.
#
#   THE BIOGRAPHY IS THE CURATORS REVISION, corrected for spelling and
#   grammar only. Eight edits, every one of them listed in the payload so
#   they can be read back and reversed individually. No fact, claim or
#   hedge was touched: the trespassing charge, the family killed in the
#   Holocaust, the student-group invitation, over a year, the escape and
#   the attribution of his blindness to the shock experiments all say
#   exactly what the curator wrote.
#
#   THE FOUR THAT MATTER MOST were not commas. As supplied, "While
#   speaking at the University of Chicago his lecture was shut down" has
#   the lecture doing the speaking, and "despite being invited to speak"
#   attaches the invitation to the administration rather than to Bronner
#   -- so the sentence literally said the university invited itself. The
#   administration was a "who" rather than a "which". And the last
#   sentence yoked the escape to the blindness with a bare "and".
#
#   THE AKA FIELD IS CLEARED. It held "Dr. Bronner; born Emanuel
#   Heilbronner", which the prisoner page rendered as an AKA line under
#   the heading and appended to the meta description. Empty, the page
#   shows only Emanuel Bronner. The name, first_name, middle_name and
#   last_name columns are untouched -- middle_name stays Theodore, and
#   nothing on the page displays it.
#
#   THE CASE ROW FOLLOWS THE BIOGRAPHY AGAIN. The previous version
#   carried the bail and the police bringing him to Elgin. The revised
#   biography drops both, and the research found no source for either, so
#   they come out of the charges field as well. What remains is what the
#   new text says.
#
#   WHAT THE RESEARCH FOUND, recorded in the payload so a later reader
#   knows the ground under this record: no published source names the
#   charge as trespassing. Wikipedia says he was arrested for refusing to
#   leave the deans office; the German Historical Institutes Immigrant
#   Entrepreneurship entry says he was jailed for unknown reasons and
#   that his own account blamed his opposition to the universitys
#   water-fluoridation plans. That entry also states his sister Luise
#   committed him, and that he escaped in 1947 when she visited. The
#   curator saw this and chose the wording above; the biography now says
#   only that he was brought to Elgin, which does not contradict the
#   sister account.
#
#   Idempotent: fields written only when they differ.
#
# Run from the repo root, after git pull, after batches 198 and 199:
#   bash database/data/run-batch-200.sh

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
echo "  Batch 200 — Bronner: revised biography, aka removed"
echo "==================================================================="

UPDATE_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch200.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = $payload["prisoner"];

$prisoner = Prisoner::withoutGlobalScopes()->where("slug", $p["slug"])->first();

if (! $prisoner) {
    echo "  no prisoner at slug ", $p["slug"], " — run batch 198 first. Nothing changed.\n";

    return;
}

$changed = [];

if ($prisoner->description !== $p["description"]) { $prisoner->description = $p["description"]; $changed[] = "description"; }

// Cleared, not emptied to a string: the template tests truthiness, but a null
// keeps the column honest about there being no alias rather than an empty one.
if ($prisoner->aka !== null) { $prisoner->aka = null; $changed[] = "aka (cleared)"; }

if ($changed) { $prisoner->save(); }

$prisoner->refresh();
$prisoner->load("cases.institution");

$case = $prisoner->cases->first();
$caseChanged = [];

if ($case) {
    foreach ($payload["case"] as $k => $v) {
        if ($case->{$k} !== $v) { $case->{$k} = $v; $caseChanged[] = $k; }
    }

    if ($caseChanged) { $case->save(); $case->refresh(); }
}

$words = str_word_count(strip_tags((string) $prisoner->description));

echo "\n  ", $prisoner->name, "  [/prisoner/", $prisoner->slug, "]\n";
echo "    set on prisoner: ", ($changed ? implode(", ", $changed) : "nothing — already correct"), "\n";
echo "    set on case:     ", ($caseChanged ? implode(", ", $caseChanged) : "nothing — already correct"), "\n";
echo "    aka:             ", ($prisoner->aka === null ? "(empty — no AKA line renders)" : $prisoner->aka), "\n";
echo "    heading shows:   ", $prisoner->name, "\n";
echo "    names on record: ", $prisoner->first_name, " / ", $prisoner->middle_name, " / ", $prisoner->last_name, "   (untouched, not displayed)\n";
echo "    description:     ", $words, " words   (expected ", $payload["expected"]["description_words"], ")\n";

if ($case) {
    echo "    confined / out:  ", $case->incarceration_date?->toDateString(), "  ", $case->release_date?->toDateString(),
        "   ", $case->imprisoned_for_days, " days\n";
}

// The bail and the police delivery were dropped from the biography and are
// unsourced; make sure they are gone from the case row too rather than
// assuming the write landed.
$stale = [];

foreach (["posted bail", "police took him", "brought Bronner to"] as $phrase) {
    if ($case && mb_stripos((string) $case->charges, $phrase) !== false) { $stale[] = $phrase; }
}

echo "\n    unsourced wording left in charges: ", ($stale ? implode("; ", $stale)."   !! SHOULD BE NONE" : "none"), "\n";

echo "\n  Copy-edits applied to the supplied text (", count($payload["edits"]), "):\n";

foreach ($payload["edits"] as $i => $e) {
    echo "\n   ", $i + 1, ". ", wordwrap($e, 68, "\n      "), "\n";
}

echo "\n  ", wordwrap($payload["not_changed"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["research_note"], 72, "\n  "), "\n";

$ok = $prisoner->aka === null
    && ! $stale
    && $words === (int) $payload["expected"]["description_words"];

if ($ok) { echo "\nB200-OK\n"; }
'

run_tinker "revise-bronner" "B200-OK" "$UPDATE_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 200 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
