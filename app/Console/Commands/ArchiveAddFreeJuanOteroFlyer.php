<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Catalogs the "FREE JUAN OTERO" defense flyer in the site archive. The scan
 * (from an AbeBooks bookseller listing) was cropped to the sheet and run
 * through OCR into a searchable PDF, committed at
 * public/pdfs/government-repression/ with a thumbnail under
 * public/images/archive/government-repression/. Idempotent — skips if the slug
 * already exists.
 */
final class ArchiveAddFreeJuanOteroFlyer extends Command
{
    protected $signature = 'archive:add-free-juan-otero-flyer';

    protected $description = 'Add the "Free Juan Otero" 1973 Bronx defense flyer to the archive';

    public function handle(): int
    {
        $slug = 'free-juan-otero-flyer';

        if (ArchiveRecord::where('slug', $slug)->exists()) {
            $this->info('Archive record already exists, skipping.');

            return self::SUCCESS;
        }

        $record = ArchiveRecord::create([
            'title' => 'Free Juan Otero',
            'slug' => $slug,
            'description' => 'A 1973 movement defense flyer from the Committee to Defend Juan Otero (c/o Rev. Juan '
                .'Otero, 535 Jackson Ave., Bronx, New York). It presents Otero — a Bronx family man, educational '
                .'specialist, and community activist working for equality in the building trades — as the victim of a '
                .'frame-up by South Bronx police, in collaboration with corrupt construction contractors, in '
                .'retaliation for his community organizing: "FRAMED" on two robbery charges with no evidence found in '
                .'his home or car, "CONVICTED" by a jury and sentenced to five years, and calling on the movement that '
                .'freed Angela Davis and the Berrigans and kept Carlos Feliciano out of jail to see that "HE MUST BE '
                .'FREED." Includes a tear-off contribution/volunteer slip. Digitized as a searchable (OCR) PDF.',
            'record_type' => 'document',
            'source_format' => 'flyer',
            'file' => '/pdfs/government-repression/free-juan-otero-flyer.pdf',
            'thumbnail' => '/images/archive/government-repression/free-juan-otero-flyer.jpg',
            'year' => 1973,
            'publisher' => 'Committee to Defend Juan Otero',
            'authors' => 'Committee to Defend Juan Otero',
            'collection' => 'Government Repression',
            'subjects' => [
                'Juan Otero',
                'Puerto Rican movement',
                'Political prisoners',
                'Frame-up',
                'The Bronx',
                'Government Repression',
                '1973',
            ],
            'is_digitized' => true,
            'published' => true,
            'sort_order' => 0,
        ]);

        $this->info("Added archive record: {$record->title} (file {$record->file})");

        return self::SUCCESS;
    }
}
