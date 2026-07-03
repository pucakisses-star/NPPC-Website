#!/usr/bin/env bash
# SUPPLEMENTAL antifawatch 600-663 import: cases the keyword filter originally missed
# (their watchlist blurb lacked arrest/charge/jail words) but which were then verified
# to have served jail/prison time. RUN ON THE SERVER after review.
# Continue-on-error is intentional (duplicates must not abort the batch):
set +e

# ===== GROUP A -- verified served time (16) =====

# Corey Long (VA) -- 20 days jail (of 360-day sentence, rest suspended)
#   NOTE: widely viewed as self-defense against white-supremacist marchers
php artisan prisoner:add '{"name": "Corey Long", "first_name": "Corey", "last_name": "Long", "description": "Used an improvised aerosol flamethrower at the Aug 2017 Unite the Right rally, Charlottesville; disorderly conduct. Sentence: 20 days jail (of 360-day sentence, rest suspended). Verified via public court/press records: https://dailyprogress.com/news/local/corey-long-convicted-of-disorderly-conduct-sentenced-to-20-days/article_a51f9012-6b34-11e8-9d0a-7358221b4a40.html", "state": "VA", "cases": [{"charges": "Used an improvised aerosol flamethrower at the Aug 2017 Unite the Right rally, Charlottesville; disorderly conduct", "sentence": "20 days jail (of 360-day sentence, rest suspended)"}]}'

# Cyril Lartigue (TX) -- 24 months federal prison
php artisan prisoner:add '{"name": "Cyril Lartigue", "first_name": "Cyril", "last_name": "Lartigue", "description": "Made a Molotov cocktail near Austin Municipal Court during May 30 2020 protest; unregistered destructive device. Sentence: 24 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-wdtx/pr/cedar-park-man-sentenced-federal-prison-possession-unregistered-destructive-device", "state": "TX", "cases": [{"charges": "Made a Molotov cocktail near Austin Municipal Court during May 30 2020 protest; unregistered destructive device", "sentence": "24 months federal prison"}]}'

# Garrett Ziegler (MN) -- 60 months federal prison
php artisan prisoner:add '{"name": "Garrett Ziegler", "first_name": "Garrett", "last_name": "Ziegler", "description": "Molotov cocktails into Dakota County courthouse, Apple Valley (May 2020). Sentence: 60 months federal prison. Verified via public court/press records: https://patch.com/minnesota/applevalley-rosemount/man-sentenced-molotov-cocktail-attack-apple-valley-wsc-doj", "state": "MN", "cases": [{"charges": "Molotov cocktails into Dakota County courthouse, Apple Valley (May 2020)", "sentence": "60 months federal prison"}]}'

# Jabari Davis (NC) -- 30 months federal prison
php artisan prisoner:add '{"name": "Jabari Davis", "first_name": "Jabari", "last_name": "Davis", "description": "Attempted arson of a Raleigh police SUV, May 31 2020. Sentence: 30 months federal prison. Verified via public court/press records: https://www.wral.com/story/raleigh-man-gets-30-months-in-prison-for-attempt-to-burn-police-car-during-last-summer-s-riots/19576796/", "state": "NC", "cases": [{"charges": "Attempted arson of a Raleigh police SUV, May 31 2020", "sentence": "30 months federal prison"}]}'

# John Dupree (MI) -- 180 days incarceration
php artisan prisoner:add '{"name": "John Dupree", "first_name": "John", "last_name": "Dupree", "description": "Riot + destruction of police property, Grand Rapids May 30 2020. Sentence: 180 days incarceration. Verified via public court/press records: https://www.woodtv.com/news/grand-rapids/man-sentenced-in-connection-to-2020-gr-riot/", "state": "MI", "cases": [{"charges": "Riot + destruction of police property, Grand Rapids May 30 2020", "sentence": "180 days incarceration"}]}'

# Kenyatta Huggins (LA) -- 18 months federal prison
php artisan prisoner:add '{"name": "Kenyatta Huggins", "first_name": "Kenyatta", "last_name": "Huggins", "description": "Federal conspiracy to commit arson; Baton Rouge building arson spree, May-June 2020. Sentence: 18 months federal prison. Verified via public court/press records: https://www.theadvocate.com/baton_rouge/news/courts/article_86f791d0-5c2f-11ec-b1e6-53574cc6c0db.html", "state": "LA", "cases": [{"charges": "Federal conspiracy to commit arson; Baton Rouge building arson spree, May-June 2020", "sentence": "18 months federal prison"}]}'

# Mena Yousif (MN) -- Time served (~7 months) + supervised release
php artisan prisoner:add '{"name": "Mena Yousif", "first_name": "Mena", "last_name": "Yousif", "description": "Accessory after the fact to arson (helped husband Jose Felan flee); held ~7 months pretrial. Sentence: Time served (~7 months) + supervised release. Verified via public court/press records: https://www.cbsnews.com/minnesota/news/mena-yousif-will-not-serve-more-time-for-helping-her-husband/", "state": "MN", "cases": [{"charges": "Accessory after the fact to arson (helped husband Jose Felan flee); held ~7 months pretrial", "sentence": "Time served (~7 months) + supervised release"}]}'

# Oliva Hull (MI) -- 1 year Kent County jail
#   NOTE: includes looting/B&E
php artisan prisoner:add '{"name": "Oliva Hull", "first_name": "Oliva", "last_name": "Hull", "description": "Riot + breaking and entering + malicious destruction, Grand Rapids May 30 2020. Sentence: 1 year Kent County jail. Verified via public court/press records: https://www.fox17online.com/news/local-news/kent/woman-sentenced-to-prison-restitution-for-role-in-2020-gr-riots", "state": "MI", "cases": [{"charges": "Riot + breaking and entering + malicious destruction, Grand Rapids May 30 2020", "sentence": "1 year Kent County jail"}]}'

# Ronald Raymond (MI) -- 1 year Kent County jail (192 days served) + probation
php artisan prisoner:add '{"name": "Ronald Raymond", "first_name": "Ronald", "last_name": "Raymond", "description": "Riot + attempted arson (burning mannequin into a police car), Grand Rapids May 30 2020. Sentence: 1 year Kent County jail (192 days served) + probation. Verified via public court/press records: https://www.woodtv.com/news/grand-rapids/man-sentenced-to-restitution-jail-time-after-gr-riot/", "state": "MI", "cases": [{"charges": "Riot + attempted arson (burning mannequin into a police car), Grand Rapids May 30 2020", "sentence": "1 year Kent County jail (192 days served) + probation"}]}'

# Samuel Frey (MN) -- 27 months federal prison
php artisan prisoner:add '{"name": "Samuel Frey", "first_name": "Samuel", "last_name": "Frey", "description": "Conspiracy to commit arson, St. Paul store (May 2020). Sentence: 27 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-mn/pr/brooklyn-park-man-sentenced-prison-arson-st-paul-s-midway-area", "state": "MN", "cases": [{"charges": "Conspiracy to commit arson, St. Paul store (May 2020)", "sentence": "27 months federal prison"}]}'

# Semaj Pigram (NY) -- 7 years state prison
php artisan prisoner:add '{"name": "Semaj Pigram", "first_name": "Semaj", "last_name": "Pigram", "description": "Weapon possession (loaded stolen handgun) in the Buffalo protest SUV incident, June 2020. Sentence: 7 years state prison. Verified via public court/press records: https://www4.erie.gov/da/press/buffalo-woman-sentenced-injuring-state-trooper-while-driving-through-police-blockade-protest", "state": "NY", "cases": [{"charges": "Weapon possession (loaded stolen handgun) in the Buffalo protest SUV incident, June 2020", "sentence": "7 years state prison"}]}'

# Shante Sutton (NC) -- Active custodial sentence served (released Feb 6 2022) + 36-mo suspended
php artisan prisoner:add '{"name": "Shante Sutton", "first_name": "Shante", "last_name": "Sutton", "description": "Set fires inside Greenville NC businesses during May 31 2020 unrest; served active sentence for burning a building (released Feb 2022). Sentence: Active custodial sentence served (released Feb 6 2022) + 36-mo suspended. Verified via public court/press records: https://www.reflector.com/news/local/supporters-say-greenville-man-targeted-with-riot-charge/article_cb6ad067-e869-5626-ba29-7b4d8e6e079e.html", "state": "NC", "cases": [{"charges": "Set fires inside Greenville NC businesses during May 31 2020 unrest; served active sentence for burning a building (released Feb 2022)", "sentence": "Active custodial sentence served (released Feb 6 2022) + 36-mo suspended"}]}'

# Talib Crump (PA) -- Federal prison; in BOP custody, released April 2023 (exact term not published)
#   NOTE: economic ATM-bombing scheme, not clearly protest-political
php artisan prisoner:add '{"name": "Talib Crump", "first_name": "Talib", "last_name": "Crump", "description": "Federal weapons-of-mass-destruction / dynamite ATM-bombing scheme during June 2020 Philadelphia unrest. Sentence: Federal prison; in BOP custody, released April 2023 (exact term not published). Verified via public court/press records: https://billypenn.com/2024/08/03/atm-explosions-sentence-2020-protests/", "state": "PA", "cases": [{"charges": "Federal weapons-of-mass-destruction / dynamite ATM-bombing scheme during June 2020 Philadelphia unrest", "sentence": "Federal prison; in BOP custody, released April 2023 (exact term not published)"}]}'

# Tyler Maple (NE) -- 4 years prison
php artisan prisoner:add '{"name": "Tyler Maple", "first_name": "Tyler", "last_name": "Maple", "description": "Arson of a Lincoln gas station during May 30 2020 protest. Sentence: 4 years prison. Verified via public court/press records: https://journalstar.com/news/local/crime-and-courts/lincoln-man-gets-prison-time-for-arson-at-ez-go-on-night-of-protests/article_e7d9ee9d-0002-5344-8ceb-a11dc932d2e4.html", "state": "NE", "cases": [{"charges": "Arson of a Lincoln gas station during May 30 2020 protest", "sentence": "4 years prison"}]}'

# Walter Stewart (NY) -- 58 months federal prison
php artisan prisoner:add '{"name": "Walter Stewart", "first_name": "Walter", "last_name": "Stewart", "description": "Felon-in-possession of the firearm in the Buffalo protest SUV incident, June 2020. Sentence: 58 months federal prison. Verified via public court/press records: https://www.wkbw.com/news/local-news/two-buffalo-men-headed-to-prison-for-felony-possession-of-a-firearm", "state": "NY", "cases": [{"charges": "Felon-in-possession of the firearm in the Buffalo protest SUV incident, June 2020", "sentence": "58 months federal prison"}]}'

# Zachary Karas (CA) -- 33 months federal prison
php artisan prisoner:add '{"name": "Zachary Karas", "first_name": "Zachary", "last_name": "Karas", "description": "Possession of Molotov cocktails at the La Mesa police-HQ protest, May 30 2020. Sentence: 33 months federal prison. Verified via public court/press records: https://www.justice.gov/usao-sdca/pr/man-sentenced-33-months-prison-possessing-molotov-cocktails-la-mesa-protest", "state": "CA", "cases": [{"charges": "Possession of Molotov cocktails at the La Mesa police-HQ protest, May 30 2020", "sentence": "33 months federal prison"}]}'

# ===== GROUP B -- homicide cases, INCLUDED per NPPC decision (2) =====

# James Marshall (CO) -- 11 years prison
#   NOTE: homicide / shooting of a driver
php artisan prisoner:add '{"name": "James Marshall", "first_name": "James", "last_name": "Marshall", "description": "Shot a driver (Danny Pruitt, who died) during a June 2020 Alamosa protest. Sentence: 11 years prison. Verified via public court/press records: https://www.alamosanews.com/stories/james-marshall-sentenced-to-11-years-for-shooting-pruitt,8671", "state": "CO", "cases": [{"charges": "Shot a driver (Danny Pruitt, who died) during a June 2020 Alamosa protest", "sentence": "11 years prison"}]}'

# Steven Lopez (KY) -- 30 years prison
#   NOTE: homicide of a fellow protester
php artisan prisoner:add '{"name": "Steven Lopez", "first_name": "Steven", "last_name": "Lopez", "description": "Shot and killed photographer Tyler Gerth at a June 2020 Louisville protest; first-degree manslaughter + 22 counts wanton endangerment. Sentence: 30 years prison. Verified via public court/press records: https://www.lpm.org/news/2024-02-14/man-sentenced-to-30-years-in-prison-for-killing-louisville-photographer-tyler-gerth", "state": "KY", "cases": [{"charges": "Shot and killed photographer Tyler Gerth at a June 2020 Louisville protest; first-degree manslaughter + 22 counts wanton endangerment", "sentence": "30 years prison"}]}'

