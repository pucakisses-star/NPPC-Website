#!/usr/bin/env bash
# prisoner:add commands generated from antifawatch.net pages 600-663 review.
# Each person below was INDEPENDENTLY VERIFIED (court records / DOJ / news) to have
# served jail or prison time (or substantial pretrial detention) in a protest-connected case.
# RUN ON THE SERVER (this web container has no DB). Review before running.
# The command de-duplicates by name and refuses to create an existing prisoner.
# NOTE: continue-on-error is intentional -- a duplicate (or any single failure)
# must NOT abort the batch, so we do NOT use 'set -e' here.
set +e

# ===================================================================
# GROUP A — READY (37): protest-connected, served time, no special caveat
# ===================================================================

# Anthony Krohn (WI) — 60 months federal prison
php artisan prisoner:add '{"name": "Anthony Krohn", "first_name": "Anthony", "last_name": "Krohn", "description": "Felon-in-possession of firearm during June 2020 Madison protests. Sentence: 60 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-wdwi/pr/madison-man-sentenced-five-years-illegal-gun-possession", "state": "WI", "cases": [{"charges": "Felon-in-possession of firearm during June 2020 Madison protests", "sentence": "60 months federal prison"}]}'

# Branden Wolfe (MN) — 41 months federal prison
php artisan prisoner:add '{"name": "Branden Wolfe", "first_name": "Branden", "last_name": "Wolfe", "description": "Aided arson of Minneapolis Police Third Precinct (2020). Sentence: 41 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-mn/pr/st-paul-man-sentenced-prison-12-million-restitution-minneapolis-police-third-precinct", "state": "MN", "cases": [{"charges": "Aided arson of Minneapolis Police Third Precinct (2020)", "sentence": "41 months federal prison"}]}'

# Bruce Thompson (GA) — 14 months federal prison
php artisan prisoner:add '{"name": "Bruce Thompson", "first_name": "Bruce", "last_name": "Thompson", "description": "Federal arson of a Gainesville GA police car (June 2020). Sentence: 14 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-ndga/pr/gainesville-men-sentenced-federal-arson-charges-setting-fire-police-car-parked-officer", "state": "GA", "cases": [{"charges": "Federal arson of a Gainesville GA police car (June 2020)", "sentence": "14 months federal prison"}]}'

# Bryce Williams (MN) — 27 months federal prison
php artisan prisoner:add '{"name": "Bryce Williams", "first_name": "Bryce", "last_name": "Williams", "description": "Conspiracy to commit arson, Minneapolis Police Third Precinct (2020). Sentence: 27 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-mn/pr/staples-man-sentenced-prison-12-million-restitution-minneapolis-police-third-precinct", "state": "MN", "cases": [{"charges": "Conspiracy to commit arson, Minneapolis Police Third Precinct (2020)", "sentence": "27 months federal prison"}]}'

# Charles Pittman (NC) — 5 years federal prison
php artisan prisoner:add '{"name": "Charles Pittman", "first_name": "Charles", "last_name": "Pittman", "description": "Arson of Fayetteville Market House during May 30 2020 protest. Sentence: 5 years federal prison. Verified via public court/press records: https://www.justice.gov/usao-ednc/pr/fayetteville-s-market-house-arson-defendants-plead-guilty-federal-charges", "state": "NC", "cases": [{"charges": "Arson of Fayetteville Market House during May 30 2020 protest", "sentence": "5 years federal prison"}]}'

# Colinford Mattis (NY) — 12 months and a day federal prison
php artisan prisoner:add '{"name": "Colinford Mattis", "first_name": "Colinford", "last_name": "Mattis", "description": "Drove van in NYPD-vehicle Molotov firebombing, Brooklyn (2020); attorney. Sentence: 12 months and a day federal prison. Verified via public court/press records: https://www.cnn.com/2023/01/27/us/george-floyd-protests-second-lawyer-prison", "state": "NY", "cases": [{"charges": "Drove van in NYPD-vehicle Molotov firebombing, Brooklyn (2020); attorney", "sentence": "12 months and a day federal prison"}]}'

# Courtland Renford (NY) — 60 months federal prison
php artisan prisoner:add '{"name": "Courtland Renford", "first_name": "Courtland", "last_name": "Renford", "description": "Buffalo City Hall fire during 2020 protests (federal rioting). Sentence: 60 months federal prison. Verified via public court/press records: https://buffalonews.com/news/local/crime-and-courts/man-sentenced-to-5-years-in-prison-for-setting-city-hall-fire-during-2020-protests/article_6196a34c-61be-11ec-866b-6bf22723ee3b.html", "state": "NY", "cases": [{"charges": "Buffalo City Hall fire during 2020 protests (federal rioting)", "sentence": "60 months federal prison"}]}'

# Damion Zachary Feller (OR) — 73 months prison
php artisan prisoner:add '{"name": "Damion Zachary Feller", "first_name": "Damion", "last_name": "Feller", "description": "Threw flares into police cruiser and Target during Portland May Day 2017 riot; riot/arson. Sentence: 73 months prison. Verified via public court/press records: https://katu.com/news/local/man-who-threw-flares-into-police-cruiser-in-may-day-riot-sentenced-to-6-years-prison", "state": "OR", "cases": [{"charges": "Threw flares into police cruiser and Target during Portland May Day 2017 riot; riot/arson", "sentence": "73 months prison"}]}'

# Dashun Martin (GA) — 17 months federal prison
php artisan prisoner:add '{"name": "Dashun Martin", "first_name": "Dashun", "last_name": "Martin", "description": "Federal arson of a Gainesville GA police car (June 2020). Sentence: 17 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-ndga/pr/gainesville-men-sentenced-federal-arson-charges-setting-fire-police-car-parked-officer", "state": "GA", "cases": [{"charges": "Federal arson of a Gainesville GA police car (June 2020)", "sentence": "17 months federal prison"}]}'

# Delveccho Waller (GA) — 21 months federal prison
php artisan prisoner:add '{"name": "Delveccho Waller", "first_name": "Delveccho", "last_name": "Waller", "description": "Federal arson of a Gainesville GA police car (June 2020); watchlist spelled '\''Deveccho'\''. Sentence: 21 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-ndga/pr/gainesville-men-sentenced-federal-arson-charges-setting-fire-police-car-parked-officer", "state": "GA", "cases": [{"charges": "Federal arson of a Gainesville GA police car (June 2020); watchlist spelled '\''Deveccho'\''", "sentence": "21 months federal prison"}]}'

# Devarian Haynes (NV) — 2 years federal prison
php artisan prisoner:add '{"name": "Devarian Haynes", "first_name": "Devarian", "last_name": "Haynes", "description": "Federal civil disorder; burning of a Las Vegas police SUV (2020). Sentence: 2 years federal prison. Verified via public court/press records: https://knpr.org/knpr/2022-03-28/3-plead-guilty-to-burning-las-vegas-police-suv-amid-2020-protest", "state": "NV", "cases": [{"charges": "Federal civil disorder; burning of a Las Vegas police SUV (2020)", "sentence": "2 years federal prison"}]}'

# Dylan Robinson (MN) — 48 months federal prison
php artisan prisoner:add '{"name": "Dylan Robinson", "first_name": "Dylan", "last_name": "Robinson", "description": "Aiding/abetting arson of Minneapolis Police Third Precinct (2020). Sentence: 48 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-mn/pr/brainerd-man-sentenced-prison-12-million-restitution-minneapolis-police-third-precinct", "state": "MN", "cases": [{"charges": "Aiding/abetting arson of Minneapolis Police Third Precinct (2020)", "sentence": "48 months federal prison"}]}'

# Earlja Dudley (NJ) — 30 months federal prison
php artisan prisoner:add '{"name": "Earlja Dudley", "first_name": "Earlja", "last_name": "Dudley", "description": "Attempted arson of a Trenton police vehicle (2020, federal civil disorder). Sentence: 30 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-nj/pr/mercer-county-man-sentenced-30-months-prison-interfering-law-enforcement-officers-during", "state": "NJ", "cases": [{"charges": "Attempted arson of a Trenton police vehicle (2020, federal civil disorder)", "sentence": "30 months federal prison"}]}'

# Fornandous Henderson (MN) — 78 months federal prison
php artisan prisoner:add '{"name": "Fornandous Henderson", "first_name": "Fornandous", "last_name": "Henderson", "description": "Molotov arson of Dakota County government building (2020). Sentence: 78 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-mn/pr/savage-man-sentenced-more-6-years-prison-arson-dakota-county-government-building", "state": "MN", "cases": [{"charges": "Molotov arson of Dakota County government building (2020)", "sentence": "78 months federal prison"}]}'

# Gage Halupowski (OR) — 70 months (5 yr 10 mo) prison
php artisan prisoner:add '{"name": "Gage Halupowski", "first_name": "Gage", "last_name": "Halupowski", "description": "Baton assault at June 2019 Portland protest; 2nd-degree assault. Sentence: 70 months (5 yr 10 mo) prison. Verified via public court/press records: https://www.seattletimes.com/seattle-news/northwest/man-gets-prison-for-baton-strike-at-portland-protests/", "state": "OR", "cases": [{"charges": "Baton assault at June 2019 Portland protest; 2nd-degree assault", "sentence": "70 months (5 yr 10 mo) prison"}]}'

# Jackson Patton (UT) — 24 months federal prison
php artisan prisoner:add '{"name": "Jackson Patton", "first_name": "Jackson", "last_name": "Patton", "description": "Federal civil disorder; role in burning a Salt Lake City police car (2020). Sentence: 24 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-ut/pr/patton-sentenced-24-months-federal-prison-role-may-2020-salt-lake-city-civil-unrest", "state": "UT", "cases": [{"charges": "Federal civil disorder; role in burning a Salt Lake City police car (2020)", "sentence": "24 months federal prison"}]}'

# Jesse Clark (TN) — 12 years TN state prison
php artisan prisoner:add '{"name": "Jesse Clark", "first_name": "Jesse", "last_name": "Clark", "description": "Aggravated arson of Nashville Metro Courthouse (May 30 2020). Sentence: 12 years TN state prison. Verified via public court/press records: https://sci.ccc.nashville.gov/Search/CriminalHistory?P_CASE_IDENTIFIER=JESSE%5ECLARK%5E12101993%5E575865", "state": "TN", "cases": [{"charges": "Aggravated arson of Nashville Metro Courthouse (May 30 2020)", "sentence": "12 years TN state prison"}]}'

# Jesse Smallwood (GA) — 21 months federal prison
php artisan prisoner:add '{"name": "Jesse Smallwood", "first_name": "Jesse", "last_name": "Smallwood", "description": "Federal arson of a Gainesville GA police car (June 2020). Sentence: 21 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-ndga/pr/gainesville-men-sentenced-federal-arson-charges-setting-fire-police-car-parked-officer", "state": "GA", "cases": [{"charges": "Federal arson of a Gainesville GA police car (June 2020)", "sentence": "21 months federal prison"}]}'

# Jose Felan (MN) — 78 months federal prison
php artisan prisoner:add '{"name": "Jose Felan", "first_name": "Jose", "last_name": "Felan", "description": "Arson of multiple St. Paul buildings during 2020 unrest. Sentence: 78 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-mn/pr/rochester-man-sentenced-65-years-prison-arson-multiple-buildings-st-paul", "state": "MN", "cases": [{"charges": "Arson of multiple St. Paul buildings during 2020 unrest", "sentence": "78 months federal prison"}]}'

# Judah Bailey (GA) — 21 months federal prison
php artisan prisoner:add '{"name": "Judah Bailey", "first_name": "Judah", "last_name": "Bailey", "description": "Federal arson of a Gainesville GA police car (June 2020). Sentence: 21 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-ndga/pr/gainesville-men-sentenced-federal-arson-charges-setting-fire-police-car-parked-officer", "state": "GA", "cases": [{"charges": "Federal arson of a Gainesville GA police car (June 2020)", "sentence": "21 months federal prison"}]}'

# Kyle Olson (WI) — 27 months federal prison
php artisan prisoner:add '{"name": "Kyle Olson", "first_name": "Kyle", "last_name": "Olson", "description": "Felon-in-possession of firearm during May 31 2020 Madison unrest. Sentence: 27 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-wdwi/pr/edgerton-man-sentenced-5-years-illegal-gun-possession-during-civil-unrest-0", "state": "WI", "cases": [{"charges": "Felon-in-possession of firearm during May 31 2020 Madison unrest", "sentence": "27 months federal prison"}]}'

# Lore-Elisabeth Blumenthal (PA) — 30 months federal prison
php artisan prisoner:add '{"name": "Lore-Elisabeth Blumenthal", "first_name": "Lore-Elisabeth", "last_name": "Blumenthal", "description": "Arson of two Philadelphia police vehicles (2020); identified via Etsy purchase. Sentence: 30 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-edpa/pr/philadelphia-woman-sentenced-2-years-prison-after-pleading-guilty-connection-arson-two", "state": "PA", "cases": [{"charges": "Arson of two Philadelphia police vehicles (2020); identified via Etsy purchase", "sentence": "30 months federal prison"}]}'

# Loren Reed (AZ) — ~11 months pretrial detention; non-cooperation plea
php artisan prisoner:add '{"name": "Loren Reed", "first_name": "Loren", "last_name": "Reed", "description": "18 USC 844(e) threat to burn govt buildings, Page AZ (2020); held ~11 months federal pretrial detention without bail. Sentence: ~11 months pretrial detention; non-cooperation plea. Verified via public court/press records: https://cldc.org/loren-reed-accepts-non-cooperation-plea-agreement-and-is-released-pending-sentencing", "state": "AZ", "cases": [{"charges": "18 USC 844(e) threat to burn govt buildings, Page AZ (2020); held ~11 months federal pretrial detention without bail", "sentence": "~11 months pretrial detention; non-cooperation plea"}]}'

# Margaret Channon (WA) — 5 years federal prison
php artisan prisoner:add '{"name": "Margaret Channon", "first_name": "Margaret", "last_name": "Channon", "description": "Federal arson; set five Seattle police vehicles on fire (May 30 2020). Sentence: 5 years federal prison. Verified via public court/press records: https://www.justice.gov/usao-wdwa/pr/tacoma-woman-sentenced-5-years-prison-arson-downtown-seattle-protest", "state": "WA", "cases": [{"charges": "Federal arson; set five Seattle police vehicles on fire (May 30 2020)", "sentence": "5 years federal prison"}]}'

# Matthew Rupert (IL) — 105 months federal prison
php artisan prisoner:add '{"name": "Matthew Rupert", "first_name": "Matthew", "last_name": "Rupert", "description": "Arson of Minneapolis cellphone store + destructive devices (2020). Sentence: 105 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-mn/pr/illinois-man-sentenced-prison-arson-minneapolis-cell-phone-store-during-summer-2020-civil", "state": "IL", "cases": [{"charges": "Arson of Minneapolis cellphone store + destructive devices (2020)", "sentence": "105 months federal prison"}]}'

# Melquan Barnett (PA) — 5 years federal prison
php artisan prisoner:add '{"name": "Melquan Barnett", "first_name": "Melquan", "last_name": "Barnett", "description": "Set fire to an Erie coffee shop during May 30 2020 protest. Sentence: 5 years federal prison. Verified via public court/press records: https://www.justice.gov/usao-wdpa/pr/former-erie-man-sentenced-5-years-prison-malicious-destruction-property-fire", "state": "PA", "cases": [{"charges": "Set fire to an Erie coffee shop during May 30 2020 protest", "sentence": "5 years federal prison"}]}'

# Miguel Ramos (NY) — 16 months prison
php artisan prisoner:add '{"name": "Miguel Ramos", "first_name": "Miguel", "last_name": "Ramos", "description": "Set a Rochester police car on fire (2020); riot + arson. Sentence: 16 months prison. Verified via public court/press records: https://www.whec.com/archive/man-sentenced-on-rioting-charge-for-burning-rpd-car-in-may-2020/", "state": "NY", "cases": [{"charges": "Set a Rochester police car on fire (2020); riot + arson", "sentence": "16 months prison"}]}'

# Nicholas Lucia (PA) — 24 months federal prison
php artisan prisoner:add '{"name": "Nicholas Lucia", "first_name": "Nicholas", "last_name": "Lucia", "description": "Threw an explosive device at police during May 30 2020 Pittsburgh protest; federal civil disorder. Sentence: 24 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-wdpa/pr/new-jersey-man-sentenced-2-years-prison-throwing-explosive-device-police-during-may-30", "state": "PA", "cases": [{"charges": "Threw an explosive device at police during May 30 2020 Pittsburgh protest; federal civil disorder", "sentence": "24 months federal prison"}]}'

# Rakem Balogun (TX) — ~6 months pretrial detention; case dismissed
php artisan prisoner:add '{"name": "Rakem Balogun", "first_name": "Rakem", "last_name": "Balogun", "description": "FBI-targeted over Facebook posts; felon-in-possession; ~6 months pretrial detention then indictment DISMISSED. Sentence: ~6 months pretrial detention; case dismissed. Verified via public court/press records: https://www.dailydot.com/debug/rakem-balogun-black-activist-case-dismissed/", "state": "TX", "cases": [{"charges": "FBI-targeted over Facebook posts; felon-in-possession; ~6 months pretrial detention then indictment DISMISSED", "sentence": "~6 months pretrial detention; case dismissed"}]}'

# Ricardo Densmore (NV) — 2 years federal prison
php artisan prisoner:add '{"name": "Ricardo Densmore", "first_name": "Ricardo", "last_name": "Densmore", "description": "Federal civil disorder; burning of a Las Vegas police SUV (2020). Sentence: 2 years federal prison. Verified via public court/press records: https://knpr.org/knpr/2022-03-28/3-plead-guilty-to-burning-las-vegas-police-suv-amid-2020-protest", "state": "NV", "cases": [{"charges": "Federal civil disorder; burning of a Las Vegas police SUV (2020)", "sentence": "2 years federal prison"}]}'

# Richard Rubalcava (NC) — ~84 months federal prison
php artisan prisoner:add '{"name": "Richard Rubalcava", "first_name": "Richard", "last_name": "Rubalcava", "description": "Set fires to Raleigh businesses during May 30 2020 riot; federal arson. Sentence: ~84 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-ednc/pr/man-pleads-guilty-setting-fires-raleigh-business-during-riots", "state": "NC", "cases": [{"charges": "Set fires to Raleigh businesses during May 30 2020 riot; federal arson", "sentence": "~84 months federal prison"}]}'

# Samantha Shader (NY) — 72 months federal prison
php artisan prisoner:add '{"name": "Samantha Shader", "first_name": "Samantha", "last_name": "Shader", "description": "Threw Molotov at occupied NYPD van near Brooklyn Museum (2020). Sentence: 72 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-edny/pr/saugerties-woman-sentenced-72-months-imprisonment-attempted-arson-nypd-van-occupied", "state": "NY", "cases": [{"charges": "Threw Molotov at occupied NYPD van near Brooklyn Museum (2020)", "sentence": "72 months federal prison"}]}'

# Shamar Betts (IL) — 48 months federal prison
php artisan prisoner:add '{"name": "Shamar Betts", "first_name": "Shamar", "last_name": "Betts", "description": "Facebook flyer that incited the May 31 2020 Champaign mall riot; federal Anti-Riot Act. Sentence: 48 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-cdil/pr/champaign-man-sentenced-inciting-riot", "state": "IL", "cases": [{"charges": "Facebook flyer that incited the May 31 2020 Champaign mall riot; federal Anti-Riot Act", "sentence": "48 months federal prison"}]}'

# Timothy O'Donnell (IL) — 34 months federal prison
php artisan prisoner:add '{"name": "Timothy O'\''Donnell", "first_name": "Timothy", "last_name": "O'\''Donnell", "description": "Set a Chicago police SUV on fire during May 30 2020 unrest; federal civil disorder. Sentence: 34 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-ndil/pr/chicago-man-sentenced-nearly-three-years-federal-prison-setting-fire-police-vehicle", "state": "IL", "cases": [{"charges": "Set a Chicago police SUV on fire during May 30 2020 unrest; federal civil disorder", "sentence": "34 months federal prison"}]}'

# Tyree Walker (NV) — 2 years federal prison
php artisan prisoner:add '{"name": "Tyree Walker", "first_name": "Tyree", "last_name": "Walker", "description": "Federal civil disorder; burning of a Las Vegas police SUV (2020). Sentence: 2 years federal prison. Verified via public court/press records: https://knpr.org/knpr/2022-03-28/3-plead-guilty-to-burning-las-vegas-police-suv-amid-2020-protest", "state": "NV", "cases": [{"charges": "Federal civil disorder; burning of a Las Vegas police SUV (2020)", "sentence": "2 years federal prison"}]}'

# Urooj Rahman (NY) — 15 months federal prison
php artisan prisoner:add '{"name": "Urooj Rahman", "first_name": "Urooj", "last_name": "Rahman", "description": "Threw Molotov cocktail at NYPD vehicle, Brooklyn (2020); attorney. Sentence: 15 months federal prison. Verified via public court/press records: https://www.cnn.com/2022/11/18/us/lawyer-molotov-cocktail-protest-prison-sentence/index.html", "state": "NY", "cases": [{"charges": "Threw Molotov cocktail at NYPD vehicle, Brooklyn (2020); attorney", "sentence": "15 months federal prison"}]}'

# Wesley Somers (TN) — 5 years federal prison
php artisan prisoner:add '{"name": "Wesley Somers", "first_name": "Wesley", "last_name": "Somers", "description": "Federal arson of Nashville Historic Courthouse (May 30 2020). Sentence: 5 years federal prison. Verified via public court/press records: https://www.justice.gov/usao-mdtn/pr/hendersonville-man-sentenced-federal-prison-metro-courthouse-arson", "state": "TN", "cases": [{"charges": "Federal arson of Nashville Historic Courthouse (May 30 2020)", "sentence": "5 years federal prison"}]}'

# ===================================================================
# GROUP B — flagged but INCLUDED per NPPC decision (13).
# Editorially sensitive; the caveat is noted above each entry for transparency.
# ===================================================================

# Anthony Hayne (OH) — 72 months federal prison
#   NOTE: 2012 FBI-sting bridge bomb plot (entrapment case)
php artisan prisoner:add '{"name": "Anthony Hayne", "first_name": "Anthony", "last_name": "Hayne", "description": "'\''Cleveland 5'\'' 2012 FBI-sting bridge bomb plot. Sentence: 72 months federal prison. Verified via public court/press records: https://www.cbsnews.com/news/ohio-bomb-plot-update-4th-defendant-gets-prison-in-try-to-blow-up-cleveland-bridge/", "state": "OH", "cases": [{"charges": "'\''Cleveland 5'\'' 2012 FBI-sting bridge bomb plot", "sentence": "72 months federal prison"}]}'

# Brandon Baxter (OH) — 117 months federal prison
#   NOTE: 2012 FBI-sting bridge bomb plot (entrapment case)
php artisan prisoner:add '{"name": "Brandon Baxter", "first_name": "Brandon", "last_name": "Baxter", "description": "'\''Cleveland 5'\'' 2012 FBI-sting bridge bomb plot. Sentence: 117 months federal prison. Verified via public court/press records: https://www.cnn.com/2012/11/20/justice/ohio-bridge-plot-sentencing/index.html", "state": "OH", "cases": [{"charges": "'\''Cleveland 5'\'' 2012 FBI-sting bridge bomb plot", "sentence": "117 months federal prison"}]}'

# Channel Lewis (KY) — 7 months federal prison
#   NOTE: apparent economic/looting motive, not clearly protest-political
php artisan prisoner:add '{"name": "Channel Lewis", "first_name": "Channel", "last_name": "Lewis", "description": "Federal conspiracy; lookout/driver in CVS pharmacy burglary during June 2020 Louisville unrest. Sentence: 7 months federal prison. Verified via public court/press records: https://www.fbi.gov/contact-us/field-offices/louisville/news/press-releases/three-sentenced-for-pharmacy-burglary", "state": "KY", "cases": [{"charges": "Federal conspiracy; lookout/driver in CVS pharmacy burglary during June 2020 Louisville unrest", "sentence": "7 months federal prison"}]}'

# Connor Stevens (OH) — 97 months federal prison
#   NOTE: 2012 FBI-sting bridge bomb plot (entrapment case)
php artisan prisoner:add '{"name": "Connor Stevens", "first_name": "Connor", "last_name": "Stevens", "description": "'\''Cleveland 5'\'' 2012 FBI-sting bridge bomb plot. Sentence: 97 months federal prison. Verified via public court/press records: https://www.cnn.com/2012/11/20/justice/ohio-bridge-plot-sentencing/index.html", "state": "OH", "cases": [{"charges": "'\''Cleveland 5'\'' 2012 FBI-sting bridge bomb plot", "sentence": "97 months federal prison"}]}'

# David Elmakayes (PA) — 15 years federal prison
#   NOTE: apparent economic/looting motive, not clearly protest-political
php artisan prisoner:add '{"name": "David Elmakayes", "first_name": "David", "last_name": "Elmakayes", "description": "Blew up an ATM with explosive during 2020 Philadelphia unrest + felon-in-possession. Sentence: 15 years federal prison. Verified via public court/press records: https://www.justice.gov/usao-edpa/pr/philadelphia-man-sentenced-15-years-blowing-atm-during-spring-2020-civil-unrest", "state": "PA", "cases": [{"charges": "Blew up an ATM with explosive during 2020 Philadelphia unrest + felon-in-possession", "sentence": "15 years federal prison"}]}'

# Deyanna Davis (NY) — 30 months state prison
#   NOTE: serious injury to a person (drove into a trooper)
php artisan prisoner:add '{"name": "Deyanna Davis", "first_name": "Deyanna", "last_name": "Davis", "description": "Drove SUV into a state trooper during Buffalo protest (2020). Sentence: 30 months state prison. Verified via public court/press records: https://buffalonews.com/news/local/crime-courts/deyanna-davis-buffalo-sentencing/article_37b19070-10fc-11ee-80c2-a7e4d07c4b60.html", "state": "NY", "cases": [{"charges": "Drove SUV into a state trooper during Buffalo protest (2020)", "sentence": "30 months state prison"}]}'

# Douglas Wright (OH) — 138 months federal prison
#   NOTE: 2012 FBI-sting bridge bomb plot (entrapment case)
php artisan prisoner:add '{"name": "Douglas Wright", "first_name": "Douglas", "last_name": "Wright", "description": "'\''Cleveland 5'\'' 2012 FBI-sting bridge bomb plot. Sentence: 138 months federal prison. Verified via public court/press records: https://www.cnn.com/2012/11/20/justice/ohio-bridge-plot-sentencing/index.html", "state": "OH", "cases": [{"charges": "'\''Cleveland 5'\'' 2012 FBI-sting bridge bomb plot", "sentence": "138 months federal prison"}]}'

# Joshua Stafford (OH) — 120 months federal prison
#   NOTE: 2012 FBI-sting bridge bomb plot (entrapment case)
php artisan prisoner:add '{"name": "Joshua Stafford", "first_name": "Joshua", "last_name": "Stafford", "description": "'\''Cleveland 5'\'' 2012 FBI-sting bridge bomb plot. Sentence: 120 months federal prison. Verified via public court/press records: https://www.cleveland19.com/story/23630019/bomb-plot-convict-gets-10-years-in-prison-and-a-lifetime-of-probation/", "state": "OH", "cases": [{"charges": "'\''Cleveland 5'\'' 2012 FBI-sting bridge bomb plot", "sentence": "120 months federal prison"}]}'

# Linwood Kaine (MN) — 4 days jail + probation
#   NOTE: minor — only a few days in jail
php artisan prisoner:add '{"name": "Linwood Kaine", "first_name": "Linwood", "last_name": "Kaine", "description": "Obstructing legal process at March 2017 St. Paul counter-protest. Sentence: 4 days jail + probation. Verified via public court/press records: https://www.cbsnews.com/minnesota/news/linwood-kaine-probation/", "state": "MN", "cases": [{"charges": "Obstructing legal process at March 2017 St. Paul counter-protest", "sentence": "4 days jail + probation"}]}'

# Robert Majure (OR) — 5 days jail + probation
#   NOTE: minor — only a few days in jail
php artisan prisoner:add '{"name": "Robert Majure", "first_name": "Robert", "last_name": "Majure", "description": "Doused officers with lubricant/glitter, Aug 2018 Portland counter-protest; harassment. Sentence: 5 days jail + probation. Verified via public court/press records: https://www.bendbulletin.com/nation/portland-protesters-who-doused-cops-with-glitter-lubricant-in-2018-sentenced-to-5-days-in/article_f127ed65-0ddd-5fbe-a3ca-b244f6a943c2.html", "state": "OR", "cases": [{"charges": "Doused officers with lubricant/glitter, Aug 2018 Portland counter-protest; harassment", "sentence": "5 days jail + probation"}]}'

# Tandre Buchanan (OH) — 4 years federal prison
#   NOTE: apparent economic/looting motive, not clearly protest-political
php artisan prisoner:add '{"name": "Tandre Buchanan", "first_name": "Tandre", "last_name": "Buchanan", "description": "Hobbs Act robbery of a Cleveland shop during May 30 2020 unrest. Sentence: 4 years federal prison. Verified via public court/press records: https://www.justice.gov/usao-ndoh/pr/cleveland-man-sentenced-prison-after-robbery-and-evidence-tampering-conviction", "state": "OH", "cases": [{"charges": "Hobbs Act robbery of a Cleveland shop during May 30 2020 unrest", "sentence": "4 years federal prison"}]}'

# Edgar Samaniego (NV) — 20-50 years state prison
#   NOTE: attempted murder / shooting of officer
php artisan prisoner:add '{"name": "Edgar Samaniego", "first_name": "Edgar", "last_name": "Samaniego", "description": "Shot and paralyzed a Las Vegas police officer during June 2020 protest; attempted murder. Sentence: 20-50 years state prison. Verified via public court/press records: https://www.reviewjournal.com/crime/courts/man-who-shot-vegas-police-officer-sentenced-to-prison-2569025/", "state": "NV", "cases": [{"charges": "Shot and paralyzed a Las Vegas police officer during June 2020 protest; attempted murder", "sentence": "20-50 years state prison"}]}'

# Montez Lee (MN) — 120 months federal prison
#   NOTE: arson resulting in death
php artisan prisoner:add '{"name": "Montez Lee", "first_name": "Montez", "last_name": "Lee", "description": "Arson of Minneapolis pawn shop (2020); a man died in the fire. Sentence: 120 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-mn/pr/rochester-man-sentenced-10-years-prison-arson-minneapolis-pawn-shop-resulted-death-man", "state": "MN", "cases": [{"charges": "Arson of Minneapolis pawn shop (2020); a man died in the fire", "sentence": "120 months federal prison"}]}'

