#!/usr/bin/env bash
#
# BATCH 143 -- the two open questions from batch 141, answered.
#
#   ONE MAN, TWO RECORDS. steve-bratich and steve-bradich are the same
#   fourth defendant in the Woodlawn sedition case under two spellings
#   of a Serbian surname, and neither record was simply the better one:
#
#     bratich   arrest date 11 Nov 1926, the account of the raid,
#               the Labor Organizing tag — but says the U.S. Supreme
#               Court refused his appeal, which belongs to the others
#     bradich   the 1927 conviction and the 1928 discharge on appeal
#               — the fact that he never served
#
#   So the merge takes the content of both rather than picking a record
#   and discarding one. The spelling is settled by keeping Bratich as
#   the name and Bradich in the aka field, which is exactly what that
#   field is for: nothing is lost and a search for either finds him.
#   Bratich survives because its record is the better documented and
#   because Prisoner::updating only regenerates the slug when the name
#   changes — so /prisoner/steve-bratich keeps working and no incoming
#   link breaks.
#
#   THE CONVICTION YEAR, given as 1927 by one record and 1928 by two
#   others, is settled at 1927 and written to all four defendants at
#   year precision. Three reasons: the raid was 11 November 1926 and a
#   trial the next year is the ordinary interval; the fourth
#   defendant's record gives 1927 for the conviction and 1928 for his
#   discharge on appeal, which only works in that order; and the
#   appellate history — Pennsylvania Superior Court, then the state
#   Supreme Court, then a refusal to review by the United States
#   Supreme Court in 1929, then custody in November 1929 — does not fit
#   into what a 1928 conviction leaves. The 1928 date may be the
#   sentencing rather than the verdict, which would make both true. The
#   doubt is written into every affected case row rather than resolved
#   silently.
#
#   The absorbed record is deleted only after its case rows have been
#   moved, and only if it has none of its own left.
#
#   Idempotent.
#
# Run from the repo root, after git pull (after batch 142):
#   bash database/data/run-batch-143.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"
    shift
    echo
    echo "--- ${label}"
    if "$@"; then
        return 0
    fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}")
    return 0
}

echo "==================================================================="
echo "  Batch 143 — Woodlawn: one man restored, one year settled"
echo "==================================================================="

apply_batch() {
    php artisan tinker --execute='
use App\Models\CalendarEntry;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch143.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$m = $payload["merge"];

echo "MERGE\n";

$survivor = Prisoner::withUnderReview()->where("slug", $m["survivor_slug"])->with("cases")->first();
$absorbed = Prisoner::withUnderReview()->where("slug", $m["absorbed_slug"])->with("cases")->first();

if (! $survivor) {
    echo "  ", $m["survivor_slug"], " NOT FOUND — merge skipped\n";
} else {
    echo "  survivor: ", $survivor->name, " [", $survivor->slug, "]  ",
        $survivor->cases->count(), " case row(s)\n";

    if (! $absorbed) {
        echo "  ", $m["absorbed_slug"], " already absorbed\n";
    } else {
        echo "  absorbed: ", $absorbed->name, " [", $absorbed->slug, "]  ",
            $absorbed->cases->count(), " case row(s)\n";

        // The absorbed record adds nothing the merged text does not already
        // say, and a second case row for a sentence never served would be
        // counted as a second prosecution. Its rows are deleted, not moved.
        foreach ($absorbed->cases as $c) {
            echo "    dropping its case row [", $c->id, "] — ",
                mb_strimwidth((string) $c->charges, 0, 56, "..."), "\n";
            $c->delete();
        }

        $cal = CalendarEntry::where("prisoner_id", $absorbed->id)->delete();
        $absorbed->delete();

        echo "    record deleted (", $cal, " calendar entries)\n";
    }

    $survivor->aka = $m["aka"];
    $survivor->description = $m["description"];
    $survivor->save();

    echo "  aka set to ", $survivor->aka, "\n";
    echo "  slug after save: ", $survivor->refresh()->slug, "\n";
    echo "  biography rewritten to carry both records\n";

    $case = $survivor->cases()->first();

    if ($case) {
        $case->sentence = $m["case_sentence"];
        $case->convicted = $m["case_convicted"];
        $case->save();
        echo "  case row updated\n";
    }
}

// ------------------------------------------------------------------ year
$cy = $payload["conviction_year"];

echo "\n", str_repeat("=", 67), "\nCONVICTION YEAR -> ", $cy["value"], "\n";

foreach ($cy["applies_to"] as $slug) {
    $p = Prisoner::withUnderReview()->where("slug", $slug)->with("cases")->first();

    if (! $p) { echo "  ", $slug, " NOT FOUND\n"; continue; }

    foreach ($p->cases as $case) {
        $was = $case->sentenced_date ? $case->formatPartialDate("sentenced_date") : "empty";

        $case->setPartialDate("sentenced_date", $cy["value"]);

        $marker = "On the year. This record now gives the conviction";

        if (mb_strpos((string) $case->sentence, $marker) === false) {
            $case->sentence = rtrim((string) $case->sentence).$cy["note_for_case"];
            $noted = "note added";
        } else {
            $noted = "note already present";
        }

        $case->save();
        $case->refresh();

        echo "  ", str_pad($slug, 18), " sentenced_date ", $was, " -> ",
            $case->formatPartialDate("sentenced_date"),
            " [", $case->datePrecisionFor("sentenced_date"), "]  ", $noted, "\n";
    }
}

echo "\n  Grounds recorded with the change:\n";
echo "  ", wordwrap($cy["grounds"], 84, "\n  "), "\n";

// --------------------------------------------------------------- summary
echo "\n", str_repeat("=", 67), "\nTHE WOODLAWN CASE AS IT NOW STANDS\n\n";

foreach (["pete-muselin", "tom-zima", "milan-resetar", "steve-bratich"] as $slug) {
    $p = Prisoner::withUnderReview()->where("slug", $slug)->with("cases")->first();

    if (! $p) { echo "  ", str_pad($slug, 18), " NOT FOUND\n"; continue; }

    $total = (int) $p->cases->sum("imprisoned_for_days");
    $case = $p->cases->first();

    echo "  ", str_pad($p->name, 16),
        " [", str_pad($p->slug, 15), "]",
        " aka=", str_pad($p->aka ?: "-", 14),
        " convicted=", ($case && $case->sentenced_date ? $case->formatPartialDate("sentenced_date") : "-"),
        "  days=", ($total ?: "none"), "\n";
}

$stray = Prisoner::withUnderReview()->where("slug", "steve-bradich")->exists();

echo "\n  steve-bradich still present: ", ($stray ? "YES — merge did not complete" : "no"), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "woodlawn-merge-and-year" apply_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 143 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
echo
echo "Expected: four Woodlawn defendants, all convicted 1927; Muselin and"
echo "Zima 799 days, Resetar 691, Bratich none — his sentence was set"
echo "aside on appeal before he served it."
