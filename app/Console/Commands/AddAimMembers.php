<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds American Indian Movement (AIM) members found by reading the Wikipedia
 * "Members of the AIM" category who were jailed or otherwise prosecuted for
 * political/movement activity and were missing from the database. The major
 * figures (Peltier, Banks, Means, Crow Dog, Carter Camp, Trudell, the Butlers,
 * Loud Hawk, Redner, Dick Marshall, Standing Deer, etc.) were already in.
 *
 * Excluded after vetting: Darlene "Ka-Mook" Nichols (became an FBI informant
 * against AIM); Arlo Looking Cloud and Theda Clarke (the internal killing of
 * Anna Mae Aquash — not political activity against the state).
 *
 * Idempotent: skips any person whose name already exists. Uncertain dates are
 * left blank rather than guessed.
 */
final class AddAimMembers extends Command
{
    protected $signature = 'prisoners:add-aim-members';

    protected $description = 'Add jailed/exiled AIM members from the Wikipedia category that were missing';

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
        $base = [
            'gender' => 'Male',
            'race' => 'Native American',
            'ideologies' => ['Indigenous Sovereignty'],
            'affiliation' => ['American Indian Movement'],
            'in_custody' => false,
            'released' => true,
            'awaiting_trial' => false,
        ];

        return [
            array_merge($base, [
                'name' => 'Clyde Bellecourt',
                'first_name' => 'Clyde',
                'middle_name' => 'Howard',
                'last_name' => 'Bellecourt',
                'aka' => 'Nee-gon-we-way-we-dun (Thunder Before the Storm)',
                'birthdate' => '1936-05-08',
                'death_date' => '2022-01-11',
                'state' => 'Minnesota',
                'era' => '1970s',
                'description' => 'Clyde Howard Bellecourt (1936–2022) was an Ojibwe (White Earth) leader who co-founded the American Indian Movement in Minneapolis in 1968. A central organizer and negotiator during the 1972 Trail of Broken Treaties occupation of the Bureau of Indian Affairs building and the 1973 Wounded Knee occupation, he was arrested in connection with Wounded Knee and held on bond before the charges were dropped; he had earlier been jailed in 1962 for activism. He founded AIM survival schools and numerous Native community institutions and remained a leading Red Power figure until his death.',
                'cases' => [[
                    'charges' => 'Arrested in connection with the 1973 Wounded Knee occupation (Pine Ridge, SD); held on $25,000 bond. Earlier jailed in 1962 for activism.',
                    'convicted' => 'Wounded Knee charges later dropped',
                ]],
            ]),
            array_merge($base, [
                'name' => 'Eddie Benton-Banai',
                'first_name' => 'Edward',
                'last_name' => 'Benton-Banai',
                'aka' => 'Bawdwaywidun Banaise',
                'death_date' => '2020-11-30',
                'state' => 'Wisconsin',
                'era' => '1960s',
                'description' => 'Edward "Eddie" Benton-Banai (1931–2020) was an Ojibwe-Anishinaabe spiritual leader, educator, and co-founder of the American Indian Movement in 1968. He was jailed at Minnesota\'s Stillwater Prison in 1962 for his activism, where he organized Native prisoners. He went on to found the Red School House in St. Paul and to author "The Mishomis Book," a foundational text of Anishinaabe teachings, and served as a Grand Chief of the Three Fires Midewiwin Lodge.',
                'cases' => [[
                    'charges' => 'Jailed in 1962 at Stillwater Prison (Minnesota) for activism, where he organized Native prisoners',
                ]],
            ]),
            array_merge($base, [
                'name' => 'Debra White Plume',
                'first_name' => 'Debra',
                'last_name' => 'White Plume',
                'aka' => 'Wioweya Najin Win',
                'gender' => 'Female',
                'state' => 'South Dakota',
                'era' => '1970s',
                'description' => 'Debra White Plume (1954–2020) was an Oglala Lakota activist and water protector from the Pine Ridge Reservation. She joined the 1973 Wounded Knee occupation and spent decades defending treaty rights and the environment through nonviolent direct action. She was arrested at a 2011 protest outside the White House against the Keystone Pipeline and helped lead resistance to the Keystone XL and Dakota Access pipelines.',
                'cases' => [[
                    'charges' => 'Arrested at a 2011 White House protest against the Keystone XL pipeline; participant in the 1973 Wounded Knee occupation',
                ]],
            ]),
            array_merge($base, [
                'name' => 'Frank LaMere',
                'first_name' => 'Franklin',
                'middle_name' => 'Dean',
                'last_name' => 'LaMere',
                'death_date' => '2019-06-16',
                'state' => 'Nebraska',
                'era' => '1990s',
                'description' => 'Frank LaMere (1950–2019) was a Winnebago activist and Nebraska political leader known for indigenous-rights advocacy and his long campaign against beer sales to Native people at Whiteclay, Nebraska. On July 3, 1999 he was arrested with Russell Means and five others for crossing police lines during a Whiteclay protest. He worked on Native rights, anti-addiction, and environmental causes until his death.',
                'cases' => [[
                    'charges' => 'Arrested July 3, 1999 for crossing police lines during a protest against beer sales to Native people at Whiteclay, Nebraska (with Russell Means and five others)',
                    'arrest_date' => '1999-07-03',
                ]],
            ]),
            array_merge($base, [
                'name' => 'Richard Ray Whitman',
                'first_name' => 'Richard',
                'middle_name' => 'Ray',
                'last_name' => 'Whitman',
                'state' => 'Oklahoma',
                'era' => '1970s',
                'description' => 'Richard Ray Whitman (born 1949) is a Yuchi/Muscogee (Creek) multidisciplinary artist, photographer, poet, and actor from Oklahoma, best known for his "Street Chiefs" photographic series. He joined the AIM-led 1973 Wounded Knee occupation as an artist documenting events on film. He was arrested at the occupation\'s end, briefly jailed in Rapid City, South Dakota, and bused out of state, and the FBI permanently confiscated his camera and nine rolls of film.',
                'cases' => [[
                    'charges' => 'Arrested at the end of the 1973 Wounded Knee occupation; briefly jailed in Rapid City, SD and forced out of the state; camera and nine rolls of film confiscated by the FBI',
                ]],
            ]),
            array_merge($base, [
                'name' => 'Wes Studi',
                'first_name' => 'Wesley',
                'last_name' => 'Studi',
                'birthdate' => '1947-12-17',
                'state' => 'Oklahoma',
                'era' => '1970s',
                'description' => 'Wes Studi (born 1947) is a Cherokee actor and producer — later the first Native American to win an Academy Award for acting (2019 honorary Oscar) — who in the early 1970s was an American Indian Movement activist. He took part in the 1972 Trail of Broken Treaties march and BIA building occupation and the 1973 Wounded Knee occupation, where he was arrested and jailed, then released on the condition that he leave South Dakota.',
                'cases' => [[
                    'charges' => 'Arrested at the 1973 Wounded Knee occupation; jailed and released on condition of leaving South Dakota',
                ]],
            ]),
        ];
    }
}
