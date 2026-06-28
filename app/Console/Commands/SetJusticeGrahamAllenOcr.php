<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Points the "Justice for Ernest Graham & Eugene Allen" archive record at an
 * OCR'd, text-searchable copy of the pamphlet (the Freedom Archives original is
 * an image-only scan with no text layer). The searchable PDF is committed at
 * public/pdfs/freedom-archives/freedom-archives-justice-graham-allen-ocr.pdf;
 * this command repoints the record's file to it. Idempotent (matches by slug).
 */
final class SetJusticeGrahamAllenOcr extends Command
{
    protected $signature = 'archive:set-justice-graham-allen-ocr';

    protected $description = "Point the Justice for Graham & Allen archive record at the OCR'd searchable PDF";

    private const SLUG = 'freedom-archives-justice-graham-allen';

    private const FILE = '/pdfs/freedom-archives/freedom-archives-justice-graham-allen-ocr.pdf';

    public function handle(): int
    {
        if (! is_file(public_path(ltrim(self::FILE, '/')))) {
            $this->warn('Searchable PDF not found at public'.self::FILE.' — setting the path anyway.');
        }

        // Match by slug OR by title — the live record's slug differs from the
        // data-file slug (it was generated from the title), so title is the
        // reliable key. ("Justice for Ernest Graham%" uniquely identifies it;
        // the other Graham/Allen pamphlet is titled "Free Graham and Allen…".)
        $records = ArchiveRecord::where('slug', self::SLUG)
            ->orWhere('title', 'like', 'Justice for Ernest Graham%')
            ->get();

        if ($records->isEmpty()) {
            $this->error('No archive record found for "Justice for Ernest Graham & Eugene Allen".');

            return self::FAILURE;
        }

        foreach ($records as $record) {
            $record->file = self::FILE;
            $record->is_digitized = true;
            $record->save();
            $this->info("Repointed '{$record->title}' (slug: {$record->slug}) to the OCR'd PDF. View: /archive/view/{$record->id}");
        }

        return self::SUCCESS;
    }
}
