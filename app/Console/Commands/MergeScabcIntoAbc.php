<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Merges the "South Chicago ABC" archive collection into "Anarchist
 * Black Cross". South Chicago is one of many ABC chapters and
 * shouldn't be a separate collection.
 *
 * Idempotent.
 */
final class MergeScabcIntoAbc extends Command {
    protected $signature = 'archive:merge-scabc-into-abc';
    protected $description = 'Merge South Chicago ABC collection into Anarchist Black Cross';

    public function handle(): int {
        $affected = ArchiveRecord::query()
            ->where('collection', 'South Chicago ABC')
            ->update(['collection' => 'Anarchist Black Cross']);

        $this->info("Merged {$affected} records from 'South Chicago ABC' into 'Anarchist Black Cross'.");
        return self::SUCCESS;
    }
}
