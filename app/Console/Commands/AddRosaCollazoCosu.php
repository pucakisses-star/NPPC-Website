<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Final 2 PPs from the deep-crawl of the Wikipedia "Political
 * violence in DC" article:
 *
 *   - Rosa Cortez Collazo — wife of Oscar Collazo; FBI-arrested
 *     on conspiracy suspicion after the November 1, 1950 attempt
 *     on Truman's life at Blair House; held without formal trial
 *     for ~8 months
 *   - Alice Cosu — Silent Sentinel and Alice Paul's Occoquan
 *     Workhouse cellmate; suffered a heart attack from prison
 *     conditions during her sentence
 *
 * Era values per project decade-string convention.
 */
final class AddRosaCollazoCosu extends Command {
    protected $signature = 'archive:add-rosa-collazo-cosu';
    protected $description = 'Add Rosa Cortez Collazo (1950 Blair House) and Alice Cosu (Silent Sentinel)';

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
                'name' => 'Rosa Cortez Collazo',
                'first_name' => 'Rosa',
                'middle_name' => 'Cortez',
                'last_name' => 'Collazo',
                'aka' => 'Rosa Mercedes Cortez',
                'description' => 'Puerto Rican independence activist and wife of Oscar Collazo, one of the two Puerto Rican Nationalist Party members who attempted to assassinate President Harry S. Truman at Blair House on November 1, 1950. Rosa was arrested by the FBI on the day of the attack on suspicion of conspiracy and held in federal custody for approximately eight months without formal trial; she was eventually released without conviction. She remained an outspoken independentista organizer in New York and Puerto Rico for the rest of her life, advocating for her husband\'s release until his sentence was commuted by President Carter in 1979.',
                'state' => 'New York',
                'race' => 'Latinx',
                'gender' => 'Female',
                'ideologies' => ['Puerto Rican independence', 'Nationalist'],
                'affiliation' => ['Puerto Rican Nationalist Party'],
                'era' => '1950s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'New York',
                    'charges' => 'FBI detention on suspicion of conspiracy to assassinate the President (Blair House attack, November 1, 1950).',
                    'arrest_date' => '1950-11-01',
                    'release_date' => '1951-07-01',
                    'convicted' => 'No — held approximately eight months without formal trial; released without conviction.',
                    'sentence' => '~8 months federal detention.',
                ]],
            ],
            [
                'name' => 'Alice Cosu',
                'first_name' => 'Alice',
                'last_name' => 'Cosu',
                'description' => 'New Orleans suffragist and Silent Sentinel. Arrested in 1917 picketing the Wilson White House for women\'s right to vote and sentenced to the Occoquan Workhouse in Virginia, where she was Alice Paul\'s cellmate and was subjected to the brutality of the November 14, 1917 "Night of Terror." Cosu suffered a heart attack from the conditions of her imprisonment and never fully recovered. She continued NWP organizing in Louisiana through the 19th Amendment\'s ratification.',
                'state' => 'Louisiana',
                'race' => 'White',
                'gender' => 'Female',
                'death_date' => '1926-01-01',
                'ideologies' => ['Women\'s suffrage'],
                'affiliation' => ['National Woman\'s Party', 'Silent Sentinels'],
                'era' => '1910s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Occoquan Workhouse',
                    'institution_state' => 'Virginia',
                    'charges' => 'Obstructing traffic (picketing the White House for women\'s suffrage).',
                    'arrest_date' => '1917-11-10',
                    'sentence' => 'Occoquan Workhouse term; suffered a heart attack from prison conditions during incarceration.',
                ]],
            ],
        ];
    }
}
