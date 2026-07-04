<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Batch 16 of the ILD Labor Defender mining, covering the whole 1936 volume
 * (Vol. X/XII, Jan–Dec). 1936 brought the maritime frame-ups of the Popular
 * Front era — the King-Ramsay-Conner (Point Lobos) case and the Modesto
 * dynamite case — the Tampa flogging murder, the landmark Brown v. Mississippi
 * torture-confession reversal, and a wave of CIO strike prosecutions.
 *
 * This adds the clearly-attested NEW prisoners of 1936. Marquee cases:
 *  - King-Ramsay-Conner (the Point Lobos maritime murder frame-up prosecuted
 *    by Earl Warren);
 *  - the Tampa flogging of Joseph Shoemaker, Eugene Poulnot and Sam Rogers;
 *  - Brown v. Mississippi (Ed Brown, Henry Shields, Yank Ellington);
 *  - the Juneau, Alaska mine-riot trial, the Oregon criminal-syndicalism
 *    co-defendants of De Jonge, the Camden RCA and Vermont Marble strikes,
 *    the Oklahoma City unemployed prisoners, and the 1936 deportation drive.
 *
 * Cases already in the database are skipped: Scottsboro, Mooney/Billings,
 * McNamara/Schmidt, Centralia (Ray Becker), the Sacramento CAWIU, De Jonge
 * and Edward Denny, Herndon, the Gallup miners, Burlington NC, Reeltown
 * (Ned Cobb, the Moss brothers), Theodore Jordan, Willie Peterson, Jess
 * Hollins, Lawrence Simpson, Walter Brown, Ernest Mullins, the batch-15
 * Oklahoma City sedition four, and Alfred Miller.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddLaborDefender1936Cases extends Command
{
    protected $signature = 'prisoners:add-labor-defender-1936';

    protected $description = 'Add the 1936 Labor Defender class-war prisoners (King-Ramsay-Conner, Tampa flogging, Brown v. Mississippi, Juneau, the CIO strike and deportation cases)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── KING-RAMSAY-CONNER (Point Lobos) ─────────────────────────────
        $kingBase = "In 1936, five members of the Marine Firemen, Oilers and Watertenders' union were charged with the murder of chief engineer George Alberts aboard the freighter Point Lobos at Alameda, California — a killing the ILD held was seized on to break the militant West Coast maritime unions. Prosecuted by Alameda County District Attorney Earl Warren, the case became a famous labor frame-up; the defendants were convicted of second-degree murder and pardoned in 1941.";
        $king = [
            ['Earl King', 'Earl', 'King', "the union's leader and secretary"],
            ['Ernest Ramsay', 'Ernest', 'Ramsay', "a union patrolman"],
            ['Frank Conner', 'Frank', 'Conner', "a union member"],
            ['George Wallace', 'George', 'Wallace', "a union member"],
        ];
        foreach ($king as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} of the Marine Firemen, Oilers and Watertenders, one of the defendants in the King-Ramsay-Conner (Point Lobos) case. ".$kingBase,
                'state' => 'California', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['Marine Firemen, Oilers and Watertenders Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with the murder of engineer George Alberts aboard the Point Lobos at Alameda — a maritime-union frame-up.',
                    'convicted' => 'Convicted of second-degree murder, 1936; pardoned 1941',
                    'sentence' => 'Prison term; pardoned in 1941 after a long ILD/labor campaign.',
                    'institution_name' => 'San Quentin State Prison',
                    'institution_city' => 'San Quentin', 'institution_state' => 'California',
                ]],
            ], ['arrest_date' => [1936, 3, null]]);
        }

        // ── TAMPA FLOGGING CASE ──────────────────────────────────────────
        $tampaBase = "In November 1935, Tampa, Florida police raided a meeting of the Modern Democrats and the Workers' Alliance and, after holding the men in the police station, delivered them to a Ku Klux Klan mob that flogged and tarred them. The ILD and a joint civil-rights committee pressed the prosecution of the police-Klansmen responsible.";
        $mk([
            'name' => 'Joseph Shoemaker', 'first_name' => 'Joseph', 'last_name' => 'Shoemaker',
            'description' => "Joseph Shoemaker was a leader of the Modern Democrats and the unemployed movement in Tampa, Florida, arrested in the November 1935 police raid, handed to a Klan mob, flogged and tarred; he died of his injuries after a leg amputation. ".$tampaBase,
            'state' => 'Florida', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Workers Alliance'],
            'era' => '1930s', 'in_custody' => false, 'released' => false,
            'cases' => [[
                'charges' => 'Arrested in a police raid at Tampa and delivered to a Klan flogging mob.',
                'convicted' => 'Died of the flogging, December 1935',
                'sentence' => 'Flogged after police custody; died of his injuries.',
                'institution_city' => 'Tampa', 'institution_state' => 'Florida',
            ]],
        ], ['arrest_date' => [1935, 11, null], 'death_in_custody_date' => [1935, 12, null]]);
        foreach ([
            ['Eugene Poulnot', 'Eugene', 'Poulnot', "the chairman of the Workers' Alliance of Florida"],
            ['Sam Rogers', 'Sam', 'Rogers', "a leader of the Modern Democrats"],
        ] as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who}, arrested with Joseph Shoemaker in the November 1935 Tampa police raid, taken from the police station by Klansmen and flogged. ".$tampaBase,
                'state' => 'Florida', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['Workers Alliance'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Arrested in a Tampa police raid and delivered to a Klan flogging mob.',
                    'convicted' => 'Flogged after police custody, 1935',
                    'sentence' => 'Flogged after being held in the Tampa police station.',
                    'institution_city' => 'Tampa', 'institution_state' => 'Florida',
                ]],
            ], ['arrest_date' => [1935, 11, null]]);
        }
        foreach ([
            ['Hy Gordon', 'Hy', 'Gordon'],
            ['Jack Crawford', 'Jack', 'Crawford'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a Tampa, Florida Communist arrested in the early 1930s and, in the same police-to-Klan pattern that later killed Joseph Shoemaker, handed to a mob that beat, tarred and feathered him.",
                'state' => 'Florida', 'gender' => 'Male',
                'ideologies' => ['Communism'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Arrested at Tampa and delivered to a Klan mob.',
                    'convicted' => 'Beaten after police custody',
                    'sentence' => 'Tarred and feathered after arrest.',
                    'institution_city' => 'Tampa', 'institution_state' => 'Florida',
                ]],
            ], ['arrest_date' => [1932, null, null]]);
        }

        // ── BROWN v. MISSISSIPPI ─────────────────────────────────────────
        $brownBase = "Ed Brown, Henry Shields and Yank Ellington were three Mississippi sharecroppers convicted of murder and sentenced to death on confessions extracted by torture and the threat of lynching. In Brown v. Mississippi (1936) the U.S. Supreme Court unanimously reversed the convictions, holding that confessions coerced by physical brutality cannot be used — a landmark due-process decision.";
        foreach ([
            ['Ed Brown', 'Ed', 'Brown'],
            ['Henry Shields', 'Henry', 'Shields'],
            ['Yank Ellington', 'Yank', 'Ellington'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of three Black Mississippi sharecroppers sentenced to death on a tortured confession whose case became the landmark Brown v. Mississippi (1936). ".$brownBase,
                'state' => 'Mississippi', 'gender' => 'Male', 'race' => 'Black',
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Convicted of murder on a confession extracted by torture.',
                    'convicted' => 'Sentenced to death, 1934; reversed in Brown v. Mississippi (1936)',
                    'sentence' => 'Death; conviction overturned by the Supreme Court.',
                    'institution_state' => 'Mississippi',
                ]],
            ], []);
        }

        // ── JUNEAU, ALASKA MINE-RIOT TRIAL ───────────────────────────────
        foreach ([
            ['Warren Beavert', 'Warren', 'Beavert', "a striker whom a strikebreaker accused of an assault"],
            ['Al Nygren', 'Al', 'Nygren', "a striker"],
            ['Charles Crozier', 'Charles', 'Crozier', "the vice-president of Local 203"],
        ] as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} among the twenty-five members of Mine, Mill and Smelter Workers Local 203 charged with riot after a June 1935 march on the Alaska Juneau Gold Mine at Juneau; all twenty-five were acquitted at their November 1935 trial.",
                'state' => 'Alaska', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['Mine, Mill and Smelter Workers Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with riot after a strike march on the Alaska Juneau Gold Mine.',
                    'convicted' => 'Acquitted, November 1935',
                    'sentence' => 'Held for trial; acquitted.',
                    'institution_city' => 'Juneau', 'institution_state' => 'Alaska',
                ]],
            ], ['arrest_date' => [1935, 6, null]]);
        }

        // ── OREGON CRIMINAL SYNDICALISM — De Jonge co-defendants ─────────
        foreach ([
            ['Earl Steward', 'Earl', 'Steward', ''],
            ['John Weber', 'John', 'Weber', ''],
            ['Kenneth Austin', 'Kenneth', 'Austin', ', a striking seaman,'],
            ['Donald Austin', 'Donald', 'Austin', ', a striking seaman,'],
            ['Manly Mitchell', 'Manly', 'Mitchell', ', a striking seaman,'],
        ] as [$name, $first, $last, $extra]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name}{$extra} was prosecuted under Oregon's criminal-syndicalism law in the Portland drive that also convicted Dirk De Jonge, for helping organize support for the 1934 West Coast waterfront strike.",
                'state' => 'Oregon', 'gender' => 'Male',
                'ideologies' => ['Communism'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => "Prosecuted under Oregon's criminal-syndicalism law in the Portland waterfront-strike cases.",
                    'convicted' => 'Prosecuted for criminal syndicalism, 1935–36',
                    'sentence' => 'Held / convicted under the Oregon criminal-syndicalism law.',
                    'institution_state' => 'Oregon',
                ]],
            ], ['arrest_date' => [1934, null, null]]);
        }

        // ── MODESTO DYNAMITE CASE (maritime workers) ─────────────────────
        $modestoBase = "The \"Modesto Boys\" were eight maritime workers framed on a charge of possessing dynamite after the April 1935 tanker strike, when Standard Oil agents and Stanislaus County deputies stopped them on the highway near Modesto, California and — the ILD showed — planted the explosives. They were convicted in July 1935 and sent to San Quentin.";
        foreach ([
            ['Henry Silva', 'Henry', 'Silva'],
            ['Ruell Stanfield', 'Ruell', 'Stanfield'],
            ['Vic Johnson', 'Vic', 'Johnson'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of the eight Modesto maritime workers framed on a dynamite charge after the 1935 tanker strike and imprisoned at San Quentin. ".$modestoBase,
                'state' => 'California', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['Marine Firemen, Oilers and Watertenders Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Framed on a dynamite-possession charge after the 1935 tanker strike near Modesto.',
                    'convicted' => 'Convicted, July 1935',
                    'sentence' => 'Six months to five years at San Quentin.',
                    'institution_name' => 'San Quentin State Prison',
                    'institution_city' => 'San Quentin', 'institution_state' => 'California',
                ]],
            ], ['arrest_date' => [1935, 4, 21]]);
        }

        // ── OKLAHOMA CITY UNEMPLOYED (Leavenworth) — new names ───────────
        foreach ([
            ['C. C. Nesbitt', 'C. C.', 'Nesbitt', 'Male'],
            ['Joe Paskvam', 'Joe', 'Paskvam', 'Male'],
            ['Dan Womack', 'Dan', 'Womack', 'Male'],
            ['Harry Snyder', 'Harry', 'Snyder', 'Male'],
            ['Wilma Conners', 'Wilma', 'Conners', 'Female'],
        ] as [$name, $first, $last, $gender]) {
            $inst = $gender === 'Female' ? 'Federal Industrial Institution for Women' : 'Leavenworth Penitentiary';
            $city = $gender === 'Female' ? 'Alderson' : 'Leavenworth';
            $state = $gender === 'Female' ? 'West Virginia' : 'Kansas';
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of the Oklahoma City unemployed workers convicted under a federal statute after a 1934 relief demonstration demanding food, and imprisoned for a year — the men at Leavenworth and the women at the federal women's prison at Alderson.",
                'state' => 'Oklahoma', 'gender' => $gender,
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['Unemployed Councils'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Convicted under a federal statute for an Oklahoma City relief demonstration.',
                    'convicted' => 'Convicted, 1934–35',
                    'sentence' => "About one year at the {$inst}.",
                    'institution_name' => $inst,
                    'institution_city' => $city, 'institution_state' => $state,
                ]],
            ], ['incarceration_date' => [1935, null, null]]);
        }

        // ── CIO / STRIKE FRAME-UPS ───────────────────────────────────────
        $mk([
            'name' => 'James Carey', 'first_name' => 'James', 'last_name' => 'Carey', 'middle_name' => 'B.',
            'description' => "James B. Carey was the national president of the United Electrical and Radio Workers of America (CIO), arrested and held without bail during the June–July 1936 RCA-Victor strike at Camden, New Jersey and convicted of \"disorderly conduct\" with a sixty-day sentence, one of some ninety strikers held on over half a million dollars in aggregate bail.",
            'state' => 'New Jersey', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['United Electrical Radio and Machine Workers'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with disorderly conduct in the Camden RCA-Victor strike.',
                'convicted' => 'Convicted, 1936',
                'sentence' => 'Sixty days; held without bail.',
                'institution_city' => 'Camden', 'institution_state' => 'New Jersey',
            ]],
        ], ['arrest_date' => [1936, 6, null]]);
        $mk([
            'name' => 'Paul Yaskar', 'first_name' => 'Paul', 'last_name' => 'Yaskar',
            'description' => "Paul Yaskar was one of five Vermont Marble Company strikers imprisoned for one to two years in the state penitentiary after the 1936 marble strike at Rutland, Vermont.",
            'state' => 'Vermont', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed in the 1936 Vermont Marble Company strike.',
                'convicted' => 'Convicted, 1936',
                'sentence' => 'One to two years in the state penitentiary.',
                'institution_state' => 'Vermont',
            ]],
        ], ['incarceration_date' => [1936, null, null]]);
        $mk([
            'name' => 'Murray Melvin', 'first_name' => 'Murray', 'last_name' => 'Melvin',
            'description' => "Murray Melvin was a twenty-four-year-old vice-president of the Allied Printing Helpers' Union in New York, framed on a charge of assaulting a strikebreaker during the 1935 Typographic Service Company strike and sentenced in April 1936 to an indeterminate term on Rikers Island; a Defense Committee chaired by Heywood Broun won his release after six months.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Allied Printing Helpers Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed for assaulting a strikebreaker in a New York printing strike.',
                'convicted' => 'Convicted, 6 April 1936',
                'sentence' => 'Indeterminate term at Rikers Island; released after six months.',
                'institution_name' => 'Rikers Island Penitentiary',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['incarceration_date' => [1936, 4, 6]]);
        $mk([
            'name' => 'Concha Cantu', 'first_name' => 'Concha', 'last_name' => 'Cantu',
            'description' => "Concha Cantu was a secretary of the Fish Cannery Workers' Defense Committee and one of five women strikers of Fish Cannery Workers Union Local 20147 arrested by the San Pedro \"red squad\" in the December 1935 Coast Fishing Company strike at Wilmington, California, and sentenced to thirty days in the Lincoln Heights jail.",
            'state' => 'California', 'gender' => 'Female',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Fish Cannery Workers Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested in the Wilmington fish-cannery strike for talking to strikebreakers.',
                'convicted' => 'Convicted, 1936',
                'sentence' => 'Thirty days in the Lincoln Heights jail.',
                'institution_name' => 'Lincoln Heights Jail',
                'institution_city' => 'Los Angeles', 'institution_state' => 'California',
            ]],
        ], ['arrest_date' => [1935, 12, 26]]);
        foreach ([
            ['William Clay', 'William', 'Clay', "a one-armed dock worker clubbed unconscious and then charged with felonious assault"],
            ['Frank Goodall', 'Frank', 'Goodall', "a striking seaman held for trial"],
        ] as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} in the 1936 East Coast seamen's strike, when 221 pickets were arrested in a single police attack on the New York waterfront.",
                'state' => 'New York', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['International Seamen\'s Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Arrested on a New York picket line in the 1936 seamen\'s strike.',
                    'convicted' => 'Held for trial, 1936',
                    'sentence' => 'Held on strike charges; defended by the ILD.',
                    'institution_city' => 'New York', 'institution_state' => 'New York',
                ]],
            ], ['arrest_date' => [1936, 5, null]]);
        }

        // ── CRIMINAL SYNDICALISM / SEDITION INDIVIDUALS ──────────────────
        $mk([
            'name' => 'Paul Butash', 'first_name' => 'Paul', 'last_name' => 'Butash',
            'description' => "Paul Butash was a magazine salesman convicted in twenty minutes by an Angola, Indiana jury under the state's criminal-syndicalism law after a Ku Klux Klan and American Legion entrapment at a staged \"student forum\"; he had advocated a Farmer-Labor Party. He was sentenced to one to five years.",
            'state' => 'Indiana', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Convicted under Indiana's criminal-syndicalism law after a Klan/Legion entrapment.",
                'convicted' => 'Convicted, 1936',
                'sentence' => 'One to five years.',
                'institution_city' => 'Angola', 'institution_state' => 'Indiana',
            ]],
        ], ['arrest_date' => [1936, null, null]]);
        $mk([
            'name' => 'Jack Barton', 'first_name' => 'Jack', 'last_name' => 'Barton',
            'description' => "Jack Barton was a Communist organizer jailed 380 days in the Bessemer, Alabama city jail on a charge of \"possessing seditious literature,\" contracting tuberculosis; his conviction was reversed in November 1936.",
            'state' => 'Alabama', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'affiliation' => ['Communist Party USA'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with possessing seditious literature at Bessemer, Alabama.',
                'convicted' => 'Convicted; reversed November 1936',
                'sentence' => '380 days in the Bessemer city jail.',
                'institution_city' => 'Bessemer', 'institution_state' => 'Alabama',
            ]],
        ], ['arrest_date' => [1935, null, null]]);
        $mk([
            'name' => 'Homer Brooks', 'first_name' => 'Homer', 'last_name' => 'Brooks',
            'description' => "Homer Brooks was the Communist candidate for governor of Texas, jailed at Port Arthur in 1936 while campaigning.",
            'state' => 'Texas', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'affiliation' => ['Communist Party USA'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed while campaigning as a Communist candidate at Port Arthur, Texas.',
                'convicted' => 'Jailed, 1936',
                'sentence' => 'Held at Port Arthur.',
                'institution_city' => 'Port Arthur', 'institution_state' => 'Texas',
            ]],
        ], ['arrest_date' => [1936, null, null]]);
        $mk([
            'name' => 'Charles Sotis', 'first_name' => 'Charles', 'last_name' => 'Sotis',
            'description' => "Charles Sotis was a Communist organizer of Chicago stockyards workers, beaten by police and charged with perjury on the claim that he was a Communist when he was naturalized; he was acquitted on a directed verdict in federal court in 1936.",
            'state' => 'Illinois', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['Communist Party USA'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with naturalization perjury for being a Communist, Chicago.',
                'convicted' => 'Acquitted on a directed verdict, 1936',
                'sentence' => 'Held for trial; acquitted.',
                'institution_city' => 'Chicago', 'institution_state' => 'Illinois',
            ]],
        ], ['arrest_date' => [1936, null, null]]);
        $mk([
            'name' => 'Pete Turney', 'first_name' => 'Pete', 'last_name' => 'Turney',
            'description' => "Pete Turney was a Black worker of Birmingham, Alabama convicted of \"criminal libel\" for carrying a leaflet that called a brutal police official a rat, and sentenced to an Alabama chain gang into late 1936.",
            'state' => 'Alabama', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted of criminal libel for a leaflet criticizing a Birmingham police official.',
                'convicted' => 'Convicted, 1936',
                'sentence' => 'Alabama chain gang.',
                'institution_city' => 'Birmingham', 'institution_state' => 'Alabama',
            ]],
        ], []);
        $mk([
            'name' => 'Naum Acheff', 'first_name' => 'Naum', 'last_name' => 'Acheff',
            'description' => "Naum Acheff served two years in the Blawnox Workhouse in Allegheny County, Pennsylvania for helping stop an eviction, subjected to blackjacking and ball-and-chain punishment.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed for resisting an eviction in Allegheny County, Pennsylvania.',
                'convicted' => 'Convicted; served two years',
                'sentence' => 'Two years in the Blawnox Workhouse.',
                'institution_name' => 'Allegheny County Workhouse',
                'institution_city' => 'Blawnox', 'institution_state' => 'Pennsylvania',
            ]],
        ], []);
        $mk([
            'name' => 'Elinor Swimmer', 'first_name' => 'Elinor', 'last_name' => 'Swimmer',
            'description' => "Elinor Swimmer was a Chicago worker committed to a sanity hearing in 1936 in a frame-up over a circular she distributed for Communist leader Earl Browder.",
            'state' => 'Illinois', 'gender' => 'Female',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Committed to a sanity hearing over a Communist circular at Chicago.',
                'convicted' => 'Held, 1936',
                'sentence' => 'Held on the sanity-hearing frame-up.',
                'institution_city' => 'Chicago', 'institution_state' => 'Illinois',
            ]],
        ], ['arrest_date' => [1936, null, null]]);

        // ── LONG-TERM PRISON-LETTER PRISONERS ────────────────────────────
        $mk([
            'name' => 'Charles Bock', 'first_name' => 'Charles', 'last_name' => 'Bock',
            'description' => "Charles Bock was a West Virginia coal miner serving a ninety-nine-year sentence at the Moundsville penitentiary on a frame-up arising from an early-1930s miners' strike.",
            'state' => 'West Virginia', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['National Miners Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed in a West Virginia miners\' strike.',
                'convicted' => 'Sentenced to 99 years',
                'sentence' => 'Ninety-nine years at the Moundsville penitentiary.',
                'institution_name' => 'West Virginia Penitentiary',
                'institution_city' => 'Moundsville', 'institution_state' => 'West Virginia',
            ]],
        ], []);
        $mk([
            'name' => 'Raymond McSurley', 'first_name' => 'Raymond', 'last_name' => 'McSurley',
            'description' => "Raymond McSurley was a West Virginia labor prisoner serving a twelve-year sentence at the Moundsville penitentiary for his part in a strike.",
            'state' => 'West Virginia', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed for strike activity in West Virginia.',
                'convicted' => 'Sentenced to 12 years',
                'sentence' => 'Twelve years at the Moundsville penitentiary.',
                'institution_name' => 'West Virginia Penitentiary',
                'institution_city' => 'Moundsville', 'institution_state' => 'West Virginia',
            ]],
        ], []);
        $mk([
            'name' => 'C. W. Reno', 'first_name' => 'C. W.', 'last_name' => 'Reno',
            'description' => "C. W. Reno was a Harlan County, Kentucky miner serving a life sentence on a frame-up murder charge arising from the 1931 Harlan coal strike.",
            'state' => 'Kentucky', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['National Miners Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed on a murder charge from the 1931 Harlan coal strike.',
                'convicted' => 'Sentenced to life',
                'sentence' => 'Life imprisonment.',
                'institution_state' => 'Kentucky',
            ]],
        ], []);

        // ── ARKANSAS STFU ────────────────────────────────────────────────
        foreach ([
            ['Josephine Johnson', 'Josephine', 'Johnson', 'Female', "a novelist"],
            ['Joe Jones', 'Joe', 'Jones', 'Male', "an artist"],
        ] as [$name, $first, $last, $gender, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} arrested at Earle, in Crittenden County, Arkansas in June 1936 for gathering material on the Southern Tenant Farmers' Union sharecroppers' strike amid the planters' terror.",
                'state' => 'Arkansas', 'gender' => $gender,
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['Southern Tenant Farmers Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Arrested in the Arkansas STFU sharecroppers\' strike terror.',
                    'convicted' => 'Arrested, June 1936',
                    'sentence' => 'Held after the arrest.',
                    'institution_city' => 'Earle', 'institution_state' => 'Arkansas',
                ]],
            ], ['arrest_date' => [1936, 6, null]]);
        }

        // ── LINCOLN HEIGHTS JAIL (LA unemployed) ─────────────────────────
        foreach ([
            ['John Sanders', 'John', 'Sanders'],
            ['Earl Wilkenson', 'Earl', 'Wilkenson'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a class-war prisoner held in the Lincoln Heights jail in Los Angeles in 1935–36 for unemployed-movement activity, censored and threatened with solitary confinement for writing to the ILD.",
                'state' => 'California', 'gender' => 'Male',
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['Unemployed Councils'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed for unemployed-movement activity in Los Angeles.',
                    'convicted' => 'Held, 1935–36',
                    'sentence' => 'Held in the Lincoln Heights jail.',
                    'institution_name' => 'Lincoln Heights Jail',
                    'institution_city' => 'Los Angeles', 'institution_state' => 'California',
                ]],
            ], ['arrest_date' => [1935, null, null]]);
        }

        // ── DEPORTATION DRIVE ────────────────────────────────────────────
        $deport = [
            ['Charlie Rowoldt', 'Charlie', 'Rowoldt', 'Germany', "a German-born worker of twenty-two years' U.S. residence jailed and held for deportation to Nazi Germany solely for Communist Party membership — the future Rowoldt v. Perfetto litigant"],
            ['Walter Baer', 'Walter', 'Baer', 'Germany', "a Portland, Oregon anti-fascist held two years in immigration custody and on Ellis Island facing deportation to Nazi Germany"],
            ['Whirlwind Larson', 'Whirlwind', 'Larson', 'Sweden', "a Chicago Daily Worker salesman and immigrant since 1904, repeatedly denied naturalization for his labor activity and arrested for deportation in 1936"],
            ['Vincent Ferrero', 'Vincent', 'Ferrero', 'Italy', "an Italian anarchist and restaurant worker of San Francisco held for deportation to Fascist Italy"],
            ['Domenick Sallitto', 'Domenick', 'Sallitto', 'Italy', "an Italian anarchist of San Francisco held for deportation to Fascist Italy alongside Vincent Ferrero"],
        ];
        foreach ($deport as [$name, $first, $last, $origin, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} in the deportation drive of 1936.",
                'gender' => 'Male',
                'ideologies' => in_array($first, ['Vincent', 'Domenick']) ? ['Anarchism'] : ['Communism'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Held for deportation for radical labor or anti-fascist activity, 1936.',
                    'convicted' => 'Held for deportation, 1936',
                    'sentence' => 'Held pending deportation; defended by the ILD.',
                ]],
            ], ['arrest_date' => [1936, null, null]]);
        }

        // ── RAPE / MURDER FRAME-UP DEFENDANTS ────────────────────────────
        foreach ([
            ['Clyde Allen', 'Clyde', 'Allen', 'New York', "framed as the Brooklyn tabloid \"Hammer Man\" and sentenced to thirty-five years before the ILD won him a new trial"],
            ['Roy Williams', 'Roy', 'Williams', 'Nebraska', "a Black worker held on a frame-up rape charge"],
            ['Alphonso Davis', 'Alphonso', 'Davis', 'New York', "a Black worker held on a frame-up rape charge"],
            ['William Fisher', 'William', 'Fisher', 'New York', "a Black worker the ILD held was the victim of a legal lynching behind a murder charge"],
        ] as [$name, $first, $last, $state, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} — one of the Depression-era frame-up cases of Black workers the ILD publicized in 1936.",
                'state' => $state, 'gender' => 'Male', 'race' => 'Black',
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Held on a frame-up charge in a 1936 ILD defense case.',
                    'convicted' => 'Held / convicted, 1936',
                    'sentence' => 'Held on the frame-up; taken up by the ILD.',
                    'institution_state' => $state,
                ]],
            ], []);
        }

        // ── INSERT ───────────────────────────────────────────────────────
        $added = 0;
        foreach ($people as $person) {
            $payload = $person['payload'];
            $payload['in_custody'] = false;
            if (! array_key_exists('released', $payload)) {
                $payload['released'] = true;
            }

            $existing = Prisoner::withoutGlobalScopes()
                ->where('name', 'like', '%'.$payload['first_name'].'%')
                ->where('name', 'like', '%'.$payload['last_name'].'%')
                ->first();
            if ($existing) {
                $this->line("  already in database as \"{$existing->name}\" — skipping {$payload['name']}.");

                continue;
            }

            $this->call('prisoner:add', ['json' => json_encode($payload)]);

            $prisoner = Prisoner::withoutGlobalScopes()->where('name', $payload['name'])->first();
            if (! $prisoner) {
                continue;
            }
            $prisoner->in_custody = false;
            $prisoner->released = $payload['released'];
            $prisoner->save();

            $case = $prisoner->cases()->first();
            if ($case && ! empty($person['dates'])) {
                foreach ($person['dates'] as $field => [$y, $m, $d]) {
                    $case->setPartialDate($field, $y, $m, $d);
                }
                $case->save();
            }
            $added++;
        }

        $this->info("\nDone. Processed {$added} of ".count($people)." 1936 Labor Defender prisoner(s).");

        return self::SUCCESS;
    }
}
