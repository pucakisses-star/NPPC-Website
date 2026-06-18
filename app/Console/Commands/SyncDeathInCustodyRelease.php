<?php

namespace App\Console\Commands;

use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * One-time backfill: for every case that has a death-in-custody date, set the
 * release date to the same day (a death in custody ends the incarceration).
 * New/edited cases are kept in sync automatically by the PrisonerCase saving
 * hook; this fixes pre-existing rows. Idempotent.
 */
class SyncDeathInCustodyRelease extends Command
{
    protected $signature = 'prisoners:sync-death-in-custody-release {--dry-run}';

    protected $description = 'Set release_date = death_in_custody_date on cases that died in custody';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $cases = PrisonerCase::whereNotNull('death_in_custody_date')->get();
        $changed = 0;

        foreach ($cases as $case) {
            $death = $case->death_in_custody_date;
            if ($case->release_date && $case->release_date->isSameDay($death)) {
                continue;
            }

            $name = $case->prisoner?->name ?? $case->prisoner_id;
            $this->line(sprintf('%s %-26s release_date -> %s', $dry ? '[dry]' : '  ok', $name, $death->format('Y-m-d')));

            if (! $dry) {
                $case->release_date = $death;
                $case->save();
            }
            $changed++;
        }

        $this->info("\n".($dry ? 'Would update' : 'Updated')." {$changed} of ".$cases->count().' died-in-custody case(s).');

        return self::SUCCESS;
    }
}
