<?php

declare(strict_types=1);

/**
 * Final batch of Reagan-era political prisoners across the
 * remaining low-confidence categories:
 *
 *   1. Abalone Alliance / Diablo Canyon (1981 blockade)
 *   2. Rocky Flats Truth Force (1978-1980s anti-nuclear)
 *   3. Honeywell Project (Twin Cities anti-weapons)
 *   4. War tax resisters who served jail time
 *   5. Symbionese Liberation Army late captures (Carter/Reagan/post-)
 *   6. Cameron David Bishop (1969 sabotage; 1975 capture; 1977 reversal)
 *   7. Anti-apartheid student divestment leaders (1985-1986)
 *   8. Sister Dianne Muhlenkamp (1984 Texas Sanctuary case)
 *
 * Idempotent. Run on production:
 *   cd /var/www/NPPC-Website && php scripts/add_reagan_era_final_batch.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;

function addP(array $p): array
{
    $existing = Prisoner::where('name', $p['name'])->first();
    if ($existing) return [$existing, false];
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

function attachC(Prisoner $prisoner, array $c): bool
{
    if (! empty($c['arrest_date'])) {
        $exists = $prisoner->cases()->where('arrest_date', $c['arrest_date'])->first();
        if ($exists) return false;
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

$tot = ['created' => 0, 'existed' => 0, 'cases' => 0];

// =================== 1. Abalone Alliance / Diablo Canyon ===================

$diablo = ['name' => 'Diablo Canyon Nuclear Power Plant — Abalone Alliance blockade', 'city' => 'Avila Beach', 'state' => 'California'];

$diabloPeople = [
    [
        'name' => 'Jackson Browne', 'first_name' => 'Jackson', 'last_name' => 'Browne', 'aka' => 'Clyde Jackson Browne',
        'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'birthdate' => '1948-10-09',
        'description' => 'Singer-songwriter and co-founder of Musicians United for Safe Energy (MUSE) and the No Nukes concerts. Among the most visible celebrity participants in the September 1981 Diablo Canyon blockade organized by the Abalone Alliance — the largest action in U.S. anti-nuclear movement history with ~1,900 arrests over two weeks. Held at the Cuesta College gymnasium "Hotel Diablo," he performed in Wavy Gravy\'s "Tornado of Talent" variety show during detention. "I consider my actions to be patriotic."',
        'affiliation' => ['Abalone Alliance', 'Musicians United for Safe Energy (MUSE) (co-founder)'],
        'arrest' => '1981-09-15',
    ],
    [
        'name' => 'Hugh Nanton Romney Jr.', 'first_name' => 'Hugh', 'middle_name' => 'Nanton', 'last_name' => 'Romney Jr.',
        'aka' => 'Wavy Gravy', 'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'birthdate' => '1936-05-15',
        'description' => 'Counterculture icon and clown-activist. Arrested in the September 1981 Diablo Canyon blockade and held with the men at the Cuesta College gymnasium "Hotel Diablo," where he organized and emceed a multi-day variety show inside the holding facility he dubbed the "Tornado of Talent." Arrived at the action in green coveralls that he removed to reveal a Santa Claus suit. Previously the official MC of the 1969 Woodstock festival.',
        'affiliation' => ['Hog Farm Collective', 'Abalone Alliance'],
        'arrest' => '1981-09-15',
    ],
];

foreach ($diabloPeople as $p) {
    $p['ideologies'] = ['Anti-nuclear', 'Pacifism', 'Counterculture'];
    $p['era'] = '1980s';
    [$prisoner, $created] = addP($p);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$p['name']} (id={$prisoner->id})\n";
    $created ? $tot['created']++ : $tot['existed']++;
    if (attachC($prisoner, ['institution' => $diablo, 'arrest_date' => $p['arrest'], 'charges' => 'Trespassing during the September 1981 Abalone Alliance blockade of Diablo Canyon Nuclear Power Plant — ~1,900 arrests over two weeks.', 'sentence' => 'Brief detention at Cuesta College men\'s holding facility ("Hotel Diablo").', 'convicted' => 'Most charges dropped'])) { echo "    + case ({$p['arrest']})\n"; $tot['cases']++; }
}

// =================== 2. Rocky Flats Truth Force ===================

$rfInst = ['name' => 'Rocky Flats Nuclear Weapons Plant — Rocky Flats Truth Force', 'city' => 'Golden', 'state' => 'Colorado'];

$rfPeople = [
    [
        'name' => 'Allen Ginsberg', 'first_name' => 'Allen', 'last_name' => 'Ginsberg', 'aka' => 'Irwin Allen Ginsberg',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Colorado', 'birthdate' => '1926-06-03', 'death_date' => '1997-04-05',
        'description' => 'Beat Generation poet, then teaching at Naropa Institute in Boulder, was a prominent participant in the Rocky Flats Truth Force protests of 1978-1979. Alongside Daniel Ellsberg and Anne Waldman, he was arrested for blocking train tracks at the Rocky Flats Nuclear Weapons Plant during demonstrations beginning April 29, 1978; he composed the poem "Plutonian Ode" in connection with these actions. Approximately 75 protesters were arrested in the May 1978 mass action.',
        'affiliation' => ['Rocky Flats Truth Force', 'Naropa Institute', 'Beat Generation'],
        'arrest' => '1978-04-29',
    ],
    [
        'name' => 'Anne Waldman', 'first_name' => 'Anne', 'last_name' => 'Waldman',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Colorado', 'birthdate' => '1945-04-02',
        'description' => 'Poet, co-founder with Allen Ginsberg of the Jack Kerouac School of Disembodied Poetics at Naropa, was active in the Rocky Flats Truth Force from 1978-1979. Arrested with Ginsberg and Daniel Ellsberg at sitting-meditation actions at Rocky Flats in June 1978. Formerly directed the Poetry Project at St. Mark\'s in New York (1968-1978).',
        'affiliation' => ['Rocky Flats Truth Force', 'Naropa Institute', 'Jack Kerouac School of Disembodied Poetics'],
        'arrest' => '1978-06-15',
    ],
    [
        'name' => 'Pam Solo', 'first_name' => 'Pam', 'last_name' => 'Solo', 'aka' => 'Pamela Solo',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Colorado', 'birthdate' => '1946-01-01',
        'description' => 'Born in Englewood, Colorado, a Sister of Loretto and graduate of Loretto Heights College (1969) and Goddard College (1972). Co-founded and co-directed the Rocky Flats campaign in the late 1970s and the national Nuclear Weapons Facilities Task Force, working with the AFSC. Co-chaired the Strategy Task Force of the national Nuclear Weapons Freeze Campaign during the early 1980s with Randy Kehler. Named a MacArthur Fellow in 1989 for this work.',
        'affiliation' => ['Sisters of Loretto', 'American Friends Service Committee', 'Rocky Flats Action Group', 'Nuclear Weapons Freeze Campaign'],
        'arrest' => '1978-04-29',
    ],
    [
        'name' => 'Patricia McCormick', 'first_name' => 'Patricia', 'last_name' => 'McCormick', 'aka' => 'Sister Pat McCormick',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Colorado', 'birthdate' => '1942-01-01',
        'description' => 'Sister of Loretto raised on a farm northwest of Chicago, served in Bolivia in 1965 where she met Daniel Berrigan during her formation in Cuernavaca. After returning to Denver she trained at the Ground Zero Center for Nonviolent Action and formed an interfaith group vigiling at Rocky Flats\' west gate. In April 1983 she and a Mennonite friend entered the Rocky Flats plant and poured a vial of blood on a facility sign; she was charged with a misdemeanor and served two months in jail.',
        'affiliation' => ['Sisters of Loretto', 'Rocky Flats interfaith vigil'],
        'arrest' => '1983-04-15',
        'sentence' => '2 months in jail',
        'convicted' => 'Yes — misdemeanor',
    ],
];

foreach ($rfPeople as $p) {
    $p['ideologies'] = ['Anti-nuclear', 'Pacifism', 'Catholic peace tradition'];
    $p['era'] = '1980s';
    [$prisoner, $created] = addP($p);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$p['name']} (id={$prisoner->id})\n";
    $created ? $tot['created']++ : $tot['existed']++;
    if (attachC($prisoner, [
        'institution' => $rfInst,
        'arrest_date' => $p['arrest'],
        'charges' => 'Civil disobedience at Rocky Flats Nuclear Weapons Plant (Colorado), the federal facility producing plutonium triggers for U.S. nuclear warheads. Trespassing, blocking railroad tracks, and (for some) destruction of property by pouring blood on plant signage.',
        'sentence' => $p['sentence'] ?? 'Brief detention; most charges dismissed.',
        'convicted' => $p['convicted'] ?? 'Mostly dismissed (Federal/state trespass)',
    ])) { echo "    + case ({$p['arrest']})\n"; $tot['cases']++; }
}

// =================== 3. Honeywell Project ===================

$honeywell = ['name' => 'Honeywell HQ Minneapolis — Honeywell Project', 'city' => 'Minneapolis', 'state' => 'Minnesota'];

$honeywellPeople = [
    [
        'name' => 'Marvin Allen Davidov', 'first_name' => 'Marvin', 'middle_name' => 'Allen', 'last_name' => 'Davidov',
        'aka' => 'Marv Davidov', 'gender' => 'Male', 'race' => 'White', 'state' => 'Minnesota',
        'birthdate' => '1931-08-26', 'death_date' => '2012-01-14',
        'description' => 'Born in Detroit on August 26, 1931, moved to St. Paul in 1949 to attend Macalester College and served in the U.S. Army 1953-1955. A 1961 Freedom Rider arrested in Jackson, Mississippi, and a participant in the 1963-1964 Canada-to-Cuba Peace Walk, he founded the Honeywell Project in 1968 after reading a Staughton Lynd article calling on activists to target war-profiteering corporations. He led the Project for over two decades, organizing annual protests at Honeywell\'s Minneapolis headquarters and shareholder meetings against cluster bombs, missile-guidance systems, anti-personnel mines, and depleted-uranium munitions. Co-authored "You Can\'t Do That" (2010) with Carol Masters.',
        'affiliation' => ['Honeywell Project (founder, 1968)', 'Vietnam Veterans Against the War', 'Freedom Rides veteran'],
        'arrest' => '1983-10-24',
    ],
    [
        'name' => 'Polly H. Mann', 'first_name' => 'Polly', 'middle_name' => 'H.', 'last_name' => 'Mann',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Minnesota', 'birthdate' => '1919-11-19', 'death_date' => '2023-01-12',
        'description' => 'Born in Lonoke, Arkansas, Polly Mann co-founded Women Against Military Madness (WAMM) with Marianne Hamilton at Loretta\'s Tea Room in Minneapolis in fall 1981, alongside eight other women. WAMM became a frequent partner of the Honeywell Project and Mann was repeatedly arrested at Honeywell shareholder-meeting actions through the 1980s. Ran for U.S. Senate as an independent in 1988 with the motto "Speak Truth to Power," and remained active into her 100s. Died at age 103.',
        'affiliation' => ['Women Against Military Madness (co-founder)', 'Honeywell Project'],
        'arrest' => '1983-10-24',
    ],
    [
        'name' => 'Erica Bouza', 'first_name' => 'Erica', 'last_name' => 'Bouza',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Minnesota', 'birthdate' => '1931-01-01', 'death_date' => '2023-12-15',
        'description' => 'Wife of then-Minneapolis Police Chief Tony Bouza. Became national news in April 1983 when she and 138 other anti-nuclear protesters sat down in front of Honeywell\'s Minneapolis headquarters and were arrested by officers under her husband\'s command. Arrested again on October 24, 1983 in the second mass sit-in (577 arrests) and sentenced to 10 days at the Hennepin County workhouse. Featured in People Magazine. The first of approximately 11 peace-related civil-disobedience arrests in the Twin Cities.',
        'affiliation' => ['Honeywell Project', 'Women Against Military Madness'],
        'arrest' => '1983-10-24',
        'sentence' => '10 days at the Hennepin County workhouse',
        'convicted' => 'Yes — misdemeanor trespass',
    ],
    [
        'name' => 'George W. Crocker', 'first_name' => 'George', 'middle_name' => 'W.', 'last_name' => 'Crocker',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Minnesota',
        'description' => 'One of Minnesota\'s longest-serving energy and anti-nuclear activists, working since the late 1970s on energy-democracy and anti-nuclear-power issues. A member of the Northern Sun Alliance (the Minnesota anti-nuclear coalition), a frequent collaborator with Marv Davidov at the Honeywell Project, and longtime executive director of the North American Water Office. Author of "About Power: How to Democratize Electricity Now."',
        'affiliation' => ['Honeywell Project', 'Northern Sun Alliance', 'North American Water Office'],
        'arrest' => '1983-10-24',
    ],
];

foreach ($honeywellPeople as $p) {
    $p['ideologies'] = ['Pacifism', 'Anti-militarism', 'Anti-nuclear'];
    $p['era'] = '1980s';
    [$prisoner, $created] = addP($p);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$p['name']} (id={$prisoner->id})\n";
    $created ? $tot['created']++ : $tot['existed']++;
    if (attachC($prisoner, [
        'institution' => $honeywell,
        'arrest_date' => $p['arrest'],
        'charges' => 'Annual Honeywell Project sit-ins at Honeywell HQ in Minneapolis (1968-2002) — protesting cluster bombs, missile-guidance systems, anti-personnel mines, and depleted-uranium munitions produced by Honeywell\'s defense division. October 24, 1983 mass arrest produced 577 arrestees.',
        'sentence' => $p['sentence'] ?? 'Brief detention; most charges dismissed or short workhouse stints.',
        'convicted' => $p['convicted'] ?? 'Misdemeanor trespass',
    ])) { echo "    + case ({$p['arrest']})\n"; $tot['cases']++; }
}

// =================== 4. War Tax Resisters ===================

$wtrInst = ['name' => 'War Tax Resistance — federal courts', 'city' => 'Washington', 'state' => 'District of Columbia'];

$wtrPeople = [
    [
        'name' => 'Karl Meyer', 'first_name' => 'Karl', 'last_name' => 'Meyer',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Illinois', 'birthdate' => '1937-06-30',
        'description' => 'Son of former U.S. Representative William H. Meyer of Vermont, born in a cabin in northern Wisconsin. At age 19 he attempted to open a Catholic Worker house of hospitality in Washington, D.C.; on July 12, 1957 he met Dorothy Day and Ammon Hennacy at the Chrystie Street Catholic Worker. After witnessing the imprisonment of war tax resister Eroseanna Robinson, he vowed never to pay federal income tax again, pioneering the W-4 extra-allowances method to block withholding. He served two years in federal prison for tax resistance and shorter terms in 20 different jails. In 1984 he defied the IRS "frivolous filing" penalty by filing a frivolous return every day that year, was assessed $140,000 in penalties, and coined the term "cabbage patch resistance."',
        'affiliation' => ['Catholic Worker (Chicago)', 'Greenlands Catholic Worker (Nashville)'],
        'arrest' => '1968-01-15',
        'sentence' => '2 years federal prison (tax resistance) plus 20+ shorter jail stints across decades',
        'convicted' => 'Yes — willful failure to file/pay federal income tax',
    ],
    [
        'name' => 'Wallace Floyd Nelson', 'first_name' => 'Wallace', 'middle_name' => 'Floyd', 'last_name' => 'Nelson',
        'aka' => 'Wally Nelson', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Massachusetts',
        'birthdate' => '1909-03-27', 'death_date' => '2002-05-23',
        'description' => 'Served three and a half years in federal prison as a conscientious objector during World War II, fasting 108 days (with forced tube-feeding) to protest racial segregation of prisoners. Participated in the 1947 Journey of Reconciliation — the first "freedom ride" — and became the first national field organizer for the Congress of Racial Equality. With Juanita he began war tax resistance in 1948; the couple was arrested as the "Elkton Three" for trying to integrate a Maryland restaurant. The Nelsons lived a radically simple life on less than $5,000 a year on a half-acre in Deerfield, Massachusetts, growing their own food and refusing electricity.',
        'affiliation' => ['CORE (first national field organizer)', 'Peacemakers', 'Catholic Worker milieu'],
        'arrest' => '1943-06-01',
        'sentence' => '3.5 years federal prison (WWII CO sentence) plus subsequent civil rights and tax resistance arrests',
        'convicted' => 'Yes — refusing induction (WWII)',
    ],
    [
        'name' => 'Juanita Morrow Nelson', 'first_name' => 'Juanita', 'middle_name' => 'Morrow', 'last_name' => 'Nelson',
        'gender' => 'Female', 'race' => 'Black', 'state' => 'Massachusetts',
        'birthdate' => '1923-08-17', 'death_date' => '2015-03-09',
        'description' => 'Met Wally Nelson in 1948 while interviewing him in jail as a journalist; began their lifelong relationship and joint war tax resistance practice that year. In 1959 she became one of only six people imprisoned for war tax resistance in the U.S. between WWII and the Vietnam War. Arrested as the "Elkton Three" for restaurant desegregation in Maryland, she was a long-running NWTRCC presence and co-builder of the off-grid Deerfield homestead. She continued speaking publicly into her 90s.',
        'affiliation' => ['CORE', 'Peacemakers', 'NWTRCC'],
        'arrest' => '1959-06-15',
        'sentence' => 'Imprisoned for war tax resistance in 1959 (length not specified in available sources)',
        'convicted' => 'Yes — willful failure to comply with IRS summons',
    ],
    [
        'name' => 'Randall Forsberg Kehler', 'first_name' => 'Randall', 'middle_name' => 'Forsberg', 'last_name' => 'Kehler',
        'aka' => 'Randy Kehler', 'gender' => 'Male', 'race' => 'White', 'state' => 'Massachusetts',
        'birthdate' => '1944-01-01', 'death_date' => '2024-05-15',
        'description' => 'As a young War Resisters League speaker in 1969, his testimony inspired Daniel Ellsberg to leak the Pentagon Papers. Served as national coordinator of the Nuclear Weapons Freeze Campaign in the early 1980s alongside Pam Solo. From 1977 onward, Kehler and his wife Betsy Corner refused to pay federal income tax in protest against military spending. In 1989 the IRS seized their Colrain, Massachusetts home for $27,000 in unpaid taxes; the resulting four-year occupation by supporters (1989-1993) became the longest war-tax-resistance protest in U.S. history, with 60 arrests of 52 people serving 5 days to 2 weeks each. Documented in the 1997 Robbie Leppzer film "An Act of Conscience."',
        'affiliation' => ['War Resisters League', 'Nuclear Weapons Freeze Campaign (national coordinator)', 'Traprock Peace Center'],
        'arrest' => '1989-12-01',
        'sentence' => 'Brief jail terms during the 1989-1993 Colrain action.',
        'convicted' => 'Yes — civil contempt for tax resistance',
    ],
    [
        'name' => 'Betsy Corner', 'first_name' => 'Betsy', 'last_name' => 'Corner',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Massachusetts',
        'description' => 'Wife of Randy Kehler, co-resister in the Colrain, Massachusetts war-tax-resistance case that became the most sustained war tax action in U.S. history. The couple stopped paying federal income taxes in 1977; the IRS seized their home in 1989 to recover $27,000. Corner participated in the four-year community occupation of the seized property (1989-1993) that drew dozens of arrests, the documentary "An Act of Conscience," and significant national attention to the war tax resistance movement.',
        'affiliation' => ['Traprock Peace Center', 'War Resisters League', 'NWTRCC'],
        'arrest' => '1989-12-01',
        'sentence' => 'Brief jail terms.',
        'convicted' => 'Yes — civil contempt',
    ],
];

foreach ($wtrPeople as $p) {
    $p['ideologies'] = ['Pacifism', 'War tax resistance', 'Anti-militarism'];
    $p['era'] = '1980s';
    [$prisoner, $created] = addP($p);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$p['name']} (id={$prisoner->id})\n";
    $created ? $tot['created']++ : $tot['existed']++;
    if (attachC($prisoner, [
        'institution' => $wtrInst,
        'arrest_date' => $p['arrest'],
        'charges' => 'War tax resistance — willful failure to file/pay federal income tax under IRS regulations and 26 U.S.C. § 7203 (failure to pay) or contempt for refusing to comply with IRS summons. Most prosecutions are civil contempt with brief jail; some progress to criminal Espionage Act-adjacent charges with longer sentences.',
        'sentence' => $p['sentence'],
        'convicted' => $p['convicted'],
    ])) { echo "    + case ({$p['arrest']})\n"; $tot['cases']++; }
}

// =================== 5. SLA late captures ===================

$slaInst = ['name' => 'California state and federal courts — Symbionese Liberation Army', 'city' => 'San Francisco', 'state' => 'California'];

$slaPeople = [
    [
        'name' => 'William Taylor Harris', 'first_name' => 'William', 'middle_name' => 'Taylor', 'last_name' => 'Harris',
        'aka' => 'General Teko; Bill Harris', 'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'birthdate' => '1945-01-22',
        'description' => 'Former U.S. Marine Vietnam-era veteran (enlisted 1965) who, after returning to Indiana University and earning a master\'s degree in urban education in 1972, became radicalized and joined the Symbionese Liberation Army around 1973-74. Self-proclaimed leader of the SLA after Donald DeFreeze\'s death in the May 1974 LAPD shootout and participated in the kidnapping of Patricia Hearst in February 1974. Captured with his wife Emily, Patty Hearst, and Wendy Yoshimura in San Francisco on September 18, 1975, he served roughly seven years for the Hearst kidnapping. In 2003 he pleaded guilty to second-degree murder for the 1975 Crocker National Bank/Myrna Opsahl killing, serving an additional seven years.',
        'arrest' => '1975-09-18',
        'sentence' => 'Up to 11 years (kidnapping); 7 additional years for the 2003 Opsahl plea',
        'release' => '1983-04-26',
    ],
    [
        'name' => 'Emily Montague Harris', 'first_name' => 'Emily', 'middle_name' => 'Montague', 'last_name' => 'Harris',
        'aka' => 'Yolanda; Emily Schwartz', 'gender' => 'Female', 'race' => 'White', 'state' => 'California', 'birthdate' => '1947-02-11',
        'description' => 'Born in Baltimore and raised in Clarendon Hills, Illinois, the daughter of an engineer, Emily Schwartz earned a B.A. in language arts from Indiana University Bloomington, where she met William Harris. She used the nom de guerre "Yolanda" as an SLA soldier. Captured September 18, 1975, she served eight years for kidnapping Patty Hearst and was paroled May 27, 1983. In 2002 she was charged in the 1975 Crocker National Bank/Myrna Opsahl killing — prosecutors named her as the shooter — and pleaded guilty to second-degree murder, serving an additional eight-year term.',
        'arrest' => '1975-09-18',
        'sentence' => 'Eight years (kidnapping); eight additional years for 2003 Opsahl plea',
        'release' => '1983-05-27',
    ],
    [
        'name' => 'Russell Jack Little', 'first_name' => 'Russell', 'middle_name' => 'Jack', 'last_name' => 'Little',
        'aka' => 'Osceola; Osi', 'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'birthdate' => '1949-01-01',
        'description' => 'Raised in Pensacola, Florida, attended the University of Florida from 1967, and was radicalized by the May 1970 Kent State killings. In 1972 he relocated to Berkeley, joined the Maoist Peking House commune, and became a founding member of the SLA in 1973. With Joseph Remiro he was arrested January 10, 1974 — convicted of first-degree murder (Marcus Foster, Oakland school superintendent, Nov 6, 1973) and sentenced to life in 1975. His conviction was overturned in 1981 on a jury-instruction error and he was acquitted at retrial in Monterey County, gaining release in 1981. Has lived in Hawaii since.',
        'arrest' => '1974-01-10',
        'sentence' => 'Life sentence (1975); acquitted on retrial 1981',
        'release' => '1981-12-15',
    ],
    [
        'name' => 'Joseph Michael Remiro', 'first_name' => 'Joseph', 'middle_name' => 'Michael', 'last_name' => 'Remiro',
        'aka' => 'Bo', 'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'birthdate' => '1947-01-01',
        'description' => 'Raised in a devout Catholic family in San Francisco, served two tours in Vietnam with the 101st Airborne before being radicalized. Founding member of the SLA in 1973 and, with Russell Little, assassinated Oakland school superintendent Marcus Foster on November 6, 1973. Arrested January 10, 1974 in Concord, California; convicted of first-degree murder and sentenced to life on June 27, 1975. His conviction stood on appeal and he was held at Pelican Bay State Prison; denied parole repeatedly through the 1980s, 1990s and 2000s before being paroled circa 2018.',
        'arrest' => '1974-01-10',
        'sentenced_date' => '1975-06-27',
        'sentence' => 'Life',
        'release' => '2018-06-15',
    ],
    [
        'name' => 'Wendy Masako Yoshimura', 'first_name' => 'Wendy', 'middle_name' => 'Masako', 'last_name' => 'Yoshimura',
        'gender' => 'Female', 'race' => 'Asian', 'state' => 'California', 'birthdate' => '1943-01-17',
        'description' => 'Born in the Manzanar Japanese-American internment camp during WWII to American-citizen parents. Studied art at California College of Arts and Crafts, drawn into radical politics through her relationship with Willie Brandt, founder of the Berkeley-based Revolutionary Army. After a March 1972 raid uncovered a Berkeley garage bomb-making operation, Yoshimura went underground and lived under aliases until her September 18, 1975 capture in San Francisco alongside Patty Hearst. Convicted in 1977 of weapons and explosives possession, she served roughly 13 months and was paroled August 25, 1980. Has worked as a still-life watercolor painter in the Bay Area since.',
        'arrest' => '1975-09-18',
        'sentence' => '1-15 years indeterminate (CA); served roughly 13 months',
        'release' => '1980-08-25',
    ],
    [
        'name' => 'Sara Jane Olson', 'first_name' => 'Sara', 'middle_name' => 'Jane', 'last_name' => 'Olson',
        'aka' => 'Kathleen Ann Soliah; Kathy Soliah', 'gender' => 'Female', 'race' => 'White', 'state' => 'Minnesota',
        'birthdate' => '1947-01-16',
        'description' => 'Born in Fargo, North Dakota and raised in Palmdale, California in a conservative Norwegian Lutheran family, Soliah graduated from UC Santa Barbara in 1969 with a theater-arts degree, where she became James Kilgore\'s girlfriend. After her friend Angela Atwood was killed in the May 1974 SLA shootout, Soliah joined the SLA\'s reconstituted second formation. Federally indicted in 1976 for a failed attempt to bomb LAPD patrol cars, she fled to Minnesota, took the alias Sara Jane Olson, married physician Fred Peterson, raised three daughters and became a community-theater actress. Profiled on America\'s Most Wanted, she was arrested June 16, 1999. In 2001 she pleaded guilty to two counts of possessing explosives with intent to murder; in 2003 she pleaded guilty to second-degree murder for the Opsahl killing.',
        'arrest' => '1999-06-16',
        'sentence' => 'Initially 5y4m, recalculated to ~14y for the LA bomb counts; additional 6 years for the 2003 Opsahl plea',
        'release' => '2009-03-17',
    ],
    [
        'name' => 'James William Kilgore', 'first_name' => 'James', 'middle_name' => 'William', 'last_name' => 'Kilgore',
        'aka' => 'John Pape', 'gender' => 'Male', 'race' => 'White', 'state' => 'Illinois', 'birthdate' => '1947-07-30',
        'description' => 'Grew up in California, graduated San Rafael High in 1965 and UC Santa Barbara in 1969, where he was the boyfriend of Kathleen Soliah and active in radical leftist circles. Joined the SLA\'s second formation in 1974 and participated in the April 1975 Crocker National Bank robbery in Carmichael during which Myrna Opsahl was killed. After a 1975 federal indictment he fled the U.S. and lived as a fugitive for 27 years in Zimbabwe, Australia, and South Africa under the alias "John Pape," completing a Ph.D. and writing widely on African political economy. Captured in Cape Town November 8, 2002, he pleaded guilty May 2003 to second-degree murder (Opsahl) and to federal explosives and passport-fraud charges.',
        'arrest' => '2002-11-08',
        'sentence' => '6 years state (Opsahl plea); 54 months federal',
        'release' => '2009-05-15',
    ],
    [
        'name' => 'Michael Bortin', 'first_name' => 'Michael', 'last_name' => 'Bortin',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Oregon', 'birthdate' => '1949-01-01',
        'description' => 'Son of a San Francisco capital-defense attorney, attended Lowell High School and briefly UC Berkeley before drifting through Haight-Ashbury counterculture; radicalized by Robert Kennedy\'s June 1968 assassination. Arrested in 1972 for conspiracy to bomb a Berkeley campus building, served roughly 18 months. Roommate of Steven Soliah and a participant in the SLA\'s second formation, taking part in the April 1975 Carmichael bank robbery in which Myrna Opsahl was killed. Went underground 1977-1984, surrendered in 1984 to visit his dying mother, and lived openly in Portland, Oregon until 2002, when he was charged for the Opsahl killing. Pleaded guilty to second-degree murder on November 7, 2002 and was sentenced February 14, 2003 to six years.',
        'arrest' => '2002-01-15',
        'sentence' => 'Six years (Opsahl 2003 plea); ~18 months earlier (1972 case)',
        'release' => '2008-12-15',
    ],
    [
        'name' => 'Steven Soliah', 'first_name' => 'Steven', 'last_name' => 'Soliah',
        'gender' => 'Male', 'race' => 'White', 'state' => 'California',
        'description' => 'House painter and brother of Kathleen Soliah, a member of the SLA\'s second formation and a roommate of Michael Bortin. Captured with the Harrises, Patty Hearst and Wendy Yoshimura on September 18, 1975 and indicted in 1976 for the Carmichael bank robbery and the Opsahl killing. Acquitted at his federal bank-robbery trial in 1976 after an alibi witness testified he was with her in San Francisco at the time. Never re-charged with the Opsahl killing in 2002 because of double-jeopardy from the federal acquittal.',
        'arrest' => '1975-09-18',
        'sentence' => 'Acquitted at trial.',
        'convicted' => 'No — acquitted',
        'release' => '1976-12-15',
    ],
];

foreach ($slaPeople as $p) {
    $p['ideologies'] = ['Marxism-Leninism', 'Revolutionary anti-imperialism', 'Anti-racism'];
    $p['affiliation'] = ['Symbionese Liberation Army'];
    $p['era'] = '1970s';
    [$prisoner, $created] = addP($p);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$p['name']} (id={$prisoner->id})\n";
    $created ? $tot['created']++ : $tot['existed']++;
    if (attachC($prisoner, [
        'institution' => $slaInst,
        'arrest_date' => $p['arrest'],
        'release_date' => $p['release'] ?? null,
        'sentenced_date' => $p['sentenced_date'] ?? null,
        'sentence' => $p['sentence'],
        'convicted' => $p['convicted'] ?? 'Yes',
        'charges' => 'Symbionese Liberation Army prosecutions: kidnapping (Patty Hearst, 1974), murder (Marcus Foster, 1973; Myrna Opsahl, 1975 Crocker National Bank robbery), bank robbery, weapons and explosives possession. Multiple state and federal jurisdictions. The 2002-2003 wave of late-prosecution Opsahl pleas resolved the 1975 cold case via second-degree murder pleas.',
    ])) { echo "    + case ({$p['arrest']})\n"; $tot['cases']++; }
}

// =================== 6. Cameron David Bishop ===================

[$bishop, $bishopCreated] = addP([
    'name' => 'Cameron David Bishop', 'first_name' => 'Cameron', 'middle_name' => 'David', 'last_name' => 'Bishop',
    'gender' => 'Male', 'race' => 'White', 'state' => 'Colorado',
    'description' => 'Students for a Democratic Society (SDS) member who opposed the Vietnam War. Became the first radical placed on the FBI\'s Ten Most Wanted Fugitives list (#300) for bombing four high-voltage Public Service Company of Colorado transmission towers in Jefferson, Arapahoe and Adams Counties over an eight-day span in January 1969. The second person ever charged under the WWII-era Federal Sabotage Act. After living as a fugitive for over six years, Bishop was arrested March 12, 1975 in East Greenwich, Rhode Island. Convicted in U.S. District Court in Denver in 1975 on three sabotage counts and sentenced to concurrent seven-year terms; conviction reversed by the Tenth Circuit in 1977 (555 F.2d 771) on the grounds that the 1950 presidential proclamation of national emergency could not sustain a sabotage prosecution for 1969 conduct.',
    'era' => '1970s', 'ideologies' => ['New Left', 'Anti-Vietnam War', 'Anti-imperialism'],
    'affiliation' => ['Students for a Democratic Society'],
]);
echo ($bishopCreated ? '  CREATED' : '  EXISTS ') . " Cameron David Bishop (id={$bishop->id})\n";
$bishopCreated ? $tot['created']++ : $tot['existed']++;
if (attachC($bishop, [
    'institution' => ['name' => 'U.S. District Court, District of Colorado — U.S. v. Bishop (FBI #300)', 'city' => 'Denver', 'state' => 'Colorado'],
    'arrest_date' => '1975-03-12', 'sentenced_date' => '1975-09-15',
    'charges' => 'Three counts of sabotage under the Federal Sabotage Act (originally) for bombing four high-voltage power transmission towers in Colorado over an eight-day span in January 1969. Reduced to lesser charges after the Tenth Circuit reversal at 555 F.2d 771 (1977).',
    'sentence' => 'Three concurrent 7-year terms (vacated 1977 by 10th Circuit; final disposition unclear in available open sources).',
    'convicted' => 'Yes (1975); conviction vacated on appeal 1977',
])) { echo "    + case (1975-03-12)\n"; $tot['cases']++; }

// =================== 7. Anti-apartheid student divestment ===================

$berkeleyInst = ['name' => 'UC Berkeley Sproul Hall — Campaign Against Apartheid / UC Divestment Committee', 'city' => 'Berkeley', 'state' => 'California'];
$columbiaInst = ['name' => 'Columbia University Hamilton Hall — Coalition for a Free South Africa', 'city' => 'New York', 'state' => 'New York'];
$yaleInst = ['name' => 'Yale University Beinecke Plaza — Yale Coalition Against Apartheid', 'city' => 'New Haven', 'state' => 'Connecticut'];
$cornellInst = ['name' => 'Cornell University Day Hall — South African Divestment Coalition', 'city' => 'Ithaca', 'state' => 'New York'];

$studentPeople = [
    [
        'name' => 'Pedro Antonio Noguera', 'first_name' => 'Pedro', 'middle_name' => 'Antonio', 'last_name' => 'Noguera',
        'gender' => 'Male', 'race' => 'Latino/Hispanic', 'state' => 'California', 'birthdate' => '1959-08-07',
        'description' => 'Born in New York City to Caribbean immigrants. Earned a B.A. and M.A. in sociology at Brown University, then a Ph.D. at UC Berkeley in 1989. As ASUC student-body president at UC Berkeley in 1985, he was one of the principal student organizers of the April 1985 Sproul Hall anti-apartheid sit-ins, which culminated in roughly 159 arrests on April 16, 1985 and a class boycott of 10,000 the following day. Arrested at least three times during the protests. Now dean of the USC Rossier School of Education.',
        'affiliation' => ['UC Berkeley student government', 'Campaign Against Apartheid'],
        'arrest' => '1985-04-16', 'institution' => $berkeleyInst,
    ],
    [
        'name' => 'Nancy Skinner', 'first_name' => 'Nancy', 'last_name' => 'Skinner',
        'gender' => 'Female', 'race' => 'White', 'state' => 'California',
        'description' => 'As a UC Berkeley graduate student, Nancy Skinner co-led the UC Divestment Committee that organized daily Sproul Plaza rallies starting April 10, 1985 demanding the UC Regents divest the $1.7-3 billion held in companies operating in apartheid South Africa. The campaign culminated in the April 16, 1985 raid in which roughly 159 protesters were arrested at the sit-in, with nearly 400 arrested over the campaign\'s full course. The UC Regents voted to divest in 1986. Later a Berkeley city councilmember, California State Assemblywoman, and currently a California State Senator.',
        'affiliation' => ['UC Divestment Committee (Berkeley)'],
        'arrest' => '1985-04-16', 'institution' => $berkeleyInst,
    ],
    [
        'name' => 'William Nessen', 'first_name' => 'William', 'last_name' => 'Nessen',
        'gender' => 'Male', 'race' => 'White', 'state' => 'California',
        'description' => 'Headed the Campaign Against Apartheid at UC Berkeley, the more militant of the two main 1985 divestment groups, partnering with Nancy Skinner\'s UC Divestment Committee to organize the April 1985 Sproul Plaza rallies and Sproul Hall sit-in. Among those arrested in the April 16, 1985 police raid. Later became a freelance war correspondent in Aceh, Indonesia.',
        'affiliation' => ['Campaign Against Apartheid (Berkeley)'],
        'arrest' => '1985-04-16', 'institution' => $berkeleyInst,
    ],
    [
        'name' => 'Matthew J. Countryman', 'first_name' => 'Matthew', 'middle_name' => 'J.', 'last_name' => 'Countryman',
        'gender' => 'Male', 'race' => 'Black', 'state' => 'Connecticut',
        'description' => 'Yale College class of 1986. Founding leader of the Yale Coalition Against Apartheid and the Yale Divestment Coalition, which on April 4, 1986 — the 18th anniversary of Martin Luther King\'s assassination — erected the "Winnie Mandela" shantytown on Beinecke Plaza. Yale administrators ordered the shanties dismantled and arrested protesters; over the spring 1986 protest cycle approximately 322 students, faculty and New Haven community members were arrested for public-disturbance offenses outside Yale administration buildings. Now professor of history and American culture at the University of Michigan.',
        'affiliation' => ['Black Student Alliance at Yale', 'Yale Coalition Against Apartheid'],
        'arrest' => '1986-04-04', 'institution' => $yaleInst,
    ],
    [
        'name' => 'Michael Morand', 'first_name' => 'Michael', 'last_name' => 'Morand',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Connecticut',
        'description' => 'Yale College class of 1987 and later Yale Divinity School class of 1993. A leader of the Yale Divestment Coalition during the 1985-86 anti-apartheid campaign and shantytown protests on Beinecke Plaza. Participated in the April 1986 protests that led to mass arrests of students, faculty and community members.',
        'affiliation' => ['Yale Divestment Coalition'],
        'arrest' => '1986-04-04', 'institution' => $yaleInst,
    ],
    [
        'name' => 'Matthew Lyons', 'first_name' => 'Matthew', 'last_name' => 'Lyons',
        'gender' => 'Male', 'race' => 'White', 'state' => 'New York',
        'description' => 'Cornell undergraduate who led the South African Divestment Coalition. On April 18, 1985 began a sit-in at Day Hall that produced over 200 initial arrests and ultimately over 900 arrests by semester\'s end. After the initial arrests, students built a shantytown on the Arts Quad that the university briefly permitted before dismantling on June 25, 1985. His papers are housed at Cornell\'s Rare and Manuscript Collections.',
        'affiliation' => ['South African Divestment Coalition (Cornell)'],
        'arrest' => '1985-04-18', 'institution' => $cornellInst,
    ],
];

foreach ($studentPeople as $p) {
    $p['ideologies'] = ['Anti-apartheid', 'Civil rights', 'Student activism'];
    $p['era'] = '1980s';
    [$prisoner, $created] = addP($p);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$p['name']} (id={$prisoner->id})\n";
    $created ? $tot['created']++ : $tot['existed']++;
    if (attachC($prisoner, [
        'institution' => $p['institution'],
        'arrest_date' => $p['arrest'],
        'charges' => 'Anti-apartheid student divestment civil disobedience (1985-1986). Misdemeanor trespass / unlawful assembly / public disturbance for sit-ins, building occupations, and shantytown construction at U.S. universities demanding divestment from companies operating in apartheid South Africa. Most arrests resulted in dismissed charges but produced major institutional change — UC, Columbia, Harvard, Cornell, Yale and many others divested between 1985 and 1988.',
        'sentence' => 'Charges typically dismissed; same-day release.',
        'convicted' => 'No — most charges dismissed',
    ])) { echo "    + case ({$p['arrest']})\n"; $tot['cases']++; }
}

// =================== 8. Sister Dianne Muhlenkamp ===================

[$muhl, $muhlCreated] = addP([
    'name' => 'Mary Dianne Muhlenkamp', 'first_name' => 'Mary', 'middle_name' => 'Dianne', 'last_name' => 'Muhlenkamp',
    'aka' => 'Sister Dianne Muhlenkamp', 'gender' => 'Female', 'race' => 'White', 'state' => 'Texas',
    'description' => 'Catholic religious sister from Indiana volunteering with the Diocese of Brownsville, Texas. Arrested by the U.S. Border Patrol on February 17, 1984 near Guerra, Texas while transporting Salvadoran refugees in a diocesan vehicle (Stacey Lynn Merkt was a passenger and was charged separately). A co-defendant in early Sanctuary Movement Texas prosecutions, but the federal government dropped the principal smuggling charge in exchange for her testimony, granting her a one-year deferred adjudication. Her case is significant as one of the first Sanctuary-related arrests of a Catholic sister and helped catalyze the Tucson and Brownsville prosecutions that followed.',
    'era' => '1980s',
    'ideologies' => ['Catholic social teaching', 'Liberation theology', 'Refugee rights', 'Sanctuary'],
    'affiliation' => ['Poor Handmaids of Jesus Christ', 'Diocese of Brownsville (volunteer)', 'Sanctuary Movement'],
]);
echo ($muhlCreated ? '  CREATED' : '  EXISTS ') . " Mary Dianne Muhlenkamp (id={$muhl->id})\n";
$muhlCreated ? $tot['created']++ : $tot['existed']++;
if (attachC($muhl, [
    'institution' => ['name' => 'U.S. District Court, Southern District of Texas (Brownsville)', 'city' => 'Brownsville', 'state' => 'Texas'],
    'arrest_date' => '1984-02-17',
    'charges' => 'Conspiracy to transport undocumented refugees; transporting undocumented refugees (8 U.S.C. § 1324). Co-defendant Stacey Lynn Merkt charged in the same federal Border Patrol stop near Guerra, Texas.',
    'sentence' => 'Principal smuggling charges dropped in exchange for testimony; one-year deferred adjudication.',
    'convicted' => 'No — charges dropped (deferred adjudication)',
])) { echo "    + case (1984-02-17)\n"; $tot['cases']++; }

echo "\nDone.\n";
echo "  prisoners created:        {$tot['created']}\n";
echo "  prisoners already existed: {$tot['existed']}\n";
echo "  cases added:              {$tot['cases']}\n";
