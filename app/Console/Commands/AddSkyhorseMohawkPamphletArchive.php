<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Adds the Native American Solidarity Committee's "Free Paul Skyhorse
 * and Richard Mohawk" defense pamphlet (Gonna Rise Again Graphics,
 * ca. 1977; Freedom Archives DOC44 scan) — the source of the two
 * defendants' portraits attached to their prisoner records.
 */
final class AddSkyhorseMohawkPamphletArchive extends Command {
    protected $signature = 'archive:add-skyhorse-mohawk-pamphlet';
    protected $description = 'Add the Free Paul Skyhorse and Richard Mohawk pamphlet to the archive';

    public function handle(): int {
        $file = '/pdfs/freedom-archives/free-paul-skyhorse-and-richard-mohawk-nasc.pdf';
        $payload = [
            'title' => 'Free Paul Skyhorse and Richard Mohawk',
            'description' => 'Native American Solidarity Committee defense pamphlet on the Skyhorse-Mohawk case — the AIM activists held nearly four years awaiting trial in the 1974 George Aird murder before their May 25, 1978 acquittal. Covers the FBI informant Douglas Durham\'s role in the prosecution, the defendants\' biographies with portraits, and the movement\'s analysis of the case as an attack on the American Indian Movement. Gonna Rise Again Graphics, ca. 1977; scanned by the Freedom Archives (DOC44).',
            'file' => $file,
            'thumbnail' => '/thumbnails/free-paul-skyhorse-and-richard-mohawk-nasc.jpg',
            'record_type' => 'document',
            'source_format' => 'pamphlet',
            'collection' => 'Freedom Archives',
            'publisher' => 'Native American Solidarity Committee',
            'year' => 1977,
            'subjects' => ['American Indian Movement', 'Skyhorse-Mohawk case', 'FBI informants', 'Political trials'],
            'is_digitized' => true,
            'published' => true,
        ];

        $existing = ArchiveRecord::query()->where('file', $file)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info('Updated existing record.');
        } else {
            ArchiveRecord::create($payload);
            $this->info('Created record.');
        }

        return self::SUCCESS;
    }
}
