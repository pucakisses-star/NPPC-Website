<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * New Masses mining, batch 2 — 1934, the first weekly year.
 *
 * 1934 was the great strike-wave year (the San Francisco general strike and
 * "Bloody Thursday," the Minneapolis Teamsters and Toledo Auto-Lite battles,
 * the nationwide textile strike, the Sacramento CAWIU criminal-syndicalism
 * trial, and the Birmingham/Deep-South repression). New Masses, now a weekly,
 * carried dense reportage of the arrests. The marquee cases are already in the
 * database from the Labor Defender run — Angelo Herndon, Tom Mooney & Warren
 * Billings, the Scottsboro defendants, the Sacramento CAWIU group (Chambers,
 * Decker, Mini, Wilson, Norman, Hougardy, Crane, Warnick), Ned Cobb, James
 * Victory, Alexander Racolin, Jan Wittenber, the Atlanta IWO defendants
 * (Yagol/Moreland/Leathers/Young), Phil Frankfeld, Jim Reynolds, Nile Cochran,
 * Theodore Jordan, Jesse Hann, Alfred Tiala, Donald Henderson — all skipped.
 *
 * This adds the genuinely-new, individually named US class-war prisoners of
 * 1934 that had no record: the Birmingham roundups, the WWI artist-objector
 * Maurice Becker, the New York needle-trades and cafeteria cases, the
 * Charlestown anti-Nazi protest, the Jersey City furniture strike under Mayor
 * Hague, the Hillsboro/Springfield Illinois unemployed cases, scattered steel,
 * textile, bus and Communist-campaign frame-ups, an Indiana farm case, and the
 * July 1934 San Francisco general-strike vigilante raids.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddNewMasses1934Cases extends Command
{
    protected $signature = 'prisoners:add-new-masses-1934';

    protected $description = 'Add the genuinely-new US class-war prisoners surfaced mining New Masses 1934 (Birmingham repression, NY needle-trades, the Jersey City furniture strike, Hillsboro IL, the San Francisco general-strike raids, and more)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── BIRMINGHAM / ALABAMA REPRESSION ─────────────────────────────
        $mk([
            'name' => 'John Howard Lawson', 'first_name' => 'John Howard', 'last_name' => 'Lawson',
            'description' => "John Howard Lawson (1894–1977) was a playwright and screenwriter — later the first president of the Screen Writers Guild and one of the Hollywood Ten — who traveled to Birmingham, Alabama in 1934 with a National Committee for the Defense of Political Prisoners delegation investigating the Scottsboro and other Black-defendant cases. He was arrested by Birmingham police on a trumped-up 'criminal libel' charge over a dispatch he filed to the Daily Worker reporting racial prejudice in the courts, and was arrested more than once during that reporting.",
            'state' => 'Alabama', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Arrested on a 'criminal libel' charge for reporting Alabama court racism for the Daily Worker.",
                'convicted' => 'Arrested, 1934',
                'sentence' => 'Held; case fought by the ILD.',
                'institution_city' => 'Birmingham', 'institution_state' => 'Alabama',
            ]],
        ], ['arrest_date' => [1934, null, null]]);

        $mk([
            'name' => 'Nat Goodwin', 'first_name' => 'Nat', 'last_name' => 'Goodwin',
            'description' => "Nat Goodwin was a Black worker arrested in Birmingham, Alabama in early 1934 and held incommunicado for coming to the defense of Jane Speed, a white Communist organizer seized for addressing a mixed-race crowd of unemployed workers. His jailing was part of the wave of racial-political repression the International Labor Defense publicized in the Deep South.",
            'state' => 'Alabama', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Held incommunicado for defending a Communist organizer at an unemployed meeting.',
                'convicted' => 'Held, 1934',
                'sentence' => 'Held incommunicado.',
                'institution_city' => 'Birmingham', 'institution_state' => 'Alabama',
            ]],
        ], ['arrest_date' => [1934, 1, null]]);

        $mk([
            'name' => 'Harold Ralston', 'first_name' => 'Harold', 'last_name' => 'Ralston',
            'description' => "Harold Ralston was an Alabama Communist Party organizer and one of six Communists tried on vagrancy charges before Judge Abernathy in the Jefferson County court, Birmingham — part of the city's 1934 campaign to jail radical labor and unemployed organizers under catch-all vagrancy statutes. Jailed around May 1934, he was, after release, targeted with death threats by the Klan-linked White Legion.",
            'state' => 'Alabama', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Tried on vagrancy charges as a Communist organizer, Jefferson County court.',
                'convicted' => 'Jailed, 1934',
                'sentence' => 'Jailed; released, then threatened by the White Legion.',
                'institution_city' => 'Birmingham', 'institution_state' => 'Alabama',
            ]],
        ], ['arrest_date' => [1934, 5, null]]);

        $mk([
            'name' => 'Laura Stark', 'first_name' => 'Laura', 'last_name' => 'Stark',
            'description' => "Laura Stark was arrested in May 1934 in the police raid on the International Labor Defense office in Birmingham, Alabama, seized alongside ILD attorney Alexander Racolin as part of the drive to shut down Communist and defense-organization work in the city.",
            'state' => 'Alabama', 'gender' => 'Female',
            'ideologies' => ['Communism'],
            'affiliation' => ['International Labor Defense'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested in the police raid on the Birmingham ILD office.',
                'convicted' => 'Arrested, 1934',
                'sentence' => 'Held.',
                'institution_city' => 'Birmingham', 'institution_state' => 'Alabama',
            ]],
        ], ['arrest_date' => [1934, 5, null]]);

        // ── WWI ARTIST-OBJECTOR (retrospective, recalled 1934) ──────────
        $mk([
            'name' => 'Maurice Becker', 'first_name' => 'Maurice', 'last_name' => 'Becker',
            'description' => "Maurice Becker (1889–1975) was a radical artist for The Masses who, as a World War I conscientious objector, was court-martialed and sentenced to twenty-five years' hard labor at Fort Leavenworth, Kansas. He took part in a prisoners' strike there before his release. His imprisonment was recalled in a first-person letter published in New Masses in 1934.",
            'state' => 'Kansas', 'gender' => 'Male',
            'ideologies' => ['Anti-war', 'Socialism'],
            'era' => '1910s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Court-martialed as a World War I conscientious objector.',
                'convicted' => 'Court-martialed, WWI era',
                'sentence' => "Twenty-five years' hard labor at Fort Leavenworth; later released.",
                'institution_name' => 'United States Disciplinary Barracks, Fort Leavenworth',
                'institution_city' => 'Leavenworth', 'institution_state' => 'Kansas',
            ]],
        ], []);

        // ── NEW YORK: NEEDLE TRADES, CAFETERIA, LAUNDRY ─────────────────
        $mk([
            'name' => 'Hyman Denowitz', 'first_name' => 'Hyman', 'last_name' => 'Denowitz',
            'description' => "Hyman Denowitz was a needle-trades union organizer framed on an assault charge arising from union activity, sentenced to eighteen months and imprisoned at the New York City Reformatory at New Hampton, New York (inmate No. 12714). He appeared on the Labor Defender's roster of imprisoned needle-trades class-war prisoners.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Needle Trades Workers Industrial Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed on an assault charge in needle-trades organizing.',
                'convicted' => 'Convicted, 1933',
                'sentence' => 'Eighteen months at the New York City Reformatory, New Hampton.',
                'institution_name' => 'New York City Reformatory',
                'institution_city' => 'New Hampton', 'institution_state' => 'New York',
            ]],
        ], []);

        $mk([
            'name' => 'Israel Simon', 'first_name' => 'Israel', 'last_name' => 'Simon',
            'description' => "Israel Simon was a needle-trades organizer framed on an assault charge during union organizing and given an indefinite reformatory sentence in the Bronx, New York. He appeared on the Labor Defender's roster of imprisoned needle-trades class-war prisoners.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Needle Trades Workers Industrial Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed on an assault charge in needle-trades organizing.',
                'convicted' => 'Convicted, early 1930s',
                'sentence' => 'Indefinite reformatory sentence.',
                'institution_state' => 'New York',
            ]],
        ], []);

        $mk([
            'name' => 'Patsy Augustine', 'first_name' => 'Patsy', 'last_name' => 'Augustine',
            'description' => "Patsy Augustine was a member of the Cafeteria Workers' Union in New York City arrested on a trumped-up charge during a 1934 strike and beaten in custody by police. The International Labor Defense took up the case as an example of police brutality against food-service strikers.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ["Cafeteria Workers' Union"],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested on a trumped-up charge during a cafeteria-workers strike.',
                'convicted' => 'Arrested, 1934',
                'sentence' => 'Held and beaten in custody.',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['arrest_date' => [1934, null, null]]);

        $mk([
            'name' => 'Leon Blum', 'first_name' => 'Leon', 'last_name' => 'Blum',
            'description' => "Leon Blum was secretary of the Laundry Workers Industrial Union in the Bronx, imprisoned at Great Meadow Prison in Comstock, New York around 1934 for his union activity. Snatched back into custody on an old parole after charges against him were dropped, he served roughly a three-year term and reported the denial of labor literature to prisoners. (Not the French politician of the same name.)",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Laundry Workers Industrial Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Imprisoned for laundry-workers union activity; re-jailed on an old parole.',
                'convicted' => 'Imprisoned, c. 1934',
                'sentence' => 'About three years at Great Meadow Prison, Comstock.',
                'institution_name' => 'Great Meadow Prison',
                'institution_city' => 'Comstock', 'institution_state' => 'New York',
            ]],
        ], []);

        // ── CHARLESTOWN ANTI-NAZI PROTEST (Karlsruhe) ───────────────────
        $mk([
            'name' => 'Allen Kellogg Philbrick', 'first_name' => 'Allen', 'last_name' => 'Philbrick',
            'description' => "Allen Kellogg Philbrick was a Harvard student and secretary of the National Students League who was arrested in 1934 aboard the visiting German training cruiser Karlsruhe at the Charlestown Navy Yard while dropping anti-Nazi leaflets in protest of the swastika-flagged goodwill visit. He was taken into custody and interrogated for hours by the Boston police 'red squad.'",
            'state' => 'Massachusetts', 'gender' => 'Male',
            'ideologies' => ['Anti-fascism'],
            'affiliation' => ['National Students League'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested for dropping anti-Nazi leaflets aboard the cruiser Karlsruhe.',
                'convicted' => 'Arrested, 1934',
                'sentence' => 'Held and interrogated by the Boston red squad; released.',
                'institution_city' => 'Charlestown', 'institution_state' => 'Massachusetts',
            ]],
        ], ['arrest_date' => [1934, null, null]]);

        // ── JERSEY CITY FURNITURE STRIKE (Mayor Hague regime) ───────────
        $mk([
            'name' => 'Corliss Lamont', 'first_name' => 'Corliss', 'last_name' => 'Lamont',
            'description' => "Corliss Lamont (1902–1995) was a philosopher and lifelong civil-liberties advocate — son of the Morgan partner Thomas W. Lamont — arrested as a picket during the 1934 furniture-workers' lockout at the Miller Furniture Company in Jersey City, New Jersey, defying Mayor Frank Hague's suppression of labor and radical assembly. His arrest dramatized the fight over the right to picket.",
            'state' => 'New Jersey', 'gender' => 'Male',
            'ideologies' => ['Civil liberties', 'Socialism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested for anti-injunction picketing in the Jersey City furniture strike.',
                'convicted' => 'Arrested, 1934',
                'sentence' => 'Jailed, fingerprinted; bail set.',
                'institution_city' => 'Jersey City', 'institution_state' => 'New Jersey',
            ]],
        ], ['arrest_date' => [1934, null, null]]);

        $mk([
            'name' => 'Alfred M. Bingham', 'first_name' => 'Alfred', 'last_name' => 'Bingham',
            'description' => "Alfred M. Bingham (1905–1998) was the editor of the independent-radical magazine Common Sense who was arrested with Corliss Lamont in 1934 for picketing in Jersey City, New Jersey, against Mayor Frank Hague's anti-picketing regime.",
            'state' => 'New Jersey', 'gender' => 'Male',
            'ideologies' => ['Socialism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested for picketing in Jersey City against the Hague anti-picketing regime.',
                'convicted' => 'Arrested, 1934',
                'sentence' => 'Held; released.',
                'institution_city' => 'Jersey City', 'institution_state' => 'New Jersey',
            ]],
        ], ['arrest_date' => [1934, null, null]]);

        $mk([
            'name' => 'Alfred H. Hirsch', 'first_name' => 'Alfred', 'last_name' => 'Hirsch',
            'description' => "Alfred H. Hirsch was secretary of the National Committee for the Defense of Political Prisoners — the intellectuals' defense body associated with Theodore Dreiser — jailed in Jersey City, New Jersey in 1934 while observing the furniture-strike picket line, one of many arrests under Mayor Frank Hague's anti-labor crackdown.",
            'state' => 'New Jersey', 'gender' => 'Male',
            'ideologies' => ['Civil liberties'],
            'affiliation' => ['National Committee for the Defense of Political Prisoners'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed for observing the Jersey City furniture-strike picket line.',
                'convicted' => 'Arrested, 1934',
                'sentence' => 'Held.',
                'institution_city' => 'Jersey City', 'institution_state' => 'New Jersey',
            ]],
        ], ['arrest_date' => [1934, null, null]]);

        $mk([
            'name' => 'Sarah Bleeker', 'first_name' => 'Sarah', 'last_name' => 'Bleeker',
            'description' => "Sarah Bleeker was arrested for picketing during the 1934 furniture-workers' strike in Jersey City, New Jersey, one of many detained under Mayor Frank Hague's suppression of strike activity.",
            'state' => 'New Jersey', 'gender' => 'Female',
            'ideologies' => ['Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested for picketing in the Jersey City furniture-workers strike.',
                'convicted' => 'Arrested, 1934',
                'sentence' => 'Held.',
                'institution_city' => 'Jersey City', 'institution_state' => 'New Jersey',
            ]],
        ], ['arrest_date' => [1934, null, null]]);

        // ── HILLSBORO / SPRINGFIELD, ILLINOIS (unemployed movement) ─────
        $mk([
            'name' => 'Gordon Hutchins', 'first_name' => 'Gordon', 'last_name' => 'Hutchins',
            'description' => "Gordon Hutchins was a local Communist Party organizer arrested at Hillsboro (Montgomery County), Illinois in June 1934 as one of eleven Unemployed Council leaders charged under the state's 1919 sedition law with conspiracy to overthrow the government — one of the last major uses of Illinois' World War I–era sedition statute, tied to unrest in the Progressive Miners' coalfields.",
            'state' => 'Illinois', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'affiliation' => ['Unemployed Councils'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Charged under Illinois' 1919 sedition law with conspiracy to overthrow the government.",
                'convicted' => 'Held, 1934',
                'sentence' => 'Held under heavy bail in the Montgomery County jail.',
                'institution_city' => 'Hillsboro', 'institution_state' => 'Illinois',
            ]],
        ], ['arrest_date' => [1934, 6, null]]);

        $mk([
            'name' => 'Ann Morton', 'first_name' => 'Ann', 'last_name' => 'Morton',
            'description' => "Ann Morton was a 23-year-old coal miner's daughter jailed in 1934 while leading an unemployed demonstration at the Sangamon County courthouse in Springfield, Illinois — one of many rank-and-file Unemployed Council activists arrested during the relief protests of the central-Illinois coalfields.",
            'state' => 'Illinois', 'gender' => 'Female',
            'ideologies' => ['Communism'],
            'affiliation' => ['Unemployed Councils'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed for leading an unemployed demonstration at the Sangamon County courthouse.',
                'convicted' => 'Jailed, 1934',
                'sentence' => 'Jailed.',
                'institution_city' => 'Springfield', 'institution_state' => 'Illinois',
            ]],
        ], ['arrest_date' => [1934, null, null]]);

        // ── STEEL, TEXTILE, BUS, CAMPAIGN FRAME-UPS ─────────────────────
        $mk([
            'name' => 'James Egan', 'first_name' => 'James', 'last_name' => 'Egan',
            'description' => "James Egan was secretary of the Communist-led Steel and Metal Workers Industrial Union. After the 1933–34 struggle at Ambridge, Pennsylvania, where sheriff's deputies attacked and killed striking steelworkers, he was prosecuted for his organizing and sentenced to a year in prison, a case the ILD publicized in the drive to crush steel-union organizing in the Beaver Valley.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Steel and Metal Workers Industrial Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Prosecuted for organizing steel workers after the Ambridge strike.',
                'convicted' => 'Convicted, 1934',
                'sentence' => 'One year in prison.',
                'institution_city' => 'Ambridge', 'institution_state' => 'Pennsylvania',
            ]],
        ], ['incarceration_date' => [1934, null, null]]);

        $mk([
            'name' => 'Oreal Grossman', 'first_name' => 'Oreal', 'last_name' => 'Grossman',
            'description' => "Oreal Grossman was an International Labor Defense attorney in Providence, Rhode Island, arrested during the September 1934 general textile strike when he went to defend jailed strikers on the day the Communist Party was outlawed in the state. He was held incommunicado for 36 hours, then released on bail on a flimsy lottery charge.",
            'state' => 'Rhode Island', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['International Labor Defense'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested while defending jailed textile strikers.',
                'convicted' => 'Held, 1934',
                'sentence' => 'Held incommunicado 36 hours; released on bail.',
                'institution_city' => 'Providence', 'institution_state' => 'Rhode Island',
            ]],
        ], ['arrest_date' => [1934, 9, null]]);

        $mk([
            'name' => 'Ralph Stoltzmann', 'first_name' => 'Ralph', 'last_name' => 'Stoltzmann',
            'description' => "Ralph Stoltzmann was a Chicago union official arrested in 1934 on a murder charge arising from the killing of a dispatcher during a bus strike — a labor frame-up the International Labor Defense contested.",
            'state' => 'Illinois', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with murder in a bus-strike frame-up.',
                'convicted' => 'Arrested, 1934',
                'sentence' => 'Held; case contested by the ILD.',
                'institution_city' => 'Chicago', 'institution_state' => 'Illinois',
            ]],
        ], ['arrest_date' => [1934, null, null]]);

        $mk([
            'name' => 'Alexander Wright', 'first_name' => 'Alexander', 'last_name' => 'Wright',
            'description' => "Alexander Wright was the Communist Party candidate for U.S. Senator arrested in Newport News, Virginia in 1934 on a trumped-up 'inciting to riot' charge amid the crackdown on the party's campaign and organizing.",
            'state' => 'Virginia', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'affiliation' => ['Communist Party'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Arrested on an 'inciting to riot' charge as a Communist Senate candidate.",
                'convicted' => 'Arrested, 1934',
                'sentence' => 'Held.',
                'institution_city' => 'Newport News', 'institution_state' => 'Virginia',
            ]],
        ], ['arrest_date' => [1934, null, null]]);

        // ── INDIANA FARM ANTI-FORECLOSURE ───────────────────────────────
        $mk([
            'name' => 'Viola Tiala', 'first_name' => 'Viola', 'last_name' => 'Tiala',
            'description' => "Viola Tiala was a United Farmers' League member arrested on January 20, 1934 alongside her husband, the league's national secretary Alfred Tiala, during anti-foreclosure resistance at a forced sale in Warsaw (Kosciusko County), Indiana. She was charged with resisting an officer and held under the same $30,000 bail, with the ILD organizing her defense.",
            'state' => 'Indiana', 'gender' => 'Female',
            'ideologies' => ['Farm organizing'],
            'affiliation' => ["United Farmers' League"],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with resisting an officer in anti-foreclosure resistance.',
                'convicted' => 'Arrested, 1934',
                'sentence' => 'Held under $30,000 bail.',
                'institution_city' => 'Warsaw', 'institution_state' => 'Indiana',
            ]],
        ], ['arrest_date' => [1934, 1, 20]]);

        // ── SAN FRANCISCO GENERAL-STRIKE VIGILANTE RAIDS (July 1934) ────
        $mk([
            'name' => 'Tillie Olsen', 'first_name' => 'Tillie', 'last_name' => 'Olsen',
            'description' => "Tillie Olsen (1912–2007), then known as Tillie Lerner and later famous as the author of Tell Me a Riddle, was a young Communist writer arrested as a 'vagrant' in a San Francisco apartment during the anti-red police drive that accompanied the July 1934 West Coast maritime and general strike. Active in the Young Communist League and the Warehouse Union, she was jailed in the roundup of radicals.",
            'state' => 'California', 'gender' => 'Female',
            'ideologies' => ['Communism'],
            'affiliation' => ['Young Communist League'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Arrested as a 'vagrant' in the July 1934 San Francisco anti-red roundup.",
                'convicted' => 'Arrested, 1934',
                'sentence' => 'Jailed; released.',
                'institution_city' => 'San Francisco', 'institution_state' => 'California',
            ]],
        ], ['arrest_date' => [1934, 7, null]]);

        $mk([
            'name' => 'Joe Wilson', 'first_name' => 'Joe', 'last_name' => 'Wilson',
            'description' => "Joe Wilson was the San Francisco district secretary of the International Labor Defense, arrested during the 1934 waterfront and general strike on a charge of conspiracy to obstruct justice, amid the July 1934 vigilante and police raids that swept up Communist and ILD organizers across California.",
            'state' => 'California', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'affiliation' => ['International Labor Defense'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested on a charge of conspiracy to obstruct justice during the general strike.',
                'convicted' => 'Arrested, 1934',
                'sentence' => 'Held on high bail; released on his own recognizance.',
                'institution_city' => 'San Francisco', 'institution_state' => 'California',
            ]],
        ], ['arrest_date' => [1934, 7, null]]);

        $mk([
            'name' => 'Sam Gardner', 'first_name' => 'Sam', 'last_name' => 'Gardner',
            'description' => "Sam Gardner was seized in the July 1934 vigilante raid on the International Labor Defense Workers' School in San Francisco and jailed on a vagrancy charge — the standard device used to hold organizers rounded up in the general-strike raids.",
            'state' => 'California', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'affiliation' => ['International Labor Defense'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Seized in the vigilante raid on the ILD Workers' School; jailed on a vagrancy charge.",
                'convicted' => 'Arrested, 1934',
                'sentence' => 'Jailed on a vagrancy charge.',
                'institution_city' => 'San Francisco', 'institution_state' => 'California',
            ]],
        ], ['arrest_date' => [1934, 7, null]]);

        $mk([
            'name' => 'George Levison', 'first_name' => 'George', 'last_name' => 'Levison',
            'description' => "George Levison was beaten and arrested on a vagrancy charge in the July 1934 vigilante raid on the International Labor Defense Workers' School in San Francisco, one of scores detained in the coordinated raids that followed the general strike.",
            'state' => 'California', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'affiliation' => ['International Labor Defense'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Beaten and arrested in the raid on the ILD Workers' School.",
                'convicted' => 'Arrested, 1934',
                'sentence' => 'Jailed on a vagrancy charge.',
                'institution_city' => 'San Francisco', 'institution_state' => 'California',
            ]],
        ], ['arrest_date' => [1934, 7, null]]);

        $mk([
            'name' => 'Joe Pettis', 'first_name' => 'Joe', 'last_name' => 'Pettis',
            'description' => "Joe Pettis was the secretary of the Marine Workers Industrial Union in San Francisco, arrested when the union's hall was raided in July 1934 during the vigilante and police attacks on maritime and Communist organizations after the general strike.",
            'state' => 'California', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Marine Workers Industrial Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Arrested when the Marine Workers Industrial Union hall was raided.",
                'convicted' => 'Arrested, 1934',
                'sentence' => 'Held.',
                'institution_city' => 'San Francisco', 'institution_state' => 'California',
            ]],
        ], ['arrest_date' => [1934, 7, null]]);

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

        $this->info("\nDone. Processed {$added} of ".count($people)." New Masses 1934 prisoner(s).");

        return self::SUCCESS;
    }
}
