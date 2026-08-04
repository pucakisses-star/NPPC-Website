#!/usr/bin/env bash
#
# BATCH 144 -- scan artifacts from the source book, reported with batch
# 141 and repaired here.
#
#   These biographies were transcribed from a scanned book and the scan
#   came with them. Three classes, all verified against the stored text
#   before being written:
#
#   1. THE THREE RECORDS NAMED IN THE FLAG.
#
#        william-j-dodge   "Dodge amember of the Socialist Labor Party",
#                          "Hemade the remarks", "Thegovernment charged",
#                          and a quotation reading: stated " somehonor "
#        ben-boloff        "he was amember of the Communist Party",
#                          "fifteen monthsbehind bars", "released onbond",
#                          and a page number and section heading on the end
#        t-a-harris        "T. A. Harris amember of the Universal Union",
#                          "beginningon April 17, 1920"
#
#   2. WORDS SPLIT BY END-OF-LINE HYPHENATION. Six patterns across
#      eight records — Leaven- worth, Com- munist, So- cialist, con-
#      scripted, Em- ery, doc- trine. Found by testing every hyphen
#      followed by a space against the archive own vocabulary, so each
#      one is a join the corpus itself confirms.
#
#   3. SECTION HEADINGS CAPTURED ONTO THE END OF A BIOGRAPHY. Six
#      records end in a page number and a running head — "184
#      PENNSYLVANIA ANTI-SEDITION LAW PRISONERS", "163 ILLINOIS
#      CRIMINAL SYNDICALISM LAW PRISONERS" and so on. Two of these
#      belong to records already repaired in batch 141 and will report
#      as already done.
#
#   WHAT IS NOT ATTEMPTED, and why. Words run together — amember,
#   Hemade — cannot be found safely across the archive. Searching for
#   rare long tokens that split into two common words returns 247
#   candidates across 342 records, and most are ordinary words the test
#   happens to fit: unnamed, southeast, farmworkers, storefront,
#   codefendant. No threshold separates those from the real damage
#   without a dictionary. Only the specific joins in the three named
#   records are repaired; the rest wants an eye, not a regular
#   expression, and the script says so at the end.
#
#   Every replacement is a literal string. Each is reported as applied
#   or already done, and one that matches more than once where a single
#   match was expected is skipped rather than applied.
#
#   Idempotent.
#
# Run from the repo root, after git pull (after batch 143):
#   bash database/data/run-batch-144.sh

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
echo "  Batch 144 — repair the scanner artifacts in the biographies"
echo "==================================================================="

apply_batch() {
    php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch144.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$applied = 0;
$alreadyDone = 0;
$skipped = 0;
$touched = [];

$repair = function (Prisoner $p, string $from, string $to, bool $single = true)
    use (&$applied, &$alreadyDone, &$skipped, &$touched) {

    $count = mb_substr_count((string) $p->description, $from);

    if ($count === 0) {
        echo "      already done   ", mb_strimwidth($from, 0, 58, "..."), "\n";
        $alreadyDone++;

        return false;
    }

    if ($single && $count > 1) {
        echo "      SKIPPED, ", $count, " matches   ", mb_strimwidth($from, 0, 48, "..."), "\n";
        $skipped++;

        return false;
    }

    $p->description = str_replace($from, $to, $p->description);
    $applied++;
    $touched[$p->slug] = true;

    echo "      ", $count, "x  ", mb_strimwidth($from, 0, 46, "..."),
        "  ->  ", ($to === "" ? "(removed)" : mb_strimwidth($to, 0, 34, "...")), "\n";

    return true;
};

// ------------------------------------------------- 1. the named records
echo "\n", str_repeat("=", 67), "\n1. THE THREE RECORDS NAMED IN THE FLAG\n";

foreach ($payload["named"] as $row) {
    $p = Prisoner::withUnderReview()->where("slug", $row["slug"])->first();

    echo "\n  ", $row["slug"], "\n";

    if (! $p) { echo "      NOT FOUND\n"; continue; }

    $before = $p->description;

    foreach ($row["replacements"] as $r) { $repair($p, $r["from"], $r["to"]); }

    if ($p->description !== $before) {
        $p->save();
        echo "      saved. now reads:\n";
        echo "      ", wordwrap(mb_strimwidth($p->description, 0, 320, "..."), 78, "\n      "), "\n";
    }
}

// --------------------------------------------------- 2. hyphenation
echo "\n", str_repeat("=", 67), "\n2. WORDS SPLIT BY END-OF-LINE HYPHENATION\n";

foreach ($payload["hyphenation"] as $h) {
    echo "\n  ", $h["from"], "  ->  ", $h["to"], "\n";

    foreach ($h["slugs"] as $slug) {
        $p = Prisoner::withUnderReview()->where("slug", $slug)->first();

        echo "    ", str_pad($slug, 30);

        if (! $p) { echo "  NOT FOUND\n"; continue; }

        echo "\n";

        // Not single-match: a long biography can carry the same split twice.
        if ($repair($p, $h["from"], $h["to"], false)) { $p->save(); }
    }
}

// ------------------------------------------------ 3. captured headings
echo "\n", str_repeat("=", 67), "\n3. SECTION HEADINGS CAPTURED ONTO THE END OF A BIOGRAPHY\n";

foreach ($payload["trailing_headings"] as $t) {
    $p = Prisoner::withUnderReview()->where("slug", $t["slug"])->first();

    echo "\n  ", str_pad($t["slug"], 26), " ", $t["heading"], "\n";

    if (! $p) { echo "      NOT FOUND\n"; continue; }

    if ($repair($p, $t["from"], "")) {
        $p->description = rtrim($p->description);
        $p->save();
        echo "      ends: ...", mb_substr($p->description, -68), "\n";
    }
}

// ------------------------------------------------------------- summary
echo "\n", str_repeat("=", 67), "\n";
echo "  replacements applied:  ", $applied, "\n";
echo "  already done:          ", $alreadyDone, "\n";
echo "  skipped:               ", $skipped, "\n";
echo "  records changed:       ", count($touched), "\n";

if ($touched) { echo "    ", implode(", ", array_keys($touched)), "\n"; }

echo "\nNOT ATTEMPTED — ", $payload["not_attempted"]["class"], "\n";
echo "  ", wordwrap($payload["not_attempted"]["reason"], 84, "\n  "), "\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "repair-scan-artifacts" apply_batch

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 144 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
echo
echo "Two entries will report as already done: pete-muselin lost doc- trine"
echo "and tom-zima lost his captured heading in batch 141."
