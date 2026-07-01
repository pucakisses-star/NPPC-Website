#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_missing_prisoners_fix.sh
# Adds 5 prisoners missed due to set -e abort in prior scripts:
# - 4 Hancock anti-drone protesters (Brian Hynes, James Ricks, Mark Scibilia-Carver, Patricia Weiland)
# - 1 FACE Act defendant (Joan Andrews Bell)
set +e

echo "Adding Brian Hynes..."
php artisan prisoner:add '{
  "name": "Brian Hynes",
  "first_name": "Brian",
  "last_name": "Hynes",
  "description": "Brian Hynes is a peace activist from the Bronx, New York who participated in the October 2012 protest at Hancock Field Air National Guard Base near Syracuse, where activists trespassed to protest the U.S. drone warfare program. Hancock trains pilots for MQ-9 Reaper drones that conduct lethal strikes abroad. Hynes was convicted of disorderly conduct and sentenced to 15 days in Onondaga County Jail in 2013.",
  "state": "New York",
  "gender": "Male",
  "ideologies": ["Anti-war", "Anti-drone", "Peace activism", "Civil disobedience"],
  "era": "2010s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Onondaga County Correctional Facility",
      "institution_city": "Jamesville",
      "institution_state": "New York",
      "charges": "Disorderly conduct; trespassed at Hancock Field Air National Guard Base during anti-drone protest, October 2012",
      "arrest_date": "2012-10-01",
      "imprisoned_for_days": 15
    }
  ]
}'

echo "Adding James Ricks..."
php artisan prisoner:add '{
  "name": "James Ricks",
  "first_name": "James",
  "last_name": "Ricks",
  "description": "James Ricks is a peace activist from Ithaca, New York who participated in the October 2012 protest at Hancock Field Air National Guard Base near Syracuse, where activists trespassed to protest the U.S. drone warfare program. Hancock trains MQ-9 Reaper drone pilots responsible for lethal strikes in Afghanistan and other countries. Ricks was convicted of disorderly conduct and sentenced to 15 days in Onondaga County Jail in 2013.",
  "state": "New York",
  "gender": "Male",
  "ideologies": ["Anti-war", "Anti-drone", "Peace activism", "Civil disobedience"],
  "era": "2010s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Onondaga County Correctional Facility",
      "institution_city": "Jamesville",
      "institution_state": "New York",
      "charges": "Disorderly conduct; trespassed at Hancock Field Air National Guard Base during anti-drone protest, October 2012",
      "arrest_date": "2012-10-01",
      "imprisoned_for_days": 15
    }
  ]
}'

echo "Adding Mark Scibilia-Carver..."
php artisan prisoner:add '{
  "name": "Mark Scibilia-Carver",
  "first_name": "Mark",
  "last_name": "Scibilia-Carver",
  "description": "Mark Scibilia-Carver is a peace activist from Trumansburg, New York who participated in the October 2012 protest at Hancock Field Air National Guard Base near Syracuse, where activists trespassed to protest the U.S. drone warfare program. Hancock trains pilots for MQ-9 Reaper drones that carry out lethal strikes in Afghanistan and other countries. Scibilia-Carver was convicted of disorderly conduct and sentenced to 15 days in Onondaga County Jail in 2013.",
  "state": "New York",
  "gender": "Male",
  "ideologies": ["Anti-war", "Anti-drone", "Peace activism", "Civil disobedience"],
  "era": "2010s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Onondaga County Correctional Facility",
      "institution_city": "Jamesville",
      "institution_state": "New York",
      "charges": "Disorderly conduct; trespassed at Hancock Field Air National Guard Base during anti-drone protest, October 2012",
      "arrest_date": "2012-10-01",
      "imprisoned_for_days": 15
    }
  ]
}'

echo "Adding Patricia Weiland..."
php artisan prisoner:add '{
  "name": "Patricia Weiland",
  "first_name": "Patricia",
  "last_name": "Weiland",
  "description": "Patricia Weiland is a peace activist from Northampton, Massachusetts who participated in the October 2012 protest at Hancock Field Air National Guard Base near Syracuse, New York, where activists trespassed to protest the U.S. drone warfare program. Hancock trains MQ-9 Reaper drone pilots responsible for lethal strikes in Afghanistan and other countries. Weiland was convicted of disorderly conduct and sentenced to 15 days in Onondaga County Jail in 2013.",
  "state": "Massachusetts",
  "gender": "Female",
  "ideologies": ["Anti-war", "Anti-drone", "Peace activism", "Civil disobedience"],
  "era": "2010s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Onondaga County Correctional Facility",
      "institution_city": "Jamesville",
      "institution_state": "New York",
      "charges": "Disorderly conduct; trespassed at Hancock Field Air National Guard Base during anti-drone protest, October 2012",
      "arrest_date": "2012-10-01",
      "imprisoned_for_days": 15
    }
  ]
}'

echo "Adding Joan Andrews Bell..."
php artisan prisoner:add '{
  "name": "Joan Andrews Bell",
  "first_name": "Joan",
  "last_name": "Andrews Bell",
  "description": "Joan Andrews Bell, 76, is a veteran pro-life activist from Montague, New Jersey who has been engaged in anti-abortion civil disobedience since Roe v. Wade was decided in 1973. A longtime participant in Operation Rescue and similar groups, she has prior convictions in Baltimore, St. Louis, Pittsburgh, and Florida related to abortion clinic protests spanning decades. On October 22, 2020, she participated in the blockade of the Washington Surgi-Clinic in Washington, D.C. Convicted of violating the Freedom of Access to Clinic Entrances (FACE) Act and conspiracy against rights, she was sentenced in May 2024 by Judge Colleen Kollar-Kotelly to 27 months in federal prison plus three years of supervised release.",
  "state": "New Jersey",
  "gender": "Female",
  "ideologies": ["Pro-life", "Anti-abortion", "Civil disobedience", "Catholic social teaching"],
  "era": "2020s",
  "in_custody": false,
  "released": false,
  "cases": [
    {
      "institution_name": "Federal Bureau of Prisons",
      "institution_state": "District of Columbia",
      "charges": "Violation of the FACE Act; conspiracy against rights; blockade of Washington Surgi-Clinic, October 22, 2020",
      "arrest_date": "2020-10-22",
      "incarceration_date": "2024-05-14",
      "sentence": "27 months federal prison plus 3 years supervised release"
    }
  ]
}'

echo "All 5 missing prisoners added."
