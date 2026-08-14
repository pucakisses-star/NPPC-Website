#!/usr/bin/env bash
#
# BATCH 237 -- birth and death dates for 64 records that had neither.
#
#   FIND A GRAVE CANNOT BE READ FROM HERE, and that is the first finding
#   rather than a footnote. Every request to it -- the site root, a search,
#   a memorial page -- returns 403 behind a Cloudflare challenge, for a
#   plain fetch and for the fetching tool alike. No attempt was made to get
#   around it. The dates below come from Wikidata, which carries the Find a
#   Grave memorial ID as a property, so where a memorial exists the
#   identifier travels with the record: 15 of the 64 have one.
#
#   SEARCHING BY NAME IS THE WRONG METHOD, and Mortimer Downing is the
#   proof. His dates were supplied and applied in batch 233 -- 27 August
#   1862 to 22 June 1948 -- and the first Find a Grave result for that name
#   is Rev Mortimer Downing, 1863 to 1942, a Cork-born priest buried in
#   Massachusetts. Same name, adjacent dates, different man. A
#   name-matching sweep would have overwritten a correct record with a
#   wrong one and looked like it was working.
#
#   HOW THESE 64 WERE MATCHED. Every undated name that already carries a
#   photograph -- 1,141 of them -- was matched against Wikidata people,
#   returning 2,986 candidates, an average of two and a half per name. Then
#   three filters: alive and adult in the decade of the case, exactly one
#   such person for the name, and an occupation consistent with the archive
#   account. That left 67. Three were rejected by eye: an Air Force Chief
#   of Chaplains standing in for one of the Episcopal clergymen arrested in
#   Jackson, and two poets sharing a name with a Freedom Rider and a
#   Baltimore defendant.
#
#   A QUARTER OF THESE DATES ARE YEAR-ONLY and are stored that way. Read as
#   plain ISO strings they would have landed as 1 January -- nineteen
#   invented birthdays and seven invented death days on records that had
#   nothing at all. Precision comes from the Wikidata statement, not from
#   the shape of the string.
#
#   NOTHING IS OVERWRITTEN. Each field is written only where the record
#   holds nothing, so the dates curated by hand in the last few batches --
#   Timothy Adams, Mortimer Downing, Carlos Cortéz -- cannot be touched
#   here even where Wikidata disagrees.
#
#   Idempotent: re-running writes nothing.
#
# Run from the repo root, after git pull, after batch 236:
#   bash database/data/run-batch-237.sh

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
echo "  Batch 237 — dates for 64 undated records"
echo "==================================================================="

DATES_CODE='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch237.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$written = 0; $skippedName = 0; $skippedFilled = 0; $missing = 0; $touched = 0;

foreach ($payload["people"] as $e) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $e["slug"])->first();

    if (! $p) { echo "  !! no record at ", $e["slug"], "\n"; $missing++; continue; }

    if ($p->name !== $e["expect_name"]) {
        echo "  !! ", $e["slug"], " now holds ", $p->name, " — skipped\n";
        $skippedName++;

        continue;
    }

    $did = [];

    foreach (["birthdate", "death_date"] as $field) {
        if (! isset($e[$field])) { continue; }

        // Only ever fills a blank. A curated date wins over Wikidata.
        if ($p->{$field}) { $skippedFilled++; continue; }

        $p->{$field} = $e[$field]["value"];
        $p->date_precision = array_merge($p->date_precision ?? [], [$field => $e[$field]["precision"]]);
        $did[] = $field."=".$e[$field]["value"]." (".$e[$field]["precision"].")";
        $written++;
    }

    if (! $did) { continue; }

    $p->save();
    $p->refresh();
    $touched++;

    echo "  ", str_pad($p->name, 28), " ", str_pad($e["era"], 7), " ",
        str_pad($p->birthdate ? $p->formatPartialDate("birthdate") : "—", 18), " ",
        str_pad($p->death_date ? $p->formatPartialDate("death_date") : "—", 18), " ",
        "wd:", $e["qid"], (isset($e["findagrave"]) ? "  fg:".$e["findagrave"] : ""), "\n";
}

echo "\n  records touched            ", $touched, "\n";
echo "  fields written             ", $written, "\n";
echo "  fields left alone (filled) ", $skippedFilled, "\n";
echo "  skipped, name moved        ", $skippedName, "\n";
echo "  slug not found             ", $missing, "\n";

// The gap this batch is a dent in, recounted from the database itself.
$total = Prisoner::withoutGlobalScopes()->count();
$noB = Prisoner::withoutGlobalScopes()->whereNull("birthdate")->count();
$noD = Prisoner::withoutGlobalScopes()->whereNull("death_date")->count();

echo "\n  records in all             ", $total, "\n";
echo "  still without a birthdate  ", $noB, "\n";
echo "  still without a death date ", $noD, "\n";

echo "\n  ", wordwrap($payload["findagrave"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["why_not_by_name"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["how_matched"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["precision_matters"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["only_fills_blanks"], 72, "\n  "), "\n";
echo "\n  ", wordwrap($payload["scale"], 72, "\n  "), "\n";

// A clean re-run touches nothing, so success is a first run that wrote
// something or a later one that had nothing left to write.
if ($skippedName === 0 && $missing === 0) { echo "\nB237-OK\n"; }
'

run_tinker "fill-dates" "B237-OK" "$DATES_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 237 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
