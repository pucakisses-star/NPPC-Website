<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Generate thumbnail images for ArchiveRecord rows that don't have one.
 *
 *   - For records whose `file` points at archive.org (details/ or download/),
 *     fetch the IA thumbnail service at /services/img/{identifier}.
 *   - For records whose `file` is a self-hosted PDF under /pdfs/..., render
 *     page 1 with pdftoppm and store it.
 *   - Audio / video / external-host records without a usable cover are
 *     skipped (the front-end already falls back to a type SVG).
 *
 * Stores results at storage/app/public/archive-thumbnails/{slug}.jpg and
 * sets `thumbnail` = "archive-thumbnails/{slug}.jpg" (resolved via
 * Storage::url() by the model).
 *
 * Idempotent — skips records that already have a thumbnail unless --force.
 */
final class GenerateArchiveThumbnails extends Command {
    protected $signature = 'archive:generate-thumbnails {--force : Regenerate even if a thumbnail already exists} {--limit=0 : Cap how many records to process}';
    protected $description = 'Generate page-1 thumbnails for ArchiveRecord rows (IA service + local PDF render)';

    private string $thumbDir;

    public function handle(): int {
        $this->thumbDir = storage_path('app/public/archive-thumbnails');
        if (!is_dir($this->thumbDir)) {
            mkdir($this->thumbDir, 0755, true);
        }

        $query = ArchiveRecord::query();
        if (!$this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('thumbnail')->orWhere('thumbnail', '');
            });
        }

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $total = (clone $query)->count();
        $this->info("Processing {$total} record(s)...");

        $generated = 0; $skipped = 0; $failed = 0;
        foreach ($query->cursor() as $record) {
            $rel = $this->generateFor($record);
            if ($rel === null) {
                $skipped++;
                continue;
            }
            if ($rel === false) {
                $failed++;
                $this->warn("  FAILED: {$record->slug} ({$record->file})");
                continue;
            }
            $record->update(['thumbnail' => $rel]);
            $generated++;
            if ($generated % 25 === 0) {
                $this->line("  ... generated {$generated} so far");
            }
        }

        $this->info("Done — generated {$generated}, skipped {$skipped}, failed {$failed}.");
        return self::SUCCESS;
    }

    /**
     * Returns the storage-relative path of the generated thumbnail,
     * null if the record was skipped (e.g. audio/video/no file),
     * or false if generation was attempted and failed.
     */
    private function generateFor(ArchiveRecord $r): string|null|false {
        $file = $r->file;
        if (!$file) return null;

        $dest = $this->thumbDir.'/'.$r->slug.'.jpg';
        $rel = 'archive-thumbnails/'.$r->slug.'.jpg';

        // Strategy 1: archive.org item URL → IA thumbnail service
        if (preg_match('|^https?://(?:www\.)?archive\.org/(?:details|download)/([^/?#]+)|i', $file, $m)) {
            $iaId = $m[1];
            $bytes = $this->fetch("https://archive.org/services/img/{$iaId}");
            if ($bytes !== null && strlen($bytes) > 1000) {
                file_put_contents($dest, $bytes);
                return $rel;
            }
            // Fall through if IA service didn't return a usable image.
        }

        // Strategy 2: self-hosted PDF → pdftoppm page 1
        if (is_string($file) && str_ends_with(strtolower($file), '.pdf')) {
            $local = $this->resolveLocalPdf($file);
            if ($local && file_exists($local)) {
                $tmp = tempnam(sys_get_temp_dir(), 'pdfthumb');
                @unlink($tmp);
                $cmd = sprintf('pdftoppm -jpeg -jpegopt quality=80 -r 72 -f 1 -l 1 %s %s 2>&1', escapeshellarg($local), escapeshellarg($tmp));
                exec($cmd, $out, $rc);
                $generated = $tmp.'-1.jpg';
                if (file_exists($generated)) {
                    rename($generated, $dest);
                    return $rel;
                }
                return false;
            }
        }

        // Audio / video / other → leave alone, view falls back to type SVG.
        return null;
    }

    private function resolveLocalPdf(string $file): ?string {
        // Strip leading slash for public_path()
        $file = ltrim($file, '/');
        $candidate = public_path($file);
        return is_string($candidate) ? $candidate : null;
    }

    private function fetch(string $url): ?string {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'NPPC-archive-thumbnailer/1.0',
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false || $code >= 400) return null;
        return is_string($body) ? $body : null;
    }
}
