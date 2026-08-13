#!/usr/bin/env bash
#
# BATCH 233 -- Mortimer A. Downing: middle initial, birth and death.
#
#   27 AUGUST 1862 to 22 JUNE 1948, both at day precision, plus the middle
#   initial A. All three fields were empty.
#
#   THE SUPPLIED AGE IS ITS OWN CHECK AND IT HOLDS. Aged 85 at death is
#   exactly what those two dates give: he died two months short of his
#   eighty-sixth birthday. They also sit sensibly against the case already
#   on the record -- 55 when Leavenworth took him in November 1917, 61 when
#   he walked out on 22 December 1923, and twenty-four more years of life
#   after that. The batch prints all three ages.
#
#   THE SLUG DOES NOT MOVE. The model regenerates a slug only when the
#   name itself is dirty; middle_name is a separate column and the name is
#   untouched, so this stays at /prisoner/mortimer-downing. Asserted rather
#   than assumed -- a slug that moved silently is what made batches 211 and
#   212 write nothing at all.
#
#   NEWARK AND LOS ANGELES CANNOT BE RECORDED. The prisoner table has a
#   state column but no birthplace and no place of death, and state is for
#   the case. His reads California, where he organised and was prosecuted,
#   and it is left alone. The New Jersey birth and the Los Angeles death
#   are in this batch and in this commit, and nowhere on the page. Same
#   call as Bruno Grunzig in batch 228.
#
#   ONE THING FLAGGED AND NOT TOUCHED. His biography ends mid-sentence:
#   the paper he was elected to edit is given as The Industrial Worker?*,
#   a stray question mark and asterisk carried in from whatever the text
#   was scraped out of. Not asked about, so not rewritten.
#
#   Idempotent: each field is written only when it differs.
#
# Run from the repo root, after git pull, after batch 232:
#   bash database/data/run-batch-233.sh

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
echo "  Batch 233 — Mortimer A. Downing"
echo "==================================================================="

DOWNING_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch233.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$p = Prisoner::withoutGlobalScopes()->where("slug", $payload["prisoner"]["slug"])->first();

if (! $p) { echo "  !! no prisoner at that slug\n"; return; }

if ($p->name !== $payload["prisoner"]["expect_name"]) {
    echo "  !! that slug holds ", $p->name, " — stopping.\n";

    return;
}

$wasSlug = $p->slug;

if ($p->middle_name !== $payload["middle_name"]) {
    echo "  middle_name    ", str_pad($p->middle_name ?: "(none)", 12), " ->  ", $payload["middle_name"], "\n";
    $p->middle_name = $payload["middle_name"];
} else {
    echo "  middle_name    already ", $payload["middle_name"], "\n";
}

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
echo "    middle name   ", $p->middle_name, "   (the display name still reads ", $p->name, ")\n";
echo "    born          ", $p->formatPartialDate("birthdate"), "\n";
echo "    died          ", $p->formatPartialDate("death_date"), "\n";
echo "    age at death  ", ($p->age ?? "(none)"), "   (supplied as 85)\n";
echo "    slug          ", $wasSlug, " -> ", $p->slug,
    ($wasSlug === $p->slug ? "   (unmoved, as expected)" : "   !! THE SLUG MOVED"), "\n";
echo "    flags         in_custody ", var_export((bool) $p->in_custody, true),
    "   released ", var_export((bool) $p->released, true), "   (untouched)\n";
echo "    state         ", $p->state, "   (the case state — untouched)\n";
echo "    photo         ", ($p->photo ?: "(none)"), "   (untouched)\n";

// The dates read against the imprisonment already on the record.
$case = $p->cases()->first();

if ($case && $p->birthdate) {
    if ($case->incarceration_date) {
        echo "\n    age at ", $case->formatPartialDate("incarceration_date"), " (Leavenworth): ",
            (int) $p->birthdate->diffInYears($case->incarceration_date, false), "\n";
    }

    if ($case->release_date) {
        echo "    age at ", $case->formatPartialDate("release_date"), " (release): ",
            (int) $p->birthdate->diffInYears($case->release_date, false), "\n";
        echo "    lived ", (int) $case->release_date->diffInYears($p->death_date, false),
            " more years after release\n";
    }
}

// The unfinished sentence, printed rather than described.
if (str_contains((string) $p->description, "Worker?*")) {
    echo "\n  !! the biography still ends: ...",
        mb_substr((string) $p->description, -72), "\n";
}

echo "\n  ", wordwrap($payload["age_check"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["slug_note"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["places"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["flag_typo"], 72, "\n  "), "\n";

$e = $payload["expected"];

$ok = $p->middle_name === $e["middle_name"]
    && optional($p->birthdate)->toDateString() === $e["birthdate"]
    && optional($p->death_date)->toDateString() === $e["death_date"]
    && ($p->date_precision["birthdate"] ?? null) === "day"
    && ($p->date_precision["death_date"] ?? null) === "day"
    && (int) $p->age === (int) $e["age"]
    && $p->slug === $e["slug"];

if ($ok) { echo "\nB233-OK\n"; }
'

run_tinker "mortimer-downing" "B233-OK" "$DOWNING_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 233 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
