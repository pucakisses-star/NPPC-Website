<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Data cleanup: removes the Robert W. Burns prisoner entry, and clears the
 * non-U.S. "state" categories (Venezuela, Northern Ireland, Guam) from any
 * prisoner carrying them so they no longer appear as state filter options.
 * Idempotent; matches regardless of the under-review global scope, and saves
 * each cleared record so the /api/prisoners cache invalidates.
 */
final class ClearForeignStatesRemoveBurns extends Command
{
    protected $signature = 'prisoners:clear-foreign-states';

    protected $description = 'Clear Venezuela/Northern Ireland/Guam state values and remove Robert W. Burns';

    private const STATES = ['Venezuela', 'Northern Ireland', 'North Ireland', 'Guam'];

    private const BURNS_ID = 'dc34373f-9fd4-4789-afa8-ba5a78f92975';

    public function handle(): int
    {
        // 1) Remove Robert W. Burns.
        $burns = Prisoner::withoutGlobalScopes()->find(self::BURNS_ID)
            ?? Prisoner::withoutGlobalScopes()->where('name', 'Robert W. Burns')->first();

        if ($burns) {
            $name = $burns->name;
            $burns->cases()->delete();
            $burns->podcastEpisodes()->delete();
            $burns->calendarEntries()->delete();
            $burns->delete();
            $this->info("Removed prisoner: {$name}");
        } else {
            $this->line('Robert W. Burns not found — nothing to remove.');
        }

        // 2) Clear the non-U.S. state categories.
        $affected = Prisoner::withoutGlobalScopes()->whereIn('state', self::STATES)->get();
        foreach ($affected as $prisoner) {
            $old = $prisoner->state;
            $prisoner->state = null;
            $prisoner->save();
            $this->line("Cleared state '{$old}' on {$prisoner->name}");
        }
        $this->info("\nCleared the state category on {$affected->count()} prisoner(s).");

        return self::SUCCESS;
    }
}
