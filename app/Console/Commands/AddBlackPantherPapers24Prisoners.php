<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Twenty-fourth batch from reading The Black Panther — the November 16, 1974
 * issue (Vol. 12 No. 17). Much of the issue's case coverage concerns people
 * already recorded — Rubin "Hurricane" Carter and John Artis (Carter's new-trial
 * petition), Inez García's sentencing (batch 20), and the Chicano Leavenworth
 * Brothers Lopez and Miramon (batch 18) — and the Kent State guardsmen, who were
 * the defendants/acquitted, not prisoners. Its one new political prisoner is:
 *
 *  - Joan Little — the landmark self-defense case: a Black woman who killed a
 *    jailer she said sexually assaulted her, later acquitted.
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers24Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-24';

    protected $description = 'Add Black Panther newspaper prisoners from the Nov 16, 1974 issue, batch 24';

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
                'name' => 'Joan Little',
                'first_name' => 'Joan',
                'last_name' => 'Little',
                'aka' => 'Joanne Little',
                'gender' => 'Female',
                'race' => 'Black',
                'state' => 'North Carolina',
                'era' => '1970s',
                'ideologies' => ['Women\'s liberation', 'Self-defense', 'Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Joan Little (also written Joanne Little) was a young Black woman who became a landmark figure in the women\'s, Black-liberation, and anti-death-penalty movements. As The Black Panther reported (November 16, 1974), she had been held in the Beaufort County Jail in Washington, North Carolina — pending appeal of a breaking-and-entering conviction — when, on August 27, 1974, the jail\'s night guard Clarence Alligood was found dead in her cell, killed with an ice pick and naked from the waist down. Little, the only woman and only Black woman in a jail with all-White guards, had fled and turned herself in to authorities in Raleigh eight days later, explaining that Alligood had forced repeated sexual advances on her and that she killed him defending herself. Charged with first-degree murder and facing the death penalty, she was acquitted in August 1975 — widely recognized as the first woman in the United States acquitted of murder on the grounds of resisting sexual assault.',
                'cases' => [[
                    'institution_name' => 'Beaufort County Jail',
                    'institution_city' => 'Washington',
                    'institution_state' => 'North Carolina',
                    'charges' => 'First-degree murder of jailer Clarence Alligood, whom she said sexually assaulted her in her cell (August 27, 1974); she had originally been jailed on a breaking-and-entering conviction',
                    'convicted' => 'No — acquitted in August 1975 on grounds of self-defense against sexual assault',
                ]],
            ],
        ];
    }
}
