#!/usr/bin/env bash
#
# BATCH 215 -- impossible institutions on the Black Panther prisoners.
#
#   TWELVE OF THE TWENTY-SEVEN people on the itsabouttimebpp.com Black
#   Panther Political Prisoners list carry an institution that cannot be
#   theirs. Albert Woodfox, who spent nearly 44 years at Angola, is filed
#   at a federal complex in Pennsylvania. Herman Wallace, same case, is in
#   Tennessee. Ed Poindexter and David Rice, both Nebraska prisoners, are
#   in Oregon and Florida. Assata Shakur, held in New Jersey, is at a
#   Pennsylvania state prison.
#
#   FOUR ARE REPLACED, NINE ARE CLEARED. Woodfox and Wallace to the
#   Louisiana State Penitentiary, Poindexter and Rice to the Nebraska
#   State Penitentiary -- those four are not in doubt. For the other nine
#   the true facility is not established here, so the false one is removed
#   and nothing is put in its place. A blank field says nothing; a wrong
#   one publishes a lie.
#
#   THIS IS A FOURTEEN-INSTITUTION PROBLEM, NOT A THIRTEEN-ROW ONE.
#   Fourteen institutions in this database hold sets of cases that cannot
#   belong together -- people from eight to twelve different states, eras
#   spanning up to three centuries, at facilities that in some cases did
#   not exist yet. FCI Waseca, a federal womens prison opened in the
#   1990s, holds a man from the 1700s. Between them these fourteen carry
#   roughly 180 case rows. This batch fixes the 13 that fall on this list.
#   The remaining ~167 are a separate job and a separate decision.
#
#   NOTHING ELSE ON THE CASE IS TOUCHED. Dates, charges, sentence and
#   conviction text are left exactly as they are.
#
#   Idempotent: each entry matches on the wrong institution name, so once
#   a row is fixed it no longer matches and is skipped.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-215.sh

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
echo "  Batch 215 — impossible institutions, Black Panther prisoners"
echo "==================================================================="

FIX_CODE='
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch215.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$applied = 0; $already = 0; $missing = 0;

foreach ($payload["entries"] as $e) {
    $prisoner = Prisoner::withoutGlobalScopes()->where("slug", $e["slug"])->first();

    if (! $prisoner) { echo "  !! no prisoner at slug ", $e["slug"], "\n"; $missing++; continue; }

    $hit = null;

    foreach ($prisoner->cases()->with("institution")->get() as $case) {
        if ($case->institution && $case->institution->name === $e["wrong"]) { $hit = $case; break; }
    }

    if (! $hit) {
        echo "  ", str_pad($prisoner->name, 26), " no case at ", $e["wrong"], " — already done\n";
        $already++;
        continue;
    }

    if ($e["action"] === "set") {
        $t = $e["to"];

        // firstOrCreate matches on name alone in this codebase, so the lookup
        // is explicit and the city/state only apply to a genuinely new row.
        $inst = Institution::where("name", $t["name"])->first();

        if (! $inst) {
            $inst = Institution::create(["name" => $t["name"], "city" => $t["city"], "state" => $t["state"]]);
            echo "  (created institution ", $t["name"], ")\n";
        }

        $hit->institution_id = $inst->getKey();
        $hit->save();

        echo "  ", str_pad($prisoner->name, 26), " ", $e["wrong"], "  ->  ", $inst->name, "\n";
    } else {
        $hit->institution_id = null;
        $hit->save();

        echo "  ", str_pad($prisoner->name, 26), " ", $e["wrong"], "  ->  (cleared)\n";
    }

    $applied++;
}

echo "\n  applied ", $applied, ", already correct ", $already, ", missing prisoners ", $missing, "\n";

// Re-read every entry and confirm the wrong institution is gone. This is the
// real test: the write above could succeed and still leave a second case row
// pointing at the same bad institution.
$stillWrong = [];

foreach ($payload["entries"] as $e) {
    $prisoner = Prisoner::withoutGlobalScopes()->where("slug", $e["slug"])->first();

    if (! $prisoner) { continue; }

    foreach ($prisoner->cases()->with("institution")->get() as $case) {
        if ($case->institution && $case->institution->name === $e["wrong"]) {
            $stillWrong[] = $prisoner->name." @ ".$e["wrong"];
        }
    }
}

echo "\n  verification — rows still holding a wrong institution: ", count($stillWrong), "\n";

foreach ($stillWrong as $s) { echo "    !! ", $s, "\n"; }

echo "\n  where the twelve stand now:\n";

foreach (array_unique(array_column($payload["entries"], "slug")) as $slug) {
    $prisoner = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();

    if (! $prisoner) { continue; }

    $names = [];

    foreach ($prisoner->cases()->with("institution")->get() as $case) {
        $names[] = $case->institution?->name ?: "(none)";
    }

    echo "    ", str_pad($prisoner->name, 26), " ", implode(" | ", $names), "\n";
}

echo "\n  ", wordwrap($payload["contamination_note"], 72, "\n  "), "\n";

$ok = count($stillWrong) === 0 && $missing === 0;

if ($ok) { echo "\nB215-OK\n"; }
'

run_tinker "fix-institutions" "B215-OK" "$FIX_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 215 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
