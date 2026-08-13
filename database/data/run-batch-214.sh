#!/usr/bin/env bash
#
# BATCH 214 -- the E.R doctor biography, which is the one that was asked
# for.
#
#   BATCH 213 RESTORED THE WRONG TEXT. The instruction was to put the old
#   biography back. I read that as the oldest one and restored the 539
#   characters recorded in batch 207 as "was". The one the curator meant
#   is the one that was actually on the page before I touched it: the
#   Airtable summary batch 207 wrote in, which opens by calling him an E.R
#   hospital doctor. That is what this batch puts back.
#
#   THREE VERSIONS EXIST, and it is worth naming them once so this does
#   not happen a third time:
#     539 chars  pre-207, "a New York-licensed physician"  <- batch 213
#     618 chars  the Airtable summary, "an E.R hospital doctor"  <- THIS
#     ~2000      mine, written by 209 and 210, never asked for
#
#   SUPERSEDES BATCH 213, which is merged and writes the 539-character
#   version. If 213 has already run, this overwrites it. If neither has
#   run, run this and skip 213. If both are run, this one goes last.
#
#   RESTORED VERBATIM, including "E.R" with the second period missing and
#   the tense slip in "admitted to his plan though maintains". The
#   instruction was to put it back, not to improve it. Those two are a
#   one-line follow-up if they are wanted.
#
#   THE CLOSING SENTENCE IS THE DEATH, kept from the standing instruction
#   to put the old bio back AND add his date of death, and from the rule
#   batch 108 recorded for this archive: nothing is deleted from
#   descriptions. Delete it if the field should hold only the Airtable
#   words.
#
#   BOTH SLUGS ARE TRIED, for the reason batch 213 documented: the live
#   slug is roberto-e-rivera, and batches 211 and 212 silently did nothing
#   because they only looked for roberto-rivera.
#
#   Idempotent: the description is written only when it differs.
#
# Run from the repo root, after git pull. Run this INSTEAD OF batch 213,
# or after it:
#   bash database/data/run-batch-214.sh

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
echo "  Batch 214 — Roberto Rivera, the E.R doctor biography"
echo "==================================================================="

RESTORE_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch214.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = $payload["prisoner"];
$r = $payload["restore"];

// The live slug is roberto-e-rivera; batches 211 and 212 looked only for
// roberto-rivera and silently did nothing. Try each and say which answered.
$prisoner = null;
$foundAt = null;

foreach ($p["slugs"] as $slug) {
    $candidate = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();

    echo "  slug ", $slug, ": ", ($candidate ? $candidate->name : "no record"), "\n";

    if ($candidate && ! $prisoner) { $prisoner = $candidate; $foundAt = $slug; }
}

if (! $prisoner) { echo "\n  !! none of those slugs exist — nothing changed.\n"; return; }

if (! in_array($prisoner->name, $p["expect_name_one_of"], true)) {
    echo "\n  !! ", $foundAt, " holds ", $prisoner->name, ", which is not this record — stopping.\n";

    return;
}

// Name which of the three versions is being replaced, so the log says what
// state the server was actually in rather than assuming it.
$before = (string) $prisoner->description;

$whichWas = str_starts_with($before, "Roberto Rivera was an E.R hospital doctor")
    ? "the Airtable version — already correct"
    : (str_starts_with($before, "Roberto Rivera was a New York-licensed physician")
        ? "the pre-207 version — batch 213 had run"
        : (str_starts_with($before, "Roberto Epifanio Rivera was a New York-licensed physician")
            ? "my rewrite from batch 210 — 211 and 213 never landed"
            : "something else"));

echo "\n  was: ", mb_strlen($before), " chars — ", $whichWas, "\n";

if ($before !== $r["description"]) {
    $prisoner->description = $r["description"];
    $prisoner->save();
    $prisoner->refresh();
    echo "  now: ", mb_strlen($prisoner->description), " chars — the E.R doctor text plus the death sentence\n";
} else {
    echo "  now: unchanged — nothing to do.\n";
}

echo "\n  ", $prisoner->name, "  [/prisoner/", $prisoner->slug, "]\n";
echo "    matched on   ", $foundAt, "\n";

// Everything below came from the curator via batch 210 and must survive a
// description write untouched; printed so that is visible, not assumed.
echo "\n  untouched:\n";
echo "    name column  ", $prisoner->name, "\n";
echo "    full name    ", trim($prisoner->first_name." ".$prisoner->middle_name." ".$prisoner->last_name), "\n";
echo "    death_date   ", optional($prisoner->death_date)->toDateString(), "\n";
echo "    in_custody   ", var_export((bool) $prisoner->in_custody, true),
     "   released ", var_export((bool) $prisoner->released, true), "\n";

$case = $prisoner->cases()->with("institution")->first();

if ($case) {
    echo "    institution  ", $case->institution?->name, "\n";
    echo "    confined     ", optional($case->incarceration_date)->toDateString(), " -> ",
        optional($case->release_date)->toDateString(), "   ", $case->imprisoned_for_days, " days\n";
}

echo "\n  the biography now reads:\n\n  ", wordwrap($prisoner->description, 72, "\n  "), "\n";

echo "\n  ", wordwrap($payload["why"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["supersedes"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["verbatim_note"], 72, "\n  "), "\n";

$ok = $prisoner->description === $r["description"]
    && mb_strlen($prisoner->description) === (int) $payload["expected"]["chars"]
    && str_starts_with($prisoner->description, $payload["expected"]["starts_with"])
    && str_ends_with($prisoner->description, $payload["expected"]["ends_with"])
    && ! str_contains($prisoner->description, "Ridgewood police arrested him")
    && ! str_contains($prisoner->description, "New York-licensed physician")
    && optional($prisoner->death_date)->toDateString() === "2020-04-22";

if ($ok) { echo "\nB214-OK\n"; }
'

run_tinker "restore-er-description" "B214-OK" "$RESTORE_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 214 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
