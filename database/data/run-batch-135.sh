#!/usr/bin/env bash
#
# BATCH 135 -- CRDL ledger 456-890: create the records that are actually
# missing, per the curator.
#
#   THE LEDGER IS MOSTLY ALREADY IN THE DATABASE. The curator's pasted
#   ledger ran to 435 entries but held only 287 distinct people: 148
#   entries re-enter somebody already named in the same range, seven
#   names three times over. Checked against all 8,570 records, 246 of
#   the 287 already exist under an exact name match and 15 more under
#   a spelling variant. Nineteen were absent on both a full-name and a
#   first-plus-surname check. Those nineteen are created here.
#
#   Five of them are Tougaloo Nine members — Albert Earl Lassiter,
#   Ethel Sawyer, Janice LaJune Jackson, James Cleo Bradford and
#   Geraldine Edwards. Four of the nine were already in the database;
#   these five were not. Their case rows carry the documented outcome
#   the CRDL material does supply: roughly thirty-two hours held,
#   convicted of disturbing the peace, $100 fine, thirty-day
#   suspended sentence.
#
#   The other fourteen are Freedom Riders and Prayer Pilgrimage clergy
#   whose disposition CRDL does not establish. Their case rows carry
#   the arrest date and say so, in the same words the earlier CRDL
#   waves used, with no incarceration date — so no imprisonment
#   counter is manufactured for them.
#
#   WHAT IS NOT INFERRED. Race is never set. Gender is set only where
#   the ledger uses a pronoun or a gendered description for that
#   person, and left empty for the five where it does not — a name is
#   not evidence of gender. Birth years are entered only for the six
#   the ledger documents, at year precision except Margaret Winonah
#   Beamer, whose full birth date is given.
#
#   Beamer also carries the ledger claim that she refused bail and
#   stayed imprisoned until December 25, 1961, reportedly the longest
#   full term of any 1961 Freedom Rider. That is recorded in her
#   biography as reported and NOT entered as custody dates, because
#   the ledger itself rates it moderate confidence against
#   non-CRDL sources.
#
#   TWO ARE FLAGGED, NOT CREATED: Lewis A. Zuchman and Jesse James
#   Harris. See the flagged block in
#   database/data/fixes/batch135.json for why.
#
#   Records are created through prisoner:add so its duplicate check
#   applies, with the JSON passed via Artisan::call so it never
#   touches the shell. Birth and death dates are set in a second pass,
#   prisoner:add having no way to express precision. Idempotent.
#
# Run from the repo root, after git pull (after batch 134):
#   bash database/data/run-batch-135.sh

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
echo "  Batch 135 — CRDL ledger 456-890: 19 genuinely missing records"
echo "==================================================================="

apply_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch135.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

echo "NEW RECORDS\n";

$made = 0;
$had = 0;

foreach ($payload["creates"] as $row) {
    $existing = Prisoner::withUnderReview()->where("name", $row["name"])->first();

    if ($existing) {
        echo "  ", str_pad($row["name"], 30), " already exists [", $existing->slug, "], not recreated\n";
        $had++;

        continue;
    }

    $code = Artisan::call("prisoner:add", ["json" => json_encode($row)]);

    $p = Prisoner::withUnderReview()->where("name", $row["name"])->first();

    if ($p) { $made++; echo "  ", str_pad($row["name"], 30), " created [", $p->slug, "]\n"; }
    else { echo "  ", str_pad($row["name"], 30), " FAILED (exit ", $code, ")\n"; }
}

echo "\n  ", $made, " created, ", $had, " already present, out of ", count($payload["creates"]), ".\n";

// ---- birth and death dates, second pass: prisoner:add cannot express precision
echo "\nDATES\n";

foreach ($payload["dates"] as $row) {
    $p = Prisoner::withUnderReview()->where("name", $row["name"])->first();

    if (! $p) { echo "  ", $row["name"], " — NOT FOUND\n"; continue; }

    $notes = [];

    if (! empty($row["birth"]) && ! $p->birthdate) {
        $b = $row["birth"];
        $p->setPartialDate("birthdate", $b["year"], $b["month"] ?? null, $b["day"] ?? null);
        $notes[] = "born ".$p->formatPartialDate("birthdate")." [".$p->datePrecisionFor("birthdate")."]";
    }

    if (! empty($row["death"]) && ! $p->death_date) {
        $p->setPartialDate("death_date", $row["death"]["year"]);
        $notes[] = "died ".$p->formatPartialDate("death_date")." [".$p->datePrecisionFor("death_date")."]";
    }

    if ($notes) { $p->save(); }

    echo "  ", str_pad($row["name"], 30), " ", implode("; ", $notes ?: ["already set"]), "\n";
}

// ---- flagged
echo "\nFLAGGED, NOT CREATED\n";

foreach ($payload["flagged"] as $f) {
    echo "\n  ", $f["name"], "\n  ", wordwrap($f["reason"], 86, "\n  "), "\n";
}

// ---- summary
echo "\nSUMMARY\n";

foreach ($payload["creates"] as $row) {
    $p = Prisoner::withUnderReview()->where("name", $row["name"])->with("cases")->first();

    if (! $p) { continue; }

    $c = $p->cases->first();

    echo "  ", str_pad($p->slug, 30),
        " born ", str_pad($p->birthdate ? $p->formatPartialDate("birthdate") : "-", 12),
        " gender ", str_pad($p->gender ?: "-", 8),
        " arrested ", ($c && $c->arrest_date ? $c->arrest_date->format("Y-m-d") : "-"),
        "  days=", ($c ? ($c->imprisoned_for_days ?? "null") : "-"), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "crdl-missing-records" apply_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 135 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
echo
echo "NOTE: new records are created with no sort_order and no photograph."
echo "Every one of the nineteen has an individual Sovereignty Commission"
echo "identification photograph in CRDL; none of them are attached here."
