#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_cop_city_march5_defendants.sh
# 12 named defendants from the March 5, 2023 mass arrest at the Atlanta Public Safety
# Training Center ("Cop City") construction site in Weelaunee Forest.
# All 23 were charged with domestic terrorism. 22 were denied bond on March 7;
# Thomas Jurgens was the only one to receive bond that day. On March 23, 15 of the
# remaining 22 were released; 8 were held longer. RICO charges against all 61 defendants
# (Sept 2023 indictment) were dismissed December 30, 2025.
set -e

echo "Adding Thomas Jurgens..."
php artisan prisoner:add '{
  "name": "Thomas Jurgens",
  "first_name": "Thomas",
  "last_name": "Jurgens",
  "description": "Thomas Webb Jurgens, 28, is a staff attorney at the Southern Poverty Law Center (UGA Law School, class of 2019) who was serving as a volunteer legal observer at a March 5, 2023 protest at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. He was wearing a bright green/yellow hat — the standard identifier for National Lawyers Guild legal observers — when police arrested him along with 22 other demonstrators. All 23 were charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. At the March 7 bond hearing before DeKalb County Magistrate Judge Anna W. Davis, Jurgens was the only one of the 23 to receive bond — a $5,000 consent bond with conditions including no contact with co-defendants and no presence at the site. He was held approximately two days. In June 2023, DeKalb County District Attorney Sherry Boston moved to drop charges against Jurgens, but Georgia Attorney General Chris Carr overruled her. In August 2023 Jurgens was included in a 61-person RICO indictment brought by Carr. On December 30, 2025, Fulton County Superior Court Judge Kevin Farmer dismissed the RICO charges against all 61 defendants, finding that Carr had lacked constitutional authority to bring racketeering charges without the Governor'\''s written approval.",
  "state": "Georgia",
  "gender": "Male",
  "ideologies": ["Stop Cop City", "Environmental justice", "Civil liberties"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; conspiracy; arrested March 5, 2023 while serving as a National Lawyers Guild legal observer at Cop City construction site protest; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "release_date": "2023-03-07",
      "imprisoned_for_days": 2,
      "convicted": "No — charges dismissed",
      "sentence": "No conviction; 2 days pre-trial detention; $5,000 bond"
    }
  ]
}'

echo "Adding Priscilla Grim..."
php artisan prisoner:add '{
  "name": "Priscilla Grim",
  "first_name": "Priscilla",
  "last_name": "Grim",
  "description": "Priscilla Grim is a Brooklyn-based activist and former Occupy Wall Street organizer who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. She was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at her March 7, 2023 hearing. On March 23, a second bond hearing was held for the remaining defendants; of the 22 denied bond on March 7, 15 were released and 8 remained in custody — Grim was held for at least 18 days total. In August 2023 she was included in a 61-person RICO indictment brought by Georgia Attorney General Chris Carr. On December 30, 2025, Fulton County Superior Court Judge Kevin Farmer dismissed the RICO charges against all 61 defendants.",
  "state": "New York",
  "gender": "Female",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism", "Anti-police brutality"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; conspiracy; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; held at least 18 days pre-trial"
    }
  ]
}'

echo "Adding James Marsicano..."
php artisan prisoner:add '{
  "name": "James Marsicano",
  "first_name": "James",
  "last_name": "Marsicano",
  "description": "James Marsicano is an activist from Charlotte, North Carolina with multiple prior arrests at left-wing protests who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. He was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at his March 7, 2023 hearing. On March 23, a second bond hearing was held; of the 22 denied bond on March 7, 15 were released and 8 remained in custody — Marsicano was held for at least 18 days total. In August 2023 he was included in a 61-person RICO indictment brought by Georgia Attorney General Chris Carr. On December 30, 2025, Fulton County Superior Court Judge Kevin Farmer dismissed the RICO charges against all 61 defendants.",
  "state": "North Carolina",
  "gender": "Male",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism", "Anti-police brutality"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; conspiracy; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; held at least 18 days pre-trial"
    }
  ]
}'

echo "Adding Victor Puertas..."
php artisan prisoner:add '{
  "name": "Victor Puertas",
  "first_name": "Victor",
  "last_name": "Puertas",
  "description": "Victor Puertas is an undocumented immigrant from Mexico living in Utah and an organizer with the Industrial Workers of the World (IWW) with prior protest arrests who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. He was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at his March 7, 2023 hearing. As an undocumented person facing domestic terrorism charges, he faced potential immigration consequences in addition to the criminal case. On March 23, a second bond hearing was held; of the 22 denied bond on March 7, 15 were released and 8 remained in custody — Puertas was held for at least 18 days total. In August 2023 he was included in a 61-person RICO indictment brought by Georgia Attorney General Chris Carr. On December 30, 2025, Fulton County Superior Court Judge Kevin Farmer dismissed the RICO charges against all 61 defendants.",
  "state": "Utah",
  "gender": "Male",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism", "Labor rights", "Immigrant rights"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; conspiracy; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; held at least 18 days pre-trial"
    }
  ]
}'

echo "Adding Max Biederman..."
php artisan prisoner:add '{
  "name": "Max Biederman",
  "first_name": "Max",
  "last_name": "Biederman",
  "description": "Max Biederman is an activist from Arizona who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. He was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at his March 7, 2023 hearing. On March 23, a second bond hearing was held; of the 22 denied bond on March 7, 15 were released and 8 remained in custody — Biederman was held for at least 18 days total. In August 2023 he was included in a 61-person RICO indictment brought by Georgia Attorney General Chris Carr. On December 30, 2025, Fulton County Superior Court Judge Kevin Farmer dismissed the RICO charges against all 61 defendants.",
  "state": "Arizona",
  "gender": "Male",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism", "Anti-police brutality"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; conspiracy; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; held at least 18 days pre-trial"
    }
  ]
}'

echo "Adding Ehret Nottingham..."
php artisan prisoner:add '{
  "name": "Ehret Nottingham",
  "first_name": "Ehret",
  "last_name": "Nottingham",
  "description": "Ehret Nottingham is a self-described community organizer from Fort Collins, Colorado who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. He was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at his March 7, 2023 hearing. On March 23, a second bond hearing was held; of the 22 denied bond on March 7, 15 were released and 8 remained in custody — Nottingham was held for at least 18 days total. In August 2023 he was included in a 61-person RICO indictment brought by Georgia Attorney General Chris Carr. On December 30, 2025, Fulton County Superior Court Judge Kevin Farmer dismissed the RICO charges against all 61 defendants.",
  "state": "Colorado",
  "gender": "Male",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism", "Anti-police brutality"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; conspiracy; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; held at least 18 days pre-trial"
    }
  ]
}'

echo "Adding Amin Chaoui..."
php artisan prisoner:add '{
  "name": "Amin Chaoui",
  "first_name": "Amin",
  "last_name": "Chaoui",
  "description": "Amin Chaoui is an activist from Richmond, Virginia who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. He was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at his March 7, 2023 hearing. On March 23, a second bond hearing was held; of the 22 denied bond on March 7, 15 were released and 8 remained in custody — Chaoui was held for at least 18 days total. In August 2023 he was included in a 61-person RICO indictment brought by Georgia Attorney General Chris Carr. On December 30, 2025, Fulton County Superior Court Judge Kevin Farmer dismissed the RICO charges against all 61 defendants.",
  "state": "Virginia",
  "gender": "Male",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism", "Anti-police brutality"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; conspiracy; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; held at least 18 days pre-trial"
    }
  ]
}'

echo "Adding Alexis Papali..."
php artisan prisoner:add '{
  "name": "Alexis Papali",
  "first_name": "Alexis",
  "last_name": "Papali",
  "description": "Alexis Papali is a non-profit employee from Boston, Massachusetts who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. She was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at her March 7, 2023 hearing. On March 23, a second bond hearing was held; of the 22 denied bond on March 7, 15 were released and 8 remained in custody — Papali was held for at least 18 days total. In August 2023 she was included in a 61-person RICO indictment brought by Georgia Attorney General Chris Carr. On December 30, 2025, Fulton County Superior Court Judge Kevin Farmer dismissed the RICO charges against all 61 defendants.",
  "state": "Massachusetts",
  "gender": "Female",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism", "Anti-police brutality"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; conspiracy; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; held at least 18 days pre-trial"
    }
  ]
}'

echo "Adding Ayla King..."
php artisan prisoner:add '{
  "name": "Ayla King",
  "first_name": "Ayla",
  "last_name": "King",
  "description": "Ayla King is an activist from Worcester, Massachusetts who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. She was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at her March 7, 2023 hearing. On March 23, a second bond hearing was held; of the 22 denied bond on March 7, 15 were released and 8 remained in custody — King was held for at least 18 days total. In August 2023 she was included in a 61-person RICO indictment brought by Georgia Attorney General Chris Carr. On December 30, 2025, Fulton County Superior Court Judge Kevin Farmer dismissed the RICO charges against all 61 defendants.",
  "state": "Massachusetts",
  "gender": "Female",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism", "Anti-police brutality"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; conspiracy; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; held at least 18 days pre-trial"
    }
  ]
}'

echo "Adding Mattia Luini..."
php artisan prisoner:add '{
  "name": "Mattia Luini",
  "first_name": "Mattia",
  "last_name": "Luini",
  "description": "Mattia Luini is a dual Italian-American citizen from New York who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. He was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at his March 7, 2023 hearing. On March 23, a second bond hearing was held; of the 22 denied bond on March 7, 15 were released and 8 remained in custody — Luini was held for at least 18 days total. In August 2023 he was included in a 61-person RICO indictment brought by Georgia Attorney General Chris Carr. On December 30, 2025, Fulton County Superior Court Judge Kevin Farmer dismissed the RICO charges against all 61 defendants.",
  "state": "New York",
  "gender": "Male",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism", "Anti-police brutality"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; conspiracy; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; held at least 18 days pre-trial"
    }
  ]
}'

echo "Adding Frederique Robert-Paul..."
php artisan prisoner:add '{
  "name": "Frederique Robert-Paul",
  "first_name": "Frederique",
  "last_name": "Robert-Paul",
  "description": "Frederique Robert-Paul is a Canadian activist from Montreal with ties to the Montreal Antifa network who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. She was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at her March 7, 2023 hearing. On March 23, a second bond hearing was held; of the 22 denied bond on March 7, 15 were released and 8 remained in custody — Robert-Paul was held for at least 18 days total. In August 2023 she was included in a 61-person RICO indictment brought by Georgia Attorney General Chris Carr. On December 30, 2025, Fulton County Superior Court Judge Kevin Farmer dismissed the RICO charges against all 61 defendants.",
  "state": "Georgia",
  "gender": "Female",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism", "Anti-fascism"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; conspiracy; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; held at least 18 days pre-trial"
    }
  ]
}'

echo "Adding Dimitri Leny..."
php artisan prisoner:add '{
  "name": "Dimitri Leny",
  "first_name": "Dimitri",
  "last_name": "Leny",
  "description": "Dimitri Leny is a French national who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. A non-English speaker, he was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at his March 7, 2023 hearing. On March 23, a second bond hearing was held; of the 22 denied bond on March 7, 15 were released and 8 remained in custody — Leny was held for at least 18 days total. In August 2023 he was included in a 61-person RICO indictment brought by Georgia Attorney General Chris Carr. On December 30, 2025, Fulton County Superior Court Judge Kevin Farmer dismissed the RICO charges against all 61 defendants.",
  "state": "Georgia",
  "gender": "Male",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism", "Anti-police brutality"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; conspiracy; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; held at least 18 days pre-trial"
    }
  ]
}'

echo "All 12 named Cop City March 5, 2023 defendants added."
