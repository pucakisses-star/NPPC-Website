#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_scan_pages_301_400_batch1.sh
# New confirmed political prisoners found by scanning antifawatch.net pages 301-400.
# Excludes people already known to be in the database (Briana Waters, Cecily McMillan,
# Jake Conroy, Cleveland Four, ELF/Operation Backfire principals, Marius Mason).
set +e

cat > /tmp/add_scan_batch1.php << 'PHPEOF'
<?php

function addPrisoner(array $data): void
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
    if (!empty($data['birthdate']))   $fields['birthdate']   = $data['birthdate'];

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
        if (!empty($c['release_date']))       $cf['release_date']       = $c['release_date'];
        if (isset($c['imprisoned_for_days'])) $cf['imprisoned_for_days'] = $c['imprisoned_for_days'];
        if (!empty($c['judge']))              $cf['judge']              = $c['judge'];
        if (!empty($c['prosecutor']))         $cf['prosecutor']         = $c['prosecutor'];

        $p->cases()->save(new \App\Models\PrisonerCase($cf));
    }

    echo 'Created: ' . $name . PHP_EOL;
}

// ─── 1. Brandon Pack (Columbus BLM, 2020) ────────────────────────────────────
addPrisoner([
    'name'       => 'Brandon Pack',
    'first_name' => 'Brandon',
    'last_name'  => 'Pack',
    'description' => <<<'DESC'
Brandon Pack is an Ohio man who was arrested during George Floyd/BLM protests in Columbus in May 2020 for shooting fireworks at Columbus police officers and participating in acts of destruction. He was convicted of aggravated riot, vandalism, breaking and entering, and three counts of felonious assault on police officers. He was sentenced to 8 to 9.5 years in Ohio state prison — one of the longest sentences imposed on any individual in connection with the 2020 George Floyd uprising.
DESC,
    'state'      => 'Ohio',
    'gender'     => 'Male',
    'ideologies' => ['Anti-Police Brutality', 'Black Liberation'],
    'era'        => '2020s',
    'in_custody' => true,
    'released'   => false,
    'cases' => [[
        'inst_name'          => 'Ohio Department of Rehabilitation and Correction',
        'charges'            => 'Aggravated riot; vandalism; breaking and entering; three counts of felonious assault on police officers — arising from George Floyd/BLM protest, Columbus, Ohio, May 2020',
        'arrest_date'        => '2020-05-30',
        'incarceration_date' => '2020-05-30',
        'imprisoned_for_days' => 2224,
        'convicted'          => 'Yes — jury verdict',
        'sentence'           => '8 to 9.5 years Ohio state prison',
    ]],
]);

// ─── 2. Jesse Cannon (San Diego Patriot March counter-protest, 2021) ─────────
addPrisoner([
    'name'       => 'Jesse Cannon',
    'first_name' => 'Jesse',
    'last_name'  => 'Cannon',
    'description' => <<<'DESC'
Jesse Cannon is a California anti-fascist activist who was part of an organized antifa group that counter-protested a January 9, 2021 Patriot March (a right-wing rally) in San Diego. Prosecutors alleged the group coordinated to confront marchers, and that Cannon participated in assaulting multiple individuals and using tear gas against counter-protesters. He pleaded guilty to three felonies — conspiracy to riot, assault, and unlawful use of tear gas — in January 2024 and was sentenced to 5 years in California state prison.
DESC,
    'state'      => 'California',
    'gender'     => 'Male',
    'ideologies' => ['Anti-fascism'],
    'era'        => '2020s',
    'in_custody' => true,
    'released'   => false,
    'cases' => [[
        'inst_name'          => 'California Department of Corrections and Rehabilitation',
        'charges'            => 'Conspiracy to riot (California Penal Code § 404.6); assault; unlawful use of tear gas — arising from January 9, 2021 counter-protest of Patriot March, San Diego, California',
        'arrest_date'        => '2021-01-09',
        'incarceration_date' => '2024-01-01',
        'imprisoned_for_days' => 913,
        'convicted'          => 'Yes — guilty plea (January 2024)',
        'sentence'           => '5 years California state prison',
    ]],
]);

// ─── 3. Jacqueline Quimby (anti-coal civil disobedience, WV, 2010) ───────────
addPrisoner([
    'name'       => 'Jacqueline Quimby',
    'first_name' => 'Jacqueline',
    'last_name'  => 'Quimby',
    'birthdate'  => '1982-12-19',
    'description' => <<<'DESC'
Jacqueline Quimby is an environmental activist affiliated with Climate Ground Zero and Mountain Justice, organizations that oppose mountaintop-removal coal mining in Appalachia. In 2010 she participated in direct action to physically block a road used by Massey Energy for coal transport in West Virginia, as part of the anti-mountain-top-removal movement. She was convicted of trespassing, conspiracy, and obstruction, and sentenced to 60 days in jail — one of the harsher sentences imposed on a mountaintop removal activist during that period.
DESC,
    'state'      => 'West Virginia',
    'gender'     => 'Female',
    'ideologies' => ['Environmental justice', 'Environmentalism'],
    'era'        => '2010s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'West Virginia Regional Jail',
        'charges'            => 'Trespassing; conspiracy; obstruction — for blocking a road used by Massey Energy for coal transport as part of an anti-mountaintop-removal direct action, West Virginia, 2010',
        'incarceration_date' => '2010-01-01',
        'imprisoned_for_days' => 60,
        'convicted'          => 'Yes',
        'sentence'           => '60 days jail',
    ]],
]);

// ─── 4. Kevin Chianella (G-20 Toronto, 2010) ─────────────────────────────────
addPrisoner([
    'name'       => 'Kevin Chianella',
    'first_name' => 'Kevin',
    'last_name'  => 'Chianella',
    'description' => <<<'DESC'
Kevin Chianella is a New Jersey activist who traveled to Toronto, Canada as a teenager to protest the G-20 Summit in June 2010. During the summit protests — some of the largest mass arrests in Canadian history — Chianella was alleged to have participated in spray-painting vehicles, assault with a weapon, arson, and break-and-enter. He returned to the United States after the events, but was later extradited to Canada to face 53 criminal counts. He was sentenced to 2 years in Canadian federal prison, making him one of the most severely sentenced U.S. participants in the Toronto G-20 protests.
DESC,
    'state'      => 'New Jersey',
    'gender'     => 'Male',
    'ideologies' => ['Anti-globalization', 'Anti-capitalism'],
    'era'        => '2010s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Correctional Service of Canada',
        'inst_city'          => 'Ontario',
        'inst_state'         => 'Canada',
        'charges'            => '53 counts including mischief, assault with a weapon, arson, and break-and-enter — G-20 Summit protests, Toronto, Canada, June 2010; extradited from the United States to Canada',
        'arrest_date'        => '2010-06-26',
        'imprisoned_for_days' => 730,
        'convicted'          => 'Yes',
        'sentence'           => '2 years Canadian federal prison',
    ]],
]);

// ─── 5. Quinn McCormic (G-20 Toronto, 2010) ──────────────────────────────────
addPrisoner([
    'name'       => 'Quinn McCormic',
    'first_name' => 'Quinn',
    'last_name'  => 'McCormic',
    'description' => <<<'DESC'
Quinn McCormic is a Massachusetts activist who traveled to Toronto, Canada to protest the G-20 Summit in June 2010. He was alleged to have caused approximately $125,000 in property damage to retail stores, a bank, and a museum during the demonstrations. He returned to the United States after the events but was later extradited to Canada to face charges. He was sentenced to four months in a Canadian provincial jail.
DESC,
    'state'      => 'Massachusetts',
    'gender'     => 'Male',
    'ideologies' => ['Anti-globalization', 'Anti-capitalism'],
    'era'        => '2010s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Ontario Ministry of Correctional Services',
        'inst_city'          => 'Ontario',
        'inst_state'         => 'Canada',
        'charges'            => 'Mischief causing property damage ($125,000 to retail stores, bank, and museum) — G-20 Summit protests, Toronto, Canada, June 2010; extradited from the United States to Canada',
        'arrest_date'        => '2010-06-26',
        'imprisoned_for_days' => 122,
        'convicted'          => 'Yes',
        'sentence'           => '4 months Canadian provincial jail',
    ]],
]);

// ─── 6. Richard Morano (G-20 Toronto, 2010) ──────────────────────────────────
addPrisoner([
    'name'       => 'Richard Morano',
    'first_name' => 'Richard',
    'last_name'  => 'Morano',
    'description' => <<<'DESC'
Richard Morano is a Pennsylvania activist who traveled to Toronto, Canada to protest the G-20 Summit in June 2010. He was alleged to have broken a police car window and a storefront window during the demonstrations. He returned to the United States but was extradited to Canada in 2014, four years after the events. He was convicted of mischief endangering life and sentenced to 7 months in a Canadian provincial jail; he was also banned from Toronto for 2 years. During his incarceration he was reportedly beaten by other inmates.
DESC,
    'state'      => 'Pennsylvania',
    'gender'     => 'Male',
    'ideologies' => ['Anti-globalization', 'Anti-capitalism'],
    'era'        => '2010s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Ontario Ministry of Correctional Services',
        'inst_city'          => 'Ontario',
        'inst_state'         => 'Canada',
        'charges'            => 'Mischief endangering life (broke police car window and storefront window) — G-20 Summit protests, Toronto, Canada, June 2010; extradited from the United States in 2014',
        'arrest_date'        => '2010-06-26',
        'incarceration_date' => '2014-01-01',
        'imprisoned_for_days' => 213,
        'convicted'          => 'Yes',
        'sentence'           => '7 months Canadian provincial jail; 2-year ban from Toronto',
    ]],
]);

// ─── 7. Kellen Sorber (Laramie Republican HQ arson, 2018) ────────────────────
addPrisoner([
    'name'       => 'Kellen Sorber',
    'first_name' => 'Kellen',
    'last_name'  => 'Sorber',
    'description' => <<<'DESC'
Kellen Sorber is a Wyoming activist who set fire to the Republican Party headquarters building in Laramie, Wyoming in October 2018. He pleaded guilty to federal arson under 18 U.S.C. § 844(i) (arson of a building used in interstate commerce) and was sentenced to 44 months in federal prison.
DESC,
    'state'      => 'Wyoming',
    'gender'     => 'Male',
    'ideologies' => ['Anti-fascism', 'Anti-capitalism'],
    'era'        => '2010s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Arson of a building used in interstate commerce (18 U.S.C. § 844(i)) — for setting fire to the Wyoming Republican Party headquarters building in Laramie, October 2018',
        'arrest_date'        => '2018-10-01',
        'imprisoned_for_days' => 1339,
        'convicted'          => 'Yes — guilty plea',
        'sentence'           => '44 months federal prison',
    ]],
]);

// ─── 8. Martino Andrews (Columbus BLM arson conspiracy, 2020) ────────────────
addPrisoner([
    'name'       => 'Martino Andrews',
    'first_name' => 'Martino',
    'last_name'  => 'Andrews',
    'description' => <<<'DESC'
Martino Andrews is an Ohio activist who pleaded guilty to conspiracy to commit arson for torching a county van during the May 30, 2020 George Floyd/BLM protests in Columbus, Ohio. His plea agreement recommended a sentence of 3.5 years in federal prison.
DESC,
    'state'      => 'Ohio',
    'gender'     => 'Male',
    'ideologies' => ['Anti-Police Brutality', 'Black Liberation'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Conspiracy to commit arson (18 U.S.C. § 844) — for torching a county van during May 30, 2020 George Floyd/BLM protests in Columbus, Ohio',
        'arrest_date'        => '2020-05-30',
        'imprisoned_for_days' => 1095,
        'convicted'          => 'Yes — guilty plea',
        'sentence'           => '3.5 years (42 months) federal prison',
    ]],
]);

// ─── 9. Daniel Alan Baker (J20/CHAZ, 2021) ───────────────────────────────────
addPrisoner([
    'name'       => 'Daniel Baker',
    'first_name' => 'Daniel',
    'last_name'  => 'Baker',
    'description' => <<<'DESC'
Daniel Alan Baker is a Florida activist and former U.S. Army airborne veteran who participated in the Seattle CHAZ/CHOP autonomous zone during the 2020 George Floyd uprising. In January 2021, following the January 6 Capitol attack, he posted a Facebook Live video threatening members of the Proud Boys. He was federally charged with transmitting a threat in interstate commerce and sentenced to 44 months in federal prison. Baker — who had also been involved in the Rojava revolution and was an anti-fascist activist — maintained that his posts were constitutionally protected political speech. He was released following completion of his sentence.
DESC,
    'state'      => 'Florida',
    'gender'     => 'Male',
    'ideologies' => ['Anti-fascism', 'Anarchism'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Transmitting a threat in interstate commerce (18 U.S.C. § 875(c)) — for posting a Facebook Live video threatening Proud Boys in January 2021; context includes participation in Seattle CHAZ/CHOP autonomous zone (2020)',
        'arrest_date'        => '2021-01-15',
        'incarceration_date' => '2022-01-01',
        'imprisoned_for_days' => 1122,
        'convicted'          => 'Yes — guilty plea',
        'sentence'           => '44 months federal prison',
    ]],
]);

// ─── 10. Alissa Azar (anti-fascist Clackamette Park, 2021/2024) ──────────────
addPrisoner([
    'name'       => 'Alissa Azar',
    'first_name' => 'Alissa',
    'last_name'  => 'Azar',
    'description' => <<<'DESC'
Alissa Azar is an Oregon anti-fascist activist and protest journalist who documented Portland-area demonstrations. On June 18, 2021, she participated in a violent confrontation between antifa and Proud Boys at Clackamette Park in Oregon City. During the clash she was knocked unconscious by a Proud Boy. She was charged with felony riot, disorderly conduct, and attempted use of tear gas. At trial the tear gas charge resulted in a hung jury, but she was convicted of felony riot and disorderly conduct in August 2024 and sentenced to 14 days in jail plus 36 months of supervised probation and GPS monitoring. She also has a separate open case in Multnomah County related to her participation in a May 2024 pro-Palestine occupation at Portland State University.
DESC,
    'state'      => 'Oregon',
    'gender'     => 'Female',
    'ideologies' => ['Anti-fascism', 'Palestine solidarity'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Clackamas County Jail',
        'inst_city'          => 'Oregon City',
        'inst_state'         => 'Oregon',
        'charges'            => 'Felony riot; disorderly conduct; attempted use of tear gas (hung jury on last count) — June 18, 2021 antifa vs. Proud Boys confrontation at Clackamette Park, Oregon City, Oregon',
        'incarceration_date' => '2024-08-01',
        'imprisoned_for_days' => 14,
        'convicted'          => 'Yes — jury verdict (felony riot and disorderly conduct; August 2024)',
        'sentence'           => '14 days jail; 36 months supervised probation; GPS monitoring',
    ]],
]);

// ─── 11. Sergey Turzhanskiy (Portland antifa, 2012) ──────────────────────────
addPrisoner([
    'name'       => 'Sergey Turzhanskiy',
    'first_name' => 'Sergey',
    'last_name'  => 'Turzhanskiy',
    'description' => <<<'DESC'
Sergey Turzhanskiy is a Portland, Oregon anti-fascist activist and union organizer. In 2012 he threw a Molotov cocktail at a Portland police car during an anti-fascist demonstration and was convicted on federal arson charges. He served approximately 2.5 years in federal prison. He was arrested again in August 2020 during the Portland protest wave following the killing of George Floyd.
DESC,
    'state'      => 'Oregon',
    'gender'     => 'Male',
    'ideologies' => ['Anti-fascism', 'Labor organizing'],
    'era'        => '2010s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Federal arson — for throwing a Molotov cocktail at a Portland Police Bureau vehicle during an anti-fascist demonstration, Portland, Oregon, 2012',
        'incarceration_date' => '2013-01-01',
        'imprisoned_for_days' => 913,
        'convicted'          => 'Yes',
        'sentence'           => 'Approximately 2.5 years federal prison',
    ]],
]);

echo PHP_EOL . 'Done.' . PHP_EOL;
PHPEOF

echo "Adding batch 1 political prisoners from antifawatch.net scan (pages 301-400)..."
php artisan tinker --execute="require '/tmp/add_scan_batch1.php';"
echo "Script complete."
