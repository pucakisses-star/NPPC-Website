<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Removes the Roy Alen Clyde and Wade Greely Lay prisoner entries (and their
 * related cases, podcast episodes, and calendar entries) per request. Each
 * target is matched on two distinctive name parts to avoid removing anyone
 * else. Idempotent — a no-op for any target with no match.
 */
final class RemoveClydeLay extends Command
{
    protected $signature = 'prisoners:remove-clyde-lay';

    protected $description = 'Remove the Roy Alen Clyde and Wade Greely Lay prisoner entries';

    /** label => name fragments that must all be present (ANDed) */
    private const TARGETS = [
        'Roy Alen Clyde' => ['Roy', 'Clyde'],
        'Wade Greely Lay' => ['Wade', 'Lay'],
    ];

    public function handle(): int
    {
        foreach (self::TARGETS as $label => $fragments) {
            $query = Prisoner::withoutGlobalScopes();
            foreach ($fragments as $fragment) {
                $query->where('name', 'like', '%'.$fragment.'%');
            }
            $matches = $query->get();

            if ($matches->isEmpty()) {
                $this->info("No match for '{$label}' — nothing to remove.");

                continue;
            }

            foreach ($matches as $prisoner) {
                $name = $prisoner->name;
                $id = $prisoner->id;
                $caseCount = $prisoner->cases()->count();

                $prisoner->cases()->delete();
                $prisoner->podcastEpisodes()->delete();
                $prisoner->calendarEntries()->delete();
                $prisoner->delete();

                $this->info("Removed prisoner '{$name}' ({$id}) and {$caseCount} case(s).");
            }
        }

        return self::SUCCESS;
    }
}
