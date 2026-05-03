<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddCooperatorPrisoners extends Command
{
    protected $signature = 'prisoners:add-cooperators';
    protected $description = 'Add ELF/ALF/animal-rights defendants who cooperated with the federal government and served prison time, including the Operation Backfire cooperators, Frank Ambrose, Justin Samuel, and Doug Ellerman.';

    public function handle(): int
    {
        $created = 0;
        $skipped = 0;

        $bopGeneric = Institution::firstOrCreate(
            ['name' => 'Federal Bureau of Prisons (location varied)'],
            ['city' => null, 'state' => null]
        );

        $usdcOregon = Institution::firstOrCreate(
            ['name' => 'United States District Court, District of Oregon (Operation Backfire prosecutions)'],
            ['city' => 'Eugene', 'state' => 'Oregon']
        );

        $usdcWestMI = Institution::firstOrCreate(
            ['name' => 'United States District Court, Western District of Michigan'],
            ['city' => 'Grand Rapids', 'state' => 'Michigan']
        );

        $usdcWWI = Institution::firstOrCreate(
            ['name' => 'United States District Court, Western District of Wisconsin'],
            ['city' => 'Madison', 'state' => 'Wisconsin']
        );

        $usdcUtah = Institution::firstOrCreate(
            ['name' => 'United States District Court, District of Utah'],
            ['city' => 'Salt Lake City', 'state' => 'Utah']
        );

        $defendants = [];

        // ─── Operation Backfire cooperators ───
        $backfireFraming = "Operation Backfire was the multi-agency federal investigation that, between 2005 and 2008, prosecuted approximately a dozen members of the small Eugene-area ELF/ALF cell known to investigators as 'The Family,' which had carried out roughly twenty arsons and acts of sabotage in five western states between 1996 and 2001. After the December 2005 arrests, the defendants split into two groups: those who cooperated with the federal government — testifying against their co-defendants in exchange for substantially reduced sentences — and those who did not (Daniel McGowan, Marius Mason, Joyanna Zacher, Nathan Block, Jonathan Paul, Briana Waters, Joseph Dibee, Justin Solondz, Rebecca Rubin, and the late William 'Avalon' Rodgers). This entry is for one of the cooperating defendants. The Earth Liberation Prisoners support network has consistently distinguished between cooperating and non-cooperating defendants, and many in the radical-ecology movement do not consider cooperators political prisoners. They are included here because they served meaningful federal prison time.";

        $defendants[] = [
            'data' => [
                'name'        => 'Stanislas Meyerhoff',
                'first_name'  => 'Stanislas',
                'middle_name' => 'Gregory',
                'last_name'   => 'Meyerhoff',
                'gender'      => 'Male',
                'birthdate'   => '1977-12-23',
                'state'       => 'Oregon',
                'era'         => '2000s',
                'ideologies'  => ['Environmental', 'Anarchist'],
                'affiliation' => ['Earth Liberation Front'],
                'in_custody'  => false, 'released' => true,
                'description' => "Stanislas Gregory Meyerhoff was one of the two most active members of the Eugene-area Earth Liberation Front cell, taking part in nearly all of the cell's major actions between 1996 and 2001 — including the 1998 Vail ski resort arson (then the most expensive act of ecotage in U.S. history), the 1997 Cavel West horse slaughterhouse arson in Redmond, Oregon, and the 1998 fire at the U.S. Forest Industries office in Medford. He pleaded guilty under Operation Backfire on July 20, 2006 to 54 charges and cooperated extensively with federal prosecutors against his co-defendants.\n\nOn May 23, 2007, U.S. District Judge Ann Aiken sentenced him to 156 months (13 years) in federal prison — the longest of any Operation Backfire defendant, but substantially below the prosecution's recommended 188 months. The terrorism enhancement was applied. He served his sentence in the federal Bureau of Prisons.\n\n".$backfireFraming,
            ],
            'case' => [
                'institution_id'     => $bopGeneric->id,
                'charges'            => 'Conspiracy, arson, use of destructive devices, and destruction of an energy facility (Operation Backfire — 54 counts)',
                'arrest_date'        => '2005-12-07',
                'incarceration_date' => '2007-05-23',
                'release_date'       => '2018-05-23',
                'convicted'          => 'Yes — guilty plea, July 20, 2006 (cooperated with federal prosecutors)',
                'sentence'           => '13 years (156 months) in federal prison with terrorism enhancement',
                'judge'              => 'Ann Aiken',
            ],
        ];

        $defendants[] = [
            'data' => [
                'name'        => 'Kevin Tubbs',
                'first_name'  => 'Kevin',
                'last_name'   => 'Tubbs',
                'gender'      => 'Male',
                'birthdate'   => '1969-01-01',
                'state'       => 'Oregon',
                'era'         => '2000s',
                'ideologies'  => ['Environmental', 'Anarchist'],
                'affiliation' => ['Earth Liberation Front'],
                'in_custody'  => false, 'released' => true,
                'description' => "Kevin Tubbs was a longstanding member of the Eugene-area Earth Liberation Front cell. He pleaded guilty under Operation Backfire to 56 charges, the most of any defendant, and cooperated with federal prosecutors. On May 24, 2007, Judge Ann Aiken sentenced him to 12 years and seven months in federal prison — the second-longest sentence in the Operation Backfire prosecutions — with the terrorism enhancement applied.\n\n".$backfireFraming,
            ],
            'case' => [
                'institution_id'     => $bopGeneric->id,
                'charges'            => 'Conspiracy, arson, use of destructive devices (Operation Backfire — 56 counts)',
                'arrest_date'        => '2005-12-07',
                'incarceration_date' => '2007-05-24',
                'release_date'       => '2017-12-24',
                'convicted'          => 'Yes — guilty plea, 2006 (cooperated with federal prosecutors)',
                'sentence'           => '12 years and 7 months (151 months) in federal prison with terrorism enhancement',
                'judge'              => 'Ann Aiken',
            ],
        ];

        $defendants[] = [
            'data' => [
                'name'        => 'Chelsea Gerlach',
                'first_name'  => 'Chelsea',
                'middle_name' => 'Dawn',
                'last_name'   => 'Gerlach',
                'gender'      => 'Female',
                'birthdate'   => '1977-12-15',
                'state'       => 'Oregon',
                'era'         => '2000s',
                'ideologies'  => ['Environmental', 'Anarchist'],
                'affiliation' => ['Earth Liberation Front'],
                'in_custody'  => false, 'released' => true,
                'description' => "Chelsea Dawn Gerlach was a member of the Eugene-area Earth Liberation Front cell during its most active period in the late 1990s, taking part in actions including the 1998 Vail ski resort arson and the 1999 toppling of a Bonneville Power Administration high-voltage transmission tower near Bend, Oregon. She was arrested December 7, 2005 in the Operation Backfire sweep, pleaded guilty in 2006 to 18 charges, and cooperated with federal prosecutors against her co-defendants. On May 25, 2007, Judge Ann Aiken sentenced her to 9 years (108 months) in federal prison with the terrorism enhancement applied.\n\n".$backfireFraming,
            ],
            'case' => [
                'institution_id'     => $bopGeneric->id,
                'charges'            => 'Conspiracy, arson, destruction of an energy facility (Operation Backfire — 18 counts)',
                'arrest_date'        => '2005-12-07',
                'incarceration_date' => '2007-05-25',
                'release_date'       => '2015-05-25',
                'convicted'          => 'Yes — guilty plea, December 15, 2006 (cooperated with federal prosecutors)',
                'sentence'           => '9 years (108 months) in federal prison with terrorism enhancement',
                'judge'              => 'Ann Aiken',
            ],
        ];

        $defendants[] = [
            'data' => [
                'name'        => 'Suzanne Savoie',
                'first_name'  => 'Suzanne',
                'last_name'   => 'Savoie',
                'gender'      => 'Female',
                'birthdate'   => '1978-01-01',
                'state'       => 'Oregon',
                'era'         => '2000s',
                'ideologies'  => ['Environmental', 'Anarchist'],
                'affiliation' => ['Earth Liberation Front'],
                'in_custody'  => false, 'released' => true,
                'description' => "Suzanne Savoie was a member of the Eugene-area Earth Liberation Front cell who participated in actions including the 2001 Superior Lumber Company arson in Glendale, Oregon. She was arrested in Operation Backfire and pleaded guilty in 2006 to 15 charges, cooperating with federal prosecutors. On May 22, 2007, Judge Ann Aiken sentenced her to 51 months (4 years and 3 months) in federal prison.\n\n".$backfireFraming,
            ],
            'case' => [
                'institution_id'     => $bopGeneric->id,
                'charges'            => 'Conspiracy, arson (Operation Backfire — 15 counts)',
                'arrest_date'        => '2005-12-07',
                'incarceration_date' => '2007-05-22',
                'release_date'       => '2010-08-22',
                'convicted'          => 'Yes — guilty plea, 2006 (cooperated with federal prosecutors)',
                'sentence'           => '4 years and 3 months (51 months) in federal prison',
                'judge'              => 'Ann Aiken',
            ],
        ];

        $defendants[] = [
            'data' => [
                'name'        => 'Kendall Tankersley',
                'first_name'  => 'Sarah',
                'middle_name' => 'Kendall',
                'last_name'   => 'Tankersley',
                'aka'         => 'Sarah Kendall Harvey',
                'gender'      => 'Female',
                'birthdate'   => '1977-01-01',
                'state'       => 'Oregon',
                'era'         => '2000s',
                'ideologies'  => ['Environmental', 'Anarchist'],
                'affiliation' => ['Earth Liberation Front'],
                'in_custody'  => false, 'released' => true,
                'description' => "Sarah Kendall Tankersley (later Sarah Kendall Harvey) was a peripheral member of the Eugene-area Earth Liberation Front cell. She was arrested in Operation Backfire in December 2005, pleaded guilty in 2006 to three charges, and cooperated with federal prosecutors. On May 24, 2007, Judge Ann Aiken sentenced her to 46 months (3 years and 10 months) in federal prison.\n\n".$backfireFraming,
            ],
            'case' => [
                'institution_id'     => $bopGeneric->id,
                'charges'            => 'Conspiracy, arson (Operation Backfire — 3 counts)',
                'arrest_date'        => '2005-12-07',
                'incarceration_date' => '2007-05-24',
                'release_date'       => '2011-03-24',
                'convicted'          => 'Yes — guilty plea, 2006 (cooperated with federal prosecutors)',
                'sentence'           => '3 years and 10 months (46 months) in federal prison',
                'judge'              => 'Ann Aiken',
            ],
        ];

        $defendants[] = [
            'data' => [
                'name'        => 'Darren Thurston',
                'first_name'  => 'Darren',
                'last_name'   => 'Thurston',
                'gender'      => 'Male',
                'birthdate'   => '1971-01-01',
                'state'       => 'Oregon',
                'race'        => null,
                'era'         => '2000s',
                'ideologies'  => ['Animal rights', 'Environmental', 'Anarchist'],
                'affiliation' => ['Earth Liberation Front', 'Animal Liberation Front'],
                'in_custody'  => false, 'released' => true,
                'description' => "Darren Thurston is a Canadian animal rights and environmental activist with a long history of ALF organizing in Edmonton, Alberta dating back to the early 1990s. He was implicated through Operation Backfire in the August 1997 horse rescue at the Cavel West horse slaughterhouse in Redmond, Oregon, in which more than 100 horses were freed before the facility was burned. He pleaded guilty in 2006 to two charges and cooperated with federal prosecutors. On May 24, 2007, Judge Ann Aiken sentenced him to 37 months (3 years and 1 month) in federal prison.\n\n".$backfireFraming,
            ],
            'case' => [
                'institution_id'     => $bopGeneric->id,
                'charges'            => 'Conspiracy, arson — Cavel West horse slaughterhouse, August 1997 (Operation Backfire — 2 counts)',
                'arrest_date'        => '2005-12-07',
                'incarceration_date' => '2007-05-24',
                'release_date'       => '2010-06-24',
                'convicted'          => 'Yes — guilty plea, 2006 (cooperated with federal prosecutors)',
                'sentence'           => '37 months in federal prison',
                'judge'              => 'Ann Aiken',
            ],
        ];

        // ─── Frank Ambrose ───
        $defendants[] = [
            'data' => [
                'name'        => 'Frank Ambrose',
                'first_name'  => 'Frank',
                'last_name'   => 'Ambrose',
                'gender'      => 'Male',
                'birthdate'   => '1975-01-01',
                'state'       => 'Indiana',
                'era'         => '2000s',
                'ideologies'  => ['Environmental', 'Anarchist'],
                'affiliation' => ['Earth Liberation Front'],
                'in_custody'  => false, 'released' => true,
                'description' => "Frank Ambrose was an Earth Liberation Front activist and the then-husband of fellow ELF organizer Marius Mason. He participated in numerous Indiana and Michigan ELF actions between 1999 and 2003, including tree-spikings and arsons of homes under construction. After federal investigators tied him to the actions in 2007, he agreed to cooperate with prosecutors and over the following year recorded approximately 178 conversations with Marius Mason and other activists for the FBI. His testimony was the principal basis for the 22-year sentence Mason received in February 2009 — at the time the longest sentence ever imposed on a U.S. eco-defendant.\n\nAmbrose divorced Mason on the day of Mason's arrest. He pleaded guilty to conspiracy to commit arson and was sentenced in October 2008 by U.S. District Judge Paul Maloney to 9 years in federal prison; that sentence was later reduced by the same judge to less than six years in light of his cooperation. He served his sentence at federal facilities and has not been publicly active since release. He is widely regarded within the radical-ecology movement as the most consequential federal informant of the Green Scare era; he is included here because he served meaningful federal prison time.",
            ],
            'case' => [
                'institution_id'     => $usdcWestMI->id,
                'charges'            => 'Conspiracy to commit arson — Earth Liberation Front actions in Indiana and Michigan (1999–2003)',
                'arrest_date'        => '2008-03-10',
                'incarceration_date' => '2008-10-15',
                'release_date'       => '2014-04-15',
                'convicted'          => 'Yes — guilty plea, 2008 (cooperated with federal prosecutors against Marius Mason)',
                'sentence'           => 'Initially 9 years; reduced to under 6 years for cooperation',
                'judge'              => 'Paul L. Maloney',
            ],
        ];

        // ─── Justin Samuel ───
        $defendants[] = [
            'data' => [
                'name'        => 'Justin Samuel',
                'first_name'  => 'Justin',
                'middle_name' => 'C.',
                'last_name'   => 'Samuel',
                'gender'      => 'Male',
                'birthdate'   => '1978-01-01',
                'state'       => 'Wisconsin',
                'era'         => '2000s',
                'ideologies'  => ['Animal rights'],
                'affiliation' => ['Animal Liberation Front'],
                'in_custody'  => false, 'released' => true,
                'description' => "Justin Samuel was an animal rights activist who, with Peter Young, broke onto mink farms in Iowa, South Dakota, and Wisconsin in 1997 and freed approximately 7,000 mink in what was then the largest single ALF action in U.S. history. Samuel fled the United States after the actions; he was arrested in Belgium in September 1999 and extradited to the U.S. He pleaded guilty in 2000 to two counts under the Animal Enterprise Protection Act and was sentenced to two years in federal prison.\n\nIn his plea agreement Samuel cooperated with federal prosecutors and named Peter Young as his co-defendant. Young remained at large for seven years before his 2005 capture; the prosecution that sent Young to two years in federal prison rested heavily on Samuel's testimony. Samuel served his sentence and was released well before Young was even captured. He is included here because he served meaningful federal prison time.",
            ],
            'case' => [
                'institution_id'     => $usdcWWI->id,
                'charges'            => 'Two counts of violating the Animal Enterprise Protection Act (1997 mink farm raids in Iowa, South Dakota, and Wisconsin)',
                'arrest_date'        => '1999-09-04',
                'incarceration_date' => '2000-09-01',
                'release_date'       => '2002-09-01',
                'convicted'          => 'Yes — guilty plea, 2000 (cooperated with federal prosecutors against Peter Young)',
                'sentence'           => '2 years in federal prison',
            ],
        ];

        // ─── Doug Ellerman ───
        $defendants[] = [
            'data' => [
                'name'        => 'Douglas Joshua Ellerman',
                'first_name'  => 'Douglas',
                'middle_name' => 'Joshua',
                'last_name'   => 'Ellerman',
                'aka'         => 'Josh Ellerman',
                'gender'      => 'Male',
                'birthdate'   => '1978-01-01',
                'state'       => 'Utah',
                'era'         => '1990s',
                'ideologies'  => ['Animal rights'],
                'affiliation' => ['Animal Liberation Front'],
                'in_custody'  => false, 'released' => true,
                'description' => "Douglas Joshua 'Josh' Ellerman was a 19-year-old Salt Lake City animal rights activist who on March 11, 1997 set fire to the Fur Breeders Agricultural Cooperative in Sandy, Utah, using six pipe bombs and causing approximately one million dollars in damage. He turned himself in to federal authorities and pleaded guilty to 3 of 16 felony counts in a plea agreement under which he cooperated with investigators in identifying other Animal Liberation Front members. He was sentenced in 1998 to 7 years in federal prison and ordered to pay approximately \$750,000 in restitution. He is included here because he served meaningful federal prison time.",
            ],
            'case' => [
                'institution_id'     => $usdcUtah->id,
                'charges'            => 'Use of destructive devices; arson; conspiracy — March 11, 1997 bombing and arson of the Fur Breeders Agricultural Cooperative in Sandy, Utah (Animal Liberation Front)',
                'arrest_date'        => '1997-06-19',
                'incarceration_date' => '1998-03-01',
                'release_date'       => '2004-03-01',
                'convicted'          => 'Yes — guilty plea, 1998 (cooperated with federal prosecutors)',
                'sentence'           => '7 years in federal prison; approximately $750,000 in restitution',
            ],
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
                PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $entry['case']));

                $this->info("Added {$prisoner->name}");
                $created++;
            });
        }

        $this->info("\nDone. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
