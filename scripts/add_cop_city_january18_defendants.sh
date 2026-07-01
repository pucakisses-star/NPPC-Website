#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_cop_city_january18_defendants.sh
# Seven Stop Cop City defendants arrested January 18-19, 2023 during raids on
# Weelaunee forest — the same day Georgia State Patrol killed Manuel "Tortuguita"
# Terán. All were charged with domestic terrorism and booked into DeKalb County Jail.
#   Geoffrey Parsons (MD, age 20) — domestic terrorism + aggravated assault on PSO
#   Spencer Bernard Liberto (PA, age 29) — domestic terrorism + aggravated assault on PSO
#   Timothy Murphy (ME, age 25) — arrested Jan 19 after 12-hour treehouse sit
#   Christopher Reynolds (OH, age 31) — domestic terrorism + aggravated assault on officer
#   Matthew Ernest Macar (PA, age 30) — domestic terrorism + aggravated assault on PSO
#   Sarah Wasilewski (PA, age 35) — domestic terrorism + aggravated assault on PSO
#   Teresa Yue Shen (NY, age 31) — domestic terrorism + criminal trespass
set +e

echo "Adding Geoffrey Parsons (Stop Cop City, January 18, 2023)..."
php artisan prisoner:add '{
  "name": "Geoffrey Parsons",
  "first_name": "Geoffrey",
  "last_name": "Parsons",
  "description": "Activist from Maryland arrested January 18, 2023 during law enforcement raids on the Weelaunee forest in Atlanta, Georgia — the same day troopers killed forest defender Manuel \"Tortuguita\" Terán nearby. Age 20 at the time of arrest, he was charged with domestic terrorism and aggravated assault on a public safety officer. He was booked into DeKalb County Jail.",
  "state": "Maryland",
  "gender": "Male",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; aggravated assault on a public safety officer; arrested January 18, 2023 during Weelaunee forest raid",
      "arrest_date": "2023-01-18",
      "incarceration_date": "2023-01-18",
      "sentence": "No conviction; pre-trial detention at DeKalb County Jail"
    }
  ]
}'

echo "Adding Spencer Bernard Liberto (Stop Cop City, January 18, 2023)..."
php artisan prisoner:add '{
  "name": "Spencer Bernard Liberto",
  "first_name": "Spencer",
  "middle_name": "Bernard",
  "last_name": "Liberto",
  "description": "Activist from Pennsylvania arrested January 18, 2023 during law enforcement raids on the Weelaunee forest in Atlanta, Georgia — the same day troopers killed forest defender Manuel \"Tortuguita\" Terán nearby. Age 29 at the time of arrest, he was charged with domestic terrorism and aggravated assault on a public safety officer. He was booked into DeKalb County Jail.",
  "state": "Pennsylvania",
  "gender": "Male",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; aggravated assault on a public safety officer; arrested January 18, 2023 during Weelaunee forest raid",
      "arrest_date": "2023-01-18",
      "incarceration_date": "2023-01-18",
      "sentence": "No conviction; pre-trial detention at DeKalb County Jail"
    }
  ]
}'

echo "Adding Timothy Murphy (Stop Cop City, January 19, 2023)..."
php artisan prisoner:add '{
  "name": "Timothy Murphy",
  "first_name": "Timothy",
  "last_name": "Murphy",
  "description": "Activist from Maine who spent the night of January 18-19, 2023 in a treehouse in the Weelaunee forest in Atlanta, Georgia in a 12-hour sit-in protest. He rappelled down from the treehouse at sunrise on January 19 and was taken into custody. The previous day, Georgia State Patrol had killed fellow forest defender Manuel \"Tortuguita\" Terán in the same forest. Age 25 at the time of arrest, he was charged with domestic terrorism and criminal trespass. He was booked into DeKalb County Jail.",
  "state": "Maine",
  "gender": "Male",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; criminal trespass; arrested January 19, 2023 after 12-hour treehouse sit-in in Weelaunee forest",
      "arrest_date": "2023-01-19",
      "incarceration_date": "2023-01-19",
      "sentence": "No conviction; pre-trial detention at DeKalb County Jail"
    }
  ]
}'

echo "Adding Christopher Reynolds (Stop Cop City, January 18, 2023)..."
php artisan prisoner:add '{
  "name": "Christopher Reynolds",
  "first_name": "Christopher",
  "last_name": "Reynolds",
  "description": "Activist from Ohio arrested January 18, 2023 during law enforcement raids on the Weelaunee forest in Atlanta, Georgia — the same day troopers killed forest defender Manuel \"Tortuguita\" Terán nearby. Age 31 at the time of arrest, he was charged with domestic terrorism and aggravated assault on an officer of the court. He was booked into DeKalb County Jail.",
  "state": "Ohio",
  "gender": "Male",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; aggravated assault on an officer of the court; arrested January 18, 2023 during Weelaunee forest raid",
      "arrest_date": "2023-01-18",
      "incarceration_date": "2023-01-18",
      "sentence": "No conviction; pre-trial detention at DeKalb County Jail"
    }
  ]
}'

echo "Adding Matthew Ernest Macar (Stop Cop City, January 18, 2023)..."
php artisan prisoner:add '{
  "name": "Matthew Ernest Macar",
  "first_name": "Matthew",
  "middle_name": "Ernest",
  "last_name": "Macar",
  "description": "Activist from Pennsylvania arrested January 18, 2023 during law enforcement raids on the Weelaunee forest in Atlanta, Georgia — the same day troopers killed forest defender Manuel \"Tortuguita\" Terán nearby. Age 30 at the time of arrest, he was charged with domestic terrorism and aggravated assault on a public safety officer. He was booked into DeKalb County Jail.",
  "state": "Pennsylvania",
  "gender": "Male",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; aggravated assault on a public safety officer; arrested January 18, 2023 during Weelaunee forest raid",
      "arrest_date": "2023-01-18",
      "incarceration_date": "2023-01-18",
      "sentence": "No conviction; pre-trial detention at DeKalb County Jail"
    }
  ]
}'

echo "Adding Sarah Wasilewski (Stop Cop City, January 18, 2023)..."
php artisan prisoner:add '{
  "name": "Sarah Wasilewski",
  "first_name": "Sarah",
  "last_name": "Wasilewski",
  "description": "Activist from Pennsylvania arrested January 18, 2023 during law enforcement raids on the Weelaunee forest in Atlanta, Georgia — the same day troopers killed forest defender Manuel \"Tortuguita\" Terán nearby. Age 35 at the time of arrest, she was charged with domestic terrorism and aggravated assault on a public safety officer. She was booked into DeKalb County Jail.",
  "state": "Pennsylvania",
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
      "charges": "Domestic terrorism; aggravated assault on a public safety officer; arrested January 18, 2023 during Weelaunee forest raid",
      "arrest_date": "2023-01-18",
      "incarceration_date": "2023-01-18",
      "sentence": "No conviction; pre-trial detention at DeKalb County Jail"
    }
  ]
}'

echo "Adding Teresa Yue Shen (Stop Cop City, January 18, 2023)..."
php artisan prisoner:add '{
  "name": "Teresa Yue Shen",
  "first_name": "Teresa",
  "middle_name": "Yue",
  "last_name": "Shen",
  "description": "Activist from New York arrested January 18, 2023 during law enforcement raids on the Weelaunee forest in Atlanta, Georgia — the same day troopers killed forest defender Manuel \"Tortuguita\" Terán nearby. Age 31 at the time of arrest, she was charged with domestic terrorism and criminal trespass. She was booked into DeKalb County Jail.",
  "state": "New York",
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
      "charges": "Domestic terrorism; criminal trespass; arrested January 18, 2023 during Weelaunee forest raid",
      "arrest_date": "2023-01-18",
      "incarceration_date": "2023-01-18",
      "sentence": "No conviction; pre-trial detention at DeKalb County Jail"
    }
  ]
}'

echo "Done."
