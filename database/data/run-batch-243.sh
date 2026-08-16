#!/usr/bin/env bash
#
# BATCH 243 -- five of the 1990 tribunal petitioners get the case they were
# imprisoned for.
#
#   ALL ELEVEN OF THESE RECORDS ENTERED THE SAME WAY: as bare names on the
#   petitioner list of the 1990 Special International Tribunal on Political
#   Prisoners and Prisoners of War in the United States. No case, no alias,
#   no inmate number, mostly no state. The tribunal named them as political
#   prisoners; it did not record what they had been prosecuted for.
#
#     Ana María Gelabert   two counts of attempted capital murder of a
#                          police officer, Houston, 26 December 1983; life
#                          on each count. Alias Ana Lucia Gelabert, Texas
#                          prisoner 384484.
#     Dorothy Eber         entering a Minuteman missile installation in
#                          Missouri; two years federal, at Lexington by
#                          early February 1989.
#     Jennifer Haines      Rocky Flats, Christmas Day 1981; guilty 1 March
#                          1982 before Judge Zita Weinshienk, suspended.
#     Raphael Kwesi Joseph the Virgin Islands Five, Fountain Valley;
#                          convicted 13 August 1973, eight consecutive
#                          life terms, pardoned 1992.
#     Yvonne Small         Site R at Raven Rock, 5 August 1990; six to
#                          twelve months, imposed 25 October 1990.
#
#   THE MOST IMPORTANT THING HERE IS A CASE NOT ATTACHED. Alberta Wicker
#   Africa, Carlos Perez Africa, Consuella Dotson Africa, Michael Hill
#   Africa and Sue Leon Africa are NOT five alternative names for members
#   of the MOVE 9, and must not be swept into the 8 August 1978 case. A
#   1989 directory lists them separately and in different prisons --
#   Consuella Dotson and Alberta Wicker at Muncy, Carlos Perez at Dallas, a
#   Michael Africa at Huntingdon. Michael Hill Africa in particular should
#   not be conflated with Michael Davis Africa of the MOVE 9. They get
#   documented prisoner numbers here and nothing else.
#
#   ROBERT TAYLOR IS LEFT ALONE. A 1989 directory gives #88-A-8613 at
#   Downstate, New York; a 1990 New Afrikan pamphlet gives #10376-054 at
#   FCI Otisville; a 1993 list ties that federal number to Attica. One man
#   moving between state and federal custody, a list error, or two Robert
#   Taylors -- merging them blind would manufacture a person.
#
#   Idempotent: a case is created only where the record has none, and each
#   field is written only where it is empty.
#
# Run from the repo root, after git pull, after batch 242:
#   bash database/data/run-batch-243.sh

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
echo "  Batch 243 — cases for the 1990 tribunal petitioners"
echo "==================================================================="

TRIB_CODE='
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch243.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$madeCases = 0; $madeIds = 0; $bad = [];

foreach ($payload["people"] as $e) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $e["slug"])->first();

    if (! $p) { echo "  !! no record at ", $e["slug"], "\n"; $bad[] = $e["slug"]; continue; }

    if ($p->name !== $e["expect_name"]) {
        echo "  !! ", $e["slug"], " now holds ", $p->name, " — skipped\n";
        $bad[] = $e["slug"];

        continue;
    }

    echo "\n  ", str_repeat("-", 66), "\n";
    echo "  ", $p->name, "   [/prisoner/", $p->slug, "]\n";

    // Prisoner-level fields, only where empty.
    foreach (($e["prisoner"] ?? []) as $field => $value) {
        if (filled($p->{$field})) {
            echo "    ", str_pad($field, 15), " already ", $p->{$field}, " — left alone\n";

            continue;
        }

        $p->{$field} = $value;
        echo "    ", str_pad($field, 15), " -> ", $value, "\n";
    }

    $p->save();

    if ($p->cases()->count() > 0) {
        echo "    already has a case row — no case created\n";

        continue;
    }

    $c = $e["case"];
    $instId = null;

    if (! empty($c["institution"])) {
        $inst = Institution::where("name", $c["institution"])->first();

        if (! $inst) {
            echo "    !! no institution named ", $c["institution"], " — case created without one\n";
            $bad[] = $e["slug"]." institution";
        } else {
            $instId = $inst->id;
        }
    }

    $row = ["prisoner_id" => $p->id, "institution_id" => $instId];

    foreach (["charges", "convicted", "judge", "sentence", "arrest_date", "sentenced_date", "release_date"] as $f) {
        if (isset($c[$f])) { $row[$f] = $c[$f]; }
    }

    if (! empty($e["precision"])) { $row["date_precision"] = $e["precision"]; }

    $case = PrisonerCase::create($row);
    $case->refresh();
    $madeCases++;

    echo "    CASE CREATED\n";
    echo "      charges     ", mb_substr((string) $case->charges, 0, 66), "\n";
    echo "      convicted   ", mb_substr((string) $case->convicted, 0, 66), "\n";

    foreach (["arrest_date", "sentenced_date", "release_date"] as $f) {
        if ($case->{$f}) { echo "      ", str_pad($f, 12), $case->formatPartialDate($f), "\n"; }
    }

    if ($case->judge) { echo "      judge       ", $case->judge, "\n"; }

    echo "      institution ", ($case->institution?->name ?: "(none)"), "\n";
    echo "      duration    ", ($case->imprisoned_for_days ?? "(not computable)"), "\n";
}

// Identifiers only, for records whose case is not yet researched.
echo "\n  ", str_repeat("-", 66), "\n";
echo "  IDENTIFIERS ONLY — no case attached\n";

foreach ($payload["identifiers"] as $e) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $e["slug"])->first();

    if (! $p || $p->name !== $e["expect_name"]) {
        echo "    !! ", $e["slug"], " missing or renamed — skipped\n";
        $bad[] = $e["slug"];

        continue;
    }

    if (filled($p->inmate_number)) {
        echo "    ", str_pad($p->name, 26), " already ", $p->inmate_number, " — left alone\n";

        continue;
    }

    $p->inmate_number = $e["inmate_number"];
    $p->save();
    $madeIds++;

    echo "    ", str_pad($p->name, 26), " inmate number -> ", $e["inmate_number"],
        "   (cases: ", $p->cases()->count(), ")\n";
}

echo "\n  cases created ", $madeCases, "   inmate numbers written ", $madeIds, "\n";

$noCase = Prisoner::withoutGlobalScopes()->doesntHave("cases")->count();

echo "  records still with no case at all: ", $noCase, "\n";

echo "\n  ", wordwrap($payload["provenance"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["not_attached"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["not_the_move_9"], 72, "\n  "), "\n";

echo "\n  FLAGGED, NOT CHANGED\n";

foreach ($payload["flags"] as $i => $f) {
    echo "\n  ", ($i + 1), ". ", wordwrap($f, 69, "\n     "), "\n";
}

echo "\n  problems: ", count($bad), "\n";

foreach ($bad as $b) { echo "    !! ", $b, "\n"; }

// A first run creates all five; a re-run creates none because every record
// already has its case. Anything between the two means something is wrong.
$expected = (int) $payload["expected"]["cases"];

if (count($bad) === 0 && ($madeCases === $expected || $madeCases === 0)) { echo "\nB243-OK\n"; }
'

run_tinker "tribunal-cases" "B243-OK" "$TRIB_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 243 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
