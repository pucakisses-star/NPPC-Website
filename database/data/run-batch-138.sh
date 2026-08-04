#!/usr/bin/env bash
#
# BATCH 138 -- six people from the Industrial Worker of August 6, 1924,
# per the curator.
#
#   FIVE OF THE SIX ARE ALREADY IN THE DATABASE. Only M. C. Sullivan is
#   genuinely absent. The other five need correcting, and one of the
#   corrections is large:
#
#     j-c-robinson     San Quentin 38127; convicted July 11, 1923;
#                      conviction reversed, ordered released by
#                      March 4, 1925. Had no institution and no dates
#                      at all.
#     roy-house        San Quentin 38535; arrested in the Portola
#                      roundup, four years from Plumas County. Loses an
#                      unsupported 1925 release date.
#     edward-r-peters  One to fourteen years; paroled January 10, 1924.
#                      Had a case row with no dates whatever; the
#                      parole was only in the prose.
#     john-lamb        Walla Walla 9412. LOSES A DEATH DATE — see below.
#     james-mcinerney  Already correct. Verified, not written.
#
#   JOHN LAMB. His record gave his death as June 6, 1922, of
#   tuberculosis at Walla Walla, and used that date as his release,
#   which cut his imprisonment to about two years three months of a 25
#   to 40 year sentence. The curator's August 6, 1924 prisoner list has
#   him alive and inside at number 9412, and his sentence is reported
#   commuted or pardoned in 1933. The tuberculosis detail matches what
#   is separately recorded of his co-defendant James McInerney, who did
#   die at Walla Walla, in 1930. The death date is removed as
#   unsupported and the removed claim is preserved in the record. This
#   is the one change here that should be reverted if a Washington
#   prison record turns up supporting 1922.
#
#   BIRTHDAYS WITH NO YEAR. The ledger gives Robinson August 19, House
#   August 21, Peters August 5 and Lamb August 25, none with a year.
#   The birthdate column cannot hold a date without a year, and a
#   fabricated year would be published as fact, so these are recorded
#   in the biographies as prose. McInerney already has a full date.
#
#   CHARGES. Robinson, House and Peters each carried an import-wide
#   default of "Federal prosecution under the Espionage Act of 1917
#   and/or the Sedition Act of 1918". These were state prosecutions
#   under California's 1919 Criminal Syndicalism Act. That string is on
#   617 rows in all; only these three are touched.
#
#   RELEASE DATES LEFT EMPTY. Robinson, House and Sullivan get no
#   release date, because none is established. That is only safe after
#   batch 137, which retired the nightly job that read an empty release
#   date as "still inside" and counted it to today. RUN BATCH 137
#   FIRST.
#
#   Idempotent: updates are matched by slug and re-applying writes the
#   same values; the creation is guarded by a name check.
#
# Run from the repo root, after git pull (AFTER BATCH 137):
#   bash database/data/run-batch-138.sh

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
echo "  Batch 138 — Industrial Worker, August 6, 1924: six people"
echo "==================================================================="

apply_batch() {
    php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch138.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$d = function ($v) { return $v ? $v->format("Y-m-d") : "-"; };

// ---------------------------------------------------------------- updates
echo "UPDATES\n";

foreach ($payload["updates"] as $u) {
    echo "\n", str_repeat("-", 67), "\n", $u["label"], "\n";

    $p = Prisoner::withUnderReview()->where("slug", $u["slug"])->with("cases")->first();

    if (! $p) { echo "  ", $u["slug"], " NOT FOUND — skipped\n"; continue; }

    echo "  before: era=", ($p->era ?: "-"),
        "  inmate=", ($p->inmate_number ?: "-"),
        "  died=", ($p->death_date ? $p->formatPartialDate("death_date") : "-"), "\n";

    foreach ($p->cases as $c) {
        echo "    case arrest=", $d($c->arrest_date), " in=", $d($c->incarceration_date),
            " out=", $d($c->release_date), " days=", ($c->imprisoned_for_days ?? "null"), "\n";
    }

    // ---- prisoner fields
    $pf = $u["prisoner"] ?? [];

    foreach (["inmate_number", "state", "era", "ideologies", "affiliation", "description", "gender"] as $f) {
        if (array_key_exists($f, $pf)) { $p->{$f} = $pf[$f]; }
    }

    if (! empty($pf["clear_death_date"]) && $p->death_date) {
        echo "  REMOVING death date ", $p->formatPartialDate("death_date"), "\n";
        $p->setPartialDate("death_date", null);
        $p->setAttribute("age", null);
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

    // ---- the case row
    $cu = $u["case"] ?? null;

    if ($cu) {
        $case = $cu["match"] === "arrest_date"
            ? $p->cases->first(fn ($c) => $c->arrest_date
                && $c->arrest_date->format("Y-m-d") === $cu["match_value"])
            : ($p->cases->count() === 1 ? $p->cases->first() : null);

        if (! $case) {
            echo "  CASE NOT MATCHED (", $cu["match"], ") — case left untouched\n";
        } else {
            if (! empty($cu["institution"]) && ! $case->institution_id) {
                $inst = Institution::firstOrCreate(
                    ["name" => $cu["institution"]["name"]],
                    ["city" => $cu["institution"]["city"], "state" => $cu["institution"]["state"]],
                );
                $case->institution_id = $inst->id;
                echo "  institution attached: ", $inst->name, "\n";
            }

            foreach (["charges", "convicted", "sentence"] as $f) {
                if (array_key_exists($f, $cu)) { $case->{$f} = $cu[$f]; }
            }

            foreach (($cu["dates"] ?? []) as $field => $parts) {
                if ($parts === null) {
                    if ($case->{$field}) { echo "  clearing ", $field, " (was ", $d($case->{$field}), ")\n"; }
                    $case->setPartialDate($field, null);

                    continue;
                }

                $case->setPartialDate($field, $parts[0], $parts[1] ?? null, $parts[2] ?? null);
                echo "  ", str_pad($field, 20), " = ", $case->formatPartialDate($field),
                    " [", $case->datePrecisionFor($field), "]\n";
            }

            $case->save();
            $case->refresh();

            echo "  after:  arrest=", $d($case->arrest_date), " in=", $d($case->incarceration_date),
                " out=", $d($case->release_date), " days=", ($case->imprisoned_for_days ?? "null"), "\n";
        }
    }
}

// ---------------------------------------------------------------- creates
echo "\n", str_repeat("=", 67), "\nNEW RECORDS\n";

foreach ($payload["creates"] as $row) {
    $existing = Prisoner::withUnderReview()->where("name", $row["name"])->first();

    if ($existing) {
        echo "  ", $row["name"], " already exists [", $existing->slug, "] — not recreated\n";

        continue;
    }

    $code = Artisan::call("prisoner:add", ["json" => json_encode($row)]);
    $p = Prisoner::withUnderReview()->where("name", $row["name"])->with("cases")->first();

    if (! $p) { echo "  ", $row["name"], " FAILED (exit ", $code, ")\n"; continue; }

    echo "  ", $row["name"], " created [", $p->slug, "]\n";
    echo "    in_custody=", ($p->in_custody ? "yes" : "no"),
        "  released=", ($p->released ? "yes" : "no"),
        "  gender=", ($p->gender ?: "(not set)"),
        "  race=", ($p->race ?: "(not set)"), "\n";
    echo "    cases=", $p->cases->count(),
        "  days=", ($p->cases->first()->imprisoned_for_days ?? "null"), "\n";
}

// ---------------------------------------------------------------- verify
echo "\n", str_repeat("=", 67), "\nVERIFIED, NOT WRITTEN\n";

foreach ($payload["verify"] as $v) {
    $p = Prisoner::withUnderReview()->where("slug", $v["slug"])->first();

    if (! $p) { echo "  ", $v["slug"], " NOT FOUND\n"; continue; }

    echo "\n  ", $p->name, " [", $p->slug, "]\n";

    foreach ($v["expect"] as $field => $want) {
        $have = $field === "birthdate"
            ? ($p->birthdate ? $p->birthdate->format("Y-m-d") : null)
            : $p->{$field};

        echo "    ", str_pad($field, 16), " want ", str_pad((string) $want, 12),
            " have ", ($have ?? "(empty)"),
            "  ", ((string) $have === (string) $want ? "OK" : "MISMATCH"), "\n";
    }

    echo "  ", wordwrap($v["note"], 84, "\n  "), "\n";
}

// ---------------------------------------------------------------- flagged
echo "\n", str_repeat("=", 67), "\nFLAGGED FOR THE CURATOR, NOT ACTED ON\n";

foreach ($payload["flagged"] as $f) {
    echo "\n  ", $f["name"], "\n  ", wordwrap($f["reason"], 84, "\n  "), "\n";
}

// ---------------------------------------------------------------- summary
echo "\n", str_repeat("=", 67), "\nSUMMARY\n";

$slugs = array_merge(
    array_column($payload["updates"], "slug"),
    ["m-c-sullivan"],
    array_column($payload["verify"], "slug"),
);

foreach ($slugs as $slug) {
    $p = Prisoner::withUnderReview()->where("slug", $slug)->with("cases")->first();

    if (! $p) { echo "  ", str_pad($slug, 20), " NOT FOUND\n"; continue; }

    $total = (int) $p->cases->sum("imprisoned_for_days");
    $start = $p->cases->map(fn ($c) => $c->incarceration_date ?: $c->arrest_date)->filter()->sort()->first();

    echo "  ", str_pad($p->slug, 20),
        " inmate=", str_pad($p->inmate_number ?: "-", 7),
        " era=", str_pad($p->era ?: "-", 7),
        " counter: ", ($total > 0
            ? \App\Support\ImprisonmentDuration::phrase($start, $total,
                \App\Support\ImprisonmentDuration::documentedMonths($p->cases))
            : "(none)"), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "iww-august-1924" apply_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 138 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
echo
echo "Robinson, House and Sullivan deliberately end with no counter."
echo "If any of them shows one tomorrow, batch 137 did not run."
