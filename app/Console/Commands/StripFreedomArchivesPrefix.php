<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Strips the "Freedom Archives — " (and similar) prefix from the
 * `collection` column on ArchiveRecord rows.
 *
 * Rationale: many collections were prefixed with "Freedom Archives"
 * because that's where the scans were sourced from, but the prefix
 * is noise when browsing the archive — what users want to see is
 * "Black Panther Party", not "Freedom Archives — Black Panther Party".
 *
 * Records whose collection is JUST "Freedom Archives" (no suffix)
 * are left alone — there's no other content to fall back to.
 *
 * Idempotent.
 */
final class StripFreedomArchivesPrefix extends Command {
    protected $signature = 'archive:strip-freedom-archives-prefix';
    protected $description = 'Strip "Freedom Archives — " prefix from collection names';

    public function handle(): int {
        $patterns = [
            // "Freedom Archives — X", "Freedom Archives - X", "Freedom Archives – X"
            '/^Freedom Archives\s*[—\-–]\s*(.+)$/u',
            // "Freedom Archives' X" or "Freedom Archives's X"
            "/^Freedom Archives'?s?\s+(.+)$/u",
            // "Freedom Archives X" (must have at least one word after, and not be a sentence)
            '/^Freedom Archives\s+([A-Z][A-Za-z0-9 &]{0,80})$/u',
        ];

        $changed = 0;
        $skipped = 0;
        $records = ArchiveRecord::query()
            ->whereNotNull('collection')
            ->where('collection', 'like', 'Freedom Archives%')
            ->get();

        foreach ($records as $r) {
            $orig = $r->collection;

            if (trim($orig) === 'Freedom Archives') {
                $skipped++;
                continue;
            }

            $new = $orig;
            foreach ($patterns as $p) {
                if (preg_match($p, $orig, $m)) {
                    $new = trim($m[1]);
                    break;
                }
            }

            if ($new !== $orig && $new !== '') {
                $r->collection = $new;
                $r->save();
                $this->line("  {$orig}  →  {$new}");
                $changed++;
            } else {
                $skipped++;
            }
        }

        $this->info("Done — collections renamed: {$changed}, left alone: {$skipped}.");
        return self::SUCCESS;
    }
}
