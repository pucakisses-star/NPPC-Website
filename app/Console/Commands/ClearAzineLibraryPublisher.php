<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Clears the publisher field on archive records whose publisher is the
 * azinelibrary.org-attribution string. The string was rendering as a
 * "Publishers" chip on /archive1-records cards, which isn't useful as a tag.
 */
final class ClearAzineLibraryPublisher extends Command {
    protected $signature = 'archive:clear-azine-publisher';
    protected $description = 'Set publisher=NULL on records whose publisher is "Anarchist Zine Library (azinelibrary.org)"';

    public function handle(): int {
        $count = ArchiveRecord::where('publisher', 'Anarchist Zine Library (azinelibrary.org)')
            ->update(['publisher' => null]);

        $this->info("Cleared publisher on {$count} records.");

        return self::SUCCESS;
    }
}
