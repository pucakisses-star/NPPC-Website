<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Sets Roman Sterlingov's year of birth (1988), stored with year-only
 * precision so it displays as just "1988". His exact DOB isn't public (it's
 * redacted from the court record); the year is well-sourced — reporting states
 * he was "born in Russia in 1988", and his ages (35 at his March 2024
 * conviction, 36 at his Nov 2024 sentencing) corroborate it. Idempotent;
 * matches by slug, then surname.
 */
final class SetSterlingovBirthYear extends Command
{
    protected $signature = 'prisoners:set-sterlingov-birthyear';

    protected $description = "Set Roman Sterlingov's year of birth (1988)";

    public function handle(): int
    {
        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'roman-sterlingov')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Sterlingov%')->first();

        if (! $prisoner) {
            $this->error('No Roman Sterlingov record found.');

            return self::FAILURE;
        }

        $prisoner->setPartialDate('birthdate', 1988);
        $prisoner->save();

        $this->info("Set year of birth 1988 on {$prisoner->name} (age {$prisoner->age}). View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
