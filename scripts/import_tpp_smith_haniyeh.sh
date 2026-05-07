#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/import_tpp_smith_haniyeh.sh
#
# Cameron Monte Smith (TPP rows 691-692, ND/SD energy facility
# attacks, 25 yr total) plus the three Hamas leaders charged in
# SDNY February 2024 for the October 7, 2023 attacks (rows 698-700).
# All three Hamas defendants were killed before trial.
set -e

php artisan tinker --execute='
$rows = [
    ["name"=>"Cameron Monte Smith","first_name"=>"Cameron","middle_name"=>"Monte","last_name"=>"Smith","gender"=>"Male","race"=>"White","state"=>"North Dakota","era"=>"2020s","ideologies"=>["Indigenous Sovereignty","Anti-pipeline","Environmental Activism"],"in_custody"=>true,"released"=>false,"description"=>"Cameron Monte Smith, 49, a Canadian citizen in the US illegally, fired shots at a Keystone Pipeline transformer/pump station near Carpenter SD in July 2022, then fired shots into the Wheelock Substation near Ray ND in May 2023, knocking out power for 240+ people and causing $1.2 million in damage. Officers observed DAPL spray-painted near the substation. His defense described him as wanting to create awareness about climate change. Pleaded guilty in both ND and SD to destruction of an energy facility; sentenced to 150 months in each district to run consecutively (300 months total / 25 years). The federal judge found his crimes met the definition of terrorism; prosecuted by the National Security Division Counterterrorism Section.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"North Dakota","charges"=>"18 USC 1366(a) Destruction of an Energy Facility; 18 USC 922(g)(5)(A) Possession of Firearm by an Illegal Alien; 18 USC 924(a)(8) Possession of Ammunition by an Illegal Alien - May 2023 Wheelock Substation shooting near Ray ND","arrest_date"=>"2023-07-12","convicted"=>"Yes - guilty plea","sentence"=>"150 months federal prison (ND, consecutive to SD)"],["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"South Dakota","charges"=>"18 USC 1366(a) Destruction of an Energy Facility - July 2022 Keystone Pipeline transformer/pump station shooting near Carpenter SD","arrest_date"=>"2024-06-17","convicted"=>"Yes - guilty plea","sentence"=>"150 months federal prison (SD, consecutive to ND); 25 years total"]]],

    ["name"=>"Ismail Haniyeh","first_name"=>"Ismail","last_name"=>"Haniyeh","gender"=>"Male","race"=>"Middle Eastern / North African","state"=>"New York","era"=>"2020s","ideologies"=>["Salafi/Jihadist/Islamist","Pro-Palestine","Anti-Israel"],"affiliation"=>["HAMAS"],"in_custody"=>false,"released"=>false,"death_date"=>"2024-07-31","description"=>"Ismail Haniyeh, 61, top political leader of Hamas, was charged February 1, 2024 in SDNY for the October 7, 2023 attacks (1,195 killed including Americans; 14,970 wounded). Charges include conspiring to murder US nationals, use weapons of mass destruction, bomb a place of public use, provide material support to a foreign terrorist organization, and finance terrorism. Never tried; assassinated July 31, 2024 by an explosive device covertly smuggled into the Tehran guesthouse where he was staying. Iranian officials and Hamas attributed the killing to Israel.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"18 USC 2332(b) Conspiring to Murder US Nationals; 18 USC 2332a Conspiring to Use a WMD; 18 USC 2332f Conspiring to Bomb a Place of Public Use; 18 USC 2339A Providing Material Support to Terrorists; 18 USC 2339B Material Support to FTO (HAMAS); 18 USC 2339C Financing Terrorism; 50 USC 1705 IEEPA - October 7 2023 attacks","arrest_date"=>"2024-02-01","death_in_custody_date"=>"2024-07-31","convicted"=>"No - charged but not tried; assassinated before extradition","sentence"=>"Killed in Tehran July 31, 2024"]]],

    ["name"=>"Yahya Sinwar","first_name"=>"Yahya","last_name"=>"Sinwar","aka"=>"Abu Ibrahim","gender"=>"Male","race"=>"Middle Eastern / North African","state"=>"New York","era"=>"2020s","ideologies"=>["Salafi/Jihadist/Islamist","Pro-Palestine","Anti-Israel"],"affiliation"=>["HAMAS"],"in_custody"=>false,"released"=>false,"death_date"=>"2024-10-16","description"=>"Yahya Sinwar, 61, leader of Hamas in Gaza after Haniyehs death, was charged February 1, 2024 in SDNY for the October 7, 2023 attacks. Never tried; killed by Israeli troops in Rafah on October 16, 2024.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"18 USC 2332(b) Conspiring to Murder US Nationals; 18 USC 2332a Conspiring to Use a WMD; 18 USC 2332f Conspiring to Bomb a Place of Public Use; 18 USC 2339A; 18 USC 2339B Material Support to FTO (HAMAS); 18 USC 2339C; 50 USC 1705 IEEPA - October 7 2023 attacks","arrest_date"=>"2024-02-01","death_in_custody_date"=>"2024-10-16","convicted"=>"No - charged but not tried; killed before extradition","sentence"=>"Killed in Rafah October 16, 2024"]]],

    ["name"=>"Mohammad Al-Masri","first_name"=>"Mohammad","last_name"=>"Al-Masri","aka"=>"Mohammed Deif, al Khalid al-Deif","gender"=>"Male","race"=>"Middle Eastern / North African","state"=>"New York","era"=>"2020s","ideologies"=>["Salafi/Jihadist/Islamist","Pro-Palestine","Anti-Israel"],"affiliation"=>["HAMAS","Al-Qassam Brigades"],"in_custody"=>false,"released"=>false,"death_date"=>"2024-07-13","description"=>"Mohammad Al-Masri (Mohammed Deif), 58, longtime commander of the Hamas Al-Qassam Brigades military wing, was charged February 1, 2024 in SDNY for the October 7, 2023 attacks. Never tried; Hamas spokesperson Abu Obeida announced January 30, 2025 that Israel had killed him (Israeli strike on July 13, 2024).","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"18 USC 2332(b) Conspiring to Murder US Nationals; 18 USC 2332a Conspiring to Use a WMD; 18 USC 2332f Conspiring to Bomb a Place of Public Use; 18 USC 2339A; 18 USC 2339B Material Support to FTO (HAMAS); 18 USC 2339C; 50 USC 1705 IEEPA - October 7 2023 attacks","arrest_date"=>"2024-02-01","death_in_custody_date"=>"2024-07-13","convicted"=>"No - charged but not tried; killed before extradition","sentence"=>"Killed in Israeli strike July 13, 2024 (death announced by Hamas Jan 30, 2025)"]]],
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
echo "Cameron Smith + Haniyeh/Sinwar/Deif import complete."
