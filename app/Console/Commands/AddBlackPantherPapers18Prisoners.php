<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Eighteenth batch from reading The Black Panther — the September 7, 1974 issue
 * (Vol. 12 No. 7), continuing on from batch 17 (the August 31, 1974 issue).
 *
 * The issue's prisoner coverage centers on the "Leavenworth Brothers": six
 * prisoners — four Black (Odell Bennett, Jesse Lee Evans, Alf Hill Jr., Alfred
 * Jasper) and two Chicano (Jesse Lopez, Armando Miramon) — charged over the
 * July 31, 1973 rebellion at the U.S. Penitentiary at Leavenworth, Kansas, whom
 * their defense committee said were framed and scapegoated for leading protests
 * of the prison's conditions. (The issue's Angola coverage centers on Herman
 * Wallace of the Angola 3, who is already recorded.)
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers18Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-18';

    protected $description = 'Add Black Panther newspaper prisoners from the Sep 7, 1974 issue, batch 18';

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
        // Shared scaffold for the six Leavenworth Brothers.
        $brother = function (string $name, string $first, string $last, string $race, string $detail, string $charges, string $convicted, ?string $middle = null) {
            $rec = [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'race' => $race,
                'state' => 'Kansas',
                'era' => '1970s',
                'ideologies' => ['Prisoners\' rights', 'Prison movement'],
                'affiliation' => ['Leavenworth Brothers'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "{$name} was one of the \"Leavenworth Brothers\" — six prisoners (four Black and two Chicano) charged in connection with the July 31, 1973 rebellion at the U.S. Penitentiary at Leavenworth, Kansas. As The Black Panther reported (September 7, 1974), their supporters, organized as the Leavenworth Brothers Offense/Defense Committee and joined by antiwar priest Philip Berrigan at an August 16, 1974 rally, held that the men had been framed and made scapegoats by the prison administration because of their leadership in protesting the penitentiary's inhumane conditions. {$detail}",
                'cases' => [[
                    'institution_name' => 'United States Penitentiary, Leavenworth',
                    'institution_city' => 'Leavenworth',
                    'institution_state' => 'Kansas',
                    'charges' => $charges,
                    'convicted' => $convicted,
                ]],
            ];
            if ($middle) {
                $rec['middle_name'] = $middle;
            }

            return $rec;
        };

        $base = 'Charged in connection with the July 31, 1973 rebellion at the U.S. Penitentiary at Leavenworth, Kansas; supporters said the Leavenworth Brothers were framed and scapegoated for leading protests of inhumane prison conditions';

        return [
            $brother('Odell Bennett', 'Odell', 'Bennett', 'Black',
                'Mutiny charges against Odell Bennett were dropped in August 1974.',
                $base.' (mutiny)',
                'No — mutiny charges dropped (August 1974)'),
            $brother('Jesse Lee Evans', 'Jesse', 'Evans', 'Black',
                'Mutiny charges against Jesse Lee Evans were dropped in August 1974.',
                $base.' (mutiny)',
                'No — mutiny charges dropped (August 1974)', 'Lee'),
            $brother('Alf Hill Jr.', 'Alf', 'Hill', 'Black',
                'The mutiny and assault charges against Alf Hill Jr. were dropped in August 1974.',
                $base.' (mutiny and assault)',
                'No — mutiny and assault charges dropped (August 1974)'),
            $brother('Alfred Jasper', 'Alfred', 'Jasper', 'Black',
                'The mutiny and assault charges against Alfred Jasper were dropped in August 1974.',
                $base.' (mutiny and assault)',
                'No — mutiny and assault charges dropped (August 1974)'),
            $brother('Jesse Lopez', 'Jesse', 'Lopez', 'Hispanic',
                'A Chicano prisoner, Jesse Lopez had his case severed from the other four brothers and was tried separately alongside Armando Miramon.',
                $base.'; his case was severed from the other four and tried separately',
                'Tried separately (case severed from the other four Leavenworth Brothers, 1974)'),
            $brother('Armando Miramon', 'Armando', 'Miramon', 'Hispanic',
                'A Chicano prisoner, Armando Miramon had his case severed from the other four brothers and was tried separately alongside Jesse Lopez.',
                $base.'; his case was severed from the other four and tried separately',
                'Tried separately (case severed from the other four Leavenworth Brothers, 1974)'),
        ];
    }
}
