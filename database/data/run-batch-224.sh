#!/usr/bin/env bash
#
# BATCH 224 -- Albert Blumberg gets his custody dates.
#
#   HIS CASE ROW HAD NO DATES AT ALL, only the sentence text "Held on
#   $40,000 bail". Arrested and incarcerated 30 September 1954, released
#   November 1954.
#
#   SUPPLIED BY THE CURATOR AND RECORDED AS SUCH. A search turned up
#   nothing that contradicts these dates and nothing that confirms the
#   specific days either. The 1954 Smith Act membership-clause prosecution
#   is well attested; the day-level detail is not something the public
#   sources carry. That is worth saying plainly rather than implying a
#   verification that did not happen.
#
#   NOVEMBER, NOT THE FIRST OF NOVEMBER. The release is stored as
#   1954-11-01 with month precision, which is the archive convention for a
#   month-precision date -- the same as Audrey Hendricks in batch 206 --
#   and the precision flag is what stops the page asserting a day nobody
#   claims. One consequence is worth knowing: imprisoned_for_days is
#   computed from the stored date, so the duration comes out at 32 days.
#   That is a floor. If he was released later in November the real figure
#   is up to thirty days more.
#
#   NOTHING ELSE IS TOUCHED. The sentence text, the empty institution and
#   the custody flags stay as they are, and so do the birth and death
#   dates from batch 223 -- which this batch reads back and prints, so a
#   write to the case cannot quietly disturb them.
#
#   Independent of batch 223: that one writes the prisoner row, this one
#   writes the case. Either order works.
#
#   Idempotent: every field is written only when it differs.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-224.sh

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
echo "  Batch 224 — Albert Blumberg, custody dates"
echo "==================================================================="

CASE_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch224.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withoutGlobalScopes()->where("slug", $payload["prisoner"]["slug"])->first();

if (! $p) { echo "  !! no prisoner at slug ", $payload["prisoner"]["slug"], "\n"; return; }

if ($p->name !== $payload["prisoner"]["expect_name"]) {
    echo "  !! that slug holds ", $p->name, ", not ", $payload["prisoner"]["expect_name"], " — stopping.\n";

    return;
}

$case = $p->cases()->first();

if (! $case) { echo "  !! ", $p->name, " has no case row to write to — stopping.\n"; return; }

$c = $payload["case"];

foreach ([["arrest_date", "arrest_precision"], ["incarceration_date", "incarceration_precision"], ["release_date", "release_precision"]] as [$field, $precKey]) {
    $was = optional($case->{$field})->toDateString() ?: "(none)";

    if ($was !== $c[$field]) {
        $case->{$field} = $c[$field];
        echo "  ", str_pad($field, 20), " ", str_pad($was, 12), " -> ", $c[$field], "\n";
    }

    if (($case->date_precision[$field] ?? null) !== $c[$precKey]) {
        $case->date_precision = array_merge($case->date_precision ?? [], [$field => $c[$precKey]]);
        echo "  ", str_pad($field." precision", 20), " -> ", $c[$precKey], "\n";
    }
}

$case->save();
$case->refresh();
$p->refresh();

echo "\n  ", $p->name, "  [/prisoner/", $p->slug, "]\n";
echo "    arrested      ", $case->formatPartialDate("arrest_date"), "\n";
echo "    incarcerated  ", $case->formatPartialDate("incarceration_date"), "\n";
echo "    released      ", $case->formatPartialDate("release_date"), "\n";
echo "    duration      ", $case->imprisoned_for_days, " days   (a floor — see the note below)\n";
echo "    sentence      ", ($case->sentence ?: "(none)"), "   (untouched)\n";
echo "    institution   ", ($case->institution?->name ?: "(none)"), "   (untouched)\n";

// Batch 223 wrote these. A case write must not have disturbed them.
echo "\n  from batch 223, read back:\n";
echo "    born          ", ($p->birthdate ? $p->formatPartialDate("birthdate") : "(not set — batch 223 has not run)"), "\n";
echo "    died          ", ($p->death_date ? $p->formatPartialDate("death_date") : "(not set — batch 223 has not run)"), "\n";
echo "    middle name   ", ($p->middle_name ?: "(not set)"), "\n";
echo "    in_custody    ", var_export((bool) $p->in_custody, true),
     "   released ", var_export((bool) $p->released, true), "\n";

echo "\n  ", wordwrap($payload["sourcing"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["precision_note"], 72, "\n  "), "\n";

$ok = optional($case->arrest_date)->toDateString() === $payload["expected"]["arrest"]
    && optional($case->incarceration_date)->toDateString() === $payload["expected"]["arrest"]
    && optional($case->release_date)->toDateString() === $payload["expected"]["release"]
    && ($case->date_precision["release_date"] ?? null) === "month"
    && (int) $case->imprisoned_for_days === (int) $payload["expected"]["days"];

if ($ok) { echo "\nB224-OK\n"; }
'

run_tinker "set-blumberg-case-dates" "B224-OK" "$CASE_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 224 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
