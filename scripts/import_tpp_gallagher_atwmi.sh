#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/import_tpp_gallagher_atwmi.sh
#
# Gallagher et al. - March 5, 2021 Mt. Juliet TN FACE Act blockade
# (rows 145-154 of TPP) plus Bevelyn Beatty Williams / Edmee Chavannes
# At The Well Ministries June 2020 Manhattan Health Center blockade
# (rows 155-156). All Gallagher defendants and Williams pardoned by
# Trump January 23, 2025.
# Heather Idoni already in DB (DC Nine PR #135); her TN case is added
# as a new case on her existing prisoner record.
set -e

php artisan tinker --execute='
$rows = [
    ["name"=>"Chester Gallagher","first_name"=>"Chester","last_name"=>"Gallagher","gender"=>"Male","race"=>"White","state"=>"Tennessee","era"=>"2020s","ideologies"=>["Anti-abortion","Christian Right"],"in_custody"=>false,"released"=>true,"description"=>"Chester Gallagher, 73, lead defendant in the March 5, 2021 Mt. Juliet TN reproductive-health clinic blockade by 20-25 anti-abortion protesters; 11 stayed past police dispersal warning and were charged under FACE Act. Convicted; 16 months federal prison. Pardoned by Trump January 23, 2025.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"Tennessee","charges"=>"18 USC 241 Conspiracy Against Rights; 18 USC 248(a)(1)(2) FACE Act - March 5 2021 Mt. Juliet TN clinic blockade","arrest_date"=>"2022-10-03","convicted"=>"Yes - jury verdict","sentence"=>"16 months federal prison; pardoned by Trump January 2025"]]],

    ["name"=>"Calvin Zastrow","first_name"=>"Calvin","last_name"=>"Zastrow","gender"=>"Male","race"=>"White","state"=>"Tennessee","era"=>"2020s","ideologies"=>["Anti-abortion","Christian Right"],"in_custody"=>false,"released"=>true,"description"=>"Calvin Zastrow, Gallagher cluster co-defendant in the March 5, 2021 Mt. Juliet TN clinic blockade. Convicted; 6 months federal prison. Pardoned by Trump January 23, 2025.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"Tennessee","charges"=>"18 USC 241 Conspiracy Against Rights; 18 USC 248(a)(1)(2) FACE Act - March 5 2021 Mt. Juliet TN clinic blockade","arrest_date"=>"2022-10-03","convicted"=>"Yes - jury verdict","sentence"=>"6 months federal prison; pardoned by Trump January 2025"]]],

    ["name"=>"Coleman Boyd","first_name"=>"Coleman","last_name"=>"Boyd","gender"=>"Male","race"=>"White","state"=>"Tennessee","era"=>"2020s","ideologies"=>["Anti-abortion","Christian Right"],"in_custody"=>false,"released"=>true,"description"=>"Coleman Boyd, Gallagher cluster co-defendant in the March 5, 2021 Mt. Juliet TN clinic blockade. Convicted; probation, no prison time. Pardoned by Trump January 23, 2025.","cases"=>[["institution_name"=>"US District Court Middle District of Tennessee","institution_state"=>"Tennessee","charges"=>"18 USC 241 Conspiracy Against Rights; 18 USC 248(a)(1)(2) FACE Act - March 5 2021 Mt. Juliet TN clinic blockade","arrest_date"=>"2022-10-03","convicted"=>"Yes - jury verdict","sentence"=>"Probation; pardoned by Trump January 2025"]]],

    ["name"=>"Eva Edl","first_name"=>"Eva","last_name"=>"Edl","gender"=>"Female","race"=>"White","state"=>"Tennessee","era"=>"2020s","ideologies"=>["Anti-abortion","Christian Right"],"in_custody"=>false,"released"=>true,"description"=>"Eva Edl, Gallagher cluster co-defendant in the March 5, 2021 Mt. Juliet TN clinic blockade. Convicted; probation. Pardoned by Trump January 23, 2025.","cases"=>[["institution_name"=>"US District Court Middle District of Tennessee","institution_state"=>"Tennessee","charges"=>"18 USC 248(a)(1)(2) FACE Act - March 5 2021 Mt. Juliet TN clinic blockade","arrest_date"=>"2022-10-03","convicted"=>"Yes - jury verdict","sentence"=>"Probation; pardoned by Trump January 2025"]]],

    ["name"=>"Eva Zastrow","first_name"=>"Eva","last_name"=>"Zastrow","gender"=>"Female","race"=>"White","state"=>"Tennessee","era"=>"2020s","ideologies"=>["Anti-abortion","Christian Right"],"in_custody"=>false,"released"=>true,"description"=>"Eva Zastrow, Gallagher cluster co-defendant in the March 5, 2021 Mt. Juliet TN clinic blockade. Convicted; probation. Pardoned by Trump January 23, 2025.","cases"=>[["institution_name"=>"US District Court Middle District of Tennessee","institution_state"=>"Tennessee","charges"=>"18 USC 248(a)(1)(2) FACE Act - March 5 2021 Mt. Juliet TN clinic blockade","arrest_date"=>"2022-10-03","convicted"=>"Yes - jury verdict","sentence"=>"Probation; pardoned by Trump January 2025"]]],

    ["name"=>"James Zastrow","first_name"=>"James","last_name"=>"Zastrow","gender"=>"Male","race"=>"White","state"=>"Tennessee","era"=>"2020s","ideologies"=>["Anti-abortion","Christian Right"],"in_custody"=>false,"released"=>true,"description"=>"James Zastrow, Gallagher cluster co-defendant in the March 5, 2021 Mt. Juliet TN clinic blockade. Convicted; probation. Pardoned by Trump January 23, 2025.","cases"=>[["institution_name"=>"US District Court Middle District of Tennessee","institution_state"=>"Tennessee","charges"=>"18 USC 248(a)(1)(2) FACE Act - March 5 2021 Mt. Juliet TN clinic blockade","arrest_date"=>"2022-10-03","convicted"=>"Yes - jury verdict","sentence"=>"Probation; pardoned by Trump January 2025"]]],

    ["name"=>"Paul Place","first_name"=>"Paul","last_name"=>"Place","gender"=>"Male","race"=>"White","state"=>"Tennessee","era"=>"2020s","ideologies"=>["Anti-abortion","Christian Right"],"in_custody"=>false,"released"=>true,"description"=>"Paul Place, Gallagher cluster co-defendant in the March 5, 2021 Mt. Juliet TN clinic blockade. Convicted; probation. Pardoned by Trump January 23, 2025.","cases"=>[["institution_name"=>"US District Court Middle District of Tennessee","institution_state"=>"Tennessee","charges"=>"18 USC 248(a)(1)(2) FACE Act - March 5 2021 Mt. Juliet TN clinic blockade","arrest_date"=>"2022-10-03","convicted"=>"Yes - jury verdict","sentence"=>"Probation; pardoned by Trump January 2025"]]],

    ["name"=>"Paul Vaughn","first_name"=>"Paul","last_name"=>"Vaughn","gender"=>"Male","race"=>"White","state"=>"Tennessee","era"=>"2020s","ideologies"=>["Anti-abortion","Christian Right"],"in_custody"=>false,"released"=>true,"description"=>"Paul Vaughn, Gallagher cluster co-defendant in the March 5, 2021 Mt. Juliet TN clinic blockade. Convicted; time served + 3 years probation. Pardoned by Trump January 23, 2025.","cases"=>[["institution_name"=>"US District Court Middle District of Tennessee","institution_state"=>"Tennessee","charges"=>"18 USC 241 Conspiracy Against Rights; 18 USC 248(a)(1)(2) FACE Act - March 5 2021 Mt. Juliet TN clinic blockade","arrest_date"=>"2022-10-03","convicted"=>"Yes - jury verdict","sentence"=>"Time served + 3 yr probation; pardoned by Trump January 2025"]]],

    ["name"=>"Dennis Green","first_name"=>"Dennis","last_name"=>"Green","gender"=>"Male","race"=>"White","state"=>"Tennessee","era"=>"2020s","ideologies"=>["Anti-abortion","Christian Right"],"in_custody"=>false,"released"=>true,"description"=>"Dennis Green, Gallagher cluster co-defendant in the March 5, 2021 Mt. Juliet TN clinic blockade. Convicted; time served (~0.5 months). Pardoned by Trump January 23, 2025.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"Tennessee","charges"=>"18 USC 248(a)(1)(2) FACE Act - March 5 2021 Mt. Juliet TN clinic blockade","arrest_date"=>"2022-10-03","convicted"=>"Yes - jury verdict","sentence"=>"Time served; pardoned by Trump January 2025"]]],

    ["name"=>"Bevelyn Beatty Williams","first_name"=>"Bevelyn","middle_name"=>"Beatty","last_name"=>"Williams","gender"=>"Female","race"=>"Black","state"=>"New York","era"=>"2020s","ideologies"=>["Anti-abortion","Christian Right"],"affiliation"=>["At The Well Ministries"],"in_custody"=>false,"released"=>true,"description"=>"Bevelyn Beatty Williams, 31, of Ooltewah TN, co-founder of At The Well Ministries, was sentenced July 24, 2024 to 41 months federal prison for FACE Act conspiracy and a violation resulting in bodily injury at the Manhattan Health Centre in June 2020. Indicted with Edmee Chavannes. Pardoned by Trump January 24, 2025.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"New York","charges"=>"18 USC 371 Conspiracy + 248(a)(1) FACE Act Conspiracy; 18 USC 248(b)(2) FACE Act Resulting in Bodily Injury - June 2020 Manhattan Health Center blockade","arrest_date"=>"2022-12-01","sentenced_date"=>"2024-07-24","convicted"=>"Yes - jury verdict","sentence"=>"41 months federal prison; pardoned by Trump January 2025"]]],

    ["name"=>"Edmee Chavannes","first_name"=>"Edmee","last_name"=>"Chavannes","gender"=>"Female","race"=>"Black","state"=>"New York","era"=>"2020s","ideologies"=>["Anti-abortion","Christian Right"],"affiliation"=>["At The Well Ministries"],"in_custody"=>false,"released"=>true,"description"=>"Edmee Chavannes, 41, of Ooltewah TN, co-founder of At The Well Ministries; indicted December 2022 alongside Bevelyn Beatty Williams for the June 2020 Manhattan Health Centre FACE Act case. Found not guilty at trial.","cases"=>[["institution_name"=>"US District Court Southern District of New York","institution_state"=>"New York","charges"=>"18 USC 371 + 248(a)(1) FACE Act Conspiracy; 18 USC 248(b)(2) FACE Act Violation - June 2020 Manhattan Health Center blockade","arrest_date"=>"2022-12-01","convicted"=>"No - acquitted","sentence"=>"Acquitted"]]],
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

echo "\n--- Adding TN Gallagher case to existing Heather Idoni record ---\n";
$idoni = App\Models\Prisoner::where("name", "Heather Idoni")->first();
if ($idoni) {
    $tnCaseExists = $idoni->cases()->where("charges", "like", "%Mt. Juliet%")->exists();
    if ($tnCaseExists) {
        echo "Idoni TN case already present, skipping\n";
    } else {
        $inst = App\Models\Institution::firstOrCreate(
            ["name" => "Federal Bureau of Prisons (location varied)"],
            ["state" => "Tennessee"]
        );
        try {
            App\Models\PrisonerCase::create([
                "prisoner_id"   => $idoni->id,
                "institution_id"=> $inst->id,
                "charges"       => "18 USC 241 Conspiracy Against Rights; 18 USC 248(a)(1)(2) FACE Act - March 5 2021 Mt. Juliet TN clinic blockade (Gallagher cluster, charges deferred from DC Nine)",
                "arrest_date"   => "2022-10-03",
                "convicted"     => "Yes - jury verdict",
                "sentence"      => "8 months federal prison; pardoned by Trump January 2025",
            ]);
            echo "Added TN Gallagher case to Heather Idoni\n";
        } catch (\Throwable $e) {
            echo "ERROR adding Idoni TN case: {$e->getMessage()}\n";
        }
    }
} else {
    echo "Heather Idoni not found in DB - run DC Nine import first\n";
}

echo "\nSummary: created={$created}, existing={$existing}, errors={$errors}\n";
'

echo
echo "Gallagher TN + ATWMI FACE Act import complete."
