#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/import_tpp_dc_nine.sh
#
# DC Nine / Handy et al. - October 22, 2020 federal civil rights and
# FACE Act blockade of an abortion clinic in Washington DC.
# Indicted October 14, 2022. All received Trump presidential pardons.
set -e

php artisan tinker --execute='
$rows = [
    ["name"=>"Lauren Handy","first_name"=>"Lauren","last_name"=>"Handy","aka"=>"Hazel Jenkins","gender"=>"Female","race"=>"White","state"=>"District of Columbia","era"=>"2020s","ideologies"=>["Anti-abortion","Christian Right"],"affiliation"=>["Progressive Anti-Abortion Uprising"],"in_custody"=>false,"released"=>true,"description"=>"Lauren Handy, 28, of Alexandria VA, directed the October 22, 2020 blockade of the Washington Surgi-Clinic abortion clinic in DC. Made a 9 a.m. appointment under the alias Hazel Jenkins to gain entry. Indicted October 14, 2022 on FACE Act and federal conspiracy-against-rights charges. Convicted at trial; sentenced to 57 months federal prison. Received a presidential pardon from Donald Trump in January 2025.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"District of Columbia","charges"=>"18 USC 241 Conspiracy Against Rights; 18 USC 248(a)(1) Freedom of Access to Clinic Entrances Act - Oct 22 2020 DC abortion clinic blockade","arrest_date"=>"2022-10-14","convicted"=>"Yes - jury verdict","sentence"=>"57 months federal prison; pardoned by Trump January 2025"]]],

    ["name"=>"Jonathan Darnel","first_name"=>"Jonathan","last_name"=>"Darnel","gender"=>"Male","race"=>"White","state"=>"District of Columbia","era"=>"2020s","ideologies"=>["Anti-abortion","Christian Right"],"affiliation"=>["Progressive Anti-Abortion Uprising"],"in_custody"=>false,"released"=>true,"description"=>"Jonathan Darnel, 40, of Arlington VA, DC Nine co-defendant; livestreamed the October 22, 2020 Washington Surgi-Clinic blockade on a Facebook event titled No one dies today. Convicted; 34 months federal prison. Pardoned by Trump January 2025.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"District of Columbia","charges"=>"18 USC 241 Conspiracy Against Rights; 18 USC 248(a)(1) FACE Act - Oct 22 2020 DC clinic blockade","arrest_date"=>"2022-10-14","convicted"=>"Yes - jury verdict","sentence"=>"34 months federal prison; pardoned by Trump January 2025"]]],

    ["name"=>"Paula Harlow","first_name"=>"Paula","last_name"=>"Harlow","aka"=>"Paulette","gender"=>"Female","race"=>"White","state"=>"District of Columbia","era"=>"2020s","ideologies"=>["Anti-abortion","Christian Right"],"affiliation"=>["Progressive Anti-Abortion Uprising"],"in_custody"=>false,"released"=>true,"description"=>"Paula Harlow, 73, of Massachusetts, DC Nine co-defendant in the October 22, 2020 Washington Surgi-Clinic blockade. Convicted; 24 months federal prison. Pardoned by Trump January 2025.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"District of Columbia","charges"=>"18 USC 241 Conspiracy Against Rights; 18 USC 248(a)(1) FACE Act - Oct 22 2020 DC clinic blockade","arrest_date"=>"2022-10-14","convicted"=>"Yes - jury verdict","sentence"=>"24 months federal prison; pardoned by Trump January 2025"]]],

    ["name"=>"Jean Marshall","first_name"=>"Jean","last_name"=>"Marshall","gender"=>"Female","race"=>"White","state"=>"District of Columbia","era"=>"2020s","ideologies"=>["Anti-abortion","Christian Right"],"affiliation"=>["Progressive Anti-Abortion Uprising"],"in_custody"=>false,"released"=>true,"description"=>"Jean Marshall, 72, of Kingston MA, DC Nine co-defendant in the October 22, 2020 Washington Surgi-Clinic blockade. Convicted; 24 months federal prison. Pardoned by Trump January 2025.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"District of Columbia","charges"=>"18 USC 241 Conspiracy Against Rights; 18 USC 248(a)(1) FACE Act - Oct 22 2020 DC clinic blockade","arrest_date"=>"2022-10-14","convicted"=>"Yes - jury verdict","sentence"=>"24 months federal prison; pardoned by Trump January 2025"]]],

    ["name"=>"John Hinshaw","first_name"=>"John","last_name"=>"Hinshaw","gender"=>"Male","race"=>"White","state"=>"District of Columbia","era"=>"2020s","ideologies"=>["Anti-abortion","Christian Right"],"affiliation"=>["Progressive Anti-Abortion Uprising"],"in_custody"=>false,"released"=>true,"description"=>"John Hinshaw, 67, of Levittown NY, DC Nine co-defendant in the October 22, 2020 Washington Surgi-Clinic blockade. Also faced charges for not leaving a Pennsylvania Planned Parenthood in August 2021. Convicted; 21 months federal prison. Pardoned by Trump January 2025.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"District of Columbia","charges"=>"18 USC 241 Conspiracy Against Rights; 18 USC 248(a)(1) FACE Act - Oct 22 2020 DC clinic blockade","arrest_date"=>"2022-10-14","convicted"=>"Yes - jury verdict","sentence"=>"21 months federal prison; pardoned by Trump January 2025"]]],

    ["name"=>"Heather Idoni","first_name"=>"Heather","last_name"=>"Idoni","gender"=>"Female","race"=>"White","state"=>"District of Columbia","era"=>"2020s","ideologies"=>["Anti-abortion","Christian Right"],"affiliation"=>["Progressive Anti-Abortion Uprising"],"in_custody"=>false,"released"=>true,"description"=>"Heather Idoni, 61, of Linden MI, DC Nine co-defendant in the October 22, 2020 Washington Surgi-Clinic blockade; charges deferred to a separate Tennessee FACE Act case (Gallagher cluster). Pardoned by Trump January 2025.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"District of Columbia","charges"=>"18 USC 241 Conspiracy Against Rights; 18 USC 248(a)(1) FACE Act - Oct 22 2020 DC clinic blockade","arrest_date"=>"2022-10-14","convicted"=>"No - DC charges deferred to Tennessee FACE case","sentence"=>"DC charges deferred; pardoned by Trump January 2025"]]],

    ["name"=>"William Goodman","first_name"=>"William","last_name"=>"Goodman","gender"=>"Male","race"=>"White","state"=>"District of Columbia","era"=>"2020s","ideologies"=>["Anti-abortion","Christian Right"],"affiliation"=>["Progressive Anti-Abortion Uprising"],"in_custody"=>false,"released"=>true,"description"=>"William Goodman, 52, of the Bronx NY, DC Nine co-defendant in the October 22, 2020 Washington Surgi-Clinic blockade. Also faced previous 2019 charges for resisting arrest at a Michigan anti-abortion protest. Convicted; 27 months federal prison. Pardoned by Trump January 2025.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"District of Columbia","charges"=>"18 USC 241 Conspiracy Against Rights; 18 USC 248(a)(1) FACE Act - Oct 22 2020 DC clinic blockade","arrest_date"=>"2022-10-14","convicted"=>"Yes - jury verdict","sentence"=>"27 months federal prison; pardoned by Trump January 2025"]]],

    ["name"=>"Joan Bell","first_name"=>"Joan","last_name"=>"Bell","aka"=>"Joan Andrews-Bell","gender"=>"Female","race"=>"White","state"=>"District of Columbia","era"=>"2020s","ideologies"=>["Anti-abortion","Christian Right"],"affiliation"=>["Progressive Anti-Abortion Uprising"],"in_custody"=>false,"released"=>true,"description"=>"Joan Bell (also Joan Andrews-Bell), 74, of Montague NJ, DC Nine co-defendant in the October 22, 2020 Washington Surgi-Clinic blockade. Convicted; 27 months federal prison. Pardoned by Trump January 2025.","cases"=>[["institution_name"=>"Federal Bureau of Prisons (location varied)","institution_state"=>"District of Columbia","charges"=>"18 USC 241 Conspiracy Against Rights; 18 USC 248(a)(1) FACE Act - Oct 22 2020 DC clinic blockade","arrest_date"=>"2022-10-14","convicted"=>"Yes - jury verdict","sentence"=>"27 months federal prison; pardoned by Trump January 2025"]]],
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
echo "DC Nine FACE Act import complete."
