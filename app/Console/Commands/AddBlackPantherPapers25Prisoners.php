<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Twenty-fifth batch from reading The Black Panther — the December 7, 1974 issue
 * (Vol. 12 No. 20). Most of the issue's case coverage is follow-up on people
 * already recorded (the acquittals of the Chicano Leavenworth Brothers Jesse
 * Lopez and Armando Miramon, and the conviction of the four Black Leavenworth
 * Brothers — all batch 18). Its one new political prisoner is:
 *
 *  - Morton Sobell — co-defendant of the Rosenbergs, interviewed in the issue
 *    about his 18 years in federal prison.
 *
 * (A referenced volleyball-court prison shooting that killed inmates James Durr
 * and Robert Gilkey is left out — the scan does not clearly establish the prison
 * or the incident.) Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers25Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-25';

    protected $description = 'Add Black Panther newspaper prisoners from the Dec 7, 1974 issue, batch 25';

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
                'name' => 'Morton Sobell',
                'first_name' => 'Morton',
                'last_name' => 'Sobell',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'New York',
                'era' => '1950s',
                'death_date' => '2018-12-26',
                'ideologies' => ['Communism', 'Anti-imperialism'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Morton Sobell was the co-defendant of Ethel and Julius Rosenberg in the 1951 Cold War "atom bomb" espionage trial. Convicted of conspiracy to commit espionage and sentenced to 30 years, he served 18 years and 5 months in federal prison — the first five on Alcatraz ("the Rock") and the rest largely at the Atlanta Federal Penitentiary — before his release in 1969. As The Black Panther reported (December 7, 1974) in an extended interview marking his memoir On Doing Time, Sobell described being pressured to "cooperate" by falsely accusing others of espionage and recounted the isolation of Alcatraz; he was widely regarded on the left as a political prisoner of America\'s Cold War repression. He died in 2018 at the age of 101.',
                'cases' => [[
                    'institution_name' => 'Alcatraz Federal Penitentiary',
                    'institution_city' => 'San Francisco',
                    'institution_state' => 'California',
                    'charges' => 'Conspiracy to commit espionage (tried with Ethel and Julius Rosenberg, 1951)',
                    'convicted' => 'Yes — sentenced to 30 years; served 18 years and 5 months (first five on Alcatraz, then the Atlanta Federal Penitentiary), released in 1969',
                    'sentence' => '30 years',
                ]],
            ],
        ];
    }
}
