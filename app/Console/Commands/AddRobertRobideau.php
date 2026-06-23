<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds American Indian Movement activist Robert Robideau (1946–2009), a cousin of
 * Leonard Peltier and a defendant in the RESMURS case from the June 26, 1975
 * Pine Ridge shootout. Arrested after the September 10, 1975 Kansas Turnpike
 * explosion, he was tried with Darrelle "Dino" Butler in Cedar Rapids, Iowa and
 * acquitted on grounds of self-defense in July 1976; he later directed the
 * Leonard Peltier Defense Committee. Delegates to prisoner:add, which refuses
 * duplicates by name, so re-running is safe. (His portrait is attached separately
 * by prisoners:attach-nonfree-photos.)
 */
final class AddRobertRobideau extends Command
{
    protected $signature = 'prisoners:add-robert-robideau';

    protected $description = 'Add AIM activist Robert Robideau (RESMURS defendant, acquitted 1976) as a political prisoner';

    public function handle(): int
    {
        $payload = [
            'name' => 'Robert Robideau',
            'first_name' => 'Robert',
            'last_name' => 'Robideau',
            'aka' => 'Robert Eugene Robideau; Bob Robideau',
            'gender' => 'Male',
            'race' => 'Native American',
            'birthdate' => '1946-11-11',
            'death_date' => '2009-02-17',
            'state' => 'South Dakota',
            'era' => '1970s',
            'ideologies' => ['Native American sovereignty', 'Indigenous rights'],
            'affiliation' => ['American Indian Movement'],
            'in_custody' => false,
            'released' => true,
            'description' => 'Robert Eugene Robideau (November 11, 1946 – February 17, 2009) was an American Indian '
                .'Movement (AIM) activist and a cousin of Leonard Peltier. He was one of the defendants in the RESMURS '
                .'case arising from the June 26, 1975 shootout at the Jumping Bull compound on the Pine Ridge '
                .'Reservation, in which FBI Special Agents Jack Coler and Ronald Williams were killed. Robideau was '
                .'arrested after the September 10, 1975 explosion of a station wagon on the Kansas Turnpike — where one '
                .'of the dead agents\' rifles was recovered — and was tried with Darrelle "Dino" Butler in Cedar '
                .'Rapids, Iowa. In July 1976 a federal jury acquitted both men on grounds of self-defense; Peltier, '
                .'tried separately, was convicted and imprisoned. Robideau went on to direct the Leonard Peltier '
                .'Defense Committee and worked as an artist. He died in Barcelona, Spain in 2009 at age 62, reportedly '
                .'of seizures linked to shrapnel left in his brain by the 1975 explosion.',
            'cases' => [[
                'charges' => 'Charged in the deaths of FBI Special Agents Jack Coler and Ronald Williams in the June '
                    .'26, 1975 shootout at the Jumping Bull compound, Pine Ridge Reservation. He was arrested after '
                    .'the September 10, 1975 Kansas Turnpike explosion that tied AIM members to the agents\' weapons.',
                'convicted' => 'No — acquitted (with Darrelle "Dino" Butler) by a federal jury in Cedar Rapids, Iowa '
                    .'in July 1976 on grounds of self-defense.',
                'arrest_date' => '1975-09-10',
                'incarceration_date' => '1975-09-10',
                'release_date' => '1976-07-16',
            ]],
        ];

        return $this->call('prisoner:add', ['json' => json_encode($payload)]);
    }
}
