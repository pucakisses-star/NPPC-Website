#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_michael_patschak_prisoner.sh
set -e

php artisan prisoner:add '{
  "name": "Michael Patschak",
  "first_name": "Michael",
  "last_name": "Patschak",
  "description": "Michael Patschak is a Maryland activist who participated in the Black Lives Matter protests of 2020. On December 12, 2020, at Black Lives Matter Plaza on 16th Street NW in Washington, D.C., Patschak intervened when police were arresting fellow demonstrators, attempting a de-arrest. During the confrontation he took a police body-worn camera and later returned to the scene, where he was identified and arrested after punching an officer. He was convicted at trial in November 2022 on two counts of assaulting a police officer and one count of robbery. He was sentenced to a suspended term with 15 days to serve, a $200 fine, and one year of probation.",
  "state": "Maryland",
  "gender": "Male",
  "ideologies": ["Black Lives Matter", "Anarchism", "Anti-police brutality"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DC Jail",
      "institution_city": "Washington",
      "institution_state": "District of Columbia",
      "charges": "Two counts of assaulting a police officer; robbery of a body-worn camera during attempted de-arrest at Black Lives Matter Plaza, December 12, 2020",
      "arrest_date": "2020-12-12",
      "imprisoned_for_days": 15,
      "sentence": "All but 15 days suspended; $200 fine; 1 year probation"
    }
  ]
}'
