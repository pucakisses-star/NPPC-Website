#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_scan_pages_500_600_batch1.sh
# Adds 6 confirmed imprisoned political prisoners from antifawatch.net scan pages 500–599.
# Skipped: Larry Williams Jr. (home confinement only), Jason Charter (probation only),
#   Nicholas Tilsen (charges dismissed), and ~1,940 others without confirmed imprisonment.
set +e

cat > /tmp/add_pages_500_600.php << 'PHPEOF'
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
// antifawatch.net scan — pages 500–599
// -----------------------------------------------------------------------

// 1. Dakotah Horton
addPrisoner([
    'name'        => 'Dakotah Horton',
    'first_name'  => 'Dakotah',
    'last_name'   => 'Horton',
    'description' => <<<'DESC'
Dakotah Horton is a Portland, Oregon activist who was sentenced to 24 months in federal prison for assaulting a Deputy U.S. Marshal with a baseball bat during sustained anti-police/BLM protests at the Hatfield Federal Courthouse in Portland on July 27, 2020. Horton struck a kneeling marshal from behind with a wooden bat during a chaotic confrontation between federal officers — deployed by the Trump administration — and protesters. A loaded firearm was also found on him at arrest. He pleaded guilty in federal court and was sentenced in December 2021 before Judge Michael H. Simon.
DESC,
    'state'       => 'Oregon',
    'gender'      => 'Male',
    'ideologies'  => ['Antifascism'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Assaulting a federal officer with a deadly or dangerous weapon (18 U.S.C. § 111(b)) — struck a Deputy U.S. Marshal from behind with a wooden baseball bat at the Hatfield Federal Courthouse during BLM/anti-federal-deployment protests, Portland, Oregon, July 27, 2020; a loaded firearm was also recovered at arrest',
        'arrest_date'         => '2020-08-17',
        'incarceration_date'  => '2020-08-19',
        'release_date'        => '2022-04-30',
        'imprisoned_for_days' => 619,
        'convicted'           => 'Yes — guilty plea (December 2021)',
        'sentence'            => '24 months federal prison + 3 years supervised release',
        'judge'               => 'U.S. District Judge Michael H. Simon',
    ]],
]);

// 2. Isaiah Willoughby
addPrisoner([
    'name'        => 'Isaiah Willoughby',
    'first_name'  => 'Isaiah',
    'last_name'   => 'Willoughby',
    'description' => <<<'DESC'
Isaiah Willoughby is a Tacoma, Washington man who was sentenced to 24 months in federal prison for setting fire to the Seattle Police East Precinct during the CHOP/CHAZ (Capitol Hill Occupied Protest) autonomous zone. In the early morning of June 12, 2020, Willoughby poured gasoline and ignited a debris pile against the precinct wall and subsequently deleted social media posts to conceal his involvement. The East Precinct was the symbolic center of the CHOP zone, a multi-week occupation of several city blocks following the police killing of George Floyd. Willoughby pleaded guilty to federal arson and was sentenced in October 2021. After his release he ran as a candidate for Seattle City Council.
DESC,
    'state'       => 'Washington',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter', 'Antifascism'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Federal arson (18 U.S.C. § 844(i)) — poured gasoline and ignited a debris pile against the Seattle Police East Precinct during the CHOP/CHAZ occupation, June 12, 2020; deleted social media posts to conceal involvement',
        'arrest_date'         => '2020-07-14',
        'incarceration_date'  => '2020-07-14',
        'release_date'        => '2022-03-01',
        'imprisoned_for_days' => 595,
        'convicted'           => 'Yes — guilty plea (October 2021)',
        'sentence'            => '24 months federal prison + 3 years supervised release',
    ]],
]);

// 3. Desmond David-Pitts
addPrisoner([
    'name'        => 'Desmond David-Pitts',
    'first_name'  => 'Desmond',
    'last_name'   => 'David-Pitts',
    'description' => <<<'DESC'
Desmond David-Pitts is an Anchorage, Alaska man who traveled to Seattle specifically to participate in direct actions during the CHOP/CHAZ occupation and was sentenced to 20 months in federal prison for a second arson attempt on the Seattle Police East Precinct. On August 24, 2020, David-Pitts spent approximately 11 minutes piling trash outside a precinct side door and igniting it while accomplices attempted to block the door with crowbars. He was identified and arrested within an hour by his distinctive pink camouflage trousers, which were visible on surveillance footage. He pleaded guilty to conspiracy to commit arson in May 2021.
DESC,
    'state'       => 'Washington',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter', 'Antifascism'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Conspiracy to commit arson (18 U.S.C. § 844(n)) — piled trash outside a Seattle Police East Precinct side door and ignited it while accomplices blocked the door with crowbars, August 24, 2020; had traveled from Anchorage, Alaska to Seattle specifically to participate in the action; identified and arrested within an hour by distinctive pink camouflage trousers on surveillance footage',
        'arrest_date'         => '2020-08-24',
        'incarceration_date'  => '2020-08-24',
        'release_date'        => '2022-03-01',
        'imprisoned_for_days' => 554,
        'convicted'           => 'Yes — guilty plea (May 2021)',
        'sentence'            => '20 months federal prison + restitution to Seattle Police Department',
    ]],
]);

// 4. Edward Schinzing
addPrisoner([
    'name'        => 'Edward Schinzing',
    'first_name'  => 'Edward',
    'last_name'   => 'Schinzing',
    'description' => <<<'DESC'
Edward Schinzing is a Portland, Oregon man who was sentenced to 15 months in federal prison for setting fire inside the Multnomah County Justice Center during the first night of George Floyd/BLM protests in Portland on May 29, 2020. Schinzing entered the building during the protest, ignited papers in a filing cabinet, and spread the fire. He was identified from surveillance footage by a tattoo of his own last name across his back. He was indicted in August 2020 and pleaded guilty, receiving a significantly below-guidelines sentence reflecting a downward departure.
DESC,
    'state'       => 'Oregon',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter', 'Antifascism'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'Federal Bureau of Prisons',
        'charges'             => 'Federal arson (18 U.S.C. § 844(i)) — entered the Multnomah County Justice Center during George Floyd/BLM protests in Portland, Oregon, May 29, 2020, ignited papers in a filing cabinet, and spread the fire throughout; identified from surveillance footage by a tattoo of his own last name across his back',
        'arrest_date'         => '2020-07-28',
        'incarceration_date'  => '2020-07-28',
        'release_date'        => '2021-10-22',
        'imprisoned_for_days' => 451,
        'convicted'           => 'Yes — guilty plea (September 2020)',
        'sentence'            => '15 months federal prison + 3 years supervised release + restitution to Multnomah County',
    ]],
]);

// 5. Dwight Parker
addPrisoner([
    'name'        => 'Dwight Parker',
    'first_name'  => 'Dwight',
    'last_name'   => 'Parker',
    'description' => <<<'DESC'
Dwight Parker is a Troy, New York man convicted by jury and sentenced to prison for throwing Molotov cocktails at four Albany Police officers riding on horseback and their horses during George Floyd/BLM protests in Albany, New York on May 30, 2020. He also set a tractor-trailer on fire on South Pearl Street. Parker was originally sentenced to 20–24 years in state prison on terrorism-enhanced charges; however, in February 2025 the New York Appellate Division vacated the terrorism conviction, ruling that his statements about police brutality toward Black Americans did not establish the intent to influence government policy required by the terrorism statute, and reduced his sentence to 10 years determinate with all counts running concurrently. He remains in New York state prison.
DESC,
    'state'       => 'New York',
    'race'        => 'Black',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter'],
    'era'         => '2020s',
    'in_custody'  => true,
    'released'    => false,
    'cases' => [[
        'institution_name'    => 'New York State Department of Corrections',
        'charges'             => 'Attempted aggravated assault on police officers; attempted first-degree assault; third-degree arson; second-degree criminal mischief; first-degree riot — threw Molotov cocktails at four mounted Albany Police officers and their horses during George Floyd/BLM protests, Albany, New York, May 30, 2020; also set a tractor-trailer on fire; original terrorism enhancement on assault count subsequently vacated on appeal (February 2025, People v. Parker, NY App. Div. Third Dept.)',
        'arrest_date'         => '2020-07-16',
        'incarceration_date'  => '2020-07-16',
        'imprisoned_for_days' => 2177,
        'convicted'           => 'Yes — jury verdict (April 2022)',
        'sentence'            => 'Originally 20–24 years NY state prison + 5 years post-release supervision; reduced on appeal to 10 years determinate + 5 years post-release supervision, all counts concurrent (February 2025); terrorism conviction vacated',
        'judge'               => 'Judge Ackerman, Albany County Court',
    ]],
]);

// 6. Matthew Scott White
addPrisoner([
    'name'        => 'Matthew Scott White',
    'first_name'  => 'Matthew',
    'last_name'   => 'White',
    'description' => <<<'DESC'
Matthew Scott White is an enrolled member of a Minnesota Chippewa tribe and a lifelong St. Paul, Minnesota resident who was sentenced to six years in federal prison for burning down an Enterprise Rent-A-Car building on University Avenue in St. Paul during the George Floyd uprising in May 2020. White and a juvenile accomplice entered the building, started a fire in the back office, fed it with papers and flammable materials, and told bystanders the building was "going up" as he left; the building was completely destroyed. He was arrested on June 29, 2020, pleaded guilty in September 2020 before Judge Wilhelmina M. Wright, and was sentenced to 72 months in June 2021. He served his sentence at USP Terre Haute in Indiana and was released to a halfway house in June 2025.
DESC,
    'state'       => 'Minnesota',
    'race'        => 'Indigenous',
    'gender'      => 'Male',
    'ideologies'  => ['Black Lives Matter', 'Indigenous rights'],
    'era'         => '2020s',
    'in_custody'  => false,
    'released'    => true,
    'cases' => [[
        'institution_name'    => 'United States Penitentiary, Terre Haute',
        'institution_city'    => 'Terre Haute',
        'institution_state'   => 'Indiana',
        'charges'             => 'Federal arson (18 U.S.C. § 844(i)) — entered an Enterprise Rent-A-Car building on University Avenue in St. Paul, Minnesota, during George Floyd/BLM protests, started a fire in the back office, and fed it with papers and flammable materials; the building was completely destroyed; May 28, 2020',
        'arrest_date'         => '2020-06-29',
        'incarceration_date'  => '2020-06-29',
        'release_date'        => '2025-06-01',
        'imprisoned_for_days' => 1798,
        'convicted'           => 'Yes — guilty plea (September 2020)',
        'sentence'            => '72 months (6 years) federal prison + 3 years supervised release',
        'judge'               => 'U.S. District Judge Wilhelmina M. Wright',
        'prosecutor'          => 'Assistant U.S. Attorney Bradley M. Endicott',
    ]],
]);

echo PHP_EOL . 'Done.' . PHP_EOL;
PHPEOF

echo "Adding 6 political prisoners from antifawatch.net scan (pages 500–599)..."
php artisan tinker --execute="require '/tmp/add_pages_500_600.php';"
echo "Script complete."
