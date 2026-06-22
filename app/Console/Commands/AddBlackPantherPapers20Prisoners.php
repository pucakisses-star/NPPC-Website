<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Twentieth batch from reading The Black Panther — the October 12, 1974 issue
 * (Vol. 12 No. 12). (The Sep 21 / Sep 28 / Oct 5 issues are blank in the scan
 * set, so this is the next readable issue after batch 19.)
 *
 *  - Inez García — the landmark self-defense case: a Latina woman convicted of
 *    killing one of the men who raped her (later acquitted on retrial).
 *  - Vernon Benton and Claude Frost — Houston Black Panther Party members tried
 *    and convicted with James Aaron (already recorded) after a 1971 raid.
 *  - Bobby Hardwick — a Black inmate activist at Georgia State Prison who sued
 *    for the right to receive The Black Panther in prison.
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers20Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-20';

    protected $description = 'Add Black Panther newspaper prisoners from the Oct 12, 1974 issue, batch 20';

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
        // Houston BPP co-defendants of James Aaron share a scaffold.
        $houston = function (string $name, string $first, string $last): array {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Texas',
                'era' => '1970s',
                'ideologies' => ['Black Power', 'Black liberation'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "{$name} was a member of the Houston chapter of the Black Panther Party who, with fellow members James Aaron and ".($name === 'Vernon Benton' ? 'Claude Frost' : 'Vernon Benton').", was prosecuted as part of what The Black Panther described (October 12, 1974) as a drive by the Houston power structure to destroy the chapter after a June 1971 police raid on the Party's headquarters. The three were charged with possession of stolen weapons, burglary, and felony theft; the burglary and theft charges proved so hard to substantiate that they were dropped, leaving only possession of stolen weapons, on which all three were tried and convicted.",
                'cases' => [[
                    'institution_state' => 'Texas',
                    'charges' => 'Possession of stolen weapons (following the June 1971 police raid on the Houston Black Panther Party headquarters); accompanying burglary and felony-theft charges were dropped',
                    'convicted' => 'Yes — tried and convicted with James Aaron and the other Houston Panther co-defendant',
                ]],
            ];
        };

        return [
            [
                'name' => 'Inez García',
                'first_name' => 'Inez',
                'last_name' => 'García',
                'gender' => 'Female',
                'race' => 'Hispanic',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Women\'s liberation', 'Self-defense'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Inez García was a young Latina woman who became a landmark figure in the women\'s self-defense movement after she killed Miguel Jiménez, one of two men she said had raped her, in Soledad, California, in 1974. As The Black Panther reported (October 12, 1974), a jury convicted her of murder — one juror telling the press afterward that the issue of rape "never entered the panel\'s deliberation" — and she faced a sentence of years to life, defended by the noted movement attorney Charles Garry. Her case became a rallying point for the argument that women have the right to defend themselves against rape. Her conviction was overturned on appeal, and at a 1977 retrial she was acquitted on grounds of self-defense.',
                'cases' => [[
                    'institution_state' => 'California',
                    'charges' => 'Second-degree murder of Miguel Jiménez, one of two men García said had raped her (Soledad, California, 1974)',
                    'convicted' => 'Convicted 1974; conviction overturned on appeal and acquitted at a 1977 retrial on grounds of self-defense',
                    'sentence' => 'Five years to life (later vacated)',
                ]],
            ],
            $houston('Vernon Benton', 'Vernon', 'Benton'),
            $houston('Claude Frost', 'Claude', 'Frost'),
            [
                'name' => 'Bobby Hardwick',
                'first_name' => 'Bobby',
                'last_name' => 'Hardwick',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Georgia',
                'era' => '1970s',
                'ideologies' => ['Black liberation', 'Prisoners\' rights'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Bobby Hardwick was a Black inmate activist at the Georgia State Prison in Reidsville who, The Black Panther reported (October 12, 1974), had been convicted of armed bank robbery and assaulting a police officer and who filed a federal civil-rights suit in August 1974 for the right to receive The Black Panther newspaper in prison. U.S. District Judge Wilbur Owens rejected the suit over an allegedly improperly completed filing questionnaire; Hardwick charged that Owens consistently denied Black people access to the Georgia courts. His suit named state corrections commissioner Allen Ault, warden James G. Ricketts, and prison official E.B. Blackburn.',
                'cases' => [[
                    'institution_name' => 'Georgia State Prison',
                    'institution_city' => 'Reidsville',
                    'institution_state' => 'Georgia',
                    'charges' => 'Armed bank robbery and assaulting a police officer; while imprisoned he fought a federal civil-rights case for the right to receive The Black Panther newspaper',
                    'convicted' => 'Yes',
                ]],
            ],
        ];
    }
}
