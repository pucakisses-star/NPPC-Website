<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Third batch from reading The Black Panther (1967-1970) — two prisoners the
 * paper covered who could NOT be independently corroborated. They are added at
 * the user's request, explicitly sourced to The Black Panther alone, with the
 * lack of outside verification flagged in each bio:
 *
 *  - Michael Harris (Des Moines, Iowa BPP) — parole revoked and charged with
 *    the Jewett Lumber Co. arson (May 1969 coverage). Standard accounts of that
 *    arson name other defendants, so this rests on the Party's account.
 *  - Mickey White (Michael "Mickey" White) — charged with attempted murder
 *    after a March 1969 car stop; co-defendants Nathaniel Junior and Merrill
 *    Harvey; subject of a "Mickey White Defense Fund." Too common a name to
 *    confirm externally; the chart's bail/date/judge figures appear to be OCR
 *    column-bleed from adjacent entries and are not asserted.
 *
 * Unknown/garbled fields (Mickey White's city/state and outcome; exact dates)
 * are omitted rather than guessed. Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers3Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-3';

    protected $description = 'Add two newspaper-only (uncorroborated) Black Panther-era prisoners';

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
                'name' => 'Michael Harris',
                'first_name' => 'Michael',
                'last_name' => 'Harris',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Iowa',
                'era' => '1960s',
                'ideologies' => ['Black Power', 'Black nationalism'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Michael Harris was a member of the Des Moines, Iowa chapter of the Black Panther Party. According to The Black Panther newspaper (May 19, 1969), while he was on parole the authorities moved to revoke it — citing parole violations such as traveling out of Polk County — and charged him with the arson of the Jewett Lumber Co., a charge the Party said rested only on the proximity of another member\'s car. The paper reported he sat in the county jail awaiting transfer to the state reformatory and ran a "Free Michael Harris" campaign that framed the parole revocation as political repression, likening it to Eldridge Cleaver\'s. NOTE: this record is documented only in The Black Panther; standard accounts of the Jewett Lumber arson name other defendants (Charles Knox and the Cheatoms), so the details here are not independently corroborated.',
                'cases' => [[
                    'institution_name' => 'Polk County Jail',
                    'institution_city' => 'Des Moines',
                    'institution_state' => 'Iowa',
                    'charges' => 'Arson of the Jewett Lumber Co. and parole violation (per The Black Panther, May 1969); the Party said the only evidence was the proximity of another member\'s car',
                    'convicted' => 'Parole revoked; held in the county jail awaiting transfer to the Iowa state reformatory (per The Black Panther). The arson charge\'s disposition is not documented.',
                ]],
            ],
            [
                'name' => 'Mickey White',
                'first_name' => 'Michael',
                'last_name' => 'White',
                'aka' => 'Michael White',
                'gender' => 'Male',
                'race' => 'Black',
                'era' => '1960s',
                'ideologies' => ['Black Power', 'Black nationalism'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Michael "Mickey" White was a member of the Black Panther Party jailed in 1969. According to The Black Panther newspaper, he was charged with attempted murder after a March 1969 incident in which a carload of Party members was stopped and searched by police, and he was held alongside co-defendants Nathaniel Junior and Merrill Harvey. The paper ran a "Mickey White Defense Fund" and, in its July 19, 1969 issue, a tribute that called him "beautiful" and noted "he\'s being held now." NOTE: this record is documented only in The Black Panther; the name is too common to confirm in independent sources, and the bail, trial-date and judge figures in the paper\'s prisoner chart appear to be OCR column-bleed from adjacent entries (Fred Hampton\'s), so they are not asserted here. His city/state and the outcome of the case could not be determined.',
                'cases' => [[
                    'charges' => 'Attempted murder (per The Black Panther); arose from a March 1969 incident in which a carload of Party members was stopped and searched by police',
                    'convicted' => 'Not documented',
                ]],
            ],
        ];
    }
}
