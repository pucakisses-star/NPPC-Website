#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_prairieland_plea_defendants.sh
# Adds the 5 Prairieland ICE Detention Center defendants who pleaded guilty to
# providing material support to terrorism (18 U.S.C. § 2339A).
# Sentences range from 22 months to 15 years.
# Rebecca Morgan (180 months) is already in the database — she is excluded here.
# Susan Kent (plea, not yet sentenced as of 2026-07-02) is also excluded.
set +e

cat > /tmp/add_prairieland_plea.php << 'PHPEOF'
<?php

function addPrairieDefendant(array $data): void
{
    $name = $data['name'];

    if (\App\Models\Prisoner::withoutGlobalScopes()->where('name', $name)->exists()) {
        echo 'SKIP (already exists): ' . $name . PHP_EOL;
        return;
    }

    $fields = [
        'name'       => $name,
        'first_name' => $data['first_name'],
        'last_name'  => $data['last_name'],
        'description'=> $data['description'],
        'state'      => $data['state'],
        'gender'     => $data['gender'],
        'ideologies' => $data['ideologies'],
        'era'        => $data['era'],
        'in_custody' => $data['in_custody'],
        'released'   => $data['released'],
    ];
    if (!empty($data['race'])) $fields['race'] = $data['race'];

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

// ─── 1. Nathan Baumann ───────────────────────────────────────────────────────
addPrairieDefendant([
    'name'       => 'Nathan Baumann',
    'first_name' => 'Nathan',
    'last_name'  => 'Baumann',
    'description' => <<<'DESC'
Nathan Baumann is a Texas activist who was present at the July 4, 2025 solidarity demonstration at the Prairieland ICE Detention Center in Alvarado, Texas, organized to protest the Trump administration's mass deportation campaign. He was among those arrested at the scene after spray-painting vehicles during the protest. Baumann cooperated with federal prosecutors as a witness and pleaded guilty to providing material support to terrorism. He was sentenced to 22 months in federal prison.
DESC,
    'state'      => 'Texas',
    'gender'     => 'Male',
    'ideologies' => ['Anti-deportation', 'Immigration justice', 'Anti-fascism'],
    'era'        => '2020s',
    'in_custody' => true,
    'released'   => false,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Providing material support to terrorists (18 U.S.C. § 2339A); pleaded guilty in connection with July 4, 2025 solidarity demonstration at Prairieland ICE Detention Center, Alvarado, Texas; spray-painted vehicles at the scene',
        'arrest_date'        => '2025-07-04',
        'incarceration_date' => '2025-07-04',
        'imprisoned_for_days' => 362,
        'convicted'          => 'Yes — guilty plea',
        'sentence'           => '22 months federal prison',
    ]],
]);

// ─── 2. Joy Gibson (Rowan) ───────────────────────────────────────────────────
addPrairieDefendant([
    'name'       => 'Joy Gibson',
    'first_name' => 'Joy',
    'last_name'  => 'Gibson',
    'description' => <<<'DESC'
Joy Gibson, also known as Rowan, is a trans nonbinary activist from Texas and the partner of fellow Prairieland defendant Benjamin Hanil Song. Gibson pleaded guilty to providing material support to terrorism in connection with the July 4, 2025 solidarity demonstration at the Prairieland ICE Detention Center in Alvarado, Texas, protesting the Trump administration's mass deportation campaign. As a non-cooperating defendant who refused to testify against co-defendants, Gibson received a significantly harsher sentence than cooperating witnesses, and was sentenced to 180 months (15 years) in federal prison.
DESC,
    'state'      => 'Texas',
    'gender'     => 'Non-binary',
    'ideologies' => ['Anti-deportation', 'Immigration justice', 'Anti-fascism'],
    'era'        => '2020s',
    'in_custody' => true,
    'released'   => false,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Providing material support to terrorists (18 U.S.C. § 2339A); pleaded guilty in connection with July 4, 2025 solidarity demonstration at Prairieland ICE Detention Center, Alvarado, Texas',
        'arrest_date'        => '2025-07-04',
        'incarceration_date' => '2025-07-04',
        'imprisoned_for_days' => 362,
        'convicted'          => 'Yes — guilty plea',
        'sentence'           => '180 months (15 years) federal prison',
    ]],
]);

// ─── 3. Lynette Sharp ────────────────────────────────────────────────────────
addPrairieDefendant([
    'name'       => 'Lynette Sharp',
    'first_name' => 'Lynette',
    'last_name'  => 'Sharp',
    'description' => <<<'DESC'
Lynette Sharp is a 57-year-old Texas activist who pleaded guilty to providing material support to terrorism in connection with the July 4, 2025 solidarity demonstration at the Prairieland ICE Detention Center in Alvarado, Texas. A cooperating witness who testified at trial, Sharp has publicly stated that she pleaded guilty under duress, citing harsh conditions in pretrial detention at Johnson County Jail. She was sentenced to 110 months (approximately 9 years and 2 months) in federal prison.
DESC,
    'state'      => 'Texas',
    'gender'     => 'Female',
    'ideologies' => ['Anti-deportation', 'Immigration justice', 'Anti-fascism'],
    'era'        => '2020s',
    'in_custody' => true,
    'released'   => false,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Providing material support to terrorists (18 U.S.C. § 2339A); pleaded guilty in connection with July 4, 2025 solidarity demonstration at Prairieland ICE Detention Center, Alvarado, Texas',
        'arrest_date'        => '2025-07-04',
        'incarceration_date' => '2025-07-04',
        'imprisoned_for_days' => 362,
        'convicted'          => 'Yes — guilty plea',
        'sentence'           => '110 months (approximately 9 years, 2 months) federal prison',
    ]],
]);

// ─── 4. John Phillip Thomas ──────────────────────────────────────────────────
addPrairieDefendant([
    'name'       => 'John Thomas',
    'first_name' => 'John',
    'last_name'  => 'Thomas',
    'description' => <<<'DESC'
John Phillip Thomas is a Texas activist who pleaded guilty to providing material support to terrorism in connection with the July 4, 2025 solidarity demonstration at the Prairieland ICE Detention Center in Alvarado, Texas, protesting the Trump administration's mass deportation campaign. A cooperating witness who testified at trial about assisting Benjamin Song in the aftermath of the incident, he was sentenced to 110 months (approximately 9 years and 2 months) in federal prison.
DESC,
    'state'      => 'Texas',
    'gender'     => 'Male',
    'ideologies' => ['Anti-deportation', 'Immigration justice', 'Anti-fascism'],
    'era'        => '2020s',
    'in_custody' => true,
    'released'   => false,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Providing material support to terrorists (18 U.S.C. § 2339A); pleaded guilty in connection with July 4, 2025 solidarity demonstration at Prairieland ICE Detention Center, Alvarado, Texas; cooperated with prosecution regarding assistance rendered to Benjamin Song',
        'arrest_date'        => '2025-07-04',
        'incarceration_date' => '2025-07-04',
        'imprisoned_for_days' => 362,
        'convicted'          => 'Yes — guilty plea',
        'sentence'           => '110 months (approximately 9 years, 2 months) federal prison',
    ]],
]);

// ─── 5. Seth Sikes ───────────────────────────────────────────────────────────
addPrairieDefendant([
    'name'       => 'Seth Sikes',
    'first_name' => 'Seth',
    'last_name'  => 'Sikes',
    'description' => <<<'DESC'
Seth Sikes is a 22-year-old activist from Kennedale, Texas. He pleaded guilty to providing material support to terrorism in connection with the July 4, 2025 solidarity demonstration at the Prairieland ICE Detention Center in Alvarado, Texas. Sikes testified at trial that he believed the event was planned only as a noise demonstration to show solidarity with detained immigrants and that the violence that unfolded came as a shock to him. As a cooperating witness, he was sentenced to 72 months (6 years) in federal prison.
DESC,
    'state'      => 'Texas',
    'gender'     => 'Male',
    'ideologies' => ['Anti-deportation', 'Immigration justice', 'Anti-fascism'],
    'era'        => '2020s',
    'in_custody' => true,
    'released'   => false,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Providing material support to terrorists (18 U.S.C. § 2339A); pleaded guilty in connection with July 4, 2025 solidarity demonstration at Prairieland ICE Detention Center, Alvarado, Texas',
        'arrest_date'        => '2025-07-04',
        'incarceration_date' => '2025-07-04',
        'imprisoned_for_days' => 362,
        'convicted'          => 'Yes — guilty plea',
        'sentence'           => '72 months (6 years) federal prison',
    ]],
]);

echo PHP_EOL . 'Done.' . PHP_EOL;
PHPEOF

echo "Adding 5 Prairieland plea defendants..."
php artisan tinker --execute="require '/tmp/add_prairieland_plea.php';"
echo "Script complete."
