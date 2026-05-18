<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Delete the 22 archive records whose source files I could not
 * recover from any alternate online source (vault.fbi.gov,
 * Internet Archive, Wayback Machine, dokumen.pub, libgen, IA
 * search, Anna's Archive, hathitrust, etc.). Per user instruction
 * after #484: if a record's underlying file can't be hosted, the
 * record should not be in the catalog at all.
 *
 * Idempotent — re-running with already-deleted slugs is a no-op.
 */
final class RemoveUnrecoverableArchiveRecords extends Command {
    protected $signature = 'archive:remove-unrecoverable';
    protected $description = 'Delete the 22 archive records whose files could not be sourced from anywhere online';

    public function handle(): int {
        $slugs = [
            // Dead search.freedomarchives.org pages (SF8 record set)
            'sf8-africa-today-on-grand-jury-repression',
            'sf8-free-the-san-francisco-8-la-prison-times',
            'sf8-francisco-torres-on-the-dismissal',
            'sf8-free-the-sf8-flyer-2008-03-05',
            'sf8-bookmark-and-flyer-2008-08-27',
            'sf8-signed-photograph',
            'sf8-legacy-of-torture-sf-bayview-2007',
            'sf8-37-year-old-case-drop-the-charges',
            'sf8-attorneys-respond-to-reopening-sun-reporter',
            // Other dead search.freedomarchives.org pages
            'fa-c23-interview-with-josefina-rodriguez',
            'fa-c325-counterinsurgency-in-the-courtroom-the-resistance-conspiracy-case',
            'fa-c325-international-day-to-stop-violence-against-women-solidarity-statement',
            // Dead Freedom Archives Documents/Finder paths
            'fag-c261-stonewall-means-fight-back-fight-for-lesbian-and-gay-liberation',
            'fag-c261-support-the-chilean-resistance',
            // Other unrecoverable
            'fag-c13-the-object-is-to-win',
            'fag-c237-akwasi-evans-reads-space',
            'fbi-black-panther-party-part-01',
            'fbi-black-panther-party-part-06',
            'loc-alien-anarchists-exclusion-1919',
            'peltier-flawed-justice-leonard-peltier-defense-committee',
            'black-and-pink-black-and-pink-lgbtq-prisoner-resource-list',
        ];

        $deleted = 0;
        foreach ($slugs as $slug) {
            $rec = ArchiveRecord::where('slug', $slug)->first();
            if ($rec) {
                $rec->delete();
                $this->info("Deleted: {$slug}");
                $deleted++;
            }
        }
        $this->line("Done — deleted {$deleted} records.");
        return self::SUCCESS;
    }
}
