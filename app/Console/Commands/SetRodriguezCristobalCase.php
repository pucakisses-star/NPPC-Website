<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * Fills the arrest/sentencing/incarceration/death dates on Ángel Rodríguez
 * Cristóbal's existing case. The Liga Socialista Puertorriqueña militant was
 * arrested on May 19, 1979 in the mass civil-disobedience landing on the U.S.
 * Navy's Vieques bombing range, convicted of trespassing, and sentenced to six
 * months on September 26, 1979 by federal judge Juan R. Torruella. He entered
 * the Federal Correctional Institution at Tallahassee, Florida on September 28,
 * 1979 and was found dead in his cell 44 days later, on November 11, 1979 —
 * ruled a suicide by the authorities but widely held by the independence
 * movement to have been a beating death (his body bore a deep wound above the
 * left eyebrow). His portrait is attached separately via
 * prisoners:attach-nonfree-photos.
 *
 * He was born on April 2, 1946 in Barrio Pozas, Ciales (per Claridad), so his
 * birthdate is set here alongside the case dates.
 * Idempotent: sets the documented dates authoritatively on each run.
 */
final class SetRodriguezCristobalCase extends Command
{
    protected $signature = 'prisoners:set-rodriguez-cristobal-case';

    protected $description = 'Set Ángel Rodríguez Cristóbal\'s Vieques arrest/imprisonment/death dates';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()
            ->where('name', 'like', '%Rodríguez Cristóbal%')
            ->orWhere('name', 'like', '%Rodriguez Cristobal%')
            ->first();

        if (! $prisoner) {
            $this->warn('Ángel Rodríguez Cristóbal not found, skipping.');

            return self::SUCCESS;
        }

        $prisoner->birthdate = '1946-04-02';
        // The arrest was May 19, 1979 (federal court record in U.S. v. Parrilla
        // Bonilla and the Puerto Rican chronologies agree); correct the earlier
        // "May 21" that had propagated into the prose.
        $prisoner->description = str_replace('May 21, 1979', 'May 19, 1979', (string) $prisoner->description);
        $prisoner->save();

        $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);

        $case->arrest_date = '1979-05-19';
        $case->sentenced_date = '1979-09-26';
        $case->incarceration_date = '1979-09-28';
        $case->death_in_custody_date = '1979-11-11';
        $case->charges = str_replace('May 21, 1979', 'May 19, 1979', (string) $case->charges);
        $case->save();

        $this->info("Set Ángel Rodríguez Cristóbal case dates (imprisoned_for_days={$case->imprisoned_for_days}).");

        return self::SUCCESS;
    }
}
