#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_isael_ortiz.sh
# Adds Isael Ortiz — Oklahoma City, convicted in connection with the May 30, 2020
# BLM protests (originally charged under Oklahoma's Anti-Terrorism Act).
set +e

cat > /tmp/add_isael_ortiz.php << 'PHPEOF'
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

addPrisoner([
    'name'        => 'Isael Ortiz',
    'first_name'  => 'Isael',
    'last_name'   => 'Ortiz',
    'description' => <<<'DESC'
Isael Antonio Ortiz is an Oklahoma City man convicted in connection with the May 30, 2020 BLM protests in downtown Oklahoma City. Ortiz was charged in two related Oklahoma County cases: helping break windows and attempting to set fire to CJ's Bail Bonds, and jointly setting fire to an Oklahoma County Sheriff's Office van alongside co-defendant Eric Ruffin. He was originally charged under Oklahoma's Anti-Terrorism Act, drawing criticism from the ACLU of Oklahoma and Black Lives Matter OKC as prosecutorial overreach against protesters. The terrorism counts were later amended to rioting as part of a 2023 plea agreement, alongside guilty pleas to attempted second-degree arson, third-degree arson, and malicious injury and destruction of property. He was sentenced to a suspended term with the first two years served in Oklahoma Department of Corrections custody.
DESC,
    'state'       => 'Oklahoma',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Oklahoma Department of Corrections',
        'charges'             => 'Rioting (amended from terrorism under Oklahoma\'s Anti-Terrorism Act, 21 O.S. §§ 1268-1268.8), attempted second-degree arson, third-degree arson, and malicious injury and destruction of property — helped break windows and attempted to set fire to CJ\'s Bail Bonds, and set fire to an Oklahoma County Sheriff\'s Office van, during BLM protests in downtown Oklahoma City, May 30, 2020',
        'arrest_date'         => '2020-06-26',
        'incarceration_date'  => '2023-04-12',
        'release_date'        => '2024-11-24',
        'imprisoned_for_days' => 592,
        'convicted'           => 'Yes — guilty plea (April 12, 2023)',
        'sentence'            => '10 years suspended sentence, with the first 2 years served in Oklahoma DOC custody (concurrent across both cases); gang-offender probation conditions; registration under the Mary Rippy Violent Crime Offenders Registration Act',
        'judge'               => 'Judge Stallings',
    ]],
]);

echo PHP_EOL . 'Done.' . PHP_EOL;
PHPEOF

echo "Adding Isael Ortiz..."
php artisan tinker --execute="require '/tmp/add_isael_ortiz.php';"
echo "Script complete."
