<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 3 PPs surfaced from the Wikipedia "Political violence in the
 * United States" sweep that weren't already in the DB:
 *
 *   - Tom Manning (1946-2019) — United Freedom Front / Ohio Seven;
 *     life sentence for the 1981 killing of NJ state trooper Philip
 *     Lamonaco; died in custody at FCI Hazelton 2019.
 *
 *   - Carole Manning — UFF co-defendant; convicted of conspiracy /
 *     sedition / RICO; released in the 1990s.
 *
 *   - Víctor Manuel Gerena — Inside man at Wells Fargo's West
 *     Hartford depot in the 1983 $7M heist staged by the Puerto
 *     Rican independentista group Los Macheteros (EPB-Macheteros).
 *     On the FBI Ten Most Wanted Fugitives list from 1984 to 2017
 *     — the longest run in the program's history (32 years). Status
 *     unknown; widely believed to live in Cuba.
 *
 * Era values per project decade-string convention.
 */
final class AddManningGerena extends Command {
    protected $signature = 'archive:add-manning-gerena';
    protected $description = 'Add Tom & Carole Manning (UFF) and Víctor Manuel Gerena (Macheteros)';

    public function handle(): int {
        $added = 0; $skipped = 0;

        foreach ($this->prisoners() as $payload) {
            $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
            if ($exit === self::SUCCESS) {
                $this->info('ADD: '.$payload['name']);
                $added++;
            } else {
                $skipped++;
            }
        }

        $this->info("Done — added {$added}, skipped {$skipped}.");
        return self::SUCCESS;
    }

    /** @return array<int, array<string, mixed>> */
    private function prisoners(): array {
        return [
            [
                'name' => 'Tom Manning',
                'first_name' => 'Tom',
                'last_name' => 'Manning',
                'description' => 'Anti-imperialist political prisoner and a founder of the United Freedom Front (UFF) — a clandestine armed group active in the 1970s–80s that carried out bank robberies, bombings of corporate and military targets connected to U.S. support for South African apartheid, and the December 1981 killing of New Jersey State Trooper Philip Lamonaco during a traffic stop. Captured in 1985 after years underground, Manning was sentenced to life in federal prison plus 80 years in New Jersey. He spent decades in maximum-security CMU units (USP Marion, ADX Florence, USP Hazelton), where he became a respected prison artist whose paintings of fellow political prisoners hung in movement spaces around the country. Died in custody at FCI Hazelton on July 30, 2019.',
                'state' => 'Massachusetts',
                'race' => 'White',
                'gender' => 'Male',
                'birthdate' => '1946-06-28',
                'death_date' => '2019-07-30',
                'ideologies' => ['Anti-imperialist', 'Anti-apartheid', 'Anti-racist'],
                'affiliation' => ['United Freedom Front', 'Ohio Seven'],
                'era' => '1980s',
                'in_custody' => false,
                'released' => false,
                'cases' => [[
                    'institution_name' => 'FCI Hazelton',
                    'institution_state' => 'West Virginia',
                    'charges' => 'Murder of NJ State Trooper Philip Lamonaco (Dec 21, 1981); seditious conspiracy; RICO; conspiracy to bomb military and corporate targets.',
                    'arrest_date' => '1985-04-25',
                    'sentenced_date' => '1987-03-12',
                    'death_in_custody_date' => '2019-07-30',
                    'convicted' => 'Yes.',
                    'sentence' => 'Life federal + 80 years New Jersey; died in custody at FCI Hazelton 2019.',
                ]],
            ],
            [
                'name' => 'Carole Manning',
                'first_name' => 'Carole',
                'last_name' => 'Manning',
                'description' => 'United Freedom Front member and co-defendant of Tom Manning. Convicted in the 1986 federal sedition trial of conspiracy, sedition, and RICO charges in connection with the UFF\'s campaign of bombings against military and corporate targets supporting U.S. wars in Central America and South African apartheid. Sentenced to 15–53 years; released in the 1990s.',
                'state' => 'Massachusetts',
                'race' => 'White',
                'gender' => 'Female',
                'ideologies' => ['Anti-imperialist', 'Anti-apartheid'],
                'affiliation' => ['United Freedom Front', 'Ohio Seven'],
                'era' => '1980s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Massachusetts',
                    'charges' => 'Seditious conspiracy; conspiracy to bomb; RICO.',
                    'arrest_date' => '1985-04-25',
                    'sentenced_date' => '1987-03-12',
                    'convicted' => 'Yes.',
                    'sentence' => '15–53 years federal; released in the 1990s.',
                ]],
            ],
            [
                'name' => 'Víctor Manuel Gerena',
                'first_name' => 'Víctor',
                'middle_name' => 'Manuel',
                'last_name' => 'Gerena',
                'description' => 'Puerto Rican independentista and member of Los Macheteros (Ejército Popular Boricua) who, while working as a security guard at the Wells Fargo armored car depot in West Hartford, Connecticut, on September 12, 1983, helped engineer a $7 million heist — at the time the largest cash robbery in U.S. history. He immediately disappeared. Indicted in 1985 but never located. Gerena was on the FBI Ten Most Wanted Fugitives list from May 14, 1984 until December 14, 2016 — a 32-year stretch that is the longest run in the program\'s history. Federal authorities long believed he was living in Cuba; his current status is unknown.',
                'state' => 'Connecticut',
                'race' => 'Latinx',
                'gender' => 'Male',
                'birthdate' => '1958-06-24',
                'ideologies' => ['Puerto Rican independence', 'Anti-imperialist'],
                'affiliation' => ['Los Macheteros (Ejército Popular Boricua)'],
                'era' => '1980s',
                'in_custody' => false,
                'released' => false,
                'in_exile' => true,
                'cases' => [[
                    'institution_state' => 'Connecticut',
                    'charges' => 'Bank robbery; theft of government property; armed robbery; conspiracy. Indictment under federal seal until 1985.',
                    'arrest_date' => null,
                    'in_exile_since' => '1983-09-12',
                    'convicted' => 'No — indicted but never apprehended.',
                    'sentence' => 'Indicted; on FBI Ten Most Wanted Fugitives from 1984 until 2016 (32-year run, longest in program history).',
                ]],
            ],
        ];
    }
}
