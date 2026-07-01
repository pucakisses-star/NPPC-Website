#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_sam_resto_prisoner.sh
set -e

php artisan prisoner:add '{
  "name": "Sam Resto",
  "first_name": "Sam",
  "last_name": "Resto",
  "description": "Sam Resto is a New York City activist who was arrested on August 13, 2020 in connection with actions during the nationwide uprising that followed the police killing of George Floyd. Prosecutors in the Eastern District of New York charged him with arson, conspiracy, and civil disorder, alleging he firebombed an NYPD vehicle with gasoline-filled bottles on July 28, 2020, vandalized a George Washington statue with red paint, and participated in the City Hall Autonomous Zone protests. The government sought a sentence of 48 to 72 months. Resto was held in pre-trial detention for 26 months before being sentenced to time served plus three years of probation and $14,000 in restitution.",
  "state": "New York",
  "gender": "Male",
  "ideologies": ["Black Lives Matter", "Anarchism", "Anti-police brutality", "Police abolition"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Metropolitan Detention Center",
      "institution_city": "Brooklyn",
      "institution_state": "New York",
      "charges": "Arson, conspiracy, civil disorder; arose from protest activity during 2020 BLM uprising in New York City",
      "arrest_date": "2020-08-13",
      "incarceration_date": "2020-08-13",
      "imprisoned_for_days": 791,
      "sentence": "26 months time served, 3 years probation, $14,000 restitution"
    }
  ]
}'
