#!/usr/bin/env bash
#
# BATCH 209 -- Roberto Rivera, the Occupy Wall Street physician: state,
# birth year, sentencing date, charges and a sourced biography.
#
#   THERE ARE TWO. This updates roberto-rivera, the physician, which is
#   the exact-name match. The other is roberto-jose-maldonado-rivera, the
#   Puerto Rican attorney from the 1985 Macheteros roundup whose sentence
#   Clinton commuted in 1999 -- a different man, untouched here.
#
#   WHAT WAS WRONG OR MISSING:
#     - No state at all, which kept him off every state filter and off
#       the map. He lived in Ridgewood and the case was New Jersey.
#     - No birth date. He was 60 at his arrest in November 2012 and 66 at
#       his sentencing in February 2019; those windows intersect only
#       between February and November 1952, so the year is solid and the
#       day is not. Stored at year precision.
#     - Sentencing recorded only as 2019-02. It was February 22, 2019.
#     - No charges on the case row at all.
#     - A description written from thin sourcing, hedged with "reportedly"
#       three times, and missing everything that was actually found.
#
#   PAROLE AFTER SEVEN YEARS is worth two notes. It dates his first
#   eligibility to February 2026 -- now -- so this record may be about to
#   go stale. And it is the tell that this was a New Jersey STATE
#   prosecution, not a federal one: federal sentences have carried no
#   parole since 1987.
#
#   TWO THINGS DELIBERATELY NOT FIXED, because fixing them means guessing:
#
#   MCC CHICAGO. A federal jail in Illinois attached to a New Jersey
#   state prisoner -- the same shape as the FCI Allenwood entry on Fran
#   Thompson. Fourteen records here carry it, among them Bartolomeo
#   Vanzetti, who was executed in 1927. Replacing it means deciding what
#   it should be for all fourteen; inventing a New Jersey facility for
#   this one man would just be a better-looking error.
#
#   THE SECOND CASE ROW. It says arrested and incarcerated November 15,
#   2012 and released April 21, 2020, with no charge and no conviction.
#   It disagrees with the first row by a day on the arrest and
#   contradicts the 25-year sentence outright on the release. Probably
#   one event recorded twice, but deleting a case row is destructive and
#   the sources do not settle which row is real. Knock-on worth seeing:
#   the record is flagged In Custody while carrying a 2020 release date,
#   and both cannot be true.
#
#   Idempotent: every field is written only when it differs.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-209.sh

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
echo "  Batch 209 — Roberto Rivera (Occupy Wall Street physician)"
echo "==================================================================="

UPDATE_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch209.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = $payload["prisoner"];

$prisoner = Prisoner::withoutGlobalScopes()->where("slug", $p["slug"])->first();

if (! $prisoner) { echo "  no prisoner at slug ", $p["slug"], " — nothing changed.\n"; return; }

// Guard: the right Rivera. The other one is a Puerto Rican attorney.
if ($prisoner->name !== $p["name"]) {
    echo "  slug ", $p["slug"], " holds ", $prisoner->name, ", expected ", $p["name"], " — stopping.\n";

    return;
}

$changed = [];

foreach (["state", "description"] as $f) {
    if ($prisoner->{$f} !== $p[$f]) { $prisoner->{$f} = $p[$f]; $changed[] = $f; }
}

if ((string) $prisoner->birthdate !== $p["birthdate"]) { $prisoner->birthdate = $p["birthdate"]; $changed[] = "birthdate"; }

if (($prisoner->date_precision["birthdate"] ?? null) !== $p["birthdate_precision"]) {
    $prisoner->date_precision = array_merge($prisoner->date_precision ?? [], ["birthdate" => $p["birthdate_precision"]]);
    $changed[] = "birthdate precision";
}

if ($changed) { $prisoner->save(); $prisoner->refresh(); }

// The sentencing case is the one carrying the 25-year sentence; matched on
// that rather than on position, so the second row cannot be hit by accident.
$c = $payload["case"];
$case = $prisoner->cases()->whereNotNull("sentenced_date")->first();
$caseChanged = [];

if (! $case) {
    echo "  !! no case with a sentenced_date — the charges and the exact date were not written.\n";
} else {
    if (optional($case->sentenced_date)->toDateString() !== $c["sentenced_date_to"]) {
        $case->sentenced_date = $c["sentenced_date_to"];
        $caseChanged[] = "sentenced_date";
    }

    // Day precision now that the day is known, in case it was stored as month.
    if (($case->date_precision["sentenced_date"] ?? null) !== "day") {
        $case->date_precision = array_merge($case->date_precision ?? [], ["sentenced_date" => "day"]);
        $caseChanged[] = "sentenced_date precision";
    }

    if ((string) $case->charges !== $c["charges"]) { $case->charges = $c["charges"]; $caseChanged[] = "charges"; }

    if ($caseChanged) { $case->save(); $case->refresh(); }
}

echo "\n  ", $prisoner->name, "  [/prisoner/", $prisoner->slug, "]\n";
echo "    set on prisoner: ", ($changed ? implode(", ", $changed) : "nothing — already correct"), "\n";
echo "    set on case:     ", ($caseChanged ? implode(", ", $caseChanged) : "nothing — already correct"), "\n";
echo "    state            ", ($prisoner->state ?: "(none)"), "\n";
echo "    born             ", $prisoner->formatPartialDate("birthdate"), "\n";
echo "    description      ", str_word_count(strip_tags((string) $prisoner->description)), " words, ",
    substr_count((string) $prisoner->description, "\n\n") + 1, " paragraphs\n";

if ($case) {
    echo "    sentenced        ", $case->formatPartialDate("sentenced_date"), "\n";
    echo "    sentence         ", ($case->sentence ?: "(none)"), "\n";
}

echo "\n  Still wrong, deliberately left for a decision:\n";

$prisoner->load("cases.institution");

foreach ($prisoner->cases as $i => $row) {
    echo "    case ", $i + 1, ": ", ($row->institution?->name ?: "(no institution)"),
        "   arrest ", optional($row->arrest_date)->toDateString() ?: "-",
        "   release ", optional($row->release_date)->toDateString() ?: "-",
        "   ", ($row->convicted ?: "-"), "\n";
}

echo "    in custody flag: ", ($prisoner->in_custody ? "true" : "false"),
    "   released flag: ", ($prisoner->released ? "true" : "false"), "\n";

echo "\n  ", wordwrap($payload["not_touched_mcc"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["not_touched_second_case"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["parole_note"], 72, "\n  "), "\n";

$ok = $prisoner->state === $payload["expected"]["state"]
    && ($prisoner->date_precision["birthdate"] ?? null) === $payload["expected"]["birthdate_precision"]
    && $case
    && optional($case->sentenced_date)->toDateString() === $payload["expected"]["sentenced_date"];

if ($ok) { echo "\nB209-OK\n"; }
'

run_tinker "update-rivera" "B209-OK" "$UPDATE_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 209 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
