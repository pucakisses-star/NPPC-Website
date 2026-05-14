<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Fetch 41 political-prisoner PDFs from six sources that aren't yet
 * in the archive:
 *
 *   - Critical Resistance — The Abolitionist newspaper (8 issues, 2022–2025)
 *   - People's Law Office — Hampton / Burge torture / FALN files
 *   - Marxists Internet Archive — IWW & Smith Act materials
 *   - National Lawyers Guild — Know Your Rights / Mass Defense
 *   - The Sentencing Project — mass-incarceration reports
 *   - Vera Institute — People in Jail and Prison
 *
 * PDFs land in public/pdfs/new-sources/. If a download 403s the
 * record is still registered with the remote URL so the link
 * survives.
 */
final class FetchNewSourcesPdfs extends Command {
    protected $signature = 'archive:fetch-new-sources-pdfs {--force : Re-download even if local file exists}';
    protected $description = 'Fetch PP PDFs from Critical Resistance, PLO, MIA, NLG, Sentencing Project, and Vera Institute';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $payloads = json_decode(file_get_contents(database_path('data/new-sources-pp-pdfs.json')), true);

        $dir = public_path('pdfs/new-sources');
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
            $webPath = '/pdfs/new-sources/'.$filename;

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
                        $this->error("  HTTP {$resp->status()} — remote URL stored.");
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
                'title'         => $payload['title'],
                'description'   => $payload['description'] ?? null,
                'record_type'   => 'document',
                'source_format' => 'pdf',
                'file'          => $webPath,
                'source_url'    => $url,
                'collection'    => $payload['collection'] ?? 'New Sources',
                'publisher'     => $payload['publisher'] ?? null,
                'authors'       => $payload['authors'] ?? null,
                'year'          => $payload['year'] ?? null,
                'date'          => $payload['date'] ?? null,
                'subjects'      => $payload['subjects'] ?? [],
                'is_digitized'  => true,
                'published'     => true,
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
