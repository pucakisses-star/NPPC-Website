<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Sets an approximate year of birth for Shamim Mafi, derived from her stated
 * age (44). Stored with year-only precision (so it displays as just "1982"),
 * which keeps her computed age at 44. This is an estimate from age — not a
 * confirmed date of birth — and can be replaced if an exact DOB is found.
 * Idempotent; matches by slug, then surname.
 */
final class SetMafiBirthYear extends Command
{
    protected $signature = 'prisoners:set-mafi-birthyear';

    protected $description = "Set Shamim Mafi's (approximate) year of birth from her age";

    public function handle(): int
    {
        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'shamim-mafi')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Mafi%')->first();

        if (! $prisoner) {
            $this->error('No Shamim Mafi record found.');

            return self::FAILURE;
        }

        // 44 years old as of 2026 -> born ~1982. Year precision.
        $prisoner->setPartialDate('birthdate', 1982);
        $prisoner->save();

        $this->info("Set year of birth ~1982 on {$prisoner->name} (age {$prisoner->age}). View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
