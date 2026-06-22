<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * Records Richard Oakes's documented activism arrests and his only documented
 * jail sentence, drawn from Kent Blansett's biography "A Journey to Freedom:
 * Richard Oakes, Alcatraz, and the Red Power Movement" (Yale Univ. Press, 2018)
 * and the period newspapers it cites:
 *
 *   - May 1971: arrested (~May 3, per the Redding Record-Searchlight) in the
 *     takeover of the former U.S. Army radio receiving station near Middletown
 *     (Lake County) — part of the Elem Pomo campaign to reclaim Rattlesnake
 *     Island/Clear Lake land. CONVICTED by an all-white jury of trespass and
 *     unlawful entry of a government facility; sentenced to ten days in jail and
 *     a $125 fine. This is his only documented jail sentence for his activism.
 *   - Nov. 1970 (~Nov. 21; Press Democrat, Nov. 22, 1970): armed toll blockade
 *     on Skaggs Springs Road through the Stewart's Point (Kashia Pomo)
 *     Rancheria — arrested for "armed robbery," bail $6,125, released once he
 *     agreed to repay the toll fees; trial set for September 1971.
 *   - 1970: Pit River mass arrest — bailed out (by Buffy Sainte-Marie) and
 *     released the same evening.
 *   - March 1970: Fort Lawton takeover (Seattle) — held in the military
 *     stockade and released; related sentences were suspended.
 *
 * The case is anchored on the Middletown conviction (the one event with a clean
 * arrest -> conviction -> sentence arc); the other arrests are kept as context.
 * No incarceration/release dates are set because the exact ten-day service
 * window is not documented. Idempotent — sets the fields authoritatively.
 */
final class SetOakesArrests extends Command
{
    protected $signature = 'prisoners:set-oakes-arrests';

    protected $description = 'Record Richard Oakes\'s activism arrests and his 10-day Middletown jail sentence (per Blansett biography)';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Richard Oakes')->first();
        if (! $prisoner) {
            $this->warn('Richard Oakes not found, skipping.');

            return self::SUCCESS;
        }

        $charges = 'A leader of the Red Power movement — he led the November 20, 1969 – June 11, 1971 '
            .'occupation of Alcatraz Island and organized land-claim occupations across northern '
            .'California — Oakes was arrested repeatedly for direct action. His documented conviction '
            .'came from the May 1971 takeover of the former U.S. Army radio receiving station near '
            .'Middletown (Lake County), part of the Elem Pomo campaign to reclaim Rattlesnake Island '
            .'and Clear Lake land; he was seized by sheriff\'s deputies when he left to fetch supplies. '
            .'He was also arrested at the Fort Lawton takeover (Seattle, March 1970), in the Pit River '
            .'land struggle against PG&E (1970), and for an armed toll blockade on Skaggs Springs Road '
            .'through the Stewart\'s Point (Kashia Pomo) Rancheria in November 1970.';

        $convicted = 'Yes — convicted by an all-white jury of trespass and unlawful entry of a '
            .'government facility for the May 1971 Middletown occupation. (His other Red Power arrests '
            .'did not lead to substantial convictions.)';

        $sentence = 'Ten days in jail and a $125 fine for the Middletown conviction — his only '
            .'documented jail sentence for his activism. His other arrests brought only brief '
            .'detention: at the 1970 Pit River mass arrest he was bailed out and released the same '
            .'evening; at Fort Lawton (March 1970) he was held in the military stockade and released '
            .'(the related sentences were suspended); and after the November 1970 armed toll blockade '
            .'at the Stewart\'s Point Rancheria he was arrested for "armed robbery" (bail $6,125) but '
            .'released once he agreed to repay the toll fees. (His only lengthy incarceration was '
            .'pre-political: a three-year sentence at Comstock/Great Meadow prison as a young man, on '
            .'which he was paroled within the first year.)';

        $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);

        // Anchored on the Middletown arrest (~May 3, 1971, per the Record-Searchlight),
        // the one arrest that produced his documented conviction and jail sentence.
        $case->arrest_date = '1971-05-03';
        $case->charges = $charges;
        $case->convicted = $convicted;
        $case->sentence = $sentence;
        $case->save();

        $this->info('Updated Richard Oakes case from the Blansett biography (Middletown conviction, 10-day sentence).');

        return self::SUCCESS;
    }
}
