<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Downloads 49 institutional government / academic-archive PDFs
 * relevant to U.S. political prisoners — FBI Vault COINTELPRO and
 * surveillance files, Church Committee reports, FBI FOIA consolidated
 * releases, NARA records schedules, Library of Congress PP pamphlets,
 * IWW oral history, and Churchill / Vander Wall's "Agents of
 * Repression." PDFs land in public/pdfs/institutional/.
 *
 * Note: FBI Vault files are large (30-100MB each); the full run is
 * ~2-3GB. Several FBI Vault domains are anti-bot — confirm in a
 * browser if a download 403s.
 */
final class FetchInstitutionalPpPdfs extends Command {
    protected $signature = 'archive:fetch-institutional-pp-pdfs {--force : Re-download even if local file exists}';
    protected $description = 'Fetch 49 institutional PP PDFs (FBI Vault, NARA, LoC, Internet Archive)';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $payloads = json_decode(file_get_contents(database_path('data/institutional-pp-pdfs.json')), true);

        $dir = public_path('pdfs/institutional');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $downloaded = 0;
        $registered = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($payloads as $payload) {
            $slug = $payload['slug'];
            $url = $payload['pdf_url'];
            $filename = $slug.'.pdf';
            $localPath = $dir.DIRECTORY_SEPARATOR.$filename;
            $webPath = '/pdfs/institutional/'.$filename;

            if (! is_file($localPath) || $force || filesize($localPath) < 1000) {
                $this->line("fetch {$url}");
                $tmp = $localPath.'.partial';
                try {
                    $resp = Http::withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (compatible; NPPC-Archive/1.0; +https://nationalpoliticalprisonercoalition.org)',
                    ])
                        ->withOptions(['sink' => $tmp, 'allow_redirects' => true])
                        ->timeout(900)
                        ->get($url);
                    if (! $resp->successful()) {
                        @unlink($tmp);
                        $this->error("  HTTP {$resp->status()} — registration deferred, file URL stored remote.");
                        // Still register but with remote URL as `file`
                        $webPath = $url;
                        $failed++;
                    } else {
                        $size = is_file($tmp) ? filesize($tmp) : 0;
                        if ($size < 1000) {
                            @unlink($tmp);
                            $this->error("  suspiciously small ({$size} bytes) — remote URL stored.");
                            $webPath = $url;
                            $failed++;
                        } else {
                            rename($tmp, $localPath);
                            $this->info('  saved '.number_format($size / 1024 / 1024, 1).' MB to '.$webPath);
                            $downloaded++;
                        }
                    }
                } catch (\Throwable $e) {
                    @unlink($tmp);
                    $this->error('  '.$e->getMessage().' — remote URL stored.');
                    $webPath = $url;
                    $failed++;
                }
            } else {
                $this->line('exists '.$webPath);
                $skipped++;
            }

            $record = [
                'title' => $payload['title'],
                'description' => $payload['description'] ?? null,
                'record_type' => 'document',
                'source_format' => 'pdf',
                'file' => $webPath,
                'collection' => $payload['collection'] ?? 'Institutional Archives',
                'publisher' => $payload['publisher'] ?? null,
                'authors' => $payload['authors'] ?? null,
                'year' => $payload['year'] ?? null,
                'date' => $payload['date'] ?? null,
                'subjects' => ['COINTELPRO', 'FBI Surveillance', 'Government Repression', 'Political Prisoners'],
                'is_digitized' => true,
                'published' => true,
            ];

            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) {
                $existing->update($record);
                $this->info('  RECORD updated: '.$payload['title']);
            } else {
                ArchiveRecord::create(['slug' => $slug] + $record);
                $this->info('  RECORD added: '.$payload['title']);
            }
            $registered++;
        }

        $this->info("\nDone. Downloaded={$downloaded} Skipped={$skipped} Registered={$registered} FailedDownload={$failed}");

        return self::SUCCESS;
    }
}
