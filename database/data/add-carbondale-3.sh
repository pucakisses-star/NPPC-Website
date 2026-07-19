#!/usr/bin/env bash
#
# Adds the Carbondale 3 — James K. Holley (Badatuade Dmowali), Michael
# Johnson (Milton Boyd) and Leonard Thomas, the Southern Illinois
# University students and Black Panthers arrested after the November 12,
# 1970 pre-dawn police siege of the Panther house at 401 North Washington
# Street in Carbondale, Illinois, in which university, state and local
# police fired 778 bullets into the building. All were acquitted on all
# 41 counts by a Jackson County jury in the summer of 1971 (People's Law
# Office defense: Jeff Haas, Michael Deutsch, Flint Taylor, Steve White);
# charges against the three other occupants were dropped. The siege is
# the subject of the documentary "778 Bullets".
#
# Idempotent: prisoner:add refuses duplicates (|| true).
#
# Run from the repo root:  bash database/data/add-carbondale-3.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name":"James K. Holley","first_name":"James","middle_name":"K.","last_name":"Holley","aka":"Badatuade Dmowali","description":"James K. Holley, 20, was one of the Carbondale 3 — Southern Illinois University students and Black Panthers arrested after the November 12, 1970 pre-dawn police siege of the Panther house at 401 North Washington Street in Carbondale, Illinois, in which university, state and local police fired 778 bullets into the building during a nearly three-hour standoff. All six occupants were charged with attempted murder and aggravated battery of a police officer; Holley, Michael Johnson and Leonard Thomas were arrested and prosecuted as the Carbondale 3. In the summer of 1971 a Jackson County jury acquitted them on all 41 counts, with the People'"'"'s Law Office (Jeff Haas, Michael Deutsch, Flint Taylor, Steve White) defending, and charges against the other three were dropped. The siege is the subject of the documentary 778 Bullets. How long the three were held before trial has not been located.","state":"Illinois","race":"Black","gender":"Male","ideologies":["Black liberation"],"affiliation":["Black Panther Party"],"era":"1970s","released":true,"cases":[{"charges":"Attempted murder and aggravated battery of a police officer — 41 counts arising from the November 12, 1970 police siege of the Carbondale Black Panther house","arrest_date":"1970-11-12","convicted":"No — acquitted on all 41 counts by a Jackson County jury, summer 1971"}]}' || true

php artisan prisoner:add '{"name":"Michael Johnson (Carbondale 3)","first_name":"Michael","last_name":"Johnson","aka":"Milton Boyd","description":"Michael Johnson, 22, also known as Milton Boyd, was one of the Carbondale 3 — Southern Illinois University students and Black Panthers arrested after the November 12, 1970 pre-dawn police siege of the Panther house at 401 North Washington Street in Carbondale, Illinois, in which police fired 778 bullets into the building. Charged with attempted murder and aggravated battery of a police officer alongside James K. Holley and Leonard Thomas, he was acquitted on all 41 counts by a Jackson County jury in the summer of 1971, with the People'"'"'s Law Office defending. The siege is the subject of the documentary 778 Bullets. How long the three were held before trial has not been located.","state":"Illinois","race":"Black","gender":"Male","ideologies":["Black liberation"],"affiliation":["Black Panther Party"],"era":"1970s","released":true,"cases":[{"charges":"Attempted murder and aggravated battery of a police officer — 41 counts arising from the November 12, 1970 police siege of the Carbondale Black Panther house","arrest_date":"1970-11-12","convicted":"No — acquitted on all 41 counts by a Jackson County jury, summer 1971"}]}' || true

php artisan prisoner:add '{"name":"Leonard Thomas","first_name":"Leonard","last_name":"Thomas","description":"Leonard Thomas, 20, was one of the Carbondale 3 — Southern Illinois University students and Black Panthers arrested after the November 12, 1970 pre-dawn police siege of the Panther house at 401 North Washington Street in Carbondale, Illinois, in which police fired 778 bullets into the building. Charged with attempted murder and aggravated battery of a police officer alongside James K. Holley and Michael Johnson, he was acquitted on all 41 counts by a Jackson County jury in the summer of 1971, with the People'"'"'s Law Office defending. The siege is the subject of the documentary 778 Bullets. How long the three were held before trial has not been located.","state":"Illinois","race":"Black","gender":"Male","ideologies":["Black liberation"],"affiliation":["Black Panther Party"],"era":"1970s","released":true,"cases":[{"charges":"Attempted murder and aggravated battery of a police officer — 41 counts arising from the November 12, 1970 police siege of the Carbondale Black Panther house","arrest_date":"1970-11-12","convicted":"No — acquitted on all 41 counts by a Jackson County jury, summer 1971"}]}' || true

php artisan tinker --execute='
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Carbondale 3 added."
