<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 11 PPs surfaced from the deep crawl of the Wikipedia
 * "Political violence in the United States" article (and its
 * linked sub-articles). 44 of the 55 candidates were already in;
 * these are the missing ones:
 *
 *   - 4 IWW labor-war defendants: Giuseppe Caruso (Lawrence 1912),
 *     Richard "Blackie" Ford & Herman D. Suhr (Wheatland 1913),
 *     John McInerney (Centralia 1920, died in prison)
 *   - 1 Magonista: Antonio I. Villarreal (PLM 1907)
 *   - 2 Detroit Sweet case (Black armed self-defense, 1925):
 *     Ossian Sweet, Henry Sweet
 *   - 1 Smith Act CPUSA (Foley Square 11): Jack Stachel
 *   - 1 Iraq War refuser: Ehren Watada
 *   - 2 Espionage Act whistleblowers: Thomas A. Drake (NSA),
 *     Henry Kyle Frese (DIA)
 *
 * Era values per project decade-string convention.
 */
final class AddPolViolenceDeep extends Command {
    protected $signature = 'archive:add-pol-violence-deep';
    protected $description = 'Add 11 PPs from deep crawl of Political violence in the US';

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
            // === IWW labor wars ===
            [
                'name' => 'Giuseppe Caruso',
                'first_name' => 'Giuseppe',
                'last_name' => 'Caruso',
                'description' => 'Italian-American striker arrested during the 1912 Lawrence textile strike ("Bread and Roses") and accused of firing the shot that killed striker Anna LoPizzo. Held in pretrial detention for ten months with IWW organizers Joseph Ettor and Arturo Giovannitti, who were charged as accessories. Acquitted by jury in November 1912 after the IWW organized a successful national defense campaign and a Boston general strike in support of the defendants.',
                'state' => 'Massachusetts',
                'race' => 'White',
                'gender' => 'Male',
                'ideologies' => ['IWW', 'Anarcho-syndicalist'],
                'affiliation' => ['Industrial Workers of the World'],
                'era' => '1910s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Massachusetts',
                    'charges' => 'Murder of striker Anna LoPizzo (Lawrence textile strike).',
                    'arrest_date' => '1912-01-30',
                    'release_date' => '1912-11-26',
                    'convicted' => 'No — acquitted by jury.',
                    'sentence' => '~10 months pretrial detention; acquitted.',
                ]],
            ],
            [
                'name' => 'Richard "Blackie" Ford',
                'first_name' => 'Richard',
                'last_name' => 'Ford',
                'aka' => 'Blackie Ford',
                'description' => 'IWW spokesman convicted of second-degree murder for the 1913 Wheatland Hop Riot near Wheatland, California — a clash between heavily armed sheriffs and 2,800 impoverished hop pickers organizing for water, sanitary conditions, and back wages on the Durst Ranch. Four people were killed in the clash, including the district attorney and two pickers. Ford was scapegoated for his role giving the workers\' speech that morning. Sentenced to life; paroled in 1924.',
                'state' => 'California',
                'gender' => 'Male',
                'ideologies' => ['IWW', 'Anarcho-syndicalist'],
                'affiliation' => ['Industrial Workers of the World'],
                'era' => '1910s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Folsom State Prison',
                    'institution_state' => 'California',
                    'charges' => 'Second-degree murder (Wheatland Hop Riot, August 3, 1913).',
                    'arrest_date' => '1913-08-04',
                    'sentenced_date' => '1914-01-31',
                    'release_date' => '1924-01-01',
                    'convicted' => 'Yes.',
                    'sentence' => 'Life imprisonment; paroled 1924.',
                ]],
            ],
            [
                'name' => 'Herman D. Suhr',
                'first_name' => 'Herman',
                'last_name' => 'Suhr',
                'description' => 'IWW Durst Farm local secretary convicted of second-degree murder for the 1913 Wheatland Hop Riot in California alongside Richard "Blackie" Ford. The Wheatland defense became one of the IWW\'s longest-running legal campaigns; both men were sentenced to life. Suhr was later pardoned.',
                'state' => 'California',
                'gender' => 'Male',
                'ideologies' => ['IWW', 'Anarcho-syndicalist'],
                'affiliation' => ['Industrial Workers of the World'],
                'era' => '1910s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Folsom State Prison',
                    'institution_state' => 'California',
                    'charges' => 'Second-degree murder (Wheatland Hop Riot, August 3, 1913).',
                    'arrest_date' => '1913-08-04',
                    'sentenced_date' => '1914-01-31',
                    'convicted' => 'Yes.',
                    'sentence' => 'Life imprisonment; pardoned.',
                ]],
            ],
            [
                'name' => 'John McInerney',
                'first_name' => 'John',
                'last_name' => 'McInerney',
                'description' => 'IWW member convicted of second-degree murder in the 1920 Centralia trial alongside seven other Wobblies for the November 11, 1919 shooting of American Legion members during the IWW hall raid in Centralia, Washington. McInerney died in custody at the Washington State Penitentiary in 1930.',
                'state' => 'Washington',
                'gender' => 'Male',
                'ideologies' => ['IWW', 'Anarcho-syndicalist'],
                'affiliation' => ['Industrial Workers of the World'],
                'era' => '1920s',
                'in_custody' => false,
                'released' => false,
                'cases' => [[
                    'institution_name' => 'Washington State Penitentiary',
                    'institution_state' => 'Washington',
                    'charges' => 'Second-degree murder (Centralia Massacre, November 11, 1919).',
                    'arrest_date' => '1919-11-11',
                    'sentenced_date' => '1920-03-13',
                    'death_in_custody_date' => '1930-01-01',
                    'convicted' => 'Yes.',
                    'sentence' => '25–40 years; died in custody at Washington State Penitentiary in 1930.',
                ]],
            ],

            // === Magonista (PLM, Mexican Revolution) ===
            [
                'name' => 'Antonio I. Villarreal',
                'first_name' => 'Antonio',
                'middle_name' => 'Irineo',
                'last_name' => 'Villarreal',
                'description' => 'Mexican revolutionary and Partido Liberal Mexicano (PLM) organizer who, with Ricardo Flores Magón and Librado Rivera, was arrested in Los Angeles in 1907 on U.S. neutrality-law charges for organizing armed expeditions across the border against the Porfirio Díaz dictatorship. Imprisoned in Yuma Territorial Prison and later Florence (Arizona). Released in 1910; later served as Minister of Agriculture under Mexican President Álvaro Obregón.',
                'state' => 'California',
                'race' => 'Latinx',
                'gender' => 'Male',
                'birthdate' => '1879-07-17',
                'death_date' => '1944-12-16',
                'ideologies' => ['Liberalism', 'Anti-Díaz', 'Magonista'],
                'affiliation' => ['Partido Liberal Mexicano (PLM)'],
                'era' => '1900s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Yuma Territorial Prison',
                    'institution_state' => 'Arizona',
                    'charges' => 'Violation of U.S. neutrality laws for organizing armed cross-border expeditions against the Mexican government.',
                    'arrest_date' => '1907-08-23',
                    'release_date' => '1910-08-01',
                    'sentence' => '18 months federal; ~3 years held including pretrial.',
                ]],
            ],

            // === Detroit Sweet case (Black armed self-defense, 1925) ===
            [
                'name' => 'Ossian Sweet',
                'first_name' => 'Ossian',
                'last_name' => 'Sweet',
                'description' => 'Black physician who, in September 1925, moved his family into a previously all-white Detroit neighborhood. The second night, a white mob of 500+ surrounded the house and attacked it with stones; from inside, gunfire (by Sweet\'s younger brother Henry) killed one mob member and wounded another. Police arrested all eleven Black occupants of the house and charged them with murder. Defended by Clarence Darrow and the NAACP in two trials. The first ended in a hung jury; the second tried Henry alone and ended in acquittal. Charges against Ossian and the others were then dropped. The Sweet case became one of the defining tests of Black Americans\' right to armed self-defense of their homes.',
                'state' => 'Michigan',
                'race' => 'Black',
                'gender' => 'Male',
                'birthdate' => '1895-10-30',
                'death_date' => '1960-03-19',
                'ideologies' => ['Black armed self-defense', 'Civil rights'],
                'affiliation' => ['NAACP'],
                'era' => '1920s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Michigan',
                    'charges' => 'First-degree murder (defense of home from white mob assault, Sept 9, 1925).',
                    'arrest_date' => '1925-09-09',
                    'release_date' => '1926-05-13',
                    'convicted' => 'No — hung jury (first trial); charges dropped after Henry Sweet\'s acquittal in the second.',
                    'sentence' => 'Hung jury; charges ultimately dismissed.',
                ]],
            ],
            [
                'name' => 'Henry Sweet',
                'first_name' => 'Henry',
                'last_name' => 'Sweet',
                'description' => 'Younger brother of Detroit physician Ossian Sweet and the family member who fired the shot that killed a white mob member during the September 9, 1925 attack on the Sweet family\'s newly-purchased home in a previously all-white Detroit neighborhood. Tried alone in the second trial in April 1926 with Clarence Darrow leading the defense; acquitted by an all-white jury on grounds of self-defense — a landmark verdict establishing that Black Americans had the same right as whites to defend their homes against mob violence.',
                'state' => 'Michigan',
                'race' => 'Black',
                'gender' => 'Male',
                'birthdate' => '1903-04-20',
                'death_date' => '1939-12-26',
                'ideologies' => ['Black armed self-defense'],
                'affiliation' => ['NAACP'],
                'era' => '1920s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Michigan',
                    'charges' => 'First-degree murder (shooting of Leon Breiner during white-mob assault on Sweet home, Sept 9, 1925).',
                    'arrest_date' => '1925-09-09',
                    'release_date' => '1926-05-13',
                    'convicted' => 'No — acquitted April 1926 (Darrow defense, all-white jury).',
                    'sentence' => 'Acquitted.',
                ]],
            ],

            // === Smith Act CPUSA (Foley Square 11) — Jack Stachel ===
            [
                'name' => 'Jack Stachel',
                'first_name' => 'Jack',
                'last_name' => 'Stachel',
                'description' => 'Communist Party USA labor secretary and one of the eleven defendants in the 1949 "Foley Square" Smith Act trial in New York federal court — the first major prosecution of Communist Party leaders for "conspiracy to teach and advocate the overthrow of the United States government by force or violence." Convicted along with Eugene Dennis, John Gates, Gus Hall, Henry Winston, Benjamin Davis Jr., and others. Sentenced to five years in federal prison and a $10,000 fine.',
                'state' => 'New York',
                'race' => 'White',
                'gender' => 'Male',
                'birthdate' => '1900-01-01',
                'death_date' => '1965-12-04',
                'ideologies' => ['Communist'],
                'affiliation' => ['Communist Party USA'],
                'era' => '1940s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'New York',
                    'charges' => 'Conspiracy to teach and advocate the overthrow of the U.S. government by force or violence (Smith Act of 1940).',
                    'arrest_date' => '1948-07-20',
                    'sentenced_date' => '1949-10-21',
                    'convicted' => 'Yes — upheld in Dennis v. United States (1951).',
                    'sentence' => '5 years federal prison + $10,000 fine.',
                ]],
            ],

            // === Iraq War refuser ===
            [
                'name' => 'Ehren Watada',
                'first_name' => 'Ehren',
                'last_name' => 'Watada',
                'description' => 'U.S. Army First Lieutenant who, in June 2006, became the first commissioned officer to publicly refuse to deploy to Iraq, calling the war illegal under U.S. and international law. The Army charged him with missing movement and conduct unbecoming an officer. His February 2007 court-martial ended in a mistrial; the Army\'s attempts to retry him were blocked by a federal court ruling that retrial would violate the Double Jeopardy Clause. Discharged Other Than Honorable in October 2009.',
                'state' => 'Washington',
                'race' => 'Asian',
                'gender' => 'Male',
                'birthdate' => '1978-06-04',
                'ideologies' => ['Anti-Iraq War', 'Military refuser'],
                'era' => '2000s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Fort Lewis (court-martial)',
                    'institution_state' => 'Washington',
                    'charges' => 'Missing movement; conduct unbecoming an officer (refusal to deploy to Iraq).',
                    'arrest_date' => '2006-06-22',
                    'sentenced_date' => '2007-02-07',
                    'convicted' => 'No — mistrial declared; federal court barred retrial on double-jeopardy grounds.',
                    'sentence' => 'Mistrial; Other Than Honorable discharge October 2009.',
                ]],
            ],

            // === Espionage Act whistleblowers ===
            [
                'name' => 'Thomas A. Drake',
                'first_name' => 'Thomas',
                'middle_name' => 'A.',
                'last_name' => 'Drake',
                'description' => 'Former senior NSA executive who reported waste, fraud, and constitutional violations in the Trailblazer surveillance program through internal channels and to congressional oversight committees, then to a Baltimore Sun reporter. Indicted in April 2010 on ten felony counts including five Espionage Act violations — faced 35 years. The case collapsed on the eve of trial in June 2011 after the prosecution\'s evidence was discredited; Drake pled to a single misdemeanor of unauthorized use of a computer and received one year of probation. His case became a defining example of Obama-era weaponization of the Espionage Act against whistleblowers.',
                'state' => 'Maryland',
                'race' => 'White',
                'gender' => 'Male',
                'birthdate' => '1957-01-01',
                'ideologies' => ['Whistleblower', 'Civil liberties'],
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Maryland',
                    'charges' => 'Originally 10 felonies including 5 Espionage Act violations; reduced to one misdemeanor (unauthorized use of a computer).',
                    'arrest_date' => '2010-04-14',
                    'sentenced_date' => '2011-07-15',
                    'convicted' => 'Pled to misdemeanor; felonies dropped.',
                    'sentence' => '1 year probation, 240 hours community service.',
                ]],
            ],
            [
                'name' => 'Henry Kyle Frese',
                'first_name' => 'Henry',
                'middle_name' => 'Kyle',
                'last_name' => 'Frese',
                'description' => 'U.S. Defense Intelligence Agency counterterrorism analyst convicted under the Espionage Act for leaking classified information about a foreign country\'s weapons systems to two journalists with whom he had personal relationships. Arrested October 8, 2019 and indicted under 18 U.S.C. §793(d) and (e). Pled guilty June 2020; sentenced to 30 months in federal prison.',
                'state' => 'Virginia',
                'race' => 'White',
                'gender' => 'Male',
                'ideologies' => ['Whistleblower'],
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Virginia',
                    'charges' => 'Willful transmission of classified national defense information (Espionage Act, 18 U.S.C. §793(d) and (e)).',
                    'arrest_date' => '2019-10-08',
                    'sentenced_date' => '2020-06-18',
                    'convicted' => 'Yes — pled guilty.',
                    'sentence' => '30 months federal prison.',
                ]],
            ],
        ];
    }
}
