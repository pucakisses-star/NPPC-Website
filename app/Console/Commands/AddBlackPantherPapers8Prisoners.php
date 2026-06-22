<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Eighth batch from reading The Black Panther — drawn from the November-December
 * 1971 issues:
 *
 *  - Norma Gist — an Idabel, Oklahoma mother sentenced to ten years and re-taken
 *    to McAlester prison after she confronted a white principal over the beating
 *    of her son.
 *  - Frank Nubin — an Oakland man repeatedly returned to San Quentin through
 *    California's Adult Authority / indeterminate-sentence parole machinery.
 *  - Mark Edward Allen — a young Black man who died in Monrovia, California police
 *    custody (Nov. 16, 1971) in a disputed "suicide."
 *  - The Orleans Parish Prison Annex protest (Oct. 27, 1971): inmates Cleveland
 *    Shaw, Raymond Prieur and Shelly Batiste, charged with aggravated escape after
 *    briefly detaining an official to publicize prison conditions.
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers8Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-8';

    protected $description = 'Add Black Panther newspaper prisoners from the Nov-Dec 1971 issues, batch 8';

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
        // Orleans Parish Prison Annex protest defendants (shared case).
        $annex = function (string $name, string $first, string $last): array {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Louisiana',
                'era' => '1970s',
                'ideologies' => ['Prison movement'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "{$name} was an inmate of the Orleans Parish Prison Annex in New Orleans who, on October 27, 1971, joined two fellow prisoners in briefly detaining a prison official in an attempt to publicize their grievances about conditions to the news media. They released the official unharmed, but the three were charged with aggravated escape. Documented in The Black Panther (December 4, 1971).",
                'cases' => [[
                    'institution_name' => 'Orleans Parish Prison',
                    'institution_city' => 'New Orleans',
                    'institution_state' => 'Louisiana',
                    'charges' => 'Aggravated escape (the Oct. 27, 1971 inmate protest at the Orleans Parish Prison Annex)',
                    'arrest_date' => '1971-10-27',
                ]],
            ];
        };

        return [
            [
                'name' => 'Norma Gist',
                'first_name' => 'Norma',
                'last_name' => 'Gist',
                'gender' => 'Female',
                'race' => 'Black',
                'state' => 'Oklahoma',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Norma Gist was a thirty-year-old Black mother of two in Idabel, Oklahoma, whose prosecution grew out of an incident in which she criticized a white school principal, Wilfred Conant, for the brutal beating of her ten-year-old son, Sol. On April 12, 1971, after what The Black Panther called a "quickie, non-peer jury trial," she was convicted and sentenced to ten years in the state prison, then released two days later on a $10,000 appeal bond. On November 15, 1971 Idabel and Oklahoma state police seized her from her home and, on November 17, took her to McAlester prison; the state had also cut off her welfare in an effort to force her into submission. Documented in The Black Panther (November 29, 1971).',
                'cases' => [[
                    'institution_name' => 'Oklahoma State Penitentiary',
                    'institution_city' => 'McAlester',
                    'institution_state' => 'Oklahoma',
                    'charges' => 'Convicted (April 12, 1971) in Idabel, Oklahoma following her confrontation with a white school principal over the beating of her son',
                    'convicted' => 'Yes — convicted April 12, 1971; freed on appeal bond, then re-jailed November 1971',
                    'sentence' => 'Ten years',
                ]],
            ],
            [
                'name' => 'Frank Nubin',
                'first_name' => 'Frank',
                'last_name' => 'Nubin',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Prison movement'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Frank Nubin was a 46-year-old Oakland, California steelworker — married, studying structural engineering — who had spent some fifteen years of his life in California prisons and became, to The Black Panther, an emblem of the abuses of the state\'s indeterminate-sentence and Adult Authority parole system. Arrested at Christmas 1968 with less than a month left on parole (on his former common-law wife\'s claim that he had attacked her), he was returned to San Quentin on a parole violation in February 1969. In November 1971, after a deputy attorney general called him "a potentially dangerous man," a court ordered him back to prison for ten more months. Documented in The Black Panther (November 29, 1971).',
                'cases' => [[
                    'institution_name' => 'San Quentin State Prison',
                    'institution_city' => 'San Quentin',
                    'institution_state' => 'California',
                    'charges' => 'Parole violation (returned to prison under California\'s Adult Authority / indeterminate-sentence system)',
                ]],
            ],
            [
                'name' => 'Mark Edward Allen',
                'first_name' => 'Mark',
                'last_name' => 'Allen',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'death_date' => '1971-11-16',
                'ideologies' => [],
                'affiliation' => [],
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => false,
                'description' => 'Mark Edward Allen was a young Black man who died in police custody in Monrovia, California. Arrested on November 16, 1971 by Monrovia police on suspicion of shoplifting a leather coat, he was booked at 5:11 p.m. and was soon found dead — police claimed he had strangled himself to death with his pants through the jail bars. A Black police cadet, Keith Lowden, said Allen had been brought in shirtless, in a state of "complete emotional excitation," with scratches across his body, and he was pronounced dead on arrival at Arcadia Methodist Hospital. The Black Panther (December 11, 1971) reported the death as a police killing. NOTE: this is a death-in-custody case rather than a conventional prosecution.',
                'cases' => [[
                    'institution_name' => 'Monrovia Police Station',
                    'institution_city' => 'Monrovia',
                    'institution_state' => 'California',
                    'charges' => 'Suspicion of shoplifting (Allen died in custody the same day, November 16, 1971)',
                    'arrest_date' => '1971-11-16',
                    'death_in_custody_date' => '1971-11-16',
                ]],
            ],

            // ---- Orleans Parish Prison Annex protest (Oct. 27, 1971) ----
            $annex('Cleveland Shaw', 'Cleveland', 'Shaw'),
            $annex('Raymond Prieur', 'Raymond', 'Prieur'),
            $annex('Shelly Batiste', 'Shelly', 'Batiste'),
        ];
    }
}
