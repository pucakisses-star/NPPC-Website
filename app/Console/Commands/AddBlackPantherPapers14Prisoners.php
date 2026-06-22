<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Fourteenth batch from reading The Black Panther — drawn from the 1973 issues
 * (a stretch otherwise heavy on Watergate, international news, and cases already
 * in the database):
 *
 *  - Roger Champen — one of the Attica Brothers, indicted on three counts of
 *    murder after the September 1971 Attica uprising; assistant coordinator of
 *    the Attica Legal Defense Committee.
 *  - James Aaron — a Houston Black Panther convicted on a frame-up assault charge
 *    and held at the Harris County Rehabilitation Center.
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers14Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-14';

    protected $description = 'Add Black Panther newspaper prisoners from the 1973 issues, batch 14';

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
                'name' => 'Roger Champen',
                'first_name' => 'Roger',
                'last_name' => 'Champen',
                'aka' => 'Champ',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'New York',
                'era' => '1970s',
                'ideologies' => ['Prison movement', 'Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Roger Champen was one of the Attica Brothers — prisoners indicted after the September 1971 uprising at New York\'s Attica prison and its bloody suppression, in which the state killed dozens of inmates and hostages. A leader during the rebellion, he became an assistant coordinator of the Attica Legal Defense Committee and himself faced a three-count murder indictment, among the scores of charges (ranging from murder to "possession of prison keys") that Governor Nelson Rockefeller\'s special grand jury handed down against the prisoners — while the officials responsible for the retaking of the prison went uncharged. The Attica prosecutions of the inmates were eventually largely abandoned, and in 1976 Governor Hugh Carey closed the books on the cases. Documented in The Black Panther (December 8, 1973).',
                'cases' => [[
                    'institution_name' => 'Attica Correctional Facility',
                    'institution_city' => 'Attica',
                    'institution_state' => 'New York',
                    'charges' => 'Three-count murder indictment (as one of the Attica Brothers, following the September 1971 Attica uprising)',
                    'convicted' => 'Indicted on three counts of murder; the Attica inmate prosecutions were later largely abandoned (Gov. Carey closed the cases in 1976)',
                ]],
            ],
            [
                'name' => 'James Aaron',
                'first_name' => 'James',
                'last_name' => 'Aaron',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Texas',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'James Aaron was a member of the Black Panther Party in Houston, Texas. In July 1970 he was accused of assault on a police officer with a deadly weapon (an alleged shotgun) and convicted by an all-white jury that deliberated only five minutes — a prosecution The Black Panther described as a political frame-up aimed at halting his organizing. Freed on appeal bond for about a year, he began serving a two-year sentence after his appeal was denied, and the court converted an earlier ten-year probation into an actual ten-year prison term. He was moved from the Harris County Jail to the Harris County Rehabilitation Center after he refused, reportedly at gunpoint, to stop organizing among the inmates; his case was tied to that of fellow Texas organizer Fred Bell. Documented in The Black Panther (June 9, 1973).',
                'cases' => [[
                    'institution_name' => 'Harris County Rehabilitation Center',
                    'institution_city' => 'Houston',
                    'institution_state' => 'Texas',
                    'charges' => 'Assault on a police officer with a deadly weapon (an alleged shotgun; Houston, July 1970 — described as a political frame-up)',
                    'convicted' => 'Yes — convicted by an all-white jury; a two-year sentence plus a ten-year term from a revoked probation',
                ]],
            ],
        ];
    }
}
