#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_richard_hunsinger_prisoner.sh
set -e

php artisan prisoner:add '{
  "name": "Richard Hunsinger",
  "first_name": "Richard",
  "last_name": "Hunsinger",
  "description": "Richard Tyler Hunsinger is a Georgia activist who participated in the nationwide uprising following the police killing of George Floyd. On July 25, 2020, during a protest in Atlanta targeting a federal building, Hunsinger threw molotov cocktails and nail bombs into the building. He was identified through DNA evidence left at the scene and arrested in November 2020. Federal prosecutors initially charged him with arson, civil disorder, and destruction of federal property, and sought a terrorism enhancement and a seven-year sentence. In October 2022 he took a plea deal, pleading guilty to civil disorder and property destruction. The federal judge rejected the terrorism enhancement. In February 2023 he was sentenced to 32 months in federal prison.",
  "state": "Georgia",
  "gender": "Male",
  "ideologies": ["Black Lives Matter", "Anti-fascism", "Anarchism"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Federal Bureau of Prisons",
      "institution_state": "Georgia",
      "charges": "Civil disorder; destruction of federal property; guilty plea; threw molotov cocktails and nail bombs at a federal building in Atlanta, July 25, 2020",
      "arrest_date": "2020-11-01",
      "incarceration_date": "2023-02-01",
      "imprisoned_for_days": 975,
      "sentence": "32 months federal prison"
    }
  ]
}'
