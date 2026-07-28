#!/usr/bin/env bash
#
# Idell Kennedy -- a woman convicted in the federal wartime speech prosecutions
# in Washington State, tried with Frank J. Howenstine, who is already in the
# database (/prisoner/frank-j-howenstine).
#
# DOCUMENTED
#   Sentenced          June 29, 1918 -- eleven years in federal prison and a
#                      $5,000 fine
#   Pending appeal     remained at liberty
#   Ninth Circuit      affirmed the convictions February 2, 1920
#                      rehearing denied April 5, 1920
#   Commutation        President Warren G. Harding, effective
#                      December 25, 1921 -- the same Christmas Day clemency
#                      that freed Eugene Debs and the other wartime speech
#                      prisoners
#   Time served        approximately 19 to 20 months of the eleven years
#
# NOT DOCUMENTED, AND DELIBERATELY NOT ASSERTED
#   Her prison-admission register has not been found, so the date she actually
#   entered custody is unknown. The obvious guess is May 21, 1920 -- the day
#   Howenstine entered McNeil Island after the same appeal -- but that is a
#   reconstruction, and two things make it shakier than it looks:
#
#     * it assumes she surrendered on the same day as a co-defendant, which
#       does not follow from anything on the record; and
#     * McNeil Island was a mens penitentiary. Federal women prisoners of that
#       era were generally held elsewhere under contract, so she almost
#       certainly did not go where Howenstine went, and there is no reason to
#       expect the same admission date.
#
#   So no incarceration date and no institution are recorded. The release date
#   is real -- the commutation took effect December 25, 1921 -- and the roughly
#   19 to 20 months goes in the sentence text, where a reader can see it is an
#   estimate. Her profile shows no imprisonment counter rather than a
#   fabricated one.
#
#   If you would rather record the reconstruction, the last section of this
#   script does it behind a flag:
#
#     ASSUME_MAY_1920=1 bash database/data/add-idell-kennedy.sh
#
#   which sets incarceration to May 21, 1920 and produces a counter of 583 days
#   (1 year, 7 months, 4 days). It is off by default.
#
# Uses prisoner:add, which refuses to create a duplicate, so re-running is
# safe. Run from the repo root:
#   bash database/data/add-idell-kennedy.sh
#   ASSUME_MAY_1920=1 bash database/data/add-idell-kennedy.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{
  "name": "Idell Kennedy",
  "first_name": "Idell",
  "last_name": "Kennedy",
  "gender": "Female",
  "state": "Washington",
  "era": "1910s",
  "ideologies": ["Anti-Militarism"],
  "description": "Idell Kennedy was convicted in the federal wartime speech prosecutions in Washington State and sentenced on June 29, 1918 to eleven years in federal prison and a $5,000 fine — one of the heaviest terms handed down to a woman under the Espionage and Sedition Acts. She remained at liberty while her case was appealed. The Ninth Circuit affirmed the convictions on February 2, 1920 and denied a rehearing on April 5, 1920, after which she entered custody. President Warren G. Harding commuted her sentence effective December 25, 1921, the same Christmas Day clemency that released Eugene Debs and other wartime speech prisoners; she had served approximately nineteen to twenty months of the eleven years. Her prison-admission register has not been located, so the exact date she entered custody, and the institution that held her, remain undocumented. She was tried alongside Frank J. Howenstine, who entered McNeil Island Penitentiary on May 21, 1920 following the same appeal.",
  "in_custody": false,
  "released": true,
  "awaiting_trial": false,
  "cases": [
    {
      "charges": "Federal prosecution under the Espionage Act of 1917 and/or the Sedition Act of 1918.",
      "convicted": "Yes — convicted at trial; the Ninth Circuit affirmed on February 2, 1920 and denied a rehearing on April 5, 1920.",
      "sentenced_date": "1918-06-29",
      "release_date": "1921-12-25",
      "sentence": "Eleven years in federal prison and a $5,000 fine, imposed June 29, 1918. She stayed at liberty through the appeal and entered custody after the Ninth Circuit denied a rehearing in April 1920. President Harding commuted the sentence effective December 25, 1921. She served approximately nineteen to twenty months of the eleven years, but her prison-admission register has not been found, so no incarceration date is recorded here and the profile shows no day count. The co-defendant Frank J. Howenstine entered McNeil Island on May 21, 1920; assuming the same date for her would be a reconstruction, and McNeil was a mens penitentiary, so she was very likely held elsewhere."
    }
  ]
}'

if [ "${ASSUME_MAY_1920:-0}" = "1" ]; then
    echo
    echo "ASSUME_MAY_1920=1 -- recording the reconstructed entry date."
    php artisan tinker --execute='
    use App\Models\Prisoner;

    $p = Prisoner::withoutGlobalScopes()->where("slug", "idell-kennedy")->with("cases")->first();
    if (! $p) { echo "NOT FOUND: idell-kennedy\n"; exit(1); }

    $c = $p->cases->first();
    $c->setPartialDate("incarceration_date", 1920, 5, 21);
    $c->sentence = $c->sentence." RECONSTRUCTED: the incarceration date of May 21, 1920 is not documented for her — it is the date her co-defendant Frank J. Howenstine entered McNeil Island after the same appeal, applied here as an estimate.";
    $c->save();

    echo "incarceration set to 1920-05-21, days = ".($c->imprisoned_for_days ?? "null")."\n";

    \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
    '
fi

echo
echo "Done."
echo "Place the new record:  php artisan prisoners:auto-place-zero-sort"
