#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_scan_pages_500_600_batch3.sh
# Adds 6 more confirmed imprisoned political prisoners from antifawatch.net scan
# pages 500–599 (third pass — Rochester NY cluster, DC explosives case, SLC case).
set +e

cat > /tmp/add_pages_500_600_batch3.php << 'PHPEOF'
<?php

function addPrisoner(array $data): void
{
    $name = $data['name'];
    $checkNames = $data['check_names'] ?? [$name];
    foreach ($checkNames as $checkName) {
        if (\App\Models\Prisoner::withoutGlobalScopes()->where('name', $checkName)->exists()) {
            echo 'SKIP (already exists as "' . $checkName . '"): ' . $name . PHP_EOL;
            return;
        }
    }

    $cases = $data['cases'] ?? [];
    unset($data['cases'], $data['check_names']);

    $p = \App\Models\Prisoner::create($data);

    foreach ($cases as $c) {
        $instFields = ['name' => $c['institution_name']];
        if (!empty($c['institution_city']))  $instFields['city']  = $c['institution_city'];
        if (!empty($c['institution_state'])) $instFields['state'] = $c['institution_state'];
        $inst = \App\Models\Institution::firstOrCreate($instFields);

        $cf = [
            'institution_id' => $inst->id,
            'charges'        => $c['charges'],
            'convicted'      => $c['convicted'],
            'sentence'       => $c['sentence'],
        ];
        if (!empty($c['arrest_date']))         $cf['arrest_date']         = $c['arrest_date'];
        if (!empty($c['incarceration_date']))  $cf['incarceration_date']  = $c['incarceration_date'];
        if (!empty($c['release_date']))        $cf['release_date']        = $c['release_date'];
        if (isset($c['imprisoned_for_days']))  $cf['imprisoned_for_days'] = $c['imprisoned_for_days'];
        if (!empty($c['judge']))               $cf['judge']               = $c['judge'];
        if (!empty($c['prosecutor']))          $cf['prosecutor']          = $c['prosecutor'];

        $p->cases()->save(new \App\Models\PrisonerCase($cf));
    }

    echo 'Added: ' . $name . PHP_EOL;
}

// -----------------------------------------------------------------------
// antifawatch.net scan — pages 500–599, batch 3 (Rochester cluster, DC, SLC)
// -----------------------------------------------------------------------

// 1. Mackenzie Drechsler
addPrisoner([
    'name'        => 'Mackenzie Drechsler',
    'first_name'  => 'Mackenzie',
    'last_name'   => 'Drechsler',
    'description' => <<<'DESC'
Mackenzie Drechsler is a Rochester, New York area woman (of Ontario, NY, Wayne County, at the time of the offense) sentenced to federal prison for setting two vehicles on fire during BLM protests in downtown Rochester on May 30, 2020. Drechsler placed burning cardboard inside a parked New York State Attorney General's Office vehicle, then roughly twenty minutes later helped a co-defendant ignite a City of Rochester Family Crisis Intervention Team (FACIT) vehicle nearby; both vehicles were total losses. She also participated in breaking glass during subsequent looting. She pleaded guilty to rioting in June 2021; Judge David Larimer ordered her held in custody pending sentencing, finding she posed a danger to the community.
DESC,
    'state'       => 'New York',
    'race'        => 'Black',
    'gender'      => 'Female',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Rioting (pled down from arson, 18 U.S.C. § 844(f)(1)) — set fire to a parked New York State Attorney General\'s Office vehicle using cardboard, and helped ignite a City of Rochester Family Crisis Intervention Team (FACIT) vehicle with burning fabric, both during May 30, 2020 BLM protests in downtown Rochester; also participated in breaking glass during looting',
        'arrest_date'         => '2020-07-01',
        'incarceration_date'  => '2021-06-08',
        'release_date'        => '2022-06-09',
        'imprisoned_for_days' => 366,
        'convicted'           => 'Yes — guilty plea (June 8, 2021)',
        'sentence'            => '1 year and 1 day federal prison + 2 years supervised release + $8,674 restitution ($3,775 to City of Rochester, $4,899 to NY State Attorney General\'s Office)',
        'judge'               => 'U.S. District Judge David G. Larimer',
    ]],
]);

// 2. Shakell Sanks
addPrisoner([
    'name'        => 'Shakell Sanks',
    'first_name'  => 'Shakell',
    'last_name'   => 'Sanks',
    'description' => <<<'DESC'
Shakell Sanks is a Rochester, New York man sentenced to federal prison for helping set fire to a City of Rochester Family Crisis Intervention Team (FACIT) vehicle during BLM protests in downtown Rochester on May 30, 2020. Sanks assisted others in stuffing burning fabric into the vehicle's gas tank; the car was a total loss within twenty minutes. He pleaded guilty to rioting (reduced from arson) alongside co-defendant Mackenzie Drechsler in June 2021.
DESC,
    'state'       => 'New York',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Rioting (pled down from arson) — assisted others in stuffing burning fabric into the gas tank of a parked City of Rochester Family Crisis Intervention Team (FACIT) vehicle, completely destroying it, during May 30, 2020 BLM protests in downtown Rochester',
        'incarceration_date'  => '2021-09-25',
        'release_date'        => '2022-02-25',
        'imprisoned_for_days' => 153,
        'convicted'           => 'Yes — guilty plea (June 8, 2021)',
        'sentence'            => '5 months federal prison',
        'judge'               => 'U.S. District Judge David G. Larimer',
    ]],
]);

// 3. Javon Hardy
addPrisoner([
    'name'        => 'Javon Hardy',
    'first_name'  => 'Javon',
    'last_name'   => 'Hardy',
    'description' => <<<'DESC'
Javon Hardy is a Rochester, New York man sentenced to federal prison for setting fire to a mobile construction trailer during BLM protests in downtown Rochester on May 30, 2020. Hardy reached through a broken window of the trailer and ignited flammable material inside using a container of accelerant, and was recorded on Facebook Live saying "Let that [expletive] burn" and "If it's not on fire, I didn't do my job." He pleaded guilty to rioting (reduced from arson) in December 2020 and was the first of at least sixteen Rochester and Buffalo protest defendants to be sentenced to prison.
DESC,
    'state'       => 'New York',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Rioting (pled down from arson) — reached through a broken window of a mobile construction trailer and ignited flammable material inside using a container of accelerant during May 30, 2020 BLM protests in downtown Rochester',
        'release_date'        => '2022-11-02',
        'convicted'           => 'Yes — guilty plea (December 16, 2020)',
        'sentence'            => '12 months federal prison + $14,504 restitution',
        'judge'               => 'U.S. District Judge Charles J. Siragusa',
    ]],
]);

// 4. Marquis Frasier
addPrisoner([
    'name'        => 'Marquis Frasier',
    'first_name'  => 'Marquis',
    'last_name'   => 'Frasier',
    'description' => <<<'DESC'
Marquis Frasier is a Rochester, New York man sentenced to federal prison for throwing a Molotov cocktail into a mobile construction trailer during BLM protests in downtown Rochester on May 30, 2020. The act was captured on Facebook Live video, showing Frasier carrying the device up the trailer's steps, throwing it inside, and running back down. He pleaded guilty to rioting (reduced from arson) in July 2022 and served his sentence at FCI Williamsburg in South Carolina.
DESC,
    'state'       => 'New York',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Correctional Institution, Williamsburg',
        'institution_city'    => 'Salters',
        'institution_state'   => 'South Carolina',
        'charges'             => 'Rioting (pled down from arson) — threw a Molotov cocktail into a mobile construction trailer at Exchange Boulevard/Court Street during May 30, 2020 BLM protests in downtown Rochester, caught on Facebook Live video',
        'release_date'        => '2024-01-25',
        'convicted'           => 'Yes — guilty plea (July 8, 2022)',
        'sentence'            => 'Approximately 13 months federal prison (exact term not published in DOJ records)',
        'judge'               => 'U.S. District Judge Charles J. Siragusa',
    ]],
]);

// 5. Jerritt Pace
addPrisoner([
    'name'        => 'Jerritt Pace',
    'first_name'  => 'Jerritt',
    'last_name'   => 'Pace',
    'description' => <<<'DESC'
Jerritt Jeremy Pace is a Washington, DC man sentenced to three years in federal prison for attempting to burn a Metropolitan Police Department precinct station during BLM protests on May 29, 2020. Pace attempted to ignite a gasoline-filled detergent container outside the 4th District station after posting on Facebook, "I WILL BURN A [4th district] STATION DOWN" and "BURN IT DOWN" along with a map link to the station. He was held without bond throughout the case; a judge's opinion denying his release cited his "escalating violence" and the fact that he was on supervision for other matters when he committed the offense. He pleaded guilty via superseding information and was sentenced in June 2021.
DESC,
    'state'       => 'District of Columbia',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Attempting to damage a building by fire (18 U.S.C. § 844(i)), receiving explosives in interstate commerce with intent to damage a building (§ 844(d)), and threatening to destroy a building by fire (§ 844(e)) — attempted to ignite a gasoline-filled detergent container outside the Metropolitan Police Department\'s 4th District station after threatening on Facebook to burn it down, Washington, DC, May 29, 2020',
        'arrest_date'         => '2020-05-29',
        'incarceration_date'  => '2020-05-29',
        'release_date'        => '2023-05-15',
        'imprisoned_for_days' => 1081,
        'convicted'           => 'Yes — guilty plea via superseding information (November 2020)',
        'sentence'            => '36 months federal prison + 3 years supervised release',
        'judge'               => 'U.S. District Judge Rudolph Contreras',
    ]],
]);

// 6. Christopher Rojas
addPrisoner([
    'name'        => 'Christopher Rojas',
    'first_name'  => 'Christopher',
    'last_name'   => 'Rojas',
    'description' => <<<'DESC'
Christopher Isidro Rojas is a Salt Lake City, Utah man sentenced to federal prison for igniting a piece of cloth inside an overturned Salt Lake City Police Department patrol car during BLM protests on May 30, 2020. Rojas used a cigarette lighter to ignite cloth that a co-defendant threw into the overturned vehicle, then helped remove the car's bumper; he was later recorded celebrating his involvement, saying "I put the cop car on fire. It didn't blow up." He was the last of five co-defendants in the case to plead guilty, entering a plea to civil disorder (reduced from arson) in September 2021.
DESC,
    'state'       => 'Utah',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Civil disorder (pled down from arson) — used a cigarette lighter to ignite a piece of cloth thrown into an overturned Salt Lake City Police Department patrol car by a co-defendant, then helped remove the vehicle\'s bumper, during May 30, 2020 BLM protests in Salt Lake City, Utah',
        'arrest_date'         => '2020-07-17',
        'incarceration_date'  => '2021-12-06',
        'release_date'        => '2023-01-09',
        'imprisoned_for_days' => 399,
        'convicted'           => 'Yes — guilty plea (September 15, 2021)',
        'sentence'            => '13 months federal prison (sentenced between December 2021 and January 2022; exact sentencing date not published) + $2,500 restitution',
        'judge'               => 'U.S. District Judge David Barlow',
    ]],
]);

echo PHP_EOL . 'Done.' . PHP_EOL;
PHPEOF

echo "Adding 6 more political prisoners from antifawatch.net scan (pages 500-599, batch 3)..."
php artisan tinker --execute="require '/tmp/add_pages_500_600_batch3.php';"
echo "Script complete."
