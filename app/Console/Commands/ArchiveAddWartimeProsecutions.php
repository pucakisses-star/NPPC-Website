<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Catalogs the National Civil Liberties Bureau's March 1919 compilation
 * "War-Time Prosecutions and Mob Violence" — an index of free-speech, free-press,
 * and peaceful-assembly cases from April 1, 1917 to March 1, 1919. The NCLB was
 * the direct predecessor of the ACLU (founded 1920). The public-domain scan
 * (from the University of Colorado Boulder digital library) is committed at
 * public/pdfs/government-repression/ and referenced by its public /pdfs/ path,
 * matching the other ArchiveRecords.
 *
 * Idempotent — skips if the slug already exists. Run archive:generate-thumbnails
 * afterward to render the page-1 cover thumbnail.
 */
final class ArchiveAddWartimeProsecutions extends Command
{
    protected $signature = 'archive:add-wartime-prosecutions';

    protected $description = 'Add the NCLB 1919 "War-Time Prosecutions and Mob Violence" compilation to the archive';

    public function handle(): int
    {
        $slug = 'war-time-prosecutions-and-mob-violence-1919';

        if (ArchiveRecord::where('slug', $slug)->exists()) {
            $this->info('Archive record already exists, skipping.');

            return self::SUCCESS;
        }

        $record = ArchiveRecord::create([
            'title' => 'War-Time Prosecutions and Mob Violence: Involving the Rights of Free Speech, Free Press and Peaceful Assemblage (April 1, 1917 – March 1, 1919)',
            'slug' => $slug,
            'description' => 'A compilation published by the National Civil Liberties Bureau (the direct '
                .'predecessor of the American Civil Liberties Union, founded 1920) indexing cases of prosecution and '
                .'mob violence directed against the rights of free speech, free press, and peaceful assembly in the '
                .'United States from April 1, 1917 to March 1, 1919 — the World War I and immediate post-war period of '
                .'Espionage Act and Sedition Act enforcement. Compiled from the Bureau\'s correspondence and press '
                .'clippings, the booklet notes it is "by no means a complete record." Published March 1919 from the '
                .'Bureau\'s office at 41 Union Square, New York City. Public-domain scan digitized by the University of '
                .'Colorado Boulder.',
            'record_type' => 'document',
            'source_format' => 'pamphlet',
            'file' => '/pdfs/government-repression/war-time-prosecutions-and-mob-violence-1919.pdf',
            'year' => 1919,
            'publisher' => 'National Civil Liberties Bureau',
            'authors' => 'National Civil Liberties Bureau',
            'collection' => 'Government Repression',
            'subjects' => [
                'Free speech',
                'Free press',
                'Freedom of assembly',
                'World War I',
                'Espionage Act',
                'Sedition Act',
                'Conscientious objectors',
                'Mob violence',
                'Civil liberties',
                'Government Repression',
            ],
            'is_digitized' => true,
            'published' => true,
            'sort_order' => 0,
        ]);

        $this->info("Added archive record: {$record->title} (file {$record->file})");
        $this->comment('Run `php artisan archive:generate-thumbnails` to render the cover thumbnail.');

        return self::SUCCESS;
    }
}
