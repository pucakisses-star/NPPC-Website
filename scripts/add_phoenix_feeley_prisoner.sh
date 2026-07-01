#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_phoenix_feeley_prisoner.sh
set -e

php artisan prisoner:add '{
  "name": "Phoenix Feeley",
  "first_name": "Phoenix",
  "last_name": "Feeley",
  "description": "Phoenix Feeley (legal name Jill Coccaro) is a New York City-based artist and fire-eating performer who was arrested twice in June 2008 at Salem Avenue Beach in Spring Lake, New Jersey, for topless sunbathing in violation of a local public nudity ordinance. An advocate for gender equality, Feeley challenged the discriminatory enforcement of laws that permit men — but not women — to bare their torsos in public. She had previously won a 2007 civil rights lawsuit in New York City after a wrongful topless sunbathing arrest there. After the New Jersey Supreme Court declined to hear her appeal, Feeley filed a petition directly to the U.S. Supreme Court. Rather than pay an $861 fine, she chose to serve 12 days in jail in August 2013, during which she conducted a hunger strike to protest conditions at the facility.",
  "state": "New Jersey",
  "gender": "Female",
  "ideologies": ["Gender equality", "Women'\''s rights", "Civil liberties"],
  "era": "2010s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Monmouth County Correctional Institution",
      "institution_city": "Freehold",
      "institution_state": "New Jersey",
      "charges": "Topless sunbathing in violation of Spring Lake municipal public nudity ordinance",
      "arrest_date": "2008-06-01",
      "incarceration_date": "2013-08-09",
      "release_date": "2013-08-21",
      "imprisoned_for_days": 12
    }
  ]
}'
