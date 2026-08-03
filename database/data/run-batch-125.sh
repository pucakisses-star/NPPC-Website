#!/usr/bin/env bash
#
# BATCH 125 -- Bill Sutherland, per the curator:
#
#   Canonical name stays Bill Sutherland; the archival correspondence
#   form William Sutherland Jr. is recorded as an AKA.
#
#   Life dates: born December 24, 1918; died January 2, 2010.
#
#   CUSTODY. The recorded 1943 incarceration was wrong. Sutherland
#   recalled going to prison in 1942, and a historical study has him
#   sentenced in July 1942 after refusing to report to a Civilian
#   Public Service camp — so incarceration becomes July 1942 at month
#   precision, with the old value echoed. Four years were imposed; 38
#   months were served. Release is recorded as 1945 at YEAR precision
#   only: the surviving summaries conflict (an AFSC biography gives
#   1942-45, its own internal chronology 1943-45, another AFSC
#   brochure 1943-46), the African Activist Archive interview
#   transcript gives 1945, and no prison register has turned up to fix
#   an admission or discharge day. A July 1942 sentencing plus 38
#   months lands on approximately September 1945, but that month is
#   derived, not attested, so it is not stored as though it were.
#
#   The reliable common element is the 38 months. It goes into the new
#   imprisoned_for_months column, which is authoritative over the date
#   arithmetic, so the public counter reads "Time Imprisoned: 38
#   Months" instead of manufacturing a day-level span out of two dates
#   that cannot support one. (Requires the migration shipped with this
#   change: php artisan migrate.)
#
#   Institution is already United States Penitentiary, Lewisburg,
#   Pennsylvania — left as it stands.
#
#   PHOTO. The War Resisters League portrait attaches into his empty
#   photo slot, cropped to the standard 525x700 panel. Credit in
#   database/data/CREDITS-batch-125.md.
#
#   Only empty fields are filled, except the two explicit curator
#   corrections (the 1943 incarceration date and the sentence text),
#   which are forced with the old values echoed. Nothing is deleted:
#   the biography is appended to.
#
#   Idempotent — a second run reports "already correct" throughout.
#
# Run from the repo root, after git pull (after batch 124):
#   php artisan migrate
#   bash database/data/run-batch-125.sh

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
echo "  Batch 125 — Bill Sutherland: life dates, 1942 custody, 38 months"
echo "==================================================================="

update_sutherland() {
    local DST_DIR="storage/app/public/prisoners"
    mkdir -p "$DST_DIR"

    if [ -f "database/data/photos/bill-sutherland.jpg" ]; then
        cp -f "database/data/photos/bill-sutherland.jpg" "${DST_DIR}/bill-sutherland.jpg"
        echo "portrait copied to ${DST_DIR}/bill-sutherland.jpg"
    else
        echo "!! portrait missing from database/data/photos — the record will keep its empty photo slot"
    fi

    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\Schema;

if (! Schema::hasColumn("prisoner_cases", "imprisoned_for_months")) {
    echo "ABORT: prisoner_cases.imprisoned_for_months is missing — run php artisan migrate first.\n";
    return;
}

$p = Prisoner::withUnderReview()->where("slug", "bill-sutherland")->with("cases")->first();

if (! $p) { echo "bill-sutherland NOT FOUND — nothing changed.\n"; return; }

$notes = [];

// --- AKA: additive, never overwriting an existing one.
$aka = "William Sutherland Jr.";
if (! $p->aka) {
    $p->aka = $aka;
    $notes[] = "aka=".$aka;
} elseif (stripos($p->aka, "William Sutherland") === false) {
    $p->aka = $p->aka.", ".$aka;
    $notes[] = "aka appended";
}

// --- Life dates: empty fields only.
if (! $p->birthdate) {
    $p->setPartialDate("birthdate", 1918, 12, 24);
    $notes[] = "birthdate=1918-12-24";
}
if (! $p->death_date) {
    $p->setPartialDate("death_date", 2010, 1, 2);
    $notes[] = "death_date=2010-01-02";
}

// --- Photo into the empty slot.
$rel = "prisoners/bill-sutherland.jpg";
if (! $p->photo && is_file(storage_path("app/public/".$rel))) {
    $p->photo = $rel;
    $notes[] = "photo attached";
}

// --- Biography: appended, nothing replaced.
$addition = "Sutherland was sentenced in July 1942 after refusing to report to a Civilian Public Service camp, and served 38 months of a four-year federal sentence at the United States Penitentiary at Lewisburg, Pennsylvania, before his release in 1945. The surviving summaries disagree about the years — an American Friends Service Committee biography gives 1942-45, its own internal chronology 1943-45, and another AFSC brochure 1943-46 — and no prison register has been found to fix an admission or discharge day; the 38 months is the figure they all share. Born December 24, 1918; died January 2, 2010.";

if (strpos((string) $p->description, "38 months") === false) {
    $p->description = trim((string) $p->description)."\n\n".$addition;
    $notes[] = "biography appended";
}

if ($notes) { $p->save(); }

echo "prisoner: ", implode("; ", $notes ?: ["already correct"]), "\n";

// --- The case row.
$case = $p->cases->first();

if (! $case) { echo "case: NONE — expected the Lewisburg case row; nothing changed on it.\n"; }
else {
    $cnotes = [];

    // Forced correction: the recorded 1943 start was wrong.
    $wasInc = $case->incarceration_date ? $case->incarceration_date->format("Y-m-d") : null;
    $wasIncPrec = $case->datePrecisionFor("incarceration_date");
    if ($wasInc !== "1942-07-01" || $wasIncPrec !== "month") {
        $case->setPartialDate("incarceration_date", 1942, 7);
        $cnotes[] = "incarceration=July 1942 (was ".($wasInc ? $wasInc." / ".$wasIncPrec : "empty").")";
    }

    // Year precision on purpose — the month is derived, not attested.
    if (! $case->release_date) {
        $case->setPartialDate("release_date", 1945);
        $cnotes[] = "release=1945 (year precision)";
    }

    if (! $case->sentenced_date) {
        $case->setPartialDate("sentenced_date", 1942, 7);
        $cnotes[] = "sentenced=July 1942";
    }

    if ((int) $case->imprisoned_for_months !== 38) {
        $case->imprisoned_for_months = 38;
        $cnotes[] = "imprisoned_for_months=38";
    }

    // Forced correction: the stored sentence text said only that he was
    // held and released after the war, which is what obscured the four
    // years imposed against the 38 served.
    $sentence = "Four years in federal prison; 38 months served. Held at the United States Penitentiary, Lewisburg, Pennsylvania. Release recorded as 1945 at year precision — a July 1942 sentencing plus 38 months places it at approximately September 1945, but no prison register has been found to fix the day, and the surviving summaries conflict (1942-45, 1943-45, 1943-46).";
    if ($case->sentence !== $sentence) {
        $cnotes[] = "sentence text replaced (was: ".\Illuminate\Support\Str::limit((string) $case->sentence, 60).")";
        $case->sentence = $sentence;
    }

    if ($cnotes) { $case->save(); }

    $case->refresh();
    echo "case: ", implode("; ", $cnotes ?: ["already correct"]), "\n";
    echo "  incarcerated ", $case->formatPartialDate("incarceration_date"),
        "  released ", $case->formatPartialDate("release_date"),
        "  months=", ($case->imprisoned_for_months ?? "null"),
        "  days=", ($case->imprisoned_for_days ?? "null"), "\n";
}

$p->refresh()->load("cases");
echo "\npublic counter will read: Time Imprisoned: ",
    \App\Support\ImprisonmentDuration::phrase(
        $p->cases->map(fn ($c) => $c->incarceration_date ?: $c->arrest_date)->filter()->sort()->first(),
        (int) $p->cases->sum("imprisoned_for_days"),
        \App\Support\ImprisonmentDuration::documentedMonths($p->cases),
    ), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
}

run "sutherland-update" update_sutherland

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 125 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
