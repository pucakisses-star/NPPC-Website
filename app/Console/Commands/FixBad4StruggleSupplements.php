<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Two files in public/pdfs/4strugglemag/ were Wayback-Machine HTML wrappers,
 * not real PDFs — the original https://4strugglemag.org/files/... URLs are
 * dead, and curl saved the archive.org viewer page disguised as a .pdf.
 *
 * This command removes the broken file/thumbnail references from those
 * ArchiveRecord rows and marks them as non-digitized, so they hide from
 * the default /archive1-records view but still show when "Include
 * non-digitized records" is toggled.
 */
final class FixBad4StruggleSupplements extends Command {
    protected $signature = 'archive:fix-bad-4struggle-supplements';
    protected $description = 'Clear broken UFF.pdf and Securite-eng-letter.pdf references on archive records';

    public function handle(): int {
        $slugs = [
            'united-freedom-front-pamphlet',
            '4strugglemag-securite-eng',
        ];

        $updated = 0;
        foreach ($slugs as $slug) {
            $r = ArchiveRecord::where('slug', $slug)->first();
            if (! $r) {
                $this->warn("not found: {$slug}");

                continue;
            }
            $r->file = null;
            $r->thumbnail = null;
            $r->is_digitized = false;
            $r->save();
            $updated++;
            $this->info("updated: {$slug}");
        }

        $this->info("Done. Updated {$updated} records.");

        return self::SUCCESS;
    }
}
