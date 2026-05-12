<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Registers the NYC Anarchist Black Cross U.S. Political Prisoner and
 * Prisoner of War Listing, 5th Edition (April 2011) as an ArchiveRecord.
 * Companion to the April 2026 edition already in the archive.
 */
final class AddNycAbc2011Listing extends Command {
    protected $signature = 'archive:add-nyc-abc-2011-listing';
    protected $description = 'Register the NYC ABC 5th Edition (April 2011) PP/POW listing as an ArchiveRecord';

    public function handle(): int {
        $slug = 'nyc-abc-pppow-listing-5th-april-2011';
        $payload = [
            'title' => 'NYC ABC U.S. Political Prisoner and Prisoner of War Listing — 5th Edition (April 2011)',
            'description' => 'Fifth edition (April 2011) of the New York City Anarchist Black Cross\'s compendium of U.S. political prisoners and prisoners of war, predating the modern monthly format. Includes contact addresses, inmate numbers, charges, and case histories for political prisoners across federal and state systems as of spring 2011 — the BLA/BPP elders, MOVE 9, AIM (Peltier, Standing Deer), Puerto Rican Macheteros, the surviving Resistance Conspiracy / Brink\'s defendants, Green Scare prisoners (Mason, McGowan, Dibee on the run), and contemporary anarchist defendants.',
            'record_type' => 'document',
            'source_format' => 'newsletter',
            'file' => '/pdfs/nyc-abc/nycabc_polprisoner_listing_5th_april-2011.pdf',
            'collection' => 'NYC Anarchist Black Cross',
            'publisher' => 'NYC ABC',
            'year' => 2011,
            'date' => '2011-04-01',
            'subjects' => ['Anarchist Black Cross', 'Political Prisoners', 'Prisoners of War'],
            'is_digitized' => true,
            'published' => true,
        ];

        $existing = ArchiveRecord::where('slug', $slug)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info('RECORD updated: NYC ABC April 2011 listing.');
        } else {
            ArchiveRecord::create(['slug' => $slug] + $payload);
            $this->info('RECORD added: NYC ABC April 2011 listing.');
        }

        return self::SUCCESS;
    }
}
