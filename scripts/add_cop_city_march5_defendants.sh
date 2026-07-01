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
  "description": "Priscilla Grim, 49, is a Brooklyn-based activist and former Occupy Wall Street organizer who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. She was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied twice — at the March 7, 2023 hearing and again at the March 23 hearing — and she was held for 31 days in DeKalb County Jail. She documented conditions including temperatures below 60°F, broken toilets, no running water, meals delivered 12–14 hours apart, approximately 3 hours of sunlight over the entire 31 days, and witnessing a suicide attempt. She lost her job at Fordham University as a result of her incarceration and later reported developing PTSD. In August 2023 she was included in a 61-person RICO indictment brought by Georgia Attorney General Chris Carr. On December 30, 2025, Fulton County Superior Court Judge Kevin Farmer dismissed the RICO charges against all 61 defendants. Her underlying domestic terrorism charge in DeKalb County remained pending as of early 2026.",
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
      "charges": "Domestic terrorism; conspiracy; arrested March 5, 2023 at Cop City construction site protest; bond denied twice; RICO charge added September 2023, dismissed December 2025; underlying domestic terrorism charge pending as of early 2026",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "imprisoned_for_days": 31,
      "sentence": "No conviction; 31 days pre-trial detention"
    }
  ]
}'

echo "Adding James Marsicano..."
php artisan prisoner:add '{
  "name": "James Marsicano",
  "first_name": "James",
  "last_name": "Marsicano",
  "description": "James \"Jamie\" Marsicano is a law student at UNC Chapel Hill from Charlotte, North Carolina with multiple prior arrests at left-wing protests who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. He was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at his March 7 hearing; after approximately three weeks he was released on a $25,000 bond with an ankle monitor. He was also barred from the UNC campus after his release. He passed the North Carolina bar exam in 2025 but bar examiners would not consider his license eligibility while the domestic terrorism charge remained pending. In August 2023 he was included in a 61-person RICO indictment brought by Georgia Attorney General Chris Carr. On December 30, 2025, the RICO charges against all 61 defendants were dismissed. On August 14, 2025, a DeKalb County judge dismissed his domestic terrorism charge outright on due process and speedy trial grounds, finding the state had failed to indict him for 29 months.",
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
      "charges": "Domestic terrorism; conspiracy; arrested March 5, 2023 at Cop City construction site protest; domestic terrorism charge dismissed August 14, 2025 on speedy trial grounds after 29 months without indictment; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "imprisoned_for_days": 21,
      "convicted": "No — all charges dismissed",
      "sentence": "No conviction; approximately 3 weeks pre-trial detention; $25,000 bond with ankle monitor"
    }
  ]
}'

echo "Adding Victor Puertas..."
php artisan prisoner:add '{
  "name": "Victor Puertas",
  "first_name": "Victor",
  "last_name": "Puertas",
  "description": "Victor Puertas is an undocumented immigrant from Peru living in Utah and an organizer with the Industrial Workers of the World (IWW) with prior protest arrests who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. He was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at his March 7 hearing and all subsequent hearings. He was held in DeKalb County Jail for the full 90-day statutory maximum permitted without a grand jury indictment under Georgia law — no indictment was ever filed against him. Within 48 hours of reaching that limit and being released from state custody in early June 2023, he was transferred directly to ICE immigration detention at Stewart Detention Center in Lumpkin, Georgia. He remained in ICE custody for approximately eight more months, facing deportation, while supporters organized at freevictor.org. He was released from immigration detention in approximately early 2024 — having spent roughly 10 to 11 months in combined state and federal custody, the longest confirmed pre-trial detention of any Cop City defendant, on charges for which no indictment was ever returned. In August 2023 he was included in a 61-person RICO indictment brought by Georgia AG Chris Carr; the RICO charges were dismissed December 30, 2025.",
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
      "charges": "Domestic terrorism; arrested March 5, 2023 at Cop City construction site protest; bond denied; held 90 days (statutory maximum) without indictment; no charges ever filed; RICO indictment added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "release_date": "2023-06-03",
      "imprisoned_for_days": 90,
      "convicted": "No — no indictment ever returned",
      "sentence": "No conviction; 90 days pre-trial detention (state); approximately 8 additional months ICE immigration detention at Stewart Detention Center, Lumpkin, GA"
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
  "description": "Ayla King is an activist from Worcester, Massachusetts who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. She was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at her March 7, 2023 hearing; she was held for several weeks before being released on a consent bond at the March 23–24 hearing. In August 2023 she was included in a 61-person RICO indictment brought by Georgia Attorney General Chris Carr. She requested a speedy trial in October 2023 and became the first and only Stop Cop City RICO defendant to go to trial. On July 7, 2025, the trial ended in a mistrial after the judge cited errors during jury selection including a closed-courtroom proceeding. The defense filed a double jeopardy motion and the Georgia Court of Appeals was reviewing the case as of mid-2026. On December 30, 2025, the RICO charges against all 61 defendants were dismissed.",
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
      "charges": "Domestic terrorism; conspiracy; RICO; arrested March 5, 2023 at Cop City construction site protest; first Stop Cop City defendant to go to trial (July 2025); mistrial declared; RICO charges dismissed December 2025; double jeopardy appeal pending",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "convicted": "No — mistrial declared July 7, 2025; double jeopardy appeal pending",
      "sentence": "No conviction; several weeks pre-trial detention"
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

echo "All 12 named Cop City March 5, 2023 defendants added (original batch)."
