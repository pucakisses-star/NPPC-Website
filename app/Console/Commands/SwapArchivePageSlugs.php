<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

/**
 * Moves the "Magazines" page from /archive to /magazines, then promotes
 * the freedomarchives-style landing from /archive1 to /archive. Also
 * rewrites any /archive1-records links in the promoted page's body.
 */
final class SwapArchivePageSlugs extends Command {
    protected $signature = 'archive:swap-archive-page-slugs';
    protected $description = 'Move Magazines /archive → /magazines and promote /archive1 → /archive';

    public function handle(): int {
        $magazines = Page::where('slug', 'archive')->first();
        $landing = Page::where('slug', 'archive1')->first();

        if (! $magazines) {
            $this->warn('No page found at slug "archive" (Magazines). Continuing.');
        }
        if (! $landing) {
            $this->error('No page found at slug "archive1". Nothing to promote.');

            return self::FAILURE;
        }

        if (Page::where('slug', 'magazines')->where('id', '!=', $magazines?->id)->exists()) {
            $this->error('A different page already occupies slug "magazines". Aborting.');

            return self::FAILURE;
        }

        if ($magazines) {
            $magazines->slug = 'magazines';
            $magazines->save();
            $this->info("Moved '{$magazines->title}' to /{$magazines->slug}");
        }

        $landing->slug = 'archive';
        if (is_string($landing->body) && str_contains($landing->body, '/archive1-records')) {
            $landing->body = str_replace('/archive1-records', '/archive-records', $landing->body);
            $this->info('Rewrote /archive1-records links in page body.');
        }
        $landing->save();
        $this->info("Promoted '{$landing->title}' to /{$landing->slug}");

        return self::SUCCESS;
    }
}
