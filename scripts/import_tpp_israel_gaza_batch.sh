#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/import_tpp_israel_gaza_batch.sh
#
# Israel-Gaza-tagged defendants (TPP rows 701-767). Many are already
# in DB from batches 7-8 (will report EXISTS); 21 new defendants here:
# - Three additional Hamas leaders charged in SDNY February 2024
#   (Marwan Issa, Khaled Meshaal, Ali Baraka)
# - Csaba John Csukas (SF rideshare hate-crime, acquitted)
# - Jeffrey Stevens (IN threats, 15 mo)
# - Hashem Younis Hashem Hnaihen (Orlando solar facility, 72 mo)
# - Pahlawan dhow crew (Ullah, Muhammad, Mazhar - false-statements,
#   all dismissed/acquitted)
# - Orlando Javier Ramirez (Fresno synagogue, 16 mo)
# - Jeffrey Scott Hobgood (NC Jewish threats, 18 mo)
# - Karrem Nasr (al-Shabaab material support, 60 mo)
# - Karlovsky cluster (Phoenix car-keying, 3 defendants)
# - UCLA encampment counter-attack (Marlan-Librett, Shalom)
# - Terrence Clyne (IL Palestinian neighbor punch)
# - Mufid Fawaz Alkhader (Albany synagogue shotgun, 120 mo)
# - Salem Seleiman (2021 Times Square antisemitic assault, 24 mo)
set -e

php artisan tinker --execute='
$rows = [
    ["name"=>"Marwan Issa","first_name"=>"Marwan","last_name"=>"Issa","aka"=>"Abu Baraa","gender"=>"Male","race"=>"Middle Eastern / North African","state"=>"New York","era"=>"2020s","ideologies"=>["Salafi/Jihadist/Islamist","Pro-Palestine","Anti-Israel"],"affiliation"=>["HAMAS","Al-Qassam Brigades"],"in_custody"=>false,"released"=>false,"death_date"=>"2024-03-10","description"=>"Marwan Issa, 58, deputy commander of the Hamas Al-Qassam Brigades military wing, was charged February 1, 2024 in SDNY for the October 7, 2023 attacks. Never tried; thought to have been killed in an Israeli airstrike in March 2024.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"18 USC 2332(b) Conspiring to Murder US Nationals; 18 USC 2332a Conspiring to Use a WMD; 18 USC 2332f Conspiring to Bomb a Place of Public Use; 18 USC 2339A; 18 USC 2339B Material Support to FTO (HAMAS); 18 USC 2339C; 50 USC 1705 IEEPA - October 7 2023 attacks","arrest_date"=>"2024-02-01","death_in_custody_date"=>"2024-03-10","convicted"=>"No - charged but not tried; killed before extradition","sentence"=>"Killed in Israeli airstrike March 2024"]]],

    ["name"=>"Khaled Meshaal","first_name"=>"Khaled","last_name"=>"Meshaal","aka"=>"Abu al-Waleed","gender"=>"Male","race"=>"Middle Eastern / North African","state"=>"New York","era"=>"2020s","ideologies"=>["Salafi/Jihadist/Islamist","Pro-Palestine","Anti-Israel"],"affiliation"=>["HAMAS"],"in_custody"=>false,"released"=>true,"description"=>"Khaled Meshaal, 68, senior political leader of Hamas, was charged February 1, 2024 in SDNY for the October 7, 2023 attacks. Active in international media and Hamas diplomatic positioning; resides between Doha, Qatar and Cairo, Egypt as of 2025.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"18 USC 2332(b) Conspiring to Murder US Nationals; 18 USC 2332a Conspiring to Use a WMD; 18 USC 2332f Conspiring to Bomb a Place of Public Use; 18 USC 2339A; 18 USC 2339B Material Support to FTO (HAMAS); 18 USC 2339C; 50 USC 1705 IEEPA - October 7 2023 attacks","arrest_date"=>"2024-02-01","convicted"=>"No - charged but not tried","sentence"=>"At large in Doha/Cairo"]]],

    ["name"=>"Ali Baraka","first_name"=>"Ali","last_name"=>"Baraka","gender"=>"Male","race"=>"Middle Eastern / North African","state"=>"New York","era"=>"2020s","ideologies"=>["Salafi/Jihadist/Islamist","Pro-Palestine","Anti-Israel"],"affiliation"=>["HAMAS"],"in_custody"=>false,"released"=>true,"description"=>"Ali Baraka, 57, head of Hamas National Relations Abroad, was charged February 1, 2024 in SDNY for the October 7, 2023 attacks.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"18 USC 2332(b) Conspiring to Murder US Nationals; 18 USC 2332a Conspiring to Use a WMD; 18 USC 2332f Conspiring to Bomb a Place of Public Use; 18 USC 2339A; 18 USC 2339B Material Support to FTO (HAMAS); 18 USC 2339C; 50 USC 1705 IEEPA - October 7 2023 attacks","arrest_date"=>"2024-02-01","convicted"=>"No - charged but not tried","sentence"=>"At large"]]],

    ["name"=>"Csaba John Csukas","first_name"=>"Csaba","middle_name"=>"John","last_name"=>"Csukas","gender"=>"Male","race"=>"White","state"=>"California","era"=>"2020s","ideologies"=>["Anti-Israel"],"in_custody"=>false,"released"=>true,"description"=>"Csaba John Csukas, 39, of Daly City CA, naturalized US citizen who arrived as a refugee of the Bosnian war, was arrested October 26, 2023 at SFO for allegedly hitting a rideshare customer in the face after asking if he was Jewish or Israeli. Indicted March 12, 2024 on federal hate-crime charges. Acquitted - jury found insufficient evidence of religious or national bias motivation.","cases"=>[["institution_name"=>"US District Court Northern District of California","institution_state"=>"California","charges"=>"18 USC 249(a)(2) Hate Crimes - Oct 2023 SFO rideshare assault","arrest_date"=>"2024-03-12","convicted"=>"No - acquitted","sentence"=>"Acquitted"]]],

    ["name"=>"Jeffrey Stevens","first_name"=>"Jeffrey","last_name"=>"Stevens","aka"=>"Zayed Stevens, Zaid Stevens","gender"=>"Male","race"=>"White","state"=>"Indiana","era"=>"2020s","ideologies"=>["Pro-Palestine","Anti-Jewish"],"in_custody"=>false,"released"=>true,"description"=>"Jeffrey Stevens, 41, of Fort Wayne IN, freelance journalist for The Arab American News, communicated threats online November 17-20, 2023. Posted to the CIA public website that he was going to shoot every government official in the head, and messaged Fort Wayne PD that he would kill every Jew in Fort Wayne. Pleaded guilty; 15 months federal prison with hate-crime motivation enhancement.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"Indiana","charges"=>"18 USC 875(c) Interstate Threatening Communications - Nov 2023 threats to Fort Wayne PD and CIA website","arrest_date"=>"2024-04-05","convicted"=>"Yes - guilty plea","sentence"=>"15 months federal prison"]]],

    ["name"=>"Hashem Younis Hashem Hnaihen","first_name"=>"Hashem","middle_name"=>"Younis Hashem","last_name"=>"Hnaihen","gender"=>"Male","race"=>"Middle Eastern / North African","state"=>"Florida","era"=>"2020s","ideologies"=>["Pro-Palestine","Anti-Israel"],"in_custody"=>true,"released"=>false,"description"=>"Hashem Younis Hashem Hnaihen, 43, Jordanian national residing illegally in Orlando, left letters at multiple Orange County FL locations June 25, 2024 threatening to explode everything here in whole America. Damaged a Wedgefield FL solar power facility on June 29, 2024; the targets supported Israel. Pleaded guilty; sentenced April 2025 to 72 months federal prison.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"Florida","charges"=>"18 USC 844(e) Threatened Use of Explosive [4 counts]; 18 USC 1366(a) Destruction of an Energy Facility - June 2024 Orlando solar facility damage and bomb threats","arrest_date"=>"2024-08-07","convicted"=>"Yes - guilty plea","sentence"=>"72 months federal prison"]]],

    ["name"=>"Ghufran Ullah","first_name"=>"Ghufran","last_name"=>"Ullah","gender"=>"Male","race"=>"Middle Eastern / North African","state"=>"Virginia","era"=>"2020s","ideologies"=>["Salafi/Jihadist/Islamist"],"in_custody"=>false,"released"=>true,"description"=>"Ghufran Ullah, Pakistani citizen and crewmember of the dhow Yunus, was indicted August 7, 2024 in the Pahlawan Iran-to-Houthi weapons-smuggling case for providing materially false information to Navy/Coast Guard boarders during the January 11, 2024 interdiction off Somalia. Charges dismissed without prejudice.","cases"=>[["institution_name"=>"US District Court Eastern District of Virginia","institution_state"=>"Virginia","charges"=>"18 USC 2237(a)(2)(B) Providing Materially False Information to a Federal LEO During Boarding of a Vessel - January 2024 dhow Yunus interdiction","arrest_date"=>"2024-08-07","convicted"=>"No - dismissed without prejudice","sentence"=>"Dismissed"]]],

    ["name"=>"Izhar Muhammad","first_name"=>"Izhar","last_name"=>"Muhammad","gender"=>"Male","race"=>"Middle Eastern / North African","state"=>"Virginia","era"=>"2020s","ideologies"=>["Salafi/Jihadist/Islamist"],"in_custody"=>false,"released"=>true,"description"=>"Izhar Muhammad, Pakistani citizen and crewmember of the dhow Yunus, indicted August 7, 2024 alongside Ghufran Ullah for providing false information during the January 2024 Iran-to-Houthi weapons interdiction off Somalia. Charges dismissed without prejudice.","cases"=>[["institution_name"=>"US District Court Eastern District of Virginia","institution_state"=>"Virginia","charges"=>"18 USC 2237(a)(2)(B) Providing Materially False Information to a Federal LEO During Boarding of a Vessel - January 2024 dhow Yunus interdiction","arrest_date"=>"2024-08-07","convicted"=>"No - dismissed without prejudice","sentence"=>"Dismissed"]]],

    ["name"=>"Mohammad Mazhar","first_name"=>"Mohammad","last_name"=>"Mazhar","gender"=>"Male","race"=>"Middle Eastern / North African","state"=>"Virginia","era"=>"2020s","ideologies"=>["Salafi/Jihadist/Islamist"],"in_custody"=>false,"released"=>true,"description"=>"Mohammad Mazhar, Pakistani citizen, brother of dhow captain Muhammad Pahlawan; indicted December 4, 2024 in the Iran-to-Houthi weapons-smuggling case for providing false information about the captain during the January 2024 interdiction. Charge dismissed with prejudice.","cases"=>[["institution_name"=>"US District Court Eastern District of Virginia","institution_state"=>"Virginia","charges"=>"18 USC 2237(a)(2)(B) Providing Materially False Information to a Federal LEO During Boarding of a Vessel - January 2024 dhow Yunus interdiction","arrest_date"=>"2024-12-04","convicted"=>"No - acquitted/dismissed with prejudice","sentence"=>"Dismissed"]]],

    ["name"=>"Orlando Javier Ramirez","first_name"=>"Orlando","middle_name"=>"Javier","last_name"=>"Ramirez","gender"=>"Male","race"=>"Latino/Hispanic","state"=>"California","era"=>"2020s","ideologies"=>["Anti-Israel","Anti-Jewish"],"in_custody"=>false,"released"=>true,"description"=>"Orlando Javier Ramirez, 30, of Fresno CA, was charged in 2023 with felony vandalism, hate crime, and criminal threats for throwing rocks at a synagogue window. Sentenced to 16 months in state prison.","cases"=>[["institution_name"=>"Fresno County Superior Court","institution_state"=>"California","charges"=>"California PC 422.6(b) Violation of Civil Rights (Damaged Property) [2 counts]; PC 594(a)(2) Vandalism [2 counts]; PC 422(a) Criminal Threat - Oct 2023 Fresno synagogue rocks","arrest_date"=>"2023-10-10","convicted"=>"Yes - jury verdict on some counts","sentence"=>"16 months California state prison"]]],

    ["name"=>"Jeffrey Scott Hobgood","first_name"=>"Jeffrey","middle_name"=>"Scott","last_name"=>"Hobgood","gender"=>"Male","race"=>"White","state"=>"North Carolina","era"=>"2020s","ideologies"=>["Anti-Jewish"],"in_custody"=>false,"released"=>true,"description"=>"Jeffrey Scott Hobgood, 64, of Troy NC, sent an email October 11, 2023 to a Jewish organization stating that he was going to take out every single Jewish person there. Pleaded guilty; 18 months federal prison.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"North Carolina","charges"=>"18 USC 875(c) Transmitting in Interstate Commerce a Communication Containing a Threat to Injure - Oct 2023 NC Jewish organization threat","arrest_date"=>"2023-10-16","convicted"=>"Yes - guilty plea","sentence"=>"18 months federal prison"]]],

    ["name"=>"Karrem Nasr","first_name"=>"Karrem","last_name"=>"Nasr","aka"=>"Ghareeb Al-Muhajir, Egyptian Muslim","gender"=>"Male","race"=>"Middle Eastern / North African","state"=>"New York","era"=>"2020s","ideologies"=>["Salafi/Jihadist/Islamist"],"affiliation"=>["al-Shabaab"],"in_custody"=>true,"released"=>false,"description"=>"Karrem Nasr, 23, of New Jersey, was charged in late December 2023 by SDNY with attempting to join and provide material support to al-Shabaab, a designated FTO. Cited the October 7, 2023 attack as motivation. Pleaded guilty; sentenced to 60 months federal prison + 15 years supervised release.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"18 USC 2339B Attempted Provision of Material Support to FTO (al-Shabaab); 18 USC 3238; 18 USC 2 Aiding and Abetting - Dec 2023 al-Shabaab attempt","arrest_date"=>"2024-01-11","convicted"=>"Yes - guilty plea","sentence"=>"60 months federal prison + 15 yr supervised release"]]],

    ["name"=>"Lisa Melanie Karlovsky","first_name"=>"Lisa","middle_name"=>"Melanie","last_name"=>"Karlovsky","gender"=>"Female","race"=>"White","state"=>"Arizona","era"=>"2020s","ideologies"=>["Pro-Israel"],"in_custody"=>false,"released"=>true,"description"=>"Lisa Melanie Karlovsky, 52, former Arizona director of the Republican Jewish Coalition, with husband Matthew and Bryan Long was arrested January 28, 2024 in Phoenix for keying 16 cars at a pro-Palestine protest. Pleaded guilty to one count of criminal damage; sentenced March 27, 2025 to 18 months supervised probation.","cases"=>[["institution_name"=>"Maricopa County Superior Court","institution_state"=>"Arizona","charges"=>"AZ 13-1602(A)(1) Criminal Damage / Deface - Jan 28 2024 Phoenix pro-Palestine protest car keying","arrest_date"=>"2024-01-28","sentenced_date"=>"2025-03-27","convicted"=>"Yes - guilty plea","sentence"=>"18 months supervised probation"]]],

    ["name"=>"Matthew Karlovsky","first_name"=>"Matthew","last_name"=>"Karlovsky","gender"=>"Male","race"=>"White","state"=>"Arizona","era"=>"2020s","ideologies"=>["Pro-Israel"],"in_custody"=>false,"released"=>true,"description"=>"Matthew Karlovsky, 51, husband of Lisa Karlovsky; arrested January 28, 2024 in Phoenix for keying 16 cars at a pro-Palestine protest. Charges dismissed with prejudice.","cases"=>[["institution_name"=>"Maricopa County Superior Court","institution_state"=>"Arizona","charges"=>"AZ 13-1602(A)(1) Criminal Damage / Deface - Jan 28 2024 Phoenix pro-Palestine protest car keying","arrest_date"=>"2024-01-28","convicted"=>"No - dismissed with prejudice","sentence"=>"Dismissed"]]],

    ["name"=>"Bryan Long","first_name"=>"Bryan","last_name"=>"Long","gender"=>"Male","race"=>"White","state"=>"Arizona","era"=>"2020s","ideologies"=>["Pro-Israel"],"in_custody"=>false,"released"=>true,"description"=>"Bryan Long, 48, with the Karlovskys arrested January 28, 2024 in Phoenix for keying 16 cars at a pro-Palestine protest. Pleaded guilty; 12 months supervised probation.","cases"=>[["institution_name"=>"Maricopa County Superior Court","institution_state"=>"Arizona","charges"=>"AZ 13-1602(A)(1) Criminal Damage / Deface - Jan 28 2024 Phoenix pro-Palestine protest car keying","arrest_date"=>"2024-01-28","convicted"=>"Yes - guilty plea","sentence"=>"12 months supervised probation"]]],

    ["name"=>"Malachi Joshua Marlan-Librett","first_name"=>"Malachi","middle_name"=>"Joshua","last_name"=>"Marlan-Librett","aka"=>"UCLANeffHat","gender"=>"Male","state"=>"California","era"=>"2020s","ideologies"=>["Pro-Israel"],"in_custody"=>false,"released"=>true,"description"=>"Malachi Joshua Marlan-Librett, 28, of LA, allegedly attacked Pro-Palestinian Encampment protesters at UCLA on April 30, 2024 with a broom and other items as part of the counter-protest mob attack. Charges dismissed in exchange for completing 90 hours of therapy and anti-bias training.","cases"=>[["institution_name"=>"Los Angeles County Superior Court","institution_state"=>"California","charges"=>"California PC 242 Battery; PC 245(a)(1) Assault With Deadly Weapon (Not Firearm); PC 422.75(a) Hate Crime Allegation; PC 12022(b)(1) Use of Deadly Weapon - April 30 2024 UCLA encampment attack","arrest_date"=>"2024-04-30","convicted"=>"No - dismissed via diversion (90 hrs therapy/anti-bias training)","sentence"=>"Diversion"]]],

    ["name"=>"Eyal Shalom","first_name"=>"Eyal","last_name"=>"Shalom","aka"=>"UCLAMaroonHoodie","gender"=>"Male","race"=>"Middle Eastern / North African","state"=>"California","era"=>"2020s","ideologies"=>["Pro-Israel"],"in_custody"=>false,"released"=>true,"description"=>"Eyal Shalom, 24, of LA, allegedly used tear gas against pro-Palestinian protesters during the April 30, 2024 UCLA encampment counter-attack. Reconciliation Education and Counseling Crimes of Hate agreement August 20, 2024; 40 hours individual + 40 hours group therapy in lieu of prison.","cases"=>[["institution_name"=>"Los Angeles County Superior Court","institution_state"=>"California","charges"=>"California PC 22810 Unlawful Use of Tear Gas - April 30 2024 UCLA encampment attack","arrest_date"=>"2024-04-30","convicted"=>"No - REACH agreement","sentence"=>"80 hours therapy"]]],

    ["name"=>"Terrence Clyne","first_name"=>"Terrence","last_name"=>"Clyne","gender"=>"Male","race"=>"White","state"=>"Illinois","era"=>"2020s","ideologies"=>["Anti-Palestinian","Anti-Muslim"],"in_custody"=>false,"released"=>true,"description"=>"Terrence Clyne, 68, of Orland Park IL, was charged with misdemeanor battery and felony hate crime for punching his Palestinian neighbor while shouting hateful remarks; dispute stemmed from trash can placement in shared driveway. Pleaded guilty to misdemeanor battery; felony hate crime dropped. Probation.","cases"=>[["institution_name"=>"Cook County Circuit Court","institution_state"=>"Illinois","charges"=>"Illinois Battery (misdemeanor); Hate Crime (Felony, dropped) - 2024 Orland Park IL Palestinian neighbor assault","arrest_date"=>"2024-08-07","convicted"=>"Yes - guilty plea","sentence"=>"Probation"]]],

    ["name"=>"Mufid Fawaz Alkhader","first_name"=>"Mufid","middle_name"=>"Fawaz","last_name"=>"Alkhader","gender"=>"Male","race"=>"Middle Eastern / North African","state"=>"New York","era"=>"2020s","ideologies"=>["Pro-Palestine","Anti-Israel"],"in_custody"=>true,"released"=>false,"description"=>"Mufid Fawaz Alkhader, 29, US citizen born in Iraq and living in Schenectady NY, was indicted February 4, 2025 for discharging a Kel-Tec shotgun into the air twice outside Temple Israel synagogue in Albany while shouting Free Palestine. Attempted to fire a third time but the shotgun jammed; then attempted to tear an Israeli flag from a flagpole. Stated he wanted to scare Zionists. Had a prior restraining order which prevented gun-shop purchase. Pleaded guilty; 120 months federal prison.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"18 USC 922(g)(3) Possession of a Firearm by a Prohibited Person; 18 USC 371 + 922(a)(6) Conspiracy / False Statement in Firearms Purchase; 18 USC 247(a)(2) Obstruction of Free Exercise of Religious Beliefs With a Dangerous Weapon; 18 USC 924(c)(1)(A)(ii) Brandishing a Firearm in Furtherance of a Crime of Violence - Albany Temple Israel shotgun","arrest_date"=>"2025-02-04","convicted"=>"Yes - guilty plea","sentence"=>"120 months federal prison"]]],

    ["name"=>"Salem Seleiman","first_name"=>"Salem","last_name"=>"Seleiman","gender"=>"Male","race"=>"Middle Eastern / North African","state"=>"New York","era"=>"2020s","ideologies"=>["Pro-Palestine","Anti-Israel"],"in_custody"=>true,"released"=>false,"description"=>"Salem Seleiman, 30, was sentenced December 4, 2025 to 24 months New York state prison for the May 20, 2021 antisemitic hate-crime assault in Times Square against a 29-year-old Jewish man attending a Pro-Israel rally; the victim was beaten, pepper-sprayed, kicked, and subjected to antisemitic slurs. Sixth defendant sentenced in connection with the incident; pleaded guilty September 29, 2025.","cases"=>[["institution_name"=>"New York State Department of Corrections","institution_state"=>"New York","charges"=>"NY PEN Assault in the Second Degree (Felony); Assault in the Third Degree as a Hate Crime (Felony) - May 20 2021 Times Square assault","arrest_date"=>"2025-09-29","sentenced_date"=>"2025-12-04","convicted"=>"Yes - guilty plea","sentence"=>"24 months New York state prison"]]],
];

$created = 0; $existing = 0; $errors = 0;
foreach ($rows as $row) {
    $name = $row["name"];
    $existsP = App\Models\Prisoner::where("name", $name)
        ->orWhere("name", "like", "%" . $name . "%")
        ->first();
    if ($existsP) {
        echo "EXISTS: {$name}\n";
        $existing++;
        continue;
    }
    $cases = $row["cases"] ?? [];
    unset($row["cases"]);
    try {
        $prisoner = App\Models\Prisoner::create($row);
    } catch (\Throwable $e) {
        echo "ERROR creating {$name}: {$e->getMessage()}\n";
        $errors++;
        continue;
    }
    foreach ($cases as $c) {
        $instName  = $c["institution_name"]  ?? "Federal Bureau of Prisons (location varied)";
        $instCity  = $c["institution_city"]  ?? null;
        $instState = $c["institution_state"] ?? null;
        unset($c["institution_name"], $c["institution_city"], $c["institution_state"]);
        $inst = App\Models\Institution::firstOrCreate(
            ["name" => $instName],
            array_filter(["city" => $instCity, "state" => $instState])
        );
        $c["prisoner_id"] = $prisoner->id;
        $c["institution_id"] = $inst->id;
        try {
            App\Models\PrisonerCase::create($c);
        } catch (\Throwable $e) {
            echo "  case-create error for {$name}: {$e->getMessage()}\n";
        }
    }
    echo "CREATED: {$name}\n";
    $created++;
}
echo "\nSummary: created={$created}, existing={$existing}, errors={$errors}\n";
'

echo
echo "Israel-Gaza batch import complete."
