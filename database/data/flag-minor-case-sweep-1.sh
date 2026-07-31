#!/usr/bin/env bash
#
# MINOR-CASE SWEEP -- flag every record whose whole documented custody
# comes to ten days or less.
#
# 66 records qualified on the arithmetic. 57 are flagged. 9 are held
# back, and the reason each one is held back is the point of this
# script, so it is worth reading before running it.
#
# WHAT minor_case MEANS HERE. The field has no public effect at all --
# it is an admin filter, exposed in the Filament table and in the API
# payload and rendered nowhere on the site. Its help text reads "brief
# or minor detentions (short holds, dismissed charges, pretrial-only)".
#
# It is a DURATION filter, not a judgment about whether a case mattered.
# That is not an assumption; it is what the existing 214 flagged records
# already say. Doris Stevens is flagged. So are Fannie Lou Hamer, Annell
# Ponder, June Johnson and Lawrence Guyot, who were beaten in the Winona
# jail. So are Wesley Everest and Frank Little, who were lynched, and
# Anna Mae Pictou Aquash and Mark Clark, who were murdered. The curator
# has been using it to mean "this custody was short", and this sweep
# follows that usage rather than inventing a stricter one.
#
# So Allanawissica -- held one day as a hostage at Fort Randolph and
# then murdered -- is flagged, and Himelius Hockett, who spent four days
# and five nights at Kinston without food or water, is flagged. The flag
# measures how long they were held. It does not measure what was done to
# them while they were held.
#
# ------------------------------------------------------------------
# THE NINE HELD BACK, AND WHY
# ------------------------------------------------------------------
#
# None of them is held back on grounds of importance. Every one is held
# back because THE TEN-DAY FIGURE IS NOT REAL -- the record contradicts
# itself, or it stores one term out of several. Flagging those would
# turn a data error into a curatorial judgment and then hide the record
# from the working list that would have caught it.
#
#   lucy-burns           Arrested 1917-09-03 by her own record, but her
#                        custody is stored as 1917-07-06 to 07-08 --
#                        jailed two months BEFORE she was arrested. Her
#                        sentence field says six months and her
#                        biography says she served more prison time than
#                        any other American suffragist.
#
#   dora-kelly-lewis     Same inversion: arrested 1917-11-09, custody
#                        stored from 1917-07-06. Her biography is about
#                        the November 14 Night of Terror.
#
#     Those two dates are not hers and are not his. THE PAIR
#     1917-07-06 -> 1917-07-08 IS STORED IDENTICALLY ON ELEVEN RECORDS,
#     and 1917-07-17 -> 1917-07-19 on thirteen more. They are cohort
#     stamps for the July 4 and July 14 picket groups, applied in bulk.
#     For most of the cohort the stamp is a fair reconstruction of time
#     actually served, which is why the other twenty-two ARE flagged.
#     For these two it collides with an arrest date that postdates it,
#     which is how the stamp becomes visible.
#
#   eleanor-calnan       Stevens records three terms -- three days,
#                        sixty days, eight days -- about seventy-one in
#                        all. One two-day row is stored.
#   lucille-shields      Three terms recorded (3 + 5 + 3, and the last
#                        was for applauding in court), about eleven days.
#                        One two-day row is stored.
#   eunice-dana-brannan  Her own text says pardoned after three days and
#                        then re-imprisoned. The later term is not
#                        recorded, so the total is unknown.
#   hazel-hunkins        Fifteen days recorded, five stored.
#   katharine-fisher     Thirty days at Occoquan recorded, five stored.
#
#   gabriel-meyers       Dated 2005-07-07 to 2005-07-16. Oscar Grant was
#                        killed on January 1, 2009 and the BART board
#                        protest his record describes was that April, so
#                        the year is wrong by four. His own biography
#                        also states a thirty-day jail sentence against
#                        the nine days stored.
#   david-lock           Contradicts itself three ways: the sentence
#                        text says released 1795, the verdict says he
#                        escaped on 1794-11-24, and the biography says
#                        he reached Philadelphia around Christmas 1794 --
#                        which is after the stored release.
#
# Four of the nine (Calnan, Hunkins, Fisher, Burns) would not be minor
# cases even after repair; their real terms run to thirty, sixty and
# a hundred and eighty days. The other five are simply unknown.
#
# ------------------------------------------------------------------
#
# TWO ARE FLAGGED WITH A CAVEAT, recorded here rather than left silent:
#
#   adrian-andrew-martinez  Three days, then a $5,000 bond. His case is
#                           UNRESOLVED and the count carries a six-year
#                           statutory maximum. The flag describes the
#                           detention that happened, not the sentence
#                           that has not. Revisit it if he is convicted.
#   betty-connolly,         Both store one day against the eight days
#   ruth-small              Stevens records. Eight is still under ten,
#                           so the flag is right either way -- but the
#                           stored dates are wrong and should be fixed.
#
# NOTHING ELSE IS TOUCHED. No dates, no prose, no flags but this one.
# The nine held back are printed at the end of the run so they land in
# the terminal output and not only in this header.
#
# Records already flagged were excluded when the list was built, so this
# only ever turns the flag on. Idempotent: a second run reports every
# record as already flagged and writes nothing.
#
# The payload lives in database/data/fixes/minor-case-sweep-1.json.
#
# Run from the repo root:
#   bash database/data/flag-minor-case-sweep-1.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/minor-case-sweep-1.json")), true);

if (! $payload || empty($payload["flag"])) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

$flagged = 0;
$already = 0;
$missing = 0;

foreach ($payload["flag"] as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->first();

    if (! $p) {
        echo "  NOT FOUND: ", $row["slug"], "\n";
        $missing++;
        continue;
    }

    if ($p->minor_case) {
        echo "  ", str_pad($p->slug, 34), " already flagged\n";
        $already++;
        continue;
    }

    $p->minor_case = true;
    $p->save();
    $flagged++;

    echo "  ", str_pad($p->slug, 34), " FLAGGED  (", $row["days"], "d)\n";
}

echo "\n";
echo "Flagged now:      {$flagged}\n";
echo "Already flagged:  {$already}\n";
echo "Slugs not found:  {$missing}\n";

echo "\n";
echo "HELD BACK — the ten-day figure is not real on these, so they are\n";
echo "NOT flagged. They need their dates repaired instead:\n\n";

foreach ($payload["skip"] as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->first();
    $state = $p ? ($p->minor_case ? " [currently FLAGGED — consider clearing]" : "") : " [slug not found]";
    echo "  ", $row["slug"], $state, "\n";
    echo "      ", $row["why"], "\n\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
