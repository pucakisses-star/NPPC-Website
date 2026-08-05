#!/usr/bin/env bash
#
# BATCH 174 -- Rose Fishstein, the one suffrage prisoner in Doris
# Stevens's Appendix 4 that this archive did not have.
#
#   THE SOURCE. Doris Stevens, Jailed for Freedom (1920), Appendix 4:
#   "Suffrage Prisoners" — eighteen scanned pages listing, in her words,
#   "only those women who actually served prison sentences although more
#   than five hundred women were arrested during the agitation." 168
#   entries. The scan carries no text layer, so every page was read.
#
#   THE ANSWER IS ALMOST NONE. 167 of the 168 were already here. That is
#   the finding, and it is a good one: this archive has the Stevens list
#   essentially complete, including the women Stevens records only under
#   a husband name. Mrs. Charles W. Barnes is here as Nellie Main
#   Barnes, Mrs. Frederick W. Kendall as Ada Louise Davenport Kendall,
#   Mrs. J. A. H. Hopkins as Alison Turnbull Hopkins, Mrs. Harvey W.
#   Wiley as Anna Kelton Wiley, Mrs. Robert Walker as Mary Walker, Mrs.
#   H. O. Havemeyer as Louisine Havemeyer, Mrs. John Rogers Jr. as
#   Elizabeth Selden Rogers.
#
#   Two entries needed a closer look and turned out to be present under
#   a different spelling: Stevens prints Anne Herkimer for the Baltimore
#   child-labor inspector this archive calls Anna Herkner, and Rebecca
#   Harrison of Joplin, Missouri for the woman here as Rebecca M.
#   Garrison. The biographies and the dates match in both cases.
#
#   THE ONE THAT WAS MISSING is Rose Fishstein, and the reason she was
#   missing is worth recording. Stevens lists her immediately after Mrs.
#   Rose Gratz Fishstein, her sister-in-law, who IS in the archive.
#   Every check for a Rose Fishstein found Rose Gratz Fishstein and
#   stopped. Two women, one surname, one forename, adjacent entries.
#
#   NO DAY IS STORED FOR HER, only the month. Stevens gives "Feb., 1919"
#   for Rose Fishstein where she gives February 9 for her sister-in-law.
#   The women arrested at the February 9 watchfire demonstration were
#   released together on February 13 after the hunger strike, so the
#   five days imposed may well not be the time served. The row carries a
#   month-precision incarceration, no release date, and publishes no
#   imprisonment figure. Filling in February 9 to 13 from what the group
#   did would be inference wearing a date field.
#
#   A DUPLICATE FOUND ON THE WAY, and reported rather than fixed.
#   Stevens lists one Mrs. Lawrence Lewis; the archive carries two
#   records, dora-lewis and dora-kelly-lewis, for the same Philadelphia
#   widow and the same Night of Terror. They disagree about her dates:
#   one gives born June 12, 1862 and died October 22, 1928, the other
#   born October 13, 1862 and died January 31, 1928. dora-kelly-lewis
#   also holds a case row whose incarceration date precedes its arrest
#   date by four months. Merging means choosing which dates to keep, and
#   neither record says where its came from, so this batch names the
#   problem and leaves it.
#
#   Idempotent: the record is created only when the slug is absent.
#
# Run from the repo root, after git pull (after batch 173):
#   bash database/data/run-batch-174.sh

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
echo "  Batch 174 — Rose Fishstein, from the Stevens appendix"
echo "==================================================================="

apply_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch174.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$src = $payload["source"];

echo "  source: ", $src["work"], "\n";
echo "          ", $src["entries"], " entries, ", $src["already_in_archive"], " already here\n\n";

$row = $payload["add"];
$slug = $row["slug"];

$p = Prisoner::withUnderReview()->where("slug", $slug)->first();

if ($p) {
    echo "  ", $slug, " already exists — not created again.\n";
} else {
    // prisoner:add is the documented path for adding a prisoner, and it takes
    // the JSON as one argument, so it is called rather than reimplemented.
    $spec = $row;
    unset($spec["slug"]);
    Artisan::call("prisoner:add", ["json" => json_encode($spec)]);
    echo Artisan::output();

    $p = Prisoner::withUnderReview()->where("slug", $slug)->first();
}

if (! $p) { echo "  the record was not created — nothing further to do.\n"; return; }

$p->load("cases");
$case = $p->cases->first();

// prisoner:add stores plain dates, which land at day precision. Stevens gives
// only the month, and a stored February 1 would read as a day she was taken in.
if ($case) {
    foreach ($payload["month_precision"] as $f) {
        if ($case->{$f}) {
            $case->setPartialDate($f, (int) $case->{$f}->format("Y"), (int) $case->{$f}->format("n"));
        }
    }

    $case->save();
    $case->refresh();
}

$p->refresh()->load("cases");

echo "\n  record: ", $p->name, "  [", $p->slug, "]\n";
echo "    affiliation ", implode(", ", $p->affiliation ?: []), "\n";
echo "    cases ", $p->cases->count(), "\n";

foreach ($p->cases as $c) {
    foreach (["arrest_date", "incarceration_date", "release_date"] as $f) {
        if ($c->{$f}) {
            echo "    ", str_pad($f, 20), $c->formatPartialDate($f),
                "  [", $c->datePrecisionFor($f), "]\n";
        }
    }

    echo "    ", str_pad("imprisoned_for_days", 20), ($c->imprisoned_for_days ?? "null"),
        "  (expected null: the sentence is recorded, the custody is not)\n";
}

// The duplicate is reported, not touched.
$dup = $payload["duplicate"];

echo "\n  DUPLICATE FOUND, NOT MERGED:\n";

foreach ($dup["slugs"] as $s) {
    $x = Prisoner::withUnderReview()->where("slug", $s)->first();

    if (! $x) { echo "    ", $s, " — not found\n"; continue; }

    echo "    ", str_pad($s, 20),
        " born ", ($x->birthdate ? $x->formatPartialDate("birthdate") : "-"),
        "   died ", ($x->death_date ? $x->formatPartialDate("death_date") : "-"), "\n";
}

echo "\n", wordwrap("    ".$dup["why"], 76, "\n    "), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "rose-fishstein" apply_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 174 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "167 of the 168 women in the Stevens appendix were already here."
echo "That is the result: the list is essentially complete."
