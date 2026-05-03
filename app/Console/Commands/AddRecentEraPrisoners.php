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
        $fortLewis   = Institution::firstOrCreate(['name' => 'Fort Lewis (military confinement)'], ['city' => 'Fort Lewis', 'state' => 'Washington']);
        $sanDiego    = Institution::firstOrCreate(['name' => 'Naval Station San Diego (military confinement)'], ['city' => 'San Diego', 'state' => 'California']);
        $campPendle  = Institution::firstOrCreate(['name' => 'Camp Pendleton brig'], ['city' => 'Camp Pendleton', 'state' => 'California']);
        $bopGeneric  = Institution::firstOrCreate(['name' => 'Federal Bureau of Prisons (low-security)'], ['city' => null, 'state' => null]);
        $tucsonFed   = Institution::firstOrCreate(['name' => 'Federal courthouse, Tucson (No More Deaths trial)'], ['city' => 'Tucson', 'state' => 'Arizona']);
        $dcCentral   = Institution::firstOrCreate(['name' => 'D.C. Central Detention Facility'], ['city' => 'Washington', 'state' => 'District of Columbia']);
        $atlantaForest = Institution::firstOrCreate(['name' => 'Atlanta Public Safety Training Center forest (police killing)'], ['city' => 'DeKalb County', 'state' => 'Georgia']);

        $defendants = [];

        // Daniel Ellsberg
        $defendants[] = [
            'data' => [
                'name' => 'Daniel Ellsberg', 'first_name' => 'Daniel', 'last_name' => 'Ellsberg',
                'birthdate' => '1931-04-07', 'death_date' => '2023-06-16',
                'gender' => 'Male', 'state' => 'California', 'era' => '1970s',
                'ideologies' => ['Anti-war', 'Whistleblower'],
                'in_custody' => false, 'released' => true,
                'description' => "Daniel Ellsberg was a RAND Corporation analyst and former Marine Corps officer who copied and leaked the Pentagon Papers — a 7,000-page top-secret Department of Defense study of U.S. decision-making in Vietnam from 1945 to 1967 — to The New York Times and 17 other newspapers in June 1971. The leak proved that four successive U.S. administrations had systematically lied to Congress and the public about the war.\n\nIndicted on December 30, 1971 along with his RAND colleague Anthony Russo on twelve counts of theft, conspiracy, and violations of the Espionage Act, he faced a maximum sentence of 115 years. The trial began in Los Angeles in January 1973. On May 11, 1973, Federal District Judge William Matthew Byrne Jr. dismissed all charges with prejudice on the grounds that the Nixon administration's 'plumbers' unit had broken into the office of Ellsberg's psychiatrist looking for damaging material, illegally wiretapped him, and made an improper offer of the FBI directorship to the trial judge — and that 'the bizarre events have incurably infected the prosecution of this case.'\n\nHe was the first person ever prosecuted under the Espionage Act for leaking to the press, and remained a leading whistleblower advocate and a public supporter of Chelsea Manning, Edward Snowden, and Julian Assange until his death in 2023.",
            ],
            'cases' => [[
                'institution_id' => $laFederal->id,
                'charges'        => 'Theft, conspiracy, and violations of the Espionage Act of 1917 (12 counts; release of the Pentagon Papers)',
                'arrest_date'    => '1971-06-28',
                'release_date'   => '1973-05-11',
                'convicted'      => 'No — all charges dismissed with prejudice by Judge William Matthew Byrne Jr. on May 11, 1973, citing prosecutorial and executive-branch misconduct',
                'sentence'       => 'No conviction',
            ]],
        ];

        // Anthony Russo
        $defendants[] = [
            'data' => [
                'name' => 'Anthony Russo', 'first_name' => 'Anthony', 'middle_name' => 'Joseph', 'last_name' => 'Russo Jr.',
                'aka' => 'Tony Russo',
                'birthdate' => '1936-10-11', 'death_date' => '2008-08-06',
                'gender' => 'Male', 'state' => 'California', 'era' => '1970s',
                'ideologies' => ['Anti-war', 'Whistleblower'],
                'in_custody' => false, 'released' => true,
                'description' => "Anthony 'Tony' Russo Jr. was a RAND Corporation analyst who, in October 1969, helped Daniel Ellsberg secretly photocopy the Pentagon Papers. He was indicted alongside Ellsberg in December 1971 and tried with him in Los Angeles in 1973. Earlier, in 1971, he had served 47 days in the Los Angeles County Jail for refusing to testify before a federal grand jury about Ellsberg without a grant of immunity that would also bar use of his testimony in his own prosecution. The Pentagon Papers charges were dismissed with prejudice on May 11, 1973.",
            ],
            'cases' => [[
                'institution_id' => $laFederal->id,
                'charges'        => 'Conspiracy, theft, and Espionage Act violations (Pentagon Papers); contempt of court (1971 grand jury refusal)',
                'arrest_date'    => '1971-08-16',
                'release_date'   => '1973-05-11',
                'convicted'      => 'No — Pentagon Papers charges dismissed with prejudice May 11, 1973; previously served 47 days for grand-jury contempt in 1971',
                'sentence'       => '47 days for contempt; no conviction on the underlying Pentagon Papers charges',
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

        // Ehren Watada
        $defendants[] = [
            'data' => [
                'name' => 'Ehren Watada', 'first_name' => 'Ehren', 'middle_name' => 'Kyoshi', 'last_name' => 'Watada',
                'birthdate' => '1978-06-22', 'death_date' => null,
                'gender' => 'Male', 'race' => 'Asian American', 'state' => 'Washington', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Conscientious objector'],
                'in_custody' => false, 'released' => true,
                'description' => "First Lieutenant Ehren Watada became the first commissioned officer in the United States military to publicly refuse deployment to Iraq. In June 2006, after his unit was ordered to deploy, he announced that he believed the Iraq War to be illegal under the U.S. Constitution and the principles of the Nuremberg trials and that his oath of office required him to disobey unlawful orders. He was charged with missing movement and conduct unbecoming an officer; if convicted on all counts he faced up to seven years in military prison. His first court-martial at Fort Lewis, Washington in February 2007 ended in a mistrial declared by the military judge over defense objection. The government attempted to retry him; in 2008 a federal civilian court ruled that retrial would violate the constitutional protection against double jeopardy. The Army eventually accepted his resignation in 2009 with no further imprisonment.",
            ],
            'cases' => [[
                'institution_id' => $fortLewis->id,
                'charges'        => 'Missing movement; conduct unbecoming an officer (UCMJ Articles 87 and 133)',
                'arrest_date'    => '2006-07-02',
                'release_date'   => '2009-10-02',
                'convicted'      => 'No — first court-martial ended in mistrial February 2007; federal court barred retrial on double jeopardy grounds in 2008; resignation accepted 2009',
                'sentence'       => 'No imprisonment beyond restriction to base',
            ]],
        ];

        // Pablo Paredes
        $defendants[] = [
            'data' => [
                'name' => 'Pablo Paredes', 'first_name' => 'Pablo', 'last_name' => 'Paredes',
                'birthdate' => '1981-01-01', 'death_date' => null,
                'gender' => 'Male', 'race' => 'Latino', 'state' => 'California', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Conscientious objector'],
                'in_custody' => false, 'released' => true,
                'description' => "Petty Officer Third Class Pablo Paredes was a U.S. Navy weapons-control technician who refused to board the amphibious assault ship USS Bonhomme Richard as it deployed to the Persian Gulf on December 6, 2004 in support of the Iraq War. He held a press conference on the dock instead, wearing a t-shirt reading 'Like a cabinet member, I resign.' Court-martialed at Naval Station San Diego in May 2005 on charges of missing ship's movement and unauthorized absence, he was convicted of missing movement; the military judge declined to imprison him after testimony from former U.N. weapons inspector Denis Halliday and others established the reasonableness of his belief that the war was illegal. He was sentenced to two months of restriction, three months of hard labor without confinement, and a reduction in rank.",
            ],
            'cases' => [[
                'institution_id' => $sanDiego->id,
                'charges'        => 'Missing movement; unauthorized absence (UCMJ Articles 87 and 86)',
                'arrest_date'    => '2004-12-06',
                'release_date'   => '2005-08-15',
                'convicted'      => 'Yes — Navy special court-martial, May 11, 2005',
                'sentence'       => 'Two months of restriction; three months of hard labor without confinement; reduction in rank to seaman recruit',
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

        // Thomas Drake
        $defendants[] = [
            'data' => [
                'name' => 'Thomas Drake', 'first_name' => 'Thomas', 'middle_name' => 'Andrews', 'last_name' => 'Drake',
                'birthdate' => '1957-04-22', 'death_date' => null,
                'gender' => 'Male', 'state' => 'Maryland', 'era' => '2010s',
                'ideologies' => ['Whistleblower'],
                'in_custody' => false, 'released' => true,
                'description' => "Thomas Drake was a senior executive at the National Security Agency who, after exhausting every internal and Inspector General avenue, leaked unclassified information to a Baltimore Sun reporter in 2006 about the agency's wasteful and constitutionally questionable Trailblazer mass-surveillance program. In April 2010 the Obama Justice Department indicted him on ten felony counts including five Espionage Act counts of 'willful retention of national defense information,' obstruction of justice, and false statements — a possible 35-year sentence. He was the first whistleblower the Obama administration prosecuted under the Espionage Act, in what would become a record-setting series of such prosecutions.\n\nOn June 9, 2011, four days before the trial was scheduled to begin in U.S. District Court in Baltimore, the prosecution collapsed: all ten felony counts were dropped and Drake pleaded to a single misdemeanor of exceeding authorized use of a computer, receiving one year of probation and 240 hours of community service with no fine. He has since become a leading public advocate for Edward Snowden, William Binney, and other intelligence-community whistleblowers.",
            ],
            'cases' => [[
                'institution_id' => $bopGeneric->id,
                'charges'        => 'Five counts of willful retention of national defense information (Espionage Act); obstruction of justice; four counts of making false statements to the FBI',
                'arrest_date'    => '2010-04-14',
                'release_date'   => '2011-06-09',
                'convicted'      => 'No — all 10 felony counts dropped on the eve of trial; pleaded to a single misdemeanor (exceeding authorized use of a computer)',
                'sentence'       => 'One year of probation; 240 hours of community service; no fine, no imprisonment',
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
                'in_custody' => true, 'released' => false,
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

        // Manuel Esteban Paez Terán / Tortuguita (Stop Cop City — killed by police, not imprisoned but a recognized political martyr)
        $defendants[] = [
            'data' => [
                'name' => 'Manuel Esteban Paez Terán', 'first_name' => 'Manuel', 'middle_name' => 'Esteban', 'last_name' => 'Paez Terán',
                'aka' => 'Tortuguita',
                'birthdate' => '1996-08-13', 'death_date' => '2023-01-18',
                'gender' => 'Non-binary', 'race' => 'Latino', 'state' => 'Georgia', 'era' => '2020s',
                'ideologies' => ['Environmental', 'Anti-police', 'Forest defender'],
                'affiliation' => ['Stop Cop City', 'Defend the Atlanta Forest'],
                'in_custody' => false, 'released' => false,
                'description' => "Manuel Esteban Paez Terán, who used the forest name Tortuguita ('little turtle') and they/them pronouns, was a 26-year-old Venezuelan-American medic and environmental activist living at the encampment of forest defenders attempting to stop the construction of the Atlanta Public Safety Training Center — a 171-acre, $90 million police and fire training facility in the South River Forest known to opponents as 'Cop City.'\n\nOn the morning of January 18, 2023, during a multi-agency police raid on the encampment, Tortuguita was shot at least 57 times by Georgia State Patrol officers while sitting cross-legged in their tent. They became the first known U.S. environmental activist killed by law enforcement during a protest. Police initially claimed Tortuguita had shot a state trooper; the trooper was wounded but ballistics later confirmed the bullet that hit him came from another officer's weapon. The DeKalb County medical examiner found Tortuguita's hands were raised at the time of the killing and that they were sitting with their hands open and empty.\n\nNo officer has been criminally charged. The killing accelerated the federal and state Racketeer Influenced and Corrupt Organizations Act prosecution of the Stop Cop City movement: 61 forest defenders were indicted under Georgia RICO in September 2023, the largest such prosecution of a U.S. protest movement in recent decades. Tortuguita is included in this database as a political martyr in the same tradition as Frank Little — killed extrajudicially by state actors for political organizing.",
            ],
            'cases' => [[
                'institution_id'        => $atlantaForest->id,
                'charges'               => "No legal charges — killed by Georgia State Patrol gunfire (at least 57 rounds) during a multi-agency raid on the Stop Cop City forest encampment",
                'death_in_custody_date' => '2023-01-18',
                'convicted'             => 'No — extrajudicial police killing, January 18, 2023; no officers charged',
                'sentence'              => 'Death by police gunfire',
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
