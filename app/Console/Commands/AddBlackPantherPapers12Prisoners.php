<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Twelfth batch from reading The Black Panther — drawn from the October-December
 * 1972 issues:
 *
 *  - The San Francisco Black Caucus: co-chairman Larry Pinkney and Kirk Taylor,
 *    arrested in 1972 in a Berkeley police car-search and charged on weapons/
 *    stolen-goods counts that collapsed.
 *  - Larry Justice and Earl Gibson, charged with the murder of San Quentin guard
 *    Leo Davis and tried at the Marin County Courthouse on what The Black Panther
 *    called a no-evidence case.
 *
 * (Wesley Robert Wells, profiled in the same period, is already in the database.)
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers12Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-12';

    protected $description = 'Add Black Panther newspaper prisoners from the Oct-Dec 1972 issues, batch 12';

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
        // Leo Davis (San Quentin guard) murder case — shared scaffold.
        $leoDavis = function (string $name, string $first, string $last): array {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "{$name} was one of two Black men charged with the murder of San Quentin prison guard Leo Davis and put on trial at the Marin County Courthouse in San Rafael, California, in late 1972. The Black Panther reported that the state's case against him and his co-defendant rested on no real evidence linking them to the killing. Documented in The Black Panther (December 30, 1972).",
                'cases' => [[
                    'institution_state' => 'California',
                    'charges' => 'Murder of San Quentin guard Leo Davis (tried at the Marin County Courthouse; the state\'s case rested on no real evidence)',
                ]],
            ];
        };

        return [
            [
                'name' => 'Larry Pinkney',
                'first_name' => 'Larry',
                'last_name' => 'Pinkney',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['San Francisco Black Caucus'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Larry Pinkney was the co-chairman of the San Francisco Black Caucus, a community organization that fought the firing of Black workers and pressed for Black hiring in city government and at San Francisco General Hospital. In 1972, as he and Kirk Taylor drove back to San Francisco from Richmond, Berkeley police stopped and searched their car without a warrant; Pinkney was charged with carrying a concealed weapon and held overnight before the charge was dropped the next day — one of a series of harassment arrests the Caucus faced. Documented in The Black Panther (October 21, 1972).',
                'cases' => [[
                    'institution_state' => 'California',
                    'charges' => 'Carrying a concealed weapon (a 1972 warrantless Berkeley car-search arrest of the San Francisco Black Caucus co-chairman)',
                    'convicted' => 'No — the charge was dropped the next day',
                ]],
            ],
            [
                'name' => 'Kirk Taylor',
                'first_name' => 'Kirk',
                'last_name' => 'Taylor',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['San Francisco Black Caucus'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Kirk Taylor was a member of the San Francisco Black Caucus, arrested with co-chairman Larry Pinkney in 1972 when Berkeley police searched their car without a warrant. He was charged with carrying a concealed weapon and receiving stolen goods and held on $8,500 bond, jailed two days until the bond was reduced at his arraignment — at which, The Black Panther reported, it was made clear there was no case against him. Documented in The Black Panther (October 21, 1972).',
                'cases' => [[
                    'institution_state' => 'California',
                    'charges' => 'Carrying a concealed weapon and receiving stolen goods (San Francisco Black Caucus; held on $8,500 bond, then released for lack of a case)',
                    'convicted' => 'No — no case; released after his bond was reduced',
                ]],
            ],

            // ---- Leo Davis (San Quentin guard) murder case ----
            $leoDavis('Larry Justice', 'Larry', 'Justice'),
            $leoDavis('Earl Gibson', 'Earl', 'Gibson'),
        ];
    }
}
