#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_atlanta_blackbloc_6.sh
# Six activists arrested January 21, 2023 during a downtown Atlanta protest
# following the police killing of Manuel "Tortuguita" Paez Terán on January 18, 2023.
set -e

echo "Adding Francis Carroll..."
php artisan prisoner:add '{
  "name": "Francis Carroll",
  "first_name": "Francis",
  "last_name": "Carroll",
  "description": "Francis Carroll, 22, is a pro-forest, anti-Cop City activist from Kennebunkport, Maine. On January 21, 2023, he was arrested during a protest march through downtown Atlanta called in response to the police killing of Manuel \"Tortuguita\" Paez Terán three days earlier. Demonstrators vandalized the Atlanta Police Foundation building, smashed windows at a Wells Fargo branch, and set an APD patrol car on fire. Carroll was charged with domestic terrorism, first-degree arson, second-degree criminal damage to property, interference with government property, rioting, unlawful assembly, obstruction of a law enforcement officer, and pedestrian in a roadway. He was denied bond on January 23, 2023 and held in Fulton County Jail for several weeks before bond was eventually granted. It was his second domestic terrorism arrest in five weeks, following a December 13, 2022 arrest at the Cop City construction site. In September 2023 he was included in a 61-person RICO indictment brought by the Georgia Attorney General; the RICO charge was dismissed December 30, 2025. His domestic terrorism charge remained pending as of July 2026, with an interlocutory appeal filed in January 2026.",
  "state": "Maine",
  "gender": "Male",
  "ideologies": ["Stop Cop City", "Anti-police brutality", "Anarchism", "Environmental justice"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Fulton County Jail",
      "institution_city": "Atlanta",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; first-degree arson; criminal damage to property; interference with government property; rioting; unlawful assembly; obstruction; arrested January 21, 2023 during downtown Atlanta protest following police killing of Tortuguita",
      "arrest_date": "2023-01-21",
      "incarceration_date": "2023-01-21",
      "sentence": "No conviction; held pre-trial several weeks before bond granted"
    }
  ]
}'

echo "Adding Nadja Geier..."
php artisan prisoner:add '{
  "name": "Nadja Geier",
  "first_name": "Nadja",
  "last_name": "Geier",
  "description": "Nadja Geier, 24, is an activist from Nashville, Tennessee. On January 21, 2023, she was arrested during a protest march through downtown Atlanta called in response to the police killing of Manuel \"Tortuguita\" Paez Terán three days earlier. Demonstrators vandalized the Atlanta Police Foundation building, smashed windows at a Wells Fargo branch, and set an APD patrol car on fire. Geier was charged with domestic terrorism, first-degree arson, second-degree criminal damage to property, interference with government property, rioting, unlawful assembly, obstruction of a law enforcement officer, and pedestrian in a roadway. She was denied bond on January 23, 2023 and held in Fulton County Jail for several weeks before bond was eventually granted. In September 2023 she was included in a 61-person RICO indictment; the RICO charge was dismissed December 30, 2025. Her domestic terrorism and arson charges remained pending as of July 2026.",
  "state": "Tennessee",
  "gender": "Female",
  "ideologies": ["Stop Cop City", "Anti-police brutality", "Anarchism", "Environmental justice"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Fulton County Jail",
      "institution_city": "Atlanta",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; first-degree arson; criminal damage to property; interference with government property; rioting; unlawful assembly; obstruction; arrested January 21, 2023 during downtown Atlanta protest following police killing of Tortuguita",
      "arrest_date": "2023-01-21",
      "incarceration_date": "2023-01-21",
      "sentence": "No conviction; held pre-trial several weeks before bond granted"
    }
  ]
}'

echo "Adding Henri Feola..."
php artisan prisoner:add '{
  "name": "Henri Feola",
  "first_name": "Henri",
  "last_name": "Feola",
  "description": "Henri Feola, 22, is an activist from Spokane, Washington who grew up in Happy Valley, Oregon and earned a degree in archaeological studies with a focus on decolonization from Oberlin College in 2022. On January 21, 2023, Feola was arrested during a protest march through downtown Atlanta called in response to the police killing of Manuel \"Tortuguita\" Paez Terán three days earlier. Demonstrators vandalized the Atlanta Police Foundation building, smashed windows at a Wells Fargo branch, and set an APD patrol car on fire. Feola was charged with domestic terrorism, first-degree arson, second-degree criminal damage to property, interference with government property, rioting, unlawful assembly, obstruction of a law enforcement officer, and pedestrian in a roadway. Feola was denied bond on January 23, 2023 and held in Fulton County Jail for several weeks before bond was eventually granted. In September 2023 Feola was included in a 61-person RICO indictment; the RICO charge was dismissed December 30, 2025. Domestic terrorism and arson charges remained pending as of July 2026.",
  "state": "Washington",
  "gender": "Male",
  "ideologies": ["Stop Cop City", "Anti-police brutality", "Anarchism", "Environmental justice"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Fulton County Jail",
      "institution_city": "Atlanta",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; first-degree arson; criminal damage to property; interference with government property; rioting; unlawful assembly; obstruction; arrested January 21, 2023 during downtown Atlanta protest following police killing of Tortuguita",
      "arrest_date": "2023-01-21",
      "incarceration_date": "2023-01-21",
      "sentence": "No conviction; held pre-trial several weeks before bond granted"
    }
  ]
}'

echo "Adding Ivan Ferguson..."
php artisan prisoner:add '{
  "name": "Ivan Ferguson",
  "first_name": "Ivan",
  "last_name": "Ferguson",
  "description": "Ivan James Ferguson, 23, is a classical clarinetist from Henderson, Nevada who graduated from the San Francisco Conservatory of Music in 2021 and had performed as a soloist with the Henderson Symphony Orchestra and Berkeley Symphony. On January 21, 2023, he was arrested during a protest march through downtown Atlanta called in response to the police killing of Manuel \"Tortuguita\" Paez Terán three days earlier. Demonstrators vandalized the Atlanta Police Foundation building, smashed windows at a Wells Fargo branch, and set an APD patrol car on fire. Ferguson was charged with domestic terrorism, first-degree arson, second-degree criminal damage to property, interference with government property, rioting, unlawful assembly, obstruction of a law enforcement officer, and pedestrian in a roadway. He was held in Fulton County Jail from the time of arrest until bond of over $355,000 was granted on January 23, 2023 — approximately two days. In September 2023 he was included in a 61-person RICO indictment; the RICO charge was dismissed December 30, 2025. His domestic terrorism and arson charges remained pending as of July 2026.",
  "state": "Nevada",
  "gender": "Male",
  "ideologies": ["Stop Cop City", "Anti-police brutality", "Anarchism", "Environmental justice"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Fulton County Jail",
      "institution_city": "Atlanta",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; first-degree arson; criminal damage to property; interference with government property; rioting; unlawful assembly; obstruction; arrested January 21, 2023 during downtown Atlanta protest following police killing of Tortuguita",
      "arrest_date": "2023-01-21",
      "incarceration_date": "2023-01-21",
      "imprisoned_for_days": 2,
      "sentence": "No conviction; held approximately 2 days before bond granted"
    }
  ]
}'

echo "Adding Graham Evatt..."
php artisan prisoner:add '{
  "name": "Graham Evatt",
  "first_name": "Graham",
  "last_name": "Evatt",
  "description": "Graham Evatt, 20, is an activist from Decatur, Georgia — the only Georgia resident among the six people arrested during the January 21, 2023 protest in Atlanta following the police killing of Manuel \"Tortuguita\" Paez Terán. Demonstrators vandalized the Atlanta Police Foundation building, smashed windows at a Wells Fargo branch, and set an APD patrol car on fire. Evatt was charged with domestic terrorism, first-degree arson, second-degree criminal damage to property, interference with government property, rioting, unlawful assembly, obstruction of a law enforcement officer, and pedestrian in a roadway. Prosecutors described him as \"maybe one of the primary organizers since he is local.\" Bond of over $355,000 was granted on January 23, 2023; his parents used college savings and home equity to secure his release. He served five days in Fulton County Jail and contracted scabies during his detention. He was never indicted by a Fulton County grand jury and all charges against him were dismissed. In early 2024, he filed a federal civil rights lawsuit against the City of Atlanta seeking approximately $1 million in damages for wrongful arrest.",
  "state": "Georgia",
  "gender": "Male",
  "ideologies": ["Stop Cop City", "Anti-police brutality", "Anarchism", "Environmental justice"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Fulton County Jail",
      "institution_city": "Atlanta",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; first-degree arson; criminal damage to property; interference with government property; rioting; unlawful assembly; obstruction; all charges later dismissed; arrested January 21, 2023 during downtown Atlanta protest following police killing of Tortuguita",
      "arrest_date": "2023-01-21",
      "incarceration_date": "2023-01-21",
      "release_date": "2023-01-26",
      "imprisoned_for_days": 5,
      "convicted": "No — all charges dismissed",
      "sentence": "No conviction; 5 days pre-trial detention"
    }
  ]
}'

echo "Adding Emily Murphy..."
php artisan prisoner:add '{
  "name": "Emily Murphy",
  "first_name": "Emily",
  "last_name": "Murphy",
  "description": "Emily Murphy, 37, is an activist from Grosse Ile, Michigan. On January 21, 2023, she was arrested during a protest march through downtown Atlanta called in response to the police killing of Manuel \"Tortuguita\" Paez Terán three days earlier. Demonstrators vandalized the Atlanta Police Foundation building, smashed windows at a Wells Fargo branch, and set an APD patrol car on fire. Murphy was the oldest of the six arrested and was charged with domestic terrorism, first-degree arson, second-degree criminal damage to property, interference with government property, rioting, unlawful assembly, obstruction of a law enforcement officer, and pedestrian in a roadway. She was denied bond on January 23, 2023 and held in Fulton County Jail for several weeks before bond was eventually granted. In September 2023 she was included in a 61-person RICO indictment; the RICO charge was dismissed December 30, 2025. Her domestic terrorism and arson charges remained pending as of July 2026.",
  "state": "Michigan",
  "gender": "Female",
  "ideologies": ["Stop Cop City", "Anti-police brutality", "Anarchism", "Environmental justice"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Fulton County Jail",
      "institution_city": "Atlanta",
      "institution_state": "Georgia",
      "charges": "Domestic terrorism; first-degree arson; criminal damage to property; interference with government property; rioting; unlawful assembly; obstruction; arrested January 21, 2023 during downtown Atlanta protest following police killing of Tortuguita",
      "arrest_date": "2023-01-21",
      "incarceration_date": "2023-01-21",
      "sentence": "No conviction; held pre-trial several weeks before bond granted"
    }
  ]
}'

echo "All 6 Atlanta Black Bloc protesters added."
