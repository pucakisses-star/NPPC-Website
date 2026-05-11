<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

/**
 * In the "Learn More" nav dropdown, replace the Magazines page link with
 * a link to the Archive (freedomarchives-style) landing.
 *
 * - Removes Magazines from the dropdown (clears parent_id, hides from nav)
 * - Renames the landing page title from "Archive1" → "Archive"
 * - Attaches the landing as a child of "Learn More" and shows it in nav
 */
final class SwapLearnMoreMagazinesForArchive extends Command {
    protected $signature = 'archive:swap-learn-more-magazines-for-archive';
    protected $description = 'Swap Magazines for Archive in the Learn More dropdown';

    public function handle(): int {
        $learnMore = Page::where('slug', 'learn-more')
            ->orWhere('title', 'Learn More')
            ->first();
        if (! $learnMore) {
            $this->error('No "Learn More" page found (looked up by slug=learn-more or title="Learn More").');

            return self::FAILURE;
        }
        $this->info("Found Learn More: id={$learnMore->id} slug={$learnMore->slug}");

        $magazines = Page::where('slug', 'magazines')->first();
        if ($magazines) {
            $magazines->parent_id = null;
            $magazines->show_in_nav = false;
            $magazines->save();
            $this->info("Detached Magazines (/{$magazines->slug}) from Learn More and hid from nav.");
        } else {
            $this->warn('No Magazines page found (slug=magazines).');
        }

        $archive = Page::where('slug', 'archive')->first();
        if (! $archive) {
            $this->error('No Archive page found (slug=archive).');

            return self::FAILURE;
        }
        $archive->title = 'Archive';
        $archive->parent_id = $learnMore->id;
        $archive->show_in_nav = true;
        $archive->save();
        $this->info("Attached Archive (/{$archive->slug}) as child of Learn More and showed in nav.");

        return self::SUCCESS;
    }
}
