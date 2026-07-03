<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Batch 12 of the ILD Labor Defender mining, covering the whole 1932 volume
 * (Vol. VIII, Jan–Nov). 1932 was the pit of the Depression: the Scottsboro
 * appeals, the Ford Hunger March, the Bonus Army, the Kentucky and
 * Pennsylvania coal wars, the Tampa tobacco case, and the arrest of Angelo
 * Herndon in Atlanta.
 *
 * This adds the clearly-attested NEW prisoners of 1932. Marquee cases:
 *  - Angelo Herndon (the Atlanta insurrection case);
 *  - the Tampa, Florida tobacco-strike defendants;
 *  - the Pineville / Bell County, Kentucky criminal-syndicalism raid and the
 *    Straight Creek strike (Aunt Molly Jackson's circle);
 *  - the Bonus Army arrests and the Ford Hunger March;
 *  - the Blawnox Workhouse and Western Pennsylvania coal/sedition roster;
 *  - the Philadelphia mass arrests, the Logan Circle (D.C.) case, the Los
 *    Angeles "Free Tom Mooney" Olympic protest, and the 1932 deportation drive.
 *
 * Cases already in the database are skipped: Scottsboro, Mooney/Billings,
 * McNamara/Schmidt, Imperial Valley, Centralia, Camp Hill, Edith Berkman,
 * Ben Boloff, Euel Lee / Orphan Jones, Willie Peterson, Paul Kassay, the
 * NY "Nessin" delegation, and the batch-11 Pennsylvania coal names (Getto,
 * Leo Thompson, Sherwood, the Rasefskys, Devine, Murdock, the Harlan roster).
 * OCR-garbled surname-only fragments from the January winter-relief roster
 * were omitted rather than added as uncertain data.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddLaborDefender1932Cases extends Command
{
    protected $signature = 'prisoners:add-labor-defender-1932';

    protected $description = 'Add the 1932 Labor Defender class-war prisoners (Angelo Herndon, Tampa case, Kentucky criminal syndicalism, Bonus Army, Blawnox coal roster, deportation drive)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── ANGELO HERNDON ────────────────────────────────────────────────
        $mk([
            'name' => 'Angelo Herndon', 'first_name' => 'Angelo', 'last_name' => 'Herndon',
            'description' => "Angelo Herndon was a nineteen-year-old Black Communist organizer arrested in Atlanta in July 1932 after leading an interracial demonstration of the unemployed, and charged under a Georgia slave-insurrection statute of 1861 with \"attempting to incite insurrection\" — a capital offense — on the strength of the radical literature found in his room. Convicted and sentenced to eighteen to twenty years on the chain gang, his case became a landmark ILD defense; in Herndon v. Lowry (1937) the U.S. Supreme Court struck down the Georgia statute as an unconstitutional restraint on free speech and assembly, and he went free.",
            'state' => 'Georgia', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['Communist Party USA'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with "attempting to incite insurrection" under Georgia\'s 1861 statute for leading an interracial unemployment demonstration and possessing radical literature.',
                'convicted' => 'Convicted, 1933; conviction struck down in Herndon v. Lowry (1937)',
                'sentence' => 'Eighteen to twenty years on the chain gang; freed when the Supreme Court voided the statute.',
                'institution_name' => 'Fulton County Jail',
                'institution_city' => 'Atlanta', 'institution_state' => 'Georgia',
            ]],
        ], ['arrest_date' => [1932, 7, 11]]);

        // ── TAMPA, FLORIDA TOBACCO-STRIKE CASE ────────────────────────────
        $tampaBase = "On 7 November 1931 police and vigilantes broke up a Tampa, Florida meeting marking the Russian Revolution; some two dozen cigar- and tobacco-workers were arrested and prosecuted on a framed charge of shooting a policeman. Fourteen were sent to the state penitentiary at Raiford — a total of fifty-seven years — many to the sweat-boxes and Everglades road gangs, in a case the ILD fought as a Southern labor frame-up.";
        $tampa = [
            ['Jim Nino', 'Jim', 'Nino', 'Male', "a twenty-year-old leader of the Tampa tobacco workers, sentenced to ten years for strike activity", 'Tampa County Jail', 'Tampa'],
            ['Al McBride', 'Al', 'McBride', 'Male', "a Marine Workers Industrial Union organizer and World War veteran held over seventy-five days without trial, beaten so badly at his arrest that several ribs were broken", 'Hillsborough County Jail', 'Tampa'],
            ['Frank Guido', 'Frank', 'Guido', 'Male', "a Tampa-born Young Pioneer leader beaten by police and Legionnaires, who put a noose around his neck in a swamp", 'Hillsborough County Jail', 'Tampa'],
            ['Comas', 'Comas', '', 'Male', "a worker arrested with Frank Guido at the platform and beaten though never charged", 'Hillsborough County Jail', 'Tampa'],
            ['McDonald', 'McDonald', '', 'Male', "one of the Tampa defendants serving ten years at the Raiford penitentiary, worked to collapse on the road gang", 'Raiford Penitentiary', 'Raiford'],
            ['Ismael Cruz', 'Ismael', 'Cruz', 'Male', "a Tampa defendant sentenced to a year and sent to the Indiantown road camp in the Everglades", 'State Road Camp', 'Indiantown'],
            ['Angel Cabrera', 'Angel', 'Cabrera', 'Male', "a Tampa defendant sent to the Indiantown road camp, his hands bleeding from grading with a spade", 'State Road Camp', 'Indiantown'],
            ['Marko Lopez', 'Marko', 'Lopez', 'Male', "a Tampa defendant sentenced to a year at the Raiford penitentiary for the 7 November celebration", 'Raiford Penitentiary', 'Raiford'],
            ['Frances Romero', 'Frances', 'Romero', 'Female', "a fifty-four-year-old mother of six held in the women's quarters at Raiford", 'Raiford Penitentiary', 'Raiford'],
            ['Caroline Vasquez', 'Caroline', 'Vasquez', 'Female', "a tobacco worker's daughter sentenced to a year for protesting the arrest of her fourteen-year-old brother", 'Raiford Penitentiary', 'Raiford'],
            ['Francisca Vasquez', 'Francisca', 'Vasquez', 'Female', "Caroline's mother, also sentenced to a year in the Tampa case", 'Raiford Penitentiary', 'Raiford'],
        ];
        foreach ($tampa as [$name, $first, $last, $gender, $who, $inst, $city]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last ?: $first,
                'description' => "{$name} was {$who} in the Tampa tobacco-strike case of 1931–32. ".$tampaBase,
                'state' => 'Florida', 'gender' => $gender,
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['Trade Union Unity League'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Framed on a charge of shooting a policeman after the 7 November 1931 Russian Revolution celebration in Tampa.',
                    'convicted' => 'Convicted in the Tampa case, 1932',
                    'sentence' => 'Sent to the Florida penitentiary / road camps.',
                    'institution_name' => $inst,
                    'institution_city' => $city, 'institution_state' => 'Florida',
                ]],
            ], ['arrest_date' => [1931, 11, 7]]);
        }

        // ── KENTUCKY — PINEVILLE / BELL COUNTY CRIMINAL SYNDICALISM ───────
        $pinevilleBase = "On 4 January 1932 Kentucky officers raided the National Miners' Union Southern District headquarters at Pineville, in Bell County, and jailed the staff on criminal-syndicalism charges during the Harlan–Bell coal strike; each faced twenty-one years. Held in the Pineville jail and later moved to Harlan, they were freed after a national ILD campaign.";
        $pineville = [
            ['Vern Smith', 'Vern', 'Smith', 'Male', "a Daily Worker correspondent"],
            ['Vincent Kemenovich', 'Vincent', 'Kemenovich', 'Male', "a National Miners' Union organizer"],
            ['John Harvey', 'John', 'Harvey', 'Male', "a National Miners' Union organizer"],
            ['Ann Barton', 'Ann', 'Barton', 'Female', "a writer arrested in the early days of the strike"],
            ['Norma Martin', 'Norma', 'Martin', 'Female', "a Workers International Relief director"],
            ['Julia Parker', 'Julia', 'Parker', 'Female', "a National Miners' Union organizer who spent about three months in the Pineville jail"],
            ['Margaret Fontaine', 'Margaret', 'Fontaine', 'Female', "arrested near the office though no evidence was shown against her"],
            ['Dorothy Ross Weber', 'Dorothy Ross', 'Weber', 'Female', "an ILD organizer arrested in her room"],
            ['Clarina Michaelson', 'Clarina', 'Michaelson', 'Female', "an organizer who contracted pneumonia in the Pineville jail"],
            ['Harry Chandler', 'Harry', 'Chandler', 'Male', "a young National Miners' Union organizer"],
            ['June Croll', 'June', 'Croll', 'Female', "arrested and held in the Pineville jail"],
            ['Harold Hickerson', 'Harold', 'Hickerson', 'Male', "arrested and held in the Pineville jail"],
            ['Doris Parks', 'Doris', 'Parks', 'Female', "jailed early in the strike and moved to Harlan County"],
        ];
        foreach ($pineville as [$name, $first, $last, $gender, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who}, jailed in the 4 January 1932 raid on the National Miners' Union headquarters at Pineville, Kentucky and charged with criminal syndicalism. ".$pinevilleBase,
                'state' => 'Kentucky', 'gender' => $gender,
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['National Miners Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with criminal syndicalism in the raid on the National Miners\' Union headquarters at Pineville, Kentucky.',
                    'convicted' => 'Held on criminal-syndicalism charges, 1932',
                    'sentence' => 'Held in the Pineville and Harlan jails; freed on ILD defense.',
                    'institution_name' => 'Bell County Jail',
                    'institution_city' => 'Pineville', 'institution_state' => 'Kentucky',
                ]],
            ], ['arrest_date' => [1932, 1, 4]]);
        }

        // ── KENTUCKY — STRAIGHT CREEK / AUNT MOLLY JACKSON'S CIRCLE ───────
        $mk([
            'name' => 'Aunt Molly Jackson', 'first_name' => 'Molly', 'last_name' => 'Jackson', 'aka' => 'Aunt Molly Jackson',
            'description' => "Aunt Molly Jackson was a midwife, balladeer and National Miners' Union activist of the Straight Creek section of Bell County, Kentucky, whose songs of the coal wars made her a famous voice of the Harlan miners. Jailed during the 1931–32 Kentucky coal strike, she and her family were driven out of the state; her brothers were also arrested and hunted in the criminal-syndicalism drive.",
            'state' => 'Kentucky', 'gender' => 'Female',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['National Miners Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed for National Miners\' Union activity during the Kentucky coal strike.',
                'convicted' => 'Jailed in the Kentucky coal war, 1931–32',
                'sentence' => 'Jailed and driven out of Kentucky.',
                'institution_state' => 'Kentucky',
            ]],
        ], ['arrest_date' => [1932, null, null]]);
        foreach ([
            ['W. M. Garland', 'W. M.', 'Garland', "Aunt Molly Jackson's brother, jailed at Pineville with seven other miners on a criminal-syndicalism charge after demanding a checkweighman, held under $6,000 bond"],
            ['Ebb Payne', 'Ebb', 'Payne', "a Straight Creek miner jailed at Pineville in the criminal-syndicalism drive, leaving a wife and six children"],
            ['J. C. Garland', 'J. C.', 'Garland', "the youngest Garland brother, who fled the deputies to avoid a criminal-syndicalism arrest"],
            ['Green Lawson', 'Green', 'Lawson', "a National Miners' Union member jailed at Barboursville, Kentucky on a concealed-weapons charge the morning organizer Harry Simms was shot"],
        ] as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} during the 1931–32 Kentucky coal war.",
                'state' => 'Kentucky', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['National Miners Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed on criminal-syndicalism or weapons charges in the Kentucky coal war.',
                    'convicted' => 'Jailed in the Kentucky coal war, 1932',
                    'sentence' => 'Held during the Kentucky coal strike.',
                    'institution_state' => 'Kentucky',
                ]],
            ], ['arrest_date' => [1932, null, null]]);
        }

        // ── HARLAN / BELL COUNTY MURDER DEFENDANTS (new names) ────────────
        foreach ([
            ['Virgil Hutton', 'Virgil', 'Hutton', "his head split open in the arrest"],
            ['Kike Hall', 'Kike', 'Hall', "a National Miners' Union member"],
            ['Leonard Farmer', 'Leonard', 'Farmer', "a National Miners' Union member"],
        ] as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was an active National Miners' Union member, {$who}, arrested for the Christmas Day 1931 shooting of Deputy Sheriff Virgil Sizemore while distributing leaflets calling a general strike, and charged with murder in the Harlan–Bell County, Kentucky coal war.",
                'state' => 'Kentucky', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['National Miners Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with murder in the death of a deputy sheriff during the Harlan coal war.',
                    'convicted' => 'Held on murder charges, 1932',
                    'sentence' => 'Held facing murder charges in the Kentucky coal war.',
                    'institution_state' => 'Kentucky',
                ]],
            ], ['arrest_date' => [1931, 12, 25]]);
        }
        foreach ([
            ['Cecil Shadrick', 'Cecil', 'Shadrick', 'Harlan County Jail'],
            ['James Oliver', 'James', 'Oliver', 'Clark County Jail'],
            ['Silas Serge', 'Silas', 'Serge', 'Harlan County Jail'],
            ['William Hightower', 'William', 'Hightower', 'Harlan County Jail'],
        ] as [$name, $first, $last, $inst]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a Harlan-area coal miner indicted for murder in the frame-ups that followed the 5 May 1931 Battle of Evarts and the Kentucky coal war, held awaiting trial in the {$inst}.",
                'state' => 'Kentucky', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['National Miners Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Indicted for murder in the Harlan County coal war.',
                    'convicted' => 'Held awaiting trial, 1931–32',
                    'sentence' => 'Held in the Kentucky jails awaiting trial.',
                    'institution_name' => $inst,
                    'institution_state' => 'Kentucky',
                ]],
            ], ['arrest_date' => [1931, null, null]]);
        }

        // ── BONUS ARMY ────────────────────────────────────────────────────
        foreach ([
            ['John Pace', 'John', 'Pace', "the chairman of the rank-and-file committee of the Bonus Marchers"],
            ['Eicker', 'Eicker', '', "the secretary of the rank-and-file committee of the Bonus Marchers"],
            ['Bonus March Johnson', 'Johnson', '', "a rank-and-file leader of the Bonus Marchers"],
        ] as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last ?: $first,
                'description' => "{$name} was {$who} — the some 20,000 World War veterans who camped in Washington in the summer of 1932 to demand early payment of their service bonus — arrested while picketing President Hoover and the White House, and freed after two days under mass pressure. The camps were burned out by the Army under MacArthur on 28 July 1932.",
                'state' => 'District of Columbia', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['Workers Ex-Servicemen\'s League'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Arrested picketing the White House as a leader of the Bonus Army.',
                    'convicted' => 'Held two days, 1932',
                    'sentence' => 'Arrested and freed under mass pressure.',
                    'institution_city' => 'Washington', 'institution_state' => 'District of Columbia',
                ]],
            ], ['arrest_date' => [1932, 7, null]]);
        }

        // ── FORD HUNGER MARCH ─────────────────────────────────────────────
        $mk([
            'name' => 'Mary Gossman', 'first_name' => 'Mary', 'last_name' => 'Gossman',
            'description' => "Mary Gossman was a young worker arrested at the Ford Hunger March in Dearborn, Michigan on 7 March 1932 — \"Bloody Monday,\" when police and Ford security killed four marchers. Interrogated at the Detroit detective bureau and held incommunicado for three days, she was released on a writ of habeas corpus and subpoenaed before the grand jury.",
            'state' => 'Michigan', 'gender' => 'Female',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['Unemployed Councils'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested at the Ford Hunger March, Dearborn, Michigan.',
                'convicted' => 'Held three days, 1932',
                'sentence' => 'Held incommunicado; released on habeas corpus.',
                'institution_city' => 'Detroit', 'institution_state' => 'Michigan',
            ]],
        ], ['arrest_date' => [1932, 3, 7]]);

        // ── BLAWNOX WORKHOUSE / WESTERN PA COAL ROSTER (new names) ────────
        $blawnoxBase = "The Blawnox (Allegheny County) Workhouse in Pennsylvania held National Miners' Union strikers convicted after the 40,000-strong 1931 Pittsburgh-district coal strike, serving up to six years; the ILD's roster of these class-war prisoners ran through the Labor Defender.";
        $blawnox = [
            ['Theresa Presillac', 'Theresa', 'Presillac', 'Female', "a militant picket-line leader at Wildwood serving two years"],
            ['Harry Boswell', 'Harry', 'Boswell', 'Male', "a twenty-eight-year-old striker shot and wounded after a tear-gas attack in the Westland march, serving a year and a half"],
            ['Tom Myerscough', 'Tom', 'Myerscough', 'Male', "a National Miners' Union strike leader serving two years"],
            ['Bob Young', 'Bob', 'Young', 'Male', "a miner serving two years who distributed the Labor Defender among the class-war prisoners"],
            ['Will MacQueen', 'Will', 'MacQueen', 'Male', "a Black miner serving a year"],
            ['Pete Jugrine', 'Pete', 'Jugrine', 'Male', "a Black miner serving a year"],
            ['Tom Boich', 'Tom', 'Boich', 'Male', "a miner serving twenty months, gravely ill with stomach trouble"],
            ['Julius Hollis', 'Julius', 'Hollis', 'Male', "a miner serving twenty months"],
            ['Andy Skarupa', 'Andy', 'Skarupa', 'Male', "a Westland-district striker held in the coal roundup"],
        ];
        foreach ($blawnox as [$name, $first, $last, $gender, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} at the Blawnox Workhouse, one of the National Miners' Union strikers jailed after the 1931 Pittsburgh-district coal strike. ".$blawnoxBase,
                'state' => 'Pennsylvania', 'gender' => $gender,
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['National Miners Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed for National Miners\' Union activity in the 1931 Pennsylvania coal strike.',
                    'convicted' => 'Convicted, 1931',
                    'sentence' => 'Held at the Blawnox Workhouse, Allegheny County.',
                    'institution_name' => 'Allegheny County Workhouse',
                    'institution_city' => 'Blawnox', 'institution_state' => 'Pennsylvania',
                ]],
            ], ['arrest_date' => [1931, null, null]]);
        }

        // ── WESTERN PA FLYNN-SEDITION / WINTER-AID ROSTER (clearly named) ──
        $paSedition = [
            ['Sam Betti', 'Sam', 'Betti', 'Blawnox Prison', 'Blawnox'],
            ['Anton Zilich', 'Anton', 'Zilich', 'Blawnox Prison', 'Blawnox'],
            ['John White', 'John', 'White', 'Blawnox Prison', 'Blawnox'],
            ['William Diehl', 'William', 'Diehl', 'Blawnox Prison', 'Blawnox'],
            ['Paul Babish', 'Paul', 'Babish', 'Allegheny County Workhouse', 'Blawnox'],
            ['Steve Vargo', 'Steve', 'Vargo', 'Allegheny County Workhouse', 'Blawnox'],
            ['John Vargo', 'John', 'Vargo', 'Allegheny County Workhouse', 'Blawnox'],
            ['Mike Vargo', 'Mike', 'Vargo', 'Allegheny County Workhouse', 'Blawnox'],
            ['Pete Lesko', 'Pete', 'Lesko', 'Allegheny County Workhouse', 'Blawnox'],
            ['Steve Perlich', 'Steve', 'Perlich', 'Allegheny County Jail', 'Pittsburgh'],
            ['Philip Giambattista', 'Philip', 'Giambattista', 'Allegheny County Jail', 'Pittsburgh'],
            ['Tom Borich', 'Tom', 'Borich', 'Washington County Jail', 'Washington'],
            ['Louis Fazio', 'Louis', 'Fazio', 'Washington County Jail', 'Washington'],
            ['Henry Stark', 'Henry', 'Stark', 'Washington County Jail', 'Washington'],
            ['Julius Soich', 'Julius', 'Soich', 'Wildwood', 'Wildwood'],
            ['Helen Prescelic', 'Helen', 'Prescelic', 'Wildwood', 'Wildwood'],
        ];
        foreach ($paSedition as [$name, $first, $last, $inst, $city]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was listed on the ILD's 1932 winter-relief roster of Western Pennsylvania class-war prisoners — coal strikers and sedition-law defendants jailed in the Pittsburgh-district struggles — confined at the {$inst} in {$city}, Pennsylvania.",
                'state' => 'Pennsylvania', 'gender' => $first === 'Helen' ? 'Female' : 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['National Miners Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed in the Western Pennsylvania coal and sedition-law cases.',
                    'convicted' => 'Imprisoned as of 1932',
                    'sentence' => "Held at the {$inst}.",
                    'institution_name' => $inst,
                    'institution_city' => $city, 'institution_state' => 'Pennsylvania',
                ]],
            ], []);
        }

        // ── PHILADELPHIA MASS ARRESTS ─────────────────────────────────────
        $mk([
            'name' => 'Willie Brown', 'first_name' => 'Willie', 'last_name' => 'Brown',
            'description' => "Willie Brown was a sixteen-year-old Black youth of Philadelphia sentenced to the electric chair on 13 May 1932 by Judge McDevitt for the murder of a white girl, Dorothy Lutz — a conviction the ILD fought as a frame-up built on a beaten-out confession with no evidence.",
            'state' => 'Pennsylvania', 'gender' => 'Male', 'race' => 'Black',
            'era' => '1930s', 'in_custody' => false, 'released' => false,
            'cases' => [[
                'charges' => 'Convicted of murder on a coerced confession at Philadelphia.',
                'convicted' => 'Sentenced to death, 13 May 1932',
                'sentence' => 'Death; the ILD fought the case as a frame-up.',
                'institution_city' => 'Philadelphia', 'institution_state' => 'Pennsylvania',
            ]],
        ], ['arrest_date' => [1932, null, null]]);
        $mk([
            'name' => 'Bill Lawrence', 'first_name' => 'Bill', 'last_name' => 'Lawrence',
            'description' => "Bill Lawrence was a Philadelphia Communist organizer sentenced to four years in 1932 in the wave of mass arrests of the unemployed movement in the city.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed in the Philadelphia unemployment-movement arrests.',
                'convicted' => 'Convicted, 1932',
                'sentence' => 'Four years.',
                'institution_city' => 'Philadelphia', 'institution_state' => 'Pennsylvania',
            ]],
        ], ['incarceration_date' => [1932, null, null]]);
        $mk([
            'name' => 'Mary Williams', 'first_name' => 'Mary', 'last_name' => 'Williams',
            'description' => "Mary Williams was an ILD member in Philadelphia held on $500 bail in 1932 on a charge of \"seditious remarks — breach of the peace\" after an alleged scuffle with a stool-pigeon.",
            'state' => 'Pennsylvania', 'gender' => 'Female',
            'ideologies' => ['Communism'],
            'affiliation' => ['International Labor Defense'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with "seditious remarks — breach of the peace" at Philadelphia.',
                'convicted' => 'Held on $500 bail, 1932',
                'sentence' => 'Held on the sedition charge.',
                'institution_city' => 'Philadelphia', 'institution_state' => 'Pennsylvania',
            ]],
        ], ['arrest_date' => [1932, null, null]]);
        foreach ([
            ['Charles Starocarski', 'Charles', 'Starocarski', 'a Manayunk strike meeting', '10 days for "disorderly conduct"'],
            ['Floyd Tyson', 'Floyd', 'Tyson', 'a Manayunk strike meeting', '10 days for "disorderly conduct"'],
            ['Charles Skowronski', 'Charles', 'Skowronski', 'a Manayunk strike meeting', '10 days for "disorderly conduct"'],
            ['Violet Lynn', 'Violet', 'Lynn', 'a Manayunk strike meeting', '10 days for "disorderly conduct"'],
            ['Irving Schwartz', 'Irving', 'Schwartz', 'the furriers\' convention delegation', '"inciting to riot," held on $1,500 bail'],
            ['Sol Wollin', 'Sol', 'Wollin', 'the furriers\' convention delegation', '"inciting to riot," held on $1,500 bail'],
            ['Anna Bogansky', 'Anna', 'Bogansky', 'the furriers\' convention delegation', '"inciting to riot," held on $1,500 bail'],
            ['Eli Sitkin', 'Eli', 'Sitkin', 'the furriers\' convention delegation', '"inciting to riot," held on $1,500 bail'],
        ] as [$name, $first, $last, $ctx, $charge]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was arrested at {$ctx} in Philadelphia in May 1932 and charged with {$charge}.",
                'state' => 'Pennsylvania', 'gender' => in_array($first, ['Violet', 'Anna']) ? 'Female' : 'Male',
                'ideologies' => ['Communism', 'Labor organizing'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => "Arrested at {$ctx} in Philadelphia.",
                    'convicted' => 'Held/convicted, 1932',
                    'sentence' => ucfirst($charge).'.',
                    'institution_city' => 'Philadelphia', 'institution_state' => 'Pennsylvania',
                ]],
            ], ['arrest_date' => [1932, 5, null]]);
        }

        // ── LOGAN CIRCLE CASE, WASHINGTON D.C. ────────────────────────────
        foreach ([
            ['Bob Jackson', 'Bob', 'Jackson'],
            ['Joseph Jackson', 'Joseph', 'Jackson'],
            ['Ralph Holmes', 'Ralph', 'Holmes'],
            ['Irving Murray', 'Irving', 'Murray'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of a group of Black youths indicted for the first-degree murder of Park Policeman Milo Kennedy at Logan Circle, Washington, D.C. in August 1932 — a case, tied to the Bonus March terror, in which the ILD argued the young men had acted in self-defense against a policeman attacking them for refusing to be jim-crowed. Three were convicted and faced death.",
                'state' => 'District of Columbia', 'gender' => 'Male', 'race' => 'Black',
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Indicted for the murder of a park policeman at Logan Circle, Washington, D.C.',
                    'convicted' => 'Indicted / convicted of first-degree murder, 1932',
                    'sentence' => 'Held facing the death penalty.',
                    'institution_city' => 'Washington', 'institution_state' => 'District of Columbia',
                ]],
            ], ['arrest_date' => [1932, 8, 7]]);
        }

        // ── LOS ANGELES — "FREE TOM MOONEY" OLYMPIC PROTEST & LA PRISONERS ─
        $laMooney = [
            ['Meyer Baylin', 'Meyer', 'Baylin', 'Male', "who was dragged to court with a 104-degree fever by the police Red Squad"],
            ['Ethel Dell', 'Ethel', 'Dell', 'Female', "who drew an extra fifty days for \"contempt of court\""],
            ['Ben Schapiro', 'Ben', 'Schapiro', 'Male', "one of the six young demonstrators"],
            ['Ann Davis', 'Ann', 'Davis', 'Female', "one of the six young demonstrators"],
            ['Ernest Palmer', 'Ernest', 'Palmer', 'Male', "one of the six young demonstrators"],
        ];
        foreach ($laMooney as [$name, $first, $last, $gender, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of six young workers, {$who}, who leapt the railing at the closing of the 1932 Los Angeles Olympic Games on 14 August to demand freedom for Tom Mooney; convicted of \"disturbing the peace,\" each was given the maximum 270 days in jail.",
                'state' => 'California', 'gender' => $gender,
                'ideologies' => ['Communism'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Convicted of disturbing the peace for the "Free Tom Mooney" protest at the Los Angeles Olympics.',
                    'convicted' => 'Convicted, August 1932',
                    'sentence' => '270 days in jail.',
                    'institution_city' => 'Los Angeles', 'institution_state' => 'California',
                ]],
            ], ['arrest_date' => [1932, 8, 14]]);
        }
        foreach ([
            ['N. Yamashita', 'N.', 'Yamashita', 'Los Angeles County Jail'],
            ['Buchan Singh', 'Buchan', 'Singh', 'Los Angeles County Jail'],
            ['Y. Fukunaga', 'Y.', 'Fukunaga', 'Los Angeles County Jail'],
            ['S. Sakiyama', 'S.', 'Sakiyama', 'Los Angeles County Jail'],
            ['E. Yamaguchi', 'E.', 'Yamaguchi', 'Lincoln Heights Jail'],
        ] as [$name, $first, $last, $inst]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was named among the political prisoners jailed in Los Angeles in 1932 who sent revolutionary greetings to the International Red Aid, held in the {$inst}.",
                'state' => 'California', 'gender' => 'Male',
                'ideologies' => ['Communism'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Held as a political prisoner in Los Angeles.',
                    'convicted' => 'Imprisoned, 1932',
                    'sentence' => "Held in the {$inst}.",
                    'institution_name' => $inst,
                    'institution_city' => 'Los Angeles', 'institution_state' => 'California',
                ]],
            ], []);
        }

        // ── CHICAGO JAPANESE CONSULATE "RIOTING" CASE ────────────────────
        $mk([
            'name' => 'Stephen Chuck', 'first_name' => 'Stephen', 'last_name' => 'Chuck',
            'description' => "Stephen Chuck was one of thirteen workers charged with rioting after police attacked an anti-imperialist demonstration at the Japanese Consulate in Chicago on 12 March 1932; defending himself against police clubs, he shot and wounded three officers. All thirteen were acquitted.",
            'state' => 'Illinois', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Anti-imperialism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with rioting at the anti-Japan demonstration at the Chicago Japanese Consulate.',
                'convicted' => 'Acquitted, 1932',
                'sentence' => 'Held for trial; acquitted.',
                'institution_city' => 'Chicago', 'institution_state' => 'Illinois',
            ]],
        ], ['arrest_date' => [1932, 3, 12]]);

        // ── IRISH WORKERS' CLUB EVICTION FRAME-UP (Bronx) ─────────────────
        foreach ([
            ['John Mullally', 'John', 'Mullally'],
            ['Hugh McKiernan', 'Hugh', 'McKiernan'],
            ['John Rooney', 'John', 'Rooney'],
            ['Martin Moriarty', 'Martin', 'Moriarty'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of four members of the Irish Workers' Club in the Bronx, New York arrested defending against an eviction in 1932; the disorderly-conduct charge was raised to felonious assault and they were secretly indicted by the Bronx grand jury, held on $1,000 bail each.",
                'state' => 'New York', 'gender' => 'Male',
                'ideologies' => ['Communism'],
                'affiliation' => ['Irish Workers Club'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Indicted for felonious assault for resisting an eviction in the Bronx.',
                    'convicted' => 'Indicted, 1932',
                    'sentence' => 'Held on $1,000 bail.',
                    'institution_city' => 'New York', 'institution_state' => 'New York',
                ]],
            ], ['arrest_date' => [1932, null, null]]);
        }

        // ── DEATH-ROW / LEGAL-LYNCHING FRAME-UPS ─────────────────────────
        $deathRow = [
            ['George Moore', 'George', 'Moore', 'North Carolina', "sentenced to death at Winston-Salem, North Carolina for stealing a pair of shoes worth less than a dollar; the ILD forced the governor to commute the sentence to life"],
            ['Jess Hollins', 'Jess', 'Hollins', 'Oklahoma', "a Black man railroaded toward the electric chair on a rape charge in Oklahoma; the ILD won a stay and a new trial"],
            ['Barney Lee Ross', 'Barney Lee', 'Ross', 'Oklahoma', "a Black worker framed and executed in Oklahoma with no mass defense mobilized"],
            ['Isaac Mims', 'Isaac', 'Mims', 'Alabama', "a Black youth electrocuted in the same Kilby Prison death house as the Scottsboro boys for stealing a half-dollar"],
            ['Percy Irvin', 'Percy', 'Irvin', 'Alabama', "a Black youth electrocuted in the Kilby Prison death house for stealing a half-dollar"],
            ['Sam Brown', 'Sam', 'Brown', 'New York', "sentenced to six months for taking part in a demonstration at a New York relief station"],
            ['Jimmy Ford', 'Jimmy', 'Ford', 'New York', "sentenced to a year in a New York reform school for leading Black children to a jim-crow swimming pool"],
        ];
        foreach ($deathRow as [$name, $first, $last, $state, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} — one of the Depression-era Southern and Northern frame-up and legal-lynching cases the ILD publicized in 1932.",
                'state' => $state, 'gender' => 'Male', 'race' => 'Black',
                'era' => '1930s', 'in_custody' => false, 'released' => in_array($first, ['Barney Lee', 'Isaac', 'Percy']) ? false : true,
                'cases' => [[
                    'charges' => 'Framed or harshly sentenced in a 1932 ILD legal-lynching case.',
                    'convicted' => 'Convicted, 1931–32',
                    'sentence' => 'Death or imprisonment; taken up by the ILD.',
                    'institution_state' => $state,
                ]],
            ], []);
        }

        // ── COAL / STRIKE FRAME-UPS ──────────────────────────────────────
        $mk([
            'name' => 'Osip Orloff', 'first_name' => 'Osip', 'last_name' => 'Orloff',
            'description' => "Osip Orloff was a Russian-born coal miner arrested with a comrade near Farmington, West Virginia in July 1932 during a mine-organizing drive; the company \"found\" a planted knife and he was charged with murder after a fight, facing life, held in the Morgantown county jail.",
            'state' => 'West Virginia', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['National Miners Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with murder on a planted-knife frame-up during a West Virginia mine strike.',
                'convicted' => 'Held facing life, 1932',
                'sentence' => 'Held in the Morgantown county jail.',
                'institution_city' => 'Morgantown', 'institution_state' => 'West Virginia',
            ]],
        ], ['arrest_date' => [1932, 7, 25]]);
        $mk([
            'name' => 'K. Y. Hendrix', 'first_name' => 'K. Y.', 'last_name' => 'Hendrix',
            'description' => "K. Y. Hendrix was a class-war prisoner writing to the ILD from behind bars in North Carolina in 1932, serving five to seven years.",
            'state' => 'North Carolina', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Held as a class-war prisoner in North Carolina.',
                'convicted' => 'Imprisoned, 1932',
                'sentence' => 'Five to seven years.',
                'institution_state' => 'North Carolina',
            ]],
        ], []);
        $mk([
            'name' => 'Fred Powers', 'first_name' => 'Fred', 'last_name' => 'Powers',
            'description' => "Fred Powers was a political prisoner in the Hartford County Jail, Connecticut in 1932, threatened with solitary confinement for signing, with nine other prisoners, a protest against the \"musty and sour bread.\"",
            'state' => 'Connecticut', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Held as a political prisoner at the Hartford County Jail.',
                'convicted' => 'Imprisoned, 1932',
                'sentence' => 'Held in the Hartford County Jail.',
                'institution_city' => 'Hartford', 'institution_state' => 'Connecticut',
            ]],
        ], []);

        // ── INDIVIDUAL 1932 CASES ─────────────────────────────────────────
        $mk([
            'name' => 'Israel Lazar', 'first_name' => 'Israel', 'last_name' => 'Lazar',
            'description' => "Israel Lazar was a young revolutionary worker held (inmate No. 6977) in the Eastern State Penitentiary at Philadelphia, sentenced to two years for speaking for the Communist Party during the 1928 election campaign and kept in solitary confinement on the fifth gallery.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Sentenced for speaking for the Communist Party at Philadelphia.',
                'convicted' => 'Convicted; re-jailed 1932',
                'sentence' => 'Two years, in solitary confinement, Eastern State Penitentiary.',
                'institution_name' => 'Eastern State Penitentiary',
                'institution_city' => 'Philadelphia', 'institution_state' => 'Pennsylvania',
            ]],
        ], []);
        $mk([
            'name' => 'Harry Roth', 'first_name' => 'Harry', 'last_name' => 'Roth',
            'description' => "Harry Roth was arrested for speaking at an open-air meeting and convicted under Pennsylvania's Flynn sedition law, sentenced to a year and held in the Delaware County jail at Media in 1932.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Convicted under Pennsylvania's Flynn sedition law for speaking at an open-air meeting.",
                'convicted' => 'Convicted, 1932',
                'sentence' => 'One year in the Delaware County jail, Media.',
                'institution_name' => 'Delaware County Jail',
                'institution_city' => 'Media', 'institution_state' => 'Pennsylvania',
            ]],
        ], ['incarceration_date' => [1932, null, null]]);
        $mk([
            'name' => 'Ernest McDuffy', 'first_name' => 'Ernest', 'last_name' => 'McDuffy',
            'description' => "Ernest McDuffy was an eighteen-year-old worker arrested in January 1932 on a frame-up rape charge and sentenced to seven years in the St. Cloud Reformatory, Minnesota, carried on the ILD's youth-prisoner list.",
            'state' => 'Minnesota', 'gender' => 'Male',
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted on a frame-up rape charge in Minnesota.',
                'convicted' => 'Convicted, January 1932',
                'sentence' => 'Seven years in the St. Cloud Reformatory.',
                'institution_name' => 'St. Cloud Reformatory',
                'institution_city' => 'St. Cloud', 'institution_state' => 'Minnesota',
            ]],
        ], ['incarceration_date' => [1932, 1, null]]);
        $mk([
            'name' => 'John Porter', 'first_name' => 'John', 'last_name' => 'Porter',
            'description' => "John Porter was an Army deserter and vice-president of the New Bedford Textile Workers' Union arrested in the April 1928 New Bedford, Massachusetts textile strike and court-martialed to two and a half years' hard labor at Fort Leavenworth, Kansas.",
            'state' => 'Massachusetts', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['National Textile Workers Union'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Court-martialed after the 1928 New Bedford textile strike.',
                'convicted' => 'Court-martialed, 1928',
                'sentence' => "Two and a half years' hard labor at Fort Leavenworth.",
                'institution_name' => 'Fort Leavenworth',
                'institution_city' => 'Leavenworth', 'institution_state' => 'Kansas',
            ]],
        ], []);
        $mk([
            'name' => 'Sam Darcy', 'first_name' => 'Sam', 'last_name' => 'Darcy',
            'description' => "Sam Darcy, editor of the Western Worker, was arrested with forty-five other workers at Long Beach, California in 1932 for attempting to lecture on the economic crisis; he tried his own case and he and the forty-five were acquitted.",
            'state' => 'California', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'affiliation' => ['Communist Party USA'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested for a free-speech lecture at Long Beach, California.',
                'convicted' => 'Acquitted, 1932',
                'sentence' => 'Arrested; acquitted at trial.',
                'institution_city' => 'Long Beach', 'institution_state' => 'California',
            ]],
        ], ['arrest_date' => [1932, null, null]]);
        $mk([
            'name' => 'Henry Shepard', 'first_name' => 'Henry', 'last_name' => 'Shepard',
            'description' => "Henry Shepard was a Black Communist candidate for lieutenant-governor of New York jailed five days in 1932 for leading a relief delegation of Harlem's unemployed.",
            'state' => 'New York', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Communism'],
            'affiliation' => ['Communist Party USA'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed for leading a relief delegation in Harlem.',
                'convicted' => 'Held five days, 1932',
                'sentence' => 'Jailed five days.',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['arrest_date' => [1932, null, null]]);

        // ── 1932 DEPORTATION DRIVE ───────────────────────────────────────
        $deport = [
            ['Frank Borich', 'Frank', 'Borich', 'Yugoslavia', "the general secretary of the National Miners' Union, held for deportation for leading the 43,000-strong 1931 coal strike"],
            ['Vincent Kamenovich', 'Vincent', 'Kamenovich', 'Yugoslavia', "a National Miners' Union organizer held for deportation alongside Frank Borich"],
            ['A. W. Mills', 'A. W.', 'Mills', 'Britain', "the national organizer of the 1931–32 Hunger March on Washington, arrested for deportation and held under $1,000 bail"],
            ['Louis Bebrits', 'Louis', 'Bebrits', 'Romania', "the editor of the Hungarian workers' daily Uj Elore, ordered deported"],
            ['Nels Kjar', 'Nels', 'Kjar', 'Denmark', "a militant Chicago worker slated for deportation"],
            ['G. Antonoff', 'G.', 'Antonoff', 'Bulgaria', "a leader of Detroit's unemployed ordered deported"],
            ['Jack Schneider', 'Jack', 'Schneider', 'Poland', "a militant New York needle-trades strike leader ordered deported"],
            ['Leon Glaser', 'Leon', 'Glaser', 'Russia', "the secretary of the Portland branch of the Friends of the Soviet Union, held for deportation \"to the Soviet Union via Shanghai\""],
            ['Michael Saksagansky', 'Michael', 'Saksagansky', 'Russia', "held for deportation with Leon Glaser"],
            ['Hans Simon', 'Hans', 'Simon', 'Germany', "held for deportation in the Mobile, Alabama county jail"],
            ['Eduardo Machado', 'Eduardo', 'Machado', 'Venezuela', "a Venezuelan worker selected for deportation to a fascist-ruled country"],
            ['Matti Tenhunen', 'Matti', 'Tenhunen', 'Finland', "a U.S. citizen and organizer of Finnish-American emigration to Karelia, arrested by the political police at Helsinki"],
        ];
        foreach ($deport as [$name, $first, $last, $origin, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} in the intensified deportation drive of 1932 under Secretary of Labor William Doak.",
                'gender' => 'Male',
                'ideologies' => ['Communism', 'Labor organizing'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Held for deportation for radical labor activity during the 1932 deportation drive.',
                    'convicted' => 'Held for deportation, 1932',
                    'sentence' => 'Held pending deportation; defended by the ILD.',
                ]],
            ], ['arrest_date' => [1932, null, null]]);
        }

        // ── CALIFORNIA MAY DAY / ANTI-WAR ARRESTS ────────────────────────
        foreach ([
            ['W. H. Langley', 'W. H.', 'Langley', 'San Pedro', 'suspicion of criminal syndicalism'],
            ['W. Howard', 'W.', 'Howard', 'San Pedro', 'suspicion of criminal syndicalism'],
            ['Bernard Rosenfeld', 'Bernard', 'Rosenfeld', 'Los Angeles', 'an anti-war meeting'],
            ['James Turner', 'James', 'Turner', 'Los Angeles', 'an anti-war meeting'],
        ] as [$name, $first, $last, $city, $charge]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was arrested at {$city}, California in May 1932 on {$charge}.",
                'state' => 'California', 'gender' => 'Male',
                'ideologies' => ['Communism'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => "Arrested at {$city}, California in a May 1932 anti-war / criminal-syndicalism sweep.",
                    'convicted' => 'Arrested, May 1932',
                    'sentence' => 'Held after the arrest.',
                    'institution_city' => $city, 'institution_state' => 'California',
                ]],
            ], ['arrest_date' => [1932, 5, null]]);
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

        $this->info("\nDone. Processed {$added} of ".count($people)." 1932 Labor Defender prisoner(s).");

        return self::SUCCESS;
    }
}
