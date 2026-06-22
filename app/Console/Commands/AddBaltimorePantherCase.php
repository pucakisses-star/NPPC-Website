<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * The Baltimore Black Panther case (1970-71) — the prosecution over the torture
 * killing of Eugene Leroy Anderson, a 20-year-old Panther sympathizer whose body
 * was found in Leakin Park on October 27, 1969. Maryland authorities charged some
 * 21 members of the Baltimore chapter, in what supporters and many observers saw
 * as an effort to destroy the party there; in the end no one was convicted of the
 * killing except a single defendant, who was released after four years by Gov.
 * Marvin Mandel.
 *
 * Recorded here are the documentable defendants missing from the database:
 * movement lawyer Arthur Turco (mistrial, then a misdemeanor plea), Charles Wyche
 * (tried April 1971) and Irving Young (alleged driver, tried November 1970).
 * (Marshall "Eddie" Conway, prosecuted in a separate Baltimore case, is already
 * recorded.) Idempotent: skips any name already present.
 */
final class AddBaltimorePantherCase extends Command
{
    protected $signature = 'prisoners:add-baltimore-panther-case';

    protected $description = 'Add the Baltimore Black Panther (Eugene Anderson) case defendants';

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
        $caseNote = ' Eugene Leroy Anderson, a 20-year-old Panther sympathizer, was tortured and shot to death in July 1969 and his body left in Baltimore\'s Leakin Park, where it was found on October 27, 1969. Maryland authorities charged some 21 members of the Baltimore Black Panther Party — a prosecution widely seen as an attempt to destroy the chapter — but no one was ultimately convicted of the killing except one defendant, who was released after four years by Governor Marvin Mandel.';

        return [
            [
                'name' => 'Arthur Turco',
                'first_name' => 'Arthur',
                'last_name' => 'Turco',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'Maryland',
                'era' => '1970s',
                'ideologies' => ['Civil rights'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Arthur Turco Jr. was a New York movement lawyer indicted on May 1, 1970 for taking part in the torture and murder of Eugene Leroy Anderson, in the Baltimore Black Panther case.'.$caseNote.' Turco spent about a year in the Baltimore City Jail awaiting trial; his trial ran June 16 to July 3, 1971 before Judge James W. Murphy and ended in a mistrial (the jury reportedly stood 10-2 for conviction). He then accepted an offer to plead guilty to a misdemeanor and was freed on time served; William Kunstler took part in his defense.',
                'cases' => [[
                    'institution_name' => 'Baltimore City Jail',
                    'institution_city' => 'Baltimore',
                    'institution_state' => 'Maryland',
                    'charges' => 'Conspiracy and murder in the torture-killing of Eugene Leroy Anderson',
                    'convicted' => 'No — mistrial (June-July 1971); later pleaded guilty to a misdemeanor and was freed on time served',
                ]],
            ],
            [
                'name' => 'Charles Wyche',
                'first_name' => 'Charles',
                'last_name' => 'Wyche',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Maryland',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Charles Wyche was a member of the Baltimore Black Panther Party tried in April 1971 in the prosecution over the torture-killing of Eugene Leroy Anderson.'.$caseNote,
                'cases' => [[
                    'institution_state' => 'Maryland',
                    'charges' => 'Murder of Eugene Leroy Anderson (the Baltimore Black Panther case; tried April 1971)',
                ]],
            ],
            [
                'name' => 'Irving Young',
                'first_name' => 'Irving',
                'last_name' => 'Young',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Maryland',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Irving Young was a member of the Baltimore Black Panther Party whom police alleged had driven Eugene Leroy Anderson and his killers to Leakin Park in July 1969; his case went to trial in November 1970.'.$caseNote,
                'cases' => [[
                    'institution_state' => 'Maryland',
                    'charges' => 'Murder of Eugene Leroy Anderson — alleged to have driven the victim and his killers to Leakin Park (the Baltimore Black Panther case; tried November 1970)',
                ]],
            ],
        ];
    }
}
