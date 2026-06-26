<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Removes the activist nicknames (the "AKA" line) from the Turtle Island
 * Liberation Front defendants so only their normal names show. Clears the aka
 * field on the five TILF records (matched by slug, plus any record whose
 * description/affiliation references Turtle Island). Idempotent.
 */
final class ClearTurtleIslandAkas extends Command
{
    protected $signature = 'prisoners:clear-turtle-island-akas';

    protected $description = 'Remove the nicknames (aka) from the Turtle Island Liberation Front defendants';

    private const SLUGS = ['micah-legnon', 'audrey-carroll', 'zachary-page', 'dante-gaffield', 'tina-lai'];

    public function handle(): int
    {
        $prisoners = Prisoner::withoutGlobalScopes()
            ->whereIn('slug', self::SLUGS)
            ->orWhere('description', 'like', '%Turtle Island%')
            ->orWhere('affiliation', 'like', '%Turtle Island%')
            ->get();

        if ($prisoners->isEmpty()) {
            $this->warn('No Turtle Island records found — nothing to change.');

            return self::SUCCESS;
        }

        $cleared = 0;
        foreach ($prisoners as $p) {
            if ($p->aka === null || $p->aka === '') {
                $this->line("{$p->name}: no nickname — already clean.");

                continue;
            }

            $this->info("{$p->name}: removed aka \"{$p->aka}\".");
            $p->aka = null;
            $p->save();
            $cleared++;
        }

        $this->info("\nDone. Cleared nicknames on {$cleared} record(s).");

        return self::SUCCESS;
    }
}
