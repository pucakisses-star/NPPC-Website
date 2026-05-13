<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Registers two dokumen.pub-hosted PP titles the user explicitly
 * asked for. dokumen.pub is currently in maintenance and direct
 * PDF download URLs couldn't be resolved from this environment,
 * so the records point at the viewer URLs — swap to a direct PDF
 * later if a clean source surfaces.
 */
final class AddDokumenPubRecords extends Command {
    protected $signature = 'archive:add-dokumen-pub-records';
    protected $description = 'Add NYC ABC Illustrated Guide (dokumen.pub edition) + Anarchy Live by Michael Kimble';

    public function handle(): int {
        $entries = [
            [
                'slug' => 'nycabc-us-political-prisoner-and-pow-listing-illustrated-guide',
                'title' => 'NYC Anarchist Black Cross: US Political Prisoner and Prisoner of War Listing — Illustrated Guide to Political Prisoners',
                'description' => 'NYC ABC\'s longstanding illustrated guide to U.S. political prisoners and POWs — short biographies, illustrations, and mailing addresses, used as the standard letter-writing reference in the anarchist prisoner-support movement. dokumen.pub mirror of an edition not represented in the abcf.net archive set.',
                'record_type' => 'document',
                'source_format' => 'pdf',
                'file' => 'https://dokumen.pub/nyc-anarchist-black-cross-us-political-prisoner-and-prisoner-of-war-listing-illustrated-guide-to-political-prisoners.html',
                'collection' => 'NYC ABC Illustrated Guide',
                'publisher' => 'New York City Anarchist Black Cross',
                'authors' => 'NYC Anarchist Black Cross',
                'subjects' => ['NYC ABC', 'Anarchist Black Cross', 'Political Prisoners', 'POW Listing', 'Letter Writing'],
                'is_digitized' => true,
                'published' => true,
            ],
            [
                'slug' => 'michael-kimble-anarchy-live-collected-writings',
                'title' => 'Anarchy Live: The Writings of Anarchist Prisoner Michael Kimble',
                'description' => 'Collected writings of Michael Kimble — Black anarchist prisoner held by the Alabama Department of Corrections since 1987 after defending himself and a friend from a homophobic attack by a known white supremacist. Includes essays on revolutionary organising inside, queer life behind bars, Kuwasi Balagoon, and the Free Alabama Movement.',
                'record_type' => 'document',
                'source_format' => 'pdf',
                'file' => 'https://dokumen.pub/anarchy-live-the-writings-of-anarchist-prisoner-michael-kimble.html',
                'collection' => 'Michael Kimble',
                'authors' => 'Michael Kimble',
                'subjects' => ['Michael Kimble', 'Black Anarchism', 'Queer Liberation', 'Alabama', 'Prisoner Writings'],
                'is_digitized' => true,
                'published' => true,
            ],
        ];

        foreach ($entries as $entry) {
            $existing = ArchiveRecord::where('slug', $entry['slug'])->first();
            if ($existing) {
                $existing->update($entry);
                $this->info("Updated: {$entry['title']}");
            } else {
                ArchiveRecord::create($entry);
                $this->info("Added: {$entry['title']}");
            }
        }

        return self::SUCCESS;
    }
}
