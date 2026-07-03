<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Batch 13 of the ILD Labor Defender mining, covering the whole 1933 volume
 * (Vol. IX, Jan–Dec). 1933 brought the Scottsboro Decatur retrials, the
 * sentencing of Angelo Herndon, the Alabama Share Croppers' Union shootout at
 * Reeltown, a nationwide farm-defense revolt against foreclosures, and the
 * first wave of NRA-era strikes.
 *
 * This adds the clearly-attested NEW prisoners of 1933. Marquee cases:
 *  - the Reeltown / Tallapoosa County, Alabama Share Croppers' Union;
 *  - the Denver / Brighton, Colorado sugar-beet-worker death case;
 *  - the 1933 farm-defense / anti-foreclosure criminal-syndicalism arrests
 *    across Michigan, Nebraska, South Dakota and Wisconsin;
 *  - the Anacortes / Skagit County, Washington food-raid case;
 *  - the Terzani anti-fascist frame-up, the Utica unemployed-council five,
 *    and a range of NRA-strike, unemployed-demonstration and deportation cases.
 *
 * Cases already in the database are skipped: Scottsboro, Mooney/Billings,
 * McNamara/Schmidt, Centralia, Imperial Valley, Angelo Herndon, the Atlanta
 * Six, Edith Berkman, Euel Lee, the Tampa defendants, the batch-11/12 Harlan
 * and Pennsylvania coal rosters (Getto, Leo Thompson, the Rasefskys, Orloff),
 * Ben Gold, and Alexander Berkman. The 91-name January "1000 Years in Prison"
 * honor roll was mined but only its genuinely-new entries are added here.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddLaborDefender1933Cases extends Command
{
    protected $signature = 'prisoners:add-labor-defender-1933';

    protected $description = 'Add the 1933 Labor Defender class-war prisoners (Reeltown sharecroppers, Colorado beet workers, farm-defense arrests, NRA-strike and unemployed cases)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── REELTOWN / TALLAPOOSA COUNTY SHARE CROPPERS' UNION ────────────
        $reeltownBase = "On 19 December 1932 a sheriff's posse attacked members of the Communist-led Share Croppers' Union near Reeltown in Tallapoosa County, Alabama, when they resisted the seizure of a member's livestock for debt. Several croppers were shot; the survivors were charged with \"assault with intent to murder\" and held for the grand jury at the Dadeville jail under $750 bail. The case, following the 1931 Camp Hill affair, became a major ILD Southern defense.";
        $mk([
            'name' => 'Cliff James', 'first_name' => 'Cliff', 'last_name' => 'James',
            'description' => "Clifford \"Cliff\" James was a sharecropper and a leader of the Share Croppers' Union whose farm was the site of the 19 December 1932 Reeltown, Alabama shootout. Wounded in the shoulder, he crawled to Tuskegee Institute, was turned over to the police, and died in the Montgomery County jail on 27 December 1932 while held in dying condition. ".$reeltownBase,
            'state' => 'Alabama', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['Share Croppers Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => false,
            'cases' => [[
                'charges' => 'Charged with assault with intent to murder after the Reeltown Share Croppers\' Union shootout.',
                'convicted' => 'Died in custody, 27 December 1932',
                'sentence' => 'Died of his wounds in the Montgomery County jail.',
                'institution_name' => 'Montgomery County Jail',
                'institution_city' => 'Montgomery', 'institution_state' => 'Alabama',
            ]],
        ], ['arrest_date' => [1932, 12, 19], 'death_in_custody_date' => [1932, 12, 27]]);
        $mk([
            'name' => 'Milo Bentley', 'first_name' => 'Milo', 'last_name' => 'Bentley',
            'description' => "Milo Bentley was a sharecropper and Share Croppers' Union member arrested on 21 December 1932 in the aftermath of the Reeltown, Alabama shootout, shot five times \"resisting arrest.\" Held in the Montgomery County jail, he died about ten hours after being transferred to the Kilby Prison hospital on 27 December 1932. ".$reeltownBase,
            'state' => 'Alabama', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['Share Croppers Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => false,
            'cases' => [[
                'charges' => 'Arrested after the Reeltown Share Croppers\' Union shootout, shot five times.',
                'convicted' => 'Died in custody, 27 December 1932',
                'sentence' => 'Died in the Kilby Prison hospital.',
                'institution_name' => 'Kilby Prison',
                'institution_city' => 'Montgomery', 'institution_state' => 'Alabama',
            ]],
        ], ['arrest_date' => [1932, 12, 21], 'death_in_custody_date' => [1932, 12, 27]]);
        $reeltownConvicted = [
            ['Ned Cobb', 'Ned', 'Cobb', '12 to 15 years'],
            ['Judson Simpson', 'Judson', 'Simpson', '10 to 12 years'],
            ['Alfred White', 'Alfred', 'White', '10 to 10½ years'],
            ['Clinton Moss', 'Clinton', 'Moss', '10 to 10½ years'],
            ['Sam Moss', 'Sam', 'Moss', '5 to 6 years'],
        ];
        foreach ($reeltownConvicted as [$name, $first, $last, $term]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a Black sharecropper and Share Croppers' Union member wounded and arrested in the 19 December 1932 Reeltown, Alabama shootout, one of the survivors tried at Dadeville before Judge W. B. Bowling in 1933 and sentenced to {$term} for \"assault with intent to murder.\" (Ned Cobb was later the subject of Theodore Rosengarten's oral history \"All God's Dangers.\") ".$reeltownBase,
                'state' => 'Alabama', 'gender' => 'Male', 'race' => 'Black',
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['Share Croppers Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Convicted of assault with intent to murder after the Reeltown Share Croppers\' Union shootout.',
                    'convicted' => 'Convicted at Dadeville, 1933',
                    'sentence' => "{$term} in the Alabama penitentiary.",
                    'institution_state' => 'Alabama',
                ]],
            ], ['arrest_date' => [1932, 12, 19]]);
        }
        $mk([
            'name' => 'Minion Clifton', 'first_name' => 'Minion', 'last_name' => 'Clifton',
            'description' => "Minion Clifton was a Black sharecropper backed by the Share Croppers' Union, framed on an arson charge at Dadeville, Alabama in 1933 amid the terror against the union in Tallapoosa County.",
            'state' => 'Alabama', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Share Croppers Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed on an arson charge at Dadeville, Alabama.',
                'convicted' => 'Held on the arson charge, 1933',
                'sentence' => 'Held; defended by the ILD.',
                'institution_city' => 'Dadeville', 'institution_state' => 'Alabama',
            ]],
        ], ['arrest_date' => [1933, null, null]]);

        // ── DENVER / BRIGHTON, COLORADO SUGAR-BEET-WORKER DEATH CASE ──────
        foreach ([
            ['Joe Saiz', 'Joe', 'Saiz'],
            ['Roy Vigil', 'Roy', 'Vigil'],
            ['Candalaria Montoya', 'Candalaria', 'Montoya'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was an eighteen-year-old Spanish-speaking sugar-beet worker sentenced to death near Brighton, in Adams County, Colorado in 1933 on a coerced confession for the murder of a farmer, George Arnold — a frame-up the ILD fought as a case of Mexican-American beet laborers railroaded to the electric chair.",
                'state' => 'Colorado', 'gender' => 'Male', 'race' => 'Hispanic',
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Sentenced to death on a coerced confession for the murder of a farmer near Brighton, Colorado.',
                    'convicted' => 'Sentenced to death, 1933',
                    'sentence' => 'Death; the conviction was fought by the ILD.',
                    'institution_state' => 'Colorado',
                ]],
            ], ['arrest_date' => [1933, null, null]]);
        }

        // ── 1933 FARM-DEFENSE / ANTI-FORECLOSURE ARRESTS ─────────────────
        $farmBase = "The farm revolt of 1933 — the Farmers' Holiday Association and the Communist-led United Farmers' League fighting mortgage foreclosures with penny auctions and picket lines — brought waves of arrests across the Midwest, many under state criminal-syndicalism laws.";
        $farm = [
            ['John Rose', 'John', 'Rose', 'Michigan', "sentenced to six months to five years in the Michigan State Penitentiary for a foreclosure-resistance protest"],
            ['Nile Cochran', 'Nile', 'Cochran', 'South Dakota', "an Iowa farmer and milk-strike picket sentenced to three years in the South Dakota penitentiary at Elk Point for manslaughter after a strikebreaker was killed"],
            ['George Casper', 'George', 'Casper', 'Michigan', "a Farmers' League member charged with criminal syndicalism at White Cloud, Michigan"],
            ['Clyde Smith', 'Clyde', 'Smith', 'Michigan', "a Farmers' League member charged with criminal syndicalism at White Cloud, Michigan"],
            ['Harry Lux', 'Harry', 'Lux', 'Nebraska', "a leader of the Nebraska farm revolt held in solitary confinement on shifting charges"],
            ['Otto Passow', 'Otto', 'Passow', 'Michigan', "one of the Bad Axe, Michigan farmers charged with criminal syndicalism"],
            ['Forrest Jackson', 'Forrest', 'Jackson', 'Wisconsin', "arrested in the Wisconsin farm strike at Wausau"],
        ];
        foreach ($farm as [$name, $first, $last, $state, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} during the 1933 farm revolt. ".$farmBase,
                'state' => $state, 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['United Farmers League'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed in the 1933 farm-defense / anti-foreclosure struggle.',
                    'convicted' => 'Held / convicted, 1933',
                    'sentence' => 'Jailed in the farm revolt; defended by the ILD.',
                    'institution_state' => $state,
                ]],
            ], ['arrest_date' => [1933, null, null]]);
        }

        // ── ANACORTES / SKAGIT COUNTY, WASHINGTON FOOD RAID ──────────────
        $anacortesBase = "In 1932 starving mill and cannery workers at Anacortes, in Skagit County, Washington raided a chain grocery for food; the leaders were framed on grand-larceny and inciting-to-riot charges and sentenced in October 1932 at Mount Vernon, several of them Communist election candidates.";
        $anacortes = [
            ['Ivor Moe', 'Ivor', 'Moe', "a Communist candidate for state senator, sentenced to one to two years and a $200 fine"],
            ['Stanley Anderson', 'Stanley', 'Anderson', "a painter and Communist legislative candidate, sentenced to six to eight months and a $100 fine"],
            ['William Wollertz', 'William', 'Wollertz', "a mill worker sentenced to six to eighteen months and a $100 fine"],
            ['Ray Trafton', 'Ray', 'Trafton', "a carpenter and Communist candidate for county commissioner, sentenced to nine to twenty-one months and a $100 fine"],
        ];
        foreach ($anacortes as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} in the Anacortes food-raid case, held in the Washington State Reformatory at Monroe. ".$anacortesBase,
                'state' => 'Washington', 'gender' => 'Male',
                'ideologies' => ['Communism'],
                'affiliation' => ['Unemployed Councils'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Convicted of grand larceny and inciting to riot in the Anacortes food raid.',
                    'convicted' => 'Convicted, October 1932',
                    'sentence' => 'Held in the Washington State Reformatory, Monroe.',
                    'institution_name' => 'Washington State Reformatory',
                    'institution_city' => 'Monroe', 'institution_state' => 'Washington',
                ]],
            ], ['incarceration_date' => [1932, 10, 22]]);
        }

        // ── TERZANI ANTI-FASCIST FRAME-UP (Khaki Shirts) ─────────────────
        $mk([
            'name' => 'Athos Terzani', 'first_name' => 'Athos', 'last_name' => 'Terzani',
            'description' => "Athos Terzani was an anti-fascist worker framed in 1933 for the shooting of Anthony Fierro at a rally of the fascist \"Khaki Shirts\" in Long Island City, New York — a killing the ILD showed was done by the Khaki Shirts' own men. He was defended by the ILD and eventually acquitted.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Anti-fascism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed for the murder of Anthony Fierro at a fascist Khaki Shirts rally in Long Island City.',
                'convicted' => 'Held for trial, 1933; later acquitted',
                'sentence' => 'Held on the murder charge; acquitted.',
                'institution_city' => 'Long Island City', 'institution_state' => 'New York',
            ]],
        ], ['arrest_date' => [1933, null, null]]);
        $mk([
            'name' => 'Michael Palumbo', 'first_name' => 'Michael', 'last_name' => 'Palumbo',
            'description' => "Michael Palumbo was an anti-fascist arrested with Athos Terzani and framed in connection with the shooting at the Khaki Shirts rally in Long Island City, New York in 1933.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Anti-fascism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed in the Khaki Shirts shooting at Long Island City.',
                'convicted' => 'Held, 1933',
                'sentence' => 'Held on the frame-up.',
                'institution_city' => 'Long Island City', 'institution_state' => 'New York',
            ]],
        ], ['arrest_date' => [1933, null, null]]);

        // ── UTICA, N.Y. UNEMPLOYED-COUNCIL FIVE ──────────────────────────
        $uticaBase = "Five unemployed workers of Utica, New York were convicted in December 1932 in an anti-hunger demonstration case and sentenced in 1933.";
        $utica = [
            ['Peter Kraus', 'Peter', 'Kraus', "a Communist Party organizer at Utica, sentenced to one and a half to three years at Auburn State Prison"],
            ['John Della Monica', 'John', 'Della Monica', "an ILD leader at Utica, sentenced to one and a half to three years at Auburn State Prison"],
            ['Frank Carone', 'Frank', 'Carone', "a one-armed Italian youth sentenced to a year"],
            ['Mike Charles', 'Mike', 'Charles', "one of the five Utica unemployed defendants"],
            ['Rocco Volpe', 'Rocco', 'Volpe', "one of the five Utica unemployed defendants"],
        ];
        foreach ($utica as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} in the Utica, New York unemployed-council case. ".$uticaBase,
                'state' => 'New York', 'gender' => 'Male',
                'ideologies' => ['Communism'],
                'affiliation' => ['Unemployed Councils'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Convicted in the Utica, New York anti-hunger demonstration case.',
                    'convicted' => 'Convicted, December 1932',
                    'sentence' => 'Sentenced to prison terms; the leaders to Auburn.',
                    'institution_state' => 'New York',
                ]],
            ], ['incarceration_date' => [1933, null, null]]);
        }

        // ── MILWAUKEE UNEMPLOYED-RELIEF DEMONSTRATIONS ───────────────────
        $milwaukee = [
            ['Joe Hawkins', 'Joe', 'Hawkins', "the leader of a Milwaukee unemployed demonstration, sentenced to six years at the Wisconsin State Prison at Waupun", 'Wisconsin State Prison', 'Waupun'],
            ['Fred Burback', 'Fred', 'Burback', "a Milwaukee relief-case defendant framed on perjury after a relief demonstration and sentenced to two years", 'Milwaukee House of Correction', 'Milwaukee'],
            ['Benjamin Feifer', 'Benjamin', 'Feifer', "sentenced to ten months for rioting and unlawful assembly in a Milwaukee relief demonstration", 'Milwaukee House of Correction', 'Milwaukee'],
            ['Carl Lester', 'Carl', 'Lester', "sentenced to ten months for rioting and unlawful assembly in a Milwaukee relief demonstration", 'Milwaukee House of Correction', 'Milwaukee'],
        ];
        foreach ($milwaukee as [$name, $first, $last, $who, $inst, $city]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} in the Milwaukee, Wisconsin unemployed-relief demonstrations of 1932.",
                'state' => 'Wisconsin', 'gender' => 'Male',
                'ideologies' => ['Communism'],
                'affiliation' => ['Unemployed Councils'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed for a Milwaukee unemployed-relief demonstration.',
                    'convicted' => 'Convicted, 1932',
                    'sentence' => "Held at the {$inst}.",
                    'institution_name' => $inst,
                    'institution_city' => $city, 'institution_state' => 'Wisconsin',
                ]],
            ], ['incarceration_date' => [1932, 10, null]]);
        }

        // ── NEW YORK — NEEDLE TRADES & MARINE FRAME-UPS ──────────────────
        foreach ([
            ['Salvatore Adalchi', 'Salvatore', 'Adalchi'],
            ['Leonard Miller', 'Leonard', 'Miller'],
            ['Dave Turner', 'Dave', 'Turner'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a needle-trades worker arrested on a New York picket line during the 1932 garment strike and sentenced to an indeterminate term of up to three years on Hart's Island.",
                'state' => 'New York', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['Needle Trades Workers Industrial Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Arrested on a needle-trades picket line in New York.',
                    'convicted' => 'Convicted, 1932',
                    'sentence' => 'Up to three years on Hart\'s Island.',
                    'institution_name' => 'Hart\'s Island',
                    'institution_city' => 'New York', 'institution_state' => 'New York',
                ]],
            ], ['incarceration_date' => [1932, 4, null]]);
        }
        foreach ([
            ['Thomas Bunker', 'Thomas', 'Bunker', 'Sing Sing Prison', 'Ossining', '6½ to 25 years'],
            ['John Soderberg', 'John', 'Soderberg', 'Sing Sing Prison', 'Ossining', '12½ to 25 years'],
            ['William Trajer', 'William', 'Trajer', 'Clinton Prison', 'Dannemora', '6½ to 25 years'],
        ] as [$name, $first, $last, $inst, $city, $term]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a marine worker framed in New York in 1932 on a charge of \"placing explosives in a vessel with intent to injure human life\" and sentenced to {$term}.",
                'state' => 'New York', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['Marine Workers Industrial Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Framed for placing explosives in a vessel, New York.',
                    'convicted' => 'Convicted, 25 April 1932',
                    'sentence' => "{$term} at {$inst}.",
                    'institution_name' => $inst,
                    'institution_city' => $city, 'institution_state' => 'New York',
                ]],
            ], ['incarceration_date' => [1932, 4, 25]]);
        }

        // ── INDIVIDUAL 1933 CASES ─────────────────────────────────────────
        $mk([
            'name' => 'Sam Gonshak', 'first_name' => 'Sam', 'last_name' => 'Gonshak',
            'description' => "Sam Gonshak was a New York Unemployed Council organizer sentenced to two years in the Welfare Island workhouse in 1933 on a \"disorderly conduct\" charge for leading two hundred jobless workers to a Home Relief Bureau.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['Unemployed Councils'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with disorderly conduct for leading a relief demonstration in New York.',
                'convicted' => 'Convicted, 1933',
                'sentence' => 'Two years in the Welfare Island workhouse.',
                'institution_name' => 'Welfare Island',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['incarceration_date' => [1933, null, null]]);
        $mk([
            'name' => 'Sam Weinstein', 'first_name' => 'Sam', 'last_name' => 'Weinstein',
            'description' => "Sam Weinstein was a decorated World War veteran and leader of the 1932 Muskin Manufacturing Company furniture strike in New York, a member of the Furniture Workers' Industrial Union, framed for an assault on strikebreakers in the Bronx and convicted of second-degree manslaughter on 20 February 1933, facing a further charge carrying twenty years to life.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Furniture Workers Industrial Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed for assault on strikebreakers during the Muskin furniture strike, New York.',
                'convicted' => 'Convicted of second-degree manslaughter, 20 February 1933',
                'sentence' => 'Facing twenty years to life.',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['arrest_date' => [1932, 7, null]]);
        $mk([
            'name' => 'Peter Panagopoulos', 'first_name' => 'Peter', 'last_name' => 'Panagopoulos',
            'description' => "Peter Panagopoulos was a Greek worker and bookseller arrested in a Red Squad raid on a Long Beach, California mass meeting in 1932, indicted for criminal syndicalism by a Los Angeles grand jury, and held under $3,000 bail while fighting deportation to Greece — a focal case in the campaign to repeal California's criminal-syndicalism law.",
            'state' => 'California', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Indicted for criminal syndicalism at Los Angeles and held for deportation to Greece.',
                'convicted' => 'Held on $3,000 bail, 1932–33',
                'sentence' => 'Held on the criminal-syndicalism charge and deportation fight.',
                'institution_city' => 'Los Angeles', 'institution_state' => 'California',
            ]],
        ], ['arrest_date' => [1932, 1, null]]);
        $mk([
            'name' => 'Theodore Jordan', 'first_name' => 'Theodore', 'last_name' => 'Jordan',
            'description' => "Theodore Jordan was a Black worker tortured into signing seven statements and sentenced to death in a Southern Pacific railroad murder frame-up, held at the Oregon State Penitentiary; the ILD won a commutation and appeal.",
            'state' => 'Oregon', 'gender' => 'Male', 'race' => 'Black',
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed for murder in a Southern Pacific railroad case, tortured into confessions.',
                'convicted' => 'Sentenced to death; commuted on appeal',
                'sentence' => 'Death, commuted; held at the Oregon State Penitentiary.',
                'institution_name' => 'Oregon State Penitentiary',
                'institution_state' => 'Oregon',
            ]],
        ], []);
        $mk([
            'name' => 'Todor Antonoff', 'first_name' => 'Todor', 'last_name' => 'Antonoff',
            'description' => "Todor Antonoff was an Auto Workers Union organizer in the Detroit–Pontiac district held eleven months under $25,000 bail on a criminal-syndicalism charge and threatened with deportation; the ILD won his release to a country of his choice.",
            'state' => 'Michigan', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['Auto Workers Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with criminal syndicalism in the Detroit auto district and held for deportation.',
                'convicted' => 'Held eleven months, 1932–33',
                'sentence' => 'Held under $25,000 bail; released to a country of his choice.',
                'institution_city' => 'Detroit', 'institution_state' => 'Michigan',
            ]],
        ], ['arrest_date' => [1932, null, null]]);
        $mk([
            'name' => 'Jesse Crawford', 'first_name' => 'Jesse', 'last_name' => 'Crawford',
            'description' => "Jesse Crawford was a young Black worker repeatedly framed onto the Georgia chain gang who escaped and was recaptured in Detroit; a national ILD protest forced the Michigan governor to refuse Georgia's extradition demand in January 1933. He had earlier been held four months in the Fulton Tower at Atlanta.",
            'state' => 'Georgia', 'gender' => 'Male', 'race' => 'Black',
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed onto the Georgia chain gang; fought extradition from Michigan.',
                'convicted' => 'Chain-gang sentence; extradition refused January 1933',
                'sentence' => 'Held on the Georgia chain gang; freed when Michigan refused extradition.',
                'institution_state' => 'Georgia',
            ]],
        ], ['arrest_date' => [1931, null, null]]);
        $mk([
            'name' => 'Herbert Benjamin', 'first_name' => 'Herbert', 'last_name' => 'Benjamin',
            'description' => "Herbert Benjamin was a national leader of the Unemployed Councils and the hunger marches, arrested by the army in New Mexico in 1933 while organizing the unemployed.",
            'state' => 'New Mexico', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['Unemployed Councils'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested by the army in New Mexico while organizing the unemployed.',
                'convicted' => 'Arrested, 1933',
                'sentence' => 'Held after the arrest.',
                'institution_state' => 'New Mexico',
            ]],
        ], ['arrest_date' => [1933, null, null]]);
        $mk([
            'name' => 'John Adams', 'first_name' => 'John', 'last_name' => 'Adams',
            'description' => "John Adams was arrested for speaking at a Communist election meeting and convicted of sedition in Pennsylvania, held on an indeterminate sentence at the Huntingdon industrial school, where he lost a month's time for protesting prison conditions.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted of sedition for speaking at a Communist election meeting in Pennsylvania.',
                'convicted' => 'Convicted, 7 April 1932',
                'sentence' => 'Indeterminate term at the Huntingdon industrial school.',
                'institution_name' => 'Pennsylvania Industrial School',
                'institution_city' => 'Huntingdon', 'institution_state' => 'Pennsylvania',
            ]],
        ], ['incarceration_date' => [1932, 4, 7]]);
        $mk([
            'name' => 'Leon Moore', 'first_name' => 'Leon', 'last_name' => 'Moore',
            'description' => "Leon Moore was a nineteen-year-old textile striker sentenced to five to seven years for dynamiting a house during the 1929 Marion, North Carolina strike, held at the state prison camp at Tryon.",
            'state' => 'North Carolina', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['National Textile Workers Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted of dynamiting a house during the Marion, North Carolina textile strike.',
                'convicted' => 'Convicted, June 1931',
                'sentence' => 'Five to seven years at the Tryon prison camp.',
                'institution_city' => 'Tryon', 'institution_state' => 'North Carolina',
            ]],
        ], ['incarceration_date' => [1931, 6, null]]);
        $mk([
            'name' => 'James Ford', 'first_name' => 'James', 'last_name' => 'Ford', 'middle_name' => 'Randall',
            'description' => "James Ford was a twenty-year-old Black worker arrested at an anti-jim-crow action at the Bronxdale swimming pool in New York and committed to the House of Refuge on Randall's Island in 1932. (He is distinct from the Communist vice-presidential candidate James W. Ford.)",
            'state' => 'New York', 'gender' => 'Male', 'race' => 'Black',
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested at an anti-jim-crow protest at a New York swimming pool.',
                'convicted' => 'Committed, 5 September 1932',
                'sentence' => 'Held at the House of Refuge, Randall\'s Island.',
                'institution_name' => 'House of Refuge',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['incarceration_date' => [1932, 9, 5]]);

        // ── VIRGINIA SOUTHERN CASES ──────────────────────────────────────
        $mk([
            'name' => 'Joe Benson', 'first_name' => 'Joe', 'last_name' => 'Benson',
            'description' => "Joe Benson was arrested at Norfolk, Virginia under a \"move-on\" ordinance for unemployed organizing in 1933; his case was carried on appeal by the ILD.",
            'state' => 'Virginia', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested under a Norfolk "move-on" ordinance for unemployed organizing.',
                'convicted' => 'Convicted, 1933; on appeal',
                'sentence' => 'Held; case appealed by the ILD.',
                'institution_city' => 'Norfolk', 'institution_state' => 'Virginia',
            ]],
        ], ['arrest_date' => [1933, null, null]]);
        $mk([
            'name' => 'Reginald Leftwich', 'first_name' => 'Reginald', 'last_name' => 'Leftwich',
            'description' => "Reginald Leftwich was a Black worker held on a murder charge at Lynchburg, Virginia in 1933 in a case the ILD took up as a Southern frame-up.",
            'state' => 'Virginia', 'gender' => 'Male', 'race' => 'Black',
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Held on a murder charge at Lynchburg, Virginia.',
                'convicted' => 'Held for trial, 1933',
                'sentence' => 'Held on the murder charge; defended by the ILD.',
                'institution_city' => 'Lynchburg', 'institution_state' => 'Virginia',
            ]],
        ], ['arrest_date' => [1933, null, null]]);

        // ── BIRMINGHAM UNEMPLOYED COUNCIL ────────────────────────────────
        foreach ([
            ['Wirt Taylor', 'Wirt', 'Taylor', 'Male'],
            ['Alice Burke', 'Alice', 'Burke', 'Female'],
        ] as [$name, $first, $last, $gender]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was an Unemployed Council organizer in Birmingham, Alabama sentenced in 1933 to six months at hard labor for relief-demonstration activity.",
                'state' => 'Alabama', 'gender' => $gender,
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['Unemployed Councils'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Sentenced for unemployed-council activity at Birmingham, Alabama.',
                    'convicted' => 'Convicted, 1933',
                    'sentence' => 'Six months at hard labor.',
                    'institution_city' => 'Birmingham', 'institution_state' => 'Alabama',
                ]],
            ], ['incarceration_date' => [1933, null, null]]);
        }

        // ── DEPORTATION / MISC ────────────────────────────────────────────
        $mk([
            'name' => 'Basil Wahib', 'first_name' => 'Basil', 'last_name' => 'Wahib',
            'description' => "Basil Wahib was one of thirteen dye-workers arrested in the 1933 Paterson, New Jersey silk-and-dye strike and held at Ellis Island for deportation to the Philippines.",
            'state' => 'New Jersey', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested in the Paterson dye strike and held for deportation.',
                'convicted' => 'Held for deportation, 1933',
                'sentence' => 'Held at Ellis Island.',
                'institution_name' => 'Ellis Island',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['arrest_date' => [1933, null, null]]);
        foreach ([
            ['Pavel Martinov', 'Pavel', 'Martinov', 'Cleveland, Ohio'],
            ['Emil Gardos', 'Emil', 'Gardos', 'Hungary'],
            ['Jack Thomas', 'Jack', 'Thomas', 'Pittsburgh, Pennsylvania'],
        ] as [$name, $first, $last, $where]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a foreign-born labor militant held for deportation in the 1933 deportation drive ({$where}).",
                'gender' => 'Male',
                'ideologies' => ['Communism', 'Labor organizing'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Held for deportation for radical labor activity, 1933.',
                    'convicted' => 'Held for deportation, 1933',
                    'sentence' => 'Held pending deportation; defended by the ILD.',
                ]],
            ], ['arrest_date' => [1933, null, null]]);
        }
        $mk([
            'name' => 'Irving Potash', 'first_name' => 'Irving', 'last_name' => 'Potash',
            'description' => "Irving Potash was a leader of the Furriers' Industrial Union arrested with other fur-workers in a 1933 New York strike case and held on heavy bail.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['Needle Trades Workers Industrial Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested in a New York fur-workers strike case.',
                'convicted' => 'Held on bail, 1933',
                'sentence' => 'Held on the strike charges.',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['arrest_date' => [1933, null, null]]);

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

        $this->info("\nDone. Processed {$added} of ".count($people)." 1933 Labor Defender prisoner(s).");

        return self::SUCCESS;
    }
}
