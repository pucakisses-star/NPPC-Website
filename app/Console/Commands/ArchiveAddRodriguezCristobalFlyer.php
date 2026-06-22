<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Catalogs the "Murdered by the U.S. Government: Ángel Rodríguez Cristóbal"
 * solidarity flyer in the site's archive. The scanned PDF (from the Freedom
 * Archives, DOC41) is committed at public/pdfs/government-repression/ and
 * referenced by its public /pdfs/ path, matching the other ArchiveRecords.
 * Idempotent — skips if the slug already exists.
 */
final class ArchiveAddRodriguezCristobalFlyer extends Command
{
    protected $signature = 'archive:add-rodriguez-cristobal-flyer';

    protected $description = 'Add the Ángel Rodríguez Cristóbal "Murdered by the U.S. Government" flyer to the archive';

    public function handle(): int
    {
        $slug = 'angel-rodriguez-cristobal-murdered-flyer';

        if (ArchiveRecord::where('slug', $slug)->exists()) {
            $this->info('Archive record already exists, skipping.');

            return self::SUCCESS;
        }

        $record = ArchiveRecord::create([
            'title' => 'Murdered by the U.S. Government: Ángel Rodríguez Cristóbal',
            'slug' => $slug,
            'description' => 'A movement solidarity flyer protesting the November 1979 death of Puerto Rican '
                .'independence and socialist activist Ángel Rodríguez Cristóbal in the federal prison at Tallahassee, '
                .'Florida. It condemns his death — officially ruled a suicide — as a political killing by the U.S. '
                .'government, and ties it to the Vieques struggle and the wider movement for Puerto Rican independence. '
                .'Scanned from the Freedom Archives.',
            'record_type' => 'document',
            'source_format' => 'flyer',
            'file' => '/pdfs/government-repression/angel-rodriguez-cristobal-murdered-flyer.pdf',
            'year' => 1979,
            'publisher' => 'Freedom Archives',
            'collection' => 'Government Repression',
            'subjects' => [
                'Ángel Rodríguez Cristóbal',
                'Vieques',
                'Puerto Rican independence',
                'Political prisoners',
                'Government Repression',
                '1979',
            ],
            'is_digitized' => true,
            'published' => true,
            'sort_order' => 0,
        ]);

        $this->info("Added archive record: {$record->title} (file {$record->file})");

        return self::SUCCESS;
    }
}
