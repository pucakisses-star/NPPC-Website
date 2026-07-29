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
# AGE 32 IS CURRENT, NOT HISTORICAL. The two BOP fields are read
# separately: "Not in BOP Custody as of" is the date he left Bureau
# custody, while the locator prints the person's age AT THE MOMENT THE
# PAGE IS VIEWED. So the 32 belongs to the present, not to 2023, and it
# is stored in the age column.
#
# It is still a value with a shelf life -- there is no birthdate behind
# it, so nothing recomputes it and it will read 32 after his next
# birthday too. The case text therefore records when the figure was
# taken, so a later reader can tell how stale it is.
#
# NO BIRTHDATE IS SET. Being 32 in mid-2026 places his birth between
# about July 1993 and July 1994 -- a two-year window, which does not fit
# a year-precision field (the Barbara Katt rule). The window is stated
# in the case text instead. A real date of birth would let the age
# maintain itself and should replace this if one turns up.
#
# THE RELEASE DATE IS AUGUST 1, 2023 -- the date the locator gives as
# the point he was no longer in Bureau custody, recorded as his release.
#
# NO ADMISSION DATE IS ADDED, so the imprisonment counter stays empty
# even with the release date in place: the model needs both ends. The
# twenty-four month sentence would put the admission in August 2021 if
# counted backwards, but that is arithmetic rather than evidence, and
# federal terms rarely run their nominal length. Find the admission date
# and the span completes itself.
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
$p->age = 32;
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
$case->sentence = "Twenty-four months in federal prison. Federal Bureau of Prisons register number 48611-480. Released August 1, 2023, the date the BOP inmate locator gives as the point he was no longer in Bureau custody. The locator also gave his age as 32 when checked in July 2026 — it prints a current age rather than an age at release, which places his birth between about July 1993 and July 1994. That two-year window is too wide for a birthdate field, so none is set; the stored age of 32 has no birthdate behind it and will not advance on its own, and should be replaced by a real date of birth if one turns up. NO ADMISSION DATE IS DOCUMENTED, so the imprisonment counter stays empty: counting back twenty-four months from the release would put his admission in August 2021, but that is arithmetic rather than evidence and federal terms rarely run their nominal length. If the admission date is found, the span becomes complete.";
$case->setPartialDate("release_date", 2023, 8, 1);
$case->save();

$p->refresh()->load("cases");
echo "Cyril Lartigue  [{$p->slug}]\n";
echo "  register no. ".($p->inmate_number ?: "-")."   race ".($p->race ?: "-")."   sex ".($p->gender ?: "-")."\n";
echo "  in_custody ".var_export($p->in_custody, true)."   released ".var_export($p->released, true)."  (both were false before)\n";
echo "  age ".($p->age ?? "-")."   (expect 32; no birthdate behind it, so it will not advance on its own)\n";
echo "  photo ".($p->photo ?: "(none)")."   ".filesize(storage_path("app/public/".$p->photo))." bytes\n";
$c = $p->cases->first();
echo "  released ".(optional($c->release_date)->toDateString() ?: "-")."   (expect 2023-08-01)\n";
echo "  incarcerated ".(optional($c->incarceration_date)->toDateString() ?: "(not documented)")."\n";
echo "  days ".($c->imprisoned_for_days ?? "null")."  (expect null — a release date alone cannot make a span)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
