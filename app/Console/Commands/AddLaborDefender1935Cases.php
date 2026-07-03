<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Batch 15 of the ILD Labor Defender mining, covering the whole 1935 volume
 * (Vol. XI, Jan–Dec). 1935 brought Norris v. Alabama, the Gallup, New Mexico
 * coal-camp tragedy and mass murder trial, the Burlington dynamite plot,
 * the Southern Tenant Farmers' Union terror in Arkansas, the Sacramento
 * CAWIU convictions, and the anti-Nazi deportation drive.
 *
 * This adds the clearly-attested NEW prisoners of 1935. Marquee cases:
 *  - the Gallup, New Mexico murder trial (Ochoa, Avitia, Velarde and the
 *    other named defendants of the April 1935 eviction riot);
 *  - the Burlington, North Carolina dynamite frame-up (textile strikers);
 *  - the Oklahoma City federal sedition case (a FERA relief demonstration);
 *  - the S.S. Bremen anti-Nazi demonstration and the anti-Nazi deportation
 *    drive; the SF Ship Scalers frame-up; Ward Rodgers of the Arkansas STFU;
 *  - and a broad set of strike, relief-demonstration and legal-lynching cases.
 *
 * Cases already in the database are skipped: Scottsboro, Mooney/Billings,
 * Angelo Herndon, the Sacramento CAWIU defendants, Dirk DeJonge, the
 * Reeltown sharecroppers, Anita Whitney, Powers Hapgood, Otto Richter,
 * Theodore Jordan, Jess Hollins, and the batch-11–14 rosters. Surname-only
 * OCR fragments were omitted rather than added as uncertain data.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddLaborDefender1935Cases extends Command
{
    protected $signature = 'prisoners:add-labor-defender-1935';

    protected $description = 'Add the 1935 Labor Defender class-war prisoners (Gallup NM trial, Burlington dynamite plot, Oklahoma federal sedition, Bremen case, 1935 deportation drive)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── GALLUP, NEW MEXICO ────────────────────────────────────────────
        $gallupBase = "On 4 April 1935 a crowd of Gallup, New Mexico coal miners protesting the eviction of a National Miners' Union family clashed with deputies; Sheriff Carmichael was killed and two miners shot dead. Under martial law some six hundred were rounded up, dozens held for deportation, and ten miners — most of them Mexican-born — were charged with first-degree murder, facing the electric chair. At the Aztec trial in October 1935 three were convicted of second-degree murder and sentenced to forty-five to sixty years; the rest were acquitted, several only to be seized for deportation. The Gallup case became one of the ILD's great Southwestern defenses.";
        $gallupConvicted = [
            ['Juan Ochoa', 'Juan', 'Ochoa'],
            ['Manuel Avitia', 'Manuel', 'Avitia'],
            ['Leandro Velarde', 'Leandro', 'Velarde'],
        ];
        foreach ($gallupConvicted as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of the ten Gallup, New Mexico miners charged with first-degree murder after the 4 April 1935 eviction riot, and one of the three convicted of second-degree murder at Aztec on 17 October 1935 and sentenced to forty-five to sixty years at hard labor in the Santa Fe penitentiary. ".$gallupBase,
                'state' => 'New Mexico', 'gender' => 'Male', 'race' => 'Hispanic',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['National Miners Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with the murder of Sheriff Carmichael in the Gallup eviction riot.',
                    'convicted' => 'Convicted of second-degree murder, 17 October 1935',
                    'sentence' => 'Forty-five to sixty years at the Santa Fe penitentiary; later pardoned.',
                    'institution_name' => 'New Mexico State Penitentiary',
                    'institution_city' => 'Santa Fe', 'institution_state' => 'New Mexico',
                ]],
            ], ['arrest_date' => [1935, 4, 4]]);
        }
        $gallupAcquitted = [
            ['Joe Bartol', 'Joe', 'Bartol', "the president of the UMWA's Southwestern local, acquitted but re-held on charges of aiding an escape"],
            ['Agustin Calvillo', 'Agustin', 'Calvillo', "acquitted after months in jail"],
            ['Gregorio Correa', 'Gregorio', 'Correa', "acquitted after months in jail"],
            ['Victorio Correa', 'Victorio', 'Correa', "acquitted after months in jail"],
            ['Rafael Gomez', 'Rafael', 'Gomez', "acquitted after months in jail"],
            ['Willie Gonzales', 'Willie', 'Gonzales', "acquitted but re-held on charges of aiding an escape"],
            ['Serapio Sosa', 'Serapio', 'Sosa', "acquitted only to be seized and deported to Mexico"],
            ['Esiquio Navarro', 'Esiquio', 'Navarro', "a National Miners' Union leader jailed in the roundup"],
            ['Victor Campos', 'Victor', 'Campos', "jailed in the Gallup roundup"],
        ];
        foreach ($gallupAcquitted as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of the Gallup, New Mexico defendants of 1935 — {$who} — in the mass prosecution that followed the 4 April eviction riot. ".$gallupBase,
                'state' => 'New Mexico', 'gender' => 'Male', 'race' => 'Hispanic',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['National Miners Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged in the Gallup, New Mexico murder case after the eviction riot.',
                    'convicted' => 'Held for trial, 1935; acquitted or released',
                    'sentence' => 'Held facing the electric chair; acquitted.',
                    'institution_state' => 'New Mexico',
                ]],
            ], ['arrest_date' => [1935, 4, 4]]);
        }

        // ── BURLINGTON, N.C. DYNAMITE PLOT ───────────────────────────────
        $burlingtonBase = "After the September 1934 general textile strike, a dynamite charge at the E. M. Holt Plaid Mill in Burlington, North Carolina was used to frame union leaders of the Piedmont Textile Council; convicted in 1935 on the word of a paid informer, they drew sentences totalling twenty-seven years. The ILD carried the appeal.";
        $burlington = [
            ['John Anderson', 'John', 'Anderson', "the president of the UTW's Piedmont Textile Council, sentenced to eight to ten years at hard labor"],
            ['J. P. Hoggard', 'J. P.', 'Hoggard', "sentenced to four to six years"],
            ['Howard Overman', 'Howard', 'Overman', "sentenced to four to six years"],
            ['Florence Blalock', 'Florence', 'Blalock', "sentenced to four to six years"],
            ['Tom Canipe', 'Tom', 'Canipe', "sentenced to two years"],
            ['J. F. Harraway', 'J. F.', 'Harraway', "sentenced to two years"],
            ['Avery Kimrey', 'Avery', 'Kimrey', "sentenced to two years"],
            ['Jerry Furlough', 'Jerry', 'Furlough', "sentenced to twelve months on the roads"],
        ];
        foreach ($burlington as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of the Burlington, North Carolina textile strikers framed in the 1934–35 \"dynamite plot\" case, {$who}. ".$burlingtonBase,
                'state' => 'North Carolina', 'gender' => $first === 'Florence' ? 'Female' : 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['United Textile Workers'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Framed for dynamiting the E. M. Holt Plaid Mill after the 1934 textile strike.',
                    'convicted' => 'Convicted, 1935; appealed by the ILD',
                    'sentence' => 'Sentences totalling twenty-seven years for the group.',
                    'institution_city' => 'Burlington', 'institution_state' => 'North Carolina',
                ]],
            ], ['arrest_date' => [1934, null, null]]);
        }

        // ── ARKANSAS — SOUTHERN TENANT FARMERS' UNION ────────────────────
        $mk([
            'name' => 'Ward Rodgers', 'first_name' => 'Ward', 'last_name' => 'Rodgers',
            'description' => "Ward Rodgers was a young Methodist minister and organizer of the Southern Tenant Farmers' Union, convicted of \"anarchy\" at Marked Tree, Arkansas in January 1935 — fined $500 and given six months — for a speech to evicted sharecroppers, in the planter terror against the STFU.",
            'state' => 'Arkansas', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Southern Tenant Farmers Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted of anarchy for a speech to evicted sharecroppers at Marked Tree, Arkansas.',
                'convicted' => 'Convicted, January 1935',
                'sentence' => 'Six months and a $500 fine.',
                'institution_city' => 'Marked Tree', 'institution_state' => 'Arkansas',
            ]],
        ], ['arrest_date' => [1935, 1, null]]);
        $mk([
            'name' => 'Horace Bryan', 'first_name' => 'Horace', 'last_name' => 'Bryan',
            'description' => "Horace Bryan was a relief-workers' strike leader convicted of \"anarchy\" at Fort Smith, Arkansas in 1935 and sentenced to six months and a $500 fine, held in the Sebastian County jail.",
            'state' => 'Arkansas', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted of anarchy for leading a relief-workers\' strike at Fort Smith, Arkansas.',
                'convicted' => 'Convicted, 1935',
                'sentence' => 'Six months and a $500 fine, Sebastian County jail.',
                'institution_name' => 'Sebastian County Jail',
                'institution_city' => 'Fort Smith', 'institution_state' => 'Arkansas',
            ]],
        ], ['arrest_date' => [1935, null, null]]);

        // ── OKLAHOMA CITY FEDERAL SEDITION CASE ──────────────────────────
        $okcBase = "After a May 1934 demonstration of relief workers at FERA headquarters in Oklahoma City, eighteen were prosecuted in the first federal sedition case since 1922, charged with \"attempting to seize goods of the federal government\"; several drew federal prison terms.";
        $okc = [
            ['Wilma Conner', 'Wilma', 'Conner', 'Female', "a Black mother sentenced to a federal term served at the Alderson, West Virginia women's prison"],
            ['George Hopkins', 'George', 'Hopkins', 'Male', "sentenced to eighteen months and a $500 fine"],
            ['George Taylor', 'George', 'Taylor', 'Male', "jailed since May 1934 awaiting the federal trial"],
            ['George Wilson', 'George', 'Wilson', 'Male', "jailed since May 1934 awaiting the federal trial"],
            ['Robert Seymour', 'Robert', 'Seymour', 'Male', "jailed since May 1934 awaiting the federal trial"],
            ['Marshall Lakey', 'Marshall', 'Lakey', 'Male', "convicted in the federal sedition case"],
            ['Harry Snyder', 'Harry', 'Snyder', 'Male', "an Unemployment Council organizer sentenced to a year and a day at Leavenworth (inmate No. 46468)"],
        ];
        foreach ($okc as [$name, $first, $last, $gender, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} in the Oklahoma City federal sedition case of 1934–35. ".$okcBase,
                'state' => 'Oklahoma', 'gender' => $gender,
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['Unemployed Councils'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Prosecuted under the federal sedition law for a FERA relief demonstration at Oklahoma City.',
                    'convicted' => 'Convicted / held for trial, 1934–35',
                    'sentence' => 'Federal prison terms; defended by the ILD.',
                    'institution_state' => 'Oklahoma',
                ]],
            ], ['arrest_date' => [1934, 5, null]]);
        }

        // ── OREGON CRIMINAL SYNDICALISM (DeJonge satellites) ─────────────
        foreach ([
            ['Kyle Pugh', 'Kyle', 'Pugh', 'five years, convicted at Medford for circulating literature'],
            ['Sam Cluster', 'Sam', 'Cluster', 'one year at Portland'],
            ['Edward Denny', 'Edward', 'Denny', 'two years'],
        ] as [$name, $first, $last, $term]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was convicted under Oregon's criminal-syndicalism law in the 1934–35 drive that also produced the De Jonge case, sentenced to {$term}.",
                'state' => 'Oregon', 'gender' => 'Male',
                'ideologies' => ['Communism'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => "Convicted under Oregon's criminal-syndicalism law.",
                    'convicted' => 'Convicted, 1934–35',
                    'sentence' => ucfirst($term).'.',
                    'institution_state' => 'Oregon',
                ]],
            ], []);
        }

        // ── S.S. BREMEN ANTI-NAZI DEMONSTRATION ──────────────────────────
        $mk([
            'name' => 'Edward Drolette', 'first_name' => 'Edward', 'last_name' => 'Drolette',
            'description' => "Edward Drolette was one of the anti-fascists arrested in the 26 July 1935 demonstration aboard the German liner S.S. Bremen at its New York pier, in which the swastika was torn from the bow. Shot and wounded by a detective, he was charged with felonious assault; Magistrate Louis Brodsky's dismissal of the other defendants — calling the swastika a pirate flag — became an international incident.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Anti-fascism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with felonious assault in the S.S. Bremen anti-Nazi demonstration.',
                'convicted' => 'Held for trial, 1935',
                'sentence' => 'Held on the assault charge after being shot by a detective.',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['arrest_date' => [1935, 7, 26]]);
        $mk([
            'name' => 'Lawrence Simpson', 'first_name' => 'Lawrence', 'last_name' => 'Simpson',
            'description' => "Lawrence Simpson was an American seaman seized by Nazi secret police from the American ship Manhattan at Hamburg in 1935 for possessing anti-fascist literature, and held in a German prison and concentration camp for years while the ILD campaigned for his release.",
            'state' => 'Washington', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Anti-fascism'],
            'affiliation' => ['Marine Workers Industrial Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Seized by the Gestapo from an American ship at Hamburg for anti-fascist literature.',
                'convicted' => 'Held in Nazi prisons and camps from 1935',
                'sentence' => 'Imprisoned in Germany; freed after an international campaign.',
            ]],
        ], ['arrest_date' => [1935, null, null]]);

        // ── SF SHIP SCALERS FRAME-UP ─────────────────────────────────────
        foreach ([
            ['Archie Brown', 'Archie', 'Brown', "a young longshore and Ship Scalers Union leader"],
            ['Julio Canales', 'Julio', 'Canales', "a Ship Scalers Union member"],
            ['Francisco Jiminez', 'Francisco', 'Jiminez', "a Ship Scalers Union member"],
            ['Natalio Villi', 'Natalio', 'Villi', "a Ship Scalers Union member"],
        ] as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name}, {$who}, was framed on a murder charge in the San Francisco Ship Scalers Union case of 1935, tried in December 1935 as the shipowners moved against the militant waterfront unions.",
                'state' => 'California', 'gender' => 'Male',
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['Ship Scalers Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Framed on a murder charge in the San Francisco Ship Scalers case.',
                    'convicted' => 'Tried, December 1935',
                    'sentence' => 'Held on the murder frame-up; defended by the ILD.',
                    'institution_city' => 'San Francisco', 'institution_state' => 'California',
                ]],
            ], ['arrest_date' => [1935, null, null]]);
        }
        $mk([
            'name' => 'Louise Todd', 'first_name' => 'Louise', 'last_name' => 'Todd',
            'description' => "Louise Todd was a California Communist Party official sentenced in 1935 to one to fourteen years at the Tehachapi women's prison on a \"technicality\" over ballot-petition signatures — a prosecution aimed at the party's electoral work after the San Francisco general strike.",
            'state' => 'California', 'gender' => 'Female',
            'ideologies' => ['Communism'],
            'affiliation' => ['Communist Party USA'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted over Communist ballot-petition signatures in California.',
                'convicted' => 'Convicted, 1935',
                'sentence' => 'One to fourteen years at Tehachapi.',
                'institution_name' => 'Tehachapi State Prison',
                'institution_city' => 'Tehachapi', 'institution_state' => 'California',
            ]],
        ], ['incarceration_date' => [1935, null, null]]);

        // ── SEATTLE "VOICE OF ACTION" CRIMINAL LIBEL ─────────────────────
        foreach ([
            ['Lowell Wakefield', 'Lowell', 'Wakefield', 'the editor'],
            ['Emerson Daggett', 'Emerson', 'Daggett', 'a reporter'],
        ] as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name}, {$who} of the Seattle labor weekly Voice of Action, was charged with criminal libel in 1935 for the paper's exposures during the Northwest lumber strike.",
                'state' => 'Washington', 'gender' => 'Male',
                'ideologies' => ['Communism', 'Labor organizing'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with criminal libel over the Voice of Action\'s lumber-strike coverage.',
                    'convicted' => 'Prosecuted, 1935',
                    'sentence' => 'Held on the criminal-libel charge.',
                    'institution_city' => 'Seattle', 'institution_state' => 'Washington',
                ]],
            ], ['arrest_date' => [1935, null, null]]);
        }

        // ── 1935 DEPORTATION DRIVE ───────────────────────────────────────
        $deport = [
            ['Stella Petrosky', 'Stella', 'Petrosky', 'Female', "a Pennsylvania anthracite Unemployment Council leader and mother of eight American-born children, arrested at an eviction protest at Wilkes-Barre and put through deportation proceedings to Poland"],
            ['John Ujich', 'John', 'Ujich', 'Male', "a Tacoma, Washington relief protester ordered deported to fascist Italy, whose twenty-seven-month fight the ILD carried through the courts"],
            ['Jesus Pallares', 'Jesus', 'Pallares', 'Male', "the founder of the New Mexico coal miners' Liga Obrera de Habla Española, seized for deportation to Mexico for his organizing in the Gallup country"],
            ['Alfred Miller', 'Alfred', 'Miller', 'Male', "the editor of the Producers News at Plentywood, Montana, held for deportation to Nazi Germany"],
            ['Erich Becker', 'Erich', 'Becker', 'Male', "held for deportation to Nazi Germany after a Chicago consulate protest"],
            ['Carl Ohm', 'Carl', 'Ohm', 'Male', "held for deportation to Nazi Germany"],
            ['Walter Baer', 'Walter', 'Baer', 'Male', "held at Portland for deportation to Nazi Germany"],
            ['Fred Werrmann', 'Fred', 'Werrmann', 'Male', "held for deportation to Nazi Germany"],
            ['Harry Loftus', 'Harry', 'Loftus', 'Male', "held at Portland for deportation to Greece"],
            ['Christ Popoff', 'Christ', 'Popoff', 'Male', "one of the Ellis Island deportation detainees held in solitary confinement in 1935"],
            ['Ray Carlson', 'Ray', 'Carlson', 'Male', "one of the Ellis Island deportation detainees held in solitary confinement in 1935"],
            ['Paul Kettunen', 'Paul', 'Kettunen', 'Male', "one of the Ellis Island deportation detainees held in solitary confinement in 1935"],
            ['Oscar Mannisto', 'Oscar', 'Mannisto', 'Male', "one of the Ellis Island deportation detainees held in solitary confinement in 1935"],
            ['Patrick Francis Kevin', 'Patrick Francis', 'Kevin', 'Male', "held ten months at the San Pedro immigration station"],
        ];
        foreach ($deport as [$name, $first, $last, $gender, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who}, in the deportation drive of 1935 against foreign-born labor militants and anti-fascists.",
                'gender' => $gender,
                'ideologies' => ['Labor organizing'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Held for deportation for labor or anti-fascist activity, 1935.',
                    'convicted' => 'Held for deportation, 1935',
                    'sentence' => 'Held pending deportation; defended by the ILD.',
                ]],
            ], ['arrest_date' => [1935, null, null]]);
        }

        // ── ALABAMA — SHARE CROPPERS UNION / BIRMINGHAM TERROR ───────────
        $mk([
            'name' => 'Walter Brown', 'first_name' => 'Walter', 'last_name' => 'Brown',
            'description' => "Walter Brown was a Black worker framed on a \"rape\" charge in the 1935 Birmingham, Alabama terror against the Share Croppers Union and given twenty years.",
            'state' => 'Alabama', 'gender' => 'Male', 'race' => 'Black',
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed on a rape charge in the Birmingham terror.',
                'convicted' => 'Convicted, 1935',
                'sentence' => 'Twenty years.',
                'institution_city' => 'Birmingham', 'institution_state' => 'Alabama',
            ]],
        ], []);
        $mk([
            'name' => 'Willie Witcher', 'first_name' => 'Willie', 'last_name' => 'Witcher',
            'description' => "Willie Witcher was a Share Croppers Union striker in Lowndes County, Alabama, shot five times in the 1935 cotton-choppers' strike and then jailed for twenty-seven days while wounded.",
            'state' => 'Alabama', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Share Croppers Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed after being shot in the Lowndes County cotton-choppers\' strike.',
                'convicted' => 'Held twenty-seven days, 1935',
                'sentence' => 'Jailed while wounded.',
                'institution_state' => 'Alabama',
            ]],
        ], ['arrest_date' => [1935, null, null]]);
        $mk([
            'name' => 'Ed Sears', 'first_name' => 'Ed', 'last_name' => 'Sears',
            'description' => "Ed Sears was sentenced to ten months in Birmingham, Alabama in 1935 for possessing ILD pamphlets, under the city's anti-radical ordinances.",
            'state' => 'Alabama', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Sentenced for possessing ILD literature at Birmingham.',
                'convicted' => 'Convicted, 1935',
                'sentence' => 'Ten months.',
                'institution_city' => 'Birmingham', 'institution_state' => 'Alabama',
            ]],
        ], []);
        $mk([
            'name' => 'Pete Turney', 'first_name' => 'Pete', 'last_name' => 'Turney',
            'description' => "Pete Turney was jailed in the Birmingham, Alabama anti-radical drive of 1935, one of the workers held under the city ordinances against Communist literature and meetings.",
            'state' => 'Alabama', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed in the Birmingham anti-radical drive.',
                'convicted' => 'Held, 1935',
                'sentence' => 'Jailed under the Birmingham ordinances.',
                'institution_city' => 'Birmingham', 'institution_state' => 'Alabama',
            ]],
        ], []);

        // ── LEGAL-LYNCHING / FRAME-UP SINGLES ────────────────────────────
        $mk([
            'name' => 'Clide Allen', 'first_name' => 'Clide', 'last_name' => 'Allen',
            'description' => "Clide Allen was an unemployed Black worker of Brooklyn sentenced to thirty-five years in 1935 on a fabricated rape charge in the \"Hammer Man\" frame-up, a case the ILD fought as a northern legal lynching.",
            'state' => 'New York', 'gender' => 'Male', 'race' => 'Black',
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted on a fabricated rape charge in the Brooklyn "Hammer Man" case.',
                'convicted' => 'Convicted, 1935',
                'sentence' => 'Thirty-five years.',
                'institution_city' => 'Brooklyn', 'institution_state' => 'New York',
            ]],
        ], []);
        $mk([
            'name' => 'Henry Teal', 'first_name' => 'Henry', 'last_name' => 'Teal',
            'description' => "Henry Teal was sentenced to fifty years in Texas in a 1934–35 frame-up arising from the killing of a CWA relief-work foreman, a case the ILD listed among the era's worst railroadings.",
            'state' => 'Texas', 'gender' => 'Male',
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed in the killing of a CWA relief foreman in Texas.',
                'convicted' => 'Convicted, 1934–35',
                'sentence' => 'Fifty years.',
                'institution_state' => 'Texas',
            ]],
        ], []);
        $mk([
            'name' => 'O. G. Brown', 'first_name' => 'O. G.', 'last_name' => 'Brown',
            'description' => "O. G. Brown was a Black Mississippian sentenced to death for a theft of $1.85 — one of the legal-lynching cases the ILD publicized in 1935.",
            'state' => 'Mississippi', 'gender' => 'Male', 'race' => 'Black',
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Sentenced to death over a $1.85 theft in Mississippi.',
                'convicted' => 'Sentenced to death, 1935',
                'sentence' => 'Death; taken up by the ILD.',
                'institution_state' => 'Mississippi',
            ]],
        ], []);
        $mk([
            'name' => 'E. K. Harris', 'first_name' => 'E. K.', 'last_name' => 'Harris',
            'description' => "E. K. Harris was a Black defendant convicted of \"rape\" in eight minutes at Shelbyville, Tennessee in 1934 while a lynch mob burned the courthouse — a legal-lynching case carried by the ILD.",
            'state' => 'Tennessee', 'gender' => 'Male', 'race' => 'Black',
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted of rape in an eight-minute trial amid a lynch mob at Shelbyville, Tennessee.',
                'convicted' => 'Convicted, 1934',
                'sentence' => 'Death sentence; fought by the ILD.',
                'institution_city' => 'Shelbyville', 'institution_state' => 'Tennessee',
            ]],
        ], []);
        $mk([
            'name' => 'Ernest Mullins', 'first_name' => 'Ernest', 'last_name' => 'Mullins',
            'description' => "Ernest Mullins was a West Virginia miner serving ninety-nine years at Moundsville on a murder frame-up from the 1931 miners' strike, listed among the ILD's \"neediest cases\" of 1935.",
            'state' => 'West Virginia', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['National Miners Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed for murder in the 1931 West Virginia miners\' strike.',
                'convicted' => 'Convicted, 1931',
                'sentence' => 'Ninety-nine years at Moundsville.',
                'institution_name' => 'West Virginia Penitentiary',
                'institution_city' => 'Moundsville', 'institution_state' => 'West Virginia',
            ]],
        ], []);

        // ── STRIKE & RELIEF CASES ────────────────────────────────────────
        $mk([
            'name' => 'Emma Brletic', 'first_name' => 'Emma', 'last_name' => 'Brletic',
            'description' => "Emma Brletic was a striker in the 1933 Ambridge, Pennsylvania steel strike, sentenced to two years and a $500 fine for \"inciting to riot,\" and released from the Beaver County prison in February 1935 after a defense campaign.",
            'state' => 'Pennsylvania', 'gender' => 'Female',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Steel and Metal Workers Industrial Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted of inciting to riot in the Ambridge steel strike.',
                'convicted' => 'Convicted, 1933; released February 1935',
                'sentence' => 'Two years and a $500 fine; released after four and a half months.',
                'institution_name' => 'Beaver County Prison',
                'institution_state' => 'Pennsylvania',
            ]],
        ], ['release_date' => [1935, 2, null]]);
        foreach ([
            ['Odel Huey', 'Odel', 'Huey'],
            ['A. F. Ashe', 'A. F.', 'Ashe'],
            ['A. G. Graham', 'A. G.', 'Graham'],
            ['J. H. Stephenson', 'J. H.', 'Stephenson'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a United Textile Workers striker at the Eton Mill in Shelby, North Carolina, charged with \"inciting to riot\" in the 1935 aftermath of the general textile strike.",
                'state' => 'North Carolina', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['United Textile Workers'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with inciting to riot in the Shelby, North Carolina textile strike.',
                    'convicted' => 'Held for trial, 1935',
                    'sentence' => 'Held on the riot charge.',
                    'institution_city' => 'Shelby', 'institution_state' => 'North Carolina',
                ]],
            ], ['arrest_date' => [1935, null, null]]);
        }
        $mk([
            'name' => 'Fred McMahan', 'first_name' => 'Fred', 'last_name' => 'McMahan',
            'description' => "Fred McMahan was a Gastonia-area United Textile Workers man sentenced to eighteen months on the Union County chain gang at Monroe, North Carolina for textile-strike activity.",
            'state' => 'North Carolina', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['United Textile Workers'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed for textile-strike activity in North Carolina.',
                'convicted' => 'Convicted, 1934–35',
                'sentence' => 'Eighteen months on the Union County chain gang.',
                'institution_city' => 'Monroe', 'institution_state' => 'North Carolina',
            ]],
        ], []);
        foreach ([
            ['James Workman', 'James', 'Workman', "an AFL gold miner of the Jackson, California mother-lode strike, convicted and then granted a new trial"],
            ['Alfo Canales', 'Alfo', 'Canales', "a striker of the Amador County, California gold fields sentenced to one to five years at San Quentin"],
        ] as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} in the 1935 Amador County gold-mine strike, where mass raids jailed scores of strikers.",
                'state' => 'California', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed in the Amador County, California gold-mine strike.',
                    'convicted' => 'Convicted, 1935',
                    'sentence' => 'Imprisoned; defended by the ILD.',
                    'institution_state' => 'California',
                ]],
            ], ['arrest_date' => [1935, null, null]]);
        }
        $mk([
            'name' => 'Gene Corish', 'first_name' => 'Gene', 'last_name' => 'Corish',
            'description' => "Gene Corish was convicted in the Denver \"Bloody Tuesday\" FERA relief-strike riot case of October 1934 — charges included \"rescuing a prisoner\" — and later pardoned after an ILD campaign.",
            'state' => 'Colorado', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['Unemployed Councils'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted in the Denver "Bloody Tuesday" relief-strike riot case.',
                'convicted' => 'Convicted, 1935; later pardoned',
                'sentence' => 'Imprisoned; pardoned after a defense campaign.',
                'institution_city' => 'Denver', 'institution_state' => 'Colorado',
            ]],
        ], ['arrest_date' => [1934, 10, 30]]);
        $mk([
            'name' => 'Harold Hendricks', 'first_name' => 'Harold', 'last_name' => 'Hendricks',
            'description' => "Harold Hendricks was sentenced to two years in Los Angeles for demanding relief, one of the \"June 1st victims\" of 1935 held in the Lincoln Heights jail.",
            'state' => 'California', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'affiliation' => ['Unemployed Councils'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Sentenced for a relief demonstration in Los Angeles.',
                'convicted' => 'Convicted, 1935',
                'sentence' => 'Two years.',
                'institution_name' => 'Lincoln Heights Jail',
                'institution_city' => 'Los Angeles', 'institution_state' => 'California',
            ]],
        ], []);
        $mk([
            'name' => 'Charles Krumbein', 'first_name' => 'Charles', 'last_name' => 'Krumbein',
            'description' => "Charles Krumbein, the New York district organizer of the Communist Party, was imprisoned at the Lewisburg federal penitentiary (No. 32739) in 1935 on a passport-fraud conviction, with parole denied — a prosecution the ILD treated as political.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'affiliation' => ['Communist Party USA'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Imprisoned on a passport-fraud conviction.',
                'convicted' => 'Convicted, 1934–35',
                'sentence' => 'Eighteen months at Lewisburg federal penitentiary.',
                'institution_name' => 'Lewisburg Federal Penitentiary',
                'institution_city' => 'Lewisburg', 'institution_state' => 'Pennsylvania',
            ]],
        ], []);

        // ── COHASSET CCC CAMP (Chico, California) ────────────────────────
        foreach ([
            ['Augustus Swift', 'Augustus', 'Swift'],
            ['Cornelius Smith', 'Cornelius', 'Smith'],
            ['John Boyd', 'John', 'Boyd'],
            ['Donald Johnson', 'Donald', 'Johnson'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of four Black Civilian Conservation Corps youths of the Cohasset camp near Chico, California arrested in 1935 for \"assault with a deadly weapon\" after defending themselves from a racist attack; the ILD defense reduced the case to suspended sentences.",
                'state' => 'California', 'gender' => 'Male', 'race' => 'Black',
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with assault after self-defense against a racist attack at a CCC camp.',
                    'convicted' => 'Suspended sentences, 1935',
                    'sentence' => 'Suspended sentences after ILD defense.',
                    'institution_city' => 'Chico', 'institution_state' => 'California',
                ]],
            ], ['arrest_date' => [1935, null, null]]);
        }

        // ── MISC INDIVIDUALS ─────────────────────────────────────────────
        $mk([
            'name' => 'Phil Frankfeld', 'first_name' => 'Phil', 'last_name' => 'Frankfeld',
            'description' => "Phil Frankfeld was a Western Pennsylvania unemployed-movement leader sentenced to two to four years and held in the Blawnox workhouse for relief-demonstration activity in Pittsburgh.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['Unemployed Councils'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed for unemployed-movement activity at Pittsburgh.',
                'convicted' => 'Convicted, 1934–35',
                'sentence' => 'Two to four years, Allegheny County Workhouse.',
                'institution_name' => 'Allegheny County Workhouse',
                'institution_city' => 'Blawnox', 'institution_state' => 'Pennsylvania',
            ]],
        ], []);
        $mk([
            'name' => 'David Kaplan', 'first_name' => 'David', 'last_name' => 'Kaplan',
            'description' => "David Kaplan was jailed thirty-six days in the 1935 Bridgeport, Connecticut student-strike case, one of the youth free-speech prosecutions of the year.",
            'state' => 'Connecticut', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed in the Bridgeport student-strike case.',
                'convicted' => 'Held thirty-six days, 1935',
                'sentence' => 'Thirty-six days.',
                'institution_city' => 'Bridgeport', 'institution_state' => 'Connecticut',
            ]],
        ], []);
        $mk([
            'name' => 'Jack Carney', 'first_name' => 'Jack', 'last_name' => 'Carney',
            'description' => "Jack Carney was a class-war prisoner (No. 59755) writing to the ILD from Welfare Island, New York in 1935.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Held as a class-war prisoner on Welfare Island.',
                'convicted' => 'Imprisoned, 1935',
                'sentence' => 'Held on Welfare Island.',
                'institution_name' => 'Welfare Island',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], []);

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

        $this->info("\nDone. Processed {$added} of ".count($people)." 1935 Labor Defender prisoner(s).");

        return self::SUCCESS;
    }
}
