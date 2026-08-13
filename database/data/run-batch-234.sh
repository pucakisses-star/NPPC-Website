#!/usr/bin/env bash
#
# BATCH 234 -- James Larkin: the whole case, and a charge that was wrong.
#
#   THE CHARGE WAS WRONG, AND NOT SLIGHTLY. The case read as a federal
#   prosecution under the Espionage Act of 1917 and/or the Sedition Act of
#   1918, with a sentence line to match. Larkin was prosecuted by the
#   State of New York under its criminal anarchy law -- which is what the
#   supplied account says, and what this record own biography has said all
#   along, twice. Different statute, different sovereign, different court.
#   Replaced rather than appended to, with the old text printed in full
#   before the write so it stays recoverable from this log.
#
#   THREE SUPPLIED DATES WERE CHECKED AND HOLD EXACTLY: the arrest of 7
#   November 1919, the day of the raids on the second anniversary of the
#   Bolshevik revolution; the five-to-ten-year sentence; and the free
#   pardon from Governor Al Smith on 17 January 1923. So do all three
#   prisons, and all three already exist in this database.
#
#   ONE SUPPLIED DATE IS CONTRADICTED AND IS NOT RECORDED. The account
#   puts the start of the trial at 5 April 1920; two independent sources
#   put it at 30 January 1920. There is no trial-start column on a case,
#   so nothing is lost by leaving it out, and writing a contested date
#   into the prose would have been worse than omitting it.
#
#   THE CONFINEMENT IS 989 DAYS, NOT 1,167. This prosecution produced two
#   separate custody periods and a case row holds one. He was arrested on
#   7 November 1919 and released thirteen days later on $15,000 bail, and
#   he was at liberty through the trial. Recording arrest to pardon as
#   continuous would claim 1,167 days of imprisonment for a man who spent
#   most of that winter and spring out on bail. What is recorded runs from
#   sentencing to pardon; the thirteen days are described in the case text.
#
#   THE INCARCERATION DATE COMES FROM THE SOURCES, NOT THE LIST. The list
#   gives the conviction as April 1920 and no date for the start of
#   custody. The verdict came in late April, the sentence was passed on 3
#   May 1920, and that is when he went to Sing Sing -- a contemporary
#   article of 14 May 1920 is headlined that Larkin had been hurried
#   there. If he was remanded at the verdict rather than at sentencing,
#   custody began a few days before what is recorded.
#
#   THE DEPORTATION IS IN THE CASE TEXT, NOT THE EXILE FIELDS. He was not
#   exiled; he was expelled from the United States and delivered home to
#   Ireland, where he spent the rest of his life in public office. Nine
#   days of exile for the crossing would be a false record and a false
#   number in the exile totals.
#
#   Idempotent: every field is written only when it differs.
#
# Run from the repo root, after git pull, after batch 233:
#   bash database/data/run-batch-234.sh

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
echo "  Batch 234 — James Larkin, criminal anarchy not the Espionage Act"
echo "==================================================================="

LARKIN_CODE='
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch234.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withoutGlobalScopes()->where("slug", $payload["prisoner"]["slug"])->first();

if (! $p) { echo "  !! no prisoner at that slug\n"; return; }

if ($p->name !== $payload["prisoner"]["expect_name"]) {
    echo "  !! that slug holds ", $p->name, " — stopping.\n";

    return;
}

$case = $p->cases()->first();

if (! $case) { echo "  !! no case row — stopping.\n"; return; }

$bad = [];

// The wrong charge, printed in full before it goes, so it stays recoverable.
echo "\n  THE CHARGE BEING REPLACED:\n\n  ", wordwrap((string) $case->charges, 70, "\n  "), "\n";
echo "\n  THE SENTENCE TEXT BEING REPLACED:\n\n  ", wordwrap((string) $case->sentence, 70, "\n  "), "\n\n";

// Sing Sing must already exist. This batch does not create institutions --
// inventing a second Sing Sing is exactly how the institution rows got
// tangled before.
$inst = Institution::where("name", $payload["institution_name"])->first();

if (! $inst) {
    $bad[] = "no institution named ".$payload["institution_name"];
} elseif ($case->institution_id !== $inst->id) {
    echo "  institution        ", str_pad($case->institution_id ? "(some other)" : "(none)", 14),
        " ->  ", $inst->name, "\n";
    $case->institution_id = $inst->id;
} else {
    echo "  institution        already ", $inst->name, "\n";
}

foreach ($payload["case"] as $field => $value) {
    $current = $case->{$field};
    $was = $current instanceof \DateTimeInterface ? $current->format("Y-m-d") : (string) $current;

    if ($was !== $value) {
        $case->{$field} = $value;
        $shown = mb_strlen((string) $value) > 40 ? mb_substr((string) $value, 0, 37)."..." : $value;
        echo "  ", str_pad($field, 18), " -> ", $shown, "\n";
    }
}

foreach ($payload["precision"] as $field => $prec) {
    if (($case->date_precision[$field] ?? null) !== $prec) {
        $case->date_precision = array_merge($case->date_precision ?? [], [$field => $prec]);
    }
}

$case->save();
$case->refresh();

echo "\n  ", $p->name, "  [/prisoner/", $p->slug, "]\n";
echo "    arrested      ", $case->formatPartialDate("arrest_date"), "\n";
echo "    sentenced     ", $case->formatPartialDate("sentenced_date"), "\n";
echo "    incarcerated  ", $case->formatPartialDate("incarceration_date"), "\n";
echo "    released      ", $case->formatPartialDate("release_date"), "   (the pardon)\n";
echo "    duration      ", $case->imprisoned_for_days, " days\n";
echo "    institution   ", ($case->institution?->name ?: "(none)"), "\n";
echo "    convicted     ", $case->convicted, "\n";
echo "    flags         in_custody ", var_export((bool) $p->in_custody, true),
    "   released ", var_export((bool) $p->released, true),
    "   in_exile ", var_export((bool) $p->in_exile, true), "   (untouched)\n";
echo "    exile dates   ", ($case->in_exile_since ?: "(none)"), "  ", ($case->end_of_exile ?: "(none)"),
    "   (deliberately empty — see the note)\n";
echo "    death_date    ", ($p->death_date ? $p->formatPartialDate("death_date") : "(none)"), "   (untouched)\n";

echo "\n  the charge now reads:\n\n  ", wordwrap((string) $case->charges, 70, "\n  "), "\n";

// The two custody periods, so the 989 is visibly a choice and not an error.
if ($case->arrest_date && $case->release_date) {
    echo "\n    arrest to pardon would have been ",
        (int) $case->arrest_date->diffInDays($case->release_date), " days;",
        " the 13 days on bail and the months at liberty are not counted.\n";
}

// Nothing about the biography is touched, but it is the witness against the
// old charge, so the sentence that names the real law is printed.
if (preg_match("/[^.]*criminal anarchy[^.]*\./i", (string) $p->description, $m)) {
    echo "\n  the biography said so all along:\n     ", wordwrap(trim($m[0]), 68, "\n     "), "\n";
}

echo "\n  ", wordwrap($payload["charge_fix"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["dates_confirmed"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["trial_conflict"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["sentencing_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["bail_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["exile_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["contamination"], 72, "\n  "), "\n";

$e = $payload["expected"];

foreach (["arrest_date", "sentenced_date", "incarceration_date", "release_date"] as $field) {
    if (optional($case->{$field})->toDateString() !== $e[$field]) { $bad[] = $field; }
}

if ((int) $case->imprisoned_for_days !== (int) $e["days"]) {
    $bad[] = "duration is ".$case->imprisoned_for_days.", expected ".$e["days"];
}

if (($case->institution?->name) !== $e["institution"]) { $bad[] = "institution"; }

if (str_contains((string) $case->charges, "Espionage")) { $bad[] = "the Espionage Act text survived"; }

echo "\n  problems: ", count($bad), "\n";

foreach ($bad as $b) { echo "    !! ", $b, "\n"; }

if (count($bad) === 0) { echo "\nB234-OK\n"; }
'

run_tinker "james-larkin" "B234-OK" "$LARKIN_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 234 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
