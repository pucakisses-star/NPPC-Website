<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Scans every PDF in the ArchiveRecord set and classifies each as
 * "OCR / text-searchable" or "image-only / no extractable text" by
 * running `pdftotext` (Poppler) against it and measuring the size
 * of the extracted text.
 *
 * Threshold (tunable via --min-chars=N): files whose first 5 pages
 * yield fewer than 200 stripped characters are flagged as image-only.
 *
 * Requires `pdftotext` (poppler-utils) on the host:
 *   sudo apt install poppler-utils
 *
 *   php artisan archive:audit-pdf-ocr
 *   php artisan archive:audit-pdf-ocr --collection=Scholarship
 *   php artisan archive:audit-pdf-ocr --json > /tmp/ocr-audit.json
 */
final class AuditPdfOcr extends Command {
    protected $signature = 'archive:audit-pdf-ocr
        {--collection= : Only audit one collection}
        {--min-chars=200 : Min extracted-text chars to count as OCR}
        {--json : Output JSON instead of summary table}
        {--list-image-only : Print the path of every image-only PDF}';
    protected $description = 'Classify every archive PDF as text-searchable (OCR) or image-only';

    public function handle(): int {
        if (! $this->binaryAvailable('pdftotext')) {
            $this->error('pdftotext (poppler-utils) is not installed on this host.');
            $this->line('  Debian/Ubuntu: sudo apt install poppler-utils');
            $this->line('  macOS:         brew install poppler');
            return self::FAILURE;
        }

        $minChars = (int) $this->option('min-chars');
        $query = ArchiveRecord::query()->whereNotNull('file')->where('file', 'like', '%.pdf');
        if ($collection = $this->option('collection')) {
            $query->where('collection', $collection);
        }
        $records = $query->orderBy('collection')->orderBy('title')->get();

        $total = $records->count();
        if ($total === 0) {
            $this->warn('No PDF archive records found.');
            return self::SUCCESS;
        }

        $ocr = []; $imageOnly = []; $missing = []; $error = [];
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($records as $r) {
            $bar->advance();
            $path = $this->resolveFilePath($r->file);
            if (! $path || ! is_readable($path)) {
                $missing[] = ['id' => $r->id, 'title' => $r->title, 'file' => $r->file];
                continue;
            }

            // Extract text from first 5 pages only — fast + enough to classify.
            $cmd = sprintf('pdftotext -q -f 1 -l 5 %s - 2>/dev/null', escapeshellarg($path));
            $text = @shell_exec($cmd);
            if ($text === null) {
                $error[] = ['id' => $r->id, 'title' => $r->title, 'file' => $r->file];
                continue;
            }

            $stripped = trim(preg_replace('/\s+/u', ' ', (string) $text));
            $len = mb_strlen($stripped);

            $entry = ['id' => $r->id, 'title' => $r->title, 'file' => $r->file, 'collection' => $r->collection, 'chars' => $len];
            if ($len >= $minChars) {
                $ocr[] = $entry;
            } else {
                $imageOnly[] = $entry;
            }
        }
        $bar->finish();
        $this->newLine(2);

        if ($this->option('json')) {
            $this->line(json_encode([
                'total' => $total,
                'ocr' => count($ocr),
                'image_only' => count($imageOnly),
                'missing_file' => count($missing),
                'extract_error' => count($error),
                'image_only_list' => $imageOnly,
                'missing_list' => $missing,
            ], JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        $pct = fn (int $n) => $total > 0 ? round(($n / $total) * 100, 1) : 0;

        $this->info('=== PDF OCR Audit ===');
        $this->table(['classification', 'count', 'percent'], [
            ['OCR / text-searchable', count($ocr),       $pct(count($ocr)).'%'],
            ['Image-only (no text)',  count($imageOnly), $pct(count($imageOnly)).'%'],
            ['File missing on disk',  count($missing),   $pct(count($missing)).'%'],
            ['pdftotext error',       count($error),     $pct(count($error)).'%'],
            ['TOTAL',                 $total,            '100%'],
        ]);

        // Per-collection breakdown if no filter.
        if (! $this->option('collection')) {
            $byCol = [];
            foreach ($ocr as $r)       { $byCol[$r['collection'] ?? '(none)']['ocr'] = ($byCol[$r['collection'] ?? '(none)']['ocr'] ?? 0) + 1; }
            foreach ($imageOnly as $r) { $byCol[$r['collection'] ?? '(none)']['img'] = ($byCol[$r['collection'] ?? '(none)']['img'] ?? 0) + 1; }
            $rows = [];
            ksort($byCol);
            foreach ($byCol as $col => $counts) {
                $o = $counts['ocr'] ?? 0;
                $i = $counts['img'] ?? 0;
                $t = $o + $i;
                $rows[] = [$col, $o, $i, $t, $t > 0 ? round(($o / $t) * 100).'%' : ''];
            }
            $this->newLine();
            $this->info('Per-collection breakdown:');
            $this->table(['collection', 'OCR', 'image-only', 'total', '% OCR'], $rows);
        }

        if ($this->option('list-image-only') && count($imageOnly)) {
            $this->newLine();
            $this->warn('Image-only PDFs:');
            foreach ($imageOnly as $r) {
                $this->line('  '.$r['file'].'  ('.$r['title'].')');
            }
        }

        if ($missing) {
            $this->newLine();
            $this->warn('Missing files (DB references but no file on disk):');
            foreach (array_slice($missing, 0, 20) as $r) {
                $this->line('  '.$r['file'].'  — '.$r['title']);
            }
            if (count($missing) > 20) {
                $this->line('  ...+'.(count($missing) - 20).' more');
            }
        }

        return self::SUCCESS;
    }

    private function binaryAvailable(string $bin): bool {
        $path = trim((string) @shell_exec('command -v '.escapeshellarg($bin).' 2>/dev/null'));
        return $path !== '';
    }

    private function resolveFilePath(?string $file): ?string {
        if (! $file) return null;
        $clean = ltrim($file, '/');
        $candidates = [
            base_path('public/'.$clean),
            storage_path('app/public/'.$clean),
        ];
        foreach ($candidates as $p) {
            if (is_file($p)) return $p;
        }
        return null;
    }
}
