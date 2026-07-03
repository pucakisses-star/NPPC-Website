<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Batch 6 from the ILD's Labor Defender (July-December 1927 issues): the named
 * cases of the second half of 1927 not already recorded.
 *
 *  - The Greco-Carrillo frame-up: two anti-fascist Italian immigrants held for
 *    the Memorial Day 1927 killing of two Bronx fascists, defended by Clarence
 *    Darrow in an ILD campaign, and acquitted in December 1927.
 *  - The Daily Worker "America" poem prosecution: 18-year-old poet David
 *    Gordon and editor William F. Dunne.
 *  - The Mineola furriers' trial: six co-defendants of Ben Gold (already
 *    recorded) convicted on assault charges after the 1926 strike.
 *  - The Cheswick, Pa. miners beaten and jailed after the August 1927 raid on
 *    a Sacco-Vanzetti protest meeting.
 *  - Ella Reeve "Mother" Bloor, jailed at Boston's Sacco-Vanzetti death watch;
 *    Rothschild Francis, the Virgin Islands editor jailed for contempt for
 *    opposing Navy rule; Tony Stafford, the West Virginia miner imprisoned at
 *    Moundsville and then deported; and Stephen Zinich, the Radnik editor held
 *    for deportation.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddLaborDefender1927Cases extends Command
{
    protected $signature = 'prisoners:add-labor-defender-1927';

    protected $description = 'Add 16 prisoners from Labor Defender Jul-Dec 1927 (Greco-Carrillo, America poem, Mineola furriers, Cheswick, Bloor, Francis, Stafford, Zinich)';

    public function handle(): int
    {
        $people = [
            [
                'payload' => [
                    'name' => 'Calogero Greco',
                    'first_name' => 'Calogero',
                    'last_name' => 'Greco',
                    'description' => "Calogero Greco, an anti-fascist Italian immigrant tailor in New York, was arrested with Donato Carrillo and charged with first-degree murder in the Memorial Day 1927 killing of two Bronx Blackshirts — a prosecution the defense showed to be a frame-up built on fascist testimony. Held in the Bronx County Jail for over six months facing the electric chair, the two were defended by Clarence Darrow in a celebrated ILD campaign and acquitted in December 1927.",
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Anti-fascism', 'Anarchism'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Charged with first-degree murder in the Memorial Day 1927 killing of two Bronx fascists — the Greco-Carrillo frame-up.',
                        'convicted' => 'Acquitted, December 1927 (defended by Clarence Darrow)',
                        'sentence' => 'Held over six months in the Bronx County Jail facing the electric chair before acquittal.',
                        'institution_name' => 'Bronx County Jail',
                        'institution_city' => 'Bronx',
                        'institution_state' => 'New York',
                    ]],
                ],
                'dates' => ['arrest_date' => [1927, 6, null], 'release_date' => [1927, 12, null]],
            ],
            [
                'payload' => [
                    'name' => 'Donato Carrillo',
                    'first_name' => 'Donato',
                    'last_name' => 'Carrillo',
                    'description' => "Donato Carrillo, an anti-fascist Italian immigrant in New York, was arrested with Calogero Greco and charged with first-degree murder in the Memorial Day 1927 killing of two Bronx Blackshirts — a frame-up resting on fascist testimony. Held in the Bronx County Jail for over six months facing the electric chair, the two were defended by Clarence Darrow in a celebrated ILD campaign and acquitted in December 1927.",
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Anti-fascism', 'Anarchism'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Charged with first-degree murder in the Memorial Day 1927 killing of two Bronx fascists — the Greco-Carrillo frame-up.',
                        'convicted' => 'Acquitted, December 1927 (defended by Clarence Darrow)',
                        'sentence' => 'Held over six months in the Bronx County Jail facing the electric chair before acquittal.',
                        'institution_name' => 'Bronx County Jail',
                        'institution_city' => 'Bronx',
                        'institution_state' => 'New York',
                    ]],
                ],
                'dates' => ['arrest_date' => [1927, 6, null], 'release_date' => [1927, 12, null]],
            ],
            [
                'payload' => [
                    'name' => 'David Gordon',
                    'first_name' => 'David',
                    'last_name' => 'Gordon',
                    'description' => "David Gordon, an eighteen-year-old worker-poet, was sentenced in 1927 to up to three years in the New York reformatory for his poem \"America,\" published in the Daily Worker — prosecuted as \"indecent literature\" for its bitter image of the country's prostitution to the dollar. The ILD and a broad free-speech campaign fought the case, and he was released in 1928. He went on to a career as a poet and writer.",
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Communism'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Sentenced for publishing the poem "America" in the Daily Worker, prosecuted as indecent literature.',
                        'convicted' => 'Convicted, 1927',
                        'sentence' => 'Up to three years in the New York reformatory; released in 1928 after a free-speech campaign.',
                        'institution_name' => 'New York State Reformatory',
                        'institution_city' => 'Elmira',
                        'institution_state' => 'New York',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1927, null, null], 'release_date' => [1928, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'William F. Dunne',
                    'first_name' => 'William',
                    'last_name' => 'Dunne',
                    'aka' => 'Bill Dunne (Daily Worker editor)',
                    'description' => "William F. Dunne, the veteran labor journalist and editor of the Daily Worker, was sentenced to thirty days in New York City jail in 1927 for publishing David Gordon's poem \"America\" — prosecuted as indecent literature — and faced a further federal indictment over the paper's mailing. Bailed pending appeal, his case was part of the broad free-speech fight around the Daily Worker. He is distinct from the later anarchist prisoner Bill Dunne already in this database.",
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Communism'],
                    'affiliation' => ['Workers (Communist) Party', 'Daily Worker'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Sentenced for publishing the poem "America" in the Daily Worker; further federal indictment over the paper\'s mailing.',
                        'convicted' => 'Convicted, 1927; bailed pending appeal',
                        'sentence' => 'Thirty days in New York City jail; bailed pending appeal.',
                    ]],
                ],
                'dates' => ['arrest_date' => [1927, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Jack Schneider',
                    'first_name' => 'Jack',
                    'last_name' => 'Schneider',
                    'description' => "Jack Schneider was one of the New York fur workers convicted with Ben Gold in the 1927 Mineola trial — the Long Island prosecution of furriers' union militants on assault charges growing out of the victorious 1926 strike. Sentenced to two and a half to five years and jailed through the trial, the defendants were bailed pending appeal in an ILD-supported defense.",
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Communism', 'Labor organizing'],
                    'affiliation' => ['Furriers Union'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => "Convicted in the 1927 Mineola trial of furriers' union militants on assault charges from the 1926 strike.",
                        'convicted' => 'Convicted, 1927; bailed pending appeal',
                        'sentence' => 'Two and a half to five years; jailed during trial and bailed pending appeal.',
                    ]],
                ],
                'dates' => ['arrest_date' => [1927, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Samuel Mencher',
                    'first_name' => 'Samuel',
                    'last_name' => 'Mencher',
                    'description' => "Samuel Mencher (also printed Moncher) was one of the New York fur workers convicted with Ben Gold in the 1927 Mineola trial — the Long Island prosecution of furriers' union militants on assault charges growing out of the victorious 1926 strike. Sentenced to two and a half to five years and jailed through the trial, the defendants were bailed pending appeal in an ILD-supported defense.",
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Communism', 'Labor organizing'],
                    'affiliation' => ['Furriers Union'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => "Convicted in the 1927 Mineola trial of furriers' union militants on assault charges from the 1926 strike.",
                        'convicted' => 'Convicted, 1927; bailed pending appeal',
                        'sentence' => 'Two and a half to five years; jailed during trial and bailed pending appeal.',
                    ]],
                ],
                'dates' => ['arrest_date' => [1927, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Maurice Malkin',
                    'first_name' => 'Maurice',
                    'last_name' => 'Malkin',
                    'description' => "Maurice Malkin, a founding member of the American communist movement and a furriers' union militant, was convicted with Ben Gold in the 1927 Mineola trial on assault charges growing out of the victorious 1926 New York fur strike, sentenced to two and a half to five years, and ultimately served his term at Sing Sing. He later broke with the party and became a government witness in the 1940s-50s.",
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Communism', 'Labor organizing'],
                    'affiliation' => ['Furriers Union', 'Communist Party USA'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => "Convicted in the 1927 Mineola trial of furriers' union militants on assault charges from the 1926 strike.",
                        'convicted' => 'Convicted, 1927',
                        'sentence' => 'Two and a half to five years; served at Sing Sing after appeals failed.',
                        'institution_name' => 'Sing Sing Correctional Facility',
                        'institution_city' => 'Ossining',
                        'institution_state' => 'New York',
                    ]],
                ],
                'dates' => ['arrest_date' => [1927, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Oscar Mileaf',
                    'first_name' => 'Oscar',
                    'last_name' => 'Mileaf',
                    'description' => "Oscar Mileaf was one of the New York fur workers convicted with Ben Gold in the 1927 Mineola trial — the Long Island prosecution of furriers' union militants on assault charges growing out of the victorious 1926 strike. Sentenced to two and a half to five years and jailed through the trial, the defendants were bailed pending appeal in an ILD-supported defense.",
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Communism', 'Labor organizing'],
                    'affiliation' => ['Furriers Union'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => "Convicted in the 1927 Mineola trial of furriers' union militants on assault charges from the 1926 strike.",
                        'convicted' => 'Convicted, 1927; bailed pending appeal',
                        'sentence' => 'Two and a half to five years; jailed during trial and bailed pending appeal.',
                    ]],
                ],
                'dates' => ['arrest_date' => [1927, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Joe Katz',
                    'first_name' => 'Joe',
                    'last_name' => 'Katz',
                    'description' => "Joe Katz was one of the New York fur workers convicted with Ben Gold in the 1927 Mineola trial — the Long Island prosecution of furriers' union militants on assault charges growing out of the victorious 1926 strike. Sentenced to two and a half to five years and jailed through the trial, the defendants were bailed pending appeal in an ILD-supported defense.",
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Communism', 'Labor organizing'],
                    'affiliation' => ['Furriers Union'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => "Convicted in the 1927 Mineola trial of furriers' union militants on assault charges from the 1926 strike.",
                        'convicted' => 'Convicted, 1927; bailed pending appeal',
                        'sentence' => 'Two and a half to five years; jailed during trial and bailed pending appeal.',
                    ]],
                ],
                'dates' => ['arrest_date' => [1927, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Otto Lenhart',
                    'first_name' => 'Otto',
                    'last_name' => 'Lenhart',
                    'description' => "Otto Lenhart was one of the New York fur workers convicted with Ben Gold in the 1927 Mineola trial — the Long Island prosecution of furriers' union militants on assault charges growing out of the victorious 1926 strike. Sentenced to two and a half to five years and jailed through the trial, the defendants were bailed pending appeal in an ILD-supported defense.",
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Communism', 'Labor organizing'],
                    'affiliation' => ['Furriers Union'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => "Convicted in the 1927 Mineola trial of furriers' union militants on assault charges from the 1926 strike.",
                        'convicted' => 'Convicted, 1927; bailed pending appeal',
                        'sentence' => 'Two and a half to five years; jailed during trial and bailed pending appeal.',
                    ]],
                ],
                'dates' => ['arrest_date' => [1927, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Tony De Bernardini',
                    'first_name' => 'Tony',
                    'last_name' => 'De Bernardini',
                    'description' => "Tony De Bernardini, a union coal miner at Cheswick, Pennsylvania, was beaten so badly by state police in the 22 August 1927 raid on a Sacco-Vanzetti protest meeting of striking miners that his skull was fractured — and was then held incommunicado in a Pittsburgh jail on riot charges under \$1,500 bail. The ILD's Labor Defender documented the Cheswick prosecutions, in which more than twenty miners faced six-to-ten-year terms.",
                    'state' => 'Pennsylvania',
                    'gender' => 'Male',
                    'ideologies' => ['Industrial unionism'],
                    'affiliation' => ['United Mine Workers of America'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Jailed on riot charges after the 22 August 1927 state-police raid on a Sacco-Vanzetti protest meeting of striking miners at Cheswick, Pa.; skull fractured by police.',
                        'convicted' => 'Held on riot charges, 1927',
                        'sentence' => 'Held incommunicado in a Pittsburgh jail under \$1,500 bail (disposition not documented).',
                    ]],
                ],
                'dates' => ['arrest_date' => [1927, 8, 22]],
            ],
            [
                'payload' => [
                    'name' => 'Steve Kurepa',
                    'first_name' => 'Steve',
                    'last_name' => 'Kurepa',
                    'description' => "Steve Kurepa, a union coal miner at Cheswick, Pennsylvania, was arrested in the 22 August 1927 state-police raid on a Sacco-Vanzetti protest meeting of striking miners, beaten three times in custody, held under \$3,000 bail on riot charges, and rearrested after his release — one of the Cheswick defendants whose prosecutions the ILD's Labor Defender documented through the winter of 1927.",
                    'state' => 'Pennsylvania',
                    'gender' => 'Male',
                    'ideologies' => ['Industrial unionism'],
                    'affiliation' => ['United Mine Workers of America'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Arrested on riot charges in the 22 August 1927 Cheswick raid; beaten three times in custody and rearrested after release.',
                        'convicted' => 'Held on riot charges, 1927',
                        'sentence' => 'Held under \$3,000 bail; rearrested (disposition not documented).',
                    ]],
                ],
                'dates' => ['arrest_date' => [1927, 8, 22]],
            ],
            [
                'payload' => [
                    'name' => 'Ella Reeve Bloor',
                    'first_name' => 'Ella',
                    'last_name' => 'Bloor',
                    'aka' => 'Mother Bloor',
                    'description' => "Ella Reeve \"Mother\" Bloor, the tireless organizer whose career ran from the Socialist Party's founding through decades of communist and labor campaigning, was arrested at the Boston death-watch pickets for Sacco and Vanzetti in August 1927, jailed at the Joy Street station, and convicted of \"inciting to riot\" — one of some 160 pickets convicted in those final days. It was one of dozens of arrests across her half-century of organizing; she remained a beloved figure of the movement into her eighties.",
                    'state' => 'Massachusetts',
                    'gender' => 'Female',
                    'ideologies' => ['Communism', 'Socialism'],
                    'affiliation' => ['Communist Party USA'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Arrested and convicted of "inciting to riot" at the Boston Sacco-Vanzetti death-watch pickets, August 1927.',
                        'convicted' => 'Convicted, 1927; bailed',
                        'sentence' => 'Jailed at the Joy Street station; among some 160 pickets convicted.',
                    ]],
                ],
                'dates' => ['arrest_date' => [1927, 8, null]],
            ],
            [
                'payload' => [
                    'name' => 'Rothschild Francis',
                    'first_name' => 'Rothschild',
                    'last_name' => 'Francis',
                    'description' => "Rothschild Francis, the crusading Black editor of The Emancipator in the U.S. Virgin Islands and a leader of the fight for civilian government, was jailed for thirty days and fined for contempt in 1927 for his editorials against the U.S. Navy's colonial courts — a press-freedom case the ILD carried to the mainland labor movement. His prosecutions helped galvanize the campaign that eventually ended naval rule in the islands.",
                    'gender' => 'Male',
                    'race' => 'Black',
                    'ideologies' => ['Civil rights', 'Press freedom'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => "Jailed for contempt over editorials in The Emancipator opposing the U.S. Navy's colonial courts in the Virgin Islands.",
                        'convicted' => 'Convicted of contempt, 1927',
                        'sentence' => 'Thirty days and \$200 fine; faced renewed jailing for continued criticism.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1927, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Tony Stafford',
                    'first_name' => 'Tony',
                    'last_name' => 'Stafford',
                    'description' => "Tony Stafford, an Italian-born union coal miner, was imprisoned at the West Virginia penitentiary in Moundsville for his part in the 1920 West Virginia miners' strike and, after serving years, was deported to Italy in July 1925 — his family left behind on ILD relief. The Labor Defender told his story as a warning of what deportation added to the class-war prisoner's sentence.",
                    'state' => 'West Virginia',
                    'gender' => 'Male',
                    'ideologies' => ['Industrial unionism'],
                    'affiliation' => ['United Mine Workers of America'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => "Imprisoned for his part in the 1920 West Virginia miners' strike; deported to Italy on release.",
                        'convicted' => 'Convicted, early 1920s',
                        'sentence' => 'Served years at the West Virginia penitentiary, Moundsville; deported to Italy in July 1925.',
                        'institution_name' => 'West Virginia State Penitentiary',
                        'institution_city' => 'Moundsville',
                        'institution_state' => 'West Virginia',
                    ]],
                ],
                'dates' => ['release_date' => [1925, 7, null]],
            ],
            [
                'payload' => [
                    'name' => 'Stephen Zinich',
                    'first_name' => 'Stephen',
                    'last_name' => 'Zinich',
                    'description' => "Stephen Zinich, editor of the South Slavic communist paper Radnik, was seized in 1927 and held for deportation to the Kingdom of Yugoslavia for his communist activity — a deportation the ILD fought and postponed, warning that handing an editor to the monarchist police meant prison or worse. The outcome of his case is not documented in the magazine.",
                    'gender' => 'Male',
                    'ideologies' => ['Communism'],
                    'affiliation' => ['Workers (Communist) Party', 'Radnik'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Held for deportation to Yugoslavia in 1927 for communist activity as editor of Radnik.',
                        'convicted' => 'Deportation proceedings, 1927; postponed after ILD defense',
                        'sentence' => 'Held in deportation custody; final disposition not documented.',
                    ]],
                ],
                'dates' => ['arrest_date' => [1927, null, null]],
            ],
        ];

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

        $this->info("\nDone. Processed {$added} 1927 case prisoner(s).");

        return self::SUCCESS;
    }
}
