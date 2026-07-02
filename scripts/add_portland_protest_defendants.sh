#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_portland_protest_defendants.sh
# Three Portland-area activists who served brief jail sentences:
#   Ginovanni Brumbelow — 2 days federal custody; No Kings Day ICE protest, June 14, 2025; felony assault on federal officer
#   Joshua Ames Cartrette — 2 days federal custody; same protest; misdemeanor assault on federal officer
#   Robert "Jonah" Majure — 5 days jail; Patriot Prayer counter-protest Aug 4, 2018; convicted June 7, 2019
set +e

echo "Adding Ginovanni Joseph Brumbelow (No Kings Day, Portland, June 14, 2025)..."
php artisan prisoner:add '{
  "name": "Ginovanni Brumbelow",
  "first_name": "Ginovanni",
  "last_name": "Brumbelow",
  "description": "Ginovanni Joseph Brumbelow, 21, is an activist from Gresham, Oregon. On June 14, 2025 — No Kings Day, chosen to coincide with Donald Trump'\''s birthday — he was arrested at a Portland protest against ICE immigration enforcement. He was charged with felony assault on a federal officer under 18 U.S.C. § 111(a)(1) and held approximately two days in federal custody before release pending trial. His case was among dozens arising from intensified federal prosecution of immigration-related demonstrators during the Trump administration'\''s second term.",
  "state": "Oregon",
  "gender": "Male",
  "ideologies": ["Anti-deportation", "Immigration justice", "Anti-fascism"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Multnomah County Detention Center",
      "institution_city": "Portland",
      "institution_state": "Oregon",
      "charges": "Felony assault on a federal officer (18 U.S.C. § 111(a)(1)); arrested June 14, 2025 at No Kings Day anti-ICE protest in Portland",
      "arrest_date": "2025-06-14",
      "incarceration_date": "2025-06-14",
      "imprisoned_for_days": 2,
      "sentence": "2 days pre-trial detention; charges pending"
    }
  ]
}'

echo ""
echo "Adding Joshua Ames Cartrette (No Kings Day, Portland, June 14, 2025)..."
php artisan prisoner:add '{
  "name": "Joshua Cartrette",
  "first_name": "Joshua",
  "last_name": "Cartrette",
  "description": "Joshua Ames Cartrette, 46, is an activist from Oregon City, Oregon. On June 14, 2025 — No Kings Day, chosen to coincide with Donald Trump'\''s birthday — he was arrested at a Portland protest against ICE immigration enforcement. He was charged with misdemeanor assault on a federal officer under 18 U.S.C. § 111(a) and held approximately two days in federal custody before release pending proceedings. His case was among dozens arising from intensified federal prosecution of immigration-related demonstrators during the Trump administration'\''s second term.",
  "state": "Oregon",
  "gender": "Male",
  "ideologies": ["Anti-deportation", "Immigration justice", "Anti-fascism"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Multnomah County Detention Center",
      "institution_city": "Portland",
      "institution_state": "Oregon",
      "charges": "Misdemeanor assault on a federal officer (18 U.S.C. § 111(a)); arrested June 14, 2025 at No Kings Day anti-ICE protest in Portland",
      "arrest_date": "2025-06-14",
      "incarceration_date": "2025-06-14",
      "imprisoned_for_days": 2,
      "sentence": "2 days pre-trial detention; charges pending"
    }
  ]
}'

echo ""
echo "Adding Robert Majure (Patriot Prayer counter-protest, Portland, 2018-2019)..."
php artisan prisoner:add '{
  "name": "Robert Majure",
  "first_name": "Robert",
  "last_name": "Majure",
  "description": "Robert \"Jonah\" Majure is an anti-fascist activist from Portland, Oregon. On August 4, 2018, he was present at a counter-protest against a Patriot Prayer rally in Portland. During the event he was accused of throwing glitter and a lubricant substance on police officers and was charged with misdemeanor harassment. He was convicted at trial on June 7, 2019 and sentenced to 5 days in jail.",
  "state": "Oregon",
  "gender": "Male",
  "ideologies": ["Anti-fascism", "Anti-racism"],
  "era": "2010s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Multnomah County Detention Center",
      "institution_city": "Portland",
      "institution_state": "Oregon",
      "charges": "Misdemeanor harassment; threw glitter and lubricant on police officers at August 4, 2018 Patriot Prayer counter-protest in Portland",
      "arrest_date": "2018-08-04",
      "incarceration_date": "2019-06-07",
      "release_date": "2019-06-12",
      "imprisoned_for_days": 5,
      "convicted": "Yes — jury verdict",
      "sentence": "5 days jail"
    }
  ]
}'

echo ""
echo "Done."
