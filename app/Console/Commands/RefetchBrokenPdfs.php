<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Try to re-fetch every locally-hosted PDF that pdftoppm can't render
 * (truncated downloads, broken trailer dictionaries, HTML pages saved
 * as .pdf, etc.). Resolves the original source URL from the JSON data
 * files in database/data/ by slug, then downloads with a browser-like
 * User-Agent. Skips anything where the bytes don't start with "%PDF-".
 *
 * After this runs, re-run `archive:generate-thumbnails` to fill in
 * the thumbnail column for the records whose PDFs got fixed.
 *
 * Usage:
 *   php artisan archive:refetch-broken-pdfs           # all broken local PDFs
 *   php artisan archive:refetch-broken-pdfs --slug=X  # one record only
 *   php artisan archive:refetch-broken-pdfs --dry-run # report without writing
 */
final class RefetchBrokenPdfs extends Command {
    protected $signature = 'archive:refetch-broken-pdfs {--slug= : Only process this slug} {--dry-run : Don\'t write anything, just report}';
    protected $description = 'Re-download locally-hosted PDFs that pdftoppm cannot render (truncation, broken xref, etc.)';

    public function handle(): int {
        $sourceUrls = $this->loadSourceUrls();

        $query = ArchiveRecord::query()
            ->where('file', 'like', '/pdfs/%.pdf');
        if ($s = $this->option('slug')) {
            $query->where('slug', $s);
        }

        $tried = 0; $repaired = 0; $stillBroken = 0; $okAlready = 0; $noSource = 0;
        foreach ($query->cursor() as $r) {
            $local = public_path(ltrim($r->file, '/'));
            if (!file_exists($local)) {
                continue;
            }
            // Skip if pdftoppm can already render it.
            if ($this->canRender($local)) {
                $okAlready++;
                continue;
            }
            $tried++;
            $url = $sourceUrls[$r->slug] ?? null;
            if (!$url) {
                $this->warn("  no source URL for {$r->slug}");
                $noSource++;
                continue;
            }
            $this->line("  refetching {$r->slug} <- {$url}");
            $bytes = $this->download($url);
            if ($bytes === null || strlen($bytes) < 1024 || !str_starts_with($bytes, '%PDF-')) {
                $this->warn("    download failed or not a PDF");
                $stillBroken++;
                continue;
            }
            if ($this->option('dry-run')) {
                $this->info("    [dry-run] would write ".strlen($bytes)." bytes to {$local}");
                $repaired++;
                continue;
            }
            file_put_contents($local, $bytes);
            // Confirm the new copy renders.
            if ($this->canRender($local)) {
                $repaired++;
                $this->info("    fixed ({$r->slug}, ".strlen($bytes)." bytes)");
            } else {
                $stillBroken++;
                $this->warn("    new copy still won't render");
            }
        }

        $this->info("Done — ok-already {$okAlready}, repaired {$repaired}, still-broken {$stillBroken}, no-source {$noSource} (of {$tried} attempted).");
        return self::SUCCESS;
    }

    private function canRender(string $pdfPath): bool {
        $tmp = tempnam(sys_get_temp_dir(), 'pftest');
        @unlink($tmp);
        $cmd = sprintf('pdftoppm -jpeg -singlefile %s %s 2>/dev/null', escapeshellarg($pdfPath), escapeshellarg($tmp));
        exec($cmd, $_, $rc);
        $out = $tmp.'.jpg';
        $ok = file_exists($out);
        @unlink($out);
        return $ok;
    }

    /**
     * Build {slug => source_url} from every JSON file under database/data/
     * that contains records with slug + url fields.
     */
    private function loadSourceUrls(): array {
        $map = [];
        foreach (glob(database_path('data/*.json')) as $j) {
            $data = json_decode((string) @file_get_contents($j), true);
            if (!is_array($data)) continue;
            foreach ($data as $row) {
                if (!is_array($row) || empty($row['slug'])) continue;
                $url = $row['pdf_url'] ?? $row['url'] ?? $row['source_url'] ?? null;
                if (is_string($url) && $url !== '') {
                    $map[$row['slug']] = $url;
                }
            }
        }
        return $map;
    }

    private function download(string $url): ?string {
        $headers = [
            'Accept: application/pdf,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
        ];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0 Safari/537.36',
            CURLOPT_HTTPHEADER => $headers,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false || $code >= 400) return null;
        return is_string($body) ? $body : null;
    }
}
