<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Batch 10 of the ILD Labor Defender mining, covering the whole 1930 volume
 * (Vol. V, Jan–Dec). 1930 was the depression's first full year and the ILD's
 * caseload exploded: the 6 March "International Unemployment Day" mass arrests,
 * a nationwide sedition/criminal-syndicalism wave, and two landmark death- or
 * decades-long cases — the Atlanta "incite insurrection" prosecution and the
 * Imperial Valley criminal-syndicalism convictions.
 *
 * This command adds the clearly-attested NEW prisoners of 1930 not already
 * recorded in earlier batches. Cases already in the database are intentionally
 * skipped: the Gastonia Seven and Stromberg/Yucaipa (batch 8); Accorsi, Anna
 * Burlak, Shifrin, Betty Gannett, Lawrence Allen, Robert Anderson, John M.
 * Lynch, Mario Giletti, Teddy Jackoski, Stephan Graham, John Morgan (batch 9);
 * the criminal-syndicalism roster incl. Cornelison, Merritt, Godlasky,
 * Venturato (batch 5); and the 1928-roster names Muselin, Resetar, Zima,
 * Corbishley, Madsen, Pesce, Chessman, Mendola, Bonita, Harry Eisman. The
 * famous frame-ups (Mooney/Billings, Centralia IWW, McNamara/Schmidt, Joe
 * Hill, Wesley Everest) are likewise left to their existing entries.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard below skips anyone already recorded.
 */
final class AddLaborDefender1930Cases extends Command
{
    protected $signature = 'prisoners:add-labor-defender-1930';

    protected $description = 'Add the 1930 Labor Defender class-war prisoners (Atlanta insurrection, Imperial Valley CS, March 6 unemployment arrests, sedition wave)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── ATLANTA INSURRECTION SIX ──────────────────────────────────────
        // Charged under Georgia's 1861 insurrection statute — a capital
        // offense — for organizing Black and white workers and leading
        // unemployment protests in Atlanta; held in Fulton Tower Prison.
        // (Anna Burlak, the sixth, is already in the database.)
        $atlantaBase = "In the spring of 1930 the state of Georgia revived an 1861 anti-slave-insurrection statute to charge a group of Communist and labor organizers with \"attempting to incite insurrection\" — a capital offense — for organizing Black and white workers into the same unions and leading demonstrations of Atlanta's unemployed. Held without bail in the Fulton Tower Prison and threatened with the electric chair, the \"Atlanta Six\" became one of the ILD's signature free-speech defenses; the insurrection charges were eventually beaten.";
        $atlanta = [
            ['M. H. Powers', 'M. H.', 'Powers', 'Male', "M. H. Powers, a Trade Union Unity League iron-workers' organizer and Communist district organizer from St. Paul, Minnesota, was jailed in March 1930 for leaflets and a mass meeting of the unemployed and held on the insurrection charge in a death cell."],
            ['Joe Carr', 'Joe', 'Carr', 'Male', "Joe Carr, a nineteen-year-old West Virginia coal miner and Young Communist League and National Miners' Union organizer, was placed in a death cell on \"Murderers' Row\" in the Fulton Tower Prison alongside condemned men while facing the electric chair."],
            ['Herbert Newton', 'Herbert', 'Newton', 'Male', "Herbert Newton, a Black national organizer of the American Negro Labor Congress born in Boston to a family formerly enslaved in Virginia, was held on the Atlanta insurrection charge; he had already been jailed at Stamford, Connecticut and Trenton, New Jersey for unemployment and May Day activity."],
            ['Mary Dalton', 'Mary', 'Dalton', 'Female', "Mary Dalton, a twenty-year-old National Textile Workers' Union organizer, spent six weeks in the Fulton Tower Prison on the Atlanta insurrection charge before being released on ILD bail."],
            ['Henry Storey', 'Henry', 'Storey', 'Male', "Henry Storey, a Black World War veteran and Atlanta metal- and print-shop worker who chaired the American Negro Labor Congress protest meeting at which the arrests were made, was held on the Atlanta insurrection charge."],
        ];
        foreach ($atlanta as [$name, $first, $last, $gender, $bio]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => $bio.' '.$atlantaBase,
                'state' => 'Georgia', 'gender' => $gender,
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['Communist Party USA'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with "attempting to incite insurrection" under Georgia\'s 1861 statute — a capital offense — for organizing Black and white workers and leading unemployment demonstrations in Atlanta.',
                    'convicted' => 'Held on the capital insurrection charge; charges ultimately defeated',
                    'sentence' => 'Held without bail in the Fulton Tower Prison facing the electric chair; released on ILD bail pending the fight against the charge.',
                    'institution_name' => 'Fulton Tower Prison',
                    'institution_city' => 'Atlanta', 'institution_state' => 'Georgia',
                ]],
            ], ['arrest_date' => [1930, 3, null]]);
        }

        // ── IMPERIAL VALLEY CRIMINAL SYNDICALISM ─────────────────────────
        // The April 1930 El Centro raid on the Agricultural Workers Industrial
        // League; nine leaders convicted under California's criminal-syndicalism
        // law and given 3-to-42 or 2-to-28 year terms at San Quentin and Folsom.
        $ivBase = "On 14 April 1930 the Imperial County sheriff raided a meeting of the Agricultural Workers Industrial League — the Trade Union Unity League's farm-labor union — at El Centro, California, arresting about a hundred workers organizing the valley's cantaloupe and lettuce fields. Nine leaders were convicted under California's criminal-syndicalism law, without any overt act of violence, and sent to state prison; the case became one of the ILD's major criminal-syndicalism appeals.";
        $iv = [
            ['Carl Sklar', 'Carl', 'Sklar', '3 to 42 years', 'Folsom State Prison', 'Represa', "a Los Angeles Communist Party section organizer"],
            ['Tetsuji Horiuchi', 'Tetsuji', 'Horiuchi', '3 to 42 years', 'Folsom State Prison', 'Represa', "a Japanese-born Trade Union Unity League organizer"],
            ['Oscar Erickson', 'Oscar', 'Erickson', '3 to 42 years', 'San Quentin State Prison', 'San Quentin', "the national secretary of the Agricultural Workers Industrial League and a native Californian"],
            ['Lawrence Emery', 'Lawrence', 'Emery', '3 to 42 years', 'San Quentin State Prison', 'San Quentin', "a Marine Workers Industrial Union organizer"],
            ['Frank Spector', 'Frank', 'Spector', '3 to 42 years', 'San Quentin State Prison', 'San Quentin', "the ILD's Los Angeles district organizer, who wrote the pamphlet \"The Story of the Imperial Valley\" from his cell (prison No. 48688)"],
            ['Danny Roxas', 'Danny', 'Roxas', '2 to 28 years', 'San Quentin State Prison', 'San Quentin', "a Filipino agricultural worker and secretary of the Agricultural Workers Industrial League in the Imperial Valley"],
            ['Eduardo Herrera', 'Eduardo', 'Herrera', '2 to 28 years', 'San Quentin State Prison', 'San Quentin', "a Mexican agricultural worker who had first been held for deportation before being sent to prison"],
            ['Braulio Orosco', 'Braulio', 'Orosco', '2 to 28 years', 'San Quentin State Prison', 'San Quentin', "a Mexican agricultural worker who had first been held for deportation before being sent to prison"],
        ];
        foreach ($iv as [$name, $first, $last, $term, $inst, $city, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name}, {$who}, was one of the nine leaders convicted under California's criminal-syndicalism law after the 14 April 1930 raid on the Agricultural Workers Industrial League at El Centro and sentenced to {$term}. ".$ivBase,
                'state' => 'California', 'gender' => 'Male',
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['Agricultural Workers Industrial League', 'Trade Union Unity League'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Convicted under California\'s criminal-syndicalism law for organizing the Agricultural Workers Industrial League among Imperial Valley farm workers.',
                    'convicted' => 'Convicted of criminal syndicalism, 1930',
                    'sentence' => "{$term} at {$inst}.",
                    'institution_name' => $inst,
                    'institution_city' => $city, 'institution_state' => 'California',
                ]],
            ], ['arrest_date' => [1930, 4, 14]]);
        }

        // ── OHIO CRIMINAL SYNDICALISM (Martins Ferry / Belmont County) ────
        $ohioBase = "In 1929–30 Belmont County, Ohio prosecuted National Miners' Union and Trade Union Unity League organizers under the state's criminal-syndicalism law for distributing shop papers and speaking at mill and mine gates around Martins Ferry; the convictions carried five-to-ten-year terms and were appealed by the ILD.";
        $ohio = [
            ['Charles Guynn', 'Charles', 'Guynn', 'Male', "the national secretary of the National Miners' Union", '10 years'],
            ['Tom Johnson', 'Tom', 'Johnson', 'Male', "a Trade Union Unity League and Metal Workers' Industrial League organizer from Cleveland", '10 years'],
            ['Lil Andrews', 'Lil', 'Andrews', 'Female', "the Young Communist League district organizer for Ohio", 'an indeterminate term'],
        ];
        foreach ($ohio as [$name, $first, $last, $gender, $who, $term]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name}, {$who}, was convicted under Ohio's criminal-syndicalism law for speaking and distributing leaflets at Martins Ferry and sentenced to {$term}. ".$ohioBase,
                'state' => 'Ohio', 'gender' => $gender,
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['National Miners Union', 'Trade Union Unity League'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Convicted of criminal syndicalism for speaking and distributing leaflets to miners and mill workers at Martins Ferry, Ohio.',
                    'convicted' => 'Convicted of criminal syndicalism, 1929–30',
                    'sentence' => "{$term}; conviction appealed by the ILD.",
                    'institution_state' => 'Ohio',
                ]],
            ], ['arrest_date' => [1929, 8, 1]]);
        }

        // ── WOODLAWN / ALIQUIPPA PA (Flynn Sedition Act) — the fourth man ──
        $mk([
            'name' => 'Steve Bratich', 'first_name' => 'Steve', 'last_name' => 'Bratich',
            'description' => "Steve Bratich was the fourth defendant in the Woodlawn (Aliquippa), Pennsylvania sedition case, a group of Communist organizers of Jones & Laughlin steelworkers arrested in a \"red raid\" on Armistice Day, 11 November 1926. Tried under Pennsylvania's Flynn anti-sedition law, he was sentenced to two and a half years' hard labor at the Allegheny County Workhouse at Blawnox, alongside co-defendants Pete Muselin, Milan Resetar and Tom Zima. The U.S. Supreme Court refused to hear the appeal.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['Communist Party USA'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Convicted under Pennsylvania's Flynn anti-sedition law for organizing Jones & Laughlin steelworkers at Woodlawn (Aliquippa).",
                'convicted' => 'Convicted of sedition',
                'sentence' => "Two and a half years' hard labor at the Allegheny County Workhouse, Blawnox.",
                'institution_name' => 'Allegheny County Workhouse',
                'institution_city' => 'Blawnox', 'institution_state' => 'Pennsylvania',
            ]],
        ], ['arrest_date' => [1926, 11, 11]]);

        // ── CHESTER PA (Flynn Sedition Act) ──────────────────────────────
        $chester = [
            ['Thomas Holmes', 'Thomas', 'Holmes', "an eighteen-year-old Young Communist League district organizer at Chester", 'a maximum of three years in the State Industrial Reformatory at Huntingdon', 'State Industrial Reformatory', 'Huntingdon'],
            ['Ray Peltz', 'Ray', 'Peltz', "a Young Communist League organizer at Chester", 'one to twenty years and a $5,000 fine', 'Media County Jail', 'Media'],
        ];
        foreach ($chester as [$name, $first, $last, $who, $term, $inst, $city]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name}, {$who}, was arrested in January 1930 for leafleting the unemployed and convicted of \"sedition\" under Pennsylvania's Flynn Act at Media on 2 April 1930, drawing {$term}. Holmes was described as the only political prisoner in the Huntingdon reformatory.",
                'state' => 'Pennsylvania', 'gender' => 'Male',
                'ideologies' => ['Communism'],
                'affiliation' => ['Young Communist League'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => "Convicted of sedition under Pennsylvania's Flynn Act for distributing unemployment leaflets at Chester.",
                    'convicted' => 'Convicted of sedition, 2 April 1930',
                    'sentence' => ucfirst($term).'.',
                    'institution_name' => $inst,
                    'institution_city' => $city, 'institution_state' => 'Pennsylvania',
                ]],
            ], ['arrest_date' => [1930, 1, 15]]);
        }

        // ── ILLINOIS MINERS (National Miners' Union, Taylorville strike) ──
        $ilBase = "During the 1929–30 Illinois coal strike, the National Miners' Union led rank-and-file miners against both the operators and the Lewis machine of the United Mine Workers. Militia and deputies jailed strike leaders wholesale around Taylorville and the Franklin County fields.";
        $ilMiners = [
            ['Freeman Thompson', 'Freeman', 'Thompson', "a leader of the Illinois miners placed under military arrest at Taylorville"],
            ['George Voyzey', 'George', 'Voyzey', "the Illinois state president of the National Miners' Union, jailed under numerous charges and heavy bond"],
            ['Dan Slinger', 'Dan', 'Slinger', "a militant National Miners' Union miner of Eldorado, Illinois"],
            ['John Lapshansky', 'John', 'Lapshansky', "a National Miners' Union leader of Nokomis, Illinois"],
            ['Charlie Mammen', 'Charlie', 'Mammen', "a Taylorville miner arrested with his wife after being beaten in a police raid on his home on his return from the ILD convention"],
        ];
        foreach ($ilMiners as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} during the Illinois coal strike of 1929–30. ".$ilBase,
                'state' => 'Illinois', 'gender' => 'Male',
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['National Miners Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed for National Miners\' Union strike activity during the Illinois coal strike.',
                    'convicted' => 'Held on strike-related charges, 1929–30',
                    'sentence' => 'Jailed during the strike; released on ILD defense.',
                    'institution_state' => 'Illinois',
                ]],
            ], ['arrest_date' => [1929, 12, 9]]);
        }

        // ── MARCH 6, 1930 — NYC UNEMPLOYED DELEGATION ────────────────────
        $march6Base = "On 6 March 1930 — International Unemployment Day — the Communist-led Unemployed Councils drew huge crowds into the streets to demand \"work or wages.\" In New York a delegation that tried to present demands at City Hall was arrested on the steps, held incommunicado in the Tombs, denied a jury, convicted of unlawful assembly, and sentenced in April 1930 to three-year terms served on the city's prison islands.";
        $march6 = [
            ['William Z. Foster', 'William Z.', 'Foster', "the secretary of the Trade Union Unity League and organizer of the demonstration"],
            ['Robert Minor', 'Robert', 'Minor', "the Daily Worker editor and cartoonist"],
            ['Israel Amter', 'Israel', 'Amter', "the New York district organizer of the Communist Party"],
            ['Harry Raymond', 'Harry', 'Raymond', "a young seaman and delegation member, held on Hart's Island"],
            ['Joseph Leston', 'Joseph', 'Leston', "a seaman and delegation member, given thirty days"],
        ];
        foreach ($march6 as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name}, {$who}, was one of the New York unemployed delegation jailed for the 6 March 1930 demonstration. ".$march6Base,
                'state' => 'New York', 'gender' => 'Male',
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['Trade Union Unity League', 'Communist Party USA'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with unlawful assembly (and threatened with felonious assault) for the 6 March 1930 unemployment demonstration at New York City Hall.',
                    'convicted' => 'Convicted of unlawful assembly, 1930',
                    'sentence' => 'Sentenced to a three-year term (Leston, thirty days); served on the New York prison islands.',
                    'institution_name' => 'New York City Penitentiary',
                    'institution_city' => 'New York', 'institution_state' => 'New York',
                ]],
            ], ['arrest_date' => [1930, 3, 6]]);
        }

        // ── MARCH 6 — MILWAUKEE ──────────────────────────────────────────
        $milwaukee = [
            ['Fred Bassett', 'Fred', 'Bassett', '1 year', "who was the Communist candidate for governor of Wisconsin while imprisoned"],
            ['Max Kagan', 'Max', 'Kagan', '6 months', ''],
            ['Leo Fisher', 'Leo', 'Fisher', '6 months', ''],
            ['John Perlich', 'John', 'Perlich', '6 months', ''],
            ['John Hilty', 'John', 'Hilty', '3 months', ''],
            ['William Felix', 'William', 'Felix', '3 months', ''],
            ['Oscar Bobby', 'Oscar', 'Bobby', '3 months', ''],
            ['Joe Carl', 'Joe', 'Carl', '3 months', ''],
            ['Sonia Mason', 'Sonia', 'Mason', '3 months', ''],
        ];
        foreach ($milwaukee as [$name, $first, $last, $term, $extra]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => trim("{$name} was one of the leaders of the 6 March 1930 unemployment demonstration in Milwaukee — a crowd estimated at 25,000 — sentenced to {$term} in the Milwaukee House of Correction under the city's Socialist administration. {$extra}"),
                'state' => 'Wisconsin', 'gender' => in_array($first, ['Sonia']) ? 'Female' : 'Male',
                'ideologies' => ['Communism'],
                'affiliation' => ['Communist Party USA'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed for leading the 6 March 1930 unemployment demonstration in Milwaukee.',
                    'convicted' => 'Convicted, 1930',
                    'sentence' => "{$term} in the Milwaukee House of Correction.",
                    'institution_name' => 'Milwaukee House of Correction',
                    'institution_city' => 'Milwaukee', 'institution_state' => 'Wisconsin',
                ]],
            ], ['arrest_date' => [1930, 3, 6]]);
        }

        // ── BUFFALO "WORK OR WAGES" ───────────────────────────────────────
        $buffalo = [
            ['Murry Melvin', 'Murry', 'Melvin', '100 days'],
            ['Arthur S. Harvey', 'Arthur S.', 'Harvey', '100 days (twice)'],
            ['Ruth Williams', 'Ruth', 'Williams', 'six months and a $50 fine'],
            ['Louis Murray', 'Louis', 'Murray', '100 days'],
            ['Jack Donald', 'Jack', 'Donald', '130 days'],
            ['Carl Larson', 'Carl', 'Larson', '150 days'],
            ['Fred Shearer', 'Fred', 'Shearer', '150 days'],
            ['Angelo Cappelo', 'Angelo', 'Cappelo', '100 days'],
        ];
        foreach ($buffalo as [$name, $first, $last, $term]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of the Buffalo, New York workers jailed in 1930 for demanding \"work or wages\" in the unemployment demonstrations, drawing a sentence of {$term}.",
                'state' => 'New York', 'gender' => $first === 'Ruth' ? 'Female' : 'Male',
                'ideologies' => ['Communism'],
                'affiliation' => ['Unemployed Councils'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed for unemployment demonstrations demanding "work or wages" at Buffalo, New York.',
                    'convicted' => 'Convicted, 1930',
                    'sentence' => ucfirst($term).' in jail.',
                    'institution_city' => 'Buffalo', 'institution_state' => 'New York',
                ]],
            ], ['arrest_date' => [1930, null, null]]);
        }

        // ── NEWARK NJ SEDITION (1918/1902 NJ sedition law) ───────────────
        $newark = [
            ['Dominick Flaiani', 'Dominick', 'Flaiani', "the Communist organizer for Newark, found guilty and given two years' probation plus ten days for contempt"],
            ['Dozier Will Graham', 'Dozier Will', 'Graham', "a Black worker and Communist candidate for U.S. Senator from New Jersey, given a suspended sentence"],
            ['Samuel D. Levine', 'Samuel D.', 'Levine', "a Communist candidate for the House, given two years' probation"],
            ['Edward Childs', 'Edward', 'Childs', "indicted and awaiting trial"],
            ['John Pado', 'John', 'Pado', "indicted and awaiting trial"],
            ['David Rosen', 'David', 'Rosen', "indicted and awaiting trial"],
            ['Morris Langer', 'Morris', 'Langer', "indicted and awaiting trial"],
            ['Albert Hedar', 'Albert', 'Hedar', "indicted and awaiting trial"],
            ['Joseph Lypsevitch', 'Joseph', 'Lypsevitch', "indicted and awaiting trial"],
        ];
        foreach ($newark as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of nine Newark, New Jersey workers arrested at unemployment meetings in February 1930 and indicted under New Jersey's sedition law — facing up to fifteen years — for demonstrating against unemployment. He was {$who}.",
                'state' => 'New Jersey', 'gender' => 'Male',
                'ideologies' => ['Communism'],
                'affiliation' => ['Communist Party USA'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => "Indicted under New Jersey's sedition law for demonstrating against unemployment at Newark.",
                    'convicted' => 'Indicted for sedition, 1930',
                    'sentence' => 'Faced up to fifteen years; outcomes ranged from probation to suspended sentences.',
                    'institution_state' => 'New Jersey',
                ]],
            ], ['arrest_date' => [1930, 2, 4]]);
        }

        // ── OAKLAND (USL Battery Co. / City Hall demo) ───────────────────
        $oakland = [
            ['John Mitigli', 'John', 'Mitigli', 'Male', '30 days'],
            ['Arvid Owens', 'Arvid', 'Owens', 'Male', '40 days'],
            ['Sam Barman', 'Sam', 'Barman', 'Male', '30 days'],
            ['Sonia Baltruin', 'Sonia', 'Baltruin', 'Female', '60 days'],
            ['Anna Robbins', 'Anna', 'Robbins', 'Female', '40 days'],
            ['Bessie Herman', 'Bessie', 'Herman', 'Female', 'a suspended six-month sentence'],
            ['Julia Wilde', 'Julia', 'Wilde', 'Female', '40 days'],
        ];
        foreach ($oakland as [$name, $first, $last, $gender, $term]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was arrested and clubbed leading a 1930 Oakland, California City Hall demonstration protesting police brutality against a Trade Union Unity League shop-gate meeting at the USL Battery Company, and sentenced to {$term}.",
                'state' => 'California', 'gender' => $gender,
                'ideologies' => ['Communism'],
                'affiliation' => ['Trade Union Unity League'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Arrested leading an Oakland City Hall demonstration against police brutality at the USL Battery Company.',
                    'convicted' => 'Convicted, 1930',
                    'sentence' => ucfirst($term).'.',
                    'institution_city' => 'Oakland', 'institution_state' => 'California',
                ]],
            ], ['arrest_date' => [1930, null, null]]);
        }

        // ── LOS ANGELES MAY DAY 1930 ─────────────────────────────────────
        foreach ([
            ['D. Fradkin', 'D.', 'Fradkin'],
            ['Carl Hummel', 'Carl', 'Hummel'],
            ['Martin Shapiro', 'Martin', 'Shapiro'],
            ['John Vilarino', 'John', 'Vilarino'],
            ['George Hoxie', 'George', 'Hoxie'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of the workers arrested at the Los Angeles May Day demonstration of 1 May 1930 — on charges of parading without a permit, battery and distributing handbills — and held in jail when eight of the eighteen arrested had their charges dropped.",
                'state' => 'California', 'gender' => 'Male',
                'ideologies' => ['Communism'],
                'affiliation' => ['Communist Party USA'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Arrested at the Los Angeles May Day demonstration of 1930 for parading without a permit, battery and distributing handbills.',
                    'convicted' => 'Held in the Los Angeles County Jail, 1930',
                    'sentence' => 'Jailed after the May Day demonstration.',
                    'institution_name' => 'Los Angeles County Jail',
                    'institution_city' => 'Los Angeles', 'institution_state' => 'California',
                ]],
            ], ['arrest_date' => [1930, 5, 1]]);
        }

        // ── LOS ANGELES ANTI-IMPERIALIST DEMO (Aug 1, 1930) ──────────────
        foreach ([
            ['Emma Cutler', 'Emma', 'Cutler'],
            ['Sarah Cutler', 'Sarah', 'Cutler'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name}, one of a mother and daughter, was sentenced to serve ninety days and pay a $500 fine at the Los Angeles County Jail for taking part in the 1 August 1930 anti-imperialist demonstration in Los Angeles.",
                'state' => 'California', 'gender' => 'Female',
                'ideologies' => ['Communism', 'Anti-imperialism'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Sentenced for participating in the 1 August 1930 anti-imperialist demonstration in Los Angeles.',
                    'convicted' => 'Convicted, 1930',
                    'sentence' => 'Ninety days and a $500 fine at the Los Angeles County Jail.',
                    'institution_name' => 'Los Angeles County Jail',
                    'institution_city' => 'Los Angeles', 'institution_state' => 'California',
                ]],
            ], ['arrest_date' => [1930, 8, 1]]);
        }

        // ── NEW ORLEANS MARINE WORKERS' LEAGUE SEDITION ──────────────────
        foreach ([
            ['Victor Aronson', 'Victor', 'Aronson', "an ILD organizer in New Orleans"],
            ['William J. Davids', 'William J.', 'Davids', "a Marine Workers' League seaman"],
            ['Leonard Brown', 'Leonard', 'Brown', "a Marine Workers' League seaman"],
        ] as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name}, {$who}, was arrested in simultaneous late-1929 raids on seamen's headquarters in New Orleans and San Pedro and charged with \"sedition\" for spreading the Labor Defender and union leaflets among sailors.",
                'state' => 'Louisiana', 'gender' => 'Male',
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['Marine Workers League'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with sedition for distributing the Labor Defender and union leaflets to seamen at New Orleans.',
                    'convicted' => 'Arrested for sedition, 1929–30',
                    'sentence' => 'Held for trial on the sedition charge.',
                    'institution_city' => 'New Orleans', 'institution_state' => 'Louisiana',
                ]],
            ], ['arrest_date' => [1929, 12, null]]);
        }

        // ── SOUTHERN CHAIN GANG (Marion & Chattanooga) ───────────────────
        $chain = [
            ['Dewey Martin', 'Dewey', 'Martin', 'North Carolina', "a National Textile Workers' Union organizer", 'seven months on the county chain gang', 'Marion, North Carolina'],
            ['George Saul', 'George', 'Saul', 'North Carolina', "a Southern ILD organizer", 'six months on the chain gang', 'Marion, North Carolina'],
            ['Gilbert Lewis', 'Gilbert', 'Lewis', 'Tennessee', "a Black Trade Union Unity League organizer", 'a chain-gang sentence', 'Chattanooga, Tennessee'],
        ];
        foreach ($chain as [$name, $first, $last, $state, $who, $term, $place]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name}, {$who}, was sentenced to {$term} at {$place} in 1930 for textile and labor organizing in the Southern mill campaign.",
                'state' => $state, 'gender' => 'Male',
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['National Textile Workers Union', 'Trade Union Unity League'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed and sentenced to the chain gang for textile-union organizing in the South.',
                    'convicted' => 'Sentenced to the chain gang, 1930',
                    'sentence' => ucfirst($term).'.',
                    'institution_state' => $state,
                ]],
            ], ['arrest_date' => [1930, null, null]]);
        }

        // ── DEPORTATION HOLDS ────────────────────────────────────────────
        $mk([
            'name' => 'Guido Serio', 'first_name' => 'Guido', 'last_name' => 'Serio',
            'description' => "Guido Serio was an Italian anti-fascist labor leader — a former national secretary of the Seamen's Union of Italy — and Communist Party organizer who bore seven stiletto wounds from Fascist attackers. Arrested at an Italian-language meeting in Erie, Pennsylvania in May 1930 and charged with sedition for protesting unemployment, he was held under $25,000 bail and ordered deported; refused a new trial, he was held on Ellis Island awaiting deportation to Mussolini's Italy.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Anti-fascism'],
            'affiliation' => ['Communist Party USA'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested for sedition at Erie, Pennsylvania and held for deportation to Fascist Italy.',
                'convicted' => 'Ordered deported, 1930',
                'sentence' => 'Held under $25,000 bail on Ellis Island awaiting deportation.',
                'institution_name' => 'Ellis Island',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['arrest_date' => [1930, 5, 11]]);
        $mk([
            'name' => 'Rade Radekovitch', 'first_name' => 'Rade', 'last_name' => 'Radekovitch',
            'description' => "Rade Radekovitch was a labor militant deported from Galveston, Texas on 12 September 1930 to Yugoslavia on a framed charge of \"illegal entry,\" arrested on his return after briefly crossing the Mexican border at Nogales, Arizona.",
            'state' => 'Texas', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Held for deportation on a framed "illegal entry" charge.',
                'convicted' => 'Deported to Yugoslavia, 12 September 1930',
                'sentence' => 'Deported from Galveston, Texas.',
                'institution_city' => 'Galveston', 'institution_state' => 'Texas',
            ]],
        ], ['release_date' => [1930, 9, 12]]);

        // ── BOSTON FRAME-UP (needle-trades picket) ───────────────────────
        $mk([
            'name' => 'Leonard Doherty', 'first_name' => 'Leonard', 'last_name' => 'Doherty',
            'description' => "Leonard Doherty was a marine worker arrested on a needle-trades picket line in Boston in 1930 and held in the Suffolk County Jail, where authorities sought to deport him to Canada and, the ILD charged, cooperate with Canadian police to frame him on a murder charge. The ILD campaigned to \"save Leonard Doherty from sentence to death on a frame-up murder charge.\"",
            'state' => 'Massachusetts', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested on a Boston needle-trades picket line and held for deportation amid a threatened murder frame-up.',
                'convicted' => 'Held pending deportation, 1930',
                'sentence' => 'Held in the Suffolk County Jail, Boston.',
                'institution_name' => 'Suffolk County Jail',
                'institution_city' => 'Boston', 'institution_state' => 'Massachusetts',
            ]],
        ], ['arrest_date' => [1930, null, null]]);
        $mk([
            'name' => 'Stephen Puleo', 'first_name' => 'Stephen', 'last_name' => 'Puleo',
            'description' => "Stephen Puleo was arrested with seven other workers at a protest meeting on Boston Common on 6 April 1930 against the Leonard Doherty frame-up, when they resisted police breaking up the gathering.",
            'state' => 'Massachusetts', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested at the 6 April 1930 Boston Common protest against the Doherty frame-up.',
                'convicted' => 'Arrested, 6 April 1930',
                'sentence' => 'Jailed after the Boston Common protest.',
                'institution_city' => 'Boston', 'institution_state' => 'Massachusetts',
            ]],
        ], ['arrest_date' => [1930, 4, 6]]);

        // ── WELFARE ISLAND / NYC FRAME-UPS ───────────────────────────────
        $mk([
            'name' => 'Peter Darck', 'first_name' => 'Peter', 'last_name' => 'Darck',
            'description' => "Peter Darck was a militant window washer jailed on Welfare Island, New York in 1930 for strike activity, writing to the ILD from the island's Correction Hospital.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed for strike activity as a window washer in New York City.',
                'convicted' => 'Jailed for strike activity, 1930',
                'sentence' => 'Served a term on Welfare Island.',
                'institution_name' => 'Welfare Island',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['arrest_date' => [1930, null, null]]);
        $mk([
            'name' => 'J. L. Williamson', 'first_name' => 'J. L.', 'last_name' => 'Williamson',
            'description' => "J. L. Williamson was a sixty-seven-year-old Spanish–American War veteran and American Legion charter member framed on a \"petty larceny\" charge — arrested carrying Ex-Servicemen's League and Anti-Imperialist League cards — and sentenced to three years on Welfare Island, New York. Held four months in the Tombs awaiting trial, he had been imprisoned since September 1928.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Anti-imperialism'],
            'affiliation' => ['Anti-Imperialist League'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed on a petty-larceny charge for carrying Ex-Servicemen\'s League and Anti-Imperialist League cards.',
                'convicted' => 'Convicted, sentenced to three years',
                'sentence' => 'Three years on Welfare Island; imprisoned since September 1928.',
                'institution_name' => 'Welfare Island',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['incarceration_date' => [1928, 9, null]]);

        // ── OTHER INDIVIDUAL 1930 CASES ──────────────────────────────────
        $mk([
            'name' => 'Sol Harper', 'first_name' => 'Sol', 'last_name' => 'Harper',
            'description' => "Sol Harper was arrested and imprisoned in New York City in 1930 for raising the anti-lynching issue against the leadership of the Brotherhood of Sleeping Car Porters and the Socialist Party.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Anti-racism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested for raising the anti-lynching issue in New York City.',
                'convicted' => 'Jailed, 1930',
                'sentence' => 'Imprisoned in New York City.',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['arrest_date' => [1930, null, null]]);
        $mk([
            'name' => 'Phil Raymond', 'first_name' => 'Phil', 'last_name' => 'Raymond',
            'description' => "Phil Raymond was a much-persecuted leader of the Detroit auto workers and Communist mayoral candidate, beaten and arrested at Pontiac in the spring of 1930 when General Motors and Fisher Body agents broke up meetings of the unemployed.",
            'state' => 'Michigan', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['Auto Workers Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested at Pontiac, Michigan for organizing unemployed auto workers.',
                'convicted' => 'Arrested, 1930',
                'sentence' => 'Repeatedly arrested in the Detroit auto organizing drive.',
                'institution_city' => 'Pontiac', 'institution_state' => 'Michigan',
            ]],
        ], ['arrest_date' => [1930, null, null]]);
        $mk([
            'name' => 'Anderson McPherson', 'first_name' => 'Anderson', 'last_name' => 'McPherson',
            'description' => "Anderson McPherson was a Black youth condemned to death at Crescent Springs, Kentucky in 1930 — a case the ILD denounced as a \"legal lynching.\"",
            'state' => 'Kentucky', 'gender' => 'Male', 'race' => 'Black',
            'era' => '1930s', 'in_custody' => false, 'released' => false,
            'cases' => [[
                'charges' => 'Condemned to death at Crescent Springs, Kentucky in what the ILD called a legal lynching.',
                'convicted' => 'Sentenced to death, 1930',
                'sentence' => 'Death sentence.',
                'institution_state' => 'Kentucky',
            ]],
        ], []);
        $mk([
            'name' => 'Bill Caudle', 'first_name' => 'Bill', 'last_name' => 'Caudle',
            'description' => "Bill Caudle was a fifty-one-year-old Southern textile mill night watchman who joined the union and was jailed at Lumberton, North Carolina on charges of \"carrying concealed weapons\" and threatening people on the highway; the ILD defended and freed him.",
            'state' => 'North Carolina', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['National Textile Workers Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed on concealed-weapons and threat charges after joining the textile union at Lumberton, North Carolina.',
                'convicted' => 'Jailed; freed on ILD defense',
                'sentence' => 'Held at Lumberton until freed by the ILD.',
                'institution_city' => 'Lumberton', 'institution_state' => 'North Carolina',
            ]],
        ], ['arrest_date' => [1930, null, null]]);
        $mk([
            'name' => 'Cliff Saylors', 'first_name' => 'Cliff', 'last_name' => 'Saylors',
            'description' => "Cliff Saylors was a Southern ILD organizer active around the Gastonia textile struggle, against whom North Carolina lodged charges of murder and perjury in connection with the Aderholt case in late 1929–1930.",
            'state' => 'North Carolina', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['International Labor Defense'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with murder and perjury in connection with the Gastonia (Aderholt) case as a Southern ILD organizer.',
                'convicted' => 'Charged, 1929–30',
                'sentence' => 'Faced the Gastonia-related charges; defended by the ILD.',
                'institution_state' => 'North Carolina',
            ]],
        ], ['arrest_date' => [1930, null, null]]);
        $mk([
            'name' => 'Andrew Turner', 'first_name' => 'Andrew', 'last_name' => 'Turner',
            'description' => "Andrew Turner was a Black militant worker of Chester, Pennsylvania held on a manslaughter/murder charge over a motor accident \"for which he was not to blame,\" facing ten years; the ILD made his defense a cover campaign, \"Free Andrew Turner.\"",
            'state' => 'Pennsylvania', 'gender' => 'Male', 'race' => 'Black',
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Held on a manslaughter/murder charge over a motor accident at Chester, Pennsylvania.',
                'convicted' => 'Held facing ten years, 1930',
                'sentence' => 'Faced ten years; defended by the ILD.',
                'institution_city' => 'Chester', 'institution_state' => 'Pennsylvania',
            ]],
        ], ['arrest_date' => [1930, null, null]]);
        $mk([
            'name' => 'Sam Benato', 'first_name' => 'Sam', 'last_name' => 'Benato',
            'description' => "Sam Benato was an anthracite miner of the Pittston, Pennsylvania region jailed in the Eastern State Penitentiary at Philadelphia for organizing against the contractor system in the coal fields.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['National Miners Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed for organizing anthracite miners against the contractor system in the Pittston region.',
                'convicted' => 'Imprisoned, 1930',
                'sentence' => 'Held in the Eastern State Penitentiary, Philadelphia.',
                'institution_name' => 'Eastern State Penitentiary',
                'institution_city' => 'Philadelphia', 'institution_state' => 'Pennsylvania',
            ]],
        ], []);
        $mk([
            'name' => 'Israel Prager', 'first_name' => 'Israel', 'last_name' => 'Prager',
            'description' => "Israel Prager was arrested as a speaker when Boston police broke up a 1930 Sacco–Vanzetti memorial demonstration.",
            'state' => 'Massachusetts', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested for speaking at a Sacco–Vanzetti demonstration broken up by Boston police.',
                'convicted' => 'Arrested, 1930',
                'sentence' => 'Jailed after the demonstration.',
                'institution_city' => 'Boston', 'institution_state' => 'Massachusetts',
            ]],
        ], ['arrest_date' => [1930, null, null]]);

        // ── VAN ETTEN NY FLAG CASE (YCL summer camp) ─────────────────────
        foreach ([
            ['Mabel Husa', 'Mabel', 'Husa'],
            ['Ailene Holmes', 'Ailene', 'Holmes'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a Young Communist League summer-camp leader at Van Etten, New York convicted in 1930 under the state penal code for \"insulting\" the flag and sentenced to three months in jail and a $50 fine, held in the Monroe County Penitentiary at Elmira.",
                'state' => 'New York', 'gender' => 'Female',
                'ideologies' => ['Communism'],
                'affiliation' => ['Young Communist League'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => "Convicted under New York's penal code for \"insulting\" the flag at a Young Communist League camp at Van Etten.",
                    'convicted' => 'Convicted, 1930',
                    'sentence' => 'Three months and a $50 fine at the Monroe County Penitentiary, Elmira.',
                    'institution_name' => 'Monroe County Penitentiary',
                    'institution_city' => 'Elmira', 'institution_state' => 'New York',
                ]],
            ], ['arrest_date' => [1930, null, null]]);
        }

        // ── NEW BEDFORD TEXTILE STRIKE ───────────────────────────────────
        $mk([
            'name' => 'August Pinto', 'first_name' => 'August', 'last_name' => 'Pinto',
            'description' => "August Pinto was a picket captain during the New Bedford, Massachusetts textile strike, sentenced to six months in jail.",
            'state' => 'Massachusetts', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['National Textile Workers Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed as a picket captain during the New Bedford textile strike.',
                'convicted' => 'Convicted, 1930',
                'sentence' => 'Six months in jail at New Bedford.',
                'institution_city' => 'New Bedford', 'institution_state' => 'Massachusetts',
            ]],
        ], ['arrest_date' => [1930, null, null]]);

        // ── FLEET LEAFLET ARRESTS (NYC) ──────────────────────────────────
        foreach ([
            ['Rose Resnikoff', 'Rose', 'Resnikoff', "charged with \"prostitution\""],
            ['May Miller', 'May', 'Miller', "charged with \"criminal anarchy\""],
        ] as [$name, $first, $last, $charge]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a young Communist arrested in New York City in 1930 for distributing anti-militarist leaflets to sailors of the U.S. fleet during the Grover Whalen crackdown, {$charge}.",
                'state' => 'New York', 'gender' => 'Female',
                'ideologies' => ['Communism'],
                'affiliation' => ['Young Communist League'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Arrested for distributing leaflets to sailors of the U.S. fleet in New York City.',
                    'convicted' => 'Arrested, 1930',
                    'sentence' => 'Jailed on the fleet-leaflet charge.',
                    'institution_city' => 'New York', 'institution_state' => 'New York',
                ]],
            ], ['arrest_date' => [1930, null, null]]);
        }

        // ── SAN QUENTIN CLASS-WAR LETTER PRISONER ────────────────────────
        $mk([
            'name' => 'Mike Miskich', 'first_name' => 'Mike', 'last_name' => 'Miskich',
            'description' => "Mike Miskich was a self-identified class-war prisoner writing to the ILD from San Quentin Prison, California in 1930.",
            'state' => 'California', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Held as a class-war prisoner at San Quentin.',
                'convicted' => 'Imprisoned, 1930',
                'sentence' => 'Served time at San Quentin.',
                'institution_name' => 'San Quentin State Prison',
                'institution_city' => 'San Quentin', 'institution_state' => 'California',
            ]],
        ], []);

        // ── CHRISTMAS "PRISONERS" ROSTER — additional named class-war men ─
        // From the Dec 1930 Winter Relief roster (dependents excluded; names
        // already recorded elsewhere omitted).
        $roster = [
            ['Ignacio Gonzalez', 'Ignacio', 'Gonzalez', 'San Quentin State Prison', 'San Quentin', 'California'],
            ['George B. Pesce', 'George B.', 'Pesce', 'San Quentin State Prison', 'San Quentin', 'California'],
            ['Frank Brbot', 'Frank', 'Brbot', 'West Virginia Penitentiary', 'Moundsville', 'West Virginia'],
            ['Alex Chessman', 'Alex', 'Chessman', 'West Virginia Penitentiary', 'Moundsville', 'West Virginia'],
            ['Steve Jacobs', 'Steve', 'Jacobs', 'Ohio Penitentiary', 'Roseville', 'Ohio'],
            ['Leon Mabille', 'Leon', 'Mabille', 'Franklin County Jail', 'Malone', 'New York'],
            ['A. Feinberg', 'A.', 'Feinberg', 'Los Angeles County Jail', 'Los Angeles', 'California'],
            ['Jack Garvine', 'Jack', 'Garvine', 'Deer Island Prison', 'Boston', 'Massachusetts'],
            ['John Sims', 'John', 'Sims', 'Cook County Jail', 'Chicago', 'Illinois'],
            ['John Munch', 'John', 'Munch', 'Cook County Jail', 'Chicago', 'Illinois'],
            ['Herbert Zimmerman', 'Herbert', 'Zimmerman', 'Cook County Jail', 'Chicago', 'Illinois'],
        ];
        foreach ($roster as [$name, $first, $last, $inst, $city, $state]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was listed among the class-war prisoners on the International Labor Defense's 1930 Winter Relief roster, confined at the {$inst} in {$city}, {$state}.",
                'state' => $state, 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['International Labor Defense'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Recorded as a class-war prisoner on the ILD\'s 1930 Winter Relief roster.',
                    'convicted' => 'Imprisoned as of 1930',
                    'sentence' => "Held at the {$inst}.",
                    'institution_name' => $inst,
                    'institution_city' => $city, 'institution_state' => $state,
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

        $this->info("\nDone. Processed {$added} of ".count($people)." 1930 Labor Defender prisoner(s).");

        return self::SUCCESS;
    }
}
