#!/usr/bin/env bash
#
# BATCH 148 -- Ronald Dwayne Glick: custody dates, and a Massachusetts
# jail detached from a Montana case.
#
#   His record held one case row with the charge "Sexual Assault" and
#   NO DATES OF ANY KIND, so the profile published no imprisonment
#   figure at all. The curator supplies the custody:
#
#     Feb 2004 - 18 Feb 2009   1,844 days   principal custody
#     23 Apr 2009 - Jun 2009      39 days   revocation, then dismissed
#
#   1,844 days is a little over five years, which is what a twenty-year
#   sentence with fifteen suspended implies. That agreement is the
#   check on the dates.
#
#   THE WRONG JAIL. The row was attached to Norfolk County Jail at
#   Dedham, Massachusetts — a jail with no connection to a Flathead
#   County, Montana prosecution. Because the profile prints
#   institution.mailing_address under "Mailing Address", his page has
#   been giving readers a Massachusetts address to write to. The
#   attachment is removed. No institution is put in its place: he was
#   held pretrial at the Flathead County Detention Center, but the
#   prison where the sentence was served is not established, and a
#   five-year term should not be recorded against a county jail.
#
#   THE SECOND ROW is the April 2009 re-arrest on probation-revocation
#   proceedings that were then dismissed. It is a separate custody and
#   gets its own row so the record does not read as one continuous
#   five-and-a-half-year imprisonment.
#
#   ON THE JUNE DATE. The source gives approximately six weeks, which
#   would end about June 4. That day is reached by counting forward
#   from the arrest, not from a discharge record, so it is stored at
#   month precision instead — June 2009, anchored at the 1st, which
#   measures 39 days where the six weeks would be 42. The difference is
#   written into the row.
#
#   THIS DOES NOT SETTLE WHETHER HE BELONGS. He is one of the five
#   Tier 1 findings in POLITICAL-MOTIVATION-AUDIT.md, where the record
#   states no political motivation and the only argument offered is
#   that he maintains innocence. Recording what happened to him and
#   deciding whether to keep him are different questions.
#
#   Idempotent: the second row is matched on its incarceration date
#   before being created.
#
# Run from the repo root, after git pull (after batch 147):
#   bash database/data/run-batch-148.sh

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
echo "  Batch 148 — Ron Glick: custody dates and a wrong jail detached"
echo "==================================================================="

apply_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch148.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$d = fn ($v) => $v ? $v->format("Y-m-d") : "----------";

$p = Prisoner::withUnderReview()->where("slug", $payload["slug"])->with("cases")->first();

if (! $p) { echo "  ", $payload["slug"], " NOT FOUND — nothing changed.\n"; return; }

echo "record: ", $p->name, "  [", $p->slug, "]\n";
echo "  before: ", $p->cases->count(), " case row(s), ",
    (int) $p->cases->sum("imprisoned_for_days"), " days\n";

foreach ($p->cases as $c) {
    echo "    in=", $d($c->incarceration_date), " out=", $d($c->release_date),
        "  institution=", ($c->institution ? $c->institution->name : "(none)"), "\n";
}

$first = null;

foreach ($payload["cases"] as $spec) {
    if ($spec["role"] === "existing") {
        $case = $p->cases->count() >= 1 ? $p->cases->first() : null;

        if (! $case) { echo "\n  no existing case row — first segment not applied\n"; continue; }

        if ($case->institution && $case->institution->name === $payload["detach_institution"]) {
            echo "\n  DETACHING ", $case->institution->name,
                " (", ($case->institution->state ?: "-"), ") — wrong institution for this case\n";
            $case->institution_id = null;
        }

        $first = $case;
    } else {
        $case = $p->cases->first(function ($c) use ($spec) {
            return $c->incarceration_date
                && $c->incarceration_date->format("Y-m-d") === sprintf("%04d-%02d-%02d",
                    $spec["dates"]["incarceration_date"][0],
                    $spec["dates"]["incarceration_date"][1],
                    $spec["dates"]["incarceration_date"][2]);
        });

        if ($case) {
            echo "\n  second row already present [", $case->id, "] — updated in place\n";
        } else {
            $case = new PrisonerCase(["prisoner_id" => $p->id]);
            echo "\n  creating the second row\n";
        }

        if ($first) { $case->charges = $first->charges; }
    }

    foreach ($spec["dates"] as $field => $parts) {
        $case->setPartialDate($field, $parts[0], $parts[1] ?? null, $parts[2] ?? null);
    }

    $case->convicted = $spec["convicted"];
    $case->sentence = $spec["sentence"];
    $case->prisoner_id = $p->id;
    $case->save();
    $case->refresh();

    foreach (["incarceration_date", "sentenced_date", "release_date"] as $f) {
        if ($case->{$f}) {
            echo "    ", str_pad($f, 20), $case->formatPartialDate($f),
                "  [", $case->datePrecisionFor($f), "]\n";
        }
    }

    echo "    days = ", ($case->imprisoned_for_days ?? "null"), "\n";
}

if (! empty($payload["description"])) { $p->description = $payload["description"]; $p->save(); }

$p->refresh()->load("cases");

$total = (int) $p->cases->sum("imprisoned_for_days");
$start = $p->cases->map(fn ($c) => $c->incarceration_date ?: $c->arrest_date)->filter()->sort()->first();

echo "\n  after: ", $p->cases->count(), " case row(s), ", $total, " days\n";
echo "  counter: ", ($total > 0
    ? \App\Support\ImprisonmentDuration::phrase($start, $total,
        \App\Support\ImprisonmentDuration::documentedMonths($p->cases))
    : "(none)"), "\n";

$stillWrong = $p->cases->filter(fn ($c) => $c->institution
    && $c->institution->name === $payload["detach_institution"])->count();

echo "  rows still on ", $payload["detach_institution"], ": ", $stillWrong, " (want 0)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "glick-custody" apply_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 148 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
echo
echo "Expected: 1844 + 39 = 1883 days across two rows, no institution."
echo "Whether he belongs in the archive is still an open question — see"
echo "database/data/POLITICAL-MOTIVATION-AUDIT.md."
