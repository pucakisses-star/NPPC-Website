<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Catalogs James Barrera's NACCS conference paper "The Political Repression of a
 * Chicano Movement Activist: The Plight of Francisco E. 'Kiko' Martínez" (NACCS
 * Annual Conference Proceedings, 2004) in the site's archive. The open-access
 * PDF (from SJSU ScholarWorks) is committed at public/pdfs/government-repression/
 * and referenced by its public /pdfs/ path, matching the other ArchiveRecords.
 * Idempotent — skips if the slug already exists.
 */
final class ArchiveAddKikoMartinezPaper extends Command
{
    protected $signature = 'archive:add-kiko-martinez-paper';

    protected $description = 'Add the Barrera NACCS paper on Francisco "Kiko" Martínez to the archive';

    public function handle(): int
    {
        $slug = 'political-repression-kiko-martinez-naccs-2004';

        if (ArchiveRecord::where('slug', $slug)->exists()) {
            $this->info('Archive record already exists, skipping.');

            return self::SUCCESS;
        }

        $record = ArchiveRecord::create([
            'title' => 'The Political Repression of a Chicano Movement Activist: The Plight of Francisco E. "Kiko" Martínez',
            'slug' => $slug,
            'description' => 'An academic conference paper by James Barrera (South Texas College) examining the '
                .'decades-long political repression and federal prosecution of Chicano movement attorney and activist '
                .'Francisco E. "Kiko" Martínez, presented in the NACCS Annual Conference Proceedings. Open-access via '
                .'SJSU ScholarWorks / the National Association for Chicana and Chicano Studies Archive.',
            'record_type' => 'document',
            'source_format' => 'article',
            'file' => '/pdfs/government-repression/political-repression-kiko-martinez-naccs-2004.pdf',
            'year' => 2004,
            'publisher' => 'National Association for Chicana and Chicano Studies (NACCS)',
            'authors' => 'James Barrera',
            'collection' => 'Government Repression',
            'subjects' => [
                'Francisco "Kiko" Martínez',
                'Chicano movement',
                'Political repression',
                'Government Repression',
                'COINTELPRO',
            ],
            'is_digitized' => true,
            'published' => true,
            'sort_order' => 0,
        ]);

        $this->info("Added archive record: {$record->title} (file {$record->file})");

        return self::SUCCESS;
    }
}
