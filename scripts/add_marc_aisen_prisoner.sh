#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_marc_aisen_prisoner.sh
set -e

php artisan prisoner:add '{
  "name": "Marc Aisen",
  "first_name": "Marc",
  "last_name": "Aisen",
  "description": "Marc William Aisen, 50, a Massachusetts man, was arrested in late 2023 and held in Oakland County Jail in Michigan for approximately 931 days — nearly two and a half years — in pre-trial detention before being sentenced on June 30, 2026. His charges arose from a series of emails he sent beginning in July 2023 to Bloomfield Township treasurer Michael Schostak and roughly 200 of his associates, making accusations about a Boston-based Jewish organization and later about Schostak himself. An Oakland County jury found him guilty in May 2026 of two counts: using a computer to commit a crime and unlawful posting of messages. He was sentenced to 365 days in jail on each count, served concurrently, with full credit for 931 days already served — meaning he was immediately eligible for release. Critics and civil liberties observers noted that the length of his pre-trial detention was grossly disproportionate to the speech-based nature of the charges, and that prosecuting someone for the content of emails and online posts raises serious First Amendment concerns.",
  "state": "Michigan",
  "gender": "Male",
  "ideologies": ["Free speech", "Civil liberties"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Oakland County Jail",
      "institution_city": "Pontiac",
      "institution_state": "Michigan",
      "charges": "Using a computer to commit a crime; unlawful posting of messages",
      "arrest_date": "2023-12-01",
      "incarceration_date": "2023-12-01",
      "release_date": "2026-06-30",
      "imprisoned_for_days": 931,
      "sentence": "365 days time served (concurrent); no additional incarceration"
    }
  ]
}'
