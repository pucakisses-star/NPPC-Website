#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/import_tpp_eco_animal_rows_5.sh
#
# Fifth batch (rows 401-406). Anti-government / anti-IRS / weapons-
# trafficking prosecutions. Skipped if already in DB: Eric G. King.
# All other defendants are foreign nationals or US citizens prosecuted
# in U.S. federal court.
set -e

php artisan tinker --execute='
$rows = [
    ["name"=>"Eric G. King","first_name"=>"Eric","middle_name"=>"G.","last_name"=>"King","gender"=>"Male","race"=>"White","state"=>"Missouri","era"=>"2010s","ideologies"=>["Anarchism","Anti-government"],"in_custody"=>false,"released"=>true,"description"=>"Eric G. King attempted to firebomb Congressman Emanuel Cleavers Kansas City office with Molotov cocktails on September 11, 2014 as a form of anti-government protest. The cocktails failed to ignite. Sentenced to 120 months federal prison.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"Missouri","charges"=>"Federal arson and use of explosive devices - 2014 attempted firebombing of Rep. Emanuel Cleavers Kansas City congressional office","arrest_date"=>"2014-09-11","incarceration_date"=>"2014-09-11","convicted"=>"Yes - jury verdict","sentence"=>"120 months federal prison"]]],

    ["name"=>"Massimo Romagnoli","first_name"=>"Massimo","last_name"=>"Romagnoli","gender"=>"Male","race"=>"White","state"=>"New York","era"=>"2010s","ideologies"=>["Arms Trafficking"],"in_custody"=>false,"released"=>true,"description"=>"Massimo Romagnoli, a former member of the Italian Parliament, conspired in 2014 with U.S. citizen Virgil Flaviu Georgescu and former Romanian government minister Cristian Vintila to sell weapons to what they believed were members of the FARC. The buyers were DEA informants posing as FARC representatives interested in arming attacks on U.S. military personnel in Colombia. Sentenced to 48 months federal prison.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"Federal conspiracy to provide material support to a foreign terrorist organization - 2014 DEA-FARC weapons sting","arrest_date"=>"2014-12-15","incarceration_date"=>"2014-12-15","convicted"=>"Yes - guilty plea","sentence"=>"48 months federal prison"]]],

    ["name"=>"Cristian Vintila","first_name"=>"Cristian","last_name"=>"Vintila","gender"=>"Male","race"=>"White","state"=>"New York","era"=>"2010s","ideologies"=>["Arms Trafficking"],"in_custody"=>false,"released"=>true,"description"=>"Cristian Vintila, a former high-ranking government minister in Romania, conspired with Italian ex-MP Massimo Romagnoli and U.S. citizen Virgil Flaviu Georgescu to sell weapons to DEA informants posing as FARC members. Sentenced to 48 months federal prison.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"Federal conspiracy to provide material support to a foreign terrorist organization - 2014 DEA-FARC weapons sting","arrest_date"=>"2014-12-15","incarceration_date"=>"2014-12-15","convicted"=>"Yes - guilty plea","sentence"=>"48 months federal prison"]]],

    ["name"=>"Virgil Flaviu Georgescu","first_name"=>"Virgil","middle_name"=>"Flaviu","last_name"=>"Georgescu","gender"=>"Male","race"=>"White","state"=>"New York","era"=>"2010s","ideologies"=>["Arms Trafficking"],"in_custody"=>false,"released"=>true,"description"=>"Virgil Flaviu Georgescu, a U.S. citizen and international businessman, conspired with Italian ex-MP Massimo Romagnoli and former Romanian government minister Cristian Vintila to sell weapons to DEA informants posing as FARC members. The deal contemplated arming attacks on U.S. military personnel in Colombia. Convicted at trial; 120 months federal prison.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"Federal conspiracy to provide material support to a foreign terrorist organization - 2014 DEA-FARC weapons sting","arrest_date"=>"2014-12-15","incarceration_date"=>"2014-12-15","convicted"=>"Yes - jury verdict","sentence"=>"120 months federal prison"]]],

    ["name"=>"Michael Steven Sandford","first_name"=>"Michael","middle_name"=>"Steven","last_name"=>"Sandford","gender"=>"Male","race"=>"White","state"=>"Nevada","era"=>"2010s","ideologies"=>["Anti-Trump"],"in_custody"=>false,"released"=>true,"description"=>"Michael Steven Sandford, a 20-year-old British national who had overstayed his tourism visa, attended a Donald Trump campaign rally in Las Vegas on June 18, 2016. After approaching police officers under the pretext of asking for an autograph, he attempted to seize an officers Glock handgun. The day before, he had practiced firing a Glock at a rented range. Pleaded guilty; sentenced to 12 months and 1 day federal prison; deported to Britain after about 5 months served.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"Nevada","charges"=>"Federal disrupting an official function and being an alien in possession of a firearm - June 2016 Las Vegas Trump rally attempted firearm seizure","arrest_date"=>"2016-06-18","incarceration_date"=>"2016-06-18","convicted"=>"Yes - guilty plea","sentence"=>"12 months and 1 day federal prison; deported to U.K. after ~5 months served"]]],

    ["name"=>"David Michael Ansberry","first_name"=>"David","middle_name"=>"Michael","last_name"=>"Ansberry","gender"=>"Male","race"=>"White","state"=>"Colorado","era"=>"2010s","ideologies"=>["Anti-government"],"in_custody"=>true,"released"=>false,"description"=>"David Michael Ansberry, age 66, a Colorado counterculture-community veteran, placed a radio-controlled explosive device in a backpack at the Nederland Police Station on October 11, 2016 and attempted to detonate it from the Nederland Inn across the street. The detonation failed. Police later identified him as the bomber; officers found stickers reading STP (Serenity, Tranquility, Peace) in his luggage. He was reportedly motivated by belief that Nederland officers had murdered a member of his community. Sentenced to 324 months federal prison.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"Colorado","charges"=>"Federal use of an explosive device against a government building; possession of an unregistered destructive device - October 2016 Nederland CO police-station bombing attempt","arrest_date"=>"2016-10-15","incarceration_date"=>"2016-10-15","convicted"=>"Yes - guilty plea","sentence"=>"324 months federal prison"]]],
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
echo "Prosecution Project rows 401-406 import complete."
