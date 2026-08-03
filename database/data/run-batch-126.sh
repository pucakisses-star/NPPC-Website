#!/usr/bin/env bash
#
# BATCH 126 -- JAFRC contempt custody dates, per the curator.
#
#   The eleven Joint Anti-Fascist Refugee Committee defendants all
#   entered federal custody on June 7, 1950, which every record
#   already had right. What was wrong was the other end: every stored
#   release date was the sentence length added to the surrender date,
#   not a date any source reported. Barsky was recorded out on
#   December 7 (six months to the day) and the other ten on September
#   7 (three months to the day). None of those are releases.
#
#   THREE ARE NOW ATTESTED:
#     Barsky   released November 7, 1950 — about five months of six,
#              at the Federal Reformatory, Petersburg, Virginia.
#     Fast     released August 29, 1950 from Mill Point Federal
#              Prison Camp, West Virginia.
#     Bradley  released August 29, 1950 from Mill Point with Fast;
#              the September 1, 1950 Daily Worker reported both men
#              free after serving their terms.
#
#   THE OTHER EIGHT drop to MONTH precision — September 1950. The
#   same September 1 report had them due out the following week,
#   which places the release at approximately September 3-9, 1950,
#   and no individual release day has been found. Recording
#   "September 1950" says exactly that; the previous September 7 said
#   more than anyone knows. Their documented three months goes into
#   imprisoned_for_months (batch 125), so the counter still reads "3
#   Months" instead of the "2 Months 25 Days" a month-precision date
#   would otherwise produce.
#
#   BRYAN AND FLEISCHMAN were not part of the June 7 surrender. They
#   entered custody on November 13, 1950 after their later appeal
#   failed — already correct — and their February 13, 1951 release
#   was the same three-months-to-the-day arithmetic. No release
#   notice has been found for either woman, so it drops to February
#   1951 at month precision on the same basis.
#
#   EUGENE DENNIS IS NOT TOUCHED. See the "flagged" entry in
#   database/data/fixes/batch126.json for why.
#
#   Case rows are found by their incarceration date, so a record
#   carrying several cases (Bryan, Lustig) cannot be edited on the
#   wrong one. Sentence text is APPENDED, never replaced. Every
#   changed value is echoed with what it was. Idempotent.
#
# Requires the batch 125 migration (prisoner_cases.imprisoned_for_months).
#
# Run from the repo root, after git pull (after batch 125):
#   php artisan migrate
#   bash database/data/run-batch-126.sh

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
echo "  Batch 126 — JAFRC release dates: 3 attested, 10 to month precision"
echo "==================================================================="

apply_jafrc() {
    php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use App\Support\ImprisonmentDuration;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

if (! Schema::hasColumn("prisoner_cases", "imprisoned_for_months")) {
    echo "ABORT: prisoner_cases.imprisoned_for_months is missing — run php artisan migrate first.\n";
    return;
}

$payload = json_decode(File::get(base_path("database/data/fixes/batch126.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$rows = array_merge($payload["june7"] ?? [], $payload["november13"] ?? []);

if (! $rows) { echo "Payload holds no rows — nothing changed.\n"; return; }

$missing = [];

foreach ($rows as $row) {
    echo "\n", $row["slug"], "  (", $row["name"], ")\n";

    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

    if (! $p) { echo "  NOT FOUND — skipped\n"; $missing[] = $row["slug"]; continue; }

    // Located by the custody start, never by position: Bryan and Lustig
    // each carry more than one case row.
    $case = $p->cases->first(function ($c) use ($row) {
        return $c->incarceration_date
            && $c->incarceration_date->format("Y-m-d") === $row["incarcerated"];
    });

    if (! $case) {
        echo "  no case row starting ", $row["incarcerated"], " — skipped (custody start differs from the curator record?)\n";
        $missing[] = $row["slug"];
        continue;
    }

    $notes = [];

    // --- Release date. Day precision where a source names the day,
    //     month precision where only the window is known.
    $rel = $row["release"];
    $target = sprintf("%04d-%02d-%02d", $rel["year"], $rel["month"] ?? 1, $rel["day"] ?? 1);
    $wantPrec = isset($rel["day"]) ? "day" : (isset($rel["month"]) ? "month" : "year");
    $wasRel = $case->release_date ? $case->release_date->format("Y-m-d") : null;
    $wasPrec = $case->datePrecisionFor("release_date");

    if ($wasRel !== $target || $wasPrec !== $wantPrec) {
        $case->setPartialDate("release_date", $rel["year"], $rel["month"] ?? null, $rel["day"] ?? null);
        $notes[] = "release=".$case->formatPartialDate("release_date")." [".$wantPrec."]"
            ." (was ".($wasRel ? $wasRel." [".$wasPrec."]" : "empty").")";
    }

    // --- Documented months, where the release is only known to the month.
    $months = $row["months"] ?? null;
    if ($months !== null && (int) $case->imprisoned_for_months !== (int) $months) {
        $case->imprisoned_for_months = (int) $months;
        $notes[] = "imprisoned_for_months=".$months;
    }

    // --- Institution, empty slots only.
    if (! empty($row["institution"]) && ! $case->institution_id) {
        $inst = Institution::firstOrCreate(
            ["name" => $row["institution"]["name"]],
            ["city" => $row["institution"]["city"] ?? null, "state" => $row["institution"]["state"] ?? null],
        );
        $case->institution_id = $inst->id;
        $notes[] = "institution=".$inst->name;
    }

    // --- Sentence text: appended, never replaced.
    $append = $row["sentence_append"] ?? null;
    if ($append && strpos((string) $case->sentence, $append) === false) {
        $case->sentence = trim(trim((string) $case->sentence)."\n\n".$append);
        $notes[] = "sentence provenance appended";
    }

    if ($notes) { $case->save(); }

    $case->refresh();

    echo "  ", implode("\n  ", $notes ?: ["already correct"]), "\n";
    echo "  -> ", $case->formatPartialDate("incarceration_date"), " to ", $case->formatPartialDate("release_date"),
        "  |  counter: ", ImprisonmentDuration::phrase(
            $case->incarceration_date,
            (int) $p->refresh()->cases->sum("imprisoned_for_days"),
            ImprisonmentDuration::documentedMonths($p->cases),
        ), "\n";
}

foreach ($payload["flagged"] ?? [] as $f) {
    echo "\nFLAGGED, NOT CHANGED — ", $f["slug"], "\n  ", wordwrap($f["reason"], 90, "\n  "), "\n";
}

if ($missing) {
    echo "\n", count($missing), " record(s) could not be updated: ", implode(", ", $missing), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "jafrc-release-dates" apply_jafrc

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 126 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
