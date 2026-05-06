#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_nicole_kissane.sh
#
# Adds Nicole Kissane as a new prisoner record. She was the
# co-defendant of Joseph Buddenberg (already in the database) in
# the federal Animal Enterprise Terrorism Act prosecution arising
# from a 2013-2014 cross-country campaign of mink-farm releases
# and fur-industry vandalism.
#
# Source-derived facts:
#   - Arrested July 24, 2015 in Oakland, California after a federal
#     indictment in the Southern District of California.
#   - The indictment alleged that she and Buddenberg released
#     approximately 5,740 mink from fur farms in Idaho, Iowa,
#     Minnesota, Pennsylvania, and elsewhere, and vandalized a fur
#     retailer and the home of its owner in San Diego County.
#   - Pleaded guilty in 2016 to one count of conspiracy to violate
#     the Animal Enterprise Terrorism Act.
#   - Sentenced February 16, 2017 to 21 months federal prison plus
#     3 years supervised release and $423,477 in restitution.
#   - Released from federal custody on July 20, 2018.
#
# Sources: U.S. Attorneys Office for the Southern District of
# California; Earth First! Newswire (July 2018); Washington Post
# (Jan 2017); Wikipedia (Joseph Buddenberg).
set -e

php artisan prisoner:add '{
  "name": "Nicole Kissane",
  "first_name": "Nicole",
  "last_name": "Kissane",
  "description": "Nicole Kissane was the co-defendant of Joseph Buddenberg in the federal Animal Enterprise Terrorism Act prosecution arising from a 2013-2014 cross-country campaign by Animal Liberation Front-affiliated activists in which approximately 5,740 mink were released from fur farms in Idaho, Iowa, Minnesota, Pennsylvania, and elsewhere, and a San Diego County fur retailer plus the home of its owner were vandalized. Kissane was arrested in Oakland on July 24, 2015 and indicted in the U.S. District Court for the Southern District of California. She pleaded guilty in 2016 to a single count of conspiracy to violate AETA and was sentenced February 16, 2017 to 21 months federal prison, 3 years supervised release, and $423,477 in restitution. She was released from federal custody on July 20, 2018.",
  "state": "California",
  "race": "White",
  "gender": "Female",
  "ideologies": ["Animal Rights Activism"],
  "affiliation": ["Animal Liberation Front"],
  "era": "2010s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Federal Bureau of Prisons (location varied)",
      "charges": "Federal conspiracy to violate the Animal Enterprise Terrorism Act (18 U.S.C. ss 43) - co-defendant with Joseph Buddenberg in a 2013-2014 cross-country campaign to release approximately 5,740 mink from fur farms in Idaho, Iowa, Minnesota, Pennsylvania, and elsewhere, and to vandalize a San Diego County fur retailer and the home of its owner.",
      "arrest_date": "2015-07-24",
      "incarceration_date": "2017-02-16",
      "sentenced_date": "2017-02-16",
      "release_date": "2018-07-20",
      "sentence": "21 months federal prison + 3 years supervised release + $423,477 restitution. Sentenced February 16, 2017 in the U.S. District Court for the Southern District of California; released July 20, 2018.",
      "convicted": "Yes - guilty plea, 2016, U.S. District Court for the Southern District of California"
    }
  ]
}' || true

echo
echo "Nicole Kissane add complete."
