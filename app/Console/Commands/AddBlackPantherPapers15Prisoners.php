<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Fifteenth batch from reading The Black Panther — drawn from the spring 1974
 * issues:
 *
 *  - Freddie Lee Pitts and Wilbert Lee — two Black men sentenced to death in
 *    Florida for the 1963 Port St. Joe gas-station murders on beaten confessions,
 *    though the actual killer confessed; pardoned by Gov. Reubin Askew in 1975.
 *  - James Carrington — a young Black man given a 75-year sentence on a frame-up
 *    rape charge by an all-white Appomattox, Virginia jury (1970).
 *
 * (The Charlotte Three's Charles Parker, the Fountain Valley Five, and others in
 * these issues were already in the database.) Idempotent: skips any name already
 * present.
 */
final class AddBlackPantherPapers15Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-15';

    protected $description = 'Add Black Panther newspaper prisoners from the spring 1974 issues, batch 15';

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
        // Pitts & Lee — shared Florida death-row frame-up.
        $pittsLee = function (string $name, string $first, string $last, string $descExtra): array {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Florida',
                'era' => '1960s',
                'ideologies' => [],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "{$name} was one of two Black men, with ".($first === 'Freddie' ? 'Wilbert Lee' : 'Freddie Lee Pitts').", falsely convicted of the August 1963 kidnapping, robbery and murder of two white gas-station attendants in Port St. Joe, Florida. They were convicted with no material evidence, on confessions obtained through beatings and threats, and sentenced to death in 1968; in 1968 a key witness admitted she had lied under police threat.{$descExtra} After the Florida Supreme Court ordered a new grand jury in 1969 (Black citizens had been excluded), an all-white jury again convicted them in 1972 and they were sentenced to be electrocuted — even though another man, Curtis Adams Jr., had confessed to the killings. They were finally pardoned by Governor Reubin Askew in 1975. Documented in The Black Panther (April 27, 1974).",
                'cases' => [[
                    'institution_name' => 'Florida State Prison',
                    'institution_state' => 'Florida',
                    'charges' => 'Kidnapping, robbery, and the murder of two white gas-station attendants (Port St. Joe, Florida, August 1963) — convicted on beaten confessions, with no material evidence',
                    'convicted' => 'Yes — sentenced to death (1968; re-convicted 1972); pardoned by Gov. Reubin Askew in 1975 after another man confessed',
                    'sentence' => 'Death (later pardoned)',
                ]],
            ];
        };

        return [
            $pittsLee('Freddie Lee Pitts', 'Freddie', 'Pitts',
                ' Pitts, a Black soldier, was beaten until he confessed to the killings.'),
            $pittsLee('Wilbert Lee', 'Wilbert', 'Lee',
                ' Lee, a pulpwood cutter at whose home Pitts had attended a party that night, confessed after being told his wife would be harmed.'),
            [
                'name' => 'James Carrington',
                'first_name' => 'James',
                'last_name' => 'Carrington',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Virginia',
                'era' => '1970s',
                'ideologies' => [],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'James Carrington was a young Black man convicted in 1970 in Appomattox, Virginia of raping a white friend and sentenced to 75 years — a conviction his supporters, organized as a Prisoners Solidarity Committee, said was a frame-up. By 1974, having served four years, he won an argument before the U.S. Fourth Circuit Court of Appeals that there had been racial discrimination in the selection of the all-white jury of twelve elderly men that had convicted him. Documented in The Black Panther (May 11, 1974).',
                'cases' => [[
                    'institution_state' => 'Virginia',
                    'charges' => 'Rape (Appomattox, Virginia, 1970) — the defense showed racial discrimination in the selection of the all-white jury',
                    'convicted' => 'Yes — convicted 1970 by an all-white jury',
                    'sentence' => '75 years',
                ]],
            ],
        ];
    }
}
