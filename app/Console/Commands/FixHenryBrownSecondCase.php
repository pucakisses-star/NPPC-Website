<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Corrects Henry "Sha Sha" Brown's second case. His record carried an
 * unverified case — "bank expropriation, weapons charges, escape from the Tombs"
 * with a placeholder Jan-1-1975 arrest date and a claimed 1976 escape from the
 * Manhattan House of Detention. Research could not confirm any of that: the only
 * documented escape was the September 1973 Kings County Hospital escape (while he
 * was held at the BROOKLYN House of Detention for the Foster/Laurie trial), and
 * the "Tombs 1976" claim appears to be a conflation of that 1973 escape.
 *
 * What is documented (Twymon Myers record / contemporaneous reporting): on
 * February 14, 1972, stopped by the NYPD while driving toward St. Louis, Brown
 * and other BLA members exchanged fire with police and Brown accidentally shot
 * and killed fellow BLA member Ronald Carter. By one account he was convicted
 * and sentenced to 25 years to life for that shootout — the conviction on which
 * he remained imprisoned after his 1974 acquittal on the officers' murders.
 *
 * This repurposes the dubious case into that accurate shootout case. Idempotent.
 */
class FixHenryBrownSecondCase extends Command
{
    protected $signature = 'prisoners:fix-henry-brown-second-case';

    protected $description = 'Replace Henry "Sha Sha" Brown\'s unverified bank/weapons/Tombs case with the documented 1972 Ronald Carter shootout';

    public function handle(): int
    {
        DB::transaction(function () {
            $prisoner = Prisoner::withUnderReview()
                ->where('name', 'Henry Brown')
                ->where('aka', 'like', '%Sha Sha%')
                ->first();

            if (! $prisoner) {
                $this->warn('Henry "Sha Sha" Brown not found — nothing to do.');

                return;
            }

            $dubious = $prisoner->cases()
                ->where(function ($q) {
                    $q->where('charges', 'like', '%Bank expropriation%')
                        ->orWhere('charges', 'like', '%Tombs%');
                })
                ->first();

            if (! $dubious) {
                if ($prisoner->cases()->where('charges', 'like', '%Ronald Carter%')->exists()) {
                    $this->info('Already corrected — the shootout case is in place.');
                } else {
                    $this->warn('Original bank/weapons/Tombs case not found — nothing to correct.');
                }

                return;
            }

            $dubious->charges = 'Fatal shooting of fellow Black Liberation Army member Ronald Carter, and firing on police, during the February 14, 1972 shootout after he and other BLA members were stopped by the NYPD while driving toward St. Louis.';
            $dubious->convicted = 'By one account, convicted and sentenced to 25 years to life for the shootout — the conviction on which he remained imprisoned after his 1974 acquittal on the Foster and Laurie murders. Reported by a single secondary source; not independently confirmed.';
            $dubious->sentence = 'Reportedly 25 years to life.';
            $dubious->arrest_date = '1972-02-14';
            $dubious->incarceration_date = '1972-02-14';
            $dubious->release_date = null;
            $dubious->save();

            $this->info('Corrected Henry Brown\'s second case: bank/weapons/Tombs -> the 1972 Ronald Carter shootout.');
        });

        return self::SUCCESS;
    }
}
