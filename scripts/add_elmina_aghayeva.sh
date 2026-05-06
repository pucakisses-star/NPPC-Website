#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_elmina_aghayeva.sh
#
# Adds Elmina "Ellie" Aghayeva, an Azerbaijani national and Columbia
# University student detained by ICE on February 26, 2026 in a
# pre-dawn raid on a Columbia residential building in Manhattan.
# DHS cited a 2016 F-1 student-visa revocation as the basis for
# detention; Columbia and her attorneys disputed the manner of
# entry, alleging that ICE misrepresented itself to the building
# manager and her roommate to gain access. She was released the
# same afternoon, roughly twelve hours later, following the
# intervention of New York City Mayor Zohran Mamdani who raised
# her case directly with President Trump. She remains in removal
# proceedings.
#
# Sources: CNBC; Documented; CBS News New York; Newsweek; VisaVerge.
set -e

php artisan prisoner:add '{
  "name": "Elmina Aghayeva",
  "first_name": "Elmina",
  "last_name": "Aghayeva",
  "aka": "Ellie Aghayeva",
  "description": "Elmina \"Ellie\" Aghayeva is an Azerbaijani national and Columbia University student who was detained by ICE in a pre-dawn raid on a Columbia residential building in Manhattan on February 26, 2026. DHS cited a 2016 F-1 student-visa revocation as the basis for detention; Columbia and her attorneys disputed the manner of entry, alleging that ICE misrepresented itself to the building manager and a roommate in order to gain access to her apartment. She was released the same afternoon, approximately twelve hours after the arrest, following intervention by New York City Mayor Zohran Mamdani who raised her case directly with President Trump in a White House meeting. She remains in removal proceedings.",
  "state": "New York",
  "race": "Asian",
  "gender": "Female",
  "ideologies": ["Palestine solidarity", "Anti-War"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "ICE custody (New York City field office)",
      "institution_state": "New York",
      "charges": "ICE administrative detention - DHS cited a 2016 F-1 student-visa revocation as the basis for arrest. Columbia University and her attorneys alleged the agents misrepresented themselves to the buildings staff and to her roommate to gain access to her apartment. No criminal charges; removal proceedings are pending.",
      "arrest_date": "2026-02-26",
      "incarceration_date": "2026-02-26",
      "release_date": "2026-02-26",
      "sentence": "Held in ICE custody for approximately 12 hours on February 26, 2026 before being released the same afternoon following Mayor Zohran Mamdanis intervention with President Trump. Removal proceedings are pending.",
      "convicted": "No - ICE administrative detention; no criminal charges"
    }
  ]
}' || true

echo
echo "Elmina Aghayeva add complete."
