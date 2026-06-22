<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Twenty-sixth batch from reading The Black Panther — the February 1, 1975 issue
 * (Vol. 12 No. 28). The issue's Attica Brothers coverage names several defendants
 * in the prosecutions arising from the September 1971 Attica rebellion. Already
 * recorded are Herbert X. Blyden, Roger Champen, Frank "Big Black" Smith and
 * Bernard Stroble (in the database as Shango Bahati Kakawana); newly added here
 * are four Attica defendants:
 *
 *  - Eric Thompson, John Hill (Dacajeweiah), Charles Pernasilice and Omar Seku.
 *
 * (Ruchell Magee's life sentence is also reported in the issue, but he is already
 * recorded.) Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers26Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-26';

    protected $description = 'Add Black Panther newspaper Attica Brothers from the Feb 1, 1975 issue, batch 26';

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
        $attica = function (string $name, string $first, string $last, ?string $race, string $detail, string $charges, ?string $convicted, ?string $aka = null): array {
            $rec = [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'state' => 'New York',
                'era' => '1970s',
                'ideologies' => ['Prison movement', 'Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "{$name} was one of the Attica Brothers — the prisoners the state of New York prosecuted after the September 1971 uprising at Attica Correctional Facility, which was crushed when state forces retook the prison and killed 29 inmates and 10 hostages. {$detail} As The Black Panther reported (February 1, 1975), the Attica Brothers' defense, led by William Kunstler and former U.S. Attorney General Ramsey Clark, won a string of pretrial rulings exposing the weakness of the state's cases; most of the 42 indictments against 62 men were ultimately dismissed, and Governor Hugh Carey closed out the prosecutions and granted clemencies in 1976.",
                'cases' => [[
                    'institution_name' => 'Attica Correctional Facility',
                    'institution_city' => 'Attica',
                    'institution_state' => 'New York',
                    'charges' => $charges,
                ]],
            ];
            if ($race !== null) {
                $rec['race'] = $race;
            }
            if ($aka !== null) {
                $rec['aka'] = $aka;
            }
            if ($convicted !== null) {
                $rec['cases'][0]['convicted'] = $convicted;
            }

            return $rec;
        };

        return [
            $attica('Eric Thompson', 'Eric', 'Thompson', 'Black',
                'He was one of five men the state originally charged with murder in connection with the rebellion.',
                'Murder, in connection with the September 1971 Attica rebellion (one of five men originally so charged)',
                null),
            $attica('John Hill', 'John', 'Hill', 'Native American',
                'A young Mohawk prisoner later widely known as Dacajeweiah ("Splitting the Sky"), he and Charles Pernasilice were tried for the death of corrections officer William Quinn during the uprising — the most serious of the Attica prosecutions.',
                'Murder of Attica corrections officer William Quinn, who died of injuries from the first hours of the September 1971 uprising',
                'Yes — convicted of murder in 1975 (the only Attica Brother convicted of a killing) and sentenced to 20 years to life; paroled in 1979',
                'Dacajeweiah; Splitting the Sky'),
            $attica('Charles Pernasilice', 'Charles', 'Pernasilice', null,
                'He was tried alongside John Hill for the death of corrections officer William Quinn during the uprising.',
                'Tried for the death of Attica corrections officer William Quinn (September 1971 uprising)',
                'Convicted of the lesser charge of attempted assault (1975)'),
            $attica('Omar Seku', 'Omar', 'Seku', 'Black',
                'He spent some two years in solitary confinement after being accused of kidnapping a guard, Franklin Kline, and stabbing an inmate, Ronald Lyons — accusations the prosecution abandoned after both Kline and Lyons told reporters the stabbing never happened and that Seku had in fact helped protect the guard.',
                'Kidnapping of guard Franklin Kline and assault on inmate Ronald Lyons during the September 1971 Attica uprising',
                'No — charges dropped after he served about two years in solitary confinement',
                'Chris Willoughby'),
        ];
    }
}
