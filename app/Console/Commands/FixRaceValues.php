<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Normalizes prisoner race values:
 *  - "White and Cherokee" -> "Native American".
 *  - Trims stray leading/trailing whitespace (e.g. the lone "White " with a
 *    trailing space that shows up as a separate value from "White").
 *
 * Uses query-builder updates so the race change doesn't trigger model events
 * (slug/age recomputation). Idempotent.
 */
final class FixRaceValues extends Command
{
    protected $signature = 'prisoners:fix-race-values';

    protected $description = 'Normalize race values (White and Cherokee -> Native American; trim whitespace)';

    public function handle(): int
    {
        $changed = Prisoner::withoutGlobalScopes()
            ->where('race', 'White and Cherokee')
            ->update(['race' => 'Native American']);
        $this->info("\"White and Cherokee\" -> \"Native American\": {$changed} record(s).");

        // Any race with leading/trailing whitespace (fixes the stray "White ").
        $whitespace = Prisoner::withoutGlobalScopes()
            ->whereNotNull('race')
            ->whereRaw('race <> TRIM(race)')
            ->get(['id', 'name', 'race']);

        foreach ($whitespace as $p) {
            $clean = trim($p->race);
            Prisoner::withoutGlobalScopes()->whereKey($p->id)->update(['race' => $clean]);
            $this->info('Trimmed race on '.$p->name.': '.var_export($p->race, true)." -> '{$clean}'.");
        }

        if ($whitespace->isEmpty()) {
            $this->line('No whitespace-padded race values found.');
        }

        return self::SUCCESS;
    }
}
