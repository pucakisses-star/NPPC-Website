<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;

/**
 * Removes the curated external organisation links that were seeded into the
 * dashboard newswire. They were evergreen resource pages (Amnesty, HRW, CPJ,
 * ACLU, …), not dated events, so they don't belong in the feed. This deletes
 * exactly those rows, matched by URL, and leaves any other DashboardLinks
 * (manually curated in /admin) untouched. Safe to run if they were never added
 * — it simply removes zero rows.
 *
 * Run on the server: php artisan newswire:remove-links
 */
final class RemoveNewswireLinks extends Command {
    protected $signature = 'newswire:remove-links';
    protected $description = 'Remove the curated external links that were seeded into the dashboard newswire';

    /** The exact URLs that were seeded into the newswire. */
    private array $urls = [
        'https://cpj.org/imprisoned/',
        'https://www.amnesty.org/en/latest/',
        'https://www.hrw.org/world-report',
        'https://www.frontlinedefenders.org/',
        'https://pen.org/',
        'https://rsf.org/en',
        'https://www.aclu.org/',
        'https://freedom.press/',
        'https://ccrjustice.org/',
        'https://www.nlg.org/',
        'https://www.prisonpolicy.org/',
        'https://www.sentencingproject.org/',
    ];

    public function handle(): int {
        $deleted = DashboardLink::whereIn('url', $this->urls)->delete();

        $this->info("Removed {$deleted} newswire link(s).");

        return self::SUCCESS;
    }
}
