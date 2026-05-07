#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/import_cilia_flores_and_narcosobrinos.sh
#
# Adds Cilia Flores (Maduros wife) and her two nephews
# convicted in SDNY in 2016 (Los Narcosobrinos).
set -e

php artisan tinker --execute='
$rows = [
    ["name"=>"Cilia Adela Flores de Maduro","first_name"=>"Cilia","middle_name"=>"Adela","last_name"=>"Flores de Maduro","aka"=>"Cilia Flores","gender"=>"Female","race"=>"Latino/Hispanic","state"=>"New York","era"=>"2020s","ideologies"=>["Bolivarianism"],"affiliation"=>["United Socialist Party of Venezuela (PSUV)","Venezuelan government"],"in_custody"=>true,"released"=>false,"description"=>"Cilia Adela Flores de Maduro, attorney and former Venezuelan National Assembly president, is the wife of ex-president Nicolas Maduro. She was sanctioned by the U.S. Treasury Department under OFAC in September 2018 for alleged corruption and complicity in undermining Venezuelan democracy. Her two nephews, Efrain Antonio Campo Flores and Franqui Francisco Flores de Freitas, were convicted in SDNY in 2016 of conspiring to import 800 kilograms of cocaine into the United States.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"OFAC sanctions designation under E.O. 13692; allegations relating to the Cartel de los Soles and the Maduro narco-terrorism conspiracy","convicted"=>"Pending","sentence"=>"Pending"]]],

    ["name"=>"Efrain Antonio Campo Flores","first_name"=>"Efrain","middle_name"=>"Antonio","last_name"=>"Campo Flores","gender"=>"Male","race"=>"Latino/Hispanic","state"=>"New York","era"=>"2010s","ideologies"=>["Bolivarianism"],"affiliation"=>["Los Narcosobrinos"],"in_custody"=>false,"released"=>true,"description"=>"Efrain Antonio Campo Flores, nephew of Cilia Flores and step-nephew of Nicolas Maduro, was arrested in Port-au-Prince, Haiti on November 10, 2015 by DEA agents in a sting operation. With his cousin Franqui Francisco Flores de Freitas, he attempted to broker a sale of 800 kilograms of cocaine to undercover informants posing as Honduran traffickers, with the cocaine to be flown into the U.S. from Honduras. Convicted at trial in SDNY November 18, 2016; sentenced December 14, 2017 to 216 months federal prison. Released and deported to Venezuela in October 2022 as part of a U.S.-Venezuela prisoner swap.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"21 USC 959(a), 960(a)(3), 960(b)(1)(B) conspiracy to import 5+ kg of cocaine into the United States; 21 USC 963 international cocaine importation conspiracy - November 2015 DEA Haiti sting","arrest_date"=>"2015-11-10","incarceration_date"=>"2015-11-10","sentenced_date"=>"2017-12-14","release_date"=>"2022-10-01","convicted"=>"Yes - jury verdict","sentence"=>"216 months federal prison; commuted/released October 2022 in U.S.-Venezuela prisoner swap"]]],

    ["name"=>"Franqui Francisco Flores de Freitas","first_name"=>"Franqui","middle_name"=>"Francisco","last_name"=>"Flores de Freitas","gender"=>"Male","race"=>"Latino/Hispanic","state"=>"New York","era"=>"2010s","ideologies"=>["Bolivarianism"],"affiliation"=>["Los Narcosobrinos"],"in_custody"=>false,"released"=>true,"description"=>"Franqui Francisco Flores de Freitas, nephew of Cilia Flores, was arrested in Port-au-Prince, Haiti on November 10, 2015 by DEA agents alongside his cousin Efrain Antonio Campo Flores. Convicted at trial in SDNY November 18, 2016 of conspiring to import 800 kilograms of cocaine into the United States; sentenced December 14, 2017 to 216 months federal prison. Released and deported to Venezuela in October 2022 as part of a U.S.-Venezuela prisoner swap.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"21 USC 959(a), 960(a)(3), 960(b)(1)(B) conspiracy to import 5+ kg of cocaine into the United States; 21 USC 963 international cocaine importation conspiracy - November 2015 DEA Haiti sting","arrest_date"=>"2015-11-10","incarceration_date"=>"2015-11-10","sentenced_date"=>"2017-12-14","release_date"=>"2022-10-01","convicted"=>"Yes - jury verdict","sentence"=>"216 months federal prison; commuted/released October 2022 in U.S.-Venezuela prisoner swap"]]],
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
echo "Cilia Flores + Los Narcosobrinos import complete."
