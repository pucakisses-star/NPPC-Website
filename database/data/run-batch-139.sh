#!/usr/bin/env bash
#
# BATCH 139 -- six people from the May 1923 birthdays box, per the
# curator. All six are already in the database. What they need is dates.
#
#   FOUR OF THEM PUBLISH A TOTAL IMPRISONMENT OF ZERO DAYS.
#
#     richard-brazier   ~6 years at Leavenworth   counter: none
#     myron-sprague     ~6 years at Leavenworth   counter: none
#     george-oconnell   ~5 years at Leavenworth   counter: none
#     david-caplan      ~8 years in custody       counter: none
#
#   In each case the row carries a release date, or a sentence, or an
#   inmate number, but no incarceration date — and the counter measures
#   from the incarceration date, so it had nothing to measure. Sprague
#   and O'Connell each hold a December 22, 1923 release with no start
#   at all. Filling in the four start dates is most of this batch:
#
#     brazier   Sep 1917 - Aug 3, 1923    2,162 days
#     sprague   Apr 1918 - Dec 22, 1923   2,091 days
#     oconnell  Aug 1918 - Dec 22, 1923   1,969 days
#     caplan    Feb 18, 1915 - Jul 10, 1923, in two placements, 3,064
#
#   CAPLAN GETS A SECOND CASE ROW. He was held in the Los Angeles
#   County Jail for twenty-three months before being moved to San
#   Quentin in January 1917. That is one continuous custody in two
#   places, and a single row would have to choose one of them; two rows
#   total the eight years and five months the sources report. His
#   arrest date is corrected from February 1 to February 18, 1915, and
#   his verdict from second-degree murder to second-degree
#   manslaughter. His biography now states that the bombing killed
#   twenty-one people, at the curator's instruction: he is kept as a
#   political prisoner, but the entry must not read as though he was
#   imprisoned for speech.
#
#   RUTHENBERG'S DRAFT CUSTODY IS CHANGED, AND THIS IS THE ONE TO
#   CHECK. The row ran from July 14, 1917 to March 31, 1918 and gave
#   260 days. Its start was the day of sentence — the commonest way a
#   custody date gets invented rather than recorded — and it sits
#   alongside an appeal that went to the Supreme Court after
#   sentencing. The curator's chronology puts the custody in 1918,
#   about January to December 8, which is 341 days. That is what is
#   stored. His empty second case row, a stub holding only a generic
#   import charge, becomes the New York criminal anarchism case, and a
#   third row is added for the Michigan conviction he never served.
#
#   FOSTER gains the two episodes the curator names: forty-seven days
#   in the Spokane City Jail in the 1909-1910 free-speech fight, and
#   the August 22, 1922 Bridgman arrest whose 1923 trial hung six to
#   six. Neither produces a counter — the Spokane days are documented
#   without endpoints, and the schema records a documented duration in
#   months, not days. The stray 1930 arrest date on his Smith Act row
#   is annotated, not deleted.
#
#   Idempotent. Rows created by this script are matched on their
#   charges before being created again, and every update rewrites the
#   same values.
#
# Run from the repo root, after git pull (after batch 138):
#   bash database/data/run-batch-139.sh

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
echo "  Batch 139 — May 1923 birthdays: six records that need dates"
echo "==================================================================="

apply_batch() {
    php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch139.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$d = fn ($v) => $v ? $v->format("Y-m-d") : "----------";

$institutionFor = function (array $spec) {
    $inst = Institution::firstOrCreate(
        ["name" => $spec["name"]],
        ["city" => $spec["city"] ?? null, "state" => $spec["state"] ?? null],
    );

    return $inst;
};

$applyDates = function (PrisonerCase $case, array $dates) {
    foreach ($dates as $field => $parts) {
        if ($parts === null) { $case->setPartialDate($field, null); continue; }

        $case->setPartialDate($field, $parts[0], $parts[1] ?? null, $parts[2] ?? null);
    }
};

foreach ($payload["people"] as $person) {
    echo "\n", str_repeat("=", 67), "\n", $person["label"], "\n";

    $p = Prisoner::withUnderReview()->where("slug", $person["slug"])->with("cases")->first();

    if (! $p) { echo "  ", $person["slug"], " NOT FOUND — skipped\n"; continue; }

    $before = (int) $p->cases->sum("imprisoned_for_days");

    echo "  before: ", $p->cases->count(), " case row(s), total ", $before, " days\n";

    foreach ($p->cases as $c) {
        echo "    arrest=", $d($c->arrest_date), " in=", $d($c->incarceration_date),
            " out=", $d($c->release_date), " days=", str_pad((string) ($c->imprisoned_for_days ?? "null"), 6),
            " ", mb_strimwidth((string) $c->charges, 0, 52, "..."), "\n";
    }

    // ------------------------------------------------------------ prisoner
    $pf = $person["prisoner"] ?? [];

    foreach (["inmate_number", "state", "era", "ideologies", "affiliation", "description"] as $f) {
        if (array_key_exists($f, $pf)) { $p->{$f} = $pf[$f]; }
    }

    if (! empty($pf["description_append"])) {
        $marker = mb_substr($pf["description_append"], 0, 40);

        if (mb_strpos((string) $p->description, $marker) === false) {
            $p->description = trim((string) $p->description)."\n\n".$pf["description_append"];
            echo "  biography: paragraph appended\n";
        } else {
            echo "  biography: paragraph already present\n";
        }
    }

    $p->save();

    // ---------------------------------------------------------------- cases
    foreach ($person["cases"] as $spec) {
        $mode = $spec["match"];
        $role = $spec["role"] ?? $mode;
        $case = null;

        if ($mode === "only") {
            $case = $p->cases->count() === 1 ? $p->cases->first() : null;

            if (! $case) { echo "\n  [", $role, "] expected exactly one case row, found ",
                $p->cases->count(), " — skipped\n"; continue; }
        } elseif ($mode === "institution") {
            $case = $p->cases->first(fn ($c) => $c->institution
                && $c->institution->name === $spec["match_institution"]);

            if (! $case) { echo "\n  [", $role, "] no case at ", $spec["match_institution"], " — skipped\n"; continue; }
        } elseif ($mode === "stub") {
            // Charges first, so a second run finds the row this script
            // already filled in; the empty-stub search only matches on the
            // first run, when the row still holds the generic import charge.
            $case = $p->cases->first(fn ($c) => (string) $c->charges === (string) $spec["charges"])
                ?: $p->cases->first(fn ($c) => ! $c->institution_id
                    && ! $c->incarceration_date && ! $c->release_date && ! $c->arrest_date);

            if (! $case) { echo "\n  [", $role, "] no empty stub row found — skipped\n"; continue; }
        } elseif ($mode === "stub_annotate") {
            $case = $p->cases->first(fn ($c) => mb_strpos((string) $c->charges, $spec["match_charge"]) !== false);

            if (! $case) { echo "\n  [", $role, "] no row whose charges mention ",
                $spec["match_charge"], " — skipped\n"; continue; }

            $note = $spec["append_sentence"];

            if (mb_strpos((string) $case->sentence, mb_substr(trim($note), 0, 40)) !== false) {
                echo "\n  [", $role, "] note already present\n";

                continue;
            }

            $case->sentence = trim((string) $case->sentence).$note;
            $case->save();
            echo "\n  [", $role, "] note appended to the existing row\n";

            continue;
        } elseif ($mode === "new") {
            // Matched on charges so a re-run does not create it twice.
            $case = $p->cases->first(fn ($c) => (string) $c->charges === (string) $spec["charges"]);

            if ($case) {
                echo "\n  [", $role, "] already present [", $case->id, "] — updated in place\n";
            } else {
                $case = new PrisonerCase(["prisoner_id" => $p->id]);
                echo "\n  [", $role, "] creating a new case row\n";
            }
        } else {
            echo "\n  unknown match mode: ", $mode, " — skipped\n";

            continue;
        }

        if ($mode !== "new") { echo "\n  [", $role, "] on the existing row [", $case->id, "]\n"; }

        if (! empty($spec["institution"]) && ! $case->institution_id) {
            $inst = $institutionFor($spec["institution"]);
            $case->institution_id = $inst->id;
            echo "    institution: ", $inst->name,
                ($inst->wasRecentlyCreated ? " (created)" : " (existing)"), "\n";
        }

        foreach (["charges", "convicted", "sentence"] as $f) {
            if (array_key_exists($f, $spec)) { $case->{$f} = $spec[$f]; }
        }

        $applyDates($case, $spec["dates"] ?? []);

        $case->prisoner_id = $p->id;
        $case->save();
        $case->refresh();

        foreach (["arrest_date", "sentenced_date", "incarceration_date", "release_date"] as $f) {
            if ($case->{$f}) {
                echo "    ", str_pad($f, 20), $case->formatPartialDate($f),
                    "  [", $case->datePrecisionFor($f), "]\n";
            }
        }

        echo "    days = ", ($case->imprisoned_for_days ?? "null"), "\n";
    }

    // -------------------------------------------------------------- summary
    $p->refresh()->load("cases");

    $total = (int) $p->cases->sum("imprisoned_for_days");
    $start = $p->cases->map(fn ($c) => $c->incarceration_date ?: $c->arrest_date)->filter()->sort()->first();

    echo "\n  after:  ", $p->cases->count(), " case row(s), total ", $total, " days",
        " (was ", $before, ")\n";
    echo "  public counter: ", ($total > 0
        ? \App\Support\ImprisonmentDuration::phrase($start, $total,
            \App\Support\ImprisonmentDuration::documentedMonths($p->cases))
        : "(none)"), "\n";
}

// --------------------------------------------------------------- flagged
echo "\n", str_repeat("=", 67), "\nFLAGGED FOR THE CURATOR, NOT ACTED ON\n";

foreach ($payload["flagged"] as $f) {
    echo "\n  ", $f["name"], "\n  ", wordwrap($f["reason"], 84, "\n  "), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "may-1923-birthdays" apply_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 139 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
echo
echo "Expected counters: brazier 2162, sprague 2091, oconnell 1969,"
echo "caplan 3064 (2381 + 683), ruthenberg 860 (341 + 519)."
echo "Foster stays at none: he has no case row with dates at both ends."
