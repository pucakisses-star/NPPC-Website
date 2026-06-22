<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Nineteenth batch from reading The Black Panther — the September 14, 1974 issue
 * (Vol. 12 No. 8), continuing on from batch 18.
 *
 * That issue's Leavenworth coverage is trial reporting on the six Leavenworth
 * Brothers already added in batch 18; its other clearly-named new political
 * prisoner is Joyce Guerrero, the Potawatomi woman jailed over the 1972 Trail of
 * Broken Treaties occupation of the BIA. (The issue's "Iwakuni 5" GI case is held
 * back — the five Marines' names are too garbled in the scan to record reliably.)
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers19Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-19';

    protected $description = 'Add Black Panther newspaper prisoners from the Sep 14, 1974 issue, batch 19';

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
                'name' => 'Joyce Guerrero',
                'first_name' => 'Joyce',
                'last_name' => 'Guerrero',
                'gender' => 'Female',
                'race' => 'Native American',
                'state' => 'Kansas',
                'era' => '1970s',
                'ideologies' => ['Indigenous sovereignty', 'Native American rights'],
                'affiliation' => ['American Indian Movement', 'Trail of Broken Treaties'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Joyce Guerrero was a 27-year-old Potawatomi woman and mother of two who, The Black Panther reported (September 14, 1974), was the only person sentenced over the confiscation of Bureau of Indian Affairs (BIA) documents during the November 1972 "Trail of Broken Treaties" occupation of BIA headquarters in Washington, D.C. Her role in the demonstration had been caring for the 75 to 100 children left without shelter when promised accommodations fell through. After she returned home to Topeka, Kansas, FBI agents charged her with receiving, concealing and retaining stolen BIA documents — though, supporters noted, no BIA property was found on her person or in her possession — and an all-White jury convicted her on May 22, 1973. She was sentenced to nine months in jail and three years\' probation, and later given additional jail time for "willfully" failing to appear at an earlier pretrial hearing.',
                'cases' => [[
                    'charges' => 'Receiving, concealing and retaining U.S. government (Bureau of Indian Affairs) documents taken during the November 1972 Trail of Broken Treaties occupation of the BIA building in Washington, D.C.; supporters noted no BIA property was found in her possession',
                    'convicted' => 'Yes — convicted May 22, 1973 by an all-White jury',
                    'sentence' => 'Nine months in jail and three years probation, plus additional jail time for failing to appear at a pretrial hearing',
                ]],
            ],
        ];
    }
}
