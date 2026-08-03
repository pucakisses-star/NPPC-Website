#!/usr/bin/env bash
#
# BATCH 131 -- exiles abroad, per the curator: put these people in
# exile on the assumption they are abroad.
#
#   SIX NEW RECORDS, each created through prisoner:add so its
#   duplicate check and institution matching apply. The JSON is
#   handed to the command through Artisan::call, never through the
#   shell, so apostrophes in the biographies cannot break anything:
#
#     David Hemler         Sweden. Air Force, deserted February 10,
#                          1984 from Augsburg; came forward June 2012
#                          after 28 years under an assumed name.
#     Joshua Key           Canada. Deserted November 2003 after eight
#                          months in Iraq; entered at Toronto March
#                          8, 2005.
#     Kyle Snyder          Canada or unknown. To Canada 2005,
#                          surrendered at Fort Knox October 31, 2006,
#                          absent again, arrested at Nelson BC
#                          February 23, 2007 and released.
#     Dave Smith           Sweden. Army deserter, Swedish citizenship
#                          by 1982.
#     William Schiller     Sweden. Draft resister, former Peace Corps,
#                          Swedish citizenship by 1982.
#     Richard Johnstone    Sweden. Army deserter, formally released by
#                          the Army in 1977 and free to travel to the
#                          United States thereafter; stayed anyway.
#
#   BIRTH DATES are set in a second pass, because prisoner:add cannot
#   express precision. Four of the six are known only as an age
#   reported on a known day, so they are stored as CIRCA — the
#   precision the schema provides for exactly this, rendering "c.
#   1946" rather than asserting a year the source does not support.
#   Only Kyle Snyder has a reported day.
#
#   LEO FREDERICK BURT is updated, not created. He goes into exile
#   from his September 2, 1970 federal indictment at Madison, gains
#   the FBI alias Eugene Donald Fieldston, and his biography gains
#   the wanted-notice detail: Darby, Pennsylvania; a second date of
#   birth used (April 15, 1950); the $150,000 reward; and the ties to
#   New York, Boston and Peterborough, Ontario that the notice lists
#   WITHOUT saying he is in Canada. Country unknown, living status
#   unconfirmed, and the biography says so.
#
#   JEREMY HINZMAN already carried currently_in_exile, so his flags
#   need nothing. His age column holds 2 with no birthdate — his
#   profile reads "Age: 2" — which is cleared. A second defect is
#   REPORTED, not fixed: his case row is attached to FCI Petersburg
#   with its mailing address, a prison he has never been in.
#
#   DANIEL ANDREAS SAN DIEGO is checked and not changed. The curator
#   notes he is a detained extradition defendant rather than an
#   exile; the record already says so.
#
#   ON THE EXILE FLAGS. The curator asked that the four military
#   cases be treated as possible rather than confirmed current
#   exiles. The schema has no such tier — only in_exile and
#   currently_in_exile — so the flags are set as instructed and every
#   biography carries the caveat in the curator's own terms. If a
#   distinct "status unverified" state is wanted, it needs a column.
#
#   Idempotent: prisoner:add refuses an existing name, and every
#   other step checks before writing.
#
# Run from the repo root, after git pull (after batch 130):
#   bash database/data/run-batch-131.sh

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
echo "  Batch 131 — six new exiles abroad, Burt into exile, two checks"
echo "==================================================================="

apply_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use App\Support\ExileDuration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch131.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

// ---------------------------------------------------------------- creates
echo "NEW RECORDS\n";

foreach ($payload["creates"] as $row) {
    $existing = Prisoner::withUnderReview()->where("name", $row["name"])->first();

    if ($existing) {
        echo "  ", $row["name"], " — already exists [", $existing->slug, "], not recreated\n";

        continue;
    }

    // Through Artisan::call, so the JSON never touches the shell.
    $code = Artisan::call("prisoner:add", ["json" => json_encode($row)]);

    $p = Prisoner::withUnderReview()->where("name", $row["name"])->first();

    echo "  ", $row["name"], " — ", ($p ? "created [".$p->slug."]" : "FAILED (exit ".$code.")"), "\n";
}

// ------------------------------------------------------------- precision
echo "\nBIRTH DATES (a second pass: prisoner:add cannot express precision)\n";

foreach ($payload["precision"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->first();

    if (! $p) { echo "  ", $row["slug"], " — NOT FOUND, birth date not set\n"; continue; }

    $circa = (bool) ($row["circa"] ?? false);

    if ($p->birthdate) {
        echo "  ", $row["slug"], " — already ", $p->formatPartialDate("birthdate"), ", left alone\n";

        continue;
    }

    $p->setPartialDate("birthdate", $row["year"], $row["month"] ?? null, $row["day"] ?? null, $circa);
    $p->save();

    echo "  ", $row["slug"], " — ", $p->formatPartialDate("birthdate"),
        " [", $p->datePrecisionFor("birthdate"), "]  (", $row["basis"], ")\n";
}

// --------------------------------------------------------------- updates
echo "\nUPDATES\n";

foreach ($payload["updates"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

    if (! $p) { echo "  ", $row["slug"], " — NOT FOUND\n"; continue; }

    $notes = [];

    if (! empty($row["set_exile"])) {
        if (! $p->in_exile) { $p->in_exile = true; $notes[] = "in_exile=true"; }
        if (! $p->currently_in_exile) { $p->currently_in_exile = true; $notes[] = "currently_in_exile=true"; }
    }

    if (! empty($row["aka"]) && ! $p->aka) { $p->aka = $row["aka"]; $notes[] = "aka=".$row["aka"]; }

    $append = $row["bio_append"] ?? null;
    if ($append && strpos((string) $p->description, "Eugene Donald Fieldston") === false) {
        $p->description = trim((string) $p->description)." ".$append;
        $notes[] = "biography appended";
    }

    if ($notes) { $p->save(); }

    $case = $p->cases->first();

    if ($case && ! empty($row["exile_since"]) && ! $case->in_exile_since) {
        $e = $row["exile_since"];
        $case->setPartialDate("in_exile_since", $e["year"], $e["month"] ?? null, $e["day"] ?? null);
        $case->save();
        $notes[] = "in_exile_since=".$case->formatPartialDate("in_exile_since");
    }

    $p->refresh()->load("cases");

    echo "  ", $row["slug"], " — ", implode("; ", $notes ?: ["already correct"]), "\n";
    echo "      ", $row["note"] ?? "", "\n";
}

// ---------------------------------------------------------------- checks
echo "\nCHECKED, NOT CHANGED\n";

foreach ($payload["verify"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->first();

    if (! $p) { echo "  ", $row["slug"], " — NOT FOUND\n"; continue; }

    $state = "in_custody=".var_export((bool) $p->in_custody, true)
        ." awaiting_trial=".var_export((bool) $p->awaiting_trial, true)
        ." currently_in_exile=".var_export((bool) $p->currently_in_exile, true);

    $ok = $p->in_custody && ! $p->currently_in_exile;

    echo "  ", $row["slug"], " — ", $state, "  ", ($ok ? "MATCHES what the curator expects" : "DOES NOT MATCH — review"), "\n";
}

// --------------------------------------------------------------- flagged
echo "\nFLAGGED\n";

foreach ($payload["flagged"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->with("cases")->first();

    if (! $p) { echo "  ", $row["slug"], " — NOT FOUND\n"; continue; }

    // The one unambiguous part: an age with no birthdate behind it.
    // setAttribute, not $p->attributes[...] — the attributes array is
    // protected, so the subscript form only works from inside the class
    // (which is why the Prisoner::saving closure can use it and this
    // cannot).
    $storedAge = $p->getAttributes()["age"] ?? null;

    if (! $p->birthdate && $storedAge !== null) {
        $p->setAttribute("age", null);
        $p->save();
        echo "  ", $p->slug, " — cleared the stored age (was ", $storedAge, ", no birthdate behind it)\n";
    }

    echo "  ", wordwrap($row["reason"], 88, "\n  "), "\n";
}

// --------------------------------------------------------------- summary
echo "\nSUMMARY\n";

$slugs = array_merge(
    array_column($payload["precision"], "slug"),
    array_column($payload["updates"], "slug"),
);

foreach ($slugs as $slug) {
    $p = Prisoner::withUnderReview()->where("slug", $slug)->with("cases")->first();

    if (! $p) { continue; }

    $days = ExileDuration::totalDays($p->cases);

    echo "  ", str_pad($p->slug, 26), " born ", str_pad($p->birthdate ? $p->formatPartialDate("birthdate") : "-", 14),
        " exile since ", str_pad(ExileDuration::startFor($p->cases)?->format("Y-m-d") ?? "-", 12),
        " ", ($days > 0 ? $days." days" : "no counter"), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "exiles-abroad" apply_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 131 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
echo
echo "NOTE: new records are created with no sort_order. If the site's"
echo "ordering matters for them, database/data/resequence-sort-order.sh"
echo "and list-zero-sort-prisoners.sh are the existing tools."
