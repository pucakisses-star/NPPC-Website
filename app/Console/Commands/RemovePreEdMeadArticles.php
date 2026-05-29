<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

/**
 * Permanently delete every news article published BEFORE the
 * "Remembering Ed Mead" memorial (published_at < 2023-11-26). On the
 * live site these are ~47 imported Civil Liberties Defense Center
 * articles from 2012–2023. The Ed Mead memorial itself (dated exactly
 * 2023-11-26) and everything after it are kept.
 *
 * This is a HARD delete — the Article model has no soft-deletes, so the
 * rows are gone for good (recoverable only from a database backup).
 * Associated image files on disk are NOT removed (they may be shared or
 * remote); only the database records and their tag links are deleted.
 *
 * Safe by default: a dry run that lists what WOULD be deleted. Pass
 * --confirm to actually delete. Idempotent.
 */
final class RemovePreEdMeadArticles extends Command {
    protected $signature = 'articles:remove-pre-ed-mead {--confirm : Actually delete (omit for a dry run)}';
    protected $description = 'Permanently delete news articles published before the Ed Mead memorial (2023-11-26)';

    private const CUTOFF    = '2023-11-26 00:00:00';
    private const KEEP_SLUG = 'remembering-ed-mead-george-jackson-brigade-political-prisoner-memorial';

    public function handle(): int {
        $articles = Article::whereNotNull('published_at')
            ->where('published_at', '<', self::CUTOFF)
            ->where('slug', '!=', self::KEEP_SLUG)
            ->orderBy('published_at')
            ->get();

        $count = $articles->count();
        if ($count === 0) {
            $this->info('No articles found before '.self::CUTOFF.'. Nothing to do.');
            return self::SUCCESS;
        }

        $live = (bool) $this->option('confirm');
        $this->warn(($live ? 'DELETING' : 'DRY RUN — would delete')." {$count} article(s) published before ".self::CUTOFF.':');
        foreach ($articles as $a) {
            $this->line('  '.optional($a->published_at)->format('Y-m-d').'  '.$a->title);
        }

        if (! $live) {
            $this->newLine();
            $this->info("Dry run only — nothing deleted. Re-run with --confirm to permanently delete these {$count} article(s).");
            return self::SUCCESS;
        }

        $deleted = 0;
        foreach ($articles as $a) {
            $a->tags()->detach();   // drop Spatie tag pivot rows so none are orphaned
            $a->delete();
            $deleted++;
        }

        $this->info("\nPermanently deleted {$deleted} article(s). (Image files on disk were left untouched.)");
        return self::SUCCESS;
    }
}
