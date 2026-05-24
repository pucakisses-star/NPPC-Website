<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 7 political prisoners surfaced from Richard Henry Dana Jr.'s
 * legal-defense docket. Dana — Boston attorney and author of "Two
 * Years Before the Mast" — was the leading legal defender of
 * fugitive-slave defendants and Boston Vigilance Committee
 * rescuers in the 1850s.
 *
 * The 7 split into two groups:
 *
 *   - Three Black men held under the Fugitive Slave Act of 1850
 *     in Boston federal custody, awaiting return to slavery:
 *       Thomas Sims (1851) — returned to Georgia
 *       Shadrach Minkins (1851) — rescued from federal court by
 *         the Boston Vigilance Committee
 *       Anthony Burns (1854) — returned to Virginia despite
 *         massive Boston protests; later freed by abolitionists
 *         who purchased his freedom
 *
 *   - Four members of the Boston Vigilance Committee prosecuted
 *     by the federal government for aiding Shadrach Minkins's
 *     rescue. All juries refused to convict — early test of
 *     anti-slavery jury nullification:
 *       Lewis Hayden — Black abolitionist, former enslaved man
 *       Robert Morris — Black attorney, one of the first Black
 *         lawyers in the U.S.
 *       Elizur Wright — abolitionist newspaper editor
 *       Charles G. Davis — abolitionist attorney
 *
 * Era set to "1850s" per the project decade-string convention.
 */
final class AddDanaFugitiveSlaveDefendants extends Command {
    protected $signature = 'archive:add-dana-defendants';
    protected $description = 'Add 7 fugitive-slave-era PPs defended by Richard Henry Dana Jr.';

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
            [
                'name' => 'Anthony Burns',
                'first_name' => 'Anthony',
                'last_name' => 'Burns',
                'description' => 'Black man held in Boston federal custody under the Fugitive Slave Act of 1850 after escaping from enslavement in Virginia. His May 1854 arrest in Boston touched off the largest abolitionist protests of the antebellum era — an estimated 50,000 people lined the streets to witness his court-ordered return to slavery, the federal government deploying state militia and U.S. Marines to enforce the rendition. Defended by Richard Henry Dana Jr. and Charles Mayo Ellis. After return to Virginia, abolitionists purchased his freedom; he later became a Baptist minister in Ontario, Canada, where he died of tuberculosis in 1862 at age 28.',
                'state' => 'Massachusetts',
                'race' => 'Black',
                'gender' => 'Male',
                'birthdate' => '1834-05-31',
                'death_date' => '1862-07-27',
                'ideologies' => ['Anti-slavery', 'Black freedom'],
                'affiliation' => ['Fugitive from enslavement'],
                'era' => '1850s',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'institution_name' => 'Boston Court House',
                        'institution_state' => 'Massachusetts',
                        'charges' => 'Held under the Fugitive Slave Act of 1850 as alleged property of slaveowner Charles F. Suttle of Alexandria, VA.',
                        'arrest_date' => '1854-05-24',
                        'release_date' => '1855-02-22',
                        'convicted' => 'Returned to slavery by federal commissioner; freedom purchased by abolitionists ~9 months later.',
                        'sentence' => 'Returned to Virginia in chains aboard a U.S. revenue cutter; later manumitted.',
                    ],
                ],
            ],
            [
                'name' => 'Thomas Sims',
                'first_name' => 'Thomas',
                'last_name' => 'Sims',
                'description' => 'Black man held in Boston federal custody under the Fugitive Slave Act of 1850 after escaping enslavement in Georgia. The first major fugitive-slave rendition in Massachusetts under the 1850 Act; the courthouse was wrapped in chains by U.S. Marshals to prevent rescue by the Boston Vigilance Committee. Defended by Richard Henry Dana Jr. and Robert Rantoul Jr. Returned to Savannah on April 12, 1851, where his enslaver had him publicly whipped. Freed in 1863 during the Civil War; later worked at the U.S. Department of Justice in Washington, DC under President Grant.',
                'state' => 'Massachusetts',
                'race' => 'Black',
                'gender' => 'Male',
                'birthdate' => '1834-01-01',
                'death_date' => '1902-01-01',
                'ideologies' => ['Anti-slavery', 'Black freedom'],
                'affiliation' => ['Fugitive from enslavement'],
                'era' => '1850s',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'institution_name' => 'Boston Court House',
                        'institution_state' => 'Massachusetts',
                        'charges' => 'Held under the Fugitive Slave Act of 1850.',
                        'arrest_date' => '1851-04-04',
                        'release_date' => '1851-04-12',
                        'convicted' => 'Returned to slavery by federal commissioner.',
                        'sentence' => 'Returned to Savannah, Georgia and publicly whipped on arrival.',
                    ],
                ],
            ],
            [
                'name' => 'Shadrach Minkins',
                'first_name' => 'Shadrach',
                'last_name' => 'Minkins',
                'description' => 'Black man held in Boston federal custody under the Fugitive Slave Act of 1850 after escaping enslavement in Norfolk, Virginia. Defended by Richard Henry Dana Jr. and Robert Morris. On February 15, 1851 — while his lawyers awaited a court session — a Boston Vigilance Committee crowd of free Black Bostonians led by Lewis Hayden rushed the courthouse and freed him, hiding him in Beacon Hill before spiriting him to Montreal via the Underground Railroad. President Fillmore demanded prosecutions; juries acquitted all the rescuers. Minkins lived the rest of his life as a free man in Montreal, where he ran a restaurant and barber shop.',
                'state' => 'Massachusetts',
                'race' => 'Black',
                'gender' => 'Male',
                'birthdate' => '1817-01-01',
                'death_date' => '1875-12-13',
                'ideologies' => ['Anti-slavery', 'Black freedom'],
                'affiliation' => ['Fugitive from enslavement'],
                'era' => '1850s',
                'in_custody' => false,
                'released' => true,
                'in_exile' => true,
                'cases' => [
                    [
                        'institution_name' => 'Boston Court House',
                        'institution_state' => 'Massachusetts',
                        'charges' => 'Held under the Fugitive Slave Act of 1850.',
                        'arrest_date' => '1851-02-15',
                        'release_date' => '1851-02-15',
                        'convicted' => 'No — rescued from federal custody by the Boston Vigilance Committee.',
                        'sentence' => 'Escaped via the Underground Railroad to Montreal, where he lived in exile until his death.',
                    ],
                ],
            ],
            [
                'name' => 'Lewis Hayden',
                'first_name' => 'Lewis',
                'last_name' => 'Hayden',
                'description' => 'Black abolitionist, former enslaved person, Boston Vigilance Committee leader, and Underground Railroad conductor. With Robert Morris, Hayden organized and led the February 15, 1851 rescue of Shadrach Minkins from the Boston Court House. Federal authorities prosecuted him — the first Black American tried by the United States under federal law for aiding a fugitive — but the jury refused to convict. Hayden continued to harbor fugitives at his Beacon Hill home, served in the Massachusetts House of Representatives in 1873, and helped recruit Black soldiers for the 54th Massachusetts during the Civil War. Defended by Richard Henry Dana Jr.',
                'state' => 'Massachusetts',
                'race' => 'Black',
                'gender' => 'Male',
                'birthdate' => '1811-01-01',
                'death_date' => '1889-04-07',
                'ideologies' => ['Abolitionist', 'Black freedom'],
                'affiliation' => ['Boston Vigilance Committee', 'Underground Railroad'],
                'era' => '1850s',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'institution_state' => 'Massachusetts',
                        'charges' => 'Aiding and abetting the rescue of a fugitive from labor under the Fugitive Slave Act of 1850.',
                        'arrest_date' => '1851-02-21',
                        'convicted' => 'No — federal jury refused to convict.',
                        'sentence' => 'Acquitted.',
                    ],
                ],
            ],
            [
                'name' => 'Robert Morris',
                'first_name' => 'Robert',
                'last_name' => 'Morris',
                'description' => 'One of the first Black lawyers in the United States, admitted to the Massachusetts bar in 1847. Co-leader with Lewis Hayden of the February 1851 rescue of Shadrach Minkins from the Boston Court House. Federal authorities prosecuted Morris for aiding the rescue; the jury acquitted. Morris also served as co-counsel (with Charles Sumner) in Roberts v. City of Boston (1849), the first court challenge to segregated public schools in the United States.',
                'state' => 'Massachusetts',
                'race' => 'Black',
                'gender' => 'Male',
                'birthdate' => '1823-06-08',
                'death_date' => '1882-12-12',
                'ideologies' => ['Abolitionist', 'Civil rights law'],
                'affiliation' => ['Boston Vigilance Committee', 'Underground Railroad'],
                'era' => '1850s',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'institution_state' => 'Massachusetts',
                        'charges' => 'Aiding and abetting the rescue of a fugitive from labor under the Fugitive Slave Act of 1850.',
                        'arrest_date' => '1851-02-21',
                        'convicted' => 'No — federal jury refused to convict.',
                        'sentence' => 'Acquitted.',
                    ],
                ],
            ],
            [
                'name' => 'Elizur Wright',
                'first_name' => 'Elizur',
                'last_name' => 'Wright',
                'description' => 'Abolitionist newspaper editor (Boston Chronotype, Commonwealth) and Boston Vigilance Committee organizer. Prosecuted by the federal government for his role in the February 1851 rescue of Shadrach Minkins; the jury hung and the case was eventually dropped. Wright was also a foundational actuary of U.S. life insurance regulation and a co-founder of the American Anti-Slavery Society.',
                'state' => 'Massachusetts',
                'race' => 'White',
                'gender' => 'Male',
                'birthdate' => '1804-02-12',
                'death_date' => '1885-11-22',
                'ideologies' => ['Abolitionist'],
                'affiliation' => ['Boston Vigilance Committee', 'American Anti-Slavery Society'],
                'era' => '1850s',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'institution_state' => 'Massachusetts',
                        'charges' => 'Aiding and abetting the rescue of a fugitive from labor under the Fugitive Slave Act of 1850.',
                        'arrest_date' => '1851-03-01',
                        'convicted' => 'No — hung jury; case dropped.',
                        'sentence' => 'Charges dismissed after hung jury.',
                    ],
                ],
            ],
            [
                'name' => 'Charles G. Davis',
                'first_name' => 'Charles',
                'middle_name' => 'G.',
                'last_name' => 'Davis',
                'description' => 'Massachusetts abolitionist attorney and Boston Vigilance Committee member, prosecuted by the federal government for his role in the February 1851 rescue of Shadrach Minkins. The jury acquitted.',
                'state' => 'Massachusetts',
                'race' => 'White',
                'gender' => 'Male',
                'ideologies' => ['Abolitionist'],
                'affiliation' => ['Boston Vigilance Committee'],
                'era' => '1850s',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'institution_state' => 'Massachusetts',
                        'charges' => 'Aiding and abetting the rescue of a fugitive from labor under the Fugitive Slave Act of 1850.',
                        'arrest_date' => '1851-02-21',
                        'convicted' => 'No — acquitted.',
                        'sentence' => 'Acquitted.',
                    ],
                ],
            ],
        ];
    }
}
