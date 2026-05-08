<?php

declare(strict_types=1);

/**
 * Bulk-add LBJ-era political prisoners (1963-1969):
 *
 *   1. Puerto Rican Nationalists (1954 Capitol attack — imprisoned through LBJ era)
 *   2. Chicano Movement: Tijerina, Sal Castro/East L.A. 13, Corky Gonzales
 *   3. Free Speech Movement: Mario Savio (Berkeley FSM)
 *   4. Vietnam draft resisters: D. Miller, Cornell, Mitchell, O'Brien, Levy, Noyd, Howe
 *   5. Black Power: Robert F. Williams, Mae Mallory, Cleveland Sellers, David Hilliard
 *   6. Civil rights movement: Bevel, Lawson, Lafayette, Hamer, Ponder, Johnson, Guyot, Bob Moses
 *   7. Indigenous treaty rights: Hank Adams, Billy Frank Jr., Janet McCloud
 *   8. Anti-McCarthy / civil liberties: Frank Wilkinson
 *
 * Idempotent: re-runs skip already-present prisoners by name.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;

function addP(array $p): array {
    $existing = Prisoner::where('name', $p['name'])->first();
    if ($existing) return [$existing, false];
    $prisoner = Prisoner::create(array_filter([
        'name' => $p['name'], 'first_name' => $p['first_name'] ?? null, 'middle_name' => $p['middle_name'] ?? null,
        'last_name' => $p['last_name'] ?? null, 'aka' => $p['aka'] ?? null,
        'gender' => $p['gender'] ?? null, 'race' => $p['race'] ?? null, 'state' => $p['state'] ?? null,
        'birthdate' => $p['birthdate'] ?? null, 'death_date' => $p['death_date'] ?? null,
        'description' => $p['description'] ?? null, 'era' => $p['era'] ?? 'Civil Rights & Vietnam',
        'ideologies' => $p['ideologies'] ?? null, 'affiliation' => $p['affiliation'] ?? null,
        'in_custody' => $p['in_custody'] ?? false, 'released' => $p['released'] ?? true,
    ], fn ($v) => $v !== null));
    return [$prisoner, true];
}

function attachC(Prisoner $prisoner, array $c): bool {
    if (! empty($c['arrest_date'])) {
        $exists = $prisoner->cases()->where('arrest_date', $c['arrest_date'])->first();
        if ($exists) return false;
    }
    $institution = Institution::firstOrCreate(
        ['name' => $c['institution']['name']],
        ['city' => $c['institution']['city'] ?? null, 'state' => $c['institution']['state'] ?? null]
    );
    PrisonerCase::create([
        'prisoner_id' => $prisoner->id, 'institution_id' => $institution->id,
        'charges' => $c['charges'] ?? null, 'arrest_date' => $c['arrest_date'] ?? null,
        'incarceration_date' => $c['incarceration_date'] ?? null, 'release_date' => $c['release_date'] ?? null,
        'sentenced_date' => $c['sentenced_date'] ?? null, 'convicted' => $c['convicted'] ?? null,
        'sentence' => $c['sentence'] ?? null, 'prosecutor' => $c['prosecutor'] ?? null, 'judge' => $c['judge'] ?? null,
    ]);
    return true;
}

$tot = ['created' => 0, 'existed' => 0, 'cases' => 0];

// ---------------------------------------------------------------------------
// 1. PUERTO RICAN NATIONALISTS — 1954 Capitol attack, served through LBJ era
// ---------------------------------------------------------------------------

$prInst1954 = ['name' => 'U.S. District Court, D.C. — 1954 Capitol Attack / Puerto Rican Nationalists', 'city' => 'Washington', 'state' => 'D.C.'];
$prCharges = 'Assault with intent to kill (5 counts) for the March 1, 1954 armed attack on the U.S. House of Representatives, in which four Puerto Rican Nationalists fired ~30 rounds from the Ladies\' Gallery, wounding five congressmen. Additional 6-year sentence under Smith Act for seditious conspiracy (October 1954). Defendants refused parole conditions requiring renunciation of Puerto Rican independence. Sentences commuted by President Carter in 1977-1979.';

foreach ([
    [
        'p' => ['name' => 'Lolita Lebrón', 'first_name' => 'Lolita', 'last_name' => 'Lebrón', 'aka' => 'Dolores Lebrón Sotomayor',
            'birthdate' => '1919-11-19', 'death_date' => '2010-08-01', 'gender' => 'Female', 'race' => 'Latino/Hispanic',
            'state' => 'West Virginia', 'affiliation' => ['Puerto Rican Nationalist Party'],
            'ideologies' => ['Puerto Rican independence', 'Anti-colonialism'], 'era' => 'Civil Rights & Vietnam',
            'description' => "Lolita Lebrón was a Puerto Rican nationalist who on March 1, 1954, led an armed assault on the U.S. House of Representatives, unfurling a Puerto Rican flag and shouting 'Free Puerto Rico!' from the Ladies' Gallery as her three comrades fired thirty rounds into the chamber, wounding five congressmen. Although the prosecution under Eisenhower predates the LBJ era, Lebrón remained imprisoned at the Federal Reformatory for Women in Alderson, West Virginia throughout the Kennedy and Johnson administrations, refusing parole conditions that would have required her to renounce Puerto Rican independence. She served twenty-five years until President Jimmy Carter commuted her sentence on September 6, 1979, releasing her on September 10, 1979. She remained an outspoken independentista until her death in 2010."],
        'c' => ['institution' => ['name' => 'Federal Reformatory for Women, Alderson', 'city' => 'Alderson', 'state' => 'West Virginia'],
            'charges' => $prCharges, 'arrest_date' => '1954-03-01', 'release_date' => '1979-09-10',
            'sentence' => '16 to 50 years assault + 6 years seditious conspiracy (~56 years total)', 'convicted' => 'Yes',
            'judge' => 'Alexander Holtzoff (assault); Lawrence E. Walsh (seditious conspiracy)'],
    ],
    [
        'p' => ['name' => 'Rafael Cancel Miranda', 'first_name' => 'Rafael', 'middle_name' => 'Cancel', 'last_name' => 'Miranda',
            'birthdate' => '1930-07-18', 'death_date' => '2020-03-02', 'gender' => 'Male', 'race' => 'Latino/Hispanic',
            'state' => 'Kansas', 'affiliation' => ['Puerto Rican Nationalist Party'],
            'ideologies' => ['Puerto Rican independence', 'Anti-colonialism'], 'era' => 'Civil Rights & Vietnam',
            'description' => "Rafael Cancel Miranda, born in Mayagüez, Puerto Rico, was one of four Nationalists who carried out the March 1, 1954 armed attack on the U.S. House of Representatives. Sentenced to 75 years for assault with intent to kill plus 6 years for seditious conspiracy (total 81 years), he was the only one of the group sent initially to Alcatraz, later transferred to USP Marion and USP Leavenworth (1960), where he organized a 1970 prisoners' strike with Oscar Collazo, Andrés Figueroa Cordero, and Irvin Flores. He served 25 years through the entirety of the LBJ era and beyond, repeatedly refusing conditional releases that required renouncing independentismo. President Carter commuted his sentence in September 1979. After his release he became a poet and continued advocating Puerto Rican independence until his death in 2020."],
        'c' => ['institution' => ['name' => 'USP Leavenworth — Puerto Rican Nationalist prisoners', 'city' => 'Leavenworth', 'state' => 'Kansas'],
            'charges' => $prCharges, 'arrest_date' => '1954-03-01', 'release_date' => '1979-09-10',
            'sentence' => '75 years (assault) + 6 years (sedition) = 81 years', 'convicted' => 'Yes',
            'judge' => 'Alexander Holtzoff; Lawrence E. Walsh'],
    ],
    [
        'p' => ['name' => 'Andrés Figueroa Cordero', 'first_name' => 'Andrés', 'middle_name' => 'Figueroa', 'last_name' => 'Cordero',
            'birthdate' => '1925-08-04', 'death_date' => '1979-03-07', 'gender' => 'Male', 'race' => 'Latino/Hispanic',
            'state' => 'Georgia', 'affiliation' => ['Puerto Rican Nationalist Party'],
            'ideologies' => ['Puerto Rican independence', 'Anti-colonialism'], 'era' => 'Civil Rights & Vietnam',
            'description' => "Andrés Figueroa Cordero was the fourth Nationalist who participated in the March 1, 1954 attack on the U.S. House of Representatives alongside Lolita Lebrón, Rafael Cancel Miranda, and Irvin Flores. He was sentenced to 75 years plus 6 years for seditious conspiracy and incarcerated at the U.S. Penitentiary in Atlanta, Georgia, where he spent the entirety of the LBJ era. Diagnosed with terminal cancer in the mid-1970s, he was transferred to the Federal Medical Center for Prisoners in Springfield, Missouri. President Jimmy Carter commuted his sentence on humanitarian grounds in October 1977, and he was released to die at home in Puerto Rico, where he passed away in March 1979."],
        'c' => ['institution' => ['name' => 'USP Atlanta — Puerto Rican Nationalist prisoners', 'city' => 'Atlanta', 'state' => 'Georgia'],
            'charges' => $prCharges, 'arrest_date' => '1954-03-01', 'release_date' => '1977-10-06',
            'sentence' => '75 years + 6 years = 81 years', 'convicted' => 'Yes',
            'judge' => 'Alexander Holtzoff; Lawrence E. Walsh'],
    ],
    [
        'p' => ['name' => 'Irvin Flores Rodríguez', 'first_name' => 'Irvin', 'middle_name' => 'Flores', 'last_name' => 'Rodríguez',
            'birthdate' => '1924-08-18', 'death_date' => '1994-03-20', 'gender' => 'Male', 'race' => 'Latino/Hispanic',
            'state' => 'Kansas', 'affiliation' => ['Puerto Rican Nationalist Party'],
            'ideologies' => ['Puerto Rican independence', 'Anti-colonialism'], 'era' => 'Civil Rights & Vietnam',
            'description' => "Irvin Flores Rodríguez was one of the four Puerto Rican Nationalists who attacked the U.S. House of Representatives on March 1, 1954. Apprehended at a bus station shortly after the assault, he was convicted alongside his comrades and sentenced to over 75 years in federal prison plus six additional years for seditious conspiracy. He served his sentence at USP Leavenworth, where in 1970 he joined Oscar Collazo, Andrés Figueroa Cordero, and Rafael Cancel Miranda in organizing a prisoner strike protesting their treatment. After 25 years of imprisonment spanning the Eisenhower, Kennedy, Johnson, Nixon, Ford, and Carter administrations, he was released on September 10, 1979 when President Carter commuted his sentence. He returned to Puerto Rico, married, managed a boarding house, and remained an independence advocate until his death in 1994."],
        'c' => ['institution' => ['name' => 'USP Leavenworth — Puerto Rican Nationalist prisoners', 'city' => 'Leavenworth', 'state' => 'Kansas'],
            'charges' => $prCharges, 'arrest_date' => '1954-03-01', 'release_date' => '1979-09-10',
            'sentence' => '75+ years + 6 years', 'convicted' => 'Yes',
            'judge' => 'Alexander Holtzoff; Lawrence E. Walsh'],
    ],
] as $row) {
    [$prisoner, $created] = addP($row['p']);
    $tot[$created ? 'created' : 'existed']++;
    if (attachC($prisoner, $row['c'])) $tot['cases']++;
}

// ---------------------------------------------------------------------------
// 2. CHICANO MOVEMENT
// ---------------------------------------------------------------------------

[$tijerina, $created] = addP([
    'name' => 'Reies López Tijerina', 'first_name' => 'Reies', 'middle_name' => 'López', 'last_name' => 'Tijerina',
    'aka' => 'El Tigre',
    'birthdate' => '1926-09-21', 'death_date' => '2015-01-19', 'gender' => 'Male', 'race' => 'Latino/Hispanic',
    'state' => 'New Mexico', 'affiliation' => ['Alianza Federal de las Mercedes'],
    'ideologies' => ['Chicano nationalism', 'Land grant restoration', 'Indigenous-Hispano sovereignty'],
    'era' => 'Civil Rights & Vietnam',
    'description' => "Reies López Tijerina was born to migrant Mexican-American cotton farmers in Falls City, Texas, and became the founder of the Alianza Federal de las Mercedes, a movement to restore Spanish and Mexican land grants confiscated under the 1848 Treaty of Guadalupe Hidalgo. On June 5, 1967, he led an armed raid on the Rio Arriba County courthouse in Tierra Amarilla, New Mexico, attempting to make a citizen's arrest of District Attorney Alfonso Sánchez; in the gunfight a state policeman and a jailer were wounded. The raid triggered the largest manhunt in New Mexico history. Tijerina was acquitted of state kidnapping/assault charges (defending himself), but later convicted on federal charges related to a 1966 Kit Carson National Forest occupation and on additional state charges; he served roughly two years (1969-1971) at La Tuna Federal Correctional Institution in Texas, with a transfer to the federal medical center in Springfield, Missouri. He remains a foundational figure of the Chicano Movement.",
]);
$tot[$created ? 'created' : 'existed']++;
if (attachC($tijerina, [
    'institution' => ['name' => 'FCI La Tuna — Tijerina / Tierra Amarilla', 'city' => 'Anthony', 'state' => 'Texas'],
    'charges' => 'Federal: assault and aiding/abetting destruction of U.S. Forest Service property (1966 Echo Amphitheater incident); state: kidnapping, assault on jailer, false imprisonment (Tierra Amarilla courthouse raid)',
    'arrest_date' => '1967-06-10', 'release_date' => '1971-07-26',
    'sentence' => '2 years federal + concurrent state sentence', 'convicted' => 'Mixed (federal yes; state Tierra Amarilla acquitted)',
])) $tot['cases']++;

[$castro, $created] = addP([
    'name' => 'Sal Castro', 'first_name' => 'Salvador', 'last_name' => 'Castro', 'aka' => 'Salvador B. Castro',
    'birthdate' => '1933-10-25', 'death_date' => '2013-04-15', 'gender' => 'Male', 'race' => 'Latino/Hispanic',
    'state' => 'California', 'affiliation' => ['East L.A. 13', 'Chicano Movement'],
    'ideologies' => ['Chicano nationalism', 'Educational equity', 'Civil rights'],
    'era' => 'Civil Rights & Vietnam',
    'description' => "Sal Castro was a Lincoln High School social-studies teacher in East Los Angeles who in March 1968 helped organize the East L.A. Walkouts (or 'Blowouts'), the largest mass Latino student protest in U.S. history, in which more than 15,000 Chicano students walked out of LAUSD schools to protest substandard, segregated education. On May 31, 1968, Castro and twelve other organizers — known as the East L.A. 13, including Moctesuma Esparza, Eliezer Risco, Carlos Montes, and Carlos Muñoz Jr. — were indicted by an LA County grand jury on conspiracy to disturb the peace and disrupt schools, charges that carried up to 66 years. Castro, the only adult educator among them, was held the longest. The California Court of Appeals struck down the indictments in 1970 as a violation of First Amendment rights. He continued teaching and organizing until his death in 2013.",
]);
$tot[$created ? 'created' : 'existed']++;
if (attachC($castro, [
    'institution' => ['name' => 'Los Angeles County Jail — East L.A. 13 / Chicano Walkouts', 'city' => 'Los Angeles', 'state' => 'California'],
    'charges' => 'Conspiracy to disturb the peace and disrupt the operation of public schools (15 counts) — East L.A. Walkouts',
    'arrest_date' => '1968-05-31', 'release_date' => '1970',
    'convicted' => 'No — indictments dismissed by California Court of Appeals, 1970',
])) $tot['cases']++;

[$corky, $created] = addP([
    'name' => 'Rodolfo Gonzales', 'first_name' => 'Rodolfo', 'last_name' => 'Gonzales', 'aka' => 'Corky Gonzales',
    'birthdate' => '1928-06-18', 'death_date' => '2005-04-12', 'gender' => 'Male', 'race' => 'Latino/Hispanic',
    'state' => 'Colorado', 'affiliation' => ['Crusade for Justice', 'La Raza Unida Party'],
    'ideologies' => ['Chicano nationalism', 'Aztlán self-determination', 'Anti-war'],
    'era' => 'Civil Rights & Vietnam',
    'description' => "Rodolfo 'Corky' Gonzales was a former featherweight boxer from Denver who founded the Crusade for Justice in 1966 and authored the foundational Chicano Movement epic poem 'I Am Joaquín' in 1967. In 1968 he led the Chicano contingent in the Poor People's Campaign in Washington, D.C., and in March 1969 hosted the First National Chicano Youth Liberation Conference in Denver, which adopted 'El Plan Espiritual de Aztlán.' His most prominent arrests came at the August 29, 1970 Chicano Moratorium and a 1971 Los Angeles antiwar rally where he was charged with carrying a loaded firearm. The March 1973 Denver police raid on Crusade for Justice headquarters left organizer Luis 'Junior' Martínez dead.",
]);
$tot[$created ? 'created' : 'existed']++;
if (attachC($corky, [
    'institution' => ['name' => 'Los Angeles County Jail — Chicano Moratorium', 'city' => 'Los Angeles', 'state' => 'California'],
    'charges' => 'Carrying a loaded firearm (1971 Los Angeles arrest after antiwar rally)',
    'arrest_date' => '1970-08-29', 'sentence' => 'Brief jail terms; no extended imprisonment documented',
])) $tot['cases']++;

// ---------------------------------------------------------------------------
// 3. FREE SPEECH MOVEMENT
// ---------------------------------------------------------------------------

[$savio, $created] = addP([
    'name' => 'Mario Savio', 'first_name' => 'Mario', 'last_name' => 'Savio',
    'birthdate' => '1942-12-08', 'death_date' => '1996-11-06', 'gender' => 'Male', 'race' => 'White',
    'state' => 'California', 'affiliation' => ['Free Speech Movement (UC Berkeley)', 'Friends of SNCC'],
    'ideologies' => ['Free speech', 'Civil rights', 'Anti-war', 'Student power'],
    'era' => 'Civil Rights & Vietnam',
    'description' => "Mario Savio, the son of Sicilian immigrants, became the leading orator of the 1964 Berkeley Free Speech Movement after participating in SNCC's Mississippi Freedom Summer. On December 2, 1964, atop the steps of Sproul Hall, he delivered his iconic 'Bodies Upon the Gears' speech and led approximately 1,000 students into a sit-in protesting university restrictions on political activity. Police arrested 773 students that night in the largest mass arrest in California history to that date. Savio was convicted of trespass and resisting arrest and in 1967 served 120 days at Santa Rita Jail in Alameda County. He spent decades teaching mathematics and physics, returning to activism in his later years to oppose California's Proposition 209 before suffering a fatal heart attack in 1996.",
]);
$tot[$created ? 'created' : 'existed']++;
if (attachC($savio, [
    'institution' => ['name' => 'Santa Rita Jail — Berkeley Free Speech Movement', 'city' => 'Dublin', 'state' => 'California'],
    'charges' => 'Trespass, resisting arrest, unlawful assembly — December 2, 1964 Sproul Hall sit-in (FSM)',
    'arrest_date' => '1964-12-03', 'sentence' => '120 days county jail (imposed 1967)', 'convicted' => 'Yes',
    'release_date' => '1967',
])) $tot['cases']++;

// ---------------------------------------------------------------------------
// 4. VIETNAM DRAFT RESISTERS
// ---------------------------------------------------------------------------

$draftCharges = '50 U.S.C. App. § 462 — Selective Service Act violations (refusing induction or destroying draft card)';

[$davidMiller, $created] = addP([
    'name' => 'David J. Miller', 'first_name' => 'David', 'middle_name' => 'J.', 'last_name' => 'Miller',
    'birthdate' => '1942', 'gender' => 'Male', 'race' => 'White',
    'state' => 'New York', 'affiliation' => ['Catholic Worker Movement'],
    'ideologies' => ['Catholic pacifism', 'Anti-war'], 'era' => 'Civil Rights & Vietnam',
    'description' => "David J. Miller was a 24-year-old Catholic Worker pacifist and the first person prosecuted under the August 1965 amendment to the Selective Service Act criminalizing destruction of a draft card. On October 15, 1965, at a rally outside the Armed Forces Induction Center on Whitehall Street in lower Manhattan, Miller burned his draft card with a borrowed lighter; FBI agents arrested him three days later. He was convicted in February 1966 and sentenced to 30 months in federal prison. The Second Circuit upheld his conviction (367 F.2d 72), and the Supreme Court declined to take the case, instead later upholding the law in United States v. O'Brien (1968). Miller remained free on bail until June 1968, when he began serving and ultimately did 22 months in federal prison.",
]);
$tot[$created ? 'created' : 'existed']++;
if (attachC($davidMiller, [
    'institution' => ['name' => 'Federal prison — Selective Service Act prosecutions', 'city' => null, 'state' => null],
    'charges' => 'Destruction of Selective Service registration certificate (50 U.S.C. App. § 462(b)(3)) — first prosecution under 1965 draft-card-burning amendment',
    'arrest_date' => '1965-10-18', 'sentence' => '30 months (served 22 months)', 'convicted' => 'Yes',
    'release_date' => '1970',
])) $tot['cases']++;

[$cornell, $created] = addP([
    'name' => 'Tom Cornell', 'first_name' => 'Thomas', 'middle_name' => 'Charles', 'last_name' => 'Cornell',
    'birthdate' => '1934-04-26', 'death_date' => '2022-08-01', 'gender' => 'Male', 'race' => 'White',
    'state' => 'Connecticut', 'affiliation' => ['Catholic Worker Movement', 'Catholic Peace Fellowship', 'War Resisters League'],
    'ideologies' => ['Catholic pacifism', 'Anti-war', 'Christian nonviolence'], 'era' => 'Civil Rights & Vietnam',
    'description' => "Tom Cornell was a Catholic Worker Movement editor and pacifist who, on November 6, 1965, publicly burned his draft card in Union Square, New York City alongside David McReynolds and three others — Roy Lisker, Marc Paul Edelman, and James Wilson — in one of the first prosecutions under the August 1965 amendment criminalizing draft-card destruction. Dorothy Day and A.J. Muste stood with the protesters in support. Cornell was convicted and sentenced to six months at the federal correctional institution in Danbury, Connecticut, which he served. He continued antiwar and Catholic peace organizing for the rest of his life, was ordained a permanent deacon in 1988, and assisted in negotiations around the Panama Canal Treaty in 1977.",
]);
$tot[$created ? 'created' : 'existed']++;
if (attachC($cornell, [
    'institution' => ['name' => 'FCI Danbury — Vietnam draft resisters', 'city' => 'Danbury', 'state' => 'Connecticut'],
    'charges' => 'Destruction of Selective Service registration certificate (50 U.S.C. App. § 462(b)(3)) — Union Square draft-card burning, November 6, 1965',
    'arrest_date' => '1965-11-06', 'sentence' => '6 months federal prison', 'convicted' => 'Yes',
])) $tot['cases']++;

[$mitchell, $created] = addP([
    'name' => 'David Henry Mitchell III', 'first_name' => 'David', 'middle_name' => 'Henry', 'last_name' => 'Mitchell',
    'birthdate' => '1942', 'gender' => 'Male', 'race' => 'White',
    'state' => 'Connecticut', 'affiliation' => ['Committee for Non-Violent Action', 'End the Draft'],
    'ideologies' => ['Anti-war (selective conscientious objection)', 'Pacifism', 'Anti-imperialism'], 'era' => 'Civil Rights & Vietnam',
    'description' => "David Henry Mitchell III was a Connecticut draft resister whose 1965 conviction became a landmark of selective conscientious objection — the legal argument that a citizen may refuse to participate in a particular war (Vietnam) on the grounds that it violates international law, without claiming pacifist objection to all wars. Refusing to fill out conscientious objector forms, Mitchell argued at trial that the Vietnam War constituted a war of aggression under the 1945 London Charter (Nuremberg Principles). U.S. District Judge William H. Timbers rejected his Nuremberg defense in United States v. Mitchell, 246 F. Supp. 874 (D. Conn. 1965). Mitchell was convicted of refusing induction, sentenced to five years in federal prison, and the Supreme Court denied certiorari in 1967 over a famous dissent by Justice William O. Douglas. He served approximately two years.",
]);
$tot[$created ? 'created' : 'existed']++;
if (attachC($mitchell, [
    'institution' => ['name' => 'Federal prison — Selective Service Act prosecutions', 'city' => null, 'state' => null],
    'charges' => 'Refusing induction (50 U.S.C. App. § 462) — Nuremberg defense rejected, U.S. v. Mitchell, 246 F. Supp. 874 (D. Conn. 1965)',
    'arrest_date' => '1965', 'sentence' => '5 years federal prison (served ~2 years)', 'convicted' => 'Yes',
    'release_date' => '1969', 'judge' => 'William H. Timbers (D. Conn.)',
])) $tot['cases']++;

[$obrien, $created] = addP([
    'name' => 'David O\'Brien', 'first_name' => 'David', 'last_name' => "O'Brien",
    'gender' => 'Male', 'race' => 'White', 'state' => 'Massachusetts',
    'affiliation' => ['Vietnam draft resisters'],
    'ideologies' => ['Anti-war', 'Free speech'], 'era' => 'Civil Rights & Vietnam',
    'description' => "David O'Brien was one of four men who burned their Selective Service registration certificates on the steps of the South Boston Courthouse on March 31, 1966 in protest of the Vietnam War. Convicted under the August 1965 amendment criminalizing draft-card destruction, his appeal produced the landmark First Amendment ruling United States v. O'Brien, 391 U.S. 367 (1968), in which the Supreme Court announced the four-part test for content-neutral regulation of expressive conduct. O'Brien lost the appeal 7-1 and served his federal sentence; the case remains foundational doctrine on the symbolic-speech / conduct distinction.",
]);
$tot[$created ? 'created' : 'existed']++;
if (attachC($obrien, [
    'institution' => ['name' => 'Federal prison — Selective Service Act prosecutions', 'city' => null, 'state' => null],
    'charges' => 'Destruction of Selective Service registration certificate — South Boston Courthouse, March 31, 1966 (United States v. O\'Brien, 391 U.S. 367)',
    'arrest_date' => '1966-03-31', 'sentence' => 'Federal prison', 'convicted' => 'Yes (affirmed 1968 by SCOTUS)',
])) $tot['cases']++;

[$levy, $created] = addP([
    'name' => 'Howard Levy', 'first_name' => 'Howard', 'last_name' => 'Levy', 'aka' => 'Howard Brett Levy',
    'birthdate' => '1937', 'gender' => 'Male', 'race' => 'White', 'state' => 'New Jersey',
    'affiliation' => ['U.S. Army Medical Corps (in-uniform resister)'],
    'ideologies' => ['Anti-war', 'Medical ethics'], 'era' => 'Civil Rights & Vietnam',
    'description' => "Captain Howard Levy was an Army dermatologist at Fort Jackson, South Carolina who in 1966 refused orders to train Green Beret medics for deployment to Vietnam, arguing that doing so would make him complicit in war crimes and violate medical ethics. He also publicly opposed the war and made statements supporting Black resistance to the draft. Court-martialed in 1967, he was convicted of willful disobedience, promoting disloyalty, and conduct unbecoming an officer. Sentenced to three years at Fort Leavenworth's military prison, he served 26 months before parole. His case, Parker v. Levy, 417 U.S. 733 (1974), reached the Supreme Court on First Amendment grounds; the conviction was ultimately upheld. After release Levy resumed medical practice in New York.",
]);
$tot[$created ? 'created' : 'existed']++;
if (attachC($levy, [
    'institution' => ['name' => 'United States Disciplinary Barracks, Fort Leavenworth — Vietnam-era court-martials', 'city' => 'Fort Leavenworth', 'state' => 'Kansas'],
    'charges' => 'Willful disobedience of a lawful order (UCMJ Art. 90); promoting disloyalty (Art. 134); conduct unbecoming an officer (Art. 133) — refusing to train Green Beret medics',
    'arrest_date' => '1967-06', 'sentence' => '3 years military prison (served 26 months)', 'convicted' => 'Yes (court-martial)',
    'release_date' => '1969-08',
])) $tot['cases']++;

[$noyd, $created] = addP([
    'name' => 'Dale Noyd', 'first_name' => 'Dale', 'last_name' => 'Noyd',
    'birthdate' => '1933-09-25', 'death_date' => '2007-01-11', 'gender' => 'Male', 'race' => 'White',
    'state' => 'Colorado', 'affiliation' => ['U.S. Air Force (in-uniform resister)'],
    'ideologies' => ['Selective conscientious objection', 'Anti-war'], 'era' => 'Civil Rights & Vietnam',
    'description' => "Captain Dale Noyd was a decorated Air Force fighter pilot, psychology professor at the Air Force Academy, and combat veteran who in December 1966 sought discharge as a selective conscientious objector to the Vietnam War — a status the military refused to recognize. After being ordered to train F-100 pilots for Vietnam in 1967, he refused on conscience grounds. Court-martialed in early 1968 at Cannon Air Force Base, he was convicted of willful disobedience and missing movement, sentenced to one year at hard labor and dismissal from the service. Noyd v. Bond, 395 U.S. 683 (1969) refused habeas relief on jurisdictional grounds. He served his time and went on to teach psychology at the University of Washington, becoming a leading civilian voice on selective conscientious objection.",
]);
$tot[$created ? 'created' : 'existed']++;
if (attachC($noyd, [
    'institution' => ['name' => 'Cannon AFB / military confinement — Vietnam selective CO court-martials', 'city' => 'Clovis', 'state' => 'New Mexico'],
    'charges' => 'Willful disobedience and missing movement (UCMJ) — refusing to train Vietnam-bound F-100 pilots as selective conscientious objector',
    'arrest_date' => '1967', 'sentence' => '1 year at hard labor + dismissal from service', 'convicted' => 'Yes',
    'release_date' => '1969',
])) $tot['cases']++;

[$howe, $created] = addP([
    'name' => 'Henry Howe Jr.', 'first_name' => 'Henry', 'last_name' => 'Howe',
    'gender' => 'Male', 'race' => 'White', 'state' => 'Texas',
    'affiliation' => ['U.S. Army (in-uniform resister)'],
    'ideologies' => ['Anti-war'], 'era' => 'Civil Rights & Vietnam',
    'description' => "Second Lieutenant Henry Howe Jr. was an Army officer at Fort Bliss, Texas who on November 6, 1965 attended an antiwar demonstration in El Paso while off duty and out of uniform, carrying a sign reading 'Let's have more than a choice between petty ignorant facists [sic] in 1968' and 'End Johnson's Facist [sic] Aggression in Vietnam.' He was court-martialed under UCMJ Article 88 (contempt toward officials) and Article 134 (conduct prejudicial to good order), convicted, and sentenced to two years at hard labor and dismissal from the service. Despite filing for civilian relief, his conviction was sustained. He served his sentence at Fort Leavenworth's Disciplinary Barracks, becoming a touchstone case for First Amendment rights of in-uniform military personnel during Vietnam.",
]);
$tot[$created ? 'created' : 'existed']++;
if (attachC($howe, [
    'institution' => ['name' => 'United States Disciplinary Barracks, Fort Leavenworth — Vietnam-era court-martials', 'city' => 'Fort Leavenworth', 'state' => 'Kansas'],
    'charges' => 'Use of contemptuous words against the President (UCMJ Art. 88); conduct prejudicial to good order and discipline (Art. 134) — off-duty antiwar demonstration, El Paso, Nov 6 1965',
    'arrest_date' => '1965-11-06', 'sentence' => '2 years at hard labor + dismissal', 'convicted' => 'Yes',
])) $tot['cases']++;

// ---------------------------------------------------------------------------
// 5. BLACK POWER & SNCC
// ---------------------------------------------------------------------------

[$sellers, $created] = addP([
    'name' => 'Cleveland Sellers Jr.', 'first_name' => 'Cleveland', 'last_name' => 'Sellers', 'aka' => 'Cleve Sellers',
    'birthdate' => '1944-11-08', 'gender' => 'Male', 'race' => 'Black',
    'state' => 'South Carolina', 'affiliation' => ['Student Nonviolent Coordinating Committee (SNCC)'],
    'ideologies' => ['Civil rights', 'Black Power', 'Anti-war'], 'era' => 'Civil Rights & Vietnam',
    'description' => "Cleveland Sellers Jr. was SNCC's national program director and a key Black Power-era organizer. On the night of February 8, 1968, South Carolina Highway Patrol officers opened fire on unarmed Black students at South Carolina State University in Orangeburg who had been protesting the segregated All Star Bowling Lane; three students — Samuel Hammond, Delano Middleton, and Henry Smith — were killed, and 27 were wounded, including Sellers, who was shot in the shoulder. Although nine state troopers were acquitted in federal court, Sellers was the only person prosecuted in connection with the Orangeburg Massacre, convicted of riot in 1970. He began serving his sentence in February 1973 at the South Carolina Department of Corrections, was released in August 1973 after seven months, and received a full pardon from Governor Carroll Campbell on July 20, 1993, declining to have his record expunged.",
]);
$tot[$created ? 'created' : 'existed']++;
if (attachC($sellers, [
    'institution' => ['name' => 'South Carolina Department of Corrections — Orangeburg Massacre', 'city' => 'Columbia', 'state' => 'South Carolina'],
    'charges' => 'Inciting to riot — Orangeburg Massacre, Feb 8 1968 (the only person prosecuted; pardoned 1993)',
    'arrest_date' => '1968-02-09', 'sentence' => '1 year (served ~7 months)', 'convicted' => 'Yes (pardoned July 20, 1993)',
    'release_date' => '1973-08-31',
])) $tot['cases']++;

[$rfw, $created] = addP([
    'name' => 'Robert F. Williams', 'first_name' => 'Robert', 'middle_name' => 'F.', 'last_name' => 'Williams',
    'birthdate' => '1925-02-26', 'death_date' => '1996-10-15', 'gender' => 'Male', 'race' => 'Black',
    'state' => 'North Carolina', 'affiliation' => ['NAACP (Monroe, NC chapter)', 'Republic of New Afrika'],
    'ideologies' => ['Black self-defense', 'Black nationalism', 'Anti-imperialism'], 'era' => 'Civil Rights & Vietnam',
    'description' => "Robert F. Williams was the head of the Monroe, NC chapter of the NAACP and author of the 1962 book Negroes with Guns, which championed armed Black self-defense. After the August 27, 1961 'Freedom Riders riot' in Monroe, North Carolina indicted him for kidnapping a white couple — a charge he and supporters always insisted was a frame-up to silence his organizing. He fled the United States with his wife Mabel and their children, living in exile in Cuba (1961-1965), where he broadcast 'Radio Free Dixie,' and then in China (1966-1969). He returned to the U.S. in September 1969 to face the kidnapping charges; the case was dismissed in 1976. Williams influenced an entire generation of Black Power activists from Huey Newton to Eldridge Cleaver and was elected first president-in-exile of the Republic of New Afrika in 1968.",
]);
$tot[$created ? 'created' : 'existed']++;
if (attachC($rfw, [
    'institution' => ['name' => 'Union County Jail — Monroe NC kidnapping indictment (Robert F. Williams)', 'city' => 'Monroe', 'state' => 'North Carolina'],
    'charges' => 'Kidnapping (state) — August 27, 1961 Monroe, NC (defendants long maintained the charge was a political frame-up; case dismissed 1976)',
    'arrest_date' => '1969-09-12', 'release_date' => '1976',
    'convicted' => 'No — charges dismissed 1976',
])) $tot['cases']++;

[$mallory, $created] = addP([
    'name' => 'Mae Mallory', 'first_name' => 'Mae', 'last_name' => 'Mallory',
    'birthdate' => '1927-06-09', 'death_date' => '2007-06-09', 'gender' => 'Female', 'race' => 'Black',
    'state' => 'New York', 'affiliation' => ['Harlem Nine', 'Monroe Defense Committee', 'NAACP'],
    'ideologies' => ['Civil rights', 'Black self-defense', 'School desegregation'], 'era' => 'Civil Rights & Vietnam',
    'description' => "Mae Mallory was a Harlem activist and one of the 'Harlem Nine' mothers whose 1958 lawsuit against New York City's segregated schools produced In re Skipwith, a precedent against de facto segregation. Visiting Robert F. Williams in Monroe, North Carolina during the August 27, 1961 Freedom Rider violence, she was indicted alongside Williams on kidnapping charges arising from a white couple they had sheltered from a mob. While Williams fled the country, Mallory was arrested in Cleveland, fought extradition to North Carolina for two years, and ultimately served time. Convicted by an all-white jury and sentenced to 16-20 years in 1964, her conviction was reversed in 1965 on the grounds that Black citizens had been excluded from the grand and petit juries. She was released and the charges were eventually dismissed.",
]);
$tot[$created ? 'created' : 'existed']++;
if (attachC($mallory, [
    'institution' => ['name' => 'Cleveland city/county jail; North Carolina state prison — Monroe kidnapping case', 'city' => 'Cleveland', 'state' => 'Ohio'],
    'charges' => 'Kidnapping (state) — Monroe, NC, August 27, 1961',
    'arrest_date' => '1961-10', 'sentence' => '16-20 years (1964 conviction; reversed 1965)',
    'convicted' => 'Yes (reversed on appeal — jury exclusion of Black citizens)', 'release_date' => '1965',
])) $tot['cases']++;

// ---------------------------------------------------------------------------
// 6. CIVIL RIGHTS MOVEMENT — LBJ era arrests
// ---------------------------------------------------------------------------

$crCharges = 'Civil rights demonstration arrest — LBJ-era nonviolent direct action (sit-ins, marches, Freedom Rides aftermath, voter-registration organizing). Multiple arrests across Mississippi, Alabama, Georgia, and the Carolinas, 1963-1968.';

foreach ([
    [
        'p' => ['name' => 'Fannie Lou Hamer', 'first_name' => 'Fannie', 'middle_name' => 'Lou', 'last_name' => 'Hamer',
            'birthdate' => '1917-10-06', 'death_date' => '1977-03-14', 'gender' => 'Female', 'race' => 'Black',
            'state' => 'Mississippi', 'affiliation' => ['SNCC', 'Mississippi Freedom Democratic Party'],
            'ideologies' => ['Civil rights', 'Voting rights', 'Sharecropper organizing'],
            'era' => 'Civil Rights & Vietnam',
            'description' => "Fannie Lou Hamer was a Mississippi sharecropper who became one of the towering organizers of the civil rights era. Evicted from the Marlow plantation after attempting to register to vote in August 1962, she joined SNCC's voter-registration project. On June 9, 1963, returning from a citizenship-school workshop in Charleston, South Carolina, Hamer and fellow SNCC workers Annell Ponder, June Johnson, Lawrence Guyot, Euvester Simpson, James West, and Rosemary Freeman were arrested at the Trailways bus station in Winona, Mississippi. In Montgomery County jail they were brutally beaten by guards and prisoners directed by police; Hamer's beating left her with permanent kidney damage, a blood clot, and a damaged eye. Co-founder of the Mississippi Freedom Democratic Party, she gave the searing 1964 Democratic Convention testimony — 'Is this America?' — that pushed the national party to seat Black delegates."],
        'c' => ['institution' => ['name' => 'Montgomery County Jail (Winona, MS) — June 1963 SNCC beating', 'city' => 'Winona', 'state' => 'Mississippi'],
            'charges' => 'Disorderly conduct / refusing to leave segregated bus station — Winona, MS, June 9, 1963 (federal civil-rights prosecution against the police later resulted in acquittals)',
            'arrest_date' => '1963-06-09', 'sentence' => 'Several days county jail; severe beating in custody'],
    ],
    [
        'p' => ['name' => 'Annell Ponder', 'first_name' => 'Annell', 'last_name' => 'Ponder',
            'birthdate' => '1932', 'death_date' => '2005', 'gender' => 'Female', 'race' => 'Black',
            'state' => 'Mississippi', 'affiliation' => ['SCLC Citizenship Education Program', 'SNCC'],
            'ideologies' => ['Civil rights', 'Voter education'], 'era' => 'Civil Rights & Vietnam',
            'description' => "Annell Ponder was a SCLC Citizenship Education Program field secretary who trained voter-registration teachers across the Deep South alongside Septima Clark and Dorothy Cotton. Returning from a citizenship workshop in Charleston, South Carolina on June 9, 1963, she was arrested with Fannie Lou Hamer, June Johnson, Lawrence Guyot and others at the Winona, Mississippi Trailways bus station. In Montgomery County jail she was brutally beaten by Highway Patrol officers and prisoners; her face was so swollen she could barely speak. The beatings produced national outcry and a federal Justice Department prosecution of the officers — who were acquitted by an all-white jury. Ponder continued civil-rights organizing until her death."],
        'c' => ['institution' => ['name' => 'Montgomery County Jail (Winona, MS) — June 1963 SNCC beating', 'city' => 'Winona', 'state' => 'Mississippi'],
            'charges' => 'Disorderly conduct — Winona, MS, June 9, 1963',
            'arrest_date' => '1963-06-09', 'sentence' => 'Several days; severe beating in custody'],
    ],
    [
        'p' => ['name' => 'June Johnson', 'first_name' => 'June', 'last_name' => 'Johnson',
            'birthdate' => '1947-12-31', 'death_date' => '2007-04-15', 'gender' => 'Female', 'race' => 'Black',
            'state' => 'Mississippi', 'affiliation' => ['SNCC', 'Mississippi Freedom Democratic Party'],
            'ideologies' => ['Civil rights', 'Voting rights'], 'era' => 'Civil Rights & Vietnam',
            'description' => "June Johnson was a 15-year-old Greenwood, Mississippi SNCC volunteer when she was arrested on June 9, 1963 at the Winona Trailways bus station with Fannie Lou Hamer, Annell Ponder, and others returning from a citizenship workshop. Beaten by officers in Montgomery County jail despite her age — they struck her with blackjacks until her hair fell out — Johnson became a lifelong civil-rights organizer. She continued with SNCC and the MFDP through the 1964 Freedom Summer and Atlantic City convention, and served as a school board member and longtime activist in Washington, D.C."],
        'c' => ['institution' => ['name' => 'Montgomery County Jail (Winona, MS) — June 1963 SNCC beating', 'city' => 'Winona', 'state' => 'Mississippi'],
            'charges' => 'Disorderly conduct — Winona, MS, June 9, 1963',
            'arrest_date' => '1963-06-09', 'sentence' => 'Several days county jail; severe beating in custody at age 15'],
    ],
    [
        'p' => ['name' => 'Lawrence Guyot', 'first_name' => 'Lawrence', 'last_name' => 'Guyot',
            'birthdate' => '1939-07-17', 'death_date' => '2012-11-22', 'gender' => 'Male', 'race' => 'Black',
            'state' => 'Mississippi', 'affiliation' => ['SNCC', 'Mississippi Freedom Democratic Party'],
            'ideologies' => ['Civil rights', 'Voting rights'], 'era' => 'Civil Rights & Vietnam',
            'description' => "Lawrence Guyot was a Tougaloo College student and SNCC field organizer who became chairman of the Mississippi Freedom Democratic Party from its 1964 founding. Arrested with Fannie Lou Hamer, Annell Ponder, and June Johnson at the Winona Trailways bus station on June 9, 1963, he was beaten in Montgomery County jail. Across his civil-rights career he was arrested an estimated 100+ times in Mississippi for voter-registration work. He led the MFDP's 1964 challenge to the all-white Mississippi delegation at the Atlantic City Democratic Convention and continued political organizing in Washington, D.C. until his death."],
        'c' => ['institution' => ['name' => 'Montgomery County Jail (Winona, MS) — June 1963 SNCC beating', 'city' => 'Winona', 'state' => 'Mississippi'],
            'charges' => 'Voter-registration organizing arrests across Mississippi (~100+ arrests); disorderly conduct, Winona, June 9, 1963',
            'arrest_date' => '1963-06-09', 'sentence' => 'Multiple short terms; severe beating in Winona custody'],
    ],
    [
        'p' => ['name' => 'Bob Moses', 'first_name' => 'Robert', 'middle_name' => 'Parris', 'last_name' => 'Moses',
            'birthdate' => '1935-01-23', 'death_date' => '2021-07-25', 'gender' => 'Male', 'race' => 'Black',
            'state' => 'Mississippi', 'affiliation' => ['SNCC', 'Council of Federated Organizations (COFO)'],
            'ideologies' => ['Civil rights', 'Voting rights', 'Anti-war'], 'era' => 'Civil Rights & Vietnam',
            'description' => "Robert P. 'Bob' Moses was a Hamilton College and Harvard-trained mathematician who joined SNCC in 1960 and became the architect of the SNCC Mississippi voter-registration project. Beginning in McComb in 1961, he was repeatedly arrested, beaten, and jailed for organizing — arrested by Sheriff Billy Jack Caston in Liberty, Mississippi (August 1961) after one of the first SNCC voter-registration drives, and many times subsequently. He directed Council of Federated Organizations (COFO) and the 1964 Mississippi Freedom Summer. After Atlantic City he turned against the Vietnam War; threatened with the draft, he left for Tanzania in 1966, returning in 1976. He founded the Algebra Project, building math literacy as a civil right, until his death in 2021."],
        'c' => ['institution' => ['name' => 'Pike County Jail / Magnolia (McComb, MS) — SNCC voter-registration arrests', 'city' => 'Magnolia', 'state' => 'Mississippi'],
            'charges' => 'Disturbing the peace; conspiracy to violate state laws — voter-registration organizing, McComb / Liberty MS area, 1961-1964',
            'arrest_date' => '1961-08', 'sentence' => 'Multiple county-jail terms (days to weeks each)'],
    ],
    [
        'p' => ['name' => 'James Bevel', 'first_name' => 'James', 'middle_name' => 'Luther', 'last_name' => 'Bevel',
            'birthdate' => '1936-10-19', 'death_date' => '2008-12-19', 'gender' => 'Male', 'race' => 'Black',
            'state' => 'Alabama', 'affiliation' => ['SCLC', 'Nashville Student Movement', 'SNCC'],
            'ideologies' => ['Civil rights', 'Nonviolence', 'Anti-war'], 'era' => 'Civil Rights & Vietnam',
            'description' => "Reverend James Bevel was an SCLC field secretary and a principal strategist of the 1963 Birmingham Children's Crusade, the 1964 Mississippi Freedom Summer, the 1965 Selma-to-Montgomery march, and the 1966 Chicago open-housing campaign. Trained at the American Baptist Theological Seminary alongside John Lewis and Bernard Lafayette, Bevel was arrested at sit-ins and demonstrations from the 1960 Nashville lunch-counter sit-ins forward. He served numerous short jail terms across the Deep South for civil disobedience, was a co-strategist of King's anti-Vietnam War turn in 1967, and helped organize the 1968 Poor People's Campaign in Washington."],
        'c' => ['institution' => ['name' => 'Selma / Birmingham city jails — SCLC organizing arrests', 'city' => 'Birmingham', 'state' => 'Alabama'],
            'charges' => $crCharges, 'arrest_date' => '1963-04', 'sentence' => 'Multiple short county-jail terms'],
    ],
    [
        'p' => ['name' => 'James Lawson', 'first_name' => 'James', 'middle_name' => 'Morris', 'last_name' => 'Lawson',
            'birthdate' => '1928-09-22', 'death_date' => '2024-06-09', 'gender' => 'Male', 'race' => 'Black',
            'state' => 'Tennessee', 'affiliation' => ['Fellowship of Reconciliation', 'SCLC', 'Nashville Student Movement'],
            'ideologies' => ['Christian nonviolence', 'Civil rights', 'Anti-war'], 'era' => 'Civil Rights & Vietnam',
            'description' => "Reverend James Lawson was the United States's foremost teacher of Gandhian nonviolent direct action. A draft refuser during the Korean War, he served 13 months in federal prison (1951-1952) and then traveled to India to study satyagraha. Returning to the U.S., he led the Nashville workshops that trained John Lewis, Diane Nash, James Bevel, Bernard Lafayette, and a generation of SNCC and SCLC organizers in nonviolence. Expelled from Vanderbilt Divinity School in 1960 for his role in the Nashville sit-ins, Lawson invited Martin Luther King to Memphis in 1968 to support the sanitation workers' strike — the trip that ended in King's assassination. Across the LBJ era he was arrested at sit-ins, Freedom Rides, and demonstrations from Nashville to Memphis."],
        'c' => ['institution' => ['name' => 'Nashville / Memphis city jails — Civil rights organizing', 'city' => 'Nashville', 'state' => 'Tennessee'],
            'charges' => $crCharges . ' Earlier Korean War draft refusal: 13 months federal prison, 1951-1952.',
            'arrest_date' => '1960-02-27', 'sentence' => 'Multiple short county-jail terms during 1960s civil-rights organizing'],
    ],
    [
        'p' => ['name' => 'Bernard Lafayette', 'first_name' => 'Bernard', 'last_name' => 'Lafayette',
            'birthdate' => '1940-07-29', 'gender' => 'Male', 'race' => 'Black',
            'state' => 'Alabama', 'affiliation' => ['SNCC', 'SCLC', 'Nashville Student Movement'],
            'ideologies' => ['Civil rights', 'Nonviolence'], 'era' => 'Civil Rights & Vietnam',
            'description' => "Bernard Lafayette Jr. was a co-founder of SNCC, a Nashville Student Movement organizer trained by James Lawson, and an original Freedom Rider. Beaten unconscious during the May 1961 Montgomery Freedom Rides, he was arrested with the rest of the riders in Jackson, Mississippi and served time in Parchman Farm. He directed SNCC's Selma project beginning in 1962, surviving an assassination attempt outside his apartment in June 1963 the same night Medgar Evers was murdered. Across the LBJ era he was arrested repeatedly while organizing in Selma, Alabama and beyond, becoming national program director of SCLC's 1968 Poor People's Campaign after King's assassination."],
        'c' => ['institution' => ['name' => 'Mississippi State Penitentiary, Parchman Farm — Freedom Riders', 'city' => 'Parchman', 'state' => 'Mississippi'],
            'charges' => 'Breach of peace — 1961 Freedom Rides (Jackson, MS); civil-rights demonstrations, Selma, Alabama, 1962-1965',
            'arrest_date' => '1961-05', 'sentence' => 'Multiple short jail terms (Freedom Rides + Selma organizing)'],
    ],
    [
        'p' => ['name' => 'Diane Nash', 'first_name' => 'Diane', 'last_name' => 'Nash',
            'birthdate' => '1938-05-15', 'gender' => 'Female', 'race' => 'Black',
            'state' => 'Tennessee', 'affiliation' => ['SNCC', 'SCLC', 'Nashville Student Movement'],
            'ideologies' => ['Civil rights', 'Nonviolence'], 'era' => 'Civil Rights & Vietnam',
            'description' => "Diane Nash, a Fisk University student in 1959, became the chair of the Nashville Student Movement, leader of the 1960 lunch-counter sit-ins, and one of the founders of SNCC. After Klan violence forced CORE to abandon the Freedom Rides in May 1961, she organized Nashville students to continue the rides into Mississippi, and was repeatedly arrested. In April 1962, four months pregnant, she refused to post bond and chose to serve a Mississippi jail sentence rather than cooperate with segregated courts. She co-led SCLC's 1965 Selma campaign and the Alabama Project that drove the Voting Rights Act."],
        'c' => ['institution' => ['name' => 'Hinds County Jail (Jackson, MS) — Diane Nash 1962', 'city' => 'Jackson', 'state' => 'Mississippi'],
            'charges' => 'Contributing to the delinquency of minors (teaching nonviolence to Mississippi students under 21) — 1962 Mississippi conviction',
            'arrest_date' => '1962-04', 'sentence' => '10 days county jail (1962); multiple sit-in arrests prior'],
    ],
] as $row) {
    [$prisoner, $created] = addP($row['p']);
    $tot[$created ? 'created' : 'existed']++;
    if (attachC($prisoner, $row['c'])) $tot['cases']++;
}

// ---------------------------------------------------------------------------
// 7. INDIGENOUS TREATY-RIGHTS / PNW FISH-INS
// ---------------------------------------------------------------------------

[$adams, $created] = addP([
    'name' => 'Hank Adams', 'first_name' => 'Henry', 'middle_name' => 'Lyle', 'last_name' => 'Adams',
    'birthdate' => '1943-05-16', 'death_date' => '2020-12-21', 'gender' => 'Male', 'race' => 'Native American',
    'state' => 'Washington', 'affiliation' => ['National Indian Youth Council', 'Survival of American Indians Association'],
    'ideologies' => ['Indigenous sovereignty', 'Treaty rights', 'Native self-determination'], 'era' => 'Civil Rights & Vietnam',
    'description' => "Hank Adams was an Assiniboine-Sioux activist born on the Fort Peck Reservation who became one of the most effective Indigenous strategists of the twentieth century. He left the University of Washington on November 22, 1963 — the day Kennedy was assassinated — to organize full-time, leading the March 3, 1964 march on the Washington state capitol that drew 1,000 people protesting state suppression of Native treaty fishing rights. He worked closely with Billy Frank Jr. at Frank's Landing on the Nisqually River, was arrested repeatedly between 1968 and 1971 at fish-ins, and in January 1971 was shot in the stomach by an unknown assailant while guarding a fishing site. His organizing helped produce the 1974 Boldt Decision affirming tribal treaty fishing rights.",
]);
$tot[$created ? 'created' : 'existed']++;
if (attachC($adams, [
    'institution' => ['name' => 'Washington state and county jails — Pacific Northwest fish-ins', 'city' => 'Olympia', 'state' => 'Washington'],
    'charges' => 'Illegal fishing in violation of Washington state regulations (multiple counts) — 1968-1971 treaty-rights fish-ins',
    'arrest_date' => '1968', 'sentence' => 'Multiple short jail terms', 'convicted' => 'Yes (multiple)',
])) $tot['cases']++;

[$bfj, $created] = addP([
    'name' => 'Billy Frank Jr.', 'first_name' => 'Billy', 'last_name' => 'Frank',
    'birthdate' => '1931-03-09', 'death_date' => '2014-05-05', 'gender' => 'Male', 'race' => 'Native American',
    'state' => 'Washington', 'affiliation' => ['Survival of American Indians Association', 'Northwest Indian Fisheries Commission'],
    'ideologies' => ['Indigenous sovereignty', 'Treaty rights', 'Environmental stewardship'], 'era' => 'Civil Rights & Vietnam',
    'description' => "Billy Frank Jr. was a Nisqually fisherman and treaty-rights organizer first arrested in December 1945 at age 14 for fishing in the Nisqually River. He went on to be arrested more than fifty times through the 1960s and 1970s in defense of fishing rights guaranteed by the 1854 Treaty of Medicine Creek. From his family's six-acre property, Frank's Landing, he and Hank Adams organized the iconic LBJ-era 'fish-ins' modeled on the civil rights sit-ins, drawing the support of Marlon Brando (arrested 1964) and Dick Gregory. The arrests and protests culminated in the 1974 Boldt Decision (United States v. Washington), which restored Pacific Northwest tribes' rights to half the salmon harvest. Frank was awarded the Presidential Medal of Freedom posthumously in 2015.",
]);
$tot[$created ? 'created' : 'existed']++;
if (attachC($bfj, [
    'institution' => ['name' => 'Washington state and county jails — Pacific Northwest fish-ins', 'city' => 'Olympia', 'state' => 'Washington'],
    'charges' => 'Illegal fishing in violation of Washington state regulations (50+ counts over decades) — Treaty of Medicine Creek defense',
    'arrest_date' => '1945-12', 'sentence' => 'Multiple short jail terms over 30+ years', 'convicted' => 'Yes (multiple)',
])) $tot['cases']++;

[$mccloud, $created] = addP([
    'name' => 'Janet McCloud', 'first_name' => 'Janet', 'last_name' => 'McCloud', 'aka' => 'Yet-Si-Blue',
    'birthdate' => '1934-03-30', 'death_date' => '2003-11-25', 'gender' => 'Female', 'race' => 'Native American',
    'state' => 'Washington', 'affiliation' => ['Survival of American Indians Association', 'Indigenous Women\'s Network'],
    'ideologies' => ['Indigenous sovereignty', 'Treaty rights', 'Indigenous feminism'], 'era' => 'Civil Rights & Vietnam',
    'description' => "Janet McCloud, born on the Tulalip Reservation and a descendant of Chief Seattle, was a Tulalip-Nisqually fishing-rights leader at Frank's Landing who organized the early 1960s fish-ins alongside her husband Don McCloud, Hank Adams, and Billy Frank Jr. At a 1965 fish-in on the Nisqually River she and five others — including her husband — were arrested; she served six days and conducted a hunger strike in jail. She published the underground newspaper 'Survival News,' co-founded the Survival of American Indians Association, and later founded the Indigenous Women's Network from her Yelm, Washington home, which she named Sapa Dawn Center. She died in 2003.",
]);
$tot[$created ? 'created' : 'existed']++;
if (attachC($mccloud, [
    'institution' => ['name' => 'Washington state and county jails — Pacific Northwest fish-ins', 'city' => 'Olympia', 'state' => 'Washington'],
    'charges' => 'Illegal fishing in violation of Washington state regulations — 1965 Nisqually fish-in',
    'arrest_date' => '1965', 'sentence' => '6 days county jail (1965 conviction); hunger strike',
    'convicted' => 'Yes',
])) $tot['cases']++;

// ---------------------------------------------------------------------------
// 8. ANTI-McCARTHY / CIVIL LIBERTIES
// ---------------------------------------------------------------------------

[$wilkinson, $created] = addP([
    'name' => 'Frank Wilkinson', 'first_name' => 'Frank', 'last_name' => 'Wilkinson',
    'birthdate' => '1914-08-16', 'death_date' => '2006-01-02', 'gender' => 'Male', 'race' => 'White',
    'state' => 'Pennsylvania',
    'affiliation' => ['National Committee to Abolish HUAC', 'National Committee Against Repressive Legislation'],
    'ideologies' => ['Civil liberties', 'Anti-McCarthyism', 'First Amendment absolutism'],
    'era' => 'Civil Rights & Vietnam',
    'description' => "Frank Wilkinson was a former Los Angeles public housing administrator fired in 1952 after refusing to answer questions about his political associations. He went on to lead the national campaign to abolish the House Un-American Activities Committee (HUAC), founding the National Committee to Abolish HUAC in 1960. Subpoenaed before HUAC in Atlanta on July 30, 1958, he refused to answer questions, citing the First Amendment. The U.S. Supreme Court upheld his contempt-of-Congress conviction 5-4 in Wilkinson v. United States, 365 U.S. 399 (1961). Beginning in May 1961 he served nine months at the federal penitentiary in Lewisburg, Pennsylvania. His imprisonment fell in the Kennedy-LBJ transition, and he organized civil-liberties efforts throughout the LBJ era. The FBI maintained a 132,000-page file on him.",
]);
$tot[$created ? 'created' : 'existed']++;
if (attachC($wilkinson, [
    'institution' => ['name' => 'USP Lewisburg — HUAC contempt prosecutions', 'city' => 'Lewisburg', 'state' => 'Pennsylvania'],
    'charges' => 'Contempt of Congress (2 U.S.C. § 192) — refusing to answer HUAC questions, Atlanta hearing July 30, 1958. Wilkinson v. United States, 365 U.S. 399 (1961, 5-4).',
    'arrest_date' => '1958-07-30', 'sentence' => '1 year (served 9 months)', 'convicted' => 'Yes',
    'release_date' => '1962-02',
])) $tot['cases']++;

// ---------------------------------------------------------------------------
echo "\nLBJ-era prisoners load complete.\n";
echo sprintf("  Created:  %d new prisoners\n", $tot['created']);
echo sprintf("  Existed:  %d prisoners (skipped — already in DB)\n", $tot['existed']);
echo sprintf("  Cases:    %d cases attached\n", $tot['cases']);
echo "\n";
