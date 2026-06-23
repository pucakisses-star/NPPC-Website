<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Enriches Larry Guy's record (previously only a generic PFOC Breakthrough
 * listing) from a Revolutionary Worker account of the Battle Creek, Michigan
 * police repression of the Guy family. Larry — older brother of Robert Guy, a
 * Black revolutionary assassinated by a pipe bomb on August 31, 1981 — was
 * stopped and beaten unconscious by police in June 1979 and charged with a
 * concealed weapon (a frame-up, per supporters); convicted by an all-white jury
 * in August 1980 and imprisoned at the State Prison of Southern Michigan
 * (Jackson) on a 3-to-5-year term; that sentence was raised to 6-to-10 years in
 * March 1981 under Michigan's "habitual criminal" law. Dates are kept in the
 * narrative (month precision only) rather than asserted as exact day fields.
 * Upsert / idempotent.
 */
final class UpdateLarryGuy extends Command
{
    protected $signature = 'prisoners:update-larry-guy';

    protected $description = 'Enrich Larry Guy from the Revolutionary Worker Battle Creek account';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()
            ->where('name', 'Larry Guy')
            ->first();

        if (! $prisoner) {
            $this->warn('Larry Guy record not found, skipping.');

            return self::SUCCESS;
        }

        $description = 'Larry Guy was a Black activist in Battle Creek, Michigan, and the older brother of Robert Guy '
            .'— a Black revolutionary assassinated by a pipe bomb on August 31, 1981, a killing his supporters '
            .'attributed to the Battle Creek police. The Guy family helped lead the fight against police brutality in '
            .'Battle Creek, founding the newspaper Black Alleged News (BAN) and organizing through the Black United '
            .'Front and the Coalition to End Police Brutality; Robert was also a member of the Republic of New Afrika. '
            .'Singled out for police attacks, Larry was stopped by police in June 1979 (along with Robert and their '
            .'cousin Willie), beaten unconscious, and arrested on a concealed-weapons charge his supporters condemned '
            .'as a frame-up. In August 1980 an all-white jury convicted him, and he was sent to the State Prison of '
            .'Southern Michigan at Jackson on a 3-to-5-year sentence. In March 1981 the state invoked Michigan\'s '
            .'"habitual criminal" law to increase his sentence to 6-to-10 years — a hearing 150 people came out to '
            .'protest. His case became a rallying point against police repression in Battle Creek.';

        DB::transaction(function () use ($prisoner, $description) {
            $prisoner->description = $description;
            $prisoner->in_custody = false;
            $prisoner->released = true;
            $prisoner->save();

            $case = $prisoner->cases()->first();
            if (! $case) {
                $this->warn('No case found for Larry Guy; nothing to update.');

                return;
            }

            $case->charges = 'Concealed-weapons charge. In June 1979 police stopped Larry Guy along with his brother '
                .'Robert and their cousin Willie, beat Larry unconscious, and arrested him — claiming he had a handgun '
                .'— amid the Guy family\'s organizing against police brutality in Battle Creek, Michigan. Supporters '
                .'and the Revolutionary Worker described the charge as a trumped-up frame-up.';
            $case->convicted = 'Yes — convicted of the concealed-weapons charge by an all-white jury in August 1980 and '
                .'sent to the State Prison of Southern Michigan at Jackson. In March 1981 the state used Michigan\'s '
                .'"habitual criminal" law to increase his sentence.';
            $case->sentence = 'Originally 3 to 5 years; increased to 6 to 10 years under Michigan\'s habitual-criminal '
                .'statute in March 1981.';
            $case->save();
        });

        $this->info("Updated {$prisoner->name}: description and case enriched from the Revolutionary Worker account.");

        return self::SUCCESS;
    }
}
