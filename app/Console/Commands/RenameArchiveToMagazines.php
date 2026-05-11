<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

/**
 * Rename the /archive page's title from "Archive" to "Magazines".
 * Slug stays "archive" so the URL doesn't break.
 */
final class RenameArchiveToMagazines extends Command {
    protected $signature = 'archive:rename-archive-to-magazines';
    protected $description = 'Set the /archive page title to "Magazines"';

    public function handle(): int {
        $page = Page::where('slug', 'archive')->first();
        if (! $page) {
            $this->error('Page with slug "archive" not found.');

            return self::FAILURE;
        }

        $old = $page->title;
        $page->title = 'Magazines';
        $page->save();

        $this->info("Updated title: '{$old}' → '{$page->title}' (slug still '{$page->slug}', URL still /archive)");

        return self::SUCCESS;
    }
}
