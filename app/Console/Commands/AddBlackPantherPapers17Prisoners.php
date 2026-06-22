<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Seventeenth batch from reading The Black Panther — drawn from the 1974 issues:
 *
 *  - Marvin Fentis — a Texan already serving two life sentences for defending
 *    himself against Garland police, convicted in 1974 of a Houston officer's
 *    murder on circumstantial evidence (38 more years).
 *  - Welton "Butch" Armstead — a 17-year-old Seattle Black Panther killed by
 *    police on October 5, 1968 (date verified via HistoryLink; the paper gave
 *    October 15). A martyr, recorded here from the paper's memorial coverage.
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers17Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-17';

    protected $description = 'Add Black Panther newspaper prisoners from the 1974 issues, batch 17';

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
                'name' => 'Marvin Fentis',
                'first_name' => 'Marvin',
                'last_name' => 'Fentis',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Texas',
                'era' => '1970s',
                'ideologies' => [],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Marvin Fentis was a Black Texan who, The Black Panther reported, was already serving two life sentences for defending himself against two Garland, Texas police officers when, in August 1974, he was brought to Houston, tried, and convicted of the murder of a Houston police officer — and sentenced to 38 more years on what his defense attorney, Grey Washington, called purely circumstantial evidence, after police "concentrated on finding three Black men" to fit a fabricated description. Documented in The Black Panther (August 31, 1974).',
                'cases' => [[
                    'institution_state' => 'Texas',
                    'charges' => 'Murder of a Houston police officer (1974, convicted on circumstantial evidence); separately, the shooting of two Garland, Texas police officers, which he said was self-defense',
                    'convicted' => 'Yes — already serving two life sentences (the Garland case) when sentenced to 38 additional years for the Houston case',
                    'sentence' => 'Two life sentences plus 38 years',
                ]],
            ],
            [
                'name' => 'Welton Armstead',
                'first_name' => 'Welton',
                'last_name' => 'Armstead',
                'aka' => 'Butch',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Washington',
                'era' => '1960s',
                'death_date' => '1968-10-05',
                'ideologies' => ['Black Power', 'Black liberation'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => false,
                'description' => 'Welton "Butch" Armstead was a 17-year-old member of the Seattle chapter of the Black Panther Party and one of the earliest Panthers to be killed by police. On October 5, 1968, after Seattle police stopped a car reported stolen and an officer chased a fleeing occupant near Armstead\'s home, Armstead came out with a rifle; officer Erling J. Buttedahl fired and struck him in the heart, killing him. The Black Panther memorialized him among the party\'s fallen. (The paper gave the date as October 15, 1968; contemporary records date the killing to October 5.)',
                'cases' => [[
                    'institution_state' => 'Washington',
                    'charges' => 'Shot and killed by a Seattle police officer (Erling J. Buttedahl) on October 5, 1968',
                ]],
            ],
        ];
    }
}
