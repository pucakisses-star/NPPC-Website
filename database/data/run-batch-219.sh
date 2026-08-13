#!/usr/bin/env bash
#
# BATCH 219 -- Jack Ivory Johnson, Baltimore Panther.
#
#   THE ONE PERSON ON THE LIST WHO WAS ACTUALLY MISSING. Checking the
#   wider itsabouttimebpp coverage turned up two absences. This is the one
#   that could be sourced.
#
#   THE DATABASE ALREADY HAS A JACK JOHNSON and it is the wrong one: the
#   boxer, John Arthur Johnson, 1878-1946, prosecuted under the Mann Act.
#   A name search finds him and stops. That near miss is why this record
#   is filed as Jack Ivory Johnson, with Jack Johnson on the aka field --
#   reusing the short name would have produced the slug jack-johnson-2 and
#   two pages a reader could not tell apart.
#
#   WHAT HE IS ON THE RECORD FOR. Baltimore police officer Donald W. Sager
#   was shot dead on 24 April 1970. Johnson, 23, was arrested in the
#   manhunt, beaten badly enough that supporters had to go to court to get
#   him hospitalised, and coerced into signing a statement written for him
#   that implicated himself, James Powell and Marshall Eddie Conway. He
#   recanted once he had counsel, and refused complete immunity in
#   exchange for testifying against Conway. Conway served forty-four
#   years. Johnson was released in May 2010.
#
#   WHAT IS DELIBERATELY EMPTY. No birth date: he was 23 in April 1970,
#   which places his birth between April 1946 and April 1947, and nothing
#   found narrows it. No sentence: he was convicted and served four
#   decades, but no source states the term. No institution: the interview
#   mentions Jessup, but about the informant in Conway case rather than
#   about where Johnson served, and this database already has fourteen
#   contaminated institutions without adding a guess.
#
#   MAY 2010 IS STORED AS A MONTH, first of the month plus a precision
#   flag, the way batch 206 stored Audrey Hendricks.
#
#   Idempotent: prisoner:add refuses on a duplicate name.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-219.sh

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
echo "  Batch 219 — Jack Ivory Johnson, Baltimore Panther"
echo "==================================================================="

ADD_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch219.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = $payload["prisoner"];

$prisoner = Prisoner::withoutGlobalScopes()->where("name", $p["name"])->first();

if ($prisoner) {
    echo "  ", $p["name"], " already exists [", $prisoner->slug, "] — not created again.\n";
} else {
    Artisan::call("prisoner:add", ["json" => json_encode($p)]);
    echo Artisan::output();

    $prisoner = Prisoner::withoutGlobalScopes()->where("name", $p["name"])->first();
}

if (! $prisoner) { echo "  !! prisoner was not created — stopping.\n"; return; }

$case = $prisoner->cases()->first();

// May 2010, not the first of May.
$rp = $payload["release_precision"];

if ($case && ($case->date_precision[$rp["field"]] ?? null) !== $rp["precision"]) {
    $case->date_precision = array_merge($case->date_precision ?? [], [$rp["field"] => $rp["precision"]]);
    $case->save();
    $case->refresh();
    echo "  release_date set to ", $rp["precision"], " precision\n";
}

echo "\n  ", $prisoner->name, "  [/prisoner/", $prisoner->slug, "]\n";
echo "    aka          ", ($prisoner->aka ?: "(none)"), "\n";
echo "    era / state  ", $prisoner->era, "   ", $prisoner->state, "\n";
echo "    affiliation  ", implode(", ", (array) $prisoner->affiliation), "\n";
echo "    in custody   ", var_export((bool) $prisoner->in_custody, true),
     "   released ", var_export((bool) $prisoner->released, true), "\n";
echo "    birthdate    ", ($prisoner->birthdate ? $prisoner->formatPartialDate("birthdate") : "(none, deliberately)"), "\n";

if ($case) {
    echo "    arrested     ", $case->formatPartialDate("arrest_date"), "\n";
    echo "    released     ", $case->formatPartialDate("release_date"), "\n";
    echo "    convicted    ", ($case->convicted ?: "(none)"), "\n";
    echo "    institution  ", ($case->institution?->name ?: "(none, deliberately)"), "\n";
}

// The whole point of the long name: the boxer must still be a separate record.
$johnsons = Prisoner::withoutGlobalScopes()
    ->where("name", "like", "%Johnson%")
    ->where(function ($q) { $q->where("name", "like", "Jack%")->orWhere("aka", "like", "%Jack Johnson%"); })
    ->get(["name", "slug", "aka"]);

echo "\n  records a search for Jack Johnson now reaches: ", $johnsons->count(), "\n";

foreach ($johnsons as $j) {
    echo "    ", str_pad($j->name, 24), " [", str_pad($j->slug, 20), "] aka: ", ($j->aka ?: "-"), "\n";
}

echo "\n  sources:\n";

foreach ($payload["sources"] as $s) { echo "    - ", $s, "\n"; }

echo "\n  ", wordwrap($payload["omitted"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["institution_note"], 72, "\n  "), "\n";

$ok = $prisoner->slug === $payload["expected"]["slug"]
    && $prisoner->name === $payload["expected"]["name"]
    && $case
    && optional($case->arrest_date)->toDateString() === $payload["expected"]["arrest"]
    && ($case->date_precision["release_date"] ?? null) === "month"
    && $johnsons->count() >= 2;

if ($ok) { echo "\nB219-OK\n"; }
'

run_tinker "add-jack-ivory-johnson" "B219-OK" "$ADD_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 219 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
