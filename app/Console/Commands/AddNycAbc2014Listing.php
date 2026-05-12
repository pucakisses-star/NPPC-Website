<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Registers the NYC Anarchist Black Cross U.S. Political Prisoner and
 * Prisoner of War Listing (November 2014 booklet edition) as an ArchiveRecord.
 * Sits between the April 2011 5th edition and the modern monthly format that
 * culminated in the April 2026 edition — both already in the archive.
 */
final class AddNycAbc2014Listing extends Command {
    protected $signature = 'archive:add-nyc-abc-2014-listing';
    protected $description = 'Register the NYC ABC November 2014 PP/POW booklet listing as an ArchiveRecord';

    public function handle(): int {
        $slug = 'nyc-abc-pppow-listing-nov-2014';
        $payload = [
            'title' => 'NYC ABC U.S. Political Prisoner and Prisoner of War Listing — November 2014',
            'description' => 'November 2014 booklet edition of the New York City Anarchist Black Cross\'s compendium of U.S. political prisoners and prisoners of war. Provides contact addresses, inmate numbers, charges, sentences, and case histories for political prisoners held in federal and state custody as of late 2014 — BLA/BPP elders, MOVE 9, AIM (Peltier), Puerto Rican independentistas, Macheteros, surviving Resistance Conspiracy / Brink\'s defendants, Green Scare prisoners, anti-imperialist and anarchist defendants. Bridge edition between the April 2011 5th-edition booklet and the later monthly listing format.',
            'record_type' => 'document',
            'source_format' => 'newsletter',
            'file' => '/pdfs/nyc-abc/nycabc-pppow-listing-nov-2014.pdf',
            'collection' => 'NYC Anarchist Black Cross',
            'publisher' => 'NYC ABC',
            'year' => 2014,
            'date' => '2014-11-01',
            'subjects' => ['Anarchist Black Cross', 'Political Prisoners', 'Prisoners of War'],
            'is_digitized' => true,
            'published' => true,
        ];

        $existing = ArchiveRecord::where('slug', $slug)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info('RECORD updated: NYC ABC November 2014 listing.');
        } else {
            ArchiveRecord::create(['slug' => $slug] + $payload);
            $this->info('RECORD added: NYC ABC November 2014 listing.');
        }

        return self::SUCCESS;
    }
}
