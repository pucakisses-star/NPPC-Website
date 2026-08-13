#!/usr/bin/env bash
#
# BATCH 213 -- restore Roberto Rivera's biography, for real this time.
#
#   BATCH 211 WAS SUPPOSED TO DO THIS AND DIDN'T. It looks the record up
#   by the slug roberto-rivera. On the live server the slug is
#   roberto-e-rivera. The lookup returns nothing, the script prints its
#   "no prisoner at slug" line, exits without touching anything, and the
#   biography batch 210 wrote is still the one on the page. Batch 212 has
#   exactly the same defect and is exactly the same no-op. This batch
#   tries both slugs and checks the name before it writes.
#
#   HOW THE SLUG MOVED IS NOT ESTABLISHED, and this batch does not
#   pretend otherwise. The Prisoner admin form has no slug field, and
#   HasSlug generates only on creating, so neither the panel nor the model
#   changed it. Batch 210 did run against roberto-rivera -- its
#   biography, its Bergen County Jail case, its deleted MCC Chicago row
#   and its 7 years 5 months 6 days are all live -- so the slug moved
#   after that, by some edit outside these batches. Recorded as an open
#   question rather than a guess.
#
#   THE NAME IS NOT TOUCHED. The live record reads Roberto E. Rivera with
#   a slug to match, and the instruction here was about the description
#   alone. Batch 212 would rename it to Roberto Rivera; while the slug is
#   roberto-e-rivera that batch does nothing, and it should not be made to
#   work until there is a decision about which name the page carries.
#   Renaming would also strand /prisoner/roberto-e-rivera unless the slug
#   moved with it.
#
#   THE TEXT IS THE ORIGINAL, WORD FOR WORD, taken from the "was" field
#   batch 207 recorded before anything changed, plus the one sentence
#   recording the death. It is looser than the structured fields beside it
#   -- "November 16-17, 2012", "reportedly in February 2019" -- and it
#   stays that way, because the instruction was to put the original back,
#   not to improve it.
#
#   Idempotent: the description is written only when it differs.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-213.sh

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
echo "  Batch 213 — Roberto Rivera, biography restored"
echo "==================================================================="

RESTORE_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch213.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = $payload["prisoner"];
$r = $payload["restore"];

// Batch 211 assumed one slug and found nothing. Try each in turn and say
// which one answered, so the next batch does not have to guess either.
$prisoner = null;
$foundAt = null;

foreach ($p["slugs"] as $slug) {
    $candidate = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();

    echo "  slug ", $slug, ": ", ($candidate ? $candidate->name : "no record"), "\n";

    if ($candidate && ! $prisoner) { $prisoner = $candidate; $foundAt = $slug; }
}

if (! $prisoner) { echo "\n  !! none of those slugs exist — nothing changed.\n"; return; }

// The slug moved once already. Confirm this is the right person before
// overwriting a biography.
if (! in_array($prisoner->name, $p["expect_name_one_of"], true)) {
    echo "\n  !! ", $foundAt, " holds ", $prisoner->name, ", which is not this record — stopping.\n";

    return;
}

$wasChars = mb_strlen((string) $prisoner->description);

if ($prisoner->description !== $r["description"]) {
    $prisoner->description = $r["description"];
    $prisoner->save();
    $prisoner->refresh();
    echo "\n  description: ", $wasChars, " chars  ->  ", mb_strlen($prisoner->description), " chars\n";
} else {
    echo "\n  description already restored — nothing to do.\n";
}

echo "\n  ", $prisoner->name, "  [/prisoner/", $prisoner->slug, "]\n";
echo "    matched on   ", $foundAt, "\n";
echo "    full name    ", trim($prisoner->first_name." ".$prisoner->middle_name." ".$prisoner->last_name), "\n";
echo "    name column  ", $prisoner->name, "   (not touched by this batch)\n";

// Everything below came from the curator via batch 210 and must survive a
// description write untouched; printed so that is visible, not assumed.
echo "\n  untouched:\n";
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
echo "\n  ", wordwrap($payload["slug_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["name_note"], 72, "\n  "), "\n";

$ok = $prisoner->description === $r["description"]
    && mb_strlen($prisoner->description) === (int) $payload["expected"]["chars"]
    && str_ends_with($prisoner->description, $payload["expected"]["ends_with"])
    && ! str_contains($prisoner->description, "Ridgewood police arrested him")
    && optional($prisoner->death_date)->toDateString() === "2020-04-22";

if ($ok) { echo "\nB213-OK\n"; }
'

run_tinker "restore-description" "B213-OK" "$RESTORE_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 213 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
