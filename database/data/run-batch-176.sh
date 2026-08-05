#!/usr/bin/env bash
#
# BATCH 176 -- thirty Jackson, Mississippi 1961 arrestees the CRDL mining
# turned up that this archive did not have.
#
#   THE SOURCE. The Civil Rights Digital Library's run of Mississippi State
#   Sovereignty Commission arrest photographs from Jackson in 1961, with
#   partner item records at the Mississippi Department of Archives and
#   History. 1,355 ledger entries were supplied; 377 distinct personal names
#   parse out of them, because the mining reports repeat heavily — Claire
#   O'Connor is entered three separate times.
#
#   MOST OF THEM WERE ALREADY HERE. 338 of the 377 matched a record. That
#   is the headline and it is a good one: the archive's Freedom Ride
#   coverage was already close to complete.
#
#   THE FIRST MATCHER WAS WRONG IN BOTH DIRECTIONS, and it is worth saying
#   how, because the same mistake is available every time a list like this
#   arrives.
#
#   It keyed on the LAST token of a name, which is not the surname when a
#   married name has been appended or the forms are reordered. Six people
#   it called missing were sitting in the archive:
#
#     Lewis Richard Zuchman  -> richard-lewis-zuchman     (forms reordered)
#     Teri Susan Perlman     -> terry-perlman-hickerson   (married name)
#     Frances Lee Wilson     -> frances-lee-wilson-canty  (married name)
#     Allen Levine           -> allan-levine              (a/e)
#     Claire O'Connor        -> claire-oconnor            (apostrophe)
#     Jesse Harris           -> jessie-l-harris           (photo title)
#
#   And it paired two people to records that are plainly somebody else —
#   Jesse James Harris to a 1970s Alabama prisoner, Joseph Jackson Jr. of
#   the Tougaloo Nine to a 1930s Washington murder defendant. Every claimed
#   match was re-checked against the arrest cohort, and every claimed gap
#   was searched by hand for married and variant forms before anything was
#   created here.
#
#   SIX OF THE TOUGALOO NINE WERE MISSING. The archive held Alfred Lee
#   Cook, Evelyn Pierce and Meredith Coleman Anding Jr. and not the other
#   six. All nine walked into the whites-only main branch of the Jackson
#   public library on March 27, 1961, asked for service and refused to
#   leave.
#
#   NO IMPRISONMENT FIGURE IS PUBLISHED FOR THE RIDERS, and that is
#   deliberate. The catalog says the same thing on nearly every entry:
#   it confirms the arrest and the Jackson police processing, and does not
#   give sentencing, Parchman admission, release or appellate dates. So
#   these rows carry an arrest date and nothing else. An arrest with no
#   recorded end is not a sentence, and this archive does not count it as
#   one.
#
#   TWO EXCEPTIONS, both sourced:
#
#     The Tougaloo Nine were held about thirty-two hours and released on
#     March 28 for court, convicted of disturbing the peace, fined $100
#     each and given thirty-day suspended sentences. That is a documented
#     span, so those rows carry it — and the three records already here get
#     it too, which they were missing.
#
#     Margaret Winonah Beamer served close to six months at Parchman rather
#     than taking the appeal bond that freed most riders within weeks. The
#     six months goes in as imprisoned_for_months with no admission or
#     discharge date, because the duration is sourced and the dates are
#     not. That is the batch 168 convention.
#
#   TWO ARE NOT CREATED, and are reported instead:
#
#     LEWELL A. CECIL. The archive already holds Lewell A. Woods Jr. from
#     the same June 10, 1961 group. Lewell A. is not a name two men in one
#     arrest group are likely to share, and the two forms come from
#     separate passes over the catalog rather than from one list naming
#     both. Creating this would risk publishing one man twice under a
#     garbled surname.
#
#     JESSE JAMES HARRIS. Catalogued as a June 2 arrest and asserted to be
#     a different man from the Jesse Harris arrested at Livingston Park on
#     July 6, who is already here as Jessie L. Harris. The source rates
#     that distinction only moderate and itself flags a possible identity
#     overlap. One SNCC organiser of that name is documented for 1961.
#
#   ONE NAME IS KEPT AS THE CATALOG PRINTS IT. Gwendolyn Green carries a
#   [sic] in the catalog — the archivists doubt the form and offer no
#   other. It is stored unchanged, with the doubt written into the record,
#   rather than quietly normalised into a spelling nobody has attested.
#
#   ABRAHAM BASSFORD GETS NO ARREST DATE. The catalog holds his photograph
#   and confirms the arrest but does not expose the date; the rosters place
#   him in the June 6 New Orleans-to-Jackson group. A group placement is
#   not a date.
#
#   Idempotent: each record is created only when its name is absent, the
#   partial dates and the months are fixed values, and the backfill
#   paragraph is appended only when it is not already there.
#
# Run from the repo root, after git pull (after batch 175):
#   bash database/data/run-batch-176.sh

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
echo "  Batch 176 — 30 Jackson 1961 arrestees from the CRDL catalog"
echo "==================================================================="

add_records() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch176.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$src = $payload["source"];

echo "  source: ", $src["catalog"], "\n";
echo "          ", $src["scope"], "\n";
echo "          ", $src["ledger_entries_read"], " ledger entries, ",
     $src["distinct_names_parsed"], " distinct names\n\n";

$created = 0; $already = 0; $failed = [];

foreach ($payload["rows"] as $row) {
    // The name is the key, not the slug: prisoner:add generates the slug
    // itself, and a record created under a different slug would be created
    // twice by a second run.
    $existing = Prisoner::withUnderReview()->where("name", $row["name"])->first();

    if ($existing) {
        echo "  ", str_pad($row["name"], 32), "already here [", $existing->slug, "]\n";
        $already++;
        continue;
    }

    Artisan::call("prisoner:add", ["json" => json_encode($row)]);
    $out = Artisan::output();

    $made = Prisoner::withUnderReview()->where("name", $row["name"])->first();

    if ($made) {
        echo "  ", str_pad($row["name"], 32), "created      [", $made->slug, "]\n";
        $created++;
    } else {
        echo "  ", str_pad($row["name"], 32), "NOT CREATED\n", trim($out), "\n";
        $failed[] = $row["name"];
    }
}

echo "\n  created ", $created, ", already present ", $already;
if ($failed) { echo ", FAILED ", count($failed), ": ", implode(", ", $failed); }
echo "\n";
'
}

set_precision() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch176.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

// prisoner:add stores plain dates, which land at day precision. The catalog
// gives a year and nothing more, and a stored January 1 would read as a
// birthday. So the years are written here instead, at year precision.
echo "  ", str_pad("record", 30), str_pad("born", 16), "died\n";
echo "  ", str_repeat("-", 62), "\n";

foreach ($payload["partial_dates"] as $slug => $fields) {
    $p = Prisoner::withUnderReview()->where("slug", $slug)->first();

    if (! $p) { echo "  ", $slug, " — not found\n"; continue; }

    foreach ($fields as $f => $parts) {
        $p->setPartialDate($f, $parts[0], $parts[1] ?? null, $parts[2] ?? null);
    }

    $p->save();
    $p->refresh();

    echo "  ", str_pad($slug, 30),
        str_pad(($p->birthdate ? $p->formatPartialDate("birthdate")." [".$p->datePrecisionFor("birthdate")."]" : "-"), 16),
        ($p->death_date ? $p->formatPartialDate("death_date")." [".$p->datePrecisionFor("death_date")."]" : "-"), "\n";
}

// The one sourced duration in the batch. Months outrank the date arithmetic
// in computeImprisonedForDays(), so this publishes six months from the
// arrest without claiming an admission or a discharge date.
foreach ($payload["months"] as $slug => $months) {
    $p = Prisoner::withUnderReview()->where("slug", $slug)->with("cases")->first();

    if (! $p || $p->cases->isEmpty()) { echo "  ", $slug, " — no case row for the months\n"; continue; }

    $case = $p->cases->first();
    $case->imprisoned_for_months = $months;
    $case->save();
    $case->refresh();

    echo "\n  ", $slug, ": imprisoned_for_months ", $case->imprisoned_for_months,
        " -> imprisoned_for_days ", ($case->imprisoned_for_days ?? "null"),
        "  (no release date is claimed)\n";
}
'
}

backfill_tougaloo() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch176.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$b = $payload["tougaloo_backfill"];

echo "  the three Tougaloo Nine records already in the archive were carrying the\n";
echo "  arrest and nothing else. The catalog gives the disposition, so it goes in.\n\n";

foreach ($b["slugs"] as $slug) {
    $p = Prisoner::withUnderReview()->where("slug", $slug)->with("cases")->first();

    if (! $p) { echo "  ", $slug, " — not found\n"; continue; }

    $case = $p->cases->first();

    if (! $case) { echo "  ", $slug, " — no case row\n"; continue; }

    if (mb_strpos($p->description ?? "", $b["marker"]) === false) {
        $p->description = trim(($p->description ?? "").$b["note"]);
        $p->save();
    }

    if (! $case->release_date) { $case->release_date = $b["release_date"]; }
    if (! $case->incarceration_date) { $case->incarceration_date = $b["incarceration_date"]; }

    $case->convicted = $b["convicted"];
    $case->save();
    $case->refresh();

    echo "  ", str_pad($slug, 30),
        "in ", ($case->incarceration_date ? $case->formatPartialDate("incarceration_date") : "-"),
        "   out ", ($case->release_date ? $case->formatPartialDate("release_date") : "-"),
        "   ", ($case->imprisoned_for_days ?? "null"), " day(s)\n";
}
'
}

report() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch176.json")), true);

if (! $payload) { echo "Could not read the payload.\n"; return; }

$noEnd = 0;

foreach ($payload["rows"] as $row) {
    $p = Prisoner::withUnderReview()->where("name", $row["name"])->with("cases")->first();

    if ($p && $p->cases->first() && ! $p->cases->first()->release_date) { $noEnd++; }
}

echo "  ", $noEnd, " of the ", count($payload["rows"]), " new records publish no imprisonment\n";
echo "  figure at all. That is the catalog being honest: it establishes the arrest\n";
echo "  and the police photograph, and not the sentence, the Parchman admission or\n";
echo "  the release.\n";

echo "\n  NOT CREATED, and why:\n";

foreach ($payload["held_back"] as $h) {
    echo "\n    ", $h["name"], "\n";
    echo wordwrap("      ".$h["why"], 74, "\n      "), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "add-records"       add_records
run "set-precision"     set_precision
run "backfill-tougaloo" backfill_tougaloo
run "report"            report

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 176 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "338 of the 377 names the CRDL mining produced were already here."
echo "30 were genuinely missing and are now in; 2 are reported rather than"
echo "created, because creating them risks publishing one person twice."
