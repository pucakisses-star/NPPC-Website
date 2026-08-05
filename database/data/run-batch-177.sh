#!/usr/bin/env bash
#
# BATCH 177 -- birth and death dates for 131 Jackson 1961 arrestees, from the
# same CRDL material batch 176 was built from.
#
#   THE SAME LEDGER, READ FOR A DIFFERENT FIELD. Batch 176 asked the CRDL
#   mining who was missing, and the answer was thirty people. This asks it
#   what it knows about the people already here, and the answer is much
#   larger: the catalog's AUTHORITY PAGES carry birth and death years that
#   its photograph titles do not, and 145 of the ledger entries state one.
#
#   131 RECORDS IN THIS ARCHIVE HAVE NO DATE OF BIRTH AND ARE ABOUT TO HAVE
#   ONE. Almost all of the 1961 cohort was entered with an arrest and
#   nothing else, so this is the difference between a name on a list and a
#   person with an age.
#
#   THE LEDGER STATES THE SAME PERSON MORE THAN ONCE, and that is the whole
#   risk here. Carroll Gary Barber appears four times: three passes say
#   "DOB/DOD: Not documented" and the fourth gives 1924–1999. Taking the
#   first answer would have lost him his dates; taking any answer without
#   looking would have let a contradiction through silently. So every pass
#   was collected and compared before anything was written. Where two
#   passes give the same year at different precision the finer one wins.
#   ACROSS ALL 145 PEOPLE THERE WERE NO CONTRADICTIONS — which is itself
#   worth knowing about this source.
#
#   FIVE ARE FULL DATES, not years: Cordell Hull Reagon (February 22, 1943
#   – November 12, 1996), Percy Sutton (November 24, 1920 – December 26,
#   2009), Wyatt Tee Walker (August 16, 1928 – January 23, 2018), Michael
#   Leon Prichard (March 31, 1943 – November 11, 2007) and Margaret Winonah
#   Beamer, whose September 10, 1941 upgrades the bare 1941 batch 176 put
#   in yesterday. The rest go in at YEAR PRECISION, which is what the
#   catalog gives; a stored January 1 would read as a birthday.
#
#   ONE DEATH DATE IS HELD BACK. See the report at the end.
#
#   THREE SOURCED DURATIONS, and only one of them can be published as a
#   number. Robert Filner refused to post bond after his June 16, 1961
#   arrest and stayed in about two months — that is a whole number of
#   months with no endpoints, which is exactly what imprisoned_for_months
#   is for. Byron Baer and Rick Sheviakov each did 45 days, Parchman
#   included, as each other's cellmates. Forty-five days is neither a pair
#   of dates nor a whole number of months, and this archive stores
#   durations only in those two forms. One month would understate it by two
#   weeks and two months would overstate it by two, so their records carry
#   the 45 days in prose, where it is exact, and publish no counter.
#
#   MICHAEL AUDAIN'S ARREST MOVES A DAY. Batch 176 entered him on June 8
#   from the photograph. The catalog's own biography puts the arrest on
#   June 7 at the Greyhound terminal, after he walked into a restroom
#   marked for African Americans, and sends him on to Parchman. June 8 is
#   when the picture was processed. A photograph date is not an arrest
#   date.
#
#   THE TWO NAMES BATCH 176 REFUSED TO CREATE ARE CREATED HERE, because
#   this pass over the material changed what is known about both:
#
#     JESSE JAMES HARRIS. Held back because one entry rated the split from
#     Jessie L. Harris only moderate and flagged a possible identity
#     overlap. A second entry states the same split at high confidence, and
#     a third gives the Livingston Park man a catalog authority of his own
#     — Harris, Jesse, 1942- — which also lands as his birth year here.
#
#     LEWELL A. CECIL. Held back because Lewell A. Woods Jr. sits in the
#     same June 10 group and is not a name two men are likely to share.
#     What changed is the evidence: the Cecil form has an item-level
#     photograph record, verified singly, while the Woods form came from a
#     collection listing whose item record and full title were never
#     recovered.
#
#   NEITHER IS PUBLISHED AS SETTLED. Both new records carry the doubt in
#   their own text, and the same note is appended to the record each might
#   duplicate, so a reader who lands on either one sees the question. That
#   is the difference between publishing a documented arrest and asserting
#   a person.
#
#   Idempotent: dates are written only where the field is empty (or a year
#   is being replaced by a full date), records are created only when absent,
#   and every appended paragraph is keyed on a marker.
#
# Run from the repo root, after git pull (after batch 176):
#   bash database/data/run-batch-177.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"; shift
    echo; echo "--- ${label}"
    if "$@"; then return 0; fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}"); return 0
}

echo "==================================================================="
echo "  Batch 177 — birth and death dates for 131 Jackson 1961 arrestees"
echo "==================================================================="

apply_dates() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch177.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$s = $payload["source"];

echo "  source: ", $s["catalog"], "\n";
echo "          ", $s["note"], "\n";
echo "          ", $s["people_with_a_date_in_the_ledger"], " people carry a date across the ledger;\n";
echo "          contradictions between passes: ", $s["contradictions_found"], "\n\n";

$wrote = 0; $skipped = 0; $missing = []; $upgraded = 0;

echo "  ", str_pad("record", 34), str_pad("born", 22), "died\n";
echo "  ", str_repeat("-", 78), "\n";

foreach ($payload["dates"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->first();

    if (! $p) { $missing[] = $row["slug"]; continue; }

    $touched = false;

    foreach (["birthdate", "death_date"] as $f) {
        if (empty($row[$f])) { continue; }

        // Written only into an empty field, with one exception: a bare year
        // already stored is replaced by a full date when the catalog has one.
        $isUpgrade = $p->{$f} && count($row[$f]) === 3 && $p->datePrecisionFor($f) !== "day";

        if ($p->{$f} && ! $isUpgrade) { continue; }
        if ($isUpgrade) { $upgraded++; }

        $p->setPartialDate($f, $row[$f][0], $row[$f][1] ?? null, $row[$f][2] ?? null);
        $touched = true;
    }

    if (! $touched) { $skipped++; continue; }

    $p->save();
    $p->refresh();
    $wrote++;

    echo "  ", str_pad($row["slug"], 34),
        str_pad(($p->birthdate ? $p->formatPartialDate("birthdate")." [".$p->datePrecisionFor("birthdate")."]" : "-"), 22),
        ($p->death_date ? $p->formatPartialDate("death_date")." [".$p->datePrecisionFor("death_date")."]" : "-"), "\n";
}

echo "\n  dated ", $wrote, " record(s), ", $upgraded, " of them upgrading a year to a full date\n";
echo "  already had the date, left alone: ", $skipped, "\n";

if ($missing) {
    echo "\n  NOT FOUND (", count($missing), ") — expected if batch 176 has not been run yet:\n";
    echo "    ", wordwrap(implode(", ", $missing), 74, "\n    "), "\n";
}
'
}

apply_durations() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch177.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

// A whole number of months with no endpoints. This is the only one of the
// three durations the schema can publish as a figure.
foreach ($payload["durations"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

    if (! $p || $p->cases->isEmpty()) { echo "  ", $row["slug"], " — no case row\n"; continue; }

    $case = $p->cases->first();

    if (! $case->imprisoned_for_months) {
        $case->imprisoned_for_months = $row["months"];
        $case->save();
    }

    if (mb_strpos($p->description ?? "", "refused to post bond") === false) {
        $p->description = trim(($p->description ?? "").$row["note"]);
        $p->save();
    }

    $case->refresh();

    echo "  ", str_pad($row["slug"], 30), $case->imprisoned_for_months, " months -> ",
        ($case->imprisoned_for_days ?? "null"), " days, anchored on the arrest\n";
    echo "    ", wordwrap($row["why"], 72, "\n    "), "\n";
}

// Forty-five days is neither a pair of dates nor a whole number of months,
// so it goes in the prose and no counter is published.
echo "\n  recorded in prose, publishing no figure:\n";

foreach ($payload["prose_durations"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

    if (! $p) { echo "    ", $row["slug"], " — not found\n"; continue; }

    if (mb_strpos($p->description ?? "", $row["marker"]) === false) {
        $p->description = trim(($p->description ?? "").$row["note"]);
        $p->save();
    }

    $case = $p->cases->first();

    echo "    ", str_pad($row["slug"], 32),
        "imprisoned_for_days ", ($case && $case->imprisoned_for_days !== null ? $case->imprisoned_for_days : "null"),
        "  (intentionally empty)\n";
}
'
}

apply_corrections() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch177.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

foreach ($payload["corrections"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

    if (! $p || $p->cases->isEmpty()) { echo "  ", $row["slug"], " — not found, or no case row\n"; continue; }

    $case = $p->cases->first();
    $was = $case->arrest_date ? $case->formatPartialDate("arrest_date") : "(none)";

    $case->arrest_date = $row["arrest_date"];
    $case->save();

    if (mb_strpos($p->description ?? "", $row["marker"]) === false) {
        $p->description = trim(($p->description ?? "").$row["note"]);
        $p->save();
    }

    $case->refresh();

    echo "  ", $row["slug"], ": arrest ", $was, " -> ", $case->formatPartialDate("arrest_date"), "\n";
    echo "    ", wordwrap($row["why"], 72, "\n    "), "\n";
}
'
}

add_and_crossref() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch177.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

foreach ($payload["add"] as $row) {
    $existing = Prisoner::withUnderReview()->where("name", $row["name"])->first();

    if ($existing) {
        echo "  ", str_pad($row["name"], 26), "already here [", $existing->slug, "]\n";
        continue;
    }

    $spec = $row;
    unset($spec["slug"]);
    Artisan::call("prisoner:add", ["json" => json_encode($spec)]);

    $made = Prisoner::withUnderReview()->where("name", $row["name"])->first();

    echo "  ", str_pad($row["name"], 26), ($made ? "created      [".$made->slug."]" : "NOT CREATED"), "\n";
}

// The point of these two records is the doubt, so it is put on BOTH sides.
// A reader landing on either one sees the question.
echo "\n  cross-references written onto the records these two might duplicate:\n";

foreach ($payload["cross_reference"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->first();

    if (! $p) { echo "    ", $row["slug"], " — not found\n"; continue; }

    if (mb_strpos($p->description ?? "", $row["marker"]) !== false) {
        echo "    ", str_pad($row["slug"], 26), "already noted\n";
        continue;
    }

    $p->description = trim(($p->description ?? "").$row["note"]);
    $p->save();

    echo "    ", str_pad($row["slug"], 26), "noted\n";
}

foreach ($payload["birth_year_only"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->first();

    if (! $p) { echo "\n  ", $row["slug"], " — not found\n"; continue; }

    if (! $p->birthdate) {
        $p->setPartialDate("birthdate", $row["birthdate"][0]);
        $p->save();
        $p->refresh();
    }

    echo "\n  ", $row["slug"], ": born ", $p->formatPartialDate("birthdate"),
        " [", $p->datePrecisionFor("birthdate"), "]\n";
    echo "    ", wordwrap($row["why"], 72, "\n    "), "\n";
}
'
}

report() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch177.json")), true);

if (! $payload) { echo "Could not read the payload.\n"; return; }

$withAge = 0;

foreach ($payload["dates"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->first();

    if ($p && $p->birthdate) { $withAge++; }
}

echo "  ", $withAge, " of the ", count($payload["dates"]), " records now carry a date of birth.\n";

echo "\n  NOT WRITTEN, and why:\n";

foreach ($payload["held_back"] as $h) {
    echo "\n    ", $h["what"], "\n";
    echo wordwrap("      ".$h["why"], 74, "\n      "), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "apply-dates"       apply_dates
run "apply-durations"   apply_durations
run "apply-corrections" apply_corrections
run "add-and-crossref"  add_and_crossref
run "report"            report

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 177 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "The CRDL mining held no more missing people — all 367 names it lists"
echo "are now accounted for. What it still held was dates, and this is them."
