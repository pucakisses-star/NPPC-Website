#!/usr/bin/env bash
#
# Merrimack Four -- corrected custody periods and action date.
#
# All four records had a single case running to a release date of
# January 12, 2025, which is the date a 60-day term would have ended if
# it had been served day for day from the sentencing. It was not: all
# four walked out on DECEMBER 20, 2024, thirty-six days in. Each record
# also folded the arrest-and-bail detention into the same row or lost it
# entirely, and Paige Belanger carried an incarceration date of
# November 29, 2023 that belongs to nobody -- it gave her a counter of
# 410 days against the identical 60-day sentence her co-defendants show
# as 60.
#
# TWO CUSTODY PERIODS EACH, so the profile totals add up (the profile
# sums across cases, which is why each period needs its own row):
#
#   SHERGALIS, WALSH, ROSS
#     Nov 20 - Nov 22, 2023   arrested at the Elbit facility on the day
#                             of the action, held about three days
#                             before bail
#     Nov 14 - Dec 20, 2024   the sentence
#                             = 2 + 36 = 38 days
#
#   BELANGER
#     Jan 24, 2024            arrested outside the courthouse and
#                             released on bail the same day -- hours,
#                             not days, so the counter for this row is
#                             zero and that is correct
#     Nov 14 - Dec 20, 2024   the sentence
#                             = 0 + 36 = 36 days
#
#   Belanger was not among those held in November 2023; she was charged
#   later, which is why her first custody comes two months after the
#   others.
#
# THE ACTION WAS NOVEMBER 20, 2023, NOT NOVEMBER 30. All four
# descriptions said the 30th. The New Hampshire Attorney Generals
# sentencing release, the Intercept, Patch and the Union Leader all give
# the 20th, and Sophie Rosss own bio already said November 20 in its
# first sentence while saying November 30 in its second. The date is
# corrected by targeted replacement, so the rest of the prose is
# untouched.
#
# THE SENTENCING DATE IS NOVEMBER 14, 2024, not the 13th the records
# carried -- the Attorney Generals release is dated the 14th and says
# the four "each pled guilty to and were sentenced today".
#
# THE FEBRUARY 22, 2024 ARREST DATE on the three sentence rows is
# cleared. Their arrests are recorded on the new arrest-and-bail rows;
# the February date corresponds to the grand jury stage, not to a
# separate arrest of these three, and is left out rather than
# reclassified on a guess.
#
# Idempotent -- cases are keyed by a marker in the charges text, so a
# second run updates the same two rows instead of adding more. Run from
# the repo root:
#   bash database/data/fix-merrimack-four-custody.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$arrestMarker   = "[arrest-and-bail]";
$sentenceMarker = "[sentence-served]";

$people = [
    "bridget-shergalis" => ["arrest" => [2023, 11, 20], "bail" => [2023, 11, 22]],
    "calla-walsh"       => ["arrest" => [2023, 11, 20], "bail" => [2023, 11, 22]],
    "sophie-ross"       => ["arrest" => [2023, 11, 20], "bail" => [2023, 11, 22]],
    "paige-belanger"    => ["arrest" => [2024, 1, 24],  "bail" => [2024, 1, 24]],
];

foreach ($people as $slug => $cfg) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->with("cases")->first();
    if (! $p) {
        echo "  NOT FOUND: {$slug}\n";
        continue;
    }

    // The action was November 20, 2023. Targeted replacement so the rest
    // of the description is left exactly as written.
    $p->description = str_replace("November 30, 2023", "November 20, 2023", (string) $p->description);
    $p->in_custody = false;
    $p->released = true;
    $p->awaiting_trial = false;
    $p->save();

    // ---- the arrest-and-bail detention -------------------------------
    $arrest = $p->cases->first(fn ($c) => str_contains((string) $c->charges, $arrestMarker))
        ?? $p->cases()->make([]);
    $arrest->prisoner_id = $p->id;
    $sameDay = $cfg["arrest"] === $cfg["bail"];
    $arrest->charges = $arrestMarker." Arrest arising from the November 20, 2023 action at the Elbit Systems of America facility in Merrimack, New Hampshire — riot, criminal mischief, conspiracy to commit criminal mischief and conspiracy to commit burglary as originally charged, later resolved by plea to misdemeanours.";
    $arrest->sentence = $sameDay
        ? "Arrested outside the courthouse on January 24, 2024 and released on bail the same day. Belanger was not among those held at the time of the action in November 2023; she was charged afterwards. The custody here was a matter of hours, so this row contributes nothing to the imprisonment total, which is correct rather than a missing figure."
        : "Arrested at the scene on November 20, 2023 and held roughly three days, until release on bail on November 22, 2023. These are the best-supported dates for the initial detention.";
    $arrest->setPartialDate("arrest_date", ...$cfg["arrest"]);
    $arrest->setPartialDate("incarceration_date", ...$cfg["arrest"]);
    $arrest->setPartialDate("release_date", ...$cfg["bail"]);
    $arrest->save();

    // ---- the sentence ------------------------------------------------
    $sentence = $p->cases->first(fn ($c) => str_contains((string) $c->charges, $sentenceMarker))
        ?? $p->cases->first(fn ($c) => ! str_contains((string) $c->charges, $arrestMarker))
        ?? $p->cases()->make([]);
    $sentence->prisoner_id = $p->id;
    if (! str_contains((string) $sentence->charges, $sentenceMarker)) {
        $sentence->charges = $sentenceMarker." ".trim((string) $sentence->charges);
    }
    $sentence->convicted = "Yes — pleaded guilty to misdemeanour criminal mischief and criminal trespass on November 14, 2024 in Hillsborough County Superior Court-South.";
    $sentence->sentence = "Twelve months, all but sixty days deferred, imposed November 14, 2024. Custody ran from November 14 to December 20, 2024 — thirty-six days, not the full sixty and not the January 12, 2025 release the record previously carried, which was the date a sixty-day term would have ended if served day for day.";
    $sentence->setPartialDate("sentenced_date", 2024, 11, 14);
    $sentence->setPartialDate("incarceration_date", 2024, 11, 14);
    $sentence->setPartialDate("release_date", 2024, 12, 20);
    $sentence->arrest_date = null;
    $sentence->save();
}

echo "\n";
foreach (array_keys($people) as $slug) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->with("cases")->first();
    $total = 0;
    echo str_pad($p->name, 20)."[{$slug}]\n";
    foreach ($p->cases->sortBy("incarceration_date") as $c) {
        $total += (int) $c->imprisoned_for_days;
        echo "   inc ".str_pad(optional($c->incarceration_date)->toDateString() ?: "-", 12)
            ." rel ".str_pad(optional($c->release_date)->toDateString() ?: "-", 12)
            ." days ".($c->imprisoned_for_days ?? "null")."\n";
    }
    echo "   TOTAL {$total} days\n";
    echo "   bio still says November 30: ".(str_contains((string) $p->description, "November 30, 2023") ? "YES -- not fixed" : "no")."\n";
}
echo "\nExpected: Shergalis, Walsh and Ross 38 days each (2 + 36); Belanger 36 (0 + 36).\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
