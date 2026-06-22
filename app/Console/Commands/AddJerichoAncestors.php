<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Deceased political prisoners from the Jericho Movement's "Ancestors" list that
 * were not already covered by the project's other add-commands. Most of the
 * Jericho ancestors (Geronimo ji-Jaga, Mutulu Shakur, Afeni Shakur, Sekou
 * Odinga, Cetewayo Tabor, Marilyn Buck, Herman Wallace, Russell Maroon Shoatz,
 * Albert Nuh Washington, Romaine Fitzgerald, the MOVE prisoners, etc.) are
 * already in the database; recorded here are the remaining real political
 * prisoners:
 *
 *  - Robert "Seth" Hayes (BPP/BLA), William "Lefty" Gilday (anti-imperialist),
 *    Robert Webb (BPP, killed 1971), Abdullah Majid / Anthony Laborde (BLA),
 *    Masai Ehehosi (BLA/RNA), and Oscar Washington (BPP/BLA).
 *
 * Jericho organizers who were not themselves prisoners (Iyaluua Ferguson, Frank
 * Velgara, Abduljabbar Caliph) are not included; Salih Ali Abdullah could not be
 * verified and was left out.
 *
 * Idempotent and variant-aware: skips a record if ANY of its name variants is
 * already present (so it will not duplicate anyone already on production).
 */
final class AddJerichoAncestors extends Command
{
    protected $signature = 'prisoners:add-jericho-ancestors';

    protected $description = 'Add missing deceased political prisoners from the Jericho Movement ancestors list';

    public function handle(): int
    {
        $added = 0;
        $skipped = 0;

        foreach ($this->records() as $r) {
            $terms = $r['match'] ?? [$r['name']];
            unset($r['match']);

            $exists = false;
            foreach ($terms as $t) {
                if (Prisoner::withUnderReview()->where('name', 'like', '%'.$t.'%')->exists()) {
                    $exists = true;
                    break;
                }
            }
            if ($exists) {
                $this->warn("Exists, skipping: {$r['name']}");
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($r) {
                $cases = $r['cases'] ?? [];
                unset($r['cases']);
                $prisoner = Prisoner::create($r);
                foreach ($cases as $c) {
                    if (! empty($c['institution_name'])) {
                        $inst = Institution::firstOrCreate(
                            ['name' => $c['institution_name']],
                            ['city' => $c['institution_city'] ?? null, 'state' => $c['institution_state'] ?? null],
                        );
                        $c['institution_id'] = $inst->id;
                    }
                    unset($c['institution_name'], $c['institution_city'], $c['institution_state']);
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
                'name' => 'Robert Seth Hayes',
                'first_name' => 'Robert',
                'last_name' => 'Hayes',
                'aka' => 'Seth Hayes; Shaba',
                'match' => ['Seth Hayes', 'Robert Seth Hayes'],
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'New York',
                'era' => '1970s',
                'death_date' => '2019-12-21',
                'ideologies' => ['Black liberation', 'Black Power'],
                'affiliation' => ['Black Panther Party', 'Black Liberation Army'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Robert "Seth" Hayes was a member of the Black Panther Party and the Black Liberation Army and one of the longest-held political prisoners in the United States. Born in Harlem in 1947, he was a Vietnam combat veteran (awarded the Purple Heart) before joining the movement. Arrested in 1973 in connection with Black Liberation Army activity in New York, he served some 45 years in New York prisons before finally being paroled in August 2018; he died on December 21, 2019. He was active in prisoners\' and political-prisoner support work throughout his decades inside.',
                'cases' => [[
                    'institution_state' => 'New York',
                    'charges' => 'Convicted in connection with Black Liberation Army activity in New York (1973)',
                    'convicted' => 'Yes — served about 45 years; paroled August 2018',
                ]],
            ],
            [
                'name' => 'William Gilday',
                'first_name' => 'William',
                'last_name' => 'Gilday',
                'aka' => 'Lefty',
                'match' => ['Gilday'],
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'Massachusetts',
                'era' => '1970s',
                'death_date' => '2011-09-10',
                'ideologies' => ['Anti-imperialism', 'Revolutionary socialism'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => false,
                'description' => 'William "Lefty" Gilday was a white anti-imperialist convicted in the September 23, 1970 robbery of a State Street Bank branch in the Brighton section of Boston — a holdup carried out by a small group (also including Susan Saxe and Katherine Ann Power) to raise funds for the antiwar and Black liberation movements — during which Boston police officer Walter Schroeder was shot and killed. Gilday was sentenced to life and died in prison at MCI-Shirley on September 10, 2011, after some 41 years inside.',
                'cases' => [[
                    'institution_name' => 'MCI-Shirley',
                    'institution_state' => 'Massachusetts',
                    'charges' => 'Murder of Boston police officer Walter Schroeder during the September 23, 1970 Brighton bank robbery',
                    'convicted' => 'Yes — sentenced to life; died in prison in 2011',
                    'sentence' => 'Life imprisonment',
                ]],
            ],
            [
                'name' => 'Robert Webb',
                'first_name' => 'Robert',
                'last_name' => 'Webb',
                'match' => ['Robert Webb'],
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'New York',
                'era' => '1970s',
                'death_date' => '1971-03-08',
                'ideologies' => ['Black liberation', 'Black Power'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => false,
                'description' => 'Robert Webb was a West Coast Black Panther who sided with the Eldridge Cleaver / New York faction during the bitter 1971 split in the Black Panther Party — a rupture inflamed by the FBI\'s COINTELPRO operations. On March 8, 1971 he was shot and killed on a Harlem street during the resulting internal conflict. He is honored among the movement\'s fallen.',
                'cases' => [[
                    'institution_state' => 'New York',
                    'charges' => 'Killed on a Harlem street on March 8, 1971, during the COINTELPRO-inflamed split in the Black Panther Party',
                ]],
            ],
            [
                'name' => 'Abdullah Majid',
                'first_name' => 'Abdullah',
                'last_name' => 'Majid',
                'aka' => 'Anthony Laborde',
                'match' => ['Abdullah Majid', 'Abdul Majid', 'Anthony Laborde'],
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'New York',
                'era' => '1980s',
                'ideologies' => ['Black liberation', 'New Afrikan'],
                'affiliation' => ['Black Panther Party', 'Black Liberation Army', 'Republic of New Afrika'],
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => false,
                'description' => 'Abdullah Majid, who before his imprisonment and conversion to Islam was known as Anthony Laborde, was a member of the Black Panther Party, the Black Liberation Army and the Republic of New Afrika. He was convicted in the April 1981 shooting of two New York City police officers in Queens — the killing of Officer John Scarangella and the wounding of Officer Richard Rainey — and sentenced to 25 years to life. He spent decades maintaining his innocence and died in custody at the Five Points Correctional Facility in 2016.',
                'cases' => [[
                    'institution_name' => 'Five Points Correctional Facility',
                    'institution_state' => 'New York',
                    'charges' => 'Murder of NYPD Officer John Scarangella and attempted murder of Officer Richard Rainey (Queens, April 1981)',
                    'convicted' => 'Yes — sentenced to 25 years to life; died in custody in 2016',
                    'sentence' => '25 years to life',
                ]],
            ],
            [
                'name' => 'Masai Ehehosi',
                'first_name' => 'Masai',
                'last_name' => 'Ehehosi',
                'match' => ['Ehehosi'],
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Virginia',
                'era' => '1970s',
                'ideologies' => ['Black liberation', 'New Afrikan'],
                'affiliation' => ['Black Panther Party', 'Black Liberation Army', 'Republic of New Afrika'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Masai Ehehosi was a member of the Black Panther Party, the Black Liberation Army, and a citizen of the Republic of New Afrika. Captured by police in 1973 as a member of the Black Liberation Army — a co-defendant of Safiya Bukhari — he served some 14 years in Virginia prisons. After his release he worked with the American Friends Service Committee and became a longtime member of the prison-abolition organization Critical Resistance.',
                'cases' => [[
                    'institution_state' => 'Virginia',
                    'charges' => 'Captured in 1973 as a member of the Black Liberation Army (co-defendant of Safiya Bukhari)',
                    'convicted' => 'Yes — served about 14 years in Virginia',
                ]],
            ],
            [
                'name' => 'Oscar Washington',
                'first_name' => 'Oscar',
                'last_name' => 'Washington',
                'match' => ['Oscar Washington'],
                'gender' => 'Male',
                'race' => 'Black',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Panther Party', 'Black Liberation Army'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Oscar Washington (1950-2016) was a member of the Black Panther Party and the Black Liberation Army, honored among the political prisoners memorialized on the Jericho Movement\'s ancestors list. Detailed records of his specific case were not available in the sources consulted.',
                'cases' => [[
                    'charges' => 'Imprisoned in connection with Black Panther Party / Black Liberation Army activity (case details not documented in available sources)',
                ]],
            ],
        ];
    }
}
