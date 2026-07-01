#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_hancock_drone_protesters.sh
# Adds 11 Hancock anti-drone activists who served 15 days in Onondaga County Jail
# in 2013 for their October 2012 protest. Mary Anne Grady Flores (the 12th) is separate.
# Note: Mark Colville, Clare Grady, and Martha Hennessy later participated in the
# Kings Bay Plowshares action (2018) and are given both cases here.
set -e

echo "Adding Judy Bello..."
php artisan prisoner:add '{
  "name": "Judy Bello",
  "first_name": "Judy",
  "last_name": "Bello",
  "description": "Judy Bello is a peace activist from Rochester, New York and member of Upstate Drone Action, a coalition that organized sustained nonviolent resistance to U.S. drone warfare at Hancock Field Air National Guard Base near Syracuse. On October 1, 2012, Bello was among a group of activists who entered the base to protest the MQ-9 Reaper drone program, which operates lethal drone strikes in Afghanistan and other countries from Hancock. She was convicted of disorderly conduct and sentenced to 15 days in Onondaga County Jail in 2013.",
  "state": "New York",
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

echo "Adding Daniel Burgevin..."
php artisan prisoner:add '{
  "name": "Daniel Burgevin",
  "first_name": "Daniel",
  "last_name": "Burgevin",
  "description": "Daniel Burgevin is a peace activist from Trumansburg, New York and participant in Upstate Drone Action. On October 1, 2012, he was among a group of activists who entered Hancock Field Air National Guard Base near Syracuse to protest the U.S. drone warfare program. Hancock trains drone pilots and coordinates lethal MQ-9 Reaper drone strikes in Afghanistan. Burgevin was convicted of disorderly conduct and sentenced to 15 days in Onondaga County Jail in 2013.",
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

echo "Adding Mark Colville..."
php artisan prisoner:add '{
  "name": "Mark Colville",
  "first_name": "Mark",
  "last_name": "Colville",
  "description": "Mark Colville is a Catholic Worker activist and director of the Amistad Catholic Worker community in New Haven, Connecticut. A committed practitioner of nonviolent direct action, Colville participated in the October 2012 anti-drone protest at Hancock Field Air National Guard Base near Syracuse, New York, where he was arrested for trespassing on the base that trains pilots for MQ-9 Reaper drone strikes in Afghanistan. Convicted of disorderly conduct, he served 15 days in Onondaga County Jail in 2013. In April 2018, Colville joined six other Catholic Worker and peace activists in the Kings Bay Plowshares action, entering Naval Submarine Base Kings Bay in St. Marys, Georgia — the only East Coast Trident nuclear submarine base — to enact a symbolic disarmament. They hammered on a monument to nuclear weapons, poured blood, and unfurled banners reading \"The Ultimate Logic of Racism Is Genocide\" and \"Nuclear Weapons: Illegal / Immoral.\" Colville was convicted of conspiracy, destruction of property, and trespass on a military installation and sentenced to time served plus three years of supervised release.",
  "state": "Connecticut",
  "gender": "Male",
  "ideologies": ["Anti-war", "Anti-drone", "Nuclear disarmament", "Catholic Worker", "Civil disobedience"],
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
    },
    {
      "institution_name": "Camden County Jail",
      "institution_city": "Woodbine",
      "institution_state": "Georgia",
      "charges": "Conspiracy, destruction of government property, trespass on a military installation; Kings Bay Plowshares action at Naval Submarine Base Kings Bay, April 4, 2018",
      "arrest_date": "2018-04-04",
      "sentence": "Time served plus 3 years supervised release"
    }
  ]
}'

echo "Adding Clare Grady..."
php artisan prisoner:add '{
  "name": "Clare Grady",
  "first_name": "Clare",
  "last_name": "Grady",
  "description": "Clare Grady is a Catholic Worker activist from Ithaca, New York and sister of fellow anti-drone activist Mary Anne Grady Flores. A longtime peace activist, Grady participated in the October 2012 protest at Hancock Field Air National Guard Base near Syracuse, where she was arrested for trespassing during a demonstration against the U.S. drone warfare program. Convicted of disorderly conduct, she served 15 days in Onondaga County Jail in 2013. In April 2018, Grady joined six other Catholic Worker and peace activists in the Kings Bay Plowshares action, entering Naval Submarine Base Kings Bay in St. Marys, Georgia — the only East Coast Trident nuclear submarine base — on the 50th anniversary of Dr. Martin Luther King Jr.'\''s assassination to perform a symbolic disarmament of nuclear weapons. She was convicted of conspiracy, destruction of government property, and trespass on a military installation. Having already served several months in pre-trial detention, she was sentenced to time served plus three years of supervised release.",
  "state": "New York",
  "gender": "Female",
  "ideologies": ["Anti-war", "Anti-drone", "Nuclear disarmament", "Catholic Worker", "Civil disobedience"],
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
    },
    {
      "institution_name": "Camden County Jail",
      "institution_city": "Woodbine",
      "institution_state": "Georgia",
      "charges": "Conspiracy, destruction of government property, trespass on a military installation; Kings Bay Plowshares action at Naval Submarine Base Kings Bay, April 4, 2018",
      "arrest_date": "2018-04-04",
      "sentence": "Time served (several months pre-trial detention) plus 3 years supervised release"
    }
  ]
}'

echo "Adding Martha Hennessy..."
php artisan prisoner:add '{
  "name": "Martha Hennessy",
  "first_name": "Martha",
  "last_name": "Hennessy",
  "description": "Martha Hennessy is a Catholic Worker activist from New York City and the granddaughter of Dorothy Day, co-founder of the Catholic Worker movement. A devoted practitioner of nonviolent resistance, Hennessy participated in the October 2012 protest at Hancock Field Air National Guard Base near Syracuse, New York, trespassing on the base that trains MQ-9 Reaper drone pilots responsible for lethal strikes in Afghanistan and other countries. Convicted of disorderly conduct, she served 15 days in Onondaga County Jail in 2013. In April 2018, Hennessy was one of the Kings Bay Plowshares Seven who entered Naval Submarine Base Kings Bay in St. Marys, Georgia — the only East Coast base for Trident nuclear submarines — on the 50th anniversary of Dr. Martin Luther King Jr.'\''s assassination. The group hammered on a monument to nuclear weapons, poured blood on the facility, and displayed banners declaring nuclear weapons illegal and immoral. After a lengthy pre-trial period, Hennessy was convicted of conspiracy, destruction of government property, and trespass on a military installation. In January 2020 she was sentenced to 10 months in federal prison plus three years of supervised release.",
  "state": "New York",
  "gender": "Female",
  "ideologies": ["Anti-war", "Anti-drone", "Nuclear disarmament", "Catholic Worker", "Civil disobedience"],
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
    },
    {
      "institution_name": "FCI Danbury",
      "institution_city": "Danbury",
      "institution_state": "Connecticut",
      "charges": "Conspiracy, destruction of government property, trespass on a military installation; Kings Bay Plowshares action at Naval Submarine Base Kings Bay, April 4, 2018",
      "arrest_date": "2018-04-04",
      "sentence": "10 months federal prison plus 3 years supervised release",
      "imprisoned_for_days": 304
    }
  ]
}'

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

echo "Adding Ed Kinane..."
php artisan prisoner:add '{
  "name": "Ed Kinane",
  "first_name": "Ed",
  "last_name": "Kinane",
  "description": "Ed Kinane is a longtime peace activist and writer from Syracuse, New York, longtime member of the Syracuse Peace Council and a regular participant in Upstate Drone Action. He participated in the October 2012 protest at Hancock Field Air National Guard Base near Syracuse, where activists trespassed to protest the MQ-9 Reaper drone program responsible for lethal strikes in Afghanistan and other countries. Kinane was convicted of disorderly conduct and sentenced to 15 days in Onondaga County Jail in 2013.",
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

echo "Adding Rae Kramer..."
php artisan prisoner:add '{
  "name": "Rae Kramer",
  "first_name": "Rae",
  "last_name": "Kramer",
  "description": "Rae Kramer is a peace activist from Syracuse, New York who participated in the October 2012 protest at Hancock Field Air National Guard Base near Syracuse, where activists trespassed to protest U.S. drone warfare. Hancock trains pilots for MQ-9 Reaper drones used in lethal strikes in Afghanistan and elsewhere. Kramer was convicted of disorderly conduct and sentenced to 15 days in Onondaga County Jail in 2013.",
  "state": "New York",
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

echo "All 11 Hancock drone protesters added."
