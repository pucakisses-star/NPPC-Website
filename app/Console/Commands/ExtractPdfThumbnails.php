<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Generates a JPEG thumbnail from the first page of every locally-
 * mirrored PDF ArchiveRecord that doesn't already have a thumbnail.
 * Tries pdftoppm (poppler-utils) first, falls back to mutool
 * (mupdf-tools) if pdftoppm isn't installed. Saves to
 * public/thumbnails/<slug>.jpg and writes the relative path into
 * the ArchiveRecord.thumbnail column.
 *
 * Flags:
 *   --force      Re-extract even if the thumbnail file already exists
 *   --limit=N    Only process this many records (0 = all)
 *   --width=N    Output width in pixels (default 600)
 */
final class ExtractPdfThumbnails extends Command {
    protected $signature = 'archive:extract-pdf-thumbnails {--force : Re-extract even if thumbnail exists} {--limit=0 : Cap batch size} {--width=600 : Output width px}';
    protected $description = 'Generate first-page JPEG thumbnails for PDFs that don\'t have a cover image yet';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $limit = (int) $this->option('limit');
        $width = (int) $this->option('width');

        // Detect available extractor
        $extractor = $this->detectExtractor();
        if (! $extractor) {
            $this->error('No PDF→image tool found. Install poppler-utils (pdftoppm) or mupdf-tools (mutool).');

            return self::FAILURE;
        }
        $this->info("Using extractor: {$extractor}");

        $dir = public_path('thumbnails');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $query = ArchiveRecord::query()
            ->where('source_format', 'pdf')
            ->where('file', 'like', '/pdfs/%');
        if (! $force) {
            $query->where(function ($q) {
                $q->whereNull('thumbnail')->orWhere('thumbnail', '');
            });
        }
        $rows = $limit > 0 ? $query->limit($limit)->get() : $query->get();
        $this->info('Candidates: '.$rows->count());

        $done = 0;
        $skipped = 0;
        $failed = 0;
        foreach ($rows as $r) {
            $pdf = public_path(ltrim($r->file, '/'));
            if (! is_file($pdf)) {
                $this->line('SKIP (missing) #'.$r->id.'  '.$r->slug);
                $skipped++;

                continue;
            }
            $thumbRel = '/thumbnails/'.$r->slug.'.jpg';
            $thumbAbs = $dir.DIRECTORY_SEPARATOR.$r->slug.'.jpg';
            if (! $force && is_file($thumbAbs) && filesize($thumbAbs) > 1000) {
                if ($r->thumbnail !== $thumbRel) {
                    $r->thumbnail = $thumbRel;
                    $r->save();
                }
                $skipped++;

                continue;
            }
            if ($this->extract($extractor, $pdf, $thumbAbs, $width)) {
                $r->thumbnail = $thumbRel;
                $r->save();
                $this->info('OK   #'.$r->id.'  '.$r->slug.'  ('.number_format(filesize($thumbAbs) / 1024, 1).' KB)');
                $done++;
            } else {
                $this->error('FAIL #'.$r->id.'  '.$r->slug);
                $failed++;
            }
        }

        $this->info("\nGenerated: {$done}    Skipped: {$skipped}    Failed: {$failed}");

        return self::SUCCESS;
    }

    private function detectExtractor(): ?string {
        foreach (['pdftoppm', 'mutool'] as $tool) {
            $out = [];
            $code = 0;
            @exec('which '.escapeshellarg($tool).' 2>/dev/null', $out, $code);
            if ($code === 0 && ! empty($out)) {
                return $tool;
            }
        }

        return null;
    }

    private function extract(string $tool, string $pdf, string $out, int $width): bool {
        if ($tool === 'pdftoppm') {
            // pdftoppm writes to <prefix>-1.jpg ; we use a temp prefix then rename.
            $prefix = $out.'.tmp';
            $cmd = sprintf(
                'pdftoppm -f 1 -l 1 -jpeg -scale-to-x %d -scale-to-y -1 %s %s 2>/dev/null',
                $width,
                escapeshellarg($pdf),
                escapeshellarg($prefix)
            );
            exec($cmd, $output, $code);
            $candidate = $prefix.'-1.jpg';
            if ($code === 0 && is_file($candidate) && filesize($candidate) > 1000) {
                rename($candidate, $out);

                return true;
            }
            @unlink($candidate);

            return false;
        }
        if ($tool === 'mutool') {
            $cmd = sprintf(
                'mutool draw -F jpg -w %d -o %s %s 1 2>/dev/null',
                $width,
                escapeshellarg($out),
                escapeshellarg($pdf)
            );
            exec($cmd, $output, $code);

            return $code === 0 && is_file($out) && filesize($out) > 1000;
        }

        return false;
    }
}
