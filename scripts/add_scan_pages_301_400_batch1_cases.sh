#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_scan_pages_301_400_batch1_cases.sh
# Attaches PrisonerCase records to the 11 prisoners added by batch1 who already
# exist in the database but were created without cases.
set +e

cat > /tmp/add_batch1_cases.php << 'PHPEOF'
<?php

function addCase(string $name, array $c): void
{
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where('name', $name)->first();
    if (!$p) {
        echo 'NOT FOUND: ' . $name . PHP_EOL;
        return;
    }
    if ($p->cases()->count() > 0) {
        echo 'SKIP (cases already exist): ' . $name . PHP_EOL;
        return;
    }

    $instFields = ['name' => $c['inst_name']];
    if (!empty($c['inst_city']))  $instFields['city']  = $c['inst_city'];
    if (!empty($c['inst_state'])) $instFields['state'] = $c['inst_state'];
    $inst = \App\Models\Institution::firstOrCreate($instFields);

    $cf = [
        'institution_id' => $inst->id,
        'charges'        => $c['charges'],
        'convicted'      => $c['convicted'],
        'sentence'       => $c['sentence'],
    ];
    if (!empty($c['arrest_date']))        $cf['arrest_date']        = $c['arrest_date'];
    if (!empty($c['incarceration_date'])) $cf['incarceration_date'] = $c['incarceration_date'];
    if (!empty($c['release_date']))       $cf['release_date']       = $c['release_date'];
    if (isset($c['imprisoned_for_days'])) $cf['imprisoned_for_days'] = $c['imprisoned_for_days'];
    if (!empty($c['judge']))              $cf['judge']              = $c['judge'];
    if (!empty($c['prosecutor']))         $cf['prosecutor']         = $c['prosecutor'];

    $p->cases()->save(new \App\Models\PrisonerCase($cf));
    echo 'Added case: ' . $name . PHP_EOL;
}

// 1. Brandon Pack
addCase('Brandon Pack', [
    'inst_name'          => 'Ohio Department of Rehabilitation and Correction',
    'charges'            => 'Aggravated riot; vandalism; breaking and entering; three counts of felonious assault on police officers — arising from George Floyd/BLM protest, Columbus, Ohio, May 2020',
    'arrest_date'        => '2020-05-30',
    'incarceration_date' => '2020-05-30',
    'imprisoned_for_days' => 2224,
    'convicted'          => 'Yes — jury verdict',
    'sentence'           => '8 to 9.5 years Ohio state prison',
]);

// 2. Jesse Cannon
addCase('Jesse Cannon', [
    'inst_name'          => 'California Department of Corrections and Rehabilitation',
    'charges'            => 'Conspiracy to riot (California Penal Code § 404.6); assault; unlawful use of tear gas — arising from January 9, 2021 counter-protest of Patriot March, San Diego, California',
    'arrest_date'        => '2021-01-09',
    'incarceration_date' => '2024-01-01',
    'imprisoned_for_days' => 913,
    'convicted'          => 'Yes — guilty plea (January 2024)',
    'sentence'           => '5 years California state prison',
]);

// 3. Jacqueline Quimby
addCase('Jacqueline Quimby', [
    'inst_name'          => 'West Virginia Regional Jail',
    'charges'            => 'Trespassing; conspiracy; obstruction — for blocking a road used by Massey Energy for coal transport as part of an anti-mountaintop-removal direct action, West Virginia, 2010',
    'incarceration_date' => '2010-01-01',
    'imprisoned_for_days' => 60,
    'convicted'          => 'Yes',
    'sentence'           => '60 days jail',
]);

// 4. Kevin Chianella
addCase('Kevin Chianella', [
    'inst_name'          => 'Correctional Service of Canada',
    'inst_city'          => 'Ontario',
    'inst_state'         => 'Canada',
    'charges'            => '53 counts including mischief, assault with a weapon, arson, and break-and-enter — G-20 Summit protests, Toronto, Canada, June 2010; extradited from the United States to Canada',
    'arrest_date'        => '2010-06-26',
    'imprisoned_for_days' => 730,
    'convicted'          => 'Yes',
    'sentence'           => '2 years Canadian federal prison',
]);

// 5. Quinn McCormic
addCase('Quinn McCormic', [
    'inst_name'          => 'Ontario Ministry of Correctional Services',
    'inst_city'          => 'Ontario',
    'inst_state'         => 'Canada',
    'charges'            => 'Mischief causing property damage ($125,000 to retail stores, bank, and museum) — G-20 Summit protests, Toronto, Canada, June 2010; extradited from the United States to Canada',
    'arrest_date'        => '2010-06-26',
    'imprisoned_for_days' => 122,
    'convicted'          => 'Yes',
    'sentence'           => '4 months Canadian provincial jail',
]);

// 6. Richard Morano
addCase('Richard Morano', [
    'inst_name'          => 'Ontario Ministry of Correctional Services',
    'inst_city'          => 'Ontario',
    'inst_state'         => 'Canada',
    'charges'            => 'Mischief endangering life (broke police car window and storefront window) — G-20 Summit protests, Toronto, Canada, June 2010; extradited from the United States in 2014',
    'arrest_date'        => '2010-06-26',
    'incarceration_date' => '2014-01-01',
    'imprisoned_for_days' => 213,
    'convicted'          => 'Yes',
    'sentence'           => '7 months Canadian provincial jail; 2-year ban from Toronto',
]);

// 7. Kellen Sorber
addCase('Kellen Sorber', [
    'inst_name'          => 'Federal Bureau of Prisons',
    'charges'            => 'Arson of a building used in interstate commerce (18 U.S.C. § 844(i)) — for setting fire to the Wyoming Republican Party headquarters building in Laramie, October 2018',
    'arrest_date'        => '2018-10-01',
    'imprisoned_for_days' => 1339,
    'convicted'          => 'Yes — guilty plea',
    'sentence'           => '44 months federal prison',
]);

// 8. Martino Andrews
addCase('Martino Andrews', [
    'inst_name'          => 'Federal Bureau of Prisons',
    'charges'            => 'Conspiracy to commit arson (18 U.S.C. § 844) — for torching a county van during May 30, 2020 George Floyd/BLM protests in Columbus, Ohio',
    'arrest_date'        => '2020-05-30',
    'imprisoned_for_days' => 1095,
    'convicted'          => 'Yes — guilty plea',
    'sentence'           => '3.5 years (42 months) federal prison',
]);

// 9. Daniel Baker
addCase('Daniel Baker', [
    'inst_name'          => 'Federal Bureau of Prisons',
    'charges'            => 'Transmitting a threat in interstate commerce (18 U.S.C. § 875(c)) — for posting a Facebook Live video threatening Proud Boys in January 2021; context includes participation in Seattle CHAZ/CHOP autonomous zone (2020)',
    'arrest_date'        => '2021-01-15',
    'incarceration_date' => '2022-01-01',
    'imprisoned_for_days' => 1122,
    'convicted'          => 'Yes — guilty plea',
    'sentence'           => '44 months federal prison',
]);

// 10. Alissa Azar
addCase('Alissa Azar', [
    'inst_name'          => 'Clackamas County Jail',
    'inst_city'          => 'Oregon City',
    'inst_state'         => 'Oregon',
    'charges'            => 'Felony riot; disorderly conduct; attempted use of tear gas (hung jury on last count) — June 18, 2021 antifa vs. Proud Boys confrontation at Clackamette Park, Oregon City, Oregon',
    'incarceration_date' => '2024-08-01',
    'imprisoned_for_days' => 14,
    'convicted'          => 'Yes — jury verdict (felony riot and disorderly conduct; August 2024)',
    'sentence'           => '14 days jail; 36 months supervised probation; GPS monitoring',
]);

// 11. Sergey Turzhanskiy
addCase('Sergey Turzhanskiy', [
    'inst_name'          => 'Federal Bureau of Prisons',
    'charges'            => 'Federal arson — for throwing a Molotov cocktail at a Portland Police Bureau vehicle during an anti-fascist demonstration, Portland, Oregon, 2012',
    'incarceration_date' => '2013-01-01',
    'imprisoned_for_days' => 913,
    'convicted'          => 'Yes',
    'sentence'           => 'Approximately 2.5 years federal prison',
]);

echo PHP_EOL . 'Done.' . PHP_EOL;
PHPEOF

echo "Attaching cases to 11 existing batch1 prisoners..."
php artisan tinker --execute="require '/tmp/add_batch1_cases.php';"
echo "Script complete."
