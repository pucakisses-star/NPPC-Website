<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Adds the political prisoners surfaced as genuine gaps by a systematic
 * roster-by-roster sweep of the major U.S. armed/clandestine and
 * civil-resistance formations against the existing database. Across BLA,
 * FALN/Macheteros, the Nationalist Party, MOVE, the Plowshares movement,
 * the Green Scare (ELF/ALF), the United Freedom Front / Ohio 7, the Weather
 * Underground / May 19th / Resistance Conspiracy cases, and grand-jury
 * resisters — roughly 200 names verified — the database was missing only a
 * handful of people. This command adds them:
 *
 *  - Avelino & Norberto González-Claudio — Los Macheteros, the 1983 Wells
 *    Fargo (Águila Blanca) expropriation in West Hartford, CT.
 *  - Scott DeMuth — Green Scare; AEPA conspiracy conviction (2006 ferret
 *    release), six months.
 *  - Carrie Feldman — Green Scare; four months civil contempt for refusing
 *    the Davenport grand jury alongside DeMuth.
 *  - David Agranoff — Green Scare; ~80 days civil contempt for refusing a
 *    San Diego ELF grand jury (profile per the prisoner-only framing chosen
 *    by the editor).
 *  - Barbara Curzi-Laaman — United Freedom Front / Ohio 7; the one UFF
 *    member not already on the roll.
 *  - Cameron Bishop — 1969 SDS anti-war sabotage (Colorado transmission
 *    towers); conviction later overturned on appeal.
 *
 * Idempotent: prisoner:add refuses to create a name that already exists, so
 * re-running this command simply skips anyone already added.
 */
final class AddFormationGapPrisoners extends Command {
    protected $signature = 'prisoners:add-formation-gaps';
    protected $description = 'Add the political prisoners missing from the BLA/FALN/UFF/Green Scare/Plowshares formation sweep';

    public function handle(): int {
        $prisoners = [
            [
                'name' => 'Avelino González-Claudio',
                'first_name' => 'Avelino',
                'last_name' => 'González-Claudio',
                'description' => 'Avelino González-Claudio was a Puerto Rican independence militant and a member of Los Macheteros (the Boricua Popular Army / Ejército Popular Boricua). He played a central role in the September 12, 1983 robbery of a Wells Fargo armored-car depot in West Hartford, Connecticut, known within the movement as the Águila Blanca (White Eagle) action, in which more than 7 million dollars was expropriated to fund the Puerto Rican independence struggle. It was one of the largest cash robberies in U.S. history at the time. González-Claudio evaded capture for 25 years, living in Puerto Rico and teaching school under the alias José Ortega Morales, until his arrest in 2008. Suffering from Parkinson disease, he pleaded guilty to conspiracy to commit robbery and was sentenced in 2010 to seven years in federal prison. He was released in 2013 and died in 2019.',
                'race' => 'Hispanic/Latino',
                'gender' => 'Male',
                'birthdate' => '1942-10-08',
                'death_date' => '2019-07-09',
                'ideologies' => ['Puerto Rican independence', 'Anti-imperialism', 'Revolutionary nationalism'],
                'affiliation' => ['Los Macheteros', 'Boricua Popular Army'],
                'era' => '1980s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'Conspiracy to commit robbery (1983 Wells Fargo / Águila Blanca expropriation)',
                    'arrest_date' => '2008-02-07',
                    'convicted' => 'Pleaded guilty to conspiracy to commit robbery (2010)',
                    'release_date' => '2013-02-05',
                    'sentence' => 'Seven years in federal prison',
                ]],
            ],
            [
                'name' => 'Norberto González-Claudio',
                'first_name' => 'Norberto',
                'last_name' => 'González-Claudio',
                'description' => 'Norberto González-Claudio was a Puerto Rican independence militant and a member of Los Macheteros, and the younger brother of fellow Machetero Avelino González-Claudio. He took part in the September 12, 1983 Wells Fargo armored-car expropriation in West Hartford, Connecticut, the Águila Blanca action that raised more than 7 million dollars for the Puerto Rican independence movement. He remained a fugitive for nearly three decades before being arrested in Puerto Rico in May 2011. He pleaded guilty in federal court in Hartford to conspiracy related to the 1983 robbery, as well as to a charge of possessing a machine gun at the time of his 2011 arrest. He was released from federal prison on January 15, 2015.',
                'race' => 'Hispanic/Latino',
                'gender' => 'Male',
                'ideologies' => ['Puerto Rican independence', 'Anti-imperialism', 'Revolutionary nationalism'],
                'affiliation' => ['Los Macheteros', 'Boricua Popular Army'],
                'era' => '1980s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'Conspiracy related to the 1983 Wells Fargo robbery; possession of a machine gun (2011)',
                    'arrest_date' => '2011-05-10',
                    'convicted' => 'Pleaded guilty to conspiracy and to possession of a machine gun',
                    'release_date' => '2015-01-15',
                ]],
            ],
            [
                'name' => 'Scott DeMuth',
                'first_name' => 'Scott',
                'last_name' => 'DeMuth',
                'description' => 'Minneapolis anarchist, Dakota activist, and University of Minnesota anthropology graduate student prosecuted during the Green Scare. In November 2009 DeMuth was subpoenaed to a federal grand jury in Davenport, Iowa investigating a 2004 Animal Liberation Front raid at the University of Iowa, and was jailed for civil contempt after refusing to testify. He was then indicted under the Animal Enterprise Terrorism Act. In September 2010 he pleaded guilty to a single misdemeanor count of conspiracy under the Animal Enterprise Protection Act for a 2006 ferret release at a Minnesota fur farm, and was sentenced to six months in federal prison. He entered custody in February 2011 and was released on July 29, 2011. His prosecution became a cause celebre for grand jury resistance and academic freedom, as the government sought to use his graduate anthropology research against him.',
                'state' => 'Minnesota',
                'race' => 'Native American',
                'gender' => 'Male',
                'ideologies' => ['Anarchism', 'Animal liberation', 'Indigenous resistance'],
                'affiliation' => ['Animal Liberation Front'],
                'era' => 'Green Scare',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Iowa',
                    'charges' => 'Conspiracy to commit animal enterprise terrorism under the Animal Enterprise Protection Act, plus civil contempt for refusing grand jury testimony',
                    'arrest_date' => '2009-11-17',
                    'incarceration_date' => '2011-02-16',
                    'release_date' => '2011-07-29',
                    'convicted' => 'Yes — pleaded guilty to one misdemeanor conspiracy count',
                    'sentence' => 'Six months in federal prison plus one year supervised release',
                    'imprisoned_for_days' => 163,
                ]],
            ],
            [
                'name' => 'Carrie Feldman',
                'first_name' => 'Carrie',
                'last_name' => 'Feldman',
                'description' => 'Minneapolis anarchist and animal-rights activist jailed during the Green Scare for refusing to cooperate with a federal grand jury. In October 2009 Feldman was subpoenaed to a federal grand jury in Davenport, Iowa investigating a 2004 Animal Liberation Front raid at the University of Iowa, in which hundreds of animals were released and laboratories were damaged. She refused to testify, citing the long history of grand juries being used to surveil and dismantle political movements. She was jailed for civil contempt of court in November 2009 and held for roughly four months, until the grand jury decided it did not need her testimony and she was abruptly released on March 19, 2010, having never cooperated. Her imprisonment alongside co-resister Scott DeMuth made the Davenport grand jury a focal point of Green Scare grand-jury resistance organizing.',
                'state' => 'Minnesota',
                'gender' => 'Female',
                'ideologies' => ['Animal liberation', 'Anarchism'],
                'era' => 'Green Scare',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Iowa',
                    'charges' => 'Civil contempt of court for refusing to testify before a federal grand jury',
                    'incarceration_date' => '2009-11-17',
                    'release_date' => '2010-03-19',
                    'convicted' => 'Civil contempt (no underlying criminal conviction)',
                    'sentence' => 'Jailed approximately four months for civil contempt',
                    'imprisoned_for_days' => 122,
                ]],
            ],
            [
                'name' => 'David Agranoff',
                'first_name' => 'David',
                'last_name' => 'Agranoff',
                'description' => 'San Diego animal-liberation and Earth Liberation Front activist and author imprisoned during the Green Scare for refusing to cooperate with a federal grand jury. In 2005 Agranoff was subpoenaed to a federal grand jury in San Diego investigating Earth Liberation Front activity, including the 2003 arson of a La Jolla apartment complex claimed by the ELF and a speech delivered by activist Rod Coronado. Together with fellow activists Nicole Fink and Danae Kelley, Agranoff refused to testify, invoking the long record of grand juries being used to map and repress radical movements. He was jailed for roughly 80 days for civil contempt of court before being released. His case was part of the broader wave of grand-jury resistance that defined the Green Scare era of animal- and earth-liberation prosecutions.',
                'state' => 'California',
                'gender' => 'Male',
                'ideologies' => ['Animal liberation', 'Earth liberation', 'Anarchism'],
                'era' => 'Green Scare',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'California',
                    'charges' => 'Civil contempt of court for refusing to testify before a federal grand jury',
                    'convicted' => 'Civil contempt (no underlying criminal conviction)',
                    'sentence' => 'Jailed approximately 80 days for civil contempt',
                    'imprisoned_for_days' => 80,
                ]],
            ],
            [
                'name' => 'Barbara Curzi-Laaman',
                'first_name' => 'Barbara',
                'last_name' => 'Curzi-Laaman',
                'description' => 'Barbara Jean Curzi was a member of the United Freedom Front (UFF), the New England-based revolutionary armed organization originally known as the Sam Melville/Jonathan Jackson Unit. Recruited into the underground by Raymond Luc Levasseur and married to fellow member Jaan Laaman, Curzi was part of a clandestine cell that, between 1975 and 1984, carried out a campaign of bombings and bank expropriations across the northeastern United States targeting military contractors, corporations tied to apartheid South Africa, and institutions linked to U.S. intervention in Central America. On November 4, 1984, she was arrested in Cleveland, Ohio along with other members of the group, with three children leaving the house ahead of the adults during the raid. Convicted of offenses connected to the UFF bombing campaign, Curzi was sentenced to 15 years in federal prison. She was later among the defendants in the Springfield, Massachusetts seditious-conspiracy trial that produced the Ohio 7 label, where the sedition charges against her were ultimately dismissed. She was released during the 1990s.',
                'race' => 'White',
                'gender' => 'Female',
                'ideologies' => ['Anti-imperialism', 'Revolutionary socialism', 'Anti-apartheid solidarity'],
                'affiliation' => ['United Freedom Front', 'Sam Melville/Jonathan Jackson Unit'],
                'era' => '1980s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'Offenses connected to United Freedom Front bombings; harboring at the time of arrest; seditious conspiracy and racketeering (later dismissed)',
                    'arrest_date' => '1984-11-04',
                    'convicted' => 'Convicted of UFF bombing-related offenses; seditious conspiracy charges later dismissed',
                    'sentence' => '15 years in federal prison',
                ]],
            ],
            [
                'name' => 'Cameron Bishop',
                'first_name' => 'Cameron',
                'last_name' => 'Bishop',
                'description' => 'Cameron David Bishop was an anti-war radical associated with Students for a Democratic Society who became, in 1969, one of the first activists placed on the FBI Ten Most Wanted Fugitives list for an act of political sabotage. In January 1969 Bishop used dynamite to topple four high-voltage electrical transmission towers of the Public Service Company of Colorado near Denver. One of the targeted lines fed the Coors Porcelain plant, which manufactured ceramic nose cones for military missiles, and the action was intended to disrupt Vietnam War production. Bishop remained a fugitive for six years before being captured in Rhode Island in March 1975 and returned to Denver. In 1975 he was convicted on three counts of sabotage under a 1918 statute that had rarely been used since the World Wars, and was sentenced to three concurrent seven-year terms. His conviction was overturned on appeal in 1977. Bishop was among the earliest figures of the New Left to be imprisoned for armed sabotage against the U.S. war effort.',
                'race' => 'White',
                'gender' => 'Male',
                'ideologies' => ['New Left', 'Anti-war', 'Anti-imperialism'],
                'affiliation' => ['Students for a Democratic Society'],
                'era' => '1960s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'Three counts of sabotage of national-defense utilities under the federal Sabotage Act',
                    'convicted' => 'Convicted on three counts of sabotage in 1975; conviction overturned on appeal in 1977',
                    'sentence' => 'Three concurrent seven-year federal terms',
                ]],
            ],
        ];

        $added = 0;
        $skipped = 0;
        foreach ($prisoners as $p) {
            $this->line("\n— {$p['name']} —");
            $code = Artisan::call('prisoner:add', ['json' => json_encode($p, JSON_UNESCAPED_UNICODE)]);
            $this->line(trim(Artisan::output()));
            if ($code === self::SUCCESS) {
                $added++;
            } else {
                $skipped++;
            }
        }

        $this->info("\nDone — added {$added}, skipped {$skipped} (already present).");

        return self::SUCCESS;
    }
}
