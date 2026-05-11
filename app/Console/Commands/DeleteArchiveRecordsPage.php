<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

/**
 * Removes the freedomarchives-style records browser page. The route,
 * controller method, and view are deleted in code; this command cleans
 * up any leftover Page rows at the old/new slugs.
 */
final class DeleteArchiveRecordsPage extends Command {
    protected $signature = 'archive:delete-archive-records-page';
    protected $description = 'Delete leftover Page rows for /archive1-records and /archive-records';

    public function handle(): int {
        $slugs = ['archive1-records', 'archive-records'];
        $deleted = 0;

        foreach ($slugs as $slug) {
            $page = Page::where('slug', $slug)->first();
            if (! $page) {
                continue;
            }
            $title = $page->title;
            $page->delete();
            $this->info("Deleted page '{$title}' (/{$slug}).");
            $deleted++;
        }

        if ($deleted === 0) {
            $this->info('No matching pages found — nothing to delete.');
        }

        return self::SUCCESS;
    }
}
