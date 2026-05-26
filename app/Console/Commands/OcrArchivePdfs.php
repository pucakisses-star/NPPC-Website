<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Adds an OCR text layer to image-only archive PDFs using ocrmypdf
 * (tesseract + ghostscript).
 *
 * The audit command (archive:audit-pdf-ocr) flags which PDFs are
 * image-only. This command iterates them and runs ocrmypdf in place
 * (write to tmp, atomically rename over the original).
 *
 * Batchable so a multi-thousand-PDF backlog can run over multiple
 * sessions without holding a single terminal open for hours.
 *
 *   sudo apt install ocrmypdf tesseract-ocr poppler-utils ghostscript
 *
 *   php artisan archive:ocr-pdfs                           # dry-run, default limit 10
 *   php artisan archive:ocr-pdfs --collection="Arm The Spirit (Toronto, 1990–1995)" --apply
 *   php artisan archive:ocr-pdfs --limit=50 --apply
 *   php artisan archive:ocr-pdfs --min-chars=200 --apply
 */
final class OcrArchivePdfs extends Command {
    protected $signature = 'archive:ocr-pdfs
        {--collection= : Only process one collection (exact name match)}
        {--limit=10 : Maximum number of PDFs to process this run}
        {--min-chars=200 : Re-detection threshold — files below this many chars are treated as image-only}
        {--language=eng : Tesseract language code (eng, spa, fra, deu, eng+spa, etc.)}
        {--apply : Actually write OCR layers (otherwise dry-run preview only)}
        {--skip-failed : Skip PDFs that fail ocrmypdf instead of stopping}';
    protected $description = 'Add OCR text layer to image-only archive PDFs (batched)';

    public function handle(): int {
        if (! $this->binaryAvailable('ocrmypdf')) {
            $this->error('ocrmypdf is not installed.');
            $this->line('  sudo apt install ocrmypdf tesseract-ocr poppler-utils ghostscript');
            return self::FAILURE;
        }
        if (! $this->binaryAvailable('pdftotext')) {
            $this->error('pdftotext (poppler-utils) is not installed.');
            return self::FAILURE;
        }

        $apply = (bool) $this->option('apply');
        $limit = max(1, (int) $this->option('limit'));
        $minChars = (int) $this->option('min-chars');
        $lang = $this->option('language') ?: 'eng';
        $collection = $this->option('collection');

        $query = ArchiveRecord::query()->whereNotNull('file')->where('file', 'like', '%.pdf');
        if ($collection) {
            $query->where('collection', $collection);
        }
        $records = $query->orderBy('collection')->orderBy('title')->get();
        $this->info('Scanning '.$records->count().' PDF record(s)'.($collection ? ' in collection "'.$collection.'"' : '').' for image-only files...');

        // First pass: detect image-only PDFs.
        $imageOnly = [];
        $bar = $this->output->createProgressBar($records->count());
        $bar->start();
        foreach ($records as $r) {
            $bar->advance();
            $path = $this->resolveFilePath($r->file);
            if (! $path || ! is_readable($path)) continue;
            $text = @shell_exec(sprintf('pdftotext -q -f 1 -l 5 %s - 2>/dev/null', escapeshellarg($path)));
            $len = mb_strlen(trim(preg_replace('/\s+/u', ' ', (string) $text)));
            if ($len < $minChars) {
                $imageOnly[] = ['record' => $r, 'path' => $path];
            }
        }
        $bar->finish();
        $this->newLine(2);

        $totalImageOnly = count($imageOnly);
        if ($totalImageOnly === 0) {
            $this->info('No image-only PDFs found. Nothing to OCR.');
            return self::SUCCESS;
        }

        $batch = array_slice($imageOnly, 0, $limit);
        $this->info(sprintf('Found %d image-only PDF(s). Processing %d in this batch.', $totalImageOnly, count($batch)));
        $this->newLine();

        if (! $apply) {
            foreach ($batch as $entry) {
                $this->line('  [DRY] '.$entry['record']->file.'  ('.$entry['record']->title.')');
            }
            $this->newLine();
            $this->info('(dry-run; re-run with --apply to actually OCR these files)');
            $this->line('Remaining after this batch would be: '.($totalImageOnly - count($batch)));
            return self::SUCCESS;
        }

        $done = 0; $failed = 0; $startedAt = microtime(true);
        foreach ($batch as $i => $entry) {
            $r = $entry['record'];
            $src = $entry['path'];
            $tmp = $src.'.ocr.tmp.pdf';
            $size = filesize($src);
            $this->line(sprintf('[%d/%d] OCRing %s  (%.1f MB)', $i + 1, count($batch), $r->file, $size / 1048576));

            $cmd = sprintf(
                'ocrmypdf --skip-text --rotate-pages --deskew --language %s --jobs 2 --output-type pdf %s %s 2>&1',
                escapeshellarg($lang),
                escapeshellarg($src),
                escapeshellarg($tmp)
            );
            $t0 = microtime(true);
            $output = @shell_exec($cmd);
            $exitOk = is_file($tmp) && filesize($tmp) > 1024;
            $elapsed = microtime(true) - $t0;

            if (! $exitOk) {
                @unlink($tmp);
                $this->warn(sprintf('  FAIL (%.1fs)', $elapsed));
                if ($output) $this->line('  '.trim($output));
                $failed++;
                if ($this->option('skip-failed')) continue;
                $this->error('Stopping. Pass --skip-failed to keep going past failures.');
                return self::FAILURE;
            }

            if (! @rename($tmp, $src)) {
                @unlink($tmp);
                $this->error('  FAIL: could not rename '.$tmp.' over '.$src);
                $failed++;
                continue;
            }
            $done++;
            $this->line(sprintf('  OK   (%.1fs, %.1f MB)', $elapsed, filesize($src) / 1048576));
        }

        $totalElapsed = microtime(true) - $startedAt;
        $remaining = $totalImageOnly - $done;
        $this->newLine();
        $this->info(sprintf('Batch done — OCR\'d %d, failed %d, %.0fs total (avg %.1fs/file).',
            $done, $failed, $totalElapsed, $done > 0 ? $totalElapsed / $done : 0));
        if ($remaining > 0) {
            $this->info('Still image-only: '.$remaining.'. Re-run the same command to continue.');
        } else {
            $this->info('All image-only PDFs in this set are now OCR\'d. Run archive:audit-pdf-ocr to confirm.');
        }

        return self::SUCCESS;
    }

    private function binaryAvailable(string $bin): bool {
        return trim((string) @shell_exec('command -v '.escapeshellarg($bin).' 2>/dev/null')) !== '';
    }

    private function resolveFilePath(?string $file): ?string {
        if (! $file) return null;
        $clean = ltrim($file, '/');
        foreach ([base_path('public/'.$clean), storage_path('app/public/'.$clean)] as $p) {
            if (is_file($p)) return $p;
        }
        return null;
    }
}
