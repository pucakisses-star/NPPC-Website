#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_scan_pages_500_600_existing_cases.sh
# Attaches PrisonerCase records to 17 prisoners from the antifawatch.net 500-599
# scan (batches 1-3) who already existed in the database (from a prior import)
# but were created without cases, so the batch scripts skipped them entirely.
set +e

cat > /tmp/add_500_600_existing_cases.php << 'PHPEOF'
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

// -----------------------------------------------------------------------
// batch1 (6)
// -----------------------------------------------------------------------

addCase('Dakotah Horton', [
    'inst_name'           => 'Federal Bureau of Prisons',
    'charges'             => 'Assaulting a federal officer with a deadly or dangerous weapon (18 U.S.C. § 111(b)) — struck a Deputy U.S. Marshal from behind with a wooden baseball bat at the Hatfield Federal Courthouse during BLM/anti-federal-deployment protests, Portland, Oregon, July 27, 2020; a loaded firearm was also recovered at arrest',
    'arrest_date'         => '2020-08-17',
    'incarceration_date'  => '2020-08-19',
    'release_date'        => '2022-04-30',
    'imprisoned_for_days' => 619,
    'convicted'           => 'Yes — guilty plea (December 2021)',
    'sentence'            => '24 months federal prison + 3 years supervised release',
    'judge'               => 'U.S. District Judge Michael H. Simon',
]);

addCase('Isaiah Willoughby', [
    'inst_name'           => 'Federal Bureau of Prisons',
    'charges'             => 'Federal arson (18 U.S.C. § 844(i)) — poured gasoline and ignited a debris pile against the Seattle Police East Precinct during the CHOP/CHAZ occupation, June 12, 2020; deleted social media posts to conceal involvement',
    'arrest_date'         => '2020-07-14',
    'incarceration_date'  => '2020-07-14',
    'release_date'        => '2022-03-01',
    'imprisoned_for_days' => 595,
    'convicted'           => 'Yes — guilty plea (October 2021)',
    'sentence'            => '24 months federal prison + 3 years supervised release',
]);

addCase('Desmond David-Pitts', [
    'inst_name'           => 'Federal Bureau of Prisons',
    'charges'             => 'Conspiracy to commit arson (18 U.S.C. § 844(n)) — piled trash outside a Seattle Police East Precinct side door and ignited it while accomplices blocked the door with crowbars, August 24, 2020; had traveled from Anchorage, Alaska to Seattle specifically to participate in the action; identified and arrested within an hour by distinctive pink camouflage trousers on surveillance footage',
    'arrest_date'         => '2020-08-24',
    'incarceration_date'  => '2020-08-24',
    'release_date'        => '2022-03-01',
    'imprisoned_for_days' => 554,
    'convicted'           => 'Yes — guilty plea (May 2021)',
    'sentence'            => '20 months federal prison + restitution to Seattle Police Department',
]);

addCase('Edward Schinzing', [
    'inst_name'           => 'Federal Bureau of Prisons',
    'charges'             => 'Federal arson (18 U.S.C. § 844(i)) — entered the Multnomah County Justice Center during George Floyd/BLM protests in Portland, Oregon, May 29, 2020, ignited papers in a filing cabinet, and spread the fire throughout; identified from surveillance footage by a tattoo of his own last name across his back',
    'arrest_date'         => '2020-07-28',
    'incarceration_date'  => '2020-07-28',
    'release_date'        => '2021-10-22',
    'imprisoned_for_days' => 451,
    'convicted'           => 'Yes — guilty plea (September 2020)',
    'sentence'            => '15 months federal prison + 3 years supervised release + restitution to Multnomah County',
]);

addCase('Dwight Parker', [
    'inst_name'           => 'New York State Department of Corrections',
    'charges'             => 'Attempted aggravated assault on police officers; attempted first-degree assault; third-degree arson; second-degree criminal mischief; first-degree riot — threw Molotov cocktails at four mounted Albany Police officers and their horses during George Floyd/BLM protests, Albany, New York, May 30, 2020; also set a tractor-trailer on fire; original terrorism enhancement on assault count subsequently vacated on appeal (February 2025, People v. Parker, NY App. Div. Third Dept.)',
    'arrest_date'         => '2020-07-16',
    'incarceration_date'  => '2020-07-16',
    'imprisoned_for_days' => 2177,
    'convicted'           => 'Yes — jury verdict (April 2022)',
    'sentence'            => 'Originally 20–24 years NY state prison + 5 years post-release supervision; reduced on appeal to 10 years determinate + 5 years post-release supervision, all counts concurrent (February 2025); terrorism conviction vacated',
    'judge'               => 'Judge Ackerman, Albany County Court',
]);

addCase('Matthew Scott White', [
    'inst_name'           => 'United States Penitentiary, Terre Haute',
    'inst_city'           => 'Terre Haute',
    'inst_state'          => 'Indiana',
    'charges'             => 'Federal arson (18 U.S.C. § 844(i)) — entered an Enterprise Rent-A-Car building on University Avenue in St. Paul, Minnesota, during George Floyd/BLM protests, started a fire in the back office, and fed it with papers and flammable materials; the building was completely destroyed; May 28, 2020',
    'arrest_date'         => '2020-06-29',
    'incarceration_date'  => '2020-06-29',
    'release_date'        => '2025-06-01',
    'imprisoned_for_days' => 1798,
    'convicted'           => 'Yes — guilty plea (September 2020)',
    'sentence'            => '72 months (6 years) federal prison + 3 years supervised release',
    'judge'               => 'U.S. District Judge Wilhelmina M. Wright',
    'prosecutor'          => 'Assistant U.S. Attorney Bradley M. Endicott',
]);

// -----------------------------------------------------------------------
// batch2 (7)
// -----------------------------------------------------------------------

addCase('Sam Resto', [
    'inst_name'           => 'Federal Bureau of Prisons',
    'charges'             => 'Federal arson (18 U.S.C. § 844(f)(1)) — smashed the window of a marked NYPD van, poured gasoline inside, and set it on fire on the Upper West Side of Manhattan during BLM protests, July 29, 2020; identified via a backpack recovered in Central Park containing matching clothing, a Guy Fawkes mask, a gasoline can, a hammer, and lighters',
    'arrest_date'         => '2020-08-13',
    'incarceration_date'  => '2020-08-13',
    'release_date'        => '2022-11-04',
    'imprisoned_for_days' => 813,
    'convicted'           => 'Yes — guilty plea',
    'sentence'            => 'Time served (approximately 27 months pretrial detention) + 3 years supervised release + $14,065.35 restitution',
    'judge'               => 'U.S. District Judge Nicholas G. Garaufis',
]);

addCase('Elaine Carberry', [
    'inst_name'           => 'Federal Bureau of Prisons',
    'charges'             => 'Conspiracy to commit arson (18 U.S.C. § 371) — ignited an object and threw it into a marked NYPD Homeless Outreach Unit van, then returned to reignite it with a flammable substance, Greenwich Village, Manhattan, July 15, 2020',
    'arrest_date'         => '2020-08-13',
    'incarceration_date'  => '2022-06-20',
    'release_date'        => '2022-12-20',
    'imprisoned_for_days' => 183,
    'convicted'           => 'Yes — guilty plea (September 22, 2021)',
    'sentence'            => '6 months federal prison + 6 months home confinement + 3 years supervised release + 400 hours community service + $14,000 fine + $72,308.66 restitution (joint with co-defendant)',
    'judge'               => 'U.S. District Judge Lewis J. Liman',
]);

addCase('Corey Smith', [
    'inst_name'           => 'Federal Bureau of Prisons',
    'charges'             => 'Conspiracy to commit arson (18 U.S.C. § 371) — burned a marked NYPD Homeless Outreach Unit van in Greenwich Village, Manhattan, alongside co-defendant Elaine Carberry, July 15, 2020',
    'arrest_date'         => '2020-08-13',
    'incarceration_date'  => '2022-06-20',
    'release_date'        => '2022-12-20',
    'imprisoned_for_days' => 183,
    'convicted'           => 'Yes — guilty plea (September 22, 2021)',
    'sentence'            => '6 months federal prison + 6 months home confinement + 3 years supervised release + 400 hours community service + $72,308.66 restitution (joint with co-defendant)',
    'judge'               => 'U.S. District Judge Lewis J. Liman',
]);

addCase('Christopher Tindal', [
    'inst_name'           => 'United States Penitentiary, Canaan',
    'inst_city'           => 'Waymart',
    'inst_state'          => 'Pennsylvania',
    'charges'             => 'Rioting and failure to appear — set fire to a Rochester Police Department vehicle with an aerosol can and open flame outside the Public Safety Building during May 30, 2020 protests; after pleading guilty, removed his GPS ankle monitor in March 2022 and fled rather than appear for sentencing, resulting in a second federal conviction',
    'arrest_date'         => '2020-07-31',
    'incarceration_date'  => '2023-08-24',
    'imprisoned_for_days' => 1044,
    'convicted'           => 'Yes — guilty plea to rioting (April 27, 2021) and guilty plea to failure to appear (May 3, 2023)',
    'sentence'            => '5 years federal prison',
    'judge'               => 'U.S. District Judge Charles J. Siragusa',
]);

addCase('Tyvarh Nicholson', [
    'inst_name'           => 'Federal Bureau of Prisons',
    'charges'             => 'Possession of an unregistered destructive device (National Firearms Act) — threw Molotov cocktails at Erie Police officers during BLM protests in downtown Erie, Pennsylvania, May 30, 2020',
    'arrest_date'         => '2020-06-01',
    'incarceration_date'  => '2021-08-04',
    'release_date'        => '2024-06-04',
    'imprisoned_for_days' => 1035,
    'convicted'           => 'Yes — guilty plea (August 4, 2021)',
    'sentence'            => '40 months federal prison + 3 years supervised release',
    'judge'               => 'Senior U.S. District Judge David S. Cercone',
]);

addCase('Devin Montgomery', [
    'inst_name'           => 'Federal Bureau of Prisons',
    'charges'             => 'Conspiracy against the United States and bank burglary — used lit objects to ignite an unmarked Pittsburgh police vehicle near PPG Paints Arena, then smashed windows and broke into a Dollar Bank branch, May 30, 2020',
    'incarceration_date'  => '2023-03-28',
    'imprisoned_for_days' => 1193,
    'convicted'           => 'Yes — guilty plea',
    'sentence'            => '4 years (48 months) federal prison + 3 years supervised release + $25,635.50 restitution',
    'judge'               => 'Chief U.S. District Judge Mark R. Hornak',
]);

addCase('Lateesha Richards', [
    'inst_name'           => 'Federal Bureau of Prisons',
    'charges'             => 'Federal arson — identified on video by a distinctive neck tattoo standing in front of a burning Salt Lake City police vehicle during BLM protests, May 30, 2020',
    'convicted'           => 'Yes',
    'sentence'            => '20 months federal prison (sentenced August 3, 2021)',
]);

// -----------------------------------------------------------------------
// batch3 (4)
// -----------------------------------------------------------------------

addCase('Mackenzie Drechsler', [
    'inst_name'           => 'Federal Bureau of Prisons',
    'charges'             => 'Rioting (pled down from arson, 18 U.S.C. § 844(f)(1)) — set fire to a parked New York State Attorney General\'s Office vehicle using cardboard, and helped ignite a City of Rochester Family Crisis Intervention Team (FACIT) vehicle with burning fabric, both during May 30, 2020 BLM protests in downtown Rochester; also participated in breaking glass during looting',
    'arrest_date'         => '2020-07-01',
    'incarceration_date'  => '2021-06-08',
    'release_date'        => '2022-06-09',
    'imprisoned_for_days' => 366,
    'convicted'           => 'Yes — guilty plea (June 8, 2021)',
    'sentence'            => '1 year and 1 day federal prison + 2 years supervised release + $8,674 restitution ($3,775 to City of Rochester, $4,899 to NY State Attorney General\'s Office)',
    'judge'               => 'U.S. District Judge David G. Larimer',
]);

addCase('Shakell Sanks', [
    'inst_name'           => 'Federal Bureau of Prisons',
    'charges'             => 'Rioting (pled down from arson) — assisted others in stuffing burning fabric into the gas tank of a parked City of Rochester Family Crisis Intervention Team (FACIT) vehicle, completely destroying it, during May 30, 2020 BLM protests in downtown Rochester',
    'incarceration_date'  => '2021-09-25',
    'release_date'        => '2022-02-25',
    'imprisoned_for_days' => 153,
    'convicted'           => 'Yes — guilty plea (June 8, 2021)',
    'sentence'            => '5 months federal prison',
    'judge'               => 'U.S. District Judge David G. Larimer',
]);

addCase('Javon Hardy', [
    'inst_name'           => 'Federal Bureau of Prisons',
    'charges'             => 'Rioting (pled down from arson) — reached through a broken window of a mobile construction trailer and ignited flammable material inside using a container of accelerant during May 30, 2020 BLM protests in downtown Rochester',
    'release_date'        => '2022-11-02',
    'convicted'           => 'Yes — guilty plea (December 16, 2020)',
    'sentence'            => '12 months federal prison + $14,504 restitution',
    'judge'               => 'U.S. District Judge Charles J. Siragusa',
]);

addCase('Marquis Frasier', [
    'inst_name'           => 'Federal Correctional Institution, Williamsburg',
    'inst_city'           => 'Salters',
    'inst_state'          => 'South Carolina',
    'charges'             => 'Rioting (pled down from arson) — threw a Molotov cocktail into a mobile construction trailer at Exchange Boulevard/Court Street during May 30, 2020 BLM protests in downtown Rochester, caught on Facebook Live video',
    'release_date'        => '2024-01-25',
    'convicted'           => 'Yes — guilty plea (July 8, 2022)',
    'sentence'            => 'Approximately 13 months federal prison (exact term not published in DOJ records)',
    'judge'               => 'U.S. District Judge Charles J. Siragusa',
]);

echo PHP_EOL . 'Done.' . PHP_EOL;
PHPEOF

echo "Attaching cases to 17 existing prisoners from the 500-599 scan..."
php artisan tinker --execute="require '/tmp/add_500_600_existing_cases.php';"
echo "Script complete."
