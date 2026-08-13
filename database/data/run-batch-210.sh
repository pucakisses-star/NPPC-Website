#!/usr/bin/env bash
#
# BATCH 210 -- Roberto E. Rivera died in custody. Correcting the record,
# per the curator.
#
#   HE DIED ON APRIL 22, 2020, of COVID-19, aged 68, in New Jersey
#   custody. The record said In Custody and was counting 14 years 11
#   months against him as though he were still inside. He had been
#   considered for emergency release as the pandemic moved through the
#   state's prisons; prosecutors objected, and he stayed in.
#
#   THE WORST ERROR ON THE RECORD was a case row reading "Released: April
#   21, 2020". He was not released. He was being considered for release
#   around that date and died the following day. That row turned a death
#   into a release, and it is deleted here -- contents preserved verbatim
#   in the payload, because deleting a case row is destructive.
#
#   MCC CHICAGO GOES. A federal jail in Illinois on a New Jersey state
#   prosecution. Both rows carried it; one row is deleted outright and
#   the surviving row is moved to the Bergen County Jail in Hackensack,
#   where he was held on $1 million cash bail for the six years his case
#   took to reach trial. That institution already exists in this
#   database, so nothing new is minted.
#
#   THE ARREST WAS THE 16th, not the 15th. The New Jersey Attorney
#   General states that Ridgewood police arrested him on November 16.
#
#   ONE CASE ROW, DELIBERATELY. He was held in two places -- the county
#   jail before trial, state prison after sentencing -- but one row is
#   what produces the single unbroken span from November 16, 2012 to
#   April 22, 2020. Splitting it would show two shorter imprisonments and
#   lose the fact that he was never free between them.
#
#   BOTH FLAGS OFF. in_custody false and released FALSE, which looks
#   wrong until you read Prisoner::getIncarcerationYearsArray: it tests
#   died-in-custody as (! released && death_date), and its own comment
#   names this exact state as the one recorded for Luis Rodriguez and
#   Romaine Fitzgerald. Marking him released would be untrue and would
#   break the stats chart.
#
#   2,714 DAYS. death_in_custody_date goes on the case, not just
#   death_date on the prisoner: the saving hook mirrors it into
#   release_date so imprisoned_for_days stops at the death. Seven years,
#   five months, six days.
#
#   1952 SURVIVES, better attested. Three reported ages -- 60 at arrest,
#   66 at sentencing, 68 at death -- intersect only between late February
#   and April 22, 1952. Year certain, day unknown.
#
#   THE OCCUPY WORDING IS DELIBERATELY CAREFUL. He was never charged with
#   an active plot to bomb anything; the convictions were for what was in
#   his apartment. Contemporary reporting called him a vocal supporter
#   who volunteered medical care to protesters, while an Occupy Wall
#   Street press-team member said immediately after the arrest that he
#   knew of no connection between Rivera and the movement. Both are in
#   the biography.
#
#   SUPERSEDES BATCH 209's description, which ends by saying he is
#   eligible for parole after seven years. He was not; he was dead. 209
#   is merged but unrun, so on a first run it writes that text and this
#   overwrites it. This batch does not depend on 209 having run.
#
#   Idempotent: fields written only when they differ; the bad case row is
#   deleted only if it is still there.
#
# Run from the repo root, after git pull (after 209 if running both):
#   bash database/data/run-batch-210.sh

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
echo "  Batch 210 — Roberto E. Rivera died in custody, 22 April 2020"
echo "==================================================================="

FIX_CODE='
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch210.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = $payload["prisoner"];

$prisoner = Prisoner::withoutGlobalScopes()->where("slug", $p["slug"])->first();

if (! $prisoner) { echo "  no prisoner at slug ", $p["slug"], " — nothing changed.\n"; return; }

// Guard: the right Rivera. The other one is a Puerto Rican attorney.
if (! in_array($prisoner->name, $p["expect_name_one_of"], true)) {
    echo "  slug ", $p["slug"], " holds ", $prisoner->name, ", which is not the record this batch is for — stopping.\n";

    return;
}

$changed = [];

foreach (["name", "first_name", "middle_name", "last_name", "description"] as $f) {
    if ($prisoner->{$f} !== $p[$f]) { $prisoner->{$f} = $p[$f]; $changed[] = $f; }
}

if ((string) $prisoner->death_date !== $p["death_date"]) { $prisoner->death_date = $p["death_date"]; $changed[] = "death_date"; }
if ((bool) $prisoner->in_custody !== $p["in_custody"]) { $prisoner->in_custody = $p["in_custody"]; $changed[] = "in_custody"; }
if ((bool) $prisoner->released !== $p["released"]) { $prisoner->released = $p["released"]; $changed[] = "released"; }

if ($changed) { $prisoner->save(); $prisoner->refresh(); }

echo "  set on prisoner: ", ($changed ? implode(", ", $changed) : "nothing — already correct"), "\n";

// --- delete the row that turned a death into a release --------------------
$bad = $prisoner->cases()
    ->whereDate("release_date", $payload["delete_case_matching"]["release_date"])
    ->get();

foreach ($bad as $row) {
    echo "  deleting the false-release row: arrest ", optional($row->arrest_date)->toDateString(),
        ", release ", optional($row->release_date)->toDateString(), "\n";
    $row->delete();
}

if ($bad->isEmpty()) { echo "  no row with a ", $payload["delete_case_matching"]["release_date"], " release — already removed.\n"; }

// --- the surviving row becomes the whole custody ---------------------------
$k = $payload["keep_case"];
$prisoner->load("cases");
$case = $prisoner->cases->first();

if (! $case) {
    echo "  !! no case row left — stopping before this record loses its custody entirely.\n";

    return;
}

$institution = Institution::firstOrCreate(
    ["name" => $k["institution_name"]],
    ["city" => $k["institution_city"], "state" => $k["institution_state"]]
);

$caseChanged = [];

if ((string) $case->institution_id !== (string) $institution->id) {
    $case->institution_id = $institution->id;
    $caseChanged[] = "institution";
}

foreach (["arrest_date", "incarceration_date", "death_in_custody_date", "sentenced_date"] as $f) {
    if (optional($case->{$f})->toDateString() !== $k[$f]) { $case->{$f} = $k[$f]; $caseChanged[] = $f; }
}

foreach (["charges", "convicted", "sentence"] as $f) {
    if ((string) $case->{$f} !== $k[$f]) { $case->{$f} = $k[$f]; $caseChanged[] = $f; }
}

if ($caseChanged) { $case->save(); $case->refresh(); }

$prisoner->refresh();
$prisoner->load("cases.institution");
$case = $prisoner->cases->first();

echo "  set on case:     ", ($caseChanged ? implode(", ", $caseChanged) : "nothing — already correct"), "\n";

echo "\n  ", $prisoner->name, "  [/prisoner/", $prisoner->slug, "]\n";
echo "    born / died      ", $prisoner->formatPartialDate("birthdate"), "   ", $prisoner->formatPartialDate("death_date"), "\n";
echo "    flags            in_custody ", ($prisoner->in_custody ? "true" : "false"),
    ", released ", ($prisoner->released ? "true" : "false"), "   (both false = died inside)\n";
echo "    state            ", ($prisoner->state ?: "(none)"), "\n";
echo "    cases            ", $prisoner->cases->count(), "\n";
echo "    institution      ", $case->institution?->name, " — ", $case->institution?->city, ", ", $case->institution?->state, "\n";
echo "    arrested         ", optional($case->arrest_date)->toDateString(), "\n";
echo "    died in custody  ", optional($case->death_in_custody_date)->toDateString(), "\n";
echo "    release_date     ", optional($case->release_date)->toDateString(), "   (mirrored from the death by the saving hook)\n";
echo "    imprisoned       ", $case->imprisoned_for_days, " days   (expected ", $payload["expected"]["days"], ")\n";

$years = $prisoner->getIncarcerationYearsArray();

echo "    years counted    ", (count($years) ? min($years)."-".max($years)." (".count($years).")" : "none"),
    "   — must stop at 2020, not run to today\n";

$mcc = $prisoner->cases->contains(fn ($c) => str_contains((string) $c->institution?->name, "MCC Chicago"));

echo "\n    MCC Chicago still on this record: ", ($mcc ? "YES — should be gone" : "no"), "\n";
echo "    a case still claiming a 2020-04-21 release: ",
    ($prisoner->cases->contains(fn ($c) => optional($c->release_date)->toDateString() === "2020-04-21") ? "YES" : "no"), "\n";

echo "\n  ", wordwrap($payload["flags_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["birthdate_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["one_case_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["name_note"], 72, "\n  "), "\n";

$ok = $prisoner->cases->count() === (int) $payload["expected"]["cases_after"]
    && (int) $case->imprisoned_for_days === (int) $payload["expected"]["days"]
    && (string) $prisoner->death_date !== ""
    && ! $prisoner->in_custody
    && ! $prisoner->released
    && ! $mcc
    && $case->institution?->name === $payload["expected"]["institution"]
    && count($years) && max($years) === 2020;

if ($ok) { echo "\nB210-OK\n"; }
'

run_tinker "correct-rivera" "B210-OK" "$FIX_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 210 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "The deleted case row is preserved verbatim in batch210.json under"
echo "delete_case_matching, should it ever need to come back."
