#!/usr/bin/env bash
#
# BATCH 141 -- the three Woodlawn sedition defendants, per the curator.
#
#   ALL THREE ARE ALREADY IN THE DATABASE AND ALL THREE PUBLISH ZERO
#   DAYS. Each case row holds the five-year sentence and nothing else:
#   no institution, no incarceration date, and for two of them no
#   release date either. The curator supplies the endpoints.
#
#     pete-muselin    26 Nov 1929 - 3 Feb 1932    799 days
#     tom-zima        26 Nov 1929 - 3 Feb 1932    799 days
#     milan-resetar   26 Nov 1929 - 18 Oct 1931   691 days, died inside
#
#   Both stated day-counts reproduce exactly from the dates, which is
#   the check on them.
#
#   RESETAR DIED AT BLAWNOX. His death goes in death_in_custody_date
#   rather than being written as an ordinary release: the model mirrors
#   it onto the release date, so the imprisonment stops at his death,
#   and the record says how the custody ended. His death date on the
#   prisoner record sharpens from October 1931 to October 18, and his
#   released flag goes false — he was never released.
#
#   A CONFLICT KEPT RATHER THAN SMOOTHED. Muselin's biography says he
#   and Zima obtained pardons in December 1931; the curator gives a
#   parole on February 3, 1932. Different mechanisms, two months apart,
#   and not necessarily contradictory. February 3 is stored, because it
#   is the day the custody ended, and both are written into the record.
#
#   THE CHARGE WAS WRONG ON ALL THREE. Each carried the import-wide
#   default of a federal Espionage and Sedition Act prosecution. This
#   was a state prosecution under Pennsylvania's Flynn anti-sedition
#   law, arising from a red raid on Communist organizers of the Jones &
#   Laughlin steelworkers on November 11, 1926 — eight years after the
#   war the Espionage Act was passed for. The wording now used is the
#   wording already on steve-bratich and steve-bradich, two records for
#   the same prosecution that were better documented than these three,
#   so the whole case finally reads the same way.
#
#   OCR REPAIRS. These biographies were transcribed from a scanned
#   book and the scan came with them: Blawnox misread as Biawnox
#   throughout, a running head and page number sitting in the middle of
#   a quotation about Resetar dying, "amember", "doc- trine",
#   "Woodland" for Woodlawn, and a section heading captured onto the
#   end of Zima's three-line biography. Repaired by literal
#   find-and-replace, each one reported as applied or already done, so
#   nothing is rewritten blind.
#
#   Idempotent throughout.
#
# Run from the repo root, after git pull (after batch 140):
#   bash database/data/run-batch-141.sh

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
echo "  Batch 141 — Woodlawn sedition case: three records, zero counters"
echo "==================================================================="

apply_batch() {
    php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch141.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$d = fn ($v) => $v ? $v->format("Y-m-d") : "----------";

$inst = Institution::firstOrCreate(
    ["name" => $payload["institution"]["name"]],
    ["city" => $payload["institution"]["city"], "state" => $payload["institution"]["state"]],
);

echo "institution: ", $inst->name, " (", ($inst->wasRecentlyCreated ? "created" : "existing"), ")\n";

foreach ($payload["people"] as $person) {
    echo "\n", str_repeat("=", 67), "\n", $person["label"], "\n";

    $p = Prisoner::withUnderReview()->where("slug", $person["slug"])->with("cases")->first();

    if (! $p) { echo "  ", $person["slug"], " NOT FOUND — skipped\n"; continue; }

    $before = (int) $p->cases->sum("imprisoned_for_days");

    echo "  before: ", $p->cases->count(), " case row(s), ", $before, " days";
    echo "  died=", ($p->death_date ? $p->formatPartialDate("death_date") : "-"), "\n";

    $pf = $person["prisoner"] ?? [];

    foreach (["state", "era", "ideologies", "affiliation", "inmate_number"] as $f) {
        if (array_key_exists($f, $pf)) { $p->{$f} = $pf[$f]; }
    }

    if (array_key_exists("released", $pf)) {
        $p->released = (bool) $pf["released"];
        echo "  released flag -> ", ($p->released ? "true" : "false"), "\n";
    }

    if (! empty($pf["death_date"])) {
        $dd = $pf["death_date"];
        $p->setPartialDate("death_date", $dd[0], $dd[1] ?? null, $dd[2] ?? null);
        echo "  death date -> ", $p->formatPartialDate("death_date"),
            " [", $p->datePrecisionFor("death_date"), "]\n";
    }

    // ---- biography: whole replacement, guarded by a marker from the old text
    if (! empty($pf["description"])) {
        $marker = $pf["description_replaces_marker"] ?? null;

        if ($marker === null || mb_strpos((string) $p->description, $marker) !== false) {
            $p->description = $pf["description"];
            echo "  biography replaced\n";
        } else {
            echo "  biography: marker not found, already replaced\n";
        }
    }

    // ---- biography: literal find-and-replace
    foreach (($pf["description_replacements"] ?? []) as $r) {
        $count = mb_substr_count((string) $p->description, $r["from"]);

        if ($count === 0) {
            echo "  repair: already done — ", mb_strimwidth($r["from"], 0, 54, "..."), "\n";

            continue;
        }

        if (empty($r["all"]) && $count > 1) {
            echo "  repair: SKIPPED, ", $count, " matches for a single replacement — ",
                mb_strimwidth($r["from"], 0, 44, "..."), "\n";

            continue;
        }

        $p->description = str_replace($r["from"], $r["to"], $p->description);
        echo "  repair: ", $count, "x  ", mb_strimwidth($r["from"], 0, 54, "..."), "\n";
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
    $cu = $person["case"];
    $case = $p->cases->count() === 1 ? $p->cases->first() : null;

    if (! $case) {
        echo "  expected exactly one case row, found ", $p->cases->count(), " — case left untouched\n";

        continue;
    }

    if (! $case->institution_id) {
        $case->institution_id = $inst->id;
        echo "  institution attached\n";
    }

    $case->charges = $payload["case_charges"];
    $case->convicted = $cu["convicted"];
    $case->sentence = $cu["sentence"];

    foreach ($cu["dates"] as $field => $parts) {
        $case->setPartialDate($field, $parts[0], $parts[1] ?? null, $parts[2] ?? null);
    }

    $case->save();
    $case->refresh();

    echo "  case: in=", $d($case->incarceration_date),
        " out=", $d($case->release_date),
        " died-inside=", $d($case->death_in_custody_date),
        " days=", ($case->imprisoned_for_days ?? "null"), "\n";

    $p->refresh()->load("cases");

    $total = (int) $p->cases->sum("imprisoned_for_days");
    $start = $p->cases->map(fn ($c) => $c->incarceration_date ?: $c->arrest_date)->filter()->sort()->first();

    echo "  counter: ", ($total > 0
        ? \App\Support\ImprisonmentDuration::phrase($start, $total,
            \App\Support\ImprisonmentDuration::documentedMonths($p->cases))
        : "(none)"), "   [", $total, " days, was ", $before, "]\n";
}

echo "\n", str_repeat("=", 67), "\nFLAGGED FOR THE CURATOR, NOT ACTED ON\n";

foreach ($payload["flagged"] as $f) {
    echo "\n  ", $f["name"], "\n  ", wordwrap($f["reason"], 84, "\n  "), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "woodlawn-sedition-custody" apply_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 141 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
echo
echo "Expected: muselin 799, zima 799, resetar 691."
echo "Resetar's release date is written by the model from his death in"
echo "custody, so it should read 1931-10-18 without having been set."
