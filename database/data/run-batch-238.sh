#!/usr/bin/env bash
#
# BATCH 238 -- the judge in the Sacramento criminal syndicalism trial.
#
#   SUPERIOR JUDGE DAL M. LEMMON, on all seventeen defendants this archive
#   holds from the 1934-35 Sacramento prosecution of the Cannery and
#   Agricultural Workers Industrial Union.
#
#   THE JUDGE FIELD IS THE EMPTIEST COLUMN IN THE DATABASE. 8,671 of 9,037
#   case rows have nothing in it, 95.9 percent. This fills seventeen from
#   one primary source.
#
#   THE SOURCE IS A NEWSPAPER PAGE, READ RATHER THAN SUMMARISED. The
#   Evening Star of Washington D.C., 2 April 1935, page 2, an Associated
#   Press dispatch datelined Sacramento, April 2: "Superior Judge Dal M.
#   Lemmon will hear tomorrow the retrial motion on behalf of the eight,
#   convicted yesterday by a Jury that freed six other Communists of the
#   same charge." The OCR was fetched from the Library of Congress and
#   read; nothing here comes from a search-result snippet.
#
#   IT SETTLES THE JUDGE AND NOTHING ELSE. He presided over the trial these
#   records already describe -- the archive text says the eighteen were
#   tried at Sacramento -- so the name applies to every defendant in it
#   whatever the verdict.
#
#   WHAT IT DOES NOT SETTLE IS WHO WAS CONVICTED. The dispatch gives the
#   count and not the names: eight convicted, six acquitted, of the
#   fourteen who reached a verdict. All seventeen of our records carry the
#   same generic line -- tried for criminal syndicalism, 1934 to 1935,
#   convictions reversed on appeal in 1937 -- and not one says which side
#   of that split its subject fell on. So no conviction date and no
#   sentence is written here. Marking an acquitted organiser as convicted
#   to fill a column would be worse than leaving the column empty.
#
#   THE VERDICT DATE IS 1 APRIL 1935 and the retrial motion the 3rd. Both
#   are real and neither is written to a record, for the same reason.
#
#   Idempotent: the judge is written only where the field is empty.
#
# Run from the repo root, after git pull, after batch 237:
#   bash database/data/run-batch-238.sh

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
echo "  Batch 238 — Sacramento CAWIU trial, the judge"
echo "==================================================================="

JUDGE_CODE='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch238.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$judge = $payload["judge"];
$written = 0; $already = 0; $bad = [];

echo "\n";

foreach ($payload["people"] as $e) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $e["slug"])->first();

    if (! $p) { echo "  !! no record at ", $e["slug"], "\n"; $bad[] = $e["slug"]; continue; }

    if ($p->name !== $e["expect_name"]) {
        echo "  !! ", $e["slug"], " now holds ", $p->name, " — skipped\n";
        $bad[] = $e["slug"];

        continue;
    }

    $case = $p->cases()->first();

    if (! $case) { echo "  !! no case row for ", $p->name, "\n"; $bad[] = $e["slug"]; continue; }

    if ($case->judge) {
        echo "  ", str_pad($p->name, 22), " already reads ", $case->judge, " — left alone\n";
        $already++;

        continue;
    }

    $case->judge = $judge;
    $case->save();
    $written++;

    echo "  ", str_pad($p->name, 22), " judge -> ", $judge,
        "   [", ($case->convicted ?: "no disposition recorded"), "]\n";
}

echo "\n  judges written             ", $written, "\n";
echo "  already had one            ", $already, "\n";

// The column this is a dent in.
$rows = PrisonerCase::count();
$noJudge = PrisonerCase::whereNull("judge")->orWhere("judge", "")->count();

echo "\n  case rows in all           ", $rows, "\n";
echo "  still without a judge      ", $noJudge, "   (", number_format(100 * $noJudge / max($rows, 1), 1), " percent)\n";

echo "\n  SOURCE\n  ", wordwrap($payload["source"], 70, "\n  "), "\n";
echo "\n  ", wordwrap($payload["what_it_settles"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["what_it_does_not_settle"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["dates_found_not_written"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["method"], 72, "\n  "), "\n";

echo "\n  problems: ", count($bad), "\n";

foreach ($bad as $b) { echo "    !! ", $b, "\n"; }

if (count($bad) === 0 && ($written + $already) === (int) $payload["expected"]["count"]) { echo "\nB238-OK\n"; }
'

run_tinker "sacramento-judge" "B238-OK" "$JUDGE_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 238 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
