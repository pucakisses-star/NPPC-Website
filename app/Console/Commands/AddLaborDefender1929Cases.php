<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Batch 9 from the ILD's Labor Defender (1929): the remaining named cases of
 * the year beyond Gastonia and Stromberg (which shipped in batch 8).
 *
 *  - The San Quentin frame-up from the 1922 railway shopmen's strike at Daly
 *    City (adds Fred Mermon and Ed Condon; Cornelison and Merritt already
 *    recorded).
 *  - The 1929 Bethlehem, Pa. May-Day sedition arrests (Anna Burlak and others).
 *  - The Pittston anthracite strike arrests of Powers and Mary Donovan Hapgood.
 *  - Salvatore Accorsi, framed for the murder of a state trooper at the 1927
 *    Cheswick Sacco-Vanzetti demonstration.
 *  - Class-war prisoners newly named on the December 1929 roster.
 *  - The 1929 anti-war (August 1) and free-speech / anti-fascist cases.
 *
 * Also corrects a data error from batch 7: the Boston Sacco-Vanzetti-placard
 * prisoner was recorded as "Harry Kantor" but his name is Harry Canter; this
 * command renames that record if present (and does not create a duplicate).
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddLaborDefender1929Cases extends Command
{
    protected $signature = 'prisoners:add-labor-defender-1929';

    protected $description = 'Add remaining 1929 Labor Defender prisoners (railway frame-up, Bethlehem, Hapgoods, Accorsi, roster, anti-war/anti-fascist) + fix Harry Canter name';

    public function handle(): int
    {
        // --- Correction: Harry Kantor -> Harry Canter (batch 7 typo) ---
        $wrong = Prisoner::withoutGlobalScopes()->where('name', 'Harry Kantor')->first();
        if ($wrong && ! Prisoner::withoutGlobalScopes()->where('name', 'Harry Canter')->exists()) {
            $wrong->name = 'Harry Canter';
            $wrong->last_name = 'Canter';
            $wrong->save();
            $this->info('  corrected "Harry Kantor" -> "Harry Canter".');
        }

        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // --- 1922 railway shopmen's strike frame-up (San Quentin) ---
        foreach ([
            ['Fred Mermon', 'Fred', 'Mermon'],
            ['Ed Condon', 'Ed', 'Condon'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of the railway shopmen convicted in the frame-up that grew out of the 1922 nationwide shop-crafts strike near Daly City, California, where a clash left a man dead. Pleading guilty, he was sentenced to ten years at San Quentin; his fellow defendants John J. Cornelison and Claude Merritt drew life terms. The ILD's Labor Defender kept the men's cause alive through the 1920s.",
                'state' => 'California', 'gender' => 'Male',
                'ideologies' => ['Industrial unionism'],
                'affiliation' => ['Brotherhood of Railway Carmen'],
                'era' => '1920s', 'released' => true,
                'cases' => [[
                    'charges' => 'Convicted in the frame-up arising from the 1922 railway shopmen\'s strike near Daly City, California.',
                    'convicted' => 'Pleaded guilty, 1920s',
                    'sentence' => 'Ten years at San Quentin.',
                    'institution_name' => 'San Quentin State Prison',
                    'institution_city' => 'San Quentin', 'institution_state' => 'California',
                ]],
            ], []);
        }

        // --- Bethlehem, Pa. May-Day sedition arrests (1929) ---
        $mk([
            'name' => 'Anna Burlak', 'first_name' => 'Anna', 'last_name' => 'Burlak',
            'description' => "Anna Burlak, an eighteen-year-old silk weaver known as \"the Red Flame,\" was a leader of the National Textile Workers Union and the Young Pioneers when she was arrested in the 1929 red raid on a May-Day meeting of the Ukrainian Workingmen's Association at Bethlehem, Pennsylvania and held under \$5,000 bail on a sedition charge. She went on to a long career as a communist labor organizer in the New England textile mills.",
            'state' => 'Pennsylvania', 'gender' => 'Female',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['National Textile Workers Union'],
            'era' => '1920s', 'released' => true,
            'cases' => [[
                'charges' => 'Arrested on a sedition charge in the 1929 red raid on a May-Day meeting at Bethlehem, Pa.',
                'convicted' => 'Held for trial, 1929',
                'sentence' => 'Held under \$5,000 bail on the sedition charge.',
            ]],
        ], ['arrest_date' => [1929, 5, null]]);
        foreach ([
            ['Albert Brown', 'Albert', 'Brown', ' He was beaten in a third-degree interrogation by a Bethlehem detective.'],
            ['Joe Yelenics', 'Joe', 'Yelenics', ''],
        ] as [$name, $first, $last, $extra]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was arrested with Anna Burlak in the 1929 red raid on a May-Day meeting of the Ukrainian Workingmen's Association at Bethlehem, Pennsylvania, and held under \$5,000 bail on a sedition charge.{$extra}",
                'state' => 'Pennsylvania', 'gender' => 'Male',
                'ideologies' => ['Communism'],
                'era' => '1920s', 'released' => true,
                'cases' => [[
                    'charges' => 'Arrested on a sedition charge in the 1929 red raid on a May-Day meeting at Bethlehem, Pa.',
                    'convicted' => 'Held for trial, 1929',
                    'sentence' => 'Held under \$5,000 bail on the sedition charge.',
                ]],
            ], ['arrest_date' => [1929, 5, null]]);
        }

        // --- Pittston anthracite strike (Hapgoods) ---
        $mk([
            'name' => 'Powers Hapgood', 'first_name' => 'Powers', 'last_name' => 'Hapgood',
            'description' => "Powers Hapgood, the Harvard-educated coal miner and socialist who devoted his life to the labor movement, was arrested in March 1929 at Pittston, Pennsylvania during the anthracite miners' revolt and held under \$5,000 bail on a charge of \"inciting to riot,\" of which he was later acquitted. He became a leading CIO organizer.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Socialism', 'Industrial unionism'],
            'era' => '1920s', 'released' => true,
            'cases' => [[
                'charges' => 'Arrested for "inciting to riot" during the March 1929 anthracite miners\' revolt at Pittston, Pa.',
                'convicted' => 'Acquitted',
                'sentence' => 'Held under \$5,000 bail; later acquitted.',
            ]],
        ], ['arrest_date' => [1929, 3, null]]);
        $mk([
            'name' => 'Mary Donovan Hapgood', 'first_name' => 'Mary', 'last_name' => 'Hapgood',
            'description' => "Mary Donovan Hapgood, a labor activist who had been a leading figure in the Sacco-Vanzetti defense, was arrested with her husband Powers Hapgood in March 1929 at Pittston, Pennsylvania during the anthracite miners' revolt on a charge of \"inciting to riot,\" of which she was later acquitted.",
            'state' => 'Pennsylvania', 'gender' => 'Female',
            'ideologies' => ['Socialism', 'Labor organizing'],
            'era' => '1920s', 'released' => true,
            'cases' => [[
                'charges' => 'Arrested for "inciting to riot" during the March 1929 anthracite miners\' revolt at Pittston, Pa.',
                'convicted' => 'Acquitted',
                'sentence' => 'Held on the riot charge; later acquitted.',
            ]],
        ], ['arrest_date' => [1929, 3, null]]);

        // --- Cheswick trooper-murder frame-up ---
        $mk([
            'name' => 'Salvatore Accorsi', 'first_name' => 'Salvatore', 'last_name' => 'Accorsi',
            'description' => "Salvatore Accorsi was framed for the murder of a Pennsylvania state trooper killed when mounted police charged a Sacco-Vanzetti memorial meeting of striking miners at Cheswick, Pennsylvania in 1927. Tracked down in New York two years later, he was extradited to Allegheny County, held in the Pittsburgh jail facing the electric chair, and defended by the ILD.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Industrial unionism'],
            'era' => '1920s', 'released' => true,
            'cases' => [[
                'charges' => 'Framed for the murder of a state trooper killed when police charged a 1927 Sacco-Vanzetti miners\' meeting at Cheswick, Pa.',
                'convicted' => 'Extradited and held for trial, 1929',
                'sentence' => 'Held in the Allegheny County jail facing the electric chair.',
                'institution_name' => 'Allegheny County Jail',
                'institution_city' => 'Pittsburgh', 'institution_state' => 'Pennsylvania',
            ]],
        ], ['arrest_date' => [1929, null, null]]);

        // --- December 1929 roster (new names) ---
        $mk([
            'name' => 'Lawrence Allen', 'first_name' => 'Lawrence', 'last_name' => 'Allen',
            'description' => "Lawrence Allen was a West Virginia coal striker imprisoned at the Moundsville penitentiary on a charge of dynamiting a coal mine during the miners' strike — one of ten years of his sentence served, as the ILD's Labor Defender reported him on its 1929 roster of class-war prisoners.",
            'state' => 'West Virginia', 'gender' => 'Male',
            'ideologies' => ['Industrial unionism'],
            'affiliation' => ['United Mine Workers of America'],
            'era' => '1920s', 'released' => true,
            'cases' => [[
                'charges' => 'Convicted of dynamiting a coal mine during the West Virginia miners\' strike.',
                'convicted' => 'Convicted, 1920s',
                'sentence' => 'Ten-year term at the Moundsville penitentiary.',
                'institution_name' => 'West Virginia State Penitentiary',
                'institution_city' => 'Moundsville', 'institution_state' => 'West Virginia',
            ]],
        ], []);
        $mk([
            'name' => 'John M. Lynch', 'first_name' => 'John', 'last_name' => 'Lynch',
            'description' => "John M. Lynch was a West Virginia coal miner serving a ten-year sentence at the Moundsville penitentiary on what he and the ILD described as a frame-up growing out of the mine wars — listed on the International Labor Defense's 1929 roster of class-war prisoners.",
            'state' => 'West Virginia', 'gender' => 'Male',
            'ideologies' => ['Industrial unionism'],
            'affiliation' => ['United Mine Workers of America'],
            'era' => '1920s', 'released' => true,
            'cases' => [[
                'charges' => 'Framed, by his account, out of the West Virginia mine wars.',
                'convicted' => 'Convicted, 1920s',
                'sentence' => 'Ten years at the Moundsville penitentiary.',
                'institution_name' => 'West Virginia State Penitentiary',
                'institution_city' => 'Moundsville', 'institution_state' => 'West Virginia',
            ]],
        ], []);
        $mk([
            'name' => 'Robert Anderson', 'first_name' => 'Robert', 'last_name' => 'Anderson',
            'description' => "Robert Anderson was a Pennsylvania coal miner convicted of assault with intent to kill during a 1928 miners' strike and held at the state prison at Bellefonte, where he had served two years by the time the ILD's Labor Defender listed him on its 1929 roster of class-war prisoners.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Industrial unionism'],
            'affiliation' => ['National Miners Union'],
            'era' => '1920s', 'released' => true,
            'cases' => [[
                'charges' => "Convicted of assault with intent to kill during a 1928 Pennsylvania miners' strike.",
                'convicted' => 'Convicted, 1928',
                'sentence' => 'Held at the state prison, Bellefonte, Pa.',
                'institution_name' => 'Rockview State Penitentiary',
                'institution_city' => 'Bellefonte', 'institution_state' => 'Pennsylvania',
            ]],
        ], []);
        $mk([
            'name' => 'Teddy Jackoski', 'first_name' => 'Teddy', 'last_name' => 'Jackoski',
            'description' => "Teddy Jackoski was an Ohio coal miner convicted of assault with intent to murder during the 1928 Ohio miners' strike and sentenced to five to twenty years — held in the state penitentiary, and listed on the ILD's 1929 roster of American class-war prisoners.",
            'state' => 'Ohio', 'gender' => 'Male',
            'ideologies' => ['Industrial unionism'],
            'affiliation' => ['United Mine Workers of America'],
            'era' => '1920s', 'released' => true,
            'cases' => [[
                'charges' => "Convicted of assault with intent to murder during the 1928 Ohio miners' strike.",
                'convicted' => 'Convicted, 1928',
                'sentence' => 'Five to twenty years at the Ohio Penitentiary.',
                'institution_name' => 'Ohio Penitentiary',
                'institution_city' => 'Columbus', 'institution_state' => 'Ohio',
            ]],
        ], []);
        $mk([
            'name' => 'John Morgan', 'first_name' => 'John', 'last_name' => 'Morgan',
            'description' => "John Morgan, an organizer for the Marine Workers' League, served a six-month term on New York's Welfare Island for his waterfront organizing, and appeared in the ILD's Labor Defender as one of the labor prisoners of 1929.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['Marine Workers League'],
            'era' => '1920s', 'released' => true,
            'cases' => [[
                'charges' => 'Imprisoned for waterfront organizing as a Marine Workers\' League organizer.',
                'convicted' => 'Convicted, 1929',
                'sentence' => 'Six months on Welfare Island, New York.',
            ]],
        ], []);
        $mk([
            'name' => 'Mike Matty', 'first_name' => 'Mike', 'last_name' => 'Matty',
            'description' => "Mike Matty was a Pennsylvania coal miner jailed in the Allegheny County Workhouse at Blawnox for backing the insurgent National Miners Union against the John L. Lewis leadership during the 1928 soft-coal strike — one of the class-war prisoners the ILD's Labor Defender supported.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Industrial unionism'],
            'affiliation' => ['National Miners Union'],
            'era' => '1920s', 'released' => true,
            'cases' => [[
                'charges' => "Jailed for supporting the National Miners Union in the 1928 Pennsylvania soft-coal strike.",
                'convicted' => 'Convicted, 1928',
                'sentence' => 'Held in the Allegheny County Workhouse at Blawnox.',
                'institution_name' => 'Allegheny County Workhouse',
                'institution_city' => 'Blawnox', 'institution_state' => 'Pennsylvania',
            ]],
        ], []);

        // --- Anti-fascist / free-speech / anti-war (1929) ---
        $mk([
            'name' => 'Mario Giletti', 'first_name' => 'Mario', 'last_name' => 'Giletti',
            'description' => "Mario Giletti was an Italian-American anti-fascist who served nine months at the Comstock (Great Meadow) prison in New York for fighting Mussolini's Blackshirts in the streets, and on his release in 1929 was seized by the immigration authorities and held for deportation to Fascist Italy — a case the ILD's Labor Defender fought as doubly unjust.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Anti-fascism'],
            'era' => '1920s', 'released' => true,
            'cases' => [[
                'charges' => "Convicted after a street fight with Mussolini's Blackshirts; then held for deportation to Fascist Italy.",
                'convicted' => 'Convicted, 1928; held for deportation on release, 1929',
                'sentence' => 'Nine months at the Comstock (Great Meadow) prison; then immigration custody.',
                'institution_name' => 'Great Meadow Correctional Facility',
                'institution_city' => 'Comstock', 'institution_state' => 'New York',
            ]],
        ], []);
        $mk([
            'name' => 'Ben Lifshitz', 'first_name' => 'Ben', 'last_name' => 'Lifshitz',
            'description' => "Ben Lifshitz was given thirty days on New York's Welfare Island in 1929 after being seized in a police raid on the Daily Worker office during a demonstration — one of the free-speech arrests the ILD's Labor Defender reported that year.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1920s', 'released' => true,
            'cases' => [[
                'charges' => 'Arrested in a 1929 police raid on the Daily Worker office during a demonstration.',
                'convicted' => 'Convicted, 1929',
                'sentence' => 'Thirty days on Welfare Island, New York.',
            ]],
        ], ['arrest_date' => [1929, null, null]]);
        $mk([
            'name' => 'Clarence Hathaway', 'first_name' => 'Clarence', 'last_name' => 'Hathaway',
            'description' => "Clarence Hathaway, a communist leader who would later edit the Daily Worker, was arrested in Chicago in October 1929 in a sedition-and-robbery frame-up connected to a Western Electric organizing drive, and released on bail with several co-defendants — a case the ILD's Labor Defender carried.",
            'state' => 'Illinois', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'affiliation' => ['Communist Party USA'],
            'era' => '1920s', 'released' => true,
            'cases' => [[
                'charges' => 'Arrested in an October 1929 Chicago sedition-and-robbery frame-up tied to a Western Electric organizing drive.',
                'convicted' => 'Held for trial, 1929; released on bail',
                'sentence' => 'Jailed and bonded awaiting trial.',
            ]],
        ], ['arrest_date' => [1929, 10, null]]);
        $mk([
            'name' => 'Betty Gannett', 'first_name' => 'Betty', 'last_name' => 'Gannett',
            'description' => "Betty Gannett, a young communist organizer who would become a leading party educator, was arrested at Martins Ferry, Ohio at an August 1, 1929 anti-war meeting and held under \$1,000 bail on a criminal-syndicalism charge — one of the International Anti-War Day arrests the ILD's Labor Defender defended.",
            'state' => 'Ohio', 'gender' => 'Female',
            'ideologies' => ['Communism'],
            'affiliation' => ['Communist Party USA'],
            'era' => '1920s', 'released' => true,
            'cases' => [[
                'charges' => 'Arrested on a criminal-syndicalism charge at an August 1, 1929 anti-war meeting in Martins Ferry, Ohio.',
                'convicted' => 'Held for trial, 1929',
                'sentence' => 'Held under \$1,000 bail on the criminal-syndicalism charge.',
            ]],
        ], ['arrest_date' => [1929, 8, 1]]);
        $mk([
            'name' => 'Stephan Graham', 'first_name' => 'Stephan', 'last_name' => 'Graham',
            'description' => "Stephan Graham, a local secretary of the International Labor Defense, was arrested at Portsmouth, Virginia in 1929 on a charge of \"inciting to riot\" after a Trade Union Unity League meeting, and held under \$2,500 bail.",
            'state' => 'Virginia', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'affiliation' => ['International Labor Defense'],
            'era' => '1920s', 'released' => true,
            'cases' => [[
                'charges' => 'Arrested for "inciting to riot" after a Trade Union Unity League meeting, Portsmouth, Va., 1929.',
                'convicted' => 'Held for trial, 1929',
                'sentence' => 'Held under \$2,500 bail.',
            ]],
        ], ['arrest_date' => [1929, null, null]]);
        $mk([
            'name' => 'William Shifrin', 'first_name' => 'William', 'last_name' => 'Shifrin',
            'description' => "William Shifrin, a member of the New York butchers' union, was indicted for manslaughter after killing, in self-defense, one of the strike-breaking gangsters hired against the union during a 1929 strike — a defense the ILD's Labor Defender took up with a \"Shifrin Defense Fund.\"",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Amalgamated Food Workers'],
            'era' => '1920s', 'released' => true,
            'cases' => [[
                'charges' => 'Indicted for manslaughter for killing, in self-defense, a strike-breaking gangster during a 1929 butchers\' union strike.',
                'convicted' => 'Held for trial, 1929',
                'sentence' => 'Held under high bail pending trial.',
            ]],
        ], ['arrest_date' => [1929, null, null]]);

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

        $this->info("\nDone. Processed {$added} 1929 prisoner(s).");

        return self::SUCCESS;
    }
}
