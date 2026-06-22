<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * Records the two specifically-documented arrests of Richard Oakes, the Mohawk
 * Red Power leader, verified in the Sonoma Index-Tribune / Press Democrat
 * ("Sonoma County remembers Alcatraz Activist Richard Oakes," Nov. 21, 2019):
 *
 *   - ~Thanksgiving 1970: arrested for an armed toll blockade at the Skaggs
 *     Springs / Tin Barn roads junction on the Stewart's Point (Kashia Pomo)
 *     Rancheria, protesting Sonoma County's widening of Skaggs Springs Road
 *     through the tribe's 40-acre reservation (the toll booth raised only $8).
 *   - May 1971: arrested for occupying the former Middletown Army radio
 *     receiving station.
 *
 * The sources give only month-level dates and no charges, jail durations or
 * dispositions (consistent with the "brief stints in jail" every biography
 * describes), so arrest_date is anchored to Thanksgiving Day 1970 (the earliest
 * documented arrest, approximate to within a few days) and no incarceration/
 * release dates are set — he is not known to have served a substantial term.
 * Idempotent: sets the case's narrative fields authoritatively on each run.
 */
final class SetOakesArrests extends Command
{
    protected $signature = 'prisoners:set-oakes-arrests';

    protected $description = 'Record Richard Oakes\'s documented 1970/1971 arrests (Stewart\'s Point toll blockade; Middletown occupation)';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Richard Oakes')->first();
        if (! $prisoner) {
            $this->warn('Richard Oakes not found, skipping.');

            return self::SUCCESS;
        }

        $charges = 'A leader of the Red Power movement who led the November 20, 1969 – June 11, 1971 '
            .'occupation of Alcatraz Island, Oakes was arrested and briefly jailed several times for '
            .'direct-action protests on behalf of California tribes. Around Thanksgiving 1970 he was '
            .'arrested for setting up an armed toll blockade at the Skaggs Springs / Tin Barn roads '
            ."junction on the Stewart's Point (Kashia Pomo) Rancheria, protesting Sonoma County's "
            .'widening of Skaggs Springs Road through the tribe\'s 40-acre reservation. In May 1971 he '
            .'was arrested again for occupying the former Middletown Army radio receiving station.';

        $convicted = 'Repeated brief arrests during direct-action protests (Stewart\'s Point toll '
            .'blockade, around Thanksgiving 1970; Middletown radio-station occupation, May 1971). The '
            .'specific charges and dispositions are not documented; he is not known to have been '
            .'convicted or to have served a substantial sentence.';

        $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);

        // Anchored to Thanksgiving Day 1970 (earliest documented arrest); the
        // source says "around Thanksgiving," so this is approximate.
        $case->arrest_date = '1970-11-26';
        $case->charges = $charges;
        $case->convicted = $convicted;
        $case->sentence = 'Brief jailings tied to his activism; no documented sentence.';
        $case->save();

        $this->info('Updated Richard Oakes case with documented 1970/1971 arrests.');

        return self::SUCCESS;
    }
}
