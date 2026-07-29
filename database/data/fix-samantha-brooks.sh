#!/usr/bin/env bash
#
# Samantha Frances Brooks -- merge a duplicate record, add the BOP
# details, and correct the custody span.
#
# SHE IS IN THE DATABASE TWICE:
#
#   samantha-frances-brooks   sort 466   register number 19550-509,
#                             arrest and incarceration 2020-11-27,
#                             release 2022-05-19, counter 538 days,
#                             thin bio
#   samantha-brooks           sort 579   no register number, arrest
#                             2020-11-28, no custody dates, counter 0,
#                             the fuller and more accurate bio
#
# Same woman: the November 2020 BNSF shunt action near Bellingham,
# Washington with Ellen Reiche, in solidarity with the Wetsuweten
# hereditary chiefs. The BOP register number the site owner supplied,
# 19550-509, matches the first record and confirms the identification.
#
# The FULL-NAME record is kept, because it carries the register number
# and matches how the Bureau of Prisons lists her, and it inherits the
# better bio from the duplicate. The short-name record is deleted.
#
# THE 538-DAY COUNTER WAS WRONG. It ran from the arrest straight
# through to the BOP release, as though she had been held continuously
# for a year and a half. She was not: she pleaded guilty in July 2021
# and was sentenced in October 2021, which she could not have done from
# inside a sentence that had not yet been imposed. Nor does 538 days
# fit a six-month term -- had she really been held from November 2020,
# her credit would have exceeded the sentence long before it was
# passed and she would have walked out of the courtroom in October
# 2021, not the following May.
#
# WHAT IS RECORDED INSTEAD:
#
#   Nov 28, 2020   arrested at the scene with Ellen Reiche. Released
#                  pending trial; the length of that first custody is
#                  not documented, so the arrest date is kept and no
#                  incarceration is claimed from it.
#   Jul 2021       guilty plea
#   Oct 2021       sentenced: six months in federal prison, four months
#                  home confinement, three years supervised release,
#                  200 hours community service
#   May 20, 2022   released -- the date the BOP inmate locator gives as
#                  the point she was no longer in Bureau custody. The
#                  record previously said May 19; the locator date is
#                  used.
#
# THE COUNTER WILL READ EMPTY, not 538. No admission date to Bureau
# custody is documented, and a span needs both ends. Six months counted
# back from the release would put it around November 19, 2021, but that
# is arithmetic rather than evidence. Find the admission date and the
# span completes itself.
#
# HOME CONFINEMENT IS NOT COUNTED as imprisonment, consistent with how
# the database treats it elsewhere; it is described in the case text.
#
# AGE 29 is stored as supplied. The Bureau of Prisons locator prints a
# current age, so it belongs to the present; it also corroborates the
# old bio, which put her at 24 around the December 2020 indictment. No
# birthdate is set: 29 now places her birth between about July 1996 and
# July 1997, a two-year window too wide for a year-precision field, and
# with nothing behind it the stored age will not advance on its own.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-samantha-brooks.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\DB;

$keep = Prisoner::withoutGlobalScopes()->where("slug", "samantha-frances-brooks")->with("cases")->first();
if (! $keep) {
    echo "NOT FOUND: samantha-frances-brooks\n";
    exit(1);
}

// ---- fold in the duplicate ------------------------------------------
$dup = Prisoner::withoutGlobalScopes()->where("slug", "samantha-brooks")->with("cases")->first();
if ($dup) {
    $calendar = DB::table("calendar_entries")->where("prisoner_id", $dup->id)->count();
    $podcasts = DB::table("podcast_episodes")->where("prisoner_id", $dup->id)->count();
    if ($calendar || $podcasts) {
        echo "  WARNING: samantha-brooks has {$calendar} calendar entr(ies) and {$podcasts} podcast episode(s) -- NOT deleted, resolve by hand\n";
    } else {
        $cases = $dup->cases()->count();
        $dup->cases()->delete();
        $dup->delete();
        echo "  deleted duplicate samantha-brooks ({$cases} case(s))\n";
    }
} else {
    echo "  duplicate samantha-brooks already gone\n";
}

// ---- the surviving record -------------------------------------------
$keep->first_name = "Samantha";
$keep->middle_name = "Frances";
$keep->last_name = "Brooks";
$keep->inmate_number = "19550-509";
$keep->race = "White";
$keep->gender = "Female";
$keep->state = "Washington";
$keep->age = 29;
$keep->in_custody = false;
$keep->released = true;
$keep->awaiting_trial = false;
$keep->ideologies = ["Environmental Activism", "Indigenous Sovereignty"];
$keep->description = "Samantha Frances Brooks is a Bellingham, Washington activist who placed a shunting device on BNSF railroad tracks near Bellingham with Ellen Reiche on November 28, 2020, in solidarity with the Wetsuweten hereditary chiefs opposing pipeline construction across British Columbia. A shunt makes a stretch of track read as occupied, which halts trains; the two were arrested at the scene and charged in December 2020 under 18 U.S.C. § 1992, the federal statute covering terrorist attacks and other violence against railroad carriers. Brooks pleaded guilty in July 2021 and was sentenced in October 2021 to six months in federal prison, four months of home confinement, three years of supervised release and 200 hours of community service — a lighter sentence than Reiche received. The Bureau of Prisons, where she was register number 19550-509, records her as out of custody on May 20, 2022.";
$keep->save();

// ---- the case --------------------------------------------------------
$case = $keep->cases->first() ?? $keep->cases()->create([]);
$case->charges = "18 U.S.C. § 1992(a)(5) — terrorist attacks and other violence against railroad carriers, for placing a shunting device on BNSF Railway track near Bellingham, Washington on November 28, 2020 with Ellen Reiche, in solidarity with the Wetsuweten hereditary chiefs opposing pipeline construction in British Columbia.";
$case->convicted = "Yes — guilty plea, July 2021; sentenced October 2021.";
$case->sentence = "Six months in federal prison, four months of home confinement, three years of supervised release and 200 hours of community service, imposed October 2021 — lighter than co-defendant Ellen Reiche received. Bureau of Prisons register number 19550-509; released May 20, 2022, the date the BOP inmate locator gives as the point she was no longer in Bureau custody. NO ADMISSION DATE TO BUREAU CUSTODY IS DOCUMENTED, so the imprisonment counter stays empty: six months counted back from the release would fall around November 19, 2021, but that is arithmetic rather than evidence. The record previously ran a single span from the November 2020 arrest to May 2022 and counted 538 days, which cannot be right — she pleaded guilty in July 2021 and was sentenced that October, and had she been held from the arrest her credit would have exceeded the six-month term before it was even imposed. She was released pending trial after the arrest; the length of that first custody is not documented. The four months of home confinement are not counted as imprisonment.";
$case->setPartialDate("arrest_date", 2020, 11, 28);
$case->incarceration_date = null;
$case->setPartialDate("release_date", 2022, 5, 20);
$case->save();

$keep->refresh()->load("cases");
$c = $keep->cases->first();
echo "\nSamantha Frances Brooks  [{$keep->slug}]\n";
echo "  register no. ".($keep->inmate_number ?: "-")."   age ".($keep->age ?? "-")."   race ".($keep->race ?: "-")."   sex ".($keep->gender ?: "-")."\n";
echo "  arrested     ".(optional($c->arrest_date)->toDateString() ?: "-")."\n";
echo "  incarcerated ".(optional($c->incarceration_date)->toDateString() ?: "(not documented)")."\n";
echo "  released     ".(optional($c->release_date)->toDateString() ?: "-")."   (expect 2022-05-20)\n";
echo "  days ".($c->imprisoned_for_days ?? "null")."  (expect null, was 538)\n";
$left = Prisoner::withoutGlobalScopes()->where("slug", "samantha-brooks")->count();
echo "  duplicate record remaining: {$left}  (expect 0)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
