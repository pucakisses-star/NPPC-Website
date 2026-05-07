<?php

declare(strict_types=1);

/**
 * Bulk-add Reagan-era political prisoners across four major cases:
 *
 *   1. MOVE 9 (Philadelphia, 1978-) plus Ramona Africa (1985 MOVE bombing)
 *   2. FALN — Puerto Rican independentistas, Evanston 1980 + Chicago 1983
 *      arrests, plus Oscar Lopez Rivera (1981) and Carlos Alberto Torres
 *      and Haydée Beltrán Torres
 *   3. Macheteros / Ejército Popular Boricua — 1985 Wells Fargo robbery
 *      prosecution (U.S. v. Gerena, D. Conn.) plus the Plan Tortuga
 *      late captures (Avelino & Norberto González Claudio, Luis Colón
 *      Osorio)
 *   4. Resistance Conspiracy Case (1988 indictment) + Ohio 7 / United
 *      Freedom Front (1984-1989 trials)
 *   5. Free South Africa Movement (FSAM, Nov 1984 - 1986 — high-profile
 *      arrestees only)
 *
 * Bios researched from:
 * - Wikipedia individual articles & Wikipedia: FALN, Macheteros,
 *   1983 Wells Fargo robbery, MOVE bombing, MOVE 9, Resistance
 *   Conspiracy Case, United Freedom Front, Free South Africa Movement
 * - U.S. v. Gerena, U.S. v. Rosado (FALN), 883 F.2d 662 etc.
 * - ProLibertad / National Boricua Human Rights Network
 * - Obama 2017 commutation coverage; Clinton 1999 commutation coverage
 * - Free The MOVE 9 campaign; "Let the Fire Burn" (2013) documentary
 * - "An American Radical" (Susan Rosenberg memoir, 2011); "Hauling Up
 *   the Morning" (Blunk + Levasseur, 1990)
 * - Washington Post / NYT 1984-1986 archive for FSAM
 *
 * Idempotent — uses firstOrCreate by name and only adds case rows when
 * none with the same arrest_date already exists.
 *
 * Run on production:
 *   cd /var/www/NPPC-Website && php scripts/add_reagan_era_prisoners.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;

function upsertPrisoner(array $p): array
{
    $existing = Prisoner::where('name', $p['name'])->first();
    if ($existing) {
        return [$existing, false];
    }
    $prisoner = Prisoner::create(array_filter([
        'name'         => $p['name'],
        'first_name'   => $p['first_name']  ?? null,
        'middle_name'  => $p['middle_name'] ?? null,
        'last_name'    => $p['last_name']   ?? null,
        'aka'          => $p['aka']         ?? null,
        'gender'       => $p['gender']      ?? null,
        'race'         => $p['race']        ?? null,
        'state'        => $p['state']       ?? null,
        'birthdate'    => $p['birthdate']   ?? null,
        'death_date'   => $p['death_date']  ?? null,
        'description'  => $p['description'] ?? null,
        'era'          => $p['era']         ?? '1980s',
        'ideologies'   => $p['ideologies']  ?? null,
        'affiliation'  => $p['affiliation'] ?? null,
        'in_custody'   => $p['in_custody']  ?? false,
        'released'     => $p['released']    ?? true,
    ], fn ($v) => $v !== null));
    return [$prisoner, true];
}

function attachCase(Prisoner $prisoner, array $c): bool
{
    if (! empty($c['arrest_date'])) {
        $exists = $prisoner->cases()->where('arrest_date', $c['arrest_date'])->first();
        if ($exists) {
            return false;
        }
    }
    $institution = Institution::firstOrCreate(
        ['name'  => $c['institution']['name']],
        ['city'  => $c['institution']['city']  ?? null,
         'state' => $c['institution']['state'] ?? null],
    );
    PrisonerCase::create([
        'prisoner_id'           => $prisoner->id,
        'institution_id'        => $institution->id,
        'charges'               => $c['charges'] ?? null,
        'arrest_date'           => $c['arrest_date'] ?? null,
        'incarceration_date'    => $c['incarceration_date'] ?? null,
        'release_date'          => $c['release_date'] ?? null,
        'death_in_custody_date' => $c['death_in_custody_date'] ?? null,
        'sentenced_date'        => $c['sentenced_date'] ?? null,
        'convicted'             => $c['convicted'] ?? null,
        'sentence'              => $c['sentence'] ?? null,
        'prosecutor'            => $c['prosecutor'] ?? null,
        'judge'                 => $c['judge'] ?? null,
    ]);
    return true;
}

$totals = ['created' => 0, 'existed' => 0, 'cases' => 0];

// =================== Cluster 1: MOVE 9 + Ramona Africa ===================

$moveCourt = ['name' => 'Philadelphia Court of Common Pleas / SCI Pennsylvania state prisons', 'city' => 'Philadelphia', 'state' => 'Pennsylvania'];
$move9Charges = 'Convicted Aug 8, 1980 of third-degree murder, conspiracy, attempted murder and aggravated assault for the death of Officer James Ramp during the Aug 8, 1978 Powelton Village police siege of the MOVE house. Eight of nine defendants were tried together; sentenced 30 to 100 years.';

$movePeople = [
    [
        'name' => 'Debbie Sims Africa', 'first_name' => 'Debbie', 'last_name' => 'Sims Africa',
        'aka' => 'Debbie Sims', 'gender' => 'Female', 'race' => 'Black', 'state' => 'Pennsylvania',
        'description' => 'Debbie Sims Africa was a member of MOVE living at the Powelton Village house at the time of the August 8, 1978 police siege. She was pregnant during the siege and gave birth to her son Michael Davis Africa Jr. in a Philadelphia jail cell weeks after her arrest; her baby was taken from her after three days. Convicted of third-degree murder in the death of Officer James Ramp and sentenced to 30-100 years, she was the first of the MOVE 9 paroled, released in June 2018 after nearly 40 years.',
        'affiliation' => ['MOVE'],
        'cases' => [['arrest_date' => '1978-08-08', 'sentenced_date' => '1981-04-01', 'sentence' => '30 to 100 years', 'release_date' => '2018-06-16', 'convicted' => 'Yes', 'judge' => 'Hon. Edwin S. Malmed', 'charges' => $move9Charges, 'institution' => $moveCourt]],
    ],
    [
        'name' => 'Janet Holloway Africa', 'first_name' => 'Janet', 'last_name' => 'Holloway Africa',
        'aka' => 'Janet Holloway', 'gender' => 'Female', 'race' => 'Black', 'state' => 'Pennsylvania',
        'description' => 'Janet Holloway Africa was a longtime MOVE member arrested at Powelton Village following the August 8, 1978 confrontation. Convicted as one of the MOVE 9 of third-degree murder and sentenced to 30-100 years, she was repeatedly denied parole despite a clean prison record and was finally released in May 2019 after more than 40 years in custody.',
        'affiliation' => ['MOVE'],
        'cases' => [['arrest_date' => '1978-08-08', 'sentenced_date' => '1981-04-01', 'sentence' => '30 to 100 years', 'release_date' => '2019-05-25', 'convicted' => 'Yes', 'judge' => 'Hon. Edwin S. Malmed', 'charges' => $move9Charges, 'institution' => $moveCourt]],
    ],
    [
        'name' => 'Janine Phillips Africa', 'first_name' => 'Janine', 'last_name' => 'Phillips Africa',
        'aka' => 'Janine Phillips', 'gender' => 'Female', 'race' => 'Black', 'state' => 'Pennsylvania',
        'description' => 'Janine Phillips Africa was a MOVE member and the wife of Phil (William Phillips) Africa. Her infant son Life Africa was reportedly killed during a police confrontation at the MOVE house in 1976, a death MOVE attributed to police violence and which the city denied. Convicted as one of the MOVE 9 and sentenced to 30-100 years, she was paroled in May 2019 after more than 40 years incarcerated.',
        'affiliation' => ['MOVE'],
        'cases' => [['arrest_date' => '1978-08-08', 'sentenced_date' => '1981-04-01', 'sentence' => '30 to 100 years', 'release_date' => '2019-05-25', 'convicted' => 'Yes', 'judge' => 'Hon. Edwin S. Malmed', 'charges' => $move9Charges, 'institution' => $moveCourt]],
    ],
    [
        'name' => 'Michael Davis Africa Sr.', 'first_name' => 'Michael', 'middle_name' => 'Davis', 'last_name' => 'Africa Sr.',
        'aka' => 'Mike Africa Sr.', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Pennsylvania',
        'description' => 'Michael Davis Africa Sr. was a MOVE member and husband of Debbie Sims Africa, arrested at Powelton Village on August 8, 1978 and convicted with the other MOVE 9 of third-degree murder. Sentenced to 30-100 years, he served roughly 40 years before being paroled in October 2018, four months after his wife. Their son, Mike Africa Jr., born to Debbie in jail in 1978, led much of the public campaign to free his parents.',
        'affiliation' => ['MOVE'],
        'cases' => [['arrest_date' => '1978-08-08', 'sentenced_date' => '1981-04-01', 'sentence' => '30 to 100 years', 'release_date' => '2018-10-23', 'convicted' => 'Yes', 'judge' => 'Hon. Edwin S. Malmed', 'charges' => $move9Charges, 'institution' => $moveCourt]],
    ],
    [
        'name' => 'Edward Goodman Africa', 'first_name' => 'Edward', 'last_name' => 'Goodman Africa',
        'aka' => 'Eddie Goodman Africa', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Pennsylvania',
        'description' => 'Edward "Eddie" Goodman Africa was a member of MOVE arrested in the August 8, 1978 Powelton Village siege and convicted with the other MOVE 9. Sentenced to 30-100 years, he was repeatedly denied parole despite a clean institutional record and was finally paroled in 2020 after more than 41 years.',
        'affiliation' => ['MOVE'],
        'cases' => [['arrest_date' => '1978-08-08', 'sentenced_date' => '1981-04-01', 'sentence' => '30 to 100 years', 'release_date' => '2020-02-01', 'convicted' => 'Yes', 'judge' => 'Hon. Edwin S. Malmed', 'charges' => $move9Charges, 'institution' => $moveCourt]],
    ],
    [
        'name' => 'Charles Sims Africa', 'first_name' => 'Charles', 'last_name' => 'Sims Africa',
        'aka' => 'Chuck Sims Africa', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Pennsylvania',
        'death_date' => '2020-09-12',
        'description' => 'Charles "Chuck" Sims Africa was the youngest of the MOVE 9, the brother of Debbie Sims Africa, and a teenager when he joined his family in MOVE. Arrested at age 18 in the August 8, 1978 Powelton Village siege, he was convicted of third-degree murder and sentenced to 30-100 years. He was the last of the living MOVE 9 paroled, released in February 2020. Just months after his release he died of cancer in September 2020, having been diagnosed while still incarcerated.',
        'affiliation' => ['MOVE'],
        'cases' => [['arrest_date' => '1978-08-08', 'sentenced_date' => '1981-04-01', 'sentence' => '30 to 100 years', 'release_date' => '2020-02-07', 'convicted' => 'Yes', 'judge' => 'Hon. Edwin S. Malmed', 'charges' => $move9Charges, 'institution' => $moveCourt]],
    ],
    [
        'name' => 'Delbert Orr Africa', 'first_name' => 'Delbert', 'middle_name' => 'Orr', 'last_name' => 'Africa',
        'aka' => 'Delbert Africa', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Pennsylvania',
        'death_date' => '2020-06-15',
        'description' => 'Delbert Orr Africa was a senior member and public spokesperson for MOVE in the 1970s. During the August 8, 1978 Powelton Village siege he was famously beaten by Philadelphia police on live television after surrendering unarmed; the officers seen kicking him with helmets and rifle butts were later acquitted. Convicted with the other MOVE 9, he was sentenced to 30-100 years and paroled in January 2020 after more than 41 years; he died of cancer in June 2020, less than six months after his release.',
        'affiliation' => ['MOVE'],
        'cases' => [['arrest_date' => '1978-08-08', 'sentenced_date' => '1981-04-01', 'sentence' => '30 to 100 years', 'release_date' => '2020-01-18', 'convicted' => 'Yes', 'judge' => 'Hon. Edwin S. Malmed', 'charges' => $move9Charges, 'institution' => $moveCourt]],
    ],
    [
        'name' => 'William Phillips Africa', 'first_name' => 'William', 'last_name' => 'Phillips Africa',
        'aka' => 'Phil Africa', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Pennsylvania',
        'death_date' => '2015-01-10',
        'description' => 'William "Phil" Africa was a senior MOVE member, a martial-arts-trained minister of defense for the organization, and the husband of Janine Phillips Africa. Arrested in the August 8, 1978 Powelton Village siege and convicted with the other MOVE 9, he was sentenced to 30-100 years. He died in custody at SCI Dallas in January 2015 under circumstances his family disputed, alleging medical neglect after a sudden illness; he never lived to see any of the MOVE 9 paroled.',
        'affiliation' => ['MOVE'], 'released' => false,
        'cases' => [['arrest_date' => '1978-08-08', 'sentenced_date' => '1981-04-01', 'sentence' => '30 to 100 years', 'death_in_custody_date' => '2015-01-10', 'convicted' => 'Yes', 'judge' => 'Hon. Edwin S. Malmed', 'charges' => $move9Charges, 'institution' => $moveCourt]],
    ],
    [
        'name' => 'Merle Austin Africa', 'first_name' => 'Merle', 'middle_name' => 'Austin', 'last_name' => 'Africa',
        'aka' => 'Merle Africa', 'gender' => 'Female', 'race' => 'Black', 'state' => 'Pennsylvania',
        'death_date' => '1998-03-13',
        'description' => 'Merle Austin Africa was a MOVE member arrested at Powelton Village following the August 8, 1978 confrontation and convicted with eight others of third-degree murder. Sentenced to 30-100 years, she was the first of the MOVE 9 to die in custody, dying at SCI Cambridge Springs in March 1998 after roughly 20 years incarcerated. MOVE supporters disputed the official account of her death, alleging inadequate medical care.',
        'affiliation' => ['MOVE'], 'released' => false,
        'cases' => [['arrest_date' => '1978-08-08', 'sentenced_date' => '1981-04-01', 'sentence' => '30 to 100 years', 'death_in_custody_date' => '1998-03-13', 'convicted' => 'Yes', 'judge' => 'Hon. Edwin S. Malmed', 'charges' => $move9Charges, 'institution' => $moveCourt]],
    ],
    [
        'name' => 'Ramona Africa', 'first_name' => 'Ramona', 'last_name' => 'Africa',
        'aka' => 'Ramona Johnson Africa', 'gender' => 'Female', 'race' => 'Black', 'state' => 'Pennsylvania',
        'description' => 'Ramona Africa joined MOVE in the late 1970s and became MOVE\'s communications minister in the 1980s. She was the only adult survivor of the May 13, 1985 Philadelphia police bombing of the MOVE house at 6221 Osage Avenue, in which a satchel charge of C-4 and Tovex was dropped from a state police helicopter, killing 11 people including MOVE founder John Africa and five children, and burning down 61 row homes; she escaped the burning house with 13-year-old Birdie Africa (Michael Ward). Despite being a survivor of a bombing by her own city government, she was charged and convicted of riot and conspiracy and sentenced to seven years, which she served in full, refusing parole conditions that required her to disassociate from MOVE. Released in 1992, she has spent decades as MOVE\'s most prominent public spokesperson.',
        'affiliation' => ['MOVE'],
        'cases' => [['arrest_date' => '1985-05-13', 'sentenced_date' => '1986-04-01', 'sentence' => '7 years (served in full)', 'release_date' => '1992-05-13', 'convicted' => 'Yes', 'charges' => 'Riot, conspiracy, and aggravated assault for events leading up to the May 13, 1985 Philadelphia police bombing of the MOVE house at 6221 Osage Avenue. The MOVE Special Investigation Commission (1985, chaired by William H. Brown III) concluded that dropping the bomb was unconscionable; no city official was ever criminally charged. A 1996 federal jury awarded Ramona Africa and the families of two victims $1.5 million in a civil-rights suit against the City of Philadelphia.', 'institution' => $moveCourt]],
    ],
];

$ideologiesMove = ['Black liberation', 'Animal rights', 'Naturalist / back-to-nature', 'Anti-technology'];
foreach ($movePeople as $p) {
    $p['ideologies'] = $ideologiesMove;
    $p['era'] = '1970s';
    [$prisoner, $created] = upsertPrisoner($p);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$p['name']} (id={$prisoner->id})\n";
    $created ? $totals['created']++ : $totals['existed']++;
    foreach ($p['cases'] ?? [] as $c) {
        if (attachCase($prisoner, $c)) {
            echo "    + case (arrest_date={$c['arrest_date']})\n";
            $totals['cases']++;
        }
    }
}

// =================== Cluster 2: FALN ===================

$falnEvanston = ['name' => 'U.S. District Court, Northern District of Illinois — U.S. v. Rosado et al. (Evanston FALN)', 'city' => 'Chicago', 'state' => 'Illinois'];
$falnChicago1983 = ['name' => 'U.S. District Court, Northern District of Illinois — Chicago FALN (1983 indictment)', 'city' => 'Chicago', 'state' => 'Illinois'];
$falnSDNY = ['name' => 'U.S. District Court, Southern District of New York / NY State (FALN bombings)', 'city' => 'New York', 'state' => 'New York'];

$falnChargesEvanston = 'Seditious conspiracy (18 U.S.C. § 2384), armed robbery (Hobbs Act), interstate transportation of firearms with intent to commit violent crime, weapons possession. Defendants asserted prisoner-of-war status under Protocol I of the Geneva Conventions and refused to participate in the trial.';

$falnPeople = [
    [
        'name' => 'Oscar López Rivera', 'first_name' => 'Oscar', 'last_name' => 'López Rivera',
        'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Puerto Rico',
        'birthdate' => '1943-01-06',
        'description' => 'Oscar López Rivera, born in San Sebastián, Puerto Rico, was raised in Chicago and decorated as a U.S. Army Vietnam veteran (Bronze Star) before becoming a community organizer in Chicago\'s Puerto Rican community and a co-founder of institutions like the Pedro Albizu Campos Puerto Rican High School. Identified by the FBI as a leader of the FALN, he went underground after the 1980 Evanston arrests and was captured by the FBI on May 29, 1981 in Glenview, Illinois. Convicted of seditious conspiracy and related charges, sentenced to 55 years (with an additional 15 added in 1988 for an alleged escape conspiracy), he refused the 1999 Clinton commutation because it excluded co-defendants. After a long international clemency campaign his sentence was commuted by President Barack Obama on January 17, 2017 and he was released on May 17, 2017 after roughly 36 years.',
        'affiliation' => ['FALN'],
        'cases' => [['arrest_date' => '1981-05-29', 'sentenced_date' => '1981-08-11', 'sentence' => '55 years federal prison + 15 years (1988 escape conspiracy)', 'release_date' => '2017-05-17', 'convicted' => 'Yes', 'charges' => 'Seditious conspiracy, armed robbery (Hobbs Act), interstate transportation of firearms, weapons possession, conspiracy to escape (1988).', 'institution' => $falnEvanston]],
    ],
    [
        'name' => 'Carmen Valentín Pérez', 'first_name' => 'Carmen', 'middle_name' => 'Valentín', 'last_name' => 'Pérez',
        'aka' => 'Carmen Valentín', 'gender' => 'Female', 'race' => 'Latino/Hispanic', 'state' => 'Illinois',
        'birthdate' => '1946-04-19',
        'description' => 'Carmen Valentín was a Chicago public-school guidance counselor and longtime community activist in the city\'s Puerto Rican movement before her arrest with ten other FALN suspects in Evanston, Illinois on April 4, 1980. Like her co-defendants she declared herself a prisoner of war and was convicted of seditious conspiracy and weapons offenses; her 90-year sentence was one of the longest imposed in the case. President Bill Clinton commuted her sentence on August 11, 1999 and she was released September 10, 1999.',
        'affiliation' => ['FALN'],
        'cases' => [['arrest_date' => '1980-04-04', 'sentenced_date' => '1981-02-18', 'sentence' => '90 years federal prison', 'release_date' => '1999-09-10', 'convicted' => 'Yes', 'judge' => 'Hon. Thomas R. McMillen', 'prosecutor' => 'Jeremy D. Margolis (AUSA, N.D. Ill.)', 'charges' => $falnChargesEvanston, 'institution' => $falnEvanston]],
    ],
    [
        'name' => 'Alicia Rodríguez', 'first_name' => 'Alicia', 'last_name' => 'Rodríguez',
        'gender' => 'Female', 'race' => 'Latino/Hispanic', 'state' => 'Illinois',
        'description' => 'Alicia Rodríguez, sister of Ida Luz "Lucy" Rodríguez, was a young Chicago activist arrested with ten others in Evanston on April 4, 1980. She and her sister both refused to recognize the court\'s jurisdiction, asserting prisoner-of-war status. Convicted of seditious conspiracy and weapons offenses, sentenced to 55 years; her sentence was commuted by Clinton on August 11, 1999 and she was released that September. She has worked as an educator and ecological-justice organizer in Chicago since.',
        'affiliation' => ['FALN'],
        'cases' => [['arrest_date' => '1980-04-04', 'sentenced_date' => '1981-02-18', 'sentence' => '55 years federal prison', 'release_date' => '1999-09-10', 'convicted' => 'Yes', 'judge' => 'Hon. Thomas R. McMillen', 'prosecutor' => 'Jeremy D. Margolis (AUSA, N.D. Ill.)', 'charges' => $falnChargesEvanston, 'institution' => $falnEvanston]],
    ],
    [
        'name' => 'Ida Luz Rodríguez', 'first_name' => 'Ida', 'middle_name' => 'Luz', 'last_name' => 'Rodríguez',
        'aka' => 'Lucy Rodríguez', 'gender' => 'Female', 'race' => 'Latino/Hispanic', 'state' => 'Illinois',
        'description' => 'Ida Luz "Lucy" Rodríguez, older sister of Alicia Rodríguez, was a Chicago community activist arrested with the rest of the Evanston cell on April 4, 1980. Convicted of seditious conspiracy and weapons offenses; sentenced to 75 years; sentence commuted by Clinton in August 1999 and released September 10, 1999.',
        'affiliation' => ['FALN'],
        'cases' => [['arrest_date' => '1980-04-04', 'sentenced_date' => '1981-02-18', 'sentence' => '75 years federal prison', 'release_date' => '1999-09-10', 'convicted' => 'Yes', 'judge' => 'Hon. Thomas R. McMillen', 'prosecutor' => 'Jeremy D. Margolis (AUSA, N.D. Ill.)', 'charges' => $falnChargesEvanston, 'institution' => $falnEvanston]],
    ],
    [
        'name' => 'Ricardo Jiménez', 'first_name' => 'Ricardo', 'last_name' => 'Jiménez',
        'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Illinois',
        'description' => 'Ricardo Jiménez, then 23, was the youngest of the Evanston defendants when he was arrested on April 4, 1980. He declared prisoner-of-war status, was convicted of seditious conspiracy and weapons offenses, and sentenced to 90 years. Released after Clinton\'s 1999 commutation, he has been a community organizer in Chicago, including at the Puerto Rican Cultural Center.',
        'affiliation' => ['FALN'],
        'cases' => [['arrest_date' => '1980-04-04', 'sentenced_date' => '1981-02-18', 'sentence' => '90 years federal prison', 'release_date' => '1999-09-10', 'convicted' => 'Yes', 'judge' => 'Hon. Thomas R. McMillen', 'prosecutor' => 'Jeremy D. Margolis (AUSA, N.D. Ill.)', 'charges' => $falnChargesEvanston, 'institution' => $falnEvanston]],
    ],
    [
        'name' => 'Adolfo Matos Antongiorgi', 'first_name' => 'Adolfo', 'middle_name' => 'Matos', 'last_name' => 'Antongiorgi',
        'aka' => 'Adolfo Matos', 'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Illinois',
        'description' => 'Adolfo Matos was arrested on April 4, 1980 at the FALN safe-house operation in Evanston, Illinois. He refused to recognize U.S. jurisdiction and was convicted of seditious conspiracy and weapons offenses, receiving a 70-year sentence. After 19 years his sentence was commuted by Clinton on August 11, 1999 and he was released the following month; he returned to Puerto Rico.',
        'affiliation' => ['FALN'],
        'cases' => [['arrest_date' => '1980-04-04', 'sentenced_date' => '1981-02-18', 'sentence' => '70 years federal prison', 'release_date' => '1999-09-10', 'convicted' => 'Yes', 'judge' => 'Hon. Thomas R. McMillen', 'prosecutor' => 'Jeremy D. Margolis (AUSA, N.D. Ill.)', 'charges' => $falnChargesEvanston, 'institution' => $falnEvanston]],
    ],
    [
        'name' => 'Dylcia Noemí Pagán', 'first_name' => 'Dylcia', 'middle_name' => 'Noemí', 'last_name' => 'Pagán',
        'aka' => 'Dylcia Pagán', 'gender' => 'Female', 'race' => 'Latino/Hispanic', 'state' => 'Illinois',
        'birthdate' => '1946-10-15',
        'description' => 'Dylcia Pagán was a New York-born television producer (NBC, Channel 13) and journalist who had worked on Puerto Rican community programming before being identified as a FALN member and captured in the wake of the April 1980 Evanston arrests. Convicted of seditious conspiracy and weapons offenses, she was sentenced to 63 years, while her young son Guillermo (born underground) was raised by family. Her sentence was commuted by Clinton in August 1999 and she settled in Puerto Rico, where she has worked as a poet, writer, and activist.',
        'affiliation' => ['FALN'],
        'cases' => [['arrest_date' => '1980-04-04', 'sentenced_date' => '1981-02-18', 'sentence' => '63 years federal prison', 'release_date' => '1999-09-10', 'convicted' => 'Yes', 'judge' => 'Hon. Thomas R. McMillen', 'prosecutor' => 'Jeremy D. Margolis (AUSA, N.D. Ill.)', 'charges' => $falnChargesEvanston, 'institution' => $falnEvanston]],
    ],
    [
        'name' => 'Edwin Cortés', 'first_name' => 'Edwin', 'last_name' => 'Cortés',
        'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Illinois',
        'description' => 'Edwin Cortés was a Chicago Puerto Rican community activist arrested in June 1983 in Chicago in a separate FALN-related case (with Alberto Rodríguez and Alejandrina Torres) following an FBI surveillance operation that recorded an alleged FALN safe house. Convicted of seditious conspiracy and weapons offenses; 35-year sentence commuted by Clinton August 1999; released September 10, 1999.',
        'affiliation' => ['FALN'],
        'cases' => [['arrest_date' => '1983-06-29', 'sentenced_date' => '1985-02-01', 'sentence' => '35 years federal prison', 'release_date' => '1999-09-10', 'convicted' => 'Yes', 'charges' => 'Seditious conspiracy (18 U.S.C. § 2384); weapons possession; conspiracy.', 'institution' => $falnChicago1983]],
    ],
    [
        'name' => 'Elizam Escobar', 'first_name' => 'Elizam', 'last_name' => 'Escobar',
        'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Illinois',
        'birthdate' => '1948-05-24', 'death_date' => '2021-02-19',
        'description' => 'Elizam Escobar was a Puerto Rican painter and poet, trained at Pratt Institute and the Art Students League in New York, arrested in the April 4, 1980 Evanston roundup. Convicted of seditious conspiracy and weapons offenses, he served almost 20 years, during which he produced a substantial body of paintings, drawings, and theoretical writing on art and politics. His sentence was commuted by Clinton in August 1999; after release he taught at the Escuela de Artes Plásticas in Puerto Rico and continued his career as a painter and essayist until his death in 2021.',
        'affiliation' => ['FALN'],
        'cases' => [['arrest_date' => '1980-04-04', 'sentenced_date' => '1981-02-18', 'sentence' => '68 years federal prison', 'release_date' => '1999-09-10', 'convicted' => 'Yes', 'judge' => 'Hon. Thomas R. McMillen', 'prosecutor' => 'Jeremy D. Margolis (AUSA, N.D. Ill.)', 'charges' => $falnChargesEvanston, 'institution' => $falnEvanston]],
    ],
    [
        'name' => 'Luis Rosa', 'first_name' => 'Luis', 'last_name' => 'Rosa',
        'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Illinois',
        'description' => 'Luis Rosa was 19, the youngest of the Evanston eleven, when arrested on April 4, 1980. Like the others he refused to recognize the court\'s jurisdiction and was convicted of seditious conspiracy and weapons offenses, sentenced to 75 years. President Clinton commuted his sentence on August 11, 1999 and he returned to Chicago, where he has worked in education and youth programs.',
        'affiliation' => ['FALN'],
        'cases' => [['arrest_date' => '1980-04-04', 'sentenced_date' => '1981-02-18', 'sentence' => '75 years federal prison', 'release_date' => '1999-09-10', 'convicted' => 'Yes', 'judge' => 'Hon. Thomas R. McMillen', 'prosecutor' => 'Jeremy D. Margolis (AUSA, N.D. Ill.)', 'charges' => $falnChargesEvanston, 'institution' => $falnEvanston]],
    ],
    [
        'name' => 'Alberto Rodríguez', 'first_name' => 'Alberto', 'last_name' => 'Rodríguez',
        'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Illinois',
        'description' => 'Alberto Rodríguez was a Chicago community organizer arrested in June 1983 with Edwin Cortés and Alejandrina Torres following an FBI bug-and-camera operation in a Chicago apartment alleged to be a FALN safe house. Convicted of seditious conspiracy and related charges; sentenced to 35 years; sentence commuted by Clinton August 1999.',
        'affiliation' => ['FALN'],
        'cases' => [['arrest_date' => '1983-06-29', 'sentenced_date' => '1985-02-01', 'sentence' => '35 years federal prison', 'release_date' => '1999-09-10', 'convicted' => 'Yes', 'charges' => 'Seditious conspiracy (18 U.S.C. § 2384); weapons possession; conspiracy.', 'institution' => $falnChicago1983]],
    ],
    [
        'name' => 'Carlos Alberto Torres', 'first_name' => 'Carlos', 'middle_name' => 'Alberto', 'last_name' => 'Torres',
        'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Illinois',
        'birthdate' => '1952-09-19',
        'description' => 'Carlos Alberto Torres, a Chicago-raised activist and former student leader at the Puerto Rican Cultural Center, was arrested with the FALN cell in Evanston, Illinois on April 4, 1980 and convicted of seditious conspiracy and weapons offenses, sentenced to 78 years. He refused the conditional 1999 Clinton commutation along with Oscar López Rivera and Haydée Beltrán Torres. He was released on parole on July 26, 2010 by the U.S. Parole Commission and returned to Puerto Rico, where he has been an active independence-movement organizer.',
        'affiliation' => ['FALN'],
        'cases' => [['arrest_date' => '1980-04-04', 'sentenced_date' => '1981-02-18', 'sentence' => '78 years federal prison', 'release_date' => '2010-07-26', 'convicted' => 'Yes', 'judge' => 'Hon. Thomas R. McMillen', 'prosecutor' => 'Jeremy D. Margolis (AUSA, N.D. Ill.)', 'charges' => $falnChargesEvanston, 'institution' => $falnEvanston]],
    ],
    [
        'name' => 'Haydée Beltrán Torres', 'first_name' => 'Haydée', 'middle_name' => 'Beltrán', 'last_name' => 'Torres',
        'aka' => 'Haydée Beltrán', 'gender' => 'Female', 'race' => 'Latino/Hispanic', 'state' => 'New York',
        'description' => 'Haydée Beltrán Torres (sister of Carlos Alberto Torres) was prosecuted separately from the Evanston cell, in the Southern District of New York, in connection with FALN bombings in Manhattan including the August 3, 1977 Mobil Oil building bombing that killed Charles Steinberg. Convicted in 1980 and sentenced to life imprisonment under New York state and federal charges, she declined the 1999 Clinton commutation offer along with Oscar López Rivera and Carlos Alberto Torres. She was paroled in February 2009 after serving roughly 28 years.',
        'affiliation' => ['FALN'],
        'cases' => [['arrest_date' => '1980-04-04', 'sentenced_date' => '1980-12-01', 'sentence' => 'Life (NY state) + ~35-year federal term', 'release_date' => '2009-02-15', 'convicted' => 'Yes', 'charges' => 'Seditious conspiracy; second-degree murder (NY state, Mobil Oil bombing); conspiracy; explosives offenses.', 'institution' => $falnSDNY]],
    ],
];

$ideologiesPRI = ['Puerto Rican independence', 'Anti-imperialism', 'Socialism'];
foreach ($falnPeople as $p) {
    $p['ideologies'] = $ideologiesPRI;
    $p['era'] = '1980s';
    [$prisoner, $created] = upsertPrisoner($p);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$p['name']} (id={$prisoner->id})\n";
    $created ? $totals['created']++ : $totals['existed']++;
    foreach ($p['cases'] ?? [] as $c) {
        if (attachCase($prisoner, $c)) {
            echo "    + case (arrest_date={$c['arrest_date']})\n";
            $totals['cases']++;
        }
    }
}

// =================== Cluster 3: Macheteros ===================

$macheterosCourt = ['name' => 'U.S. District Court, District of Connecticut — U.S. v. Gerena et al. (Wells Fargo)', 'city' => 'Hartford', 'state' => 'Connecticut'];
$macheterosBaseCharges = '1985 U.S. v. Gerena et al. (D. Conn.) prosecution of the Macheteros / Ejército Popular Boricua for the September 12, 1983 Wells Fargo armored-car robbery in West Hartford, CT — $7.17 million taken, then the largest cash robbery in U.S. history. Charges: bank-robbery conspiracy (18 U.S.C. §§ 371, 2113); foreign transportation of stolen money (18 U.S.C. § 2314); RICO predicates.';

$macheteros = [
    [
        'name' => 'Filiberto Ojeda Ríos', 'first_name' => 'Filiberto', 'last_name' => 'Ojeda Ríos',
        'aka' => 'El Comandante', 'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Puerto Rico',
        'birthdate' => '1933-04-26', 'death_date' => '2005-09-23',
        'description' => 'Filiberto Ojeda Ríos was a musician, longtime Puerto Rican independence militant, and the founding commander of the Ejército Popular Boricua (Macheteros). After earlier underground activity with MIRA in the 1960s, he co-founded the Macheteros around 1976 and led several high-profile actions, including the 1979 Sabana Seca attack on a U.S. Navy bus and (allegedly) the September 12, 1983 Wells Fargo armored-car robbery in West Hartford, Connecticut. He was arrested on August 30, 1985 in a massive FBI raid in Puerto Rico but in 1990 cut off his electronic monitor and went underground, living as a fugitive for 15 years until he was killed by FBI agents on September 23, 2005 at his hideout in Hormigueros — the anniversary of the Grito de Lares. The Puerto Rico Civil Rights Commission has characterized the FBI operation as an extrajudicial killing.',
        'affiliation' => ['Macheteros / Ejército Popular Boricua', 'Movimiento Independentista Revolucionario en Armas (MIRA)'],
        'released' => false,
        'cases' => [['arrest_date' => '1985-08-30', 'sentenced_date' => '1992-07-01', 'sentence' => '55 years (Puerto Rico assault case, in absentia, 1992); main Hartford case unresolved as to him', 'death_in_custody_date' => '2005-09-23', 'convicted' => 'Yes (in absentia for PR assault case)', 'charges' => 'Bank robbery (18 U.S.C. § 2113); conspiracy; foreign transportation of stolen money; RICO; armed assault on FBI agents during the 1985 arrest in his apartment (separately prosecuted in Puerto Rico).', 'institution' => $macheterosCourt]],
    ],
    [
        'name' => 'Juan Enrique Segarra Palmer', 'first_name' => 'Juan', 'middle_name' => 'Enrique', 'last_name' => 'Segarra Palmer',
        'aka' => 'Juan Segarra Palmer', 'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Puerto Rico',
        'description' => 'Juan Enrique Segarra Palmer, Harvard-educated and from a prominent Puerto Rican family, was identified by federal prosecutors as a senior Macheteros leader and a principal organizer of the September 1983 Wells Fargo robbery, including the recruitment of Wells Fargo guard Víctor Gerena. Arrested in the August 30, 1985 sweep, he went to trial rather than accept a plea and was convicted in 1989 of conspiracy and foreign transportation of stolen money, receiving a 65-year sentence. His sentence was commuted by Clinton on August 11, 1999 and he was released on parole on October 11, 2004.',
        'affiliation' => ['Macheteros / Ejército Popular Boricua'],
        'cases' => [['arrest_date' => '1985-08-30', 'sentenced_date' => '1989-08-29', 'sentence' => '65 years federal prison', 'release_date' => '2004-10-11', 'convicted' => 'Yes', 'judge' => 'Hon. Alan H. Nevas', 'prosecutor' => 'Albert S. Dabrowski / Leonard Boyle (AUSA, D. Conn.)', 'charges' => $macheterosBaseCharges, 'institution' => $macheterosCourt]],
    ],
    [
        'name' => 'Roberto José Maldonado Rivera', 'first_name' => 'Roberto', 'middle_name' => 'José', 'last_name' => 'Maldonado Rivera',
        'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Puerto Rico',
        'description' => 'Roberto Maldonado Rivera, a Puerto Rican attorney, was arrested in the August 30, 1985 Macheteros roundup and charged in connection with the Wells Fargo robbery prosecution. He was convicted at trial in the District of Connecticut and his sentence was commuted by Clinton on August 11, 1999.',
        'affiliation' => ['Macheteros / Ejército Popular Boricua'],
        'cases' => [['arrest_date' => '1985-08-30', 'sentenced_date' => '1989-08-29', 'sentence' => '5 years federal prison (commuted)', 'release_date' => '1999-09-10', 'convicted' => 'Yes', 'charges' => $macheterosBaseCharges, 'institution' => $macheterosCourt]],
    ],
    [
        'name' => 'Norman Ramírez Talavera', 'first_name' => 'Norman', 'last_name' => 'Ramírez Talavera',
        'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Puerto Rico',
        'description' => 'Norman Ramírez Talavera was arrested in the August 30, 1985 Macheteros sweep, took a plea agreement, and received a comparatively short sentence in the early 1990s. He was among the Macheteros defendants whose remaining sentences were commuted by Clinton on August 11, 1999.',
        'affiliation' => ['Macheteros / Ejército Popular Boricua'],
        'cases' => [['arrest_date' => '1985-08-30', 'sentence' => '5 years federal prison (plea)', 'release_date' => '1999-09-10', 'convicted' => 'Yes — guilty plea', 'charges' => $macheterosBaseCharges, 'institution' => $macheterosCourt]],
    ],
    [
        'name' => 'Hilton Fernández Diamante', 'first_name' => 'Hilton', 'last_name' => 'Fernández Diamante',
        'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Puerto Rico',
        'description' => 'Hilton Fernández Diamante was arrested in the August 30, 1985 Macheteros operation. He pleaded guilty to a conspiracy charge in connection with the Wells Fargo robbery and received a relatively short sentence; remaining obligations were resolved by Clinton\'s August 1999 commutation.',
        'affiliation' => ['Macheteros / Ejército Popular Boricua'],
        'cases' => [['arrest_date' => '1985-08-30', 'sentence' => '5 years federal prison (plea)', 'release_date' => '1999-09-10', 'convicted' => 'Yes — guilty plea', 'charges' => $macheterosBaseCharges, 'institution' => $macheterosCourt]],
    ],
    [
        'name' => 'Carlos Ayes Suárez', 'first_name' => 'Carlos', 'last_name' => 'Ayes Suárez',
        'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Puerto Rico',
        'description' => 'Carlos Ayes Suárez was arrested in the August 30, 1985 Macheteros operation and accepted a plea agreement on conspiracy charges related to the Wells Fargo robbery. Remaining obligations were addressed in Clinton\'s August 11, 1999 commutation.',
        'affiliation' => ['Macheteros / Ejército Popular Boricua'],
        'cases' => [['arrest_date' => '1985-08-30', 'sentence' => '5 years federal prison (plea)', 'release_date' => '1999-09-10', 'convicted' => 'Yes — guilty plea', 'charges' => $macheterosBaseCharges, 'institution' => $macheterosCourt]],
    ],
    [
        'name' => 'Antonio Camacho Negrón', 'first_name' => 'Antonio', 'last_name' => 'Camacho Negrón',
        'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Puerto Rico',
        'description' => 'Antonio Camacho Negrón was arrested in the August 30, 1985 Macheteros operation. He refused to take a plea, went to trial, and was convicted of conspiracy and foreign transportation of stolen money in connection with the Wells Fargo robbery, receiving a 15-year sentence. He served his time, was released, and later refused on principle to participate in supervised release; he was returned to prison briefly in the early 2000s for that refusal before being released — a notable independentista act of civil resistance.',
        'affiliation' => ['Macheteros / Ejército Popular Boricua'],
        'cases' => [['arrest_date' => '1985-08-30', 'sentenced_date' => '1989-08-29', 'sentence' => '15 years federal prison', 'release_date' => '2004-06-15', 'convicted' => 'Yes', 'judge' => 'Hon. Alan H. Nevas', 'charges' => $macheterosBaseCharges, 'institution' => $macheterosCourt]],
    ],
    [
        'name' => 'Luz Berríos Berríos', 'first_name' => 'Luz', 'last_name' => 'Berríos Berríos',
        'gender' => 'Female', 'race' => 'Latino/Hispanic', 'state' => 'Puerto Rico',
        'description' => 'Luz Berríos Berríos was arrested in the August 30, 1985 Macheteros operation and was a plea defendant in the Wells Fargo prosecution. Remaining obligations were resolved by Clinton\'s August 1999 commutation.',
        'affiliation' => ['Macheteros / Ejército Popular Boricua'],
        'cases' => [['arrest_date' => '1985-08-30', 'sentence' => '5 years federal prison (plea)', 'release_date' => '1999-09-10', 'convicted' => 'Yes — guilty plea', 'charges' => $macheterosBaseCharges, 'institution' => $macheterosCourt]],
    ],
    [
        'name' => 'Iván Darío Meléndez Carrión', 'first_name' => 'Iván', 'middle_name' => 'Darío', 'last_name' => 'Meléndez Carrión',
        'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Puerto Rico',
        'description' => 'Iván Darío Meléndez Carrión was arrested in the August 30, 1985 Macheteros operation and accepted a plea agreement in connection with the Wells Fargo robbery. Remaining obligations were addressed in Clinton\'s August 11, 1999 commutation.',
        'affiliation' => ['Macheteros / Ejército Popular Boricua'],
        'cases' => [['arrest_date' => '1985-08-30', 'sentence' => '5 years federal prison (plea)', 'release_date' => '1999-09-10', 'convicted' => 'Yes — guilty plea', 'charges' => $macheterosBaseCharges, 'institution' => $macheterosCourt]],
    ],
    [
        'name' => 'Ángel Díaz Ruiz', 'first_name' => 'Ángel', 'last_name' => 'Díaz Ruiz',
        'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Puerto Rico',
        'description' => 'Ángel Díaz Ruiz was arrested in the August 30, 1985 Macheteros operation and was a plea defendant in the Wells Fargo prosecution. Remaining obligations addressed by Clinton\'s August 11, 1999 commutation.',
        'affiliation' => ['Macheteros / Ejército Popular Boricua'],
        'cases' => [['arrest_date' => '1985-08-30', 'sentence' => '5 years federal prison (plea)', 'release_date' => '1999-09-10', 'convicted' => 'Yes — guilty plea', 'charges' => $macheterosBaseCharges, 'institution' => $macheterosCourt]],
    ],
    [
        'name' => 'Avelino González Claudio', 'first_name' => 'Avelino', 'last_name' => 'González Claudio',
        'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Puerto Rico',
        'description' => 'Avelino González Claudio was indicted in the 1985 Wells Fargo case but went underground before he could be arrested in the August 30, 1985 sweep, and lived as a fugitive in Puerto Rico for over two decades. He was captured by the FBI on February 7, 2008 in Manatí and extradited to Connecticut; in 2010 he pleaded guilty to conspiracy in the Wells Fargo robbery and was sentenced to 7 years; released in 2013. He has openly identified as a Machetero veteran and continues independence-movement work in Puerto Rico.',
        'affiliation' => ['Macheteros / Ejército Popular Boricua'],
        'cases' => [['arrest_date' => '2008-02-07', 'sentenced_date' => '2010-09-15', 'sentence' => '7 years federal prison', 'release_date' => '2013-08-01', 'convicted' => 'Yes — guilty plea', 'charges' => 'Conspiracy to commit robbery; foreign transportation of stolen money in connection with the 1983 Wells Fargo robbery (Plan Tortuga prosecution).', 'institution' => $macheterosCourt]],
    ],
    [
        'name' => 'Norberto González Claudio', 'first_name' => 'Norberto', 'last_name' => 'González Claudio',
        'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Puerto Rico',
        'description' => 'Norberto González Claudio, brother of Avelino, was the last major 1985 Wells Fargo indictee at large after the August 30, 1985 sweep. He lived as a fugitive in Puerto Rico for nearly 26 years before his capture by the FBI on May 10, 2011 near Cayey. Extradited to Connecticut, he pleaded guilty to conspiracy in the Wells Fargo robbery and was sentenced in 2013 to 5 years; released around 2014-2015.',
        'affiliation' => ['Macheteros / Ejército Popular Boricua'],
        'cases' => [['arrest_date' => '2011-05-10', 'sentenced_date' => '2013-04-01', 'sentence' => '5 years federal prison', 'release_date' => '2014-12-01', 'convicted' => 'Yes — guilty plea', 'charges' => 'Conspiracy; foreign transportation of stolen money (Plan Tortuga prosecution).', 'institution' => $macheterosCourt]],
    ],
    [
        'name' => 'Orlando González Claudio', 'first_name' => 'Orlando', 'last_name' => 'González Claudio',
        'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Puerto Rico',
        'description' => 'Orlando González Claudio was arrested in the August 30, 1985 Macheteros operation. Unlike his brothers Avelino and Norberto he was in custody from the outset. A plea defendant in the Wells Fargo prosecution; remaining obligations resolved by Clinton\'s August 11, 1999 commutation.',
        'affiliation' => ['Macheteros / Ejército Popular Boricua'],
        'cases' => [['arrest_date' => '1985-08-30', 'sentence' => '5 years federal prison (plea)', 'release_date' => '1999-09-10', 'convicted' => 'Yes — guilty plea', 'charges' => $macheterosBaseCharges, 'institution' => $macheterosCourt]],
    ],
    [
        'name' => 'Luis Alfredo Colón Osorio', 'first_name' => 'Luis', 'middle_name' => 'Alfredo', 'last_name' => 'Colón Osorio',
        'aka' => 'Luis Colón Osorio', 'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'Puerto Rico',
        'description' => 'Luis Colón Osorio was indicted in the 1985 Wells Fargo case but evaded the August 30, 1985 sweep and lived as a fugitive in Puerto Rico for several years before his capture/surrender in 1990. Pleaded guilty in the District of Connecticut to conspiracy charges and received a sentence in the single-digit years range; remaining obligations resolved by Clinton\'s August 11, 1999 commutation.',
        'affiliation' => ['Macheteros / Ejército Popular Boricua'],
        'cases' => [['arrest_date' => '1990-06-01', 'sentence' => '5 years federal prison (plea)', 'release_date' => '1999-09-10', 'convicted' => 'Yes — guilty plea', 'charges' => $macheterosBaseCharges, 'institution' => $macheterosCourt]],
    ],
];

foreach ($macheteros as $p) {
    $p['ideologies'] = ['Puerto Rican independence', 'Marxism', 'Anti-imperialism', 'Bolivarianism'];
    $p['era'] = '1980s';
    [$prisoner, $created] = upsertPrisoner($p);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$p['name']} (id={$prisoner->id})\n";
    $created ? $totals['created']++ : $totals['existed']++;
    foreach ($p['cases'] ?? [] as $c) {
        if (attachCase($prisoner, $c)) {
            echo "    + case (arrest_date={$c['arrest_date']})\n";
            $totals['cases']++;
        }
    }
}

// =================== Cluster 4: Resistance Conspiracy + UFF ===================

$dcGreene = ['name' => 'U.S. District Court, District of Columbia — Resistance Conspiracy Case', 'city' => 'Washington', 'state' => 'District of Columbia'];
$ednyBrooklyn = ['name' => 'U.S. District Court, Eastern District of New York (UFF Brooklyn trial)', 'city' => 'Brooklyn', 'state' => 'New York'];
$dnjCherryHill = ['name' => 'U.S. District Court, District of New Jersey (1985 Rosenberg/Blunk weapons case)', 'city' => 'Newark', 'state' => 'New Jersey'];
$dlaBaton = ['name' => 'U.S. District Court, Middle District of Louisiana (Buck/Evans 1985 firearms case)', 'city' => 'Baton Rouge', 'state' => 'Louisiana'];
$dpaPa = ['name' => 'U.S. District Court, Middle District of Pennsylvania (Berkman/Duke weapons case)', 'city' => 'Harrisburg', 'state' => 'Pennsylvania'];
$njSuperior = ['name' => 'New Jersey Superior Court, Warren County — State v. Manning & Williams (Lamonaco murder)', 'city' => 'Belvidere', 'state' => 'New Jersey'];

$rcCharges = '1988 Resistance Conspiracy Case (D.D.C.): conspiracy (18 U.S.C. § 371) and destruction of government property (18 U.S.C. § 844) for the November 7, 1983 bombing of the U.S. Capitol north wing (after the Grenada invasion) plus seven other 1983-1985 bombings of military and government targets including the Washington Navy Yard, Naval and National War Colleges, Israeli Aircraft Industries (Manhattan), the South African consulate (NYC), and the FBI office on Staten Island. Claimed by the "Armed Resistance Unit," "Red Guerrilla Resistance," and "Revolutionary Fighting Group" — anti-imperialist underground groups associated with the May 19th Communist Organization.';

$rcUff = [
    [
        'name' => 'Susan Lisa Rosenberg', 'first_name' => 'Susan', 'middle_name' => 'Lisa', 'last_name' => 'Rosenberg',
        'gender' => 'Female', 'race' => 'White', 'state' => 'New York',
        'birthdate' => '1955-10-05',
        'description' => 'Susan Rosenberg was a New York-born radical who joined the May 19th Communist Organization in the late 1970s and went underground after being implicated as an accessory in the 1979 prison escape of Assata Shakur and the 1981 Brink\'s robbery. On November 29, 1984 she and Tim Blunk were arrested in Cherry Hill, New Jersey while unloading 740 pounds of dynamite, weapons, and false-identity documents at a storage locker. Sentenced to 58 years for weapons and explosives possession — an unprecedented sentence widely condemned by legal observers — she pled guilty in 1990 to conspiracy in the Resistance Conspiracy Case with no additional time. President Clinton commuted her sentence on January 20, 2001 and she was released after 16 years; she has since worked in HIV/AIDS advocacy and prisoner rights.',
        'affiliation' => ['May 19th Communist Organization', 'Armed Resistance Unit', 'Weather Underground (associate)'],
        'cases' => [
            ['arrest_date' => '1984-11-29', 'sentenced_date' => '1985-05-20', 'sentence' => '58 years federal prison', 'release_date' => '2001-01-20', 'convicted' => 'Yes', 'judge' => 'Hon. Frederick B. Lacey', 'prosecutor' => 'Samuel Alito (U.S. Attorney for New Jersey)', 'charges' => 'Possession of 740 lbs of explosives, unregistered firearms, and false identification documents.', 'institution' => $dnjCherryHill],
            ['arrest_date' => '1990-09-07', 'sentenced_date' => '1990-12-06', 'sentence' => 'Concurrent guilty plea — no additional time', 'release_date' => '2001-01-20', 'convicted' => 'Yes — guilty plea', 'judge' => 'Hon. Harold H. Greene', 'prosecutor' => 'Jay Stephens (U.S. Attorney, D.D.C.)', 'charges' => $rcCharges, 'institution' => $dcGreene],
        ],
    ],
    [
        'name' => 'Marilyn Jean Buck', 'first_name' => 'Marilyn', 'middle_name' => 'Jean', 'last_name' => 'Buck',
        'gender' => 'Female', 'race' => 'White', 'state' => 'New York',
        'birthdate' => '1947-12-13', 'death_date' => '2010-08-03',
        'description' => 'Marilyn Buck was a Texas-born, white anti-imperialist revolutionary, the only white member of the Black Liberation Army, and a poet whose work won a 2001 PEN Prize. Originally convicted in 1973 for purchasing ammunition for the BLA, she escaped from federal prison in 1977 while on furlough and went underground, where she participated in the 1979 prison liberation of Assata Shakur and the 1981 Brink\'s robbery. Arrested in May 1985, she was ultimately convicted of the Brink\'s-related RICO conspiracy (1988) and the Assata Shakur escape, then pled guilty in 1990 in the Resistance Conspiracy Case. She received a cumulative 80-year sentence; she was paroled July 15, 2010 after a cancer diagnosis and died of uterine sarcoma on August 3, 2010, twenty days after release.',
        'affiliation' => ['Black Liberation Army', 'May 19th Communist Organization', 'Armed Resistance Unit'],
        'cases' => [
            ['arrest_date' => '1985-05-11', 'sentenced_date' => '1988-05-09', 'sentence' => '80 years cumulative (50 RICO/Brink\'s + 10 Shakur escape + Resistance Conspiracy partly consecutive)', 'release_date' => '2010-07-15', 'convicted' => 'Yes', 'judge' => 'Hon. Charles S. Haight Jr. (SDNY); Hon. Harold H. Greene (DDC)', 'charges' => 'RICO conspiracy (Brink\'s), aiding 1979 Shakur escape, conspiracy and bombing charges (Resistance Conspiracy 1990).', 'institution' => $dcGreene],
        ],
    ],
    [
        'name' => 'Linda Sue Evans', 'first_name' => 'Linda', 'middle_name' => 'Sue', 'last_name' => 'Evans',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Colorado',
        'birthdate' => '1947-05-11',
        'description' => 'Linda Evans was a Fort Collins-born SDS national organizer who traveled to North Vietnam in 1969 and helped found the Weather Underground. She went underground for over a decade and was arrested in Baton Rouge, Louisiana on May 11, 1985 with Marilyn Buck. Convicted of harboring a fugitive and acquiring four firearms with false identification — receiving a 40-year sentence (then the longest ever imposed for those offenses) — and was a defendant in the Resistance Conspiracy Case (charges dropped 1990 due to her existing sentence). President Clinton commuted her sentence on January 20, 2001 and she was released that day after 16 years.',
        'affiliation' => ['Students for a Democratic Society', 'Weather Underground', 'May 19th Communist Organization', 'Armed Resistance Unit'],
        'cases' => [
            ['arrest_date' => '1985-05-11', 'sentenced_date' => '1986-12-15', 'sentence' => '40 years federal prison', 'release_date' => '2001-01-20', 'convicted' => 'Yes', 'judge' => 'Hon. Frank J. Polozola', 'prosecutor' => 'Stanford O. Bardwell Jr. (AUSA, M.D. La.)', 'charges' => 'Harboring a federal fugitive (Marilyn Buck); using false identification to acquire firearms (4 counts).', 'institution' => $dlaBaton],
        ],
    ],
    [
        'name' => 'Laura Whitehorn', 'first_name' => 'Laura', 'last_name' => 'Whitehorn',
        'gender' => 'Female', 'race' => 'White', 'state' => 'New York',
        'birthdate' => '1945-05-26',
        'description' => 'Laura Whitehorn was a Brooklyn-raised activist and Radcliffe graduate who organized with SDS and Weather, and was a leader of the May 19th Communist Organization in Washington, DC. Arrested May 11, 1985 in Baltimore on conspiracy charges and held in pretrial detention while the Resistance Conspiracy Case was prepared. After more than five years pretrial she pled guilty on September 7, 1990 to conspiracy and destruction of government property (the 1983 U.S. Capitol bombing) and was sentenced to 20 years. Released August 6, 1999, she has since worked as an editor at POZ magazine and an organizer for political-prisoner amnesty.',
        'affiliation' => ['Students for a Democratic Society', 'May 19th Communist Organization', 'Madame Binh Graphics Collective', 'Armed Resistance Unit'],
        'cases' => [
            ['arrest_date' => '1985-05-11', 'sentenced_date' => '1990-12-06', 'sentence' => '20 years federal prison', 'release_date' => '1999-08-06', 'convicted' => 'Yes — guilty plea', 'judge' => 'Hon. Harold H. Greene', 'prosecutor' => 'Jay Stephens (U.S. Attorney, D.D.C.)', 'charges' => $rcCharges, 'institution' => $dcGreene],
        ],
    ],
    [
        'name' => 'Timothy Allen Blunk', 'first_name' => 'Timothy', 'middle_name' => 'Allen', 'last_name' => 'Blunk',
        'aka' => 'Tim Blunk', 'gender' => 'Male', 'race' => 'White', 'state' => 'New Jersey',
        'birthdate' => '1957-01-19',
        'description' => 'Tim Blunk was a New Jersey activist arrested with Susan Rosenberg on November 29, 1984 in Cherry Hill while moving 740 pounds of explosives and weapons into a rented storage locker. Convicted in May 1985 of weapons, explosives, and false-identification offenses and sentenced to 58 years — at the time the longest sentence ever imposed for those offenses. Named in the 1988 Resistance Conspiracy indictment, but charges were dropped in 1990 because he was already serving the 58-year sentence. He co-edited the 1990 anthology "Hauling Up the Morning" with Raymond Luc Levasseur and was paroled in the late 1990s.',
        'affiliation' => ['May 19th Communist Organization', 'Armed Resistance Unit', 'John Brown Anti-Klan Committee'],
        'cases' => [
            ['arrest_date' => '1984-11-29', 'sentenced_date' => '1985-05-20', 'sentence' => '58 years federal prison', 'release_date' => '1999-12-01', 'convicted' => 'Yes', 'judge' => 'Hon. Frederick B. Lacey', 'prosecutor' => 'Samuel Alito (U.S. Attorney for New Jersey)', 'charges' => 'Possession of explosives, unregistered firearms, false identification documents.', 'institution' => $dnjCherryHill],
        ],
    ],
    [
        'name' => 'Alan Berkman', 'first_name' => 'Alan', 'last_name' => 'Berkman',
        'gender' => 'Male', 'race' => 'White', 'state' => 'New York',
        'birthdate' => '1945-09-04', 'death_date' => '2009-06-05',
        'description' => 'Alan Berkman was a Columbia-trained physician who provided medical care to underground revolutionaries, including treating Brink\'s robbery defendant Mutulu Shakur and reportedly treating Marilyn Buck for a gunshot wound after the 1981 Brink\'s robbery. Indicted for refusing to testify before a grand jury, he went underground in 1982 and was captured May 24, 1985 in Doylestown, Pennsylvania. Convicted of weapons, explosives and false-identification charges and named in the 1988 Resistance Conspiracy indictment (charges dropped 1990). He developed Hodgkin\'s lymphoma in prison, became a leading AIDS and global-health activist after his 1992 release, founded Health GAP, and died of cancer on June 5, 2009.',
        'affiliation' => ['May 19th Communist Organization', 'Republic of New Afrika (medical support)', 'Armed Resistance Unit'],
        'cases' => [
            ['arrest_date' => '1985-05-24', 'sentenced_date' => '1987-04-10', 'sentence' => '12 years federal prison', 'release_date' => '1992-08-01', 'convicted' => 'Yes', 'judge' => 'Hon. Sylvia H. Rambo', 'charges' => 'Possession of unregistered weapons and explosives, false identification, harboring a fugitive.', 'institution' => $dpaPa],
        ],
    ],
    [
        'name' => 'Elizabeth Ann Duke', 'first_name' => 'Elizabeth', 'middle_name' => 'Ann', 'last_name' => 'Duke',
        'aka' => 'Liz Duke', 'gender' => 'Female', 'race' => 'White', 'state' => 'Texas',
        'birthdate' => '1940-04-08',
        'description' => 'Elizabeth Ann Duke was a Texas-born former teacher and Methodist minister\'s wife who became a May 19th Communist Organization militant. Arrested May 24, 1985 with Alan Berkman in Doylestown, Pennsylvania, charged with weapons and false-identification offenses and named in the 1988 Resistance Conspiracy indictment. While released on bond in 1985 she failed to appear for trial and went underground; she remained a federal fugitive for nearly 25 years. On August 28, 2009 the U.S. government quietly dropped the charges against her, and she has reportedly been living openly in Cuba.',
        'affiliation' => ['May 19th Communist Organization', 'Armed Resistance Unit'],
        'released' => false,
        'cases' => [
            ['arrest_date' => '1985-05-24', 'sentence' => 'Fugitive — never sentenced; charges dismissed Aug 28, 2009', 'convicted' => 'No — fugitive; charges dismissed', 'judge' => 'Hon. Harold H. Greene (D.D.C.); Hon. Sylvia H. Rambo (M.D. Pa.)', 'charges' => 'Weapons and explosives possession, false identification, conspiracy and destruction of government property (Resistance Conspiracy).', 'institution' => $dcGreene],
        ],
    ],
    // Ohio 7 / United Freedom Front
    [
        'name' => 'Raymond Luc Levasseur', 'first_name' => 'Raymond', 'middle_name' => 'Luc', 'last_name' => 'Levasseur',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Maine',
        'birthdate' => '1946-10-10',
        'description' => 'Raymond Luc Levasseur was a Vietnam veteran from Sanford, Maine and former prisoner-rights organizer who founded the Sam Melville/Jonathan Jackson Unit in 1975 and the United Freedom Front in 1982. After nearly a decade underground with his family, he was arrested November 4, 1984 in Deerfield, Ohio. Convicted in 1986 in Brooklyn federal court of conspiracy, bombings, and bank robberies, sentenced to 45 years. In 1989 he stood trial in Springfield, MA on seditious-conspiracy charges and was acquitted, with the jury hung on RICO counts. Released from federal custody on November 5, 2004 after 20 years, he now lives in Maine.',
        'affiliation' => ['Sam Melville/Jonathan Jackson Unit', 'United Freedom Front'],
        'cases' => [
            ['arrest_date' => '1984-11-04', 'sentenced_date' => '1986-06-27', 'sentence' => '45 years federal prison', 'release_date' => '2004-11-05', 'convicted' => 'Yes', 'judge' => 'Hon. Edward T. Gignoux (1986 EDNY); Hon. William G. Young (1989 Springfield)', 'prosecutor' => 'Jeremiah T. O\'Sullivan (DOJ Organized Crime Strike Force, Boston)', 'charges' => 'RICO conspiracy, bombings (18 U.S.C. § 844), bank robbery (18 U.S.C. § 2113), seditious conspiracy (acquitted 1989).', 'institution' => $ednyBrooklyn],
        ],
    ],
    [
        'name' => 'Patricia Gros Levasseur', 'first_name' => 'Patricia', 'middle_name' => 'Gros', 'last_name' => 'Levasseur',
        'aka' => 'Pat Levasseur', 'gender' => 'Female', 'race' => 'White', 'state' => 'Maine',
        'description' => 'Patricia Gros Levasseur, the wife of Raymond Luc Levasseur, lived underground with him and their three young daughters for nearly a decade and was arrested with him in Deerfield, Ohio on November 4, 1984. She pled guilty in 1986 to harboring a fugitive and was sentenced to five years; she was tried and acquitted in the 1989 Springfield seditious-conspiracy trial.',
        'affiliation' => ['United Freedom Front (support member)'],
        'cases' => [
            ['arrest_date' => '1984-11-04', 'sentenced_date' => '1986-06-27', 'sentence' => '5 years federal prison', 'release_date' => '1988-09-01', 'convicted' => 'Yes — guilty plea', 'judge' => 'Hon. Edward T. Gignoux', 'charges' => 'Harboring a fugitive; seditious conspiracy and RICO (acquitted 1989 Springfield).', 'institution' => $ednyBrooklyn],
        ],
    ],
    [
        'name' => 'Thomas William Manning', 'first_name' => 'Thomas', 'middle_name' => 'William', 'last_name' => 'Manning',
        'aka' => 'Tom Manning', 'gender' => 'Male', 'race' => 'White', 'state' => 'Massachusetts',
        'birthdate' => '1946-06-28', 'death_date' => '2019-07-30',
        'description' => 'Tom Manning was a Boston-born Vietnam veteran (Marine Corps) and former prisoner who became a co-founder of the Sam Melville/Jonathan Jackson Unit and the United Freedom Front. Arrested April 24, 1985 in Norfolk, Virginia and convicted in 1986 in Brooklyn federal court of bombings and bank robberies, receiving 53 years. In 1987 he was convicted by a New Jersey jury of the December 21, 1981 fatal shooting of NJ State Trooper Philip Lamonaco during a roadside stop on I-80 and sentenced to life plus 30 years. A noted prison artist, he died of natural causes in federal prison in Hazelton, West Virginia on July 30, 2019.',
        'affiliation' => ['Sam Melville/Jonathan Jackson Unit', 'United Freedom Front'],
        'released' => false,
        'cases' => [
            ['arrest_date' => '1985-04-24', 'sentenced_date' => '1986-06-27', 'sentence' => '53 years federal prison', 'death_in_custody_date' => '2019-07-30', 'convicted' => 'Yes', 'judge' => 'Hon. Edward T. Gignoux', 'charges' => 'RICO conspiracy, bombings, bank robbery (1986 federal); first-degree murder of NJ State Trooper Philip Lamonaco (1987 NJ state — life plus 30 years).', 'institution' => $ednyBrooklyn],
        ],
    ],
    [
        'name' => 'Carol Ann Manning', 'first_name' => 'Carol', 'middle_name' => 'Ann', 'last_name' => 'Manning',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Massachusetts',
        'description' => 'Carol Ann Manning, wife of Tom Manning, lived underground with the UFF families for years and was arrested April 24, 1985 in Norfolk, Virginia with her husband and three children. She pled guilty in 1986 to harboring a fugitive and was sentenced to 15 years; acquitted in the 1989 Springfield seditious-conspiracy trial.',
        'affiliation' => ['United Freedom Front (support member)'],
        'cases' => [
            ['arrest_date' => '1985-04-24', 'sentenced_date' => '1986-06-27', 'sentence' => '15 years federal prison', 'release_date' => '1990-12-01', 'convicted' => 'Yes — guilty plea', 'judge' => 'Hon. Edward T. Gignoux', 'charges' => 'Harboring a federal fugitive; seditious conspiracy and RICO (acquitted 1989).', 'institution' => $ednyBrooklyn],
        ],
    ],
    [
        'name' => 'Richard Charles Williams', 'first_name' => 'Richard', 'middle_name' => 'Charles', 'last_name' => 'Williams',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Massachusetts',
        'birthdate' => '1947-08-05', 'death_date' => '2005-12-05',
        'description' => 'Richard Williams was a Massachusetts-born radical and Vietnam veteran who joined the United Freedom Front in the late 1970s. Arrested April 24, 1985 in Cleveland, Ohio and convicted in the 1986 Brooklyn federal trial of conspiracy, bombings, and bank robberies, receiving 45 years. In 1987 he was tried with Tom Manning for the December 21, 1981 murder of NJ State Trooper Philip Lamonaco; the first jury hung but he was retried and convicted in 1988, receiving life plus 30 years. He died of liver failure in federal custody at FMC Butner, North Carolina on December 5, 2005.',
        'affiliation' => ['Sam Melville/Jonathan Jackson Unit', 'United Freedom Front'],
        'released' => false,
        'cases' => [
            ['arrest_date' => '1985-04-24', 'sentenced_date' => '1986-06-27', 'sentence' => '45 years federal prison + life plus 30 years (NJ state)', 'death_in_custody_date' => '2005-12-05', 'convicted' => 'Yes', 'judge' => 'Hon. Edward T. Gignoux', 'charges' => 'RICO conspiracy, bombings, bank robbery; first-degree murder of NJ State Trooper Philip Lamonaco.', 'institution' => $ednyBrooklyn],
        ],
    ],
    [
        'name' => 'Jaan Karl Laaman', 'first_name' => 'Jaan', 'middle_name' => 'Karl', 'last_name' => 'Laaman',
        'gender' => 'Male', 'race' => 'White', 'state' => 'New Hampshire',
        'birthdate' => '1948-11-21',
        'description' => 'Jaan Karl Laaman was an Estonian-born, New Hampshire-raised radical who served time in the early 1970s for a New Hampshire police-station bombing before joining the underground UFF in the late 1970s. Arrested November 4, 1984 in Cleveland, Ohio with his family and convicted in the 1986 Brooklyn federal trial of conspiracy, bombings, and bank robberies, receiving 53 years. Tried in the 1989 Springfield seditious-conspiracy case (jury hung on RICO; sedition acquittal). Paroled in June 2021 after 36 years; he hosts the political-prisoner radio program "4 Struggle Magazine" from New Hampshire.',
        'affiliation' => ['Sam Melville/Jonathan Jackson Unit', 'United Freedom Front'],
        'cases' => [
            ['arrest_date' => '1984-11-04', 'sentenced_date' => '1986-06-27', 'sentence' => '53 years federal prison', 'release_date' => '2021-06-08', 'convicted' => 'Yes', 'judge' => 'Hon. Edward T. Gignoux', 'charges' => 'RICO conspiracy, bombings, bank robbery, seditious conspiracy (acquitted on sedition).', 'institution' => $ednyBrooklyn],
        ],
    ],
    [
        'name' => 'Barbara Curzi-Laaman', 'first_name' => 'Barbara', 'last_name' => 'Curzi-Laaman',
        'aka' => 'Barbara Curzi', 'gender' => 'Female', 'race' => 'White', 'state' => 'Massachusetts',
        'description' => 'Barbara Curzi-Laaman, wife of Jaan Laaman, lived underground with the UFF families and was arrested with her husband and children in Cleveland, Ohio on November 4, 1984. She pled guilty in 1986 to harboring a fugitive and was sentenced to 15 years; acquitted in the 1989 Springfield seditious-conspiracy trial.',
        'affiliation' => ['United Freedom Front (support member)'],
        'cases' => [
            ['arrest_date' => '1984-11-04', 'sentenced_date' => '1986-06-27', 'sentence' => '15 years federal prison', 'release_date' => '1995-12-01', 'convicted' => 'Yes — guilty plea', 'judge' => 'Hon. Edward T. Gignoux', 'charges' => 'Harboring a federal fugitive; seditious conspiracy and RICO (acquitted 1989).', 'institution' => $ednyBrooklyn],
        ],
    ],
    [
        'name' => 'Kazi Toure', 'first_name' => 'Kazi', 'last_name' => 'Toure',
        'aka' => 'Christopher King', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Massachusetts',
        'description' => 'Kazi Toure (born Christopher King) was a Boston-born Black-liberation activist and the only Black member of the United Freedom Front. The first UFF member to be captured, he was arrested in 1982 and convicted in 1985 in the seditious-conspiracy and racketeering case, receiving 10 years. Released in 1991, he has continued to organize for Black liberation, anti-imperialism, and political-prisoner amnesty in the Boston area.',
        'affiliation' => ['United Freedom Front', 'Sam Melville/Jonathan Jackson Unit'],
        'cases' => [
            ['arrest_date' => '1982-04-04', 'sentenced_date' => '1985-06-01', 'sentence' => '10 years federal prison', 'release_date' => '1991-05-01', 'convicted' => 'Yes', 'judge' => 'Hon. Edward T. Gignoux', 'charges' => 'Seditious conspiracy, RICO, weapons possession.', 'institution' => $ednyBrooklyn],
        ],
    ],
];

foreach ($rcUff as $p) {
    $p['ideologies'] = ['Anti-imperialism', 'Communism', 'Anti-racism', 'Anti-apartheid'];
    $p['era'] = '1980s';
    [$prisoner, $created] = upsertPrisoner($p);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$p['name']} (id={$prisoner->id})\n";
    $created ? $totals['created']++ : $totals['existed']++;
    foreach ($p['cases'] ?? [] as $c) {
        if (attachCase($prisoner, $c)) {
            echo "    + case (arrest_date={$c['arrest_date']})\n";
            $totals['cases']++;
        }
    }
}

// =================== Cluster 5: Free South Africa Movement ===================

$fsamInstitution = ['name' => 'South African Embassy / DC Superior Court — Free South Africa Movement (FSAM)', 'city' => 'Washington', 'state' => 'District of Columbia'];
$fsamCharges = 'Misdemeanor DC Code 22-1307 (congregating within 500 feet of a foreign embassy / failure to disperse) or unlawful entry. Free South Africa Movement civil disobedience at the South African Embassy on Massachusetts Avenue. The DC U.S. Attorney (Joseph diGenova) declined to prosecute most arrestees, holding the cases lacked "prosecutive merit." Approximately 5,000 such arrests in DC alone Nov 1984 - 1986; daily sit-ins helped produce the Comprehensive Anti-Apartheid Act of 1986, passed over President Reagan\'s veto.';
$fsamSentence = 'Released same day or following morning; charges declined / dropped (typical FSAM disposition).';

$fsam = [
    ['name' => 'Randall Robinson', 'first_name' => 'Randall', 'last_name' => 'Robinson', 'gender' => 'Male', 'race' => 'Black', 'state' => 'District of Columbia', 'birthdate' => '1941-07-06', 'death_date' => '2023-03-24', 'description' => 'Randall Robinson was the lawyer-activist who founded TransAfrica in 1977 to organize Black American foreign-policy advocacy on Africa and the Caribbean. He architected the November 21, 1984 sit-in at the South African embassy that launched the Free South Africa Movement and chaired its steering committee. After apartheid, Robinson led TransAfrica\'s pressure on the Clinton administration over Haiti and wrote "The Debt: What America Owes to Blacks" (2000). He spent his final years in self-imposed exile on Saint Kitts.', 'affiliation' => ['TransAfrica (founder)', 'Free South Africa Movement (architect)'], 'arrest' => '1984-11-21'],
    ['name' => 'Mary Frances Berry', 'first_name' => 'Mary', 'middle_name' => 'Frances', 'last_name' => 'Berry', 'gender' => 'Female', 'race' => 'Black', 'state' => 'District of Columbia', 'birthdate' => '1938-02-17', 'description' => 'Mary Frances Berry is a historian, lawyer, and longtime civil-rights advocate who served on the U.S. Commission on Civil Rights from 1980 to 2004 and chaired it from 1993 to 2004. President Reagan tried to fire her from the Commission for criticizing his policies; she sued and won, earning the nickname "the woman the President could not fire." On November 21, 1984 she was one of the three founders arrested at the South African embassy launching FSAM.', 'affiliation' => ['U.S. Commission on Civil Rights', 'Free South Africa Movement (founder)'], 'arrest' => '1984-11-21'],
    ['name' => 'Walter Edward Fauntroy', 'first_name' => 'Walter', 'middle_name' => 'Edward', 'last_name' => 'Fauntroy', 'gender' => 'Male', 'race' => 'Black', 'state' => 'District of Columbia', 'birthdate' => '1933-02-06', 'description' => 'Walter Fauntroy was the District of Columbia\'s first elected nonvoting delegate to the U.S. House (1971-1991) and a Baptist minister who had served as DC coordinator of the 1963 March on Washington. As a co-founder of FSAM, he was arrested in the November 21, 1984 sit-in alongside Robinson and Berry. He chaired the Congressional Black Caucus and helped shepherd the Comprehensive Anti-Apartheid Act of 1986 through the House.', 'affiliation' => ['U.S. House (DC Delegate)', 'Southern Christian Leadership Conference', 'Free South Africa Movement (founder)'], 'arrest' => '1984-11-21'],
    ['name' => 'John James Conyers Jr.', 'first_name' => 'John', 'middle_name' => 'James', 'last_name' => 'Conyers Jr.', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Michigan', 'birthdate' => '1929-05-16', 'death_date' => '2019-10-27', 'description' => 'John Conyers represented Detroit in the U.S. House for over half a century, becoming a co-founder of the Congressional Black Caucus in 1971 and the longest-serving Black member of Congress in U.S. history. He introduced the bill that became MLK Day and authored landmark voting-rights and police-accountability legislation. He was arrested at the South African embassy on November 27, 1984, six days after FSAM launched.', 'affiliation' => ['U.S. House (D-MI)', 'Congressional Black Caucus (co-founder)'], 'arrest' => '1984-11-27'],
    ['name' => 'Joseph Echols Lowery', 'first_name' => 'Joseph', 'middle_name' => 'Echols', 'last_name' => 'Lowery', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Georgia', 'birthdate' => '1921-10-06', 'death_date' => '2020-03-27', 'description' => 'Joseph Lowery co-founded the SCLC with Martin Luther King Jr. in 1957 and led the organization from 1977 to 1997. A United Methodist minister rooted in Birmingham and Atlanta, he led the 1965 Selma-to-Montgomery petition delivery and a generation later took on apartheid. He was among the first wave of FSAM arrestees on November 26, 1984. He delivered the benediction at Barack Obama\'s 2009 inauguration.', 'affiliation' => ['Southern Christian Leadership Conference (President)', 'United Methodist Church'], 'arrest' => '1984-11-26'],
    ['name' => 'Charles Arthur Hayes', 'first_name' => 'Charles', 'middle_name' => 'Arthur', 'last_name' => 'Hayes', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Illinois', 'birthdate' => '1918-02-17', 'death_date' => '1997-04-08', 'description' => 'Charles Hayes was a Chicago labor leader (United Packinghouse Workers / UFCW) who succeeded Harold Washington in the U.S. House when Washington became mayor of Chicago in 1983. A consistent foe of apartheid, he introduced sanctions legislation and was an early FSAM arrestee on November 26, 1984.', 'affiliation' => ['U.S. House (D-IL)', 'United Food and Commercial Workers'], 'arrest' => '1984-11-26'],
    ['name' => 'Ronald Vernie Dellums', 'first_name' => 'Ronald', 'middle_name' => 'Vernie', 'last_name' => 'Dellums', 'gender' => 'Male', 'race' => 'Black', 'state' => 'California', 'birthdate' => '1935-11-24', 'death_date' => '2018-07-30', 'description' => 'Ron Dellums was a self-described democratic socialist who represented Oakland and Berkeley in Congress for nearly three decades and chaired the House Armed Services Committee. He introduced the first U.S. anti-apartheid sanctions bill in 1972 and finally saw it enacted as the Comprehensive Anti-Apartheid Act of 1986, passed over President Reagan\'s veto. He was among the early FSAM arrestees at the embassy in late 1984.', 'affiliation' => ['U.S. House (D-CA)', 'Democratic Socialists of America'], 'arrest' => '1984-11-30'],
    ['name' => 'George William Crockett Jr.', 'first_name' => 'George', 'middle_name' => 'William', 'last_name' => 'Crockett Jr.', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Michigan', 'birthdate' => '1909-08-10', 'death_date' => '1997-09-07', 'description' => 'George Crockett Jr. was a pioneering civil-rights lawyer who had been jailed for contempt during the McCarthy-era Smith Act trials, served as a Detroit Recorder\'s Court judge, and won a House seat at age 70. At 75 he was arrested with Coleman Young at the South African embassy in 1984.', 'affiliation' => ['U.S. House (D-MI)', 'National Lawyers Guild (Detroit)'], 'arrest' => '1984-12-13'],
    ['name' => 'Parren James Mitchell', 'first_name' => 'Parren', 'middle_name' => 'James', 'last_name' => 'Mitchell', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Maryland', 'birthdate' => '1922-04-29', 'death_date' => '2007-05-28', 'description' => 'Parren Mitchell was Maryland\'s first Black congressman, a co-founder of the Congressional Black Caucus, and a champion of minority small-business set-asides in federal contracting. On December 3, 1984 he was arrested with Dick Gregory after they were denied entry to the South African embassy and refused to disperse, singing "We Shall Overcome" before being handcuffed.', 'affiliation' => ['U.S. House (D-MD)', 'Congressional Black Caucus (co-founder)'], 'arrest' => '1984-12-03'],
    ['name' => 'Donald LeRoy Edwards', 'first_name' => 'Donald', 'middle_name' => 'LeRoy', 'last_name' => 'Edwards', 'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'birthdate' => '1915-01-06', 'death_date' => '2015-10-01', 'description' => 'Don Edwards represented California\'s Silicon Valley in the House for 32 years and chaired the Judiciary Subcommittee on Civil and Constitutional Rights. A former FBI agent who became one of the FBI\'s sharpest congressional critics, he authored extensions of the Voting Rights Act. In December 1984 he became the first white member of Congress arrested at the South African embassy.', 'affiliation' => ['U.S. House (D-CA)'], 'arrest' => '1984-12-13'],
    ['name' => 'Lowell Palmer Weicker Jr.', 'first_name' => 'Lowell', 'middle_name' => 'Palmer', 'last_name' => 'Weicker Jr.', 'gender' => 'Male', 'race' => 'White', 'state' => 'Connecticut', 'birthdate' => '1931-05-16', 'death_date' => '2023-06-28', 'description' => 'Lowell Weicker was a maverick liberal Republican senator from Connecticut famous for his role on the Senate Watergate Committee. On January 15, 1985 he became the first U.S. Senator arrested in the FSAM campaign, declaring U.S. silence on apartheid made America "an ally of apartheid." He later left the GOP and was elected Connecticut\'s governor as an independent in 1990.', 'affiliation' => ['U.S. Senate (R-CT)'], 'arrest' => '1985-01-15'],
    ['name' => 'Coleman Alexander Young', 'first_name' => 'Coleman', 'middle_name' => 'Alexander', 'last_name' => 'Young', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Michigan', 'birthdate' => '1918-05-24', 'death_date' => '1997-11-29', 'description' => 'Coleman Young was Detroit\'s first Black mayor (1974-1994). A former Tuskegee Airman and labor organizer who had defied HUAC in the 1950s, he steered Detroit through deindustrialization. On January 7, 1985 he was arrested at the South African embassy with Marian Wright Edelman.', 'affiliation' => ['Mayor of Detroit', 'Tuskegee Airmen (former)'], 'arrest' => '1985-01-07'],
    ['name' => 'Marian Wright Edelman', 'first_name' => 'Marian', 'middle_name' => 'Wright', 'last_name' => 'Edelman', 'gender' => 'Female', 'race' => 'Black', 'state' => 'District of Columbia', 'birthdate' => '1939-06-06', 'description' => 'Marian Wright Edelman was the first Black woman admitted to the Mississippi bar, an SNCC and NAACP Legal Defense Fund attorney during Mississippi Freedom Summer, and in 1973 founded the Children\'s Defense Fund. She was arrested at the South African embassy on January 7, 1985 alongside Detroit Mayor Coleman Young.', 'affiliation' => ['Children\'s Defense Fund (founder)', 'NAACP Legal Defense Fund (former)'], 'arrest' => '1985-01-07'],
    ['name' => 'Hilda Howland Mason', 'first_name' => 'Hilda', 'middle_name' => 'Howland', 'last_name' => 'Mason', 'gender' => 'Female', 'race' => 'Black', 'state' => 'District of Columbia', 'birthdate' => '1916-06-14', 'death_date' => '2007-12-08', 'description' => 'Hilda Mason was a longtime DC Council member representing the DC Statehood Party from 1977 to 1999. She was arrested on November 28, 1984, in the first week of FSAM. Charges dropped within days.', 'affiliation' => ['DC Statehood Party', 'DC Council'], 'arrest' => '1984-11-28'],
    ['name' => 'Yolanda Denise King', 'first_name' => 'Yolanda', 'middle_name' => 'Denise', 'last_name' => 'King', 'gender' => 'Female', 'race' => 'Black', 'state' => 'Georgia', 'birthdate' => '1955-11-17', 'death_date' => '2007-05-15', 'description' => 'Yolanda King was the eldest daughter of Martin Luther King Jr. and Coretta Scott King, an actor and motivational speaker. On November 29, 1984, age 29, she was arrested at the South African embassy as her mother watched, telling reporters it was her first time in jail.', 'affiliation' => ['King Center'], 'arrest' => '1984-11-29'],
    ['name' => 'Coretta Scott King', 'first_name' => 'Coretta', 'middle_name' => 'Scott', 'last_name' => 'King', 'gender' => 'Female', 'race' => 'Black', 'state' => 'Georgia', 'birthdate' => '1927-04-27', 'death_date' => '2006-01-30', 'description' => 'Coretta Scott King was a singer, civil-rights organizer, and founder of the Atlanta-based King Center who became the public custodian of her husband\'s legacy after his 1968 assassination. On June 26, 1985 she was arrested with her son Martin III and daughter Bernice while pressing for Senate passage of the Anti-Apartheid Act.', 'affiliation' => ['King Center (founder)'], 'arrest' => '1985-06-26'],
    ['name' => 'Bernice Albertine King', 'first_name' => 'Bernice', 'middle_name' => 'Albertine', 'last_name' => 'King', 'gender' => 'Female', 'race' => 'Black', 'state' => 'Georgia', 'birthdate' => '1963-03-28', 'description' => 'Bernice King, just 22 when arrested, is the youngest of the King children and now serves as CEO of the King Center in Atlanta. She was five years old when her father was assassinated. Arrested at the South African embassy with her mother Coretta and brother Martin III on June 26, 1985.', 'affiliation' => ['King Center'], 'arrest' => '1985-06-26'],
    ['name' => 'Martin Luther King III', 'first_name' => 'Martin', 'middle_name' => 'Luther', 'last_name' => 'King III', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Georgia', 'birthdate' => '1957-10-23', 'description' => 'Martin Luther King III, second-born child of Martin and Coretta Scott King, has spent his career as a human-rights advocate and later led the SCLC (2004-2010). Arrested with his mother Coretta and sister Bernice at the South African embassy on June 26, 1985.', 'affiliation' => ['Southern Christian Leadership Conference', 'Realizing the Dream'], 'arrest' => '1985-06-26'],
    ['name' => 'Rosa Louise Parks', 'first_name' => 'Rosa', 'middle_name' => 'Louise', 'last_name' => 'Parks', 'gender' => 'Female', 'race' => 'Black', 'state' => 'Michigan', 'birthdate' => '1913-02-04', 'death_date' => '2005-10-24', 'description' => 'Rosa Parks\'s December 1, 1955 refusal to give up her seat on a Montgomery bus sparked the bus boycott that launched the modern civil-rights movement. By 1984 she lived in Detroit and worked in the office of Rep. John Conyers. On December 1, 1984 — the 29th anniversary of her Montgomery arrest — she was arrested at the South African embassy alongside Walter Fauntroy in one of the most symbolically resonant FSAM actions.', 'affiliation' => ['Civil Rights Movement', 'NAACP'], 'arrest' => '1984-12-01'],
    ['name' => 'Stevland Hardaway Morris', 'first_name' => 'Stevland', 'middle_name' => 'Hardaway', 'last_name' => 'Morris', 'aka' => 'Stevie Wonder', 'gender' => 'Male', 'race' => 'Black', 'state' => 'California', 'birthdate' => '1950-05-13', 'description' => 'Stevie Wonder is the Motown legend behind "Higher Ground," "Living for the City," and the apartheid-themed "It\'s Wrong (Apartheid)" from his 1985 album In Square Circle. Calling himself a "conscientious criminal for world equality," he chose Valentine\'s Day to be arrested. Police took him into custody on February 14, 1985 with 47 others. South Africa banned all Stevie Wonder music in retaliation.', 'affiliation' => ['Motown'], 'arrest' => '1985-02-14'],
    ['name' => 'Harry Belafonte', 'first_name' => 'Harold', 'middle_name' => 'George', 'last_name' => 'Belafonte Jr.', 'aka' => 'Harry Belafonte', 'gender' => 'Male', 'race' => 'Black', 'state' => 'New York', 'birthdate' => '1927-03-01', 'death_date' => '2023-04-25', 'description' => 'Harry Belafonte was an EGOT-winning entertainer who funded much of the civil-rights movement, helped organize "We Are the World" and the 1985 "Sun City" artist boycott, and served on TransAfrica\'s board. He was arrested at the South African embassy in 1985.', 'affiliation' => ['TransAfrica (board)', 'Civil Rights Movement'], 'arrest' => '1985-04-15'],
    ['name' => 'Arthur Robert Ashe Jr.', 'first_name' => 'Arthur', 'middle_name' => 'Robert', 'last_name' => 'Ashe Jr.', 'gender' => 'Male', 'race' => 'Black', 'state' => 'New York', 'birthdate' => '1943-07-10', 'death_date' => '1993-02-06', 'description' => 'Arthur Ashe was the first Black man to win the U.S. Open, the Australian Open, and Wimbledon, and a tireless campaigner against apartheid in international sport. South Africa had repeatedly denied him a visa in the 1970s. He was arrested at the South African embassy on January 11, 1985 with sixteen other demonstrators.', 'affiliation' => ['Tennis'], 'arrest' => '1985-01-11'],
    ['name' => 'Amy Lynn Carter', 'first_name' => 'Amy', 'middle_name' => 'Lynn', 'last_name' => 'Carter', 'gender' => 'Female', 'race' => 'White', 'state' => 'Georgia', 'birthdate' => '1967-10-19', 'description' => 'Amy Carter was 17 and the daughter of former President Jimmy Carter when she joined an FSAM sit-in. She telephoned her father for permission, then marched up to the embassy door singing "We Shall Overcome." Arrested April 8, 1985 with 14 others, she went to trial and the entire group was acquitted.', 'affiliation' => ['Carter family'], 'arrest' => '1985-04-08', 'sentence_override' => 'Acquitted at trial.', 'convicted_override' => 'No — acquitted at trial'],
    ['name' => 'Jesse Louis Jackson Sr.', 'first_name' => 'Jesse', 'middle_name' => 'Louis', 'last_name' => 'Jackson Sr.', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Illinois', 'birthdate' => '1941-10-08', 'death_date' => '2025-09-21', 'description' => 'Jesse Jackson was a young aide to Martin Luther King Jr. who founded Operation PUSH in 1971 and the Rainbow Coalition in 1984, running competitive Democratic presidential primary campaigns in 1984 and 1988. A member of the FSAM steering committee, he was arrested with his sons Jesse Jr. and Jonathan at the South African embassy on March 11, 1985 after standing on the embassy steps in the rain singing "We Shall Overcome."', 'affiliation' => ['Operation PUSH', 'Rainbow Coalition'], 'arrest' => '1985-03-11'],
    ['name' => 'Richard Claxton Gregory', 'first_name' => 'Richard', 'middle_name' => 'Claxton', 'last_name' => 'Gregory', 'aka' => 'Dick Gregory', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Massachusetts', 'birthdate' => '1932-10-12', 'death_date' => '2017-08-19', 'description' => 'Dick Gregory was the breakthrough Black stand-up comedian of the 1960s who left the nightclub circuit for full-time activism, undertaking long hunger strikes for civil rights, peace, and prison reform. On December 3, 1984 he was arrested with Rep. Parren Mitchell and DC labor leader Joslyn Williams at the South African embassy.', 'affiliation' => ['Civil Rights Movement'], 'arrest' => '1984-12-03'],
    ['name' => 'Gloria Marie Steinem', 'first_name' => 'Gloria', 'middle_name' => 'Marie', 'last_name' => 'Steinem', 'gender' => 'Female', 'race' => 'White', 'state' => 'New York', 'birthdate' => '1934-03-25', 'description' => 'Gloria Steinem was the most visible American feminist of her generation, co-founder of Ms. magazine (1972) and the National Women\'s Political Caucus. She was arrested at the South African embassy on December 19, 1984 alongside Rev. Rollins Lambert of the U.S. Catholic Conference and DC Health Commissioner Andrew McBride.', 'affiliation' => ['Ms. magazine (co-founder)', 'National Women\'s Political Caucus'], 'arrest' => '1984-12-19'],
    ['name' => 'Larry Holmes', 'first_name' => 'Larry', 'last_name' => 'Holmes', 'aka' => 'The Easton Assassin', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Pennsylvania', 'birthdate' => '1949-11-03', 'description' => 'Larry Holmes was world heavyweight boxing champion when he was arrested at the South African embassy in February 1985, having long refused lucrative offers to fight in apartheid South Africa.', 'affiliation' => ['Boxing'], 'arrest' => '1985-02-21'],
    ['name' => 'Anthony Leonard Randall', 'first_name' => 'Anthony', 'middle_name' => 'Leonard', 'last_name' => 'Randall', 'aka' => 'Tony Randall', 'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'birthdate' => '1920-02-26', 'death_date' => '2004-05-17', 'description' => 'Tony Randall was the Emmy-winning actor best known for playing Felix Unger on The Odd Couple (1970-1975) and a longtime liberal-cause supporter. He was arrested in early 1985 outside the South African embassy in one of the celebrity-led waves of FSAM civil disobedience.', 'affiliation' => ['Acting', 'National Actors Theatre (founder)'], 'arrest' => '1985-01-11'],
];

foreach ($fsam as $row) {
    $person = [
        'name' => $row['name'], 'first_name' => $row['first_name'], 'middle_name' => $row['middle_name'] ?? null,
        'last_name' => $row['last_name'], 'aka' => $row['aka'] ?? null,
        'gender' => $row['gender'], 'race' => $row['race'], 'state' => $row['state'],
        'birthdate' => $row['birthdate'] ?? null, 'death_date' => $row['death_date'] ?? null,
        'description' => $row['description'], 'affiliation' => $row['affiliation'],
        'ideologies' => ['Anti-apartheid', 'Civil rights', 'Anti-imperialism'],
        'era' => '1980s', 'in_custody' => false, 'released' => true,
    ];
    [$prisoner, $created] = upsertPrisoner($person);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$row['name']} (id={$prisoner->id})\n";
    $created ? $totals['created']++ : $totals['existed']++;

    $case = [
        'institution' => $fsamInstitution,
        'arrest_date' => $row['arrest'],
        'sentence' => $row['sentence_override'] ?? $fsamSentence,
        'convicted' => $row['convicted_override'] ?? 'No — charges declined / dropped',
        'charges' => $fsamCharges,
    ];
    if (attachCase($prisoner, $case)) {
        echo "    + case (arrest_date={$case['arrest_date']})\n";
        $totals['cases']++;
    }
}

echo "\n";
echo "Done.\n";
echo "  prisoners created:        {$totals['created']}\n";
echo "  prisoners already existed: {$totals['existed']}\n";
echo "  cases added:              {$totals['cases']}\n";
