#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_cop_city_additional_defendants.sh
# Additional Stop Cop City / Weelaunee Forest defendants across multiple arrest events.
# Part 1: 11 remaining named defendants from March 5, 2023 mass arrest
# Part 2: December 14, 2022 forest encampment arrests (4 new defendants)
# Part 3: April 28, 2023 flyer distribution arrests (3 defendants, ~2.5-3 months detention)
# Part 4: May 31, 2023 Atlanta Solidarity Fund organizers (3 defendants, ~3 days)
set +e  # allow prisoner:add to fail (duplicate) without aborting the script

# ---- PART 1: Remaining March 5, 2023 defendants ----

echo "Adding Luke Harper..."
php artisan prisoner:add '{
  "name": "Luke Harper",
  "first_name": "Luke",
  "last_name": "Harper",
  "description": "Luke Harper, approximately 27, is a freelance copywriter from Florida with no prior criminal record who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. He was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied repeatedly throughout his pre-trial detention. He was held in DeKalb County Jail for the full 90-day statutory maximum allowed under Georgia law without a grand jury indictment and was released on the final day permissible, in early June 2023. He was in the RICO indictment brought by Georgia AG Chris Carr in August 2023; the RICO charges were dismissed December 30, 2025.",
  "state": "Florida",
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
      "charges": "Domestic terrorism; arrested March 5, 2023 at Cop City construction site protest; bond denied; held 90 days (statutory maximum without indictment); RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "release_date": "2023-06-03",
      "imprisoned_for_days": 90,
      "convicted": "No — no indictment returned",
      "sentence": "No conviction; 90 days pre-trial detention"
    }
  ]
}'

echo "Adding Timothy Bilodeau..."
php artisan prisoner:add '{
  "name": "Timothy Bilodeau",
  "first_name": "Timothy",
  "last_name": "Bilodeau",
  "description": "Timothy Bilodeau is an activist from Massachusetts who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. He was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at his March 7, 2023 hearing. He was held in DeKalb County Jail until bond was eventually granted at the March 23–24, 2023 hearing — approximately 19 days. He was included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; the RICO charges were dismissed December 30, 2025.",
  "state": "Massachusetts",
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
      "charges": "Domestic terrorism; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; approximately 19 days pre-trial detention"
    }
  ]
}'

echo "Adding Samuel Ward..."
php artisan prisoner:add '{
  "name": "Samuel Ward",
  "first_name": "Samuel",
  "last_name": "Ward",
  "description": "Samuel Ward is an activist from Arizona who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. He was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at his March 7, 2023 hearing. He was held in DeKalb County Jail until bond was eventually granted at the March 23–24, 2023 hearing — approximately 19 days. He was included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; the RICO charges were dismissed December 30, 2025.",
  "state": "Arizona",
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
      "charges": "Domestic terrorism; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; approximately 19 days pre-trial detention"
    }
  ]
}'

echo "Adding Emma Bogush..."
php artisan prisoner:add '{
  "name": "Emma Bogush",
  "first_name": "Emma",
  "last_name": "Bogush",
  "description": "Emma Bogush is an activist from Connecticut who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. She was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at her March 7, 2023 hearing. She was held in DeKalb County Jail until bond was eventually granted — approximately 19 days or longer. She was included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; the RICO charges were dismissed December 30, 2025.",
  "state": "Connecticut",
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
      "charges": "Domestic terrorism; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; at least 19 days pre-trial detention"
    }
  ]
}'

echo "Adding Kayley Meissner..."
php artisan prisoner:add '{
  "name": "Kayley Meissner",
  "first_name": "Kayley",
  "last_name": "Meissner",
  "description": "Kayley Meissner is an activist from Wisconsin who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. She was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at her March 7, 2023 hearing. She was held in DeKalb County Jail until bond was eventually granted — approximately 19 days or longer. She was included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; the RICO charges were dismissed December 30, 2025.",
  "state": "Wisconsin",
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
      "charges": "Domestic terrorism; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; at least 19 days pre-trial detention"
    }
  ]
}'

echo "Adding Grace Martin..."
php artisan prisoner:add '{
  "name": "Grace Martin",
  "first_name": "Grace",
  "last_name": "Martin",
  "description": "Grace Martin is an activist from Wisconsin who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. She was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at her March 7, 2023 hearing. She was held in DeKalb County Jail until bond was eventually granted — approximately 19 days or longer. She was included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; the RICO charges were dismissed December 30, 2025.",
  "state": "Wisconsin",
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
      "charges": "Domestic terrorism; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; at least 19 days pre-trial detention"
    }
  ]
}'

echo "Adding Jack Beaman..."
php artisan prisoner:add '{
  "name": "Jack Beaman",
  "first_name": "Jack",
  "last_name": "Beaman",
  "description": "Jack Beaman is an activist from Georgia who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. He was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at his March 7, 2023 hearing. He was held in DeKalb County Jail until bond was eventually granted — approximately 19 days or longer. He was included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; the RICO charges were dismissed December 30, 2025.",
  "state": "Georgia",
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
      "charges": "Domestic terrorism; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; at least 19 days pre-trial detention"
    }
  ]
}'

echo "Adding Kamryn Pipes..."
php artisan prisoner:add '{
  "name": "Kamryn Pipes",
  "first_name": "Kamryn",
  "last_name": "Pipes",
  "description": "Kamryn Pipes is an activist from Louisiana who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. She was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at her March 7, 2023 hearing. She was held in DeKalb County Jail until bond was eventually granted — approximately 19 days or longer. She was included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; the RICO charges were dismissed December 30, 2025.",
  "state": "Louisiana",
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
      "charges": "Domestic terrorism; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; at least 19 days pre-trial detention"
    }
  ]
}'

echo "Adding Maggie Gates..."
php artisan prisoner:add '{
  "name": "Maggie Gates",
  "first_name": "Maggie",
  "last_name": "Gates",
  "description": "Maggie Gates is an activist from Indiana who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. She was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at her March 7, 2023 hearing. She was held in DeKalb County Jail until bond was eventually granted — approximately 19 days or longer. She was included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; the RICO charges were dismissed December 30, 2025.",
  "state": "Indiana",
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
      "charges": "Domestic terrorism; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; at least 19 days pre-trial detention"
    }
  ]
}'

echo "Adding Colin Dorsey..."
php artisan prisoner:add '{
  "name": "Colin Dorsey",
  "first_name": "Colin",
  "last_name": "Dorsey",
  "description": "Colin Dorsey is an activist from Maine who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. He was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at his March 7, 2023 hearing. He was held in DeKalb County Jail until bond was eventually granted — approximately 19 days or longer. He was included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; the RICO charges were dismissed December 30, 2025.",
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
      "charges": "Domestic terrorism; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; at least 19 days pre-trial detention"
    }
  ]
}'

echo "Adding Zoe Larmey..."
php artisan prisoner:add '{
  "name": "Zoe Larmey",
  "first_name": "Zoe",
  "last_name": "Larmey",
  "description": "Zoe Larmey is an activist from Tennessee who was arrested on March 5, 2023 at the Atlanta Public Safety Training Center (\"Cop City\") construction site in Weelaunee Forest. She was one of 23 people charged with felony domestic terrorism after protesters threw bricks, Molotov cocktails, and fireworks at officers. Bond was denied at her March 7, 2023 hearing. She was held in DeKalb County Jail until bond was eventually granted — approximately 19 days or longer. She was included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; the RICO charges were dismissed December 30, 2025.",
  "state": "Tennessee",
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
      "charges": "Domestic terrorism; arrested March 5, 2023 at Cop City construction site protest; bond denied March 7; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-03-05",
      "incarceration_date": "2023-03-05",
      "sentence": "No conviction; at least 19 days pre-trial detention"
    }
  ]
}'

# ---- PART 2: December 14, 2022 forest encampment arrests ----
# Note: Francis Carroll is already in the database from the January 21, 2023 Black Bloc 6 arrests.

echo "Adding Nicholas Olson..."
php artisan prisoner:add '{
  "name": "Nicholas Olson",
  "first_name": "Nicholas",
  "last_name": "Olson",
  "description": "Nicholas Olson, 25, is an activist from Nebraska who was among the first five people arrested at the Stop Cop City forest encampment in Weelaunee Forest on December 14, 2022 — one of the earliest domestic terrorism arrests in the Cop City protest movement. He and four co-defendants were charged with domestic terrorism, aggravated assault, criminal trespass, felony obstruction, interference with government property, and possession of tools for the commission of a crime. All five were initially held without bond. DeKalb County Senior Judge Mathew Robins eventually granted bond of between $6,000 and $13,500 with restrictive conditions including an extradition waiver, no-contact with co-defendants, no social media contact with activist groups, and a ban on returning to the training center site. He was later included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; the RICO charges were dismissed December 30, 2025.",
  "state": "Nebraska",
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
      "charges": "Domestic terrorism; aggravated assault; criminal trespass; felony obstruction; interference with government property; possession of tools for crime commission; arrested December 14, 2022 at Weelaunee Forest Cop City encampment; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2022-12-14",
      "incarceration_date": "2022-12-14",
      "sentence": "No conviction; held without bond until bond eventually granted"
    }
  ]
}'

echo "Adding Serena Hertel..."
php artisan prisoner:add '{
  "name": "Serena Hertel",
  "first_name": "Serena",
  "last_name": "Hertel",
  "description": "Serena Hertel, 25, is an activist from California who was among the first five people arrested at the Stop Cop City forest encampment in Weelaunee Forest on December 14, 2022 — one of the earliest domestic terrorism arrests in the Cop City protest movement. She and four co-defendants were charged with domestic terrorism, aggravated assault, criminal trespass, felony obstruction, interference with government property, and possession of tools for the commission of a crime. All five were initially held without bond. DeKalb County Senior Judge Mathew Robins eventually granted bond of between $6,000 and $13,500 with restrictive conditions including an extradition waiver, no-contact with co-defendants, no social media contact with activist groups, and a ban on returning to the training center site. She was later included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; the RICO charges were dismissed December 30, 2025.",
  "state": "California",
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
      "charges": "Domestic terrorism; aggravated assault; criminal trespass; felony obstruction; interference with government property; possession of tools for crime commission; arrested December 14, 2022 at Weelaunee Forest Cop City encampment; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2022-12-14",
      "incarceration_date": "2022-12-14",
      "sentence": "No conviction; held without bond until bond eventually granted"
    }
  ]
}'

echo "Adding Leonard Vioselle..."
php artisan prisoner:add '{
  "name": "Leonard Vioselle",
  "first_name": "Leonard",
  "last_name": "Vioselle",
  "description": "Leonard Vioselle, 20, is an activist from Macon, Georgia who was among the first five people arrested at the Stop Cop City forest encampment in Weelaunee Forest on December 14, 2022 — one of the earliest domestic terrorism arrests in the Cop City protest movement. He and four co-defendants were charged with domestic terrorism, aggravated assault, criminal trespass, felony obstruction, interference with government property, and possession of tools for the commission of a crime. All five were initially held without bond. DeKalb County Senior Judge Mathew Robins eventually granted bond of between $6,000 and $13,500 with restrictive conditions including an extradition waiver, no-contact with co-defendants, no social media contact with activist groups, and a ban on returning to the training center site. He was later included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; the RICO charges were dismissed December 30, 2025.",
  "state": "Georgia",
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
      "charges": "Domestic terrorism; aggravated assault; criminal trespass; felony obstruction; interference with government property; possession of tools for crime commission; arrested December 14, 2022 at Weelaunee Forest Cop City encampment; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2022-12-14",
      "incarceration_date": "2022-12-14",
      "sentence": "No conviction; held without bond until bond eventually granted"
    }
  ]
}'

echo "Adding Arieon Robinson..."
php artisan prisoner:add '{
  "name": "Arieon Robinson",
  "first_name": "Arieon",
  "last_name": "Robinson",
  "description": "Arieon Robinson, 22, is an activist from Wisconsin who was among the first five people arrested at the Stop Cop City forest encampment in Weelaunee Forest on December 14, 2022 — one of the earliest domestic terrorism arrests in the Cop City protest movement. She and four co-defendants were charged with domestic terrorism, aggravated assault, criminal trespass, felony obstruction, interference with government property, and possession of tools for the commission of a crime. All five were initially held without bond. DeKalb County Senior Judge Mathew Robins eventually granted bond of between $6,000 and $13,500 with restrictive conditions including an extradition waiver, no-contact with co-defendants, no social media contact with activist groups, and a ban on returning to the training center site. She was later included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; the RICO charges were dismissed December 30, 2025.",
  "state": "Wisconsin",
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
      "charges": "Domestic terrorism; aggravated assault; criminal trespass; felony obstruction; interference with government property; possession of tools for crime commission; arrested December 14, 2022 at Weelaunee Forest Cop City encampment; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2022-12-14",
      "incarceration_date": "2022-12-14",
      "sentence": "No conviction; held without bond until bond eventually granted"
    }
  ]
}'

# ---- PART 3: April 28, 2023 — Flyer distribution arrests (Bartow County) ----
# Three activists arrested for posting flyers identifying a state trooper involved
# in the killing of Tortuguita. Charged with felony intimidation of a state officer
# and misdemeanor stalking. Held approximately 2.5-3 months total.

echo "Adding Julia Dupuis..."
php artisan prisoner:add '{
  "name": "Julia Dupuis",
  "first_name": "Julia",
  "last_name": "Dupuis",
  "description": "Julia Dupuis, 24, is an activist from Massachusetts who was arrested on April 28, 2023 in Cartersville, Georgia along with two co-defendants for posting flyers that identified the Georgia State Patrol trooper involved in the January 18, 2023 killing of Stop Cop City activist Manuel \"Tortuguita\" Paez Terán. The three were charged with felony intimidation of a state officer (carrying up to 20 years) and misdemeanor stalking. Bond was denied on May 1, 2023, and all three were held in solitary confinement in Bartow County Jail for their first four nights. Dupuis was held for approximately two and a half to three months total before eventually being released. She was later included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; the RICO charges were dismissed December 30, 2025.",
  "state": "Massachusetts",
  "gender": "Female",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism", "Anti-police brutality"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Bartow County Jail",
      "institution_city": "Cartersville",
      "institution_state": "Georgia",
      "charges": "Felony intimidation of a state officer; misdemeanor stalking; arrested April 28, 2023 for distributing flyers identifying trooper involved in Tortuguita killing; bond denied May 1; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-04-28",
      "incarceration_date": "2023-04-28",
      "imprisoned_for_days": 75,
      "convicted": "No — charges dismissed",
      "sentence": "No conviction; approximately 2.5 months pre-trial detention including 4 nights in solitary confinement"
    }
  ]
}'

echo "Adding Charley Tennenbaum..."
php artisan prisoner:add '{
  "name": "Charley Tennenbaum",
  "first_name": "Charley",
  "last_name": "Tennenbaum",
  "description": "Charley Tennenbaum is an activist who was arrested on April 28, 2023 in Cartersville, Georgia along with two co-defendants for posting flyers that identified the Georgia State Patrol trooper involved in the January 18, 2023 killing of Stop Cop City activist Manuel \"Tortuguita\" Paez Terán. The three were charged with felony intimidation of a state officer (carrying up to 20 years) and misdemeanor stalking. Bond was denied on May 1, 2023, and all three were held in solitary confinement in Bartow County Jail for their first four nights. Tennenbaum was held for approximately two and a half to three months — confirmed released by August 2023. He was later included in the 61-person RICO indictment brought by Georgia AG Chris Carr; the RICO charges were dismissed December 30, 2025.",
  "state": "Georgia",
  "gender": "Male",
  "ideologies": ["Stop Cop City", "Environmental justice", "Anarchism", "Anti-police brutality"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Bartow County Jail",
      "institution_city": "Cartersville",
      "institution_state": "Georgia",
      "charges": "Felony intimidation of a state officer; misdemeanor stalking; arrested April 28, 2023 for distributing flyers identifying trooper involved in Tortuguita killing; bond denied May 1; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-04-28",
      "incarceration_date": "2023-04-28",
      "imprisoned_for_days": 75,
      "convicted": "No — charges dismissed",
      "sentence": "No conviction; approximately 2.5 months pre-trial detention including 4 nights in solitary confinement"
    }
  ]
}'

# ---- PART 4: May 31, 2023 — Atlanta Solidarity Fund organizers ----
# Three organizers of the Stop Cop City bail fund arrested on charity fraud
# and money laundering charges. Held approximately 3 days each.

echo "Adding Marlon Kautz..."
php artisan prisoner:add '{
  "name": "Marlon Kautz",
  "first_name": "Marlon",
  "last_name": "Kautz",
  "description": "Marlon Scott Kautz, 39, is an Atlanta-based organizer and co-founder of the Atlanta Solidarity Fund, the community bail fund that posted bond for Stop Cop City protesters. On May 31, 2023, he and two co-organizers were arrested on charges of charity fraud and money laundering — widely understood as retaliation for the Solidarity Fund'\''s bail work on behalf of Cop City demonstrators. Magistrate Judge James Altman granted bond of $15,000 for each of the three defendants, expressing skepticism about the prosecution'\''s case. Kautz was held for approximately three days. The money laundering charges against all three were later dropped. He was included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; the RICO charges were dismissed December 30, 2025.",
  "state": "Georgia",
  "gender": "Male",
  "ideologies": ["Stop Cop City", "Environmental justice", "Mutual aid", "Anarchism"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Charity fraud; money laundering; arrested May 31, 2023 for operating Atlanta Solidarity Fund bail fund for Cop City protesters; charges later dropped; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-05-31",
      "incarceration_date": "2023-05-31",
      "imprisoned_for_days": 3,
      "convicted": "No — charges dropped",
      "sentence": "No conviction; approximately 3 days pre-trial detention; $15,000 bond"
    }
  ]
}'

echo "Adding Adele MacLean..."
php artisan prisoner:add '{
  "name": "Adele MacLean",
  "first_name": "Adele",
  "last_name": "MacLean",
  "description": "Adele MacLean, 42, is an Atlanta-based organizer and co-founder of the Atlanta Solidarity Fund, the community bail fund that posted bond for Stop Cop City protesters. On May 31, 2023, she and two co-organizers were arrested on charges of charity fraud and money laundering — widely understood as retaliation for the Solidarity Fund'\''s bail work. While in custody, MacLean was denied access to her braces and medication, and was placed in solitary confinement. Magistrate Judge James Altman granted bond of $15,000, expressing skepticism about the prosecution'\''s case. She was held for approximately three days. The money laundering charges were later dropped. She was included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; the RICO charges were dismissed December 30, 2025.",
  "state": "Georgia",
  "gender": "Female",
  "ideologies": ["Stop Cop City", "Environmental justice", "Mutual aid", "Anarchism"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Charity fraud; money laundering; arrested May 31, 2023 for operating Atlanta Solidarity Fund bail fund for Cop City protesters; denied medication and braces; placed in solitary confinement; charges later dropped; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-05-31",
      "incarceration_date": "2023-05-31",
      "imprisoned_for_days": 3,
      "convicted": "No — charges dropped",
      "sentence": "No conviction; approximately 3 days pre-trial detention including solitary confinement; $15,000 bond"
    }
  ]
}'

echo "Adding Savannah Patterson..."
php artisan prisoner:add '{
  "name": "Savannah Patterson",
  "first_name": "Savannah",
  "last_name": "Patterson",
  "description": "Savannah Patterson, 30, is an organizer from Savannah, Georgia and co-founder of the Atlanta Solidarity Fund, the community bail fund that posted bond for Stop Cop City protesters. On May 31, 2023, she and two co-organizers were arrested on charges of charity fraud and money laundering — widely understood as retaliation for the Solidarity Fund'\''s bail work. Magistrate Judge James Altman granted bond of $15,000, expressing skepticism about the prosecution'\''s case. She was held for approximately three days. The money laundering charges were later dropped. She was included in the 61-person RICO indictment brought by Georgia AG Chris Carr in August 2023; the RICO charges were dismissed December 30, 2025.",
  "state": "Georgia",
  "gender": "Female",
  "ideologies": ["Stop Cop City", "Environmental justice", "Mutual aid", "Anarchism"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "DeKalb County Jail",
      "institution_city": "Decatur",
      "institution_state": "Georgia",
      "charges": "Charity fraud; money laundering; arrested May 31, 2023 for operating Atlanta Solidarity Fund bail fund for Cop City protesters; charges later dropped; RICO charge added September 2023, dismissed December 2025",
      "arrest_date": "2023-05-31",
      "incarceration_date": "2023-05-31",
      "imprisoned_for_days": 3,
      "convicted": "No — charges dropped",
      "sentence": "No conviction; approximately 3 days pre-trial detention; $15,000 bond"
    }
  ]
}'

echo "All additional Cop City defendants added."
