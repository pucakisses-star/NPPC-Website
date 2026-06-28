<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Adds the FOX 2 Detroit news report on the Waterford, Michigan automated
 * license-plate-reader (ALPR) vandalism case to the archive, as a searchable
 * PDF clipping committed at public/pdfs/news/. Idempotent (matches by slug).
 */
final class AddWaterfordAlprArchiveRecord extends Command
{
    protected $signature = 'archive:add-waterford-alpr-article';

    protected $description = 'Add the FOX 2 Detroit Waterford ALPR-vandalism article to the archive';

    private const SLUG = 'license-plate-reader-vandalism-waterford';

    public function handle(): int
    {
        $file = public_path('pdfs/news/'.self::SLUG.'.pdf');
        if (! is_file($file)) {
            $this->warn('PDF not found at public/pdfs/news/'.self::SLUG.'.pdf — registering the record anyway.');
        }

        $record = ArchiveRecord::updateOrCreate(
            ['slug' => self::SLUG],
            [
                'title' => 'Man charged with license plate reader camera vandalism in Waterford',
                'description' => 'News report on the arrest of Spencer Anderson, 24, of Clarkston, Michigan, '
                    .'charged with three felony counts of malicious destruction of police property for damaging '
                    .'automated license plate readers (ALPRs) in Waterford Township — one of the cameras he '
                    .'allegedly damaged helped police identify him. Originally published by FOX 2 Detroit, '
                    .'March 3, 2026. Source: '
                    .'https://www.fox2detroit.com/news/license-plate-reader-helps-lead-license-plate-reader-vandal-waterford',
                'record_type' => 'document',
                'source_format' => 'article',
                'file' => '/pdfs/news/'.self::SLUG.'.pdf',
                'publisher' => 'FOX 2 Detroit',
                'date' => '2026-03-03',
                'year' => 2026,
                'subjects' => ['Surveillance', 'Automated license plate readers', 'Policing'],
                'is_digitized' => true,
                'published' => true,
            ],
        );

        $this->info("Archive record ready: {$record->title}");
        $this->info('View: /archive/view/'.$record->id);

        return self::SUCCESS;
    }
}
