<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

/**
 * Hide the freedomarchives-style landing (/archive, formerly /archive1)
 * and the records browser page (/archive-records) from the public nav.
 * The pages still exist and are reachable by direct URL.
 */
final class HideArchivePagesFromNav extends Command {
    protected $signature = 'archive:hide-archive-pages-from-nav';
    protected $description = 'Set show_in_nav=false on the /archive landing and /archive-records pages';

    public function handle(): int {
        $slugs = ['archive', 'archive-records', 'archive1', 'archive1-records'];
        $count = 0;

        foreach ($slugs as $slug) {
            $page = Page::where('slug', $slug)->first();
            if (! $page) {
                continue;
            }
            $page->show_in_nav = false;
            $page->save();
            $this->info("Hid '{$page->title}' (/{$page->slug}) from nav.");
            $count++;
        }

        if ($count === 0) {
            $this->warn('No matching pages found.');
        }

        return self::SUCCESS;
    }
}
