<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds American Indian Movement activist Darrelle Dean "Dino" Butler (b. 1942),
 * co-defendant of Robert Robideau in the 1975 Pine Ridge / RESMURS case. Two
 * cases:
 *   1. RESMURS: present and armed at the June 26, 1975 Jumping Bull shootout;
 *      arrested September 5, 1975; indicted November 17, 1975 with Peltier,
 *      Robideau and Jimmy Eagle; tried with Robideau before Judge Edward McManus
 *      in Cedar Rapids, Iowa and acquitted July 16, 1976 on self-defense grounds.
 *   2. A February 23, 1981 arrest in Vancouver, British Columbia (with his cousin
 *      Gary Butler) on attempted-murder/weapons charges after a police pursuit;
 *      he was held in the Canadian system 1981–1984 (Oakalla, then Kent). The
 *      verdict is disputed in the sources (a 1982 report had him convicted on a
 *      reduced "attempting to wound" charge; Butler said he was acquitted after
 *      refusing to participate). The 1984 release date is approximate.
 *
 * No death is documented (he appears to have remained living), so death_date is
 * left unset. Delegates to prisoner:add, which refuses duplicates by name.
 */
final class AddDinoButler extends Command
{
    protected $signature = 'prisoners:add-dino-butler';

    protected $description = 'Add AIM activist Darrelle "Dino" Butler (RESMURS co-defendant) as a political prisoner';

    public function handle(): int
    {
        $payload = [
            'name' => 'Darrelle "Dino" Butler',
            'first_name' => 'Darrelle',
            'last_name' => 'Butler',
            'aka' => 'Dino Butler; Darelle Butler; Darrelle Dean Butler',
            'gender' => 'Male',
            'race' => 'Native American',
            'birthdate' => '1942-04-08',
            'state' => 'South Dakota',
            'era' => '1970s',
            'ideologies' => ['Native American sovereignty', 'Indigenous rights'],
            'affiliation' => ['American Indian Movement'],
            'in_custody' => false,
            'released' => true,
            'description' => 'Darrelle Dean "Dino" Butler (born April 8, 1942 in Portland, Oregon) is a Tututni '
                .'(Confederated Tribes of Siletz) member and American Indian Movement (AIM) activist. He was present '
                .'and armed at the June 26, 1975 shootout at the Jumping Bull compound on the Pine Ridge Reservation, '
                .'in which FBI Special Agents Jack Coler and Ronald Williams were killed. Arrested on September 5, '
                .'1975 and indicted that November with Leonard Peltier, Robert Robideau and Jimmy Eagle, Butler was '
                .'tried with Robideau before Judge Edward McManus in Cedar Rapids, Iowa and acquitted on July 16, 1976 '
                .'on grounds of self-defense; Peltier, tried separately, was convicted. In 1981, traveling to a '
                .'ceremony in Canada, Butler and his cousin Gary were arrested after a February 23 police pursuit in '
                .'Vancouver and charged with attempted murder; he was imprisoned in British Columbia from 1981 to 1984 '
                .'(held at Oakalla and Kent), where his hunger strikes for religious rights won the first Indigenous '
                .'ceremonies ever permitted in the Canadian prison system. He later returned to the Siletz area of '
                .'Oregon, co-founded the Oregon Native Youth Council, and remained active in the campaign to free '
                .'Leonard Peltier.',
            'cases' => [
                [
                    'charges' => 'Two counts of first-degree murder and aiding and abetting (with Leonard Peltier, '
                        .'Robert Robideau and Jimmy Eagle) in the deaths of FBI Special Agents Jack Coler and Ronald '
                        .'Williams at the June 26, 1975 shootout at the Jumping Bull compound, Pine Ridge Reservation. '
                        .'Indicted November 17, 1975.',
                    'convicted' => 'No — tried with Robert Robideau before Judge Edward McManus in Cedar Rapids, Iowa '
                        .'and acquitted on July 16, 1976 on grounds of self-defense (the jury accepted that the '
                        .'atmosphere of violence on Pine Ridge gave the defendants reason to fear the unmarked cars).',
                    'arrest_date' => '1975-09-05',
                    'incarceration_date' => '1975-09-05',
                    'release_date' => '1976-07-16',
                ],
                [
                    'institution_name' => 'Oakalla Prison Farm',
                    'institution_city' => 'Burnaby',
                    'institution_state' => 'British Columbia',
                    'charges' => 'Attempted murder and weapons offenses after a February 23, 1981 police pursuit in '
                        .'Vancouver, British Columbia, in which Butler and his cousin Gary Butler fled and their car '
                        .'overturned; he said he faced two life terms.',
                    'convicted' => 'Disputed. He was held in the British Columbia prison system from 1981 to 1984 '
                        .'(at Oakalla, then Kent), waging hunger strikes for the right to ceremony. A 1982 account '
                        .'reported a conviction on a reduced charge of attempting to wound, while Butler later said he '
                        .'and his cousin were acquitted after refusing to participate in the trial. He was released in '
                        .'1984 (exact date undocumented).',
                    'arrest_date' => '1981-02-23',
                    'incarceration_date' => '1981-02-23',
                    'release_date' => '1984-01-01',
                ],
            ],
        ];

        return $this->call('prisoner:add', ['json' => json_encode($payload)]);
    }
}
