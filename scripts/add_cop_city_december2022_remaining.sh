#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_cop_city_december2022_remaining.sh
# Two Stop Cop City defendants from December 2022 not previously added:
#   Ariel Ebaugh — arrested December 13, 2022; at least 15 days in DeKalb County Jail
#   Vienna Forrest — arrested December 2022; approximately 2 weeks in DeKalb County Jail
set +e

echo "Adding Ariel Ebaugh (Stop Cop City, December 13, 2022)..."
php artisan prisoner:add '{
  "name": "Ariel Ebaugh",
  "first_name": "Ariel",
  "last_name": "Ebaugh",
  "description": "Georgia activist arrested December 13, 2022 during an early law enforcement raid on the Weelaunee forest in Atlanta. Among the first Stop Cop City defendants, she was charged with domestic terrorism, two counts of firearm possession during commission of a felony, aggravated assault, and felony obstruction. Bond was set between $6,000 and $13,500. She remained in DeKalb County Jail for at least 15 days — still confined as of December 28, 2022 — and filed a habeas corpus petition challenging her detention. She was the partner of Manuel \"Tortuguita\" Terán, the forest defender killed by Georgia State Patrol in the same forest on January 18, 2023. She was later included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; those charges were dismissed December 30, 2025.",
  "state": "Georgia",
  "gender": "Female",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; firearm possession during commission of a felony (2 counts); aggravated assault; felony obstruction; arrested December 13, 2022 at Weelaunee forest Cop City encampment; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2022-12-13",
      "incarceration_date": "2022-12-13",
      "imprisoned_for_days": 15,
      "sentenced": "No conviction; at least 15 days pre-trial detention"
    }
  ]
}'

echo "Adding Vienna Forrest (Stop Cop City, December 2022)..."
php artisan prisoner:add '{
  "name": "Vienna Forrest",
  "first_name": "Vienna",
  "last_name": "Forrest",
  "description": "Georgia activist arrested in December 2022 during law enforcement raids on the Weelaunee forest in Atlanta. Charged with domestic terrorism, she was held for approximately two weeks in DeKalb County Jail, where she reported being misgendered by staff and was placed in solitary confinement. She was later included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; those charges were dismissed December 30, 2025.",
  "state": "Georgia",
  "gender": "Female",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; arrested December 2022 at Weelaunee forest Cop City encampment; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2022-12-14",
      "incarceration_date": "2022-12-14",
      "imprisoned_for_days": 14,
      "sentence": "No conviction; approximately 2 weeks pre-trial detention including time in solitary confinement"
    }
  ]
}'

echo "Done."
