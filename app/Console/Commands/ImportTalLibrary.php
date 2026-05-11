<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Imports the curated set of US-political-prisoner-related articles from
 * theanarchistlibrary.org. The metadata (title, author, year, topics) was
 * scraped from each article's TAL page; the PDFs come from TAL's per-article
 * /library/<slug>.pdf endpoint.
 */
final class ImportTalLibrary extends Command {
    protected $signature = 'archive:import-tal {--dry : preview only}';
    protected $description = 'Import US-political-prisoner articles from theanarchistlibrary.org';

    public function handle(): int {
        $path = database_path('data/tal-meta.json');
        if (! is_file($path)) {
            $this->error("Source JSON not found at {$path}");

            return self::FAILURE;
        }
        $items = json_decode(file_get_contents($path), true);
        if (! is_array($items)) {
            $this->error('Could not parse tal-meta.json');

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry');
        $created = 0;
        $updated = 0;
        $missing = 0;
        $sort = 400;

        foreach ($items as $r) {
            $slug = (string) ($r['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            $pdfRel = '/pdfs/tal/'.$slug.'.pdf';
            $pdfPath = public_path('pdfs/tal/'.$slug.'.pdf');
            if (! is_file($pdfPath)) {
                $missing++;
                $this->warn("skip (pdf missing): {$slug}");

                continue;
            }

            $thumbRel = is_file(public_path('images/archive/tal/'.$slug.'-cover.jpg'))
                ? '/images/archive/tal/'.$slug.'-cover.jpg'
                : null;

            $title = $r['title'] ?? $slug;
            $author = $r['author'] ?? null;
            $tags = $r['tags'] ?? [];
            $description = trim((string) ($r['description'] ?? ''));
            $year = $r['year'] ?? null;

            $payload = [
                'title' => $title,
                'description' => ($description !== '' ? $description.' ' : '').'Sourced from theanarchistlibrary.org.',
                'record_type' => 'document',
                'source_format' => 'article',
                'file' => $pdfRel,
                'thumbnail' => $thumbRel,
                'year' => $year,
                'collection' => 'The Anarchist Library',
                'publisher' => 'The Anarchist Library',
                'authors' => $author,
                'subjects' => array_values(array_unique(array_filter(array_merge(['Political Prisoners'], $tags)))),
                'is_digitized' => true,
                'published' => true,
                'sort_order' => $sort++,
            ];

            $archiveSlug = 'tal-'.$slug;

            if ($dry) {
                $this->info("would import: {$archiveSlug} -- {$title}");
                $created++;

                continue;
            }

            $existing = ArchiveRecord::where('slug', $archiveSlug)->first();
            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                ArchiveRecord::create(['slug' => $archiveSlug] + $payload);
                $created++;
            }
        }

        $this->info("Done. Created={$created} Updated={$updated} MissingPdf={$missing}");

        return self::SUCCESS;
    }
}
