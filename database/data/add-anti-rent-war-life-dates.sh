#!/usr/bin/env bash
#
# Anti-Rent War prisoners -- life dates for six of the nineteen.
#
#   Edward O'Connor        1819 – May 4, 1863
#   John Harding Phoenix   July 27, 1819 – December 19, 1908
#   Calvin Madison         January 14, 1817 – March 7, 1896
#   William Brisbane       1811 – 1890
#   Isaac L. Burhans       probable December 5, 1818
#   Zera Preston           probable 1814
#
# Precision is recorded as given: full days where the day is known, year
# precision for Brisbane and Preston, so the profile prints "1811" rather than
# a defaulted January 1.
#
# THE TWO PROBABLES. Burhans and Preston are supplied as probable rather than
# established. They are entered -- a researched probable identification is not
# the same thing as a guess -- but each bio gains a sentence saying the date is
# probable, so the page never presents it as settled. To leave both out
# instead:
#
#   OMIT_PROBABLE=1 bash database/data/add-anti-rent-war-life-dates.sh
#
# JOHN PHOENIX also gains his middle name, Harding. The display name and slug
# stay "John Phoenix"; the profile shows "John Harding Phoenix" in the full-name
# row above the date of birth.
#
# EDWARD O'CONNOR'S PHOTOGRAPH is confirmed as genuinely him. No file changes:
# he already carries one, cropped from a family group -- another adult at the
# right edge and a child at the bottom. The confirmation is recorded here and
# in the run output. If a cleaner scan of the original ever turns up, it is
# worth re-cropping.
#
# Setting a death date makes the profile compute a lifespan and show the
# deceased dagger beside the age.
#
# Idempotent: the bio notes are appended only when absent. Run from the repo
# root:
#   bash database/data/add-anti-rent-war-life-dates.sh
#   OMIT_PROBABLE=1 bash database/data/add-anti-rent-war-life-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

OMIT_PROBABLE="${OMIT_PROBABLE:-0}" php artisan tinker --execute='
use App\Models\Prisoner;

$omitProbable = getenv("OMIT_PROBABLE") === "1";

// slug => [birth y/m/d or null, death y/m/d or null, probable?, bio note or null]
$people = [
    "edward-oconnor" => [
        [1819], [1863, 5, 4], false,
        "He was born in 1819 and died on May 4, 1863, sixteen years after the pardon.",
    ],
    "john-phoenix" => [
        [1819, 7, 27], [1908, 12, 19], false,
        "Born John Harding Phoenix on July 27, 1819, he outlived every other man imprisoned in the case, dying on December 19, 1908 at the age of 89.",
    ],
    "calvin-madison" => [
        [1817, 1, 14], [1896, 3, 7], false,
        "He was born on January 14, 1817 and died on March 7, 1896.",
    ],
    "william-brisbane" => [
        [1811], [1890], false,
        "He was born in 1811 and died in 1890.",
    ],
    "isaac-l-burhans" => [
        [1818, 12, 5], null, true,
        "His date of birth is probably December 5, 1818, though the identification is not established beyond doubt.",
    ],
    "zera-preston" => [
        [1814], null, true,
        "He was probably born in 1814, though the identification is not established beyond doubt.",
    ],
];

$done = 0; $skipped = 0;

foreach ($people as $slug => [$birth, $death, $probable, $note]) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) {
        echo "NOT FOUND: {$slug} -- run php artisan prisoners:expand-anti-rent-war first\n";
        continue;
    }

    if ($probable && $omitProbable) {
        echo "  skipped   ".str_pad($slug, 20)." (OMIT_PROBABLE=1)\n";
        $skipped++;
        continue;
    }

    if ($slug === "john-phoenix") {
        $p->middle_name = "Harding";
    }

    if ($birth) { $p->setPartialDate("birthdate", ...$birth); }
    if ($death) { $p->setPartialDate("death_date", ...$death); }

    if ($note && ! str_contains((string) $p->description, $note)) {
        $p->description = rtrim((string) $p->description)." ".$note;
    }

    $p->save();

    echo "  ".($probable ? "probable  " : "set       ").str_pad($slug, 20)
        ." born ".str_pad((string) ($p->formatPartialDate("birthdate") ?: "-"), 14)
        ." died ".str_pad((string) ($p->formatPartialDate("death_date") ?: "-"), 14)
        ." age ".($p->age ?? "-")."\n";
    $done++;
}

echo "\nEdward O’Connor: photograph confirmed as genuinely him. Already attached,\n";
echo "cropped from a family group -- another adult at the right edge, a child at\n";
echo "the bottom. Worth re-cropping if a cleaner scan of the original surfaces.\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone. {$done} record(s) updated";
echo $skipped ? ", {$skipped} skipped.\n" : ".\n";
'

echo
echo "Done."
