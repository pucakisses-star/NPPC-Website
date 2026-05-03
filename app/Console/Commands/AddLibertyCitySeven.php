<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddLibertyCitySeven extends Command
{
    protected $signature = 'prisoners:add-liberty-city-seven';
    protected $description = 'Add the seven defendants of the 2006 Liberty City case (Miami "Seas of David"), one of the most-cited FBI entrapment prosecutions of the post-9/11 era.';

    private const CASE_CONTEXT = <<<'TXT'
On June 23, 2006, the Federal Bureau of Investigation arrested seven men in the predominantly Haitian-American Liberty City neighborhood of Miami, Florida. They were members of a small religious collective called the Seas of David — founded by Narseal Batiste, the group blended elements of Christianity, the Moorish Science Temple, and Black Hebrew Israelite practice, and met in a warehouse in Liberty City where its members worked construction jobs and studied scripture. Federal prosecutors charged all seven with conspiring with al-Qaeda to blow up the 110-story Sears Tower in Chicago, the FBI's Miami office, and other federal buildings.

The plot was, from the federal government's first contact with the group, manufactured by FBI confidential informants. After receiving an October 2005 tip, the Miami FBI inserted a paid informant (Abbas al-Saidi) into the group's circle, and later a second informant (Elie Assad) posing as an al-Qaeda recruiter. The informants supplied the group with cash, with combat boots, and with the suggestion that they "swear bayat" (an oath of loyalty to Osama bin Laden) — a ceremony staged at the informants' insistence. The defendants never had any actual contact with al-Qaeda, never possessed any weapons or explosives, never traveled to any of the buildings prosecutors said they planned to attack, and never made any concrete steps toward an actual operation. Their case became, alongside the Newburgh Four, one of the two most cited examples of post-9/11 FBI manufactured-plot terrorism prosecutions.

The case went through three federal trials before Judge Joan A. Lenard at the U.S. District Court in Miami. The first trial (October–December 2007) ended in a hung jury and acquittal for Lyglenson Lemorin. The second trial (January–April 2008) also ended in a hung jury for the remaining six defendants. The third trial (January–May 2009) finally produced convictions of Narseal Batiste, Patrick Abraham, Stanley Grant Phanor, Burson Augustin, and Rothschild Augustine, and an acquittal for Naudimar Herrera. The five convicted defendants were sentenced together on November 20, 2009. The Eleventh Circuit affirmed the convictions in 2013 over a vigorous dissent that compared the case to the wave of "outrageous government conduct" cases that had once been recognized in U.S. law.
TXT;

    public function handle(): int
    {
        $created = 0;
        $skipped = 0;

        $miami = Institution::firstOrCreate(
            ['name' => 'Federal Detention Center, Miami'],
            ['city' => 'Miami', 'state' => 'Florida']
        );

        $bopGeneric = Institution::firstOrCreate(
            ['name' => 'Federal Bureau of Prisons (location varied)'],
            ['city' => null, 'state' => null]
        );

        $haiti = Institution::firstOrCreate(
            ['name' => 'Deported to Haiti as a terrorism-related removal'],
            ['city' => 'Port-au-Prince', 'state' => 'Haiti']
        );

        $sharedIdeologies  = ['Targeted by post-9/11 terrorism prosecution', 'Religious community organizing'];
        $sharedAffiliation = ['Seas of David'];

        $entries = [
            [
                'data' => [
                    'name' => 'Narseal Batiste', 'first_name' => 'Narseal', 'last_name' => 'Batiste',
                    'birthdate' => '1973-01-01',
                    'gender' => 'Male', 'race' => 'Black', 'state' => 'Florida', 'era' => '2000s',
                    'ideologies' => $sharedIdeologies, 'affiliation' => $sharedAffiliation,
                    'in_custody' => false, 'released' => true, 'awaiting_trial' => false,
                    'description' => "Narseal Batiste was the founder and acknowledged spiritual leader of the Seas of David, the small Liberty City religious collective the FBI targeted in 2006. A Chicago native who had moved to Miami and worked in construction, he was the only Liberty City defendant convicted on all four counts of the federal indictment. He was sentenced on November 20, 2009 by Judge Joan A. Lenard to 162 months (13 years and 6 months) in federal prison, followed by 35 years of supervised release. He served his sentence in the federal Bureau of Prisons and was released in approximately 2018.\n\n".self::CASE_CONTEXT,
                ],
                'case' => [
                    'institution_id' => $bopGeneric->id,
                    'charges'        => 'Conspiracy to provide material support to terrorists; conspiracy to provide material support to a designated foreign terrorist organization (al-Qaeda); conspiracy to maliciously damage and destroy buildings used in interstate commerce; conspiracy to levy war against the United States',
                    'arrest_date'    => '2006-06-23',
                    'incarceration_date' => '2009-11-20',
                    'release_date'   => '2018-08-23',
                    'convicted'      => 'Yes — federal jury verdict, May 12, 2009, U.S. District Court for the Southern District of Florida (third trial; first two trials ended in hung juries)',
                    'sentence'       => '162 months (13.5 years) federal prison plus 35 years supervised release',
                    'judge'          => 'Joan A. Lenard',
                ],
            ],
            [
                'data' => [
                    'name' => 'Patrick Abraham', 'first_name' => 'Patrick', 'last_name' => 'Abraham',
                    'birthdate' => '1969-01-01',
                    'gender' => 'Male', 'race' => 'Black', 'state' => 'Florida', 'era' => '2000s',
                    'ideologies' => $sharedIdeologies, 'affiliation' => $sharedAffiliation,
                    'in_custody' => false, 'released' => true, 'awaiting_trial' => false,
                    'description' => "Patrick Abraham was a Haitian national legally residing in the United States and a member of the Seas of David in Liberty City, Miami. He was convicted in the third Liberty City trial on multiple counts of conspiracy and sentenced on November 20, 2009 to 112.5 months (9 years and 4 months) in federal prison, followed by 15 years of supervised release. After completion of his federal sentence he was subject to immigration removal to Haiti.\n\n".self::CASE_CONTEXT,
                ],
                'case' => [
                    'institution_id' => $bopGeneric->id,
                    'charges'        => 'Conspiracy to provide material support to a designated foreign terrorist organization (al-Qaeda); conspiracy to maliciously damage and destroy buildings used in interstate commerce; conspiracy to levy war against the United States',
                    'arrest_date'    => '2006-06-23',
                    'incarceration_date' => '2009-11-20',
                    'release_date'   => '2016-05-20',
                    'convicted'      => 'Yes — federal jury verdict, May 12, 2009 (third trial)',
                    'sentence'       => '112.5 months (9 years and 4 months) federal prison plus 15 years supervised release',
                    'judge'          => 'Joan A. Lenard',
                ],
            ],
            [
                'data' => [
                    'name' => 'Stanley Grant Phanor', 'first_name' => 'Stanley', 'middle_name' => 'Grant', 'last_name' => 'Phanor',
                    'birthdate' => '1975-01-01',
                    'gender' => 'Male', 'race' => 'Black', 'state' => 'Florida', 'era' => '2000s',
                    'ideologies' => $sharedIdeologies, 'affiliation' => $sharedAffiliation,
                    'in_custody' => false, 'released' => true, 'awaiting_trial' => false,
                    'description' => "Stanley Grant Phanor was a member of the Seas of David in Liberty City, Miami. He was convicted in the third Liberty City trial and sentenced on November 20, 2009 to 96 months (8 years) in federal prison, followed by 15 years of supervised release.\n\n".self::CASE_CONTEXT,
                ],
                'case' => [
                    'institution_id' => $bopGeneric->id,
                    'charges'        => 'Conspiracy to provide material support to terrorists and to a designated foreign terrorist organization; conspiracy to maliciously damage federal buildings',
                    'arrest_date'    => '2006-06-23',
                    'incarceration_date' => '2009-11-20',
                    'release_date'   => '2015-11-20',
                    'convicted'      => 'Yes — federal jury verdict, May 12, 2009 (third trial)',
                    'sentence'       => '96 months (8 years) federal prison plus 15 years supervised release',
                    'judge'          => 'Joan A. Lenard',
                ],
            ],
            [
                'data' => [
                    'name' => 'Burson Augustin', 'first_name' => 'Burson', 'last_name' => 'Augustin',
                    'birthdate' => '1985-01-01',
                    'gender' => 'Male', 'race' => 'Black', 'state' => 'Florida', 'era' => '2000s',
                    'ideologies' => $sharedIdeologies, 'affiliation' => $sharedAffiliation,
                    'in_custody' => false, 'released' => true, 'awaiting_trial' => false,
                    'description' => "Burson Augustin was the youngest of the convicted Liberty City defendants — barely 21 years old at the time of the June 2006 arrests. A member of the Seas of David, he was convicted in the third trial and sentenced on November 20, 2009 to 72 months (6 years) in federal prison, followed by 10 years of supervised release.\n\n".self::CASE_CONTEXT,
                ],
                'case' => [
                    'institution_id' => $bopGeneric->id,
                    'charges'        => 'Conspiracy to provide material support to terrorists and to a designated foreign terrorist organization',
                    'arrest_date'    => '2006-06-23',
                    'incarceration_date' => '2009-11-20',
                    'release_date'   => '2013-11-20',
                    'convicted'      => 'Yes — federal jury verdict, May 12, 2009 (third trial)',
                    'sentence'       => '72 months (6 years) federal prison plus 10 years supervised release',
                    'judge'          => 'Joan A. Lenard',
                ],
            ],
            [
                'data' => [
                    'name' => 'Rothschild Augustine', 'first_name' => 'Rothschild', 'last_name' => 'Augustine',
                    'birthdate' => '1981-01-01',
                    'gender' => 'Male', 'race' => 'Black', 'state' => 'Florida', 'era' => '2000s',
                    'ideologies' => $sharedIdeologies, 'affiliation' => $sharedAffiliation,
                    'in_custody' => false, 'released' => true, 'awaiting_trial' => false,
                    'description' => "Rothschild Augustine was a member of the Seas of David in Liberty City, Miami. He was convicted in the third Liberty City trial and sentenced on November 20, 2009 to 84 months (7 years) in federal prison, followed by 10 years of supervised release.\n\n".self::CASE_CONTEXT,
                ],
                'case' => [
                    'institution_id' => $bopGeneric->id,
                    'charges'        => 'Conspiracy to provide material support to terrorists and to a designated foreign terrorist organization',
                    'arrest_date'    => '2006-06-23',
                    'incarceration_date' => '2009-11-20',
                    'release_date'   => '2014-11-20',
                    'convicted'      => 'Yes — federal jury verdict, May 12, 2009 (third trial)',
                    'sentence'       => '84 months (7 years) federal prison plus 10 years supervised release',
                    'judge'          => 'Joan A. Lenard',
                ],
            ],
            [
                'data' => [
                    'name' => 'Naudimar Herrera', 'first_name' => 'Naudimar', 'last_name' => 'Herrera',
                    'birthdate' => '1984-01-01',
                    'gender' => 'Male', 'state' => 'Florida', 'era' => '2000s',
                    'ideologies' => $sharedIdeologies, 'affiliation' => $sharedAffiliation,
                    'in_custody' => false, 'released' => true, 'awaiting_trial' => false,
                    'description' => "Naudimar Herrera was the only Liberty City defendant to be acquitted at the third trial. He was held in federal pretrial detention from his June 23, 2006 arrest through the end of the third trial in May 2009 — nearly three full years in custody — before a federal jury cleared him on all four counts. His acquittal was the only complete acquittal of any Seas of David defendant in the case.\n\n".self::CASE_CONTEXT,
                ],
                'case' => [
                    'institution_id' => $miami->id,
                    'charges'        => 'Conspiracy to provide material support to terrorists; conspiracy to provide material support to a designated foreign terrorist organization; conspiracy to maliciously damage federal buildings; conspiracy to levy war against the United States',
                    'arrest_date'    => '2006-06-23',
                    'incarceration_date' => '2006-06-23',
                    'release_date'   => '2009-05-12',
                    'convicted'      => 'No — acquitted on all four counts by federal jury, May 12, 2009 (third trial)',
                    'sentence'       => 'No conviction; held approximately 3 years in federal pretrial detention at the Federal Detention Center, Miami',
                    'judge'          => 'Joan A. Lenard',
                ],
            ],
            [
                'data' => [
                    'name' => 'Lyglenson Lemorin', 'first_name' => 'Lyglenson', 'last_name' => 'Lemorin',
                    'birthdate' => '1976-01-01',
                    'gender' => 'Male', 'race' => 'Black', 'state' => 'Florida', 'era' => '2000s',
                    'ideologies' => $sharedIdeologies, 'affiliation' => $sharedAffiliation,
                    'in_custody' => false, 'released' => true, 'awaiting_trial' => false,
                    'description' => "Lyglenson Lemorin was a Haitian-born member of the Seas of David and a legal U.S. resident at the time of the June 23, 2006 arrests. He was held in federal pretrial detention through the first Liberty City trial in fall 2007. The first jury hung as to the other six defendants but unanimously acquitted Lemorin on all charges in December 2007. Despite his acquittal in federal criminal court, the Department of Homeland Security immediately initiated immigration removal proceedings against him as a 'terrorism-related' deportee, and he was deported to Haiti in 2010 — making him one of the most cited cases of a federal terrorism acquittal followed by an immigration-court deportation on the same alleged conduct.\n\n".self::CASE_CONTEXT,
                ],
                'case' => [
                    'institution_id' => $haiti->id,
                    'charges'        => 'Conspiracy to provide material support to terrorists; conspiracy to provide material support to a designated foreign terrorist organization; conspiracy to maliciously damage federal buildings; conspiracy to levy war against the United States',
                    'arrest_date'    => '2006-06-23',
                    'incarceration_date' => '2006-06-23',
                    'release_date'   => '2010-01-15',
                    'convicted'      => 'No — acquitted on all four counts by federal jury, December 13, 2007 (first trial). Subsequently subjected to immigration removal proceedings on the same alleged conduct and deported to Haiti in 2010',
                    'sentence'       => 'No criminal conviction; held approximately 18 months in federal pretrial detention plus additional immigration detention; ultimately deported to Haiti as a "terrorism-related" removal',
                    'judge'          => 'Joan A. Lenard (criminal); separate immigration judge',
                ],
            ],
        ];

        foreach ($entries as $entry) {
            DB::transaction(function () use ($entry, &$created, &$skipped) {
                $name = $entry['data']['name'];
                if (Prisoner::where('name', $name)->exists()) {
                    $this->warn("  skipped: {$name} already exists");
                    $skipped++;
                    return;
                }

                $prisoner = Prisoner::create($entry['data']);
                PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $entry['case']));

                $this->info("  added {$prisoner->name}");
                $created++;
            });
        }

        $this->info("\nDone. {$created} created, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
