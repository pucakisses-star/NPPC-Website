<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds the Native American activists found by reading the Wikipedia "Native
 * American activists" category who were arrested, jailed, criminally charged,
 * or forced into exile for political/movement activity and were missing from
 * the database. Most of the ~234 people in that category are advocacy-only
 * figures (politicians, academics, artists, writers, lawyers, suffragists) and
 * were excluded; the qualifiers cluster around the Alcatraz/Fort Lawton/Wounded
 * Knee occupations, the Pacific Northwest "fish-in" treaty-fishing protests,
 * the U.S. Senate KXL protest, WWII draft resistance, and forced exile. Several
 * of these arrests are documented only in news/archival sources, not the
 * subjects' Wikipedia bios.
 *
 * Included: LaNada War Jack, Bernie Whitebear, Madonna Thunder Hawk, Mary Crow
 * Dog, Greg Grey Cloud, Ramona Bennett, Clinton Rickard, Lyda Conley, Deskaheh,
 * Thomas Banyacya, Carrie Dann, Mary Dann, Ellen Moves Camp.
 *
 * Excluded after vetting:
 *  - Already in the DB under another name: John Boncore / "Splitting the Sky"
 *    (stored as "John Boncore Hill").
 *  - No documented personal arrest (advocacy/organizers): Adam Fortunate Eagle,
 *    Stella Leach, Woesha Cloud North, Selo Black Crow, Reuben Snake, Mel Thom,
 *    Amanda Blackhorse, Nathan Phillips, Corrina Gould, Andrea Carmen,
 *    Dan Katchongva (counseled the draft resisters; not jailed himself).
 *  - Borderline, left out: Minnie Two Shoes (1975 exile was an intra-AIM purge,
 *    not state action), Wallace "Mad Bear" Anderson (militant direct action, but
 *    his personal arrest is unconfirmed), Alex White Plume (3 armed federal hemp
 *    raids + a civil injunction, but the action was civil — no arrest).
 *
 * Note: Mary Dann's basis is civil (named defendant in United States v. Dann +
 * armed BLM seizures) rather than a personal criminal arrest; she is included
 * alongside her sister Carrie (who was arrested at the Nevada Test Site in 2007)
 * as the recognized Western Shoshone resistance pair. Idempotent.
 */
final class AddNativeActivists extends Command
{
    protected $signature = 'prisoners:add-native-activists';

    protected $description = 'Add jailed/charged/exiled Native American activists from the Wikipedia category that were missing';

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
                'name' => 'LaNada War Jack',
                'first_name' => 'LaNada',
                'last_name' => 'War Jack',
                'aka' => 'LaNada Means; LaNada Boyer; LaNada Vernae Boyer',
                'gender' => 'Female',
                'race' => 'Native American',
                'state' => 'California',
                'era' => '1960s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['Indians of All Tribes', 'Third World Liberation Front'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'LaNada War Jack (born LaNada Vernae Boyer, 1947), Shoshone-Bannock of the Fort Hall Reservation, was the first Native American student admitted to UC Berkeley. As a leader of the 1969 Third World Liberation Front strike she was arrested and suspended, and the strike won the first ethnic-studies department in the country. She then became one of the principal organizers and leaders of the 1969–1971 Occupation of Alcatraz, staying nearly to the end, and later became the first member of her tribe to earn a PhD.',
                'cases' => [[
                    'charges' => 'Arrested during the 1969 Third World Liberation Front strike at UC Berkeley (strike charges ranged up to felonious assault on an officer); a leader of the 1969–1971 Occupation of Alcatraz',
                    'convicted' => 'Arrested and suspended from UC Berkeley; case disposition not documented in available sources',
                ]],
            ],
            [
                'name' => 'Bernie Whitebear',
                'first_name' => 'Bernie',
                'last_name' => 'Whitebear',
                'aka' => 'Bernard Reyes',
                'gender' => 'Male',
                'race' => 'Native American',
                'state' => 'Washington',
                'era' => '1970s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['United Indians of All Tribes'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Bernie Whitebear (born Bernard Reyes, 1937–2000), a Colville (Sinixt) activist, was a central leader of Seattle\'s Red Power movement. On March 8, 1970 he co-led the occupation of Fort Lawton to reclaim federal land for urban Native people; he was arrested in the military\'s forceful response and released the next day, then kept organizing. The campaign won a 99-year lease in Discovery Park, where he founded the United Indians of All Tribes Foundation and the Daybreak Star Cultural Center.',
                'cases' => [[
                    'charges' => 'Arrested at the March 8, 1970 occupation of Fort Lawton, Seattle (reported as "felonious riot"), with United Indians of All Tribes',
                    'arrest_date' => '1970-03-08',
                    'convicted' => 'Arrested and released the next day; no conviction documented. The occupation campaign succeeded politically with the Discovery Park land grant',
                ]],
            ],
            [
                'name' => 'Madonna Thunder Hawk',
                'first_name' => 'Madonna',
                'last_name' => 'Thunder Hawk',
                'gender' => 'Female',
                'race' => 'Native American',
                'state' => 'South Dakota',
                'era' => '1970s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['American Indian Movement', 'Women of All Red Nations'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Madonna Thunder Hawk (born 1940) is an Oohenumpa Lakota (Cheyenne River Sioux) organizer who has taken part in nearly every major modern Native occupation, from Alcatraz (1969) to Wounded Knee (1973) to Standing Rock (2016). She was arrested and jailed for several nights on the opening night of the Wounded Knee siege, then returned to serve as a camp medic. A co-founder of Women of All Red Nations, she later directed the Wounded Knee Legal Defense/Offense Committee and remains a prominent Lakota elder and organizer.',
                'cases' => [[
                    'charges' => 'Arrested on the opening night of the 1973 Wounded Knee occupation',
                    'arrest_date' => '1973-02-27',
                    'convicted' => 'Jailed about four to five nights, then released; returned to the occupation as a medic',
                    'sentence' => 'About 4–5 nights in jail',
                ]],
            ],
            [
                'name' => 'Mary Crow Dog',
                'first_name' => 'Mary',
                'last_name' => 'Crow Dog',
                'aka' => 'Mary Brave Bird; Mary Ellen Moore-Richard',
                'gender' => 'Female',
                'race' => 'Native American',
                'state' => 'South Dakota',
                'era' => '1970s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['American Indian Movement'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Mary Crow Dog, later Mary Brave Bird (1954–2013), was a Sicangu (Brulé) Lakota AIM activist from the Rosebud Reservation and author of the American Book Award–winning memoir "Lakota Woman." She joined the American Indian Movement as a teenager and gave birth to her son during the 1973 occupation of Wounded Knee, for which she was arrested and briefly jailed. She later married the medicine man Leonard Crow Dog and continued writing and activism until her death in 2013.',
                'cases' => [[
                    'charges' => 'Arrested for participating in the 1973 Wounded Knee occupation, during which she gave birth in the besieged camp',
                    'convicted' => 'Jailed briefly (~24 hours) and separated from her newborn; charges dropped',
                    'sentence' => 'Briefly jailed; charges dropped',
                ]],
            ],
            [
                'name' => 'Greg Grey Cloud',
                'first_name' => 'Greg',
                'last_name' => 'Grey Cloud',
                'gender' => 'Male',
                'race' => 'Native American',
                'state' => 'South Dakota',
                'era' => '2010s',
                'ideologies' => ['Indigenous Sovereignty', 'Environmental'],
                'affiliation' => ['Wica Agli'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Greg Grey Cloud is a Crow Creek Sioux singer, educator, and activist from South Dakota, and co-founder of Wica Agli, a Native men\'s organization against domestic and sexual violence. On November 18, 2014, he was arrested by Capitol Police after singing a Lakota honor song from the U.S. Senate gallery as senators rejected the Keystone XL pipeline; all charges were later dismissed. In 2016 he led the horseback "Spirit Riders" during the Standing Rock / Dakota Access Pipeline resistance.',
                'cases' => [[
                    'charges' => 'Arrested for disrupting Senate proceedings — singing a Lakota honor song from the U.S. Senate gallery after the vote rejecting the Keystone XL pipeline (Washington, D.C.)',
                    'arrest_date' => '2014-11-18',
                    'convicted' => 'All charges dismissed',
                ]],
            ],
            [
                'name' => 'Ramona Bennett',
                'first_name' => 'Ramona',
                'last_name' => 'Bennett',
                'gender' => 'Female',
                'race' => 'Native American',
                'state' => 'Washington',
                'era' => '1970s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['Survival of American Indians Association'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Ramona Bennett (born 1938) is a Puyallup activist and former tribal council chair who was a principal leader of the Pacific Northwest "fish wars" over treaty fishing rights. She co-founded the Survival of American Indians Association, which brought the "fish-ins" to national attention. On September 9, 1970 she was among roughly 55 adults arrested when authorities destroyed the armed Puyallup River fishing camp she helped lead; the protesters were later acquitted, and the campaign helped produce the 1974 Boldt decision affirming tribal treaty fishing rights.',
                'cases' => [[
                    'charges' => 'Mass arrest at the September 9, 1970 destruction of the Puyallup River treaty-fishing protest camp ("fish wars"), Tacoma, Washington — a leader of the camp',
                    'arrest_date' => '1970-09-09',
                    'convicted' => 'Acquitted by a jury',
                    'sentence' => 'Faced up to roughly 35 years but served no jail time; acquitted',
                ]],
            ],
            [
                'name' => 'Clinton Rickard',
                'first_name' => 'Clinton',
                'last_name' => 'Rickard',
                'gender' => 'Male',
                'race' => 'Native American',
                'state' => 'New York',
                'era' => '1920s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['Indian Defense League of America'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Chief Clinton Rickard (1882–1971) was a Tuscarora leader and U.S. Army veteran who founded the Indian Defense League of America in 1926 to defend Haudenosaunee rights to cross the U.S.–Canada border freely under the Jay Treaty. He organized the defense of Mohawk ironworker Paul Diabo, arrested in Philadelphia in 1925 as an "illegal alien," winning a 1927 ruling that upheld Native border-crossing rights. Rickard himself was arrested and held in a Canadian jail while exercising those border-crossing rights, and he founded the annual border-crossing ceremony that continues today.',
                'cases' => [[
                    'charges' => 'Arrested and held in a Canadian jail while asserting Jay Treaty Native border-crossing rights',
                    'convicted' => 'Held in jail, then released using his legal knowledge and help from friends; no lasting conviction documented',
                ]],
            ],
            [
                'name' => 'Lyda Conley',
                'first_name' => 'Lyda',
                'last_name' => 'Conley',
                'aka' => 'Eliza Burton Conley',
                'gender' => 'Female',
                'race' => 'Native American',
                'state' => 'Kansas',
                'era' => '1900s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Eliza Burton "Lyda" Conley (c. 1869–1946) was a Wyandot lawyer — the first woman admitted to the Kansas bar — who fought for decades to stop the sale of the Huron Indian Cemetery in Kansas City, Kansas, where her parents were buried. With her sisters she built an armed shack, "Fort Conley," inside the cemetery and physically guarded the graves. She was arrested multiple times for interfering with city officials and in June 1937 served ten days in jail rather than pay a fine for defending the cemetery. In 1909 she became the first Native American woman to argue a case before the U.S. Supreme Court (Conley v. Ballinger).',
                'cases' => [[
                    'charges' => 'Disturbance of the peace / interfering with city officials while physically guarding the Huron Indian Cemetery, Kansas City, Kansas (multiple arrests)',
                    'convicted' => 'Convicted of disturbing the peace (June 1937)',
                    'sentence' => 'Served ten days in jail (June 1937) rather than pay a fine; arrested several times over the cemetery',
                ]],
            ],
            [
                'name' => 'Deskaheh',
                'first_name' => 'Levi',
                'last_name' => 'General',
                'aka' => 'Levi General',
                'gender' => 'Male',
                'race' => 'Native American',
                'state' => 'New York',
                'era' => '1920s',
                'death_date' => '1925-06-27',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['Six Nations Hereditary Council'],
                'in_custody' => false,
                'released' => false,
                'in_exile' => true,
                'currently_in_exile' => false,
                'awaiting_trial' => false,
                'description' => 'Levi General, known by his hereditary title Deskaheh (1873–1925), was a Cayuga chief and Speaker of the Six Nations Hereditary Council. In 1923 he traveled to the League of Nations in Geneva to assert Haudenosaunee sovereignty against Canada\'s imposition of an elected band council. While he was abroad the RCMP dissolved the traditional council; fearing arrest, he never returned to Canada and died in 1925 in exile at the Tuscarora Reservation in New York, in the home of fellow activist Chief Clinton Rickard.',
                'cases' => [[
                    'charges' => 'Forced into exile after the Canadian government and RCMP dissolved the Six Nations Hereditary Council during his 1923 League of Nations sovereignty campaign; fearing arrest, he never returned to Canada',
                    'in_exile_since' => '1924-09-17',
                    'end_of_exile' => '1925-06-27',
                    'convicted' => 'Died in exile at the Tuscarora Reservation, New York, in 1925, never returning home',
                ]],
            ],
            [
                'name' => 'Thomas Banyacya',
                'first_name' => 'Thomas',
                'last_name' => 'Banyacya',
                'aka' => 'Thomas Jenkins',
                'gender' => 'Male',
                'race' => 'Native American',
                'state' => 'Arizona',
                'era' => '1940s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['Hopi Traditionalist Movement'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Thomas Banyacya (born Thomas Jenkins, 1909–1999) was a Hopi spokesman appointed in 1948 by traditional Hopi leaders to carry the Hopi message of peace and prophecy to the wider world. During World War II he refused to register for the draft as contrary to Hopi religion and sovereignty, and was convicted of draft evasion, serving about seven years at the Tucson Federal Prison Camp. He later won conscientious-objector status for Hopi men and famously addressed the UN General Assembly in 1992.',
                'cases' => [[
                    'charges' => 'Draft evasion — refusal to register for the World War II draft on Hopi religious and sovereignty grounds',
                    'convicted' => 'Convicted of draft evasion',
                    'sentence' => 'About seven years\' imprisonment at the Tucson Federal Prison Camp (Catalina Federal Honor Camp), c. 1942–1949',
                ]],
            ],
            [
                'name' => 'Carrie Dann',
                'first_name' => 'Carrie',
                'last_name' => 'Dann',
                'gender' => 'Female',
                'race' => 'Native American',
                'state' => 'Nevada',
                'era' => '1990s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['Western Shoshone Defense Project'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Carrie Dann (1932–2021) was a Western Shoshone rancher and land-rights leader who, with her sister Mary, refused to pay federal grazing fees on ancestral land secured by the 1863 Treaty of Ruby Valley. Their decades-long resistance produced the U.S. Supreme Court case United States v. Dann and repeated armed BLM roundups of their cattle and horses. She was arrested in 2007 for trespass at the Nevada Test Site, co-founded the Western Shoshone Defense Project, and shared the 1993 Right Livelihood Award.',
                'cases' => [
                    [
                        'charges' => 'Federal grazing-trespass litigation (United States v. Dann) and armed BLM roundups seizing the Dann sisters\' cattle and horses over Western Shoshone treaty land (1974–2003)',
                        'convicted' => 'Lost the federal land claim; livestock repeatedly seized in armed federal roundups (1992, 2002–2003)',
                    ],
                    [
                        'charges' => 'Arrested for trespassing at the Nevada Test Site during a Nevada Desert Experience anti-nuclear protest (with 38 others)',
                        'arrest_date' => '2007-04-01',
                        'convicted' => 'Arrested for trespass',
                    ],
                ],
            ],
            [
                'name' => 'Mary Dann',
                'first_name' => 'Mary',
                'last_name' => 'Dann',
                'gender' => 'Female',
                'race' => 'Native American',
                'state' => 'Nevada',
                'era' => '1990s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['Western Shoshone Defense Project'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Mary Dann (1923–2005) was a Western Shoshone rancher who, with her younger sister Carrie, led decades of resistance to federal authority over ancestral land guaranteed by the 1863 Treaty of Ruby Valley. As a named defendant in United States v. Dann, she refused to pay BLM grazing fees and saw the sisters\' cattle and horses seized in armed federal roundups. She shared the 1993 Right Livelihood Award and died in 2005 in a ranch accident.',
                'cases' => [[
                    'charges' => 'Named defendant in the federal grazing-trespass suit United States v. Dann and a direct target of armed BLM roundups seizing the Dann sisters\' livestock over Western Shoshone treaty land',
                    'convicted' => 'Lost the federal land claim; livestock repeatedly seized in armed federal roundups. No personal arrest documented before her death in 2005',
                ]],
            ],
            [
                'name' => 'Ellen Moves Camp',
                'first_name' => 'Ellen',
                'last_name' => 'Moves Camp',
                'gender' => 'Female',
                'race' => 'Native American',
                'state' => 'South Dakota',
                'era' => '1970s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['American Indian Movement', 'Oglala Sioux Civil Rights Organization'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Ellen Moves Camp (1931–2008) was an Oglala Lakota activist and co-founder of the Oglala Sioux Civil Rights Organization. She was one of the women who pushed AIM leaders to act, helping precipitate the 1973 occupation of Wounded Knee, where she ran camp logistics and negotiated with federal officials during the 71-day siege. During the subsequent Wounded Knee trials she was forcibly removed from the courtroom and arrested, and she remained a respected movement elder until her death in 2008.',
                'cases' => [[
                    'charges' => 'A principal women\'s leader inside the 71-day 1973 Wounded Knee occupation (Oglala Sioux Civil Rights Organization co-founder); arrested during the 1974 Wounded Knee trials',
                    'convicted' => 'Removed from the courtroom and arrested during the trials; no charges or sentence documented',
                ]],
            ],
        ];
    }
}
