<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Batch 7 from the ILD's Labor Defender (all 12 issues of 1928): the named
 * cases of 1928 not already recorded. The year's big fronts:
 *
 *  - The New Bedford / Fall River textile strike (1928): its jailed leaders,
 *    and John Porter, the strike organizer court-martialed as an Army
 *    "deserter" and sent to Leavenworth (the "Free John Porter" campaign).
 *  - The Pittston, Pa. anthracite frame-up: Sam Bonita and two co-defendants,
 *    progressive UMW men charged with the killing of a Cappellini gunman.
 *  - The 1928 soft-coal strike prosecutions (Save-the-Union / National Miners
 *    Union): John Brophy, Pat Toohey, Anthony Minerich.
 *  - Steve Bradich, the fourth Woodlawn Flynn-Sedition-Act defendant (the trio
 *    Muselin/Resetar/Zima are already recorded).
 *  - The Kansas City criminal-syndicalism raids on packinghouse meetings.
 *  - The Wall Street anti-imperialist ("Hands Off Nicaragua") arrests of July
 *    1928, and the Washington anti-war / Free-John-Porter arrests of December.
 *  - Free-speech and anti-fascist cases: Young Pioneer Harry Eisman, Harry
 *    Kantor (jailed over a Sacco-Vanzetti placard), and two anti-fascists.
 *  - Class-war prisoners newly named on the December Christmas roster.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddLaborDefender1928Cases extends Command
{
    protected $signature = 'prisoners:add-labor-defender-1928';

    protected $description = 'Add named 1928 prisoners from Labor Defender (New Bedford strike, Pittston miners, KC syndicalism, Wall St + DC anti-war, Young Pioneer, roster)';

    public function handle(): int
    {
        $people = [];

        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ---- New Bedford / Fall River textile strike (1928) ----
        $nbStrikers = [
            ['Fred Beal', 'Fred', 'Beal', "Fred E. Beal was the lead organizer of the great 1928 New Bedford, Massachusetts textile strike, in which tens of thousands of mill workers walked out against a wage cut. Arrested again and again on the picket line — the ILD's Labor Defender followed his jailings through the year — he was one of the strike leaders indicted for conspiracy. The following year he led the Gastonia, North Carolina textile strike and was convicted in the notorious Gastonia case before jumping bail to the Soviet Union."],
            ['William Murdoch', 'William', 'Murdoch', "William T. Murdoch was one of the leaders of the 1928 New Bedford, Massachusetts textile strike, jailed repeatedly on charges of \"disturbing the peace\" and serving months in the city jail for his picket-line activity, as the ILD's Labor Defender reported through the strike."],
            ['Eli Keller', 'Eli', 'Keller', "Eli Keller was one of the leaders of the 1928 New Bedford, Massachusetts textile strike who, the ILD's Labor Defender reported, faced years of imprisonment in the mass conspiracy prosecutions the mill owners brought against the strike committee."],
            ['Eulalia Mendes', 'Eulalia', 'Mendes', "Eulalia Mendes was one of the women leaders of the 1928 New Bedford, Massachusetts textile strike, repeatedly arrested and prosecuted with the rest of the strike committee, as the ILD's Labor Defender reported."],
            ['Frank Augusto', 'Frank', 'Augusto', "Frank Augusto, a 62-year-old striker in the 1928 New Bedford, Massachusetts textile strike, was beaten unconscious by police on the picket line and then sentenced to four months in jail — one of the elderly strikers whose treatment the ILD's Labor Defender publicized."],
            ['Augusto Gonzales Pinto', 'Augusto', 'Pinto', "Augusto C. Gonzales Pinto was a Portuguese-immigrant leader of the 1928 New Bedford, Massachusetts textile strike, described by the ILD's Labor Defender as the most frequently arrested of the strikers — jailed over and over for his part in the mass picketing."],
        ];
        foreach ($nbStrikers as [$name, $first, $last, $desc]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => $desc,
                'state' => 'Massachusetts', 'gender' => (str_contains($name, 'Eulalia') ? 'Female' : 'Male'),
                'ideologies' => ['Labor organizing', 'Communism'],
                'affiliation' => ['Textile Mill Committees'],
                'era' => '1920s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed and prosecuted for leading mass picketing in the 1928 New Bedford textile strike.',
                    'convicted' => 'Repeatedly arrested and convicted, 1928',
                    'sentence' => 'Served jail terms during the strike; the strike leaders faced mass conspiracy indictments.',
                ]],
            ], ['arrest_date' => [1928, null, null]]);
        }
        $mk([
            'name' => 'John Porter', 'first_name' => 'John', 'last_name' => 'Porter',
            'description' => "John Porter, a young vice-president of the New Bedford Textile Workers' Union and a Young Workers League organizer, had earlier enlisted in the U.S. Army; when his strike leadership drew the authorities' attention in 1928 he was seized, court-martialed as a \"deserter,\" and sentenced to two and a half years of hard labor — a punishment the movement understood as retaliation for the strike. Held first at Fort Adams, Rhode Island, then near-incommunicado at the Fort Leavenworth military prison, he became the subject of a national \"Free John Porter\" campaign led by the ILD.",
            'state' => 'Massachusetts', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['New Bedford Textile Workers Union', 'Young Workers League'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Court-martialed as an Army "deserter" — in reality for leading the 1928 New Bedford textile strike.',
                'convicted' => 'Convicted by court-martial, 1928',
                'sentence' => 'Two and a half years of hard labor; held at Fort Adams, R.I. and the Fort Leavenworth military prison.',
                'institution_name' => 'United States Disciplinary Barracks, Fort Leavenworth',
                'institution_city' => 'Leavenworth', 'institution_state' => 'Kansas',
            ]],
        ], ['incarceration_date' => [1928, null, null]]);

        // ---- Pittston, Pa. anthracite frame-up (Bonita case) ----
        $mk([
            'name' => 'Sam Bonita', 'first_name' => 'Sam', 'last_name' => 'Bonita',
            'description' => "Sam Bonita, president of a progressive United Mine Workers local at Pittston in the Pennsylvania anthracite field, was one of three insurgent miners framed for the killing of Frank Agati, a gunman for the corrupt district president Rinaldo Cappellini, during the bitter 1927-28 anthracite union civil war. Convicted of manslaughter in April 1928, Bonita was sentenced to six to twelve years and sent to the Eastern State Penitentiary in Philadelphia; the ILD carried the defense.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Industrial unionism'],
            'affiliation' => ['United Mine Workers of America'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed for the killing of a Cappellini gunman (Frank Agati) during the 1927-28 anthracite union civil war at Pittston, Pa.',
                'convicted' => 'Convicted of manslaughter, April 1928',
                'sentence' => 'Six to twelve years at the Eastern State Penitentiary, Philadelphia.',
                'institution_name' => 'Eastern State Penitentiary',
                'institution_city' => 'Philadelphia', 'institution_state' => 'Pennsylvania',
            ]],
        ], ['incarceration_date' => [1928, 4, null]]);
        foreach ([['Steve Mendola', 'Steve', 'Mendola'], ['Adam Moleski', 'Adam', 'Moleski']] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of the three progressive Pittston, Pennsylvania anthracite miners framed with Sam Bonita for the killing of Frank Agati, a gunman for district president Rinaldo Cappellini, during the 1927-28 anthracite union civil war — though, the defense insisted, he never fired a gun. He was held in the Wilkes-Barre county jail and prosecuted in the case the ILD's Labor Defender fought through 1928.",
                'state' => 'Pennsylvania', 'gender' => 'Male',
                'ideologies' => ['Industrial unionism'],
                'affiliation' => ['United Mine Workers of America'],
                'era' => '1920s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Framed with Sam Bonita for the killing of a Cappellini gunman during the 1927-28 Pittston anthracite union war.',
                    'convicted' => 'Prosecuted for manslaughter, 1928',
                    'sentence' => 'Held in the Wilkes-Barre county jail; tried in the Bonita anthracite frame-up.',
                    'institution_name' => 'Luzerne County Jail',
                    'institution_city' => 'Wilkes-Barre', 'institution_state' => 'Pennsylvania',
                ]],
            ], ['arrest_date' => [1928, null, null]]);
        }

        // ---- 1928 soft-coal strike prosecutions ----
        $mk([
            'name' => 'John Brophy', 'first_name' => 'John', 'last_name' => 'Brophy',
            'description' => "John Brophy, the reform miners' leader who had challenged John L. Lewis for the presidency of the United Mine Workers and headed the \"Save the Union\" movement, was arrested at a miners' meeting in Renton, Pennsylvania in March 1928 during the great soft-coal strike — one of many arrests of insurgent organizers the ILD's Labor Defender reported that year. He went on to become a founding director of the CIO.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Industrial unionism'],
            'affiliation' => ['United Mine Workers of America', 'Save the Union Committee'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested at a "Save the Union" miners\' meeting in Renton, Pa. during the 1928 soft-coal strike.',
                'convicted' => 'Arrested, March 1928',
                'sentence' => 'Jailed at the miners\' meeting (a brief arrest amid the strike prosecutions).',
            ]],
        ], ['arrest_date' => [1928, 3, null]]);
        $mk([
            'name' => 'Pat Toohey', 'first_name' => 'Pat', 'last_name' => 'Toohey',
            'description' => "Pat Toohey, a young communist miner and editor of the insurgents' paper The Coal Digger, was arrested and beaten by a state trooper in Renton, Pennsylvania in March 1928 for an anti-war speech during the great soft-coal strike. He became a leading figure of the National Miners Union.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Industrial unionism'],
            'affiliation' => ['National Miners Union'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested and beaten by a state trooper for an anti-war speech at a miners\' meeting, Renton, Pa., March 1928.',
                'convicted' => 'Arrested, March 1928',
                'sentence' => 'Beaten and jailed during the soft-coal strike.',
            ]],
        ], ['arrest_date' => [1928, 3, null]]);
        $mk([
            'name' => 'Anthony Minerich', 'first_name' => 'Anthony', 'last_name' => 'Minerich',
            'description' => "Anthony (Tony) Minerich, a communist coal miner and organizer of the insurgent movement in the soft-coal fields, was convicted in 1928 of violating a federal anti-picketing injunction during the great strike and was arrested again on a riot charge at the September 1928 National Miners Union convention in Pittsburgh. The ILD carried his injunction case toward the Supreme Court.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Industrial unionism'],
            'affiliation' => ['National Miners Union'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted of violating a federal anti-picketing injunction during the 1928 soft-coal strike; also arrested on a riot charge at the 1928 NMU convention.',
                'convicted' => 'Convicted, 1928; out on \$2,000 bond pending appeal',
                'sentence' => 'Injunction-contempt conviction, appealed with ILD support.',
            ]],
        ], ['arrest_date' => [1928, null, null]]);

        // ---- Woodlawn fourth defendant ----
        $mk([
            'name' => 'Steve Bradich', 'first_name' => 'Steve', 'last_name' => 'Bradich',
            'description' => "Steve Bradich was the fourth defendant convicted in the 1927 \"Woodlawn\" prosecution under Pennsylvania's Flynn Anti-Sedition Act — the red raid on a workers' meeting in the Jones & Laughlin company town of Woodlawn (Aliquippa). Sentenced to two and a half years, a lesser term than the five years given to Pete Muselin, Milan Resetar, and Tom Zima, he was discharged on appeal in 1928.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'affiliation' => ['Workers (Communist) Party'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Convicted under Pennsylvania's Flynn Anti-Sedition Act in the 1926-27 Woodlawn red-raid case.",
                'convicted' => 'Convicted, 1927; discharged on appeal, 1928',
                'sentence' => 'Two and a half years; discharged on appeal.',
                'institution_name' => 'Allegheny County Workhouse',
                'institution_city' => 'Blawnox', 'institution_state' => 'Pennsylvania',
            ]],
        ], []);

        // ---- Kansas City criminal-syndicalism raids (1928) ----
        foreach ([
            ['Hugo Oehler', 'Hugo', 'Oehler', ' A speaker at the raided meetings, he was charged with advocating the overthrow of government; he became a well-known communist and, later, Trotskyist organizer.'],
            ['Matt Cushing', 'Matt', 'Cushing', ''],
            ['E. B. Eastwood', 'E. B.', 'Eastwood', ' He was the local secretary of the International Labor Defense.'],
        ] as [$name, $first, $last, $extra]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was arrested in the 1928 Kansas City, Kansas raids on workers' meetings at the Armour packing plant and charged under the Kansas Criminal Syndicalism Law with advocating or belonging to an organization that advocated the overthrow of government.{$extra} The defendants were released on ILD bond to await trial.",
                'state' => 'Kansas', 'gender' => 'Male',
                'ideologies' => ['Communism'],
                'era' => '1920s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Arrested in the 1928 Kansas City, Kansas packinghouse-meeting raids and charged under the Kansas Criminal Syndicalism Law.',
                    'convicted' => 'Held for trial, 1928; released on ILD bond',
                    'sentence' => 'Jailed and bonded awaiting trial under the criminal-syndicalism act.',
                ]],
            ], ['arrest_date' => [1928, null, null]]);
        }

        // ---- Wall Street "Hands Off Nicaragua" arrests, July 1928 ----
        foreach ([
            ['Robert Minor', 'Robert', 'Minor', 'the celebrated cartoonist turned communist organizer', ['Communism']],
            ['Max Shachtman', 'Max', 'Shachtman', 'a communist writer and editor of the Labor Defender', ['Communism']],
            ['Rebecca Grecht', 'Rebecca', 'Grecht', 'a Workers Party election campaign manager', ['Communism']],
            ['Robert Wolf', 'Robert', 'Wolf', 'a writer', ['Communism']],
        ] as [$name, $first, $last, $who, $ideo]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name}, {$who}, was arrested in the July 3, 1928 Wall Street demonstration against U.S. military intervention in Nicaragua, when police broke up the \"Hands Off Nicaragua\" meeting in New York's financial district and jailed the speakers and dozens of workers. Most of the defendants were given five days.",
                'state' => 'New York', 'gender' => (str_contains($name, 'Rebecca') ? 'Female' : 'Male'),
                'ideologies' => $ideo,
                'affiliation' => ['Workers (Communist) Party'],
                'era' => '1920s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Arrested at the July 3, 1928 Wall Street "Hands Off Nicaragua" anti-imperialist demonstration in New York.',
                    'convicted' => 'Convicted, 1928',
                    'sentence' => 'Five days in jail (as given to most of the defendants).',
                ]],
            ], ['arrest_date' => [1928, 7, 3]]);
        }
        $mk([
            'name' => 'Nathan Kaplan', 'first_name' => 'Nathan', 'last_name' => 'Kaplan',
            'description' => "Nathan Kaplan was one of the workers arrested in the July 3, 1928 Wall Street demonstration against U.S. intervention in Nicaragua, and — unlike the others, who drew five days — was held for the grand jury on a framed felonious-assault charge arising from the police attack on the \"Hands Off Nicaragua\" meeting.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Held for the grand jury on a framed felonious-assault charge after the July 3, 1928 Wall Street anti-imperialist demonstration.',
                'convicted' => 'Held for grand jury, 1928',
                'sentence' => 'Jailed on a felonious-assault frame-up (the others got five days).',
            ]],
        ], ['arrest_date' => [1928, 7, 3]]);

        // ---- Washington anti-war / Free-John-Porter arrests, December 1928 ----
        foreach ([
            ['Karl Reeve', 'Karl', 'Reeve', 'editor of the Labor Defender'],
            ['Karl Jones', 'Karl', 'Jones', 'an organizer of the American Negro Labor Congress'],
            ['Ben Thomas', 'Ben', 'Thomas', 'a Philadelphia machinist and Workers (Communist) Party representative'],
        ] as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name}, {$who}, was one of the leaders arrested in the December 1928 anti-war and \"Free John Porter\" demonstration in Washington, D.C., when police jailed some two dozen workers and several children. The leaders were sentenced to sixty days or \$100; those who refused to pay their fines sat out their terms in jail.",
                'state' => (str_contains($who, 'Philadelphia') ? 'Pennsylvania' : 'District of Columbia'),
                'gender' => 'Male',
                'race' => (str_contains($who, 'Negro Labor') ? 'Black' : null),
                'ideologies' => ['Communism'],
                'affiliation' => ['Workers (Communist) Party'],
                'era' => '1920s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Arrested leading the December 1928 anti-war / "Free John Porter" demonstration in Washington, D.C.',
                    'convicted' => 'Convicted, December 1928',
                    'sentence' => 'Sixty days or \$100; served the term on refusing to pay.',
                ]],
            ], ['arrest_date' => [1928, 12, null]]);
        }

        // ---- Free-speech / Young Pioneer / anti-fascist ----
        $mk([
            'name' => 'Harry Eisman', 'first_name' => 'Harry', 'last_name' => 'Eisman',
            'description' => "Harry Eisman was a New York schoolboy and Young Pioneer leader who, from 1928, was hounded by the authorities for his communist activity — arrested for distributing leaflets, reported by his principal to both the police and the immigration service for deportation, and eventually, in 1930, committed to a reformatory and then deported. His persecution as a child radical made him a cause célèbre; on his deportation he was welcomed in the Soviet Union.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'affiliation' => ['Young Pioneers of America'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested and persecuted from 1928 as a child Young Pioneer for distributing communist leaflets; targeted for deportation.',
                'convicted' => 'Repeatedly arrested from 1928; committed to a reformatory and deported in 1930',
                'sentence' => 'Reform-school commitment and deportation to the Soviet Union.',
            ]],
        ], ['arrest_date' => [1928, null, null]]);
        $mk([
            'name' => 'Harry Kantor', 'first_name' => 'Harry', 'last_name' => 'Kantor',
            'description' => "Harry Kantor was held on \$1,000 bail for the superior court in Boston in 1928 on a charge of criminal libel — for carrying a placard blaming Governor Alvan Fuller for the execution of Sacco and Vanzetti. His fellow demonstrators were fined; his case was one of the free-speech prosecutions the ILD's Labor Defender took up.",
            'state' => 'Massachusetts', 'gender' => 'Male',
            'ideologies' => ['Anarchism'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with criminal libel for a placard blaming Governor Fuller for the Sacco-Vanzetti execution, Boston, 1928.',
                'convicted' => 'Held for superior court on \$1,000 bail, 1928',
                'sentence' => 'Jailed pending trial on the criminal-libel charge.',
            ]],
        ], ['arrest_date' => [1928, null, null]]);
        $mk([
            'name' => 'V. Gaudenzi', 'first_name' => 'V.', 'last_name' => 'Gaudenzi',
            'description' => "V. Gaudenzi was an Italian-American anti-fascist held in the New London, Connecticut jail in 1928 on a charge of \"inciting a riot\" after opposing a Columbus Day fascist parade — one of the anti-fascist arrests the ILD's Labor Defender defended as Mussolini's Blackshirt movement organized among Italian immigrants in America.",
            'state' => 'Connecticut', 'gender' => 'Male',
            'ideologies' => ['Anti-fascism'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Held for "inciting a riot" after opposing a Columbus Day fascist parade, New London, Conn., 1928.',
                'convicted' => 'Jailed on a riot charge, 1928',
                'sentence' => 'Held in the New London county jail.',
                'institution_name' => 'New London County Jail',
                'institution_city' => 'New London', 'institution_state' => 'Connecticut',
            ]],
        ], ['arrest_date' => [1928, 10, null]]);

        // ---- Class-war prisoners newly named on the December 1928 roster ----
        $mk([
            'name' => 'Sam Kurland', 'first_name' => 'Sam', 'last_name' => 'Kurland',
            'description' => "Sam Kurland was a needle-trades class-war prisoner held at Sing Sing (inmate no. 8273), listed on the ILD's roster of American labor prisoners in Labor Defender in 1928. Like most of the rank-and-file union prisoners of the period, little else about his case entered the historical record.",
            'state' => 'New York', 'gender' => 'Male', 'inmate_number' => '8273',
            'ideologies' => ['Labor organizing'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Imprisoned in a needle-trades union case; listed by the ILD as a class-war prisoner (Labor Defender, 1928).',
                'convicted' => 'Convicted (details not documented)',
                'sentence' => 'Held at Sing Sing (no. 8273).',
                'institution_name' => 'Sing Sing Correctional Facility',
                'institution_city' => 'Ossining', 'institution_state' => 'New York',
            ]],
        ], []);
        $mk([
            'name' => 'Alex Chessman', 'first_name' => 'Alex', 'last_name' => 'Chessman',
            'description' => "Alex Chessman was a West Virginia coal miner imprisoned at the Moundsville penitentiary (inmate no. 16282), listed on the ILD's roster of American class-war prisoners in Labor Defender in 1928 as one of the men jailed \"out of the labor struggles.\" Little else about his case entered the historical record.",
            'state' => 'West Virginia', 'gender' => 'Male', 'inmate_number' => '16282',
            'ideologies' => ['Industrial unionism'],
            'affiliation' => ['United Mine Workers of America'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Imprisoned out of the West Virginia mine struggles; listed by the ILD as a class-war prisoner (Labor Defender, 1928).',
                'convicted' => 'Convicted (details not documented)',
                'sentence' => 'Held at the West Virginia penitentiary, Moundsville (no. 16282).',
                'institution_name' => 'West Virginia State Penitentiary',
                'institution_city' => 'Moundsville', 'institution_state' => 'West Virginia',
            ]],
        ], []);
        foreach ([['George Pesce', 'George', 'Pesce', '44462'], ['Gus Madsen', 'Gus', 'Madsen', '44461']] as [$name, $first, $last, $num]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a class-war prisoner held at San Quentin (inmate no. {$num}), listed on the ILD's roster of American labor prisoners in Labor Defender in 1928. Like most of the rank-and-file labor prisoners of the criminal-syndicalism era, little else about his case entered the historical record.",
                'state' => 'California', 'gender' => 'Male', 'inmate_number' => $num,
                'ideologies' => ['Industrial unionism'],
                'era' => '1920s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Listed by the International Labor Defense as a class-war prisoner at San Quentin (Labor Defender, 1928).',
                    'convicted' => 'Convicted (details not documented)',
                    'sentence' => "Held at San Quentin (no. {$num}).",
                    'institution_name' => 'San Quentin State Prison',
                    'institution_city' => 'San Quentin', 'institution_state' => 'California',
                ]],
            ], []);
        }

        $added = 0;
        foreach ($people as $person) {
            $payload = array_filter($person['payload'], fn ($v) => $v !== null);
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
            $prisoner->released = true;
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

        $this->info("\nDone. Processed {$added} 1928 prisoner(s).");

        return self::SUCCESS;
    }
}
