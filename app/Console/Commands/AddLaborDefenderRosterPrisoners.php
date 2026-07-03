<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Batch 2 from the International Labor Defense's magazine Labor Defender
 * (Vol. 1 No. 1, January 1926, via marxists.org): the class-war prisoners on
 * the ILD's own "Birthdays of Class-War Prisoners" roster and in its case
 * reports who were not already in the database.
 *
 * These men are obscure — most appear in no modern secondary source — so each
 * record claims only what the ILD's primary source attests: the name, the
 * prison, and (for the case reports) the charge. The California men were held
 * at San Quentin and Folsom at the height of the state's criminal-syndicalism
 * prosecutions, which the same issue describes ("men arrested for 'criminal
 * syndicalism' and serving from one to ten years").
 *
 * Already in the database and skipped: Tom Mooney, Richard "Blackie" Ford,
 * Benjamin Gitlow, J. M. Rangel (prisoners:add-labor-defender-prisoners).
 * Deliberately excluded: Fred Merrick (ten-year suspended sentence — not
 * imprisoned in this case).
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddLaborDefenderRosterPrisoners extends Command
{
    protected $signature = 'prisoners:add-labor-defender-roster';

    protected $description = 'Add class-war prisoners from the ILD Labor Defender Jan 1926 roster and case reports';

    public function handle(): int
    {
        $caRoster = function (string $name, string $first, string $last, string $prison, string $city) {
            return [
                'payload' => [
                    'name' => $name,
                    'first_name' => $first,
                    'last_name' => $last,
                    'description' => "{$name} was imprisoned at {$prison} in the mid-1920s, during the era of California's criminal-syndicalism prosecutions that sent more than a hundred labor radicals to San Quentin and Folsom. He appears on the International Labor Defense's roster of class-war prisoners in the first issue of Labor Defender (January 1926); like most of the rank-and-file victims of that campaign, little else about his case entered the historical record.",
                    'state' => 'California',
                    'gender' => 'Male',
                    'ideologies' => ['Industrial unionism'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => "Imprisoned during California's criminal-syndicalism prosecutions of labor radicals; listed by the International Labor Defense as a class-war prisoner (Labor Defender, January 1926).",
                        'convicted' => 'Convicted, California criminal-syndicalism era',
                        'sentence' => "Held at {$prison}; California criminal-syndicalism sentences ran from one to ten years (release date not documented).",
                        'institution_name' => $prison,
                        'institution_city' => $city,
                        'institution_state' => 'California',
                    ]],
                ],
                'dates' => [],
            ];
        };

        $meRoster = function (string $name, string $first, string $last) {
            return [
                'payload' => [
                    'name' => $name,
                    'first_name' => $first,
                    'last_name' => $last,
                    'description' => "{$name} was imprisoned at the Maine State Prison in Thomaston in the mid-1920s and appears on the International Labor Defense's roster of class-war prisoners in the first issue of Labor Defender (January 1926). Like most of the rank-and-file labor prisoners of the period, little else about his case entered the historical record.",
                    'state' => 'Maine',
                    'gender' => 'Male',
                    'ideologies' => ['Industrial unionism'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Listed by the International Labor Defense as a class-war prisoner at the Maine State Prison (Labor Defender, January 1926).',
                        'convicted' => 'Convicted (details not documented)',
                        'sentence' => 'Held at the Maine State Prison, Thomaston (term and release date not documented).',
                        'institution_name' => 'Maine State Prison',
                        'institution_city' => 'Thomaston',
                        'institution_state' => 'Maine',
                    ]],
                ],
                'dates' => [],
            ];
        };

        $people = [
            $caRoster('John Hiza', 'John', 'Hiza', 'Folsom State Prison', 'Folsom'),
            $caRoster('C. F. McGrath', 'C. F.', 'McGrath', 'San Quentin State Prison', 'San Quentin'),
            $caRoster('Henry Matlin', 'Henry', 'Matlin', 'San Quentin State Prison', 'San Quentin'),
            $caRoster('W. Rutherford', 'W.', 'Rutherford', 'San Quentin State Prison', 'San Quentin'),
            $caRoster('Jack Nash', 'Jack', 'Nash', 'San Quentin State Prison', 'San Quentin'),
            $caRoster('F. Franklin', 'F.', 'Franklin', 'San Quentin State Prison', 'San Quentin'),
            $meRoster('Peter Dirks', 'Peter', 'Dirks'),
            $meRoster('Dan Fallon', 'Dan', 'Fallon'),
            [
                'payload' => [
                    'name' => 'John Buksa',
                    'first_name' => 'John',
                    'last_name' => 'Buksa',
                    'description' => "John Buksa was arrested on a Wheeling, West Virginia street car for distributing the program of the Workers (Communist) Party, and was tried and found guilty under West Virginia's \"Red Flag\" law — a conviction for nothing more than handing out political literature. The International Labor Defense took up his appeal, reporting the case in the first issue of Labor Defender (January 1926).",
                    'state' => 'West Virginia',
                    'gender' => 'Male',
                    'ideologies' => ['Communism'],
                    'affiliation' => ['Workers (Communist) Party'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Convicted under West Virginia\'s "Red Flag" law for distributing the Workers Party program on a Wheeling street car.',
                        'convicted' => 'Tried and found guilty; case appealed with ILD defense (Labor Defender, January 1926)',
                        'sentence' => 'Conviction under the Red Flag law; disposition of the appeal not documented.',
                    ]],
                ],
                'dates' => ['arrest_date' => [1925, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'John Merrick',
                    'first_name' => 'John',
                    'last_name' => 'Merrick',
                    'description' => "John Merrick, an active union shoe worker in Haverhill, Massachusetts, was framed up in January 1923 on a charge of placing a bomb in front of a shoe factory — a classic case, as the International Labor Defense reported it, of a militant unionist targeted with a manufactured charge. Tried and convicted, he was out on bail while the case was appealed; the ILD supported the defense (\"The Haverhill Frame-Up,\" Labor Defender, January 1926).",
                    'state' => 'Massachusetts',
                    'gender' => 'Male',
                    'ideologies' => ['Industrial unionism'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Framed up in January 1923 on a charge of placing a bomb in front of a Haverhill shoe factory; an active union man targeted amid shoe-industry labor conflict.',
                        'convicted' => 'Tried and convicted; on bail pending appeal with ILD support (Labor Defender, January 1926)',
                        'sentence' => 'Convicted; released on bail pending appeal (final disposition not documented).',
                    ]],
                ],
                'dates' => ['arrest_date' => [1923, 1, null]],
            ],
            [
                'payload' => [
                    'name' => 'Edward Horacek',
                    'first_name' => 'Edward',
                    'last_name' => 'Horacek',
                    'description' => "Edward Horacek was one of ten workers arrested in a 1923 Pittsburgh red raid and indicted under Pennsylvania's Sedition Act. Tried in late 1925, he was the first of the defendants found guilty; the International Labor Defense reported his conviction and appeal in the first issue of Labor Defender (January 1926).",
                    'state' => 'Pennsylvania',
                    'gender' => 'Male',
                    'ideologies' => ['Communism'],
                    'affiliation' => ['Workers (Communist) Party'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => "Indicted under Pennsylvania's Sedition Act after the April 1923 Pittsburgh red raids; first of the ten defendants to be tried and found guilty.",
                        'convicted' => 'Found guilty, 1925; case appealed (Labor Defender, January 1926)',
                        'sentence' => 'Convicted under the Pennsylvania Sedition Act; disposition of the appeal not documented.',
                    ]],
                ],
                'dates' => ['arrest_date' => [1923, 4, null]],
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

        $this->info("\nDone. Processed {$added} Labor Defender roster prisoner(s).");

        return self::SUCCESS;
    }
}
