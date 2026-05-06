#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_buck_mcqueen.sh
#
# Adds Ernest "Buck" McQueen (birth name Ernest Johnson Jr.), the
# second Vietnam-era Marine Corps deserter named in the June 2006
# Banderas News article alongside Jerry Texiero. McQueen deserted in
# November 1969, citing fears about deployment to Vietnam and the
# moral impact of learning about the 1968 My Lai massacre. He lived
# under his mother's married surname (which appeared on his Social
# Security card) for ~36 years, working as a carpenter in Fort Worth,
# Texas, while concealing his prior military service from two wives
# and two children. Marine investigators located him in January 2006
# after a former brother-in-law disclosed his whereabouts; he was
# discharged from custody without disciplinary action.
#
# Source: https://www.banderasnews.com/0603/nw-desertnam.htm
set -e

php artisan prisoner:add '{
  "name": "Ernest McQueen",
  "first_name": "Ernest",
  "last_name": "McQueen",
  "aka": "Buck McQueen / Ernest Johnson Jr.",
  "description": "Ernest \"Buck\" McQueen, born Ernest Johnson Jr., was a Vietnam-era Marine Corps deserter who walked away from the Marines in November 1969 after learning about the My Lai massacre and fearing deployment to Vietnam. He did not adopt a wholly new identity but lived under his mothers married surname (which appeared on his Social Security card), working as a carpenter in Fort Worth, Texas, for roughly 36 years while concealing his prior military service from two successive wives and two children. In January 2006 the Marine Corps located him after his former brother-in-law disclosed his whereabouts to investigators. McQueen was 55 at the time of his apprehension. Unlike Jerry Texiero, who was held five months at the Pinellas County Jail in Clearwater, Florida, McQueen was discharged from custody without disciplinary action. (Source: Banderas News, June 2006.)",
  "state": "Texas",
  "race": "White",
  "gender": "Male",
  "birthdate": "1950-01-01",
  "ideologies": ["Anti-war", "Conscientious objector"],
  "era": "1960s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "U.S. Marine Corps military custody (Fort Worth, Texas)",
      "institution_state": "Texas",
      "charges": "Desertion (Vietnam era) - Marine private who walked away from his unit in November 1969 citing fears of Vietnam deployment and the moral weight of learning about the 1968 My Lai massacre. Lived under his mothers married surname (per his Social Security card) in Fort Worth, Texas for ~36 years until a former brother-in-law disclosed his whereabouts to Marine investigators.",
      "arrest_date": "2006-01-01",
      "release_date": "2006-01-01",
      "sentence": "Brief military custody following January 2006 apprehension in Fort Worth, Texas.",
      "convicted": "No - discharged without disciplinary action, January 2006"
    }
  ]
}' || true

echo
echo "Buck McQueen add complete."
