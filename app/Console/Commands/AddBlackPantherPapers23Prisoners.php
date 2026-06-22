<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Twenty-third batch from reading The Black Panther — the November 2, 1974 issue
 * (Vol. 12 No. 15). The issue's lead case coverage is sentencing follow-ups on
 * people already recorded (the Leavenworth Brothers, batch 18; Inez García,
 * batch 20). Its two new figures both come out of the October 1974 shooting at
 * the Black Panther Party's Community Learning Center in Oakland:
 *
 *  - Bruce "Deacon" Washington — a Party member killed protecting children at a
 *    teen dance there.
 *  - Walter Rozier — a Party member, the only person arrested afterward.
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers23Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-23';

    protected $description = 'Add Black Panther newspaper prisoners from the Nov 2, 1974 issue, batch 23';

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
        return [
            [
                'name' => 'Bruce Washington',
                'first_name' => 'Bruce',
                'last_name' => 'Washington',
                'aka' => 'Deacon',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'death_date' => '1974-10-26',
                'ideologies' => ['Black Power', 'Black liberation'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => false,
                'description' => 'Bruce "Deacon" Washington was a 26-year-old member of the Black Panther Party in Oakland who was shot in the back and killed on October 26, 1974 while shielding children during a shooting at a teen dance held at the Party\'s Community Learning Center (Son of Man Temple, 6118 East 14th Street). Three people, including a 12-year-old, were also wounded; Washington died after more than eight hours of emergency surgery at Highland Hospital. As The Black Panther reported (November 2, 1974), Party spokesperson Elaine Brown said the Party believed the attack had been provoked by police to disrupt the Center\'s community work.',
                'cases' => [[
                    'institution_state' => 'California',
                    'charges' => 'Fatally shot in the back on October 26, 1974 while protecting children at a teen dance at the Black Panther Party\'s Community Learning Center in Oakland',
                ]],
            ],
            [
                'name' => 'Walter Rozier',
                'first_name' => 'Walter',
                'last_name' => 'Rozier',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Black Power', 'Black liberation'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Walter Rozier was a member of the Black Panther Party in Oakland who, The Black Panther reported (November 2, 1974), was the only person arrested following the October 1974 shooting at the Party\'s Community Learning Center — the attack in which Panther Bruce "Deacon" Washington was killed shielding children. He was charged with allegedly withholding evidence; the Party, through spokesperson Elaine Brown, maintained that the incident had been provoked by police and that the sole arrest of a Panther member was further evidence of an effort to destroy the Center\'s work.',
                'cases' => [[
                    'institution_state' => 'California',
                    'charges' => 'Allegedly withholding evidence — the only person arrested after the October 1974 shooting at the Black Panther Party\'s Community Learning Center in Oakland, in which Panther Bruce "Deacon" Washington was killed',
                ]],
            ],
        ];
    }
}
