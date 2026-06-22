<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Twenty-first batch from reading The Black Panther — the "Iwakuni 5" from the
 * September 14, 1974 issue (Vol. 12 No. 8). These five were held back from
 * batch 19 because their names were garbled in the first OCR pass; the names
 * here were recovered by re-OCRing page 8 of the original scan at high
 * resolution (the fifth surname, Falatine, is transcribed as it reads in the
 * paper and may be spelled Palatine).
 *
 * The Iwakuni 5 were U.S. Marines at Marine Corps Air Station Iwakuni, Japan,
 * and members of Vietnam Veterans Against the War / Winter Soldiers Organization,
 * arrested July 12-13, 1974 and court-martialed (proceedings began August 28,
 * 1974) for circulating an off-base letter — addressed to Senator J.W. Fulbright
 * — documenting the Park Chung Hee dictatorship's repression in South Korea, in
 * alleged violation of a local Marine Corps order against distributing petitions
 * off base without command approval.
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers21Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-21';

    protected $description = 'Add the Iwakuni 5 GI resisters from the Sep 14, 1974 Black Panther issue, batch 21';

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
        $marine = function (string $name, string $first, string $last, string $rank): array {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'era' => '1970s',
                'ideologies' => ['Anti-war', 'GI resistance', 'Anti-imperialism'],
                'affiliation' => ['Vietnam Veterans Against the War', 'Winter Soldiers Organization'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "{$rank} {$name} was one of the \"Iwakuni 5\" — five U.S. Marines stationed at Marine Corps Air Station Iwakuni, Japan, and members of Vietnam Veterans Against the War / Winter Soldiers Organization. As The Black Panther reported (September 14, 1974), they were arrested July 12-13, 1974 and charged with violating a local Marine Corps order barring off-base distribution of a petition by off-duty personnel without command approval, after they circulated a letter — addressed to Senator J.W. Fulbright, chairman of the Senate Foreign Relations Committee — documenting the repression of democratic freedoms under the U.S.-backed Park Chung Hee dictatorship in South Korea. Special courts-martial began August 28, 1974; the five argued the charge was an unconstitutional restriction on their right to petition Congress.",
                'cases' => [[
                    'institution_name' => 'Marine Corps Air Station Iwakuni',
                    'institution_city' => 'Iwakuni',
                    'institution_state' => 'Japan',
                    'charges' => 'Violation of a local U.S. Marine Corps order prohibiting off-base distribution of a petition by off-duty personnel without command approval — for circulating a letter to Senator J.W. Fulbright documenting repression under the Park Chung Hee government in South Korea',
                    'convicted' => 'Special court-martial begun August 28, 1974 (the defendants challenged the charge as an unconstitutional bar on the right to petition Congress)',
                ]],
            ];
        };

        return [
            $marine('Gerald W. MacCauley', 'Gerald', 'MacCauley', 'Lance Corporal'),
            $marine('Robert A. Falatine', 'Robert', 'Falatine', 'Lance Corporal'),
            $marine('Frank Huff', 'Frank', 'Huff', 'Lance Corporal'),
            $marine('Hugh G. Dalton', 'Hugh', 'Dalton', 'Private'),
            $marine('Patrick McDonald', 'Patrick', 'McDonald', 'Private First Class'),
        ];
    }
}
