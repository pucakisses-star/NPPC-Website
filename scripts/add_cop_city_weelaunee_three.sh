#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_cop_city_weelaunee_three.sh
# The "Weelaunee Three" — arrested December 22, 2023 at a weekly Forest Friday
# picket outside the Cop City construction site; held one night in DeKalb County Jail;
# all released December 23, 2023. Offered pretrial diversion March 2025.
set +e

echo "Adding Rukia Rogers (Weelaunee Three, December 22, 2023)..."
php artisan prisoner:add '{
  "name": "Rukia Rogers",
  "first_name": "Rukia",
  "last_name": "Rogers",
  "description": "Activist arrested December 22, 2023 during a \"Forest Friday\" weekly picket outside the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee forest (South River Forest). She was one of three people collectively known as the \"Weelaunee Three\" charged with criminal trespass and held overnight in DeKalb County Jail before being released on December 23, 2023. In March 2025 prosecutors offered all three pretrial diversion requiring a rehabilitation course in exchange for dropping the charges.",
  "gender": "Female",
  "ideologies": ["Stop Cop City", "Environmental justice"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Criminal trespass; arrested December 22, 2023 at weekly Forest Friday picket outside Cop City construction site",
      "arrest_date": "2023-12-22",
      "incarceration_date": "2023-12-22",
      "release_date": "2023-12-23",
      "imprisoned_for_days": 1,
      "sentence": "No conviction; 1 night pre-trial detention; pretrial diversion offered March 2025"
    }
  ]
}'

echo "Adding Noah Grigni (Weelaunee Three, December 22, 2023)..."
php artisan prisoner:add '{
  "name": "Noah Grigni",
  "first_name": "Noah",
  "last_name": "Grigni",
  "description": "Activist arrested December 22, 2023 during a \"Forest Friday\" weekly picket outside the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee forest (South River Forest). One of three people collectively known as the \"Weelaunee Three,\" Grigni was charged with criminal trespass and obstruction of law enforcement. As a transgender person, Grigni was separated from co-defendants, placed in solitary confinement, and misgendered by guards at DeKalb County Jail. All three were released December 23, 2023. In March 2025 prosecutors offered pretrial diversion requiring a rehabilitation course in exchange for dropping the charges; attorneys indicated Grigni and the others might choose trial to vindicate their rights.",
  "gender": "Non-binary",
  "ideologies": ["Stop Cop City", "Environmental justice", "Queer liberation"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Criminal trespass; obstruction of law enforcement; arrested December 22, 2023 at weekly Forest Friday picket outside Cop City construction site; held in solitary confinement",
      "arrest_date": "2023-12-22",
      "incarceration_date": "2023-12-22",
      "release_date": "2023-12-23",
      "imprisoned_for_days": 1,
      "sentence": "No conviction; 1 night in solitary confinement at DeKalb County Jail; pretrial diversion offered March 2025"
    }
  ]
}'

echo "Adding Kyra Hanlon (Weelaunee Three, December 22, 2023)..."
php artisan prisoner:add '{
  "name": "Kyra Hanlon",
  "first_name": "Kyra",
  "last_name": "Hanlon",
  "description": "Activist arrested December 22, 2023 during a \"Forest Friday\" weekly picket outside the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee forest (South River Forest). She was one of three people collectively known as the \"Weelaunee Three\" charged with criminal trespass and held overnight in DeKalb County Jail before being released on December 23, 2023. In March 2025 prosecutors offered all three pretrial diversion requiring a rehabilitation course in exchange for dropping the charges.",
  "gender": "Female",
  "ideologies": ["Stop Cop City", "Environmental justice"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Criminal trespass; arrested December 22, 2023 at weekly Forest Friday picket outside Cop City construction site",
      "arrest_date": "2023-12-22",
      "incarceration_date": "2023-12-22",
      "release_date": "2023-12-23",
      "imprisoned_for_days": 1,
      "sentence": "No conviction; 1 night pre-trial detention; pretrial diversion offered March 2025"
    }
  ]
}'

echo "Done."
