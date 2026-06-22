<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Thirteenth batch from reading The Black Panther — drawn from the 1973 issues:
 *
 *  - Ron Carter — an Atlanta Black Panther arrested twice in one week in June
 *    1973 as police moved to break up the chapter.
 *  - The "USS Sumter 3" — Black servicemen (Alexander Jenkins, Roy Barnwell and
 *    James Blackburn) charged with mutiny for playing the Last Poets over the
 *    ship's intercom off Vietnam in September 1972.
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers13Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-13';

    protected $description = 'Add Black Panther newspaper prisoners from the 1973 issues, batch 13';

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
        // The "USS Sumter 3" — shared mutiny case (off Vietnam, September 1972).
        $sumter = function (string $name, string $first, string $last, string $outcome): array {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'race' => 'Black',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "{$name} was one of three Black servicemen originally charged with mutiny over a racial rebellion aboard the U.S.S. Sumter while it was off the coast of Vietnam in September 1972 — a case that, by The Black Panther's account, arose from acts such as playing the music of the Last Poets over the ship's intercom. After a determined struggle, the Marine command backed down from the mutiny charges. Documented in The Black Panther (July and September 1973).",
                'cases' => [[
                    'charges' => 'Mutiny (a racial rebellion aboard the U.S.S. Sumter off Vietnam, September 1972)',
                    'convicted' => $outcome,
                ]],
            ];
        };

        return [
            [
                'name' => 'Ron Carter',
                'first_name' => 'Ron',
                'last_name' => 'Carter',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Georgia',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Ron Carter was a member of the Atlanta chapter of the Black Panther Party whom police arrested twice in one week in June 1973, in what The Black Panther described as a scheme to destroy the chapter. On June 23 he was seized at his home by heavily armed police and held in the Fulton County Jail to await extradition on charges stemming from a New Jersey arrest for alleged arms violations; on June 29 he was arrested again on a downtown street while distributing the party newspaper. Documented in The Black Panther (July 28, 1973).',
                'cases' => [[
                    'institution_name' => 'Fulton County Jail',
                    'institution_city' => 'Atlanta',
                    'institution_state' => 'Georgia',
                    'charges' => 'Held for extradition on a New Jersey arms-violation charge, and separately arrested while distributing the Black Panther newspaper (Atlanta, June 1973)',
                    'arrest_date' => '1973-06-23',
                ]],
            ],

            // ---- The USS Sumter 3 ----
            $sumter('Alexander Jenkins', 'Alexander', 'Jenkins',
                'Charged with mutiny; accepted a lesser charge and received a three-month sentence'),
            $sumter('Roy Barnwell', 'Roy', 'Barnwell',
                'Charged with mutiny; the charge was dropped and he was given a less-than-honorable discharge'),
            $sumter('James Blackburn', 'James', 'Blackburn',
                'Charged with mutiny; the charge was dropped and he was given a less-than-honorable discharge'),
        ];
    }
}
