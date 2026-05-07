#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/import_tpp_maduro_and_revisits.sh
#
# Adds defendants from the March 2020 SDNY narco-terrorism
# indictments (Maduro et al., rows 654-659 of TPP) and revisits
# previously-skipped FARC/ELN fugitives who may now be in U.S.
# custody. Per user direction, all seven MADURO indictees are
# created here; user will refine custody/sentencing status in the
# admin panel as needed.
set -e

php artisan tinker --execute='
$rows = [
    // ---- MADURO et al. - SDNY March 2020 narco-terrorism indictments ----
    ["name"=>"Nicolas Maduro Moros","first_name"=>"Nicolas","middle_name"=>"Maduro","last_name"=>"Moros","gender"=>"Male","race"=>"Latino/Hispanic","state"=>"New York","era"=>"2020s","ideologies"=>["Bolivarianism","Anti-imperialism"],"affiliation"=>["United Socialist Party of Venezuela (PSUV)","Cartel de los Soles"],"in_custody"=>true,"released"=>false,"description"=>"Nicolas Maduro Moros, ex-president of Venezuela, was charged March 26, 2020 by the U.S. Attorneys Office for the Southern District of New York with narco-terrorism, conspiracy to import cocaine, and weapons offenses. The DOJ alleged he ran the Cartel de los Soles in conjunction with FARC dissidents to flood the United States with cocaine. The U.S. State Department initially offered a $15 million reward for information leading to his arrest, raised to $25 million in 2025.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"21 USC 960a narco-terrorism; 21 USC 963, 959(a), 960(b)(1)(B) international cocaine importation conspiracy; 18 USC 924(c) firearms in furtherance of drug trafficking; 18 USC 924(o) conspiracy to use machineguns - March 2020 SDNY indictment","arrest_date"=>null,"incarceration_date"=>null,"convicted"=>"Pending","sentence"=>"Pending"]]],

    ["name"=>"Diosdado Cabello Rondon","first_name"=>"Diosdado","middle_name"=>"Cabello","last_name"=>"Rondon","gender"=>"Male","race"=>"Latino/Hispanic","state"=>"New York","era"=>"2020s","ideologies"=>["Bolivarianism"],"affiliation"=>["United Socialist Party of Venezuela (PSUV)","Cartel de los Soles"],"in_custody"=>true,"released"=>false,"description"=>"Diosdado Cabello Rondon, former Venezuelan National Assembly president and PSUV vice president, was charged March 26, 2020 in SDNY with narco-terrorism alongside Nicolas Maduro. Alleged co-leader of the Cartel de los Soles. State Department reward of $10 million.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"21 USC 960a narco-terrorism; 21 USC 963, 959(a), 960(b)(1)(B) international cocaine conspiracy; 18 USC 924(c), 924(o) firearms - March 2020 SDNY indictment","arrest_date"=>null,"convicted"=>"Pending","sentence"=>"Pending"]]],

    ["name"=>"Vladimir Padrino Lopez","first_name"=>"Vladimir","middle_name"=>"Padrino","last_name"=>"Lopez","gender"=>"Male","race"=>"Latino/Hispanic","state"=>"District of Columbia","era"=>"2020s","ideologies"=>["Bolivarianism"],"affiliation"=>["Venezuelan Armed Forces"],"in_custody"=>true,"released"=>false,"description"=>"Vladimir Padrino Lopez, Venezuelan Minister of Defense, was charged March 26, 2020 by the U.S. Attorneys Office for the District of Columbia with conspiring to traffic cocaine using military aircraft and to provide weapons to FARC dissidents. State Department reward of $10 million.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"District of Columbia","charges"=>"21 USC 963 international cocaine importation conspiracy; 18 USC 2 aiding and abetting - March 2020 D.D.C. indictment","arrest_date"=>null,"convicted"=>"Pending","sentence"=>"Pending"]]],

    ["name"=>"Maikel Jose Moreno Perez","first_name"=>"Maikel","middle_name"=>"Jose","last_name"=>"Moreno Perez","gender"=>"Male","race"=>"Latino/Hispanic","state"=>"Florida","era"=>"2020s","ideologies"=>["Bolivarianism"],"affiliation"=>["Venezuelan Supreme Tribunal"],"in_custody"=>true,"released"=>false,"description"=>"Maikel Jose Moreno Perez, former chief justice of Venezuelas Supreme Tribunal of Justice, was charged March 26, 2020 in the Southern District of Florida with money laundering and obstruction of justice. Alleged to have accepted millions in bribes to fix outcomes of dozens of civil and criminal cases. State Department reward of $5 million.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"Florida","charges"=>"18 USC 1956(h) money laundering conspiracy; 18 USC 1957 spending of criminally derived proceeds - March 2020 S.D. Florida indictment","arrest_date"=>null,"convicted"=>"Pending","sentence"=>"Pending"]]],

    ["name"=>"Tareck Zaidan El Aissami Maddah","first_name"=>"Tareck","middle_name"=>"Zaidan","last_name"=>"El Aissami Maddah","aka"=>"El Aissami","gender"=>"Male","race"=>"Middle Eastern / North African","state"=>"New York","era"=>"2020s","ideologies"=>["Bolivarianism"],"affiliation"=>["Venezuelan government","Cartel de los Soles"],"in_custody"=>true,"released"=>false,"description"=>"Tareck Zaidan El Aissami Maddah, former Venezuelan vice president and oil minister, was charged March 8, 2019 in the Southern District of New York with violating sanctions imposed under the Foreign Narcotics Kingpin Designation Act. Sanctioned by OFAC in February 2017 as a Specially Designated Narcotics Trafficker. State Department reward of $10 million.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"21 USC 1906 violating the Foreign Narcotics Kingpin Designation Act; 50 USC 1705 IEEPA sanctions evasion - March 2019 SDNY indictment","arrest_date"=>null,"convicted"=>"Pending","sentence"=>"Pending"]]],

    ["name"=>"Cliver Antonio Alcala Cordero","first_name"=>"Cliver","middle_name"=>"Antonio","last_name"=>"Alcala Cordero","gender"=>"Male","race"=>"Latino/Hispanic","state"=>"New York","era"=>"2020s","ideologies"=>["Bolivarianism"],"affiliation"=>["Venezuelan Armed Forces (retired)","Cartel de los Soles"],"in_custody"=>false,"released"=>true,"description"=>"Cliver Antonio Alcala Cordero, retired Venezuelan major general, was charged March 26, 2020 in SDNY with narco-terrorism alongside Nicolas Maduro. He surrendered to DEA agents in Colombia on March 27, 2020 and was extradited to the United States. Pleaded guilty in August 2023 to a narco-terrorism conspiracy count; sentenced March 5, 2024 to 262 months federal prison.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"21 USC 960a narco-terrorism conspiracy - March 2020 SDNY indictment, surrendered to DEA March 27 2020","arrest_date"=>"2020-03-27","incarceration_date"=>"2020-03-27","sentenced_date"=>"2024-03-05","convicted"=>"Yes - guilty plea","sentence"=>"262 months federal prison"]]],

    ["name"=>"Hugo Armando Carvajal Barrios","first_name"=>"Hugo","middle_name"=>"Armando","last_name"=>"Carvajal Barrios","aka"=>"El Pollo","gender"=>"Male","race"=>"Latino/Hispanic","state"=>"New York","era"=>"2020s","ideologies"=>["Bolivarianism"],"affiliation"=>["Venezuelan Military Intelligence (DGCIM, retired)"],"in_custody"=>true,"released"=>false,"description"=>"Hugo Armando Carvajal Barrios, former director of Venezuelan military intelligence under Hugo Chavez, was charged in 2011 and again in 2019 in SDNY with narco-terrorism conspiracy and FARC material support. Arrested in Spain in April 2019, fought extradition for four years, and was extradited to the United States on July 19, 2023. Pleaded guilty June 25, 2025 to narco-terrorism conspiracy and weapons charges; awaiting sentencing.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"21 USC 960a narco-terrorism; 21 USC 963 international cocaine importation conspiracy; 18 USC 924(c) firearms in furtherance of drug trafficking; 18 USC 2339B material support to FARC - 2011/2019 SDNY indictments, extradited from Spain July 19 2023","arrest_date"=>"2019-04-12","incarceration_date"=>"2023-07-19","convicted"=>"Yes - guilty plea June 25 2025","sentence"=>"Pending sentencing"]]],
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
echo "MADURO indictees + revisit batch import complete."
