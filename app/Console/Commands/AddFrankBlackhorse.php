<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds American Indian Movement activist Frank Blackhorse (also known as Frank
 * DeLuca and Frank Black Horse) as a political prisoner. He was charged with
 * wounding FBI Special Agent Curtis Fitzgerald during the 1973 occupation of
 * Wounded Knee, jumped bond and became a fugitive, was named a suspect in the
 * RESMURS investigation of the June 26, 1975 Pine Ridge shootout, and was
 * arrested alongside Leonard Peltier in Alberta on February 6, 1976 and held at
 * the Oakalla Prison Farm in British Columbia before the RESMURS charges against
 * him were dropped. Delegates to prisoner:add, which refuses duplicates by name,
 * so re-running is safe.
 */
final class AddFrankBlackhorse extends Command
{
    protected $signature = 'prisoners:add-frank-blackhorse';

    protected $description = 'Add AIM activist Frank Blackhorse (Frank DeLuca / Frank Black Horse) as a political prisoner';

    public function handle(): int
    {
        $payload = [
            'name' => 'Frank Blackhorse',
            'first_name' => 'Frank',
            'last_name' => 'Blackhorse',
            'aka' => 'Frank DeLuca; Frank Black Horse; Francis Blackhorse',
            'gender' => 'Male',
            'race' => 'Native American',
            'state' => 'South Dakota',
            'era' => '1970s',
            'ideologies' => ['Native American sovereignty', 'Indigenous rights'],
            'affiliation' => ['American Indian Movement'],
            'in_custody' => false,
            'released' => true,
            'description' => 'Frank Blackhorse — who also used the names Frank DeLuca and Frank Black Horse — was an '
                .'American Indian Movement (AIM) activist caught up in the movement\'s confrontations with federal '
                .'authorities in the early 1970s. He was arrested and charged with wounding FBI Special Agent Curtis '
                .'Fitzgerald during the 1973 occupation of Wounded Knee, South Dakota; released on bond, he failed to '
                .'appear and became a fugitive. He was later named a suspect in the RESMURS investigation into the '
                .'June 26, 1975 shootout at the Jumping Bull compound on the Pine Ridge Reservation, in which FBI '
                .'agents Jack Coler and Ronald Williams and AIM member Joe Stuntz were killed. On February 6, 1976, '
                .'Blackhorse was arrested alongside Leonard Peltier by the Royal Canadian Mounted Police near Hinton, '
                .'Alberta, and held at the Oakalla Prison Farm in British Columbia. The RESMURS charges against him '
                .'were dropped; accounts differ on whether he was ever extradited to the United States, and his later '
                .'whereabouts are not publicly known.',
            'cases' => [[
                'institution_name' => 'Oakalla Prison Farm',
                'institution_city' => 'Burnaby',
                'institution_state' => 'British Columbia',
                'charges' => 'Charged with wounding FBI Special Agent Curtis Fitzgerald during the March 1973 '
                    .'occupation of Wounded Knee, South Dakota; later named a suspect in the RESMURS investigation of '
                    .'the June 26, 1975 Pine Ridge shootout that killed FBI agents Jack Coler and Ronald Williams.',
                'convicted' => 'No — released on bond after his 1973 arrest, he failed to appear and became a '
                    .'fugitive. Arrested with Leonard Peltier in Alberta on February 6, 1976 and held at the Oakalla '
                    .'Prison Farm; the RESMURS charges against him were dropped and he was not convicted in connection '
                    .'with the shootout.',
                'arrest_date' => '1976-02-06',
            ]],
        ];

        return $this->call('prisoner:add', ['json' => json_encode($payload)]);
    }
}
