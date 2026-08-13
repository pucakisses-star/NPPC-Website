#!/usr/bin/env bash
#
# BATCH 232 -- Timothy Adams gets a birth date and a death date.
#
#   22 MARCH 1948 to 22 JUNE 1982. Both at day precision. Neither was on
#   the record; both fields were empty.
#
#   THE RECORD IS TIMOTHY ADAMS, NOT TIMOTHY ADAM. The request named
#   Timothy Adam. This is the only Timothy Adam-anything in the database,
#   so that is where the dates go, and the batch guards on the name at
#   the slug rather than trusting the slug alone -- batches 211 and 212
#   silently wrote nothing on this branch because a slug had moved.
#
#   THE DATES CHECK OUT AGAINST HIS OWN DESCRIPTION, which is a better
#   witness here than any search result. It calls him a 25-year-old at
#   the Bushwick raid of 3 October 1973. Born 22 March 1948, he was 25
#   that day, having turned 25 six months earlier. October 1948 onward
#   would make him 24; before October 1947, 26.
#
#   THIS ALSO FIXES SOMETHING THE PAGE WAS GETTING WRONG. Age is a stored
#   column, recomputed on save as birthdate to death_date, or to today
#   when there is no death date. It read 78 -- 1948 measured against
#   2026. The site has been showing a man who died in 1982 as a living
#   78-year-old. It recomputes to 34, his age at death. The batch asserts
#   the 34.
#
#   DIED FREE, NOT IN CUSTODY. He is flagged released, so the timeline
#   reads this as a death out of prison. That is right: no incarceration
#   date, and the record says outright that the outcome of the charges is
#   not documented. The flags are untouched.
#
#   WHERE THE DATES COME FROM IS NOT ESTABLISHED. A search against the
#   Black Liberation Army returned nothing on him. Recorded as supplied,
#   corroborated by the age arithmetic and by nothing else.
#
#   Idempotent: each field is written only when it differs.
#
# Run from the repo root, after git pull, after batch 231:
#   bash database/data/run-batch-232.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run_tinker() {
    local label="$1" sentinel="$2" code="$3" out
    echo; echo "--- ${label}"
    out=$(php artisan tinker --execute="$code" 2>&1) || true
    printf '%s\n' "$out"
    if ! grep -q "$sentinel" <<<"$out"; then
        echo "  !! FAILED: ${label} — sentinel ${sentinel} missing (exception above?)"
        FAILED+=("${label}")
    fi
}

echo "==================================================================="
echo "  Batch 232 — Timothy Adams, birth and death"
echo "==================================================================="

ADAMS_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch232.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withoutGlobalScopes()->where("slug", $payload["prisoner"]["slug"])->first();

if (! $p) { echo "  !! no prisoner at that slug\n"; return; }

if ($p->name !== $payload["prisoner"]["expect_name"]) {
    echo "  !! that slug holds ", $p->name, " — stopping.\n";

    return;
}

$wasAge = $p->age === null ? "(none)" : $p->age;

foreach (["birthdate", "death_date"] as $field) {
    $spec = $payload[$field];
    $was = optional($p->{$field})->toDateString() ?: "(none)";

    if ($was !== $spec["value"]) {
        $p->{$field} = $spec["value"];
        echo "  ", str_pad($field, 14), " ", str_pad($was, 12), " ->  ", $spec["value"], "\n";
    } else {
        echo "  ", str_pad($field, 14), " already ", $spec["value"], "\n";
    }

    if (($p->date_precision[$field] ?? null) !== $spec["precision"]) {
        $p->date_precision = array_merge($p->date_precision ?? [], [$field => $spec["precision"]]);
    }
}

// age is recomputed by the saving hook, not written here.
$p->save();
$p->refresh();

echo "\n  ", $p->name, "  [/prisoner/", $p->slug, "]\n";
echo "    born          ", $p->formatPartialDate("birthdate"), "\n";
echo "    died          ", $p->formatPartialDate("death_date"), "\n";
echo "    age           ", $wasAge, "  ->  ", ($p->age ?? "(none)"), "   (recomputed on save)\n";
echo "    flags         in_custody ", var_export((bool) $p->in_custody, true),
    "   released ", var_export((bool) $p->released, true), "   (untouched)\n";
echo "    era / state   ", $p->era, "   ", $p->state, "   (untouched)\n";

$case = $p->cases()->first();

if ($case) {
    echo "    arrested      ", ($case->arrest_date ? $case->formatPartialDate("arrest_date") : "(none)"),
        "   (untouched)\n";
    echo "    incarcerated  ", ($case->incarceration_date ? $case->formatPartialDate("incarceration_date") : "(none)"),
        "   (untouched)\n";

    // The check that matters, printed rather than asserted quietly: his own
    // description gives his age at the arrest already on the case row.
    if ($case->arrest_date && $p->birthdate) {
        echo "\n    age at the ", $case->formatPartialDate("arrest_date"), " arrest: ",
            (int) $p->birthdate->diffInYears($case->arrest_date, false),
            "   (the description says 25-year-old)\n";
    }
}

echo "\n  ", wordwrap($payload["name_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["corroboration"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["age_fix"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["died_free"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["unsourced"], 72, "\n  "), "\n";

$ok = optional($p->birthdate)->toDateString() === $payload["expected"]["birthdate"]
    && optional($p->death_date)->toDateString() === $payload["expected"]["death_date"]
    && ($p->date_precision["birthdate"] ?? null) === "day"
    && ($p->date_precision["death_date"] ?? null) === "day"
    && (int) $p->age === (int) $payload["expected"]["age"];

if ($ok) { echo "\nB232-OK\n"; }
'

run_tinker "timothy-adams" "B232-OK" "$ADAMS_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 232 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
