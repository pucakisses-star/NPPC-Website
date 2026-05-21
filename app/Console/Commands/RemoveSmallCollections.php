<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Nulls out the `collection` field on ArchiveRecord rows where the
 * collection value has fewer than 3 records assigned to it. The PDFs
 * themselves are untouched — only the collection tag is removed.
 */
final class RemoveSmallCollections extends Command {
    protected $signature = 'archive:remove-small-collections {--min=3 : Minimum number of records a collection must have to be kept}';
    protected $description = 'Null out collections with fewer than N records';

    public function handle(): int {
        $min = (int) $this->option('min');

        $smallCollections = DB::table('archive_records')
            ->select('collection', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('collection')
            ->groupBy('collection')
            ->havingRaw('COUNT(*) < ?', [$min])
            ->pluck('cnt', 'collection');

        if ($smallCollections->isEmpty()) {
            $this->info('No collections with fewer than '.$min.' records.');
            return self::SUCCESS;
        }

        $totalRecords = 0;
        foreach ($smallCollections as $name => $cnt) {
            $this->line("  {$cnt}× {$name}");
            $totalRecords += $cnt;
        }

        $names = $smallCollections->keys()->all();
        $affected = ArchiveRecord::query()
            ->whereIn('collection', $names)
            ->update(['collection' => null]);

        $this->info("Done — removed ".count($names)." small collections ({$affected} records reset to NULL collection).");
        return self::SUCCESS;
    }
}
