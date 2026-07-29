#!/usr/bin/env bash
#
# Cyril Lartigue -- cropped portrait and the BOP inmate-locator record.
#
# THE PHOTO. His record carried a 960x540 KXAN broadcast still: the
# Travis County booking photograph inset on the station's blue backdrop,
# with the KXAN logo in the corner. The mugshot itself occupies only
# x=260..688 of that frame. It is now cropped to the inset alone, at its
# native 422x534 -- deliberately NOT upscaled to the usual 700px height,
# because enlarging past the source resolution invents detail rather
# than adding it.
#
# THE BOP RECORD as supplied:
#
#   Register Number:            48611-480
#   Age:                        32
#   Race:                       White
#   Sex:                        Male
#   Not in BOP Custody as of:   08/01/2023
#
# The register number goes on the record. Race and sex already matched.
# "Not in BOP Custody as of August 1, 2023" resolves a contradiction the
# record carried -- it was flagged BOTH not in custody AND not released,
# which is not a state a person can be in -- so he is now marked
# released.
#
# THE AGE IS NOT STORED IN THE AGE FIELD, ON PURPOSE. It is a snapshot
# taken at the moment of a lookup, not a fact about him: 32 as of
# August 1, 2023 means he is 34 or 35 now, so writing 32 into the age
# column would put a figure on the profile that is wrong today and gets
# wronger every year. This record already has no birthdate to derive
# from, so nothing would correct it. Instead the snapshot is recorded
# in the case text WITH its as-of date, where it stays true.
#
# NO BIRTHDATE IS SET EITHER. Age 32 on August 1, 2023 places his birth
# between about August 1990 and August 1991 -- a two-year window, which
# does not fit a year-precision field (the Barbara Katt rule). The
# window is stated in the case text instead.
#
# NO CUSTODY DATES ARE ADDED. The twenty-four month sentence is known
# but no admission date is, and the August 1, 2023 locator check is a
# LATEST-POSSIBLE release rather than a release date. The imprisonment
# counter therefore stays empty, which is correct rather than missing.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-cyril-lartigue.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$p = Prisoner::withoutGlobalScopes()->where("slug", "cyril-lartigue")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: cyril-lartigue\n";
    exit(1);
}

$p->first_name = "Cyril";
$p->last_name = "Lartigue";
$p->inmate_number = "48611-480";
$p->race = "White";
$p->gender = "Male";
$p->in_custody = false;
$p->released = true;
$p->awaiting_trial = false;
$p->save();

$src = database_path("data/photos/nonfree/cyril-lartigue.jpg");
if (is_file($src)) {
    File::ensureDirectoryExists(storage_path("app/public/prisoners"));
    $dest = "prisoners/cyril-lartigue.jpg";
    File::copy($src, storage_path("app/public/".$dest), true);
    touch(storage_path("app/public/".$dest));
    $p->photo = $dest;
    $p->save();
} else {
    echo "  photo file missing: {$src}\n";
}

$case = $p->cases->first() ?? $p->cases()->create([]);
$case->charges = "Possession of an unregistered destructive device — a Molotov cocktail made near the Austin Municipal Court during the protest of May 30, 2020.";
$case->sentence = "Twenty-four months in federal prison. Federal Bureau of Prisons register number 48611-480. The BOP inmate locator, checked August 1, 2023, listed him as no longer in Bureau custody and gave his age as 32 — an age recorded at the moment of that lookup rather than a fixed fact, which places his birth somewhere between about August 1990 and August 1991. NO ADMISSION OR RELEASE DATE IS DOCUMENTED: the August 1, 2023 locator check establishes only a latest-possible release, not the day he walked out, so no custody dates are recorded and the imprisonment counter stays empty rather than carrying an invented span.";
$case->save();

$p->refresh()->load("cases");
echo "Cyril Lartigue  [{$p->slug}]\n";
echo "  register no. ".($p->inmate_number ?: "-")."   race ".($p->race ?: "-")."   sex ".($p->gender ?: "-")."\n";
echo "  in_custody ".var_export($p->in_custody, true)."   released ".var_export($p->released, true)."  (both were false before)\n";
echo "  age field ".($p->age ?? "(unset — the BOP 32 was a 2023 snapshot, see the case text)")."\n";
echo "  photo ".($p->photo ?: "(none)")."   ".filesize(storage_path("app/public/".$p->photo))." bytes\n";
echo "  cases ".$p->cases->count()."   days ".($p->cases->first()->imprisoned_for_days ?? "null")."  (expect null — no documented dates)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
