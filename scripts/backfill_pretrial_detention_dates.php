<?php

declare(strict_types=1);

/**
 * Backfill pretrial-detention case data for ~65 prisoners that the
 * agent web-research audit confirmed DID spend time in pretrial
 * custody (overnight or longer), but whose case rows in the DB did
 * not capture the dates / duration.
 *
 * For each prisoner: look up by name, find their first existing case
 * (or create one), set arrest_date / incarceration_date / release_date
 * / sentence text reflecting documented detention. Idempotent — only
 * sets fields if they're currently null/empty.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;

/**
 * Apply the supplied data to a prisoner's first case.
 * Only fills fields that are currently null/empty (so we don't
 * overwrite existing structured data).
 *
 * @param array $row keys: name, arrest, incarceration, release,
 *   sentence_note, institution (optional ['name', 'city', 'state'])
 */
function backfill(array $row): void {
    $p = Prisoner::where('name', $row['name'])->first();
    if (! $p) {
        echo "  SKIP (not found): {$row['name']}\n";
        return;
    }

    $case = $p->cases()->orderBy('created_at')->first();
    if (! $case) {
        if (empty($row['institution']['name'])) {
            echo "  SKIP (no case + no institution provided): {$p->name}\n";
            return;
        }
        $inst = Institution::firstOrCreate(
            ['name' => $row['institution']['name']],
            ['city' => $row['institution']['city'] ?? null, 'state' => $row['institution']['state'] ?? null]
        );
        $case = new PrisonerCase([
            'prisoner_id'    => $p->id,
            'institution_id' => $inst->id,
        ]);
    }

    $changed = false;
    if (! empty($row['arrest']) && empty($case->arrest_date)) {
        $case->arrest_date = $row['arrest'];
        $changed = true;
    }
    if (! empty($row['incarceration']) && empty($case->incarceration_date)) {
        $case->incarceration_date = $row['incarceration'];
        $changed = true;
    }
    if (! empty($row['release']) && empty($case->release_date)) {
        $case->release_date = $row['release'];
        $changed = true;
    }
    if (! empty($row['sentence_note']) && empty($case->sentence)) {
        $case->sentence = $row['sentence_note'];
        $changed = true;
    }

    if ($changed) {
        $case->save();
        echo "  Updated: {$p->name} (arrest={$case->arrest_date}, release={$case->release_date})\n";
    } else {
        echo "  Already populated: {$p->name}\n";
    }
}

$rows = [];

// ---- Greenpeace 28: Sept 12-14, 2019, held two nights in Houston-area jail ----
$gp = ['arrest' => '2019-09-12', 'incarceration' => '2019-09-12', 'release' => '2019-09-14',
    'sentence_note' => 'Held two nights in Houston-area custody before federal magistrate released on personal-recognizance bond Sept 14, 2019.',
    'institution' => ['name' => 'Houston-area federal custody — Greenpeace 28 Fred Hartman Bridge action', 'city' => 'Houston', 'state' => 'Texas']];
foreach ([
    'Brianna LaTrell Gibson', 'Cole Asher Taylor-Martin', 'Richard Alexander Sisney',
    'Ryan Harris', 'Sarah Francis Newman', 'Sydney Leanne Clifford', 'Shavone Torres',
    'Tyler N. McFarland', 'Tracye Redd', 'Tamura Russell Seiji', 'Zeph Fishlyn',
    'Heather Glasgow Doyle', 'Christian Deshawn Bufford', 'Chelcee Price',
    'Dakota Paige Schee', 'Heidi Nybroten', 'Julie Ann McElvain', 'Jonathan Butler',
    'Jayden Chayanne Allen', 'Kim Irene', 'Michael Anton Herbert', 'Piper Werle',
    'Mariah De Los Santos',
] as $n) $rows[] = array_merge(['name' => $n], $gp);

// ---- Asheville 11: May 1, 2010 arrest, held at Buncombe County Detention through May 3 arraignment, $50K+ bond ----
$ash = ['arrest' => '2010-05-01', 'incarceration' => '2010-05-01', 'release' => '2010-05-03',
    'sentence_note' => 'Held at Buncombe County Detention Facility from May 1, 2010 arrest until at least the May 3 arraignment with bond raised to $50,000+. Charges later dismissed without leave.',
    'institution' => ['name' => 'Buncombe County Detention Facility — Asheville 11 May Day', 'city' => 'Asheville', 'state' => 'North Carolina']];
foreach ([
    'Jordan Magde Ferrand-Sapsis', 'Randall Duncan Stezer', 'Wyatt Sherman Allgeier',
    'Cailin Elizabeth Major', 'Nicholas Ryan Entwistle',
] as $n) $rows[] = array_merge(['name' => $n], $ash);

// ---- RNC 8: Aug 30 - Sept 4, 2008 (~5 days) at Ramsey County Jail ----
$rnc = ['arrest' => '2008-08-30', 'incarceration' => '2008-08-30', 'release' => '2008-09-04',
    'sentence_note' => 'Arrested Aug 30, 2008 in pre-RNC police raids; held in Ramsey County jail (~5 days) until release on $10,000 bail Sept 4, 2008. All felony charges later dropped.',
    'institution' => ['name' => 'Ramsey County Jail — RNC 8', 'city' => 'St. Paul', 'state' => 'Minnesota']];
foreach ([
    'Monica Bicking', 'Luce Guillen-Givins', 'Eryn Trimmer',
    'Robert Czernik', 'Garrett Fitzgerald', 'Nathanael Secor', 'Max Specktor',
] as $n) $rows[] = array_merge(['name' => $n], $rnc);

// ---- SF8 (San Francisco 8) — Jan 2007 sweep, multimillion-dollar bail, held months ----
$sf8 = ['arrest' => '2007-01-23', 'incarceration' => '2007-01-23',
    'sentence_note' => 'Arrested January 2007 in SF8 sweep with bail set in the millions; held in jail until bail was reduced and posted, charges ultimately dismissed in 2009.',
    'institution' => ['name' => 'San Francisco County Jail — SF8 1971 cold case', 'city' => 'San Francisco', 'state' => 'California']];
foreach ([
    'Ray Boudreaux', 'Richard Brown', "Richard O'Neal",
] as $n) $rows[] = array_merge(['name' => $n], $sf8);

// ---- Diablo Canyon 1981: held days at "Hotel Diablo" Cuesta College gym ----
$diablo = ['arrest' => '1981-09-15', 'incarceration' => '1981-09-15', 'release' => '1981-09-25',
    'sentence_note' => 'Among ~1,900 arrested in the September 1981 Diablo Canyon blockade. Held at the Cuesta College gymnasium ("Hotel Diablo") for multiple days during the two-week blockade.',
    'institution' => ['name' => 'Cuesta College gymnasium ("Hotel Diablo") — Diablo Canyon blockade', 'city' => 'San Luis Obispo', 'state' => 'California']];
foreach ([
    'Jackson Browne', 'Hugh Nanton Romney Jr.',
] as $n) $rows[] = array_merge(['name' => $n], $diablo);

// ---- Bangor Trident 1983: 60-day sentence post-action ----
$rows[] = ['name' => 'Shelley Douglass', 'arrest' => '1983-02-16', 'incarceration' => '1983-02-16', 'release' => '1983-04-17',
    'sentence_note' => 'Walked Trident warhead rail line at Bangor naval base on Ash Wednesday, Feb 16, 1983; jailed for 60 days following arrest.',
    'institution' => ['name' => 'Federal/state custody — Bangor Trident rail-line action', 'city' => 'Silverdale', 'state' => 'Washington']];
$rows[] = ['name' => 'Karol Schulkin', 'arrest' => '1983-02-16', 'incarceration' => '1983-02-16',
    'sentence_note' => 'Arrested with Shelley Douglass and Mary Grondin walking Trident warhead rail line into Bangor naval base, Ash Wednesday Feb 16, 1983; jailed.',
    'institution' => ['name' => 'Federal/state custody — Bangor Trident rail-line action', 'city' => 'Silverdale', 'state' => 'Washington']];
$rows[] = ['name' => 'James W. Douglass', 'arrest' => '1979-10-30',
    'sentence_note' => 'Among October 1979 mass arrestees at Bangor naval base; received jail sentences "far longer in length than anything previously handed down" for Bangor-related civil disobedience.',
    'institution' => ['name' => 'Federal/state custody — Bangor Trident rail-line action', 'city' => 'Silverdale', 'state' => 'Washington']];

// ---- 1984 FSAM founders: held overnight DC jail Nov 21-22 1984 ----
$fsamFounders = ['arrest' => '1984-11-21', 'incarceration' => '1984-11-21', 'release' => '1984-11-22',
    'sentence_note' => 'One of the three FSAM founders arrested in the November 21, 1984 inaugural sit-in at the South African Embassy. Held overnight in DC jail before charges dismissed.',
    'institution' => ['name' => 'DC jail — Free South Africa Movement embassy sit-in', 'city' => 'Washington', 'state' => 'D.C.']];
foreach ([
    'Randall Robinson', 'Mary Frances Berry', 'Walter Edward Fauntroy',
] as $n) $rows[] = array_merge(['name' => $n], $fsamFounders);

// ---- Individual cases ----
$rows[] = ['name' => 'Ahmadullah Sais Niazi', 'arrest' => '2009-02-20', 'incarceration' => '2009-02-20',
    'sentence_note' => 'Arrested at home February 2009; held on $500,000 bail before release to electronic monitoring/home detention. FBI Operation Flex prosecution; indictment dropped after informant Craig Monteilh\'s testimony surfaced.',
    'institution' => ['name' => 'Santa Ana Federal Detention Center — FBI Operation Flex', 'city' => 'Santa Ana', 'state' => 'California']];

$rows[] = ['name' => 'Carolyn Rodriguez', 'arrest' => '2024-06-23', 'incarceration' => '2024-06-23', 'release' => '2024-06-24',
    'sentence_note' => 'Arrested June 23, 2024 in Fort Worth and booked into jail after hospital treatment, held overnight until release the next day.',
    'institution' => ['name' => 'Tarrant County Jail — Fort Worth copwatch arrest', 'city' => 'Fort Worth', 'state' => 'Texas']];

$rows[] = ['name' => 'Omali Yeshitela', 'arrest' => '2023-04-18', 'incarceration' => '2023-04-18',
    'sentence_note' => 'Surrendered to federal court in Tampa after April 2023 indictment; handcuffed, placed in leg irons, and held in cells before release on $25,000 bail conditions.',
    'institution' => ['name' => 'M.D. Fla. federal court holding — Uhuru Movement indictment', 'city' => 'Tampa', 'state' => 'Florida']];

$rows[] = ['name' => 'Holden Dometrius', 'arrest' => '2019-04-25', 'incarceration' => '2019-04-25',
    'sentence_note' => 'Held at Southern Regional Jail in Beaver, WV on $8,000 cash-only bond following April 25, 2019 arrest at Mountain Valley Pipeline worksite. Case dismissed November 2020.',
    'institution' => ['name' => 'Southern Regional Jail (Beaver, WV) — MVP pipeline action', 'city' => 'Beaver', 'state' => 'West Virginia']];

$rows[] = ['name' => 'Gina Eleyna Wertz', 'arrest' => '2009-04-24', 'incarceration' => '2009-04-24',
    'sentence_note' => 'Arrested at Gibson County courthouse April 24, 2009; held on $10,000 bond pending posting. Felony racketeering charge dismissed; pleaded to misdemeanor with two years unsupervised probation.',
    'institution' => ['name' => 'Pike County Jail — I-69 highway protest', 'city' => 'Petersburg', 'state' => 'Indiana']];

$rows[] = ['name' => 'Hugh F. Farrell', 'arrest' => '2009-04-24', 'incarceration' => '2009-04-24', 'release' => '2009-04-28',
    'sentence_note' => 'Held 4 days at Pike County jail in Indiana on $20,000 cash bond after April 24, 2009 arrest. Felony racketeering charge dismissed; pleaded to misdemeanor with two years unsupervised probation.',
    'institution' => ['name' => 'Pike County Jail — I-69 highway protest', 'city' => 'Petersburg', 'state' => 'Indiana']];

$rows[] = ['name' => 'Ernest McQueen', 'arrest' => '2006-01-15', 'incarceration' => '2006-01-15', 'release' => '2006-01-31',
    'sentence_note' => 'Arrested January 2006 in Fort Worth, Texas after fingerprint match revealed his Vietnam-era desertion identity; shipped to Marine brig in California pending discharge proceedings. Discharged without disciplinary action January 2006.',
    'institution' => ['name' => 'U.S. Marine Corps brig — Vietnam-era desertion 1969', 'city' => null, 'state' => 'California']];

$rows[] = ['name' => 'Brandon Aaron Miller-Castillo', 'arrest' => '2017-09-21',
    'sentence_note' => 'Federally indicted in 2017 alongside co-defendants who served federal prison time for the October 27, 2016 NoDAPL Backwater Bridge fire at Standing Rock.',
    'institution' => ['name' => 'D.N.D. federal custody — NoDAPL Backwater Bridge', 'city' => 'Bismarck', 'state' => 'North Dakota']];

$rows[] = ['name' => 'Gloria Merriweather', 'arrest' => '2018-08',
    'sentence_note' => 'Turned themself in at Mecklenburg County Jail on felony inciting-a-riot/assault charges from the 2016 Charlotte protests; jail booking required posting bond. Charges dropped two weeks before trial.',
    'institution' => ['name' => 'Mecklenburg County Jail — 2016 Charlotte protests (Keith Lamont Scott)', 'city' => 'Charlotte', 'state' => 'North Carolina']];

$rows[] = ['name' => 'Davis Alan Beeman', 'arrest' => '2021-01-21', 'incarceration' => '2021-01-21',
    'sentence_note' => 'Booked into Multnomah County Jail January 21, 2021 on Riot and Disorderly Conduct II charges; no formal complaint filed.',
    'institution' => ['name' => 'Multnomah County Detention Center — January 2021 Portland protests', 'city' => 'Portland', 'state' => 'Oregon']];

$rows[] = ['name' => 'Leo A. Randle', 'arrest' => '2024-05-01', 'incarceration' => '2024-05-01',
    'sentence_note' => 'Booked into Dane County Jail on May 1, 2024 on felony battery-to-officer charges from the UW-Madison Palestine encampment. Charges dismissed.',
    'institution' => ['name' => 'Dane County Jail — UW-Madison Palestine encampment', 'city' => 'Madison', 'state' => 'Wisconsin']];

$rows[] = ['name' => 'Trevor H. Carter', 'arrest' => '2024-05-01', 'incarceration' => '2024-05-01',
    'sentence_note' => 'Booked into Dane County Jail on May 1, 2024 on battery-to-officer charges from the UW-Madison Palestine encampment. Charges dropped.',
    'institution' => ['name' => 'Dane County Jail — UW-Madison Palestine encampment', 'city' => 'Madison', 'state' => 'Wisconsin']];

$rows[] = ['name' => 'Haley Rainwater', 'arrest' => '2020-10-09', 'incarceration' => '2020-10-09', 'release' => '2020-10-09',
    'sentence_note' => 'Arrested ~3am Oct 9, 2020 in Collierville TN; held in jail less than 24 hours with $10,000 bond before release the morning of Oct 9. Charges dropped prior to trial.',
    'institution' => ['name' => 'Collierville Police / Shelby County Jail — Confederate monument vandalism', 'city' => 'Collierville', 'state' => 'Tennessee']];

$rows[] = ['name' => 'Cortez Aaron Rice', 'arrest' => '2021-11-24', 'incarceration' => '2021-11-24',
    'sentence_note' => 'Booked into Waukesha County jail with $50,000 cash bail and waived extradition for transfer to Hennepin County. Charges dismissed without prejudice.',
    'institution' => ['name' => 'Waukesha County / Hennepin County Jail — Daunte Wright case', 'city' => 'Waukesha', 'state' => 'Wisconsin']];

$rows[] = ['name' => 'Hicham Talal', 'arrest' => '2024-02-26', 'incarceration' => '2024-02-26',
    'sentence_note' => 'Turned himself in to West Hartford police Feb 26, 2024; released only after a $50,000 bond was posted, indicating at minimum he was booked into custody pending bond. Charges dropped July 29, 2024.',
    'institution' => ['name' => 'West Hartford Police booking — Webster Bank Israel-investments protest', 'city' => 'West Hartford', 'state' => 'Connecticut']];

$rows[] = ['name' => 'Emily Keppler', 'arrest' => '2021-04-19', 'incarceration' => '2021-04-19',
    'sentence_note' => 'Booked into Multnomah County Jail at 11:35 PM April 19, 2021 with no bail set, almost certainly resulting in an overnight stay before any release.',
    'institution' => ['name' => 'Multnomah County Detention Center — April 2021 Portland Bank of America', 'city' => 'Portland', 'state' => 'Oregon']];

// Pahlawan dhow case (Iran-to-Houthi weapons)
$dhow = ['arrest' => '2024-01-11', 'incarceration' => '2024-01-11',
    'sentence_note' => 'Taken into U.S. Navy custody at sea January 11, 2024 during the Yunus dhow interdiction off Somalia; transferred to Norfolk EDVA, faced detention hearings late February 2024 with prosecutors seeking detention without bond. Charges later dismissed.',
    'institution' => ['name' => 'EDVA federal custody — Yunus dhow Iran-to-Houthi weapons interdiction', 'city' => 'Norfolk', 'state' => 'Virginia']];
foreach (['Ghufran Ullah', 'Izhar Muhammad', 'Mohammad Mazhar'] as $n) $rows[] = array_merge(['name' => $n], $dhow);

$rows[] = ['name' => 'Galen Sol Shireman-Grabowski', 'arrest' => '2019-10-01', 'incarceration' => '2019-10-01',
    'sentence_note' => 'Held without bond in Western Virginia Regional Jail after October 2019 helicopter lockdown protest at Mountain Valley Pipeline crews in Ellison VA.',
    'institution' => ['name' => 'Western Virginia Regional Jail — MVP pipeline helicopter action', 'city' => 'Salem', 'state' => 'Virginia']];

$rows[] = ['name' => 'George A. Vassilatos', 'arrest' => '2024-04-26', 'incarceration' => '2024-04-26', 'release' => '2024-04-27',
    'sentence_note' => 'Arrested Friday April 26, 2024 at UIUC encampment; held overnight until Saturday release. Sentenced included 2 days credit for time served.',
    'institution' => ['name' => 'Champaign County Jail — UIUC Gaza encampment', 'city' => 'Urbana', 'state' => 'Illinois']];

$rows[] = ['name' => 'Terrence Clyne', 'arrest' => '2024-01-03', 'incarceration' => '2024-01-03', 'release' => '2024-01-04',
    'sentence_note' => 'Arrested Wednesday morning Jan 3, 2024 and released only after court appearance Thursday morning, indicating overnight detention. Pleaded guilty to misdemeanor battery; felony hate crime dropped.',
    'institution' => ['name' => 'Will County Jail — Orland Park hate-crime arrest', 'city' => 'Joliet', 'state' => 'Illinois']];

$rows[] = ['name' => 'Chase A. Iron Eyes', 'arrest' => '2017-02-01', 'incarceration' => '2017-02-01',
    'sentence_note' => 'Held in Morton County Jail after February 1, 2017 NoDAPL Last Child\'s Camp arrest; issued statement from inside jail. Plea deal reduced to misdemeanor; 360-day deferred imposition.',
    'institution' => ['name' => 'Morton County Jail — NoDAPL Last Child\'s Camp', 'city' => 'Mandan', 'state' => 'North Dakota']];

// Apply all backfills
echo "Backfilling " . count($rows) . " prisoner case records...\n\n";
foreach ($rows as $row) {
    backfill($row);
}

echo "\nDone.\n";
