<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Sixteenth batch from reading The Black Panther — drawn from the 1974 issues:
 *
 *  - Thomas Wansley — a Black man arrested at 17 in Lynchburg, Virginia in 1962
 *    on a rape charge, a national cause célèbre against the racist use of the
 *    rape charge and the death penalty; conviction overturned, released 1973,
 *    pardon denied.
 *  - Woodrow Wilson Gillis — convicted of assaulting a white couple in self-
 *    defense in Kinston, N.C. (1965), sentenced to a chain gang, who escaped to
 *    Philadelphia and fought extradition in 1974.
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers16Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-16';

    protected $description = 'Add Black Panther newspaper prisoners from the 1974 issues, batch 16';

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
                'name' => 'Thomas Wansley',
                'first_name' => 'Thomas',
                'last_name' => 'Wansley',
                'aka' => 'Thomas Carlton Wansley',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Virginia',
                'era' => '1960s',
                'ideologies' => [],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Thomas Wansley was a Black teenager arrested in 1962 in Lynchburg, Virginia at the age of 17 on a rape charge — a prosecution that became a national cause célèbre against the racially discriminatory use of the rape charge and the death penalty. Originally sentenced to death as a teenager, he was later resentenced to a life term. His conviction was overturned and he was released on bond in January 1973, but Virginia Governor Linwood Holton denied him a pardon — the Lynchburg prosecutor, Royston Jester, having called Wansley "a discredit to his race" — despite petitions bearing 15,000 signatures and the support of some 20 national human-rights organizations. Documented in The Black Panther (January 19, 1974).',
                'cases' => [[
                    'institution_state' => 'Virginia',
                    'charges' => 'Rape (Lynchburg, Virginia, 1962) — widely condemned as a racist frame-up and a discriminatory use of the capital rape charge against a Black teenager',
                    'convicted' => 'Yes — sentenced to death as a youth, later a life term; conviction overturned and released on bond January 1973; pardon denied',
                    'sentence' => 'Death (as a youth), later commuted to life imprisonment',
                ]],
            ],
            [
                'name' => 'Woodrow Wilson Gillis',
                'first_name' => 'Woodrow',
                'last_name' => 'Gillis',
                'aka' => 'Woodrow Wilson',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'North Carolina',
                'era' => '1960s',
                'ideologies' => [],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Woodrow Wilson Gillis was a Black man who, after being attacked by a white man and woman in Kinston, North Carolina in 1965 — the white man having pulled a knife on him — was himself convicted of assaulting the white couple and sentenced to 18 to 20 years on a North Carolina chain gang. He escaped and settled in the Germantown section of Philadelphia, living for years as a respected community member under the name Woodrow Wilson. In 1974, at age 51, he faced extradition back to North Carolina to serve at least 16 more years; his attorney David Kairys and his neighbors petitioned Pennsylvania Governor Milton Shapp not to send him back. Documented in The Black Panther (July 6, 1974).',
                'cases' => [[
                    'institution_state' => 'North Carolina',
                    'charges' => 'Assault on a white couple (Kinston, North Carolina, 1965) — in self-defense, after the white man pulled a knife on him',
                    'convicted' => 'Yes — convicted of assault; escaped a North Carolina chain gang and fought extradition from Pennsylvania in 1974',
                    'sentence' => '18 to 20 years on a chain gang',
                ]],
            ],
        ];
    }
}
