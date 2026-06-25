<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Marks Chase Grijalva as having been in custody continuously since his arrest:
 * sets each case's incarceration_date to its arrest_date (so the public page
 * counts his imprisonment from the arrest rather than from sentencing) and keeps
 * him flagged in custody. Idempotent; matches an existing record (prod may hold
 * him even when the local snapshot does not).
 */
final class SetGrijalvaCustodyFromArrest extends Command
{
    protected $signature = 'prisoners:set-grijalva-custody-from-arrest';

    protected $description = 'Set Chase Grijalva as in custody since his arrest (incarceration_date = arrest_date)';

    public function handle(): int
    {
        $prisoner = Prisoner::withoutGlobalScopes()
            ->where('slug', 'chase-grijalva')
            ->orWhere('name', 'like', '%Grijalva%')
            ->with('cases')
            ->first();

        if (! $prisoner) {
            $this->warn('No Chase Grijalva record found — nothing to change.');

            return self::SUCCESS;
        }

        $prisoner->in_custody = true;
        $prisoner->released = false;
        $prisoner->save();

        $updated = 0;
        foreach ($prisoner->cases as $case) {
            if (! $case->arrest_date) {
                $this->warn("Case {$case->id} has no arrest date — left unchanged.");

                continue;
            }

            $case->incarceration_date = $case->arrest_date;
            $case->mirrorDatePrecision('arrest_date', 'incarceration_date');
            $case->save();
            $updated++;
            $this->info("Case {$case->id}: incarceration_date set to {$case->incarceration_date->toDateString()} (since arrest).");
        }

        $this->info("Done. {$prisoner->name}: {$updated} case(s) updated. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
