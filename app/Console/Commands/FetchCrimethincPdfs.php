<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Downloads CrimethInc.'s political-prisoner-relevant zines surfaced
 * from crimethinc.com/library — Green Scare analysis, J20 defendant
 * series, Stop Cop City RICO explainers, and anti-repression
 * manuals. PDFs land in public/pdfs/crimethinc/ and each one is
 * registered as an ArchiveRecord.
 *
 * Idempotent — skips downloads when the local file is already
 * present (and a healthy non-zero size). --force re-downloads.
 */
final class FetchCrimethincPdfs extends Command {
    protected $signature = 'archive:fetch-crimethinc-pdfs {--force : Re-download even if local file exists}';
    protected $description = 'Download CrimethInc. PP-relevant PDFs and register ArchiveRecords';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $payloads = json_decode(file_get_contents(database_path('data/crimethinc-media.json')), true);

        $dir = public_path('pdfs/crimethinc');
        $webPrefix = '/pdfs/crimethinc';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $downloaded = 0;
        $registered = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($payloads as $payload) {
            $slug = $payload['slug'];
            $url = $payload['file_url'];
            $filename = $slug.'.pdf';
            $localPath = $dir.DIRECTORY_SEPARATOR.$filename;
            $webPath = $webPrefix.'/'.$filename;

            if (! is_file($localPath) || $force || filesize($localPath) < 1000) {
                $this->line("fetch {$url}");
                $tmp = $localPath.'.partial';
                try {
                    $resp = Http::withHeaders([
                        'User-Agent' => 'NPPC-Archive/1.0 (https://nationalpoliticalprisonercoalition.org)',
                    ])
                        ->withOptions(['sink' => $tmp])
                        ->timeout(600)
                        ->get($url);
                    if (! $resp->successful()) {
                        @unlink($tmp);
                        $this->error("  HTTP {$resp->status()} — skipping registration.");
                        $failed++;

                        continue;
                    }
                    $size = is_file($tmp) ? filesize($tmp) : 0;
                    if ($size < 1000) {
                        @unlink($tmp);
                        $this->error('  suspiciously small response ('.$size.' bytes).');
                        $failed++;

                        continue;
                    }
                    rename($tmp, $localPath);
                    $this->info('  saved '.number_format($size / 1024, 1).' KB to '.$webPath);
                    $downloaded++;
                } catch (\Throwable $e) {
                    @unlink($tmp);
                    $this->error('  '.$e->getMessage());
                    $failed++;

                    continue;
                }
            } else {
                $this->line('exists '.$webPath);
                $skipped++;
            }

            $record = [
                'title' => $payload['title'],
                'description' => $payload['description'],
                'record_type' => 'document',
                'source_format' => 'pdf',
                'file' => $webPath,
                'collection' => 'CrimethInc.',
                'publisher' => 'CrimethInc. Ex-Workers Collective',
                'year' => $payload['year'] ?? null,
                'subjects' => $payload['subjects'] ?? ['CrimethInc.', 'Anarchist Black Cross', 'Political Prisoners'],
                'is_digitized' => true,
                'published' => true,
            ];

            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) {
                $existing->update($record);
                $this->info("  RECORD updated: {$payload['title']}");
            } else {
                ArchiveRecord::create(['slug' => $slug] + $record);
                $this->info("  RECORD added: {$payload['title']}");
            }
            $registered++;
        }

        $this->info("\nDone. Downloaded={$downloaded} Skipped={$skipped} Registered={$registered} Failed={$failed}");

        return self::SUCCESS;
    }
}
