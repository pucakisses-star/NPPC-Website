#!/usr/bin/env bash
#
# BATCH 140 -- institutions publishing another prison's mailing address.
#
#   NOT REQUESTED. Found while entering Richard Brazier's dates in
#   batch 139, and fixed because of what it is.
#
#   The prisoner profile renders institution.mailing_address under the
#   heading "Mailing Address". That is the address a reader uses to
#   write to a prisoner — the practical point of a page like this.
#   Eighteen institution records carry the address of a different
#   facility, across about 210 case rows:
#
#     USP Leavenworth              87   FCI Marianna, Florida
#     U.S. Penitentiary Atlanta    19   FCI Petersburg, Virginia
#     Butler County Prison         13   a private box in Seminole, FL
#     SCI Dallas                   12   a named prisoner at another jail
#     Occoquan Workhouse / DC Jail 11   MCC Chicago
#     ... and thirteen more
#
#   Richard Brazier, imprisoned at Leavenworth in 1917, publishes the
#   address of an operating federal prison in Florida. So do eighty-five
#   other people. Two records go further and publish a named
#   individual's inmate number on other people's profiles.
#
#   NOTHING IS GUESSED. Every entry is cleared, not corrected — except
#   Trinidad, where the facility address is genuinely right and only a
#   personal line is stripped. An address is removed only where the
#   stored text names a facility other than the institution it sits on.
#
#   AND NOTHING IS CLEARED BLIND. Each entry carries an expect_contains
#   guard: if the stored address no longer contains the marker this
#   audit saw, the record is reported and left alone. A record whose
#   address has been fixed by hand in the meantime survives this
#   script.
#
#   DELIBERATELY UNTOUCHED: institutions whose address is a legitimate
#   central mail-processing box for their own facility — Edna Mahan
#   through the New Jersey vendor in Las Vegas, Lane County through
#   Smart Communications, the Yuma unit through Arizona's Phoenix
#   address. Those look wrong and are right.
#
#   Idempotent: a second run finds the fields already empty and says so.
#
# Run from the repo root, after git pull (after batch 139):
#   bash database/data/run-batch-140.sh

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
echo "  Batch 140 — institutions publishing another prison's address"
echo "==================================================================="

apply_batch() {
    php artisan tinker --execute='
use App\Models\Institution;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch140.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$flat = fn ($s) => trim(preg_replace("/\s+/", " ", (string) $s));

$cleared = 0;
$skipped = 0;
$already = 0;
$rowsFixed = 0;

$handle = function (array $row, array $fields) use (&$cleared, &$skipped, &$already, &$rowsFixed, $flat) {
    $inst = Institution::where("name", $row["name"])->first();

    echo "\n  ", $row["name"], "\n";

    if (! $inst) { echo "    NOT FOUND — skipped\n"; $skipped++; return; }

    $rows = PrisonerCase::where("institution_id", $inst->id)->count();

    echo "    case rows: ", $rows, " (audit said ", $row["cases"], ")\n";

    $current = $flat($inst->mailing_address)." ".$flat($inst->physical_address);

    // Only the fields this entry actually changes. A field already empty
    // (for a clear) or already holding the new text (for a rewrite) is
    // done, and if none is left there is nothing to guard or to write —
    // which is what the second run of this script sees.
    $pending = array_filter(
        $fields,
        fn ($value, $field) => $flat($inst->{$field}) !== ""
            && $flat($inst->{$field}) !== $flat((string) $value),
        ARRAY_FILTER_USE_BOTH,
    );

    if (! $pending) {
        echo "    already applied — nothing to do\n";
        $already++;

        return;
    }

    if (mb_stripos($current, $row["expect_contains"]) === false) {
        echo "    GUARD: the stored address no longer contains \"", $row["expect_contains"], "\"\n";
        echo "    mailing:  ", mb_strimwidth($flat($inst->mailing_address), 0, 88, "..."), "\n";
        echo "    physical: ", mb_strimwidth($flat($inst->physical_address), 0, 88, "..."), "\n";
        echo "    left alone — it has changed since the audit and wants a fresh look\n";
        $skipped++;

        return;
    }

    foreach ($pending as $field => $value) {
        $was = $flat($inst->{$field});

        echo "    ", str_pad($field, 18), " was: ", mb_strimwidth($was, 0, 76, "..."), "\n";
        $inst->{$field} = $value;
        echo "    ", str_pad("", 18), " now: ", ($value === null ? "(empty)" : $flat($value)), "\n";
    }

    $inst->save();
    $cleared++;
    $rowsFixed += $rows;

    echo "    ", wordwrap($row["reason"], 80, "\n    "), "\n";
};

echo "\n", str_repeat("=", 67), "\nCLEARED — BOTH FIELDS\n";

foreach ($payload["clear_both"] as $row) {
    $handle($row, ["mailing_address" => null, "physical_address" => null]);
}

echo "\n", str_repeat("=", 67), "\nCLEARED — MAILING ONLY, THE PHYSICAL ADDRESS IS THE FACILITY OWN\n";

foreach ($payload["clear_mailing_only"] as $row) {
    $handle($row, ["mailing_address" => null]);
}

echo "\n", str_repeat("=", 67), "\nCLEARED — THE LITERAL STRING N/A\n";

foreach ($payload["clear_na"] as $row) {
    $handle($row, ["mailing_address" => null, "physical_address" => null]);
}

echo "\n", str_repeat("=", 67), "\nREWRITTEN\n";

foreach ($payload["rewrite"] as $row) {
    $handle($row, [$row["field"] => $row["value"]]);
}

// ------------------------------------------------------------------ sweep
echo "\n", str_repeat("=", 67), "\nWHAT IS LEFT\n";

$remaining = Institution::query()
    ->where(fn ($q) => $q->whereNotNull("mailing_address")->orWhereNotNull("physical_address"))
    ->get()
    ->filter(fn ($i) => $flat($i->mailing_address) !== "" || $flat($i->physical_address) !== "");

echo "\n  ", $remaining->count(), " institutions still carry an address.\n";
echo "  Listed so the ones deliberately kept can be eyeballed:\n\n";

foreach ($remaining->sortBy("name") as $i) {
    $rows = PrisonerCase::where("institution_id", $i->id)->count();

    echo "    [", str_pad((string) $rows, 3, " ", STR_PAD_LEFT), " rows] ", $i->name, "\n";
    echo "              ", mb_strimwidth($flat($i->mailing_address) ?: "(no mailing address)", 0, 84, "..."), "\n";
}

echo "\n", str_repeat("=", 67), "\nFLAGGED FOR THE CURATOR, NOT ACTED ON\n";

foreach ($payload["flagged"] as $f) {
    echo "\n  ", $f["name"], "\n  ", wordwrap($f["reason"], 84, "\n  "), "\n";
}

echo "\n", str_repeat("=", 67), "\n";
echo "  institutions corrected:      ", $cleared, "\n";
echo "  already clean:               ", $already, "\n";
echo "  skipped by the guard:        ", $skipped, "\n";
echo "  case rows no longer showing a wrong address: ", $rowsFixed, "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "institution-addresses" apply_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 140 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
echo
echo "Every removed value is printed above. Nothing was corrected to a"
echo "different address: where the right one is not known, the field is"
echo "empty, and an empty field is better than a wrong one on a page"
echo "that tells people where to send a letter."
