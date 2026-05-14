<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Merges every ABC-themed ArchiveRecord collection into a single
 * "Anarchist Black Cross" collection. Records keep their city /
 * chapter context in subjects + slug, but their `collection`
 * field is normalized.
 *
 * Matches any collection whose name contains "Anarchist Black
 * Cross" or "ABC" (with a NYC/Boston/Austin/Philly prefix or
 * ABCF wherever) — also "Freedom Archives — Anarchist Black
 * Cross". Dry-run by default; --apply writes.
 */
final class MergeAbcCollections extends Command {
    protected $signature = 'archive:merge-abc-collections {--apply : Actually write the change}';
    protected $description = 'Merge all ABC-themed collections into a single "Anarchist Black Cross" collection';

    public function handle(): int {
        $apply = (bool) $this->option('apply');
        $target = 'Anarchist Black Cross';

        // Match collections by regex. "ABC" alone is too broad; we
        // require it appears as the start of the collection name or
        // after a separator, or the full phrase "Anarchist Black Cross".
        $pattern = '/^(NYC ABC|Boston ABC|Austin ABC|Philly ABC|ABCF|Anarchist Black Cross)\b|Anarchist Black Cross|^Freedom Archives — Anarchist Black Cross$/i';

        $rows = ArchiveRecord::query()
            ->whereNotNull('collection')
            ->where('collection', '!=', $target)
            ->get(['id', 'slug', 'title', 'collection']);

        $matches = [];
        foreach ($rows as $r) {
            if (preg_match($pattern, (string) $r->collection)) {
                $matches[] = $r;
            }
        }

        // Summarize current collection names being merged
        $from = [];
        foreach ($matches as $r) {
            $from[$r->collection] = ($from[$r->collection] ?? 0) + 1;
        }
        arsort($from);
        $this->line('Collections to be merged into "'.$target.'":');
        foreach ($from as $c => $n) {
            $this->info('  '.str_pad((string) $n, 5, ' ', STR_PAD_LEFT).'  '.$c);
        }
        $this->info('Total rows: '.count($matches));

        if (! $apply) {
            $this->info('(dry-run; re-run with --apply to write)');

            return self::SUCCESS;
        }

        $updated = 0;
        foreach ($matches as $r) {
            $r->collection = $target;
            $r->save();
            $updated++;
        }
        $this->info('Updated '.$updated.' rows.');

        return self::SUCCESS;
    }
}
