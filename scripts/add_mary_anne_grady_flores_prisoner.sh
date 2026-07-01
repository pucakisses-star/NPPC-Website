#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_mary_anne_grady_flores_prisoner.sh
set -e

php artisan prisoner:add '{
  "name": "Mary Anne Grady Flores",
  "first_name": "Mary Anne",
  "last_name": "Grady Flores",
  "description": "Mary Anne Grady Flores is a grandmother and peace activist from Ithaca, New York who was imprisoned for protesting U.S. drone warfare at Hancock Air National Guard Base in DeWitt, New York — a facility that trains drone pilots and coordinates drone surveillance and strikes in Afghanistan. Prosecutors employed an unusual legal tactic against her and roughly 30 other activists: an Order of Protection ordinarily used in domestic violence cases, which named base commander Col. Earl A. Evans as the designated \"victim\" and barred the activists from approaching the base. Grady Flores was the first protester sentenced under this instrument. In July 2014, Judge David Gideon sentenced her to one year in prison — overriding the prosecution'\''s own recommendation against incarceration, citing her role as primary caregiver for her elderly mother. She was released on bond pending appeal. In January 2016, an appellate court upheld her conviction but reduced the sentence to six months, and she was immediately handcuffed in the courtroom and transferred to Jamesville Correctional Facility in East Syracuse. The New York Court of Appeals subsequently ordered her release on $5,000 bail while her appeal continued.",
  "state": "New York",
  "gender": "Female",
  "ideologies": ["Anti-war", "Anti-drone", "Peace activism", "Civil disobedience"],
  "era": "2010s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Jamesville Correctional Facility",
      "institution_city": "East Syracuse",
      "institution_state": "New York",
      "charges": "Violation of an Order of Protection (naming base commander Col. Earl Evans as \"victim\"); trespassing at Hancock Air National Guard Base during anti-drone protest",
      "arrest_date": "2013-01-01",
      "incarceration_date": "2016-01-19",
      "sentence": "Originally 1 year (2014); reduced to 6 months on appeal (2016); released on bail by NY Court of Appeals pending further appeal"
    }
  ]
}'
