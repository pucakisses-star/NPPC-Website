#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/import_tpp_infrastructure.sh
#
# Infrastructure-attack defendants (TPP rows 678-683):
# - James E. Robinson AkronPhoenix420 (already in DB from batch 7)
# - Galen Sol Shireman-Grabowski (Mountain Valley Pipeline helicopter)
# - Ellen Brennan Reiche & Samantha Frances Brooks (BNSF shunt)
# - Mylene Vialard (Line 3 pipeline)
# - Peter Karasev (San Jose energy facility bombing, 126 mo)
set -e

php artisan tinker --execute='
$rows = [
    ["name"=>"James E. Robinson","first_name"=>"James","middle_name"=>"E.","last_name"=>"Robinson","aka"=>"AkronPhoenix420","gender"=>"Male","race"=>"White","state"=>"Ohio","era"=>"2010s","ideologies"=>["Anti-police","Hacktivism"],"affiliation"=>["Anonymous"],"in_custody"=>false,"released"=>true,"description"=>"James E. Robinson, 33, of Akron OH, was indicted October 24, 2018 for a series of denial-of-service attacks that shut down akronohio.gov and akroncops.org on August 1, 2017. Twitter user AkronPhoenix420 claimed responsibility using #Anonymous and #TangoDown, with a Guy Fawkes mask video stating it was to teach the law a lesson for abusing the law. Previous 2018 DDoS attacks targeted Ohio DPS, NIH, DISA, DoD, and Treasury. Sentenced to 72 months federal prison + $668,684 restitution.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"Ohio","charges"=>"18 USC 1030(a)(5)(A) Damaging Protected Computers; 18 USC 1030(c)(4)(B) Damaging Protected Computers - Aug 2017 Akron PD DDoS","arrest_date"=>"2018-10-24","convicted"=>"Yes - guilty plea","sentence"=>"72 months federal prison + $668,684 restitution"]]],

    ["name"=>"Galen Sol Shireman-Grabowski","first_name"=>"Galen","middle_name"=>"Sol","last_name"=>"Shireman-Grabowski","gender"=>"Male","race"=>"White","state"=>"Virginia","era"=>"2010s","ideologies"=>["Environmental Activism","Anti-pipeline"],"affiliation"=>["Appalachians Against Pipelines"],"in_custody"=>false,"released"=>true,"description"=>"Galen Sol Shireman-Grabowski, 24, locked himself to a helicopter used by Mountain Valley Pipeline crews in Ellison VA in October 2019, damaging the helicopter. Charged with 2 felonies and 4 misdemeanors. Pleaded guilty; 12-month suspended sentence + $14,030.41 restitution + community service. Felony destruction of property reduced to misdemeanor on completion.","cases"=>[["institution_name"=>"Virginia state court","institution_state"=>"Virginia","charges"=>"Va Code 5.1-22 Interference With Aircraft Operation; Va Code 18.2-137 Destruction of Property; Va Code 18.2-422 Wearing of Masks; Va Code 18.2-460 Obstructing Justice; Va Code 18.2-404 Obstructing Free Passage; Va Code 18.2-146 Tampering With Vehicle/Aircraft - Oct 2019 MVP helicopter lockdown","arrest_date"=>"2020-04-28","convicted"=>"Yes - guilty plea","sentence"=>"12-month suspended sentence + $14,030 restitution"]]],

    ["name"=>"Ellen Brennan Reiche","first_name"=>"Ellen","middle_name"=>"Brennan","last_name"=>"Reiche","gender"=>"Female","race"=>"White","state"=>"Washington","era"=>"2020s","ideologies"=>["Indigenous Sovereignty","Anti-pipeline","Environmental Activism"],"in_custody"=>false,"released"=>true,"description"=>"Ellen Brennan Reiche, 28, of Bellingham WA, with co-defendant Samantha Frances Brooks, placed shunts on BNSF Railway tracks near Bellingham on November 28, 2020 to disrupt rail signaling and BNSF operations supplying the Coastal GasLink pipeline. Acted in solidarity with the Wetsuweten Nation against pipeline construction across British Columbia. Pleaded guilty; 12 months and 1 day federal prison + 3 years supervised release.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"Washington","charges"=>"18 USC 1992(a)(5) Terrorist Attacks and Other Violence Against Railroad Carriers - Nov 2020 BNSF Bellingham track shunt","arrest_date"=>"2020-12-09","convicted"=>"Yes - guilty plea","sentence"=>"12 months and 1 day federal prison + 3 yr supervised release"]]],

    ["name"=>"Samantha Frances Brooks","first_name"=>"Samantha","middle_name"=>"Frances","last_name"=>"Brooks","gender"=>"Female","race"=>"White","state"=>"Washington","era"=>"2020s","ideologies"=>["Indigenous Sovereignty","Anti-pipeline","Environmental Activism"],"in_custody"=>false,"released"=>true,"description"=>"Samantha Frances Brooks, 24, co-defendant of Ellen Brennan Reiche in the November 28, 2020 BNSF Bellingham track-shunting in solidarity with the Wetsuweten Nation. Pleaded guilty; 6 months federal prison + 3 years supervised release.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"Washington","charges"=>"18 USC 1992(a)(5) Terrorist Attacks and Other Violence Against Railroad Carriers - Nov 2020 BNSF Bellingham track shunt","arrest_date"=>"2020-12-09","convicted"=>"Yes - guilty plea","sentence"=>"6 months federal prison + 3 yr supervised release"]]],

    ["name"=>"Mylene Vialard","first_name"=>"Mylene","last_name"=>"Vialard","gender"=>"Female","race"=>"White","state"=>"Minnesota","era"=>"2020s","ideologies"=>["Indigenous Sovereignty","Anti-pipeline","Environmental Activism"],"in_custody"=>false,"released"=>true,"description"=>"Mylene Vialard, 54, attached herself to a 25-foot bamboo tower blocking a Line 3 pipeline pumping station in Aitkin County MN on August 21, 2021 in solidarity with the water protectors opposing the pipeline through Indigenous territory. Convicted of felony obstruction; 2 days county jail + 1 year probation.","cases"=>[["institution_name"=>"Aitkin County Jail","institution_state"=>"Minnesota","charges"=>"Minn Stat 609.50.1(2) Obstructing Legal Process (Felony) - Aug 2021 Line 3 pumping station blockade","arrest_date"=>"2021-08-27","convicted"=>"Yes on felony obstruction; trespass charge dropped","sentence"=>"2 days county jail + 1 year probation"]]],

    ["name"=>"Peter Karasev","first_name"=>"Peter","last_name"=>"Karasev","gender"=>"Male","race"=>"White","state"=>"California","era"=>"2020s","ideologies"=>["Anti-Russia-Ukraine-War"],"in_custody"=>true,"released"=>false,"description"=>"Peter Karasev, 38, US citizen of San Jose CA, was indicted October 19, 2023 for two willful destructions of San Jose energy facilities and use of fire/explosives to commit a federal crime. Prosecutors said he was motivated by the Russia-Ukraine war, though a precise ideological position is unclear. Pleaded guilty April 29, 2025; sentenced to 126 months federal prison.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"California","charges"=>"18 USC 1366(a) Destruction of an Energy Facility [2 counts]; 18 USC 844(h) Use of Fire or Explosive to Commit a Federal Felony - 2022-2023 San Jose energy facility bombings","arrest_date"=>"2023-10-19","sentenced_date"=>"2025-04-29","convicted"=>"Yes - guilty plea","sentence"=>"126 months federal prison"]]],
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
echo "Infrastructure-attack import complete."
