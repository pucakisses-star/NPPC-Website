#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_scan_pages_400_500_batch1.sh
# 27 confirmed new political prisoners from antifawatch.net scan pages 400-499.
# Primarily 2020 George Floyd/BLM uprising defendants, plus Wet'suwet'en solidarity
# railroad activists, Iowa BLM protesters, anti-ICE activist, and Matthew Banta.
set +e

cat > /tmp/add_scan_400_500_batch1.php << 'PHPEOF'
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
    if (!empty($data['race']))      $fields['race']      = $data['race'];
    if (!empty($data['birthdate'])) $fields['birthdate'] = $data['birthdate'];

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

// ─── 1. Victor Devon Edwards (Minneapolis Target arson, Aug 2020) ─────────────
addPrisoner([
    'name'       => 'Victor Devon Edwards',
    'first_name' => 'Victor',
    'last_name'  => 'Edwards',
    'description' => <<<'DESC'
Victor Devon Edwards is a St. Paul, Minnesota man convicted by federal jury in August 2021 for arson and riot at Target Corporation headquarters in downtown Minneapolis during civil unrest on August 26, 2020 — unrest triggered by false rumors of a police killing on Nicollet Mall (the actual event was the suicide of Eddie Sole Jr. while being pursued by police). Edwards broke into the Target building using a construction sign and added fuel to a fire that had already been started inside, causing nearly $1 million in damage. He was sentenced in December 2021 to 100 months (8 years, 4 months) in federal prison. Co-defendants Leroy Lemonte Perry Williams and Shador Jackson were convicted in separate proceedings.
DESC,
    'state'      => 'Minnesota',
    'gender'     => 'Male',
    'ideologies' => ['Anti-Police Brutality', 'Black Lives Matter'],
    'era'        => '2020s',
    'in_custody' => true,
    'released'   => false,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Arson (18 U.S.C. § 844(i)); federal riot (18 U.S.C. § 2101) — Target Corporation headquarters, Minneapolis, August 26, 2020 civil unrest following false rumors of police killing on Nicollet Mall',
        'arrest_date'        => '2020-08-26',
        'incarceration_date' => '2021-12-01',
        'imprisoned_for_days' => 1674,
        'convicted'          => 'Yes — jury verdict (August 2021)',
        'sentence'           => '100 months (8 years, 4 months) federal prison; 2 years supervised release; $941,682 restitution',
    ]],
]);

// ─── 2. Leroy Lemonte Perry Williams (Minneapolis Target arson, Aug 2020) ─────
addPrisoner([
    'name'       => 'Leroy Lemonte Perry Williams',
    'first_name' => 'Leroy',
    'last_name'  => 'Williams',
    'description' => <<<'DESC'
Leroy Lemonte Perry Williams is a Minneapolis, Minnesota man convicted in October 2023 and sentenced in February 2024 to 10 years in federal prison for attempting to set fire to Target Corporation headquarters during civil unrest on August 26, 2020. Surveillance footage showed Williams breaching the building and repeatedly attempting to light fires inside and outside. The unrest was sparked by false rumors of a police killing on Nicollet Mall; the actual event was the suicide of Eddie Sole Jr. while being pursued by police. Co-defendants Victor Devon Edwards (sentenced to 100 months in December 2021) and Shador Jackson (sentenced to 33 months in June 2021) received their sentences in earlier proceedings.
DESC,
    'state'      => 'Minnesota',
    'gender'     => 'Male',
    'ideologies' => ['Anti-Police Brutality', 'Black Lives Matter'],
    'era'        => '2020s',
    'in_custody' => true,
    'released'   => false,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Arson (18 U.S.C. § 844(i)) — Target Corporation headquarters, Minneapolis, August 26, 2020 civil unrest following false rumors of a police killing',
        'arrest_date'        => '2020-08-26',
        'incarceration_date' => '2024-02-01',
        'imprisoned_for_days' => 883,
        'convicted'          => 'Yes — jury verdict (October 2023)',
        'sentence'           => '120 months (10 years) federal prison; 3 years supervised release',
    ]],
]);

// ─── 3. Ellen Reiche (BNSF railroad sabotage, Wet'suwet'en solidarity, 2020) ──
addPrisoner([
    'name'       => 'Ellen Reiche',
    'first_name' => 'Ellen',
    'last_name'  => 'Reiche',
    'description' => <<<'DESC'
Ellen Reiche is a Bellingham, Washington environmental and Indigenous solidarity activist convicted for placing a shunting device on BNSF railroad tracks near Bellingham, Washington on November 28, 2020. The sabotage was part of a campaign opposing Trans Mountain and Coastal GasLink pipeline construction across British Columbia in solidarity with Wet'suwet'en hereditary chiefs. The device disrupted train signals at a moment when a crude oil tanker train was due through the area. Reiche was convicted by jury in September 2021 and sentenced to 12 months and one day in federal prison plus 3 years supervised release and 100 hours community service. She is a co-defendant with Samantha Brooks.
DESC,
    'state'      => 'Washington',
    'gender'     => 'Female',
    'ideologies' => ['Environmental justice', 'Anti-pipeline', 'Indigenous solidarity'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Terrorist attack and violence against a railroad carrier (18 U.S.C. § 1992) — placement of shunting device on BNSF railroad tracks near Bellingham, Washington, November 28, 2020, in solidarity with Wet\'suwet\'en land defenders opposing Trans Mountain and Coastal GasLink pipelines',
        'arrest_date'        => '2020-11-28',
        'imprisoned_for_days' => 366,
        'convicted'          => 'Yes — jury verdict (September 2021)',
        'sentence'           => '12 months and one day federal prison; 3 years supervised release; 100 hours community service',
    ]],
]);

// ─── 4. Samantha Brooks (BNSF railroad sabotage, Wet'suwet'en solidarity, 2020)
addPrisoner([
    'name'       => 'Samantha Brooks',
    'first_name' => 'Samantha',
    'last_name'  => 'Brooks',
    'description' => <<<'DESC'
Samantha Brooks is a Bellingham, Washington activist who pleaded guilty in July 2021 and was sentenced in October 2021 for placing sabotage devices on BNSF railroad tracks alongside Ellen Reiche in November 2020, in solidarity with Wet'suwet'en hereditary chiefs opposing pipeline construction across British Columbia. She was sentenced to 6 months in federal prison plus 4 months home confinement, 3 years supervised release, and 200 hours community service — a shorter sentence than co-defendant Reiche, reflecting her plea and cooperation.
DESC,
    'state'      => 'Washington',
    'gender'     => 'Female',
    'ideologies' => ['Environmental justice', 'Anti-pipeline', 'Indigenous solidarity'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Terrorist attack and violence against a railroad carrier (18 U.S.C. § 1992) — placement of shunting device on BNSF railroad tracks near Bellingham, Washington, November 28, 2020, in solidarity with Wet\'suwet\'en land defenders opposing pipelines',
        'arrest_date'        => '2020-11-28',
        'imprisoned_for_days' => 183,
        'convicted'          => 'Yes — guilty plea (July 2021)',
        'sentence'           => '6 months federal prison; 4 months home confinement; 3 years supervised release; 200 hours community service',
    ]],
]);

// ─── 5. Carlos Matchett (Philadelphia police car arson, May 2020) ─────────────
addPrisoner([
    'name'       => 'Carlos Matchett',
    'first_name' => 'Carlos',
    'last_name'  => 'Matchett',
    'description' => <<<'DESC'
Carlos Matchett is an Atlantic City, New Jersey man who set fire to an overturned Philadelphia police car on May 30, 2020, during George Floyd uprising protests, spraying the vehicle with lighter fluid before igniting it. He was also charged in connection with separate acts of theft and vandalism during a riot in Atlantic City the following day. Officers arrested him at the Atlantic City protest in possession of a folding knife, hatchet, and jar of gasoline. He pleaded guilty in late 2022 and was sentenced in February 2023 to 46 months in federal prison.
DESC,
    'state'      => 'New Jersey',
    'gender'     => 'Male',
    'ideologies' => ['Anti-Police Brutality', 'Black Lives Matter'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Obstruction of law enforcement during civil disorder (18 U.S.C. § 231); traveling in interstate commerce to incite a riot — for setting fire to a Philadelphia Police Department vehicle on May 30, 2020 during George Floyd uprising protests, and rioting in Atlantic City, New Jersey on May 31, 2020',
        'incarceration_date' => '2023-02-01',
        'imprisoned_for_days' => 1186,
        'convicted'          => 'Yes — guilty plea (late 2022)',
        'sentence'           => '46 months federal prison',
    ]],
]);

// ─── 6. Vida Jones (Atlanta USPS arson conspiracy, 2020) ──────────────────────
addPrisoner([
    'name'       => 'Vida Jones',
    'first_name' => 'Vida',
    'last_name'  => 'Jones',
    'description' => <<<'DESC'
Vida Jones is a Georgia activist who was 18 years old at the time of the offenses. She was charged with federal arson conspiracy in connection with the fall 2020 Atlanta protest-era case involving the destruction of five USPS mail trucks and a police car. Co-defendants John Wade and Ellie Brett each received 5-year federal sentences. Jones received a sentence of time served in approximately March 2022, reflecting her lesser role in the conspiracy and her youth.
DESC,
    'state'      => 'Georgia',
    'gender'     => 'Female',
    'ideologies' => ['Anti-fascism', 'Black Lives Matter'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Federal arson conspiracy (18 U.S.C. § 844) — in connection with destruction of five USPS mail trucks and a police car in Atlanta, Georgia, fall 2020; co-defendants John Wade and Ellie Brett each sentenced to 5 years',
        'convicted'          => 'Yes — guilty plea',
        'sentence'           => 'Time served (approximately March 2022)',
    ]],
]);

// ─── 7. Ayoub Tabri (Philadelphia PSP vehicle arson, May 2020) ───────────────
addPrisoner([
    'name'       => 'Ayoub Tabri',
    'first_name' => 'Ayoub',
    'last_name'  => 'Tabri',
    'description' => <<<'DESC'
Ayoub Tabri is an Arlington, Virginia resident who traveled to Philadelphia and, during the May 30, 2020 George Floyd uprising, threw a lit road flare into a Pennsylvania State Police SUV parked near City Hall, engulfing the vehicle in flames. He confessed to FBI investigators and pleaded guilty in March 2022 in the Eastern District of Pennsylvania. He was sentenced to 364 days in federal prison in July 2022 — one day under the one-year threshold — and released approximately July 2023.
DESC,
    'state'      => 'Virginia',
    'gender'     => 'Male',
    'ideologies' => ['Black Lives Matter', 'Racial Justice'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Arson of property belonging to a government agency (18 U.S.C. § 844(f)) — threw a lit road flare into a Pennsylvania State Police SUV near Philadelphia City Hall during May 30, 2020 George Floyd uprising',
        'arrest_date'        => '2022-03-01',
        'incarceration_date' => '2022-07-01',
        'release_date'       => '2023-07-01',
        'imprisoned_for_days' => 364,
        'convicted'          => 'Yes — guilty plea (March 2022)',
        'sentence'           => '364 days federal prison',
    ]],
]);

// ─── 8. Marc Castillo (Muscatine BLM vehicle attack, Iowa, 2020) ──────────────
addPrisoner([
    'name'       => 'Marc Castillo',
    'first_name' => 'Marc',
    'last_name'  => 'Castillo',
    'description' => <<<'DESC'
Marc Castillo is a Muscatine, Iowa man who on August 9, 2020, during the period of widespread BLM protest activity across Iowa, participated with his brother Gilberto in driving a vehicle toward the Muscatine Public Safety Building. Both men then jumped from the moving vehicle, which struck a flagpole and stairway before coming to rest. Originally charged with state terrorism (up to 50 years). Marc pleaded guilty to lesser charges including assault on a peace officer and second-degree criminal mischief, and was sentenced in April 2021 to 10 years in Iowa state prison.
DESC,
    'state'      => 'Iowa',
    'gender'     => 'Male',
    'ideologies' => ['Black Lives Matter', 'Racial Justice'],
    'era'        => '2020s',
    'in_custody' => true,
    'released'   => false,
    'cases' => [[
        'inst_name'          => 'Iowa Department of Corrections',
        'inst_state'         => 'Iowa',
        'charges'            => 'Assault on a peace officer; second-degree criminal mischief; criminal gang participation (original terrorism charge up to 50 years dropped in plea) — for driving a vehicle toward the Muscatine Public Safety Building on August 9, 2020 during BLM protest activity',
        'arrest_date'        => '2020-08-09',
        'incarceration_date' => '2021-04-01',
        'imprisoned_for_days' => 1919,
        'convicted'          => 'Yes — guilty plea',
        'sentence'           => '10 years Iowa state prison',
    ]],
]);

// ─── 9. Gilberto Castillo (Muscatine BLM vehicle attack, Iowa, 2020) ──────────
addPrisoner([
    'name'       => 'Gilberto Castillo',
    'first_name' => 'Gilberto',
    'last_name'  => 'Castillo',
    'description' => <<<'DESC'
Gilberto Castillo is a Muscatine, Iowa man who, alongside his brother Marc Castillo, drove a vehicle toward the Muscatine Public Safety Building on August 9, 2020, during the BLM protest summer. Originally charged with terrorism (up to 50 years), assault on a police officer, and multiple other counts. Gilberto was the driver of the vehicle and pleaded guilty to lesser charges including second-degree criminal mischief, operation while intoxicated, criminal gang participation, and assault on a peace officer. He was sentenced in May 2021 to 12 years in Iowa state prison — the longer sentence of the two brothers, reflecting his role as the driver.
DESC,
    'state'      => 'Iowa',
    'gender'     => 'Male',
    'ideologies' => ['Black Lives Matter', 'Racial Justice'],
    'era'        => '2020s',
    'in_custody' => true,
    'released'   => false,
    'cases' => [[
        'inst_name'          => 'Iowa Department of Corrections',
        'inst_state'         => 'Iowa',
        'charges'            => 'Second-degree criminal mischief; operation while intoxicated; criminal gang participation; assault on a peace officer (original terrorism charge up to 50 years dropped in plea) — driver of vehicle directed at Muscatine Public Safety Building on August 9, 2020',
        'arrest_date'        => '2020-08-09',
        'incarceration_date' => '2021-05-01',
        'imprisoned_for_days' => 1889,
        'convicted'          => 'Yes — guilty plea',
        'sentence'           => '12 years Iowa state prison',
    ]],
]);

// ─── 10. William Pierce (Portland anti-ICE encampment, 2018) ──────────────────
addPrisoner([
    'name'       => 'William Pierce',
    'first_name' => 'William',
    'last_name'  => 'Pierce',
    'description' => <<<'DESC'
William Pierce is a Portland, Oregon immigrant rights and anti-ICE activist who had been camping outside Portland City Hall for weeks in a protest encampment opposing ICE enforcement policy. In August 2018, during a confrontation outside City Hall, Pierce struck a passerby in the face multiple times with a PVC pipe after an argument broke out between the passerby and other protesters, damaging the victim's vision in his left eye. Pierce pleaded guilty and was sentenced in October 2019 to 36 months in Oregon state prison.
DESC,
    'state'      => 'Oregon',
    'gender'     => 'Male',
    'ideologies' => ['Anti-ICE', 'Immigrant rights', 'Anarchism'],
    'era'        => '2010s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Oregon Department of Corrections',
        'inst_state'         => 'Oregon',
        'charges'            => 'Assault causing serious physical injury (attack with PVC pipe causing vision damage) — August 2018, during ongoing anti-ICE protest encampment at Portland City Hall',
        'arrest_date'        => '2018-08-01',
        'incarceration_date' => '2019-10-01',
        'release_date'       => '2022-10-01',
        'imprisoned_for_days' => 1095,
        'convicted'          => 'Yes — guilty plea',
        'sentence'           => '36 months Oregon state prison',
    ]],
]);

// ─── 11. Jacob Greenburg (Seattle CHOP/CHAZ bat attack + Molotov, 2020) ────────
addPrisoner([
    'name'       => 'Jacob Greenburg',
    'first_name' => 'Jacob',
    'last_name'  => 'Greenburg',
    'description' => <<<'DESC'
Jacob Greenburg is a Washington state activist who was convicted for his participation in black bloc activities during anti-police brutality protests in Seattle's Capitol Hill neighborhood near the former CHOP/CHAZ zone in September 2020. He struck Seattle police officer Jose Jimenez in the head with a metal baseball bat — the officer was saved by his bicycle helmet — and helped light and throw Molotov cocktails targeting the Seattle Police Department East Precinct. The bat attack was captured on video and went viral. He was sentenced in March 2022 to 60 months (5 years) in Washington state prison plus 18 months community custody. His stepmother is former Washington State Democratic legislator Laura Ruderman.
DESC,
    'state'      => 'Washington',
    'gender'     => 'Male',
    'ideologies' => ['Anti-fascism', 'Black Lives Matter', 'Anti-Police Brutality'],
    'era'        => '2020s',
    'in_custody' => true,
    'released'   => false,
    'cases' => [[
        'inst_name'          => 'Washington Department of Corrections',
        'inst_state'         => 'Washington',
        'charges'            => 'First-degree assault with a deadly weapon (metal baseball bat attack on Seattle police officer); first-degree attempted arson; first-degree reckless burning (Molotov cocktails at Seattle PD East Precinct) — September 2020, Capitol Hill/CHOP zone protests, Seattle',
        'arrest_date'        => '2020-10-01',
        'incarceration_date' => '2022-03-01',
        'imprisoned_for_days' => 1584,
        'convicted'          => 'Yes — jury verdict',
        'sentence'           => '60 months (5 years) Washington state prison; 18 months community custody',
    ]],
]);

// ─── 12. Steven M. Fitch (Omaha Molotov, May 2020) ───────────────────────────
addPrisoner([
    'name'       => 'Steven M. Fitch',
    'first_name' => 'Steven',
    'last_name'  => 'Fitch',
    'description' => <<<'DESC'
Steven M. Fitch is a Council Bluffs, Iowa resident who was found carrying a Molotov cocktail during BLM protests in downtown Omaha, Nebraska on May 31, 2020. Officers discovered in his front pants pocket a glass bottle with liquid and a red rag fuse that field-tested positive for gasoline, along with multiple lighters. The device was in operating condition and capable of exploding when ignited. Charged in the District of Nebraska, Fitch pleaded guilty to unlawful possession of a destructive device under the National Firearms Act and was sentenced to 30 months in federal prison by U.S. District Judge Brian C. Buescher.
DESC,
    'state'      => 'Nebraska',
    'gender'     => 'Male',
    'ideologies' => ['Black Lives Matter', 'Anti-Police Brutality'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Unlawful possession of a destructive device (Molotov cocktail classified as a firearm under the National Firearms Act, 26 U.S.C. § 5845(f)) — found on his person during BLM protests in downtown Omaha, Nebraska, May 31, 2020',
        'arrest_date'        => '2020-05-31',
        'imprisoned_for_days' => 913,
        'convicted'          => 'Yes — guilty plea',
        'judge'              => 'U.S. District Judge Brian C. Buescher, District of Nebraska',
        'sentence'           => '30 months federal prison; 3 years supervised release',
    ]],
]);

// ─── 13. Christian Rea (Naperville explosive device, June 2020) ───────────────
addPrisoner([
    'name'       => 'Christian Rea',
    'first_name' => 'Christian',
    'last_name'  => 'Rea',
    'description' => <<<'DESC'
Christian Rea is an Aurora, Illinois man who was 19 years old when he threw an explosive device near a police vehicle and several officers during a BLM protest in Naperville, Illinois in June 2020. The device detonated, causing an explosion and panic in the crowd, and injured one officer. Rea was arrested on June 25, 2020, pleaded guilty to federal civil disorder charges, and was sentenced to 1 year in federal prison plus $13,585.66 in restitution to the City of Naperville.
DESC,
    'state'      => 'Illinois',
    'gender'     => 'Male',
    'ideologies' => ['Black Lives Matter', 'Anti-Police Brutality'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Obstruction of law enforcement during civil disorder (18 U.S.C. § 231) — threw an explosive device near police officers during BLM protest in Naperville, Illinois, June 2020',
        'arrest_date'        => '2020-06-25',
        'imprisoned_for_days' => 365,
        'convicted'          => 'Yes — guilty plea',
        'sentence'           => '1 year federal prison; $13,585.66 restitution to the City of Naperville',
    ]],
]);

// ─── 14. Tyre Wayne Means Jr. (Seattle police car arson + rifle theft, 2020) ──
addPrisoner([
    'name'       => 'Tyre Wayne Means Jr.',
    'first_name' => 'Tyre',
    'last_name'  => 'Means',
    'race'       => 'Black',
    'description' => <<<'DESC'
Tyre Wayne Means Jr. is a man who, during BLM protests in Seattle on May 30, 2020, helped set fire to a Seattle Police Department patrol car by lighting a paper towel and throwing it into the vehicle, then removed an assault-style rifle from another damaged police car. At sentencing he told the judge "the days of just rolling over to police brutality are over" and stated "I have no regrets." He was sentenced on June 11, 2021 by U.S. District Judge Richard Jones in the Western District of Washington to 60 months (5 years) — the mandatory minimum sentence for theft of a firearm during a crime of violence. He was incarcerated at USP Victorville in Adelanto, California and released in August 2025.
DESC,
    'state'      => 'Washington',
    'gender'     => 'Male',
    'ideologies' => ['Black Lives Matter', 'Anti-Police Brutality'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'USP Victorville',
        'inst_city'          => 'Adelanto',
        'inst_state'         => 'California',
        'charges'            => 'Arson of a Seattle Police Department patrol car; theft of an assault-style rifle from a damaged police vehicle (18 U.S.C. § 924(c)) — May 30, 2020 George Floyd uprising, Seattle, Washington',
        'incarceration_date' => '2021-06-11',
        'release_date'       => '2025-08-01',
        'imprisoned_for_days' => 1512,
        'convicted'          => 'Yes — guilty plea',
        'judge'              => 'U.S. District Judge Richard Jones, Western District of Washington',
        'sentence'           => '60 months (5 years) federal prison; 3 years supervised release (mandatory minimum for theft of firearm during crime of violence)',
    ]],
]);

// ─── 15. Marquon Clark (Madison City-County Building firebombing, June 2020) ──
addPrisoner([
    'name'       => 'Marquon Clark',
    'first_name' => 'Marquon',
    'last_name'  => 'Clark',
    'race'       => 'Black',
    'description' => <<<'DESC'
Marquon Clark is a Black social justice and anti-capitalist activist who firebombed the Madison City-County Building during George Floyd protests on June 24, 2020. He threw projectiles through windows and then threw a lit roll of paper towels through the broken windows, starting a fire in a building occupied by over 250 people, including 182 adults and juveniles held in the county jail above. Community supporters marched demanding his release and he has been described as a Black political prisoner by various solidarity groups. Clark pleaded guilty in January 2021 and was sentenced on June 2, 2021 by U.S. District Judge James D. Peterson to 7 years in federal prison, to run concurrently with a prior state sentence of 5 years 8 months.
DESC,
    'state'      => 'Wisconsin',
    'gender'     => 'Male',
    'ideologies' => ['Black Liberation', 'Anti-Capitalism', 'Social Justice'],
    'era'        => '2020s',
    'in_custody' => true,
    'released'   => false,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Federal arson (18 U.S.C. § 844(i)) — threw projectiles through windows and ignited fire in Madison City-County Building (which contained 250+ occupants including 182 in county jail above) during George Floyd protests, June 24, 2020',
        'arrest_date'        => '2020-06-30',
        'incarceration_date' => '2021-06-02',
        'imprisoned_for_days' => 1856,
        'convicted'          => 'Yes — guilty plea (January 2021)',
        'judge'              => 'U.S. District Judge James D. Peterson, Western District of Wisconsin',
        'sentence'           => '7 years federal prison (concurrent with 5 years 8 months Wisconsin state sentence for prior unrelated convictions)',
    ]],
]);

// ─── 16. Bryce Michael Williams (Minneapolis 3rd Precinct burning, May 2020) ──
addPrisoner([
    'name'       => 'Bryce Michael Williams',
    'first_name' => 'Bryce',
    'last_name'  => 'Williams',
    'race'       => 'Black',
    'description' => <<<'DESC'
Bryce Michael Williams is a former college basketball player and TikTok influencer who helped burn the Minneapolis Police Department Third Precinct on May 28, 2020, during the George Floyd uprising. Surveillance footage showed him inside the precinct fence holding a Molotov cocktail while co-conspirator Davon Turner lit the wick; Williams then threw a box onto the flames. He was one of four co-conspirators federally charged in the burning, which became one of the defining images of the 2020 George Floyd uprising. Williams pleaded guilty and was sentenced on June 7, 2021 by U.S. District Judge Patrick Schiltz — who noted he was "a good person who made a terrible mistake" and that he was the first in his family to graduate college — to 27 months in federal prison. He was released approximately September 2023.
DESC,
    'state'      => 'Minnesota',
    'gender'     => 'Male',
    'ideologies' => ['Black Liberation', 'Anti-Police Brutality', 'Black Lives Matter'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Federal conspiracy to commit arson (18 U.S.C. § 844(i)) — Minneapolis Police Department Third Precinct burning, May 28, 2020 George Floyd uprising',
        'arrest_date'        => '2020-05-28',
        'incarceration_date' => '2021-06-07',
        'release_date'       => '2023-09-07',
        'imprisoned_for_days' => 822,
        'convicted'          => 'Yes — guilty plea',
        'judge'              => 'U.S. District Judge Patrick Schiltz, District of Minnesota',
        'sentence'           => '27 months federal prison; 2 years supervised release; $12 million restitution (shared among co-conspirators)',
    ]],
]);

// ─── 17. Davon DeAndre Turner (Minneapolis 3rd Precinct burning, May 2020) ────
addPrisoner([
    'name'       => 'Davon DeAndre Turner',
    'first_name' => 'Davon',
    'last_name'  => 'Turner',
    'race'       => 'Black',
    'description' => <<<'DESC'
Davon DeAndre Turner is a St. Paul, Minnesota man who participated in the burning of the Minneapolis Police Department Third Precinct on May 28, 2020, during the George Floyd uprising following the police killing of George Floyd. Surveillance footage showed Turner carrying a Molotov cocktail that co-conspirator Bryce Williams lit and bringing it inside the precinct building to start or accelerate the fires. Turner pleaded guilty in January 2021 and was sentenced on May 13, 2021 to 3 years in federal prison. He was released approximately May 2024.
DESC,
    'state'      => 'Minnesota',
    'gender'     => 'Male',
    'ideologies' => ['Black Liberation', 'Anti-Police Brutality', 'Black Lives Matter'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Federal conspiracy to commit arson (18 U.S.C. § 844(i)) — Minneapolis Police Department Third Precinct burning, May 28, 2020 George Floyd uprising',
        'arrest_date'        => '2020-05-28',
        'incarceration_date' => '2021-05-13',
        'release_date'       => '2024-05-13',
        'imprisoned_for_days' => 1096,
        'convicted'          => 'Yes — guilty plea (January 2021)',
        'sentence'           => '3 years federal prison; $12 million restitution (shared among co-conspirators)',
    ]],
]);

// ─── 18. Jonathan Montanez (Fargo police car attack, May 2020) ───────────────
addPrisoner([
    'name'       => 'Jonathan Montanez',
    'first_name' => 'Jonathan',
    'last_name'  => 'Montanez',
    'description' => <<<'DESC'
Jonathan Montanez is a Moorhead, Minnesota man who jumped on top of an occupied Fargo Police Department vehicle and slammed his fists on it, causing extensive damage, during George Floyd protests in Fargo, North Dakota on May 30, 2020. He was also accused of provoking other protesters to become violent and destructive. The protests had begun peacefully in response to the police killing of George Floyd. Montanez pleaded guilty to one federal count of civil disorder and was sentenced to 2 years in federal prison. His conviction was upheld by the U.S. 8th Circuit Court of Appeals in 2022.
DESC,
    'state'      => 'North Dakota',
    'gender'     => 'Male',
    'ideologies' => ['Anti-Police Brutality', 'Black Lives Matter'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Obstruction of law enforcement during civil disorder (18 U.S.C. § 231) — jumped on top of occupied Fargo Police Department vehicle and damaged it during George Floyd/BLM protests in Fargo, North Dakota, May 30, 2020',
        'arrest_date'        => '2020-05-30',
        'incarceration_date' => '2021-01-01',
        'release_date'       => '2023-01-01',
        'imprisoned_for_days' => 730,
        'convicted'          => 'Yes — guilty plea (December 2020)',
        'sentence'           => '2 years federal prison; 3 years supervised release',
    ]],
]);

// ─── 19. Cyan Waters Bass (Portland Justice Center Molotov, Sept 2020) ─────────
addPrisoner([
    'name'        => 'Cyan Waters Bass',
    'first_name'  => 'Cyan',
    'last_name'   => 'Bass',
    'check_names' => ['Cyan Waters Bass', 'Cyan Bass'],
    'description' => <<<'DESC'
Cyan Waters Bass is a Portland, Oregon activist convicted of setting fire to the Multnomah County Justice Center during a September 23, 2020 protest against the Breonna Taylor grand jury decision. Bass used a wrist-rocket slingshot to break windows, ignited a Molotov cocktail and threw it toward police officers, and also threw an unexploded destructive device at an officer, causing an estimated $46,000 in damage. Bass pleaded guilty to all charges and was sentenced in July 2021 to 48 months (4 years) in Oregon state prison. Bass was released approximately mid-2025.
DESC,
    'state'      => 'Oregon',
    'gender'     => 'Male',
    'ideologies' => ['Anti-fascism', 'Anti-Police Brutality'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Oregon Department of Corrections',
        'inst_state'         => 'Oregon',
        'charges'            => 'Arson in the first degree; attempted assault in the first degree; criminal mischief in the first degree; unlawful possession of an explosive device; riot — Multnomah County Justice Center, Portland, September 23, 2020 protest against Breonna Taylor grand jury decision',
        'arrest_date'        => '2020-09-23',
        'incarceration_date' => '2021-07-01',
        'release_date'       => '2025-07-01',
        'imprisoned_for_days' => 1461,
        'convicted'          => 'Yes — guilty plea',
        'sentence'           => '48 months (4 years) Oregon state prison',
    ]],
]);

// ─── 20. Gavaughn Streeter-Hillerich (Portland North Precinct dumpster arson, 2020)
addPrisoner([
    'name'       => 'Gavaughn Streeter-Hillerich',
    'first_name' => 'Gavaughn',
    'last_name'  => 'Streeter-Hillerich',
    'description' => <<<'DESC'
Gavaughn Streeter-Hillerich is a Portland, Oregon activist who set a dumpster on fire and pushed it against the plywood-covered windows of the Portland Police Bureau North Precinct building during a protest on June 26, 2020. Nearly 20 officers were inside the building at the time. Streeter-Hillerich originally faced federal arson charges, which were dropped in exchange for a guilty plea to state charges including arson, coercion, unlawful use of a weapon, and two counts of fourth-degree assault. He was sentenced in June 2021 to 60 months (5 years) in Oregon state prison plus 3 years post-prison supervision.
DESC,
    'state'      => 'Oregon',
    'gender'     => 'Male',
    'ideologies' => ['Anti-Police Brutality', 'Black Lives Matter'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Oregon Department of Corrections',
        'inst_state'         => 'Oregon',
        'charges'            => 'Arson first degree; coercion; unlawful use of a weapon; two counts of fourth-degree assault — set a dumpster on fire and pushed it against the Portland Police Bureau North Precinct building (with ~20 officers inside) during protest, June 26, 2020; federal charges dropped in exchange for state plea',
        'incarceration_date' => '2021-06-01',
        'release_date'       => '2026-06-01',
        'imprisoned_for_days' => 1826,
        'convicted'          => 'Yes — guilty plea',
        'sentence'           => '60 months (5 years) Oregon state prison; 3 years post-prison supervision',
    ]],
]);

// ─── 21. Jessica Lopez (Lancaster Ricardo Munoz protests, 2020) ──────────────
addPrisoner([
    'name'       => 'Jessica Lopez',
    'first_name' => 'Jessica',
    'last_name'  => 'Lopez',
    'description' => <<<'DESC'
Jessica Lopez is a Lancaster, Pennsylvania woman convicted by jury of riot and criminal conspiracy for her role in protests outside the Lancaster City Bureau of Police station on September 13-14, 2020, following the fatal police shooting of Ricardo Munoz by officers responding to a call. Prosecutors argued she escalated the unrest. She was sentenced on April 4, 2023 to 13 to 30 months in Pennsylvania state prison and was released approximately May 2024 upon serving the 13-month minimum.
DESC,
    'state'      => 'Pennsylvania',
    'gender'     => 'Female',
    'ideologies' => ['Anti-Police Brutality'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Pennsylvania Department of Corrections',
        'inst_state'         => 'Pennsylvania',
        'charges'            => 'Riot (F3); criminal conspiracy to commit riot (F3); failure to disperse; disorderly conduct; obstruction of highways; defiant trespass — protests outside Lancaster City Bureau of Police following fatal police shooting of Ricardo Munoz, September 13-14, 2020',
        'incarceration_date' => '2023-04-04',
        'release_date'       => '2024-05-04',
        'imprisoned_for_days' => 396,
        'convicted'          => 'Yes — jury verdict',
        'sentence'           => '13 to 30 months Pennsylvania state prison',
    ]],
]);

// ─── 22. Jamal Newman Jr. (Lancaster Ricardo Munoz protests, 2020) ────────────
addPrisoner([
    'name'       => 'Jamal Newman Jr.',
    'first_name' => 'Jamal',
    'last_name'  => 'Newman',
    'description' => <<<'DESC'
Jamal Newman Jr. is a Lancaster, Pennsylvania man who pleaded guilty to riot and related charges, including dangerous burning, stemming from the September 13-14, 2020 protests outside the Lancaster City Bureau of Police station following the fatal police shooting of Ricardo Munoz. He was initially charged with Arson (F1) and Institutional Vandalism (F3); those charges were reduced in a plea agreement. He was sentenced on May 3, 2022 to a maximum of 23 months in Pennsylvania state prison plus 2 years probation.
DESC,
    'state'      => 'Pennsylvania',
    'gender'     => 'Male',
    'ideologies' => ['Anti-Police Brutality'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Pennsylvania Department of Corrections',
        'inst_state'         => 'Pennsylvania',
        'charges'            => 'Riot; criminal conspiracy; failure to disperse; obstruction of highways; disorderly conduct; loitering; defiant trespass; dangerous burning (original arson F1 and institutional vandalism F3 reduced in plea) — protests outside Lancaster City Bureau of Police following fatal police shooting of Ricardo Munoz, September 13-14, 2020',
        'incarceration_date' => '2022-05-03',
        'release_date'       => '2024-04-03',
        'imprisoned_for_days' => 701,
        'convicted'          => 'Yes — guilty plea',
        'sentence'           => 'Time served to 23 months Pennsylvania state prison; 2 years probation',
    ]],
]);

// ─── 23. Shador Jackson (Minneapolis Target arson, Aug 2020) ─────────────────
addPrisoner([
    'name'       => 'Shador Jackson',
    'first_name' => 'Shador',
    'last_name'  => 'Jackson',
    'description' => <<<'DESC'
Shador Jackson is a Richfield, Minnesota man who used a construction sign to break the glass entrance of Target Corporation's headquarters building in Minneapolis, then intentionally set a fire in the mailroom on August 26, 2020, during protests sparked by the death of Eddie Sole Jr. (falsely reported at the time as a police shooting). Jackson pleaded guilty to federal conspiracy to commit arson and was sentenced in June 2021 to 33 months in federal prison. He was released approximately March 2024. Co-defendants Victor Devon Edwards received 100 months and Leroy Lemonte Perry Williams received 120 months in separate proceedings.
DESC,
    'state'      => 'Minnesota',
    'gender'     => 'Male',
    'ideologies' => ['Anti-Police Brutality', 'Black Lives Matter'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Conspiracy to commit arson (18 U.S.C. § 844(i)) — broke into Target Corporation headquarters, Minneapolis and set fire in the mailroom during August 26, 2020 civil unrest',
        'incarceration_date' => '2021-06-01',
        'release_date'       => '2024-03-01',
        'imprisoned_for_days' => 1004,
        'convicted'          => 'Yes — guilty plea',
        'sentence'           => '33 months federal prison',
    ]],
]);

// ─── 24. Kelly Thomas Jackson (Seattle police car Molotov, May 2020) ──────────
addPrisoner([
    'name'       => 'Kelly Thomas Jackson',
    'first_name' => 'Kelly',
    'last_name'  => 'Jackson',
    'description' => <<<'DESC'
Kelly Thomas Jackson is an Edmonds, Washington man who was 21 years old when he used Molotov cocktails to set fire to two Seattle Police Department vehicles parked in downtown Seattle during a George Floyd protest on May 30, 2020. He was identified from video footage; Apple provided iCloud account data to federal investigators. Jackson pleaded guilty in January 2021 to two counts of unlawful possession of destructive devices and was sentenced in March 2021 by U.S. District Judge James L. Robart to 40 months in federal prison. He was released approximately July 2024.
DESC,
    'state'      => 'Washington',
    'gender'     => 'Male',
    'ideologies' => ['Anti-Police Brutality', 'Black Lives Matter'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Two counts of unlawful possession of a destructive device (Molotov cocktails classified under National Firearms Act) — used to set fire to two Seattle Police Department vehicles during George Floyd protests, May 30, 2020',
        'arrest_date'        => '2020-09-01',
        'incarceration_date' => '2021-03-01',
        'release_date'       => '2024-07-01',
        'imprisoned_for_days' => 1219,
        'convicted'          => 'Yes — guilty plea (January 2021)',
        'judge'              => 'U.S. District Judge James L. Robart, Western District of Washington',
        'sentence'           => '40 months federal prison; 3 years supervised release',
    ]],
]);

// ─── 25. Carlos Espriu (La Quinta Republican women's HQ firebombing, 2020) ────
addPrisoner([
    'name'       => 'Carlos Espriu',
    'first_name' => 'Carlos',
    'last_name'  => 'Espriu',
    'description' => <<<'DESC'
Carlos Espriu is a Palm Desert, California man who firebombed the East Valley Republican Women Federated headquarters in La Quinta, California on May 31, 2020, during nationwide protests following George Floyd's death. He broke windows with a bat and threw a Molotov cocktail made of three taped bottles into the office, igniting a fire. He was arrested on September 9, 2020. Espriu pleaded guilty in federal court in March 2021 and in California state court in October 2021. He was sentenced in November 2021 to 5 years in federal prison and in December 2021 to 13 years in California state prison, the two sentences to run concurrently. The controlling 13-year state sentence keeps him in California state custody through approximately December 2034.
DESC,
    'state'      => 'California',
    'gender'     => 'Male',
    'ideologies' => ['Anti-fascism', 'Black Lives Matter'],
    'era'        => '2020s',
    'in_custody' => true,
    'released'   => false,
    'cases' => [[
        'inst_name'          => 'California Department of Corrections and Rehabilitation',
        'inst_state'         => 'California',
        'charges'            => 'Attempted arson of a building (federal, 18 U.S.C. § 844); arson of a non-dwelling; possession of a destructive or explosive device with fire-accelerant enhancement (California state) — firebombed East Valley Republican Women Federated headquarters, La Quinta, California, May 31, 2020',
        'arrest_date'        => '2020-09-09',
        'incarceration_date' => '2021-12-01',
        'imprisoned_for_days' => 1675,
        'convicted'          => 'Yes — guilty plea (federal March 2021; state October 2021)',
        'sentence'           => '5 years federal prison (November 2021); 13 years California state prison (December 2021); sentences concurrent; $5,426 restitution',
    ]],
]);

// ─── 26. Bryan Kelley (Portland laser assault on police officer, 2020) ─────────
addPrisoner([
    'name'       => 'Bryan Kelley',
    'first_name' => 'Bryan',
    'last_name'  => 'Kelley',
    'description' => <<<'DESC'
Bryan Kelley is a Portland, Oregon man convicted of a felony in connection with pointing a high-powered blue laser into the eyes of a Portland Police Bureau sergeant during a mass demonstration outside Portland City Hall on August 25-26, 2020. The laser was powerful enough to burn through paper and ignite dry material; the officer reported sustained eye impairment. After arrest and Miranda advisement, Kelley admitted he knew the laser could damage eyes. He was convicted of Assault in the Second Degree (felony), unlawful use of a weapon, and unlawful directing of light from a laser pointer in Multnomah County, Oregon state court.
DESC,
    'state'      => 'Oregon',
    'gender'     => 'Male',
    'ideologies' => ['Anti-Police Brutality', 'Black Lives Matter'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [[
        'inst_name'          => 'Oregon Department of Corrections',
        'inst_state'         => 'Oregon',
        'charges'            => 'Assault in the second degree (felony); unlawful use of a weapon; unlawful directing of light from a laser pointer (two counts) — pointed a high-powered blue laser at a Portland Police Bureau sergeant causing eye damage during protest outside Portland City Hall, August 25-26, 2020',
        'arrest_date'        => '2020-09-01',
        'convicted'          => 'Yes — jury verdict (Multnomah County Oregon state court)',
        'sentence'           => 'Felony conviction; specific prison term not publicly confirmed',
    ]],
]);

// ─── 27. Matthew Banta ("Commander Red", Wisconsin BLM/antifa, 2020) ──────────
addPrisoner([
    'name'       => 'Matthew Banta',
    'first_name' => 'Matthew',
    'last_name'  => 'Banta',
    'race'       => 'White',
    'description' => <<<'DESC'
Matthew Banta, known as "Commander Red" in anti-fascist circles, is a Neenah, Wisconsin organizer who was arrested twice during BLM protests in August 2020. At a protest in Waupaca, Wisconsin on August 1, 2020, he was accused of raising a loaded rifle at a police officer and struggling with officers. At a Green Bay protest on August 29, 2020, he was stopped en route to the demonstration and found carrying smoke grenades, fireworks, and what police described as a flamethrower (which Banta maintained was a propane hand torch without a propulsion system). A criminal complaint called him "known to be a violent Antifa member who incites violence in otherwise relatively peaceful protests." In the Brown County (Green Bay) case he was convicted of misdemeanor obstruction and bail jumping and sentenced in September 2021 to 90 days in jail. In a separate Waupaca County case he pleaded no contest in December 2022 to felony battery or threat to a law enforcement officer and received 3 years probation.
DESC,
    'state'      => 'Wisconsin',
    'gender'     => 'Male',
    'ideologies' => ['Anti-fascism', 'Black Lives Matter'],
    'era'        => '2020s',
    'in_custody' => false,
    'released'   => true,
    'cases' => [
        [
            'inst_name'          => 'Brown County Jail',
            'inst_city'          => 'Green Bay',
            'inst_state'         => 'Wisconsin',
            'charges'            => 'Obstruction of an officer (misdemeanor); bail jumping — two counts (Green Bay, Brown County case); arrested August 29, 2020 while en route to BLM protest carrying smoke grenades, fireworks, and a propane torch device',
            'arrest_date'        => '2020-08-29',
            'incarceration_date' => '2021-09-01',
            'release_date'       => '2021-12-01',
            'imprisoned_for_days' => 90,
            'convicted'          => 'Yes — jury verdict (Brown County)',
            'sentence'           => '90 days Brown County Jail; 2 years probation',
        ],
        [
            'inst_name'          => 'Waupaca County Jail',
            'inst_city'          => 'Waupaca',
            'inst_state'         => 'Wisconsin',
            'charges'            => 'Battery or threat to a law enforcement officer (felony, Waupaca County case); accused of raising a loaded rifle at an officer and struggling with officers during Waupaca BLM protest, August 1, 2020',
            'arrest_date'        => '2020-08-01',
            'imprisoned_for_days' => 0,
            'convicted'          => 'Yes — no contest plea (December 2022)',
            'sentence'           => '3 years probation (Waupaca County); no additional prison time',
        ],
    ],
]);

echo PHP_EOL . 'Done.' . PHP_EOL;
PHPEOF

echo "Adding 27 political prisoners from antifawatch.net scan (pages 400-499)..."
php artisan tinker --execute="require '/tmp/add_scan_400_500_batch1.php';"
echo "Script complete."
