<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Registers 101 political-prisoner-focused zines from the South
 * Chicago ABC zine library (southchicagoabc.org) as ArchiveRecord
 * rows.
 *
 * Curated subset only — focused on incarcerated voices, prisoner
 * support campaigns, ABC organizing, prison strikes, solitary
 * confinement, and anti-repression work. Excludes the library's
 * theory, herbalism, fitness, RPG, and general-anarchism material.
 *
 * PDFs are sourced from the .web.pdf / .pdf URLs at
 * southchicagoabc.org/cabc/<slug>/<slug>.(web.)pdf and stored under
 * public/pdfs/south-chicago-abc/.
 *
 * Idempotent — matches by file path.
 */
final class AddSouthChicagoAbcZines extends Command {
    protected $signature = 'archive:add-south-chicago-abc-zines';
    protected $description = 'Register South Chicago ABC political-prisoner zines as ArchiveRecord rows';

    public function handle(): int {
        $jsonPath = database_path('data/south-chicago-abc-zines.json');
        if (! file_exists($jsonPath)) {
            $this->error('Missing data file: '.$jsonPath);
            return self::FAILURE;
        }
        $records = json_decode((string) file_get_contents($jsonPath), true);
        if (! is_array($records)) {
            $this->error('Failed to parse '.$jsonPath);
            return self::FAILURE;
        }

        $added = 0;
        $updated = 0;
        $missing = 0;

        foreach ($records as $rec) {
            $filePath = '/pdfs/south-chicago-abc/'.$rec['file'];

            if (! file_exists(public_path('pdfs/south-chicago-abc/'.$rec['file']))) {
                $this->warn('Missing file on disk: '.$filePath);
                $missing++;
                continue;
            }

            $thumbnailPath = null;
            if (! empty($rec['thumbnail']) && file_exists(public_path('pdfs/south-chicago-abc-thumbnails/'.$rec['thumbnail']))) {
                $thumbnailPath = '/pdfs/south-chicago-abc-thumbnails/'.$rec['thumbnail'];
            }

            $payload = [
                'title' => $rec['title'],
                'description' => $rec['description'] ?? null,
                'authors' => $rec['authors'] ?? null,
                'year' => $rec['year'] ?? null,
                'file' => $filePath,
                'thumbnail' => $thumbnailPath,
                'record_type' => 'document',
                'source_format' => 'pamphlet',
                'collection' => 'South Chicago ABC',
                'publisher' => 'South Chicago Anarchist Black Cross',
                'is_digitized' => true,
                'published' => true,
            ];

            $existing = ArchiveRecord::query()->where('file', $filePath)->first();
            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                ArchiveRecord::create($payload);
                $added++;
            }
        }

        $this->info("Done — added {$added}, updated {$updated}, missing {$missing}.");
        return self::SUCCESS;
    }
}
