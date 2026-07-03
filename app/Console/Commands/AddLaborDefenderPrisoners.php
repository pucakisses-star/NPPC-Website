<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Class-war prisoners surfaced from the International Labor Defense's magazine
 * Labor Defender (1926-1937, marxists.org) and verified against public sources.
 * Batch 1: Anita Whitney (California criminal-syndicalism case) and the six
 * "insurrectos" of the 1913 Texas Rangel-Cline case, all freed by the mid-1920s
 * and not already in the database.
 *
 * Dates are set with honest precision. Idempotent: prisoner:add refuses
 * duplicates by name and the variant-name guard skips anyone already recorded.
 */
final class AddLaborDefenderPrisoners extends Command
{
    protected $signature = 'prisoners:add-labor-defender-prisoners';

    protected $description = 'Add class-war prisoners from Labor Defender (Anita Whitney; the 1913 Rangel-Cline defendants)';

    public function handle(): int
    {
        $rangelCase = fn (string $charges) => [[
            'charges' => $charges,
            'convicted' => 'Convicted, 1913',
            'sentence' => 'Sentenced to a long term at the Texas State Penitentiary in Huntsville; freed in 1926, after roughly thirteen years, when Governor Miriam A. Ferguson pardoned the group.',
            'institution_name' => 'Texas State Penitentiary, Huntsville',
            'institution_city' => 'Huntsville',
            'institution_state' => 'Texas',
        ]];
        $rangelCharge = 'Convicted in the 1913 Rangel-Cline case, after a party of revolutionaries crossing Texas to join the Mexican Revolution was captured in a gunfight with Texas authorities in which a deputy sheriff was killed; the convictions were widely attributed to a lack of evidence and anti-Mexican prejudice.';
        $rangelDates = ['incarceration_date' => [1913, null, null], 'release_date' => [1926, null, null]];

        $people = [
            [
                'payload' => [
                    'name' => 'Anita Whitney',
                    'first_name' => 'Anita',
                    'last_name' => 'Whitney',
                    'aka' => 'Charlotte Anita Whitney',
                    'description' => "Charlotte Anita Whitney, a suffragist and social reformer from a prominent California family, was convicted in 1920 under California's Criminal Syndicalism Act for her part in organizing the Communist Labor Party. Sentenced to one to fourteen years at San Quentin, she was released on bond after eleven days pending appeal. The U.S. Supreme Court upheld her conviction in Whitney v. California (1927) — the case of Justice Brandeis's celebrated free-speech concurrence — but weeks later, on 20 June 1927, Governor C. C. Young granted her an unconditional pardon. She remained a leading Communist and civil-liberties figure for the rest of her life.",
                    'state' => 'California',
                    'gender' => 'Female',
                    'ideologies' => ['Communism', 'Socialism'],
                    'affiliation' => ['Communist Labor Party'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => "Convicted on four counts under California's Criminal Syndicalism Act (1919) for helping organize the Communist Labor Party.",
                        'convicted' => 'Convicted February 1920; upheld in Whitney v. California (1927)',
                        'sentence' => 'One to fourteen years at San Quentin; released on bond after eleven days pending appeal, then unconditionally pardoned by Governor C. C. Young on 20 June 1927.',
                        'institution_name' => 'San Quentin State Prison',
                        'institution_city' => 'San Quentin',
                        'institution_state' => 'California',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1920, 2, null], 'release_date' => [1920, 2, null]],
            ],
            [
                'payload' => [
                    'name' => 'Jesús M. Rangel',
                    'first_name' => 'Jesús',
                    'last_name' => 'Rangel',
                    'description' => "Jesús María Rangel was a Mexican revolutionary and Partido Liberal Mexicano militant who, in 1913, led a small party of revolutionaries crossing Texas to join the fight against the Díaz-era regime in Mexico. Captured in a gunfight with Texas authorities in which a deputy sheriff was killed, he and his comrades became the defendants in the Rangel-Cline case, a cause célèbre of the labor and radical left. Held at the Texas State Penitentiary in Huntsville, he was freed in 1926 when Governor Miriam Ferguson pardoned the group.",
                    'state' => 'Texas',
                    'gender' => 'Male',
                    'ideologies' => ['Anarchism'],
                    'affiliation' => ['Partido Liberal Mexicano'],
                    'era' => '1910s',
                    'released' => true,
                    'cases' => $rangelCase($rangelCharge),
                ],
                'dates' => $rangelDates,
            ],
            [
                'payload' => [
                    'name' => 'Charles Cline',
                    'first_name' => 'Charles',
                    'last_name' => 'Cline',
                    'description' => "Charles Cline was an American labor radical convicted alongside Jesús Rangel in the 1913 Rangel-Cline case, after the party of revolutionaries he was travelling with was captured in a Texas gunfight en route to the Mexican Revolution. Imprisoned at Huntsville, he served roughly thirteen years before the group was pardoned in 1926, and afterward told the prisoners' story in the pages of Labor Defender.",
                    'state' => 'Texas',
                    'gender' => 'Male',
                    'ideologies' => ['Socialism', 'Anarchism'],
                    'era' => '1910s',
                    'released' => true,
                    'cases' => $rangelCase($rangelCharge),
                ],
                'dates' => $rangelDates,
            ],
            [
                'payload' => [
                    'name' => 'Abraham Cisneros',
                    'first_name' => 'Abraham',
                    'last_name' => 'Cisneros',
                    'description' => "Abraham Cisneros was one of the Mexican revolutionaries convicted in the 1913 Rangel-Cline case in Texas, captured with the party crossing to join the Mexican Revolution and imprisoned at the Texas State Penitentiary in Huntsville until the group's 1926 pardon.",
                    'state' => 'Texas',
                    'gender' => 'Male',
                    'ideologies' => ['Anarchism'],
                    'affiliation' => ['Partido Liberal Mexicano'],
                    'era' => '1910s',
                    'released' => true,
                    'cases' => $rangelCase($rangelCharge),
                ],
                'dates' => $rangelDates,
            ],
            [
                'payload' => [
                    'name' => 'Pedro Perales',
                    'first_name' => 'Pedro',
                    'last_name' => 'Perales',
                    'description' => "Pedro Perales was one of the Mexican revolutionaries convicted in the 1913 Rangel-Cline case in Texas, captured with the party crossing to join the Mexican Revolution and imprisoned at the Texas State Penitentiary in Huntsville until the group's 1926 pardon.",
                    'state' => 'Texas',
                    'gender' => 'Male',
                    'ideologies' => ['Anarchism'],
                    'affiliation' => ['Partido Liberal Mexicano'],
                    'era' => '1910s',
                    'released' => true,
                    'cases' => $rangelCase($rangelCharge),
                ],
                'dates' => $rangelDates,
            ],
            [
                'payload' => [
                    'name' => 'Jesús González',
                    'first_name' => 'Jesús',
                    'last_name' => 'González',
                    'description' => "Jesús González was one of the Mexican revolutionaries convicted in the 1913 Rangel-Cline case in Texas, captured with the party crossing to join the Mexican Revolution and imprisoned at the Texas State Penitentiary in Huntsville until the group's 1926 pardon.",
                    'state' => 'Texas',
                    'gender' => 'Male',
                    'ideologies' => ['Anarchism'],
                    'affiliation' => ['Partido Liberal Mexicano'],
                    'era' => '1910s',
                    'released' => true,
                    'cases' => $rangelCase($rangelCharge),
                ],
                'dates' => $rangelDates,
            ],
            [
                'payload' => [
                    'name' => 'Leonardo Vásquez',
                    'first_name' => 'Leonardo',
                    'last_name' => 'Vásquez',
                    'description' => "Leonardo Vásquez was one of the Mexican revolutionaries convicted in the 1913 Rangel-Cline case in Texas, captured with the party crossing to join the Mexican Revolution and imprisoned at the Texas State Penitentiary in Huntsville until the group's 1926 pardon.",
                    'state' => 'Texas',
                    'gender' => 'Male',
                    'ideologies' => ['Anarchism'],
                    'affiliation' => ['Partido Liberal Mexicano'],
                    'era' => '1910s',
                    'released' => true,
                    'cases' => $rangelCase($rangelCharge),
                ],
                'dates' => $rangelDates,
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

        $this->info("\nDone. Processed {$added} Labor Defender prisoner(s).");

        return self::SUCCESS;
    }
}
