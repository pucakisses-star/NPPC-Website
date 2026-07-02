#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_environmental_activists.sh
# Two climate/environmental activists who served jail time for direct action:
#   Heather Doyle — ~55 days total (Calvert County MD, Cove Point LNG protest 2015–2016)
#   Bob Atchison — 4 days (Montgomery County MD, Declare Emergency Beltway blockade Oct 2022)
set +e

echo "Adding Heather Doyle (Cove Point LNG protest, Maryland, 2015–2016)..."
php artisan prisoner:add '{
  "name": "Heather Doyle",
  "first_name": "Heather",
  "last_name": "Doyle",
  "description": "Climate and anti-extraction activist from Maryland affiliated with the SEED Coalition (Stopping Extraction and Exports Destruction). On February 3, 2015, Doyle and co-activist Carling Sothoron climbed a construction crane at the Dominion Resources Cove Point LNG export terminal on the Chesapeake Bay in Calvert County, Maryland, hanging a banner opposing LNG exports and fracking in Maryland. Cove Point was one of the first East Coast LNG export terminals. The Calvert County Sheriff received $1.5 million annually from Dominion to fund deputies who provided security at the site — the same deputies who arrested her. Charged with criminal trespass, Doyle pleaded guilty in April 2015 and chose to serve 40 days at Calvert County Detention Center rather than accept probation, explaining she did not want conditions restricting her future protest activity. After her release, she filed a formal complaint alleging a deputy choked her during the crane arrest. Prosecutors then charged her with making a false statement to a police officer. She was found guilty at jury trial on May 27, 2016 and sentenced to an additional 15 days in jail (3 months with all but 15 suspended), 240 hours of community service, and 2 years supervised probation. Her total incarceration across both cases was approximately 55 days.",
  "state": "Maryland",
  "gender": "Female",
  "ideologies": ["Environmentalist", "Climate justice", "Anti-fracking"],
  "era": "2010s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Calvert County Detention Center",
      "institution_city": "Prince Frederick",
      "institution_state": "Maryland",
      "charges": "Criminal trespass; climbed construction crane at Dominion Cove Point LNG terminal February 3, 2015 with co-activist Carling Sothoron to hang anti-LNG banner",
      "arrest_date": "2015-02-03",
      "incarceration_date": "2015-04-20",
      "imprisoned_for_days": 40,
      "convicted": "Yes — guilty plea",
      "sentence": "40 days Calvert County Detention Center (chose jail over probation to preserve future protest rights)"
    },
    {
      "institution_name": "Calvert County Detention Center",
      "institution_city": "Prince Frederick",
      "institution_state": "Maryland",
      "charges": "Making a false statement to a police officer; filed misconduct complaint alleging a deputy choked her during the crane arrest",
      "arrest_date": "2016-05-27",
      "incarceration_date": "2016-05-27",
      "imprisoned_for_days": 15,
      "convicted": "Yes — jury verdict",
      "sentence": "3 months jail (all but 15 days suspended); 240 hours community service; 2 years supervised probation"
    }
  ]
}'

echo ""
echo "Adding Bob Atchison (Declare Emergency, I-495 blockade, October 2022)..."
php artisan prisoner:add '{
  "name": "Bob Atchison",
  "first_name": "Bob",
  "last_name": "Atchison",
  "description": "Vermont-based climate activist and member of Declare Emergency, a group that used disruptive highway blockades to demand that President Biden declare a national climate emergency. On October 10, 2022, Atchison was one of seven people arrested after Declare Emergency activists blocked the inner loop of Interstate 495 (the Capital Beltway) in Montgomery County, Maryland. He was charged with obstructing or hindering free passage on a public road and failing to obey a law enforcement officer, both misdemeanors. He was sentenced to 4 days in jail and 1 year of probation. A co-defendant from the same action, William Regan, received a 30-day sentence.",
  "state": "Vermont",
  "gender": "Male",
  "ideologies": ["Climate justice", "Environmentalist", "Civil disobedience"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Montgomery County Detention Center",
      "institution_city": "Rockville",
      "institution_state": "Maryland",
      "charges": "Obstructing or hindering free passage on a public road; failing to obey a law enforcement officer; arrested October 10, 2022 at Declare Emergency blockade of I-495 Capital Beltway",
      "arrest_date": "2022-10-10",
      "incarceration_date": "2022-10-10",
      "imprisoned_for_days": 4,
      "convicted": "Yes — guilty",
      "sentence": "4 days jail; 1 year probation"
    }
  ]
}'

echo ""
echo "Done."
