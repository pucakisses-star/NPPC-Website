<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Self-host 20 ArchiveRecords that were still pointing at
 * archive.org/details/ URLs. PDFs are now committed under
 * public/pdfs/ia-self-host/ and this command rewrites each
 * record's `file` column from the IA URL to the local path.
 *
 * Matches by IA URL (not slug), so we don't have to guess
 * the exact slug values stored in the DB.
 *
 * Two records intentionally remain on archive.org URLs:
 *
 *   - https://archive.org/details/clayton-van-lydegraf
 *     (122 MB scan that wouldn't survive the per-push size
 *     limit even after ghostscript /printer + 150-DPI downsampling.)
 *
 *   - https://archive.org/details/la-patria-radical-vol-2-no-1-august-1989-compresse
 *     (the IA item has no downloadable PDF — JP2 stack only.)
 *
 * Idempotent — re-runs are safe; only updates records whose `file`
 * column is still the IA URL.
 */
final class SelfHostRemainingIaRecords extends Command {
    protected $signature = 'archive:selfhost-remaining-ia';
    protected $description = 'Repoint 20 archive.org/details/ records at self-hosted /pdfs/ia-self-host/ PDFs';

    public function handle(): int {
        // IA URL substring → local PDF filename under /pdfs/ia-self-host/
        $map = [
            'archive.org/details/armspiritwomansj0000bloc'                              => 'arm-the-spirit-diana-block-2009.pdf',
            'archive.org/details/recruitment-ofc_bombing'                               => 'armed-forces-recruiting-stations-communiques.pdf',
            'archive.org/details/badmoonrisinghow0000ecks'                              => 'bad-moon-rising-weather-underground-jacobs.pdf',
            'archive.org/details/captivenationbla0000berg'                              => 'captive-nation-black-prison-organizing-berger.pdf',
            'archive.org/details/coconspiratorfor0000reve'                              => 'co-conspirator-for-justice-alan-berkman.pdf',
            'archive.org/details/demonstrate-iwd-1983'                                  => 'demonstrate-iwd-1983.pdf',
            'archive.org/details/erosionoflawenfo00unit'                                => 'erosion-of-law-enforcement-intelligence.pdf',
            'archive.org/details/FBI_file_100_459234'                                   => 'fbi-file-gerald-doeden-gino-perente.pdf',
            'archive.org/details/la-patria-radical-april-may-1992'                      => 'la-patria-radical-april-may-1992.pdf',
            'archive.org/details/cabepcc_000044'                                        => 'la-pena-newsletter-october-1980.pdf',
            'archive.org/details/lavenderredliber0000hobs'                              => 'lavender-and-red-hobson.pdf',
            'archive.org/details/lns-755'                                               => 'liberation-news-service-755-1976.pdf',
            'archive.org/details/libertad-update-january-251986'                        => 'libertad-update-january-25-1986.pdf',
            'archive.org/details/libertad-july-august-1987'                             => 'libertad-july-august-1987-pfoc-alt.pdf',
            'archive.org/details/libertad-may-1985'                                     => 'libertad-may-1985-pfoc-alt.pdf',
            'archive.org/details/Libertad_7_May1985_3325'                               => 'libertad-no-7-may-1985-alt-scan.pdf',
            'archive.org/details/Libertad_8_July-August1987_6597'                       => 'libertad-no-8-july-august-1987-alt-scan.pdf',
            'archive.org/details/nofascistusajohn0000moor'                              => 'no-fascist-usa-john-brown-anti-klan-committee.pdf',
            'archive.org/details/ohio-7-defeat-us-imperialism-049'                      => 'ohio-7-defeat-us-imperialism.pdf',
            'archive.org/details/outlawsofamerica0000berg'                              => 'outlaws-of-america-weather-underground-berger.pdf',
        ];

        $updated = 0; $notMatched = 0; $localMissing = 0; $alreadyLocal = 0;
        foreach ($map as $iaSubstring => $filename) {
            $local = public_path("pdfs/ia-self-host/{$filename}");
            if (!file_exists($local)) {
                $this->warn("  local PDF missing: {$filename}");
                $localMissing++;
                continue;
            }
            $r = ArchiveRecord::where('file', 'like', '%'.$iaSubstring.'%')->first();
            if (!$r) {
                $this->warn("  no record with file like '%{$iaSubstring}%'");
                $notMatched++;
                continue;
            }
            if (str_starts_with((string) $r->file, '/pdfs/ia-self-host/')) {
                $alreadyLocal++;
                continue;
            }
            $r->update([
                'file' => "/pdfs/ia-self-host/{$filename}",
                'thumbnail' => null, // force regen
            ]);
            $this->line("  updated: {$r->slug}");
            $updated++;
        }

        $this->info("Done — updated {$updated}, already-local {$alreadyLocal}, no-match {$notMatched}, missing-file {$localMissing}.");
        $this->info('Run `php artisan archive:generate-thumbnails` to regenerate covers for these records.');
        return self::SUCCESS;
    }
}
