<?php

declare(strict_types=1);

/**
 * Bulk-add 41 missing Plowshares Movement participants identified by
 * scripts/find_missing_plowshares_prisoners.php (Art Laffin's chronology).
 *
 * Bios researched per-person from Kings Bay Plowshares 7 chronology,
 * ickevald.net Plowshares chronology 1980-2018, jonahhouse.org,
 * Nuclear Resister archives, obituaries (Macy Morse, Agnes Bauerlein,
 * Vern Rossman, Judy Beaumont), Wikipedia (Per Herngren, Plowshares
 * movement), People v. Nord (Colo. 1990), and primary Catholic Worker
 * sources. Where no biographical detail beyond participation could be
 * sourced, that's stated explicitly in the description.
 *
 * Run on production:
 *   cd /var/www/NPPC-Website && php scripts/add_missing_plowshares_prisoners.php
 *
 * Idempotent — uses Prisoner::firstOrCreate by name, and only attaches a
 * new case row when none with the same arrest_date already exists.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;

// --- Action chronology metadata ---------------------------------------------

$actions = [
    'trident_nein' => [
        'date' => '1982-07-04',
        'institution_name' => 'General Dynamics Electric Boat shipyard',
        'institution_city' => 'Groton',
        'institution_state' => 'Connecticut',
        'court' => 'New London Superior Court',
        'charges_base' => 'Trident Nein action: nine activists boarded the USS Florida Trident submarine at the Electric Boat shipyard, hammered missile-tube hatches and poured blood. Charged in Connecticut state court with criminal mischief, conspiracy and criminal trespass.',
        'default_sentence' => 'Up to one year in jail; shared $1,386.67 restitution to the Navy.',
        'default_convicted' => 'Yes — jury verdict (October 1, 1982)',
    ],
    'plowshares_four' => [
        'date' => '1982-11-14',
        'institution_name' => 'General Dynamics Electric Boat shipyard',
        'institution_city' => 'Groton',
        'institution_state' => 'Connecticut',
        'court' => 'New London Superior Court',
        'charges_base' => 'Plowshares Number Four: seven activists entered the Electric Boat shipyard, hammered Trident missile components and poured blood. Charged in Connecticut state court with criminal mischief, conspiracy and criminal trespass.',
        'default_sentence' => 'Sentence within the group range of two months to one year.',
        'default_convicted' => 'Yes',
    ],
    'avco' => [
        'date' => '1983-07-14',
        'institution_name' => 'AVCO Systems Division',
        'institution_city' => 'Wilmington',
        'institution_state' => 'Massachusetts',
        'court' => 'Massachusetts state court (Middlesex County); appeals to Massachusetts Appeals Court and Supreme Judicial Court',
        'charges_base' => 'AVCO Plowshares: seven activists entered the AVCO Systems Division re-entry-vehicle plant, hammered MX and Trident II nose cones and poured blood. Charged with wanton destruction of property and trespass.',
        'default_sentence' => 'Up to 3.5 months in jail. After roughly seven years of appeals the defendants were resentenced in 1990 to time served (about two weeks for most).',
        'default_convicted' => 'Yes',
    ],
    'griffiss' => [
        'date' => '1983-11-24',
        'institution_name' => 'Griffiss Air Force Base',
        'institution_city' => 'Rome',
        'institution_state' => 'New York',
        'court' => 'U.S. District Court, Northern District of New York',
        'prosecutor' => 'U.S. Attorney for the Northern District of New York',
        'charges_base' => 'Griffiss Plowshares: seven activists entered Griffiss AFB on Thanksgiving Day, hammered a B-52 bomber and poured blood. The first federal Plowshares trial. Charged with sabotage, conspiracy and destruction of government property; convicted on the conspiracy and destruction counts and acquitted of sabotage. Federal appeal denied March 1985.',
        'default_sentence' => 'Two to three years in federal prison.',
        'default_convicted' => 'Yes',
    ],
    'plowshares_seven' => [
        'date' => '1983-12-04',
        'institution_name' => 'U.S. Army Pershing II missile base',
        'institution_city' => 'Schwäbisch-Gmünd',
        'institution_state' => 'Baden-Württemberg, West Germany',
        'court' => 'Schwäbisch-Gmünd court (West Germany)',
        'charges_base' => 'Plowshares Number Seven: the first Plowshares action in Europe — Carl Kabat plus three German peace activists entered a U.S. Army Pershing II missile base in Schwäbisch-Gmünd to hammer and pour blood on a launcher.',
        'default_sentence' => 'Fines of 450–1,800 DM (~$225–$900) or 60–90 days in jail.',
        'default_convicted' => 'Yes — early February 1985 trial',
    ],
    'pershing' => [
        'date' => '1984-04-22',
        'institution_name' => 'Martin Marietta Aerospace plant',
        'institution_city' => 'Orlando',
        'institution_state' => 'Florida',
        'court' => 'U.S. District Court, Middle District of Florida (Orlando)',
        'charges_base' => 'Pershing Plowshares: eight activists entered the Martin Marietta plant in Orlando producing Pershing II launchers, hammered components and poured blood. Charged with conspiracy and depredation of government property.',
        'default_sentence' => 'Three years federal prison plus a five-year suspended sentence with probation; $2,900 restitution.',
        'default_convicted' => 'Yes',
    ],
    'sperry' => [
        'date' => '1984-08-10',
        'institution_name' => 'Sperry Corporation',
        'institution_city' => 'Eagan',
        'institution_state' => 'Minnesota',
        'court' => 'U.S. District Court, District of Minnesota',
        'judge' => 'Hon. Miles Lord',
        'charges_base' => 'Sperry Software Pair: dressed as quality-control inspectors, John LaForge and Barbara Katt walked into the Sperry plant in Eagan and used household hammers to smash two missile-guidance computers under construction. Charged with felony destruction of government property.',
        'default_sentence' => 'Six-month suspended sentence after a jury conviction; Judge Miles Lord delivered a sentencing speech rebuking the arms industry.',
        'default_convicted' => 'Yes — jury verdict (October 1984)',
    ],
    'trident_ii' => [
        'date' => '1984-10-01',
        'institution_name' => 'Electric Boat Quonset Point facility',
        'institution_city' => 'North Kingstown',
        'institution_state' => 'Rhode Island',
        'court' => 'Rhode Island Superior Court',
        'charges_base' => 'Trident II Plowshares: five activists entered the Electric Boat Quonset Point Trident II missile-tube assembly site, hammered tubes and poured blood. Charged with malicious damage to property.',
        'default_sentence' => 'One year and $500 fine.',
        'default_convicted' => 'Yes',
    ],
    'plowshares_twelve' => [
        'date' => '1985-02-19',
        'institution_name' => 'Whiteman Air Force Base, Minuteman II silo (Odessa, MO)',
        'institution_city' => 'Odessa',
        'institution_state' => 'Missouri',
        'court' => 'U.S. District Court, Western District of Missouri',
        'charges_base' => 'Plowshares Number Twelve: Martin Holladay acted alone, entering a Minuteman II missile silo at Whiteman AFB, damaging the silo lid and electrical boxes with hammer and chisel, pouring blood and spray-painting "No More Hiroshimas." Charged with destruction of government property and destruction of national defense material.',
        'default_sentence' => 'Eight years federal prison plus five years probation; released after 19 months.',
        'default_convicted' => 'Yes',
        'sentenced_date' => '1985-05-16',
        'release_date' => '1986-09-01',
    ],
    'trident_ii_pruning' => [
        'date' => '1985-04-18',
        'institution_name' => 'Electric Boat Quonset Point facility',
        'institution_city' => 'North Kingstown',
        'institution_state' => 'Rhode Island',
        'court' => 'Rhode Island Superior Court',
        'charges_base' => 'Trident II Pruning Hooks: six activists entered the Electric Boat Quonset Point facility, hammered missile tubes and poured blood. Charged with malicious damage, possession of burglary tools and trespass.',
        'default_sentence' => 'Three years (suspended after one year) plus two years probation. Released summer 1986 (men) / January 1987 (women).',
        'default_convicted' => 'Yes',
    ],
    'michigan_elf' => [
        'date' => '1985-05-28',
        'institution_name' => 'U.S. Navy ELF transmitter site',
        'institution_city' => 'Republic',
        'institution_state' => 'Michigan',
        'court' => 'Marquette County, Michigan',
        'charges_base' => 'Michigan ELF Disarmament: Tom Hastings sawed down a Navy Extremely Low Frequency submarine-communication antenna pole in Michigan\'s Upper Peninsula, then prayed and planted corn at the site for 45 minutes before turning himself in to the local sheriff. Charged with malicious destruction of property.',
        'default_sentence' => '15 days plus two years probation.',
        'default_convicted' => 'Yes — jury verdict',
        'sentenced_date' => '1985-09-27',
    ],
    'pantex' => [
        'date' => '1985-07-16',
        'institution_name' => 'Pantex Nuclear Weapons Assembly Plant rail spur',
        'institution_city' => 'Amarillo',
        'institution_state' => 'Texas',
        'court' => 'U.S. District Court, Northern District of Texas',
        'charges_base' => 'Pantex Disarmament: Richard Miller acted alone over seven hours of railroad work, removing a 39-foot section of rail from the spur line carrying nuclear-weapon components from the Pantex Plant. Charged with "wrecking trains" (sabotage) and destruction of national defense materials.',
        'default_sentence' => 'Two concurrent four-year terms in federal prison; served the full sentence.',
        'default_convicted' => 'Yes',
        'release_date' => '1989-02-01',
    ],
    'mx_witness' => [
        'date' => '1985-09-27',
        'institution_name' => 'Martin Marietta plant',
        'institution_city' => 'Denver',
        'institution_state' => 'Colorado',
        'court' => 'Jefferson County, Colorado (state); appellate review People v. Nord (Colo. 1990)',
        'charges_base' => 'Martin Marietta MX Witness: three activists entered the Martin Marietta plant in Denver. Charged with class-4 felony criminal mischief and felony burglary; raised a "choice of evils" defense citing imminent danger of MX missile production.',
        'default_sentence' => 'Two months in prison; convictions later reversed on appeal on indigency-determination grounds (Colorado Court of Appeals; companion ruling in People v. Nord, 1990).',
        'default_convicted' => 'Yes (later reversed on appeal)',
    ],
    'silo_plowshares' => [
        'date' => '1986-03-28',
        'institution_name' => 'Whiteman Air Force Base, Minuteman II silos (Holden, MO)',
        'institution_city' => 'Holden',
        'institution_state' => 'Missouri',
        'court' => 'U.S. District Court, Western District of Missouri',
        'charges_base' => 'Silo Plowshares: five activists entered two Minuteman II missile silos at Whiteman AFB on Good Friday, hammered silo lids and electrical boxes, poured blood, and spray-painted disarmament messages. Charged with destruction of government property and conspiracy.',
        'default_sentence' => 'Initially 7–8 years plus five years probation; reduced on resentencing; released 1987.',
        'default_convicted' => 'Yes',
    ],
    'credo' => [
        'date' => '1988-09-20',
        'institution_name' => 'Sheraton-Washington Hotel (Air Force Association Arms Bazaar)',
        'institution_city' => 'Washington',
        'institution_state' => 'District of Columbia',
        'court' => 'D.C. Superior Court',
        'charges_base' => 'Credo Plowshares: Marcia Timmel acted alone at the Air Force Association Arms Bazaar, hammering and pouring blood on a Textron Defense System MX missile display. Charged with property damage.',
        'default_sentence' => '90 days (83 suspended), 90 days probation, 7 days served.',
        'default_convicted' => 'Yes — jury verdict (November 18, 1988)',
        'sentenced_date' => '1988-12-29',
        'incarceration_date' => '1989-01-09',
    ],
    'aegis' => [
        'date' => '1991-12-07',
        'institution_name' => 'Bath Iron Works',
        'institution_city' => 'Bath',
        'institution_state' => 'Maine',
        'court' => 'U.S. District Court, District of Maine',
        'charges_base' => 'Aegis Plowshares: four activists boarded the USS The Sullivans Aegis cruiser under construction at Bath Iron Works, hammered missile launchers and poured blood. Charged with conspiracy and depredation of government property.',
        'default_sentence' => 'Federal sentence varied by defendant.',
        'default_convicted' => 'Yes',
    ],
    'thames_river' => [
        'date' => '1989-09-04',
        'institution_name' => 'Naval Underwater Systems Center / USS Pennsylvania (10th Trident)',
        'institution_city' => 'New London',
        'institution_state' => 'Connecticut',
        'court' => 'U.S. District Court, District of Connecticut',
        'charges_base' => 'Thames River Plowshares: six activists swam and canoed to the USS Pennsylvania (the 10th Trident submarine) in the Thames River, hammered the hull and poured blood. Charged with conspiracy and damage to a U.S. naval vessel.',
        'default_sentence' => 'Federal sentence varied by defendant.',
        'default_convicted' => 'Yes',
    ],
];

// --- Per-person data --------------------------------------------------------

$people = [
    [
        'name' => 'Judith Ann Beaumont',
        'first_name' => 'Judith', 'middle_name' => 'Ann', 'last_name' => 'Beaumont',
        'aka' => 'Sister Judy Beaumont',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Connecticut',
        'death_date' => '2018-01-01',
        'description' => 'Judith Ann Beaumont was a Benedictine sister of 34 years and a teacher who came to Hartford from Chicago. After leaving the Benedictines she co-founded and served as Executive Director of My Sisters\' Place, a four-tier program for homeless women and children in Hartford. In 2012 she was ordained a Roman Catholic Woman Priest and became pastor of Good Shepherd Inclusive Catholic Community in Fort Myers, Florida, where she ran Good Shepherd Ministries of SW Florida and Church in the Park. She died of AML leukemia at her home in Fort Myers on January 1, 2018, partnered with Judy Lee.',
        'affiliation' => ['Plowshares Movement','Benedictine Sisters (former)','Roman Catholic Womenpriests'],
        'actions' => [['key' => 'trident_nein']],
    ],
    [
        'name' => 'James Cunningham',
        'first_name' => 'James', 'last_name' => 'Cunningham',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Maryland',
        'description' => 'James Cunningham, 40 at the time of Trident Nein, was a former lawyer who had given up a high-living legal practice (and a house in Laguna Beach, California) to join the Jonah House resistance community in Baltimore founded by Phil Berrigan and Liz McAlister. He had previously served 60 days for trespassing at the Trident submarine plant in Bangor, Washington, before joining Jonah House through his friend Karl Smith.',
        'affiliation' => ['Plowshares Movement','Jonah House'],
        'actions' => [['key' => 'trident_nein']],
    ],
    [
        'name' => 'George Veasey',
        'first_name' => 'George', 'last_name' => 'Veasey',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Maryland',
        'description' => 'George Veasey was a Vietnam War veteran who, after his wartime experience, joined the Jonah House resistance community in Baltimore and committed himself to nonviolent disarmament work. He was a two-time Plowshares activist, participating in the 1982 Trident Nein action and again in the 1985 Trident II Pruning Hooks action at Quonset Point.',
        'affiliation' => ['Plowshares Movement','Jonah House','Atlantic Life Community'],
        'actions' => [
            ['key' => 'trident_nein'],
            ['key' => 'trident_ii_pruning', 'sentence' => 'Three years (suspended after one year) plus two years probation; released summer 1986.', 'release_date' => '1986-07-01'],
        ],
    ],
    [
        'name' => 'Timothy Quinn',
        'first_name' => 'Timothy', 'last_name' => 'Quinn',
        'aka' => 'Tim Quinn',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Connecticut',
        'birthdate' => null,
        'description' => 'Tim Quinn was 28 at the time of Trident Nein, an expectant father working as a house painter in Hartford, Connecticut. No further sourceable biographical detail was found.',
        'affiliation' => ['Plowshares Movement'],
        'actions' => [['key' => 'trident_nein']],
    ],
    [
        'name' => 'Anne Bennis',
        'first_name' => 'Anne', 'last_name' => 'Bennis',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Pennsylvania',
        'description' => 'Anne Bennis was a teacher from Philadelphia active in the Atlantic Life Community network of nonviolent resisters. She was a two-time Plowshares activist, participating in Trident Nein in 1982 and the Aegis Plowshares action at Bath Iron Works in 1991.',
        'affiliation' => ['Plowshares Movement','Atlantic Life Community'],
        'actions' => [
            ['key' => 'trident_nein'],
            ['key' => 'aegis'],
        ],
    ],
    [
        'name' => 'Bill Hartman',
        'first_name' => 'Bill', 'last_name' => 'Hartman',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Pennsylvania',
        'description' => 'Bill Hartman was a peace worker based in Philadelphia and active in the Atlantic Life Community network of Catholic radical resistance. No further sourceable biographical detail beyond participation in Trident Nein was found.',
        'affiliation' => ['Plowshares Movement','Atlantic Life Community'],
        'actions' => [['key' => 'trident_nein']],
    ],
    [
        'name' => 'Vincent Kay',
        'first_name' => 'Vincent', 'last_name' => 'Kay',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Connecticut',
        'description' => 'Vincent Kay was a house painter and poet from New Haven, Connecticut, and a member of the Covenant Peace Community. After his Plowshares activism he became a beekeeper, maintaining roughly 450 hives across the region under the brand "Swords into Plowshares Honey," sold in local Connecticut stores, restaurants, and Yale\'s dining halls; he processes the honey at an East Rock workshop.',
        'affiliation' => ['Plowshares Movement','Covenant Peace Community'],
        'actions' => [['key' => 'trident_nein']],
    ],
    [
        'name' => 'Arthur J. Laffin',
        'first_name' => 'Arthur', 'middle_name' => 'J.', 'last_name' => 'Laffin',
        'aka' => 'Art Laffin',
        'gender' => 'Male', 'race' => 'White', 'state' => 'District of Columbia',
        'description' => 'Art Laffin is the principal chronicler of the Plowshares movement, co-editor with Sr. Anne Montgomery of two editions of "Swords Into Plowshares: Nonviolent Direct Action for Disarmament" (Harper & Row 1987; Fortkamp 1996). He was a member of the Covenant Peace Community in New Haven from 1978 to 1990 before joining the Olive Branch Catholic Worker in DC in 1990 and the Dorothy Day Catholic Worker in 1994, where he still lives with his wife and son. He is the author of a new edition of "The Risk of the Cross: Living Gospel Nonviolence in the Nuclear Age" and has been imprisoned for two Plowshares actions.',
        'affiliation' => ['Plowshares Movement','Catholic Worker','Dorothy Day Catholic Worker (DC)','Atlantic Life Community','Covenant Peace Community (former)'],
        'actions' => [
            ['key' => 'trident_nein'],
            ['key' => 'thames_river'],
        ],
    ],
    [
        'name' => 'John Grady',
        'first_name' => 'John', 'last_name' => 'Grady',
        'gender' => 'Male', 'race' => 'White', 'state' => 'New York',
        'description' => 'John Grady was an auto mechanic from Ithaca, NY and patriarch of the Grady family of Catholic radicals. He had previously been a defendant in the Camden 28 anti-draft action of 1971 and worked closely with Daniel and Philip Berrigan; his daughters Clare, Ellen, and Teresa Grady all became major Plowshares and Catholic Worker activists. The Grady family home in Ithaca was a center of Vietnam-era and ongoing antiwar organizing.',
        'affiliation' => ['Plowshares Movement','Catholic Worker','Camden 28 (earlier)'],
        'actions' => [['key' => 'plowshares_four']],
    ],
    [
        'name' => 'Ellen Grady',
        'first_name' => 'Ellen', 'last_name' => 'Grady',
        'gender' => 'Female', 'race' => 'White', 'state' => 'New York',
        'description' => 'Ellen Grady, daughter of Camden 28 defendant John Grady and sister of Clare and Teresa Grady, was an aide to an elderly woman and peace worker in Ithaca, NY at the time of Plowshares Number Four. She and her husband Peter DeMott (Plowshares Number Two) helped establish the Ithaca Catholic Worker community in the 1990s and have continued lifelong nonviolent resistance, including organizing against drone-warfare operations at Hancock Air Force Base. Peter DeMott died in a fall in 2009.',
        'affiliation' => ['Plowshares Movement','Ithaca Catholic Worker','Atlantic Life Community'],
        'actions' => [['key' => 'plowshares_four']],
    ],
    [
        'name' => 'Jean Holladay',
        'first_name' => 'Jean', 'last_name' => 'Holladay',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Massachusetts',
        'description' => 'Jean Holladay was a nurse, mother of four, and grandmother from Massachusetts who became one of the most committed serial Plowshares activists of the 1980s, taking part in three actions: Plowshares Number Four (1982), AVCO Plowshares (1983), and Trident II Plowshares (1984). She is the mother of Plowshares Number Twelve activist Martin Holladay.',
        'affiliation' => ['Plowshares Movement'],
        'actions' => [
            ['key' => 'plowshares_four'],
            ['key' => 'avco', 'sentence' => 'Up to 3.5 months; eventually resentenced to time served (~3 months) after appeals.'],
            ['key' => 'trident_ii'],
        ],
    ],
    [
        'name' => 'Roger Ludwig',
        'first_name' => 'Roger', 'last_name' => 'Ludwig',
        'gender' => 'Male', 'race' => 'White', 'state' => 'District of Columbia',
        'description' => 'Roger Ludwig was a poet and musician engaged in work with the poor in Washington, DC. He was a two-time Plowshares activist, participating in Plowshares Number Four (1982) and Trident II Pruning Hooks (1985).',
        'affiliation' => ['Plowshares Movement'],
        'actions' => [
            ['key' => 'plowshares_four'],
            ['key' => 'trident_ii_pruning', 'sentence' => 'Three years (suspended after one year) plus two years probation; released summer 1986.', 'release_date' => '1986-07-01'],
        ],
    ],
    [
        'name' => 'Marcia Timmel',
        'first_name' => 'Marcia', 'last_name' => 'Timmel',
        'gender' => 'Female', 'race' => 'White', 'state' => 'District of Columbia',
        'description' => 'Marcia Timmel was a longtime member of the Olive Branch / Dorothy Day Catholic Worker in Washington, DC, and one of the most active women in the early Plowshares movement. She participated in Plowshares Number Four (1982) and acted alone in the 1988 Credo Plowshares, where she hammered and poured blood on a Textron Defense System MX missile display at the Air Force Association Arms Bazaar at the Sheraton-Washington Hotel, leaving her own creed of life and faith in response to the bazaar\'s slogan "Freedom: A Creed To Believe In."',
        'affiliation' => ['Plowshares Movement','Catholic Worker','Dorothy Day Catholic Worker (DC)','Olive Branch Catholic Worker'],
        'actions' => [
            ['key' => 'plowshares_four'],
            ['key' => 'credo'],
        ],
    ],
    [
        'name' => 'Agnes Bauerlein',
        'first_name' => 'Agnes', 'middle_name' => 'Gertrude', 'last_name' => 'Bauerlein',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Pennsylvania',
        'death_date' => '2015-11-26',
        'description' => 'Agnes Gertrude Bauerlein was a Dutch-born mother, grandmother, and lifelong social activist in Ambler and Philadelphia. Having lost a brother and sister in WWII bombings of the Netherlands, she devoted her life to peace work; she and her husband opened their Ambler home to the Plowshares Eight defendants during the 1981 Norristown trial, traveled to Iraq with the Gulf Peace Team in 1991 to try to avert war, and was repeatedly jailed for nonviolent action. She died at age 87 of complications from Alzheimer\'s, survived by 11 children, 26 grandchildren, and 13 great-grandchildren.',
        'affiliation' => ['Plowshares Movement','Gulf Peace Team'],
        'actions' => [['key' => 'avco']],
    ],
    [
        'name' => 'Macy Morse',
        'first_name' => 'Macy', 'middle_name' => 'Elkins', 'last_name' => 'Morse',
        'gender' => 'Female', 'race' => 'White', 'state' => 'New Hampshire',
        'birthdate' => '1921-01-25',
        'death_date' => '2019-07-18',
        'description' => 'Macy Morse was a mother and grandmother from Nashua, NH whose three oldest sons served in the military, one in Vietnam, prompting her conversion to active pacifism. She co-founded the Nashua, NH People Concerned About the War in Vietnam and accumulated more than 60 years of nonviolent activism, at least 10 arrests, and four jail terms — including for splashing blood in Alexander Haig\'s office in 1981 and for trespassing in Sen. Judd Gregg\'s office in 2003. She died at age 98.',
        'affiliation' => ['Plowshares Movement','Nashua People Concerned About the War in Vietnam'],
        'actions' => [['key' => 'avco']],
    ],
    [
        'name' => 'Mary Lyons',
        'first_name' => 'Mary', 'last_name' => 'Lyons',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Connecticut',
        'description' => 'Mary Lyons was a history teacher, mother of five, and grandmother from Hartford, Connecticut. She testified at the AVCO trial about her religious and moral reasoning for entering the plant. No further sourceable biographical detail was found.',
        'affiliation' => ['Plowshares Movement'],
        'actions' => [['key' => 'avco']],
    ],
    [
        'name' => 'Frank Panopoulos',
        'first_name' => 'Frank', 'last_name' => 'Panopoulos',
        'gender' => 'Male', 'race' => 'White', 'state' => 'New York',
        'description' => 'Frank Panopoulos has worked on peace and social justice issues since 1979. During the 1980s he lived in the Cor Jesu Community, a lay Catholic community engaged in works of mercy in Upper Manhattan. He served prison time for two Plowshares actions (AVCO 1983 and Trident II 1984) and later became a human rights attorney and a writer for CovertAction Magazine.',
        'affiliation' => ['Plowshares Movement','Cor Jesu Community'],
        'actions' => [
            ['key' => 'avco'],
            ['key' => 'trident_ii', 'sentence' => 'Convicted of malicious damage; one year plus $500 fine, plus an additional two months for contempt.'],
        ],
    ],
    [
        'name' => 'John Pendleton',
        'first_name' => 'John', 'last_name' => 'Pendleton',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Maryland',
        'description' => 'John Pendleton was a member of the Jonah House nonviolent resistance community in Baltimore and a two-time Plowshares activist (AVCO 1983 and Trident II 1984).',
        'affiliation' => ['Plowshares Movement','Jonah House'],
        'actions' => [
            ['key' => 'avco', 'sentence' => 'Up to 3.5 months — Pendleton served three months.'],
            ['key' => 'trident_ii'],
        ],
    ],
    [
        'name' => 'Jackie Allen-Doucet',
        'first_name' => 'Jackie', 'last_name' => 'Allen-Doucet',
        'aka' => 'Jackie Allen',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Connecticut',
        'description' => 'Jackie Allen-Doucet was 22 and a nursery school teacher in Hartford, CT at the time of the Griffiss Plowshares action. She is a life-long Plowshares activist, artist, water protector, Catholic Worker, spiritual companion, and member of the Atlantic Life Community, raised among the Berrigans\' circle of Catholic radicals. She also took part in the 1989 Thames River Plowshares action while living and doing shelter work at the Ahimsa Community in Voluntown, Connecticut, and continues mentorship and afterschool work in Hartford.',
        'affiliation' => ['Plowshares Movement','Catholic Worker','Atlantic Life Community','Ahimsa Community (Voluntown CT)'],
        'actions' => [
            ['key' => 'griffiss'],
            ['key' => 'thames_river'],
        ],
    ],
    [
        'name' => 'Vernon Joseph Rossman',
        'first_name' => 'Vernon', 'middle_name' => 'Joseph', 'last_name' => 'Rossman',
        'aka' => 'Vern Rossman',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Massachusetts',
        'birthdate' => '1927-02-03',
        'description' => 'Vern Rossman was an ordained minister of the Christian Church (Disciples of Christ), born Feb. 3, 1927, raised in a minister\'s family in Oklahoma, educated at Phillips University and Yale Divinity School (ordained 1951). He served as a missionary to Japan from 1952 to 1963, where the legacy of Hiroshima and Nagasaki shaped his lifelong commitment to peace and anti-nuclear work; he later worked for the Disciples\' Division of Overseas Ministries in Indianapolis and as executive director of Intermedia in NYC under the World Council of Churches. At the time of Griffiss he was a 56-year-old father and grandfather living in Dorchester, MA. He died at his home in Columbia, Missouri in 2014.',
        'death_date' => '2014-10-10',
        'affiliation' => ['Plowshares Movement','Christian Church (Disciples of Christ)'],
        'actions' => [['key' => 'griffiss']],
    ],
    [
        'name' => 'Karl Smith',
        'first_name' => 'Karl', 'last_name' => 'Smith',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Maryland',
        'description' => 'Karl Smith was 27 at the time of the Griffiss action and a member of the Jonah House resistance community in Baltimore. He had previously been imprisoned with James Cunningham for 60 days for trespassing at the Trident plant in Bangor, Washington, before they both joined Jonah House.',
        'affiliation' => ['Plowshares Movement','Jonah House'],
        'actions' => [['key' => 'griffiss']],
    ],
    [
        'name' => 'Herwig Jantschik',
        'first_name' => 'Herwig', 'last_name' => 'Jantschik',
        'gender' => 'Male', 'race' => 'White',
        'description' => 'Herwig Jantschik was a West German peace activist and one of three Germans (with Wolfgang Sternstein and Karin Vix) who joined Carl Kabat in carrying out the first Plowshares action in Europe at the U.S. Army\'s Pershing II base in Schwäbisch-Gmünd. The four had previously distributed booklets about Plowshares actions during a six-week peace march through Germany.',
        'affiliation' => ['Plowshares Movement','West German peace movement'],
        'actions' => [['key' => 'plowshares_seven', 'sentence' => 'Sentenced to 1,800 DM (~$900) or 90 days in jail; Jantschik served the prison sentence.']],
    ],
    [
        'name' => 'Karin Vix',
        'first_name' => 'Karin', 'last_name' => 'Vix',
        'gender' => 'Female', 'race' => 'White',
        'description' => 'Karin Vix was a West German peace activist who joined Carl Kabat, Wolfgang Sternstein and Herwig Jantschik in the December 1983 Plowshares Number Seven action — the first Plowshares action in Europe — disarming a Pershing II missile launcher at the U.S. Army base in Schwäbisch-Gmünd.',
        'affiliation' => ['Plowshares Movement','West German peace movement'],
        'actions' => [['key' => 'plowshares_seven', 'sentence' => 'Sentenced to 450 DM (~$225) or 60 days in jail; she served the prison sentence.']],
    ],
    [
        'name' => 'Per Herngren',
        'first_name' => 'Per', 'last_name' => 'Herngren',
        'gender' => 'Male', 'race' => 'White',
        'birthdate' => '1961-07-16',
        'description' => 'Per Herngren is a Swedish nonviolence theorist, trainer, and writer based in the Fig Tree Resistance Community outside Gothenburg, Sweden. He is the author of "Path of Resistance: The Practice of Civil Disobedience" (originally Swedish, translated into English, Dutch and other languages) and a leading figure in the Swedish Plogbillar (Plowshares) movement; he has also been imprisoned in Sweden and England for further disarmament actions. He works half-time as a nonviolence trainer and half-time as a writer.',
        'affiliation' => ['Plowshares Movement','Swedish Plogbillsrörelsen','Fig Tree Resistance Community (Gothenburg)'],
        'actions' => [['key' => 'pershing', 'sentence' => 'Three years federal prison plus a five-year suspended sentence with probation; $2,900 restitution. As a Swedish national he was deported on August 27, 1985 after serving over one year (totaling roughly fifteen months in eleven different prisons).', 'release_date' => '1985-08-27']],
    ],
    [
        'name' => 'Todd Kaplan',
        'first_name' => 'Todd', 'last_name' => 'Kaplan',
        'gender' => 'Male', 'race' => 'White', 'state' => 'District of Columbia',
        'description' => 'Todd Kaplan was engaged in work with the poor in Washington, DC at the time of Pershing Plowshares. No further sourceable biographical detail was found.',
        'affiliation' => ['Plowshares Movement','Catholic Worker (DC)'],
        'actions' => [['key' => 'pershing']],
    ],
    [
        'name' => 'Tim Lietzke',
        'first_name' => 'Tim', 'last_name' => 'Lietzke',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Virginia',
        'description' => 'Tim Lietzke was a member of Jeremiah House, a Catholic peace community in Richmond, Virginia, at the time of the Pershing Plowshares action. He has continued to write for Pax Christi USA on peace and disarmament.',
        'affiliation' => ['Plowshares Movement','Jeremiah House (Richmond VA)','Pax Christi USA'],
        'actions' => [['key' => 'pershing']],
    ],
    [
        'name' => 'Jim Perkins',
        'first_name' => 'Jim', 'last_name' => 'Perkins',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Maryland',
        'description' => 'Jim Perkins was a teacher and father living in the Jonah House resistance community in Baltimore at the time of Pershing Plowshares.',
        'affiliation' => ['Plowshares Movement','Jonah House'],
        'actions' => [['key' => 'pershing']],
    ],
    [
        'name' => 'Christin Schmidt',
        'first_name' => 'Christin', 'last_name' => 'Schmidt',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Rhode Island',
        'description' => 'Christin Schmidt was a university student and peace worker from Rhode Island at the time of Pershing Plowshares. The group\'s banner at the action read "Violence Ends Where Love Begins."',
        'affiliation' => ['Plowshares Movement'],
        'actions' => [['key' => 'pershing']],
    ],
    [
        'name' => 'Barbara Katt',
        'first_name' => 'Barbara', 'last_name' => 'Katt',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Minnesota',
        'description' => 'Barbara Katt was a house painter and peace worker in Bemidji, Minnesota, with a philosophy degree from Bemidji State University and experience working with mentally impaired adults. She and her partner John LaForge had been engaged in nonviolent civil disobedience for four years before the Sperry action; dressed as quality-control inspectors in business suits, the two walked into the Sperry plant in Eagan, MN and used household hammers to smash two missile-guidance computers under construction.',
        'affiliation' => ['Plowshares Movement','Nukewatch'],
        'actions' => [['key' => 'sperry']],
    ],
    [
        'name' => 'William Boston',
        'first_name' => 'William', 'last_name' => 'Boston',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Connecticut',
        'description' => 'William Boston was a house painter and peace worker living in New Haven, Connecticut at the time of Trident II Plowshares. No further sourceable biographical detail was found.',
        'affiliation' => ['Plowshares Movement'],
        'actions' => [['key' => 'trident_ii']],
    ],
    [
        'name' => 'Leo Schiff',
        'first_name' => 'Leo', 'last_name' => 'Schiff',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Vermont',
        'description' => 'Leo Schiff was a draft-registration resister and natural-foods chef from Vermont. No further detailed biography beyond his Plowshares participation was sourced.',
        'affiliation' => ['Plowshares Movement','Draft Registration Resistance'],
        'actions' => [['key' => 'trident_ii']],
    ],
    [
        'name' => 'Martin Holladay',
        'first_name' => 'Martin', 'last_name' => 'Holladay',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Vermont',
        'description' => 'Martin Holladay was a carpenter from Sheffield, Vermont, and the son of three-time Plowshares activist Jean Holladay. Acting alone on Feb. 19, 1985 he entered a Minuteman II missile silo at Whiteman AFB near Odessa, Missouri, damaged the silo lid and electrical boxes with hammer and chisel, poured blood, and spray-painted "No More Hiroshimas." When the alarm-response team arrived with automatic rifles he assured them, "Don\'t worry, I\'m unarmed." He later became a well-known building-science writer.',
        'affiliation' => ['Plowshares Movement'],
        'actions' => [['key' => 'plowshares_twelve']],
    ],
    [
        'name' => 'Sheila Parks',
        'first_name' => 'Sheila', 'last_name' => 'Parks',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Massachusetts',
        'description' => 'Sheila Parks was a former college teacher based in Medford, Massachusetts at the time of Trident II Pruning Hooks. No further sourceable biographical detail was found.',
        'affiliation' => ['Plowshares Movement'],
        'actions' => [['key' => 'trident_ii_pruning', 'sentence' => 'Three years (suspended after one year) plus two years probation; released January 1987.', 'release_date' => '1987-01-15']],
    ],
    [
        'name' => 'Suzanne Schmidt',
        'first_name' => 'Suzanne', 'last_name' => 'Schmidt',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Maryland',
        'description' => 'Suzanne Schmidt was a mother, grandmother, and worker with people with disabilities who lived in the Jonah House resistance community in Baltimore at the time of the Trident II Pruning Hooks action.',
        'affiliation' => ['Plowshares Movement','Jonah House'],
        'actions' => [['key' => 'trident_ii_pruning', 'sentence' => 'Three years (suspended after one year) plus two years probation; released January 1987.', 'release_date' => '1987-01-15']],
    ],
    [
        'name' => 'Tom H. Hastings',
        'first_name' => 'Tom', 'middle_name' => 'H.', 'last_name' => 'Hastings',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Oregon',
        'birthdate' => '1950-01-01',
        'description' => 'Tom Hastings, born 1950, began peace and justice activism in 1968; his youth, early activism, and college took place in Wisconsin. He is a two-time Plowshares resister, founding member of two Catholic Worker communities, and worked for years to defend Anishinabe treaty rights. He is now Professor and Coordinator of the Conflict Resolution program at Portland State University, Director of PeaceVoice, co-founder of the Portland Peace Team, faculty with the James Lawson Institute, on the Academic Advisory Council of the International Center on Nonviolent Conflict, and the author of more than 500 articles and several books on nonviolence.',
        'affiliation' => ['Plowshares Movement','Catholic Worker (former)','PeaceVoice','Portland Peace Team'],
        'actions' => [['key' => 'michigan_elf']],
    ],
    [
        'name' => 'Richard Miller',
        'first_name' => 'Richard', 'last_name' => 'Miller',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Iowa',
        'aka' => 'Pantex Plowshares Richard Miller',
        'description' => 'Richard Miller was engaged in work with the poor in Des Moines, Iowa, at the time of his solo Pantex action. He took precautions to prevent accidental derailment and personal injury, then labored for seven hours with railroad tools, removing a 39-foot section of rail from the spur line carrying nuclear-weapon components from the Pantex Plant in Amarillo to the Atchison, Topeka & Santa Fe main line.',
        'affiliation' => ['Plowshares Movement','Des Moines Catholic Worker'],
        'actions' => [['key' => 'pantex']],
    ],
    [
        'name' => 'Al Zook',
        'first_name' => 'Al', 'last_name' => 'Zook',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Colorado',
        'description' => 'Al Zook was a father and grandfather active in the Denver Catholic Worker community in Colorado at the time of the Martin Marietta MX Witness action.',
        'affiliation' => ['Plowshares Movement','Denver Catholic Worker'],
        'actions' => [['key' => 'mx_witness']],
    ],
    [
        'name' => 'Mary Sprunger-Froese',
        'first_name' => 'Mary', 'last_name' => 'Sprunger-Froese',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Colorado',
        'description' => 'Mary Sprunger-Froese, with her husband Peter, has lived since 1979 at the Bijou Community, an intentional faith-based community in Colorado Springs working for nonviolence, hospitality, and social justice. She is a longtime war tax resister and is artist-in-residence at RAWtools (which forges donated guns into garden tools), where she leads multi-age programs in storytelling, rap, skits, and de-escalation training.',
        'affiliation' => ['Plowshares Movement','Bijou Community (Colorado Springs)','Mennonite peace tradition','War tax resister'],
        'actions' => [['key' => 'mx_witness']],
    ],
    [
        'name' => 'Marie Nord',
        'first_name' => 'Marie', 'last_name' => 'Nord',
        'aka' => 'Sister Marie Nord',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Minnesota',
        'description' => 'Sister Marie Nord was a Minnesota Franciscan sister whose ministry was hospitality work for women. The People v. Nord (Colo. 1990) Colorado Supreme Court ruling on the indigency-determination error in her case is named for her.',
        'affiliation' => ['Plowshares Movement','Franciscan Sisters of Little Falls (Minnesota)'],
        'actions' => [['key' => 'mx_witness']],
    ],
    [
        'name' => 'Darla Bradley',
        'first_name' => 'Darla', 'last_name' => 'Bradley',
        'gender' => 'Female', 'race' => 'White', 'state' => 'Iowa',
        'description' => 'Darla Bradley was 22 and a member of the Davenport Catholic Worker in Iowa at the time of Silo Plowshares. She has said in retrospect that she would not repeat the action.',
        'affiliation' => ['Plowshares Movement','Davenport Catholic Worker'],
        'actions' => [['key' => 'silo_plowshares', 'sentence' => 'Initially 8 years plus 5 years probation; reduced to one year; released mid-June 1987.', 'release_date' => '1987-06-15']],
    ],
    [
        'name' => 'Ken Rippetoe',
        'first_name' => 'Ken', 'last_name' => 'Rippetoe',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Illinois',
        'description' => 'Ken Rippetoe was a member of the Catholic Worker in Rock Island, Illinois at the time of Silo Plowshares.',
        'affiliation' => ['Plowshares Movement','Catholic Worker (Rock Island IL)'],
        'actions' => [['key' => 'silo_plowshares', 'sentence' => 'Initially 8 years plus 5 years probation; reduced to one year; released mid-June 1987.', 'release_date' => '1987-06-15']],
    ],
    [
        'name' => 'John Volpe',
        'first_name' => 'John', 'last_name' => 'Volpe',
        'gender' => 'Male', 'race' => 'White', 'state' => 'Iowa',
        'description' => 'John Volpe was a father and member of the Davenport Catholic Worker in Iowa. Published Plowshares chronologies (Laffin/Kings Bay Plowshares 7; ickevald.net) describe him as a former employee of the Rock Island Arsenal.',
        'affiliation' => ['Plowshares Movement','Davenport Catholic Worker'],
        'actions' => [['key' => 'silo_plowshares', 'sentence' => 'Initially 7 years plus 5 years probation; reduced to 10 months; released April 1987.', 'release_date' => '1987-04-15']],
    ],
];

// --- Apply ------------------------------------------------------------------

$created = 0;
$skipped = 0;
$casesAdded = 0;

foreach ($people as $p) {
    $existing = Prisoner::where('name', $p['name'])
        ->orWhere('aka', $p['aka'] ?? '__none__')
        ->first();

    if ($existing) {
        $prisoner = $existing;
        echo "  EXISTS  {$p['name']} (id={$prisoner->id}) — adding cases only\n";
        $skipped++;
    } else {
        $prisoner = Prisoner::create([
            'name'         => $p['name'],
            'first_name'   => $p['first_name'] ?? null,
            'middle_name'  => $p['middle_name'] ?? null,
            'last_name'    => $p['last_name'] ?? null,
            'aka'          => $p['aka'] ?? null,
            'gender'       => $p['gender'] ?? null,
            'race'         => $p['race'] ?? null,
            'state'        => $p['state'] ?? null,
            'birthdate'    => $p['birthdate'] ?? null,
            'death_date'   => $p['death_date'] ?? null,
            'description'  => $p['description'] ?? null,
            'era'          => '1980s',
            'ideologies'   => ['Pacifism','Catholic radicalism','Anti-nuclear'],
            'affiliation'  => $p['affiliation'] ?? ['Plowshares Movement'],
            'in_custody'   => false,
            'released'     => true,
        ]);
        echo "  CREATED {$p['name']} (id={$prisoner->id})\n";
        $created++;
    }

    foreach ($p['actions'] as $actionRef) {
        $a = $actions[$actionRef['key']];
        $arrestDate = $a['date'];

        $existingCase = $prisoner->cases()
            ->where('arrest_date', $arrestDate)
            ->first();
        if ($existingCase) {
            echo "    case exists ({$actionRef['key']}, arrest_date={$arrestDate})\n";
            continue;
        }

        $institution = Institution::firstOrCreate(
            ['name' => $a['institution_name']],
            ['city' => $a['institution_city'] ?? null, 'state' => $a['institution_state'] ?? null],
        );

        $sentence       = $actionRef['sentence']       ?? $a['default_sentence'];
        $convicted      = $actionRef['convicted']      ?? $a['default_convicted'];
        $sentencedDate  = $actionRef['sentenced_date'] ?? ($a['sentenced_date'] ?? null);
        $incarcDate     = $actionRef['incarceration_date'] ?? ($a['incarceration_date'] ?? $arrestDate);
        $releaseDate    = $actionRef['release_date']   ?? ($a['release_date'] ?? null);
        $prosecutor     = $actionRef['prosecutor']     ?? ($a['prosecutor'] ?? null);
        $judge          = $actionRef['judge']          ?? ($a['judge'] ?? null);

        PrisonerCase::create([
            'prisoner_id'        => $prisoner->id,
            'institution_id'     => $institution->id,
            'charges'            => $a['charges_base'],
            'arrest_date'        => $arrestDate,
            'incarceration_date' => $incarcDate,
            'release_date'       => $releaseDate,
            'sentenced_date'     => $sentencedDate,
            'convicted'          => $convicted,
            'sentence'           => $sentence,
            'prosecutor'         => $prosecutor,
            'judge'              => $judge,
        ]);
        echo "    + case ({$actionRef['key']}, arrest_date={$arrestDate})\n";
        $casesAdded++;
    }
}

echo "\nDone. created={$created}, already-existed={$skipped}, cases-added={$casesAdded}\n";
