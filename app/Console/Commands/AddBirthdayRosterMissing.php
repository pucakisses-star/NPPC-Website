<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds the 5 political prisoners flagged NOT FOUND by the birthday
 * audit (against the Jericho Movement / ABCF / NYC ABC canonical
 * lists). One additional name — Joyce Powell — is omitted pending
 * identity confirmation.
 *
 * Already-in-DB names skipped automatically by prisoner:add
 * idempotency.
 */
final class AddBirthdayRosterMissing extends Command {
    protected $signature = 'archive:add-birthday-roster-missing';
    protected $description = 'Add the 5 PPs flagged NOT FOUND by prisoners:audit-birthdays';

    public function handle(): int {
        $added = 0; $skipped = 0;
        foreach ($this->prisoners() as $p) {
            $exit = $this->call('prisoner:add', ['json' => json_encode($p)]);
            if ($exit === self::SUCCESS) { $this->info('ADD: '.$p['name']); $added++; }
            else { $skipped++; }
        }
        $this->info("Done — added {$added}, skipped {$skipped}.");
        return self::SUCCESS;
    }

    /** @return array<int, array<string, mixed>> */
    private function prisoners(): array {
        return [
            [
                'name' => 'Jamil Abdullah Al-Amin',
                'aka' => 'H. Rap Brown',
                'first_name' => 'Jamil',
                'middle_name' => 'Abdullah',
                'last_name' => 'Al-Amin',
                'description' => 'Former SNCC chairman (1967-68), Honorary Prime Minister of the Black Panther Party, and longtime Imam of the Community Mosque of Atlanta. Convicted in 2002 of the killing of Fulton County sheriff\'s deputy Ricky Kinchen during a 2000 attempted arrest in the West End of Atlanta — a case the defense team and longtime Imam supporters have argued was constructed around testimony from a third party (Otis Jackson) who confessed in writing to the killing both before and after Al-Amin\'s trial. Originally sentenced to life without parole at USP ADX Florence; transferred in 2014 to USP Tucson for medical reasons (multiple myeloma).',
                'state' => 'Georgia',
                'race' => 'Black',
                'gender' => 'Male',
                'birthdate' => '1943-10-04',
                'ideologies' => ['Black liberation', 'Islamic'],
                'affiliation' => ['SNCC', 'Black Panther Party', 'Community Mosque of Atlanta'],
                'era' => '2000s',
                'in_custody' => true,
                'released' => false,
                'cases' => [[
                    'institution_name' => 'USP Tucson',
                    'institution_state' => 'Arizona',
                    'charges' => 'Felony murder of Fulton County sheriff\'s deputy Ricky Kinchen (March 16, 2000, Atlanta); state conviction 2002.',
                    'arrest_date' => '2000-03-20',
                    'sentenced_date' => '2002-03-13',
                    'incarceration_date' => '2002-03-13',
                    'convicted' => 'Yes — defense maintains a third party (Otis Jackson) repeatedly confessed.',
                    'sentence' => 'Life without parole.',
                ]],
            ],
            [
                'name' => 'Thomas Manning',
                'first_name' => 'Thomas',
                'last_name' => 'Manning',
                'description' => 'Maine-born Vietnam War combat veteran turned member of the United Freedom Front and the Ohio 7 — the anti-imperialist clandestine group prosecuted in the 1985-89 federal trials for a series of bombings of corporate offices linked to apartheid South Africa and U.S. military buildup, and for the 1981 killing of New Jersey state trooper Philip Lamonaco during a traffic stop. Convicted of the Lamonaco shooting in 1987 and sentenced to 58 years, served first at USP Marion and later FMC Butner. Died in federal custody at FMC Butner on July 30, 2019.',
                'state' => 'Massachusetts',
                'race' => 'White',
                'gender' => 'Male',
                'birthdate' => '1946-06-28',
                'death_date' => '2019-07-30',
                'ideologies' => ['Anti-imperialist', 'Revolutionary socialist'],
                'affiliation' => ['United Freedom Front', 'Ohio 7'],
                'era' => '1980s',
                'in_custody' => false,
                'released' => false,
                'cases' => [[
                    'institution_name' => 'Federal Medical Center, Butner',
                    'institution_state' => 'North Carolina',
                    'charges' => 'Second-degree murder of NJ state trooper Philip Lamonaco (Dec 21, 1981); RICO; United Freedom Front bombings prosecution.',
                    'arrest_date' => '1985-04-24',
                    'sentenced_date' => '1987-03-01',
                    'incarceration_date' => '1985-04-24',
                    'release_date' => '2019-07-30',
                    'convicted' => 'Yes.',
                    'sentence' => '58 years federal; died in custody at FMC Butner July 30, 2019.',
                ]],
            ],
            [
                'name' => 'Charles Sims Africa',
                'first_name' => 'Charles',
                'middle_name' => 'Sims',
                'last_name' => 'Africa',
                'description' => 'Member of the MOVE organization and one of the MOVE 9 — the nine MOVE members convicted in the death of Philadelphia police officer James Ramp during the August 8, 1978 police raid on the MOVE house at 309 N. 33rd Street. All nine were sentenced to 30-100 years. Charles served 41 years at SCI Dallas before parole in January 2019. Died February 11, 2020 at age 59 from complications of cancer first diagnosed and inadequately treated inside the PA Department of Corrections.',
                'state' => 'Pennsylvania',
                'race' => 'Black',
                'gender' => 'Male',
                'birthdate' => '1960-04-17',
                'death_date' => '2020-02-11',
                'ideologies' => ['MOVE', 'Anti-system'],
                'affiliation' => ['MOVE', 'MOVE 9'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'SCI Dallas',
                    'institution_state' => 'Pennsylvania',
                    'charges' => 'Third-degree murder of PPD officer James Ramp (Aug 8, 1978 MOVE house raid).',
                    'arrest_date' => '1978-08-08',
                    'sentenced_date' => '1980-08-04',
                    'incarceration_date' => '1980-08-04',
                    'release_date' => '2019-01-08',
                    'convicted' => 'Yes.',
                    'sentence' => '30-100 years; paroled January 8, 2019 after 41 years.',
                ]],
            ],
            [
                'name' => 'Maumin Khabir',
                'aka' => 'Melvin Mayes',
                'first_name' => 'Maumin',
                'last_name' => 'Khabir',
                'description' => 'New Afrikan revolutionary and former member of the El-Rukn organization in Chicago. Convicted in the 1987 federal "El-Rukn / Libya" prosecution that charged El-Rukn members with conspiring to commit acts of terrorism on U.S. soil on behalf of the Libyan government. Sentenced to 35 years federal; spent decades at USP Marion and later USP Florence ADX. Imam of the Muslim community inside USP Florence until his 2023 release on parole.',
                'state' => 'Illinois',
                'race' => 'Black',
                'gender' => 'Male',
                'birthdate' => '1953-09-15',
                'ideologies' => ['New Afrikan', 'Islamic'],
                'affiliation' => ['El-Rukn'],
                'era' => '1980s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Illinois',
                    'charges' => 'Federal seditious conspiracy; conspiracy to bomb on behalf of a foreign government (1987 El-Rukn / Libya prosecution).',
                    'arrest_date' => '1986-10-31',
                    'sentenced_date' => '1987-11-24',
                    'incarceration_date' => '1986-10-31',
                    'release_date' => '2023-05-01',
                    'convicted' => 'Yes.',
                    'sentence' => '35 years federal; paroled 2023.',
                ]],
            ],
            // Alexander Contompasis is already covered by
            // archive:add-nyc-abc-prisoners (run that command on prod
            // if his record is missing). Adding birthdate here too in
            // case he was added without one.
            [
                'name' => 'Alexander Contompasis',
                'aka' => 'Alex Stokes',
                'first_name' => 'Alexander',
                'last_name' => 'Contompasis',
                'description' => 'Antifascist journalist charged in the long-delayed Albany County, New York, prosecution arising from a January 6, 2021 counter-protest in Albany during which a Proud Boy tased a Black man. Sentenced to 20 years at Sing Sing Correctional Facility; the prosecution and the steep sentence have been the subject of an active Free Alex Stokes defense campaign.',
                'state' => 'New York',
                'race' => 'White',
                'gender' => 'Male',
                'birthdate' => '1990-02-26',
                'ideologies' => ['Anti-fascism'],
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'website' => 'https://freealexstokes.com',
                'inmate_number' => '22-B-5028',
                'cases' => [[
                    'institution_name' => 'Sing Sing Correctional Facility',
                    'institution_state' => 'New York',
                    'charges' => 'Multiple counts arising from a January 6, 2021 Proud Boys counter-protest in Albany, NY.',
                    'arrest_date' => '2021-01-06',
                    'sentenced_date' => '2022-11-01',
                    'convicted' => 'Yes.',
                    'sentence' => '20 years state.',
                ]],
            ],
        ];
    }
}
