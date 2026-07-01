#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_monica_washington_padula_prisoner.sh
set -e

php artisan prisoner:add '{
  "name": "Monica Washington-Padula",
  "first_name": "Monica",
  "last_name": "Washington-Padula",
  "description": "Monica Washington-Padula is an Afro-Indigenous (Ojibwe) activist and human rights advocate based in Kalamazoo, Michigan. On October 7–8, 2021, she was among advocates present when the Kalamazoo Department of Public Safety swept and evicted residents of the Ampersee homeless encampment. Police body camera footage later showed her being approached from behind and slammed to the ground by officers. She was arrested and held for four days, facing seven charges including four felonies and a misdemeanor — among them felony assault of a police officer. Prosecutors alleged she used pepper spray against officers; Washington-Padula and supporters contended she acted in self-defense. In May 2023 she pleaded no contest to one charge of using pepper spray, with the remaining charges including the felony counts dismissed. On August 21, 2023, she was sentenced to four days in jail with full credit for time already served, plus court costs and attorney fees.",
  "state": "Michigan",
  "gender": "Female",
  "race": "Black",
  "ideologies": ["Housing justice", "Homeless rights", "Anti-police brutality", "Black Lives Matter"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Kalamazoo County Jail",
      "institution_city": "Kalamazoo",
      "institution_state": "Michigan",
      "charges": "Originally 4 felonies and 1 misdemeanor including felony assault of a police officer; pleaded no contest to one count of using pepper spray; remaining charges dismissed",
      "arrest_date": "2021-10-07",
      "incarceration_date": "2021-10-07",
      "release_date": "2021-10-11",
      "imprisoned_for_days": 4,
      "sentence": "4 days time served, court costs and attorney fees"
    }
  ]
}'
