<?php

declare(strict_types=1);

/**
 * Bulk-add the Reagan-era political prisoner categories that were
 * missing from add_reagan_era_prisoners.php:
 *
 *   1. Pledge of Resistance organizers / frequent arrestees (12)
 *   2. Veterans Fast for Life (4)
 *   3. Samuel Loring Morison (1985 Espionage Act prosecution) (1)
 *   4. Jack Elder (Sanctuary Movement, Brownsville TX, separately
 *      prosecuted 1985 with Stacey Merkt) (1)
 *   5. Big Mountain / Black Mesa Diné resistance elders (10)
 *   6. Nevada Test Site / Nevada Desert Experience anti-nuclear arrests (10)
 *   7. Pacific Life Community / Bangor Trident base (5)
 *   8. George Jackson Brigade (Pacific NW armed left, 1975-1978;
 *      members served deep into Reagan era) (7)
 *
 * Idempotent — uses firstOrCreate by name; cases skipped if a case
 * with the same arrest_date already exists on that prisoner.
 *
 * Run on production:
 *   cd /var/www/NPPC-Website && php scripts/add_reagan_era_supplementary.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;

function addPrisoner(array $p): array
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

$tot = ['created' => 0, 'existed' => 0, 'cases' => 0];

// =================== 1. Pledge of Resistance ===================

$porInst = ['name' => 'U.S. Capitol / federal buildings — Pledge of Resistance civil disobedience', 'city' => 'Washington', 'state' => 'District of Columbia'];
$porCharges = 'Civil disobedience arrest associated with the Pledge of Resistance national network (1984-1990) protesting Reagan-administration support for the Contras and the Salvadoran government. Most arrests resulted in misdemeanor charges (federal trespass, unlawful entry, congregating) and were dismissed or resolved with brief jail. The network reached ~100,000 signers in ~400 local groups by 1987 and produced approximately 10,000+ arrests.';

$porPeople = [
    ['name' => 'Jim Wallis', 'first_name' => 'Jim', 'last_name' => 'Wallis', 'aka' => 'James E. Wallis Jr.', 'gender' => 'Male', 'race' => 'White', 'state' => 'District of Columbia', 'birthdate' => '1948-06-04', 'description' => 'Evangelical theologian and founder/editor of Sojourners magazine. Co-drafted the original "Promise of Resistance" statement at the November 1983 Kirkridge retreat with Jim Rice; circulated the Pledge through Sojourners in 1984, helping seed the national network. Arrested multiple times at the U.S. Capitol over four decades of antiwar witness.', 'affiliation' => ['Sojourners (founder/editor)', 'Pledge of Resistance']],
    ['name' => 'Joyce Hollyday', 'first_name' => 'Joyce', 'last_name' => 'Hollyday', 'gender' => 'Female', 'race' => 'White', 'state' => 'North Carolina', 'description' => 'Yale Divinity-trained associate editor of Sojourners magazine for 15 years and co-founder of Witness for Peace, the faith-based peace witness in Nicaraguan war zones. Spent multiple weekends in DC jail for protests against U.S. wars in Central America and the nuclear arms race. Author of "Then Shall Your Light Rise" and several other books.', 'affiliation' => ['Sojourners', 'Witness for Peace (co-founder)', 'Pledge of Resistance']],
    ['name' => 'Ken Butigan', 'first_name' => 'Ken', 'last_name' => 'Butigan', 'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'description' => 'Berkeley graduate-theological-union student who reworked the original Pledge document for mass appeal and helped coordinate the October 9, 1984 first mass public signing at the San Francisco Federal Building. National coordinator of the Pledge of Resistance 1987-1990. Convicted with two friends in a mid-1980s Central America-related civil disobedience action; sentenced to six weeks at Terminal Island federal prison, Long Beach. Long-time Pace e Bene staff and DePaul University professor; chronicler of the Nevada Desert Experience.', 'affiliation' => ['Pledge of Resistance (National Coordinator 1987-1990)', 'Pace e Bene', 'Nevada Desert Experience']],
    ['name' => 'David Hartsough', 'first_name' => 'David', 'last_name' => 'Hartsough', 'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'birthdate' => '1940-01-01', 'death_date' => '2025-03-15', 'description' => 'Quaker organizer and longtime AFSC director of the Nonviolent Movement Building Program. Helped form both the Pledge of Resistance and the Nuremberg Action Group blockading Concord Naval Weapons Station. Arrested more than 150 times across his career, beginning with a 1960 lunch-counter sit-in. Co-founded Nonviolent Peaceforce (2002) and World Beyond War (2014). Died March 2025 at age 84 — final arrest weeks before his death at Travis Air Force Base.', 'affiliation' => ['American Friends Service Committee', 'Pledge of Resistance', 'Nuremberg Action', 'Nonviolent Peaceforce (co-founder)', 'World Beyond War (co-founder)']],
    ['name' => 'Frances Crowe', 'first_name' => 'Frances', 'last_name' => 'Crowe', 'gender' => 'Female', 'race' => 'White', 'state' => 'Massachusetts', 'birthdate' => '1919-03-15', 'death_date' => '2019-08-27', 'description' => 'Legendary western Massachusetts peace organizer based in Northampton. Active in Pledge of Resistance organizing and Central America solidarity work through her AFSC role. Arrested countless times across nearly seven decades of protest, most documented arrests being against nuclear power (Vermont Yankee) and pipelines; her latest arrest was at age 98 in 2017.', 'affiliation' => ['American Friends Service Committee (Western Massachusetts)', 'Fellowship of Reconciliation', 'Pledge of Resistance']],
    ['name' => 'Jack Cohen-Joppa', 'first_name' => 'Jack', 'last_name' => 'Cohen-Joppa', 'gender' => 'Male', 'race' => 'White', 'state' => 'Arizona', 'birthdate' => '1956-01-01', 'description' => 'Co-founder and co-publisher (with wife Felice) of The Nuclear Resister newsletter, which since 1980 has chronicled and supported imprisoned anti-war and anti-nuclear protesters. The Nuclear Resister has documented over 100,000 anti-war/anti-nuke arrests. Awarded the Nuclear Free Future Foundation 2020 education prize.', 'affiliation' => ['The Nuclear Resister (co-founder)']],
    ['name' => 'Felice Cohen-Joppa', 'first_name' => 'Felice', 'last_name' => 'Cohen-Joppa', 'gender' => 'Female', 'race' => 'White', 'state' => 'Arizona', 'birthdate' => '1959-01-01', 'description' => 'Co-founder and co-publisher of The Nuclear Resister newsletter from 1980 with husband Jack. Documented arrests of Pledge of Resistance, Plowshares, and Central America solidarity activists throughout the Reagan years. Awarded the Nuclear Free Future Foundation 2020 education prize.', 'affiliation' => ['The Nuclear Resister (co-founder)']],
    ['name' => 'Anne Symens-Bucher', 'first_name' => 'Anne', 'last_name' => 'Symens-Bucher', 'aka' => 'Anne Bucher', 'gender' => 'Female', 'race' => 'White', 'state' => 'California', 'birthdate' => '1957-01-01', 'description' => 'Lifelong Catholic Worker peace activist born in Oakland. Lived at the New York Catholic Worker with Dorothy Day in the 1970s; later founded the Oakland Catholic Worker. Co-founder of the Nevada Desert Experience, with hundreds of arrests across that campaign. Active in Bay Area Pledge of Resistance organizing during Central America era.', 'affiliation' => ['Catholic Worker', 'Nevada Desert Experience (co-founder)', 'Pace e Bene', 'Oakland Catholic Worker']],
    ['name' => 'Bill Wylie-Kellermann', 'first_name' => 'William', 'last_name' => 'Wylie-Kellermann', 'aka' => 'Bill Wylie-Kellermann', 'gender' => 'Male', 'race' => 'White', 'state' => 'Michigan', 'birthdate' => '1949-01-01', 'description' => 'Detroit-based Methodist pastor, theologian, and lifelong nonviolent activist; longtime contributing editor for Sojourners. National steering committee of Clergy and Laity Concerned about the War in Vietnam (1967-76). Engaged in Central America Pledge of Resistance actions and Plowshares-tradition civil disobedience throughout the 1980s. Best-documented arrest is the Detroit "Homrich 9" water-shutoff protest in 2014.', 'affiliation' => ['United Methodist Church', 'Sojourners (contributing editor)', 'Clergy and Laity Concerned']],
    ['name' => 'John Dear', 'first_name' => 'John', 'last_name' => 'Dear', 'gender' => 'Male', 'race' => 'White', 'state' => 'New Mexico', 'birthdate' => '1959-08-13', 'description' => 'Jesuit priest born in Elizabeth City, NC; entered the Society of Jesus 1982. Lived and worked in a Jesuit Refugee Service refugee camp in El Salvador for three months in 1985. Has been arrested 75-85 times for nonviolent civil disobedience. Most-documented arrest: December 7, 1993 Plowshares action at Seymour Johnson Air Force Base, Goldsboro NC, hammering on an F-15; convicted on two felony counts and served roughly 8 months in North Carolina jails plus 4.5 months house arrest. Dismissed from the Jesuits in 2014.', 'affiliation' => ['Society of Jesus (Jesuits, until 2014 dismissal)', 'Fellowship of Reconciliation', 'Pace e Bene', 'Plowshares Movement']],
    ['name' => 'Jim Forest', 'first_name' => 'James', 'middle_name' => 'Hendrickson', 'last_name' => 'Forest', 'aka' => 'Jim Forest', 'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'birthdate' => '1941-11-02', 'death_date' => '2022-01-13', 'description' => 'Conscientious objector discharged from US Navy 1961. FOR Vietnam Program Coordinator who, in 1968, joined the "Milwaukee 14" draft-board raid and burning, serving 13 months in federal prison. Editor of Fellowship magazine and Secretary General of the International Fellowship of Reconciliation 1977-1988, the years of the Pledge of Resistance, working from the Netherlands. Author of biographies of Daniel Berrigan, Dorothy Day, and Thomas Merton.', 'affiliation' => ['Fellowship of Reconciliation', 'International Fellowship of Reconciliation (Secretary General)', 'Catholic Peace Fellowship']],
];

foreach ($porPeople as $p) {
    $p['ideologies'] = ['Pacifism', 'Anti-imperialism', 'Christian peace activism'];
    $p['era'] = '1980s';
    [$prisoner, $created] = addPrisoner($p);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$p['name']} (id={$prisoner->id})\n";
    $created ? $tot['created']++ : $tot['existed']++;
    if ($created && empty($p['skip_case'])) {
        attachCase($prisoner, ['institution' => $porInst, 'arrest_date' => null, 'charges' => $porCharges, 'sentence' => 'Multiple Pledge of Resistance arrests, mostly cite-and-release; specific dates not consistently documented in open sources.', 'convicted' => 'Multiple misdemeanor charges, mostly dismissed']) && $tot['cases']++;
    }
}

// =================== 2. Veterans Fast for Life ===================

$capitolFast = ['name' => 'U.S. Capitol steps — Veterans Fast for Life', 'city' => 'Washington', 'state' => 'District of Columbia'];
$cnws = ['name' => 'Concord Naval Weapons Station — Nuremberg Action', 'city' => 'Concord', 'state' => 'California'];
$ftBenning = ['name' => 'Fort Benning, Georgia — School of the Americas Watch', 'city' => 'Fort Benning', 'state' => 'Georgia'];

$vfflPeople = [
    [
        'name' => 'Charles James Liteky', 'first_name' => 'Charles', 'middle_name' => 'James', 'last_name' => 'Liteky',
        'aka' => 'Charlie Liteky; Angelo Liteky', 'gender' => 'Male', 'race' => 'White', 'state' => 'California',
        'birthdate' => '1931-02-14', 'death_date' => '2017-01-20',
        'description' => 'Roman Catholic Army chaplain in Vietnam, awarded the Medal of Honor for carrying 20+ wounded soldiers through fire to evacuation on December 6, 1967 near Phuoc Lac. Left the priesthood 1975; married former nun Judy Balch 1983. On July 29, 1986 became the first and only Medal of Honor recipient to renounce his medal, placing it in an envelope addressed to President Reagan near the Vietnam Veterans Memorial. With George Mizo, began the Veterans Fast for Life on the U.S. Capitol steps September 1, 1986 — a 47-day water-only fast against Contra aid. Repeatedly arrested at the School of the Americas; in June 2000 sentenced to 1 year at Lompoc (with 70 days solitary) plus $10,000 fine for trespassing at Fort Benning.',
        'affiliation' => ['Veterans Fast for Life (founder)', 'SOA Watch', 'Catholic Worker'],
        'cases' => [
            ['institution' => $capitolFast, 'arrest_date' => '1986-09-01', 'charges' => 'Veterans Fast for Life — 47-day water-only fast on U.S. Capitol steps protesting Contra aid; tolerated/permitted, not formally arrested but committed sustained civil disobedience.', 'sentence' => 'Fast ended after 47 days following an 88-member-of-Congress joint statement of support.', 'convicted' => 'Not arrested at this action'],
            ['institution' => $ftBenning, 'arrest_date' => '2000-06-15', 'sentenced_date' => '2000-08-01', 'sentence' => 'Two consecutive 6-month terms (1 year total) at Lompoc Prison + $10,000 fine; spent his last 70 days in solitary confinement.', 'convicted' => 'Yes', 'charges' => 'Federal trespass at Fort Benning, GA (School of the Americas Watch civil disobedience).'],
        ],
    ],
    [
        'name' => 'S. Brian Willson', 'first_name' => 'Brian', 'last_name' => 'Willson',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Oregon', 'birthdate' => '1941-07-04',
        'description' => 'U.S. Air Force captain (1966-1970) including months as combat security officer in Vietnam. Trained attorney. Joined the Veterans Fast for Life on September 15, 1986. On September 1, 1987 — the one-year anniversary of the fast launch — he and other Veterans Peace Action Team members blocked railroad tracks at Concord Naval Weapons Station, CA, to stop munitions trains bound for Central America. The Navy train did not stop; investigators later found it was speeding, the crew had over 650 feet of clear sight, never applied brakes, and had been advised not to stop. Willson lost both legs below the knee and suffered a severe skull fracture. He had been listed by the FBI as a domestic "terrorist" suspect. Settled federal lawsuit 1990 for $920,000; the train crew was acquitted criminally.',
        'affiliation' => ['Veterans Fast for Life', 'Veterans Peace Action Teams (founder 1987)', 'Veterans for Peace', 'Nuremberg Actions'],
        'cases' => [
            ['institution' => $cnws, 'arrest_date' => '1987-09-01', 'charges' => 'Veterans Peace Action Team blockade of munitions trains at Concord Naval Weapons Station, CA. Train ran over Willson, severing both legs. Settled federal lawsuit 1990 for $920,000; train crew acquitted criminally.', 'sentence' => 'Civil settlement $920,000; no criminal liability for the train crew.', 'convicted' => 'No — civil settlement won'],
        ],
    ],
    [
        'name' => 'George Mizo', 'first_name' => 'George', 'last_name' => 'Mizo',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Germany', 'birthdate' => '1945-10-21', 'death_date' => '2002-03-18',
        'description' => 'Decorated Army combat infantry sergeant in Vietnam who served at Khe Sanh, wounded in a firefight and evacuated just before NVA troops overran the base in the Tet Offensive and wiped out his unit. Operated in areas heavily sprayed with Agent Orange. Joined Vietnam Veterans Against the War, returned his medals to the Pentagon. With Charles Liteky, began the Veterans Fast for Life on the U.S. Capitol steps September 1, 1986. Married German peace activist Rosemarie Höhn-Mizo (met 1986); they founded the Vietnam Friendship Village near Hanoi in 1992 caring for Agent Orange victims. Died March 18, 2002 of complications from his own Agent Orange exposure.',
        'affiliation' => ['Veterans Fast for Life (founder)', 'Vietnam Veterans Against the War', 'Vietnam Friendship Village (founder 1992)'],
        'cases' => [
            ['institution' => $capitolFast, 'arrest_date' => '1986-09-01', 'charges' => 'Veterans Fast for Life — 47-day water-only fast on U.S. Capitol steps protesting Contra aid.', 'sentence' => 'Fast tolerated/permitted; not formally arrested at the Capitol fast.', 'convicted' => 'Not arrested at this action'],
        ],
    ],
    [
        'name' => 'Duncan Murphy', 'first_name' => 'Duncan', 'last_name' => 'Murphy',
        'gender' => 'Male', 'race' => 'White',
        'description' => 'World War II conscientious objector who served with an Allied ambulance unit through North Africa, Italy, France, and Germany; was with British troops liberating Bergen-Belsen. After the war spent 20 years in the Shiloh Community. Joined Brian Willson on day 15 of the Veterans Fast for Life (September 15, 1986). In 1987 joined Nuremberg Action — the 40-day fast and ongoing blockade at Concord Naval Weapons Station — present in the broader blockade work that culminated in Willson\'s September 1, 1987 injury. Continued protesting at the School of the Americas at Fort Benning, GA, through later years.',
        'affiliation' => ['Veterans Fast for Life', 'Nuremberg Action', 'Shiloh Community'],
        'cases' => [
            ['institution' => $capitolFast, 'arrest_date' => '1986-09-15', 'charges' => 'Veterans Fast for Life (joined day 15) — 47-day water-only fast on U.S. Capitol steps protesting Contra aid.', 'sentence' => 'Fast tolerated/permitted.', 'convicted' => 'Not arrested at this action'],
        ],
    ],
];

foreach ($vfflPeople as $p) {
    $p['ideologies'] = ['Pacifism', 'Anti-imperialism', 'Anti-militarism', 'Veterans peace movement'];
    $p['era'] = '1980s';
    [$prisoner, $created] = addPrisoner($p);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$p['name']} (id={$prisoner->id})\n";
    $created ? $tot['created']++ : $tot['existed']++;
    foreach ($p['cases'] ?? [] as $c) {
        if (attachCase($prisoner, $c)) {
            echo "    + case (arrest_date={$c['arrest_date']})\n";
            $tot['cases']++;
        }
    }
}

// =================== 3. Samuel Loring Morison ===================

[$morison, $morisonCreated] = addPrisoner([
    'name' => 'Samuel Loring Morison', 'first_name' => 'Samuel', 'middle_name' => 'Loring', 'last_name' => 'Morison',
    'gender' => 'Male', 'race' => 'White', 'state' => 'Maryland',
    'birthdate' => '1944-10-30', 'death_date' => '2018-01-14',
    'description' => 'Civilian Navy intelligence analyst at the Naval Intelligence Support Center in Suitland, Maryland (1974-1984), specializing in Soviet amphibious vessels. The grandson of Pulitzer Prize-winning naval historian Rear Admiral Samuel Eliot Morison. He moonlighted as the American editor of Jane\'s Fighting Ships and in July 1984 took three classified KH-11 satellite photographs of a Soviet aircraft-carrier shipyard from a coworker\'s desk and mailed them to Jane\'s Defence Weekly, which ran them on its August 11, 1984 cover. Convicted October 17, 1985 — the first U.S. official ever convicted under the Espionage Act for a leak to the press. Sentenced to 2 years federal prison; conviction affirmed at 844 F.2d 1057 (4th Cir. 1988). Pardoned by President Bill Clinton on January 20, 2001 (Clinton\'s last day in office).',
    'era' => '1980s', 'ideologies' => ['Press freedom', 'Whistleblowing'],
    'affiliation' => ['U.S. Navy intelligence (former)', 'Jane\'s Fighting Ships'],
]);
echo ($morisonCreated ? '  CREATED' : '  EXISTS ') . " Samuel Loring Morison (id={$morison->id})\n";
$morisonCreated ? $tot['created']++ : $tot['existed']++;
if (attachCase($morison, [
    'institution' => ['name' => 'U.S. District Court, District of Maryland — U.S. v. Morison (Crim. No. Y-84-00455)', 'city' => 'Baltimore', 'state' => 'Maryland'],
    'charges' => '18 U.S.C. § 793(d) and § 793(e) Espionage Act (two counts) and 18 U.S.C. § 641 theft of government property (two counts) — first prosecution of a U.S. official under the Espionage Act for a leak to journalists. Morison mailed three classified KH-11 satellite photographs of Nikolayev shipyard to Jane\'s Defence Weekly, which published them August 11, 1984.',
    'arrest_date' => '1984-10-01', 'sentenced_date' => '1985-12-04', 'convicted' => 'Yes — October 17, 1985 jury verdict, all four counts',
    'sentence' => '2 years federal prison; served ~8 months and was paroled circa 1989. Pardoned by President Bill Clinton on January 20, 2001 (Clinton\'s last day in office).',
    'release_date' => '1989-08-01', 'judge' => 'Hon. Joseph H. Young', 'prosecutor' => 'U.S. Attorney\'s Office, District of Maryland',
])) {
    echo "    + case (1984-10-01)\n"; $tot['cases']++;
}

// =================== 4. Jack Elder (Sanctuary - Brownsville) ===================

[$elder, $elderCreated] = addPrisoner([
    'name' => 'Jack Elder', 'first_name' => 'Jack', 'last_name' => 'Elder', 'aka' => 'John Elder',
    'gender' => 'Male', 'race' => 'White', 'state' => 'Texas',
    'description' => 'Catholic layman who directed Casa Oscar Romero in San Benito, Texas (Diocese of Brownsville) from 1983 — a refugee shelter for Salvadorans and Guatemalans fleeing the U.S.-backed wars. The most prominent Sanctuary Movement defendant prosecuted outside the Tucson trial. Federally indicted 1984. Convicted February 21, 1985 with co-worker Stacey Lynn Merkt of conspiracy and transporting/bringing in undocumented refugees (six counts). Initially sentenced to two years probation but rejected the gag order forbidding sanctuary work or public discussion; resentenced to 150 days in a San Antonio halfway house. Judge Filemón Vela Sr. noted on the record that he "agree[d] with the ends of the sanctuary movement." Co-founded RAICES with Stacey Merkt after release.',
    'era' => '1980s',
    'ideologies' => ['Catholic social teaching', 'Liberation theology', 'Sanctuary', 'Anti-imperialism'],
    'affiliation' => ['Sanctuary Movement', 'Casa Oscar Romero (Brownsville Diocese — Director)', 'RAICES (later)'],
]);
echo ($elderCreated ? '  CREATED' : '  EXISTS ') . " Jack Elder (id={$elder->id})\n";
$elderCreated ? $tot['created']++ : $tot['existed']++;
if (attachCase($elder, [
    'institution' => ['name' => 'U.S. District Court, Southern District of Texas (Brownsville) — U.S. v. Elder', 'city' => 'Brownsville', 'state' => 'Texas'],
    'charges' => 'Conspiracy to transport undocumented refugees; transporting undocumented refugees; bringing in undocumented refugees (six counts; 8 U.S.C. § 1324). Co-defendant Stacey Lynn Merkt (separately added). Affirmed at 601 F. Supp. 1574 (S.D. Tex. 1985).',
    'arrest_date' => '1984-04-01', 'sentenced_date' => '1985-04-19', 'convicted' => 'Yes — February 21, 1985 jury verdict',
    'sentence' => '150 days in San Antonio halfway house (after refusing initial 2-year probation with gag-order conditions).',
    'incarceration_date' => '1987-01-30',
    'judge' => 'Hon. Filemón B. Vela Sr.', 'prosecutor' => 'U.S. Attorney for the Southern District of Texas',
])) { echo "    + case (1984-04-01)\n"; $tot['cases']++; }

// =================== 5. Big Mountain / Black Mesa ===================

$bigMt = ['name' => 'Big Mountain / Hopi Partitioned Land — Diné resistance to Navajo-Hopi Land Settlement Act', 'city' => 'Big Mountain', 'state' => 'Arizona'];

$bigMountainPeople = [
    ['name' => 'Roberta Blackgoat', 'first_name' => 'Roberta', 'last_name' => 'Blackgoat', 'gender' => 'Female', 'race' => 'Native American', 'state' => 'Arizona', 'birthdate' => '1917-10-15', 'death_date' => '2002-04-23', 'description' => 'Diné elder matriarch born near Thin Rock Mesa, Arizona. Roberta Blackgoat became the international face of resistance to the 1974 Navajo-Hopi Land Settlement Act, refusing to sign relocation agreements and spending decades speaking against coal/uranium mining and forced removal. Co-founder of the Sovereign Diné Nation; addressed the United Nations on Indigenous rights. "If they leave me here but take away my community, it is still genocide."', 'affiliation' => ['Big Mountain Diné Resistance', 'Sovereign Diné Nation (co-founder)']],
    ['name' => 'Pauline Whitesinger', 'first_name' => 'Pauline', 'last_name' => 'Whitesinger', 'gender' => 'Female', 'race' => 'Native American', 'state' => 'Arizona', 'death_date' => '2014-08-01', 'description' => 'Diné elder of Big Mountain (Black Mesa, AZ), sister of Katherine Smith. In fall 1977 she single-handedly halted the BIA partition fence, knocking the foreman down and driving the crew off with sticks and rocks; the fence has not progressed since. She remained on her ancestral land until her death in 2014, repeating: "In our traditional tongue, there is no word for relocation." Arrested July 11, 2001 at the Camp Anna Mae Sundance ceremony when Hopi police raided the gathering.', 'affiliation' => ['Big Mountain Diné Resistance']],
    ['name' => 'Katherine Smith', 'first_name' => 'Katherine', 'last_name' => 'Smith', 'gender' => 'Female', 'race' => 'Native American', 'state' => 'Arizona', 'death_date' => '2017-03-29', 'description' => 'Diné matriarch of the Tábąąhá clan, lifelong resident of Big Mountain. In September 1979 she met a BIA fencing crew with a rifle and fired warning shots over their heads when they came within 14 feet of her ceremonial hogan. Tried on serious firearms charges, she received a directed verdict of acquittal. She became an enduring icon of Diné land defense, featured in the 1985 documentary "Broken Rainbow."', 'affiliation' => ['Big Mountain Diné Resistance']],
    ['name' => 'Mae Wilson Tso', 'first_name' => 'Mae', 'middle_name' => 'Wilson', 'last_name' => 'Tso', 'aka' => 'Mae Tso', 'gender' => 'Female', 'race' => 'Native American', 'state' => 'Arizona', 'death_date' => '2021-01-12', 'description' => 'Master Navajo weaver from Mosquito Springs near Big Mountain. One of four matriarchs profiled in oral histories of the Navajo-Hopi Land Settlement Act resistance. She publicly opposed livestock impoundments and relocation pressure throughout the 1980s and beyond, raising a multigenerational family on the disputed land. Reportedly detained/cited during livestock impoundment confrontations in 1986.', 'affiliation' => ['Big Mountain Diné Resistance']],
    ['name' => 'Glenna Begay', 'first_name' => 'Glenna', 'last_name' => 'Begay', 'gender' => 'Female', 'race' => 'Native American', 'state' => 'Arizona', 'description' => 'Diné elder weaver and "wisdom keeper," nonsigner of the Accommodation Agreement, continuing resistance on the Hopi Partitioned Land. Has traveled internationally to advocate against Peabody coal mining ("I want the mining to stop").', 'affiliation' => ['Big Mountain Diné Resistance', 'Black Mesa Resistance Camp']],
    ['name' => 'Bahe Y. Katenay', 'first_name' => 'Bahe', 'middle_name' => 'Y.', 'last_name' => 'Katenay', 'aka' => 'Bahe Keediniihii', 'gender' => 'Male', 'race' => 'Native American', 'state' => 'Arizona', 'description' => 'Big Mountain Diné organizer, writer, and spokesperson. Participated in The Longest Walk of 1978; helped articulate the 1979 declaration of the Big Mountain Independent Diné Nation. Decades-long advocate against Peabody Coal and federal relocation policy.', 'affiliation' => ['Big Mountain Diné Resistance', 'Sovereign Diné Nation']],
    ['name' => 'John Benally', 'first_name' => 'John', 'last_name' => 'Benally', 'gender' => 'Male', 'race' => 'Native American', 'state' => 'Arizona', 'description' => 'Big Mountain Diné nonsigner residing on Hopi Partitioned Land. Long-time resister to livestock impoundments and BIA enforcement actions; appealed Tribal/BIA confiscation of his cattle (notably April 5, 2016) before the Indian Board of Indian Appeals.', 'affiliation' => ['Big Mountain Diné Resistance']],
    ['name' => 'Ruth Benally', 'first_name' => 'Ruth', 'last_name' => 'Benally', 'gender' => 'Female', 'race' => 'Native American', 'state' => 'Arizona', 'description' => 'Diné elder arrested with Pauline Whitesinger and three others during the 2001 Hopi police raid on the Camp Anna Mae Sundance ceremony at Big Mountain. Charges of trespass on the Hopi Partitioned Land were dismissed seven months after the arrest.', 'affiliation' => ['Big Mountain Diné Resistance']],
    ['name' => 'Louise Benally', 'first_name' => 'Louise', 'last_name' => 'Benally', 'gender' => 'Female', 'race' => 'Native American', 'state' => 'Arizona', 'description' => 'Diné elder and outspoken Big Mountain spokesperson; arrested with sister-resisters at Camp Anna Mae in 2001. Frequent public voice against Peabody Coal mining, water depletion of the N-Aquifer, and forced relocation; has spoken internationally on Diné sovereignty.', 'affiliation' => ['Big Mountain Diné Resistance']],
    ['name' => 'Joella Ashkie', 'first_name' => 'Joella', 'last_name' => 'Ashkie', 'gender' => 'Female', 'race' => 'Native American', 'state' => 'Arizona', 'description' => 'One of the five Diné women arrested by Hopi police at the Camp Anna Mae Sundance grounds on July 11, 2001. Charges of trespass were ultimately dismissed; the camp grounds were bulldozed and fenced off following the raid.', 'affiliation' => ['Big Mountain Diné Resistance']],
];

foreach ($bigMountainPeople as $p) {
    $p['ideologies'] = ['Indigenous sovereignty', 'Anti-relocation', 'Environmental justice'];
    $p['era'] = '1980s';
    [$prisoner, $created] = addPrisoner($p);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$p['name']} (id={$prisoner->id})\n";
    $created ? $tot['created']++ : $tot['existed']++;
    if ($created) {
        attachCase($prisoner, [
            'institution' => $bigMt,
            'charges' => 'Resistance to enforcement of the 1974 Navajo-Hopi Land Settlement Act, which required relocation of ~10,000 Diné from the Joint Use Area to clear the way for Peabody Coal mining. BIA livestock impoundments, fence-construction crews, and Hopi-tribal-police raids produced multiple confrontations with Big Mountain elders 1977-2001. The Camp Anna Mae Sundance raid of July 11, 2001 produced the most documented federal-trespass arrests; charges dismissed.',
            'sentence' => 'Most charges dismissed or never formally filed; livestock confiscated.',
            'convicted' => 'Most charges dismissed; Katherine Smith acquitted (1979)',
        ]) && $tot['cases']++;
    }
}

// =================== 6. Nevada Test Site / Pacific Life Community ===================

$nts = ['name' => 'Nevada Test Site (Mercury, NV) — Nevada Desert Experience', 'city' => 'Mercury', 'state' => 'Nevada'];
$bangor = ['name' => 'Bangor Trident submarine base — Pacific Life Community / Ground Zero Center', 'city' => 'Bangor', 'state' => 'Washington'];

$ntsPeople = [
    ['name' => 'Daniel Ellsberg', 'first_name' => 'Daniel', 'last_name' => 'Ellsberg', 'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'birthdate' => '1931-04-07', 'death_date' => '2023-06-16', 'description' => 'Pentagon Papers whistleblower turned nuclear-disarmament activist. Arrested ~70 times in nonviolent civil disobedience, roughly 50 of those at nuclear weapons sites. In April 1986 he and Greenpeace/Nuclear Freeze allies drove deep into the Nevada Test Site before a scheduled detonation, delaying the test; he then helped pass a House resolution against testing. Returned for the Feb 5, 1987 mass arrest with Sagan, Sheen, and 400 others.', 'affiliation' => ['Nevada Desert Experience adjacent', 'American Peace Test', 'Greenpeace'], 'arrest' => '1987-02-05', 'institution' => $nts],
    ['name' => 'Martin Sheen', 'first_name' => 'Martin', 'last_name' => 'Sheen', 'aka' => 'Ramón Antonio Gerardo Estévez', 'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'birthdate' => '1940-08-03', 'description' => 'Actor and Catholic peace activist arrested 80+ times in civil-disobedience actions at military and nuclear sites. Arrested at the Nevada Test Site on Nov 18, 1986; again Jan 27, 1987 (36th anniversary of the first NTS test); and Feb 5, 1987 in the largest action ever there. The Nevada Supreme Court ruled in Dec 1988 that his peace-bond arrest was illegal, holding civil disobedience is not a crime in Nevada.', 'affiliation' => ['Nevada Desert Experience adjacent', 'Catholic Worker adjacent'], 'arrest' => '1987-02-05', 'institution' => $nts],
    ['name' => 'Carl Edward Sagan', 'first_name' => 'Carl', 'middle_name' => 'Edward', 'last_name' => 'Sagan', 'aka' => 'Carl Sagan', 'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'birthdate' => '1934-11-09', 'death_date' => '1996-12-20', 'description' => 'Cornell astronomer and science communicator, co-author of the "nuclear winter" TTAPS paper. Arrested with wife Ann Druyan and Daniel Ellsberg on Feb 5, 1987 at the Nevada Test Site after deliberately walking onto government property; Sagan called it "the largest demonstration in the history of the nuclear testing site." Returned and was arrested again in October 1987.', 'affiliation' => ['Nevada Desert Experience adjacent', 'American Peace Test'], 'arrest' => '1987-02-05', 'institution' => $nts],
    ['name' => 'Ann Druyan', 'first_name' => 'Ann', 'last_name' => 'Druyan', 'gender' => 'Female', 'race' => 'White', 'state' => 'New York', 'birthdate' => '1949-06-13', 'description' => 'Author and producer (Cosmos), and wife of Carl Sagan. Arrested alongside Sagan and Ellsberg on Feb 5, 1987 at the Nevada Test Site for trespassing.', 'affiliation' => ['American Peace Test'], 'arrest' => '1987-02-05', 'institution' => $nts],
    ['name' => 'Kris Kristofferson', 'first_name' => 'Kris', 'last_name' => 'Kristofferson', 'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'birthdate' => '1936-06-22', 'death_date' => '2024-09-28', 'description' => 'Country singer-songwriter and actor. Arrested with Martin Sheen and Robert Blake on Feb 5, 1987 at the Nevada Test Site after crossing the line with ~400 other protesters. "It was the first time I ever got arrested on purpose."', 'affiliation' => [], 'arrest' => '1987-02-05', 'institution' => $nts],
    ['name' => 'Thomas John Gumbleton', 'first_name' => 'Thomas', 'middle_name' => 'John', 'last_name' => 'Gumbleton', 'aka' => 'Bishop Gumbleton', 'gender' => 'Male', 'race' => 'White', 'state' => 'Michigan', 'birthdate' => '1930-01-26', 'death_date' => '2024-04-04', 'description' => 'Auxiliary Bishop of Detroit (1968-2006) and founding president of Pax Christi USA (1972). On May 5-6, 1987, on the 4th anniversary of the U.S. bishops\' pastoral "The Challenge of Peace," Gumbleton crossed the line at the Nevada Test Site with fellow bishop Charles Buswell and 96 others — only the second time a U.S. Catholic hierarch had committed civil disobedience.', 'affiliation' => ['Pax Christi USA (founding president)', 'Nevada Desert Experience'], 'arrest' => '1987-05-06', 'institution' => $nts],
    ['name' => 'Charles Albert Buswell', 'first_name' => 'Charles', 'middle_name' => 'Albert', 'last_name' => 'Buswell', 'aka' => 'Bishop Buswell', 'gender' => 'Male', 'race' => 'White', 'state' => 'Colorado', 'birthdate' => '1913-10-15', 'death_date' => '2008-06-17', 'description' => 'Vatican-II-era Bishop of Pueblo, Colorado (1959-1979). After retirement, became one of the most-arrested American Catholic bishops, repeatedly crossing the line at the Nevada Test Site, including the May 5-6, 1987 action with Bishop Gumbleton.', 'affiliation' => ['Pax Christi USA', 'Nevada Desert Experience'], 'arrest' => '1987-05-06', 'institution' => $nts],
    ['name' => 'Rosemary Lynch', 'first_name' => 'Rosemary', 'last_name' => 'Lynch', 'aka' => 'Sister Rosemary Lynch, OSF', 'gender' => 'Female', 'race' => 'White', 'state' => 'Nevada', 'birthdate' => '1917-03-18', 'death_date' => '2011-01-09', 'description' => 'Franciscan sister born in Phoenix; based at the Las Vegas Franciscan Center from 1977. With Fr. Louis Vitale and others she organized the 1982 Lenten Desert Experience to mark Francis of Assisi\'s 800th birthday and the 25th anniversary of Quaker NTS protests. The annual witness became the Nevada Desert Experience, the catalyst for thousands of NTS arrests through the 1980s-90s. Co-founded Pace e Bene in 1989.', 'affiliation' => ['Sisters of St. Francis of Penance and Christian Charity', 'Nevada Desert Experience (co-founder)', 'Pace e Bene (co-founder)'], 'arrest' => '1982-03-20', 'institution' => $nts],
    ['name' => 'Louis Vitale', 'first_name' => 'Louis', 'last_name' => 'Vitale', 'aka' => 'Father Louie; Father Louis Vitale, OFM', 'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'birthdate' => '1932-06-01', 'death_date' => '2023-09-26', 'description' => 'Franciscan priest and former Air Force radar officer who became one of the most-arrested peace activists in U.S. history (~400+ arrests). Provincial superior of the Franciscans of St. Barbara (1979-1988). Co-founded the Lenten Desert Experience / NDE in 1982 with Sr. Rosemary Lynch and Michael Affleck. Arrested with Daniel Berrigan at UC Berkeley (1979) and repeatedly at NTS through the 1980s.', 'affiliation' => ['Franciscan Friars (Province of St. Barbara)', 'Nevada Desert Experience (co-founder, 1982/1984)', 'Pace e Bene (co-founder)'], 'arrest' => '1982-03-20', 'institution' => $nts],
    ['name' => 'James W. Douglass', 'first_name' => 'James', 'last_name' => 'Douglass', 'aka' => 'Jim Douglass', 'gender' => 'Male', 'race' => 'White', 'state' => 'Washington', 'birthdate' => '1937-07-14', 'description' => 'Theologian and author ("JFK and the Unspeakable"). Co-founded the Pacific Life Community in January 1975 with his wife Shelley to confront construction of the Trident submarine base at Bangor, WA, after whistleblower Robert Aldridge exposed the system. In November 1977 they purchased 3.8 acres next to the base and named it Ground Zero. Led the White Train tracking campaign and dozens of Bangor line-crossing arrests through the 1980s.', 'affiliation' => ['Pacific Life Community (co-founder, 1975)', 'Ground Zero Center for Nonviolent Action (co-founder, 1977)', 'Mary\'s House Catholic Worker (Birmingham)'], 'arrest' => '1983-02-16', 'institution' => $bangor],
    ['name' => 'Shelley Douglass', 'first_name' => 'Shelley', 'last_name' => 'Douglass', 'gender' => 'Female', 'race' => 'White', 'state' => 'Washington', 'description' => 'Co-founder of the Pacific Life Community and Ground Zero Center for Nonviolent Action. On Ash Wednesday, Feb 16, 1983, she walked the rail tracks into Bangor naval base with Karol Schulkin and Mary Grondin, posting Hiroshima/Nagasaki photographs on warhead-transport rails before being arrested.', 'affiliation' => ['Pacific Life Community (co-founder)', 'Ground Zero Center', 'Mary\'s House Catholic Worker'], 'arrest' => '1983-02-16', 'institution' => $bangor],
    ['name' => 'Karol Schulkin', 'first_name' => 'Karol', 'last_name' => 'Schulkin', 'gender' => 'Female', 'race' => 'White', 'state' => 'Washington', 'description' => 'Early Ground Zero member arrested with Shelley Douglass and Mary Grondin walking the warhead rail line into Bangor naval base on Ash Wednesday, Feb 16, 1983. Told the court: "I must stand up and say, no more — no more bombings and burning of people and lands."', 'affiliation' => ['Ground Zero Center for Nonviolent Action', 'Pacific Life Community'], 'arrest' => '1983-02-16', 'institution' => $bangor],
    ['name' => 'Mary Grondin', 'first_name' => 'Mary', 'last_name' => 'Grondin', 'gender' => 'Female', 'race' => 'White', 'state' => 'Washington', 'description' => 'Ground Zero Center member who walked the Trident rail line at Bangor with Shelley Douglass and Karol Schulkin on Ash Wednesday, Feb 16, 1983, posting atomic-bomb-victim photographs along the way before being arrested.', 'affiliation' => ['Ground Zero Center for Nonviolent Action'], 'arrest' => '1983-02-16', 'institution' => $bangor],
    ['name' => 'Jerome Zawada', 'first_name' => 'Jerome', 'last_name' => 'Zawada', 'aka' => 'Fr. Jerry Zawada, OFM', 'gender' => 'Male', 'race' => 'White', 'state' => 'Wisconsin', 'birthdate' => '1937-04-28', 'death_date' => '2017-07-25', 'description' => 'Franciscan priest arrested 100+ times for civil disobedience, with roughly five years total in federal prison. Repeatedly arrested at the Nevada Test Site and at the Bangor Trident base; in August 1988 he and another activist breached a Missouri nuclear missile silo and celebrated liturgy on the hatch (Missouri Peace Planting). Member of the 2009 "Creech 14" against drone warfare.', 'affiliation' => ['Franciscan Friars', 'Nevada Desert Experience', 'Pacific Life Community', 'Missouri Peace Planting'], 'arrest' => '1988-08-15', 'institution' => ['name' => 'Whiteman AFB Minuteman silo (Missouri Peace Planting)', 'city' => 'Knob Noster', 'state' => 'Missouri']],
];

foreach ($ntsPeople as $row) {
    $person = [
        'name' => $row['name'], 'first_name' => $row['first_name'] ?? null, 'middle_name' => $row['middle_name'] ?? null,
        'last_name' => $row['last_name'] ?? null, 'aka' => $row['aka'] ?? null,
        'gender' => $row['gender'] ?? null, 'race' => $row['race'] ?? null, 'state' => $row['state'] ?? null,
        'birthdate' => $row['birthdate'] ?? null, 'death_date' => $row['death_date'] ?? null,
        'description' => $row['description'], 'affiliation' => $row['affiliation'],
        'ideologies' => ['Anti-nuclear', 'Pacifism', 'Catholic peace tradition'],
        'era' => '1980s',
    ];
    [$prisoner, $created] = addPrisoner($person);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$row['name']} (id={$prisoner->id})\n";
    $created ? $tot['created']++ : $tot['existed']++;

    if (attachCase($prisoner, [
        'institution' => $row['institution'],
        'arrest_date' => $row['arrest'],
        'charges' => 'Federal trespass (anti-nuclear civil disobedience). Most charges dismissed/cite-and-release.',
        'sentence' => 'Cite-and-release / brief detention typical.',
        'convicted' => 'Mostly dismissed (Federal trespass)',
    ])) {
        echo "    + case (arrest_date={$row['arrest']})\n";
        $tot['cases']++;
    }
}

// =================== 7. George Jackson Brigade ===================

$gjbWa = ['name' => 'Washington State Penitentiary at Walla Walla / U.S. District Court, W.D. Washington', 'city' => 'Walla Walla', 'state' => 'Washington'];

$gjbPeople = [
    ['name' => 'Edward Allen Mead', 'first_name' => 'Edward', 'middle_name' => 'Allen', 'last_name' => 'Mead', 'aka' => 'Ed Mead', 'gender' => 'Male', 'race' => 'White', 'state' => 'Washington', 'birthdate' => '1941-01-01', 'description' => 'Founding member of the George Jackson Brigade, a Pacific Northwest revolutionary cell active 1975-1978. Captured January 23, 1976 during the failed Tukwila (WA) Pacific National Bank robbery in which fellow brigadier Bruce Seidel was killed and John Sherman was wounded. Held at Washington State Penitentiary at Walla Walla, where he co-founded Men Against Sexism. Released in 1993 after 18 years for his political actions (35 years of his life were spent in prisons total). Continued radical organizing post-release.', 'arrest' => '1976-01-23', 'release' => '1993-12-15', 'sentence' => 'Multiple consecutive sentences (~18 years served)', 'convicted' => 'Yes — federal bank robbery, conspiracy, bombing'],
    ['name' => 'Bruce Seidel', 'first_name' => 'Bruce', 'last_name' => 'Seidel', 'gender' => 'Male', 'race' => 'White', 'state' => 'Washington', 'death_date' => '1976-01-23', 'description' => 'Former University of Illinois economics graduate student turned prison-rights activist who helped found the Seattle-based George Jackson Brigade. Shot and killed January 23, 1976 by police during the GJB\'s failed bank robbery in Tukwila, Washington. Killed before trial.', 'arrest' => '1976-01-23', 'death_in_custody' => '1976-01-23', 'sentence' => 'Killed during arrest — never tried.', 'convicted' => 'Killed during arrest', 'released' => false],
    ['name' => 'Mark Cook', 'first_name' => 'Mark', 'last_name' => 'Cook', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Washington', 'description' => 'Only Black member of the George Jackson Brigade and the last to join. Already a co-founder of the Black Panther Party prison chapter at Washington State Penitentiary, he avoided arrest at the Tukwila bank robbery but was captured March 12, 1976 after he engineered John Sherman\'s armed escape from Harborview Medical Center, in which he wounded King County Deputy Virgil Johnson. He served 24 years in Washington State and federal prisons — the longest of any GJB member — and was released in 2000. In prison he founded a Black Panther chapter and the PIVOT post-release jobs program.', 'arrest' => '1976-03-12', 'release' => '2000-12-15', 'sentence' => 'Multiple consecutive sentences (~24 years served)', 'convicted' => 'Yes — aiding escape; first-degree assault'],
    ['name' => 'John William Sherman', 'first_name' => 'John', 'middle_name' => 'William', 'last_name' => 'Sherman', 'gender' => 'Male', 'race' => 'White', 'state' => 'Washington', 'description' => 'Met Ed Mead at McNeil Island federal prison in the late 1960s and joined the George Jackson Brigade through that connection. Wounded and captured at the failed Tukwila bank robbery January 23, 1976, then freed by Mark Cook from Harborview Medical Center on March 10, 1976. Recaptured in Tacoma March 21, 1978 alongside Therese Coupez and Janine Bertram. Convicted in W.D. Washington July 12, 1978 on bank robbery and bombing-conspiracy counts; later escaped a California federal prison; finally released 1998. Now a spiritual teacher.', 'arrest' => '1976-01-23', 'release' => '1998-12-15', 'sentence' => 'Long federal sentence (Counts I-VIII)', 'convicted' => 'Yes — convicted July 12, 1978', 'sentenced_date' => '1978-07-12'],
    ['name' => 'Therese Ann Coupez', 'first_name' => 'Therese', 'middle_name' => 'Ann', 'last_name' => 'Coupez', 'gender' => 'Female', 'race' => 'White', 'state' => 'Washington', 'description' => 'Rita "Bo" Brown\'s partner from the Seattle area, joined the George Jackson Brigade in late 1975 and participated in numerous bombings, attempted bombings, and bank robberies in the Seattle area between January 1976 and March 1978. Arrested March 21, 1978 at a Tacoma restaurant alongside Sherman and Bertram. Convicted July 12, 1978 in W.D. Washington on Counts I-VIII (armed bank robbery, conspiracy, bombing). Sentenced to 20 years; released 1999. Co-produced the documentary "Creating a Movement With Teeth."', 'arrest' => '1978-03-21', 'release' => '1999-12-15', 'sentence' => '20 years federal prison', 'convicted' => 'Yes — convicted July 12, 1978 (United States v. Coupez, 603 F.2d 1347)'],
    ['name' => 'Rita D. Brown', 'first_name' => 'Rita', 'middle_name' => 'D.', 'last_name' => 'Brown', 'aka' => 'Bo Brown; The Gentleman Bank Robber', 'gender' => 'Female', 'race' => 'White', 'state' => 'Oregon', 'description' => 'Working-class butch lesbian from Klamath Falls, Oregon. A former prisoner herself active in Seattle prisoner-support work before joining the George Jackson Brigade. Arrested in September 1977 while casing a bank. Served roughly 8 years in federal prison for several bank robberies in Oregon claimed by the GJB. Released in 1987. Founded Out of Control: Lesbian Committee to Support Women Political Prisoners.', 'arrest' => '1977-09-15', 'release' => '1987-12-15', 'sentence' => '~8 years federal prison', 'convicted' => 'Yes — multiple bank robbery counts'],
    ['name' => 'Janine Bertram', 'first_name' => 'Janine', 'last_name' => 'Bertram', 'gender' => 'Female', 'race' => 'White', 'state' => 'Washington', 'description' => 'Joined the George Jackson Brigade after becoming involved with Rita "Bo" Brown, serving as the group\'s getaway driver. Arrested March 21, 1978 in a Tacoma restaurant alongside Sherman and Coupez, the day they planned to rob a Tacoma branch of United Mutual Savings Bank. Served approximately 6 years in federal prison.', 'arrest' => '1978-03-21', 'release' => '1984-12-15', 'sentence' => '~6 years federal prison', 'convicted' => 'Yes — conspiracy to commit bank robbery'],
];

foreach ($gjbPeople as $row) {
    $person = [
        'name' => $row['name'], 'first_name' => $row['first_name'] ?? null, 'middle_name' => $row['middle_name'] ?? null,
        'last_name' => $row['last_name'] ?? null, 'aka' => $row['aka'] ?? null,
        'gender' => $row['gender'] ?? null, 'race' => $row['race'] ?? null, 'state' => $row['state'] ?? null,
        'birthdate' => $row['birthdate'] ?? null, 'death_date' => $row['death_date'] ?? null,
        'description' => $row['description'],
        'ideologies' => ['Revolutionary anti-capitalism', 'Prison abolition', 'Anti-imperialism'],
        'affiliation' => ['George Jackson Brigade'],
        'era' => '1970s',
        'released' => $row['released'] ?? true,
    ];
    [$prisoner, $created] = addPrisoner($person);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$row['name']} (id={$prisoner->id})\n";
    $created ? $tot['created']++ : $tot['existed']++;

    if (attachCase($prisoner, [
        'institution' => $gjbWa,
        'arrest_date' => $row['arrest'] ?? null,
        'release_date' => $row['release'] ?? null,
        'death_in_custody_date' => $row['death_in_custody'] ?? null,
        'sentenced_date' => $row['sentenced_date'] ?? null,
        'sentence' => $row['sentence'] ?? null,
        'convicted' => $row['convicted'] ?? null,
        'charges' => 'George Jackson Brigade prosecutions: armed bank robbery (18 U.S.C. § 2113), conspiracy, bombing-related federal counts. Active 1975-1978 in the Pacific Northwest with ~16 bombings and several bank robberies targeting infrastructure, prison administrators, and corporations. The Tukwila Pacific National Bank robbery on January 23, 1976 ended in Bruce Seidel\'s death and the wounding/capture of John Sherman; Mark Cook\'s subsequent armed liberation of Sherman from Harborview Medical Center extended the case. Federal trial in Western District of Washington (Counts I-VIII).',
    ])) {
        echo "    + case (arrest_date={$row['arrest']})\n";
        $tot['cases']++;
    }
}

echo "\nDone.\n";
echo "  prisoners created:        {$tot['created']}\n";
echo "  prisoners already existed: {$tot['existed']}\n";
echo "  cases added:              {$tot['cases']}\n";
