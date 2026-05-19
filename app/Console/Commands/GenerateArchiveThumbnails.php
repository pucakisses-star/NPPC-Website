<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Generate thumbnail images for ArchiveRecord rows that don't have one.
 *
 * Strategy is tried in order; first hit wins:
 *
 *   1. archive.org item URL  → IA thumbnail service /services/img/{id}.
 *   2. self-hosted PDF (/pdfs/...)
 *                            → render page 1 with pdftoppm.
 *   3. any other remote PDF URL (freedomarchives.org, nycabc.wordpress.com,
 *      tile.loc.gov, etc.)
 *                            → curl-download to temp, render page 1 with
 *                              pdftoppm, discard the temp file.
 *
 * Audio / video / non-PDF remote URLs without a usable cover are skipped —
 * the front-end already falls back to a type SVG.
 *
 * Stores results at storage/app/public/archive-thumbnails/{slug}.jpg and
 * sets `thumbnail` = "archive-thumbnails/{slug}.jpg" (resolved via
 * Storage::url() by the model).
 *
 * Idempotent — skips records that already have a thumbnail unless --force.
 */
final class GenerateArchiveThumbnails extends Command {
    protected $signature = 'archive:generate-thumbnails {--force : Regenerate even if a thumbnail already exists} {--limit=0 : Cap how many records to process}';
    protected $description = 'Generate page-1 thumbnails for ArchiveRecord rows (IA service + local + remote-PDF render)';

    /** Max remote PDF size we are willing to download just to render page 1. */
    private const REMOTE_PDF_MAX_BYTES = 250 * 1024 * 1024; // 250 MB

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
        if (preg_match('#^https?://(?:www\.)?archive\.org/(?:details|download)/([^/?\#]+)#i', $file, $m)) {
            $iaId = $m[1];
            $bytes = $this->fetch("https://archive.org/services/img/{$iaId}");
            if ($bytes !== null && strlen($bytes) > 1000) {
                file_put_contents($dest, $bytes);
                return $rel;
            }
            // Fall through if IA service didn't return a usable image.
        }

        $isPdf = is_string($file) && str_ends_with(strtolower($file), '.pdf');
        if (!$isPdf) {
            // Audio / video / non-PDF page URL → leave alone, view falls back to type SVG.
            return null;
        }

        // Strategy 2: self-hosted PDF → pdftoppm page 1
        $local = $this->resolveLocalPdf($file);
        if ($local && file_exists($local)) {
            return $this->renderPdfPageOne($local, $dest) ? $rel : false;
        }

        // Strategy 3: remote PDF → download to temp, render page 1, discard
        if (preg_match('#^https?://#i', $file)) {
            $tmpPdf = tempnam(sys_get_temp_dir(), 'remotepdf').'.pdf';
            $ok = $this->downloadTo($file, $tmpPdf, self::REMOTE_PDF_MAX_BYTES);
            if (!$ok) {
                @unlink($tmpPdf);
                return false;
            }
            $rendered = $this->renderPdfPageOne($tmpPdf, $dest);
            @unlink($tmpPdf);
            return $rendered ? $rel : false;
        }

        return null;
    }

    private function resolveLocalPdf(string $file): ?string {
        $file = ltrim($file, '/');
        $candidate = public_path($file);
        return is_string($candidate) ? $candidate : null;
    }

    /**
     * Render page 1 of $pdfPath as a JPEG at $destJpg. Uses -singlefile
     * so the output filename is exactly $destJpg (no page-number padding).
     */
    private function renderPdfPageOne(string $pdfPath, string $destJpg): bool {
        $tmp = tempnam(sys_get_temp_dir(), 'pdfthumb');
        @unlink($tmp);
        $cmd = sprintf('pdftoppm -jpeg -jpegopt quality=80 -r 72 -singlefile %s %s 2>&1', escapeshellarg($pdfPath), escapeshellarg($tmp));
        exec($cmd, $out, $rc);
        $generated = $tmp.'.jpg';
        if (!file_exists($generated)) {
            return false;
        }
        rename($generated, $destJpg);
        return true;
    }

    /**
     * Download $url to $destPath with a size cap. Returns true if the file
     * was downloaded successfully and looks like a PDF.
     */
    private function downloadTo(string $url, string $destPath, int $maxBytes): bool {
        $fh = fopen($destPath, 'wb');
        if ($fh === false) return false;

        $ch = curl_init($url);
        $downloaded = 0;
        $aborted = false;
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fh,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_USERAGENT => 'NPPC-archive-thumbnailer/1.0',
            CURLOPT_NOPROGRESS => false,
            CURLOPT_PROGRESSFUNCTION => function ($ch, $dlTotal, $dlNow) use (&$aborted, $maxBytes) {
                if ($dlNow > $maxBytes || $dlTotal > $maxBytes) {
                    $aborted = true;
                    return 1; // abort
                }
                return 0;
            },
        ]);
        $ok = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fh);

        if ($aborted || $ok === false || $code >= 400) {
            return false;
        }
        if (!file_exists($destPath) || filesize($destPath) < 1024) {
            return false;
        }
        // Quick sanity check: file starts with "%PDF-"
        $head = file_get_contents($destPath, false, null, 0, 5);
        return $head === '%PDF-';
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
