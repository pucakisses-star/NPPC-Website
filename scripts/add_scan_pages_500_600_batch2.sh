#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_scan_pages_500_600_batch2.sh
# Adds 12 more confirmed imprisoned political prisoners from antifawatch.net scan
# pages 500–599 (second pass over Tier 2 federal arson/assault candidates).
set +e

cat > /tmp/add_pages_500_600_batch2.php << 'PHPEOF'
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
// antifawatch.net scan — pages 500–599, batch 2 (Tier 2 federal cases)
// -----------------------------------------------------------------------

// 1. Sam Resto
addPrisoner([
    'name'        => 'Sam Resto',
    'first_name'  => 'Sam',
    'last_name'   => 'Resto',
    'description' => <<<'DESC'
Sam Resto is an Elmhurst, Queens, New York man who spent approximately 27 months in federal pretrial detention for setting fire to a marked NYPD van on the Upper West Side of Manhattan during BLM protests on July 29, 2020. Resto smashed the van's window with a blunt object, poured gasoline inside, and set it ablaze before fleeing toward Central Park. He was identified through a discarded backpack recovered in the park containing clothing matching surveillance footage, a Guy Fawkes mask, a gasoline can, a hammer, and lighters, along with cell tower data and fingerprint evidence. He was arrested in August 2020 and held continuously until his November 2022 sentencing, at which point Judge Nicholas Garaufis imposed a sentence of time served.
DESC,
    'state'       => 'New York',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Federal arson (18 U.S.C. § 844(f)(1)) — smashed the window of a marked NYPD van, poured gasoline inside, and set it on fire on the Upper West Side of Manhattan during BLM protests, July 29, 2020; identified via a backpack recovered in Central Park containing matching clothing, a Guy Fawkes mask, a gasoline can, a hammer, and lighters',
        'arrest_date'         => '2020-08-13',
        'incarceration_date'  => '2020-08-13',
        'release_date'        => '2022-11-04',
        'imprisoned_for_days' => 813,
        'convicted'           => 'Yes — guilty plea',
        'sentence'            => 'Time served (approximately 27 months pretrial detention) + 3 years supervised release + $14,065.35 restitution',
        'judge'               => 'U.S. District Judge Nicholas G. Garaufis',
    ]],
]);

// 2. Elaine Carberry
addPrisoner([
    'name'        => 'Elaine Carberry',
    'first_name'  => 'Elaine',
    'last_name'   => 'Carberry',
    'description' => <<<'DESC'
Elaine Carberry is a Brooklyn, New York LGBTQ activist sentenced to federal prison for conspiring to burn a marked NYPD Homeless Outreach Unit van in Greenwich Village, Manhattan, during the early morning hours of July 15, 2020. Carberry and a co-defendant, Corey Smith, ignited an object and threw it into the van, then returned minutes later and used a flammable substance to reignite the fire and ensure the vehicle's complete destruction. She pleaded guilty to conspiracy to commit arson in September 2021. At sentencing, Judge Lewis J. Liman told the pair, "your passion to protect others led you to harm others," noting that the destroyed van served homeless New Yorkers.
DESC,
    'state'       => 'New York',
    'gender'      => 'Female',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Conspiracy to commit arson (18 U.S.C. § 371) — ignited an object and threw it into a marked NYPD Homeless Outreach Unit van, then returned to reignite it with a flammable substance, Greenwich Village, Manhattan, July 15, 2020',
        'arrest_date'         => '2020-08-13',
        'incarceration_date'  => '2022-06-20',
        'release_date'        => '2022-12-20',
        'imprisoned_for_days' => 183,
        'convicted'           => 'Yes — guilty plea (September 22, 2021)',
        'sentence'            => '6 months federal prison + 6 months home confinement + 3 years supervised release + 400 hours community service + $14,000 fine + $72,308.66 restitution (joint with co-defendant)',
        'judge'               => 'U.S. District Judge Lewis J. Liman',
    ]],
]);

// 3. Corey Smith
addPrisoner([
    'name'        => 'Corey Smith',
    'first_name'  => 'Corey',
    'last_name'   => 'Smith',
    'description' => <<<'DESC'
Corey Smith is a Brooklyn, New York man sentenced to federal prison as the co-defendant of Elaine Carberry in the burning of a marked NYPD Homeless Outreach Unit van in Greenwich Village, Manhattan, on July 15, 2020, during BLM protests. At his guilty plea, Smith stated: "During the height of the Black Lives Matter movement, I agreed with others to cause damage to a parked, empty NYPD van in New York." He was sentenced alongside Carberry in February 2022.
DESC,
    'state'       => 'New York',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Conspiracy to commit arson (18 U.S.C. § 371) — burned a marked NYPD Homeless Outreach Unit van in Greenwich Village, Manhattan, alongside co-defendant Elaine Carberry, July 15, 2020',
        'arrest_date'         => '2020-08-13',
        'incarceration_date'  => '2022-06-20',
        'release_date'        => '2022-12-20',
        'imprisoned_for_days' => 183,
        'convicted'           => 'Yes — guilty plea (September 22, 2021)',
        'sentence'            => '6 months federal prison + 6 months home confinement + 3 years supervised release + 400 hours community service + $72,308.66 restitution (joint with co-defendant)',
        'judge'               => 'U.S. District Judge Lewis J. Liman',
    ]],
]);

// 4. Nicholas Scaglione
addPrisoner([
    'name'        => 'Nicholas Scaglione',
    'first_name'  => 'Nicholas',
    'last_name'   => 'Scaglione',
    'description' => <<<'DESC'
Nicholas Scaglione is a Cranston, Rhode Island man sentenced to three years in federal prison for helping destroy a Providence Police cruiser during the June 2020 George Floyd protests. Scaglione climbed atop an unoccupied cruiser, threw an object at it, joined others in an unsuccessful attempt to flip it, and sprayed a flammable liquid into the vehicle, intensifying a fire that had already begun and resulting in the cruiser's total destruction. He later texted an associate, "that police cruiser that went up in flames last night can be replaced." He pleaded guilty to conspiracy to commit arson in April 2022.
DESC,
    'state'       => 'Rhode Island',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Conspiracy to commit arson — climbed atop an unoccupied Providence Police cruiser, attempted to flip it, and sprayed flammable liquid into it to intensify a fire that completely destroyed the vehicle, Providence, Rhode Island, June 2020',
        'arrest_date'         => '2020-08-19',
        'incarceration_date'  => '2022-09-14',
        'release_date'        => '2025-04-05',
        'imprisoned_for_days' => 934,
        'convicted'           => 'Yes — guilty plea (April 14, 2022)',
        'sentence'            => '36 months federal prison + 2 years supervised release + $52,166.80 restitution',
        'judge'               => 'U.S. District Judge Mary S. McElroy',
    ]],
]);

// 5. Christopher Tindal
addPrisoner([
    'name'        => 'Christopher Tindal',
    'first_name'  => 'Christopher',
    'last_name'   => 'Tindal',
    'description' => <<<'DESC'
Christopher Tindal is a Rochester, New York man serving a five-year federal sentence for setting fire to a Rochester Police Department vehicle outside the Public Safety Building during George Floyd protests on May 30, 2020. Tindal and a co-defendant used an aerosol can and open flame to destroy the vehicle; he was identified on video by distinctive clothing and a forearm tattoo. After pleading guilty to rioting in 2021, Tindal removed his court-ordered GPS ankle monitor in March 2022 and failed to appear for his scheduled sentencing the following month, fleeing before eventually being caught and pleading guilty to a second federal charge of failure to appear in 2023. He is incarcerated at USP Canaan in Waymart, Pennsylvania, with a projected release date of February 2028.
DESC,
    'state'       => 'New York',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => true,
    'released'    => false,
    'cases' => [[
        'institution_name'    => 'United States Penitentiary, Canaan',
        'institution_city'    => 'Waymart',
        'institution_state'   => 'Pennsylvania',
        'charges'             => 'Rioting and failure to appear — set fire to a Rochester Police Department vehicle with an aerosol can and open flame outside the Public Safety Building during May 30, 2020 protests; after pleading guilty, removed his GPS ankle monitor in March 2022 and fled rather than appear for sentencing, resulting in a second federal conviction',
        'arrest_date'         => '2020-07-31',
        'incarceration_date'  => '2023-08-24',
        'imprisoned_for_days' => 1044,
        'convicted'           => 'Yes — guilty plea to rioting (April 27, 2021) and guilty plea to failure to appear (May 3, 2023)',
        'sentence'            => '5 years federal prison',
        'judge'               => 'U.S. District Judge Charles J. Siragusa',
    ]],
]);

// 6. Tyvarh Nicholson
addPrisoner([
    'name'        => 'Tyvarh Nicholson',
    'first_name'  => 'Tyvarh',
    'last_name'   => 'Nicholson',
    'description' => <<<'DESC'
Tyvarh Nicholson is an Erie, Pennsylvania man sentenced to 40 months in federal prison for throwing Molotov cocktails at Erie police officers during BLM protests in downtown Erie on May 30, 2020. A community GoFundMe campaign raised money toward his initial state bail before the case was taken over federally. He pleaded guilty in August 2021 to possession of an unregistered destructive device; Judge David S. Cercone sentenced him well below the federal guidelines range of 70–87 months, crediting defense arguments that his criminal history had been over-represented in the guidelines calculation.
DESC,
    'state'       => 'Pennsylvania',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Possession of an unregistered destructive device (National Firearms Act) — threw Molotov cocktails at Erie Police officers during BLM protests in downtown Erie, Pennsylvania, May 30, 2020',
        'arrest_date'         => '2020-06-01',
        'incarceration_date'  => '2021-08-04',
        'release_date'        => '2024-06-04',
        'imprisoned_for_days' => 1035,
        'convicted'           => 'Yes — guilty plea (August 4, 2021)',
        'sentence'            => '40 months federal prison + 3 years supervised release',
        'judge'               => 'Senior U.S. District Judge David S. Cercone',
    ]],
]);

// 7. Micah Tillmon
addPrisoner([
    'name'        => 'Micah Tillmon',
    'first_name'  => 'Micah',
    'last_name'   => 'Tillmon',
    'description' => <<<'DESC'
Micah Tillmon is a West Hills, California man who was 19 years old when he was sentenced to 18 months in federal prison for setting fire to the Sake House by Hikari restaurant in downtown Santa Monica during BLM protests on May 31, 2020. Tillmon entered the restaurant after hours, without authorization, removed a tube-shaped incendiary device from his jacket, placed it behind the reception desk, and ignited a fire that spread throughout the building, forcing Santa Monica firefighters to repeatedly retreat for safety. The restaurant was permanently closed as a result. He was identified through security footage and social media, including his vehicle leaving the scene minutes after the fire began.
DESC,
    'state'       => 'California',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Possession of an unregistered destructive device — entered the Sake House by Hikari restaurant in Santa Monica after hours, placed an incendiary device behind the reception desk, and ignited a fire that spread throughout the building and permanently closed the business, May 31, 2020',
        'incarceration_date'  => '2022-04-06',
        'release_date'        => '2023-07-17',
        'imprisoned_for_days' => 467,
        'convicted'           => 'Yes — guilty plea (September 2021)',
        'sentence'            => '18 months federal prison',
        'judge'               => 'U.S. District Judge Michael W. Fitzgerald',
    ]],
]);

// 8. Devin Montgomery
addPrisoner([
    'name'        => 'Devin Montgomery',
    'first_name'  => 'Devin',
    'last_name'   => 'Montgomery',
    'description' => <<<'DESC'
Devin Montgomery is a Pittsburgh, Pennsylvania man serving a four-year federal sentence for setting fire to an unmarked Pittsburgh police vehicle near PPG Paints Arena and breaking into a Dollar Bank branch during BLM protests on May 30, 2020. Montgomery used lit objects to ignite the interior of the police vehicle, then used rocks to smash windows and enter the bank. He was identified on video by a distinctive tattoo and an Antonio Brown #84 Steelers jersey. Chief Judge Mark R. Hornak sentenced him to 48 months in prison in March 2023 after Montgomery pleaded guilty to conspiracy against the United States and bank burglary.
DESC,
    'state'       => 'Pennsylvania',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => true,
    'released'    => false,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Conspiracy against the United States and bank burglary — used lit objects to ignite an unmarked Pittsburgh police vehicle near PPG Paints Arena, then smashed windows and broke into a Dollar Bank branch, May 30, 2020',
        'incarceration_date'  => '2023-03-28',
        'imprisoned_for_days' => 1193,
        'convicted'           => 'Yes — guilty plea',
        'sentence'            => '4 years (48 months) federal prison + 3 years supervised release + $25,635.50 restitution',
        'judge'               => 'Chief U.S. District Judge Mark R. Hornak',
    ]],
]);

// 9. Joseph Ybarra
addPrisoner([
    'name'        => 'Joseph Ybarra',
    'first_name'  => 'Joseph',
    'last_name'   => 'Ybarra',
    'description' => <<<'DESC'
Joseph Ybarra is a 21-year-old who was sentenced to time served after roughly 18 months in federal pretrial detention for throwing an incendiary device at the Mark O. Hatfield U.S. Courthouse in Portland, Oregon during protests on July 22, 2020. Ybarra had arrived in Portland by bus from Seattle the day before, originally intending to turn himself in on an unrelated Multnomah County warrant, and became swept up in the protest. He lit and threw the device at the courthouse three times; it fell to the ground and did not fully ignite, and no one was injured. He pleaded guilty to attempted arson in January 2022.
DESC,
    'state'       => 'Oregon',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Attempted arson — lit an incendiary device and threw it at the Mark O. Hatfield U.S. Courthouse three times during protests, Portland, Oregon, July 22, 2020; device did not fully ignite and no one was injured',
        'incarceration_date'  => '2020-07-23',
        'release_date'        => '2022-04-15',
        'imprisoned_for_days' => 631,
        'convicted'           => 'Yes — guilty plea (January 2022)',
        'sentence'            => 'Time served (approximately 18 months pretrial detention)',
        'judge'               => 'U.S. District Judge Karin J. Immergut',
    ]],
]);

// 10. Lateesha Richards
addPrisoner([
    'name'        => 'Lateesha Richards',
    'first_name'  => 'Lateesha',
    'last_name'   => 'Richards',
    'description' => <<<'DESC'
Lateesha Richards is a Salt Lake City, Utah woman sentenced to 20 months in federal prison for federal arson arising from the May 30, 2020 BLM protests in Salt Lake City. She was identified on video by a distinctive neck tattoo while standing in front of a burning Salt Lake City police vehicle during the unrest.
DESC,
    'state'       => 'Utah',
    'gender'      => 'Female',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Federal arson — identified on video by a distinctive neck tattoo standing in front of a burning Salt Lake City police vehicle during BLM protests, May 30, 2020',
        'convicted'           => 'Yes',
        'sentence'            => '20 months federal prison (sentenced August 3, 2021)',
    ]],
]);

// 11. Jacob Gaines
addPrisoner([
    'name'        => 'Jacob Gaines',
    'first_name'  => 'Jacob',
    'last_name'   => 'Gaines',
    'description' => <<<'DESC'
Jacob Gaines is a Texas man who was sentenced to 46 months in federal prison for striking a Deputy U.S. Marshal with a construction hammer during protests at the Mark O. Hatfield U.S. Courthouse in Portland, Oregon, in the early morning of July 11, 2020. Gaines used a 4-pound DeWalt hammer to breach a plywood barricade at a side entrance, and when deputies emerged to prevent trespassing, struck one three times in the shoulder, neck, and upper back before being subdued and arrested. He was held in continuous federal custody from his arrest until sentencing. At sentencing, Judge Karin Immergut rejected a joint prosecution-defense recommendation of 37 months and imposed 46 months instead, calling the attack an "ambush," not lawful protest behavior.
DESC,
    'state'       => 'Oregon',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Assault of a federal officer with a deadly or dangerous weapon (18 U.S.C. § 111(a)(1) and (b)) — used a 4-pound construction hammer to breach a plywood barricade at the Hatfield federal courthouse and struck a Deputy U.S. Marshal three times, Portland, Oregon, July 11, 2020',
        'arrest_date'         => '2020-07-11',
        'incarceration_date'  => '2020-07-11',
        'release_date'        => '2024-04-01',
        'imprisoned_for_days' => 1360,
        'convicted'           => 'Yes — guilty plea (September 8, 2021)',
        'sentence'            => '46 months federal prison + 3 years supervised release',
        'judge'               => 'U.S. District Judge Karin J. Immergut',
    ]],
]);

// 12. Jordan Coyne
addPrisoner([
    'name'        => 'Jordan Coyne',
    'first_name'  => 'Jordan',
    'last_name'   => 'Coyne',
    'description' => <<<'DESC'
Jordan Coyne is a Pittsburgh (South Side Slopes) man sentenced to 18 months in federal prison for throwing rocks, bricks, concrete, and a tear gas canister at occupied Pittsburgh police vehicles during protests following the murder of George Floyd on May 30, 2020. His actions cracked a windshield and shattered a rear window of police vehicles, and a tear gas canister he threw struck an officer's hand, causing bleeding and swelling. He pleaded guilty in March 2022 to federal obstruction of law enforcement during civil disorder. At sentencing, the judge imposed 18 months — below the 27-to-33-month guidelines range — citing Coyne's history of psychiatric hospitalization and suicide attempts, including one following his guilty plea.
DESC,
    'state'       => 'Pennsylvania',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Obstruction of law enforcement during civil disorder (18 U.S.C. § 231) — threw concrete, bricks, rocks, and a tear gas canister at occupied Pittsburgh police vehicles during May 30, 2020 protests, cracking a windshield and shattering a rear window; a tear gas canister struck an officer\'s hand, causing bleeding and swelling',
        'incarceration_date'  => '2023-03-10',
        'release_date'        => '2024-06-24',
        'imprisoned_for_days' => 471,
        'convicted'           => 'Yes — guilty plea (March 2, 2022)',
        'sentence'            => '18 months federal prison + 3 years supervised release',
        'judge'               => 'U.S. District Judge Arthur J. Schwab',
    ]],
]);

echo PHP_EOL . 'Done.' . PHP_EOL;
PHPEOF

echo "Adding 12 more political prisoners from antifawatch.net scan (pages 500-599, batch 2)..."
php artisan tinker --execute="require '/tmp/add_pages_500_600_batch2.php';"
echo "Script complete."
