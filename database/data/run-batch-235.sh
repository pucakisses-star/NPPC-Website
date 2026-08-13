#!/usr/bin/env bash
#
# BATCH 235 -- Carlos Alfredo Cortéz, added.
#
#   THE EIGHTEEN MONTHS ARE STORED AS MONTHS. The model has a column for
#   exactly this case: computeImprisonedForDays gives a documented
#   duration priority over date arithmetic and walks the day count forward
#   from the start, rather than measuring between two dates that are only
#   approximate. Its own comment cites Bill Sutherland, whose 38 months
#   are known while his endpoints are not. So the bare years 1945 and 1947
#   stay bare years, and the time served reads 546 days -- eighteen months
#   from the start of 1945 -- instead of being inflated to a full two
#   years by the gap between them.
#
#   FOUR SUPPLIED FACTS WERE CHECKED AND HOLD. Alfredo is right; his full
#   name was Carlos Alfredo Cortez. The birth of 13 August 1923 is right,
#   in Milwaukee. The eighteen months are right, and so is the two-year
#   sentence the biography gives. The years fit as well: he joined the IWW
#   in 1947 shortly after finishing the sentence, which puts the release
#   at the end of 1946 or the start of 1947 and the start in 1945.
#
#   ONE SUPPLIED FACT IS CONTRADICTED, AND IT IS THE ONE TO LOOK AT. The
#   death was given as 18 January 2005; two independent sources give 19
#   January 2005, and the record is created with the 19th. The age settles
#   nothing -- born in August, he was 81 on either day. If the 18th came
#   from a source then the 18th wins and this is a one-line change.
#
#   BOTH SPELLINGS OF THE NAME ARE REAL. Wikipedia and the IWW write
#   Cortez; the Smithsonian Archives of American Art and the Chicago
#   Literary Hall of Fame write Cortéz. The supplied spelling is the name;
#   the plain Cortez goes in the aka beside Koyokuikatl, the Nahuatl name
#   he took, so he can be found either way. The slug is carlos-cortez
#   regardless, since slugs strip the accent.
#
#   THE PRISON IS ADDED AND THE STATE IS NOT. Sandstone, Minnesota is
#   where the eighteen months were served and it already exists here with
#   twelve other prisoners attached. The prisoner state is left empty on
#   purpose: on this archive that field is where the case was, not where
#   the prison was, and his draft refusal was likely prosecuted in
#   Milwaukee -- likely, not verified, and a guessed state appears on the
#   map as a fact.
#
#   THE BIOGRAPHY IS VERBATIM, including the two small grammatical slips
#   in it. No fix was asked for, and the same call was made for Pío del
#   Pilar.
#
#   Idempotent: the create step is skipped when he is already there, and
#   the finish step writes each field only when it differs.
#
# Run from the repo root, after git pull, after batch 234:
#   bash database/data/run-batch-235.sh

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
echo "  Batch 235 — Carlos Alfredo Cortéz"
echo "==================================================================="

CREATE_JSON="database/data/fixes/batch235-create.json"

echo
echo "--- check-existing"
CHECK_CODE='
use App\Models\Prisoner;
$n = Prisoner::withoutGlobalScopes()->where("name", "Carlos Cortéz")
    ->orWhere("slug", "carlos-cortez")->count();
echo $n === 0 ? "CORTEZ-ABSENT\n" : "CORTEZ-PRESENT (".$n.")\n";
'
CHECK_OUT=$(php artisan tinker --execute="$CHECK_CODE" 2>&1) || true
printf '%s\n' "$CHECK_OUT"

if grep -q "CORTEZ-ABSENT" <<<"$CHECK_OUT"; then
    echo
    echo "--- create"
    if [ ! -f "$CREATE_JSON" ]; then
        echo "  !! missing $CREATE_JSON"
        FAILED+=("create")
    elif ! php artisan prisoner:add "$(cat "$CREATE_JSON")"; then
        echo "  !! prisoner:add failed"
        FAILED+=("create")
    fi
elif grep -q "CORTEZ-PRESENT" <<<"$CHECK_OUT"; then
    echo "  already present — skipping the create step"
else
    echo "  !! could not determine whether he exists"
    FAILED+=("check-existing")
fi

# prisoner:add takes neither date precision nor imprisoned_for_months, so the
# two things that make this record honest are set here.
FINISH_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch235.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withoutGlobalScopes()->where("name", $payload["prisoner"]["name"])->first();

if (! $p) { echo "  !! he is not there — the create step must have failed\n"; return; }

foreach ($payload["precision"]["prisoner"] as $field => $prec) {
    if (($p->date_precision[$field] ?? null) !== $prec) {
        $p->date_precision = array_merge($p->date_precision ?? [], [$field => $prec]);
        echo "  ", str_pad($field." precision", 26), " -> ", $prec, "\n";
    }
}

$p->save();
$p->refresh();

$case = $p->cases()->first();

if (! $case) { echo "  !! no case row — stopping.\n"; return; }

foreach ($payload["precision"]["case"] as $field => $prec) {
    if (($case->date_precision[$field] ?? null) !== $prec) {
        $case->date_precision = array_merge($case->date_precision ?? [], [$field => $prec]);
        echo "  ", str_pad($field." precision", 26), " -> ", $prec, "\n";
    }
}

if ((int) $case->imprisoned_for_months !== (int) $payload["imprisoned_for_months"]) {
    echo "  ", str_pad("imprisoned_for_months", 26), " ", ($case->imprisoned_for_months ?? "(none)"),
        "  ->  ", $payload["imprisoned_for_months"], "\n";
    $case->imprisoned_for_months = $payload["imprisoned_for_months"];
}

// imprisoned_for_days is recomputed by the saving hook from the months.
$case->save();
$case->refresh();

$e = $payload["expected"];

echo "\n  ", $p->name, "  [/prisoner/", $p->slug, "]\n";
echo "    middle name   ", ($p->middle_name ?: "(none)"), "\n";
echo "    aka           ", ($p->aka ?: "(none)"), "\n";
echo "    born          ", $p->formatPartialDate("birthdate"), "\n";
echo "    died          ", $p->formatPartialDate("death_date"), "   (supplied as 18 January — see the note)\n";
echo "    age           ", ($p->age ?? "(none)"), "\n";
echo "    era           ", $p->era, "\n";
echo "    ideologies    ", implode(", ", (array) $p->ideologies), "\n";
echo "    affiliation   ", implode(", ", (array) $p->affiliation), "\n";
echo "    state         ", ($p->state ?: "(empty on purpose — see the note)"), "\n";
echo "    photo         ", ($p->photo ?: "(none)"), "\n";
echo "    incarcerated  ", $case->formatPartialDate("incarceration_date"), "\n";
echo "    released      ", $case->formatPartialDate("release_date"), "\n";
echo "    served        ", $case->imprisoned_for_months, " months = ", $case->imprisoned_for_days, " days\n";
echo "    institution   ", ($case->institution?->name ?: "(none)"),
    "   ", ($case->institution?->city ?: ""), " ", ($case->institution?->state ?: ""), "\n";

// What the day count would have been without the months column, so the
// difference is visible rather than asserted.
if ($case->incarceration_date && $case->release_date) {
    echo "\n    without the months column the two years would have read ",
        (int) $case->incarceration_date->diffInDays($case->release_date), " days\n";
}

echo "\n  the biography reads:\n\n  ", wordwrap((string) $p->description, 72, "\n  "), "\n";

echo "\n  ", wordwrap($payload["months_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["confirmed"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["death_date_conflict"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["name_spelling"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["prison_added"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["state_left_empty"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["bio_verbatim"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["institution_duplicate"], 72, "\n  "), "\n";

$bad = [];

if ($p->middle_name !== $e["middle_name"]) { $bad[] = "middle_name"; }
if (optional($p->birthdate)->toDateString() !== $e["birthdate"]) { $bad[] = "birthdate"; }
if (optional($p->death_date)->toDateString() !== $e["death_date"]) { $bad[] = "death_date"; }
if (($p->date_precision["birthdate"] ?? null) !== "day") { $bad[] = "birthdate precision"; }
if (($p->date_precision["death_date"] ?? null) !== "day") { $bad[] = "death_date precision"; }
if (optional($case->incarceration_date)->toDateString() !== $e["incarceration_date"]) { $bad[] = "incarceration_date"; }
if (optional($case->release_date)->toDateString() !== $e["release_date"]) { $bad[] = "release_date"; }
if (($case->date_precision["incarceration_date"] ?? null) !== "year") { $bad[] = "incarceration precision"; }
if (($case->date_precision["release_date"] ?? null) !== "year") { $bad[] = "release precision"; }
if ((int) $case->imprisoned_for_months !== (int) $e["months"]) { $bad[] = "months"; }
if ((int) $case->imprisoned_for_days !== (int) $e["days"]) {
    $bad[] = "days is ".$case->imprisoned_for_days.", expected ".$e["days"];
}
if (($case->institution?->name) !== $e["institution"]) { $bad[] = "institution"; }

echo "\n  problems: ", count($bad), "\n";

foreach ($bad as $b) { echo "    !! ", $b, "\n"; }

if (count($bad) === 0) { echo "\nB235-OK\n"; }
'

run_tinker "finish-cortez" "B235-OK" "$FINISH_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 235 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
