<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 4 1917 NWP suffragist political prisoners + 1 Bonus Army
 * organizer that were surfaced from the Wikipedia "Political Violence
 * in DC" list but weren't already in NPPC's prisoner DB.
 *
 * Idempotent — prisoner:add refuses duplicates by name.
 */
final class AddDcSuffragistsBonus extends Command {
    protected $signature = 'archive:add-dc-suffragists-bonus';
    protected $description = 'Add 4 NWP suffragists + Bonus Army leader John Pace';

    public function handle(): int {
        $added = 0;
        $skipped = 0;

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
                'name' => 'Dora Lewis',
                'first_name' => 'Dora',
                'last_name' => 'Lewis',
                'description' => 'Pennsylvania suffragist and National Woman\'s Party organizer who served as one of the eldest "Silent Sentinels" picketing the Wilson White House for women\'s right to vote. Arrested repeatedly in 1917, she was beaten and tortured during the November 14, 1917 "Night of Terror" at the Occoquan Workhouse in Virginia, where she and other imprisoned suffragists were dragged, choked, and chained to cell bars by guards. She continued organizing for the 19th Amendment until its ratification in 1920.',
                'state' => 'Pennsylvania',
                'race' => 'White',
                'gender' => 'Female',
                'birthdate' => '1862-06-12',
                'death_date' => '1928-10-22',
                'ideologies' => ['Women\'s suffrage', 'Civil disobedience'],
                'affiliation' => ['National Woman\'s Party', 'Silent Sentinels'],
                'era' => '1910s',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'institution_name' => 'Occoquan Workhouse',
                        'institution_state' => 'Virginia',
                        'charges' => 'Obstructing traffic / picketing the White House (Silent Sentinels).',
                        'arrest_date' => '1917-11-10',
                        'sentence' => '60 days; beaten in "Night of Terror" by guards on November 14, 1917.',
                    ],
                ],
            ],
            [
                'name' => 'Mabel Vernon',
                'first_name' => 'Mabel',
                'last_name' => 'Vernon',
                'description' => 'Suffragist, pacifist, and longtime organizer for the National Woman\'s Party who was one of the first Silent Sentinels arrested for picketing the White House. Vernon went on to become a leading peace activist and director of the People\'s Mandate Committee, lobbying for disarmament in the 1930s through 1950s.',
                'state' => 'Delaware',
                'race' => 'White',
                'gender' => 'Female',
                'birthdate' => '1883-09-19',
                'death_date' => '1975-09-02',
                'ideologies' => ['Women\'s suffrage', 'Pacifism'],
                'affiliation' => ['National Woman\'s Party', 'Silent Sentinels'],
                'era' => '1910s',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'charges' => 'Obstructing traffic for picketing the White House.',
                        'arrest_date' => '1917-07-04',
                        'sentence' => '3 days in District Jail (refused to pay $10 fine).',
                    ],
                ],
            ],
            [
                'name' => 'Annie Arniel',
                'first_name' => 'Annie',
                'last_name' => 'Arniel',
                'description' => 'Delaware factory worker and one of the most frequently jailed Silent Sentinels picketing for women\'s suffrage. Arniel served multiple terms in the District Jail and at the Occoquan Workhouse throughout 1917–1919 and joined hunger strikes; she was force-fed by guards.',
                'state' => 'Delaware',
                'race' => 'White',
                'gender' => 'Female',
                'birthdate' => '1873-07-25',
                'death_date' => '1924-12-29',
                'ideologies' => ['Women\'s suffrage'],
                'affiliation' => ['National Woman\'s Party', 'Silent Sentinels'],
                'era' => '1910s',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'institution_name' => 'District Jail / Occoquan Workhouse',
                        'institution_state' => 'District of Columbia',
                        'charges' => 'Obstructing traffic (picketing the White House).',
                        'arrest_date' => '1917-06-26',
                        'sentence' => 'Multiple jail terms over 1917–1919; participated in hunger strikes and was force-fed.',
                    ],
                ],
            ],
            [
                'name' => 'Florence Bayard Hilles',
                'first_name' => 'Florence',
                'middle_name' => 'Bayard',
                'last_name' => 'Hilles',
                'description' => 'Delaware NWP chair and Silent Sentinel from a prominent political family (her father was Thomas F. Bayard, U.S. Secretary of State under Cleveland). Arrested July 13, 1917 picketing the White House, she received a 60-day Occoquan Workhouse sentence; she refused to pay a fine.',
                'state' => 'Delaware',
                'race' => 'White',
                'gender' => 'Female',
                'birthdate' => '1865-04-13',
                'death_date' => '1954-09-03',
                'ideologies' => ['Women\'s suffrage'],
                'affiliation' => ['National Woman\'s Party', 'Silent Sentinels'],
                'era' => '1910s',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'institution_name' => 'Occoquan Workhouse',
                        'institution_state' => 'Virginia',
                        'charges' => 'Obstructing traffic (picketing the White House).',
                        'arrest_date' => '1917-07-13',
                        'sentence' => '60 days at Occoquan Workhouse.',
                    ],
                ],
            ],
            [
                'name' => 'John Pace',
                'first_name' => 'John',
                'last_name' => 'Pace',
                'description' => 'Communist faction leader during the 1932 Bonus Army encampment of unemployed WWI veterans in Washington, DC. Pace headed the Workers Ex-Servicemen\'s League contingent of roughly 200 communist veterans within the larger Bonus Expeditionary Force. When General Douglas MacArthur led active-duty troops to clear the encampment on July 28, 1932, Pace was among those arrested. The violent eviction — which killed two veterans and injured many more — became a defining political scandal of the Hoover administration.',
                'state' => 'Michigan',
                'race' => 'White',
                'gender' => 'Male',
                'ideologies' => ['Communist', 'Veterans\' organizing'],
                'affiliation' => ['Workers Ex-Servicemen\'s League', 'Bonus Army'],
                'era' => '1930s',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'institution_state' => 'District of Columbia',
                        'charges' => 'Various charges arising from the eviction of the Bonus Army encampment by federal troops.',
                        'arrest_date' => '1932-07-28',
                    ],
                ],
            ],
        ];
    }
}
