<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Batch 14 of the ILD Labor Defender mining, covering the whole 1934 volume
 * (Vol. X, Jan–Dec). 1934 was the year of the great strikes — the West Coast
 * maritime and San Francisco general strike, the Minneapolis Teamsters and
 * Toledo Auto-Lite battles, the general textile strike — and the vigilante and
 * criminal-syndicalism crackdowns that followed, above all the Sacramento
 * trial of the Cannery and Agricultural Workers' Industrial Union.
 *
 * This adds the clearly-attested NEW prisoners of 1934. Marquee cases:
 *  - the Sacramento criminal-syndicalism trial (CAWIU);
 *  - the Georgia insurrection-law textile defendants (the Herndon statute);
 *  - the San Francisco general-strike vigilante raids;
 *  - Dirk DeJonge of the landmark De Jonge v. Oregon;
 *  - the Toledo Auto-Lite, Minneapolis, Riverside and Seabrook Farms strikes,
 *    the Midwest farm-defense arrests, the anti-Nazi picketing cases, and the
 *    1934 deportation drive.
 *
 * Cases already in the database are skipped: Scottsboro, Mooney/Billings,
 * McNamara/Schmidt, Centralia, Imperial Valley, Angelo Herndon, the Atlanta
 * Six, Edith Berkman, Euel Lee, the Reeltown sharecroppers, the Colorado
 * beet-worker boys, Willie Peterson, Theodore Jordan, Herbert Benjamin, and
 * the batch-13 farm-defense and death-row names. The recurring honor-roll
 * rosters were mined but only their genuinely-new entries are added here.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddLaborDefender1934Cases extends Command
{
    protected $signature = 'prisoners:add-labor-defender-1934';

    protected $description = 'Add the 1934 Labor Defender class-war prisoners (Sacramento CAWIU trial, San Francisco general strike, Georgia insurrection defendants, 1934 strike wave)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── SACRAMENTO CRIMINAL SYNDICALISM CASE (CAWIU) ─────────────────
        $sacBase = "In the summer of 1934, after leading the great California agricultural strikes, the leadership of the Cannery and Agricultural Workers' Industrial Union was rounded up and eighteen were tried at Sacramento under the state's criminal-syndicalism law — the largest such prosecution of the decade, with defendants facing six to eighty-four years. Eight were convicted in 1935; the convictions were reversed on appeal in 1937.";
        $sacramento = [
            ['Pat Chambers', 'Pat', 'Chambers', 'Male', "a leader of the California cotton and cannery strikes"],
            ['Caroline Decker', 'Caroline', 'Decker', 'Female', "the secretary of the Cannery and Agricultural Workers' Industrial Union"],
            ['Martin Wilson', 'Martin', 'Wilson', 'Male', "a CAWIU organizer"],
            ['Nora Conklin', 'Nora', 'Conklin', 'Female', "a CAWIU organizer"],
            ['Albert Hougardy', 'Albert', 'Hougardy', 'Male', "a CAWIU organizer"],
            ['Jack Warnick', 'Jack', 'Warnick', 'Male', "a CAWIU organizer"],
            ['Jack Crane', 'Jack', 'Crane', 'Male', "a CAWIU organizer"],
            ['Lorine Norman', 'Lorine', 'Norman', 'Female', "a CAWIU organizer"],
            ['Arthur Mini', 'Arthur', 'Mini', 'Male', "a CAWIU organizer"],
            ['Fred Kirkwood', 'Fred', 'Kirkwood', 'Male', "a CAWIU organizer"],
            ['Lee Hung', 'Lee', 'Hung', 'Male', "a CAWIU organizer"],
            ['A. G. Ford', 'A. G.', 'Ford', 'Male', "a CAWIU organizer"],
            ['Luther Mincy', 'Luther', 'Mincy', 'Male', "a CAWIU organizer"],
            ['W. H. Huffine', 'W. H.', 'Huffine', 'Male', "a CAWIU organizer"],
            ['Harry Collentz', 'Harry', 'Collentz', 'Male', "a CAWIU organizer"],
            ['John Fisher', 'John', 'Fisher', 'Male', "a CAWIU organizer"],
            ['Mike Plesh', 'Mike', 'Plesh', 'Male', "a CAWIU organizer"],
        ];
        foreach ($sacramento as [$name, $first, $last, $gender, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who}, one of the eighteen indicted at Sacramento in July 1934 for criminal syndicalism after the California agricultural strikes. ".$sacBase,
                'state' => 'California', 'gender' => $gender,
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['Cannery and Agricultural Workers Industrial Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Indicted for criminal syndicalism at Sacramento for leading the California agricultural workers\' union.',
                    'convicted' => 'Tried for criminal syndicalism, 1934–35 (convictions reversed on appeal, 1937)',
                    'sentence' => 'Faced six to eighty-four years; convictions later reversed.',
                    'institution_city' => 'Sacramento', 'institution_state' => 'California',
                ]],
            ], ['arrest_date' => [1934, 7, null]]);
        }

        // ── GEORGIA INSURRECTION-LAW TEXTILE DEFENDANTS ──────────────────
        $gaBase = "In 1934 the state of Georgia used the same 1861 insurrection statute wielded against Angelo Herndon to indict organizers and textile strikers for \"circulating insurrectionary literature\" and advocating Black–white labor unity, holding several without bond in the DeKalb County jail.";
        $georgia = [
            ['Clarence Weaver', 'Clarence', 'Weaver', 'Male', "held without bond in the DeKalb County jail"],
            ['Nathan Yagol', 'Nathan', 'Yagol', 'Male', "held without bond in the DeKalb County jail"],
            ['Alexander Racolin', 'Alexander', 'Racolin', 'Male', "held without bond in the DeKalb County jail"],
            ['Fannie Aderhold', 'Fannie', 'Aderhold', 'Female', "indicted and held on $5,000 bond"],
            ['J. A. Moreland', 'J. A.', 'Moreland', 'Male', "indicted and held on $5,000 bond"],
            ['Julia Weaver', 'Julia', 'Weaver', 'Female', "indicted and held on $5,000 bond"],
            ['Lucille Lawrence', 'Lucille', 'Lawrence', 'Female', "indicted and held on $5,000 bond"],
            ['Annie Mae Leathers', 'Annie Mae', 'Leathers', 'Female', "a white Southern textile striker indicted for insurrection"],
            ['Leah Young', 'Leah', 'Young', 'Female', "a white Southern textile striker indicted for insurrection"],
        ];
        foreach ($georgia as [$name, $first, $last, $gender, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who}, charged under Georgia's insurrection law in 1934 — the same statute used against Angelo Herndon. ".$gaBase,
                'state' => 'Georgia', 'gender' => $gender,
                'ideologies' => ['Communism', 'Labor organizing'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Indicted under Georgia\'s insurrection law for radical and textile-strike activity.',
                    'convicted' => 'Held / indicted for insurrection, 1934',
                    'sentence' => 'Held facing the capital insurrection charge; defended by the ILD.',
                    'institution_state' => 'Georgia',
                ]],
            ], ['arrest_date' => [1934, null, null]]);
        }

        // ── SAN FRANCISCO GENERAL STRIKE — VIGILANTE RAIDS ───────────────
        $sfBase = "The San Francisco general strike of July 1934, growing out of the West Coast maritime walkout, was met by police and vigilante raids that swept up hundreds of radicals across California; more than 450 were arrested and many held or beaten without charge.";
        $sf = [
            ['Elaine Black', 'Elaine', 'Black', 'Female', "an ILD district organizer convicted of \"belonging to an illegal organization\" and sentenced to six months"],
            ['James Lacy', 'James', 'Lacy', 'Male', "arrested in a hall raid, beaten, and held three days before release without charge"],
            ['Carl Carlson', 'Carl', 'Carlson', 'Male', "a fifty-nine-year-old longshoreman arrested on the picket line, tied to a post and beaten handcuffed"],
            ['Archie Crawford', 'Archie', 'Crawford', 'Male', "a nineteen-year-old sentenced to thirty days and denied medical care"],
            ['Thomas Sharp', 'Thomas', 'Sharp', 'Male', "arrested without cause and beaten in jail, his leg broken"],
        ];
        foreach ($sf as [$name, $first, $last, $gender, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} in the San Francisco general-strike terror of July 1934. ".$sfBase,
                'state' => 'California', 'gender' => $gender,
                'ideologies' => ['Communism', 'Labor organizing'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Arrested in the vigilante and police raids during the San Francisco general strike.',
                    'convicted' => 'Held / convicted, 1934',
                    'sentence' => 'Held or jailed in the general-strike crackdown.',
                    'institution_city' => 'San Francisco', 'institution_state' => 'California',
                ]],
            ], ['arrest_date' => [1934, 7, null]]);
        }

        // ── DIRK DEJONGE — De Jonge v. Oregon ────────────────────────────
        $mk([
            'name' => 'Dirk DeJonge', 'first_name' => 'Dirk', 'last_name' => 'DeJonge',
            'description' => "Dirk DeJonge was a Communist arrested in Portland, Oregon in 1934 for speaking at a peaceful, publicly-advertised meeting protesting police shootings during a maritime strike, and convicted under the state's criminal-syndicalism law. His appeal produced De Jonge v. Oregon (1937), in which the U.S. Supreme Court held that the right of peaceable assembly is protected against the states by the Fourteenth Amendment — a landmark First Amendment decision.",
            'state' => 'Oregon', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'affiliation' => ['Communist Party USA'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Convicted under Oregon's criminal-syndicalism law for speaking at a peaceful protest meeting.",
                'convicted' => 'Convicted, 1934; conviction reversed in De Jonge v. Oregon (1937)',
                'sentence' => 'Seven years; conviction struck down by the Supreme Court.',
                'institution_city' => 'Portland', 'institution_state' => 'Oregon',
            ]],
        ], ['arrest_date' => [1934, 7, null]]);
        $mk([
            'name' => 'Don Cluster', 'first_name' => 'Don', 'last_name' => 'Cluster',
            'description' => "Don Cluster was arrested with Dirk DeJonge in Portland, Oregon in 1934 and prosecuted under the state's criminal-syndicalism law for the maritime-strike protest meeting.",
            'state' => 'Oregon', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Charged under Oregon's criminal-syndicalism law over the Portland protest meeting.",
                'convicted' => 'Prosecuted for criminal syndicalism, 1934',
                'sentence' => 'Held on the criminal-syndicalism charge.',
                'institution_city' => 'Portland', 'institution_state' => 'Oregon',
            ]],
        ], ['arrest_date' => [1934, 7, null]]);

        // ── TOLEDO AUTO-LITE STRIKE ──────────────────────────────────────
        foreach ([
            ['Louis Budenz', 'Louis', 'Budenz', "a strike leader"],
            ['Kenneth Osthimer', 'Kenneth', 'Osthimer', "a striker charged under the injunction"],
            ['Eddie Blakely', 'Eddie', 'Blakely', "a striker charged under the injunction"],
            ['Arthur Buchanan', 'Arthur', 'Buchanan', "a striker jailed in the Auto-Lite battle"],
        ] as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} in the Toledo Auto-Lite strike of May 1934, when thirty-one strikers were charged with violating Judge Stuart's anti-picketing injunction amid the battle with the National Guard.",
                'state' => 'Ohio', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with violating the anti-picketing injunction in the Toledo Auto-Lite strike.',
                    'convicted' => 'Held / charged, 1934',
                    'sentence' => 'Held in the Auto-Lite strike.',
                    'institution_city' => 'Toledo', 'institution_state' => 'Ohio',
                ]],
            ], ['arrest_date' => [1934, 5, null]]);
        }

        // ── RIVERSIDE COUNTY, CA FRUIT-PICKER STRIKE (CAWIU) ─────────────
        $riverside = [
            ['Salas', 'Salas', '18 months'],
            ['Ambrose', 'Ambrose', '18 months'],
            ['Winters', 'Winters', '15 months'],
            ['Stewart', 'Stewart', '15 months'],
            ['Rosenbaum', 'Rosenbaum', '9 months'],
            ['Ramirez', 'Ramirez', '3 months'],
            ['Guerrero', 'Guerrero', '3 months'],
        ];
        foreach ($riverside as [$name, $last, $term]) {
            $mk([
                'name' => $name, 'first_name' => $name, 'last_name' => $last,
                'description' => "{$name} was a Cannery and Agricultural Workers' Industrial Union organizer convicted in a Riverside County, California fruit-pickers' strike case in 1934 and sentenced to {$term}.",
                'state' => 'California', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['Cannery and Agricultural Workers Industrial Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Convicted in a Riverside County fruit-pickers\' strike case.',
                    'convicted' => 'Convicted, 1934',
                    'sentence' => "{$term} in jail.",
                    'institution_state' => 'California',
                ]],
            ], ['arrest_date' => [1934, null, null]]);
        }

        // ── MIDWEST FARM-DEFENSE ─────────────────────────────────────────
        $mk([
            'name' => 'Alfred Tiala', 'first_name' => 'Alfred', 'last_name' => 'Tiala',
            'description' => "Alfred Tiala was the national secretary of the United Farmers' League, sentenced to six months for \"obstructing the course of justice\" in the January 1934 anti-foreclosure resistance at Warsaw, Indiana.",
            'state' => 'Indiana', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['United Farmers League'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted of obstructing justice in an Indiana anti-foreclosure protest.',
                'convicted' => 'Convicted, 1934',
                'sentence' => 'Six months.',
                'institution_state' => 'Indiana',
            ]],
        ], ['incarceration_date' => [1934, 1, null]]);
        foreach ([['Jesse Hann', 'Jesse', 'Hann'], ['Milo Long', 'Milo', 'Long']] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a United Farmers' League member charged with \"obstructing the course of justice\" in the January 1934 anti-foreclosure resistance at Warsaw, Indiana.",
                'state' => 'Indiana', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['United Farmers League'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with obstructing justice in an Indiana anti-foreclosure protest.',
                    'convicted' => 'Held / charged, 1934',
                    'sentence' => 'Held in the farm-defense case.',
                    'institution_state' => 'Indiana',
                ]],
            ], ['arrest_date' => [1934, 1, null]]);
        }
        foreach ([
            ['Bert Hanson', 'Bert', 'Hanson'],
            ['Julius Walstadt', 'Julius', 'Walstadt'],
            ['William Rieck', 'William', 'Rieck'],
            ['Louis Rieck', 'Louis', 'Rieck'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of seventeen anti-eviction farmers charged with rioting — facing up to ten years — at Sisseton, South Dakota in 1934, part of the United Farmers' League resistance to foreclosures.",
                'state' => 'South Dakota', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['United Farmers League'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with rioting in an anti-eviction fight at Sisseton, South Dakota.',
                    'convicted' => 'Held facing ten years, 1934',
                    'sentence' => 'Held in the Sisseton anti-eviction case.',
                    'institution_city' => 'Sisseton', 'institution_state' => 'South Dakota',
                ]],
            ], ['arrest_date' => [1934, null, null]]);
        }

        // ── MICHIGAN CRIMINAL SYNDICALISM (Red Flag law) ─────────────────
        foreach ([
            ['E. F. Burman', 'E. F.', 'Burman', '4 to 8 years'],
            ['Unto Immonen', 'Unto', 'Immonen', '2 to 6 years'],
        ] as [$name, $first, $last, $term]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a Communist convicted under Michigan's criminal-syndicalism (\"red flag\") law and sentenced to {$term} at the state prison at Marquette, in a case arising in the Upper Peninsula around Eben Junction.",
                'state' => 'Michigan', 'gender' => 'Male',
                'ideologies' => ['Communism'],
                'affiliation' => ['Communist Party USA'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => "Convicted under Michigan's criminal-syndicalism law.",
                    'convicted' => 'Convicted, 1933–34',
                    'sentence' => "{$term} at the Marquette state prison.",
                    'institution_name' => 'Marquette State Prison',
                    'institution_city' => 'Marquette', 'institution_state' => 'Michigan',
                ]],
            ], []);
        }

        // ── MINNEAPOLIS RELIEF DEMONSTRATIONS ────────────────────────────
        foreach ([
            ['Leo Tuuri', 'Leo', 'Tuuri', '35 days'],
            ['Arthur Hazelton', 'Arthur', 'Hazelton', '90 days'],
            ['S. K. Davis', 'S. K.', 'Davis', '50 days'],
        ] as [$name, $first, $last, $term]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was sentenced to {$term} in a 1934 Minneapolis relief demonstration case.",
                'state' => 'Minnesota', 'gender' => 'Male',
                'ideologies' => ['Communism'],
                'affiliation' => ['Unemployed Councils'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed in a Minneapolis relief demonstration.',
                    'convicted' => 'Convicted, 1934',
                    'sentence' => "{$term} in jail.",
                    'institution_city' => 'Minneapolis', 'institution_state' => 'Minnesota',
                ]],
            ], ['arrest_date' => [1934, null, null]]);
        }

        // ── GALLUP, NEW MEXICO MARTIAL-LAW COAL STRIKE ───────────────────
        foreach ([
            ['Robert Roberts', 'Robert', 'Roberts', 'six months at Santa Fe'],
            ['George Kaplan', 'George', 'Kaplan', 'six months at Albuquerque'],
        ] as [$name, $first, $last, $term]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a strike leader court-martialed and jailed under the martial law imposed on the National Miners' Union coal strike at Gallup, New Mexico in 1933–34, sentenced to {$term}.",
                'state' => 'New Mexico', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['National Miners Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Court-martialed under martial law in the Gallup, New Mexico coal strike.',
                    'convicted' => 'Convicted, 1933–34',
                    'sentence' => ucfirst($term).'.',
                    'institution_state' => 'New Mexico',
                ]],
            ], ['arrest_date' => [1933, null, null]]);
        }

        // ── NEBRASKA / LOUP CITY ─────────────────────────────────────────
        $mk([
            'name' => 'Mother Bloor', 'first_name' => 'Ella', 'last_name' => 'Bloor', 'aka' => 'Mother Bloor',
            'description' => "Ella Reeve \"Mother\" Bloor, the veteran Communist organizer, was arrested at Loup City, Nebraska in 1934 during a poultry-workers' strike and, at the age of seventy-two, sentenced to thirty days and a $100 fine for \"inciting to riot.\"",
            'state' => 'Nebraska', 'gender' => 'Female',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['Communist Party USA'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with inciting to riot at a Loup City, Nebraska strike.',
                'convicted' => 'Convicted, 1934',
                'sentence' => 'Thirty days and a $100 fine.',
                'institution_city' => 'Loup City', 'institution_state' => 'Nebraska',
            ]],
        ], ['arrest_date' => [1934, null, null]]);
        foreach ([
            ['Harry McDonald', 'Harry', 'McDonald'],
            ['Carl Wickland', 'Carl', 'Wickland'],
            ['Floyd Booth', 'Floyd', 'Booth'],
            ['Bert Sells', 'Bert', 'Sells'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was arrested with Mother Bloor at the 1934 Loup City, Nebraska strike and sentenced to thirty days and a $100 fine for \"inciting to riot.\"",
                'state' => 'Nebraska', 'gender' => 'Male',
                'ideologies' => ['Communism', 'Labor organizing'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with inciting to riot at the Loup City, Nebraska strike.',
                    'convicted' => 'Convicted, 1934',
                    'sentence' => 'Thirty days and a $100 fine.',
                    'institution_city' => 'Loup City', 'institution_state' => 'Nebraska',
                ]],
            ], ['arrest_date' => [1934, null, null]]);
        }

        // ── HILLSBORO, ILLINOIS "TREASON" CASE (Progressive Miners) ──────
        foreach ([
            ['Frank Mucci', 'Frank', 'Mucci', "a militant young miner and \"red\" alderman of Taylor Springs"],
            ['Jan Wittenber', 'Jan', 'Wittenber', "a coal-field ILD organizer"],
        ] as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who}, one of fourteen Progressive Miners of America defendants charged under Illinois's sedition law with \"treason\" at Hillsboro in 1934, with bail set at some half a million dollars.",
                'state' => 'Illinois', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['Progressive Miners of America'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with treason under Illinois\'s sedition law at Hillsboro.',
                    'convicted' => 'Held on the treason charge, 1934',
                    'sentence' => 'Held under heavy bail; defended by the ILD.',
                    'institution_city' => 'Hillsboro', 'institution_state' => 'Illinois',
                ]],
            ], ['arrest_date' => [1934, null, null]]);
        }

        // ── DENVER "BLOODY TUESDAY" RELIEF STRIKE ────────────────────────
        foreach ([
            ['Henry Brown', 'Henry', 'Brown'],
            ['Ripley Gibson', 'Ripley', 'Gibson'],
            ['Floyd Bartlett', 'Floyd', 'Bartlett'],
            ['Pearl Bartlett', 'Pearl', 'Bartlett'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of sixteen workers arrested in the Denver \"Bloody Tuesday\" relief strike of 1934.",
                'state' => 'Colorado', 'gender' => $first === 'Pearl' ? 'Female' : 'Male',
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['Unemployed Councils'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Arrested in the Denver "Bloody Tuesday" relief strike.',
                    'convicted' => 'Held, 1934',
                    'sentence' => 'Held after the relief strike.',
                    'institution_city' => 'Denver', 'institution_state' => 'Colorado',
                ]],
            ], ['arrest_date' => [1934, null, null]]);
        }

        // ── SEABROOK FARMS STRIKE (CAWIU, New Jersey) ────────────────────
        foreach ([
            ['Tom Crawford', 'Tom', 'Crawford', 'Male'],
            ['Donald Henderson', 'Donald', 'Henderson', 'Male'],
            ['Elinor Henderson', 'Elinor', 'Henderson', 'Female'],
        ] as [$name, $first, $last, $gender]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a leader of the 1934 Seabrook Farms agricultural strike in New Jersey, arrested in the crackdown on the farm-workers' organizing drive.",
                'state' => 'New Jersey', 'gender' => $gender,
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['Cannery and Agricultural Workers Industrial Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Arrested in the Seabrook Farms strike, New Jersey.',
                    'convicted' => 'Held, 1934',
                    'sentence' => 'Held in the strike crackdown.',
                    'institution_state' => 'New Jersey',
                ]],
            ], ['arrest_date' => [1934, null, null]]);
        }

        // ── ANTI-NAZI PICKETING (Free Thaelmann) ─────────────────────────
        foreach ([
            ['Ben Gardner', 'Ben', 'Gardner', 'Philadelphia', 'sentenced to a year at the Holmesburg workhouse for picketing the German consulate'],
            ['James Wilson', 'James', 'Wilson', 'Philadelphia', 'sentenced to a year at the Holmesburg workhouse for picketing the German consulate'],
            ['Otto Popovich', 'Otto', 'Popovich', 'New York', 'sentenced to six months for distributing Free-Thaelmann leaflets'],
        ] as [$name, $first, $last, $city, $what]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was an anti-fascist {$what} at {$city} in 1934, in the campaign to free the imprisoned German Communist leader Ernst Thaelmann.",
                'state' => $city === 'Philadelphia' ? 'Pennsylvania' : 'New York', 'gender' => 'Male',
                'ideologies' => ['Communism', 'Anti-fascism'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed for anti-Nazi picketing / leafleting.',
                    'convicted' => 'Convicted, 1934',
                    'sentence' => 'Jailed in the anti-Nazi campaign.',
                    'institution_city' => $city, 'institution_state' => $city === 'Philadelphia' ? 'Pennsylvania' : 'New York',
                ]],
            ], ['arrest_date' => [1934, null, null]]);
        }

        // ── 1934 DEPORTATION DRIVE ───────────────────────────────────────
        $deport = [
            ['Otto Richter', 'Otto', 'Richter', "a nineteen-year-old refugee from Nazi Germany arrested during the San Francisco general strike and held for deportation back to Germany"],
            ['Frederick Beijerbach', 'Frederick', 'Beijerbach', "a Heidelberg baker who fled Nazi Germany as a stowaway on the Leviathan and was held at Ellis Island for deportation"],
            ['Theodore Eggeling', 'Theodore', 'Eggeling', "a German seaman attacked by New York Nazis for an anti-Nazi pin and carried aboard ship for deportation, released on habeas corpus twenty minutes before sailing"],
            ['Maximo Penaherrera', 'Maximo', 'Penaherrera', "an Ecuadorean-born worker of thirty-two years' U.S. residence ordered deported"],
            ['Jose Sepulveda', 'Jose', 'Sepulveda', "a Chilean-born worker of sixteen years' U.S. residence ordered deported"],
        ];
        foreach ($deport as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} in the 1934 deportation drive.",
                'gender' => 'Male',
                'ideologies' => ['Anti-fascism'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Held for deportation for radical or anti-fascist activity, 1934.',
                    'convicted' => 'Held for deportation, 1934',
                    'sentence' => 'Held pending deportation; defended by the ILD.',
                ]],
            ], ['arrest_date' => [1934, null, null]]);
        }

        // ── INDIVIDUAL 1934 CASES ─────────────────────────────────────────
        $mk([
            'name' => 'James Victory', 'first_name' => 'James', 'last_name' => 'Victory',
            'description' => "James Victory was a Black worker of Detroit framed on a robbery charge in a 1934 case the ILD publicized as a \"northern Scottsboro\"; a mass defense won his acquittal.",
            'state' => 'Michigan', 'gender' => 'Male', 'race' => 'Black',
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed on a robbery charge at Detroit — a "northern Scottsboro" case.',
                'convicted' => 'Held for trial, 1934; acquitted',
                'sentence' => 'Held on the frame-up; acquitted after mass defense.',
                'institution_city' => 'Detroit', 'institution_state' => 'Michigan',
            ]],
        ], ['arrest_date' => [1934, null, null]]);
        foreach ([
            ['Nelson Pierce', 'Nelson', 'Pierce'],
            ['James Pierce', 'James', 'Pierce'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a coal miner of the Washburn, Tennessee area arrested in December 1933 and sentenced to a year in the state prison for strike activity.",
                'state' => 'Tennessee', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed for coal-strike activity in Tennessee.',
                    'convicted' => 'Convicted, 1933',
                    'sentence' => 'One year in the state prison.',
                    'institution_state' => 'Tennessee',
                ]],
            ], ['arrest_date' => [1933, 12, 5]]);
        }
        $mk([
            'name' => 'Emanuel Biddings', 'first_name' => 'Emanuel', 'last_name' => 'Biddings',
            'description' => "Emanuel Biddings was a Black sharecropper sentenced to death at Roxboro, North Carolina in 1934 in a case the ILD took up as a Southern frame-up after the NAACP declined it.",
            'state' => 'North Carolina', 'gender' => 'Male', 'race' => 'Black',
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Sentenced to death at Roxboro, North Carolina.',
                'convicted' => 'Sentenced to death, 1934',
                'sentence' => 'Death; taken up by the ILD.',
                'institution_city' => 'Roxboro', 'institution_state' => 'North Carolina',
            ]],
        ], []);
        $mk([
            'name' => 'Will Sanders', 'first_name' => 'Will', 'last_name' => 'Sanders',
            'description' => "Will Sanders was a sixteen-year-old Black youth sentenced to death in South Carolina in 1934, one of the Southern legal-lynching cases the ILD fought.",
            'state' => 'South Carolina', 'gender' => 'Male', 'race' => 'Black',
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Sentenced to death in South Carolina at sixteen.',
                'convicted' => 'Sentenced to death, 1934',
                'sentence' => 'Death; taken up by the ILD.',
                'institution_state' => 'South Carolina',
            ]],
        ], []);
        $mk([
            'name' => 'Ike Robinson', 'first_name' => 'Ike', 'last_name' => 'Robinson',
            'description' => "Ike Robinson was an American Federation of Labor organizer, one of ten arrested at Russellville, Alabama during the September 1934 general textile strike and warned to leave the state.",
            'state' => 'Alabama', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['United Textile Workers'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested in the 1934 general textile strike at Russellville, Alabama.',
                'convicted' => 'Held, 1934',
                'sentence' => 'Held in the textile-strike arrests.',
                'institution_city' => 'Russellville', 'institution_state' => 'Alabama',
            ]],
        ], ['arrest_date' => [1934, 9, null]]);
        $mk([
            'name' => 'Bill Gebert', 'first_name' => 'Bill', 'last_name' => 'Gebert',
            'description' => "Bill Gebert was a Chicago Communist labor leader under a criminal-syndicalism indictment in 1934 for his organizing among the city's workers.",
            'state' => 'Illinois', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['Communist Party USA'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Under a criminal-syndicalism indictment at Chicago.',
                'convicted' => 'Indicted, 1934',
                'sentence' => 'Held under the criminal-syndicalism indictment.',
                'institution_city' => 'Chicago', 'institution_state' => 'Illinois',
            ]],
        ], ['arrest_date' => [1934, null, null]]);

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

        $this->info("\nDone. Processed {$added} of ".count($people)." 1934 Labor Defender prisoner(s).");

        return self::SUCCESS;
    }
}
