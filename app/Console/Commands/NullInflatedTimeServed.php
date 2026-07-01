<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * Applies the released-with-no-release-date fix to existing rows.
 *
 * The PrisonerCase save hook now leaves imprisoned_for_days null (instead of
 * counting to today) when a prisoner has an incarceration date, no release
 * date, and is NOT still detained. This command re-saves every already-stored
 * case in that state so the correction takes effect — clearing the fabricated
 * multi-year "time served" figures (e.g. Civil War prisoners showing ~160
 * years) for the ~600 records still lacking a researched release date.
 *
 * Records where the prisoner is genuinely still in custody / awaiting trial are
 * left untouched (their count-to-today is correct). Idempotent.
 */
final class NullInflatedTimeServed extends Command
{
    protected $signature = 'prisoners:null-inflated-time-served';

    protected $description = 'Null out inflated time-served on released prisoners with no recorded release date';

    public function handle(): int
    {
        $cases = PrisonerCase::whereNotNull('incarceration_date')
            ->whereNull('release_date')
            ->get();

        $fixed = 0;
        $keptDetained = 0;

        foreach ($cases as $case) {
            $prisoner = Prisoner::withoutGlobalScopes()->find($case->prisoner_id);
            if (! $prisoner) {
                continue;
            }

            if ($prisoner->in_custody || $prisoner->awaiting_trial) {
                $keptDetained++; // genuinely still detained — count-to-today is correct

                continue;
            }

            $before = $case->imprisoned_for_days;
            $case->save(); // saving hook now recomputes this to null

            if ($before !== null) {
                $fixed++;
            }
        }

        $this->info("Nulled inflated time-served on {$fixed} released record(s); left {$keptDetained} still-detained record(s) untouched.");

        return self::SUCCESS;
    }
}
