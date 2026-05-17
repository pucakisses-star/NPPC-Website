<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Fix-up for #480: the original SelfHostExternalArchiveRecords used
 * the wrong slugs for the two dokumen.pub records — they were
 *   nycabc-us-political-prisoner-and-pow-listing-illustrated-guide
 *   michael-kimble-anarchy-live-collected-writings
 * (not nyc-abc-illustrated-guide / anarchy-live-michael-kimble).
 *
 * Result: the original external-URL records still exist; my command
 * created two duplicate records with different slugs.
 *
 * This command:
 *   1. Repoints the two correct-slug records to local file paths
 *   2. Deletes the two duplicates created in #480
 *
 * Idempotent.
 */
final class FixDokumenPubSelfHostSlugs extends Command {
    protected $signature = 'archive:fix-dokumen-self-host-slugs';
    protected $description = 'Fix #480 — repoint dokumen.pub records using correct slugs, delete duplicates';

    public function handle(): int {
        // 1. NYC ABC Illustrated Guide
        $real = ArchiveRecord::where('slug', 'nycabc-us-political-prisoner-and-pow-listing-illustrated-guide')->first();
        if ($real) {
            $real->update(['file' => '/pdfs/abc/nyc-abc-illustrated-guide-political-prisoners-v19-3-2026.pdf']);
            $this->info('Repointed: nycabc-us-political-prisoner-and-pow-listing-illustrated-guide → /pdfs/abc/');
        } else {
            $this->warn('Original NYC ABC record not found at expected slug.');
        }
        $dup = ArchiveRecord::where('slug', 'nyc-abc-illustrated-guide')->first();
        if ($dup) {
            $dup->delete();
            $this->info('Deleted duplicate: nyc-abc-illustrated-guide');
        }

        // 2. Michael Kimble — Anarchy Live
        $real = ArchiveRecord::where('slug', 'michael-kimble-anarchy-live-collected-writings')->first();
        if ($real) {
            $real->update(['file' => '/pdfs/anarchist-prisoners/anarchy-live-writings-of-michael-kimble.pdf']);
            $this->info('Repointed: michael-kimble-anarchy-live-collected-writings → /pdfs/anarchist-prisoners/');
        } else {
            $this->warn('Original Michael Kimble record not found at expected slug.');
        }
        $dup = ArchiveRecord::where('slug', 'anarchy-live-michael-kimble')->first();
        if ($dup) {
            $dup->delete();
            $this->info('Deleted duplicate: anarchy-live-michael-kimble');
        }

        $this->line('Done.');
        return self::SUCCESS;
    }
}
