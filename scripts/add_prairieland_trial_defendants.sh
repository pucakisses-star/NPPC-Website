#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_prairieland_trial_defendants.sh
# Adds the 9 Prairieland ICE Detention Center defendants convicted at trial
# (sentenced June 23 – July 1, 2026). Sentences range from 30 to 100 years.
# July 4, 2025: solidarity demonstration at Prairieland facility, Alvarado TX.
# Rebecca Morgan (plea deal, already in DB) is excluded.
set +e

cat > /tmp/add_prairieland_trial.php << 'PHPEOF'
<?php

function addPrairieDefendant(array $data): void
{
    $name = $data['name'];

    if (\App\Models\Prisoner::withoutGlobalScopes()->where('name', $name)->exists()) {
        echo 'SKIP (already exists): ' . $name . PHP_EOL;
        return;
    }

    $fields = [
        'name'        => $name,
        'first_name'  => $data['first_name'],
        'last_name'   => $data['last_name'],
        'description' => $data['description'],
        'state'       => $data['state'],
        'gender'      => $data['gender'],
        'ideologies'  => $data['ideologies'],
        'era'         => $data['era'],
        'in_custody'  => $data['in_custody'],
        'released'    => $data['released'],
    ];
    if (!empty($data['race']))        $fields['race']        = $data['race'];
    if (!empty($data['affiliation'])) $fields['affiliation'] = $data['affiliation'];

    $p = \App\Models\Prisoner::create($fields);

    foreach ($data['cases'] as $c) {
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
        if (isset($c['imprisoned_for_days'])) $cf['imprisoned_for_days'] = $c['imprisoned_for_days'];

        $p->cases()->save(new \App\Models\PrisonerCase($cf));
    }

    echo 'Created: ' . $name . PHP_EOL;
}

// ─── 1. Benjamin Hanil Song ──────────────────────────────────────────────────
addPrairieDefendant([
    'name'       => 'Benjamin Song',
    'first_name' => 'Benjamin',
    'last_name'  => 'Song',
    'description' => <<<'DESC'
Benjamin Hanil Song is a Korean American activist from Dallas, Texas. A former U.S. Marine Corps reservist (2011–2016) and member of the Elm Fork chapter of the John Brown Gun Club, Song participated in a July 4, 2025 solidarity demonstration at the Prairieland ICE Detention Center in Alvarado, Texas, protesting the Trump administration's mass deportation campaign. During the demonstration, an Alvarado police officer was shot in the neck and wounded; Song was accused of firing the shot. Song evaded capture for twelve days before being arrested on July 16, 2025. Convicted at trial and sentenced to 100 years in federal prison, Song's case drew international attention as the longest sentence ever imposed in connection with a U.S. political protest.
DESC,
    'state'       => 'Texas',
    'race'        => 'Asian / Pacific Islander',
    'gender'      => 'Male',
    'ideologies'  => ['Anti-deportation', 'Immigration justice', 'Anti-fascism'],
    'affiliation' => ['John Brown Gun Club'],
    'era'         => '2020s',
    'in_custody'  => true,
    'released'    => false,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Attempted murder of a federal officer (18 U.S.C. § 1114); discharging a firearm during a crime of violence (18 U.S.C. § 924(c)); rioting (18 U.S.C. § 2101); providing material support to terrorists (18 U.S.C. § 2339A); conspiracy to use explosives (18 U.S.C. § 842) — July 4, 2025 solidarity demonstration at Prairieland ICE Detention Center, Alvarado, Texas',
        'arrest_date'        => '2025-07-16',
        'incarceration_date' => '2025-07-16',
        'imprisoned_for_days' => 351,
        'convicted'          => 'Yes — jury verdict',
        'sentence'           => '100 years federal prison',
    ]],
]);

// ─── 2. Maricela Rueda ───────────────────────────────────────────────────────
addPrairieDefendant([
    'name'       => 'Maricela Rueda',
    'first_name' => 'Maricela',
    'last_name'  => 'Rueda',
    'description' => <<<'DESC'
Maricela Rueda is a Latina activist from Texas and the mother of a teenage child. She participated in the July 4, 2025 solidarity demonstration at the Prairieland ICE Detention Center in Alvarado, Texas, organized to protest the Trump administration's mass deportation campaign. Prosecutors alleged that from jail she called her husband Daniel Sanchez Estrada and asked him to move a box of political zines, and that she asked him to destroy evidence. She was convicted at trial and sentenced to 70 years in federal prison.
DESC,
    'state'      => 'Texas',
    'race'       => 'Hispanic / Latino',
    'gender'     => 'Female',
    'ideologies' => ['Anti-deportation', 'Immigration justice', 'Anti-fascism'],
    'era'        => '2020s',
    'in_custody' => true,
    'released'   => false,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Rioting (18 U.S.C. § 2101); providing material support to terrorists (18 U.S.C. § 2339A); conspiracy to use explosives; conspiracy to conceal documents — July 4, 2025 solidarity demonstration at Prairieland ICE Detention Center, Alvarado, Texas',
        'arrest_date'        => '2025-07-04',
        'incarceration_date' => '2025-07-04',
        'imprisoned_for_days' => 354,
        'convicted'          => 'Yes — jury verdict',
        'sentence'           => '70 years federal prison',
    ]],
]);

// ─── 3. Autumn Hill ──────────────────────────────────────────────────────────
addPrairieDefendant([
    'name'       => 'Autumn Hill',
    'first_name' => 'Autumn',
    'last_name'  => 'Hill',
    'description' => <<<'DESC'
Autumn Hill (legal name Cameron Arnold) is a transgender woman and activist from Dallas, Texas. She was arrested on July 5, 2025 in an FBI no-knock raid the morning after the solidarity demonstration at the Prairieland ICE Detention Center in Alvarado, Texas. She was convicted at trial of rioting and providing material support to terrorism for her participation in the protest against the Trump administration's mass deportation campaign, and sentenced to 50 years in federal prison.
DESC,
    'state'      => 'Texas',
    'gender'     => 'Female',
    'ideologies' => ['Anti-deportation', 'Immigration justice', 'Anti-fascism'],
    'era'        => '2020s',
    'in_custody' => true,
    'released'   => false,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Rioting (18 U.S.C. § 2101); providing material support to terrorists (18 U.S.C. § 2339A); conspiracy to use explosives — July 4, 2025 solidarity demonstration at Prairieland ICE Detention Center, Alvarado, Texas; arrested July 5, 2025 in FBI no-knock raid',
        'arrest_date'        => '2025-07-05',
        'incarceration_date' => '2025-07-05',
        'imprisoned_for_days' => 353,
        'convicted'          => 'Yes — jury verdict',
        'sentence'           => '50 years federal prison',
    ]],
]);

// ─── 4. Zachary Evetts ───────────────────────────────────────────────────────
addPrairieDefendant([
    'name'       => 'Zachary Evetts',
    'first_name' => 'Zachary',
    'last_name'  => 'Evetts',
    'description' => <<<'DESC'
Zachary Evetts is an activist from Texas. He was arrested while fleeing on foot in Venus, Texas, within hours of the July 4, 2025 solidarity demonstration at the Prairieland ICE Detention Center in Alvarado, Texas, organized to show solidarity with immigrants detained under the Trump administration's mass deportation campaign. He was convicted at trial of rioting and providing material support to terrorism and sentenced to 50 years in federal prison.
DESC,
    'state'      => 'Texas',
    'gender'     => 'Male',
    'ideologies' => ['Anti-deportation', 'Immigration justice', 'Anti-fascism'],
    'era'        => '2020s',
    'in_custody' => true,
    'released'   => false,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Rioting (18 U.S.C. § 2101); providing material support to terrorists (18 U.S.C. § 2339A); conspiracy to use explosives — July 4, 2025 solidarity demonstration at Prairieland ICE Detention Center, Alvarado, Texas',
        'arrest_date'        => '2025-07-04',
        'incarceration_date' => '2025-07-04',
        'imprisoned_for_days' => 354,
        'convicted'          => 'Yes — jury verdict',
        'sentence'           => '50 years federal prison',
    ]],
]);

// ─── 5. Meagan Morris ────────────────────────────────────────────────────────
addPrairieDefendant([
    'name'       => 'Meagan Morris',
    'first_name' => 'Meagan',
    'last_name'  => 'Morris',
    'description' => <<<'DESC'
Meagan Morris (birth name Bradford Morris) is a transgender woman and activist from Texas who served as a van driver transporting fellow protesters to and from the July 4, 2025 solidarity demonstration at the Prairieland ICE Detention Center in Alvarado, Texas. Arrested on July 5, 2025 when her van was pulled over by police, she was convicted at trial of rioting and providing material support to terrorism for her role in the protest against the Trump administration's mass deportation campaign. She was sentenced to 50 years in federal prison.
DESC,
    'state'      => 'Texas',
    'gender'     => 'Female',
    'ideologies' => ['Anti-deportation', 'Immigration justice', 'Anti-fascism'],
    'era'        => '2020s',
    'in_custody' => true,
    'released'   => false,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Rioting (18 U.S.C. § 2101); providing material support to terrorists (18 U.S.C. § 2339A); conspiracy to use explosives — July 4, 2025 solidarity demonstration at Prairieland ICE Detention Center, Alvarado, Texas; arrested July 5, 2025',
        'arrest_date'        => '2025-07-05',
        'incarceration_date' => '2025-07-05',
        'imprisoned_for_days' => 353,
        'convicted'          => 'Yes — jury verdict',
        'sentence'           => '50 years federal prison',
    ]],
]);

// ─── 6. Savanna Batten ───────────────────────────────────────────────────────
addPrairieDefendant([
    'name'       => 'Savanna Batten',
    'first_name' => 'Savanna',
    'last_name'  => 'Batten',
    'description' => <<<'DESC'
Savanna Batten is a nonbinary activist from Texas and co-organizer of the Emma Goldman Book Club, a Dallas-Fort Worth reading group and zine-distribution collective. They carried only a medical kit to the July 4, 2025 solidarity demonstration at the Prairieland ICE Detention Center in Alvarado, Texas, and were not present at any planning meetings. Despite having no firearm and no involvement in planning the action, they were convicted at trial of providing material support to terrorism — largely on the basis of shared political associations — and sentenced to 50 years in federal prison.
DESC,
    'state'       => 'Texas',
    'gender'      => 'Non-binary',
    'ideologies'  => ['Anti-deportation', 'Immigration justice', 'Anarchism', 'Anti-fascism'],
    'affiliation' => ['Emma Goldman Book Club'],
    'era'         => '2020s',
    'in_custody'  => true,
    'released'    => false,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Rioting (18 U.S.C. § 2101); providing material support to terrorists (18 U.S.C. § 2339A); conspiracy to use explosives — July 4, 2025 solidarity demonstration at Prairieland ICE Detention Center, Alvarado, Texas',
        'arrest_date'        => '2025-07-04',
        'incarceration_date' => '2025-07-04',
        'imprisoned_for_days' => 354,
        'convicted'          => 'Yes — jury verdict',
        'sentence'           => '50 years federal prison',
    ]],
]);

// ─── 7. Elizabeth Soto ───────────────────────────────────────────────────────
addPrairieDefendant([
    'name'       => 'Elizabeth Soto',
    'first_name' => 'Elizabeth',
    'last_name'  => 'Soto',
    'description' => <<<'DESC'
Elizabeth Soto is an activist from Texas who co-founded the Emma Goldman Book Club, a Fort Worth reading group and zine-printing collective, alongside her spouse Ines Soto. She participated in the July 4, 2025 solidarity demonstration at the Prairieland ICE Detention Center in Alvarado, Texas, and was convicted at trial of rioting and providing material support to terrorism. She was sentenced to 50 years in federal prison alongside her spouse, who received the same sentence.
DESC,
    'state'       => 'Texas',
    'gender'      => 'Female',
    'ideologies'  => ['Anti-deportation', 'Immigration justice', 'Anarchism', 'Anti-fascism'],
    'affiliation' => ['Emma Goldman Book Club'],
    'era'         => '2020s',
    'in_custody'  => true,
    'released'    => false,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Rioting (18 U.S.C. § 2101); providing material support to terrorists (18 U.S.C. § 2339A); conspiracy to use explosives — July 4, 2025 solidarity demonstration at Prairieland ICE Detention Center, Alvarado, Texas',
        'arrest_date'        => '2025-07-04',
        'incarceration_date' => '2025-07-04',
        'imprisoned_for_days' => 354,
        'convicted'          => 'Yes — jury verdict',
        'sentence'           => '50 years federal prison',
    ]],
]);

// ─── 8. Ines Soto ────────────────────────────────────────────────────────────
addPrairieDefendant([
    'name'       => 'Ines Soto',
    'first_name' => 'Ines',
    'last_name'  => 'Soto',
    'description' => <<<'DESC'
Ines Soto is an activist from Texas who co-founded the Emma Goldman Book Club with his spouse Elizabeth Soto. He was previously arrested in 2016 while protesting white supremacist Richard Spencer's appearance at Texas A&M University. He participated in the July 4, 2025 solidarity demonstration at the Prairieland ICE Detention Center in Alvarado, Texas, protesting the Trump administration's mass deportation campaign. He was convicted at trial of providing material support to terrorism and sentenced to 50 years in federal prison.
DESC,
    'state'       => 'Texas',
    'gender'      => 'Male',
    'ideologies'  => ['Anti-deportation', 'Immigration justice', 'Anarchism', 'Anti-fascism'],
    'affiliation' => ['Emma Goldman Book Club'],
    'era'         => '2020s',
    'in_custody'  => true,
    'released'    => false,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Providing material support to terrorists (18 U.S.C. § 2339A); rioting (18 U.S.C. § 2101); conspiracy to use explosives — July 4, 2025 solidarity demonstration at Prairieland ICE Detention Center, Alvarado, Texas',
        'arrest_date'        => '2025-07-04',
        'incarceration_date' => '2025-07-04',
        'imprisoned_for_days' => 362,
        'convicted'          => 'Yes — jury verdict',
        'sentence'           => '50 years federal prison',
    ]],
]);

// ─── 9. Daniel Rolando Sanchez Estrada ───────────────────────────────────────
addPrairieDefendant([
    'name'       => 'Daniel Sanchez Estrada',
    'first_name' => 'Daniel',
    'last_name'  => 'Sanchez Estrada',
    'description' => <<<'DESC'
Daniel Rolando Sanchez Estrada, known as Des, is a Dallas teacher, artist, and member of the Socialist Rifle Association, and the husband of fellow Prairieland defendant Maricela Rueda. He was not present at the July 4, 2025 demonstration at the Prairieland ICE Detention Center. Prosecutors alleged that at his wife's request he moved a box of antifascist political zines from their home to a Denton apartment, deleted Signal and Discord communications, and attempted to modify a Nintendo Game Boy as an electronic trigger device. He was convicted and sentenced to 30 years in federal prison — a case that drew international condemnation as an example of extreme political repression.
DESC,
    'state'       => 'Texas',
    'gender'      => 'Male',
    'ideologies'  => ['Anti-deportation', 'Immigration justice', 'Anarchism', 'Anti-fascism', 'Socialism'],
    'affiliation' => ['Socialist Rifle Association'],
    'era'         => '2020s',
    'in_custody'  => true,
    'released'    => false,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Corrupting and concealing documents to impair criminal proceedings (18 U.S.C. § 1512); conspiracy to conceal documents; connection to July 4, 2025 Prairieland ICE Detention Center protest — not present at scene; convicted for moving box of antifascist zines and deleting protest communications',
        'arrest_date'        => '2025-07-07',
        'incarceration_date' => '2025-07-07',
        'imprisoned_for_days' => 360,
        'convicted'          => 'Yes — jury verdict',
        'sentence'           => '30 years federal prison',
    ]],
]);

echo PHP_EOL . 'Done.' . PHP_EOL;
PHPEOF

echo "Adding 9 Prairieland trial defendants..."
php artisan tinker --execute="require '/tmp/add_prairieland_trial.php';"
echo "Script complete."
