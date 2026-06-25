<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use Illuminate\Console\Command;

/**
 * Removes the "NPPC Editorial" author, reassigning any of its articles to the
 * existing "National Political Prisoner Coalition" author first. Deletes the
 * old author only after a successful reassignment, and never if the target
 * author is missing (so articles can't be orphaned). Idempotent.
 */
final class RemoveNppcEditorialAuthor extends Command
{
    protected $signature = 'authors:remove-nppc-editorial';

    protected $description = 'Remove the NPPC Editorial author and move its articles to the National Political Prisoner Coalition author';

    public function handle(): int
    {
        $editorial = Author::where('name', 'NPPC Editorial')->first();
        if (! $editorial) {
            $this->info('No "NPPC Editorial" author found — nothing to do.');

            return self::SUCCESS;
        }

        $target = Author::where('name', 'National Political Prisoner Coalition')->first()
            ?? Author::where('name', 'like', '%National Political Prisoner Coalition%')->first();

        if (! $target) {
            $this->error('Target author "National Political Prisoner Coalition" not found — aborting so articles are not orphaned.');

            return self::FAILURE;
        }

        if ($target->id === $editorial->id) {
            $this->warn('Editorial and target resolve to the same author — nothing to do.');

            return self::SUCCESS;
        }

        $moved = Article::withoutGlobalScopes()
            ->where('author_id', $editorial->id)
            ->update(['author_id' => $target->id]);

        $this->info("Reassigned {$moved} article(s) from \"{$editorial->name}\" to \"{$target->name}\".");

        $editorial->delete();
        $this->info("Deleted author \"NPPC Editorial\". View target: {$target->name} ({$target->articles()->count()} article(s)).");

        return self::SUCCESS;
    }
}
