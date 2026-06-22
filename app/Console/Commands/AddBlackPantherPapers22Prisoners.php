<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Twenty-second batch from reading The Black Panther — the October 19, 1974
 * issue (Vol. 12 No. 13). That issue's other case coverage is police-killing
 * stories where grand juries refused to indict the officers (Luther Young Jr.
 * in Marshall County, Mississippi; Alberto Terrones Jr. in Union City,
 * California) — victims rather than prisoners — plus a follow-up on Inez García
 * (already recorded, batch 20). Its one new political prisoner is:
 *
 *  - Willie E. Smith — the defendant in the first of the Attica Brothers cases
 *    to reach trial, whose charges were dismissed.
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers22Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-22';

    protected $description = 'Add Black Panther newspaper prisoners from the Oct 19, 1974 issue, batch 22';

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
                'name' => 'Willie E. Smith',
                'first_name' => 'Willie',
                'last_name' => 'Smith',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'New York',
                'era' => '1970s',
                'ideologies' => ['Prison movement', 'Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Willie E. Smith was the defendant in the first of the Attica Brothers cases to reach trial — the prosecutions the state brought against inmates after the September 1971 uprising at New York\'s Attica Correctional Facility. As The Black Panther reported (October 19, 1974), New York State Supreme Court Justice Frank F. Barger, sitting in Buffalo, dismissed the two counts of sodomy and two counts of sexual abuse the state had charged against Smith — who had been accused of attacking another prisoner during the rebellion — making his the first of the Attica indictments to collapse at trial. He was represented by Rochester attorney James L. Kemp.',
                'cases' => [[
                    'institution_name' => 'Attica Correctional Facility',
                    'institution_city' => 'Attica',
                    'institution_state' => 'New York',
                    'charges' => 'Two counts of sodomy and two counts of sexual abuse, alleging he attacked another prisoner during the September 1971 Attica uprising — the first Attica Brothers indictment to be tried',
                    'convicted' => 'No — all charges dismissed at trial by New York State Supreme Court Justice Frank F. Barger',
                ]],
            ],
        ];
    }
}
