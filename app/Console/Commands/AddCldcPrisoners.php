<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 19 political prisoners and movement defendants surfaced from
 * the Civil Liberties Defense Center (cldc.org) cross-reference,
 * filtered to people who fit NPPC's "political prisoner" definition
 * — killed by the state, currently incarcerated, served significant
 * time, or major-case defendants facing serious charges.
 *
 * Excludes: civil-rights plaintiffs only, dismissed/acquitted
 * minor-charge cases, brief detentions.
 *
 * Idempotent: prisoner:add refuses duplicates by name.
 */
final class AddCldcPrisoners extends Command {
    protected $signature = 'archive:add-cldc-prisoners';
    protected $description = 'Add political prisoners surfaced from cldc.org';

    public function handle(): int {
        $added = 0;
        $skipped = 0;

        foreach ($this->prisoners() as $payload) {
            $name = $payload['name'];
            $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
            if ($exit === self::SUCCESS) {
                $this->info("ADD: {$name}");
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
                'name' => 'Manuel Esteban Paez Terán',
                'aka' => 'Tortuguita',
                'first_name' => 'Manuel',
                'middle_name' => 'Esteban',
                'last_name' => 'Paez Terán',
                'description' => 'Environmental activist and forest defender known as "Tortuguita" ("Little Turtle"). A nonbinary Venezuelan-American organizer, Tortuguita was killed by Georgia State Patrol officers during a multi-agency raid on the Weelaunee Forest encampment opposing the construction of the Atlanta Public Safety Training Center ("Cop City"). An autopsy found at least 57 gunshot wounds and no gunpowder residue on their hands; the official narrative claimed they fired first, a claim contradicted by body-camera footage and the autopsy. No officers were charged. Tortuguita\'s killing galvanized the Stop Cop City movement.',
                'state' => 'Georgia',
                'race' => 'Latinx',
                'gender' => 'Nonbinary',
                'death_date' => '2023-01-18',
                'ideologies' => ['Anarchist', 'Anti-fascist', 'Environmentalist'],
                'affiliation' => ['Stop Cop City', 'Defend the Atlanta Forest'],
                'era' => 'Stop Cop City',
                'in_custody' => false,
                'released' => false,
                'cases' => [
                    [
                        'charges' => 'Killed by police during forest encampment raid; no charges had been filed at time of death.',
                        'death_in_custody_date' => '2023-01-18',
                    ],
                ],
            ],
            [
                'name' => 'Mahmoud Khalil',
                'first_name' => 'Mahmoud',
                'last_name' => 'Khalil',
                'description' => 'Palestinian organizer and Columbia University graduate student detained by ICE in March 2025 in retaliation for his prominent role in the Columbia Gaza solidarity encampment. A lawful permanent resident, Khalil was targeted under a rarely-invoked Cold War-era provision allowing the Secretary of State to deport noncitizens whose presence is deemed adverse to U.S. foreign policy. His arrest is widely viewed as the opening salvo in a Trump-administration campaign to deport pro-Palestine student organizers. He has been held in immigration detention in Louisiana while challenging his removal.',
                'race' => 'Arab',
                'gender' => 'Male',
                'ideologies' => ['Pro-Palestine', 'Student organizer'],
                'affiliation' => ['Columbia University Apartheid Divest'],
                'era' => 'Gaza Solidarity Movement',
                'in_custody' => true,
                'released' => false,
                'awaiting_trial' => true,
                'cases' => [
                    [
                        'institution_name' => 'LaSalle ICE Processing Center',
                        'institution_city' => 'Jena',
                        'institution_state' => 'Louisiana',
                        'charges' => 'Immigration detention under INA §237(a)(4)(C) (foreign-policy grounds); no criminal charges.',
                        'arrest_date' => '2025-03-08',
                    ],
                ],
            ],
            [
                'name' => 'Caleb Freestone',
                'first_name' => 'Caleb',
                'last_name' => 'Freestone',
                'description' => 'Reproductive-rights defendant prosecuted under the federal FACE Act and related conspiracy statutes as part of the "Jane\'s Revenge" investigation into actions targeting crisis pregnancy centers in Florida after the Dobbs decision. Sentenced to roughly one year in federal prison following alleged pretrial-release violations.',
                'state' => 'Florida',
                'ideologies' => ['Reproductive justice'],
                'affiliation' => ['Jane\'s Revenge'],
                'era' => 'Post-Dobbs reproductive resistance',
                'in_custody' => true,
                'released' => false,
                'cases' => [
                    [
                        'charges' => 'Conspiracy against rights (18 U.S.C. §241), FACE Act violations.',
                        'sentence' => '~1 year federal imprisonment.',
                    ],
                ],
            ],
            [
                'name' => 'Tarek Mehanna',
                'first_name' => 'Tarek',
                'last_name' => 'Mehanna',
                'description' => 'Egyptian-American pharmacist convicted in 2011 on material-support-for-terrorism and false-statements charges in what civil liberties advocates widely characterized as a criminalized-speech prosecution. The government\'s case rested largely on Mehanna\'s online translation work and political views; he refused to become an FBI informant. Sentenced to 17.5 years; his case became a touchstone for critics of post-9/11 First Amendment encroachment.',
                'state' => 'Massachusetts',
                'race' => 'Arab',
                'gender' => 'Male',
                'ideologies' => ['Muslim political speech', 'Anti-imperialist'],
                'era' => 'War on Terror',
                'in_custody' => true,
                'released' => false,
                'cases' => [
                    [
                        'charges' => 'Conspiracy to provide material support to terrorists, providing material support to terrorists, conspiracy to kill in a foreign country, false statements.',
                        'sentenced_date' => '2012-04-12',
                        'sentence' => '17 years 6 months federal imprisonment.',
                    ],
                ],
            ],
            [
                'name' => 'Loren Reed',
                'first_name' => 'Loren',
                'last_name' => 'Reed',
                'description' => 'Diné (Navajo) man federally prosecuted for Facebook posts made during the 2020 George Floyd uprising in Page, Arizona. Charged with making interstate threats to damage property by fire; took a non-cooperation plea after roughly a year in pretrial detention. His case drew attention as an example of federal prosecution of online speech connected to Black Lives Matter organizing.',
                'state' => 'Arizona',
                'race' => 'Indigenous',
                'gender' => 'Male',
                'ideologies' => ['Indigenous solidarity', 'Black Lives Matter'],
                'era' => '2020 George Floyd Uprising',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'charges' => 'Interstate threats to damage property by fire (18 U.S.C. §844(e)).',
                        'arrest_date' => '2020-06-02',
                    ],
                ],
            ],
            [
                'name' => 'Jeffrey Luers',
                'aka' => 'Jeff "Free" Luers',
                'first_name' => 'Jeffrey',
                'last_name' => 'Luers',
                'description' => 'Earth Liberation Front-affiliated activist who, with co-defendant Craig Marshall, set fire to three SUVs at a Eugene, Oregon Chevrolet dealership in June 2000 to protest climate change. Originally sentenced to 22 years 8 months — an extraordinary sentence widely cited as politically motivated — the term was reduced on appeal to 10 years. Released December 2009 after nearly a decade in Oregon state custody.',
                'state' => 'Oregon',
                'gender' => 'Male',
                'ideologies' => ['Anarchist', 'Environmentalist', 'Earth Liberation Front'],
                'affiliation' => ['Earth Liberation Front (ELF)'],
                'era' => 'Green Scare',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'charges' => 'Arson, attempted arson, possession of a destructive device.',
                        'sentenced_date' => '2001-06-11',
                        'release_date' => '2009-12-16',
                        'sentence' => 'Originally 22 years 8 months; reduced on appeal to 10 years.',
                    ],
                ],
            ],
            [
                'name' => 'Tre Arrow',
                'aka' => 'Michael Scarpitti',
                'first_name' => 'Tre',
                'last_name' => 'Arrow',
                'description' => 'Environmental activist and former Pacific Green Party congressional candidate, federally prosecuted in connection with the 2001 firebombing of cement and logging trucks in Oregon. Fled to Canada, was arrested in Victoria in 2004, and extradited in 2008. Pled guilty in 2008 to two counts of arson and was sentenced to 78 months; released in 2009 after credit for time served.',
                'state' => 'Oregon',
                'gender' => 'Male',
                'ideologies' => ['Environmentalist', 'Earth Liberation Front'],
                'affiliation' => ['Earth Liberation Front (ELF)'],
                'era' => 'Green Scare',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'charges' => 'Arson (cement trucks at Ross Island Sand & Gravel; logging trucks at Schoppert Logging).',
                        'arrest_date' => '2004-03-13',
                        'sentenced_date' => '2008-08-12',
                        'sentence' => '78 months (6.5 years); released 2009.',
                    ],
                ],
            ],
            [
                'name' => 'Lori Berenson',
                'first_name' => 'Lori',
                'last_name' => 'Berenson',
                'description' => 'U.S. activist arrested in Lima, Peru in 1995 and convicted by a hooded military tribunal of treason for alleged collaboration with the Tupac Amaru Revolutionary Movement (MRTA). After international pressure, her conviction was overturned and she was retried in civilian court in 2001 on the lesser charge of collaboration with terrorism, receiving a 20-year sentence. Granted parole in 2010; completed her sentence and returned to the United States in December 2015.',
                'state' => 'New York',
                'race' => 'White',
                'gender' => 'Female',
                'ideologies' => ['Latin American solidarity', 'Anti-imperialist'],
                'era' => 'Latin American solidarity',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'institution_name' => 'Various Peruvian prisons',
                        'institution_state' => 'Peru',
                        'charges' => 'Treason (1996, military tribunal — overturned); collaboration with terrorism (2001 civilian retrial).',
                        'arrest_date' => '1995-11-30',
                        'release_date' => '2015-12-03',
                        'sentence' => '20 years; paroled 2010, sentence completed 2015.',
                    ],
                ],
            ],
            [
                'name' => 'Jerry Koch',
                'first_name' => 'Jerry',
                'last_name' => 'Koch',
                'description' => 'New York anarchist and grand jury resister jailed for civil contempt after refusing to testify before a federal grand jury reportedly investigating a 2008 bombing at the Times Square military recruiting station. Held in the Metropolitan Correctional Center; released in January 2014 after his attorneys filed a Grumbles motion arguing his continued detention had lost any coercive purpose.',
                'state' => 'New York',
                'gender' => 'Male',
                'ideologies' => ['Anarchist'],
                'era' => 'Grand jury resistance',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'institution_name' => 'Metropolitan Correctional Center',
                        'institution_city' => 'New York',
                        'institution_state' => 'New York',
                        'charges' => 'Civil contempt (28 U.S.C. §1826) for refusing federal grand jury testimony.',
                        'release_date' => '2014-01-29',
                    ],
                ],
            ],
            [
                'name' => 'Ken Ward',
                'first_name' => 'Ken',
                'last_name' => 'Ward',
                'description' => 'Climate activist and one of the five "Valve Turners" who on October 11, 2016 simultaneously shut off emergency valves on five tar sands pipelines carrying crude oil from Canada to the United States. Ward closed the Kinder Morgan pipeline in Burlington, Washington. After two trials, he was convicted of burglary; his case made history when the Washington Supreme Court ultimately ruled defendants must be allowed to present a climate-necessity defense to juries.',
                'state' => 'Washington',
                'gender' => 'Male',
                'ideologies' => ['Climate justice', 'Direct action'],
                'affiliation' => ['Climate Direct Action / Valve Turners'],
                'era' => 'Pipeline resistance',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'charges' => 'Burglary in the second degree, sabotage.',
                        'arrest_date' => '2016-10-11',
                    ],
                ],
            ],
            [
                'name' => 'Leonard Higgins',
                'first_name' => 'Leonard',
                'last_name' => 'Higgins',
                'description' => 'Retired state IT manager and Valve Turner who shut off the Enbridge Express tar sands pipeline in Chouteau County, Montana on October 11, 2016. Convicted of felony criminal mischief and misdemeanor trespass; received a three-year deferred sentence with no jail time. The trial court denied his climate-necessity defense.',
                'state' => 'Montana',
                'gender' => 'Male',
                'ideologies' => ['Climate justice', 'Direct action'],
                'affiliation' => ['Climate Direct Action / Valve Turners'],
                'era' => 'Pipeline resistance',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'charges' => 'Felony criminal mischief, misdemeanor trespass.',
                        'arrest_date' => '2016-10-11',
                        'sentenced_date' => '2018-03-20',
                        'sentence' => '3-year deferred sentence, no jail time.',
                    ],
                ],
            ],
            [
                'name' => 'Emily Johnston',
                'first_name' => 'Emily',
                'last_name' => 'Johnston',
                'description' => 'Seattle-based poet and Valve Turner who shut off Enbridge tar sands pipelines near Leonard, Minnesota on October 11, 2016. After multiple appeals, her trial court granted her the right to present a climate-necessity defense — a first in such a case. Charges were ultimately resolved without prison time.',
                'state' => 'Minnesota',
                'gender' => 'Female',
                'ideologies' => ['Climate justice', 'Direct action'],
                'affiliation' => ['Climate Direct Action / Valve Turners'],
                'era' => 'Pipeline resistance',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'charges' => 'Felony criminal damage to property, aiding and abetting; reckless endangerment.',
                        'arrest_date' => '2016-10-11',
                    ],
                ],
            ],
            [
                'name' => 'Annette Klapstein',
                'first_name' => 'Annette',
                'last_name' => 'Klapstein',
                'description' => 'Retired attorney and Valve Turner who acted with Emily Johnston to shut off Enbridge tar sands pipelines near Leonard, Minnesota on October 11, 2016. The Minnesota trial court granted her a climate-necessity defense, a landmark ruling in U.S. climate litigation.',
                'state' => 'Minnesota',
                'gender' => 'Female',
                'ideologies' => ['Climate justice', 'Direct action'],
                'affiliation' => ['Climate Direct Action / Valve Turners'],
                'era' => 'Pipeline resistance',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'charges' => 'Felony criminal damage to property, aiding and abetting; reckless endangerment.',
                        'arrest_date' => '2016-10-11',
                    ],
                ],
            ],
            [
                'name' => 'Sam Jessup',
                'first_name' => 'Sam',
                'last_name' => 'Jessup',
                'description' => 'Co-defendant and videographer in the North Dakota Valve Turner case alongside Michael Foster, who shut off the TransCanada Keystone pipeline on October 11, 2016. Charged with conspiracy for filming and providing logistical support to the action.',
                'state' => 'North Dakota',
                'gender' => 'Male',
                'ideologies' => ['Climate justice', 'Direct action'],
                'affiliation' => ['Climate Direct Action / Valve Turners'],
                'era' => 'Pipeline resistance',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'charges' => 'Conspiracy to commit criminal mischief; conspiracy to commit reckless endangerment.',
                        'arrest_date' => '2016-10-11',
                    ],
                ],
            ],
            [
                'name' => 'Benjamin Joldersma',
                'aka' => 'Ben Joldersma',
                'first_name' => 'Benjamin',
                'last_name' => 'Joldersma',
                'description' => 'Valve Turner support team member who provided videographer and logistical support for the October 11, 2016 coordinated tar sands pipeline shutdowns. Charged as a co-defendant in the Minnesota case alongside Emily Johnston and Annette Klapstein.',
                'state' => 'Minnesota',
                'gender' => 'Male',
                'ideologies' => ['Climate justice', 'Direct action'],
                'affiliation' => ['Climate Direct Action / Valve Turners'],
                'era' => 'Pipeline resistance',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'charges' => 'Aiding and abetting felony criminal damage to property; conspiracy.',
                        'arrest_date' => '2016-10-11',
                    ],
                ],
            ],
            [
                'name' => 'Ayla King',
                'first_name' => 'Ayla',
                'last_name' => 'King',
                'description' => 'Stop Cop City forest defender and the first defendant tried under Georgia\'s sweeping RICO indictment against 61 activists opposing the Atlanta Public Safety Training Center. King was arrested at the March 2023 "week of action" in the Weelaunee Forest and severed their trial from the rest of the 61 defendants. The first attempt at trial ended in a mistrial in July 2025.',
                'state' => 'Georgia',
                'ideologies' => ['Anarchist', 'Environmentalist'],
                'affiliation' => ['Stop Cop City', 'Defend the Atlanta Forest'],
                'era' => 'Stop Cop City',
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => true,
                'cases' => [
                    [
                        'charges' => 'Domestic terrorism (Georgia state); RICO conspiracy.',
                        'arrest_date' => '2023-03-05',
                    ],
                ],
            ],
            [
                'name' => 'Amber Smith-Stewart',
                'first_name' => 'Amber',
                'last_name' => 'Smith-Stewart',
                'description' => 'Reproductive-rights defendant and one of the "Florida 4" prosecuted by the federal government under the FACE Act and conspiracy-against-rights statutes for actions against crisis pregnancy centers in Florida after the Dobbs decision overturned Roe v. Wade. Served approximately 30 days in federal prison followed by house arrest with electronic monitoring.',
                'state' => 'Florida',
                'gender' => 'Female',
                'ideologies' => ['Reproductive justice'],
                'affiliation' => ['Jane\'s Revenge'],
                'era' => 'Post-Dobbs reproductive resistance',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'charges' => 'Conspiracy against rights (18 U.S.C. §241); FACE Act violations.',
                        'sentence' => '~30 days federal imprisonment followed by house arrest.',
                    ],
                ],
            ],
            [
                'name' => 'Annarella Rivera',
                'first_name' => 'Annarella',
                'last_name' => 'Rivera',
                'description' => 'Reproductive-rights defendant and one of the "Florida 4" prosecuted under the FACE Act and federal conspiracy statutes for actions against crisis pregnancy centers in Florida following the Dobbs decision. Served roughly 30 days in federal prison followed by house arrest.',
                'state' => 'Florida',
                'gender' => 'Female',
                'ideologies' => ['Reproductive justice'],
                'affiliation' => ['Jane\'s Revenge'],
                'era' => 'Post-Dobbs reproductive resistance',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'charges' => 'Conspiracy against rights (18 U.S.C. §241); FACE Act violations.',
                        'sentence' => '~30 days federal imprisonment followed by house arrest.',
                    ],
                ],
            ],
            [
                'name' => 'Gabriela Oropesa',
                'first_name' => 'Gabriela',
                'last_name' => 'Oropesa',
                'description' => 'Reproductive-rights defendant and the only one of the "Florida 4" Jane\'s Revenge defendants to take her case to trial rather than plead. Convicted in 2024 of FACE Act and conspiracy charges in connection with actions against Florida crisis pregnancy centers after the Dobbs decision.',
                'state' => 'Florida',
                'gender' => 'Female',
                'ideologies' => ['Reproductive justice'],
                'affiliation' => ['Jane\'s Revenge'],
                'era' => 'Post-Dobbs reproductive resistance',
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => false,
                'cases' => [
                    [
                        'charges' => 'Conspiracy against rights (18 U.S.C. §241); FACE Act violations.',
                        'convicted' => 'Yes — convicted at trial 2024.',
                    ],
                ],
            ],
        ];
    }
}
