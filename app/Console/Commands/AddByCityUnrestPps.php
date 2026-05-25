<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 13 PPs surfaced from the deep crawl of Wikipedia's per-city
 * civil unrest lists.
 *
 *   - Mike Forcia (AIM, 2020 Columbus statue toppling at MN capitol)
 *   - 2 Days of Rage 1969 Weatherman defendants: John "JJ" Jacobs,
 *     Brian Flanagan
 *   - 6 SWP / Teamsters 544 defendants from the 1941 Minneapolis
 *     Smith Act trial (the first-ever Smith Act prosecution):
 *     Clarence Hamel, Emil Hansen, Karl Kuehn, Edward Palmquist,
 *     Alfred Russell, Oscar Schoenfeld
 *   - 2 Detroit 1833 Blackburn Riots fugitive-slave defendants
 *   - 2 Atlanta 2020 Wendy's arson defendants (Rayshard Brooks
 *     killing)
 *
 * Era values per project decade-string convention.
 */
final class AddByCityUnrestPps extends Command {
    protected $signature = 'archive:add-by-city-unrest-pps';
    protected $description = 'Add 13 PPs from per-city unrest list crawl';

    public function handle(): int {
        $added = 0; $skipped = 0;
        foreach ($this->prisoners() as $p) {
            $exit = $this->call('prisoner:add', ['json' => json_encode($p)]);
            if ($exit === self::SUCCESS) { $this->info('ADD: '.$p['name']); $added++; }
            else { $skipped++; }
        }
        $this->info("Done — added {$added}, skipped {$skipped}.");
        return self::SUCCESS;
    }

    /** @return array<int, array<string, mixed>> */
    private function prisoners(): array {
        $out = [];

        // === Minneapolis 2020 — Mike Forcia ===
        $out[] = [
            'name' => 'Mike Forcia',
            'first_name' => 'Mike',
            'last_name' => 'Forcia',
            'description' => 'Anishinaabe (Bad River Band of Lake Superior Ojibwe) American Indian Movement organizer in Minnesota. Led the public toppling of the Christopher Columbus statue on the Minnesota State Capitol grounds in St. Paul on June 10, 2020 during the George Floyd uprising. Charged in state court with felony destruction of public property; pled guilty and was sentenced in October 2021 to 100 hours of community service to be performed at MN AIM and the Lower Phalen Creek Project.',
            'state' => 'Minnesota',
            'race' => 'Indigenous',
            'gender' => 'Male',
            'ideologies' => ['Indigenous resistance', 'AIM', 'Anti-colonial'],
            'affiliation' => ['American Indian Movement'],
            'era' => '2020s',
            'in_custody' => false,
            'released' => true,
            'cases' => [[
                'institution_state' => 'Minnesota',
                'charges' => 'Felony destruction of public property (Columbus statue toppling, MN State Capitol, June 10, 2020).',
                'arrest_date' => '2020-06-11',
                'sentenced_date' => '2021-10-13',
                'convicted' => 'Pled guilty.',
                'sentence' => '100 hours community service at MN AIM and the Lower Phalen Creek Project.',
            ]],
        ];

        // === Chicago — Days of Rage 1969 (Weatherman) ===
        $weathermanBase = [
            'state' => 'Illinois',
            'race' => 'White',
            'gender' => 'Male',
            'ideologies' => ['Anti-imperialist', 'Anti-Vietnam War'],
            'affiliation' => ['Weatherman', 'SDS'],
            'era' => '1960s',
            'in_custody' => false,
            'released' => true,
        ];
        $out[] = [
            'name' => 'John Jacobs',
            'aka' => 'JJ',
            'first_name' => 'John',
            'last_name' => 'Jacobs',
            'description' => 'Weather Underground / SDS co-founder ("JJ"). Indicted for conspiracy and crossing state lines to riot after the October 1969 Days of Rage in Chicago. Went underground in 1970 with the rest of the Weather collective; remained a fugitive for decades. Died in Vancouver in 1997.',
            'birthdate' => '1947-02-02',
            'death_date' => '1997-09-08',
            'cases' => [[
                'institution_state' => 'Illinois',
                'charges' => 'Conspiracy; crossing state lines to riot (Days of Rage, October 8-11, 1969); federal UFAP warrant added when he went underground.',
                'arrest_date' => '1969-10-11',
                'convicted' => 'No — charges ultimately dropped due to government illegal surveillance.',
                'sentence' => 'Fugitive for decades; never tried.',
            ]],
        ] + $weathermanBase;
        $out[] = [
            'name' => 'Brian Flanagan',
            'first_name' => 'Brian',
            'last_name' => 'Flanagan',
            'description' => 'Weather Underground / SDS member tried for attempted murder of Chicago city attorney Richard Elrod during the October 1969 Days of Rage — when Weather collective members ran through downtown Chicago smashing cars and shop windows in protest of the war and the Chicago 8 trial. Acquitted at trial. Later notable for being one of the highest-earning Jeopardy! champions in the show\'s history.',
            'cases' => [[
                'institution_state' => 'Illinois',
                'charges' => 'Attempted murder of Chicago city attorney Richard Elrod (Days of Rage, October 11, 1969).',
                'arrest_date' => '1969-10-11',
                'sentenced_date' => '1971-02-01',
                'convicted' => 'No — acquitted.',
                'sentence' => 'Acquitted.',
            ]],
        ] + $weathermanBase;

        // === SWP 1941 Smith Act — 6 lesser-known co-defendants ===
        $swpDesc = 'Member of the Socialist Workers Party (SWP) or Minneapolis Teamsters Local 544 prosecuted in the 1941 Minneapolis Smith Act trial — the first-ever federal prosecution under the Smith Act of 1940. The Roosevelt administration charged 29 SWP and Teamsters 544 leaders with conspiracy to advocate the violent overthrow of the U.S. government, in retaliation for the SWP\'s antiwar organizing and its leadership of the 1934 Minneapolis general strike. 18 defendants were convicted; the 12-month group received sentences served at federal correctional institutions.';
        $swpCase = [
            'institution_state' => 'Minnesota',
            'charges' => 'Smith Act of 1940 — conspiracy to advocate the overthrow of the U.S. government by force or violence.',
            'arrest_date' => '1941-07-15',
            'sentenced_date' => '1941-12-08',
            'convicted' => 'Yes — upheld by 8th Circuit; SCOTUS denied cert.',
            'sentence' => '12 months federal imprisonment.',
        ];
        $swpBase = [
            'state' => 'Minnesota',
            'race' => 'White',
            'gender' => 'Male',
            'ideologies' => ['Trotskyist', 'Socialist'],
            'affiliation' => ['Socialist Workers Party', 'Teamsters Local 544'],
            'era' => '1940s',
            'in_custody' => false,
            'released' => true,
        ];
        foreach ([
            'Clarence Hamel' => 'Minneapolis Teamsters Local 544 organizer.',
            'Emil Hansen' => 'Minneapolis Teamsters Local 544 organizer.',
            'Karl Kuehn' => 'SWP organizer.',
            'Edward Palmquist' => 'SWP organizer.',
            'Alfred Russell' => 'SWP organizer.',
            'Oscar Schoenfeld' => 'SWP organizer.',
        ] as $name => $bio) {
            $parts = preg_split('/\s+/', $name);
            $out[] = [
                'name' => $name,
                'first_name' => $parts[0],
                'last_name' => end($parts),
                'description' => $swpDesc.' '.$bio,
                'cases' => [$swpCase],
            ] + $swpBase;
        }

        // === Detroit 1833 Blackburn Riots ===
        $blackburnDesc = 'Fugitive enslaved person arrested in Detroit on June 14, 1833 under the Fugitive Slave Act of 1793 after being recognized by a Kentucky slave-hunter. Held in the Wayne County Jail awaiting return to enslavement in Kentucky. The arrest provoked Detroit\'s Black community — almost entirely refugees from southern slavery — to organize a successful rescue: a group of Black women led by Caroline French and Tabitha Lightfoot smuggled Rutha out by switching clothes; the next day a Black mob freed Thornton from the sheriff during the move to the steamboat dock. Both fled to Canada via the Underground Railroad and lived out their lives as free people in Toronto. The "Blackburn Riots" were Detroit\'s first major racial unrest and the first major Underground Railroad rescue in Michigan.';
        $blackburnBase = [
            'state' => 'Michigan',
            'race' => 'Black',
            'ideologies' => ['Anti-slavery', 'Black freedom'],
            'affiliation' => ['Fugitives from enslavement'],
            'era' => '1830s',
            'in_custody' => false,
            'released' => true,
            'in_exile' => true,
        ];
        $out[] = [
            'name' => 'Thornton Blackburn',
            'first_name' => 'Thornton',
            'last_name' => 'Blackburn',
            'description' => $blackburnDesc.' He later became one of the first Black taxi-business owners in Toronto.',
            'gender' => 'Male',
            'birthdate' => '1812-01-01',
            'death_date' => '1890-02-26',
            'cases' => [[
                'institution_name' => 'Wayne County Jail',
                'institution_state' => 'Michigan',
                'charges' => 'Held under the Fugitive Slave Act of 1793.',
                'arrest_date' => '1833-06-14',
                'release_date' => '1833-06-17',
                'sentence' => 'Rescued from sheriff\'s custody by Black mob during transfer; escaped to Canada via Underground Railroad.',
            ]],
        ] + $blackburnBase;
        $out[] = [
            'name' => 'Rutha Blackburn',
            'aka' => 'Lucie Blackburn',
            'first_name' => 'Rutha',
            'last_name' => 'Blackburn',
            'description' => $blackburnDesc.' Smuggled out by clothes-swap rescue led by Caroline French and Tabitha Lightfoot.',
            'gender' => 'Female',
            'death_date' => '1895-02-06',
            'cases' => [[
                'institution_name' => 'Wayne County Jail',
                'institution_state' => 'Michigan',
                'charges' => 'Held under the Fugitive Slave Act of 1793.',
                'arrest_date' => '1833-06-14',
                'release_date' => '1833-06-16',
                'sentence' => 'Rescued by clothes-switch from Wayne County Jail; escaped to Canada.',
            ]],
        ] + $blackburnBase;

        // === Atlanta 2020 — Wendy's arson (Rayshard Brooks killing) ===
        $wendysDesc = 'Atlanta defendant prosecuted for the June 13, 2020 arson of the Wendy\'s restaurant at 125 University Avenue — the site where APD officer Garrett Rolfe had killed 27-year-old Rayshard Brooks the previous evening. The Wendy\'s fire became a defining image of the George Floyd uprising in Atlanta. Both defendants were charged with felony arson; both pled to lesser charges and received probationary sentences.';
        $wendysBase = [
            'state' => 'Georgia',
            'race' => 'Black',
            'ideologies' => ['Anti-police violence', 'Black uprising'],
            'affiliation' => ['2020 George Floyd uprising / Atlanta'],
            'era' => '2020s',
            'in_custody' => false,
            'released' => true,
        ];
        $out[] = [
            'name' => 'Natalie Hanna White',
            'first_name' => 'Natalie',
            'middle_name' => 'Hanna',
            'last_name' => 'White',
            'description' => $wendysDesc.' White was identified via security footage and turned herself in.',
            'gender' => 'Female',
            'cases' => [[
                'institution_state' => 'Georgia',
                'charges' => 'Arson in the first degree (Wendy\'s on University Avenue, June 13, 2020).',
                'arrest_date' => '2020-06-17',
                'sentenced_date' => '2023-08-01',
                'convicted' => 'Pled to lesser charge.',
                'sentence' => '5 years probation + $500 fine.',
            ]],
        ] + $wendysBase;
        $out[] = [
            'name' => 'Chisom Kingston',
            'first_name' => 'Chisom',
            'last_name' => 'Kingston',
            'description' => $wendysDesc.' Kingston was a co-defendant in the same proceeding.',
            'gender' => 'Female',
            'cases' => [[
                'institution_state' => 'Georgia',
                'charges' => 'Arson in the first degree.',
                'arrest_date' => '2020-06-17',
                'sentenced_date' => '2023-08-01',
                'convicted' => 'Pled to lesser charge.',
                'sentence' => '5 years probation + $500 fine.',
            ]],
        ] + $wendysBase;

        return $out;
    }
}
