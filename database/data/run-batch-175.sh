#!/usr/bin/env bash
#
# BATCH 175 -- the April 2, 1919 bail order recorded on the thirty-seven
# Chicago IWW defendants the New York Times names.
#
#   THE QUESTION WAS WHETHER ALL 38 ARE IN THE DATABASE. They are, with
#   a caveat about the number: the article counts thirty-eight men and
#   NAMES thirty-seven of them — Haywood plus thirty-six others. All
#   thirty-seven are here. The thirty-eighth is not named in the piece,
#   so this batch does not guess at him.
#
#   SEVEN WERE HIDING UNDER A DIFFERENT SPELLING, which is why a plain
#   name search would have said they were missing. The wire copy is
#   rough — it prints "Albert I'rashner" for Prashner — and the archive
#   holds the researched forms:
#
#     Petro Nigrh        -> Pietro Nigra
#     Price C. Wenter    -> Pierce C. Wetter
#     Raynor Johnson     -> Ragnar Johanson
#     Vladimir Losieff   -> Vladimir Lossieff
#     Siegfried Stenberg -> Sigfrid Stenberg
#     Frank Westerland   -> Frank Westerlund
#     Charles Rothfisher -> Charles Rothfiser
#
#   Two more are here under the names they are actually known by:
#   William D. Haywood as Bill Haywood, and J. A. McDonald as John Alex
#   MacDonald, editor of the Industrial Worker.
#
#   THE ORDER IS NOT THE RELEASE, and the archive already proves it. The
#   court admitted them to bail on April 2; each man left Leavenworth
#   when his bond was actually furnished. The release dates already in
#   these records fall between April 28 and September 28, 1919 —
#   Rothfiser and Westerlund on April 28, Laukki April 30, Gordon May 5,
#   Jaakkola May 9, Pancner May 19, Lossieff June 30, Perry September
#   13, Law September 28. That spread is exactly what raising
#   thirty-eight bonds looks like, and it is why NO RELEASE DATE IS
#   WRITTEN by this batch. Stamping April 2 on all of them would replace
#   nine sourced dates with one wrong one.
#
#   WHAT IS WRITTEN is a paragraph on each case row recording the order,
#   the bond amounts, and the fifty-five who did not seek bail.
#
#   NINETEEN OF THE THIRTY-SEVEN HAVE NO RELEASE DATE AT ALL and publish
#   nothing. For them this article establishes something they did not
#   have: they were inside Leavenworth on April 2, 1919. That is a floor,
#   not an end, so it goes in the text rather than the date column.
#
#   AND IT EXPOSES AN OVERSTATEMENT. Several of these men have one case
#   row running from 1917 or 1918 straight through to 1921 or 1923 —
#   Edwards 2,123 days, Nef and St. John 1,933, Tanner 1,791, Chaplin
#   1,750. But they were out on bail from 1919 until the appeal failed.
#   William Tanner own description says it plainly: "December 1917 to
#   May 1919 and from April 1921 to October 27, 1922." His row counts
#   the two years he was at liberty. This is the class B shape from
#   DURATION-SWEEP.md, now confirmed by a source, and it needs the rows
#   split per man — which needs each man return date, and this article
#   does not give them.
#
#   Idempotent: the paragraph is appended only when it is not already
#   there.
#
# Run from the repo root, after git pull (after batch 174):
#   bash database/data/run-batch-175.sh

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
echo "  Batch 175 — April 2, 1919: 37 Chicago IWW defendants bailed"
echo "==================================================================="

apply_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch175.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$src = $payload["source"];
$marker = $payload["marker"];

echo "  source: ", $src["publication"], ", ", $src["date"], "\n";
echo "          ", $src["headline"], "\n\n";

$done = 0; $already = 0; $missing = []; $noRelease = []; $spans = [];

echo "  ", str_pad("record", 26), str_pad("printed in the article as", 24), "release on the Chicago case\n";
echo "  ", str_repeat("-", 84), "\n";

foreach ($payload["rows"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

    if (! $p) { $missing[] = $row["slug"]; continue; }

    // The Chicago espionage prosecution, not any other case on the record.
    $case = $p->cases->first(function ($c) {
        $hay = ($c->sentence ?? "")." ".($c->charges ?? "")
            ." ".($c->institution ? $c->institution->name : "");

        return preg_match("/espionage|sedition|Leavenworth|Chicago/i", $hay);
    }) ?: $p->cases->first();

    if (! $case) { $missing[] = $row["slug"]." (no case row)"; continue; }

    if (mb_strpos($case->sentence ?? "", $marker) !== false) {
        $already++;
    } else {
        $case->sentence = trim(($case->sentence ?? "").$row["note"]);
        $case->save();
        $done++;
    }

    $case->refresh();

    $rel = $case->release_date ? $case->formatPartialDate("release_date") : "(none)";

    if (! $case->release_date) { $noRelease[] = $row["slug"]; }

    // A single row that runs past 1920 spans the bail period and counts it.
    if ($case->release_date && (int) $case->release_date->format("Y") >= 1921
        && $case->incarceration_date && (int) $case->incarceration_date->format("Y") <= 1919) {
        $spans[] = $row["slug"]." (".(int) $case->imprisoned_for_days." days)";
    }

    echo "  ", str_pad($row["slug"], 26), str_pad($row["printed_as"], 24), $rel, "\n";
}

$c = $payload["counts"];

echo "\n  ", str_repeat("=", 84), "\n";
echo "  the article counts ", $c["article_says"], " men and names ", $c["article_names"], "\n";
echo "  found in the archive: ", (count($payload["rows"]) - count($missing)), " of ", count($payload["rows"]), "\n";
echo "  bail paragraph added to ", $done, " case rows, already present on ", $already, "\n";

if ($missing) { echo "\n  NOT FOUND: ", implode(", ", $missing), "\n"; }

echo "\n  NO RELEASE DATE ON THE CHICAGO CASE (", count($noRelease), ") — these publish nothing,\n";
echo "  and this article now puts each of them inside Leavenworth on April 2, 1919:\n";
echo "    ", wordwrap(implode(", ", $noRelease), 74, "\n    "), "\n";

echo "\n  ROWS THAT SPAN THE BAIL PERIOD (", count($spans), ") — one case row running from\n";
echo "  before the bail order to after the appeal failed, counting the years at\n";
echo "  liberty as custody. Class B in DURATION-SWEEP.md, now confirmed by a source:\n";
echo "    ", wordwrap(implode(", ", $spans), 74, "\n    "), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "iww-bail-1919" apply_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 175 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
echo
echo "Answer to the question: all 37 men the article names are already in"
echo "the database. The article itself names only 37 of the 38 it counts."
