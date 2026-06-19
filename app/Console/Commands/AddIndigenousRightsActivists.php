<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Indigenous-rights activists found by reading the Wikipedia "American
 * Indigenous rights activists" category who were jailed/charged/exiled for
 * political activity and were missing from the database (the major figures and
 * the AIM overlap were already in). Excluded after vetting: advocates/elders
 * with no arrest (Mankiller, Carmen, Quannah Chasinghorse, Tokata Iron Eyes,
 * Clyde Warrior, Tall Oak Weeden, etc.), June Sapiel (her son was arrested,
 * not her), and Matthew McDaniel (detained by Thailand — outside U.S. scope).
 *
 * Note: the arrests for LaDuke, Goldtooth, and Camp-Horinek are documented in
 * news reporting, not their Wikipedia bios. Idempotent.
 */
final class AddIndigenousRightsActivists extends Command
{
    protected $signature = 'prisoners:add-indigenous-rights-activists';

    protected $description = 'Add jailed/exiled Indigenous-rights activists from the Wikipedia category that were missing';

    public function handle(): int
    {
        $added = 0;
        $skipped = 0;

        foreach ($this->records() as $r) {
            if (Prisoner::withUnderReview()->where('name', $r['name'])->exists()) {
                $this->warn("Exists, skipping: {$r['name']}");
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($r) {
                $cases = $r['cases'] ?? [];
                unset($r['cases']);
                $prisoner = Prisoner::create($r);
                foreach ($cases as $c) {
                    $c['prisoner_id'] = $prisoner->id;
                    PrisonerCase::create($c);
                }
            });

            $this->info("Added: {$r['name']}");
            $added++;
        }

        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }

    private function records(): array
    {
        return [
            [
                'name' => 'Gladys Bissonette',
                'first_name' => 'Gladys',
                'last_name' => 'Bissonette',
                'gender' => 'Female',
                'race' => 'Native American',
                'state' => 'South Dakota',
                'era' => '1970s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['American Indian Movement', 'Oglala Sioux Civil Rights Organization'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Gladys Bissonette was an Oglala Lakota elder and a leader of the traditionalist faction on the Pine Ridge Reservation that opposed tribal chairman Dick Wilson. A principal organizer of the 1973 Wounded Knee occupation, she helped convene the meeting that launched it, ran its health clinic during the 71-day siege, and was a negotiator with the U.S. government. Afterward she was prosecuted in U.S. federal court for her role in the occupation. She endured the killing of her nephew Pedro Bissonette in October 1973.',
                'cases' => [[
                    'charges' => 'Federally prosecuted for her leadership role in the 1973 Wounded Knee occupation',
                    'convicted' => 'Tried in U.S. federal court following the occupation (outcome not documented in available sources)',
                ]],
            ],
            [
                'name' => 'Edgar Bear Runner',
                'first_name' => 'Edgar',
                'middle_name' => 'Donroy',
                'last_name' => 'Bear Runner',
                'gender' => 'Male',
                'race' => 'Native American',
                'birthdate' => '1951-05-28',
                'death_date' => '2021-07-04',
                'state' => 'South Dakota',
                'era' => '1970s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['American Indian Movement'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Edgar Bear Runner (1951–2021) was an Oglala Lakota activist with the American Indian Movement. He was arrested and charged with "rioting to obstruct justice" for the February 6, 1973 protest at the Custer County Courthouse in South Dakota and was acquitted by a jury, later facing related state prosecutions in South Dakota and Nebraska. He played a notable role as a negotiator during the 1975 Jumping Bull ranch incident at Pine Ridge.',
                'cases' => [[
                    'charges' => 'Rioting to obstruct justice — the February 6, 1973 Custer County Courthouse protest (AIM); also charged in related 1976 South Dakota and Nebraska cases',
                    'arrest_date' => '1973-02-06',
                    'convicted' => 'Acquitted by a jury',
                ]],
            ],
            [
                'name' => 'Richard Oakes',
                'first_name' => 'Richard',
                'last_name' => 'Oakes',
                'gender' => 'Male',
                'race' => 'Native American',
                'birthdate' => '1942-05-22',
                'death_date' => '1972-09-20',
                'state' => 'California',
                'era' => '1960s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['Indians of All Tribes'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Richard Oakes (1942–1972) was a Mohawk activist who led the 1969–1971 occupation of Alcatraz Island, a landmark event of the Red Power movement, and helped establish one of the first Native American Studies programs. His activism brought tear gas, beatings, and repeated brief jailings. He was shot and killed in 1972 by a YMCA camp manager, who was acquitted after claiming self-defense.',
                'cases' => [[
                    'charges' => 'Jailed repeatedly for his activism, chiefly leading the 1969–1971 occupation of Alcatraz Island',
                    'convicted' => 'Brief jailings tied to his activism (specific charges/dates not documented)',
                ]],
            ],
            [
                'name' => 'Pun Plamondon',
                'first_name' => 'Lawrence',
                'middle_name' => 'Robert',
                'last_name' => 'Plamondon',
                'aka' => 'Pun',
                'gender' => 'Male',
                'race' => 'Native American',
                'birthdate' => '1945-04-27',
                'death_date' => '2023-03-06',
                'state' => 'Michigan',
                'era' => '1970s',
                'ideologies' => ['New Left', 'Anti-War'],
                'affiliation' => ['White Panther Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Lawrence Robert "Pun" Plamondon (1945–2023), of Ottawa/Odawa descent, co-founded the White Panther Party with John Sinclair in Ann Arbor, Michigan. Indicted over the 1968 bombing of a CIA recruiting office, he went underground and in 1970 became the first counterculture figure on the FBI\'s Ten Most Wanted Fugitives list. He served roughly 32 months in federal prison, and his prosecution produced the landmark 1972 Supreme Court "Keith" decision (United States v. U.S. District Court) barring warrantless domestic "national security" wiretaps — after which his charges were dismissed. He later became a Native American storyteller and wrote the memoir "Lost from the Ottawa."',
                'cases' => [[
                    'charges' => 'Conspiracy in the 1968 bombing of a CIA recruiting office in Ann Arbor, Michigan (White Panther Party); FBI Ten Most Wanted Fugitive (1970)',
                    'arrest_date' => '1970-07-23',
                    'convicted' => 'Convicted; charges ultimately dismissed after United States v. U.S. District Court (the "Keith" case), 407 U.S. 297 (1972), suppressed the illegal warrantless-wiretap evidence',
                    'sentence' => 'About 32 months served in federal prison',
                ]],
            ],
            [
                'name' => 'Winona LaDuke',
                'first_name' => 'Winona',
                'last_name' => 'LaDuke',
                'gender' => 'Female',
                'race' => 'Native American',
                'birthdate' => '1959-08-18',
                'state' => 'Minnesota',
                'era' => '2020s',
                'ideologies' => ['Indigenous Sovereignty', 'Environmental'],
                'affiliation' => ['Honor the Earth'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Winona LaDuke (born 1959) is an Ojibwe economist, environmentalist, and writer from the White Earth Nation, a co-founder of Honor the Earth, and twice the Green Party\'s U.S. vice-presidential nominee. She has led Indigenous land- and treaty-rights and anti-pipeline campaigns for decades, including the Standing Rock resistance to the Dakota Access Pipeline and opposition to Enbridge\'s Line 3. In July 2021 she was arrested and jailed for three days for civil disobedience at a Line 3 worksite in Minnesota; the charges were dismissed in 2023.',
                'cases' => [[
                    'charges' => 'Gross-misdemeanor trespass on a critical public-service facility and obstruction of legal process — Line 3 (Enbridge) pipeline protest, Wadena County, Minnesota',
                    'arrest_date' => '2021-07-19',
                    'convicted' => 'Jailed three days; charges dismissed in 2023 by a judge who cited the government\'s mistreatment of Indigenous people',
                    'sentence' => 'Three days jailed (charges later dismissed)',
                ]],
            ],
            [
                'name' => 'Casey Camp-Horinek',
                'first_name' => 'Casey',
                'last_name' => 'Camp-Horinek',
                'gender' => 'Female',
                'race' => 'Native American',
                'state' => 'Oklahoma',
                'era' => '2010s',
                'ideologies' => ['Indigenous Sovereignty', 'Environmental'],
                'affiliation' => ['Indigenous Environmental Network'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Casey Camp-Horinek is a Ponca Nation of Oklahoma elder, hereditary Drumkeeper, former tribal councilwoman, environmental ambassador, and actress, and a sister of AIM leader Carter Camp. A longtime Indigenous- and environmental-rights organizer with the Indigenous Environmental Network, she helped lead the Ponca "Rights of Nature" campaign. She was arrested for civil disobedience at the Standing Rock / Dakota Access Pipeline protests (2016–17) and again at the 2021 "People vs. Fossil Fuels" action at the White House.',
                'cases' => [[
                    'charges' => 'Civil-disobedience arrests at the Dakota Access Pipeline protests (2016–17) and the "People vs. Fossil Fuels" action at the White House (October 11, 2021)',
                    'arrest_date' => '2021-10-11',
                    'convicted' => 'Arrested for civil disobedience',
                ]],
            ],
            [
                'name' => 'Tom Goldtooth',
                'first_name' => 'Tom',
                'last_name' => 'Goldtooth',
                'aka' => 'Tom B.K. Goldtooth; Bruce Kendall Goldtooth',
                'gender' => 'Male',
                'race' => 'Native American',
                'state' => 'Minnesota',
                'era' => '2010s',
                'ideologies' => ['Indigenous Sovereignty', 'Environmental'],
                'affiliation' => ['Indigenous Environmental Network'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Tom B.K. Goldtooth (born 1953), Diné (Navajo) and Bdewakantonwan Dakota, is the longtime executive director of the Indigenous Environmental Network and a co-founder of the Climate Justice Now! coalition. A leading voice linking colonialism, environmental destruction, and climate change, he received the 2015 Gandhi Peace Award. He was arrested at the White House in 2011 during an Indigenous Day of Action against the Keystone XL pipeline.',
                'cases' => [[
                    'charges' => 'Arrested at the White House in 2011 during an Indigenous Day of Action against the Keystone XL pipeline (civil disobedience)',
                    'convicted' => 'Arrested for civil disobedience',
                ]],
            ],
        ];
    }
}
