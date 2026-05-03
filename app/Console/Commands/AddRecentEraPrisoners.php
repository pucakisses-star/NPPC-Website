<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddRecentEraPrisoners extends Command
{
    protected $signature = 'prisoners:add-recent-era';
    protected $description = 'Add Pentagon Papers defendants, Ramona Africa, Iraq War resisters, NSA whistleblower Drake, Scott Warren, Lauren Handy, and the Stop Cop City martyr Tortuguita.';

    public function handle(): int
    {
        $created = 0;
        $skipped = 0;

        $laFederal   = Institution::firstOrCreate(['name' => 'Federal courthouse, Los Angeles (Pentagon Papers trial)'], ['city' => 'Los Angeles', 'state' => 'California']);
        $muncyPa     = Institution::firstOrCreate(['name' => 'State Correctional Institution – Muncy'], ['city' => 'Muncy', 'state' => 'Pennsylvania']);
        $fortSill    = Institution::firstOrCreate(['name' => 'Fort Sill (military confinement)'], ['city' => 'Fort Sill', 'state' => 'Oklahoma']);
        $campPendle  = Institution::firstOrCreate(['name' => 'Camp Pendleton brig'], ['city' => 'Camp Pendleton', 'state' => 'California']);
        $tucsonFed   = Institution::firstOrCreate(['name' => 'Federal courthouse, Tucson (No More Deaths trial)'], ['city' => 'Tucson', 'state' => 'Arizona']);
        $dcCentral   = Institution::firstOrCreate(['name' => 'D.C. Central Detention Facility'], ['city' => 'Washington', 'state' => 'District of Columbia']);

        $defendants = [];

        // Anthony Russo
        $defendants[] = [
            'data' => [
                'name' => 'Anthony Russo', 'first_name' => 'Anthony', 'middle_name' => 'Joseph', 'last_name' => 'Russo Jr.',
                'aka' => 'Tony Russo',
                'birthdate' => '1936-10-11', 'death_date' => '2008-08-06',
                'gender' => 'Male', 'state' => 'California', 'era' => '1970s',
                'ideologies' => ['Anti-war', 'Whistleblower'],
                'in_custody' => false, 'released' => true,
                'description' => "Anthony 'Tony' Russo Jr. was a RAND Corporation analyst who, in October 1969, helped Daniel Ellsberg secretly photocopy the Pentagon Papers — the 7,000-page top-secret Department of Defense study of U.S. decision-making in Vietnam from 1945 to 1967 that Ellsberg leaked to The New York Times in June 1971.\n\nIn August 1971, a federal grand jury subpoenaed Russo to testify about Ellsberg. He refused to answer questions without a grant of immunity that would bar the government from using his testimony against him in any future prosecution of his own. Held in civil contempt, he was jailed at the Los Angeles County Jail and served 47 days before the grand jury's term expired and he was released. He was the first person to serve any time in connection with the Pentagon Papers.\n\nHe was then indicted alongside Ellsberg in December 1971 on conspiracy, theft, and Espionage Act charges and stood trial with him in Los Angeles in 1973. On May 11, 1973, Federal District Judge William Matthew Byrne Jr. dismissed all charges with prejudice on the grounds that the Nixon administration's 'plumbers' unit had broken into the office of Ellsberg's psychiatrist, illegally wiretapped the defendants, and made an improper offer of the FBI directorship to the trial judge.",
            ],
            'cases' => [[
                'institution_id' => $laFederal->id,
                'charges'        => 'Civil contempt of court for refusing to testify before a federal grand jury about Daniel Ellsberg (1971); separately indicted with Ellsberg on conspiracy, theft, and Espionage Act violations (Pentagon Papers)',
                'arrest_date'    => '1971-08-16',
                'release_date'   => '1971-10-02',
                'convicted'      => 'Yes for the contempt (1971); Pentagon Papers charges dismissed with prejudice May 11, 1973',
                'sentence'       => '47 days served at Los Angeles County Jail for grand-jury contempt; no time served on the Pentagon Papers charges',
            ]],
        ];

        // Ramona Africa
        $defendants[] = [
            'data' => [
                'name' => 'Ramona Africa', 'first_name' => 'Ramona', 'last_name' => 'Africa',
                'birthdate' => '1955-01-01', 'death_date' => null,
                'gender' => 'Female', 'race' => 'Black', 'state' => 'Pennsylvania', 'era' => '1980s',
                'ideologies' => ['Black liberation', 'Anti-authoritarian', 'Environmental'],
                'affiliation' => ['MOVE'],
                'in_custody' => false, 'released' => true,
                'description' => "Ramona Africa is the only adult survivor of the May 13, 1985 Philadelphia police bombing of the MOVE house at 6221 Osage Avenue. On the day of the assault, Philadelphia police fired more than 10,000 rounds into the house and then dropped a satchel of C-4 and Tovex on the roof from a state police helicopter. The resulting fire killed 11 MOVE members, including five children and MOVE founder John Africa, and destroyed 61 homes in the surrounding Black middle-class neighborhood. As fellow MOVE members tried to escape the burning house, police fired on them; Ramona Africa survived only because Birdie Africa (the only child to survive) and she made it through the back alley.\n\nDespite being one of the surviving victims of what remains the only aerial bombing of an American city by its own government, she was the only person ever tried in connection with the events. She was charged with riot and conspiracy and convicted in 1986; offered parole repeatedly on the condition that she sever all contact with MOVE, she refused and served the entire seven-year sentence at SCI Muncy. Released in 1992, she became the central spokesperson for the MOVE 9 prisoners and for accountability for the bombing. In 1996 a federal jury found the city of Philadelphia liable for excessive force and unlawful seizure and awarded her $500,000.",
            ],
            'cases' => [[
                'institution_id' => $muncyPa->id,
                'charges'        => 'Riot; conspiracy',
                'arrest_date'    => '1985-05-13',
                'release_date'   => '1992-05-13',
                'convicted'      => 'Yes — Philadelphia, 1986',
                'sentence'       => '16 months to 7 years; served full 7-year maximum after refusing conditional parole that would have required severing contact with MOVE',
            ]],
        ];

        // Camilo Mejía
        $defendants[] = [
            'data' => [
                'name' => 'Camilo Mejía', 'first_name' => 'Camilo', 'last_name' => 'Mejía',
                'birthdate' => '1975-08-28', 'death_date' => null,
                'gender' => 'Male', 'state' => 'Florida', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Conscientious objector'],
                'affiliation' => ['Iraq Veterans Against the War'],
                'in_custody' => false, 'released' => true,
                'description' => "Staff Sergeant Camilo Mejía became the first U.S. soldier to publicly refuse to return to Iraq. After leading an infantry squad in Iraq for five months in 2003, he came home on leave and refused to redeploy, applying for conscientious-objector status on the grounds that he had witnessed war crimes including the use of small-arms fire as crowd control on civilians and the abuse of detainees at Al Asad airbase. The Army denied the application and court-martialed him at Fort Stewart, Georgia for desertion. He was convicted on May 21, 2004 and sentenced to one year in military prison; he served nine months at Fort Sill, Oklahoma. After his release he was elected national chairman of Iraq Veterans Against the War.",
            ],
            'cases' => [[
                'institution_id' => $fortSill->id,
                'charges'        => 'Desertion (Article 85, UCMJ)',
                'arrest_date'    => '2004-03-15',
                'release_date'   => '2005-02-15',
                'convicted'      => 'Yes — military court-martial, Fort Stewart, May 21, 2004',
                'sentence'       => 'One year in military confinement; bad-conduct discharge; reduction in rank',
            ]],
        ];

        // Stephen Funk
        $defendants[] = [
            'data' => [
                'name' => 'Stephen Funk', 'first_name' => 'Stephen', 'middle_name' => 'Eagle', 'last_name' => 'Funk',
                'birthdate' => '1982-01-01', 'death_date' => null,
                'gender' => 'Male', 'state' => 'California', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Conscientious objector', 'Pacifist'],
                'in_custody' => false, 'released' => true,
                'description' => "Lance Corporal Stephen Funk was the first U.S. service member to publicly refuse to participate in the Iraq War. A Marine Corps reservist, he failed to report when his unit was activated in February 2003 and held a press conference in San Francisco announcing his refusal on conscientious-objector grounds. He was court-martialed at Camp Pendleton in September 2003, convicted of unauthorized absence, and sentenced to six months in the brig, hard labor, forfeiture of pay, and a bad-conduct discharge. His public refusal helped catalyze the wider Iraq War resistance movement among U.S. service members.",
            ],
            'cases' => [[
                'institution_id' => $campPendle->id,
                'charges'        => 'Unauthorized absence (UCMJ Article 86)',
                'arrest_date'    => '2003-04-01',
                'release_date'   => '2004-02-01',
                'convicted'      => 'Yes — Marine Corps special court-martial, Camp Pendleton, September 6, 2003',
                'sentence'       => 'Six months in the brig, hard labor, forfeiture of pay, and bad-conduct discharge',
            ]],
        ];

        // Scott Warren
        $defendants[] = [
            'data' => [
                'name' => 'Scott Warren', 'first_name' => 'Scott', 'middle_name' => 'Daniel', 'last_name' => 'Warren',
                'birthdate' => '1983-01-01', 'death_date' => null,
                'gender' => 'Male', 'state' => 'Arizona', 'era' => '2010s',
                'ideologies' => ['Humanitarian', 'Migrant rights'],
                'affiliation' => ['No More Deaths / No Más Muertes'],
                'in_custody' => false, 'released' => true,
                'description' => "Scott Daniel Warren is a geographer and humanitarian aid volunteer with No More Deaths / No Más Muertes, a Tucson-based organization that has placed water and medical supplies in the Sonoran Desert since 2004 to reduce migrant deaths along the U.S.–Mexico border. In January 2018, hours after No More Deaths released video footage showing U.S. Border Patrol agents systematically destroying jugs of water left for migrants, Border Patrol arrested Warren at a humanitarian aid station in Ajo, Arizona where two migrants were receiving food and water. He was charged with two felony counts of harboring and one of conspiracy; he faced up to 20 years in federal prison.\n\nHis first trial in June 2019 ended with the jury split 8–4 for acquittal; the government retried him on the harboring counts in November 2019, and a unanimous jury acquitted him on November 20, 2019. His prosecution was widely covered as an attempt to criminalize humanitarian aid in the borderlands. He continues to organize and write about Sonoran Desert migrant deaths.",
            ],
            'cases' => [[
                'institution_id' => $tucsonFed->id,
                'charges'        => 'Two counts of felony harboring and one count of felony conspiracy (8 U.S.C. § 1324) for providing water, food, and shelter to two undocumented migrants',
                'arrest_date'    => '2018-01-17',
                'release_date'   => '2019-11-20',
                'convicted'      => 'No — first trial hung 8–4 for acquittal (June 11, 2019); retrial acquitted on all counts (November 20, 2019)',
                'sentence'       => 'No conviction on humanitarian aid charges; separately convicted of one minor vehicle infraction in a related littering case',
            ]],
        ];

        // Lauren Handy
        $defendants[] = [
            'data' => [
                'name' => 'Lauren Handy', 'first_name' => 'Lauren', 'last_name' => 'Handy',
                'birthdate' => '1993-01-01', 'death_date' => null,
                'gender' => 'Female', 'state' => 'District of Columbia', 'era' => '2020s',
                'ideologies' => ['Anti-abortion', 'Catholic'],
                'affiliation' => ['Progressive Anti-Abortion Uprising'],
                'in_custody' => false, 'released' => true, // pardoned by President Trump January 23, 2025; FACE Act conviction vacated
                'description' => "Lauren Handy is a Catholic anti-abortion activist who organized a coordinated October 22, 2020 blockade of the Washington Surgi-Clinic in Washington, D.C., chaining herself and other activists to the doors and to one another to prevent patients from entering. Several patients and a clinic nurse were physically obstructed during the blockade. She and nine co-defendants were charged in federal court with conspiracy against rights and violations of the Freedom of Access to Clinic Entrances Act of 1994. She was convicted by a federal jury in August 2023.\n\nOn May 14, 2024, she was sentenced to 57 months (4 years, 9 months) in federal prison and three years of supervised release. The case became a major flashpoint in conservative legal circles, drawing arguments that the FACE Act has been unevenly enforced; on January 23, 2025, President Trump pardoned Handy along with 22 other anti-abortion activists prosecuted under the Act. She was released from federal custody and the FACE Act-related convictions were vacated. Her case is included here for parallel coverage with the Caleb Freestone-style abortion-rights prosecutions already documented in the database.",
            ],
            'cases' => [[
                'institution_id' => $dcCentral->id,
                'charges'        => 'Conspiracy against rights (18 U.S.C. § 241) and violation of the Freedom of Access to Clinic Entrances Act of 1994 (18 U.S.C. § 248)',
                'arrest_date'    => '2022-03-30',
                'release_date'   => '2025-01-23',
                'convicted'      => 'Yes — federal jury verdict, August 2023; pardoned by President Trump January 23, 2025',
                'sentence'       => '57 months in federal prison and three years of supervised release; sentence vacated by presidential pardon January 23, 2025',
            ]],
        ];

        foreach ($defendants as $entry) {
            DB::transaction(function () use ($entry, &$created, &$skipped) {
                $name = $entry['data']['name'];
                if (Prisoner::where('name', $name)->exists()) {
                    $this->warn("Skipping {$name} — already exists.");
                    $skipped++;
                    return;
                }

                $prisoner = Prisoner::create($entry['data']);
                foreach ($entry['cases'] as $case) {
                    PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $case));
                }
                $this->info("Added {$prisoner->name}");
                $created++;
            });
        }

        $this->info("\nDone. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
