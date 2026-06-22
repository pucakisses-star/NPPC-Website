<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * Records the exact 1985 release dates for the two activists who took over the
 * Chilean consulate in San Juan in 1978: Pablo Marcano García was released on
 * June 21, 1985 and Nydia Cuevas on August 18, 1985. Both were held from their
 * July 4, 1978 arrest, so the incarceration date is set as well, giving each a
 * roughly seven-year imprisonment span.
 *
 * Idempotent: finds the existing records, sets the case incarceration/release
 * dates authoritatively, and tightens the "released in 1985 / the 1980s" prose
 * to the exact dates.
 */
final class SetChileanConsulateReleases extends Command
{
    protected $signature = 'prisoners:set-chilean-consulate-releases';

    protected $description = 'Set Pablo Marcano García (June 21, 1985) and Nydia Cuevas (Aug 18, 1985) release dates';

    public function handle(): int
    {
        $people = [
            [
                'match' => 'Pablo Marcano',
                'release' => '1985-06-21',
                'desc_from' => 'released in 1985',
                'desc_to' => 'released on June 21, 1985',
                'sentence' => 'Initially sentenced to 22 years, reduced to seven; held from his July 1978 arrest and released on June 21, 1985.',
            ],
            [
                'match' => 'Nydia Cuevas',
                'release' => '1985-08-18',
                'desc_from' => 'released in the 1980s',
                'desc_to' => 'released on August 18, 1985',
                'sentence' => 'Imprisoned for the consulate takeover from her July 1978 arrest; released on August 18, 1985.',
            ],
        ];

        foreach ($people as $cfg) {
            $prisoner = Prisoner::withUnderReview()->where('name', 'like', '%'.$cfg['match'].'%')->first();
            if (! $prisoner) {
                $this->warn("Not found, skipping: {$cfg['match']}");

                continue;
            }

            $prisoner->description = str_replace($cfg['desc_from'], $cfg['desc_to'], (string) $prisoner->description);
            $prisoner->in_custody = false;
            $prisoner->released = true;
            $prisoner->save();

            $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $case->incarceration_date = '1978-07-04';
            $case->release_date = $cfg['release'];
            $case->sentence = $cfg['sentence'];
            $case->save();

            $this->info("{$prisoner->name}: released {$cfg['release']} (imprisoned_for_days={$case->imprisoned_for_days}).");
        }

        return self::SUCCESS;
    }
}
