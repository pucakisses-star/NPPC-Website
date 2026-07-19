#!/usr/bin/env bash
#
# Adds Susan Marie Parker, the SDS member jailed for civil contempt in
# April 1969 by the Denver federal grand jury investigating the Public
# Service Co. transmission-tower bombings — the sabotage investigation
# at the center of the Cameron Bishop case. Sourced from the AP clipping
# in Colorado State University's Collection on Student Unrest (JSTOR /
# Reveal Digital "Student Activism", community.32541592) and the Tenth
# Circuit's opinion affirming her contempt, In the Matter of the Grand
# Jury and Susan Marie Parker, 411 F.2d 1067 (10th Cir. 1969).
#
# Idempotent: prisoner:add refuses duplicates (|| true).
#
# Run from the repo root:  bash database/data/add-susan-marie-parker.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name":"Susan Marie Parker","first_name":"Susan","middle_name":"Marie","last_name":"Parker","description":"Susan Marie Parker was a 21-year-old member of Students for a Democratic Society from Colorado Springs who was jailed for civil contempt by the federal grand jury in Denver investigating the dynamiting of Public Service Company transmission towers — the sabotage investigation (18 U.S.C. § 2153) at the center of the Cameron Bishop case; press accounts described her as an alleged friend of Bishop, then a fugitive believed to be in Canada. Called before the grand jury on April 1, 1969, she refused to answer on Fifth Amendment grounds, arguing among other things that answers could expose her to prosecution for an extraditable offense in Canada. Judge Alfred Arraj entered an immunity order on April 2; when she still refused, she was found in civil contempt on April 3, 1969 and committed to custody until she testified. Contemporaneous AP coverage preserved in Colorado State University'"'"'s Collection on Student Unrest records her wavering under confinement — at one point announcing through counsel that she would testify \"due to the coercion of incarceration\" and a \"decline in mental health,\" then changing her mind again. The Tenth Circuit affirmed the contempt in June 1969 (In the Matter of the Grand Jury and Susan Marie Parker, 411 F.2d 1067), while holding she had to be released once the grand jury expired. How long she ultimately remained jailed has not been located.","state":"Colorado","gender":"Female","ideologies":["Anti-war"],"affiliation":["Students for a Democratic Society"],"era":"1960s","released":true,"cases":[{"charges":"Civil contempt — refusing, after a grant of immunity, to testify before the federal grand jury investigating the Denver-area Public Service Co. transmission-tower bombings (the Cameron Bishop sabotage investigation); she was never charged with a crime","arrest_date":"1969-04-03","incarceration_date":"1969-04-03","convicted":"No — jailed for civil contempt, never charged with a crime","sentence":"Committed to custody until she purged the contempt by testifying; the Tenth Circuit affirmed but held she had to be released when the grand jury expired (411 F.2d 1067)"}]}' || true

php artisan tinker --execute='
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Susan Marie Parker added."
