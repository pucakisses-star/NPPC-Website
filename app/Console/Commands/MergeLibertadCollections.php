<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Consolidates multiple Libertad-related collections into a single
 * "Libertad" bucket.
 *
 * Idempotent.
 */
final class MergeLibertadCollections extends Command {
    protected $signature = 'archive:merge-libertad';
    protected $description = 'Merge all Libertad-related collections into a single "Libertad" collection';

    public function handle(): int {
        $target = 'Libertad';
        $sources = [
            'Movimiento de Liberación Nacional Puertorriqueño — Libertad',
            'National Committee to Free Puerto Rican POWs — Libertad',
        ];

        $affected = ArchiveRecord::query()
            ->whereIn('collection', $sources)
            ->update(['collection' => $target]);

        $this->info("Merged {$affected} records into '{$target}'.");
        return self::SUCCESS;
    }
}
