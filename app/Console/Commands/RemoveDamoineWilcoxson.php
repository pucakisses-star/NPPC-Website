<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Removes the Damoine Wilcoxson prisoner entry (and its related cases, podcast
 * episodes, and calendar entries) per request. Idempotent — a no-op if no
 * matching record exists. Matched by name so it works regardless of the
 * under-review global scope.
 */
final class RemoveDamoineWilcoxson extends Command
{
    protected $signature = 'prisoners:remove-damoine-wilcoxson';

    protected $description = 'Remove the Damoine Wilcoxson prisoner entry';

    public function handle(): int
    {
        $prisoners = Prisoner::withoutGlobalScopes()
            ->where('name', 'like', '%Wilcoxson%')
            ->get();

        if ($prisoners->isEmpty()) {
            $this->info("No 'Wilcoxson' prisoner found — nothing to remove.");

            return self::SUCCESS;
        }

        foreach ($prisoners as $prisoner) {
            $name = $prisoner->name;
            $id = $prisoner->id;
            $caseCount = $prisoner->cases()->count();

            // Clear related rows first so foreign keys don't block the delete.
            $prisoner->cases()->delete();
            $prisoner->podcastEpisodes()->delete();
            $prisoner->calendarEntries()->delete();
            $prisoner->delete();

            $this->info("Removed prisoner '{$name}' ({$id}) and {$caseCount} case(s).");
        }

        return self::SUCCESS;
    }
}
